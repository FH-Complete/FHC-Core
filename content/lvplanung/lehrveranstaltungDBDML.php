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
// * Insert/Update/Delete
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

require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/lehreinheit.class.php');
require_once('../../include/lehreinheitmitarbeiter.class.php');
require_once('../../include/lehreinheitgruppe.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/log.class.php');

$user = get_uid();

error_reporting(0);

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$return = false;
$errormsg = 'unknown';
$data = '';
$error = false;

//Berechtigungen laden
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin'))
{
	$return = false;
	$errormsg = 'Keine Berechtigung';
	$data = '';
	$error = true;
}
	
if(!$error)
{
	
	if(isset($_POST['type']) && $_POST['type']=='lehreinheit_mitarbeiter_save')
	{
		//Lehreinheitmitarbeiter Zuteilung

		if(!$error)
		{
			$lem = new lehreinheitmitarbeiter($conn, null, null, true);
			
			if(!$lem->load($_POST['lehreinheit_id'],$_POST['mitarbeiter_uid_old']))
			{
				$return = false;
				$errormsg = 'Fehler beim laden:'.$lem->errormsg;
				$error = true;
			}

			if(!$error)
			{
				$lem->lehreinheit_id = $_POST['lehreinheit_id'];
				$lem->lehrfunktion_kurzbz = $_POST['lehrfunktion_kurzbz'];
				$lem->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
				$lem->mitarbeiter_uid_old = $_POST['mitarbeiter_uid_old'];
				$lem->semesterstunden = $_POST['semesterstunden'];
				$lem->planstunden = $_POST['planstunden'];
				$lem->stundensatz = $_POST['stundensatz'];
				$lem->faktor = $_POST['faktor'];
				$lem->anmerkung = $_POST['anmerkung'];
				$lem->bismelden = ($_POST['bismelden']=='true'?true:false);
				$lem->updateamum = date('Y-m-d H:i:s');
				$lem->updatevon = $user;
			
				$lem->new=false;
				

				if(!$error)
				{
					if($lem->save())
					{
						$return = true;
						$error=false;
					}
					else
					{
						$return = false;
						$errormsg  = $lem->errormsg;
						$error = true;
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_mitarbeiter_add')
	{
		//neue Lehreinheitmitarbeiterzuteilung anlegen
		
		if(isset($_POST['lehreinheit_id']) && isset($_POST['mitarbeiter_uid']))
		{
			$lem = new lehreinheitmitarbeiter($conn, null, null, true);
			
			$lem->lehreinheit_id = $_POST['lehreinheit_id'];
			$lem->lehrfunktion_kurzbz = 'lektor';
			$lem->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
			
			$lem->semesterstunden = '0';
			$lem->planstunden = '0';
			$lem->anmerkung = '';
			$lem->bismelden = true;
			$lem->updateamum = date('Y-m-d H:i:s');
			$lem->updatevon = $user;
			$lem->insertamum = date('Y-m-d H:i:s');
			$lem->insertvon = $user;
			$lem->new=true;
			
			//Stundensatz aus tbl_mitarbeiter holen
			$qry = "SELECT stundensatz FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='".addslashes($_POST['mitarbeiter_uid'])."'";
			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					if($row->stundensatz!='')
						$lem->stundensatz = $row->stundensatz;
					else 
						$lem->stundensatz = '0';
				}
				else 
				{
					$error=true;
					$return=false;
					$errormsg='Mitarbeiter '.addslashes($_POST['mitarbeiter_uid']).' wurde nicht gefunden';
				}
			}
			else 
			{
				$error=true;
				$return=false;
				$errormsg='Fehler bei einer Datenbankabfrage:'.pg_errormessage($conn);
			}

			//Faktor aus tbl_lehrveranstaltung holen
			$qry = "SELECT planfaktor FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id) WHERE lehreinheit_id='".$_POST['lehreinheit_id']."';";
			if($result = pg_query($conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					if($row->planfaktor!='')
						$lem->faktor = $row->planfaktor;
					else 
						$lem->faktor = '0';
				}
				else 
				{
					$error = true;
					$return = false;
					$errormsg = 'Lehrveranstaltung wurde nicht gefunden';
				}
			}
			else 
			{
				$error = true;
				$return = false;
				$errormsg = 'Fehler in einer Datenbankabfrage:'.pg_errormessage($conn);
			}
			
			if(!$error)
			{
				if($lem->save())
				{
					$return = true;
					$error = false;
				}
				else
				{
					$return = false;
					$errormsg = $lem->errormsg;
					$error = true;
				}
			}
		}
		else 
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_mitarbeiter_del')
	{
		//Lehreinheitmitarbeiterzuteilung loeschen
		if(isset($_POST['lehreinheit_id']) && is_numeric($_POST['lehreinheit_id']) && isset($_POST['mitarbeiter_uid']))
		{
			$leg = new lehreinheitmitarbeiter($conn);
			if($leg->delete($_POST['lehreinheit_id'], $_POST['mitarbeiter_uid']))
			{
				$return = true;
			}
			else
			{
				$return = false;
				$errormsg = $leg->errormsg;
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehler beim loeschen der Zuordnung';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_gruppe_del')
	{
		//Lehreinheitgruppezuteilung loeschen
		if(isset($_POST['lehreinheitgruppe_id']) && is_numeric($_POST['lehreinheitgruppe_id']))
		{
			$leg = new lehreinheitgruppe($conn);
			if($leg->delete($_POST['lehreinheitgruppe_id']))
			{
				$return = true;
			}
			else
			{
				$return = false;
				$errormsg = $leg->errormsg;
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehler beim loeschen der Zuordnung';
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
			$leg->insertamum = date('Y-m-d H:i:s');
			$leg->insertvon = $user;

			if($leg->save(true))
			{
				$return = true;
			}
			else
			{
				$return = false;
				$errormsg = $leg->errormsg;
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Bitte zuerst eine Lehreinheit auswaehlen';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit')
	{
		//Lehreinheit anlegen/aktualisieren
		$leDAO=new lehreinheit($conn, null, true);
		if ($_POST['do']=='create' || ($_POST['do']=='update'))
		{
			if($_POST['do']=='update')
			{
				if(!$leDAO->load($_POST['lehreinheit_id']))
				{
					$return = false;
					$error = true;
					$errormsg = 'Fehler beim laden der lehreinheit';
				}
			}

			if(!$error)
			{
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
				$leDAO->lvnr=(isset($_POST['lvnr'])?$_POST['lvnr']:'');
				$leDAO->unr=(isset($_POST['unr'])?$_POST['unr']:'');
				$leDAO->updateamum=date('Y-m-d H:i:s');
				$leDAO->updatevon=$user;

				if ($_POST['do']=='create')
				{
					// LE neu anlegen
					$leDAO->new=true;
					$leDAO->insertamum=date('Y-m-d H:i:s');
					$leDAO->insertvon=$user;
				}
				else if ($_POST['do']=='update')
				{
					// LE aktualisieren
					$leDAO->new=false;
				}
				if ($leDAO->save())
				{
					$data = $leDAO->lehreinheit_id;
					$return = true;
				}
				else
				{
					$return = false;
					$errormsg = $leDAO->errormsg;
				}
			}
		}
		else if ($_POST['do']=='delete') //Lehreinheit loeschen
		{
			// LE loeschen
			if ($leDAO->delete($_POST['lehreinheit_id']))
			{
				$return = true;
			}
			else
			{
				$return = false;
				$errormsg = $leDAO->errormsg;
			}
		}
	}
	else
	{
		$return = false;
		$errormsg = 'Unkown type: '.$_POST['type'];
		$data = '';
	}
}
?>
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:DBDML="http://www.technikum-wien.at/dbdml/rdf#"
>
  <RDF:Seq RDF:about="http://www.technikum-wien.at/dbdml/msg">
	<RDF:li>
    	<RDF:Description RDF:about="http://www.technikum-wien.at/dbdml/0" >
    		<DBDML:return><?php echo ($return?'true':'false'); ?></DBDML:return>
        	<DBDML:errormsg><![CDATA[<?php echo $errormsg; ?>]]></DBDML:errormsg>
        	<DBDML:data><![CDATA[<?php echo $data ?>]]></DBDML:data>
        </RDF:Description>
	</RDF:li>
  </RDF:Seq>
</RDF:RDF>
