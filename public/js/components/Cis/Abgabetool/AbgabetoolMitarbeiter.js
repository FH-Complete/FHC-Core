import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeMitarbeiterDetail.js";
import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';
import ApiAbgabe from '../../../api/factory/abgabe.js'

export const AbgabetoolMitarbeiter = {
	name: "AbgabetoolMitarbeiter",
	components: {
		BsModal,
		CoreFilterCmpt,
		AbgabeDetail,
		Dropdown: primevue.dropdown,
		Textarea: primevue.textarea,
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
				layout: 'fitDataStretch',
				placeholder: Vue.computed(() => this.$p.t('global/noDataAvailable')),
				selectable: true,
				selectableCheck: this.selectionCheck,
				rowHeight: 80,
				columns: [
					{
						formatter: 'rowSelection',
						titleFormatter: 'rowSelection',
						titleFormatterParams: {
							rowRange: "active" // Only toggle the values of the active filtered rows
						},
						hozAlign:"center",
						headerSort: false,
						frozen: true,
						width: 70
					},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4details'))), field: 'details', formatter: this.detailFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4personenkennzeichen'))), headerFilter: true, field: 'pkz', formatter: this.pkzTextFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('abgabetool/c4kontakt'))),  field: 'mail', formatter: this.mailFormatter, widthGrow: 1, tooltip: false},
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
					this.selectedData = data
				}
			}
			]};
	},
	methods: {
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
				this.selectedData?.map(projekt => projekt.projektarbeit_id),
				false
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
			this.loading=true
			this.loadAbgaben(details).then((res)=> {
				const pa = this.projektarbeiten?.retval?.find(projekarbeit => projekarbeit.projektarbeit_id == details.projektarbeit_id)
				pa.abgabetermine = res.data[0].retval
				pa.isCurrent = res.data[1]
				
				// pa.abgabetermine.push({ // new abgatermin row
				//
				// 	'paabgabe_id': -1,
				// 	'projektarbeit_id': pa.projektarbeit_id,
				// 	'fixtermin': false,
				// 	'kurzbz': '',
				// 	'datum': new Date().toISOString().split('T')[0],
				// 	'note': this.allowedNotenOptions.find(opt => opt.note == 9),
				// 	'upload_allowed': false,
				// 	'paabgabetyp_kurzbz': '',
				// 	'bezeichnung': '',
				// 	'abgabedatum': null,
				// 	'insertvon': this.viewData?.uid ?? ''
				//	
				// })
				
				pa.abgabetermine.forEach(termin => {
					termin.note = this.allowedNotenOptions.find(opt => opt.note == termin.note)
					termin.file = []
					
					// lektoren are only allowed to edit their own deadline entries
					termin.allowedToSave = termin.insertvon == this.viewData?.uid && pa.betreuerart_kurzbz != 'Zweitbegutachter'
					
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
				'<p style="max-width: 100%; word-wrap: break-word; white-space: normal;">'+val+'</p></div>'
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
			
			const d = data[0]?.retval?.map(projekt => {

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
			this.$refs.abgabeTable.tabulator.setData(d);
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

	},
	computed: {
		
	},
	created() {
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
					<div class="col-3 d-flex justify-content-center align-items-center">
						{{$p.t('abgabetool/c4zieldatum')}}
					</div>
					<div class="col-3 d-flex justify-content-center align-items-center">
						{{$p.t('abgabetool/c4abgabetypv2')}}
					</div>
					<div class="col-6 d-flex justify-content-center align-items-center">
						{{$p.t('abgabetool/c4abgabekurzbz')}}
					</div>
				</div>
				<div class="row">
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
					<div class="col-6 d-flex justify-content-center align-items-center">
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
		<div style="max-height:40vw;">
		
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
