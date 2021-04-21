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

    $('#requestAnrechnung-apply-anrechnung').click(function(e){

        // Avoid form redirecting automatically
        e.preventDefault();

        // Get form data
        let formdata = new FormData($('#requestAnrechnung-form')[0]);

        // These field MUST be activated
        if (!formdata.has('bestaetigung'))
        {
            return FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("ui", "errorBestaetigungFehlt"));
        }

        $.ajax({
            url : "RequestAnrechnung/apply",
            type: "POST",
            data : formdata,
            processData: false, // needed to pass uploaded file with FormData
            contentType: false, // needed to pass uploaded file with FormData
            success:function(data, textStatus, jqXHR){
                if (data.error && data.retval != null)
                {
                    FHC_DialogLib.alertWarning(data.retval);
                }

                if (!data.error && data.retval != null)
                {
                      requestAnrechnung.formatAnrechnungIsApplied(
                        data.retval.antragdatum,
                        data.retval.dms_id,
                        formdata.get('uploadfile').name
                    );

                    FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("global", "antragWurdeGestellt"));
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                FHC_DialogLib.alertWarning(FHC_PhrasesLib.t("ui", "systemfehler"));
            }
        });
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
            $("#requestAnrechnung-form :input").prop("disabled", true);
        }
    },
    markAsBestaetigtIfAntragIsApplied: function(){
        let status_kurzbz = $('#requestAnrechnung-status_kurzbz').data('status_kurzbz');

        if (status_kurzbz != '')
        {
            $("#requestAnrechnung-form :input[name='bestaetigung']").prop('checked', true);
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