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
					{title: Vue.computed(() => this.$p.t('lehre/kurzbz')), field: 'studiengang_kuerzel', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('global/actions')), headerSort: false,
						field: 'menu', formatter: this.actionFormatter, widthGrow: 1, tooltip: this.spoofingFunc}
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
		spoofingFunc() {
			// intentionally send empty tooltip info so tabulator tooltip doesnt get rendered but hover event propagates
			// to individual button tooltips
			return ''
		},
		c4_target(menuItem) {
			if (menuItem.c4_moodle_links?.length > 0) return null;
			return menuItem.c4_target ?? null;
		},
		c4_link(menuItem) {
			if (!menuItem) return null;
			if (Array.isArray(menuItem.c4_moodle_links) && menuItem.c4_moodle_links.length) {
				return '#';
			} else {
				return menuItem.c4_link ?? null;
			}
		},
		handleUuidDefined(uuid) {
			this.tabulatorUuid = uuid
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		actionFormatter(cell, formatterParams, onRendered) {
			let container = document.createElement('div');
			container.className = "d-flex gap-2";

			const data = cell.getData()
			console.log(data)
			if(data.menu && data.menu.length) {

				const calculatedMinWidth = data.menu.length * 120;
				container.style.minWidth = `${calculatedMinWidth}px`;
				const abbreviate = (str, limit = 12) =>
					str.length > limit + 3 ? `${str.slice(0, limit)}...` : str;
				
				data.menu.forEach((lvLink) => {
					let button = document.createElement('button');
					button.className = 'btn btn-outline-secondary btn-action';
					if (!lvLink.c4_link) {
						button.classList.add('unavailable');
					}
					const icon = lvLink.c4_icon2 ?? 'fa-solid fa-pen-to-square'
					button.id = lvLink.name
					button.innerHTML =  '<i class="'+icon+'"></i>';
					
					button.title = lvLink.phrase ? this.$p.t(lvLink.phrase) : lvLink.name;
					button.innerHTML += '<span style="margin-left: 2px;">'+abbreviate(button.title)+'</span>';
					button.addEventListener('click', (event) => {
						// replicate a tag here
						event.preventDefault();
						const url = this.c4_link(lvLink);
						if (url) {
							const target = lvLink.c4_target || '_blank';

							if (target === '_blank') {
								window.open(url, '_blank', 'noopener,noreferrer');
							} else {
								window.location.href = url;
							}
						} else {
							console.warn("Link is unavailable for:", lvLink.name);
						}
					});
					container.append(button);
				})
				
			}

			return container;
		},
		async setupData() {
			this.$refs.mylvTable.tabulator.setData(this.lvs);
		},
		async setupMounted() {
			// console.log('mounted pre table promise')
			this.tableBuiltPromise = new Promise(this.tableResolve)
			await this.tableBuiltPromise

			console.log('mounted post table promise')
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
	watch: {
		lvs: {
			handler(newVal, oldVal) {
				console.log('watcher')
				if(this.$refs.mylvTable) {
					console.log('watcher inside if ref table clause')
					this.$refs.mylvTable.tabulator.setData(newVal);
				}
			},
			deep: true
		}
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