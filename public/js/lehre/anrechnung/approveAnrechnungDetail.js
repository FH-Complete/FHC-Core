const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';
const ANRECHNUNGSTATUS_APPROVED = 'approved';
const ANRECHNUNGSTATUS_REJECTED = 'rejected';



$(function(){

    const genehmigung_panel = $('#approveAnrechnungDetail-genehmigung-panel');
    const begruendung_panel = $('#approveAnrechnungDetail-begruendung-panel');

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

    // Init tooltips
    approveAnrechnungDetail.initTooltips();

    // Ask if Approve Anrechnungen
    $("#approveAnrechnungDetail-approve-anrechnung-ask").click(function(){

        begruendung_panel.css('display', 'none');

        if (genehmigung_panel.is(":hidden"))
        {
            // Show begruendung panel if is hidden
            genehmigung_panel.slideDown('slow');
            return;
        }
    });

    // Approve Anrechnungen
    $("#approveAnrechnungDetail-approve-anrechnung-confirm").click(function(){

        // Get form data
        let form_data = $('form').serializeArray();

        // Prepare data object for ajax call
        let data = {
            'data': [{
                'anrechnung_id' : form_data[0].value
            }]
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
                        approveAnrechnungDetail.formatGenehmigungIsPositiv(
                            data.retval[0].abgeschlossen_am,
                            data.retval[0].abgeschlossen_von,
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

    // Ask if Reject Anrechnungen
    $("#approveAnrechnungDetail-reject-anrechnung-ask").click(function(){

        genehmigung_panel.css('display', 'none');

        if (begruendung_panel.is(":hidden"))
        {
            // Show begruendung panel if is hidden
            begruendung_panel.slideDown('slow');
            return;
        }
    });

    // Reject Anrechnungen
    $("#approveAnrechnungDetail-reject-anrechnung-confirm").click(function(){

        let begruendung = $('#approveAnrechnungDetail-begruendung').val();

        // Check if begruendung is given
        if (!begruendung.trim()) // empty or white spaces only
        {
            FHC_DialogLib.alertInfo('Bitte tragen Sie eine Begründung ein.');
            return;
        }

        // Avoid form redirecting automatically
        event.preventDefault();

        // Get form data
        let form_data = $('form').serializeArray();

        // Prepare data object for ajax call
        let data = {
            'data': [{
                'anrechnung_id' : form_data[0].value,
                'begruendung'   : begruendung
            }]
        }

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
                        approveAnrechnungDetail.formatGenehmigungIsNegativ(
                            data.retval[0].abgeschlossen_am,
                            data.retval[0].abgeschlossen_von,
                            data.retval[0].status_bezeichnung,
                            begruendung
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

    // Request Recommendation for Anrechnungen
    $("#request-recommendation").click(function(){

        // Get form data
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

    // Break Genehmigung abgeben
    $('#approveAnrechnungDetail-genehmigung-abbrechen').click(function(){
        genehmigung_panel.slideUp('slow');

    })

    // Break Begruendung abgeben
    $('#approveAnrechnungDetail-begruendung-abbrechen').click(function(){
        $('#approveAnrechnungDetail-begruendung').val('');
        begruendung_panel.slideUp('slow');

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
    initTooltips: function (){
        $('[data-toggle="tooltip"]').tooltip({
                delay: { "show": 200, "hide": 200 },
                html: true
        }
        );
    },
    copyIntoTextarea: function(elem){

        // Find closest textarea
        let textarea = $(elem).closest('div').find('textarea');

        // Copy begruendung into textarea
        textarea.val($.trim($(elem).parent().find('span:first').text()));
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
    formatGenehmigungIsPositiv: function(abgeschlossenAm, abgeschlossenVon, statusBezeichnung){
        $('#approveAnrechnungDetail-genehmigungDetail').children().addClass('hidden');
        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsPositiv').removeClass('hidden');
        $('#approveAnrechnung-status_kurzbz').text(statusBezeichnung);
        $('#approveAnrechnung-status_kurzbz').closest('div').removeClass('alert-warning').addClass('alert-success');
        $('#approveAnrechnungDetail-abgeschlossenAm').text(abgeschlossenAm);
        $('#approveAnrechnungDetail-abgeschlossenVon').text(abgeschlossenVon);
        $('#request-recommendation').prop('disabled', true);
        $('#approve-anrechnung').prop('disabled', true);
        $('#reject-anrechnung').prop('disabled', true);
    },
    formatGenehmigungIsNegativ: function(abgeschlossenAm, abgeschlossenVon, statusBezeichnung, begruendung){
        $('#approveAnrechnungDetail-genehmigungDetail').children().addClass('hidden');
        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsNegativ').removeClass('hidden');
        $('#approveAnrechnung-status_kurzbz').text(statusBezeichnung);
        $('#approveAnrechnung-status_kurzbz').closest('div').removeClass('alert-warning').addClass('alert-danger');
        $('#approveAnrechnungDetail-abgeschlossenAm').text(abgeschlossenAm);
        $('#approveAnrechnungDetail-abgeschlossenVon').text(abgeschlossenVon);
        $('#approveAnrechnungDetail-genehmigungDetail-begruendung').text(begruendung);
        $('#request-recommendation').prop('disabled', true);
        $('#approve-anrechnung').prop('disabled', true);
        $('#reject-anrechnung').prop('disabled', true);
    }
}