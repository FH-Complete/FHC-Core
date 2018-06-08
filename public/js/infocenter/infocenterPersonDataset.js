/**
 * Javascript file for infocenter overview page
 */

/**
* Refreshes the side menu
* NOTE: it is called from the FilterWidget therefore must be a global function
*/
function refreshSideMenu()
{
	//
	FHC_AjaxClient.ajaxCallGet(
		'system/infocenter/InfoCenter/setNavigationMenuArrayJson',
		null,
		{
			successCallback: function(data, textStatus, jqXHR) {
				FHC_NavigationWidget.renderSideMenu();
			},
			errorCallback: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			}
		}
	);
}

/**
 *
 */
var InfocenterPersonDataset = {

	/**
	 * adds person table additional actions html (above and beneath it)
	 /*
	 */
	appendTableActionsHtml: function()
	{
		var currurl = window.location.href;
		var url = currurl.replace(/infocenter\/InfoCenter(.*)/, "Messages/write");

		var formHtml = '<form id="sendMsgsForm" method="post" action="'+ url +'" target="_blank"></form>';
		$("#datasetActionsTop").before(formHtml);

		var selectAllHtml =
			'<a href="javascript:void(0)" class="selectAll">' +
			'<i class="fa fa-check"></i>&nbsp;Alle</a>&nbsp;&nbsp;' +
			'<a href="javascript:void(0)" class="unselectAll">' +
			'<i class="fa fa-times"></i>&nbsp;Keinen</a>&nbsp;&nbsp;&nbsp;&nbsp;';

		var actionHtml = 'Mit Ausgew&auml;hlten:&nbsp;&nbsp;' +
			'<a href="javascript:void(0)" class="sendMsgsLink">' +
			'<i class="fa fa-envelope"></i>&nbsp;Nachricht senden</a>';

		var legendHtml = '<i class="fa fa-circle text-danger"></i> Gesperrt&nbsp;&nbsp;&nbsp;&nbsp;' +
			'<i class="fa fa-circle text-info"></i> Geparkt';

		var personcount = 0;

		FHC_AjaxClient.ajaxCallGet(
			'system/Filters/rowNumber',
			{
				filter_page: FHC_FilterWidget.getFilterPage()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						personcount = FHC_AjaxClient.getData(data);

						if (personcount > 0)
						{
							var persontext = personcount === 1 ? "Person" : "Personen";
							var countHtml = personcount + " " + persontext;

							$("#datasetActionsTop, #datasetActionsBottom").append(
								"<div class='row'>"+
								"<div class='col-xs-6'>" + selectAllHtml + "&nbsp;&nbsp;" + actionHtml + "</div>"+
								"<div class='col-xs-4'>" + legendHtml + "</div>"+
								"<div class='col-xs-2 text-right'>" + countHtml + "</div>"+
								"<div class='clearfix'></div>"+
								"</div>"
							);
							$("#datasetActionsBottom").append("<br><br>");

							InfocenterPersonDataset.setTableActions();
						}
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					alert(textStatus);
				}
			}
		);
	},

	/**
	 * sets functionality for the actions above and beneath the person table
	 */
	setTableActions: function()
	{
		$(".sendMsgsLink").click(function() {
			var idsel = $("#filterTableDataset input:checked[name=PersonId\\[\\]]");
			if(idsel.length > 0)
			{
				var form = $("#sendMsgsForm");
				form.find("input[type=hidden]").remove();
				for (var i = 0; i < idsel.length; i++)
				{
					var id = $(idsel[i]).val();
					form.append("<input type='hidden' name='person_id[]' value='" + id + "'>");
				}
				form.submit();
			}
		});

		$(".selectAll").click(function()
			{
				//select only trs if not filtered by tablesorter
				var trs = $("#filterTableDataset tbody tr").not(".filtered");
				trs.find("input[name=PersonId\\[\\]]").prop("checked", true);
			}
		);

		$(".unselectAll").click(function()
			{
				var trs = $("#filterTableDataset tbody tr").not(".filtered");
				trs.find("input[name=PersonId\\[\\]]").prop("checked", false);
			}
		);
	}

};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	InfocenterPersonDataset.appendTableActionsHtml();

});
