<?php
const HERKUNFT_DER_KENNTNISSE_MAX_LENGTH = 125;

$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => $this->p->t('anrechnung', 'antragStellen'),
		'jquery' => true,
		'jqueryui' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'ajaxlib' => true,
		'dialoglib' => true,
		'phrases' => array(
			'global' => array(
				'anerkennungNachgewiesenerKenntnisse',
				'antragStellen',
                'antragWurdeGestellt',
                'antragBereitsGestellt',
				'bearbeitungGesperrt'
			),
			'ui' => array(
				'hilfeZuDieserSeite',
				'hochladen',
                'inBearbeitung',
                'neu',
				'maxZeichen',
                'errorBestaetigungFehlt',
				'systemfehler'
			),
			'anrechnung' => array(
				'deadlineUeberschritten',
				'benotungDerLV'
			),
			'person' => array(
				'student',
				'personenkennzeichen'
			),
			'lehre' => array(
				'studiensemester',
				'studiengang',
				'lehrveranstaltung',
				'ects',
				'lektor',
			)
		),
		'customJSs' => array(
			'public/js/bootstrapper.js',
			'public/js/lehre/anrechnung/requestAnrechnung.js'

		)
	)
);
?>

<style>
    .tooltip-inner {
        width:300px;
    }
</style>

