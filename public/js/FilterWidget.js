/**
 * FH-Complete
 *
 * @package
 * @author
 * @copyright   Copyright (c) 2016 fhcomplete.org
 * @license GPLv3
 * @link    https://fhcomplete.org
 * @since	Version 1.0.0
 */

 /**
 * Global function used by FilterWidget JS to refresh the side menu
 * NOTE: it is called from the FilterWidget JS therefore must be a global function
 *		It may be overwritten by a custom refreshSideMenuHook included in a javascript which will be loaded after this one
 *		The given parameters, filterUniqueId and navigation_page, are required
 */
function refreshSideMenuHook()
{
	FHC_NavigationWidget.refreshSideMenuHook(
		'widgets/Filters/setNavigationMenu',
		{
			filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix(),
			navigation_page: FHC_NavigationWidget.getNavigationPage()
		}
	);
}

/**
 * Global function used by NavigationWidget JS to bind events to side menu elements
 * NOTE: it is called from the NavigationWidget JS therefore must be a global function
 *		Be carefull about recursive function calls!!!
 */
function sideMenuHook()
{
	// If menu is present
	if (FHC_FilterWidget._hideMenu != true)
	{
		$(".remove-custom-filter").click(function() {

			// Ajax call to remove a custom filter
			FHC_AjaxClient.ajaxCallPost(
				"widgets/Filters/removeCustomFilter",
				{
					filter_id: $(this).attr("value"), // filter_id of the filter to be removed
					filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
				},
				{
					reloadPage: true,
					successCallback: function(data, textStatus, jqXHR) {

						if (FHC_AjaxClient.isError(data))
						{
							console.error(FHC_AjaxClient.getError(data));
						}
						else
						{
							if (typeof refreshSideMenuHook == "function")
							{
								refreshSideMenuHook();
							}
						}
					}
				}
			);
		});
	}
}

//--------------------------------------------------------------------------------------------------------------------
// Constants

// Success
const DATASET_REP_TABLESORTER = "tablesorter";
const DATASET_REP_PIVOTUI = "pivotUI";
const DATASET_REP_TABULATOR = "tabulator";

/**
 * FHC_FilterWidget this object is used to render the GUI of a filter widget and to operate with it
 */
