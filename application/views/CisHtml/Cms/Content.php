<?php
$includesArray = array(
	'customJSModules' => ['public/js/apps/Cis/Cms.js'],
	'primevue3'=>true,
	'customCSSs' => [
		'public/css/Cis4/Cms.css',
		#'skin/style.css.php'
	]
);

if(isset($template_kurzbz)){
	switch($template_kurzbz){
		case 'raum_contentmittitel': 
			$includesArray['tabulator5'] = true; 
			break;
	}
}

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<?php if(!isset($content_id)) {
	echo "
	<h2>News</h2>
	<hr/>";
}?>

<div id="cms">
<?php echo (isset($content_id) ? '<cms-content :content_id="'.$content_id.'" :version="'.$version.'" :sprache="'.$sprache.'" :sichtbar="'.$sichtbar.'" />' : '<cms-news/>'); ?>
	
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>

