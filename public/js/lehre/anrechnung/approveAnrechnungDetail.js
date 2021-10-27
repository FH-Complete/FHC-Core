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

    // Set Empfehlungstext
    approveAnrechnungDetail.setEmpfehlungstext();

    // Init tooltips
    approveAnrechnungDetail.initTooltips();

    // Ask if Approve Anrechnungen
    $("#approveAnrechnungDetail-approve-anrechnung-ask").click(function(){

        begruendung_panel.css('display', 'none');

        if (genehmigung_panel.is(":hidden"))
        {
            // Show genehmigung panel if is hidden
            genehmigung_panel.css('display', 'block');
            genehmigung_panel.slideDown(400, function() {
                $('html, body').animate({
                    scrollTop: genehmigung_panel.offset().top // Move genehmigung panel bottom up to be visible within window screen
                }, 400);
            });

            return;
        }
    });

    // Approve Anrechnungen
    $("#approveAnrechnungDetail-approve-anrechnung-confirm").click(function(e){

        // Avoid bubbling click event to sibling break button
        e.stopImmediatePropagation();

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
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
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
            begruendung_panel.css('display', 'block');
            begruendung_panel.slideDown(400, function() {
                $('html, body').animate({
                    scrollTop: begruendung_panel.offset().top // Move begruendung panel bottom up to be visible within window screen
                }, 400);
            });

            return;
        }
    });

    // Reject Anrechnungen
    $("#approveAnrechnungDetail-reject-anrechnung-confirm").click(function(e){

        // Avoid bubbling click event to sibling break button
        e.stopImmediatePropagation();

        let begruendung = $('#approveAnrechnungDetail-begruendung').val();

        // Check if begruendung is given
        if (!begruendung.trim()) // empty or white spaces only
        {
            FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "bitteBegruendungAngeben"));
            return;
        }

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
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });

    // Request Recommendation for Anrechnungen
    $("#approveAnrechnungDetail-request-recommendation").click(function(e){

        e.preventDefault();

        // Get form data
        let form_data = $('#form-empfehlung').serializeArray();


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
                            data.retval[0].status_bezeichnung,
                            data.retval[0].empfehlungsanfrageAm,
                            data.retval[0].empfehlungsanfrageAn
                        );
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });

    // Withdraw approvement or rejection
    $("#approveAnrechnungDetail-withdraw-anrechnung-approvement").click(function(){

        if(!confirm(FHC_PhrasesLib.t("anrechnung", "genehmigungAblehnungWirklichZuruecknehmen")))
        {
            return;
        }

        // Get form data
        let form_data = $('form').serializeArray();

        // Prepare data object for ajax call
        let data = {
            'anrechnung_id' : form_data[0].value
        };

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/withdraw",
            data,
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    console.log(data);
                    if (data.error && data.retval != null)
                    {
                        console.log('inside error');
                        // Print error message
                        FHC_DialogLib.alertWarning(data.retval);
                    }

                    if (!data.error && data.retval != null)
                    {
                        console.log('inside success');
                        approveAnrechnungDetail.formatGenehmigungIsWithdrawed(
                            data.retval.status_bezeichnung
                        );

                        FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("anrechnung", "erfolgreichZurueckgenommen"));

                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });

    // Withdraw request for recommendation
    $("#approveAnrechnungDetail-withdraw-request-recommedation").click(function(e){

        e.preventDefault();

        if(!confirm(FHC_PhrasesLib.t("anrechnung", "empfehlungsanforderungWirklichZuruecknehmen")))
        {
            return;
        }

        // Get form data
        let form_data = $('#form-empfehlung').serializeArray();

        // Prepare data object for ajax call
        let data = {
            'anrechnung_id' : form_data[0].value
        };

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/withdrawRequestRecommendation",
            data,
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    console.log(data);
                    if (data.error && data.retval != null)
                    {
                        // Print error message
                        FHC_DialogLib.alertWarning(data.retval);
                    }

                    if (!data.error && data.retval != null)
                    {
                        approveAnrechnungDetail.formatEmpfehlungIsWithdrawed(
                            data.retval.status_bezeichnung
                        );

                        FHC_DialogLib.alertSuccess(
                            FHC_PhrasesLib.t("anrechnung", "erfolgreichZurueckgenommen")
                        );

                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });

    $('#form-empfehlungNotiz').submit(function(e){

        e.preventDefault();

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/saveEmpfehlungsNotiz",
            {
                anrechnung_id: this.anrechnung_id.value,
                notiz_id: this.notiz_id.value,
                empfehlung_text: this.empfehlungText.value
            },
            {
                successCallback: function (data){

                    if (FHC_AjaxClient.isError(data)){

                        // Print error message
                        FHC_DialogLib.alertWarning(FHC_AjaxClient.getError(data));
                    }

                    if (FHC_AjaxClient.hasData(data)){

                        // Print success message
                        FHC_DialogLib.alertSuccess((FHC_AjaxClient.getData(data)))
                    }
                },
                errorCallback(){
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        )
    })

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

        begruendung_panel.slideUp('slow');

    })


});

