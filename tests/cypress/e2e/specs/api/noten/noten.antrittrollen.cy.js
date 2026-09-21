/**
 * From which attempt the exam is kommissionell.
 *
 * The number comes from CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT, not from this test. Checks that
 * the built chain follows it: role -> legacy type -> the `kommissionell` flag.
 */

import { expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { skipIf } from "../../../../support/helpers/notenConfig";
import { attemptDate, loadNotenContext, requireDbReset } from "../../../../support/helpers/notenTestData";
import {
	addPruefung,
	attemptsOfStudent,
	givenBaseline,
	readStateViaApi,
} from "../../../../support/helpers/notenScenario";

describe("Noten API - ab welchem Antritt kommissionell", () => {
	let ctx;
	let fromAntritt; // null means no Antritt is kommissionell

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
			fromAntritt = context.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT ?? null;
			cy.log(`KOMMISSIONELL_AB_ANTRITT = ${fromAntritt} maxAntritte = ${context.maxAntritte}`);
		});
	});

	beforeEach(() => requireDbReset());

	it("liefert eine Antrittsnummer, die zur Kette passt", function () {
		skipIf(this, fromAntritt === null, "Übersprungen: kein Antritt ist kommissionell.");

		expect(fromAntritt, "die Nummer ist eine Zahl ab 1").to.be.a("number").and.to.be.at.least(1);
		expect(fromAntritt, "sie liegt innerhalb der Kette").to.be.at.most(ctx.maxAntritte);
	});

	it("baut die Kette nach der konfigurierten Nummer", function () {
		skipIf(
			this,
			ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF === false,
			"Übersprungen: das Werkzeug darf den letzten Antritt nicht anlegen.",
		);

		const student = ctx.students[0];
		expect(student, "ein Student der LV").to.exist;

		// The base step produces step 1. A negative note keeps the chain open.
		givenBaseline(ctx, student, { note: ctx.notes.negativ });

		for (let nr = 2; nr <= ctx.maxAntritte; nr += 1) {
			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: attemptDate(ctx, nr - 1) }).then((response) =>
				expectNotenSuccess(response, `Antritt ${nr}`),
			);
		}

		readStateViaApi(ctx).then((data) => {
			const attempts = attemptsOfStudent(data, student.uid);
			expect(attempts.length, "die Kette ist vollständig").to.eq(ctx.maxAntritte);

			attempts.forEach((termin, index) => {
				const nr = index + 1;
				const expected = fromAntritt !== null && nr >= fromAntritt;

				expect(termin.kommissionell, `Antritt ${nr} kommissionell?`).to.eq(expected);
			});
		});
	});
});
