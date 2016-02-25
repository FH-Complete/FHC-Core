<?php
/* Copyright (C) 2008 Technikum-Wien
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
/*
 * Erstellt eine Liste mit den Noten des eingeloggten Studenten
 * das betreffende Studiensemester kann ausgewaehlt werden
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/note.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/pruefung.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../../../include/js/jquery.js"></script>
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<title>'.$p->t('tools/leistungsbeurteilung').'</title>

	<script language="JavaScript" type="text/javascript">
	function MM_jumpMenu(targ, selObj, restore)
	{
	  eval(targ + ".location=\'" + selObj.options[selObj.selectedIndex].value + "\'");

	  if(restore)
	  {
	  	selObj.selectedIndex = 0;
	  }
	}
    
    $(document).ready(function() 
    { 
        $("#notenliste").tablesorter(
        {
            sortList: [[3,1]],
            widgets: ["zebra"]
        }); 
    });
	</script>
</head>

<body>
	<h1>'.$p->t('tools/leistungsbeurteilung').'</h1>';


if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';
	
$user = get_uid();

if(isset($_GET['uid']))
{
	// Administratoren duerfen die UID als Parameter uebergeben um die Notenliste
	// von anderen Personen anzuzeigen

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	if($rechte->isBerechtigt('admin'))
    {
		$user = $_GET['uid'];
        $getParam = "&uid=" . $user;
    }
    else
        $getParam = "";
}
else
	$getParam='';

$datum_obj = new datum();

$error = '';


if(!check_student($user))
{
	$error .= $p->t('tools/mussAlsStudentEingeloggtSein');
}
else
{
	$qry = "SELECT vw_student.vorname, vw_student.nachname, tbl_studiengang.studiengang_kz 
		FROM public.tbl_studiengang JOIN campus.vw_student USING (studiengang_kz) 
		WHERE campus.vw_student.uid = ".$db->db_add_param($user).";";
	
	if (!$result=$db->db_query($qry))
		die($p->t('tools/studentWurdeNichtGefunden'));
	else
	{
		$row=$db->db_fetch_object($result);
		
		$vorname= $row->vorname;
		$nachname = $row->nachname;
		$stg_obj = new studiengang();
		$stg_obj->load($row->studiengang_kz);
		$stg_name = $stg_obj->bezeichnung_arr[$sprache];
	}

	$notenarr=array();
	$note = new note();
	$note->getAll();
	foreach($note->result as $row)
		$notenarr[$row->note]=$row->bezeichnung;
	
	//Aktuelles Studiensemester ermitteln
	
	$stsem_obj = new studiensemester();
	if($stsem=='')
		$stsem = $stsem_obj->getaktorNext();
	
	$stsem_obj->getAll();

	
	echo "<br />";
	echo "<b>".$p->t('global/name').":</b> $vorname $nachname<br />";
	echo "<b>".$p->t('global/studiengang').":</b>  $stg_name<br />";
	echo "<b>".$p->t('global/studiensemester')."</b> <SELECT name='stsem' onChange=\"MM_jumpMenu('self',this,0)\">";
    echo "<OPTION value='notenliste.php?stsem=alle".$getParam."'>alle Semester</OPTION>";
	foreach ($stsem_obj->studiensemester as $semrow)
	{
		if($stsem == $semrow->studiensemester_kurzbz)
			echo "<OPTION value='notenliste.php?stsem=".$semrow->studiensemester_kurzbz.$getParam."' selected>$semrow->studiensemester_kurzbz</OPTION>";
		else
			echo "<OPTION value='notenliste.php?stsem=".$semrow->studiensemester_kurzbz.$getParam."'>$semrow->studiensemester_kurzbz</OPTION>";
	}
	echo "</SELECT><br />";

	//echo "Datum: ".date('d.m.Y')."<br />";
	echo "<br />";

	//Lehrveranstaltungen und Noten holen
	if($stsem != "alle")
    {
        $sqlFilter = " AND tbl_zeugnisnote.studiensemester_kurzbz = ".$db->db_add_param($stsem)."
                      AND (tbl_lvgesamtnote.studiensemester_kurzbz = ".$db->db_add_param($stsem)." OR tbl_lvgesamtnote.studiensemester_kurzbz is null) ";
    }
    else
        $sqlFilter = "";
    
    $qry = "SELECT
				tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_zeugnisnote.note, tbl_zeugnisnote.punkte,
				tbl_lvgesamtnote.note as lvnote, tbl_lvgesamtnote.punkte as lvpunkte,
				tbl_zeugnisnote.benotungsdatum, tbl_lvgesamtnote.freigabedatum, 
				tbl_lvgesamtnote.benotungsdatum as lvbenotungsdatum
			FROM
				lehre.tbl_lehrveranstaltung, lehre.tbl_zeugnisnote
			LEFT OUTER JOIN
				campus.tbl_lvgesamtnote
			USING (lehrveranstaltung_id, student_uid, studiensemester_kurzbz)
			WHERE
				tbl_zeugnisnote.student_uid = ".$db->db_add_param($user)
			.$sqlFilter."
			AND	tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_zeugnisnote.lehrveranstaltung_id
			ORDER BY bezeichnung";

	if($result=$db->db_query($qry))
	{
		//Tabelle anzeigen
		$tbl= "<table class='tablesorter' id='notenliste' style='width: auto;'>
			<thead>
                <tr class='liste'>
                    <th>".$p->t('global/lehrveranstaltung')."</th>
                    <th>".$p->t('benotungstool/lvNote')."</th>";
            if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
                $tbl.="<th>".$p->t('benotungstool/punkte')."</th>";

            $tbl.="	<th>".$p->t('benotungstool/zeugnisnote')."</th>";
            if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
                $tbl.="<th>".$p->t('benotungstool/punkte')."</th>";

            $tbl.="
                    <th>".$p->t('tools/benotungsdatumDerZeugnisnote')."</th>
                    <th>".$p->t('benotungstool/pruefung')."</th>
                </tr>
            </thead>
            <tbody>";
		$i=0;
		while($row=$db->db_fetch_object($result))
		{
			$lv_obj = new lehrveranstaltung();
			$lv_obj->load($row->lehrveranstaltung_id);
			
			$i++;
			$tbl.= "<tr class='liste".($i%2)."'><td>".$lv_obj->bezeichnung_arr[$sprache].($lv_obj->lehrform_kurzbz!="" && $lv_obj->lehrform_kurzbz!=" - "?" (".$lv_obj->lehrform_kurzbz.")":"")."</td>";
			$tbl.= "<td>";
			
			//Nur freigegebene Noten anzeigen
			if($row->freigabedatum>=$row->lvbenotungsdatum)
			{
				if(isset($notenarr[$row->lvnote]))
					$tbl.=$notenarr[$row->lvnote];
				else
					$tbl.=$row->lvnote;
			}

			$tbl.= "</td>";

			// LV Gesamtnote Punkte
			if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
			{
				$lvpunkte = ($row->lvpunkte!=''?(float)$row->lvpunkte:'');
				$tbl.="<td>".$lvpunkte."</td>";
			}

			if ($row->note != $row->lvnote && $row->lvnote != NULL)
				$markier = " style='border: 1px solid red;'";
			else
				$markier = "";
			$tbl .= "<td".$markier.">";
			
			if(isset($notenarr[$row->note]))
				$tbl.=$notenarr[$row->note];
			else
				$tbl.=$row->note;
			
			$tbl .= "</td>";

			if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
			{
				$punkte = ($row->punkte!=''?((float)$row->punkte):'');
				$tbl.="<td>".$punkte."</td>";
			}

			$tbl .= '<td>'.$datum_obj->formatDatum($row->benotungsdatum,'d.m.Y').'</td>';

			$pruefung = new pruefung();
			$pruefung->getPruefungen($user, null,$row->lehrveranstaltung_id,$stsem);

			if(count($pruefung->result)>0)
			{
				$tbl.='<td>';
				foreach($pruefung->result as $row)
				{
					if(isset($notenarr[$row->note]))
						$note=$notenarr[$row->note];
					else
						$note=$row->note;

					if($row->punkte!='')
						$punkte = ' ('.(float)$row->punkte.')';
					else
						$punkte='';

					$tbl.= $row->pruefungstyp_beschreibung.' '.$datum_obj->formatDatum($row->datum,'d.m.Y').' '.$note.$punkte.'<br>';
					
				}
				$tbl.='</td>';
			}
			else
				$tbl.='<td></td>';

			$tbl .= "</tr>";
		}
		

		$tbl.= "</tbody></table>";
		if($i==0)
			echo $p->t('tools/nochKeineBeurteilungEingetragen');
		else
			echo $tbl;
	}
	else
	{
		$error .= $p->t('tools/fehlerBeimAuslesenDerNoten');
	}
}
echo $error;
echo '</body>
</html>';
?>
