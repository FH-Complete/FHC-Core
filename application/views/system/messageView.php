<?php $this->load->view('templates/header'); ?>

<script type="text/javascript" src="<?php echo base_url('vendor/tinymce/tinymce/tinymce.min.js');?>"></script>

	<body>
		<div class="row">
			<div class="span4">
				<h2>
					From: <?php echo $message->uid . " " . $message->vorname . " " . $message->nachname . " " . $message->kontakt; ?><br/>
					Subject: <?php echo $message->subject; ?><br/>
				</h2>
				<textarea id="bodyTextArea"><?php echo $message->body; ?></textarea>
			</div>
		</div>

		<div class="row">
			<div class="span4">
				<button type="submit" onClick="parent.document.getElementById('MessagesBottom').src = 'Messages/write/<?php echo $message->message_id; ?>/<?php echo $message->person_id; ?>'">
					Reply
				</button>
			</div>
		</div>

		<script>
			tinymce.init({
				selector: '#bodyTextArea',
				readonly : 1,
				statusbar: false,
				toolbar: false,
				menubar: false
			});
		</script>
	</body>
</html>
