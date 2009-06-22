<?php
/* Copyright (C) 2007 Technikum-Wien
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

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';
	
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen(get_uid());

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body>';

	echo "<h2>Abgängerstatistik $stsem";
	echo '<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>';
	echo '</h2>';
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">Studiensemester: <SELECT name="stsem">';
	$studsem = new studiensemester();
	$studsem->getAll();

	foreach ($studsem->studiensemester as $stsemester)
	{
		if($stsemester->studiensemester_kurzbz==$stsem)
			$selected='selected';
		else 
			$selected='';
		
		echo '<option value="'.$stsemester->studiensemester_kurzbz.'" '.$selected.'>'.$stsemester->studiensemester_kurzbz.'</option>';
	}
	echo '</SELECT>
		<input type="submit" value="Anzeigen" /></form><br><br>';

if($stsem!='')
{
	$stgs = $rechte->getStgKz();
	
	if($stgs[0]=='')
		$stgwhere='';
	else 
	{
		$stgwhere=' AND studiengang_kz in(';
		foreach ($stgs as $stg)
			$stgwhere.="'$stg',";
		$stgwhere = mb_substr($stgwhere,0, mb_strlen($stgwhere)-1);
		$stgwhere.=' )';
	}
	
	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,

				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abgewiesener' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS abgewiesener,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abbrecher' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS abbrecher,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Unterbrecher' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS unterbrecher,
				(SELECT count(*) FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Absolvent' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS absolvent
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere
			ORDER BY kurzbzlang; ";

	if($db->db_query($qry))
	{
		echo "<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>
				<thead>
					<tr>
						<th class='table-sortable:default'>Studiengang</th>
						<th class='table-sortable:numeric'>Abgewiesene</th>
						<th class='table-sortable:numeric'>Abbrecher</th>
						<th class='table-sortable:numeric'>Unterbrecher</th>
						<th class='table-sortable:numeric'>Absolventen</th>
					</tr>
				</thead>
				<tbody>
			 ";
		
		while($row = $db->db_fetch_object())
		{
			echo '<tr>';
			echo "<td>".strtoupper($row->typ.$row->kurzbz)." ($row->kurzbzlang)</td>";
			echo "<td align='center'>$row->abgewiesener</td>";
			echo "<td align='center'>$row->abbrecher</td>";
			echo "<td align='center'>$row->unterbrecher</td>";
			echo "<td align='center'>$row->absolvent</td>";
			echo "</tr>";
		}
		echo '</tbody></table>';
	}
}
?>
</body>
</html>