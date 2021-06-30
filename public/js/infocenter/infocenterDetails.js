
const BASE_URL = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
const CALLED_PATH = FHC_JS_DATA_STORAGE_OBJECT.called_path;
const CONTROLLER_URL = BASE_URL + "/"+CALLED_PATH;
const RTFREIGABE_MESSAGE_VORLAGE = "InfocenterRTfreigegeben";
const RTFREIGABE_MESSAGE_VORLAGE_MASTER = "InfocenterRTfreigegebenM";
const RTFREIGABE_MESSAGE_VORLAGE_MASTER_ENGLISCH = "InfocenterRTfreigegebenMEnglisch";
const RTFREIGABE_MESSAGE_VORLAGE_QUER = "InfocenterRTfreigegQuer";
const RTFREIGABE_MESSAGE_VORLAGE_QUER_KURZ = "InfocenterRTfreigegQuerKurz";
const STGFREIGABE_MESSAGE_VORLAGE = "InfocenterSTGfreigegeben";
const STGFREIGABE_MESSAGE_VORLAGE_MASTER = "InfocenterSTGfreigegebenM";
const STGFREIGABE_MESSAGE_VORLAGE_MASTER_ENGLISCH = "InfocenterSTGfreigegebenMEng";

//Statusgründe for which no Studiengang Freigabe Message should be sent
const FIT_PROGRAMM_STUDIENGAENGE = [10021, 10027];

const PARKEDNAME = 'parked';
const ONHOLDNAME = 'onhold';

/**
 * javascript file for infocenterDetails page
 */
