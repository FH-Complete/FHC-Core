<?php
/**
 * Rebuilds a throwaway test database into a usable FHComplete instance.
 *
 *   php system/setup_testinstance.php --setup     rebuild the database, then verify
 *   php system/setup_testinstance.php --verify    verify only, write nothing
 *
 * The database itself is built by  db_setup.sh: truncate -> FHComplete-3.3.sql ->
 * checksystem.php -> seeders. This script only adds what db_setup.sh does not cover — addon
 * dbupdates, extension SQL and a closing health check.
 *
 * needs psql and db_setup.sh. Connection data comes from config/system.config.inc.php.
 */

if (php_sapi_name() !== 'cli') {
	http_response_code(403);
	exit("Command line only.\n");
}

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
set_time_limit(0);
ini_set('memory_limit', '1024M');

define('SETUP_ROOT', dirname(__DIR__));
define('DB_SETUP_SH', '/home/testing/fhc-coreutils/tests/automated/db_setup.sh');

function out($msg = '') { fwrite(STDOUT, $msg . PHP_EOL); }
function step($msg)     { out(''); out('=== ' . $msg . ' ' . str_repeat('=', max(0, 62 - strlen($msg)))); }
function info($msg)     { out('    ' . $msg); }
function ok($msg)       { out('  + ' . $msg); }
function warn($msg)     { out('  ! ' . $msg); }
function bail($msg)     { out(''); out('  X ' . $msg); out(''); exit(1); }

function loadConfig()
{
	$file = SETUP_ROOT . '/config/system.config.inc.php';
	if (!is_file($file)) bail("Config not found: $file");

	require_once($file);

	// the FHC config sets error_reporting(E_ALL); restore ours, or every include of the legacy
	// classes prints deprecation notices over the output
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);

	foreach (['DB_HOST', 'DB_NAME', 'DB_USER'] as $const) {
		if (!defined($const)) bail("$const is not defined in $file.");
	}
	putenv('PGPASSWORD=' . (defined('DB_PASSWORD') ? DB_PASSWORD : ''));
}

function connectDb()
{
	$conn = @pg_connect(sprintf(
		'host=%s port=%s dbname=%s user=%s password=%s',
		DB_HOST, defined('DB_PORT') ? DB_PORT : '5432', DB_NAME, DB_USER,
		defined('DB_PASSWORD') ? DB_PASSWORD : ''
	));
	if ($conn === false) bail('Cannot connect to ' . DB_NAME . '@' . DB_HOST . ' as ' . DB_USER . '.');
	return $conn;
}

function scalar($conn, $sql)
{
	$res = @pg_query($conn, $sql);
	if ($res === false) return null;
	$row = pg_fetch_row($res);
	return $row ? $row[0] : null;
}

/** Runs one .sql file. Returns the error count; "already exists" is normal on a repeat run. */
function psqlFile($file)
{
	exec(sprintf('psql -h %s -p %s -U %s -d %s -f %s 2>&1',
		escapeshellarg(DB_HOST), escapeshellarg(defined('DB_PORT') ? DB_PORT : '5432'),
		escapeshellarg(DB_USER), escapeshellarg(DB_NAME), escapeshellarg($file)), $lines);

	$errors = 0;
	foreach ($lines as $line) {
		if (stripos($line, 'ERROR') !== false && stripos($line, 'already exists') === false) $errors++;
	}
	return $errors;
}

/** Guards, before anything is dropped: --setup empties the target, so refuse anything but cy-*. */
function preflight()
{
	step('preflight');

	if (!shell_exec('command -v psql')) bail('psql is missing. Run this on the dev machine.');
	if (!is_file(DB_SETUP_SH)) bail('db_setup.sh not found: ' . DB_SETUP_SH);

	if (strpos(DB_NAME, 'cy-') !== 0) {
		bail("Target is '" . DB_NAME . "'. --setup drops every schema and only touches cy-* databases.");
	}

	$seeders = glob(SETUP_ROOT . '/system/seeders/*.sql');
	if (!$seeders) bail('No seeders in ' . SETUP_ROOT . '/system/seeders/ — the rebuild would leave an empty database.');

	ok(count($seeders) . ' seeders, target ' . DB_NAME);
}

