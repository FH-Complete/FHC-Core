<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
			
		require_once('../../include/functions.inc.php');
    require_once('../../include/studiengang.class.php');
    
	$htmlstr = "";
	
	$sql_query = "select distinct(tbl_benutzerberechtigung.uid), tbl_person.nachname, tbl_person.vorname from tbl_benutzerberechtigung, tbl_benutzer, tbl_person where tbl_benutzerberechtigung.uid = tbl_benutzer.uid and tbl_benutzer.person_id = tbl_person.person_id order by tbl_benutzerberechtigung.uid";
	
    if(!$erg=$db->db_query($sql_query))
	{
		$htmlstr='Fehler beim laden der Berechtigungen';
	}
	
	else
	{
		//$htmlstr = "<table class='liste sortable'>\n";
		$htmlstr .= "<div style='text-align:right'>";
		$htmlstr .= "<form name='neuform' action='benutzerberechtigung_details.php' target='vilesci_detail'><input type='text' value='' name='uid'>&nbsp;<input type='submit' name='neuschick' value='go'></form>";
		$htmlstr .= "</div>";
	    $htmlstr .= "<form name='formular'><input type='hidden' name='check' value=''></form><table id='t1' class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>\n";
		$htmlstr .= "   <thead><tr class='liste'>\n";
	    $htmlstr .= "       <th class='table-sortable:default'>UID</th><th class='table-sortable:default'>Vorname</th><th class='table-sortable:alphanumeric'>Nachname</th>";
	    $htmlstr .= "   </tr></thead><tbody>\n";
	    $i = 0;
		while($row=$db->db_fetch_object($erg))
	    {
	        //$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
			$htmlstr .= "   <tr>\n";
	        $htmlstr .= "       <td>".$row->uid."</td>\n";
			$htmlstr .= "       <td>".$row->vorname."</td>\n";
	        $htmlstr .= "       <td><a href='benutzerberechtigung_details.php?uid=".$row->uid."' target='vilesci_detail'>".$row->nachname."</a></td>\n";
	        $htmlstr .= "   </tr>\n";
	        $i++;
	    }
	    $htmlstr .= "</tbody></table>\n";
	}


?>
<html>
<head>
<title>Studieng&auml;nge Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
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
