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
require_once('../../include/kontakt.class.php');
require_once('../../include/adresse.class.php');
require_once('../../include/geschlecht.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user=get_uid();
$datum_obj = new datum();
loadVariables($user);

?><!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<script language="Javascript">
	function disablefields(obj)
	{
		if (obj.value==0)
			val=false;
		else
			val=true;

		document.getElementById('anrede').disabled=val;
		document.getElementById('titel').disabled=val;
		document.getElementById('titelpost').disabled=val;
		document.getElementById('nachname').disabled=val;
		document.getElementById('vorname').disabled=val;
		document.getElementById('geschlecht').disabled=val;
		document.getElementById('geburtsdatum').disabled=val;
		document.getElementById('svnr').disabled=val;
		document.getElementById('ersatzkennzeichen').disabled=val;
		if (val)
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

	function GeburtsdatumEintragen()
	{
		svnr = document.getElementById('svnr').value;
		gebdat = document.getElementById('geburtsdatum');

		if (svnr.length==10 && gebdat.value=='')
		{
			var tag = svnr.substr(4,2);
			var monat = svnr.substr(6,2);
			var jahr = svnr.substr(8,2);

			gebdat.value='19'+jahr+'-'+monat+'-'+tag;
		}
	}

	function disablefields2(val)
	{
		document.getElementById('adresse').disabled=val;
		document.getElementById('plz').disabled=val;
		document.getElementById('ort').disabled=val;
	}
	</script>
</head>
<body>
<h1>Person Anlegen</h1>
<?php
//Berechtigung pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if (!$rechte->isBerechtigt('admin')
	&& !$rechte->isBerechtigt('mitarbeiter')
	&& !$rechte->isBerechtigt('assistenz')
	&& !$rechte->isBerechtigt('basis/firma', null, 'sui'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$where = '';
$error = false;
//Parameter
$titel = (isset($_POST['titel'])?$_POST['titel']:'');
$anrede = (isset($_POST['anrede'])?$_POST['anrede']:'');
$titelpost = (isset($_POST['titelpost'])?$_POST['titelpost']:'');
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
$person_id = (isset($_POST['person_id'])?$_POST['person_id']:'');
$svnr = (isset($_POST['svnr'])?$_POST['svnr']:'');
$ersatzkennzeichen = (isset($_POST['ersatzkennzeichen'])?$_POST['ersatzkennzeichen']:'');
$ueberschreiben = (isset($_REQUEST['ueberschreiben'])?$_REQUEST['ueberschreiben']:'');
//end Parameter
$geburtsdatum_error=false;

// *** Speichern der Daten ***
if (isset($_POST['save']))
{
	//echo "Saving Data: Geburtsdatum: $geburtsdatum | Titel: $titel | Nachname: $nachname | Vorname: $vorname |
	//		Geschlecht: $geschlecht | Adresse: $adresse | Plz: $plz | Ort: $ort |
	//		Email: $email | Telefon: $telefon | Mobil: $mobil | Letzteausbildung: $letzteausbildung | ausbildungsart: $ausbildungsart |
	//		anmerkungen: $anmerkungen | studiengang_kz: $studiengang_kz | person_id: $person_id<br><br>";
	$person = new person();
	$db->db_query('BEGIN');
	//Wenn die person_id=0 dann wird eine neue Person angelegt
	//Sonst nicht
	if ($person_id == '')
		die('Es ist ein Fehler aufgetreten');

	if ($person_id=='0')
	{
		$person->new = true;
		$person->anrede = $anrede;
		$person->titelpre = $titel;
		$person->nachname = $nachname;
		$person->vorname = $vorname;
		$person->titelpost = $titelpost;
		$person->geschlecht = $geschlecht;
		$person->gebdatum = $datum_obj->formatDatum($geburtsdatum,'Y-m-d');
		$person->svnr = $svnr;
		$person->ersatzkennzeichen = $ersatzkennzeichen;
		$person->aktiv = true;
		$person->insertamum = date('Y-m-d H:i:s');
		$person->insertvon = $user;
		$person->zugangscode= uniqid();

		if ($person->save())
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
	if ($ueberschreiben!='' && !($plz=='' && $adresse=='' && $ort==''))
	{
		if ($person_id=='0')
			$ueberschreiben='Nein';

		$adr = new adresse();
		//Adresse neu anlegen
		if ($ueberschreiben=='Nein')
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
			if (isset($adr->result[0]))
			{
				//Erste Adresse laden
				if ($adr->load($adr->result[0]->adresse_id))
				{
					$adr->new = false;
					$adr->updateamum = date('Y-m-d H:i:s');
					$adr->updatevon = $user;
				}
				else
				{
					$error = true;
					$errormsg = 'Fehler beim Laden der Adresse';
				}
			}
			else
			{
				//Wenn keine Adrese vorhanden ist dann eine neue Anlegen
				$adr->new = true;
				$adr->insertamum = date('Y-m-d H:i:s');
				$adr->insertvon = $user;
			}
		}

		if (!$error)
		{
			//Adressdaten zuweisen und speichern
			$adr->person_id = $person->person_id;
			$adr->strasse = $adresse;
			$adr->plz = $plz;
			$adr->ort = $ort;
			$adr->typ = 'h';
			$adr->heimatadresse = true;
			$adr->zustelladresse = true;
			if (!$adr->save())
			{
				$error = true;
				$errormsg = $adr->errormsg;
			}
		}
	}

	//Kontaktdaten anlegen
	if (!$error)
	{
		//EMail Adresse speichern
		if ($email!='')
		{
			$kontakt = new kontakt();
			$kontakt->person_id = $person->person_id;
			$kontakt->kontakttyp = 'email';
			$kontakt->kontakt = $email;
			$kontakt->zustellung = true;
			$kontakt->insertamum = date('Y-m-d H:i:s');
			$kontakt->insertvon = $user;
			$kontakt->new = true;

			if (!$kontakt->save())
			{
				$error = true;
				$errormsg = 'Fehler beim Speichern der Email Adresse';
			}
		}
		//Telefonnummer speichern
		if ($telefon!='')
		{
			$kontakt = new kontakt();
			$kontakt->person_id = $person->person_id;
			$kontakt->kontakttyp = 'telefon';
			$kontakt->kontakt = $telefon;
			$kontakt->zustellung = true;
			$kontakt->insertamum = date('Y-m-d H:i:s');
			$kontakt->insertvon = $user;
			$kontakt->new = true;

			if (!$kontakt->save())
			{
				$error = true;
				$errormsg = 'Fehler beim Speichern der Telefonnummer';
			}
		}
		//Mobiltelefonnummer speichern
		if ($mobil!='')
		{
			$kontakt = new kontakt();
			$kontakt->person_id = $person->person_id;
			$kontakt->kontakttyp = 'mobil';
			$kontakt->kontakt = $mobil;
			$kontakt->zustellung = true;
			$kontakt->insertamum = date('Y-m-d H:i:s');
			$kontakt->insertvon = $user;
			$kontakt->new = true;

			if (!$kontakt->save())
			{
				$error = true;
				$errormsg = 'Fehler beim Speichern der Mobiltelefonnummer';
			}
		}
	}

	if (!$error)
	{
		$db->db_query('COMMIT');
		die("<script language='Javascript'>
				window.opener.StudentProjektbetreuerMenulistPersonLoad(window.opener.document.getElementById('student-projektbetreuer-menulist-person'), '$nachname');
				window.opener.MenulistSelectItemOnValue('student-projektbetreuer-menulist-person', $person->person_id);
			</script>
			<b>Person $vorname $nachname wurde erfolgreich angelegt</b><br><br><a href='personen_anlegen.php>Neue Person Anlegen</a><br>");
	}
	else
	{
		$db->db_query('ROLLBACK');
		echo '<span class="error">'.$errormsg.'</span>';
	}
}
// *** SAVE ENDE ***
if ($geburtsdatum!='')
{
	//Wenn das Datum im Format d.m.Y ist dann in Y-m-d umwandeln
	if (strpos($geburtsdatum,'.'))
	{
		if ($datum_obj->mktime_datum($geburtsdatum))
		{
			$geburtsdatum = date('Y-m-d',$datum_obj->mktime_datum($geburtsdatum));
		}
		else
		{
			$geburtsdatum_error=true;
		}
	}
	else
	{
		if (!mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$geburtsdatum))
			$geburtsdatum_error=true;
	}

	if ($geburtsdatum_error)
		echo "Format des Geburtsdatums ist ungueltig!";
}
?>
<form method='POST'>
<table width="100%">

<tr>
<td>
<!--Formularfelder-->
<table>
<?php
echo '<tr><td>Anrede</td><td><input type="text" id="anrede" name="anrede" maxlength="16" value="'.$anrede.'" /></td></tr>';
echo '<tr><td>Titel(Pre) *</td><td><input type="text" id="titel" name="titel" maxlength="64" value="'.$titel.'" /></td></tr>';
echo '<tr><td>Vorname</td><td><input type="text" id="vorname" maxlength="32" name="vorname" value="'.$vorname.'" /></td></tr>';
echo '<tr><td>Nachname *</td><td><input type="text" maxlength="64" id="nachname" name="nachname" value="'.$nachname.'" /></td></tr>';
echo '<tr><td>Titel(Post)</td><td><input type="text" id="titelpost" name="titelpost" maxlength="64" value="'.$titelpost.'" /></td></tr>';
echo '<tr><td>Geschlecht *</td><td><SELECT id="geschlecht" name="geschlecht">';
$geschlecht_obj = new geschlecht();
$geschlecht_obj->getAll();
foreach ($geschlecht_obj->result as $row_geschlecht)
{
	if ($geschlecht == $row_geschlecht->geschlecht)
		$selected = 'selected';
	else
		$selected = '';

	echo '<OPTION value="'.$row_geschlecht->geschlecht.'" '.$selected.'>'.$row_geschlecht->bezeichnung_mehrsprachig_arr[DEFAULT_LANGUAGE].'</OPTION>';
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>SVNR</td><td><input type="text" id="svnr" size="10" maxlength="10" name="svnr" value="'.$svnr.'" onblur="GeburtsdatumEintragen()" /></td></tr>';
echo '<tr><td>Ersatzkennzeichen</td><td><input type="text" id="ersatzkennzeichen" size="10" maxlength="10" name="ersatzkennzeichen" value="'.$ersatzkennzeichen.'" /></td></tr>';
echo '<tr><td>Geburtsdatum</td><td><input type="text" id="geburtsdatum" size="10" maxlength="10" name="geburtsdatum" value="'.$geburtsdatum.'" /> (Format dd.mm.JJJJ)</td></tr>';
echo '<tr><td colspan="2"><fieldset><legend>Adresse</legend><table>';
echo '<tr><td>Adresse</td><td><input type="text" id="adresse" maxlength="256" name="adresse" value="'.$adresse.'" /></td></tr>';
echo '<tr><td>Postleitzahl</td><td><input type="text" maxlength="16" id="plz" name="plz" value="'.$plz.'" /></td></tr>';
echo '<tr><td>Ort</td><td><input type="text" id="ort" maxlength="256" name="ort" value="'.$ort.'" /></td></tr>';
echo '</table>';
echo '<div style="display: none;" id="ueb1"><input type="radio" id="ueberschreiben1" name="ueberschreiben" value="Ja" onclick="disablefields2(false)">Bestehende Adresse überschreiben</div>';
echo '<div style="display: none;" id="ueb2"><input type="radio" id="ueberschreiben2" name="ueberschreiben" value="Nein" onclick="disablefields2(false)" checked>Adresse hinzufügen</div>';
echo '<div style="display: none;" id="ueb3"><input type="radio" id="ueberschreiben3" name="ueberschreiben" value="" onclick="disablefields2(true)">Adresse nicht anlegen</div>';
echo '</fieldset></td></tr>';
echo '<tr><td>EMail</td><td><input type="text" id="email" maxlength="128" name="email" value="'.$email.'" /></td></tr>';
echo '<tr><td>Telefon</td><td><input type="text" id="telefon" maxlength="128" name="telefon" value="'.$telefon.'" /></td></tr>';
echo '<tr><td>Mobil</td><td><input type="text" id="mobil" maxlength="128" name="mobil" value="'.$mobil.'" /></td></tr>';
echo '<tr><td></td><td>';

if (($geburtsdatum=='' && $vorname=='' && $nachname=='') || $geburtsdatum_error)
	echo '<input type="submit" name="showagain" value="Vorschlag laden">';
else
	echo '<input type="submit" name="save" value="Speichern">';

echo '</td></tr>';
echo '</table>';
echo '
<br><br>
Felder die mit einem * gekennzeichnet sind müssen ausgefüllt werden!
';
echo '</td>';
echo '<td valign="top">';

//Vorschlaege

$where = '';
//Vorschlaege laden
if ($geburtsdatum!='')
{
	if (mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$geburtsdatum))
	{
		$where = " gebdatum=".$db->db_add_param($geburtsdatum);
	}
}

if ($vorname!='' && $nachname!='')
{
	if ($where!='')
		$where.=' OR';
	$where.=" (LOWER(vorname)=LOWER(".$db->db_add_param($vorname).") AND LOWER(nachname)=LOWER(".$db->db_add_param($nachname)."))";
}
elseif ($nachname!='')
{
	if ($where!='')
		$where.=' OR';
	$where.=" LOWER(nachname)=LOWER(".$db->db_add_param($nachname).")";
}

if ($where!='')
{
	$qry = "SELECT * FROM public.tbl_person WHERE $where ORDER BY nachname, vorname, gebdatum";

	if ($result = $db->db_query($qry))
	{
		echo '<table>
				<tr>
					<th></th>
					<th>Nachname</th>
					<th>Vorname</th>
					<th>GebDatum</th>
					<th>SVNR</th>
					<th>Geschlecht</th>
					<th>Adresse</th>
					<th>Status</th>
					<th>Details</th>
				</tr>';
		while($row = $db->db_fetch_object($result))
		{
			$status = '';
			$qry_stati = "SELECT 'Mitarbeiter' as rolle FROM campus.vw_mitarbeiter WHERE person_id='$row->person_id'
							UNION
							SELECT (get_rolle_prestudent(prestudent_id, null) || ' ' || UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz)) as rolle FROM public.tbl_prestudent JOIN public.tbl_studiengang USING(studiengang_kz) WHERE person_id='$row->person_id'
							UNION
							SELECT 'PreInteressent' as rolle FROM public.tbl_preinteressent WHERE person_id='$row->person_id'";
			if ($result_stati = $db->db_query($qry_stati))
			{
				while($row_stati=$db->db_fetch_object($result_stati))
				{
					$status.=$row_stati->rolle.', ';
				}
			}
			$status = mb_substr($status, 0, mb_strlen($status)-2);

			echo '<tr valign="top"><td><input type="radio" name="person_id" value="'.$row->person_id.'" onclick="disablefields(this)"></td><td>'."$row->nachname</td><td>$row->vorname</td><td>$row->gebdatum</td><td>$row->svnr</td><td>".($row->geschlecht=='m'?'männlich':'weiblich')."</td><td>";
			$qry_adr = "SELECT * FROM public.tbl_adresse WHERE person_id='$row->person_id'";
			if ($result_adr = $db->db_query($qry_adr))
				while($row_adr=$db->db_fetch_object($result_adr))
					echo "$row_adr->plz $row_adr->ort, $row_adr->strasse<br>";
			echo "<td>$status</td>";
			echo '<td><a href="personendetails.php?id='.$row->person_id.'" target="_blank">Details</a></td>';
			echo "</td></tr>";
		}
		echo '<tr><td><input type="radio" name="person_id" value="0" checked onclick="disablefields(this)"></td><td>Neue Person anlegen</td></tr>';
		echo '</table>';
	}
}

?>
</td>
</tr>
</table>
</form>
</body>
</html>
