/**
 * Javascript file for benutzergruppe management page
 */

var Benutzergruppe = {
	getAllBenutzer: function()
	{
		FHC_AjaxClient.ajaxCallGet(
			'organisation/Gruppenadministration/getAllBenutzer',
			null,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						benutzerArr = [];

						// save loaded data
						let benutzerData = FHC_AjaxClient.getData(data);

						for (let i = 0; i < benutzerData.length; i++)
						{
							let ben = benutzerData[i];

							benutzerArr.push(
								{
									vorname: ben.vorname,
									nachname: ben.nachname,
									uid: ben.uid,
									label: ben.nachname + " " + ben.vorname + " (" + ben.uid + ")",
									id: ben.uid
								}
							);
						}

						// callback for searching source mitarbeiter array correctly
						let sourceCallback = function(request, response)
						{
							// case insensitive matcher
							let matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");

							// match vorname nachname OR nachname vorname OR uid
							response($.grep(benutzerArr, function (value) {
								return matcher.test(value.nachname + ' '+value.vorname + ' ' + value.nachname)
									|| matcher.test(value.uid);
							}));
						}

						// fill autocomplete field with benutzer data
						Benutzergruppe._fillAutocomplete(
							'teilnehmerSelect',
							'teilnehmer_uid',
							sourceCallback
						);
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	addBenutzer: function(uid, gruppe_kurzbz)
	{
		FHC_AjaxClient.ajaxCallPost(
			'organisation/Gruppenadministration/addBenutzer',
			{
				uid: uid,
				gruppe_kurzbz: gruppe_kurzbz
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						let addBenutzerRes = FHC_AjaxClient.getData(data);
						console.log(addBenutzerRes);
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	_fillAutocomplete: function(autocompleteId, idFieldId, source)
	{
		// jQuery ui autocomplete for benutzer
		$("#"+autocompleteId).autocomplete(
			{
				// custom matcher
				source: source,
				autoFocus: true,
				select: function(event, ui)
				{
					// when autocmplete entry selected, display label text in autocomplete, fill hidden value field
					$("#"+autocompleteId).val(ui.item.label);
					$("#"+idFieldId).val(ui.item.id);
					return false;
				}
			}
		);
	}
};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	Benutzergruppe.getAllBenutzer();
	Tablesort.addTablesorter(
		"benutzer-table", [[0,0], [2,0]], ['filter'], 2
	)

	$("#teilnehmerHinzufuegen").click(
		function(){
			let uid = $("#teilnehmer_uid").val();
			let gruppe_kurzbz = $("#gruppe_kurzbz").val();
			Benutzergruppe.addBenutzer(uid, gruppe_kurzbz);
		}
	);
});
