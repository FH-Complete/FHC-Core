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
$k=0;
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
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
</head>
<body>
<style>
TR.liste
{
	background-color: #D3DCE3;
}
TR.liste0
{
	background-color: #EEEEEE;
}
TR.liste1
{
	background-color: #DDDDDD;
}
</style>
<?php

if(isset($_POST['da']))
{
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

ob_flush();
flush();

$qryvilesci="SELECT titelpre, nachname, vorname, titelpost, person_id FROM public.tbl_person WHERE 
	(person_id IN (SELECT person_id FROM public.tbl_benutzer, public.tbl_mitarbeiter WHERE public.tbl_benutzer.uid=public.tbl_mitarbeiter.mitarbeiter_uid) 
	OR trim(updatevon)='Administrator')
	AND trim(nachname)!='Account' AND trim(nachname)!='Lektor' AND trim(vorname)!='Lektor' 
	ORDER BY nachname;";
if($resultvilesci = pg_query($conn, $qryvilesci))
{
	while($rowvilesci = pg_fetch_object($resultvilesci))
	{
		$combobox[$i]=trim(trim($rowvilesci->titelpre).' '.trim($rowvilesci->vorname).' '.trim($rowvilesci->nachname)." ".trim($rowvilesci->titelpost));
		$nachname[$i]=trim($rowvilesci->nachname);
		$valuebox[$i]=$rowvilesci->person_id;
		$i++;
	}
}

echo "<table class='liste'><tr><th></th><th>FAS</th><th>Vilesci</th><th></th></tr>";

$qry="SELECT * FROM diplomarbeit WHERE 
	((vilesci_zweitbegutachter IS NULL AND trim(zweitbegutachter)!='') OR
	(vilesci_betreuer IS NULL AND trim(betreuer)!='') OR
	(vilesci_firmenbetreuer IS NULL AND trim(firmenbetreuer)!='') OR
	(vilesci_pruefer IS NULL AND trim(pruefer)!='') OR
	(vilesci_vorsitzender IS  NULL AND trim(vorsitzender)!='') OR
	(vilesci_pruefer1 IS NULL AND trim(pruefer1)!=''))
	ORDER BY diplomarbeit_pk 
	LIMIT 10;";

if($result = pg_query($conn_fas, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$qryselect="SELECT trim(substring(trim(zweitbegutachter) from ' [A-ü]*$')) as zweit, trim(substring(trim(betreuer) from ' [A-ü]*$')) as dritt,
		trim(substring(trim(firmenbetreuer) from ' [A-ü]*$')) as viert, trim(substring(trim(pruefer) from ' [A-ü]*$')) as fuenft,
		trim(substring(trim(vorsitzender) from ' [A-ü]*$')) as sechst, trim(substring(trim(pruefer1) from ' [A-ü]*$')) as siebent  
		FROM diplomarbeit WHERE diplomarbeit_pk='".$row->diplomarbeit_pk."';";
		$resultselect = pg_query($conn_fas, $qryselect);
		$rowselect = pg_fetch_object($resultselect);
		
		$k++;
		if($row->vilesci_zweitbegutachter=='' AND $row->zweitbegutachter!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form action='$PHP_SELF'  method='POST'>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "<td>'".$row->zweitbegutachter."'";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "</td>";
			echo "<td><select name=\"top1\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$rowselect->zweit)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else 
				{
					echo"<option value=\"".$valuebox[$j]."\">".$combobox[$j]."</option>";
				}
			}
			echo"</select>";
			echo "</td>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "</form>";
			echo "</tr>";
		}
		if(($row->vilesci_betreuer=='' OR $row->vilesci_betreuer==NULL) AND trim($row->betreuer)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form action='$PHP_SELF'  method='POST'>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "<td>'".$row->betreuer."'";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "</td>";
			echo "<td><select name=\"top2\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$rowselect->dritt)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else 
				{
					echo"<option value=\"".$valuebox[$j]."\">".$combobox[$j]."</option>";
				}
			}	
			echo"</select>";
			echo "</td>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "</form>";
			echo "</tr>";
		}
		if(($row->vilesci_firmenbetreuer=='' OR $row->vilesci_firmenbetreuer==NULL) AND trim($row->firmenbetreuer)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form action='$PHP_SELF'  method='POST'>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "<td>'".$row->firmenbetreuer."'";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "</td>";
			echo "<td><select name=\"top3\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$rowselect->viert)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else 
				{
					echo"<option value=\"".$valuebox[$j]."\">".$combobox[$j]."</option>";
				}
			}	
			echo"</select>";
			echo "</td>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "</form>";
			echo "</tr>";
		}
		if(($row->vilesci_pruefer=='' OR $row->vilesci_pruefer==NULL) AND trim($row->pruefer)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form action='$PHP_SELF'  method='POST'>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "<td>'".$row->pruefer."'";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "</td>";
			echo "<td><select name=\"top4\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$rowselect->fuenft)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else 
				{
					echo"<option value=\"".$valuebox[$j]."\">".$combobox[$j]."</option>";
				}
			}	
			echo"</select>";
			echo "</td>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "</form>";
			echo "</tr>";
		}
		if(($row->vilesci_vorsitzender=='' OR $row->vilesci_vorsitzender==NULL) AND trim($row->vorsitzender)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form action='$PHP_SELF'  method='POST'>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "<td>'".$row->vorsitzender."'";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "</td>";
			echo "<td><select name=\"top5\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$rowselect->sechst)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else 
				{
					echo"<option value=\"".$valuebox[$j]."\">".$combobox[$j]."</option>";
				}
			}
			echo"</select>";
			echo "</td>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "</form>";
			echo "</tr>";
		}
		if(($row->vilesci_pruefer1=='' OR $row->vilesci_pruefer1==NULL) AND trim($row->pruefer1)!='')
		{
			echo "<tr class='liste".($k%2)."'>";
			echo "<form action='$PHP_SELF'  method='POST'>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "<td>'".$row->pruefer1."'";
			echo "<input type='hidden' name='da' value='".$row->diplomarbeit_pk."'>";
			echo "</td>";
			echo "<td><select name=\"top6\">";
			echo"<option value=\"\"></option>";
			for($j=0;$j<$i;$j++)
			{
				if($nachname[$j]==$rowselect->siebent)
				{
					echo"<option value=\"".$valuebox[$j]."\" selected=\"selected\">".$combobox[$j]."</option>";
				}
				else 
				{
					echo"<option value=\"".$valuebox[$j]."\">".$combobox[$j]."</option>";
				}
			}	
			echo"</select>";
			echo "</td>";
			echo "<td><input type='submit' value='Speichern'></td>";
			echo "</form>";
			echo "</tr>";			
		}
	}
}
echo "</table>";
?>
</body>
</html>