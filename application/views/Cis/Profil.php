<?php
$includesArray = array(
	'title' => 'Stundenplan',
	'customJSModules' => ['public/js/apps/Cis/ProfilApp.js'],
	'tabulator5' => true,
	'customCSSs' => ['public/css/components/calendar.css', 'public/css/components/FilterComponent.css'],
	'childs' => ['test1','test2','test3','test4']
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content">
	<h2>Profil</h2>
	
	<hr>
	
	<!-- we can pass information from the php view file to the public js file throughz interpolating data from php into vue props -->
	<Profil <?php echo "uid=$uid" ?> view=<?php echo $view? boolval(1): boolval(0); ?>></Profil>
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
