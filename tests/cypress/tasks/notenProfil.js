/**
 * Konfigurationsprofile der Benotungstool-Suite auf der Testinstanz.
 *
 * Ein Profil ändert application/config/noten.php und die define()-Schalter in
 * config/global.config.inc.php. Die Originale liegen im Home-Verzeichnis des SSH-Benutzers, also
 * ausserhalb des Webroots: global.config.inc.php enthält Zugangsdaten.
 *
 * zustand.json hält die md5 jeder geschriebenen Datei. Weicht eine Datei davon ab, ist sie seither neu
 * ausgerollt. Dann gilt sie als Original, und die alte Sicherung verfällt.
 */

const crypto = require("crypto");
const path = require("path");
const { resolveAuth } = require("./sshTunnel");
const { basis, profile } = require("../profiles/noten");

const PREFIX = process.env.TEST_ENV_PREFIX || "TEST";

// Die Instanz kann auf einem anderen Host liegen als der SSH-Tunnel zur Datenbank. Ist
// <PREFIX>_PROFIL_SSH_HOST gesetzt, kommt jeder SSH-Wert aus <PREFIX>_PROFIL_SSH_*; ohne Schlüssel gilt
// dann der SSH-Agent.
const envWert = (key) => {
	const eigenerHost = key.startsWith("SSH_") && Boolean(process.env[`${PREFIX}_PROFIL_SSH_HOST`]);
	return process.env[`${PREFIX}_${eigenerHost ? "PROFIL_" : ""}${key}`];
};

const DATEIEN = {
	config: "application/config/noten.php",
	flags: "config/global.config.inc.php",
};

// relativ zum Home-Verzeichnis, dort beginnt jede SFTP-Sitzung
const SICHERUNG = ".cypress-noten-profil";
const ZUSTAND = `${SICHERUNG}/zustand.json`;

const NAME = /^[a-z0-9-]+$/;
const SCHLUESSEL = /^[A-Z0-9_]+$/;

const md5 = (inhalt) => crypto.createHash("md5").update(inhalt).digest("hex");

const sftpAufruf = (fn) => new Promise((resolve, reject) => fn((err, wert) => (err ? reject(err) : resolve(wert))));

const sshKonfiguriert = () => Boolean(envWert("SSH_HOST") && envWert("SSH_USER"));

// --- SFTP ---

const verbinde = () =>
	new Promise((resolve, reject) => {
		const { Client } = require("ssh2");

		let auth;
		try {
			auth = resolveAuth({
				keyPath: envWert("SSH_KEY"),
				passphrase: envWert("SSH_PASSPHRASE"),
				agent: envWert("SSH_AGENT"),
			});
		} catch (error) {
			reject(error);
			return;
		}

		const client = new Client();
		client
			.on("ready", () => client.sftp((err, sftp) => (err ? reject(err) : resolve({ client, sftp }))))
			.on("error", (err) =>
				reject(new Error(`SSH zu ${envWert("SSH_USER")}@${envWert("SSH_HOST")} gescheitert: ${err.message}`)),
			)
			.connect({
				host: envWert("SSH_HOST"),
				port: Number(envWert("SSH_PORT") || 22),
				username: envWert("SSH_USER"),
				privateKey: auth.privateKey,
				passphrase: auth.passphrase,
				agent: auth.agent,
				readyTimeout: 15000,
			});
	});

const mitSftp = async (fn) => {
	const { client, sftp } = await verbinde();
	try {
		return await fn(sftp);
	} finally {
		client.end();
	}
};

const existiert = (sftp, datei) => new Promise((resolve) => sftp.stat(datei, (err) => resolve(!err)));
const lies = (sftp, datei) => sftpAufruf((cb) => sftp.readFile(datei, cb));
const schreibeRoh = (sftp, datei, inhalt, mode) => sftpAufruf((cb) => sftp.writeFile(datei, inhalt, { mode }, cb));

/** Schreibt über eine Temporärdatei, damit kein Request eine halbe Konfiguration liest. */
const schreibe = async (sftp, datei, inhalt) => {
	// .php: ein Webserver führt die Datei aus, statt ihre Zugangsdaten auszuliefern
	const temp = `${datei}.cypress-temp.php`;
	await schreibeRoh(sftp, temp, inhalt, 0o644);
	await sftpAufruf((cb) => sftp.ext_openssh_rename(temp, datei, cb));
};

const sicherungVon = (entfernt) => `${SICHERUNG}/${path.posix.basename(entfernt)}`;

const leseZustand = async (sftp) =>
	(await existiert(sftp, ZUSTAND)) ? JSON.parse((await lies(sftp, ZUSTAND)).toString("utf8")) : null;

const schreibeZustand = (sftp, zustand) => schreibeRoh(sftp, ZUSTAND, JSON.stringify(zustand, null, 2), 0o600);

// --- Umbau der Dateien ---

const phpString = (text) => `'${String(text).replace(/\\/g, "\\\\").replace(/'/g, "\\'")}'`;

