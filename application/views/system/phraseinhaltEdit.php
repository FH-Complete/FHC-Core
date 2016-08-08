<?php
	$this->load->view('templates/header', array('title' => 'TemplateEdit', 'jquery' => true, 'textile' => true));
?>

<div class="row">
	<div class="span4">
	  <h2>Phrasentext: <?=$phrasentext_id?></h2>

<form method="post" action="../saveText/<?=$phrasentext_id?>">
	<input type="hidden" name="phrase_inhalt_id" value="<?php echo $phrasentext_id; ?>" />
	<table>
	<tr>
		<td>OE</td>
		<td><?php echo $this->templatelib->widget("organisationseinheit_widget", array('oe_kurzbz' => $orgeinheit_kurzbz)); ?></td>
		<td>Preview</td>
	</tr>
	<tr><td>Orgform</td><td><?php echo $this->templatelib->widget("orgform_widget", array('orgform_kurzbz' => $orgform_kurzbz)); ?></td><td></td></tr>
	<tr><td>Sprache</td><td><?php echo $this->templatelib->widget("sprache_widget", array('sprache' => $sprache)); ?></td><td></td></tr>
	<tr><td>Text</td><td><textarea name="text" style="width:500px; height:300px;" id="markitup"><?php echo $text ?></textarea></td>
		<td valign="top">
			<div id="textile-preview" style="width:500px; height:300px; border: 1px solid gray; overflow: auto;"></div>
		</td>
	</tr>
	<tr><td>Beschreibung</td><td><textarea name="description" style="width:500px; height:100px;"><?php echo $description ?></textarea></td>
	<td><h3>Formatierung (Textile) Hilfe:</h3><br/>
			<code>
				_emphasis_
				*strong*
				??citation??
				-deleted text-
				+inserted text+
				^superscript^
			</code><br/>
		<a href="https://warpedvisions.org/projects/textile-cheat-sheet/" target="_blank">Textile CheatSheet</a>
	</td></tr>
 	<?php
		// This is an example to show that you can load stuff from inside the template file
		//echo $this->templatelib->widget("tinymce_widget", array('name' => 'text', 'text' => $text));
	?>
	<tr><td colspan="2" align="right"><button type="submit">Save</button></td></tr>
</table>
</form>

</div>
</div>


<script>

$(document).ready(function () {
    initTextile();
});

function initTextile() {
    var $content = $('#markitup'); // my textarea
    var $preview = $('#textile-preview'); // the preview div

    //$content.markItUp(); // init markitup

    // use a simple timer to check if the textarea content has changed
    var value = $content.val();
	$preview.html(textile.convert(value));
    setInterval(function () {
        var newValue = $content.val();
        if (value != newValue) {
            value = newValue;
            $preview.html(textile.convert(newValue)); // convert the textile to html
        }
    }, 500);
};

</script>

<!--
<iframe name="TemplatePreview" width="100%" src=""/>
-->
</body>
</html>
