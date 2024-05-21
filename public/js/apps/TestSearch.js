import {CoreFilterCmpt} from '../components/filter/Filter.js';
import {CoreNavigationCmpt} from '../components/navigation/Navigation.js';
import CoreVerticalsplit from "../components/verticalsplit/verticalsplit.js";
import CoreSearchbar from "../components/searchbar/searchbar.js";
import FhcApi from "../plugin/FhcApi.js";

const app = Vue.createApp({
	components: {
		CoreNavigationCmpt,
		CoreFilterCmpt,
		CoreVerticalsplit,
		CoreSearchbar
	},
	data() {
		return {
			title: "Test Search",
			appSideMenuEntries: {},
			searchbaroptions: {
				types: [
					"person",
					"raum",
					"mitarbeiter",
					"student",
					"prestudent",
					"document",
					"cms",
					"organisationunit"
					],
				actions: {
					person: {
						defaultaction: {
							type: "link",
							action(data) { 
											//alert('person defaultaction ' + JSON.stringify(data)); 
											//window.location.href = data.profil;
								return data.profil;
							}
						},
						childactions: [
						{
							label: "testchildaction1",
							icon: "fas fa-check-circle",
							type: "function",
							action(data) { 
								alert('person testchildaction 01 ' + JSON.stringify(data)); 
							}
						},
						{
							label: "testchildaction2",
							icon: "fas fa-file-csv",
							type: "function",
							action(data) { 
								alert('person testchildaction 02 ' + JSON.stringify(data)); 
							}
						}
						]
					},
					raum: {
						defaultaction: {
							type: "function",
							action(data) { 
								alert('raum defaultaction ' + JSON.stringify(data)); 
							}
						},
						childactions: [                      
						{
							label: "Rauminformation",
							icon: "fas fa-info-circle",
							type: "link",
							action(data) { 
								return data.infolink;
							}
						},
						{
							label: "Raumreservierung",
							icon: "fas fa-bookmark",
							type: "link",
							action(data) { 
								return data.booklink;
							}
						}
						]
					},
					employee: {
						defaultaction: {
							type: "function",
							action(data) { 
								alert('employee defaultaction ' + JSON.stringify(data)); 
							}
						},
						childactions: [
						{
							label: "testchildaction1",
							icon: "fas fa-address-book",
							type: "function",
							action(data) { 
								alert('employee testchildaction 01 ' + JSON.stringify(data)); 
							}
						},
						{
							label: "testchildaction2",
							icon: "fas fa-user-slash",
							type: "function",
							action(data) { 
								alert('employee testchildaction 02 ' + JSON.stringify(data)); 
							}
						},
						{
							label: "testchildaction3",
							icon: "fas fa-bell",
							type: "function",
							action(data) { 
								alert('employee testchildaction 03 ' + JSON.stringify(data)); 
							}
						},
						{
							label: "testchildaction4",
							icon: "fas fa-calculator",
							type: "function",
							action(data) { 
								alert('employee testchildaction 04 ' + JSON.stringify(data)); 
							}
						}
						]
					},
					organisationunit: {
						defaultaction: {
							type: "function",
							action(data) { 
								alert('organisationunit defaultaction ' + JSON.stringify(data)); 
							}
						},
						childactions: []
					}
				}
			},
			searchbaroptions2: {
				types: [
					"raum",
					"organisationunit"
					],
				actions: {
					raum: {
						defaultaction: {
							type: "function",
							action(data) { 
								alert('raum defaultaction ' + JSON.stringify(data)); 
							}
						},
						childactions: [                      
						{
							label: "Rauminformation",
							icon: "fas fa-info-circle",
							type: "link",
							action(data) { 
								return data.infolink;
							}
						},
						{
							label: "Raumreservierung",
							icon: "fas fa-bookmark",
							type: "link",
							action(data) { 
								return data.booklink;
							}
						}
						]
					},              
					organisationunit: {
						defaultaction: {
							type: "function",
							action(data) { 
								alert('organisationunit defaultaction ' + JSON.stringify(data)); 
							}
						},
						childactions: []
					}
				}
			}
		};
	},
	methods: {
		newSideMenuEntryHandler(payload) {
			this.appSideMenuEntries = payload;
		},
		searchfunction(searchsettings) {
			return this.$fhcApi.factory.search.search(searchsettings);  
		},
		searchfunctiondummy(searchsettings) {
			return this.$fhcApi.factory.search.searchdummy(searchsettings);  
		}
	}
});
app.use(FhcApi)
app.mount('#main');
