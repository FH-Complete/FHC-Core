const BASE_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
const CALLED_PATH = FHC_JS_DATA_STORAGE_OBJECT.called_path;
const CONTROLLER_URL = BASE_URL + "/"+CALLED_PATH;

/**
 * javascript file for Öhbeitrag GUI
 */
$(document).ready(function ()
{
	// get Öhbeiträge and show them in table
	Oehbeitrag.getOehBeitraege();

	// set event for adding new Oehhbeitrag
	$("#addNewOeh").click(
		function()
		{
			let callback = function(data)
			{
				if (FHC_AjaxClient.hasData(data))
				{
					let studiensemester = FHC_AjaxClient.getData(data);
					let nextOehbeitragId = Oehbeitrag.newBeitragId;

					// add new table row
					let newRowHtml = "<tr>"+
										"<td>";
					newRowHtml += Oehbeitrag._getStudiensemesterDropdown(Oehbeitrag.newBeitragId, 'von_studiensemester_kurzbz', studiensemester);
					newRowHtml += 		"</td><td>";
					newRowHtml += Oehbeitrag._getStudiensemesterDropdown(Oehbeitrag.newBeitragId, 'bis_studiensemester_kurzbz', studiensemester);
					newRowHtml += 		"</td>";
					newRowHtml += 		"<td><input type='text' name='studierendenbeitrag' id='input_studierendenbeitrag_"+nextOehbeitragId+"' class='form-control' placeholder='0,00'></td>"+
										"<td><input type='text' name='versicherung' id='input_versicherung_"+nextOehbeitragId+"' class='form-control' placeholder='0,00'></td>"+
										"<td><button class='btn btn-default' id='addOeh_"+nextOehbeitragId+"'>"+FHC_PhrasesLib.t('ui', 'speichern')+"</button>&nbsp;" +
						"						<button class='btn btn-default' id='delete_"+nextOehbeitragId+"'>"+FHC_PhrasesLib.t('ui', 'entfernen')+"</button></td>"+
									"</tr>";

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

					// remove html row if delete button clicked
					$("#delete_"+nextOehbeitragId).click(
						function()
						{
							$(this).parent('td').parent('tr').remove();
						}
					);

					// increase counter for newly added rows id
					Oehbeitrag.newBeitragId++;
				}
				else
					FHC_DialogLib.alertInfo(FHC_PhrasesLib.t('oehbeitrag', 'oehbeitraegeFestgelegt'));
			}

			Oehbeitrag.getValidStudiensemester(callback);
		}
	)
});

