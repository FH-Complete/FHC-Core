const BASE_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
const APPROVE_ANRECHNUNG_DETAIL_URI = "lehre/anrechnung/ReviewAnrechnungDetail";

const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';
const ANRECHNUNGSTATUS_APPROVED = 'approved';
const ANRECHNUNGSTATUS_REJECTED = 'rejected';

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
function func_tableBuilt(table) {
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
        }, false, "status"  // place column after status
    );
}

// Formats the rows
function func_rowFormatter(row){
    let status_kurzbz = row.getData().status_kurzbz;

    row.getCells().forEach(function(cell){
        if (status_kurzbz != ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR)
        {
            row.getElement().style["background-color"] = COLOR_LIGHTGREY;   // default
        }
    });
}

// Formats row selectable/unselectable
function func_selectableCheck(row){
    let status_kurzbz = row.getData().status_kurzbz;

    return (
        status_kurzbz == ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR
    );
}

// Performes after row was updated
function func_rowUpdated(row){
    // Refresh row formatters
    row.reformat();

    // Deselect and disable new selection of updated rows
    row.deselect();
    row.getElement().style["pointerEvents"] = "none";

    // ...but leave url links selectable
    row.getCell('dokument_bezeichnung').getElement().firstChild.style["pointerEvents"] = "auto";
    row.getCell('details').getElement().firstChild.style["pointerEvents"] = "auto";
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
    tableWidgetDiv.find("#tableWidgetTabulator").tabulator('getRows', true)
        .filter(row =>
            row.getData().status_kurzbz == ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR
        )
        .forEach((row => row.select()));
}


$(function(){

    const empfehlung_panel = $('#reviewAnrechnungUebersicht-empfehlung-panel');
    const begruendung_panel = $('#reviewAnrechnungUebersicht-begruendung-panel');

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

    // Set status alert color
    reviewAnrechnung.setStatusAlertColor();

    // Show only rows with anrechnungen ohne Empfehlung
    $("#show-need-recommendation").click(function(){
        $('#tableWidgetTabulator').tabulator('setFilter',
            [
                {field: 'status_kurzbz', type: '=', value: ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR}
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

    // Ask ifRecommend Anrechnungen
    $("#reviewAnrechnungUebersicht-recommend-anrechnungen-ask").click(function(){

        begruendung_panel.css('display', 'none');

        if (empfehlung_panel.is(":hidden"))
        {
            // Show begruendung panel if is hidden
            empfehlung_panel.slideDown('slow');
            return;
        }
    });

    // Recommend Anrechnungen
    $("#reviewAnrechnungUebersicht-recommend-anrechnungen-confirm").click(function(e){

        // Avoid bubbling click event to sibling break button
        e.stopImmediatePropagation();

        // Get selected rows data
        let selected_data = $('#tableWidgetTabulator').tabulator('getSelectedData')
            .map(function(data){
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

        // Hide empfehlung panel again
        empfehlung_panel.slideUp('slow');

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/recommend",
            data,
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (data.error && data.retval != null)
                    {
                        // Print error message
                        FHC_DialogLib.alertWarning(data.retval);
                    }

                    if (!data.error && data.retval != null)
                    {
                        // Update status 'genehmigt'
                        $('#tableWidgetTabulator').tabulator('updateData', data.retval);

                        // Print success message
                        FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("ui", "anrechnungenWurdenEmpfohlen"));
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });

    // Ask if Dont recommend Anrechnungen
    $("#reviewAnrechnungUebersicht-dont-recommend-anrechnungen-ask").click(function(){

        empfehlung_panel.css('display', 'none');

        if (begruendung_panel.is(":hidden"))
        {
            // Show begruendung panel if is hidden
            begruendung_panel.slideDown('slow');
            return;
        }
    });

    // Dont recommend Anrechnungen
    $("#reviewAnrechnungUebersicht-dont-recommend-anrechnungen-confirm").click(function(e){

        // Avoid bubbling click event to sibling break button
        e.stopImmediatePropagation();

        let begruendung = $('#reviewAnrechnungUebersicht-begruendung').val();

        empfehlung_panel.css('display', 'none');

        // Check if begruendung is given
        if (!begruendung.trim()) // empty or white spaces only
        {
            FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "bitteBegruendungAngeben"));
            return;
        }

        // Get selected rows data and add begruendung
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
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/dontRecommend",
            data,
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (data.error && data.retval != null)
                    {
                        // Print error message
                        FHC_DialogLib.alertWarning(data.retval);
                    }

                    if (!data.error && data.retval != null)
                    {
                        // Update status 'genehmigt'
                        $('#tableWidgetTabulator').tabulator('updateData', data.retval);

                        // Print success message
                        FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("ui", "anrechnungenWurdenNichtEmpfohlen"));
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });

    // Break Empfehlung abgeben
    $('#reviewAnrechnungUebersicht-empfehlung-abbrechen').click(function(){
        empfehlung_panel.slideUp('slow');

    })

    // Break Begruendung abgeben
    $('#reviewAnrechnungUebersicht-begruendung-abbrechen').click(function(){

        begruendung_panel.slideUp('slow');

    })

    // Copy Begruendung into textarea
    $(".btn-copyIntoTextarea").click(function(){
        reviewAnrechnung.copyIntoTextarea(this);
    })
});

var reviewAnrechnung = {
    setStatusAlertColor: function () {
        let status_kurzbz = $('#reviewAnrechnung-status_kurzbz').data('status_kurzbz');

        switch (status_kurzbz) {
            case ANRECHNUNGSTATUS_APPROVED:
                $('#reviewAnrechnung-status_kurzbz').closest('div').addClass('alert-success');
                break;
            case ANRECHNUNGSTATUS_REJECTED:
                $('#reviewAnrechnung-status_kurzbz').closest('div').addClass('alert-danger');
                break;
            case '':
                $('#reviewAnrechnung-status_kurzbz').closest('div').addClass('alert-info');
                break;
            default:
                $('#reviewAnrechnung-status_kurzbz').closest('div').addClass('alert-warning');
        }
    },
    copyIntoTextarea: function(elem){

        // Find closest textarea
        let textarea = $(elem).closest('div').find('textarea');

        // Copy begruendung into textarea
        textarea.val($.trim($(elem).parent().text()));
    }
}