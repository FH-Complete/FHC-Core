<script language="Javascript" type="text/javascript">

	function resetEventsSFilters()
	{
		$("#addFilter").off('change');
	}

	function addEventsSFilters()
	{
		$("#addFilter").change(function(event) {

			$.ajax({
				url: "<?php echo base_url('index.ci.php/system/Filters/addSelectedFilters'); ?>",
				method: "POST",
				data: {
					fieldName: $(this).val()
				}
			})
			.done(function(data, textStatus, jqXHR) {

				resetSelectedFilters();
				renderSelectedFilters();

				renderTableDataset();

			}).fail(function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			});

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

			$.ajax({
				url: "<?php echo base_url('index.ci.php/system/Filters/applyFilter'); ?>",
				method: "POST",
				data: {
					filterNames: selectFilterName,
					filterOperations: selectFilterOperation,
					filterOperationValues: selectFilterOperationValue,
					filterOptions: selectFilterOption
				}
			})
			.done(function(data, textStatus, jqXHR) {

				// Success

			}).fail(function(jqXHR, textStatus, errorThrown) {

				// Error

			}).always(function() {

				location.reload();

			});

		});

		$(".remove-selected-filter").click(function(event) {
			$.ajax({
				url: "<?php echo base_url('index.ci.php/system/Filters/removeSelectedFilters'); ?>",
				method: "POST",
				data: {
					fieldName: $(this).attr('filterToRemove')
				}
			})
			.done(function(data, textStatus, jqXHR) {
				resetSelectedFilters();
				renderSelectedFilters();
			}).fail(function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			});
		});

	}

	function renderSelectedFilterFields(metaData, activeFilters, activeFiltersOperation, activeFiltersOption)
	{
		var html = '';

		if (metaData.type.toLowerCase().indexOf("int") >= 0)
		{
			html = '<span>';
			html += '	<select class="select-filter-operation form-control">';
			html += '		<option value="equal" ' + (activeFiltersOperation == "equal" ? "selected" : "") + '>equal</option>';
			html += '		<option value="nequal" ' + (activeFiltersOperation == "nqual" ? "selected" : "") + '>not equal</option>';
			html += '		<option value="gt" ' + (activeFiltersOperation == "gt" ? "selected" : "") + '>greater than</option>';
			html += '		<option value="lt" ' + (activeFiltersOperation == "lt" ? "selected" : "") + '>less than</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="number" value="' + activeFilters + '" class="select-filter-operation-value form-control">';
			html += '</span>';
		}
		if (metaData.type.toLowerCase().indexOf('varchar') >= 0)
		{
			html = '<span>';
			html += '	<select class="select-filter-operation form-control">';
			html += '		<option value="contains" ' + (activeFiltersOperation == "contains" ? "selected" : "") + '>contains</option>';
			html += '		<option value="ncontains" ' + (activeFiltersOperation == "ncontains" ? "selected" : "") + '>does not contain</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="text" value="' + activeFilters + '" class="select-filter-operation-value form-control">';
			html += '</span>';
		}
		if (metaData.type.toLowerCase().indexOf('bool') >= 0)
		{
			html = '<span>';
			html += '	<select class="select-filter-operation form-control">';
			html += '		<option value="true" ' + (activeFiltersOperation == "true" ? "selected" : "") + '>is true</option>';
			html += '		<option value="false" ' + (activeFiltersOperation == "false" ? "selected" : "") + '>is false</option>';
			html += '	</select>';
			html += '</span>';
			html += '<span>';
			html += '	<input type="hidden" value="' + activeFilters + '" class="select-filter-operation-value form-control">';
			html += '</span>';
		}
		if (metaData.type.toLowerCase().indexOf('timestamp') >= 0 || metaData.type.toLowerCase().indexOf('date') >= 0)
		{
			var classOperation = 'select-filter-operation-value form-control';
			var classOption = 'select-filter-option form-control';
			var disabled = "";

			if (activeFiltersOperation == "set" || activeFiltersOperation == "nset")
			{
				classOperation += ' hidden-control';
				classOption += ' hidden-control';
				disabled = "disabled";
			}

			html = '<span>';
			html += '	<select class="select-filter-operation form-control">';
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
	}

	function renderSelectedFilters()
	{
		$.ajax({
			url: "<?php echo base_url('index.ci.php/system/Filters/selectFilters'); ?>",
			method: "GET",
			data: {},
			dataType: "json"
		})
		.done(function(data, textStatus, jqXHR) {

			resetEventsSFilters();

			if (data != null)
			{
				var strDropDown = '<option value="">Select a filter to add...</option>';
				$("#addFilter").append(strDropDown);

				for (var i = 0; i < data.selectedFilters.length; i++)
				{
					var selectedFilters = '<div>';

					selectedFilters += '<span>';
					selectedFilters += data.selectedFiltersAliases[i];
					selectedFilters += '</span>';

					selectedFilters += renderSelectedFilterFields(
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

			addEventsSFilters();
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			alert(textStatus);
		});
	}

	function resetSelectedFilters()
	{
		$("#addFilter").html("");
		$("#selectedFilters").html("");
	}

	$(document).ready(function() {
		renderSelectedFilters();
	});

</script>

<div id="selectedFilters"></div>

<div>
	<span>
		Add filter:
	</span>

	<span>
		<select id="addFilter"></select>
	</span>

	<span>
		<input id="applyFilter" type="button" value="Apply">
	</span>
</div>
