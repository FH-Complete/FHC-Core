import CoreSearchbar from "../searchbar/searchbar.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";
import AppMenu from "../AppMenu.js";
import BaseTreemenu from '../Base/Treemenu.js';
import StvStudiensemester from "../Stv/Studentenverwaltung/Studiensemester.js";
import LvTable from "./Setup/Table.js";
import LvTabs from "./Setup/Tabs.js";

import ApiDetails from "../../api/lehrveranstaltung/details.js";
import ApiLektor from "../../api/lehrveranstaltung/lektor.js";
import ApiGruppe from "../../api/lehrveranstaltung/gruppe.js";
import ApiStudiengangTree from "../../api/lehrveranstaltung/studiengangtree.js";
import ApiSearchbar from "../../api/factory/searchbar.js";


export default {
	name: "LVVerwaltung",
	components: {
		CoreSearchbar,
		VerticalSplit,
		AppMenu,
		BaseTreemenu,
		StvStudiensemester,
		LvTable,
		LvTabs,
	},
	props: {
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
		}
	},
	data() {
		return {
			selected: [],
			studiengang: "",
			selectedStudiensemester: this.defaultSemester,
			endpoint: ApiStudiengangTree,
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
			}

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
		onSelectVerband({ path: link })
		{
			this.$router.push({
				name: this.$route.name == 'emp' ? 'emp' : 'treemenu',
				params: {
					...this.$route.params,
					treemenu: link.split('/')
				}
			});
			this.selected = [];
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
	<div class="stv">
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
		</header>
		<div class="container-fluid overflow-hidden">
			<div class="row h-100">
				<aside id="appMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
					<div class="offcanvas-header">
						LV Verwaltung
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
					</div>
					<div class="offcanvas-body">
						<app-menu app-identifier="lvv" />
					</div>
				</aside>
				<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
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
				
				<main class="col-md-8 ms-sm-auto col-lg-9 col-xl-10">
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
			</div>
		</div>
	</div>`
};
