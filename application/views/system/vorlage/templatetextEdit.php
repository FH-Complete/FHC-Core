<?php
	$this->load->view('templates/header', array('title' => 'VorlageEdit', 'tinymce4' => true, 'jsonforms' => true));
?>

<div class="row">
	<div class="span4">
	  <h2>Vorlagetext: <?=$vorlagestudiengang_id?></h2>
	<!--StudiengangKZ: <?=$studiengang_kz?>-->
<form method="post" action="../saveText/<?=$vorlagestudiengang_id?>">

	OE:	<?php
			echo $this->widgetlib->widget(
				'Organisationseinheit_widget',
				array(
					DropdownWidget::SELECTED_ELEMENT => $oe_kurzbz,
					'typ' => array('Erhalter', 'Studienzentrum', 'Studiengang', 'Lehrgang')
				),
				array('name' => 'organisationseinheit', 'id' => 'organisationseinheitDnD')
			);
		?>
	Sprache:	<?php
					echo $this->widgetlib->widget(
						'Sprache_widget',
						array(DropdownWidget::SELECTED_ELEMENT => $sprache),
						array('name' => 'sprache', 'id' => 'spracheDnD')
					);
				?>
	OrgForm:	<?php
					echo $this->widgetlib->widget(
						'Orgform_widget',
						array(DropdownWidget::SELECTED_ELEMENT => $orgform_kurzbz),
						array('name' => 'orgform', 'id' => 'orgformDnD')
					);
				?>
	Version: <input type="text" name="version" value="<?php echo $version; ?>" size="1" />
	Aktiv: <input type="text" name="aktiv" value="<?php echo $aktiv; ?>" size="1" />
	<input type="hidden" name="vorlagestudiengang_id" value="<?php echo $vorlagestudiengang_id; ?>" />
	<input type="hidden" name="studiengang_kz" value="<?php echo $studiengang_kz; ?>" />
 	<?php
		// This is an example to show that you can load stuff from inside the template file
		echo $this->widgetlib->widget("tinymce_widget", array('name' => 'text', 'text' => $text));
	?>
	<button type="submit">Save</button>
</form>
<hr/><h2>Preview-Data</h2>
<form method="post" action="../preview/<?=$vorlagestudiengang_id?>" target="VorlagePreview">
	<?php echo $this->widgetlib->widget("jsonforms_widget", array('id' => 'dataform', 'schema' => $schema)); ?>
	<input type="hidden" name="formdata" id="formdata" value="" />
	<button type="submit" onclick="getFormdata(this.form);">Preview</button>
</form>
</div>
</div>

<script type="text/javascript" >
	// get json
    function getFormdata(form)
	{
        form.elements["formdata"].value = JSON.stringify(bf.getData(), null, 2);
		//alert(form.elements["formdata"].value);
    }
</script>

<iframe name="VorlagePreview" width="100%" src=""/>
</body>
</html>
