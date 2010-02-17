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

require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/adresse.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/nation.class.php');
require_once('../../../include/'.EXT_FKT_PATH.'/generateuid.inc.php');

$db = new basis_db();
$user=get_uid();
$datum_obj = new datum();

loadVariables($user);

function getGemeindeDropDown($postleitzahl)
{
	global $_REQUEST, $gemeinde;
	$db = new basis_db();
	
	$found=false;
	$firstentry='';
	$gemeinde_x = (isset($_REQUEST['gemeinde'])?$_REQUEST['gemeinde']:'');
	$qry = "SELECT distinct name FROM bis.tbl_gemeinde WHERE plz='".addslashes($postleitzahl)."'";
	echo '<SELECT id="gemeinde" name="gemeinde" onchange="loadOrtData()">';
	if(is_numeric($postleitzahl) && $postleitzahl<10000)
	{
		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
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
	global $_REQUEST;
	$db = new basis_db();
	
	$ort = (isset($_REQUEST['ort'])?$_REQUEST['ort']:'');
	$qry = "SELECT distinct ortschaftsname FROM bis.tbl_gemeinde 
			WHERE plz='".addslashes($postleitzahl)."' AND name='".addslashes($gemeindename)."'";
	echo '<SELECT id="ort" name="ort">';
	if(is_numeric($postleitzahl) && $postleitzahl<10000)
	{
		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
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
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
<script language="Javascript">
function disablefields(obj)
{
	if(obj.value==0)
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
	//document.getElementById('adresse').disabled=val;
	//document.getElementById('plz').disabled=val;
	//document.getElementById('ort').disabled=val;
	if(val)
	{
		document.getElementById('ueb1').style.display = 'block';
		document.getElementById('ueb2').style.display = 'block';
		document.getElementById('ueb3').style.display = 'block';
		document.getElementById('ueberschreiben3').checked = true;
	}
	else
	{
		document.getElementById('ueb1').style.display = 'none';
		document.getElementById('ueb2').style.display = 'none';
		document.getElementById('ueb3').style.display = 'none';
		document.getElementById('ueberschreiben1').checked = true;
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
	    document.getElementById('adresse-gemeinde-textfeld').type='hidden';
		document.getElementById('adresse-ort-textfeld').type='hidden';
	}
	else
	{
		document.getElementById('adresse-gemeinde-textfeld').type='text';
		document.getElementById('adresse-ort-textfeld').type='text';
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

function AnredeChange()
{
	anrede = document.getElementById('anrede').value;
	
	if(anrede=='Herr')
		document.getElementById('geschlecht').value='m';
	if(anrede=='Frau')
		document.getElementById('geschlecht').value='w';
}

function GeschlechtChange()
{
	geschlecht = document.getElementById('geschlecht').value;
	anrede = document.getElementById('anrede');
	
	if(anrede.value=='' || anrede.value=='Herr' || anrede.value=='Frau')
	{
		if(geschlecht=='m')
			anrede.value='Herr';
			
		if(geschlecht=='w')
			anrede.value='Frau';
	}
}
</script>
</head>
<body>
<h1>Mitarbeiter Anlegen</h1>
<?php
//Berechtigung pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin', null, 'suid') && !$rechte->isBerechtigt('mitarbeiter', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$where = '';
$error = false;
$importort='';
//Parameter
$anrede = (isset($_POST['anrede'])?$_POST['anrede']:'Herr');
$titel = (isset($_POST['titel'])?$_POST['titel']:'');
$titelpost = (isset($_POST['titelpost'])?$_POST['titelpost']:'');
$nachname = (isset($_POST['nachname'])?$_POST['nachname']:'');
$vorname = (isset($_POST['vorname'])?$_POST['vorname']:'');
$geschlecht = (isset($_POST['geschlecht'])?$_POST['geschlecht']:'');
$geburtsdatum = (isset($_POST['geburtsdatum'])?$_POST['geburtsdatum']:'');
$adresse = (isset($_POST['adresse'])?$_POST['adresse']:'');
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
if($gemeinde=='' && $ort!='')
{
	$importort=$ort;
	$gemeinde=$ort;
	$ort='';
}

$email = (isset($_POST['email'])?$_POST['email']:'');
$telefon = (isset($_POST['telefon'])?$_POST['telefon']:'');
$mobil = (isset($_POST['mobil'])?$_POST['mobil']:'');
$letzteausbildung = (isset($_POST['letzteausbildung'])?$_POST['letzteausbildung']:'');
$anmerkungen = (isset($_POST['anmerkungen'])?$_POST['anmerkungen']:'');
$person_id = (isset($_POST['person_id'])?$_POST['person_id']:'');
$ueberschreiben = (isset($_POST['ueberschreiben'])?$_POST['ueberschreiben']:'');
$svnr = (isset($_POST['svnr'])?$_POST['svnr']:'');
$lektor = (isset($_POST['lektor'])?true:false);
if(!isset($_POST['svnr']))
	$lektor = true;
$ersatzkennzeichen = (isset($_POST['ersatzkennzeichen'])?$_POST['ersatzkennzeichen']:'');
//end Parameter
$geburtsdatum_error=false;
$personalnummer = (isset($_POST['personalnummer'])?trim($_POST['personalnummer']):'');

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
			$svnr = $person->svnr;
			$ersatzkennzeichen = $person->ersatzkennzeichen;
			$titel = $person->titelpre;
			$titelpost = $person->titelpost;
			$geschlecht = $person->geschlecht;
		}
	}
	else
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
		$person->geburtsnation = 'A';
		$person->staatsbuergerschaft = 'A';
		$person->familienstand = 'l';
		
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

	//UID generieren
	if(!$error)
	{		
		$nachname_clean = mb_strtolower(convertProblemChars($nachname));
		$vorname_clean = mb_strtolower(convertProblemChars($vorname));
		$uid='';
		
		$uid = generateMitarbeiterUID($vorname_clean, $nachname_clean, $lektor);
			
		$bn = new benutzer();
		
		if($bn->uid_exists($uid))
		{
			$error = true;
			$errormsg = 'Es konnte keine UID ermittelt werden';
		}
	}
	
	//Kurzbz generieren
	if(!$error)
	{
		$kurzbz='';
 		$mitarbeiter = new mitarbeiter();
 		$nachname_clean = convertProblemChars($nachname);
 		$vorname_clean = convertProblemChars($vorname);
 		for($nn=6,$vn=2;$nn!=0;$nn--,$vn++)
 		{
 			$kurzbz = mb_substr($nachname_clean,0,$nn);
 			$kurzbz .= mb_substr($vorname_clean,0,$vn);

 			if(!$mitarbeiter->kurzbz_exists($kurzbz))
 				if($mitarbeiter->errormsg=='')
 					break;
 		}

 		if($mitarbeiter->kurzbz_exists($kurzbz))
 		{
 			$error = true;
 			$errormsg = 'Es konnte keine Kurzbezeichnung ermittelt werden';
 		}
	}
	
	//Alias generieren
	if(!$error)
	{
		$nachname_clean = mb_strtolower(convertProblemChars($nachname));
		$vorname_clean = mb_strtolower(convertProblemChars($vorname));
		$bn = new benutzer();
		
		if(!$bn->alias_exists($vorname_clean.'.'.$nachname_clean))
			$alias = $vorname_clean.'.'.$nachname_clean;
		else 
			$alias = '';
	}
	
	//Benutzer anlegen
	if(!$error)
	{
		$benutzer = new benutzer();
		
		$benutzer->uid = $uid;
		$benutzer->person_id = $person->person_id;
		$benutzer->bnaktiv = true;
		$benutzer->aktiv = true;
		$benutzer->alias = $alias;
		$benutzer->insertamum=date('Y-m-d H:i:s');
		$benutzer->insertvon = $user;

		if($benutzer->save(true,false))
		{
			$error = false;
		}
		else 
		{
			$error = true;
			$errormsg = 'Fehler beim Speichern des Benutzers:'.$benutzer->errormsg;
		}
	}
			
	//Mitarbeiter anlegen
	if(!$error)
	{
		$mitarbeiter = new mitarbeiter();
		
		$mitarbeiter->uid = $uid;
		$mitarbeiter->personalnummer = $personalnummer;
		$mitarbeiter->kurzbz = $kurzbz;
		$mitarbeiter->lektor = $lektor;
		$mtiarbeiter->aktiv = true;
		$mitarbeiter->fixangestellt = true;
		$mitarbeiter->stundensatz = 0;
		$mitarbeiter->bismelden = true;
		$mitarbeiter->anmerkung = $anmerkungen;
		$mitarbeiter->ausbildungcode = $letzteausbildung;
		$mitarbeiter->insertamum = date('Y-m-d H:i:s');
		$mitarbeiter->insertvon = $user;
		
		if($mitarbeiter->save(true, false))
		{
			$error = false;
		}
		else 
		{
			$error = true;
			$errormsg = 'Fehler beim Speichern des Mitarbeiters:'.$mitarbeiter->errormsg;
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
			$adr->gemeinde = $gemeinde;
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
		$db->db_query('COMMIT');
		die("<b>Mitarbeiter $vorname $nachname wurde erfolgreich angelegt</b><br><br><a href='mitarbeiterimport.php'>Neue Person Anlegen</a><br>");
	}
	else
	{
		$db->db_query('ROLLBACK');
		echo '<font class="error">'.$errormsg.'</font>';
	}
}
// *** SAVE ENDE ***
if($geburtsdatum!='')
{
	//Wenn das Datum im Format d.m.Y ist dann in Y-m-d umwandeln
	if(mb_strpos($geburtsdatum,'.'))
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
<form method='POST'>
<table width="100%">

<tr>
<td>
<!--Formularfelder-->
<table>
<?php
echo '<tr><td>Anrede</td><td><input type="text" id="anrede" name="anrede" maxlength="16" value="'.$anrede.'" onblur="AnredeChange()"/></td></tr>';
echo '<tr><td>Titel(Pre)</td><td><input type="text" id="titel" name="titel" maxlength="64" value="'.$titel.'" /></td></tr>';
echo '<tr><td>Vorname</td><td><input type="text" id="vorname" maxlength="32" name="vorname" value="'.$vorname.'" /></td></tr>';
echo '<tr><td>Nachname *</td><td><input type="text" maxlength="64" id="nachname" name="nachname" value="'.$nachname.'" /></td></tr>';
echo '<tr><td>Titel(Post)</td><td><input type="text" id="titelpost" name="titelpost" maxlength="64" value="'.$titelpost.'" /></td></tr>';
echo '<tr><td>Geschlecht *</td><td><SELECT id="geschlecht" name="geschlecht" onchange="GeschlechtChange()">';
echo '<OPTION value="m" '.($geschlecht=='m'?'selected':'').'>m&auml;nnlich</OPTION>';
echo '<OPTION value="w" '.($geschlecht=='w'?'selected':'').'>weiblich</OPTION>';
echo '<OPTION value="u" '.($geschlecht=='u'?'selected':'').'>unbekannt</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>SVNR</td><td><input type="text" id="svnr" size="10" maxlength="10" name="svnr" value="'.$svnr.'" onblur="GeburtsdatumEintragen()" /></td></tr>';
echo '<tr><td>Ersatzkennzeichen</td><td><input type="text" id="ersatzkennzeichen" size="10" maxlength="10" name="ersatzkennzeichen" value="'.$ersatzkennzeichen.'" /></td></tr>';
echo '<tr><td>Geburtsdatum</td><td><input type="text" id="geburtsdatum" size="10" maxlength="10" name="geburtsdatum" value="'.$geburtsdatum.'" /> (Format: dd.mm.JJJJ)</td></tr>';
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

echo '</div><input type="'.($adresse_nation=='A'?'hidden':'text').'" id="adresse-gemeinde-textfeld" maxlength="256" name="gemeinde_txt" value="'.$gemeinde.'" />';

echo '</td></tr>';
echo '<tr><td>Ort</td><td><div id="ortdiv">';
if($adresse_nation=='A' && $plz!='')
{
	echo getOrtDropDown($plz, $gemeinde);
}
echo '</div><input type="'.($adresse_nation=='A'?'hidden':'text').'" id="adresse-ort-textfeld" maxlength="256" name="ort_txt" value="'.$ort.'"/></td></tr>';

echo '</table>';
echo '<div style="display: none;" id="ueb1"><input type="radio" id="ueberschreiben1" name="ueberschreiben" value="Ja" onclick="disablefields2(false)">Bestehende Adresse überschreiben</div>';
echo '<div style="display: none;" id="ueb2"><input type="radio" id="ueberschreiben2" name="ueberschreiben" value="Nein" onclick="disablefields2(false)" checked>Adresse hinzufügen</div>';
echo '<div style="display: none;" id="ueb3"><input type="radio" id="ueberschreiben3" name="ueberschreiben" value="" onclick="disablefields2(true)">Adresse nicht anlegen</div>';
echo '</fieldset></td></tr>';
echo '<tr><td>EMail</td><td><input type="text" id="email" maxlength="128" name="email" value="'.$email.'" /></td></tr>';
echo '<tr><td>Telefon</td><td><input type="text" id="telefon" maxlength="128" name="telefon" value="'.$telefon.'" /></td></tr>';
echo '<tr><td>Mobil</td><td><input type="text" id="mobil" maxlength="128" name="mobil" value="'.$mobil.'" /></td></tr>';
echo '<tr><td>Letzte Ausbildung</td><td><SELECT id="letzteausbildung" name="letzteausbildung">';
echo '<OPTION value="">-- keine Auswahl --</OPTION>';
$qry = "SELECT * FROM bis.tbl_ausbildung ORDER BY ausbildungcode";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		echo '<OPTION value="'.$row->ausbildungcode.'" '.($letzteausbildung==$row->ausbildungcode?'selected':'').'>'.$row->ausbildungbez.'</OPTION>';
	}
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Lektor</td><td><input type="checkbox" name="lektor" '.($lektor?'checked':'').' /></td></tr>';
echo '<tr><td>Personalnummer</td><td><input type="text" name="personalnummer" size="4" value="'.$personalnummer.'" /> (optional)</td></tr>';
echo '<tr><td>Anmerkungen</td><td><textarea id="anmerkung" name="anmerkungen">'.$anmerkungen.'</textarea></td></tr>';
echo '<tr><td></td><td>';

if(($geburtsdatum=='' && $vorname=='' && $nachname=='') || $geburtsdatum_error)
	echo '<input type="submit" name="showagain" value="Vorschlag laden"></td></tr>';
else
{
	echo '<input type="submit" name="showagain" value="Vorschlag laden">';
	echo '<input type="submit" name="save" value="Speichern"></td></tr>';
}

echo '
</table>
<br><br>
Felder die mit einem * gekennzeichnet sind müssen ausgefüllt werden!
</td>

<td valign="top">
';

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
	$where.=" (LOWER(vorname)=LOWER('".$vorname."') AND LOWER(nachname)=LOWER('".$nachname."'))";
}

if($where!='')
{
	$qry = "SELECT * FROM public.tbl_person WHERE $where ORDER BY nachname, vorname, gebdatum";
	
	if($result = $db->db_query($qry))
	{
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
				while($row_stati = $db->db_fetch_object($result_stati))
				{
					$status.=$row_stati->rolle.', ';
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
			echo '<td><a href="../personendetails.php?id='.$row->person_id.'" target="_blank">Details</a></td>';
			echo '</tr>';
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