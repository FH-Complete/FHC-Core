<?php
$includesArray = array(
	'title' => 'Stundenplan',
	'customJSModules' => ['public/js/apps/Cis/ProfilApp.js'],
	'tabulator5' => true,
	'customCSSs' => ['public/css/components/calendar.css', 'public/css/components/FilterComponent.css']
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content">
	<h2>Profil</h2>
	
	<hr>
	
	<!-- we can pass information from the php view file to the public js file through interpolating data from php into vue props -->
	<Profil uid="<?php echo $uid ?>" pid="<?php echo $pid ?>"></Profil>
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
