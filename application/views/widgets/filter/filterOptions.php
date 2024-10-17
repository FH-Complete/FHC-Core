
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

						<!-- Filter save options -->
						<div>
							<?php FilterWidget::loadViewSaveFilter(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<br>
