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
/*
 * Erstelle eine Liste mit allen Personen die innerhalb der naechsten 2
 * Wochen eine Zeitsperre eingetragen haben
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/zeitsperre.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/sprache.class.php');
require_once('../../../include/Excel/excel.php');
require_once('../../../include/benutzerberechtigung.class.php');

$datum_obj = new datum();
$sprache = getSprache();
$p = new phrasen($sprache);
$sprache_obj = new sprache();
$sprache_obj->load($sprache);
$sprache_index=$sprache_obj->index;
$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
	
if(!check_lektor($uid) && (!$rechte->isBerechtigt('admin',0) && !$rechte->isBerechtigt('mitarbeiter')))
	die($p->t('global/keineBerechtigung'));

$days=trim((isset($_REQUEST['days']) && is_numeric($_REQUEST['days'])?$_REQUEST['days']:14));

if(isset($_REQUEST['lektor']))
		$lektor=$_REQUEST['lektor'];
	else
		$lektor=null;
		
// Link fuer den Export
$export_link='zeitsperre_days.php?format=xls&';
$export_param='';

if(!is_null($days))
	$export_param.=($export_param!=''?'&':'')."days=$days";
if(!is_null($lektor))
	$export_param.=($export_param!=''?'&':'')."lektor=$lektor";

$export_link.=$export_param;
	
$datum_beginn=date('Y-m-d');
$ts_beginn=$datum_obj->mktime_fromdate($datum_beginn);
$ts_ende=$datum_obj->jump_day($ts_beginn,$days);
$datum_ende=date('Y-m-d',$ts_ende);

// Lektoren holen
$ma=new mitarbeiter();
$mitarbeiter=$ma->getMitarbeiterZeitsperre($datum_beginn,$datum_ende,$lektor);

if(isset($_REQUEST['format']) && $_REQUEST['format']=='xls')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send($p->t('zeitsperre/zeitsperren').".xls");
	$workbook->setVersion(8);
	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet($p->t('zeitsperre/zeitsperren'));
	$worksheet->setInputEncoding('utf-8');
	$worksheet->setZoom (85);
	$worksheet->freezePanes(array(1, 0, 1, 0));
	//Formate Definieren
	$format_header =& $workbook->addFormat();
	$format_header->setBold();
	$format_header->setAlign("center");
	$format_header->setVAlign('vcenter');
	$format_header->setTextWrap();
	
	$format_header_feiertag =& $workbook->addFormat();
	$format_header_feiertag->setBold();
	$format_header_feiertag->setAlign("center");
	$format_header_feiertag->setVAlign('vcenter');
	$format_header_feiertag->setFgColor('yellow');
	$format_header_feiertag->setTextWrap();
	
	$format_namen =& $workbook->addFormat();
	$format_namen->setVAlign('top');
	
	$format_legende =& $workbook->addFormat();
	$format_legende->setBold();
	$format_legende->setAlign("left");
	$format_legende->setVAlign('vcenter');
	
	$format_mehrzeilig = &$workbook->addFormat(array('Align' => 'left')); 
	$format_mehrzeilig->setVAlign('top');
	$format_mehrzeilig->setTextWrap();

	$spalte=0;
	$zeile=0;
	
	$worksheet->write(0,$spalte,$p->t('global/datum'), $format_header);
	$maxlength[0]=20;
	for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
		{
			$tag=date('d',$ts);
			$wt=date('N',$ts);
			$monat=date('M',$ts);
			if ($wt==7 || $wt==6)
				$class='feiertag';
			else
				$class='';
			$datum = $tagbez[$sprache_index][$wt]. "\n";
			$datum .= $tag.' '.$monat;
			$worksheet->write(0,++$spalte,$datum, ($class!=''?$format_header_feiertag:$format_header));
			$maxlength[$spalte]=15;
		}

	$zeile=0;
	$spalte=0;
	$zs=new zeitsperre();
	if (!empty ($mitarbeiter))
	{
		foreach ($mitarbeiter as $ma)
		{
			$zeile++;
			$spalte=0;
			$zs->getzeitsperren($ma->uid, false);
			$worksheet->write($zeile,$spalte,$ma->nachname.' '.$ma->vorname, $format_namen);
			for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
			{
				$wt=date('N',$ts);
				$grund=$zs->getTyp($ts);
				$grund = (!empty($grund)) ? $p->t('zeitsperre/abwesend') : '';	// anonymize reason
				$erbk=html_entity_decode($zs->getErreichbarkeit($ts));
				$vertretung=$zs->getVertretung($ts);
				$zelleninhalt = ($grund!=''?(($grund!=''?substr($p->t('zeitsperre/grund'),0,1).': ':'').$grund. "\n"):'');
				$zelleninhalt .= ($erbk!=''?(($erbk!=''?substr($p->t('urlaubstool/erreichbarkeit'),0,1).': ':'').$erbk. "\n"):'');
				$zelleninhalt .= ($erbk!=''?($erbk!=''?substr($p->t('urlaubstool/vertretung'),0,1).': ':''):''); 
				$count = 0;
				foreach ($vertretung as $vt)
				{
					if ($vt!='')
					{
						$ma_kurzbz = new mitarbeiter();
						$ma_kurzbz->load($vt);
						$zelleninhalt .= ($count!=0?', ':'').$ma_kurzbz->vorname.' '.$ma_kurzbz->nachname.' ('.$ma_kurzbz->telefonklappe.')';
						$count++;
					}
				}
				$worksheet->write($zeile,++$spalte,$zelleninhalt,$format_mehrzeilig);
			}
		}
	}
	++$zeile;
	$worksheet->write(++$zeile,0,$p->t('zeitsperre/legendeGrund'), $format_legende);
	$worksheet->write(++$zeile,0,$p->t('zeitsperre/legendeErreichbarkeit'), $format_legende);
	$worksheet->write(++$zeile,0,$p->t('zeitsperre/legendeVertretung'), $format_legende);
	$worksheet->write(++$zeile,0,$p->t('zeitsperre/legendeDurchwahl'), $format_legende);
						
	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite);
	
	$workbook->close();
}
else
{
	echo '
	<html>
	<head>
		<title>'.$p->t('zeitsperre/zeitsperren').'</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	</head>
	
	<body>
		<H1>'.$p->t('zeitsperre/zeitsperren').'</H1>
	 
		<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'.$p->t('zeitsperre/anzahlTage').' <input type="text" name="days" size="2" maxlength="2" value="'.$days.'"><input type="hidden" name="lektor" value="'.$lektor.'"><input type="submit" value="Go"></form>
		<H2>'.$p->t('zeitsperre/zeitsperreVonBis',array($datum_beginn, $datum_ende)).'</H2>
		<a href="'.$export_link.'"><img src="../../../skin/images/xls_icon.png" alt="Icon Excel"> Excel Export</a><br>
		<TABLE id="zeitsperren">
	    <TR>';
	    	
	echo '<th>'.$p->t('zeitsperre/monat').'<br>'.$p->t('zeitsperre/tag').'</th>';
	for ($ts=$ts_beginn;$ts<=$ts_ende; $ts+=$datum_obj->ts_day)
	{
		$tag=date('d',$ts);
		$wt=date('N',$ts);
		$monat=date('M',$ts);
		
		if ($wt==6 || $wt==7)
			$class='feiertag';
		else
			$class='';
					
		echo "<th class='$class'><div align=\"center\">".$tagbez[$sprache_index][$wt]."<BR>$monat<br>$tag</div></th>";
	}
	
	echo '</TR>';
	$zs=new zeitsperre();
	if (!empty ($mitarbeiter))
	{
		foreach ($mitarbeiter as $ma)
		{
			$zs->getzeitsperren($ma->uid);
			echo '<TR>';
			echo "<td>$ma->nachname $ma->vorname</td>";
			for ($ts=$ts_beginn;$ts<=$ts_ende; $ts+=$datum_obj->ts_day)
			{
				$tag=date('d',$ts);
				$monat=date('M',$ts);
				$wt=date('N',$ts);
				if ($wt==6 || $wt==7)
					$class='feiertag';
				else
					$class='';
				$grund=$zs->getTyp($ts);
				$grund = (!empty($grund)) ? $p->t('zeitsperre/abwesend') : '';	// anonymize reason
				$erbk=$zs->getErreichbarkeit($ts);
				$vertretung=$zs->getVertretung($ts);
				echo '<td '.$class.' style="white-space: nowrap;">'.($grund!=''?'<span title="'.$p->t('zeitsperre/grund').'">'.substr($p->t('zeitsperre/grund'),0,1).'</span>: ':'').$grund;
				echo '<br>'.($erbk!=''?'<span title="'.$p->t('urlaubstool/erreichbarkeit').'">'.substr($p->t('urlaubstool/erreichbarkeit'),0,1).'</span>: ':'').$erbk;
				echo '<br>'.($erbk!=''?'<span title="'.$p->t('urlaubstool/vertretung').'">'.substr($p->t('urlaubstool/vertretung'),0,1).'</span>: ':'');
				foreach ($vertretung as $vt)
				{
					if ($vt!='')
					{
						$ma_kurzbz = new mitarbeiter();
						$ma_kurzbz->load($vt);
						echo '<a href="index.php?uid='.$ma_kurzbz->uid.'">'.$ma_kurzbz->kurzbz.'</a>&nbsp;';
					}
				}
			}
			echo '</TR>';
		}
	}
	else
	{
		$ma=new mitarbeiter($lektor);
		echo '<TR>';
		echo "<td>$ma->nachname $ma->vorname</td>";
		for ($ts=$ts_beginn;$ts<=$ts_ende; $ts+=$datum_obj->ts_day)
		{
			$tag=date('d',$ts);
			$monat=date('M',$ts);
			$wt=date('N',$ts);
			if ($wt==6 || $wt==7)
				$class='feiertag';
			else
				$class='';
			echo "<td class='$class'>&nbsp;</td>";
		}
		echo '</TR>';
	}
	
	echo '  </TABLE>
		</body>
	</html>';
}
?>