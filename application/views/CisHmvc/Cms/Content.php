<?php
$includesArray = array(
	'title' => 'FH-Complete',
	'customCSSs' => [
		'public/css/Cis4/Cms.css',
		#'skin/style.css.php'
	]
);

$this->load->view('templates/FHC-Header', $includesArray);
?>

<div id="cms">
	<?= $content; ?>
</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

