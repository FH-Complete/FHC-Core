<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Reply to a message',
			'jquery' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'tinymce' => true,
			'sbadmintemplate' => true,
			'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messaging/message.css'),
			'customJSs' => array('public/js/bootstrapper.js', 'public/js/messaging/messageWriteReply.js')
		)
	);
?>
	<body>
		<div id="wrapper">
			<div id="page-wrapper">
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h3 class="page-header">Send message</h3>
						</div>
					</div>
					<form id="sendForm" method="post" action="<?php echo site_url('/ViewMessage/sendReply'); ?>">
						<div class="row">
							<div class="form-group">
								<div class="col-lg-1 msgfieldcol-left">
									<label>Receiver:</label>
								</div>
								<div class="col-lg-11 msgfieldcol-right">

									<?php echo $receiver; ?>

								</div>
							</div>
						</div>
						<div class="row">
							<div class="form-group">
								<div class="col-lg-1 msgfield msgfieldcol-left">
									<label>Subject:</label>
								</div>
								&nbsp;
								<div class="col-lg-7">
									<input id="subject" class="form-control" type="text" value="<?php echo $subject; ?>" name="subject">
								</div>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-lg-12">
								<label>Message:</label>
								<textarea id="bodyTextArea" name="body"><?php echo $body; ?></textarea>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-lg-12 text-right">
								<button id="sendButton" class="btn btn-default" type="button">Send</button>
							</div>
						</div>

						<input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
						<input type="hidden" name="relationmessage_id" value="<?php echo $relationmessage_id; ?>">
						<input type="hidden" name="token" value="<?php echo $token; ?>">

					</form>
				</div>
			</div>
		</div>
	</body>

<?php $this->load->view("templates/FHC-Footer"); ?>
