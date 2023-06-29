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

import {BismeldestichtagTabulatorOptions} from './TabulatorSetup.js';
import {BismeldestichtagTabulatorEventHandlers} from './TabulatorSetup.js';

import {CoreFilterCmpt} from '../../components/filter/Filter.js';
import {CoreNavigationCmpt} from '../../components/navigation/Navigation.js';
import {CoreRESTClient} from '../../RESTClient.js';
import {CoreFetchCmpt} from '../../components/Fetch.js';
import {BismeldestichtagAPIs} from './API.js';

const bismeldestichtagApp = Vue.createApp({
	data: function() {
		return {
			appSideMenuEntries: {},
			bismeldestichtagTabulatorOptions: BismeldestichtagTabulatorOptions,
			bismeldestichtagTabulatorEventHandlers: BismeldestichtagTabulatorEventHandlers,
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
		CoreFetchCmpt
	},
	created() {
		this.handlerStudiensemester();
	},
	methods: {
		newSideMenuEntryHandler: function(payload) {
			this.appSideMenuEntries = payload;
		},
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
		handlerDeleteBismeldestichtag: function(event) {
			this.startFetchCmpt(
				BismeldestichtagAPIs.deleteBismeldestichtag,
				{
					meldestichtag: this.meldestichtag,
					studiensemester_kurzbz: this.currSem
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
			}
			else
				alert("No response data");
		},
		/**
		 * Called after Add Bismeldestichtag response is received
		 */
		fetchCmptDataFetchedAddBismeldestichtag: function(data) {
			if (CoreRESTClient.isError(data))
				alert(CoreRESTClient.getError(data));
			else if (CoreRESTClient.hasData(data))
			{
				window.location.reload();
				alert("Successfully added Bismeldestichtag");
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
				window.location.reload();
				alert("Successfully deletted Bismeldestichtag");
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

bismeldestichtagApp.mount('#main');
