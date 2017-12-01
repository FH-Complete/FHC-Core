<?php
	$this->load->view(
		'templates/header',
		array('title' => 'Filters', 'tablesort' => true, 'tableid' => 'tableDataset', 'widgets' => 'zebra')
	);
?>
<script language="Javascript" type="text/javascript">
	$(document).ready(function() {

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

	});
</script>
<body>
	<form id="filterForm" method="POST" action="<?php echo current_url(); ?>">
		<div>
			<?php FilterWidget::loadViewSelectFields($listFields); ?>
		</div>

		<br>

		<div>
			<?php FilterWidget::loadViewSelectFilters($metaData); ?>
		</div>

		<br>

		<div>
			<?php FilterWidget::loadViewTableDataset($dataset); ?>
		</div>
	</form>
</body>
<?php
	$this->load->view('templates/footer');
?>
