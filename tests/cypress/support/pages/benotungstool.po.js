import { notenAuth } from "../api/notenApi";
import { waitForOk } from "../helpers/network";

/**
 * Page object for the grading tool.
 *
 * Selectors are data-cy attributes from Benotungstool.js. The table is a tabulator: rows contain
 * data-cy=“student-row-<uid>”, cells contain the tabulator-field set by the tabulator, and the
 * approval status is stored as data-state in the cell content rather than in the icon.
 *
 * After every write operation, the system waits for the corresponding request (waitForOk), not for
 * a DOM change. Otherwise, the specs would check against the state prior to the response. The submit methods
 * do not wait; a test uses them to check for rejection by the server.
 */

const TABLE_TIMEOUT = 60_000;
const API = "**/api/frontend/v1/Noten";

/**
 * Exact text comparison for option lists. This is necessary because the grade labels are subsets of one another:
 * contains(“Gut”) matches “Sehr Gut” first, and contains(‘Genügend’) matches “Nicht Genügend” first.
 */
const exactText = (text) => new RegExp(`^\\s*${String(text).replace(/[.*+?^${}()|[\]\\]/g, "\\$&")}\\s*$`);

class BenotungstoolPage {
	selectors = {
		table: "[data-cy='benotungstool-table']",
		row: "[data-cy^='student-row-']",
	};

	setupIntercepts = () => {
		cy.intercept({ method: "GET", url: `${API}/getStudentenNoten*` }).as("getStudentenNoten");
		cy.intercept({ method: "GET", url: `${API}/getCisConfig*` }).as("getCisConfig");
		cy.intercept({ method: "POST", url: `${API}/saveNotenvorschlag` }).as("saveNotenvorschlag");
		cy.intercept({ method: "POST", url: `${API}/saveNotenvorschlagBulk` }).as("saveNotenvorschlagBulk");
		cy.intercept({ method: "POST", url: `${API}/saveStudentPruefung` }).as("saveStudentPruefung");
		cy.intercept({ method: "POST", url: `${API}/createPruefungen` }).as("createPruefungen");
		cy.intercept({ method: "POST", url: `${API}/savePruefungenBulk` }).as("savePruefungenBulk");
		cy.intercept({ method: "POST", url: `${API}/saveStudentenNoten` }).as("saveStudentenNoten");
		cy.intercept({ method: "POST", url: `${API}/getNoteByPunkte` }).as("getNoteByPunkte");
	};

	/**
	 * Deep link to LV + Semester. The four dropdown menus do not need to be used for this.
	 * The column layout is set before loading: the component reads localStorage during
	 * initialization, and before the first visit, it still belongs to about:blank.
	 */
	visit = (ctx, { columns = "antritt" } = {}) =>
		cy.visit(`/cis.php/Cis/Benotungstool/${ctx.semKurzbz}/${ctx.lvId}`, {
			onBeforeLoad(win) {
				win.localStorage.setItem("notenToolPruefungsspalten", columns);
				win.localStorage.removeItem("notenToolStickyCols");
			},
		});

	visitAndWaitForTable = (ctx, options) => {
		const { username, password } = notenAuth();
		cy.login(username, password);
		this.setupIntercepts();
		this.visit(ctx, options);

		waitForOk("@getStudentenNoten");
		this.getTable().should("be.visible");
		this.getRows().should("have.length.greaterThan", 0);
	};

	// --- Grundelemente ---------------------------------------------------------------------------

	getTable = () => cy.get(this.selectors.table, { timeout: TABLE_TIMEOUT });
	getRows = () => cy.get(this.selectors.row, { timeout: TABLE_TIMEOUT });
	getRow = (uid) => cy.get(`[data-cy='student-row-${uid}']`, { timeout: TABLE_TIMEOUT });
	getCell = (uid, field) => this.getRow(uid).find(`[tabulator-field='${field}']`);

	getFreigabeState = (uid) => this.getCell(uid, "freigegeben").find("[data-cy='freigabe-state']");
	getPruefungCell = (uid, column) => this.getCell(uid, column).find("[data-cy='pruefung-cell']");
	getUebernehmenButton = (uid) => this.getCell(uid, "übernehmen").find("[data-cy='btn-uebernehmen']");
	getPruefungAddButton = (uid, column) => this.getCell(uid, column).find("[data-cy='btn-pruefung-add']");
	getPruefungEditButton = (uid, column) => this.getCell(uid, column).find("[data-cy='btn-pruefung-edit']");
	getBestandenHint = (uid, column) => this.getCell(uid, column).find("[data-cy='pruefung-bestanden']");

