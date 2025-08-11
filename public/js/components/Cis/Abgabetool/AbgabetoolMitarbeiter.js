import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeMitarbeiterDetail.js";
import VerticalSplit from "../../verticalsplit/verticalsplit.js"
import BsModal from '../../Bootstrap/Modal.js';
import VueDatePicker from '../../vueDatepicker.js.php';

export const AbgabetoolMitarbeiter = {
	name: "AbgabetoolMitarbeiter",
	components: {
		BsModal,
		CoreFilterCmpt,
		AbgabeDetail,
		VerticalSplit,
		Dropdown: primevue.dropdown,
		Textarea: primevue.textarea,
		VueDatePicker
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
			saving: false,
			loading: false,
			// TODO: fetch types
			allAbgabeTypes: [
				{
					paabgabetyp_kurzbz: 'abstract',
					bezeichnung: 'Entwurf'
				},
				{
					paabgabetyp_kurzbz: 'zwischen',
					bezeichnung: 'Zwischenabgabe'
				},
				{
					paabgabetyp_kurzbz: 'note',
					bezeichnung: 'Benotung'
				},
				{
					paabgabetyp_kurzbz: 'end',
					bezeichnung: 'Endupload'
				},
				{
					paabgabetyp_kurzbz: 'enda',
					bezeichnung: 'Endabgabe im Sekretariat'
				}
			],
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
				height: 700,
				index: 'projektarbeit_id',
				layout: 'fitDataStretch',
				placeholder: this.$p.t('global/noDataAvailable'),
				selectable: true,
				selectableCheck: this.selectionCheck,
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
					{title: Vue.computed(() => this.$p.t('abgabetool/c4details')), field: 'details', formatter: this.detailFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4personenkennzeichen')), field: 'pkz', formatter: this.pkzTextFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4kontakt')),  field: 'mail', formatter: this.mailFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4vorname')), field: 'vorname', formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4nachname')), field: 'nachname', formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4projekttyp')), field: 'projekttyp_kurzbz', formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4stg')), field: 'stg', formatter: this.centeredTextFormatter, widthGrow: 2},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4sem')), field: 'studiensemester_kurzbz', formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4titel')), field: 'titel', formatter: this.centeredTextFormatter, maxWidth: 500, widthGrow: 8},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4betreuerart')), field: 'betreuerart_beschreibung',formatter: this.centeredTextFormatter, widthGrow: 8}
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
			this.$fhcApi.factory.lehre.postSerientermin(
				this.serienTermin.datum.toISOString(),
				this.serienTermin.bezeichnung.paabgabetyp_kurzbz,
				this.serienTermin.bezeichnung.bezeichnung,
				this.serienTermin.kurzbz,
				this.selectedData?.map(projekt => projekt.projektarbeit_id)
			).then(res => {
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
			this.loadAbgaben(details).then((res)=> {
				const pa = this.projektarbeiten?.retval?.find(projekarbeit => projekarbeit.projektarbeit_id == details.projektarbeit_id)
				pa.abgabetermine = res.data[0].retval
				pa.isCurrent = res.data[1]
				pa.abgabetermine.push({ // new abgatermin row

					'paabgabe_id': -1,
					'projektarbeit_id': pa.projektarbeit_id,
					'fixtermin': false,
					'kurzbz': '',
					'datum': new Date().toISOString().split('T')[0],
					'paabgabetyp_kurzbz': '',
					'bezeichnung': '',
					'abgabedatum': null,
					'insertvon': this.viewData?.uid ?? ''
					
				})
				pa.abgabetermine.forEach(termin => {
					termin.file = []
					termin.allowedToSave = termin.insertvon == this.viewData?.uid && pa.betreuerart_kurzbz != 'Zweitbegutachter'
					termin.allowedToDelete = termin.allowedToSave && !termin.abgabedatum
					
					termin.bezeichnung = {
						bezeichnung: termin.bezeichnung,
						paabgabetyp_kurzbz: termin.paabgabetyp_kurzbz
					}
				})
				pa.betreuer = this.buildBetreuer(pa)
				pa.student_uid = details.student_uid
				pa.student = `${pa.vorname} ${pa.nachname}`
				
				this.selectedProjektarbeit = pa
				
				
				this.$refs.verticalsplit.showBoth()
				
			
			})
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
		buildBetreuer(abgabe) {
			// TODO: preload and insert own titled name of betreuer somehow
			return abgabe.betreuerart_beschreibung + ': ' + (abgabe.btitelpre ? abgabe.btitelpre + ' ' : '') + abgabe.bvorname + ' ' + abgabe.bnachname + (abgabe.btitelpost ? ' ' + abgabe.btitelpost : '')
		},
		setupData(data){
			this.projektarbeiten = data[0]
			this.domain = data[1]
			
			const d = data[0]?.retval?.map(projekt => {
				let mode = 'detailTermine'

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
			this.$fhcApi.factory.lehre.getMitarbeiterProjektarbeiten(this.viewData?.uid ?? null, all)
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
				this.$fhcApi.factory.lehre.getStudentProjektabgaben(details)
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

			this.abgabeTableOptions.height = window.visualViewport.height - rect.top
			this.$refs.abgabeTable.tabulator.setHeight(this.abgabeTableOptions.height)
		},
		async setupMounted() {
			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise

			this.loadProjektarbeiten()


			this.$refs.verticalsplit.collapseBottom()
			this.calcMaxTableHeight()
			
		}
	},
	watch: {

	},
	computed: {

	},
	created() {

	},
	mounted() {
		this.setupMounted()
	},
	template: `
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
						{{$p.t('abgabetool/c4abgabetyp')}}
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
							:options="allAbgabeTypes"
							:optionLabel="getOptionLabelAbgabetyp">
						</Dropdown>
					</div>
					<div class="col-6 d-flex justify-content-center align-items-center">
						<Textarea style="margin-bottom: 4px;" v-model="serienTermin.kurzbz" rows="3" cols="40"></Textarea>
					</div>
				</div>
				
			</template>
			<template v-slot:footer>
				<button type="button" class="btn btn-primary" @click="addSeries">{{ $p.t('global/speichern') }}</button>
			</template>
		</bs-modal>	
		
		<vertical-split ref="verticalsplit">		
			
			<template #top>
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
						
						<div v-show="saving">
							{{ $p.t('abgabetool/currentlySaving') }} <i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
						</div>
						<div v-show="loading">
							{{ $p.t('abgabetool/currentlyLoading') }} <i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
						</div>
						
					</template>
				</core-filter-cmpt>

			</template>
			<template #bottom>
				<div v-show="selectedProjektarbeit" ref="selProj"> 
					<AbgabeDetail :projektarbeit="selectedProjektarbeit"></AbgabeDetail>
				</div>
			</template>
		</vertical-split>

	 
    `,
};

export default AbgabetoolMitarbeiter;
