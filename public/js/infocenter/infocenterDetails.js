
var fhc_controller_id = FHC_AjaxClient.getUrlParameter('fhc_controller_id');
const CONTROLLER_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/"+FHC_JS_DATA_STORAGE_OBJECT.called_path;

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
		tablesortAddPager("msgtable", "msgpager", 14);

		formatNotizTable();
		formatLogTable();

		//initialise datepicker
		$.datepicker.setDefaults($.datepicker.regional['de']);
		$(".dateinput").datepicker({
			"dateFormat": "dd.mm.yy"
		});

		var personid = $("#hiddenpersonid").val();

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
			var akteid = boxid.substr(boxid.indexOf("_") + 1);
			var checked = this.checked;
			saveFormalGeprueft(personid, akteid, checked)
		});

		//zgv übernehmen
		$(".zgvUebernehmen").click(function ()
		{
			var btn = $(this);
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

		//show popup with zgvinfo
		$(".zgvinfo").click(function ()
			{
				var prestudentid = this.id.substr(this.id.indexOf("_") + 1);
				openZgvInfoForPrestudent(prestudentid);
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
				var personId = $("#hiddenpersonid").val();
				var notizId = $("#notizform :input[name='hiddenNotizId']").val();
				var data = $(this).serializeArray();

				if (notizId !== '')
				{
					updateNotiz(notizId, personid, data);
				}
				else
				{
					saveNotiz(personid, data);
				}
			}
		);

		//update notiz - autofill notizform
		$(document).on("click", "#notiztable tbody tr", function ()
			{
				var notizId = $(this).find("td:eq(3)").html();
				var notizTitle = $(this).find("td:eq(1)").text();
				var notizContent = this.title;

				$("#notizform label:first").text("Notiz ändern").css("color", "red");
				$("#notizform :input[type='reset']").css("display", "inline-block");

				$("#notizform :input[name='hiddenNotizId']").val(notizId);
				$("#notizform :input[name='notiztitel']").val(notizTitle);
				$("#notizform :input[name='notiz']").val(notizContent);
			}
		);

		//update notiz - abbrechen-button: reset styles
		$("#notizform :input[type='reset']").click(function ()
			{
				resetNotizFields();
			}
		);

		//check if person is parked and display it
		getParkedDateAjax(personid);

	});

function openZgvInfoForPrestudent(prestudent_id)
{
	var screenwidth = screen.width;
	var popupwidth = 760;
	var marginleft = screenwidth - popupwidth;
	window.open("../getZgvInfoForPrestudent/" + prestudent_id, "_blank","resizable=yes,scrollbars=yes,width="+popupwidth+",height="+screen.height+",left="+marginleft);
}

// -----------------------------------------------------------------------------------------------------------------
// ajax calls

