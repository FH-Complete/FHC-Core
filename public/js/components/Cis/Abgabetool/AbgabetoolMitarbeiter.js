import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeMitarbeiterDetail.js";
import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiAbgabe from '../../../api/factory/abgabe.js'
import FhcOverlay from "../../Overlay/FhcOverlay.js";
import { getDateStyleClass } from "./getDateStyleClass.js";
import { dateFilter } from '../../../tabulator/filters/Dates.js';
import {splitMailsHelper} from "../../../helpers/EmailHelpers.js";

export const AbgabetoolMitarbeiter = {
	name: "AbgabetoolMitarbeiter",
	components: {
		BsModal,
		CoreFilterCmpt,
		AbgabeDetail,
		Checkbox: primevue.checkbox,
		Dropdown: primevue.dropdown,
		Textarea: primevue.textarea,
		TieredMenu: primevue.tieredmenu,
		VueDatePicker,
		FhcOverlay
	},
	provide() {
		return {
			abgabeTypeOptions: Vue.computed(() => this.abgabeTypeOptions),
			abgabetypenBetreuer: Vue.computed(() => this.abgabetypenBetreuer),
			allowedNotenOptions: Vue.computed(() => this.allowedNotenOptions),
			notenOptionsNonFinal: Vue.computed(() => this.notenOptionsNonFinal),
			turnitin_link: Vue.computed(() => this.turnitin_link),
			old_abgabe_beurteilung_link: Vue.computed(() => this.old_abgabe_beurteilung_link)
		}
	},
	props: {
		viewData: {
			type: Object,
			required: true,
			default: () => ({name: '', uid: ''}),
			validator(value) {
				return value && value.uid // && value.name -> extensive viewData use only for cis4 onwards
			}
		}
	},
	data() {
		return {
			abgabetypenBetreuer: null,
			detailIsFullscreen: false,
			phrasenPromise: null,
			phrasenResolved: false,
			turnitin_link: null,
			old_abgabe_beurteilung_link: null,
			BETREUER_SAMMELMAIL_BUTTON_STUDENT: null,
			saving: false,
			loading: false,
			abgabeTypeOptions: null,
			notenOptions: null,
			allowedNotenOptions: null,
			notenOptionsNonFinal: null,
			serienTermin: Vue.reactive({
				datum: new Date().toISOString().split('T')[0],
				bezeichnung: {
					paabgabetyp_kurzbz: 'zwischen',
					bezeichnung: 'Zwischenabgabe'
				},
				kurzbz: '',
				upload_allowed: false
			}),
			showAll: false,
			tabulatorUuid: Vue.ref(0),
			selectedData: [],
			domain: '',
			student_uid: null,
			detail: null,
			detailOffset: 0,
			projektarbeiten: null,
			selectedProjektarbeit: null,
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			abgabeTableOptions: {
				minHeight: 250,
				index: 'projektarbeit_id',
				layout: 'fitDataStretch',
				placeholder: Vue.computed(() => this.$p.t('global/noDataAvailable')),
				selectable: true,
				selectableCheck: this.selectionCheck,
				rowHeight: 40,
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
						cssClass: 'sticky-col'
					},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4details'))), field: 'details', formatter: this.detailFormatter, headerFilter: false, headerSort: false, widthGrow: 1, tooltip: false, cssClass: 'sticky-col'},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4personenkennzeichen'))), headerFilter: true, field: 'pkz', formatter: this.pkzTextFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4vorname'))), field: 'vorname', headerFilter: true, formatter: this.centeredTextFormatter,widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nachname'))), field: 'nachname', headerFilter: true, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4projekttyp'))), field: 'projekttyp_kurzbz', formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4stg'))), field: 'stg', headerFilter: true, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4sem'))), field: 'studiensemester_kurzbz', headerFilter: true, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4titel'))), field: 'titel', headerFilter: true, formatter: this.centeredTextFormatter, maxWidth: 500, widthGrow: 8},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4betreuerartv2'))), field: 'betreuerart_beschreibung',formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4prevAbgabetermin'))), field: 'prevTermin',
						headerFilter: dateFilter,
						headerFilterFunc: this.headerFilterTerminCol,
						sorter: this.sortFuncTerminCol,
						tooltip: this.toolTipFuncPrevTermin,
						formatter: this.abgabterminFormatter, widthGrow: 1, width: 250},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nextAbgabetermin'))), field: 'nextTermin',
						headerFilter: dateFilter,
						headerFilterFunc: this.headerFilterTerminCol,
						sorter: this.sortFuncTerminCol,
						tooltip: this.toolTipFuncNextTermin,
						formatter: this.abgabterminFormatter, widthGrow: 1, width: 250},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4qgate1Status'))),
						headerFilter: 'list',
						headerFilterParams: { valuesLookup: this.getQGateStatusList },
						titleFormatter: this.shortLongTitleFormatter,
						titleFormatterParams: {
							shortForm: 'QG1'
						},
						field: 'qgate1Status', formatter: this.centeredTextFormatter, widthGrow: 1, width: 220,
						tooltip: (e, cell) => {
							const data = cell.getData();
							return data.qgate1Status
						}
					},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4qgate2Status'))),
						headerFilter: 'list',
						headerFilterParams: { valuesLookup: this.getQGateStatusList },
						titleFormatter: this.shortLongTitleFormatter,
						titleFormatterParams: {
							shortForm: 'QG2'
						},
						field: 'qgate2Status', formatter: this.centeredTextFormatter, widthGrow: 1, width: 220,
						tooltip: (e, cell) => {
							const data = cell.getData();
							return data.qgate2Status
						}
					}
				],
				persistence: false,
				persistenceID: 'abgabeTableBetreuer2026-02-26'
			},
			abgabeTableEventHandlers: [{
				event: "tableBuilt",
				handler: async () => {
					this.tableBuiltResolve()
				}
			},
			{
				event: "cellClick",
				handler: async (e, cell) => {
					if(cell.getColumn().getField() === "details") {
						this.setDetailComponent(cell.getValue())
						this.undoSelection(cell)
					} else if (cell.getColumn().getField() === "mail") {
						this.undoSelection(cell)
					}
				}
			},
			{
				event: "rowSelectionChanged",
				handler: async(data) => {
					this.selectedData.filter(sd => !data.includes(sd)).forEach(fsd => {
						if(fsd.checkbox) fsd.checkbox.checked = false
					})
					
					data.forEach(d => {
						if(d.checkbox) d.checkbox.checked = true
					})
					
					this.selectedData = data
				}
			}
			]};
	},
	methods: {
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
			return this.mapDateStyleToTabulatorTooltip(data.prevTermin.dateStyle);
		},
		toolTipFuncNextTermin(e, cell, onRendered) {
			const data = cell.getData();
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
			this.$refs.abgabeTable.tabulator.redraw(true)
		},
		sammelMailStudent(param) {
			
			const recipientList = [];
			this.selectedData.forEach(d => {
				recipientList.push(`${d.student_uid}@${this.domain}`)
			})
			const uniqueRecipients = [...new Set(recipientList)];
			const subject = ""; // empty subject line 
			splitMailsHelper(uniqueRecipients, param.originalEvent, subject, this.$fhcAlert, this.$p)
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
					dt = luxon.DateTime.fromISO(val);
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

				}

			});
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
		checkAbgabetermineProjektarbeit(projekt) {
			const now = luxon.DateTime.now()
			// calculate Abgabetermin time diff to now and assign last and next to projekt
			projekt.abgabetermine.forEach(termin => {
				
				// while already looping through each termin, calculate datestyle beforehand
				termin.dateStyle = getDateStyleClass(termin, this.notenOptions)

				const date = luxon.DateTime.fromISO(termin.datum).endOf('day')
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
		abgabterminFormatter(cell) {
			const val = cell.getValue()

			if(val) {
				let icon = ''
				switch(val.dateStyle) {
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

				return '<div style="display: flex; height: 100%">' +
					'<div class=' + val.dateStyle + "-header" + ' style="min-width:48px; height: 100%; padding: 0px; display: flex; align-items: center; justify-content: center;">' +
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
		selectHandler(e, cell) {
			const row = cell.getRow();

			if(row.isSelected()) {
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
			const rows = table.getRows();

			// custom select all logic
			const allowed = rows.filter(r => r.getData().selectable);
			const selected = allowed.every(r => r.isSelected());

			if(selected) {
				allowed.forEach(r => r.deselect());
			} else {
				allowed.forEach(r => r.select());
			}

			// stop built-in handler
			e.stopPropagation();
			return false;
		},
		handleToggleFullscreenDetail() {
			this.detailIsFullscreen = !this.detailIsFullscreen
		},
		getOptionLabelAbgabetyp(option){
			return option.bezeichnung
		},
		formatDate(dateParam) {
			const date = new Date(dateParam)
			// handle missing leading 0
			const padZero = (num) => String(num).padStart(2, '0');

			const month = padZero(date.getMonth() + 1); // Months are zero-based
			const day = padZero(date.getDate());
			const year = date.getFullYear();

			return `${day}.${month}.${year}`;
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
			if(data?.betreuerart_kurzbz == 'Zweitbegutachter') return false
			return true
		},
		showDeadlines(){
			const link = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router
				+ '/Cis/Abgabetool/Deadlines'
			window.open(link, '_blank')
		},
		toggleShowAll(showall) {
			this.showAll = showall
			this.loading = true
			this.loadProjektarbeiten(showall, () => {
				this.$refs.abgabeTable?.tabulator.redraw(true)
				this.$refs.abgabeTable?.tabulator.setSort([]);
				this.loading = false
			})
		},
		openAddSeriesModal() {
			this.$refs.modalContainerAddSeries.show()
		},
		addSeries() {
			this.saving = true
			this.$api.call(ApiAbgabe.postSerientermin(
				this.serienTermin.datum,
				this.serienTermin.bezeichnung.paabgabetyp_kurzbz,
				this.serienTermin.bezeichnung.bezeichnung,
				this.serienTermin.kurzbz,
				this.serienTermin.upload_allowed,
				this.selectedData?.map(projekt => projekt.projektarbeit_id),
				false
			)).then(res => {
				if (res.meta.status === "success" && res.data) {
					this.$fhcAlert.alertSuccess(this.$p.t('abgabetool/serienTerminGespeichert'))

					const oldScrollLeft = this.$refs.abgabeTable?.tabulator.rowManager.scrollLeft
					const oldScrollTop = this.$refs.abgabeTable?.tabulator.rowManager.scrollTop
					this.loading = true
					this.loadProjektarbeiten(this.showAll, () => {
						this.$refs.abgabeTable?.tabulator.redraw(true)
						this.$refs.abgabeTable?.tabulator.setSort([]);
						this.loading = false

						Vue.nextTick(()=> {
							const table = this.$refs.abgabeTable?.tabulator.element.querySelector('.tabulator-tableholder')
							if(table) {
								table.scrollLeft = oldScrollLeft;
								table.scrollTop = oldScrollTop;
							}
						})
						
					})
				} else {
					this.$fhcAlert.alertError(this.$p.t('abgabetool/errorSerienterminSpeichern'))
				}
			}).finally(()=>{
				this.saving = false
			})

			this.$refs.modalContainerAddSeries.hide()
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
			this.loading=true

			const projektarbeiten = this.projektarbeiten?.retval ?? this.projektarbeiten

			const pa = projektarbeiten.find(projekarbeit => projekarbeit.projektarbeit_id == details.projektarbeit_id)
			
			let paIsBenotet = false
			if(pa.note !== undefined && pa.note !== null) {
				// check if the note is not defined as a non final projektarbeit note
				const opt = this.notenOptionsNonFinal.find(opt => opt.note)
				// if thats the case allow further work
				if(opt) paIsBenotet = false
				// else the PA is to be considered finished
				paIsBenotet = true
			}

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

			pa.abgabetermine.forEach(termin => {
				const noteOpt = this.allowedNotenOptions.find(opt => opt.note == termin.note)
				if(noteOpt) termin.note =  noteOpt
				termin.file = []
				
				// only set this if it has not been set yet and abgabetermin has a note (qgate)
				if(!termin.noteBackend && noteOpt) {
					termin.noteBackend = noteOpt
				}
				
				// update 08-01-2026: everybody is allowed to do everything in client, critical checks happen at backend level
				// termin.allowedToSave = true
				
				// update 21-01-2026: actually blocking operations on finished projektarbeiten seems like a decent idea
				termin.allowedToSave = paIsBenotet ? false : true
				
				// lektoren are not allowed to delete deadlines with existing submissions
				termin.allowedToDelete = termin.allowedToSave && !termin.abgabedatum
				
				termin.bezeichnung = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === termin.paabgabetyp_kurzbz)

			})
			
			pa.student_uid = details.student_uid
			pa.student = `${pa.vorname} ${pa.nachname}`
			
			this.selectedProjektarbeit = pa
			this.$refs.modalContainerAbgabeDetail.show()
		
		
			this.loading = false
			
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
		detailFormatter(cell) {
			return '<div style="display: flex; justify-content: start; align-items: center; height: 100%">' +
				'<a><i class="fa fa-folder-open" style="color:#00649C"></i></a></div>'
		},
		pkzTextFormatter(cell) {
			const val = cell.getValue()

			return '<div style="display: flex; justify-content: start; align-items: center; height: 100%">' +
				'<a style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">'+val+'</a></div>'
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		buildMailToLink(abgabe) {
			return 'mailto:' + abgabe.uid +'@'+ this.domain
		},
		buildPKZ(projekt) {
			return `${projekt.uid} / ${projekt.matrikelnr}`
		},
		buildStg(projekt) {
			return (projekt.typ + projekt.kurzbz)?.toUpperCase()	
		},
		setupData(data){
			
			
			this.domain = data[1]
			
			this.projektarbeiten = data[0]?.retval?.map(projekt => {
				this.checkAbgabetermineProjektarbeit(projekt)
				projekt.selectable = projekt.betreuerart_kurzbz !== 'Zweitbegutachter'

				return {
					...projekt,
					details: {
						student_uid: projekt.uid,
						projektarbeit_id: projekt.projektarbeit_id,
					},
					pkz: this.buildPKZ(projekt),
					beurteilung: projekt.beurteilungLink ?? null,
					sem: projekt.studiensemester_kurzbz,
					stg: this.buildStg(projekt),
					mail: this.buildMailToLink(projekt),
					typ: projekt.projekttyp_kurzbz,
					titel: projekt.titel
				}
			})
			
			this.$refs.abgabeTable.tabulator.setColumns(this.abgabeTableOptions.columns)
			this.$refs.abgabeTable.tabulator.setData(this.projektarbeiten);
		},
		loadProjektarbeiten(all = false, callback) {
			this.$api.call(ApiAbgabe.getMitarbeiterProjektarbeiten(all))
				.then(res => {
					if(res?.data) this.setupData(res.data)
				}).finally(() => {
					if(callback) {
						callback()
					}
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
		calcMaxTableHeight() {
			const tableID = this.tabulatorUuid ? ('-' + this.tabulatorUuid) : ''
			const tableDataSet = document.getElementById('filterTableDataset' + tableID);
			if(!tableDataSet) return
			const rect = tableDataSet.getBoundingClientRect();

			this.abgabeTableOptions.height = window.visualViewport.height - rect.top - 80
			this.$refs.abgabeTable.tabulator.setHeight(this.abgabeTableOptions.height)
		},
		async setupMounted() {
			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise

			this.loadProjektarbeiten()

			this.calcMaxTableHeight()

		}
	},
	watch: {
		'serienTermin.bezeichnung'(newVal) {
			if(newVal?.paabgabetyp_kurzbz === 'qualgate1' || newVal?.paabgabetyp_kurzbz === 'qualgate2') {
				this.serienTermin.kurzbz = newVal.bezeichnung
			}

			this.serienTermin.upload_allowed = newVal.upload_allowed_default
		},
	},
	computed: {
		emailItems() {
			const menu = []

			if(this.BETREUER_SAMMELMAIL_BUTTON_STUDENT){
				menu.push({
					label: this.$p.t('abgabetool/c4sendEmailStudierendev2', [this.uniqueStudentEmailCount]),
					command: this.sammelMailStudent
				})
			}

			return menu
		},
		uniqueStudentEmailCount() {
			const emails = new Set();

			this.selectedData.forEach(row => {
				if (row.student_uid) {
					emails.add(row.student_uid); // actually dont need domain for this
				}
			});

			return emails.size;
		},
		getAllowedAbgabeTypeOptions() {
			return this.abgabeTypeOptions.filter(opt => this.abgabetypenBetreuer.includes(opt.paabgabetyp_kurzbz))
		}
	},
	created() {
		this.phrasenPromise = this.$p.loadCategory(['abgabetool', 'global'])
		this.phrasenPromise.then(()=> {this.phrasenResolved = true})
		// fetch config to avoid hard coded links
		this.$api.call(ApiAbgabe.getConfig()).then(res => {
			this.turnitin_link = res.data?.turnitin_link
			this.old_abgabe_beurteilung_link = res.data?.old_abgabe_beurteilung_link
			this.abgabetypenBetreuer = res.data?.abgabetypenBetreuer
			this.BETREUER_SAMMELMAIL_BUTTON_STUDENT = res.data?.BETREUER_SAMMELMAIL_BUTTON_STUDENT
		}).catch(e => {
			this.loading = false
		})
		
		// fetch noten options
		//TODO: SWITCH TO NOTEN API ONCE NOTENTOOL IS IN MASTER TO AVOID DUPLICATE API
		this.$api.call(ApiAbgabe.getNoten()).then(res => {
			if(res.meta.status == 'success') {
				this.notenOptions = res.data[0]

				this.allowedNotenOptions = this.notenOptions.filter(
					opt => res.data[1].includes(opt.note)
				)
				
				this.notenOptionsNonFinal = this.notenOptions.filter(
					opt => res.data[2].includes(opt.note)
				)
			}
			
		}).catch(e => {
			this.loading = false
		})
		
		// fetch abgabetypen options
		this.$api.call(ApiAbgabe.getPaAbgabetypen()).then(res => {
			this.abgabeTypeOptions = res.data
		}).catch(e => {
			this.loading = false
		})
	},
	mounted() {
		this.setupMounted()
	},
	template: `
	<template v-if="phrasenResolved">
		<FhcOverlay :active="loading || saving"></FhcOverlay>

		<bs-modal ref="modalContainerAddSeries" class="bootstrap-prompt"
			dialogClass="modal-lg">
			<template v-slot:title>
				<div>
					{{ $p.t('abgabetool/neueTerminserie') }}
				</div>
			</template>
			<template v-slot:default>
			
				<div class="row mt-2">
					<div class="col-12 col-md-3 align-content-center">
						<div class="row fw-bold" style="margin-left: 2px">{{$capitalize( $p.t('abgabetool/c4zieldatumv2') )}}</div>
					</div>
					<div class="col-12 col-md-9">
						<VueDatePicker
							style="width: 95%;"
							v-model="serienTermin.datum"
							:clearable="false"
							:enable-time-picker="false"
							locale="de"
							format="dd.MM.yyyy"
							model-type="yyyy-MM-dd"
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
					<div class="col-12 col-md-9"
						v-if="abgabetypenBetreuer && abgabeTypeOptions"
					>
						<Dropdown
							:style="{'width': '100%'}"
							v-model="serienTermin.bezeichnung"
							:options="getAllowedAbgabeTypeOptions"
							:optionLabel="getOptionLabelAbgabetyp">
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
			@toggle-fullscreen="handleToggleFullscreenDetail">
			<template v-slot:title>
				<div>
					{{$p.t('abgabetool/c4abgabeMitarbeiterDetailTitle')}}
				</div>
			</template>
			<template v-slot:default>
				<AbgabeDetail 
					:projektarbeit="selectedProjektarbeit" 
					:isFullscreen="detailIsFullscreen"
					@paUpdated="handlePaUpdated">
				</AbgabeDetail>
				
			</template>
		</bs-modal>	
		
		<!-- low max height on this vsplit wrapper to avoid padding scrolls, elements have their inherent height anyways -->
		<div id="abgabetable" style="max-height:40vw;">
		
			<h2>{{$p.t('abgabetool/abgabetoolTitle')}}</h2>
			<hr>
			<core-filter-cmpt
				:title="''"  
				@uuidDefined="handleUuidDefined"
				ref="abgabeTable"
				:newBtnShow="true"
				:newBtnLabel="$p.t('abgabetool/neueTerminserie')"
				:newBtnDisabled="!selectedData.length"
				@click:new=openAddSeriesModal
				:tabulator-options="abgabeTableOptions"  
				:tabulator-events="abgabeTableEventHandlers"
				@tableBuilt="handleTableBuilt"
				tableOnly
				:sideMenu="false"
				:useSelectionSpan="false"
			>
				<template #actions>
					<button @click="toggleShowAll(!showAll)" role="button" class="btn btn-secondary ml-2">
						<i v-show="!showAll" class="fa fa-eye"></i>
						<i v-show="showAll" class="fa fa-eye-slash"></i>
						{{ $p.t('abgabetool/showAll') }}
					</button>
					
					<button @click="showDeadlines" role="button" class="btn btn-secondary ml-2">
						<i class="fa fa-hourglass-end"></i>
						{{ $p.t('abgabetool/showDeadlines') }}
					</button>
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

				</template>
			</core-filter-cmpt>
		
		</div>
	</template>
    `,
};

export default AbgabetoolMitarbeiter;
