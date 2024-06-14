<?php
$includesArray = array(
	'primevue3' => true,
	'customJSModules' => ['public/js/apps/Cis/Cms.js'],
	'customCSSs' => [
		'public/css/Cis4/Cms.css',
		#'skin/style.css.php'
	]
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="cms">
<?php echo (isset($content) ? $content : '<content/>'); ?>

</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>