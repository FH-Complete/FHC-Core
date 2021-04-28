$(document).ready(function ()
{
	var personid = $("#hiddenpersonid").val();

	zgvUeberpruefung.checkAfterReload();

	$('.zgvRueckfragen').click(function ()
	{
		var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);

		var data = {
			'person_id' : personid,
			'prestudent_id' : prestudentid
		}
		zgvUeberpruefung.zgvRueckfragen(data);
	});

	$('.zgvAkzeptieren').click(function (){
		var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);

		var data = {
			'person_id' : personid,
			'prestudent_id' : prestudentid,
			'status' : 'accepted'
		}
		zgvUeberpruefung.zgvStatusUpdate(data);
	});

	$('.zgvAblehnen').click(function (){
		var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
		$('#inputStatus_' + prestudentid).val('rejected');
		$('#notizModal_' + prestudentid).modal('show');
	});

	$('.zgvAkzeptierenPruefung').click(function (){
		var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);
		$('#inputStatus_' + prestudentid).val('accepted_pruefung');
		$('#notizModal_' + prestudentid).modal('show');
	});

	$('.saveZgvNotiz').click(function (){
		var prestudentid = InfocenterDetails._getPrestudentIdFromElementId(this.id);

		if ($('#inputNotizTitelModal').val() === '' || $('#inputNotizTextModal').val() === '')
			return FHC_DialogLib.alertWarning('Please fill out all fields');

		var data = {
			'person_id' : personid,
			'notiztitel' : $('#inputNotizTitelModal').val(),
			'notiz' : $('#inputNotizTextModal').val(),
			'prestudent_id' : prestudentid,
			'status' : $('#inputStatus_' + prestudentid).val()
		}

		InfocenterDetails.saveNotiz(personid, data, zgvUeberpruefung.zgvStatusUpdate);

		$('#notizModal_' + prestudentid).modal('hide');
	});
});

var zgvUeberpruefung = {
	checkStatus: function(prestudent_id)
	{
		FHC_AjaxClient.ajaxCallGet(
			"system/infocenter/ZGVUeberpruefung/getZgvStatusByPrestudent",
			{
				prestudent_id : prestudent_id
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						$('#zgvBearbeitungButtons_' + prestudent_id +' button').each(function() {
							$(this).attr('disabled', false);
						});

						var status = FHC_AjaxClient.getData(data);

						switch (status)
						{
							case 'rejected' :
								$('#zgvAblehnen_' + prestudent_id).attr('disabled', true);
								$('#zgvStatusText_' + prestudent_id).text(FHC_PhrasesLib.t('infocenter', 'zgvNichtErfuellt'));
								break;
							case 'accepted' :
								$('#zgvAkzeptieren_' + prestudent_id).attr('disabled', true);
								$('#zgvStatusText_' + prestudent_id).text(FHC_PhrasesLib.t('infocenter', 'zgvErfuellt'));
								break;
							case 'accepted_pruefung' :
								$('#zgvAkzeptierenPruefung_' + prestudent_id).attr('disabled', true);
								$('#zgvStatusText_' + prestudent_id).text(FHC_PhrasesLib.t('infocenter', 'zgvErfuelltPruefung'));
								break;
							case 'pruefung_stg' :
								$('#zgvRueckfragen_' + prestudent_id).attr('disabled', true);
								$('#zgvStatusText_' + prestudent_id).text(FHC_PhrasesLib.t('infocenter', 'zgvInPruefung'));
								break;
						}
					}
				},
				errorCallback: function(data, textStatus, errorThrown)
				{
					FHC_DialogLib.alertError(data);
				},
				veilTimeout: 0
			}
		);
	},

	zgvRueckfragen: function(data)
	{
		var prestudent_id = data.prestudent_id;
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/zgvRueckfragen',
			data,
			{
				successCallback: function(data, textStatus, jqXHR)
				{
					if (FHC_AjaxClient.hasData(data))
					{
						zgvUeberpruefung.checkStatus(prestudent_id);

						var response = FHC_AjaxClient.getData(data);

						if (response.hold === false)
						{
							var datum = new Date();
							datum.setDate(datum.getDate() + 14);
							var formatedDate = $.datepicker.formatDate("mm/dd/yy", datum);
							InfocenterDetails.setPersonOnHold(response.person_id, formatedDate);
						}

						InfocenterDetails._refreshLog();
						FHC_DialogLib.alertSuccess(response.msg);
					} else if(FHC_AjaxClient.isError(data))
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
				},
				errorCallback: function(jqXHR, textStatus, errorThrown)
				{
					FHC_DialogLib.alertError((jqXHR.responseText));
				}
			}
		);
	},

	zgvStatusUpdate: function(data)
	{
		var prestudent_id = data.prestudent_id;
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + '/zgvStatusUpdate',
			data,
			{
				successCallback: function(data, textStatus, jqXHR)
				{
					if (FHC_AjaxClient.hasData(data))
					{
						zgvUeberpruefung.checkStatus(prestudent_id);
						var response = FHC_AjaxClient.getData(data)

						if (response.openZgv === false)
							InfocenterDetails.removePersonOnHold(response.person_id);

						FHC_DialogLib.alertSuccess(response.msg);
					} else if (FHC_AjaxClient.isError(data))
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
				},
				errorCallback: function(jqXHR, textStatus, errorThrown)
				{
					FHC_DialogLib.alertError((jqXHR.responseText));
				}
			}
		);
	},

	checkAfterReload: function()
	{
		$('.zgvStatusText').each(function() {
			if($(this).data('info')) {
				zgvUeberpruefung.checkStatus(InfocenterDetails._getPrestudentIdFromElementId($(this).attr('id')));
			}
		});
	}
}