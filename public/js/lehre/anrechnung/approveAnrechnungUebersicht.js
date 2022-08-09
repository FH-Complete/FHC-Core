const BASE_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
const APPROVE_ANRECHNUNG_DETAIL_URI = "lehre/anrechnung/ApproveAnrechnungDetail";

const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';
const ANRECHNUNGSTATUS_APPROVED = 'approved';
const ANRECHNUNGSTATUS_REJECTED = 'rejected';

const COLOR_LIGHTGREY = "#f5f5f5";
const COLOR_DANGER = '#f2dede';

var tabulator = null; // Set in tableBuilt function.

// Array with accumulated LV ECTS by Prestudent. Used to find out if max ECTS are exceeded.
var selectedPrestudentWithAccumulatedLvEcts = [];

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

// TABULATOR FUNCTIONS
// ---------------------------------------------------------------------------------------------------------------------
// Returns relative height (depending on screen size)
function func_height(table){
    return $(window).height() * 0.50;
}

// Filters boolean values
function hf_filterTrueFalse(headerValue, rowValue){

    if ('ja'.startsWith(headerValue) || 'yes'.startsWith(headerValue))
    {
        return rowValue == 'true';
    }

    if ('nein'.startsWith(headerValue) || 'no'.startsWith(headerValue))
    {
        return rowValue == 'false';
    }

    if (headerValue = '-')
    {
        return rowValue == null;
    }
}

// Adds column details
// Sets focus on filterbutton, if table starts with stored filter.
function func_tableBuilt(table) {

    // Store table in global var
    tabulator = table;

    table.addColumn(
        {
            title: "Details",
            field: 'details',
            align: "center",
            width: 100,
            formatter: "link",
            formatterParams:{
                label:"Details",
                url:function(cell){
                    return  BASE_URL + "/" + APPROVE_ANRECHNUNG_DETAIL_URI + "?anrechnung_id=" + cell.getData().anrechnung_id
                },
                target:"_blank"
            }
        }, true  // place column on the very left
    );

    // Set focus on filterbutton
    let filters = table.getFilters();
    if (filters.length > 0){
        approveAnrechnung.focusFilterbuttonIfTableStartsWithStoredFilter(filters);
    }
}

/**
 * Formats column ECTS (LV + Bisher).
 */
var format_ectsSumBisherUndNeu = function(cell, formatterParams, onRendered){
    let row = cell.getRow();
    let rowData = row.getData();

    let begruendung_id = (rowData.begruendung_id);
    let ectsSumBisherUndNeuTotal = (rowData.ectsSumSchulisch + rowData.ectsSumBeruflich);
    let ectsSumBisherUndNeuSchulisch = rowData.ectsSumSchulisch;
    let ectsSumBisherUndNeuBeruflich = rowData.ectsSumBeruflich;

    // If exists, add accumulated LV ECTS to bisherige ECTS
    if (selectedPrestudentWithAccumulatedLvEcts.length > 0)
    {
        let selectedPrestudent = selectedPrestudentWithAccumulatedLvEcts.find(x => x.prestudent_id === rowData.prestudent_id);

        if (selectedPrestudent != undefined)
        {
            ectsSumBisherUndNeuTotal = (rowData.ectsSumSchulisch + rowData.ectsSumBeruflich) + selectedPrestudent.ectsSumAnzurechnendeLvsSchulisch + selectedPrestudent.ectsSumAnzurechnendeLvsBeruflich;
            ectsSumBisherUndNeuSchulisch = rowData.ectsSumSchulisch + selectedPrestudent.ectsSumAnzurechnendeLvsSchulisch;
            ectsSumBisherUndNeuBeruflich = rowData.ectsSumBeruflich + selectedPrestudent.ectsSumAnzurechnendeLvsBeruflich;
        }

        // Color column if maximum ECTS exceeded
        if (begruendung_id != 5 && row.isSelected())
        {

            if (
                (ectsSumBisherUndNeuSchulisch + ectsSumBisherUndNeuBeruflich) > 90 ||
                ectsSumBisherUndNeuSchulisch > 60 ||
                ectsSumBisherUndNeuBeruflich > 60
            )
            {
                cell.getElement().style["background-color"] = COLOR_DANGER;
            }
        }
        else
        {
            cell.getElement().style.removeProperty('background-color');
        }
    }

    // If max ECTS is exceeded, format font color / weight
    ectsSumBisherUndNeuTotal = (ectsSumBisherUndNeuTotal > 90) ? "<span class='text-danger'><b><u>" + ectsSumBisherUndNeuTotal + "</u></b></span>" :  ectsSumBisherUndNeuTotal;
    ectsSumBisherUndNeuSchulisch = (ectsSumBisherUndNeuSchulisch > 60) ? "<span class='text-danger'><b><u>" + ectsSumBisherUndNeuSchulisch + "</u></b></span>" : ectsSumBisherUndNeuSchulisch;
    ectsSumBisherUndNeuBeruflich = (ectsSumBisherUndNeuBeruflich > 60) ? "<span class='text-danger'><b><u>" + ectsSumBisherUndNeuBeruflich + "</u></b></span>" : ectsSumBisherUndNeuBeruflich;

    return "T: " + ectsSumBisherUndNeuTotal + " [ S: " + ectsSumBisherUndNeuSchulisch + " | B: " + ectsSumBisherUndNeuBeruflich + " ]";
}

