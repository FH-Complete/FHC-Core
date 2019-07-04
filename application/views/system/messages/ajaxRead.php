<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Read personal messages',
			'jquery' => true,
			'jqueryui' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'sbadmintemplate' => true,
			'momentjs' => true,
			'tabulator' => true,
			'ajaxlib' => true,
			'dialoglib' => true,
			'tinymce' => true,
			'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messaging/message.css'),
			'customJSs' => array('public/js/bootstrapper.js', 'public/js/messaging/read.js')
		)
	);
?>
	<body>
		<fieldset>
			<div id="toggleMessages" class="toggle">
				<input type="radio" name="toggleMessages" id="received" checked>
				<label for="received">Received</label>
				<input type="radio" name="toggleMessages" id="sent">
				<label for="sent">Sent</label>
			</div>
		</fieldset>
		<div id="lstMessagesPanel"></div>
		<div id="readMessagePanel"></div>
	</body>

<?php $this->load->view("templates/FHC-Footer"); ?>
