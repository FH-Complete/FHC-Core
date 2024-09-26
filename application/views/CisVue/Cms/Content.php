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

if(defined('CIS4')){
	$this->load->view('templates/CISVUE-Header', $includesArray);
}else{
	$this->load->view('templates/FHC-Header', $includesArray);
}
?>

<div id="cms">
<?php echo (isset($content_id) ? '<cms-content :content_id="'.$content_id.'" :version="'.$version.'" :sprache="'.$sprache.'" :sichtbar="'.$sichtbar.'" />' : '<cms-news/>'); ?>
</div>

<?php
if (defined('CIS4')) {
	$this->load->view('templates/CISVUE-Footer', $includesArray);
} else {
	$this->load->view('templates/FHC-Footer', $includesArray);
}
 ?>

