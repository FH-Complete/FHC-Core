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
require_once('../../../system/sync/sync_config.inc.php');
require_once('../../../include/'.EXT_FKT_PATH.'/generateuid.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/adresse.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/student.class.php');
require_once('../../../include/lehrverband.class.php');
require_once('../../../include/nation.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
	die('Fehler beim Herstellen der DB Connection');

$user=get_uid();
$datum_obj = new datum();
#gss loadVariables($conn, $user);
loadVariables($user);

function getGemeindeDropDown($postleitzahl)
{
	global $conn, $_REQUEST, $gemeinde;
	$found=false;
	$firstentry='';
	$gemeinde_x = (isset($_REQUEST['gemeinde'])?$_REQUEST['gemeinde']:'');
	$qry = "SELECT distinct name FROM bis.tbl_gemeinde WHERE plz='".addslashes($postleitzahl)."'";
	echo '<SELECT id="gemeinde" name="gemeinde" onchange="loadOrtData()">';
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
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
	global $conn, $_REQUEST;
	$ort = (isset($_REQUEST['ort'])?$_REQUEST['ort']:'');
	$qry = "SELECT distinct ortschaftsname FROM bis.tbl_gemeinde 
			WHERE plz='".addslashes($postleitzahl)."' AND name='".addslashes($gemeindename)."'";
	echo '<SELECT id="ort" name="ort">';
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			if($row->ortschaftsname==$ort)
				$selected='selected';
			else 
				$selected='';
			echo "<option value='$row->ortschaftsname' $selected>$row->ortschaftsname</option>";
		}
	}
	
	echo '</SELECT>';
}
if(isset($_GET['type']) && $_GET['type']=='getortcontent' && isset($_GET['plz']) && isset($_GET['gemeinde']))
{
	header('Content-Type: text/html; charset=UTF-8');
	
	echo getOrtDropDown($_GET['plz'], utf8_decode($_GET['gemeinde']));
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

function disablefields2(val)
{
	document.getElementById('adresse').disabled=val;
	document.getElementById('plz').disabled=val;
	document.getElementById('ort').disabled=val;
}

function AnredeChange()
{
	anrede = document.getElementById('anrede').value;
	
	if(anrede=='Herr')
		document.getElementById('geschlecht').value='m';
	if(anrede=='Frau')
		document.getElementById('geschlecht').value='w';
}

function cmdIncoming()
{
	document.getElementById('ausbildungssemester').disabled=document.getElementById('incoming').checked;
}

function checkVorschlag()
{
	var elems = document.getElementsByName('person_id');
	
	for(i=0;i<=elems.length;i++)
	{
		try
		{
			//alert(elems[i].name+elems[i].checked+elems[i].value);
			if(elems[i].checked)
				return true;
		}
		catch(e)
		{}
	}
	
	alert('Bitte wählen Sie einen der Vorschläge aus');
	return false;
}

function checkInput1()
{
	if(document.getElementById('nachname').value=='')
	{
		alert('Nachname muss eingetragen werden');
		return false;
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
</script>
</head>
<body>
<h1>Interessent Anlegen</h1>
<?php
//Berechtigung pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$where = '';
$error = false;
$importort='';
//Parameter
$titel = (isset($_REQUEST['titel'])?$_REQUEST['titel']:'');
$titelpost = (isset($_REQUEST['titelpost'])?$_REQUEST['titelpost']:'');
$anrede = (isset($_REQUEST['anrede'])?$_REQUEST['anrede']:'');
$nachname = (isset($_REQUEST['nachname'])?$_REQUEST['nachname']:'');
$vorname = (isset($_REQUEST['vorname'])?$_REQUEST['vorname']:'');
$geschlecht = (isset($_REQUEST['geschlecht'])?$_REQUEST['geschlecht']:'');
$geburtsdatum = (isset($_REQUEST['geburtsdatum'])?$_REQUEST['geburtsdatum']:'');
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
if($gemeinde=='' && $ort!='')
{
	$importort=$ort;
	$gemeinde=$ort;
	$ort='';
}
$email = (isset($_REQUEST['email'])?$_REQUEST['email']:'');
$telefon = (isset($_REQUEST['telefon'])?$_REQUEST['telefon']:'');
$mobil = (isset($_REQUEST['mobil'])?$_REQUEST['mobil']:'');
$letzteausbildung = (isset($_REQUEST['letzteausbildung'])?$_REQUEST['letzteausbildung']:'');
$ausbildungsart = (isset($_REQUEST['ausbildungsart'])?$_REQUEST['ausbildungsart']:'');
$anmerkungen = (isset($_REQUEST['anmerkungen'])?$_REQUEST['anmerkungen']:'');
$studiengang_kz = (isset($_REQUEST['studiengang_kz'])?$_REQUEST['studiengang_kz']:'');
if($studiengang_kz=='' && isset($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
$person_id = (isset($_REQUEST['person_id'])?$_REQUEST['person_id']:'');
$ueberschreiben = (isset($_REQUEST['ueberschreiben'])?$_REQUEST['ueberschreiben']:'');
$studiensemester_kurzbz = (isset($_REQUEST['studiensemester_kurzbz'])?$_REQUEST['studiensemester_kurzbz']:'');
$ausbildungssemester = (isset($_REQUEST['ausbildungssemester'])?$_REQUEST['ausbildungssemester']:'0');
$incoming = (isset($_REQUEST['incoming'])?true:false);
$orgform_kurzbz = (isset($_REQUEST['orgform_kurzbz'])?$_REQUEST['orgform_kurzbz']:'');
//end Parameter
$geburtsdatum_error=false;

// ****
// * Generiert die Matrikelnummer
// * FORMAT: 0710254001
// * 07 = Jahr
// * 1/2/0  = WS/SS/incoming
// * 0254 = Studiengangskennzahl vierstellig
// * 001 = Laufende Nummer
// ****
function generateMatrikelnummer($conn, $studiengang_kz, $studiensemester_kurzbz)
{
	$jahr = substr($studiensemester_kurzbz, 4);	
	$sem = substr($studiensemester_kurzbz, 0, 2);
	if($sem=='SS')
		$jahr = $jahr-1;
	$art =0;
	
	$matrikelnummer = sprintf("%02d",$jahr).$art.sprintf("%04d",$studiengang_kz);
	
	$qry = "SELECT matrikelnr FROM public.tbl_student WHERE matrikelnr LIKE '$matrikelnummer%' ORDER BY matrikelnr DESC LIMIT 1";
	
	if($result = pg_query($conn, $qry))
	{
		if($row = pg_fetch_object($result))
		{
			$max = substr($row->matrikelnr,7);
		}
		else 
			$max = 0;
		
		$max += 1;
		return $matrikelnummer.sprintf("%03d",$max);
	}
	else 
	{
		return false;
	}
}

function clean_string($string)
 {
 	$trans = array("ä" => "ae",
 				   "Ä" => "Ae",
 				   "ö" => "oe",
 				   "Ö" => "Oe",
 				   "ü" => "ue",
 				   "Ü" => "Ue",
 				   "á" => "a",
 				   "à" => "a",
 				   "é" => "e",
 				   "è" => "e",
 				   "ó" => "o",
 				   "ò" => "o",
 				   "í" => "i",
 				   "ì" => "i",
 				   "ù" => "u",
 				   "ú" => "u",
 				   "ß" => "ss");
	$string = strtr($string, $trans);
    return ereg_replace("[^a-zA-Z0-9]", "", $string);
    //[:space:]
 }

if($studiensemester_kurzbz == '')
{
	//Im September wird das Aktuelle Studiensemester vorgeschlagen sonst immer das naechste WS
	$stsem = new studiensemester($conn);
	if(date('m')=='9')
		$studiensemester_kurzbz = $stsem->getaktorNext();
	else
	{
		$stsem->getNextStudiensemester('WS');
		$studiensemester_kurzbz = $stsem->studiensemester_kurzbz;
	}
	
}

// *** Speichern der Daten ***
if(isset($_POST['save']))
{
	//echo "Saving Data: Geburtsdatum: $geburtsdatum | Titel: $titel | Nachname: $nachname | Vorname: $vorname |
	//		Geschlecht: $geschlecht | Adresse: $adresse | Plz: $plz | Ort: $ort |
	//		Email: $email | Telefon: $telefon | Mobil: $mobil | Letzteausbildung: $letzteausbildung | ausbildungsart: $ausbildungsart |
	//		anmerkungen: $anmerkungen | studiengang_kz: $studiengang_kz | person_id: $person_id<br><br>";
	$person = new person($conn);
	$prestudent = new prestudent($conn);
	
	$prestudent_vorhanden=false;
	
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
			$titelpost = $person->titelpost;
			$geschlecht = $person->geschlecht;
			$anrede = $person->anrede;
			//Wenn Prestudent bereits existiert, dann abbrechen
			if($prestudent->exists($person_id, $studiengang_kz))
			{
				//Prestudent ID holen
				$qry = "SELECT prestudent_id FROM public.tbl_prestudent WHERE person_id='$person_id' AND studiengang_kz='$studiengang_kz'";
				if($result = pg_query($conn, $qry))
				{
					if(pg_num_rows($result)>1)
					{
						// Wenn bereits mehrere Prestudenten in diesem Studingang vorhanden sind, dann abbrechen
						$error=true;
						$errormsg = 'Der Interessent konnte nicht angelegt werden, da dieser Student bereits mehr als einen Prestudenten in diesem Studiengang hat.';
					}
					else 
					{
						$row = pg_fetch_object($result);
						$prestudent_id=$row->prestudent_id;

						//Wenn der Prestudent noch keinen Studenten eintrag hat, dann wird die neue
						//Rolle hinzugefuegt, sonst wird abgebrochen
						$qry = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id='$prestudent_id' AND status_kurzbz='Student'";
						if($result = pg_query($conn, $qry))
						{
							if(pg_num_rows($result)>0)
							{
								$error=true;
								$errormsg = 'Der Interessent konnte nicht angelegt werden, da diese Person bereits als Student in diesem Studiengang angelegt ist.';
							}
							else 
							{
								$prestudent_vorhanden=true;
								$prestudent->load($prestudent_id);
							}
						}
						else 
						{
							$error=true;
							$errormsg = 'Fehler beim Ermitteln der Prestudentrollen!';
						}
					}
				}
				else 
				{
					$error = true;
					$errormsg = 'Fehler beim Suchen der PrestudentID';
				}
			}
		}
	}
	else
	{
		$person->new = true;
		$person->anrede = $anrede;
		$person->titelpre = $titel;
		$person->titelpost = $titelpost;
		$person->nachname = $nachname;
		$person->vorname = $vorname;
		$person->geschlecht = $geschlecht;
		$person->gebdatum = $geburtsdatum;
		$person->geburtsnation = 'A';
		$person->staatsbuergerschaft = 'A';
		$person->aktiv = true;
		$person->insertamum = date('Y-m-d H:i:s');
		$person->insertvon = $user;
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
	if(!$error && !$prestudent_vorhanden)
	{
		$prestudent->new = true;
		$prestudent->aufmerksamdurch_kurzbz = 'k.A.';
		$prestudent->person_id = $person->person_id;
		$prestudent->studiengang_kz = $studiengang_kz;
		$prestudent->ausbildungcode = $letzteausbildung;
		$prestudent->anmerkung = $anmerkungen .($ausbildungsart!=''?' Ausbildungsart:'.$ausbildungsart:'');
		$prestudent->reihungstestangetreten = false;
		$prestudent->bismelden = true;
		
		//Wenn die Person schon im System erfasst ist, dann die ZGV des Datensatzes uebernehmen
		$qry_zgv = "SELECT * FROM public.tbl_prestudent WHERE person_id='$person->person_id' AND zgv_code is not null ORDER BY zgvmas_code, zgv_code DESC LIMIT 1";
		if($result_zgv = pg_query($conn, $qry_zgv))
		{
			if($row_zgv = pg_fetch_object($result_zgv))
			{
				if($row_zgv->zgv_code!='')
				{
					$prestudent->zgv_code = $row_zgv->zgv_code;
					$prestudent->zgvort = $row_zgv->zgvort;
					$prestudent->zgvdatum = $row_zgv->zgvdatum;
					
					$prestudent->zgvmas_code = $row_zgv->zgvmas_code;
					$prestudent->zgvmaort = $row_zgv->zgvmaort;
					$prestudent->zgvmadatum = $row_zgv->zgvmadatum;
				}
			}
		}

		if(!$prestudent->save())
		{
			$error=true;
			$errormsg = $prestudent->errormsg;
		}
	}
	
	if(!$error)
	{
		//Prestudent Rolle Anlegen			
		$rolle = new prestudent($conn);

		$rolle->prestudent_id = $prestudent->prestudent_id;
		if(!$incoming)
			$rolle->status_kurzbz = 'Interessent';
		else
			$rolle->status_kurzbz = 'Incoming';
		$rolle->studiensemester_kurzbz = $studiensemester_kurzbz;
		$rolle->ausbildungssemester = $ausbildungssemester;
		$rolle->orgform_kurzbz = $orgform_kurzbz;
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

	if(!$error && $incoming)
	{
		//Matrikelnummer und UID generieren
		$matrikelnr = generateMatrikelnummer($conn, $studiengang_kz, $studiensemester_kurzbz);
				
		$jahr = substr($matrikelnr,0, 2);
		$stg = substr($matrikelnr, 3, 4);
		
		$stg_obj = new studiengang($conn);
		$stg_obj->load(ltrim($stg,'0'));
		
		$uid = generateUID($stg_obj->kurzbz,$jahr, $stg_obj->typ, $matrikelnr);
						
		//Benutzerdatensatz anlegen
		$benutzer = new benutzer();
		$benutzer->uid = $uid;
		$benutzer->person_id = $person->person_id;
		$benutzer->aktiv = true;
							
		$qry_alias = "SELECT * FROM public.tbl_benutzer WHERE alias=LOWER('".clean_string($person->vorname).".".clean_string($person->nachname)."')";
		$result_alias = pg_query($conn, $qry_alias);
		if(pg_num_rows($result_alias)==0)								
			$benutzer->alias = strtolower(clean_string($person->vorname).'.'.clean_string($person->nachname));
		else 
			$benutzer->alias = '';
									
		$benutzer->insertamum = date('Y-m-d H:i:s');
		$benutzer->insertvon = $user;
																	
		if($benutzer->save(true, false))
		{
			//Studentendatensatz anlegen
			$student = new student($conn);
			$student->uid = $uid;
			$student->matrikelnr = $matrikelnr;
			$student->prestudent_id = $prestudent->prestudent_id;
			$student->studiengang_kz = $studiengang_kz;
			$student->semester = '0';
			$student->verband = 'I';
			$student->gruppe = ' ';
			$student->insertamum = date('Y-m-d H:i:s');
			$student->insertvon = $user;

			$lvb = new lehrverband($conn);
			if(!$lvb->exists($student->studiengang_kz, $student->semester, $student->verband, $student->gruppe))
			{
				$lvb->studiengang_kz = $student->studiengang_kz;
				$lvb->semester = $student->semester;
				$lvb->verband = $student->verband;
				$lvb->gruppe = $student->gruppe;
				$lvb->bezeichnung = 'Incoming';
				$lvb->aktiv = true;
				
				$lvb->save(true);
			}
			
			if($student->save(true, false))
			{
				//StudentLehrverband anlegen
				$studentlehrverband = new student($conn);
				$studentlehrverband->uid = $uid;
				$studentlehrverband->studiensemester_kurzbz = $studiensemester_kurzbz;
				$studentlehrverband->studiengang_kz = $studiengang_kz;
				$studentlehrverband->semester = '0';
				$studentlehrverband->verband = 'I';
				$studentlehrverband->gruppe = ' ';
				$studentlehrverband->insertamum = date('Y-m-d H:i:s');
				$studentlehrverband->insertvon = $user;
					
				if(!$studentlehrverband->save_studentlehrverband(true))
				{
					$error = true;
					$errormsg = 'StudentLehrverband konnte nicht angelegt werden';
				}
			}
			else 
			{
				$error = true;
				$errormsg = 'Student konnte nicht angelegt werden: '.$student->errormsg;
			}
		}
		else 
		{
			$error = true;
			$errormsg = 'Benutzer konnte nicht angelegt werden:'.$benutzer->errormsg;
		}
	}
	
	if(!$error)
	{
		pg_query($conn, 'COMMIT');
		die("<b>".($incoming?'Incoming':'Interessent')." $vorname $nachname wurde erfolgreich angelegt</b><br><br><a href='interessentenimport.php?studiengang_kz=$studiengang_kz'>Neue Person Anlegen</a>");
	}
	else
	{
		pg_query($conn, 'ROLLBACK');
		echo '<span class="error">'.$errormsg.'</span>';
	}
}
// *** SAVE ENDE ***

$geburtsdatum_orig = $geburtsdatum;
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
		if(!ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$geburtsdatum))
			$geburtsdatum_error=true;
	}
	
	if($geburtsdatum_error)
		echo "Format des Geburtsdatums ist ungueltig!";
}
if(($geburtsdatum=='' && $vorname=='' && $nachname=='') || $geburtsdatum_error)
	echo "<form method='POST' onsubmit='return checkInput1();'>";
else 
	echo "<form method='POST'>";
?>
<table width="100%">

<tr>
<td>
<!--Formularfelder-->
<table>
<?php
echo '<tr><td>Anrede</td><td><input type="text" id="anrede" name="anrede" maxlength="64" value="'.$anrede.'"  onblur="AnredeChange()"/></td></tr>';
echo '<tr><td>Titel(Pre)</td><td><input type="text" id="titel" name="titel" maxlength="64" value="'.$titel.'" /></td></tr>';
echo '<tr><td>Vorname </td><td><input type="text" id="vorname" maxlength="32" name="vorname" value="'.$vorname.'" /></td></tr>';
echo '<tr><td>Nachname *</td><td><input type="text" maxlength="64" id="nachname" name="nachname" value="'.$nachname.'" /></td></tr>';
echo '<tr><td>Titel(Post)</td><td><input type="text" id="titelpost" name="titelpost" maxlength="64" value="'.$titelpost.'" /></td></tr>';
echo '<tr><td>Geschlecht *</td><td><SELECT id="geschlecht" name="geschlecht">';
echo '<OPTION value="m" '.($geschlecht=='m'?'selected':'').'>m&auml;nnlich</OPTION>';
echo '<OPTION value="w" '.($geschlecht=='w'?'selected':'').'>weiblich</OPTION>';
echo '<OPTION value="u" '.($geschlecht=='u'?'selected':'').'>unbekannt</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Geburtsdatum </td><td><input type="text" id="geburtsdatum" size="10" maxlength="10" name="geburtsdatum" value="'.$geburtsdatum_orig.'" /> (Format: dd.mm.JJJJ)</td></tr>';
echo '<tr><td colspan="2"><fieldset><legend>Adresse</legend><table>';
echo '<tr><td>Nation</td><td><SELECT name="adresse_nation" id="adresse_nation" onchange="loadGemeindeData()">';
$nation =  new nation($conn);
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
echo '<OPTION value="" '.($letzteausbildung==''?'selected':'').'>-- keine Auswahl --</OPTION>';
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
echo '<tr><td>Studiengang *</td><td><SELECT id="studiengang_kz" name="studiengang_kz">';
$stg_obj = new studiengang($conn);
$stg_obj->getAll('typ, kurzbz');
foreach ($stg_obj->result as $row)
{
	if($rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid') || $rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid'))
		echo '<OPTION value="'.$row->studiengang_kz.'" '.($row->studiengang_kz==$studiengang_kz?'selected':'').'>'.$row->kuerzel.'</OPTION>';
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Studiensemester *</td><td><SELECT id="studiensemester_kurzbz" name="studiensemester_kurzbz">';
$stsem = new studiensemester($conn);
$stsem->getAll();
foreach ($stsem->studiensemester as $row)
	echo '<OPTION value="'.$row->studiensemester_kurzbz.'" '.($row->studiensemester_kurzbz==$studiensemester_kurzbz?'selected':'').'>'.$row->studiensemester_kurzbz.'</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Ausbildungssemester *</td><td><SELECT id="ausbildungssemester" name="ausbildungssemester" '.($incoming?'disabled':'').'>';
for ($i=1;$i<9;$i++)
	echo '<OPTION value="'.$i.'" '.($i==$ausbildungssemester?'selected':'').'>'.$i.'. Semester</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>OrgForm</td><td><SELECT name="orgform_kurzbz">';
echo '<OPTION value="">-- keine Auswahl --</OPTION>';
$qry = "SELECT orgform_kurzbz, bezeichnung FROM bis.tbl_orgform WHERE rolle ORDER BY bezeichnung";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		echo '<OPTION value="'.$row->orgform_kurzbz.'" '.($orgform_kurzbz==$row->orgform_kurzbz?'selected':'').'>'.$row->bezeichnung.'</OPTION>';
	}
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><td>Incoming:</td><td><input type="checkbox" id="incoming" name="incoming" '.($incoming?'checked':'').' onclick="cmdIncoming()" /></td></tr>';
echo '<tr><tr><td></td><td>';

if(($geburtsdatum=='' && $vorname=='' && $nachname=='') || $geburtsdatum_error)
	echo '<input type="submit" name="showagain" value="Vorschlag laden"></td></tr>';
else
{
	echo '<input type="submit" name="showagain" value="Vorschlag laden">';
	echo '<input type="submit" name="save" value="Speichern" onclick="return checkVorschlag()"></td></tr>';
}
?>
</table>
<br><br>
Felder die mit einem * gekennzeichnet sind müssen ausgefüllt werden!

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
	$where.=" (LOWER(vorname)=LOWER('".$vorname."') AND LOWER(nachname)=LOWER('".$nachname."'))";
}
elseif($nachname!='')
{
	if($where!='')
		$where.=' OR';
	$where.=" LOWER(nachname)=LOWER('".$nachname."')";
}

