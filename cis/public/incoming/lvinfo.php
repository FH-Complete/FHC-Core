<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Karl Burkhart 		< burkhart@technikum-wien.at >
 */
require_once('../../../config/cis.config.inc.php');
require_once 'auth.php';
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lvinfo.class.php');
require_once('../../../include/studiengang.class.php');

if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>ECTS - European Course Credit Transfer Systems (ECTS)</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" type="text/css" rel="stylesheet" />
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
<div align="center">

<?php
	if(isset($_REQUEST['lv']))
		$lv = $_REQUEST['lv'];
	$language='';
	
	if(isset($_GET['language']))
		$language=$_GET['language'];
		
	if(!is_numeric($lv))
		die('ungueltige ID');
	
	if(isset($_GET['lv'])) //LV Id wird uebergeben (zB bei Ansicht fuer alle von lesson.php)
	{
		$lehrveranstaltung_id=$_GET['lv'];

		if(!is_numeric($lehrveranstaltung_id))
			die('ungueltige ID');
		
		$stsemobj = new studiensemester();
		$stsem = $stsemobj->getaktorNext();

  	  	$lvinfo_obj = new lvinfo();
  	  	if($lvinfo_obj->load($lehrveranstaltung_id, ATTR_SPRACHE_DE))
  	  	{
			// german content variables
			//$titel_de = $lvinfo_obj->titel;
			$methodik_de = $lvinfo_obj->methodik;
			$kurzbeschreibung_de = $lvinfo_obj->kurzbeschreibung;
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
			$lehrziele_en = $lvinfo_obj->lehrziele;
			$lehrinhalte_en = $lvinfo_obj->lehrinhalte;
			$voraussetzungen_en = $lvinfo_obj->voraussetzungen;
			$unterlagen_en = $lvinfo_obj->unterlagen;
			$pruefungsordnung_en = $lvinfo_obj->pruefungsordnung;
			$anmerkungen_en = $lvinfo_obj->anmerkungen;
		}
		else
			die('Es sind keine Informationen zu dieser Lehrveranstaltung vorhanden');
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
				tbl_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($lv, FHC_INTEGER)." AND
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
		       <td class=\"ContentHeader2\" align='center' valign='top'>

		          <div style='font-size: medium; padding-top: 15px; padding-bottom: 15px;'>
		          ".stripslashes($titel_de)."</div>

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
			echo "<tr valign='top'><td>Lehrbeauftragte(r): </td><td>";

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
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Kurzbeschreibung</td></tr>";
			echo "<tr><td><br />".stripslashes($kurzbeschreibung_de)."<br /><br /></td></tr>";
	    }

	    if ($lehrziele_de)
		{
	     	echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Kompetenzerwerb</td></tr>";
	     	echo "<tr><td><br />".stripslashes($lehrziele_de)."<br /><br /></td></tr>";
		}

	    if ($lehrinhalte_de)
		{
	     	echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Lehrinhalte</td></tr>";
	     	echo "<tr><td><br />".stripslashes($lehrinhalte_de)."<br /><br /></td></tr>";
		}

	    if ($voraussetzungen_de)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Vorkenntnisse</td></tr>";
			echo "<tr><td><br />".stripslashes($voraussetzungen_de)."<br><br /></td></tr>";
		}

	    if ($methodik_de)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Methodik / Didaktik</td></tr>";
			echo "<tr><td><br />".stripslashes($methodik_de)."<br><br /></td></tr>";
		}

	    if ($pruefungsordnung_de)
	    {
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Leistungsbeurteilung</td></tr>";
			echo "<tr><td><br />".stripslashes($pruefungsordnung_de)."<br /><br /></td></tr>";
	    }

		if ($unterlagen_de)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Literatur</td></tr>";
			echo "<tr><td><br />".stripslashes($unterlagen_de)."<br /><br /></td></tr>";
		}

		if ($anmerkungen_de)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Anmerkungen</td></tr>";
			echo "<tr><td><br />".stripslashes($anmerkungen_de)."&nbsp;<br /><br /></td></tr>";
		}

		echo "</td></tr></table>";
	}

	//Englische Version
	if(!(isset($language) && $language=='de'))
	{
		echo "<a name=\"en\"></a><br><br>";
		echo "<table class='tabcontent'>
				<tr>
					<td class=\"ContentHeader2\" align='center' valign='top'>
	                	<div style='font-size: medium; padding-top: 15px; padding-bottom: 15px;'>
							".stripslashes($titel_en)."
						</div>
					</td>
				</tr>
				<tr><td><br />";

		echo '<table border="0" cellpadding="0">';
	    echo "<tr><td>degree programme:</td><td>$stg_kurzbz</td></tr>";
	    echo "<tr><td>semester:</td><td>$sem</td></tr>";
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
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Course Description</td></tr>";
			echo "<tr><td><br />".stripslashes($kurzbeschreibung_en)."<br /><br /></td></tr>";
	    }

		if ($lehrziele_en)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Learning outcome</td></tr>";
			echo "<tr><td><br />".stripslashes($lehrziele_en)."<br /><br /></td></tr>";
		}

		if ($lehrinhalte_en)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Course Contents</td></tr>";
			echo "<tr><td><br />".stripslashes($lehrinhalte_en)."<br /><br /></td></tr>";
		}

		if ($voraussetzungen_en)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Prerequisites</td></tr>";
			echo "<tr><td><br />".stripslashes($voraussetzungen_en)."<br /><br /></td></tr>";
		}

	    if ($methodik_en)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Teaching Methods</td></tr>";
			echo "<tr><td><br />".stripslashes($methodik_en)."<br><br /></td></tr>";
		}

		if ($pruefungsordnung_en)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Assessment Methods</td></tr>";
			echo "<tr><td><br />".stripslashes($pruefungsordnung_en)."<br /><br /></td></tr>";
		}

		if ($unterlagen_en)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Recommended Reading and Material</td></tr>";
			echo "<tr><td><br />".stripslashes($unterlagen_en)."<br /><br /></td></tr>";
		}

		if ($anmerkungen_en)
		{
			echo "<tr><td class=\"ContentHeader2\" align='left' valign='top'>Comments</td></tr>";
			echo "<tr><td><br />".stripslashes($anmerkungen_en)."&nbsp;<br /></td></tr>";
		}
	}

    echo "</table>";

    //Ein paar Zeilenumbrueche damit er beim Sprung zum Anker weit genug nach unten springt
    echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";

?>
</body></html>
