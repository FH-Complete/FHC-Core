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
		$('.kontakt_input').each(function(){
			kontakt.push({
				id: $(this).data('id'),
				value: $(this).val()
			});
		});

		var adresse = [];
		$('.adresse').each(function(){
			var id = $(this).data('id');
			adresse.push({
				id: id,
				value: {
					'strasse': $('#strasse_' + id).val(),
					'plz': $('#plz_' + id).val(),
					'ort': $('#ort_' + id).val(),
					'nation': $('#nation_' + id).val(),
				}
			});
		});

		var data = {
			"personid" : personid,
			"titelpre" : $('#titelpre_input').val(),
			"vorname" : $('#vorname_input').val(),
			"nachname" : $('#nachname_input').val(),
			"titelpost" : $('#titelpost_input').val(),
			"gebdatum" : $('#gebdatum_input').val(),
			"svnr" : $('#svnr_input').val(),
			"buergerschaft" : $('#buergerschaft').val(),
			"geschlecht" : $('#geschlecht').val(),
			"gebnation" : $('#gebnation').val(),
			"gebort" : $('#gebort_input').val(),
			"kontakt" : kontakt,
			"adresse" : adresse,
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
				successCallback: function(response, textStatus, jqXHR) {
					if (FHC_AjaxClient.isSuccess(response))
					{
						FHC_DialogLib.alertSuccess("Done!");
						Stammdaten._updated();
						PersonCheck.update(data)
					}
					else
					{
						FHC_DialogLib.alertError(FHC_AjaxClient.getError(response));
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
		$('.stammdaten_input').each(function(){
			$(this).parent('td').children('div').show();
			$(this).remove();
		});

		$('.kontakt_input').each(function(){
			$(this).parent('td').children('span').show();
			$(this).remove();
		});

		$('.adresse_input').each(function(){
			$(this).parent('div').children('div').show();
			$(this).remove();
		});

		Stammdaten._setReadOnly(true);
	},

	_show: function()
	{
		$('.stammdaten').each(function() {
			var id = $(this).attr('id');
			var input = $('<input />');
			input.attr('id', id + '_input');
			input.addClass('form-control stammdaten_input');
			input.val($(this).html());
			$(this).hide();
			$(this).parent('td').append(input);
		});

		$('.kontakt').each(function() {
			var id = $(this).data('id');
			var value = $(this).data('value');

			$(this).hide();

			var input = $('<input />');
			input.attr('data-id', id);
			input.attr('value', value);
			input.addClass('form-control kontakt_input');
			input.val(value);
			$(this).parent('td').append(input);
		});

		$('.adresse').each(function() {
			var adressenID = $(this).data('id');
			$($(this).children('div').get().reverse()).each(function() {
				$(this).hide();
				var type = $(this).data('type');
				var value = $(this).data('value');
				var input = $('<input />');

				input.attr('data-type', type);
				input.attr('id', type + '_' + adressenID);
				input.attr('value', value);
				input.attr('placeholder', type.toUpperCase());
				input.addClass('form-control adresse_input');
				input.val(value);
				$(this).parent().prepend(input);
			});
		});

		Stammdaten._setReadOnly(false);
	},

	_updated: function()
	{

		$('.kontakt_input').each(function() {
			var span = $(this).parent('td').children('span');
			var value = $(this).val();

			var oldSpanValue = span.data('value');
			span.data('value', value);
			var newhtml = span.html().replace(oldSpanValue, value);
			span.html(newhtml);
			if (span.hasClass('email'))
				span.find('a').attr('href', 'mailto:' + value);

			span.show();
			$(this).remove();
		});

		$('.adresse').each(function() {
			$(this).children('input').each(function() {
				var value = $(this).val();
				var type = $(this).data('type');
				var div = $('div[data-type="' + type + '"]');
				div.data('value', value);
				div.html(value);
				div.show();
				$(this).remove();
			});
		});

		$('.stammdaten_input').each(function() {
			var div = $(this).parent('td').children('div');
			var value = $(this).val();
			div.html(value);
			div.show();
			$(this).remove();
		});

		Stammdaten._setReadOnly(true);
	},

	_setReadOnly: function(readonly)
	{
		var stammdatenform = $('.stammdaten_form');

		stammdatenform.find('select').attr('disabled', readonly);

		if (readonly === true)
		{
			$('.editActionStammdaten').hide();
			$('.editStammdaten').show();
		}
		else
		{
			$('.editActionStammdaten').show();
			$('.editStammdaten').hide();
		}

	}
}