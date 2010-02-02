<?php
/* Copyright (C) 2010 Technikum-Wien
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
/**
 * Detailierte Auswertung der Reihungstests
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/gebiet.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Testtool Auswertung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../../skin/style.css.php">
	</head>
	<body>
	<h1>Testtool Auswertung - Detail</h1>
';

$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'');
$gebiet_id = (isset($_GET['gebiet_id'])?$_GET['gebiet_id']:'');
$db = new basis_db();

echo '
	<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
	<table>
		<tr>
			<td>Studiengang</td>
			<td>
				<SELECT name="stg_kz">
					<OPTION value="">-- keine Auswahl --</OPTION>';

$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz');
foreach ($stg_obj->result as $row)
{
	if($row->studiengang_kz == $stg_kz)
		$selected='selected="selected"';
	else 
		$selected='';
	
	echo '	<OPTION value="'.$row->studiengang_kz.'" '.$selected.'>'.$row->kuerzel.'('.$row->kurzbzlang.')</OPTION>';
}
echo '
			</SELECT>
		</td>
		<td>Gebiet</td>
		<td>
			<SELECT name="gebiet_id">
				<OPTION value="">-- keine Auswahl --</OPTION>';
$gebiet_obj = new gebiet();
$gebiet_obj->getAll();
foreach ($gebiet_obj->result as $row)
{
	if($row->gebiet_id == $gebiet_id)
		$selected='selected="selected"';
	else 
		$selected='';
	
	echo '	<OPTION value="'.$row->gebiet_id.'" '.$selected.'>'.$row->bezeichnung.' - '.$row->kurzbz.'</OPTION>';
}
echo '
			</SELECT>
		</td>
		
		<td><input type="submit" name="show" value="Anzeigen"></td>

		</tr>
	</table>';

if(isset($_GET['show']))
{
	$qry = "SELECT * FROM (
			SELECT 
				distinct on(tbl_frage.frage_id) *, tbl_gebiet.kurzbz as gebiet
			FROM 
				testtool.tbl_frage 
				JOIN testtool.tbl_ablauf USING(gebiet_id)
				JOIN testtool.tbl_frage_sprache USING(frage_id)
				JOIN testtool.tbl_gebiet USING(gebiet_id)
			WHERE 
				demo=false
				AND tbl_frage_sprache.sprache='German'";
	if($stg_kz!='')
		$qry.=" AND studiengang_kz='".addslashes($stg_kz)."'";
	if($gebiet_id!='')
		$qry.=" AND gebiet_id='".addslashes($gebiet_id)."'";
	$qry.=") as a ORDER BY gebiet_id, nummer";
	
	//echo $qry.'<br><br>';
	if($result = $db->db_query($qry))
	{
		echo '<br />
			<table>
				<tr class="liste">
					<th></th>
					<th>Gebiet</th>
					<th>Frage</th>
					<th title="Anzahl der Personen die diese Frage gestellt bekommen haben">Gesamt</th>
					<th colspan="16">Vorschlag/Punkte/Anzahl</th>
				</tr>';
		$i=0;
		while($row = $db->db_fetch_object($result))
		{
			$i++;
			$hlp='';
			$hlp .='<tr class="liste'.($i%2).'">
				<td><a href="auswertung_detail_frage.php?frage_id='.$row->frage_id.'">Details</a></td>
				<td nowrap>'.$row->gebiet.'</td>
				<td>'.strip_tags($row->text).' ('.$row->nummer.')</td>';
			
			$qry = "SELECT 
						count(*) as anzahl 
					FROM 
						testtool.tbl_pruefling_frage 
						JOIN testtool.tbl_pruefling USING(pruefling_id)
					WHERE
						frage_id=$row->frage_id";
			if($stg_kz!='')
				$qry.=" AND studiengang_kz='".addslashes($stg_kz)."'";
			
			$qry_vorschlag = "
				SELECT 
					vorschlag_id, nummer, punkte, count(*) as anzahl_vorschlag, ($qry) as anzahl_gesamt,
					(SELECT text FROM testtool.tbl_vorschlag_sprache WHERE vorschlag_id=tbl_vorschlag.vorschlag_id AND sprache='German' LIMIT 1) as text
				FROM 
					testtool.tbl_vorschlag 
					JOIN testtool.tbl_antwort USING(vorschlag_id)
					JOIN testtool.tbl_pruefling USING(pruefling_id)
				WHERE 
					frage_id='$row->frage_id' ";
			if($stg_kz!='')
				$qry_vorschlag.=" AND studiengang_kz='".addslashes($stg_kz)."'";
			
			$qry_vorschlag.="
				GROUP BY
					vorschlag_id, nummer, punkte
				ORDER BY punkte DESC, vorschlag_id";
			//echo $qry_vorschlag.'<br>';
			$hlp2='';
			$gesamt =0;
			if($result_vorschlag = $db->db_query($qry_vorschlag))
			{
				while($row_vorschlag = $db->db_fetch_object($result_vorschlag))
				{
					$hlp2.= '
						<td style="border-left: 1px solid black">&nbsp;'.$row_vorschlag->text.'</td>
						<td>'.number_format($row_vorschlag->punkte,2).' Pkt</td>
						<td><b>'.$row_vorschlag->anzahl_vorschlag.'</b></td>';
					
					$gesamt = $row_vorschlag->anzahl_gesamt;
				}
			}
			echo $hlp."<td><b>$gesamt</b></td>".$hlp2;
			echo '</tr>';
		}
		echo '</table>';
	}
}
?>
	</body>
</html>