/**
 * Javascript file for infocenter overview page
 */
$(document).ready(function() {

	appendTableActionsHtml();
	// setTableActions();

});

/**
 * adds person table additional actions html (above and beneath it)
 */
function appendTableActionsHtml()
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

	$.ajax({
		url: window.location.pathname.replace('infocenter/InfoCenter', 'Filters/rowNumber'),
		method: "GET",
		data: {
			fhc_controller_id: getUrlParameter("fhc_controller_id"),
			filter_page: FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method
		},
		dataType: "json"
	})
	.done(function(data, textStatus, jqXHR) {

		if (data != null)
		{
			if (data.rowNumber != null)
			{
				personcount = data.rowNumber;

				var persontext = personcount === 1 ? "Person" : "Personen";
				var countHtml = personcount + " " + persontext;

				$("#datasetActionsTop, #datasetActionsBottom").append(
					"<div class='row'>"+
						"<div class='col-xs-4'>" + selectAllHtml + "&nbsp;&nbsp;" + actionHtml + "</div>"+
						"<div class='col-xs-4 text-center'>" + legendHtml + "</div>"+
						"<div class='col-xs-4 text-right'>" + countHtml + "</div>"+
						"<div class='clearfix'></div>"+
					"</div>"
				);
				$("#datasetActionsBottom").append("<br><br>");
			}

			setTableActions();
		}

	}).fail(function(jqXHR, textStatus, errorThrown) {
		alert(textStatus);
	});

}

/**
 * sets functionality for the actions above and beneath the person table
 */
function setTableActions()
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

/**
 *
 */
function getUrlParameter(sParam)
{
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++)
	{
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam)
		{
            return sParameterName[1];
        }
    }
}

/**
 * Refreshes the side menu
 */
function refreshSideMenu()
{
	$.ajax({
		url: window.location.pathname+"/setNavigationMenuArray",
		method: "GET",
		data: {
			fhc_controller_id: getUrlParameter("fhc_controller_id")
		}
	})
	.done(function(data, textStatus, jqXHR) {

		FHC_NavigationWidget.renderSideMenu();

	}).fail(function(jqXHR, textStatus, errorThrown) {
		alert(textStatus);
	});
}
