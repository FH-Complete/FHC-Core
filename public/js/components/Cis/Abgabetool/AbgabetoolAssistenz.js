import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeMitarbeiterDetail.js";
import BsModal from '../../Bootstrap/Modal.js';
import BsOffcanvas from '../../Bootstrap/Offcanvas.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiAbgabe from '../../../api/factory/abgabe.js'
import ApiStudiensemester from '../../../api/factory/studiensemester.js';
import AbgabeterminStatusLegende from "./StatusLegende.js";
import FhcOverlay from "../../Overlay/FhcOverlay.js";
import AbgabeStudentTimeline from "./AbgabeStudentTimeline.js";
import { splitMailsHelper } from "../../../helpers/EmailHelpers.js"
import { getDateStyleClass} from "./getDateStyleClass.js";
import { dateFilter } from '../../../tabulator/filters/DatesManual.js';
import { compareISODateValues, formatISODate, getViennaTodayISO, toViennaDate } from "./dateUtils.js";

export const AbgabetoolAssistenz = {
	name: "AbgabetoolAssistenz",
	components: {
		AbgabeterminStatusLegende,
		AbgabeStudentTimeline,
		BsModal,
		BsOffcanvas,
		CoreFilterCmpt,
		AbgabeDetail,
		Checkbox: primevue.checkbox,
		Dropdown: primevue.dropdown,
		Inplace: primevue.inplace,
		Textarea: primevue.textarea,
		Timeline: primevue.timeline,
		TieredMenu: primevue.tieredmenu,
		VueDatePicker,
		FhcOverlay
	},
	provide() {
		return {
			abgabeTypeOptions: Vue.computed(() => this.abgabeTypeOptions),
			allowedNotenOptions: Vue.computed(() => this.allowedNotenOptions),
			notenOptionsNonFinal: Vue.computed(() => this.notenOptionsNonFinal),
			turnitin_link: Vue.computed(() => this.turnitin_link),
			old_abgabe_beurteilung_link: Vue.computed(() => this.old_abgabe_beurteilung_link),
			abgabetypenBetreuer: Vue.computed(() => this.abgabeTypeOptions)
		}
	},
	props: {
		stg_kz_prop: {
			default: null
		},
	},
	data() {
		return {
			flatDataDirty: true,
			mode: 'perProjectView',
			qgate1FilterSelected: [],
			qgate2FilterSelected: [],
			pa_noteFilterSelected: [],
			noteFilterSelected: [],
			count: 0,
			filteredcount: 0,
			selectedcount: 0,
			countFlat: 0,
			filteredcountFlat: 0,
			selectedcountFlat: 0,
			filteredRows: null,
			filteredRowsFlat: null,
			studiensemesterOptions: null,
			allSem: null,
			allSemOption: null,
			curSem: null,
			notenOptionFilter: null,
			inplaceToggle: false,
			headerFiltersRestored: false,
			filtersRestored: false,
			colLayoutRestored: false,
			sortRestored: false,
			stateRestored: false,
			headerFiltersRestoredFlat: false,
			filtersRestoredFlat: false,
			colLayoutRestoredFlat: false,
			sortRestoredFlat: false,
			stateRestoredFlat: false,
			timelineProjekte: [],
			selectedStudiengangOption: null,
			studiengaengeOptions: null,
			detailIsFullscreen: false,
			allConfigPromise: null,
			phrasenPromise: null,
			phrasenResolved: false,
			turnitin_link: null,
			old_abgabe_beurteilung_link: null,
			ASSISTENZ_SAMMELMAIL_BUTTON_STUDENT: null,
			ASSISTENZ_SAMMELMAIL_BUTTON_BETREUER: null,
			MULTIEDIT_TABLE: false,
			saving: false,
			loading: false,
			abgabeTypeOptions: null,
			notenOptions: null,
			allowedNotenFilterOptions: null,
			allowedNotenOptions: null,
			notenOptionsNonFinal: null,
			serienEdit: Vue.reactive({
				datum: null,
				bezeichnung: null,
				kurzbz: null,
				upload_allowed: null,
				fixtermin: null,
				invertedFixtermin: null,
			}),
			// track which fields should actually be applied
			serienEditFields: {
				datum: false,
				bezeichnung: false,
				kurzbz: false,
				upload_allowed: false,
				fixtermin: false,
			},
			serienTermin: Vue.reactive({
				datum: getViennaTodayISO(),
				bezeichnung: {
					paabgabetyp_kurzbz: 'zwischen',
					bezeichnung: 'Zwischenabgabe'
				},
				kurzbz: '',
				fixtermin: false,
				invertedFixtermin: true,
				upload_allowed: false
			}),
			showAll: false,
			tabulatorUuid: Vue.ref(0),
			tabulatorUuidFlat: Vue.ref(0),
			selectedData: [],
			selectedDataFlat: [],
			domain: '',
			student_uid: null,
			detail: null,
			detailOffset: 0,
			projektarbeiten: null,
			selectedProjektarbeit: null,
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			tableBuiltResolveFlat: null,
			tableBuiltPromiseFlat: null,
			abgabeTableOptions: {
				minHeight: 250,
				index: 'projektarbeit_id',
				layout: 'fitData',
				placeholder: Vue.computed(() => this.$capitalize(this.$p.t('global/noDataAvailable'))),
				selectable: true,
				selectableCheck: this.selectionCheck,
				renderVerticalBuffer: 2000,
				columns: [
					{
						formatter: function (cell, formatterParams, onRendered) {
							// create the built-in checkbox
							if(!cell.getRow().getData().selectable) return
							let checkbox = document.createElement("input");
							checkbox.type = "checkbox";

							// Handle select manually
							checkbox.addEventListener("click", (e) => {
								e.stopPropagation();

								// call our function
								if (formatterParams && formatterParams.handleClick) {
									formatterParams.handleClick(e, cell);
								}
							});

							cell.getRow().getData().checkbox = checkbox

							let wrapper = document.createElement("div");
							wrapper.style.cssText = "display: flex; justify-content: center; align-items: center; height: 100%; width: 100%;";

							wrapper.appendChild(checkbox);

							return wrapper;
						},
						titleFormatter: function (cell, formatterParams, onRendered) {
							// create the built-in checkbox
							let checkbox = document.createElement("input");
							checkbox.type = "checkbox";

							// Handle "select all" manually
							checkbox.addEventListener("click", (e) => {
								e.stopPropagation();

								// call our function
								if (formatterParams && formatterParams.handleClick) {
									formatterParams.handleClick(e, cell);
								}
							});

							return checkbox;
						},
						hozAlign: "center",
						headerSort: false,
						formatterParams: {
							handleClick: this.selectHandler
						},
						titleFormatterParams: {
							handleClick: this.selectAllHandler
						},
						width: 50,
						cssClass: 'sticky-col',
						visible: true
					},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4details'))), field: 'details', headerFilter: false, headerSort: false, formatter: this.formAction, tooltip:false, minWidth: 130, visible: true, cssClass: 'sticky-col'},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4personenkennzeichen'))), headerFilter: true, field: 'pkz', formatter: this.pkzTextFormatter, minWidth: 140, tooltip: false, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4vorname'))), field: 'student_vorname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nachname'))), field: 'student_nachname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4studstatus'))), field: 'studienstatus', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 150, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4orgformv2'))), field: 'orgform', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 50, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4projekttyp'))), field: 'projekttyp_kurzbz', formatter: this.centeredTextFormatter, minWidth: 150, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4stg'))), field: 'stg', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 50, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4note'))), field: 'note_bez', headerFilter: true, sorter: this.notenSorter, visible: false, minWidth: 200, formatter: this.centeredTextFormatter},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4sem'))), field: 'studiensemester_kurzbz', headerFilter: true, visible: false, formatter: this.centeredTextFormatter, minWidth: 100},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4titel'))), field: 'titel', headerFilter: true,  formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuerv2'))), field: 'erstbetreuer', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuerTitelPre'))), field: 'betreuer_titelpre', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuerVorname'))), field: 'betreuer_vorname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuerNachname'))), field: 'betreuer_nachname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuerTitelPost'))), field: 'betreuer_titelpost', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},

					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuerv2'))), field: 'zweitbetreuer', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},

					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuerTitelPre'))), field: 'zweitbetreuer_titelpre', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuerVorname'))), field: 'zweitbetreuer_vorname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuerNachname'))), field: 'zweitbetreuer_nachname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuerTitelPost'))), field: 'zweitbetreuer_titelpost', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},

					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4prevAbgabetermin'))),
						headerFilter: dateFilter,
						headerFilterFunc: this.headerFilterTerminCol,
						sorter: this.sortFuncTerminCol,
						tooltip: this.toolTipFuncPrevTermin,
						field: 'prevTermin', formatter: this.abgabterminFormatter, width: 250, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nextAbgabetermin'))), field: 'nextTermin',
						headerFilter: dateFilter,
						headerFilterFunc: this.headerFilterTerminCol,
						sorter: this.sortFuncTerminCol,
						tooltip: this.toolTipFuncNextTermin,
						formatter: this.abgabterminFormatter, width: 250, visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4qgate1Status'))),
						headerFilter: this.qgateHeaderFilterEditor,
						headerFilterFunc: this.qgateHeaderFilterFunc,
						headerFilterParams: {},
						field: 'qgate1Status',
						formatter: this.centeredTextFormatter,
						titleFormatter: this.shortLongTitleFormatter,
						titleFormatterParams: {
							shortForm: 'QG1'
						},
						width: 50,
						tooltip: (e, cell) => {
							const data = cell.getData();
							return data.qgate1Status
						}
					},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4qgate2Status'))),
						headerFilter: this.qgateHeaderFilterEditor,
						headerFilterFunc: this.qgateHeaderFilterFunc,
						headerFilterParams: {},
						field: 'qgate2Status', 
						formatter: this.centeredTextFormatter,
						titleFormatter: this.shortLongTitleFormatter,
						titleFormatterParams: {
							shortForm: 'QG2'
						},
						width: 50,
						tooltip: (e, cell) => {
							const data = cell.getData();
							return data.qgate2Status
						}
					},
				],
				persistence: false,
				persistenceID: "abgabetool_2026_03_16"
			},
			abgabeTableEventHandlers: [
			{
				event: "rowSelectionChanged",
				handler: async(data) => 
				{
					this.selectedData.filter(sd => !data.includes(sd)).forEach(fsd => {
						if(fsd.checkbox) fsd.checkbox.checked = false
					})

					data.forEach(d => {
						if(d.checkbox) d.checkbox.checked = true
					})
					
					this.selectedData = data
					this.selectedcount = data.length;
				}
			},
			{
				event: 'dataFiltered',
				handler: (filters, rows) => {
					this.filteredRows = rows;
					this.filteredcount = rows.length;

					if (!this.selectedData.length) return;

					const visibleData = new Set(rows.map(r => r.getData()));
					const filteredOut = this.selectedData.filter(sd => !visibleData.has(sd));

					if (!filteredOut.length) return;

					const filteredOutSet = new Set(filteredOut);
					this.$refs.abgabeTable.tabulator.getSelectedRows()
						.filter(r => filteredOutSet.has(r.getData()))
						.forEach(r => r.deselect());
				}
			}],
			abgabeTableOptionsFlat: {
				minHeight: 250,
				height: 700,
				index: 'paabgabe_id',
				layout: 'fitColumns',
				placeholder: Vue.computed(() => this.$capitalize(this.$p.t('global/noDataAvailable'))),
				selectable: true,
				renderVerticalBuffer: 400,
				columns: [
					{
						formatter: function (cell, formatterParams, onRendered) {
							// create the built-in checkbox
							if(!cell.getRow().getData().selectable) return
							let checkbox = document.createElement("input");
							checkbox.type = "checkbox";

							// Handle select manually
							checkbox.addEventListener("click", (e) => {
								e.stopPropagation();

								// call our function
								if (formatterParams && formatterParams.handleClick) {
									formatterParams.handleClick(e, cell);
								}
							});

							cell.getRow().getData().checkbox = checkbox

							let wrapper = document.createElement("div");
							wrapper.style.cssText = "display: flex; justify-content: center; align-items: center; height: 100%; width: 100%;";

							wrapper.appendChild(checkbox);

							return wrapper;
						},
						titleFormatter: function (cell, formatterParams, onRendered) {
							// create the built-in checkbox
							let checkbox = document.createElement("input");
							checkbox.type = "checkbox";

							// Handle "select all" manually
							checkbox.addEventListener("click", (e) => {
								e.stopPropagation();

								// call our function
								if (formatterParams && formatterParams.handleClick) {
									formatterParams.handleClick(e, cell);
								}
							});

							return checkbox;
						},
						hozAlign: "center",
						headerSort: false,
						formatterParams: {
							handleClick: this.selectHandler
						},
						titleFormatterParams: {
							handleClick: this.selectAllHandlerFlat
						},
						width: 50,
						cssClass: 'sticky-col'
					},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4personenkennzeichen'))), headerFilter: true, field: 'pkz', formatter: this.pkzTextFormatter, minWidth: 140, tooltip: false, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4vorname'))), field: 'student_vorname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nachname'))), field: 'student_nachname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4studstatus'))), field: 'studienstatus', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 150, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4orgformv2'))), field: 'orgform', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 50, visible: false},
					{
						title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4abgabetyp'))),
						field: 'paabgabetyp_kurzbz',
						headerFilter: true,
						formatter: this.paabgabetypFormatter,
						
						minWidth: 120,
					},
					{
						title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4abgabekurzbzv2'))),
						field: 'kurzbz',
						headerFilter: true,
						formatter: this.centeredTextFormatter,
						minWidth: 120
					},
					{
						title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zieldatumv2'))),
						field: 'datum',
						headerFilter: dateFilter,
						headerFilterFunc: this.headerFilterTerminColISO,
						sorter: compareISODateValues,
						formatter: (cell) => this.formatDate(cell.getValue()),
						minWidth: 100
					},
					{
						title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4abgabedatum'))),
						field: 'abgabedatum',
						headerFilter: dateFilter,
						headerFilterFunc: this.headerFilterTerminColISO,
						sorter: compareISODateValues,
						formatter: (cell) => this.formatDate(cell.getValue()),
						minWidth: 100
					},
					{
						title: 'Status',
						field: 'dateStyle',
						headerSort: false,
						headerFilter: this.statusHeaderFilterEditor,
						headerFilterFunc: this.statusHeaderFilterFunc,
						headerFilterParams: {},
						formatter: this.abgabterminFormatter,
						formatterParams: { iconOnly: true },
						width: 70,
						tooltip: (e, cell) => this.mapDateStyleToTabulatorTooltip(cell.getValue())
					},
					{
						title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4noteprojektarbeit'))),
						field: 'pa_note',
						formatter: (cell) => {
							const val = cell.getValue();
							if (!val) return '';
							return val?.bezeichnung ?? this.notenOptions?.find(n => n.note == val)?.bezeichnung ?? val;
						},
						sorter: this.notenSorterFlat,
						minWidth: 100,
						tooltip: false,
						headerFilter: this.notenHeaderFilterEditor,
						headerFilterFunc: this.notenHeaderFilterFunc,
						headerFilterParams: {},
					},
					{
						title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4notetermin'))),
						field: 'note',
						formatter: (cell) => {
							const val = cell.getValue();
							if (!val) return '';
							return val?.bezeichnung ?? this.notenOptions?.find(n => n.note == val)?.bezeichnung ?? val;
						},
						minWidth: 100,
						headerFilter: this.notenHeaderFilterEditor,
						headerFilterFunc: this.notenHeaderFilterFunc,
						headerFilterParams: {},
					},
					{
						title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4notizQualGatev2'))),
						field: 'beurteilungsnotiz',
						headerFilter: true,
						formatter: this.centeredTextFormatter,
						minWidth: 150, visible: false
					},
					{
						title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4fixterminv4'))),
						field: 'fixtermin',
						hozAlign: 'center',
						formatter: 'tickCross',
						width: 80,
						headerFilter: 'tickCross',
						headerFilterParams: { tristate: true },
					},
					{
						title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4upload_allowed'))),
						field: 'upload_allowed',
						hozAlign: 'center',
						formatter: 'tickCross',
						width: 80,
						headerFilter: 'tickCross',
						headerFilterParams: { tristate: true },
					},
				],
				persistence: false,
				persistenceID: "abgabetoolflat_2026_05_05"
			},
			abgabeTableEventHandlersFlat: [
				{
					event: "rowSelectionChanged",
					handler: async(data) =>
					{
						this.selectedDataFlat.filter(sd => !data.includes(sd)).forEach(fsd => {
							if(fsd.checkbox) fsd.checkbox.checked = false
						})

						data.forEach(d => {
							if(d.checkbox) d.checkbox.checked = true
						})

						this.selectedDataFlat = data
						this.selectedcountFlat = data.length;
					}
				},
				{
					event: 'dataFiltered',
					handler: (filters, rows) => {
						this.filteredRowsFlat = rows;
						this.filteredcountFlat = rows.length;

						if (!this.selectedDataFlat.length) return;

						const visibleData = new Set(rows.map(r => r.getData()));
						const filteredOut = this.selectedDataFlat.filter(sd => !visibleData.has(sd));

						if (!filteredOut.length) return;

						const filteredOutSet = new Set(filteredOut);
						this.$refs.abgabeTableFlat.tabulator.getSelectedRows()
							.filter(r => filteredOutSet.has(r.getData()))
							.forEach(r => r.deselect());
					}
				}
			]
		};
	},
	methods: {
		notenSorterFlat(a, b, aRow, bRow, column, dir, sorterParams) {
			// flat table has their own sort since the field is called sligthly different in that context
			// since note would be bestanden/nicht bestanden and that hardly needs sorting
			const aData = aRow.getData()
			const bData = bRow.getData()
			return aData.pa_note - bData.pa_note
		},
		notenSorter(a, b, aRow, bRow, column, dir, sorterParams) {
			const aData = aRow.getData()
			const bData = bRow.getData()
			return aData.note - bData.note
		},
		notenHeaderFilterEditor(cell, onRendered, success, cancel, editorParams) {
			if (!this.notenOptions) return;

			const field = cell.getField();
			const stateKey = field + 'FilterSelected';
			let selected = [...(this[stateKey] || [])];

			const wrapper = document.createElement('div');
			wrapper.style.cssText = 'position: relative; width: 100%;';

			const display = document.createElement('input');
			display.readOnly = true;
			display.placeholder = '';
			display.style.cssText = 'padding: 4px; width: 100%; box-sizing: border-box; cursor: default; border: 1px solid; outline: none; background: #fff; appearance: none; caret-color: transparent;';

			const dropdown = document.createElement('div');
			dropdown.style.cssText = 'display: none; position: fixed; background: #fff; border: 1px solid; z-index: 9999; min-width: 180px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);';


			// mapping evaluated at render time, not at column definition time
			const fieldOptionsMap = {
				'pa_note': this.notenOptions,
				'note': this.allowedNotenOptions,
			};
			const options = fieldOptionsMap[cell.getField()] ?? this.notenOptions;
			if (!options) return;
			
			const updateDisplay = () => {
				display.value = options
					.filter(o => selected.includes(o.note))
					.map(o => o.bezeichnung)
					.join(', ');
			};
			options.forEach(opt => {
				const row = document.createElement('label');
				row.style.cssText = 'display: flex; align-items: center; gap: 6px; padding: 4px 8px; cursor: pointer; white-space: nowrap;';
				row.addEventListener('mousedown', e => e.preventDefault());

				const cb = document.createElement('input');
				cb.type = 'checkbox';
				cb.value = opt.note;
				cb.checked = selected.includes(opt.note);
				cb.style.cssText = 'margin: 0 6px;';
				cb.addEventListener('change', () => {
					selected = cb.checked
						? [...selected, opt.note]
						: selected.filter(v => v !== opt.note);
					this[stateKey] = [...selected];
					updateDisplay();
					success([...selected]);
				});

				const labelText = document.createElement('span');
				labelText.textContent = opt.bezeichnung;

				row.appendChild(cb);
				row.appendChild(labelText);
				dropdown.appendChild(row);
			});

			updateDisplay();

			display.addEventListener('click', () => {
				if (dropdown.style.display === 'none') {
					const rect = display.getBoundingClientRect();
					dropdown.style.top = rect.bottom + 'px';
					dropdown.style.left = rect.left + 'px';
					dropdown.style.display = 'block';
				} else {
					dropdown.style.display = 'none';
				}
			});

			display.addEventListener('blur', () => {
				setTimeout(() => { dropdown.style.display = 'none'; }, 150);
			});

			document.body.appendChild(dropdown);
			wrapper.appendChild(display);
			cell.getElement().addEventListener('remove', () => dropdown.remove());
			onRendered(() => display.focus());

			return wrapper;
		},

		notenHeaderFilterFunc(filterVal, rowVal, rowData, filterParams) {
			if (!filterVal || !filterVal.length) return true;
			// rowVal is the raw integer note id or a note object
			const noteId = typeof rowVal === 'object' ? rowVal?.note : rowVal;
			return filterVal.some(val => val == noteId); // loose equality: filter vals are numbers, noteId might be string
		},
		handleFilterActiveChanged(active) {
			if(!active && this.allSemOption && this.stateRestored) {
				this.curSem = this.allSemOption
			}
		},
		reloadData() {
			this.loadProjektarbeiten()
		},
		openEditModal() {
			// reset
			this.serienEditFields = {
				datum: false,
				bezeichnung: false,
				kurzbz: false,
				upload_allowed: false,
				fixtermin: false,
			}
			this.serienEdit.datum = getViennaTodayISO()
			this.serienEdit.bezeichnung = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === 'zwischen')
			this.serienEdit.kurzbz = ''
			this.serienEdit.upload_allowed = false
			this.serienEdit.invertedFixtermin = true

			this.$refs.modalContainerEditSeries.show()
		},
		async handleEditSelectedTermine() {
			const activeFields = Object.keys(this.serienEditFields).filter(k => this.serienEditFields[k])
			if (!activeFields.length) {
				this.$fhcAlert.alertWarning(this.$p.t('abgabetool/c4noFieldsSelected'))
				return
			}

			if (await this.$fhcAlert.confirm({
				message: this.$p.t('abgabetool/c4confirm_edit_n_termine', [this.selectedDataFlat.length]),
				acceptLabel: this.$capitalize(this.$p.t('abgabetool/c4AcceptAndProceed')),
				acceptClass: 'p-button-primary',
				rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
				rejectClass: 'p-button-secondary'
			}) === false) return

			this.$refs.modalContainerEditSeries.hide()
			this.editSelectedTermine(this.selectedDataFlat)
		},
		editSelectedTermine(termine) {
			const paabgabeIDS = termine.map(t => t.paabgabe_id)

			// only send fields that were checked
			const payload = { paabgabe_ids: paabgabeIDS }
			if (this.serienEditFields.datum)        payload.datum = this.serienEdit.datum
			if (this.serienEditFields.bezeichnung)  payload.paabgabetyp_kurzbz = this.serienEdit.bezeichnung.paabgabetyp_kurzbz
			if (this.serienEditFields.kurzbz)       payload.kurzbz = this.serienEdit.kurzbz
			if (this.serienEditFields.upload_allowed) payload.upload_allowed = this.serienEdit.upload_allowed
			if (this.serienEditFields.fixtermin)    payload.fixtermin = !this.serienEdit.invertedFixtermin

			this.saving = true
			this.$api.call(ApiAbgabe.patchProjektarbeitAbgabeMultiple(payload)).then(res => {
				if (res?.meta?.status == 'success') {
					this.$fhcAlert.alertSuccess(this.$p.t('ui/gespeichert'))

					// patch local data structure
					termine.forEach(t => {
						const pa = this.projektarbeiten.find(pa => pa.projektarbeit_id == t.projektarbeit_id)
						const termin = pa.abgabetermine.find(termin => termin.paabgabe_id === t.paabgabe_id)
						if (!termin) return

						if (this.serienEditFields.datum)         termin.datum = this.serienEdit.datum
						if (this.serienEditFields.bezeichnung)   termin.paabgabetyp_kurzbz = this.serienEdit.bezeichnung.paabgabetyp_kurzbz
						if (this.serienEditFields.kurzbz)        termin.kurzbz = this.serienEdit.kurzbz
						if (this.serienEditFields.upload_allowed) termin.upload_allowed = this.serienEdit.upload_allowed
						if (this.serienEditFields.fixtermin)     termin.fixtermin = !this.serienEdit.invertedFixtermin
					})

					const updatedProjektarbeiten = new Set(termine.map(t => t.projektarbeit_id))
					updatedProjektarbeiten.forEach(pa_id => {
						const projektarbeit = this.projektarbeiten.find(pa => pa.projektarbeit_id == pa_id)
						this.checkAbgabetermineProjektarbeit(projektarbeit)
					})

					this.redrawTableScrollSave()
					this.selectedDataFlat = []
					this.selectedcountFlat = 0
					this.$refs.abgabeTableFlat.tabulator.setData(this.getAllTermine)

				} else if (res?.meta?.status == 'error') {
					this.$fhcAlert.alertError()
				}
			}).finally(() => {
				this.saving = false
			})
		},
		deleteSelectedTermine(termine) {
			const paabgabeIDS = termine.map(t => t.paabgabe_id)
			this.$api.call(ApiAbgabe.deleteProjektarbeitAbgabeMultiple(paabgabeIDS)).then( (res) => {
				if(res?.meta?.status == 'success') {
					this.$fhcAlert.alertSuccess(this.$p.t('ui/genericDeleted', [this.$p.t('abgabetool/c4abgaben_n', [paabgabeIDS.length])]))
					
					termine.forEach(t => {
						const pa = this.projektarbeiten.find(pa => pa.projektarbeit_id == t.projektarbeit_id)
						const deletedTerminIndex = pa.abgabetermine.findIndex(termin => t.paabgabe_id === termin.paabgabe_id)
						pa.abgabetermine.splice(deletedTerminIndex, 1)
					})
					
					const updatedProjektarbeiten = new Set(termine.map(t => t.projektarbeit_id))

					updatedProjektarbeiten.forEach(pa_id => {
						const projektarbeit = this.projektarbeiten.find(pa => pa.projektarbeit_id == pa_id)
						this.checkAbgabetermineProjektarbeit(projektarbeit)
					})

					this.redrawTableScrollSave()

					// update flat table with fresh computed data and clear selection
					this.selectedDataFlat = []
					this.selectedcountFlat = 0
					this.$refs.abgabeTableFlat.tabulator.setData(this.getAllTermine)
					
				} else if(res?.meta?.status == 'error'){
					this.$fhcAlert.alertError()
				}
			})
		},
		async handleDeleteSelectedTermine() {
			// TODO: check if every selected termin is actually "allowed to delete"
			
			
			if(await this.$fhcAlert.confirm({
				message: this.$p.t('abgabetool/c4confirm_delete_n_termine', [this.selectedDataFlat.length]),
				acceptLabel: 'Löschen',
				acceptClass: 'p-button-danger',
				rejectLabel: 'Zurück',
				rejectClass: 'p-button-secondary'
			}) === false) {
				return false
			} else {
				this.deleteSelectedTermine(this.selectedDataFlat)
			}
			
		},
		async switchMode() {
			if(this.mode == 'perProjectView') {
				this.mode = 'flatView'

				await this.tableBuiltPromiseFlat;
				
				if(this.flatDataDirty) {
					this.$refs.abgabeTableFlat.tabulator.setData(this.getAllTermine);
					this.flatDataDirty = false
				}
				
			} else {
				this.mode = 'perProjectView'
			}
		},
		getDateStyleHtml(dateStyle) {
			const iconMap = {
				'verspaetet':              '<i class="fa-solid fa-triangle-exclamation"></i>',
				'verpasst':                '<i class="fa-solid fa-calendar-xmark"></i>',
				'abzugeben':               '<i class="fa-solid fa-hourglass-half"></i>',
				'standard':                '<i class="fa-solid fa-clock"></i>',
				'abgegeben':               '<i class="fa-solid fa-paperclip"></i>',
				'beurteilungerforderlich': '<i class="fa-solid fa-list-check"></i>',
				'bestanden':               '<i class="fa-solid fa-check"></i>',
				'nichtbestanden':          '<i class="fa-solid fa-circle-exclamation"></i>',
			};
			return iconMap[dateStyle] ?? '';
		},
		statusHeaderFilterEditor(cell, onRendered, success, cancel, editorParams) {
			const options = [
				{ label: this.$p.t('abgabetool/c4positivBenotet'),    value: 'bestanden',              dateStyle: 'bestanden' },
				{ label: this.$p.t('abgabetool/c4negativBenotet'),    value: 'nichtbestanden',         dateStyle: 'nichtbestanden' },
				{ label: this.$p.t('abgabetool/c4tooltipVerspaetet'), value: 'verspaetet',             dateStyle: 'verspaetet' },
				{ label: this.$p.t('abgabetool/c4tooltipVerpasst'),   value: 'verpasst',               dateStyle: 'verpasst' },
				{ label: this.$p.t('abgabetool/c4tooltipAbzugeben'),  value: 'abzugeben',              dateStyle: 'abzugeben' },
				{ label: this.$p.t('abgabetool/c4tooltipAbgegeben'),  value: 'abgegeben',              dateStyle: 'abgegeben' },
				{ label: this.$p.t('abgabetool/c4tooltipBeurteilungerforderlich'), value: 'beurteilungerforderlich', dateStyle: 'beurteilungerforderlich' },
				{ label: this.$p.t('abgabetool/c4tooltipStandardv2'), value: 'standard',               dateStyle: 'standard' },
			];

			const field = cell.getField();
			const stateKey = field + 'FilterSelected'; // e.g. dateStyleFilterSelected
			let selected = [...(this[stateKey] || [])];

			const wrapper = document.createElement('div');
			wrapper.style.cssText = 'position: relative; width: 100%;';

			const display = document.createElement('input');
			display.readOnly = true;
			display.placeholder = '';
			display.style.cssText = 'padding: 4px; width: 100%; box-sizing: border-box; cursor: default; border: 1px solid; outline: none; background: #fff; appearance: none; caret-color: transparent;';

			const dropdown = document.createElement('div');
			dropdown.style.cssText = 'display: none; position: fixed; background: #fff; border: 1px solid; z-index: 9999; min-width: 220px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);';

			const updateDisplay = () => {
				display.value = options
					.filter(o => selected.includes(o.value))
					.map(o => o.label)
					.join(', ');
			};

			options.forEach(opt => {
				const row = document.createElement('label');
				row.style.cssText = 'display: flex; align-items: center; gap: 0; cursor: pointer; white-space: nowrap; padding-right: 8px;';
				row.addEventListener('mousedown', e => e.preventDefault());

				const cb = document.createElement('input');
				cb.type = 'checkbox';
				cb.value = opt.value;
				cb.checked = selected.includes(opt.value);
				cb.style.cssText = 'margin: 0 6px;';
				cb.addEventListener('change', () => {
					selected = cb.checked
						? [...selected, opt.value]
						: selected.filter(v => v !== opt.value);
					this[stateKey] = [...selected];
					updateDisplay();
					success([...selected]);
				});

				// icon badge — same look as cell
				const badge = document.createElement('div');
				badge.className = opt.dateStyle + '-header';
				badge.style.cssText = `min-width: 36px; height: 36px; display: flex; align-items: center; 
				justify-content: center; flex-shrink: 0; padding: 0px 17px 0px 17px;`;
				badge.innerHTML = this.getDateStyleHtml(opt.dateStyle);

				const labelText = document.createElement('span');
				labelText.textContent = opt.label;
				labelText.style.cssText = 'margin-left: 6px;';

				row.appendChild(cb);
				row.appendChild(badge);
				row.appendChild(labelText);
				dropdown.appendChild(row);
			});

			updateDisplay();

			display.addEventListener('click', () => {
				if (dropdown.style.display === 'none') {
					const rect = display.getBoundingClientRect();
					dropdown.style.top = rect.bottom + 'px';
					dropdown.style.left = rect.left + 'px';
					dropdown.style.display = 'block';
				} else {
					dropdown.style.display = 'none';
				}
			});

			display.addEventListener('blur', () => {
				setTimeout(() => { dropdown.style.display = 'none'; }, 150);
			});

			document.body.appendChild(dropdown);
			wrapper.appendChild(display);
			cell.getElement().addEventListener('remove', () => dropdown.remove());
			onRendered(() => display.focus());

			return wrapper;
		},
		statusHeaderFilterFunc(filterVal, rowVal, rowData, filterParams) {
			if (!filterVal || !filterVal.length) return true;
			// rowVal is the raw dateStyle string on the flat table
			return filterVal.some(val => val === rowVal);
		},
		qgateHeaderFilterEditor(cell, onRendered, success, cancel, editorParams) {

			const options = [
				{ label: '[+] ' + this.$p.t('abgabetool/c4positivBenotet'), value: 'positive' },
				{ label: '[-] ' + this.$p.t('abgabetool/c4negativBenotet'), value: 'negative' },
				{ label: '[~] ' + this.$p.t('abgabetool/c4notYetGraded'), value: 'not_graded' },
				{ label: '[?] ' + this.$p.t('abgabetool/c4notSubmitted'), value: 'not_submitted' },
				{ label: '[o] ' + this.$p.t('abgabetool/c4notHappenedYet'), value: 'not_happened' },
				{ label: '[--] ' + this.$p.t('abgabetool/c4keinTerminVorhanden'), value: 'no_termin' },
			];

			const field = cell.getField();
			const stateKey = field === 'qgate1Status' ? 'qgate1FilterSelected' : 'qgate2FilterSelected';
			let selected = [...(this[stateKey] || [])]; // restore persistence state

			const wrapper = document.createElement('div');
			wrapper.style.cssText = 'position: relative; width: 100%;';

			const display = document.createElement('input');
			display.readOnly = true;
			display.placeholder = '';
			display.style.cssText = 'padding: 4px; width: 100%; box-sizing: border-box; cursor: default; border: 1px solid; outline: none; background: #fff; appearance: none; caret-color: transparent;';

			const dropdown = document.createElement('div');
			dropdown.style.cssText = 'display: none; position: fixed; background: #fff; border: 1px solid; z-index: 9999; min-width: 180px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);';

			options.forEach(opt => {
				const row = document.createElement('label');
				row.style.cssText = 'display: flex; align-items: center; gap: 6px; padding: 4px 8px; cursor: pointer; white-space: nowrap;';
				row.addEventListener('mousedown', e => e.preventDefault());

				const cb = document.createElement('input');
				cb.type = 'checkbox';
				cb.value = opt.value;
				cb.checked = selected.includes(opt.value); // sync with persistence
				cb.addEventListener('change', () => {
					if (cb.checked) {
						selected.push(opt.value);
					} else {
						selected = selected.filter(v => v !== opt.value);
					}
					this[stateKey] = [...selected]; // sync with persistence
					display.value = options.filter(o => selected.includes(o.value)).map(o => o.label).join(', ');
					success([...selected]);
				});

				row.appendChild(cb);
				row.appendChild(document.createTextNode(opt.label));
				dropdown.appendChild(row);
			});

			display.value = options.filter(o => selected.includes(o.value)).map(o => o.label).join(', ');

			display.addEventListener('click', () => {
				if (dropdown.style.display === 'none') {
					const rect = display.getBoundingClientRect();
					dropdown.style.top = rect.bottom + 'px';
					dropdown.style.left = rect.left + 'px';
					dropdown.style.display = 'block';
				} else {
					dropdown.style.display = 'none';
				}
			});

			display.addEventListener('blur', () => {
				setTimeout(() => { dropdown.style.display = 'none'; }, 150);
			});

			document.body.appendChild(dropdown);
			wrapper.appendChild(display);

			cell.getElement().addEventListener('remove', () => dropdown.remove());

			onRendered(() => display.focus());

			return wrapper;
		},
		qgateHeaderFilterFunc(filterVal, rowVal, rowData, filterParams) {
			if (!filterVal || !filterVal.length) return true;

			const matches = (val) => {
				switch (val) {
					case 'positive':     return rowVal === this.$p.t('abgabetool/c4positivBenotet');
					case 'negative':     return rowVal === this.$p.t('abgabetool/c4negativBenotet');
					case 'not_graded':   return rowVal === this.$p.t('abgabetool/c4notYetGraded');
					case 'not_submitted':return rowVal === this.$p.t('abgabetool/c4notSubmitted');
					case 'not_happened': return rowVal === this.$p.t('abgabetool/c4notHappenedYet');
					case 'no_termin':    return rowVal === this.$p.t('abgabetool/c4keinTerminVorhanden');
					default:             return true;
				}
			};

			// OR logic — row passes if it matches any selected filter
			return filterVal.some(val => matches(val));
		},
		redrawTableScrollSave() {
			const table = this.$refs.abgabeTable.tabulator;
			const scrollX = table.rowManager.scrollLeft;
			const scrollY = table.rowManager.scrollTop;
			this.$refs.abgabeTable.tabulator.redraw(true)

			Vue.nextTick(()=> {
				const tableholder = this.$refs.abgabeTable?.tabulator.element.querySelector('.tabulator-tableholder')
				if(tableholder) {
					tableholder.scrollLeft = scrollX;
					tableholder.scrollTop = scrollY;
				}
			})
		},
		shortLongTitleFormatter(cell, formatterParams, onRendered) {
			const longForm = cell.getValue()
			const shortForm = formatterParams?.shortForm
			
			if(longForm && shortForm) {
				return `<span class="full-text" style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin: 0px;">
					${longForm}
				</span>
				<span class="short-text" style="font-weight: bold; display: none;">
					${shortForm}
				</span>`
			} else {
				return `<span class="full-text" style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin: 0px;">
					${longForm}
				</span>`
			}
		
		},
		toolTipFuncPrevTermin(e, cell, onRendered) {
			const data = cell.getData();
			if(!data.prevTermin) return ''
			return this.mapDateStyleToTabulatorTooltip(data.prevTermin.dateStyle);
		},
		toolTipFuncNextTermin(e, cell, onRendered) {
			const data = cell.getData();
			if(!data.nextTermin) return ''
			return this.mapDateStyleToTabulatorTooltip(data.nextTermin.dateStyle);
		},
		mapDateStyleToTabulatorTooltip(dateStyleString) {
			switch(dateStyleString) {
				case 'bestanden':
					return this.$p.t('abgabetool/c4tooltipBestanden')
				break;
				case 'nichtbestanden':
					return this.$p.t('abgabetool/c4tooltipNichtBestanden')
				break;
				case 'beurteilungerforderlich':
					return this.$p.t('abgabetool/c4tooltipBeurteilungerforderlich')
				break;
				case 'verspaetet':
					return this.$p.t('abgabetool/c4tooltipVerspaetet')
				break;
				case 'abgegeben':
					return this.$p.t('abgabetool/c4tooltipAbgegeben')
				break;
				case 'verpasst':
					return this.$p.t('abgabetool/c4tooltipVerpasst')
				break;
				case 'abzugeben':
					return this.$p.t('abgabetool/c4tooltipAbzugeben')
				break;
				case 'standard':
					return this.$p.t('abgabetool/c4tooltipStandardv2')
				break;
				default: return ''
			}
		},
		handlePaUpdated(projektarbeit) {
			this.checkAbgabetermineProjektarbeit(projektarbeit)
			this.redrawTableScrollSave()
		},
		getQGateStatusList() {
			return [
				this.$p.t('abgabetool/c4keinTerminVorhanden'),
				this.$p.t('abgabetool/c4positivBenotet'),
				this.$p.t('abgabetool/c4negativBenotet'),
				this.$p.t('abgabetool/c4notYetGraded'),
				this.$p.t('abgabetool/c4notSubmitted'),
				this.$p.t('abgabetool/c4notHappenedYet')
			]	
		},
		sortFuncTerminCol(a, b, aRow, bRow, column, dir, params) {
			if (a === null || typeof a === "undefined") return 1;
			if (b === null || typeof b === "undefined") return -1;
			
			// try to handle the prev/next interpretation consistently
			// can only make this wrong UX whise so whatever
			if(column._column.field == 'prevTermin') {
				return Math.abs(b.diffMs) - Math.abs(a.diffMs)
			} else if (column._column.field == 'nextTermin') {
				return Math.abs(a.diffMs) - Math.abs(b.diffMs)
			}
			
			// just in case someone reuses this
			return Math.abs(b.diffMs) - Math.abs(a.diffMs) 
		},
		headerFilterTerminColISO(filterVal, rowVal) {
			if (!rowVal) {
				return false;
			}

			const toLuxon = (val) => {
				if (!val) return null;
				let dt;
				if (val instanceof Date) {
					dt = luxon.DateTime.fromJSDate(val);
				} else if (typeof val === "string") {
					dt = toViennaDate(val);
				} else { // fallback
					dt = luxon.DateTime.fromMillis(Number(val));
				}

				return dt.isValid ? dt : null;
			};

			const rowDate = toLuxon(rowVal);
			const von = toLuxon(filterVal[0]);
			const bis = toLuxon(filterVal[1]);

			// specific day
			if (von && !bis) {
				return rowDate.hasSame(von, "day");
			}

			// range case
			if (von && bis) {
				return rowDate >= von.startOf("day") && rowDate <= bis.endOf("day");
			}

			return false
		},
		headerFilterTerminCol(filterVal, rowVal) {
			if (!rowVal || !rowVal.luxonDate || !rowVal.luxonDate.isValid) {
				return false;
			}

			const rowDate = rowVal.luxonDate;

			const toLuxon = (val) => {
				if (!val) return null;
				let dt;
				if (val instanceof Date) {
					dt = luxon.DateTime.fromJSDate(val);
				} else if (typeof val === "string") {
					dt = toViennaDate(val);
				} else { // fallback
					dt = luxon.DateTime.fromMillis(Number(val));
				}

				return dt.isValid ? dt : null;
			};

			const von = toLuxon(filterVal[0]);
			const bis = toLuxon(filterVal[1]);

			// specific day
			if (von && !bis) {
				return rowDate.hasSame(von, "day");
			}

			// range case
			if (von && bis) {
				return rowDate >= von.startOf("day") && rowDate <= bis.endOf("day");
			}

			return false
		},
		sammelMailStudent(param) {

			const recipientList = [];
			this.selectedData.forEach(d => {
				recipientList.push(`${d.student_uid}@${this.domain}`)
			})

			const uniqueRecipients = [...new Set(recipientList)];
			const subject = this.$p.t('abgabetool/c4sammelmailStudentBetreff', [this.selectedStudiengangOption?.bezeichnung]);
			splitMailsHelper(uniqueRecipients, param.originalEvent, subject, null, this.$fhcAlert, this.$p)
		},
		sammelMailBetreuer(param) {
			const recipientList = [];
			this.selectedData.forEach(row => {
				if (row.betreuer_mail) recipientList.push(row.betreuer_mail);
				if (row.zweitbetreuer_mail) recipientList.push(row.zweitbetreuer_mail);
			});

			const uniqueRecipients = [...new Set(recipientList)];
			const subject = this.$p.t('abgabetool/c4sammelmailBetreuerBetreff', [this.selectedStudiengangOption?.bezeichnung]);

			// dedupe by student_uid, then build one line per student
			const seenUids = new Set();
			const bodyLines = [];
			this.selectedData.forEach(row => {
				if (seenUids.has(row.student_uid)) return;
				seenUids.add(row.student_uid);
				const name = `${row.student_vorname ?? ''} ${row.student_nachname ?? ''}`.trim();
				const titel = row.titel ? ` - ${row.titel}` : '';
				bodyLines.push(`${name}${titel}`);
			});

			const body = bodyLines.join('\n');
			splitMailsHelper(uniqueRecipients, param.originalEvent, subject, body, this.$fhcAlert, this.$p)
		},
		selectHandler(e, cell) {
			const row = cell.getRow();

			if(row.isSelected()){
				row.deselect();
			} else {
				row.select();
			}

			// stop built-in handler
			e.stopPropagation();
			return false;
		},
		selectAllHandler(e, cell) {
			const table = cell.getTable();
			const rows = this.filteredRows ?? table.getRows();

			// custom select all logic
			const allowed = rows.filter(r => r.getData().selectable);
			const selected = rows.every(r => r.isSelected());

			if(selected){
				allowed.forEach(r => r.deselect());
				e.target.checked = false;
			} else {
				allowed.forEach(r => r.select());
				e.target.checked = true;
			}

			// stop built-in handler
			e.stopPropagation();
			return false;
		},
		selectAllHandlerFlat(e, cell) {
			const table = cell.getTable();
			const rows = this.filteredRowsFlat ?? table.getRows();

			// custom select all logic
			const allowed = rows.filter(r => r.getData().selectable);
			const selected = rows.every(r => r.isSelected());

			if(selected){
				allowed.forEach(r => r.deselect());
			} else {
				allowed.forEach(r => r.select());
			}

			// stop built-in handler
			e.stopPropagation();
			return false;
		},
		checkQualityGateStatus(projekt) {
			const qgate1Termine = []
			const qgate2Termine = []
			
			projekt.qgate1Status = this.$p.t('abgabetool/c4keinTerminVorhanden')// 'Kein Termin vorhanden'
			projekt.qgate1StatusRank = 0
			projekt.qgate2Status = this.$p.t('abgabetool/c4keinTerminVorhanden')
			projekt.qgate2StatusRank = 0
			
			projekt.abgabetermine.forEach(termin => {
				if(termin.paabgabetyp_kurzbz == 'qualgate1') qgate1Termine.push(termin)
				if(termin.paabgabetyp_kurzbz == 'qualgate2') qgate2Termine.push(termin)
			})
			
			// calculate qgateStatusRank and display the highest order status rank of all quality gate termine until one
			// counts as passed, which is just a positive note no matter if anything has been uploaded
			
			// reuse luxon calculated diffMs (termin.datum in relation to today) from previous datestyle check 
			qgate1Termine.forEach(qgate => {
				if(qgate.note != null && projekt.qgate1StatusRank <= 5) {
					const noteOpt = typeof qgate.note !== 'object' ? this.notenOptions.find(opt => opt.note == qgate.note) : qgate.note
					if(noteOpt.positiv) {
						projekt.qgate1Status = this.$p.t('abgabetool/c4positivBenotet')
						projekt.qgate1StatusRank = 5
					} else {
						projekt.qgate1Status = this.$p.t('abgabetool/c4negativBenotet')
						projekt.qgate1StatusRank = 4
					}
				} else if (qgate.note == null && projekt.qgate1StatusRank <= 3) {
					projekt.qgate1Status = this.$p.t('abgabetool/c4notYetGraded')
					projekt.qgate1StatusRank = 3
				} else if(qgate.upload_allowed == true && qgate.abgabedatum == null && projekt.qgate1StatusRank <= 2) {
					projekt.qgate1Status = this.$p.t('abgabetool/c4notSubmitted')
					projekt.qgate1StatusRank = 2
				} else if (qgate.upload_allowed == false && qgate.diffMs <= 0 && projekt.qgate1StatusRank <= 1) {
					projekt.qgate1Status = this.$p.t('abgabetool/c4notHappenedYet')
					projekt.qgate1StatusRank = 1
				}
			})

			qgate2Termine.forEach(qgate => {
				if(qgate.note != null && projekt.qgate1StatusRank <= 5) {
					const noteOpt = typeof qgate.note !== 'object' ? this.notenOptions.find(opt => opt.note == qgate.note) : qgate.note
					if(noteOpt.positiv) {
						projekt.qgate2Status = this.$p.t('abgabetool/c4positivBenotet')
						projekt.qgate2StatusRank = 5
					} else {
						projekt.qgate2Status = this.$p.t('abgabetool/c4negativBenotet')
						projekt.qgate2StatusRank = 4
					}
				} else if (qgate.note == null && projekt.qgate2StatusRank <= 3) {
					projekt.qgate2Status = this.$p.t('abgabetool/c4notYetGraded')
					projekt.qgate2StatusRank = 3
				} else if(qgate.upload_allowed == true && qgate.abgabedatum == null && projekt.qgate2StatusRank <= 2) {
					projekt.qgate2Status = this.$p.t('abgabetool/c4notSubmitted')
					projekt.qgate2StatusRank = 2
				} else if (qgate.upload_allowed == false && qgate.diffMs <= 0 && projekt.qgate2StatusRank <= 1) {
					projekt.qgate2Status = this.$p.t('abgabetool/c4notHappenedYet')
					projekt.qgate2StatusRank = 1
				}
			})
			
			// set shorthand statuscode once real status has been determined
			projekt.qgate1StatusShort = this.mapRankToShortStatus(projekt.qgate1StatusRank)
			projekt.qgate2StatusShort = this.mapRankToShortStatus(projekt.qgate2StatusRank)
		},
		mapRankToShortStatus(rank) {
				switch(rank){
					case 0: // kein termin vorhanden
						return '--'
						break;
					case 1: // noch nicht stattgefunden
						return 'o'
						break;
					case 2: // noch nicht abgegeben
						return '?'
						break;
					case 3: // noch nicht benotet
						return '~'
						break;
					case 4: // negativ benotet
						return '-'
						break;
					case 5: // positiv benotet
						return '+'
						break;
				}
		},
		getItemBezeichnung(item){
			if(!item.bezeichnung) return ''
			
			return item?.bezeichnung?.bezeichnung ?? item?.bezeichnung
		},
		getItemNote(item) {
			// note can be just a number if it is coming from backend
			// if note was just set it is a note option
			if(!item?.note) return ''
			if(item.note?.bezeichnung) return item.note.bezeichnung
			
			const notenOption = this.notenOptions.find(note => note.note == item.note)
			if(!notenOption) return item.note
			
			return notenOption.bezeichnung
		},
		handleChangeAbgabetypSerientermin(termin) {
			// if paabgabetype qualgate is selected, fill out kurzbz textfield with bezeichnung of quality gate so users
			// are possibly less confused
			if(termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate1' || termin.bezeichnung?.paabgabetyp_kurzbz === 'qualgate2') {
				termin.kurzbz = termin.bezeichnung.bezeichnung
			} else {
				termin.kurzbz = ''
			}
		},
		semesterChanged(e) {
			if(this.$refs.abgabeTable.tabulator) {
				const table = this.$refs.abgabeTable.tabulator

				const existing = table.getFilters().filter(f => f.field != 'studiensemester_kurzbz');

				const compVal = e.value.studiensemester_kurzbz == this.$p.t('abgabetool/c4all') ? '' : e.value.studiensemester_kurzbz
				const compType = e.value.studiensemester_kurzbz == this.$p.t('abgabetool/c4all') ? '!=' : '='
				const newFilter = { field: "studiensemester_kurzbz", type: compType, value: compVal };

				// merge and reapply
				table.setFilter([...existing, newFilter]);
			}
			
		},
		checkAbgabetermineProjektarbeit(projekt) {
			const now = luxon.DateTime.now()
			
			// calculate Abgabetermin time diff to now and assign last and next to projekt
			projekt.abgabetermine.forEach(termin => {

				// only set this if it has not been set yet and abgabetermin has a note (qgate)
				if(!termin.noteBackend && termin.note) {
					termin.noteBackend = termin.note
				}
				
				termin.bezeichnung = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === termin.paabgabetyp_kurzbz)
				
				// while already looping through each termin, calculate datestyle beforehand
				termin.dateStyle = getDateStyleClass(termin, this.notenOptions)

				const date = toViennaDate(termin.datum).endOf('day')
				termin.luxonDate = date
				termin.diffMs = date.toMillis() - now.toMillis(); // positive = future, negative = past

				if (termin.diffMs < 0) {
					if (!projekt.prevTermin ||
						termin.diffMs > projekt.prevTermin.diffMs // larger (less negative) = closer to now
					) {
						projekt.prevTermin = termin;
					}
				} else if (termin.diffMs > 0) {
					if (!projekt.nextTermin ||
						termin.diffMs < projekt.nextTermin.diffMs // smaller positive = closer to now
					) {
						projekt.nextTermin = termin;
					}
				}
			})

			// seperate check for quality gates
			this.checkQualityGateStatus(projekt)
		},
		loadState() {
			return JSON.parse(localStorage.getItem(this.abgabeTableOptions.persistenceID) || "null");
		},
		saveState(table) {
			// avoid storing state after first restore part happened
			if(!this.stateRestored) return
			const rawLayout = table.getColumnLayout();
			const state = {
				columns: rawLayout.map(col => ({
					field: col.field,
					visible: col.visible,
					width: col.width,
				})),
				sort: table.getSorters().map(s => ({
					field: s.field,
					dir: s.dir,
				})),
				filters: table.getFilters(),
				headerFilters: table.getHeaderFilters()
			};
			
			localStorage.setItem(this.abgabeTableOptions.persistenceID, JSON.stringify(state));
		},
		handleTableBuilt() {
			const table = this.$refs.abgabeTable.tabulator

			this.tableBuiltResolve()
			
			table.on("columnMoved", () => {
				this.saveState(table);
			});

			table.on("columnResized", () => {
				this.saveState(table);
			});

			table.on("columnVisibilityChanged", () => {
				this.saveState(table);
			});

			table.on("filterChanged", () => {
				this.saveState(table);
			});

			table.on("headerFilterChanged", () => {
				this.saveState(table);
			});

			table.on("dataSorted", () => {
				this.saveState(table);
			});

			table.on("columnSorted", () => {
				this.saveState(table);
			});

			table.on("sortersChanged", () => {
				this.saveState(table);
			});

			const saved = this.loadState();

			table.on("renderComplete", () => {
				if(!this.stateRestored) {
					
					if (saved?.columns && !this.colLayoutRestored) {
						const layout = saved.columns.map(col => ({
							field: col.field,
							width: col.width,
							visible: col.visible,
							// add more if needed, but keep it simple
						}));
						
						table.setColumnLayout(layout);

						this.colLayoutRestored = true;
					}

					if (saved?.filters && !this.filtersRestored) {
						this.filtersRestored = true // instantly avoid retriggers
						table.setFilter(saved.filters);
					}
					if (saved?.headerFilters && !this.headerFiltersRestored) {
						this.headerFiltersRestored = true // instantly avoid retriggers
						for (let hf of saved.headerFilters) {
							if (hf.field === 'qgate1Status') this.qgate1FilterSelected = hf.value || [];
							if (hf.field === 'qgate2Status') this.qgate2FilterSelected = hf.value || [];
							table.setHeaderFilterValue(hf.field, hf.value);
						}
					}

					if (saved?.sort?.length && !this.sortRestored) {
						this.sortRestored = true;

						setTimeout(() => {
							const sortList = saved.sort.map(s => {
								const col = table.columnManager.findColumn(s.field);
								if (!col) {
									return null;
								}
								return { column: col, dir: s.dir };
							}).filter(Boolean);

							table.setSort(sortList);
						}, 100);
					}
					this.stateRestored = true
					
					// ensure that the filterCollapseables thingy has the correct values
					this.$refs.abgabeTable.setSelectedFields();

				}

			});
		},
		handleTableBuiltFlat() {
			const table = this.$refs.abgabeTableFlat.tabulator

			this.tableBuiltResolveFlat()

			table.on("columnMoved", () => {
				this.saveStateFlat(table);
			});

			table.on("columnResized", () => {
				this.saveStateFlat(table);
			});

			table.on("columnVisibilityChanged", () => {
				this.saveStateFlat(table);
			});

			table.on("filterChanged", () => {
				this.saveStateFlat(table);
			});

			table.on("headerFilterChanged", () => {
				this.saveStateFlat(table);
			});

			table.on("dataSorted", () => {
				this.saveStateFlat(table);
			});

			table.on("columnSorted", () => {
				this.saveStateFlat(table);
			});

			table.on("sortersChanged", () => {
				this.saveStateFlat(table);
			});

			const saved = this.loadStateFlat();

			table.on("renderComplete", () => {
				if(!this.stateRestoredFlat) {
					
					if (saved?.columns && !this.colLayoutRestoredFlat) {
						const layout = saved.columns.map(col => ({
							field: col.field,
							width: col.width,
							visible: col.visible,
							// add more if needed, but keep it simple
						}));

						table.setColumnLayout(layout);

						this.colLayoutRestoredFlat = true;
					}

					if (saved?.filters && !this.filtersRestoredFlat) {
						this.filtersRestoredFlat = true // instantly avoid retriggers
						table.setFilter(saved.filters);
					}
					if (saved?.headerFilters && !this.headerFiltersRestoredFlat) {
						this.headerFiltersRestoredFlat = true // instantly avoid retriggers
						for (let hf of saved.headerFilters) {
							if (hf.field === 'note') this.noteFilterSelected = hf.value || [];
							if (hf.field === 'pa_note') this.pa_noteFilterSelected = hf.value || [];
							table.setHeaderFilterValue(hf.field, hf.value);
						}
					}

					if (saved?.sort?.length && !this.sortRestoredFlat) {
						this.sortRestoredFlat = true;

						setTimeout(() => {
							const sortList = saved.sort.map(s => {
								const col = table.columnManager.findColumn(s.field);
								if (!col) {
									return null;
								}
								return { column: col, dir: s.dir };
							}).filter(Boolean);

							table.setSort(sortList);
						}, 100);
					}
					this.stateRestoredFlat = true

					// ensure that the filterCollapseables thingy has the correct values
					this.$refs.abgabeTableFlat.setSelectedFields();

				}

			});
		},
		loadStateFlat() {
			return JSON.parse(localStorage.getItem(this.abgabeTableOptionsFlat.persistenceID) || "null");
		},
		saveStateFlat(table) {
			// avoid storing state after first restore part happened
			if(!this.stateRestoredFlat) return
			const rawLayout = table.getColumnLayout();
			const state = {
				columns: rawLayout.map(col => ({
					field: col.field,
					visible: col.visible,
					width: col.width,
				})),
				sort: table.getSorters().map(s => ({
					field: s.field,
					dir: s.dir,
				})),
				filters: table.getFilters(),
				headerFilters: table.getHeaderFilters()
			};

			localStorage.setItem(this.abgabeTableOptionsFlat.persistenceID, JSON.stringify(state));
		},
		handleToggleFullscreenDetail() {
			this.detailIsFullscreen = !this.detailIsFullscreen
		},
		getOptionLabelAbgabetyp(option){
			return option.bezeichnung
		},
		getOptionLabelStg(option){
			return option.kurzbzlang + ' ' + option.bezeichnung
		},
		getOptionLabelStudiensemester(option){
			return option.studiensemester_kurzbz
		},
		getNotenFilterOptionLabel(option) {
			return option.bezeichnung	
		},
		formatDate(dateParam) {
			return formatISODate(dateParam);
		},
		formAction(cell) {
			const actionButtons = document.createElement('div');
			actionButtons.className = "d-flex gap-3";
			actionButtons.style.display = "flex";
			actionButtons.style.alignItems = "stretch";
			actionButtons.style.justifyContent = "start";
			actionButtons.style.height = "100%";

			const val = cell.getValue();

			const createButton = (iconClass, titleKey, clickHandler) => {
				const btn = document.createElement('button');
				btn.className = 'btn btn-outline-secondary';
				btn.style.display = "flex";
				btn.style.alignItems = "center"; // center icon vertically
				btn.style.justifyContent = "center"; // center icon horizontally
				btn.style.height = "100%"; // fill parent container height
				btn.style.aspectRatio = "1 / 1"; // keep square shape (optional)
				btn.style.padding = "0"; // remove extra padding for compactness
				if(iconClass == 'fa fa-timeline') btn.style.transform = "rotate(90deg)";
				btn.innerHTML = `<i class="${iconClass}" style="color:#00649C; font-size:1.1rem;"></i>`;
				btn.title = this.$capitalize(this.$p.t(titleKey));
				btn.addEventListener('click', (e) => {
					e.stopPropagation();
					e.stopImmediatePropagation();
					clickHandler();
				});
				return btn;
			};

			actionButtons.append(
				createButton('fa fa-folder-open', 'abgabetool/c4details', () => this.setDetailComponent(val)),
				createButton('fa fa-timeline', 'abgabetool/c4termineTimeLine', () => this.openTimeline(val))
			);

			if(val.latestTerminWithUpload) {
				actionButtons.append(
					createButton('fa fa-download', 'abgabetool/c4downloadLatestAbgabe', () => this.downloadAbgabe(val.latestTerminWithUpload.paabgabe_id, val.student_uid, val.projektarbeit_id))
				)
			}

			return actionButtons;
		},
		downloadAbgabe(paabgabe_id, student_uid, projektarbeit_id) {
			const url = `/api/frontend/v1/Abgabe/getStudentProjektarbeitAbgabeFile?paabgabe_id=${paabgabe_id}&student_uid=${student_uid}&projektarbeit_id=${projektarbeit_id}`;

			window.open(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + url)
			// this.$api.call(ApiAbgabe.getStudentProjektarbeitAbgabeFile(termin.paabgabe_id, this.projektarbeit.student_uid))
		},

		undoSelection(cell) {
			// checks if cells row is selected and unselects -> imitates columns which dont trigger row selection
			// but actually just revert it after the fact

			const row = cell.getRow()
			if(row.isSelected()) {
				row.deselect();
			}
		},
		selectionCheck(row) {
			const data = row.getData()
			
			// currently assistenz is allowed to select everything in projektarbeit table
			
			return true
		},
		showDeadlines(){
			const link = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router
				+ '/Cis/Abgabetool/Deadlines'
			window.open(link, '_blank')
		},
		openAddSeriesModal() {
			this.$refs.modalContainerAddSeries.show()
		},
		addSeries() {
			const pids = this.selectedData?.map(projekt => projekt.projektarbeit_id)
			
			const preserveSelected = [...this.selectedData]
			
			this.saving = true
			this.serienTermin.fixtermin = !this.serienTermin.invertedFixtermin
			this.$api.call(ApiAbgabe.postSerientermin(
				this.serienTermin.datum,
				this.serienTermin.bezeichnung.paabgabetyp_kurzbz,
				this.serienTermin.bezeichnung.bezeichnung,
				this.serienTermin.kurzbz,
				this.serienTermin.upload_allowed,
				pids,
				this.serienTermin.fixtermin
			)).then(res => {
				
				if (res.meta.status === "success" && res.data) {
					this.$fhcAlert.alertSuccess(this.$p.t('abgabetool/serienTerminGespeichert'))
				} else {
					this.$fhcAlert.alertError(this.$p.t('abgabetool/errorSerienterminSpeichern'))
				}
				
				// put new abgaben into projektarbeiten
				const newAbgaben = res.data
				pids.forEach(pid => {
					const abgabe = newAbgaben.find(abgabe => abgabe.projektarbeit_id == pid)
					
					const pa = this.projektarbeiten.find(pa => pa.projektarbeit_id == pid)
				
					abgabe.bezeichnung = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz == abgabe.paabgabetyp_kurzbz)
					
					pa.abgabetermine.push(abgabe)
					pa.abgabetermine.sort((a, b) => compareISODateValues(a.datum, b.datum))
				})
				
				this.projektarbeiten = this.mapProjekteToTableData(this.projektarbeiten)

				this.redrawTableScrollSave()

				// in case pesky user creates a series and instantly switches viewmode
				this.flatDataDirty = true
				if (this.mode === 'flatView') {
					this.$refs.abgabeTableFlat.tabulator.setData(this.getAllTermine)
				}
				
			}).finally(()=>{
				this.saving = false
				this.selectedData = preserveSelected
			})

			this.$refs.modalContainerAddSeries.hide()
		},
		mapProjekteToTableData(projekte) {
			// const now = luxon.DateTime.now();
			return projekte.map(projekt => {
				
				// in assistenz context every projektarbeit should be allowed to be selected i guess
				projekt.selectable = true;
				
				projekt.prevTermin = null;
				projekt.nextTermin = null;

				this.checkAbgabetermineProjektarbeit(projekt)

				if(this.notenOptions && projekt.note) {
					const opt = this.notenOptions.find(n => n.note == projekt.note)
				
					// TODO: mehrsprachig englisch -> nevermind the english field in
					// notenoption->bezeichnung_mehrsprachig is ALWAYS german
					projekt.note_bez = opt.bezeichnung
				}

				const latestTerminWithUpload = this.findLatestTerminWithUpload(projekt)

				return {
					...projekt,
					abgabetermine: projekt.abgabetermine,
					details: {
						student_uid: projekt.student_uid,
						projektarbeit_id: projekt.projektarbeit_id,
						latestTerminWithUpload: latestTerminWithUpload ?? null
					},
					pkz: this.buildPKZ(projekt),
					beurteilung: projekt.beurteilungLink ?? null,
					sem: projekt.studiensemester_kurzbz,
					stg: this.buildStg(projekt),
					mail: this.buildMailToLink(projekt),
					erstbetreuer: this.buildErstbetreuer(projekt),
					zweitbetreuer: this.buildZweitbetreuer(projekt),
					typ: projekt.projekttyp_kurzbz,
					titel: projekt.titel
				}
			})
		},
		findLatestTerminWithUpload(projekt) {
			const withAbgabedatumSorted = projekt?.abgabetermine
				?.filter(t => t.abgabedatum != null)
				?.sort((a, b) => compareISODateValues(b.abgabedatum, a.abgabedatum));
			
			if(withAbgabedatumSorted.length) {
				return withAbgabedatumSorted[0]
			}

			return null
		},
		createInfoString(data) {
			let str = '';

			data.forEach(name => {
				str += name
				str += '; '
			})

			return str
		},
		isPastDate(date) {
			const deadline = luxon.DateTime.fromISO(date, { zone: 'Europe/Vienna' }).endOf('day');
			const nowInVienna = luxon.DateTime.now().setZone('Europe/Vienna');
			return nowInVienna > deadline;
		},
		setDetailComponent(details){
			const pa = this.projektarbeiten.find(projektarbeit => projektarbeit.projektarbeit_id == details.projektarbeit_id)

			if(pa?.abgabetermine?.length) {
				this.$api.call(ApiAbgabe.getSignaturStatusForProjektarbeitAbgaben(pa.abgabetermine.map(termin => termin.paabgabe_id), pa.student_uid))
					.then(res => {
						if(res.meta.status === 'success') {
							res.data.forEach(paabgabe => {
								const termin = pa.abgabetermine.find(abgabe => abgabe.paabgabe_id == paabgabe.paabgabe_id)
								if(termin && paabgabe.signatur !== undefined) termin.signatur = paabgabe.signatur
							})
						}
					})
			}
			
			const paIsBenotet = pa.note !== null
			
			pa.abgabetermine.forEach(termin => {
				if(typeof termin.note !== 'object') {
					termin.note = this.allowedNotenOptions.find(opt => opt.note == termin.note)
				}
				
				termin.file = []

				// assistenz should be able to edit every abgabe
				// update 21-01-2026: actually blocking operations on finished projektarbeiten seems like a decent idea
				const terminHasAbgabe = termin.abgabedatum != null
				const terminHasNote = termin.noteBackend
				termin.allowedToSave = paIsBenotet ? false : true

				// assistenz are not allowed to delete deadlines with existing submissions
				termin.allowedToDelete = paIsBenotet || terminHasNote || terminHasAbgabe ? false : true
				
			})
			
			const vorname = pa.vorname ?? pa.student_vorname
			const nachname = pa.nachname ?? pa.student_nachname
			pa.student = `${vorname} ${nachname}`

			this.selectedProjektarbeit = pa
			this.$refs.modalContainerAbgabeDetail.show()
		},
		openTimeline(val) {
			
			this.$api.call(ApiAbgabe.fetchProjektarbeitenHistory(val.student_uid)).then(res => {
				console.log(res)

				res.data.forEach(projekt => {
					projekt.abgabetermine?.forEach(termin => {
						// only set this if it has not been set yet and abgabetermin has a note (qgate)
						if(!termin.noteBackend && termin.note) {
							termin.noteBackend = this.notenOptions.find(opt => termin.note == opt.note)
						}
						
						termin.dateStyle = getDateStyleClass(termin, this.notenOptions)
						
						const terminTypOpt = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz == termin.paabgabetyp_kurzbz)
						if (terminTypOpt) termin.benotbar = terminTypOpt.benotbar
					})
				})

				// keep the history API stub for future use


				this.timelineProjekte = res.data
				this.$refs.drawer.show()
			})
		},
		// openTimeline(val) {
		//	
		// 	this.$api.call(ApiAbgabe.fetchProjektarbeitenHistory(val.student_uid)).then(res => {
		// 		console.log(res)
		// 	})
		//	
		// 	const projekt = this.projektarbeiten.find(p => p.projektarbeit_id == val.projektarbeit_id)
		// 	if(!projekt) {
		//
		// 		this.$fhcAlert.alertInfo('Keine projektarbeit gefunden')
		//		
		// 		return
		// 	}
		//	
		// 	projekt.abgabetermine.forEach(termin => {
		// 		// show note only on termine with abgabetypen which are benotbar
		// 		const terminTypOpt = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz == termin.paabgabetyp_kurzbz)
		// 		termin.benotbar = terminTypOpt.benotbar 
		// 	})
		//	
		// 	this.timelineProjekt = projekt
		// 	this.$refs.drawer.show()
		// },
		paabgabetypFormatter(cell) {
			const key = cell.getValue()
			return this.$p.t('abgabetool/c4paatyp' + key)
		},
		centeredTextFormatter(cell) {
			const longForm = cell.getValue()
			if(!longForm) return
			const data = cell.getData()
			const entry = Object.entries(data).find(entry => entry[1] == longForm)

			// shortFormKey must have same keyname as longForm but with 'Short' appended 
			const shortForm = data[entry[0]+'Short']

			if(shortForm && longForm) {
				return `<div style="display: flex; justify-content: start; align-items: center; height: 100%; width: 100%;">
				<span class="full-text" style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin: 0px;">
					${longForm}
				</span>
				<span class="short-text" style="font-weight: bold; display: none;">
					${shortForm}
				</span>
				</div>`;
			} else {
				return '<div style="display: flex; justify-content: start; align-items: center; height: 100%">' +
					'<p style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin: 0px;">'+longForm+'</p></div>'
			}
		},
		pkzTextFormatter(cell) {
			const val = cell.getValue()

			return '<div style="display: flex; justify-content: start; align-items: center; height: 100%">' +
				'<a style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">'+val+'</a></div>'
		},
		abgabterminFormatter(cell, formatterParams, onRendered,) {
			const val = cell.getValue()
			const dateStyle = val?.dateStyle ?? val
			
			
			if(val) {
				let icon = ''
				switch(dateStyle) {
					case 'verspaetet':
						icon = '<i class="fa-solid fa-triangle-exclamation"></i>'
						break
					case 'verpasst':
						icon = '<i class="fa-solid fa-calendar-xmark"></i>'
						break
					case 'abzugeben':
						icon = '<i class="fa-solid fa-hourglass-half"></i>'
						break
					case 'standard':
						icon = '<i class="fa-solid fa-clock"></i>'
						break
					case 'abgegeben':
						icon = '<i class="fa-solid fa-paperclip"></i>'
						break
					case 'beurteilungerforderlich':
						icon = '<i class="fa-solid fa-list-check"></i>'
						break
					case 'bestanden':
						icon = '<i class="fa-solid fa-check"></i>'
						break
					case 'nichtbestanden':
						icon = '<i class="fa-solid fa-circle-exclamation"></i>'
						break
				}
				
				const bezeichnung = val.bezeichnung?.bezeichnung ?? val.bezeichnung
				
				if(formatterParams?.iconOnly) {
					return '<div style="display: flex; height: 20px;">' +
						'<div class=' + dateStyle + "-header" + ' style="min-width:48px; height: 100%; padding: 0px; display: flex; align-items: center; justify-content: center;">' +
						icon +
						'</div>' +
						'</div>'
				}
				
				return '<div style="display: flex; height: 20px;">' +
					'<div class=' + dateStyle + "-header" + ' style="min-width:48px; height: 100%; padding: 0px; display: flex; align-items: center; justify-content: center;">' +
						icon +
					'</div>' + 
					'<div style="margin-left: 4px;">' +
						'<p style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">'+bezeichnung+' - '+ this.formatDate(val.datum)+'</p>' +
					'</div>'+
					'</div>'
				
			} else {
				return ''
			}
			
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		tableResolveFlat(resolve) {
			this.tableBuiltResolveFlat = resolve
		},
		buildMailToLink(projekt) {
			return 'mailto:' + projekt.student_uid +'@'+ this.domain
		},
		buildPKZ(projekt) {
			return `${projekt.student_uid} / ${projekt.matrikelnr}`
		},
		buildStg(projekt) {
			return (projekt.typ + projekt.kurzbz)?.toUpperCase()
		},
		buildErstbetreuer(projekt) {
			if(projekt.erstbetreuer_full_name) return projekt.erstbetreuer_full_name
			return projekt.betreuer_vorname + ' ' + projekt.betreuer_nachname
		},
		buildZweitbetreuer(projekt) {
			return projekt.zweitbetreuer_full_name ?? ''
		},
		async setupData(data){
			this.domain = data[1]

			this.projektarbeiten = this.mapProjekteToTableData(data[0])
			this.count = this.projektarbeiten.length
			
			await this.tableBuiltPromise
			
			this.$refs.abgabeTable.tabulator.setData(this.projektarbeiten);

			// apply preselected semester filter now that table + data are ready
			this.semesterChanged({ value: this.curSem })
			
			// keep flat table in sync
			this.flatDataDirty = true
			if (this.mode === 'flatView') {
				await this.tableBuiltPromiseFlat
				this.$refs.abgabeTableFlat.tabulator.setData(this.getAllTermine)
				this.flatDataDirty = false
			}

			// reset flat selection since the underlying data changed entirely
			this.selectedDataFlat = []
			this.selectedcountFlat = 0
		},
		loadProjektarbeiten(all = false, callback) {
			this.loading = true
			this.$api.call(ApiAbgabe.getProjektarbeitenForStudiengang(
				this.selectedStudiengangOption.studiengang_kz,
				this.notenOptionFilter?.benotet ?? 0
			))
				.then(res => {
					if(res?.data) this.setupData(res.data)
				}).finally(() => {
				if(callback) {
					callback()
				}
			}).finally(()=>{
				this.loading = false
			})
		},
		loadAbgaben(details) {
			return new Promise((resolve) => {
				this.$api.call(ApiAbgabe.getStudentProjektabgaben(details))
					.then(res => {
						resolve(res)
					})
			})
		},
		handleUuidDefined(uuid) {
			this.tabulatorUuid = uuid
		},
		handleUuidDefinedFlat(uuid) {
			this.tabulatorUuidFlat = uuid
		},
		calcMaxTableHeight() {
			const tableID = this.tabulatorUuid ? ('-' + this.tabulatorUuid) : ''
			const tableDataSet = document.getElementById('filterTableDataset' + tableID);
			if(!tableDataSet) return
			const rect = tableDataSet.getBoundingClientRect();


			this.abgabeTableOptions.height = Math.round(window.visualViewport.height - rect.top)
			this.$refs.abgabeTable.tabulator.setHeight(this.abgabeTableOptions.height)
	
			// same thing for 2nd tabulator which would exceed in size massively
			this.abgabeTableOptionsFlat.height = Math.round(window.visualViewport.height - rect.top)
	
		},
		async setupMounted() {
			this.tableBuiltPromise = new Promise(this.tableResolve)
			this.tableBuiltPromiseFlat = new Promise(this.tableResolveFlat);
			await this.tableBuiltPromise
			
			await this.allConfigPromise
			
			// called through notenOptionFilter/selectedStudiengangOption watcher on startup
			this.loadProjektarbeiten()

			this.calcMaxTableHeight()
		},
		getOptionDisabled(option) {
			return !option.aktiv
		},
	},
	computed: {
		getDisableDeleteForSelectedFlat() {
			return this.selectedDataFlat.some(s => s.allowedToDelete === false)
		},
		getDisableSaveForSelectedFlat(){
			return this.selectedDataFlat.some(s => s.allowedToSave === false)
		},
		getAllTermine() {
			if (!this.projektarbeiten) return [];
			return this.projektarbeiten.flatMap(pa =>
				pa.abgabetermine.map(termin => {
					const terminHasAbgabe = termin.abgabedatum != null
					const terminHasNote = termin.noteBackend
					
					// IN multiedit changing anything for a termin with an existing note is not allowed anymore, to avoid
					// confusing UX behaviour why some fields could be edited and others not -> just edit in detail view
					const allowedToSave = pa.note !== null || terminHasNote || terminHasAbgabe ? false : true
					const allowedToDelete = pa.note !== null || terminHasNote || terminHasAbgabe ? false : true
					
					return {
						allowedToSave,
						allowedToDelete,
						selectable: allowedToSave || allowedToDelete,
						...termin,
						student_uid: pa.student_uid,
						student_vorname: pa.student_vorname,
						student_nachname: pa.student_nachname,
						titel: pa.titel,
						pa_note: pa.note,
						projektarbeit_id: pa.projektarbeit_id,
						stg: pa.stg,
						pkz: pa.pkz,
						studienstatus: pa.studienstatus,
						orgform: pa.orgform
					}
				}
					
				)
			);
		},
		countsToHTMLFlat() {
			return this.$p.t('global/ausgewaehlt')
				+ ': <strong>' + (this.selectedcountFlat || 0) + '</strong>'
				+ ' | '
				+ this.$p.t('global/gefiltert')
				+ ': '
				+ '<strong>' + (this.filteredcountFlat || 0) + '</strong>'
				+ ' | '
				+ this.$p.t('global/gesamt')
				+ ': <strong>' + (this.countFlat || 0) + '</strong>';
		},
		countsToHTML() {
			return this.$p.t('global/ausgewaehlt')
				+ ': <strong>' + (this.selectedcount || 0) + '</strong>'
				+ ' | '
				+ this.$p.t('global/gefiltert')
				+ ': '
				+ '<strong>' + (this.filteredcount || 0) + '</strong>'
				+ ' | '
				+ this.$p.t('global/gesamt')
				+ ': <strong>' + (this.count || 0) + '</strong>';
		},
		emailItems() {
			const menu = []
			
			if(this.ASSISTENZ_SAMMELMAIL_BUTTON_STUDENT){
				menu.push({
					label: this.$p.t('abgabetool/c4sendEmailStudierendev2', [this.uniqueStudentEmailCount]),
					command: this.sammelMailStudent
				})
			}
			
			if(this.ASSISTENZ_SAMMELMAIL_BUTTON_BETREUER) {
				menu.push({
					label: this.$p.t('abgabetool/c4sendEmailBetreuerv3', [this.uniqueBetreuerEmailCount]),
					command: this.sammelMailBetreuer
				})
			}

			return menu
		},
		uniqueBetreuerEmailCount() {
			const emails = new Set();
			
			this.selectedData.forEach(row => {
				if (row.betreuer_mail) emails.add(row.betreuer_mail);
				if (row.zweitbetreuer_mail) emails.add(row.zweitbetreuer_mail);
			});

			return emails.size;
		},
		uniqueStudentEmailCount() {
			const emails = new Set();

			this.selectedData.forEach(row => {
				if (row.student_uid) {
					emails.add(row.student_uid); // actually dont need domain for this
				}
			});

			return emails.size;
		}
	},
	watch: {
		'serienTermin.bezeichnung'(newVal) {
			if(newVal?.paabgabetyp_kurzbz === 'qualgate1' || newVal?.paabgabetyp_kurzbz === 'qualgate2') {
				this.serienTermin.kurzbz = newVal.bezeichnung
			}

			this.serienTermin.upload_allowed = newVal.upload_allowed_default
		},
		selectedStudiengangOption(newVal, oldVal) {
			if(this.loading == true) return
			
			// implicitely avoids juggling around promises for created api calls,
			// since we need note & stg flags for loadProjektarbeiten
			if(this.notenOptionFilter !== null && this.selectedStudiengangOption !== null) {
				this.loadProjektarbeiten()
			}
		},
		notenOptionFilter(newVal) {
			if(this.loading == true) return
			
			// that single where clause is worth a decent load time so rather not filter tabulator but just 
			// adapt the qry
			if(this.notenOptionFilter !== null && this.selectedStudiengangOption !== null) {
				this.loadProjektarbeiten()
			}
		},
		selectedData(newVal) {
			const table = this.$refs.abgabeTable?.tabulator
			if(!table) return

			const allRows = table.getRows();

			newVal.forEach(selected => {
				const row = allRows.find(r => {
					const data = r.getData()
					if (data.projektarbeit_id == selected.projektarbeit_id) return r
				}) 	
				
				row.select()
				const cb = row.getElement().children[0]?.children[0]?.children[0]
				if(cb) cb.checked = true
			})
		},
		projektarbeiten: {
			handler() {
				this.flatDataDirty = true	
			},
			deep: true
		}
	},
	created() {
		// make sure zoom media query doesnt spill ever to other CIS4 sites
		document.documentElement.classList.add('abgabetool');
		
		this.loading = true
		this.phrasenPromise = this.$p.loadCategory(['abgabetool', 'global'])
		this.phrasenPromise.then(()=> {this.phrasenResolved = true})
		
		//TODO: SWITCH TO NOTEN API ONCE NOTENTOOL IS IN MASTER TO AVOID DUPLICATE API
		const requests = [
			this.$api.call(ApiAbgabe.getConfig()),
			this.$api.call(ApiAbgabe.getStudiengaenge()),
			this.$api.call(ApiStudiensemester.getAllStudiensemesterAndAktOrNext()),
			this.$api.call(ApiAbgabe.getNoten()),
			this.$api.call(ApiAbgabe.getPaAbgabetypen())
		];

		this.allConfigPromise = Promise.allSettled(requests)
			.then((results) => {
				// results is an array of { status: 'fulfilled'|'rejected', value?: any, reason?: any }

				// 1. Config
				if (results[0].status === 'fulfilled') {
					const res = results[0].value;
					this.turnitin_link = res.data?.turnitin_link;
					this.old_abgabe_beurteilung_link = res.data?.old_abgabe_beurteilung_link;
					this.ASSISTENZ_SAMMELMAIL_BUTTON_STUDENT = res.data?.ASSISTENZ_SAMMELMAIL_BUTTON_STUDENT;
					this.ASSISTENZ_SAMMELMAIL_BUTTON_BETREUER = res.data?.ASSISTENZ_SAMMELMAIL_BUTTON_BETREUER;
					this.MULTIEDIT_TABLE = res.data?.MULTIEDIT_TABLE;
				}

				// 2. Studiengänge
				if (results[1].status === 'fulfilled') {
					const res = results[1].value;
					this.studiengaengeOptions = res.data;
					if (this.studiengaengeOptions?.length) {
						this.selectedStudiengangOption = this.stg_kz_prop
							? res.data.find(stgOpt => stgOpt.studiengang_kz == this.stg_kz_prop)
							: res.data[0];
					}
				}

				// 3. Studiensemester
				if (results[2].status === 'fulfilled') {
					const res = results[2].value;
					this.allSem = res.data[0];
					const all = { studiensemester_kurzbz: this.$p.t('abgabetool/c4all') };
					
					this.allSemOption = all
					this.studiensemesterOptions = [all, ...this.allSem];

					const currentSemObj = res.data[1];

					// find the matching entry from studiensemesterOptions so PrimeVue
					// can match by reference for the dropdown preselection
					const foundRef = this.studiensemesterOptions.find(
						s => s.studiensemester_kurzbz === currentSemObj.studiensemester_kurzbz
					)
					this.curSem = foundRef ?? all;
				}

				// 4. Noten
				if (results[3].status === 'fulfilled') {
					const res = results[3].value;
					if (res.meta?.status === 'success') {
						this.notenOptions = res.data[0];
						this.allowedNotenOptions = this.notenOptions.filter(
							opt => res.data[1].includes(opt.note)
						);

						this.notenOptionsNonFinal = this.notenOptions.filter(
							opt => res.data[2].includes(opt.note)
						)
					}

					this.allowedNotenFilterOptions = [
						{ bezeichnung: Vue.computed(() => this.$p.t('abgabetool/keineNoteEingetragen')), benotet: 0 },
						{ bezeichnung: Vue.computed(() => this.$p.t('abgabetool/c4benotet')), benotet: 1 },
						{ bezeichnung: Vue.computed(() => this.$p.t('abgabetool/showAll')), benotet: -1 }
					];
					this.notenOptionFilter = this.allowedNotenFilterOptions[0];
				}

				// 5. Abgabetypen
				if (results[4].status === 'fulfilled') {
					const res = results[4].value;
					this.abgabeTypeOptions = res.data;
				}
			})
			.finally(() => {
				this.loading = false;
			});
	},
	mounted() {
		this.setupMounted()
	},
	beforeUnmount() {
		document.documentElement.classList.remove('abgabetool');
	},
	template: `
	<template v-if="phrasenResolved">
		<FhcOverlay :active="loading || saving"></FhcOverlay>

	<bs-modal 
		ref="modalContainerEditSeries" 
		class="bootstrap-prompt" 
		dialogClass="modal-lg"
		bodyClass="px-4 py-4">
		<template v-slot:title>
			<div>{{ $p.t('abgabetool/c4editTerminserie') }}</div>
			<div class="text-muted" style="font-size: 0.9rem;">
				{{ $p.t('abgabetool/c4nSelected', [selectedDataFlat.length]) }}
			</div>
		</template>
		<template v-slot:default>
	
			<div class="row mt-2 align-items-center">
				<div class="col-1">
					<Checkbox v-model="serienEditFields.fixtermin" :binary="true"/>
				</div>
				<div class="col-3 fw-bold">{{$capitalize($p.t('abgabetool/c4fixterminv4'))}}</div>
				<div class="col-8">
					<Checkbox
						v-model="serienEdit.invertedFixtermin"
						:binary="true"
						:disabled="!serienEditFields.fixtermin"
					/>
				</div>
			</div>
	
			<div class="row mt-2 align-items-center">
				<div class="col-1">
					<Checkbox v-model="serienEditFields.datum" :binary="true"/>
				</div>
				<div class="col-3 fw-bold">{{$capitalize($p.t('abgabetool/c4zieldatumv2'))}}</div>
				<div class="col-8">
					<VueDatePicker
						style="width: 95%;"
						v-model="serienEdit.datum"
						:clearable="false"
						:disabled="!serienEditFields.datum"
						locale="de"
						format="dd.MM.yyyy"
						model-type="yyyy-MM-dd"
						:enable-time-picker="false"
						:text-input="true"
						auto-apply>
					</VueDatePicker>
				</div>
			</div>
	
			<div class="row mt-2 align-items-center">
				<div class="col-1">
					<Checkbox v-model="serienEditFields.bezeichnung" :binary="true"/>
				</div>
				<div class="col-3 fw-bold">{{$capitalize($p.t('abgabetool/c4abgabetyp'))}}</div>
				<div class="col-8">
					<Dropdown
						:style="{'width': '100%'}"
						v-model="serienEdit.bezeichnung"
						:disabled="!serienEditFields.bezeichnung"
						:options="abgabeTypeOptions"
						:optionLabel="getOptionLabelAbgabetyp"
						:optionDisabled="getOptionDisabled">
					</Dropdown>
				</div>
			</div>
	
			<div class="row mt-2 align-items-center">
				<div class="col-1">
					<Checkbox v-model="serienEditFields.upload_allowed" :binary="true"/>
				</div>
				<div class="col-3 fw-bold">{{$capitalize($p.t('abgabetool/c4upload_allowed'))}}</div>
				<div class="col-8">
					<Checkbox
						v-model="serienEdit.upload_allowed"
						:binary="true"
						:disabled="!serienEditFields.upload_allowed"
					/>
				</div>
			</div>
	
			<div class="row mt-2 align-items-center">
				<div class="col-1">
					<Checkbox v-model="serienEditFields.kurzbz" :binary="true"/>
				</div>
				<div class="col-3 fw-bold">{{$capitalize($p.t('abgabetool/c4abgabekurzbzv2'))}}</div>
				<div class="col-8">
					<Textarea
						style="margin-bottom: 4px;"
						v-model="serienEdit.kurzbz"
						:disabled="!serienEditFields.kurzbz"
						rows="1"
						class="w-100">
					</Textarea>
				</div>
			</div>
	
		</template>
		<template v-slot:footer>
			<button type="button" class="btn btn-primary" @click="handleEditSelectedTermine">
				{{ $capitalize($p.t('abgabetool/c4save')) }}
			</button>
		</template>
	</bs-modal>

		<bs-modal 
			ref="modalContainerAddSeries" 
			class="bootstrap-prompt"
			dialogClass="modal-lg"
			bodyClass="px-4 py-4">
			<template v-slot:title>
				<div>
					{{ $p.t('abgabetool/neueTerminserie') }}
				</div>
			</template>
			<template v-slot:default>
			
				<div class="row mt-2">
					<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4fixterminv4') )}}</div>
					<div class="col-12 col-md-9">
						<Checkbox
							v-model="serienTermin.invertedFixtermin"
							:binary="true"
							:pt="{ root: { class: 'ml-auto' }}"
						>
						</Checkbox>
					</div>
				</div>
			
				<div class="row mt-2">
					<div class="col-12 col-md-3 align-content-center">
						<div class="row fw-bold" style="margin-left: 2px">{{$capitalize( $p.t('abgabetool/c4zieldatumv2') )}}</div>
					</div>
					<div class="col-12 col-md-9">
						<VueDatePicker
							style="width: 95%;"
							v-model="serienTermin.datum"
							:clearable="false"
							locale="de"
							format="dd.MM.yyyy"
							model-type="yyyy-MM-dd"
							:enable-time-picker="false"
							:text-input="true"
							auto-apply>
						</VueDatePicker>
					</div>
				</div>
			
				<div class="row mt-2">
					<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4upload_allowed') )}}</div>
					<div class="col-12 col-md-9">
						<Checkbox
							v-model="serienTermin.upload_allowed"
							:binary="true"
							:pt="{ root: { class: 'ml-auto' }}"
						>
						</Checkbox>
					</div>
				</div>
				
				<div class="row mt-2">
					<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4abgabetyp') )}}</div>
					<div class="col-12 col-md-9">
						<Dropdown 
							:style="{'width': '100%'}"
							v-model="serienTermin.bezeichnung"
							:options="abgabeTypeOptions"
							:optionLabel="getOptionLabelAbgabetyp"
							:optionDisabled="getOptionDisabled">
						</Dropdown>
					</div>
				</div>
				
				<div class="row mt-2">
					<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4abgabekurzbzv2') )}}</div>
					<div class="col-12 col-md-9">
						<Textarea style="margin-bottom: 4px;" v-model="serienTermin.kurzbz" rows="1" class="w-100"></Textarea>
					</div>
				</div>
				
			</template>
			<template v-slot:footer>
				<button type="button" class="btn btn-primary" @click="addSeries">{{ $p.t('global/speichern') }}</button>
			</template>
		</bs-modal>	
		
		<bs-modal ref="modalContainerAbgabeDetail" class="bootstrap-prompt"
			dialogClass="modal-xl" :allowFullscreenExpand="true"
			@toggle-fullscreen="handleToggleFullscreenDetail"
			bodyClass="px-4 py-4">
			<template v-slot:title>
				<div>
					{{$p.t('abgabetool/c4abgabeMitarbeiterDetailTitle')}}
				</div>
			</template>
			<template v-slot:default>
				<AbgabeDetail 
					:projektarbeit="selectedProjektarbeit" 
					:isFullscreen="detailIsFullscreen" 
					:assistenzMode="true"
					@paUpdated="handlePaUpdated">
				
				</AbgabeDetail>
				
			</template>
		</bs-modal>	
		
		<BsOffcanvas
			ref="drawer"
			placement="end"
			:backdrop="true"
			:style="{ '--bs-offcanvas-width': '600px' }"
		>
			<template #title>
				{{ $p.t('abgabetool/c4projektarbeitTimelineTitle') }}
			</template>

			<AbgabeStudentTimeline
				:projekte="timelineProjekte"
				:notenOptions="notenOptions"
				:formatDateFn="formatDate"
			/>
		</BsOffcanvas>
		
		<div id="abgabetable" style="max-height:40vw;">
			<div class="row">
				<div class="col-auto me-auto">
					<h2 tabindex="1">{{$p.t('abgabetool/abgabetoolTitleAdmin')}}</h2>
				</div>
				<div class="col-auto d-none d-xxl-flex">
					<label class="col-form-label">{{$capitalize($p.t('lehre/studiengang'))}}:</label>
				</div>
				<div class="col-3">
					<Dropdown
						:placeholder="$capitalize($p.t('lehre/studiengang'))" 
						:style="{'width': '100%', 'scroll-behavior': 'auto !important'}" 
						:optionLabel="getOptionLabelStg" 
						v-model="selectedStudiengangOption" 
						:options="studiengaengeOptions"
						:tabindex="2"
					>
						<template #optionsgroup="slotProps">
							<div> {{ option.kurzbzlang }} {{ option.bezeichnung }} </div>
						</template>
					</Dropdown>
				</div>
				<div class="col-auto d-none d-xxl-flex">
					<label class="col-form-label">{{$capitalize($p.t('lehre/note'))}}:</label>
				</div>
				<div class="col-2">
					<Dropdown
						:placeholder="$p.t('lehre/note')" 
						:style="{'width': '100%', 'scroll-behavior': 'auto !important'}" 
						:optionLabel="getNotenFilterOptionLabel" 
						v-model="notenOptionFilter" 
						:options="allowedNotenFilterOptions" 
						:tabindex="3"
					>
						<template #optionsgroup="slotProps">
							<div>{{ option.bezeichnung }} </div>
						</template>
					</Dropdown>
				</div>
			</div>
			<hr>
			<div :style="mode === 'perProjectView' ? '' : 'visibility: hidden; height: 0; overflow: hidden;'">
			<core-filter-cmpt
				:title="''"
				@uuidDefined="handleUuidDefined"
				ref="abgabeTable"
				:description="countsToHTML"
				:newBtnShow="true"
				:newBtnLabel="$p.t('abgabetool/neueTerminserie')"
				:newBtnDisabled="!selectedData.length"
				@click:new=openAddSeriesModal
				:tabulator-options="abgabeTableOptions"  
				:tabulator-events="abgabeTableEventHandlers"
				@tableBuilt="handleTableBuilt"
				@headerFilterOn="handleFilterActiveChanged"
				tableOnly
				:sideMenu="false"
				:useSelectionSpan="false"
			>
				<template #actions>
					<Dropdown
						v-if="curSem"
						@change="semesterChanged" 
						:placeholder="$capitalize($p.t('lehre/studiensemester'))" 
						:style="{'scroll-behavior': 'auto !important'}" 
						:optionLabel="getOptionLabelStudiensemester" 
						v-model="curSem" 
						:options="studiensemesterOptions" 
					>
						<template #optionsgroup="slotProps">
							<div>{{ option.studiensemester_kurzbz }}</div>
						</template>
					</Dropdown>
					
					<button 
						v-if="emailItems.length"
						role="button"
						@click="evt => $refs.menu.toggle(evt)"
						class="btn btn-outline-secondary dropdown-toggle"
						aria-haspopup="true"
					>
						<i class="fa fa-envelope"></i>
					</button>
					<tiered-menu ref="menu" :model="emailItems" popup :autoZIndex="false" />
					
					<button v-if="MULTIEDIT_TABLE" @click="switchMode" class="btn btn-secondary">
						{{ $p.t('abgabetool/c4terminansicht') }}
					</button>
					
					<button @click="reloadData" class="btn btn-secondary">
						<i class="fa-solid fa-arrows-rotate"></i>
					</button>
				</template>
			</core-filter-cmpt>
			</div>
			
			<div :style="mode === 'flatView' ? '' : 'visibility: hidden; height: 0; overflow: hidden;'">
			<core-filter-cmpt
				:title="''"
				@uuidDefined="handleUuidDefinedFlat"
				ref="abgabeTableFlat"
				:description="countsToHTMLFlat"
				:tabulator-options="abgabeTableOptionsFlat"  
				:tabulator-events="abgabeTableEventHandlersFlat"
				@tableBuilt="handleTableBuiltFlat"
				tableOnly
				:sideMenu="false"
				:useSelectionSpan="false"
			>
				<template #actions>
					<button style="max-height: 40px;" :disabled="!selectedcountFlat || getDisableDeleteForSelectedFlat" class="btn btn-danger border-0" @click="handleDeleteSelectedTermine">
						{{$capitalize( $p.t('abgabetool/c4delete') )}}
						<i class="fa-solid fa-trash"></i>
					</button>
					
					<button @click="openEditModal" :disabled="!selectedcountFlat || getDisableSaveForSelectedFlat" class="btn btn-success ml-2" role="button">
						{{$capitalize( $p.t('abgabetool/c4edit') )}}
						<i class="fa fa-pen"></i>
					</button>
										
					<button @click="switchMode" class="btn btn-secondary">
						{{ $p.t('abgabetool/c4projektansicht') }}
					</button>
					
					<button @click="reloadData" class="btn btn-secondary">
						<i class="fa-solid fa-arrows-rotate"></i>
					</button>
				</template>
			</core-filter-cmpt>
			</div>
		</div>
	</template>
    `,
};

export default AbgabetoolAssistenz;
