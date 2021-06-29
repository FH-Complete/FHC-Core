$(document).ready(function ()
{
	var personid = $("#hiddenpersonid").val();

	DocUeberpruefung.checkNachreichungButtons();

	$('select.aktenid').change(function()
	{
		var akteid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
		var typ = $(this).val();
		DocUeberpruefung.saveDocTyp(personid, akteid, typ);
	});

	$('.nachreichungInfos').click(function()
	{
		var akteid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
		DocUeberpruefung.checkNachreichungInputs(akteid);
	});

	$('.nachreichungAbbrechen').click(function()
	{
		var akteid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
		DocUeberpruefung.checkNachreichungInputs(akteid);
	});

	$('.nachreichungSpeichern').click(function()
	{
		var akteid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
		var typ = $('#aktenid_' + akteid).val();

		var nachreichungAm = $('#nachreichungAm_' + akteid).val();
		var nachreichungAnmerkung = $('#nachreichungAnmerkung_' + akteid).val();

		if(nachreichungAm === '')
		{
			FHC_DialogLib.alertError('Ein Datum muss im folgenden Format angegeben werden: tt.mm.jjjj');
			return false;
		}

		var regEx = /^\d{2}\.\d{2}\.(\d{2}|\d{4})$/;
		if(nachreichungAm.match(regEx) === null)
		{
			FHC_DialogLib.alertError('Bitte das Datum im folgenden Format angeben: tt.mm.jjjj')
			return false;
		}

		DocUeberpruefung.saveNachreichung(personid, nachreichungAm, nachreichungAnmerkung, typ);
	})
});

var DocUeberpruefung = {

	saveDocTyp: function(personid, akteid, typ)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + "/saveDocTyp/"+encodeURIComponent(personid),
			{
				"akte_id": akteid,
				"typ" : typ
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isSuccess(data))
					{
						FHC_DialogLib.alertSuccess("Done!");
						InfocenterDetails._refreshLog();
						DocUeberpruefung.checkNachreichungButton(akteid);
					}
					else
					{
						FHC_DialogLib.alertError(data);
					}
				},
				errorCallback: function() {
					FHC_DialogLib.alertWarning("Fehler beim Speichern!");
				}
			}
		);
	},

	saveNachreichung: function (personid, nachreichungAm, nachreichungAnmerkung, typ)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + "/saveNachreichung/"+encodeURIComponent(personid),
			{
				"nachreichungAm": nachreichungAm,
				"nachreichungAnmerkung" : nachreichungAnmerkung,
				"typ" : typ
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isSuccess(data))
					{
						FHC_DialogLib.alertSuccess("Done!");
						InfocenterDetails._refreshLog();
					}
					else
					{
						FHC_DialogLib.alertError(data);
					}
				},
				errorCallback: function() {
					FHC_DialogLib.alertWarning("Fehler beim Speichern!");
				}
			}
		);
	},

	checkNachreichungInputs: function(akteid)
	{
		var inputs = $('#nachreichungInputs_' + akteid);

		if (inputs.hasClass('hidden'))
		{
			inputs.removeClass('hidden');
		}
		else
		{
			inputs.addClass('hidden');
			$('#nachreichungAnmerkung_' + akteid).val("");
			$('#nachreichungAm_' + akteid).val("");
		}
	},

	checkNachreichungButtons: function()
	{
		$('select.aktenid').each(function () {
			var akteid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
			DocUeberpruefung.checkNachreichungButton(akteid);
		});
	},

	checkNachreichungButton: function(akteid)
	{
		var allowedTyps = ['VorlSpB2', 'ZgvBaPre', 'ZgvMaPre'];
		var typ = $('#aktenid_' + akteid).val();
		var infos = $('#nachreichungInfos_' + akteid);

		if ($.inArray(typ, allowedTyps) === -1)
			infos.addClass('hidden');
		else
			infos.removeClass('hidden');
	}

}