/**
 * Javascript file for Plausichecks display
 */

var Plausichecks = {

	startPlausichecks: function (studiensemester_kurzbz, studiengang_kz, fehler_kurzbz) {
		FHC_AjaxClient.ajaxCallGet(
			'system/issues/Plausichecks/runChecks',
			{
				studiensemester_kurzbz: studiensemester_kurzbz,
				studiengang_kz: studiengang_kz,
				fehler_kurzbz: fehler_kurzbz
			},
			{
				successCallback: function (data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isError(data)) FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));

					let messageStr = "<strong>Plausichecks Prüfung Start</strong>";

					if (FHC_AjaxClient.hasData(data))
					{
						let issueTexts = FHC_AjaxClient.getData(data);

						for (let fehler_kurzbz in issueTexts)
						{
							messageStr += "<br /><br /><span>Prüfe " + fehler_kurzbz + "...</span>";
							let texts = issueTexts[fehler_kurzbz];

							if (texts.length == 0) {
								messageStr += "<br /><span class='text-success'>Keine Issues für " + fehler_kurzbz + "</span>";
								continue;
							}

							for (i = 0; i < texts.length; i++)
							{
								messageStr += "<br /><span class='text-danger'>" + texts[i] + "</span>";
							}
						}
					}
					messageStr += "<br /><br /><strong>Plausichecks Prüfung Ende</strong>";
					$("#plausioutput").html(messageStr);
				},
				errorCallback: function (jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},

};

/**
 * When JQuery is up
 */
$(document).ready(function () {
	// start the Plausicheck run on button click
	$("#plausistart").click(
		function () {
			Plausichecks.startPlausichecks(
				$("#studiensemester").val(),
				$("#studiengang_kz").val(),
				$("#fehler_kurzbz").val(),
			);
		}
	)
});
