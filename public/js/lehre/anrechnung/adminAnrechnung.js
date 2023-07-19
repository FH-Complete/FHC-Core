// TABULATOR
// ---------------------------------------------------------------------------------------------------------------------

// Add Edit and Update Buttons to table rows
function func_tableBuilt(table) {
    table.addColumn(
        {
            title: "Aktion",
            align: "center",
            width: 150,
            formatter: addActionButtons,
        }, false  // place column right
    );

}

// Returns relative height (depending on screen size)
function func_height(table){
    return $(window).height() * 0.50;
}

// Converts string date postgre style to string DD.MM.YYYY.
// This will allow correct filtering.
var formatDate = function(cell, formatterParams){
    let postgreDate = cell.getValue();
    if (postgreDate != null)
    {
        var d = new Date(postgreDate);
        return ("0" + (d.getDate())).slice(-2)  + "." + ("0" + (d.getMonth() + 1)).slice(-2) + "." + d.getFullYear();
    }
}

// Create Edit and Update Buttons for table rows
var addActionButtons = function(cell) {

    // Create edit button
    var editBtn = document.createElement("button");
    editBtn.type = "button";
    editBtn.innerHTML = "<i class=\"fa fa-edit\"></i>";
    editBtn.classList.add("azrEditBtn");
    editBtn.classList.add("btn");
    editBtn.classList.add("btn-default");
    editBtn.addEventListener("click", function(){
        adminAnrechnung.editRow(cell);
    });



    // Create delete button
    var delBtn= document.createElement("button");
    delBtn.type = "button";
    delBtn.innerHTML = "<i class=\"fa fa-times\"></i>";
    delBtn.classList.add("azrDeleteBtn");
    delBtn.classList.add("btn");
    delBtn.classList.add("btn-default");
    delBtn.style.marginLeft = '5px';
    delBtn.addEventListener("click", function(){
        adminAnrechnung.deleteRow(cell);
    });

    // Add buttons to cell
    var buttonHolder = document.createElement("span");
    buttonHolder.appendChild(editBtn);
    buttonHolder.appendChild(delBtn);

    return buttonHolder;
}

// ---------------------------------------------------------------------------------------------------------------------

$(function () {

    // Empty Modal fields on 'Anrechnungszeitraum hinzufuegen'
    $(document).on('click', '.azrOpenModal', function(){

            let defaultStudiensemester_kurzbz = $('.modal-body #defaultStudiensemester_kurzbz').val();

            $('.modal-header #azrModalLabel').text(FHC_PhrasesLib.t("anrechnung", "anrechnungszeitraumHinzufuegen"));

            $('.modal-body #anrechnungszeitraum_id').val('');
            $('.modal-body #studiensemester').val(defaultStudiensemester_kurzbz).change();
            $('.modal-body #azrStart').val('');
            $('.modal-body #azrEnde').val('');

    });

    // Insert Anrechnungszeitraum
    $(document).on('click', '#azrInsertBtn', function(){
        var studiensemester_kurzbz = $('.modal-body #studiensemester').val();
        var anrechnungstart = $('.modal-body #azrStart').val();
        var anrechnungende = $('.modal-body #azrEnde').val();

        // Insert Anrechnungszeitraum
        adminAnrechnung.insertAzr(studiensemester_kurzbz, anrechnungstart, anrechnungende);
    });

})

var adminAnrechnung = {
    insertAzr: function(studiensemester_kurzbz, anrechnungstart, anrechnungende){
        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/save",
            {
                studiensemester_kurzbz: studiensemester_kurzbz,
                anrechnungstart: anrechnungstart,
                anrechnungende: anrechnungende
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
                        data = FHC_AjaxClient.getData(data);

                        // Update row
                        $('#tableWidgetTabulator').tabulator('addData', [{
                            anrechnungszeitraum_id: data.anrechnungszeitraum_id,
                            studiensemester_kurzbz: studiensemester_kurzbz,
                            anrechnungstart: anrechnungstart,
                            anrechnungende: anrechnungende
                        }], true); // true to add row on top

                        // Close Modal
                        $('#azrModal').modal('hide');

                        // Success message
                        FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("ui", "gespeichert"));
                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    },
    editRow: function (cell){
        // Open Modal
        $('#azrModal').modal('show');

        let row = cell.getRow();
        var anrechnungszeitraum_id = row.getData().anrechnungszeitraum_id;
        var studiensemester_kurzbz = row.getData().studiensemester_kurzbz;
        var anrechnungstart = row.getData().anrechnungstart;
        var anrechnungende = row.getData().anrechnungende;

        $('.modal-header #azrModalLabel').text('Anrechnungszeitraum bearbeiten');

        $('.modal-body #anrechnungszeitraum_id').val(anrechnungszeitraum_id);
        $('.modal-body #studiensemester').val(studiensemester_kurzbz).change();
        $('.modal-body #azrStart').val(anrechnungstart);
        $('.modal-body #azrEnde').val(anrechnungende);

    },
    updateAzr: function (anrechnungszeitraum_id, studiensemester_kurzbz, anrechnungstart, anrechnungende) {
        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/edit",
            {
                anrechnungszeitraum_id: anrechnungszeitraum_id,
                studiensemester_kurzbz: studiensemester_kurzbz,
                anrechnungstart: anrechnungstart,
                anrechnungende: anrechnungende
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
                        // Update row
                        $('#tableWidgetTabulator').tabulator('updateData', [{
                            anrechnungszeitraum_id: anrechnungszeitraum_id,
                            studiensemester_kurzbz: studiensemester_kurzbz,
                            anrechnungstart: anrechnungstart,
                            anrechnungende: anrechnungende
                        }]);

                        // Close Modal
                        $('#azrModal').modal('hide');

                        // Success message
                        FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("ui", "gespeichert"));

                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    },
    deleteAzr: function(anrechnungszeitraum_id){
        FHC_AjaxClient.ajaxCallPost(
            FHC_JS_DATA_STORAGE_OBJECT.called_path + "/delete",
            {
                anrechnungszeitraum_id: anrechnungszeitraum_id
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
                        let row = $('#tableWidgetTabulator').tabulator('getRow', anrechnungszeitraum_id);
                        row.delete(anrechnungszeitraum_id);

                        // Success message
                        FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t("ui", "geloescht"));

                    }
                },
                errorCallback: function (jqXHR, textStatus, errorThrown)
                {
                    FHC_DialogLib.alertError(FHC_PhrasesLib.t("ui", "systemfehler"));
                }
            }
        );
    },
    deleteRow: function (cell){
        if(!confirm(FHC_PhrasesLib.t("ui", "frageSicherLoeschen")))
        {
            return;
        }

        // Delete Anrechnungszeitraum
        adminAnrechnung.deleteAzr(cell.getRow().getData().anrechnungszeitraum_id);
    }
}