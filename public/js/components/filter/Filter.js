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
			notFilterFields: null,

			// FetchCmpt binded properties
			fetchCmptRefresh: false,
			fetchCmptApiFunction: null,
			fetchCmptApiFunctionParams: null,
			fetchCmptDataFetched: null
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

                                if( columns[i].hasOwnProperty('resizable') ) {
                                  columns[i].visible ? columns[i].resizable = true : columns[i].resizable = false;
                                }
			}
		}

		// Define a default tabulator options in case it was not provided
		let tabulatorOptions = {
			height: 500,
			layout: "fitColumns",
			columns: columns,
			data: JSON.parse(JSON.stringify(this.dataset)),
			reactiveData: true
		};

		// If it was provided
		if (this.tabulatorOptions != null)
		{
			// Then copy it...
			tabulatorOptions = this.tabulatorOptions;
			// ...and overwrite the properties data, reactiveData and columns
			tabulatorOptions.data = JSON.parse(JSON.stringify(this.dataset));
			tabulatorOptions.reactiveData = true;
			tabulatorOptions.columns = columns;
		}

		// Start the tabulator with the buid options
		let tabulator = new Tabulator(
			"#filterTableDataset",
			tabulatorOptions
		);

		// If event handlers have been provided
		if (Array.isArray(this.tabulatorEvents) && this.tabulatorEvents.length > 0)
		{
			// Attach all the provided event handlers to the started tabulator
			for (let i = 0; i < this.tabulatorEvents.length; i++)
			{
				tabulator.on(this.tabulatorEvents[i].event, this.tabulatorEvents[i].handler);
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
				let tmpFilterFields = [];
				for (let i = 0; i < data.datasetMetadata.length; i++)
				{
					for (let j = 0; j< data.filters.length; j++)
					{
						if (data.datasetMetadata[i].name == data.filters[j].name)
						{
							let filter = data.filters[j];
							filter.type = data.datasetMetadata[i].type;

							this.filterFields.push(filter);
							tmpFilterFields.push(filter.name);
							break;
						}
					}
				}

				this.notFilterFields = this.fields.filter(x => tmpFilterFields.indexOf(x) === -1);
				this.setSideMenu(data);
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
					sort: filtersCount,
					onClickCall: this.handlerGetFilterById
				};
			}

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

					if (filterColumnElement.name != null)
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
		handlerAddFilterField: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.addFilterField,
				{
					filterField: event.currentTarget.value
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerAddSelectedField: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.addSelectedField,
				{
					selectedField: event.currentTarget.value
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerRemoveSelectedField: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.removeSelectedField,
				{
					selectedField: event.currentTarget.getAttribute('field-to-remove')
				},
				this.getFilter
			);
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
			//
			this.startFetchCmpt(
				CoreFilterAPIs.getFilterById,
				{
					filterId: event.currentTarget.getAttribute("href").substring(1)
				},
				this.render
			);
		},
		handlerDragOver: function(event) {
			let draggedFieldToDisplay = event.currentTarget;
			let fieldsToDisplayDivs = document.getElementsByClassName('filter-dnd-object');
			let filterFilterOptions = document.getElementsByClassName('filter-filter-options')[0];

			// For each draggable element
			for (let i = 0; i < fieldsToDisplayDivs.length; i++)
			{
				let fieldToDisplayDiv = fieldsToDisplayDivs[i]; //

				// If the dragged element is not the same element in the loop
				if (draggedFieldToDisplay != fieldToDisplayDiv)
				{
					fieldToDisplayDiv.classList.remove("selection-after");
					fieldToDisplayDiv.classList.remove("selection-before");

					let fieldToDisplayDivCenter = (filterFilterOptions.offsetLeft + fieldToDisplayDiv.offsetLeft + fieldToDisplayDiv.offsetWidth) / 2;

					if (event.pageX > filterFilterOptions.offsetLeft + fieldToDisplayDiv.offsetLeft
						&& event.pageX < filterFilterOptions.offsetLeft + fieldToDisplayDiv.offsetLeft + fieldToDisplayDiv.offsetWidth)
					{
						if (event.pageX > fieldToDisplayDivCenter)
						{
							fieldToDisplayDiv.classList.add("selection-after");
							fieldToDisplayDiv.classList.remove("selection-before");
						}
						else if (event.pageX < fieldToDisplayDivCenter)
						{
							fieldToDisplayDiv.classList.add("selection-before");
							fieldToDisplayDiv.classList.remove("selection-after");
						}
					}
				}
			}
		},
		handlerOnDrop: function() {
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

		<div class="row">
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
						<div class="filter-dnd-area">
							<template v-for="fieldToDisplay in selectedFields">
								<div
									class="filter-dnd-object" draggable="true" @dragover="handlerDragOver">
									{{ fieldToDisplay}}
									<button
										type="button"
										class="btn-close"
										v-bind:field-to-remove="fieldToDisplay"
										@click=handlerRemoveSelectedField>
									</button>
								</div>
							</template>
						</div>
						<div class="col-7">
							<select class="form-select" @change=handlerAddSelectedField>
								<option value="">Wählen Sie ein anzuzeigendes Feld aus...</option>
								<template v-for="hiddenField in notSelectedFields">
									<option v-bind:value="hiddenField">{{ hiddenField }}</option>
								</template>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div id="collapseFilters" class="card-body collapse" data-bs-parent="#filterCollapsables">
				<div class="card">
				<!-- Filter options -->
					<div class="card-body filter-options-div">
						<div class="col-9">
							<select class="form-select" @change=handlerAddFilterField>
								<option value="">Feld zum Filter hinzufügen...</option>
								<template v-for="notFilterField in notFilterFields">
									<option v-bind:value="notFilterField">{{ notFilterField}}</option>
								</template>
							</select>
						</div>
						<div id="filterFields" class="filter-filter-fields">
							<template v-for="filterField in filterFields">

								<!-- Numeric -->
								<div v-if="filterField.type.toLowerCase().indexOf('int') >= 0" class="row">
									<div class="col-3">
										<input type="hidden" name="fieldName" v-bind:value="filterField.name">
										<span class="input-group-text">{{ filterField.name}}</span>
									</div>
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
								</div>

								<!-- Text -->
								<div
									v-if="filterField.type.toLowerCase().indexOf('varchar') >= 0
										|| filterField.type.toLowerCase().indexOf('text') >= 0
										|| filterField.type.toLowerCase().indexOf('bpchar') >= 0"
									class="row">
									<div class="col-3">
										<input type="hidden" name="fieldName" v-bind:value="filterField.name">
										<span class="input-group-text">{{ filterField.name}}</span>
									</div>
									<div class="col-2">
										<select class="form-select" name="operation" v-model="filterField.operation">
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
								</div>

								<!-- Timestamp and date -->
								<div
									v-if="filterField.type.toLowerCase().indexOf('timestamp') >= 0
										|| filterField.type.toLowerCase().indexOf('date') >= 0"
									class="row">
									<div class="col-3">
										<input type="hidden" name="fieldName" v-bind:value="filterField.name">
										<span class="input-group-text">{{ filterField.name}}</span>
									</div>
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
											@click=handlerRemoveFilterField>
											&emsp;X&emsp;
										</button>
									</div>
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

