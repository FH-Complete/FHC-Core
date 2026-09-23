#!/usr/bin/env node
/**
 * Diagnose or seed the database fixture a Cypress suite needs. Suite-agnostic runner.
 *
 *   node tests/cypress/tools/dbCheck.js noten            report what is present and what is missing
 *   node tests/cypress/tools/dbCheck.js noten seed       apply the suite's seeder, then re-check
 *   node tests/cypress/tools/dbCheck.js noten env        print the .env block to use
 *
 * A suite contributes tools/checks/<suite>.js exporting { title, checks, sqlFiles }. Connection
 * and output are shared and live here. A sqlFiles entry is a path or { file, group }; a
 * "-- ==== <group> ====" line starts a group.
 *
 * `check` is read-only; `seed` needs NOTEN_DB_ALLOW_WRITES=true and the owner role.
 */

const fs = require("fs");
const path = require("path");

try {
	require("dotenv").config({ path: path.join(__dirname, "..", "suites", ".env") });
} catch (e) {
	// dotenv missing -> fall back to the ambient environment
}

const db = require("../tasks/db");

const COL = 48;
const CHECKS_DIR = path.join(__dirname, "checks");

const usage = (suite, mode) => `node tests/cypress/tools/dbCheck.js ${suite} ${mode}`;

const loadSuite = (name) => {
	const file = path.join(CHECKS_DIR, `${name}.js`);
	if (!fs.existsSync(file)) {
		const available = fs.existsSync(CHECKS_DIR)
			? fs
					.readdirSync(CHECKS_DIR)
					.map((f) => f.replace(/\.js$/, ""))
					.join(", ")
			: "(none)";
		console.error(`\nUnknown suite "${name}". Available: ${available}\n`);
		process.exit(2);
	}
	return require(file);
};

const runChecks = async (client, suite) => {
	console.log(`\n${suite.title} fixture check\n${"=".repeat(70)}`);

	const failures = [];
	for (const check of suite.checks) {
		let row;
		try {
			row = (await client.query(check.sql)).rows[0] || {};
		} catch (error) {
			console.log(`  [ERR ] ${check.label}\n         ${error.message}`);
			failures.push(check);
			continue;
		}

		const passed = check.ok(row);
		const value = row.value === "" ? "(none)" : String(row.value);
		console.log(`  [${passed ? "PASS" : "FAIL"}] ${check.label.padEnd(COL)} ${value}`);
		if (!passed) failures.push(check);
	}

	console.log("=".repeat(70));

	if (!failures.length) {
		console.log("\nAll preconditions met.\n");
		return 0;
	}

	console.log(`\n${failures.length} problem(s):\n`);
	failures.forEach((f) => console.log(`  - ${f.label}\n      ${f.hint}\n`));
	console.log(`Fix with:  ${usage(suite.name, "seed")}\n`);
	return 1;
};

/** The SQL of a file, or of one group in it. A group ends at the next group header. */
const readSql = (file, group) => {
	const sql = fs.readFileSync(file, "utf8");
	if (!group) return sql;
	const parts = sql.split(/^-- ==== (.+?) ====[ \t]*\r?$/m);
	for (let i = 1; i < parts.length; i += 2) if (parts[i] === group) return parts[i + 1];
	return null;
};

const applySqlFile = async (client, entry) => {
	const { file, group } = typeof entry === "string" ? { file: entry } : entry;
	const label = path.relative(process.cwd(), file) + (group ? ` [${group}]` : "");
	if (!db.writesAllowed()) {
		console.error("\nRefusing to write: set NOTEN_DB_ALLOW_WRITES=true first.\n");
		return 2;
	}
	if (!fs.existsSync(file)) {
		console.error(`\nFile not found: ${file}\n`);
		return 2;
	}

	const sql = readSql(file, group);
	if (sql === null) {
		console.error(`\nGroup "${group}" not found in ${file}\n`);
		return 2;
	}

	console.log(`\nApplying ${label} ...\n`);

	// One connection, one call: the seeders rely on running as a unit (temp tables, DO blocks and a
	// closing verification query).
	let result;
	try {
		result = await client.query(sql);
	} catch (error) {
		// `check` gets by as the application role; seeding creates and grants objects.
		if (/permission denied/i.test(error.message)) {
			console.error(`${error.message}\n`);
			console.error(`Seeding needs the database owner, but NOTEN_DB_USER is "${db.env("USER")}".`);
			console.error("Point it at the owner role, or rebuild the database with db_setup.sh.\n");
			return 2;
		}
		throw error;
	}

	// Print whatever the last statement returned, whatever its shape.
	const results = Array.isArray(result) ? result : [result];
	const last = results.filter((r) => r && r.rows && r.rows.length).pop();
	if (last) {
		console.log("Verification\n" + "-".repeat(50));
		last.rows.forEach((row) => Object.entries(row).forEach(([k, v]) => console.log(`  ${k.padEnd(24)} ${v}`)));
	}

	console.log(`\nDone: ${label}\n`);
	return 0;
};

const printEnv = () => {
	console.log(`
Add to tests/cypress/suites/.env (gitignored):

  NOTEN_DB_HOST=<postgres host>
  NOTEN_DB_PORT=5432
  NOTEN_DB_NAME=<test database>
  NOTEN_DB_USER=<user>
  NOTEN_DB_PASSWORD=<password>
  NOTEN_DB_ALLOW_WRITES=true
`);
};

const main = async () => {
	const suiteName = process.argv[2];
	const mode = process.argv[3] || "check";

	if (!suiteName || suiteName.startsWith("-")) {
		console.error(`\n  ${usage("<suite>", "[check|seed|env]")}\n`);
		return 2;
	}
	if (mode === "env") {
		printEnv();
		return 0;
	}
	if (!["check", "seed"].includes(mode)) {
		console.error(`Unknown mode "${mode}". Use: check | seed | env`);
		return 2;
	}

	const suite = loadSuite(suiteName);
	suite.name = suiteName;

	if (mode !== "check" && !(suite.sqlFiles && suite.sqlFiles[mode])) {
		console.error(`Suite "${suiteName}" defines no SQL file for "${mode}".`);
		return 2;
	}

	const status = await db.checkConnection({ requireWrites: mode !== "check" });
	if (!status.available) {
		console.error(`\nCould not connect: ${status.reason}\n`);
		if (status.reason === "not-configured") printEnv();
		return 2;
	}

	try {
		return await db.withClient(async (client) => {
			client.on("notice", (n) => console.log(`  NOTICE: ${n.message}`));

			if (mode !== "check") {
				// a suite can name several files or groups; they run in this order
				for (const file of [].concat(suite.sqlFiles[mode])) {
					const code = await applySqlFile(client, file);
					if (code !== 0) return code;
				}
			}
			return runChecks(client, suite);
		});
	} finally {
		await db.closeDb();
	}
};

main()
	.then((code) => process.exit(code))
	.catch((error) => {
		console.error(`\n${error.message}\n`);
		process.exit(1);
	});
