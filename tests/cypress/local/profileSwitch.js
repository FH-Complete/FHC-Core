/**
 * Switches the test instance to a configuration profile of the noten suite.
 *
 * A profile changes application/config/noten.php and the define() flags in config/global.config.inc.php.
 * The originals go to the home directory of the SSH user, outside the web root, because
 * global.config.inc.php contains credentials.
 *
 * state.json keeps the MD5 hash of each written file. If a file has a different hash, someone deployed
 * it again. Then that file is the new original, and the old backup is void.
 */

const crypto = require("crypto");
const path = require("path");
const { resolveAuth } = require("./sshTunnel");
const { base, profiles } = require("../profiles/noten");

// The instance can run on a different host than the database tunnel. If NOTEN_PROFILE_SSH_HOST is set,
// all SSH values come from NOTEN_PROFILE_SSH_*; without a key, the SSH agent is used.
const envValue = (key) => {
	const ownHost = key.startsWith("SSH_") && Boolean(process.env.NOTEN_PROFILE_SSH_HOST);
	return process.env[`NOTEN_${ownHost ? "PROFILE_" : ""}${key}`];
};

const FILES = {
	config: "application/config/noten.php",
	flags: "config/global.config.inc.php",
};

// relative to the home directory; that's where every SFTP session starts
const BACKUP_DIR = ".cypress-noten-profile";
const STATE_FILE = `${BACKUP_DIR}/state.json`;

const NAME_PATTERN = /^[a-z0-9-]+$/;
const KEY_PATTERN = /^[A-Z0-9_]+$/;

const md5 = (content) => crypto.createHash("md5").update(content).digest("hex");

const sftpCall = (fn) => new Promise((resolve, reject) => fn((err, value) => (err ? reject(err) : resolve(value))));

const sshConfigured = () => Boolean(envValue("SSH_HOST") && envValue("SSH_USER"));

// --- SFTP ---

const connect = () =>
	new Promise((resolve, reject) => {
		const { Client } = require("ssh2");

		let auth;
		try {
			auth = resolveAuth({
				keyPath: envValue("SSH_KEY"),
				passphrase: envValue("SSH_PASSPHRASE"),
				agent: envValue("SSH_AGENT"),
			});
		} catch (error) {
			reject(error);
			return;
		}

		const client = new Client();
		client
			.on("ready", () => client.sftp((err, sftp) => (err ? reject(err) : resolve({ client, sftp }))))
			.on("error", (err) =>
				reject(new Error(`SSH zu ${envValue("SSH_USER")}@${envValue("SSH_HOST")} gescheitert: ${err.message}`)),
			)
			.connect({
				host: envValue("SSH_HOST"),
				port: Number(envValue("SSH_PORT") || 22),
				username: envValue("SSH_USER"),
				privateKey: auth.privateKey,
				passphrase: auth.passphrase,
				agent: auth.agent,
				readyTimeout: 15000,
			});
	});

const withSftp = async (fn) => {
	const { client, sftp } = await connect();
	try {
		return await fn(sftp);
	} finally {
		client.end();
	}
};

const exists = (sftp, file) => new Promise((resolve) => sftp.stat(file, (err) => resolve(!err)));
const read = (sftp, file) => sftpCall((cb) => sftp.readFile(file, cb));
const writeRaw = (sftp, file, content, mode) => sftpCall((cb) => sftp.writeFile(file, content, { mode }, cb));

/** Writes to a temporary file first, so no request reads a half-written configuration. */
const write = async (sftp, file, content) => {
	// .php: a web server runs the file instead of sending its credentials
	const temp = `${file}.cypress-temp.php`;
	await writeRaw(sftp, temp, content, 0o644);
	await sftpCall((cb) => sftp.ext_openssh_rename(temp, file, cb));
};

const backupOf = (remotePath) => `${BACKUP_DIR}/${path.posix.basename(remotePath)}`;

const readState = async (sftp) =>
	(await exists(sftp, STATE_FILE)) ? JSON.parse((await read(sftp, STATE_FILE)).toString("utf8")) : null;

const writeState = (sftp, state) => writeRaw(sftp, STATE_FILE, JSON.stringify(state, null, 2), 0o600);

// --- Changing the files ---

const phpString = (text) => `'${String(text).replace(/\\/g, "\\\\").replace(/'/g, "\\'")}'`;

const phpValue = (value) => {
	if (value === null) return "null";
	if (typeof value === "boolean" || typeof value === "number") return String(value);
	if (typeof value === "string") return phpString(value);
	throw new Error(`Ein define()-Schalter nimmt nur einen einfachen Wert, nicht ${JSON.stringify(value)}.`);
};

