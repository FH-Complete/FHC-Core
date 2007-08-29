<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../../config.inc.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;
$i=0;
$qry1='';
$zweitbetreuer='';
$combobox=array();
$valuebox=array();
$nachname=array();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Berufspraktikum-Datenkorrektur</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-9">
<link href="../../../skin/vilesci.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
if(isset($_POST['anlegen']))
{
	$qry="INSERT INTO public.tbl_person (geschlecht,titelpre,vorname,nachname,updatevon) VALUES
		('".$_POST['geschlecht']."','".$_POST['titel']."','".$_POST['vorname']."','".$_POST['nachname']."','Administrator');";
	if($result = pg_query($conn, $qry))
		echo 'Person '.$_POST['nachname'].' wurde in VileSci angelegt!<BR>';
}

if(isset($_POST['da']))
{
	if(isset($_POST['top1']) AND trim($_POST['top1'])!='')
	{
		$qry1= "UPDATE berufspraktikum SET vilesci_firmenbetreuer='".$_POST['top1']."' WHERE berufspraktikum_pk='".$_POST['da']."';";
	}
	if(trim($qry1)!='')
	{
		pg_query($conn_fas, $qry1);
		echo $qry1;
	}
}

$qryvilesci="SELECT titelpre, nachname, vorname, titelpost, person_id
				FROM public.tbl_person WHERE trim(updatevon)='Administrator'";
$qryvilesci.=" UNION SELECT titelpre, nachname, vorname, titelpost, person_id
				FROM public.tbl_person JOIN tbl_benutzer USING (person_id) JOIN tbl_mitarbeiter ON (uid=mitarbeiter_uid)";
if (isset($_GET['all']))
	$qryvilesci.=" ORDER BY nachname;";
if($resultvilesci = pg_query($conn, $qryvilesci))
{
	while($rowvilesci = pg_fetch_object($resultvilesci))
	{
		$combobox[$i]=trim($rowvilesci->nachname)." ".trim($rowvilesci->vorname).' '.trim(trim($rowvilesci->titelpre).' '.trim($rowvilesci->titelpost));
		$nachname[$i]=trim($rowvilesci->nachname);
		$valuebox[$i]=$rowvilesci->person_id;
		$i++;
	}
}

$qry="SELECT count(*) AS anz FROM berufspraktikum WHERE
	vilesci_firmenbetreuer IS NULL AND trim(firmenbetreuer)!='';";

if($result = pg_query($conn_fas, $qry))
{
	$row=pg_fetch_object($result);
	echo '<BR>Verbleibend: '.$row->anz.' Berufspraktika<BR>';
}

echo "<table class='liste'><tr><th>FAS</th><th>Vilesci</th><th></th><th>Titel/Vorname/Nachname</th></tr>";


$qry="SELECT *,
	trim(substring(trim(firmenbetreuer) from ' [A-ü]*$')) as zweit 
	FROM berufspraktikum WHERE
	vilesci_firmenbetreuer IS NULL AND trim(firmenbetreuer)!=''
	ORDER BY berufspraktikum_pk
	LIMIT 20;";

if($result = pg_query($conn_fas, $qry))
{
	for($k=0;$row=pg_fetch_object($result);$k++)
	{
		if(($row->vilesci_firmenbetreuer=='' OR $row->vilesci_firmenbetreuer==NULL) AND trim($row->firmenbetreuer)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form  method='POST'>";
			echo "<input type='hidden' name='da' value='".$row->berufspraktikum_pk."'>";
			echo "<td>".$row->firmenbetreuer."</td>";
			echo "<td><select name=\"top1\">";
			echo "<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$row->zweit)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else if(soundex($nachname[$j])==soundex($row->zweit))
				{
					echo"<option value=\"".$valuebox[$j]."\">".$combobox[$j]."</option>";
				}
				else if($_GET['all']==true)
				{
					echo"<option value=\"".$valuebox[$j]."\">".$combobox[$j]."</option>";
				}
			}
			echo"</select>";
			echo "</td>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "</form>";
			echo "<form method='Post'><td><input type='text' name='geschlecht' value='m' size='1'><input type='text' name='titel'><input type='text' name='vorname'><input type='text' name='nachname' value='$row->zweit'><input type='submit' name='anlegen' value='Anlegen'></td></form>";
			echo "</tr>";
		}
	}
}
echo "</table>";
?>
</body>
</html>