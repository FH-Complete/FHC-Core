const ANRECHNUNGSTATUS_APPROVED = 'approved';
const ANRECHNUNGSTATUS_REJECTED = 'rejected';

$(function(){
    // Set status alert color
    requestAnrechnung.setStatusAlertColor();
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
                break;
            default:
                $('#requestAnrechnung-status_kurzbz').closest('div').addClass('alert-warning');
        }
    }
}