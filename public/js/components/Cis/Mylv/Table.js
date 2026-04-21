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
				layout: 'fitData',
				placeholder: this.$p.t('global/noDataAvailable'),
				columns: [
					{title: Vue.computed(() => this.$p.t('lehre/studiengang')), field: 'sg_bezeichnung', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('global/bezeichnung')), field: 'bezeichnung', widthGrow: 2},
					{title: Vue.computed(() => this.$p.t('lehre/orgform')), field: 'orgform_kurzbz', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('lehre/kurzbz')), field: 'studiengang_kuerzel', widthGrow: 1},
					{title: Vue.computed(() => this.$p.t('lehre/semesterstunden')), field: 'semesterstunden', 
						bottomCalc: this.semesterstundenCalc, widthGrow: 1, visible: true},
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
					// render dropdown if we have a link and some some linklist
					const hasDropdown = (lvLink.c4_moodle_links?.length || lvLink.c4_linkList?.length) && lvLink.c4_link;

					if (hasDropdown) {
						// button group
						const group = document.createElement('div');
						group.className = 'btn-group';

						// main action button
						const button = document.createElement('a');
						button.className = 'fhc-body text-decoration-none text-truncate';
						if (!lvLink.c4_link) button.classList.add('unavailable');
						button.id = lvLink.name;

						const icon = lvLink.c4_icon2 ?? 'fa-solid fa-pen-to-square';
						const label = lvLink.phrase ? this.$p.t(lvLink.phrase) : lvLink.name;
						button.title = label;
						button.innerHTML = `<i class="${icon}"></i><span style="margin-left:2px;">${abbreviate(label)}</span>`;

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
						dropMenu.style.zIndex = 9999 // over 9000
						dropMenu.style.position = 'fixed'
						
						// moodle links have priority to be dropdown items but both can be!
						const items = lvLink.c4_moodle_links?.length
							? lvLink.c4_moodle_links.map(item => ({ text: item.lehrform, href: item.url }))
							: lvLink.c4_linkList.map(([text, link]) => ({ text, href: link }));

						for(let i = 0; i < 10; i++) {
							items.push({text: 'puffer', href: 'www.google.com'})
						}
						
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
						// action button only
						const button = document.createElement('a');
						button.className = 'fhc-body text-decoration-none text-truncate';
						if (!lvLink.c4_link) button.classList.add('unavailable');
						button.id = lvLink.name;

						const icon = lvLink.c4_icon2 ?? 'fa-solid fa-pen-to-square';
						const label = lvLink.phrase ? this.$p.t(lvLink.phrase) : lvLink.name;
						button.title = label;
						button.innerHTML = `<i class="${icon}"></i><span style="margin-left:2px;">${abbreviate(label)}</span>`;

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

						container.appendChild(button);
					}

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
		this.phrasenPromise = this.$p.loadCategory(['global', 'lehre', 'lvinfo'])
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
	<div class="mylv-semester-table" v-if="ready">
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