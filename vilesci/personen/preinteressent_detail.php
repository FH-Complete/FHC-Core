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
require_once('../../include/studiengang.class.php');
require_once('../../include/preinteressent.class.php');
require_once('../../include/person.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/aufmerksamdurch.class.php');
require_once('../../include/firma.class.php');
require_once('../../include/nation.class.php');
require_once('../../include/mail.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$datum_obj = new datum();
$stsem = new studiensemester();
$stsem_aktuell = $stsem->getaktorNext();

$selection = (isset($_GET['selection'])?$_GET['selection']:'preinteressent');

//wenn der parameter type=firmenrequest uebergeben wird
//dann eine Liste aller firmen zurueckliefern die dem ueberbenen firmentyp entsprechen
if(isset($_GET['type']) && $_GET['type']=='firmenrequest')
{
	header('Content-Type: text/html; charset=UTF-8');
	$firmentyp_kurzbz = (isset($_GET['firmentyp_kurzbz'])?$_GET['firmentyp_kurzbz']:'');
	$firma = new firma();
	$firma->getFirmen($firmentyp_kurzbz);
	echo " -- keine Angabe --\n";
	foreach ($firma->result as $row)
		echo $row->firma_id.' '.$row->name."\n";
	exit();
}

echo '<html>
	<head>
		<title>PreInteressenten</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script language="Javascript">
		<!--
		var selection = "'.$selection.'";
		
		function checkschulid(schuleid)
		{
			if(schuleid!="")
			{
				dd = document.getElementById("firma")
				//preufen ob die id im DD vorhanden ist
				myoptions = dd.getElementsByTagName("option");
				id="";
				for(i=0;i<myoptions.length;i++)
				{
		
					node = myoptions[i];
					if(node.value==schuleid)
						id=schuleid;
				}
				
				document.getElementById("firma").value=id;
			}
			return true;
		}
		
		function changeTo(id)
		{
			selection=id;
			document.getElementById(id).style.display="block";
			document.getElementById(id+"_label").style.textDecoration="underline";
			
			if(id=="personendaten")
			{
				document.getElementById("preinteressent").style.display="none";
				document.getElementById("preinteressent_label").style.textDecoration="none";
				document.getElementById("studiengangszuordnung").style.display="none";
				document.getElementById("studiengangszuordnung_label").style.textDecoration="none";
			}
			else if(id=="preinteressent")
			{
				document.getElementById("personendaten").style.display="none";
				document.getElementById("personendaten_label").style.textDecoration="none";
				document.getElementById("studiengangszuordnung").style.display="none";
				document.getElementById("studiengangszuordnung_label").style.textDecoration="none";
			}
			else if(id=="studiengangszuordnung")
			{
				document.getElementById("personendaten").style.display="none";
				document.getElementById("personendaten_label").style.textDecoration="none";
				document.getElementById("preinteressent").style.display="none";
				document.getElementById("preinteressent_label").style.textDecoration="none";
			}
		}
		
		function confdel()
		{
			return confirm("Wollen Sie diesen Eintrag wirklich loeschen?");
		}
				
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
      
		function reloadSchulen()
		{
			schultyp = document.getElementById("schultyp").value;
			
			erzeugeAnfrage(); 
		    var jetzt = new Date();
			var ts = jetzt.getTime();
		    var url= "preinteressent_detail.php?type=firmenrequest";
		    url += "&firmentyp_kurzbz="+schultyp+"&"+ts;
		    anfrage.open("GET", url, true);
		    anfrage.onreadystatechange = updateSeite;
		    anfrage.send(null);
		}
   
		function updateSeite()
		{
	    	if (anfrage.readyState == 4)
	    	{
	        	if (anfrage.status == 200) 
	        	{
					var resp = anfrage.responseText;
	            	firma = document.getElementById("firma");
	            	while (firma.childNodes.length>0)
	            	{
						firma.removeChild(firma.lastChild);
	            	}
	            	
	            	var zeilen = resp.split("\n");
	            	var items="";
	            	for(i=0;i<zeilen.length-1;i++)
	            	{
	            		spalten = zeilen[i].split(" ",1);
	            		var opt = document.createElement("option");
						opt.setAttribute("value", spalten[0]);
						var txt = document.createTextNode(utf(zeilen[i].substring(zeilen[i].indexOf(" ")+1)));
						opt.appendChild(txt); 
						firma.appendChild(opt);
	            	}
				}
				else
				{
					alert(resp);
				}
			}
	    }
	    
	    function utf(txt)
	    {
	    	txt=encode_utf8(txt);
	    	txt=decode_utf8(txt);
	    	
	    	return txt;
	    }
	    
        function encode_utf8(rohtext) {
             // dient der Normalisierung des Zeilenumbruchs
             rohtext = rohtext.replace(/\r\n/g,"\n");
             var utftext = "";
             for(var n=0; n<rohtext.length; n++)
                 {
                 // ermitteln des Unicodes des  aktuellen Zeichens
                 var c=rohtext.charCodeAt(n);
                 // alle Zeichen von 0-127 => 1byte
                 if (c<128)
                     utftext += String.fromCharCode(c);
                 // alle Zeichen von 127 bis 2047 => 2byte
                 else if((c>127) && (c<2048)) {
                  utftext += String.fromCharCode((c>>6)|192);
                     utftext += String.fromCharCode((c&63)|128);}
                 // alle Zeichen von 2048 bis 66536 => 3byte
                 else {
                     utftext += String.fromCharCode((c>>12)|224);
                     utftext += String.fromCharCode(((c>>6)&63)|128);
                     utftext += String.fromCharCode((c&63)|128);}
                 }
             return utftext;
         }
		function decode_utf8(utftext) 
		{
			var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    
			
		}
		-->
		</script>
	</head>
	<body class="Background_main">
	';

if(!$rechte->isBerechtigt('admin', null, 'suid') && 
   !$rechte->isBerechtigt('preinteressent', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if(isset($_GET['id']) && is_numeric($_GET['id']))
	$id = $_GET['id'];
else 
	die('<h2>Details</h2>');
	
	
$preinteressent = new preinteressent();

if(!$preinteressent->load($id))
	die('Datensatz konnte nicht geladen werden');
	
$person = new person();
if(!$person->load($preinteressent->person_id))
	die('Personen Datensatz konnte nicht geladen werden');

echo "<h2>Details - $person->nachname $person->vorname</h2>";

if(isset($_POST['save_preinteressent']))
{
	//Speichern der Preinteressentdaten
	
	$preinteressent->studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
	$preinteressent->aufmerksamdurch_kurzbz = $_POST['aufmerksamdurch_kurzbz'];
	$preinteressent->firma_id = $_POST['firma'];
	$preinteressent->erfassungsdatum = $datum_obj->formatDatum($_POST['erfassungsdatum'],'Y-m-d');
	$preinteressent->einverstaendnis = isset($_POST['einverstaendnis']);
	if(isset($_POST['absagedatum']) && $preinteressent->absagedatum=='')
		$preinteressent->absagedatum = date('Y-m-d H:i:s');
	if(!isset($_POST['absagedatum']))
		$preinteressent->absagedatum = '';
	$preinteressent->anmerkung = $_POST['anmerkung'];
	$preinteressent->updateamum = date('Y-m-d H:i:s');
	$preinteressent->updatevon = $user;
	$preinteressent->maturajahr = $_POST['maturajahr'];
	$preinteressent->infozusendung = $_POST['infozusendung'];
	$preinteressent->kontaktmedium_kurzbz = $_POST['kontaktmedium_kurzbz'];

	if(!$preinteressent->save(false))
		echo "<b>Fehler beim Speichern der Daten: $preinteressent->errormsg</b>";
	else 
		echo "<b>Daten wurden gespeichert</b>";
}

if(isset($_POST['saveperson']))
{
	//Speichern der Personendaten
	
	$person->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
	$person->geburtsnation = $_POST['geburtsnation'];
	$person->sprache = $_POST['sprache'];
	$person->anrede = $_POST['anrede'];
	$person->titelpost = $_POST['titelpost'];
	$person->titelpre = $_POST['titelpre'];
	$person->nachname = $_POST['nachname'];
	$person->vorname = $_POST['vorname'];
	$person->vornamen = $_POST['vornamen'];
	$person->gebdatum = $datum_obj->formatDatum($_POST['gebdatum'],'Y-m-d');
	$person->gebort = $_POST['gebort'];
	$person->gebzeit = $_POST['gebzeit'];
	$person->anmerkungen = $_POST['anmerkungen'];
	$person->homepage = $_POST['homepage'];
	$person->svnr = $_POST['svnr'];
	$person->ersatzkennzeichen = $_POST['ersatzkennzeichen'];
	$person->familienstand = $_POST['familienstand'];
	$person->geschlecht = $_POST['geschlecht'];
	$person->anzahlkinder = $_POST['anzahlkinder'];
	$person->aktiv = isset($_POST['aktiv']);
	$person->updateamum = date('Y-m-d H:i:s');
	$person->updatevon = $user;
	
	if($person->save(false))
	{
		echo '<b>Daten wurden erfolgreich gespeichert</b>';
	}
	else 
	{
		echo "<b>Fehler beim Speichern der Daten: $person->errormsg</b>";
	}
	
}
if(isset($_GET['action']) && $_GET['action']=='neuezuordnung')
{
	//speichern einer neue Studiengangszuordnung
	$zuordnung = new preinteressent();

	if(!$zuordnung->loadZuordnung($preinteressent->preinteressent_id, $_POST['studiengang_kz']))
	{
		$zuordnung->preinteressent_id = $preinteressent->preinteressent_id;
		$zuordnung->studiengang_kz = $_POST['studiengang_kz'];
		$zuordnung->prioritaet = $_POST['prioritaet'];
		$zuordnung->insertamum = date('Y-m-d H:i:s');
		$zuordnung->insertvon = $user;
		
		if(!$zuordnung->saveZuordnung(true))
			echo "<b>Fehler beim Speichern: $zuordnung->errormsg</b>";
	}
	else 
		echo "<b>Es besteht bereits eine Zuordnung zu diesem Studiengang</b>";
}

if(isset($_GET['savezuordnung']))
{
	//bestehende Zuordnung speichern
	$zuordnung = new preinteressent();	
	
	if($zuordnung->loadZuordnung($preinteressent->preinteressent_id, $_GET['studiengang_kz']))
	{
		$zuordnung->prioritaet = $_POST['prioritaet'];
		$zuordnung->updateamum = date('Y-m-d H:i:s');
		$zuordnung->updatevon = $user;
		
		if(!$zuordnung->saveZuordnung(false))
			echo "<b>Fehler beim Speichern der Daten: $zuordnung->errormsg</b>";
	}
	else 
		echo '<b>Fehler beim Speichern der Daten: Datensatz wurde nicht gefunden</b>';
}

if(isset($_POST['freigabe']))
{
	if($preinteressent->studiensemester_kurzbz!='')
	{
		//freigabe einer zuordnung
		$zuordnung = new preinteressent();
		if($zuordnung->loadZuordnung($preinteressent->preinteressent_id, $_GET['studiengang_kz']))
		{
			if($zuordnung->freigabedatum=='')
			{
				$zuordnung->freigabedatum = date('Y-m-d H:i:s');
				$zuordnung->updateamum = date('Y-m-d H:i:s');
				$zuordnung->updatevon = $user;
			
				if(!$zuordnung->saveZuordnung(false))
					echo "<b>Fehler beim Speichern der Daten: $zuordnung->errormsg</b>";
				else 
				{
					//MAIL an Assistenz verschicken
					$qry_person = "SELECT vorname, nachname 
									FROM public.tbl_person JOIN public.tbl_preinteressent USING(person_id) 
									WHERE preinteressent_id='$preinteressent->preinteressent_id'";
					$name='';
					if($result_person = $db->db_query($qry_person))
						if($row_person = $db->db_fetch_object($result_person))
							$name = $row_person->nachname.' '.$row_person->vorname;
					$stg_obj = new studiengang();
					$stg_obj->load($zuordnung->studiengang_kz);
					$to = $stg_obj->email;
					//$to = 'oesi@technikum-wien.at';
					$message = "Dies ist eine automatische Mail! $stg_obj->email\n\n".
								"Der Preinteressent $name wurde zur Übernahme freigegeben. \nSie können diesen ".
								"im FAS unter 'Extras->Preinteressenten übernehmen' oder unter folgendem Link\n\n".
								APP_ROOT."vilesci/personen/preinteressent_uebernahme.php?studiengang_kz=$zuordnung->studiengang_kz \n".
								"ins FAS übertragen";
					$mail = new mail($to, 'vilesci@'.DOMAIN, 'Preinteressent Freigabe', $message);
					if($mail->send())
						echo "<br><b>Freigabemail wurde an $to versendet</b>";
					else 
						echo "<br><b>Fehler beim Versenden des Freigabemails an $to</b>";
				}
			}
			else 
			{
				echo '<b>Diese Zuteilung ist bereits freigegeben</b>';
			}
		}
		else 
			echo '<b>Fehler beim Speichern der Daten: Datensatz wurde nicht gefunden</b>';	
	}
	else 
	{
		echo '<b>Es muss ein Studiensemester eingetragen sein damit diese Person freigegeben werden kann</b>';
	}
}

if(isset($_POST['freigabe_rueckgaengig']))
{
	//studiengangsfreigabe zurueckziehen
	$zuordnung = new preinteressent();
	if($zuordnung->loadZuordnung($preinteressent->preinteressent_id, $_GET['studiengang_kz']))
	{
		if($zuordnung->freigabedatum!='')
		{
			if($zuordnung->uebernahmedatum=='')
			{
				$zuordnung->freigabedatum = '';
				$zuordnung->updateamum = date('Y-m-d H:i:s');
				$zuordnung->updatevon = $user;
		
				if(!$zuordnung->saveZuordnung(false))
					echo "<b>Fehler beim Speichern der Daten: $zuordnung->errormsg</b>";	
			}
			else 
			{
				echo '<b>Freigabe kann nicht R&uuml;ckg&auml;ngig gemacht werden da der Datensatz bereits &uuml;bernommen wurde</b>';
			}
		}
		else 
		{
			echo '<b>Diese Zuteilung ist bereits freigegeben</b>';
		}
	}
	else 
		echo '<b>Fehler beim Speichern der Daten: Datensatz wurde nicht gefunden</b>';	
}
if(isset($_POST['zuordnungloeschen']))
{
	//zuordnung zu einem studiengang loeschen
	$zuordnung = new preinteressent();
	if($zuordnung->loadZuordnung($preinteressent->preinteressent_id, $_GET['studiengang_kz']))
	{
		if($zuordnung->uebernahmedatum=='')
		{
			if(!$zuordnung->deleteZuordnung($preinteressent->preinteressent_id, $_GET['studiengang_kz']))
				echo "<b>Fehler beim L&ouml;schen der Zuteilung: $zuordnung->errormsg</b>";
		}
		else 
		{
			echo '<b>Diese Zuteilung wurde bereits uebernommen und kann daher nicht geloescht werden</b>';
		}
	}
	else 
		echo '<b>Fehler beim Speichern der Daten: Datensatz wurde nicht gefunden</b>';	
}

// ----- TABS ------
echo '<h3><a id="preinteressent_label" href="javascript: changeTo(\'preinteressent\');" '.($selection=='preinteressent'?'style="text-decoration:underline"':'').'>PreInteressent</a> - ';
echo '<a id="studiengangszuordnung_label" href="javascript: changeTo(\'studiengangszuordnung\');"'.($selection=='studiengangszuordnung'?'style="text-decoration:underline"':'').'>Studiengangszuordnung</a> - ';
echo '<a id="personendaten_label" href="javascript: changeTo(\'personendaten\');"'.($selection=='personendaten'?'style="text-decoration:underline"':'').'>Personendaten</a></h3>';

// ----- PERSON -----
echo "<div id='personendaten' style='display: ".($selection=='personendaten'?'block':'none')."'>";


$disabled=true;
$qry = "SELECT count(*) as anzahl FROM (
		SELECT 1 FROM public.tbl_prestudent WHERE person_id='$person->person_id' UNION 
		SELECT 1 FROM public.tbl_benutzer WHERE person_id='$person->person_id') as foo";
if($result = $db->db_query($qry))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->anzahl==0)
			$disabled=false;
	}
}

