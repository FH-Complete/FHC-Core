<div>
<?php
	$selectedFields = FilterWidget::getSelectedFields();

	for ($selectedFieldsCounter = 0; $selectedFieldsCounter < count($selectedFields); $selectedFieldsCounter++)
	{
		$selectedField = $selectedFields[$selectedFieldsCounter];
?>
		<input type="button" value="<?php echo $selectedField; ?> X" class="remove-field" fieldToRemove="<?php echo $selectedField; ?>">
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
		?>
			<option value="<?php echo $listField; ?>"><?php echo $listField; ?></option>
		<?php
			}
		?>
		</select>
	</span>
</div>