	getPruefungModal = () => cy.get("[data-cy='modal-pruefung']");
	getNewPruefungModal = () => cy.get("[data-cy='modal-neue-pruefung']");
	getFreigabeModal = () => cy.get("[data-cy='modal-freigabe']");
	getUebernahmeModal = () => cy.get("[data-cy='modal-uebernahme']");
	getNotenImportModal = () => cy.get("[data-cy='modal-noten-import']");
	getPruefungImportModal = () => cy.get("[data-cy='modal-pruefung-import']");
	getFreigabeSummaryRow = (uid) => cy.get(`[data-cy='freigabe-row-${uid}']`);

	// --- Zustand prüfen --------------------------------------------------------------------------

	/** offen | changed | ok */
	expectFreigabeState = (uid, state) => this.getFreigabeState(uid).should("have.attr", "data-state", state);

	expectLvNote = (uid, bezeichnung) => this.getCell(uid, "lv_note").should("contain.text", bezeichnung);

	expectNotenvorschlag = (uid, bezeichnung) =>
		this.getCell(uid, "note_vorschlag").should("contain.text", bezeichnung);

	/** centeredTextFormatter prüft auf falsy, eine 0 rendert daher als leere Zelle. */
	expectAntrittCount = (uid, count) =>
		Number(count) === 0
			? this.getCell(uid, "hoechsterAntritt").invoke("text").invoke("trim").should("eq", "")
			: this.getCell(uid, "hoechsterAntritt").should("contain.text", String(count));

	expectPruefung = (uid, column, { note, antritt } = {}) => {
		// je Assertion neu abfragen, damit Cypress ein Re-Render der Zelle erneut versucht
		if (note !== undefined) {
			this.getPruefungCell(uid, column).should("have.attr", "data-note", String(note));
		}
		if (antritt !== undefined) {
			this.getPruefungCell(uid, column).should("have.attr", "data-attempt", String(antritt));
		}
	};

	expectNoPruefung = (uid, column) => this.getCell(uid, column).find("[data-cy='pruefung-cell']").should("not.exist");

	expectNoAntrittColumn = (nr) => cy.get(`[tabulator-field='antritt_${nr}']`).should("not.exist");

	/**
	 * The server refuses to save the data, and a toast message displays its message. The message comes from
	 * the response, so the test does not set a phrase.
	 */
	expectRejected = (alias) =>
		cy.wait(alias).then(({ response }) => {
			expect(response.statusCode, `${alias} lehnt ab`).to.not.eq(200);
			const errorMessage = (response.body?.errors ?? [])[0]?.message;
			expect(errorMessage, `Meldung von ${alias}`).to.be.a("string").and.not.be.empty;
			cy.get(".p-toast-message", { timeout: TABLE_TIMEOUT }).should("contain.text", errorMessage);
		});

	/** A collection path rejects a row: HTTP 200; the message is stored in data[uid] and in the toast. */
	expectRowRejected = (alias, uid) =>
		cy.wait(alias).then(({ response }) => {
			expect(response.statusCode, `${alias} antwortet`).to.eq(200);
			const errorMessage = response.body?.data?.[uid];
			expect(errorMessage, `Ablehnung der Zeile ${uid}`).to.be.a("string").and.not.be.empty;
			cy.get(".p-toast-message", { timeout: TABLE_TIMEOUT }).should("contain.text", errorMessage);
		});

	/** After an action is completely rejected, the interface reports that it was unsuccessful. */
	expectNoSuccess = () => cy.get(".p-toast-message-success").should("not.exist");

	/** A client warning. Warnings do not have an expiration time. */
	expectWarning = (text) => cy.get(".p-toast-message-warn", { timeout: TABLE_TIMEOUT }).should("contain.text", text);

	expectWarnings = (minCount) =>
		cy.get(".p-toast-message-warn", { timeout: TABLE_TIMEOUT }).should("have.length.at.least", minCount);

	/** The UIDs of the last request in a collection path. Field: “noten” or “pruefungen” */
	sentUids = (alias, bodyField) =>
		cy
			.get(alias)
			.its(`request.body.${bodyField}`)
			.then((sentRows) => sentRows.map((z) => z.uid));

	// --- Notenvorschlag --------------------------------------------------------------------------

	/** Opens the tab-separated list editor for the suggestion column and selects the label. */
	setNotenvorschlag = (uid, bezeichnung) => {
		this.getCell(uid, "note_vorschlag").click();
		cy.get(".tabulator-edit-list-item").contains(bezeichnung).click();
	};

	/** “Übernehmen” first asks for the grading date. If `datum` is omitted, the dialog's default value is used. */
	submitUebernahme = (uid, { datum } = {}) => {
		this.closeToasts();
		this.getUebernehmenButton(uid).click();
		this.getUebernahmeModal().should("be.visible");

		if (datum) this.setDate("uebernahme-datum", datum);

		cy.get("[data-cy='uebernahme-submit']").click();
	};