var Oehbeitrag = {
	newBeitragId: 0,
	// -----------------------------------------------------------------------------------------------------------------
	// ajax calls
	getOehBeitraege: function()
	{
		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + "/getOehbeitraege",
			null,
			{
				successCallback: function(data, textStatus, jqXHR)
				{
					if (FHC_AjaxClient.isError(data))
					{
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
					}
					else if (FHC_AjaxClient.hasData(data))
					{
						let oehbeitraege = FHC_AjaxClient.getData(data);

						let oehbeitrStr = '';
						for (let idx in oehbeitraege)
						{
							let oehbeitrag = oehbeitraege[idx];

							// add Öhbeitrag row
							oehbeitrStr += '<tr>' +
								'<td id="cell_von_studiensemester_kurzbz_' + oehbeitrag.oehbeitrag_id + '">' +
									Oehbeitrag._formatDateToGerman(oehbeitrag.von_datum) + '/' + oehbeitrag.von_studiensemester_kurzbz +
									'&nbsp;<i class="fa fa-edit editVonStudiensemester" id="edit_von_studiensemester_' + oehbeitrag.oehbeitrag_id + '"></i>'+
								'</td>' +
								'<td id="cell_bis_studiensemester_kurzbz_' + oehbeitrag.oehbeitrag_id + '">' + (oehbeitrag.bis_studiensemester_kurzbz == null ? FHC_PhrasesLib.t('global', 'unbeschraenkt') :
									Oehbeitrag._formatDateToGerman(oehbeitrag.bis_datum) + '/' + oehbeitrag.bis_studiensemester_kurzbz) +
									'&nbsp;<i class="fa fa-edit editBisStudiensemester" id="edit_bis_studiensemester_' + oehbeitrag.oehbeitrag_id + '"></i>'+
								'</td>' +
								'<td id="cell_studierendenbeitrag_' + oehbeitrag.oehbeitrag_id + '">' +  Oehbeitrag._formatDecimal(oehbeitrag.studierendenbeitrag) +
									'&nbsp;<i class="fa fa-edit editStudierendenbeitrag" id="edit_studierendenbeitrag_' + oehbeitrag.oehbeitrag_id + '"></i>'+
								'</td>' +
								'<td id="cell_versicherung_' + oehbeitrag.oehbeitrag_id + '">' + Oehbeitrag._formatDecimal(oehbeitrag.versicherung) +
									'&nbsp;<i class="fa fa-edit editVersicherung" id="edit_versicherung_' + oehbeitrag.oehbeitrag_id + '"></i>'+
								'</td>' +
								'<td>' +
									'<button class="btn btn-default editBtn" id="edit_'+oehbeitrag.oehbeitrag_id+'">'+FHC_PhrasesLib.t('ui', 'bearbeiten')+'</button>' +
									'&nbsp;<button class="btn btn-default deleteBtn" id="delete_'+oehbeitrag.oehbeitrag_id+'">'+FHC_PhrasesLib.t('ui', 'loeschen')+'</button>' +
								'</td>' +
							'</tr>';
						}
						$("#oehbeitraegeTbl tbody").html(oehbeitrStr);

						// set events for editing, deleting etc.
						Oehbeitrag._setUpdateEvents();

						//initialise table sorter
						Oehbeitrag._addTablesorter();
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError(FHC_PhrasesLib.t('oehbeitrag', 'fehlerHolenOehbeitraege'));
				}
			}
		);
	},
	// get all Studiensemester which are valid for assignment (where no Öhbeitrag is assigned)
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
					FHC_DialogLib.alertError(FHC_PhrasesLib.t('oehbeitrag', 'fehlerHolenSemester'));
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
						// refresh Öhbeitragstable
						Oehbeitrag.getOehBeitraege();
					}
					else
					{
						FHC_DialogLib.alertError(FHC_PhrasesLib.t('oehbeitrag', 'fehlerHinzufuegenOehbeitrag'));
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError(FHC_PhrasesLib.t('oehbeitrag', 'fehlerHinzufuegenOehbeitrag'));
				}
			}
		);
	},
	// update whole Öhbeitrag
	updateOehbeitrag: function(oehbeitrag_id, oehbeitragData)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/updateOehbeitrag',
			{
				"oehbeitrag_id": oehbeitrag_id,
				"data": oehbeitragData
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isError(data))
					{
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
					}
					else if (FHC_AjaxClient.hasData(data))
					{
						// refresh Öhbeitragstable
						Oehbeitrag.getOehBeitraege();
					}
					else
					{
						FHC_DialogLib.alertError(FHC_PhrasesLib.t('oehbeitrag', 'fehlerAktualisierenOehbeitrag'));
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError(FHC_PhrasesLib.t('oehbeitrag', 'fehlerAktualisierenOehbeitrag'));
				}
			}
		);
	},
	// update one field of Öhbeitrag (e.g. only semester or only Betrag)
	updateOehbeitragField: function(oehbeitrag_id, fieldname, fieldelement, inputtype)
	{
		let oehbeitragdata = {};
		let fieldvalue = fieldelement.val();

		if (inputtype != 'semester') // formal number as decimal with point separator
			fieldvalue = Oehbeitrag._formatDecimal(fieldvalue, ".");

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
						// refresh table cell with correct value and set edit event and tablesorter
						if (inputtype == 'semester')
							fieldvalue = $(fieldelement).find("option:selected").text();
						else
							fieldvalue = Oehbeitrag._formatDecimal(fieldvalue);
						$("#confirm_"+fieldname+"_"+oehbeitrag_id).parent('td').html(
							fieldvalue+" <i class='fa fa-edit' id='edit_"+fieldname+"_"+oehbeitrag_id+"'></i>"
						);
						$("#edit_"+fieldname+"_"+oehbeitrag_id).click(
							function()
							{
								Oehbeitrag._makeFieldEditable($(this).prop("id"), fieldname, inputtype, true);
							}
						);

						Oehbeitrag._addTablesorter();
					}
					else
					{
						FHC_DialogLib.alertError(FHC_PhrasesLib.t('oehbeitrag', 'fehlerAktualisierenOehbeitrag'));
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError(FHC_PhrasesLib.t('oehbeitrag', 'fehlerAktualisierenOehbeitrag'));
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
						FHC_DialogLib.alertError(FHC_PhrasesLib.t('oehbeitrag', 'fehlerLoeschenOehbeitrag'));
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError(FHC_PhrasesLib.t('oehbeitrag', 'fehlerLoeschenOehbeitrag'));
				}
			}
		);
	},

	// -----------------------------------------------------------------------------------------------------------------
	/**
	 * Sets click events for updating, deleting Oehbeitrag
	 */
	_setUpdateEvents: function()
	{
		// set edit event for whole row
		$(".editBtn").click(
			function()
			{
				let id = $(this).prop("id");
				let oehbeitrag_id = id.substr(id.lastIndexOf('_')+1);
				Oehbeitrag._makeFieldEditable(id, 'von_studiensemester_kurzbz', 'semester');
				Oehbeitrag._makeFieldEditable(id, 'bis_studiensemester_kurzbz', 'semester');
				Oehbeitrag._makeFieldEditable(id, 'studierendenbeitrag');
				Oehbeitrag._makeFieldEditable(id, 'versicherung');
				$(this).after("&nbsp;<button class='btn btn-default saveBtn' id='save_"+oehbeitrag_id+"'>"+FHC_PhrasesLib.t('ui', 'speichern')+"</button>");
				$(this).remove();
				$("#delete_" + oehbeitrag_id).remove();

				$("#save_"+oehbeitrag_id).click(
					function()
					{
						let von_studiensemester_kurzbz = $("#input_von_studiensemester_kurzbz_"+oehbeitrag_id+" option:selected").val();
						let bis_studiensemester_kurzbz = $("#input_bis_studiensemester_kurzbz_"+oehbeitrag_id+" option:selected").val();
						let studierendenbeitrag = Oehbeitrag._formatDecimal($("#input_studierendenbeitrag_"+oehbeitrag_id).val(), '.');
						let versicherung = Oehbeitrag._formatDecimal($("#input_versicherung_"+oehbeitrag_id).val(), '.');

						let oehbeitragData = {
							"von_studiensemester_kurzbz": von_studiensemester_kurzbz,
							"bis_studiensemester_kurzbz": bis_studiensemester_kurzbz,
							"studierendenbeitrag": studierendenbeitrag,
							"versicherung": versicherung
						}

						Oehbeitrag.updateOehbeitrag(oehbeitrag_id, oehbeitragData);
					}
				)
			}
		);

		// set delete event for all rows
		$(".deleteBtn").click(
			function()
			{
				let oehbeitrag_id_prefixed = $(this).prop("id");
				let oehbeitrag_id = oehbeitrag_id_prefixed.substr(oehbeitrag_id_prefixed.lastIndexOf('_')+1);

				Oehbeitrag.deleteOehbeitrag(oehbeitrag_id);
			}
		)

		// set edit events for single cells
		$(".editStudierendenbeitrag").off('click').click(
			function()
			{
				Oehbeitrag._makeFieldEditable($(this).prop("id"), 'studierendenbeitrag', null, true);
			}
		);

		$(".editVersicherung").off('click').click(
			function()
			{
				Oehbeitrag._makeFieldEditable($(this).prop("id"), 'versicherung', null, true);
			}
		);

		$(".editBisStudiensemester").off('click').click(
			function()
			{
				Oehbeitrag._makeFieldEditable($(this).prop("id"), 'bis_studiensemester_kurzbz', 'semester', true);
			}
		);

		$(".editVonStudiensemester").off('click').click(
			function()
			{
				Oehbeitrag._makeFieldEditable($(this).prop("id"), 'von_studiensemester_kurzbz', 'semester', true);
			}
		);
	},
	// make Öhbeitrag field editable, i.e. show input field instead of text
	_makeFieldEditable: function(oehbeitrag_id_prefixed, fieldname, inputtype, singleUpdate)
	{
		let oehbeitrag_id = oehbeitrag_id_prefixed.substr(oehbeitrag_id_prefixed.lastIndexOf('_')+1);
		let initElement = $("#cell_"+fieldname+"_"+oehbeitrag_id); // clicked element triggering event
		let currFieldvalue = initElement.text().trim();

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

			if (singleUpdate === true)
				inputHtml += " <i class='fa fa-check text-success' id='confirm_"+fieldname+"_"+oehbeitrag_id+"' ></i>";

			initElement.html(inputHtml);

			if (singleUpdate === true)
			{
				// set the update event if single field update
				$("#confirm_" + fieldname + "_" + oehbeitrag_id).click(
					function()
					{
						Oehbeitrag.updateOehbeitragField(oehbeitrag_id, fieldname, $("#input_" + fieldname + "_" + oehbeitrag_id), inputtype);
					}
				);
			}
		}

		// get valid Studiensemester with no Öhbeitrag assigned
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
		// add parser through the tablesorter addParser method
		$.tablesorter.addParser({
			// set a unique id
			id: 'germandatesort',
			is: function(s, table, cell, $cell) {
				// return false so this parser is not auto detected
				return false;
			},
			format: function(s, table, cell, cellIndex) {
				// format data, should sort by leading german date
				return s.substring(0, 10).split(".").reverse().join("");
			},
			// set type, either numeric or text
			type: 'numeric'
		});

		let headers = {headers: { 0: {sorter: "germandatesort"}, 1: {sorter: "germandatesort"}, 4: {sorter: false}}};

		Tablesort.addTablesorter("oehbeitraegeTbl", [[0,1]], ["zebra"], 8, headers);
	},
	// Formats a numeric value as a float with two decimals
	_formatDecimal: function(value, decSeparator)
	{
		let dec = null;
		let prevSeparator = ".";

		if (decSeparator === ".")
			prevSeparator = ",";
		else
			decSeparator = ",";

		dec = value.split(prevSeparator);
		if (dec.length === 2)
		{
			dec = parseFloat(dec[0] + '.' + dec[1]).toFixed(2);
			dec = dec.replace(prevSeparator, decSeparator);
		}
		else if (Math.floor(value) == value) // if integer, add zeros
			dec = value + decSeparator + '00';
		else
			dec = value;

		return dec;
	},
	// formats english date to as german
	_formatDateToGerman: function(date)
	{
		return date.substring(8, 10) + "." + date.substring(5, 7) + "." + date.substring(0, 4);
	}
};
