import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeMitarbeiterDetail.js";
import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiAbgabe from '../../../api/factory/abgabe.js'
import FhcOverlay from "../../Overlay/FhcOverlay.js";

export const AbgabetoolMitarbeiter = {
	name: "AbgabetoolMitarbeiter",
	components: {
		BsModal,
		CoreFilterCmpt,
		AbgabeDetail,
		Checkbox: primevue.checkbox,
		Dropdown: primevue.dropdown,
		Textarea: primevue.textarea,
		VueDatePicker,
		FhcOverlay
	},
	provide() {
		return {
			abgabeTypeOptions: Vue.computed(() => this.abgabeTypeOptions),
			abgabetypenBetreuer: Vue.computed(() => this.abgabetypenBetreuer),
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
				return value && value.uid // && value.name -> extensive viewData use only for cis4 onwards
			}
		}
	},
	data() {
		return {
			tableData: null,
			abgabetypenBetreuer: null,
			detailIsFullscreen: false,
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
				rowHeight: 80,
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
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4details'))), field: 'details', formatter: this.detailFormatter, widthGrow: 1, tooltip: false, cssClass: 'sticky-col'},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4personenkennzeichen'))), headerFilter: true, field: 'pkz', formatter: this.pkzTextFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4kontakt'))),  field: 'mail', formatter: this.mailFormatter, widthGrow: 1, tooltip: false, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4vorname'))), field: 'vorname', headerFilter: true, formatter: this.centeredTextFormatter,widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4nachname'))), field: 'nachname', headerFilter: true, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4projekttyp'))), field: 'projekttyp_kurzbz', formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4stg'))), field: 'stg', headerFilter: true, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4sem'))), field: 'studiensemester_kurzbz', headerFilter: true, formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4titel'))), field: 'titel', headerFilter: true, formatter: this.centeredTextFormatter, maxWidth: 500, widthGrow: 8},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4betreuerart'))), field: 'betreuerart_beschreibung',formatter: this.centeredTextFormatter, widthGrow: 1}
				],
				persistence: false,
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
				this.serienTermin.datum.toISOString(),
				this.serienTermin.bezeichnung.paabgabetyp_kurzbz,
				this.serienTermin.bezeichnung.bezeichnung,
				this.serienTermin.kurzbz,
				this.serienTermin.upload_allowed,
				this.selectedData?.map(projekt => projekt.projektarbeit_id),
				false
			)).then(res => {
				if (res.meta.status === "success" && res.data) {
					this.$fhcAlert.alertSuccess(this.$p.t('abgabetool/serienTerminGespeichert'))
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
			this.loading=true
			this.loadAbgaben(details).then((res)=> {
				const pa = this.projektarbeiten?.retval?.find(projekarbeit => projekarbeit.projektarbeit_id == details.projektarbeit_id)
				pa.abgabetermine = res.data[0].retval
				pa.isCurrent = res.data[1]
				
				pa.abgabetermine.forEach(termin => {
					termin.note = this.allowedNotenOptions.find(opt => opt.note == termin.note)
					termin.file = []
					
					// update 08-01-20206: everybody is allowed to do everything in client, critical checks happen at backend level
					termin.allowedToSave = true
					
					// lektoren are not allowed to delete deadlines with existing submissions
					termin.allowedToDelete = termin.allowedToSave && !termin.abgabedatum
					
					termin.bezeichnung = this.abgabeTypeOptions.find(opt => opt.paabgabetyp_kurzbz === termin.paabgabetyp_kurzbz)

				})
				pa.student_uid = details.student_uid
				pa.student = `${pa.vorname} ${pa.nachname}`
				
				this.selectedProjektarbeit = pa
				
				this.$refs.modalContainerAbgabeDetail.show()
			
			}).finally(()=>{this.loading = false})
		},
		centeredTextFormatter(cell) {
			const val = cell.getValue()
			if(!val) return
			
			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<p style="max-width: 100%; width: 100%; overflow-wrap: break-word; word-break: break-word; white-space: normal; margin: 0px; text-align: center">'+val+'</p></div>'
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
				'<a style="max-width: 100%; word-wrap: break-word; white-space: normal;">'+val+'</a></div>'
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
			this.projektarbeiten = data[0]
			this.domain = data[1]
			
			this.tableData = data[0]?.retval?.map(projekt => {

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
			this.$refs.abgabeTable.tabulator.setData(this.tableData);
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
		}).catch(e => {
			console.log(e)
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
				<AbgabeDetail :projektarbeit="selectedProjektarbeit" :isFullscreen="detailIsFullscreen"></AbgabeDetail>
				
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
					
				</template>
			</core-filter-cmpt>
		
		</div>
	</template>
    `,
};

export default AbgabetoolMitarbeiter;