	uebernehmen = (uid, options) => {
		this.submitUebernahme(uid, options);
		waitForOk("@saveNotenvorschlag");
		this.getUebernahmeModal().should("not.be.visible");
	};

	// --- Prüfungen -------------------------------------------------------------------------------

	/** PrimeVue attaches its panels to the `body`, so don't search inside the modal. */
	selectDropdownOption = (dataCy, label) => {
		cy.get(`[data-cy='${dataCy}']`).click();
		cy.contains(".p-dropdown-panel .p-dropdown-item", exactText(label)).click();
	};

	setDate = (dataCy, ddmmyyyy) => cy.get(`[data-cy='${dataCy}'] input`).first().clear().type(`${ddmmyyyy}{enter}`);

	/**
	 * After each save, the tool displays a success toast with no timeout. It is positioned above the modal
	 * (z-index 100001) and covers its buttons, which is why the test closes it using its button.
	 */
	closeToasts = () =>
		cy
			.get("body")
			.then(($body) => {
				const buttons = $body.find(".p-toast-message .p-toast-icon-close");
				if (buttons.length) cy.wrap(buttons).click({ multiple: true });
			})
			.then(() => cy.get(".p-toast-message").should("not.exist"));

	/** Dialog from the table cell: new enrollment for ONE student. */
	submitPruefungInCell = (uid, column, { note, datum } = {}) => {
		this.closeToasts();
		this.getPruefungAddButton(uid, column).click();
		this.getPruefungModal().should("be.visible");

		if (datum) this.setDate("pruefung-datum", datum);
		if (note) this.selectDropdownOption("pruefung-note", note);

		cy.get("[data-cy='pruefung-submit']").click();
	};

	addPruefungInCell = (uid, column, options) => {
		this.submitPruefungInCell(uid, column, options);
		waitForOk("@saveStudentPruefung");
		this.getPruefungModal().should("not.be.visible");
	};

	/** Edit an existing Antrittsdatum. If `note` is omitted, the start date remains unchanged (date correction). */
	editPruefungInCell = (uid, column, { note, datum } = {}) => {
		this.closeToasts();
		this.getPruefungEditButton(uid, column).click();
		this.getPruefungModal().should("be.visible");

		if (datum) this.setDate("pruefung-datum", datum);
		if (note) this.selectDropdownOption("pruefung-note", note);

		cy.get("[data-cy='pruefung-submit']").click();
		waitForOk("@saveStudentPruefung");
		this.getPruefungModal().should("not.be.visible");
	};

	openPruefungModalForEdit = (uid, column) => {
		this.closeToasts();
		this.getPruefungEditButton(uid, column).click();
		this.getPruefungModal().should("be.visible");
	};

	/** Submit the batch request via import modal without waiting for a response. */
	submitPruefungBulk = ({ uids, note, punkte, datum }) => {
		this.closeToasts();
		cy.get("[data-cy='btn-neue-pruefung']").click();
		this.getNewPruefungModal().should("be.visible");

		if (datum) this.setDate("neue-pruefung-datum", datum);
		if (note) this.selectDropdownOption("neue-pruefung-note", note);
		if (punkte !== undefined) this.setNewPruefungPunkte(punkte);

		cy.get("[data-cy='neue-pruefung-studenten']").click();
		// The label is “uid – Last Name First Name – Appearances: n”
		uids.forEach((uid) => cy.contains(".p-multiselect-panel .p-multiselect-item", uid).click());

		// DO NOT use the Escape key: that closes the Bootstrap modal as well. The open panel may cover the trigger,
		// so use the panel's close button instead.
		cy.get(".p-multiselect-panel .p-multiselect-close").click();
		cy.get(".p-multiselect-panel").should("not.exist");

		cy.get("[data-cy='neue-pruefung-submit']").click();
	};

	/** Bulk insert for multiple students. */
	addPruefungBulk = (options) => {
		this.submitPruefungBulk(options);
		waitForOk("@createPruefungen");
		this.getNewPruefungModal().should("not.be.visible");
	};

	// --- Freigabe --------------------------------------------------------------------------------

	openFreigabeModal = () => {
		cy.get("[data-cy='btn-freigabe']").click();
		this.getFreigabeModal().should("be.visible");
	};

	expectFreigabeSummaryRow = (uid, releasedBezeichnung) =>
		this.getFreigabeSummaryRow(uid)
			.find("[data-cy='freigabe-row-released']")
			.should("contain.text", releasedBezeichnung);

	typeFreigabePassword = (password) => cy.get("[data-cy='freigabe-passwort'] input").type(password, { log: false });

	submitFreigabe = () => cy.get("[data-cy='freigabe-submit']").click();

	freigeben = (password) => {
		this.typeFreigabePassword(password);
		this.submitFreigabe();
		waitForOk("@saveStudentenNoten");
		this.getFreigabeModal().should("not.be.visible");
	};

