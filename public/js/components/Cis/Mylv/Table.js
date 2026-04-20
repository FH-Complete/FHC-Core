import {CoreFilterCmpt} from "../../../components/filter/Filter.js";

export default {
	name: 'MylvTable',
	components: {
		CoreFilterCmpt
	},
	props: {
		semester: [String],
		lvs: Array,
	},
	data() {
		return {
			phrasenPromise: null,
			phrasenResolved: false,
			tabulatorUuid: Vue.ref(0),
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			mylvTableOptions: {
				height: Vue.ref(400),
				index: 'lehrveranstaltung_id',
				layout: 'fitDataStretch',
				placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{title: Vue.computed(() => this.$p.t('lehre/studiengang')), field: 'sg_bezeichnung', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('global/bezeichnung')), field: 'bezeichnung', widthGrow: 2},
					{title: Vue.computed(() => this.$p.t('lehre/orgform')), field: 'orgform_kurzbz', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('admission/stg_kurz')), field: 'studiengang_kuerzel', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('global/actions')),
						field: 'menu', formatter: this.actionFormatter, widthGrow: 1}
				],
				persistence: false,
				persistenceID: "mylv_2026_04_17"
			},
			mylvTableEventHandlers: [
				{
					event: "tableBuilt",
					handler: async () => {
						this.tableBuiltResolve()
					}
				}
			]
		}
	},
	computed: {
		ready() { return this.lvs !== null; },
		
	},
	methods: {
		handleUuidDefined(uuid) {
			this.tabulatorUuid = uuid
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		actionFormatter() {
			return ''	
		},
		async setupData() {
			this.$refs.mylvTable.tabulator.setData(this.lvs);
		},
		async setupMounted() {

			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise

			this.setupData()
			
			const tableID = this.tabulatorUuid ? ('-' + this.tabulatorUuid) : ''
			const tableDataSet = document.getElementById('filterTableDataset' + tableID);
			if(!tableDataSet) return
			const rect = tableDataSet.getBoundingClientRect();

			const h = window.visualViewport.height - rect.top - 100
			if(this.$refs.mylvTable) {
				this.$refs.mylvTable.$refs.table.style.setProperty('height', h+'px')
			}

		}
	},
	created() {
		this.phrasenPromise = this.$p.loadCategory(['global'])
		this.phrasenPromise.then(()=> {this.phrasenResolved = true})	
	},
	mounted() {
		this.setupMounted()	
	},
	template: `
	<div class="mylv-semester" v-if="ready">
		 <core-filter-cmpt
			v-if="phrasenResolved"
			@uuidDefined="handleUuidDefined"
			:title="''"
			ref="mylvTable"
			:tabulator-options="mylvTableOptions"
			:tabulator-events="mylvTableEventHandlers"
			tableOnly
			:sideMenu="false"
		 />
	</div>`
};