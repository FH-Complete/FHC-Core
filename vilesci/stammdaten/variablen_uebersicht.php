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

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	    
if (!$user = get_uid())
		die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
		
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/variable'))
		die('Sie haben keine Berechtigung fuer diese Seite. !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

if(isset($_GET['searchstr']))
	$searchstr = $_GET['searchstr'];
else 
	$searchstr = '';
	
$htmlstr = "";	
	
$htmlstr.='
<table width="100%">
<tr>
	<td>
		<form accept-charset="UTF-8" name="search" method="GET">
			Bitte Suchbegriff eingeben: 
			<input type="text" name="searchstr" size="30" value="'.$searchstr.'">
			<input type="submit" value="Suchen">
		</form>
	</td>
	<td align="right">
		<form name="neuform" action="variablen_details.php" target="vilesci_detail">
			UID <input type="text" value="" name="uid">&nbsp;
			<input type="submit" name="neuschick" value="anlegen">
		</form>
	</td>
</tr>
</table>
';	

if(isset($_GET['searchstr']))
{	
	$sql_query = "SELECT 
					distinct(tbl_variable.uid), tbl_person.nachname, tbl_person.vorname 
				  FROM 
				  	public.tbl_variable, public.tbl_benutzer, public.tbl_person 
				  WHERE 
				  	tbl_variable.uid = tbl_benutzer.uid AND
				  	tbl_benutzer.person_id = tbl_person.person_id 
				and (nachname ~* '".addslashes($searchstr)."' OR 
				vorname ~* '".addslashes($searchstr)."' OR
				alias ~* '".addslashes($searchstr)."' OR
				nachname || ' ' || vorname = '".addslashes($searchstr)."' OR 
				vorname || ' ' || nachname = '".addslashes($searchstr)."' OR 
				tbl_variable.uid ~* '".addslashes($searchstr)."')
									
				  ORDER BY 
				  	nachname";
	
    if(!$erg=$db->db_query($sql_query))
	{
			$htmlstr='Fehler beim Laden der Berechtigungen';
	}	
	else
	{
		$htmlstr .= "<table id='t1' class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'><thead><tr class='liste'>\n";
	  $htmlstr .= "       <th class='table-sortable:default'>UID</th><th class='table-sortable:default'>Vorname</th><th class='table-sortable:alphanumeric'>Nachname</th>";
	  $htmlstr .= "   </tr></thead><tbody>\n";
	  $i = 0;
		while($row=$db->db_fetch_object($erg))
	    {
					$htmlstr .= "   <tr>\n";
	        $htmlstr .= "       <td>".$row->uid."</td>\n";
					$htmlstr .= "       <td>".$row->vorname."</td>\n";
	        $htmlstr .= "       <td><a href='variablen_details.php?uid=".$row->uid."' target='vilesci_detail'>".$row->nachname."</a></td>\n";		
	        $htmlstr .= "   </tr>\n";
	        $i++;
	    }
	    $htmlstr .= "</tbody></table>\n";
	}
}	
?>
<html>
<head>
<title>Variablen Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>

</head>

<body class="background_main">
<h2>Variablen &Uuml;bersicht</h2>

<?php 
    echo $htmlstr;
?>

</body>
</html>
