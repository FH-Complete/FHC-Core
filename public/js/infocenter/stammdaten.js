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
			var id = $(this).data('value');
			adresse.push({
				id: id,
				value: {
					'strasse': $('#input_strasse_' + id).val(),
					'plz': $('#input_plz_' + id).val(),
					'ort': $('#input_ort_' + id).val(),
					'nation': $('#nation_' + id).val(),
				}
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
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.isSuccess(data))
					{
						FHC_DialogLib.alertSuccess("Done!");
						Stammdaten._showKontakt();
						Stammdaten._showAdresse();
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

	_showKontakt: function()
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
	},

	_showAdresse: function()
	{
		$('.adresse').each(function() {
			var adressenID = $(this).data('value');
			$(this).children('input').each(function() {
				$(this).attr('id');
				var div = $(this).attr('id').replace('input_', '');
				$('#' + div).html($(this).val())
				$('#' + div).show();
				$(this).remove();
			});
		});

	},

	_hide: function()
	{
		var stammdatenform = $('.stammdaten_form');
		stammdatenform.find('select').attr('disabled', true);

		$('.stammdaten').each(function(){
			var id = $(this).attr('id');
			var div = $('<div />');
			div.attr('id', id);
			div.addClass('stammdaten');
			div.html($(this).val());
			$(this).parent('td').html(div);
		});

		$('.kontakt_input').each(function(){
			$(this).parent('td').children('span').show();
			$(this).remove();
		});

		$('.adresse_input').each(function(){
			$(this).parent('div').children('div').show();
			$(this).remove();
		});

		$('.editActionStammdaten').hide();
		$('.editStammdaten').show();
	},

	_show: function()
	{
		$('.stammdaten').each(function() {
			var id = $(this).attr('id');
			var input = $('<input />');
			input.attr('id', id);
			input.addClass('form-control stammdaten');
			input.val($(this).html());
			$(this).parent('td').html(input);
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
			var adressenID = $(this).data('value');
			$($(this).children('div').get().reverse()).each(function() {
				$(this).hide();
				var id = $(this).attr('id');

				var input = $('<input />');
				var value = $(this).html();

				input.attr('id', 'input_' + Stammdaten._getPlaceholder(id) + "_" + adressenID);
				input.attr('value', value);
				input.attr('placeholder', Stammdaten._getPlaceholder(id).toUpperCase());
				input.addClass('form-control adresse_input');
				input.val(value);
				$(this).parent().prepend(input);
			});
		});

		var stammdatenform = $('.stammdaten_form');

		stammdatenform.find('select').attr('disabled', false);
		$('.editActionStammdaten').show();
		$('.editStammdaten').hide();
	},

	_getPlaceholder(elementid)
	{
		return elementid.substr(0, elementid.indexOf("_"));
	}
}