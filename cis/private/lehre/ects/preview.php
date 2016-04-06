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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/* @date 27.10.2005
   @brief Zeigt die Daten aus der tbl_lvinfo an

   @edit	08-11-2006 Versionierung wurde entfernt. Alle eintraege werden jetzt im WS2007
   					   abgespeichert
   			03-02-2006 Anpassung an die neue Datenbank
*/

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../config/global.config.inc.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/lvinfo.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/safehtml/safehtml.class.php');
require_once '../../../../include/phrasen.class.php';
require_once '../../../../include/lehreinheit.class.php';
require_once '../../../../include/lehrstunde.class.php';
require_once '../../../../include/datum.class.php';
require_once '../../../../include/stunde.class.php';

if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');

$phrasen = new phrasen();

function cmp($a, $b)
{
    if($a->datum == $b->datum && $a->stunde == $b->stunde)
    {
	return 0;
    }
    if($a->datum == $b->datum && $a->stunde < $b->stunde)
    {
	return -1;
    }
    else if($a->datum == $b->datum && $a->stunde >= $b->stunde)
    {
	return 1;
    }
    return ($a->datum < $b->datum) ? -1 : 1;

}

function getLastStundeByDatum(Array $array, $filterDatum)
{
    $callback = function($item) use ($filterDatum)
		{
		    return ($filterDatum == $item->datum);
		};
    return array_filter($array,$callback);
}

$titel_de = '';
$methodik_de = '';
$kurzbeschreibung_de = '';
$anwesenheit_de = '';
$lehrziele_de = '';
$lehrinhalte_de = '';
$voraussetzungen_de = '';
$unterlagen_de = '';
$pruefungsordnung_de = '';
$anmerkungen_de = '';

$titel_en = '';
$methodik_en = '';
$kurzbeschreibung_en = '';
$anwesenheit_en = '';
$lehrziele_en = '';
$lehrinhalte_en = '';
$voraussetzungen_en = '';
$unterlagen_en = '';
$pruefungsordnung_en = '';
$anmerkungen_en = '';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>ECTS - European Course Credit Transfer Systems (ECTS)</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../../skin/style.css.php" type="text/css" rel="stylesheet" />
</head>
<body>
<table align="right">
	<tr>
		<td>
			<div class="home_logo"></div>
		</td>
	</tr>
</table>
<br><br><br><br><br><br>
<table class="tabcontent" id="inhalt">

<tr>
<td><div align="center">