var FHC_FilterWidget = {

	//------------------------------------------------------------------------------------------------------------------
	// Properties

	_datasetRepresentation: null, // contains the current data representation
	_hideMenu: false, //
	_hideOptions: false, //

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * To display the FilterWidget using the loaded data present in the session
	 */
	display: function() {

		FHC_FilterWidget._getFilter(FHC_FilterWidget._renderFilterWidget);
	},

	/**
	 * Alias call to method display only to improve the readability of the code
	 */
	refresh: function() {

		FHC_FilterWidget.display();
	},

	/**
	 * To retrieve the page where the FilterWidget is used, using the FHC_JS_DATA_STORAGE_OBJECT
	 */
	getFilterUniqueIdPrefix: function() {

		return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
	},

	/**
	 * Reload of dataset, also reloads page to show changes
	 */
	reloadDataset: function() {
		FHC_AjaxClient.ajaxCallPost(
			"widgets/Filters/reloadDataset",
			{
				filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					FHC_FilterWidget._failOrReload(data);
				}
			}
		);
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Utility method that checks if data contains an error and print that to the console
	 * otherwise the FilterWidget GUI is refreshed
	 */
	_failOrRefresh: function(data, textStatus, jqXHR) {

		if (FHC_AjaxClient.isError(data))
		{
			console.error(FHC_AjaxClient.getError(data));
		}
		else
		{
			FHC_FilterWidget.refresh();
		}
	},

	/**
	 * Utility method that checks if data contains an error and print that to the console
	 * otherwise the page is reloaded
	 */
	_failOrReload: function(data, textStatus, jqXHR) {

		if (FHC_AjaxClient.isError(data))
		{
			console.error(FHC_AjaxClient.getError(data));
		}
		else
		{
			location.reload();
		}
	},

	/**
	 * To reset the Filter Options GUI
	 */
	_resetGUI: function() {

		$("#dragAndDropFieldsArea").html("");
		$("#addField").html("<option value=''>" + FHC_PhrasesLib.t("ui", "bitteEintragWaehlen") + "</option>");
		$("#appliedFilters").html("");
		$("#addFilter").html("<option value=''>" + FHC_PhrasesLib.t("ui", "bitteEintragWaehlen") + "</option>");
		FHC_FilterWidget._toggleApplySaveButtons(false);

		// If the choosen dataset representation is tablesorter
		if (FHC_FilterWidget._datasetRepresentation == DATASET_REP_TABLESORTER)
		{
			$("#filterTableDataset > thead > tr").html("");
			$("#filterTableDataset > tbody").html("");
		}

		// If the choosen dataset representation is pivotUI
		if (FHC_FilterWidget._datasetRepresentation == DATASET_REP_PIVOTUI)
		{
			$("#filterPivotUI").html("");
		}

		// If the choosen dataset representation is tabulator
		if (FHC_FilterWidget._datasetRepresentation == DATASET_REP_TABULATOR)
		{
			$("#filterTabulator").html("");
		}
	},

	/**
	 * To get via Ajax all the data related to the FilterWidget present in the given page
	 * If the parameter renderFunction is a valid function, is called on success
	 */
	_getFilter: function(renderFunction) {

		FHC_AjaxClient.ajaxCallGet(
			"widgets/Filters/getFilter",
			{
				filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
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
						console.error(FHC_AjaxClient.getError(data));
					}
				}
			}
		);
	},

	/**
	 * This method calls all the other methods needed to render the GUI for a FilterWidget
	 * The parameter data contains all the data about the FilterWidget and it is given as parameter
	 * to all the methods that here are called
	 * NOTE: think very carefully before changing the order of the calls
	 */
	_renderFilterWidget: function(data) {

		FHC_FilterWidget._initSessionStorage(); // initialize the session storage

		FHC_FilterWidget._setDatasetRepresentation(data); // set what type of dataset representation was choosen
		FHC_FilterWidget._setHideMenu(data); // sets the _hideMenu property
		FHC_FilterWidget._setHideOptions(data);

		FHC_FilterWidget._turnOffEvents(); // turns all the events off
		FHC_FilterWidget._resetGUI(); // Reset the entire GUI

		// Render the GUI for this FilterWidget
		FHC_FilterWidget._setFilterName(data); // set the name in the GUI

		if (FHC_FilterWidget._hideOptions != true)
		{
			FHC_FilterWidget._renderDragAndDropFields(data); // render the fields drag and drop GUI
			FHC_FilterWidget._renderDropDownFields(data); // render the fields drop-down
			FHC_FilterWidget._renderAppliedFilters(data); // render the GUI for the applied filters
			FHC_FilterWidget._renderDropDownFilters(data); // render the filters drop-down
		}

		FHC_FilterWidget._renderDataset(data);

		FHC_FilterWidget._turnOnEvents(); // turns all the events off
	},

	/**
	 * Initialize the session storage
	 */
	_initSessionStorage: function() {

		// If the browser supports storage
		if (typeof(Storage) !== "undefined")
		{
			// Checks if the "filter-options-status" is present in the session storage and if is equal to "open"
			if (sessionStorage.getItem("filter-options-status") && sessionStorage.getItem("filter-options-status") == "open")
			{
				$(".collapse").collapse("show"); // then open the filter options panel
			}
			else
			{
				sessionStorage.setItem("filter-options-status", "closed"); // otherwise set "filter-options-status" to "close"
			}
		}
	},

	/**
	 * Turns all the events off
	 * NOTE: must be aligned to _turnOnEvents
	 */
	_turnOffEvents: function() {

		$('[data-toggle="collapse"]').off("click");
		$(".drag-and-drop-fields-span").off("draggable");
		$(".drag-and-drop-fields-span").off("droppable");
		$(".remove-selected-field").off("click");
		$("#addField").off("change");
		$(".applied-filter-operation").off("change");
		$(".applied-filter-condition").off("keyup");
		$(".applied-filter-option").off("change");
		$(".remove-applied-filter").off("click");
		$("#addFilter").off("change");
		$("#applyFilter").off("click");
		$("#saveCustomFilterButton").off("click");

		// If the choosen dataset representation is tablesorter
		if (FHC_FilterWidget._datasetRepresentation == DATASET_REP_TABLESORTER)
		{
			FHC_FilterWidget._disableTablesorter(); // disable the tablesorter
		}
	},

	/**
	 * Turns all the events on
	 * NOTE: must be aligned to _turnOffEvents
	 */
	_turnOnEvents: function() {

		$('[data-toggle="collapse"]').click(FHC_FilterWidget._dataToggleCollapseEvent); // Click event to collapse or to open the filter options panel
		$(".drag-and-drop-fields-span").draggable(FHC_FilterWidget._draggableConf); // draggable event on selected fields
		$(".drag-and-drop-fields-span").droppable(FHC_FilterWidget._droppableConf); // droppable event on selected fields
		$(".remove-selected-field").click(FHC_FilterWidget._revomeSelectedFieldsEvent); // Click event on the "X" link
		$("#addField").change(FHC_FilterWidget._addFieldEvent); // Change event on the fields drop-down to add new fields
		$(".applied-filter-operation").change(FHC_FilterWidget._appliedFiltersOperationsEvent); // Change event on the operation drop-down
		$(".applied-filter-condition").keyup(FHC_FilterWidget._appliedFiltersConditionsEvent); // Change event on the conditions fields
		$(".applied-filter-option").change(FHC_FilterWidget._appliedFiltersOptionsEvent); // Change event on the operation drop-down
		$(".remove-applied-filter").click(FHC_FilterWidget._removeAppliedFiltersEvent); // Click event to the "X" button to remove an applied filter
		$("#addFilter").change(FHC_FilterWidget._addFilterEvent); // Click event on the applied filters drop-down to add a new filter to the dataset
		$("#applyFilter").click(FHC_FilterWidget._applyFilterEvent); // Click event on the applied filters drop-down to apply filters to the dataset
		$("#saveCustomFilterButton").click(FHC_FilterWidget._saveCustomFilterButtonEvent); // Click evento to for the save custom filter button

		// If the choosen dataset representation is tablesorter
		if (FHC_FilterWidget._datasetRepresentation == DATASET_REP_TABLESORTER)
		{
			FHC_FilterWidget._enableTableSorter(); // enable the tablesorter
		}
	},

	/**
	 * Configuration object used by draggable event on selected fields
	 */
	_draggableConf: {
		containment: "parent",
		cursor: "move",
		opacity: 0.4,
		revert: "invalid",
		revertDuration: 200,
		drag: function(event, ui) {

			var padding = 20;
			var draggedElement = $(this);

			$(".drag-and-drop-fields-span").each(function(i, e) {

				if ($(this).attr("id") != draggedElement.attr("id"))
				{
					$(this).removeClass("selection-after");
					$(this).removeClass("selection-before");

					var elementCenter = $(this).offset().left + ((padding + $(this).width()) / 2);

					if (event.pageX > ($(this).offset().left - (padding / 2))
						&& event.pageX < ($(this).offset().left + $(this).width() + (padding / 2)))
					{
						if (event.pageX > elementCenter)
						{
							$(this).addClass("selection-after");
							$(this).removeClass("selection-before");
						}
						else if (event.pageX < elementCenter)
						{
							$(this).addClass("selection-before");
							$(this).removeClass("selection-after");
						}
					}
				}

			});

		}
	},

	/**
	 * Configuration object used by droppable event on selected fields
	 */
	_droppableConf: {
		accept: ".drag-and-drop-fields-span",
		drop: function(event, ui) {

			var padding = 20;
			var elementCenter = $(this).offset().left + ((padding + $(this).width()) / 2);
			var draggedElement = ui.helper;

			if (event.pageX > elementCenter)
			{
				draggedElement.insertAfter($(this));
			}
			else if (event.pageX < elementCenter)
			{
				draggedElement.insertBefore($(this));
			}

			$(this).removeClass("selection-before");
			$(this).removeClass("selection-after");

			draggedElement.css({left: "0px", top: "10px"});

			var arrayDndId = [];

			$(".drag-and-drop-fields-span").each(function(i, e) {

				arrayDndId[i] = $(this).attr("id").replace("dnd", "");

			});

			FHC_AjaxClient.ajaxCallPost(
				"widgets/Filters/sortSelectedFields",
				{
					selectedFields: arrayDndId,
					filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
				},
				{
					successCallback: function(data, textStatus, jqXHR) {
						FHC_FilterWidget._failOrRefresh(data, textStatus, jqXHR);
					}
				}
			);
		}
	},

	/**
	 * Event function used to remove selected fields
	 */
	_revomeSelectedFieldsEvent: function(event) {

 		FHC_AjaxClient.ajaxCallPost(
 			"widgets/Filters/removeSelectedField",
 			{
 				selectedField: $(this).attr("fieldToRemove"),
				filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
 			},
 			{
 				successCallback: function(data, textStatus, jqXHR) {
 					FHC_FilterWidget._failOrRefresh(data, textStatus, jqXHR);
 				}
 			}
 		);
 	},

	/**
	 * Event function used by the applied filter operation drop-down to hide others element when thery are not needed
	 */
	_appliedFiltersOperationsEvent: function(event) {

		if ($(this).val() == "set" || $(this).val() == "nset")
		{
			$(this).parent().parent().find(".applied-filter-condition").addClass("hidden-control");
			$(this).parent().parent().find(".applied-filter-option").addClass("hidden-control");
			$(this).parent().parent().find(".applied-filter-condition").prop("disabled", true);
			$(this).parent().parent().find(".applied-filter-option").prop("disabled", true);
		}
		else
		{
			$(this).parent().parent().find(".applied-filter-condition").removeClass("hidden-control");
			$(this).parent().parent().find(".applied-filter-option").removeClass("hidden-control");
			$(this).parent().parent().find(".applied-filter-condition").prop("disabled", false);
			$(this).parent().parent().find(".applied-filter-option").prop("disabled", false);
		}

		FHC_FilterWidget._toggleApplySaveButtons(true);
	},

	/**
	 * Event function used by the applied filter conditions
	 */
	_appliedFiltersConditionsEvent: function(event) {
		FHC_FilterWidget._toggleApplySaveButtons(true);
	},

	/**
	 * Event function used by the applied filter options
	 */
	_appliedFiltersOptionsEvent: function(event) {
		FHC_FilterWidget._toggleApplySaveButtons(true);
	},

	/**
	 * Event function used by the add field drop-down
	 */
	_addFieldEvent: function(event) {

		FHC_AjaxClient.ajaxCallPost(
			"widgets/Filters/addSelectedField",
			{
				selectedField: $(this).val(),
				filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					FHC_FilterWidget._failOrRefresh(data, textStatus, jqXHR);
				}
			}
		);
	},

	/**
	 * Event function used by the apply filter button
	 * The given parameter is used to decide if the page is going to be reloaded
	 */
	_applyFilterEvent: function() {

		var isValid = true;
		var appliedFilters = [];
		var appliedFiltersOperations = [];
		var appliedFiltersConditions = [];
		var appliedFiltersOptions = [];

		// Get all the data from the filter form and fill the arrays
		$("#appliedFilters > div").each(function(i, e) {

			appliedFilters.push($(this).find(".hidden-field-name").val());
			appliedFiltersOperations.push($(this).find(".applied-filter-operation").val());

			// Checks if the conditions are filled by the user
			if ($(this).find(".applied-filter-condition:enabled").length > 0
				&& $(this).find(".applied-filter-condition:enabled").val().trim() != '')
			{
				appliedFiltersConditions.push($(this).find(".applied-filter-condition:enabled").val());
			}
			else // otherwise mark the empty conditions in red
			{
				$(this).find(".applied-filter-condition:enabled").css("border", "1px solid red");
				isValid = false;
			}

			appliedFiltersOptions.push($(this).find(".applied-filter-option:enabled").val());
		});

		if (isValid)
		{
			FHC_AjaxClient.ajaxCallPost(
				"widgets/Filters/applyFilters",
				{
					appliedFilters: appliedFilters,
					appliedFiltersOperations: appliedFiltersOperations,
					appliedFiltersConditions: appliedFiltersConditions,
					appliedFiltersOptions: appliedFiltersOptions,
					filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
				},
				{
					successCallback: function(data, textStatus, jqXHR) {
						FHC_FilterWidget._failOrReload(data, textStatus, jqXHR);
					}
				}
			);
		}
	},

	/**
	 * Event function used to remove an applied filter to the dataset
	 */
	_removeAppliedFiltersEvent: function(event) {

		FHC_AjaxClient.ajaxCallPost(
			"widgets/Filters/removeAppliedFilter",
			{
				appliedFilter: $(this).attr("filterToRemove"),
				filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					FHC_FilterWidget._failOrReload(data, textStatus, jqXHR);
				}
			}
		);
	},

	/**
	 * Event function used to add a new filter to the dataset
	 */
	_addFilterEvent: function(event) {

 		FHC_AjaxClient.ajaxCallPost(
 			"widgets/Filters/addFilter",
 			{
 				filter: $(this).val(),
				filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
 			},
 			{
 				successCallback: function(data, textStatus, jqXHR) {
 					FHC_FilterWidget._failOrRefresh(data, textStatus, jqXHR);
 				}
 			}
 		);
 	},

	/**
	 * Event function used to collapse the filter options panel and to store the info into the session storage
	 */
	_dataToggleCollapseEvent: function() {

		if (typeof(Storage) !== "undefined")
		{
			if (sessionStorage.getItem("filter-options-status"))
			{
				if (sessionStorage.getItem("filter-options-status") == "closed")
				{
					sessionStorage.setItem("filter-options-status", "open");
				}
				else
				{
					sessionStorage.setItem("filter-options-status", "closed");
				}
			}
		}
	},

	/**
	 * Event function used to save a custom filter
	 */
	_saveCustomFilterButtonEvent: function() {

 		if ($("#customFilterDescription").val() != "")
 		{
 			FHC_AjaxClient.ajaxCallPost(
 				"widgets/Filters/saveCustomFilter",
 				{
 					customFilterDescription: $("#customFilterDescription").val(),
					filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
 				},
 				{
 					successCallback: function(data, textStatus, jqXHR) {

						// If an error occurred then log it
						if (FHC_AjaxClient.isError(data)) console.error(data);

						// In any case tries to apply the filter
						FHC_FilterWidget._applyFilterEvent();
					}
 				}
 			);
 		}
 		else
 		{
 			alert("Please fill the description of this filter");
 		}
 	},

	/**
	 * Retrive the filter name from data and display it in the GUI
	 */
	_setFilterName: function(data) {

		if (data.hasOwnProperty("filterName"))
		{
			$(".filter-name-title").html(data.filterName);
		}
	},

	/**
	 * Renders the drag and drop GUI for the fields of the FilterWidget
	 * Retrieves the list of used fields, the list of all the fields and
	 * their possibly present aliases from the parameter data
	 */
	_renderDragAndDropFields: function(data) {

		var arrayFieldsToDisplay = FHC_FilterWidget._getFieldsToDisplay(data);

		for (var i = 0; i < arrayFieldsToDisplay.length; i++)
		{
			var fieldToDisplay = arrayFieldsToDisplay[i];
			var fieldName = data.selectedFields[i];

			var strHtml = "<span id='dnd" + fieldName + "' class='drag-and-drop-fields-span'>";
			strHtml += "	<span>" + fieldToDisplay + "</span>";
			strHtml += "	<span>";
			strHtml += "		<a class='remove-selected-field' fieldToRemove='" + fieldName + "'> X </a>";
			strHtml += "	</span>";
			strHtml += "</span>";

			$("#dragAndDropFieldsArea").append(strHtml);
		}
	},

	/**
	 * Renders the drop-down element that contains all the usable fields in the FilterWidget
	 * The list of all usable fields and their possibly aliases are retrieved from the parameter data
	 */
	_renderDropDownFields: function(data) {

		FHC_FilterWidget._renderDropDown(data, data.selectedFields, 'addField');
	},

	/**
	 * Renders a dropdown attached to the HTML element ddElementId, using the elements from data.fields
	 * and excluding the elements that are prenset in the elements parameter
	 */
	_renderDropDown: function(data, elements, ddElementId) {

		if (data.hasOwnProperty("fields") && $.isArray(data.fields))
		{
			for (var fc = 0; fc < data.fields.length; fc++)
			{
				var toBeDisplayed = true;

				for (var ec = 0; ec < elements.length; ec++)
				{
					var elementName = elements[ec].hasOwnProperty("name") ? elements[ec].name : elements[ec];

					if (data.fields[fc] == elementName)
					{
						toBeDisplayed = false;
						break;
					}
				}

				if (toBeDisplayed == true)
				{
					var fieldName = data.fields[fc];
					var fieldToDisplay = data.fields[fc];

					if (data.hasOwnProperty("columnsAliases") && $.isArray(data.columnsAliases))
					{
						fieldToDisplay = data.columnsAliases[fc];
					}

					if ($("#" + ddElementId).length) // checks if the element exists
					{
						$("#" + ddElementId).append("<option value='" + fieldName + "'>" + fieldToDisplay + "</option>");
					}
				}
			}
		}
	},

	/**
	 * Render the GUI to operate with the filters applied to the dataset
	 * The list of all applied filters is retrieved from the parameter data
	 */
	_renderAppliedFilters: function(data) {

		if (data.hasOwnProperty("datasetMetadata") && $.isArray(data.datasetMetadata)
			&& data.hasOwnProperty("filters") && $.isArray(data.filters))
		{
			for (var fc = 0; fc < data.filters.length; fc++)
			{
				for (var dmc = 0; dmc < data.datasetMetadata.length; dmc++)
				{
					if (data.filters[fc].name == data.datasetMetadata[dmc].name)
					{
						var appliedFilters = "<div>";

						appliedFilters += "<span class='filter-span-label'>";

						if (data.hasOwnProperty("columnsAliases") && $.isArray(data.columnsAliases))
						{
							fieldToDisplay = data.columnsAliases[dmc];
						}
						else
						{
							fieldToDisplay = data.datasetMetadata[dmc].name;
						}

						appliedFilters += fieldToDisplay;
						appliedFilters += "</span>";

						appliedFilters += FHC_FilterWidget._renderSingleAppliedFilter(data.filters[fc], data.datasetMetadata[dmc]);

						appliedFilters += "</div>";

						$("#appliedFilters").append(appliedFilters);
					}
				}
			}
		}
	},

	/**
	 * Renders the drop-down element that contains all the possibly fields that can be used
	 * to apply a filter to the dataset
	 * The list of all usable fields and their possibly aliases are retrieved from the parameter data
	 */
	_renderDropDownFilters: function(data) {

		FHC_FilterWidget._renderDropDown(data, data.filters, 'addFilter');
	},

	/**
	 * Renders a single applied filter to the dataset using the applied filter configuration and its related metadata
	 */
	_renderSingleAppliedFilter: function(appliedFilter, metaData) {

		var html = "";

		// If integer type
		if (metaData.type.toLowerCase().indexOf("int") >= 0)
		{
			if (appliedFilter.condition == null) appliedFilter.condition = 0;

			html = "<span>";
			html += "	<select class='form-control applied-filter-operation'>";
			html += "		<option value='equal' " + (appliedFilter.operation == "equal" ? "selected" : "") + ">equal</option>";
			html += "		<option value='nequal' " + (appliedFilter.operation == "nqual" ? "selected" : "") + ">not equal</option>";
			html += "		<option value='gt' " + (appliedFilter.operation == "gt" ? "selected" : "") + ">greater than</option>";
			html += "		<option value='lt' " + (appliedFilter.operation == "lt" ? "selected" : "") + ">less than</option>";
			html += "	</select>";
			html += "</span>";
			html += "<span>";
			html += "	<input type='numbe' value='" + appliedFilter.condition + "' class='form-control applied-filter-condition'>";
			html += "</span>";
		}

		// If text, varchar or char type
		if (metaData.type.toLowerCase().indexOf("varchar") >= 0
			|| metaData.type.toLowerCase().indexOf("text") >= 0
			|| metaData.type.toLowerCase().indexOf("bpchar") >= 0)
		{
			if (appliedFilter.condition == null) appliedFilter.condition = "";

			html = "<span>";
			html += "	<select class='form-control applied-filter-operation'>";
			html += "		<option value='contains' " + (appliedFilter.operation == "contains" ? "selected" : "") + ">contains</option>";
			html += "		<option value='ncontains' " + (appliedFilter.operation == "ncontains" ? "selected" : "") + ">does not contain</option>";
			html += "	</select>";
			html += "</span>";
			html += "<span>";
			html += "	<input type='text' value='" + appliedFilter.condition + "' class='form-control applied-filter-condition'>";
			html += "</span>";
		}

		// If boolean type
		if (metaData.type.toLowerCase().indexOf("bool") >= 0)
		{
			html = "<span>";
			html += "	<select class='form-control applied-filter-operation'>";
			html += "		<option value='true' " + (appliedFilter.operation == "true" ? "selected" : "") + ">is true</option>";
			html += "		<option value='false' " + (appliedFilter.operation == "false" ? "selected" : "") + ">is false</option>";
			html += "	</select>";
			html += "</span>";
			html += "<span>";
			html += "	<input type='hidden' value='" + appliedFilter.condition + "' class='form-control applied-filter-condition'>";
			html += "</span>";
		}

		// If timestamp or date type
		if (metaData.type.toLowerCase().indexOf("timestamp") >= 0 || metaData.type.toLowerCase().indexOf("date") >= 0)
		{
			var classOperation = "form-control applied-filter-condition";
			var classOption = "form-control applied-filter-option";
			var disabled = "";

			if (appliedFilter.condition == null) appliedFilter.condition = 0;

			if (appliedFilter.operation == "set" || appliedFilter.operation == "nset")
			{
				classOperation += " hidden-control";
				classOption += " hidden-control";
				disabled = "disabled";
			}

			html = "<span>";
			html += "	<select class='form-control applied-filter-operation'>";
			html += "		<option value='lt' " + (appliedFilter.operation == "lt" ? "selected" : "") + ">less than</option>";
			html += "		<option value='gt' " + (appliedFilter.operation == "gt" ? "selected" : "") + ">greater than</option>";
			html += "		<option value='set' " + (appliedFilter.operation == "set" ? "selected" : "") + ">is set</option>";
			html += "		<option value='nset' " + (appliedFilter.operation == "nset" ? "selected" : "") + ">is not set</option>";
			html += "	</select>";
			html += "</span>";
			html += "<span>";
			html += "	<input type='number' value='" + appliedFilter.condition + "' class='" + classOperation + "' " + disabled + ">";
			html += "</span>";
			html += "<span>";
			html += "	<select class='" + classOption + "' " + disabled + ">";
			html += "		<option value='minutes' " + (appliedFilter.option == "minutes" ? "selected" : "") + ">Minutes</option>";
			html += "		<option value='hours' " + (appliedFilter.option == "hours" ? "selected" : "") + ">Hours</option>";
			html += "		<option value='days' " + (appliedFilter.option == "days" ? "selected" : "") + ">Days</option>";
			html += "		<option value='months' " + (appliedFilter.option == "months" ? "selected" : "") + ">Months</option>";
			html += "	</select>";
			html += "</span>";
		}

		html += "<span>";
		html += "	<input type='hidden' value='" + metaData.name + "' class='hidden-field-name'>";
		html += "</span>";

		html += "<span>";
		html += "	<input type='button' value='X' class='remove-applied-filter btn btn-default' filterToRemove='" + appliedFilter.name + "'>";
		html += "</span>";

		return html;
	},

	/**
	 * It renders the dataset with a tablesorter, puvotUI or a tabulator
	 */
	_renderDataset: function(data) {

		// If the choosen dataset representation is tablesorter then...
		if (FHC_FilterWidget._datasetRepresentation == DATASET_REP_TABLESORTER)
		{
			FHC_FilterWidget._renderDatasetTablesorter(data); // ...render the tablesorter GUI
		}

		// If the choosen dataset representation is pivotUI then...
		if (FHC_FilterWidget._datasetRepresentation == DATASET_REP_PIVOTUI)
		{
			FHC_FilterWidget._renderDatasetPivotUI(data); // ...render the pivotUI GUI
		}

		// If the choosen dataset representation is tabulator then...
		if (FHC_FilterWidget._datasetRepresentation == DATASET_REP_TABULATOR)
		{
			FHC_FilterWidget._renderDatasetTabulator(data); // ...render the tabulator GUI
		}
	},

	/**
	 * Renders the tablesorter for the FilterWidget
	 * The data to be displayed are retrived from the parameter data
	 */
	_renderDatasetTablesorter: function(data) {

		//clear tablesorter filter storage
		var keepTsFilter = FHC_AjaxClient.getUrlParameter("keepTsFilter");

		if (typeof keepTsFilter === "undefined" || keepTsFilter !== "true")
		{
			FHC_FilterWidget._clearTablesorterLocalStorage();
		}

		if (data.hasOwnProperty("checkboxes") && data.checkboxes != null && data.checkboxes.trim() != "")
		{
			$("#filterTableDataset > thead > tr").append("<th data-filter='false' title='Select'>Select</th>");
		}

		var arrayFieldsToDisplay = FHC_FilterWidget._getFieldsToDisplay(data);

		for (var i = 0; i < arrayFieldsToDisplay.length; i++)
		{
			var columnName = arrayFieldsToDisplay[i];

			$("#filterTableDataset > thead > tr").append("<th title='" + columnName + "'>" + columnName + "</th>");
		}

		if (data.hasOwnProperty("additionalColumns") && $.isArray(data.additionalColumns))
		{
			for (var i = 0; i < data.additionalColumns.length; i++)
			{
				var columnName = data.additionalColumns[i];

				$("#filterTableDataset > thead > tr").append("<th title='" + columnName + "'>" + columnName + "</th>");
			}
		}

		if (arrayFieldsToDisplay.length > 0)
		{
			if (data.hasOwnProperty("dataset") && $.isArray(data.dataset))
			{
				if (data.checkboxes != null && data.checkboxes != "")
				{
					// select checkbox range with shift key
					if (typeof $("#filterTableDataset").checkboxes === 'function')
						$("#filterTableDataset").checkboxes("range", true);
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

						if (record.hasOwnProperty(data.selectedFields[i]))
						{
							strHtml += "<td>" + record[data.selectedFields[i]] + "</td>";
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

					$("#filterTableDataset > tbody").append(strHtml);
				}
			}
		}
	},

	/**
	 * Enable the tablesorter libs to render the dataset table with sorting features
	 */
	_enableTableSorter: function() {

		var filterWidgetTablesorter = $("#filterTableDataset");

		// Checks if the table contains data (rows)
		if (filterWidgetTablesorter.find("tbody:empty").length == 0
			&& filterWidgetTablesorter.find("tr:empty").length == 0
			&& filterWidgetTablesorter.hasClass("table-condensed"))
		{
			filterWidgetTablesorter.tablesorter({
				dateFormat: "ddmmyyyy",
				widgets: ["zebra", "filter"],
				widgetOptions: {
					filter_saveFilters : true
				}
			});

			$.tablesorter.updateAll(filterWidgetTablesorter[0].config, true, null);
		}
	},

	/**
	 * Disable the tablesorter
	 */
	_disableTablesorter: function() {

		$("#filterTableDataset").trigger("disable");
	},

	/**
	 * Tablesorter filter local storage clean
	 */
	_cleanTablesorterLocalStorage: function() {

		$("#filterTableDataset").trigger("filterResetSaved");
	},

	/**
	 * Renders the pivotUI for the FilterWidget
	 * The data to be displayed are retrived from the parameter data
	 */
	_renderDatasetPivotUI: function(data) {

		// Checks if options were given and returns them
		var options = FHC_FilterWidget._getRepresentationOptions(data);

		// Manipulation for the representation!
		var arrayFieldsToDisplay = FHC_FilterWidget._getFieldsToDisplay(data);

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

						if (record.hasOwnProperty(data.selectedFields[i]))
						{
							tmpObj[fieldToDisplay] = record[data.selectedFields[i]]; // Add data with the column alias
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
				$("#filterPivotUI").pivotUI(
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

		if (data.hasOwnProperty("selectedFields") && $.isArray(data.selectedFields))
		{
			if (data.hasOwnProperty("columnsAliases") && $.isArray(data.columnsAliases))
			{
				for (var sfc = 0; sfc < data.selectedFields.length; sfc++)
				{
					for (var fc = 0; fc < data.fields.length; fc++)
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

		return arrayFieldsToDisplay;
	},

	/**
	 * Renders the tabulator for the FilterWidget
	 * The data to be displayed are retrived from the parameter data
	 */
	_renderDatasetTabulator: function(data) {

		// Checks if options were given and returns them
		var options = FHC_FilterWidget._getRepresentationOptions(data);
		// Checks if record fields definitions were given and returns them
		var recordFieldsDefinitions = FHC_FilterWidget._getRepresentationFieldsDefinitions(data);

		// Manipulation for the representation!
		var arrayTabulatorColumns = FHC_FilterWidget._getTabulatorColumns(data, recordFieldsDefinitions);

		if (arrayTabulatorColumns.length > 0)
		{
			// ...if there are data to be displayed...
			if (data.hasOwnProperty("dataset") && $.isArray(data.dataset))
			{
				if (options == null) options = {};

				options.columns = arrayTabulatorColumns;
				options.data = data.dataset;

				// Renders the tabulator
				$("#filterTabulator").tabulator(options);
			}
		}
	},

	/**
	 * Retrives the fields to be displayed from the data parameter, if aliases are present then they are used
	 */
	_getTabulatorColumns: function(data, recordFieldsDefinitions) {

		var fieldsToDisplayTabulator = [];

		if (data.hasOwnProperty("selectedFields") && $.isArray(data.selectedFields))
		{
			for (var sfc = 0; sfc < data.selectedFields.length; sfc++)
			{
				for (var fc = 0; fc < data.fields.length; fc++)
				{
					if (data.selectedFields[sfc] == data.fields[fc])
					{
						// Build the array of objects (columns) used by tabulator and store it in tabulatorColumns
						var tmpColumnObj = {}; // New object that represents a column

						// If was given a definition for this field then use it!
						if (recordFieldsDefinitions != null && recordFieldsDefinitions.hasOwnProperty(data.selectedFields[sfc]))
						{
							tmpColumnObj = recordFieldsDefinitions[data.selectedFields[sfc]];
						}

						tmpColumnObj.field = data.selectedFields[sfc]; // Field name to be linked with dataset field name

						// If there is an alias for this field use it to give a title to this field (header)
						if (data.hasOwnProperty("columnsAliases") && $.isArray(data.columnsAliases))
						{
							tmpColumnObj.title = data.columnsAliases[fc];
						}
						else // otherwise use the field name itself
						{
							tmpColumnObj.title = data.selectedFields[sfc];
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

	_clearTablesorterLocalStorage: function() {
		localStorage.removeItem("tablesorter-filters");
	},

	/**
	 * Set what type of dataset representation was choosen
	 */
	_setDatasetRepresentation: function(data) {

		if (data.hasOwnProperty("datasetRepresentation"))
		{
			FHC_FilterWidget._datasetRepresentation = data.datasetRepresentation;
		}
	},

	/**
	 * Set what type of dataset representation was choosen
	 */
	_setHideMenu: function(data) {

		if (data.hasOwnProperty("hideMenu"))
		{
			FHC_FilterWidget._hideMenu = data.hideMenu;
		}
	},

	/**
	 * Set what type of dataset representation was choosen
	 */
	_setHideOptions: function(data) {

		if (data.hasOwnProperty("hideOptions"))
		{
			FHC_FilterWidget._hideOptions = data.hideOptions;
		}
	},

	/**
	 * Enable/disable the apply and save buttons
	 */
	_toggleApplySaveButtons(addedNewFilterOption) {
		$("#applyFilter").prop("disabled", addedNewFilterOption != true);
		$("#saveCustomFilterButton").prop("disabled", addedNewFilterOption === true);
	}
};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	FHC_FilterWidget.display();

});

