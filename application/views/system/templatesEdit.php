<?php
	$this->load->view('templates/header', array('title' => 'TemplateEdit'));
?>
<div class="row">
	<div class="span4">
	  <h2>Vorlage: <?php echo $vorlage->vorlage_kurzbz; ?></h2>
<form method="post" action="../save">
	Bezeichnung: <input type="text" name="bezeichnung" value="<?php echo $vorlage->bezeichnung; ?>" />
	Anmerkung: <input type="text" name="anmerkung" value="<?php echo $vorlage->anmerkung; ?>" />
	MimeType:<?php echo $this->templatelib->widget("mimetype_widget", array('mimetype' => $vorlage->mimetype)); ?>
	<input type="hidden" name="vorlage_kurzbz" value="<?php echo $vorlage->vorlage_kurzbz; ?>" />
	<button type="submit">Save</button> 
</form>

</div>
</div>
</body>
</html>
