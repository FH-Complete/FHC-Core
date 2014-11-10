<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
/**
 * Statistik Uebersichtsseite
 * - zeigt die Beschreibung einer Statistik ein
 * - Link zum Starten der Statistik
 * - Eventuelle Parametereingabe für die Statistik 
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/statistik.class.php');
require_once('../../include/filter.class.php');
require_once('../../include/functions.inc.php');

if(!isset($_GET['statistik_kurzbz']))
	die('Statistik_kurzbz Parameter fehlt');

$statistik_kurzbz = $_GET['statistik_kurzbz'];

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Statistik</title>	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css"/>
</head>
<body>';

$statistik = new statistik();
if(!$statistik->load($statistik_kurzbz))
	die($statistik->errormsg);

echo '<h2>Report - '.$statistik->bezeichnung.'</h2>';

//Beschreibung zu der Statistik anzeigen
if($statistik->content_id!='')
{
	echo "\n",'<a href="#" onclick="window.open(\'../../cms/content.php?content_id='.$statistik->content_id.'\', \'Beschreibung\', \'width=600,height=600, scrollbars=yes\');">Beschreibung anzeigen</a><br><br>';
}
$variablenstring='';
$action='';
if($statistik->url!='')
{
	$action = $statistik->url;
	$variablenstring = $statistik->url;
}
elseif($statistik->sql!='')
{
	$action = 'statistik_sql.php?statistik_kurzbz='.$statistik_kurzbz;
	$variablenstring = $statistik->sql;
}

$vars = $statistik->parseVars($variablenstring);
//var_dump($vars);
echo '
<script type="text/javascript">
function doit()
{';
	if($statistik->url!='')
	{
		echo 'var action=\''.$action.'\';
		';
	
		foreach ($vars as $var)
		{
			echo 'action = action.replace(\'$'.$var.'\', document.getElementById(\''.$var.'\').value);';
		}
		echo '
		parent.detail_statistik.location.href=action;
		return false;';
	}
	else
		echo 'return true;';
echo '
}
</script>
<form action="'.$action.'" method="POST" target="detail_statistik" onsubmit="return doit();">
	<table>
';
// Filter parsen
$fltr=new filter();
$fltr->loadAll();
echo '<tr>';
foreach($vars as $var)
{
	if ($fltr->isFilter($var))
		echo "<td>$var</td><td>".$fltr->getHtmlWidget($var)."</td>\n";
	else
		echo "<td>$var</td><td><input type=\"text\" id=\"$var\" name=\"$var\" value=\"\"></td>";
}
echo '</tr>';
echo '
	<tr>
		<td></td>
		<td><input type="submit" value="Anzeigen"></td>
	</tr>
	</table>
</form>';

echo '</body>
</html>';


?>
