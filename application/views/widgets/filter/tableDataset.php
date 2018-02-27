<script language="Javascript" type="text/javascript">

	function callTableSorter()
	{
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
	}

	function renderTableDataset()
	{
		$.ajax({
			url: "<?php echo base_url('index.ci.php/system/Filters/tableDataset'); ?>",
			method: "GET",
			data: {},
		    dataType: "json"
		})
		.done(function(data, textStatus, jqXHR) {

			resetTableDataset();

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
					console.log("No fields to display!!!");
				}
			}
			else
			{
				console.log("No data!!!");
			}

			callTableSorter();

		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			alert(textStatus);
		});
	}

	function resetTableDataset()
	{
		$("#filterTableDataset > thead > tr").html("");
		$("#filterTableDataset > tbody").html("");
	}

	$(document).ready(function() {
		renderTableDataset();
	});

</script>

<div>
	<table class="tablesorter table-bordered table-responsive" id="filterTableDataset">
		<thead>
			<tr></tr>
		</thead>
		<tbody></tbody>
	</table>
</div>
