<?php
// TODO: phrasen anpassen
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
        'customJSs' => array('public/js/bootstrapper.js')
    )
);
?>

<body>
    <?php echo $this->widgetlib->widget('NavigationWidget'); ?>
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
                            'Studiengang_widget',
                            array(
                                DropdownWidget::SELECTED_ELEMENT => $studiengang_selected,
                                'studiengang' => $studiengang
                            ),
                            array(
                                'name' => 'studiengang',
                                'id' => 'studiengang'
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
                <?php $this->load->view('lehre/lehrauftrag/orderLehrauftragData.php'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <button id="order-lehrauftraege" class="btn btn-primary pull-right">Lehrauftrag bestellen</button>
                <button id="select-all" class="btn btn-default">Alle auswählen</button>
                <button id="deselect-all" class="btn btn-default">Alle abwählen</button>
                <button id="show-all" class="btn btn-default" data-toggle="tooltip" data-placement="left" title="Alle anzeigen"><i class='fa fa-users'></i></button>
                <button id="show-new" class="btn btn-default" data-toggle="tooltip" data-placement="left" title="Nur neue anzeigen"><i class='fa fa-user-plus'></i></button>
                <button id="show-ordered" class="btn btn-default" data-toggle="tooltip" data-placement="left" title="Nur bestellte anzeigen"><i class='fa fa-check-square-o'></i></button>
                <button id="show-approved" class="btn btn-default" data-toggle="tooltip" data-placement="left" title="Nur erteilte anzeigen"><i class='fa fa-check-square'></i></button>
                <button id="show-accepted" class="btn btn-default" data-toggle="tooltip" data-placement="left" title="Nur akzeptierte anzeigen"><i class='fa fa-handshake-o'></i></button>
            </div>
        </div>
    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>


<script type="text/javascript">

    const COLOR_LIGHTGREY = "#f5f5f5";

    // -----------------------------------------------------------------------------------------------------------------
    // Mutators - setter methods to manipulate table data when entering the tabulator
    // -----------------------------------------------------------------------------------------------------------------

    // Converts string date postgre style to string DD.MM.YYYY.
    // This will allow correct filtering.
    var mut_formatStringDate = function(value, data, type, params, component) {
        if (value != null)
        {
            var d = new Date(value);
            return ("0" + (d.getDate())).slice(-2)  + "." + ("0" + (d.getMonth() + 1)).slice(-2) + "." + d.getFullYear();
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

    // -----------------------------------------------------------------------------------------------------------------
    // Tabulator table format functions
    // -----------------------------------------------------------------------------------------------------------------
    // Formats the group header
    function func_groupHeader(data){
        return data[0].lv_bezeichnung;  // change name to lehrveranstaltung
    };

    // Formats the rows
    function func_rowFormatter(row){
        var bestellt = row.getData().bestellt;
        var betrag = parseFloat(row.getData().betrag);
        var vertrag_betrag = parseFloat(row.getData().vertrag_betrag);

        /*
        Formats the color of the rows depending on their status
        - orange: geaendert
        - default: neu und erteilt
        - green: akzeptiert
        - grey: all other (marks unselectable)
         */
        row.getCells().forEach(function(cell){
            if (bestellt != null && (betrag != vertrag_betrag)  && !row._row.element.classList.contains('tabulator-calcs')) // exclude calculation rows
            {
                // cell.getElement().classList.add('bg-warning')                   // geaendert
            }
            else if(row.getData().bestellt == null)
            {
                return;                                                         // neu und erteilt
            }
            else if(row.getData().bestellt != null && row.getData().erteilt != null && row.getData().akzeptiert != null)
            {
                cell.getElement().classList.add('bg-success')                   // akzeptiert
            }
            else
            {
                row.getElement().style["background-color"] = COLOR_LIGHTGREY;   // default
            }
        });
    }

    // Formats row selectable/unselectable
    function func_selectableCheck(row){
        var betrag = parseFloat(row.getData().betrag);
        var vertrag_betrag = parseFloat(row.getData().vertrag_betrag);

        // Only allow to select neue and geaenderte
        return  row.getData().bestellt == null ||  // neue
                row.getData().bestellt != null && betrag != vertrag_betrag; // geaenderte
    }

    // Adds column status
    function func_tableBuilt(table) {
        // Add status column to table
        table.addColumn(
            {
                title: "Status",
                field: "status",
                width:40,
                align:"center",
                formatter: status_formatter,
                tooltip: status_tooltip
            }, true
        );
    }

    // Sets status values into column status
    function func_renderStarted(table){
        // set literally status to each row - this enables sorting by status despite using icons
        table.getRows().forEach(function(row){
            var bestellt = row.getData().bestellt;
            var erteilt = row.getData().erteilt;
            var akzeptiert = row.getData().akzeptiert;

            var betrag = parseFloat(row.getData().betrag);
            var vertrag_betrag = parseFloat(row.getData().vertrag_betrag);

            if (bestellt != null && (betrag != vertrag_betrag))
            {
                row.getData().status = 'Geändert';      // geaendert
            }
            else if (bestellt == null && erteilt == null && akzeptiert == null)
            {
                row.getData().status = 'Neu';           // neu
            }
            else if (bestellt != null && erteilt == null && akzeptiert == null)
            {
                row.getData().status = 'Bestellt';      // bestellt
            }
            else if (bestellt != null && erteilt != null && akzeptiert == null)
            {
                row.getData().status = 'Erteilt';       // erteilt
            }
            else if (bestellt != null && erteilt != null && akzeptiert != null)
            {
                row.getData().status = 'Akzeptiert';    // akzeptiert
            }
            else
            {
                row.getData().status = null;            // default
            }
        });
    }

    function func_renderComplete(table){
        // console.log(table.getDataCount());
        // Display message if table is empty
        // TODO: msg funktioniert, aber wenn beim filtern kein Ergebnis (0 rows), und man den filter nachher wegmacht,
        // TODO: bleibt die msg. Ev gleich am anfang check: nur wenn nicht gefiltert. ODER ev besser: nicht in renderStarted, sondern tabelBuilt
        // if (table.getRows( == 0)
        // {
        //     table.element.childNodes.item(1).innerHTML =
        //         '<div class="tabulator-row panel text-center" role="row" style="padding: 50px;">Es sind keine Lehraufträge vorhanden.</div>';
        // }
    }

    // Performes after row was updated
    function func_rowUpdated(row){
        // Deselect and disable new selection of updated rows (ordering done)
        row.deselect();
        row.getElement().style["background-color"] = COLOR_LIGHTGREY;
        row.getElement().style["pointerEvents"] = "none";
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Tabulator columns format functions
    // -----------------------------------------------------------------------------------------------------------------
    // Generates status icons
    status_formatter = function(cell, formatterParams, onRendered){

         var bestellt = cell.getRow().getData().bestellt;
         var erteilt = cell.getRow().getData().erteilt;
         var akzeptiert = cell.getRow().getData().akzeptiert;

         var betrag = parseFloat(cell.getRow().getData().betrag);
         var vertrag_betrag = parseFloat(cell.getRow().getData().vertrag_betrag);

         // commented icons would be so nice to have with fontawsome 5.11...
         if (bestellt != null && isNaN(vertrag_betrag))
         {
             return "<i class='fas fa-user-minus'></i>";    // kein Vertrag
         }
         else if (bestellt != null && (betrag != vertrag_betrag))
         {
             return "<i class='fa fa-pencil'></i>";     // geaendert
             // return "<i class='fas fa-user-edit'></i>";     // geaendert
         }
         else if (bestellt == null && erteilt == null && akzeptiert == null)
         {
             return "<i class='fa fa-user-plus'></i>";      // neu
         }
         else if (bestellt != null && erteilt == null && akzeptiert == null)
         {
             return "<i class='fa fa-check-square-o'></i>";     // bestellt
             // return "<i class='fa fa-user-tag'></i>";     // bestellt
         }
         else if (bestellt != null && erteilt != null && akzeptiert == null)
         {
             return "<i class='fa fa-check-square'></i>";  // erteilt
             // return "<i class='fas fa-user-check'></i>";  // erteilt
         }
         else if (bestellt != null && erteilt != null && akzeptiert != null)
         {
             return "<i class='fa fa-handshake-o'></i>";  // akzeptiert
             // return "<i class='fas fa-user-graduate'></i>";  // akzeptiert
         }
         else
         {
            return "<i class='fa fa-user'></i>";            // default
         }
    };

    // Generates status tooltip
    status_tooltip = function(cell){
        var betrag = parseFloat(cell.getRow().getData().betrag);
        var stunden = parseFloat(cell.getRow().getData().stunden);
        var stundensatz = parseFloat(cell.getRow().getData().stundensatz);

        var vertrag_betrag = parseFloat(cell.getRow().getData().vertrag_betrag);
        var vertrag_stunden = parseFloat(cell.getRow().getData().vertrag_stunden);
        var vertrag_stundensatz = 0;

        // Calculate vertrag stundensatz
        if (vertrag_stunden != 0)
        {
            vertrag_stundensatz = vertrag_betrag/vertrag_stunden;
        }

        // Return tooltip message
        if (isNaN(vertrag_betrag))
        {
            return 'Neuer Lehrauftrag. Noch kein Vertrag vorhanden.'
        }
        else if (betrag != vertrag_betrag) {
            var text = 'NACH Änderung: Stundensatz: ' + stundensatz + '  Stunden: ' + stunden;
                text += "\n";
                text += 'VOR Änderung:' + '\xa0\xa0\xa0' + 'Stundensatz: ' + vertrag_stundensatz + '  Stunden: ' + vertrag_stunden;
            return text;
         }
    }

$(function() {

    // Select all (filtered) rows and ignore rows which have status bestellt
    $("#select-all").click(function(){
        $('#filterTabulator').tabulator('getRows', true)
            .filter(row => row.getData().bestellt == null)
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

    // Show only rows with new lehrauftraege
    $("#show-new").click(function(){
        $('#filterTabulator').tabulator('setFilter',
            [
                {field: 'bestellt', type: '=', value: null},
                {field: 'erteilt', type: '=', value: null},
                {field: 'akzeptiert', type: '=', value: null}
            ]
        );
    });

    // Show only rows with ordered lehrauftraege
    $("#show-ordered").click(function(){
        $('#filterTabulator').tabulator('setFilter',
            [
                {field: 'bestellt', type: '!=', value: null},
                {field: 'erteilt', type: '=', value: null},
                {field: 'akzeptiert', type: '=', value: null}
            ]
        );
    });

    // Show only rows with erteilte lehrauftraege
    $("#show-approved").click(function(){
        $('#filterTabulator').tabulator('setFilter',
            [
                {field: 'bestellt', type: '!=', value: null},
                {field: 'erteilt', type: '!=', value: null},
                {field: 'akzeptiert', type: '=', value: null}
            ]
        );
    });

    // Show only rows with ordered lehrauftraege
    $("#show-accepted").click(function(){
        $('#filterTabulator').tabulator('setFilter',
            [
                {field: 'bestellt', type: '!=', value: null},
                {field: 'erteilt', type: '!=', value: null},
                {field: 'akzeptiert', type: '!=', value: null}
            ]
        );
    });

    // Order Lehrauftraege
    $("#order-lehrauftraege").click(function(){

        var selected_data = $('#filterTabulator').tabulator('getSelectedData')
            .filter(function(val){
                // filter pseudo lines of groupBy (e.g. the bottom calculations lines)
                return val.row_index != null || typeof(val.row_index) !== 'undefined';
            });

        // Alert and exit if no lehraufgang is selected
        if (selected_data.length == 0)
        {
            FHC_DialogLib.alertInfo('Bitte wählen Sie erst zumindest einen Lehrauftrag');
            return;
        }

        // Prepare data object for ajax call
        var data = {
            'selected_data': selected_data
        };

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/orderLehrauftrag",
            data,
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (!data.error && data.retval != null)
                    {
                        // Update status 'Bestellt'
                        $('#filterTabulator').tabulator('updateData', data.retval);
                    }

                    FHC_DialogLib.alertSuccess("Alle " + data.retval.length + " Lehraufträge wurden bestellt.")
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError("Sytemfehler<br>Bitte kontaktieren Sie Ihren Administrator.");
                }
            }
        );
     });
});
</script>