// Formats the rows
function func_rowFormatter(row){
    let status_kurzbz = row.getData().status_kurzbz;

    // If status is anything else then 'Bearbeitet von STGL-Leitung'
    if (status_kurzbz != ANRECHNUNGSTATUS_PROGRESSED_BY_STGL)
    {
        // Disable new selection of updated rows
        row.getElement().style["pointerEvents"] = "none";

        // ...but leave url links selectable
        row.getCell('dokument_bezeichnung').getElement().firstChild.style["pointerEvents"] = "auto";
        row.getCell('details').getElement().firstChild.style["pointerEvents"] = "auto";

        // Color background grey
        row.getElement().style["background-color"] = COLOR_LIGHTGREY;   // default
    }
}

// Formats row selectable/unselectable
function func_selectableCheck(row){
    let status_kurzbz = row.getData().status_kurzbz;

    return (
        status_kurzbz != ANRECHNUNGSTATUS_APPROVED &&
        status_kurzbz != ANRECHNUNGSTATUS_REJECTED &&
        status_kurzbz != ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR
    );
}

// Calculate dynamically sum of all LV ECTS by Student and display, when maximum ECTS are exceeded.
// data = selected data, rows = selected rows
function func_rowSelectionChanged(data, rows){

    // Sum up over all anzurechnenden LV-ECTS by Prestudent
    selectedPrestudentWithAccumulatedLvEcts = approveAnrechnung.getSumLvEctsByPreStudent(data);

    // Loop through all active rows
    var rowManager = tabulator.rowManager;
    for (var i = 0; i < rowManager.activeRows.length; i++) {

        // Reinitialize row -> triggers formatters.
        rowManager.activeRows[i].reinitialize();
    }

    // Show number of selected rows.
    approveAnrechnung.showNumberSelectedRows(rows);
}

// Returns tooltip
function func_tooltips(cell) {
    // Return tooltip if row is unselectable
    if (!func_selectableCheck(cell.getRow())){
        return FHC_PhrasesLib.t("ui", "nichtSelektierbarAufgrundVon") + 'Status';
    }
}

// Formats empfehlung_anrechnung
var format_empfehlung_anrechnung = function(cell, formatterParams){
    return (cell.getValue() == null)
        ? '-'
        : (cell.getValue() ==  'true')
            ? FHC_PhrasesLib.t("ui", "ja")
            : FHC_PhrasesLib.t("ui", "nein");
}

/*
 * Hook to overwrite TableWigdgets select-all-button behaviour
 * Select all (filtered) rows that are progressed by stg leiter.
 * (Ignore rows that are approved, rejected or in request for recommendation)
 */
function tableWidgetHook_selectAllButton(tableWidgetDiv){
    var resultRows = tableWidgetDiv.find("#tableWidgetTabulator").tabulator('getRows', true)
        .filter(row =>
            row.getData().status_kurzbz == ANRECHNUNGSTATUS_PROGRESSED_BY_STGL
        );

    tableWidgetDiv.find("#tableWidgetTabulator").tabulator('selectRow', resultRows);
}


