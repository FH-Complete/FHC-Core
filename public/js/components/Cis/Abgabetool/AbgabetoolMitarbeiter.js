import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeMitarbeiterDetail.js";
import VerticalSplit from "../../verticalsplit/verticalsplit.js"

export const AbgabetoolMitarbeiter = {
	name: "AbgabetoolMitarbeiter",
	components: {
		CoreFilterCmpt,
		AbgabeDetail,
		VerticalSplit,
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
					{title: Vue.computed(() => this.$p.t('abgabetool/c4beteuerart')), field: 'betreuerart_beschreibung',formatter: this.centeredTextFormatter, widthGrow: 8}
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
					}
					e.stopPropagation()

				}
			},
			{
				event: "rowClick",
				handler: async (e, row) => {

					e.stopPropagation()

				}
			},
			{
				event: "rowSelected",
				handler: async (row) => {


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
		showDeadlines(){
			// TODO: open seperate view in new window containing all future deadlines for the employee
		},
		toggleShowAll(showall) {
			this.showAll = showall
			// TODO: debug tabulator row render
			this.loadProjektarbeiten(showall, () => { this.$refs.abgabeTable?.tabulator.redraw(true) })
		},
		addSeries() {
			
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


		
		<vertical-split ref="verticalsplit">		
			
			<template #top>
				<h2>{{$p.t('abgabetool/abgabetoolTitle')}}</h2>
				<hr>
				<core-filter-cmpt 
					:title="''"  
					@uuidDefined="handleUuidDefined"
					ref="abgabeTable"
					:newBtnShow="true"
					:newBtnLabel="$p.t('global/neueTerminserie')"
					:newBtnDisabled="!selectedData.length"
					@click:new=addSeries
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
