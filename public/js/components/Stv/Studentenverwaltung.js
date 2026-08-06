/**
 * Copyright (C) 2022 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

import CoreSearchbar from "../searchbar/searchbar.js";
import NavLanguage from "../navigation/Language.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";
import HorizontalSplit from "../horizontalsplit/horizontalsplit.js";
import AppMenu from "../AppMenu.js";
import AppConfig from "../AppConfig.js";
import StvVerband from "./Studentenverwaltung/Verband.js";
import StvList from "./Studentenverwaltung/List.js";
import StvDetails from "./Studentenverwaltung/Details.js";
import StvStudiensemester from "./Studentenverwaltung/Studiensemester.js";

import ApiSearchbar from "../../api/factory/searchbar.js";
import ApiTreemenu from "../../api/factory/treemenu.js";
import ApiStv from "../../api/factory/stv.js";
import ApiStvConfig from '../../api/factory/stv/config.js';


export default {
	name: 'Studentenverwaltung',
	components: {
		CoreSearchbar,
		NavLanguage,
		VerticalSplit,
		HorizontalSplit,
		AppMenu,
		AppConfig,
		StvVerband,
		StvList,
		StvDetails,
		StvStudiensemester
	},
	props: {
		defaultSemester: String,
		config: Object,
		permissions: Object,
		stvRoot: String,
		cisRoot: String,
		avatarUrl: String,
		logoutUrl: String,
		activeAddons: String, // semicolon separated list of active addons
		authUid: String
	},
	provide() {
		return {
			cisRoot: this.cisRoot,
			activeAddonBewerbung: this.activeAddons.split(';').includes('bewerbung'),
			configGenerateAlias: this.config.generateAlias,
			configShowHintKommPrfg: this.config.showHintKommPrfg,
			hasBpkPermission: this.permissions['student/bpk'],
			hasAliasPermission: this.permissions['student/alias'],
			hasPrestudentPermission: this.permissions['basis/prestudent'],
			hasPrestudentstatusPermission: this.permissions['basis/prestudentstatus'],
			hasAssistenzPermissionForStgs: this.permissions['assistenz_stgs'],
			hasSchreibrechtAss: this.permissions['assistenz_schreibrechte'],
			hasAdminPermission: this.permissions['admin'],
			hasPermissionToSkipStatusCheck: this.permissions['student/keine_studstatuspruefung'],
			hasPermissionRtAufsicht: this.permissions['lehre/reihungstestAufsicht'],
			hasPermissionOutputformat: this.permissions['system/change_outputformat'],
			lists: this.lists,
			currentSemester: Vue.computed(() => this.studiensemesterKurzbz),
			defaultSemester: this.defaultSemester,
			$reloadList: () => {
				this.$refs.stvList.reload();
			},
			configShowAufnahmegruppen: this.config.showAufnahmegruppen,
			configAllowUebernahmePunkte: this.config.allowUebernahmePunkte,
			configUseReihungstestPunkte: this.config.useReihungstestPunkte,
			configHasExcludedAreas: this.config.hasExcludedAreas,
			configStvTagsEnabled: this.config.stvTagsEnabled,
			appConfig: Vue.computed(() => this.appconfig),
			hasZGVBakkPermission: this.permissions['student/editBakkZgv'],
			hasZGVMasterPermission: this.permissions['student/editMakkZgv'],
			hasZGVDoctorPermission: this.permissions['student/editDokZgv'],
			hasBismeldenPermission: this.permissions['student/editBismelden'],
			authUid: this.authUid

		}
	},
	data() {
		return {
			sidebarCollapsed: false,
			appconfig: {},
			configEndpoints: ApiStvConfig,
			selected: [],
			searchbaroptions: {
				origin: 'stv',
				calcheightonly: true,
				nolivesearch: true,
				types: {
					student: Vue.computed(() => this.$p.t('search/type_student')),
					prestudent: Vue.computed(() => this.$p.t('search/type_prestudent'))
				},
				actions: {
					student: {
						defaultaction: {
							type: "link",
							action: this.buildStudentSearchResultLink
						},
						childactions: [
						]
					},
					prestudent: {
						defaultaction: {
							type: "link",
							action: this.buildPrestudentSearchResultLink
						},
						childactions: [
						]
					},
					mergedPerson: {
						defaultaction: {
							type: "link",
							action: this.buildPersonSearchResultLink
						},
						defaultactionstudent: {
							type: "link",
							action: this.buildMergedPersonSearchResultLink
						},
						childactions: []
					}
				},
				mergeResults: 'person'
			},
			studiengangKz: undefined,
			studiensemesterKurzbz: this.defaultSemester,
			selected_semester: undefined,
			selected_orgform: undefined,
			lists: {
				nations: [],
				sprachen: [],
				geschlechter: []
			},
			filter: []
		}
	},
	computed: {
		appMenuExtraItems() {
			const extraItems = [];

			if (this.studiengangKz !== undefined && this.selected_semester !== undefined) {
				const studiengang_kz = String(this.studiengangKz);
				const semester = String(this.selected_semester);
				const orgform = this.selected_orgform || '';

				extraItems.push({
					link: FHC_JS_DATA_STORAGE_OBJECT.app_root
						+ 'content/statistik/notenspiegel.php?typ=xls'
						+ '&studiengang_kz=' + studiengang_kz
						+ '&semester=' + semester
						+ '&studiensemester=' + this.studiensemesterKurzbz
						+ '&orgform=' + orgform,
					description: 'stv/grade_report_xls'
				});
				extraItems.push({
					link: FHC_JS_DATA_STORAGE_OBJECT.app_root
						+ 'content/statistik/notenspiegel_erweitert.php?typ=xls'
						+ '&studiengang_kz=' + studiengang_kz
						+ '&semester=' + semester
						+ '&studiensemester=' + this.studiensemesterKurzbz
						+ '&orgform=' + orgform,
					description: 'stv/grade_report_xls_extended'
				});
				extraItems.push({
					link: FHC_JS_DATA_STORAGE_OBJECT.app_root
						+ 'content/statistik/notenspiegel.php?typ=html'
						+ '&studiengang_kz=' + studiengang_kz
						+ '&semester=' + semester
						+ '&studiensemester=' + this.studiensemesterKurzbz
						+ '&orgform=' + orgform,
					description: 'stv/grade_report_html'
				});
			}

			return extraItems;
		},
		appMenuLvPlanungItems() {
			const extraItems = [];

			if (this.studiengangKz !== undefined && this.selected_semester !== undefined) {
				const studiengang_kz = String(this.studiengangKz);
				const semester = String(this.selected_semester);
				const orgform = this.selected_orgform || '';

				extraItems.push({
					link: FHC_JS_DATA_STORAGE_OBJECT.app_root
						+ 'content/statistik/lvplanung.xls.php?'
						+ '&studiengang_kz=' + studiengang_kz
						+ '&semester=' + semester
						+ '&studiensemester_kurzbz=' + this.studiensemesterKurzbz
						+ '&orgform_kurzbz=' + orgform,
					description: 'stv/lvplanung_xls'
				});
				extraItems.push({
					link: FHC_JS_DATA_STORAGE_OBJECT.app_root
						+ 'content/statistik/lvplanung.php?'
						+ '&studiengang_kz=' + studiengang_kz
						+ '&semester=' + semester,
					description: 'stv/lvplanung_html'
				});
			}

			return extraItems;
		},
		linkRt(){
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + '/vilesci/stammdaten/reihungstestverwaltung.php'
		},
		selected_uid(){
			return this.selected?.[this.selected.length - 1]?.uid ?? null;
		},
		linkGradeList(){
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + 'index.ci.php/person/gradelist/index/' + this.selected_uid
		},
		listEndpoint() {
			if (['index', 'stdsem'].includes(this.$route.name))
				return null;

			if (this.$route.params.query) {
				return ApiStv.students.search({
					searchstr: this.$route.params.query,
					types: this.$route.params.types?.split('+') || []
				}, this.$route.params.stdsem);
			}

			let url = 'stdsem/' + this.$route.params.stdsem + '/';
			
			if (this.$route.params.treemenu) {
				url += this.$route.params.treemenu.join('/');
				return ApiTreemenu.data('stv', url);
			}
			
			const paramName = Object.keys(this.$route.params).find(k => k != 'stdsem');
			if (paramName) {
				url += this.$route.name + '/' + this.$route.params[paramName];
				return ApiTreemenu.data('stv', url);
			}

			return null;
		},
		studiengangKuerzel() {
			if (!this.lists?.stgs)
				return '';

			const stg = this.lists.stgs
				.find(stg => stg.studiengang_kz == this.studiengangKz);
			
			if (stg)
				return (stg.typ + stg.kurzbz).toUpperCase();
			
			return '';
		}
	},
	watch: {
		listEndpoint(n, o) {
			if (n != o) {
				this.$refs.stvList.updateUrl(n || []);
			}
		},
		'appconfig.font_size'() {
			// add to html class
			const classList = Object.keys(this.$refs.config.setup.font_size.options);
			classList.forEach(cn => document.documentElement.classList.remove(cn));
			document.documentElement.classList.add(this.appconfig.font_size);
			// recalc Tabulator heights
			if (this.$el) {
				const tabulatorEls = this.$el.querySelectorAll('.tabulator');
				for (const el of tabulatorEls) {
					const tabulators = Tabulator.findTable(el);
					if (tabulators) {
						tabulators[0].searchRows().forEach(row => row.normalizeHeight());
					}
				}
			}
		},
		sidebarCollapsed(newVal) {
			if(newVal) this.$refs.hSplit.collapseLeft()
			else this.$refs.hSplit.showBoth()
		}
	},
	methods: {
		buildMergedPersonSearchResultLink(data) {
			if (data.prestudent_id) {
				return this.buildPrestudentSearchResultLink(data);
			} else if (data.uid) {
				return this.buildStudentSearchResultLink(data);
			} else {
				return this.buildPersonSearchResultLink(data);
			}
		},
		buildPrestudentSearchResultLink(data) {
			return this.$api.getUri(
				'/studentenverwaltung'
				+ '/' + this.studiensemesterKurzbz
				+ '/prestudent/'
				+ data.prestudent_id
				);
		},
		buildStudentSearchResultLink(data) {
			return this.$api.getUri(
				'/studentenverwaltung'
				+ '/' + this.studiensemesterKurzbz
				+ '/student/'
				+ data.uid
				);
		},
		buildPersonSearchResultLink(data) {
			return this.$api.getUri(
				'/studentenverwaltung'
				+ '/' + this.studiensemesterKurzbz
				+ '/person/'
				+ data.person_id
				);
		},
		onSelectVerband({ link, studiengang_kz, semester, orgform_kurzbz }) {
			this.studiengangKz = studiengang_kz;
			this.selected_semester = semester;
			this.selected_orgform = orgform_kurzbz;

			this.$router.push({
				name: 'treemenu',
				params: {
					treemenu: link.split('/')
				}
			});
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
			if (stdsem.toLowerCase() != this.studiensemesterKurzbz?.toLowerCase()) {
				this.$router.push({
					params: {
						stdsem: stdsem.toLowerCase()
					}
				});
			}
			this.studiensemesterKurzbz = stdsem;
		},
		reloadList() {
			this.$refs.stvList.reload();
		},
		searchfunction(params, config) {
			return this.$api.call(ApiSearchbar.searchStv(params), config);
		},
		onSearch(e) {
			const searchsettings = { ...this.$refs.searchbar.searchsettings };

			if (searchsettings.searchstr.length >= 2) {
				this.deleteCustomFilter();
				
				if (!searchsettings.types.length || searchsettings.types.length == this.$refs.searchbar.types.length) {
					this.$router.push({
						name: 'search',
						params: {
							query: searchsettings.searchstr
						}
					});
				} else {
					this.$router.push({
						name: 'searchtypes',
						params: {
							query: searchsettings.searchstr,
							types: searchsettings.types.join('+')
						}
					});
				}
			}
		},
		handleCustomFilter(filter){
			this.filter = filter;
		},
		deleteCustomFilter(){
			this.$refs.stvList.resetFilter();
		},
		showAlertNoSelectedStudent(){
			this.$fhcAlert.alertError(this.$p.t('ui', 'alert_chooseStudent'));
		},
		showAlertMultipleStudents() {
			this.$fhcAlert.alertError(this.$p.t('ui', 'alert_chooseOnlyOneStudent'));
		},
		showAlertNoGroupChosen(){
			this.$fhcAlert.alertError(this.$p.t('ui', 'alert_chooseGroupSem'));
		},
		showMenu(refName) {
			bootstrap.Dropdown.getOrCreateInstance(this.$refs[refName]).show();
		},
		hideMenu(refName) {
			bootstrap.Dropdown.getOrCreateInstance(this.$refs[refName]).hide();
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

		this.$api
			.call(ApiStv.kontakt.address.getNations())
			.then(result => {
				this.lists.nations = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStv.lists.getSprachen())
			.then(result => {
				this.lists.sprachen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStv.lists.getGeschlechter())
			.then(result => {
				this.lists.geschlechter = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStv.lists.getAusbildungen())
			.then(result => {
				this.lists.ausbildungen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStv.lists.getStgs())
			.then(result => {
				this.lists.stgs = result.data;
				this.lists.active_stgs = this.lists.stgs.filter(stg => stg.aktiv);
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStv.lists.getOrgforms())
			.then(result => {
				this.lists.orgforms = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStv.konto.getBuchungstypen())
			.then(result => {
				this.lists.buchungstypen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStv.lists.getStudiensemester())
			.then(result => {
				this.lists.studiensemester = result.data;
				this.lists.studiensemester_desc = result.data.toReversed();
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	mounted() {
		if (this.listEndpoint)
			this.$refs.stvList.updateUrl(this.listEndpoint);
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
				<a class="navbar-brand me-0" :href="stvRoot">StudVw: {{studiensemesterKurzbz}} {{studiengangKuerzel}}</a>
				<button
					class="btn btn-outline-light border-0 d-none d-md-inline-flex m-1  ms-auto"
					type="button"
					@click="sidebarCollapsed = !sidebarCollapsed"
					:aria-label="$p.t('ui/toggle_nav')"
				>
					<span class="fa-solid fa-list"></span>
				</button>
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
			<core-searchbar
				ref="searchbar"
				:searchoptions="searchbaroptions"
				:searchfunction="searchfunction"
				class="searchbar position-relative w-100"
				show-btn-submit
				@submit.prevent="onSearch"
			></core-searchbar>
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
						StudVw: {{studiensemesterKurzbz}} {{studiengangKuerzel}}
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
					</div>
					<div class="offcanvas-body">
						<app-menu app-identifier="stv">
							<li :class="{ dropend: appMenuExtraItems.length }"
								@mouseenter="appMenuExtraItems.length && showMenu('gradeReportToggle')"
								@mouseleave="appMenuExtraItems.length && hideMenu('gradeReportToggle')"
							>
								<a
									ref="gradeReportToggle"
									v-if="appMenuExtraItems.length"
									class="dropdown-toggle"
									href="#"
									role="button"
									data-bs-toggle="dropdown"
									aria-expanded="false"
									data-bs-popper-config='{"strategy":"fixed"}'
								>
									{{ $p.t('stv/grade_report') }}
								</a>
								<a
									v-else
									href="#"
									@click.prevent="showAlertNoGroupChosen"
									>
										{{ $p.t('stv/grade_report') }}
								</a>
								<ul v-if="appMenuExtraItems.length" class="dropdown-menu p-0">
									<li
										v-for="(item, key) in appMenuExtraItems"
										:key="key"
									>
										<a class="dropdown-item" :href="item.link" target="_blank">
											{{ $p.t(item.description) }}
										</a>
									</li>
								</ul>
							</li>
							<li :class="{ dropend: appMenuExtraItems.length }"
								@mouseenter="appMenuExtraItems.length && showMenu('lvPlanungToggle')"
								@mouseleave="appMenuExtraItems.length && hideMenu('lvPlanungToggle')"
							>
								<a
									ref="lvPlanungToggle"
									v-if="appMenuExtraItems.length"
									class="dropdown-toggle"
									href="#"
									role="button"
									data-bs-toggle="dropdown"
									aria-expanded="false"
									data-bs-popper-config='{"strategy":"fixed"}'
									>
										{{ $p.t('stv/lvplanung') }}
								</a>
								<a
									v-else
									href="#"
									@click.prevent="showAlertNoGroupChosen"
								>
									{{ $p.t('stv/lvplanung') }}
								</a>
								<ul v-if="appMenuExtraItems.length" class="dropdown-menu p-0">
									<li
										v-for="(item, key) in appMenuLvPlanungItems"
										:key="key"
									>
										<a class="dropdown-item" :href="item.link" target="_blank">
											{{ $p.t(item.description) }}
										</a>
									</li>
								</ul>
							</li>
							<li>
								<a :href="linkRt" target="_blank">
									{{ $p.t('stv/RTVerwaltung') }}
								</a>
							</li>
							<li>
								<a v-if="selected.length === 1" :href="linkGradeList" target="_blank">
									{{ $p.t('stv/studienverlauf') }}
								</a>
								<a v-else-if="selected.length === 0" href="#" @click.prevent="showAlertNoSelectedStudent">
									{{ $p.t('stv/studienverlauf') }}
								</a>
								<a v-else href="#" @click.prevent="showAlertMultipleStudents">
									{{ $p.t('stv/studienverlauf') }}
								</a>
							</li>
						</app-menu>
					</div>
				</aside>

				<horizontal-split ref="hSplit" :defaultRatio="[15, 85]">
					<template #left>
						<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100  w-100">
							<div class="offcanvas-header justify-content-end px-1 d-md-none">
								<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
							</div>
							<stv-verband :preselected-key="$route.params.treemenu" @select-verband="onSelectVerband" class="col" style="height:0%"></stv-verband>
							<stv-studiensemester :studiensemester-kurzbz="$route.params.stdsem || defaultSemester" @update:studiensemester-kurzbz="studiensemesterChanged"></stv-studiensemester>
						</nav>
					</template>
					<template #right>
						<main>
							<vertical-split :defaultRatio="[50, 50]">
								<template #top>
									<stv-list ref="stvList" v-model:selected="selected" :studiengang-kz="studiengangKz" :studiensemester-kurzbz="studiensemesterKurzbz" @filter-active="handleCustomFilter"></stv-list>
								</template>
								<template #bottom>
									<stv-details ref="details" :students="selected" @reload="reloadList"></stv-details>
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
