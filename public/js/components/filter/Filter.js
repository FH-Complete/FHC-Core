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

import {CoreFetchCmpt} from '../../components/Fetch.js';
import FilterConfig from './Filter/Config.js';
import FilterColumns from './Filter/Columns.js';
import TableDownload from './Table/Download.js';

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
		FilterColumns,
		TableDownload
	},
	emits: [
		'nwNewEntry',
		'click:new'
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
		tableOnly: Boolean,
		reload: Boolean,
		download: {
			type: [Boolean, String, Function, Array, Object],
			default: false
		},
		newBtnShow: Boolean,
		newBtnClass: [String, Array, Object],
		newBtnDisabled: Boolean,
		newBtnLabel: String,
		uniqueId: String,
		// TODO soll im master kommen?
		idField: String,
		parentIdField: String
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
			selectedFilter: null,

			// FetchCmpt binded properties
			fetchCmptRefresh: false,
			fetchCmptApiFunction: null,
			fetchCmptApiFunctionParams: null,
			fetchCmptDataFetched: null,

			tabulator: null,
			tableBuilt: false,
			tabulatorHasSelector: false,
			selectedData: []
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
					/* fields.indexOf(col.field) == -1; ensures displaying formatter colums
					e.g. column with rowSelection checkboxes or with custom formatted action buttons */
					col.visible = selectedFields.indexOf(col.field) >= 0 || fields.indexOf(col.field) == -1;

					if (col.hasOwnProperty('resizable'))
						col.resizable = col.visible;
				}
			}

			return columns;
		},
		fieldIdsForVisibilty() {
			if (!this.tableBuilt)
				return [];
			return this.tabulator.getColumns().filter(col => {
				let def = col.getDefinition();
				return !def.frozen && def.title;
			}).map(col => col.getField());
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
		reloadTable() {
			if (this.tableOnly)
				this.tabulator.setData();
			else
				this.getFilter();
		},
		async initTabulator() {
			let placeholder = '< Phrasen Plugin not loaded! >';
			if (this.$p) {
				await this.$p.loadCategory('ui');
				placeholder = this.$p.t('ui/keineDatenVorhanden');
			}
			// Define a default tabulator options in case it was not provided
			let tabulatorOptions = {...{
					height: 500,
					layout: "fitDataStretch",
					movableColumns: true,
					columnDefaults:{
						tooltip: true,
					},
					placeholder,
					reactiveData: true,
					persistence: true
				}, ...(this.tabulatorOptions || {})};

			if (!this.tableOnly) {
				tabulatorOptions.data = this.filteredData;
				tabulatorOptions.columns = this.filteredColumns;
			}

			if (tabulatorOptions.columns && tabulatorOptions.columns.filter(el => el.formatter == 'rowSelection').length)
				this.tabulatorHasSelector = true;
			// TODO check ob im core bleiben soll
			if (this.idField) {
				// enable nested tabulator if parent Id given
				if (this.parentIdField) tabulatorOptions.dataTree = true;
				// set tabulator index
				tabulatorOptions.index = this.idField;
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
			this.tabulator.on("rowSelectionChanged", data => {
				this.selectedData = data;
			});
			// TODO check ob im core so bleiben soll
			// if nested tabulator, restructure data
			if (this.parentIdField && this.idField) {
				this.tabulator.on("dataLoading", data => {
					let toDelete = [];

					// loop through all data
					for (let childIdx = 0; childIdx < data.length; childIdx++)
					{
						let child = data[childIdx];

						// if it has parent id, it is a child
						if (child[this.parentIdField])
						{
							// append the child on the right place. If parent found, mark original sw child on 0 level for deleting
							if (this.appendChild(data, child)) toDelete.push(childIdx);
						}
					}

					// delete the marked children from 0 level
					for (let counter = 0; counter < toDelete.length; counter++)
					{
						// decrease index by counter as index of data array changes after every deletion
						data.splice(toDelete[counter] - counter, 1);
					}
				});
			}
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
			this.tabulatorHasSelector = this.filteredColumns.filter(el => el.formatter == 'rowSelection').length;
			this.tabulator.setColumns(this.filteredColumns);
			this.tabulator.setData(this.filteredData);
		},
		/**
		 *
		 */
		getFilter() {
			if (this.selectedFilter === null)
				this.startFetchCmpt(this.$fhcApi.factory.filter.getFilter, null, this.render);
			else
				this.startFetchCmpt(
					this.$fhcApi.factory.filter.getFilterById,
					{
						filterId: this.selectedFilter
					},
					this.render
				);
		},
		/**
		 *
		 */
		render(response) {
			let data = response;
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
		},
		/**
		 * Set the menu
		 */
		setSideMenu(data) {
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
		setDropDownMenu(data) {
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
		startFetchCmpt(apiFunction, apiFunctionParameters, dataFetchedCallback) {
			// Assign the function api of the FetchCmpt binded property
			this.fetchCmptApiFunction = apiFunction;

			// In case a null value is provided set the parameters as an empty object
			if (apiFunctionParameters == null) apiFunctionParameters = {};

			// Always needed parameters
			apiFunctionParameters.filterUniqueId = FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
			apiFunctionParameters.filterType = this.filterType;

			if (this.uniqueId)
				apiFunctionParameters.filterUniqueId += '_' + this.uniqueId;

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
		handlerSaveCustomFilter(customFilterName) {
			this.selectedFilter = null;
			//
			this.startFetchCmpt(
				this.$fhcApi.factory.filter.saveCustomFilter,
				{
					customFilterName
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerRemoveCustomFilter(event) {
			let filterId = event.currentTarget.getAttribute("href").substring(1);
			if (filterId === this.selectedFilter)
				this.selectedFilter = null;
			//
			this.startFetchCmpt(
				this.$fhcApi.factory.filter.removeCustomFilter,
				{
					filterId: filterId
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
			this.selectedFilter = filterId;
			this.getFilter();
		},
		applyFilterConfig(filterFields) {
			this.selectedFilter = null;
			this.startFetchCmpt(
				this.$fhcApi.factory.filter.applyFilterFields,
				{
					filterFields
				},
				this.getFilter
			);
		},
		// TODO check ob im core so bleiben soll
		// append child to it's parent
		appendChild(data, child) {
			// get parent id
			let parentId = child[this.parentIdField];

			// loop thorugh all data
			for (let parentIdx = 0; parentIdx < data.length; parentIdx++)
			{
				let parent = data[parentIdx];

				// if it's the parent
				if (parent[this.idField] == parentId)
				{
					// create children array if not done yet
					if (!parent._children) parent._children = [];

					// if child is not included in children array, append the child
					if (!parent._children.includes(child)) parent._children.push(child);

					// parent found
					return true;
				}
				// search children for parents
				else if (parent._children) this.appendChild(parent._children, child);
			}

			// parent not found
			return false;
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
	template: `
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
				<h3 class="page-header mt-1 mb-4">
					{{ title }}
				</h3>
			</div>
		</div>

		<div :id="'filterCollapsables' + idExtra">

			<div class="d-flex flex-row justify-content-between flex-wrap">
				<div v-if="newBtnShow || reload || $slots.search || $slots.actions" class="d-flex gap-2 align-items-baseline flex-wrap">
					<button v-if="newBtnShow" class="btn btn-primary" :class="newBtnClass" :title="newBtnLabel ? undefined : 'New'" :aria-label="newBtnLabel ? undefined : 'New'" @click="$emit('click:new', $event)" :disabled="newBtnDisabled">
						<span class="fa-solid fa-plus" aria-hidden="true"></span>
						{{ newBtnLabel }}
					</button>
					<button v-if="reload" class="btn btn-outline-secondary" aria-label="Reload" @click="reloadTable">
						<span class="fa-solid fa-rotate-right" aria-hidden="true"></span>
					</button>
					<span v-if="$slots.actions && tabulatorHasSelector">Mit {{selectedData.length}} ausgewählten:</span>
					<slot name="actions" v-bind="tabulatorHasSelector ? selectedData : []"></slot>
					<slot name="search"></slot>
				</div>
				<div class="d-flex gap-1 align-items-baseline flex-grow-1 justify-content-end">
					<span v-if="!tableOnly">[ {{ filterName }} ]</span>
					<a v-if="!tableOnly" href="#" class="btn btn-link px-0 text-dark" data-bs-toggle="collapse" :data-bs-target="'#collapseFilters' + idExtra">
						<span class="fa-solid fa-xl fa-filter"></span>
					</a>
					<a href="#" class="btn btn-link px-0 text-dark" data-bs-toggle="collapse" :data-bs-target="'#collapseColumns' + idExtra">
						<span class="fa-solid fa-xl fa-table-columns"></span>
					</a>
					<table-download class="btn btn-link px-0 text-dark" :tabulator="tabulator" :config="download"></table-download>
				</div>
			</div>

			<filter-columns
				:id="'collapseColumns' + idExtra"
				class="card-body collapse"
				:data-bs-parent="'#filterCollapsables' + idExtra"
				:fields="fieldIdsForVisibilty"
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
