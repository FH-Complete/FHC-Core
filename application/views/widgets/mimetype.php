<select name="mimetype">
	<?php foreach($items as $item): ?>
	    <option value="<?php echo $item['value']; ?>" <?php if ($item['selected']) echo 'selected'?>>
			<?php echo $item['name']; ?>
		</option>
    <?php endforeach; ?>
</select>
