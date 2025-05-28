import CoreSearchbar from "../searchbar/searchbar.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";
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
		StvVerband,
		StvStudiensemester,
		LvTable,
		LvTabs,
	},
	props: {
		defaultSemester: String,
		lvRoot: String,
		permissions: Object,
		config: Object
	},
	computed: {
		type(){
			return this.$route.params.type
		}
	},
	provide() {
		return {
			currentSemester: Vue.computed(() => this.studiensemesterKurzbz),
			dropdowns: this.dropdowns,
			configShowVertragsdetails: this.config.showVertragsdetails,
			configShowGewichtung: this.config.showGewichtung,
			lehreinheitAnmerkungDefault: this.config.lehreinheitAnmerkungDefault,
			lehreinheitRaumtypDefault: this.config.lehreinheitRaumtypDefault,
			lehreinheitRaumtypAlternativeDefault: this.config.lehreinheitRaumtypAlternativeDefault,

			permissionLehrveranstaltung: this.permissions['lehre/lehrveranstaltung'],
			permissionGruppenEntfernen: this.permissions['lv-plan/gruppenentfernen'],
			permissionLektorEntfernen: this.permissions['lv-plan/lektorentfernen'],
		}
	},
	mounted() {
		this.updateFilter(this.$route);
	},
	watch: {
		'$route'(to) {
			this.updateFilter(to);
		},
	},
	data() {
		return {
			selected: [],
			studiensemesterKurzbz: this.defaultSemester,
			stg: "",
			filter: {},
			endpoint: ApiStudiengangTree,
			dropdowns: {
				studiensemester_array: [],
				sprachen_array: [],
				lehrform_array: [],
				raumtyp_array: [],
				lektor_array: [],
				gruppen_array: [],
				benutzer_array: [],
			},
			selectedStudiengang: '',
			searchbaroptions: {
				cssclass: "position-relative",
				calcheightonly: true,
				types: [
					"mitarbeiter"
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
		updateFilter(route)
		{
			let filter = { ...route.params, ...route.query };
			if (!filter.activeFilter)
			{
				if (filter.emp)
				{
					filter.activeFilter = 'employee';
				}
				else if (filter.stg)
				{
					filter.activeFilter = 'verband';
				}
			}
			this.filter = filter;
		},
		handleRowClicked(data)
		{
			this.selected = data
		},
		onSelectEmployee(emp)
		{
			let stg = this.stg === '' ? null : this.stg;

			this.$router.push({
				name: 'byEmp',
				params: { emp, stg, activeFilter: 'employee'},
			});
		},

		onSelectVerband({link})
		{
			let stg = null;
			let semester = null;

			if (typeof link === 'number')
				stg = link;
			else if (typeof link === 'string')
			{
				[stg, semester] = link.split('/');
			}

			this.stg = stg;

			if (this.filter && this.filter.emp)
			{
				this.$router.push({
					name: 'byEmp',
					params: { emp: this.filter.emp, stg, activeFilter: 'employee' },
				});
			}
			else
			{
				this.$router.push({
					name: 'byStg',
					params: { stg, semester, activeFilter: 'verband' },
				});
			}

			this.selected = [];
		},
		resetEmployeeFilter()
		{
			const newParams = { ...this.$route.params, activeFilter: 'verband' };
			if (newParams.stg === '')
				this.$router.replace({ name: 'index' });
			else
			{
				delete newParams.emp;
				newParams.semester = null;
				this.$router.replace({ name: 'byStg', params: newParams });

			}
		},
		searchfunction(params) {
			return this.$api.call(ApiSearchbar.search(params));
		},
		studiensemesterChanged(newValue) {
			this.studiensemesterKurzbz = newValue;
			this.$refs.lvTable.reload();
			this.selected = [];
		},
	},
	created() {
		if (this.$route.params.stg !== undefined)
		{
			this.selectedStudiengang = this.$route.params?.semester !== '' && this.$route.params?.semester
				? `${this.$route.params.stg}/${this.$route.params.semester}`
				: this.$route.params.stg;
		}

		this.$p.loadCategory(['lehre', 'person'])

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

		this.$api.call(ApiLektor.getLektoren())
			.then(result => {
				this.dropdowns.lektor_array = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api.call(ApiGruppe.getAll())
			.then(result => {
				this.dropdowns.gruppen_array = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api.call(ApiGruppe.getBenutzer())
			.then(result => {
				this.dropdowns.benutzer_array = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},

	template: `
	<div class="stv">
		<header class="navbar navbar-expand-lg navbar-dark bg-dark flex-md-nowrap p-0 shadow">
			<a class="navbar-brand col-md-4 col-lg-3 col-xl-2 me-0 px-3">LV Verwaltung</a>
			<core-searchbar :searchoptions="searchbaroptions" :searchfunction=searchfunction class="searchbar w-100"></core-searchbar>
		</header>
		<div class="container-fluid overflow-hidden">
			<div class="row h-100">
				<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
					<div class="offcanvas-header justify-content-end px-1 d-md-none">
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
					</div>
					<stv-verband @select-verband="onSelectVerband" class="col" style="height:0%" :preselectedKey="selectedStudiengang" :endpoint="endpoint"></stv-verband>
					<stv-studiensemester :default="defaultSemester" @changed="studiensemesterChanged"></stv-studiensemester>
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
									<button type="button" 
										class="btn btn-outline-secondary btn-action"
										title="Mitarbeiter Filter entfernen"
										@click="resetEmployeeFilter">
										<i class="fa fa-xmark"></i>
									</button>
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
