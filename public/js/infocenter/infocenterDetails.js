
var fhc_controller_id = FHC_AjaxClient.getUrlParameter('fhc_controller_id');
const CONTROLLER_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/"+FHC_JS_DATA_STORAGE_OBJECT.called_path;
const CALLED_PATH = FHC_JS_DATA_STORAGE_OBJECT.called_path;

/**
 * javascript file for infocenterDetails page
 */
$(document).ready(
	function ()
	{
		//initialise table sorter
		Tablesort.addTablesorter("doctable", [[2, 1], [1, 0]], ["zebra"]);
		Tablesort.addTablesorter("nachgdoctable", [[2, 0], [1, 1]], ["zebra"]);
		Tablesort.addTablesorter("msgtable", [[0, 1], [2, 0]], ["zebra", "filter"], 2);
		Tablesort.tablesortAddPager("msgtable", "msgpager", 14);

		InfocenterDetails._formatNotizTable();
		InfocenterDetails._formatLogTable();

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
			InfocenterDetails.saveFormalGeprueft(personid, akteid, checked)
		});

		//zgv übernehmen
		$(".zgvUebernehmen").click(function ()
		{
			var btn = $(this);
			var prestudentid = this.id.substr(this.id.indexOf("_") + 1);
			$('#zgvUebernehmenNotice').remove();
			InfocenterDetails.zgvUebernehmen(personid, prestudentid, btn)
		});

		//zgv speichern
		$(".zgvform").on('submit', function (e)
			{
				e.preventDefault();
				var formdata = $(this).serializeArray();

				var data = {};

				for (var i = 0; i < formdata.length; i++)
				{
					data[formdata[i].name] = formdata[i].value;
				}

				InfocenterDetails.saveZgv(data);
			}
		);

		//show popup with zgvinfo
		$(".zgvinfo").click(function ()
			{
				var prestudentid = this.id.substr(this.id.indexOf("_") + 1);
				InfocenterDetails.openZgvInfoForPrestudent(prestudentid);
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
				var notizId = $("#notizform :input[name='hiddenNotizId']").val();
				var formdata = $(this).serializeArray();
				var data = {};

				for (var i = 0; i < formdata.length; i++)
				{
					data[formdata[i].name] = formdata[i].value;
				}

				$("#notizmsg").empty();

				if (notizId !== '')
				{
					InfocenterDetails.updateNotiz(notizId, personid, data);
				}
				else
				{
					InfocenterDetails.saveNotiz(personid, data);
				}
			}
		);

		//update notiz - autofill notizform
		$(document).on("click", "#notiztable tbody tr", function ()
			{
				$("#notizmsg").empty();

				var notizId = $(this).find("td:eq(3)").html();
				var notizTitle = $(this).find("td:eq(1)").text();
				var notizContent = this.title;

				$("#notizform label:first").text(FHC_PhraseLib.t('infocenter', 'notizAendern')).css("color", "red");
				$("#notizform :input[type='reset']").css("display", "inline-block");

				$("#notizform :input[name='hiddenNotizId']").val(notizId);
				$("#notizform :input[name='notiztitel']").val(notizTitle);
				$("#notizform :input[name='notiz']").val(notizContent);
			}
		);

		//update notiz - abbrechen-button: reset styles
		$("#notizform :input[type='reset']").click(function ()
			{
				InfocenterDetails._resetNotizFields();
			}
		);

		//check if person is parked and display it
		InfocenterDetails.getParkedDate(personid);

	});

