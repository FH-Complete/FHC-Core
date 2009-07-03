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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

	require_once('../../../config/cis.config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiensemester.class.php');
	require_once('../../../include/konto.class.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/datum.class.php');
	require_once('../../../include/studiengang.class.php');

	$uid=get_uid();
	$datum_obj = new datum();

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<title>Zahlungen</title>
				<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
			</head>
			<body id="inhalt">';

	$studiengang = new studiengang();
	$studiengang->getAll();
	
	$stg_arr = array();
	foreach ($studiengang->result as $row)
		$stg_arr[$row->studiengang_kz]=$row->kuerzel;
	
	$benutzer = new benutzer();
	if(!$benutzer->load($uid))
		die('Benutzer wurde nicht gefunden');
	
	echo '<table class="tabcontent">
  			<tr>
    			<td class="tdwidth10">&nbsp;</td>
    			<td>
    				<table class="tabcontent">
	      				<tr>
	        				<td width ="690" class="ContentHeader"><font class="ContentHeader">Zahlungen von '.$benutzer->vorname.' '.$benutzer->nachname.'</font></td>
	      				</tr>
	      			</table>';
		
	$konto = new konto();
	$konto->getBuchungstyp();
	$buchungstyp = array();
	
	foreach ($konto->result as $row)
		$buchungstyp[$row->buchungstyp_kurzbz]=$row->beschreibung;
	
	$konto = new konto();
	$konto->getBuchungen($benutzer->person_id);
	if(count($konto->result)>0)
	{
		echo '<br><br><table>';
		echo '<tr class="liste">';
		echo '<td>Datum</td><td>Typ</td><td>Stg</td><td>Studiensemester</td><td>Buchungstext</td><td>Betrag</td><td>Zahlungsbestätigung</td>';
		echo '</tr>';
		$i=0;
		foreach ($konto->result as $row)
		{
			$i++;
			$betrag = $row['parent']->betrag;
			
			if(isset($row['childs']))
			{
				foreach ($row['childs'] as $row_child)
				{
					$betrag += $row_child->betrag;
				}
			}
			
			if($betrag<0)
				$style='style="background-color: #FF8888;"';
			elseif($betrag>0)
				$style='style="background-color: #88DD88;"';
			else 
			{
				$style='class="liste'.($i%2).'"';
			}
			
			echo "<tr $style>";
			echo '<td>'.date('d.m.Y',$datum_obj->mktime_fromdate($row['parent']->buchungsdatum)).'</td>';
			echo '<td>'.$buchungstyp[$row['parent']->buchungstyp_kurzbz].'</td>';
			echo '<td>'.$stg_arr[$row['parent']->studiengang_kz].'</td>';
			echo '<td>'.$row['parent']->studiensemester_kurzbz.'</td>';			
			
			echo '<td>'.$row['parent']->buchungstext.'</td>';
			echo '<td align="right">'.($betrag<0?'-':($betrag>0?'+':'')).sprintf('%.2f',abs($row['parent']->betrag)).' €</td>';
			echo '<td align="center">';
			if($betrag==0 && $row['parent']->betrag<0)
				echo '<a href="../pdfExport.php?xml=konto.rdf.php&xsl=Zahlung&uid='.$uid.'&buchungsnummern='.$row['parent']->buchungsnr.'" title="Bestaetigung drucken"><img src="../../../skin/images/pdfpic.gif" alt="Bestaetigung drucken"></a>';
			elseif($row['parent']->betrag>0)
			{
				//Auszahlung
			}
			else
				echo 'offen';
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	else 
	{
		echo 'Derzeit sind keine Zahlungen vorhanden';
	}	
	echo '</td></tr></table';
	
	echo '</body></html>';	   	
?>
