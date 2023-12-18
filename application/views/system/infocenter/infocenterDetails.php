<?php
	$includesArray = array(
		'title' => 'InfocenterDetails',
		'jquery3' => true,
		'bootstrap3' => true,
		'fontawesome4' => true,
		'jqueryui1' => true,
		'dialoglib' => true,
		'ajaxlib' => true,
		'tablesorter2' => true,
		'tinymce5' => true,
		'sbadmintemplate3' => true,
		'addons' => true,
		'navigationwidget' => true,
		'udfs' => true,
		'widgets' => true,
		'customCSSs' => array(
			'public/css/sbadmin2/admintemplate.css',
			'public/css/sbadmin2/tablesort_bootstrap.css',
			'public/css/infocenter/infocenterDetails.css'
		),
		'customJSs' => array(
			'public/js/bootstrapper.js',
			'public/js/tablesort/tablesort.js',
			'public/js/infocenter/messageList.js',
			'public/js/infocenter/infocenterDetails.js',
			'public/js/infocenter/rueckstellung.js',
			'public/js/infocenter/zgvUeberpruefung.js',
			'public/js/infocenter/docUeberpruefung.js',
			'public/js/infocenter/stammdaten.js'
		),
		'phrases' => array(
			'infocenter',
			'ui',
			'global'
		)
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>
	<div id="wrapper">
	
		<?php echo $this->widgetlib->widget('NavigationWidget'); ?>
	
		<div id="page-wrapper">
			<div class="container-fluid">
				<input type="hidden" id="hiddenpersonid" value="<?php echo $stammdaten->person_id ?>">
				<div class="row<?php if ($lockedbyother) echo ' alert-danger' ?>">
					<div class="col-lg-8">
						<h3 class="page-header">
							Infocenter Details: <?php echo $stammdaten->vorname.' '.$stammdaten->nachname ?>
						</h3>
					</div>
					<div class="col-lg-4">
						<div class="headerright text-right">
							<?php
							if (isset($lockedby)):
								echo $this->p->t('global', 'wirdBearbeitetVon').': ';
								echo $lockedby;
								if (in_array($origin_page, array('index', 'abgewiesen'))):
									$unlockpath = 'unlockPerson/'.$stammdaten->person_id;
									$unlockpath .= '?fhc_controller_id='.$fhc_controller_id;
									$unlockpath .= '&filter_id='.$prev_filter_id;
									$unlockpath .= '&origin_page='.$origin_page;
							?>
									&nbsp;&nbsp;
									<a href="<?php echo $unlockpath; ?>">
										<i class="fa fa-sign-out"></i>&nbsp;<?php echo ucfirst($this->p->t('ui', 'freigeben')) ?>
									</a>
								<?php endif; ?>
							<?php else: ?>
								&nbsp;
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php if (!is_null($duplicated)): ?>
					<div class="row alert-warning">
						<h3 class="header col-lg-12">
							<?php echo $this->p->t('global', 'bewerberVorhanden') . ':'; ?>
						</h3>
						<div class="text-left col-lg-12">
							<?php
							foreach ($duplicated as $duplicate)
							{
								echo  'Person ID: ' . $duplicate->person_id . '<br />';
							}
							?>
						</div>
	
					</div>
				<?php endif; ?>
				<br/>
				<section>
					<div class="row">
						<div class="col-lg-12">
							<div class="panel panel-primary">
								<div class="panel-heading text-center">
									<h4><?php echo ucfirst($this->p->t('global', 'stammdaten')) ?></h4>
								</div>
								<div class="panel-body">
									<?php $this->load->view('system/infocenter/stammdaten.php'); ?>
									<?php $this->load->view('system/infocenter/anmerkungenZurBewerbung.php'); ?>
								</div> <!-- ./panel-body -->
							</div> <!-- ./panel -->
						</div> <!-- ./main column -->
					</div> <!-- ./main row -->
				</section>
				<section>
					<div class="row">
						<div class="col-lg-12">
							<div class="panel panel-primary">
								<a name="DokPruef"></a><!-- anchor for jumping to the section -->
								<div class="panel-heading text-center">
									<h4>
										<?php echo  ucfirst($this->p->t('infocenter', 'dokumentenpruefung')) ?>
									</h4>
								</div>
								<div class="panel-body">
									<?php $this->load->view('system/infocenter/dokpruefung.php'); ?>
									<div id="nachzureichendeDoks">
										<?php $this->load->view('system/infocenter/dokNachzureichend.php'); ?>
									</div>
								</div> <!-- ./panel-body -->
							</div> <!-- ./panel -->
						</div> <!-- ./column -->
					</div> <!-- ./row -->
				</section>
				<section>
					<div class="row">
						<div class="col-md-12">
							<div class="panel panel-primary">
								<div class="panel-heading text-center">
									<a name="ZgvPruef"></a>
									<h4>
										<?php echo $this->p->t('infocenter', 'zgv').' - '.ucfirst($this->p->t('lehre', 'pruefung'))?>
									</h4>
								</div>
								<div class="panel-body" id="zgvpruefungen">
									<?php $this->load->view('system/infocenter/zgvpruefungen.php'); ?><!-- /.panel-group -->
								</div><!-- /.main panel body -->
							</div> <!-- /.main panel-->
						</div> <!-- /.column freigabe-->
					</div> <!-- /.row freigabe-->
				</section>
				<section>
					<div class="row">
						<div class="col-lg-12">
							<div class="panel panel-primary">
								<div class="panel-heading text-center">
									<a name="Nachrichten"></a>
									<h4 class="text-center">
										<?php echo ucfirst($this->p->t('global', 'nachrichten')) ?>
									</h4>
								</div>
								<div class="panel-body">
									<div class="row" id="messagelist">
										<?php
										$this->load->view('system/infocenter/messageList.php', $messages);
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
				<section>
					<div class="row">
						<div class="col-lg-12">
							<div class="panel panel-primary">
								<div class="panel-heading text-center">
									<a name="NotizAkt"></a>
									<h4 class="text-center">
										<?php echo ucfirst($this->p->t('global', 'notizen')).' & '.ucfirst($this->p->t('global', 'aktivitaeten')) ?>
									</h4>
								</div>
								<div class="panel-body">
									<div class="row">
										<div class="col-lg-6">
											<div id="addnotiz">
												<?php $this->load->view('system/infocenter/addNotiz.php'); ?>
											</div>
											<div id="notizen">
												<?php $this->load->view('system/infocenter/notizen.php'); ?>
											</div>
										</div>
										<div class="col-lg-6">
											<div id="postponing"></div>
											<div id="logs">
											<?php $this->load->view('system/infocenter/logs.php'); ?>
											</div>
										</div> <!-- ./column -->
									</div> <!-- ./row -->
								</div> <!-- ./panel-body -->
							</div> <!-- ./panel -->
						</div> <!-- ./main column -->
					</div> <!-- ./main row -->
				</section>
			</div> <!-- ./container-fluid-->
		</div> <!-- ./page-wrapper-->
	</div> <!-- ./wrapper -->
	<button id="scrollToTop" title="Go to top"><i class="fa fa-chevron-up"></i></button>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

