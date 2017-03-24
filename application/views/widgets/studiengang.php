<select id="<?php echo $html['id']; ?>" name="<?php echo $html['name']; ?>">
	<?php foreach($studiengaenge as $v): ?>
		<?php
			$selected = '';
			if ($v->studiengang_kz == $studiengang)
			{
				$selected = 'selected';
			}
		?>
		<option value="<?php echo $v->studiengang_kz; ?>" <?php echo $selected; ?>>
			<?php
				if ($v->studiengang_kz == '')
				{	
					echo $v->kurzbzlang;
				}
				else
				{
					echo $v->kurzbzlang . ' (' . $v->bezeichnung . ')';
				}
			?>
		</option>
	<?php endforeach; ?>
</select>