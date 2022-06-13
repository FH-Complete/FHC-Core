/**
 * Javascript file for benutzergruppe management page
 */

var Benutzergruppe = {
	getBenutzer: function(gruppe_kurzbz)
	{
		FHC_AjaxClient.ajaxCallGet(
			'person/gruppenadministration/getBenutzer',
			{
				gruppe_kurzbz: gruppe_kurzbz
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isError(data)) FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));

					if (FHC_AjaxClient.hasData(data))
					{
						// save loaded data
						let benutzerData = FHC_AjaxClient.getData(data);

						console.log(benutzerData);

						let benutzerTable = $("#benutzer-table tbody");

						benutzerTable.empty();

						// fill table with Benutzer of Gruppe
						for (let i = 0; i < benutzerData.length; i++)
						{
							let benutzer = benutzerData[i];
							benutzerTable.append(
								"<tr>"+
									"<td>"+benutzer.uid+"</td>"+
									"<td>"+benutzer.vorname+"</td>"+
									"<td>"+benutzer.nachname+"</td>"+
									"<td>"+
										"<button class='btn btn-default benutzerLoeschen' id='"+benutzer.uid+"_benutzerLoeschen'>"+
										FHC_PhrasesLib.t('ui', 'entfernen')+
										"</button>"+
									"</td>"+
								"</tr>"
							);

							// add delete event to button
							$("#"+benutzer.uid+"_benutzerLoeschen").click(
								function() {
									Benutzergruppe.removeBenutzer(benutzer.uid, gruppe_kurzbz);
								}
							)
						}

						Tablesort.addTablesorter(
							"benutzer-table", [[0,0], [2,0]], ["filter", "zebra"], 2, {headers: {3: {filter: false}}}
						)
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	getAllBenutzer: function()
	{
		FHC_AjaxClient.ajaxCallGet(
			'person/gruppenadministration/getAllBenutzer',
			null,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isError(data)) FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));

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
			'person/gruppenadministration/addBenutzer',
			{
				uid: uid,
				gruppe_kurzbz: gruppe_kurzbz
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isError(data)) FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));

					if (FHC_AjaxClient.hasData(data))
					{
						let addBenutzerRes = FHC_AjaxClient.getData(data);

						Benutzergruppe.getBenutzer(gruppe_kurzbz);
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	removeBenutzer: function(uid, gruppe_kurzbz)
	{
		FHC_AjaxClient.ajaxCallPost(
			'person/gruppenadministration/removeBenutzer',
			{
				uid: uid,
				gruppe_kurzbz: gruppe_kurzbz
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isError(data)) FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));

					if (FHC_AjaxClient.hasData(data))
					{
						let addBenutzerRes = FHC_AjaxClient.getData(data);

						Benutzergruppe.getBenutzer(gruppe_kurzbz);
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
	let gruppe_kurzbz = $("#gruppe_kurzbz").val();
	Benutzergruppe.getAllBenutzer();
	Benutzergruppe.getBenutzer(gruppe_kurzbz);

	$("#teilnehmerHinzufuegen").click(
		function(){
			let uid = $("#teilnehmer_uid").val();
			Benutzergruppe.addBenutzer(uid, gruppe_kurzbz);
			$("#teilnehmerSelect").val('');
		}
	);
});
