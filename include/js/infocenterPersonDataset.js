/**
 */
$(document).ready(
	function()
	{
		// Checks if the table contains data (rows)
		if ($('#tableDataset').find('tbody:empty').length == 0
			&& $('#tableDataset').find('tr:empty').length == 0)
		{
			$("#tableDataset").tablesorter(
				{
					widgets: ["zebra", "filter"]
				});
		}
		appendTableActionsHtml();
		setTableActions();
	}
);

function appendTableActionsHtml()
{
	var currurl = window.location.href;
	var url = currurl.replace(/infocenter\/InfoCenter(.*)/, "Messages/write");

	var formHtml = '<form id="sendMsgsForm" method="post" action="'+ url +'" target="_blank"></form>';
	$("#filterForm").before(formHtml);

	var selectAllHtml =
		'<a href="javascript:void(0)" class="selectAll">' +
		'<i class="fa fa-check"></i>&nbsp;Alle</a>&nbsp;&nbsp;' +
		'<a href="javascript:void(0)" class="unselectAll">' +
		'<i class="fa fa-times"></i>&nbsp;Keinen</a>&nbsp;&nbsp;&nbsp;&nbsp;';

	var messageHtml = 'Mit Ausgew&auml;hlten:&nbsp;&nbsp;' +
		'<a href="javascript:void(0)" class="sendMsgsLink">' +
		'<i class="fa fa-envelope"></i>&nbsp;Nachricht senden</a>';

	var personcount = $("#tableDataset tbody tr").length;
	var persontext = personcount === 1 ? "Person" : "Personen";
	var countHtml = $("#tableDataset tbody tr").length +" "+persontext;

	$("#datasetActionsTop, #datasetActionsBottom").append(
		"<div class='pull-left'>"+selectAllHtml+"&nbsp;&nbsp;"+ messageHtml+"</div>"+
		"<div class='pull-right'>"+countHtml+"</div>"+
		"<div class='clearfix'></div>"
	);
	$("#datasetActionsBottom").append("<br><br>");
}


function setTableActions()
{
	$(".sendMsgsLink").click(function() {
		var idsel = $("#tableDataset input:checked[name=PersonId\\[\\]]");
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
			//trs only if not filtered by tablesorter
			var trs = $("#tableDataset tbody tr").not(".filtered");
			trs.find("input[name=PersonId\\[\\]]").prop("checked", true);
		}
	);

	$(".unselectAll").click(function()
		{
			var trs = $("#tableDataset tbody tr").not(".filtered");
			trs.find("input[name=PersonId\\[\\]]").prop("checked", false);
		}
	);
}