import {CoreFilterCmpt} from "../../filter/Filter.js";
import ApiNoten from "../../../api/factory/noten.js";
import ApiStudiensemester from "../../../api/factory/studiensemester.js";
import BsModal from '../../Bootstrap/Modal.js';
import BsOffcanvas from '../../Bootstrap/Offcanvas.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import MobilityLegende from '../../Mobility/Legende.js';
import FhcOverlay from "../../Overlay/FhcOverlay.js";
import BenotungstoolImport from "./Import.js";
import BenotungstoolPruefungDialog from "./PruefungDialog.js";
import BenotungstoolFreigabeDialog from "./FreigabeDialog.js";
import * as TableLayout from "./tableLayout.js";
import {debounce} from "../../../helpers/debounce.js";
import {escapeHtml} from "../../../helpers/StringHelpers.js";
import {today, toIsoDate, parseIsoDate, parseTimestamp, isoToDmy} from "../../../helpers/DateHelpers.js";
import {centeredTextFormatter} from "../../../tabulator/formatter/centered.js";

/**
 * The Benotungstool: one row per student of an LV, with the LV-Note and the Pruefungen.
 *
 * The server sends each student with a Verlauf (antrittCount, canAdd, bestanden, pruefungen ...).
 * Each write answers with the new Verlauf, and applyRows() puts it into the row.
 *
 * The server decides every rule and sends the answers in the Verlauf (canAdd, lvNoteLocked,
 * earliestNewDay, notenAtLimit, note_locked ...). The client repeats no rule; it reads these answers to
 * place the buttons and to warn before a request.
 *
 * Import.js, PruefungDialog.js and FreigabeDialog.js hold the dialogs. A dialog emits the input, and
 * this component writes it.
 *
 * The domain model is in tests/cypress/suites/readme_noten.txt, section 2.
 */

// The browser keeps the table layout. Set a new date after a column change: a kept layout knows only
// the old fields, and it puts every new column at the end.
const LAYOUT_KEY = 'notenToolTable2026-09-22';
const STICKY_KEY = 'notenToolStickyCols';
const COLUMN_MODE_KEY = 'notenToolPruefungsspalten';
// the columns that the user can pin to the left
const STICKY_FIELDS = ['selectCol', 'uid', 'vorname', 'nachname'];

/**
 * One dropdown option per Lehreinheit. The query gives one row per Lehreinheitgruppe, so the rows of
 * one Lehreinheit become one option: "kurzbz - lehrform - gruppe, gruppe".
 */
function toLehreinheitOptions(rows) {
	const options = [];

	(rows ?? []).forEach(row => {
		// a Gruppe without an own gruppe_kurzbz is named after its Lehrverband
		const gruppe = (row.gruppe_kurzbz !== null && row.direktinskription == false)
			? row.gruppe_kurzbz
			: row.kurzbzlang + '-' + row.semester + (row.verband ?? '') + (row.gruppe ?? '');

		const option = options.find(o => o.lehreinheit_id === row.lehreinheit_id);
		if (option) {
			option.infoString += ', ' + gruppe;
			return;
		}

		options.push({ ...row, infoString: row.kurzbz + ' - ' + row.lehrform_kurzbz + ' - ' + gruppe });
	});

	return options;
}