echo "<form accept-charset='UTF-8' action='".$_SERVER['PHP_SELF']."?id=$preinteressent->preinteressent_id&selection=personendaten' method='POST'>";
echo "<table><tr>";

//Anrede
echo "<td>Anrede:</td><td><input type='text' name='anrede' ".($disabled?'disabled':'')." value='".$person->anrede."'></td>";
//Titelpre
echo "<td>Titelpre:</td><td><input type='text' name='titelpre' ".($disabled?'disabled':'')." value='".$person->titelpre."'></td>";
//Titelpost
echo "<td>Titelpost:</td><td><input type='text' name='titelpost' ".($disabled?'disabled':'')." value='".$person->titelpost."'></td>";
echo '<td width="100%" align="right"><a href="personendetails.php?id='.$person->person_id.'" target="_blank">Gesamtübersicht über diese Person</a></td>';
echo '</tr><tr>';
//Nachname
echo "<td>Nachname*:</td><td><input type='text' name='nachname' ".($disabled?'disabled':'')." value='".$person->nachname."'></td>";
//Vorname
echo "<td>Vorname:</td><td><input type='text' name='vorname' ".($disabled?'disabled':'')." value='".$person->vorname."'></td>";
//Vornamen
echo "<td>2. Vorname:</td><td><input type='text' name='vornamen' ".($disabled?'disabled':'')." value='".$person->vornamen."'></td>";
echo '<td width="100%" align="right">';
if(!$disabled)
	echo "<a href='kontaktdaten_edit.php?person_id=$person->person_id' target='_blank'>Kontaktdaten bearbeiten</a>";
