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
			'customJSs' => array('public/js/messaging/read.js')
		)
	);
?>
	<body>
		<div class="toggleMessages">
			<div class="btn-group btn-group-toggle" data-toggle="buttons">
				<label class="btn btn-secondary active" id="r">
					<input type="radio" autocomplete="off" checked> Received
				</label>
				<label class="btn btn-secondary" id="s">
					<input type="radio" autocomplete="off"> Sent
				</label>
			</div>
		</div>
		<div id="lstMessagesPanel"></div>
		<div id="readMessagePanel"></div>
	</body>

<?php $this->load->view("templates/FHC-Footer"); ?>
