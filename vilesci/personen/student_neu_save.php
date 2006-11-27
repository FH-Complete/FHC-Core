<?php
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	include('../../include/person.class.php');
	include('../../include/student.class.php');
	include ('../../include/studiengang.class.php');
	if (!$conn = @pg_pconnect($conn_string))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if ($save=='ok')
	{
		$sql_query="INSERT INTO student (uid, titel, vornamen, nachname, gebdatum, gebort, ";
		$sql_query.="emailtw, emailforw, emailalias, matrikelnr";
		$sql_query.=", studiengang_id, semester, verband, gruppe) VALUES ('$uid', '$titel', '$vornamen'";
		$sql_query.=", '$nachname', '$gebdatum', '$gebort'";
		$sql_query.=", '$emailtw', '$emailforw', '$emailalias'";
		$sql_query.=", '$matrikelnr', '$studiengang_id', '$sem', '$ver', '$grp')";

		$student=new student($conn);


		if(!($erg=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		else
			echo 'Daten wurden erfolgreich gespeichert!<hr>';
	}
	else
   	{
		$uid='?';
		$titel='?';
		$anrede='Herr';
		$vornamen='?';
		$nachname='?';
		$gebdatum='24.08.1980';
		$gebort='?';
		$adresse='?';
		$plz='?';
		$ort='?';
		$teltw='?';
		$telpriv='?';
		$telfirma='?';
		$telmobil='?';
		$emailtw='?';
		$emailpriv='?';
		$emailfirm='?';
		$homedir='/home01/?';
		$emailalias='?';
		$matrikelnr='?';
		$studiengang_id='1';
		$sem='1';
		$ver='A';
		$grp='1';
	}



?>

<html>
<head>
<title>Student Neu</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h4>Student Neu</h4>
<form name="std_edit" action="student_neu_save.php">
<table border="<?php echo $cfgBorder;?>" bgcolor="<?php echo $cfgThBgcolor; ?>">
<tr><td>UID</td><td><input type="text" name="uid" value="<?php echo $uid; ?>"><font size="-1">Account Name (Bei Studenten ist das der erste Teil der Mail-Adresse)</font></td></tr>
<tr><td>Titel</td><td><input type="text" name="titel" value="<?php echo $titel; ?>"></td></tr>
<tr><td>Vornamen</td><td><input type="text" name="vornamen" value="<?php echo $vornamen; ?>"></td></tr>
<tr><td>Nachname</td><td><input type="text" name="nachname" value="<?php echo $nachname; ?>"></td></tr>
<tr><td>Geburtsdatum</td><td><input type="text" name="gebdatum" value="<?php echo $gebdatum; ?>"><font size="-1">Format: TT.MM.JJJJ</font></td></tr>
<tr><td>Geburtsort</td><td><input type="text" name="gebort" value="<?php echo $gebort; ?>"></td></tr>
<tr><td>eMail Technikum</td><td><input type="text" name="email" value="<?php echo $emailtw; ?>"></td></tr>
<tr><td>eMail Alias</td><td><input type="text" name="alias" value="<?php echo $emailalias; ?>"></td></tr>
<tr><td>Homepage</td><td><input type="text" name="homepage" value=""></td></tr>
<tr><td>matrikelnr</td><td><input type="text" name="matrikelnr" value="<?php echo $matrikelnr; ?>"></td></tr>


<tr><td>Studiengang</td><td>
<SELECT name="studiengang_kz">
      <option value="-1">- auswählen -</option>
      <?php
		// Auswahl des Studiengangs
		$stg=new studiengang($conn);
		$stg_alle=$stg->getAll();
		foreach($stg_alle as $studiengang)
		{
			echo "<option value=\"$studiengang->studiengang_kz\" ";
			echo ">$studiengang->kurzbz ($studiengang->bezeichnung)</option>";
		}
		?>
    </SELECT>
</td></tr>

<tr><td>Semester</td><td><input type="text" name="sem" value="<?php echo $sem; ?>"></td></tr>
<tr><td>Verband</td><td><input type="text" name="ver" value="<?php echo $ver; ?>"><font size="-1">A B oder C</font></td></tr>
<tr><td>Gruppe</td><td nowrap><input type="text" name="grp" value="<?php echo $grp; ?>"><font size="-1">Werden keine Aufteilungen in Gruppen verwendet (zb:EW) einfach 0 eingeben</font></td></tr>


</table>
  <input type="submit" name="Save" value="Speichern">
  <input type="hidden" name="save" value="ok">
</form>
</body>
</html>
