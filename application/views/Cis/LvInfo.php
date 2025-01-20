<?php
$includesArray = array(
	'title' => 'LvInfo',
	'customJSModules' => ['public/js/apps/Cis/LvInfo.js']
);

$this->load->view('templates/CISVUE-Header', $includesArray);
?>

<div id="content">
	<Info studien_semester="<?= $studien_semester ?>" lehrveranstaltung_id="<?= $lvid ?>"></Info>
	
</div>

<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>
