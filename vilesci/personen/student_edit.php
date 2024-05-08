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

 
 
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema sowie Verwendung der
 *                      'student'-Klasse; Datei ersetzt student_edit_save.php
 *                      (WM)
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzerberechtigung.class.php');

echo '
<html>
<head>
<title>Student Edit</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
';

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('student/stammdaten',null, 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

echo '<h2>Student ';
if (isset($_GET['new']))
	echo 'Neu</h2>';
else
	echo 'Edit</h2>';

if (isset($_POST['Save']))
{
	doSAVE();
}
else if (isset($_GET['new']))
{
	doEDIT(null,true);

}
else
{
	if (!isset ($_GET['id']))
	{
		echo "benötige UID für Student";
	}
	doEDIT($_GET['id']);
}

/**
 * Daten speichern
 */
function doSAVE()
{
	$student = new student();
	if($_POST['new'])
	{
		$student->new=true;
		$student->insertamum=date('Y-m-d H:i:s');
		$student->insertvon=$user;
	}
	else 
	{
		$student->load($_POST['uid']);
		$student->new=false;
	}
	// person
	$student->uid=$_POST['uid'];
	if (isset($_POST['new_uid']))
		$student->uid=$_POST['new_uid'];
	$student->titelpre=$_POST['titelpre'];
	$student->vorname=$_POST['vorname'];
	$student->nachname=$_POST['nachname'];
	$student->gebdatum=$_POST['gebdatum'];
	$student->gebort=$_POST['gebort'];
	//$student->gebzeit=$_POST['gebzeit'];
	//$student->anmerkungen=$_POST['anmerkungen'];
	$student->aktiv=($_POST['aktiv']=='1'?true:false);
	$student->alias=$_POST['alias'];
	$student->homepage=$_POST['homepage'];
	//echo "<br><h2>aktiv=".($student->aktiv?'true':'false').'</h2>';
	// student
	if (is_numeric($_POST['studiengang_kz']))
	{
		$student->studiengang_kz=$_POST['studiengang_kz'];
	}
	else
	{
		echo "<p>Studiengang ist keine Zahl (".$_POST['studiengang_kz'].").</p>";
		return;
	}
	$student->matrikelnr=$_POST['matrikelnr'];
	if (is_numeric($_POST['semester']))
	{
		$student->semester=$_POST['semester'];
	}
	else
	{
		echo "<p>Semester ist keine Zahl";
		return;
	}
	$student->verband=$_POST['verband'];
	$student->gruppe=$_POST['gruppe'];

	if ($student->save())
	{
		echo "<h3>Datensatz gespeichert.</h3>";
	}
	else
	{
		echo "<p>".$student->errormsg."</p>";
	}

	doEDIT($student->uid);
}

/**
 * Edit-Formular
 */
function doEDIT($id,$new=false)
{

	// Studentendaten holen
	$student = new student();
	$status_ok=false;
	if (!$new)
	{
		$status_ok=$student->load($id);
	}
	if (!$status_ok && !$new)
	{
		// Laden fehlgeschlagen
		echo $student->errormsg;
	}
	else
	{
		// Eingabeformular anzeigen
		echo '<table><tr><td>';
		
		echo '
		<form name="std_edit" action="'.$_SERVER['REQUEST_URI'].'" method="POST">
		<input type="hidden" name="new" value="'.$new.'">
			<table>
			<tr>
			      <td>UID*</td>
			      <td>	<input type="text" name="new_uid" value="'.$student->uid.'">
			      		<input type="hidden" name="uid" value="'.$student->uid.'" >
			      </td>
			</tr>
			<tr><td>Titel</td><td><input type="text" name="titelpre" value="'.$student->titelpre.'"></td></tr>
			<tr><td>Vornamen</td><td><input type="text" name="vorname" value="'.$student->vorname.'"></td></tr>
			<tr><td>Nachname</td><td><input type="text" name="nachname" value="'.$student->nachname.'"></td></tr>
			<tr><td>Aktiv</td><td><input type="checkbox" name="aktiv" value="1" '.($student->aktiv?'checked':'').'></td></tr>
			<tr><td>Geburtsdatum</td><td><input type="text" name="gebdatum" value="'.$student->gebdatum.'"> (TT.MM.JJJJ)</td></tr>
			<tr><td>Gebort</td><td><input type="text" name="gebort" value="'.$student->gebort.'"></td></tr>
			<tr><td>eMail Alias</td><td><input type="text" name="alias" value="'.$student->alias.'"></td></tr>
	
			<tr><td>Homepage</td><td><input type="text" name="homepage" value="'.$student->homepage.'"></td></tr>
			<tr>
			      <td>Matrikelnr*</td>
			      <td><input type="text" name="matrikelnr" value="'.$student->matrikelnr.'"></td></tr>
			<tr><td>Studiengang</td><td>
			<SELECT name="studiengang_kz">
      			<option value="-1">- auswählen -</option>';

		// Auswahl des Studiengangs
		$stg=new studiengang();
		$stg->getAll();
		foreach($stg->result as $studiengang)
		{
			echo "<option value=\"$studiengang->studiengang_kz\" ";
			if ($studiengang->studiengang_kz==$student->studiengang_kz)
				echo "selected";
			echo " >$studiengang->kuerzel ($studiengang->bezeichnung)</option>\n";
		}
		
		echo '
		    </SELECT>

			</td></tr>
			<tr><td>Semester</td><td><input type="text" name="semester" value="'.$student->semester.'"></td></tr>
			<tr><td>Verband</td><td><input type="text" name="verband" value="'.$student->verband.'"></td></tr>
			<tr><td>Gruppe</td><td><input type="text" name="gruppe" value="'.$student->gruppe.'"></td></tr>

			</table>

			<input type="submit" name="Save" value="Speichern">
			<input type="hidden" name="id" value="'.$id.'">
			</form>';
			
		echo '</td><td valign="top">';
		echo '<a href="../../content/pdfExport.php?xsl=AccountInfo&xml=accountinfoblatt.xml.php&uid='.$student->uid.'" >AccountInfoBlatt erstellen</a>';
			
		echo '</td></tr></table>';

	}

} // ENDE doEDIT()

?>

</body>
</html>
