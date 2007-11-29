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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
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

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$user = get_uid();

$stg_kz = (isset($_REQUEST['stg_kz'])?$_REQUEST['stg_kz']:'');
$stsem = (isset($_REQUEST['stsem'])?$_REQUEST['stsem']:'');

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_kz, 'suid'))
	die('Sie haben keine Berechtigung f�r diesen Studiengang');

if($stsem=='')
{
	$stsem_obj = new studiensemester($conn);
	$stsem = $stsem_obj->getaktorNext();
}

if($stg_kz=='')
	$stg_kz='0';

echo '<html>
	<head>
	<title>Lehrveranstaltung Verwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	<script language="Javascript">

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
		if(strstr($key, 'note_'))
		{
			$id = substr($key, 5);
			$prj = new projektarbeit($conn);
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

$noten = new note($conn);
$noten->getAll();

$stg_arr = array();
$stg_obj = new studiengang($conn);
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
	$stgs = $rechte->getStgKz();

foreach ($stgs as $kz) 
{
	if($stg_kz == $kz)
		$selected = 'selected';
	else 
		$selected = '';
	echo '<option value="'.$kz.'" '.$selected.'>'.$stg_arr[$kz].'</option>';
}
echo '</SELECT>';

echo ' Studiensemester: <SELECT name="stsem">';
$stsem_obj = new studiensemester($conn);
$stsem_obj->getAll();

foreach ($stsem_obj->studiensemester as $row)
{
	if($stsem == $row->studiensemester_kurzbz)
		$selected = 'selected';
	else 
		$selected = '';
		
	echo '<option value="'.$row->studiensemester_kurzbz.'" '.$selected.'">'.$row->studiensemester_kurzbz.'</option>';
}
echo '</SELECT>';

echo '<input type="submit" value="Anzeigen">';
echo '</form>';
echo '<br><br>';

$projekt = new projektarbeit($conn);
$projekt->getProjektarbeitStudiensemester($stg_kz, $stsem);

echo '<form action="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'&stsem='.$stsem.'" method="POST" />';

echo "<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'><thead><tr class='liste'>";
echo "<th class='table-sortable:default'>Student</th>
	  <th class='table-sortable:default'>Typ</th>
	  <th class='table-sortable:default'>Titel</th>
	  <th class='table-sortable:default'>Betreuer</th>
	  <th class='table-sortable:default'>Beginn</th>
	  <th class='table-sortable:default'>Ende</th>
	  <th class='table-sortable:default'>Gesamtnote</th>";
echo "</tr></thead>";
echo "<tbody>";

foreach ($projekt->result as $row)
{
	echo '<tr>';
	
	$student = new student($conn);
	$student->load($row->student_uid);
	echo "<td nowrap>$student->nachname $student->vorname $student->titelpre $student->titelpost</td>";
	echo "<td>$row->projekttyp_kurzbz</td>";
	echo "<td>$row->titel</td>";
	
	echo '<td nowrap>';
	$qry = "SELECT distinct vorname, nachname, titelpre, titelpost FROM public.tbl_person JOIN lehre.tbl_projektbetreuer USING(person_id) WHERE projektarbeit_id='".$row->projektarbeit_id."'";
	if($result_betreuer = pg_query($conn, $qry))
	{
		while($row_betreuer = pg_fetch_object($result_betreuer))
		{
			echo trim($row_betreuer->titelpre.' '.$row_betreuer->vorname.' '.$row_betreuer->nachname.' '.$row_betreuer->titelpost);
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