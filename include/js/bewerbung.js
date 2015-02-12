function checkNotEmpty(ids)
{
	var errors = [];

	for(var i in ids) {

		var input = $('#' + ids[i]);

		if(!$.trim(input.val())) {
			errors.push(ids[i]);
			input.closest('div.form-group').removeClass('has-success').addClass('has-error');
		} else {
			input.closest('div.form-group').removeClass('has-error').addClass('has-success');
		}
	}

	return errors;
}

function checkKontakt()
{
	var errors;

	errors = checkNotEmpty([
			'telefonnummer',
			'email',
			'strasse',
			'plz',
			'ort'
		]);

	if(errors.length) {
		return false;
	}

	return true;
}

function checkPerson()
{
	var errors;

	errors = checkNotEmpty([
		'nachname',
		'vorname',
		'staatsbuergerschaft'
	]);

	if ($("#gebdatum").val() !== '')
	{
		var patt1 = new RegExp("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})");
		if (!patt1.test($("#gebdatum").val()))
		{
			$('#gebdatum').closest('div.form-group').removeClass('has-success').addClass('has-error');
			errors.push('gebdatum');
		}
		else
		{
			$('#gebdatum').closest('div.form-group').removeClass('has-error').addClass('has-success');
		}
	}

	// Berechnung der Sozialversicherungsnummer wenn AT
	if ($("#staatsbuergerschaft").val() === 'A')
	{
		var soz_nr = $.trim($("#svnr").val());

		if (!/^\d{10}$/.test(soz_nr))
		{
			$('#svnr').closest('div.form-group').removeClass('has-success').addClass('has-error');
			errors.push('svnr');
		}

		var checksum = 0;

		checksum = (3 * soz_nr[0]) + (7 * soz_nr[1]) + (9 * soz_nr[2]) + (5 * soz_nr[4]) + (8 * soz_nr[5]) + (4 * soz_nr[6]) + (2 * soz_nr[7]) + (1 * soz_nr[8]) + (6 * soz_nr[9]);
		checksum = checksum % 11;

		if (checksum !== parseInt(soz_nr[3], 10))
		{
			$('#svnr').closest('div.form-group').removeClass('has-success').addClass('has-error');
			errors.push('svnr');
		}
		else
		{
			$('#svnr').closest('div.form-group').removeClass('has-error').addClass('has-success');
		}
	}

	if(errors.length) {
		return false;
	}

	return true;
}

function FensterOeffnen(adresse)
{
	MeinFenster = window.open(adresse, "Info", "width=700,height=200");
	MeinFenster.focus();
}

function toggleDiv(div)
{
	$('#'+div).toggle();
}

$(function() {

	if(activeTab) {
		$('#bewerber-navigation a[href="#' + activeTab + '"]').tab('show');
	}

	$('.btn-nav').on('click', function() {
		var tabname = $(this).attr('data-jump-tab');
		$('#bewerber-navigation a[href="#' + tabname + '"]').tab('show');
	});

	$('#bewerber-navigation a').on('click', function() {
		$(this).closest('.collapse').collapse('hide');
	});
});
