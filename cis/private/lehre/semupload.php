<?php
// ***************************************************
// *@author Andras Oesterreicher
// *@brief Formular zum Uploaden und Loeschen von
// *       Semesterplaenen.
// *@date 31.Aug. 2005
// *@edit 19.Dez. 2006 Anpassung an neue DB
// ***************************************************/
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
    require_once('../../../include/studiengang.class.php');
    require_once('../../../include/lehrveranstaltung.class.php');

    //Connection Herstellen
    if(!$conn = pg_pconnect(CONN_STRING))
    {
       die('Fehler beim oeffnen der Datenbankverbindung');
    }

	$user = get_uid();

	if(check_lektor($user,$conn))
       $is_lector=true;

	 if(!isset($_GET['lvid']) || !is_numeric($_GET['lvid']))
	 {
    	die("Fehler bei der Parameteruebergabe");
	 }
	 else
	 	$lvid = $_GET['lvid'];

    $lv_obj = new lehrveranstaltung($conn);
    if(!$lv_obj->load($lvid))
    	die('Fehler beim Laden der Lehrveranstaltung');
    $stg_obj = new studiengang($conn);

    if(!$stg_obj->load($lv_obj->studiengang_kz))
    	die('Fehler beim Laden des Studienganges');

    $openpath = '../../../documents/'.strtolower($stg_obj->kuerzel).'/'.$lv_obj->semester.'/'.strtolower($lv_obj->lehreverzeichnis).'/semesterplan/';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">

/****************************************************
 * @brief Zeigt eine Sicherheitsabfrage ob die Datei
 * 		  wirklich gelöscht werden soll
 ****************************************************/
function ConfirmFile(handle)
{
	return confirm('Wollen Sie die ausgewählten Dateien wirklich löschen? Dieser Vorgang ist unwiderruflich!');
}

</script>
</head>
<title>Upload Semesterplan</title>
<body id="inhalt">
<table class="tabcontent" >
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">Upload Semesterplan</font></td>

	</tr>
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="tdwidth10">&nbsp;</td>
	</tr>

<?php


	if(!$is_lector)
		die('<tr><td class="tdwidth10">&nbsp;</td><td>Sie haben keine Berechtigung für diesen Bereich</td></tr>');

	echo "<tr><td class='tdwidth10'>&nbsp;</td><td>";
	if(isset($_POST['inhalt']))
	{
		if($inhalt!="____Ordnerinhalt____")
		{
			if(!mb_strstr($inhalt,'..'))
			{
				if(is_file($openpath . $inhalt))
				{
					writeCISlog('DELETE', "rm -r '$openpath$inhalt'");
					exec("rm -r '$openpath$inhalt'");
					echo "<center>Datei erfolgreich gelöscht</center>";
				}
				else
				{
				   echo "<center>Die Datei $openpath$inhalt konnte nicht gefunden werden.</center>";
				}
			}
			else
			{
				writeCISlog('REPORT', 'versuchter Loeschvorgang von '.$openpath.$inhalt);
				echo "<center>Fehlerhafte Parameter</center>";
			}
		}
		else
		{
			echo "<center>Bitte zuerst eine Datei auswählen</center>";
		}
	}

	if(isset($userfile))
	{
		if(is_uploaded_file($userfile))
		{
			$fn = $_FILES['userfile']['name']; //Original Dateiname

			if(!stristr($fn, '.php') && !stristr($fn, '.php3') &&
			   !stristr($fn,'.php4') && !stristr($fn, '.php5') &&
			   !stristr($fn, '.cgi') && !stristr($fn, '.pl'))
			{
				if(move_uploaded_file($userfile,$openpath . $fn))
					echo "<center>Das File wurde erfolgreich hochgeladen</center>";
				else
					echo "<center>Fehler beim Upload! Bitte Versuchen Sie es erneut</center>";
			}
			else
			{
				echo "<center>Dieser Dateityp ist nicht erlaubt <center>";
			}
		}
		else
			echo "<center>Fehler beim Upload! Bitte Versuchen Sie es erneut</center>";
	}

	echo "</tr></td>";

	echo "<tr><td class='tdwidth10'>&nbsp;</td><td><form accept-charset="UTF-8" name=\"form1\"  method=\"POST\" action=\"semupload.php?lvid=".$lvid."\"  onSubmit=\"return ConfirmFile(this);\">";
	echo "<select name=\"inhalt\" size=5>";
	echo "<option selected>____Ordnerinhalt____</option>";
	//Inhalt des Semesterplan Ordners Auslesen
	if(is_dir($openpath))
	{
  		$dest_dir = dir($openpath);
		while($entry = $dest_dir->read())
		{
			if(!is_dir($entry))
				echo "<option>$entry</option>";
		}
	}
	echo "</select>";
	echo "<input type=\"submit\" value=\"Datei Löschen\">";
	echo "</form></td><td>";

    //FileAuswahlfeld
	echo '<tr><td class="tdwidth10">&nbsp;</td><td><br><form accept-charset="UTF-8" enctype="multipart/form-data" method="POST" action="semupload.php?lvid='.$lvid.'">';
	echo ' <input type="file" name="userfile" size="30">';
	echo ' <input type="submit" name="upload" value="Upload">';
	echo '</form></td><td>';
?>
</body>
</html>