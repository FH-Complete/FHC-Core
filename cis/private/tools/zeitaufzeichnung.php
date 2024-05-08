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
 *          Manfred Kindl <kindlm@technikum.wien.at>
 *          Gerald Raab <raab@technikum-wien.at>
 * 			Alexei Karpenko <karpenko@technikum-wien.at>
 *			Manuela Thamer <manuela.thamer@technikum-wien.at>
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
require_once('../../../include/projektphase.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/service.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/betriebsmittelperson.class.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/zeitaufzeichnung_import_csv.class.php');
require_once('../../../include/zeitaufzeichnung_import_post.class.php');
require_once('../../../include/vertragsbestandteil.class.php');

$sprache = getSprache();
$p=new phrasen($sprache);
$sprache_obj = new sprache();
$sprache_obj->load($sprache);
$sprache_index=$sprache_obj->index;

if (!$db = new basis_db())
	die($p->t("global/fehlerBeimOeffnenDerDatenbankverbindung"));

$user = get_uid();

$passuid = false;
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$mas = new mitarbeiter();
$mas->getUntergebene($user, true);
$untergebenen_arr = array();
$untergebenen_arr = $mas->untergebene;
$adminView = false;

//Wenn User Administrator ist und UID uebergeben wurde, dann die Zeitaufzeichnung
//des uebergebenen Users anzeigen
if(isset($_GET['uid']))
{
	if ($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('mitarbeiter/urlaube', null, 'suid') ||
	(in_array($_GET['uid'], $untergebenen_arr)))
	{
		$user = $_GET['uid'];
		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($user);
		$passuid = true;
		$adminView = true;
	}
	else
	{
		die($p->t('global/FuerDieseAktionBenoetigenSieAdministrationsrechte'));
	}
}
if($rechte->isBerechtigt('addon/casetimeGenerateXLS'))
	$export_xls = 'true';
else {
	$export_xls = 'false';
}

$datum = new datum();

$fieldheadings = array(
	'id' => $p->t("zeitaufzeichnung/id"), 'user' => $p->t("zeitaufzeichnung/user"), 'projekt' => $p->t("zeitaufzeichnung/projekt"), 'ap' => $p->t("zeitaufzeichnung/projektphase"),
	'oe1' => $p->t("zeitaufzeichnung/oe"), 'oe2' => $p->t("zeitaufzeichnung/oe").'2', 'aktivitaet' => $p->t("zeitaufzeichnung/aktivitaet"),
	'service' => $p->t("zeitaufzeichnung/service"), 'start' => $p->t("zeitaufzeichnung/start"), 'ende' => $p->t("zeitaufzeichnung/ende"),
	'dauer' => $p->t("zeitaufzeichnung/dauer"), 'kunde' => $p->t("zeitaufzeichnung/kunde"), 'beschreibung' => $p->t("global/beschreibung"), 'aktion' => $p->t("global/aktion"),
	'datum' => $p->t("global/datum"),'homeoffice' => $p->t("zeitaufzeichnung/homeoffice")
);

if ($rechte->isBerechtigt('basis/servicezeitaufzeichnung'))
{
	$za_simple = 0;
	$activities = 	array('Design', 'Operativ', 'Betrieb',  'Pause', 'FuE','FuEallg', 'Lehre', 'Arztbesuch', 'DienstreiseMT', 'Behoerde', 'Ersatzruhe', 'Weiterbildung', 'LVEntwicklung');
}
else
{
	$za_simple = 1;
	$activities = array('Admin', 'FuE','FuEallg','Lehre', 'Pause', 'Arztbesuch', 'DienstreiseMT', 'Behoerde', 'Ersatzruhe', 'Weiterbildung', 'LVEntwicklung');
}

$activities_str = "'".implode("','", $activities)."'";

// definiert bis zu welchem Datum die Eintragung nicht mehr möglich ist
$zasperre = new zeitaufzeichnung();
if ($sperrdat = $zasperre->getEintragungGesperrtBisForUser($user))
	$gesperrt_bis = $sperrdat;
else if (defined('CIS_ZEITAUFZEICHNUNG_GESPERRT_BIS') && CIS_ZEITAUFZEICHNUNG_GESPERRT_BIS != '')
	$gesperrt_bis = CIS_ZEITAUFZEICHNUNG_GESPERRT_BIS;
else
	$gesperrt_bis = '2015-08-31';

$sperrdatum = date('c', strtotime($gesperrt_bis));

// Uses urlencode to avoid XSS issues
$zeitaufzeichnung_id = urlencode(isset($_GET['zeitaufzeichnung_id'])?$_GET['zeitaufzeichnung_id']:'');
$projekt_kurzbz = (isset($_POST['projekt'])?$_POST['projekt']:'');
$projektphase_id = (isset($_POST['projektphase'])?$_POST['projektphase']:'');
$oe_kurzbz_1 = (isset($_POST['oe_kurzbz_1'])?$_POST['oe_kurzbz_1']:'');
$oe_kurzbz_2 = (isset($_POST['oe_kurzbz_2'])?$_POST['oe_kurzbz_2']:'');
$aktivitaet_kurzbz = (isset($_POST['aktivitaet'])?$_POST['aktivitaet']:'');
$von_datum = (isset($_REQUEST['von_datum'])?$_REQUEST['von_datum']:date('d.m.Y'));
$von_uhrzeit = (isset($_POST['von_uhrzeit'])?$_POST['von_uhrzeit']:date('H:i'));
$von = $von_datum.' '.$von_uhrzeit;
$bis_datum = (isset($_REQUEST['bis_datum'])?$_REQUEST['bis_datum']:date('d.m.Y'));
$bis_uhrzeit = (isset($_POST['bis_uhrzeit'])?$_POST['bis_uhrzeit']:date('H:i',mktime(date('H'), date('i')+10)));
$bis = $bis_datum.' '.$bis_uhrzeit;

$homeoffice = (isset($_POST['homeoffice'])?true:false);
$pause_von = (isset($_POST['pause_von'])?$_POST['pause_von']:date('H:i'));
$pause_bis = (isset($_POST['pause_bis'])?$_POST['pause_bis']:date('H:i'));
$von_pause = $von_datum.' '.$pause_von;
$bis_pause = $bis_datum.' '.$pause_bis;
$homeofficeChecked = '';
//$reload = false;

$beschreibung = (isset($_POST['beschreibung'])?$_POST['beschreibung']:'');
$service_id = (isset($_POST['service_id'])?$_POST['service_id']:'');
$kunde_uid = (isset($_POST['kunde_uid'])?$_POST['kunde_uid']:'');
$kartennummer = (isset($_POST['kartennummer'])?$_POST['kartennummer']:'');
$filter = (isset($_GET['filter'])?$_GET['filter']:'foo');
$alle = (isset($_GET['alle'])?(isset($_GET['normal'])?false:true):false);
$angezeigte_tage = '50';

$zs = new zeitsperre();
if ($alle)
	$zs->getZeitsperrenForZeitaufzeichnung($user,'360');
else
	$zs->getZeitsperrenForZeitaufzeichnung($user,$angezeigte_tage);

$zeitsperren = $zs->result;

$bn = new benutzer();
if(!$bn->load($user))
	die($p->t("zeitaufzeichnung/benutzerWurdeNichtGefunden",array($user)));

//CSV export - Konflikt mit normalen HTML headern deshalb weiter vorne
if(isset($_POST['export']))
{
	if(isset($_POST['exp_von_datum']) && isset($_POST['exp_bis_datum']))
	{
		$datevon = $datum->formatDatum($_POST['exp_von_datum'], 'Y-m-d');
		$datebis = $datum->formatDatum($_POST['exp_bis_datum'], 'Y-m-d');
		$ztauf = getZeitaufzeichnung( $user, $datevon, $datebis);
		exportAsCSV($ztauf->result, ',', $fieldheadings, $za_simple, $user);
	}
}

