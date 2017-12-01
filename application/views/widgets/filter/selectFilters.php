<div>
<?php
	$selectedFilters = FilterWidget::getSelectedFilters();

	for ($filtersCounter = 0; $filtersCounter < count($selectedFilters); $filtersCounter++)
	{
		$selectedFilter = $selectedFilters[$filtersCounter];

		$md = FilterWidget::getFilterMetaData($selectedFilter, $metaData);
?>
		<div>

			<span>
				<?php echo $md->name; ?>
			</span>

			<?php echo FilterWidget::renderFilterType($md); ?>

			<span>
				<input type="submit" value="X" class="remove-filter" filterToRemove="<?php echo $md->name; ?>">
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
	?>
			<option value="<?php echo $field; ?>"><?php echo $field; ?></option>
	<?php
		}
	?>
		</select>
	</span>
</div>
