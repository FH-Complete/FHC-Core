<?php
	$this->load->view(
		'templates/header',
		array('title' => 'Filters', 'tablesort' => true, 'tableid' => 'tableDataset', 'widgets' => 'zebra')
	);
?>
<script language="Javascript" type="text/javascript">
	$(document).ready(function() {

		$("#addFilter").change(function() {
			$("#filterForm").submit();
		});

		$(".remove-filter").each(function() {
			$(this).click(function() {
				$("#rmFilter").val($(this).attr('filterToRemove'));
			});
		});

		$(".select-filter-operation").change(function() {
			$("#filterForm").submit();
		});

		$(".select-filter-operation-value").keyup(function() {
			$("#filterForm").submit();
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
