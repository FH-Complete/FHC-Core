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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/person.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/kontakt.class.php');
require_once('../../include/adresse.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/preinteressent.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/nation.class.php');
require_once('../../include/sprache.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user=get_uid();
$datum_obj = new datum();

loadVariables($user);

function getGemeindeDropDown($postleitzahl)
{
	global $db, $_REQUEST, $gemeinde;
	$found=false;
	$firstentry='';
	$gemeinde_x = (isset($_REQUEST['gemeinde'])?$_REQUEST['gemeinde']:'');
	$qry = "SELECT distinct name FROM bis.tbl_gemeinde WHERE plz='".addslashes($postleitzahl)."'";
	echo '<SELECT id="gemeinde" name="gemeinde" onchange="loadOrtData()">';
	if(is_numeric($postleitzahl) && $postleitzahl<10000)
	{
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				if($firstentry=='')
					$firstentry=$row->name;
				if($gemeinde_x=='')
					$gemeinde_x=$row->name;
				
				if($row->name==$gemeinde_x)
				{
					$selected='selected';
					$found=true;
				}
				else
					$selected='';
				echo "<option value='$row->name' $selected>$row->name</option>";
			}
		}
	}
	
	echo '</SELECT>';
	if(!$found && (isset($importort) && $importort!=''))
	{
		echo $importort;
	}
	$gemeinde = $gemeinde_x;
}

if(isset($_GET['type']) && $_GET['type']=='getgemeindecontent' && isset($_GET['plz']))
{
	header('Content-Type: text/html; charset=UTF-8');

	echo getGemeindeDropDown($_GET['plz']);
	exit;
}

