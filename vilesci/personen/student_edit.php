<?php
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema sowie Verwendung der
 *                      'student'-Klasse; Datei ersetzt student_edit_save.php
 *                      (WM)
 */
require_once('../config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/studiengang.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
	die("Fehler beim Connecten zur Datenbank");
?>
<html>
<head>
<title>Student Edit</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<?php
$user = get_uid();

echo '<h4>Student ';
if (isset($_GET['new']))
	echo 'Neu</h4>';
else
	echo 'Edit</h4>';

if (isset($_POST['Save']))
{
	doSAVE($conn);
}
else if (isset($_GET['new']))
{
	doEDIT($conn,null,true);

}
else
{
	if (!isset ($_GET['id']))
	{
		echo "benötige UID für Student";
	}
	doEDIT($conn,$_GET['id']);
}

/**
 * Daten speichern
 */
function doSAVE($conn)
{
	$student = new student($conn);
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
		echo "<p>Studiengang-KZ ist keine Zahl (".$_POST['studiengang_kz'].").</p>";
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
		echo "<h2>Datensatz gespeichert.</h2>";
	}
	else
	{
		echo "<p>".$student->errormsg."</p>";
	}

	doEDIT($conn,$student->uid);
}

/**
 * Edit-Formular
 */
function doEDIT($conn,$id,$new=false)
{

	// Studentendaten holen
	$student = new student($conn);
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
		?>
		<form name="std_edit" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="POST">
		<input type="hidden" name="new" value="<?php echo $new; ?>">
			<table>
			<tr>
			      <td>UID*</td>
			      <td>	<input type="text" name="new_uid" value="<?php echo $student->uid; ?>">
			      		<input type="hidden" name="uid" value="<?php echo $student->uid ?>" >
			      </td>
			</tr>
			<tr><td>Titel</td><td><input type="text" name="titelpre" value="<?php   echo $student->titelpre;
		?>"></td></tr>
			<tr><td>Vornamen</td><td><input type="text" name="vorname" value="<?php   echo $student->vorname;
		?>"></td></tr>
			<tr><td>Nachname</td><td><input type="text" name="nachname" value="<?php   echo $student->nachname;
		?>"></td></tr>
			<tr><td>Aktiv</td><td><input type="checkbox" name="aktiv" value="1" <?php   echo $student->aktiv?'checked':'';
		?>></td></tr>
			<tr><td>Geburtsdatum</td><td><input type="text" name="gebdatum" value="<?php   echo $student->gebdatum;
		?>"> (TT.MM.JJJJ)</td></tr>
			<tr><td>Gebort</td><td><input type="text" name="gebort" value="<?php   echo $student->gebort;
		?>"></td></tr>
			<tr><td>eMail Alias</td><td><input type="text" name="alias" value="<?php   echo $student->alias;
		?>"></td></tr>
	
			<tr><td>Homepage</td><td><input type="text" name="homepage" value="<?php echo $student->homepage;	?>"></td></tr>
			<tr>
			      <td>Matrikelnr*</td>
			      <td><input type="text" name="matrikelnr" value="<?php   echo $student->matrikelnr;
		?>"></td></tr>
			<tr><td>Studiengang-KZ</td><td>
			<SELECT name="studiengang_kz">
      			<option value="-1">- auswählen -</option>
<?php
			// Auswahl des Studiengangs
			$stg=new studiengang($conn);
			$stg->getAll();
			foreach($stg->result as $studiengang)
			{
				echo "<option value=\"$studiengang->studiengang_kz\" ";
				if ($studiengang->studiengang_kz==$student->studiengang_kz)
					echo "selected";
				echo " >$studiengang->kuerzel ($studiengang->bezeichnung)</option>\n";
			}
?>
		    </SELECT>

			</td></tr>
			<tr><td>Semester</td><td><input type="text" name="semester" value="<?php   echo $student->semester;
		?>"></td></tr>
			<tr><td>Verband</td><td><input type="text" name="verband" value="<?php   echo $student->verband;
		?>"></td></tr>
			<tr><td>Gruppe</td><td><input type="text" name="gruppe" value="<?php   echo $student->gruppe;
		?>"></td></tr>


			</table>

			<input type="submit" name="Save" value="Speichern">
			<input type="hidden" name="id" value="<?php   echo $id;
		?>">
			</form>

			<?php

				}

} // ENDE doEDIT()

?>

</body>
</html>