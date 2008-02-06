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

require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/log.class.php');
require_once('../include/adresse.class.php');
require_once('../include/kontakt.class.php');
require_once('../include/bankverbindung.class.php');
require_once('../include/variable.class.php');
require_once('../include/benutzerfunktion.class.php');
require_once('../include/studiensemester.class.php');

$user = get_uid();
//header("Content-type: application/xhtml+xml");
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
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('mitarbeiter') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('lv-plan'))
{
	$return = false;
	$errormsg = 'Keine Berechtigung';
	$data = '';
	$error = true;
}

if(!$error)
{
	//in der Variable type wird die auszufuehrende Aktion mituebergeben
	if(isset($_POST['type']) && $_POST['type']=='adressesave') // ***** ADRESSEN ***** //
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') && 
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') && 
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
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
			
			//Wenn die Nation Oesterreich ist, dann muss die Gemeinde in der Tabelle Gemeinde vorkommen
			if($_POST['nation']=='A')
			{
				$qry = "SELECT * FROM bis.tbl_gemeinde WHERE lower(name)=lower('".addslashes($_POST['gemeinde'])."') AND plz='".addslashes($_POST['plz'])."'";
				if($result = pg_query($conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$adresse->gemeinde = $row->name;	
					}
					else
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
	}
	elseif(isset($_POST['type']) && $_POST['type']=='adressedelete')
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') && 
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') && 
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else 
		{
			//Loescht Adressen aus der DB
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
	}
	elseif(isset($_POST['type']) && $_POST['type']=='kontaktsave') // ***** KONTAKT ***** //
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') && 
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') && 
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else 
		{
			//Speichert die Kontaktdaten in die Datenbank
			$kontakt = new kontakt($conn, null, true);
	
			if($_POST['neu']=='false')
			{
				$kontakt->load($_POST['kontakt_id']);
				$kontakt->new = false;
			}
			else
			{
				$kontakt->insertamum = date('Y-m-d H:i:s');
				$kontakt->insertvon = $user;
				$kontakt->new = true;
			}
	
			$kontakt->kontakt_id = $_POST['kontakt_id'];
			$kontakt->person_id = $_POST['person_id'];
			$kontakt->anmerkung = $_POST['anmerkung'];
			$kontakt->kontakt = $_POST['kontakt'];
			$kontakt->kontakttyp = $_POST['typ'];
			$kontakt->zustellung = ($_POST['zustellung']=='true'?true:false);
			$kontakt->firma_id = $_POST['firma_id'];
			$kontakt->updateamum = date('Y-m-d H:i:s');
			$kontakt->updatevon = $user;
	
			if($kontakt->save())
			{
				$return = true;
				$data = $kontakt->kontakt_id;
			}
			else
			{
				$return = false;
				$errormsg = $kontakt->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='kontaktdelete')
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') && 
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') && 
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else 
		{
			//Loescht Kontaktdaten aus der Datenbank
			$kontakt = new kontakt($conn, null, true);
	
			if($kontakt->delete($_POST['kontakt_id']))
			{
				$return = true;
			}
			else 
			{
				$return = false;
				$errormsg = $kontakt->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='bankverbindungsave') // ***** BANKVERBINDUNG ***** //
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') && 
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') && 
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else 
		{
			//Speichert die Kontaktdaten in die Datenbank
			$bankverbindung = new bankverbindung($conn, null, true);
			
			if($_POST['neu']=='false')
			{
				$bankverbindung->load($_POST['bankverbindung_id']);
				$bankverbindung->new = false;
			}
			else 
			{
				$bankverbindung->insertamum = date('Y-m-d H:i:s');
				$bankverbindung->insertvon = $user;
				$bankverbindung->new = true;
			}
			
			$bankverbindung->bankverbindung_id = $_POST['bankverbindung_id'];
			$bankverbindung->person_id = $_POST['person_id'];
			$bankverbindung->name = $_POST['name'];
			$bankverbindung->anschrift = $_POST['anschrift'];
			$bankverbindung->bic = $_POST['bic'];
			$bankverbindung->blz = $_POST['blz'];
			$bankverbindung->iban = $_POST['iban'];
			$bankverbindung->kontonr = $_POST['kontonr'];
			$bankverbindung->typ = $_POST['typ'];
			$bankverbindung->verrechnung = ($_POST['verrechnung']=='true'?true:false);
			$bankverbindung->updateamum = date('Y-m-d H:i:s');
			$bankverbindung->updatevon = $user;
			
			if($bankverbindung->save())
			{
				$return = true;
				$data = $bankverbindung->bankverbindung_id;
			}
			else 
			{
				$return = false;
				$errormsg = $bankverbindung->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='bankverbindungdelete')
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') && 
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') && 
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{ 
			//Loescht Bankverbindungen aus der Datenbank
			$bankverbindung = new bankverbindung($conn, null, true);
			
			if($bankverbindung->delete($_POST['bankverbindung_id']))
			{
				$return = true;
			}
			else 
			{
				$return = false;
				$errormsg = $bankverbindung->errormsg;
			}
		}	
	}
	elseif(isset($_POST['type']) && $_POST['type']=='funktionsave') // ****************** BENUTZERFUNKTION **************** //
	{
		if(($_POST['studiengang_kz_berecht']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz_berecht'],'suid') && 
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz_berecht'], 'suid')) ||
		   ($_POST['studiengang_kz_berecht']=='' && !$rechte->isBerechtigt('admin', null, 'suid') && 
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{ 
			$benutzerfunktion = new benutzerfunktion($conn);
			if(isset($_POST['neu']) && $_POST['neu']=='true')
			{
				$benutzerfunktion->new = true;
				$bentuzerfunktion->insertamum=date('Y-m-d H:i:s');
				$benutzerfunktion->insertvon = $user;
			}
			else 
			{
				if(isset($_POST['benutzerfunktion_id']))
				{
					if($benutzerfunktion->load($_POST['benutzerfunktion_id']))
					{
						$benutzerfunktion->new = false;
					}
					else 
					{
						$error = true;
						$errormsg = 'Fehler beim Laden der Funktion: '.$benutzerfunktion->errormsg;
						$return = false;
					}
				}
				else 
				{
					$error = true;
					$errormsg = 'Benutzerfunktion_id wurde nicht uebergeben';
					$return = false;
				}
			}
			
			if(!$error)
			{
				$benutzerfunktion->studiengang_kz = $_POST['studiengang_kz'];
				$benutzerfunktion->fachbereich_kurzbz = $_POST['fachbereich_kurzbz'];
				$benutzerfunktion->uid = $_POST['uid'];
				$benutzerfunktion->funktion_kurzbz = $_POST['funktion_kurzbz'];
				$benutzerfunktion->updateamum = date('Y-m-d H:i:s');
				$benutzerfunktion->updatevon = $user;
				
				if($benutzerfunktion->save())
				{
					$return = true;
					$data = $benutzerfunktion->benutzerfunktion_id;
				}
				else 
				{
					$return = false;
					$errormsg = 'Fehler beim Speichern:'.$benutzerfunktion->errormsg.' "'.$_POST['fachbereich_kurzbz'].' "';
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='funktiondelete')
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') && 
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') && 
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{ 
			if(isset($_POST['benutzerfunktion_id']) && is_numeric($_POST['benutzerfunktion_id']))
			{
				$benutzerfunktion = new benutzerfunktion($conn);
				if($benutzerfunktion->delete($_POST['benutzerfunktion_id']))
				{
					$return = true;	
				}
				else 
				{
					$return = false;
					$errormsg = 'Fehler beim Loeschen:'.$benutzerfunktion->errormsg;
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='variablechange') /**********************SONSTIGES*****************/
	{
		$variable = new variable($conn, null, null, true);
		
		$variable->uid = $user;
		$variable->new = false;
		
		// Aendert die Variable Studiensemester		
		if(isset($_POST['stsem']))
		{
			if(isset($_POST['wert']) && $_POST['wert']!=0)
			{
				$stsem = new studiensemester($conn);
				$studiensemester_kurzbz = $stsem->jump($_POST['stsem'], $wert);
			}
			else 
				$studiensemester_kurzbz = $_POST['stsem'];
				
			$variable->name = 'semester_aktuell';
			$variable->wert = $studiensemester_kurzbz;
		}
		elseif(isset($_POST['kontofilterstg']))
		{
			$variable->name = 'kontofilterstg';
			$variable->wert = $_POST['kontofilterstg'];
		}
		elseif(isset($_POST['name']))
		{
			$variable->name = $_POST['name'];
			$variable->wert = $_POST['wert'];
		}
		else
		{
			$error = true;
		}
			
		if(!$error)
		{
			if($variable->save())
			{
				$return = true;
				$data = $variable->wert;
			}
			else
			{
				$return = false;
				$errormsg = $variable->errormsg;
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Falsche Paramenteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='getvariable')
	{
		$variable = new variable($conn, null, null, true);
		
		if($variable->load($user, $_POST['name']))
		{	
			$return = true;
			$data = $variable->wert;
		}
		else 
		{
			$return = false;
			$errormsg = 'Fehler: '.$variable->errormsg;
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