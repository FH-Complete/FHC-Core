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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/person.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/adresse.class.php');
require_once('../../include/adressentyp.class.php');
require_once('../../include/nation.class.php');
require_once('../../include/firma.class.php');
require_once('../../include/standort.class.php');
require_once('../../include/kontakt.class.php');

$user=get_uid();
$datum_obj = new datum();
loadVariables($user);

if(isset($_GET['getfirma']))
{
	$firma = new firma();
	$firma->searchFirma($_GET['q']);

	foreach ($firma->result as $row)
	{
		echo $row->name.'|'.$row->firma_id."\n";
	}
	exit;
}

if(isset($_GET['getstandort']))
{
	if(isset($_GET['firma_id']) && is_numeric($_GET['firma_id']))
	{
		$standort = new standort();
		$standort->load_firma($_GET['firma_id']);
		$arr = array();
		foreach($standort->result as $row)
		{
			array_push($arr,array('kurzbz'=>$row->bezeichnung,'standort_id'=>$row->standort_id));
		}
		echo json_encode($arr);
	}
	exit;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/styles/jquery.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js" ></script>

	<script language="Javascript">
		function confdel()
		{
			return confirm('Wollen Sie diesen Datensatz wirklich loeschen?');
		}
		$(document).ready(function()
		{
			$("#firma_ac").autocomplete("kontaktdaten_edit.php?getfirma=true",
			{
				width: 500,
				minChars:2,
				matchSubset:1,matchContains:1,
				highlight: false,
				scroll: true,
				formatItem: function(row)
				{
					return row[0];
				},
				formatResult: function(row)
				{
					return row[0];
				}
			}).result(function(event,row)
			{
				$("#firma").val(row[1]);
			});

			$("#firma_kontakt_ac").autocomplete("kontaktdaten_edit.php?getfirma=true",
			{
				width: 500,
				minChars:2,
				matchSubset:1,matchContains:1,
				highlight: false,
				scroll: true,
				formatItem: function(row)
				{
					return row[0];
				},
				formatResult: function(row)
				{
					return row[0];
				}
			}).result(function(event,row)
			{
				$("#firma_kontakt").val(row[1]);
				setstandort();
			});

			function setstandort()
			{
				$('#standort_kontakt').children().remove().end();
				$.ajax
				(
					{
						type: "GET",
						url: 'kontaktdaten_edit.php',
						dataType: 'json',
						data: "getstandort=true&firma_id=" + $("#firma_kontakt").val(),
						success: function(json)
						{
							var output = '';
							for (p in json)
							{
								output += '<option value=\"' + json[p].standort_id + '\">' + json[p].kurzbz + '</option>\n';
							}
							$('#standort_kontakt').html(output);
							$('#standort_kontakt').result(function(event, data, formatted) {}).focus();
						}
					}
				);
			}
		});
	</script>
	<style>
		td
		{
			font-size: small;
		}
	</style>
</head>
<body>
<?php
//Berechtigung pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('mitarbeiter') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('preinteressent'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$person_id = (isset($_GET['person_id'])?$_GET['person_id']:'');
$adresse_id = (isset($_REQUEST['adresse_id'])?$_REQUEST['adresse_id']:'');
$kontakt_id = (isset($_REQUEST['kontakt_id'])?$_REQUEST['kontakt_id']:'');
$errormsg = '';
$error = false;

$adresstyp = (isset($_POST['adresstyp'])?$_POST['adresstyp']:'');
$strasse = (isset($_POST['strasse'])?$_POST['strasse']:'');
$plz = (isset($_POST['plz'])?$_POST['plz']:'');
$ort = (isset($_POST['ort'])?$_POST['ort']:'');
$gemeinde = (isset($_POST['gemeinde'])?$_POST['gemeinde']:'');
$nation = (isset($_POST['nation'])?$_POST['nation']:'');
$heimatadresse = (isset($_POST['heimatadresse'])?true:false);
$zustelladresse = (isset($_POST['zustelladresse'])?true:false);
$standort_id = (isset($_POST['standort_id'])?$_POST['standort_id']:'');
$firma_id = (isset($_POST['firma'])?$_POST['firma']:'');
$firma_ac = (isset($_POST['firma_ac'])?$_POST['firma_ac']:'');
$zustellung = (isset($_POST['zustellung'])?true:false);
$anmerkung = (isset($_POST['anmerkung'])?$_POST['anmerkung']:false);
$kontakt = (isset($_POST['kontakt'])?$_POST['kontakt']:false);
$kontakttyp = (isset($_POST['kontakttyp'])?$_POST['kontakttyp']:false);

if($person_id=='')
	die('Person_id muss uebergeben werden');

//Loeschen einer Adresse
if(isset($_GET['deleteadresse']))
{
	if(is_numeric($adresse_id))
	{
		$adresse_obj = new adresse();
		if(!$adresse_obj->delete($adresse_id))
		{
			$errormsg = 'Fehler beim Loeschen der Adresse:'.$adresse_obj->errormsg;
		}
	}
}

//Loeschen einen Kontakt
if(isset($_GET['deletekontakt']))
{
	if(is_numeric($kontakt_id))
	{
		$kontakt_obj = new kontakt();
		if(!$kontakt_obj->delete($kontakt_id))
		{
			$errormsg = 'Fehler beim Loeschen des Kontakts:'.$kontakt_obj->errormsg;
		}
	}
}

//Speichern einer Adresse
if(isset($_POST['saveadresse']))
{
	$adresse_obj = new adresse();

	if(is_numeric($adresse_id))
	{
		if($adresse_obj->load($adresse_id))
		{
			$adresse_obj->new = false;
		}
		else
		{
			$errormsg = 'Adresse wurde nicht gefunden:'.$adresse_id;
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
		$adresse_obj->person_id=$person_id;
		$adresse_obj->strasse = $strasse;
		$adresse_obj->plz = $plz;
		$adresse_obj->ort = $ort;
		$adresse_obj->gemeinde = $gemeinde;
		$adresse_obj->nation = $nation;
		$adresse_obj->typ = $adresstyp;
		$adresse_obj->heimatadresse = $heimatadresse;
		$adresse_obj->zustelladresse = $zustelladresse;
		if($firma_ac=='')
			$adresse_obj->firma_id = '';
		else
			$adresse_obj->firma_id = $firma_id;
		$adresse_obj->updateamum = date('Y-m-d H:i:s');
		$adresse_obj->updatvon = $user;

		if(!$adresse_obj->save())
		{
			$errormsg = 'Fehler beim Speichern der Adresse:'.$adresse_obj->errormsg;
		}
		else
		{
			$errormsg = 'Daten wurden gespeichert';
		}
	}
}

//Speichern eines Kontaktes
if(isset($_POST['savekontakt']))
{
	$kontakt_obj = new kontakt();

	if(is_numeric($kontakt_id))
	{
		if($kontakt_obj->load($kontakt_id))
		{
			$kontakt_obj->new = false;
		}
		else
		{
			$errormsg = 'Kontakt wurde nicht gefunden:'.$kontakt_id;
			$error=true;
		}
	}
	else
	{
		$kontakt_obj->new = true;
		$kontakt_obj->insertamum = date('Y-m-d H:i:s');
		$kontakt_obj->insertvon = $user;
	}

	if(!$error)
	{
		$kontakt_obj->person_id=$person_id;
		//$kontakt_obj->firma_id = $firma_id;
		$kontakt_obj->standort_id = $standort_id;
		$kontakt_obj->kontakttyp = $kontakttyp;
		$kontakt_obj->kontakt = $kontakt;
		$kontakt_obj->anmerkung = $anmerkung;
		$kontakt_obj->zustellung = $zustellung;
		$kontakt_obj->updateamum = date('Y-m-d H:i:s');
		$kontakt_obj->updatvon = $user;

		if(!$kontakt_obj->save())
		{
			$errormsg = 'Fehler beim Speichern des Kontaktes:'.$kontakt_obj->errormsg;
		}
		else
		{
			$errormsg = 'Daten wurden gespeichert';
		}
	}
}

//Person laden
$person = new person();
if(!$person->load($person_id))
	die('Person wurde nicht gefunden');

//Nationen laden
$nation_arr = array();
$nation = new nation();
$nation->getAll();

foreach($nation->nation as $row)
	$nation_arr[$row->code]=$row->kurztext;

//Firmen laden

$firma_arr = array();
$firma = new firma();
$firma->getAll();

foreach($firma->result as $row)
	$firma_arr[$row->firma_id]=$row->name;

//Kontakttypen laden
$kontakttyp_arr = array();
$kontakt_obj = new kontakt();
$kontakt_obj->getKontakttyp();
foreach ($kontakt_obj->result as $row)
	$kontakttyp_arr[]=$row->kontakttyp;

echo "<h2>Kontaktdaten von $person->vorname $person->nachname</h2>";
echo $errormsg.'<br>';

// *** ADRESSEN ***
echo "<h3>Adressen:</h3>";
echo "<form accept-charset='UTF-8' action='".$_SERVER['PHP_SELF']."?person_id=$person_id' method='POST' />";
echo "<table class='liste'><tr><th>STRASSE</th><th>PLZ</th><th>ORT</th><th>GEMEINDE</th><th>NATION</th><th>TYP</th><th>HEIMAT</th><th>ZUSTELLUNG</th><th>FIRMA</th></tr>";
$adresse_obj = new adresse();
$adresse_obj->load_pers($person_id);

foreach ($adresse_obj->result as $row)
{
	echo '<tr class="liste1">';
	echo "<td>$row->strasse</td>";
	echo "<td>$row->plz</td>";
	echo "<td>$row->ort</td>";
	echo "<td>$row->gemeinde</td>";
	echo "<td>".(isset($nation_arr[$row->nation])?$nation_arr[$row->nation]:'')."</td>";
	echo "<td>".(isset($row->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE])?$row->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE]:'')."</td>";
	echo "<td>".($row->heimatadresse?'Ja':'Nein')."</td>";
	echo "<td>".($row->zustelladresse?'Ja':'Nein')."</td>";
	echo "<td>".($row->firma_id!=''?$firma_arr[$row->firma_id]:'')."</td>";
	echo "<td><a href='".$_SERVER['PHP_SELF']."?editadresse=true&adresse_id=$row->adresse_id&person_id=$person_id'>bearbeiten</a></td>";
	echo "<td><a href='".$_SERVER['PHP_SELF']."?deleteadresse=true&adresse_id=$row->adresse_id&person_id=$person_id' onclick='return confdel()'>loeschen</a></td>";
}

