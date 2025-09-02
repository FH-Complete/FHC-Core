import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import AbgabeDetail from "./AbgabeStudentDetail.js";
import VerticalSplit from "../../verticalsplit/verticalsplit.js";
import ApiAbgabe from '../../../api/factory/abgabe.js'
import BsModal from "../../Bootstrap/Modal.js";

export const AbgabetoolStudent = {
	name: "AbgabetoolStudent",
	components: {
		BsModal,
		CoreFilterCmpt,
		AbgabeDetail,
		VerticalSplit
	},
	provide() {
		return {
			notenOptions: Vue.computed(() => this.notenOptions)
		}
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
			notenOptions: null,
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
				responsiveLayout: "collapse",
				placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{
						formatter:"responsiveCollapse",
						width:30, minWidth:30, hozAlign:"center", resizable:false, headerSort:false
					},
					{
						title: Vue.computed(() => this.$p.t('abgabetool/c4details')), field: 'details',
						formatter: this.detailFormatter,
						widthGrow: 1, tooltip: false
						, responsive: 0, minWidth: 80
					},
					{
						title: Vue.computed(() => this.$p.t('abgabetool/c4beurteilung')), field: 'beurteilung',
						formatter: this.beurteilungFormatter,
						widthGrow: 1, tooltip: false
						, responsive: 0, minWidth: 80
					},
					{
						title: Vue.computed(() => this.$p.t('abgabetool/c4sem')), field: 'sem',
						formatter: this.centeredTextFormatter,
						widthGrow: 1
						, responsive: 5, minWidth: 120
					},
					{
						title: Vue.computed(() => this.$p.t('abgabetool/c4stg')), field: 'stg',
						formatter: this.centeredTextFormatter,
						widthGrow: 1
						, responsive: 6, minWidth: 120
					},
					{
						title: Vue.computed(() => this.$p.t('abgabetool/c4kontakt')), field: 'mail',
						formatter: this.mailFormatter,
						widthGrow: 1
						, responsive: 0, minWidth: 80
					},
					{
						title: Vue.computed(() => this.$p.t('abgabetool/c4betreuer')), field: 'betreuer',
						formatter: this.centeredTextFormatter,
						widthGrow: 2
						, responsive: 7, minWidth: 300
					},
					{
						title: Vue.computed(() => this.$p.t('abgabetool/c4projekttyp')), field: 'typ',
						formatter: this.centeredTextFormatter,
						widthGrow: 1
						, responsive: 8, minWidth: 200
					},
					{
						title: Vue.computed(() => this.$p.t('abgabetool/c4titel')), field: 'titel', 
						formatter: this.centeredTextFormatter,
						widthGrow: 8
						, responsive: 1, minWidth: 420
					}
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
		checkQualityGates(termine) {
			let qgate1Passed = false
			let qgate2Passed = false
			
			termine.forEach(t => {
				const noteOption = this.notenOptions.find(opt => opt.note == t.note)
				if(noteOption && noteOption.positiv) {
					if(t.paabgabetyp_kurzbz == 'qualgate1') {
						qgate1Passed = true
					} else if(t.paabgabetyp_kurzbz == 'qualgate2') {
						qgate2Passed = true
					}
				}
			})

			return qgate1Passed && qgate2Passed
		},
		isPastDate(date) {
			return new Date(date) < new Date(Date.now())	
		},
		setDetailComponent(details){
			this.loadAbgaben(details).then((res)=> {
				const pa = this.projektarbeiten?.find(projekarbeit => projekarbeit.projektarbeit_id == details.projektarbeit_id)
				pa.abgabetermine = res.data[0].retval
				pa.abgabetermine.forEach(termin => {
					termin.file = []
					termin.allowedToUpload = false
					// termin.datum = '2025-10-16'
					// TODO: fixtermin logic?
					if(termin.paabgabetyp_kurzbz == 'enda') {
						
						termin.allowedToUpload = !this.isPastDate(termin.datum) && this.checkQualityGates(pa.abgabetermine)
					} else if(termin.paabgabetyp_kurzbz == 'qualgate1' || termin.paabgabetyp_kurzbz == 'qualgate2') {
						termin.allowedToUpload = termin.upload_allowed
					} else {
						termin.allowedToUpload = true
					}

				})
				pa.betreuer = this.buildBetreuer(pa)
				pa.student_uid = this.student_uid

				this.selectedProjektarbeit = pa

				this.$refs.modalContainerAbgabeDetail.show()
				// this.$refs.verticalsplit.showBoth()
				
			})
			
		},
		centeredTextFormatter(cell) {
			const val = cell.getValue()

			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%;">' +
				'<p style="max-width: 100%; word-wrap: break-word; white-space: normal;">'+val+'</p></div>'
		},
		detailFormatter(cell) {
			const val = cell.getValue()

			if(val.mode === 'detailTermine') {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%;">' +
					'<a><i class="fa fa-folder-open" style="color:#00649C"></i></a></div>'
			} else if (val.mode === 'beurteilungDownload') {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%;">' +
					'<a><i class="fa fa-file-pdf" style="color:#00649C"></i></a></div>'
			}
		},
		mailFormatter(cell) {
			const val = cell.getValue()
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%;">' +
					'<a href='+val+'><i class="fa fa-envelope" style="color:#00649C"></i></a></div>'
		},
		beurteilungFormatter(cell) {
			const val = cell.getValue()
			if(val) {
				return '<div style="display: flex; justify-content: center; align-items: center; height: 100%;">' +
					'<a><i class="fa fa-file-pdf" style="color:#00649C"></i></a></div>'
			} else return '-'
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		buildMailToLink(projekt) {
			if(projekt.mitarbeiter_uid) { // standard
				return 'mailto:' + projekt.mitarbeiter_uid +'@'+ this.domain
			} else { // private
				return 'mailto:' + projekt.email
			}
		},
		buildBetreuer(abgabe) {
			return abgabe.betreuerart_beschreibung + ': ' + (abgabe.btitelpre ? abgabe.btitelpre + ' ' : '') + abgabe.bvorname + ' ' + abgabe.bnachname + (abgabe.btitelpost ? ' ' + abgabe.btitelpost : '')
		},
		setupData(data){
			this.projektarbeiten = data[0]
			this.domain = data[1]
			this.student_uid = data[2]
			const d = data[0]?.map(projekt => {
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

			Vue.nextTick(()=>{
				this.$refs.abgabeTable?.tabulator.setColumns(this.$refs.abgabeTable?.tabulator.getColumnDefinitions())

			})
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

			this.abgabeTableOptions.height = window.visualViewport.height - rect.top - 100
			this.$refs.abgabeTable.tabulator.setHeight(this.abgabeTableOptions.height)
		},
		async setupMounted() {
			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise
			
			this.loadProjektarbeiten()
			

			// window.addEventListener("resize", this.redrawHandler);
			// this.$refs.verticalsplit.collapseBottom()
		},
		redrawHandler() {
			console.log('redrawHandler')
			this.$refs.abgabeTable?.tabulator.setColumns(this.$refs.abgabeTable?.tabulator.getColumnDefinitions())
			this.$refs.abgabeTable?.tabulator.redraw(true)
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
		//TODO: SWITCH TO NOTEN API ONCE NOTENTOOL IS IN MASTER TO AVOID DUPLICATE API
		this.$api.call(ApiAbgabe.getNoten()).then(res => {
			this.notenOptions = res.data
		}).catch(e => {
			this.loading = false
		})

	},
	mounted() {
		this.setupMounted()
	},
	unmounted() {
		// window.removeEventListener("resize", this.redrawHandler);
	},
	template: `
	
	<bs-modal ref="modalContainerAbgabeDetail" class="bootstrap-prompt"
		dialogClass="modal-fullscreen">
		<template v-slot:title>
			<div>
				abgabe detail wtb phrasen
			</div>
		</template>
		<template v-slot:default>
			<AbgabeDetail :projektarbeit="selectedProjektarbeit"></AbgabeDetail>
			
		</template>
	</bs-modal>
	
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
    `,
};

export default AbgabetoolStudent;
