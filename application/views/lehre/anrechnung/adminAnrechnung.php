<?php
$includesArray = array(
    'title' => $this->p->t('anrechnung', 'anrechnungenVerwalten'),
    'jquery3' => true,
    'jqueryui1' => true,
    'bootstrap3' => true,
    'fontawesome6' => true,
    'ajaxlib' => true,
    'dialoglib' => true,
    'tabulator4' => true,
    'tablewidget' => true,
    'sbadmintemplate3' => true,
    'navigationwidget' => true,
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
            'frageSicherLoeschen',
            'spaltenEinstellen'
        ),
        'lehre' => array('studiensemester'),
        'table' => array(
            'spaltenEinAusblenden',
            'spaltenEinAusblendenMitKlickOeffnen',
            'spaltenEinAusblendenAufEinstellungenKlicken',
            'spaltenEinAusblendenMitKlickAktivieren',
            'spaltenEinAusblendenMitKlickSchliessen',
            'spaltenbreiteVeraendern',
            'spaltenbreiteVeraendernText',
            'spaltenbreiteVeraendernInfotext',
            'zeilenAuswaehlen',
            'zeilenAuswaehlenEinzeln',
            'zeilenAuswaehlenBereich',
            'zeilenAuswaehlenAlle'
        )
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

<div id="wrapper">

    <?php echo $this->widgetlib->widget('NavigationWidget'); ?>

    <div id="page-wrapper">
        <div class="container-fluid">

            <!--Titel-->
            <div class="page-header">
                <h3><?php echo $this->p->t('anrechnung', 'anrechnungenVerwalten'); ?></h3>
            </div><br>

            <!--Untertitel-->
            <h4><?php echo $this->p->t('anrechnung', 'anrechnungszeitraumFestlegen'); ?></h4><br>

            <div class="row">
                <div class="col-xs-4">
                    <button class="btn btn-primary azrOpenModal" data-toggle="modal" data-target="#azrModal">
                        <i class="fa fa-plus"></i> <?php echo $this->p->t('anrechnung', 'anrechnungszeitraumHinzufuegen'); ?>
                    </button>
                </div>
            </div>

            <!-- Tabelle -->
            <div class="row">
                <div class="col-lg-12">
                    <?php $this->load->view('lehre/anrechnung/adminAnrechnungData.php'); ?>
                </div>
            </div>

            <!-- Modal (für insert und update von Anrechnungszeitraum)-->
            <div class="modal fade" id="azrModal" tabindex="-1" role="dialog" aria-labelledby="azrModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="azrModalLabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="anrechnungszeitraum_id" value="">
                            <input type="hidden" id="defaultStudiensemester_kurzbz" value="<?php echo $studiensemester_kurzbz ?>">
                            <div class="col-xs-4">
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
                            <div class="col-xs-4">
                                <label for="azrStart" class="small">Anr.-Zeitraum Start</label>
                                <input type="date" id="azrStart" value="" class="form-control" required>
                            </div>
                            <div class="col-xs-4">
                                <label for="azrEnde" class="small">Anr.-Zeitraum Ende</label>
                                <input type="date" id="azrEnde" value="" class="form-control" required>
                            </div>
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
</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

