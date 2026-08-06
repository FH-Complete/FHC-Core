import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeMitarbeiterDetail.js";
import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiAbgabe from '../../../api/factory/abgabe.js'
import FhcOverlay from "../../Overlay/FhcOverlay.js";
import { getDateStyleClass } from "./getDateStyleClass.js";
import { dateFilter } from '../../../tabulator/filters/DatesManual.js';
import {splitMailsHelper} from "../../../helpers/EmailHelpers.js";
import { getViennaTodayISO, toViennaDate } from "./dateUtils.js";
import AbgabetoolTableMixin from "./AbgabetoolTableMixin.js";

export const AbgabetoolMitarbeiter = {
	name: "AbgabetoolMitarbeiter",
	mixins: [AbgabetoolTableMixin],
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
	data() {
		return {
			filteredRows: null,
			count: 0,
			filteredcount: 0,
			selectedcount: 0,
			// restore flags of the table, filled by initTablePersistence
			tableState: {},
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
				datum: getViennaTodayISO(),
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
				layout: 'fitData',
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
						cssClass: 'sticky-col',
						visible: true
					},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4details'))), field: 'details', formatter: this.formAction, headerFilter: false, headerSort: false, width: 140, minWidth: 50, visible: true, tooltip: false, cssClass: 'sticky-col'},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4personenkennzeichen'))), headerFilter: true, field: 'pkz', formatter: this.pkzTextFormatter, minWidth: 140, visible: false,tooltip: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4vorname'))), field: 'vorname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100,visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nachname'))), field: 'nachname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100,visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4studstatus'))), field: 'studienstatus', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 150, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4orgformv2'))), field: 'orgform', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 50, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4projekttyp'))), field: 'projekttyp_kurzbz', formatter: this.centeredTextFormatter, minWidth: 100,visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4stg'))), field: 'stg', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 50, visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4note'))), field: 'note_bez',
						headerFilter: this.notenHeaderFilterEditor,
						headerFilterFunc: this.notenHeaderFilterFunc,
						headerFilterParams: {},
						headerFilterFuncParams: { idField: 'note' }, // column shows the bezeichnung, filter compares the note id
						visible: false, sorter: this.notenSorter, minWidth: 200, formatter: this.centeredTextFormatter},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4sem'))), field: 'studiensemester_kurzbz', headerFilter: true, formatter: this.centeredTextFormatter, visible: true, minWidth: 100},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4titel'))), field: 'titel', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, width: 500, visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuerv2'))), field: 'betreuer_full_name', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},

					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuerTitelPre'))), field: 'betreuer_titelpre', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuerVorname'))), field: 'betreuer_vorname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuerNachname'))), field: 'betreuer_nachname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: true},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4erstbetreuerTitelPost'))), field: 'betreuer_titelpost', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},

					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuerv2'))), field: 'zweitbetreuer_full_name', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},

					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuerTitelPre'))), field: 'zweitbetreuer_titelpre', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuerVorname'))), field: 'zweitbetreuer_vorname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuerNachname'))), field: 'zweitbetreuer_nachname', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4zweitbetreuerTitelPost'))), field: 'zweitbetreuer_titelpost', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4betreuerartv2'))), field: 'betreuerart_kurzbz', headerFilter: true, formatter: this.centeredTextFormatter, minWidth: 100, visible: false},

					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4prevAbgabetermin'))),
						headerFilter: dateFilter,
						headerFilterFunc: this.headerFilterTerminCol,
						sorter: this.sortFuncTerminCol,
						tooltip: this.toolTipFuncPrevTermin,
						field: 'prevTermin', formatter: this.abgabeterminFormatter, width: 250, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nextAbgabetermin'))), field: 'nextTermin',
						headerFilter: dateFilter,
						headerFilterFunc: this.headerFilterTerminCol,
						sorter: this.sortFuncTerminCol,
						tooltip: this.toolTipFuncNextTermin,
						formatter: this.abgabeterminFormatter, width: 250, visible: true},
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
					}
				],
				persistence: false,
				persistenceID: 'abgabeTableBetreuer2026-05-26'
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
			}
			]};
	},
	methods: {
		async openBenotung(type, link) {
			if(type === 'new') {
				window.open(link, '_blank')
			} else if(type === 'old') {
				if(await this.$fhcAlert.confirm({
					message: this.$p.t('abgabetool/c4aeltereParbeitBenotenv2'),
					acceptLabel: this.$capitalize(this.$p.t('abgabetool/c4AcceptAndProceed')),
					acceptClass: 'btn btn-danger',
					rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
					rejectClass: 'btn btn-outline-secondary'
				}) === false) {
					return false
				}

				window.open(link, '_blank')
			} else {
				// show info text that no endupload with abgabe has been found
				if(await this.$fhcAlert.confirm({
					message: this.$p.t('abgabetool/c4keinEnduploadErfolgt'),
					acceptLabel: this.$capitalize(this.$p.t('abgabetool/c4AcceptAndProceed')),
					acceptClass: 'btn btn-danger',
					rejectLabel: this.$capitalize(this.$p.t('abgabetool/c4Cancel')),
					rejectClass: 'btn btn-outline-secondary'
				}) === false) {
					return false
				}
			}
		},
		formAction(cell) {
			
			const actionButtons = document.createElement('div');
			actionButtons.className = "d-flex gap-3";
			actionButtons.style.display = "flex";
			actionButtons.style.alignItems = "stretch";
			actionButtons.style.justifyContent = "start";
			actionButtons.style.height = "100%";

			const val = cell.getValue();
			const data = cell.getRow().getData()
			
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
			);
			
			if(data.isCurrent && data.abgabetermine?.find(termin => termin.paabgabetyp_kurzbz == 'end' && termin.abgabedatum !== null) && data.beurteilungLinkNew) {
				actionButtons.append(createButton('fa fa-user-check', 'abgabetool/c4benoten', () => this.openBenotung('new', data.beurteilungLinkNew)))
			} else if(data.abgabetermine?.find(termin => termin.paabgabetyp_kurzbz == 'end' && termin.abgabedatum !== null) && data.beurteilungLinkOld) {
				actionButtons.append(createButton('fa fa-user-check', 'abgabetool/c4benoten', () => this.openBenotung('old', data.beurteilungLinkOld)))
			}
			
			if(this.checkForZweitbetreuerTokenMailAvailability(data)) {
				actionButtons.append(createButton('fa fa-envelope-open-text', 'abgabetool/c4zweitBegutachterTokenMailSenden', () => this.sendZweitbetreuerToken(data)))

			}
			
			return actionButtons;
		},
		checkForZweitbetreuerTokenMailAvailability(data) {
			const hasEndabgabeWithUpload = !!data.abgabetermine.find(termin => termin.abgabedatum !== null && termin.paabgabetyp_kurzbz == 'end')
			const hasZweitbetreuerWithoutBenutzerUid = data.zweitbetreuer_person_id !== null && data.zweitbetreuer_benutzer_uid === null
			
			return hasEndabgabeWithUpload && hasZweitbetreuerWithoutBenutzerUid
		},
		sendZweitbetreuerToken(data) {

			this.$api.call(ApiAbgabe.sendZweitbetreuerTokenMail(data.projektarbeit_id, data.betreuer_person_id, data.student_uid))
				.then(res => {
					if(res.meta.status == 'success') this.$fhcAlert.alertSuccess(this.$p.t('abgabetool/c4zweitBegutachterTokenMailSuccess'))
				})
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
			splitMailsHelper(uniqueRecipients, param.originalEvent, subject, null, this.$fhcAlert, this.$p)
		},
		handleTableBuilt() {
			this.tableBuiltResolve()

			this.initTablePersistence(this.$refs.abgabeTable, this.abgabeTableOptions.persistenceID, this.tableState)
		},
		checkAbgabetermineProjektarbeit(projekt) {
			const now = luxon.DateTime.now()
			// calculate Abgabetermin time diff to now and assign last and next to projekt
			projekt.abgabetermine.forEach(termin => {
				
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
			const rows = this.filteredRows ?? table.getRows();

			// custom select all logic
			const allowed = rows.filter(r => r.getData().selectable);
			// since betreuerpage acctually has logic behind selectable flag, it is important to go over allowed only here
			const selected = allowed.every(r => r.isSelected());

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
			
			// zweitbetreuer cant select projektarbeiten for serientermine
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
				termin.allowedToDelete = termin.allowedToSave && !termin.abgabedatum && !termin.note
				
				termin.bezeichnung = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === termin.paabgabetyp_kurzbz)

			})
			
			pa.student_uid = details.student_uid
			pa.student = `${pa.vorname} ${pa.nachname}`
			
			this.selectedProjektarbeit = pa
			this.$refs.modalContainerAbgabeDetail.show()
		
		
			this.loading = false
			
		},
		detailFormatter(cell) {
			return '<div style="display: flex; justify-content: start; align-items: center; height: 100%">' +
				'<a><i class="fa fa-folder-open" style="color:#00649C"></i></a></div>'
		},
		buildMailToLink(abgabe) {
			return 'mailto:' + abgabe.uid +'@'+ this.domain
		},
		buildPKZ(projekt) {
			return `${projekt.uid} / ${projekt.matrikelnr}`
		},
		setupData(data){
			this.domain = data[1]
			
			this.projektarbeiten = data[0]?.retval?.map(projekt => {
				this.checkAbgabetermineProjektarbeit(projekt)
				projekt.selectable = projekt.betreuerart_kurzbz !== 'Zweitbegutachter'

				if(this.notenOptions && projekt.note) {
					const opt = this.notenOptions.find(n => n.note == projekt.note)
					// TODO: mehrsprachig englisch -> nevermind the english field in
					// notenoption->bezeichnung_mehrsprachig is ALWAYS german
					projekt.note_bez = opt?.bezeichnung
				}
				
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
			this.count = this.projektarbeiten.length
			
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
		}
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
		document.documentElement.classList.add('abgabetool');
		
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
	beforeUnmount() {
		document.documentElement.classList.remove('abgabetool');
	},
	template: `
	<template v-if="phrasenResolved">
		<FhcOverlay :active="loading || saving"></FhcOverlay>

		<bs-modal ref="modalContainerAddSeries" class="bootstrap-prompt"
			dialogClass="modal-lg"
			bodyClass="px-4 py-4">
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
					@paUpdated="handlePaUpdated">
				</AbgabeDetail>
				
			</template>
		</bs-modal>	
		
		<!-- low max height on this vsplit wrapper to avoid padding scrolls, elements have their inherent height anyways -->
		<div id="abgabetable" style="max-height:40vw;">
		
			<h2>{{$p.t('abgabetool/abgabetoolTitleBetreuer')}}</h2>
			<hr>
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