$(document).ready(function ()
{

	InfocenterDetails._formatMessageTable();
	InfocenterDetails._formatNotizTable();
	InfocenterDetails._formatLogTable();

	var personid = $("#hiddenpersonid").val();

	//add submit event to message send link
	$("#sendmsglink").click(function ()
	{
		$("#sendmsgform").submit();
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

	//check if person is postponed (parked, on hold...) and display it
	InfocenterDetails.getPostponeDate(personid);

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
	);

});

var InfocenterDetails = {

	openZgvInfoForPrestudent: function(prestudent_id)
	{
		var screenwidth = screen.width;
		var popupwidth = 760;
		var marginleft = screenwidth - popupwidth;
		window.open(CONTROLLER_URL + "/getZgvInfoForPrestudent/" + encodeURIComponent(prestudent_id), "_blank","resizable=yes,scrollbars=yes,width="+popupwidth+",height="+screen.height+",left="+marginleft);
	},

	// -----------------------------------------------------------------------------------------------------------------
	// ajax calls
	saveBewPriorisierung: function(data)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/saveBewPriorisierung',
			data,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (!FHC_AjaxClient.hasData(data) || data.retval[0] !== true)
					{
						InfocenterDetails._genericSaveError();
					}
					InfocenterDetails._refreshZgv(true);
				},
				errorCallback: InfocenterDetails._genericSaveError
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

						var zgvmas_code = prestudent.zgvmas_code !== null ? prestudent.zgvmas_code : "null";
						var zgvmaort = prestudent.zgvmaort !== null ? prestudent.zgvmaort : "";
						var zgvmadatum = prestudent.zgvmadatum;
						var gerzgvmadatum = "";
						if (zgvmadatum !== null)
						{
							zgvmadatum = $.datepicker.parseDate("yy-mm-dd", prestudent.zgvmadatum);
							gerzgvmadatum = $.datepicker.formatDate("dd.mm.yy", zgvmadatum);
						}
						var zgvmanation = prestudent.zgvmanation !== null ? prestudent.zgvmanation : "null";

						$("#zgvmas_" + prestudentid).val(zgvmas_code);
						$("#zgvmaort_" + prestudentid).val(zgvmaort);
						$("#zgvmadatum_" + prestudentid).val(gerzgvmadatum);
						$("#zgvmanation_" + prestudentid).val(zgvmanation);
					}
					else
					{
						btn.after("&nbsp;&nbsp;<span id='zgvUebernehmenNotice' class='text-warning'>keine ZGV vorhanden</span>");
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError('Error when getting last ZGV');
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
						InfocenterDetails._genericSaveError();
					}
				},
				errorCallback: InfocenterDetails._genericSaveError
			}
		);
	},
	saveFreigabe: function(freigabeData)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/saveFreigabe',
			{"prestudent_id": freigabeData.prestudent_id, "statusgrund_id": freigabeData.statusgrund_id},
			{
				successCallback: function(data, textStatus, jqXHR) {

					if (FHC_AjaxClient.hasData(data))
					{
						var freigabeResponseData = FHC_AjaxClient.getData(data);

						if (freigabeResponseData.nonCriticalErrors && freigabeResponseData.nonCriticalErrors.length > 0)
						{
							FHC_DialogLib.alertWarning(freigabeResponseData.nonCriticalErrors.join(", "));
						}
						else if (freigabeResponseData.infoMessages && freigabeResponseData.infoMessages.length > 0)
						{
							FHC_DialogLib.alertInfo(freigabeResponseData.infoMessages.join(", "));
						}
						FHC_AjaxClient.showVeil();
						InfocenterDetails.initFrgMessageSend(freigabeData);
						InfocenterDetails._refreshZgv();
						FHC_AjaxClient.hideVeil();
						InfocenterDetails._refreshLog();
					}
					else
					{
						InfocenterDetails._genericSaveError();
					}
				},
				errorCallback: InfocenterDetails._genericSaveError
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
	saveNotiz: function(personid, data, callback)
	{
		var callbackValue = data;
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/saveNotiz/' + encodeURIComponent(personid),
			data,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						InfocenterDetails._refreshNotizen();
						InfocenterDetails._refreshLog();
						if ($.isFunction(callback))
							callback(callbackValue);
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
					if (FHC_AjaxClient.hasData(data))
					{
						var engdate = $.datepicker.parseDate("yy-mm-dd", FHC_AjaxClient.getData(data)[0]);
						var gerdate = $.datepicker.formatDate("dd.mm.yy", engdate);
						$("#postponedate").val(gerdate);
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError("error when getting Studienjahr end");
				},
				veilTimeout: 0
			}
		);
	},
	getPostponeDate: function(personid)
	{
		FHC_AjaxClient.ajaxCallGet(
			CALLED_PATH + "/getPostponeDate/"+encodeURIComponent(personid),
			null,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						var postponeobj = FHC_AjaxClient.getData(data);
						InfocenterDetails._refreshPostpone(postponeobj);
						InfocenterDetails._refreshLog();
						if (postponeobj === null || postponeobj.type === null)
							InfocenterDetails.getStudienjahrEnd();
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError("error when getting parked status");
				},
				veilTimeout: 0
			}
		);
	},
	parkPerson: function(personid, date)
	{
		var parkError = function(){
			$("#postponemsg").text("   Fehler beim Parken!");
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
						InfocenterDetails.getPostponeDate(personid);
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
					if (FHC_AjaxClient.hasData(data))
					{
						InfocenterDetails.getPostponeDate(personid);
					}
					else
						$("#unpostponemsg").removeClass().addClass("text-warning").text(FHC_PhrasesLib.t('infocenter', 'nichtsZumAusparken'));
				},
				errorCallback: function(){
					$("#unpostponemsg").removeClass().addClass("text-danger").text(FHC_PhrasesLib.t('infocenter', 'fehlerBeimAusparken'));
				},
				veilTimeout: 0
			}
		);
	},
	setPersonOnHold: function(personid, date)
	{
		var onHoldError = function(){
			$("#postponemsg").text("   Fehler beim Setzen auf On Hold!");
		};

		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/setOnHold',
			{
				"person_id": personid,
				"onholddate": date
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
						InfocenterDetails.getPostponeDate(personid);
					else
					{
						onHoldError();
					}
				},
				errorCallback: onHoldError,
				veilTimeout: 0
			}
		);
	},
	removePersonOnHold: function(personid)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/removeOnHold',
			{
				"person_id": personid
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						InfocenterDetails.getPostponeDate(personid);
					}
					else
						$("#unpostponemsg").removeClass().addClass("text-warning").text(FHC_PhrasesLib.t('infocenter', 'nichtsZumEntfernen'));
				},
				errorCallback: function(){
					$("#unpostponemsg").removeClass().addClass("text-danger").text(FHC_PhrasesLib.t('infocenter', 'fehlerBeimEntfernen'));
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
				errorCallback: function()
				{
					FHC_DialogLib.alertError("error when getting prestudent data")
				},
				veilTimeout: 0
			}
		);
	},
	initFrgMessageSend: function(freigabedata)
	{
		var personid = $("#hiddenpersonid").val();

		var callback = function (prestudentresponse)
		{
			if (!FHC_AjaxClient.hasData(prestudentresponse))
				return;

			var prestudentdata = prestudentresponse.retval;

			var prestudent_id = freigabedata.prestudent_id;
			var statusgrund_id = freigabedata.statusgrund_id;
			var rtfreigabe = !$.isNumeric(statusgrund_id);//no Statusgrund - RT Freigabe

			var rtFreigegeben = false;
			var stgFreigegeben = false;
			var receiverPrestudent = null;
			var receiverPrestudentstatus = null;

			//get prestudentstatus of message receiver
			for(var i = 0; i < prestudentdata.length; i++)
			{
				if (prestudentdata[i].prestudentstatus.prestudent_id === prestudent_id)
				{
					receiverPrestudent = prestudentdata[i];
					receiverPrestudentstatus = receiverPrestudent.prestudentstatus;
					break;
				}
			}

			if (receiverPrestudent == null || receiverPrestudentstatus == null)
				return;

			//check other prestudentstati wether already freigegeben
			for (var j = 0; j < prestudentdata.length; j++)
			{
				var prestudent = prestudentdata[j];
				var prestudentstatus = prestudent.prestudentstatus;
				var id = prestudentstatus.prestudent_id;

				if (id !== prestudent_id) //exclude receiver prestudentstatus
				{
					var fitstg = $.inArray(parseInt(prestudent.studiengang_kz), FIT_PROGRAMM_STUDIENGAENGE) >= 0;

					if (receiverPrestudentstatus.studiensemester_kurzbz === prestudentstatus.studiensemester_kurzbz
						&& (prestudent.studiengangtyp === "b" || prestudent.studiengangtyp === "m" || fitstg))
					{
						if (prestudent.isRtFreigegeben)
						{
							rtFreigegeben = true;
						}
						else if (prestudent.isStgFreigegeben)
						{
							stgFreigegeben = true;
						}
					}
				}
			}

			var ausbildungssemester = receiverPrestudentstatus.ausbildungssemester;
			var studiengangbezeichnung = receiverPrestudentstatus.studiengangbezeichnung;
			var studiengangbezeichnung_englisch = receiverPrestudentstatus.studiengangbezeichnung_englisch;
			var vorlage = null;

			var orgform_deutsch, orgform_englisch;
			orgform_deutsch = orgform_englisch = "";

			if (typeof receiverPrestudentstatus.bezeichnung_orgform_german === 'string')
			{
				orgform_deutsch = receiverPrestudentstatus.bezeichnung_orgform_german.toLowerCase();
			}

			if (typeof receiverPrestudentstatus.bezeichnung_orgform_english === 'string')
			{
				orgform_englisch = receiverPrestudentstatus.bezeichnung_orgform_english.toLowerCase();
			}

			var quereinstiegsmsgvars = {
				'ausbildungssemester': ausbildungssemester,
				'studiengangbezeichnung': studiengangbezeichnung,
				'studiengangbezeichnung_englisch': studiengangbezeichnung_englisch,
				'orgform_deutsch': orgform_deutsch,
				'orgform_englisch': orgform_englisch
			};

			var msgvars = {};

			if (rtfreigabe)
			{
				if (rtFreigegeben)
				{
					//if already for RT freigegeben, still send short message if Quereinsteiger
					if (ausbildungssemester > 1)
					{
						msgvars = quereinstiegsmsgvars;
						InfocenterDetails.sendFreigabeMessage(prestudent_id, RTFREIGABE_MESSAGE_VORLAGE_QUER_KURZ, msgvars);
					}
				}
				else //not already for RT freigegeben - send RTfreigabe message
				{
					//send Quereinstiegsmessage if later Ausbildungssemester
					if (ausbildungssemester > 1)
					{
						msgvars = quereinstiegsmsgvars;
						vorlage = RTFREIGABE_MESSAGE_VORLAGE_QUER
					}
					else
					{
						//send normal RTfreigabe message
						if (receiverPrestudent.studiengangtyp === 'm') {
							if (receiverPrestudentstatus.sprache === 'English')
								vorlage = RTFREIGABE_MESSAGE_VORLAGE_MASTER_ENGLISCH
							else
								vorlage = RTFREIGABE_MESSAGE_VORLAGE_MASTER
						} else
						{
							vorlage = RTFREIGABE_MESSAGE_VORLAGE
						}
					}

					InfocenterDetails.sendFreigabeMessage(prestudent_id, vorlage, msgvars);
				}
			}
			else
			{
				if (receiverPrestudent.studiengangtyp === 'm' && (freigabedata.statusgrundbezeichnung === 'Ergänzungsprüfungen' || freigabedata.statusgrundbezeichnung === 'Supplementary exams'))
				{
					msgvars = {
						'studiengangbezeichnung': studiengangbezeichnung,
						'studiengangbezeichnung_englisch': studiengangbezeichnung_englisch,
						'orgform_deutsch': orgform_deutsch,
						'orgform_englisch': orgform_englisch
					}
					if (receiverPrestudentstatus.sprache === 'English')
						vorlage = STGFREIGABE_MESSAGE_VORLAGE_MASTER_ENGLISCH
					else
						vorlage = STGFREIGABE_MESSAGE_VORLAGE_MASTER

					InfocenterDetails.sendFreigabeMessage(prestudent_id, vorlage, msgvars);
				}
				//if Freigabe to Studiengang, send StgFreigabe Message if not already sent and allowed to send
				else if (!stgFreigegeben && receiverPrestudent.sendStgFreigabeMsg === true)
				{
					InfocenterDetails.sendFreigabeMessage(prestudent_id, STGFREIGABE_MESSAGE_VORLAGE, msgvars);
				}
			}
		};

		InfocenterDetails.getPrestudentData(
			personid, callback
		);
	},
	sendFreigabeMessage: function(prestudentid, vorlage_kurzbz, msgvars)
	{
		FHC_AjaxClient.ajaxCallPost(
			'system/messages/Messages/sendExplicitTemplateJson',
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
				},
				errorCallback: function() {
					FHC_DialogLib.alertWarning("Freigabe message could not be sent");
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
			var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
			var data = {
				"prestudentid": prestudentid,
				"change": -1
			};
			InfocenterDetails.saveBewPriorisierung(data);
		});
		$(".priodown").click(function ()
		{
			var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
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
			var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
			$('#zgvUebernehmenNotice').remove();
			InfocenterDetails.zgvUebernehmen(personid, prestudentid, btn);
		});

		$('.notizModal').on('hidden.bs.modal', function () {
			$(':input', this).val('');
		});

		//zgv speichern
		$(".saveZgv").click(function ()
			{
				var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
				var formdata = $("#zgvform_" + prestudentid).serializeArray();

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
				var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
				InfocenterDetails.openZgvInfoForPrestudent(prestudentid);
			}
		);

		$(".freigabebtn").click(function()
			{
				var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
				//true - Reihungstestfreigabe
				InfocenterDetails._toggleFreigabeDialog(prestudentid, true);
			}
		);

		$(".freigabebtnstg").click(function()
			{
				var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
				var statusgrel = $("#frgstatusgrselect_"+prestudentid+" select[name=frgstatusgrund]");
				var statusgrund_id = statusgrel.val();
				var statusgrund = statusgrel.find("option:selected").text();

				if (!$.isNumeric(statusgrund_id))
				{
					$("#frgstatusgrselect_" + prestudentid).addClass("has-error");
				}
				else
				{
					//false - no Reihungstestfreigabe
					InfocenterDetails._toggleFreigabeDialog(prestudentid, false, statusgrund);
				}
			}
		);

		$(".absageBtn").click(function()
			{
				var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
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
				var prestudent_id = InfocenterDetails._getPrestudentIdFromElementId(this.id);
				var statusgrund_id = $("#absgstatusgrselect_" + prestudent_id + " select[name=absgstatusgrund]").val();
				var data = {"prestudent_id": prestudent_id , "statusgrund": statusgrund_id};
				InfocenterDetails.saveAbsage(data);
			}
		);

		$(".saveFreigabe").click(function()
			{
				$(".freigabeModal").modal("hide");
				var prestudent_id = InfocenterDetails._getPrestudentIdFromElementId(this.id);
				var data = {"prestudent_id": prestudent_id, "statusgrund_id": null};
				InfocenterDetails.saveFreigabe(data);//Reihungstestfreigabe
			}
		);

		$(".saveStgFreigabe").click(function()
			{
				$(".freigabeModal").modal("hide");
				var prestudent_id = InfocenterDetails._getPrestudentIdFromElementId(this.id);
				var statusgrundel = $("#frgstatusgrselect_" + prestudent_id + " select[name=frgstatusgrund]");
				var statusgrund_id = statusgrundel.val();
				var statusgrundbezeichnung = statusgrundel.find("option[value="+statusgrund_id+"]").text();
				var data = {"prestudent_id": prestudent_id, "statusgrund_id": statusgrund_id, "statusgrundbezeichnung": statusgrundbezeichnung};
				InfocenterDetails.saveFreigabe(data);//Studiengangfreigabe
			}
		);
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
				// call to UDFWidget again to add events and other JS functionality (before _addZgvPruefungEvents because it adds bootstrap format)
				FHC_UDFWidget.display();
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

		zgvUeberpruefung.checkAfterReload();
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
	_refreshPostpone: function(postponeobj)
	{
		var personid = $("#hiddenpersonid").val();
		if (postponeobj === null || postponeobj.date === null || postponeobj.type === null)
		{
			//show both park and on hold buttons if not parked and not on hold
			$("#postponing").html(
				'<div class="form-group form-inline">'+
					'<button class="btn btn-default" id="parklink" type="button""><i class="fa fa-clock-o"></i>&nbsp;' + FHC_PhrasesLib.t('infocenter', 'bewerberParken') + '</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
					'<button class="btn btn-default" id="onholdlink" type="button""><i class="fa fa-anchor"></i>&nbsp;' + FHC_PhrasesLib.t('infocenter', 'bewerberOnHold') + '</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
					'<label id="postponedatelabel">'+FHC_PhrasesLib.t('global', 'bis') + '&nbsp;&nbsp;'+
					'<input id="postponedate" type="text" class="form-control" placeholder="Parkdatum">&nbsp;'+
					'<i class="fa fa-info-circle"  data-toggle="tooltip" title="'+FHC_PhrasesLib.t('infocenter', 'parkenZurueckstellenInfo')+'"></i></label>'+
					'<span class="text-danger" id="postponemsg"></span>'+
				'</div>');

			$("#postponedate").datepicker({
				"dateFormat": "dd.mm.yy",
				"minDate": 1
			});

			$("#parklink").click(

				function ()
				{
					var date = $("#postponedate").val();
					InfocenterDetails.parkPerson(personid, date);
				}
			);

			$("#onholdlink").click(

				function ()
				{
					var date = $("#postponedate").val();
					InfocenterDetails.setPersonOnHold(personid, date);
				}
			);
		}
		else
		{
			//info if parked/on hold and possibility to undo parking/on hold
			var postponedate = $.datepicker.parseDate("yy-mm-dd", postponeobj.date);
			var gerpostponedate = $.datepicker.formatDate("dd.mm.yy", postponedate);

			//var postponehtml = "";
			var callbackforundo = null;
			var removePhrase = "";
			var postponedPhrase = "";
			var postponedtext = "";

			if (postponeobj.type === PARKEDNAME)
			{
				removePhrase = 'bewerberAusparken';
				postponedtext = FHC_PhrasesLib.t('infocenter', 'bewerberGeparktBis')+'&nbsp;&nbsp;'+gerpostponedate;

				callbackforundo = function ()
				{
					InfocenterDetails.unparkPerson(personid);
				}
			}
			else if (postponeobj.type === ONHOLDNAME)
			{
				removePhrase = 'bewerberOnHoldEntfernen';
				postponedtext = FHC_PhrasesLib.t('infocenter', 'bewerberOnHoldBis')+'&nbsp;&nbsp;'+gerpostponedate;

				var currdate = new Date();

				if (currdate > postponedate)
					postponedtext = "<span class='alert-danger' data-toggle='tooltip' title='"+FHC_PhrasesLib.t('infocenter', 'rueckstelldatumUeberschritten')+"'>"+postponedtext+"</span>";

				callbackforundo = function ()
				{
					InfocenterDetails.removePersonOnHold(personid);
				}
			}

			var postponehtml = postponedtext+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
				'<button class="btn btn-default" id="unpostponelink"><i class="fa fa-sign-out"></i>&nbsp;'+FHC_PhrasesLib.t('infocenter', removePhrase)+'</button>&nbsp;'+
				'<span id="unpostponemsg"></span>';


			$("#postponing").html(
				postponehtml
			);

			$("#unpostponelink").click(
				callbackforundo
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
	},
	_genericSaveError: function() {
		FHC_DialogLib.alertError("error when saving!");
	},
	_getPrestudentIdFromElementId(elementid)
	{
		return elementid.substr(elementid.indexOf("_") + 1);
	}
};
