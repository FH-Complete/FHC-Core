<?php
$includesArray = array(
	'title' => 'MyLv',
	'customJSModules' => ['public/js/apps/Cis/MyLv/Student.js']
);

$this->load->view('templates/CISVUE-Header', $includesArray);
?>

<div id="content">
	<h2>MyLv</h2>
	<hr>
	<mylv-student></mylv-student>
</div>

<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>