<body>
<div id="page-wrapper">
    <div class="container-fluid">
        <!-- header -->
        <div class="row">
            <div class="col-lg-12 page-header">
                <h3>
					<?php echo $this->p->t('anrechnung', 'anerkennungNachgewiesenerKenntnisse'); ?>
                    <small>| <?php echo $this->p->t('anrechnung', 'antragStellen'); ?></small>
                </h3>
            </div>
        </div>
		<!-- end header-->
		
        <div class="row">
            <div class="col-xs-8">
				<!-- Antragsdaten, Dokument Upload, Notiz-->
				<div class="row">
					<div class="col-lg-12">
						<form id="requestAnrechnung-form">
							<input type="hidden" name="lv_id" value="<?php echo $antragData->lv_id ?>">
							<input type="hidden" name="studiensemester" value="<?php echo $antragData->studiensemester_kurzbz ?>">
							<!-- Antragsdaten -->
							<div class="row">
								<div class="col-lg-12">
									<div class="panel panel-default">
										<div class="panel-heading">
											<span class="text-uppercase"><b><?php echo $this->p->t('anrechnung', 'antrag'); ?></b></span>&emsp;
											<span class="requestAnrechnung-anrechnungInfoTooltip" data-toggle="tooltip" data-placement="right"
												  title="<?php echo $this->p->t('anrechnung', 'anrechnungInfoTooltipText'); ?>">
												<i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>
											</span>
											<span class="pull-right"><?php echo $this->p->t('anrechnung', 'antragdatum'); ?>: <span
														id="requestAnrechnung-antragdatum"><?php echo !empty($anrechnungData->anrechnung_id) ? $anrechnungData->insertamum : '-' ?></span></span>
										</div>
										<table class="panel-body table table-bordered table-condensed">
											<tbody>
											<tr>
												<th><?php echo ucfirst($this->p->t('person', 'studentIn')); ?></th>
												<td><?php echo $antragData->vorname . ' ' . $antragData->nachname; ?></td>
											</tr>
											<tr>
												<th><?php echo $this->p->t('person', 'personenkennzeichen'); ?></th>
												<td><?php echo $antragData->matrikelnr ?></td>
											</tr>
											<tr>
												<th><?php echo ucfirst($this->p->t('lehre', 'studiensemester')); ?></th>
												<td><?php echo $antragData->studiensemester_kurzbz ?></td>
											</tr>
											<tr>
												<th><?php echo ucfirst($this->p->t('lehre', 'studiengang')); ?></th>
												<td><?php echo $antragData->stg_bezeichnung ?></td>
											</tr>
											<tr>
												<th><?php echo $this->p->t('lehre', 'lehrveranstaltung'); ?></th>
												<td><?php echo $antragData->lv_bezeichnung ?></td>
											</tr>
											<tr>
												<th><?php echo $this->p->t('lehre', 'ects'); ?></th>
												<td><?php echo $antragData->ects ?></td>
											</tr>
											<tr>
												<th><?php echo ucfirst($this->p->t('lehre', 'lektorInnen')); ?></th>
												<td>
													<?php $len = count($antragData->lektoren) - 1 ?>
													<?php foreach ($antragData->lektoren as $key => $lektor): ?>
														<?php echo $lektor->vorname . ' ' . $lektor->nachname;
														echo $key === $len ? '' : ', ' ?>
													<?php endforeach; ?>
												</td>
											</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<!-- Antrag mit Checkboxen -->
							<div class="row">
								<div class="col-lg-12">
									<div class="well" style="border:solid black 3px">
										<span><?php echo $this->p->t('anrechnung', 'antragStellenText'); ?></span>&emsp;
										<div class="checkbox">
											<label>
												<input type="radio" name="begruendung" value="1" required
													<?php echo $anrechnungData->begruendung_id == '1' ? 'checked' : ''; ?> />
												<?php echo $this->p->t('anrechnung', 'antragStellenWegenZeugnis'); ?>&emsp;
												<span id="requestAnrechnung-anrechnungGrundZeugnisTooltip" data-toggle="tooltip" data-placement="right"
													  title="<?php echo $this->p->t('anrechnung', 'anrechnungGrundZeugnisTooltipText'); ?>" />
												<i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>
											</span>
											</label>
										</div>
										<div class="checkbox">
											<label>
												<input type="radio" name="begruendung" value="4" required
													<?php echo $anrechnungData->begruendung_id == '4' ? 'checked' : ''; ?>>
												<?php echo $this->p->t('anrechnung', 'antragStellenWegenPraxis'); ?>&emsp;
												<span id="requestAnrechnung-anrechnungGrundBerufTooltip" data-toggle="tooltip" data-placement="right"
													  title="<?php echo $this->p->t('anrechnung', 'anrechnungGrundBerufTooltipText'); ?>">
												<i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>
											</span>
											</label>
										</div>
									</div>
								</div>
							</div>
							<!-- Dokument Upload-->
							<div class="row">
								<div class="col-lg-12">
									<div class="panel panel-default">
										<div class="panel-heading">
											<b><?php echo $this->p->t('anrechnung', 'nachweisdokumente'); ?></b>&emsp;
											<span class="requestAnrechnung-anrechnungInfoTooltip" data-toggle="tooltip" data-placement="right"
												  title="<?php echo $this->p->t('anrechnung', 'anrechnungGrundAllgemeinTooltipText'); ?>">
												<i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>
											</span>
										</div>
										<div class="form-inline panel-body">
											<div class="form-group">
												<input type="file" id="requestAnrechnung-uploadfile"
													   name="uploadfile" accept=".pdf" size="50"
													   required>
											</div>
											<span id="requestAnrechnung-uploadTooltip" data-toggle="tooltip" data-placement="right"
											   title="<?php echo $this->p->t('ui', 'uploadTooltipText'); ?>">
												<i class="fa fa-lg fa-question-circle-o" aria-hidden="true"></i>
											</span>
											<a class="pull-right <?php echo !empty($anrechnungData->dms_id) ? '' : 'hidden' ?>"
											   id="requestAnrechnung-downloadDocLink"
											   href="<?php echo current_url() . '/download?dms_id=' . $anrechnungData->dms_id; ?>"
											   target="_blank"><?php echo htmlentities($anrechnungData->dokumentname) ?>
											</a>
										</div>
									</div>
								</div>
							</div>
							<!-- Herkunft der Kenntnisse -->
							<div class="row">
								<div class="col-lg-12">
									<div class="row">
										<div class="col-lg-12">
											<div class="panel panel-default">
												<div class="panel-heading">
													<b><?php echo $this->p->t('anrechnung', 'herkunftDerKenntnisse'); ?></b>&emsp;
													<span class="requestAnrechnung-anrechnungInfoTooltip" data-toggle="tooltip" data-placement="right"
														  title="<?php echo $this->p->t('anrechnung', 'anrechnungInfoTooltipText'); ?>">
														<i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>
													</span>
												</div>
												<div class="panel-body">
													<textarea class="form-control" name="anmerkung" rows="1" id="requestAnrechnung-herkunftDerKenntnisse"
															  maxlength="<?php echo HERKUNFT_DER_KENNTNISSE_MAX_LENGTH ?>" required><?php echo $anrechnungData->anmerkung; ?></textarea>
													<small><span class="text-muted pull-right"><?php echo $this->p->t('ui', 'maxZeichen'); ?> :<span id="requestAnrechnung-herkunftDerKenntnisse-charCounter"><?php echo HERKUNFT_DER_KENNTNISSE_MAX_LENGTH ?></span></span></small>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- Bestaetigung-->
							<div class="row">
								<div class="col-lg-12">
									<div class="well" style="border:solid black 3px">
										<div class="checkbox">
											<label>
												<input type="checkbox" name="bestaetigung" required>
												<b><?php echo $this->p->t('anrechnung', 'bestaetigungstext'); ?></b>
											</label>
										</div>
									</div>
								</div>
							</div>
							<!-- Button 'Anrechnung beantragen'-->
							<div class="pull-right">
								<input type="submit" id="requestAnrechnung-apply-anrechnung" class="btn btn-primary"
									   value="<?php echo $this->p->t('anrechnung', 'anrechnungBeantragen'); ?>">
							</div>
						</form>
					</div>
				</div>
				<br><br><br><br>
            </div>
            <div class="col-xs-4">
				 <!-- Status panel -->
                <div class="alert text-center" id="requestAnrechnung-status">Status: <b><span class="text-uppercase" id="requestAnrechnung-status_kurzbz"
                             data-status_kurzbz="<?php echo $anrechnungData->status_kurzbz ?>">
                            <?php echo $anrechnungData->status; ?>
                        </span></b>
				</div>
				<!-- Sperregrund panel (hidden by default) -->
				<div class="alert alert-danger text-center hidden" id="requestAnrechnung-sperre"
					 data-anrechnung_id="<?php echo empty($anrechnungData->anrechnung_id) ? '' : $anrechnungData->anrechnung_id; ?>"
					 data-expired="<?php echo json_encode($is_expired); ?>"
					 data-blocked="<?php echo json_encode($is_blocked) ?>">
				</div>
                <br>
				<?php $this->load->view('lehre/anrechnung/requestAnrechnungImportant'); ?>
            </div>
        </div>
    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
