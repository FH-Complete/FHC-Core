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
if(!$rechte->isBerechtigt('student/stammdaten', null, 's'))
	die($rechte->errormsg);

echo '<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">';
include('../../include/meta/jquery.php');
include('../../include/meta/jquery-tablesorter.php');
echo '
	<script language="Javascript">
	$(document).ready(function()
	{
		$("#t1").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"],
		});
	});
	</script>
	</head>
	<body>';
echo "<h2>AbgängerInnenstatistik ".$db->convert_html_chars($stsem);
echo '<span style="position:absolute; right:15px;">'.date('d.m.Y').'</span></h2><br>';
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">Studiensemester: <SELECT name="stsem">';
$studsem = new studiensemester();
$studsem->getAll();

foreach ($studsem->studiensemester as $stsemester)
{
	if($stsemester->studiensemester_kurzbz == $stsem)
		$selected = 'selected';
	else
		$selected = '';

	echo '<option value="'.$stsemester->studiensemester_kurzbz.'" '.$selected.'>';
	echo $stsemester->studiensemester_kurzbz;
	echo '</option>';
}
echo '</SELECT>
	<input type="submit" value="Anzeigen" /></form><br><br>';

if ($stsem != '')
{
	$stgs = $rechte->getStgKz();

	if (count($stgs) == 0)
		$stgwhere = '';
	else
	{
		$stgwhere = ' AND studiengang_kz in(';
		$stgwhere .= $db->db_implode4SQL($stgs);
		$stgwhere .= ' )';
	}

	$qry = "SELECT studiengang_kz, kurzbz, typ, kurzbzlang, bezeichnung, orgform_kurzbz,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abgewiesener'
					AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS abgewiesene,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abgewiesener'
					AND geschlecht ='m' AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS abgewiesene_maennlich,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abgewiesener'
					AND geschlecht ='w' AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS abgewiesene_weiblich,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abbrecher'
					AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS abbrecher,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abbrecher'
					AND geschlecht ='m' AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS abbrecher_maennlich,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Abbrecher'
					AND geschlecht ='w' AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS abbrecher_weiblich,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Unterbrecher'
					AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS unterbrecher,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Unterbrecher'
					AND geschlecht='m' AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS unterbrecher_maennlich,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Unterbrecher'
					AND geschlecht='w' AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS unterbrecher_weiblich,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Absolvent'
					AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS absolvent,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Absolvent'
					AND geschlecht='m' AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS absolvent_maennlich,
				(SELECT count(*) FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_person USING (person_id)
	   			 	WHERE studiengang_kz=stg.studiengang_kz AND status_kurzbz='Absolvent'
					AND geschlecht='w' AND studiensemester_kurzbz=".$db->db_add_param($stsem)."
					) AS absolvent_weiblich
			FROM
				public.tbl_studiengang stg
			WHERE
				studiengang_kz>0 AND studiengang_kz<10000 AND aktiv $stgwhere
			ORDER BY kurzbzlang; ";
	if ($db->db_query($qry))
	{ ?>
		<table id="t1">
				<thead>
					<tr>
						<th></th>
						<th colspan ='3'>Abgewiesene </th>
						<th colspan ='3'>AbbrecherInnen </th>
						<th colspan ='3'>UnterbrecherInnen </th>
						<th colspan ='3'>AbsolventInnen </th>
					</tr>

					<tr>
						<th>Studiengang</th>
						<th>m</th>
						<th>w</th>
						<th>Gesamt</th>
						<th>m</th>
						<th>w</th>
						<th>Gesamt</th>
						<th>m</th>
						<th>w</th>
						<th>Gesamt</th>
						<th>m</th>
						<th>w</th>
						<th>Gesamt</th>
					</tr>
				</thead>
				<tbody>

		<?php while ($row = $db->db_fetch_object())
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
	<?php
	}
}
?>
</body>
</html>