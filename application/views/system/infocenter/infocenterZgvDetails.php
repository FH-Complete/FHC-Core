<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'InfocenterZgvDetails',
			'jquery' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'jqueryui' => true,
			'dialoglib' => true,
			'ajaxlib' => true,
			'tablesorter' => true,
			'tinymce' => true,
			'sbadmintemplate' => true,
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
				'public/js/infocenter/zgvUeberpruefung.js'
			),
			'phrases' => array(
				'infocenter' => array(
					'notizHinzufuegen',
					'notizAendern',
					'nichtsZumEntfernen',
					'fehlerBeimEntfernen',
					'zgvInPruefung',
					'zgvErfuellt',
					'zgvNichtErfuellt',
					'zgvErfuelltPruefung'
				),
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
			<input type="hidden" id="studiengangtyp" value="<?php echo $studiengang_typ ?>">
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
							if ($origin_page == 'index'):
								$unlockpath = 'unlockPerson/'.$stammdaten->person_id;
								$unlockpath .= '?fhc_controller_id='.$fhc_controller_id;
								$unlockpath .= '&filter_id='.$prev_filter_id;
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
			<br/>
			<section>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading text-center">
								<h4><?php echo ucfirst($this->p->t('global', 'stammdaten')) ?></h4>
							</div>
							<div class="panel-body">
								<?php
								$this->load->view('system/infocenter/stammdaten.php'); ?>
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
								<?php $this->load->view('system/infocenter/dokpruefung.php', array('formalReadonly' => true)); ?>
								<div id="nachzureichendeDoks">
									<?php $this->load->view('system/infocenter/dokNachzureichend.php'); ?>
								</div>
							</div> <!-- ./panel-body -->

							<div class="panel-body zgvBearbeitungButtons" id="zgvBearbeitungButtons_<?php echo $prestudent_id ?>">
								<button type="button" class="btn btn-default zgvAkzeptieren" id="zgvAkzeptieren_<?php echo $prestudent_id ?>">
									<?php echo $this->p->t('infocenter', 'zgvErfuellt') ?>
								</button>
								<button type="button" class="btn btn-default zgvAblehnen" id="zgvAblehnen_<?php echo $prestudent_id ?>">
									<?php echo $this->p->t('infocenter', 'zgvNichtErfuellt') ?>
								</button>
								<?php
								if ($studiengang_typ === 'm') :
								?>
									<button type="button" class="btn btn-default zgvAkzeptierenPruefung" id="zgvAkzeptierenPruefung_<?php echo $prestudent_id ?>">
										<?php echo $this->p->t('infocenter', 'zgvErfuelltPruefung') ?>
									</button>
								<?php
								endif;
								?>
								<span class="zgvStatusText" id="zgvStatusText_<?php echo $prestudent_id ?>" data-info="need">
								</span>
							</div>
						</div> <!-- ./panel -->
					</div> <!-- ./column -->
				</div> <!-- ./row -->
			</section>
			<section>
				<div class="modal fade notizModal" id="notizModal_<?php echo $prestudent_id ?>" tabindex="-1"
					 role="dialog"
					 aria-labelledby="notizModalLabel"
					 aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close"
										data-dismiss="modal"
										aria-hidden="true">&times;
								</button>
								<h4 class="modal-title"
									id="notizModalLabel">
									<?php echo $this->p->t('infocenter', 'notizHinzufuegen') ?>
									<span id="notizModalStgr_<?php echo $prestudent_id ?>"></span>
								</h4>
							</div>
							<div class="modal-body">
								<input type="hidden" id="inputStatus_<?php echo $prestudent_id ?>">
								<div class="form-group">
									<label for="inputNotizTitelModal"><?php echo  ucfirst($this->p->t('global', 'titel')) . ':' ?></label>
									<input id="inputNotizTitelModal" required type="text" class="form-control"/>
								</div>
								<div class="form-group">
									<label for="inputNotizTextModal"><?php echo  ucfirst($this->p->t('global', 'text')) . ':' ?></label>
									<textarea id="inputNotizTextModal" required class="form-control" rows="3" cols="32"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button"
										class="btn btn-default"
										data-dismiss="modal"><?php echo  $this->p->t('ui', 'abbrechen') ?>
								</button>
								<button type="button"
										class="btn btn-default saveZgvNotiz" id="saveZgvNotiz_<?php echo $prestudent_id ?>">
									<?php echo $this->p->t('ui', 'speichern') ?>
								</button>
							</div>
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /.modal-fade -->
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
									<?php echo ucfirst($this->p->t('global', 'notizen'))?>
								</h4>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-12">
										<div id="addnotiz">
											<?php $this->load->view('system/infocenter/addNotiz.php'); ?>
										</div>
										<div id="notizen">
											<?php $this->load->view('system/infocenter/notizen.php'); ?>
										</div>
									</div>

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
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
