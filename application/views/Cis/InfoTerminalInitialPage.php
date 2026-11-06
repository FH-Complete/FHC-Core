<?php
	$includesArray = array(
		'title' => 'Infoterminal',
		'bootstrap5' => true,
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>
	<div id="main" class="vh-100 d-flex flex-column justify-content-center align-items-center text-center">
		<h1 class="display-3">Hello</h1>
		<p class="lead">Scan your card for more info</p>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>