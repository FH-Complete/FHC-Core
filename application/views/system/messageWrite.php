<?php $this->load->view("templates/header", array("title" => "MessageReply", "jquery" => true, "tinymce" => true)); ?>

	<body>
	
		<?php
			$href = str_replace("/system/Messages/write", "/system/Messages/send", $_SERVER["REQUEST_URI"]);
		?>
		
		<form id="sendForm" method="post" action="<?php echo $href; ?>">
		
			<table>
				<tr>
					<td>
						<strong>To:</strong>
					</td>
					<td>
						<?php
							for ($i = 0; $i < count($receivers); $i++)
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
					</td>
				</tr>
				<tr>
					<td height="3px"></td>
				</tr>
				<tr>
					<td>
						<strong>Subject:</strong>&nbsp;
					</td>
					<td>
						<?php
							$subject = '';
							if (isset($message))
							{
								$subject = 'Re: '.$message->subject;
							}
						?>
						<input id="subject" type="text" value="<?php echo $subject; ?>" name="subject" size="70">
					</td>
				</tr>
			</table>
			
			<table width="100%">
				<tr>
					<td width="80%">
						<strong>Message:</strong><br>
						<?php
							$body = '';
							if (isset($message))
							{
								$body = $message->body;
							}
						?>
						<textarea id="bodyTextArea" name="body"><?php echo $body; ?></textarea>
					</td>
					<td width="3%">&nbsp;</td>
					<td width="17%">
						<?php
							if (isset($variables))
							{
						?>
							<div>
								<strong>Variables:</strong><br>
								<select id="variables" size="14" style="min-width:200px;">
								<?php
									foreach($variables as $key => $val)
									{
								?>
										<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
								<?php
									}
								?>
								</select>
							</div>
						<?php
							}
						?>
					</td>
				</tr>
			</table>
			
			<table>
				<tr>
					<td>
						<?php
							echo $this->widgetlib->widget(
								'Vorlage_widget',
								null,
								array('name' => 'vorlage', 'id' => 'vorlageDnD')
							);
						?>
					</td>
					<td>
						&nbsp;
					</td>
					<td>
						<button id="sendButton" type="button">Send</button>
					</td>
				</tr>
			</table>
			
			<br>
			
			<?php
				if (isset($receivers) && count($receivers) > 0)
				{
			?>
				<div>
					Preview:
				</div>
				<div style="border: 1px; border-style: solid;">
					<table width="100%" style="margin: 3px;">
						<tr>
							<td>
								<strong>Recipient:</strong>
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
								&nbsp;
								<strong><a href="#" id="refresh">Refresh</a></strong>
							</td>
						</tr>
						<tr>
							<td>
								&nbsp;
							</td>
						</tr>
						<tr>
							<td width="100%">
								<textarea id="tinymcePreview"></textarea>
							</td>
						</tr>
					</table>
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
			
			<?php
				if (isset($message))
				{
			?>
					<input type="hidden" name="relationmessage_id" value="<?php echo $message->message_id; ?>">
			<?php
				}
			?>
			
		</form>
	
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
			
			if ($("#sendButton") && $("#sendForm"))
			{
				$("#sendButton").click(function() {
					if ($("#subject") && $("#subject").val() != '' && tinyMCE.get("bodyTextArea").getContent() != '')
					{
						$("#sendForm").submit();
					}
					else
					{
						alert("Subject and text are required fields!");
					}
				});
			}
			
			if ($("#vorlageDnD"))
			{
				$("#vorlageDnD").change(function() {
					if (this.value != '')
					{
						<?php
							$url = str_replace("/system/Messages/write", "/system/Messages/getVorlage", $_SERVER["REQUEST_URI"]);
						?>
						
						$.ajax({
							dataType: "json",
							url: "<?php echo $url; ?>",
							data: {"vorlage_kurzbz": this.value},
							success: function(data, textStatus, jqXHR) {
								tinyMCE.get("bodyTextArea").setContent(data.retval[0].text);
								$("#subject").val(data.retval[0].subject);
							},
							error: function(jqXHR, textStatus, errorThrown) {
								alert(textStatus + " - " + errorThrown);
							}
						});
					}
				});
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
	</script>
	
	</body>
	
<?php $this->load->view("templates/footer"); ?>