$savebuttonvalue='Neu';
if(isset($_GET['editadresse']))
{
	$adresse_obj = new adresse();
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
	$strasse='';
	$plz='';
	$ort='';
	$gemeinde='';
	$nation = 'A';
	$typ='';
	$heimatadresse='';
	$zustelladresse='';
	$firma_id='';
	$adresse_id='';
}
	echo "<input type='hidden' name='adresse_id' value='".$adresse_id."' />";
	echo '<tr class="liste1">';
	echo "<td><input type='text' name='strasse' value='".$strasse."' /></td>";
	echo "<td><input type='text' name='plz' size='4' value='".$plz."' /></td>";
	echo "<td><input type='text' name='ort' value='".$ort."' /></td>";
	echo "<td><input type='text' name='gemeinde' value='".$gemeinde."' /></td>";
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

	$adressetyp = new adressentyp();
	$adressetyp->getAll();

	echo "<td><SELECT name='adresstyp'>";
	foreach($adressetyp->result as $row)
	{
		if($row->adressentyp==$typ)
			$selected='selected';
		else
			$selected='';

		echo "<OPTION value='". $row->adressentyp ."' $selected>$row->bezeichnung</OPTION>";
	}
	echo "</SELECT></td>";
	echo "<td><input type='checkbox' name='heimatadresse' ".($heimatadresse?'checked':'')." /></td>";
	echo "<td><input type='checkbox' name='zustelladresse' ".($zustelladresse?'checked':'')." /></td>";
	$firma_obj = new firma();
	if($firma_id!='')
	{
		$firma_obj->load($firma_id);
	}
	echo '<td><input type="text" id="firma_ac" name="firma_ac" value="'.$firma_obj->name.'" /><input type="hidden" id="firma" name="firma" value="'.$firma_id.'" /></td>';
	echo "<td><input type='submit' name='saveadresse' value='$savebuttonvalue' /></td>";

	echo "</table>";
	echo "</form>";

