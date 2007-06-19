<?php
/* Copyright (C) 2007 Technikum-Wien
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

require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/adresse.class.php');
require_once('../../../include/studiensemester.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
	die('Fehler beim Herstellen der DB Connection');

$user=get_uid();

loadVariables($conn, $user);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
<script language="Javascript">
function disablefields(obj)
{
	if(obj.value==0)
		val=false;
	else
		val=true;

	document.getElementById('titel').disabled=val;
	document.getElementById('nachname').disabled=val;
	document.getElementById('vorname').disabled=val;
	document.getElementById('geschlecht').disabled=val;
	document.getElementById('geburtsdatum').disabled=val;
	//document.getElementById('adresse').disabled=val;
	//document.getElementById('plz').disabled=val;
	//document.getElementById('ort').disabled=val;
	if(val)
	{
		document.getElementById('ueb1').style.display = 'block';
		document.getElementById('ueb2').style.display = 'block';
		document.getElementById('ueb3').style.display = 'block';
	}
	else
	{
		document.getElementById('ueb1').style.display = 'none';
		document.getElementById('ueb2').style.display = 'none';
		document.getElementById('ueb3').style.display = 'none';
	}
}

</script>
</head>
<body>
<h1>Interessent Anlegen</h1>
<?php
//Berechtigung pruefen
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$where = '';
$error = false;
//Parameter
$titel = (isset($_POST['titel'])?$_POST['titel']:'');
$nachname = (isset($_POST['nachname'])?$_POST['nachname']:'');
$vorname = (isset($_POST['vorname'])?$_POST['vorname']:'');
$geschlecht = (isset($_POST['geschlecht'])?$_POST['geschlecht']:'');
$geburtsdatum = (isset($_POST['geburtsdatum'])?$_POST['geburtsdatum']:'');
$adresse = (isset($_POST['adresse'])?$_POST['adresse']:'');
$plz = (isset($_POST['plz'])?$_POST['plz']:'');
$ort = (isset($_POST['ort'])?$_POST['ort']:'');
$email = (isset($_POST['email'])?$_POST['email']:'');
$telefon = (isset($_POST['telefon'])?$_POST['telefon']:'');
$mobil = (isset($_POST['mobil'])?$_POST['mobil']:'');
$letzteausbildung = (isset($_POST['letzteausbildung'])?$_POST['letzteausbildung']:'');
$ausbildungsart = (isset($_POST['ausbildungsart'])?$_POST['ausbildungsart']:'');
$anmerkungen = (isset($_POST['anmerkungen'])?$_POST['anmerkungen']:'');
$studiengang_kz = (isset($_POST['studiengang_kz'])?$_POST['studiengang_kz']:'');
$person_id = (isset($_POST['person_id'])?$_POST['person_id']:'');
$ueberschreiben = (isset($_POST['ueberschreiben'])?$_POST['ueberschreiben']:'');
$studiensemester_kurzbz = (isset($_POST['studiensemester_kurzbz'])?$_POST['studiensemester_kurzbz']:'');
//end Parameter

if($studiensemester_kurzbz == '')
{
	$stsem = new studiensemester($conn);
	$stsem->getNextStudiensemester();
	$studiensemester_kurzbz = $stsem->studiensemester_kurzbz;
}

//Testphase fix
if($studiengang_kz!='' && $studiengang_kz!='299')
	die('Keine Berechtigung zum Importieren');
//end Testphase

// *** Speichern der Daten ***
if(isset($_POST['save']))
{
	//echo "Saving Data: Geburtsdatum: $geburtsdatum | Titel: $titel | Nachname: $nachname | Vorname: $vorname |
	//		Geschlecht: $geschlecht | Adresse: $adresse | Plz: $plz | Ort: $ort |
	//		Email: $email | Telefon: $telefon | Mobil: $mobil | Letzteausbildung: $letzteausbildung | ausbildungsart: $ausbildungsart |
	//		anmerkungen: $anmerkungen | studiengang_kz: $studiengang_kz | person_id: $person_id<br><br>";
	$person = new person($conn);
	$prestudent = new prestudent($conn);
	pg_query($conn, 'BEGIN');
	//Wenn die person_id=0 dann wird eine neue Person angelegt
	//Ansosnsten wird es an die Person mit $person_id angehaengt
	if($person_id!='0')
	{
		if(!$person->load($person_id))
		{
			$error=true;
			$errormsg = 'Person konnte nicht geladen werden';
		}
		else
		{
			$geburtsdatum = $person->gebdatum;
			$vorname = $person->vorname;
			$nachname = $person->nachname;
			$titel = $person->titelpre;
			$geschlecht = $person->geschlecht;
			//Wenn Prestudent bereits existiert, dann abbrechen
			if($prestudent->exists($person_id, $studiengang_kz))
			{
				$error=true;
				$errormsg = 'Prestudent existiert bereits!';
			}
		}
	}
	else
	{
		$person->new = true;
		$person->titelpre = $titel;
		$person->nachname = $nachname;
		$person->vorname = $vorname;
		$person->geschlecht = $geschlecht;
		$person->gebdatum = $geburtsdatum;
		$person->aktiv = true;
		if($person->save())
		{
			$error=false;
		}
		else
		{
			$error=true;
			$errormsg = "Person konnte nicht gespeichert werden: $person->errormsg";
		}
	}

	//Adresse anlegen
	if($ueberschreiben!='' && !($plz=='' && $adresse=='' && $ort==''))
	{
		if($person_id=='0')
			$ueberschreiben='Nein';

		$adr = new adresse($conn);
		//Adresse neu anlegen
		if($ueberschreiben=='Nein')
		{
			$adr->new = true;
			$adr->insertamum = date('Y-m-d H:i:s');
			$adr->insertvon = $user;
		}
		else
		{
			//Bestehende Adresse Ueberschreiben

			//Adressen der Peron laden
			$adr->load_pers($person->person_id);
			if(isset($adr->result[0]))
			{
				//Erste Adresse laden
				if($adr->load($adr->result[0]->adresse_id))
				{
					$adr->new = false;
					$adr->updateamum = date('Y-m-d H:i:s');
					$adr->updatevon = $user;
				}
				else
				{
					$error = true;
					$errormsg = 'Fehler beim laden der Adresse';
				}
			}
			else
			{
				$error = true;
				$errormsg = 'Kann die Adresse nicht ueberschreiben wenn keine da ist';
			}
		}

		if(!$error)
		{
			//Adressdaten zuweisen und speichern
			$adr->person_id = $person->person_id;
			$adr->strasse = $adresse;
			$adr->plz = $plz;
			$adr->ort = $ort;
			$adr->typ = 'h';
			$adr->heimatadresse = true;
			$adr->zustelladresse = true;
			if(!$adr->save())
			{
				$error = true;
				$errormsg = $adr->errormsg;
			}
		}
	}

	//Kontaktdaten anlegen
	if(!$error)
	{
		//EMail Adresse speichern
		if($email!='')
		{
			$kontakt = new kontakt($conn);
			$kontakt->person_id = $person->person_id;
			$kontakt->kontakttyp = 'email';
			$kontakt->kontakt = $email;
			$kontakt->zustellung = true;
			$kontakt->insertamum = date('Y-m-d H:i:s');
			$kontakt->insertvon = $user;
			$kontakt->new = true;

			if(!$kontakt->save())
			{
				$error = true;
				$errormsg = 'Fehler beim Speichern der Email Adresse';
			}
		}
		//Telefonnummer speichern
		if($telefon!='')
		{
			$kontakt = new kontakt($conn);
			$kontakt->person_id = $person->person_id;
			$kontakt->kontakttyp = 'telefon';
			$kontakt->kontakt = $telefon;
			$kontakt->zustellung = true;
			$kontakt->insertamum = date('Y-m-d H:i:s');
			$kontakt->insertvon = $user;
			$kontakt->new = true;

			if(!$kontakt->save())
			{
				$error = true;
				$errormsg = 'Fehler beim Speichern der Telefonnummer';
			}
		}
		//Mobiltelefonnummer speichern
		if($mobil!='')
		{
			$kontakt = new kontakt($conn);
			$kontakt->person_id = $person->person_id;
			$kontakt->kontakttyp = 'mobil';
			$kontakt->kontakt = $mobil;
			$kontakt->zustellung = true;
			$kontakt->insertamum = date('Y-m-d H:i:s');
			$kontakt->insertvon = $user;
			$kontakt->new = true;

			if(!$kontakt->save())
			{
				$error = true;
				$errormsg = 'Fehler beim Speichern der Mobiltelefonnummer';
			}
		}
	}

	//Prestudent Anlegen
	if(!$error)
	{
		$prestudent->new = true;
		$prestudent->aufmerksamdurch_kurzbz = 'k.A.';
		$prestudent->person_id = $person->person_id;
		$prestudent->studiengang_kz = $studiengang_kz;
		$prestudent->ausbildungcode = $letzteausbildung;
		$prestudent->anmerkung = $anmerkungen .($ausbildungsart!=''?' Ausbildungsart:'.$ausbildungsart:'');
		$prestudent->reihungstestangetreten = false;
		$prestudent->bismelden = true;

		if(!$prestudent->save())
		{
			$error=true;
			$errormsg = $prestudent->errormsg;
		}
	}

	//Prestudent Rolle Anlegen
	if(!$error)
	{
		$rolle = new prestudent($conn);

		$rolle->prestudent_id = $prestudent->prestudent_id;
		$rolle->rolle_kurzbz = 'Interessent';
		$rolle->studiensemester_kurzbz = $studiensemester_kurzbz;
		$rolle->ausbildungssemester = 1;
		$rolle->datum = date('Y-m-d');
		$rolle->insertamum = date('Y-m-d H:i:s');
		$rolle->insertvon = $user;

		$rolle->new = true;

		if(!$rolle->save_rolle())
		{
			$error = true;
			$errormsg = $rolle->errormsg;
		}
		else
			$error = false;
	}

	if(!$error)
	{
		pg_query($conn, 'COMMIT');
		die("<b>Interessent $vorname $nachname wurde erfolgreich angelegt</b><br>");
	}
	else
	{
		pg_query($conn, 'ROLLBACK');
		echo '<span class="error">'.$errormsg.'</span>';
	}
}
// *** SAVE ENDE ***
?>
<form method='POST'>
<table width="100%">

<tr>
<td>
<!--Formularfelder-->
<table>
<?php
echo '<tr><td>Titel</td><td><input type="text" id="titel" name="titel" maxlength="64" value="'.$titel.'" /></td></tr>';
echo '<tr><td>Vorname</td><td><input type="text" id="vorname" maxlength="32" name="vorname" value="'.$vorname.'" /></td></tr>';
echo '<tr><td>Nachname</td><td><input type="text" maxlength="64" id="nachname" name="nachname" value="'.$nachname.'" /></td></tr>';
echo '<tr><td>Geschlecht</td><td><SELECT id="geschlecht" name="geschlecht">';
echo '<OPTION value="m" '.($geschlecht=='m'?'selected':'').'>m&auml;nnlich</OPTION>';
echo '<OPTION value="w" '.($geschlecht=='w'?'selected':'').'>weiblich</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Geburtsdatum</td><td><input type="text" id="geburtsdatum" size="10" maxlength="10" name="geburtsdatum" value="'.$geburtsdatum.'" /></td></tr>';
echo '<tr><td colspan="2"><fieldset><legend>Adresse</legend><table>';
echo '<tr><td>Adresse</td><td><input type="text" id="adresse" maxlength="256" name="adresse" value="'.$adresse.'" /></td></tr>';
echo '<tr><td>Postleitzahl</td><td><input type="text" maxlength="16" id="plz" name="plz" value="'.$plz.'" /></td></tr>';
echo '<tr><td>Ort</td><td><input type="text" id="ort" maxlength="256" name="ort" value="'.$ort.'" /></td></tr>';
echo '</table>';
echo '<div style="display: none;" id="ueb1"><input type="radio" id="ueberschreiben1" name="ueberschreiben" value="Ja" checked>Bestehende Adresse �berschreiben</div>';
echo '<div style="display: none;" id="ueb2"><input type="radio" id="ueberschreiben2" name="ueberschreiben" value="Nein">Adresse neu anlegen</div>';
echo '<div style="display: none;" id="ueb3"><input type="radio" id="ueberschreiben3" name="ueberschreiben" value="">Adresse nicht anlegen</div>';
echo '</fieldset></td></tr>';
echo '<tr><td>EMail</td><td><input type="text" id="email" maxlength="128" name="email" value="'.$email.'" /></td></tr>';
echo '<tr><td>Telefon</td><td><input type="text" id="telefon" maxlength="128" name="telefon" value="'.$telefon.'" /></td></tr>';
echo '<tr><td>Mobil</td><td><input type="text" id="mobil" maxlength="128" name="mobil" value="'.$mobil.'" /></td></tr>';
echo '<tr><td>Letzte Ausbildung</td><td><SELECT id="letzteausbildung" name="letzteausbildung">';
$qry = "SELECT * FROM bis.tbl_ausbildung ORDER BY ausbildungcode";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		echo '<OPTION value="'.$row->ausbildungcode.'" '.($letzteausbildung==$row->ausbildungcode?'selected':'').'>'.$row->ausbildungbez.'</OPTION>';
	}
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Ausbildungsart</td><td><input type="text" id="ausbildungsart" name="ausbildungsart" value="'.$ausbildungsart.'" /></td></tr>';
echo '<tr><td>Anmerkungen</td><td><textarea id="anmerkung" name="anmerkungen">'.$anmerkungen.'</textarea></td></tr>';
echo '<tr><td>Studiengang</td><td><SELECT id="studiengang_kz" name="studiengang_kz">';
$stg_obj = new studiengang($conn);
$stg_obj->getAll('typ, kurzbz');
foreach ($stg_obj->result as $row)
	echo '<OPTION value="'.$row->studiengang_kz.'" '.($row->studiengang_kz==$studiengang_kz?'selected':'').'>'.$row->kuerzel.'</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Studiensemester</td><td><SELECT id="studiensemester_kurzbz" name="studiensemester_kurzbz">';
$stsem = new studiensemester($conn);
$stsem->getAll();
foreach ($stsem->studiensemester as $row)
	echo '<OPTION value="'.$row->studiensemester_kurzbz.'" '.($row->studiensemester_kurzbz==$studiensemester_kurzbz?'selected':'').'>'.$row->studiensemester_kurzbz.'</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><tr><td></td><td>';

if($geburtsdatum=='' && $vorname=='' && $nachname=='')
	echo '<input type="submit" name="showagain" value="Vorschlag laden"</td></tr>';
else
	echo '<input type="submit" name="save" value="Speichern"</td></tr>';
?>

</table>
</td>
<td valign="top">
<!--Vorschlaege-->
<?php
//Vorschlaege laden
if($geburtsdatum!='')
{
	if(ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$geburtsdatum))
	{
		$where = " gebdatum='".$geburtsdatum."'";
	}
}

if($vorname!='' && $nachname!='')
{
	if($where!='')
		$where.=' OR';
	$where.=" (LOWER(vorname)='".strtolower($vorname)."' AND LOWER(nachname)='".strtolower($nachname)."')";
}

if($where!='')
{
	$qry = "SELECT * FROM public.tbl_person WHERE $where ORDER BY nachname, vorname, gebdatum";
	if($result = pg_query($conn, $qry))
	{
		echo '<table><tr><th></th><th>Nachname</th><th>Vorname</th><th>GebDatum</th><th>SVNR</th><th>Geschlecht</th><th>Adresse</th></tr>';
		while($row = pg_fetch_object($result))
		{
			echo '<tr valign="top"><td><input type="radio" name="person_id" value="'.$row->person_id.'" onclick="disablefields(this)"></td><td>'."$row->nachname</td><td>$row->vorname</td><td>$row->gebdatum</td><td>$row->svnr</td><td>".($row->geschlecht=='m'?'m�nnlich':'weiblich')."</td><td>";
			$qry_adr = "SELECT * FROM public.tbl_adresse WHERE person_id='$row->person_id'";
			if($result_adr = pg_query($conn, $qry_adr))
				while($row_adr=pg_fetch_object($result_adr))
					echo "$row_adr->plz $row_adr->ort, $row_adr->strasse<br>";
			echo "</td></tr>";
		}
		echo '<tr><td><input type="radio" name="person_id" value="0" checked onclick="disablefields(this)"></td><td>Neue Person anlegen</td></tr>';
		echo '</table>';
	}
}
//else
//	echo 'Zum Erstellen des Vorschlags bitte Geburtsdatum oder Vorname und Nachname eingeben';

?>
</td>
</tr>
</table>
</form>
</body>
</html>