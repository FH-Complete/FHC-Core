#!/usr/bin/env node
/**
 * Configuration profiles for the grading tool suite, via SFTP on a c3p0 instance.
 *
 *   node tests/cypress/local/notenProfiles.js status                  Profiles and the active profile
 *   node tests/cypress/local/notenProfiles.js apply <profile>         For example, before running `cypress open`
 *   node tests/cypress/local/notenProfiles.js restore                 restore the originals
 *   node tests/cypress/local/notenProfiles.js run [api|ui|all] [a,b]  one run per profile, followed by the report
 *
 * The profiles are located in tests/cypress/profiles/noten.js. The `run` command opens the SSH tunnel itself.
 */

const path = require("path");

["local", "suites", "."].forEach((dir) => require("dotenv").config({ path: path.join(__dirname, "..", dir, ".env") }));

const profileSwitch = require("./profileSwitch");
const { openDbTunnel } = require("./withTunnel");
const { profiles } = require("../profiles/noten");

const SCOPES = {
	api: { spec: "tests/cypress/e2e/specs/{unit,api}/**/*.cy.js" },
	// Electron crashes on the grading tool page
	ui: { spec: "tests/cypress/e2e/specs/ui/**/*.cy.js", browser: "chrome" },
	all: { spec: "tests/cypress/e2e/specs/**/*.cy.js", browser: "chrome" },
};

const NOT_RUN = ["pending", "skipped"];

const testTitle = (run, test) => `${path.basename(run.spec.relative)} › ${test.title.join(" › ")}`;

const column = (value, width) => String(value).padStart(width);

/** Numbers per profile, the red tests per profile, and the tests that aren't running in any profile. */
const report = (names, results) => {
	const tests = new Map();
	const completed = [];
	let failed = false;

	console.log(`\n${"=".repeat(78)}\n${"Profil".padEnd(20)}  Tests    grün     rot  pending\n${"-".repeat(78)}`);

	names.forEach((name) => {
		const result = results[name];
		if (!result || !Array.isArray(result.runs)) {
			failed = true;
			console.log(`${name.padEnd(20)}  Lauf gescheitert: ${result ? result.message : "kein Ergebnis"}`);
			return;
		}

		completed.push(name);
		const counts = { passed: 0, failed: 0, pending: 0, skipped: 0 };
		result.runs.forEach((run) =>
			run.tests.forEach((test) => {
				counts[test.state] = (counts[test.state] || 0) + 1;
				const key = testTitle(run, test);
				if (!tests.has(key)) tests.set(key, {});
				tests.get(key)[name] = test;
			}),
		);

		if (counts.failed) failed = true;
		const total = counts.passed + counts.failed + counts.pending + counts.skipped;
		console.log(
			`${name.padEnd(20)}  ${column(total, 5)}  ${column(counts.passed, 6)}  ${column(counts.failed, 6)}  ${column(counts.pending + counts.skipped, 7)}`,
		);
	});

	completed.forEach((name) => {
		const failures = [...tests.entries()].filter(
			([, byProfile]) => byProfile[name] && byProfile[name].state === "failed",
		);
		if (!failures.length) return;

		console.log(`\nRot in "${name}":`);
		failures.forEach(([key, byProfile]) =>
			console.log(`  - ${key}\n      ${String(byProfile[name].displayError || "").split("\n")[0]}`),
		);
	});

	const neverRun = [...tests.keys()].filter((key) =>
		completed.every((name) => !tests.get(key)[name] || NOT_RUN.includes(tests.get(key)[name].state)),
	);
	console.log(`\nIn keinem Profil gelaufen (${neverRun.length}):`);
	neverRun.forEach((key) => console.log(`  - ${key}`));
	console.log("=".repeat(78));

	return failed ? 1 : 0;
};

const runProfiles = async (scope = "api", selection = null) => {
	const options = SCOPES[scope];
	if (!options) {
		console.error(`Unbekannter Umfang "${scope}". Erlaubt: ${Object.keys(SCOPES).join(", ")}`);
		return 2;
	}

	const names = selection
		? selection
				.split(",")
				.map((n) => n.trim())
				.filter(Boolean)
		: Object.keys(profiles);
	names.forEach((name) => profileSwitch.profileData(name));

	const cypress = require("cypress");
	const results = {};
	const closeTunnel = await openDbTunnel();

	try {
		for (const name of names) {
			console.log(`\n### Profil "${name}": ${profiles[name].description}\n`);
			try {
				await profileSwitch.apply(name);
				results[name] = await cypress.run(options);
			} catch (error) {
				// ein gescheiterter Profilwechsel beendet nur diesen Lauf; der Bericht nennt den Grund
				results[name] = { message: error.message };
			}
		}
	} finally {
		await profileSwitch.restore();
		await closeTunnel();
	}

	return report(names, results);
};

const main = async () => {
	const [command, first, second] = process.argv.slice(2);

	if (command === "run") return runProfiles(first, second);

	if (command === "apply" && first) {
		await profileSwitch.apply(first);
		return 0;
	}

	if (command === "restore") {
		if (!(await profileSwitch.restore())) console.log("Kein Profil war aktiv.");
		return 0;
	}

	if (command === "status") {
		const state = await profileSwitch.status();
		console.log(state && state.profile ? `\nAktiv auf ${state.root}: "${state.profile}"` : "\nKein Profil aktiv.");
		console.log("\nProfile:");
		Object.entries(profiles).forEach(([name, p]) => console.log(`  ${name.padEnd(18)} ${p.description}`));
		console.log("");
		return 0;
	}

	console.error(
		"\n  node tests/cypress/local/notenProfiles.js status | apply <profile> | restore | run [api|ui|all] [profile,profile]\n",
	);
	return 2;
};

main()
	.then((code) => process.exit(code))
	.catch((error) => {
		console.error(`\n${error.message}\n`);
		process.exit(1);
	});
