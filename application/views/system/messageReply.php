<?php $this->load->view("templates/header", array("title" => "MessageReply", "jquery" => true, "tinymce" => true)); ?>

	<body>
		<?php
			$href = str_replace("/system/Messages/reply", "/system/Messages/sendReply", $_SERVER["REQUEST_URI"]);
		?>
		<form id="sendForm" method="post" action="<?php echo $href; ?>">
			
			<div class="row">
				<div class="span4">
					To: <?php echo $message->uid . " " . $message->vorname . " " . $message->nachname . " " . $message->kontakt; ?><br/>
					Subject: <input type="text" value="Re: <?php echo $message->subject; ?>" name="subject"><br/>
					<textarea id="bodyTextArea" name="body"><?php echo $message->body; ?></textarea>
				</div>
			</div>
			
			<div class="row">
				<div class="span4">
					<button type="submit">Send</button>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?php
						echo $this->widgetlib->widget(
							'Vorlage_widget',
							null,
							array('name' => 'vorlage', 'id' => 'vorlageDnD')
						);
					?>
				</div>
			</div>
			
		</form>
	</body>
	
	<script>
		tinymce.init({
			selector: "#bodyTextArea"
		});
		
		<?php
			$url = str_replace("/system/Messages/reply", "/system/Messages/getVorlage", $_SERVER["REQUEST_URI"]);
		?>
		
		$(document).ready(function() {
			if ($("#vorlageDnD"))
			{
				$("#vorlageDnD").change(function() {
					if (this.value != '')
					{
						$.ajax({
							dataType: "json",
							url: "<?php echo $url; ?>",
							data: {"vorlage_kurzbz": this.value},
							success: function(data, textStatus, jqXHR) {
								tinyMCE.activeEditor.setContent(data.retval[0].text + "<?php echo $message->body; ?>");
							},
							error: function(jqXHR, textStatus, errorThrown) {
								alert(textStatus + " - " + errorThrown);
							}
						});
					}
				});
			}
		});
	</script>
</html>
