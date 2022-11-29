<?php
$includesArray = array(
	'customCSSs' => [
		'public/css/Cis4/Cms.css',
		#'skin/style.css.php'
	]
);

$this->load->view('templates/CIS-Header', $includesArray);
?>

<div id="cms">
	<?= $content; ?>
</div>

<?php $this->load->view('templates/CIS-Footer', $includesArray); ?>

