<?php
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	if(isset($_GET['uid']) && isset($_GET['del']))
	{
		//$sql_query = "Delete from tbl_person where uid='".$_GET["uid"]."';";
		//echo $sql_query;
		//if(!pg_exec($conn,$sql_query))
		//   echo "Fehler beim löschen: möglicherweise besteht noch eine Referenz zu einer anderen Tabelle<br>";
		echo 'Loeschen noch nicht implementiert';
	}

	if(isset($_GET['fix']) && isset($_GET['uid']))
	{
		$sql_query = "UPDATE public.tbl_mitarbeiter SET fixangestellt=". ($_GET['fix']=='t'?'false':'true') ." WHERE mitarbeiter_uid='".addslashes($_GET['uid'])."'";
		//echo $sql_query;
		pg_query($conn,$sql_query);
	}

	if(isset($_GET['lek']) && isset($_GET['uid']))
	{
		$sql_query = "UPDATE public.tbl_mitarbeiter SET lektor=". ($_GET['lek']=='t'?'false':'true') ." WHERE mitarbeiter_uid='".addslashes($_GET['uid'])."'";
		//echo $sql_query;
		pg_query($conn,$sql_query);
	}
?>
<html>
<head>
<title>Mitarbeiter Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script language="JavaScript">
function confdel()
{
	if(confirm("Diesen Datensatz wirklick loeschen?"))
	  return true;
	return false;
}
</script>
</head>

<body class="background_main">
<h1>Mitarbeiter &Uuml;bersicht</h1><br>


<?php
	$qry = "SELECT * FROM campus.vw_mitarbeiter";
	if(isset($order))
		$qry .= " ORDER BY $order";
	else
		$qry .= " ORDER BY nachname, vorname";

	if($result = pg_query($conn, $qry))
	{
		echo "<table class='liste'>";
		echo "<tr class='liste'><th><a href='lektor_uebersicht.php?order=uid'>UID</a></th><th>Titel</th><th>Vorname</th><th><a href='lektor_uebersicht.php?order=nachname'>Nachname</a></th><th><a href='lektor_uebersicht.php?order=fixangestellt DESC, nachname'>Fix</a></th><th>Lkt</th><th>Raum</th><th>Standort</th><th>Tel</th><th>eMail</th><th colspan='3'>Aktion</th></tr>";

		for ($i=0; $row=pg_fetch_object($result); $i++)
		{
			echo "<tr class='liste". ($i%2) ."'>";
			if((isset($fix) || isset($lek))&& isset($uid) && $uid==$row->uid) //Anker setzen
				echo "<td nowrap>".$row->uid."<a name='anker1'></a></td>";
			else
				echo "<td nowrap>".$row->uid."</td>";

			echo "<td nowrap>".$row->titelpre."</td>";
			echo "<td nowrap>".$row->vorname."</td>";
			echo "<td nowrap>".$row->nachname."</td>";
			echo "<td nowrap><a href='lektor_uebersicht.php?uid=".$row->uid."&fix=".$row->fixangestellt . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".($row->fixangestellt=='t'?'true':'false').".gif'></a></td>";
			echo "<td nowrap><a href='lektor_uebersicht.php?uid=".$row->uid."&lek=".$row->lektor . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".($row->lektor=='t'?'true':'false').".gif'></a></td>";

			echo "<td nowrap>".$row->ort_kurzbz."</td>";
			echo "<td nowrap>".$row->standort_kurzbz."</td>";
			echo "<td nowrap>".$row->telefonklappe."</td>";
			//echo "<td nowrap><a href='#' onClick='javascript:document.form1.uid=".$lektoren[$i]->uid.";document.form1.fix=".$lektoren[$i]->fixangestellt .";document.form1.order=". (isset($order)?$order:'') .";'><img src='../../skin/images/".$lektoren[$i]->fixangestellt.".gif'></a></td>";
			//echo "<td nowrap><a href='#' onClick='javascript:document.form1.uid=".$lektoren[$i]->uid.";document.form1.fix=".$lektoren[$i]->fixangestellt .";document.form1.order=". (isset($order)?$order:'') .";'lek=".$lektoren[$i]->lektor . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".$lektoren[$i]->lektor.".gif'></a></td>";

			$email=$row->uid.'@technikum-wien.at';
			echo "<td nowrap><a href='mailto:$email'>$email</a></td>";
			echo "<td nowrap class='button'><a href='lektor_edit.php?id=".$row->uid."'>Edit</a></td>";
			echo "<td nowrap class='button'>";
			if ($row->lektor)
			{
				echo "<a href='zeitwunsch.php?uid=".$row->uid."&vorname=".rawurlencode($row->vorname)."&nachname=".rawurlencode($row->nachname)."&titel=".rawurlencode($row->titelpre)." class='linkblue'>Zeitwunsch</a>";
			}
			echo "</td>";
			echo "<td nowrap class='button'><a href='lektor_uebersicht.php?del=1&uid=".$row->uid."' onClick='javascript: return confdel();'>Delete</a></td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	else
		echo "Fehler beim laden der Mitarbeiter: ".pg_errormessage($conn);

	if(isset($_GET['fix']) || isset($_GET['lek'])) //Zum Anker hüpfen
	{
		echo "<script language='JavaScript'>this.location.hash='#anker1'</script>";
	}
?>

</body>
</html>
