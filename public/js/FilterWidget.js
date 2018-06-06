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
	$(".remove-filter").click(function() {
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
	renderSelectedFields: function() {
		//
		FHC_AjaxClient.ajaxCallGet(
			'system/Filters/selectFields',
			{
				filter_page: FHC_FilterWidget._getFilterPage()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {

					FHC_FilterWidget._resetEventsSF();

					if (data != null)
					{
						var arrayFieldsToDisplay = [];

						if (data.columnsAliases != null && $.isArray(data.columnsAliases))
						{
							arrayFieldsToDisplay = data.columnsAliases;
						}
						else if (data.selectedFields != null && $.isArray(data.selectedFields))
						{
							arrayFieldsToDisplay = data.selectedFields;
						}

						for (var i = 0; i < arrayFieldsToDisplay.length; i++)
						{
							var fieldToDisplay = arrayFieldsToDisplay[i];
							var fieldName = data.selectedFields[i];

							var strHtml = '<span id="dnd' + fieldName + '" class="filter-select-field-dnd-span">';

							strHtml += '<span>';
							strHtml += fieldToDisplay;
							strHtml += '</span>';
							strHtml += '<span><a class="remove-field" fieldToRemove="' + fieldName + '"> X </a></span>';
							strHtml += '</span>';
							$("#filterSelectFieldsDnd").append(strHtml);
						}

						var strDropDown = '<option value="">' + FHC_PhraseLib.t('ui', 'bitteEintragWaehlen') + '</option>';
						$("#addField").append(strDropDown);

						if (data.allSelectedFields != null)
						{
							for (var i = 0; i < data.allSelectedFields.length; i++)
							{
								var fieldName = data.allSelectedFields[i];
								var fieldToDisplay = data.allSelectedFields[i];

								if (data.selectedFields.indexOf(fieldName) < 0)
								{
									if (data.allColumnsAliases != null && $.isArray(data.allColumnsAliases))
									{
										fieldToDisplay = data.allColumnsAliases[i];
									}

									strDropDown = '<option value="' + fieldName + '">' + fieldToDisplay + '</option>';
									$("#addField").append(strDropDown);
								}
							}
						}
					}

					FHC_FilterWidget._dndSF();
					FHC_FilterWidget._addEventsSF();
				}
			}
		);
	},

	/**
	 *
	 */
	renderSelectedFilters: function() {
		//
		FHC_AjaxClient.ajaxCallGet(
			'system/Filters/selectFilters',
			{
				filter_page: FHC_FilterWidget._getFilterPage()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {

					FHC_FilterWidget._resetEventsSFilters();

					if (data != null)
					{
						var strDropDown = '<option value="">' + FHC_PhraseLib.t('ui', 'bitteEintragWaehlen') + '</option>';
						$("#addFilter").append(strDropDown);

						if (data.selectedFilters != null)
						{
							for (var i = 0; i < data.selectedFilters.length; i++)
							{
								var selectedFilters = '<div>';

								selectedFilters += '<span class="filter-options-span">';
								selectedFilters += data.selectedFiltersAliases[i];
								selectedFilters += '</span>';

								selectedFilters += FHC_FilterWidget._getSelectedFilterFields(
									data.selectedFiltersMetaData[i],
									data.selectedFiltersActiveFilters[i],
									data.selectedFiltersActiveFiltersOperation[i],
									data.selectedFiltersActiveFiltersOption[i]
								);

								selectedFilters += '<span>';
								selectedFilters += '<input type="button" value="X" class="remove-selected-filter btn btn-default" filterToRemove="' + data.selectedFilters[i] + '">';
								selectedFilters += '</span>';

								selectedFilters += '</div>';

								$("#selectedFilters").append(selectedFilters);
							}
						}

						if (data.allSelectedFields != null)
						{
							for (var i = 0; i < data.allSelectedFields.length; i++)
							{
								var fieldName = data.allSelectedFields[i];
								var fieldToDisplay = data.allSelectedFields[i];

								if (data.selectedFilters.indexOf(fieldName) < 0)
								{
									if (data.allColumnsAliases != null && $.isArray(data.allColumnsAliases))
									{
										fieldToDisplay = data.allColumnsAliases[i];
									}

									strDropDown = '<option value="' + fieldName + '">' + fieldToDisplay + '</option>';
									$("#addFilter").append(strDropDown);
								}
							}
						}
					}

					FHC_FilterWidget._addEventsSFilters();
				}
			}
		);
	},

	/**
	 *
	 */
	renderTableDataset: function() {
		//
		FHC_AjaxClient.ajaxCallGet(
			'system/Filters/tableDataset',
			{
				filter_page: FHC_FilterWidget._getFilterPage()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {

					FHC_FilterWidget._resetTableDataset();

					if (data != null)
					{
						if (data.checkboxes != null)
						{
							$("#filterTableDataset > thead > tr").append("<th title=\"Select\">Select</th>");
						}

						var arrayFieldsToDisplay = [];

						if (data.columnsAliases != null && $.isArray(data.columnsAliases) && data.columnsAliases.length > 0)
						{
							arrayFieldsToDisplay = data.columnsAliases;
						}
						else if (data.selectedFields != null && $.isArray(data.selectedFields))
						{
							arrayFieldsToDisplay = data.selectedFields;
						}

						/* ------------------------------------------------------------------------------------------------ */
						if (data.checkboxes != null && data.checkboxes != "")
						{
							$("#filterTableDataset > thead > tr").html("<th title=\"Select\">Select</th>");
						}

						for (var i = 0; i < arrayFieldsToDisplay.length; i++)
						{
							var th = arrayFieldsToDisplay[i];

							$("#filterTableDataset > thead > tr").append("<th title=\"" + th + "\">" + th + "</th>");
						}

						if (data.additionalColumns != null && $.isArray(data.additionalColumns))
						{
							for (var i = 0; i < data.additionalColumns.length; i++)
							{
								var th = data.additionalColumns[i];

								$("#filterTableDataset > thead > tr").append("<th title=\"" + th + "\">" + th + "</th>");
							}
						}
						/* ------------------------------------------------------------------------------------------------ */

						if (arrayFieldsToDisplay.length > 0)
						{
							if (data.dataset != null && $.isArray(data.dataset))
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
					}
					else
					{
						console.log("No data!!!");
					}

					FHC_FilterWidget._callTableSorter();

				}
			}
		);
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *
	 */
	_dndSF: function() {
		$(".filter-select-field-dnd-span").draggable({
			containment: "parent",
			cursor: "move",
			opacity: 0.4,
			revert: "invalid",
			revertDuration: 200,
			drag: function(event, ui) {

				var padding = 20;
				var draggedElement = $(this);

				$(".filter-select-field-dnd-span").each(function(i, e) {

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

		$(".filter-select-field-dnd-span").droppable({
			accept: ".filter-select-field-dnd-span",
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

				$(".filter-select-field-dnd-span").each(function(i, e) {

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
	},

	/**
	 *
	 */
	_resetEventsSF: function() {
		$("#addField").off('change');
		$(".remove-field").off('click');
	},

	/**
	 *
	 */
	_addEventsSF: function() {
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

		$(".remove-field").click(function(event) {
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

	/**
	 *
	 */
	_resetSelectedFields: function() {
		$("#filterSelectFieldsDnd").html("");
		$("#addField").html("");
	},

	/**
	 *
	 */
	_resetEventsSFilters: function() {
		$("#addFilter").off('change');
	},

	/**
	 *
	 */
	_addEventsSFilters: function() {
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

		$(".select-filter-operation").change(function() {

			if ($(this).val() == "set" || $(this).val() == "nset")
			{
				$(this).parent().parent().find(".select-filter-operation-value").addClass("hidden-control");
				$(this).parent().parent().find(".select-filter-option").addClass("hidden-control");

				$(this).parent().parent().find(".select-filter-operation-value").prop('disabled', true);
				$(this).parent().parent().find(".select-filter-option").prop('disabled', true);
			}
			else
			{
				$(this).parent().parent().find(".select-filter-operation-value").removeClass("hidden-control");
				$(this).parent().parent().find(".select-filter-option").removeClass("hidden-control");

				$(this).parent().parent().find(".select-filter-operation-value").prop('disabled', false);
				$(this).parent().parent().find(".select-filter-option").prop('disabled', false);
			}

		});

		$("#applyFilter").click(function() {

			var selectFilterName = [];
			var selectFilterOperation = [];
			var selectFilterOperationValue = [];
			var selectFilterOption = [];

			$("#selectedFilters > div").each(function(i, e) {
				var tmpSelectFilterName = $(this).find('.hidden-field-name').val();
				var tmpSelectFilterOperation = $(this).find('.select-filter-operation').val();
				var tmpSelectFilterOperationValue = $(this).find('.select-filter-operation-value:enabled').val();
				var tmpSelectFilterOption = $(this).find('.select-filter-option:enabled').val();

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

		$(".remove-selected-filter").click(function(event) {
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
	_getSelectedFilterFields: function(metaData, activeFilters, activeFiltersOperation, activeFiltersOption) {
		var html = '';

		if (metaData.type.toLowerCase().indexOf("int") >= 0)
		{
			html = '<span>';
			html += '	<select class="form-control select-filter-operation">';
			html += '		<option value="equal" ' + (activeFiltersOperation == "equal" ? "selected" : "") + '>equal</option>';
			html += '		<option value="nequal" ' + (activeFiltersOperation == "nqual" ? "selected" : "") + '>not equal</option>';
			html += '		<option value="gt" ' + (activeFiltersOperation == "gt" ? "selected" : "") + '>greater than</option>';
			html += '		<option value="lt" ' + (activeFiltersOperation == "lt" ? "selected" : "") + '>less than</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="number" value="' + activeFilters + '" class="form-control select-filter-operation-value">';
			html += '</span>';
		}
		if (metaData.type.toLowerCase().indexOf('varchar') >= 0 || metaData.type.toLowerCase() == 'text')
		{
			html = '<span>';
			html += '	<select class="form-control select-filter-operation">';
			html += '		<option value="contains" ' + (activeFiltersOperation == "contains" ? "selected" : "") + '>contains</option>';
			html += '		<option value="ncontains" ' + (activeFiltersOperation == "ncontains" ? "selected" : "") + '>does not contain</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="text" value="' + activeFilters + '" class="form-control select-filter-operation-value">';
			html += '</span>';
		}
		if (metaData.type.toLowerCase().indexOf('bool') >= 0)
		{
			html = '<span>';
			html += '	<select class="form-control select-filter-operation">';
			html += '		<option value="true" ' + (activeFiltersOperation == "true" ? "selected" : "") + '>is true</option>';
			html += '		<option value="false" ' + (activeFiltersOperation == "false" ? "selected" : "") + '>is false</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="hidden" value="' + activeFilters + '" class="form-control select-filter-operation-value">';
			html += '</span>';
		}
		if (metaData.type.toLowerCase().indexOf('timestamp') >= 0 || metaData.type.toLowerCase().indexOf('date') >= 0)
		{
			var classOperation = 'form-control select-filter-operation-value';
			var classOption = 'form-control select-filter-option';
			var disabled = "";

			if (activeFiltersOperation == "set" || activeFiltersOperation == "nset")
			{
				classOperation += ' hidden-control';
				classOption += ' hidden-control';
				disabled = "disabled";
			}

			html = '<span>';
			html += '	<select class="form-control select-filter-operation">';
			html += '		<option value="lt" ' + (activeFiltersOperation == "lt" ? "selected" : "") + '>less than</option>';
			html += '		<option value="gt" ' + (activeFiltersOperation == "gt" ? "selected" : "") + '>greater than</option>';
			html += '		<option value="set" ' + (activeFiltersOperation == "set" ? "selected" : "") + '>is set</option>';
			html += '		<option value="nset" ' + (activeFiltersOperation == "nset" ? "selected" : "") + '>is not set</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="text" value="' + activeFilters + '" class="' + classOperation + '" ' + disabled + '>';
			html += '</span>';
			html += '<span>';
			html += '	<select class="' + classOption + '" ' + disabled + '>';
			html += '		<option value="days" ' + (activeFiltersOption == "days" ? "selected" : "") + '>Days</option>';
			html += '		<option value="months" ' + (activeFiltersOption == "months" ? "selected" : "") + '>Months</option>';
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
	_resetSelectedFilters: function() {
		$("#addFilter").html("");
		$("#selectedFilters").html("");
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
	_getFilterPage: function() {
		return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
	}
};


/**
 * When JQuery is up
 */
$(document).ready(function() {

	$("[data-toggle='collapse']").click(function() {

		var filterOptionsStatus = sessionStorage.getItem('filter-options-status');

		if (filterOptionsStatus != null && filterOptionsStatus == 'closed')
		{
			sessionStorage.setItem('filter-options-status', 'open');
		}
		else
		{
			sessionStorage.setItem('filter-options-status', 'closed');
		}

	});

	var filterOptionsStatus = sessionStorage.getItem('filter-options-status');
	if (filterOptionsStatus != null && filterOptionsStatus == 'open')
	{
		$('.collapse').collapse("show");
	}

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

	FHC_FilterWidget.renderSelectedFields();
	FHC_FilterWidget.renderSelectedFilters();
	FHC_FilterWidget.renderTableDataset();

});
