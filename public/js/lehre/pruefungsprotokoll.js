const CALLED_PATH = FHC_JS_DATA_STORAGE_OBJECT.called_path;

$("document").ready(function() {
    // if no abschlussbeurteilung is not filled out, new formular -> verfassungscheck is by default not checked.
    // If abschlussbeurteilung is filled out -> already graded, verfassungscheck is checked.
    Pruefungsprotokoll.abschlussbeurteilung_kurzbz = $("#abschlussbeurteilung_kurzbz").val();
    if (Pruefungsprotokoll.abschlussbeurteilung_kurzbz != '')
        $("#verfCheck").prop('checked', true);
    Pruefungsprotokoll.checkVerfassung();

    $("#saveProtocolBtn, #freigebenProtocolBtn").click(
        function() {

            var freigebendata = {
                freigeben:  false,
                password: null
            }

            var data = {
                abschlussbeurteilung_kurzbz: $("#abschlussbeurteilung_kurzbz").val(),
                protokoll: $("#protokoll").val(),
                uhrzeit: $("#pruefungsbeginn").val(),
                endezeit: $("#pruefungsende").val()
            }

            if ($(this).prop("id") === 'freigebenProtocolBtn')
            {
                freigebendata.freigeben = true;
                freigebendata.password = $("#password").val();
            }

            var checkFields = Pruefungsprotokoll.checkFields(data, freigebendata, $("#verfCheck").prop('checked'));
            $("#protocolform td").removeClass('has-error');
            if (checkFields.length > 0)
            {
                var errortext = '';
                for (var i = 0; i < checkFields.length; i++)
                {
                    var error = checkFields[i];
                    $.each(error, function(i, n)
                    {
                       $("#"+i).closest('td').addClass('has-error');
                       if (errortext !== '')
                           errortext += '; ';
                       errortext += n;
                    });
                }

                FHC_DialogLib.alertError(errortext);
                return;
            }

            Pruefungsprotokoll.saveProtokoll($("#abschlusspruefung_id").val(), freigebendata, data);
        }
    )

    $("#verfCheck").change(
        Pruefungsprotokoll.checkVerfassung
    );

    $( ".timepicker" ).timepicker({
        showPeriodLabels: false,
        hours: {starts: 7,ends: 22},
        timeFormat: 'hh:mm',
        defaultTime: '',
        hourText: FHC_PhrasesLib.t("ui", "stunde"),
        minuteText: FHC_PhrasesLib.t("ui", "minute"),
        rows: 4
    });
})

var Pruefungsprotokoll = {
    abschlussbeurteilung_kurzbz: '',
    checkVerfassung: function()
    {
        // if student not mentally and physically fit (checkbox), no grade can be set
        if ($("#verfCheck").prop('checked'))
        {
            $("#abschlussbeurteilung_kurzbz").prop('disabled', false).val(Pruefungsprotokoll.abschlussbeurteilung_kurzbz);
            $("#verfNotice").html("");
        }
        else
        {
            $("#abschlussbeurteilung_kurzbz").prop('disabled', true).val(null);
            $("#verfNotice").html(FHC_PhrasesLib.t("abschlusspruefung", "verfNotice"));
        }
    },
    checkFields: function(data, freigebendata, verfChecked)
    {
        var errors =  [];

        if (data.abschlussbeurteilung_kurzbz == "" && freigebendata.freigeben === true && verfChecked)
            errors.push({"abschlussbeurteilung_kurzbz": FHC_PhrasesLib.t("abschlusspruefung", "abschlussbeurteilungLeer")});

        var zeitregex = /^[0-2][0-9]:[0-5][0-9]$/;

        if (data.uhrzeit == "")
        {
            if (verfChecked)
                errors.push({"pruefungsbeginn": FHC_PhrasesLib.t("abschlusspruefung", "beginnzeitLeer")});
        }
        else if(!zeitregex.test(data.uhrzeit))
            errors.push({"pruefungsbeginn": FHC_PhrasesLib.t("abschlusspruefung", "beginnzeitFormatError")});

        if (data.endezeit == "")
        {
            if (verfChecked)
                errors.push({"pruefungsende": FHC_PhrasesLib.t("abschlusspruefung", "endezeitLeer")});
        }
        else if(!zeitregex.test(data.endezeit))
            errors.push({"pruefungsende": FHC_PhrasesLib.t("abschlusspruefung", "endezeitFormatError")});

        if (data.uhrzeit > data.endezeit && data.endezeit != "" && data.uhrzeit != "")
            errors.push({"pruefungsende": FHC_PhrasesLib.t("abschlusspruefung", "endezeitBeforeError")});

        return errors;
    },
    // ajax calls
    // -----------------------------------------------------------------------------------------------------------------
    saveProtokoll: function(abschlusspruefung_id, freigeben, data)
    {
        FHC_AjaxClient.ajaxCallPost(
            CALLED_PATH + '/saveProtokoll',
            {
                abschlusspruefung_id: abschlusspruefung_id,
                freigebendata: freigeben,
                protocoldata: data
            },
            {
                successCallback: function(data, textStatus, jqXHR) {
                    if (FHC_AjaxClient.hasData(data))
                    {
                        var dataresponse = FHC_AjaxClient.getData(data);
                        if (dataresponse.freigabedatum)
                        {
                            $("#saveProtocolBtn").prop("disabled", true);
                            $("#freigegebenText").html('&nbsp;&nbsp;' + FHC_PhrasesLib.t("abschlusspruefung", "freigegebenAm") +
                                '&nbsp;' + dataresponse.freigabedatum)
                        }
                        FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("abschlusspruefung", "pruefungGespeichert"));
                    }
                    else if(FHC_AjaxClient.isError(data))
                    {
                        FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
                    }
                },
                errorCallback: function() {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("abschlusspruefung", "pruefungSpeichernFehler"));
                },
                veilTimeout: 0
            }
        );
    }
}
