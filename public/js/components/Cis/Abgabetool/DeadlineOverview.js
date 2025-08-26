import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import ApiAbgabe from '../../../api/factory/abgabe.js'

export const DeadlineOverview = {
	name: "DeadlineOverview",
	components: {
		CoreFilterCmpt,
	},
	props: {
		person_uid_prop: {
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
			fullName: null, // TODO: fetch this somewhere
			deadlines: null,
			tabulatorUuid: Vue.ref(0),
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			deadlineTableOptions: {
				height: 700,
				index: 'projektarbeit_id',
				layout: 'fitColumns',
				placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{title: Vue.computed(() => this.$p.t('abgabetool/c4zieldatum')), field: 'datum', formatter: this.centeredTextFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4fixtermin')), field: 'fixterminstring', formatter: this.centeredTextFormatter, widthGrow: 1, tooltip: false},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4abgabetyp')), field: 'typ_bezeichnung', formatter: this.centeredTextFormatter, widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4abgabekurzbz')), field: 'kurzbz', formatter: this.centeredTextFormatter, widthGrow: 3},
					{title: Vue.computed(() => this.$p.t('person/studentIn')), field: 'student', formatter: this.centeredTextFormatter, widthGrow: 2},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4stg')), field: 'stg', formatter: this.centeredTextFormatter,widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('abgabetool/c4sem')), field: 'semester', formatter: this.centeredTextFormatter, widthGrow: 1}
				],
				persistence: false,
			},
			deadlineTableEventHandlers: [{
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
		centeredTextFormatter(cell) {
			const val = cell.getValue()

			return '<div style="display: flex; justify-content: center; align-items: center; height: 100%">' +
				'<p style="max-width: 100%; word-wrap: break-word; white-space: normal;">'+val+'</p></div>'
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		loadDeadlines() {
			this.$api.call(ApiAbgabe.fetchDeadlines(this.person_uid_prop ??  null))
				.then(res => {
					if(res?.data) this.setupData(res.data)
				})
		},
		setupData(data) {
			this.deadlines = data
			
			this.deadlines.forEach(dl => {
				dl.student = (dl.stud_titelpre ? (dl.stud_titelpre + ' ') :'') + dl.stud_vorname + ' ' + dl.stud_nachname + (dl.stud_titelpost ? (' ' + dl.stud_titelpost) :'')
				dl.fixterminstring = dl.fixtermin ? this.$p.t('abgabetool/c4yes') : this.$p.t('abgabetool/c4no')
			})

			this.$refs.deadlineTable.tabulator.setColumns(this.deadlineTableOptions.columns)
			this.$refs.deadlineTable.tabulator.setData(this.deadlines);
		},
		handleUuidDefined(uuid) {
			this.tabulatorUuid = uuid
		},
		calcMaxTableHeight() {
			const tableID = this.tabulatorUuid ? ('-' + this.tabulatorUuid) : ''
			const tableDataSet = document.getElementById('filterTableDataset' + tableID);
			if(!tableDataSet) return
			const rect = tableDataSet.getBoundingClientRect();

			this.deadlineTableOptions.height = window.visualViewport.height - rect.top
			this.$refs.deadlineTable.tabulator.setHeight(this.deadlineTableOptions.height)
		},
		async setupMounted() {
			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise

			this.loadDeadlines()
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
		<h2>{{$p.t('abgabetool/deadlinesTitle')}} {{ fullName ? ('-' + fullName) : ''}}</h2>
		<hr>
			
		 <core-filter-cmpt
			@uuidDefined="handleUuidDefined"
			:title="''"  
			ref="deadlineTable" 
			:tabulator-options="deadlineTableOptions"  
			:tabulator-events="deadlineTableEventHandlers"
			tableOnly
			:sideMenu="false"
		 />
    `,
};

export default DeadlineOverview;
