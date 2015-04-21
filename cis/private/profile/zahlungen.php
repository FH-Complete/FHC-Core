<?php
/* Copyright (C) 2006 fhcomplete.org
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
	require_once('../../../include/phrasen.class.php');
	
	$sprache = getSprache();
	$p = new phrasen($sprache);
	$uid=get_uid();
	$datum_obj = new datum();

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<title>'.$p->t('tools/zahlungen').'</title>
				<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
			</head>
			<body>';

	$studiengang = new studiengang();
	$studiengang->getAll(null,null);
	
	$stg_arr = array();
	foreach ($studiengang->result as $row)
		$stg_arr[$row->studiengang_kz]=$row->kuerzel;
	
	$benutzer = new benutzer();
	if(!$benutzer->load($uid))
		die('Benutzer wurde nicht gefunden');
	
	echo '<h1>'.$p->t('tools/zahlungen').' - '.$benutzer->vorname.' '.$benutzer->nachname.'</h1>';
		
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
		echo '
			<td>'.$p->t('global/datum').'</td>
			<td>'.$p->t('tools/zahlungstyp').'</td>
			<td>'.$p->t('lvplan/stg').'</td>
			<td>'.$p->t('global/studiensemester').'</td>
			<td>'.$p->t('tools/buchungstext').'</td>
			<td>'.$p->t('tools/betrag').'</td>
			<td>'.$p->t('tools/zahlungsbestaetigung').'</td>';	
	echo '</tr>';
		$i=0;
		foreach ($konto->result as $row)
		{
			$i++;

			if(!isset($row['parent']))
				continue;
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
				echo '<a href="../pdfExport.php?xml=konto.rdf.php&xsl=Zahlung&uid='.$uid.'&buchungsnummern='.$row['parent']->buchungsnr.'" title="'.$p->t('tools/bestaetigungDrucken').'"><img src="../../../skin/images/pdfpic.gif" alt="'.$p->t('tools/bestaetigungDrucken').'"></a>';
			elseif($row['parent']->betrag>0)
			{
				//Auszahlung
			}
			else 
			{
			{
				echo '<a onclick="window.open(';
				echo "'zahlungen_details.php?buchungsnr=".$row['parent']->buchungsnr."','Zahlungsdetails','height=500,width=550,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=no,toolbar=no,location=no,menubar=no,dependent=yes');return false;";
				echo '" href="#">'.$p->t('tools/offen').'</a>';
			}
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}
	else 
	{
		echo $p->t('tools/keineZahlungenVorhanden');
	}	
	echo '</td></tr></table';
	
	echo '</body></html>';	   	
?>
