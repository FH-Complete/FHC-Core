<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
/**
 * Firmenverwaltung fuer WaWi
 * 
 * Dies ist eine abgespeckte Version der Firmenverwaltung zum einfachen Anlegen und
 * Bearbeiten von Firmen.
 */
require_once('../config/wawi.config.inc.php');
require_once('auth.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/firma.class.php');
require_once('../include/standort.class.php');
require_once('../include/kontakt.class.php');
require_once('../include/adresse.class.php');
require_once('../include/nation.class.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WaWi Firma</title>	
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../include/js/jquery.css" type="text/css"/>	
	
	<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script> 

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php 
$user=get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('wawi/firma'))
	die('Sie haben keine Berechtigung für diese Seite');

$method = isset($_GET['method'])?$_GET['method']:'search';
$strasse = '';
$name = '';
$plz = '';
$ort = '';
$telefon = '';
$fax = '';
$email = '';
$nation = 'A';
$id = isset($_GET['id'])?$_GET['id']:'';

//Speichern der Daten
if(isset($_POST['save']))
{
	if(!isset($_POST['strasse']) || !isset($_POST['name']) || !isset($_POST['plz']) || !isset($_POST['ort']) ||
	   !isset($_POST['telefon']) || !isset($_POST['fax']) || !isset($_POST['email']) || !isset($_POST['anmerkung']))
		die('Ungueltige Parameteruebergabe');
		
	$error = false;
	$strasse = $_POST['strasse'];
	$name = $_POST['name'];
	$plz = $_POST['plz'];
	$ort = $_POST['ort'];
	$telefon = $_POST['telefon'];
	$fax = $_POST['fax'];
	$email = $_POST['email'];
	$nation = $_POST['nation'];
	$anmerkung = $_POST['anmerkung'];
	
	//Bei einem Update werden die IDs der Datensaetze uebergeben
	$adresse_id = $_POST['adresse_id'];
	$firma_id = $_POST['firma_id'];
	$standort_id = $_POST['standort_id'];
	$fax_id = $_POST['fax_id'];
	$telefon_id = $_POST['telefon_id'];
	$email_id = $_POST['email_id'];
	$kundennummer_erhalter_id = $_POST['kundennummer_erhalter_id'];
	$kundennummer_erhalter = $_POST['kundennummer_erhalter'];
	$kundennummer_gmbh_id = $_POST['kundennummer_gmbh_id'];
	$kundennummer_gmbh = $_POST['kundennummer_gmbh'];
	
	$errormsg='';
	
	if($email!='' && !mb_strstr($email,'@'))
	{
		$errormsg = 'Email muss ein @ enthalten';
		$error = true;
	}
	if($name=='')
	{
		$errormsg = 'Es muss ein Firmennamen eingetragen werden!';
		$error = true;
	}
	$db = new basis_db();
	
	$db->db_query('BEGIN;');
	
	if(!$error)
	{
		//Firmendatensatz anlegen/updaten
		$firma = new firma();
		
		if($firma_id!='')
		{
			if(!$firma->load($firma_id))
				die('Firma wurde nicht gefunden');
			$firma->new  = false;
			$firma->updateamum = date('Y-m-d H:i:s');
			$firma->updatevon = $user;
		}
		else
		{
			$firma->schule=false;
			$firma->gesperrt=false;
			$firma->aktiv=true;
			$firma->insertamum = date('Y-m-d H:i:s');
			$firma->insertvon = $user;
			$firma->new = true;
			$firma->firmentyp_kurzbz='Firma';
		}
		
		$firma->name=$name;
		$firma->anmerkung=$anmerkung;
		
		
		if($firma->save())
		{
			//Kundennummer Erhalter
			if($kundennummer_erhalter_id!='' || $kundennummer_erhalter!='')
			{
				$firma_oe = new firma();
				if($kundennummer_erhalter_id!='')
				{
					if(!$firma_oe->load_firmaorganisationseinheit($kundennummer_erhalter_id))
					{
						$error = true;
						$errormsg.='Fehler beim Laden der Organisationseinheitenzuordnung';
					}
					$firma_oe->new = false;
				}
				else 
				{
					$firma_oe->firma_id = $firma->firma_id;
					$firma_oe->new = true;
					$firma_oe->oe_kurzbz='gst';
					$firma_oe->insertamum = date('Y-m-d H:i:s');
					$firma_oe->insertvon = $user;
				}
				
				$firma_oe->updateamum = date('Y-m-d H:i:s');
				$firma_oe->updatevon = $user;
				$firma_oe->kundennummer=$kundennummer_erhalter;
				
				
				if(!$firma_oe->saveorganisationseinheit())
				{
					$error = true;
					$errormsg.='Fehler beim Speichern der Kundennummer:'.$firma_oe->errormsg;
				}
			}
			
			//Kundennummer GmbH
			if($kundennummer_gmbh_id!='' || $kundennummer_gmbh!='')
			{
				$firma_oe = new firma();
				if($kundennummer_gmbh_id!='')
				{
					if(!$firma_oe->load_firmaorganisationseinheit($kundennummer_gmbh_id))
					{
						$error = true;
						$errormsg.='Fehler beim Laden der Organisationseinheitenzuordnung';
					}
					$firma_oe->new = false;
				}
				else 
				{
					$firma_oe->firma_id = $firma->firma_id;
					$firma_oe->new = true;
					$firma_oe->oe_kurzbz='gmbh';
					$firma_oe->insertamum = date('Y-m-d H:i:s');
					$firma_oe->insertvon = $user;
				}
				
				$firma_oe->updateamum = date('Y-m-d H:i:s');
				$firma_oe->updatevon = $user;
				$firma_oe->kundennummer=$kundennummer_gmbh;
				
				
				if(!$firma_oe->saveorganisationseinheit())
				{
					$error = true;
					$errormsg.='Fehler beim Speichern der Kundennummer:'.$firma_oe->errormsg;
				}
			}
			
			//Adressdatensatz anlegen/updaten
			$adresse = new adresse();
			
			if($adresse_id!='')
			{
				$adresse->load($adresse_id);
				$adresse->udpateamum = date('Y-m-d H:i:s');
				$adresse->updatevon = $user;
				$adresse->new = false;
			}
			else
			{
				$adresse->zustelladresse = true;
				$adresse->heimatadresse = false;
				$adresse->new = true;
				$adresse->insertamum = date('Y-m-d H:i:s');
				$adresse->insertvon = $user;
			}
			
			$adresse->strasse = $strasse;
			$adresse->plz = $plz;
			$adresse->ort = $ort;
			$adresse->nation = $nation;
			
			if($adresse->save())
			{		
				//Standort anlegen/updaten
				$standort = new standort();
				
				if($standort_id!='')
				{
					$standort->load($standort_id);
					$standort->new = false;
					$standort->insertamum = date('Y-m-d H:i:s');
					$standort->insertvon = $user;
					$standort->new = false;
				}
				else
				{
					$standort->insertamum = date('Y-m-d H:i:s');
					$standort->insertvon = $user;
					$standort->new = true;
				}
				
				$standort->firma_id = $firma->firma_id;
				$standort->adresse_id = $adresse->adresse_id;				
				$standort->kurzbz = mb_substr($firma->name, 0,16);
				
				if($standort->save())
				{
					//Kontaktdaten anlegen/updaten
					if($fax!='')
					{
						$kontakt = new kontakt();
						if($fax_id!='')
						{
							$kontakt->load($fax_id);
							$kontakt->new = false;
						}
						else
							$kontakt->new = true;
						
						$kontakt->kontakttyp='fax';
						$kontakt->standort_id = $standort->standort_id;
						$kontakt->kontakt = $fax;
												
						if(!$kontakt->save())
						{
							$errormsg.=$kontakt->errormsg;
							$error = true;
						}
					}
					if($telefon!='')
					{
						$kontakt = new kontakt();
						if($telefon_id!='')
						{
							$kontakt->load($telefon_id);
							$kontakt->new = false;
						}
						else
						{
							$kontakt->new = true;
						}
						$kontakt->kontakttyp='telefon';
						$kontakt->standort_id = $standort->standort_id;
						$kontakt->kontakt = $telefon;
												
						if(!$kontakt->save())
						{
							$errormsg.=$kontakt->errormsg;
							$error = true;
						}
					}
					
					if($email!='')
					{
						$kontakt = new kontakt();
						if($email_id!='')
						{
							$kontakt->load($email_id);
							$kontakt->new = false;
						}
						else
						{
							$kontakt->new = true;
						}
							
						$kontakt->kontakttyp='email';
						$kontakt->standort_id = $standort->standort_id;
						$kontakt->kontakt = $email;
												
						if(!$kontakt->save())
						{
							$errormsg.=$kontakt->errormsg;
							$error = true;
						}
					}
				}
				else
				{
					$errormsg.='Standort:'.$standort->errormsg;
					$error = true;
				}
			}
			else
			{
				$errormsg.='Adresse:'.$adresse->errormsg;
				$error=true;
			}
		}
		else
		{
			$errormsg.='Firma:'.$firma->errormsg;
			$error=true;
		}
	}
	
	if($error)
	{
		echo '<span class="error">Fehler: '.$errormsg.'</span>';
		$db->db_query('ROLLBACK;');
		if($firma_id!='')
		{
			$method='update';
			$id = $firma_id;
		}
		else
			$method='new';
	}
	else
	{
		$db->db_query('COMMIT;');
		echo 'Die Firma wurde erfolgreich gespeichert!';
		
		if(isset($_SESSION['wawi/last_bestellung_id']))
			echo '<br><a href="bestellung.php?method=update&id=',$_SESSION['wawi/last_bestellung_id'],'">Zur&uuml;ck zur letzten Bestellung</a>';
		
		$method='update';
		$id=$firma->firma_id;
	}	
}

