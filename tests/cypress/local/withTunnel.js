#!/usr/bin/env node
/**
 * Runs a command using the database behind an SSH tunnel. For your own workstation only:
 * No part of the suite references this folder.
 *
 *   node tests/cypress/local/withTunnel.js npm run noten:api
 *   node tests/cypress/local/withTunnel.js npm run noten:check
 *   node tests/cypress/local/withTunnel.js npx cypress open
 *
 * The tunnel sets the command’s <PREFIX>_DB_HOST and <PREFIX>_DB_PORT to its local endpoint.
 */

const path = require("path");
const { spawn } = require("child_process");
const { ensureTunnel, closeTunnel } = require("./sshTunnel");

["local", "suites"].forEach((dir) => require("dotenv").config({ path: path.join(__dirname, "..", dir, ".env") }));

const PREFIX = process.env.TEST_ENV_PREFIX || "TEST";
const env = (key) => process.env[`${PREFIX}_${key}`];

/** Opens the tunnel and redirects the DB values in process.env to it. Provides the function to close it. */
const openDbTunnel = async () => {
	const tunnel = await ensureTunnel({
		sshHost: env("SSH_HOST"),
		sshPort: Number(env("SSH_PORT") || 22),
		sshUser: env("SSH_USER"),
		keyPath: env("SSH_KEY"),
		passphrase: env("SSH_PASSPHRASE"),
		agent: env("SSH_AGENT"),
		dbHost: env("DB_HOST"),
		dbPort: Number(env("DB_PORT") || 5432),
	});
	if (!tunnel.tunnelled) throw new Error(`SSH tunnel failed: ${tunnel.reason}`);

	process.env[`${PREFIX}_DB_HOST`] = "127.0.0.1";
	process.env[`${PREFIX}_DB_PORT`] = String(tunnel.localPort);
	return closeTunnel;
};

// cmd.exe runs npm and npx only as command-line tools
const commandLine = (parts) => parts.map((p) => (/[\s,;=&|<>^]/.test(p) ? `"${p}"` : p)).join(" ");

const main = async () => {
	const parts = process.argv.slice(2);
	if (!parts.length) {
		console.error("\n  node tests/cypress/local/withTunnel.js <command> [arguments]\n");
		return 2;
	}

	const close = await openDbTunnel();

	// Strg+C erreicht auch den Befehl; der Tunnel bleibt offen, bis der Befehl endet
	process.on("SIGINT", () => {});

	return new Promise((resolve) => {
		const child = spawn(commandLine(parts), { stdio: "inherit", shell: true });
		const finish = async (code) => {
			await close();
			resolve(code);
		};
		child.on("error", (error) => {
			console.error(`\n${error.message}\n`);
			finish(1);
		});
		child.on("exit", (code) => finish(code === null ? 1 : code));
	});
};

if (require.main === module) {
	main()
		.then((code) => process.exit(code))
		.catch((error) => {
			console.error(`\n${error.message}\n`);
			process.exit(1);
		});
}

module.exports = { openDbTunnel };
