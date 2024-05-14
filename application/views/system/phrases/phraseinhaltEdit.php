<?php
	$this->load->view('templates/header', array('title' => 'TemplateEdit', 'jquery3' => true, 'textile' => true));
?>

<div class="row">
	<div class="span4">
	  <h2>Phrasentext: <?=$phrasentext_id?></h2>

<form method="post" action="../saveText/<?=$phrasentext_id?>">
	<input type="hidden" name="phrase_inhalt_id" value="<?php echo $phrasentext_id; ?>" />
	<table>
	<tr>
		<td>OE</td>
		<td>
			<?php
				echo $this->widgetlib->widget(
					'Organisationseinheit_widget',
					array(DropdownWidget::SELECTED_ELEMENT => $orgeinheit_kurzbz),
					array('name' => 'organisationseinheit', 'id' => 'organisationseinheitDnD')
				);
			?>
		</td>
		<td>Preview</td>
	</tr>
	<tr>
		<td>Orgform</td>
		<td>
			<?php
				echo $this->widgetlib->widget(
					'Orgform_widget',
					array(DropdownWidget::SELECTED_ELEMENT => $orgform_kurzbz),
					array('name' => 'orgform', 'id' => 'orgformDnD')
				);
			?>
		</td>
		<td></td>
	</tr>
	<tr>
		<td>Sprache</td>
		<td>
			<?php
				echo $this->widgetlib->widget(
					'Sprache_widget',
					array(DropdownWidget::SELECTED_ELEMENT => $sprache),
					array('name' => 'sprache', 'id' => 'spracheDnD')
				);
			?>
		</td>
		<td></td>
	</tr>
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

			    // use a simple timer to check if the textarea content has changed
			    var value = $content.val();
				$preview.html(textile.parse(value));
			    setInterval(function () {
			        var newValue = $content.val();
			        if (value != newValue) {
			            value = newValue;
			            $preview.html(textile.parse(newValue)); // convert the textile to html
			        }
			    }, 500);
			};

		</script>

	</body>
</html>
