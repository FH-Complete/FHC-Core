<?php $this->load->view('templates/header'); ?>

	<script type="text/javascript" src="<?php echo base_url('vendor/tinymce/tinymce/tinymce.min.js');?>"></script>

	<body>
		<div class="row">
			<div class="span4">
				<?php
					$href = str_replace("/system/Messages/write", "/system/Messages/send", $_SERVER["REQUEST_URI"]);
					/*$href = substr($href, 0, strrpos($href, "/") - strlen($href));
					$href = substr($href, 0, strrpos($href, "/") - strlen($href));*/
					/*$href .= "/" . $m->message_id . "/" . $person->person_id;*/
				?>
				<form id="sendForm" method="post" action="<?php echo $href; ?>">
					<div class="span4">
						To: <?php echo $message->uid . " " . $message->vorname . " " . $message->nachname . " " . $message->kontakt; ?><br/>
						Subject: <input type="text" value="Re: <?php echo $message->subject; ?>" name="subject"><br/>
						<textarea id="bodyTextArea" name="body"><?php echo $message->body; ?></textarea>
					</div>
					<button type="submit">Send</button>
				</form> 
			</div>
		</div>

		<script>
			tinymce.init({
				selector: '#bodyTextArea'
			});
		</script>
	</body>
</html>
