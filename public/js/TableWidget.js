/**
 * TableWidget JS magic
 */

//--------------------------------------------------------------------------------------------------------------------
// Constants

//
const DATASET_REP_TABLESORTER = "tablesorter";
const DATASET_REP_PIVOTUI = "pivotUI";
const DATASET_REP_TABULATOR = "tabulator";

/**
 * FHC_TableWidget this object is used to render the GUI of a table widget and to operate with it
 */
var FHC_TableWidget = {

	//------------------------------------------------------------------------------------------------------------------
	// Properties

	_datasetRepresentation: null, // contains the current data representation

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * To display the TableWidget using the loaded data prenset in the session
	 */
	display: function() {

		FHC_TableWidget._getTables(FHC_TableWidget._renderTableWidget);
	},

	/**
	 * Alias call to method display only to inprove the readability of the code
	 */
	refresh: function() {

		FHC_TableWidget.display();
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Utility method that checks if data contains an error and print that to the console
	 * otherwise the TableWidget GUI is refreshed
	 */
	_failOrRefresh: function(data, textStatus, jqXHR) {

		if (FHC_AjaxClient.isError(data))
		{
			console.log(FHC_AjaxClient.getError(data));
		}
		else
		{
			FHC_TableWidget.refresh();
		}
	},

	/**
	 * Utility method that checks if data contains an error and print that to the console
	 * otherwise the page is reloaded
	 */
	_failOrReload: function(data, textStatus, jqXHR) {

		if (FHC_AjaxClient.isError(data))
		{
			console.log(FHC_AjaxClient.getError(data));
		}
		else
		{
			location.reload();
		}
	},

	/**
	 * To reset the Table Widget GUI
	 */
	_resetGUI: function(tableWidgetDiv) {

		// If the choosen dataset representation is tablesorter
		if (FHC_TableWidget._datasetRepresentation == DATASET_REP_TABLESORTER)
		{
			tableWidgetDiv.find("#tableWidgetTableDataset > thead > tr").html("");
			tableWidgetDiv.find("#tableWidgetTableDataset > tbody").html("");
		}

		// If the choosen dataset representation is pivotUI
		if (FHC_TableWidget._datasetRepresentation == DATASET_REP_PIVOTUI)
		{
			tableWidgetDiv.find("#tableWidgetPivotUI").html("");
		}

		// If the choosen dataset representation is tabulator
		if (FHC_TableWidget._datasetRepresentation == DATASET_REP_TABULATOR)
		{
			tableWidgetDiv.find("#tableWidgetTabulator").html("");
		}
	},

	/**
	 * To get via Ajax all the data related to the TableWidget present in the given page
	 * If the parameter renderFunction is a valid function, is called on success
	 */
	_getTables: function(renderFunction) {

		var tableWidgetUniqueIdArray = FHC_TableWidget._getTableWidgetUniqueIdArray();

		for (var tableWidgetsCounter = 0; tableWidgetsCounter < tableWidgetUniqueIdArray.length; tableWidgetsCounter++)
		{

			FHC_AjaxClient.ajaxCallGet(
				"widgets/Tables/getTable",
				{
					tableUniqueId: tableWidgetUniqueIdArray[tableWidgetsCounter]
				},
				{
					successCallback: function(data, textStatus, jqXHR) {

						if (FHC_AjaxClient.hasData(data))
						{
							if (typeof renderFunction == "function")
							{
								renderFunction(FHC_AjaxClient.getData(data));
							}
						}
						else
						{
							console.log(FHC_AjaxClient.getError(data));
						}
					}
				}
			);
		}
	},

	/**
	 * This method calls all the other methods needed to rendere the GUI for a TableWidget
	 * The parameter data contains all the data about the TableWidget and it is given as parameter
	 * to all the methods that here are called
	 * NOTE: think very carefully before changing the order of the calls
	 */
	_renderTableWidget: function(data) {

		FHC_TableWidget._setDatasetRepresentation(data); // set what type of dataset representation was choosen

		var tableWidgetDiv = $('div[tableUniqueId="' + data.tableUniqueId + '"]');

		FHC_TableWidget._turnOffEvents(tableWidgetDiv); // turns all the events off

		FHC_TableWidget._resetGUI(tableWidgetDiv); // Reset the entire GUI

		FHC_TableWidget._renderDataset(tableWidgetDiv, data);

		FHC_TableWidget._turnOnEvents(tableWidgetDiv); // turns all the events off
	},

	/**
	 * Turns all the events off
	 * NOTE: must be aligned to _turnOnEvents
	 */
	_turnOffEvents: function(tableWidgetDiv) {

		// If the choosen dataset representation is tablesorter
		if (FHC_TableWidget._datasetRepresentation == DATASET_REP_TABLESORTER)
		{
			FHC_TableWidget._disableTablesorter(tableWidgetDiv); // disable the tablesorter
		}
	},

	/**
	 * Turns all the events on
	 * NOTE: must be aligned to _turnOffEvents
	 */
	_turnOnEvents: function(tableWidgetDiv) {

		// If the choosen dataset representation is tablesorter
		if (FHC_TableWidget._datasetRepresentation == DATASET_REP_TABLESORTER)
		{
			FHC_TableWidget._enableTableSorter(tableWidgetDiv); // enable the tablesorter
		}
	},

	_renderDataset: function(tableWidgetDiv, data) {

		// If the choosen dataset representation is tablesorter then...
		if (FHC_TableWidget._datasetRepresentation == DATASET_REP_TABLESORTER)
		{
			FHC_TableWidget._renderDatasetTablesorter(tableWidgetDiv, data); // ...render the tablesorter GUI
		}

		// If the choosen dataset representation is pivotUI then...
		if (FHC_TableWidget._datasetRepresentation == DATASET_REP_PIVOTUI)
		{
			FHC_TableWidget._renderDatasetPivotUI(tableWidgetDiv, data); // ...render the pivotUI GUI
		}

		// If the choosen dataset representation is tabulator then...
		if (FHC_TableWidget._datasetRepresentation == DATASET_REP_TABULATOR)
		{
			FHC_TableWidget._renderDatasetTabulator(tableWidgetDiv, data); // ...render the tabulator GUI
		}
	},

	/**
	 * Renders the tablesorter for the TableWidget
	 * The data to be displayed are retrived from the parameter data
	 */
	_renderDatasetTablesorter: function(tableWidgetDiv, data) {

		if (data.hasOwnProperty("checkboxes") && data.checkboxes != null && data.checkboxes.trim() != "")
		{
			tableWidgetDiv.find("#tableWidgetTableDataset > thead > tr").append("<th data-filter='false' title='Select'>Select</th>");
		}

		var arrayFieldsToDisplay = FHC_TableWidget._getFieldsToDisplay(data);

		for (var i = 0; i < arrayFieldsToDisplay.length; i++)
		{
			var columnName = arrayFieldsToDisplay[i];

			tableWidgetDiv.find("#tableWidgetTableDataset > thead > tr").append("<th title='" + columnName + "'>" + columnName + "</th>");
		}

		if (data.hasOwnProperty("additionalColumns") && $.isArray(data.additionalColumns))
		{
			for (var i = 0; i < data.additionalColumns.length; i++)
			{
				var columnName = data.additionalColumns[i];

				tableWidgetDiv.find("#tableWidgetTableDataset > thead > tr").append("<th title='" + columnName + "'>" + columnName + "</th>");
			}
		}

		if (arrayFieldsToDisplay.length > 0)
		{
			if (data.hasOwnProperty("dataset") && $.isArray(data.dataset))
			{
				if (data.checkboxes != null && data.checkboxes != "")
				{
					// select checkbox range with shift key
					if (typeof tableWidgetDiv.find("#tableWidgetTableDataset").checkboxes === 'function')
						tableWidgetDiv.find("#tableWidgetTableDataset").checkboxes("range", true);
				}

				for (var i = 0; i < data.dataset.length; i++)
				{
					var record = data.dataset[i];

					if ($.isEmptyObject(record))
					{
						continue;
					}

					var strHtml = "<tr class='" + record.MARK_ROW_CLASS + "'>";

					if (data.checkboxes != null && data.checkboxes != "")
					{
						strHtml += "<td>";
						strHtml += "<input type='checkbox' name='" + data.checkboxes + "[]' value='" + record[data.checkboxes] + "'>";
						strHtml += "</td>";
					}

					$.each(arrayFieldsToDisplay, function(i, fieldToDisplay) {

						if (record.hasOwnProperty(data.fields[i]))
						{
							strHtml += "<td>" + record[data.fields[i]] + "</td>";
						}
					});

					if (data.additionalColumns != null && $.isArray(data.additionalColumns))
					{
						$.each(data.additionalColumns, function(i, additionalColumn) {

							if (record.hasOwnProperty(additionalColumn))
							{
								strHtml += "<td>" + record[additionalColumn] + "</td>";
							}
						});
					}

					strHtml += "</tr>";

					tableWidgetDiv.find("#tableWidgetTableDataset > tbody").append(strHtml);
				}
			}
		}
	},

	/**
	 * Enable the tablesorter libs to render the dataset table with sorting features
	 */
	_enableTableSorter: function(tableWidgetDiv) {

		var tableWidgetTablesorter = tableWidgetDiv.find("#tableWidgetTableDataset");

		// Checks if the table contains data (rows)
		if (tableWidgetTablesorter.find("tbody:empty").length == 0
			&& tableWidgetTablesorter.find("tr:empty").length == 0
			&& tableWidgetTablesorter.hasClass("table-condensed"))
		{
			tableWidgetTablesorter.tablesorter({
				dateFormat: "ddmmyyyy",
				widgets: ["zebra", "filter"],
				widgetOptions: {
					filter_saveFilters : true
				}
			});

			$.tablesorter.updateAll(tableWidgetTablesorter[0].config, true, null);
		}
	},

	/**
	 * Disable the tablesorter
	 */
	_disableTablesorter: function(tableWidgetDiv) {

		tableWidgetDiv.find("#tableWidgetTableDataset").trigger("disable");
	},

	/**
	 * Renders the pivotUI for the TableWidget
	 * The data to be displayed are retrived from the parameter data
	 */
	_renderDatasetPivotUI: function(tableWidgetDiv, data) {

		// Checks if options were given and returns them
		var options = FHC_TableWidget._getRepresentationOptions(data);

		// Manipulation for the representation!
		var arrayFieldsToDisplay = FHC_TableWidget._getFieldsToDisplay(data);

		// If there are fields to be displayed...
		if (arrayFieldsToDisplay.length > 0)
		{
			// ...if there are data to be displayed...
			if (data.hasOwnProperty("dataset") && $.isArray(data.dataset))
			{
				// Build the array of objects used by pivotUI and store it in pivotUIData
				var pivotUIData = [];

				// Loops through data
				for (var i = 0; i < data.dataset.length; i++)
				{
					var record = data.dataset[i]; // Single record
					var tmpObj = {}; // New object that represents a record

					// Loops through columns of a record
					$.each(arrayFieldsToDisplay, function(i, fieldToDisplay) {

						if (record.hasOwnProperty(data.fields[i]))
						{
							tmpObj[fieldToDisplay] = record[data.fields[i]]; // Add data with the column alias
						}
					});

					// If additional columns are present...
					if (data.additionalColumns != null && $.isArray(data.additionalColumns))
					{
						// ...loops through them
						$.each(data.additionalColumns, function(i, additionalColumn) {

							if (record.hasOwnProperty(additionalColumn))
							{
								tmpObj[additionalColumn] = record[additionalColumn]; // Add the additional column
							}
						});
					}

					pivotUIData.push(tmpObj); // Add tmpObj to pivotUIData
				}

				// Renders the pivotUI
				tableWidgetDiv.find("#tableWidgetPivotUI").pivotUI(
		            pivotUIData,
					options
		        );
			}
		}
	},

	/**
	 * Retrives the fields to be displayed from the data parameter, if aliases are present then they are used
	 */
	_getFieldsToDisplay: function(data) {

		var arrayFieldsToDisplay = [];

		if (data.hasOwnProperty("fields") && $.isArray(data.fields))
		{
			if (data.hasOwnProperty("columnsAliases") && $.isArray(data.columnsAliases))
			{
				for (var sfc = 0; sfc < data.fields.length; sfc++)
				{
					for (var fc = 0; fc < data.fields.length; fc++)
					{
						if (data.fields[sfc] == data.fields[fc])
						{
							arrayFieldsToDisplay[sfc] = data.columnsAliases[fc];
						}
					}
				}
			}
			else
			{
				arrayFieldsToDisplay = data.fields;
			}
		}

		return arrayFieldsToDisplay;
	},

	/**
	 * Renders the tabulator for the TableWidget
	 * The data to be displayed are retrived from the parameter data
	 */
	_renderDatasetTabulator: function(tableWidgetDiv, data) {

		// Checks if options were given and returns them
		var options = FHC_TableWidget._getRepresentationOptions(data);
		// Checks if record fields definitions were given and returns them
		var recordFieldsDefinitions = FHC_TableWidget._getRepresentationFieldsDefinitions(data);

		// Manipulation for the representation!
		var arrayTabulatorColumns = FHC_TableWidget._getTabulatorColumns(data, recordFieldsDefinitions);

		if (arrayTabulatorColumns.length > 0)
		{
			// ...if there are data to be displayed...
			if (data.hasOwnProperty("dataset") && $.isArray(data.dataset))
			{
				if (options == null) options = {};

				options.columns = arrayTabulatorColumns;
				options.data = data.dataset;

				// Renders the tabulator
				tableWidgetDiv.find("#tableWidgetTabulator").tabulator(options);
			}
		}
	},

	/**
	 * Retrives the fields to be displayed from the data parameter, if aliases are present then they are used
	 */
	_getTabulatorColumns: function(data, recordFieldsDefinitions) {

		var fieldsToDisplayTabulator = [];

		if (data.hasOwnProperty("fields") && $.isArray(data.fields))
		{
			for (var sfc = 0; sfc < data.fields.length; sfc++)
			{
				for (var fc = 0; fc < data.fields.length; fc++)
				{
					if (data.fields[sfc] == data.fields[fc])
					{
						// Build the array of objects (columns) used by tabulator and store it in tabulatorColumns
						var tmpColumnObj = {}; // New object that represents a column

						// If was given a definition for this field then use it!
						if (recordFieldsDefinitions != null && recordFieldsDefinitions.hasOwnProperty(data.fields[sfc]))
						{
							tmpColumnObj = recordFieldsDefinitions[data.fields[sfc]];
						}

						tmpColumnObj.field = data.fields[sfc]; // Field name to be linked with dataset field name

						// If there is an alias for this field use it to give a title to this field (header)
						if (data.hasOwnProperty("columnsAliases") && $.isArray(data.columnsAliases))
						{
							tmpColumnObj.title = data.columnsAliases[fc];
						}
						else // otherwise use the field name itself
						{
							tmpColumnObj.title = data.fields[sfc];
						}

						fieldsToDisplayTabulator.push(tmpColumnObj); // Add tmpColumnObj to tabulatorColumns
					}
				}
			}
		}

		// If additional columns are present...
		if (data.hasOwnProperty("additionalColumns") && data.additionalColumns != null && $.isArray(data.additionalColumns))
		{
			// ...loops through them
			$.each(data.additionalColumns, function(i, additionalColumn) {

				var tmpColumnObj = {}; // New object that represents a column

				// If was given a definition for this field then use it!
				if (recordFieldsDefinitions != null && recordFieldsDefinitions.hasOwnProperty(additionalColumn))
				{
					tmpColumnObj = recordFieldsDefinitions[additionalColumn];
				}

				tmpColumnObj.title = additionalColumn; // Give a title to this field (header)
				tmpColumnObj.field = additionalColumn; // Field name to be linked with dataset field name

				fieldsToDisplayTabulator.push(tmpColumnObj); // Add tmpColumnObj to tabulatorColumns
			});
		}

		return fieldsToDisplayTabulator;
	},

	/**
	 * Gets options for the representation
	 */
	_getRepresentationOptions: function(data) {

		var options = {}; // eventually contains options fot the representation

		// Checks if options were given
		if (data.hasOwnProperty("datasetRepresentationOptions") && data.datasetRepresentationOptions != "")
		{
			var tmpOptions = eval("(" + data.datasetRepresentationOptions + ")"); // and converts them from string to javascript code

			// If it is an object then can be used
			if (typeof tmpOptions == "object")
			{
				options = tmpOptions;
			}
		}

		return options;
	},

	/**
	 * Gets record fields definitions to represent the dataset
	 */
	_getRepresentationFieldsDefinitions: function(data) {

		var fieldsDefinitions = {}; // eventually contains record fields definitions

		// Checks if record fields definitions was given as parameter
		if (data.hasOwnProperty("datasetRepresentationFieldsDefinitions") && data.datasetRepresentationFieldsDefinitions != "")
		{
			var tmpFDefs = eval("(" + data.datasetRepresentationFieldsDefinitions + ")"); // and converts them from string to javascript code

			// If it is an object then can be used
			if (typeof tmpFDefs == "object")
			{
				fieldsDefinitions = tmpFDefs;
			}
		}

		return fieldsDefinitions;
	},

	/**
	 * Set what type of dataset representation was choosen
	 */
	_setDatasetRepresentation: function(data) {

		if (data.hasOwnProperty("datasetRepresentation"))
		{
			FHC_TableWidget._datasetRepresentation = data.datasetRepresentation;
		}
	},

	_getTableWidgetUniqueIdArray: function() {

		var tableWidgetUniqueIdArray = [];

		$("div[id*='divTableWidgetDataset']").each(function(i, e) {

			tableWidgetUniqueIdArray.push(e.attributes["tableUniqueId"].nodeValue);
		});

		return tableWidgetUniqueIdArray;
	}
};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	FHC_TableWidget.display();

});