else 
	echo "Kontaktdaten bearbeiten";
echo '</td>';
echo '</tr><tr>';
//Geburtsdatum
echo "<td>Geburtsdatum:</td><td><input type='text' name='gebdatum' ".($disabled?'disabled':'')." value='".$datum_obj->formatDatum($person->gebdatum, 'd.m.Y')."'></td>";
//Geburtsort
echo "<td>Geburtsort:</td><td><input type='text' name='gebort' ".($disabled?'disabled':'')." value='".$person->gebort."'></td>";
//Geburtszeit
echo "<td>Geburtszeit:</td><td><input type='text' name='gebzeit' size='5' ".($disabled?'disabled':'')." value='".$person->gebzeit."'></td>";
echo '</tr><tr>';
//Staatsbuergerschaft
echo "<td>Staatsb&uuml;rgerschaft:</td><td><SELECT ".($disabled?'disabled':'')." name='staatsbuergerschaft'>";
echo "<option value=''>-- Keine Auswahl --</option>";
$nation = new nation();
$nation->getAll();

foreach ($nation->nation as $row)
{
	if($row->code==$person->staatsbuergerschaft)
		$selected='selected';
	else 
		$selected='';
	
	echo "<option value='$row->code' $selected>$row->kurztext</option>";
}
echo "</SELECTED>";
echo "</td>";
//Geburtsnation
echo "<td>Geburtsnation:</td><td><SELECT ".($disabled?'disabled':'')." name='geburtsnation'>";
echo "<option value=''>-- Keine Auswahl --</option>";
$nation = new nation();
$nation->getAll();

