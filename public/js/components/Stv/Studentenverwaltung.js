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
		activeAddons: String // semicolon separated list of active addons
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
			endpoint: ApiStvVerband,
			selected: [],
			searchbaroptions: {
				cssclass: "position-relative",
				calcheightonly: true,
				types: [
					"studentStv",
					"prestudent"
				],
				actions: {
					student: {
						defaultaction: {
							type: "link",
							action: function(data) {
								return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/studentenverwaltung/student/' + data.uid;
							}
						},
						childactions: [
						]
					},
					prestudent: {
						defaultaction: {
							type: "link",
							action: function(data) {
								return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/studentenverwaltung/prestudent/' + data.prestudent_id;
							}
						},
						childactions: [
						]
					}
				}
			},
			studiengangKz: undefined,
			studiensemesterKurzbz: this.defaultSemester,
			lists: {
				nations: [],
				sprachen: [],
				geschlechter: []
			}
		}
	},
	methods: {
		onSelectVerband({link, studiengang_kz}) {
			this.studiengangKz = studiengang_kz;
			this.$refs.stvList.updateUrl(
				ApiStv.students.verband(link)
			);
		},
		studiensemesterChanged(v) {
			this.studiensemesterKurzbz = v;
			this.$refs.stvList.updateUrl();
			this.$refs.details.reload();
		},
		reloadList() {
			this.$refs.stvList.reload();
		},
		searchfunction(params) {
			return this.$api.call(ApiSearchbar.search(params));
		}
	},
	created() {
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
		//Test manu Systemerror
		//FHC_JS_DATA_STORAGE_OBJECT.systemerror_mailto = 'ma0068@technikum-wien.at';this.$fhcAlert.handleSystemError(1);
		if (this.$route.params.id) {
			this.$refs.stvList.updateUrl(
				ApiStv.students.uid(this.$route.params.id),
				true
			);
		} else if (this.$route.params.prestudent_id) {
			this.$refs.stvList.updateUrl(
				ApiStv.students.prestudent(this.$route.params.prestudent_id),
				true
			);
		} else if (this.$route.params.person_id) {
			this.$refs.stvList.updateUrl(
				ApiStv.students.person(this.$route.params.person_id),
				true
			);
		}

	},
	template: `
	<div class="stv">
		<header class="navbar navbar-expand-lg navbar-dark bg-dark flex-md-nowrap p-0 shadow">
			<a class="navbar-brand col-md-4 col-lg-3 col-xl-2 me-0 px-3" :href="stvRoot">FHC 4.0</a>
			<button class="navbar-toggler d-md-none m-1 collapsed" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" :aria-label="$p.t('ui/toggle_nav')"><span class="navbar-toggler-icon"></span></button>
			<core-searchbar :searchoptions="searchbaroptions" :searchfunction="searchfunction" class="searchbar w-100"></core-searchbar>
		</header>
		<div class="container-fluid overflow-hidden">
			<div class="row h-100">
				<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
					<div class="offcanvas-header justify-content-end px-1 d-md-none">
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
					</div>
					<stv-verband @select-verband="onSelectVerband" class="col" style="height:0%" :endpoint="endpoint"></stv-verband>
					<stv-studiensemester :default="defaultSemester" @changed="studiensemesterChanged"></stv-studiensemester>
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