// ASCII only: this block does not change the file's encoding
const asciiJson = (value) =>
	JSON.stringify(value).replace(/[-￿]/g, (c) => `\\u${c.charCodeAt(0).toString(16).padStart(4, "0")}`);

/** Appends the profile keys to noten.php. The later assignment wins. */
const withConfig = (original, name, config) => {
	const text = original.toString("latin1");
	const block =
		`\n// cypress-profil "${name}": nur auf der Testinstanz, die Suite stellt das Original wieder her\n` +
		`$config = array_replace($config, json_decode(${phpString(asciiJson(config))}, true));\n`;
	const end = text.search(/\?>\s*$/);

	return Buffer.from(end === -1 ? text + block : text.slice(0, end) + block + text.slice(end), "latin1");
};

/** Replaces the `define()` line of each flag. A second `define()` would print a notice on the page. */
const withFlags = (original, flags) => {
	let text = original.toString("latin1");

	Object.entries(flags).forEach(([flag, value]) => {
		const pattern = new RegExp(`^([ \\t]*)define\\(\\s*['"]${flag}['"]\\s*,[^;]*;`, "m");
		if (!pattern.test(text)) {
			throw new Error(
				`${FILES.flags} definiert ${flag} nicht. Ein Profil ändert nur einen bestehenden Schalter.`,
			);
		}
		text = text.replace(pattern, (match, indent) => `${indent}define('${flag}', ${phpValue(value)});`);
	});

	return Buffer.from(text, "latin1");
};

// --- Profile ---

/** The profile plus the base. `expect` lists getCisConfig values that a flag changes only indirectly. */
const profileData = (name) => {
	if (!NAME_PATTERN.test(String(name)) || !Object.prototype.hasOwnProperty.call(profiles, name)) {
		throw new Error(`Unbekanntes Profil "${name}". Vorhanden: ${Object.keys(profiles).join(", ")}`);
	}

	const data = {
		config: { ...base.config, ...profiles[name].config },
		flags: { ...base.flags, ...profiles[name].flags },
		expect: { ...profiles[name].expect },
	};

	[...Object.keys(data.config), ...Object.keys(data.flags)].forEach((key) => {
		if (!KEY_PATTERN.test(key)) throw new Error(`Ungültiger Schlüssel "${key}" in Profil "${name}".`);
	});

	return data;
};

/** Restores a file that a profile wrote. A file deployed again since then stays. */
const restoreFile = async (sftp, state, fileKey) => {
	const entry = state.files[fileKey];
	if (!entry) return;

	const remotePath = `${state.root}/${FILES[fileKey]}`;
	const backup = backupOf(remotePath);

	if (await exists(sftp, backup)) {
		if (md5(await read(sftp, remotePath)) === entry.md5) {
			await write(sftp, remotePath, await read(sftp, backup));
		} else {
			console.warn(`[profile] ${remotePath} ist seit dem Profil neu ausgerollt und bleibt stehen.`);
		}
		await sftpCall((cb) => sftp.unlink(backup, cb));
	}

	delete state.files[fileKey];
	await writeState(sftp, state);
};

/** Writes the profile version of a file and keeps the original as a backup. */
const applyFile = async (sftp, state, fileKey, transform) => {
	const remotePath = `${state.root}/${FILES[fileKey]}`;
	const backup = backupOf(remotePath);
	const current = await read(sftp, remotePath);
	const entry = state.files[fileKey];
	const fromProfile = Boolean(entry) && md5(current) === entry.md5;

	let original = current;
	if (fromProfile) {
		if (!(await exists(sftp, backup))) {
			throw new Error(`Die Sicherung ${backup} fehlt. Das Original von ${remotePath} ist unbekannt.`);
		}
		original = await read(sftp, backup);
	} else {
		if (entry)
			console.warn(`[profile] ${remotePath} ist seit dem letzten Profil neu ausgerollt und gilt als Original.`);
		await writeRaw(sftp, backup, current, 0o600);
	}

	const updated = transform(original);

	// the state first: if the write fails, the next run takes the file as the original
	state.files[fileKey] = { md5: md5(updated) };
	await writeState(sftp, state);
	await write(sftp, remotePath, updated);
};

const matches = (actual, expected) =>
	typeof expected === "boolean" ? Boolean(actual) === expected : JSON.stringify(actual) === JSON.stringify(expected);

