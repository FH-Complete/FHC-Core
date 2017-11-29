<?php
	$result = $dataset->retval;

	$selectedFields = FilterWidget::getSelectedFields();
?>

<div>
	<table class="tablesorter" id="tableDataset">
		<thead>
			<tr>
			<?php
				foreach ($selectedFields as $key => $value)
				{
					if (array_key_exists($value, $result[0]))
					{
			?>
					<th title="<?php echo $value; ?>"><?php echo $value; ?></th>
			<?php
					}
				}
			?>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ($result as $key => $value)
				{
					echo "<tr>";

					foreach ($selectedFields as $key2 => $value2)
					{
						if (array_key_exists($value2, $value))
						{
			?>
						<td><?php echo $value->{$value2}; ?></td>
			<?php
						}
					}

					echo "</tr>";
				}
			?>
		</tbody>
	</table>
</div>
