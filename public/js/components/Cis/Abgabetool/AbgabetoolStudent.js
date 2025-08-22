import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeStudentDetail.js";
import VerticalSplit from "../../verticalsplit/verticalsplit.js";

export const AbgabetoolStudent = {
	name: "AbgabetoolStudent",
	components: {
		CoreFilterCmpt,
		AbgabeDetail,
		VerticalSplit
	},
	props: {
		student_uid_prop: {
			default: null
		},
		viewData: {
			type: Object,
			required: true,
			default: () => ({uid: ''}),
			validator(value) {
				return value && value.uid
			}
		}
	},
	data() {
		return {
			tabulatorUuid: Vue.ref(0),
			domain: '',
			student_uid: null,
			detail: null,
			projektarbeiten: null,
			selectedProjektarbeit: null,
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			abgabeTableOptions: {
				minHeight: 250,
				index: 'projektarbeit_id',
				layout: 'fitColumns',
				placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{title: Vue.computed(() => this.$p.t('abgabetool/c4details')), field: 'details', formatter: this.detailFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4beurteilung')), field: 'beurteilung', formatter: this.beurteilungFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4sem')), field: 'sem', formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4stg')), field: 'stg', formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4kontakt')), field: 'mail', formatter: this.mailFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4betreuer')), field: 'betreuer', formatter: this.centeredTextFormatter,widthGrow: 2},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4projekttyp')), field: 'typ', formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4titel')), field: 'titel', formatter: this.centeredTextFormatter, widthGrow: 8}
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
						const val = cell.getValue()
						
						if(val.mode === 'detailTermine') {
							this.setDetailComponent(cell.getValue())
						} else if (val.mode === 'beurteilungDownload') {
							const pdfExportLink = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'cis/private/pdfExport.php?xml=projektarbeitsbeurteilung.xml.php&xsl=Projektbeurteilung&betreuerart_kurzbz='+val.betreuerart_kurzbz+'&projektarbeit_id='+val.projektarbeit_id+'&person_id=' + val.betreuer_person_id
							// const pdfExportLink2 = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'cis/private/lehre/projektbeurteilungDocumentExport.php?betreuerart_kurzbz='+val.betreuerart_kurzbz+'&projektarbeit_id='+val.projektarbeit_id+'&person_id=' + val.betreuer_person_id
							window.open(pdfExportLink, '_blank')
						}

					} else if (cell.getColumn().getField() === "beurteilung") {
						const val = cell.getValue()
						
						if(val != '-') window.open(val, '_blank')
					} 
					e.stopPropagation()

				}
			}
			]};
	},
	methods: {
		isPastDate(date) {
			return new Date(date) < new Date(Date.now())	
		},
		setDetailComponent(details){
			this.loadAbgaben(details).then((res)=> {
				const pa = this.projektarbeiten?.retval?.find(projekarbeit => projekarbeit.projektarbeit_id == details.projektarbeit_id)
				pa.abgabetermine = res.data[0].retval
				pa.abgabetermine.forEach(termin => {
					termin.file = []
					termin.allowedToUpload = true
					
					// TODO: fixtermin logic?
					if(termin.bezeichnung == 'Endupload' && this.isPastDate(termin.datum)) {
						
						// termin.allowedToUpload = false
					} else {
						// termin.allowedToUpload = true
					}

				})
				pa.betreuer = this.buildBetreuer(pa)
				pa.student_uid = this.student_uid

				this.selectedProjektarbeit = pa

				
				this.$refs.verticalsplit.showBoth()
				
			})
			
		},
		centeredTextFormatter(cell) {
			const val = cell.getValue()

			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<p style="max-width: 100%; word-wrap: break-word; white-space: normal;">'+val+'</p></div>'
		},
		detailFormatter(cell) {
			const val = cell.getValue()

			if(val.mode === 'detailTermine') {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
					'<a><i class="fa fa-folder-open" style="color:#00649C"></i></a></div>'
			} else if (val.mode === 'beurteilungDownload') {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
					'<a><i class="fa fa-file-pdf" style="color:#00649C"></i></a></div>'
			}
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
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		buildMailToLink(abgabe) {
			return 'mailto:' + abgabe.mitarbeiter_uid +'@'+ this.domain
		},
		buildBetreuer(abgabe) {
			return abgabe.betreuerart_beschreibung + ': ' + (abgabe.btitelpre ? abgabe.btitelpre + ' ' : '') + abgabe.bvorname + ' ' + abgabe.bnachname + (abgabe.btitelpost ? ' ' + abgabe.btitelpost : '')
		},
		setupData(data){
			this.projektarbeiten = data[0]
			this.domain = data[1]
			this.student_uid = data[2]
			const d = data[0]?.retval?.map(projekt => {
				let mode = 'detailTermine'
				
				if (projekt.babgeschickt || projekt.zweitbetreuer_abgeschickt) {
					// mode = 'beurteilungDownload' // build dl link for both betreuer documents
					projekt.beurteilungLink = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'cis/private/pdfExport.php?xml=projektarbeitsbeurteilung.xml.php&xsl=Projektbeurteilung&betreuerart_kurzbz='+projekt.betreuerart_kurzbz+'&projektarbeit_id='+projekt.projektarbeit_id+'&person_id=' + projekt.bperson_id

				}
				
				return {
					details: {
						student_uid: this.student_uid,
						projektarbeit_id: projekt.projektarbeit_id,
						betreuer_person_id: projekt.bperson_id,
						betreuerart_kurzbz: projekt.betreuerart_kurzbz,
						mode
					},
					beurteilung: projekt.beurteilungLink ?? null,
					sem: projekt.studiensemester_kurzbz,
					stg: projekt.kurzbzlang,
					mail: this.buildMailToLink(projekt),
					betreuer: this.buildBetreuer(projekt),
					typ: projekt.projekttypbezeichnung,
					titel: projekt.titel
				}
			})

			this.$refs.abgabeTable.tabulator.setColumns(this.abgabeTableOptions.columns)
			this.$refs.abgabeTable.tabulator.setData(d);
		},
		loadProjektarbeiten() {
			this.$api.call(ApiAbgabe.getStudentProjektarbeiten(this.student_uid_prop || this.viewData?.uid || null))
				.then(res => {
					if(res?.data) this.setupData(res.data)
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

			this.abgabeTableOptions.height = window.visualViewport.height - rect.top
			this.$refs.abgabeTable.tabulator.setHeight(this.abgabeTableOptions.height)
		},
		async setupMounted() {
			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise
			
			this.loadProjektarbeiten()

			this.$refs.verticalsplit.collapseBottom()
			//this.calcMaxTableHeight()
		}
	},
	watch: {

	},
	computed: {
		isViewMode() {
			return this.student_uid !== this.viewData.uid
		}
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
			 	@uuidDefined="handleUuidDefined"
				:title="''"  
				ref="abgabeTable" 
				:tabulator-options="abgabeTableOptions"  
				:tabulator-events="abgabeTableEventHandlers"
				tableOnly
				:sideMenu="false"
			 />
			 
		 </template>
		<template #bottom>
			<div v-show="selectedProjektarbeit"> 
				<AbgabeDetail :viewMode="isViewMode" :projektarbeit="selectedProjektarbeit"></AbgabeDetail>
			 </div>
		</template>
	</vertical-split>
    `,
};

export default AbgabetoolStudent;
