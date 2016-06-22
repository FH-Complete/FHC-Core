<?php
	$this->load->view('templates/header', array('title' => 'TemplateEdit', 'tinymce' => true, 'jsonforms' => true));
?>

<div class="row">
	<div class="span4">
	  <h2>Phrase Inhalt: <?=$phrase_inhalt_id?></h2>

<form method="post" action="../saveText/<?=$phrase_inhalt_id?>">
	<input type="hidden" name="phrase_inhalt_id" value="<?php echo $phrase_inhalt_id; ?>" />
	<table>
	<tr><td>OE</td><td><?php echo $this->templatelib->widget("organisationseinheit_widget", array('oe_kurzbz' => $orgeinheit_kurzbz)); ?></td></tr>
	<tr><td>Sprache</td><td><input type="text" name="sprache" value="<?php echo $sprache?>"></td></tr>
	<tr><td>Text</td><td><textarea name="text" cols="50" rows="5"><?php echo $text ?></textarea></td></tr>
	<tr><td>Beschreibung</td><td><textarea name="description" cols="50" rows="5"><?php echo $description ?></textarea></td></tr>
 	<?php
		// This is an example to show that you can load stuff from inside the template file
		//echo $this->templatelib->widget("tinymce_widget", array('name' => 'text', 'text' => $text));
	?>
	<tr><td colspan="2" align="right"><button type="submit">Save</button></td></tr>
</table>
</form>

</div>
</div>

<!--
<iframe name="TemplatePreview" width="100%" src=""/>
-->
</body>
</html>