// Update / Neuanlage
if($method=='new' || $method=='update')
{
	$firma_id='';
	$standort_id='';
	$adresse_id='';
	$fax_id='';
	$email_id='';
	$telefon_id='';
	$anmerkung='';
	$kundennummer_erhalter='';
	$kundennummer_erhalter_id='';
	$kundennummer_gmbh='';
	$kundennummer_gmbh_id='';
	
	if($method=='new')
		echo '<h1>Neue Firma</h1>';
	else
	{
		echo '<h1>Firma Bearbeiten</h1>';

		if(!is_numeric($id))
			die('ID ist ungueltig');
			
		//Firma Laden
		$firma = new firma();
		if(!$firma->load($id))
			die('Firma konnte nicht geladen werden');
		
		$name = $firma->name;
		$anmerkung = $firma->anmerkung;
		$firma_id = $firma->firma_id;
		
		$firma_oe = new firma();
		$firma_oe->get_firmaorganisationseinheit($firma_id,'gst');
		if(isset($firma_oe->result[0]))
		{
			$kundennummer_erhalter = $firma_oe->result[0]->kundennummer;
			$kundennummer_erhalter_id = $firma_oe->result[0]->firma_organisationseinheit_id;
		}
		$firma_oe = new firma();
		$firma_oe->get_firmaorganisationseinheit($firma_id,'gmbh');
		if(isset($firma_oe->result[0]))
		{
			$kundennummer_gmbh = $firma_oe->result[0]->kundennummer;
			$kundennummer_gmbh_id = $firma_oe->result[0]->firma_organisationseinheit_id;
		}
		
		//Standort Laden
		$standort = new standort();
		$standort->load_firma($firma_id);
		if(isset($standort->result[0]))
		{
			$standort_id = $standort->result[0]->standort_id;
			$adresse_id = $standort->result[0]->adresse_id;
			
			//Adresse Laden
			$adresse = new adresse();
			$adresse->load($adresse_id);
							
			$strasse = $adresse->strasse; 
			$plz = $adresse->plz;
			$ort = $adresse->ort;
			$nation = $adresse->nation;
			if($nation=='')
				$nation='A';
			
			//Kontaktdaten Laden
			$kontakt = new kontakt();
			$kontakt->loadFirmaKontakttyp($standort_id, 'telefon');
			
			$telefon = $kontakt->kontakt;
			$telefon_id = $kontakt->kontakt_id;
			
			$kontakt = new kontakt();
			$kontakt->loadFirmaKontakttyp($standort_id, 'fax');
			
			$fax = $kontakt->kontakt;
			$fax_id = $kontakt->kontakt_id;
			
			$kontakt = new kontakt();
			$kontakt->loadFirmaKontakttyp($standort_id, 'email');
			
			$email = $kontakt->kontakt;
			$email_id = $kontakt->kontakt_id;
		}
	}
	
	
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" name="firmaForm">';
	echo '
	<input type="hidden" name="firma_id" value="'.$firma_id.'">
	<input type="hidden" name="standort_id" value="'.$standort_id.'">
	<input type="hidden" name="adresse_id" value="'.$adresse_id.'">
	<input type="hidden" name="telefon_id" value="'.$telefon_id.'">
	<input type="hidden" name="fax_id" value="'.$fax_id.'">
	<input type="hidden" name="email_id" value="'.$email_id.'">
	<input type="hidden" name="kundennummer_erhalter_id" value="'.$kundennummer_erhalter_id.'">
	<input type="hidden" name="kundennummer_gmbh_id" value="'.$kundennummer_gmbh_id.'">
	<table>
	<tr>
		<td>Name:</td>
		<td><input type="text" name="name" maxlength="128" size="80" value="'.$name.'"/></td>
	</tr>
	<tr>
		<td>Straße:</td>
		<td><input type="text" name="strasse" size="40" maxlength="255" value="'.$strasse.'"/></td>
	</tr>
	<tr>
		<td>Plz / Ort:</td>
		<td><input type="text" size="6" name="plz" maxlength="10" value="'.$plz.'"/><input type="text" name="ort" maxlength="255" value="'.$ort.'"/></td>
	</tr>
	<tr>
		<td>Nation:</td>
		<td>
		';
	$nat = new nation();
	$nat->getAll();
	
	echo '<SELECT name="nation">';
	
	foreach($nat->nation as $row)
	{
		if($row->code==$nation)
			$selected='selected';
		else
			$selected='';
		
		echo '<OPTION value="'.$row->code.'" '.$selected.'>'.$row->kurztext.'</OPTION>';
	}
	echo '</SELECT>';
	echo '
		</td>
	</tr>
	<tr>
		<td>Telefon:</td>
		<td><input type="text" name="telefon" maxlength="128" value="'.$telefon.'"/></td>
	</tr>
	<tr>
		<td>Fax:</td>
		<td><input type="text" name="fax" maxlength="128" value="'.$fax.'"/></td>
	</tr>
	<tr>
		<td>E-Mail:</td>
		<td><input type="text" name="email" maxlength="128" size="40" value="'.$email.'"/></td>
	</tr>
	<tr>
		<td>Kundennummer Erhalter:</td>
		<td><input type="text" name="kundennummer_erhalter" maxlength="128" value="'.$kundennummer_erhalter.'"/></td>
	</tr>
	<tr>
		<td>Kundennummer GmbH:</td>
		<td><input type="text" name="kundennummer_gmbh" maxlength="128" value="'.$kundennummer_gmbh.'"/></td>
	</tr>
	<tr>
		<td>Anmerkungen:</td>
		<td><textarea name="anmerkung" cols="50" rows="3">'.$anmerkung.'</textarea></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" name="save" value="Speichern" onclick="return validate()"/></td>
	</tr>
	</table>
	';
	
	echo '</form>';
	
	echo '<script language="javascript" type="text/javascript">
		function validate() 
		{
			maxlength=256;
			
			if(document.firmaForm.name.value == "")
		    {
		    	alert("Name der Firma darf nicht leer sein!");
		    	document.firmaForm.name.focus(); 
		    	return false;
		    }
	     	if(document.firmaForm.email.value.indexOf("@") == -1 && document.firmaForm.email.value != "") 
	     	{
		    	alert("Ungültige E-Mail Adresse eingegeben!");
		    	document.firmaForm.email.focus();
		    	return false;
		    }			
	     	if(document.firmaForm.anmerkung.value.length>=maxlength) 
	     	{
	          alert("Die Anmerkung darf nur 256 Zeichen lang sein. Aktuell sind es : "+document.firmaForm.anmerkung.value.length+" Zeichen.");
	          document.firmaForm.anmerkung.focus();
	          return false;
	     	}

		    return true; 
  		}

		</script>';
}

