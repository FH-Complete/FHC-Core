<?php
// *****************************
// Create/Update/Delete
// der Lehrfachverteilung
// *****************************

include('../vilesci/config.inc.php');
include_once('../include/lfvt.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$lvaDAO=new lfvt($conn);

if ($_GET['do']=='create' || ($_GET['do']=='update')) {

	$lvaDAO->lvnr=$_GET['lvnr'];
	$lvaDAO->unr=$_GET['unr'];
	$lvaDAO->einheit_kurzbz=$_GET['einheit_kurzbz'];
	$lvaDAO->lektor=$_GET['lektor'];
	$lvaDAO->lehrfach_nr=$_GET['lehrfach_nr'];
	$lvaDAO->studiengang_kz=$_GET['studiengang_kz'];
	$lvaDAO->fachbereich_id=$_GET['fachbereich_id'];
	$lvaDAO->semester=$_GET['semester'];
	$lvaDAO->verband=$_GET['verband'];
	$lvaDAO->gruppe=$_GET['gruppe'];
	$lvaDAO->raumtyp=$_GET['raumtyp'];
	if (isset($_GET['raumtypalternativ'])) $lvaDAO->raumtypalternativ=$_GET['raumtypalternativ'];
	if (isset($_GET['semesterstunden'])) $lvaDAO->semesterstunden=$_GET['semesterstunden'];
	if (isset($_GET['stundenblockung'])) $lvaDAO->stundenblockung=$_GET['stundenblockung'];
	if (isset($_GET['wochenrythmus'])) $lvaDAO->wochenrythmus=$_GET['wochenrythmus'];
	if (isset($_GET['start_kw'])) $lvaDAO->start_kw=$_GET['start_kw'];
	if (isset($_GET['anmerkung'])) $lvaDAO->anmerkung=$_GET['anmerkung'];
	if (isset($_GET['studiensemester_kurzbz'])) $lvaDAO->studiensemester_kurzbz=$_GET['studiensemester_kurzbz'];
	if (isset($_GET['lehrform'])) $lvaDAO->lehrform=$_GET['lehrform'];

	if ($_GET['do']=='create') {
		// LVA neu anlegen
		$lvaDAO->new=true;
		if ($lvaDAO->save()) echo 'ok';
		else echo $lvaDAO->errormsg;
		//echo "NEU";

	} else if ($_GET['do']=='update') {
		// LVA aktualisieren
		$lvaDAO->lehrveranstaltung_id=$_GET['lehrveranstaltung_id'];
		$lvaDAO->new=false;
		if ($lvaDAO->save()) echo 'ok';
		else echo $lvaDAO->errormsg;
	}

} else if ($_GET['do']=='delete') {
	// LVA löschen
	$lvaDAO->new=false;
	$lvaDAO->lehrveranstaltung_id=$_GET['lehrveranstaltung_id'];
	if ($lvaDAO->lehrveranstaltung_id>0) {
		$lvaDAO->delete();
	}
	if ($lvaDAO->save()) echo 'ok';
	else echo $lvaDAO->errormsg;
}




?>
