$(function () {

    // tableInit is called in the jquery_wrapper when the tableBuilt event was finished
    $(document).on("tableInit", function(event,tabulatorInstance) {
        
           // event rowSelected needs to be attached as a callback to the tableBuilt event in tabulator5
           $("#tableWidgetTabulator").tabulator("on","rowSelected",(row)=>{func_rowSelected(row)});
           // event rowSelectionChanged needs to be attached as a callback to the tableBuilt event in tabulator5
           $("#tableWidgetTabulator").tabulator("on","rowSelectionChanged",(data,rows)=>{func_rowSelectionChanged(data,rows)});
          
    });


    // Disable all form fields by default
    createAnrechnung.disableFormFields();

    // Create Anrechnung on form submit
    $('#createAnrechnung-form').submit(function(e){

        e.preventDefault();

        var fileInput = $('#requestAnrechnung-uploadfile');
        const uploadMaxFilesize = $('#requestAnrechnung-uploadfile').data('maxsize')  ; // in byte
        if (!createAnrechnung.fileSizeOk(fileInput, uploadMaxFilesize)) // in byte
        {
            return FHC_DialogLib.alertWarning(FHC_PhrasesLib.t("ui", "errorDokumentZuGross"));
        }

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/create",
            {
                prestudent_id: this.prestudent_id.value,
                studiensemester_kurzbz: this.studiensemester_kurzbz.value,
                lehrveranstaltung_id: this.lehrveranstaltung_id.value,
                begruendung_id: this.begruendung_id.value,
                herkunftKenntnisse: this.herkunftKenntnisse.value,
                uploadfile: this.uploadfile.files
            },
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (FHC_AjaxClient.isError(data))
                    {
                        FHC_DialogLib.alertWarning(FHC_AjaxClient.getError(data));
                    }

                    if (FHC_AjaxClient.hasData(data))
                    {
                        FHC_DialogLib.alertSuccess(FHC_AjaxClient.getData(data).msg);

                        // Display button to open Anrechnung in new tab
                        createAnrechnung.displayButtonToOpenAnrechnung(FHC_AjaxClient.getData(data).anrechnung_id);
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    });
})

// TABULATOR FUNCTIONS
// ---------------------------------------------------------------------------------------------------------------------
/**
 * Set form fields and populate selectmenu with LVs of student
 * on row selection.
 *
 * @param row
 */
function func_rowSelected(row){
    let studiensemester_kurzbz = $('#studiensemester_kurzbz').val();
    let prestudent_id = row.getData().prestudent_id;
    let vorname = row.getData().vorname;
    let nachname = row.getData().nachname;

    // Set hidden form field Prestudent ID
    $('#prestudent_id').val(prestudent_id);

    // Set field StudentIn
    $('#student').text(vorname + ' ' + nachname);

    // Populate Select with LVs of student
    createAnrechnung.populateSelectWithStudentLVs(prestudent_id, studiensemester_kurzbz);

    // Enable all form fields
    createAnrechnung.enableFormFields();
}

/**
 * Empty and disable form fields
 * when none row selected.
 *
 * @param data
 * @param rows
 */
function func_rowSelectionChanged(data, rows){

    // If no student is selected in the table
    if (rows.length == 0)
    {
        // ...empty form fields
        createAnrechnung.emptyFormFields();

        // ...disable form fields
        createAnrechnung.disableFormFields();
    }
}

// ---------------------------------------------------------------------------------------------------------------------

var createAnrechnung = {
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
    emptyFormFields: function(){

        // Empty field StudentIn
        $('#student').text('');

        // Empty form fields (except hidden ones)
        $('#createAnrechnung-form :input:not([type=hidden])').val('');

        // Hide eventually displayed button to open Anrechnung
        $('#createAnrechnung-openAnrechnung').addClass('visually-hidden');
    },
    disableFormFields: function(){
        let prestudent_id = $('#prestudent_id').data('prestudent_id');

        if (prestudent_id == '')
        {
            // Disable all form elements
            $("#createAnrechnung-form :input").prop("disabled", true);
        }
    },
    enableFormFields: function(){
        $("#createAnrechnung-form :input").prop("disabled", false);
    },
    populateSelectWithStudentLVs: function(prestudent_id, studiensemester_kurzbz){
        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/getLVsByStudent",
            {'prestudent_id': prestudent_id, 'studiensemester_kurzbz': studiensemester_kurzbz},
            {
                successCallback: function (data, textStatus, jqXHR)
                {
                    if (FHC_AjaxClient.isError(data))
                    {
                        // Print error message
                        FHC_DialogLib.alertWarning(FHC_AjaxClient.getError(data));
                    }

                    if (FHC_AjaxClient.hasData(data))
                    {
                        let lehrveranstaltungen = FHC_AjaxClient.getData(data);

                        // Remove previous Lehrveranstaltungen
                        $('#select-lehrveranstaltung option').slice(1).remove();    // Leave first option 'Bitte wählen'

                        // Append Lehrveranstaltungen
                        for (let lv of lehrveranstaltungen){
                            $('#select-lehrveranstaltung').append('<option value="'+ lv.lehrveranstaltung_id +'">'+ lv.bezeichnung +'</option>');
                        }
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    },
    displayButtonToOpenAnrechnung: function(anrechnung_id){
        // Display button to open Anrechnung in new tab
        $('#createAnrechnung-openAnrechnung')
            .removeClass('visually-hidden')
            .attr('href', 'ApproveAnrechnungDetail/?anrechnung_id=' + anrechnung_id)
            .html('<i class="fa fa-external-link" aria-hidden="true"></i> ' + FHC_PhrasesLib.t("global", "antragBearbeiten"));
    }
}