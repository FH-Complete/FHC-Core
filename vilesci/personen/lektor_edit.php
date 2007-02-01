<?php
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/person.class.php');
	require_once('../../include/benutzer.class.php');
	require_once('../../include/mitarbeiter.class.php');
?>
<html>
<head>
<title>Lektor Edit</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body class="background_main">
<?php
if(!$conn = pg_pconnect(CONN_STRING))
       die ('Fehler beim Herstellen der DB Connection');

	if (isset($_POST['Save']))
	{
		doSAVE($conn);
		echo "<script language='Javascript'>window.location.href='lektor_uebersicht.php';</script>";
	}
	else if (isset($_GET['new']))
	{
		doEDIT($conn,null,true);
	
	}
	else
	{
		if (!isset ($_GET['id']))
		{
			echo "benötige ID für Lektor";
		}
		doEDIT($conn,$_GET['id']);
	}
	
/**
 * Lektor speichern/anlegen
 */
function doSAVE($conn)
{
	$lektor = new mitarbeiter($conn);
	if ($_POST['new']==1)
	{
		$lektor->new=true;
	} 
	else
	{
		$lektor->load($_POST['uid']);
		$lektor->new=false;
	}
	// person
	$lektor->uid=$_POST['uid'];
	$lektor->titel=$_POST['titel'];
	$lektor->vornamen=$_POST['vornamen'];
	$lektor->nachname=$_POST['nachname'];
	$lektor->gebdatum=$_POST['gebdatum'];
	$lektor->gebort=$_POST['gebort'];
	$lektor->gebzeit=$_POST['gebzeit'];
	$lektor->anmerkungen=$_POST['anmerkungen'];
	$lektor->aktiv=($_POST['aktiv']=='1'?true:false);
	$lektor->email=$_POST['email'];
	$lektor->alias=$_POST['alias'];
	$lektor->kurzbz=$_POST['kurzbz'];
	$lektor->homepage=$_POST['homepage'];
	// mitarbeiter
	$lektor->personalnummer=$_POST['personalnummer'];
	$lektor->lektor=($_POST['lektor']=='1'?true:false);
	$lektor->fixangestellt=($_POST['fixangestellt']=='t'?true:false);
	$lektor->telefonklappe=$_POST['telefonklappe'];
	$lektor->ort_kurzbz=$_POST['raumnr'];
	//print_r($_POST);


	if ($lektor->save())
	{
		$msg="<p>Datensatz gespeichert.</p>";
	} else
	{
		$msg="<p>".$lektor->errormsg."</p>";
	}

	doEDIT($lektor->uid,false,$msg);
}



/**
 * MA bearbeiten/anlegen
 * @param string $id optional; wenn nicht angegeben -> neuer datensatz
 */
function doEDIT($conn,$id='',$new=false,$msg='')
{
	// Mitarbeiterdaten holen
	$lektor = new mitarbeiter($conn);
	$status_ok=false;
	if (!$new)
	{
		$status_ok=$lektor->load(addslashes($id));
	}
	
	if (!$status_ok && !$new)
	{
		// Laden fehlgeschlagen
		echo $lektor->errormsg;
	} 
	else
	{
?>

<h2>Lektor/Mitarbeiter <?php echo $new?'Neu':'Edit' ?></h2>
<?php
if (strlen($msg)>0) echo $msg."<br/>";
?>
<form name="std_edit" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<table border="0">
<tr><td>UID</td><td><input type="text" name="uid" value="<?php echo $lektor->uid; ?>"></td></tr>
<tr><td>Personalnummer</td><td><input type="text" name="personalnummer" value="<?php echo $lektor->personalnummer; ?>"></td></tr>
<tr><td>Titel</td><td><input type="text" name="titel" value="<?php echo $lektor->titelpre; ?>"></td></tr>
<tr><td>Vornamen</td><td><input type="text" name="vornamen" value="<?php echo $lektor->vorname; ?>"></td></tr>
<tr><td>Nachname</td><td><input type="text" name="nachname" value="<?php echo $lektor->nachname; ?>"></td></tr>
<tr><td>Lektor</td><td><input type="checkbox" name="lektor" value="1" <?php   echo ($lektor->lektor?'checked':'') ?> ></td></tr>
<tr><td>Aktiv</td><td><input type="checkbox" name="aktiv" value="1" <?php   echo ($lektor->aktiv?'checked':'') ?> ></td></tr>
<tr><td>Geburtsdatum</td><td><input type="text" name="gebdatum" value="<?php echo $lektor->gebdatum; ?>"> (TT.MM.JJJJ)</td></tr>
<tr><td>Geburtsort</td><td><input type="text" name="gebort" value="<?php echo $lektor->gebort; ?>"></td></tr>
<tr><td>eMail Alias</td><td><input type="text" name="alias" value="<?php echo $lektor->alias; ?>"></td></tr>
<tr><td>Homepage</td><td><input type="text" name="homepage" value="<?php echo $lektor->homepage; ?>"></td></tr>
<tr><td>Kurzbezeichnung</td><td><input type="text" name="kurzbz" value="<?php echo $lektor->kurzbz; ?>"></td></tr>
<tr><td>Telefon Technikum</td><td><input type="text" name="telefonklappe" value="<?php echo $lektor->telefonklappe; ?>"></td></tr>
<tr><td>Fix angestellt</td><td><SELECT name="fixangestellt">
	<OPTION value="t" <?php if($lektor->fixangestellt) echo 'selected'; ?>>Ja</OPTION>
    <OPTION value="f" <?php if(!$lektor->fixangestellt) echo 'selected'; ?>>Nein</OPTION>
    </SELECT></td></tr>
<tr><td>Raum Nr:</td><td>
<SELECT name="raumnr">
<OPTION value="0" selected>--Kein Raum--</OPTION>
<?php
	$qry = "SELECT ort_kurzbz FROM public.tbl_ort WHERE aktiv=true ORDER BY ort_kurzbz";
	if($result=pg_query($conn,$qry))
	{
		while($row=pg_fetch_object($result))
			echo "<OPTION value='$row->ort_kurzbz' ". ($lektor->ort_kurzbz===$row->ort_kurzbz?'selected':'').">$row->ort_kurzbz</OPTION>";
	}
?>
</SELECT>
</td></tr>
</table>
  <input type="submit" name="Save" value="Speichern">
  <input type="hidden" name="id" value="<?php echo $lektor->uid; ?>">
  <input type="hidden" name="new" value="<?php echo $new?'1':'0' ?>">
</form>
<?php

	}
}

?>
</body>
</html>