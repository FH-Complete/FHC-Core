<div class="row">
	<div class="col-lg-12">

		<!-- Filter name -->
		<div class="filter-name-title"></div>

		<br>

		<?php FilterWidget::loadViewFilterOptions(); ?>

		<br>

		<!-- Filter info top -->
		<div id="datasetActionsTop"></div>

		<!-- Filter table -->
		<div>
			<?php FilterWidget::loadViewDataset(); ?>
		</div>

		<!-- Filter info bottom -->
		<div id="datasetActionsBottom"></div>

	</div>
</div>