const phpWert = (wert) => {
	if (wert === null) return "null";
	if (typeof wert === "boolean" || typeof wert === "number") return String(wert);
	if (typeof wert === "string") return phpString(wert);
	throw new Error(`Ein define()-Schalter nimmt nur einen einfachen Wert, nicht ${JSON.stringify(wert)}.`);
};

// nur ASCII: der Block ändert die Kodierung der Datei nicht
const asciiJson = (wert) =>
	JSON.stringify(wert).replace(/[-￿]/g, (c) => `\\u${c.charCodeAt(0).toString(16).padStart(4, "0")}`);

/** Hängt die Schlüssel des Profils an noten.php. Die spätere Zuweisung gewinnt. */
const mitConfig = (original, name, config) => {
	const text = original.toString("latin1");
	const block =
		`\n// cypress-profil "${name}": nur auf der Testinstanz, die Suite stellt das Original wieder her\n` +
		`$config = array_replace($config, json_decode(${phpString(asciiJson(config))}, true));\n`;
	const ende = text.search(/\?>\s*$/);

	return Buffer.from(ende === -1 ? text + block : text.slice(0, ende) + block + text.slice(ende), "latin1");
};

/** Ersetzt die define()-Zeile jedes Schalters. Ein zweites define() druckt eine Notice in die Seite. */
const mitFlags = (original, flags) => {
	let text = original.toString("latin1");

	Object.entries(flags).forEach(([flag, wert]) => {
		const muster = new RegExp(`^([ \\t]*)define\\(\\s*['"]${flag}['"]\\s*,[^;]*;`, "m");
		if (!muster.test(text)) {
			throw new Error(
				`${DATEIEN.flags} definiert ${flag} nicht. Ein Profil ändert nur einen bestehenden Schalter.`,
			);
		}
		text = text.replace(muster, (treffer, einzug) => `${einzug}define('${flag}', ${phpWert(wert)});`);
	});

	return Buffer.from(text, "latin1");
};

// --- Profile ---

/** Profil samt Basis. pruefe nennt Werte aus getCisConfig, die ein Flag nur mittelbar ändert. */
const profilDaten = (name) => {
	if (!NAME.test(String(name)) || !Object.prototype.hasOwnProperty.call(profile, name)) {
		throw new Error(`Unbekanntes Profil "${name}". Vorhanden: ${Object.keys(profile).join(", ")}`);
	}

	const daten = {
		config: { ...basis.config, ...profile[name].config },
		flags: { ...basis.flags, ...profile[name].flags },
		pruefe: { ...profile[name].pruefe },
	};

	[...Object.keys(daten.config), ...Object.keys(daten.flags)].forEach((key) => {
		if (!SCHLUESSEL.test(key)) throw new Error(`Ungültiger Schlüssel "${key}" in Profil "${name}".`);
	});

	return daten;
};

/** Stellt eine Datei her, die ein Profil geschrieben hat. Eine neu ausgerollte Datei bleibt stehen. */
const stelleHer = async (sftp, zustand, schluessel) => {
	const eintrag = zustand.dateien[schluessel];
	if (!eintrag) return;

	const entfernt = `${zustand.root}/${DATEIEN[schluessel]}`;
	const sicherung = sicherungVon(entfernt);

	if (await existiert(sftp, sicherung)) {
		if (md5(await lies(sftp, entfernt)) === eintrag.md5) {
			await schreibe(sftp, entfernt, await lies(sftp, sicherung));
		} else {
			console.warn(`[profil] ${entfernt} ist seit dem Profil neu ausgerollt und bleibt stehen.`);
		}
		await sftpAufruf((cb) => sftp.unlink(sicherung, cb));
	}

	delete zustand.dateien[schluessel];
	await schreibeZustand(sftp, zustand);
};

/** Schreibt eine Datei nach dem Profil. Die Grundlage ist immer das Original, nie ein anderes Profil. */
const setze = async (sftp, zustand, schluessel, umbau) => {
	const entfernt = `${zustand.root}/${DATEIEN[schluessel]}`;
	const sicherung = sicherungVon(entfernt);
	const aktuell = await lies(sftp, entfernt);
	const eintrag = zustand.dateien[schluessel];
	const vomProfil = Boolean(eintrag) && md5(aktuell) === eintrag.md5;

	let original = aktuell;
	if (vomProfil) {
		if (!(await existiert(sftp, sicherung))) {
			throw new Error(`Die Sicherung ${sicherung} fehlt. Das Original von ${entfernt} ist unbekannt.`);
		}
		original = await lies(sftp, sicherung);
	} else {
		if (eintrag)
			console.warn(`[profil] ${entfernt} ist seit dem letzten Profil neu ausgerollt und gilt als Original.`);
		await schreibeRoh(sftp, sicherung, aktuell, 0o600);
	}

	const neu = umbau(original);

	// zuerst der Zustand: bricht das Schreiben ab, gilt die Datei beim nächsten Lauf als Original
	zustand.dateien[schluessel] = { md5: md5(neu) };
	await schreibeZustand(sftp, zustand);
	await schreibe(sftp, entfernt, neu);
};

const gleich = (ist, soll) =>
	typeof soll === "boolean" ? Boolean(ist) === soll : JSON.stringify(ist) === JSON.stringify(soll);

