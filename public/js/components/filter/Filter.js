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

import {CoreFilterAPIs} from './API.js';
import {CoreRESTClient} from '../../RESTClient.js';
import {CoreFetchCmpt} from '../../components/Fetch.js';
import FilterConfig from './Filter/Config.js';
import FilterColumns from './Filter/Columns.js';

//
const FILTER_COMPONENT_NEW_FILTER = 'Filter Component New Filter';
const FILTER_COMPONENT_NEW_FILTER_TYPE = 'Filter Component New Filter Type';

var _uuid = 0;

/**
 *
 */
export const CoreFilterCmpt = {
	components: {
		CoreFetchCmpt,
		FilterConfig,
		FilterColumns
	},
	emits: [
		'nwNewEntry'
	],
	props: {
		onNwNewEntry: Function, // NOTE(chris): Hack to get the nwNewEntry listener into $props
		title: String,
		sideMenu: {
			type: Boolean,
			default: true
		},
		filterType: {
			type: String,
			required: true
		},
		tabulatorOptions: Object,
		tabulatorEvents: Array,
		tableOnly: Boolean
	},
	data: function() {
		return {
			uuid: 0,
			// FilterCmpt properties
			filterName: null,
			fields: null,
			dataset: null,
			datasetMetadata: null,
			selectedFields: null,
			notSelectedFields: null,
			filterFields: null,

			availableFilters: null,

			// FetchCmpt binded properties
			fetchCmptRefresh: false,
			fetchCmptApiFunction: null,
			fetchCmptApiFunctionParams: null,
			fetchCmptDataFetched: null,

			tabulator: null,
			tableBuilt: false
		};
	},
	computed: {
		filteredData() {
			if (!this.dataset)
				return [];
			return JSON.parse(JSON.stringify(this.dataset));
		},
		filteredColumns() {
			let fields = JSON.parse(JSON.stringify(this.fields)) || [];
			let selectedFields = JSON.parse(JSON.stringify(this.selectedFields)) || [];

			let columns = null;

			// If the tabulator options has been provided and it contains the property columns
			if (this.tabulatorOptions && this.tabulatorOptions.hasOwnProperty('columns'))
				columns = this.tabulatorOptions.columns;

			// If columns is not an array or it is an array with less elements then the array fields
			if (!Array.isArray(columns) || (Array.isArray(columns) && columns.length < fields.length))
			{
				columns = []; // set it as an empty array

				// Loop throught all the retrieved columns from database
				for (let field of fields)
				{
					// Create a new column having the title equal to the field name
					let column = {
						title: field,
						field: field
					};

					// If the column has to be displayed or not
					column.visible = selectedFields.indexOf(field) >= 0;

					// Add the new column to the list of columns
					columns.push(column);
				}
			}
			else // the property columns has been provided in the tabulator options
			{
				// Loop throught the property columns of the tabulator options
				for (let col of columns)
				{
					// If the column has to be displayed or not
					col.visible = selectedFields.indexOf(col.field) >= 0;

					if (col.hasOwnProperty('resizable'))
						col.resizable = col.visible;
				}
			}

			return columns;
		},
		fieldNames() {
			if (!this.tableBuilt)
				return {};
			return this.tabulator.getColumns().reduce((res, col) => {
				res[col.getField()] = col.getDefinition().title;
				return res;
			}, {});
		},
		idExtra() {
			if (!this.uuid)
				return '';
			return '-' + this.uuid;
		},
		columnsForFilter() {
			if (!this.filteredColumns || !this.datasetMetadata)
				return [];
			const filterTitles = this.filteredColumns.reduce((a,c) => {
				a[c.field] = c.title;
				return a;
			}, {});
			return this.datasetMetadata.map(el => ({...el, ...{title: filterTitles[el.name]}}));
		}
	},
	methods: {
		initTabulator() {
			// Define a default tabulator options in case it was not provided
			let tabulatorOptions = {...{
				height: 500,
				layout: "fitColumns",
				movableColumns: true,
				reactiveData: true
			}, ...(this.tabulatorOptions || {})};

			if (!this.tableOnly) {
				tabulatorOptions.data = this.filteredData;
				tabulatorOptions.columns = this.filteredColumns;
			}

			// Start the tabulator with the build options
			this.tabulator = new Tabulator(
				this.$refs.table,
				tabulatorOptions
			);
			// If event handlers have been provided
			if (Array.isArray(this.tabulatorEvents) && this.tabulatorEvents.length > 0)
			{
				// Attach all the provided event handlers to the started tabulator
				for (let evt of this.tabulatorEvents)
					this.tabulator.on(evt.event, evt.handler);
			}
			this.tabulator.on('tableBuilt', () => this.tableBuilt = true);
			if (this.tableOnly) {
				this.tabulator.on('tableBuilt', () => {
					const cols = this.tabulator.getColumns();
					this.fields = cols.map(col => col.getField());
					this.selectedFields = cols.filter(col => col.isVisible()).map(col => col.getField());
				});
			}
		},
		updateTabulator() {
			if (this.tabulator) {
				if (this.tableBuilt)
					this._updateTabulator();
				else
					this.tabulator.on('tableBuilt', this._updateTabulator);
			}
		},
		_updateTabulator() {
			this.tabulator.setData(this.filteredData);
			this.tabulator.setColumns(this.filteredColumns);
		},
		/**
		 *
		 */
		getFilter: function() {
			//
			this.startFetchCmpt(CoreFilterAPIs.getFilter, null, this.render);
		},
		/**
		 *
		 */
		render: function(response) {

			if (CoreRESTClient.hasData(response))
			{
				let data = CoreRESTClient.getData(response);
				this.filterName = data.filterName;
				this.dataset = data.dataset;
				this.datasetMetadata = data.datasetMetadata;
				this.fields = data.fields;
				this.selectedFields = data.selectedFields;
				this.notSelectedFields = this.fields.filter(x => this.selectedFields.indexOf(x) === -1);
				this.filterFields = [];

				for (let i = 0; i < data.datasetMetadata.length; i++)
				{
					for (let j = 0; j < data.filters.length; j++)
					{
						if (data.datasetMetadata[i].name == data.filters[j].name)
						{
							let filter = data.filters[j];
							filter.type = data.datasetMetadata[i].type;

							this.filterFields.push(filter);
							//break;
						}
					}
				}

				// If the side menu is active
				if (this.sideMenu === true)
				{
					this.setSideMenu(data);
				}
				else // otherwise use the dropdown in the filter options
				{
					this.setDropDownMenu(data);
				}
				this.updateTabulator();
			}
			else
			{
				console.error(CoreRESTClient.getError(response));
			}
		},
		/**
		 * Set the menu
		 */
		setSideMenu: function(data) {
			let filters = data.sideMenu.filters;
			let personalFilters = data.sideMenu.personalFilters;
			let filtersArray = [];

			for (let filtersCount = 0; filtersCount < filters.length; filtersCount++)
			{
				let link = filters[filtersCount].link;

				if (link == null) link = '#';

				filtersArray[filtersArray.length] = {
					id: filters[filtersCount].filter_id,
					link: link + filters[filtersCount].filter_id,
					description: filters[filtersCount].desc,
					sort: filtersCount,
					onClickCall: this.handlerGetFilterById
				};
			}

			for (let filtersCount = 0; filtersCount < personalFilters.length; filtersCount++)
			{
				let link = personalFilters[filtersCount].link;

				if (link == null) link = '#';

				filtersArray[filtersArray.length] = {
					id: personalFilters[filtersCount].filter_id,
					link: link + personalFilters[filtersCount].filter_id,
					description: personalFilters[filtersCount].desc,
					subscriptDescription: personalFilters[filtersCount].subscriptDescription,
					subscriptLinkClass: personalFilters[filtersCount].subscriptLinkClass,
					subscriptLinkValue: personalFilters[filtersCount].subscriptLinkValue,
					sort: filtersCount,
					onClickCall: this.handlerGetFilterById,
					onClickSubscriptCall: this.handlerRemoveCustomFilter
				};
			}

			this.availableFilters = filtersArray;

			this.$emit(
				'nwNewEntry',
				{
					link: "#",
					description: "Filters",
					icon: "filter",
					children: filtersArray
				}
			);
		},
		/**
		 * Set the drop down menu
		 */
		setDropDownMenu: function(data) {
			let filters = data.sideMenu.filters;
			let personalFilters = data.sideMenu.personalFilters;
			let filtersArray = [];

			for (let filtersCount = 0; filtersCount < filters.length; filtersCount++)
			{
				let link = filters[filtersCount].link;

				if (link == null) link = '#';

				filtersArray[filtersArray.length] = {
					id: filters[filtersCount].filter_id,
					option: filters[filtersCount].filter_id,
					description: filters[filtersCount].desc
				};
			}

			for (let filtersCount = 0; filtersCount < personalFilters.length; filtersCount++)
			{
				let link = personalFilters[filtersCount].link;

				if (link == null) link = '#';

				filtersArray[filtersArray.length] = {
					id: personalFilters[filtersCount].filter_id,
					option: personalFilters[filtersCount].filter_id,
					description: personalFilters[filtersCount].desc
				};
			}

			this.availableFilters = filtersArray;
		},
		/**
		 * Used to start/refresh the FetchCmpt
		 */
		startFetchCmpt: function(apiFunction, apiFunctionParameters, dataFetchedCallback) {
			// Assign the function api of the FetchCmpt binded property
			this.fetchCmptApiFunction = apiFunction;

			// In case a null value is provided set the parameters as an empty object
			if (apiFunctionParameters == null) apiFunctionParameters = {};

			// Always needed parameters
			apiFunctionParameters.filterUniqueId = FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
			apiFunctionParameters.filterType = this.filterType;

			// Assign parameters to the FetchCmpt binded properties
			this.fetchCmptApiFunctionParams = apiFunctionParameters;
			// Assign data fetch callback to the FetchCmpt binded properties
			this.fetchCmptDataFetched = dataFetchedCallback;
			// Set the FetchCmpt binded property refresh to have the component to refresh
			// NOTE: this should be the last one to be called because it triggers the FetchCmpt to start to refresh
			this.fetchCmptRefresh === true ? this.fetchCmptRefresh = false : this.fetchCmptRefresh = true;
		},

		// ------------------------------------------------------------------------------------------------------------------
		// Event handlers

		/**
		 *
		 */
		handlerSaveCustomFilter: function(customFilterName) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.saveCustomFilter,
				{
					customFilterName
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerRemoveCustomFilter: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.removeCustomFilter,
				{
					filterId: event.currentTarget.getAttribute("href").substring(1)
				},
				this.getFilter
			);
		},

		/*
		 *
		 */
		handlerGetFilterById: function(event) {

			let filterId = null;

			// Get the attribute href if side menu is rendered
			let attr = event.currentTarget.getAttribute("href");

			// Otherwise get the value of the drop down menu
			if (attr == null)
			{
				filterId = event.currentTarget.value;
			}
			else
			{
				filterId = attr.substring(1);
			}

			this.switchFilter(filterId);
		},
		switchFilter(filterId) {
			// Ajax call
			this.startFetchCmpt(
				CoreFilterAPIs.getFilterById,
				{
					filterId
				},
				this.render
			);
		},
		applyFilterConfig(filterFields) {
			this.startFetchCmpt(
				CoreFilterAPIs.applyFilterFields,
				{
					filterFields
				},
				this.getFilter
			);
		}
	},
	beforeCreate() {
		if (!this.tableOnly == !this.filterType)
			alert('You can not have a filter-type in table-only mode!');
	},
	created() {
		if (this.sideMenu && (!this.$props.onNwNewEntry || !(this.$props.onNwNewEntry instanceof Function)))
			alert('"nwNewEntry" listener is mandatory when sideMenu is true');
		this.uuid = _uuid++;
		if (!this.tableOnly)
			this.getFilter(); // get the filter data
	},
	mounted() {
		this.initTabulator();
	},
	template: `{{$attrs}}
		<!-- Load filter data -->
		<core-fetch-cmpt
			v-if="!tableOnly"
			v-bind:api-function="fetchCmptApiFunction"
			v-bind:api-function-parameters="fetchCmptApiFunctionParams"
			v-bind:refresh="fetchCmptRefresh"
			@data-fetched="fetchCmptDataFetched">
		</core-fetch-cmpt>

		<div class="row" v-if="title != null && title != ''">
			<div class="col-lg-12">
				<h3 class="page-header">
					{{ title }}
				</h3>
			</div>
		</div>

		<div :id="'filterCollapsables' + idExtra">

			<div class="filter-header-title">
				<span v-if="!tableOnly" class="filter-header-title-span-filter">[ {{ filterName }} ]</span>
				<span v-if="!tableOnly" data-bs-toggle="collapse" :data-bs-target="'#collapseFilters' + idExtra" class="filter-header-title-span-icon fa-solid fa-filter fa-xl"></span>
				<span data-bs-toggle="collapse" :data-bs-target="'#collapseColumns' + idExtra" class="filter-header-title-span-icon fa-solid fa-table-columns fa-xl"></span>
			</div>

			<filter-columns
				:id="'collapseColumns' + idExtra"
				class="card-body collapse"
				:data-bs-parent="'#filterCollapsables' + idExtra"
				:fields="fields"
				:selected="selectedFields"
				:names="fieldNames"
				@hide="tabulator.hideColumn($event)"
				@show="tabulator.showColumn($event)"
			></filter-columns>

			<filter-config
				v-if="!tableOnly"
				:id="'collapseFilters' + idExtra"
				class="card-body collapse"
				:data-bs-parent="'#filterCollapsables' + idExtra"
				:filters="!sideMenu ? (availableFilters || []) : []"
				:columns="columnsForFilter"
				:fields="filterFields || []"
				@switch-filter="switchFilter"
				@apply-filter-config="applyFilterConfig"
				@save-custom-filter="handlerSaveCustomFilter"
			></filter-config>
		</div>

		<!-- Tabulator -->
		<div ref="table" :id="'filterTableDataset' + idExtra" class="filter-table-dataset"></div>
	`
};

