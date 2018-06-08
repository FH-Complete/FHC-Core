<div class="row">
	<div class="col-lg-12">

		<!-- Filter name -->
		<div class="filter-name-title"></div>

		<br>

		<!-- Filter options -->
		<div class="panel-group">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" href="#collapseFilterHeader"><?php echo  ucfirst($this->p->t('filter', 'filterEinstellungen')) ?></a>
					</h4>
				</div>
				<div id="collapseFilterHeader" class="panel-collapse collapse">
					<div class="filters-hidden-panel">
						<!-- Filter fields options -->
						<div>
							<?php FilterWidget::loadViewSelectFields(); ?>
						</div>

						<br>

						<!-- Filter filters options -->
						<div>
							<?php FilterWidget::loadViewSelectFilters(); ?>
						</div>

						<br>

						<!-- Filter save options -->
						<div>
							<?php FilterWidget::loadViewSaveFilter(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<br>

		<!-- Filter info top -->
		<div id="datasetActionsTop"></div>

		<!-- Filter table -->
		<div>
			<?php FilterWidget::loadViewTableDataset(); ?>
		</div>

		<!-- Filter info bottom -->
		<div id="datasetActionsBottom"></div>

	</div>
</div>
