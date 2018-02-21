	<div id="filterSelectFieldsDnd" class="filter-select-fields-dnd-div">
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
			<span class="filter-select-field-dnd-span">
				<?php echo $selectedFieldAlias; ?>
				<a class="remove-field" fieldToRemove="<?php echo $selectedField; ?>">X</a>
				<input type="hidden" name="<?php echo $selectedField; ?>" value="<?php echo $selectedField; ?>">
			</span>
	<?php
		}
	?>
	</div>
	<input type="hidden" id="<?php echo FilterWidget::CMD_REMOVE_FIELD; ?>" name="<?php echo FilterWidget::CMD_REMOVE_FIELD; ?>" value="">
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
