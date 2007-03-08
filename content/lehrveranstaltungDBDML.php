<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

// ****************************************
// * Create/Update/Delete
// * der Lehreinheiten
// * 
// * Script sorgt fuer den Datenbanzugriff
// * fuer das XUL - Lehreinheiten-Modul
// *
// * Derzeitige Funktionen:
// * - Lehreinheitmitarbeiter Zuteilung hinzufuegen/bearbeiten/loeschen
// * - Lehreinheitgruppe Zutelung hinzufuegen/loeschen
// * - Lehreinheit anlegen/bearbeiten/loeschen
// ****************************************

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

//Berechtigungen laden
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin'))
	die('Keine Berechtigung');
	
$leDAO=new lehreinheit($conn);

if(isset($_POST['type']) && $_POST['type']=='lehreinheit_mitarbeiter_add')
{
	//Lehreinheitmitarbeiter Zuteilung
	//wenn do=update dann wird aktualisiert
	//wenn do=create wird ein neuer datensatz angelegt
	
	if (!isset($_POST['do']))
		die('Fehlerhafte Parameteruebergabe');
			
	$lem = new lehreinheitmitarbeiter($conn);
	
	if($_POST['do']=='update')
		if(!$lem->load($_POST['lehreinheit_id'],$_POST['mitarbeiter_uid_old']))
			die('Fehler beim laden:'.$lem->errormsg);
	
	$lem->lehreinheit_id = $_POST['lehreinheit_id'];
	$lem->lehrfunktion_kurzbz = $_POST['lehrfunktion_kurzbz'];
	$lem->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
	if($_POST['do']=='update')
		$lem->mitarbeiter_uid_old = $_POST['mitarbeiter_uid_old'];
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
elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_mitarbeiter_del')
{
	//Lehreinheitmitarbeiterzuteilung loeschen
	if(isset($_POST['lehreinheit_id']) && is_numeric($_POST['lehreinheit_id']) && isset($_POST['mitarbeiter_uid']))
	{
		$leg = new lehreinheitmitarbeiter($conn);
		if($leg->delete($_POST['lehreinheit_id'], $_POST['mitarbeiter_uid']))
			echo 'ok';
		else
			echo $leg->errormsg;
	}
}
elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_gruppe_del')
{
	//Lehreinheitgruppezuteilung loeschen
	if(isset($_POST['lehreinheitgruppe_id']) && is_numeric($_POST['lehreinheitgruppe_id']))
	{
		$leg = new lehreinheitgruppe($conn);
		if($leg->delete($_POST['lehreinheitgruppe_id']))
			echo 'ok';
		else
			echo $leg->errormsg;
	}
}
elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_gruppe_add')
{
	//Lehreinheitgruppezuteilung anlegen
	if(isset($_POST['lehreinheit_id']) && is_numeric($_POST['lehreinheit_id']))
	{
		$leg = new lehreinheitgruppe($conn);
		$leg->lehreinheit_id = $_POST['lehreinheit_id'];
		$leg->studiengang_kz = $_POST['studiengang_kz'];
		$leg->semester = $_POST['semester'];
		$leg->verband = $_POST['verband'];
		$leg->gruppe = $_POST['gruppe'];
		$leg->gruppe_kurzbz = $_POST['gruppe_kurzbz'];
		
		if($leg->save(true))
		{
			echo 'ok';
		}
		else 
			echo $leg->errormsg;
	}
	else 
		echo "Lehreinheit_id ist ungueltig";
}
elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit')
{
	//Lehreinheit anlegen/aktualisieren
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
	else if ($_POST['do']=='delete') //Lehreinheit loeschen
	{	
		// LE loeschen
		if ($leDAO->delete($_POST['lehreinheit_id']))
			echo 'ok';
		else 
			echo $leDAO->errormsg;	
	}
}
else 
	echo "Unkown type";
?>
