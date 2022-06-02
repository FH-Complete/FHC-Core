const CoreFilterCmpt = {
	emits: ['nwNewEntry'],
	data() {
		return {
			fieldsToDisplay: null,
			dataset: null,
			selectedFields: null
		};
	},
	created() {
		this.fetchFilterData();
		this.fetchFilterMenuData();
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
		fetchFilterData() {
			FHC_AjaxClient.ajaxCallGet(
				"components/Filter/getFilter",
				{
					filterUniqueId: this.getFilterUniqueIdPrefix(),
					filterType: this.filterType, // props!!
					filterId: 170
				},   
				{
					successCallback: this.renderTableSorter
				}
			);
		},
		fetchFilterMenuData() {
			FHC_AjaxClient.ajaxCallGet(
				"components/Filter/generateFilterMenu",
				{
					filterUniqueId: this.getFilterUniqueIdPrefix(),
					navigation_page: this.getNavigationPage()
				},   
				{
					successCallback: this.setSideMenu
				}
			);
		},
		setSideMenu(data) {
			// Set the menu
			if (FHC_AjaxClient.hasData(data))
			{
				let filters = FHC_AjaxClient.getData(data).filters;
				let personalFilters = FHC_AjaxClient.getData(data).personalFilters;
				let filtersArray = [];

				for (let filtersCount = 0; filtersCount < filters.length; filtersCount++)
				{
					filtersArray[filtersArray.length] = {
						link: filters[filtersCount].link,
                        	                description: filters[filtersCount].desc,
                        	                sort: filtersCount
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
		getFilterUniqueIdPrefix() {
			return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
		},
		getNavigationPage: function() {
			return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
		},
		renderTableSorter(data) {
		
			if (FHC_AjaxClient.hasData(data))
			{
				this._setFieldsToDisplay(FHC_AjaxClient.getData(data));
				this.dataset = FHC_AjaxClient.getData(data).dataset;
				this.selectedFields = FHC_AjaxClient.getData(data).selectedFields;
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
		}
	},
	template: `
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

