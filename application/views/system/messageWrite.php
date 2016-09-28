<?php $this->load->view("templates/header", array("title" => "MessageReply", "jquery" => true, "tinymce" => true)); ?>

	<body>
		<?php
			$href = str_replace("/system/Messages/write", "/system/Messages/send", $_SERVER["REQUEST_URI"]);
		?>
		<form id="sendForm" method="post" action="<?php echo $href; ?>">
			
			<div class="row">
				<div class="span4">
					To: <?php echo $receiver->vorname . " " . $receiver->nachname; ?><br/>
					Subject: <input type="text" value="" name="subject"><br/>
					<textarea id="bodyTextArea" name="body"></textarea>
				</div>
			</div>
			
			<div class="row">
				<div class="span4">
					<button type="submit">Send</button>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?php echo $this->templatelib->widget("Vorlage_widget", array("title" => "Vorlage")); ?>
				</div>
			</div>
			
		</form>
	</body>
	
	<script>
		tinymce.init({
			selector: "#bodyTextArea"
		});
		
		<?php
			$url = str_replace("/system/Messages/write", "/system/Messages/getVorlage", $_SERVER["REQUEST_URI"]);
		?>
		
		function getVorlageText(vorlage_kurzbz)
		{
			$.ajax({
				dataType: "json",
				url: "<?php echo $url; ?>",
				data: {"vorlage_kurzbz": vorlage_kurzbz},
				success: function(data, textStatus, jqXHR) {
					tinyMCE.activeEditor.setContent(data.retval[0].text);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert(textStatus + " - " + errorThrown);
				}
			});
		}
	</script>
</html>