foreach ($nation->nation as $row)
{
	if($row->code==$person->geburtsnation)
		$selected='selected';
	else 
		$selected='';
	
	echo "<option value='$row->code' $selected>$row->kurztext</option>";
}
echo "</SELECTED>";
echo "</td>";
//Sprache
echo "<td>Sprache:</td><td><SELECT ".($disabled?'disabled':'')." name='sprache'>";
echo "<option value=''>-- keine Auswahl --</option>";
$qry = "SELECT * FROM public.tbl_sprache ORDER BY sprache";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($row->sprache==$person->sprache)
			$selected='selected';
		else 
			$selected='';
		
		echo "<option value='$row->sprache' $selected>$row->sprache</option>";
	}
}
echo '</SELECT></td>';
echo '</tr><tr>';
//SVNR
echo "<td>SVNR:</td><td><input type='text' name='svnr' ".($disabled?'disabled':'')." value='".$person->svnr."'></td>";
//Ersatzkennzeichen
echo "<td>Ersatzkennzeichen</td><td><input type='text' name='ersatzkennzeichen' ".($disabled?'disabled':'')." value='".$person->ersatzkennzeichen."'></td>";
//Geschlecht
echo "<td>Geschlecht*</td><td><SELECT ".($disabled?'disabled':'')." name='geschlecht'>";
echo '<option value="m" '.($person->geschlecht=='m'?'selected':'').'>männlich</option>';
echo '<option value="w" '.($person->geschlecht=='w'?'selected':'').'>weiblich</option>';
echo '<option value="u" '.($person->geschlecht=='u'?'selected':'').'>unbekannt</option>';
echo '</SELECT></td>';
echo '</tr><tr>';

