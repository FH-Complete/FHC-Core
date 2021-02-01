<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => $this->p->t('anrechnung', 'anrechnungenPruefen'),
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
			'public/js/lehre/anrechnung/reviewAnrechnung.js'
		
		)
	)
);
?>

<body>
<div id="page-wrapper">
    <div class="container-fluid">
        <!-- title -->
        <div class="row">
            <div class="col-lg-12 page-header">
                <h3>
					<?php echo $this->p->t('anrechnung', 'anrechnungenPruefen'); ?>
                    <small>| <?php echo $this->p->t('global', 'detailsicht'); ?></small>
                </h3>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-8">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <!-- Antragsdaten -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <span class="text-uppercase"><b><?php echo $this->p->t('anrechnung', 'antrag'); ?></b></span>
                                                <span class="pull-right"><?php echo $this->p->t('anrechnung', 'antragdatum'); ?>: <span
                                                            id="reviewAnrechnung-status"><?php echo !empty($anrechnungData->anrechnung_id) ? $anrechnungData->insertamum : '-' ?></span></span>
                                            </div>
                                            <table class="panel-body table table-bordered table-condensed">
                                                <tbody>
                                                <tr>
                                                    <td><?php echo $this->p->t('person', 'student'); ?></td>
                                                    <td><?php echo $antragData->vorname . ' ' . $antragData->nachname; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $this->p->t('person', 'personenkennzeichen'); ?></td>
                                                    <td><?php echo $antragData->matrikelnr ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $this->p->t('lehre', 'studiensemester'); ?></td>
                                                    <td><?php echo $antragData->studiensemester_kurzbz ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $this->p->t('lehre', 'studiengang'); ?></td>
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
                                                    <td><?php echo $this->p->t('lehre', 'lektorInnen'); ?></td>
                                                    <td>
														<?php $len = count($antragData->lektoren) - 1 ?>
														<?php foreach ($antragData->lektoren as $key => $lektor): ?>
															<?php echo $lektor->vorname . ' ' . $lektor->nachname;
															echo $key === $len ? '' : ', ' ?>
														<?php endforeach; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $this->p->t('anrechnung', 'herkunftDerKenntnisse'); ?></td>
                                                    <td><?php echo $anrechnungData->anmerkung ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $this->p->t('anrechnung', 'nachweisdokumente'); ?></td>
                                                    <td>
                                                        <a href="<?php echo current_url() . '/download?dms_id=' . $anrechnungData->dms_id; ?>"
                                                           target="_blank"><?php echo $anrechnungData->dokumentname ?></a>
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
                                        <div class="well">


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="alert text-center">
                    Status:
                    <b><span class="text-uppercase" id="reviewAnrechnung-status_kurzbz"
                             data-status_kurzbz="<?php echo $anrechnungData->status_kurzbz ?>">
                            <?php echo $anrechnungData->status; ?>
                        </span></b>
                </div>
                <br>
				<?php $this->load->view('lehre/anrechnung/reviewAnrechnungInfo'); ?>
            </div>
        </div>
        
    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
