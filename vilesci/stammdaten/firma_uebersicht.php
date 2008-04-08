<?php
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
    require_once('../../include/firma.class.php');
	if (!$conn = pg_pconnect(CONN_STRING))
	   	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
    
	$htmlstr = "";
	
	$sql_query = "SELECT * FROM public.tbl_firma";
	
    if(!$erg=pg_query($conn, $sql_query))
	{
		$errormsg='Fehler beim Laden der Firma';
	}
	
	else
	{
		
	    $htmlstr .= "</form><table id='t1' class='liste table-autosort:1 table-stripeclass:alternate table-autostripe'>\n";
		$htmlstr .= "   <thead><tr class='liste'>\n";
	    $htmlstr .= "       <th class='table-sortable:numeric'>ID</th><th class='table-sortable:default'>Name</th><th class='table-sortable:default'>Adresse</th><th class='table-sortable:default'>Email</th><th class='table-sortable:default'>Telefon</th><th class='table-sortable:default'>Fax</th><th class='table-sortable:default'>Anmerkung</th><th class='table-sortable:default'>Typ</th>";
	    $htmlstr .= "   </tr></thead><tbody>\n";
	    $i = 0;
		while($row=pg_fetch_object($erg))
	    {
	        //$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
			$htmlstr .= "   <tr>\n";
	        $htmlstr .= "       <td><a href='firma_details.php?firma_id=".$row->firma_id."' target='detail_firma'>".$row->firma_id."</a></td>\n";
			$htmlstr .= "       <td><a href='firma_details.php?firma_id=".$row->firma_id."' target='detail_firma'>".$row->name."</a></td>\n";
	        $htmlstr .= "       <td>$row->adresse</td>\n";
	        $htmlstr .= "       <td>$row->email</td>\n";
	        $htmlstr .= "       <td>$row->telefon</td>\n";
	        $htmlstr .= "       <td>$row->fax</td>\n";
	        $htmlstr .= "       <td>$row->anmerkung</td>\n";
	        $htmlstr .= "       <td>$row->firmentyp_kurzbz</td>\n";
	        $htmlstr .= "   </tr>\n";
	        $i++;
	    }
	    $htmlstr .= "</tbody></table>\n";
	}


?>
<html>
<head>
<title>Firma Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="JavaScript">

</script>

</head>

<body class="background_main">
<h2>Firmen - &Uuml;bersicht</h2>

<?php 
	echo '<table width="100%"><tr><td>';
	echo '<h3>Übersicht</h3>';
	echo '</td><td align="right">';
	echo "<input type='button' onclick='parent.detail_firma.location=\"firma_details.php?neu=true\"' value='Neue Firma anlegen'/>";
	echo '</td></tr></table><br />';
	
	echo $htmlstr;
?>



</body>
</html>
