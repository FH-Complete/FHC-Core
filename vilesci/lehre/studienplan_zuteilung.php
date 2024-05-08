<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Martin Tatzber <tatzberm@technikum-wien.at
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/prestudent.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('assistenz', null, 'suid'))
	die('keine Berechtigung für diese Seite!');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if(isset($_REQUEST['stg_kz']))
	$stg_kz=$_REQUEST['stg_kz'];
else
	$stg_kz='';

if(isset($_POST["schick"]))
{
	$studienplan_id=$_POST["stpl"];

	$prestudent_id=$_POST["prestudent_id"];
	$prestudent_rollen=new prestudent();
	$prestudent_rollen->getPrestudentRolle($prestudent_id);
	if($studienplan_id!='')
	{
		foreach($prestudent_rollen->result as $rolle)
		{
			$prestudent=new prestudent();
			$prestudent->load_rolle($rolle->prestudent_id, $rolle->status_kurzbz, $rolle->studiensemester_kurzbz, $rolle->ausbildungssemester);
			$prestudent->studienplan_id=$studienplan_id;
			if(!$prestudent->save_rolle())
				echo 'Fehler: '.$prestudent->errormsg;
		}
	}
}

$output='<h1>Zuteilung von Studenten zum zugehörigen Studienplan</h1>
<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
Studiengang: <select name="stg_kz" onchange="this.form.submit()"><option value="">-- Alle --</option>';

$studiengang=new studiengang();
$studiengang->getAll('typ,kurzbz');
foreach ($studiengang->result as $stg)
{
	if($stg->studiengang_kz==$stg_kz)
		$selected=' selected';
	else
		$selected='';
	$output .= '<option value="'.$stg->studiengang_kz.'"'.$selected.'>'.$stg->kurzbzlang.' - '.$stg->bezeichnung.'</option>';
}
$output .= '</select>
	</form>';

$limit=20;
$qry_from_where=" FROM public.tbl_prestudent
	JOIN public.tbl_person USING(person_id)
	JOIN public.tbl_student USING(prestudent_id)
	JOIN public.tbl_benutzer ON(student_uid=uid)
	WHERE NOT EXISTS(
		SELECT 1 FROM public.tbl_prestudentstatus
		WHERE tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id
		AND tbl_prestudentstatus.studienplan_id is not null)
	AND tbl_benutzer.aktiv = TRUE
	AND get_rolle_prestudent(prestudent_id,null) in('Student','Diplomand','Unterbrecher')";

if($stg_kz!='')
{
	$qry_from_where .= " AND tbl_prestudent.studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER);
	$qry_order_by = " ORDER BY nachname";
}
else
	$qry_order_by = " ORDER BY tbl_prestudent.studiengang_kz, nachname";
$qry_limit=" LIMIT ".$limit;

$count_qry="SELECT count(*) as count".$qry_from_where;
$select_qry="SELECT tbl_person.vorname, tbl_person.nachname, tbl_prestudent.prestudent_id, tbl_prestudent.studiengang_kz,
				get_rolle_prestudent(prestudent_id,null) as laststatus".$qry_from_where.$qry_order_by.$qry_limit;

$count='';
if($result=$db->db_query($count_qry))
{
	if($row=$db->db_fetch_object($result))
	{
		$count=$row->count;
	}
}

$output .= 'Zeige '.($count<$limit?$count:$limit).' von '.$count;

//if($stg_kz!='')
//{
	$output .= '
<table class="tablesorter" id="t1">
	<thead>
		<th>Vorname</th>
		<th>Nachname</th>
		<th>Studiengang</th>
		<th>Status</th>
		<th>Studienplan</th>
	</thead>
	<tbody>';
	
	$studiengang=new studiengang();
	if($result=$db->db_query($select_qry))
	{
		while($row=$db->db_fetch_object($result))
		{
			$studiengang->load($row->studiengang_kz);
			$output .= '
		<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
		<input type="hidden" name="stg_kz" value="'.$stg_kz.'" />
		<tr>
			<td>'.$row->vorname.'</td>
			<td>'.$row->nachname.'</td>
			<td>'.$studiengang->kurzbzlang.' - '.$studiengang->bezeichnung.'</td>';

			$prestudent=new prestudent();
			$prestudent->getLastStatus($row->prestudent_id);

			$output .= '<td>'.$prestudent->status_kurzbz.'</td>
			<td>
				<input type="hidden" name="prestudent_id" value="'.$row->prestudent_id.'" />
				<select name="stpl">
					<option value="">-- keine Auswahl--</option>';
			$studienplan=new studienplan();
			$studienplan->getStudienplaene($row->studiengang_kz);
			foreach($studienplan->result as $stpl)
			{
				$output .= '<option value="'.$stpl->studienplan_id.'">'.$db->convert_html_chars($stpl->bezeichnung.' ('.$stpl->studienplan_id.')').'</option>';
			}
			$output .= '
				</select><input type="submit" name="schick" value="Speichern"/>
			</td>
		</tr></form>';
		}
	}
	$output .= '</tbody></table>';
//}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
	<script type="text/javascript">
		$(document).ready(function() 
		{ 
			$("#t1").tablesorter(); 
		}); 
	</script>

	<title>Studienplan Zuteilung</title>
</head>
<body>
	<?php echo $output; ?>
</body>
</html>