function saveFormalGeprueft(personid, akteid, checked)
{
	$.ajax({
		type: "POST",
		dataType: "json",
		url: CONTROLLER_URL+"/saveFormalGeprueft/" + personid + '?fhc_controller_id=' + fhc_controller_id,
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
		type: "GET",
		dataType: "json",
		url: CONTROLLER_URL+"/getLastPrestudentWithZgvJson/" + personid + '?fhc_controller_id=' + fhc_controller_id,
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
		url: CONTROLLER_URL+"/saveZgvPruefung/" + prestudentid + '?fhc_controller_id=' + fhc_controller_id,
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
		url: CONTROLLER_URL+"/saveNotiz/" + personid + '?fhc_controller_id=' + fhc_controller_id,
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

function updateNotiz(notizId, personId, data)
{
	$.ajax({
		type: "POST",
		dataType: "json",
		data: data,
		url: CONTROLLER_URL+"/updateNotiz/" + notizId + "/" + personId + '?fhc_controller_id=' + fhc_controller_id,
		success: function (data, textStatus, jqXHR)
		{
			if (data)
			{
				refreshNotizen();
				refreshLog();
				resetNotizFields();
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
		}
	});
}

function getStudienjahrEndAjax()
{
	$.ajax({
		url: CONTROLLER_URL+"/getStudienjahrEnd?fhc_controller_id="+fhc_controller_id,
		method: "GET",
		success: function(data, textStatus, jqXHR)
		{
			//var gerdate = data.substring(8, 10) + "."+data.substring(5, 7) + "." + data.substring(0, 4);
			var engdate = $.datepicker.parseDate("yy-mm-dd", data);
			var gerdate = $.datepicker.formatDate("dd.mm.yy", engdate);
			$("#parkdate").val(gerdate);
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert(textStatus);
		}
	});
}

function getParkedDateAjax(personid)
{
	$.ajax({
		url: CONTROLLER_URL+"/getParkedDate/"+personid+"?fhc_controller_id="+fhc_controller_id,
		method: "GET",
		success: function(data, textStatus, jqXHR)
		{
			refreshParking(data);
			refreshLog();
			getStudienjahrEndAjax();
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert(textStatus);
		}
	});
}

function parkPersonAjax(personid, date)
{
	$.ajax({
		url: CONTROLLER_URL+"/park?fhc_controller_id="+fhc_controller_id,
		method: "POST",
		data:
		{
			"person_id": personid,
			"parkdate": date
		},
		success: function(data, textStatus, jqXHR)
		{
			getParkedDateAjax(personid);
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert(textStatus);
		}
	});
}

function unparkPersonAjax(personid)
{
	$.ajax({
		url: CONTROLLER_URL+"/unpark?fhc_controller_id="+fhc_controller_id,
		method: "POST",
		data:
		{
			"person_id": personid
		},
		success: function(data, textStatus, jqXHR)
		{
			getParkedDateAjax(personid);
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			alert(textStatus);
		}
	});
}

// -----------------------------------------------------------------------------------------------------------------
// methods executed after ajax (refreshers)

function refreshLog()
{
	var personid = $("#hiddenpersonid").val();
	$("#logs").load('../reloadLogs/' + personid + '?fhc_controller_id=' + fhc_controller_id,
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
	tablesortAddPager("logtable", "logpager", 22);
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

function refreshParking(date)
{
	if (date === null)
	{
		$("#parking").html(
			'<div class="form-group form-inline">'+
				'<button class="btn btn-default" id="parklink" type="button""><i class="fa fa-clock-o"></i>&nbsp;BewerberIn parken</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
				'bis&nbsp;&nbsp;'+
				'<input id="parkdate" type="text" class="form-control" placeholder="Parkdatum" style="height: 25px; width: 99px">'+
			'</div>');

		$("#parkdate").datepicker({
			"dateFormat": "dd.mm.yy",
			"minDate": 0
		});

		$("#parklink").click(
			function ()
			{
				var personid = $("#hiddenpersonid").val();
				var date = $("#parkdate").val();

				parkPersonAjax(personid, date);
			}
		);
	}
	else
	{
		var parkdate = $.datepicker.parseDate("yy-mm-dd", date);
		var gerparkdate = $.datepicker.formatDate("dd.mm.yy", parkdate);
		$("#parking").html(
			'BewerberIn geparkt bis '+gerparkdate+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
			'<button class="btn btn-default" id="unparklink"><i class="fa fa-sign-out"></i>&nbsp;BewerberIn ausparken</button>&nbsp;');

		$("#unparklink").click(
			function ()
			{
				var personid = $("#hiddenpersonid").val();
				unparkPersonAjax(personid, date);
			}
		);
	}
}

function formatNotizTable()
{
	addTablesorter("notiztable", [[0, 1]], ["filter"], 2);
	tablesortAddPager("notiztable", "notizpager", 11);
	$("#notiztable").addClass("table-condensed");
}

function resetNotizFields()
{
	$("#notizform :input[name='hiddenNotizId']").val("");
	$("#notizform label:first").text("Notiz hinzufügen").css("color", "black");
	$("#notizform :input[type='reset']").css("display", "none");
}
