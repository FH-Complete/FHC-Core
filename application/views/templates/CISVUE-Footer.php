<?php
$includesArray = array(
	'title' => $title ?? 'FH-Complete',
	'vue3' => true,
	'bootstrap5' => true,
	'fontawesome6' => true,
	'axios027' => true,
	'fhcApps' => array_merge([
		'Cis'
	], $fhcApps ?? []),
	'customCSSs' => array_merge([
		'public/css/Cis4/Cis.css'
	], $customCSSs ?? [])
);
?>

</main>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>