/** Die Daten von getCisConfig, oder null mit Grund bei einem vorübergehenden Fehler (Netz, HTTP 5xx). */
const leseCisConfig = async (url, auth) => {
	let antwort;
	try {
		antwort = await fetch(url, { headers: { Authorization: auth } });
	} catch (error) {
		return { ist: null, grund: error.message };
	}

	const text = await antwort.text();
	try {
		const ist = JSON.parse(text).data;
		if (ist) return { ist };
	} catch (e) {
		// keine JSON-Antwort
	}

	// unter 500 steht eine PHP-Meldung vor dem JSON: die Datei ist kaputt, Warten hilft nicht
	if (antwort.status < 500) {
		throw new Error(
			`getCisConfig antwortet nach dem Profilwechsel mit HTTP ${antwort.status}: ${text.slice(0, 300)}`,
		);
	}
	return { ist: null, grund: `getCisConfig antwortet mit HTTP ${antwort.status}` };
};

/** Wartet, bis getCisConfig das Profil liefert. PHP-FPM liest eine geänderte Datei erst nach opcache.revalidate_freq. */
const pruefeInstanz = async (daten) => {
	const erwartet = { ...daten.config, ...daten.flags, ...daten.pruefe };
	const basisUrl = String(process.env.BASE_URL).replace(/\/+$/, "");
	const url = `${basisUrl}/index.ci.php/api/frontend/v1/Noten/getCisConfig`;
	const auth = `Basic ${Buffer.from(`${process.env.USER_NAME}:${process.env.USER_PASSWORD}`).toString("base64")}`;
	const ende = Date.now() + 60000;

	for (;;) {
		const { ist, grund } = await leseCisConfig(url, auth);
		let offen = grund;

		if (ist) {
			const pruefbar = Object.keys(erwartet).filter((key) => key in ist);
			if (!pruefbar.length) {
				console.warn("[profil] getCisConfig liefert keinen Schlüssel des Profils. Der Wechsel ist ungeprüft.");
				return;
			}

			const abweichend = pruefbar.filter((key) => !gleich(ist[key], erwartet[key]));
			if (!abweichend.length) return;

			const liste = abweichend.map(
				(k) => `  ${k}: ${JSON.stringify(ist[k])} statt ${JSON.stringify(erwartet[k])}`,
			);
			offen = `die Instanz zeigt das Profil nicht:\n${liste.join("\n")}`;
		}

		if (Date.now() > ende) throw new Error(`Profilwechsel nach 60 Sekunden ungeprüft, ${offen}`);
		await new Promise((resolve) => setTimeout(resolve, 2000));
	}
};

// --- öffentlich ---

/** Nimmt das aktive Profil zurück. Liefert seinen Namen oder null. */
const wiederherstellen = async () => {
	if (!sshKonfiguriert()) return null;

	return mitSftp(async (sftp) => {
		const zustand = await leseZustand(sftp);
		if (!zustand || !Object.keys(zustand.dateien).length) return null;

		const war = zustand.profil;
		for (const schluessel of Object.keys(DATEIEN)) await stelleHer(sftp, zustand, schluessel);

		zustand.profil = null;
		await schreibeZustand(sftp, zustand);
		console.log(`[profil] "${war}" ist zurückgenommen.`);
		return war;
	});
};

/** Schaltet die Instanz auf ein Profil. Ohne Namen stellt es die Originale her. */
const anwenden = async (name) => {
	if (!name) return wiederherstellen();

	const daten = profilDaten(name);
	const root = envWert("REMOTE_ROOT");
	if (!sshKonfiguriert() || !root) {
		throw new Error(
			`Profil "${name}" braucht ${PREFIX}_SSH_HOST, ${PREFIX}_SSH_USER und ${PREFIX}_REMOTE_ROOT in tests/cypress/.env.`,
		);
	}

	await mitSftp(async (sftp) => {
		if (!(await existiert(sftp, SICHERUNG))) await sftpAufruf((cb) => sftp.mkdir(SICHERUNG, { mode: 0o700 }, cb));

		const zustand = (await leseZustand(sftp)) || { dateien: {} };
		if (zustand.root && zustand.root !== root && Object.keys(zustand.dateien).length) {
			throw new Error(
				`Auf ${zustand.root} ist noch "${zustand.profil}" aktiv. Zuerst: npm run noten:profil -- wiederherstellen`,
			);
		}
		zustand.root = root;
		zustand.profil = name;

		if (Object.keys(daten.config).length)
			await setze(sftp, zustand, "config", (o) => mitConfig(o, name, daten.config));
		else await stelleHer(sftp, zustand, "config");

		if (Object.keys(daten.flags).length) await setze(sftp, zustand, "flags", (o) => mitFlags(o, daten.flags));
		else await stelleHer(sftp, zustand, "flags");

		await schreibeZustand(sftp, zustand);
	});

	await pruefeInstanz(daten);
	console.log(`[profil] "${name}" ist aktiv.`);
	return name;
};

const status = () => (sshKonfiguriert() ? mitSftp(leseZustand) : Promise.resolve(null));

module.exports = { anwenden, wiederherstellen, status, profilDaten };
