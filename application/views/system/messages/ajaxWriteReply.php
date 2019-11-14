<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Reply to a message',
			'jquery' => true,
			'jqueryui' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'sbadmintemplate' => true,
			'ajaxlib' => true,
			'dialoglib' => true,
			'tinymce' => true,
			'phrases' => array('global', 'ui'),
			'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messaging/message.css'),
			'customJSs' => array('public/js/bootstrapper.js', 'public/js/messaging/writeReply.js')
		)
	);
?>
	<body>
		<div id="wrapper">
			<div id="page-wrapper">
				<div class="container-fluid">
					<div class="row">
						<div class="col-lg-12">
							<h3 class="page-header"><?php echo $this->p->t('ui', 'sendMessage'); ?></h3>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-lg-1 msgfieldcol-left">
								<label><?php echo $this->p->t('ui', 'receiver'); ?>:</label>
							</div>
							<div class="col-lg-11 msgfieldcol-right">

								<?php echo $receiver; ?>

							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-lg-1 msgfield msgfieldcol-left">
								<label><?php echo $this->p->t('ui', 'subject'); ?>:</label>
							</div>
							&nbsp;
							<div id="subject" class="col-lg-7">

								<?php echo $subject; ?>

							</div>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-12">
							<label><?php echo $this->p->t('ui', 'message'); ?>:</label>
							<textarea id="body">

								<?php echo $body; ?>

							</textarea>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-12 text-right">

							<input id="receiver_id" type="hidden" value="<?php echo $receiver_id; ?>">
							<input id="relationmessage_id" type="hidden" value="<?php echo $relationmessage_id; ?>">
							<input id="token" type="hidden" value="<?php echo $token; ?>">

							<button id="sendButton" class="btn btn-default" type="button"><?php echo $this->p->t('ui', 'send'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>

<?php $this->load->view("templates/FHC-Footer"); ?>
