<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => $this->p->t('anrechnung', 'antragStellen'),
		'jquery' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'ajaxlib' => true,
		'dialoglib' => true,
		'phrases' => array(
			'global' => array(
				'anerkennungNachgewiesenerKenntnisse',
				'antragStellen'
			),
			'ui' => array(
				'hilfeZuDieserSeite',
				'hochladen'
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
        <!-- title -->
        <div class="row">
            <div class="col-lg-12 page-header">
                <h3>
					<?php echo $this->p->t('anrechnung', 'anerkennungNachgewiesenerKenntnisse'); ?>
                    <small>| <?php echo $this->p->t('anrechnung', 'antragStellen'); ?></small>
                </h3>
            </div>
        </div>

		<?php echo form_open_multipart(current_url() . '/apply',
			['id' => 'requestAnrechnung-form'],
			['lv_id' => $antragData->lv_id, 'studiensemester' => $antragData->studiensemester_kurzbz]
		); ?>
        <div class="row">
            <div class="col-xs-8">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <!-- Antragsdaten, Dokument Upload, Notiz-->
                        <div class="row">
                            <div class="col-lg-12">
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
                                                            id="requestAnrechnung-status"><?php echo !empty($anrechnungData->anrechnung_id) ? $anrechnungData->insertamum : '-' ?></span></span>
                                            </div>
                                            <table class="panel-body table table-bordered table-condensed">
                                                <tbody>
                                                <tr>
                                                    <td><?php echo ucfirst($this->p->t('person', 'student')); ?></td>
                                                    <td><?php echo $antragData->vorname . ' ' . $antragData->nachname; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $this->p->t('person', 'personenkennzeichen'); ?></td>
                                                    <td><?php echo $antragData->matrikelnr ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo ucfirst($this->p->t('lehre', 'studiensemester')); ?></td>
                                                    <td><?php echo $antragData->studiensemester_kurzbz ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo ucfirst($this->p->t('lehre', 'studiengang')); ?></td>
                                                    <td><?php echo $antragData->stg_bezeichnung ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $this->p->t('lehre', 'lehrveranstaltung'); ?></td>
                                                    <td><?php echo $antragData->lv_bezeichnung ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $this->p->t('lehre', 'ects'); ?></td>
                                                    <td><?php echo $antragData->ects ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo ucfirst($this->p->t('lehre', 'lektor')); ?></td>
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
														<?php echo $anrechnungData->begruendung_id == '1' ? 'checked' : ''; ?>
														<?php echo $disabled; ?>>
													<?php echo $this->p->t('anrechnung', 'antragStellenWegenZeugnis'); ?>&emsp;
                                                    <span id="requestAnrechnung-anrechnungGrundZeugnisTooltip" data-toggle="tooltip" data-placement="right"
                                                          title="<?php echo $this->p->t('anrechnung', 'anrechnungGrundZeugnisTooltipText'); ?>">
                                                    <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>
                                                </span>
                                                </label>
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="radio" name="begruendung" value="4" required
														<?php echo $anrechnungData->begruendung_id == '4' ? 'checked' : ''; ?>
														<?php echo $disabled; ?>>
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
                                                           required <?php echo $disabled; ?>>
                                                </div>
                                                <span id="requestAnrechnung-uploadTooltip" data-toggle="tooltip" data-placement="right"
                                                   title="<?php echo $this->p->t('ui', 'uploadTooltipText'); ?>">
                                                    <i class="fa fa-lg fa-question-circle-o" aria-hidden="true"></i>
                                                </span>
												<?php if (!empty($anrechnungData->dms_id)): ?>
                                                    <a class="pull-right"
                                                       href="<?php echo current_url() . '/download?dms_id=' . $anrechnungData->dms_id; ?>"
                                                       target="_blank"><?php echo htmlentities($anrechnungData->dokumentname) ?></a>
												<?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Notiz -->
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
                                                        <textarea class="form-control" name="anmerkung" rows="2"
                                                                  required <?php echo $disabled; ?>><?php echo $anrechnungData->anmerkung; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Submit button 'Anrechnung beantragen'-->
                                <div class="pull-right">
                                    <input type="submit" id="requestAnrechnung-submit" class="btn btn-primary"
                                           value="<?php echo $this->p->t('anrechnung', 'anrechnungBeantragen'); ?>" <?php echo $disabled; ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="alert text-center">
                    Status:
                    <b><span class="text-uppercase" id="requestAnrechnung-status_kurzbz"
                             data-status_kurzbz="<?php echo $anrechnungData->status_kurzbz ?>">
                            <?php echo $anrechnungData->status; ?>
                        </span></b>
                </div>
				<?php if ($is_expired): ?>
                    <div class="alert alert-warning">
						<?php echo $this->p->t('global', 'bearbeitungGesperrt'); ?>
						<?php echo $is_expired && empty($antragData->anrechnung_id) ? ': ' . $this->p->t('anrechnung', 'deadlineUeberschritten') : ''; ?>
                    </div>
				<?php endif; ?>
                <br>
				<?php $this->load->view('lehre/anrechnung/requestAnrechnungImportant'); ?>
            </div>
        </div>
		<?php echo form_close(); ?>
    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
