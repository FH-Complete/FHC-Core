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
	require_once('../../../include/benutzerberechtigung.class.php');
	
	$sprache = getSprache();
	$p = new phrasen($sprache);
	$uid=get_uid();
	
	if(isset($_GET['uid']))
	{
		// Administratoren duerfen die UID als Parameter uebergeben um die Zahlungen
		// von anderen Personen anzuzeigen
	
		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($uid);
		if($rechte->isBerechtigt('admin'))
		{
			$uid = $_GET['uid'];
			$getParam = "&uid=" . $uid;
		}
		else
			$getParam = "";
	}
	else
		$getParam='';
	
	$datum_obj = new datum();

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<title>'.$p->t('tools/zahlungen').'</title>
				<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
				<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
				<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
			</head>
			<style>
				table.tablesorter
				{
					width: auto;
				}
			</style>
			<script type="text/javascript">
				$(document).ready(function() 
				{ 
					$("#t1").tablesorter(
					{
						sortList: [[0,0],[1,0]],
						widgets: ["zebra"]
					}); 
				});
			</script>
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
		echo '<br><br><table class="tablesorter" id="t1"><thead>';
		echo '<tr>';
		echo '
			<th>'.$p->t('global/datum').'</th>
			<th>'.$p->t('tools/zahlungstyp').'</th>
			<th>'.$p->t('lvplan/stg').'</th>
			<th>'.$p->t('global/studiensemester').'</th>
			<th>'.$p->t('tools/buchungstext').'</th>
			<th>'.$p->t('tools/betrag').'</th>
			<th>'.$p->t('tools/zahlungsbestaetigung').'</th>';	
		echo '</tr></thead><tbody>';
		
		foreach ($konto->result as $row)
		{
			$i=0;  //Zaehler fuer Anzahl Gegenbuchungen
			$buchungsnummern='';
			
			if(!isset($row['parent']))
				continue;
			$betrag = $row['parent']->betrag;

			
			if(isset($row['childs']))
			{
				foreach ($row['childs'] as $key => $row_child)
				{
					$betrag += $row_child->betrag;
					$betrag = round($betrag, 2);
					$buchungsnummern .= ';'.$row['childs'][$key]->buchungsnr;
					$i = $key; //Zaehler auf letzten Gegenbuchungseintrag setzen
				}
			}
			else 
				$buchungsnummern = $row['parent']->buchungsnr;
			
			if($betrag<0)
				$style='style="background-color: #FF8888;"';
			elseif($betrag>0)
				$style='style="background-color: #88DD88;"';
			else 
				$style='';

			echo "<tr>";
			echo '<td '.$style.'>'.date('d.m.Y',$datum_obj->mktime_fromdate(isset($row['childs'][$i])?$row['childs'][$i]->buchungsdatum:$row['parent']->buchungsdatum)).'</td>';
			echo '<td '.$style.'>'.$buchungstyp[$row['parent']->buchungstyp_kurzbz].'</td>';
			echo '<td '.$style.'>'.$stg_arr[$row['parent']->studiengang_kz].'</td>';
			echo '<td '.$style.'>'.$row['parent']->studiensemester_kurzbz.'</td>';			
			
			echo '<td '.$style.'>'.$row['parent']->buchungstext.'</td>';
			echo '<td align="right" '.$style.'>'.($betrag<0?'-':($betrag>0?'+':'')).sprintf('%.2f',abs($row['parent']->betrag)).' €</td>';
			echo '<td align="center" '.$style.'>';
			if($betrag>=0 && $row['parent']->betrag<=0)
				echo '<a href="../pdfExport.php?xml=konto.rdf.php&xsl=Zahlung&uid='.$uid.'&buchungsnummern='.$buchungsnummern.'" title="'.$p->t('tools/bestaetigungDrucken').'"><img src="../../../skin/images/pdfpic.gif" alt="'.$p->t('tools/bestaetigungDrucken').'"></a>';
			elseif($row['parent']->betrag>0)
			{
				//Auszahlung
			}
			else 
			{
				echo '<a onclick="window.open(';
				echo "'zahlungen_details.php?buchungsnr=".$row['parent']->buchungsnr.$getParam."','Zahlungsdetails','height=500,width=550,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=no,toolbar=no,location=no,menubar=no,dependent=yes');return false;";
				echo '" href="#">'.$p->t('tools/offen').'</a> ('.sprintf('%.2f',$betrag*-1).' €)';
			
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
	else 
	{
		echo $p->t('tools/keineZahlungenVorhanden');
	}	
	echo '</td></tr></table';
	
	echo '</body></html>';	   	
?>