//CSV export für Übersicht zugeteilter Projekte - Konflikt mit normalen HTML headern deshalb weiter vorne
if(isset($_GET['projektübersichtexport']))
{
	exportProjectOverviewAsCSV($user, ',');
}

echo '<!DOCTYPE HTML>
<html>
	<head>
		<title>'.$p->t("zeitaufzeichnung/zeitaufzeichnung").'</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
		<link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
		<link href="../../../skin/jquery.css" rel="stylesheet" type="text/css"/>
		<link href="../../../vendor/fgelinas/timepicker/jquery.ui.timepicker.css" rel="stylesheet" type="text/css"/>
        <link href="../../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet"  type="text/css">
		<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
        <script src="../../../vendor/fgelinas/timepicker/jquery.ui.timepicker.js" type="text/javascript" ></script>
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
			addon[i].init("cis/private/tools/zeitaufzeichnung.php", {uid:\''.$user.'\',exportXLS:'.$export_xls.'});
		}
	}
});
</script>
';

echo <<<EOSBJS
<script>
	$(document).ready(function() {
        const scrollDiv = document.createElement('div');
        scrollDiv.style.cssText = 'width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;';
        document.body.appendChild(scrollDiv);
        const scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
        document.body.removeChild(scrollDiv);
		var marginright = Math.max((20 - scrollbarWidth), 0);
        document.body.style.setProperty('width', 'calc(100% - ' + marginright + 'px)');
    });
</script>

EOSBJS;