export const Benotungstool = {
	name: "Benotungstool",
	components: {
		BsModal,
		BsOffcanvas,
		CoreFilterCmpt,
		MobilityLegende,
		BenotungstoolImport,
		BenotungstoolPruefungDialog,
		BenotungstoolFreigabeDialog,
		Dropdown: primevue.dropdown,
		Datepicker: VueDatePicker,
		Multiselect: primevue.multiselect,
		FhcOverlay
	},
	// the deep link of the route: the first load reads it; the requests use lvId and semKurzbz
	props: {
		lv_id: {
			default: null,
			required: false
		},
		sem_kurzbz: {
			default: null,
			required: false
		}
	},
	data() {
		return {
			config: null,
			loading: false,
			domain: '',

			// the dropdowns above the table
			studiensemester: null,
			selectedSemester: null,
			isAssistenz: false,
			assistenzStudiengaenge: null,
			selectedStudiengang: null,
			lehrveranstaltungen: null,
			selectedLehrveranstaltung: null,
			lehreinheiten: [],
			selectedLehreinheit: null,

			// the table
			canBuildTable: false,
			notenTableOptions: null,
			// the LV and the semester of the rows in the table; every request of the table uses them
			lvId: null,
			semKurzbz: null,
			students: null,
			notenOptions: null,
			notenOptionsLehre: null,
			distinctPruefungsDates: [],
			// the copy of the table selection; only onRowSelectionChanged writes it
			selectedStudents: [],
			filteredRows: null,
			filteredCount: 0,
			tabulatorUuid: 0,
			// increases on sort, filter and new data, so that studentOptions follows the table
			tableVersion: 0,
			// Tabulator changes rows outside of Vue; the counter makes changedLvNoten run again
			lvNotenVersion: 0,

			layoutRestored: false,
			stickySelection: TableLayout.loadKept(STICKY_KEY, ['selectCol', 'uid']),
			columnModeChoice: TableLayout.loadKept(COLUMN_MODE_KEY, null),

			// the proposal dialog
			proposalStudent: null,
			proposalDate: today(),
			proposalMaxDate: today()
		};
	},
	computed: {
		/** The column layout: the choice of the user, else CIS_GESAMTNOTE_PRUEFUNGSSPALTEN. */
		columnMode() {
			return this.columnModeChoice ?? this.config?.CIS_GESAMTNOTE_PRUEFUNGSSPALTEN;
		},
		selectionSummary() {
			return this.$p.t('global/ausgewaehlt') + ': <strong>' + this.selectedStudents.length + '</strong>'
				+ ' | ' + this.$p.t('global/gefiltert') + ': <strong>' + this.filteredCount + '</strong>'
				+ ' | ' + this.$p.t('global/gesamt') + ': <strong>' + (this.students?.length ?? 0) + '</strong>';
		},
		/** The options of "new Pruefung": the students that can get a Pruefung, in the order of the table. */
		studentOptions() {
			// a reactive dependency: sort, filter and new data increase it
			this.tableVersion;
			const table = this.getTable();
			const rows = table ? table.getRows('active').map(r => r.getData()) : (this.students ?? []);

			return rows.filter(s => s.verlauf.canAdd);
		},
		/** The rows that the Freigabe sends: an LV-Note that changed since the last Freigabe. */
		changedLvNoten() {
			this.lvNotenVersion;
			return (this.students ?? []).filter(s => s.freigabe_state === 'changed');
		},
		freigabeButtonClass() {
			return this.changedLvNoten.length ? "btn btn-primary ml-2" : "btn btn-secondary ml-2";
		},
		/** The Tabulator events. The handlers need `this`, so the list is no module constant. */
		tableEvents() {
			return [
				{ event: 'rowSelectionChanged', handler: (data) => this.onRowSelectionChanged(data) },
				{ event: 'rowSelected', handler: (row) => this.syncCheckbox(row) },
				{ event: 'rowDeselected', handler: (row) => this.syncCheckbox(row) },
				{ event: 'cellEditing', handler: (cell) => this.onCellEditing(cell) },
				{ event: 'cellEdited', handler: (cell) => this.onCellEdited(cell) },
				{ event: 'cellEditCancelled', handler: (cell) => this.onCellEditCancelled(cell) },
				{ event: 'cellClick', handler: (e, cell) => this.onCellClick(e, cell) },
				{ event: 'dataFiltered', handler: (filters, rows) => this.onDataFiltered(rows) }
			];
		},
		stickyOptions() {
			return [
				{ field: 'selectCol', label: this.$capitalize(this.$p.t('benotungstool/c4selection')) },
				{ field: 'uid', label: 'UID' },
				{ field: 'vorname', label: this.$capitalize(this.$p.t('benotungstool/c4vorname')) },
				{ field: 'nachname', label: this.$capitalize(this.$p.t('benotungstool/c4nachname')) }
			];
		}
	},
	watch: {
		selectedLehreinheit(lehreinheit) {
			const table = this.getTable();
			if (!table) return;

			const others = table.getFilters().filter(f => f.field !== 'lehreinheit_id');
			const filters = lehreinheit ? [...others, { field: 'lehreinheit_id', type: '=', value: lehreinheit.lehreinheit_id }] : others;

			table.clearFilter();
			if (filters.length) table.setFilter(filters);
		},
		/** The Lehreinheiten belong to one LV, so they follow the selection. */
		selectedLehrveranstaltung(lehrveranstaltung) {
			this.lehreinheiten = [];
			this.selectedLehreinheit = null;

			const sem_kurzbz = this.selectedSemester?.studiensemester_kurzbz;
			if (lehrveranstaltung && sem_kurzbz) this.loadLehreinheiten(lehrveranstaltung.lehrveranstaltung_id, sem_kurzbz);
		}
	},
	created() {
		this.setup();
	},
	methods: {
		// === Setup and loading ======================================================================

		async setup() {
			this.loading = true;

			// the table needs the configuration and the Noten
			const configLoaded = this.$api.call(ApiNoten.getCisConfig()).then(res => {
				this.config = res.data;
			});

			// the answer is [all semesters, the current or next one]
			this.$api.call(ApiStudiensemester.getAllStudiensemesterAndAktOrNext()).then(res => {
				this.studiensemester = res.data[0];
				this.selectedSemester = this.studiensemester.find(s => s.studiensemester_kurzbz === this.sem_kurzbz)
					?? this.studiensemester.find(s => s.studiensemester_kurzbz === res.data[1]?.studiensemester_kurzbz)
					?? this.studiensemester[0]
					?? null;

				const sem = this.selectedSemester?.studiensemester_kurzbz;
				if (sem) this.loadLvSource(sem, this.lv_id);
			});

			try {
				const res = await this.$api.call(ApiNoten.getNoten());
				this.notenOptions = res.data;
				this.notenOptionsLehre = res.data.filter(n => n.lehre === true);

				await configLoaded;
				this.notenTableOptions = this.getTableOptions();
				this.canBuildTable = true;
			} catch (e) {
				this.loading = false;
			}
		},

		/**
		 * Fills the LV dropdown of one semester. An Assistenz selects a Studiengang first; a Lektor
		 * gets the own LVs at once.
		 *
		 * @param keepLvId the LV to select again: the deep link at the start, else the open LV
		 */
		loadLvSource(sem_kurzbz, keepLvId = null) {
			// a semester change keeps the Studiengang of the user; the first load has none
			const keepStudiengang = this.selectedStudiengang?.studiengang_kz;

			return this.$api.call(ApiNoten.getBenotungstoolContext(sem_kurzbz, keepLvId)).then(res => {
				this.isAssistenz = !!res.data.isAssistenz;

				if (!this.isAssistenz) {
					this.setLehrveranstaltungen(res.data.lehrveranstaltungen, keepLvId);
					return;
				}

				this.setAssistenzStudiengaenge(res.data.studiengaenge);

				// without a Studiengang of the user, the server names the one of the deep link
				const studiengang_kz = keepStudiengang ?? res.data.preselectStudiengang_kz;
				this.selectedStudiengang = this.assistenzStudiengaenge.find(s => s.studiengang_kz == studiengang_kz) ?? null;

				if (this.selectedStudiengang) {
					return this.loadLehrveranstaltungenForStudiengang(this.selectedStudiengang.studiengang_kz, sem_kurzbz, keepLvId);
				}

				// the Studiengang does not exist in this semester
				this.lehrveranstaltungen = null;
				this.selectedLehrveranstaltung = null;
			});
		},

		loadLehrveranstaltungenForStudiengang(studiengang_kz, sem_kurzbz, preselectLvId = null) {
			return this.$api.call(ApiNoten.getLvForStudiengang(studiengang_kz, sem_kurzbz)).then(res => {
				this.setLehrveranstaltungen(res.data, preselectLvId);
			});
		},

		setAssistenzStudiengaenge(studiengaenge) {
			studiengaenge.forEach(stg => stg.fullString = `${stg.kuerzel} - ${stg.bezeichnung}`);
			this.assistenzStudiengaenge = studiengaenge;
		},

		setLehrveranstaltungen(lehrveranstaltungen, preselectLvId = null) {
			lehrveranstaltungen.forEach(lv => lv.fullString = `${lv.stg_kurzbz} - ${lv.lv_semester} - ${lv.orgform}: ${lv.lv_bezeichnung}`);
			this.lehrveranstaltungen = lehrveranstaltungen;
			this.selectedLehrveranstaltung = preselectLvId
				? lehrveranstaltungen.find(lv => lv.lehrveranstaltung_id == preselectLvId) ?? null
				: null;
		},

		onSemesterChange(e) {
			const sem = e.value.studiensemester_kurzbz;
			const keepLvId = this.selectedLehrveranstaltung?.lehrveranstaltung_id ?? this.lv_id;

			this.loading = true;
			this.loadLvSource(sem, keepLvId)
				.then(() => this.showSemester(sem))
				.finally(() => this.loading = false);
		},

		showSemester(sem) {
			const lvId = this.selectedLehrveranstaltung?.lehrveranstaltung_id ?? null;

			this.$router.push({ name: "Benotungstool", params: { sem_kurzbz: sem, lv_id: lvId ?? undefined } });

			if (lvId) {
				this.loadNoten(lvId, sem);
			} else {
				this.clearTable();
			}
		},

		/** No LV is selected: the table shows no rows, and no request has an LV. */
		clearTable() {
			this.lvId = null;
			this.semKurzbz = null;
			this.students = [];
			this.getTable()?.setData([]);
		},

		onStudiengangChange(e) {
			const sem = this.selectedSemester?.studiensemester_kurzbz ?? this.sem_kurzbz;
			const studiengang_kz = e.value?.studiengang_kz ?? null;

			this.selectedLehrveranstaltung = null;
			if (!studiengang_kz || !sem) {
				this.lehrveranstaltungen = null;
				return;
			}

			this.loading = true;
			this.loadLehrveranstaltungenForStudiengang(studiengang_kz, sem).finally(() => this.loading = false);
		},

		onLvChange(e) {
			const sem = this.selectedSemester?.studiensemester_kurzbz ?? this.sem_kurzbz;

			this.$router.push({ name: "Benotungstool", params: { sem_kurzbz: sem, lv_id: e.value.lehrveranstaltung_id } });
			this.loadNoten(e.value.lehrveranstaltung_id, sem);
		},

		/** The Lehreinheiten of the LV: the filter above the table and the links to the Notenlisten. */
		loadLehreinheiten(lv_id, sem_kurzbz) {
			// every Lehreinheit of the LV, not only the own ones: an Assistenz teaches none
			return this.$api.call(ApiNoten.getLehreinheitenForLv(lv_id, sem_kurzbz)).then(res => {
				this.lehreinheiten = toLehreinheitOptions(res.data);
			});
		},

		loadNoten(lv_id, sem_kurzbz) {
			if (!lv_id || !sem_kurzbz) return;

			this.loading = true;
			this.$api.call(ApiNoten.getStudentenNoten(lv_id, sem_kurzbz))
				.then(async res => {
					this.lvId = lv_id;
					this.semKurzbz = sem_kurzbz;
					await this.showStudents(res.data);

					if (res.meta?.getExternalGradesError) {
						this.$fhcAlert.alertError(this.$p.t('benotungstool/c4moodleTeilnotenError', [res.meta.getExternalGradesError]));
					}
				})
				.finally(() => this.loading = false);
		},

		/** Shows the rows of getStudentenNoten: { students, domain }. */
		async showStudents(data) {
			this.domain = data.domain;
			this.students = data.students;
			this.students.forEach(student => {
				student.email = 'mailto:' + student.uid + '@' + this.domain;
				student.proposed_punkte = student.lv_punkte;
				student.freigabedatum = parseTimestamp(student.freigabedatum);
				student.benotungsdatum = parseTimestamp(student.benotungsdatum);
				student.freigabe_state = this.freigabeState(student);
				this.applyVerlauf(student, student.verlauf);
			});
			this.afterVerlaufChange();

			// a new LV: build the columns again and start at the top
			this.applyPruefungColumns(true);
			this.getTable().setData(this.students);
			this.getTable().redraw(true);
			this.applyStickyColumns();

			// the first rows: now the columns of a kept sort exist
			if (!this.layoutRestored) {
				TableLayout.restoreFiltersAndSort(this.getTable(), TableLayout.loadKept(LAYOUT_KEY, null));
				this.layoutRestored = true;
			}

			// keep the loading overlay until the browser has painted the table
			await new Promise(requestAnimationFrame);
		},

		// === Server answers =========================================================================

		/**
		 * Applies the answer of a write: { uid: { lvgesamtnote, verlauf } } or { uid: { error } }.
		 *
		 * @returns {{saved: string[], errors: {uid: string, message: string}[]}}
		 */
		applyRows(data) {
			const saved = [];
			const errors = [];

			Object.entries(data ?? {}).forEach(([uid, row]) => {
				if (row.error) {
					errors.push({ uid, message: row.error.message });
					return;
				}

				saved.push(uid);
				const student = this.students.find(s => s.uid === uid);
				if (!student) return;

				this.applyLvGesamtnote(student, row.lvgesamtnote);
				this.applyVerlauf(student, row.verlauf);
			});
			this.afterVerlaufChange();

			return { saved, errors };
		},

		/** Takes the LV-Note of a server answer into the row. An LV-Note replaces the proposal. */
		applyLvGesamtnote(student, lvgesamtnote) {
			student.lv_note = lvgesamtnote.note;
			student.lv_punkte = lvgesamtnote.punkte;
			student.proposed_note = lvgesamtnote.note;
			student.proposed_punkte = lvgesamtnote.punkte;
			student.freigabedatum = parseTimestamp(lvgesamtnote.freigabedatum);
			student.benotungsdatum = parseTimestamp(lvgesamtnote.benotungsdatum);
			student.freigabe_state = this.freigabeState(student);

			this.lvNotenVersion++;
		},

		/** Takes the Verlauf of a server answer into the row. Call afterVerlaufChange() after the last row. */
		applyVerlauf(student, verlauf) {
			student.verlauf = verlauf;
			this.indexPruefungen(student);
		},

		/** Once after new Verlaeufe: the date columns follow, and canAdd decides the options of "new Pruefung". */
		afterVerlaufChange() {
			this.syncDistinctPruefungsDates();
			this.tableVersion++;
		},

		/**
		 * The Freigabe state from the two dates:
		 *   open         no LV-Note
		 *   changed      an LV-Note that is not freigegeben, or changed after the Freigabe
		 *   freigegeben  freigegeben and not changed since then
		 */
		freigabeState(student) {
			if (!student.benotungsdatum) return 'open';
			if (!student.freigabedatum || student.benotungsdatum > student.freigabedatum) return 'changed';
			return 'freigegeben';
		},

		/**
		 * Draws the table again after a write, at the same scroll position. A new Pruefung can need a
		 * new column. `rebuildColumns` builds the Pruefung columns again, also without a change.
		 */
		refreshTable(rebuildColumns = false) {
			TableLayout.preserveScroll(this.getTable(), () => {
				this.applyPruefungColumns(rebuildColumns);
				const loaded = this.getTable()?.setData(this.students);
				this.getTable()?.redraw(true);
				return loaded;
			});
		},

		// === Table ==================================================================================

		getTable() {
			return this.$refs.notenTable?.tabulator;
		},

		getTableOptions() {
			return {
				height: 700,
				virtualDom: true,
				renderVerticalBuffer: 1000,
				index: 'uid',
				layout: 'fitData',
				placeholder: this.$capitalize(this.$p.t('global/noDataAvailable')),
				selectable: true,
				selectableRangeMode: "click",
				// new rows, a filter and a sort clear the selection
				selectablePersistence: false,
				// the calculation row has no Verlauf
				selectableCheck: (row) => row.getData().verlauf?.canAdd === true,
				rowHeight: 30,
				rowFormatter: this.rowFormatter,
				columns: this.getColumns(),
				persistence: false
			};
		},

		/** The table exists now, so the rows can follow. A deep link brings semester and LV. */
		onTableBuilt() {
			const table = this.getTable();

			['columnMoved', 'columnResized', 'columnVisibilityChanged', 'filterChanged', 'headerFilterChanged', 'dataSorted', 'columnSorted', 'sortersChanged']
				.forEach(event => table.on(event, () => this.saveTableLayout()));

			['columnResized', 'columnMoved', 'columnVisibilityChanged']
				.forEach(event => table.on(event, () => this.applyStickyColumns()));

			// the "new Pruefung" dropdown uses the order of the table
			table.on('dataSorted', () => this.tableVersion++);

			// the widths are known here
			table.on('renderComplete', () => this.applyStickyColumns());

			if (this.lv_id && this.sem_kurzbz) {
				this.loadNoten(this.lv_id, this.sem_kurzbz);
			} else {
				this.loading = false;
			}
			this.calcMaxTableHeight();
		},

		saveTableLayout() {
			// the first rows restore the layout (showStudents); do not overwrite it before
			if (!this.layoutRestored) return;

			TableLayout.saveLayout(LAYOUT_KEY, this.getTable(), this.notenTableOptions.columns.map(c => c.field));
		},

		applyStickyColumns() {
			TableLayout.applyStickyColumns(document.getElementById('notentable'), this.getTable(), STICKY_FIELDS, this.stickySelection);
		},

		onStickySelectionChange() {
			TableLayout.saveKept(STICKY_KEY, this.stickySelection);
			this.applyStickyColumns();
		},

		/** Each column that can be pinned has the class; the container decides if it is pinned (tableLayout.js). */
		stickyClass(field) {
			return STICKY_FIELDS.includes(field) ? 'sticky-col' : undefined;
		},

		calcMaxTableHeight() {
			const dataset = document.getElementById('filterTableDataset' + (this.tabulatorUuid ? '-' + this.tabulatorUuid : ''));
			if (!dataset) return;

			this.notenTableOptions.height = window.visualViewport.height - dataset.getBoundingClientRect().top - 50;
			this.getTable().setHeight(this.notenTableOptions.height);
		},

		onUuidDefined(uuid) {
			this.tabulatorUuid = uuid;
		},

		/** The table holds the selection. selectedStudents is its copy for Vue; only this handler writes it. */
		onRowSelectionChanged(data) {
			// a second click on the only selected row clears the selection: Tabulator selects that row again
			const sameSingleRow = data.length === 1 && this.selectedStudents.length === 1 && data[0].uid === this.selectedStudents[0].uid;
			if (sameSingleRow) {
				this.getTable().deselectRow();
				return;
			}

			this.selectedStudents = data.filter(d => d.verlauf.canAdd);
		},

		/** The multiselect of "new Pruefung" selects through the table. One call per direction gives one event each. */
		selectStudents(students) {
			const table = this.getTable();
			const uids = new Set(students.map(s => s.uid));

			table.deselectRow(table.getSelectedRows().filter(row => !uids.has(row.getData().uid)));
			table.selectRow(table.getRows().filter(row => uids.has(row.getData().uid) && !row.isSelected()));
		},

		/** The checkbox of selectFormatter follows the selection; select() does not format the cell again. */
		syncCheckbox(row) {
			const checkbox = this.checkboxOf(row.getElement());
			if (checkbox) checkbox.checked = row.isSelected();
		},

		/** A filter clears the selection itself (selectablePersistence: false). */
		onDataFiltered(rows) {
			this.filteredRows = rows;
			this.filteredCount = rows.length;
			this.tableVersion++;
		},

		onCellClick(e, cell) {
			const field = cell.getField();

			if (field === 'mobility_zusatz') {
				this.$refs.drawer.show();
				e.stopPropagation();
			}

			// a click into an input column does not select the row
			if (['mobility_zusatz', 'proposed_punkte', 'proposed_note', 'apply_proposal'].includes(field) && cell.getRow().isSelected()) {
				cell.getRow().deselect();
			}
		},

		// === Columns ================================================================================

		getColumns() {
			const column = (phrase, field, options) => ({
				title: Vue.computed(() => this.$capitalize(this.$p.t('benotungstool/' + phrase))),
				field,
				...options
			});
			const notenFilter = {
				headerFilter: 'list',
				headerFilterParams: () => ({
					values: [" ", this.$p.t('benotungstool/c4noteEmpty'), this.$p.t('benotungstool/c4positiv'),
						this.$p.t('benotungstool/c4negativ'), ...this.notenOptions.map(n => n.bezeichnung)]
				}),
				headerFilterFunc: this.notenFilter
			};

			// A column with a header filter or an editor needs more space than its title. Without the
			// minimum width the filter field and the values are cut.
			const columns = [
				{
					field: 'selectCol',
					title: '',
					formatter: this.selectFormatter,
					titleFormatter: this.selectAllFormatter,
					hozAlign: "center",
					headerSort: false,
					width: 50,
					minWidth: 50,
					cssClass: this.stickyClass('selectCol')
				},
				{ title: 'UID', field: 'uid', tooltip: false, topCalc: (values) => values.length, formatter: centeredTextFormatter, minWidth: 110, cssClass: this.stickyClass('uid') },
				column('c4mail', 'email', { formatter: this.mailFormatter, tooltip: false, visible: false, minWidth: 170, variableHeight: true }),
				column('c4antrittCountv2', 'verlauf.antrittCount', { formatter: this.antrittCountFormatter, tooltip: false, minWidth: 100 }),
				column('c4vorname', 'vorname', { formatter: centeredTextFormatter, headerFilter: true, tooltip: false, minWidth: 140, cssClass: this.stickyClass('vorname') }),
				column('c4nachname', 'nachname', { formatter: centeredTextFormatter, headerFilter: true, minWidth: 140, cssClass: this.stickyClass('nachname') }),
				column('c4anwesenheitsquote', 'anwquote', { formatter: this.percentFormatter, minWidth: 120 }),
				column('c4mobility', 'mobility_zusatz', { formatter: centeredTextFormatter, headerFilter: true, visible: false, minWidth: 140 })
			];

			if (this.config.CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE) {
				columns.push(column('c4teilnoten', 'teilnoten', { formatter: this.teilnotenFormatter, minWidth: 160, variableHeight: true }));
			}

			if (this.config.CIS_GESAMTNOTE_PUNKTE) {
				columns.push(column('c4punkte', 'proposed_punkte', {
					minWidth: 110,
					editor: this.punkteEditor,
					// the server locks the LV-Note (verlauf.lvNoteLocked); the calculation row has no Verlauf
					editable: (cell) => cell.getRow().getData().verlauf?.lvNoteLocked === false,
					variableHeight: true
				}));
			}

			columns.push(
				column('c4notenvorschlag', 'proposed_note', {
					minWidth: 160,
					editor: 'list',
					editorParams: (cell) => {
						// the value before the edit; onCellEdited restores it if nothing is selected
						cell.getRow().getData()._proposedNoteBeforeEdit = cell.getValue();

						// the LV-Note is never 'entschuldigt'
						return {
							values: this.notenOptionsLehre
								.filter(n => n.note != this.config.NOTE_ENTSCHULDIGT)
								.map(n => ({ label: n.bezeichnung, value: n.note }))
						};
					},
					editable: (cell) => this.canEditProposal(cell.getRow().getData()),
					formatter: this.proposalFormatter
				}),
				column('c4notenvorschlagUebernehmen', 'apply_proposal', {
					width: 150,
					minWidth: 100,
					hozAlign: 'center',
					formatter: this.applyProposalFormatter,
					cellClick: this.openApplyProposalModal,
					variableHeight: true
				}),
				column('c4lvnote', 'lv_note', { minWidth: 160, formatter: this.notenFormatter, ...notenFilter }),
				column('c4freigabe', 'freigabe_state', { formatter: this.freigabeFormatter, minWidth: 130, variableHeight: true }),
				column('c4zeugnisnote', 'zeugnisnote', {
					minWidth: 160,
					formatter: this.notenFormatter,
					topCalc: this.negativeNotenCount,
					topCalcFormatter: (cell) => this.$capitalize(this.$p.t('benotungstool/c4negativ')) + ': ' + cell.getValue(),
					...notenFilter
				})
			);

			return columns;
		},

		/**
		 * The only place that sets the columns. setColumns builds the full table again, so it runs only
		 * if the Pruefung columns change.
		 */
		applyPruefungColumns(force = false) {
			const table = this.getTable();
			if (!table || !this.notenTableOptions) return;

			const pruefungColumns = this.buildPruefungColumns();
			const key = pruefungColumns.map(c => c.field).join('|');
			if (!force && key === this._pruefungColumnsKey) return;
			this._pruefungColumnsKey = key;

			table.setColumns(TableLayout.restoreColumns([...this.notenTableOptions.columns, ...pruefungColumns], TableLayout.loadKept(LAYOUT_KEY, null)));
		},

		/**
		 * 'antritt': one column per Pruefung of the student, the date is in the cell. Good if each student
		 * has an own date. 'datum': one column per Pruefung date. Good if the students share the dates.
		 */
		buildPruefungColumns() {
			if (this.columnMode === 'datum') {
				return this.distinctPruefungsDates.map(datum => this.pruefungColumn(datum, isoToDmy(datum)));
			}

			// One more column while a row can get one more Pruefung: the kommissionelle Pruefung is the
			// last one and uses such a column too. A row closed by a pass also gets it; the cell names the reason.
			let count = 0;
			(this.students ?? []).forEach(student => {
				const free = (student.verlauf.canAdd || this.showBestandenHint(student)) ? 1 : 0;
				count = Math.max(count, this.pruefungenOf(student).length + free);
			});

			return Array.from({ length: count }, (_, i) =>
				this.pruefungColumn('antritt_' + (i + 1), this.$capitalize(this.$p.t('benotungstool/c4terminNr', [i + 1])))
			);
		},

		pruefungColumn(field, title) {
			// the fixed tracks of .pruefung-cell and a readable Note
			const width = this.columnMode === 'antritt' ? 320 : 250;

			return {
				title,
				field,
				formatter: this.pruefungFormatter,
				sorter: this.pruefungSorter,
				topCalc: (values) => values.filter(v => v !== undefined).length,
				topCalcFormatter: (cell) => this.$capitalize(this.$p.t('benotungstool/prueflingSelectionv2')) + ': ' + cell.getValue(),
				hozAlign: "center",
				widthGrow: 1,
				minWidth: width,
				width,
				visible: true,
				tooltip: false
			};
		},

		/** Puts each Pruefung into the field of its column. Remove the old fields first, or they stay. */
		indexPruefungen(student) {
			(student._pruefungFields ?? []).forEach(field => delete student[field]);

			student._pruefungFields = this.pruefungenOf(student).map((pruefung, i) => {
				const field = this.columnMode === 'antritt' ? 'antritt_' + (i + 1) : pruefung.datum;
				student[field] = pruefung;
				return field;
			});
		},

		syncDistinctPruefungsDates() {
			const dates = new Set();
			(this.students ?? []).forEach(s => this.pruefungenOf(s).forEach(p => dates.add(p.datum)));
			this.distinctPruefungsDates = [...dates].sort();
		},

		/** Changes the column layout. The browser keeps the choice. */
		setColumnMode(mode) {
			this.columnModeChoice = mode;
			TableLayout.saveKept(COLUMN_MODE_KEY, mode);

			(this.students ?? []).forEach(s => this.indexPruefungen(s));

			// all columns change, but the rows stay at their position
			this.refreshTable(true);
		},

		pruefungenOf(student) {
			return student?.verlauf?.pruefungen ?? [];
		},

		// === Cells ==================================================================================

		rowFormatter(row) {
			const data = row.getData();
			const element = row.getElement();

			// Tabulator also formats its calculation row above the table; it is no student
			if (!data.verlauf) return;

			// the tests address a row by its uid (tests/cypress/support/pages)
			element.setAttribute('data-cy', 'student-row-' + data.uid);

			// a row without a free Antritt has no checkbox
			if (!data.verlauf.canAdd) this.checkboxOf(element)?.remove();
			element.classList.toggle('tabulator-selectable', data.verlauf.canAdd);
			element.classList.toggle('tabulator-unselectable', !data.verlauf.canAdd);
		},

		/** The checkbox of selectFormatter in a row. Search it by field: the user can move the column. */
		checkboxOf(rowElement) {
			return rowElement.querySelector('[tabulator-field="selectCol"] input');
		},

		selectFormatter(cell) {
			const checkbox = document.createElement("input");
			checkbox.type = "checkbox";
			// the state survives a sort or a filter
			checkbox.checked = cell.getRow().isSelected();
			checkbox.addEventListener("click", (e) => {
				e.stopPropagation();
				const row = cell.getRow();
				if (row.isSelected()) row.deselect(); else row.select();
			});
			return checkbox;
		},

		selectAllFormatter(cell, formatterParams, onRendered) {
			const selectableRows = () => (this.filteredRows ?? cell.getTable().getRows('active')).filter(r => r.getData().verlauf.canAdd);

			const checkbox = document.createElement("input");
			checkbox.type = "checkbox";
			onRendered(() => {
				const rows = selectableRows();
				checkbox.checked = rows.length > 0 && rows.every(r => r.isSelected());
			});
			checkbox.addEventListener("click", (e) => {
				e.stopPropagation();
				const rows = selectableRows();
				const select = !rows.every(r => r.isSelected());
				rows.forEach(r => select ? r.select() : r.deselect());
				checkbox.checked = select;
			});
			return checkbox;
		},

		/** A Pruefung cell: the badge, the Note, the date (in the 'antritt' layout) and a button. */
		pruefungFormatter(cell) {
			const student = cell.getData();

			// an Anrechnung: the row is visible, but it gets no Pruefung
			if (student.verlauf.angerechnet) return '';

			// A Zeugnisnote that the Lektor may not overwrite locks the Pruefungen. Without a
			// Zeugnisnote the columns stay open, because Antritt 1 starts there.
			if (student.verlauf.zeugnisnoteLocked) return '';

			const field = cell.getColumn().getField();
			const pruefung = student[field];
			const antrittMode = this.columnMode === 'antritt';

			const cellDiv = document.createElement('div');
			cellDiv.className = antrittMode ? 'pruefung-cell with-datum' : 'pruefung-cell';

			const addSlot = (className, content) => {
				const div = document.createElement('div');
				div.className = className;
				if (typeof content === 'string') div.textContent = content;
				else if (content instanceof HTMLElement) div.appendChild(content);
				cellDiv.appendChild(div);
				return div;
			};
			const addButton = (label, title, onClick, dataCy) => {
				const button = document.createElement('button');
				button.className = 'btn btn-outline-secondary';
				button.textContent = label;
				button.dataset.cy = dataCy;
				if (title) button.title = title;
				button.addEventListener('click', onClick);
				addSlot('pruefung-action', button);
			};

			if (pruefung) {
				// The badge shows the Antritt number, a kommissionelle Pruefung adds the K ('3-K'). A
				// Pruefung without an Antritt (entschuldigt, not assessed) has no number.
				const badge = this.antrittBadge(pruefung);
				const colorClass = pruefung.kommissionell
					? 'antritt-k'
					// the colors stop at Antritt 3; a longer chain keeps the last one
					: (pruefung.is_antritt ? 'antritt-' + Math.min(pruefung.antritt_nr, 3) : 'antritt-none');

				cellDiv.classList.add('pruefung-badge', colorClass);
				cellDiv.setAttribute('data-antritt', badge);
				cellDiv.setAttribute('data-cy', 'pruefung-cell');
				cellDiv.setAttribute('data-note', pruefung.note);
				cellDiv.title = this.antrittTooltip(student, pruefung);

				// the StV owns a kommissionelle Pruefung without an Antritt (zusKommPruef): no button
				const readOnly = pruefung.kommissionell && !pruefung.is_antritt;
				if (readOnly) cellDiv.classList.add('without-action');

				const bezeichnung = this.bezeichnungOf(pruefung.note) ?? '';
				addSlot('pruefung-note', bezeichnung).title = bezeichnung;
				if (antrittMode) addSlot('pruefung-datum', isoToDmy(pruefung.datum));
				if (readOnly) return cellDiv;

				// a later Pruefung locks the Note; the dialog still corrects the date
				addButton(
					this.$capitalize(this.$p.t('benotungstool/changePruefungButtonText')),
					pruefung.note_locked ? this.$capitalize(this.$p.t('benotungstool/pruefungNoteLockedHint')) : null,
					() => this.$refs.pruefungDialog.openForCell(student, pruefung, field),
					'btn-pruefung-edit'
				);
				return cellDiv;
			}

			// An empty cell gets a button only if one more Pruefung is possible, and only in the column
			// after all Pruefungen. The server decides if the new Pruefung is kommissionell. A pass
			// closes the chain: that cell names the reason.
			const bestanden = this.showBestandenHint(student);
			if (!student.verlauf.canAdd && !bestanden) return '';

			const nextColumn = antrittMode
				? field === 'antritt_' + (this.pruefungenOf(student).length + 1)
				: !this.isBeforeEarliestNewDay(student, field);
			if (!nextColumn) return '';

			if (bestanden) {
				cellDiv.classList.add('without-action');
				const hint = addSlot('pruefung-hint', this.$capitalize(this.$p.t('benotungstool/c4bestandenHint')));
				hint.title = this.$capitalize(this.$p.t('benotungstool/c4bestandenTooltip'));
				hint.dataset.cy = 'pruefung-bestanden';
				return cellDiv;
			}

			addButton(
				this.$capitalize(this.$p.t('benotungstool/addPruefungButtonText')),
				null,
				() => this.$refs.pruefungDialog.openForCell(student, null, field),
				'btn-pruefung-add'
			);
			return cellDiv;
		},

		pruefungSorter(a, b) {
			if (a == null || a === '') return -1;
			if (b == null || b === '') return 1;

			// the Pruefungen of one date column: sort by the Note
			return a.note - b.note;
		},

		/** The badge of a Pruefung; CIS_GESAMTNOTE_ANTRITT_ZEICHEN holds the templates, {n} is the Antritt number. */
		antrittBadge(pruefung) {
			const templates = this.config.CIS_GESAMTNOTE_ANTRITT_ZEICHEN;
			const nr = pruefung.antritt_nr;
			const template = pruefung.kommissionell
				? (nr ? templates.kommissionell : templates.kommissionell_ohne_antritt)
				: (nr ? templates.antritt : templates.ohne_antritt);

			return String(template).replace('{n}', nr ?? '');
		},

		/**
		 * The tooltip of the badge. The column counts Pruefungen, the badge counts Antritte: an
		 * entschuldigt Pruefung has a column but uses no Antritt.
		 */
		antrittTooltip(student, pruefung) {
			const max = student.verlauf.maxAntritte;
			const phrase = pruefung.kommissionell
				? (pruefung.antritt_nr ? 'c4badgeKommAntritt' : 'c4badgeKommOhneAntritt')
				: (pruefung.antritt_nr ? 'c4badgeAntritt' : 'c4badgeOhneAntritt');

			return this.$capitalize(this.$p.t('benotungstool/' + phrase, [pruefung.antritt_nr, max]));
		},

		proposalFormatter(cell) {
			const bezeichnung = this.bezeichnungOf(cell.getValue()) ?? cell.getValue();
			if (bezeichnung === undefined || bezeichnung === null) return '';

			const locked = !this.canEditProposal(cell.getRow().getData());
			return '<div' + (locked ? ' class="proposal-locked"' : '') + '>' + bezeichnung + '</div>';
		},

		/** The button that writes the proposal as the LV-Note. */
		applyProposalFormatter(cell) {
			const student = cell.getRow().getData();
			if (!this.canApplyProposal(student)) return '';

			const button = document.createElement('button');
			button.className = 'btn btn-outline-secondary';
			button.dataset.cy = 'btn-apply-proposal';
			button.textContent = this.$capitalize(this.$p.t('benotungstool/c4notenvorschlagUebernehmen'));
			return button;
		},

		notenFormatter(cell) {
			const student = cell.getData();
			const bezeichnung = this.bezeichnungOf(cell.getValue()) ?? cell.getValue();
			if (bezeichnung === undefined || bezeichnung === null || bezeichnung === '') return '';

			// a Zeugnisnote that differs from the LV-Note gets a red border
			const differs = cell.getField() === 'zeugnisnote' && student.zeugnisnote != null && student.zeugnisnote != student.lv_note;
			return '<div class="cell-start' + (differs ? ' zeugnisnote-differs' : '') + '">' + bezeichnung + '</div>';
		},

		freigabeFormatter(cell) {
			const state = cell.getValue();
			const icons = {
				freigegeben: '<i class="fa fa-circle-check freigegeben-icon"></i>',
				open: '<i class="fa-regular fa-circle"></i>',
				changed: '<i class="fa fa-circle-check"></i>'
			};

			// data-state lets a test read the state without the icon
			return '<div data-cy="freigabe-state" data-state="' + state + '" class="cell-center">'
				+ (icons[state] ?? state) + '</div>';
		},

		teilnotenFormatter(cell) {
			return '<div>' + (cell.getValue() ?? []).map(teilnote => {
				// Moodle sends a number or a Bezeichnung ("Sehr Gut", "Bestanden")
				const option = this.notenOptions.find(n => n.note == teilnote.grade || n.bezeichnung == teilnote.grade);
				const negativ = option && !option.positiv;

				// the text comes from the addon, so escape it
				return '<span' + (negativ ? ' class="teilnote-negativ"' : '') + '>' + escapeHtml(teilnote.text) + '</span>';
			}).join('<br/>') + '</div>';
		},

		mailFormatter(cell) {
			// the address holds a uid from the database, so escape it
			return '<div class="cell-center">'
				+ '<a href="' + escapeHtml(cell.getValue()) + '"><i class="fa fa-envelope mail-icon"></i></a></div>';
		},

		/** The Antritte of the Verlauf. It has an own formatter because a 0 must stay visible. */
		antrittCountFormatter(cell) {
			const count = cell.getValue();
			if (count === undefined || count === null) return '';

			return '<div class="cell-start">' + count + '</div>';
		},

		percentFormatter(cell) {
			return '<div class="cell-center">' + (cell.getData().anwquote ?? '-') + ' %</div>';
		},

		negativeNotenCount(values) {
			return values.filter(value => this.notenOptions.find(n => n.note == value)?.positiv === false).length;
		},

		/** The header filter of a Note column: empty, positive, negative or one Bezeichnung. */
		notenFilter(filter, value) {
			if (filter === " " || filter === "" || filter === null) return true;

			const option = this.notenOptions.find(n => n.note == value);
			if (filter === this.$p.t('benotungstool/c4positiv')) return option?.positiv === true;
			if (filter === this.$p.t('benotungstool/c4negativ')) return option?.positiv === false;
			if (filter === this.$p.t('benotungstool/c4noteEmpty')) return value === null;

			return value != null && option?.bezeichnung === filter;
		},

		bezeichnungOf(note) {
			return this.notenOptions?.find(n => n.note == note)?.bezeichnung;
		},

		// === Answers of the server ==================================================================

		/** The apply button: the proposal differs from the LV-Note, and the server does not lock the LV-Note. */
		canApplyProposal(student) {
			return student.proposed_note != null && student.proposed_note != student.lv_note && !student.verlauf.lvNoteLocked;
		},

		/** A pass closed the chain: the next Pruefung cell names the reason. An Anrechnung stays empty. */
		showBestandenHint(student) {
			return !student.verlauf.canAdd && student.verlauf.bestanden && !student.verlauf.angerechnet;
		},

		/** Is this day (Y-m-d) too early for a new Pruefung of the student? The server sends the first free day. */
		isBeforeEarliestNewDay(student, day) {
			const earliest = student.verlauf.earliestNewDay;

			return earliest != null && day < earliest;
		},

		/**
		 * The proposal editor: the server locks the LV-Note (a repeat, a final Freigabe, a locked
		 * Zeugnisnote). In the Punkte mode the Punkte give the Note. Tabulator also asks for its
		 * calculation row, which has no Verlauf.
		 */
		canEditProposal(student) {
			return !!student.verlauf && !this.config.CIS_GESAMTNOTE_PUNKTE && !student.verlauf.lvNoteLocked;
		},

		/**
		 * Warns and returns false if the student cannot get a Pruefung on this day. The server decides;
		 * the warning comes before the request.
		 */
		checkNewPruefung(student, day, suffix) {
			if (!student.verlauf.canAdd) {
				this.warnNoAntritt(student, suffix);
				return false;
			}

			if (this.isBeforeEarliestNewDay(student, day)) {
				// the last Pruefung has the latest date: the order of the Verlauf puts undated ones first
				const last = this.pruefungenOf(student).at(-1);
				this.$fhcAlert.alertWarning(this.$capitalize(this.$p.t('benotungstool/c4pruefungBereitsAmDatum', [
					student.uid, isoToDmy(last.datum), suffix
				])));
				return false;
			}

			return true;
		},

		/** Why this row gets no further Antritt: a locked kommissionelle Pruefung, a pass, or the limit. */
		warnNoAntritt(student, suffix) {
			if (student.verlauf.kommPruefLocked) {
				this.$fhcAlert.alertWarning(this.$capitalize(this.$p.t('benotungstool/kommPruefNichtErlaubt', [student.uid])));
			} else if (student.verlauf.bestanden) {
				this.$fhcAlert.alertWarning(this.$capitalize(this.$p.t('benotungstool/pruefungNachBestandenerNote', [student.uid])));
			} else {
				this.$fhcAlert.alertWarning(this.$capitalize(this.$p.t('benotungstool/c4antritteAufgebraucht', [
					student.uid, student.verlauf.antrittCount, suffix
				])));
			}
		},

		// === Pruefungen ==============================================================================

		/** The cell dialog (PruefungDialog.js): one new or changed Pruefung. */
		savePruefung(student, pruefung) {
			this.loading = true;
			this.$api.call(ApiNoten.savePruefung(this.lvId, this.semKurzbz, student.uid, pruefung))
				.then(res => {
					this.$fhcAlert.alertDefault('success', 'Info',
						this.$capitalize(this.$p.t('benotungstool/pruefungSaveForUid', [student.uid])), true);

					this.applyRows(res.data);
					this.refreshTable();
				})
				.finally(() => this.loading = false);
		},

		/** "New Pruefung" (PruefungDialog.js): one Pruefung on the same day for each selected student. */
		createPruefungen(pruefung) {
			const suffix = this.$p.t('benotungstool/c4keinePruefungAngelegt');

			const students = this.selectedStudents
				.filter(student => this.checkNewPruefung(student, pruefung.datum, suffix))
				.map(student => ({ uid: student.uid, lehreinheit_id: student.lehreinheit_id }));
			if (!students.length) return;

			this.loading = true;
			this.$api.call(ApiNoten.createPruefungen(this.lvId, this.semKurzbz, students, pruefung))
				.then(res => {
					const { saved, errors } = this.applyRows(res.data);

					if (errors.length) {
						this.$fhcAlert.alertError(this.$capitalize(this.$p.t('benotungstool/c4pruefungAnlageError', [isoToDmy(pruefung.datum)]))
							+ ': ' + errors.map(e => e.uid + ' - ' + e.message).join('\n'));
					}
					if (saved.length) {
						this.$fhcAlert.alertDefault('success', 'Info',
							this.$capitalize(this.$p.t('benotungstool/pruefungAngelegtAn', [isoToDmy(pruefung.datum)])) + ': ' + saved.join(' '), true);
						this.refreshTable();
					}
				})
				.finally(() => this.loading = false);
		},

		// === Proposal ===============================================================================

		/** The Note of these Punkte, from the Notenschluessel of the LV. @returns {Promise<*>} */
		noteForPunkte(punkte) {
			return this.$api
				.call(ApiNoten.getNoteByPunkte(this.lvId, this.semKurzbz, punkte === '' ? null : punkte))
				.then(res => res.data);
		},

		/** The Punkte editor: each input asks the server for the Note, and the proposal shows it. */
		punkteEditor(cell, onRendered, success, cancel) {
			const editor = document.createElement("input");
			editor.setAttribute("type", "number");
			editor.value = cell.getValue();

			const row = cell.getRow();
			const showNote = debounce((punkte) => {
				this.noteForPunkte(punkte).then(note => {
					if (note >= 0) {
						row.update({ proposed_note: note });
						row.reformat();
					}
				});
			}, 500);

			editor.addEventListener("input", (e) => showNote(e.target.value));
			editor.addEventListener("change", () => success(editor.value));
			editor.addEventListener("blur", () => success(editor.value));
			editor.addEventListener("keydown", (e) => {
				if (e.key === 'Enter') success(editor.value);
				if (e.key === 'Escape') cancel();
			});

			onRendered(() => {
				editor.focus();
				editor.style.height = "100%";
			});

			return editor;
		},

		/** A second click on the open list editor closes it. A click on an option still selects it. */
		onCellEditing(cell) {
			if (cell.getField() !== 'proposed_note') return;
			const element = cell.getElement();
			if (!element) return;

			this.detachProposalEditorToggle();

			const listener = (e) => {
				if (e.target?.closest?.('.tabulator-edit-list')) return;
				e.stopPropagation();
				try { cell.cancelEdit(); } catch (error) { /* the editor is closed already */ }
				// the option list is outside the cell and stays after cancelEdit
				document.querySelectorAll('.tabulator-edit-list').forEach(list => list.remove());
			};
			this._proposalEditorElement = element;
			this._proposalEditorListener = listener;

			// wait one tick: the click that opened the editor must not close it; the capture phase
			// also reaches a click that the editor stops
			setTimeout(() => {
				if (this._proposalEditorElement === element && this._proposalEditorListener === listener) {
					element.addEventListener('mousedown', listener, true);
				}
			}, 0);
		},

		onCellEdited(cell) {
			if (cell.getField() !== 'proposed_note') return;

			const row = cell.getRow();
			const student = row.getData();

			// no selection: the value before the edit comes back
			if (cell.getValue() == null || cell.getValue() === '') cell.setValue(student._proposedNoteBeforeEdit, true);
			delete student._proposedNoteBeforeEdit;

			// the apply button depends on the value
			row.reformat();
			this.detachProposalEditorToggle();
		},

		onCellEditCancelled(cell) {
			if (cell.getField() === 'proposed_note') this.detachProposalEditorToggle();
		},

		detachProposalEditorToggle() {
			this._proposalEditorElement?.removeEventListener('mousedown', this._proposalEditorListener, true);
			this._proposalEditorElement = null;
			this._proposalEditorListener = null;
		},

		/**
		 * The apply button asks for the day of the assessment first. The server writes the LV-Note AND
		 * Antritt 1 on that day, so the chain is complete from the start.
		 */
		openApplyProposalModal(e, cell) {
			const student = cell.getRow().getData();
			if (!this.canApplyProposal(student)) return;

			this.proposalStudent = student;
			// Antritt 1 of the row, else the first free date column, else today
			this.proposalDate = parseIsoDate(this.pruefungenOf(student)[0]?.datum) ?? this.firstFreeDateColumn(student) ?? today();
			// an assessment that did not happen yet has no date
			this.proposalMaxDate = this.config.CIS_GESAMTNOTE_DATUM_ZUKUNFT ? null : today();

			this.$refs.modalApplyProposal.show();
		},

		/**
		 * The first Pruefung date of the LV that this row does not use yet. The 'datum' layout has one
		 * column per date, so the proposal fills the column that is still empty in this row. A day in
		 * the future is no assessment date.
		 *
		 * @returns {Date|null}
		 */
		firstFreeDateColumn(student) {
			const used = new Set(this.pruefungenOf(student).map(p => String(p.datum ?? '').slice(0, 10)));
			const todayIso = toIsoDate(today());

			const free = this.distinctPruefungsDates.find(datum => {
				const day = String(datum ?? '').slice(0, 10);
				return day !== '' && day <= todayIso && !used.has(day);
			});

			return free ? parseIsoDate(free) : null;
		},

		/** Writes the proposal as the LV-Note, with the chosen day as Antritt 1. */
		applyProposal() {
			const student = this.proposalStudent;
			if (!student) return;

			this.$refs.modalApplyProposal.hide();
			this.loading = true;
			this.$api.call(ApiNoten.saveLvNote(
				this.lvId, this.semKurzbz, student.uid, student.proposed_note, student.proposed_punkte ?? null, toIsoDate(this.proposalDate)
			))
				.then(res => {
					this.applyRows(res.data);
					this.refreshTable();
				})
				.finally(() => {
					this.proposalStudent = null;
					this.loading = false;
				});
		},

		// === Import =================================================================================

		/** The rows of the Pruefung import. Rows that cannot get a Pruefung stay here with a warning. */
		importPruefungen(rows) {
			const suffix = this.$p.t('benotungstool/c4zeileUebersprungen');
			const pruefungen = rows.filter(row => this.checkNewPruefung(this.students.find(s => s.uid === row.uid), row.datum, suffix));
			if (!pruefungen.length) return;

			this.loading = true;
			this.$api.call(ApiNoten.importPruefungen(this.lvId, this.semKurzbz, pruefungen))
				.then(res => this.showImportResult(res.data, 'pruefungImportSuccessAlert'))
				.finally(() => this.loading = false);
		},

		importLvNoten(rows) {
			if (!rows.length) return;

			this.loading = true;
			this.$api.call(ApiNoten.importLvNoten(this.lvId, this.semKurzbz, rows))
				.then(res => this.showImportResult(res.data, 'notenImportSuccessAlert'))
				.finally(() => this.loading = false);
		},

		/** An import answers per row. A rejected row reports its message; only a saved row is a success. */
		showImportResult(data, successPhrase) {
			const { saved, errors } = this.applyRows(data);

			if (errors.length) this.$fhcAlert.alertError(errors.map(e => e.message).join('\n'));
			if (saved.length) this.$fhcAlert.alertDefault('success', 'Info', this.$capitalize(this.$p.t('benotungstool/' + successPhrase)), true);

			this.refreshTable();
		},

		// === Freigabe ===============================================================================

		/** The Freigabe dialog (FreigabeDialog.js) sends the changed LV-Noten. */
		saveFreigabe(password) {
			const uids = this.changedLvNoten.map(s => s.uid);

			this.loading = true;
			this.$api.call(ApiNoten.saveFreigabe(this.lvId, this.semKurzbz, password, uids))
				.then(res => {
					// the Freigabe writes Antritt 1, which can need a new column
					const { saved } = this.applyRows(res.data);
					if (saved.length) this.$fhcAlert.alertDefault('success', 'Info', this.$capitalize(this.$p.t('benotungstool/c4notenGespeichert')), true);

					this.refreshTable();
				})
				.finally(() => this.loading = false);
		}
	},
	template: `
		<benotungstool-import
			ref="importDialogs"
			:students="students ?? []"
			:config="config"
			:noten-options="notenOptions ?? []"
			:lehrveranstaltung="selectedLehrveranstaltung"
			:sem-kurzbz="selectedSemester?.studiensemester_kurzbz"
			:lehreinheiten="lehreinheiten"
			:selected-lehreinheit="selectedLehreinheit"
			@import-pruefungen="importPruefungen"
			@import-lv-noten="importLvNoten" />

		<benotungstool-pruefung-dialog
			ref="pruefungDialog"
			:config="config"
			:noten-options="notenOptions ?? []"
			:lv-id="lvId"
			:sem-kurzbz="semKurzbz"
			:student-options="studentOptions"
			:selected-students="selectedStudents"
			@select-students="selectStudents"
			@save-pruefung="savePruefung"
			@create-pruefungen="createPruefungen" />

		<benotungstool-freigabe-dialog
			ref="freigabeDialog"
			:lv-noten="changedLvNoten"
			:noten-options="notenOptions ?? []"
			@save-freigabe="saveFreigabe" />

		<bs-modal data-cy="modal-apply-proposal" ref="modalApplyProposal" class="bootstrap-prompt" bodyClass="px-3 py-3">
			<template v-slot:title>{{ $capitalize($p.t('benotungstool/c4notenvorschlagUebernehmen')) }}: {{ proposalStudent?.vorname }} {{ proposalStudent?.nachname }}</template>
			<template v-slot:default>
				<div class="d-flex align-items-center gap-2">
					<span class="text-nowrap">{{ $capitalize($p.t('benotungstool/c4benotungsdatum')) }}:</span>
					<div class="flex-grow-1" data-cy="apply-proposal-datum">
						<datepicker v-model="proposalDate" :clearable="false" :enableTimePicker="false" format="dd.MM.yyyy" placeholder="TT.MM.JJJJ"
							:max-date="proposalMaxDate" :text-input="true" :auto-apply="true" autocomplete="off">
						</datepicker>
					</div>
				</div>
				<div class="text-muted small mt-2" data-cy="apply-proposal-hint">{{ $capitalize($p.t('benotungstool/c4benotungsdatumHinweis')) }}</div>
			</template>
			<template v-slot:footer>
				<button type="button" data-cy="apply-proposal-submit" class="btn btn-primary" @click="applyProposal">{{ $capitalize($p.t('global/speichern')) }}</button>
			</template>
		</bs-modal>

		<BsOffcanvas ref="drawer" placement="end" :backdrop="true" :style="{ '--bs-offcanvas-width': '600px' }">
			<MobilityLegende/>
		</BsOffcanvas>

		<FhcOverlay :active="loading"></FhcOverlay>

		<div class="row align-items-center gy-2 mb-2">
			<div class="col-12 col-xxl-auto">
				<h2 class="mb-0">{{ $capitalize($p.t('benotungstool/benotungstoolTitle')) }}</h2>
				<h5 class="mb-0 text-truncate" style="max-width: 22rem;">{{ selectedLehrveranstaltung?.lv_bezeichnung }}</h5>
			</div>

			<div class="col-12 col-xxl">
				<div class="d-flex flex-wrap align-items-center justify-content-xxl-end" style="gap: 0.5rem;">
					<div v-if="isAssistenz" class="d-flex align-items-center" style="flex: 1 1 12rem; min-width: 9rem; gap: 0.35rem;">
						<label class="col-form-label py-0 text-nowrap flex-shrink-0 d-none d-xxl-inline">{{ $capitalize($p.t('lehre/studiengang')) }}:</label>
						<Dropdown data-cy="dropdown-studiengang" @change="onStudiengangChange" class="flex-grow-1" :style="{'minWidth': '0'}" optionLabel="fullString"
							:placeholder="$capitalize($p.t('lehre/studiengang'))" v-model="selectedStudiengang" :options="assistenzStudiengaenge" appendTo="self">
						</Dropdown>
					</div>

					<div class="d-flex align-items-center" style="flex: 1 1 12rem; min-width: 9rem; gap: 0.35rem;">
						<label class="col-form-label py-0 text-nowrap flex-shrink-0 d-none d-xxl-inline">{{ $capitalize($p.t('lehre/lehrveranstaltung')) }}:</label>
						<Dropdown data-cy="dropdown-lehrveranstaltung" @change="onLvChange" class="flex-grow-1" :style="{'minWidth': '0'}" optionLabel="fullString"
							:placeholder="$capitalize($p.t('lehre/lehrveranstaltung'))" v-model="selectedLehrveranstaltung" :options="lehrveranstaltungen" appendTo="self">
						</Dropdown>
					</div>

					<div class="d-flex align-items-center" style="flex: 1 1 12rem; min-width: 9rem; gap: 0.35rem;">
						<label class="col-form-label py-0 text-nowrap flex-shrink-0 d-none d-xxl-inline">{{ $capitalize($p.t('lehre/lehreinheit')) }}:</label>
						<Dropdown data-cy="dropdown-lehreinheit" class="flex-grow-1" :style="{'minWidth': '0'}" optionLabel="infoString"
							:placeholder="$capitalize($p.t('lehre/lehreinheit'))" :options="lehreinheiten"
							v-model="selectedLehreinheit" showClear appendTo="self">
							<template #option="slotProps">
								<div>
									{{ slotProps.option.infoString }}
									<i class="fa-solid fa-user"></i>
									{{ slotProps.option.studentcount }}
									<i class="fa-solid fa-calendar-days"></i>
									{{ slotProps.option.termincount }}
								</div>
							</template>
						</Dropdown>
					</div>

					<div class="d-flex align-items-center" style="flex: 1 1 8rem; min-width: 7rem; gap: 0.35rem;">
						<label class="col-form-label py-0 text-nowrap flex-shrink-0 d-none d-xxl-inline">{{ $capitalize($p.t('lehre/studiensemester')) }}:</label>
						<Dropdown data-cy="dropdown-semester" @change="onSemesterChange" class="flex-grow-1" :style="{'minWidth': '0'}" optionLabel="studiensemester_kurzbz"
							v-model="selectedSemester" :options="studiensemester" appendTo="self">
						</Dropdown>
					</div>
				</div>
			</div>
		</div>

		<div id="notentable" data-cy="benotungstool-table" class="row" style="overflow-x: auto;">
			<core-filter-cmpt
				v-if="canBuildTable"
				ref="notenTable"
				:title="''"
				:description="selectionSummary"
				:useSelectionSpan="false"
				:tabulator-options="notenTableOptions"
				:tabulator-events="tableEvents"
				:sideMenu="false"
				tableOnly
				@uuidDefined="onUuidDefined"
				@tableBuilt="onTableBuilt">
				<template #actions>
					<Multiselect v-model="stickySelection" :options="stickyOptions" optionLabel="label" optionValue="field"
						:placeholder="$capitalize($p.t('benotungstool/freezeColumnsToggle'))" :maxSelectedLabels="0"
						:selectedItemsLabel="$capitalize($p.t('benotungstool/freezeColumnsLabel'))" showToggleAll
						@change="onStickySelectionChange" class="ml-2" style="min-width: 12rem" />

					<div class="btn-group ml-2" role="group">
						<button type="button" :class="columnMode === 'antritt' ? 'btn btn-primary' : 'btn btn-outline-primary'"
							:title="$capitalize($p.t('benotungstool/c4spaltenTerminHint'))" @click="setColumnMode('antritt')">
							{{ $capitalize($p.t('benotungstool/c4spaltenTermin')) }}
						</button>
						<button type="button" :class="columnMode === 'datum' ? 'btn btn-primary' : 'btn btn-outline-primary'"
							:title="$capitalize($p.t('benotungstool/c4spaltenDatumHint'))" @click="setColumnMode('datum')">
							{{ $capitalize($p.t('benotungstool/c4spaltenDatum')) }}
						</button>
					</div>

					<button data-cy="btn-new-pruefung" @click="$refs.pruefungDialog.openForSelection()" role="button" class="btn btn-primary ml-2">
						{{ $capitalize($p.t('benotungstool/c4addNewPruefung')) }} <i class="fa fa-plus"></i>
					</button>
					<button data-cy="btn-pruefung-import" v-if="config?.CIS_GESAMTNOTE_PRUEFUNGSIMPORT" @click="$refs.importDialogs.openPruefungen()" role="button" class="btn btn-primary ml-2">
						{{ $capitalize($p.t('benotungstool/c4pruefungImportieren')) }} <i class="fa fa-file-import"></i>
					</button>
					<button data-cy="btn-lvnoten-import" v-if="config?.CIS_GESAMTNOTE_NOTENIMPORT" @click="$refs.importDialogs.openLvNoten()" role="button" class="btn btn-primary ml-2">
						{{ $capitalize($p.t('benotungstool/c4notenImportieren')) }} <i class="fa fa-file-import"></i>
					</button>
					<button data-cy="btn-freigabe" @click="$refs.freigabeDialog.open()" role="button" :class="freigabeButtonClass">
						{{ $capitalize($p.t('benotungstool/approveGradesv2', [changedLvNoten.length])) }} <i class="fa fa-save"></i>
					</button>
				</template>
			</core-filter-cmpt>
		</div>
	`
};

export default Benotungstool;
