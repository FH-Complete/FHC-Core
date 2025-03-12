import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeDetail";

export const Abgabetool = {
	name: "Abgabetool",
	components: {
		VueDatePicker,
		CoreFilterCmpt,
		AbgabeDetail
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
			domain: '',
			detail: null,
			selectedProjektarbeit: null,
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			abgabeTableOptions: {
				height: 200, // TODO: determine smallest necessary height
				index: 'projektarbeit_id',
				layout: 'fitColumns',
				placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{title: Vue.computed(() => this.$p.t('abgabetool/c4details')), formatter: this.detailFormatter, field: 'details', widthGrow: 1, tooltip: false},
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
							//demo.dev.technikum-wien.at/cis/private/lehre/projektbeurteilungDocumentExport.php?betreuerart_kurzbz=Begutachter&projektarbeit_id=39239&person_id=22117
							const pdfExportLink = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'cis/private/pdfExport.php?xml=projektarbeitsbeurteilung.xml.php&xsl=Projektbeurteilung&betreuerart_kurzbz='+val.betreuerart_kurzbz+'&projektarbeit_id='+val.projektarbeit_id+'&person_id=' + val.betreuer_person_id
							const pdfExportLink2 = FHC_JS_DATA_STORAGE_OBJECT.app_root + 'cis/private/lehre/projektbeurteilungDocumentExport.php?betreuerart_kurzbz='+val.betreuerart_kurzbz+'&projektarbeit_id='+val.projektarbeit_id+'&person_id=' + val.betreuer_person_id
							window.open(pdfExportLink, '_blank')
						}

					}
					console.log(cell.getData())
					e.stopPropagation()

				}
			}
			]};
	},
	methods: {
		setDetailComponent(details){
			this.loadAbgaben(details).then((res)=> {
				console.log(res)
				const pa = this.data[0]?.retval?.find(projekarbeit => projekarbeit.projektarbeit_id == details.projektarbeit_id)
				pa.abgabetermine = res.data.retval
				pa.betreuer = this.buildBetreuer(pa)
				
				this.selectedProjektarbeit = pa
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
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		buildMailToLink(abgabe) {
			return 'mailto:' + abgabe.mitarbeiter_uid +'@'+ this.domain
		},
		buildBetreuer(abgabe) {
			return abgabe.betreuerart_beschreibung + ': ' + (abgabe.btitelpre ? abgabe.btitelpre + ' ' : '') + abgabe.bvorname + ' ' + abgabe.bnachname + (abgabe.btitelpost ?? '')
		},
		setupData(data){
			
			this.data = data // TODO: better define what is needed from this for detail component
			this.domain = data[1] // TODO do this in backend but this is a prototype anyway
			const d = data[0]?.retval?.map(projekt => {
				console.log('projekt', projekt)
				let mode = 'detailTermine'
				
				if (projekt.babgeschickt || projekt.zweitbetreuer_abgeschickt) {
					mode = 'beurteilungDownload' // build dl link for both betreuer documents
				}
				
				return {
					details: {
						student_uid: this.viewData?.uid,
						projektarbeit_id: projekt.projektarbeit_id,
						betreuer_person_id: projekt.bperson_id,
						betreuerart_kurzbz: projekt.betreuerart_kurzbz,
						mode
					},
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
			this.$fhcApi.factory.lehre.getStudentProjektarbeiten(this.viewData?.uid ?? null)
				.then(res => {
					if(res?.data) this.setupData(res.data)
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
		async setupMounted() {
			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise
			
			this.loadProjektarbeiten()
			
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
	<h2>{{$p.t('abgabetool/abgabetoolTitle')}}</h2>
	<hr>
		
     <core-filter-cmpt 
		:title="''"  
		ref="abgabeTable" 
		:tabulator-options="abgabeTableOptions"  
		:tabulator-events="abgabeTableEventHandlers"
		tableOnly
		:sideMenu="false"
	 />
	 
	 <hr>
	 <div v-show="selectedProjektarbeit"> 
	 	<AbgabeDetail :projektarbeit="selectedProjektarbeit"></AbgabeDetail>
	 </div>
    `,
};

export default Abgabetool;
