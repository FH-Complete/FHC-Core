<div>
	<?php
		foreach ($metaData as $key => $value)
		{
			echo $value->name.' - '.$value->type.'<br>';
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
