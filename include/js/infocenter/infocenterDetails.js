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
 * javascript file for infocenterDetails page
 */

$(document).ready(
	function ()
	{
		//initialise table sorter
		addTablesorter("doctable", [[2, 1], [1, 0]], ["zebra"]);
		addTablesorter("nachgdoctable", [[2, 0], [1, 1]], ["zebra"]);
		addTablesorter("msgtable", [[0, 1], [2, 0]], ["zebra", "filter"], 2);
		tablesortAddPager("msgtable", "msgpager", 10);

		formatNotizTable();
		formatLogTable();

		//initialise datepicker
		$.datepicker.setDefaults($.datepicker.regional['de']);
		$(".dateinput").datepicker({
			"dateFormat": "dd.mm.yy"
		});

		//add submit event to message send link
		$("#sendmsglink").click(
			function ()
			{
				$("#sendmsgform").submit();
			}
		);

		//add click events to "formal geprüft" checkboxes
		$(".prchbox").click(function ()
		{
			var boxid = this.id;
			var personid = $("#hiddenpersonid").val();
			var akteid = boxid.substr(boxid.indexOf("_") + 1);
			var checked = this.checked;
			saveFormalGeprueft(personid, akteid, checked)
		});

		//zgv übernehmen
		$(".zgvUebernehmen").click(function ()
		{
			var btn = $(this);
			var personid = $("#hiddenpersonid").val();
			var prestudentid = this.id.substr(this.id.indexOf("_") + 1);
			$('#zgvUebernehmenNotice').remove();
			zgvUebernehmen(personid, prestudentid, btn)
		});

		//zgv speichern
		$(".zgvform").on('submit', function (e)
			{
				e.preventDefault();
				var data = $(this).serializeArray();
				saveZgv(data);
			}
		);

		//prevent opening modal when Statusgrund not chosen
		$(".absageModal").on('show.bs.modal', function (e)
			{
				var id = this.id.substr(this.id.indexOf("_") + 1);
				var statusgrvalue = $("#statusgrselect_" + id + " select[name=statusgrund]").val();
				if (statusgrvalue === "null")
				{
					$("#statusgrselect_" + id).addClass("has-error");
					return e.preventDefault();
				}
			}
		);

		//remove red mark when statusgrund is selected again
		$("select[name=statusgrund]").change(
			function ()
			{
				$(this).parent().removeClass("has-error");
			}
		);

		//save notiz
		$("#notizform").on("submit", function (e)
			{
				e.preventDefault();
				var personid = $("#hiddenpersonid").val();
				var data = $(this).serializeArray();
				saveNotiz(personid, data);
			}
		)
	});

// -----------------------------------------------------------------------------------------------------------------
// ajax calls

function saveFormalGeprueft(personid, akteid, checked)
{
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "../saveFormalGeprueft/" + personid,
		data: {"akte_id": akteid, "formal_geprueft": checked},
		success: function (data, textStatus, jqXHR)
		{
			if (data === null)
			{
				$("#formalgeprueftam_" + akteid).text("");
			}
			else
			{
				fgdatum = $.datepicker.parseDate("yy-mm-dd", data);
				gerfgdatum = $.datepicker.formatDate("dd.mm.yy", fgdatum);
				$("#formalgeprueftam_" + akteid).text(gerfgdatum);
			}
			//refresh doctable tablesorter, formal geprueft changed!
			$("#doctable").trigger("update");
			refreshLog();
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
		}
	});
}

function zgvUebernehmen(personid, prestudentid, btn)
{
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "../getLastPrestudentWithZgvJson/" + personid,
		success: function (data, textStatus, jqXHR)
		{
			if (data !== null)
			{
				var zgvcode = data.zgv_code !== null ? data.zgv_code : "null";
				var zgvort = data.zgvort !== null ? data.zgvort : "";
				var zgvdatum = data.zgvdatum;
				var gerzgvdatum = "";
				if (zgvdatum !== null)
				{
					zgvdatum = $.datepicker.parseDate("yy-mm-dd", data.zgvdatum);
					gerzgvdatum = $.datepicker.formatDate("dd.mm.yy", zgvdatum);
				}
				var zgvnation = data.zgvnation !== null ? data.zgvnation : "null";
				$("#zgv_" + prestudentid).val(zgvcode);
				$("#zgvort_" + prestudentid).val(zgvort);
				$("#zgvdatum_" + prestudentid).val(gerzgvdatum);
				$("#zgvnation_" + prestudentid).val(zgvnation);
			}
			else
			{
				btn.after("&nbsp;&nbsp;<span id='zgvUebernehmenNotice' class='text-warning'>keine ZGV vorhanden</span>");
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
		}
	});
}

function saveZgv(data)
{
	var prestudentid = data[0].value;
	$("#zgvSpeichernNotice").remove();
	$.ajax({
		type: "POST",
		dataType: "json",
		data: data,
		url: "../saveZgvPruefung/" + prestudentid,
		success: function (data, textStatus, jqXHR)
		{
			if (data === prestudentid)
			{
				refreshLog();
				$("#zgvSpeichern_" + prestudentid).before("<span id='zgvSpeichernNotice' class='text-success'>ZGV erfolgreich gespeichert!</span>&nbsp;&nbsp;");
			}
			else
			{
				$("#zgvSpeichern_" + prestudentid).before("<span id='zgvSpeichernNotice' class='text-danger'>Fehler beim Speichern der ZGV!</span>&nbsp;&nbsp;");
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
		}
	});
}

function saveNotiz(personid, data)
{
	$.ajax({
		type: "POST",
		dataType: "json",
		data: data,
		url: "../saveNotiz/" + personid,
		success: function (data, textStatus, jqXHR)
		{
			refreshNotizen();
			refreshLog();
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
		}
	});
}

// -----------------------------------------------------------------------------------------------------------------
// methods executed after ajax (refreshers)

function refreshLog()
{
	var personid = $("#hiddenpersonid").val();
	$("#logs").load('../reloadLogs/' + personid,
		function ()
		{
			//readd tablesorter
			formatLogTable()
		}
	);
}

function formatLogTable()
{
	addTablesorter("logtable", [[0, 1]], ["filter"], 2);
	tablesortAddPager("logtable", "logpager", 23);
	$("#logtable").addClass("table-condensed");
}

function refreshNotizen()
{
	$("#notizform").find("input[type=text], textarea").val("");
	var personid = $("#hiddenpersonid").val();
	$("#notizen").load('../reloadNotizen/' + personid,
		function ()
		{
			//readd tablesorter
			formatNotizTable()
		}
	);
}

function formatNotizTable()
{
	addTablesorter("notiztable", [[0, 1]], ["filter"], 2);
	tablesortAddPager("notiztable", "notizpager", 10);
	$("#notiztable").addClass("table-condensed");
}
