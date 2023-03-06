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

        if (mode === 'update')
        {
            let row = $(this).closest('tr');
            var anrechnungszeitraum_id = row.data('anrechnungszeitraum_id');
            var studiensemester_kurzbz = row.find('.studiensemester_kurzbz').text();
            var anrechnungstart = row.find('.anrechnungstart').text();
            var anrechnungende = row.find('.anrechnungende').text();

            $('.modal-header #azrModalLabel').text('Anrechnungszeitraum bearbeiten');

            $('.modal-body #anrechnungszeitraum_id').val(anrechnungszeitraum_id);
            $('.modal-body #studiensemester').val(studiensemester_kurzbz).change();
            $('.modal-body #azrStart').val(anrechnungstart);
            $('.modal-body #azrEnde').val(anrechnungende);

            $('.modal-footer #azrInsertOrUpdateBtn').val('update');
        }
    });

    // Insert or update Anrechnungszeitraum
    $(document).on('click', '#azrInsertOrUpdateBtn', function(){

        var anrechnungszeitraum_id = $('.modal-body #anrechnungszeitraum_id').val();
        var studiensemester_kurzbz = $('.modal-body #studiensemester').val();
        var anrechnungstart = $('.modal-body #azrStart').val();
        var anrechnungende = $('.modal-body #azrEnde').val();

        // insert or update
        let mode = this.value;

        if (mode === 'insert')
        {
            // Insert Anrechnungszeitraum
            adminAnrechnung.insertAzr(studiensemester_kurzbz, anrechnungstart, anrechnungende);
        }

        if (mode === 'update')
        {
            // Update Anrechnungszeitraum
            adminAnrechnung.updateAzr(anrechnungszeitraum_id, studiensemester_kurzbz, anrechnungstart, anrechnungende);
        }
    });

    // Delete Anrechnungszeitraum
    $('#azrTable').on('click', '.azrDeleteBtn', function(){

        if(!confirm(FHC_PhrasesLib.t("ui", "frageSicherLoeschen")))
        {
            return;
        }

        var anrechnungszeitraum_id = $(this).closest('tr').data('anrechnungszeitraum_id');
        var row = $(this).closest('tr');

        // Delete Anrechnungszeitraum
        adminAnrechnung.deleteAzr(anrechnungszeitraum_id);

        // Remove row
        row.remove();
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

                        // Add row on top
                        adminAnrechnung.prependRow(
                            data.anrechnungszeitraum_id,
                            studiensemester_kurzbz,
                            anrechnungstart,
                            anrechnungende
                        );

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
    prependRow: function (anrechnungszeitraum_id, studiensemester_kurzbz, anrechnungstart, anrechnungende) {
        $('#azrTable').prepend($(
            '<tr data-anrechnungszeitraum_id="' + anrechnungszeitraum_id + '">' +
            '<td>' + anrechnungszeitraum_id + '</td>' +
            '<td class="studiensemester_kurzbz">' + studiensemester_kurzbz + '</td>' +
            '<td class="anrechnungstart">' + anrechnungstart + '</td>' +
            '<td class="anrechnungende">' + anrechnungende + '</td>' +
            '<td>' +
            '<button class="btn btn-outline-secondary azrOpenModal" value="update"><i class="fa fa-edit"></i></button>' +
            '<button class="btn btn-outline-secondary ms-1 azrDeleteBtn"><i class="fa fa-times"></i></button>' +
            '</td>' +
            '</tr>'
        ))
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
                        adminAnrechnung.updateRow(anrechnungszeitraum_id, studiensemester_kurzbz, anrechnungstart, anrechnungende);

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
    updateRow: function (anrechnungszeitraum_id, studiensemester_kurzbz, anrechnungstart, anrechnungende){
        let row = $('#azrTable').find('tr').filter('[data-anrechnungszeitraum_id=' + anrechnungszeitraum_id + ']');
        row.find('.studiensemester_kurzbz').text(studiensemester_kurzbz);
        row.find('.anrechnungstart').text(anrechnungstart);
        row.find('.anrechnungende').text(anrechnungende);
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
    }
}