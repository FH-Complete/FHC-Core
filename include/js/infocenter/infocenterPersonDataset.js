/*
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

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

	var messageHtml = 'Mit Ausgew&auml;hlten:&nbsp;&nbsp;' +
		'<a href="javascript:void(0)" class="sendMsgsLink">' +
		'<i class="fa fa-envelope"></i>&nbsp;Nachricht senden</a>';

	var personcount = 0;

	$.ajax({
		url: window.location.pathname.replace('infocenter/InfoCenter', 'Filters/rowNumber'),
		method: "GET"
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
					"<div class='pull-left'>" + selectAllHtml + "&nbsp;&nbsp;" + messageHtml + "</div>"+
					"<div class='pull-right'>" + countHtml + "</div>"+
					"<div class='clearfix'></div>"
				);
				$("#datasetActionsBottom").append("<br><br>");
			}

			setTableActions();
		}

	}).fail(function(jqXHR, textStatus, errorThrown) {});

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
 * Refreshes the side menu
 */
function refreshSideMenu()
{
	$.ajax({
		url: window.location.pathname+"/setNavigationMenuArray",
		method: "GET",
		data: {}
	})
	.done(function(data, textStatus, jqXHR) {

		renderSideMenu();

	}).fail(function(jqXHR, textStatus, errorThrown) {
		alert(textStatus);
	});
}
