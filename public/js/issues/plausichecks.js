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
						let issueData = FHC_AjaxClient.getData(data);

						for (let fehler_kurzbz in issueData)
						{
							let issues = issueData[fehler_kurzbz]['data'];
							messageStr += "<br /><br /><span>Prüfe " + fehler_kurzbz + " ("+issueData[fehler_kurzbz]['fehlercode']+")...</span>";

							if (issues.length == 0) {
								messageStr += "<br /><span class='text-success'>Keine Issues für " + fehler_kurzbz + "</span>";
								continue;
							}

							for (i = 0; i < issues.length; i++)
							{
								let className = issues[i].type == 'warning' ? 'text-warning' : 'text-danger';
								messageStr += "<br /><span class='"+className+"'>" + issues[i].fehlertext + "</span>";
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
	}

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
