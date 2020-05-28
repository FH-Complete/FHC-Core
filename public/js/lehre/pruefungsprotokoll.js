const CALLED_PATH = FHC_JS_DATA_STORAGE_OBJECT.called_path;

$("document").ready(function() {
    $("#saveProtocolBtn, #freigebenProtocolBtn").click(
        function() {
            var data = {
                abschlussbeurteilung_kurzbz: $("#abschlussbeurteilung_kurzbz").val(),
                protokoll: $("#protokoll").val(),
                uhrzeit: $("#pruefungsbeginn").val(),
                endezeit: $("#pruefungsende").val()
            }

            if ($(this).prop("id") === 'freigebenProtocolBtn')
            {
                data.freigabedatum = true;
                data.password = $("#password").val();
            }

            if ($("#verfCheck").prop('checked'))
            {
                var checkFields = Pruefungsprotokoll.checkFields(data, $("#verfCheck").prop('checked'));
                $("#protocolform td").removeClass('has-error');
                if (checkFields.length > 0)
                {
                    var errortext = '';
                    for (var i = 0; i < checkFields.length; i++)
                    {
                        var error = checkFields[i];
                        $.each(error, function(i, n)
                        {
                            console.log($("#"+i).closest('td'));
                           $("#"+i).closest('td').addClass('has-error');
                           if (errortext !== '')
                               errortext += '; ';
                           errortext += n;
                        });
                    }

                    FHC_DialogLib.alertError(errortext);
                    return;
                }
            }

            Pruefungsprotokoll.saveProtokoll($("#abschlusspruefung_id").val(),data);
        }
    )

    $("#verfCheck").change(
        function() { // if student not mentally and physically fit (checkbox), no form entry
            if ($(this).prop('checked'))
            {
                $("#abschlussbeurteilung_kurzbz, #pruefungsbeginn, #pruefungsende").prop('disabled', false);
                $("#abschlussbeurteilung_kurzbz").val($("#abschlussbeurteilung_kurzbz option").first().val());
            }
            else
            {
                $("#abschlussbeurteilung_kurzbz, #pruefungsbeginn, #pruefungsende").prop('disabled', true).val(null);
            }

            $("#pruefungsbeginn").val(null);
            $("#pruefungsende").val(null);

        }
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

    // -----------------------------------------------------------------------------------------------------------------
    // ajax calls
    saveProtokoll: function(abschlusspruefung_id, data)
    {
        FHC_AjaxClient.ajaxCallPost(
            CALLED_PATH + '/saveProtokoll',
            {
                abschlusspruefung_id: abschlusspruefung_id,
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
                            $("#freigegebenText").html('&nbsp;&nbsp;' + FHC_PhrasesLib.t("pruefungsprotokoll", "freigegebenAm") +
                                '&nbsp;' + dataresponse.freigabedatum)
                        }
                        FHC_DialogLib.alertSuccess("Prüfung erfolgreich gespeichert!");
                    }
                    else if(FHC_AjaxClient.isError(data))
                    {
                        FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
                    }
                },
                errorCallback: function() {
                    FHC_DialogLib.alertError("Fehler beim Speichern der Prüfung");
                },
                veilTimeout: 0
            }
        );
    },
    checkFields: function(data)
    {
        var errors =  [];

        if (data.abschlussbeurteilung_kurzbz == "")
            errors.push({"abschlussbeurteilung_kurzbz": "Abschlussbeurteilung darf nicht leer sein!"}); // TODO phrases

        var zeitregex = /^[0-2][0-9]:[0-5][0-9]$/;

        if (data.uhrzeit == "")
            errors.push({"pruefungsbeginn": "Beginnzeit darf nicht leer sein!"});
        else if(!zeitregex.test(data.uhrzeit))
            errors.push({"pruefungsbeginn": "Beginnzeit muss Format Stunden:Minuten haben!"});

        if (data.endezeit == "")
            errors.push({"pruefungsende": "Endzeit darf nicht leer sein!"});
        else if(!zeitregex.test(data.endezeit))
            errors.push({"pruefungsende": "Endzeit muss Format Stunden:Minuten haben!"});

        return errors;
    }
}