/** Output is passed through verbatim, checksystem.php's HTML included. */
function rebuild()
{
	step('db_setup.sh');
	info('truncate -> FHComplete-3.3.sql -> checksystem.php -> seeders');
	out('');

	passthru(escapeshellarg(DB_SETUP_SH) . ' ' . escapeshellarg(SETUP_ROOT), $code);
	if ($code !== 0) bail('db_setup.sh failed with exit code ' . $code . '.');

	ok('database rebuilt');
}

/**
 * Addon DB updates. Their install.php files are web installers and empty stubs; the DDL sits in
 * addons/<name>/system/dbupdate_*.php and expects CHECKSYSTEM and a $db in scope.
 */
function runAddons()
{
	step('addons');

	$files = glob(SETUP_ROOT . '/addons/*/system/dbupdate_*.php');
	if (!$files) { info('none found'); return; }

	if (!defined('CHECKSYSTEM')) define('CHECKSYSTEM', 'setup_testinstance');
	require_once(SETUP_ROOT . '/include/basis_db.class.php');
	$db = new basis_db();

	foreach ($files as $file) {
		$name = basename(dirname(dirname($file))) . '/' . basename($file);
		ob_start();
		try {
			require($file);
			ob_end_clean();
			ok($name);
		} catch (Throwable $t) {
			ob_end_clean();
			warn($name . ': ' . $t->getMessage());
		}
	}
}

/** Extension SQL, for an instance that has the files but never ran their sql/<version>/*.sql. */
function runExtensions()
{
	step('extensions');

	$dirs = glob(SETUP_ROOT . '/application/extensions/*/sql', GLOB_ONLYDIR);
	if (!$dirs) { info('none found'); return; }

	foreach ($dirs as $dir) {
		// every version the extension ships, oldest first; a test instance always wants the newest schema
		$files = glob($dir . '/*/*.sql') ?: [];
		sort($files, SORT_NATURAL);

		$errors = 0;
		foreach ($files as $file) $errors += psqlFile($file);

		$label = sprintf('%s (%d file(s)%s)', basename(dirname($dir)), count($files), $errors ? ", $errors errors" : '');
		if ($errors) warn($label); else ok($label);
	}
}

/**
 * General signs of a usable instance only. Whether a given suite's fixture is in place is that
 * suite's own business (see tests/cypress/tools/dbCheck.js).
 */
function verify($conn)
{
	step('verify');

	$checks = [
		// dump applied
		['Tables',          "SELECT count(*) FROM information_schema.tables
		                      WHERE table_schema IN ('public','lehre','campus')", function ($v) { return $v > 200; }],
		// checksystem.php ran
		['Phrases',         "SELECT count(*) FROM system.tbl_phrase",             function ($v) { return $v > 0; }],
		['Permissions',     "SELECT count(*) FROM system.tbl_berechtigung",       function ($v) { return $v > 0; }],
		// seeders ran
		['Studiensemester', "SELECT count(*) FROM public.tbl_studiensemester
		                      WHERE start <= now()",                              function ($v) { return $v > 0; }],
		['Mitarbeiter',     "SELECT count(*) FROM public.tbl_mitarbeiter",        function ($v) { return $v > 0; }],
		['Studenten',       "SELECT count(*) FROM public.tbl_student",            function ($v) { return $v > 0; }],
	];

	$failed = 0;
	foreach ($checks as list($label, $sql, $test)) {
		$value  = scalar($conn, $sql);
		$passed = $test($value);
		if (!$passed) $failed++;
		out(sprintf('  [%s] %-20s %s', $passed ? 'OK ' : 'XX ', $label, $value === null ? '(null)' : $value));
	}

	return $failed === 0;
}

$flags  = array_slice($argv, 1);
$setup  = in_array('--setup', $flags, true);
$verify = in_array('--verify', $flags, true);

// --setup must be spelled out: the run empties the target database.
if (!$setup && !$verify) {
	out('');
	out('  php system/setup_testinstance.php --setup     rebuild the database, then verify');
	out('  php system/setup_testinstance.php --verify    verify only, write nothing');
	out('');
	exit(0);
}

loadConfig();

out('');
out('  Target : ' . DB_NAME . ' @ ' . DB_HOST);

if ($setup) {
	preflight();
	rebuild();
	runAddons();
	runExtensions();
}

$success = verify(connectDb());

out('');
out($success ? '  Done.' : '  Finished with findings, see above.');
out('');

exit($success ? 0 : 1);
