import { waitForOk } from "../helpers/network";

/**
 * Page object für das Benotungstool.
 *
 * Selektoren sind data-cy-Attribute aus Benotungstool.js. Die Tabelle ist Tabulator: Zeilen tragen
 * data-cy="student-row-<uid>", Zellen das von Tabulator gesetzte tabulator-field, und der
 * Freigabestatus steckt als data-state am Zellinhalt statt am Icon.
 *
 * Nach jeder schreibenden Aktion wird auf den zugehörigen Request gewartet (waitForOk), nicht auf
 * eine DOM-Änderung - sonst prüfen die Specs gegen den Stand vor der Antwort.
 */

const TABLE_TIMEOUT = 60_000;
const API = "**/api/frontend/v1/Noten";

/**
 * Exakter Textvergleich für Optionslisten. Nötig, weil die Notenbezeichnungen einander enthalten:
 * contains("Gut") trifft zuerst "Sehr Gut", contains("Genügend") zuerst "Nicht Genügend".
 */
const exactText = (text) =>
	new RegExp(`^\\s*${String(text).replace(/[.*+?^${}()|[\]\\]/g, "\\$&")}\\s*$`);

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
	 * Deep-Link auf LV + Semester - die vier Dropdowns müssen dafür nicht bedient werden.
	 * Die Spaltenaufteilung wird vor dem Laden gesetzt: die Komponente liest den localStorage beim
	 * Initialisieren, und vor dem ersten visit gehört er noch about:blank.
	 */
	visit = (ctx, { spalten = "antritt" } = {}) =>
		cy.visit(`/cis.php/Cis/Benotungstool/${ctx.lvId}/${ctx.semKurzbz}`, {
			onBeforeLoad(win) {
				win.localStorage.setItem("notenToolPruefungsspalten", spalten);
				win.localStorage.removeItem("notenToolStickyCols");
			},
		});

	visitAndWaitForTable = (ctx, options) => {
		cy.login();
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
	getPruefungCell = (uid, spalte) => this.getCell(uid, spalte).find("[data-cy='pruefung-cell']");
	getUebernehmenButton = (uid) => this.getCell(uid, "übernehmen").find("[data-cy='btn-uebernehmen']");
	getPruefungAddButton = (uid, spalte) =>
		this.getCell(uid, spalte).find("[data-cy='btn-pruefung-add']");
	getPruefungEditButton = (uid, spalte) =>
		this.getCell(uid, spalte).find("[data-cy='btn-pruefung-edit']");

	getPruefungModal = () => cy.get("[data-cy='modal-pruefung']");
	getNeuePruefungModal = () => cy.get("[data-cy='modal-neue-pruefung']");
	getFreigabeModal = () => cy.get("[data-cy='modal-freigabe']");
	getNotenImportModal = () => cy.get("[data-cy='modal-noten-import']");
	getPruefungImportModal = () => cy.get("[data-cy='modal-pruefung-import']");
	getFreigabeSummaryRow = (uid) => cy.get(`[data-cy='freigabe-row-${uid}']`);

	// --- Zustand prüfen --------------------------------------------------------------------------

	/** offen | changed | ok */
	expectFreigabeState = (uid, state) =>
		this.getFreigabeState(uid).should("have.attr", "data-state", state);

	expectLvNote = (uid, bezeichnung) => this.getCell(uid, "lv_note").should("contain.text", bezeichnung);

	expectNotenvorschlag = (uid, bezeichnung) =>
		this.getCell(uid, "note_vorschlag").should("contain.text", bezeichnung);

	/** centeredTextFormatter prüft auf falsy, eine 0 rendert daher als leere Zelle. */
	expectAntrittCount = (uid, count) =>
		Number(count) === 0
			? this.getCell(uid, "hoechsterAntritt").invoke("text").invoke("trim").should("eq", "")
			: this.getCell(uid, "hoechsterAntritt").should("contain.text", String(count));

	expectPruefung = (uid, spalte, { note, antritt } = {}) => {
		// je Assertion neu abfragen, damit Cypress ein Re-Render der Zelle erneut versucht
		if (note !== undefined) {
			this.getPruefungCell(uid, spalte).should("have.attr", "data-note", String(note));
		}
		if (antritt !== undefined) {
			this.getPruefungCell(uid, spalte).should("have.attr", "data-attempt", String(antritt));
		}
	};

	expectKeinePruefung = (uid, spalte) =>
		this.getCell(uid, spalte).find("[data-cy='pruefung-cell']").should("not.exist");

	expectKeineAntrittsspalte = (nr) => cy.get(`[tabulator-field='antritt_${nr}']`).should("not.exist");

	// --- Notenvorschlag --------------------------------------------------------------------------

	/** Öffnet den Tabulator-Listeneditor der Vorschlagsspalte und wählt die Bezeichnung. */
	setNotenvorschlag = (uid, bezeichnung) => {
		this.getCell(uid, "note_vorschlag").click();
		cy.get(".tabulator-edit-list-item").contains(bezeichnung).click();
	};

	uebernehmen = (uid) => {
		this.getUebernehmenButton(uid).click();
		waitForOk("@saveNotenvorschlag");
	};

	// --- Prüfungen -------------------------------------------------------------------------------

	/** PrimeVue hängt seine Panels an den body, daher nicht innerhalb des Modals suchen. */
	selectDropdownOption = (dataCy, label) => {
		cy.get(`[data-cy='${dataCy}']`).click();
		cy.contains(".p-dropdown-panel .p-dropdown-item", exactText(label)).click();
	};

	setDatum = (dataCy, ddmmyyyy) =>
		cy.get(`[data-cy='${dataCy}'] input`).first().clear().type(`${ddmmyyyy}{enter}`);

	/** Dialog aus der Tabellenzelle: neuer Antritt für EINEN Studenten. */
	addPruefungInCell = (uid, spalte, { note, datum } = {}) => {
		this.getPruefungAddButton(uid, spalte).click();
		this.getPruefungModal().should("be.visible");

		if (datum) this.setDatum("pruefung-datum", datum);
		if (note) this.selectDropdownOption("pruefung-note", note);

		cy.get("[data-cy='pruefung-submit']").click();
		waitForOk("@saveStudentPruefung");
		this.getPruefungModal().should("not.be.visible");
	};

	/** Bestehenden Antritt bearbeiten. Ohne `note` bleibt sie unverändert (Datumskorrektur). */
	editPruefungInCell = (uid, spalte, { note, datum } = {}) => {
		this.getPruefungEditButton(uid, spalte).click();
		this.getPruefungModal().should("be.visible");

		if (datum) this.setDatum("pruefung-datum", datum);
		if (note) this.selectDropdownOption("pruefung-note", note);

		cy.get("[data-cy='pruefung-submit']").click();
		waitForOk("@saveStudentPruefung");
		this.getPruefungModal().should("not.be.visible");
	};

	openPruefungModalForEdit = (uid, spalte) => {
		this.getPruefungEditButton(uid, spalte).click();
		this.getPruefungModal().should("be.visible");
	};

	/** Sammelanlage über die Toolbar, für mehrere Studierende auf einmal. */
	addPruefungBulk = ({ uids, note, datum }) => {
		cy.get("[data-cy='btn-neue-pruefung']").click();
		this.getNeuePruefungModal().should("be.visible");

		if (datum) this.setDatum("neue-pruefung-datum", datum);
		if (note) this.selectDropdownOption("neue-pruefung-note", note);

		cy.get("[data-cy='neue-pruefung-studenten']").click();
		// das Label ist "uid – Nachname Vorname – Antritte: n", daher hier bewusst per Teilstring
		uids.forEach((uid) => cy.contains(".p-multiselect-panel .p-multiselect-item", uid).click());

		// Panel über den Trigger schliessen, NICHT mit Escape: das schliesst das Bootstrap-Modal mit
		cy.get("[data-cy='neue-pruefung-studenten']").click();
		cy.get(".p-multiselect-panel").should("not.exist");

		cy.get("[data-cy='neue-pruefung-submit']").click();
		waitForOk("@createPruefungen");
		this.getNeuePruefungModal().should("not.be.visible");
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

	typeFreigabePasswort = (password) =>
		cy.get("[data-cy='freigabe-passwort'] input").type(password, { log: false });

	submitFreigabe = () => cy.get("[data-cy='freigabe-submit']").click();

	freigeben = (password) => {
		this.typeFreigabePasswort(password);
		this.submitFreigabe();
		waitForOk("@saveStudentenNoten");
		this.getFreigabeModal().should("not.be.visible");
	};

	// --- Import ----------------------------------------------------------------------------------

	/** rows: [[uid, note], ...] -> "uid<TAB>note" je Zeile */
	importNoten = (rows) => {
		cy.get("[data-cy='btn-noten-import']").click();
		this.getNotenImportModal().should("be.visible");

		cy.get("[data-cy='noten-import-text']").type(rows.map((r) => r.join("\t")).join("\n"));
		cy.get("[data-cy='noten-import-submit']").click();
		waitForOk("@saveNotenvorschlagBulk");
		this.getNotenImportModal().should("not.be.visible");
	};

	/** rows: [[uid, "dd.MM.yyyy", note], ...] */
	importPruefungen = (rows) => {
		cy.get("[data-cy='btn-pruefung-import']").click();
		this.getPruefungImportModal().should("be.visible");

		cy.get("[data-cy='pruefung-import-text']").type(rows.map((r) => r.join("\t")).join("\n"));
		cy.get("[data-cy='pruefung-import-submit']").click();
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

	expectPunkte = (uid, punkte) =>
		this.getPunkteCell(uid).should("contain.text", String(punkte));

	/** Sobald ein Termin existiert, ist die Punktespalte gesperrt (editable-Guard der Spalte). */
	expectPunkteZelleGesperrt = (uid) => {
		this.getPunkteCell(uid).click();
		this.getPunkteCell(uid).find("input").should("not.exist");
	};

	/** Im Punktemodus ist die Vorschlagsspalte nicht editierbar - die Note kommt aus den Punkten. */
	expectNotenvorschlagGesperrt = (uid) => {
		this.getCell(uid, "note_vorschlag").click();
		cy.get(".tabulator-edit-list").should("not.exist");
	};

	expectNoteFeldGesperrt = (dataCy) => cy.get(`[data-cy='${dataCy}']`).should("have.class", "p-disabled");

	/** Punktefeld im Einzeldialog; löst die Ableitung der Note aus. */
	setPruefungPunkte = (punkte) => {
		cy.get("[data-cy='pruefung-punkte'] input").clear().type(String(punkte));
		waitForOk("@getNoteByPunkte");
	};

	/** Punktefeld der Sammelanlage; dort leitet erst der Server beim Speichern ab. */
	setNeuePruefungPunkte = (punkte) =>
		cy.get("[data-cy='neue-pruefung-punkte'] input").clear().type(String(punkte));

	// --- Hilfen ----------------------------------------------------------------------------------

	/** Die Bezeichnung zu einer Noten-PK, wie sie in Dropdowns und Zellen steht. */
	bezeichnungOf = (ctx, note) =>
		(ctx.notenOptions ?? []).find((n) => String(n.note) === String(note))?.bezeichnung;

	toDDMMYYYY = (isoDate) => {
		const [y, m, d] = isoDate.split("-");
		return `${d}.${m}.${y}`;
	};
}

export const benotungstoolPage = new BenotungstoolPage();
