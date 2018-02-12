	<div class="panel-group">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">
					<a data-toggle="collapse" href="#collapseSelectFields">Select columns</a>
				</h4>
			</div>
			<div id="collapseSelectFields" class="panel-collapse collapse">
				<div class="filters-hidden-panel">
					<div>
					<?php
						$selectedFields = FilterWidget::getSelectedFields();
						$columnsAliases = FilterWidget::getColumnsAliases();

						for ($selectedFieldsCounter = 0; $selectedFieldsCounter < count($selectedFields); $selectedFieldsCounter++)
						{
							$selectedField = $selectedFields[$selectedFieldsCounter];
							$selectedFieldAlias = $selectedField;

							if ($columnsAliases != null)
							{
								$indx = array_search($selectedField, $listFields);
								if ($indx !== false)
								{
									$selectedFieldAlias = $columnsAliases[$indx];
								}
							}
					?>
							<input type="button" value="<?php echo $selectedFieldAlias; ?> X" class="remove-field" fieldToRemove="<?php echo $selectedField; ?>">
					<?php
						}
					?>
						<input type="hidden" id="<?php echo FilterWidget::CMD_REMOVE_FIELD; ?>" name="<?php echo FilterWidget::CMD_REMOVE_FIELD; ?>" value="">
					</div>
					<div>
						<span>
							Add field:
						</span>
						<span>
							<select id="<?php echo FilterWidget::CMD_ADD_FIELD; ?>" name="<?php echo FilterWidget::CMD_ADD_FIELD; ?>">
								<option value="">Select a field to add..</option>
							<?php
								for ($listFieldsCounter = 0; $listFieldsCounter < count($listFields); $listFieldsCounter++)
								{
									$listField = $listFields[$listFieldsCounter];
									$listFieldAlias = $listField;

									if ($columnsAliases != null)
									{
										$listFieldAlias = $columnsAliases[$listFieldsCounter];
									}
							?>
								<option value="<?php echo $listField; ?>"><?php echo $listFieldAlias; ?></option>
							<?php
								}
							?>
							</select>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
