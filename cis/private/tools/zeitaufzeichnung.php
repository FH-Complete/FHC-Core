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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>
 *          Karl Burkhart <burkhart@technikum-wien.at>
 *          Manfred Kindl <kindlm@technikum.wien.at>.
 *          Gerald Raab <raab@technikum-wien.at> 
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/fachbereich.class.php');
require_once('../../../include/zeitaufzeichnung.class.php');
require_once('../../../include/zeitsperre.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/projekt.class.php');
require_once('../../../include/phrasen.class.php'); 
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/service.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/betriebsmittelperson.class.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/bisverwendung.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$sprache = getSprache(); 
$p=new phrasen($sprache); 
	
if (!$db = new basis_db())
	die($p->t("global/fehlerBeimOeffnenDerDatenbankverbindung"));
	
$user = get_uid();

//Wenn User Administrator ist und UID uebergeben wurde, dann die Zeiaufzeichnung 
//des uebergebenen Users anzeigen
if(isset($_GET['uid']))
{
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('mitarbeiter/urlaube', null, 'suid'))
	{
		$user = $_GET['uid'];
	}
	else 
	{
		die($p->t('global/FuerDieseAktionBenoetigenSieAdministrationsrechte'));
	}
}


$datum = new datum();

if (check_infrastruktur($user))
{
	$za_simple = 0;
	$activities = 	array('Design', 'Operativ', 'Betrieb',  'Pause', 'LehreIntern', 'LehreExtern', 'Arztbesuch', 'Dienstreise', 'Behoerde');
}	
else 
{
	$za_simple = 1;
	$activities = array('Arbeit', 'Pause', 'LehreIntern', 'LehreExtern', 'Arztbesuch', 'Dienstreise', 'Behoerde');
}

$activities_str = "'".implode("','", $activities)."'";

// definiert bis zu welchem Datum die Eintragung nicht mehr möglich ist
$gesperrt_bis = '2015-08-31';
$sperrdatum = date('c', strtotime($gesperrt_bis));

// Uses urlencode to avoid XSS issues
$zeitaufzeichnung_id = urlencode(isset($_GET['zeitaufzeichnung_id'])?$_GET['zeitaufzeichnung_id']:'');
$projekt_kurzbz = (isset($_POST['projekt'])?$_POST['projekt']:'');
$oe_kurzbz_1 = (isset($_POST['oe_kurzbz_1'])?$_POST['oe_kurzbz_1']:'');
$oe_kurzbz_2 = (isset($_POST['oe_kurzbz_2'])?$_POST['oe_kurzbz_2']:'');
$aktivitaet_kurzbz = (isset($_POST['aktivitaet'])?$_POST['aktivitaet']:'');
$von_datum = (isset($_REQUEST['von_datum'])?$_REQUEST['von_datum']:date('d.m.Y'));
$von_uhrzeit = (isset($_POST['von_uhrzeit'])?$_POST['von_uhrzeit']:date('H:i'));
$von = $von_datum.' '.$von_uhrzeit;
$bis_datum = (isset($_REQUEST['bis_datum'])?$_REQUEST['bis_datum']:date('d.m.Y'));
$bis_uhrzeit = (isset($_POST['bis_uhrzeit'])?$_POST['bis_uhrzeit']:date('H:i',mktime(date('H'), date('i')+10)));
$bis = $bis_datum.' '.$bis_uhrzeit;
$beschreibung = (isset($_POST['beschreibung'])?$_POST['beschreibung']:'');
$service_id = (isset($_POST['service_id'])?$_POST['service_id']:'');
$kunde_uid = (isset($_POST['kunde_uid'])?$_POST['kunde_uid']:'');
$kartennummer = (isset($_POST['kartennummer'])?$_POST['kartennummer']:'');
$filter = (isset($_GET['filter'])?$_GET['filter']:'foo');
$alle = (isset($_GET['alle'])?(isset($_GET['normal'])?false:true):false);
$angezeigte_tage = '50';

$zs = new zeitsperre();
if ($alle)
	$zs->getZeitsperrenForZeitaufzeichnung($user,'180');
else
	$zs->getZeitsperrenForZeitaufzeichnung($user,$angezeigte_tage);

$zeitsperren = $zs->result;

echo '<!DOCTYPE HTML>
<html>
	<head>
		<title>'.$p->t("zeitaufzeichnung/zeitaufzeichnung").'</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
		<link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
		<link href="../../../skin/jquery.css" rel="stylesheet" type="text/css"/>
		<link href="../../../skin/jquery.ui.timepicker.css" rel="stylesheet" type="text/css"/>
        <link href="../../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet"  type="text/css">
		<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
        <script src="../../../include/js/jquery1.9.min.js" type="text/javascript" ></script>
        <script src="../../../include/js/jquery.ui.timepicker.js" type="text/javascript" ></script>
		 ';

// ADDONS laden
$addon_obj = new addon();
$addon_obj->loadAddons();
foreach($addon_obj->result as $addon)
{
	if(file_exists('../../../addons/'.$addon->kurzbz.'/cis/init.js.php'))
		echo '<script type="application/x-javascript" src="../../../addons/'.$addon->kurzbz.'/cis/init.js.php" ></script>';
}