/** The getCisConfig data, or null with a reason for a temporary error (network, HTTP 5xx). */
const readCisConfig = async (url, auth) => {
	let response;
	try {
		response = await fetch(url, { headers: { Authorization: auth } });
	} catch (error) {
		return { actual: null, reason: error.message };
	}

	const text = await response.text();
	try {
		const actual = JSON.parse(text).data;
		if (actual) return { actual };
	} catch (e) {
		// no JSON response
	}

	// below 500 a PHP message comes before the JSON: the file is broken, and waiting does not help
	if (response.status < 500) {
		throw new Error(
			`getCisConfig antwortet nach dem Profilwechsel mit HTTP ${response.status}: ${text.slice(0, 300)}`,
		);
	}
	return { actual: null, reason: `getCisConfig antwortet mit HTTP ${response.status}` };
};

/** Waits until getCisConfig returns the profile. PHP-FPM reads a changed file only after opcache.revalidate_freq. */
const verifyInstance = async (data) => {
	const expected = { ...data.config, ...data.flags, ...data.expect };
	const baseUrl = String(process.env.BASE_URL).replace(/\/+$/, "");
	const url = `${baseUrl}/index.ci.php/api/frontend/v1/Noten/getCisConfig`;
	const auth = `Basic ${Buffer.from(`${process.env.NOTEN_LEKTOR_USER}:${process.env.NOTEN_LEKTOR_PASSWORD}`).toString("base64")}`;
	const deadline = Date.now() + 60000;

	for (;;) {
		const { actual, reason } = await readCisConfig(url, auth);
		let pending = reason;

		if (actual) {
			const checkable = Object.keys(expected).filter((key) => key in actual);
			if (!checkable.length) {
				console.warn("[profile] getCisConfig liefert keinen Schlüssel des Profils. Der Wechsel ist ungeprüft.");
				return;
			}

			const deviating = checkable.filter((key) => !matches(actual[key], expected[key]));
			if (!deviating.length) return;

			const lines = deviating.map(
				(k) => `  ${k}: ${JSON.stringify(actual[k])} statt ${JSON.stringify(expected[k])}`,
			);
			pending = `die Instanz zeigt das Profil nicht:\n${lines.join("\n")}`;
		}

		if (Date.now() > deadline) throw new Error(`Profilwechsel nach 60 Sekunden ungeprüft, ${pending}`);
		await new Promise((resolve) => setTimeout(resolve, 2000));
	}
};

// --- public ---

/** Puts the original files back. Returns the name of the profile that was active, or null. */
const restore = async () => {
	if (!sshConfigured()) return null;

	return withSftp(async (sftp) => {
		const state = await readState(sftp);
		if (!state || !Object.keys(state.files).length) return null;

		const previous = state.profile;
		for (const fileKey of Object.keys(FILES)) await restoreFile(sftp, state, fileKey);

		state.profile = null;
		await writeState(sftp, state);
		console.log(`[profile] "${previous}" ist zurückgenommen.`);
		return previous;
	});
};

/** Switches the instance to a profile. Without a name it restores the originals. */
const apply = async (name) => {
	if (!name) return restore();

	const data = profileData(name);
	const root = envValue("REMOTE_ROOT");
	if (!sshConfigured() || !root) {
		throw new Error(
			`Profil "${name}" braucht NOTEN_SSH_HOST, NOTEN_SSH_USER und NOTEN_REMOTE_ROOT in tests/cypress/local/.env.`,
		);
	}

	await withSftp(async (sftp) => {
		if (!(await exists(sftp, BACKUP_DIR))) await sftpCall((cb) => sftp.mkdir(BACKUP_DIR, { mode: 0o700 }, cb));

		const state = (await readState(sftp)) || { files: {} };
		if (state.root && state.root !== root && Object.keys(state.files).length) {
			throw new Error(
				`Auf ${state.root} ist noch "${state.profile}" aktiv. Zuerst: node tests/cypress/local/notenProfiles.js restore`,
			);
		}
		state.root = root;
		state.profile = name;

		if (Object.keys(data.config).length)
			await applyFile(sftp, state, "config", (o) => withConfig(o, name, data.config));
		else await restoreFile(sftp, state, "config");

		if (Object.keys(data.flags).length) await applyFile(sftp, state, "flags", (o) => withFlags(o, data.flags));
		else await restoreFile(sftp, state, "flags");

		await writeState(sftp, state);
	});

	await verifyInstance(data);
	console.log(`[profile] "${name}" ist aktiv.`);
	return name;
};

const status = () => (sshConfigured() ? withSftp(readState) : Promise.resolve(null));

module.exports = { apply, restore, status, profileData };