$(function(){

    const genehmigung_panel = $('#approveAnrechnungUebersicht-genehmigung-panel');
    const begruendung_panel = $('#approveAnrechnungUebersicht-begruendung-panel');
    const hasReadOnlyAccess = $('#formApproveAnrechnungUebersicht').data('readonly');
    const hasCreateAnrechnungAccess = $('#formApproveAnrechnungUebersicht').data('createaccess');

    // Pruefen ob Promise unterstuetzt wird
    // Tabulator funktioniert nicht mit IE
    var canPromise = !! window.Promise;
    if(!canPromise)
    {
        alert("Diese Seite kann mit ihrem Browser nicht angezeigt werden. Bitte verwenden Sie Firefox, Chrome oder Edge um die Seite anzuzeigen");
        window.location.href='about:blank';
        return;
    }

    // Redraw table on resize to fit tabulators height to windows height
    window.addEventListener('resize', function(){
        $('#tableWidgetTabulator').tabulator('setHeight', $(window).height() * 0.50);
        $('#tableWidgetTabulator').tabulator('redraw', true);
    });

    if (hasReadOnlyAccess)
    {
        approveAnrechnung.disableEditElements();
    }

    if (!hasCreateAnrechnungAccess)
    {
        approveAnrechnung.disableCreateAnrechnungButton();
    }

    // Set status alert color
    approveAnrechnung.setStatusAlertColor();

    // Show only rows that are in progress by STGL
    $("#show-inProgressDP").click(function(){
        $('#tableWidgetTabulator').tabulator('setFilter',
            [
                {field: 'status_kurzbz', type: '=', value: ANRECHNUNGSTATUS_PROGRESSED_BY_STGL},
            ]
        );
    });

    // Show only rows that are in progress by lector
    $("#show-inProgressLektor").click(function(){
        $('#tableWidgetTabulator').tabulator('setFilter',
            [
                {field: 'status_kurzbz', type: '=', value: ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR},
                {field: 'empfehlung_anrechnung', type: '=', value: null}
            ]
        );
    });

    // Show only rows with empfohlene + noch nicht genehmigte/abgelehnte anrechnungen
    $("#show-recommended").click(function(){
        $('#tableWidgetTabulator').tabulator('setFilter',
            [
                {field: 'status_kurzbz', type: '=', value: ANRECHNUNGSTATUS_PROGRESSED_BY_STGL},
                {field: 'empfehlung_anrechnung', type: '=', value: 'true'}
            ]
        );
    });

    // Show only rows with nicht empfohlene + noch nicht genehmigte/abgelehnte anrechnungen
    $("#show-not-recommended").click(function(){
        $('#tableWidgetTabulator').tabulator('setFilter', [
                {field: 'status_kurzbz', type: '=', value: ANRECHNUNGSTATUS_PROGRESSED_BY_STGL},
                {field: 'empfehlung_anrechnung', type: '=', value: 'false'},
            ]
        );
    });

    // Show only rows with genehmigte anrechnungen
    $("#show-approved").click(function(){
        $('#tableWidgetTabulator').tabulator('setFilter',
            [
                {field: 'status_kurzbz', type: '=', value: ANRECHNUNGSTATUS_APPROVED}
            ]
        );
    });

    // Show only rows with abgelehnte anrechnungen
    $("#show-rejected").click(function(){
        $('#tableWidgetTabulator').tabulator('setFilter',
            [
                {field: 'status_kurzbz', type: '=', value: ANRECHNUNGSTATUS_REJECTED}
            ]
        );
    });

    /**
     * Show all rows: clear filter and blur button
     * Bootstrap button remains in activated style, even when clicking various times.
     * This function "resets" button style and clear all tabulators filter.
     * NOTE: MUST be after all other filters
     */
    $(".btn-clearfilter").click(function(){
        if($(this).hasClass('active'))
        {
            $('#tableWidgetTabulator').tabulator('clearFilter');
            $(this).blur();
        }
    })

    // Ask if Approve Anrechnungen
    $("#approveAnrechnungUebersicht-approve-anrechnungen-ask").click(function(){

        begruendung_panel.css('display', 'none');

        if (genehmigung_panel.is(":hidden"))
        {
            // Show begruendung panel if is hidden
            genehmigung_panel.slideDown(400, function() {
                $('html, body').animate({
                    scrollTop: genehmigung_panel.offset().top // Move genehmigung panel bottom up to be visible within window screen
                }, 400);
            });

            return;
        }
    });

    // Approve Anrechnungen
    $("#approveAnrechnungUebersicht-approve-anrechnungen-confirm").click(function(e){

        // Avoid bubbling click event to sibling break button
        e.stopImmediatePropagation();

        // Get selected rows data
        let selected_data = $('#tableWidgetTabulator').tabulator('getSelectedData');

        // Alert and exit if no anrechnung is selected
        if (selected_data.length == 0)
        {
            FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "bitteMindEinenAntragWaehlen"));
            return;
        }

        // Prepare data object for ajax call
        let data = {
            'data': selected_data
        };

        // Hide genehmigung panel again
        genehmigung_panel.slideUp('slow');

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/approve",
            data,
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (FHC_AjaxClient.isError(data))
                    {
                        // Print error message
                        FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
                    }
                    else if (FHC_AjaxClient.hasData(data))
                    {
                        data = FHC_AjaxClient.getData(data);

                        var prestudenten = Object.keys(data.prestudenten);

                        // Find intersection of selected and in fact updated Anrechnungen (in case server did not approve all).
                        var updatedData = selected_data.filter(x => prestudenten.some(prestudent => x.prestudent_id == prestudent));

                        // Sum up over all anzurechnenden LV-ECTS by Prestudent
                        var sumLvEctsByPrestudent = approveAnrechnung.getSumLvEctsByPreStudent(updatedData);

                        // Loop through Prestudenten
                        // key = Prestudent, value = Approved Anrechnungen of Prestudent
                        Object.entries(data.prestudenten).forEach(([key, value]) => {

                            var rowsToDeselect = [];

                            // Get accumulated sum of all LV ECTS
                            var sumLvEcts = sumLvEctsByPrestudent.find(x => x.prestudent_id == key);

                            // Get ALL rows of that Prestudent
                            var rows = $('#tableWidgetTabulator').tabulator('searchRows', 'prestudent_id', '=', key);

                            // Loop through the rows
                            rows.forEach(row => {
                                var updateData = {};

                                // If Anrechnung was approved...
                                if ((value.findIndex(anrechnung_id => row.getData().anrechnung_id == anrechnung_id)) !== -1)
                                {
                                    // ...update status
                                   updateData.status_kurzbz = data.status_kurzbz;
                                   updateData.status_bezeichnung = data.status_bezeichnung;

                                   // ...and store row to be deselected later on
                                   rowsToDeselect.push(row);
                                }

                                // Update 'Bisher schulische ECTS' and 'Bisher berufliche ECTS' with the Sum of new approved ECTS
                                updateData.ectsSumSchulisch = row.getData().ectsSumSchulisch + sumLvEcts.ectsSumAnzurechnendeLvsSchulisch,
                                updateData.ectsSumBeruflich = row.getData().ectsSumBeruflich + sumLvEcts.ectsSumAnzurechnendeLvsBeruflich


                                // Update row
                                row.update(updateData);

                                // Reformat row
                                row.reformat();

                            })

                            // Deselect rows
                            $("#tableWidgetTabulator").tabulator('deselectRow', rowsToDeselect);

                        })

                        // Print success message
                         FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("ui", "anrechnungenWurdenGenehmigt"));
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });

    // Ask if Reject Anrechnungen
    $("#approveAnrechnungUebersicht-reject-anrechnungen-ask").click(function(){

        genehmigung_panel.css('display', 'none');

        if (begruendung_panel.is(":hidden"))
        {
            // Show begruendung panel if is hidden
            begruendung_panel.slideDown(400, function() {
                $('html, body').animate({
                    scrollTop: begruendung_panel.offset().top // Move begruendung panel bottom up to be visible within window screen
                }, 400);
            });

            return;
        }
    });

    // Reject Anrechnungen
    $("#approveAnrechnungUebersicht-reject-anrechnungen-confirm").click(function(e){

        // Avoid bubbling click event to sibling break button
        e.stopImmediatePropagation();

        let begruendung = $('#approveAnrechnungUebersicht-begruendung').val();

        genehmigung_panel.css('display', 'none');

        // Check if begruendung is given
        if (!begruendung.trim()) // empty or white spaces only
        {
            FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "bitteBegruendungAngeben"));
            return;
        }

        // Get selected rows data
        let selected_data = $('#tableWidgetTabulator').tabulator('getSelectedData')
            .map(function(data){
                // reduce to necessary fields
                return {
                    'anrechnung_id' : data.anrechnung_id,
                    'begruendung'   : begruendung
                }
            });

        // Alert and exit if no anrechnung is selected
        if (selected_data.length == 0)
        {
            FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "bitteMindEinenAntragWaehlen"));
            return;
        }

        // Prepare data object for ajax call
        let data = {
            'data': selected_data
        };

        // Hide begruendung panel again
        begruendung_panel.slideUp('slow');

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/reject",
            data,
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (FHC_AjaxClient.isError(data))
                    {
                        // Print error message
                        FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
                    }
                    else if (FHC_AjaxClient.hasData(data))
                    {
                        data = FHC_AjaxClient.getData(data);

                        // Update status 'genehmigt'
                        $('#tableWidgetTabulator').tabulator('updateData', data);

                        // Deselect rows
                        var indexesToDeselect = data.map(x => x.anrechnung_id);
                        $("#tableWidgetTabulator").tabulator('deselectRow', indexesToDeselect);

                        // Print success message
                        FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("ui", "anrechnungenWurdenAbgelehnt"));
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });

    // Request Recommendation for Anrechnungen
    $("#approveAnrechnungUebersicht-request-recommendation").click(function(){

        // Get selected rows data
        let selected_data = $('#tableWidgetTabulator').tabulator('getSelectedData');

        // If some of selected anrechnungen has already been recommended...
        if (selected_data.some((data) => data.empfehlung_anrechnung !== null))
        {
            // ...confirm before requesting recommendation
            if(!confirm(FHC_PhrasesLib.t("anrechnung", "confirmTextAntragHatBereitsEmpfehlung")))
            {
                return;
            }
        }

        selected_data.map(function(data){
            // reduce to necessary fields
            return {
                'anrechnung_id' : data.anrechnung_id,
            }
        });

        // Alert and exit if no anrechnung is selected
        if (selected_data.length == 0)
        {
            FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "bitteMindEinenAntragWaehlen"));
            return;
        }

        // Prepare data object for ajax call
        let data = {
            'data': selected_data
        };

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/requestRecommendation",
            data,
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (FHC_AjaxClient.isError(data))
                    {
                        // Print error message
                        FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
                    }
                    else if (FHC_AjaxClient.hasData(data))
                    {
                        data = FHC_AjaxClient.getData(data);

                        // Print info message, if not all selected recommendations were requested
                        if (data.length < selected_data.length){
                            FHC_DialogLib.alertInfo(
                                FHC_PhrasesLib.t(
                                    "ui", "empfehlungWurdeAngefordertAusnahmeWoKeineLektoren",
                                    [selected_data.length, data.length, selected_data.length - data.length])
                            );
                        }
                        else
                        {
                            // Print success message
                            FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("ui", "empfehlungWurdeAngefordert"));
                        }
                    }

                    //Update status 'genehmigt'
                    $('#tableWidgetTabulator').tabulator('updateData', data);

                    // Deselect rows
                    var indexesToDeselect = data.map(x => x.anrechnung_id);
                    $("#tableWidgetTabulator").tabulator('deselectRow', indexesToDeselect);
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });

    // Break Genehmigung abgeben
    $('#approveAnrechnungUebersicht-empfehlung-abbrechen').click(function(){
        genehmigung_panel.slideUp('slow');

    })

    // Break Ablehnung abgeben
    $('#approveAnrechnungUebersicht-begruendung-abbrechen').click(function(){

        begruendung_panel.slideUp('slow');

    })

    // Copy Begruendung into textarea
    $(".btn-copyIntoTextarea").click(function(){
        approveAnrechnung.copyIntoTextarea(this);
    })

});

