<?php
// *****************************
// Create/Update/Delete
// der Lehrfachverteilung
// *****************************

require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/lehreinheitmitarbeiter.class.php');
require_once('../include/lehreinheitgruppe.class.php');
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

if(isset($_POST['type']) && $_POST['type']=='lehreinheit_mitarbeiter_add')
{
	
	if (!isset($_POST['do']))
		die('Fehlerhafte Parameteruebergabe');
			
	$lem = new lehreinheitmitarbeiter($conn);
	
	if($_POST['do']=='update')
		if(!$lem->load($_POST['lehreinheit_id'],$_POST['mitarbeiter_uid']))
			die('Fehler beim laden:'.$lem->errormsg);
	
	$lem->lehreinheit_id = $_POST['lehreinheit_id'];
	$lem->lehrfunktion_kurzbz = $_POST['lehrfunktion_kurzbz'];
	$lem->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
	$lem->semesterstunden = $_POST['semesterstunden'];
	$lem->planstunden = $_POST['planstunden'];
	$lem->stundensatz = $_POST['stundensatz'];
	$lem->faktor = $_POST['faktor'];
	$lem->anmerkung = $_POST['anmerkung'];
	$lem->bismelden = $_POST['bismelden'];
	$lem->updateamum = date('Y-m-d H:i:s');
	$lem->updatevon = $user;
	
	if($_POST['do']=='update')
	{
		$lem->new=false;
	}
	elseif($_POST['do']=='create')
	{
		$lem->new=true;
		$lem->updateamum = date('Y-m-d H:i:s');
		$lem->updatevon = $user;
		$lem->insertamum = date('Y-m-d H:i:s');
		$lem->insertvon = $user;
	}
	else 
		die('Fehlerhafte Parameteruebergabe');
	
	if($lem->save())
		echo 'ok';
	else 
		echo $lem->errormsg;		
}
elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_gruppe_del')
{
	if(isset($_POST['lehreinheitgruppe_id']) && is_numeric($_POST['lehreinheitgruppe_id']))
	{
		$leg = new lehreinheitgruppe($conn);
		if($leg->delete($_POST['lehreinheitgruppe_id']))
			echo 'ok';
		else
			echo $leg->errormsg;
	}
}
else 
{
	if ($_POST['do']=='create' || ($_POST['do']=='update')) 
	{	
		if($_POST['do']=='update')
			if(!$leDAO->load($_POST['lehreinheit_id']))
				die('Fehler beim laden');
	
		$leDAO->lehrveranstaltung_id=$_POST['lehrveranstaltung'];
		$leDAO->studiensemester_kurzbz=$_POST['studiensemester_kurzbz'];
		$leDAO->lehrfach_id=$_POST['lehrfach_id'];
		$leDAO->lehrform_kurzbz=$_POST['lehrform'];
		$leDAO->stundenblockung=$_POST['stundenblockung'];
		$leDAO->wochenrythmus=$_POST['wochenrythmus'];
		if (isset($_POST['start_kw'])) $leDAO->start_kw=$_POST['start_kw'];
		$leDAO->raumtyp=$_POST['raumtyp'];
		$leDAO->raumtypalternativ=$_POST['raumtypalternativ'];
		$leDAO->sprache=$_POST['sprache'];
		if (isset($_POST['lehre'])) $leDAO->lehre=($_POST['lehre']=='true'?true:false);
		if (isset($_POST['anmerkung'])) $leDAO->anmerkung=$_POST['anmerkung'];
		if (isset($_POST['lvnr'])) $leDAO->lvnr=$_POST['lvnr'];
		if (isset($_POST['unr'])) $leDAO->unr=$_POST['unr'];
		$leDAO->updateamum=date('Y-m-d H:i:s');
		$leDAO->updatevon=$user;
		
		if ($_POST['do']=='create') 
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
		else if ($_POST['do']=='update') 
		{
			// LE aktualisieren
			$leDAO->new=false;
			if ($leDAO->save()) 
				echo 'ok';
			else 
				echo $leDAO->errormsg;
		}
	
	} 
	else if ($_POST['do']=='delete') 
	{	
		// LE loeschen
	
		if ($leDAO->delete($_POST['lehreinheit_id']))
			echo 'ok';
		else 
			echo $leDAO->errormsg;	
	}
}
?>
