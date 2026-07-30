/**
 * Local port forward over SSH, opened inside the node process.
 *
 * Knows nothing about the environment: the caller passes host, user and credential.
 */

const fs = require("fs");
const net = require("net");

let server = null;
let sshClient = null;
let localPort = null;

/** Local end of the forward, or null. Lets the pool bind without going through the environment. */
const tunnelPort = () => localPort;

const resolveAgent = (agent) => {
	if (agent && agent !== "true") return agent;
	if (process.env.SSH_AUTH_SOCK) return process.env.SSH_AUTH_SOCK;
	if (process.platform === "win32") return "\\\\.\\pipe\\openssh-ssh-agent";
	return null;
};

/**
 * Key file or agent, either is enough. A configured but missing key path is an error rather than a
 * silent fallback to the agent, since that is nearly always a typo.
 */
const resolveAuth = ({ keyPath, passphrase, agent }) => {
	if (keyPath) {
		const resolved = keyPath.startsWith("~")
			? keyPath.replace(/^~/, process.env.HOME || process.env.USERPROFILE || "")
			: keyPath;

		if (!fs.existsSync(resolved)) {
			throw new Error(`SSH key does not exist: ${resolved}. Fix the path, or clear it to use an agent.`);
		}
		return {
			privateKey: fs.readFileSync(resolved),
			passphrase: passphrase || undefined,
			description: `key ${resolved}`,
		};
	}

	const resolvedAgent = resolveAgent(agent);
	if (resolvedAgent) return { agent: resolvedAgent, description: `agent ${resolvedAgent}` };

	throw new Error("No SSH credential: set a key path, or ssh-add your key and leave it empty.");
};

/** Idempotent - repeated calls reuse the existing tunnel. */
const openTunnel = (cfg) =>
	new Promise((resolve, reject) => {
		if (localPort) return resolve(localPort);
		if (!cfg.sshHost || !cfg.sshUser) return reject(new Error("Tunnel needs an SSH host and user."));

		// eslint-disable-next-line global-require
		const { Client } = require("ssh2");

		let auth;
		try {
			auth = resolveAuth(cfg);
		} catch (error) {
			return reject(error);
		}

		sshClient = new Client();

		sshClient
			.on("ready", () => {
				server = net.createServer((socket) => {
					sshClient.forwardOut(
						socket.remoteAddress || "127.0.0.1",
						socket.remotePort || 0,
						cfg.dbHost,
						cfg.dbPort,
						(err, stream) => {
							if (err) return socket.destroy();
							socket.pipe(stream).pipe(socket);
						},
					);
				});

				server.on("error", reject);

				// port 0: let the OS pick, so a leftover process cannot collide
				server.listen(0, "127.0.0.1", () => {
					localPort = server.address().port;
					// eslint-disable-next-line no-console
					console.log(
						`[db] SSH tunnel up: 127.0.0.1:${localPort} -> ${cfg.dbHost}:${cfg.dbPort} ` +
							`via ${cfg.sshUser}@${cfg.sshHost} (${auth.description})`,
					);
					resolve(localPort);
				});
			})
			.on("error", (err) =>
				reject(
					new Error(
						`SSH to ${cfg.sshUser}@${cfg.sshHost} using ${auth.description} failed: ${err.message}. ` +
							"The SSH host is often not the web hostname, and a non-default key filename is never " +
							"tried automatically.",
					),
				),
			)
			.connect({
				host: cfg.sshHost,
				port: cfg.sshPort || 22,
				username: cfg.sshUser,
				privateKey: auth.privateKey,
				passphrase: auth.passphrase,
				agent: auth.agent,
				readyTimeout: 15000,
			});
	});

const closeTunnel = async () => {
	if (server) {
		await new Promise((resolve) => server.close(resolve));
		server = null;
	}
	if (sshClient) {
		sshClient.end();
		sshClient = null;
	}
	localPort = null;
	return null;
};

const ensureTunnel = async (cfg) => {
	try {
		return { tunnelled: true, localPort: await openTunnel(cfg) };
	} catch (error) {
		return { tunnelled: false, reason: error.message };
	}
};

module.exports = { ensureTunnel, closeTunnel, tunnelPort };
