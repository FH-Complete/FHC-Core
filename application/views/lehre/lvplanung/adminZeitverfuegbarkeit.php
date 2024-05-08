<?php
$this->load->view(
    'templates/FHC-Header',
    array(
        'title' => 'Zeitverfuegbarkeit verwalten',
        'jquery3' => true,
        'jqueryui1' => true,
        'bootstrap3' => true,
        'fontawesome4' => true,
        'momentjs2' => true,
        'ajaxlib' => true,
        'tabulator4' => true,
        'tablewidget' => true,
        'navigationwidget' => true,
        'sbadmintemplate3' => true,
        'phrases' => array(
            'global' => array(
                'bis',
                'notiz'
            ),
            'ui' => array(
                'systemfehler',
                'keineDatenVorhanden',
                'von',
                'bitteWaehlen',
                'speichern',
                'loeschen',
                'abbrechen'
            ),
            'lehre' => array(
                'lektor'
            )
        ),
        'widgets' => true,
        'dialoglib' => true,
        'customJSs' => array(
            'public/js/bootstrapper.js',
            'public/js/lehre/lvplanung/zverfueg.js'
        )
    )
);
?>

<body>
<?php echo $this->widgetlib->widget('NavigationWidget'); ?>
<div id="page-wrapper">
    <div class="container-fluid">

        <!-- title -->
        <div class="row">
            <div class="col-lg-12 page-header">
		<h3>Zeitverf&uuml;gbarkeiten verwalten<small>
			  |  Punktuelle Zeitverfügbarkeiten von Lehrenden für die LV-Planung verwalten</small>
		</h3>
            </div>
        </div>

        <!-- form -->
        <div class="row">
            <div class="col-lg-12">
                <form id="form-zeitverfuegbarkeit" class="form-horizontal">
                    <input type="hidden" id="studsemStart" value="<?php echo $studsemStart ?>">
                    <input type="hidden" id="zeitsperre_id" name="zeitsperre_id" value="">

                    <div class="form-group">
                        <label for="mitarbeiter_uid" class="col-sm-1 control-label">LektorIn: </label>
                        <div class="col-sm-3">
                            <select id="mitarbeiter_uid" name="mitarbeiter_uid" class="form-control select-w500" required>
                                <option value="" >
                                    <?php echo $this->p->t('ui', 'bitteWaehlen'); ?>
                                </option>
                                <?php foreach ($lektor_arr as $lektor) : ?>
                                    <option value="<?php echo $lektor->mitarbeiter_uid ?>">
                                        <?php echo $lektor->nachname. ' '. $lektor->vorname ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="zverfueg" class="col-sm-1 control-label">Grund: </label>
                        <div class="col-sm-3">
                            <input type="text" id="zeitsperretyp_kurzbz" value="Zeitverf&uuml;gbarkeit" name="zeitsperretyp_kurzbz" readonly />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bezeichnung" class="col-sm-1 control-label">Notiz: </label>
                        <div class="col-sm-3">
                            <textarea type="" id="bezeichnung" name="bezeichnung" value="" required></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="vondatum" class="col-sm-1 control-label">Von: </label>
                        <div class="col-sm-3">
                            <input type="date" id="vondatum" name="vondatum" class="form-control zverfueg-datepicker" required>
                        </div>
                        <label for="vonstunde" class="col-sm-1 control-label">Stunde (inkl.): </label>
                        <div class="col-sm-3">
                            <select id="vonstunde" name="vonstunde" class="form-control select-w500">
                                <option value="" >*</option>
                                <?php foreach ($stunde_arr as $stunde) : ?>
                                    <option value="<?php echo $stunde->stunde ?>">
                                        <?php echo $stunde->stunde.
                                            ' ['.
                                            (new DateTime($stunde->beginn))->format('H:i'). ' - '.
                                            (new DateTime($stunde->ende))->format('H:i').
                                            ']' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bisdatum" class="col-sm-1 control-label">Bis: </label>
                        <div class="col-sm-3">
                            <input type="date" id="bisdatum" name="bisdatum" class="form-control zverfueg-datepicker" required>
                        </div>
                        <label for="bisstunde" class="col-sm-1 control-label">Stunde (inkl.): </label>
                        <div class="col-sm-3">
                            <select id="bisstunde" name="bisstunde" class="form-control select-w500">
                                <option value="" >*</option>
                                <?php foreach ($stunde_arr as $stunde) : ?>
                                    <option value="<?php echo $stunde->stunde ?>">
                                        <?php echo $stunde->stunde.
                                            ' ['.
                                            (new DateTime($stunde->beginn))->format('H:i'). ' - '.
                                            (new DateTime($stunde->ende))->format('H:i').
                                            ']' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-8">
                        <button type="submit" id="btn-save" class="btn btn-primary btn-w200 pull-right">
                            <?php echo ucfirst($this->p->t('ui', 'speichern')); ?>
                        </button>
                        <button type="button" id="btn-delete" class="btn btn-danger btn-w200 btn-mr5 pull-right" disabled
                                data-toggle="tooltip" data-placement="right"
                                title="Zum Löschen LektorIn aus Tabelle wählen">
                            <?php echo ucfirst($this->p->t('ui', 'loeschen')); ?>
                        </button>
                        <button type="reset" id="btn-break" class="btn btn-default btn-w200 btn-mr5 pull-right">
                            <?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- data table -->
        <div class="row">
            <div class="col-lg-12">
                <?php $this->load->view('lehre/lvplanung/adminZeitverfuegbarkeitData.php'); ?>
            </div>
        </div>

    </div><!-- end container -->
</div><!-- end page-wrapper -->
<br>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
