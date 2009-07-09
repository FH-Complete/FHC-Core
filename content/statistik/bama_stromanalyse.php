<?php
/* Copyright (C) 2009 Technikum-Wien
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
/*******************************************************************************************************
 *				stromanalyse - Auswertung der Studentenstroeme in der FHTW
 *******************************************************************************************************/
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$db = new basis_db();
$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$htmlstr='';
$ausgabe='';
$summe=0;
$rest=0;

$studiensemester_kurzbz='';

$studiensemester_kurzbz = (isset($_REQUEST['studiensemester_kurzbz'])?$_REQUEST['studiensemester_kurzbz']:'-1');
if($studiensemester_kurzbz != -1)
{

	$ausgabe = "<H2>Master-Studiengänge: ($studiensemester_kurzbz)</H2>";

	$qry_stg="SELECT * FROM public.tbl_studiengang WHERE typ='m' ORDER by bezeichnung, studiengang_kz";
	$result_stg=$db->db_query($qry_stg);
	while ($row_stg = $db->db_fetch_object($result_stg))
	{
		$summe=0;
		$rest=0; 
		
		//Studiengaenge, die zuvor abgeschlossen wurden
		$qry_master="SELECT DISTINCT count(*)as count ,studiengang_kz, typ, tbl_studiengang.bezeichnung as bez, tbl_studiengang.kurzbz  
		FROM public.tbl_person JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
		JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
		JOIN public.tbl_studiengang USING(studiengang_kz) 
		WHERE status_kurzbz='Absolvent' AND typ!='m' 
			AND public.tbl_person.person_id IN(SELECT public.tbl_person.person_id FROM public.tbl_person 
			JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
			JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
			WHERE studiengang_kz='".addslashes($row_stg->studiengang_kz)."' 
			AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' 
			AND status_kurzbz='Student' 
			AND ausbildungssemester='1') 
		GROUP BY studiengang_kz, typ, public.tbl_studiengang.bezeichnung, tbl_studiengang.kurzbz ORDER BY count desc"; 
		
		//Anzahl der Studenten ohne AbschluÃ¾ auf der FHTW
		
		//Anzahl der Studenten im 1.Semester des MasterStg
		$qry_anzahl="SELECT count(*) as anzahl FROM public.tbl_person 
			JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
			JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
			WHERE studiengang_kz='".addslashes($row_stg->studiengang_kz)."' 
			AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' 
			AND status_kurzbz='Student' 
			AND ausbildungssemester='1'";
		if(!$result_anzahl=$db->db_query($qry_anzahl))
			die($db->db_last_error());
		$row_anzahl=$db->db_fetch_object($result_anzahl);
		
		$ausgabe .= "<TABLE width=90% style='border:3px solid #D3DCE3;border-spacing:0pt;-moz-border-radius-topleft:10px;-moz-border-radius-topright:10px;-khtml-border-radius-topleft:10px;-khtml-border-radius-topright:10px;' align='center'>";
		$ausgabe .= "<TR style='color:#000000; background-color:#D3DCE3;font: bold 1.2em arial;'><TD colspan='4'>&nbsp;&nbsp;&nbsp;";
		$ausgabe .= "<a href='bama_studentenstrom.svg.php?stsem=WS2008&studiengang_kz=".$row_stg->studiengang_kz."&typ=m&kurz=".$row_stg->kurzbz."' target='_blank'>";
		$ausgabe .= "Studiengang: $row_stg->studiengang_kz, $row_stg->bezeichnung (".strtoupper($row_stg->typ.$row_stg->kurzbz).")</a></TD><TD align='center'  width='20%'>$row_anzahl->anzahl Studierende im 1.Sem.</TD></TR></TABLE>";
		$ausgabe .= "<TABLE width=90% style='border:3px solid #D3DCE3;border-spacing:0pt;' align='center'>";
		$ausgabe .= "<TH width='10%' style='background-color:#dddddd;border:1px solid #D3DCE3;'>Kz</TH>";
		$ausgabe .= "<TH width='10%' style='background-color:#dddddd;border:1px solid #D3DCE3;'>Typ</TH>";
		$ausgabe .= "<TH width='60%' style='background-color:#dddddd;border:1px solid #D3DCE3;'>Name</TH>";
		$ausgabe .= "<TH width='10%' style='background-color:#dddddd;border:1px solid #D3DCE3;'>Anzahl</TH>";
		$ausgabe .= "<TH width='10%' style='background-color:#dddddd;border:1px solid #D3DCE3;'>Prozent</TH>";
		$result_master=$db->db_query($qry_master);
		$i=0;
		while ($row_master=$db->db_fetch_object($result_master))
		{
				$color=(($i%2==0)?"#F3F3E9":"#EFEFDD");
				$ausgabe .= "<TR style='background-color:$color;'>";
				$ausgabe .= "<TD style='border:1px solid #D3DCE3;'align='center'>$row_master->studiengang_kz</TD>";
				$ausgabe .= "<TD style='border:1px solid #D3DCE3;'align='center'>$row_master->typ</TD>";
				$ausgabe .= "<TD style='border:1px solid #D3DCE3;'>$row_master->bez</TD>";
				$ausgabe .= "<TD style='border:1px solid #D3DCE3;'align='center'>$row_master->count</TD>";
				$ausgabe .= "<TD style='border:1px solid #D3DCE3;'align='center'>".round((100/$row_anzahl->anzahl)*$row_master->count,2)."%</TD>";
				$ausgabe .= "</TR>";
				$summe += $row_master->count;
				$i++;
		}
		//$rest=$row_rest->rest;
		$rest=$row_anzahl->anzahl-$summe;
		if($rest>0)
		{
			$color=(($i%2==0)?"#F3F3E9":"#EFEFDD");
			$ausgabe .= "<TR style='background-color:$color;'>";
			$ausgabe .= "<TD style='border:1px solid #D3DCE3;' align='center'>-</TD>";
			$ausgabe .= "<TD style='border:1px solid #D3DCE3;' align='center'>-</TD>";
			$ausgabe .= "<TD style='border:1px solid #D3DCE3;'>extern</TD>";
			$ausgabe .= "<TD style='border:1px solid #D3DCE3;' align='center'>$rest</TD>";
			$ausgabe .= "<TD style='border:1px solid #D3DCE3;' align='center'>".round((100/$row_anzahl->anzahl)*$rest,2)."%</TD>";
			$ausgabe .= "</TR>";
		}
		//$ausgabe .="</TABLE>".$summe."+".$rest."=".($summe+$rest)."=".$row_anzahl->anzahl."?<BR><BR>";
		$ausgabe .="</TABLE><BR><BR>";
	}
	
	$ausgabe .= "<H2>Bachelor-Studiengänge: (SS".substr($studiensemester_kurzbz,-4)."/$studiensemester_kurzbz)</H2>";
	$qry_stg="SELECT * FROM public.tbl_studiengang WHERE typ='b' ORDER by bezeichnung,studiengang_kz";
	$result_stg=$db->db_query($qry_stg);
	while ($row_stg=$db->db_fetch_object($result_stg))
	{
		$summe=0;
		$rest=0; 
		
		//Master-Studiengänge, die noch besucht wurden
		$qry_bachelor="SELECT DISTINCT count(*)as count, studiengang_kz, typ, bezeichnung as bez, kurzbz FROM 
		(SELECT DISTINCT ON(public.tbl_person.person_id, studiengang_kz) studiengang_kz,typ, tbl_studiengang.bezeichnung, tbl_studiengang.kurzbz   
		FROM public.tbl_person JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
		JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
		JOIN public.tbl_studiengang USING(studiengang_kz) 
		WHERE status_kurzbz='Student' AND typ='m' 
			AND public.tbl_person.person_id IN(SELECT public.tbl_person.person_id FROM public.tbl_person 
			JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
			JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
			WHERE studiengang_kz='".addslashes($row_stg->studiengang_kz)."'  
			AND status_kurzbz='Absolvent'
			AND (studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' OR studiensemester_kurzbz='SS".substr($studiensemester_kurzbz,-4)."') )) as b 
		GROUP BY studiengang_kz, typ, bezeichnung, kurzbz ORDER BY count desc";
		
		//Anzahl der Studenten ohne weitere Masterstudien am FHTW

		
		//Anzahl der Absolventen des Studiengangs
		$qry_anzahl="SELECT count(*) as anzahl FROM public.tbl_person 
			JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
			JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
			WHERE studiengang_kz='".addslashes($row_stg->studiengang_kz)."' 
			AND status_kurzbz='Absolvent'
			AND (studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' OR studiensemester_kurzbz='SS".substr($studiensemester_kurzbz,-4)."')";
		$result_anzahl=$db->db_query($qry_anzahl);
		$row_anzahl=$db->db_fetch_object($result_anzahl);
		
		$ausgabe .= "<TABLE width=90% style='border:3px solid #D3DCE3;border-spacing:0pt;-moz-border-radius-topleft:10px;-moz-border-radius-topright:10px;-khtml-border-radius-topleft:10px;-khtml-border-radius-topright:10px;' align='center'>";
		$ausgabe .= "<TR style='color:#000000; background-color:#D3DCE3;font: bold 1.2em arial;'>";
		$ausgabe .= "<TD colspan='4'>&nbsp;&nbsp;&nbsp;";
		$ausgabe .= "<a href='bama_studentenstrom.svg.php?stsem=WS2008&studiengang_kz=".$row_stg->studiengang_kz."&typ=b&kurz=".$row_stg->kurzbz."' target='_blank'>";
		$ausgabe .= "Studiengang: $row_stg->studiengang_kz, $row_stg->bezeichnung (".strtoupper($row_stg->typ.$row_stg->kurzbz).")</a></TD>";
		$ausgabe .= "<TD align='center'  width='20%'>$row_anzahl->anzahl Absolventen</TD></TR></TABLE>";
		$ausgabe .= "<TABLE width=90% style='border:3px solid #D3DCE3;border-spacing:0pt;' align='center'>";
		$ausgabe .= "<TH width='10%' style='background-color:#dddddd;border:1px solid #D3DCE3;'>Kz</TH>";
		$ausgabe .= "<TH width='10%' style='background-color:#dddddd;border:1px solid #D3DCE3;'>Typ</TH>";
		$ausgabe .= "<TH width='60%' style='background-color:#dddddd;border:1px solid #D3DCE3;'>Name</TH>";
		$ausgabe .= "<TH width='10%' style='background-color:#dddddd;border:1px solid #D3DCE3;'>Anzahl</TH>";
		$ausgabe .= "<TH width='10%' style='background-color:#dddddd;border:1px solid #D3DCE3;'>Prozent</TH>";
		$result_bachelor=$db->db_query($qry_bachelor);
		$i=0;
		while ($row_bachelor=$db->db_fetch_object($result_bachelor))
		{
				$color=(($i%2==0)?"#F3F3E9":"#EFEFDD");
				$ausgabe .= "<TR style='background-color:$color;'>";
				$ausgabe .= "<TD style='border:1px solid #D3DCE3;'align='center'>$row_bachelor->studiengang_kz</TD>";
				$ausgabe .= "<TD style='border:1px solid #D3DCE3;'align='center'>$row_bachelor->typ</TD>";
				$ausgabe .= "<TD style='border:1px solid #D3DCE3;'>$row_bachelor->bez</TD>";
				$ausgabe .= "<TD style='border:1px solid #D3DCE3;'align='center'>$row_bachelor->count</TD>";
				$ausgabe .= "<TD style='border:1px solid #D3DCE3;'align='center'>".round((100/$row_anzahl->anzahl)*$row_bachelor->count,2)."%</TD>";
				$ausgabe .= "</TR>";
				$summe += $row_bachelor->count;
				$i++;
		}
		//$rest=$row_rest->rest;
		$rest=$row_anzahl->anzahl-$summe;
		//echo "-->".$rest."<--";
		if($rest>0)
		{
			$color=(($i%2==0)?"#F3F3E9":"#EFEFDD");
			$ausgabe .= "<TR style='background-color:$color;'>";
			$ausgabe .= "<TD style='border:1px solid #D3DCE3;' align='center'>-</TD>";
			$ausgabe .= "<TD style='border:1px solid #D3DCE3'; align='center'>-</TD>";
			$ausgabe .= "<TD style='border:1px solid #D3DCE3;'>extern</TD>";
			$ausgabe .= "<TD style='border:1px solid #D3DCE3;' align='center'>$rest</TD>";
			$ausgabe .= "<TD style='border:1px solid #D3DCE3;' align='center'>".round((100/$row_anzahl->anzahl)*$rest,2)."%</TD>";
			$ausgabe .= "</TR>";
		}
		$ausgabe .="</TABLE><BR><BR>";
	}
}
	
echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>BaMa Stromanalyse</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body class="Background_main"  style="background-color:#eeeeee;">
<h2>BaMa Stromanalyse</h2>
Wählen Sie bitte nachfolgend ein Wintersemester aus.';

$htmlstr .= "<form action='".$_SERVER['PHP_SELF']."' method='POST' name='strom'>\n";
$htmlstr .= "<select name='studiensemester_kurzbz'>\n";
$qry_sem = "SELECT * FROM public.tbl_studiensemester WHERE studiensemester_kurzbz LIKE 'WS%' ORDER BY start";
$result_sem=$db->db_query($qry_sem);
while ($row_sem=$db->db_fetch_object($result_sem))
{
	if($row_sem->studiensemester_kurzbz==$studiensemester_kurzbz)
	{
		$selected="selected='selected'";
	}
	else 
	{
		$selected='';
	}
	$htmlstr .= "<option $selected value='".$row_sem->studiensemester_kurzbz."'>".$row_sem->bezeichnung."</option>";
}		
$htmlstr .= "</select>\n";
$htmlstr .= "<input type='submit' name='speichern' value='OK' title='Studiensemester bestimmen'>";
$htmlstr .= "</form>\n";

	echo $htmlstr;
	echo $ausgabe;
	
echo "Anmerkungen:<br><br>Doppelvorkommen von Studierenden führt zu Verfaelschungen bei der Anzahl der 'Externen':<br>
- Absolventen bzw. Studenten in verschiedenen Studiengaengen.<br>
- Doppelteintragungen: z.B. nach Abbruch neu inskribiert";
?>