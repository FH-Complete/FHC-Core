const BASE_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
const CALLED_PATH = FHC_JS_DATA_STORAGE_OBJECT.called_path;
const CONTROLLER_URL = BASE_URL + "/"+CALLED_PATH;

/**
 * javascript file for Öhbeitrag GUI
 */
$(document).ready(function ()
{
	//initialise table sorter
	//Tablesort.addTablesorter("oehbeitraegeTbl", [], ["zebra"]);

	$("#addNewOeh").click(
		function()
		{
			let callback = function(data)
			{
				if (FHC_AjaxClient.hasData(data))
				{
					let studiensemester = FHC_AjaxClient.getData(data);
					let nextOehbeitragId = Oehbeitrag.newBeitragId;

					let newRowHtml = "<tr>"+
										"<td>";
					newRowHtml += 		"<select name='von_studiensemester_kurzbz' id='von_studiensemester_kurzbz_"+Oehbeitrag.newBeitragId+"' class='form-control'>";
					newRowHtml += Oehbeitrag._printStudiensemesterDropdown('von_studiensemester_kurzbz', studiensemester);
					newRowHtml += 		"</select>";
					newRowHtml += 		"</td><td>";
					newRowHtml += 		"<select name='bis_studiensemester_kurzbz' id='bis_studiensemester_kurzbz_"+Oehbeitrag.newBeitragId+"' class='form-control'>";
					newRowHtml += 		"<option value='null'>unbeschränkt</option>";
					newRowHtml += Oehbeitrag._printStudiensemesterDropdown('bis_studiensemester_kurzbz', studiensemester);
					newRowHtml += 		"</select>";
					newRowHtml += 		"</td>";
					newRowHtml += 		"<td><input type='text' name='studierendenbeitrag' id='studierendenbeitrag_"+nextOehbeitragId+"' class='form-control' placeholder='0,00'></td>"+
										"<td><input type='text' name='versicherung' id='versicherung_"+nextOehbeitragId+"' class='form-control' placeholder='0,00'></td>"+
										"<td><button class='btn btn-default' id='addOeh_"+nextOehbeitragId+"'>Speichern</button></td>"+
									"</tr>";

/*					$("#saveHeading, .saveCell").show();*/
					$("#oehbeitraegeTbl tbody").prepend(newRowHtml);
					$("#addOeh_"+Oehbeitrag.newBeitragId).click(
						function()
						{
							// get form data into object
							let oehbeitrag = {
								"von_studiensemester_kurzbz": $("#von_studiensemester_kurzbz_"+nextOehbeitragId).val(),
								"bis_studiensemester_kurzbz": $("#bis_studiensemester_kurzbz_"+nextOehbeitragId).val(),
								"studierendenbeitrag": $("#studierendenbeitrag_"+nextOehbeitragId).val().replace(",", "."),
								"versicherung": $("#versicherung_"+nextOehbeitragId).val().replace(",", ".")
							}

							Oehbeitrag.addOehbeitrag(oehbeitrag, nextOehbeitragId);
						}
					);

					// increase counter for newly added rows
					Oehbeitrag.newBeitragId++;
				}
				else
					FHC_DialogLib.alertInfo("ÖH-Beiträge für alle Studiensemester festgelegt");
			}

			Oehbeitrag.getValidStudiensemester(callback);
		}
	)

	$(".deleteBtn").click(
		function()
		{
			let oehbeitrag_id_prefixed = $(this).prop("id");
			let oehbeitrag_id = oehbeitrag_id_prefixed.substr(oehbeitrag_id_prefixed.indexOf('_')+1);

			Oehbeitrag.deleteOehbeitrag(oehbeitrag_id);
		}
	)
});

