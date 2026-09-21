/**
 * Postgres plumbing for cy.task fixture resets. Suite-agnostic.
 *
 * Connection, write guard, pooling and teardown live here. What to delete or seed belongs in a
 * suite-specific task file -- see notenDb.js.
 *
 * Settings come from <PREFIX>_DB_*, where PREFIX is TEST_ENV_PREFIX. That indirection is the point:
 * another suite sets its own prefix and reuses this file untouched.
 */

const PREFIX = process.env.TEST_ENV_PREFIX || "TEST";

const env = (key) => process.env[`${PREFIX}_DB_${key}`];

const dbConfigured = () => Boolean(env("HOST") && env("NAME") && env("USER"));
const writesAllowed = () => String(env("ALLOW_WRITES")).toLowerCase() === "true";

let pool = null;

const getPool = () => {
	if (pool) return pool;

	// lazy require so the suite loads without pg when no DB task is used
	const { Pool } = require("pg");

	pool = new Pool({
		host: env("HOST"),
		port: Number(env("PORT") || 5432),
		database: env("NAME"),
		user: env("USER"),
		password: env("PASSWORD"),
		max: 4,
		// tells these connections apart from the web application in pg_stat_activity
		application_name: "cypress",
		connectionTimeoutMillis: 15000,
		// the server ends a stuck statement or transaction before the 60 s task timeout, so no lock wait holds a connection
		statement_timeout: 30000,
		idle_in_transaction_session_timeout: 30000,
		ssl: String(env("SSL")).toLowerCase() === "true" ? { rejectUnauthorized: false } : undefined,
	});
	// an idle client that loses its connection emits here; without a listener the config process crashes
	pool.on("error", (error) => console.warn(`[db] idle connection lost: ${error.message}`));
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
			`Run from a host the database admits, or have an entry added. Details: ${msg}`
		);
	}
	if (/authentication failed/i.test(msg)) return `credentials rejected. Details: ${msg}`;
	if (/database .* does not exist/i.test(msg)) return `${PREFIX}_DB_NAME does not exist. Details: ${msg}`;
	if (/ECONNREFUSED/i.test(msg)) return `nothing listening on ${PREFIX}_DB_HOST:${PREFIX}_DB_PORT. Details: ${msg}`;
	if (/ETIMEDOUT|EHOSTUNREACH|ENETUNREACH/i.test(msg)) return `host unreachable (firewall/VPN?). Details: ${msg}`;
	return msg;
};

/** Proves the connection works. */
const checkAvailability = async ({ requireWrites = true } = {}) => {
	if (!dbConfigured()) return { available: false, reason: "not-configured" };
	if (requireWrites && !writesAllowed()) return { available: false, reason: "writes-disabled" };

	try {
		await withClient((client) => client.query("SELECT 1"));
		return { available: true };
	} catch (error) {
		// a failed pool is poisoned - drop it so a retry reconnects cleanly
		if (pool) {
			const failed = pool;
			pool = null;
			try {
				await failed.end();
			} catch (e) {
				/* ignore */
			}
		}
		return { available: false, reason: explainFailure(error) };
	}
};

const closeDb = async () => {
	// drop the reference first: a later task must not get a pool that is ending
	const open = pool;
	pool = null;
	if (open) await open.end();
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
