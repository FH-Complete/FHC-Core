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
 * Global function used by NavigationWidget JS to bind events to side menu elements
 */
function sideMenuHook()
{
	$(".remove-custom-filter").click(function() {

		// Ajax call to remove a custom filter
		FHC_AjaxClient.ajaxCallPost(
			"system/Filters/removeCustomFilter",
			{
				filter_id: $(this).attr("value"), // filter_id of the filter to be removed
				filter_page: FHC_FilterWidget.getFilterPage()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {

					if (FHC_AjaxClient.isError(data))
					{
						console.log(FHC_AjaxClient.getError(data));
					}
					else
					{
						// If a success and refreshSideMenu is a valid function then call it to refresh the side menu
						if (typeof refreshSideMenu == "function")
						{
							refreshSideMenu();
						}
					}
				}
			}
		);
	});
}

/**
 * FHC_FilterWidget this object is used to render the GUI of a filter widget and to operate with it
 */
var FHC_FilterWidget = {
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * To display the FilterWidget using the loaded data prenset in the session
	 */
	display: function() {

		FHC_FilterWidget._getFilter(FHC_FilterWidget._renderFilterWidget);
	},

	/**
	 * Alias call to method display only to inprove the readability of the code
	 */
	refresh: function() {

		FHC_FilterWidget.display();
	},

	/**
	 * To retrive the page where the FilterWidget is used, using the FHC_JS_DATA_STORAGE_OBJECT
	 */
	getFilterPage: function() {

		return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
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
			console.log(FHC_AjaxClient.getError(data));
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
			console.log(FHC_AjaxClient.getError(data));
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
		$("#filterTableDataset > thead > tr").html("");
		$("#filterTableDataset > tbody").html("");
	},

	/**
	 * To get via Ajax all the data related to the FilterWidget present in the given page
	 * If the parameter renderFunction is a valid function, is called on success
	 */
	_getFilter: function(renderFunction) {

		FHC_AjaxClient.ajaxCallGet(
			"system/Filters/getFilter",
			{
				filter_page: FHC_FilterWidget.getFilterPage()
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
	},

	/**
	 * This method calls all the other methods needed to rendere the GUI for a FilterWidget
	 * The parameter data contains all the data about the FilterWidget and it is given as parameter
	 * to all the methods that here are called
	 * NOTE: think very carefully before changing the order of the calls
	 */
	_renderFilterWidget: function(data) {

		console.log(data);

		FHC_FilterWidget._initSessionStorage(); // initialize the session storage
		FHC_FilterWidget._turnOffEvents(); // turns all the events off
		FHC_FilterWidget._resetGUI(); // Reset the entire GUI

		// Render the GUI for this FilterWidget
		FHC_FilterWidget._setFilterName(data); // set the name in the GUI
		FHC_FilterWidget._renderDragAndDropFields(data); // render the fields drag and drop GUI
		FHC_FilterWidget._renderDropDownFields(data); // render the fields drop-down
		FHC_FilterWidget._renderAppliedFilters(data); // render the GUI for the applied filters
		FHC_FilterWidget._renderDropDownFilters(data); // render the filters drop-down
		FHC_FilterWidget._renderTableDataset(data); // render the table GUI

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

		$("[data-toggle='collapse']").off("click");
		$(".drag-and-drop-fields-span").off("draggable");
		$(".drag-and-drop-fields-span").off("droppable");
		$(".remove-selected-field").off("click");
		$("#addField").off("change");
		$(".applied-filter-operation").off("change");
		$(".remove-applied-filter").off("click");
		$("#addFilter").off("change");
		$("#applyFilter").off("click");
		$("#saveCustomFilterButton").off("click");
		FHC_FilterWidget._disableTableSorter();
	},

	/**
	 * Turns all the events on
	 * NOTE: must be aligned to _turnOffEvents
	 */
	_turnOnEvents: function() {

		$("[data-toggle='collapse']").click(FHC_FilterWidget._dataToggleCollapseEvent); // Click event to collapse or to open the filter options panel
		$(".drag-and-drop-fields-span").draggable(FHC_FilterWidget._draggableConf); // draggable event on selected fields
		$(".drag-and-drop-fields-span").droppable(FHC_FilterWidget._droppableConf); // droppable event on selected fields
		$(".remove-selected-field").click(FHC_FilterWidget._revomeSelectedFieldsEvent); // Click event on the "X" link
		$("#addField").change(FHC_FilterWidget._addFieldEvent); // Change event on the fields drop-down to add new fields
		$(".applied-filter-operation").change(FHC_FilterWidget._appliedFiltersOperationsEvent); // Change event on the operation drop-down
		$(".remove-applied-filter").click(FHC_FilterWidget._removeAppliedFiltersEvent); // Click event to the "X" button to remove an applied filter
		$("#addFilter").change(FHC_FilterWidget._addFilterEvent); // Click event on the applied filters drop-down to add a new filter to the dataset
		$("#applyFilter").click(FHC_FilterWidget._applyFilterEvent); // Click event on the applied filters drop-down to apply filters to the dataset
		$("#saveCustomFilterButton").click(FHC_FilterWidget._saveCustomFilterButtonEvent); // Click evento to for the save custom filter button
		FHC_FilterWidget._enableTableSorter(); // enable the tablesorter
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
				"system/Filters/sortSelectedFields",
				{
					selectedFields: arrayDndId,
					filter_page: FHC_FilterWidget.getFilterPage()
				},
				{
					successCallback: function(data, textStatus, jqXHR) {
						FHC_FilterWidget._cleanTablesorterLocalStorage();
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
 			"system/Filters/removeSelectedField",
 			{
 				selectedField: $(this).attr("fieldToRemove"),
 				filter_page: FHC_FilterWidget.getFilterPage()
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
	},

	/**
	 * Event function used by the add field drop-down
	 */
	_addFieldEvent: function(event) {

		FHC_AjaxClient.ajaxCallPost(
			"system/Filters/addSelectedField",
			{
				selectedField: $(this).val(),
				filter_page: FHC_FilterWidget.getFilterPage()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					FHC_FilterWidget._cleanTablesorterLocalStorage();
					FHC_FilterWidget._failOrRefresh(data, textStatus, jqXHR);
				}
			}
		);
	},

	/**
	 * Event function used by the apply filter button
	 */
	_applyFilterEvent: function() {

		var appliedFilters = [];
		var appliedFiltersOperations = [];
		var appliedFiltersConditions = [];
		var appliedFiltersOptions = [];

		$("#appliedFilters > div").each(function(i, e) {
			appliedFilters.push($(this).find(".hidden-field-name").val());
			appliedFiltersOperations.push($(this).find(".applied-filter-operation").val());
			appliedFiltersConditions.push($(this).find(".applied-filter-condition:enabled").val());
			appliedFiltersOptions.push($(this).find(".applied-filter-option:enabled").val());
		});

		FHC_AjaxClient.ajaxCallPost(
			"system/Filters/applyFilters",
			{
				appliedFilters: appliedFilters,
				appliedFiltersOperations: appliedFiltersOperations,
				appliedFiltersConditions: appliedFiltersConditions,
				appliedFiltersOptions: appliedFiltersOptions,
				filter_page: FHC_FilterWidget.getFilterPage()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					FHC_FilterWidget._failOrReload(data, textStatus, jqXHR);
				}
			}
		);
	},

	/**
	 * Event function used to remove an applied filter to the dataset
	 */
	_removeAppliedFiltersEvent: function(event) {

		FHC_AjaxClient.ajaxCallPost(
			"system/Filters/removeAppliedFilter",
			{
				appliedFilter: $(this).attr("filterToRemove"),
				filter_page: FHC_FilterWidget.getFilterPage()
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
 			"system/Filters/addFilter",
 			{
 				filter: $(this).val(),
 				filter_page: FHC_FilterWidget.getFilterPage()
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
 				"system/Filters/saveCustomFilter",
 				{
 					customFilterDescription: $("#customFilterDescription").val(),
 					filter_page: FHC_FilterWidget.getFilterPage()
 				},
 				{
 					successCallback: refreshSideMenu // NOTE: to be checked
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
			for (var i = 0; i < data.fields.length; i++)
			{
				var toBeDisplayed = true;

				for (var j = 0; j < elements.length; j++)
				{
					var elementName = elements[j].hasOwnProperty("name") ? elements[j].name : elements[j];

					if (data.fields[i] == elementName)
					{
						toBeDisplayed = false;
						break;
					}
				}

				if (toBeDisplayed == true)
				{
					var fieldName = data.fields[i];
					var fieldToDisplay = data.fields[i];

					if (data.hasOwnProperty("columnsAliases") && $.isArray(data.columnsAliases))
					{
						fieldToDisplay = data.columnsAliases[i];
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
			for (var i = 0; i < data.filters.length; i++)
			{
				for (var j = 0; j < data.datasetMetadata.length; j++)
				{
					if (data.filters[i].name == data.datasetMetadata[j].name)
					{
						var appliedFilters = "<div>";

						appliedFilters += "<span class='filter-span-label'>";

						if (data.hasOwnProperty("columnsAliases") && $.isArray(data.columnsAliases))
						{
							fieldToDisplay = data.columnsAliases[j];
						}
						else
						{
							fieldToDisplay = data.datasetMetadata[j].name;
						}

						appliedFilters += fieldToDisplay;
						appliedFilters += "</span>";

						appliedFilters += FHC_FilterWidget._renderSingleAppliedFilter(data.filters[i], data.datasetMetadata[j]);

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
		if (metaData.type.toLowerCase().indexOf("varchar") >= 0 || metaData.type.toLowerCase() == "text")
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
	 * Renders the table for the FilterWidget
	 * The data to be displayed are retrived from the parameter data
	 */
	_renderTableDataset: function(data) {

		if (data.hasOwnProperty("checkboxes") && data.checkboxes.trim() != "")
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
				for (var i = 0; i < data.dataset.length; i++)
				{
					var record = data.dataset[i];
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

		// Checks if the table contains data (rows)
		if ($("#filterTableDataset").find("tbody:empty").length == 0
			&& $("#filterTableDataset").find("tr:empty").length == 0
			&& $("#filterTableDataset").hasClass("table-condensed"))
		{
			$("#filterTableDataset").tablesorter({
				widgets: ["zebra", "filter"],
				widgetOptions: {
					filter_saveFilters : true
				}
			});

			// reset filter storage if there is a filter id in url TODO: find better solution
			var filter_id = FHC_AjaxClient.getUrlParameter("filter_id");
			if (typeof filter_id !== "undefined") FHC_FilterWidget._cleanTablesorterLocalStorage();

			$.tablesorter.updateAll($("#filterTableDataset")[0].config, true, null);
		}
	},

	/**
	 * Disable the tablesorter
	 */
	_disableTableSorter: function() {

		$("#filterTableDataset").trigger("disable");
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
				for (var i = 0; i < data.selectedFields.length; i++)
				{
					for (var j = 0; j < data.fields.length; j++)
					{
						if (data.selectedFields[i] == data.fields[j])
						{
							arrayFieldsToDisplay[i] = data.columnsAliases[j];
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
	 * Tablesorter filter local storage clean
	 */
	_cleanTablesorterLocalStorage: function() {

		$("#filterTableDataset").trigger("filterResetSaved");
	}

};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	FHC_FilterWidget.display();

});
