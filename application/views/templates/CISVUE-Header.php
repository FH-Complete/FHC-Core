<?php
$includesArray = array(
	'title' => $title ?? 'FH-Complete',
	'vue3' => true,
	'bootstrap5' => true,
	'fontawesome6' => true,
	'axios027' => true,
	'customJSModules' => array_merge([
		'public/js/apps/Cis.js'
	], $customJSModules ?? []),
	'customCSSs' => array_merge([
		'public/css/Cis4/Cis.css'
	], $customCSSs ?? [])
);

$this->load->view('templates/FHC-Header', $includesArray);

?>

<header id="cis-header" class="navbar-dark">
	<cis-menu root-url="<?= site_url('CisVue'); ?>" logo-url="<?= base_url('/public/images/logo-300x160.png'); ?>" avatar-url="<?= base_url('/cis/public/bild.php?src=person&person_id=' . getAuthPersonId()); ?>" :searchbaroptions="searchbaroptions" :searchfunction="searchfunction" />
</header>
<main id="cis-main" class="flex-grow-1 overflow-scroll p-4">
