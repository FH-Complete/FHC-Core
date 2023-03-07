<?php
$includesArray = array(
    'title' => $this->p->t('anrechnung', 'neueAnrechnung'),
    'jquery3' => true,
    'jqueryui1' => true,
    'bootstrap5' => true,
    'fontawesome6' => true,
    'ajaxlib' => true,
    'dialoglib' => true,
    'tablesorter2' => true,
    'tabulator4' => true,
    'tablewidget' => true,
    'phrases' => array(
        'anrechnung' => array(
            'anrechnungenVerwalten',
            'anrechnungszeitraumFestlegen',
            'anrechnungszeitraumHinzufuegen',
            'anrechnungszeitraumSpeichern',
            'anrechnungszeitraumStart',
            'anrechnungszeitraumEnde'
        ),
        'ui' => array(
            'aktion',
            'geloescht',
            'gespeichert',
            'frageSicherLoeschen'
        ),
        'lehre' => array('studiensemester')
    ),
    'customJSs' => array(
        'public/js/bootstrapper.js',
        'public/js/lehre/anrechnung/adminAnrechnung.js'
    ),
    'customCSSs' => array(
        'public/css/sbadmin2/tablesort_bootstrap.css'
    )
);

$this->load->view('templates/FHC-Header', $includesArray);
?>


<div id="main">
    <div class="content">

        <!--Titel-->
        <div class="page-header">
            <h3><?php echo $this->p->t('anrechnung', 'anrechnungenVerwalten'); ?></h3>
        </div>

        <!--Untertitel-->
        <h4 class="mt-5"><?php echo $this->p->t('anrechnung', 'anrechnungszeitraumFestlegen'); ?></h4>

        <div class="col-sm-4 mt-4">
            <button class="btn btn-primary azrOpenModal" data-bs-toggle="modal" data-bs-target="#azrModal" value="insert">
                <i class="fa fa-plus me-1"></i><?php echo $this->p->t('anrechnung', 'anrechnungszeitraumHinzufuegen'); ?>
            </button>
        </div>

        <!-- Tabelle -->
        <div class="row">
            <div class="col-6">
                <?php $this->load->view('lehre/anrechnung/adminAnrechnungData.php'); ?>
            </div>
        </div>

        <!-- Modal (für insert und update von Anrechnungszeitraum)-->
        <div class="modal hide" id="azrModal" tabindex="-1" aria-labelledby="azrModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="azrModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body row g-1">
                        <input type="hidden" id="anrechnungszeitraum_id" value="">
                        <input type="hidden" id="defaultStudiensemester_kurzbz" value="<?php echo $studiensemester_kurzbz ?>">
                        <div class="col-sm-4">
                            <label for="studiensemester" class="small">Studiensemester</label>
                            <?php
                            echo $this->widgetlib->widget(
                                'Studiensemester_widget',
                                array(
                                    DropdownWidget::SELECTED_ELEMENT => $studiensemester_kurzbz
                                ),
                                array(
                                    'name' => 'studiensemester',
                                    'id' => 'studiensemester'
                                )
                            );
                            ?>
                        </div>
                        <div class="col-sm-4">
                            <label for="azrStart" class="small">Anr.-Zeitraum Start</label>
                            <input type="date" id="azrStart" value="" class="form-control" required>
                        </div>
                        <div class="col-sm-4">
                            <label for="azrEnde" class="small">Anr.-Zeitraum Ende</label>
                            <input type="date" id="azrEnde" value="" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="azrInsertBtn" class="btn btn-primary" value=""><?php echo $this->p->t('ui', 'speichern'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

