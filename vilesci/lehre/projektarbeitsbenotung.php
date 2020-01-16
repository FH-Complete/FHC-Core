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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/projektarbeit.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/projektbetreuer.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/note.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if (!$user=get_uid())
	die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$stg_kz = (isset($_REQUEST['stg_kz'])?$_REQUEST['stg_kz']:'');
$stsem = (isset($_REQUEST['stsem'])?$_REQUEST['stsem']:'');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_kz, 'suid'))
	die('Sie haben keine Berechtigung f&uuml;r diesen Studiengang. !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

if($stsem=='')
{
	$stsem_obj = new studiensemester();
	$stsem = $stsem_obj->getaktorNext();
}

if($stg_kz=='')
	$stg_kz='0';

echo '<html>
	<head>
		<title>Projektarbeitsbenotung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">';
include('../../include/meta/jquery.php');
include('../../include/meta/jquery-tablesorter.php');

echo '
	<script language="Javascript">
	$(document).ready(function()
	{
		$("#t1").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
	});
	</script>
	</head>
	<body class="Background_main">
	<h2>Projektarbeit - Benotung</h2>
	';

if(isset($_POST['savedata']))
{
	$errormsg = '';
	foreach($_POST as $key=>$data)
	{
		if(mb_strstr($key, 'note_'))
		{
			$id = mb_substr($key, 5);
			$prj = new projektarbeit();
			if($prj->load($id))
			{
				if($prj->note!=$data)
				{
					$prj->note = $data;
					$prj->updateamum = date('Y-m-d H:i:s');
					$prj->updatevon = $user;

					if(!$prj->save(false))
					{
						$errormsg .="Fehler beim Speichern von $prj->projektarbeit_id:".$prj->errormsg.'<br>';
					}
				}
			}
		}
	}

	if($errormsg!='')
	{
		echo $errormsg;
	}
	else
	{
		echo '<b>Daten wurden gespeichert</b><br><br>';
	}
}

$noten = new note();
$noten->getAll();

$stg_arr = array();
$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz', false);

foreach ($stg_obj->result as $row)
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;

echo '<form action="'.$_SERVER['PHP_SELF'].'" mehtod="GET">';
echo 'Studiengang: <SELECT name="stg_kz">';

if($rechte->isBerechtigt('admin', null, 'suid'))
{
	foreach($stg_obj->result as $row)
		$stgs[] = $row->studiengang_kz;
}
else
	$stgs = $rechte->getStgKz('assistenz');

foreach ($stgs as $kz)
{
	echo '<option '.($stg_kz==$kz?' selected="selected" ':'').' value="'.$kz.'" >'.$stg_arr[$kz].'</option>';
}
echo '</SELECT>';

echo ' Studiensemester: <SELECT name="stsem">';
$stsem_obj = new studiensemester();
$stsem_obj->getAll();

foreach ($stsem_obj->studiensemester as $row)
{
	echo '<option '.($stsem==$row->studiensemester_kurzbz?' selected="selected" ':'').'  value="'.$row->studiensemester_kurzbz.'" >'.$row->studiensemester_kurzbz.'</option>';
}
echo '</SELECT>';

echo '<input type="submit" value="Anzeigen">';
echo '</form>';
echo '<br><br>';

$projekt = new projektarbeit();
$projekt->getProjektarbeitStudiensemester($stg_kz, $stsem);

echo '<form action="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&stsem='.$stsem.'" method="POST" />';

echo '<table id="t1">
	<thead>
		<tr>
			<th>Student</th>
			<th>Typ</th>
			<th>Titel</th>
			<th>Themenbereich</th>
			<th>Betreuer</th>
			<th>Beginn</th>
			<th>Ende</th>
			<th>Gesamtnote</th>
		</tr>
	</thead>
	<tbody>';

foreach ($projekt->result as $row)
{
	echo '<tr>';

	$student = new student();
	$student->load($row->student_uid);
	echo "<td nowrap>$student->nachname $student->vorname $student->titelpre $student->titelpost</td>";
	echo "<td>$row->bezeichnung</td>";
	echo "<td>$row->titel".($row->titel_english!=''?'<br>'.$row->titel_english:'')."</td>";
	echo "<td>$row->themenbereich</td>";

	echo '<td nowrap>';
	$qry = "SELECT
				distinct vorname, nachname, titelpre, titelpost,
	 			(SELECT uid FROM public.tbl_benutzer JOIN public.tbl_mitarbeiter on(uid=mitarbeiter_uid)
				WHERE person_id=tbl_person.person_id LIMIT 1) as uid, betreuerart_kurzbz
			FROM
				public.tbl_person
				JOIN lehre.tbl_projektbetreuer USING(person_id)
			WHERE projektarbeit_id=".$db->db_add_param($row->projektarbeit_id);

	if($result_betreuer = $db->db_query($qry))
	{
		while($row_betreuer = $db->db_fetch_object($result_betreuer))
		{
			if($row_betreuer->uid!='')
				echo "<a href='mailto:$row_betreuer->uid@".DOMAIN."' class='Item'>";

			echo trim($row_betreuer->titelpre.' '.$row_betreuer->vorname.' '.
				$row_betreuer->nachname.' '.$row_betreuer->titelpost).' ('.$row_betreuer->betreuerart_kurzbz.')';

			if($row_betreuer->uid!='')
				echo '</a>';

			echo '<br>';
		}
	}

	echo "</td>";

	echo "<td>$row->beginn</td>";
	echo "<td>$row->ende</td>";
	echo "<td>";
	echo "<SELECT name='note_$row->projektarbeit_id'>";
	echo "<OPTION value=''></OPTION>";
	foreach ($noten->result as $note)
	{
		if($row->note==$note->note)
			$selected='selected';
		else
			$selected='';

		echo "<OPTION value='$note->note' $selected>$note->bezeichnung</OPTION>";
	}
	echo "</SELECT>";
	echo "</td>";

	echo "</tr>";
}

echo '</tbody></table>';
echo '<br><br><div align="right"><input type="submit" value="Speichern" name="savedata"/></div>';
echo '</form>';

?>

<br>
</body>
</html>