<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Read personal messages',
			'jquery3' => true,
			'jqueryui1' => true,
			'bootstrap3' => true,
			'fontawesome4' => true,
			'sbadmintemplate3' => true,
			'momentjs2' => true,
			'tabulator4' => true,
			'ajaxlib' => true,
			'dialoglib' => true,
			'tinymce4' => true,
			'phrases' => array('global', 'ui'),
			'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messaging/message.css'),
			'customJSs' => array('public/js/bootstrapper.js', 'public/js/messaging/read.js')
		)
	);
?>
	<body>

		<fieldset>

			<span id="toggleMessages" class="toggle">

				<input type="radio" name="toggleMessages" id="received" checked>
				<label for="received">

					<?php echo $this->p->t('global', 'received'); ?>

				</label>

				<input type="radio" name="toggleMessages" id="sent">
				<label for="sent">

					<?php echo ucfirst($this->p->t('global', 'gesendet')); ?>

				</label>

			</span>

			<span class="buttonsSpacer"></span>

			<span>
				<?php echo $writeButton; ?>

				<input id="replyMessage" type="button" value="<?php echo $this->p->t('global', 'reply'); ?>">
			</span>

		</fieldset>


		<div id="lstMessagesPanel"></div>

		<div id="readMessagePanel"></div>

	</body>

<?php $this->load->view("templates/FHC-Footer"); ?>
