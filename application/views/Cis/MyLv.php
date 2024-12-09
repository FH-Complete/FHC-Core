<?php
$includesArray = array(
	'title' => 'MyLv',
	'primevue3' => true,
	'customJSModules' => ['public/js/apps/Cis/MyLv/Student.js'],
	'customCSSs' => ['public/css/components/MyLv.css']
);

$this->load->view('templates/CISVUE-Header', $includesArray);
?>

<div id="content">
	<mylv-student></mylv-student>
</div>

<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>
