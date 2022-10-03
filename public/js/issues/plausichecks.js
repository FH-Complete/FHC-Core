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

					if (FHC_AjaxClient.hasData(data))
					{
						let messageStr = "";
						let messages = FHC_AjaxClient.getData(data);

						for (let i = 0; i < messages.length; i++)
						{
							messageStr += messages[i]+"<br />";
						}

						$("#plausioutput").html(messageStr);
					}
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
	// set event for adding a new Zuständigkeit
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
