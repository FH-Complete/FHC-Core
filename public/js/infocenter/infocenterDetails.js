
const BASE_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
const CALLED_PATH = FHC_JS_DATA_STORAGE_OBJECT.called_path;
const CONTROLLER_URL = BASE_URL + "/"+CALLED_PATH;
const RTFREIGABE_MESSAGE_VORLAGE = "InfocenterRTfreigegeben";
const RTFREIGABE_MESSAGE_VORLAGE_QUER = "InfocenterRTfreigegQuer";
const RTFREIGABE_MESSAGE_VORLAGE_QUER_KURZ = "InfocenterRTfreigegQuerKurz";
const STGFREIGABE_MESSAGE_VORLAGE = "InfocenterSTGfreigegeben";

/**
 * javascript file for infocenterDetails page
 */
$(document).ready(function ()
{
		//initialise table sorter
		Tablesort.addTablesorter("doctable", [[2, 1], [1, 0]], ["zebra"]);
		Tablesort.addTablesorter("nachgdoctable", [[2, 0], [1, 1]], ["zebra"]);

		InfocenterDetails._formatMessageTable();
		InfocenterDetails._formatNotizTable();
		InfocenterDetails._formatLogTable();

		var personid = $("#hiddenpersonid").val();

		//add submit event to message send link
		$("#sendmsglink").click(function ()
		{
			$("#sendmsgform").submit();
		});

		//add click events to "formal geprüft" checkboxes
		$(".prchbox").click(function ()
		{
			var boxid = this.id;
			var akteid = boxid.substr(boxid.indexOf("_") + 1);
			var checked = this.checked;
			InfocenterDetails.saveFormalGeprueft(personid, akteid, checked)
		});

		//add click events to zgv Prüfung section
		InfocenterDetails._addZgvPruefungEvents(personid);

		MessageList.initMessageList();

		//save notiz
		$("#notizform").on("submit", function (e)
			{
				e.preventDefault();
				var notizid = $("#notizform :input[name='hiddenNotizId']").val();
				var formdata = $(this).serializeArray();
				var data = {};

				data.person_id = personid;

				for (var i = 0; i < formdata.length; i++)
				{
					data[formdata[i].name] = formdata[i].value;
				}

				$("#notizmsg").empty();

				if (notizid !== '')
				{
					InfocenterDetails.updateNotiz(notizid, data);
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

				var notizid = $(this).find("td.hiddennotizid").html();

				InfocenterDetails.getNotiz(notizid);
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

		if ($(document).scrollTop() > 20)
			$("#scrollToTop").show();

		//scroll to top button
		$(window).scroll(function()
			{
				if ($(document).scrollTop() > 20)
					$("#scrollToTop").show();
				else
					$("#scrollToTop").hide();
			}
		);

		$("#scrollToTop").click(function()
			{
				$('html,body').animate({scrollTop:0},250,'linear');
			}
		)

	});

var InfocenterDetails = {

	genericSaveError: function() {
		FHC_DialogLib.alertError("error when saving");
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
	saveBewPriorisierung: function(data)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/saveBewPriorisierung',
			data,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (data !== true)
					{
						FHC_DialogLib.alertError("error when saving ZGV prio");
					}
					InfocenterDetails._refreshZgv(true);
				}
			}
		);
	},
	zgvUebernehmen: function(personid, prestudentid, btn)
	{
		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + "/getLastPrestudentWithZgvJson/" + encodeURIComponent(personid),
			null,
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
	saveZgv: function(data)
	{
		var zgvError = function(){
			$("#zgvSpeichern_" + prestudentid).before("<span id='zgvSpeichernNotice' class='text-danger'>" + FHC_PhrasesLib.t('ui', 'fehlerBeimSpeichern') + "</span>&nbsp;&nbsp;");
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
						$("#zgvSpeichern_" + prestudentid).before("<span id='zgvSpeichernNotice' class='text-success'>" + FHC_PhrasesLib.t('ui', 'gespeichert') + "</span>&nbsp;&nbsp;");
						InfocenterDetails._refreshLog();
					}
					else
					{
						zgvError();
					}
				},
				errorCallback: zgvError
			}
		);
	},
	saveAbsage: function(data)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/saveAbsage',
			data,
			{
				successCallback: function(data, textStatus, jqXHR) {

					if (FHC_AjaxClient.hasData(data))
					{
						InfocenterDetails._refreshZgv();
						InfocenterDetails._refreshLog();
					}
					else
					{
						FHC_DialogLib.alertError("error when saving Absage");
					}
				}
			}
		);
	},
	saveFreigabe: function(data, rtfreigabe)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/saveFreigabe',
			data,
			{
				successCallback: function(data, textStatus, jqXHR) {

					if (FHC_AjaxClient.hasData(data))
					{
						FHC_AjaxClient.showVeil();
						InfocenterDetails.initFrgMessageSend(data.retval.prestudent_id, rtfreigabe);
						InfocenterDetails._refreshZgv();
						FHC_AjaxClient.hideVeil();
						InfocenterDetails._refreshLog();
					}
					else if (data.error === 2 && parseInt(data.retval.prestudent_id, 10))
					{
						FHC_DialogLib.alertError("error when setting accepted documents");
						InfocenterDetails._refreshZgv();
						InfocenterDetails._refreshLog();
					}
					else
					{
						FHC_DialogLib.alertError("error when saving Freigabe");
					}
				}
			}
		);
	},
	getNotiz: function(notiz_id)
	{
		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + '/getNotiz',
			{
				"notiz_id": notiz_id
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						var notiz = data.retval[0];

						$("#notizform label:first").text(FHC_PhrasesLib.t('infocenter', 'notizAendern')).css("color", "red");
						$("#notizform :input[type='reset']").css("display", "inline-block");

						$("#notizform :input[name='hiddenNotizId']").val(notiz_id);
						$("#notizform :input[name='notiztitel']").val(notiz.titel);
						$("#notizform :input[name='notiz']").val(notiz.text);
					}
					else
					{
						InfocenterDetails._notizError('fehlerBeimLesen');
					}
				},
				errorCallback: function()
				{
					InfocenterDetails._notizError('fehlerBeimLesen');
				},
				veilTimeout: 0
			}
		);
	},
	saveNotiz: function(personid, data)
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
						InfocenterDetails._notizError('fehlerBeimSpeichern');
					}
				},
				errorCallback: function()
				{
					InfocenterDetails._notizError('fehlerBeimSpeichern');
				},
				veilTimeout: 0
			}
		);
	},
	updateNotiz: function(notizid, data)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/updateNotiz/' + encodeURIComponent(notizid),
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
						InfocenterDetails._notizError('fehlerBeimSpeichern');
					}
				},
				errorCallback: function()
				{
					InfocenterDetails._notizError('fehlerBeimSpeichern');
				},
				veilTimeout: 0
			}
		);
	},
	getStudienjahrEnd: function()
	{
		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + "/getStudienjahrEnd",
			null,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (data)
					{
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
			null,
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
							$("#unparkmsg").removeClass().addClass("text-warning").text(FHC_PhrasesLib.t('infocenter', 'nichtsZumAusparken'));
					}
				},
				errorCallback: function(){
					$("#unparkmsg").removeClass().addClass("text-danger").text(FHC_PhrasesLib.t('infocenter', 'fehlerBeimAusparken'));
				},
				veilTimeout: 0
			}
		);
	},
	getPrestudentData: function(personid, callback)
	{
		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + "/getPrestudentData/"+encodeURIComponent(personid),
			null,
			{
				successCallback: callback,
				veilTimeout: 0
			}
		);
	},
	initFrgMessageSend: function(prestudentid, rtfreigabe)
	{
		var callback = function (data)
		{
			if (data == null)
				return;

			// check if a prestudent in same semester is already freigegeben - then not send message again
			var rtFreigegeben = false;
			var stgFreigegeben = false;
			var receiverPrestudentstatus = null;

			//get prestudentstatus of message receiver
			for(var i = 0; i < data.length; i++)
			{
				if (data[i].prestudentstatus.prestudent_id === prestudentid)
				{
					receiverPrestudentstatus = data[i].prestudentstatus;
					break;
				}
			}

			if (receiverPrestudentstatus == null)
				return;

			//check other prestudentstati wether already freigegeben
			for(var j = 0; j < data.length; j++)
			{
				var prestudent = data[j];
				var prestudentstatus = prestudent.prestudentstatus;
				var id = prestudentstatus.prestudent_id;

				if (id !== prestudentid)
				{
					if (receiverPrestudentstatus.studiensemester_kurzbz === prestudentstatus.studiensemester_kurzbz
						&& prestudentstatus.bestaetigtam !== null && prestudentstatus.status_kurzbz === "Interessent"
						&& prestudent.studiengangtyp === "b")
					{
						if (prestudentstatus.statusgrund_id === null)
						{
							rtFreigegeben = true;
							break;
						}
						else if($.isNumeric(prestudentstatus.statusgrund_id))
						{
							stgFreigegeben = true;
						}
					}
				}
			}

			var ausbildungssemester = receiverPrestudentstatus.ausbildungssemester;
			var studiengangbezeichnung = receiverPrestudentstatus.studiengangbezeichnung;
			var studiengangbezeichnung_englisch = receiverPrestudentstatus.studiengangbezeichnung_englisch;
			var msgvars = {};

			if (rtfreigabe)
			{
				if (rtFreigegeben)
				{
					//if already for RT freigegeben, still send short message if Quereinsteiger
					if (ausbildungssemester > 1)
					{
						msgvars = {
							'ausbildungssemester': ausbildungssemester,
							'studiengangbezeichnung': studiengangbezeichnung,
							'studiengangbezeichnung_englisch': studiengangbezeichnung_englisch
						};

						InfocenterDetails.sendFreigabeMessage(prestudentid, RTFREIGABE_MESSAGE_VORLAGE_QUER_KURZ, msgvars);
					}
				}
				else //not already for RT freigegeben - send RTfreigabe message
				{
					var vorlage = null;
					//send Quereinstiegsmessage if later Ausbildungssemester
					if (ausbildungssemester > 1)
					{
						msgvars = {
							'ausbildungssemester': ausbildungssemester,
							'studiengangbezeichnung': studiengangbezeichnung,
							'studiengangbezeichnung_englisch': studiengangbezeichnung_englisch
						};
						vorlage = RTFREIGABE_MESSAGE_VORLAGE_QUER
					}
					else
					{
						//send normal RTfreigabe message
						vorlage = RTFREIGABE_MESSAGE_VORLAGE
					}

					InfocenterDetails.sendFreigabeMessage(prestudentid, vorlage, msgvars);
				}
			}
			else if (rtfreigabe === false)
			{
				// if Freigabe to Studiengang, send StgFreigabe Message if not already sent
				if (!stgFreigegeben)
				{
					InfocenterDetails.sendFreigabeMessage(prestudentid, STGFREIGABE_MESSAGE_VORLAGE, msgvars);
				}
			}
		};

		var personid = $("#hiddenpersonid").val();

		InfocenterDetails.getPrestudentData(
			personid, callback
		)
	},
	sendFreigabeMessage: function(prestudentid, vorlage_kurzbz, msgvars)
	{
		FHC_AjaxClient.ajaxCallPost(
			'system/Messages/sendJson',
			{
				"prestudents": prestudentid,
				"vorlage_kurzbz": vorlage_kurzbz,
				"oe_kurzbz": 'infocenter',
				"msgvars": msgvars
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					InfocenterDetails._refreshMessages();
					InfocenterDetails._refreshLog();
				}
			}
		);
	},

	// -----------------------------------------------------------------------------------------------------------------
	// (private) methods executed after ajax (refreshers)

	//adds JQuery events to ZGVprüfung section
	_addZgvPruefungEvents: function(personid)
	{
		//add bootstrap to forms
		Bootstrapper.bootstraphtml();

		//initialise datepicker
		$.datepicker.setDefaults($.datepicker.regional['de']);
		$(".dateinput").datepicker({
			"dateFormat": "dd.mm.yy"
		});

		//up/down prioritize Bewerbungen
		$(".prioup").click(function ()
		{
			var prestudentid = this.id.substr(this.id.indexOf("_") + 1);
			var data = {
				"prestudentid": prestudentid,
				"change": -1
			};
			InfocenterDetails.saveBewPriorisierung(data);
		});
		$(".priodown").click(function ()
		{
			var prestudentid = this.id.substr(this.id.indexOf("_") + 1);
			var data = {
				"prestudentid": prestudentid,
				"change": 1
			};
			InfocenterDetails.saveBewPriorisierung(data);
		});

		//zgv übernehmen
		$(".zgvUebernehmen").click(function ()
		{
			var btn = $(this);
			var prestudentid = this.id.substr(this.id.indexOf("_") + 1);
			$('#zgvUebernehmenNotice').remove();
			InfocenterDetails.zgvUebernehmen(personid, prestudentid, btn);
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

		$(".freigabebtn").click(function()
			{
				var prestudentid = this.id.substr(this.id.indexOf("_") + 1);
				InfocenterDetails._toggleFreigabeDialog(prestudentid, true);//true - Reihungstestfreigabe
			}
		);

		$(".freigabebtnstg").click(function()
			{
				var prestudentid = this.id.substr(this.id.indexOf("_") + 1);
				var statusgrel = $("#frgstatusgrselect_"+prestudentid+" select[name=frgstatusgrund]");
				var statusgrund_id = statusgrel.val();
				var statusgrund = statusgrel.find("option:selected").text();

				if (statusgrund_id === 'null')
				{
					$("#frgstatusgrselect_" + prestudentid).addClass("has-error");
				}
				else
				{
					var rtfreigabe = false;//no Reihungstestfreigabe
					InfocenterDetails._toggleFreigabeDialog(prestudentid, rtfreigabe, statusgrund);
				}
			}
		);

		$(".absageBtn").click(function()
			{
				var prestudentid = this.id.substr(this.id.indexOf("_") + 1);
				var statusgrund = $("#absgstatusgrselect_" + prestudentid + " select[name=absgstatusgrund]").val();
				if (statusgrund === "null")
					$("#absgstatusgrselect_" + prestudentid).addClass("has-error");
				else
					$("#absageModal_"+prestudentid).modal("show");
			}
		);

		//remove red mark when statusgrund is selected again
		$("select[name=absgstatusgrund],select[name=frgstatusgrund]").change(
			function ()
			{
				$(this).parent().removeClass("has-error");
			}
		);

		$(".saveAbsage").click(function()
			{
				$(".absageModal").modal("hide");
				var prestudent_id = this.id.substr(this.id.indexOf("_") + 1);
				var statusgrund_id = $("#absgstatusgrselect_" + prestudent_id + " select[name=absgstatusgrund]").val();
				var data = {"prestudent_id": prestudent_id , "statusgrund": statusgrund_id};
				InfocenterDetails.saveAbsage(data);
			}
		);

		$(".saveFreigabe").click(function()
			{
				$(".freigabeModal").modal("hide");
				var prestudent_id = this.id.substr(this.id.indexOf("_") + 1);
				var data = {"prestudent_id": prestudent_id};
				InfocenterDetails.saveFreigabe(data, true);//Reihungstestfreigabe
			}
		);

		$(".saveStgFreigabe").click(function()
			{
				$(".freigabeModal").modal("hide");
				var prestudent_id = this.id.substr(this.id.indexOf("_") + 1);
				var statusgrund_id = $("#frgstatusgrselect_" + prestudent_id + " select[name=frgstatusgrund]").val();
				var data = {"prestudent_id": prestudent_id, "statusgrund_id": statusgrund_id};
				InfocenterDetails.saveFreigabe(data, false);//Studiengangfreigabe
			}
		)
	},
	_refreshZgv: function(preserveCollapseState)
	{
		var personid = $("#hiddenpersonid").val();

		var collapsed = {};

		//check if panel is collapsed to preserve collapse state
		if (preserveCollapseState)
		{
			$("#zgvpruefungen").find(".panel-collapse").each(
				function()
				{
					var collapseid = $(this).prop("id");
					collapsed[collapseid] = !$(this).hasClass('collapse in');
				}
			);
		}

		$("#zgvpruefungen").load(
			CONTROLLER_URL + '/reloadZgvPruefungen/' + personid + '?fhc_controller_id=' + FHC_AjaxClient.getUrlParameter('fhc_controller_id'),
			function()
			{
				InfocenterDetails._addZgvPruefungEvents(personid);
				if (preserveCollapseState)
				{
					for (var i in collapsed)
					{
						if (collapsed[i])
							$("#"+i).removeClass("in");
						else
							$("#"+i).addClass("in");
					}
				}
			}
		);
	},
	_refreshMessages: function()
	{
		var personid = $("#hiddenpersonid").val();
		$("#messagelist").load(
			CONTROLLER_URL + '/reloadMessages/' + personid + '?fhc_controller_id=' + FHC_AjaxClient.getUrlParameter('fhc_controller_id'),
			function () {
				MessageList.initMessageList();
				InfocenterDetails._formatMessageTable();
			}
		);
	},
	_refreshLog: function()
	{
		var personid = $("#hiddenpersonid").val();
		$("#logs").load(
			CONTROLLER_URL + '/reloadLogs/' + personid + '?fhc_controller_id=' + FHC_AjaxClient.getUrlParameter('fhc_controller_id'),
			function () {
				//readd tablesorter
				InfocenterDetails._formatLogTable()
			}
		);
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
					'<button class="btn btn-default" id="parklink" type="button""><i class="fa fa-clock-o"></i>&nbsp;' + FHC_PhrasesLib.t('infocenter', 'bewerberParken') + '</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
					FHC_PhrasesLib.t('global', 'bis') + '&nbsp;&nbsp;'+
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
				FHC_PhrasesLib.t('infocenter', 'bewerberGeparktBis')+'&nbsp;&nbsp;'+gerparkdate+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
				'<button class="btn btn-default" id="unparklink"><i class="fa fa-sign-out"></i>&nbsp;'+FHC_PhrasesLib.t('infocenter', 'bewerberAusparken')+'</button>&nbsp;'+
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
	_formatMessageTable: function()
	{
		Tablesort.addTablesorter("msgtable", [[0, 1], [2, 0]], ["zebra", "filter"], 2);
		Tablesort.tablesortAddPager("msgtable", "msgpager", 14);
	},
	_formatNotizTable: function()
	{
		Tablesort.addTablesorter("notiztable", [[0, 1]], ["filter"], 2);
		Tablesort.tablesortAddPager("notiztable", "notizpager", 11);
		$("#notiztable").addClass("table-condensed");
	},
	_formatLogTable: function()
	{
		Tablesort.addTablesorter("logtable", [[0, 1]], ["filter"], 2);
		Tablesort.tablesortAddPager("logtable", "logpager", 22);
		$("#logtable").addClass("table-condensed");
	},
	_toggleFreigabeDialog: function(prestudentid, rtfreigabe, statusgrund)
	{
		var statusgrundspan = $("#freigabeModalStgr_"+prestudentid);
		var freigabebtn = $("#saveFreigabe_"+prestudentid);
		var stgfreigabebtn = $("#saveStgFreigabe_"+prestudentid);

		if (rtfreigabe)
		{
			statusgrundspan.text(" - Reihungstest");
			freigabebtn.show();
			stgfreigabebtn.hide();
		}
		else
		{
			if (statusgrund !== "undefined" && statusgrund !== null)
				statusgrundspan.text(" - "+statusgrund);
			freigabebtn.hide();
			stgfreigabebtn.show();
		}

		$("#freigabeModal_"+prestudentid).modal("show");
	},
	_resetNotizFields: function()
	{
		$("#notizmsg").empty();
		$("#notizform :input[name='hiddenNotizId']").val("");
		$("#notizform label:first").text(FHC_PhrasesLib.t('infocenter', 'notizHinzufuegen')).css("color", "black");
		$("#notizform :input[type='reset']").css("display", "none");
	},
	_notizError: function(phrasename)
	{
		$("#notizmsg").text(FHC_PhrasesLib.t('ui', phrasename));
	}
};
