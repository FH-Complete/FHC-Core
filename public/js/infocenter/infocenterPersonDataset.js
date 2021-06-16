/**
 * Javascript file for infocenter overview page
 */

/**
* Global function used by FilterWidget JS to refresh the side menu
* NOTE: it is called from the FilterWidget JS therefore must be a global function
* 		To be called only if the page has a customized menu (currently only index)
*/
if (FHC_JS_DATA_STORAGE_OBJECT.called_method == 'index')
{
	function refreshSideMenuHook()
	{
		FHC_NavigationWidget.refreshSideMenuHook('system/infocenter/InfoCenter/setNavigationMenuArrayJson');
	}
}

/**
 *
 */
var InfocenterPersonDataset = {
	infocenter_studiensemester_variablename: 'infocenter_studiensemester',
	infocenter_studienganstyp_variablename: 'infocenter_studiensgangtyp',

	/**
	 * adds person table additional actions html (above and beneath it)
	 */
	appendTableActionsHtml: function(infocenter_studiensemester, infocenter_studiengangstyp)
	{
		var url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/system/messages/Messages/writeTemplate";

		var formHtml = '<form id="sendMsgsForm" method="post" action="'+ url +'" target="_blank"></form>';
		$("#datasetActionsTop").before(formHtml);

		var auswahlStudienart =
			'<select class="form-control auswahlStudienArt" style="width:auto;">' +
				'<option data-id="all"> Alle </option>' +
				'<option data-id="master"> Master </option>' +
				'<option data-id="bachelor"> Bachelor </option>' +
			'</select>';

		var studienSemesterHtml = '<button class="btn btn-default btn-sm decStudiensemester">' +
			'<i class="fa fa-chevron-left"></i>' +
			'</button>&nbsp;' +
			infocenter_studiensemester +
			'&nbsp;<button class="btn btn-default btn-sm incStudiensemester">' +
			'<i class="fa fa-chevron-right"></i>' +
			'</button>';

		var selectAllHtml =
			'<a href="javascript:void(0)" class="selectAll">' +
			'<i class="fa fa-check"></i>&nbsp;Alle</a>&nbsp;&nbsp;' +
			'<a href="javascript:void(0)" class="unselectAll">' +
			'<i class="fa fa-times"></i>&nbsp;Keinen</a>&nbsp;&nbsp;&nbsp;&nbsp;';

		var actionHtml = 'Mit Ausgew&auml;hlten:&nbsp;&nbsp;' +
			'<a href="javascript:void(0)" class="sendMsgsLink">' +
			'<i class="fa fa-envelope"></i>&nbsp;Nachricht senden</a>';

		var legendHtml = '<i class="fa fa-circle text-danger"></i> Gesperrt&nbsp;&nbsp;&nbsp;&nbsp;' +
			'<i class="fa fa-circle text-info"></i> Geparkt&nbsp;&nbsp;&nbsp;&nbsp;' +
			'<i class="fa fa-circle onhold"></i> Zurückgestellt';

		// userdefined Semestervariable shown independently of personcount,
		// it is possible to change the semester
		$("#datasetActionsTop, #datasetActionsBottom").append(
			"<div class='row'>" +
				"<div class='col-xs-5 text-right'>" + auswahlStudienart + "</div>" +
				"<div class='col-xs-7 text-left'>" + studienSemesterHtml + "</div>" +
			"</div>" +
			"<div class='h-divider'></div><hr class='studiensemesterline'>"
		);

		InfocenterPersonDataset.selectStudiengangTyp(infocenter_studiengangstyp)

		$('.auswahlStudienArt').change(function()
		{
			InfocenterPersonDataset.changeStudengangsTyp($(this).find('option:selected').attr('data-id'));
		});

		$("button.incStudiensemester").click(function() {
			InfocenterPersonDataset.changeStudiensemesterUservar(1);
		});

		$("button.decStudiensemester").click(function() {
			InfocenterPersonDataset.changeStudiensemesterUservar(-1);
		});

		var personcount = 0;

		FHC_AjaxClient.ajaxCallGet(
			'widgets/Filters/rowNumber',
			{
				filterUniqueId: FHC_FilterWidget.getFilterUniqueIdPrefix()
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

							// Count Records after Filtering
							$("#filterTableDataset").bind("filterEnd", function() {
								var cnt = $("#filterTableDataset tr:visible").length - 2;
								$(".filterTableDatasetCntFiltered").html(cnt + ' / ');
							});

							$("#datasetActionsTop, #datasetActionsBottom").append(
								"<div class='row'>"+
								"<div class='col-xs-4'>" + selectAllHtml + "&nbsp;&nbsp;" + actionHtml + "</div>"+
								"<div class='col-xs-4 text-center'>" + legendHtml + "</div>"+
								"<div class='col-xs-4 text-right'>" +
								"<span class='filterTableDatasetCntFiltered'></span>" +
								countHtml +	"</div>"+
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

	selectStudiengangTyp: function(typ)
	{
		switch (typ)
		{
			case 'b, m' :
				$('.auswahlStudienArt [data-id="all"]').attr('selected', 'selected');
				break;
			case 'b' :
				$('.auswahlStudienArt [data-id="bachelor"]').attr('selected', 'selected');
				break;
			case 'm' :
				$('.auswahlStudienArt [data-id="master"]').attr('selected', 'selected');
				break;
		}
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

		//make sure tablesorter local storage for homepage url with and without "/index" shares same values
		$("#filterTableDataset").bind('filterEnd', function()
		{
			if (FHC_JS_DATA_STORAGE_OBJECT.called_method === 'index')
			{
				var pathname = window.location.pathname;
				var storageobj = localStorage.getItem("tablesorter-filters");
				var parsed = JSON.parse(storageobj);
				var regex = new RegExp(/\/index(?!\.ci\.php)/);
				if (regex.test(pathname))
				{
					parsed[pathname.replace(regex, "")] = parsed[pathname];
				}
				else
				{
					parsed[pathname + "/index"] = parsed[pathname];
				}
				storageobj = JSON.stringify(parsed);
				localStorage.setItem("tablesorter-filters", storageobj);
			}
		});
	},

	changeStudengangsTyp: function($typ)
	{
		switch ($typ)
		{
			case 'all' :
				var change = 'b\', \'m';
				break;
			case 'bachelor' :
				var change = 'b';
				break;
			case 'master' :
				var change = 'm';
				break;
		}

		FHC_AjaxClient.showVeil();

		FHC_AjaxClient.ajaxCallPost(
			'system/Variables/changeStudengangsTypVar',
			{
				'name': InfocenterPersonDataset.infocenter_studienganstyp_variablename,
				'change': change,
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						// refresh filterwidget with page reload
						FHC_FilterWidget.reloadDataset();
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_AjaxClient.hideVeil();
					alert(textStatus);
				}
			}
		);
	},

	/**
	 * initializes change of the uservariable infocenter_studiensemesster, either
	 * to next semester (change > 0) or previous semester (change < 0)
	 */
	changeStudiensemesterUservar: function(change)
	{
		FHC_AjaxClient.showVeil();

		FHC_AjaxClient.ajaxCallPost(
			'system/Variables/changeStudiensemesterVar',
			{
				'name': InfocenterPersonDataset.infocenter_studiensemester_variablename,
				'change': change
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						// refresh filterwidget with page reload
						FHC_FilterWidget.reloadDataset();
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					FHC_AjaxClient.hideVeil();
					alert(textStatus);//TODO dialoglib
				}
			}
		);
	},

	/**
	 * initializes call to get the Studiensemester user variable
	 */
	getStudiensemesterUservar: function(callback)
	{
		FHC_AjaxClient.ajaxCallGet(
			'system/Variables/getVar',
			{
				'name' : InfocenterPersonDataset.infocenter_studiensemester_variablename,
				'typ' : InfocenterPersonDataset.infocenter_studienganstyp_variablename
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						if (typeof callback === "function")
						{
							var infocenter_studiensemester = FHC_AjaxClient.getData(data);
							callback(infocenter_studiensemester[InfocenterPersonDataset.infocenter_studiensemester_variablename], infocenter_studiensemester[InfocenterPersonDataset.infocenter_studienganstyp_variablename]);
						}
					}
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					alert(textStatus);
				}
			}
		);
	}
};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	InfocenterPersonDataset.getStudiensemesterUservar(InfocenterPersonDataset.appendTableActionsHtml);

});
