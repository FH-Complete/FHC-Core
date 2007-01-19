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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/* @date 27.10.2005
   @brief Zeigt die Daten aus der tbl_lvinfo an
      
   @edit	08-11-2006 Versionierung wurde entfernt. Alle eintraege werden jetzt im WS2007
   					   abgespeichert
   			03-02-2006 Anpassung an die neue Datenbank
*/
	require_once('../../../config.inc.php');
	require_once('../../../../include/studiensemester.class.php');
	require_once('../../../../include/lehrveranstaltung.class.php');
	require_once('../../../../include/lvinfo.class.php');
	require_once('../../../../include/studiengang.class.php');

	if(!$conn=pg_pconnect(CONN_STRING))
		die('Fehler beim Connecten zur Datenbank');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>ECTS - European Course Credit Transfer Systems (ECTS)</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../../skin/cis.css" type="text/css" rel="stylesheet" />
<style type="text/css">
<!--
td {
font-family:verdana,arial,helvetica;
font-size:10pt;
}
//-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000">
<div style="text-align: right;"><td><img src='../../../../skin/images/TWLogo_klein.jpg'></div>
<table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td valign="top" width="3%">&nbsp;</td>
<td valign="top" width="94%"><div align="center">

<?php
	$language=''; 
	
	if(isset($_GET['language']))
		$language=$_GET['language'];
	
	if(isset($_POST['language']))
		$language=$_POST['language'];

	if(!isset($language) || ($language!='de' && $language!='en'))
	{
		echo "<li><a href=\"#de\">Deutsche Version</a></li>";
		echo "<li><a href=\"#en\">Englische Version</a></li>";
	}

	if(isset($_POST['titel_de'])) //Alle Variablen werden per POST Methode uebergeben (zB bei Voransicht)
	{
		//$sprache = stripslashes($_POST['sprache']);
		//$semstunden = stripslashes($_POST["semstunden"]);
		$lehrveranstaltung_id = $_POST['lv'];
		
		// german content variables
		$titel_de = str_replace("\r\n","<br>",stripslashes($_POST['titel_de']));
		$methodik_de = str_replace("\r\n","<br>",stripslashes($_POST['methodik_de']));
		$kurzbeschreibung_de = str_replace("\r\n","<br>",stripslashes($_POST['kurzbeschreibung_de']));
		$lehrziele_de = str_replace("\r\n","<br>",stripslashes($_POST['lehrziele_de']));
		$lehrinhalte_de = str_replace("\r\n","<br>",stripslashes($_POST['lehrinhalte_de']));
		$voraussetzungen_de = str_replace("\r\n","<br>",stripslashes($_POST['voraussetzungen_de']));
		$unterlagen_de = str_replace("\r\n","<br>",stripslashes($_POST['unterlagen_de']));
		$pruefungsordnung_de = str_replace("\r\n","<br>",stripslashes($_POST['pruefungsordnung_de']));
		$anmerkungen_de = str_replace("\r\n","<br>",stripslashes($_POST['anmerkungen_de']));
		
		// Englisch content variables
		$titel_en = str_replace("\r\n","<br>",stripslashes($_POST['titel_en']));
		$methodik_en = str_replace("\r\n","<br>",stripslashes($_POST['methodik_en']));
		$kurzbeschreibung_en = str_replace("\r\n","<br>",stripslashes($_POST['kurzbeschreibung_en']));
		$lehrziele_en = str_replace("\r\n","<br>",stripslashes($_POST['lehrziele_en']));
		$lehrinhalte_en = str_replace("\r\n","<br>",stripslashes($_POST['lehrinhalte_en']));
		$voraussetzungen_en = str_replace("\r\n","<br>",stripslashes($_POST['voraussetzungen_en']));
		$unterlagen_en = str_replace("\r\n","<br>",stripslashes($_POST['unterlagen_en']));
		$pruefungsordnung_en = str_replace("\r\n","<br>",stripslashes($_POST['pruefungsordnung_en']));
		$anmerkungen_en = str_replace("\r\n","<br>",stripslashes($_POST['anmerkungen_en']));
	}
	elseif(isset($_GET['lv'])) //LV Id wird uebergeben (zB bei Ansicht fuer alle von lesson.php)
	{
		$lehrveranstaltung_id=$_GET['lv'];
  	  
		$stsemobj = new studiensemester($conn);
		$stsem = $stsemobj->getaktorNext();

  	  	$lvinfo_obj = new lvinfo($conn);
  	  	if($lvinfo_obj->load($lehrveranstaltung_id, ATTR_SPRACHE_DE))
  	  	{
			// german content variables
			$titel_de = $lvinfo_obj->titel;
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
			$titel_en = $lvinfo_obj->titel;
			$methodik_en = $lvinfo_obj->methodik;
			$kurzbeschreibung_en = $lvinfo_obj->kurzbeschreibung;
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
  	
	$stsemobj = new studiensemester($conn);
	$stsem = $stsemobj->getaktorNext();
      
	$lv_obj = new lehrveranstaltung($conn);
	if(!$lv_obj->load($lehrveranstaltung_id))
		die($lv_obj->errormsg);

	$ects_points = $lv_obj->ects;
	$stg = $lv_obj->studiengang_kz;
	$sem = $lv_obj->semester;
	$lang = $lv_obj->sprache;

	//Zugeteilte Fachbereiche auslesen
	$qry = "SELECT distinct tbl_fachbereich.bezeichnung as bezeichnung, tbl_fachbereich.fachbereich_kurzbz as fachbereich_kurzbz 
			FROM public.tbl_fachbereich, lehre.tbl_lehreinheit, lehre.tbl_lehrfach 
	      	WHERE tbl_lehreinheit.studiensemester_kurzbz=(
	      		SELECT studiensemester_kurzbz FROM lehre.tbl_lehreinheit JOIN public.tbl_studiensemester USING(studiensemester_kurzbz) 
	      		WHERE tbl_lehreinheit.lehrveranstaltung_id='$lv' ORDER BY ende DESC LIMIT 1)
	      	AND tbl_lehreinheit.lehrveranstaltung_id='$lv' AND
	      	tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND
	      	tbl_fachbereich.fachbereich_kurzbz=tbl_lehrfach.fachbereich_kurzbz";
	
	if(!$result=pg_query($conn, $qry))
		die('Fehler beim Lesen aus der Datenbank');
	
	$fachbereiche='1';
	$fachbereich['kurzbz']=array();
	$fachbereich['bezeichnung']=array();
	
	while($row=pg_fetch_object($result))
	{
		$fachbereiche .= ", '$row->fachbereich_kurzbz'";
		$fachbereich['kurzbz'][]=$row->fachbereich_kurzbz;
		$fachbereich['bezeichnung'][]=$row->bezeichnung;
	}	  
	  
	//Studiengangsbezeichnung auslesen
	$stg_hlp_obj = new studiengang($conn);
	$stg_hlp_obj->load($stg);

	$stg_kurzbz = $stg_hlp_obj->kuerzel;
	$stg_kurzbzlang = $stg_hlp_obj->kurzbzlang;
	
	//Lehrform auslesen        
	$qry = "Select distinct lehrform_kurzbz FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id='$lv' AND studiensemester_kurzbz='$stsem'";
	if(!$res = pg_query($conn,$qry))
		die('Fehler beim Lesen aus der Datenbank');

	while($row = pg_fetch_object($res))
		$lehrform_kurzbz[] = $row->lehrform_kurzbz;
	//Fachbereichsleiter fuer alle FB ermitteln
	$qry="SELECT * FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE funktion_kurzbz='fbl' AND fachbereich_kurzbz in($fachbereiche)";
	if(!$res=pg_query($conn,$qry))
		die('Fehler beim herstellen der DB Connection');
	  
	$fachbereichsleiter=array();
	while($row=pg_fetch_object($res))
		$fachbereichsleiter[$row->fachbereich_kurzbz] = $row->vorname."&nbsp;".$row->nachname;

	//Fachbereichskoordinatoren fuer alle FB ermitteln
	$qry="SELECT * FROM public.tbl_benutzerfunktion JOIN campus.vw_mitarbeiter USING(uid) WHERE funktion_kurzbz='fbk' AND studiengang_kz='$stg' AND fachbereich_kurzbz in($fachbereiche)";

	if(!$res=pg_exec($conn,$qry))
		die('Fehler beim herstellen der DB Connection');
	  
	$fachbereichskoordinator=array();
	while($row=pg_fetch_object($res))
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
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lehrveranstaltung_id' 
			AND studiensemester_kurzbz='$stsem'
			AND tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id
			AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid=uid";
	   
	$lehrendearray = array();
	if($result=pg_query($conn,$qry))
	{
		while($row=pg_fetch_object($result))
			$lehrendearray[] = "$row->vorname $row->nachname";
	}
	   
	//Ausgabe der LV-Information
	  
	//Deutsch Version
	if(!(isset($language) && $language=='en'))
	{
		echo "<a name=\"de\"></a><br><br>
		    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style='margin:0px;' width='100%'>
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
			echo '<tr><td>Fachbereich:&nbsp;</td><td>';
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
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style='margin:0px;' width='100%'>
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
</td>
<td width="3%">&nbsp;</td>
</tr>
</table>
</body></html>