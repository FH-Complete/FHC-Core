$(document).ready(function ()
{
	var personid = $("#hiddenpersonid").val();

	$('.editStammdaten').click(function()
	{
		Stammdaten._show();
	});

	$('.cancelStammdaten').click(function()
	{
		Stammdaten._hide();
	});

	$('.saveStammdaten').click(function()
	{
		var kontakt = [];
		$('.kontakt_nummer').each(function(){
			kontakt.push({
				id: $(this).data('value'),
				value: $(this).val()
			});
		});

		var data = {
			"personid" : personid,
			"titelpre" : $('#titelpre').val(),
			"vorname" : $('#vorname').val(),
			"nachname" : $('#nachname').val(),
			"titelpost" : $('#titelpost').val(),
			"gebdatum" : $('#gebdatum').val(),
			"svnr" : $('#svnr').val(),
			"buergerschaft" : $('#buergerschaft').val(),
			"geschlecht" : $('#geschlecht').val(),
			"gebnation" : $('#gebnation').val(),
			"gebort" : $('#gebort').val(),
			"kontakt" : kontakt

		};
		Stammdaten.update(personid, data);
	});
});

var Stammdaten = {
	update: function(personid, data)
	{
		FHC_AjaxClient.ajaxCallPost(
			CALLED_PATH + "/updateStammdaten/",
			data,
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isSuccess(data))
					{
						FHC_DialogLib.alertSuccess("Done!");
						Stammdaten._hide();
					}
					else
					{
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(data));
					}
				},
				errorCallback: function() {
					FHC_DialogLib.alertWarning("Fehler beim Speichern!");
				}
			}
		);
	},

	_hide: function()
	{
		$('.stammdaten_form').find('input, select').attr('readonly', true);

		$('.editActionStammdaten').hide();
		$('.editStammdaten').show();
	},

	_show: function()
	{
		$('.stammdaten_form').find('input, select').attr('readonly', false);

		$('.editActionStammdaten').show();
		$('.editStammdaten').hide();
	}
}