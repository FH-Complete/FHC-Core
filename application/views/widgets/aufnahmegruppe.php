<select id="<?php echo $html['id']; ?>" name="<?php echo $html['name']; ?>">
	<?php foreach($aufnahmegruppen as $v): ?>
		<?php
			$selected = '';
			if ($v->gruppe_kurzbz == $aufnahmegruppe)
			{
				$selected = 'selected';
			}
		?>
		<option value="<?php echo $v->gruppe_kurzbz; ?>" <?php echo $selected; ?>>
			<?php echo $v->beschreibung; ?>
		</option>
	<?php endforeach; ?>
</select>