<select id="<?php echo $html['id']; ?>" name="<?php echo $html['name']; ?>">
	<?php foreach($studiensemesters as $v): ?>
		<?php
			$selected = '';
			if ($v->studiensemester_kurzbz == $studiensemester)
			{
				$selected = 'selected';
			}
		?>
		<option value="<?php echo $v->studiensemester_kurzbz; ?>" <?php echo $selected; ?>>
			<?php echo $v->beschreibung; ?>
		</option>
	<?php endforeach; ?>
</select>