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
			tabulatorUuid: null,
			tableBuiltResolve: null,
			tableBuiltPromise: null,
			mylvTableOptions: {
				height: Vue.ref(400),
				index: 'lehrveranstaltung_id',
				layout: 'fitDataStretch',
				placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{title: Vue.computed(() => this.$capitalize(this.$p.t('lehre/studiengang'))), field: 'sg_bezeichnung', widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('global/bezeichnung'))), field: 'bezeichnung', widthGrow: 2},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('lehre/orgform'))), field: 'orgform_kurzbz', widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('lehre/kurzbz'))), field: 'studiengang_kuerzel', widthGrow: 1},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('lehre/semesterstunden'))), field: 'semesterstunden', 
						bottomCalc: this.semesterstundenCalc, widthGrow: 1, visible: false},
					{title: Vue.computed(() => this.$capitalize(this.$p.t('global/actions'))), headerSort: false,
						field: 'menu', formatter: this.actionFormatter, widthGrow: 1, tooltip: this.spoofingFunc}
				],
				persistence: false,
				persistenceID: "mylv_2026_04_17"
			},
			mylvTableEventHandlers: [
				
			]
		}
	},
	computed: {
		ready() { return this.lvs !== null; },
	},
	methods: {
		semesterstundenCalc(values, data) {
			let sum = 0
			values.forEach(val => {
				sum += Number(val)
			})
			return sum
		},
		spoofingFunc() {
			// intentionally send empty tooltip info so tabulator tooltip doesnt get rendered but hover event propagates
			// to individual button tooltips
			return ''
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
		actionFormatter(cell) {
			let container = document.createElement('div');
			container.className = "d-flex gap-2";

			const data = cell.getData()
			if(data.menu && data.menu.length) {
				
				container.className = "d-flex flex-wrap gap-2"

				data.menu.forEach((lvLink) => {
					// render dropdown if we have a link and some some linklist
					const hasDropdown = (lvLink.c4_moodle_links?.length || lvLink.c4_linkList?.length) && lvLink.c4_link;

					if (hasDropdown) {
						// button group
						const group = document.createElement('div');
						group.className = 'btn-group';

						// main action button
						const button= this.createActionButton(lvLink)

						// toggle button
						const toggle = document.createElement('button');
						toggle.className = 'btn btn-sm dropdown-toggle dropdown-toggle-split border-0';
						toggle.type = 'button';
						toggle.dataset.bsToggle = 'dropdown'; // uses absolute position which gets clipped by tabulator
						toggle.ariaExpanded = 'false';
						toggle.innerHTML = '<span class="visually-hidden">Toggle Dropdown</span>';

						// dropdown menu
						const dropMenu = document.createElement('ul');
						dropMenu.className = 'dropdown-menu dropdown-menu p-0';
						
						// moodle links have priority to be dropdown items but both can be!
						const items = lvLink.c4_moodle_links?.length
							? lvLink.c4_moodle_links.map(item => ({ text: item.lehrform, href: item.url }))
							: lvLink.c4_linkList.map(([text, link]) => ({ text, href: link }));
						
						
						items.forEach(({ text, href }) => {
							const li = document.createElement('li');
							const a = document.createElement('a');
							a.className = 'dropdown-item border-bottom';
							a.href = href;
							a.target = '#';
							a.textContent = text;
							li.appendChild(a);
							dropMenu.appendChild(li);
						});

						group.appendChild(button);
						group.appendChild(toggle);
						group.appendChild(dropMenu);
						container.appendChild(group);
						
					} else {
						container.appendChild(this.createActionButton(lvLink));
					}

				})
				
			}

			return container;
		},
		createActionButton(lvLink){
			const button = document.createElement('a');
			button.className = 'fhc-body text-decoration-none text-truncate';
			if (!lvLink.c4_link) button.classList.add('unavailable');
			button.id = `${lvLink.name}_${lvLink.lehrveranstaltung_id}`;

			const icon = lvLink.c4_icon2 ?? 'fa-solid fa-pen-to-square';
			const label = lvLink.phrase ? this.$p.t(lvLink.phrase) : lvLink.name;
			button.title = label;
			button.innerHTML = `<i class="${icon}"></i><span style="margin-left:2px;">${label}</span>`;

			button.addEventListener('click', (event) => {
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
			return button
		},
		loadState() {
			return JSON.parse(localStorage.getItem(this.mylvTableOptions.persistenceID) || "null");
		},
		saveState(table) {
			// avoid storing state after first restore part happened
			if(!this.stateRestored) return
			const rawLayout = table.getColumnLayout();
			const state = {
				columns: rawLayout.map(col => ({
					field: col.field,
					visible: col.visible,
					width: col.width,
				})),
				sort: table.getSorters().map(s => ({
					field: s.field,
					dir: s.dir,
				})),
				filters: table.getFilters(),
				headerFilters: table.getHeaderFilters()
			};

			localStorage.setItem(this.mylvTableOptions.persistenceID, JSON.stringify(state));
		},
		handleTableBuilt() {
			const table = this.$refs.mylvTable.tabulator

			this.tableBuiltResolve()

			table.on("columnMoved", () => {
				this.saveState(table);
			});

			table.on("columnResized", () => {
				this.saveState(table);
			});

			table.on("columnVisibilityChanged", () => {
				this.saveState(table);
			});

			table.on("filterChanged", () => {
				this.saveState(table);
			});

			table.on("headerFilterChanged", () => {
				this.saveState(table);
			});

			table.on("dataSorted", () => {
				this.saveState(table);
			});

			table.on("columnSorted", () => {
				this.saveState(table);
			});

			table.on("sortersChanged", () => {
				this.saveState(table);
			});

			const saved = this.loadState();

			table.on("renderComplete", () => {
				if(!this.stateRestored) {

					if (saved?.columns && !this.colLayoutRestored) {
						const layout = saved.columns.map(col => ({
							field: col.field,
							width: col.width,
							visible: col.visible,
							// add more if needed, but keep it simple
						}));

						table.setColumnLayout(layout);

						this.colLayoutRestored = true;
					}

					if (saved?.filters && !this.filtersRestored) {
						this.filtersRestored = true // instantly avoid retriggers
						table.setFilter(saved.filters);
					}
					if (saved?.headerFilters && !this.headerFiltersRestored) {
						this.headerFiltersRestored = true // instantly avoid retriggers
						for (let hf of saved.headerFilters) {
							table.setHeaderFilterValue(hf.field, hf.value);
						}
					}

					if (saved?.sort?.length && !this.sortRestored) {
						this.sortRestored = true;

						setTimeout(() => {
							const sortList = saved.sort.map(s => {
								const col = table.columnManager.findColumn(s.field);
								if (!col) {
									return null;
								}
								return { column: col, dir: s.dir };
							}).filter(Boolean);

							table.setSort(sortList);
						}, 100);
					}
					this.stateRestored = true

				}

			});
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

			const h = window.visualViewport.height - rect.top - 50
			if(this.$refs.mylvTable) {
				this.$refs.mylvTable.$refs.table.style.setProperty('height', h+'px')

				// necessary so the wrapping action row resolves to the full rowHeight required
				// without the redraw here actions past the initial rowHeight would be clipped off
				this.$refs.mylvTable.tabulator.redraw(true)
			}

		}
	},
	created() {
		this.phrasenPromise = this.$p.loadCategory(['global', 'lehre', 'lvinfo'])
		this.phrasenPromise.then(()=> {this.phrasenResolved = true})	
	},
	mounted() {
		this.setupMounted()	
	},
	watch: {
		lvs: {
			async handler(newVal) {
				await this.tableBuiltPromise;
				if(!this.$refs.mylvTable?.tabulator) return
				
				this.$refs.mylvTable.tabulator.setData(newVal);

				const tableID = this.tabulatorUuid ? ('-' + this.tabulatorUuid) : ''
				const tableDataSet = document.getElementById('filterTableDataset' + tableID);
				if(!tableDataSet) return
				const rect = tableDataSet.getBoundingClientRect();

				const h = window.visualViewport.height - rect.top - 50
				if(this.$refs.mylvTable) {
					this.$refs.mylvTable.$refs.table.style.setProperty('height', h+'px')
				}
			},
			deep: true
		}
	},
	template: `
	<div class="mylv-semester-table" v-if="ready">
		 <core-filter-cmpt
			v-if="phrasenResolved"
			@uuidDefined="handleUuidDefined"
			:title="''"
			ref="mylvTable"
			:tabulator-options="mylvTableOptions"
			:tabulator-events="mylvTableEventHandlers"
			@tableBuilt="handleTableBuilt"
			tableOnly
			:sideMenu="false"
		 />
	</div>
	<div v-if="tabulatorUuid === null" class="text-center d-flex justify-content-center align-items-center h-100" >
		<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
	</div>
	`
};