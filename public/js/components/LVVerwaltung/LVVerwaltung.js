import CoreSearchbar from "../searchbar/searchbar.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";
import HorizontalSplit from "../horizontalsplit/horizontalsplit.js";
import AppMenu from "../AppMenu.js";
import BaseTreemenu from '../Base/Treemenu.js';
import StvStudiensemester from "../Stv/Studentenverwaltung/Studiensemester.js";
import LvTable from "./Setup/Table.js";
import LvTabs from "./Setup/Tabs.js";
import NavLanguage from "../navigation/Language.js";

import ApiDetails from "../../api/lehrveranstaltung/details.js";
import ApiLektor from "../../api/lehrveranstaltung/lektor.js";
import ApiSearchbar from "../../api/factory/searchbar.js";
import AppConfig from "../AppConfig.js";
import ApiLvConfig from "../../api/lehrveranstaltung/config.js";


export default {
	name: "LVVerwaltung",
	components: {
		CoreSearchbar,
		VerticalSplit,
		HorizontalSplit,
		AppMenu,
		BaseTreemenu,
		StvStudiensemester,
		LvTable,
		LvTabs,
		AppConfig,
		NavLanguage
	},
	props: {
		avatarUrl: String,
		logoutUrl: String,
		defaultSemester: String,
		lvRoot: String,
		permissions: Object,
		config: Object,
	},
	provide() {
		return {
			currentSemester: Vue.computed(() => this.selectedStudiensemester),
			dropdowns: this.dropdowns,
			configShowVertragsdetails: this.config.showVertragsdetails,
			configShowGewichtung: this.config.showGewichtung,
			lehreinheitAnmerkungDefault: (this.config.lehreinheitAnmerkungDefault || '').replace(/\\n/g, '\n'),
			lehreinheitRaumtypDefault: this.config.lehreinheitRaumtypDefault,
			lehreinheitRaumtypAlternativeDefault: this.config.lehreinheitRaumtypAlternativeDefault,
			permissionLehrveranstaltung: this.permissions['lehre/lehrveranstaltung'],
			permissionGruppenEntfernen: this.permissions['lv-plan/gruppenentfernen'],
			permissionLektorEntfernen: this.permissions['lv-plan/lektorentfernen'],
			language: Vue.computed(() => this.$p.user_language),
			isMobile: false,
		}
	},
	data() {
		return {
			sidebarCollapsed: false,
			appconfig:{},
			configEndpoints: ApiLvConfig,
			selected: [],
			studiengang_kz: null,
			selectedStudiensemester: this.defaultSemester,
			dropdowns: {
				studiensemester_array: [],
				sprachen_array: [],
				lehrform_array: [],
				raumtyp_array: [],
			},
			searchbaroptions: {
				origin: 'lvverwaltung',
				cssclass: "position-relative",
				calcheightonly: true,
				types: [
					"mitarbeiter",
					"mitarbeiter_ohne_zuordnung"
				],
				actions: {
					employee: {
						defaultaction: {
							type: "function",
							action: (data) => {
								this.onSelectEmployee(data.uid);
							}
						},
						childactions: [
						]
					},
				}
			},
		}
	},
	computed: {
		filter() {
			let filter = {
				emp: this.$route.params.emp,
				studiensemester_kurzbz: this.selectedStudiensemester
			};
			let index;
			if (this.$route.params.treemenu) {
				index = this.$route.params.treemenu.indexOf('stg');
				if (index > -1)
					filter.stg = this.$route.params.treemenu[index+1];
				index = this.$route.params.treemenu.indexOf('semester');
				if (index > -1)
					filter.semester = this.$route.params.treemenu[index+1];
			}
			filter.activeFilter = filter.emp ? 'employee' : filter.stg ? 'verband' : null;
			return filter;
		},
		emp() {
			return this.filter.emp;
		},
		stg() {
			return this.filter.stg;
		},
		semester() {
			return this.filter.semester;
		},
		appMenuExtraItems()
		{
			let extraItems = [];

			const studiengang_kz = this.studiengang_kz || '';
			const studiensemester = this.selectedStudiensemester;
			const semester = this.semester || '';
			const uid = this.emp || '';


			extraItems.push(
				{
					description: 'lehre/berichte',
					requires: ['studiengang_kz'],
					children: [
						{
							link: FHC_JS_DATA_STORAGE_OBJECT.app_root
								+ 'content/statistik/lvplanung.xls.php'
								+ '?studiengang_kz=' + studiengang_kz
								+ '&studiensemester_kurzbz=' + studiensemester
								+ '&semester=' + semester,
							description: 'lehre/lvplanung',
							requires: ['studiengang_kz']
						},
						{
							link: FHC_JS_DATA_STORAGE_OBJECT.app_root
								+ 'content/statistik/lehrauftragsliste_gst.xls.php'
								+ '?studiengang_kz=' + studiengang_kz
								+ '&studiensemester_kurzbz=' + studiensemester
								+ '&semester=' + semester,
							description: 'lehre/lehrauftragsliste',
							requires: ['studiengang_kz']
						},
						{
							link: FHC_JS_DATA_STORAGE_OBJECT.app_root
								+ 'content/pdfExport.php?xml=lehrauftrag.xml.php'
								+ '&xsl=Lehrauftrag'
								+ '&stg_kz=' + studiengang_kz
								+ '&ss=' + studiensemester,
							description: 'lehre/lehrauftraege',
							requires: ['studiengang_kz']
						},
						{
							link: FHC_JS_DATA_STORAGE_OBJECT.app_root
								+ 'content/pdfExport.php?xml=lehrauftrag.xml.php'
								+ '&xsl=Lehrauftrag'
								+ '&stg_kz=' + studiengang_kz
								+ '&ss=' + studiensemester
								+ '&uid=' + uid,
							description: 'lehre/lehrauftragslisteemp',
							requires: ['emp']
						}
					]
				},
				{
					link: FHC_JS_DATA_STORAGE_OBJECT.app_root
						+ 'vilesci/lehre/lehrveranstaltung.php'
						+ '?stg_kz=' + studiengang_kz,
					description: 'lehre/extrakvverwaltung',
					requires: ['studiengang_kz']
				}
			);

			return extraItems;
		}
	},

	watch: {
		sidebarCollapsed(newVal) {
			if(newVal)
				this.$refs.hSplit.collapseLeft()
			else
				this.$refs.hSplit.showBoth()
		}
	},
	methods: {
		updateFilter()
		{
			// deprecated
		},
		handleRowClicked(data)
		{
			this.selected = data
		},
		onSelectEmployee(emp)
		{
			this.$router.push({
				name: 'emp',
				params: {
					...this.$route.params,
					emp
				}
			});
		},
		onSelectVerband({ path: link, stg_kz })
		{
			this.$router.push({
				name: this.$route.name == 'emp' ? 'emp' : 'treemenu',
				params: {
					...this.$route.params,
					treemenu: link.split('/')
				}
			});
			this.selected = [];
			this.studiengang_kz = stg_kz;
		},
		resetEmployeeFilter()
		{
			let params = { ...this.$route.params };
			
			delete params.emp;
			
			this.$router.replace({
				name: params.treemenu ? 'treemenu' : 'stdsem',
				params
			});
		},
		resetStgFilter()
		{
			let params = { ...this.$route.params };
			let index = params.treemenu.indexOf('stg');
			if (index > -1)
				params.treemenu = params.treemenu.slice(0, index);
			if (params.treemenu.length) {
				this.$router.replace({
					params: {
						treemenu: params.treemenu
					}
				});
			} else {
				delete params.treemenu;
				this.$router.replace({
					name: params.emp ? 'emp' : 'stdsem',
					params
				});
			}
		},
		searchfunction(params, config) {
			return this.$api.call(ApiSearchbar.search(params), config);
		},
		studiensemesterChanged(stdsem) {
			if (!stdsem) {
				// no valid studiensemester in url
				this.$router.replace({
					params: {
						stdsem: this.defaultSemester.toLowerCase()
					}
				});
				return;
			}
			if (stdsem.toLowerCase() != this.selectedStudiensemester?.toLowerCase()) {
				this.$router.push({
					params: {
						stdsem: stdsem.toLowerCase()
					}
				});
			}
			this.selectedStudiensemester = stdsem;
			this.selected = [];
		},
		isDisabled(item)
		{
			if (!item?.requires?.length)
				return false;

			const values = {
				studiengang_kz: this.studiengang_kz,
				stg: this.stg,
				emp: this.emp,
				studiensemester: this.selectedStudiensemester,
				semester: this.semester
			};

			return item.requires.some(req => !values[req]);

		}
	},
	created() {
		if (!this.$route.params.stdsem) {
			this.$router.replace({
				name: 'stdsem',
				params: {
					stdsem: this.defaultSemester.toLowerCase()
				}
			});
		}

		this.$p.loadCategory(['lehre', 'person', 'global'])

		this.$api.call(ApiDetails.getStudiensemester())
			.then(result => {
				this.dropdowns.studiensemester_array = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api.call(ApiDetails.getSprache())
			.then(result => {
				this.dropdowns.sprachen_array = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api.call(ApiDetails.getLehrform())
			.then(result => {
				this.dropdowns.lehrform_array = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api.call(ApiDetails.getRaumtyp())
			.then(result => {
				this.dropdowns.raumtyp_array = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api.call(ApiLektor.getLehrfunktionen())
			.then(result => {
				this.dropdowns.lehrfunktion_array = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: /* html */`
		<div class="stv" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
		<header class="navbar navbar-expand-lg navbar-dark bg-dark flex-md-nowrap p-0 shadow">
			<div class="col-md-4 col-lg-3 col-xl-2 d-flex align-items-center">
				<button
					class="btn btn-outline-light border-0 m-1 collapsed"
					type="button"
					data-bs-toggle="offcanvas"
					data-bs-target="#appMenu"
					aria-controls="appMenu"
					aria-expanded="false"
					:aria-label="$p.t('ui/toggle_nav')"
				>
					<span class="svg-icon svg-icon-apps"></span>
				</button>
				<a class="navbar-brand me-0">LV Verwaltung</a>
			</div>
			<button
				class="btn btn-outline-light border-0 d-md-none m-1 collapsed"
				type="button"
				data-bs-toggle="offcanvas"
				data-bs-target="#sidebarMenu"
				aria-controls="sidebarMenu"
				aria-expanded="false"
				:aria-label="$p.t('ui/toggle_nav')"
			>
				<span class="fa-solid fa-table-list"></span>
			</button>
			<core-searchbar :searchoptions="searchbaroptions" :searchfunction=searchfunction class="searchbar w-100"></core-searchbar>
			<div id="nav-user" class="dropdown">
				<button
					id="nav-user-btn"
					class="btn btn-link rounded-0 py-0"
					type="button"
					data-bs-toggle="dropdown"
					data-bs-target="#nav-user-menu"
					aria-expanded="false"
					aria-controls="nav-user-menu"
				>
					<img
						:src="avatarUrl"
						:alt="$p.t('profilUpdate/profilBild')"
						class="bg-light avatar rounded-circle border border-light"
					/>
				</button>
				<ul
					ref="navUserDropdown"
					class="dropdown-menu dropdown-menu-dark dropdown-menu-end rounded-0 text-center m-0"
					aria-labelledby="nav-user-btn"
				>
					<li>
						<button
							type="button"
							class="dropdown-item"
							data-bs-toggle="modal"
							data-bs-target="#configModal"
						>
							{{ $p.t('ui/settings') }}
						</button>
					</li>
					<li><hr class="dropdown-divider m-0"/></li>
					<li>
						<nav-language
							item-class="dropdown-item border-left-dark"
						/>
					</li>
					<li><hr class="dropdown-divider m-0"/></li>
					<li>
						<a class="dropdown-item" :href="logoutUrl">
							{{ $p.t('ui/logout') }}
						</a>
					</li>
				</ul>
			</div>
		</header>
		<div class="container-fluid overflow-hidden">
			<div class="row h-100">
				<aside id="appMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
					<div class="offcanvas-header">
						LV Verwaltung
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
					</div>
					<div class="offcanvas-body">
						<app-menu app-identifier="lvv">
							<template v-for="(item, key) in appMenuExtraItems" :key="key">
								<li v-if="item.children" class="dropend">
									<a
										class="dropdown-toggle" 
										href="#"
										role="button" 
										data-bs-toggle="dropdown"
										aria-expanded="false"
										data-bs-popper-config='{"strategy":"fixed"}'
									>
										{{ $p.t(item.description) }}
									</a>
									<ul class="dropdown-menu p-0">
										<li
											v-for="(child, childKey) in item.children"
											:key="childKey"
										>
											<a class="dropdown-item" :href="child.link" target="_blank" :class="{ disabled: isDisabled(child) }">
												{{ $p.t(child.description) }}
											</a>
										</li>
									</ul>
								</li>
								<li v-else>
									<a 
										:href="item.link" 
										target="_blank" 
										:class="{ disabled: isDisabled(item) }">
										{{ $p.t(item.description) }}
									</a>
								</li>
							</template>
						</app-menu>
					</div>
				</aside>
				<horizontal-split ref="hSplit" :defaultRatio="[15, 85]">
					<template #left>
						<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100 w-100">
							<div class="offcanvas-header justify-content-end px-1 d-md-none">
								<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
							</div>
							<div class="overflow-auto col h-0">
								<base-treemenu
									config="lvv"
									:preselected-key="$route.params.treemenu"
									@select-entry="onSelectVerband"
								></base-treemenu>
							</div>
							<stv-studiensemester :studiensemester-kurzbz="$route.params.stdsem || defaultSemester" @update:studiensemester-kurzbz="studiensemesterChanged"></stv-studiensemester>
						</nav>
					</template>
					<template #right>
						<main>
							<vertical-split>
								<template #top>
									<lv-table ref="lvTable"
										v-model:selected="selected"
										 @row-clicked="handleRowClicked"
										:filter="filter"
									>
										<template #filterzuruecksetzen v-if="filter.activeFilter === 'employee'">
											<span class="fw-bold small">
											[{{ $p.t('lehre', 'lektor') }}: {{ filter.emp || '' }}
											<button type="button"
													class="btn btn-outline-secondary btn-action btn-sm ms-1"
													:title="$p.t('ui', 'filterdelete')"
													@click="resetEmployeeFilter">
												<i class="fa fa-xmark"></i>
											</button>
											<template v-if="filter.stg">
												| Stg: {{ filter.stg.toUpperCase() }}
												<button type="button"
													class="btn btn-outline-secondary btn-action btn-sm ms-1"
													:title="$p.t('ui', 'filterdelete')"
													@click="resetStgFilter">
													<i class="fa fa-xmark"></i>
												</button>
											</template>
											]
										  </span>
										</template>
									</lv-table>
								</template>
								<template #bottom>
									<lv-tabs ref="details" :lv="selected"></lv-tabs>
								</template>
							</vertical-split>
						</main>
					</template>
				</horizontal-split>
			</div>
		</div>
		<app-config ref="config" v-model="appconfig" :endpoints="configEndpoints"></app-config>
	</div>`
};
