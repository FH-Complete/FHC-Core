<?php $this->load->view("templates/header", array("title" => "MessageReply", "jquery" => true, "tinymce" => true)); ?>

	<body>
		<?php
			$href = str_replace("/system/Messages/write", "/system/Messages/send", $_SERVER["REQUEST_URI"]);
		?>
		<form id="sendForm" method="post" action="<?php echo $href; ?>">
			
			<div class="row">
				<div class="span4">
					To:
					<?php
						for($i = 0; $i < count($receivers); $i++)
						{
							$receiver = $receivers[$i];
							// Every 10 recipients a new line
							if ($i > 1 && $i % 10 == 0)
							{
								echo '<br>';
							}
							echo $receiver->Vorname . " " . $receiver->Nachname . "; ";
						}
					?>
					<br>
					Subject: <input type="text" value="" name="subject" size="70"><br/>
					<textarea id="bodyTextArea" name="body"></textarea>
				<?php
					if (isset($variables))
					{
				?>
						Variables:<br>
						<select id="variables" size="12" style="min-width:200px;">
						<?php
							foreach($variables as $key => $val)
							{
						?>
								<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
						<?php
							}
						?>
						</select>
				<?php
					}
				?>
				
				</div>
			</div>
			
			<div class="row">
				<div class="span4">
					<button type="submit">Send</button>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?php echo $this->templatelib->widget("Vorlage_widget", array("title" => "Vorlage")); ?>
				</div>
			</div>
			
			<?php
				if (isset($receivers) && count($receivers) > 0)
				{
			?>
				<div class="row">
					<div class="span4">
						Recipients:<br>
						<select id="recipients">
							<option value="-1">Select...</option>
						<?php
							foreach($receivers as $receiver)
							{
						?>
							<option value="<?php echo $receiver->prestudent_id; ?>"><?php echo $receiver->Nachname . " " . $receiver->Vorname; ?></option>
						<?php
							}
						?>	
						</select>
						<a href="#" id="refresh">Refresh</a>
					</div>
				</div>
				
				<div class="row">
					<div class="span4">
						<textarea id="tinymcePreview"></textarea>
					</div>
				</div>
			<?php
				}
			?>
			
			<?php
				for($i = 0; $i < count($receivers); $i++)
				{
					$receiver = $receivers[$i];
					echo '<input type="hidden" name="prestudents[]" value="' . $receiver->prestudent_id . '">' . "\n";
				}
			?>
			
		</form>
	</body>
	
	<script>
		tinymce.init({
			selector:  "#bodyTextArea"
		});
		
		tinymce.init({
			menubar: false,
			toolbar: false,
			readonly: 1,
			selector: "#tinymcePreview",
			statusbar: true
		});
		
		$(document).ready(function() {
			if ($("#variables"))
			{
				$("#variables").dblclick(function() {
					if ($("#bodyTextArea"))
					{
						tinyMCE.get("bodyTextArea").setContent(tinyMCE.get("bodyTextArea").getContent() + $(this).children(":selected").val());
					}
				});
			}
			
			if ($("#recipients"))
			{
				$("#recipients").change(tinymcePreviewSetContent);
			}
			
			if ($("#refresh"))
			{
				$("#refresh").click(tinymcePreviewSetContent);
			}
		});
		
		function tinymcePreviewSetContent()
		{
			if ($("#tinymcePreview"))
			{
				if ($("#recipients").children(":selected").val() > -1)
				{
					parseMessageText($("#recipients").children(":selected").val(), tinyMCE.get("bodyTextArea").getContent());
				}
				else
				{
					tinyMCE.get("tinymcePreview").setContent("");
				}
			}
		}
		
		function parseMessageText(prestudent_id, text)
		{
			<?php
				$url = str_replace("/system/Messages/write", "/system/Messages/parseMessageText", $_SERVER["REQUEST_URI"]);
				$url = substr($url, 0, strrpos($url, '/'));
			?>
			
			$.ajax({
				dataType: "json",
				url: "<?php echo $url; ?>",
				data: {"prestudent_id": prestudent_id, "text" : text},
				success: function(data, textStatus, jqXHR) {
					tinyMCE.get("tinymcePreview").setContent(data);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
				}
			});
		}
		
		function getVorlageText(vorlage_kurzbz)
		{
			<?php
				$url = str_replace("/system/Messages/write", "/system/Messages/getVorlage", $_SERVER["REQUEST_URI"]);
			?>
			
			$.ajax({
				dataType: "json",
				url: "<?php echo $url; ?>",
				data: {"vorlage_kurzbz": vorlage_kurzbz},
				success: function(data, textStatus, jqXHR) {
					tinyMCE.get("bodyTextArea").setContent(data.retval[0].text);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert(textStatus + " - " + errorThrown);
				}
			});
		}
	</script>
</html>