//Suchen von Firmen
if($method=='search')
{
	$filter = (isset($_POST['filter'])?$_POST['filter']:'');
	
	echo '<H1>Firma suchen</H1>';
	echo '<form action="'.$_SERVER['PHP_SELF'].'?method=search" method="POST">';
	echo '<input type="text" size="30" name="filter" value="'.$filter.'">';
	echo ' <input type="submit" name="send" value="Suchen">';
	echo '</form>';
	
	if($filter!='')
	{
		$firma = new firma();
		if($firma->searchFirma($filter))
		{
			echo '<br /><br />
				<script type="text/javascript">
				$(document).ready(function() 
				{ 
	    			$("#myTable").tablesorter(
					{
						sortList: [[2,0]],
						widgets: ["zebra"]
					});
				});				
				</script>
				
				<table id="myTable" class="tablesorter">
				<thead>
				<tr>
					<th>&nbsp;</th>
					<th>ID</th>
					<th>Name</th>
					<th>Adresse</th>
				</tr>
				</thead>
				<tbody>';
			
			
			foreach($firma->result as $row)
			{
				echo '<tr>';
				echo '<td><a href="firma.php?method=update&amp;id='.$row->firma_id.'" title="Bearbeiten"> <img src="../skin/images/edit_wawi.gif"> </a></td>';
				echo '<td>',$row->firma_id,'</td>';
				echo '<td>',$row->name,'</td>';
				echo '<td>',$row->strasse,' ',$row->plz,' ',$row->ort,'</td>';
				echo '</tr>';
			}
			
			echo '</tbody></table>';
		}
	}
	
}

?>
</body>
</html>
