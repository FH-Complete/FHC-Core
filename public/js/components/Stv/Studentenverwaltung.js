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

import FhcSearchbar from "../searchbar/searchbar.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";
import StvVerband from "./Studentenverwaltung/Verband.js";
import StvList from "./Studentenverwaltung/List.js";
import StvDetails from "./Studentenverwaltung/Details.js";


export default {
	components: {
		FhcSearchbar,
		StvVerband,
		StvList,
		StvDetails,
		VerticalSplit
	},
	data() {
		return {
			selected: [],
			searchbaroptions: {
				types: [
					"person",
					"student",
					"prestudent"
				],
				actions: {
					person: {
						defaultaction: {
							type: "link",
							action: function(data) { 
								return data.profil;
							}
						},
						childactions: [
							{
								"label": "testchildaction1",
								"icon": "fas fa-check-circle",
								"type": "function",
								"action": function(data) { 
									alert('person testchildaction 01 ' + JSON.stringify(data)); 
								}
							},
							{
								"label": "testchildaction2",
								"icon": "fas fa-file-csv",
								"type": "function",
								"action": function(data) { 
									alert('person testchildaction 02 ' + JSON.stringify(data)); 
								}
							}
						]
					}
				}
			},
		}
	},
	computed: {
		lastSelected() {
			return this.selected[this.selected.length - 1];
		}
	},
	methods: {
		onSelectVerband(link) {
			this.$refs.stvList.updateUrl(link);
		},
		searchfunction(searchsettings) {
			return Vue.$fhcapi.Search.search(searchsettings);  
		}
	},
	mounted() {
		if (this.$route.params.id) {
			this.$refs.stvList.updateUrl('components/stv/students/uid/' + this.$route.params.id);
		}
	},
	template: `
	<header class="navbar navbar-expand-lg navbar-dark bg-dark flex-md-nowrap p-0 shadow">
		<a class="navbar-brand col-md-4 col-lg-3 col-xl-2 me-0 px-3" href="<?= site_url('Studentenverwaltung'); ?>">FHC 4.0</a>
		<button class="navbar-toggler d-md-none m-1 collapsed" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
		<fhc-searchbar :searchoptions="searchbaroptions" :searchfunction="searchfunction" class="searchbar w-100"></fhc-searchbar>
	</header>
	<div class="container-fluid overflow-hidden">
		<div class="row h-100">
			<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
				<div class="offcanvas-header justify-content-end px-1 d-md-none">
					<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<stv-verband @select-verband="onSelectVerband"></stv-verband>
			</nav>
			<main class="col-md-8 ms-sm-auto col-lg-9 col-xl-10">
				<vertical-split>
					<template #top>
						<stv-list ref="stvList" v-model:selected="selected"></stv-list>
					</template>
					<template #bottom>
						<stv-details :student="lastSelected"></stv-details>
					</template>
				</vertical-split>
			</main>
		</div>
	</div>`
};