<?php
	if(isset($_REQUEST['lv']))
		$lv = $_REQUEST['lv'];
	$language='';

	if(isset($_GET['language']))
		$language=$_GET['language'];

	if(isset($_POST['language']))
		$language=$_POST['language'];

	if(!isset($language) || ($language!='de' && $language!='en'))
	{
		echo "<li><a class='Item' href=\"#de\">Deutsche Version</a></li><br>";
		echo "<li><a class='Item' href=\"#en\">Englische Version</a></li><br>";
	}

	if(isset($_POST['methodik_de'])) //Alle Variablen werden per POST Methode uebergeben (zB bei Voransicht)
	{
		//$sprache = stripslashes($_POST['sprache']);
		//$semstunden = stripslashes($_POST["semstunden"]);
		$lehrveranstaltung_id = $_POST['lv'];

		// german content variables
		//$titel_de = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['titel_de']));
		$methodik_de = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['methodik_de']));
		$kurzbeschreibung_de = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['kurzbeschreibung_de']));
		$anwesenheit_de = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['anwesenheit_de']));
		$lehrziele_de = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['lehrziele_de']));
		$lehrinhalte_de = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['lehrinhalte_de']));
		$voraussetzungen_de = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['voraussetzungen_de']));
		$unterlagen_de = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['unterlagen_de']));
		$pruefungsordnung_de = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['pruefungsordnung_de']));
		$anmerkungen_de = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['anmerkungen_de']));

		$parser = new SafeHTML();
	 	$lehrziele_de = $parser->parse($lehrziele_de);
	 	$parser = new SafeHTML();
	 	$lehrinhalte_de = $parser->parse($lehrinhalte_de);
	 	$parser = new SafeHTML();
		$voraussetzungen_de = $parser->parse($voraussetzungen_de);
		$parser = new SafeHTML();
		$unterlagen_de = $parser->parse($unterlagen_de);
		$parser = new SafeHTML();
		$pruefungsordnung_de = $parser->parse($pruefungsordnung_de);
		$parser = new SafeHTML();
		$anmerkungen_de = $parser->parse($anmerkungen_de);
		$parser = new SafeHTML();
		$kurzbeschreibung_de = $parser->parse($kurzbeschreibung_de);
		$parser = new SafeHTML();
		$anwesenheit_de = $parser->parse($anwesenheit_de);
		$parser = new SafeHTML();
		$methodik_de = $parser->parse($methodik_de);

		// Englisch content variables
		//$titel_en = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['titel_en']));
		$methodik_en = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['methodik_en']));
		$kurzbeschreibung_en = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['kurzbeschreibung_en']));
		$anwesenheit_en = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['anwesenheit_en']));
		$lehrziele_en = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['lehrziele_en']));
		$lehrinhalte_en = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['lehrinhalte_en']));
		$voraussetzungen_en = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['voraussetzungen_en']));
		$unterlagen_en = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['unterlagen_en']));
		$pruefungsordnung_en = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['pruefungsordnung_en']));
		$anmerkungen_en = mb_eregi_replace("\r\n","<br>",stripslashes($_POST['anmerkungen_en']));

		$parser = new SafeHTML();
		$lehrziele_en = $parser->parse($lehrziele_en);
		$parser = new SafeHTML();
	 	$lehrinhalte_en = $parser->parse($lehrinhalte_en);
	 	$parser = new SafeHTML();
		$voraussetzungen_en = $parser->parse($voraussetzungen_en);
		$parser = new SafeHTML();
		$unterlagen_en = $parser->parse($unterlagen_en);
		$parser = new SafeHTML();
		$pruefungsordnung_en = $parser->parse($pruefungsordnung_en);
		$parser = new SafeHTML();
		$anmerkungen_en = $parser->parse($anmerkungen_en);
		$parser = new SafeHTML();
		$kurzbeschreibung_en = $parser->parse($kurzbeschreibung_en);
		$parser = new SafeHTML();
		$anwesenheit_en = $parser->parse($anwesenheit_en);
		$parser = new SafeHTML();
		$methodik_en = $parser->parse($methodik_en);
	}
	elseif(isset($_GET['lv'])) //LV Id wird uebergeben (zB bei Ansicht fuer alle von lesson.php)
	{
		$lehrveranstaltung_id=$_GET['lv'];

		$stsemobj = new studiensemester();
		$stsem = $stsemobj->getaktorNext();

  	  	$lvinfo_obj = new lvinfo();
  	  	if($lvinfo_obj->load($lehrveranstaltung_id, ATTR_SPRACHE_DE))
  	  	{
			// german content variables
			//$titel_de = $lvinfo_obj->titel;
			$methodik_de = $lvinfo_obj->methodik;
			$kurzbeschreibung_de = $lvinfo_obj->kurzbeschreibung;
			$anwesenheit_de = $lvinfo_obj->anwesenheit;
			$lehrziele_de = $lvinfo_obj->lehrziele;
			$lehrinhalte_de = $lvinfo_obj->lehrinhalte;
			$voraussetzungen_de = $lvinfo_obj->voraussetzungen;
			$unterlagen_de = $lvinfo_obj->unterlagen;
			$pruefungsordnung_de = $lvinfo_obj->pruefungsordnung;
			$anmerkungen_de = $lvinfo_obj->anmerkungen;
  	  	}

		if($lvinfo_obj->load($lehrveranstaltung_id, ATTR_SPRACHE_EN))
		{
			// Englisch content variables
			//$titel_en = $lvinfo_obj->titel;
			$methodik_en = $lvinfo_obj->methodik;
			$kurzbeschreibung_en = $lvinfo_obj->kurzbeschreibung;
			$anwesenheit_en = $lvinfo_obj->anwesenheit;
			$lehrziele_en = $lvinfo_obj->lehrziele;
			$lehrinhalte_en = $lvinfo_obj->lehrinhalte;
			$voraussetzungen_en = $lvinfo_obj->voraussetzungen;
			$unterlagen_en = $lvinfo_obj->unterlagen;
			$pruefungsordnung_en = $lvinfo_obj->pruefungsordnung;
			$anmerkungen_en = $lvinfo_obj->anmerkungen;
		}
	}
	else
		die('Fehler bei der Parameteruebergabe');

	$stsemobj = new studiensemester();
	$stsem = $stsemobj->getaktorNext();

	$lv_obj = new lehrveranstaltung();
	if(!$lv_obj->load($lehrveranstaltung_id))
		die($lv_obj->errormsg);

	$ects_points = $lv_obj->ects;
	$stg = $lv_obj->studiengang_kz;
	$sem = $lv_obj->semester;
	$lang = $lv_obj->sprache;
	$titel_de = $lv_obj->bezeichnung;
	$titel_en = $lv_obj->bezeichnung_english;
	$anz_incoming = $lv_obj->incoming;

	if (!isset($lv))
		$lv=0;


	//Zugeteilte Fachbereiche auslesen
	$qry = "SELECT distinct tbl_fachbereich.bezeichnung as bezeichnung, tbl_fachbereich.fachbereich_kurzbz as fachbereich_kurzbz
			FROM public.tbl_fachbereich, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach
	      	WHERE tbl_lehreinheit.studiensemester_kurzbz=(
	      		SELECT studiensemester_kurzbz FROM lehre.tbl_lehreinheit JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
	      		WHERE tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lv, FHC_INTEGER)." ORDER BY ende DESC LIMIT 1)
	      	AND tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lv, FHC_INTEGER)." AND
	      	tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND
	      	tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz";

	if(!$result=$db->db_query($qry))
		die('Fehler beim Lesen aus der Datenbank');

	$fachbereiche="'1'";
	$fachbereich['kurzbz']=array();
	$fachbereich['bezeichnung']=array();

	while($row=$db->db_fetch_object($result))
	{
		$fachbereiche .= ", ".$db->db_add_param($row->fachbereich_kurzbz);
		$fachbereich['kurzbz'][]=$row->fachbereich_kurzbz;
		$fachbereich['bezeichnung'][]=$row->bezeichnung;
	}

	//Studiengangsbezeichnung auslesen
	$stg_hlp_obj = new studiengang();
	$stg_hlp_obj->load($stg);

	$stg_kurzbz = $stg_hlp_obj->kuerzel;
	$stg_kurzbzlang = $stg_hlp_obj->kurzbzlang;

	//Lehrform auslesen
	$qry = "Select distinct lehrform_kurzbz FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id=".$db->db_add_param($lv, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($stsem);
	if(!$res = $db->db_query($qry))
		die('Fehler beim Lesen aus der Datenbank');
	//echo $fachbereiche;
	while($row = $db->db_fetch_object($res))
		$lehrform_kurzbz[] = $row->lehrform_kurzbz;
	//Fachbereichsleiter fuer alle FB ermitteln
	$qry="
		SELECT
			vorname, nachname, tbl_fachbereich.fachbereich_kurzbz
		FROM
			public.tbl_benutzerfunktion
			JOIN public.tbl_fachbereich USING(oe_kurzbz)
			JOIN campus.vw_mitarbeiter USING(uid)
		WHERE
			vw_mitarbeiter.aktiv AND
			funktion_kurzbz='Leitung' AND tbl_fachbereich.fachbereich_kurzbz in($fachbereiche) AND
			(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
			(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now())";

	if(!$res=$db->db_query($qry))
		die('Fehler '.$db->errormsg);

	$fachbereichsleiter=array();
	while($row=$db->db_fetch_object($res))
		$fachbereichsleiter[$row->fachbereich_kurzbz] = $row->vorname."&nbsp;".$row->nachname;

	//Fachbereichskoordinatoren fuer alle FB ermitteln
	//$qry="SELECT * FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE funktion_kurzbz='fbk' AND studiengang_kz='$stg' AND fachbereich_kurzbz in($fachbereiche)";
	$qry = "SELECT
				distinct vorname, nachname, tbl_fachbereich.fachbereich_kurzbz
			FROM
				lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach, public.tbl_benutzerfunktion, campus.vw_mitarbeiter, public.tbl_fachbereich
			WHERE
				vw_mitarbeiter.aktiv AND
				tbl_lehrveranstaltung.lehrveranstaltung_id='$lv' AND
				tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND
				lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz AND
				tbl_fachbereich.fachbereich_kurzbz=tbl_benutzerfunktion.fachbereich_kurzbz AND
				tbl_benutzerfunktion.funktion_kurzbz='fbk' AND
				vw_mitarbeiter.uid=COALESCE(tbl_lehrveranstaltung.koordinator, tbl_benutzerfunktion.uid) AND
				(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
				(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()) AND
				tbl_lehrveranstaltung.studiengang_kz=(SELECT studiengang_kz FROM public.tbl_studiengang WHERE oe_kurzbz=tbl_benutzerfunktion.oe_kurzbz LIMIT 1) ";

	if(!$res=$db->db_query($qry))
		die('Fehler ! '.$db->errormsg);

	$fachbereichskoordinator=array();
	while($row=$db->db_fetch_object($res))
	{
		$name = $row->vorname."&nbsp;".$row->nachname;

		if(!isset($fachbereichskoordinator[$row->fachbereich_kurzbz]) ||
		   !in_array($name, $fachbereichskoordinator[$row->fachbereich_kurzbz]))
		{
			$fachbereichskoordinator[$row->fachbereich_kurzbz][] = $name;
		}
	}

	//Namen der Lehrenden Auslesen
	$qry = "SELECT distinct vorname, nachname FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, campus.vw_mitarbeiter
			WHERE tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
			AND studiensemester_kurzbz=(SELECT studiensemester_kurzbz FROM public.tbl_studiensemester JOIN lehre.tbl_lehreinheit USING(studiensemester_kurzbz) WHERE tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER)." ORDER BY ende DESC LIMIT 1)
			AND tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id
			AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid=uid";

	$lehrendearray = array();
	if($result=$db->db_query($qry))
	{
		while($row=$db->db_fetch_object($result))
			$lehrendearray[] = "$row->vorname $row->nachname";
	}

	//Ausgabe der LV-Information

	//Deutsch Version
	if(!(isset($language) && $language=='en'))
	{
		echo "<a name=\"de\"></a><br><br>
		    <table class='tabcontent'>
		    <tr>
		       <td align='center' valign='top'>

		          <h1>
		          ".stripslashes($titel_de)."</h1>

		       </td>
		    </tr>
		    <tr>
		    <td><br>";
		echo '<table border="0" cellpadding="0">';
		echo "<tr><td>Studiengang:</td><td>$stg_kurzbz</td></tr>";
		echo "<tr><td>Semester:</td><td>$sem</td></tr>";
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
		if(($anz=count($lehrendearray))>0)
		{
			echo "<tr valign='top'><td><nobr>".$phrasen->t('lehre/lehrbeauftragter').": </nobr></td><td>";

			foreach($lehrendearray as $elem)
			{
				$anz--;
				echo " $elem";
				if($anz!=0)
					echo ',';
			}
			echo '</td></tr>';
		}

		if(isset($lehrform_kurzbz) && count($lehrform_kurzbz)>0)
		{
			echo "<tr valign='top'><td>Lehrform:&nbsp;</td><td>";
			foreach ($lehrform_kurzbz as $lehrform_kurz)
				echo "$lehrform_kurz<br />";
			echo '</td></tr>';
		}

		if ($lang > -1)
			echo '<tr><td>Sprache:&nbsp;</td><td>'.stripslashes($lang).'</td></tr>';

		if ($ects_points)
			echo '<tr><td>ECTS:&nbsp;</td><td>'.number_format(stripslashes($ects_points),1,'.','').'</td></tr>';

		if ($anz_incoming > -1)
		{
			echo '<tr><td>Incomingpl&auml;tze:&nbsp;</td><td>'.stripslashes($anz_incoming).'</td></tr>';
		}
		else echo '<tr><td>Incomingpl&auml;tze:&nbsp;</td><td>0</td></tr>';

		echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
		//Fachbereiche und Leiter/Koordinatoren anzeigen
		if (count($fachbereich['bezeichnung'])>0)
		{
			echo '<tr><td>Institut:&nbsp;</td><td>';
			//Fachbereiche durchlaufen
			for($i=0;$i<count($fachbereich['kurzbz']);$i++)
			{
				$help='';
				echo stripslashes($fachbereich['bezeichnung'][$i]);
				//zugehoerigen Leiter ausgeben
				if(isset($fachbereichsleiter[$fachbereich['kurzbz'][$i]]))
					$help.='Leitung: '.$fachbereichsleiter[$fachbereich['kurzbz'][$i]];
				if(isset($fachbereichskoordinator[$fachbereich['kurzbz'][$i]]))
				{
					$first=true;
					//zugehoerige Koordinatoren ausgeben
					foreach($fachbereichskoordinator[$fachbereich['kurzbz'][$i]] as $fbk)
					{
						if($help!='')
						{
							if($first)
							{
								$help.=' Koordination:';
								$first=false;
							}
							else
								$help.=',';
						}
						$help.=" $fbk";
					}
				}
				if($help!='')
					echo " ($help)";
			}

			echo '</td></tr>';
		}
	    echo "</table>";
		echo "<br /><br /></td></tr>";

	    if ($kurzbeschreibung_de)
	    {
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/kurzbeschreibung')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($kurzbeschreibung_de)."<br /><br /></td></tr>";
	    }

	    if ($lehrziele_de)
		{
	     	echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/lernergebnisse')."</h2></td></tr>";
	     	echo "<tr><td>".stripslashes($lehrziele_de)."<br /><br /></td></tr>";
		}

	    if ($lehrinhalte_de)
		{
	     	echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/lehrinhalte')."</h2></td></tr>";
	     	echo "<tr><td>".stripslashes($lehrinhalte_de)."<br /><br /></td></tr>";
		}

	    if ($voraussetzungen_de)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/vorkenntnisse')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($voraussetzungen_de)."<br><br /></td></tr>";
		}

	    if ($methodik_de)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/methodik')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($methodik_de)."<br><br /></td></tr>";
		}

	    if ($pruefungsordnung_de)
	    {
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/leistungsbeurteilung')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($pruefungsordnung_de)."<br /><br /></td></tr>";
	    }

		if ($unterlagen_de)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/literatur')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($unterlagen_de)."<br /><br /></td></tr>";
		}

		if ($anwesenheit_de)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/anwesenheit')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($anwesenheit_de)."<br /><br /></td></tr>";
		}

		if ($anmerkungen_de)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/anmerkungen')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($anmerkungen_de)."&nbsp;<br /><br /></td></tr>";
		}

		echo "</td></tr></table>";
	}

	//Englische Version
	if(!(isset($language) && $language=='de'))
	{
		echo "<a name=\"en\"></a><br><br>";
		echo "<table class='tabcontent'>
				<tr>
					<td align='center' valign='top'>
	                	<h1>
							".stripslashes($titel_en)."
						</h1>
					</td>
				</tr>
				<tr><td><br />";

		echo '<table border="0" cellpadding="0">';
	    echo "<tr><td>Degree programme:</td><td>$stg_kurzbz</td></tr>";
	    echo "<tr><td>Semester:</td><td>$sem</td></tr>";
	    echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";

		if(($anz=count($lehrendearray))>0)
	     {
	     	echo "<tr><td>Lecturer:</td><td>";

	     	foreach($lehrendearray as $elem)
	     	{
	     		$anz--;
	     		echo " $elem";
	     		if($anz!=0)
	     			echo ",";
	     	}
	     	echo "</td></tr>";
	     }

		if(isset($lehrform_kurzbz) && count($lehrform_kurzbz)>0)
		{
			echo "<tr valign='top'><td>Course methods:&nbsp;</td><td>";
			foreach ($lehrform_kurzbz as $lehrform_kurz)
				echo "$lehrform_kurz<br />";
			echo "</td></tr>";
		 }

		if ($lang > -1)
			echo "<tr><td>Language:&nbsp;</td><td>".stripslashes($lang)."</td></tr>";

	    if ($ects_points)
			echo "<tr><td>ECTS Credits:&nbsp;</td><td>".number_format(stripslashes($ects_points),1,'.','')."</td></tr>";

		if ($anz_incoming > -1)
		{
			echo '<tr><td>Places Available for Incoming Students:&nbsp;</td><td>'.stripslashes($anz_incoming).'</td></tr>';
		}
		else echo '<tr><td>Places Available for Incoming Students:&nbsp;</td><td>0</td></tr>';

		echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";

		//Fachbereiche und Leiter/Koordinatoren anzeigen
		if (count($fachbereich['bezeichnung'])>0)
		{
			echo '<tr><td>Department:&nbsp;</td><td>';
			//Fachbereiche durchlaufen
			for($i=0;$i<count($fachbereich['kurzbz']);$i++)
			{
				$help='';
				echo stripslashes($fachbereich['bezeichnung'][$i]);
				//zugehoerigen Leiter ausgeben
				if(isset($fachbereichsleiter[$fachbereich['kurzbz'][$i]]))
					$help.='Head: '.$fachbereichsleiter[$fachbereich['kurzbz'][$i]];
				if(isset($fachbereichskoordinator[$fachbereich['kurzbz'][$i]]))
				{
					$first=true;
					//zugehoerige koordinatoren ausgeben
					foreach($fachbereichskoordinator[$fachbereich['kurzbz'][$i]] as $fbk)
					{
						if($help!='')
						{
							if($first)
							{
								$help.=' Coordination:';
								$first=false;
							}
							else
								$help.=',';
						}
						$help.=" $fbk";
					}
				}
				if($help!='')
					echo " ($help)";
			}

			echo '</td></tr>';
		}

		echo '</table>';
		echo '<br /><br /></td></tr>';

	    if ($kurzbeschreibung_en)
	    {
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/kurzbeschreibungEN')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($kurzbeschreibung_en)."<br /><br /></td></tr>";
	    }

		if ($lehrziele_en)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/lernergebnisseEN')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($lehrziele_en)."<br /><br /></td></tr>";
		}

		if ($lehrinhalte_en)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/lehrinhalteEN')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($lehrinhalte_en)."<br /><br /></td></tr>";
		}

		if ($voraussetzungen_en)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/vorkenntnisseEN')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($voraussetzungen_en)."<br /><br /></td></tr>";
		}

	    if ($methodik_en)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/methodikEN')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($methodik_en)."<br><br /></td></tr>";
		}

		if ($pruefungsordnung_en)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/leistungsbeurteilungEN')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($pruefungsordnung_en)."<br /><br /></td></tr>";
		}

		if ($unterlagen_en)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/literaturEN')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($unterlagen_en)."<br /><br /></td></tr>";
		}

		if ($anwesenheit_en)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/anwesenheitEN')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($anwesenheit_en)."<br /><br /></td></tr>";
		}

		if ($anmerkungen_en)
		{
			echo "<tr><td align='left' valign='top'><h2>".$phrasen->t('lvinfo/anmerkungenEN')."</h2></td></tr>";
			echo "<tr><td>".stripslashes($anmerkungen_en)."&nbsp;<br /></td></tr>";
		}
	}

    echo "</table>";

    $lehreinheit = new lehreinheit();
    $studiensemester = new studiensemester();
    $lehreinheit->load_lehreinheiten($lv, $studiensemester->getaktorNext());

    if (CIS_LVINFO_TERMINE_ANZEIGEN == true)
    {
	    if(!empty($lehreinheit->lehreinheiten))
	    {
		echo "<h2>Termine</h2><table>";
			foreach($lehreinheit->lehreinheiten as $lehreinheit_temp)
			{
			    $lehrstunde = new lehrstunde();
			    $lehrstunde->load_lehrstunden_le($lehreinheit_temp->lehreinheit_id);
			    $i = 1;
			    echo "<tr><td><ul>";

			    $result = $lehrstunde->lehrstunden;
			    $last = "";
			    $bis = "";
			    usort($result, "cmp");
			    $datum = new datum();
			    $std_von = new stunde();
			    $std_bis = new stunde();
			    foreach($result as $key=>$stunde)
			    {
				if($last !== $stunde->datum)
				{
				    $temp = array_values(getLastStundeByDatum($result, $stunde->datum));
				    $size = count($temp);
				    if($size != 0)
				    {
					$std_von->load($temp[0]->stunde);
					$std_bis->load($temp[$size-1]->stunde);
					echo "<li>".$datum->formatDatum($temp[0]->datum,"d.m.Y")." von ".mb_substr($std_von->beginn,0,5)." bis ".mb_substr($std_bis->ende,0,5)."</li>";
				    }
				    $i++;
				}
				elseif($last == "")
				{
				    $temp = getLastStundeByDatum($result, $stunde->datum);
				    var_dump($temp);
				}
				else
				{
				    $bis = $stunde->stunde;
				}

				if($i % 5 === 0)
				{
		    //		echo "</ul></td><td><ul>";
		    //		$i++;
				}
				$last = $stunde->datum;
			    }
			    echo "</ul></td></tr>";
			}
			echo "</table>";
	    }
    }

    //Ein paar Zeilenumbrueche damit er beim Sprung zum Anker weit genug nach unten springt
    echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";

?>
</td>
<td width="3%">&nbsp;</td>
</tr>
</table>
</body></html>
