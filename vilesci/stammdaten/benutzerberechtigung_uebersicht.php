<?php
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
    require_once('../../include/studiengang.class.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
    
	$htmlstr = "";
	
	$sql_query = "select distinct(tbl_benutzerberechtigung.uid), tbl_person.nachname, tbl_person.vorname from tbl_benutzerberechtigung, tbl_benutzer, tbl_person where tbl_benutzerberechtigung.uid = tbl_benutzer.uid and tbl_benutzer.person_id = tbl_person.person_id order by tbl_benutzerberechtigung.uid";
	
    if(!$erg=pg_query($conn, $sql_query))
	{
		$errormsg='Fehler beim laden der Berechtigungen';
	}
	
	else
	{
		//$htmlstr = "<table class='liste sortable'>\n";
		$htmlstr .= "<div style='text-align:right'>";
		$htmlstr .= "<form name='neuform' action='benutzerberechtigung_details.php' target='detail'><input type='text' value='' name='uid'>&nbsp;<input type='submit' name='neuschick' value='go'></form>";
		$htmlstr .= "</div>";
	    $htmlstr .= "<form name='formular'><input type='hidden' name='check' value=''></form><table id='t1' class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>\n";
		$htmlstr .= "   <thead><tr class='liste'>\n";
	    $htmlstr .= "       <th class='table-sortable:default'>UID</th><th class='table-sortable:default'>Vorname</th><th class='table-sortable:alphanumeric'>Nachname</th>";
	    $htmlstr .= "   </tr></thead><tbody>\n";
	    $i = 0;
		while($row=pg_fetch_object($erg))
	    {
	        //$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
			$htmlstr .= "   <tr>\n";
	        $htmlstr .= "       <td>".$row->uid."</td>\n";
			$htmlstr .= "       <td>".$row->vorname."</td>\n";
	        $htmlstr .= "       <td><a href='benutzerberechtigung_details.php?uid=".$row->uid."' target='detail'>".$row->nachname."</a></td>\n";
	        $htmlstr .= "   </tr>\n";
	        $i++;
	    }
	    $htmlstr .= "</tbody></table>\n";
	}


?>
<html>
<head>
<title>Studieng&auml;nge Uebersicht</title>
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
<h2>Benutzerberechtigungen &Uuml;bersicht</h2>



<?php 
    echo $htmlstr;
?>



</body>
</html>
