<?php
$includesArray = array(
	'title' => 'FH-Complete',
	'customJSModules' => ['public/js/apps/Cis/Cms.js'],
	'customCSSs' => [
		'public/css/Cis4/Cms.css',
		#'skin/style.css.php'
	]
);

$this->load->view('templates/FHC-Header', $includesArray);
?>

<div id="cms">
<?php echo (isset($content) ? $content : '<content/>'); ?>
	
</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

