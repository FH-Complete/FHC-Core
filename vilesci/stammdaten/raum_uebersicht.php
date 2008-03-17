<?php
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/ort.class.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	{
	   	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	}
    	
	if (isset($_GET["toggle"]))
	{
		if ($_GET["rlehre"] != "" && $_GET["rlehre"] != NULL)
		{
			$rlehre = $_GET["rlehre"];
			$sg_update = new ort($conn);
			$qry = "UPDATE public.tbl_ort SET lehre = NOT lehre WHERE ort_kurzbz='".$rlehre."';";
			if(!pg_query($conn, $qry))
			{
				die('Fehler beim Speichern des Datensatzes');
			}	
		}
		if ($_GET["rres"] != "" && $_GET["rres"] != NULL)
		{
			$rres = $_GET["rres"];
			$sg_update = new ort($conn);
			$qry = "UPDATE public.tbl_ort SET reservieren = NOT reservieren WHERE ort_kurzbz='".$rres."';";
			if(!pg_query($conn, $qry))
			{
				die('Fehler beim Speichern des Datensatzes');
			}	
		}
		if ($_GET["raktiv"] != "" && $_GET["raktiv"] != NULL)
		{
			$raktiv = $_GET["raktiv"];
			$sg_update = new ort($conn);
			$qry = "UPDATE public.tbl_ort SET aktiv = NOT aktiv WHERE ort_kurzbz='".$raktiv."';";
			if(!pg_query($conn, $qry))
			{
				die('Fehler beim Speichern des Datensatzes');
			}	
		}
	}
	
$sg = new ort($conn);
if (!$sg->getAll('ort_kurzbz',false))
    die($sg->errormsg);

//$htmlstr = "<table class='liste sortable'>\n";
$htmlstr = "<form name='formular'><input type='hidden' name='check' value=''></form><table id='t1' class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>\n";
$htmlstr .= "   <thead><tr class='liste'>\n";
$htmlstr .= "    <th class='table-sortable:default' onmouseup='document.formular.check.value=0'>Kurzbezeichnung</th>
		<th class='table-sortable:default'>Bezeichnung</th>
		<th class='table-sortable:default'>Planbezeichnung</th>
		<th class='table-sortable:numeric'>Max. Person</th>
		<th>Lehre</th>
		<th>Reservieren</th>
		<th>Aktiv</th>
		<th class='table-sortable:numeric'>Kosten</th>
		<th class='table-sortable:numeric'>Stockwerk</th>";
$htmlstr .= "   </tr></thead><tbody>\n";
$i = 0;
foreach ($sg->result as $twraum)
{
    //$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
	$htmlstr .= "   <tr>\n";
	$htmlstr .= "       <td><a href='raum_details.php?ort_kurzbz=".$twraum->ort_kurzbz."' target='detail'>".$twraum->ort_kurzbz."</a></td>\n";
	$htmlstr .= "       <td>".$twraum->bezeichnung."</td>\n";
	$htmlstr .= "       <td>".$twraum->planbezeichnung."</td>\n";
	$htmlstr .= "       <td>".$twraum->max_person."</td>\n";
	if($twraum->lehre=='t')
	{
		$lehrebild = "true.gif";	
	}
	else
	{
		$lehrebild = "false.gif";
	}
	$lehrelink = "?toggle=true&rlehre=".$twraum->ort_kurzbz."&rres=NULL&raktiv=NULL";
	$htmlstr .= "	    <td align='center'><a href='".$lehrelink."'><img src='../../skin/images/".$lehrebild."'></a></td>\n";
	if($twraum->reservieren=='t')
	{
		$resbild = "true.gif";	
	}
	else
	{
		$resbild = "false.gif";
	}
	$reslink = "?toggle=true&rres=".$twraum->ort_kurzbz."&rlehre=NULL&raktiv=NULL";
	$htmlstr .= "	    <td align='center'><a href='".$reslink."'><img src='../../skin/images/".$resbild."'></a></td>\n";
	if($twraum->aktiv=='t')
	{
		$aktivbild = "true.gif";	
	}
	else
	{
		$aktivbild = "false.gif";
	}
	$aktivlink = "?toggle=true&raktiv=".$twraum->ort_kurzbz."&rres=NULL&rlehre=NULL";
	$htmlstr .= "	    <td align='center'><a href='".$aktivlink."'><img src='../../skin/images/".$aktivbild."'></a></td>\n";
	$htmlstr .= "       <td>".$twraum->kosten."</td>\n";
	$htmlstr .= "       <td>".$twraum->stockwerk."</td>\n";
	$htmlstr .= "   </tr>\n";
	$i++;
}
$htmlstr .= "</tbody></table>\n";


?>
<html>
<head>
<title>R&auml;ume &Uuml;bersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
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
<h2>R&auml;ume &Uuml;bersicht</h2>



<?php 
    echo $htmlstr;
?>



</body>
</html>
