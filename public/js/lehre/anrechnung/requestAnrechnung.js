const ANRECHNUNGSTATUS_APPROVED = 'approved';
const ANRECHNUNGSTATUS_REJECTED = 'rejected';
const HERKUNFT_DER_KENNTNISSE_MAX_LENGTH = 125;

$(function(){
    // Set status alert color
    requestAnrechnung.setStatusAlertColor();

    // Disable Form fields if Anrechnung was already applied
    requestAnrechnung.disableFormFieldsIfAntragIsApplied();

    // Check Bestaetigung checkbox if Anrechnung was already applied
    requestAnrechnung.markAsBestaetigtIfAntragIsApplied();

    // Init tooltips
    requestAnrechnung.initTooltips();

    // Set chars counter for textarea 'Herkunft der Kenntnisse'
    requestAnrechnung.setCharsCounter();

    // If Sperregrund exists: display Sperre panel, hide Status panel and disable all form elements
    requestAnrechnung.displaySperreIfHasSperregrund();

    $('#requestAnrechnung-form').submit(function(e){

        // Avoid form redirecting automatically
        e.preventDefault();

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/apply",
            {
                anmerkung: this.anmerkung.value,
                begruendung: this.begruendung.value,
                lv_id: this.lv_id.value,
                studiensemester: this.studiensemester.value,
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
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('alert-success');
                break;
            case ANRECHNUNGSTATUS_REJECTED:
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('alert-danger');
                break;
            case '':
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('alert-info');
                $('#requestAnrechnung-status_kurzbz').text(FHC_PhrasesLib.t("ui", "neu"));
                break;
            default:
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('alert-warning');
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
                .removeClass('hidden')
                .html(function(){
                    let sperregrund = FHC_PhrasesLib.t('global', 'bearbeitungGesperrt') + ': ';

                    if (is_expired) {
                        sperregrund += FHC_PhrasesLib.t('anrechnung', 'deadlineUeberschritten');
                    }
                    else if (is_blocked){
                        sperregrund += FHC_PhrasesLib.t('anrechnung', 'benotungDerLV');
                    }
                    return "<b>"+ sperregrund + "</b>";
                })

            // Disable all form elements
            requestAnrechnung.disableFormFields();
        }
    },
    initTooltips: function (){
        $('[data-toggle="tooltip"]').tooltip({
                delay: { "show": 200, "hide": 200 },
                html: true
            }
        );
    },
    setCharsCounter: function(){
        $('#requestAnrechnung-herkunftDerKenntnisse').keyup(function() {

            let length = HERKUNFT_DER_KENNTNISSE_MAX_LENGTH - $(this).val().length;

            $('#requestAnrechnung-herkunftDerKenntnisse-charCounter').text(length);
        });
    },
    formatAnrechnungIsApplied: function (antragdatum, dms_id, filename){
        $('#requestAnrechnung-antragdatum').text(antragdatum);
        $('#requestAnrechnung-status_kurzbz').text(FHC_PhrasesLib.t("ui", "inBearbeitung"));
        $('#requestAnrechnung-status_kurzbz').closest('div').addClass('alert-warning');

        // Display File-Downloadlink
        $('#requestAnrechnung-downloadDocLink')
            .removeClass('hidden')
            .attr('href', 'RequestAnrechnung/download?dms_id=' + dms_id)
            .html(filename);

        // Disable all form elements
        $("#requestAnrechnung-form :input").prop("disabled", true);
    }
}