echo '
        <script type="text/javascript">
		$(document).ready(function()
		{
			//resetProjekt()
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

			checkPausenblock();

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

			$("#projekt").change(
				function()
				{
					var uid = $("#uidpass").val();
					getProjektphasen($(this).val(),uid);
				}
			)

			useCheckedDate();

			$("#von_datum").change(
				function()
				{
					var uid = $("#uidpass").val();
					var Datum = $(this).val();
					$("#triggerPhasenReset").text("");
					Tag=Datum.substring(0,2);
					Monat=Datum.substring(3,5);
					Jahr=Datum.substring(6,10);
					var day = Jahr + "-" + Monat + "-" + Tag;
					checkZeitaufzeichnung(day,uid);
					checkZeitsperre(day,uid);
				}
			)

			function isVisible()
			{
				resetPhasen()
			}

			$("#triggerPhasenReset").bind("isVisible", isVisible);

			$("#triggerPhasenReset").show("slow", function()
			{
				$(this).trigger("isVisible");
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
			$("#von_datum").trigger("change");
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
			$("#von_datum").trigger("change");
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

			a = document.getElementById("aktivitaet");
			akt = a.options[a.selectedIndex].value;
			//alert(akt);

			if (bisDatum>vonDatum)
			{
				if (diff>9999 && akt != "DienstreiseMT")
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

		/**
		* kontrolliert Start- und Enddatum für CSV Export -
		* ob Startdatum nicht größer als Enddatum ist und ob die Zeitspanne nicht größer als 1000 Tage ist
		*/
		function checkdatumCSVExp(vondatumid, bisdatumid)
		{
			var Datum,Tag,Monat,Jahr,vonDatum,bisDatum,diff;

			Datum=document.getElementById(vondatumid).value;
		    Tag=Datum.substring(0,2);
		    Monat=Datum.substring(3,5);
		    Jahr=Datum.substring(6,10);
		    vonDatum=Jahr+\'\'+Monat+\'\'+Tag;

		    Datum=document.getElementById(bisdatumid).value;
		    Tag=Datum.substring(0,2);
		    Monat=Datum.substring(3,5);
		    Jahr=Datum.substring(6,10);
		    bisDatum=Jahr+\'\'+Monat+\'\'+Tag;
		    diff=bisDatum-vonDatum;

			if (bisDatum>=vonDatum)
			{
				if (diff>2000 && bisDatum != "" && vonDatum != "")
				{
					Check = confirm("'.$p->t("zeitaufzeichnung/zeitraumAuffallendHoch").'");
					document.getElementById(bisdatumid).focus();
					if (Check == false)
				  		return false;
				  	else
				  		return true;
				}
			}
			else
			{
				if(bisDatum != "")
				{
					alert("'.$p->t("zeitaufzeichnung/bisDatumKleinerAlsVonDatum").'");
					document.getElementById(bisdatumid).focus();
					return false;
				}
			}
			return true;
		}

		function resetProjekt()
		{
			$("#projekt").val("");
			$("#projektphaseformgroup").hide();
		}

		function resetPhasen()
		{
			var uid = $("#uidpass").val();
			getProjektphasen($("#projekt").val(),uid);
		}

		function getProjektphasen(projekt_kurzbz, uid)
		{
			$.ajax
			(
				{
					type: "GET",
					url: "zeitaufzeichnung_projektphasen.php",
					dataType: "json",
					data:
					{
						"uid":uid,
						"projekt_kurzbz":projekt_kurzbz

					},
					success: function(json)
					{
						//remove Projektphasen from html if any
						$("#projektphase").children("option").each(
							function()
							{
								if ($(this).prop("id") !== "projektphasekeineausw")
									$(this).remove();
							}
						);
						//append Projektphasen if any
						if (json.length > 0)
						{
							var projphasenhtml = "";
							for (var i = 0; i < json.length; i++)
							{
								projphasenhtml += "<option value = \'" + json[i].projektphase_id + "\'>";
								projphasenhtml += json[i].bezeichnung;

								if(json[i].start != \'\' && json[i].ende !=\'\')
								{
									projphasenhtml += " ( "+json[i].start+" - "+json[i].ende+ " )";
								}
								if (!json[i].zeitaufzeichnung_erlaubt)
								{
										projphasenhtml += " ---AP NICHT bebuchbar---";
								}
								projphasenhtml += "<\/option>";
							}

							$("#projektphase").append(projphasenhtml);
							$("#projektphaseformgroup").show();
						}
						else
						{
							$("#projektphaseformgroup").hide();
						}
					}
				}
			);
		}

		function appendProjektphasenzeiten(projektphase_id)
		{
			$.ajax
			(
				{
					type: "GET",
					url: "zeitaufzeichnung_projektphasenzeiten.php",
					dataType: "json",
					data:
					{
						"projektphase_id":projektphase_id

					},
					success: function(json)
					{
						//append Projektphasenzeiten if any
						if (json.length > 0)
						{
							var projphasenhtml = "";
							for (var i = 0; i < json.length; i++)
							{
								projphasenhtml += "<option value = \'" + json[i].projektphase_id + "\'>";
								projphasenhtml += json[i].bezeichnung;
								if(json[i].start != \'\' && json[i].ende !=\'\')
								{
									projphasenhtml += " ( "+json[i].start+" - "+json[i].ende+" )";
								}
								projphasenhtml += "<\/option>";
							}
							$("#projektphase").append(projphasenhtml);
							$("#projektphaseformgroup").show();
						}
						else
						{
							$("#projektphaseformgroup").hide();
						}
					}
				}
			);
		}


		// Pausenblock

		function checkPausenblock()
		{
			var sel = $("#aktivitaet").val();
			var activities = ["Admin", "Lehre", "FuE", "Operativ", "Betrieb", "Design", "LVEntwicklung", "Weiterbildung", "FuEallg"];
			if (activities.includes(sel))
				showPausenblock();
			else
				hidePausenblock();
		}

		function hidePausenblock()
		{
			$("#pause_von").val("");
			$("#pause_bis").val("");
			$("#genPause").attr("checked", false);
			$("#pausenblock").hide();
		}
		function showPausenblock()
		{
			$("#pausenblock").show();
		}

		function checkPausenzeit()
		{
			if ($("#genPause").is(":checked"))
			{
				setPausenzeit();
			}
			else
			{
				clearPausenzeit();
			}
		}

		function setPausenzeit()
		{
			var von_stunden, bis_stunden, von_minuten, bis_minuten, Uhrzeit2, Uhrzeit1, spanne;
			Uhrzeit1 = $("#von_uhrzeit").val();
			von_stunden = Uhrzeit1.substring(0,2);
		    von_minuten = Uhrzeit1.substring(3,5);
			Uhrzeit2 = $("#bis_uhrzeit").val();
			bis_stunden = Uhrzeit2.substring(0,2);
		    bis_minuten = Uhrzeit2.substring(3,5);
			spanne = (bis_stunden*60+parseInt(bis_minuten))-(von_stunden*60+parseInt(von_minuten));

			if (spanne <= 40)
			{
				alert("'.$p->t("zeitaufzeichnung/zeitraumZuKurz").'");
				$("#genPause").attr("checked", false);
			}
			else
			{
				var pausenstart = Math.floor((spanne/2-15)+(von_stunden*60+parseInt(von_minuten)));
				if (pausenstart%15 !== 0)
				{
					pausenstart = Math.round(pausenstart/10)*10;
				}
				var pausenstart_stunde = Math.floor(pausenstart/60);
				var pausenstart_minute = pausenstart - pausenstart_stunde*60;
				pausenstart_stunde = (pausenstart_stunde < 10 ? "0"+pausenstart_stunde : pausenstart_stunde);
				pausenstart_minute = (pausenstart_minute < 10 ? "0"+pausenstart_minute : pausenstart_minute);
				var beginn_pause = pausenstart_stunde + ":" + pausenstart_minute;

				var pausenende = pausenstart + parseInt(30);
				var pausenende_stunde = Math.floor(pausenende/60);
				var pausenende_minute = pausenende - pausenende_stunde*60;
				pausenende_stunde = (pausenende_stunde < 10 ? "0"+pausenende_stunde : pausenende_stunde);
				pausenende_minute = (pausenende_minute < 10 ? "0"+pausenende_minute : pausenende_minute);

				var ende_pause = pausenende_stunde + ":" + pausenende_minute;

				$("#pause_von").val(beginn_pause);
				$("#pause_bis").val(ende_pause);
			}
		}

		function clearPausenzeit()
		{
			$("#pause_von").val("");
			$("#pause_bis").val("");
		}

		function useCheckedDate(){
			var uid = $("#uidpass").val();
			var Datum = $("#von_datum").val();
			Tag=Datum.substring(0,2);
			Monat=Datum.substring(3,5);
			Jahr=Datum.substring(6,10);
			var checkedDay = Jahr + "-" + Monat + "-" + Tag;
			checkZeitaufzeichnung(checkedDay, uid);
			checkZeitsperre(checkedDay, uid);
		}

		function checkZeitaufzeichnung(day, uid)
		{
			/* Checkt nicht mehr Bisverwendung, sondern Vertragsbestandteil Zeitaufzeichnung */
			$.ajax({
				url: "zeitaufzeichnung_bisverwendung.php",
				data: {
					day: day,
					uid: uid
				},
				success: function (result)
				{
					if (result==\'true\')
					{
						$("#homeofficeBlock").show();
					}
					else
					{
						$("#homeofficeBlock").hide();
					}
				}
			});
		}

		function checkZeitsperre(day, uid)
		{
			$.ajax
			({
				type: "GET",
				url: "zeitaufzeichnung_zeitsperren.php",
				dataType: "json",
				data:
				{
					day: day,
					uid: uid
				},
				success: function (json)
				{
					var output = "";
					if (json.length > 0)
					{
						output = "' . $p->t("zeitaufzeichnung/zeitsperreVorhanden", ['{day}', '{typ}']) . '";
						output = output.replace(\'{day}\', json[0].day);
						output = output.replace(\'{typ}\', json[0].typ);
						$("#errZeitsperren").show();
						$("#buttonSave").prop("disabled",true);
					}
					else
					{
						$("#errZeitsperren").hide();
						$("#buttonSave").prop("disabled",false);
						output = "";
					}
					$("#outputZeitsperren").html(output);
				}
			});
		}

		</script>
	</head>
<body>
';


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
	$projektph_of_user = new projektphase();
	$projektphasen = $projektph_of_user->getProjectphaseForMitarbeiter($user);

	$phaseBebuchbar = $projektph_of_user->getPhasenZA($projektphase_id);
	$projekt = new projekt();
	$projekt->load($projekt_kurzbz);

	if ($_FILES['csv']['error'] == 0 && isset($_POST['import']))
	{
		$zeit_csv_import = new zeitaufzeichnung_import_csv($p, $user, $sperrdatum, $_FILES['csv']['tmp_name']);
		$zeit_csv_import->import();
		echo $zeit_csv_import->OutputToHTML();
	}
	else if ($datum->formatDatum($von, $format='Y-m-d H:i:s') < $sperrdatum)
		echo '<span id="triggerPhasenReset" style="color:#ff0000"><b>' .$p->t("global/fehlerBeimSpeichernDerDaten").': Eingabe nicht möglich da vor dem Sperrdatum</b></span>';

	// NOTE(chris): Save
	else if (isset($_POST['save']) || isset($_POST['edit']))
	{

		$zeit_post_import = new zeitaufzeichnung_import_post($p, $user, isset($_POST['edit']), [
			'aktivitaet_kurzbz' => $aktivitaet_kurzbz,
			'beschreibung' => $beschreibung,
			'bis' => $bis,
			'bis_pause' => $bis_pause,
			'homeoffice' => $homeoffice,
			'kunde_uid' => $kunde_uid,
			'oe_kurzbz_1' => $oe_kurzbz_1,
			'oe_kurzbz_2' => $oe_kurzbz_2,
			'projekt_kurzbz' => $projekt_kurzbz,
			'projektphase_id' => $projektphase_id,
			'service_id' => $service_id,
			'von' => $von,
			'von_pause' => $von_pause,
			'zeitaufzeichnung_id' => $zeitaufzeichnung_id,
		]);

		$zeit_post_import->import();
		echo $zeit_post_import->OutputToHTML();
		if (!$zeit_post_import->hasErrors() && !$zeit_post_import->hasWarnings())
		{
			// Nach dem Speichern in den neu Modus springen und als Von Zeit
			// das Ende des letzten Eintrages eintragen
			$zeitaufzeichnung_id = '';
			$uid = $user;
			$aktivitaet_kurzbz = '';
			$von = date('d.m.Y H:i', $datum->mktime_fromtimestamp($datum->formatDatum($bis, $format = 'Y-m-d H:i:s')));
			$bis = date('d.m.Y H:i', $datum->mktime_fromtimestamp($datum->formatDatum($bis, $format = 'Y-m-d H:i:s'))+3600);
			$beschreibung = '';
			$oe_kurzbz_1 = '';
			$oe_kurzbz_2 = '';
			$projekt_kurzbz = '';
			$projektphase_id = '';
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
			echo '<span id="triggerPhasenReset" style="color:red"><b>'.$p->t("global/fehlerBeimSpeichernDerDaten").': Eingabe nicht möglich da vor dem Sperrdatum</b></span>';
		else
		{
			if($zeit->uid==$user)
			{
				if($zeit->delete($zeitaufzeichnung_id))
				{
					$zeitaufzeichnung_id = '';
					echo '<span style="color:orange"><b>'.$p->t("global/eintragWurdeGeloescht").'</b></span>';
				}
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
if (isset($_GET['type']) && $_GET['type'] == 'edit')
{
	$zeit = new zeitaufzeichnung();
	if ($zeit->load($zeitaufzeichnung_id))
	{
		if ($zeit->uid == $user)
		{
			$uid = $zeit->uid;
			$aktivitaet_kurzbz = $zeit->aktivitaet_kurzbz;
			$von = date('d.m.Y H:i', $datum->mktime_fromtimestamp($zeit->start));
			$bis = date('d.m.Y H:i', $datum->mktime_fromtimestamp($zeit->ende));
			$beschreibung = $zeit->beschreibung;
			$oe_kurzbz_1 = $zeit->oe_kurzbz_1;
			$oe_kurzbz_2 = $zeit->oe_kurzbz_2;
			$projekt_kurzbz = $zeit->projekt_kurzbz;
			$projektphase_id = $zeit->projektphase_id;
			$homeoffice = $zeit->homeoffice;
			$homeoffice[0] == 't' ? $homeofficeChecked = 'checked' : $homeofficeChecked = '';
			$service_id = $zeit->service_id;
			$kunde_uid = $zeit->kunde_uid;

			$projektphase = new projektphase();
			$projektphasen = array();

			if ($projektphase->getProjectphaseForMitarbeiterByKurzBz($uid, $projekt_kurzbz))
			{
				foreach ($projektphase->result as $row)
				{
					$projektphasen[] = $row;
				}
			}
		}
		else
		{
			echo "<b>".$p->t("global/keineBerechtigungZumAendernDesDatensatzes")."</b>";
			$zeitaufzeichnung_id = '';
		}
	}
}

//Projekte holen zu denen der Benutzer zugeteilt ist
$projekt = new projekt();

if ($projekt->getProjekteMitarbeiter($user, true))
{
	//if(count($projekt->result)>0)
	//{
		$anzprojekte = count($projekt->result);
		echo "<table width='100%'>
				<tr>
					<td>
						<a href='".$_SERVER['PHP_SELF']."' style='font-size: larger;'>".$p->t("zeitaufzeichnung/neu")."</a><a style='font-size: larger; text-decoration: none; cursor: default'> | </a>";

		if (!$adminView)
		{
			echo
			"
			<a href='".$_SERVER['PHP_SELF']."?csvimport=1' style='font-size: larger;'>CSV Import</a><a style='font-size: larger; text-decoration: none; cursor: default'> | </a>

			<a href='".$_SERVER['PHP_SELF']."?csvexport=1' style='font-size: larger;'>CSV Export</a><a style='font-size: larger; text-decoration: none; cursor: default'> | </a>
			";
		}

		echo "	<a href='".$_SERVER['PHP_SELF']."?projektübersichtexport=1".($passuid ? '&uid='.$user : '')."' style='font-size: larger;'>Projektübersichtexport</a>";
		      			if($anzprojekte > 0)
		      				echo "<a style='font-size: larger; text-decoration: none; cursor: default'> | </a><a href='".$_SERVER['PHP_SELF']."?projektexport=1".($passuid ? '&uid='.$user : '')."' style='font-size: larger;'>".$p->t("zeitaufzeichnung/projektexport")."</a>";
				echo "</td>
		      		<td class='menubox' height='10px'>";
		if ($p->t("dms_link/handbuchZeitaufzeichnung")!='')
		{
			// An der FHTW wird ins Moodle verlinkt
			if (CAMPUS_NAME == 'FH Technikum Wien')
				echo '<p><a href="https://moodle.technikum-wien.at/course/view.php?id=6251" target="_blank">'.$p->t("zeitaufzeichnung/handbuchZeitaufzeichnung").'</a></p>';
			else
				echo '<p><a href="../../../cms/dms.php?id='.$p->t("dms_link/handbuchZeitaufzeichnung").'" target="_blank">'.$p->t("zeitaufzeichnung/handbuchZeitaufzeichnung").'</a></p>';
		}
		if ($p->t("dms_link/fiktiveNormalarbeitszeit")!='')
		{
			echo '<p><a href="../../../cms/dms.php?id='.$p->t("dms_link/fiktiveNormalarbeitszeit").'" target="_blank">'.$p->t("zeitaufzeichnung/fiktiveNormalarbeitszeit").'</a></p>';
		}
		echo '<p><a href="../profile/zeitsperre_resturlaub.php">'.$p->t("urlaubstool/meineZeitsperren").'</a></p>';
		echo $p->t("zeitaufzeichnung/supportAnfragen");

		//Dropdown timesheets Mitarbeiter
		if ($untergebenen_arr)
		{
			$mitarbeiter = new mitarbeiter();
			$mitarbeiter->getMitarbeiterArray($untergebenen_arr);

			echo "
			<hr><br>

			<select name='mas' id='mas' onchange='location = this.options[this.selectedIndex].value;'>
				<option>-- Timesheets Mitarbeiter*Innen --</option>";
			foreach($mitarbeiter->result as $row)
			{
				echo "<option value='zeitaufzeichnung.php?uid=$row->uid'>$row->nachname $row->vorname</option>";
			}
			echo "<option value='zeitaufzeichnung.php'> --zurück zur Übersicht--</option>";
			echo "</select>";
		}
		echo '</td></tr>
			  </table>';
			echo '<table>
			<tr>
				<td rowspan="2">';
		echo '<table>';

		if (isset($_GET['projektexport']))
		{
			$projektexpurl = dirname($_SERVER["PHP_SELF"]) .'/zeitaufzeichnung_projektliste.php';
			$aktjahr = intval(date("Y"));
			$aktmonat = intval(date("m")) - 1;
			$jahreanz = 3;
			echo '<form action="'.$projektexpurl.'" method="GET">';
			echo '<tr><td colspan="4"><hr></td></tr>';
			echo '<tr><td>'.$p->t('zeitaufzeichnung/projektexport').'</td>';
			echo '<td align="center">'.$p->t('zeitaufzeichnung/monat').' <select id="projexpmonat" name="projexpmonat">';
			for ($i=1;$i<13;$i++)
			{
				$selected = ($i == $aktmonat)?'selected = "selected"':'';
				echo '<option value="'.$i.'" '.$selected.'>'.$monatsname[$sprache_index][$i - 1].'</option>';
			}
			echo '</select></td>';
			echo '<td align="center">'.$p->t('zeitaufzeichnung/jahr').' <select id="projexpjahr" name="projexpjahr">';
			for (;$jahreanz>0;$jahreanz--)
			{
				echo '<option value="'.$aktjahr.'">'.$aktjahr.'</option>';
				$aktjahr--;
			}
			echo '</select></td>';
			if ($passuid)
				echo '<input type="hidden" value="'.$user.'" name="uid">';
			echo '<td align="right"><input type="submit" value="Export" name="projexport"></td></tr>';
			echo '<tr><td colspan="4"><hr></td></tr>';
			echo '</form>';
		}

		//Formular
		echo '<br><form action="'.$_SERVER['PHP_SELF'].'?zeitaufzeichnung_id='.$zeitaufzeichnung_id.'" method="POST" onsubmit="return checkdatum()" enctype="multipart/form-data">';

		if (isset($_GET['csvimport']))
		{
			echo '<tr><td colspan="4"><hr></td></tr>';
			echo '<tr><td>CSV-Import</td><td colspan="2"><input type="file" name="csv" value="" /></td><td align="right"><input type="submit" value="Import" name="import"></td></tr>';
			echo '<tr><td></td><td colspan="3">Informationen zum Format der CSV-Datei s. Leitfaden Arbeitszeitaufzeichnung</td></tr>';
			echo '<tr><td colspan="4"><hr></td></tr>';
		}
		else
			echo '<input type="file" name="csv" value="" style="display:none">';

		if (isset($_GET['csvexport']))
		{
			echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" onsubmit="return checkdatumCSVExp(\'exp_von_datum\', \'exp_bis_datum\')">';
			echo '<tr><td colspan="4"><hr></td></tr>';
			echo '<tr><td>CSV-Export</td>';
			echo '<td>'.$p->t('zeitaufzeichnung/startdatum').' <input class="datepicker_datum" id="exp_von_datum" name="exp_von_datum" size="9" type="text" value="'.date('d.m.Y', strtotime('first day of previous month')).'" /></td>';
			echo '<td align="right">'.$p->t('zeitaufzeichnung/enddatum').' <input class="datepicker_datum" id="exp_bis_datum" name="exp_bis_datum" size="9" type="text"  value="'.date('d.m.Y', strtotime('last day of previous month')).'" /></td>';
			echo '<td align="right"><input type="submit" value="Export" name="export"></td></tr>';
			echo '<tr><td></td><td colspan="3"></td></tr>';
			echo '<tr><td colspan="4"><hr></td></tr>';
			echo '</form>';
		}

		if (isset($_GET['projektübersichtexport']))
		{
			echo '<tr><td colspan="4"><hr></td></tr>';
			echo '<tr><td>CSV-Export</td>';
			echo '<td align="right"><input type="submit" value="Projektübersichtexport" name="projektübersichtexport"></td></tr>';
			echo '<tr><td></td><td colspan="3"></td></tr>';
			echo '<tr><td colspan="4"><hr></td></tr>';
		}

		//Aktivitaet
		echo '<tr>';
		echo '<td>'.$p->t("zeitaufzeichnung/aktivitaet").'</td><td colspan="4">';

		$qry = "SELECT * FROM fue.tbl_aktivitaet where aktivitaet_kurzbz in (".$activities_str.") ORDER by sort,beschreibung";

		if($result = $db->db_query($qry))
		{
			echo '<SELECT name="aktivitaet" id="aktivitaet" onChange="checkPausenblock()">';

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


		if($za_simple >= 0)
		{
			$oestyle = '';
			if($za_simple == 0)
				$oestyle = 'style="width:200px;"';

			//OE_KURZBZ_1
			echo '<tr><td nowrap>'.$p->t("zeitaufzeichnung/organisationseinheiten").'</td>
				<td colspan="3"><SELECT '.$oestyle.' name="oe_kurzbz_1">';
			$oe = new organisationseinheit();
			$oe->getFrequent($user,'180','3',true, array('oezuordnung', 'fachzuordnung', 'kstzuordnung', 'Leitung'));
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
				echo '<option value="'.$db->convert_html_chars($row->oe_kurzbz).'" '.$selected.' '.$class.'>'.$db->convert_html_chars($row->bezeichnung.' ('.$row->organisationseinheittyp_kurzbz).') ['.$row->oe_kurzbz.']</option>';
			}
			echo '</SELECT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			if($za_simple == 0)
			{
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
				echo '</SELECT>';
			}
			echo '</td></tr>';
		}

		//Projekte werden nicht angezeigt wenn es keine gibt
		if($anzprojekte > 0)
		{
			//Projekt
			echo '<tr>
				<td>'.$p->t("zeitaufzeichnung/projekt").'</td>
				<td colspan="4"><SELECT name="projekt" id="projekt">
					<OPTION value="">-- '.$p->t('zeitaufzeichnung/keineAuswahl').' --</OPTION>';

			sort($projekt->result);
			$projektfound = false;
			foreach ($projekt->result as $row_projekt)
			{
				if ($projekt_kurzbz == $row_projekt->projekt_kurzbz || $filter == $row_projekt->projekt_kurzbz)
				{
					$projektfound = true;
					$selected = 'selected';
				}
				else
					$selected = '';

				echo '<option value="'.$db->convert_html_chars($row_projekt->projekt_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($row_projekt->titel).'</option>';
		}
		echo '</SELECT><!--<input type="button" value="'.$p->t("zeitaufzeichnung/uebersicht").'" onclick="loaduebersicht();">-->';

		//Projektphase
		$showprojphases = isset($projektphasen) && is_array($projektphasen) && count($projektphasen) > 0 && $projektfound;
		$hiddentext = $showprojphases ? "" : " style='display:none'";

		echo
			'<span id="projektphaseformgroup"'.$hiddentext.'>&nbsp;&nbsp;&nbsp;&nbsp;'.
			$p->t("zeitaufzeichnung/projektphase").'
				<SELECT name="projektphase" id="projektphase">
					<OPTION value="" id="projektphasekeineausw">-- '.$p->t('zeitaufzeichnung/keineAuswahl').' --</OPTION>';
		if ($showprojphases)
		{

			foreach ($projektphasen as $projektphase)
			{
				if (($projektphase->start != "" ) && ($projektphase->ende != " "))
				{
					$phasentext = " (". $datum->formatDatum($projektphase->start, 'd.m.Y'). " - ".
					$datum->formatDatum($projektphase->ende, 'd.m.Y'). ")";
				}
				else
				{
					$phasentext = '';
				}

				if ($projektphase_id == $projektphase->projektphase_id/* || $filter == $row_projekt->projekt_kurzbz*/)
					$selected = 'selected';
				else
					$selected = '';

				echo '<option value="'.$db->convert_html_chars($projektphase->projektphase_id).'" '.$selected.'>'.$db->convert_html_chars($projektphase->bezeichnung).
				$phasentext. '</option>';
			}

		}
		echo '</SELECT></span>';
		echo '</td></tr>';
	}

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
		<tr id="errZeitsperren">
		  <td>&nbsp;</td>
		  <td colspan="3">
		    <p id="outputZeitsperren" style="color:red; font-weight:bold;" ></p>
		  </td>
		</tr>
		<tr>
			<td>'.$p->t("global/von").' - '.$p->t("global/bis").'</td>
			<td>
				<input type="text" class="datepicker_datum" id="von_datum" name="von_datum" value="'.$db->convert_html_chars($datum->formatDatum($von, $format='d.m.Y')).'" size="9">
				<input onchange="checkZeiten()" type="text" class="timepicker" id="von_uhrzeit" name="von_uhrzeit" value="'.$db->convert_html_chars($datum->formatDatum($von, $format='H:i')).'" size="4">
			</td>';
		if ($za_simple == 0 || $anzprojekte > 0)
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
		echo '
		<tr>
			<td>&nbsp;</td>
			<td colspan="3">
				<span id="pausenblock">
					<input type="checkbox" name="genPause" id="genPause" onChange="checkPausenzeit()"> <label for="genPause">'.$p->t("zeitaufzeichnung/pauseEinfuegen").'</label> <input type="text" name="pause_von" class="timepicker" size="4" id="pause_von"> - <input type="text" name="pause_bis" class="timepicker" size="4" id="pause_bis">
				</span>
			</td>
		</tr>
		';

		//Homeoffice Checkbox
			echo '
			<tr>
				<td>&nbsp;</td>
				<td colspan="1">
					<span id="homeofficeBlock">
						<input type="checkbox" name="homeoffice" id="homeoffice" '. $homeofficeChecked . '> <label for="homeoffice">Homeoffice</label>
					</span>
				</td>
			</tr>

			';

			if (!$adminView)
				{
					//Beschreibung
					echo '<tr><td>'.$p->t("global/beschreibung").'</td><td colspan="3"><textarea style="font-size: 13px" name="beschreibung" cols="60" maxlength="256">'.$db->convert_html_chars($beschreibung).'</textarea></td></tr>';
					echo '<tr><td></td><td></td><td></td><td align="right">';
					//SpeichernButton
					if($zeitaufzeichnung_id == '')
						echo '<input id="buttonSave" type="submit" value="'.$p->t("global/speichern").'" name="save"></td></tr>';
					else
					{
						echo '<input type="hidden" value="" name="'.($alle===true?'alle':'').'">';
						echo '<input type="submit" value="'.$p->t("global/aendern").'" name="edit">&nbsp;&nbsp;';
						echo '<input type="submit" value="'.$p->t("zeitaufzeichnung/alsNeuenEintragSpeichern").'" name="save"></td></tr>';
					}
				}
					echo '</table>';
					echo '</td><td valign="top"><span id="zeitsaldo"></span><br>';

					$vbt = new vertragsbestandteil();
					$isAllin = $vbt->isAllin($user);

					if ($isAllin)
					{
						echo '<span id="saldoAllin"></span><br>';
					}

					echo '<br>';



				if (!$adminView)
				{
					echo '<div id="monatsliste">';
				}
				echo '</span></td></tr>';
				echo '<tr><td style="float:right;">';

		echo '</td></tr>';
		echo '</table>';
		echo '</form>';
		echo "<input type ='hidden' value='$user' id='uidpass'>";
		echo '<hr>';
		echo '<h3>'.($alle===true?$p->t('zeitaufzeichnung/alleEintraege'):$p->t('zeitaufzeichnung/xTageAnsicht', array($angezeigte_tage))).'</h3>';
		if ($alle===true)
			echo '<a href="?normal" style="text-decoration:none"><input type="button" value="'.$p->t('zeitaufzeichnung/xTageAnsicht', array($angezeigte_tage)).'"></a>';
		else
			echo '<a href="?alle" style="text-decoration:none"><input type="button" value="'.$p->t('zeitaufzeichnung/alleAnzeigen').'"></a>';



		$za = new zeitaufzeichnung();
	    if(isset($_GET['filter']))
	    	$za->getListeProjekt($_GET['filter']);
	    else
	    {
	    	if ($alle==true)
	    		$za->getListeUserFull($user, '360');
	    	else
	    		$za->getListeUserFull($user, $angezeigte_tage);
	    }

		$summe=0;
		$dr = new zeitaufzeichnung();
		$dr->getDienstreisenUser($user, 180);
		$dr_arr = $dr->result;

		if(count($za->result)>0)
		{
			//Uebersichtstabelle
			$woche=date('W');
			$colspan=($za_simple)?12:14;
			echo '
			<table id="t1" class="" style="width:100%">

					<tr>
						<th style="background-color: #8DBDD8;" align="center" class="{sorter: false}" colspan="'.$colspan.'">'.$p->t("eventkalender/kw").' '.$woche.'</th>
					</tr>';
					printTableHeadings($fieldheadings, $za_simple);


			$tag=null;
			$woche=date('W');

			$tagessumme='00:00';
			$pausesumme='00:00';
			$wochensumme='00:00';
			$extlehrearr=array();
			$elsumme = '00:00';
			$ersumme = '00:00';
			$ersumme_woche = '00:00';
			$datum_obj = new datum();
			$tagesbeginn = '';
			$tagesende = '';
			$wochensaldo = '00:00';
			$pflichtpause = false;


			foreach($za->result as $row)
			{
				$datumtag = $datum_obj->formatDatum($row->datum, 'Y-m-d');

				// Nach jedem Tag eine Summenzeile einfuegen
				if(is_null($tag))
					$tag = $datumtag;
				if($tag!=$datumtag)
				{
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
					if ($datum->formatDatum($tag, 'Y-m-d') >= '2019-11-06')
					{
						$pausesumme = $pausesumme;
					}
					else if ($tagessaldo > 18000 && $tagessaldo < 19800 && $pflichtpause==false && $elsumme == 0)
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
					// fehlende Pausen berechnen
					$pausefehlt_str = '';

					//Prüfung auf azgrelevant und blockierende Pausenfehler
					$linkExclamation =  APP_ROOT. 'skin/images/exclamation.png';
					$linkInformation =  APP_ROOT. 'skin/images/information.png';

					$za = new zeitaufzeichnung();

					if ($za->checkPausenErrors($user, $tag))
					{
						$vbt = new vertragsbestandteil();
						$isAzgrelevant = $vbt->isAzgRelevant($user, $tag);

						if ($isAzgrelevant)
						{
							$pausefehlt_str = '<span style="color:red; font-weight:bold;"> <img src= '. $linkExclamation. '> -- Pause fehlt oder zu kurz -- </span>';
						}
						else
						{
							$pausefehlt_str = '<span style="color:steelblue; font-weight:bold;"> <img src= '. $linkInformation. '> -- Pause fehlt --</span>';
						}

					}

					$tagessaldo = date('H:i', ($tagessaldo));
					$colspan = ($za_simple)?6:8;
					echo '<tr id="tag_row_'.$datum->formatDatum($tag,'d_m_Y').'"><td '.$style.' colspan="'.$colspan.'">';

					// Zusaetzlicher span fuer Addon Informationen

					$lang = getSprache();
					if ($lang == 'German')
						$langindex = 1;
					else
						$langindex = 2;
					echo '<span style="display: inline-block; width: 130px;"><b>'.$tagbez[$langindex][$datum->formatDatum($tag,'N')].' '.$datum->formatDatum($tag,'d.m.Y').'</b></span><span id="tag_'.$datum->formatDatum($tag,'d_m_Y').'">'.$zeitsperre_text.'</span>'.$pausefehlt_str;
					if ($ersumme != '00:00')
						$erstr = ' (+ '.$ersumme.' ER)';
					else
					{
						$erstr = '';
					}
					echo '</td>
			        <td align="right" colspan="2" '.$style.'>
			        	<b>'.$p->t("zeitaufzeichnung/arbeitszeit").': '.$datum->formatDatum($tagesbeginn, $format='H:i').'-'.$datum->formatDatum($tagesende, $format='H:i').' '.$p->t("eventkalender/uhr").'</b><br>
			        	'.$p->t("zeitaufzeichnung/pause").':
			        </td>
			        <td '.$style.' align="right"><b>'.$tagessaldo.$erstr.'</b><br>'.date('H:i', ($pausesumme-3600)).'</td>
			        <td '.$style.' colspan="3" align="right">';
					if ($tag > $sperrdatum)
					echo '<a href="?von_datum='.$datum->formatDatum($tag,'d.m.Y').'&bis_datum='.$datum->formatDatum($tag,'d.m.Y').'" class="item">&larr;</a>';

					echo '</td></tr>';

					$tag=$datumtag;
					$tagessumme='00:00';
					$pausesumme='00:00';
					$elsumme='00:00';
					$ersumme = '00:00';
					$extlehrearr = array();
					$tagesbeginn = '';
					$tagesende = '';
					$pflichtpause = false;
					$wochensaldo = $datum_obj->sumZeit($wochensaldo,$tagessaldo );

				}
				// Nach jeder Woche eine Summenzeile einfuegen und eine neue Tabelle beginnen
				$datumwoche = $datum_obj->formatDatum($row->datum, 'W');
				if(is_null($woche))
					$woche = $datumwoche;
				if($woche!=$datumwoche)
				{
					if ($ersumme_woche != '00:00')
						$erstr = ' (+ '.$ersumme_woche.')';
					else
					{
						$erstr = '';
					}
					echo '


							<tr>
								<th  colspan="'.$colspan.'" style="background-color: #8DBDD8;"></th>
								<th style="background-color: #8DBDD8; font-weight: normal;" align="right" colspan="2"><b>'.$p->t("zeitaufzeichnung/wochensummeArbeitszeit").':</b></th>
								<th style="background-color: #8DBDD8; font-weight: normal;" align="right"><b>'.$wochensaldo.$erstr.'</b></th>
								<th style="background-color: #8DBDD8;" colspan="3"></th>
							</tr>


					<!--</table>-->';

					$colspan=($za_simple)?12:14;
					echo '
					<!--<table id="t'.$datumwoche.'" class="tablesorter">-->
					<tr><th colspan="'.$colspan.'">&nbsp;</th></tr>

							<tr>
								<th style="background-color: #8DBDD8;" align="center" class="{sorter: false}" colspan="'.$colspan.'">'.$p->t("eventkalender/kw").' '.$datumwoche.'</th>
							</tr>';
					printTableHeadings($fieldheadings, $za_simple);

					$woche=$datumwoche;
					$wochensumme='00:00';
					$tagessumme='00:00';
					$pausesumme='00:00';
					$wochensaldo = '00:00';
					$ersumme = '00:00';
					$ersumme_woche = '00:00';
				}

				// Dienstreisen NEU
				if (array_key_exists($datumtag, $dr_arr))
				{
					$colspan=($za_simple)?6:8;
					echo '<tr style="background-color: #aabb99"><td colspan="'.$colspan.'">'.$p->t('zeitaufzeichnung/dienstreise');
					if (array_key_exists('start', $dr_arr[$datumtag]) && !array_key_exists('ende', $dr_arr[$datumtag]))
						echo ' '.$p->t('global/beginn');
					if (array_key_exists('ende', $dr_arr[$datumtag]) && !array_key_exists('start', $dr_arr[$datumtag]))
						echo ' '.$p->t('global/ende');
					echo '</td>';
					echo '<td>';
					if (array_key_exists('start', $dr_arr[$datumtag]))
						echo $dr_arr[$datumtag]['start'];
					echo '</td><td>';
					if (array_key_exists('ende', $dr_arr[$datumtag]))
						echo $dr_arr[$datumtag]['ende'];
					echo '</td>';
					echo '<td colspan="2"></td>';
					echo '<td>';
					echo "</td>\n";
					echo "<td>";
					if(!isset($_GET['filter']) && ($datumtag > $sperrdatum))
						echo '<a href="'.$_SERVER['PHP_SELF'].'?type=delete&zeitaufzeichnung_id='.$dr_arr[$datumtag]['id'].'" class="Item"  onclick="return confdel()">'.$p->t("global/loeschen").'</a>';
					echo "</td>\n";
					echo '</tr>';
					unset($dr_arr[$datumtag]);
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
				elseif ($row->aktivitaet_kurzbz=='Ersatzruhe')
				{
					$ersumme = $datum_obj->sumZeit($ersumme, $row->diff);
					$ersumme_woche = $datum_obj->sumZeit($ersumme_woche, $row->diff);
				}
				else
					$tagessumme = $datum_obj->sumZeit($tagessumme, $row->diff);
				$style = '';
				if ($row->zeitaufzeichnung_id == $zeitaufzeichnung_id)
					$style = 'style="border-top: 3px solid #8DBDD8; border-bottom: 3px solid #8DBDD8"';
				if ($row->aktivitaet_kurzbz=='Pause' || $row->aktivitaet_kurzbz=='LehreExtern'|| $row->aktivitaet_kurzbz=='Ersatzruhe')
					$style .= ' style="color: grey;"';
				if($db->convert_html_chars($row->homeoffice)=='t')
				{
					$homeoffice = " - Homeoffice";
				}else
					$homeoffice="";
				$summe = $row->summe;
				$service = new service();
				$service->load($row->service_id);
				$projektphase = new projektphase($row->projektphase_id);
				$ap = $projektphase->bezeichnung;
				echo '<tr>
					<td '.$style.'>'.$db->convert_html_chars($row->zeitaufzeichnung_id).'</td>
					<td '.$style.'>'.$db->convert_html_chars($row->uid).'</td>
					<td '.$style.'>'.$db->convert_html_chars($row->projekt_kurzbz).'</td>';
				echo '<td '.$style.' > '.$db->convert_html_chars($ap).'</td>';
				echo '<td '.$style.' > '.$db->convert_html_chars($row->oe_kurzbz_1).'</td>';
				if(!$za_simple)
				{
					echo '<td '.$style.' > '.$db->convert_html_chars($row->oe_kurzbz_2).'</td>';
				}
			    echo '<td '.$style.'>'.$db->convert_html_chars($row->aktivitaet_kurzbz).($homeoffice).'</td>';
				if(!$za_simple)
				{
					echo '<td '.$style.' title = "'.$service->bezeichnung.'" > '.StringCut($db->convert_html_chars($service->bezeichnung),20,null,'...').' </td>';
			    }
					echo '<td '.$style.' nowrap>'.date('H:i', $datum->mktime_fromtimestamp($row->start)).'</td>
			        <td '.$style.' nowrap>'.date('H:i', $datum->mktime_fromtimestamp($row->ende)).'</td>
			        <td '.$style.' align="right">'.$db->convert_html_chars($row->diff).'</td>
			        <td '.$style.' title="'.$db->convert_html_chars(mb_eregi_replace("\r\n",' ',$row->beschreibung)).'">'.StringCut($db->convert_html_chars($row->beschreibung),20,null,'...').'</td>
			        <td '.$style.'>';

					if (!$adminView)
					{
						if(!isset($_GET['filter']) && ($row->uid==$user && $row->datum > $sperrdatum))
				        	echo '<a href="'.$_SERVER['PHP_SELF'].'?type=edit&zeitaufzeichnung_id='.$row->zeitaufzeichnung_id.'" class="Item">'.$p->t("global/bearbeiten").'</a>';
					}
			        echo "</td>\n";
			        echo "       <td ".$style.">";

					if (!$adminView)
					{
						if(!isset($_GET['filter']) && ($row->uid==$user && $row->start > $sperrdatum))
							echo '<a href="'.$_SERVER['PHP_SELF'].'?type=delete&zeitaufzeichnung_id='.$row->zeitaufzeichnung_id.'" class="Item"  onclick="return confdel()">'.$p->t("global/loeschen").'</a>';
					}
			        echo "</td>\n";
			        echo "   </tr>\n";

		        if (($tagesbeginn=='' || $datum->mktime_fromtimestamp($datum->formatDatum($tagesbeginn, $format='Y-m-d H:i:s')) > $datum->mktime_fromtimestamp($datum->formatDatum($row->start, $format='Y-m-d H:i:s'))) && $row->aktivitaet_kurzbz != 'LehreExtern' && $row->aktivitaet_kurzbz != 'Ersatzruhe')
					$tagesbeginn = $row->start;

				if (($tagesende=='' || $datum->mktime_fromtimestamp($datum->formatDatum($tagesende, $format='Y-m-d H:i:s')) < $datum->mktime_fromtimestamp($datum->formatDatum($row->ende, $format='Y-m-d H:i:s'))) && $row->aktivitaet_kurzbz != 'LehreExtern' && $row->aktivitaet_kurzbz != 'Ersatzruhe')
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
									<th align="center" colspan="'.$colspan.'">'.$p->t('zeitaufzeichnung/endeXTageAnsicht', array($angezeigte_tage)).'</th>
								</tr>
						';
			}
		}
		echo '</table>';
}
else
{
	echo $p->t("zeitaufzeichnung/fehlerBeimErmittelnDerProjekte");
}



echo '
<span id="globalmessages"></span>
</body>
</html>';

/**
 * Gibt Tabellenüberschriften für Übersichtstabelle aus
 * @param $fieldheadings Namen der Tabellenüberschriften
 * @param bool $za_simple Zeitaufzeichnung lang (für Infrastrukturmitarbeiter) oder kurz (simple)
 */
function printTableHeadings($fieldheadings, $za_simple = false){
	echo '<tr>
						<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['id'].'</th>
						<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['user'].'</th>
						<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['projekt'].'</th>
						<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['ap'].'</th>
						<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['oe1'].'</th>';
			if (!$za_simple)
			{
				echo '<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['oe2'].'</th>';
			}
			echo '<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['aktivitaet'].'</th>';
			if (!$za_simple)
			{
				echo '
						<th style = "background-color:#DCE4EF" align = "center" > '.$fieldheadings['service'].'</th >';
			}
			echo '<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['start'].'</th>
						<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['ende'].'</th>
						<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['dauer'].'</th>
						<th style="background-color:#DCE4EF" align="center">'.$fieldheadings['beschreibung'].'</th>
						<th style="background-color:#DCE4EF" align="center" colspan="2">'.$fieldheadings['aktion'].'</th>
		    		</tr>';
}

/**
 * Exportiert Zeitaufzeichnungsdaten als CSV
 * @param $data Zeitaufzeichnungsdaten
 * @param string $delimiter CSV-Trennzeichen
 * @param $fieldheadings Namen der Spaltenüberschriften
 * @param bool $za_simple Zeitaufzeichnung lang (für Infrastrukturmitarbeiter) oder kurz (simple)
 * @param $uid Id des Users für CSV-Filenamen "zeitaufzeichnung_uid"
 */
function exportAsCSV($data, $delimiter = ',', $fieldheadings, $za_simple = false, $uid)
{

	$filename = "zeitaufzeichnung_".$uid.".csv";
	header('Content-type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.$filename);

	$file = fopen('php://output', 'w');
	$towrite = getDataForCSV($data, $fieldheadings, $za_simple);
	foreach ($towrite as $row)
	{
		fputcsv($file, $row, $delimiter);
	}
	fclose($file);
	//Abbruch damit HTML markup danach nicht mit exportiert wird
	exit();
}

/**
 * Liefert Daten für CSV-Export basierend auf erhaltenen Zeitaufzeichnungsdaten
 * @param $rawdata zu exportierenden Rohdaten aus der Datenbank
 * @param $fieldheadings Namen der Spaltenüberschriften
 * @param bool $za_simple Zeitaufzeichnung lang (für Infrastrukturmitarbeiter) oder kurz (simple). Wenn true, werden Spalten wie Service, OE ausgelassen
 * @return array Daten wie sie als CSV exportiert werden können
 */
function getDataForCSV($rawdata, $fieldheadings, $za_simple = false)
{
	if(!$za_simple)
		$service = new service();
	$datum = new datum();
	$csvData = array();
	//headers schreiben
	$csvData[] = ($za_simple) ? array($fieldheadings['user'], $fieldheadings['datum'], $fieldheadings['start'], $fieldheadings['ende'], $fieldheadings['projekt'], $fieldheadings['ap'], $fieldheadings['oe1'], $fieldheadings['aktivitaet'], $fieldheadings['beschreibung'], $fieldheadings['homeoffice'])
		: array($fieldheadings['user'], $fieldheadings['datum'], $fieldheadings['start'], $fieldheadings['ende'], $fieldheadings['projekt'], $fieldheadings['ap'], $fieldheadings['oe1'], $fieldheadings['oe2'], $fieldheadings['aktivitaet'], $fieldheadings['service'], $fieldheadings['kunde'], $fieldheadings['beschreibung'], $fieldheadings['homeoffice']);
	foreach ($rawdata as $zeitauf)
	{
		//Newline characters bei Beschreibung ersetzen
		$beschreibung = str_replace(array("\r\n", "\r", "\n"), " | ", $zeitauf->beschreibung);
		$hauptdatum = $datum->formatDatum($zeitauf->datum, "d.m.Y");
		$bisdatum = $datum->formatDatum($zeitauf->ende, "d.m.Y");
		//wenn Zeitspanne länger als ein Tag (kommt selten vor) dann Tag des Bisdatums dazuschreiben
		$bisdatum = ($hauptdatum == $bisdatum)?$datum->formatDatum($zeitauf->ende, 'H:i'):$datum->formatDatum($zeitauf->ende, 'd.m.Y H:i');

		if($za_simple)
		{
			$csvData[] = array($zeitauf->uid, $hauptdatum, $datum->formatDatum($zeitauf->start, 'H:i'),
				$bisdatum, $zeitauf->projekt_kurzbz, $zeitauf->projektphase_id, $zeitauf->oe_kurzbz_1, $zeitauf->aktivitaet_kurzbz, $beschreibung, $zeitauf->homeoffice);
		}
		else
		{
			$servicebez = ($service->load($zeitauf->service_id))?$service->bezeichnung:"";
			$csvData[] = array($zeitauf->uid, $hauptdatum, $datum->formatDatum($zeitauf->start, 'H:i'), $bisdatum,
				$zeitauf->projekt_kurzbz, $zeitauf->projektphase_id, $zeitauf->oe_kurzbz_1, $zeitauf->oe_kurzbz_2, $zeitauf->aktivitaet_kurzbz, $servicebez, $zeitauf->kunde_uid, $beschreibung, $zeitauf->homeoffice);
		}
	}
	return $csvData;
}

/**
 * Liefert Zeitaufzeichnungsdaten zwischen zwei Datumswerten
 * @param $user - user für den Zeitaufzeichnungsdaten geholt werden
 * @param $von - Startdatum
 * @param $bis - Enddatum
 * @return zeitaufzeichnung - die Zeitaufzeichnungsdaten
 */
function getZeitaufzeichnung($user, $von, $bis)
{
	$za = new zeitaufzeichnung();
	$za->getListeUserFromTo($user, $von, $bis);
	return $za;
}

/**
 * Exportiert Zeitaufzeichnungsdaten als CSV
 * @param $data Zeitaufzeichnungsdaten
 * @param string $delimiter CSV-Trennzeichen
 */
function exportProjectOverviewAsCSV($user, $delimiter = ',')
{

	$filename = "projektUebersicht_".$user.".csv";
	header('Content-type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.$filename);

	$file = fopen('php://output', 'w');
	$towrite = getDataForProjectOverviewCSV($user);
	foreach ($towrite as $row)
	{
		fputcsv($file, $row, $delimiter);
	}
	fclose($file);
	//Abbruch damit HTML markup danach nicht mit exportiert wird
	exit();
}

function getDataForProjectOverviewCSV($user)
{
	$db = new basis_db();
	$projects_of_user = new projekt();
	$projects = $projects_of_user->getProjekteListForMitarbeiter($user);

	$projektphase = new projektphase();
	if($projektphase->getProjectphaseForMitarbeiter($user))
		$projektphasen = $projektphase->result;
	else
		$projektphasen = array();

	$csvData = array();

	$exists = @$db->db_query('SELECT 1 FROM sync.tbl_projects_timesheets_project LIMIT 1;');

	foreach ($projects as $project)
	{
		$titel = $project->titel;
		$projekt_kurzbz = $project->projekt_kurzbz;
		$projekt_phase = '';
		$projekt_phase_id = '';
		$beginn = $project->beginn;
		$ende = $project->ende;

		$inhalt = array($titel, $projekt_kurzbz, $projekt_phase, $projekt_phase_id, $beginn, $ende);

		if ($exists)
		{
			$sap_projekt_id = $project->sap_project_id;
			$inhalt[] = $sap_projekt_id;
		}

		$csvData[] = $inhalt;
	}

	foreach ($projektphasen as $prjp)
	{
		if (true)
		{
			$titel = $prjp->projekttitel;
			$projekt_kurzbz = $prjp->projekt_kurzbz;
			$projekt_phase = $prjp->bezeichnung;
			$projekt_phase_id = $prjp->projektphase_id;
			$beginn = $prjp->start;
			$ende = $prjp->ende;
			$inhalt = array($titel, $projekt_kurzbz, $projekt_phase, $projekt_phase_id, $beginn, $ende);

			if ($exists)
			{
				$project_task_id = $prjp->project_task_id;
				$inhalt[] = $project_task_id;
			}

			array_push($csvData, $inhalt);
		}
	}

	sort($csvData);
	//headers schreiben
	$header = array('PROJEKT', 'PROJEKT KURZBEZEICHNUNG', 'PROJEKTPHASE', 'PROJEKTPHASEN ID', 'START', 'PROJEKT ENDE');

	if ($exists)
		$header[] = 'SAP PROJEKTNUMMER';

	array_unshift($csvData, $header);
	return $csvData;
}
?>
