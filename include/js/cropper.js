// ****
// * Liefert einen Timestamp in Sekunden
// * zum anhaengen an eine URL um Caching zu verhindern
// ****
function gettimestamp()
{
	var now = new Date();
	var ret = now.getHours()*60*60*60;
	ret = ret + now.getMinutes()*60*60;
	ret = ret + now.getSeconds()*60;
	ret = ret + now.getMilliseconds();
	return ret;
}
/**
 * schließt das fenster
 */
function closeWindow() {
	window.close();
}

$('document').ready(function() {

	$('#saveimgbutton').click(function() {
		//src und person_id von hidden input feldern
		var img = document.getElementById('croppingdiv').getElementsByTagName('img')[0];
		var src = (img.src).substring(22, (img.src).length);
		var person_id = document.getElementById('person_id');
		var person_idValue = person_id.getAttribute('value');

		//in crop.php wird das bild verarbeitet und abgespeichert
		$.post('crop.php', {src:src, person_idValue:person_idValue}, function() {});

		//cis seite auf zwei verschiedenen arten neu laden, damit das bild auch sicher nicht im cache abgelegt wird
		window.opener.location.reload(true);
		var locat=window.opener.location.href+'?ts='+gettimestamp();
		window.opener.location.href = locat;
		//warten bevor das fenster geschlossen wird, weil chrome und opera sonst probleme haben das bild zu speichern
		setTimeout(closeWindow, 100);
	});
});