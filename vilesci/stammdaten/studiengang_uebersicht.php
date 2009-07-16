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
	
		require_once('../../include/functions.inc.php');
    require_once('../../include/studiengang.class.php');

    
	if (isset($_GET["toggle"]) && ($_GET["kz"] != ""))
	{
		$kennzahl = intval($_GET["kz"]);
		$sg_update = new studiengang();
		if(!$sg_update->toggleAktiv($kennzahl))
			die($sg_update->errormsg);
	
	}
	
    $sg = new studiengang();
    if (!$sg->getAll('kurzbzlang',false))
        die($sg->errormsg);
    
    //$htmlstr = "<table class='liste sortable'>\n";
    $htmlstr = "<form name='formular'><input type='hidden' name='check' value=''></form><table id='t1' class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>\n";
	$htmlstr .= "   <thead><tr class='liste'>\n";
    $htmlstr .= "       <th class='table-sortable:numeric' onmouseup='document.formular.check.value=0'>Kz</th><th class='table-sortable:default'>Kurzbez</th><th class='table-sortable:default'>KurzLang</th> <th class='table-sortable:default'>Typ</th><th class='table-sortable:alphanumeric'>Bezeichnung</th><th>Aktiv</th><th class='table-sortable:default'>Telefon</th><th class='table-sortable:default'>Email</th>";
    $htmlstr .= "   </tr></thead><tbody>\n";
    $i = 0;
    foreach ($sg->result as $stg)
    {
        //$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
		$htmlstr .= "   <tr>\n";
		$htmlstr .= "       <td>".$stg->studiengang_kz."</td>\n";
        $htmlstr .= "       <td>".$stg->kurzbz."</td>\n";
        $htmlstr .= "       <td>".$stg->kurzbzlang."</td>\n";
		$htmlstr .= "       <td>".$stg->typ."</td>\n";
        $htmlstr .= "       <td><a href='studiengang_details.php?studiengang_kz=".$stg->studiengang_kz."' target='detail_studiengang'>".$stg->bezeichnung."</a></td>\n";
		
		if($stg->aktiv)
			$aktivbild = "true.gif";	
		else
			$aktivbild = "false.gif";

		$aktivlink = "?toggle=true&kz=".$stg->studiengang_kz;
		
		$htmlstr .= "		<td align='center'><a href='".$aktivlink."'><img src='../../skin/images/".$aktivbild."'></a></td>\n";
        $htmlstr .= "       <td>".$stg->telefon."</td>\n";
        $htmlstr .= "       <td><a href='mailto:".$stg->email."'>".$stg->email."</a></td>\n";
        $htmlstr .= "   </tr>\n";
        $i++;
    }
    $htmlstr .= "</tbody></table>\n";


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
<h2>Studieng&auml;nge &Uuml;bersicht</h2>
<?php 
    echo $htmlstr;
?>
</body>
</html>
