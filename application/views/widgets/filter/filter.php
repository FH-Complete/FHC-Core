<div class="row">
	<div class="col-lg-12">

		<!-- Filter name -->
		<div class="filter-name-title"></div>

		<br>

		<?php FilterWidget::loadViewFilterOptions(); ?>

		<!-- Filter info top -->
		<div id="datasetActionsTop"></div>

		<!-- Filter table -->
		<div id="divFilterWidgetDataset" app="<?php echo $app; ?>" dataset="<?php echo $dataset; ?>" filterid="<?php echo $filterid; ?>">
			<?php FilterWidget::loadViewDataset(); ?>
		</div>

		<!-- Filter info bottom -->
		<div id="datasetActionsBottom"></div>

	</div>
</div>