var approveAnrechnung = {
    setStatusAlertColor: function () {
        let status_kurzbz = $('#requestAnrechnung-status_kurzbz').data('status_kurzbz');

        switch (status_kurzbz) {
            case ANRECHNUNGSTATUS_APPROVED:
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('alert-success');
                break;
            case ANRECHNUNGSTATUS_REJECTED:
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('alert-danger');
                break;
            case '':
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('alert-info');
                break;
            default:
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('alert-warning');
        }
    },
    disableEditElements: function()
    {
        // Disable:
        // ...button Empfehlung anfordern
        $('#approveAnrechnungUebersicht-request-recommendation')
            .prop('disabled', true)
            .attr('title', FHC_PhrasesLib.t("ui", "nurLeseberechtigung"));
        // ...button Ablehnen
        $('#approveAnrechnungUebersicht-reject-anrechnungen-ask')
            .prop('disabled', true)
            .attr('title', FHC_PhrasesLib.t("ui", "nurLeseberechtigung"));
        // ...button Genehmigen
        $('#approveAnrechnungUebersicht-approve-anrechnungen-ask')
            .prop('disabled', true)
            .attr('title', FHC_PhrasesLib.t("ui", "nurLeseberechtigung"));

    },
    disableCreateAnrechnungButton: function()
    {
        // Disable button Antrag anlegen
        $('#approveAnrechnungUebersicht-create-anrechnung')
            .removeAttr('href')
            .css({'color': 'grey', 'pointer-events': 'none'}); // property disabled does not work for <a> link
    },
    copyIntoTextarea: function(elem){

        // Find closest textarea
        let textarea = $(elem).closest('div').find('textarea');

        // Copy begruendung into textarea
        textarea.val($.trim($(elem).parent().text()));
    },
    focusFilterbuttonIfTableStartsWithStoredFilter(filters){
        switch (filters[0].value) {
            case ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR:
                $("#show-inProgressLektor").addClass('active');
                break;
            case ANRECHNUNGSTATUS_APPROVED:
                $("#show-approved").addClass('active');
                break;
            case ANRECHNUNGSTATUS_REJECTED:
                $("#show-rejected").addClass('active');
                break;
            case ANRECHNUNGSTATUS_PROGRESSED_BY_STGL:
                if (filters.length > 1)
                {
                    if (filters[1].field == 'empfehlung_anrechnung')
                    {
                        if (filters[1].value === 'true')
                        {
                            $("#show-recommended").addClass('active');
                        }
                        else
                        {
                            $("#show-not-recommended").addClass('active');
                        }
                    }
                }
                else
                {
                    $("#show-inProgressDP").addClass('active');
                }

                break;

        }
    },
    getSumLvEctsByPreStudent(data){

        var result = [];
        
        // Berechne für jeden Prestudenten die kumulierte Summe aller selektierten LV ECTS
        data.reduce((prev, curr) => {

            if (!prev[curr.prestudent_id])
            {
                prev[curr.prestudent_id] = {
                    prestudent_id: curr.prestudent_id,
                    ectsSumAnzurechnendeLvsSchulisch: 0,
                    ectsSumAnzurechnendeLvsBeruflich: 0
                };

                result.push(prev[curr.prestudent_id])
            }

            // Kumulierte Summe aller selektierten LVs, die angerechnet werden sollen, getrennt nach
            // schulischer und beruflicher Qualifikation.
            // Ausgenommen ist die universitäre Qualifikation (5), da diese unbegrenzt möglich sind.
            if (curr.begruendung_id != 5)
            {
                if (curr.begruendung_id == 4)
                {
                    prev[curr.prestudent_id].ectsSumAnzurechnendeLvsBeruflich += curr.ects;
                }
                else
                {
                    prev[curr.prestudent_id].ectsSumAnzurechnendeLvsSchulisch += curr.ects;
                }
            }

            return prev;

        }, {});

        return result;
    },
    showNumberSelectedRows(rows){
        $('#number-selected').html("Ausgewählte Zeilen: <strong>" + rows.length + "</strong>");
    }
}