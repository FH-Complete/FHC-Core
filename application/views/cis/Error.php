<?php
	$includesArray = array(
		'title' => 'FH-Complete',
		'bootstrap5' => true,
		'fontawesome6' => true,
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="wrapper">
		<div class="alert alert-primary" role="alert">
			<?= $error; ?>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

