<?php 
$includesArray = array(
    'title' => 'Profil Änderungen',
    'vue3' => true,
    'bootstrap5' => true,
    'fontawesome6'=> true,
    'axios027' => true,
    'tabulator5' => true,
    'customJSModules' => array(
	'public/js/apps/Cis/ProfilUpdateRequests.js'
    ),
    'customCSSs' => array(
	'public/css/components/FilterComponent.css','public/css/components/FormUnderline.css'
    )
);

if(defined("CIS4"))
{
	$this->load->view(
		'templates/CISVUE-Header',
		$includesArray
	);
}
else
{
	$this->load->view(
		'templates/FHC-Header',
		$includesArray
	);
}
?>

<div id="content">
<profil-update-view id="<?php echo isset($profil_update_id)?$profil_update_id:null ?>"></profil-update-view>
</div>

<?php
if (defined("CIS4")) {
	$this->load->view(
		'templates/CISVUE-Footer',
		$includesArray
	);
} else {
	$this->load->view(
		'templates/FHC-Footer', 
		$includesArray
	);
}
?>