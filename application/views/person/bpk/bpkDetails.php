<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'bPK Details',
			'jquery3' => true,
			'bootstrap3' => true,
			'fontawesome4' => true,
			'jqueryui1' => true,
			'ajaxlib' => true,
			'tablesorter2' => true,
			'tinymce4' => true,
			'sbadmintemplate3' => true,
			'addons' => true,
			'navigationwidget' => true,
			'customCSSs' => array(
				'public/css/sbadmin2/admintemplate.css',
				'public/css/sbadmin2/tablesort_bootstrap.css',
				'public/css/infocenter/infocenterDetails.css'
			),
			'customJSs' => array(
				'public/js/bootstrapper.js',
				'public/js/tablesort/tablesort.js'
			),
			'phrases' => array(
				'ui' => array(
					'gespeichert',
					'fehlerBeimSpeichern'
				),
				'global' => array(
					'bis',
					'zeilen'
				)
			)
		)
	);
?>
<body>
<div id="wrapper">

	<?php echo $this->widgetlib->widget('NavigationWidget'); ?>

	<div id="page-wrapper">
		<div class="container-fluid">
			<input type="hidden" id="hiddenpersonid" value="<?php echo $stammdaten->person_id ?>">
			<div class="row">
				<div class="col-lg-8">
					<h3 class="page-header">
						bPK Details: <?php echo $stammdaten->vorname.' '.$stammdaten->nachname ?>
					</h3>
				</div>
			</div>
			<br/>
			<section>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading text-center">
								<h4><?php echo ucfirst($this->p->t('global', 'stammdaten')) ?></h4>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-6 table-responsive">
										<table class="table">
											<?php if (!empty($stammdaten->titelpre)): ?>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'titelpre')) ?></strong></td>
												<td><?php echo $stammdaten->titelpre ?></td>
											</tr>
											<?php endif; ?>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'vorname')) ?></strong></td>
												<td><?php echo $stammdaten->vorname ?></td>
											</tr>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'nachname')) ?></strong></td>
												<td>
													<?php echo $stammdaten->nachname ?></td>
											</tr>
											<?php if (!empty($stammdaten->titelpost)): ?>
												<tr>
													<td><strong><?php echo  ucfirst($this->p->t('person', 'titelpost')) ?></strong></td>
													<td><?php echo $stammdaten->titelpost ?></td>
												</tr>
											<?php endif; ?>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'geburtsdatum')) ?></strong></td>
												<td>
													<?php echo date_format(date_create($stammdaten->gebdatum), 'd.m.Y') ?></td>
											</tr>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'svnr')) ?></strong></td>
												<td>
													<?php echo $stammdaten->svnr ?></td>
											</tr>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'ersatzkennzeichen')) ?></strong></td>
												<td>
													<?php echo $stammdaten->ersatzkennzeichen ?></td>
											</tr>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'staatsbuergerschaft')) ?></strong></td>
												<td>
													<?php echo $stammdaten->staatsbuergerschaft ?></td>
											</tr>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'geschlecht')) ?></strong></td>
												<td>
													<?php echo $stammdaten->geschlecht ?></td>
											</tr>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'bpk')) ?></strong></td>
												<td>
													<?php echo $stammdaten->bpk ?></td>
											</tr>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'postleitzahl')) ?></strong></td>
												<td>
													<?php echo $adresse->plz ?></td>
											</tr>
											<tr>
												<td><strong><?php echo  ucfirst($this->p->t('person', 'strasse')) ?></strong></td>
												<td>
													<?php echo $adresse->strasse ?></td>
											</tr>
										</table>
									</div>
									<div class="col-lg-4 table-responsive">
										<form action="<?php echo base_url('soap/datenverbund_client.php?action=pruefeBPK');?>" method="POST" target="_blank">
											<input type="hidden" name="vorname" value="<?php echo $stammdaten->vorname; ?>"/>
											<input type="hidden" name="nachname" value="<?php echo $stammdaten->nachname; ?>"/>
											<input type="hidden" name="geburtsdatum" value="<?php echo mb_str_replace('-', '',$stammdaten->gebdatum); ?>"/>
											<input type="hidden" name="geschlecht" value="<?php echo mb_strtoupper($stammdaten->geschlecht); ?>"/>
											<input type="submit" value="Namenssuche starten" class="btn btn-default"/>
										</form>
										<br><br>
										<form action="<?php echo site_url('person/BPKWartung/saveBPK');?>" method="POST">
											<strong>bPK</strong><input type="text" name="bpk" value="" class="form-control"/>
											<input type="hidden" name="person_id" value="<?php echo $stammdaten->person_id;?>"/>
											<input type="submit" value="Speichern" class="btn btn-default"/>
										</form>
									</div>
							</div> <!-- ./panel-body -->
						</div> <!-- ./panel -->
					</div> <!-- ./main column -->
				</div> <!-- ./main row -->
			</section>
		</div> <!-- ./container-fluid-->
	</div> <!-- ./page-wrapper-->
</div> <!-- ./wrapper -->
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
