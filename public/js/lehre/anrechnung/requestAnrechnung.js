const ANRECHNUNGSTATUS_APPROVED = 'approved';
const ANRECHNUNGSTATUS_REJECTED = 'rejected';
const CHAR_LENGTH125 = 125;
const CHAR_LENGTH150 = 150;
const CHAR_LENGTH500 = 500;
const CHAR_LENGTH1000 = 1000;

const COLOR_DANGER = '#f2dede';

$(function(){
    const uploadMaxFilesize = $('#requestAnrechnung-uploadfile').data('maxsize')  ; // in byte

    // Set status alert color
    requestAnrechnung.setStatusAlertColor();

    // Disable Form fields if Anrechnung was already applied
    requestAnrechnung.disableFormFieldsIfAntragIsApplied();

    // Check Bestaetigung checkbox if Anrechnung was already applied
    requestAnrechnung.markAsBestaetigtIfAntragIsApplied();

    // Init tooltips
    requestAnrechnung.initTooltips();

    // Alert message, if maximum ECTS exceeded
    requestAnrechnung.alertIfMaxEctsExceeded();

    // Alert message inside Begruendungsbox, if maximum ECTS exceeded
    requestAnrechnung.alertIfMaxEctsExceededInsideBegruendungsbox();

    // Set chars counter for textareas
    requestAnrechnung.setCharsCounter();

    // If Sperregrund exists: display Sperre panel, hide Status panel and disable all form elements
    requestAnrechnung.displaySperreIfHasSperregrund();


    $('#requestAnrechnung-form :input[name="begruendung"]').click(function(e){
        var ectsLv = parseFloat($('#ects').text());
        var sumEctsSchulisch = parseFloat($('#sumEctsSchulisch').text());
        var sumEctsBeruflich = parseFloat($('#sumEctsBeruflich').text());
        var begruendung_id = $(this).val();

        if ($(this).is(':checked'))
        {
            $('#sumEctsMsg').remove();

            // If Begründung is 'Hochschulzeugnis', return. They are accepted without limit.
            if (begruendung_id == 5)
            {
                return;
            }

            // If max ECTS is ecceeded
            if (begruendung_id == 4)
            {
                if ((sumEctsSchulisch + sumEctsBeruflich + ectsLv) > 90 ||
                    (sumEctsBeruflich + ectsLv) > 60
                )
                {
                    // Get ECTS Überschreitungs-message for berufliche Qualifikation
                    var msgBeiEctsUeberschreitung = requestAnrechnung.getMsgBeiEctsUeberschreitung(begruendung_id, ectsLv, sumEctsSchulisch, sumEctsBeruflich);

                    // Add to Checkbox text
                    $(this).closest('div').append(msgBeiEctsUeberschreitung);
                }
                 return;
            }
            else
            {
                if ((sumEctsSchulisch + sumEctsBeruflich + ectsLv) > 90 ||
                    (sumEctsSchulisch + ectsLv) > 60
                )
                {
                    // Get ECTS Überschreitungs-message for schulische Qualifikation
                    var msgBeiEctsUeberschreitung = requestAnrechnung.getMsgBeiEctsUeberschreitung(begruendung_id, ectsLv, sumEctsSchulisch, sumEctsBeruflich);

                    // Add to Checkbox text
                    $(this).closest('div').append(msgBeiEctsUeberschreitung);
                }
            }

        }
    })

    $('#requestAnrechnung-form').submit(function(e){

        // Avoid form redirecting automatically
        e.preventDefault();

        var fileInput = $('#requestAnrechnung-uploadfile');
        if (!requestAnrechnung.fileSizeOk(fileInput, uploadMaxFilesize)) // in byte
        {
            return FHC_DialogLib.alertWarning(FHC_PhrasesLib.t("ui", "errorDokumentZuGross"));
        }

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/apply",
            {
                anmerkung: this.anmerkung.value,
                begruendung: this.begruendung.value,
                lv_id: this.lv_id.value,
                studiensemester: this.studiensemester.value,
                begruendung_ects: this.begruendung_ects && this.begruendung_ects.value
                    ? this.begruendung_ects.value
                    : null,
                begruendung_lvinhalt: this.begruendung_lvinhalt && this.begruendung_lvinhalt.value
                    ? this.begruendung_lvinhalt.value
                    : null,
                bestaetigung: this.bestaetigung.value,
                uploadfile: this.uploadfile.files
            },
            {
                successCallback:function(data, textStatus, jqXHR){
                    if (FHC_AjaxClient.isError(data))
                    {
                        FHC_DialogLib.alertWarning(FHC_AjaxClient.getError(data));
                    }

                    if (FHC_AjaxClient.hasData(data))
                    {
                        requestAnrechnung.formatAnrechnungIsApplied(
                            data.retval.antragdatum,
                            data.retval.dms_id,
                            data.retval.filename
                        );

                        FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("global", "antragWurdeGestellt"));
                    }
                },
                errorCallback: function(jqXHR, textStatus, errorThrown){
                    FHC_DialogLib.alertWarning(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });
})

var requestAnrechnung = {
    setStatusAlertColor: function () {
        let status_kurzbz = $('#requestAnrechnung-status_kurzbz').data('status_kurzbz');

        switch (status_kurzbz) {
            case ANRECHNUNGSTATUS_APPROVED:
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('bg-success-subtle');
                break;
            case ANRECHNUNGSTATUS_REJECTED:
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('bg-danger-subtle');
                break;
            case '':
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('bg-info-subtle');
                $('#requestAnrechnung-status_kurzbz').text(FHC_PhrasesLib.t("ui", "neu"));
                break;
            default:
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('bg-warning-subtle');
                $('#requestAnrechnung-status_kurzbz').text(FHC_PhrasesLib.t("ui", "inBearbeitung"));
        }
    },
    disableFormFieldsIfAntragIsApplied: function(){
        let status_kurzbz = $('#requestAnrechnung-status_kurzbz').data('status_kurzbz');

        if (status_kurzbz != '')
        {
            // Disable all form elements
           requestAnrechnung.disableFormFields();
        }
    },
    markAsBestaetigtIfAntragIsApplied: function(){
        let status_kurzbz = $('#requestAnrechnung-status_kurzbz').data('status_kurzbz');

        if (status_kurzbz != '')
        {
            $("#requestAnrechnung-form :input[name='bestaetigung']").prop('checked', true);
        }
    },
    disableFormFields(){
        // Disable all form elements
        $("#requestAnrechnung-form :input").prop("disabled", true);
    },
    displaySperreIfHasSperregrund: function(){
        const anrechnung_id = $('#requestAnrechnung-sperre').data('anrechnung_id');
        const is_expired = $('#requestAnrechnung-sperre').data('expired');
        const is_blocked = $('#requestAnrechnung-sperre').data('blocked');

        // If Deadline is expired or is blocked by grades of LV, AND not already angerechnet
        if ((is_expired || is_blocked) && anrechnung_id == '')
        {
            // Hide status panel
            $('#requestAnrechnung-status').hide();

            // Show sperre panel
            $('#requestAnrechnung-sperre')
                .removeClass('visually-hidden')
                .html(function(){
                    let sperregrund = FHC_PhrasesLib.t('global', 'bearbeitungGesperrt') + ': ';

                    if (is_expired) {
                        sperregrund += FHC_PhrasesLib.t('anrechnung', 'deadlineUeberschritten');
                    }
                    else if (is_blocked){
                        sperregrund += FHC_PhrasesLib.t('anrechnung', 'benotungDerLV');
                    }
                    return "<span class='fw-bold'>"+ sperregrund + "</span>";
                })

            // Disable all form elements
            requestAnrechnung.disableFormFields();
        }
    },
    initTooltips: function (){
        $('[data-bs-toggle="tooltip"]').tooltip({
                delay: { "show": 200, "hide": 200 },
                html: true
            }
        );
    },
    setCharsCounter: function(){
        $('#requestAnrechnung-herkunftDerKenntnisse').keyup(function() {
            let length = CHAR_LENGTH125 - $(this).val().length;
            $('#requestAnrechnung-herkunftDerKenntnisse-charCounter').text(length);
        });

        if ($('#requestAnrechnung-begruendungEcts').length) {
            $('#requestAnrechnung-begruendungEcts').keyup(function () {
                let length = CHAR_LENGTH150 - $(this).val().length;
                $('#requestAnrechnung-begruendungEcts-charCounter').text(length);
            });
        }

        if ($('#requestAnrechnung-begruendungLvinhalt').length){
            $('#requestAnrechnung-begruendungLvinhalt').keyup(function() {
                let maxlength = CHAR_LENGTH1000 - $(this).val().length;
                $('#requestAnrechnung-begruendungLvinhalt-charCounterMax').text(maxlength);

                let minlength = CHAR_LENGTH500 - $(this).val().length;
                $('#requestAnrechnung-begruendungLvinhalt-charCounterMin').text(minlength);
            });
        }
    },
    formatAnrechnungIsApplied: function (antragdatum, dms_id, filename){
        $('#requestAnrechnung-antragdatum').text(antragdatum);
        $('#requestAnrechnung-status_kurzbz').text(FHC_PhrasesLib.t("ui", "inBearbeitung"));
        $('#requestAnrechnung-status_kurzbz').closest('div').addClass('bg-warning-subtle');

        // Display File-Downloadlink
        $('#requestAnrechnung-downloadDocLink')
            .removeClass('visually-hidden')
            .attr('href', 'RequestAnrechnung/download?dms_id=' + dms_id)
            .html(filename);

        // Disable all form elements
        $("#requestAnrechnung-form :input").prop("disabled", true);
    },
    fileSizeOk: function(fileInput, maxSize){

        if (fileInput.get(0).files.length){

            var fileSize = fileInput.get(0).files[0].size; // in bytes

            if (fileSize > maxSize)
            {
                return false;
            }

            return true;
        }
    },
    sumUpEcts: function(begruendung_id, ects, sumEctsSchulisch, sumEctsBeruflich){
        if (begruendung_id == 5)
        {
            return;
        }

        if (begruendung_id == 4)
        {
            $('#sumEctsBeruflich').text(parseFloat(sumEctsBeruflich) + parseFloat(ects));
        }
        else
        {
            $('#sumEctsSchulisch').text(parseFloat(sumEctsSchulisch) + parseFloat(ects));
        }

        $('#sumEctsTotal').text(parseFloat(sumEctsSchulisch) + parseFloat(sumEctsBeruflich) + parseFloat(ects));


    },
    alertIfMaxEctsExceeded: function(){

        if(
        
             (parseFloat($('#sumEctsSchulisch').text())) > 60 ||
             (parseFloat($('#sumEctsBeruflich').text())) > 60 ||
             (parseFloat($('#sumEctsSchulisch').text()) + parseFloat($('#sumEctsBeruflich').text())) > 90 
        )
        {
            $('#requestAnrechnung-maxEctsUeberschrittenMsg')
                .html("<span class='flex-fill fw-bold'>Die Höchstgrenze für Anrechnungen gem. § 12 Abs. 3 Fachhochschulgesetz ist überschritten. </span><i class='mx-4 fa fa-lg fa-info-circle'></i>")
                .addClass('bg-danger-subtle')
                .tooltip({
                    title: FHC_PhrasesLib.t("anrechnung", "anrechnungEctsTooltipTextBeiUeberschreitung"),
                    placement: 'right',
                    html: true
                });
        }
    },
    alertIfMaxEctsExceededInsideBegruendungsbox: function(){
        let status_kurzbz = $('#requestAnrechnung-status_kurzbz').data('status_kurzbz');
                    
   
        if (status_kurzbz != ' ' && status_kurzbz != ANRECHNUNGSTATUS_APPROVED)
        {
            var ectsLv = parseFloat($('#ects').text());
            var sumEctsSchulisch = parseFloat($('#sumEctsSchulisch').text());
            var sumEctsBeruflich = parseFloat($('#sumEctsBeruflich').text());
            var begruendung_id = $('#requestAnrechnung-form :input[name="begruendung"]:checked').val();

            // If Begründung is 'Hochschulzeugnis', return. They are accepted without limit.
            if (begruendung_id == 5)
            {
                return;
            }
            
            // If max ECTS is ecceeded
            if (begruendung_id == 4)
            {
                if ((sumEctsSchulisch + sumEctsBeruflich + ectsLv) > 90 ||
                    (sumEctsBeruflich + ectsLv) > 60
                )
                {
                    // Get ECTS Überschreitungs-message, depending on schulische or berufliche Qualifikation
                    var msgBeiEctsUeberschreitung = requestAnrechnung.getMsgBeiEctsUeberschreitung(begruendung_id, ectsLv, sumEctsSchulisch, sumEctsBeruflich);

                    // Add to Checkbox text
                    $('#requestAnrechnung-form :input[name="begruendung"]:checked').closest('div').append(msgBeiEctsUeberschreitung);
                }
            }
            else
            {
                if ((sumEctsSchulisch + sumEctsBeruflich + ectsLv) > 90 ||
                    (sumEctsSchulisch + ectsLv) > 60
                )
                {
                    // Get ECTS Überschreitungs-message, depending on schulische or berufliche Qualifikation
                    var msgBeiEctsUeberschreitung = requestAnrechnung.getMsgBeiEctsUeberschreitung(begruendung_id, ectsLv, sumEctsSchulisch, sumEctsBeruflich);

                    // Add to Checkbox text
                    $('#requestAnrechnung-form :input[name="begruendung"]:checked').closest('div').append(msgBeiEctsUeberschreitung);
                }
            }
        }
    },
    getMsgBeiEctsUeberschreitung: function(begruendung_id, ects, sumEctsSchulisch, sumEctsBeruflich){

        const phraseUsed = '<span class="d-block">Die Höchstgrenze für Anrechnungen gem. § 12 Abs. 3 Fachhochschulgesetz wird überschritten.</span><span class=" fw-bold">Bisherige ECTS + ECTS dieser LV: Total: {0} [ Schulisch: {1}  | Beruflich: {2}  ]</span>&emsp;';

        return $('<div class="p-1" id="sumEctsMsg"></div>')
            .html(phraseUsed) // schulisch
            .append('<i class="fa fa-lg fa-info-circle"></i>')
            .addClass('bg-danger-subtle')
            .tooltip({
                title: FHC_PhrasesLib.t("anrechnung", "anrechnungEctsTooltipTextBeiUeberschreitung"),
                placement: 'right',
                html: true
            });
    }
}