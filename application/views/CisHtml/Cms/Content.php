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

<?php echo (isset($content_id) ? '<cms-content :content_id="'.$content_id.'" :version="'.$version.'" :sprache="'.$sprache.'" :sichtbar="'.$sichtbar.'" />' : '<cms-news/>'); ?>
	
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>