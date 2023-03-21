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

//
const FILTER_COMPONENT_NEW_FILTER = 'Filter Component New Filter';
const FILTER_COMPONENT_NEW_FILTER_TYPE = 'Filter Component New Filter Type';

/**
 *
 */
export const CoreFilterCmpt = {
	emits: ['nwNewEntry'],
	components: {
		CoreFetchCmpt
	},
	props: {
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
		tabulatorEvents: Array
	},
	data: function() {
		return {
			// FilterCmpt properties
			filterName: null,
			fields: null,
			dataset: null,
			datasetMetadata: null,
			selectedFields: null,
			notSelectedFields: null,
			filterFields: null,
			columnsAlias: null,

			availableFilters: null,

			// FetchCmpt binded properties
			fetchCmptRefresh: false,
			fetchCmptApiFunction: null,
			fetchCmptApiFunctionParams: null,
			fetchCmptDataFetched: null,

			tabulator: null
		};
	},
	created: function() {
		this.getFilter(); // get the filter data
	},
	updated: function() {
		//
		let dataset = JSON.parse(JSON.stringify(this.dataset));
		let fields = JSON.parse(JSON.stringify(this.fields));
		let selectedFields = JSON.parse(JSON.stringify(this.selectedFields));

		//
		let columns = null;

		// If the tabulator options has been provided and it contains the property columns
		if (this.tabulatorOptions != null && this.tabulatorOptions.hasOwnProperty('columns'))
		{
			columns = this.tabulatorOptions.columns;
		}

		// If columns is not an array or it is an array with less elements then the array fields
		if (!Array.isArray(columns) || (Array.isArray(columns) && columns.length < fields.length))
		{
			columns = []; // set it as an empty array

			// Loop throught all the retrieved columns from database
			for (let i = 0; i < fields.length; i++)
			{
				// Create a new column having the title equal to the field name
				let column = {
					title: fields[i],
					field: fields[i]
				};

				// If the column has to be displayed or not
				selectedFields.indexOf(fields[i]) >= 0 ? column.visible = true : column.visible = false;

				// Add the new column to the list of columns
				columns.push(column);
			}
		}
		else // the property columns has been provided in the tabulator options
		{
			// Loop throught the property columns of the tabulator options
			for (let i = 0; i < columns.length; i++)
			{
				// If the column has to be displayed or not
				selectedFields.indexOf(columns[i].field) >= 0 ? columns[i].visible = true : columns[i].visible = false;

                                if (columns[i].hasOwnProperty('resizable'))
				{
                        		columns[i].visible ? columns[i].resizable = true : columns[i].resizable = false;
                                }
			}
		}

		this.columnsAlias = columns;

		// Define a default tabulator options in case it was not provided
		let tabulatorOptions = {
			height: 500,
			layout: "fitColumns",
			movableColumns: true,
			reactiveData: true,
			columns: columns,
			data: JSON.parse(JSON.stringify(this.dataset))
		};

		// If it was provided
		if (this.tabulatorOptions != null)
		{
			// Then copy it...
			tabulatorOptions = this.tabulatorOptions;
			// ...and overwrite the properties data, reactiveData, movableColumns and columns
			tabulatorOptions.data = JSON.parse(JSON.stringify(this.dataset));
			tabulatorOptions.columns = columns;
			tabulatorOptions.reactiveData = true;
			tabulatorOptions.movableColumns = true;
		}

		// Start the tabulator with the buid options
		this.tabulator = new Tabulator(
			"#filterTableDataset",
			tabulatorOptions
		);

		// If event handlers have been provided
		if (Array.isArray(this.tabulatorEvents) && this.tabulatorEvents.length > 0)
		{
			// Attach all the provided event handlers to the started tabulator
			for (let i = 0; i < this.tabulatorEvents.length; i++)
			{
				this.tabulator.on(this.tabulatorEvents[i].event, this.tabulatorEvents[i].handler);
			}
		}
	},
	methods: {
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
							break;
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
					option: filters[filtersCount].filter_id,
					description: filters[filtersCount].desc
				};
			}

			for (let filtersCount = 0; filtersCount < personalFilters.length; filtersCount++)
			{
				let link = personalFilters[filtersCount].link;

				if (link == null) link = '#';

				filtersArray[filtersArray.length] = {
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
		handlerSaveCustomFilter: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.saveCustomFilter,
				{
					customFilterName: document.getElementById('customFilterName').value
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
		/**
		 *
		 */
		handlerApplyFilterFields: function(event) {
			let filterFields = [];
			let filterFieldDivRows = document.getElementById('filterFields').getElementsByClassName('row');

			for (let i = 0; i< filterFieldDivRows.length; i++)
			{
				let filterField = {};

				for (let j = 0; j< filterFieldDivRows[i].children.length; j++)
				{
					let filterColumn = filterFieldDivRows[i].children[j];
					let filterColumnElement = filterColumn.children[0];

					// If the first column then search for the fields dropdown
					if (j == 0) filterColumnElement = filterColumnElement.querySelector('select[name=fieldName]');

					// If the filter name is _not_ null and it is _not_ a new filter
					if (filterColumnElement.name != null && filterColumnElement.name != FILTER_COMPONENT_NEW_FILTER)
					{
						// Condition
						if (filterColumnElement.name == 'condition' && filterColumnElement.value == "")
						{
							alert("Please fill all the filter options");
							return;
						}

						// Name
						if (filterColumnElement.name == 'fieldName')
						{
							filterField.name = filterColumnElement.value;
						}
						// Operation
						if (filterColumnElement.name == 'operation')
						{
							filterField.operation = filterColumnElement.value;
						}
						// Condition
						if (filterColumnElement.name == 'condition')
						{
							filterField.condition = filterColumnElement.value;
						}
						// Option
						if (filterColumnElement.name == 'option')
						{
							filterField.option = filterColumnElement.value;
						}
					}
				}

				if (Object.entries(filterField).length > 0) filterFields.push(filterField);
			}

			//
			this.startFetchCmpt(
				CoreFilterAPIs.applyFilterFields,
				{
					filterFields: filterFields
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerChangeFilterField: function(oldValue, newValue) {

			// If an old filter has been changed
			if (oldValue != "")
			{
				for (let i = 0; i < this.filterFields.length; i++)
				{
					if (this.filterFields[i].name == oldValue)
					{
						this.filterFields.splice(i, 1);
						break;
					}
				}
			}

			// Then add the new filter
			for (let i = 0; i < this.datasetMetadata.length; i++)
			{
				if (this.datasetMetadata[i].name == newValue)
				{
					let filter = {
						name: this.datasetMetadata[i].name,
						type: this.datasetMetadata[i].type
					};

					this.filterFields.push(filter);
					break;
				}
			}
		},
		/**
		 *
		 */
		handlerAddNewFilter: function(event) {
			// Adds a new empty filter
			this.filterFields.push({
				name: FILTER_COMPONENT_NEW_FILTER,
				type: FILTER_COMPONENT_NEW_FILTER_TYPE
			});
		},
		/*
		 *
		 */
		handlerToggleSelectedField: function(event) {

			// If it is a selected field
			if (this.selectedFields.indexOf(event.target.innerText) != -1)
			{
				// then hide it
				this.tabulator.hideColumn(event.target.innerText);
				// and remove it from the this.selectedFields property
				this.selectedFields.splice(this.selectedFields.indexOf(event.target.innerText), 1);
			}
			else // otherwise
			{
				// show it
				this.tabulator.showColumn(event.target.innerText);
				// and add it to the this.selectedFields property
				this.selectedFields.push(event.target.innerText);
			}
		},
		/**
		 *
		 */
		handlerRemoveFilterField: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.removeFilterField,
				{
					filterField: event.currentTarget.getAttribute('field-to-remove')
				},
				this.getFilter
			);
		},
		/**
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

			// Ajax call
			this.startFetchCmpt(
				CoreFilterAPIs.getFilterById,
				{
					filterId: filterId
				},
				this.render
			);
		}
	},
	template: `
		<!-- Load filter data -->
		<core-fetch-cmpt
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

		<div id="filterCollapsables">

			<div class="filter-header-title">
				<span class="filter-header-title-span-filter">[ {{ filterName }} ]</span>
				<span data-bs-toggle="collapse" data-bs-target="#collapseFilters" class="filter-header-title-span-icon fa-solid fa-filter fa-xl"></span>
				<span data-bs-toggle="collapse" data-bs-target="#collapseColumns" class="filter-header-title-span-icon fa-solid fa-table-columns fa-xl"></span>
			</div>

			<div id="collapseColumns" class="card-body collapse" data-bs-parent="#filterCollapsables">
				<div class="card">
					<!-- Filter fields options -->
					<div class="row card-body filter-options-div">
						<div class="filter-fields-area">
							<template v-for="fieldToDisplay in fields">
								<div
									class="filter-fields-field"
									v-bind:class="selectedFields.indexOf(fieldToDisplay) != -1 ? 'text-light bg-dark' : '' "
									@click=handlerToggleSelectedField
								>
									{{ fieldToDisplay }}
								</div>
							</template>
						</div>
					</div>
				</div>
			</div>

			<div id="collapseFilters" class="card-body collapse" data-bs-parent="#filterCollapsables">
				<div class="card">
				<!-- Filter options -->
					<div class="card-body" v-if="!sideMenu">
						<select
							class="form-select"
							@change="handlerGetFilterById"
						>
							<option value="">Bitte auswählen...</option>
							<template v-for="availableFilter in availableFilters">
								<option v-bind:value="availableFilter.option">{{ availableFilter.description }}</option>
							</template>
						</select>
					</div>
					<div class="card-body filter-options-div">
						<div>
							<span>
								Neuer Filter
							</span>
							<span>
								<button class="btn btn-outline-dark" type="button" @click=handlerAddNewFilter>+</button>
							</span>
						</div>
						<div id="filterFields" class="filter-filter-fields">
							<template v-for="(filterField, index) in filterFields">
								<div class="row">

									<div class="col-5">
										<div class="input-group">
											<span class="input-group-text">Filter {{ index + 1 }}</span>
											<select
												class="form-select"
												name="fieldName"
												v-bind:value="filterField.name"
												@change="handlerChangeFilterField(filterField.name, $event.target.value)"
											>
												<option value="">Feld zum Filter hinzufügen...</option>
												<template v-for="columnAlias in columnsAlias">
													<option v-bind:value="columnAlias.field">{{ columnAlias.title }}</option>
												</template>
											</select>
										</div>
									</div>

									<!-- Numeric -->
									<template
										v-if="filterField.type.toLowerCase().indexOf('int') >= 0">
										<div class="col-2">
											<select class="form-select" name="operation" v-model="filterField.operation">
												<option value="equal">Gleich</option>
												<option value="nequal">Nicht gleich</option>
												<option value="gt">Größer als</option>
												<option value="lt">Weniger als</option>
											</select>
										</div>
										<div class="col-3">
											<input type="number" class="form-control" v-bind:value="filterField.condition" name="condition">
										</div>
										<div class="col">
											<button
												class="btn btn-outline-dark"
												type="button"
												v-bind:field-to-remove="filterField.name"
												@click=handlerRemoveFilterField>
												&emsp;X&emsp;
											</button>
										</div>
									</template>

									<!-- Text -->
									<template
										v-if="filterField.type.toLowerCase().indexOf('varchar') >= 0
											|| filterField.type.toLowerCase().indexOf('text') >= 0
											|| filterField.type.toLowerCase().indexOf('bpchar') >= 0">
										<div class="col-2">
											<select class="form-select" name="operation" v-model="filterField.operation">
												<option value="equal">Gleich</option>
												<option value="nequal">Nicht gleich</option>
												<option value="contains">Enthält</option>
												<option value="ncontains">Enthält nicht</option>
											</select>
										</div>
										<div class="col-3">
											<input type="text" class="form-control" v-bind:value="filterField.condition" name="condition">
										</div>
										<div class="col">
											<button
												class="btn btn-outline-dark"
												type="button"
												v-bind:field-to-remove="filterField.name"
												@click=handlerRemoveFilterField>
												&emsp;X&emsp;
											</button>
										</div>
									</template>

									<!-- Timestamp and date -->
									<template
										v-if="filterField.type.toLowerCase().indexOf('timestamp') >= 0
											|| filterField.type.toLowerCase().indexOf('date') >= 0">
										<div class="col-2">
											<select class="form-select" name="operation" v-model="filterField.operation">
												<option value="gt">Größer als</option>
												<option value="lt">Weniger als</option>
												<option value="set">Eingestellt ist</option>
												<option value="nset">Eingestellt nicht ist</option>
											</select>
										</div>
										<div class="col-1">
											<input type="number" class="form-control" v-bind:value="filterField.condition" name="condition">
										</div>
										<div class="col-2">
											<select class="form-select" name="option" v-model="filterField.option">
												<option value="minutes">Minuten</option>
												<option value="hours">Stunden</option>
												<option value="days">Tage</option>
												<option value="months">Monate</option>
											</select>
										</div>
										<div class="col">
											<button
												class="btn btn-outline-dark"
												type="button"
												v-bind:field-to-remove="filterField.name"
												@click=handlerRemoveFilterField
											> - </button>
										</div>
									</template>
								</div>
							</template>
						</div>

						<!-- Filter save options -->
						<div class="row">
							<div class="col-7">
								<div class="input-group">
									<input type="text" class="form-control" placeholder="Filternamen eingeben..." id="customFilterName">
									<button type="button" class="btn btn-outline-secondary" @click=handlerSaveCustomFilter>Filter speichern</button>
								</div>
							</div>
							<div class="col">
								<button type="button" class="btn btn-outline-dark" @click=handlerApplyFilterFields>Filter anwenden</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Tabulator -->
		<div id="filterTableDataset" class="filter-table-dataset"></div>
	`
};

