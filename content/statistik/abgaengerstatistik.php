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

	echo "<h2>AbgängerInnenstatistik $stsem";
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

				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abgewiesener' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS abgewiesene,			
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abgewiesener' AND geschlecht ='m' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS abgewiesene_maennlich,
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abgewiesener' AND geschlecht ='w' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS abgewiesene_weiblich,
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abbrecher' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS abbrecher,
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abbrecher' AND geschlecht ='m' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS abbrecher_maennlich,
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abbrecher' AND geschlecht ='w' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS abbrecher_weiblich,
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Unterbrecher' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS unterbrecher,
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Unterbrecher' AND geschlecht='m' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS unterbrecher_maennlich,
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Unterbrecher' AND geschlecht='w' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS unterbrecher_weiblich,
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Absolvent' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS absolvent,
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Absolvent' AND geschlecht='m' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS absolvent_maennlich,
				(SELECT count(*) FROM public.tbl_prestudent 
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Absolvent' AND geschlecht='w' AND studiensemester_kurzbz='".addslashes($stsem)."'
					) AS absolvent_weiblich
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere
			ORDER BY kurzbzlang; ";
	if($db->db_query($qry))
	{ ?>
		<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>
				<thead>
					<tr>
						<th></th>
						<th colspan ='3'>Abgewiesene </th>
						<th colspan ='3'>AbbrecherInnen </th>
						<th colspan ='3'>UnterbrecherInnen </th>
						<th colspan ='3'>AbsolventInnen </th>
					</tr>

					<tr>
						<th class='table-sortable:default'>Studiengang</th>
						<th class='table-sortable:numeric'>m</th>
						<th class='table-sortable:numeric'>w</th>
						<th class='table-sortable:numeric'>Gesamt</th>
						<th class='table-sortable:numeric'>m</th>
						<th class='table-sortable:numeric'>w</th>
						<th class='table-sortable:numeric'>Gesamt</th>
						<th class='table-sortable:numeric'>m</th>
						<th class='table-sortable:numeric'>w</th>
						<th class='table-sortable:numeric'>Gesamt</th>
						<th class='table-sortable:numeric'>m</th>
						<th class='table-sortable:numeric'>w</th>
						<th class='table-sortable:numeric'>Gesamt</th>
					</tr>
				</thead>
				<tbody>
		
		<?php while($row = $db->db_fetch_object())
		{ ?>
			<tr>
				<td><?php echo strtoupper($row->typ.$row->kurzbz)?> (<?php echo $row->kurzbzlang ?>)</td>
				<td align='center'><?php echo $row->abgewiesene_maennlich ?></td>
				<td align='center'><?php echo $row->abgewiesene_weiblich ?></td>
				<td align='center'><?php echo $row->abgewiesene ?></td>
				<td align='center'><?php echo $row->abbrecher_maennlich ?></td>
				<td align='center'><?php echo $row->abbrecher_weiblich ?></td>
				<td align='center'><?php echo $row->abbrecher ?></td>
				<td align='center'><?php echo $row->unterbrecher_maennlich ?></td>
				<td align='center'><?php echo $row->unterbrecher_weiblich ?></td>
				<td align='center'><?php echo $row->unterbrecher ?></td>
				<td align='center'><?php echo $row->absolvent_maennlich ?></td>
				<td align='center'><?php echo $row->absolvent_weiblich ?></td>
				<td align='center'><?php echo $row->absolvent ?></td>
			</tr>
		<?php } ?>
		</tbody></table>
	<?php }
}
?>
</body>
</html>