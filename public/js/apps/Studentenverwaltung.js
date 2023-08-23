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

import FhcSearchbar from "../components/searchbar/searchbar.js";
import StvVerband from "../components/Studienverwaltung/Verband.js";
import StvList from "../components/Studienverwaltung/List.js";
import StvStudent from "../components/Studienverwaltung/Student.js";
import VerticalSplit from "../components/verticalsplit/verticalsplit.js";
import fhcapifactory from "./api/fhcapifactory.js";

Vue.$fhcapi = fhcapifactory;

const app = Vue.createApp({
	components: {
		FhcSearchbar,
		StvVerband,
		StvList,
		StvStudent,
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
		searchfunction(searchsettings) {
			return Vue.$fhcapi.Search.search(searchsettings);  
		}
	}
});

app.use(primevue.config.default).mount('#main');

