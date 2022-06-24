export const CoreFilterCmpt = {
	emits: ['nwNewEntry'],
	data() {
		return {
			fields: null,
			fieldsToDisplay: null,
			dataset: null,
			selectedFields: null,
			notSelectedFields: null,
			filterFields: null,
			notFilterFields: null
		};
	},
	created() {
		this._fetchFilterData();
	},
	updated() {
		let filterCmptTablesorter = $("#filterTableDataset");

		// Checks if the table contains data (rows)
		if (filterCmptTablesorter.find("tbody:empty").length == 0
			&& filterCmptTablesorter.find("tr:empty").length == 0)
		{
			filterCmptTablesorter.tablesorter({
				dateFormat: "ddmmyyyy",
				widgets: ["zebra", "filter"],
				widgetOptions: {
					filter_saveFilters : true
				}
			});

			$.tablesorter.updateAll(filterCmptTablesorter[0].config, true, null);
		}
	},
	props: {
		filterType: {
			type: String,
			required: true
		}
	},
	methods: {
		saveCustomFilter(el) {
			FHC_AjaxClient.ajaxCallPost(
				"components/Filter/saveCustomFilter",
				{
					filterUniqueId: this._getCurrentPage(),
					filterType: this.filterType,
					customFilterName: document.getElementById('customFilterName').value
				},   
				{
					successCallback: function(data) {console.log(data)}
				}
			);
		},
		applyFilterFields(el) {
			let filterFields = [];
			let filterFieldDivs = document.getElementById('filterFields').getElementsByTagName('div');

			for (let i = 0; i < filterFieldDivs.length; i++)
			{
				let filterField = {};

				for (let j = 0; j < filterFieldDivs[i].children.length; j++)
				{
					if (filterFieldDivs[i].children[j].name != null)
					{
						// Name
						if (filterFieldDivs[i].children[j].name == 'fieldName')
						{
							filterField.name = filterFieldDivs[i].children[j].value;
						}
						// Operation
						if (filterFieldDivs[i].children[j].name == 'operation')
						{
							filterField.operation = filterFieldDivs[i].children[j].value;
						}
						// Condition
						if (filterFieldDivs[i].children[j].name == 'condition')
						{
							filterField.condition = filterFieldDivs[i].children[j].value;
						}
						// Option
						if (filterFieldDivs[i].children[j].name == 'option')
						{
							filterField.option = filterFieldDivs[i].children[j].value;
						}
					}
				}

				filterFields.push(filterField);
			}

			FHC_AjaxClient.ajaxCallPost(
				"components/Filter/applyFilterFields",
				{
					filterUniqueId: this._getCurrentPage(),
					filterType: this.filterType,
					filterFields: filterFields
				},   
				{
					successCallback: this._fetchFilterData
				}
			);
		},
		addFilterField(el) {
			FHC_AjaxClient.ajaxCallPost(
				"components/Filter/addFilterField",
				{
					filterUniqueId: this._getCurrentPage(),
					filterType: this.filterType,
					filterField: el.currentTarget.value
				},   
				{
					successCallback: this._fetchFilterData
				}
			);
		},
		addSelectedField(el) {
			FHC_AjaxClient.ajaxCallPost(
				"components/Filter/addSelectedField",
				{
					filterUniqueId: this._getCurrentPage(),
					filterType: this.filterType,
					selectedField: el.currentTarget.value
				},   
				{
					successCallback: this._fetchFilterData
				}
			);
		},
		removeSelectedField(el) {
			FHC_AjaxClient.ajaxCallPost(
				"components/Filter/removeSelectedField",
				{
					filterUniqueId: this._getCurrentPage(),
					filterType: this.filterType,
					selectedField: el.currentTarget.getAttribute('field-to-remove')
				},   
				{
					successCallback: this._fetchFilterData
				}
			);
		},
		removeFilterField(el) {
			FHC_AjaxClient.ajaxCallPost(
				"components/Filter/removeFilterField",
				{
					filterUniqueId: this._getCurrentPage(),
					filterType: this.filterType,
					filterField: el.currentTarget.getAttribute('field-to-remove')
				},   
				{
					successCallback: this._fetchFilterData
				}
			);
		},
		fetchFilterDataById(el) {
			FHC_AjaxClient.ajaxCallGet(
				"components/Filter/getFilter",
				{
					filterUniqueId: this._getCurrentPage(),
					filterType: this.filterType,
					filter_id: el.currentTarget.getAttribute("href").substring(1)
				},   
				{
					successCallback: this._render
				}
			);
		},
		_fetchFilterData() {
			FHC_AjaxClient.ajaxCallGet(
				"components/Filter/getFilter",
				{
					filterUniqueId: this._getCurrentPage(),
					filterType: this.filterType // props!!
				},   
				{
					successCallback: this._render
				}
			);
		},
		_getCurrentPage: function() {
			return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
		},
		_render(data) {
		
			if (FHC_AjaxClient.hasData(data))
			{
				this.dataset = FHC_AjaxClient.getData(data).dataset;
				this.fields = FHC_AjaxClient.getData(data).fields;
				this.selectedFields = FHC_AjaxClient.getData(data).selectedFields;
				this.notSelectedFields = this.fields.filter(x => this.selectedFields.indexOf(x) === -1);

				this.filterFields = [];
				let tmpFilterFields = [];
				for (let i = 0; i < FHC_AjaxClient.getData(data).datasetMetadata.length; i++)
				{
					for (let j = 0; j < FHC_AjaxClient.getData(data).filters.length; j++)
					{
						if (FHC_AjaxClient.getData(data).datasetMetadata[i].name == FHC_AjaxClient.getData(data).filters[j].name)
						{
							let filter = FHC_AjaxClient.getData(data).filters[j];
							filter.type = FHC_AjaxClient.getData(data).datasetMetadata[i].type;

							this.filterFields.push(filter);
							tmpFilterFields.push(filter.name);
							break;
						}
					}
				}

				this.notFilterFields = this.fields.filter(x => tmpFilterFields.indexOf(x) === -1);
				this._setFieldsToDisplay(FHC_AjaxClient.getData(data));
				this._setSideMenu(FHC_AjaxClient.getData(data));
			}
			else
			{
				console.error(FHC_AjaxClient.getError(data));
			}
		},
		_setFieldsToDisplay(data) {

			let arrayFieldsToDisplay = [];
	
			if (data.hasOwnProperty("selectedFields") && $.isArray(data.selectedFields))
			{
				if (data.hasOwnProperty("columnsAliases") && $.isArray(data.columnsAliases))
				{
					for (let sfc = 0; sfc < data.selectedFields.length; sfc++)
					{
						for (let fc = 0; fc < data.fields.length; fc++)
						{
							if (data.selectedFields[sfc] == data.fields[fc])
							{
								arrayFieldsToDisplay[sfc] = data.columnsAliases[fc];
							}
						}
					}
				}
				else
				{
					arrayFieldsToDisplay = data.selectedFields;
				}
			}
	
			this.fieldsToDisplay = arrayFieldsToDisplay;
		},
		_setSideMenu(data) {
			// Set the menu
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
					onClickCall: this.fetchFilterDataById
				};
			}

			this.$emit(
				'nwNewEntry',
				[{
					link: "#",
					description: "Filters",
					icon: "filter",
					children: filtersArray
				}]
			);
		}
	},
	template: `
		<div class="card filter-filter-options">
			<div class="card-header filter-header-title" data-bs-toggle="collapse" data-bs-target="#collapseFilterHeader">
				Filter options
			</div>
			<div id="collapseFilterHeader" class="card-body collapse">
				<!-- Filter fields options -->
				<div class="filter-options-div">
					<div class="filter-dnd-area">
						<template v-for="fieldToDisplay in fieldsToDisplay">
							<span class="filter-dnd-object">
								{{ fieldToDisplay }}
								<button
									type="button"
									class="btn-close"
									v-bind:field-to-remove="fieldToDisplay"
									@click=removeSelectedField>
								</button>
							</span>
						</template>
					</div>
					<select class="form-select form-select-sm" @change=addSelectedField>
						<option value="">Select a field to be displayed...</option>
						<template v-for="hiddenField in notSelectedFields">
							<option v-bind:value="hiddenField">{{ hiddenField }}</option>
						</template>
					</select>
				</div>

				<!-- Filter options -->
				<div class="filter-options-div">
					<div>
						<select class="form-select form-select-sm" @change=addFilterField>
							<option value="">Add a field to the filter...</option>
							<template v-for="notFilterField in notFilterFields">
								<option v-bind:value="notFilterField">{{ notFilterField }}</option>
							</template>
						</select>
					</div>
					<div id="filterFields" class="filter-filter-fields">
						<template v-for="filterField in filterFields">
							<!-- Numeric -->
							<div v-if="filterField.type.toLowerCase().indexOf('int') >= 0" class="input-group mb-3">
								<input type="hidden" name="fieldName" v-bind:value="filterField.name">
								<span class="input-group-text">{{ filterField.name }}</span>
								<select class="form-select form-select-sm" name="operation">
									<option value="equal">Equal</option>
									<option value="nequal">Not equal</option>
									<option value="gt">Greater then</option>
									<option value="lt">Less then</option>
								</select>
								<input type="number" class="form-control" v-bind:value="filterField.condition" name="condition">
								<button
									class="btn btn-sm btn-outline-dark"
									type="button"
									v-bind:field-to-remove="filterField.name"
									@click=removeFilterField>
									&emsp;X&emsp;
								</button>
							</div>

							<!-- Text -->
							<div
								v-if="filterField.type.toLowerCase().indexOf('varchar') >= 0
									|| filterField.type.toLowerCase().indexOf('text') >= 0
									|| filterField.type.toLowerCase().indexOf('bpchar') >= 0"
								class="input-group mb-3">
								<input type="hidden" name="fieldName" v-bind:value="filterField.name">
								<span class="input-group-text">{{ filterField.name }}</span>
								<select class="form-select form-select-sm" name="operation">
									<option value="contains">Conrains</option>
									<option value="ncontains">Does not contain</option>
								</select>
								<input type="text" class="form-control" v-bind:value="filterField.condition" name="condition">
								<button
									class="btn btn-sm btn-outline-dark"
									type="button"
									v-bind:field-to-remove="filterField.name"
									@click=removeFilterField>
									&emsp;X&emsp;
								</button>
							</div>

							<!-- Timestamp and date -->
							<div
								v-if="filterField.type.toLowerCase().indexOf('timestamp') >= 0
									|| filterField.type.toLowerCase().indexOf('date') >= 0"
								class="input-group mb-3">
								<input type="hidden" name="fieldName" v-bind:value="filterField.name">
								<span class="input-group-text">{{ filterField.name }}</span>
								<select class="form-select form-select-sm" name="operation">
									<option value="gt">Greater then</option>
									<option value="lt">Less then</option>
									<option value="set">Is set</option>
									<option value="nset">Is not set</option>
								</select>
								<input type="number" class="form-control" v-bind:value="filterField.condition" name="condition">
								<select class="form-select form-select-sm" name="option">
									<option value="minutes">Minutes</option>
									<option value="hours">Hours</option>
									<option value="days">Days</option>
									<option value="months">Months</option>
								</select>
								<button
									class="btn btn-sm btn-outline-dark"
									type="button"
									v-bind:field-to-remove="filterField.name"
									@click=removeFilterField>
									&emsp;X&emsp;
								</button>
							</div>

						</template>
					</div>
					<div>
						<button type="button" class="btn btn-sm btn-outline-dark" @click=applyFilterFields>Apply changes</button> 
					</div>
				</div>

				<!-- Filter save options -->
				<div class="input-group">
					<input type="text" class="form-control" placeholder="Custom filter name" id="customFilterName">
					<button type="button" class="btn btn-outline-secondary" @click=saveCustomFilter>Save</button>
				</div>
			</div>
		</div>

		<table class="tablesorter table-bordered table-responsive" id="filterTableDataset">
			<thead>
				<tr>
					<template v-for="fieldToDisplay in fieldsToDisplay">
						<th title='{{ fieldToDisplay }}'>{{ fieldToDisplay }}</th>
					</template>
				</tr>
			</thead>
			<tbody>
				<template v-for="record in dataset">
					<tr class="">
						<template v-for="(value, property) in record">
							<template v-if="selectedFields.includes(property)">
								<td>{{ value }}</td>
							</template>
						</template>
					</tr>
				</template>
			</tbody>
		</table>
	`
};

