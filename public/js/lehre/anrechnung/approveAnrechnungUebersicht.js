const BASE_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
const APPROVE_ANRECHNUNG_DETAIL_URI = "lehre/anrechnung/ApproveAnrechnungDetail";

const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';
const ANRECHNUNGSTATUS_APPROVED = 'approved';
const ANRECHNUNGSTATUS_REJECTED = 'rejected';

// TABULATOR FUNCTIONS
// ---------------------------------------------------------------------------------------------------------------------
// Returns relative height (depending on screen size)
function func_height(table){
    return $(window).height() * 0.50;
}

// Adds column details
function func_tableBuilt(table) {
    table.addColumn(
        {
            title: "Details",
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

// Formats row selectable/unselectable
function func_selectableCheck(row){
    let status_kurzbz = row.getData().status_kurzbz;

    return (
        status_kurzbz != ANRECHNUNGSTATUS_APPROVED &&
        status_kurzbz != ANRECHNUNGSTATUS_REJECTED &&
        status_kurzbz != ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR
    );
}

// Performes after row was updated
function func_rowUpdated(row){
        row.deselect();
        row.getElement().style["pointerEvents"] = "none";
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
            row.getData().status_kurzbz == ANRECHNUNGSTATUS_PROGRESSED_BY_STGL
        )
        .forEach((row => row.select()));
}


$(function(){
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

    // Approve Anrechnungen
    $("#approve-anrechnungen").click(function(){
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
            FHC_DialogLib.alertInfo('Bitte wählen Sie erst zumindest einen Antrag auf Anrechnung');
            return;
        }


        // Prepare data object for ajax call
        let data = {
            'data': selected_data
        };

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/approve",
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
                        FHC_DialogLib.alertSuccess(data.retval.length + " Anrechnungsanträge wurden genehmigt.");
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError("Systemfehler<br>Bitte kontaktieren Sie Ihren Administrator.");
                }
            }
        );
    });

    // Reject Anrechnungen
    $("#reject-anrechnungen").click(function(){
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
            FHC_DialogLib.alertInfo('Bitte wählen Sie erst zumindest einen Antrag auf Anrechnung');
            return;
        }

        // Confirm before rejecting
        if(!confirm('Wollen Sie wirklich die gewählten Anträge ablehnen?'))
        {
            return;
        }

        // Prepare data object for ajax call
        let data = {
            'data': selected_data
        };

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/reject",
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
                        FHC_DialogLib.alertSuccess(data.retval.length + " Anrechnungsanträge wurden abgelehnt.");
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError("Systemfehler<br>Bitte kontaktieren Sie Ihren Administrator.");
                }
            }
        );
    });

    // Request Recommendation for Anrechnungen
    $("#request-recommendation").click(function(){
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
            FHC_DialogLib.alertInfo('Bitte wählen Sie erst zumindest einen Antrag auf Anrechnung');
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
                        FHC_DialogLib.alertSuccess("Empfehlungen wurden angefordert.");
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError("Systemfehler<br>Bitte kontaktieren Sie Ihren Administrator.");
                }
            }
        );
    });

});