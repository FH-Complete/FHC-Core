import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeMitarbeiterDetail.js";
import BsModal from '../../Bootstrap/Modal.js';
import BsOffcanvas from '../../Bootstrap/Offcanvas.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiAbgabe from '../../../api/factory/abgabe.js'
import ApiStudiensemester from '../../../api/factory/studiensemester.js';
import AbgabeterminStatusLegende from "./StatusLegende.js";
import FhcOverlay from "../../Overlay/FhcOverlay.js";

// spoofed date testing
// const todayISO = '2025-08-08'
// const today = new Date(todayISO)
// const now = luxon.DateTime.fromISO(todayISO)

// prod code
const today = new Date()
const now = luxon.DateTime.now()

export const AbgabetoolAssistenz = {
	name: "AbgabetoolAssistenz",
	components: {
		AbgabeterminStatusLegende,
		BsModal,
		BsOffcanvas,
		CoreFilterCmpt,
		AbgabeDetail,
		Checkbox: primevue.checkbox,
		Dropdown: primevue.dropdown,
		Inplace: primevue.inplace,
		Textarea: primevue.textarea,
		Timeline: primevue.timeline,
		VueDatePicker,
		FhcOverlay
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
		stg_kz_prop: {
			default: null
		},
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
			studiensemesterOptions: null,
			allSem: null,
			curSem: null,
			notenOptionFilter: null,
			inplaceToggle: false,
			headerFiltersRestored: false,
			filtersRestored: false,
			colLayoutRestored: false,
			sortRestored: false,
			stateRestored: false,
			timelineProjekt: null,
			selectedStudiengangOption: null,
			studiengaengeOptions: null,
			detailIsFullscreen: false,
			phrasenPromise: null,
			phrasenResolved: false,
			turnitin_link: null,
			old_abgabe_beurteilung_link: null,
			saving: false,
			loading: false,
			abgabeTypeOptions: null,
			notenOptions: null,
			allowedNotenFilterOptions: null,
			allowedNotenOptions: null,
			serienTermin: Vue.reactive({
				datum: new Date(),
				bezeichnung: {
					paabgabetyp_kurzbz: 'zwischen',
					bezeichnung: 'Zwischenabgabe'
				},
				kurzbz: '',
				fixtermin: false,
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
				layout: 'fitData',
				placeholder: Vue.computed(() => this.$capitalize(this.$p.t('global/noDataAvailable'))),
				selectable: true,
				selectableCheck: this.selectionCheck,
				rowHeight: 40,
				renderVerticalBuffer: 2000,
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
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4details'))), field: 'details', formatter: this.formAction, tooltip:false, minWidth: 150, visible: true},
					// {title: 'pa_id', field: 'projektarbeit_id', visible: true},
					// {title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4details'))), field: 'details', formatter: this.detailFormatter, widthGrow: 1,responsive:0,  tooltip: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4personenkennzeichen'))), headerFilter: true, field: 'pkz', formatter: this.pkzTextFormatter,responsive:0, widthGrow: 1, tooltip: false},
					// {title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4termineTimeLine'))), headerFilter: true, field: 'abgabetermine',responsive:2, formatter: this.timelineFormatter, widthGrow: 1, tooltip: false},
					// {title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4kontakt'))),  field: 'mail', formatter: this.mailFormatter, visible: false, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4vorname'))), field: 'student_vorname', headerFilter: true,responsive:2, formatter: this.centeredTextFormatter,widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nachname'))), field: 'student_nachname', headerFilter: true,responsive:2, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4studstatus'))), field: 'studienstatus', headerFilter: true,responsive:2, formatter: this.centeredTextFormatter,widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4orgform'))), field: 'orgform', headerFilter: true,responsive:2, formatter: this.centeredTextFormatter,widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4projekttyp'))), field: 'projekttyp_kurzbz', responsive:3, visible: false, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4stg'))), field: 'stg', headerFilter: true, responsive:3, visible: false, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4note'))), field: 'note_bez', headerFilter: true,
						responsive:3, visible: false, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4sem'))), field: 'studiensemester_kurzbz', headerFilter: true, visible: true, responsive:2,formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4titel'))), field: 'titel', headerFilter: true, responsive:3, visible: false, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuer'))), field: 'erstbetreuer', headerFilter: true, responsive:3,formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuer'))), field: 'zweitbetreuer', headerFilter: true, responsive:3,formatter: this.centeredTextFormatter, widthGrow: 1, visible: false},
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
			{
				event: "rowSelectionChanged",
				handler: async(data) => {
					this.selectedData = data
				}
			}
			]};
	},
	methods: {
		semesterChanged(e) {
			if(this.$refs.abgabeTable.tabulator) {
				const table = this.$refs.abgabeTable.tabulator

				// TODO: maybe check if existing synergy really works with many filters
				const existing = table.getFilters().filter(f => f.field != 'studiensemester_kurzbz');

				const compVal = e.value.studiensemester_kurzbz == 'Alle' ? '' : e.value.studiensemester_kurzbz
				const compType = e.value.studiensemester_kurzbz == 'Alle' ? '!=' : '='
				const newFilter = { field: "studiensemester_kurzbz", type: compType, value: compVal };

				// merge and reapply
				table.setFilter([...existing, newFilter]);
			}
			
		},
		checkAbgabetermineProjektarbeit(projekt) {
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
			// table.on("columnMoved", () => {
			// 	this.saveState(table);
			// });
			//
			// table.on("columnResized", () => {
			// 	this.saveState(table);
			// });
			//
			// table.on("columnVisibilityChanged", () => {
			// 	this.saveState(table);
			// });
			//
			// table.on("filterChanged", () => {
			// 	this.saveState(table);
			// });
			//
			// table.on("headerFilterChanged", () => {
			// 	this.saveState(table);
			// });
			//
			// table.on("dataSorted", () => {
			// 	this.saveState(table);
			// });
			//
			// table.on("columnSorted", () => {
			// 	this.saveState(table);
			// });
			//
			// table.on("sortersChanged", () => {
			// 	this.saveState(table);
			// });
			//
			// const saved = this.loadState();
			
			// table.on("renderComplete", () => {
			// 	if(!this.stateRestored) {
			//		
			// 		if (saved?.columns && !this.colLayoutRestored) {
			// 			const layout = saved.columns.map(col => ({
			// 				field: col.field,
			// 				width: col.width,
			// 				visible: col.visible,
			// 				// add more if needed, but keep it simple
			// 			}));
			//
			// 			const safeLayout = layout.filter(col =>
			// 				col.field && !["rowSelection", "rowHandle", "rowNum", "zweitbetreuer"].includes(col.field)
			// 			);
			//			
			// 			table.setColumnLayout(safeLayout);
			//
			// 			this.colLayoutRestored = true;
			// 		}
			//
			// 		if (saved?.filters && !this.filtersRestored) {
			// 			this.filtersRestored = true // instantly avoid retriggers
			// 			table.setFilter(saved.filters);
			// 		}
			// 		if (saved?.headerFilters && !this.headerFiltersRestored) {
			// 			this.headerFiltersRestored = true // instantly avoid retriggers
			// 			for (let hf of saved.headerFilters) {
			// 				table.setHeaderFilterValue(hf.field, hf.value);
			// 			}
			// 		}
			//
			// 		if (saved?.sort?.length && !this.sortRestored) {
			// 			this.sortRestored = true;
			//
			// 			setTimeout(() => {
			// 				const sortList = saved.sort.map(s => {
			// 					const col = table.columnManager.findColumn(s.field);
			// 					if (!col) {
			// 						return null;
			// 					}
			// 					return { column: col, dir: s.dir };
			// 				}).filter(Boolean);
			//
			// 				table.setSort(sortList);
			// 			}, 100);
			// 		}
			// 		this.stateRestored = true
			//
			// 	}
			//
			// });
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
		openAddSeriesModal() {
			this.$refs.modalContainerAddSeries.show()
		},
		addSeries() {
			const pids = this.selectedData?.map(projekt => projekt.projektarbeit_id)
			this.saving = true
			this.$api.call(ApiAbgabe.postSerientermin(
				this.serienTermin.datum.toISOString(),
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
					pa.abgabetermine.sort((a, b) => new Date(a.datum) - new Date(b.datum))
				})
				
				// reset selection to empty
				this.$refs.abgabeTable.tabulator.deselectRow()

				const mappedData = this.mapProjekteToTableData(this.projektarbeiten)
				
				this.$refs.abgabeTable.tabulator.setColumns(this.abgabeTableOptions.columns)
				this.$refs.abgabeTable.tabulator.setData(mappedData)
				
			}).finally(()=>{
				this.saving = false
			})

			this.$refs.modalContainerAddSeries.hide()
		},
		mapProjekteToTableData(projekte) {
			// const now = luxon.DateTime.now();
			return projekte.map(projekt => {

				projekt.prevTermin = null;
				projekt.nextTermin = null;


				this.checkAbgabetermineProjektarbeit(projekt)

				if(this.notenOptions && projekt.note) {
					const opt = this.notenOptions.find(n => n.note == projekt.note)
				
					// TODO: mehrsprachig englisch
					projekt.note_bez = opt.bezeichnung
				}
				
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
			
			const pa = this.projektarbeiten.find(projektarbeit => projektarbeit.projektarbeit_id == details.projektarbeit_id)

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
			
			const vorname = pa.vorname ?? pa.student_vorname
			const nachname = pa.nachname ?? pa.student_nachname
			pa.student = `${vorname} ${nachname}`

			this.selectedProjektarbeit = pa

			this.$refs.modalContainerAbgabeDetail.show()
		},
		dateDiffInDays(datum){
			const dateToday = luxon.DateTime.now().startOf('day');

			const dateDatum = luxon.DateTime.fromISO(datum).startOf('day');

			const duration = dateDatum.diff(dateToday, 'days');

			return duration.values.days;
		},
		getDateStyleClass(termin) {
			const datum = new Date(termin.datum)
			const abgabedatum = new Date(termin.abgabedatum)

			termin.diffindays = this.dateDiffInDays(termin.datum)

			// seperate status if termin is in the past, it needs a note but doesnt have one yet			
			if(today > datum && termin.benotbar && !termin.note) return 'beurteilungerforderlich'
			if (termin.abgabedatum === null && termin.upload_allowed) {
				if(datum < today) {
					return 'verpasst' // needs upload, missed it and has not submitted anything 
				} else if (datum > today && termin.diffindays <= 12) {
					return 'abzugeben' // needs to upload soon
				} else {
					return 'standard' // upload in distant future
				}
			}
			else if(abgabedatum > datum) {
				return 'verspaetet' // needs upload, missed it and has submitted smth late
			} else if(!termin.upload_allowed) {
				if(datum > today) return termin.diffinday <= 12 ? 'abzugeben' : 'standard'
				else if (today > datum) return 'abgegeben'
			} else {
				return 'abgegeben' // nothing else to do for that termin
			}
		},
		openTimeline(val) {
			const projekt = this.projektarbeiten.find(p => p.projektarbeit_id == val.projektarbeit_id)
			if(!projekt) {

				this.$fhcAlert.alertInfo('Keine projektarbeit gefunden')
				
				return
			}
			projekt.abgabetermine.forEach(termin => {
				// show note only on termine with abgabetypen which are benotbar
				const terminTypOpt = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz == termin.paabgabetyp_kurzbz)
				termin.benotbar = terminTypOpt.benotbar 
			})
			this.timelineProjekt = projekt
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
					case 'abzugeben':
						icon = '<i class="fa-solid fa-hourglass-half"></i>'
						break
					case 'standard':
						icon = '<i class="fa-solid fa-clock"></i>'
						break
					case 'abgegeben':
						icon = '<i class="fa-solid fa-check"></i>'
						break
				}
				
				const bezeichnung = val.bezeichnung?.bezeichnung ?? val.bezeichnung
				
				return '<div style="display: flex; height: 100%">' +
					'<div class=' + val.dateStyle + "-header" + ' style="width:48px; height: 100%; padding: 0px; display: flex; align-items: center; justify-content: center;">' +
						icon +
					'</div>' + 
					'<div style="margin-left: 4px;">' +
						'<p style="max-width: 100%; word-wrap: break-word; white-space: normal;">'+bezeichnung+' - '+ this.formatDate(val.datum)+'</p>' +
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
			
			const mappedData = this.mapProjekteToTableData(this.projektarbeiten)
			
			// this.$refs.abgabeTable.tabulator.clearData()
			this.$refs.abgabeTable.tabulator.setColumns(this.abgabeTableOptions.columns)
			this.$refs.abgabeTable.tabulator.setData(mappedData);
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
			
			// called through notenOptionFilter/selectedStudiengangOption watcher on startup
			// this.loadProjektarbeiten()

			this.calcMaxTableHeight()
		}
	},
	watch: {
		selectedStudiengangOption(newVal, oldVal) {
			// implicitely avoids juggling around promises for created api calls,
			// since we need note & stg flags for loadProjektarbeiten
			if(this.notenOptionFilter !== null && this.selectedStudiengangOption !== null) {
				this.loadProjektarbeiten()
			}
		},
		notenOptionFilter(newVal) {
			// that single where clause is worth a decent load time so rather not filter tabulator but just 
			// adapt the qry
			if(this.notenOptionFilter !== null && this.selectedStudiengangOption !== null) {
				this.loadProjektarbeiten()
			}
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
			this.loading = false
		})

		// fetch studiengänge options
		this.$api.call(ApiAbgabe.getStudiengaenge()).then(res => {
			this.studiengaengeOptions = res.data
			if(this.studiengaengeOptions?.length) {
				
				// use this.stg_kz_prop as default selected in case of url param usage
				
				this.selectedStudiengangOption = this.stg_kz_prop ? res.data.find(stgOpt => stgOpt.studiengang_kz == this.stg_kz_prop) : res.data[0]
			}
			
		}).catch(e => {
			this.loading = false
		})
		
		this.$api.call(ApiStudiensemester.getAllStudiensemesterAndAktOrNext()).then((res) => {
			this.allSem = res.data[0]
			const all = {studiensemester_kurzbz: 'Alle'}
			this.curSem = all // res.data[1]
			
			this.studiensemesterOptions = [all, ...this.allSem]

		}).catch(e => {
			this.loading = false
		})


		// fetch noten options
		//TODO: SWITCH TO NOTEN API ONCE NOTENTOOL IS IN MASTER TO AVOID DUPLICATE API
		this.$api.call(ApiAbgabe.getNoten()).then(res => {
			if(res.meta.status == 'success') {
				this.notenOptions = res.data[0]

				this.allowedNotenOptions = this.notenOptions.filter(
					opt => res.data[1].includes(opt.bezeichnung)
				)
			}
			
			// allowedNotenOptions apply to quality gates abgabetermine
			// this selection is about graded projektarbeiten, so take different options here
			this.allowedNotenFilterOptions = [
				{
					bezeichnung: Vue.computed(() => this.$p.t('abgabetool/keineNoteEingetragen')),
					benotet: 0,
				},
				{
					bezeichnung: Vue.computed(() => this.$p.t('abgabetool/c4benotet')),
					benotet: 1,
				},
				{
					bezeichnung: Vue.computed(() => this.$p.t('abgabetool/showAll')),
					benotet: -1,
				},
			]
			
			this.notenOptionFilter = this.allowedNotenFilterOptions[0]
				
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
					<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4fixterminv4') )}}</div>
					<div class="col-12 col-md-9">
						<Checkbox
							v-model="serienTermin.fixtermin"
							:binary="true"
							:pt="{ root: { class: 'ml-auto' }}"
						>
						</Checkbox>
					</div>
				</div>
			
				<div class="row mt-2">
					<div class="col-12 col-md-3 align-content-center">
						<div class="row fw-bold" style="margin-left: 2px">{{$capitalize( $p.t('abgabetool/c4zieldatum') )}}</div>
					</div>
					<div class="col-12 col-md-9">
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
							:optionLabel="getOptionLabelAbgabetyp">
						</Dropdown>
					</div>
				</div>
				
				<div class="row mt-2">
					<div class="col-12 col-md-3 fw-bold align-content-center">{{$capitalize( $p.t('abgabetool/c4abgabekurzbz') )}}</div>
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
				<AbgabeDetail :projektarbeit="selectedProjektarbeit" :isFullscreen="detailIsFullscreen" :assistenzMode="true"></AbgabeDetail>
				
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

			<div class="row" style="margin-bottom: 12px;">
				<Inplace
					closable
					:closeButtonProps="{
						style: {
							position: 'absolute',
							top: '80px',
							right: '80px',
							zIndex: 1
						}
					}"
				>
					<template #display> {{ $capitalize($p.t('abgabetool/showStudentDetails'))}} </template>
					<template #content>
						<div class="col-auto">
							<div class="row">
								<div class="col-3">Student: </div>
								<div class="col-7">{{timelineProjekt?.student_vorname}} {{timelineProjekt?.student_nachname}}</div>
							</div>
							<div class="row">
								<div class="col-3">Uid: </div>
								<div class="col-7">{{timelineProjekt?.student_uid}}</div>
							</div>
							<div class="row">
								<div class="col-3">{{timelineProjekt?.betreuerart}}: </div>
								<div class="col-7">{{timelineProjekt?.erstbetreuer_full_name}}</div>
							</div>
							<div class="row">
								<div class="col-3">Titel: </div>
								<div class="col-7">{{timelineProjekt?.titel}}</div>
							</div>
						</div>
					</template>
				</Inplace>
			</div>
		
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
		
		<div style="max-height:40vw;">
			<div class="row">
				<div class="col-auto">
					<h2 tabindex="1">{{$p.t('abgabetool/abgabetoolTitle')}}</h2>
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
				<div class="col-3">
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
					<Dropdown
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
				</template>
			</core-filter-cmpt>
		</div>
	</template>
    `,
};

export default AbgabetoolAssistenz;
