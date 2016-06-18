<?php
	$this->load->view('templates/header', array('title' => 'TemplateEdit', 'tinymce' => true));
?>

<div class="row">
	<div class="span4">
	  <h2>Vorlagetext: <?=$vorlagestudiengang_id?></h2>
StudiengangKZ: <?=$studiengang_kz?>
<form method="post" action="../saveText/<?=$vorlagestudiengang_id?>">
	Version: <input type="text" name="version" value="<?php echo $version; ?>" />
	Aktiv: <input type="text" name="aktiv" value="<?php echo $aktiv; ?>" />
	OE:<?php echo $this->templatelib->widget("organisationseinheit_widget", array('oe_kurzbz' => $oe_kurzbz)); ?>
	<input type="hidden" name="vorlagestudiengang_id" value="<?php echo $vorlagestudiengang_id; ?>" />
	<input type="hidden" name="studiengang_kz" value="<?php echo $studiengang_kz; ?>" />
 	<?php 
		// This is an example to show that you can load stuff from inside the template file
		echo $this->templatelib->widget("tinymce_widget", array('name' => 'text', 'text' => $text));
	?>
	<button type="submit">Save</button>
</form>

<form method="post" action="../preview/<?=$vorlagestudiengang_id?>" target="TemplatePreview">
	<button type="submit">Preview</button>
</form>
</div>
</div>

<iframe name="TemplatePreview" width="100%" src="../preview/<?=$vorlagestudiengang_id?>"/>
</body>
</html>
