<select name="vorlage">
	<?php foreach($vorlage as $v): ?>
	    <option value="<?php echo $v->vorlage_kurzbz; ?>" onClick="getVorlageText(this.value)">
			<?php echo $v->vorlage_kurzbz . (isset($v->bezeichnung) ? " - " . $v->bezeichnung : ""); ?>
		</option>
    <?php endforeach; ?>
</select>
