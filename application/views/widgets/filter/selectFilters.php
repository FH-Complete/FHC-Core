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
	<input type="hidden" id="rmFilter" name="rmFilter" value="">
</div>
<div>
	<span>
		Add filter:
	</span>
	<span>
		<select id="addFilter" name="addFilter">
			<option value="">Select a field to add...</option>
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
