<select id="<?php echo $html['id']; ?>" name="<?php echo $html['name']; ?>">
	<?php foreach($reihungstests as $v): ?>
		<?php
			$selected = '';
			if ($v->reihungstest_id == $reihungstest)
			{
				$selected = 'selected';
			}
		?>
		<option value="<?php echo $v->reihungstest_id; ?>" <?php echo $selected; ?>>
			<?php echo $v->beschreibung; ?>
		</option>
	<?php endforeach; ?>
</select>