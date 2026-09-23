import { waitForOk } from "../helpers/network";

/**
 * Page object for the Benotungstool page.
 *
 * Selectors are the data-cy attributes from Benotungstool.js. The table is a Tabulator table: a row has
 * data-cy='student-row-<uid>', a cell has the attribute tabulator-field, and the Freigabe state is the
 * data-state attribute of the cell content, not the icon.
 *
 * After each write action a method waits for its request (waitForOk), not for the DOM. Otherwise a spec
 * checks the state before the response. The submit* methods do not wait: a test uses them to check
 * that the server rejects the input.
 */

const TABLE_TIMEOUT = 60_000;
const API = "**/api/frontend/v1/Noten";

/**
 * Exact text match for option lists. The Bezeichnungen contain each other:
 * contains("Gut") finds "Sehr Gut" first, and contains("Genügend") finds "Nicht Genügend" first.
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
		cy.intercept({ method: "POST", url: `${API}/saveLvNote` }).as("saveLvNote");
		cy.intercept({ method: "POST", url: `${API}/importLvNoten` }).as("importLvNoten");
		cy.intercept({ method: "POST", url: `${API}/savePruefung` }).as("savePruefung");
		cy.intercept({ method: "POST", url: `${API}/createPruefungen` }).as("createPruefungen");
		cy.intercept({ method: "POST", url: `${API}/importPruefungen` }).as("importPruefungen");
		cy.intercept({ method: "POST", url: `${API}/saveFreigabe` }).as("saveFreigabe");
		cy.intercept({ method: "POST", url: `${API}/getNoteByPunkte` }).as("getNoteByPunkte");
	};

	/**
	 * Opens the page by deep link to semester and LV, without the four dropdowns.
	 * The column layout goes into localStorage in onBeforeLoad: the component reads it at start,
	 * and before the first visit localStorage belongs to about:blank.
	 */
	visit = (ctx, { columns = "antritt" } = {}) =>
		cy.visit(`/cis.php/Cis/Benotungstool/${ctx.semKurzbz}/${ctx.lvId}`, {
			onBeforeLoad(win) {
				win.localStorage.setItem("notenToolPruefungsspalten", JSON.stringify(columns));
				win.localStorage.removeItem("notenToolStickyCols");
			},
		});

	/** Needs the session of loginAsLektor() from the beforeEach of the spec. */
	visitAndWaitForTable = (ctx, options) => {
		this.setupIntercepts();
		this.visit(ctx, options);

		waitForOk("@getStudentenNoten");
		this.getTable().should("be.visible");
		this.getRows().should("have.length.greaterThan", 0);
	};

	// --- Elements --------------------------------------------------------------------------------

	getTable = () => cy.get(this.selectors.table, { timeout: TABLE_TIMEOUT });
	getRows = () => cy.get(this.selectors.row, { timeout: TABLE_TIMEOUT });
	getRow = (uid) => cy.get(`[data-cy='student-row-${uid}']`, { timeout: TABLE_TIMEOUT });
	getCell = (uid, field) => this.getRow(uid).find(`[tabulator-field='${field}']`);

	getFreigabeState = (uid) => this.getCell(uid, "freigabe_state").find("[data-cy='freigabe-state']");
	getPruefungCell = (uid, column) => this.getCell(uid, column).find("[data-cy='pruefung-cell']");
	getApplyProposalButton = (uid) => this.getCell(uid, "apply_proposal").find("[data-cy='btn-apply-proposal']");
	getPruefungAddButton = (uid, column) => this.getCell(uid, column).find("[data-cy='btn-pruefung-add']");
	getPruefungEditButton = (uid, column) => this.getCell(uid, column).find("[data-cy='btn-pruefung-edit']");
	getBestandenHint = (uid, column) => this.getCell(uid, column).find("[data-cy='pruefung-bestanden']");
	getRowCheckbox = (uid) => this.getCell(uid, "selectCol").find("input");

	getPruefungModal = () => cy.get("[data-cy='modal-pruefung']");
	getFreigabeModal = () => cy.get("[data-cy='modal-freigabe']");
	getApplyProposalModal = () => cy.get("[data-cy='modal-apply-proposal']");
	getLvNotenImportModal = () => cy.get("[data-cy='modal-lvnoten-import']");
	getPruefungImportModal = () => cy.get("[data-cy='modal-pruefung-import']");
	getFreigabeSummaryRow = (uid) => cy.get(`[data-cy='freigabe-row-${uid}']`);

	// --- Assertions ------------------------------------------------------------------------------

	/** open | changed | freigegeben */
	expectFreigabeState = (uid, state) => this.getFreigabeState(uid).should("have.attr", "data-state", state);

	expectLvNote = (uid, bezeichnung) => this.getCell(uid, "lv_note").should("contain.text", bezeichnung);

	expectProposal = (uid, bezeichnung) => this.getCell(uid, "proposed_note").should("contain.text", bezeichnung);

	/** The column shows verlauf.antrittCount, a 0 included. */
	expectAntrittCount = (uid, count) =>
		this.getCell(uid, "verlauf.antrittCount").invoke("text").invoke("trim").should("eq", String(count));

	expectPruefung = (uid, column, { note, antritt } = {}) => {
		// query again for each assertion, so Cypress retries after a re-render of the cell
		if (note !== undefined) {
			this.getPruefungCell(uid, column).should("have.attr", "data-note", String(note));
		}
		if (antritt !== undefined) {
			this.getPruefungCell(uid, column).should("have.attr", "data-antritt", String(antritt));
		}
	};

	expectNoPruefung = (uid, column) => this.getCell(uid, column).find("[data-cy='pruefung-cell']").should("not.exist");

	expectNoAntrittColumn = (nr) => cy.get(`[tabulator-field='antritt_${nr}']`).should("not.exist");

	/** The table holds the selection: the row class and the checkbox in the first column. */
	expectRowSelected = (uid, selected) => {
		this.getRow(uid).should(selected ? "have.class" : "not.have.class", "tabulator-selected");
		this.getRowCheckbox(uid).should(selected ? "be.checked" : "not.be.checked");
	};

	/**
	 * The server rejects the request, and a toast shows its message. The message comes from the
	 * response, so the test needs no phrase.
	 */
	expectRejected = (alias) =>
		cy.wait(alias).then(({ response }) => {
			expect(response.statusCode, `${alias} lehnt ab`).to.not.eq(200);
			const errorMessage = (response.body?.errors ?? [])[0]?.message;
			expect(errorMessage, `Meldung von ${alias}`).to.be.a("string").and.not.be.empty;
			cy.get(".p-toast-message", { timeout: TABLE_TIMEOUT }).should("contain.text", errorMessage);
		});

	/** A bulk endpoint rejects one row: HTTP 200, the message is in data[uid].error and in the toast. */
	expectRowRejected = (alias, uid) =>
		cy.wait(alias).then(({ response }) => {
			expect(response.statusCode, `${alias} antwortet`).to.eq(200);
			const errorMessage = response.body?.data?.[uid]?.error?.message;
			expect(errorMessage, `Ablehnung der Zeile ${uid}`).to.be.a("string").and.not.be.empty;
			cy.get(".p-toast-message", { timeout: TABLE_TIMEOUT }).should("contain.text", errorMessage);
		});

	/** After a fully rejected action the page shows no success toast. */
	expectNoSuccess = () => cy.get(".p-toast-message-success").should("not.exist");

	/** A warning from the client. Warnings stay open until closed. */
	expectWarning = (text) => cy.get(".p-toast-message-warn", { timeout: TABLE_TIMEOUT }).should("contain.text", text);

	expectWarnings = (minCount) =>
		cy.get(".p-toast-message-warn", { timeout: TABLE_TIMEOUT }).should("have.length.at.least", minCount);

	/** The uids that the last bulk request sent. bodyField: "lv_noten" or "pruefungen". */
	sentUids = (alias, bodyField) =>
		cy
			.get(alias)
			.its(`request.body.${bodyField}`)
			.then((sentRows) => sentRows.map((z) => z.uid));

	// --- Proposal --------------------------------------------------------------------------------

	/** Opens the list editor of the proposal column and selects the Bezeichnung. */
	setProposal = (uid, bezeichnung) => {
		this.getCell(uid, "proposed_note").click();
		cy.get(".tabulator-edit-list-item").contains(bezeichnung).click();
	};

	/** The apply button first asks for the Benotungsdatum. Without `datum` the dialog keeps its default. */
	submitApplyProposal = (uid, { datum } = {}) => {
		this.closeToasts();
		this.getApplyProposalButton(uid).click();
		this.getApplyProposalModal().should("be.visible");

		if (datum) this.setDate("apply-proposal-datum", datum);

		cy.get("[data-cy='apply-proposal-submit']").click();
	};

	applyProposal = (uid, options) => {
		this.submitApplyProposal(uid, options);
		waitForOk("@saveLvNote");
		this.getApplyProposalModal().should("not.be.visible");
	};

	// --- Pruefungen ------------------------------------------------------------------------------

	/** PrimeVue attaches its panels to `body`, so do not search inside the modal. */
	selectDropdownOption = (dataCy, label) => {
		cy.get(`[data-cy='${dataCy}']`).click();
		cy.contains(".p-dropdown-panel .p-dropdown-item", exactText(label)).click();
	};

	setDate = (dataCy, ddmmyyyy) => cy.get(`[data-cy='${dataCy}'] input`).first().clear().type(`${ddmmyyyy}{enter}`);

	/**
	 * After each save the page shows a success toast that stays open. It lies above the modal
	 * (z-index 100001) and covers its buttons, so the test closes it with its close button.
	 */
	closeToasts = () =>
		cy
			.get("body")
			.then(($body) => {
				const buttons = $body.find(".p-toast-message .p-toast-icon-close");
				if (buttons.length) cy.wrap(buttons).click({ multiple: true });
			})
			.then(() => cy.get(".p-toast-message").should("not.exist"));

	/** The dialog of a table cell: a new Pruefung for ONE student. */
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
		waitForOk("@savePruefung");
		this.getPruefungModal().should("not.be.visible");
	};

	/** Edits an existing Pruefung. Without `note` the Note stays, for example for a date correction. */
	editPruefungInCell = (uid, column, { note, datum } = {}) => {
		this.closeToasts();
		this.getPruefungEditButton(uid, column).click();
		this.getPruefungModal().should("be.visible");

		if (datum) this.setDate("pruefung-datum", datum);
		if (note) this.selectDropdownOption("pruefung-note", note);

		cy.get("[data-cy='pruefung-submit']").click();
		waitForOk("@savePruefung");
		this.getPruefungModal().should("not.be.visible");
	};

	openPruefungModalForEdit = (uid, column) => {
		this.closeToasts();
		this.getPruefungEditButton(uid, column).click();
		this.getPruefungModal().should("be.visible");
	};

	/** Opens the Pruefung dialog for the selection ("new Pruefung"). */
	openCreatePruefungen = () => {
		this.closeToasts();
		cy.get("[data-cy='btn-new-pruefung']").click();
		this.getPruefungModal().should("be.visible");
	};

	/** Clicks the students in the multiselect of the dialog. A click selects or deselects the row in the table. */
	toggleStudentsInDialog = (uids) => {
		cy.get("[data-cy='pruefung-students']").click();
		// the label is "uid – Nachname Vorname – Antritte: n"
		uids.forEach((uid) => cy.contains(".p-multiselect-panel .p-multiselect-item", uid).click());

		// Do NOT press Escape: it also closes the Bootstrap modal. The open panel can cover the trigger,
		// so use the close button of the panel.
		cy.get(".p-multiselect-panel .p-multiselect-close").click();
		cy.get(".p-multiselect-panel").should("not.exist");
	};

	/** The multiselect shows the selected students by their label, which starts with the uid. */
	expectStudentsInDialog = (uids) =>
		uids.forEach((uid) => cy.get("[data-cy='pruefung-students']").should("contain.text", uid));

	/** Sends the Pruefung dialog for several students ("new Pruefung"). Does not wait for the response. */
	submitCreatePruefungen = ({ uids, note, punkte, datum }) => {
		this.openCreatePruefungen();

		if (datum) this.setDate("pruefung-datum", datum);
		if (note) this.selectDropdownOption("pruefung-note", note);
		if (punkte !== undefined) this.setPruefungPunkte(punkte);

		this.toggleStudentsInDialog(uids);

		cy.get("[data-cy='pruefung-submit']").click();
	};

	/** Adds a Pruefung for several students. */
	createPruefungen = (options) => {
		this.submitCreatePruefungen(options);
		waitForOk("@createPruefungen");
		this.getPruefungModal().should("not.be.visible");
	};

	// --- Freigabe --------------------------------------------------------------------------------

	openFreigabeModal = () => {
		cy.get("[data-cy='btn-freigabe']").click();
		this.getFreigabeModal().should("be.visible");
	};

	expectFreigabeSummaryRow = (uid, lvNoteBezeichnung) =>
		this.getFreigabeSummaryRow(uid)
			.find("[data-cy='freigabe-row-lvnote']")
			.should("contain.text", lvNoteBezeichnung);

	/** Without a changed LV-Note the dialog shows a hint, and its button stays disabled. */
	expectFreigabeEmpty = () => {
		cy.get("[data-cy='freigabe-summary-empty']").should("be.visible");
		cy.get("[data-cy='freigabe-submit']").should("be.disabled");
	};

	typeFreigabePassword = (password) => cy.get("[data-cy='freigabe-password'] input").type(password, { log: false });

	submitFreigabe = () => cy.get("[data-cy='freigabe-submit']").click();

	saveFreigabe = (password) => {
		this.typeFreigabePassword(password);
		this.submitFreigabe();
		waitForOk("@saveFreigabe");
		this.getFreigabeModal().should("not.be.visible");
	};

	// --- Import ----------------------------------------------------------------------------------

	/** rows: [[uid, note], ...] -> "uid<TAB>note" per row */
	submitLvNotenImport = (rows) => {
		this.closeToasts();
		cy.get("[data-cy='btn-lvnoten-import']").click();
		this.getLvNotenImportModal().should("be.visible");

		cy.get("[data-cy='lvnoten-import-text']").type(rows.map((r) => r.join("\t")).join("\n"));
		cy.get("[data-cy='lvnoten-import-submit']").click();
	};

	importLvNoten = (rows) => {
		this.submitLvNotenImport(rows);
		waitForOk("@importLvNoten");
		this.getLvNotenImportModal().should("not.be.visible");
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
		waitForOk("@importPruefungen");
		this.getPruefungImportModal().should("not.be.visible");
	};

	// --- Punkte mode ------------------------------------------------------------------------------
	// The Punkte column and the Punkte field of the Pruefung dialog exist only with CIS_GESAMTNOTE_PUNKTE.
	// The Note then comes from the Notenschluessel; nobody selects it.

	getPunkteCell = (uid) => this.getCell(uid, "proposed_punkte");

	/**
	 * Types Punkte into the cell. The client then gets the Note through getNoteByPunkte (debounced).
	 *
	 * Uses cy.focused(): the liveNumberEditor focuses its input in onRendered, and the click formats
	 * the row again (cellClick -> undoSelection), so a second query through the cell can find nothing.
	 */
	setPunkteInCell = (uid, punkte) => {
		this.getPunkteCell(uid).click();

		// Enter commits the value (success() in the editor). The render after getNoteByPunkte
		// closes the editor, so a blur after that would find nothing.
		cy.focused().should("have.attr", "type", "number").clear().type(`${punkte}{enter}`);
		waitForOk("@getNoteByPunkte");
	};

	expectPunkte = (uid, punkte) => this.getPunkteCell(uid).should("contain.text", String(punkte));

	/** After a repeat the Punkte column is locked: the Note belongs to the Pruefung. */
	expectPunkteCellLocked = (uid) => {
		this.getPunkteCell(uid).click();
		this.getPunkteCell(uid).find("input").should("not.exist");
	};

	/** In the Punkte mode the proposal column is locked: the Note comes from the Punkte. */
	expectProposalLocked = (uid) => {
		this.getCell(uid, "proposed_note").click();
		cy.get(".tabulator-edit-list").should("not.exist");
	};

	expectNoteFieldLocked = (dataCy) => cy.get(`[data-cy='${dataCy}']`).should("have.class", "p-disabled");

	/** The Punkte field of the Pruefung dialog. It triggers the Note lookup. */
	setPruefungPunkte = (punkte) => {
		cy.get("[data-cy='pruefung-punkte'] input").clear().type(String(punkte));
		waitForOk("@getNoteByPunkte");
	};

	// --- Helpers ---------------------------------------------------------------------------------

	/** The Bezeichnung of a Note id, as the dropdowns and cells show it. */
	bezeichnungOf = (ctx, note) => (ctx.notenOptions ?? []).find((n) => String(n.note) === String(note))?.bezeichnung;

	toDDMMYYYY = (isoDate) => {
		const [y, m, d] = isoDate.split("-");
		return `${d}.${m}.${y}`;
	};

	/** Date in the configured import format; kept apart from toDDMMYYYY, which the dialog uses. */
	importDate = (isoDate, format) => (format === "yyyy-MM-dd" ? isoDate : this.toDDMMYYYY(isoDate));
}

export const benotungstoolPage = new BenotungstoolPage();
