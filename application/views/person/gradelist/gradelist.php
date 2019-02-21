<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Gradelist',
			'jquery' => true,
			'jqueryui' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'ajaxlib' => true,
			'customCSSs' => array(
				'public/css/tools/gradelist.css',
				'public/css/fhcomplete.css'
			),
			'customJSs' => array(
				'public/js/bootstrapper.js'
			)
		)
	);
?>
<body>
	<div id="wrapper">
		<div id="page-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h1 class="page-header">
							<?php echo ucfirst($this->p->t('global', 'uebersicht')); ?> -
							<?php echo $person->vorname.' '.$person->nachname.' ('.$user.')';?>
						</h1>
					</div>
				</div>
				<div>
					<b><?php echo $this->p->t('lehre', 'notendurchschnitt'); ?>:</b>
					<?php echo $courses['overall']['notendurchschnitt'] ?><br>
					<b><?php echo $this->p->t('lehre', 'gewichteternotendurchschnitt'); ?>:</b>
					<?php echo $courses['overall']['notendurchschnittgewichtet'] ?><br>
					<b><?php echo $this->p->t('lehre', 'ects'); ?>:</b>
					<?php echo $courses['overall']['ectssumme_positiv'] ?><br>
					<br>
					<?php
					foreach ($courses['semester'] as $sem => $row_semester)
					{
						$this->load->view('person/gradelist/semester.php', $row_semester);
					}
					?>
				</div>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
