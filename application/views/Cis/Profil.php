<?php
$includesArray = array(
	'title' => 'Stundenplan',
	'customJSModules' => ['public/js/apps/Cis/ProfilApp.js'],
	'customCSSs' => ['public/css/components/calendar.css']
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content">
	<h2>Profil</h2>
	<hr>
	<p><?php echo $uid; ?></p>
	<Profil></Profil>
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
