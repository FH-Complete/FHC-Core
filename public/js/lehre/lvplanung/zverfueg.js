
// Returns relative height (depending on screen size)
function func_height(table) {
    return $(window).height() * 0.5;
}

$(function(){

    // tableInit is called in the jquery_wrapper when the tableBuilt event was finished
    $(document).on("tableInit", function(event,tabulatorInstance) {
    
        $("#tableWidgetTabulator").tabulator("on","rowDeselected",(row)=>func_rowDeselected(row));
        $("#tableWidgetTabulator").tabulator("on","rowSelected",(row)=>func_rowSelected(row));
        
    });

    var studiensemesterStart = $("#studsemStart").val();

    Zverfueg.initDatepicker(studiensemesterStart);

    $('#form-zeitverfuegbarkeit').submit(function(e){

        e.preventDefault();

        let zeitsperre_id = this.zeitsperre_id.value;
        let mitarbeiter_uid = this.mitarbeiter_uid.value;
        let lektor = this.mitarbeiter_uid.options[this.mitarbeiter_uid.selectedIndex].text;
        let bezeichnung = this.bezeichnung.value;
        let vondatum = this.vondatum.value;
        let vonstunde = this.vonstunde.value;
        let bisdatum = this.bisdatum.value;
        let bisstunde = this.bisstunde.value;

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/saveZeitverfuegbarkeit",
            {
                zeitsperre_id: zeitsperre_id,
                mitarbeiter_uid: mitarbeiter_uid,
                bezeichnung: bezeichnung,
                zeitsperretyp_kurzbz: 'ZVerfueg',
                vondatum: vondatum,
                vonstunde: vonstunde,
                bisdatum: bisdatum,
                bisstunde: bisstunde
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
                        if (zeitsperre_id == '')
                        {
                            // Add row
                            $('#tableWidgetTabulator').tabulator('addRow', {
                                zeitsperre_id: FHC_AjaxClient.getData(data).zeitsperre_id,
                                mitarbeiter_uid: mitarbeiter_uid,
                                lektor: lektor,
                                vondatum: vondatum,
                                vonstunde: $.isNumeric(vonstunde) ? vonstunde : '',
                                bisdatum: bisdatum,
                                bisstunde: $.isNumeric(bisstunde) ? bisstunde : '',
                                bezeichnung: bezeichnung
                            }, true); // true adds new row on top
                        }
                        else {
                            $('#tableWidgetTabulator').tabulator('updateData', [{
                                zeitsperre_id: zeitsperre_id,
                                vondatum: vondatum,
                                vonstunde: $.isNumeric(vonstunde) ? vonstunde : '',
                                bisdatum: bisdatum,
                                bisstunde: $.isNumeric(bisstunde) ? bisstunde : '',
                                bezeichnung: bezeichnung
                            }]);
                        }

                        // Reset form
                        Zverfueg.resetFormFields();

                        // Disable form elements
                        Zverfueg.disableFormElements();

                        // Display success message
                        FHC_DialogLib.alertSuccess(FHC_AjaxClient.getData(data).msg);
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    })

    $('#btn-delete').click(function() {
        let zeitsperre_id = $('#zeitsperre_id').val();

        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/deleteZeitverfuegbarkeit",
            {
                zeitsperre_id: zeitsperre_id,
            },
            {
                successCallback: function (data, textStatus, jqXHR) {
                    if (FHC_AjaxClient.isError(data)) {
                        FHC_DialogLib.alertWarning(FHC_AjaxClient.getError(data));
                    }

                    if (FHC_AjaxClient.hasData(data)) {

                        // Delete row
						$('#tableWidgetTabulator').tabulator('deleteRow', zeitsperre_id);
						

                        // Reset form
                        Zverfueg.resetFormFields();

                        // Disable form elements
                        Zverfueg.disableFormElements();

                        // Display delete message
                        FHC_DialogLib.alertSuccess(FHC_AjaxClient.getData(data).msg);
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            });
    });

    $('#btn-break').click(function () {
        Zverfueg.disableFormElements();
    })

})

var Zverfueg = {
    initDatepicker: function (studiensemesterStart) {

        // Prevent opening HTMl date picker
        $('input[type=date]').on('click', function(event) {
            event.preventDefault();
        });

        $.datepicker.setDefaults($.datepicker.regional['de']);
        $( ".zverfueg-datepicker" ).datepicker({
            "dateFormat": "yy-mm-dd",
            "minDate": $.datepicker.formatDate('yy-mm-dd', new Date(studiensemesterStart))
        });
    },
    resetFormFields: function(){
        $('#form-zeitverfuegbarkeit')
            .trigger('reset')
            .find('input:hidden[name=zeitsperre_id]').val('')
            .find('textarea[name=bezeichnung]').val('');
    },
    disableFormElements: function (){
        $('#btn-delete').prop('disabled', true).tooltip('enable');
        $('#mitarbeiter_uid').prop('disabled', false);
    },
    enableFormElements: function (){
        $('#btn-delete').prop('disabled', false).tooltip('disable');
        $('#mitarbeiter_uid').prop('disabled', true);
    }
}

// TABULATOR FUNCTIONS
// ---------------------------------------------------------------------------------------------------------------------
function func_rowSelected(row){
    Zverfueg.enableFormElements();

    // Set form fields
    $('#zeitsperre_id').val(row.getData().zeitsperre_id);
    $('#mitarbeiter_uid').val(row.getData().mitarbeiter_uid);
    $('#bezeichnung').val(row.getData().bezeichnung);
    $('#vondatum').datepicker('setDate', row.getData().vondatum);
    $('#bisdatum').datepicker('setDate', row.getData().bisdatum);
    $('#vonstunde').val(row.getData().vonstunde);
    $('#bisstunde').val(row.getData().bisstunde);
}

function func_rowDeselected(row){
    Zverfueg.resetFormFields();
    Zverfueg.disableFormElements();
    Zverfueg.resetFormFields();
}