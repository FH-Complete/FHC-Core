<script language="Javascript" type="text/javascript">
	$(document).ready(function() {

		// Checks if the table contains data (rows)
		if ($('#tableDataset').find('tbody:empty').length == 0
			&& $('#tableDataset').find('tr:empty').length == 0)
		{
			$("#tableDataset").tablesorter(
			{
				widgets: ["zebra", "filter"]
			});
		}

		$("#addField").change(function() {
			$("#filterForm").submit();
		});

		$(".remove-field").each(function() {
			$(this).click(function() {
				$("#rmField").val($(this).attr('fieldToRemove'));
				$("#filterForm").submit();
			});
		});

		$("#addFilter").change(function() {
			$("#filterForm").submit();
		});

		$(".remove-filter").each(function() {
			$(this).click(function() {
				$("#rmFilter").val($(this).attr('filterToRemove'));
				$("#filterForm").submit();
			});
		});

		$(".select-filter-operation").change(function() {
			$("#filterForm").submit();
		});

		$(".select-filter-operation-value").keydown(function(event) {
			if (event.which == 13)
			{
				$("#filterForm").submit();
			}
		});

		$("#saveCustomFilterButton").click(function() {
			$("#saveCustomFilter").val(true);
			$("#filterForm").submit();
		});

	});
</script>
<div class="row">
	<div class="col-lg-12">
		<form class="form-inline" id="filterForm" method="POST" action="<?php echo current_url(); ?>">
			<div>
				<?php FilterWidget::loadViewSelectFields($listFields); ?>
			</div>

			<br>

			<div>
				<?php FilterWidget::loadViewSelectFilters($metaData); ?>
			</div>

			<br>

			<div>
				<?php FilterWidget::loadViewSaveFilter(); ?>
			</div>

			<br>

			<div>
				<?php FilterWidget::loadViewTableDataset($dataset); ?>
			</div>
		</form>
	</div>
</div>
