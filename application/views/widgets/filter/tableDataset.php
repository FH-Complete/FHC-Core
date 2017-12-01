<?php
	$results = $dataset->retval;

	$selectedFields = FilterWidget::getSelectedFields();
?>

<div>
	<table class="tablesorter" id="tableDataset">
		<thead>
			<tr>
			<?php
				for ($selectedFieldsCounter = 0; $selectedFieldsCounter < count($selectedFields); $selectedFieldsCounter++)
				{
					$selectedFilter = $selectedFields[$selectedFieldsCounter];
			?>
					<th title="<?php echo $selectedFilter; ?>"><?php echo $selectedFilter; ?></th>
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
					for ($selectedFieldsCounter = 0; $selectedFieldsCounter < count($selectedFields); $selectedFieldsCounter++)
					{
						$selectedFilter = $selectedFields[$selectedFieldsCounter];

						if (array_key_exists($selectedFilter, $result))
						{
			?>
						<td>
							<?php

								if (is_bool($result->{$selectedFilter}))
								{
									echo $result->{$selectedFilter} === true ? 'true' : 'false';
								}
								else
								{
									echo $result->{$selectedFilter};
								}
							?>
						</td>
			<?php
						}
					}
			?>
					</tr>
			<?php
				}
			?>
		</tbody>
	</table>
</div>
