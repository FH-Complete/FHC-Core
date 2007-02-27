<?php
// *****************************
// Create/Update/Delete
// der Lehrfachverteilung
// *****************************

require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/benutzerberechtigung.class.php');

$user = get_uid();

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin'))
	die('Keine Berechtigung');
	
$leDAO=new lehreinheit($conn);

if ($_GET['do']=='create' || ($_GET['do']=='update')) 
{	
	if($_GET['do']=='update')
		if(!$leDAO->load($_GET['lehreinheit_id']))
			die('Fehler beim laden');

	$leDAO->lehrveranstaltung_id=$_GET['lehrveranstaltung'];
	$leDAO->studiensemester_kurzbz=$_GET['studiensemester_kurzbz'];
	$leDAO->lehrfach_id=$_GET['lehrfach_id'];
	$leDAO->lehrform_kurzbz=$_GET['lehrform'];
	$leDAO->stundenblockung=$_GET['stundenblockung'];
	$leDAO->wochenrythmus=$_GET['wochenrythmus'];
	if (isset($_GET['start_kw'])) $leDAO->start_kw=$_GET['start_kw'];
	$leDAO->raumtyp=$_GET['raumtyp'];
	$leDAO->raumtypalternativ=$_GET['raumtypalternativ'];
	$leDAO->sprache=$_GET['sprache'];
	if (isset($_GET['lehre'])) $leDAO->lehre=($_GET['lehre']=='true'?true:false);
	if (isset($_GET['anmerkung'])) $leDAO->anmerkung=$_GET['anmerkung'];
	if (isset($_GET['lvnr'])) $leDAO->lvnr=$_GET['lvnr'];
	if (isset($_GET['unr'])) $leDAO->unr=$_GET['unr'];
	$leDAO->updateamum=date('Y-m-d H:i:s');
	$leDAO->updatevon=$user;
	
	if ($_GET['do']=='create') 
	{
		// LE neu anlegen
		$leDAO->new=true;
		$leDAO->insertamum=date('Y-m-d H:i:s');
		$leDAO->insertvon=$user;
		if ($leDAO->save()) 
			echo 'ok';
		else 
			echo $leDAO->errormsg;
	} 
	else if ($_GET['do']=='update') 
	{
		// LE aktualisieren
		$leDAO->new=false;
		if ($leDAO->save()) 
			echo 'ok';
		else 
			echo $leDAO->errormsg;
	}

} 
else if ($_GET['do']=='delete') 
{	
	// LE loeschen

	if ($leDAO->delete($_GET['lehreinheit_id']))
		echo 'ok';
	else 
		echo $leDAO->errormsg;	
}
?>
