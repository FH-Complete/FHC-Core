<?php 
$includesArray = ['title'=> 'Profil Änderungen',
                  'customJSModules'=> ['public/js/apps/Cis/ProfilUpdateRequests.js'],
                  'tabulator5'=> true,
                  'customCSSs'=>['public/css/components/FilterComponent.css','public/css/components/FormUnderline.css'],
                 ];

$this->load->view('templates/CISHTML-Header',$includesArray);
?>

<div id="content">

</div>

<?php $this->load->view('templates/CISHTML-Footer',$includesArray); ?>