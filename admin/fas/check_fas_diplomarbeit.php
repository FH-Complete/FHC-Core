<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

include('../../vilesci/config.inc.php');

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
$combobox[]="";
$valuebox[]="";
$nachname[]="";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Diplomarbeiten-Datenkorrektur</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-9">
<link href="../../skin/vilesci_old.css" rel="stylesheet" type="text/css">
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
	if(isset($_POST['erst']) AND trim($_POST['erst'])!='')
	{
		$qry1= "UPDATE diplomarbeit SET vilesci_erstbegutachter='".$_POST['erst']."' WHERE diplomarbeit_pk='".$_POST['da']."';";
	}
	if(isset($_POST['top1']) AND trim($_POST['top1'])!='')
	{
		$qry1= "UPDATE diplomarbeit SET vilesci_zweitbegutachter='".$_POST['top1']."' WHERE diplomarbeit_pk='".$_POST['da']."';";
	}
	if(isset($_POST['top2']) AND trim($_POST['top2'])!='')
	{
		$qry1= "UPDATE diplomarbeit SET vilesci_betreuer='".$_POST['top2']."' WHERE diplomarbeit_pk='".$_POST['da']."';";
	}
	if(isset($_POST['top3']) AND trim($_POST['top3'])!='')
	{
		$qry1= "UPDATE diplomarbeit SET vilesci_firmenbetreuer='".$_POST['top3']."' WHERE diplomarbeit_pk='".$_POST['da']."';";
	}
	if(isset($_POST['top4']) AND trim($_POST['top4'])!='')
	{
		$qry1= "UPDATE diplomarbeit SET vilesci_pruefer='".$_POST['top4']."' WHERE diplomarbeit_pk='".$_POST['da']."';";
	}
	if(isset($_POST['top5']) AND trim($_POST['top5'])!='')
	{
		$qry1= "UPDATE diplomarbeit SET vilesci_vorsitzender='".$_POST['top5']."' WHERE diplomarbeit_pk='".$_POST['da']."';";
	}
	if(isset($_POST['top6']) AND trim($_POST['top6'])!='')
	{
		$qry1= "UPDATE diplomarbeit SET vilesci_pruefer1='".$_POST['top6']."' WHERE diplomarbeit_pk='".$_POST['da']."';";
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

$qry="SELECT count(*) AS anz FROM diplomarbeit WHERE
	((vilesci_erstbegutachter IS NULL AND trim(erstbegutachter)!='') OR
	(vilesci_zweitbegutachter IS NULL AND trim(zweitbegutachter)!='') OR
	(vilesci_betreuer IS NULL AND trim(betreuer)!='') OR
	(vilesci_firmenbetreuer IS NULL AND trim(firmenbetreuer)!='') OR
	(vilesci_pruefer IS NULL AND trim(pruefer)!='') OR
	(vilesci_vorsitzender IS  NULL AND trim(vorsitzender)!='') OR
	(vilesci_pruefer1 IS NULL AND trim(pruefer1)!=''));";
if($result = pg_query($conn_fas, $qry))
{
	$row=pg_fetch_object($result);
	echo '<BR>Verbleibend: '.$row->anz.' Diplomarbeiten<BR>';
}

echo "<table class='liste'><tr><th>FAS</th><th>Vilesci</th><th></th><th>Titel/Vorname/Nachname</th></tr>";


$qry="SELECT *, trim(substring(trim(erstbegutachter) from ' [A-ü]*$')) as erst, trim(substring(trim(zweitbegutachter) from ' [A-ü]*$')) as zweit, trim(substring(trim(betreuer) from ' [A-ü]*$')) as dritt,
		trim(substring(trim(firmenbetreuer) from ' [A-ü]*$')) as viert, trim(substring(trim(pruefer) from ' [A-ü]*$')) as fuenft,
		trim(substring(trim(vorsitzender) from ' [A-ü]*$')) as sechst, trim(substring(trim(pruefer1) from ' [A-ü]*$')) as siebent
	FROM diplomarbeit WHERE
	((vilesci_erstbegutachter IS NULL AND trim(erstbegutachter)!='') OR
	(vilesci_zweitbegutachter IS NULL AND trim(zweitbegutachter)!='') OR
	(vilesci_betreuer IS NULL AND trim(betreuer)!='') OR
	(vilesci_firmenbetreuer IS NULL AND trim(firmenbetreuer)!='') OR
	(vilesci_pruefer IS NULL AND trim(pruefer)!='') OR
	(vilesci_vorsitzender IS  NULL AND trim(vorsitzender)!='') OR
	(vilesci_pruefer1 IS NULL AND trim(pruefer1)!=''))
	ORDER BY diplomarbeit_pk
	LIMIT 10;";
//ORDER BY diplomarbeit_pk

if($result = pg_query($conn_fas, $qry))
{
	for($k=0;$row=pg_fetch_object($result);$k++)
	{
		if($row->vilesci_erstbegutachter=='' AND $row->erstbegutachter!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form  method='POST'>";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "<td>".$row->erstbegutachter."</td>";
			echo "<td><select name=\"erst\">";
			echo "<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$row->erst)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else if(soundex($nachname[$j])==soundex($row->erst))
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
			echo "<form method='Post'><td><input type='text' name='geschlecht' value='m' size='1'><input type='text' name='titel'><input type='text' name='vorname'><input type='text' name='nachname' value='$row->erst'><input type='submit' name='anlegen' value='Anlegen'></td></form>";
			echo "</tr>";
		}
		if($row->vilesci_zweitbegutachter=='' AND $row->zweitbegutachter!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form  method='POST'>";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "<td>".$row->zweitbegutachter."</td>";
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
		if(($row->vilesci_betreuer=='' OR $row->vilesci_betreuer==NULL) AND trim($row->betreuer)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form  method='POST'>";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "<td>".$row->betreuer."</td>";
			echo "<td><select name=\"top2\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$row->dritt)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else  if(soundex($nachname[$j])==soundex($row->dritt))
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
			echo "<form method='Post'><td><input type='text' name='geschlecht' value='m' size='1'><input type='text' name='titel'><input type='text' name='vorname'><input type='text' name='nachname' value='$row->dritt'><input type='submit' name='anlegen' value='Anlegen'></td></form>";
			echo "</tr>";
		}
		if(($row->vilesci_firmenbetreuer=='' OR $row->vilesci_firmenbetreuer==NULL) AND trim($row->firmenbetreuer)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form  method='POST'>";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "<td>".$row->firmenbetreuer."</td>";
			echo "<td><select name=\"top3\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$row->viert)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else  if(soundex($nachname[$j])==soundex($row->viert))
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
			echo "<form method='Post'><td><input type='text' name='geschlecht' value='m' size='1'><input type='text' name='titel'><input type='text' name='vorname'><input type='text' name='nachname' value='$row->viert'><input type='submit' name='anlegen' value='Anlegen'></td></form>";
			echo "</tr>";
		}
		if(($row->vilesci_pruefer=='' OR $row->vilesci_pruefer==NULL) AND trim($row->pruefer)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form  method='POST'>";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "<td>".$row->pruefer."</td>";
			echo "<td><select name=\"top4\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$row->fuenft)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else  if(soundex($nachname[$j])==soundex($row->fuenft))
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
			echo "<form method='Post'><td><input type='text' name='geschlecht' value='m' size='1'><input type='text' name='titel'><input type='text' name='vorname'><input type='text' name='nachname' value='$row->fuenft'><input type='submit' name='anlegen' value='Anlegen'></td></form>";
			echo "</tr>";
		}
		if(($row->vilesci_vorsitzender=='' OR $row->vilesci_vorsitzender==NULL) AND trim($row->vorsitzender)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form  method='POST'>";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "<td>".$row->vorsitzender."</td>";
			echo "<td><select name=\"top5\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$row->sechst)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else if(soundex($nachname[$j])==soundex($row->sechst))
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
			echo "<form method='Post'><td><input type='text' name='geschlecht' value='m' size='1'><input type='text' name='titel'><input type='text' name='vorname'><input type='text' name='nachname' value='$row->sechst'><input type='submit' name='anlegen' value='Anlegen'></td></form>";
			echo "</tr>";
		}
		if(($row->vilesci_pruefer1=='' OR $row->vilesci_pruefer1==NULL) AND trim($row->pruefer1)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form  method='POST'>";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "<td>".$row->pruefer1."</td>";
			echo "<td><select name=\"top6\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$row->siebent)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else if(soundex($nachname[$j])==soundex($row->siebent))
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
			echo "<form method='Post'><td><input type='text' name='geschlecht' value='m' size='1'><input type='text' name='titel'><input type='text' name='vorname'><input type='text' name='nachname' value='$row->siebent'><input type='submit' name='anlegen' value='Anlegen'></td></form>";
			echo "</tr>";
		}

	}
}
echo "</table>";
?>
</body>
</html>