var Oehbeitrag = {
	newBeitragId: 0,
	// -----------------------------------------------------------------------------------------------------------------
	// ajax calls
	getValidStudiensemester: function(callback)
	{
		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + "/getValidStudiensemester",
			null,
			{
				successCallback: callback,
				errorCallback: function()
				{
					FHC_DialogLib.alertError('Fehler beim Holen der Semester');
				}
			}
		);
	},
	addOehbeitrag: function(oehbeitrag, nextOehbeitragId)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/addOehbeitrag',
			oehbeitrag,
			{
				successCallback: function(data, textStatus, jqXHR) {
					console.log(data);
					if (FHC_AjaxClient.isError(data))
					{
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
					}
					else if (FHC_AjaxClient.hasData(data))
					{
						console.log(data);
						let inserted_id = FHC_AjaxClient.getData(data);

						// refresh table row in GUI
						let bis_studiensemester_kurzbz = oehbeitrag.bis_studiensemester_kurzbz == 'null' ? 'unbeschränkt' : oehbeitrag.bis_studiensemester_kurzbz;

						$("#studierendenbeitrag_"+nextOehbeitragId).parent().html(Oehbeitrag._formatDecimalGerman(oehbeitrag.studierendenbeitrag));
						$("#versicherung_"+nextOehbeitragId).parent().html(Oehbeitrag._formatDecimalGerman(oehbeitrag.versicherung));
						$("#von_studiensemester_kurzbz_"+nextOehbeitragId).parent().html(oehbeitrag.von_studiensemester_kurzbz);
						$("#bis_studiensemester_kurzbz_"+nextOehbeitragId).parent().html(bis_studiensemester_kurzbz);

						// add delete button instead of save btn
						$("#addOeh_"+nextOehbeitragId).parent().html("<button class='btn btn-default deleteBtn' id='delete_"+inserted_id+"'>L&ouml;schen</button>");

						$("#delete_"+inserted_id).click(
							function()
							{
								let oehbeitrag_id_prefixed = $(this).prop("id");
								let oehbeitrag_id = oehbeitrag_id_prefixed.substr(oehbeitrag_id_prefixed.indexOf('_')+1);

								Oehbeitrag.deleteOehbeitrag(oehbeitrag_id);
							}
						)
					}
					else
					{
						FHC_DialogLib.alertError('Fehler beim Hinzufügen des Öhbeitrags');
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError('Fehler beim Hinzufügen des Öhbeitrags');
				}
			}
		);
	},
	deleteOehbeitrag: function(oehbeitrag_id)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/deleteOehbeitrag',
			{"oehbeitrag_id": oehbeitrag_id},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						console.log(data);
						$("#delete_"+oehbeitrag_id).closest("tr").remove();
					}
					else
					{
						FHC_DialogLib.alertError('Fehler beim Löschen des Öhbeitrags');
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError('Fehler beim Löschen des Öhbeitrags');
				}
			}
		);
	},

	// -----------------------------------------------------------------------------------------------------------------
	// (private) methods
	_printStudiensemesterDropdown(name, studiensemester)
	{
		let rowHtml = "";
		let first = true;
		for (let idx in studiensemester)
		{
			let selected = first ? ' selected' : '';
			let studiensemester_kurzbz = studiensemester[idx].studiensemester_kurzbz;
			rowHtml += "<option value='" + studiensemester_kurzbz + "'"+selected+">" + studiensemester_kurzbz + "</option>";
			first = false;
		}

		return rowHtml;
	},
	/**
	 * Formats a numeric value as a float with two decimals
	 * @param sum
	 * @returns {string}
	 */
	_formatDecimalGerman: function(sum)
	{
		var dec = null;

		if(sum === null)
			dec = parseFloat(0).toFixed(2).replace(".", ",");
		else if(sum === '')
		{
			dec = ''
		}
		else
		{
			dec = parseFloat(sum).toFixed(2);

			dec = dec.split('.');
			var dec1 = dec[0];
			var dec2 = ',' + dec[1];
			var rgx = /(\d+)(\d{3})/;
			while (rgx.test(dec1)) {
				dec1 = dec1.replace(rgx, '$1' + '.' + '$2');
			}
			dec = dec1 + dec2;
		}
		return dec;
	}
};
