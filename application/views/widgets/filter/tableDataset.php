<?php
	$results = $dataset->retval;

	$selectedFields = FilterWidget::getSelectedFields();
	$additionalColumns = FilterWidget::getAdditionalColumns();
	$checkboxes = FilterWidget::getCheckboxes();
?>

<div>
	<table class="tablesorter" id="tableDataset">
		<thead>
			<tr>
			<?php
				if ($checkboxes != null)
				{
			?>
					<th title="Select">Select</th>
			<?php
				}

				for ($selectedFieldsCounter = 0; $selectedFieldsCounter < count($selectedFields); $selectedFieldsCounter++)
				{
					$selectedField = $selectedFields[$selectedFieldsCounter];
			?>
					<th title="<?php echo $selectedField; ?>"><?php echo $selectedField; ?></th>
			<?php
				}
			?>
			<?php
				for ($additionalColumnsCounter = 0; $additionalColumnsCounter < count($additionalColumns); $additionalColumnsCounter++)
				{
					$additionalColumn = $additionalColumns[$additionalColumnsCounter];
			?>
					<th title="<?php echo $additionalColumn; ?>"><?php echo $additionalColumn; ?></th>
			<?php
				}
			?>
			</tr>
		</thead>
		<tbody>
			<?php
				for ($resultsCounter = 0; $resultsCounter < count($results); $resultsCounter++)
				{
					$result = $results[$resultsCounter];
			?>
					<tr>
			<?php
					if ($checkboxes != null)
					{
			?>
					<td>
						<input type="checkbox" name="<?php echo $checkboxes[0]; ?>[]" value="<?php echo $result->{$checkboxes[0]}; ?>">
					</td>
			<?php
					}

					for ($selectedFieldsCounter = 0; $selectedFieldsCounter < count($selectedFields); $selectedFieldsCounter++)
					{
						$selectedField = $selectedFields[$selectedFieldsCounter];

						if (array_key_exists($selectedField, $result))
						{
							$formattedResult = FilterWidget::formatRaw($selectedField, $result->{$selectedField}, $result);
			?>
						<td>
							<?php
								echo $formattedResult->{$selectedField};
							?>
						</td>
			<?php
						}
					}
					for ($additionalColumnsCounter = 0; $additionalColumnsCounter < count($additionalColumns); $additionalColumnsCounter++)
					{
						$additionalColumn = $additionalColumns[$additionalColumnsCounter];
						$formattedResult = FilterWidget::formatRaw($additionalColumn, null, $result);
			?>
						<td>
							<?php
								echo $formattedResult->{$additionalColumn};
							?>
						</td>
			<?php
					}
			?>
					</tr>
			<?php
				}
			?>
		</tbody>
	</table>
</div>
