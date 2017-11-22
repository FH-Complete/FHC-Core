<div>
	<?php
		foreach ($listFields as $key => $value)
		{
			echo '<input type="button" value="'.$value.'">';
		}
	?>
</div>
<div>
	Add:
	<select>
		<option value="">Select a field to add..</option>
		<?php
			foreach ($listFields as $key => $value)
			{
				echo '<option value="'.$value.'">'.$value.'</option>';
			}
		?>
	</select>
</div>
