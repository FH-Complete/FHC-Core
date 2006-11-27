<?php
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	include('../../include/person.class.php');
	include('../../include/mitarbeiter.class.php');
	if (!$conn = @pg_pconnect(CONN_STRING)) 
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	if(isset($_GET["uid"]) && isset($_GET["del"]))
	{
		$sql_query = "Delete from tbl_person where uid='".$_GET["uid"]."';";
		//echo $sql_query;
		if(!pg_exec($conn,$sql_query))
		   echo "Fehler beim löschen: möglicherweise besteht noch eine Referenz zu einer anderen Tabelle<br>";
	}
	
	if(isset($fix) && isset($uid))
	{
		$sql_query = "UPDATE tbl_mitarbeiter SET fixangestellt=". ($fix=='true'?'false':'true') ." WHERE uid='$uid'";
		//echo $sql_query;
		pg_exec($conn,$sql_query);
		   
	}
	
	if(isset($lek) && isset($uid))
	{
		   
		$sql_query = "UPDATE tbl_mitarbeiter SET lektor=". ($lek=='true'?'false':'true') ." WHERE uid='$uid'";
		//echo $sql_query;
		pg_exec($conn,$sql_query);
	}
	
	$f_temp=new mitarbeiter($conn);
	if(isset($order))
	   $lektoren=$f_temp->getAll($order);
	else 
	   $lektoren=$f_temp->getAll();
	   
	
?>

<html>
<head>
<title>Mitarbeiter Übersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script language="JavaScript">
function confdel()
{
	if(confirm("Diesen Datensatz wirklick löschen?"))
	  return true;
	return false;
}
</script>
</head>

<body class="background_main">
<h2>Mitarbeiter Übersicht</h2><br>

<table class='liste'>
<tr class='liste'><th><a href='lektor_uebersicht.php?order=m.uid'>UID</a></th><th>Titel</th><th>Vornamen</th><th><a href='lektor_uebersicht.php?order=nachname'>Nachname</a></th><th><a href='lektor_uebersicht.php?order=fixangestellt DESC, nachname'>Fix</a></th><th>Lkt</th><th>Raum</th><th>Tel</th><th>eMail</th><th colspan="3">Aktion</th></tr>

<?php
	$num_rows=count($lektoren);
	for ($i=0; $i<$num_rows; $i++)
	{
		echo "<tr class='liste". ($i%2) ."'>";
		if((isset($fix) || isset($lek))&& isset($uid) && $uid==$lektoren[$i]->uid) //Anker setzen
			echo "<td nowrap>".$lektoren[$i]->uid."<a name='anker1'></a></td>";
		else
			echo "<td nowrap>".$lektoren[$i]->uid."</td>";
			
		echo "<td nowrap>".$lektoren[$i]->titel."</td>";
		echo "<td nowrap>".$lektoren[$i]->vornamen."</td>";
		echo "<td nowrap>".$lektoren[$i]->nachname."</td>";
		echo "<td nowrap><a href='lektor_uebersicht.php?uid=".$lektoren[$i]->uid."&fix=".$lektoren[$i]->fixangestellt . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".$lektoren[$i]->fixangestellt.".gif'></a></td>";
		echo "<td nowrap><a href='lektor_uebersicht.php?uid=".$lektoren[$i]->uid."&lek=".$lektoren[$i]->lektor . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".$lektoren[$i]->lektor.".gif'></a></td>";
		
		echo "<td nowrap>".$lektoren[$i]->ort_kurzbz."</td>";
		echo "<td nowrap>".$lektoren[$i]->telefonklappe."</td>";
		//echo "<td nowrap><a href='#' onClick='javascript:document.form1.uid=".$lektoren[$i]->uid.";document.form1.fix=".$lektoren[$i]->fixangestellt .";document.form1.order=". (isset($order)?$order:'') .";'><img src='../../skin/images/".$lektoren[$i]->fixangestellt.".gif'></a></td>";
		//echo "<td nowrap><a href='#' onClick='javascript:document.form1.uid=".$lektoren[$i]->uid.";document.form1.fix=".$lektoren[$i]->fixangestellt .";document.form1.order=". (isset($order)?$order:'') .";'lek=".$lektoren[$i]->lektor . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".$lektoren[$i]->lektor.".gif'></a></td>";

		$email=$lektoren[$i]->uid.'@technikum-wien.at'; 
		echo "<td nowrap><a href='mailto:$email'>$email</a></td>";
		echo "<td nowrap><a href='lektor_edit.php?id=".$lektoren[$i]->uid."' class='linkblue'>Edit</a></td>";
		echo "<td nowrap>";
		if ($lektoren[$i]->lektor)
		{
			echo "<a href='zeitwunsch.php?uid=".$lektoren[$i]->uid."&vornamen=".rawurlencode($lektoren[$i]->vornamen)."&nachname=".rawurlencode($lektoren[$i]->nachname)."&titel=".rawurlencode($lektoren[$i]->titel)." class='linkblue'>Zeitwunsch</a>";
		}
		echo "</td>";
		echo "<td nowrap><a href='lektor_uebersicht.php?del=1&uid=".$lektoren[$i]->uid."' class='linkblue' onClick='javascript: return confdel();'>Delete</a></td>";
		echo "</tr>";
	}
	echo "</table>";
	
	if(isset($fix) || isset($lek)) //Zum Anker hüpfen
	{
		echo "<script language='JavaScript'>this.location.hash='#anker1'</script>";
	}
?>

</body>
</html>
