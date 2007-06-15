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

require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/log.class.php');
require_once('../include/adresse.class.php');

$user = get_uid();

//error_reporting(0);

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
	if(isset($_POST['type']) && $_POST['type']=='adressesave')
	{
		//Speichert die Adressdaten in die Datenbank
		$adresse = new adresse($conn, null, true);
		
		if($_POST['neu']=='false')
		{
			$adresse->load($_POST['adresse_id']);
			$adresse->new = false;
		}
		else 
		{
			$adresse->insertamum = date('Y-m-d H:i:s');
			$adresse->insertvon = $user;
			$adresse->new = true;
		}
		
		$adresse->adresse_id = $_POST['adresse_id'];
		$adresse->person_id = $_POST['person_id'];
		$adresse->name = $_POST['name'];
		$adresse->strasse = $_POST['strasse'];
		$adresse->plz = $_POST['plz'];
		$adresse->ort = $_POST['ort'];
		$adresse->gemeinde = $_POST['gemeinde'];
		$adresse->nation = $_POST['nation'];
		$adresse->typ = $_POST['typ'];
		$adresse->heimatadresse = ($_POST['heimatadresse']=='true'?true:false);
		$adresse->zustelladresse = ($_POST['zustelladresse']=='true'?true:false);
		$adresse->firma_id = $_POST['firma_id'];
		$adresse->updateamum = date('Y-m-d H:i:s');
		$adresse->updatevon = $user;
		$adresse->insertamum = date('Y-m-d H:i:s');
		$adresse->insertvon = $user;
		
		//Wenn die Nation Oesterreich ist, dann muss die Gemeinde in der Tabelle Gemeinde vorkommen
		if($_POST['nation']=='A')
		{
			$qry = "SELECT * FROM bis.tbl_gemeinde WHERE name='".addslashes($_POST['gemeinde'])."'";
			if($result = pg_query($conn, $qry))
			{
				if(pg_num_rows($result)==0)
				{
					$error = true;
					$errormsg = 'Gemeinde ist ungueltig';
					$return = false;
				}
			}
			else 
			{
				$error = true;
				$errormsg = 'Fehler beim Ermitteln der Gemeinde';
				$return = false;
			}				
		}
		
		if(!$error)
		{
			if($adresse->save())
			{
				$return = true;
				$data = $adresse->adresse_id;
			}
			else 
			{
				$return = false;
				$errormsg = $adresse->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='adressedelete')
	{
		//Speichert die Adressdaten in die Datenbank
		$adresse = new adresse($conn, null, true);
		
		if($adresse->delete($_POST['adresse_id']))
		{
			$return = true;
		}
		else 
		{
			$return = false;
			$errormsg = $adresse->errormsg;
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
