const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
const ANRECHNUNGSTATUS_PROGRESSED_BY_KF = 'inProgressKF';
const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';
const ANRECHNUNGSTATUS_APPROVED = 'approved';
const ANRECHNUNGSTATUS_REJECTED = 'rejected';



$(function(){

    const empfehlung_panel = $('#reviewAnrechnungDetail-empfehlung-panel');
    const begruendung_panel = $('#reviewAnrechnungDetail-begruendung-panel');

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
    reviewAnrechnung.setStatusAlertColor();

    // Init tooltips
    reviewAnrechnung.initTooltips();

    // Copy Begruendung into textarea
    $(".btn-copyIntoTextarea").click(function(){
           reviewAnrechnung.copyIntoTextarea(this);
    })

    // Ask if Recommend Anrechnung
    $("#reviewAnrechnungDetail-recommend-anrechnung-ask").click(function(){

        begruendung_panel.css('display', 'none');

        if (empfehlung_panel.is(":hidden"))
        {
            // Show begruendung panel if is hidden
            empfehlung_panel.slideDown('slow');
            return;
        }
    });

    // Recommend Anrechnung
    $("#reviewAnrechnungDetail-recommend-anrechnung-confirm").click(function(){

        // Get form data
        let form_data = $('form').serializeArray();

        // Prepare data object for ajax call
        let data = {
            'data': [{
                'anrechnung_id' : form_data[0].value
            }]
        };

        // Hide begruendung panel again
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
                        reviewAnrechnung.formatEmpfehlungIsTrue(
                            data.retval[0].empfehlung_am,
                            data.retval[0].empfehlung_von,
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

    // Ask if Dont recommend Anrechnung
    $("#reviewAnrechnungDetail-dont-recommend-anrechnung-ask").click(function(){

        empfehlung_panel.css('display', 'none');

        if (begruendung_panel.is(":hidden"))
        {
            // Show begruendung panel if is hidden
            begruendung_panel.slideDown('slow');
            return;
        }
    });

    // Dont recommend Anrechnung
    $("#reviewAnrechnungDetail-dont-recommend-anrechnung-confirm").click(function(){

        let begruendung = $('#reviewAnrechnungDetail-begruendung').val();

        // Check if begruendung is given
        if (!begruendung.trim()) // empty or white spaces only
        {
            FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "bitteBegruendungAngeben"));
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
                        reviewAnrechnung.formatEmpfehlungIsFalse(
                            data.retval[0].empfehlung_am,
                            data.retval[0].empfehlung_von,
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

    // Break Empfehlung abgeben
    $('#reviewAnrechnungDetail-empfehlung-abbrechen').click(function(){
        empfehlung_panel.slideUp('slow');

    })

    // Break Begruendung abgeben
    $('#reviewAnrechnungDetail-begruendung-abbrechen').click(function(){
        $('#reviewAnrechnungDetail-begruendung').val('');
        begruendung_panel.slideUp('slow');
    })


});

var reviewAnrechnung = {
    setStatusAlertColor: function () {
        let status_kurzbz = $('#reviewAnrechnungDetail-status_kurzbz').data('status_kurzbz');

        switch (status_kurzbz) {
            case ANRECHNUNGSTATUS_APPROVED:
                $('#reviewAnrechnungDetail-status_kurzbz').closest('div').addClass('alert-success');
                break;
            case ANRECHNUNGSTATUS_REJECTED:
                $('#reviewAnrechnungDetail-status_kurzbz').closest('div').addClass('alert-danger');
                break;
            case '':
                $('#reviewAnrechnungDetail-status_kurzbz').closest('div').addClass('alert-info');
                break;
            default:
                $('#reviewAnrechnungDetail-status_kurzbz').closest('div').addClass('alert-warning');
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
    formatEmpfehlungIsTrue: function(empfehlungAm, emfehlungVon, statusBezeichnung){
        $('#reviewAnrechnungDetail-empfehlungDetail').children().addClass('hidden');
        $('#reviewAnrechnungDetail-empfehlungDetail-empfehlungIsTrue').removeClass('hidden');
        $('#reviewAnrechnungDetail-status_kurzbz').text(statusBezeichnung);
        $('#reviewAnrechnungDetail-recommend-anrechnung-ask').prop('disabled', true);
        $('#reviewAnrechnungDetail-dont-recommend-anrechnung-ask').prop('disabled', true);
        $('#reviewAnrechnungDetail-empfehlungAm').text(empfehlungAm);
        $('#reviewAnrechnungDetail-empfehlungVon').text(emfehlungVon);
    },
    formatEmpfehlungIsFalse: function(empfehlungAm, emfehlungVon, statusBezeichnung, begruendung){
        $('#reviewAnrechnungDetail-empfehlungDetail').children().addClass('hidden');
        $('#reviewAnrechnungDetail-empfehlungDetail-empfehlungIsFalse').removeClass('hidden');
        $('#reviewAnrechnungDetail-status_kurzbz').text(statusBezeichnung);
        $('#reviewAnrechnungDetail-recommend-anrechnung-ask').prop('disabled', true);
        $('#reviewAnrechnungDetail-dont-recommend-anrechnung-ask').prop('disabled', true);
        $('#reviewAnrechnungDetail-empfehlungAm').text(empfehlungAm);
        $('#reviewAnrechnungDetail-empfehlungVon').text(emfehlungVon);
        $('#reviewAnrechnungDetail-empfehlungDetail-begruendung').text(begruendung);
    }
}