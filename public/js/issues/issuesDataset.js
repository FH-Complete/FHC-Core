/**
 * Javascript file for issues overview page
 */

var IssuesDataset = {
	// number of max issue ids that can be sent in one request for status change
	maxIssuesPerRequest: 1000,

	/**
	 * adds person table additional actions html (above and beneath it)
	 */
	appendTableActionsHtml: function() {
		let auswahlStatus =
			'<div class="input-group">' +
				'<select class="form-control d-inline auswahlStatus">' +
					'<option value="resolved">'+FHC_PhrasesLib.t("fehlermonitoring", "behoben")+'</option>' +
					'<option value="inProgress">'+FHC_PhrasesLib.t("fehlermonitoring", "inBearbeitung")+'</option>' +
					'<option value="new">'+FHC_PhrasesLib.t("ui", "neu")+'</option>' +
				'</select>' +
				'<span class="input-group-btn">' +
					'<button class="btn btn-default setStatus">'+FHC_PhrasesLib.t("fehlermonitoring", "statusFuerAusgewaehlteSetzen")+'</button>' +
				'</span>' +
			'</div>';

		let selectAllHtml =
			'<a href="javascript:void(0)" class="selectAll">' +
			'<i class="fa fa-check"></i>&nbsp;'+FHC_PhrasesLib.t("ui", "alle")+'</a>&nbsp;&nbsp;' +
			'<a href="javascript:void(0)" class="unselectAll">' +
			'<i class="fa fa-times"></i>&nbsp;'+FHC_PhrasesLib.t("ui", "keinen")+'</a>&nbsp;&nbsp;&nbsp;&nbsp;';

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
							var countHtml = issuescount + " "+FHC_PhrasesLib.t("fehlermonitoring", "meldungen");

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
	setTableActions: function() {
		$(".setStatus").click(function() {
			let status_kurzbz = $(".auswahlStatus").val();
			let issue_ids_sel = $("#filterTableDataset input:checked[name=issue_id\\[\\]]");

			if(status_kurzbz.length <= 0)
				return FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("fehlermonitoring", "bitteStatusWaehlen"));

			if(issue_ids_sel.length <= 0)
				return FHC_DialogLib.alertInfo(FHC_PhrasesLib.t("fehlermonitoring", "bitteFehlerWaehlen"));

			let issue_ids = [];

			for (let i = 0; i < issue_ids_sel.length; i++)
			{
				issue_ids.push($(issue_ids_sel[i]).val());
			}

			IssuesDataset.changeIssueStatus(issue_ids, status_kurzbz);
		});

		$(".selectAll").click(function() {
			//select only trs if not filtered by tablesorter
			var trs = $("#filterTableDataset tbody tr").not(".filtered");
			trs.find("input[name=issue_id\\[\\]]").prop("checked", true);
		});

		$(".unselectAll").click(function() {
				var trs = $("#filterTableDataset tbody tr").not(".filtered");
				trs.find("input[name=issue_id\\[\\]]").prop("checked", false);
		});
	},
	/**
	 * sends request for changing issue status
	 */
	changeIssueStatus: function(issue_ids, status_kurzbz) {
		FHC_AjaxClient.ajaxCallPost(
			'system/issues/Issues/changeIssueStatus',
			{
				// split up issue ids if too much data for single request
				"issue_ids": issue_ids.slice(0, IssuesDataset.maxIssuesPerRequest),
				"status_kurzbz": status_kurzbz
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isError(data))
					{
						FHC_DialogLib.alertError(
							FHC_PhrasesLib.t("fehlermonitoring", "statusAendernFehler") + ": "
							+ FHC_AjaxClient.getError(data)
						);
					}
					else if (FHC_AjaxClient.hasData(data))
					{
						if (issue_ids.length < IssuesDataset.maxIssuesPerRequest)
						{
							FHC_DialogLib.alertSuccess(FHC_AjaxClient.getData(data));
							FHC_FilterWidget.reloadDataset();
						}
						else // send next chunk of data
							IssuesDataset.changeIssueStatus(issue_ids.slice(IssuesDataset.maxIssuesPerRequest), status_kurzbz);
					}
					else
						FHC_DialogLib.alertError(FHC_PhrasesLib.t("fehlermonitoring", "statusAendernUnbekannterFehler"));
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_DialogLib.alertError(FHC_PhrasesLib.t("fehlermonitoring", "statusAendernFehler") + ": " + textStatus);
				}
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