//Anzahlkinder
echo "<td>Anzahl der Kinder</td><td><input type='text' name='anzahlkinder' ".($disabled?'disabled':'')." value='".$person->anzahlkinder."'></td>";
//Aktiv
echo "<td>Aktiv:</td><td><input type='checkbox' ".($disabled?'disabled':'')." name='aktiv' ".($person->aktiv==false?'':'checked')."></td>";
echo '</tr><tr valign="top">';
//Anmerkung
echo "<td>Anmerkung:</td><td><textarea ".($disabled?'disabled':'')." name='anmerkungen'>".$person->anmerkungen."</textarea></td>";
//Homepage
echo "<td>Homepage:</td><td><input type='text' name='homepage' ".($disabled?'disabled':'')." value='".$person->homepage."'></td>";
//Familienstand
echo "<td>Familienstand:</td><td><SELECT ".($disabled?'disabled':'')." name='familienstand'>";
echo '<option value="l" '.($person->familienstand=='l'?'selected':'').'>ledig</option>';
echo '<option value="v" '.($person->familienstand=='v'?'selected':'').'>verheiratet</option>';
echo '<option value="g" '.($person->familienstand=='g'?'selected':'').'>geschieden</option>';
echo '<option value="w" '.($person->familienstand=='w'?'selected':'').'>verwitwert</option>';
echo '</SELECT></td>';
echo "</tr><tr><td></td><td></td><td></td><td></td><td></td><td><input ".($disabled?'disabled':'')." type='submit' value='Speichern' name='saveperson'></td>";
echo "</tr></table></form>";
echo "</div>";