	// --- Import ----------------------------------------------------------------------------------

	/** rows: [[uid, note], ...] -> "uid<TAB>note" per row */
	submitNotenImport = (rows) => {
		this.closeToasts();
		cy.get("[data-cy='btn-noten-import']").click();
		this.getNotenImportModal().should("be.visible");

		cy.get("[data-cy='noten-import-text']").type(rows.map((r) => r.join("\t")).join("\n"));
		cy.get("[data-cy='noten-import-submit']").click();
	};

	importNoten = (rows) => {
		this.submitNotenImport(rows);
		waitForOk("@saveNotenvorschlagBulk");
		this.getNotenImportModal().should("not.be.visible");
	};

	/** rows: [[uid, "dd.MM.yyyy", note], ...] */
	submitPruefungImport = (rows) => {
		this.closeToasts();
		cy.get("[data-cy='btn-pruefung-import']").click();
		this.getPruefungImportModal().should("be.visible");

		cy.get("[data-cy='pruefung-import-text']").type(rows.map((r) => r.join("\t")).join("\n"));
		cy.get("[data-cy='pruefung-import-submit']").click();
	};

	importPruefungen = (rows) => {
		this.submitPruefungImport(rows);
		waitForOk("@savePruefungenBulk");
		this.getPruefungImportModal().should("not.be.visible");
	};

	// --- Punktemodus -----------------------------------------------------------------------------
	// Die Punktespalte und die beiden Punktefelder in den Dialogen existieren nur mit
	// CIS_GESAMTNOTE_PUNKTE. Die Note wird dann aus dem Notenschlüssel abgeleitet, nicht gewählt.

	getPunkteCell = (uid) => this.getCell(uid, "punkte");

	/**
	 * Punkte in die Zelle tippen; die Note holt der Client debounced über getNoteByPunkte nach.
	 *
	 * Über cy.focused(): der liveNumberEditor fokussiert sein Input in onRendered, und die Zeile
	 * wird beim Klick neu formatiert (cellClick -> undoSelection), sodass ein zweiter Zugriff über
	 * die Zelle ins Leere greifen kann.
	 */
	setPunkteInCell = (uid, punkte) => {
		this.getPunkteCell(uid).click();

		// Über cy.focused(), weil der liveNumberEditor sein Input in onRendered fokussiert und die
		// Zeile beim Klick neu formatiert wird. Enter committet den Wert (success() im Editor);
		// die Note holt der Client danach debounced über getNoteByPunkte nach - deren Rendern
		// schliesst den Editor, ein Blur danach ginge ins Leere.
		cy.focused().should("have.attr", "type", "number").clear().type(`${punkte}{enter}`);
		waitForOk("@getNoteByPunkte");
	};

	expectPunkte = (uid, punkte) => this.getPunkteCell(uid).should("contain.text", String(punkte));

	/** Sobald ein Termin existiert, ist die Punktespalte gesperrt (editable-Guard der Spalte). */
	expectPunkteCellLocked = (uid) => {
		this.getPunkteCell(uid).click();
		this.getPunkteCell(uid).find("input").should("not.exist");
	};

	/** Im Punktemodus ist die Vorschlagsspalte nicht editierbar - die Note kommt aus den Punkten. */
	expectNotenvorschlagLocked = (uid) => {
		this.getCell(uid, "note_vorschlag").click();
		cy.get(".tabulator-edit-list").should("not.exist");
	};

	expectNoteFieldLocked = (dataCy) => cy.get(`[data-cy='${dataCy}']`).should("have.class", "p-disabled");

	/** Punktefeld im Einzeldialog; löst die Ableitung der Note aus. */
	setPruefungPunkte = (punkte) => {
		cy.get("[data-cy='pruefung-punkte'] input").clear().type(String(punkte));
		waitForOk("@getNoteByPunkte");
	};

	/** Punktefeld der Sammelanlage; dort leitet erst der Server beim Speichern ab. */
	setNewPruefungPunkte = (punkte) => cy.get("[data-cy='neue-pruefung-punkte'] input").clear().type(String(punkte));

	// --- Hilfen ----------------------------------------------------------------------------------

	/** Die Bezeichnung zu einer Noten-PK, wie sie in Dropdowns und Zellen steht. */
	bezeichnungOf = (ctx, note) => (ctx.notenOptions ?? []).find((n) => String(n.note) === String(note))?.bezeichnung;

	toDDMMYYYY = (isoDate) => {
		const [y, m, d] = isoDate.split("-");
		return `${d}.${m}.${y}`;
	};

	/** Date in the configured import format; kept apart from toDDMMYYYY, which the dialog uses. */
	importDate = (isoDate, format) => (format === "yyyy-MM-dd" ? isoDate : this.toDDMMYYYY(isoDate));
}

export const benotungstoolPage = new BenotungstoolPage();