// Wenn Seite fertig geladen ist Addons aufrufen
echo '
<script>
$( document ).ready(function() 
{
	if(typeof addon  !== \'undefined\')
	{
		for(i in addon)
		{
			addon[i].init("cis/private/tools/zeitaufzeichnung.php", {uid:\''.$user.'\'});
		}
	}
});
</script>
';

echo '
        <script type="text/javascript">
		$(document).ready(function() 
		{ 
		    $( ".datepicker_datum" ).datepicker({
					 changeMonth: true,
					 changeYear: true, 
					 dateFormat: "dd.mm.yy",
					 });
			
			$( ".timepicker" ).timepicker({
					showPeriodLabels: false,
					hourText: "'.$p->t("global/stunde").'",
					minuteText: "'.$p->t("global/minute").'",
					hours: {starts: 7,ends: 22},
					rows: 4,
					});
			
			$(".tablesorter").each(function(i,v)
			{
				$("#"+v.id).tablesorter(
				{
					widgets: ["zebra"],
					headers: {0: { sorter: false}, 12: { sorter: false}}
				})
			}); 

            function formatItem(row) 
            {
                return row[0] + " " + row[1] + " " + row[2];
            }	
            
            $("#kunde_name").autocomplete({
			source: "zeitaufzeichnung_autocomplete.php?autocomplete=kunde",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
					ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#kunde_uid").val(ui.item.uid);
			}
			});

		}); 
		
		function setbisdatum()
		{
			var now = new Date();
			var ret_datum = "";
			var ret_uhrzeit = "";
			var monat = now.getMonth();
			monat++;
			ret_datum = foo(now.getDate());
			ret_datum = ret_datum + "." + foo(monat);
			ret_datum = ret_datum + "." + now.getFullYear();
			
			ret_uhrzeit = foo(now.getHours());
			ret_uhrzeit = ret_uhrzeit + ":" + foo(now.getMinutes());
				
			document.getElementById("bis_datum").value=ret_datum;
			document.getElementById("bis_uhrzeit").value=ret_uhrzeit;
		}
		
		function setvondatum()
		{
			var now = new Date();
			var ret_datum = "";
			var ret_uhrzeit = "";
			var monat = now.getMonth();
			monat++;
			ret_datum = foo(now.getDate());
			ret_datum = ret_datum + "." + foo(monat);
			ret_datum = ret_datum + "." + now.getFullYear();
			
			ret_uhrzeit = foo(now.getHours());
			ret_uhrzeit = ret_uhrzeit + ":" + foo(now.getMinutes());
				
			document.getElementById("von_datum").value=ret_datum;
			document.getElementById("von_uhrzeit").value=ret_uhrzeit;
		}
		
		function foo(val)
		{
			if(val<10)
				return "0"+val;
			else
				return val;
		}
		
		function checkZeiten()
		{
			var von_el = document.getElementById("von_uhrzeit");
			var bis_el = document.getElementById("bis_uhrzeit");
			var von_zeit = von_el.value;
			var bis_zeit = bis_el.value;
			von_arr = von_zeit.split(":");
			bis_arr = bis_zeit.split(":");
			if (von_arr[0].length == 1)
				von_arr[0] = foo(von_arr[0]);
			if (von_arr[1].length == 1)
				von_arr[1] = foo(von_arr[1]);
			if (bis_arr[0].length == 1)
				bis_arr[0] = foo(bis_arr[0]);
			if (bis_arr[1].length == 1)
				bis_arr[1] = foo(bis_arr[1]);			
			von_zeit = von_arr[0]+":"+von_arr[1];
			bis_zeit = bis_arr[0]+":"+bis_arr[1];
			von_el.value = von_zeit;
			bis_el.value = bis_zeit;
			
		}
		function confdel()
		{
			return confirm("'.$p->t("global/warnungWirklichLoeschen").'");
		}
		
		function loaduebersicht()
		{
			projekt = document.getElementById("projekt").value;
			
			document.location.href="'.$_SERVER['PHP_SELF'].'?filter="+projekt;
		}
		
		function uebernehmen()
		{
			document.getElementById("bis_datum").value=document.getElementById("von_datum").value;
			document.getElementById("bis_uhrzeit").value=document.getElementById("von_uhrzeit").value;
		}
		
		function addieren()
		{
			var von_tag,von_monat,von_jahr,von_stunden,von_minuten,tag,monat,jahr,stunde,minute,vonDatum,bisDatum,bisUhrzeit,diff,foo;
			//Von-Datum auslesen
			Datum = document.getElementById("von_datum").value;
			Uhrzeit = document.getElementById("von_uhrzeit").value;
		    von_tag = Datum.substring(0,2); 
		    von_monat = Datum.substring(3,5);
		    von_monat = von_monat-1;
		    von_jahr = Datum.substring(6,10);
		    von_stunden = Uhrzeit.substring(0,2);
		    von_minuten = Uhrzeit.substring(3,5);
		    //Neues Datumsobjekt aus Von-Datum erzeugen
			vonDatum = new Date(von_jahr, von_monat, von_tag, von_stunden, von_minuten);
			//Falls diff kein Integer, dann 0
			diff = document.getElementById("diff").value;
			if (!isNaN(parseInt(diff)))
				diff = parseInt(diff);
			else
				diff = 0;
			
		    monat = vonDatum.getMonth();
		    monat++;
		    monat = (monat < 10 ? "0"+monat : monat);
			minute = vonDatum.getMinutes();
			minute = minute+diff;
			stunde = vonDatum.getHours();
			if (minute > 59)
			{
				foo = minute/60;
				foo = Math.floor(foo);
				stunde = stunde+foo;
				minute = minute-60*foo;
			}
			minute = (minute < 10 ? "0"+minute : minute);
			tag = vonDatum.getDate();
			if (stunde >= 24)
			{
				foo = stunde/24;
				foo = Math.floor(foo);
				tag = tag+foo;
				stunde = stunde-24;
			}
			tag = (tag < 10 ? "0"+tag : tag);
			jahr = vonDatum.getFullYear();
			stunde = (stunde < 10 ? "0"+stunde : stunde);
			
			bisDatum = tag+\'.\'+monat+\'.\'+jahr;
			bisUhrzeit = stunde+\':\'+minute;
			document.getElementById("bis_datum").value = bisDatum;
			document.getElementById("bis_uhrzeit").value = bisUhrzeit;
		}

		function uebernehmen1()
		{
			document.getElementById("von_datum").value=document.getElementById("bis_datum").value;
			document.getElementById("von_uhrzeit").value=document.getElementById("bis_uhrzeit").value;
		}

		function checkdatum()
		{
			var Datum,Tag,Monat,Jahr,Stunde,Minute,vonDatum,bisDatum,diff;
		
			Datum=document.getElementById("von_datum").value;
			Uhrzeit=document.getElementById("von_uhrzeit").value;
		    Tag=Datum.substring(0,2); 
		    Monat=Datum.substring(3,5);
		    Jahr=Datum.substring(6,10);
		    Stunde=Uhrzeit.substring(0,2);
		    Minute=Uhrzeit.substring(3,5);
		    vonDatum=Jahr+\'\'+Monat+\'\'+Tag+\'\'+Stunde+\'\'+Minute;
		    
		    Datum=document.getElementById("bis_datum").value;
		    Uhrzeit=document.getElementById("bis_uhrzeit").value;
		    Tag=Datum.substring(0,2); 
		    Monat=Datum.substring(3,5);
		    Jahr=Datum.substring(6,10);
		    Stunde=Uhrzeit.substring(0,2);
		    Minute=Uhrzeit.substring(3,5);
		    bisDatum=Jahr+\'\'+Monat+\'\'+Tag+\'\'+Stunde+\'\'+Minute;
		    diff=bisDatum-vonDatum;
		    
			if (bisDatum>vonDatum)  
			{
				if (diff>9999)  
				{
					Check = confirm("'.$p->t("zeitaufzeichnung/zeitraumAuffallendHoch").'");
					document.getElementById("bis_datum").focus();
					if (Check == false)
				  		return false;
				  	else
				  		return true;
				}
			}
			else
			{
				alert("'.$p->t("zeitaufzeichnung/bisDatumKleinerAlsVonDatum").'");
				document.getElementById("bis_datum").focus();
			  	return false;
			}
			return true;
		}
		</script>
	</head>
<body>
';
$bn = new benutzer();
		if(!$bn->load($user))
			die($p->t("zeitaufzeichnung/benutzerWurdeNichtGefunden",array($user)));
			
echo '<h1>'.$p->t("zeitaufzeichnung/zeitaufzeichnungVon").' '.$db->convert_html_chars($bn->vorname).' '.$db->convert_html_chars($bn->nachname).'</h1>';

// Wenn Kartennummer übergeben wurde dann hole uid von Karteninhaber
if($kartennummer != '')
{
    $betriebsmittel = new betriebsmittelperson(); 
    if(!$betriebsmittel->getKartenzuordnung($kartennummer))
        die($betriebsmittel->errormsg); 
    
    $kunde_uid = $betriebsmittel->uid; 
}
//Speichern der Daten
if(isset($_POST['save']) || isset($_POST['edit']) || isset($_POST['import']))
{
	$zeit = new zeitaufzeichnung();
	
	if ($_FILES['csv']['error'] == 0 && isset($_POST['import']))
	{
		$name = $_FILES['csv']['name'];		
    	$tmpName = $_FILES['csv']['tmp_name'];
    	$mimeType = mime_content_type($_FILES['csv']['tmp_name']);
		//echo($mimeType);
		if($mimeType=='text/plain')
		{
			if(($handle = fopen($tmpName, 'r')) !== FALSE)
			{
				if 	(mb_detect_encoding(fgets($handle), 'UTF-8', true))
				{			
					set_time_limit(0);
					$anzahl = 0;
					$importtage_array = array();
					$ende_vorher = date('Y-m-d H:i:s');
					while(($data = fgetcsv($handle, 1000, ';', '"')) !== FALSE) 
					{
						if($data[0] == $user)
						{
							if ($datum->formatDatum($data[2], $format='Y-m-d H:i:s') < $sperrdatum)
								echo '<span style="color:red"><b>'.$p->t("global/fehlerBeimSpeichernDerDaten").': Eingabe nicht möglich da vor dem Sperrdatum ('.$data[2].')</b></span><br>';
							else
							{	
								$zeit->new = true;
								$zeit->insertamum = date('Y-m-d H:i:s');
								$zeit->updateamum = date('Y-m-d H:i:s');
								$zeit->updatevon = $user;
								$zeit->insertvon = $user;			
								$zeit->uid = $data[0];
								$zeit->aktivitaet_kurzbz = $data[1];
								$zeit->start = $datum->formatDatum($data[2], $format='Y-m-d H:i:s');
								$zeit->ende = $datum->formatDatum($data[3], $format='Y-m-d H:i:s');
								$zeit->beschreibung = $data[4];
								$tag = $datum->formatDatum($data[2], $format='Y-m-d');
								
								
								
								if(!in_array($tag, $importtage_array))
								{
									$importtage_array[] = $tag;
									$zeit->deleteEntriesForUser($user, $tag);
									$tag_aktuell = $tag;
								}							
								else 
								{
									if ($ende_vorher < $zeit->start)
									{
										$pause = new zeitaufzeichnung();
										$pause->new = true;
										$pause->insertamum = date('Y-m-d H:i:s');
										$pause->updateamum = date('Y-m-d H:i:s');
										$pause->updatevon = $user;
										$pause->insertvon = $user;			
										$pause->uid = $user;
										$pause->aktivitaet_kurzbz = 'Pause';
										$pause->start = $ende_vorher;
										$pause->ende = $zeit->start;
										$zeit->beschreibung = '';
										if(!$pause->save())
										{
											echo '<span style="color:red"><b>'.$p->t("global/fehlerBeimSpeichernDerDaten").': '.$pause->errormsg.'</b></span>';	
										}
									}
								}
								
								$ende_vorher = $zeit->ende;
								if($data[2] != $data[3])
								{							
									/*									
									if ($data[1] == 'LehreExtern')
									{
										$zeit->start = date('Y-m-d H:i:s', strtotime('+2 seconds', strtotime($data[2])));
										$zeit->ende = date('Y-m-d H:i:s', strtotime('-2 seconds', strtotime($data[3])));
									}
									*/
									if(!$zeit->save())
									{
										echo '<span style="color:red"><b>'.$p->t("global/fehlerBeimSpeichernDerDaten").': '.$zeit->errormsg.'</b>('.$zeit->start.')</span>';
									}
									else 
										$anzahl++;
								}
								else 
									$anzahl++;					
								
							}
						}
					}
					if($anzahl>0)
					{
						echo '<span style="color:green"><b>'.$p->t("global/datenWurdenGespeichert").' ('.$anzahl.')</b></span>';
						foreach ($importtage_array as $ptag)
						{	
							$zeit->cleanPausenForUser($user, $ptag);
						}
					}
				}
				else 
					echo '<span style="color:red"><b>Datei konnte nicht importiert werden. Encoding ist nicht UTF-8!</b></span>';
			}
		}
	}
	else if ($datum->formatDatum($von, $format='Y-m-d H:i:s') < $sperrdatum)
		echo '<span style="color:red"><b>'.$p->t("global/fehlerBeimSpeichernDerDaten").': Eingabe nicht möglich da vor dem Sperrdatum</b></span>';
	else if (isset($_POST['save']) || isset($_POST['edit']))
	{

		if(isset($_POST['edit']))
		{
			if(!$zeit->load($zeitaufzeichnung_id))
				die($p->t("global/fehlerBeimLadenDesDatensatzes"));
			
			$zeit->new = false;
		}
		else 
		{
			$zeit->new = true;
			$zeit->insertamum = date('Y-m-d H:i:s');
			$zeit->insertvon = $user;
		}
		
		$zeit->uid = $user;
		$zeit->aktivitaet_kurzbz = $aktivitaet_kurzbz;
		$zeit->start = $datum->formatDatum($von, $format='Y-m-d H:i:s');
		$zeit->ende = $datum->formatDatum($bis, $format='Y-m-d H:i:s');
		$zeit->beschreibung = $beschreibung;
		$zeit->oe_kurzbz_1 = $oe_kurzbz_1;
		$zeit->oe_kurzbz_2 = $oe_kurzbz_2;
		$zeit->updateamum = date('Y-m-d H:i:s');
		$zeit->updatevon = $user;
		$zeit->projekt_kurzbz = $projekt_kurzbz;
		$zeit->service_id = $service_id;
		$zeit->kunde_uid = $kunde_uid;
		
		if(!$zeit->save())
		{
			echo '<span style="color:red"><b>'.$p->t("global/fehlerBeimSpeichernDerDaten").': '.$zeit->errormsg.'</b></span>';
		}
		else 
		{
			echo '<span style="color:green"><b>'.$p->t("global/datenWurdenGespeichert").'</b></span>';
	
			// Nach dem Speichern in den neu Modus springen und als Von Zeit
			// das Ende des letzten Eintrages eintragen
			$zeitaufzeichnung_id = '';
			$uid = $zeit->uid;
			$aktivitaet_kurzbz = '';
			$von = date('d.m.Y H:i', $datum->mktime_fromtimestamp($zeit->ende));
			$bis = date('d.m.Y H:i', $datum->mktime_fromtimestamp($zeit->ende)+3600);
			$beschreibung = '';
			$oe_kurzbz_1 = '';
			$oe_kurzbz_2 = '';
			$projekt_kurzbz = '';
			$service_id = '';
			$kunde_uid = '';
		}
	
	}
}

//Datensatz loeschen
if(isset($_GET['type']) && $_GET['type']=='delete')
{
	$zeit = new zeitaufzeichnung();
	
	if($zeit->load($zeitaufzeichnung_id))
	{

		if ($zeit->start < $sperrdatum)
			echo '<span style="color:red"><b>'.$p->t("global/fehlerBeimSpeichernDerDaten").': Eingabe nicht möglich da vor dem Sperrdatum</b></span>';		
		else 
		{
			if($zeit->uid==$user)
			{
				if($zeit->delete($zeitaufzeichnung_id))
					echo '<span style="color:orange"><b>'.$p->t("global/eintragWurdeGeloescht").'</b></span>';
				else 
					echo '<span style="color:red"><b>'.$p->t("global/fehlerBeimLoeschenDesEintrags").'</b></span>';
			}
			else 
				echo '<span style="color:red"><b>'.$p->t("global/keineBerechtigung").'!</b></span>';
		}
	}
	else 
		echo '<span style="color:red"><b>'.$p->t("global/datensatzWurdeNichtGefunden").'</b></span>';
}
else 
	echo '<b>&nbsp;</b>';

//Laden der Daten zum aendern
if(isset($_GET['type']) && $_GET['type']=='edit')
{
	$zeit = new zeitaufzeichnung();
	
	if($zeit->load($zeitaufzeichnung_id))
	{
		if($zeit->uid==$user)
		{
			$uid = $zeit->uid;
			$aktivitaet_kurzbz = $zeit->aktivitaet_kurzbz;
			$von = date('d.m.Y H:i', $datum->mktime_fromtimestamp($zeit->start));
			$bis = date('d.m.Y H:i', $datum->mktime_fromtimestamp($zeit->ende));
			$beschreibung = $zeit->beschreibung;
			$oe_kurzbz_1 = $zeit->oe_kurzbz_1;
			$oe_kurzbz_2 = $zeit->oe_kurzbz_2;
			$projekt_kurzbz = $zeit->projekt_kurzbz;
			$service_id = $zeit->service_id;
			$kunde_uid = $zeit->kunde_uid;
		}
		else 
		{
			echo "<b>".$p->t("global/keineBerechtigungZumAendernDesDatensatzes")."</b>";
			$zeitaufzeichnung_id='';
		}
	}
}

//Projekte holen zu denen der Benutzer zugeteilt ist
$projekt = new projekt();


if($projekt->getProjekteMitarbeiter($user, true))
{
	//if(count($projekt->result)>0)
	//{

		echo "<table width='100%'>
				<tr>
		      		<td>
		      			<a href='".$_SERVER['PHP_SELF']."' style='font-size: larger;'>".$p->t("zeitaufzeichnung/neu")."</a>
		      			&nbsp;
		      			<a href='".$_SERVER['PHP_SELF']."?csvimport=1' style='font-size: larger;'>CSV Import</a>
		      		</td>
		      		<td class='menubox' height='10px'>";
		if ($p->t("dms_link/handbuchZeitaufzeichnung")!='')
		{
			echo '<p><a href="../../../cms/dms.php?id='.$p->t("dms_link/handbuchZeitaufzeichnung").'" target="_blank">'.$p->t("zeitaufzeichnung/handbuchZeitaufzeichnung").'</a></p>';
		}
		if ($p->t("dms_link/fiktiveNormalarbeitszeit")!='')
		{
			echo '<p><a href="../../../cms/dms.php?id='.$p->t("dms_link/fiktiveNormalarbeitszeit").'" target="_blank">'.$p->t("zeitaufzeichnung/fiktiveNormalarbeitszeit").'</a></p>';
		}
		echo '<p><a href="../profile/zeitsperre_resturlaub.php">'.$p->t("urlaubstool/meineZeitsperren").'</a></p>';	
		echo "</td>
		      	</tr>
		      </table>";
		
		//Formular
		echo '<br><form action="'.$_SERVER['PHP_SELF'].'?zeitaufzeichnung_id='.$zeitaufzeichnung_id.'" method="POST" onsubmit="return checkdatum()" enctype="multipart/form-data">';

		echo '<table>
			<tr>
				<td rowspan="2">';		
		echo '<table>';
		if($za_simple == 0)
		{
		//Projekt
		echo '<tr>
				<td>'.$p->t("zeitaufzeichnung/projekt").'</td>
				<td colspan="4"><SELECT name="projekt" id="projekt">
					<OPTION value="">-- '.$p->t('zeitaufzeichnung/keineAuswahl').' --</OPTION>';
		
		sort($projekt->result);
		foreach($projekt->result as $row_projekt)
		{
			if($projekt_kurzbz == $row_projekt->projekt_kurzbz || $filter == $row_projekt->projekt_kurzbz)
				$selected = 'selected';
			else 
				$selected = '';
			
			echo '<option value="'.$db->convert_html_chars($row_projekt->projekt_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($row_projekt->titel).'</option>';
		}
		echo '</SELECT><!--<input type="button" value="'.$p->t("zeitaufzeichnung/uebersicht").'" onclick="loaduebersicht();">--></td>';
		echo '</tr><tr>';
		//OE_KURZBZ_1
		echo '<td nowrap>'.$p->t("zeitaufzeichnung/organisationseinheiten").'</td>
			<td colspan="3"><SELECT style="width:200px;" name="oe_kurzbz_1">';
		$oe = new organisationseinheit();
		$oe->getFrequent($user,'180','3',true);
		$trennlinie = true;
		
		echo '<option value="">-- '.$p->t("zeitaufzeichnung/keineAuswahl").' --</option>';
		
		foreach ($oe->result as $row)
		{
			if($row->oe_kurzbz == $oe_kurzbz_1)
				$selected = 'selected';
			else 
				$selected = '';
			if($row->aktiv)
				$class='';
			else
				$class='class="inaktiv"';
		
			if ($row->anzahl =='0' && $trennlinie==true)
			{
				echo '<OPTION value="" disabled="disabled">------------------------</OPTION>';
				$trennlinie = false;
			}
			echo '<option value="'.$db->convert_html_chars($row->oe_kurzbz).'" '.$selected.' '.$class.'>'.$db->convert_html_chars($row->bezeichnung.' ('.$row->organisationseinheittyp_kurzbz).')</option>';
		}
		echo '</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	
		//OE_KURZBZ_2
		echo '<SELECT style="width:200px;" name="oe_kurzbz_2">';
		echo '<option value="">-- '.$p->t("zeitaufzeichnung/keineAuswahl").' --</option>';
		
		$trennlinie = true;
		
		foreach ($oe->result as $row)
		{
			if($oe_kurzbz_2 == $row->oe_kurzbz)
				$selected = 'selected';
			else 
				$selected = '';
			
			if($row->aktiv)
				$class='';
			else
				$class='class="inaktiv"';

			if ($row->anzahl =='0' && $trennlinie==true)
			{
				echo '<OPTION value="" disabled="disabled">------------------------</OPTION>';
				$trennlinie = false;
			}	
			echo '<option value="'.$db->convert_html_chars($row->oe_kurzbz).'" '.$selected.' '.$class.'>'.$db->convert_html_chars($row->bezeichnung.' ('.$row->organisationseinheittyp_kurzbz).')</option>';
		}
		echo '</SELECT></td></tr>';
		}
		
		//Aktivitaet
		echo '<tr>';
		echo '<td>'.$p->t("zeitaufzeichnung/aktivitaet").'</td><td colspan="4">';
		//if ($za_simple == 1)
			$qry = "SELECT * FROM fue.tbl_aktivitaet where aktivitaet_kurzbz in (".$activities_str.") ORDER by sort,beschreibung";
		//else 
		//	$qry = "SELECT * FROM fue.tbl_aktivitaet where sort != 5 or sort is null ORDER by sort,beschreibung";
		if($result = $db->db_query($qry))
		{
			echo '<SELECT name="aktivitaet">';			
			if ($za_simple == 0)			
				echo '<OPTION value="">-- '.$p->t('zeitaufzeichnung/keineAuswahl').' --</OPTION>';
			//else 				
			//	echo '<OPTION value="Arbeit">Arbeit</OPTION>';
			while($row = $db->db_fetch_object($result))
			{
				if($aktivitaet_kurzbz == $row->aktivitaet_kurzbz)
					$selected = 'selected';
				else
					$selected = '';
				
				echo '<OPTION value="'.$db->convert_html_chars($row->aktivitaet_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($row->beschreibung).'</option>';
			}
			echo '</SELECT>';
		}
		echo '</td></tr>';
		
		if ($za_simple == 0)
		{
		// Service
		echo '<tr>
			<td>'.$p->t('zeitaufzeichnung/service').'</td>
			<td colspan="4"><SELECT name="service_id">
			<OPTION value="">-- '.$p->t('zeitaufzeichnung/keineAuswahl').' --</OPTION>';
		$trennlinie = true;
		$service = new service();
		$service->getFrequentServices($user, '180','3');
		foreach($service->result as $row)
		{
			if($row->service_id==$service_id)
				$selected='selected';
			else
				$selected='';

			if ($row->anzahl =='0' && $trennlinie==true)
			{
				echo '<OPTION value="" disabled="disabled">------------------------</OPTION>';
				$trennlinie = false;
			}
			echo '<OPTION title="'.$db->convert_html_chars($row->beschreibung).'" value="'.$db->convert_html_chars($row->service_id).'" '.$selected.'>'.$db->convert_html_chars($row->bezeichnung.' ('.$row->oe_kurzbz.')').'</OPTION>';
		}
		echo '</SELECT></td>
			</tr>';
        
        // person für Kundenvoransicht laden
        $kunde_name = '';
        if($kunde_uid != '')
        {
            $user_kunde = new benutzer(); 
            
            if($user_kunde->load($kunde_uid))
                $kunde_name=$user_kunde->vorname.' '.$user_kunde->nachname; 
        }
        echo '
        <tr>
            <td>'.$p->t("zeitaufzeichnung/kunde").'</td>
            <td colspan="3"><input type="text" id="kunde_name" value="'.$kunde_name.'" placeholder="'.$p->t("zeitaufzeichnung/nameEingeben").'"><input type ="hidden" id="kunde_uid" name="kunde_uid" value="'.$kunde_uid.'"> '.$p->t("zeitaufzeichnung/oderKartennummerOptional").' 
            <input type="text" id="kartennummer" name="kartennummer" placeholder="'.$p->t("zeitaufzeichnung/kartennummer").'"></td>
        </tr>'; 
		echo '<tr><td colspan="4">&nbsp;</td></tr>';
		}
		
		//Start/Ende
		$von_ts = $datum->mktime_fromtimestamp($datum->formatDatum($von, $format='Y-m-d H:i:s'));
		$bis_ts = $datum->mktime_fromtimestamp($datum->formatDatum($bis, $format='Y-m-d H:i:s'));
		$diff = $bis_ts - $von_ts;
		echo '
		<tr>
			<td>'.$p->t("global/von").' - '.$p->t("global/bis").'</td>
			<td>
				<input type="text" class="datepicker_datum" id="von_datum" name="von_datum" value="'.$db->convert_html_chars($datum->formatDatum($von, $format='d.m.Y')).'" size="9">
				<input onchange="checkZeiten()" type="text" class="timepicker" id="von_uhrzeit" name="von_uhrzeit" value="'.$db->convert_html_chars($datum->formatDatum($von, $format='H:i')).'" size="4">
			</td>';		
		if ($za_simple == 0)
		{		
			echo '
					<td align="center">				
					<img style="vertical-align:bottom; cursor:pointer" src="../../../skin/images/timetable.png" title="'.$p->t("zeitaufzeichnung/aktuelleZeitLaden").'" onclick="setvondatum()">&nbsp;
					<img style="vertical-align:bottom; cursor:pointer" src="../../../skin/images/arrow-next.png" title="'.$p->t("zeitaufzeichnung/alsEndzeitUebernehmen").'" onclick="uebernehmen()">
				
				&nbsp;&nbsp;+
					<input type="text" style="width: 25px;" maxlength="3" id="diff" name="diff" value="'.$db->convert_html_chars($diff/60).'" oninput="addieren()">
					min.
	
					<img style="vertical-align:bottom; cursor:pointer" src="../../../skin/images/arrow-previous.png" title="'.$p->t("zeitaufzeichnung/alsStartzeitUebernehmen").'" onclick="uebernehmen1()">&nbsp;
					<img style="vertical-align:bottom; cursor:pointer" src="../../../skin/images/timetable.png" title="'.$p->t("zeitaufzeichnung/aktuelleZeitLaden").'" onclick="setbisdatum()">
				</td>';
		}
		else 
		{
			echo '<td align="center">&nbsp;-&nbsp;</td>';
		}
		echo '
			<td align="right">				
				<input type="text" class="datepicker_datum" id="bis_datum" name="bis_datum" value="'.$db->convert_html_chars($datum->formatDatum($bis, $format='d.m.Y')).'" size="9">
				<input onchange="checkZeiten()" type="text" class="timepicker" id="bis_uhrzeit" name="bis_uhrzeit" value="'.$db->convert_html_chars($datum->formatDatum($bis, $format='H:i')).'" size="4">
			</td>
		<tr>';
		//Beschreibung
		echo '<tr><td>'.$p->t("global/beschreibung").'</td><td colspan="3"><textarea style="font-size: 13px" name="beschreibung" cols="60" maxlength="256">'.$db->convert_html_chars($beschreibung).'</textarea></td></tr>';
		echo '<tr><td></td><td></td><td></td><td align="right">';
		//SpeichernButton
		if($zeitaufzeichnung_id=='')
			echo '<input type="submit" value="'.$p->t("global/speichern").'" name="save"></td></tr>';
		else 
		{
			echo '<input type="hidden" value="" name="'.($alle===true?'alle':'').'">'; 
			echo '<input type="submit" value="'.$p->t("global/aendern").'" name="edit">&nbsp;&nbsp;';
			echo '<input type="submit" value="'.$p->t("zeitaufzeichnung/alsNeuenEintragSpeichern").'" name="save"></td></tr>';
		}
		if (isset($_GET['csvimport']))
		{		
			echo '<tr><td colspan="4"><hr></td></tr>';		
			echo '<tr><td>CSV-Import</td><td colspan="2"><input type="file" name="csv" value="" /></td><td align="right"><input type="submit" value="Import" name="import"></td></tr>';	
			echo '<tr><td></td><td colspan="3">Informationen zum Format der CSV-Datei s. Leitfaden Arbeitszeitaufzeichnung</td></tr>';	
		}	
		else 
			echo '<input type="file" name="csv" value="" style="visibility:hidden">';
		echo '</table>';
		
		echo '</td><td valign="top"><span id="zeitsaldo"></span><br><br><div id="monatsliste"></span></td></tr>';
		
		echo '<tr><td style="float:right;">';
		
		// Summen Lehre anzeigen
		$bv = new bisverwendung();
		$bv->getLastAktVerwendung($user);
		$lehre_inkludiert = $bv->inkludierte_lehre;
		if (!$lehre_inkludiert)
			$lehre_inkludiert = 0;
		
		$stsem = new studiensemester();
		$sem_akt = $stsem->getakt();
		$lehre = new zeitaufzeichnung();		
		$l_arr = $lehre->getLehreForUser($user, $sem_akt);
		if ($l_arr["LehreAuftraege"]>0 || $l_arr["LehreIntern"] > 0 || $l_arr["LehreExtern"] > 0)
		{		
			if ($lehre_inkludiert == -1)
			{
				$l_extern_soll = 0;
				$lehre_inkludiert = $l_arr["LehreAuftraege"];
			}
			else			
				$l_extern_soll = $l_arr["LehreAuftraege"]-$lehre_inkludiert;
			$l_extern_soll_norm = $l_extern_soll/4*3;
			$lehre_inkludiert_norm = $lehre_inkludiert/4*3;
			echo '<table style="border: 1px solid gray">';
			echo '<tr><td colspan="3" style="border: 1px solid gray"><h3>Übersicht Lehre '.$sem_akt.'</h3></tr>';
			echo '<tr><td colspan="3" style="border: 1px solid gray">(in Stunden)</tr>';
			echo '<tr><td></td><td style="border: 1px solid gray">beauftragt (LE)</td><td style="border: 1px solid gray">gebucht</td></tr>';
			if ($lehre_inkludiert > 0 || $l_arr["LehreIntern"] > 0)			
				echo '<tr><td style="border: 1px solid gray">LehreIntern:</td><td align="right" style="border: 1px solid gray">'.$lehre_inkludiert_norm.' ('.$lehre_inkludiert.')</td><td align="right" style="border: 1px solid gray">'.$l_arr["LehreIntern"].'</td></tr>';
			if ($l_extern_soll > 0 || $l_arr["LehreExtern"] > 0)
				echo '<tr><td style="border: 1px solid gray">LehreExtern:</td><td align="right" style="border: 1px solid gray">'.$l_extern_soll_norm.' ('.$l_extern_soll.')</td><td align="right" style="border: 1px solid gray">'.$l_arr["LehreExtern"].'</td></tr>';

			echo '</table>';
		}
		echo '</td></tr>';
		echo '</table>';
 		
		echo '<hr>';
		echo '<h3>'.($alle===true?$p->t('zeitaufzeichnung/alleEintraege'):$p->t('zeitaufzeichnung/xTageAnsicht', array($angezeigte_tage))).'</h3>';
		if ($alle===true)		
			echo '<a href="?normal" style="text-decoration:none"><input type="button" value="'.$p->t('zeitaufzeichnung/xTageAnsicht', array($angezeigte_tage)).'"></a>';
		else 
			echo '<a href="?alle" style="text-decoration:none"><input type="button" value="'.$p->t('zeitaufzeichnung/alleAnzeigen').'"></a>';
		//echo '<input type="submit" value="'.($alle===true?$p->t('zeitaufzeichnung/xTageAnsicht', array($angezeigte_tage)):$p->t('zeitaufzeichnung/alleAnzeigen')).'" name="'.($alle===true?'normal':'alle').'">';
		
		echo '</form>';
		
		$za = new zeitaufzeichnung();
	    if(isset($_GET['filter']))
	    	$za->getListeProjekt($_GET['filter']);
	    else
	    {
	    	if ($alle==true)
	    		$za->getListeUserFull($user, '');
	    	else 
	    		$za->getListeUserFull($user, $angezeigte_tage);
	    }
	   
		$summe=0;
		
		if(count($za->result)>0)
		{
			//Uebersichtstabelle
			$woche=date('W');
			echo '
			<table id="t1" class="" style="width:100%">
				
					<tr>
						<th style="background-color: #8DBDD8;" align="center" class="{sorter: false}" colspan="13">'.$p->t("eventkalender/kw").' '.$woche.'</th>
					</tr>
					<tr>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/id").'</th>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/user").'</th>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/projekt").'</th>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/oe").' 1</th>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/oe").' 2</th>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/aktivitaet").'</th>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/service").'</th>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/start").'</th>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/ende").'</th>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/dauer").'</th>
						<th style="background-color:#DCE4EF" align="center">'.$p->t("global/beschreibung").'</th>
						<th style="background-color:#DCE4EF" align="center" colspan="2">'.$p->t("global/aktion").'</th>
		    		</tr>
		    	
		    ';
		    
		    $tag=null;
		   $woche=date('W');

			$tagessumme='00:00';
			$pausesumme='00:00';
			$wochensumme='00:00';
			$extlehrearr=array();
			$elsumme = '00:00';
			$datum_obj = new datum();
			$tagesbeginn = '';
			$tagesende = '';
			$wochensaldo = '00:00';
			$pflichtpause = false;
			
			foreach($za->result as $row)
			{
				$datumtag = $datum_obj->formatDatum($row->datum, 'Y-m-d');
								
				//echo '<tr><th colspan="13">foo<th></tr>';				
				
				// Nach jedem Tag eine Summenzeile einfuegen
				if(is_null($tag))
					$tag = $datumtag;
				if($tag!=$datumtag)
				{
					//if ($row->uid)
					//{
						if ($datum->formatDatum($tag,'N') == '6' || $datum->formatDatum($tag,'N') == '7')
							$style = 'style="background-color:#eeeeee; font-size: 8pt;"';
						else 
							$style = 'style="background-color:#DCE4EF; font-size: 8pt;"';
						
						// zeitsperren anzeigen
						if (array_key_exists($datum->formatDatum($tag,'Y-m-d'), $zeitsperren))
						{
							$zeitsperre_text = " -- ".$zeitsperren[$datum->formatDatum($tag,'Y-m-d')]." -- ";
							$style = 'style="background-color:#cccccc; font-size: 8pt;"';
						}						
						else 
							$zeitsperre_text = '';
						//var_dump($zs->result);						
						if (isset($_GET["von_datum"]) && $datum->formatDatum($tag, 'd.m.Y') == $_GET["von_datum"])
							$style = 'style="border-top: 3px solid #8DBDD8; border-bottom: 3px solid #8DBDD8"';
							
						list($h1, $m1) = explode(':', $pausesumme);
						$pausesumme = $h1*3600+$m1*60;
						$tagessaldo = $datum->mktime_fromtimestamp($datum->formatDatum($tagesende, $format='Y-m-d H:i:s'))-$datum->mktime_fromtimestamp($datum->formatDatum($tagesbeginn, $format='Y-m-d H:i:s'))-3600;
						foreach($extlehrearr as $el)
						{
							if ($el["start"] > $tagesbeginn && $el["ende"] < $tagesende)
								$elsumme = $datum_obj->sumZeit($elsumme, $el["diff"]);
						}	
						list($h2, $m2) = explode(':', $elsumme);
						$elsumme = $h2*3600+$m2*60;					
						if ($tagessaldo > 18000 && $tagessaldo < 19800 && $pflichtpause==false && $elsumme == 0)
						{
							$pausesumme = $tagessaldo-18000;
						}
						else if ($tagessaldo>18000 && $pflichtpause==false && $elsumme == 0)
						{
							$pausesumme = $pausesumme+1800;
						}
						if ($elsumme > 0){
							$pausesumme = $pausesumme + $elsumme;
							$pflichtpause = true;
						}

						$tagessaldo = $tagessaldo-$pausesumme;
						$tagessaldo = date('H:i', ($tagessaldo));
						echo '<tr id="tag_row_'.$datum->formatDatum($tag,'d_m_Y').'"><td '.$style.' colspan="7">';
	
						// Zusaetzlicher span fuer Addon Informationen
						
						$lang = getSprache();
						if ($lang == 'German')
							$langindex = 1;
						else 
							$langindex = 2;
						echo '<b>'.$tagbez[$langindex][$datum->formatDatum($tag,'N')].' '.$datum->formatDatum($tag,'d.m.Y').'</b> <span id="tag_'.$datum->formatDatum($tag,'d_m_Y').'">'.$zeitsperre_text.'</span>';
	
						echo '</td>
				        <td align="right" colspan="2" '.$style.'>
				        	<b>'.$p->t("zeitaufzeichnung/arbeitszeit").': '.$datum->formatDatum($tagesbeginn, $format='H:i').'-'.$datum->formatDatum($tagesende, $format='H:i').' '.$p->t("eventkalender/uhr").'</b><br>LehreExtern / 
				        	'.$p->t("zeitaufzeichnung/pause").' '.($pflichtpause==false?$p->t("zeitaufzeichnung/inklusivePflichtpause"):'').':
				        </td>
				        <td '.$style.' align="right"><b>'.$tagessaldo.'</b><br>'.date('H:i', ($pausesumme-3600)).'</td>
				        <td '.$style.' colspan="3" align="right">';
						if ($tag > $sperrdatum)				      
				      	echo '<a href="?von_datum='.$datum->formatDatum($tag,'d.m.Y').'&bis_datum='.$datum->formatDatum($tag,'d.m.Y').'" class="item">&lt;-</a>';
				      
				      echo '</td>';
						
						$tag=$datumtag;
						$tagessumme='00:00';
						$pausesumme='00:00';
						$elsumme='00:00';
						$extlehrearr = array();
						$tagesbeginn = '';
						$tagesende = '';
						$pflichtpause = false;
						$wochensaldo = $datum_obj->sumZeit($wochensaldo,$tagessaldo );
					//}
					//else
					//{
					//	echo '<tr><td style="background-color:#DCE4EF; font-size: 8pt;" colspan="13"><b>'.$datum->formatDatum($row->datum,'D d.m.Y').'</b></b> <span id="tag_'.$datum->formatDatum($row->datum,'d_m_Y').'"></span></td></tr>';
					//}
				}
				// Nach jeder Woche eine Summenzeile einfuegen und eine neue Tabelle beginnen
				$datumwoche = $datum_obj->formatDatum($row->datum, 'W');
				if(is_null($woche))
					$woche = $datumwoche;
				if($woche!=$datumwoche)
				{					
					echo '
					
					
							<tr>
								<th colspan="7" style="background-color: #8DBDD8;"></th>
								<th style="background-color: #8DBDD8;" align="right" colspan="2" style="font-weight: normal;"><b>'.$p->t("zeitaufzeichnung/wochensummeArbeitszeit").':</b></th>
								<th style="background-color: #8DBDD8;" align="right" style="font-weight: normal;"><b>'.$wochensaldo.'</b></th>
								<th style="background-color: #8DBDD8;" colspan="3"></th>
							</tr>
							
					
					<!--</table>-->';

					echo '
					<!--<table id="t'.$datumwoche.'" class="tablesorter">-->
					<tr><th colspan="13">&nbsp;</th></tr>
						
							<tr>
								<th style="background-color: #8DBDD8;" align="center" class="{sorter: false}" colspan="13">'.$p->t("eventkalender/kw").' '.$datumwoche.'</th>
							</tr>
							<tr>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/id").'</th>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/user").'</th>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/projekt").'</th>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/oe").' 1</th>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/oe").' 2</th>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/aktivitaet").'</th>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/service").'</th>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/start").'</th>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/ende").'</th>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("zeitaufzeichnung/dauer").'</th>
								<th style="background-color:#DCE4EF" align="center">'.$p->t("global/beschreibung").'</th>
								<th style="background-color:#DCE4EF" align="center" colspan="2">'.$p->t("global/aktion").'</th>
							</tr>
						
					';


					$woche=$datumwoche;
					$wochensumme='00:00';
					$tagessumme='00:00';
					$pausesumme='00:00';
					$wochensaldo = '00:00';
				}
				if ($row->uid)
				{
				$wochensumme = $datum_obj->sumZeit($wochensumme, $row->diff);
				if ($row->aktivitaet_kurzbz=='Pause')
				{
					$pausesumme = $datum_obj->sumZeit($pausesumme, $row->diff);
					list($h1, $m1) = explode(':', $row->diff);
					if ($m1>=30 || $h1>0)
					{
						$pflichtpause = true;
					}
				}
				else
					$tagessumme = $datum_obj->sumZeit($tagessumme, $row->diff);
				$style = '';
				if ($row->zeitaufzeichnung_id == $zeitaufzeichnung_id)
					$style = 'style="border-top: 3px solid #8DBDD8; border-bottom: 3px solid #8DBDD8"';
				if ($row->aktivitaet_kurzbz=='Pause' || $row->aktivitaet_kurzbz=='LehreExtern')
					$style .= ' style="color: grey;"';
				$summe = $row->summe;
				$service = new service();
				$service->load($row->service_id);
				echo '<tr>
					<td '.$style.'>'.$db->convert_html_chars($row->zeitaufzeichnung_id).'</td>
					<td '.$style.'>'.$db->convert_html_chars($row->uid).'</td>
					<td '.$style.'>'.$db->convert_html_chars($row->projekt_kurzbz).'</td>
					<td '.$style.'>'.$db->convert_html_chars($row->oe_kurzbz_1).'</td>
			        <td '.$style.'>'.$db->convert_html_chars($row->oe_kurzbz_2).'</td>
			        <td '.$style.'>'.$db->convert_html_chars($row->aktivitaet_kurzbz).'</td>
			        <td '.$style.' title="'.$service->bezeichnung.'">'.StringCut($db->convert_html_chars($service->bezeichnung),20,null,'...').'</td>
			        <td '.$style.' nowrap>'.date('H:i', $datum->mktime_fromtimestamp($row->start)).'</td>
			        <td '.$style.' nowrap>'.date('H:i', $datum->mktime_fromtimestamp($row->ende)).'</td>
			        <td '.$style.' align="right">'.$db->convert_html_chars($row->diff).'</td>
			        <td '.$style.' title="'.$db->convert_html_chars(mb_eregi_replace("\r\n",' ',$row->beschreibung)).'">'.StringCut($db->convert_html_chars($row->beschreibung),20,null,'...').'</td>
			        <td '.$style.'>';
		        if(!isset($_GET['filter']) && ($row->uid==$user && $row->datum > $sperrdatum))
		        	echo '<a href="'.$_SERVER['PHP_SELF'].'?type=edit&zeitaufzeichnung_id='.$row->zeitaufzeichnung_id.'" class="Item">'.$p->t("global/bearbeiten").'</a>';
		        echo "</td>\n";
		        echo "       <td ".$style.">";
		        if(!isset($_GET['filter']) && ($row->uid==$user && $row->start > $sperrdatum))
		        	echo '<a href="'.$_SERVER['PHP_SELF'].'?type=delete&zeitaufzeichnung_id='.$row->zeitaufzeichnung_id.'" class="Item"  onclick="return confdel()">'.$p->t("global/loeschen").'</a>';
		        echo "</td>\n";
		        echo "   </tr>\n";
		        
		        if (($tagesbeginn=='' || $datum->mktime_fromtimestamp($datum->formatDatum($tagesbeginn, $format='Y-m-d H:i:s')) > $datum->mktime_fromtimestamp($datum->formatDatum($row->start, $format='Y-m-d H:i:s'))) && $row->aktivitaet_kurzbz != 'LehreExtern')
					$tagesbeginn = $row->start;
					
				if (($tagesende=='' || $datum->mktime_fromtimestamp($datum->formatDatum($tagesende, $format='Y-m-d H:i:s')) < $datum->mktime_fromtimestamp($datum->formatDatum($row->ende, $format='Y-m-d H:i:s'))) && $row->aktivitaet_kurzbz != 'LehreExtern')
					$tagesende = $row->ende;
				if ($row->aktivitaet_kurzbz == 'LehreExtern')				
					$extlehrearr[] = array("start"=>$row->start, "ende"=>$row->ende, "diff"=>$row->diff);
				}	    
		    }
			echo '';
			if ($alle===false)
			{				
				echo	'
								<tr>
									<th align="center" colspan="13">'.$p->t('zeitaufzeichnung/endeXTageAnsicht', array($angezeigte_tage)).'</th>
								</tr>
						';
			}
			//echo '</table>';
		
	    //echo $p->t("zeitaufzeichnung/gesamtdauer").": ".$db->convert_html_chars($summe); Aukommentiert. Irrelevant
		}
		echo '</table>';
	/* 
	}
	else 
	{
		echo $p->t("zeitaufzeichnung/sieSindDerzeitKeinenProjektenZugeordnet");
	}
	*/
}
else 
{
	echo $p->t("zeitaufzeichnung/fehlerBeimErmittelnDerProjekte");
}



echo '
<span id="globalmessages"></span>
</body>
</html>'; 
?>
