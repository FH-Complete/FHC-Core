<?php
$this->load->view(
    'templates/FHC-Header',
    array(
        'title' => 'Lehrauftrag',
        'jquery' => true,
        'jqueryui' => true,
        'jquerycheckboxes' => true,
        'bootstrap' => true,
        'fontawesome' => true,
        'sbadmintemplate' => true,
        'tabulator' => true,
        'momentjs' => true,
        'ajaxlib' => true,
        'dialoglib' => true,
        'filterwidget' => true,
        'navigationwidget' => true,
        'phrases' => array(
            'global' => array('lehrauftraege'),
        ),
        'customCSSs' => 'public/css/sbadmin2/tablesort_bootstrap.css',
        'customJSs' => array('public/js/bootstrapper.js')
    )
);

?>

<body>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">
                    <?php echo ucfirst($this->p->t('global', 'lehrauftraege')); ?>
                </h3>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <form id="formLehrauftrag" class="form-inline" action="" method="get">
                    <div class="form-group">
                        <?php
                        echo $this->widgetlib->widget(
                            'Studiensemester_widget',
                            array(
                                DropdownWidget::SELECTED_ELEMENT => $studiensemester_selected
                            ),
                            array(
                                'name' => 'studiensemester',
                                'id' => 'studiensemester'
                            )
                        );
                        ?>
                    </div>
                    <div class="form-group">
                        <?php
                        echo $this->widgetlib->widget(
                            'Organisationseinheit_widget',
                            array(
                                DropdownWidget::SELECTED_ELEMENT => $organisationseinheit_selected,
                                'organisationseinheit' => $organisationseinheit
                            ),
                            array(
                                'name' => 'organisationseinheit',
                                'id' => 'organisationseinheit'
                            )
                        );
                        ?>
                    </div>
                    <button type="submit" name="submit" value="anzeigen" class="btn btn-default form-group">Anzeigen</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <?php $this->load->view('lehre/lehrauftrag/approveLehrauftragData.php'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <button id="approve-lehrauftraege" class="btn btn-primary pull-right">Lehrauftrag erteilen</button>
                <button id="select-all" class="btn btn-default">Alle auswählen</button>
                <button id="deselect-all" class="btn btn-default">Alle abwählen</button>
                <button id="show-all" class="btn btn-default">Alle anzeigen</button>
                <button id="show-ordered" class="btn btn-default">Nur bestellte anzeigen</button>
                <button id="show-approved" class="btn btn-default">Nur erteilte anzeigen</button>
            </div>
        </div>
    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>


<script type="text/javascript">
    // -----------------------------------------------------------------------------------------------------------------
    // Mutators - setter methods to manipulate table data when entering the tabulator
    // -----------------------------------------------------------------------------------------------------------------

    // Converts string date postgre style to string DD.MM.YYYY.
    // This will allow correct filtering.
    var mut_formatStringDate = function(value, data, type, params, component) {
        if (value != null)
        {
            var d = new Date(value);
            return d.getDate()  + "." + ("0"+(d.getMonth()+1)).slice(-2) + "." + d.getFullYear();
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Header filter
    // -----------------------------------------------------------------------------------------------------------------

    // Casts string formatted float values to float when using the filter.
    // This will allow correct filtering.
    function hf_compareWithFloat(headerValue, rowValue, rowData, filterParams){
        //headerValue - the value of the header filter element
        //rowValue - the value of the column in this row
        //rowData - the data for the row being filtered
        //filterParams - params object passed to the headerFilterFuncParams property

        return parseFloat(headerValue) <= parseFloat(rowValue); //must return a boolean, true if it passes the filter.
    }

$(function() {

    // Select all (filtered) rows, where status bestellt has a value and status erteilt has no value.
    $("#select-all").click(function(){
        $('#filterTabulator').tabulator('getRows')
            .filter(function(row){
                return row.getData().bestellt != null && row.getData().erteilt == null;
            })
            .forEach((row => row.select()));
    });

    // Deselect all (filtered) rows
    $("#deselect-all").click(function(){
        $('#filterTabulator').tabulator('deselectRow');
    });

    // Show all rows
    $("#show-all").click(function(){
        $('#filterTabulator').tabulator('clearFilter');
    });

    // Show only rows with bestellte lehrauftraege
    $("#show-ordered").click(function(){
        $('#filterTabulator').tabulator('setFilter', [
            {field: 'bestellt', type: '!=', value: null},   // filter by bestellt must be set
            {field: 'erteilt', type: '=', value: null}      // and erteilt has no value
            ]
        );
    });

    // Show only rows with erteilte lehrauftraege
    $("#show-approved").click(function(){
        $('#filterTabulator').tabulator('setFilter', [
                {field: 'bestellt', type: '!=', value: null},   // filter by bestellt must be set
                {field: 'erteilt', type: '!=', value: null}      // and erteilt has no value
            ]
        );
    });

    // Approve Lehrauftraege
    $("#approve-lehrauftraege").click(function(){

        selected_data = $('#filterTabulator').tabulator('getSelectedData');

        if (selected_data.length == 0)
        {
            FHC_DialogLib.alertInfo('Bitte wählen Sie erst zumindest einen Lehrauftrag');
            return;
        }

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/approveLehrauftrag",
            selected_data,
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (data.retval != null)
                    {
                        // Update status 'Erteilt'
                        $('#filterTabulator').tabulator('updateData', data.retval);
                    }

                    FHC_DialogLib.alertSuccess(data.retval.length + " Lehraufträge wurden erteilt.");
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError("Systemfehler<br>Bitte kontaktieren Sie Ihren Administrator.");
                }
            }
        );

     });
});
</script>
