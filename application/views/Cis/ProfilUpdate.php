<?php 
$includesArray = ['title'=> 'Profil Änderungen',
                  'customJSModules'=> ['public/js/apps/Cis/ProfilUpdateRequests.js'],
                  'tabulator5'=> true,
                  'customCSSs'=>['public/css/components/FilterComponent.css','public/css/components/FormUnderline.css'],
                 ];

$this->load->view('templates/CISHTML-Header',$includesArray);
?>


<div id="content">
<profil-update-view id="<?php echo isset($profil_update_id)?$profil_update_id:null ?>"></profil-update-view>
</div>

<?php $this->load->view('templates/CISHTML-Footer',$includesArray); ?>