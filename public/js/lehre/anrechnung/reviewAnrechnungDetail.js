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

    // Set Empfehlungstext
    reviewAnrechnung.setEmpfehlungstext();

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
            empfehlung_panel.slideDown(400, function() {
                $('html, body').animate({
                    scrollTop: empfehlung_panel.offset().top // Move empfehlung panel bottom up to be visible within window screen
                }, 400);
            });
            return;
        }
    });

    // Recommend Anrechnung
    $("#reviewAnrechnungDetail-recommend-anrechnung-confirm").click(function(e){

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
            begruendung_panel.slideDown(400, function() {
                $('html, body').animate({
                    scrollTop: begruendung_panel.offset().top // Move begruendung panel bottom up to be visible within window screen
                }, 400);
            });
            return;
        }
    });

    // Dont recommend Anrechnung
    $("#reviewAnrechnungDetail-dont-recommend-anrechnung-confirm").click(function(e){

        // Avoid bubbling click event to sibling break button
        e.stopImmediatePropagation();

        let begruendung = $('#reviewAnrechnungDetail-begruendung').val();

        // Check if begruendung is given
        if (!begruendung.trim()) // empty or white spaces only
        {
            FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "bitteBegruendungAngeben"));
            return;
        }

        // Check if forgot to fulfill begruendung
        if (begruendung.trim().endsWith('weil') || begruendung.endsWith('because of'))
        {
            FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "bitteBegruendungVervollstaendigen"));
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
    setEmpfehlungstext: function () {
        let empfehlung = $('#reviewAnrechnungDetail-empfehlung').data('empfehlung');

        switch (empfehlung) {
            case true:
                $('#reviewAnrechnungDetail-empfehlungDetail-empfehlung')
                    .addClass('text-success')
                    .html(FHC_PhrasesLib.t("anrechnung", "empfehlungPositivConfirmed"));
                break;
            case false:
                $('#reviewAnrechnungDetail-empfehlungDetail-empfehlung')
                    .addClass('text-danger')
                    .html(FHC_PhrasesLib.t("anrechnung", "empfehlungNegativConfirmed"));
                break;
            default:
                $('#reviewAnrechnungDetail-empfehlungDetail-empfehlung').html('-');
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

        // Find Begruendung span
        let textspan = $(elem).parent().find('span:first');

        // Get Begruendung
        let begruendung = textspan.text();

        // Check if Begruendung has helptext
        let hasHelptext = textspan.children('span #helpTxtBegruendungErgaenzen').length > 0;

        if (hasHelptext)
        {
            let helptext = textspan.children('span #helpTxtBegruendungErgaenzen').text();

            // Remove helptext
            begruendung = begruendung.replace(helptext, '');
        }

        // Copy begruendung into textarea and set focus
        textarea.val($.trim(begruendung)).focus();

    },
    formatEmpfehlungIsTrue: function(empfehlungAm, emfehlungVon, statusBezeichnung){
        $('#reviewAnrechnungDetail-status_kurzbz').text(statusBezeichnung);
        $('#reviewAnrechnungDetail-recommend-anrechnung-ask').prop('disabled', true);
        $('#reviewAnrechnungDetail-dont-recommend-anrechnung-ask').prop('disabled', true);
        $('#reviewAnrechnungDetail-empfehlungAm').text(empfehlungAm);
        $('#reviewAnrechnungDetail-empfehlungVon').text(emfehlungVon);
        $('#reviewAnrechnungDetail-empfehlungDetail-empfehlung')
            .addClass('text-success')
            .html(FHC_PhrasesLib.t("anrechnung", "empfehlungPositivConfirmed"));
        $('#reviewAnrechnungDetail-empfehlungDetail-empfehlungAm').html(empfehlungAm);
        $('#reviewAnrechnungDetail-empfehlungDetail-empfehlungVon').html(emfehlungVon);
    },
    formatEmpfehlungIsFalse: function(empfehlungAm, emfehlungVon, statusBezeichnung, begruendung){
        $('#reviewAnrechnungDetail-status_kurzbz').text(statusBezeichnung);
        $('#reviewAnrechnungDetail-recommend-anrechnung-ask').prop('disabled', true);
        $('#reviewAnrechnungDetail-dont-recommend-anrechnung-ask').prop('disabled', true);
        $('#reviewAnrechnungDetail-empfehlungAm').text(empfehlungAm);
        $('#reviewAnrechnungDetail-empfehlungVon').text(emfehlungVon);
        $('#reviewAnrechnungDetail-empfehlungDetail-empfehlung')
            .addClass('text-danger')
            .html(FHC_PhrasesLib.t("anrechnung", "empfehlungNegativConfirmed"));
        $('#reviewAnrechnungDetail-empfehlungDetail-empfehlungAm').html(empfehlungAm);
        $('#reviewAnrechnungDetail-empfehlungDetail-empfehlungVon').html(emfehlungVon);
        $('#reviewAnrechnungDetail-empfehlungDetail-begruendung').text(begruendung);
    }
}