if($where!='')
{
	$qry = "SELECT * FROM public.tbl_person WHERE $where ORDER BY nachname, vorname, gebdatum";
	
	if($result = pg_query($conn, $qry))
	{
		echo '<table><tr><th></th><th>Nachname</th><th>Vorname</th><th>GebDatum</th><th>SVNR</th><th>Geschlecht</th><th>Adresse</th><th>Status</th><th>Details</th></tr>';
		while($row = pg_fetch_object($result))
		{
			$status = '';
			$qry_stati = "SELECT 'Mitarbeiter' as rolle FROM campus.vw_mitarbeiter WHERE person_id='$row->person_id'
							UNION
							SELECT (get_rolle_prestudent(prestudent_id, null) || ' ' || UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz)) as rolle FROM public.tbl_prestudent JOIN public.tbl_studiengang USING(studiengang_kz) WHERE person_id='$row->person_id'
							UNION
							SELECT 'PreInteressent' as rolle FROM public.tbl_preinteressent WHERE person_id='$row->person_id'";
			if($result_stati = pg_query($conn, $qry_stati))
			{
				while($row_stati=pg_fetch_object($result_stati))
				{
					$status.=$row_stati->rolle.', ';
				}
			}
			$status = substr($status, 0, strlen($status)-2);
			
			echo '<tr valign="top"><td><input type="radio" name="person_id" value="'.$row->person_id.'" onclick="disablefields(this)"></td><td>'."$row->nachname</td><td>$row->vorname</td><td>$row->gebdatum</td><td>$row->svnr</td><td>".($row->geschlecht=='m'?'männlich':'weiblich')."</td><td>";
			$qry_adr = "SELECT * FROM public.tbl_adresse WHERE person_id='$row->person_id'";
			if($result_adr = pg_query($conn, $qry_adr))
				while($row_adr=pg_fetch_object($result_adr))
					echo "$row_adr->plz $row_adr->ort, $row_adr->strasse<br>";
			echo '</td>';
			echo '<td>'.$status.'</td>';
			echo '<td><a href="../personendetails.php?id='.$row->person_id.'" target="_blank">Details</a></td>';
			echo '</tr>';
		}
		echo '<tr><td><input type="radio" name="person_id" value="0" onclick="disablefields(this)"></td><td>Neue Person anlegen</td></tr>';
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