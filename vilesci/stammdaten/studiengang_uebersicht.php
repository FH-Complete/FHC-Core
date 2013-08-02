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
require_once('../../include/benutzerberechtigung.class.php');

	$user = get_uid();

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	
	if(!$rechte->isBerechtigt('basis/studiengang'))
		die('Sie haben keine Berechtigung fuer diese Seite');
	
	if (isset($_GET["toggle"]) && ($_GET["kz"] != ""))
	{
		$kennzahl = intval($_GET["kz"]);
		$stg_hlp = new studiengang();
		if(!$stg_hlp->load($kennzahl))
			die('Studiengang nicht gefunden');
		
		if($rechte->isBerechtigt('basis/studiengang', $stg_hlp->oe_kurzbz, 'suid'))
		{
			$sg_update = new studiengang();
			if(!$sg_update->toggleAktiv($kennzahl))
				die($sg_update->errormsg);
		}
		else 
			die('Sie haben keine Rechte fuer diese Aktion');
	}
	
    $sg = new studiengang();
    if (!$sg->loadArray($rechte->getStgKz('basis/studiengang'),'kurzbzlang',false))
        die($sg->errormsg);
    
    //$htmlstr = "<table class='liste sortable'>\n";
    $htmlstr = "<form name='formular'><input type='hidden' name='check' value=''></form><table class=\"tablesorter\" id=\"t1\">\n";
	$htmlstr .= "   <thead><tr>\n";
    $htmlstr .= "       <th onmouseup='document.formular.check.value=0'>Kz</th><th>Kurzbz</th><th>KurzbzLang</th> <th>Typ</th><th>Bezeichnung</th><th>Aktiv</th><th>Email</th>";
    $htmlstr .= "   </tr></thead><tbody>\n";
    $i = 0;
    foreach ($sg->result as $stg)
    {
        //$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
		$htmlstr .= "   <tr>\n";
		$htmlstr .= "       <td><a href='studiengang_details.php?studiengang_kz=".$stg->studiengang_kz."' target='detail_studiengang'>".$stg->studiengang_kz."</a></td>\n";
        $htmlstr .= "       <td>".$stg->kurzbz."</td>\n";
        $htmlstr .= "       <td>".$stg->kurzbzlang."</td>\n";
		$htmlstr .= "       <td>".$stg->typ."</td>\n";
        $htmlstr .= "       <td><a href='studiengang_details.php?studiengang_kz=".$stg->studiengang_kz."' target='detail_studiengang'>".$stg->bezeichnung."</a></td>\n";
		
		if($stg->aktiv)
			$aktivbild = "true.png";	
		else
			$aktivbild = "false.png";

		$aktivlink = "?toggle=true&kz=".$stg->studiengang_kz;
		
		$htmlstr .= "		<td align='center'><a href='".$aktivlink."'><img src='../../skin/images/".$aktivbild."' height='20px'></a></td>\n";
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
<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
<script type="text/javascript" src="../../include/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript">
$(document).ready(function() 
	{ 
	    $("#t1").tablesorter(
		{
			sortList: [[2,0]],
			widgets: ["zebra"],
			headers: {5:{sorter:false}}
		}); 
	} 
);

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
