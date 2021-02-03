const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';
const ANRECHNUNGSTATUS_APPROVED = 'approved';
const ANRECHNUNGSTATUS_REJECTED = 'rejected';



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

    // Set status alert color
    approveAnrechnungDetail.setStatusAlertColor();

    // Approve Anrechnungen
    $("#approve-anrechnung").click(function(){
        let genehmigung_panel = $('#approveAnrechnungUebersicht-empfehlung-panel');
        let begruendung_panel = $('#approveAnrechnungUebersicht-begruendung-panel');

        begruendung_panel.css('display', 'none');

        if (genehmigung_panel.is(":hidden"))
        {
            // Show begruendung panel if is hidden
            genehmigung_panel.slideDown('slow');
            return;
        }

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

        // Hide genehmigung panel again
        genehmigung_panel.slideUp('slow');

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
    $("#reject-anrechnung").click(function(){
        let begruendung_panel = $('#approveAnrechnungUebersicht-begruendung-panel');
        let begruendung = $('#approveAnrechnungUebersicht-begruendung').val();
        let genehmigung_panel = $('#approveAnrechnungUebersicht-empfehlung-panel');

        genehmigung_panel.css('display', 'none');

        if (begruendung_panel.is(":hidden"))
        {
            // Show begruendung panel if is hidden
            begruendung_panel.slideDown('slow');
            return;
        }
        else
        {
            // Check if begruendung is given
            if (!begruendung.trim()) // empty or white spaces only
            {
                FHC_DialogLib.alertInfo('Bitte tragen Sie eine Begründung ein.');
                return;
            }
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

        // Hide begruendung panel again
        begruendung_panel.slideUp('slow');

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

        // Get form data
        // index 0: anrechnung_id
        let form_data = $('form').serializeArray();


        // Prepare data object for ajax call
        let data = {
            'data': [{
                'anrechnung_id' : form_data[0].value
            }]
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
                        approveAnrechnungDetail.formatEmpfehlungIsRequested(
                            data.retval[0].empfehlung_angefordert_am,
                            data.retval[0].status_bezeichnung
                        );
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError("Systemfehler<br>Bitte kontaktieren Sie Ihren Administrator.");
                }
            }
        );
    });

    // Copy Begruendung into textarea
    $(".btn-copyIntoTextarea").click(function(){
        approveAnrechnungDetail.copyIntoTextarea(this);
    })

    // Break Empfehlung abgeben
    $('#approveAnrechnungDetail-empfehlung-abbrechen').click(function(){
        $('#approveAnrechnungDetail-empfehlung-panel').slideUp('slow');

    })

    // Break Begruendung abgeben
    $('#approveAnrechnungDetail-begruendung-abbrechen').click(function(){
        $('#approveAnrechnungDetail-begruendung').val('');
        $('#approveAnrechnungDetail-begruendung-panel').slideUp('slow');

    })


});

var approveAnrechnungDetail = {
    setStatusAlertColor: function () {
        let status_kurzbz = $('#approveAnrechnung-status_kurzbz').data('status_kurzbz');

        switch (status_kurzbz) {
            case ANRECHNUNGSTATUS_APPROVED:
                $('#approveAnrechnung-status_kurzbz').closest('div').addClass('alert-success');
                break;
            case ANRECHNUNGSTATUS_REJECTED:
                $('#approveAnrechnung-status_kurzbz').closest('div').addClass('alert-danger');
                break;
            case '':
                $('#approveAnrechnung-status_kurzbz').closest('div').addClass('alert-info');
                break;
            default:
                $('#approveAnrechnung-status_kurzbz').closest('div').addClass('alert-warning');
        }
    },
    copyIntoTextarea: function(elem){

        // Find closest textarea
        let textarea = $(elem).closest('div').find('textarea');

        // Copy begruendung into textarea
        textarea.val($.trim($(elem).parent().text()));
    },
    formatEmpfehlungIsRequested: function(empfehlungAngefordertAm, statusBezeichnung) {
        $('#approveAnrechnungDetail-empfehlungDetail').children().addClass('hidden');
        $('#approveAnrechnungDetail-empfehlungDetail-empfehlungIsAngefordert').removeClass('hidden');
        $('#approveAnrechnungDetail-empfehlungDetail-empfehlungAngefordertAm').text(empfehlungAngefordertAm);
        $('#approveAnrechnung-status_kurzbz').text(statusBezeichnung);
        $('#request-recommendation').prop('disabled', true);
        $('#approve-anrechnung').prop('disabled', true);
        $('#reject-anrechnung').prop('disabled', true);
    },
    formatEmpfehlungIsTrue: function(empfehlungAm, emfehlungVon, statusBezeichnung){
        $('#approveAnrechnungDetail-empfehlungDetail').children().addClass('hidden');
        $('#approveAnrechnungDetail-empfehlungDetail-empfehlungIsTrue').removeClass('hidden');
        $('#approveAnrechnung-status_kurzbz').text(statusBezeichnung);
        $('#request-recommendation').prop('disabled', true);
        $('#approve-anrechnung').prop('disabled', true);
        $('#reject-anrechnung').prop('disabled', true);
    },
    formatEmpfehlungIsFalse: function(empfehlungAm, emfehlungVon, statusBezeichnung, begruendung){
        $('#approveAnrechnungDetail-empfehlungDetail').children().addClass('hidden');
        $('#approveAnrechnungDetail-empfehlungDetail-empfehlungIsFalse').removeClass('hidden');
        $('#approveAnrechnung-status_kurzbz').text(statusBezeichnung);
        $('#request-recommendation').prop('disabled', true);
        $('#approve-anrechnung').prop('disabled', true);
        $('#reject-anrechnung').prop('disabled', true);
        $('#approveAnrechnungDetail-empfehlungAm').text(empfehlungAm);
        $('#approveAnrechnungDetail-empfehlungVon').text(emfehlungVon);
        $('#approveAnrechnungDetail-empfehlungDetail-begruendung').text(begruendung);
    }
}