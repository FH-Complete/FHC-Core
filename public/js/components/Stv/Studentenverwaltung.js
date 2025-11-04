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
import VerticalSplit from "../verticalsplit/verticalsplit.js";
import AppMenu from "../AppMenu.js";
import StvVerband from "./Studentenverwaltung/Verband.js";
import StvList from "./Studentenverwaltung/List.js";
import StvDetails from "./Studentenverwaltung/Details.js";
import StvStudiensemester from "./Studentenverwaltung/Studiensemester.js";

import ApiSearchbar from "../../api/factory/searchbar.js";
import ApiStv from "../../api/factory/stv.js";
import ApiStvVerband from '../../api/factory/stv/verband.js';


export default {
	name: 'Studentenverwaltung',
	components: {
		CoreSearchbar,
		VerticalSplit,
		AppMenu,
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
		activeAddons: String, // semicolon separated list of active addons
		url_studiensemester_kurzbz: String,
		url_mode: String,
		url_prestudent_id: String,
		url_tab: String,
		url_studiengang: String
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
			configUseReihungstestPunkte: this.config.useReihungstestPunkte
		}
	},
	data() {
		return {
			selected: [],
			searchbaroptions: {
				origin: 'stv',
				calcheightonly: true,
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
			studiengangKuerzel: '',
			studiensemesterKurzbz: this.defaultSemester,
			lists: {
				nations: [],
				sprachen: [],
				geschlechter: []
			},
			verbandEndpoint: ApiStvVerband
		}
	},
	watch: {
		'url_studiensemester_kurzbz': function (newVal, oldVal) {
			if (newVal !== oldVal) {
				this.studiensemesterKurzbz = newVal;
				this.$refs.stvList.updateUrl();
				this.$refs.details.reload();
			}
		},
		'url_studiengang': function (newVal, oldVal) {
			if (newVal !== oldVal) {
				this.checkUrlStudiengang();
			}
		},
		'url_mode': function () {
			this.handlePersonUrl();
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
			return this.$fhcApi.getUri(
				'/studentenverwaltung'
				+ '/' + this.studiensemesterKurzbz
				+ '/prestudent/'
				+ data.prestudent_id
				);
		},
		buildStudentSearchResultLink(data) {
			return this.$fhcApi.getUri(
				'/studentenverwaltung'
				+ '/' + this.studiensemesterKurzbz
				+ '/student/'
				+ data.uid
				);
		},
		buildPersonSearchResultLink(data) {
			return this.$fhcApi.getUri(
				'/studentenverwaltung'
				+ '/' + this.studiensemesterKurzbz
				+ '/person/'
				+ data.person_id
				);
		},
		onSelectVerband( {link, studiengang_kz}) {
			let urlpath = String(link);
			if (!urlpath.match(/\/prestudent/))
			{
				urlpath = 'CURRENT_SEMESTER' + '/' + urlpath;
			}
			this.$refs.stvList.updateUrl(ApiStv.students.verband(urlpath));

			this.studiengangKz = studiengang_kz;
			const stg = this.lists.stgs.find((element) => {
				return (element.studiengang_kz === this.studiengangKz);
			});
			if (stg)
			{
				this.studiengangKuerzel = (stg.typ + stg.kurzbz).toUpperCase()
				this.$router.push({
					name: 'studiengang',
					params: {
						studiensemester_kurzbz: this.studiensemesterKurzbz,
						studiengang: this.studiengangKuerzel
					}
				});
			} else
			{
				this.studiengangKuerzel = '';
				this.$router.push({
					name: 'studiensemester',
					params: {
						studiensemester_kurzbz: this.studiensemesterKurzbz
					}
				});
		}
		},
		studiensemesterChanged(v) {
			this.studiensemesterKurzbz = v;

			this.$router.push({
				params: {
					studiensemester_kurzbz: v
				}
			});

			this.$refs.stvList.updateUrl();
			this.$refs.details.reload();
		},
		reloadList() {
			this.$refs.stvList.reload();
		},
		searchfunction(params, config) {
			return this.$api.call(ApiSearchbar.searchStv(params), config);
		},
		handlePersonUrl() {
			if (this.$route.params.id) {
				this.$refs.stvList.updateUrl(
					ApiStv.students.uid(this.$route.params.id, 'CURRENT_SEMESTER'),
					true
					);
			} else if (this.$route.params.prestudent_id) {
				this.$refs.stvList.updateUrl(
					ApiStv.students.prestudent(this.$route.params.prestudent_id, 'CURRENT_SEMESTER'),
					true
					);
			} else if (this.$route.params.person_id) {
				this.$refs.stvList.updateUrl(
					ApiStv.students.person(this.$route.params.person_id, 'CURRENT_SEMESTER'),
					true
					);
			}
		},
		checkUrlStudiengang() {
			if (this.url_studiengang) {
				const stg = this.lists.stgs.find((element) => {
					const kuerzel = (element.typ + element.kurzbz).toUpperCase();
					return (this.url_studiengang === kuerzel);
				});
				if (stg) {
					this.studiengangKz = stg.studiengang_kz;
					this.studiengangKuerzel = (stg.typ + stg.kurzbz).toUpperCase();
				} else {
					this.$router.replace({
						name: 'studiensemester',
						params: {
							studiensemester_kurzbz: this.studiensemesterKurzbz
						}
					});
				}
			}
		}
	},
	created() {
		if (!this.url_studiensemester_kurzbz) {
			this.$router.replace({
				name: 'studiensemester',
				params: {
					studiensemester_kurzbz: this.defaultSemester
				}
			});
		} else {
			this.studiensemesterKurzbz = this.url_studiensemester_kurzbz;
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
				this.checkUrlStudiengang();
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
		//Test manu Systemerror
		//FHC_JS_DATA_STORAGE_OBJECT.systemerror_mailto = 'ma0068@technikum-wien.at';this.$fhcAlert.handleSystemError(1);
		this.handlePersonUrl();
	},
	template: /* html */`
	<div class="stv">
		<header class="navbar navbar-expand-lg navbar-dark bg-dark flex-md-nowrap p-0 shadow" style="background-color: green !important;">
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
				<a class="navbar-brand me-0" :href="stvRoot">
					<span style="font-size: 10px; line-height: 1; display: block; width: 100%; text-wrap: wrap; text-align: left;">
						DEMO Datenstand 29.09.2025
					</span>
					StudVw: {{studiensemesterKurzbz}} {{studiengangKuerzel}}
				</a>
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
				:searchoptions="searchbaroptions"
				:searchfunction="searchfunction"
				class="searchbar position-relative w-100"
			></core-searchbar>
		</header>
		<div class="container-fluid overflow-hidden">
			<div class="row h-100">
				<aside id="appMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
					<div class="offcanvas-header">
						StudVw: {{studiensemesterKurzbz}} {{studiengangKuerzel}}
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
					</div>
					<div class="offcanvas-body">
						<app-menu app-identifier="stv" />
					</div>
				</aside>
				<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
					<div class="offcanvas-header justify-content-end px-1 d-md-none">
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
					</div>
					<stv-verband :preselectedKey="'' + studiengangKz" :endpoint="verbandEndpoint" @select-verband="onSelectVerband" class="col" style="height:0%"></stv-verband>
					<stv-studiensemester v-model:studiensemester-kurzbz="studiensemesterKurzbz" @update:studiensemester-kurzbz="studiensemesterChanged"></stv-studiensemester>
				</nav>
				<main class="col-md-8 ms-sm-auto col-lg-9 col-xl-10">
					<vertical-split>
						<template #top>
							<stv-list ref="stvList" v-model:selected="selected" :studiengang-kz="studiengangKz" :studiensemester-kurzbz="studiensemesterKurzbz"></stv-list>
						</template>
						<template #bottom>
							<stv-details ref="details" :students="selected"></stv-details>
						</template>
					</vertical-split>
				</main>
			</div>
		</div>
	</div>`
};
