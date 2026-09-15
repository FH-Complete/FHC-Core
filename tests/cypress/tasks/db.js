/**
 * Postgres plumbing for cy.task fixture resets. Suite-agnostic.
 *
 * Connection, write guard, pooling and teardown live here. What to delete or seed belongs in a
 * suite-specific task file -- see notenDb.js.
 *
 * Settings come from <PREFIX>_DB_* and <PREFIX>_SSH_*, where PREFIX is TEST_ENV_PREFIX. That
 * indirection is the point: another suite sets its own prefix and reuses this file untouched.
 */

const { ensureTunnel, closeTunnel, tunnelPort } = require("./sshTunnel");

const PREFIX = process.env.TEST_ENV_PREFIX || "TEST";

const env = (key) => process.env[`${PREFIX}_DB_${key}`];
const sshEnv = (key) => process.env[`${PREFIX}_SSH_${key}`];

const dbConfigured = () => Boolean(env("HOST") && env("NAME") && env("USER"));
const writesAllowed = () => String(env("ALLOW_WRITES")).toLowerCase() === "true";

let pool = null;

const getPool = () => {
	if (pool) return pool;

	// lazy require so the suite loads without pg when no DB task is used
	// eslint-disable-next-line global-require
	const { Pool } = require("pg");

	// with a tunnel up, bind to its local end - that is what gets us past pg_hba
	const forwarded = tunnelPort();

	pool = new Pool({
		host: forwarded ? "127.0.0.1" : env("HOST"),
		port: forwarded || Number(env("PORT") || 5432),
		database: env("NAME"),
		user: env("USER"),
		password: env("PASSWORD"),
		max: 4,
		ssl: String(env("SSL")).toLowerCase() === "true" ? { rejectUnauthorized: false } : undefined,
	});
	return pool;
};

const assertWritable = () => {
	if (!dbConfigured()) {
		throw new Error(`db refused - set ${PREFIX}_DB_HOST / ${PREFIX}_DB_NAME / ${PREFIX}_DB_USER.`);
	}
	if (!writesAllowed()) {
		throw new Error(`db refused - set ${PREFIX}_DB_ALLOW_WRITES=true (test databases only).`);
	}
};

const withClient = async (fn) => {
	const client = await getPool().connect();
	try {
		return await fn(client);
	} finally {
		client.release();
	}
};

const inTransaction = (fn) =>
	withClient(async (client) => {
		await client.query("BEGIN");
		try {
			const result = await fn(client);
			await client.query("COMMIT");
			return result;
		} catch (error) {
			await client.query("ROLLBACK");
			throw error;
		}
	});

const explainFailure = (error) => {
	const msg = String(error && error.message ? error.message : error);

	if (/no pg_hba\.conf entry/i.test(msg)) {
		const host = (msg.match(/for host "([^"]+)"/) || [])[1] || "this machine";
		return (
			`the server refused the connection from ${host} - pg_hba.conf has no rule allowing it. ` +
			`Use the SSH tunnel (${PREFIX}_SSH_TUNNEL=true) or have an entry added. Details: ${msg}`
		);
	}
	if (/authentication failed/i.test(msg)) return `credentials rejected. Details: ${msg}`;
	if (/database .* does not exist/i.test(msg)) return `${PREFIX}_DB_NAME does not exist. Details: ${msg}`;
	if (/ECONNREFUSED/i.test(msg)) return `nothing listening on ${PREFIX}_DB_HOST:${PREFIX}_DB_PORT. Details: ${msg}`;
	if (/ETIMEDOUT|EHOSTUNREACH|ENETUNREACH/i.test(msg)) return `host unreachable (firewall/VPN?). Details: ${msg}`;
	return msg;
};

/** Opens the tunnel if configured, then proves the connection works. */
const checkAvailability = async ({ requireWrites = true } = {}) => {
	if (!dbConfigured()) return { available: false, reason: "not-configured" };
	if (requireWrites && !writesAllowed()) return { available: false, reason: "writes-disabled" };

	if (String(sshEnv("TUNNEL")).toLowerCase() === "true") {
		const tunnel = await ensureTunnel({
			sshHost: sshEnv("HOST"),
			sshPort: Number(sshEnv("PORT") || 22),
			sshUser: sshEnv("USER"),
			keyPath: sshEnv("KEY"),
			passphrase: sshEnv("PASSPHRASE"),
			agent: sshEnv("AGENT"),
			dbHost: env("HOST"),
			dbPort: Number(env("PORT") || 5432),
		});
		if (!tunnel.tunnelled) return { available: false, reason: `ssh-tunnel-failed: ${tunnel.reason}` };
	}

	try {
		await withClient((client) => client.query("SELECT 1"));
		return { available: true };
	} catch (error) {
		// a failed pool is poisoned - drop it so a retry reconnects cleanly
		if (pool) {
			try { await pool.end(); } catch (e) { /* ignore */ }
			pool = null;
		}
		return { available: false, reason: explainFailure(error) };
	}
};

const closeDb = async () => {
	if (pool) {
		await pool.end();
		pool = null;
	}
	await closeTunnel();
	return null;
};

module.exports = {
	PREFIX,
	env,
	dbConfigured,
	writesAllowed,
	assertWritable,
	withClient,
	inTransaction,
	checkAvailability,
	closeDb,
};
