<?php
	$includesArray = array(
		'title' => 'Infoterminal',
		'bootstrap5' => true,
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>
	<div id="main" class="vh-100 d-flex flex-column justify-content-center align-items-center text-center">
		<h1 class="display-3 text-danger">Error</h1>
		<p class="lead"><?= htmlspecialchars($message ?? 'Something went wrong', ENT_QUOTES, 'UTF-8'); ?></p>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>