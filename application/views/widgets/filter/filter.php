<div class="row">
	<div class="col-lg-12">

		<?php FilterWidget::displayFilterName(); ?>

		<div class="panel-group">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" href="#collapseFilterHeader"><?= ucfirst($this->p->t('filter', 'filterEinstellungen')) ?></a>
					</h4>
				</div>
				<div id="collapseFilterHeader" class="panel-collapse collapse">
					<div class="filters-hidden-panel">
						<div>
							<?php FilterWidget::loadViewSelectFields(); ?>
						</div>

						<br>

						<div>
							<?php FilterWidget::loadViewSelectFilters(); ?>
						</div>

						<br>

						<div>
							<?php FilterWidget::loadViewSaveFilter(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<br>

		<div id="datasetActionsTop"></div>

		<div>
			<?php FilterWidget::loadViewTableDataset(); ?>
		</div>

		<div id="datasetActionsBottom"></div>

	</div>
</div>
