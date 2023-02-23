const CONTROLLER_RUECKSTELLUNG_URL = "system/infocenter/Rueckstellung";

var Rueckstellung = {
	get: function(personid)
	{
		FHC_AjaxClient.ajaxCallGet(
			CONTROLLER_RUECKSTELLUNG_URL + "/get/"+encodeURIComponent(personid),
			null,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						var rueckstellungobj = FHC_AjaxClient.getData(data);
						Rueckstellung._refreshRueckstellung(rueckstellungobj);
					}
					else
					{
						Rueckstellung._addRueckstellungButtons();
					}

				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError("error when getting rueckstellung status");
				},
				veilTimeout: 0
			}
		);
	},
	getStatus: function()
	{
		FHC_AjaxClient.ajaxCallGet(
			CONTROLLER_RUECKSTELLUNG_URL + "/getStatus",
			null,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						let status = FHC_AjaxClient.getData(data);
						$.each(status, function(key, value)
						{
							$('#rueckstellungtype').append($("<option/>")
								.val(value.status_kurzbz)
								.text(value.bezeichnung_mehrsprachig[0])
							)
						});
					}
				},
				errorCallback: function()
				{
					FHC_DialogLib.alertError("error when getting rueckstellung status");
				},
				veilTimeout: 0
			}
		);
	},
	set: function(personid, date, type)
	{
		if (type === null)
			return false;

		var onRueckstellungError = function(){
			$("#rueckstellungmsg").text("   Fehler beim Setzen auf " + type + "!");
		};

		FHC_AjaxClient.ajaxCallPost(
			CONTROLLER_RUECKSTELLUNG_URL + '/set',
			{
				"person_id": personid,
				"datum_bis": date,
				"status_kurzbz": type,
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
						Rueckstellung.get(personid);
					else
					{
						onRueckstellungError();
					}
				},
				errorCallback: onRueckstellungError,
				veilTimeout: 0
			}
		);
	},
	delete: function(personid, status = null)
	{
		FHC_AjaxClient.ajaxCallPost(
			CONTROLLER_RUECKSTELLUNG_URL + '/delete',
			{
				"person_id": personid,
				"status": status
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						Rueckstellung.get(personid);
					}
					else
						$("#unrueckstellungmsg").removeClass().addClass("text-warning").text(FHC_PhrasesLib.t('infocenter', 'nichtsZumEntfernen'));
				},
				errorCallback: function(){
					$("#unrueckstellungmsg").removeClass().addClass("text-danger").text(FHC_PhrasesLib.t('infocenter', 'fehlerBeimEntfernen'));
				},
				veilTimeout: 0
			}
		);
	},
	getStudienjahrEnd: function()
	{
		FHC_AjaxClient.ajaxCallGet(
			CONTROLLER_RUECKSTELLUNG_URL + "/getStudienjahrEnd",
			null,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						var engdate = $.datepicker.parseDate("yy-mm-dd", FHC_AjaxClient.getData(data)[0]);

						if (engdate.getDate() === 31)
							engdate.setDate(engdate.getDate() - 1);
						engdate.setMonth(engdate.getMonth() + 3);

						var gerdate = $.datepicker.formatDate("dd.mm.yy", engdate);
						$("#rueckstellungdate").val(gerdate);
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
	_refreshRueckstellung: function(rueckstellungobj)
	{
		var personid = $("#hiddenpersonid").val();
		var rueckstellungdate = $.datepicker.parseDate("yy-mm-dd", rueckstellungobj.datum_bis);
		var gerrueckstellungdate = $.datepicker.formatDate("dd.mm.yy", rueckstellungdate);

		var removetext = FHC_PhrasesLib.t('infocenter', 'statusZuruecksetzen');
		var rueckstellungdtext = FHC_PhrasesLib.t('global', 'status') + ": '" + rueckstellungobj.bezeichnung + "' " + FHC_PhrasesLib.t('global', 'bis') + ": " + gerrueckstellungdate;
		var currdate = new Date();


		if (currdate > rueckstellungdate)
			rueckstellungdtext = "<span class='alert-danger' data-toggle='tooltip' title='"+FHC_PhrasesLib.t('infocenter', 'datumuberschritten')+"'>"+rueckstellungdtext+"</span>";

		var callbackforundo = function ()
		{
			Rueckstellung.delete(personid);
		}

		var rueckstellunghtml = rueckstellungdtext+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
			'<button class="btn btn-default" id="unrueckstellunglink"><i class="fa fa-sign-out"></i>&nbsp;' + removetext +'</button>&nbsp;'+
			'<span id="unrueckstellungmsg"></span>';

		$("#postponing").html(
			rueckstellunghtml
		);

		$("#unrueckstellunglink").click(
			callbackforundo
		);
	},

	_addRueckstellungButtons: function()
	{
		var personid = $("#hiddenpersonid").val();
		$("#postponing").html(
			'<div class="form-group form-inline">'+
			'<select id="rueckstellungtype" class="form-control">' +
				'<option disabled selected>' + FHC_PhrasesLib.t('infocenter', 'statusAuswahl') + '</option>' +
			'</select>' + '&nbsp;&nbsp;' +
			'<button class="btn btn-default" id="addRueckstellung" type="button""><i class="fa fa-clock-o"></i>&nbsp;' + FHC_PhrasesLib.t('infocenter', 'statusSetzen') + '</button>&nbsp;'+
			'<label id="rueckstellungdatelabel">'+FHC_PhrasesLib.t('global', 'bis') + '&nbsp;&nbsp;'+
			'<input id="rueckstellungdate" type="text" class="form-control" placeholder="Parkdatum">&nbsp;'+
			'<i class="fa fa-info-circle"  data-toggle="tooltip" title="'+FHC_PhrasesLib.t('infocenter', 'parkenZurueckstellenInfo')+'"></i></label>'+
			'<span class="text-danger" id="rueckstellungmsg"></span>'+
			'</div>');

		Rueckstellung.getStatus();
		Rueckstellung.getStudienjahrEnd();

		$("#rueckstellungdate").datepicker({
			"dateFormat": "dd.mm.yy",
			"minDate": 1
		});

		$("#addRueckstellung").click(
			function ()
			{
				var date = $("#rueckstellungdate").val();
				var type = $("#rueckstellungtype").val();
				Rueckstellung.set(personid, date, type);
			}
		);
	},

}