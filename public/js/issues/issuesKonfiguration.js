/**
 * Javascript file for Issues Zuständigkeiten assignment page
 */

const FEHLERAPP_DROPDOWN_ID = "fehlerappSelect";
const FEHLERCODE_DROPDOWN_ID = "fehlercodeSelect";
const FEHLERKONFIGURATIONSTYP_DROPDOWN_ID = "konfigSelect";

var IssuesKonfiguration = {

	fehlerkonfigArr: [], // for saving received fehlerkonfigs
	fehlercodesArr: [], // for saving received fehlercodes

	getApps: function()
	{
		FHC_AjaxClient.ajaxCallGet(
			'system/issues/IssuesKonfiguration/getApps',
			null,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						// save loaded apps
						let apps = FHC_AjaxClient.getData(data);

						// fill dropdown with apps
						IssuesKonfiguration._fillDropdown(
							FEHLERAPP_DROPDOWN_ID,
							"app",
							apps,
							null,
							false,
							"core"
						);

						// Initiate getting of fehlercodes with apps
						IssuesKonfiguration.getFehlerKonfigurationByApp($("#"+FEHLERAPP_DROPDOWN_ID).val());
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	getFehlerKonfigurationByApp: function(app)
	{
		FHC_AjaxClient.ajaxCallGet(
			'system/issues/IssuesKonfiguration/getFehlerKonfigurationByApp',
			{
				app: app
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						let fehlerKonfigurationData = FHC_AjaxClient.getData(data);

						// save konfig and fehler data for displaying info later
						IssuesKonfiguration.fehlerkonfigArr = fehlerKonfigurationData.konfigurationstypen;
						IssuesKonfiguration.fehlercodesArr = fehlerKonfigurationData.fehler;

						// display fehlercodes in dropdown
						let fehlerCodes = [];

						for (let i = 0; i < fehlerKonfigurationData.fehler.length; i++)
						{
							let fe = fehlerKonfigurationData.fehler[i];

							fehlerCodes.push(
								{
									fehlercode: fe.fehlercode,
									fullFehlerBezeichnung: fe.fehlercode + (!fe.fehler_kurzbz ? '' : ' - ' + fe.fehler_kurzbz)
								}
							);
						}

						IssuesKonfiguration._fillDropdown(
							FEHLERCODE_DROPDOWN_ID,
							"fehlercode",
							fehlerCodes,
							"fullFehlerBezeichnung"
						);

						// display fehlerkonfiguration in dropdown
						let konfigurationstypen = [];

						for (let i = 0; i < fehlerKonfigurationData.konfigurationstypen.length; i++)
						{
							let konf = fehlerKonfigurationData.konfigurationstypen[i];

							konfigurationstypen.push(
								{
									konfigurationstyp_kurzbz: konf.konfigurationstyp_kurzbz
								}
							);
						}

						IssuesKonfiguration._fillDropdown(
							FEHLERKONFIGURATIONSTYP_DROPDOWN_ID,
							"konfigurationstyp_kurzbz",
							konfigurationstypen
						);

						// set delete event on buttons
						IssuesKonfiguration._setDeleteEvents();
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	saveFehlerKonfiguration: function(konfigurationstyp_kurzbz, fehlercode, konfigurationsWert)
	{
		FHC_AjaxClient.ajaxCallPost(
			'system/issues/IssuesKonfiguration/saveFehlerKonfiguration',
			{
				konfigurationstyp_kurzbz: konfigurationstyp_kurzbz,
				fehlercode: fehlercode,
				konfigurationsWert: konfigurationsWert
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						// show message, reset input fields and reload data after Zuständigkeit added
						FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t('fehlermonitoring', 'konfigurationGespeichert'))
						IssuesKonfiguration._reloadKonfigurationOverview();
					}
					else
					{
						// show error if no data
						let errorMsg = FHC_PhrasesLib.t('fehlermonitoring', 'konfigurationGespeichertFehler');
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
	deleteKonfigurationsWerte: function(konfigurationstyp_kurzbz, fehlercode, konfigurationsWert)
	{
		FHC_AjaxClient.ajaxCallPost(
			'system/issues/IssuesKonfiguration/deleteKonfigurationsWerte',
			{
				konfigurationstyp_kurzbz: konfigurationstyp_kurzbz,
				fehlercode: fehlercode,
				konfigurationsWert: konfigurationsWert
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t('fehlermonitoring', 'konfigurationGeloescht'))
						// reload dataset to see change
						IssuesKonfiguration._reloadKonfigurationOverview();
					}
					else
					{
						FHC_DialogLib.alertError(FHC_PhrasesLib.t('fehlermonitoring', 'konfigurationGeloeschtFehler'));
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	deleteKonfiguration: function(konfigurationstyp_kurzbz, fehlercode)
	{
		FHC_AjaxClient.ajaxCallPost(
			'system/issues/IssuesKonfiguration/deleteKonfiguration',
			{
				konfigurationstyp_kurzbz: konfigurationstyp_kurzbz,
				fehlercode: fehlercode
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						FHC_DialogLib.alertSuccess(FHC_PhrasesLib.t('fehlermonitoring', 'konfigurationGeloescht'))
						// reload dataset to see change
						IssuesKonfiguration._reloadKonfigurationOverview();
					}
					else
					{
						FHC_DialogLib.alertError(FHC_PhrasesLib.t('fehlermonitoring', 'konfigurationGeloeschtFehler'));
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},
	_reloadKonfigurationOverview: function()
	{
		FHC_FilterWidget.reloadDataset();
		IssuesKonfiguration._setDeleteEvents();
	},
	_setDeleteEvents: function()
	{
		// set events on delete buttons
		$(".deleteBtn").click(
			function()
			{
				IssuesKonfiguration.deleteKonfiguration($(this).attr("data-konfigurationstyp-kurzbz"), $(this).attr("data-fehlercode"));
			}
		)
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
	}
};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	// initiate cascade of getting data, first apps
	IssuesKonfiguration.getApps();

	// get new fehlercodes each time app is changed
	$("#"+FEHLERAPP_DROPDOWN_ID).change(
		function()
		{
			IssuesKonfiguration.getFehlerKonfigurationByApp($(this).val());
		}
	);

	// set assign configuration event
	$("#assignKonfiguration").click(
		function()
		{
			IssuesKonfiguration.saveFehlerKonfiguration(
				$("#"+FEHLERKONFIGURATIONSTYP_DROPDOWN_ID).val(),
				$("#"+FEHLERCODE_DROPDOWN_ID).val(),
				$("#konfigurationsWert").val()
			);
		}
	);

	// set delete configuration event
	$("#deleteKonfiguration").click(
		function()
		{
			IssuesKonfiguration.deleteKonfigurationsWerte(
				$("#"+FEHLERKONFIGURATIONSTYP_DROPDOWN_ID).val(),
				$("#"+FEHLERCODE_DROPDOWN_ID).val(),
				$("#konfigurationsWert").val()
			);
		}
	);

	// set events for showing info modals
	$("#fehlercodeInfoIcon").click(
		function()
		{
			let fehlercode = $("#"+FEHLERCODE_DROPDOWN_ID).val();
			let fehlercodeData = {};

			for (let i = 0; i < IssuesKonfiguration.fehlercodesArr.length; i++)
			{
				let fc = IssuesKonfiguration.fehlercodesArr[i];
				console.log(fc);

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
			$("#fehlertextInfo").text(fehlercodeData.fehlertext);

			$("#fehlerInfo").modal("show");
		}
	);

	$("#konfigurationstypInfoIcon").click(
		function()
		{
			let konfigurationstyp_kurzbz = $("#"+FEHLERKONFIGURATIONSTYP_DROPDOWN_ID).val();
			let konfigurationstypData = {};

			for (let i = 0; i < IssuesKonfiguration.fehlerkonfigArr.length; i++)
			{
				let konf = IssuesKonfiguration.fehlerkonfigArr[i];
				console.log(konf);

				if (konf.konfigurationstyp_kurzbz === konfigurationstyp_kurzbz)
				{
					konfigurationstypData = konf;
					break;
				}
			}

			if (!konfigurationstypData)
				return;

			$("#konfigurationstypInfo").text(konfigurationstypData.konfigurationstyp_kurzbz);
			$("#konfigurationsbeschreibungInfo").text(konfigurationstypData.beschreibung);
			$("#konfigurationsdatentypInfo").text(konfigurationstypData.konfigurationsdatentyp);

			$("#konfigurationsInfo").modal("show");
		}
	);

});