var InfocenterDetails = {

	genericSaveError: function() {
		alert("error when saving");
	},
	openZgvInfoForPrestudent: function(prestudent_id)
	{
		var screenwidth = screen.width;
		var popupwidth = 760;
		var marginleft = screenwidth - popupwidth;
		window.open(CONTROLLER_URL + "/getZgvInfoForPrestudent/" + encodeURIComponent(prestudent_id), "_blank","resizable=yes,scrollbars=yes,width="+popupwidth+",height="+screen.height+",left="+marginleft);
	},

	// -----------------------------------------------------------------------------------------------------------------
	// ajax calls
	saveFormalGeprueft: function(personid, akteid, checked)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/saveFormalGeprueft/' + encodeURIComponent(personid),
			{
				akte_id: akteid,
				formal_geprueft: checked
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (data !== false)
					{
						if (data === null)
						{
							$("#formalgeprueftam_" + akteid).text("");
						}
						else
						{
							var fgdatum = $.datepicker.parseDate("yy-mm-dd", data);
							var gerfgdatum = $.datepicker.formatDate("dd.mm.yy", fgdatum);
							$("#formalgeprueftam_" + akteid).text(gerfgdatum);
						}
						//refresh doctable tablesorter, formal geprueft changed!
						$("#doctable").trigger("update");
						InfocenterDetails._refreshLog();
					}
					else
					{
						InfocenterDetails.genericSaveError();
					}
				},
				errorCallback: InfocenterDetails.genericSaveError,
				veilTimeout: 0
			}
		);
	},
	zgvUebernehmen: function(personid, prestudentid, btn)
	{
		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + "/getLastPrestudentWithZgvJson/" + encodeURIComponent(personid),
			{
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						var prestudent = data.retval[0];
						var zgvcode = prestudent.zgv_code !== null ? prestudent.zgv_code : "null";
						var zgvort = prestudent.zgvort !== null ? prestudent.zgvort : "";
						var zgvdatum = prestudent.zgvdatum;
						var gerzgvdatum = "";
						if (zgvdatum !== null)
						{
							zgvdatum = $.datepicker.parseDate("yy-mm-dd", prestudent.zgvdatum);
							gerzgvdatum = $.datepicker.formatDate("dd.mm.yy", zgvdatum);
						}
						var zgvnation = prestudent.zgvnation !== null ? prestudent.zgvnation : "null";
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
				veilTimeout: 0
			}
		);
	},
	saveZgv: function (data)
	{
		var zgvError = function(){
			$("#zgvSpeichern_" + prestudentid).before("<span id='zgvSpeichernNotice' class='text-danger'>" + FHC_PhraseLib.t('ui', 'fehlerBeimSpeichern') + "</span>&nbsp;&nbsp;");
		};

		var prestudentid = data.prestudentid;
		$("#zgvSpeichernNotice").remove();

		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/saveZgvPruefung',
			data,
			{
				successCallback: function(data, textStatus, jqXHR) {

					if (FHC_AjaxClient.hasData(data))
					{
						InfocenterDetails._refreshLog();
						$("#zgvSpeichern_" + prestudentid).before("<span id='zgvSpeichernNotice' class='text-success'>" + FHC_PhraseLib.t('ui', 'gespeichert') + "</span>&nbsp;&nbsp;");
					}
					else
					{
						zgvError();
					}
				},
				errorCallback: zgvError,
				veilTimeout: 0
			}
		);
	},
	saveNotiz: function (personid, data)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/saveNotiz/' + encodeURIComponent(personid),
			data,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						InfocenterDetails._refreshNotizen();
						InfocenterDetails._refreshLog();
					}
					else
					{
						InfocenterDetails._errorSaveNotiz();
					}
				},
				errorCallback: InfocenterDetails._errorSaveNotiz,
				veilTimeout: 0
			}
		);
	},
	updateNotiz: function (notizId, personId, data)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/updateNotiz/' + encodeURIComponent(notizId) + "/" + encodeURIComponent(personId),
			data,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						InfocenterDetails._refreshNotizen();
						InfocenterDetails._refreshLog();
						InfocenterDetails._resetNotizFields();
					}
					else
					{
						InfocenterDetails._errorSaveNotiz();
					}
				},
				errorCallback: InfocenterDetails._errorSaveNotiz,
				veilTimeout: 0
			}
		);
	},
	getStudienjahrEnd: function()
	{
		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + "/getStudienjahrEnd",
			{
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (data)
					{
						console.log("studienjahr end executed");
						var engdate = $.datepicker.parseDate("yy-mm-dd", data);
						var gerdate = $.datepicker.formatDate("dd.mm.yy", engdate);
						$("#parkdate").val(gerdate);
					}
				},
				veilTimeout: 0
			}
		);
	},
	getParkedDate: function(personid)
	{
		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + "/getParkedDate/"+encodeURIComponent(personid),
			{
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					InfocenterDetails._refreshParking(data);
					InfocenterDetails._refreshLog();
					if (data === null)
						InfocenterDetails.getStudienjahrEnd();
				},
				veilTimeout: 0
			}
		);
	},
	parkPerson: function(personid, date)
	{
		var parkError = function(){
			$("#parkmsg").text("  Fehler beim Parken!");
		};

		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/park',
			{
				"person_id": personid,
				"parkdate": date
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
						InfocenterDetails.getParkedDate(personid);
					else
					{
						parkError();
					}
				},
				errorCallback: parkError,
				veilTimeout: 0
			}
		);
	},
	unparkPerson: function(personid)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/unpark',
			{
				"person_id": personid
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (Array.isArray(data))
					{
						if (data.length > 0)
							InfocenterDetails.getParkedDate(personid);
						else
							$("#unparkmsg").removeClass().addClass("text-warning").text(FHC_PhraseLib.t('infocenter', 'nichtsZumAusparken'));
					}
				},
				errorCallback: function(){
					$("#unparkmsg").removeClass().addClass("text-danger").text(FHC_PhraseLib.t('infocenter', 'fehlerBeimAusparken'));
				},
				veilTimeout: 0
			}
		);
	},

	// -----------------------------------------------------------------------------------------------------------------
	// (private) methods executed after ajax (refreshers)
	_refreshLog: function()
	{
		var personid = $("#hiddenpersonid").val();
		$("#logs").load(CONTROLLER_URL + '/reloadLogs/' + personid + '?fhc_controller_id=' + fhc_controller_id,
			function ()
			{
				//readd tablesorter
				InfocenterDetails._formatLogTable()
			}
		);
	},
	_formatLogTable: function()
	{
		Tablesort.addTablesorter("logtable", [[0, 1]], ["filter"], 2);
		Tablesort.tablesortAddPager("logtable", "logpager", 22);
		$("#logtable").addClass("table-condensed");
	},
	_refreshNotizen: function()
	{
		$("#notizform").find("input[type=text], textarea").val("");
		var personid = $("#hiddenpersonid").val();
		$("#notizen").load(CONTROLLER_URL + '/reloadNotizen/' + personid,
			function ()
			{
				//readd tablesorter
				InfocenterDetails._formatNotizTable()
			}
		);
	},
	_refreshParking: function(date)
	{
		if (date === null)
		{
			$("#parking").html(
				'<div class="form-group form-inline">'+
					'<button class="btn btn-default" id="parklink" type="button""><i class="fa fa-clock-o"></i>&nbsp;' + FHC_PhraseLib.t('infocenter', 'bewerberParken') + '</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
					FHC_PhraseLib.t('global', 'bis') + '&nbsp;&nbsp;'+
					'<input id="parkdate" type="text" class="form-control" placeholder="Parkdatum" style="height: 25px; width: 99px">&nbsp;'+
					'<span class="text-danger" id="parkmsg"></span>'+
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

					InfocenterDetails.parkPerson(personid, date);
				}
			);
		}
		else
		{
			var parkdate = $.datepicker.parseDate("yy-mm-dd", date);
			var gerparkdate = $.datepicker.formatDate("dd.mm.yy", parkdate);
			$("#parking").html(
				FHC_PhraseLib.t('infocenter', 'bewerberGeparktBis')+'&nbsp;&nbsp;'+gerparkdate+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
				'<button class="btn btn-default" id="unparklink"><i class="fa fa-sign-out"></i>&nbsp;'+FHC_PhraseLib.t('infocenter', 'bewerberAusparken')+'</button>&nbsp;'+
				'<span id="unparkmsg"></span>'
			);

			$("#unparklink").click(
				function ()
				{
					var personid = $("#hiddenpersonid").val();
					InfocenterDetails.unparkPerson(personid, date);
				}
			);
		}
	},
	_formatNotizTable: function()
	{
		Tablesort.addTablesorter("notiztable", [[0, 1]], ["filter"], 2);
		Tablesort.tablesortAddPager("notiztable", "notizpager", 11);
		$("#notiztable").addClass("table-condensed");
	},
	_resetNotizFields: function()
	{
		$("#notizmsg").empty();
		$("#notizform :input[name='hiddenNotizId']").val("");
		$("#notizform label:first").text(FHC_PhraseLib.t('infocenter', 'notizHinzufuegen')).css("color", "black");
		$("#notizform :input[type='reset']").css("display", "none");
	},
	_errorSaveNotiz: function()
	{
		$("#notizmsg").text(FHC_PhraseLib.t('ui', 'fehlerBeimSpeichern'));
	}
};
