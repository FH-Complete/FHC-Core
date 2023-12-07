<?php
$includesArray = array(
	'title' => 'Stundenplan',
	'customJSModules' => ['public/js/apps/Cis/Profil.js'],
	'tabulator5' => true,
	'customCSSs' => ['public/css/components/calendar.css', 'public/css/components/FilterComponent.css','public/css/components/Profil.css'],
	
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>
<!--

	<student-profil></student-profil>
	<component :is="StudentProfil"></component>
	<h2>Profil</h2>
	
	<hr>
	<component :is="<?php //echo "StudentProfil"; ?>" ></component>
	 we can pass information from the php view file to the public js file throughz interpolating data from php into vue props 
	<Profil <?php //echo "uid=$uid" ?> view=<?php //echo $view? boolval(1): boolval(0); ?>></Profil>

-->

<div id="content" >

</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
