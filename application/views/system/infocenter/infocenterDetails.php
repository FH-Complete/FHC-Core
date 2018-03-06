<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'InfocenterDetails',
			'jquery' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'jqueryui' => true,
			'tablesorter' => true,
			'tinymce' => true,
			'sbadmintemplate' => true,
			'addons' => true,
			'customCSSs' =>
				array(
					'skin/admintemplate.css',
					'skin/tablesort_bootstrap.css'
				),
			'customJSs' =>
				array(
					'include/js/bootstrapper.js',
					'include/js/tablesort/tablesort.js',
					'include/js/infocenter/infocenterDetails.js')
				)
	);
?>
<body>
<div id="wrapper">
	<?php
	echo $this->widgetlib->widget(
		'NavigationWidget',
		array(
			'navigationHeader' => $navigationHeaderArray,
			'navigationMenu' => $navigationMenuArray
		)
	);
	?>
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
						wird bearbeitet von:
						<?php
						if (isset($lockedby)):
							echo $lockedby;
							?>
							&nbsp;&nbsp;
							<a href="../unlockPerson/<?php echo $stammdaten->person_id; ?>"><i
										class="fa fa-sign-out"></i>&nbsp;Freigeben</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<br/>
			<section>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading text-center"><h4>Stammdaten</h4></div>
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
							<div class="panel-heading text-center"><h4>Dokumentenpr&uuml;fung</h4></div>
							<div class="panel-body">
								<?php $this->load->view('system/infocenter/dokpruefung.php'); ?>
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
								<h4>ZGV-Pr&uuml;fung</h4>
							</div>
							<div class="panel-body">
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
								<h4 class="text-center">Nachrichten</h4>
							</div>
							<div class="panel-body">
								<div class="row">
									<?php
									$this->load->view('system/messageList.php', $messages);
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
								<h4 class="text-center">Notizen &amp; Aktivit&auml;ten</h4>
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
									<div class="col-lg-6" id="logs">
										<?php $this->load->view('system/infocenter/logs.php'); ?>
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
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
