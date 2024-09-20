/**
 * Javascript file for Issues Zuständigkeiten assignment page
 */

const FEHLERAPP_DROPDOWN_ID = "fehlerappSelect";
const FEHLERCODE_DROPDOWN_ID = "fehlercodeSelect";
const MITARBEITER_AUTOCOMPLETE_ID = "mitarbeiterSelect";
const MITARBEITER_HIDDENFIELD_ID = "mitarbeiter_person_id";
const ORGANISATIONSEINHEIT_DROPDOWN_ID = "oeSelect";
const FUNKTION_DROPDOWN_ID = "funktionSelect";

var IssuesZustaendigkeiten = {

	fehlercodesArr: [], // for saving received fehlercodes
	oefunktionen: [], // for saving assigned oes and their funktionen

	getApps: function()
	{
		FHC_AjaxClient.ajaxCallGet(
			'system/issues/IssuesZustaendigkeiten/getApps',
			null,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						// save loaded apps
						let apps = FHC_AjaxClient.getData(data);

						// fill dropdown with apps
						IssuesZustaendigkeiten._fillDropdown(
							FEHLERAPP_DROPDOWN_ID,
							"app",
							apps,
							null,
							false,
							"core"
						);

						// Initiate getting of fehlercodes with apps
						IssuesZustaendigkeiten.getFehlercodes($("#"+FEHLERAPP_DROPDOWN_ID).val());
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	getFehlercodes: function(app)
	{
		FHC_AjaxClient.ajaxCallGet(
			'system/issues/IssuesZustaendigkeiten/getFehlercodes',
			{
				app: app
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						let fehlerCodesData = FHC_AjaxClient.getData(data);

						// save fehlercodes data for displaying info later
						IssuesZustaendigkeiten.fehlercodesArr = fehlerCodesData;

						// display fehlercodes in dropdown
						let fehlerCodes = [];

						for (let i = 0; i < fehlerCodesData.length; i++)
						{
							let code = fehlerCodesData[i];

							fehlerCodes.push(
								{
									fehlercode: code.fehlercode,
									fullFehlerBezeichnung: code.fehlercode + (!code.fehler_kurzbz ? '' : ' - ' + code.fehler_kurzbz)
								}
							);
						}

						IssuesZustaendigkeiten._fillDropdown(
							FEHLERCODE_DROPDOWN_ID,
							"fehlercode",
							fehlerCodes,
							"fullFehlerBezeichnung"
						);

						// initiate call for getting Zuständigkeiten
						IssuesZustaendigkeiten.getNonAssignedZustaendigkeiten($("#"+FEHLERCODE_DROPDOWN_ID).val());
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	getNonAssignedZustaendigkeiten: function(fehlercode)
	{
		FHC_AjaxClient.ajaxCallGet(
			'system/issues/IssuesZustaendigkeiten/getNonAssignedZustaendigkeiten',
			{
				fehlercode: fehlercode
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						// save loaded data
						let zustaendigkeitenData = FHC_AjaxClient.getData(data);
						// save in object to display correct funktionen when oe is changed
						IssuesZustaendigkeiten.oe_funktionen = zustaendigkeitenData.oe_funktionen;

						let zustaendigkeiten = [];

						for (let i = 0; i < zustaendigkeitenData.oe_funktionen.length; i++)
						{
							let zustaendigkeit = zustaendigkeitenData.oe_funktionen[i];

							zustaendigkeiten.push(
								{
									oe_kurzbz: zustaendigkeit.oe_kurzbz,
									fullOeBezeichnung: zustaendigkeit.organisationseinheittyp_kurzbz + ' ' + zustaendigkeit.bezeichnung
								}
							);
						}

						// fill oe dropdown with data
						IssuesZustaendigkeiten._fillDropdown(
							"oeSelect",
							"oe_kurzbz",
							zustaendigkeiten,
							"fullOeBezeichnung",
							true
						);

						// fill funktion dropdown with data
						IssuesZustaendigkeiten._fillFunktionDropdown();

						// save Mitarbeiter data for autocomplete field in array
						let autocompleteMitarbeiterArr = [];

						for (let i = 0; i < zustaendigkeitenData.mitarbeiter.length; i++)
						{
							let ma = zustaendigkeitenData.mitarbeiter[i];

							autocompleteMitarbeiterArr.push(
								{
									vorname: ma.vorname,
									nachname: ma.nachname,
									uid: ma.uid,
									label: ma.nachname + " " + ma.vorname + " (" + ma.uid + ")",
									id: ma.person_id
								}
							);
						}

						// callback for searching source mitarbeiter array correctly
						let sourceCallback = function(request, response)
						{
							// case insensitive matcher
							var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");

							// match vorname nachname OR nachname vorname OR uid
							response($.grep(autocompleteMitarbeiterArr, function (value) {
								return matcher.test(value.nachname + ' '+value.vorname + ' ' + value.nachname)
									|| matcher.test(value.uid);
							}));
						}

						// fill autocomplete field with mitarbeiter data
						IssuesZustaendigkeiten._fillAutocomplete(
							MITARBEITER_AUTOCOMPLETE_ID,
							MITARBEITER_HIDDENFIELD_ID,
							sourceCallback
						);

						// set events on delete buttons
						$(".deleteBtn").click(
							function()
							{
								IssuesZustaendigkeiten.deleteZustaendigkeit($(this).prop("id"));
							}
						)
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);

	},
	addZustaendigkeit: function(data)
	{
		FHC_AjaxClient.ajaxCallPost(
			'system/issues/IssuesZustaendigkeiten/addZustaendigkeit',
			data,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						// show message, reset input fields and reload data after Zuständigkeit added
						FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t('fehlermonitoring', 'zustaendigkeitGespeichert'))
						IssuesZustaendigkeiten._emptyInputFields();
						FHC_FilterWidget.reloadDataset();
					}
					else
					{
						// show error if no data
						let errorMsg = FHC_PhrasesLib.t('fehlermonitoring', 'zustaendigkeitGespeichertFehler');
						if (FHC_AjaxClient.isError(data))
							errorMsg += ": "+FHC_AjaxClient.getError(data);
						FHC_DialogLib.alertError(errorMsg);
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	deleteZustaendigkeit: function(fehlerzustaendigkeiten_id)
	{
		FHC_AjaxClient.ajaxCallPost(
			'system/issues/IssuesZustaendigkeiten/deleteZustaendigkeit',
			{
				fehlerzustaendigkeiten_id: fehlerzustaendigkeiten_id
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t('fehlermonitoring', 'zustaendigkeitGeloescht'))
						// reload dataset to see change
						FHC_FilterWidget.reloadDataset();
					}
					else
					{
						FHC_DialogLib.alertError(FHC_PhrasesLib.t('fehlermonitoring', 'zustaendigkeitGeloeschtFehler'));
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	_fillFunktionDropdown: function()
	{
		let funktionen = [];
		let oe_kurzbz = $("#"+ORGANISATIONSEINHEIT_DROPDOWN_ID).val();

		// get funktionen for selected oe (saved in js object)
		for (let i = 0; i < IssuesZustaendigkeiten.oe_funktionen.length; i++)
		{
			let oe_funktion = IssuesZustaendigkeiten.oe_funktionen[i];

			if (oe_funktion.oe_kurzbz === oe_kurzbz)
			{
				funktionen = oe_funktion.funktionen
				break;
			}
		}

		IssuesZustaendigkeiten._fillDropdown(
			"funktionSelect",
			"funktion_kurzbz",
			funktionen,
			"beschreibung",
			true
		);
	},
	_fillDropdown: function(dropdownId, valueName, data, textName, includeNoSelectionOption, defaultValue)
	{
		// by default, displayed text in dropdown is the value
		if (!textName)
			textName = valueName;

		// clear dropdown
		$("#"+dropdownId).empty();

		// optionally include default "no selection" value
		if (includeNoSelectionOption === true)
			$("#"+dropdownId).append("<option value=''> -- "+FHC_PhrasesLib.t('fehlermonitoring', 'keineAuswahl')+" -- </option>");

		// fill dropdown with values
		for (let i = 0; i < data.length; i++)
		{
			let val = data[i];

			// the value selected by default
			let selected = val[valueName] === defaultValue ? " selected" : "";

			// append option
			$("#"+dropdownId).append("<option value='"+val[valueName]+"'"+selected+">"+val[textName]+"</option>");
		}
	},
	_fillAutocomplete: function(autocompleteId, idFieldId, source)
	{
		// jQuery ui autocomplete for employees
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
	},
	_toggleFieldDisability: function()
	{
		let oeDropdownEl = $("#"+ORGANISATIONSEINHEIT_DROPDOWN_ID);
		let funktionDropdownEl = $("#"+FUNKTION_DROPDOWN_ID);
		let maAutocompleteEl = $("#"+MITARBEITER_AUTOCOMPLETE_ID);
		let maHiddenEl = $("#"+MITARBEITER_HIDDENFIELD_ID);

		// if Mitarbeiter is entered
		if (maAutocompleteEl.val().length > 0)
		{
			// disable oe and funktion input
			oeDropdownEl.prop('disabled', true);
			oeDropdownEl.val('');
			funktionDropdownEl.prop('disabled', true);
		}
		else
		{
			// otherwise enable oe input if Mitarbeiter input empty
			oeDropdownEl.prop('disabled', false);
		}

		// if oe is entered
		if (oeDropdownEl.val().length > 0)
		{
			// disable Mitarbeiter input
			maAutocompleteEl.prop('disabled', true);
			maAutocompleteEl.val('');
			maHiddenEl.val('');

			// enable funktion input
			funktionDropdownEl.prop('disabled', false);
			IssuesZustaendigkeiten._fillFunktionDropdown();
		}
		else
		{
			// otherwise enable mitarbeiter input
			maAutocompleteEl.prop('disabled', false);

			// disable funktion input
			funktionDropdownEl.prop('disabled', true);
		}
	},
	_emptyInputFields: function()
	{
		// clear all input fields
		$("#"+MITARBEITER_AUTOCOMPLETE_ID).val('');
		$("#"+MITARBEITER_HIDDENFIELD_ID).val('');
		$("#"+ORGANISATIONSEINHEIT_DROPDOWN_ID).val('');
		$("#"+FUNKTION_DROPDOWN_ID).val('');
	}
};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	// initiate cascade of getting data, first apps
	IssuesZustaendigkeiten.getApps();

	// get new fehlercodes each time app is changed
	$("#"+FEHLERAPP_DROPDOWN_ID).change(
		function()
		{
			IssuesZustaendigkeiten.getFehlercodes($(this).val());
		}
	);

	// get new zustaendigkeiten every time Fehlercode is changed
	$("#"+FEHLERCODE_DROPDOWN_ID).change(
		function()
		{
			IssuesZustaendigkeiten.getNonAssignedZustaendigkeiten($(this).val());
		}
	);

	// set events for disabling input fields
	$("#"+MITARBEITER_AUTOCOMPLETE_ID).keyup(IssuesZustaendigkeiten._toggleFieldDisability);
	$("#"+ORGANISATIONSEINHEIT_DROPDOWN_ID).change(IssuesZustaendigkeiten._toggleFieldDisability);

	// set event for adding a new Zuständigkeit
	$("#assignZustaendigkeit").click(
		function()
		{
			let data = {
				fehlercode: $("#"+FEHLERCODE_DROPDOWN_ID).val()
			}

			let mitarbeiter_person_id = $("#"+MITARBEITER_HIDDENFIELD_ID).val();
			let oe_kurzbz = $("#"+ORGANISATIONSEINHEIT_DROPDOWN_ID).val();

			// if person id set, send it, otherwise oe_kurzbz
			if (mitarbeiter_person_id.length > 0)
				data.mitarbeiter_person_id = mitarbeiter_person_id;
			else if (oe_kurzbz.length > 0)
			{
				data.oe_kurzbz = oe_kurzbz
				data.funktion_kurzbz = $("#"+FUNKTION_DROPDOWN_ID).val();
			}

			IssuesZustaendigkeiten.addZustaendigkeit(data);
		}
	)

	// set event for showing info modal
	$("#fehlercodeInfoCell").click(
		function()
		{
			let fehlercode = $("#"+FEHLERCODE_DROPDOWN_ID).val();
			let fehlercodeData = {};

			for (let i = 0; i < IssuesZustaendigkeiten.fehlercodesArr.length; i++)
			{
				let fc = IssuesZustaendigkeiten.fehlercodesArr[i];

				if (fc.fehlercode === fehlercode)
				{
					fehlercodeData = fc;
					break;
				}
			}

			if (!fehlercodeData)
				return;

			$("#fehlerInfoLabel").text(fehlercodeData.fehlercode + " - " + fehlercodeData.fehler_kurzbz);
			$("#fehlercodeInfo").text(fehlercodeData.fehlercode);
			$("#fehlerkurzbzInfo").text(fehlercodeData.fehler_kurzbz);
			$("#fehlertypInfo").text(fehlercodeData.fehlertyp_kurzbz);
			$("#fehlercodeExternInfo").text(fehlercodeData.fehlercode_extern ? fehlercodeData.fehlercode_extern : '-');
			$("#fehlertextInfo").text(fehlercodeData.fehlertext);

			$("#fehlerInfo").modal("show");
		}
	)
});
