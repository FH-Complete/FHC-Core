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

require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/log.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/bisverwendung.class.php');
require_once('../../include/bisfunktion.class.php');
require_once('../../include/entwicklungsteam.class.php');
require_once('../../include/resturlaub.class.php');

$user = get_uid();

$return = false;
$errormsg = 'unknown';
$data = '';
$error = false;

//Berechtigungen laden
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin', null, 'suid') && !$rechte->isBerechtigt('mitarbeiter', null, 'suid'))
{
	$return = false;
	$errormsg = 'Sie haben keine Berechtigung zum Speichern';
	$data = '';
	$error = true;
}

if(!$error)
{
	//in der Variable type wird die auszufuehrende Aktion mituebergeben
	if(isset($_POST['type']) && $_POST['type']=='mitarbeitersave')
	{
		//Speichert die Mitarbeiterdaten
		$mitarbeiter = new mitarbeiter();
		
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
			$aktiv_alt = $mitarbeiter->bnaktiv;
			$mitarbeiter->bnaktiv = ($_POST['aktiv']=='true'?true:false);
			$mitarbeiter->anzahlkinder = $_POST['anzahlderkinder'];
			$mitarbeiter->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
			$mitarbeiter->geburtsnation = $_POST['geburtsnation'];
			$mitarbeiter->sprache = $_POST['sprache'];
			$mitarbeiter->kurzbz = $_POST['kurzbezeichnung'];
			$mitarbeiter->stundensatz = $_POST['stundensatz'];
			$mitarbeiter->telefonklappe = $_POST['telefonklappe'];
			$mitarbeiter->lektor = ($_POST['lektor']=='true'?true:false);
			$mitarbeiter->fixangestellt = ($_POST['fixangestellt']=='true'?true:false);
			$mitarbeiter->bismelden = ($_POST['bismelden']=='true'?true:false);
			$mitarbeiter->ausbildungcode = $_POST['ausbildung'];
			$mitarbeiter->anmerkung = $_POST['anmerkung'];
			$mitarbeiter->ort_kurzbz = $_POST['ort_kurzbz'];
			$mitarbeiter->standort_id = $_POST['standort_id'];
			$mitarbeiter->alias = $_POST['alias'];
			$mitarbeiter->updateamum = date('Y-m-d H:i:s');
			$mitarbeiter->updatevon = $user;
			$mitarbeiter->kleriker = ($_POST['kleriker'] == 'true'?true:false);
			if(PERSONALNUMMER_EDITABLE === true)
			{
			    $mitarbeiter->personalnummer = $_POST['personalnummer'];
			}
			
			if($mitarbeiter->save())
			{
				$resturlaub = new resturlaub();
				if($resturlaub->load($_POST['uid']))
				{
					$resturlaub->new = false;
				}
				else 
				{
					$resturlaub->new = true;
					$resturlaub->insertamum = date('Y-m-d H:i:s');
					$resturlaub->insertvon = $user;
					$resturlaub->mitarbeiter_uid = $_POST['uid'];
				}
				
				$resturlaub->updateamum = date('Y-m-d H:i:s');
				$resturlaub->updatevon = $user;
				$resturlaub->urlaubstageprojahr = $_POST['urlaubsanspruch'];
				$resturlaub->resturlaubstage = $_POST['resturlaubstage'];
				
				if($resturlaub->save())
				{
					$return = true;
				}
				else 
				{
					$return = false;
					$errormsg = 'Fehler beim Speichern der Resturlaubstage/Urlaubsanspruch'.$resturlaub->errormsg;
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
			$errormsg = $mitarbeiter->errormsg;
			$return = false;
		}	
	}
	elseif(isset($_POST['type']) && $_POST['type']=='verwendungsave')
	{
		//Speichert die BISVerwendung
		$verwendung = new bisverwendung();
		
		if($_POST['neu']!='true')
		{
			if(!$verwendung->load($_POST['bisverwendung_id']))
			{
				$error = true;
				$return = false;
				$errormsg = $verwendung->errormsg;
			}
			$verwendung->new = false;
		}
		else 
		{
			$verwendung->new = true;
			$verwendung->insertamum = date('Y-m-d H:i:s');
			$verwendung->insertvon = $user;
		}
		
		if(!$error)
		{
			$verwendung->ba1code = $_POST['ba1code'];
			$verwendung->ba2code = $_POST['ba2code'];
			$verwendung->beschausmasscode = $_POST['beschausmasscode'];
			$verwendung->verwendung_code = $_POST['verwendung_code'];
			$verwendung->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
			$verwendung->hauptberufcode = $_POST['hauptberufcode'];
			if($_POST['hauptberuflich']=='true')
				$verwendung->hauptberuflich = true;
			elseif($_POST['hauptberuflich']=='false')
				$verwendung->hauptberuflich = false;
			else 
				$verwendung->hauptberuflich = '';
			$verwendung->habilitation = ($_POST['habilitation']=='true'?true:false);
			$verwendung->beginn = $_POST['beginn'];
			$verwendung->ende = $_POST['ende'];
			$verwendung->vertragsstunden = str_replace(',','.',$_POST['vertragsstunden']);
			$verwendung->updateamum = date('Y-m-d H:i:s');
			$verwendung->updatevon = $user;				
			
			if($verwendung->save())
			{
				$return = true;
				$data = $verwendung->bisverwendung_id;
			}
			else 
			{
				$errormsg = $verwendung->errormsg;
				$return = false;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='verwendungdelete')
	{
		//Loescht die BISVerwendung
		$verwendung = new bisverwendung();
		if($verwendung->delete($_POST['bisverwendung_id']))
		{
			$return = true;
		}
		else 
		{
			$return = false;
			$errormsg = $verwendung->errormsg;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='funktionsave')
	{
		//Speichert die BISFunktion
		$funktion = new bisfunktion();
		
		if($_POST['neu']!='true')
		{
			if(!$funktion->load($_POST['bisverwendung_id'],$_POST['studiengang_kz_old']))
			{
				$error = true;
				$return = false;
				$errormsg = $funktion->errormsg;
			}
			$funktion->new = false;
		}
		else 
		{
			$funktion->new = true;
			$funktion->insertamum = date('Y-m-d H:i:s');
			$funktion->insertvon = $user;
		}
				
		if(!$error)
		{
			$funktion->bisverwendung_id = $_POST['bisverwendung_id'];
			$funktion->studiengang_kz = $_POST['studiengang_kz'];
			$funktion->studiengang_kz_old = $_POST['studiengang_kz_old'];
			$funktion->sws = str_replace(',','.',$_POST['sws']);
			$funktion->updateamum = date('Y-m-d H:i:s');
			$funktion->updatevon = $user;				
			
			if($funktion->save())
			{
				$return = true;
			}
			else 
			{
				$errormsg = $funktion->errormsg;
				$return = false;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='funktiondelete')
	{
		//Loescht die BISVerwendung
		$funktion = new bisfunktion();
		if($funktion->delete($_POST['bisverwendung_id'],$_POST['studiengang_kz']))
		{
			$return = true;
		}
		else 
		{
			$return = false;
			$errormsg = $funktion->errormsg;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='entwicklungsteamsave')
	{
		//Speichert den Entwicklungsteameintrag
		$entwt = new entwicklungsteam();
		
		if($_POST['neu']!='true')
		{
			if(!$entwt->load($_POST['mitarbeiter_uid'],$_POST['studiengang_kz_old']))
			{
				$error = true;
				$return = false;
				$errormsg = $entwt->errormsg;
			}
			$entwt->new = false;
		}
		else 
		{			

			if($entwt->exists($_POST['mitarbeiter_uid'],$_POST['studiengang_kz']))
			{
				$error = true;
				$errormsg = 'Es existiert bereits ein Eintrag fuer diesen Studiengang';
				$return = false;
			}
			$entwt->new = true;
			$entwt->insertamum = date('Y-m-d H:i:s');
			$entwt->insertvon = $user;
		}
			
		if(!$error)
		{
			$entwt->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
			$entwt->studiengang_kz = $_POST['studiengang_kz'];
			$entwt->studiengang_kz_old = $_POST['studiengang_kz_old'];
			$entwt->besqualcode = $_POST['besqualcode'];
			$entwt->beginn = $_POST['beginn'];
			$entwt->ende = $_POST['ende'];
			$entwt->updateamum = date('Y-m-d H:i:s');
			$entwt->updatevon = $user;				
			
			if($entwt->save())
			{
				$return = true;
			}
			else 
			{
				$errormsg = $entwt->errormsg;
				$return = false;
			}
		}
		
	}
	elseif(isset($_POST['type']) && $_POST['type']=='entwicklungsteamdelete')
	{
		//Loescht einen Entwicklungsteameintrag
		$entwt = new entwicklungsteam();
		if($entwt->delete($_POST['mitarbeiter_uid'],$_POST['studiengang_kz']))
		{
			$return = true;
		}
		else 
		{
			$return = false;
			$errormsg = $entwt->errormsg;
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
