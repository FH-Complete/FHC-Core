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
// * Script sorgt fuer den Datenbanzugriff
// * der folgender FASonline Daten:
// *
// * - Adressen
// * - Kontakte
// * - Bankverbindungen
// ****************************************

require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/log.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');

$user = get_uid();

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$return = false;
$errormsg = 'unknown';
$data = '';
$error = false;

//Berechtigungen laden
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin','0') && !$rechte->isBerechtigt('mitarbeiter'))
{
	$return = false;
	$errormsg = 'Keine Berechtigung';
	$data = '';
	$error = true;
}

if(!$error)
{
	//in der Variable type wird die auszufuehrende Aktion mituebergeben
	if(isset($_POST['type']) && $_POST['type']=='mitarbeitersave')
	{
		//Speichert die Mitarbeiterdaten
		$mitarbeiter = new mitarbeiter($conn, null, true);
		
		if($mitarbeiter->load($_POST['uid']))
		{
			//Werte zuweisen
			$mitarbeiter->anrede = $_POST['anrede'];
			$mitarbeiter->titelpre = $_POST['titelpre'];
			$mitarbeiter->titelpost = $_POST['titelpost'];
			$mitarbeiter->vorname = $_POST['vorname'];
			$mitarbeiter->vornamen = $_POST['vornamen'];
			$mitarbeiter->nachname = $_POST['nachname'];
			$mitarbeiter->gebdatum = $_POST['geburtsdatum'];
			$mitarbeiter->gebort = $_POST['geburtsort'];
			$mitarbeiter->gebzeit = $_POST['geburtszeit'];
			$mitarbeiter->anmerkungen = $_POST['anmerkungen'];
			$mitarbeiter->homepage = $_POST['homepage'];
			$mitarbeiter->svnr = $_POST['svnr'];
			$mitarbeiter->ersatzkennzeichen = $_POST['ersatzkennzeichen'];
			$mitarbeiter->familienstand = $_POST['familienstand'];
			$mitarbeiter->geschlecht = $_POST['geschlecht'];
			$mitarbeiter->aktiv = ($_POST['aktiv']=='true'?true:false);
			$mitarbeiter->anzahlkinder = $_POST['anzahlderkinder'];
			$mitarbeiter->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
			$mitarbeiter->geburtsnation = $_POST['geburtsnation'];
			$mitarbeiter->sprache = $_POST['sprache'];
			$mitarbeiter->kurzbz = $_POST['kurzbezeichnung'];
			$mitarbeiter->stundensatz = $_POST['stundensatz'];
			$mitarbeiter->telefonklappe = $_POST['telefonklappe'];
			$mitarbeiter->lektor = ($_POST['lektor']=='true'?true:false);
			$mitarbeiter->fixangestellt = ($_POST['fixangestellt']=='true'?true:false);
			$mitarbeiter->ausbildungcode = $_POST['ausbildung'];
			$mitarbeiter->anmerkung = $_POST['anmerkung'];
			$mitarbeiter->ort_kurzbz = $_POST['ort_kurzbz'];
			$mitarbeiter->standort_kurzbz = $_POST['standort_kurzbz'];
			$mitarbeiter->alias = $_POST['alias'];
			
			if($mitarbeiter->save())
			{
				$return = true;
			}
			else 
			{
				$errormsg = $mitarbeiter->errormsg;
				$return = false;
			}
		}
		else 
		{
			$errormsg = $mitarbeiter->errormsg;
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

//RDF mit den Returnwerden ausgeben
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:DBDML="http://www.technikum-wien.at/dbdml/rdf#"
>
  <RDF:Seq RDF:about="http://www.technikum-wien.at/dbdml/msg">
	<RDF:li>
    	<RDF:Description RDF:about="http://www.technikum-wien.at/dbdml/0" >
    		<DBDML:return>'.($return?'true':'false').'</DBDML:return>
        	<DBDML:errormsg><![CDATA['.$errormsg.']]></DBDML:errormsg>
        	<DBDML:data><![CDATA['.$data.']]></DBDML:data>
        </RDF:Description>
	</RDF:li>
  </RDF:Seq>
</RDF:RDF>
';
?>