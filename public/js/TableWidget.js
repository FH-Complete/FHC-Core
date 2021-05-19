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
	 * To retrive the page where the TableWidget is used, using the FHC_JS_DATA_STORAGE_OBJECT
	 */
	_getTableUniqueIdPrefix: function() {

		return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
	},

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
					tableUniqueId: FHC_TableWidget._getTableUniqueIdPrefix() + "/" + tableWidgetUniqueIdArray[tableWidgetsCounter]
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

		var tableUniqueId = tableWidgetDiv.attr('tableUniqueId');

		// If the choosen dataset representation is tablesorter
		if (FHC_TableWidget._datasetRepresentation == DATASET_REP_TABLESORTER)
		{
			FHC_TableWidget._enableTableSorter(tableWidgetDiv); // enable the tablesorter
		}

		// If the choosen dataset representation is tabulator
		if (FHC_TableWidget._datasetRepresentation == DATASET_REP_TABULATOR)
		{
			// ---------------------------------------------------------------------------------------------------------
			// Add events to the elements
			// ---------------------------------------------------------------------------------------------------------

			// Click-Event to download csv
			tableWidgetDiv.find('#download-csv').on('click', function()
			{
				// BOM for correct UTF-8 char output
				tableWidgetDiv.find("#tableWidgetTabulator").tabulator("download", "csv", "data.csv", {bom:true});
			})

			// Click-Event to toggle the collapsable help panel
			tableWidgetDiv.find('#help').on('click', function()
			{
				// Hide the collapsable settings panel, if it actually shown
				$('#tabulatorSettings-' + tableUniqueId).collapse('hide');

				// Toggle the collapsable help panel
				$('#tabulatorHelp-' + tableUniqueId).collapse('toggle');
			})

			// Click-Event to toggle the collapsable settings panel
			tableWidgetDiv.find('#settings').on('click', function()
			{
				// Hide the collapsable help panel, if it actually shown
				$('#tabulatorHelp-' + tableUniqueId).collapse('hide');

				// Toggle the collapsable settings panel
				$('#tabulatorSettings-' + tableUniqueId).collapse('toggle');
			})

			/* Beautify button group behaviour
			 * Let buttons stay active even until they are clicked again to close the collapsable help- oder setting panels
			 * Also remove the disturbing button focus behaviour
			 */
			$(".btn-group > .btn").click(function(){
				if ($(this).hasClass("active"))
				{
					$(this).removeClass('active').css('outline', 'none');
				}
				else
				{
					$(this).addClass("active").css('outline', 'none').siblings().removeClass("active");
				}
			});

			/**
			 * Click-Event to select all rows
			 * Default is ALL rows. This can be modified via hook tableWidgetHook_selectAllButton.
 			 */
			if (typeof tableWidgetHook_selectAllButton == 'function')
			{
				tableWidgetDiv.find('#select-all').on('click', function() {
					tableWidgetHook_selectAllButton(tableWidgetDiv);
				});
			}
			else
			{
				tableWidgetDiv.find('#select-all').on('click', function() {
					tableWidgetDiv.find("#tableWidgetTabulator").tabulator('selectRow', true);
				});
			}

			// Click-Event to deselect all rows
			tableWidgetDiv.find('#deselect-all').on('click', function()
			{
				tableWidgetDiv.find("#tableWidgetTabulator").tabulator('deselectRow');
			})

			// Click-Event to toggle column-picker columns
			tableWidgetDiv.find('.btn-select-col').on('click', function()
			{
				var selected = this.value;

				tableWidgetDiv.find("#tableWidgetTabulator").tabulator('toggleColumn', selected);

				// toggle class to color button as selected / deselected
				$(this).toggleClass('btn-select-col-selected').blur();	// blur removes automatic focus
			})
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
				if (data.dataset.length == 0)
				{
					// Display placeholder if Table is empty
					var numColumns = arrayFieldsToDisplay.length;
					if (data.hasOwnProperty("additionalColumns") && $.isArray(data.additionalColumns))
					{
						numColumns += data.additionalColumns.length;
					}
					var strHtml = '<tr><td align="center" colspan="'+numColumns+'">';
					strHtml += FHC_PhrasesLib.t('ui', 'keineDatenVorhanden');
					strHtml += '</td></tr>';
					tableWidgetDiv.find("#tableWidgetTableDataset > tbody").append(strHtml);
				}
				else
				{
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
				if (typeof options.tableWidgetHeader == 'undefined')
				{
					options.persistentLayout = true;			// enables persistence (default store in localStorage if available, else in cookie)
					options.persistenceID = data.tableUniqueId;	// TableWidget unique id to store persistence data seperately for multiple tables
				}
				options.movableColumns = true;				// allows changing column order
				options.tooltipsHeader = true;				// set header tooltip with column title
				options.placeholder = _func_placeholder();	// display text when table is empty

				if (typeof options.rowSelectionChanged == 'undefined')
				{
					options.rowSelectionChanged = function(data, rows){
						_func_rowSelectionChanged(data, rows);
					};
				}
				options.columnVisibilityChanged = function(column, visible) {
					_func_columnVisibilityChanged(column, visible);
				};

				// Renders the tabulator
				tableWidgetDiv.find("#tableWidgetTabulator").tabulator(options);
			}
		}

		// -------------------------------------------------------------------------------------------------------------
		// Render TableWidget Header and -Footer
		// -------------------------------------------------------------------------------------------------------------

		// Render tableWidgetHeader
		if (typeof options.tableWidgetHeader == 'undefined' ||
			(typeof options.tableWidgetHeader != 'undefined' && options.tableWidgetHeader != false))
		{
			var tabulatorHeaderHTML = _renderTabulatorHeaderHTML(tableWidgetDiv);
			tableWidgetDiv.find('#tableWidgetHeader').append(tabulatorHeaderHTML);

			// Render the collapsable div triggered by button in tableWidgetHeader
			var tabulatorHeaderCollapseHTML = _renderTabulatorHeaderCollapseHTML(tableWidgetDiv);
			tableWidgetDiv.find('#tableWidgetHeader').after(tabulatorHeaderCollapseHTML);
		}

		/**
		 * 	tableWidgetFooter is NOT rendered by default.
		 * 	tableWidgetFooter is rendered, if tableWidgetFooter is set in tabulators datasetRepOptions.
		 *	Setup options like this:
		 *  tableWidgetFooter: {
		 *  	selectButtons: true  // tableWidgetFooter properties are checked in _renderTabulatorFooterHTML function
		 *  }
 		 */
		if (typeof options.tableWidgetFooter != 'undefined' && options.tableWidgetFooter != null)
		{
			var tabulatorFooterHTML = _renderTabulatorFooterHTML(options.tableWidgetFooter);
			tableWidgetDiv.find('#tableWidgetFooter').append(tabulatorFooterHTML);
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

//**********************************************************************************************************************
// Render functions
//**********************************************************************************************************************
/*
 * Processed when row selection changed.
 * Displays number of selected rows on row selection change.
 */
function _func_rowSelectionChanged (data, rows){

	$('#number-selected').html("Ausgewählte Zeilen: <strong>" + rows.length + "</strong>");
}

/* Processed when columns visibility changed (e.g. using the column picker).
 * Redraws the table to expand columns to table width.
 */
function _func_columnVisibilityChanged(column, visible){

	var table = column.getTable();

	table.redraw();
}

/*
 * Displays text when table is empty
 */
function _func_placeholder(){
	return '<h4>' + FHC_PhrasesLib.t('ui', 'keineDatenVorhanden') + '</h4>';
}

// Returns TableWidget Header HTML (download-, setting button...)
function _renderTabulatorHeaderHTML(tableWidgetDiv){

	var tableUniqueId = tableWidgetDiv.attr('tableUniqueId');

	var tabulatorHeaderHTML = '';
	tabulatorHeaderHTML += '<div class="btn-toolbar pull-right" role="toolbar">';
	tabulatorHeaderHTML += '<div class="btn-group" role="group">';
	tabulatorHeaderHTML += '' +
		'<button id="download-csv" class="btn btn-default" type="button" ' +
		'data-toggle="tooltip" data-placement="left" title="Download CSV">' +
		'<small>CSV&nbsp;&nbsp;</small><i class="fa fa-arrow-down"></i>' +
		'</button>';
	tabulatorHeaderHTML += '' +
		'<button id="help" class="btn btn-default" type="button" ' +
		'data-toggle="collapse tooltip" data-target="tabulatorHelp-'+ tableUniqueId + '" data-placement="left" ' +
		'title="' + FHC_PhrasesLib.t("ui", "hilfe") + '"><i class="fa fa-question"></i>' +
		'</button>';
	tabulatorHeaderHTML += '' +
		'<button id="settings" class="btn btn-default" type="button" ' +
		'data-toggle="collapse tooltip" data-target="tabulatorSettings-'+ tableUniqueId + '" data-placement="left" ' +
		'title="' + FHC_PhrasesLib.t("ui", "tabelleneinstellungen") + '" ' +
		'aria-expanded="false" aria-controls="tabulatorSettings-'+ tableUniqueId + '">' +
		'<i class="fa fa-cog"></i>' +
		'</button>';
	tabulatorHeaderHTML += '</div>';
	tabulatorHeaderHTML += '</div>';
	tabulatorHeaderHTML += '<br><br><br>';

	return tabulatorHeaderHTML;
}

// Returns collapsable HTML element for TableWidget header buttons
function _renderTabulatorHeaderCollapseHTML(tableWidgetDiv){

	var tableUniqueId = tableWidgetDiv.attr('tableUniqueId');

	var tabulatorHeaderCollapseHTML = '';

	// CollapseHTML 'Settings'
	tabulatorHeaderCollapseHTML += '<div class="row">';
	tabulatorHeaderCollapseHTML += '<div class="col-lg-12 collapse" id="tabulatorSettings-'+ tableUniqueId + '">';
	tabulatorHeaderCollapseHTML += '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';

	tabulatorHeaderCollapseHTML += '<div class="panel panel-default">';
	tabulatorHeaderCollapseHTML += '<div class="panel-heading" role="tab" id="headingOne">';
	tabulatorHeaderCollapseHTML += '<h5 class="panel-title">';
	tabulatorHeaderCollapseHTML += '' +
		'<a role="button" data-toggle="collapse" data-parent="#accordion" ' +
		'href="#selectColumns-' + tableUniqueId + '" ' +
		'aria-expanded="false" aria-controls="selectColumns">' +
		FHC_PhrasesLib.t("ui", "spaltenEinstellen") +
		'</a>';
	tabulatorHeaderCollapseHTML += '</h5>';
	tabulatorHeaderCollapseHTML += '</div>'; // end panel-heading
	tabulatorHeaderCollapseHTML += '<div id="selectColumns-' + tableUniqueId + '" class="panel-collapse collapse" ' +
		'role="tabpanel" aria-labelledby="headingOne">';
	tabulatorHeaderCollapseHTML += '<div class="panel-body">';
	tabulatorHeaderCollapseHTML += '<div class="btn-group" role="group">';

	// Create column picker (Spalten einstellen)
	tableWidgetDiv.find('#tableWidgetTabulator').tabulator('getColumns').forEach(function(column)
	{
		var field = column.getField();
		var title = column.getDefinition().title;
		var btn_select_col_selected = column.getVisibility() ? 'btn-select-col-selected' : '';

		// If certain columns should be excluded from the column picker (define them in a blacklist array)
		if (typeof tableWidgetBlacklistArray_columnUnselectable != 'undefined' &&
			Array.isArray(tableWidgetBlacklistArray_columnUnselectable) &&
			tableWidgetBlacklistArray_columnUnselectable.length)
		{
			if ($.inArray(field, tableWidgetBlacklistArray_columnUnselectable) < 0)
			{
				tabulatorHeaderCollapseHTML += '<button type="button" class="btn btn-default btn-sm btn-select-col ' + btn_select_col_selected +'" aria-pressed="true" id="btn-' + field + '" value="' + field + '">' + title + '</button>';
			}
		}
		// Else provide all tabulator fields as pickable columns
		else
		{
			tabulatorHeaderCollapseHTML += '<button type="button" class="btn btn-default btn-sm btn-select-col ' + btn_select_col_selected +'" aria-pressed="true" id="btn-' + field + '" value="' + field + '">' + title + '</button>';
		}
	});

	tabulatorHeaderCollapseHTML += '</div>'; // end btn-group
	tabulatorHeaderCollapseHTML += '</div>'; // end panel-body
	tabulatorHeaderCollapseHTML += '</div>'; // end panel-collapse
	tabulatorHeaderCollapseHTML += '</div>'; // end panel

	tabulatorHeaderCollapseHTML += '</div>'; // end panel-group
	tabulatorHeaderCollapseHTML += ' </div>'; // end col
	tabulatorHeaderCollapseHTML += ' </div>'; // end row

	return tabulatorHeaderCollapseHTML;
}

// Returns TableWidget Footer HTML (de-/select buttons,...)
function _renderTabulatorFooterHTML(tableWidgetFooterOptions){

	var tabulatorFooterHTML = '';

	// If property selectButtons is true, render 'Alle auswaehlen / Alle abwaehlen' buttons
	if (typeof tableWidgetFooterOptions.selectButtons != 'undefined' && tableWidgetFooterOptions.selectButtons == true)
	{
		tabulatorFooterHTML += '<div class="btn-toolbar" role="toolbar">';
		tabulatorFooterHTML += '<div class="btn-group" role="group">';
		tabulatorFooterHTML += '' +
			'<button id="select-all" class="btn btn-default pull-left" type="button">'
			+ FHC_PhrasesLib.t("ui", "alleAuswaehlen") + '' +
			'</button>';
		tabulatorFooterHTML += '' +
			'<button id="deselect-all" class="btn btn-default pull-left" type="button">'
			+ FHC_PhrasesLib.t("ui", "alleAbwaehlen") + '' +
			'</button>';
		tabulatorFooterHTML += '' +
			'<span id="number-selected" style="margin-left: 20px; line-height: 2; font-weight: normal">'
			+ FHC_PhrasesLib.t("ui", "ausgewaehlteZeilen") + ': <strong>0</strong>' +
			'</span>';
		tabulatorFooterHTML += '</div>';
		tabulatorFooterHTML += '</div>';
		tabulatorFooterHTML += '</br></br>';
	}

	return tabulatorFooterHTML;
}

/**
 * When JQuery is up
 */
$(document).ready(function() {

	FHC_TableWidget.display();

});