// *** Kontakte ***
echo "<h3>Kontakte:</h3>";
echo "<form action='".$_SERVER['PHP_SELF']."?person_id=$person_id' method='POST' />";
echo "<table class='liste'><tr><th>TYP</th><th>KONTAKT</th><th>ZUSTELLUNG</th><th>ANMERKUNG</th><th>FIRMA</th></tr>";
$kontakt_obj = new kontakt();
$kontakt_obj->load_pers($person_id);

foreach ($kontakt_obj->result as $row)
{
	echo '<tr class="liste1">';
	echo "<td>$row->kontakttyp</td>";
	echo "<td>$row->kontakt</td>";
	echo "<td>".($row->zustellung?'Ja':'Nein')."</td>";
	echo "<td>$row->anmerkung</td>";
	echo "<td>".($row->firma_id!=''?$firma_arr[$row->firma_id]:'')."</td>";
	echo "<td><a href='".$_SERVER['PHP_SELF']."?editkontakt=true&kontakt_id=$row->kontakt_id&person_id=$person_id'>bearbeiten</a></td>";
	echo "<td><a href='".$_SERVER['PHP_SELF']."?deletekontakt=true&kontakt_id=$row->kontakt_id&person_id=$person_id' onclick='return confdel()'>loeschen</a></td>";
}