function getOrtDropDown($postleitzahl, $gemeindename)
{
	global $db, $_REQUEST;
	$ort = (isset($_REQUEST['ort'])?$_REQUEST['ort']:'');
	$qry = "SELECT distinct ortschaftsname FROM bis.tbl_gemeinde 
			WHERE plz='".addslashes($postleitzahl)."' AND name='".addslashes($gemeindename)."'";
	echo '<SELECT id="ort" name="ort">';
	if(is_numeric($postleitzahl) && $postleitzahl<10000)
	{
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				if($row->ortschaftsname==$ort)
					$selected='selected';
				else 
					$selected='';
				echo "<option value='$row->ortschaftsname' $selected>$row->ortschaftsname</option>";
			}
		}
	}
	
	echo '</SELECT>';
}
if(isset($_GET['type']) && $_GET['type']=='getortcontent' && isset($_GET['plz']) && isset($_GET['gemeinde']))
{
	header('Content-Type: text/html; charset=UTF-8');
	
	echo getOrtDropDown($_GET['plz'], $_GET['gemeinde']);
	exit;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/cis.css" rel="stylesheet" type="text/css">
<script language="Javascript">
function disablefields(obj)
{
	if(obj.value==0)
		val=false;
	else
		val=true;

	document.getElementById('titel').disabled=val;
	document.getElementById('titelpost').disabled=val;
	document.getElementById('nachname').disabled=val;
	document.getElementById('vorname').disabled=val;
	document.getElementById('geschlecht').disabled=val;
	document.getElementById('geburtsdatum').disabled=val;
	document.getElementById('svnr').disabled=val;
	document.getElementById('ersatzkennzeichen').disabled=val;
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

function GeburtsdatumEintragen()
{
	svnr = document.getElementById('svnr').value;
	gebdat = document.getElementById('geburtsdatum');
	
	if(svnr.length==10 && gebdat.value=='')
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

function checkschulid(schuleid)
{
	if(schuleid!='')
	{
		dd = document.getElementById('schuledd')
		//preufen ob die id im DD vorhanden ist
		myoptions = dd.getElementsByTagName('option');
		id='';
		for(i=0;i<myoptions.length;i++)
		{

			node = myoptions[i];
			if(node.value==schuleid)
				id=schuleid;
		}
		
		document.getElementById('schuledd').value=id;
	}
	return true;
}

// **************************************
// * XMLHttpRequest Objekt erzeugen
// **************************************
var anfrage = null;

function erzeugeAnfrage()
{
	try
	{
		anfrage = new XMLHttpRequest();
	}
	catch (versuchmicrosoft)
	{
		try
		{
			anfrage = new ActiveXObject("Msxml12.XMLHTTP");
		}
		catch (anderesmicrosoft)
		{
			try
			{
				anfrage = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (fehlschlag)
			{
				anfrage = null;
            }
        }
    }
	if (anfrage == null)
		alert("Fehler beim Erstellen des Anfrageobjekts!");
}

//Gemeinde DropDown holen wenn Nation Oesterreich
function loadGemeindeData()
{
	if(document.getElementById('adresse_nation').value=='A')
	{
		anfrage=null;
		//Request erzeugen und die Note speichern
		erzeugeAnfrage(); 
	    var jetzt = new Date();
		var ts = jetzt.getTime();
		var plz = document.getElementById('plz').value;
	    var url= '<?php echo $_SERVER['PHP_SELF']."?type=getgemeindecontent"?>';
	    url += '&plz='+plz+"&"+ts;
	    anfrage.open("GET", url, true);
	    anfrage.onreadystatechange = setGemeindeData;
	    anfrage.send(null);
	    document.getElementById('adresse-gemeinde-textfeld').style.display='none';
		document.getElementById('adresse-ort-textfeld').style.display='none';
	}
	else
	{
		/*
		document.getElementById('adresse-gemeinde-textfeld').type='text';
		document.getElementById('adresse-ort-textfeld').type='text';
		*/
		document.getElementById('adresse-gemeinde-textfeld').style.display='block';
		document.getElementById('adresse-ort-textfeld').style.display='block';
		document.getElementById('gemeindediv').innerHTML='';
		document.getElementById('ortdiv').innerHTML='';
	}
}

function setGemeindeData()
{
	if (anfrage.readyState == 4)
	{
		if (anfrage.status == 200) 
		{
			var resp = anfrage.responseText;
            var gemeindediv = document.getElementById('gemeindediv');
			gemeindediv.innerHTML = resp;
			loadOrtData();
        } 
        else alert("Request status:" + anfrage.status);
    }
}

function loadOrtData()
{
	if(document.getElementById('gemeinde'))
	{
		anfrage=null;
		//Request erzeugen und die Note speichern
		erzeugeAnfrage(); 
	    var jetzt = new Date();
		var ts = jetzt.getTime();
		var plz = document.getElementById('plz').value;
		var gemeinde = document.getElementById('gemeinde').value;
	    var url= '<?php echo $_SERVER['PHP_SELF']."?type=getortcontent"?>';
	    url += '&plz='+plz+"&gemeinde="+encodeURIComponent(gemeinde)+"&"+ts;
	    anfrage.open("GET", url, true);
	    anfrage.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	    anfrage.onreadystatechange = setOrtData;
	    anfrage.send(null);
	}
}

function setOrtData()
{
	if (anfrage.readyState == 4)
	{
		if (anfrage.status == 200) 
		{
			var resp = anfrage.responseText;
            var ortdiv = document.getElementById('ortdiv');
			ortdiv.innerHTML = resp;
        } 
        else alert("Request status:" + anfrage.status);
    }
}	
</script>
</head>
<body>
<h1>Preinteressent Anlegen</h1>
<?php
//Berechtigung pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('mitarbeiter') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('preinteressent'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$where = '';
$error = false;
$importort='';
//Parameter
$titel = (isset($_REQUEST['titel'])?$_REQUEST['titel']:'');
$titelpost = (isset($_REQUEST['titelpost'])?$_REQUEST['titelpost']:'');
$nachname = (isset($_REQUEST['nachname'])?$_REQUEST['nachname']:'');
$vorname = (isset($_REQUEST['vorname'])?$_REQUEST['vorname']:'');
$vornamen = (isset($_REQUEST['vornamen'])?$_REQUEST['vornamen']:'');
$geschlecht = (isset($_REQUEST['geschlecht'])?$_REQUEST['geschlecht']:'');
$geburtsdatum = (isset($_REQUEST['geburtsdatum'])?$_REQUEST['geburtsdatum']:'');
$geburtsnation = (isset($_REQUEST['geburtsnation'])?$_REQUEST['geburtsnation']:'A');
$gebort = (isset($_REQUEST['gebort'])?$_REQUEST['gebort']:'');
$sprache = (isset($_REQUEST['sprache'])?$_REQUEST['sprache']:'German');
$nation = (isset($_REQUEST['nation'])?$_REQUEST['nation']:'A');
$adresse = (isset($_REQUEST['adresse'])?$_REQUEST['adresse']:'');
$adresse_nation = (isset($_REQUEST['adresse_nation'])?$_REQUEST['adresse_nation']:'A');
$plz = (isset($_REQUEST['plz'])?$_REQUEST['plz']:'');
if($adresse_nation=='A')
{
	$ort = (isset($_REQUEST['ort'])?$_REQUEST['ort']:'');
	$gemeinde = (isset($_REQUEST['gemeinde'])?$_REQUEST['gemeinde']:'');
}
else 
{
	$ort = (isset($_REQUEST['ort_txt'])?$_REQUEST['ort_txt']:'');
	$gemeinde = (isset($_REQUEST['gemeinde_txt'])?$_REQUEST['gemeinde_txt']:'');
}
//wenn die Gemeinde leer ist und im Ort etwas steht
//dann umdrehen (Das passiert wenn die Daten aus dem Mail von der www importiert werden)
if($gemeinde=='' && $ort!='' && !isset($_REQUEST['gemeinde_txt']))
{
	$importort=$ort;
	$gemeinde=$ort;
	$ort='';
}

$email = (isset($_REQUEST['email'])?$_REQUEST['email']:'');
$telefon = (isset($_REQUEST['telefon'])?$_REQUEST['telefon']:'');
$mobil = (isset($_REQUEST['mobil'])?$_REQUEST['mobil']:'');
$person_id = (isset($_REQUEST['person_id'])?$_REQUEST['person_id']:'');
$svnr = (isset($_REQUEST['svnr'])?$_REQUEST['svnr']:'');
$ersatzkennzeichen = (isset($_REQUEST['ersatzkennzeichen'])?$_REQUEST['ersatzkennzeichen']:'');
$ueberschreiben = (isset($_REQUEST['ueberschreiben'])?$_REQUEST['ueberschreiben']:'');

$anmerkung_preint = (isset($_REQUEST['anmerkung_preint'])?$_REQUEST['anmerkung_preint']:'');

$stsem = new studiensemester();
$stsem->getNextStudiensemester('WS');
$studiensemester_kurzbz = (isset($_REQUEST['studiensemester_kurzbz'])?$_REQUEST['studiensemester_kurzbz']:$stsem->studiensemester_kurzbz);

if(isset($_REQUEST['schule_id']) && $_REQUEST['schule_id']!='')
{
	$schule = $_REQUEST['schule_id'];
}
elseif(isset($_POST['schule']))
{
	$schule = $_POST['schule'];
}
else 
	$schule='';
//end Parameter
$geburtsdatum_error=false;

// *** Speichern der Daten ***
if(isset($_POST['save']))
{
	//echo "Saving Data: Geburtsdatum: $geburtsdatum | Titel: $titel | Nachname: $nachname | Vorname: $vorname |
	//		Geschlecht: $geschlecht | Adresse: $adresse | Plz: $plz | Ort: $ort |
	//		Email: $email | Telefon: $telefon | Mobil: $mobil | Letzteausbildung: $letzteausbildung | ausbildungsart: $ausbildungsart |
	//		anmerkungen: $anmerkungen | studiengang_kz: $studiengang_kz | person_id: $person_id<br><br>";
	$person = new person();
	$db->db_query('BEGIN');
	//Wenn die person_id=0 dann wird eine neue Person angelegt
	//Sonst nicht
	if($person_id=='0')
	{
		$person->new = true;
		$person->titelpre = $titel;
		$person->nachname = $nachname;
		$person->vorname = $vorname;
		$person->vornamen = $vornamen;
		$person->titelpost = $titelpost;
		$person->geschlecht = $geschlecht;
		$person->gebdatum = $datum_obj->formatDatum($geburtsdatum,'Y-m-d');
		$person->staatsbuergerschaft = $nation;
		$person->svnr = $svnr;
		$person->ersatzkennzeichen = $ersatzkennzeichen;
		$person->aktiv = true;
		$person->insertamum = date('Y-m-d H:i:s');
		$person->insertvon = $user;
        $person->zugangscode= uniqid();
		$person->geburtsnation = $geburtsnation;
		$person->sprache = $sprache;
		$person->gebort = $gebort;
        
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
	else 
	{
		if(!$person->load($person_id))
		{
			$error = true;
			$errormsg = "Person konnte nicht geladen werden";
		}
	}
	
	//Adresse anlegen
	if($ueberschreiben!='' && !($plz=='' && $adresse=='' && $ort==''))
	{
		if($person_id=='0')
			$ueberschreiben='Nein';

		$adr = new adresse();
		//Adresse neu anlegen
		if($ueberschreiben=='Nein')
		{
			$adr->new = true;
			$adr->insertamum = date('Y-m-d H:i:s');
			$adr->insertvon = $user;
			$adr->nation = $adresse_nation;
			//Wenn die Person neu angelegt wird, dann ist die neue Adresse die Heimatadresse
			//sonst nicht
			if($person_id=='0')
				$adr->heimatadresse = true;
			else
				$adr->heimatadresse = false;
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
					$errormsg = 'Fehler beim Laden der Adresse';
				}
			}
			else
			{
				//Wenn keine Adrese vorhanden ist dann eine neue Anlegen
				$adr->new = true;
				$adr->insertamum = date('Y-m-d H:i:s');
				$adr->insertvon = $user;
				$adr->nation = $adresse_nation;
				$adr->heimatadresse = true;
			}
		}

		if(!$error)
		{
			//Adressdaten zuweisen und speichern
			$adr->person_id = $person->person_id;
			$adr->strasse = $adresse;
			$adr->plz = $plz;
			$adr->ort = $ort;
			$adr->gemeinde = $gemeinde;
			$adr->typ = 'h';
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
			$kontakt = new kontakt();
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
			$kontakt = new kontakt();
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
			$kontakt = new kontakt();
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

	if(!$error)
	{
		$preinteressent = new preinteressent();
		
		$preinteressent->person_id = $person->person_id;
		$preinteressent->studiensemester_kurzbz = $studiensemester_kurzbz;
		$preinteressent->aufmerksamdurch_kurzbz = 'k.A.';
		$preinteressent->erfassungsdatum = date('Y-m-d');
		$preinteressent->firma_id = $schule;
		$preinteressent->insertamum = date('Y-m-d H:i:s');
		$preinteressent->insertvon = $user;
		$preinteressent->anmerkung = $anmerkung_preint;
		
		if(!$preinteressent->save(true))
		{
			$error = true;
			$errormsg = "Fehler beim Anlegen des Preinteressenten: $preinteressent->errormsg";
		}
		
		if(!$error)
		{
			foreach ($_REQUEST as $key=>$param)
			{
				if(substr($key,0,4)=='stg_')
				{
					$stg_kz = substr($key, 4);
					$zuordnung = new preinteressent();
					$zuordnung->preinteressent_id = $preinteressent->preinteressent_id;
					$zuordnung->studiengang_kz = $stg_kz;
					$zuordnung->prioritaet = 1;
					$zuordnung->insertamum = date('Y-m-d H:i:s');
					$zuordnung->insertvon = $user;
					
					if(!$zuordnung->saveZuordnung(true))
					{
						$errormsg.="Fehler beim Speichern der Zuordnung zum Studiengang $stg_kz";
						$error=true;
					}
				}
			}
		}
	}
	if(!$error)
	{
		$db->db_query('COMMIT');
		/*<script language='Javascript'>
				window.opener.StudentProjektbetreuerMenulistPersonLoad(window.opener.document.getElementById('student-projektbetreuer-menulist-person'), '$nachname');
				window.opener.MenulistSelectItemOnValue('student-projektbetreuer-menulist-person', $person->person_id);
			</script>*/
		die("<b>Person $vorname $nachname wurde erfolgreich angelegt</b><br><br><a href='preinteressent_anlegen.php?schule_id=".$schule."'>Neuen Preinteressenten anlegen</a><br>");
	}
	else
	{
		$db->db_query('ROLLBACK');
		echo '<span class="error">'.$errormsg.'</span>';
	}
}
// *** SAVE ENDE ***
if($geburtsdatum!='')
{
	//Wenn das Datum im Format d.m.Y ist dann in Y-m-d umwandeln
	if(strpos($geburtsdatum,'.'))
	{
		if($datum_obj->mktime_datum($geburtsdatum))
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
		if(!mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$geburtsdatum))
			$geburtsdatum_error=true;
	}
	
	if($geburtsdatum_error)
		echo "Format des Geburtsdatums ist ungueltig!";
}
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='POST'>
<table width="100%">

<tr>
<td valign="top">
<!--Formularfelder-->
<table>
<?php
echo '<tr><td>Titel(Pre)</td><td><input type="text" id="titel" name="titel" maxlength="64" value="'.$titel.'" /></td></tr>';
echo '<tr><td>Vorname</td><td><input type="text" id="vorname" maxlength="32" name="vorname" value="'.$vorname.'" /></td></tr>';
echo '<tr><td>Vornamen</td><td><input type="text" id="vornamen" maxlength="32" name="vornamen" value="'.$vornamen.'" /></td></tr>';
echo '<tr><td>Nachname *</td><td><input type="text" maxlength="64" id="nachname" name="nachname" value="'.$nachname.'" /></td></tr>';
echo '<tr><td>Titel(Post)</td><td><input type="text" id="titelpost" name="titelpost" maxlength="64" value="'.$titelpost.'" /></td></tr>';
echo '<tr><td>Geschlecht *</td><td><SELECT id="geschlecht" name="geschlecht">';
echo '<OPTION value="m" '.($geschlecht=='m'?'selected':'').'>m&auml;nnlich</OPTION>';
echo '<OPTION value="w" '.($geschlecht=='w'?'selected':'').'>weiblich</OPTION>';
echo '<OPTION value="u" '.($geschlecht=='u'?'selected':'').'>unbekannt</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Staatsbuergerschaft</td><td><SELECT name="nation">';
$qry = "SELECT nation_code, kurztext FROM bis.tbl_nation ORDER BY kurztext";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($row->nation_code==$nation)
			$selected='selected';
		else 
			$selected='';
		
		echo "<option value='$row->nation_code' $selected>$row->kurztext</option>";
	}
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>SVNR</td><td><input type="text" id="svnr" size="10" maxlength="10" name="svnr" value="'.$svnr.'" onblur="GeburtsdatumEintragen()" /></td></tr>';
echo '<tr><td>Ersatzkennzeichen</td><td><input type="text" id="ersatzkennzeichen" size="10" maxlength="10" name="ersatzkennzeichen" value="'.$ersatzkennzeichen.'" /></td></tr>';
echo '<tr><td>Geburtsdatum</td><td><input type="text" id="geburtsdatum" size="10" maxlength="10" name="geburtsdatum" value="'.$geburtsdatum.'" /> (Format dd.mm.JJJJ)</td></tr>';
echo '<tr><td>Geburtsort</td><td><input type="text" id="gebort" size="30" maxlength="255" name="gebort" value="'.$gebort.'" /></td></tr>';
echo '<tr><td>Geburtsnation</td><td><SELECT name="geburtsnation">';
$qry = "SELECT nation_code, kurztext FROM bis.tbl_nation ORDER BY kurztext";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($row->nation_code==$geburtsnation)
			$selected='selected';
		else
			$selected='';

		echo "<option value='$row->nation_code' $selected>$row->kurztext</option>";
	}
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Sprache</td><td><SELECT name="sprache">';
$sprache_obj = new sprache();
$sprache_obj->getAll();
foreach($sprache_obj->result as $row)
{
	if($row->sprache==$sprache)
		$selected='selected';
	else
		$selected='';

	echo "<option value='$row->sprache' $selected>".$row->bezeichnung_arr['German']."</option>";
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td colspan="2"><fieldset><legend>Adresse</legend><table>';
echo '<tr><td>Nation</td><td><SELECT name="adresse_nation" id="adresse_nation" onchange="loadGemeindeData()">';
$nation =  new nation();
$nation->getAll();
foreach ($nation->nation as $row)
{
	if($row->code==$adresse_nation)
		$selected='selected';
	else 
		$selected='';
	echo "<option value='$row->code' $selected>$row->langtext</option>";
}
echo '</SELECT></td></tr>';
echo '<tr><td>Postleitzahl</td><td><input type="text" size="5" maxlength="16" id="plz" name="plz" value="'.$plz.'" onblur="loadGemeindeData()" /></td></tr>';
echo '<tr><td>Adresse</td><td><input type="text" id="adresse" maxlength="256"  size="40" name="adresse" value="'.$adresse.'" /></td></tr>';
echo '<tr><td>Gemeinde</td><td><div id="gemeindediv">';
//wenn die Nation Oesterreich ist, dann wird ein DropDown fuer Gemeinde und Ort angezeigt.
//wenn die Nation nicht Oesterreich ist, werden nur textfelder angezeigt
if($adresse_nation=='A' && $plz!='')
{
	echo getGemeindeDropDown($plz);
}
else 
{
	echo '<font color="gray">Bitte zuerst eine Postleitzahl eintragen</font>';
}	

//wenn der Ort per EMail-Import von der www kommt und der Ort in der Gemeindetabelle
//nicht gefunden wird, dann wird der Ort in Klammer neben dem DropDown angezeigt
if($importort!='' && $gemeinde!=$importort)
	echo ' ( '.$importort.' )';

echo '</div><input type="text" style="display:'.($adresse_nation=='A'?'none':'block').';" id="adresse-gemeinde-textfeld" maxlength="256" name="gemeinde_txt" value="'.$gemeinde.'" />';

echo '</td></tr>';
echo '<tr><td>Ort</td><td><div id="ortdiv">';
if($adresse_nation=='A' && $plz!='')
{
	echo getOrtDropDown($plz, $gemeinde);
}
echo '</div><input type="text" style="display: '.($adresse_nation=='A'?'none':'block').';" id="adresse-ort-textfeld" maxlength="256" name="ort_txt" value="'.$ort.'"/></td></tr>';

echo '</table>';
echo '<div style="display: none;" id="ueb1"><input type="radio" id="ueberschreiben1" name="ueberschreiben" value="Ja" onclick="disablefields2(false)">Bestehende Adresse überschreiben</div>';
echo '<div style="display: none;" id="ueb2"><input type="radio" id="ueberschreiben2" name="ueberschreiben" value="Nein" onclick="disablefields2(false)" checked>Adresse hinzufügen</div>';
echo '<div style="display: none;" id="ueb3"><input type="radio" id="ueberschreiben3" name="ueberschreiben" value="" onclick="disablefields2(true)">Adresse nicht anlegen</div>';
echo '</fieldset></td></tr>';
echo '<tr><td>EMail</td><td><input type="text" id="email" maxlength="128" name="email" value="'.$email.'" /></td></tr>';
echo '<tr><td>Telefon</td><td><input type="text" id="telefon" maxlength="128" name="telefon" value="'.$telefon.'" /></td></tr>';
echo '<tr><td>Mobil</td><td><input type="text" id="mobil" maxlength="128" name="mobil" value="'.$mobil.'" /></td></tr>';
//Preinteressentdaten
echo '<tr><td>Studiensemester: </td><td><SELECT name="studiensemester_kurzbz">';
$stsem = new studiensemester();

$stsem->getAll();
if($studiensemester_kurzbz=='')
	$selected='selected';
else 
	$selected='';
echo "<option value='' $selected>-- offen --</option>";
foreach ($stsem->studiensemester as $row) 
{
	if($row->studiensemester_kurzbz==$studiensemester_kurzbz)
		$selected='selected';
	else 
		$selected='';
		
	echo "<option value='$row->studiensemester_kurzbz' $selected>$row->studiensemester_kurzbz</option>";
}
echo '</SELECT></td></tr>';
echo '<tr><td>Schule: </td><td><SELECT id="schuledd" name="schule" onchange="document.getElementById(\'schule_id\').value=this.value">';
echo "<option value=''>-- keine Auswahl --</option>";
$qry = "SELECT plz, ort, strasse, tbl_firma.name, firma_id 
		FROM public.tbl_firma LEFT JOIN public.tbl_adresse USING(firma_id) 
		WHERE schule ORDER BY plz, name";
//bei namen die laenger als 40 zeichen sind wird ein teil aus der mitte
//herausgeschnitten damit das DD nicht zu gross wird
function shortname($name)
{
	if(strlen($name)>40)
	{
		return mb_substr($name, 0, 20).' ... '.mb_substr($name, mb_strlen($name)-20);
	}
	else 
		return $name;
}

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($schule==$row->firma_id)
			$selected='selected';
		else 
			$selected='';
			
		echo "<option value='$row->firma_id' title='$row->name' $selected>$row->plz $row->ort - ".shortname($row->name)." ( $row->firma_id )</option>";
	}
}
echo '</SELECT></td></tr>';
echo '</tr><td>Schule ID:</td><td><input type="text" size="3" name="schule_id" id="schule_id" value="'.$schule.'" onkeyup="checkschulid(this.value)"></td></tr>';
echo '</tr><td>Anmerkung:</td><td><textarea name="anmerkung_preint" id="anmerkung_preint" cols="40" rows="3">'.$anmerkung_preint.'</textarea></td></tr>';
echo '<tr><td></td><td>';
if(($vorname!='' && $geburtsdatum=='' && $nachname=='') 
   || ($vorname=='' && $geburtsdatum=='' && $nachname!='') 
   || ($nachname=='' && $geburtsdatum=='')
   || ($geburtsdatum=='' && $nachname=='' && $vorname=='') 
   || $geburtsdatum_error)
	echo '<input type="submit" name="showagain" value="Vorschlag laden">';
else
{
	echo '<input type="submit" name="showagain" value="Vorschlag laden">';
	echo '<input type="submit" name="save" value="Speichern">';
}
	

echo '</td></tr>';
echo '</table>';
echo '
<br><br>
Felder die mit einem * gekennzeichnet sind müssen ausgefüllt werden!
';
echo '</td>';
echo '<td valign="top">';

//Vorschlaege

//Vorschlaege laden
if($geburtsdatum!='')
{		
	if(mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$geburtsdatum))
	{
		$where = " gebdatum='".$geburtsdatum."'";
	}
}

