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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/*
 * Liefert eine Statistik ueber folgende Daten des LV-Plans:
 *  - Wie viele Lehreinheiten sind verplant
 *  - Wie viele Stunden sind verplant
 *  - Wie viel % der Stunden sind mehrfach verplant
 * aufgesplittet nach Studiensemester, Studiengang und Ausbildungssemester
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/variable.class.php');
require_once('../../../include/functions.inc.php');

$db = new basis_db();

$user = get_uid();
$variable = new variable();
$variable->loadVariables($user);

//Studiengänge ermitteln, auf die die Person Rechte hat
$qryOE = "	SELECT
				studiengang_kz
			FROM
				public.tbl_organisationseinheit
			JOIN 
			    public.tbl_studiengang USING (oe_kurzbz)
			WHERE
				oe_kurzbz IN(
					SELECT oe_kurzbz 
					FROM public.tbl_benutzerfunktion 
					WHERE 
						funktion_kurzbz='Leitung' 
						AND uid='$user'
						AND (datum_von is null OR datum_von <= now())
						AND (datum_bis is null OR datum_bis >= now())
					)
				OR 
				tbl_organisationseinheit.oe_kurzbz IN(SELECT oe_kurzbz FROM system.vw_berechtigung WHERE uid='$user' AND berechtigung_kurzbz in('admin','assistenz'))";

if($result_rechte = $db->db_query($qryOE))
{
	while($row_rechte = $db->db_fetch_object($result_rechte))
	{
		$stgBerechtigt[] = $row_rechte->studiengang_kz;
	}
}

$stg_get = isset($_GET['stg'])?$_GET['stg'] : '';

$stg = new studiengang();
$stg->loadArray($stgBerechtigt, 'typ, kurzbz');

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else 
{
	$stsem_obj = new studiensemester();
	$stsem_obj->getNearestTillNext();
	$stsem = $stsem_obj->studiensemester_kurzbz;
}

$stsem_obj = new studiensemester();
$stsem_obj->getAll();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/vilesci.css" rel="stylesheet" type="text/css">
</head>

<body>';

echo '<h2>Übersicht - Verplanung der Lehreinheiten ('.$variable->variable->db_stpl_table.')</h2>';

echo '<form method="GET">';
echo 'Studiensemester <select name="stsem">';
foreach ($stsem_obj->studiensemester as $row)
{
	echo '<option value="'.$row->studiensemester_kurzbz.'" '.($row->studiensemester_kurzbz==$stsem?'selected':'').'>'.$row->studiensemester_kurzbz.'</option>';
}
echo '</select><br>';
echo 'Studiengang <select name="stg">';
echo '<option value="">-- Alle Berechtigten --</option>';
foreach($stg->result as $row_stg)
{
	echo '<option value="'.$row_stg->studiengang_kz.'" '.($row_stg->studiengang_kz==$stg_get?'selected':'').'>'.$row_stg->kuerzel.' - '.$row_stg->bezeichnung.'</option>';
}
echo '</select>';
echo '<input type="submit" value="Anzeigen">';
echo '</form>';

$gesamt=0;
$gesamt_verplant=0;
$gesamt_ps=0;
$gesamt_ps_offen=0;
$content='';

//Zeichnet den Fortschrittsbalken
function drawprogress($prozent, $ueberplanung=0)
{
	$color='red';
	if($prozent>=80)
		$color='lightgreen';
	elseif($prozent>=50)
		$color='yellow';
	elseif($prozent>=15)
		$color='pink';
	else
		$color='red';
		
	if($prozent==0)
		$bordercolor='2px solid red';
	else 
		$bordercolor='1px solid black';
		
	$content =  '<div style="border: '.$bordercolor.'; width: 300px"><div style="background-color: '.$color.'; width: '.(intval($prozent*3)).'px">&nbsp;'.$prozent.'%</div>';
	if($ueberplanung>0)
		$content.= '<div style="background-color: gray; width: '.(intval($ueberplanung*3)).'px">&nbsp;+'.$ueberplanung.'% zusätzliche Planstunden</div>';
	$content.= '</div>';
	return $content;
}

//Alle Studiengaenge durchlaufen
$content.= "\n<table>";
$content.= "\n<tr><th>Studiengang/Semester</th><th></th><th colspan='2'>Lehreinheiten</th><th></th><th colspan='2'>Planstunden</th></tr>";
foreach($stg->result as $row_stg)
{
	if (isset($stg_get) && $stg_get != '' && $stg_get != $row_stg->studiengang_kz)
		continue;

	$content.= "\n<tr><td colspan='2'><h3>".$row_stg->kuerzel.'</h3></td></tr>';
	
	//Anzahl der Lehreinheiten holen
	$qry = "SELECT count(*) as anzahl, semester 
			FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
			JOIN lehre.tbl_lehrform ON (tbl_lehreinheit.lehrform_kurzbz=tbl_lehrform.lehrform_kurzbz)
			WHERE studiengang_kz='$row_stg->studiengang_kz' AND studiensemester_kurzbz='$stsem' 
			AND lehreinheit_id IN(SELECT lehreinheit_id FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND tbl_lehreinheitmitarbeiter.planstunden > 0)
			AND tbl_lehreinheit.lehre
			AND tbl_lehrform.verplanen
			GROUP BY semester
			ORDER BY semester ASC";

	if($result_sem = $db->db_query($qry))
	{
		while($row_sem = $db->db_fetch_object($result_sem))
		{
			$content.= '<tr><td>';
			$content.= $row_sem->semester.'.Semester </td><td>';
			
			//Anzahl der verplanten Lehreinheiten holen
			$qry = "SELECT count(*) as verplant FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
                    JOIN lehre.tbl_lehrform ON (tbl_lehreinheit.lehrform_kurzbz=tbl_lehrform.lehrform_kurzbz)
					WHERE studiengang_kz='$row_stg->studiengang_kz' AND studiensemester_kurzbz='$stsem' AND semester='$row_sem->semester' 
					AND tbl_lehreinheit.lehre AND tbl_lehrform.verplanen
					AND lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_".$variable->variable->db_stpl_table." WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id)
					AND lehreinheit_id IN(SELECT lehreinheit_id FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id)";

			if($result_verplant = $db->db_query($qry))
			{
				if($row_verplant = $db->db_fetch_object($result_verplant))
				{
					$gesamt+=$row_sem->anzahl;
					$gesamt_verplant+=$row_verplant->verplant;
					$prozent = round($row_verplant->verplant*100/$row_sem->anzahl,2);
					$content.= '('.$row_verplant->verplant.'/'.$row_sem->anzahl.')';
					$content.= '</td><td></td><td>';
					$content.= drawprogress($prozent);
				}
			}
			$content.= '</td><td width="20px"></td><td>';
			
			//Planstunden holen
			$qry = "SELECT sum(planstunden) as planstunden
					FROM 
						lehre.tbl_lehreinheit 
						JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
						JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
						JOIN lehre.tbl_lehrform ON (tbl_lehreinheit.lehrform_kurzbz=tbl_lehrform.lehrform_kurzbz)	
					WHERE
						tbl_lehrveranstaltung.studiengang_kz='$row_stg->studiengang_kz' AND
						tbl_lehrveranstaltung.semester='$row_sem->semester' AND
						tbl_lehreinheit.studiensemester_kurzbz='$stsem' AND
						tbl_lehreinheit.lehre AND
						tbl_lehrform.verplanen";
			$ps=0;
			if($result_ps = $db->db_query($qry))
			{
				if($row_ps = $db->db_fetch_object($result_ps))
				{
					$ps = $row_ps->planstunden;
				}
			}
			
			//verplante Stunden aus LVPlan holen
			$qry = "SELECT count(*) as verplant
					FROM (SELECT distinct datum, stunde, tbl_lehreinheit.lehreinheit_id, tbl_".$variable->variable->db_stpl_table.".mitarbeiter_uid
					FROM 
						lehre.tbl_lehreinheit 
						JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
						JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
						JOIN lehre.tbl_".$variable->variable->db_stpl_table." USING(lehreinheit_id)
						JOIN lehre.tbl_lehrform ON (tbl_lehreinheit.lehrform_kurzbz=tbl_lehrform.lehrform_kurzbz)
					WHERE
						tbl_lehrveranstaltung.studiengang_kz='$row_stg->studiengang_kz' AND
						tbl_lehrveranstaltung.semester='$row_sem->semester' AND
						tbl_lehreinheit.studiensemester_kurzbz='$stsem' AND
						tbl_lehreinheit.lehre AND
						tbl_lehrform.verplanen
					) a";
			$stdverplant=0;
			
			if($result_std = $db->db_query($qry))
			{
				if($row_std = $db->db_fetch_object($result_std))
				{
					$stdverplant = $row_std->verplant;
				}
			}
		
			//offene Stunden ermitteln			
			$qry = "
			SELECT distinct lehreinheit_id, planstunden, mitarbeiter_uid 
			FROM lehre.tbl_lehreinheit 
			    JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
			    JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
				JOIN lehre.tbl_lehrform ON (tbl_lehreinheit.lehrform_kurzbz=tbl_lehrform.lehrform_kurzbz) 
			WHERE studiengang_kz='$row_stg->studiengang_kz' 
			  AND semester='$row_sem->semester' 
			  AND studiensemester_kurzbz='$stsem' 
			  AND tbl_lehreinheit.lehre
			  AND tbl_lehrform.verplanen";
			
			$offen=0;
			if($result_std = $db->db_query($qry))
			{
				while($row_std = $db->db_fetch_object($result_std))
				{
					$qry = "SELECT count(*) as anzahl FROM lehre.tbl_".$variable->variable->db_stpl_table." WHERE lehreinheit_id='$row_std->lehreinheit_id' AND mitarbeiter_uid='$row_std->mitarbeiter_uid'";
					if($result_o = $db->db_query($qry))
					{
						if($row_o = $db->db_fetch_object($result_o))
						{
							if($row_o->anzahl<$row_std->planstunden)
								$offen+=$row_std->planstunden-$row_o->anzahl;
						}
					}
				}
			}
			
			
			$gesamt_ps+=$ps;
			$gesamt_ps_offen+=$offen;
			$content.= "($stdverplant/$ps)";
			if($ps==0)
				$prozent=0;
			else
				$prozent = round(($ps-$offen)*100/$ps,2);
			
			//Ueberbuchung berechnen
			/* 
			   Es werden teilweise Stunden mehrfach verplant damit Lektoren die Stunden der anderen 
			   Lektoren der gleichen LV sehen koennen. Deshalb ist es auch notwendig die offenen Stunden aus der DB zu holen
			   anstatt zu berechnen
			 */
			if($ps==0)
				$prozentueber=0;
			else
				$prozentueber = round(($stdverplant-$ps+$offen)*100/$ps,2);

			$content.= '</td><td nowrap>';
			$content.=drawprogress($prozent, $prozentueber);
			$content.= 'offene Planstunden: '.$offen;
			$content.='</td></tr>';
		}
		$content.='<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
	}
	else 
	{
		$content.= 'Fehler';
	}
}
$content.= '</table>';
//Prozent der gesamten verplanten Lehreinheiten berechnen und anzeigen
if($gesamt==0)
	$prozent=0;
else
	$prozent = round($gesamt_verplant*100/$gesamt,2);
echo "<br><hr>\n<table><tr><td><b>Lehreinheiten:</b> (".$gesamt_verplant.'/'.$gesamt.')</td><td width="20px"></td><td>';
echo drawprogress($prozent);
echo "</td></tr><tr><td>";
//Prozent der gesamten verplanten Stunden berechnen und anzeigen
$gesamt_ps_verplant = ($gesamt_ps-$gesamt_ps_offen);
if($gesamt_ps==0)
	$prozent=0;
else
	$prozent = round($gesamt_ps_verplant*100/$gesamt_ps,2);
echo "<b>Planstunden:</b> (".$gesamt_ps_verplant.'/'.$gesamt_ps.')</td><td width="20px"></td><td>';
echo drawprogress($prozent);

echo "</td></tr></table>\n<hr>";
echo $content;
?>
</body>
</html>