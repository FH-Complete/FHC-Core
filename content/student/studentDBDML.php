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
// * der Studenten
// *
// * Script sorgt fuer den Datenbanzugriff
// * fuer das XUL - Studenten-Modul
// *
// ****************************************

require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/log.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');

$user = get_uid();

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
	
	if(isset($_POST['type']) && $_POST['type']=='savestudent')
	{
		//Studentendaten Speichern

		if(!$error)
		{
			$student = new student($conn, null, true);
			
			if(!$student->load($_POST['uid']))
			{
				$return = false;
				$errormsg = 'Fehler beim laden:'.$student->errormsg;
				$error = true;
			}
			
			if(!$error)
			{
				$student->uid = $_POST['uid'];
				$student->anrede = $_POST['anrede'];
				$student->titelpre = $_POST['titelpre'];
				$student->titelpost = $_POST['titelpost'];
				$student->vorname = $_POST['vorname'];
				$student->vornamen = $_POST['vornamen'];
				$student->nachname = $_POST['nachname'];
				$student->gebdatum = $_POST['geburtsdatum'];
				$student->gebort = $_POST['geburtsort'];
				$student->gebzeit = $_POST['geburtszeit'];
				$student->anmerkung = $_POST['anmerkung'];
				$student->homepage = $_POST['homepage'];
				$student->svnr = $_POST['svnr'];
				$student->ersatzkennzeichen = $_POST['ersatzkennzeichen'];
				$student->familienstand = $_POST['familienstand'];
				$student->geschlecht = $_POST['geschlecht'];
				$student->aktiv = ($_POST['aktiv']=='true'?true:false);
				$student->anzahlderkinder = $_POST['anzahlderkinder'];
				$student->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
				$student->geburtsnation = $_POST['geburtsnation'];
				$student->sprache = $_POST['sprache'];
				$student->matrikelnr = $_POST['matrikelnummer'];
				$student->studiengang_kz = $_POST['studiengang_kz'];
				$student->semester = $_POST['semester'];
				$student->verband = $_POST['verband'];
				$student->gruppe = $_POST['gruppe'];
				
				$student->new=false;				

				if(!$error)
				{
					if($student->save())
					{
						$return = true;
						$error=false;
						$data = $student->uid;
					}
					else
					{
						$return = false;
						$errormsg  = $student->errormsg;
						$error = true;
					}
				}
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
