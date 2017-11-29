<div>
	<?php
		$selectedFields = FilterWidget::getSelectedFields();

		foreach ($selectedFields as $key => $value)
		{
			echo '<input type="submit" value="'.$value.'" onClick="document.getElementById(\'rmField\').value=\''.$value.'\'">';
		}
	?>
	<input type="hidden" id="rmField" name="rmField" value="">
</div>
<div>
	Add:
	<select name="addField" onChange="document.getElementById('filterForm').submit()">
		<option value="">Select a field to add..</option>
		<?php
			foreach ($listFields as $key => $value)
			{
				echo '<option value="'.$value.'">'.$value.'</option>';
			}
		?>
	</select>
</div>
