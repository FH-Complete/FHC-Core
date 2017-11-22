<div>
	<?php
		foreach ($metaData as $key => $value)
		{
			echo '<div>';
			echo $value->name;

			if ($value->type == 'int4')
			{
	?>
				<select>
					<option value="">=</option>
					<option value="">!=</option>
					<option value="">>=</option>
					<option value=""><=</option>
				</select>
				<input type="number" value="" name="">
				<input type="button" value="X" name="">

	<?php
			}
			elseif ($value->type == 'varchar')
			{
	?>
				<select>
					<option value="">contains</option>
					<option value="">does not contain</option>
				</select>
				<input type="text" value="" name="">
				<input type="button" value="X" name="">
	<?php
			}
			elseif ($value->type == 'bool')
			{
	?>
				<select>
					<option value="">is true</option>
					<option value="">is false</option>
				</select>
				<input type="button" value="X" name="">
	<?php
			}

			echo '</div>';
		}
	?>
</div>
<div>
	Add filter:
	<select>
		<option value="">Select a field to add..</option>
		<?php
			foreach ($metaData as $key => $value)
			{
				echo '<option value="'.$value->name.'">'.$value->name.'</option>';
			}
		?>
	</select>
</div>
