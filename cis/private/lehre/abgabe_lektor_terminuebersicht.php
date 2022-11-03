<?php
/* Copyright (C) 2010 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/phrasen.class.php');

$uid = get_uid();
$user = '';
$db = new basis_db();
$datum_obj = new datum();
$sprache = getSprache();
$p = new phrasen($sprache);

echo '<!DOCTYPE HTML>
<head>
	<title>Termin&uuml;bersicht</title>
	<meta charset="UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css"/>

	<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
	<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript" src="../../../vendor/jquery-archive/jquery-metadata/jquery.metadata.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function()
			{
			    $("#myTable").tablesorter(
				{
					sortList: [[0,0]],
					widgets: [\'zebra\']
				});
			}
		);

	</script>
</head>
<body>
';

if(isset($_GET['user']))
{
	//Terminliste von anderen Personen darf nur dann angezeigt werden, wenn
	//die entsprechende Berechtigung vorhanden ist
	$rechte = new benutzerberechtigung();
	if(!$rechte->getBerechtigungen($uid))
		die($p->t('global/fehlerBeimLesenAusDatenbank'));
	if(!$rechte->isBerechtigt('lehre/abgabetool'))
		die($p->t('global/keineBerechtigungFuerDieseSeite'));
	$user = $_GET['user'];
}
else
	$user = $uid;
$lektor = new benutzer();
if(!$lektor->load($user))
	die($p->t('global/fehlerBeimErmittelnDerUID'));

$sql_query = "
	SELECT
		distinct tbl_paabgabe.datum, tbl_paabgabe.fixtermin, tbl_paabgabe.kurzbz,
		person_student.vorname as stud_vorname, person_student.nachname as stud_nachname,
		person_student.titelpre as stud_titelpre, person_student.titelpost as stud_titelpost,
		tbl_lehrveranstaltung.semester, UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) as stg,
		tbl_paabgabetyp.bezeichnung as typ_bezeichnung
	FROM
		campus.tbl_paabgabe
		JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
		JOIN lehre.tbl_projektbetreuer USING(projektarbeit_id)
		JOIN public.tbl_benutzer bn_student ON(tbl_projektarbeit.student_uid=bn_student.uid)
		JOIN public.tbl_person person_student ON(bn_student.person_id=person_student.person_id)
		JOIN lehre.tbl_lehreinheit ON(tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id)
		JOIN lehre.tbl_lehrveranstaltung ON(tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id)
		JOIN public.tbl_studiengang ON(tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz)
		JOIN campus.tbl_paabgabetyp USING(paabgabetyp_kurzbz)
	WHERE
		tbl_projektbetreuer.person_id=".$db->db_add_param($lektor->person_id)." AND tbl_paabgabe.datum>=now() AND bn_student.aktiv
	ORDER BY tbl_paabgabe.datum
	";

if($result = $db->db_query($sql_query))
{
	echo "<h2>".$p->t('abgabetool/terminuebersicht')." - $lektor->titelpre $lektor->vorname $lektor->nachname $lektor->titelpost</h2>";

	if($db->db_num_rows($result)>0)
	{
		echo '<table id="myTable" class="tablesorter">';
		echo '
			<thead>
			<tr class="liste">
				<th>'.$p->t('abgabetool/datum').'</th>
				<th>'.$p->t('abgabetool/fix').'</th>
				<th>'.$p->t('abgabetool/typ').'</th>
				<th>'.$p->t('abgabetool/beschreibungAbgabe').'</th>
				<th>'.$p->t('abgabetool/student').'</th>
				<th>'.$p->t('lvplan/stg').'</th>
				<th>'.$p->t('lvplan/sem').'</th>
			</tr>
			</thead>
			<tbody>
			';

		while($row = $db->db_fetch_object($result))
		{
			echo '<tr>';
			echo '<td>'.$datum_obj->formatDatum($row->datum,'d.m.Y').'</td>';
			echo '<td>'.($row->fixtermin=='t'?'Ja':'Nein').'</td>';
			echo '<td>'.$row->typ_bezeichnung.'</td>';
			echo '<td>'.$row->kurzbz.'</td>';
			echo '<td>'.$row->stud_titelpre.' '.$row->stud_vorname.' '.$row->stud_nachname.' '.$row->stud_titelpre.'</td>';
			echo '<td>'.$row->stg.'</td>';
			echo '<td>'.$row->semester.'</td>';
			echo "</tr>\n";
		}

		echo "\n</tbody></table>";
	}
	else
		echo $p->t('abgabetool/keineTermineVorhanden');
}

echo '</body></html>';
?>
