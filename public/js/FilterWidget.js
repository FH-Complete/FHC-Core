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
 * Global function used by MavigationWidget JS
 */
function sideMenuHook()
{
	$(".remove-applied-filter").click(function() {
		//
		FHC_AjaxClient.ajaxCallPost(
			'system/Filters/deleteCustomFilter',
			{
				filter_id: $(this).attr('value')
			},
			{
				successCallback: refreshSideMenu // NOTE: to be checked
			}
		);
	});
}

/**
 * FHC_FilterWidget
 */
var FHC_FilterWidget = {
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 *
	 */
	display: function() {
		FHC_FilterWidget._getFilter(FHC_FilterWidget._renderFilterWidget);
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *
	 */
	_getFilter: function(renderFunction) {
		FHC_AjaxClient.ajaxCallGet(
			'system/Filters/getFilter',
			{
				filter_page: FHC_FilterWidget._getFilterPage()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {

					// console.log(data);

					if (FHC_AjaxClient.hasData(data) && typeof renderFunction == 'function')
					{
						renderFunction(FHC_AjaxClient.getData(data));
					}
				}
			}
		);
	},

	/**
	 *
	 */
	_renderFilterWidget: function(data) {

		console.log(data);

		FHC_FilterWidget._setFilterName(data); //

		FHC_FilterWidget._renderDragAndDropFields(data); //

		FHC_FilterWidget._renderDropDownFields(data); //

		FHC_FilterWidget._renderAppliedFilters(data); //

		FHC_FilterWidget._renderDropDownFilters(data); //

		FHC_FilterWidget._renderTableDataset(data); //

	},

	/**
	 *
	 */
	_setFilterName: function(data) {
		if (data.hasOwnProperty('filterName'))
		{
			$(".filter-name-title").html(data.filterName);
		}
	},

	/**
	 *
	 */
	_renderDragAndDropFields: function(data) {

		$(".remove-selected-field").off('click');

		var arrayFieldsToDisplay = [];

		if (data.hasOwnProperty('selectedFields') && $.isArray(data.selectedFields))
		{
			if (data.hasOwnProperty('columnsAliases') && $.isArray(data.columnsAliases))
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

		for (var i = 0; i < arrayFieldsToDisplay.length; i++)
		{
			var fieldToDisplay = arrayFieldsToDisplay[i];
			var fieldName = data.selectedFields[i];

			var strHtml = '<span id="dnd' + fieldName + '" class="drag-and-drop-fields-span">';
			strHtml += '	<span>' + fieldToDisplay + '</span>';
			strHtml += '	<span>';
			strHtml += '		<a class="remove-selected-field" fieldToRemove="' + fieldName + '"> X </a>';
			strHtml += '	</span>';
			strHtml += '</span>';

			$("#dragAndDropFieldsArea").append(strHtml);
		}

		$(".drag-and-drop-fields-span").draggable({
			containment: "parent",
			cursor: "move",
			opacity: 0.4,
			revert: "invalid",
			revertDuration: 200,
			drag: function(event, ui) {

				var padding = 20;
				var draggedElement = $(this);

				$(".drag-and-drop-fields-span").each(function(i, e) {

					if ($(this).attr('id') != draggedElement.attr('id'))
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
		});

		$(".drag-and-drop-fields-span").droppable({
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

				draggedElement.css({left: '0px', top: '10px'});

				var arrayDndId = [];

				$(".drag-and-drop-fields-span").each(function(i, e) {

					arrayDndId[i] = $(this).attr('id').replace('dnd', '');

				});

				//
				FHC_AjaxClient.ajaxCallPost(
					'system/Filters/sortSelectedFields',
					{
						selectedFieldsLst: arrayDndId,
						filter_page: FHC_FilterWidget._getFilterPage()
					},
					{
						successCallback: function(data, textStatus, jqXHR) {
							FHC_FilterWidget._resetSelectedFields();

							FHC_FilterWidget.renderSelectedFields();
							FHC_FilterWidget.renderTableDataset();
						}
					}
				);
			}
		});

		$(".remove-selected-field").click(function(event) {
			//
			FHC_AjaxClient.ajaxCallPost(
				'system/Filters/removeSelectedFields',
				{
					fieldName: $(this).attr('fieldToRemove'),
					filter_page: FHC_FilterWidget._getFilterPage()
				},
				{
					successCallback: function(data, textStatus, jqXHR) {
						FHC_FilterWidget._resetSelectedFields();

						FHC_FilterWidget.renderSelectedFields();
						FHC_FilterWidget.renderTableDataset();
					}
				}
			);
		});
	},

	_renderDropDownFields: function(data) {

		$("#addField").off('change');

		var strDropDown = '';

		if (data.hasOwnProperty('fields') && $.isArray(data.fields))
		{
			for (var i = 0; i < data.fields.length; i++)
			{
				var toBeDisplayed = true;

				for (var j = 0; j < data.selectedFields.length; j++)
				{
					if (data.fields[i] == data.selectedFields[j])
					{
						toBeDisplayed = false;
						break;
					}
				}

				if (toBeDisplayed == true)
				{
					var fieldName = data.fields[i];
					var fieldToDisplay = data.fields[i];

					if (data.hasOwnProperty('columnsAliases') && $.isArray(data.columnsAliases))
					{
						fieldToDisplay = data.columnsAliases[i];
					}

					strDropDown = '<option value="' + fieldName + '">' + fieldToDisplay + '</option>';
					$("#addField").append(strDropDown);
				}
			}
		}

		$("#addField").change(function(event) {
			//
			FHC_AjaxClient.ajaxCallPost(
				'system/Filters/addSelectedFields',
				{
					fieldName: $(this).val(),
					filter_page: FHC_FilterWidget._getFilterPage()
				},
				{
					successCallback: function(data, textStatus, jqXHR) {
						FHC_FilterWidget._resetSelectedFields();

						FHC_FilterWidget.renderSelectedFields();
						FHC_FilterWidget.renderTableDataset();
					}
				}
			);
		});
	},

	/**
	 *
	 */
	_renderAppliedFilters: function(data) {

		if (data.hasOwnProperty('datasetMetadata') && $.isArray(data.datasetMetadata)
			&& data.hasOwnProperty('filters') && $.isArray(data.filters))
		{
			for (var i = 0; i < data.filters.length; i++)
			{
				for (var j = 0; j < data.datasetMetadata.length; j++)
				{
					if (data.filters[i].name == data.datasetMetadata[j].name)
					{
						var appliedFilters = '<div>';

						appliedFilters += '<span class="filter-span-label">';

						if (data.hasOwnProperty('columnsAliases') && $.isArray(data.columnsAliases))
						{
							fieldToDisplay = data.columnsAliases[j];
						}
						else
						{
							fieldToDisplay = data.datasetMetadata[j].name;
						}

						appliedFilters += fieldToDisplay;
						appliedFilters += '</span>';

						appliedFilters += FHC_FilterWidget._getSelectedFilterFields(
							data.datasetMetadata[j],
							data.filters[i]
						);

						appliedFilters += '<span>';
						appliedFilters += '	<input type="button" value="X" class="remove-applied-filter btn btn-default" filterToRemove="' + data.filters[i].name + '">';
						appliedFilters += '</span>';

						appliedFilters += '</div>';

						$("#appliedFilters").append(appliedFilters);
					}
				}
			}
		}

		$(".applied-filter-operation").change(function() {

			if ($(this).val() == "set" || $(this).val() == "nset")
			{
				$(this).parent().parent().find(".applied-filter-condition").addClass("hidden-control");
				$(this).parent().parent().find(".applied-filter-option").addClass("hidden-control");

				$(this).parent().parent().find(".applied-filter-condition").prop('disabled', true);
				$(this).parent().parent().find(".applied-filter-option").prop('disabled', true);
			}
			else
			{
				$(this).parent().parent().find(".applied-filter-condition").removeClass("hidden-control");
				$(this).parent().parent().find(".applied-filter-option").removeClass("hidden-control");

				$(this).parent().parent().find(".applied-filter-condition").prop('disabled', false);
				$(this).parent().parent().find(".applied-filter-option").prop('disabled', false);
			}

		});

		$("#applyFilter").click(function() {

			var selectFilterName = [];
			var selectFilterOperation = [];
			var selectFilterOperationValue = [];
			var selectFilterOption = [];

			$("#selectedFilters > div").each(function(i, e) {
				var tmpSelectFilterName = $(this).find('.hidden-field-name').val();
				var tmpSelectFilterOperation = $(this).find('.applied-filter-operation').val();
				var tmpSelectFilterOperationValue = $(this).find('.applied-filter-condition:enabled').val();
				var tmpSelectFilterOption = $(this).find('.applied-filter-option:enabled').val();

				selectFilterName.push(tmpSelectFilterName);
				selectFilterOperation.push(tmpSelectFilterOperation);
				selectFilterOperationValue.push(tmpSelectFilterOperationValue != null ? tmpSelectFilterOperationValue : "");
				selectFilterOption.push(tmpSelectFilterOption != null ? tmpSelectFilterOption : "");
			});

			//
			FHC_AjaxClient.ajaxCallPost(
				'system/Filters/applyFilter',
				{
					filterNames: selectFilterName,
					filterOperations: selectFilterOperation,
					filterOperationValues: selectFilterOperationValue,
					filterOptions: selectFilterOption,
					filter_page: FHC_FilterWidget._getFilterPage()
				},
				{
					successCallback: function(data, textStatus, jqXHR) {
						FHC_FilterWidget._resetSelectedFilters();

						location.reload();
					}
				}
			);
		});

		$(".remove-applied-filter").click(function(event) {
			//
			FHC_AjaxClient.ajaxCallPost(
				'system/Filters/removeSelectedFilters',
				{
					fieldName: $(this).attr('filterToRemove'),
					filter_page: FHC_FilterWidget._getFilterPage()
				},
				{
					successCallback: function(data, textStatus, jqXHR) {
						FHC_FilterWidget._resetSelectedFilters();

						location.reload();
					}
				}
			);
		});

	},

	/**
	 *
	 */
	_renderDropDownFilters: function(data) {

		$("#addFilter").off('change');

		if (data.hasOwnProperty('fields') && $.isArray(data.fields))
		{
			for (var i = 0; i < data.fields.length; i++)
			{
				var toBeDisplayed = true;

				for (var j = 0; j < data.filters.length; j++)
				{
					if (data.fields[i] == data.filters[j].name)
					{
						toBeDisplayed = false;
						break;
					}
				}

				if (toBeDisplayed == true)
				{
					var fieldName = data.fields[i];
					var fieldToDisplay = data.fields[i];

					if (data.hasOwnProperty('columnsAliases') && $.isArray(data.columnsAliases))
					{
						fieldToDisplay = data.columnsAliases[i];
					}

					strDropDown = '<option value="' + fieldName + '">' + fieldToDisplay + '</option>';
					$("#addFilter").append(strDropDown);
				}
			}
		}

		$("#addFilter").change(function(event) {
			//
			FHC_AjaxClient.ajaxCallPost(
				'system/Filters/addSelectedFilters',
				{
					fieldName: $(this).val(),
					filter_page: FHC_FilterWidget._getFilterPage()
				},
				{
					successCallback: function(data, textStatus, jqXHR) {
						FHC_FilterWidget._resetSelectedFilters();

						FHC_FilterWidget.renderSelectedFilters();
						FHC_FilterWidget.renderTableDataset();
					}
				}
			);
		});

	},

	/**
	 *
	 */
	_resetSelectedFields: function() {
		$("#dragAndDropFieldsArea").html("");
		$("#addField").html("");
	},

	/**
	 *
	 */
	_getSelectedFilterFields: function(metaData, appliedFilter) {

		var html = '';

		if (metaData.type.toLowerCase().indexOf("int") >= 0)
		{
			html = '<span>';
			html += '	<select class="form-control applied-filter-operation">';
			html += '		<option value="equal" ' + (appliedFilter.operation == "equal" ? "selected" : "") + '>equal</option>';
			html += '		<option value="nequal" ' + (appliedFilter.operation == "nqual" ? "selected" : "") + '>not equal</option>';
			html += '		<option value="gt" ' + (appliedFilter.operation == "gt" ? "selected" : "") + '>greater than</option>';
			html += '		<option value="lt" ' + (appliedFilter.operation == "lt" ? "selected" : "") + '>less than</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="number" value="' + appliedFilter.condition + '" class="form-control applied-filter-condition">';
			html += '</span>';
		}
		if (metaData.type.toLowerCase().indexOf('varchar') >= 0 || metaData.type.toLowerCase() == 'text')
		{
			html = '<span>';
			html += '	<select class="form-control applied-filter-operation">';
			html += '		<option value="contains" ' + (appliedFilter.operation == "contains" ? "selected" : "") + '>contains</option>';
			html += '		<option value="ncontains" ' + (appliedFilter.operation == "ncontains" ? "selected" : "") + '>does not contain</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="text" value="' + appliedFilter.condition + '" class="form-control applied-filter-condition">';
			html += '</span>';
		}
		if (metaData.type.toLowerCase().indexOf('bool') >= 0)
		{
			html = '<span>';
			html += '	<select class="form-control applied-filter-operation">';
			html += '		<option value="true" ' + (appliedFilter.operation == "true" ? "selected" : "") + '>is true</option>';
			html += '		<option value="false" ' + (appliedFilter.operation == "false" ? "selected" : "") + '>is false</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="hidden" value="' + appliedFilter.condition + '" class="form-control applied-filter-condition">';
			html += '</span>';
		}
		if (metaData.type.toLowerCase().indexOf('timestamp') >= 0 || metaData.type.toLowerCase().indexOf('date') >= 0)
		{
			var classOperation = 'form-control applied-filter-condition';
			var classOption = 'form-control applied-filter-option';
			var disabled = "";

			if (appliedFilter.operation == "set" || appliedFilter.operation == "nset")
			{
				classOperation += ' hidden-control';
				classOption += ' hidden-control';
				disabled = "disabled";
			}

			html = '<span>';
			html += '	<select class="form-control applied-filter-operation">';
			html += '		<option value="lt" ' + (appliedFilter.operation == "lt" ? "selected" : "") + '>less than</option>';
			html += '		<option value="gt" ' + (appliedFilter.operation == "gt" ? "selected" : "") + '>greater than</option>';
			html += '		<option value="set" ' + (appliedFilter.operation == "set" ? "selected" : "") + '>is set</option>';
			html += '		<option value="nset" ' + (appliedFilter.operation == "nset" ? "selected" : "") + '>is not set</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="text" value="' + appliedFilter.condition + '" class="' + classOperation + '" ' + disabled + '>';
			html += '</span>';
			html += '<span>';
			html += '	<select class="' + classOption + '" ' + disabled + '>';
			html += '		<option value="days" ' + (appliedFilter.option == "days" ? "selected" : "") + '>Days</option>';
			html += '		<option value="months" ' + (appliedFilter.option == "months" ? "selected" : "") + '>Months</option>';
			html += '	</select>';
			html += '</span>';
		}

		html += '<span>';
		html += '	<input type="hidden" value="' + metaData.name + '" class="hidden-field-name">';
		html += '</span>';

		return html;
	},

	/**
	 *
	 */
	_renderTableDataset: function(data) {

		FHC_FilterWidget._resetTableDataset();

		if (data.hasOwnProperty('checkboxes') && data.checkboxes.trim() != '')
		{
			$("#filterTableDataset > thead > tr").append("<th title=\"Select\">Select</th>");
		}

		var arrayFieldsToDisplay = [];

		if (data.hasOwnProperty('selectedFields') && $.isArray(data.selectedFields))
		{
			if (data.hasOwnProperty('columnsAliases') && $.isArray(data.columnsAliases))
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

		for (var i = 0; i < arrayFieldsToDisplay.length; i++)
		{
			var th = arrayFieldsToDisplay[i];

			$("#filterTableDataset > thead > tr").append("<th title=\"" + th + "\">" + th + "</th>");
		}

		if (data.hasOwnProperty('additionalColumns') && $.isArray(data.additionalColumns))
		{
			for (var i = 0; i < data.additionalColumns.length; i++)
			{
				var th = data.additionalColumns[i];

				$("#filterTableDataset > thead > tr").append("<th title=\"" + th + "\">" + th + "</th>");
			}
		}

		if (arrayFieldsToDisplay.length > 0)
		{
			if (data.hasOwnProperty('dataset') && $.isArray(data.dataset))
			{
				for (var i = 0; i < data.dataset.length; i++)
				{
					var record = data.dataset[i];
					var strHtml = '<tr class="' + record.FILTER_CLASS_MARK_ROW + '">';

					if (data.checkboxes != null && data.checkboxes != "")
					{
						strHtml += '<td>';
						strHtml += '<input type="checkbox" name="' + data.checkboxes + '[]" value="' + record[data.checkboxes] + '">';
						strHtml += '</td>';
					}

					$.each(arrayFieldsToDisplay, function(i, fieldToDisplay) {

						if (record.hasOwnProperty(data.selectedFields[i]))
						{
							strHtml += '<td>' + record[data.selectedFields[i]] + '</td>';
						}
					});

					if (data.additionalColumns != null && $.isArray(data.additionalColumns))
					{
						$.each(data.additionalColumns, function(i, additionalColumn) {

							if (record.hasOwnProperty(additionalColumn))
							{
								strHtml += '<td>' + record[additionalColumn] + '</td>';
							}

						});
					}

					strHtml += '</tr>';

					$("#filterTableDataset > tbody").append(strHtml);
				}
			}
			else
			{
				// console.log("No dataset!!!");
			}
		}
		else
		{
			// console.log("No fields to display!!!");
		}

		FHC_FilterWidget._callTableSorter();

	},

	/**
	 *
	 */
	_getFilterPage: function() {
		return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
	},

	/**
	 *
	 */
	_resetSelectedFilters: function() {
		$("#addFilter").html("");
		$("#selectedFilters").html("");
	},

	/**
	 *
	 */
	_resetTableDataset: function() {
		$("#filterTableDataset > thead > tr").html("");
		$("#filterTableDataset > tbody").html("");
	},































	/**
	 *
	 */
	_callTableSorter: function() {
		// Checks if the table contains data (rows)
		if ($('#filterTableDataset').find('tbody:empty').length == 0
			&& $('#filterTableDataset').find('tr:empty').length == 0
			&& $('#filterTableDataset').hasClass('table-condensed'))
		{
			$("#filterTableDataset").tablesorter({
				widgets: ["zebra", "filter"]
			});

			var config = $('#filterTableDataset')[0].config;
			$.tablesorter.updateAll(config, true, null);
		}
	},




};


/**
 * When JQuery is up
 */
$(document).ready(function() {

	if (typeof(Storage) !== "undefined")
	{
		if (sessionStorage.getItem('filter-options-status') && sessionStorage.getItem('filter-options-status') == 'open')
		{
			$('.collapse').collapse("show");
		}
		else
		{
			sessionStorage.setItem('filter-options-status', 'closed');
		}
	}

	$("[data-toggle='collapse']").click(function() {

		if (typeof(Storage) !== "undefined")
		{
			if (sessionStorage.getItem('filter-options-status'))
			{
				if (sessionStorage.getItem('filter-options-status') == 'closed')
				{
					sessionStorage.setItem('filter-options-status', 'open');
				}
				else
				{
					sessionStorage.setItem('filter-options-status', 'closed');
				}
			}
		}

	});

	$("#saveCustomFilterButton").click(function() {
		if ($("#customFilterDescription").val() != '')
		{
			//
			FHC_AjaxClient.ajaxCallPost(
				'system/Filters/saveFilter',
				{
					customFilterDescription: $("#customFilterDescription").val(),
					filter_page: FHC_FilterWidget._getFilterPage()
				},
				{
					successCallback: refreshSideMenu // NOTE: to be checked
				}
			);
		}
		else
		{
			alert("Please fill te description of this filter");
		}
	});

	// FHC_FilterWidget.setFilterName();
	// FHC_FilterWidget.renderSelectedFields();
	// FHC_FilterWidget.renderSelectedFilters();
	// FHC_FilterWidget.renderTableDataset();

	FHC_FilterWidget.display();

});
