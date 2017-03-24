<select id="<?php echo $html['id']; ?>" name="<?php echo $html['name']; ?>">
	<?php foreach($stufen as $v): ?>
		<?php
			$selected = '';
			if ($v->stufe == $stufe)
			{
				$selected = 'selected';
			}
		?>
		<option value="<?php echo $v->stufe; ?>" <?php echo $selected; ?>>
			<?php echo $v->beschreibung; ?>
		</option>
	<?php endforeach; ?>
</select>