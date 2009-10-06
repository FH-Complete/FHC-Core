<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/**
 * Stellt die Abhaengigkeiten Organisationseinheiten grafisch dar.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/organisationseinheit.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Organisationseinheiten - Übersicht</title>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<meta http-equiv="content-type" content="text/html" charset="UTF-8" />
</head>
<body class="Background_main">
	<h2>Organisationseinheiten - Übersicht</h2><br />';

//Benutzerdefiniert Sortierfunktion damit die Eintraege mit 
//Kindelementen nach oben sortiert werden
function mysort($a, $b)
{
	if(is_array($a) && is_array($b))
	{
		if(count($a)==count($b))
			return 0;
			
		if(count($a)>count($b))
			return -1;
		else 
			return 1;
	}
	else
	{
		if(is_array($a))
			return 1;
		if(is_array($b)) 
			return -1;
		else 
			return 0;
	}
}

//Alle obersten Organisationseinheiten holen
$oe = new organisationseinheit();
$oe->getHeads();

foreach ($oe->result as $result)
{
	$arr = array();
	//Array mit den Kindelementen erzeugen
	$arr = getChilds($result->oe_kurzbz);
	
	//Sortieren damit die Eintraege mit Kindern weiter oben stehen
	uasort($arr,'mysort');
	
	//Parent hinzufuegen
	$arr1[$result->oe_kurzbz] = $arr;
	
	//Anzeigen
	displayh($arr1);
}

//Liefert die Kindelemente einer Organisationseinheit in 
//einem verschachteltem Array zurueck
function getChilds($foo)
{
	$obj = new organisationseinheit();
	$arr = array();
	$arr1 = $obj->getDirectChilds($foo);
	foreach ($arr1 as $value)
		$arr[$value]=array();
	
	foreach ($arr as $val=>$k) 
	{
		$hlp = getChilds($val); 
		$arr[$val] = $hlp;
	}
	
	return $arr;
}

//Zeigt das Array in einer Verschachtelten Tabelle an
function displayh($arr)
{
	echo '<table style="text-align: left; padding:0;" cellspacing=0 cellpadding=0>';
	foreach ($arr as $key=>$val) 
	{
		//wenn noch Kindelemente dranhaengen dann einen Rahmen zeichen, sonst nicht
		if(is_array($val) && count($val)>0)
			$style = 'style="border: 1px solid gray; font-weight:bold; padding-right: 10px;padding-left: 10px; margin:0;"';
		else 
			$style = 'style="padding-left: 10px;padding-right: 10px;"';
		
		$obj = new organisationseinheit();
		$obj->load($key);
		
		if($obj->aktiv)
		{	
			echo '<tr><td valign="center" '.$style.'>';
		
			echo $obj->organisationseinheittyp_kurzbz.' - ';
			if($obj->organisationseinheittyp_kurzbz=='Institut')
				echo $obj->oe_kurzbz;
			else
				echo $obj->bezeichnung;
			echo '</td>';	
			$style = 'style="border: 1px solid gray; font-weight:bold; padding: 0px; margin:0;"';
			echo '<td valign="center" '.$style.'>';
			
			if(is_array($val) && count($val)>0)
				displayh($val);
				
			echo '</td></tr>';
		}
	}
	echo '</table>';
}
echo '</body></html>';
?>