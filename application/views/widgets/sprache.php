<select name="<?php echo 'sprache'; ?>">
	<?php foreach($items as $item): ?>
	    <option value="<?php echo $item['sprache']; ?>" <?php if ($item['selected']) echo 'selected'?>>
			<?php echo $item['sprache']; ?>
		</option>
    <?php endforeach; ?>
</select>
