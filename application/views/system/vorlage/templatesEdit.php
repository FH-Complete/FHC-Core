<?php
	$this->load->view('templates/header', array('title' => 'VorlageEdit', 'jsoneditor' => true));
?>
<div class="row">
	<div class="span4">
	  <h2>Vorlage: <?php echo $vorlage->vorlage_kurzbz; ?></h2>
<form method="post" action="../save">
	Bezeichnung: <input type="text" name="bezeichnung" value="<?php echo $vorlage->bezeichnung; ?>" />
	Anmerkung: <input type="text" name="anmerkung" value="<?php echo $vorlage->anmerkung; ?>" /><br/>

	MimeType:<?php echo $this->widgetlib->widget("mimetype_widget", array('mimetype' => $vorlage->mimetype)); ?>

	Attribute: <?php echo $this->widgetlib->widget("jsoneditor_widget", array('json' => $vorlage->attribute)); ?>

	<input type="hidden" name="attribute" id="attribute" value="<?=$vorlage->attribute?>" />
	<input type="hidden" name="vorlage_kurzbz" value="<?php echo $vorlage->vorlage_kurzbz; ?>" />
	<button type="submit" onclick="getJSON(this.form);">Save</button> 
</form>

</div>
</div>

<script type="text/javascript" >
	// get json
    function getJSON(form)
	{
        form.elements["attribute"].value = JSON.stringify(jsoneditor.get(), null, 2);
		//alert(form.elements["attribute"].value);
    }
</script>
</body>
</html>
