<?php
$includesArray =array(
		'title' => 'Infoterminal',
		'tabulator5' => true,
		'primevue3' => true,
); 
$this->load->view(
	'templates/CISVUE-Header',
	$includesArray	
);
?>

<iframe style="width:100%; height:90vh;" id="Infoterminal" src="<?php echo base_url() . 'cis/infoterminal/?forcelogin=true'; ?>" name="Infoterminal" frameborder="0" >
No iFrames
</iframe>
<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>