if($vorname!='' && $nachname!='')
{
	if($where!='')
		$where.=' OR';
	$where.=" (LOWER(vorname)=LOWER('".$vorname."') AND LOWER(nachname)=LOWER('".$nachname."'))";
}

/*
if($vorname=='' && $nachname!='')
{
	if($where!='')
		$where.=' OR';
	$where.=" (LOWER(nachname)=LOWER('".$nachname."'))";
}
*/

if($where!='')
{
	$qry = "SELECT * FROM public.tbl_person WHERE $where ORDER BY nachname, vorname, gebdatum";
	
	if($result = $db->db_query($qry))
	{
		$stg_obj = new studiengang();
		$stg_obj->getAll('typ, kurzbz', false);
		
		echo '<table><tr><th></th><th>Nachname</th><th>Vorname</th><th>GebDatum</th><th>SVNR</th><th>Geschlecht</th><th>Adresse</th><th>Status</th><th>Details</th></tr>';
		while($row = $db->db_fetch_object($result))
		{
			$status = '';
			
			$qry_stati = "SELECT 'Mitarbeiter' as rolle FROM campus.vw_mitarbeiter WHERE person_id='$row->person_id'
							UNION
							SELECT (get_rolle_prestudent(prestudent_id, null) || ' ' || UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz)) as rolle FROM public.tbl_prestudent JOIN public.tbl_studiengang USING(studiengang_kz) WHERE person_id='$row->person_id'
							UNION
							SELECT 'PreInteressent' as rolle FROM public.tbl_preinteressent WHERE person_id='$row->person_id'";
			if($result_stati = $db->db_query($qry_stati))
			{
				while($row_stati=$db->db_fetch_object($result_stati))
				{
					if (!empty($status))
							$status.=', ';
					$status.=trim($row_stati->rolle);
				}
			}
			$status = mb_substr($status, 0, mb_strlen($status)-2);
			
			echo '<tr valign="top"><td><input type="radio" name="person_id" value="'.$row->person_id.'" onclick="disablefields(this)"></td><td>'."$row->nachname</td><td>$row->vorname</td><td>$row->gebdatum</td><td>$row->svnr</td><td>".($row->geschlecht=='m'?'männlich':'weiblich')."</td><td>";
			$qry_adr = "SELECT * FROM public.tbl_adresse WHERE person_id='$row->person_id'";
			if($result_adr = $db->db_query($qry_adr))
				while($row_adr=$db->db_fetch_object($result_adr))
					echo "$row_adr->plz $row_adr->ort, $row_adr->strasse<br>";
			echo '</td>';
			echo "<td>$status</td>";
			echo '<td><a href="personendetails.php?id='.$row->person_id.'" target="_blank">Details</a></td>';
			echo "</tr>";
		}
		echo '<tr><td><input type="radio" name="person_id" value="0" checked onclick="disablefields(this)"></td><td>Neue Person anlegen</td></tr>';
		echo '</table>';
		echo '<hr>';
		//Studiengaenge anzeigen
		$qry = "SELECT *, UPPER(typ::varchar(1) || kurzbz) as kuerzel FROM public.tbl_studiengang 
				WHERE aktiv AND typ in('b','m') ORDER BY typ, bezeichnung";
		if($result = $db->db_query($qry))
		{
			echo '<table><tr><td valign="top">';
			echo '<table>';
			$lasttyp='';
			while($row = $db->db_fetch_object($result))
			{
				if($lasttyp!=$row->typ)
				{
					if($lasttyp!='')
						echo '</table></td><td><table>';	
					$lasttyp = $row->typ;
				}
				if(isset($_REQUEST['stg_'.$row->studiengang_kz]))
					$checked='checked';
				else
					$checked='';
				echo "<tr><td><input type='checkbox' name='stg_$row->studiengang_kz' $checked></td><td>$row->kuerzel</td><td>$row->bezeichnung</td></tr>";
			}
			echo '</table></td></tr></table>';
		}
	}
}
else
	echo '<b>Zum Erstellen des Vorschlags bitte Geburtsdatum oder Vorname und Nachname eingeben</b>';

?>
</td>
</tr>
</table>
</form>
</body>
</html>