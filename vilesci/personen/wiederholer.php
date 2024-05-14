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
/**
 * Dieses Script ist fuer Personen, die das Studium abgebrochen haben und von neuem starten.
 * Dazu muessen Studenten ein neues Personenkennzeichen etc erhalten da es sonst zu Problemen bei
 * der BIS-Meldung kommt.
 *
 * Mit diesem Script kann der Student gesucht werden und ein neuer Prestudent Eintrag angelegt werden.
 * Zusätzlich wird ein Interessentenstatus im ausgewaehlten Semester angelegt.
 *
 * Die Prestudentdaten (ZGV etc) werden fuer den neuen Eintrag uebernommen.
 *
 * Dieses Script ist NICHT fuer Studenten die nur ein Semester/Jahr wiederholen. Es ist nur fuer Abbrecher die
 * erneut in diesem Studiengang studieren moechten.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/student.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/prestudent.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('basis/person',null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$filter = isset($_POST['filter'])?$_POST['filter']:'';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<title>Studiengangswiederholer anlegen</title>
</head>
<body>

<H2>Studiengangswiederholer anlegen</H2>

<?php
echo '<form name="suche" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
echo '<input name="filter" type="text" value="'.$filter.'" size="64" maxlength="64">';
echo '<input type="submit" value="suchen">';
echo '</form><br /><br />';

//Anlegen des neuen Prestudenten
if(isset($_POST['save']))
{
	if(!isset($_POST['prestudent_id']))
		die('PrestudentID muss uebergeben werden');
	if(!isset($_POST['stsem_kurzbz']))
		die('Studiensemester muss uebergeben werden');
	if(!isset($_POST['ausbildungssemester']))
		die('Ausbildungssemester muss uebergeben werden');

	$prestudent_id=$_POST['prestudent_id'];
	$ausbildungssemester=$_POST['ausbildungssemester'];
	$stsem_kurzbz=$_POST['stsem_kurzbz'];

	if(!is_numeric($prestudent_id))
		die('PrestudentID ist ungueltig');

	$prestd_obj = new prestudent();
	if(!$prestd_obj->load($prestudent_id))
		die('PrestudentID ist ungueltig');

	$prestd_obj->new = true;
	if($prestd_obj->save())
	{
		$prestudent_id_neu=$prestd_obj->prestudent_id;

		if($prestd_obj->getLastStatus($prestudent_id))
			$orgform_kurzbz = $prestd_obj->orgform_kurzbz;
		else
			$orgform_kurzbz = null;

		$prestd_obj = new prestudent();

		$prestd_obj->prestudent_id=$prestudent_id_neu;
		$prestd_obj->status_kurzbz='Interessent';
		$prestd_obj->studiensemester_kurzbz = $stsem_kurzbz;
		$prestd_obj->ausbildungssemester = $ausbildungssemester;
		$prestd_obj->datum = date('Y-m-d');
		$prestd_obj->insertamum = date('Y-m-d H:i:s');
		$prestd_obj->insertvon = $user;
		$prestd_obj->updateamum = date('Y-m-d H:i:s');
		$prestd_obj->updatevon = $user;
		$prestd_obj->orgform_kurzbz = $orgform_kurzbz;
		$prestd_obj->new = true;;

		if(!$prestd_obj->save_rolle())
		{
			echo 'Fehler beim Speichern der Rolle:'.$prestd_obj->errormsg;
		}
		else
		{
			echo 'Prestudent wurde mit der ID '.$prestudent_id_neu.' neu angelegt';
		}
	}
}

if(isset($_POST['filter']))
{
	$filter = $_POST['filter'];
	$stg_obj = new studiengang();
	$stg_obj->getAll('typ, kurzbz',false);
	$std_obj = new student();
	$stsem_obj = new studiensemester();
	$stsem = $stsem_obj->getaktorNext();
	$stsem_obj->getAll();
	if($std_obj->getTab($filter, 'nachname, vorname'))
	{
		if(is_array($std_obj->result) && count($std_obj->result)>0)
		{
			echo '<table class="liste">';
			echo '<tr>';
			echo '<th>PersonID</td>';
			echo '<th>Vorname</td>';
			echo '<th>Nachname</td>';
			echo '<th>Studiengang</td>';
			echo '<th>Semester</td>';
			echo '<th>Action</td>';
			echo '</tr>';
			$i=0;
			foreach($std_obj->result as $row)
			{
				$i++;
				echo '<tr class="liste'.($i%2).'">';
				echo '<td>'.$row->person_id.'</td>';
				echo '<td>'.$row->vorname.'</td>';
				echo '<td>'.$row->nachname.'</td>';
				echo '<td>'.$stg_obj->kuerzel_arr[$row->studiengang_kz].'</td>';
				echo '<td>'.$row->semester.'</td>';
				echo '<td>';
				echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
				echo '<input type="hidden" name="prestudent_id" value="'.$row->prestudent_id.'" />';
				echo 'als neuen Interessenten in Studiensemester ';
				echo '<SELECT name="stsem_kurzbz">';
				foreach($stsem_obj->studiensemester as $row_stsem)
				{
					if($row_stsem->studiensemester_kurzbz==$stsem)
						$selected='selected';
					else
						$selected='';
					echo '<OPTION value="'.$row_stsem->studiensemester_kurzbz.'" '.$selected.'>'.$row_stsem->studiensemester_kurzbz.'</OPTION>';
				}
				echo '</SELECT>';
				echo ' in Ausbildungssemester <SELECT name="ausbildungssemester">';
				for($sem=1;$sem<=10;$sem++)
				{
					echo '<OPTION value="'.$sem.'">'.$sem.'</OPTION>';
				}
				echo '</SELECT>';
				echo '<input type="submit" name="save" value="anlegen">';
				echo '</form>';
				echo '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
		else
			echo 'Keine Eintraege gefunden';
	}
	else
	{
		echo 'Fehler beim Laden der Daten:'.$std_obj->errormsg;
	}
}
?>
<br /><font color="gray">Studienabbrecher, welche den selben Studiengang erneut besuchen wollen können hier angelegt werden. <br />Diese Seite ist NICHT für das Wiederholen eines einzelnen Semesters/Jahres gedacht!</font>
</body>
</html>