// ----- PREINTERESSENT -----
echo "<div id='preinteressent' style='display: ".($selection=='preinteressent'?'block':'none')."'>";
echo "<form accept-charset='UTF-8' action='".$_SERVER['PHP_SELF']."?id=$preinteressent->preinteressent_id&selection=preinteressent' method='POST'>";

echo '<table width="100%" ><tr>';

//STUDIENSEMESTER
echo "<td>Studiensemester:</td><td><SELECT name='studiensemester_kurzbz'>";
$stsem = new studiensemester();
$stsem->getAll();
echo "<option value='' >-- offen --</option>";
foreach ($stsem->studiensemester as $row)
{
	if($row->studiensemester_kurzbz==$preinteressent->studiensemester_kurzbz)
		$selected='selected';
	else
		$selected='';
		
	echo "<option value='$row->studiensemester_kurzbz' $selected>$row->studiensemester_kurzbz</option>";
}
echo "</SELECT>";

echo '</td>';

//AUFMERKSAMDURCH
echo "<td>Aufmerksam durch:</td><td> <SELECT name='aufmerksamdurch_kurzbz'>";
$aufmerksam = new aufmerksamdurch();
$aufmerksam->getAll('beschreibung');
foreach ($aufmerksam->result as $row)
{
	if($row->aufmerksamdurch_kurzbz==$preinteressent->aufmerksamdurch_kurzbz)
		$selected='selected';
	else
		$selected='';
		
	echo "<option value='$row->aufmerksamdurch_kurzbz' $selected>$row->beschreibung</option>";
}
echo "</SELECT>";
echo '</td>';

