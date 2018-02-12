<div class="panel-group">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a data-toggle="collapse" href="#collapseSelectFilters">Select filters</a>
			</h4>
		</div>
		<div id="collapseSelectFilters" class="panel-collapse collapse">
			<div class="filters-hidden-panel">
				<div>
				<?php
					$selectedFilters = FilterWidget::getSelectedFilters();
					$columnsAliases = FilterWidget::getColumnsAliases();

					for ($filtersCounter = 0; $filtersCounter < count($selectedFilters); $filtersCounter++)
					{
						$selectedFilter = $selectedFilters[$filtersCounter];

						$md = FilterWidget::getFilterMetaData($selectedFilter, $metaData);
						$selectedFieldAlias = $md->name;

						if ($columnsAliases != null)
						{
							$indx = array_search($selectedFilter, $listFields);
							if ($indx !== false)
							{
								$selectedFieldAlias = $columnsAliases[$indx];
							}
						}

				?>
						<div>

							<span>
								<?php echo $selectedFieldAlias; ?>
							</span>

							<?php echo FilterWidget::renderFilterType($md); ?>

							<span>
								<input type="button" value="X" class="remove-filter" filterToRemove="<?php echo $md->name; ?>">
							</span>

						</div>
				<?php
					}
				?>
					<input type="hidden" id="<?php echo FilterWidget::CMD_REMOVE_FILTER; ?>" name="<?php echo FilterWidget::CMD_REMOVE_FILTER; ?>" value="">
				</div>
				<div>
					<span>
						Add filter:
					</span>
					<span>
						<select id="<?php echo FilterWidget::CMD_ADD_FILTER; ?>" name="<?php echo FilterWidget::CMD_ADD_FILTER; ?>">
							<option value="">Select a filter to add...</option>
					<?php
						for ($listFieldsCounter = 0; $listFieldsCounter < count($listFields); $listFieldsCounter++)
						{
							$field = $listFields[$listFieldsCounter];
							$listFieldAlias = $field;

							if ($columnsAliases != null)
							{
								$listFieldAlias = $columnsAliases[$listFieldsCounter];
							}
					?>
							<option value="<?php echo $field; ?>"><?php echo $listFieldAlias; ?></option>
					<?php
						}
					?>
						</select>
					</span>

					<span>
						<input id="applyFilter" name="applyFilter" type="button" value="Apply">
					</span>
				</div>
			</div>
		</div>
	</div>
</div>
