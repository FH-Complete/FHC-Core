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
        'sbadmintemplate' => false,
        'tabulator' => true,
        'momentjs' => true,
        'ajaxlib' => true,
        'dialoglib' => true,
        'filterwidget' => true,
        'navigationwidget' => true,
        'phrases' => array(
            'global' => array('lehrauftraegeAnnehmen'),
        ),
       // 'customCSSs' => 'public/css/sbadmin2/tablesort_bootstrap.css',
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
                    <?php echo ucfirst($this->p->t('global', 'lehrauftraegeAnnehmen')); ?>
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
                    <button type="submit" name="submit" value="anzeigen" class="btn btn-default form-group">Anzeigen</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <?php $this->load->view('lehre/lehrauftrag/acceptLehrauftragData.php'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-6">
                <button id="select-all" class="btn btn-default">Alle auswählen</button>
                <button id="deselect-all" class="btn btn-default">Alle abwählen</button>
                <button id="show-all" class="btn btn-default btn-lehrauftrag focus" data-toggle="tooltip" data-placement="left" title="Alle anzeigen"><i class='fa fa-users'></i></button>
                <button id="show-ordered" class="btn btn-default btn-lehrauftrag" data-toggle="tooltip" data-placement="left" title="Nur bestellte anzeigen"></button>
                <button id="show-approved" class="btn btn-default btn-lehrauftrag" data-toggle="tooltip" data-placement="left" title="Nur erteilte anzeigen"></button>
                <button id="show-accepted" class="btn btn-default btn-lehrauftrag" data-toggle="tooltip" data-placement="left" title="Nur akzeptierte anzeigen"><i class='fa fa-handshake-o'></i></button>
            </div><!-- end col -->
            <div class="col-md-offset-2 col-xs-offset-1 col-md-4 col-xs-5">
                <div class="input-group">
                    <input id="password" type="password" class="form-control" placeholder="Login-Passwort">
                    <span class="input-group-btn">
                        <button id="accept-lehrauftraege" class="btn btn-primary pull-right">Lehrauftrag akzeptieren</button>
                    </span>
                </div>
            </div><!-- end col -->
        </div>

    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>


<script type="text/javascript">

    // -----------------------------------------------------------------------------------------------------------------
    // Global vars
    // -----------------------------------------------------------------------------------------------------------------

    const COLOR_LIGHTGREY = "#f5f5f5";
    // Store boolean has_inkludierteLehre. If true, used to hide column Betrag.
    var has_inkludierteLehre = new Boolean(<?php echo $has_inkludierteLehre ?>).valueOf();

    /**
     * PNG icons used in status- and filter buttons
     * Setting png icons is a workaround to use font-awsome 5.9.0 icons until system can be updated to newer font awsome version.
     * */
    const ICON_LEHRAUFTRAG_ORDERED = '<img src="../../../public/images/icons/fa-user-tag.png" style="height: 30px; width: 30px; margin: -5px;">';
    const ICON_LEHRAUFTRAG_APPROVED = '<img src="../../../public/images/icons/fa-user-check.png" style="height: 30px; width: 30px; margin: -5px;">';
    const ICON_LEHRAUFTRAG_CHANGED = '<img src="../../../public/images/icons/fa-user-edit.png" style="height: 30px; width: 30px; margin: -5px;">';


    // -----------------------------------------------------------------------------------------------------------------
    // Mutators - setter methods to manipulate table data when entering the tabulator
    // -----------------------------------------------------------------------------------------------------------------

    // Converts string date postgre style to string DD.MM.YYYY.
    // This will allow correct filtering.
    var mut_formatStringDate = function(value, data, type, params, component) {
        if (value != null)
        {
            var d = new Date(value);
            return ("0" + (d.getDate())).slice(-2)  + "." + ("0"+(d.getMonth()+1)).slice(-2) + "." + d.getFullYear();
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Header filter
    // -----------------------------------------------------------------------------------------------------------------

    // Filters values using comparison operator or just by string comparison
    function hf_filterStringnumberWithOperator(headerValue, rowValue, rowData){

        // If string starts with <, <=, >, >=, !=, ==, compare values with that operator
        var operator = '';
        if (headerValue.match(/([<=>!]{1,2})/g)) {
            var operator_arr = headerValue.match(/([<=>!]{1,2})/g);
            operator = operator_arr[0];

            headerValue = headerValue
                .replace(operator, '')
                .trim()
            ;

            // return if value comparison is true
            return eval(rowValue + operator + headerValue);
        }

        // If just a stringnumber, return if exact match found
        return parseFloat(rowValue) == headerValue;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Tabulator table format functions
    // -----------------------------------------------------------------------------------------------------------------

    // Formats the rows
    function func_rowFormatter(row){
        var bestellt = row.getData().bestellt;
        var erteilt = row.getData().erteilt;
        var akzeptiert = row.getData().akzeptiert;

        var betrag = parseFloat(row.getData().betrag);
        var vertrag_betrag = parseFloat(row.getData().vertrag_betrag);

        /*
        Formats the color of the rows depending on their status
        - orange: geaendert
        - default: bestellte und erteilte (= zu akzeptierende)
        - green: akzeptierte
        - grey: all other (marks unselectable)
         */
        row.getCells().forEach(function(cell){
            if (bestellt != null && (betrag != vertrag_betrag))
            {
                cell.getElement().classList.add('bg-warning');                  // geaenderte
            }
            else if(bestellt != null && erteilt != null && akzeptiert == null)
            {
                return;                                                         // bestellte + erteilte
            }
            else if(bestellt != null && erteilt != null && akzeptiert != null)
            {
                cell.getElement().classList.add('bg-success')                   // akzeptierte
            }
            else
            {
                row.getElement().style["background-color"] = COLOR_LIGHTGREY;   // default
            }
        });
    }

    // Formats row selectable/unselectable
    function func_selectableCheck(row){
        var betrag = row.getData().betrag;
        var vertrag_betrag = row.getData().vertrag_betrag;

        // only allow to select bestellte && erteilte && nicht geaenderte Lehraufträge
        return  row.getData().bestellt != null &&       // bestellt
                row.getData().erteilt != null &&        // AND erteilt
                row.getData().akzeptiert == null &&     // AND nicht akzeptiert
                betrag == vertrag_betrag;               // OR nicht geaenderte
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
        // Set literally status to each row - this enables sorting by status despite using icons
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

    // Performes after row was updated
    function func_rowUpdated(row){

        // Refresh status icon and row color
        row.reformat(); // retriggers cell formatters and rowFormatter callback

        // Deselect and disable new selection of updated rows
        row.deselect();
        row.getElement().style["pointerEvents"] = "none";
    }

    // Hide betrag, if lector has inkludierte Lehre
    function func_renderComplete(table){

        // If the lectors actual Verwendung has inkludierte Lehre, hide the column betrag
       if (has_inkludierteLehre)
       {
           table.hideColumn("betrag");
       }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Tabulator columns format functions
    // -----------------------------------------------------------------------------------------------------------------
    // Generates status icons
    status_formatter = function(cell, formatterParams, onRendered){

        var bestellt = cell.getRow().getData().bestellt;
        var erteilt = cell.getRow().getData().erteilt;
        var akzeptiert = cell.getRow().getData().akzeptiert;

        var betrag = cell.getRow().getData().betrag;
        var vertrag_betrag = cell.getRow().getData().vertrag_betrag;

        // commented icons would be so nice to have with fontawsome 5.11...
        if (bestellt != null && isNaN(vertrag_betrag))
        {
            return "<i class='fas fa-user-minus'></i>";     // kein Vertrag
        }
        else if (bestellt != null && (betrag != vertrag_betrag))
        {
            return ICON_LEHRAUFTRAG_CHANGED;               // geaendert
            // return "<i class='fas fa-user-edit'></i>";
        }
        else if (bestellt == null && erteilt == null && akzeptiert == null)
        {
            return "<i class='fa fa-user-plus'></i>";       // neu
        }
        else if (bestellt != null && erteilt == null && akzeptiert == null)
        {
            return ICON_LEHRAUFTRAG_ORDERED;                // bestellt
            // return "<i class='fa fa-user-tag'></i>";
        }
        else if (bestellt != null && erteilt != null && akzeptiert == null)
        {
            return ICON_LEHRAUFTRAG_APPROVED;               // erteilt
            // return "<i class='fas fa-user-check'></i>";
        }
        else if (bestellt != null && erteilt != null && akzeptiert != null)
        {
            return "<i class='fa fa-handshake-o'></i>";     // akzeptiert
            // return "<i class='fas fa-user-graduate'></i>";
        }
        else
        {
            return "<i class='fa fa-user'></i>";            // default
        }
    };

    // Generates status tooltip
    status_tooltip = function(cell){
        var bestellt = cell.getRow().getData().bestellt;
        var erteilt = cell.getRow().getData().erteilt;
        var akzeptiert = cell.getRow().getData().akzeptiert;

        var betrag = parseFloat(cell.getRow().getData().betrag);
        var vertrag_betrag = parseFloat(cell.getRow().getData().vertrag_betrag);

        var text = 'Lehrauftrag in Bearbeitung. ';

        if (bestellt != null && erteilt == null && akzeptiert == null
            && betrag != vertrag_betrag)                                        // geaendert (when never erteilt before)
        {
            text += 'Wartet auf Erteilung.';
            return text;
        }
        else if (bestellt != null && erteilt != null && akzeptiert == null
            && betrag != vertrag_betrag)                                        // geaendert (when has been erteilt once)
        {
            text += 'Wartet auf erneute Erteilung.';
            return text;
        }
        else if (bestellt != null && erteilt == null && akzeptiert == null)     // bestellt
        {
            return 'Letzter Status: Bestellt. Wartet auf Erteilung.';
        }
        else if (bestellt != null && erteilt != null && akzeptiert == null)     // erteilt
        {
            return 'Letzter Status: Erteilt. Wartet auf Annahme durch Lektor.';
        }
        else if (bestellt != null && erteilt != null && akzeptiert != null)     // akzeptiert
        {
            return 'Letzter Status: Angenommen. Vertrag wurde beidseitig abgeschlossen.';
        }
    }

    // Generates bestellt tooltip
    bestellt_tooltip = function(cell){
        if (cell.getRow().getData().bestellt_von != null)
        {
            return 'Bestellt von: ' + cell.getRow().getData().bestellt_von;
        }
    }

    // Generates erteilt tooltip
    erteilt_tooltip = function(cell){
        if (cell.getRow().getData().erteilt_von != null) {
            return 'Erteilt von: ' + cell.getRow().getData().erteilt_von;
        }
    }

    // Generates akzeptiert tooltip
    akzeptiert_tooltip = function(cell){
        if (cell.getRow().getData().akzeptiert_von != null) {
            return 'Angenommen von: ' + cell.getRow().getData().akzeptiert_von;
        }
    }

    $(function() {

    // Select all (filtered) rows, where status bestellt AND erteilt has a value and status akzeptiert has no value.
    $("#select-all").click(function(){
        $('#filterTabulator').tabulator('getRows', true)
            .filter(function(row){
                return row.getData().bestellt != null && row.getData().erteilt != null && row.getData().akzeptiert == null;
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
        $('#filterTabulator').tabulator('setFilter', [
                {field: 'bestellt', type: '!=', value: null},   // filter when is bestellt
                {field: 'erteilt', type: '!=', value: null},    // and is erteilt
                {field: 'akzeptiert', type: '=', value: null}  // and is not akzeptiert
            ]
        );
    });

    // Show only rows with akzeptierte lehrauftraege
    $("#show-accepted").click(function(){
            $('#filterTabulator').tabulator('setFilter',
                [
                    {field: 'bestellt', type: '!=', value: null},
                    {field: 'erteilt', type: '!=', value: null},
                    {field: 'akzeptiert', type: '!=', value: null}
                ]
            );
        });

    // Set png-icons into filter-buttons
    $(".btn-lehrauftrag").each(function(){
        switch(this.id) {
            case 'show-ordered':
                this.innerHTML = ICON_LEHRAUFTRAG_ORDERED;
                break;
            case 'show-approved':
                this.innerHTML = ICON_LEHRAUFTRAG_APPROVED;
                break;
        }
    });


    // Approve Lehrauftraege
    $("#accept-lehrauftraege").click(function(){

        // Get selected rows data
        var selected_data = $('#filterTabulator').tabulator('getSelectedData');
        if (selected_data.length == 0)
        {
            // Emtpy password field
            $("#password").val('');
            FHC_DialogLib.alertInfo('Bitte wählen Sie erst zumindest einen Lehrauftrag');
            return;
        }

        // Get password for verification
        var password = $("#password").val();
        if (password == '')
        {
            FHC_DialogLib.alertInfo('Bitte verifizieren Sie sich mit Ihrem Login Passwort.');
            $("#password").focus();
            return;
        }

        // Prepare data object for ajax call
        var data = {
            'password': password,
            'selected_data': selected_data
        };

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/acceptLehrauftrag",
            data,
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (data.error)
                    {
                        // Password not verified
                        FHC_DialogLib.alertWarning(data.retval);
                    }
                    if (!data.error && data.retval != null)
                    {
                        // Update status 'Erteilt'
                        $('#filterTabulator').tabulator('updateData', data.retval);
                        FHC_DialogLib.alertSuccess(data.retval.length + " Lehraufträge wurden akzeptiert.");
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError("Systemfehler<br>Bitte kontaktieren Sie Ihren Administrator.");
                }
            }
        );

        // Empty password field
        $("#password").val('');

     });

    // Focus on clicked button
    $(".btn-lehrauftrag").click(function() {
        $(".btn-lehrauftrag").removeClass('focus');
        $(this).addClass('focus');
    });
});
</script>
