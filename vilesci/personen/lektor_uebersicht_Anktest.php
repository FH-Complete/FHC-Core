<?php
	include('../../include/config.inc.php');
	include('../../include/functions.inc.php');
	include('../../include/person.class.php');
	include('../../include/mitarbeiter.class.php');
	if (!$conn = @pg_pconnect(CONN_STRING)) 
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	if(isset($fix) && strlen($fix)>0 && isset($uid) && strlen($uid))
	{
		$sql_query = "UPDATE tbl_mitarbeiter SET fixangestellt=". ($fix=='true'?'false':'true') ." WHERE uid='$uid'";
		//echo $sql_query;
		pg_exec($conn,$sql_query);
		   
	}
	
	if(isset($lek) && strlen($lek)>0 && isset($uid) && strlen($uid)>0)
	{
		   
		$sql_query = "UPDATE tbl_mitarbeiter SET lektor=". ($lek=='true'?'false':'true') ." WHERE uid='$uid'";
		//echo $sql_query;
		pg_exec($conn,$sql_query);
	}
	   	
	$f_temp=new mitarbeiter();
	if(isset($order) && strlen($order)>0)
	   $lektoren=$f_temp->getAll($order);
	else 
	   $lektoren=$f_temp->getAll();
	   
	
?>

<html>
<head>
<title>Mitarbeiter Übersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h2>Mitarbeiter Übersicht</h2><br>
<form name='form1' action='lektor_uebersicht.php#ank' method='POST'>
<input type='hidden' name='uid'>
<input type='hidden' name='fix'>
<input type='hidden' name='lek'>
<input type='hidden' name='order'>
<table class='liste'>
<tr><th><a href='lektor_uebersicht.php?order=m.uid'>UID</a></th><th>Titel</th><th>Vornamen</th><th><a href='lektor_uebersicht.php?order=nachname'>Nachname</a></th><th><a href='lektor_uebersicht.php?order=fixangestellt'>Fix</a></th><th>Lektor</th><th>eMail</th><th colspan="2">Aktion</th></tr>

<?php
	$num_rows=count($lektoren);
	for ($i=0; $i<$num_rows; $i++)
	{
		echo "<tr class='liste". ($i%2) ."'>";
		
		if($uid == $lektoren[$i]->uid)
			echo "<td nowrap><a name='ank'>".$lektoren[$i]->uid."</a></td>";
		else
			echo "<td nowrap>".$lektoren[$i]->uid."</td>";
			
		echo "<td nowrap>".$lektoren[$i]->titel."</td>";
		echo "<td nowrap>".$lektoren[$i]->vornamen."</td>";
		echo "<td nowrap>".$lektoren[$i]->nachname."</td>";
		//echo "<td nowrap><a href='lektor_uebersicht.php?uid=".$lektoren[$i]->uid."&fix=".$lektoren[$i]->fixangestellt . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".$lektoren[$i]->fixangestellt.".gif'></a></td>";
		//echo "<td nowrap><a href='lektor_uebersicht.php?uid=".$lektoren[$i]->uid."&lek=".$lektoren[$i]->lektor . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".$lektoren[$i]->lektor.".gif'></a></td>";
		
		echo "<td nowrap><a href='#' onClick='javascript:document.form1.uid=\"".$lektoren[$i]->uid."\";document.form1.fix=\"".$lektoren[$i]->fixangestellt ."\";".(isset($order)?"document.form1.order=\"".$order."\";":'') ."document.form1.submit();'><img src='../../skin/images/".$lektoren[$i]->fixangestellt.".gif'></a></td>";
		echo "<td nowrap><a href='#' onClick='javascript:document.form1.uid=\"".$lektoren[$i]->uid."\";document.form1.lek=\"".$lektoren[$i]->lektor ."\";". (isset($order)?"document.form1.order=\"".$order."\";":'') ."document.form1.submit();'><img src='../../skin/images/".$lektoren[$i]->lektor.".gif'></a></td>";

		$email=$lektoren[$i]->uid.'@technikum-wien.at'; 
		echo "<td nowrap><a href='mailto:$email'>$email</a></td>";
		echo "<td nowrap><a href='lektor_edit.php?id=$lektoren[$i]->uid' class='linkblue'>Edit</a></td>";
		echo "<td nowrap>";
		if ($lektoren[$i]->lektor)
		{
			echo "<a href='zeitwunsch.php?uid=$lektoren[$i]->uid&vornamen=".rawurlencode($lektoren[$i]->vornamen)."&nachname=".rawurlencode($lektoren[$i]->nachname)."&titel=".rawurlencode($lektoren[$i]->titel)." class='linkblue'>Zeitwunsch</a>";
		}
		echo "</td></tr>";
	}
?>
</table>
</form>
</body>
</html>
