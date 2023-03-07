// Adds column details
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

var addActionButtons = function(cell) {

    // Create edit button
    var editBtn = document.createElement("button");
    editBtn.type = "button";
    editBtn.innerHTML = "<i class=\"fa fa-edit\"></i>";
    editBtn.classList.add("azrEditBtn");
    editBtn.classList.add("btn");
    editBtn.classList.add("btn-outline-secondary");
    editBtn.addEventListener("click", function(){
        adminAnrechnung.editRow(cell);
    });



    // Create delete button
    var delBtn= document.createElement("button");
    delBtn.type = "button";
    delBtn.innerHTML = "<i class=\"fa fa-times\"></i>";
    delBtn.classList.add("azrDeleteBtn");
    delBtn.classList.add("btn");
    delBtn.classList.add("btn-outline-secondary");
    delBtn.classList.add("ms-1");
    delBtn.addEventListener("click", function(){
        adminAnrechnung.deleteRow(cell);
    });

    // Add buttons to cell
    var buttonHolder = document.createElement("span");
    buttonHolder.appendChild(editBtn);
    buttonHolder.appendChild(delBtn);

    return buttonHolder;
}

$(function () {

    // Open Modal and set values for insert or update Anrechnungszeitraum
    $(document).on('click', '.azrOpenModal', function(){
        
        // Open Modal
        $('#azrModal').modal('show');

        // insert or update
        let mode = this.value;

        if (mode === 'insert')
        {
            let defaultStudiensemester_kurzbz = $('.modal-body #defaultStudiensemester_kurzbz').val();

            $('.modal-header #azrModalLabel').text('Anrechnungszeitraum hinzufügen');

            $(".modal").show();

            $('.modal-body #anrechnungszeitraum_id').val('');
            $('.modal-body #studiensemester').val(defaultStudiensemester_kurzbz).change();
            $('.modal-body #azrStart').val('');
            $('.modal-body #azrEnde').val('');

            $('.modal-footer #azrInsertOrUpdateBtn').val('insert');
        }

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

        $('.modal-footer #azrInsertOrUpdateBtn').val('update');
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