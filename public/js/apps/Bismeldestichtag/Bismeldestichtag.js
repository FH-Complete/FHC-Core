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

import {BismeldestichtagHelper} from './BismeldestichtagHelper.js';

import {CoreFilterCmpt} from '../../components/filter/Filter.js';
import {CoreNavigationCmpt} from '../../components/navigation/Navigation.js';
import {CoreRESTClient} from '../../RESTClient.js';
import {CoreFetchCmpt} from '../../components/Fetch.js';
import {BismeldestichtagAPIs} from './API.js';

import PluginsPhrasen from '../../plugins/Phrasen.js';

const bismeldestichtagApp = Vue.createApp({
	data: function() {
		return {
			bismeldestichtagTabulatorOptions: {
				maxHeight: "100%",
				minHeight: 50,
				layout: 'fitColumns',
				index: 'meldestichtag_id',
				initialSort:[
					{column:"meldestichtag", dir:"desc"}
				],
				columns: [
					{title: 'Meldestichtag', field: 'meldestichtag', headerFilter: true, formatter: function(cell){
							return BismeldestichtagHelper.formatDate(cell.getValue());
						}
					},
					{title: 'Studiensemester', field: 'studiensemester_kurzbz', headerFilter: true, sorter:function(a, b, aRow, bRow, column, dir, sorterParams) {

							//aRow, bRow - the row components for the values being compared
							let semesterStartA = new Date(aRow.getData().semester_start);
							let semesterStartB = new Date(bRow.getData().semester_start);

							return semesterStartA - semesterStartB; // difference between studiensemester start dates
						}
					},
					{title: 'Semesterstart',field: 'semester_start', headerFilter: true, visible: false, formatter: function(cell){
							return BismeldestichtagHelper.formatDate(cell.getValue());
						}
					},
					{title: 'ID', field: 'meldestichtag_id', headerFilter: true, visible: false},
					{title: 'Insertamum', field: 'insertamum', headerFilter: true, visible: false},
					{title: 'Insertvon', field: 'insertvon', headerFilter: true, visible: false},
					{title: 'Löschen', field: 'loeschen', headerFilter: false, formatter:function(cell){
							return	'<button class="btn btn-outline-secondary delete-btn" data-meldestichtag-id="'+cell.getRow().getIndex()+'">'+
										'<i class="fa fa-xmark"></i>'+
									'</button>';
						}
					}
				]
			},
			bismeldestichtagTabulatorEventHandlers: [
				{
					event: "rowClick",
					handler: function(e, row) {
						if (e.target.nodeName == 'DIV') {
							let data = row.getData();
							alert(data.studiensemester_kurzbz + ': ' + BismeldestichtagHelper.formatDate(data.meldestichtag));
						}
					}
				},
				{
					event: "tableBuilt",
					handler: () => {
						this.handlerStudiensemester();
					}
				}
			],
			meldestichtag: null, // date of Meldestichtag
			semList: null, // all Studiensemester for dropdown
			currSem: null, // selected Studiensemester
			fetchCmptApiFunction: {}, // api function call
			fetchCmptApiFunctionParams: null, // parameters for api function call
			fetchCmptDataFetched: null, // function to execute after call
			fetchCmptRefresh: true // for refreshing
		};
	},
	components: {
		CoreNavigationCmpt,
		CoreFilterCmpt,
		BismeldestichtagAPIs,
		CoreFetchCmpt,
		"datepicker": VueDatePicker
	},
	methods: {
		/**
		 * Define Studiensemester call and method to be executed after the call
		 */
		handlerStudiensemester: function() {
			this.startFetchCmpt(
				BismeldestichtagAPIs.getStudiensemester,
				null,
				this.fetchCmptDataFetchedStudiensemester
			);
		},
		/**
		 * Define Studiensemester call and method to be executed after the call
		 */
		handlerBismeldestichtage: function() {
			this.startFetchCmpt(
				BismeldestichtagAPIs.getBismeldestichtage,
				null,
				this.fetchCmptDataFetchedBismeldestichtage
			);
		},
		/**
		 * Define add Bismeldestichtag call and method to be executed after the call
		 */
		handlerAddBismeldestichtag: function(event) {
			this.startFetchCmpt(
				BismeldestichtagAPIs.addBismeldestichtag,
				{
					meldestichtag: this.meldestichtag,
					studiensemester_kurzbz: this.currSem
				},
				this.fetchCmptDataFetchedAddBismeldestichtag
			);
		},
		/**
		 * Define delete Bismeldestichtag call and method to be executed after the call
		 */
		handlerDeleteBismeldestichtag: function(meldestichtag_id) {
			this.startFetchCmpt(
				BismeldestichtagAPIs.deleteBismeldestichtag,
				{
					meldestichtag_id: meldestichtag_id
				},
				this.fetchCmptDataFetchedDeleteBismeldestichtag
			);
		},
		/**
		 * Called after Studiensemester response is received
		 */
		fetchCmptDataFetchedStudiensemester: function(data) {
			if (CoreRESTClient.isError(data)) alert(CoreRESTClient.getError(data));
			if (CoreRESTClient.hasData(data))
			{
				let semRes = CoreRESTClient.getData(data);
				this.semList = semRes.semList;
				this.currSem = semRes.currSem;
				this.handlerBismeldestichtage();
			}
			else
				alert("No Studiensemester data");
		},
		/**
		 * Called after Bismeldestichtage response is received
		 */
		fetchCmptDataFetchedBismeldestichtage: function(data) {
			if (CoreRESTClient.isError(data)) alert(CoreRESTClient.getError(data));
			if (CoreRESTClient.hasData(data))
			{
				// set the Meldestichtagedata
				this.$refs.bismeldestichtageTable.tabulator.setData(CoreRESTClient.getData(data));

				// save delete Bismeldestichtag function
				let funcDeleteBismeldestichtag = this.handlerDeleteBismeldestichtag;

				let btns = document.getElementsByClassName('delete-btn');

				// add click events for deletion
				for (let btn in btns)
				{
					if (btns[btn].addEventListener)
					{
						btns[btn].addEventListener('click',
							function(){
								funcDeleteBismeldestichtag(btns[btn].getAttribute('data-meldestichtag-id'));
							}
						);
					}
				}
			}
			else
				this.$refs.bismeldestichtageTable.tabulator.setData([]);
		},
		/**
		 * Called after Add Bismeldestichtag response is received
		 */
		fetchCmptDataFetchedAddBismeldestichtag: function(data) {
			if (CoreRESTClient.isError(data))
				alert(CoreRESTClient.getError(data));
			else if (CoreRESTClient.hasData(data))
			{
				this.handlerBismeldestichtage();
			}
			else
				alert("No response data");
		},
		/**
		 * Called after Add Bismeldestichtag response is received
		 */
		fetchCmptDataFetchedDeleteBismeldestichtag: function(data) {
			if (CoreRESTClient.isError(data))
				alert(CoreRESTClient.getError(data));
			else if (CoreRESTClient.hasData(data))
			{
				this.handlerBismeldestichtage();
			}
			else
				alert("No response data");
		},
		/**
		 * Used to start/refresh the FetchCmpt
		 */
		startFetchCmpt: function(apiFunction, apiFunctionParameters, dataFetchedCallback) {
			// Assign the function api of the FetchCmpt binded property
			this.fetchCmptApiFunction = apiFunction;

			// In case a null value is provided set the parameters as an empty object
			if (apiFunctionParameters == null) apiFunctionParameters = {};

			// Assign parameters to the FetchCmpt binded properties
			this.fetchCmptApiFunctionParams = apiFunctionParameters;
			// Assign data fetch callback to the FetchCmpt binded properties
			this.fetchCmptDataFetched = dataFetchedCallback;
			// Set the FetchCmpt binded property refresh to have the component to refresh
			// NOTE: this should be the last one to be called because it triggers the FetchCmpt to start to refresh
			this.fetchCmptRefresh === true ? this.fetchCmptRefresh = false : this.fetchCmptRefresh = true;
		}
	}
});

bismeldestichtagApp.use(PluginsPhrasen).mount('#main');