echo "<td>Kontaktmedium (Woher)</td><td><SELECT name='kontaktmedium_kurzbz'>";

echo "<option value=''>-- keine Auswahl --</option>";
$qry = "SELECT * FROM public.tbl_kontaktmedium ORDER BY beschreibung";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($preinteressent->kontaktmedium_kurzbz==$row->kontaktmedium_kurzbz)
			$selected='selected';
		else 
			$selected='';
		
		echo "<option value='$row->kontaktmedium_kurzbz' $selected>$row->beschreibung</option>";
	}
}
echo "</SELECT></td>";

//Absagedatum
echo "<td>Absage</td><td><input type='checkbox' ".($preinteressent->absagedatum!=''?'checked':'')." name='absagedatum'></td>";

echo '</tr><tr>';

//Erfassungsdatum
echo "<td>Erfassungsdatum:</td><td> <input type='text' size='10' maxlength='10' name='erfassungsdatum' value='".$datum_obj->formatDatum($preinteressent->erfassungsdatum,'d.m.Y')."'> (31.12.2008)</td>";

//Infozusendung
echo "<td>Infozusendung am</td><td><input type='text' name='infozusendung' size='10' maxlength='10' value='".$datum_obj->formatDatum($preinteressent->infozusendung,'d.m.Y')."'></td>";


//Maturajahr
echo "<td>Maturajahr</td><td><input type='text' name='maturajahr' size='4' maxlength='4' value='$preinteressent->maturajahr'></td>";

//Einverstaendnis
echo "<td>Einverst&auml;ndnis:</td><td><input type='checkbox' ".($preinteressent->einverstaendnis?'checked':'')." name='einverstaendnis'></td>";

echo '</tr><tr>';

$schule = new firma();
if($preinteressent->firma_id!='')
	$schule->load($preinteressent->firma_id);

echo '<td>Schule ID:</td><td><input type="text" size="3" name="schule_id" id="schule_id" value="'.$preinteressent->firma_id.'" onkeyup="checkschulid(this.value)"></td>';

//SCHULE
echo "<td>Schule:</td><td colspan='5'> <SELECT id='firma' name='firma' onchange='document.getElementById(\"schule_id\").value=this.value'>";
$qry = "SELECT plz, ort, strasse, tbl_firma.name, tbl_firma.firma_id 
		FROM public.tbl_firma JOIN public.tbl_standort USING(firma_id) LEFT JOIN public.tbl_adresse USING(adresse_id) 
		WHERE schule ORDER BY plz, name";
echo "<option value='' >-- keine Angabe --</option>";

function shortname($name)
{
	if(strlen($name)>40)
	{
		return mb_substr($name, 0, 20).' ... '.mb_substr($name, mb_strlen($name)-20,mb_strlen($name));
	}
	else 
		return $name;
}
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($row->firma_id==$preinteressent->firma_id)
			$selected='selected';
		else
			$selected='';
		
	echo "<option value='$row->firma_id' $selected>$row->plz $row->ort - ".shortname($row->name)." ($row->firma_id)</option>";
	}
}

echo "</SELECT> <a href='../stammdaten/firma_frameset.html' target='_blank'><img src='../../skin/images/preferences-system.png' alt='Schulverwaltung' /></a></td>";

echo '</tr><tr>';

//Anmerkung
echo '<td>Anmerkungen:</td>';
echo '<td colspan="7">';
echo "<textarea rows='4' style='width: 100%' name='anmerkung'>".$preinteressent->anmerkung."</textarea>";
echo '</td>';

echo '</tr><tr>';
echo '<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td align="right"><input type="submit" name="save_preinteressent" value="Speichern"></td>
		';

echo '</tr></table>';
echo '</form>';
echo "</div>";

// ----- Studiengangszuordnung -----
echo "<div id='studiengangszuordnung' style='display: ".($selection=='studiengangszuordnung'?'block':'none')."'>";