var approveAnrechnungDetail = {
    setStatusAlertColor: function () {
        let status_kurzbz = $('#approveAnrechnungDetail-status_kurzbz').data('status_kurzbz');

        switch (status_kurzbz) {
            case ANRECHNUNGSTATUS_APPROVED:
                $('#approveAnrechnungDetail-status_kurzbz').closest('div').addClass('alert-success');
                break;
            case ANRECHNUNGSTATUS_REJECTED:
                $('#approveAnrechnungDetail-status_kurzbz').closest('div').addClass('alert-danger');
                break;
            case '':
                $('#approveAnrechnungDetail-status_kurzbz').closest('div').addClass('alert-info');
                break;
            default:
                $('#approveAnrechnungDetail-status_kurzbz').closest('div').addClass('alert-warning');
        }
    },
    setEmpfehlungstext: function () {
        let empfehlung = $('#approveAnrechnungDetail-empfehlung').data('empfehlung');

        switch (empfehlung) {
            case true:
                $('#approveAnrechnungDetail-empfehlungDetail-empfehlung')
                    .addClass('text-success')
                    .html(FHC_PhrasesLib.t("anrechnung", "empfehlungPositivConfirmed"));
                break;
            case false:
                $('#approveAnrechnungDetail-empfehlungDetail-empfehlung')
                    .addClass('text-danger')
                    .html(FHC_PhrasesLib.t("anrechnung", "empfehlungNegativConfirmed"));
                break;
            default:
                $('#approveAnrechnungDetail-empfehlungDetail-empfehlung').html('-');
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

        if (elem.id.length && elem.id == 'empfehlungstextUebernehmen')
        {
            // Copy Empfehlungstext into textarea
            textarea.val($.trim($('#approveAnrechnungDetail-empfehlungDetail-begruendung').text()));
            return;
        }
        else
        {
            // Copy begruendung into textarea
            textarea.val($.trim($(elem).parent().find('span:first').text()));
        }
    },
    formatEmpfehlungIsRequested: function(statusBezeichnung, empfehlungsanfrageAm, empfehlungsanfrageAn) {
        $('#approveAnrechnungDetail-empfehlungDetail-empfehlungsanfrageAm').html(empfehlungsanfrageAm);
        $('#approveAnrechnungDetail-empfehlungDetail-empfehlungsanfrageAn').html(empfehlungsanfrageAn);
        $('#approveAnrechnungDetail-status_kurzbz').text(statusBezeichnung);
        $('#approveAnrechnungDetail-request-recommendation').prop('disabled', true);
        $('#approveAnrechnungDetail-approve-anrechnung-ask').prop('disabled', true);
        $('#approveAnrechnungDetail-reject-anrechnung-ask').prop('disabled', true);
        $('#approveAnrechnungDetail-withdraw-request-recommedation').removeClass('hidden');
    },
    formatGenehmigungIsPositiv: function(abgeschlossenAm, abgeschlossenVon, statusBezeichnung){
        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsNull').addClass('hidden');
        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsNegativ').addClass('hidden');
        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsPositiv').removeClass('hidden');
        $('#approveAnrechnungDetail-status_kurzbz').text(statusBezeichnung);
        $('#approveAnrechnungDetail-status_kurzbz').closest('div').removeClass('alert-warning').addClass('alert-success');
        $('#approveAnrechnungDetail-abgeschlossenAm').text(abgeschlossenAm);
        $('#approveAnrechnungDetail-abgeschlossenVon').text(abgeschlossenVon);
        $('#approveAnrechnungDetail-request-recommendation').prop('disabled', true);
        $('#approveAnrechnungDetail-approve-anrechnung-ask').prop('disabled', true);
        $('#approveAnrechnungDetail-reject-anrechnung-ask').prop('disabled', true);

        // Show button to withdraw approval
        $('#approveAnrechnungDetail-withdraw-anrechnung-approvement').removeClass('hidden');
    },
    formatGenehmigungIsNegativ: function(abgeschlossenAm, abgeschlossenVon, statusBezeichnung, begruendung){
        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsNull').addClass('hidden');
        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsPositiv').addClass('hidden');
        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsNegativ').removeClass('hidden');
        $('#approveAnrechnungDetail-status_kurzbz').text(statusBezeichnung);
        $('#approveAnrechnungDetail-status_kurzbz').closest('div').removeClass('alert-warning').addClass('alert-danger');
        $('#approveAnrechnungDetail-abgeschlossenAm').text(abgeschlossenAm);
        $('#approveAnrechnungDetail-abgeschlossenVon').text(abgeschlossenVon);
        $('#approveAnrechnungDetail-genehmigungDetail-begruendung').text(begruendung);
        $('#approveAnrechnungDetail-request-recommendation').prop('disabled', true);
        $('#approveAnrechnungDetail-approve-anrechnung-ask').prop('disabled', true);
        $('#approveAnrechnungDetail-reject-anrechnung-ask').prop('disabled', true);

        // Show button to withdraw approval
        $('#approveAnrechnungDetail-withdraw-anrechnung-approvement').removeClass('hidden');
    },
    formatGenehmigungIsWithdrawed: function (statusBezeichnung){
        let empfehlung = $('#approveAnrechnungDetail-empfehlung').data('empfehlung'); // null / false / true

        $('#approveAnrechnungDetail-status_kurzbz').text(statusBezeichnung);
        $('#approveAnrechnungDetail-status_kurzbz').closest('div').removeClass('alert-danger').removeClass('alert-success');
        $('#approveAnrechnungDetail-status_kurzbz').closest('div').addClass('alert-warning');

        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsNull').removeClass('hidden');
        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsPositiv').addClass('hidden');
        $('#approveAnrechnungDetail-genehmigungDetail-genehmigungIsNegativ').addClass('hidden');

        $('#approveAnrechnungDetail-abgeschlossenAm').text('-');
        $('#approveAnrechnungDetail-abgeschlossenVon').text('-');

        // Only enable recommendation button again if no recommendation was submitted until now
        if (empfehlung === null)
        {
            $('#approveAnrechnungDetail-request-recommendation').prop('disabled', false);
        }
        $('#approveAnrechnungDetail-approve-anrechnung-ask').prop('disabled', false);
        $('#approveAnrechnungDetail-reject-anrechnung-ask').prop('disabled', false);
        // Hide button to withdraw approval
        $('#approveAnrechnungDetail-withdraw-anrechnung-approvement').addClass('hidden');
    },
    formatEmpfehlungIsWithdrawed: function (statusBezeichnung){
        $('#approveAnrechnungDetail-status_kurzbz').text(statusBezeichnung);

        $('#approveAnrechnungDetail-empfehlungDetail-empfehlungIsNull').removeClass('hidden');
        $('#approveAnrechnungDetail-empfehlungDetail-empfehlungsanfrageAm').html('-');
        $('#approveAnrechnungDetail-empfehlungDetail-empfehlungsanfrageAn').html('-');

        $('#approveAnrechnungDetail-request-recommendation').prop('disabled', false);
        $('#approveAnrechnungDetail-approve-anrechnung-ask').prop('disabled', false);
        $('#approveAnrechnungDetail-reject-anrechnung-ask').prop('disabled', false);
        // Hide button to withdraw approval
        $('#approveAnrechnungDetail-withdraw-request-recommedation').addClass('hidden');
    }
}