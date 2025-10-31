import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeMitarbeiterDetail.js";
import VerticalSplit from "../../verticalsplit/verticalsplit.js"
import BsModal from '../../Bootstrap/Modal.js';
import BsOffcanvas from '../../Bootstrap/Offcanvas.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiAbgabe from '../../../api/factory/abgabe.js'
import AbgabeterminStatusLegende from "./StatusLegende.js";

const todayISO = '2025-08-08'
const today = new Date(todayISO)

export const AbgabetoolAssistenz = {
	name: "AbgabetoolAssistenz",
	components: {
		AbgabeterminStatusLegende,
		BsModal,
		BsOffcanvas,
		CoreFilterCmpt,
		AbgabeDetail,
		VerticalSplit,
		Checkbox: primevue.checkbox,
		Dropdown: primevue.dropdown,
		Textarea: primevue.textarea,
		Timeline: primevue.timeline,
		VueDatePicker
	},
	provide() {
		return {
			abgabeTypeOptions: Vue.computed(() => this.abgabeTypeOptions),
			allowedNotenOptions: Vue.computed(() => this.allowedNotenOptions),
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
				return value && value.name && value.uid
			}
		}
	},
	data() {
		return {
			headerFiltersRestored: false,
			filtersRestored: false,
			colLayoutRestored: false,
			sortRestored: false,
			stateRestored: false,
			timelineProjekt: null,
			selectedStudiengangOption: null,
			studiengaengeOptions: null,
			detailIsFullscreen: false,
			showZweitbetreuerCol: false,
			phrasenPromise: null,
			phrasenResolved: false,
			turnitin_link: null,
			old_abgabe_beurteilung_link: null,
			saving: false,
			loading: false,
			abgabeTypeOptions: null,
			notenOptions: null,
			allowedNotenOptions: null,
			serienTermin: Vue.reactive({
				datum: new Date(),
				bezeichnung: {
					paabgabetyp_kurzbz: 'zwischen',
					bezeichnung: 'Zwischenabgabe'
				},
				kurzbz: ''
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
				layout: 'fitData',
				placeholder: Vue.computed(() => this.$capitalize(this.$p.t('global/noDataAvailable'))),
				selectable: true,
				selectableCheck: this.selectionCheck,
				rowHeight: 40,
				responsiveLayout: true,
				columns: [
					{
						field: 'rowSelection',
						formatter: 'rowSelection',
						titleFormatter: 'rowSelection',
						titleFormatterParams: {
							rowRange: "active" // Only toggle the values of the active filtered rows
						},
						hozAlign:"center",
						headerSort: false,
						frozen: true,
						width: 40
					},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4details'))), field: 'details', formatter: this.formAction, tooltip:false, minWidth: 150,},
					// {title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4details'))), field: 'details', formatter: this.detailFormatter, widthGrow: 1,responsive:0,  tooltip: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4personenkennzeichen'))), headerFilter: true, field: 'pkz', formatter: this.pkzTextFormatter,responsive:0, widthGrow: 1, tooltip: false},
					// {title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4termineTimeLine'))), headerFilter: true, field: 'abgabetermine',responsive:2, formatter: this.timelineFormatter, widthGrow: 1, tooltip: false},
					// {title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4kontakt'))),  field: 'mail', formatter: this.mailFormatter, visible: false, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4vorname'))), field: 'student_vorname', headerFilter: true,responsive:2, formatter: this.centeredTextFormatter,widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nachname'))), field: 'student_nachname', headerFilter: true,responsive:2, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4projekttyp'))), field: 'projekttyp_kurzbz', responsive:3, visible: false, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4stg'))), field: 'stg', headerFilter: true, responsive:3, visible: false, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4sem'))), field: 'studiensemester_kurzbz', headerFilter: true, visible: false, responsive:3,formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4titel'))), field: 'titel', headerFilter: true, responsive:3, visible: false, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuer'))), field: 'erstbetreuer', headerFilter: true, responsive:3,formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuer'))), field: 'zweitbetreuer', headerFilter: true, responsive:3,formatter: this.centeredTextFormatter, widthGrow: 1, visible: Vue.computed(()=>{return this.showZweitbetreuerCol})},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4prevAbgabetermin'))), headerFilter: true, field: 'prevTermin', responsive:4, formatter: this.abgabterminFormatter, widthGrow: 1, width: 220, tooltip: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nextAbgabetermin'))), headerFilter: true, field: 'nextTermin', responsive:4, formatter: this.abgabterminFormatter, widthGrow: 1, width: 220, tooltip: false},
				],
				persistence: false,
				persistenceID: "abgabetableV27"
			},
			abgabeTableEventHandlers: [{
				event: "tableBuilt",
				handler: async () => {
					this.tableBuiltResolve()
				}
			},
				// {
				// 	event: "cellClick",
				// 	handler: async (e, cell) => {
				// 		if(cell.getColumn().getField() === "details") {
				// 			this.setDetailComponent(cell.getValue())
				// 			this.undoSelection(cell)
				// 		} else if (cell.getColumn().getField() === "mail") {
				// 			this.undoSelection(cell)
				// 		} else if (cell.getColumn().getField() === "abgabetermine") {
				// 			this.openTimeline(cell.getValue())
				// 			this.undoSelection(cell)
				// 		}
				// 	}
				// },
				{
					event: "rowSelectionChanged",
					handler: async(data) => {
						this.selectedData = data
					}
				}
			]};
	},
	methods: {
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
					debugger
					if (saved?.columns && !this.colLayoutRestored) {
						const layout = saved.columns.map(col => ({
							field: col.field,
							width: col.width,
							visible: col.visible,
							// add more if needed, but keep it simple
						}));
						
						console.log(layout)

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
		handleToggleFullscreenDetail() {
			this.detailIsFullscreen = !this.detailIsFullscreen
		},
		getOptionLabelAbgabetyp(option){
			return option.bezeichnung
		},
		getOptionLabelStg(option){
			return option.kurzbzlang + ' ' + option.bezeichnung
		},
		sgChanged(e) {
			debugger	
		},
		formatDate(dateParam) {
			if(dateParam === null) return ''
			const date = new Date(dateParam)
			// handle missing leading 0
			const padZero = (num) => String(num).padStart(2, '0');

			const month = padZero(date.getMonth() + 1); // Months are zero-based
			const day = padZero(date.getDate());
			const year = date.getFullYear();

			return `${day}.${month}.${year}`;
		},
		formAction(cell) {
			const actionButtons = document.createElement('div');
			actionButtons.className = "d-flex gap-3"; // you can keep Bootstrap gap if loaded
			actionButtons.style.display = "flex";
			actionButtons.style.alignItems = "stretch"; // buttons stretch to full height
			actionButtons.style.justifyContent = "center";
			actionButtons.style.height = "100%"; // full grid cell height

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

			return actionButtons;
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
				this.serienTermin.datum.toISOString(),
				this.serienTermin.bezeichnung.paabgabetyp_kurzbz,
				this.serienTermin.bezeichnung.bezeichnung,
				this.serienTermin.kurzbz,
				this.selectedData?.map(projekt => projekt.projektarbeit_id)
			)).then(res => {
				if (res.meta.status === "success" && res.data) {
					this.$fhcAlert.alertSuccess(this.$p.t('abgabetool/serienTerminGespeichert'))
					// TODO: sticky lifetime erhöhen um sinnvoll lesen zu können?
					this.$fhcAlert.alertInfo(this.$p.t('abgabetool/serienTerminEmailSentInfo', [this.createInfoString(res.data)]));
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
			return new Date(date) < new Date(Date.now())
		},
		setDetailComponent(details){
			
			const pa = this.projektarbeiten.find(projekarbeit => projekarbeit.projektarbeit_id == details.projektarbeit_id)

			// pa.isCurrent = res.data[1]

			pa.abgabetermine.forEach(termin => {
				termin.note = this.allowedNotenOptions.find(opt => opt.note == termin.note)
				termin.file = []

				// assistenz should be able to edit every abgabe
				termin.allowedToSave = true

				// assistenz are not allowed to delete deadlines with existing submissions
				termin.allowedToDelete = !termin.abgabedatum

				termin.bezeichnung = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === termin.paabgabetyp_kurzbz)

			})
			
			// TODO: do same thing for sidebar
			
			const vorname = pa.vorname ?? pa.student_vorname
			const nachname = pa.nachname ?? pa.student_nachname
			pa.student = `${vorname} ${nachname}`

			this.selectedProjektarbeit = pa

			this.$refs.modalContainerAbgabeDetail.show()
		},
		dateDiffInDays(datum, today){
			const oneDayMs = 1000 * 60 * 60 * 24
			return Math.round((new Date(datum) - new Date(today)) / oneDayMs)
		},
		getDateStyleClass(termin) {
			const datum = new Date(termin.datum)
			const abgabedatum = new Date(termin.abgabedatum)

			// https://wiki.fhcomplete.info/doku.php?id=cis:abgabetool_fuer_studierende
			if (termin.abgabedatum === null) {
				if(datum < today) {
					return 'verpasst'
				} else if (datum > today && this.dateDiffInDays(datum, today) <= 12) {
					return 'abzugeben'
				} else {
					return 'standard'
				}
			} else if(abgabedatum > datum) {
				return 'verspaetet'
			} else {
				return 'abgegeben'
			}
		},
		openTimeline(val) {
			const projekt = this.projektarbeiten.find(p => p.projektarbeit_id == val.projektarbeit_id)
			if(!projekt) {

				this.$fhcAlert.alertInfo('keine projektarbeit gefunden')
				
				return
			}
			projekt.abgabetermine.forEach(termin => {
				// show note only on termine with abgabetypen which are benotbar
				const terminTypOpt = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz == termin.paabgabetyp_kurzbz)
				termin.benotbar = terminTypOpt.benotbar 
			})
			this.timelineProjekt = projekt
			// this.timelineAbgabetermine = projekt.abgabetermine
			this.$refs.drawer.show()
		},
		centeredTextFormatter(cell) {
			const val = cell.getValue()
			if(!val) return

			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<p style="max-width: 100%; overflow-wrap: break-word; word-break: break-word; white-space: normal; margin: 0px; text-align: center">'+val+'</p></div>'
		},
		detailFormatter(cell) {
			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<a><i class="fa fa-folder-open" style="color:#00649C"></i></a></div>'
		},
		mailFormatter(cell) {
			const val = cell.getValue()
			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<a href='+val+'><i class="fa fa-envelope" style="color:#00649C"></i></a></div>'
		},
		timelineFormatter() {
			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<a><i class="fa fa-timeline" style="color:#00649C"></i></a></div>'
		},
		beurteilungFormatter(cell) {
			const val = cell.getValue()
			if(val) {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
					'<a><i class="fa fa-file-pdf" style="color:#00649C"></i></a></div>'
			} else return '-'
		},
		pkzTextFormatter(cell) {
			const val = cell.getValue()

			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<p style="max-width: 100%; word-wrap: break-word; white-space: normal;">'+val+'</p></div>'
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
					case 'verpasst':
						icon = '<i class="fa-solid fa-hourglass-half"></i>'
						break
					case 'standard':
						icon = '<i class="fa-solid fa-clock"></i>'
						break
					case 'abgegeben':
						icon = '<i class="fa-solid fa-check"></i>'
						break
				}
				
				return '<div style="display: flex; height: 100%">' +
					'<div class=' + val.dateStyle + "-header" + ' style="width:48px; height: 100%; padding: 0px; display: flex; align-items: center; justify-content: center;">' +
						icon +
					'</div>' + 
					'<div style="margin-left: 4px;">' +
						'<p style="max-width: 100%; word-wrap: break-word; white-space: normal;">'+val.bezeichnung+' - '+ this.formatDate(val.datum)+'</p>' +
					'</div>'+
					'</div>'

			} else {
				return ''
			}
			
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		buildMailToLink(abgabe) {
			return 'mailto:' + abgabe.student_uid +'@'+ this.domain
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
		setupData(data){
			this.projektarbeiten = data[0]
			this.domain = data[1]

			const now = luxon.DateTime.fromISO(todayISO)
			// const now = luxon.DateTime.now();
			const d = data[0].map(projekt => {
				let mode = 'detailTermine'

				projekt.prevTermin = undefined;
				projekt.nextTermin = undefined;
				
				// only show 2tbetreuer col if any projektarbeit has one
				if(projekt.zweitbetreuer_full_name) this.showZweitbetreuerCol = true
				
				// calculate Abgabetermin time diff to now and assign last and next to projekt
				projekt.abgabetermine.forEach(termin => {
					
					// while already looping through each termin, calculate datestyle beforehand
					termin.dateStyle = this.getDateStyleClass(termin)
					
					const date = luxon.DateTime.fromISO(termin.datum)
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
					
				return {
					...projekt,
					abgabetermine: projekt.abgabetermine,
					details: {
						student_uid: projekt.student_uid,
						projektarbeit_id: projekt.projektarbeit_id,
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

			this.$refs.abgabeTable.tabulator.clearData()
			this.$refs.abgabeTable.tabulator.setColumns(this.abgabeTableOptions.columns)
			this.$refs.abgabeTable.tabulator.setData(d);
		},
		loadProjektarbeiten(all = false, callback) {
			this.loading = true
			this.$api.call(ApiAbgabe.getProjektarbeitenForStudiengang(this.getCurrentStudiengang))
				.then(res => {
					if(res?.data) this.setupData(res.data)
				}).finally(() => {
				if(callback) {
					callback()
				}
			}).finally(()=>{
				this.loading=false
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

			// this.$refs.verticalsplit.collapseBottom()
			this.calcMaxTableHeight()

		},
		sendEmailBegutachter() {
			// TODO: implement
		},
		sendEmailStudierende() {
			// TODO: implement
		}
	},
	watch: {
		selectedStudiengangOption(newVal, oldVal) {
			this.loadProjektarbeiten()
		}
	},
	computed: {
		getCurrentStudiengang() {
			// TODO: sophisticated logic pulling from default value by viewData or dropdown select
			
			return this.selectedStudiengangOption?.studiengang_kz ?? 257
		}
	},
	created() {
		this.loading = true
		this.phrasenPromise = this.$p.loadCategory(['abgabetool', 'global'])
		this.phrasenPromise.then(()=> {this.phrasenResolved = true})
		// fetch config to avoid hard coded links
		this.$api.call(ApiAbgabe.getConfig()).then(res => {
			this.turnitin_link = res.data?.turnitin_link
			this.old_abgabe_beurteilung_link = res.data?.old_abgabe_beurteilung_link
		}).catch(e => {
			console.log(e)
			this.loading = false
		})

		// fetch studiengänge options
		this.$api.call(ApiAbgabe.getStudiengaenge()).then(res => {
			this.studiengaengeOptions = res.data
			console.log(this.studiengaengeOptions)
		}).catch(e => {
			this.loading = false
		})
		
		// fetch noten options
		//TODO: SWITCH TO NOTEN API ONCE NOTENTOOL IS IN MASTER TO AVOID DUPLICATE API
		this.$api.call(ApiAbgabe.getNoten()).then(res => {
			this.notenOptions = res.data
			// TODO: more sophisticated way to filter for these two, in essence it is still hardcoded
			this.allowedNotenOptions = this.notenOptions.filter(
				opt => opt.bezeichnung === 'Bestanden'
					|| opt.bezeichnung === 'Nicht bestanden'
			)
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
		<div id="loadingOverlay" v-show="loading || saving" style="position: absolute; width: 100vw; height: 100vh; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.5); z-index: 99999999999;">
			<i class="fa-solid fa-spinner fa-pulse fa-5x"></i>
		</div>

		<bs-modal ref="modalContainerAddSeries" class="bootstrap-prompt"
			dialogClass="modal-lg">
			<template v-slot:title>
				<div>
					{{ $p.t('abgabetool/neueTerminserie') }}
				</div>
			</template>
			<template v-slot:default>
				<div class="row">
					<div class="col-1 d-flex justify-content-center align-items-center">
						{{$p.t('abgabetool/c4fixterminv2')}}
					</div>
					<div class="col-3 d-flex justify-content-center align-items-center">
						{{$p.t('abgabetool/c4zieldatum')}}
					</div>
					<div class="col-3 d-flex justify-content-center align-items-center">
						{{$p.t('abgabetool/c4abgabetypv2')}}
					</div>
					<div class="col-5 d-flex justify-content-center align-items-center">
						{{$p.t('abgabetool/c4abgabekurzbz')}}
					</div>
				</div>
				<div class="row">
					<div class="col-1 d-flex justify-content-center align-items-center">
						<Checkbox 
							v-model="serienTermin.fixtermin"
							:binary="true" 
							:pt="{ root: { class: 'ml-auto' }}"
						>
						</Checkbox>
					</div>
					<div class="col-3 d-flex justify-content-center align-items-center">
						<div>
							<VueDatePicker
								style="width: 95%;"
								v-model="serienTermin.datum"
								:clearable="false"
								:enable-time-picker="false"
								:format="formatDate"
								:text-input="true"
								auto-apply>
							</VueDatePicker>
						</div>				
					</div>
					<div class="col-3 d-flex justify-content-center align-items-center">
						<Dropdown 
							:style="{'width': '100%'}"
							v-model="serienTermin.bezeichnung"
							:options="abgabeTypeOptions"
							:optionLabel="getOptionLabelAbgabetyp">
						</Dropdown>
					</div>
					<div class="col-5 d-flex justify-content-center align-items-center">
						<Textarea style="margin-bottom: 4px;" v-model="serienTermin.kurzbz" rows="1" cols="40"></Textarea>
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
				<AbgabeDetail :projektarbeit="selectedProjektarbeit" :isFullscreen="detailIsFullscreen" :assistenzMode="true"></AbgabeDetail>
				
			</template>
		</bs-modal>	
		
		<BsOffcanvas
			ref="drawer"
			placement="end"
			:backdrop="true"
			@shownBsOffcanvas="onShown"
			@hiddenBsOffcanvas="onHidden"
			:style="{ '--bs-offcanvas-width': '600px' }"
		>
			<template #title>
				{{ $p.t('abgabetool/c4projektarbeitTimelineTitle') }}
			</template>
		
			<Timeline 
				:value="timelineProjekt?.abgabetermine"
				align="right"	
			>
			
				<template #marker="slotProps">
					<div :class="slotProps.item.dateStyle + '-header'" style="height: 48px; width:48px; padding: 0px; display: flex; align-items: center; justify-content: center;">
						<i v-if="slotProps.item.dateStyle == 'verspaetet'" class="fa-solid fa-triangle-exclamation"></i>
						<i v-else-if="slotProps.item.dateStyle == 'verpasst'" class="fa-solid fa-calendar-xmark"></i>
						<i v-else-if="slotProps.item.dateStyle == 'abzugeben'"  class="fa-solid fa-hourglass-half"></i>
						<i v-else-if="slotProps.item.dateStyle == 'standard'"  class="fa-solid fa-clock"></i>
						<i v-else-if="slotProps.item.dateStyle == 'abgegeben'"  class="fa-solid fa-check"></i>
					</div>
				</template>
			
				<template #opposite="slotProps">
					<div class="row g-1">
						<div class="col-5 fw-semibold text-end">
							{{ $capitalize($p.t('abgabetool/c4zieldatum')) }}:
						</div>
						<div class="col-7">
							{{ formatDate(slotProps.item.datum) }}
						</div>
					</div>
				</template>
				
				<template #content="slotProps">
				 	<div class="row g-1">
						<div class="col-5 fw-semibold text-end">
							{{ $capitalize($p.t('abgabetool/c4abgabetyp')) }}:
						</div>
						<div class="col-7">
							{{ slotProps?.item?.bezeichnung?.bezeichnung ?? slotProps?.item?.bezeichnung }}
						</div>
				
						<div class="col-5 fw-semibold text-end">
							{{ $capitalize($p.t('abgabetool/c4abgabedatum')) }}:
						</div>
						<div class="col-7">
							{{ formatDate(slotProps.item.abgabedatum) }}
						</div>
						
						<div v-if="slotProps.item.benotbar" class="col-5 fw-semibold text-end">
							{{ $capitalize($p.t('abgabetool/c4note')) }}:
						</div>
						<div v-if="slotProps.item.benotbar" class="col-7">
							{{ slotProps.item.note }}
						</div>
				 	</div>
				 	<hr/>
				</template>
				
			</Timeline>
			
			<template #footer>
				<AbgabeterminStatusLegende></AbgabeterminStatusLegende>
			</template>
		</BsOffcanvas>
		
		<!-- low max height on this vsplit wrapper to avoid padding scrolls, elements have their inherent height anyways -->
		<div style="max-height:40vw;">
			<div class="row">
				<div class="col-auto">
					<h2>{{$p.t('abgabetool/abgabetoolTitle')}}</h2>
				</div>
				<div class="col-3">
					<Dropdown
						@change="sgChanged" 
						:placeholder="$p.t('lehre/studiengang')" 
						:style="{'width': '100%', 'scroll-behavior': 'auto !important'}" 
						:optionLabel="getOptionLabelStg" 
						v-model="selectedStudiengangOption" 
						:options="studiengaengeOptions" 
						showClear
					>
						<template #optionsgroup="slotProps">
							<div> {{ option.kurzbzlang }} {{ option.bezeichnung }} </div>
						</template>
					</Dropdown>
				</div>
			</div>
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
					<button @click="sendEmailStudierende" role="button" class="btn btn-secondary ml-2">
						{{ $p.t('abgabetool/c4sendEmailStudierende') }}
					</button>
					
					<button @click="sendEmailBegutachter" role="button" class="btn btn-secondary ml-2">
						{{ $p.t('abgabetool/c4sendEmailBetreuer') }}
					</button>
					
				</template>
			</core-filter-cmpt>
		</div>
	</template>
    `,
};

export default AbgabetoolAssistenz;
