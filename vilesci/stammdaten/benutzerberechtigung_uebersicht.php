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
 *          Manfred Kindl	<manfred.kindl@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/benutzer.class.php');

echo '<html>
<head>
<title>Berechtigungen Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
<script src="../../include/js/jquery1.9.min.js" type="text/javascript"></script>		
<script language="JavaScript" type="text/javascript">
function checkLength()
{
	filter = document.getElementById("searchbox").value;
	if(filter.length<2)
	{
		alert ("Bitte geben Sie mindestens 2 Zeichen für die Suche ein");
		return false;
	}
	else
		return true;	
}
$(document).ready(function() 
	{ 
	    $("#t1").tablesorter(
			{
				sortList: [[0,0],[1,0],[2,0]],
				widgets: ["zebra"],
				headers: {4:{sorter:false}}
			}); 
	});

</script>

</head>

<body class="background_main" onload="document.getElementById(\'searchbox\').focus()">
<h2>Benutzerberechtigungen Übersicht</h2>';

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

//Rechte pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/berechtigung'))
	die('Sie haben keine Berechtigung für diese Seite');

$htmlstr = "";

if(isset($_GET['searchstr']))
	$searchstr = $_GET['searchstr'];
else 
	$searchstr = '';
		
$htmlstr='
<table width="100%">
<tr>
	<td>
		<form accept-charset="UTF-8" name="search" method="GET" onsubmit="return checkLength();">
	  		BenutzerIn suchen: 
	  		<input type="text" id="searchbox" name="searchstr" size="30" value="'.$searchstr.'" placeholder="Name oder UID eingeben">
	  		<input type="submit" value="Suchen">
	  	</form>
	</td>
</tr>
</table>
	';

//Benutzer suchen und Tabelle anzeigen
if(isset($_GET['searchstr']))
{	
	$benutzer = new benutzer(); 
	$searchItems = explode(' ',$searchstr);
	$benutzer->search($searchItems,"",null);
		
	if(count($benutzer->result)!=0)
	{	
		$htmlstr .= "<table id='t1' class='tablesorter'><thead><tr>\n";
	    $htmlstr .= "<th>Nachname</th><th>Vorname</th><th>UID</th><th>Aktiv</th><th>Aktion</th>";
	    $htmlstr .= "</tr></thead><tbody>\n";

		foreach($benutzer->result as $row)
		{
			$benutzerrolle = new benutzerberechtigung();
			$benutzerrolle->loadBenutzerRollen($row->uid);
			$aktiv = new benutzer(); 
			$aktiv->load($row->uid);
			
			$htmlstr .= "   <tr>\n";
	        $htmlstr .= "       <td>".$row->nachname."</td>\n";
	        $htmlstr .= "       <td>".$row->vorname."</td>\n";
	        $htmlstr .= "       <td>".$row->uid."</td>\n";	   
	        $htmlstr .= "       <td>".($aktiv->bnaktiv?"Ja":"Nein")."</td>\n";	     
	        $htmlstr .= "       <td><a href='benutzerberechtigung_details.php?uid=".$row->uid."' target='vilesci_detail'>".(count($benutzerrolle->berechtigungen)!=0?"Rechte bearbeiten":"Rechte vergeben")."</a></td>\n";
	        $htmlstr .= "   </tr>\n";
		}
		$htmlstr .= "</tbody></table>\n";
	}
	else 
	{
		$htmlstr .= "Es wurden keine Übereinstimmungen mit Ihrem Suchbegriff gefunden";
	}
}

    echo $htmlstr;
?>



</body>
</html>
