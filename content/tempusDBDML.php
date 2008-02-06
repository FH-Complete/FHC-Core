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

// *********************************************
// * Datenbankschnittstelle fuer FAS und Tempus
// *********************************************

require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/log.class.php');
require_once('../include/benutzerfunktion.class.php');
require_once('../include/stundenplan.class.php');

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
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz'))
{
	$return = false;
	$errormsg = 'Keine Berechtigung';
	$data = '';
	$error = true;
}

if(!$error)
{
	if(isset($_POST['type']) && $_POST['type']=='undo')
	{
		//UNDO Befehl ausfuehren

		if (!isset($_POST['log_id']))
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
			$data = '';
			$error = true;
		}

		if(!$error)
		{
			$log = new log($conn, null, null, true);

			if($log->undo($_POST['log_id']))
			{
				$return = true;
			}
			else
			{
				$return = false;
				$errormsg = 'Fehler bei UnDo:'.$log->errormsg;
			}
		}
	}
	elseif (isset($_POST['type']) && $_POST['type']=='addFunktionToMitarbeiter')
	{
		//Fuegt eine Lkt Funktion zu einem Studiengang/Mitarbeiter hinzu
		if(isset($_POST['uid']) && isset($_POST['studiengang_kz']))
		{
			if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') &&
			   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'],'suid'))
			{
				$return = false;
				$error = true;
				$errormsg = 'keine Berechtigung';
			}
			else
			{
				$obj = new benutzerfunktion($conn);
				$obj->uid = $_POST['uid'];
				$obj->studiengang_kz = $_POST['studiengang_kz'];
				$obj->funktion_kurzbz = 'lkt';
				$obj->updateamum = date('Y-m-d H:i:s');
				$obj->updatevon = $user;
				$obj->insertamum = date('Y-m-d H:i:s');
				$obj->insertvon = $user;
	
				if($obj->save(true))
				{
					$return = true;
				}
				else
				{
					$return = false;
					$errormsg = $obj->errormsg;
				}
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif (isset($_POST['type']) && $_POST['type']=='delFunktionFromMitarbeiter')
	{
		//Loescht eine Lektorfunktion
		if(isset($_POST['uid']) && isset($_POST['studiengang_kz']))
		{
			
			if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') &&
			   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'],'suid'))
			{
				$return = false;
				$error = true;
				$errormsg = 'keine Berechtigung';
			}
			else
			{
				$obj = new benutzerfunktion($conn);
				//Benutzerfunktion suchen
				if($obj->getBentuzerFunktion($_POST['uid'], 'lkt', $_POST['studiengang_kz']))
				{
					//Benutzerfunktion loeschen
					if($obj->delete($obj->benutzerfunktion_id))
					{
						$return = true;
					}
					else
					{
						$return = false;
						$errormsg = $obj->errormsg;
					}
				}
				else
				{
					$return = false;
					$errormsg = $obj->errormsg;
				}
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehler bei Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletestundenplaneintrag')
	{
		//Loescht einen Eintrag aus der Stundenplantabelle
		loadVariables($conn, get_uid());
		if(isset($_POST['stundenplan_id']) && is_numeric($_POST['stundenplan_id']))
		{
			$stundenplan = new stundenplan($conn, $db_stpl_table, null, true);
			if($stundenplan->delete($_POST['stundenplan_id']))
			{
				$return = true;
			}
			else 
			{
				$errormsg='Fehler beim Loeschen: '.$stundenplan->errormsg;
				$return = false;
				$data = '';
			}
		}
		else 
		{
			$return = false;
			$errormsg = 'ID ist ungueltig';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savestundenplaneintrag')
	{
		loadVariables($conn, get_uid());
		$stundenplan = new stundenplan($conn, $db_stpl_table, null, true);
		if($stundenplan->load($_POST['stundenplan_id']))
		{
			$stundenplan->unr = $_POST['unr'];
			$stundenplan->verband = $_POST['verband'];
			$stundenplan->gruppe = $_POST['gruppe'];
			$stundenplan->gruppe_kurzbz = $_POST['gruppe_kurzbz'];
			$stundenplan->ort_kurzbz = $_POST['ort_kurzbz'];
			$stundenplan->datum = $_POST['datum'];
			$stundenplan->stunde = $_POST['stunde'];
			$stundenplan->titel = $_POST['titel'];
			$stundenplan->anmerkung = $_POST['anmerkung'];
			$stundenplan->fix = ($_POST['fix']=='true'?true:false);
			$stundenplan->updateamum = date('Y-m-d H:i:s');
			$stundenplan->updatevon = get_uid();
			
			if($stundenplan->save(false))
			{
				$return = true;
			}
			else 
			{
				$return = false;
				$errormsg = 'Fehler beim Speichern der Daten:'.$stundenplan->errormsg;
			}
		}
		else 
		{
			$errormsg = 'Fehler beim Laden: '.$stundenplan->errormsg;
			$return = false;
		}
	}
	else
	{
		$return = false;
		$errormsg = 'Unkown type';
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
