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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Raab <gerald.raab@technikum-wien.at>.
 */

	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
    require_once('../../include/firma.class.php');
    
	if (!$conn = pg_pconnect(CONN_STRING))
	   	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
    
	$filter = (isset($_GET['filter'])?$_GET['filter']:'');
	$firmentypfilter = (isset($_GET['firmentypfilter'])?$_GET['firmentypfilter']:'');
	
	$htmlstr = "";

	if($filter=='')
		$sql_query = "SELECT * FROM public.tbl_firma WHERE true";
	else 
		$sql_query = "SELECT * FROM public.tbl_firma WHERE lower(name) like lower('%$filter%') OR lower(adresse) like lower('%$filter%') OR lower(anmerkung) like lower('%$filter%')";
	if($firmentypfilter!='')
		$sql_query.=" AND firmentyp_kurzbz='".addslashes($firmentypfilter)."'";
	//echo $sql_query;
    if(!$erg=pg_query($conn, $sql_query))
	{
		$errormsg='Fehler beim Laden der Firma';
	}
	
	else
	{
		
	    $htmlstr .= "</form><table id='t1' class='liste table-autosort:1 table-stripeclass:alternate table-autostripe'>\n";
		$htmlstr .= "   <thead><tr class='liste'>\n";
	    $htmlstr .= "       <th class='table-sortable:numeric'>ID</th><th class='table-sortable:default'>Name</th><th class='table-sortable:default'>Adresse</th><th class='table-sortable:default'>Email</th><th class='table-sortable:default'>Telefon</th><th class='table-sortable:default'>Fax</th><th class='table-sortable:default'>Anmerkung</th><th class='table-sortable:default'>Typ</th><th class='table-sortable:default'>Schule</th>";
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
	        $htmlstr .= "       <td title='".$row->anmerkung."'>".(strlen($row->anmerkung)>30?substr($row->anmerkung,0,27).'...':$row->anmerkung)."</td>\n";
	        $htmlstr .= "       <td>$row->firmentyp_kurzbz</td>\n";
			$htmlstr .= "       <td>".($row->schule=='t'?'Ja':'Nein')."</td>\n";
	        $htmlstr .= "   </tr>\n";
	        $i++;
	    }
	    $htmlstr .= "</tbody></table>\n";
	}


?>
<html>
<head>
<title>Firma Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
<!--
var firmentypfilter='<?php echo $firmentypfilter; ?>';
var filter = '<?php echo $filter; ?>';
-->
</script>

</head>

<body class="background_main">
<h2>Firmen - &Uuml;bersicht</h2>

<?php 
	echo '<table width="100%"><tr><td>';
	echo '<h3>Übersicht</h3>';
	echo '</td><td align="right">';
	echo "<input type='button' onclick='parent.detail_firma.location=\"firma_details.php?neu=true\"' value='Neue Firma anlegen'/>";
	echo '</td></tr></table>';
	//Suche
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
	echo '<input type="text" name="filter" value="'.$filter.'">';
	echo 'Typ: <SELECT name="firmentypfilter">
			<option value="">-- Alle --</option>';
	$firma = new firma($conn);
	$firma->getFirmenTypen();
	foreach ($firma->result as $row)
	{
		if($row->firmentyp_kurzbz==$firmentypfilter)
			$selected='selected';
		else 
			$selected='';
		echo "<option value='$row->firmentyp_kurzbz' $selected>$row->firmentyp_kurzbz</option>";
	}
	echo '</SELECT>';
	echo '<input type="submit" value="Suchen">';
	echo '</form>';
	
	echo $htmlstr;
?>



</body>
</html>