echo '<table class="liste table-stripeclass:alternate table-autostripe"><tr><th>Studiengang</th><th>Priorit&auml;t</th><th>Freigabe</th><th>&Uuml;bernahme</th><th colspan="2">Aktion</th></tr>';
$zuordnung = new preinteressent();
$zuordnung->loadZuordnungen($preinteressent->preinteressent_id);

foreach ($zuordnung->result as $row)
{
	echo "<form accept-charset='UTF-8' action='".$_SERVER['PHP_SELF']."?id=$preinteressent->preinteressent_id&studiengang_kz=$row->studiengang_kz&selection=studiengangszuordnung' method='POST'>";
	echo '<tr>';
	echo '<td>';
	$studiengang = new studiengang();
	$studiengang->load($row->studiengang_kz);
	echo "$studiengang->kuerzel - $studiengang->bezeichnung";
	echo '</td>';
	echo '<td>';
	echo '<SELECT name="prioritaet" onchange="this.form.action=this.form.action+\'&savezuordnung\';this.form.submit();">';
	echo '<option value="1" '.($row->prioritaet==1?'selected':'').'>niedrig (1)</option>';
	echo '<option value="2" '.($row->prioritaet==2?'selected':'').'>mittel (2)</option>';
	echo '<option value="3" '.($row->prioritaet==3?'selected':'').'>hoch (3)</option>';
	echo '</SELECT>';
	echo '</td>';
	echo '<td>';
	//Wenn noch nicht freigegeben - Freigabe Button anzeigen
	if($row->freigabedatum=='')
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_prestudent WHERE person_id='$person->person_id' AND studiengang_kz='$row->studiengang_kz'";
		if($result_check = $db->db_query($qry))
		{
			if($row_check = $db->db_fetch_object($result_check))
			{
				if($row_check->anzahl==0)
				{
					echo '<input type="submit" name="freigabe" value="Freigeben">';			
				}
				else 
				{
					echo 'ist bereits im Studiengang erfasst';
				}
			}
		}
		
	}
	else
	{
		if($row->uebernahmedatum=='')
		{
			//Wenn freigegeben aber noch nicht uebernommen -> zurueckziehen button anzeigen
			echo '<input type="submit" name="freigabe_rueckgaengig" value="Freigabe zur&uuml;ckziehen">';
		}
		else 
		{
			//Wenn freigegeben und uebernommen -> Freigabedatum anzeigen
			echo $datum_obj->formatDatum($row->freigabedatum, 'd.m.Y H:i:s');
		}
	}
	echo '</td>';
	
	echo '<td>';
	echo $datum_obj->formatDatum($row->uebernahmedatum, 'd.m.Y H:i:s');
	echo '</td>';
	echo '<td>';
	//echo '<input type="submit" value="Speichern" name="savezuordnung">';
	echo '</td>';
	echo '<td>';
	if($row->uebernahmedatum=='')
		echo '<input type="submit" value="L&ouml;schen" name="zuordnungloeschen" onclick="return confdel();">';
	echo '</td>';
	echo '</tr></form>';
}

//Neuer Eintrag
echo "<form accept-charset='UTF-8' action='".$_SERVER['PHP_SELF']."?id=$preinteressent->preinteressent_id&selection=studiengangszuordnung&action=neuezuordnung' method='POST'>";	
echo '<tr>';
echo '<td>';
echo '<SELECT name="studiengang_kz">';
$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

foreach ($studiengang->result as $rowstg)
{
	echo "<option value='$rowstg->studiengang_kz' $selected>$rowstg->kuerzel - $rowstg->bezeichnung</option>";
}
echo '</SELECT>';
echo '</td>';
echo '<td>';
echo '<SELECT name="prioritaet">';
echo '<option value="1" selected>niedrig (1)</option>';
echo '<option value="2">mittel (2)</option>';
echo '<option value="3">hoch (3)</option>';
echo '</SELECT>';
echo '</td>';
echo '<td>';
//Freigabedatum
echo '</td>';

echo '<td>';
//Uebernahmedatum
echo '</td>';
echo '<td>';
echo '<input type="submit" value="Neu" name="speichern">';
echo '</td>';
echo '<td>';

echo '</td>';
echo '</tr></form>';

echo '</table>';
echo '</div>';

echo '</body>';
echo '</html>';


?>