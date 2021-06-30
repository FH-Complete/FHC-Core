const ALLOWED_DOC_TYPES = ['VorlSpB2', 'ZgvBaPre', 'ZgvMaPre'];

$(document).ready(function ()
{
	DocUeberpruefung._formatDocTable();
	DocUeberpruefung.checkNachreichungButtons();

	var personid = $("#hiddenpersonid").val();

	//add click events to "formal geprüft" checkboxes
	$(".prchbox").click(function ()
	{
		var boxid = this.id;
		var akteid = InfocenterDetails._getPrestudentIdFromElementId(boxid);
		var checked = this.checked;
		DocUeberpruefung.saveFormalGeprueft(personid, akteid, checked)
	});

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
			FHC_DialogLib.alertError(FHC_PhrasesLib.t('infocenter', 'datumUngueltig'));
			return false;
		}

		var regEx = /^\d{2}\.\d{2}\.(\d{2}|\d{4})$/;

		if(nachreichungAm.match(regEx) === null)
		{
			FHC_DialogLib.alertError(FHC_PhrasesLib.t('infocenter', 'datumUngueltig'))
			return false;
		}

		DocUeberpruefung.saveNachreichung(personid, nachreichungAm, nachreichungAnmerkung, typ);
	})
});

var DocUeberpruefung = {

	saveFormalGeprueft: function(personid, akteid, checked)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/saveFormalGeprueft/' + encodeURIComponent(personid),
			{
				akte_id: akteid,
				formal_geprueft: checked
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						var timestamp = data.retval[0];
						if (timestamp === "")
						{
							$("#formalgeprueftam_" + akteid).text("");
						}
						else
						{
							var fgdatum = $.datepicker.parseDate("yy-mm-dd", timestamp);
							var gerfgdatum = $.datepicker.formatDate("dd.mm.yy", fgdatum);
							$("#formalgeprueftam_" + akteid).text(gerfgdatum);
						}
						//refresh doctable tablesorter, formal geprueft changed!
						$("#doctable").trigger("update");
						InfocenterDetails._refreshLog();
					}
					else
					{
						InfocenterDetails._genericSaveError();
					}
				},
				errorCallback: InfocenterDetails._genericSaveError,
				veilTimeout: 0
			}
		);
	},

	saveDocTyp: function(personid, akteid, typ)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + "/saveDocTyp/" + encodeURIComponent(personid),
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
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
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
			CALLED_PATH + "/saveNachreichung/" + encodeURIComponent(personid),
			{
				"nachreichungAm": nachreichungAm,
				"nachreichungAnmerkung" : nachreichungAnmerkung,
				"typ" : typ
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isSuccess(data))
					{
						DocUeberpruefung._refreshNachzureichendeDoks();
						InfocenterDetails._refreshLog();
						FHC_DialogLib.alertSuccess("Done!");
					}
					else
					{
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
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
		var typ = $('#aktenid_' + akteid).val();
		var infos = $('#nachreichungInfos_' + akteid);

		if ($.inArray(typ, ALLOWED_DOC_TYPES) === -1)
		{
			infos.addClass('hidden');
		}
		else
		{
			infos.removeClass('hidden');
		}
	},

	_refreshNachzureichendeDoks: function()
	{
		var personid = $("#hiddenpersonid").val();

		$("#nachzureichendeDoks").load(
			CONTROLLER_URL + '/reloadDoks/' + personid + '?fhc_controller_id=' + FHC_AjaxClient.getUrlParameter('fhc_controller_id'),
			function () {
				DocUeberpruefung._formatDocTable();
			}
		);
	},

	_formatDocTable: function()
	{
		Tablesort.addTablesorter("doctable", [[2, 1], [1, 0]], ["zebra"]);
		Tablesort.addTablesorter("nachgdoctable", [[2, 0], [1, 1]], ["zebra"]);
	},

}