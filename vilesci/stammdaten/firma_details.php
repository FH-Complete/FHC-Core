<?php
/* Copyright (C) 2008 Technikum-Wien
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
/**
 * Detailansicht fuer die Firmenverwaltung
 * Ermoeglicht die Eingabe der Firmendaten plus zugehoeriger Adressen
 */
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/firma.class.php');
	require_once('../../include/adresse.class.php');
	require_once('../../include/nation.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	
	if (!$conn = pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	// ******* INIT ********
	$user = get_uid();
	
	//Zugriffsrechte pruefen
	$rechte = new benutzerberechtigung($conn);
	$rechte->getBerechtigungen($user);
	
	if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('preinteressent') && !$rechte->isBerechtigt('assistenz'))
		die('Sie haben keine Berechtigung für diese Seite');
	
	$htmlstr = '';
	$errorstr = '';
	$messagestr='';
	$reloadstr = '';
	$error = false;
	$firma_id = (isset($_REQUEST["firma_id"])?$_REQUEST['firma_id']:'');
	$name = (isset($_POST['name'])?$_POST['name']:'');
	$adresse = (isset($_POST['adresse'])?$_POST['adresse']:'');
	$email = (isset($_POST['email'])?$_POST['email']:'');
	$telefon = (isset($_POST['telefon'])?$_POST['telefon']:'');
	$fax = (isset($_POST['fax'])?$_POST['fax']:'');
	$anmerkung = (isset($_POST['anmerkung'])?$_POST['anmerkung']:'');
	$firmentyp_kurzbz = (isset($_POST['typ'])?$_POST['typ']:'');
	$schule = isset($_POST['schule']);
	
	$adresstyp = (isset($_POST['adresstyp'])?$_POST['adresstyp']:'');
	$strasse = (isset($_POST['strasse'])?$_POST['strasse']:'');
	$plz = (isset($_POST['plz'])?$_POST['plz']:'');
	$ort = (isset($_POST['ort'])?$_POST['ort']:'');
	$gemeinde = (isset($_POST['gemeinde'])?$_POST['gemeinde']:'');
	$nation = (isset($_POST['nation'])?$_POST['nation']:'');
	$heimatadresse = (isset($_POST['heimatadresse'])?true:false);
	$zustelladresse = (isset($_POST['zustelladresse'])?true:false);
	$zustellung = (isset($_POST['zustellung'])?true:false);
	$adresse_id = (isset($_REQUEST['adresse_id'])?$_REQUEST['adresse_id']:'');

	//Loeschen einer Adresse
	if(isset($_GET['deleteadresse']))
	{
		if(is_numeric($adresse_id))
		{
			$adresse_obj = new adresse($conn);
			if(!$adresse_obj->delete($adresse_id))
			{
				$errorstr = 'Fehler beim Loeschen der Adresse:'.$adresse_obj->errormsg;
			}
		}
	}
	
	//Speichern einer Adresse
	if(isset($_POST['saveadresse']))
	{
		$adresse_obj = new adresse($conn);
	
		if(is_numeric($adresse_id))
		{
			if($adresse_obj->load($adresse_id))
			{
				$adresse_obj->new = false;
			}
			else
			{
				$errorstr = 'Adresse wurde nicht gefunden:'.$adresse_id;
				$error=true;
			}
		}
		else
		{
			$adresse_obj->new = true;
			$adresse_obj->insertamum = date('Y-m-d H:i:s');
			$adresse_obj->insertvon = $user;
		}
	
		if(!$error)
		{
			$adresse_obj->person_id=null;
			$adresse_obj->strasse = $strasse;
			$adresse_obj->plz = $plz;
			$adresse_obj->ort = $ort;
			$adresse_obj->gemeinde = $gemeinde;
			$adresse_obj->nation = $nation;
			$adresse_obj->typ = $adresstyp;
			$adresse_obj->heimatadresse = $heimatadresse;
			$adresse_obj->zustelladresse = $zustelladresse;
			$adresse_obj->firma_id = $firma_id;
			$adresse_obj->updateamum = date('Y-m-d H:i:s');
			$adresse_obj->updatvon = $user;
	
			//var_dump($adresse_obj);
	
			if(!$adresse_obj->save())
			{
				$errorstr = 'Fehler beim Speichern der Adresse:'.$adresse_obj->errormsg;
			}
			else
			{
				$messagestr = 'Adressdaten wurden erfolgreich gespeichert';
			}
		}
	}
	
	// Speichern der Firmendaten
	if(isset($_POST['save']))
	{
		$firma = new firma($conn);
		
		if($firma_id!='')
		{
			if(!$firma->load($firma_id))
			{
				$error = true;
			}
			else 
			{
				$firma->new = false;
			}
		}
		else 
		{
			$firma->insertamum = date('Y-m-d H:i:s');
			$firma->insertvon = $user;
			$firma->new = true;
		}
		
		if(!$error)
		{
			$firma->name = $name;
			$firma->adresse = $adresse;
			$firma->email = $email;
			$firma->telefon = $telefon;
			$firma->fax = $fax;
			$firma->anmerkung = $anmerkung;
			$firma->firmentyp_kurzbz = $firmentyp_kurzbz;
			$firma->updateamum = date('Y-m-d H:i:s');
			$firma->updatevon = $user;
			$firma->schule = $schule;
			
			if($firma->save())
			{
				$reloadstr .= "<script type='text/javascript'>\n";
				$reloadstr .= "	parent.uebersicht_firma.location.href='firma_uebersicht.php?filter='+parent.uebersicht_firma.filter+'&firmentypfilter='+parent.uebersicht_firma.firmentypfilter;";
				$reloadstr .= " window.top.opener.StudentProjektarbeitFirmaRefresh();";
				$reloadstr .= "</script>\n";
				$messagestr='Firmendaten wurden erfolgreich gespeichert';
			}
			else
			{
				$errorstr = 'Datensatz konnte nicht gespeichert werden: '.$firma->errormsg;
			}
		}
	}
	
	
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Firma - Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script src="../../include/js/mailcheck.js"></script>
<script src="../../include/js/datecheck.js"></script>
<script type="text/javascript">

function confdel()
{
	if(confirm("Diesen Datensatz wirklich loeschen?"))
	  return true;
	return false;
}

</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	//Formular fuer die Firmendaten
	if($errorstr!='')
		echo "<div class='inserterror'>".$errorstr."</div>\n";
	elseif($messagestr!='')
		echo "<div class='insertok'>".$messagestr."</div>\n";
	
	$firma = new firma($conn);
	if($firma_id!='')
	{
		if (!$firma->load($firma_id))
		{
			die('<br>Firma mit der ID <b>'.$firma_id.'</b> existiert nicht');
		}
	}
	else 
	{
		//Bei neuen Firmen wird standardmaessig Partnerfirma ausgewaehlt
		$firma->firmentyp_kurzbz='Partnerfirma';
	}
	
	echo "<form action='firma_details.php' method='POST' name='firma'>\n";
	echo "<input type='hidden' name='firma_id' value='".$firma->firma_id."'>\n";
	
	echo "<table class='detail' style='padding-top:10px;'>\n";
	echo "<tr></tr>\n";
			
	echo "	<tr>\n";
	echo "		<td>Name: </td>";
	echo "		<td colspan='3'><input type='text' name='name' value='".htmlentities($firma->name)."' size='80' maxlength='128' /></td>\n";
	echo "		<td>Typ: </td>";		
	echo "		<td><select name='typ'>\n";

	$qry = "SELECT firmentyp_kurzbz FROM public.tbl_firmentyp ORDER BY firmentyp_kurzbz";
	
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			if ($firma->firmentyp_kurzbz == $row->firmentyp_kurzbz)
				$sel = " selected";
			else
				$sel = "";
			echo "				<option value='".$row->firmentyp_kurzbz."' ".$sel.">".$row->firmentyp_kurzbz."</option>";
		}
	}
	echo "		</select></td>";
	
	echo "		<td>Schule: </td>";
	echo "		<td><input type='checkbox' name='schule' ".($firma->schule?'checked':'')."></td>\n";
	echo "</tr><tr>\n";
		
	echo "		<td>EMail: </td>";
	echo "		<td><input type='text' name='email' value='".htmlentities($firma->email)."' size='40' maxlength='128' /></td>\n";
	echo "		<td>Telefon: </td>";
	echo "		<td><input type='text' name='telefon' value='".htmlentities($firma->telefon)."' maxlength='32' /></td>\n";
	echo "		<td>Fax: </td>";
	echo "		<td><input type='text' name='fax' value='".htmlentities($firma->fax)."' maxlength='32' /></td>\n";
	echo "		<td>Adresse (alt): </td>";
	echo "		<td><input type='text' name='adresse' value='".htmlentities($firma->adresse)."' maxlength='256'></td>\n";
	echo "</tr><tr valign='top'>";
	echo "		<td>Anmerkung: </td>";
	echo "		<td colspan='5'><textarea style='width:100%' name='anmerkung'/>".htmlentities($firma->anmerkung)."</textarea></td>\n";
	echo "		<td></td><td valign='bottom'><input type='submit' name='save' value='speichern'></td>\n";
	echo "	</tr></table>\n";
	echo "</form>\n";
		
	//Nationen laden
	$nation_arr = array();
	$nation = new nation($conn);
	$nation->getAll();
	
	foreach($nation->nation as $row)
		$nation_arr[$row->code]=$row->kurztext;
	
	$adresstyp_arr = array('h'=>'Hauptwohnsitz','n'=>'Nebenwohnsitz','f'=>'Firma',''=>'');

	// Formular fuer die Adressdaten
	//echo "<h3>Adressen:</h3>";
	echo "<form action='".$_SERVER['PHP_SELF']."?firma_id=$firma_id' method='POST' />";
	echo "<table class='liste'><tr><th>STRASSE</th><th>PLZ</th><th>ORT</th><th>GEMEINDE</th><th>NATION</th><th>TYP</th><th><font size='0'>Heimatadr.</font></th><th><font size='0'>Zustelladr.</font></th></tr>";
	$adresse_obj = new adresse($conn);
	$adresse_obj->load_firma($firma_id);

	//Anzeigen aller Adresssen
	foreach ($adresse_obj->result as $row)
	{
		echo '<tr class="liste1">';
		echo "<td>$row->strasse</td>";
		echo "<td>$row->plz</td>";
		echo "<td>$row->ort</td>";
		echo "<td>$row->gemeinde</td>";
		echo "<td>".$nation_arr[$row->nation]."</td>";
		echo "<td>".$adresstyp_arr[$row->typ]."</td>";
		echo "<td>".($row->heimatadresse?'Ja':'Nein')."</td>";
		echo "<td>".($row->zustelladresse?'Ja':'Nein')."</td>";
		echo "<td><a href='".$_SERVER['PHP_SELF']."?editadresse=true&adresse_id=$row->adresse_id&firma_id=$firma_id'><img src='../../skin/images/application_form_edit.png' alt='bearbeiten' title='bearbeiten' /></a></td>";
		echo "<td><a href='".$_SERVER['PHP_SELF']."?deleteadresse=true&adresse_id=$row->adresse_id&firma_id=$firma_id' onclick='return confdel()'><img src='../../skin/images/application_form_delete.png' alt='loeschen' title='loeschen'/></a></td>";
	}

	$savebuttonvalue='Neu';
	//wenn die Adressen editiert werden dann die Adressdaten laden
	if(isset($_GET['editadresse']))
	{
		$adresse_obj = new adresse($conn);
		if($adresse_obj->load($adresse_id))
		{
			$strasse = $adresse_obj->strasse;
			$plz = $adresse_obj->plz;
			$ort = $adresse_obj->ort;
			$gemeinde = $adresse_obj->gemeinde;
			$nation = $adresse_obj->nation;
			$typ = $adresse_obj->typ;
			$heimatadresse = $adresse_obj->heimatadresse;
			$zustelladresse = $adresse_obj->zustelladresse;
			$firma_id = $adresse_obj->firma_id;
			$savebuttonvalue='Speichern';
		}
	}
	else
	{
		//bei einer neuen Adresse die Felder leeren
		$strasse='';
		$plz='';
		$ort='';
		$gemeinde='';
		$nation = 'A';
		$typ='';
		$heimatadresse='';
		$zustelladresse='';
		$adresse_id='';
	}
	
	echo "<input type='hidden' name='adresse_id' value='".$adresse_id."' />";
	echo '<tr class="liste1">';
	echo "<td><input type='text' name='strasse' value='".htmlentities($strasse)."' /></td>";
	echo "<td><input type='text' name='plz' size='4' value='".htmlentities($plz)."' /></td>";
	echo "<td><input type='text' name='ort' value='".htmlentities($ort)."' /></td>";
	echo "<td><input type='text' name='gemeinde' value='".htmlentities($gemeinde)."' /></td>";
	echo "<td><SELECT name='nation'>";
	foreach ($nation_arr as $code=>$kurzbz)
	{
		if($code==$nation)
			$selected='selected';
		else
			$selected='';

		echo "<OPTION value='$code' $selected>$kurzbz</OPTION>";
	}

	echo "</SELECT></td>";
	echo "<td><SELECT name='adresstyp'>";
	foreach($adresstyp_arr as $code=>$kurzbz)
	{
		if($code==$typ)
			$selected='selected';
		else
			$selected='';

		echo "<OPTION value='$code' $selected>$kurzbz</OPTION>";
	}
	echo "</SELECT></td>";
	echo "<td><input type='checkbox' name='heimatadresse' ".($heimatadresse?'checked':'')." /></td>";
	echo "<td><input type='checkbox' name='zustelladresse' ".($zustelladresse?'checked':'')." /></td>";
	echo "<td colspan='2'><input type='submit' name='saveadresse' value='$savebuttonvalue' /></td>";

	echo "</table>";
	echo "</form>";
	
	//eventuell die Felder im oeffnenden Fenster aktualisieren
	echo $reloadstr;
?>

</body>
</html>