$savebuttonvalue='Neu';
if(isset($_GET['editkontakt']))
{
	$kontakt_obj = new kontakt();
	if($kontakt_obj->load($kontakt_id))
	{
		$kontakttyp = $kontakt_obj->kontakttyp;
		$zustellung = $kontakt_obj->zustellung;
		$anmerkung = $kontakt_obj->anmerkung;
		$kontakt = $kontakt_obj->kontakt;
		$firma_id = $kontakt_obj->firma_id;
		$standort_id = $kontakt_obj->standort_id;
		$savebuttonvalue='Speichern';
	}
	else
		echo 'Fehler beim Laden'.$kontakt_id;
}
else
{
	$kontakt_id='';
	$kontakttyp='';
	$kontakt='';
	$zustellung=true;
	$anmerkung='';
	$firma_id='';
	$standort_id='';
}
	echo "<input type='hidden' name='kontakt_id' value='".$kontakt_id."' />";
	echo '<tr class="liste1">';
	echo "<td><SELECT name='kontakttyp'>";

	foreach ($kontakttyp_arr as $kurzbz)
	{
		if($kurzbz==$kontakttyp)
			$selected='selected';
		else
			$selected='';

		echo "<OPTION value='$kurzbz' $selected>$kurzbz</OPTION>";
	}
	echo "</SELECT></td>";

	echo "<td><input type='text' name='kontakt' value='".$kontakt."' /></td>";
	echo "<td><input type='checkbox' name='zustellung' ".($zustellung?'checked':'')." /></td>";
	echo "<td><input type='text' name='anmerkung' value='".$anmerkung."' /></td>";
	$standort = new standort();
	$firma_obj = new firma();
	if($standort_id!='')
	{
		$standort->load($standort_id);
		$firma_obj->load($standort->firma_id);
	}
	echo '<td><input type="text" id="firma_kontakt_ac" name="firma_kotankt_ac" value="'.$firma_obj->name.'" /><input type="hidden" id="firma_kontakt" name="firma" value="" />
	<SELECT id="standort_kontakt" name="standort_id">';
	if($standort_id!='')
	{
		$standort->load_firma($standort->firma_id);
		echo "<OPTION value='' >-- keine Auswahl --</OPTION>";
		foreach ($standort->result as $row)
		{
			if($row->standort_id==$standort_id)
				$selected='selected="true"';
			else
				$selected='';

			echo '<OPTION value="'.$row->standort_id.'" '.$selected.'>'.$row->kurzbz.'</OPTION>';
		}
	}
	echo '</SELECT></td>';
	echo "<td><input type='submit' name='savekontakt' value='$savebuttonvalue' /></td>";

	echo "</table>";
	echo "</form>";

?>
</body>
</html>
