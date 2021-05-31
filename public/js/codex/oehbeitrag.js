const BASE_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
const CALLED_PATH = FHC_JS_DATA_STORAGE_OBJECT.called_path;
const CONTROLLER_URL = BASE_URL + "/"+CALLED_PATH;

/**
 * javascript file for Öhbeitrag GUI
 */
$(document).ready(function ()
{
	//initialise table sorter
	Oehbeitrag._addTablesorter();

	// set trigger for adding new Oehhbeitrag
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
					newRowHtml += Oehbeitrag._getStudiensemesterDropdown(Oehbeitrag.newBeitragId, 'von_studiensemester_kurzbz', studiensemester);
					newRowHtml += 		"</td><td>";
					newRowHtml += Oehbeitrag._getStudiensemesterDropdown(Oehbeitrag.newBeitragId, 'bis_studiensemester_kurzbz', studiensemester);
					newRowHtml += 		"</td>";
					newRowHtml += 		"<td><input type='text' name='studierendenbeitrag' id='input_studierendenbeitrag_"+nextOehbeitragId+"' class='form-control' placeholder='0,00'></td>"+
										"<td><input type='text' name='versicherung' id='input_versicherung_"+nextOehbeitragId+"' class='form-control' placeholder='0,00'></td>"+
										"<td><button class='btn btn-default' id='addOeh_"+nextOehbeitragId+"'>Speichern</button>&nbsp;" +
						"						<button class='btn btn-default' id='delete_"+nextOehbeitragId+"'>Entfernen</button></td>"+
									"</tr>";

/*					$("#saveHeading, .saveCell").show();*/
					$("#oehbeitraegeTbl tbody").prepend(newRowHtml);
					$("#addOeh_"+Oehbeitrag.newBeitragId).click(
						function()
						{
							// get form data into object
							let oehbeitrag = {
								"von_studiensemester_kurzbz": $("#input_von_studiensemester_kurzbz_"+nextOehbeitragId).val(),
								"bis_studiensemester_kurzbz": $("#input_bis_studiensemester_kurzbz_"+nextOehbeitragId).val(),
								"studierendenbeitrag": $("#input_studierendenbeitrag_"+nextOehbeitragId).val().replace(",", "."),
								"versicherung": $("#input_versicherung_"+nextOehbeitragId).val().replace(",", ".")
							}

							let studiensemester_von_bis = {
								"von_semester_with_date": $("#input_von_studiensemester_kurzbz_"+nextOehbeitragId+" option:selected").text(),
								"bis_semester_with_date": $("#input_bis_studiensemester_kurzbz_"+nextOehbeitragId+" option:selected").text()
							}

							Oehbeitrag.addOehbeitrag(oehbeitrag, studiensemester_von_bis, nextOehbeitragId);
						}
					);

					$("#delete_"+nextOehbeitragId).click(
						function()
						{
							$(this).parent('td').parent('tr').remove();
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

	// set trigger for deleting Oehhbeitrag
	$(".deleteBtn").click(
		function()
		{
			let oehbeitrag_id_prefixed = $(this).prop("id");
			let oehbeitrag_id = oehbeitrag_id_prefixed.substr(oehbeitrag_id_prefixed.lastIndexOf('_')+1);

			Oehbeitrag.deleteOehbeitrag(oehbeitrag_id);
		}
	);

	// set trigger for updating Oehhbeitrag
	Oehbeitrag._setUpdateEvents();
});

var Oehbeitrag = {
	newBeitragId: 0,
	// -----------------------------------------------------------------------------------------------------------------
	// ajax calls
	getValidStudiensemester: function(callback, oehbeitrag_id)
	{
		let params = oehbeitrag_id ? {"oehbeitrag_id": oehbeitrag_id} : null;

		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + "/getValidStudiensemester",
			params,
			{
				successCallback: callback,
				errorCallback: function()
				{
					FHC_DialogLib.alertError('Fehler beim Holen der Semester');
				}
			}
		);
	},
	addOehbeitrag: function(oehbeitrag, studiensemester_von_bis, nextOehbeitragId)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/addOehbeitrag',
			oehbeitrag,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isError(data))
					{
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
					}
					else if (FHC_AjaxClient.hasData(data))
					{
						let inserted_id = FHC_AjaxClient.getData(data);

						// refresh table row in GUI
						let bis_studiensemester_kurzbz = oehbeitrag.bis_studiensemester_kurzbz == 'null' ? 'unbeschränkt' : studiensemester_von_bis.bis_semester_with_date;

						$("#input_studierendenbeitrag_"+nextOehbeitragId).parent('td').html(
							Oehbeitrag._formatDecimalGerman(oehbeitrag.studierendenbeitrag)+" <i class='fa fa-edit editStudierendenbeitrag' id='edit_studierendenbeitrag_"+inserted_id+"'></i>"
						);
						$("#input_versicherung_"+nextOehbeitragId).parent('td').html(
							Oehbeitrag._formatDecimalGerman(oehbeitrag.versicherung)+" <i class='fa fa-edit editVersicherung' id='edit_versicherung_"+inserted_id+"'></i>"
						);
						$("#input_von_studiensemester_kurzbz_"+nextOehbeitragId).parent('td').html(
							studiensemester_von_bis.von_semester_with_date+" <i class='fa fa-edit editVonStudiensemester' id='edit_von_studiensemester_kurzbz_"+inserted_id+"'></i>"
						);
						$("#input_bis_studiensemester_kurzbz_"+nextOehbeitragId).parent('td').html(
							bis_studiensemester_kurzbz+" <i class='fa fa-edit editBisStudiensemester' id='edit_bis_studiensemester_kurzbz_"+inserted_id+"'></i>"
						);

						// add delete button instead of save btn
						$("#addOeh_"+nextOehbeitragId).parent('td').html("<button class='btn btn-default deleteBtn' id='delete_"+inserted_id+"'>L&ouml;schen</button>");

						// add update and delete events
						Oehbeitrag._setUpdateEvents();

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
	updateOehbeitrag(oehbeitrag_id, fieldname, fieldelement, inputtype)
	{
		let oehbeitragdata = {};
		let fieldvalue = fieldelement.val();
		oehbeitragdata[fieldname] = fieldvalue;

		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/updateOehbeitrag',
			{
				"oehbeitrag_id": oehbeitrag_id,
				"data": oehbeitragdata
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isError(data))
					{
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
					}
					else if (FHC_AjaxClient.hasData(data))
					{
						if (inputtype == 'semester')
							fieldvalue = $(fieldelement).find("option:selected").text();
						else
							fieldvalue = Oehbeitrag._formatDecimalGerman(fieldvalue);
						$("#confirm_"+fieldname+"_"+oehbeitrag_id).parent('td').html(
							fieldvalue+" <i class='fa fa-edit' id='edit_"+fieldname+"_"+oehbeitrag_id+"'></i>"
						);
						$("#edit_"+fieldname+"_"+oehbeitrag_id).click(
							function()
							{
								Oehbeitrag._setUpdateEvent($(this).prop("id"), fieldname, inputtype);
							}
						);
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
						$("#delete_"+oehbeitrag_id).closest("tr").remove();
						Oehbeitrag._addTablesorter();
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
	_renderOehbeitragTable(oehbeitraege)
	{
		for (let oehbeitragidx in oehbeitraege)
		{
			let oehbeitrag = oehbeitraege[oehbeitragidx];


		}
	},
	_setUpdateEvents()
	{
		$(".editStudierendenbeitrag").off('click').click(
			function()
			{
				Oehbeitrag._setUpdateEvent($(this).prop("id"), 'studierendenbeitrag');
			}
		);

		$(".editVersicherung").off('click').click(
			function()
			{
				Oehbeitrag._setUpdateEvent($(this).prop("id"), 'versicherung');
			}
		);

		$(".editBisStudiensemester").off('click').click(
			function()
			{
				Oehbeitrag._setUpdateEvent($(this).prop("id"), 'bis_studiensemester_kurzbz', 'semester');
			}
		);

		$(".editVonStudiensemester").off('click').click(
			function()
			{
				Oehbeitrag._setUpdateEvent($(this).prop("id"), 'von_studiensemester_kurzbz', 'semester');
			}
		);

	},
	_setUpdateEvent(oehbeitrag_id_prefixed, fieldname, inputtype)
	{
		let initElement = $("#"+oehbeitrag_id_prefixed); // clicked element triggering event
		let oehbeitrag_id = oehbeitrag_id_prefixed.substr(oehbeitrag_id_prefixed.lastIndexOf('_')+1);
		let currFieldvalue = initElement.parent('td').text().trim();

		let callback = function(validSemesterData)
		{
			let inputHtml = "";

			// if semester dropdown, retrieve valid semester
			if (inputtype === 'semester')
			{
				if (FHC_AjaxClient.hasData(validSemesterData))
				{
					let studiensemester = FHC_AjaxClient.getData(validSemesterData);

					inputHtml = Oehbeitrag._getStudiensemesterDropdown(oehbeitrag_id, fieldname, studiensemester, currFieldvalue, 'inline-inputfield');
				}
			}
			else // otherwise display textfield
			{
				inputHtml = "<input type='text' class='form-control inline-inputfield' id='input_" + fieldname + "_" + oehbeitrag_id+"'" +
					" value='"+currFieldvalue+"' placeholder='0,00'>";
			}

			inputHtml += " <i class='fa fa-check text-success' id='confirm_"+fieldname+"_"+oehbeitrag_id+"' ></i>";

			initElement.parent('td').html(inputHtml);

			// set the update event
			$("#confirm_"+fieldname+"_"+oehbeitrag_id).click(
				function()
				{
					Oehbeitrag.updateOehbeitrag(oehbeitrag_id, fieldname, $("#input_" + fieldname + "_" + oehbeitrag_id), inputtype);
				}
			);
		}

		if (inputtype == 'semester')
		{
			Oehbeitrag.getValidStudiensemester(callback, oehbeitrag_id);
		}
		else
			callback(null);
	},
	_getStudiensemesterDropdown(oehbeitrag_id, name, studiensemester, selectedDateSemester, formatclass)
	{
		let selectedDateSemesterArr = null;
		if (selectedDateSemester)
		{
			selectedDateSemesterArr = selectedDateSemester.split('/');
		}

		let rowHtml = "";
		let inlineClass = formatclass != null ? ' '+formatclass : '';

		rowHtml += "<select name='"+name+"' id='input_"+name+"_"+oehbeitrag_id+"' class='form-control"+inlineClass+"'>";
		if (name === 'bis_studiensemester_kurzbz')
			rowHtml += "<option value='null'>unbeschränkt</option>";
		for (let idx in studiensemester)
		{
			let date = name === 'bis_studiensemester_kurzbz' ? studiensemester[idx].ende : studiensemester[idx].start;
			let studiensemester_kurzbz = studiensemester[idx].studiensemester_kurzbz;
			let dateSemester = Oehbeitrag._formatDateToGerman(date) + "/" + studiensemester_kurzbz;
			let selected = dateSemester === selectedDateSemester ? ' selected' : '';

			rowHtml += "<option value='" + studiensemester_kurzbz + "' "+selected+">" + dateSemester +"</option>";
		}
		rowHtml += "</select>";

		return rowHtml;
	},
	_compareGermanDates: function(datea, dateb)
	{
		return datea.split(".").reverse().join("") < dateb.split(".").reverse().join("");
	},
	_addTablesorter: function()
	{
		let headers = {headers: { 0: { sorter: false}, 1: { sorter: false}, 4: { sorter: false}}};
		Tablesort.addTablesorter("oehbeitraegeTbl", [[0,0],[1,0]], ["zebra"], 8, headers);
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
	},
	_formatDateToGerman: function(date)
	{
		return date.substring(8, 10) + "." + date.substring(5, 7) + "." + date.substring(0, 4);
	}
};
