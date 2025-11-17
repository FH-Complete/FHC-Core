import CoreSearchbar from "../searchbar/searchbar.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";
import AppMenu from "../AppMenu.js";
import StvVerband from "../Stv/Studentenverwaltung/Verband.js";
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
		StvVerband,
		StvStudiensemester,
		LvTable,
		LvTabs,
	},
	props: {
		defaultSemester: String,
		lvRoot: String,
		permissions: Object,
		config: Object,
		stg: { type: String, required: false },
		semester: { type: [Number, String], required: false, default: null },
		studiensemester_kurzbz: { type: String, required: false, default: null },
		emp: { type: String, required: false, default: null }
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
	mounted() {
		this.updateFilter();
	},
	watch: {
		stg() {
			this.updateFilter();
		},
		semester() {
			this.updateFilter();
		},
		selectedStudiensemester() {
			this.updateFilter();
		},
		emp() {
			this.updateFilter();
		},
		studiensemester_kurzbz(newVal) {
			this.selectedStudiensemester = newVal ?? this.defaultSemester;
		}
	},
	data() {
		return {
			selected: [],
			studiengang: "",
			filter: {},
			selectedStudiensemester: this.studiensemester_kurzbz ?? this.defaultSemester,
			endpoint: ApiStudiengangTree,
			dropdowns: {
				studiensemester_array: [],
				sprachen_array: [],
				lehrform_array: [],
				raumtyp_array: [],
			},
			selectedStudiengang: '',
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
	methods: {
		updateFilter()
		{
			const filter = {
				stg: this.stg,
				emp: this.emp,
				semester: this.semester,
				studiensemester_kurzbz: this.selectedStudiensemester,
				activeFilter: this.emp ? 'employee' : this.stg ? 'verband' : null
			}

			if (this.stg !== undefined)
			{
				this.selectedStudiengang = this.semester !== '' && this.semester
					? `${this.stg}/${this.semester}`
					: this.stg;
			}
			this.filter = filter;
		},
		handleRowClicked(data)
		{
			this.selected = data
		},
		onSelectEmployee(emp)
		{
			const { stg, semester } = this.filter;

			let studiensemester_kurzbz = this.selectedStudiensemester;
			const params = { emp };

			if (stg)
				params.stg = stg;
			if (semester !== null)
				params.semester = semester;
			if (studiensemester_kurzbz)
				params.studiensemester_kurzbz = studiensemester_kurzbz;

			this.$router.push({ name: 'byEmp', params })
		},

		onSelectVerband({link})
		{
			let stg = null;
			let semester = null;
			let studiensemester_kurzbz = this.selectedStudiensemester;

			if (typeof link === 'number')
				stg = link;
			else if (typeof link === 'string')
			{
				[stg, semester] = link.split('/');
			}

			const routeName = this.filter.emp ? 'byEmp' : 'byStg';
			const params = { stg };

			if (semester !== null)
				params.semester = semester;
			if (studiensemester_kurzbz)
				params.studiensemester_kurzbz = studiensemester_kurzbz;
			if (this.filter.emp)
				params.emp = this.filter.emp;
			this.$router.push({ name: routeName, params });
			this.selected = [];
		},
		resetEmployeeFilter()
		{
			const newParams = { ...this.filter, activeFilter: 'verband' };
			if (newParams.stg === '')
				this.$router.replace({ name: 'index' });
			else
			{
				delete newParams.emp;

				this.$router.replace({ name: 'byStg', params: newParams });
			}
		},
		resetStgFilter()
		{
			const newParams = { ...this.filter, activeFilter: 'emp' };
			delete newParams.stg;
			this.selectedStudiengang = '';
			this.$router.replace({ name: 'byEmp', params: newParams });
		},
		searchfunction(params) {
			return this.$api.call(ApiSearchbar.search(params));
		},
		studiensemesterChanged(newValue) {
			const routeName = this.filter.activeFilter === 'employee' ? 'byEmp' : 'byStg';
			const newParams = {...this.filter, studiensemester_kurzbz: newValue};
			this.$router.push({ name: routeName, params: newParams });
			this.selected = [];
		},
	},
	created() {
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
					<stv-verband :preselectedKey="selectedStudiengang" :endpoint="endpoint" @select-verband="onSelectVerband" class="col" style="height:0%"></stv-verband>
					<stv-studiensemester v-model:studiensemester-kurzbz="selectedStudiensemester" @update:studiensemester-kurzbz="studiensemesterChanged"></stv-studiensemester>
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
										| Stg: {{ filter.stg }}
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
