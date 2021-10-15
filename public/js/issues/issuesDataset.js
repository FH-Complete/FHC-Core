/**
 * Javascript file for issues overview page
 */

var IssuesDataset = {

	/**
	 * adds person table additional actions html (above and beneath it)
	 */
	appendTableActionsHtml: function()
	{
		let auswahlStatus =
			'<div class="input-group">' +
			'<select class="form-control d-inline auswahlStatus">' +
			'<option value="resolved"> Behoben </option>' +
			'<option value="inProgress"> in Bearbeitung </option>' +
			'<option value="new"> Neu </option>' +
			'</select>' +
			'<span class="input-group-btn">' +
			'<button class="btn btn-default setStatus">Status für Ausgew&auml;hlte setzen</button>' +
			'</span>' +
			'</div>';

		let selectAllHtml =
			'<a href="javascript:void(0)" class="selectAll">' +
			'<i class="fa fa-check"></i>&nbsp;Alle</a>&nbsp;&nbsp;' +
			'<a href="javascript:void(0)" class="unselectAll">' +
			'<i class="fa fa-times"></i>&nbsp;Keinen</a>&nbsp;&nbsp;&nbsp;&nbsp;';

		let issuescount = 0;

		FHC_AjaxClient.ajaxCallGet(
			'widgets/Filters/rowNumber',
			{
				filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						issuescount = FHC_AjaxClient.getData(data);

						if (issuescount > 0)
						{
							var countHtml = issuescount + " Fehler";

							// Count Records after Filtering
							$("#filterTableDataset").bind("filterEnd", function() {
								var cnt = $("#filterTableDataset tr:visible").length - 2;
								$(".filterTableDatasetCntFiltered").html(cnt + ' / ');
							});

							$("#datasetActionsTop, #datasetActionsBottom").append(
								"<div class='row'>"+
								"<div class='col-xs-3  datasetActionsTopBottomText'>" + selectAllHtml + "</div>"+
								"<div class='col-xs-6'>" + auswahlStatus + "</div>"+
								"<div class='col-xs-3 datasetActionsTopBottomText text-right'>" +
								"<span class='filterTableDatasetCntFiltered'></span>" +
								countHtml +	"</div>"+
								"</div>"+
								"<div>"+
								"<hr class='datasetActionsHorizontalLine'>"+
								"</div>"
							);
							$("#datasetActionsBottom").append("<br><br>");

							IssuesDataset.setTableActions();
						}
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(textStatus);
				}
			}
		);
	},

	/**
	 * sets functionality for the actions above and beneath the person table
	 */
	setTableActions: function()
	{
		$(".setStatus").click(function()
			{
				let status_kurzbz = $(".auswahlStatus").val();
				let issue_ids_sel = $("#filterTableDataset input:checked[name=issue_id\\[\\]]");

				if(status_kurzbz.length <= 0)
					return FHC_DialogLib.alertInfo("Bitte wählen Sie den Status aus.");

				if(issue_ids_sel.length <= 0)
					return FHC_DialogLib.alertInfo("Bitte wählen Sie die Fehler aus.");

				let issue_ids = [];

				for (let i = 0; i < issue_ids_sel.length; i++)
				{
					issue_ids.push($(issue_ids_sel[i]).val());
				}

				FHC_AjaxClient.ajaxCallPost(
					'system/issues/Issues/changeIssueStatus',
					{
						"issue_ids": issue_ids,
						"status_kurzbz": status_kurzbz
					},
					{
						successCallback: function(data, textStatus, jqXHR) {
							if (FHC_AjaxClient.isError(data))
								FHC_DialogLib.alertError("Fehler beim Status &Auml;ndern: " + FHC_AjaxClient.getError(data));
							else if (FHC_AjaxClient.hasData(data))
							{
								FHC_FilterWidget.reloadDataset();
								FHC_DialogLib.alertSuccess(FHC_AjaxClient.getData(data));
							}
							else
								FHC_DialogLib.alertError("Unbekannter Fehler beim Status &Auml;ndern");
						},
						errorCallback: function(jqXHR, textStatus, errorThrown) {
							FHC_DialogLib.alertError("Fehler beim Status &Auml;ndern: " + textStatus);
						}
					}
				);
			}
		);

		$(".selectAll").click(function()
			{
				//select only trs if not filtered by tablesorter
				var trs = $("#filterTableDataset tbody tr").not(".filtered");
				trs.find("input[name=issue_id\\[\\]]").prop("checked", true);
			}
		);

		$(".unselectAll").click(function()
			{
				var trs = $("#filterTableDataset tbody tr").not(".filtered");
				trs.find("input[name=issue_id\\[\\]]").prop("checked", false);
			}
		);
	}
};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	IssuesDataset.appendTableActionsHtml();

});
