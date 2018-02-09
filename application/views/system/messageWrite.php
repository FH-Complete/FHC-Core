<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'MessageReply',
		'jquery' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'tinymce' => true,
		'sbadmintemplate' => true,
		'customCSSs' => 'skin/admintemplate_contentonly.css',
		'customJSs' => 'include/js/bootstrapper.js'
	)
);
?>
<body>
<style>
	input[type=text] {
		height: 28px;
		padding: 0px;
	}
	.msgfield label {
		margin-bottom: 0px !important;
		margin-top: 3px;
	}
</style>
<?php
$href = str_replace("/system/Messages/write", "/system/Messages/send", $_SERVER["REQUEST_URI"]);
?>
<div id="wrapper">
	<div id="page-wrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">Send Message</h3>
				</div>
			</div>
			<form id="sendForm" method="post" action="<?php echo $href; ?>">
				<div class="row">
					<div class="form-group">
						<div class="col-lg-1">
							<label>To:</label>
						</div>
						<div class="col-lg-11">
							<?php
							for ($i = 0; $i < count($receivers); $i++)
							{
								$receiver = $receivers[$i];
								// Every 10 recipients a new line
								if ($i > 1 && $i % 10 == 0)
								{
									echo '<br>';
								}
								echo $receiver->Vorname." ".$receiver->Nachname."; ";
							}
							?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group form-inline">
						<div class="col-lg-1 msgfield">
							<label>Subject:</label>
						</div>&nbsp;
						<?php
						$subject = '';
						if (isset($message))
						{
							$subject = 'Re: '.$message->subject;
						}
						?>
						<div class="col-lg-10">
							<input id="subject" class="form-control" type="text" value="<?php echo $subject; ?>"
								   name="subject" size="70">
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-lg-10">
						<label>Message:</label>
						<?php
						$body = '';
						if (isset($message))
						{
							$body = $message->body;
						}
						?>
						<textarea id="bodyTextArea" name="body"><?php echo $body; ?></textarea>
					</div>
					<?php
					if (isset($variables)):
						?>
						<div class="col-lg-2">
							<div class="form-group">
								<label>Variables:</label>
								<select id="variables" class="form-control" size="14" multiple="multiple">
									<?php
									foreach ($variables as $key => $val)
									{
										?>
										<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<br>
				<div class="row">
					<div class="col-lg-3 text-right">
						<?php
						echo $this->widgetlib->widget(
							'Vorlage_widget',
							array('oe_kurzbz' => $oe_kurzbz, 'isAdmin' => $isAdmin),
							array('name' => 'vorlage', 'id' => 'vorlageDnD')
						);
						?>
					</div>
					<div class="col-lg-offset-6 col-lg-1 text-right">
						<button id="sendButton" class="btn btn-default" type="button">Send</button>
					</div>
				</div>
				<?php if (isset($receivers) && count($receivers) > 0): ?>
					<hr>
					<div class="row">
						<div class="col-lg-12">
							<label>Preview:</label>
						</div>
					</div>
					<div class="well">
						<div class="row">
							<div class="col-lg-5">
								<div class="form-grop form-inline">
									<label>Recipient:</label>
									<select id="recipients">
										<option value="-1">Select...</option>
										<?php
										$idtype = $personOnly === true ? 'person_id' : 'prestudent_id';
										foreach ($receivers as $receiver)
										{
											?>
											<option value="<?php echo $receiver->{$idtype}; ?>"><?php echo $receiver->Vorname." ".$receiver->Nachname; ?></option>
											<?php
										}
										?>
									</select>
									&nbsp;
									<strong><a href="#" id="refresh">Refresh</a></strong>
								</div>
							</div>
							<div class="col-lg-2">

							</div>
						</div>
						<br>
						<textarea id="tinymcePreview"></textarea>
					</div>
					<?php
				endif;
				?>

				<?php
				for ($i = 0; $i < count($receivers); $i++)
				{
					$receiver = $receivers[$i];
					if ($personOnly === true)
					{
						$receiverid = $receiver->person_id;
						$fieldname = 'persons[]';
					}
					else
					{
						$receiverid = $receiver->prestudent_id;
						$fieldname = 'prestudents[]';
					}
					echo '<input type="hidden" name="'.$fieldname.'" value="'.$receiverid.'">'."\n";
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
		</div>
	</div>
</div>
<script>
	tinymce.init({
		selector: "#bodyTextArea",
		height: 155
	});

	tinymce.init({
		menubar: false,
		toolbar: false,
		readonly: 1,
		selector: "#tinymcePreview",
		statusbar: true,
		plugins: "autoresize"
	});

	$(document).ready(function ()
	{
		if ($("#variables"))
		{
			$("#variables").dblclick(function ()
			{
				if ($("#bodyTextArea"))
				{
					//if editor active add at cursor position, otherwise at end
					if (tinymce.activeEditor.id === "bodyTextArea")
						tinymce.activeEditor.execCommand('mceInsertContent', false, $(this).children(":selected").val());
					else
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
			$("#sendButton").click(function ()
			{
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
			$("#vorlageDnD").change(function ()
			{
				if (this.value != '')
				{
					<?php
					$url = str_replace("/system/Messages/write", "/system/Messages/getVorlage", $_SERVER["REQUEST_URI"]);
					?>

					$.ajax({
						dataType: "json",
						url: "<?php echo $url; ?>",
						data: {"vorlage_kurzbz": this.value},
						success: function (data, textStatus, jqXHR)
						{
							tinyMCE.get("bodyTextArea").setContent(data.retval[0].text);
							$("#subject").val(data.retval[0].subject);
						},
						error: function (jqXHR, textStatus, errorThrown)
						{
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

	function parseMessageText(receiver_id, text)
	{
		<?php
		//replacing url (can have sender id at end)
		$url = preg_replace("/\/system\/Messages\/write(\/.*)?/", "/system/Messages/parseMessageText", $_SERVER["REQUEST_URI"]);

		$idtype = $personOnly === true ? 'person_id' : 'prestudent_id';
		?>

		$.ajax({
			dataType: "json",
			url: "<?php echo $url; ?>",
			data: {"<?php echo $idtype ?>": receiver_id, "text": text},
			success: function (data, textStatus, jqXHR)
			{
				tinyMCE.get("tinymcePreview").setContent(data);
			},
			error: function (jqXHR, textStatus, errorThrown)
			{
				alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
			}
		});
	}
</script>

</body>

<?php $this->load->view("templates/FHC-Footer"); ?>
