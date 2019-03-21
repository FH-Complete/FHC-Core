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
/**
 * Uebersicht der Zeitsperren der Mitarbeiter
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
require_once('../../../include/fachbereich.class.php');
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/sprache.class.php');
require_once('../../../include/ferien.class.php');
require_once('../../../include/Excel/excel.php');
require_once('../../../include/benutzerberechtigung.class.php');

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

	if(isset($_GET['lektor']))
		$lektor=$_GET['lektor'];
	else
		$lektor=null;
	if ($lektor=='false') $lektor=false;
	if ($lektor=='true' || $lektor=='1') $lektor=true;
	
	if(isset($_GET['fix']))
		$fix=$_GET['fix'];
	else
		$fix=null;
	if ($fix=='false') $fix=false;
	if ($fix=='true' || $fix=='1') $fix=true;

	if(isset($_GET['funktion']))
		$funktion=$_GET['funktion'];
	else
		$funktion=null;

	if(isset($_GET['organisationseinheit']))
		$organisationseinheit = $_GET['organisationseinheit'];
	else
		$organisationseinheit = null;

	$stge=array();
	if(isset($_GET['stg_kz']))
	{
		$stg_kz=$_GET['stg_kz'];
		$stge[]=$stg_kz;
	}
	
	$days=trim((isset($_REQUEST['days']) && is_numeric($_REQUEST['days'])?$_REQUEST['days']:14));

	// Link fuer den Export
	$export_link='zeitsperre.php?format=xls&';
	$export_param='';
	
	if(!is_null($days))
		$export_param.=($export_param!=''?'&':'')."days=$days";
	if(!is_null($organisationseinheit))
		$export_param.=($export_param!=''?'&':'')."organisationseinheit=$organisationseinheit";
	else
	{
		if ($fix)
			$export_param.=($export_param!=''?'&':'').'fix=true';
		if($lektor)
			$export_param.=($export_param!=''?'&':'').'lektor=true';
		
		if(!is_null($funktion))
			$export_param.=($export_param!=''?'&':'').'funktion='.$funktion;
		if(isset($stg_kz))
			$export_param.=($export_param!=''?'&':'').'stg_kz='.$stg_kz;
		
	}
	$export_link.=$export_param;

	//Datumsbereich ermitteln
	$datum_obj = new datum();

	$dTmpAktuellerMontag=date("Y-m-d",strtotime(date('Y')."W".date('W')."1")); // Montag der Aktuellen Woche
	$dTmpAktuellesDatum=explode("-",$dTmpAktuellerMontag);
	$dTmpMontagPlus=date("Y-m-d", mktime(0,0,0,date($dTmpAktuellesDatum[1]),date($dTmpAktuellesDatum[2])+$days,date($dTmpAktuellesDatum[0])));

	$datum_beginn=$dTmpAktuellerMontag; 
	$datum_ende=$dTmpMontagPlus;

	$ts_beginn=$datum_obj->mktime_fromdate($datum_beginn);
	$ts_ende=$datum_obj->mktime_fromdate($datum_ende);

	// Mitarbeiter laden
	$ma=new mitarbeiter();

	if(!is_null($organisationseinheit))
	{
		$mitarbeiter = $ma->getMitarbeiterOrganisationseinheit($organisationseinheit);
	}
	else
	{
		if (!is_null($lektor))
			$mitarbeiter=$lektor;
		else
		{						
			if (is_null($funktion))
				$mitarbeiter=$ma->getMitarbeiter($lektor,$fix);
			else
				$mitarbeiter=$ma->getMitarbeiterStg(null,null,$stge,$funktion,'nachname,vorname');
		}
	}

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
	$uid='';
	$zs=new zeitsperre();
	if(is_array($mitarbeiter))
	{
		foreach ($mitarbeiter as $ma)
		{
			if ($ma->uid!=$uid) //Um doppelte Eintraege rauszufiltern
			{
				if($ma->aktiv)
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
				$uid=$ma->uid;
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
{	echo '
	<html>
	<head>
		<title>'.$p->t('zeitsperre/zeitsperren').'</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	</head>
	<body>
		<h1>'.$p->t('zeitsperre/zeitsperren').'</h1>
	
		<H3>'.$p->T('zeitsperre/zeitsperreVonBis',array($datum_beginn, $datum_ende)).'</H3>';
		
			if(isset($_GET['organisationseinheit']))
			{
				echo '<br>';
				echo '<FORM action="'.$_SERVER['PHP_SELF'].'" method="GET">'.$p->t('global/organisationseinheit').': <SELECT name="organisationseinheit">';
				$oe_obj = new organisationseinheit();
				$oe_obj->getAll();
	
				echo "<option value='' ".(is_null($organisationseinheit)?'selected':'').">-- ".$p->t('global/auswahl')." --</option>";
				foreach ($oe_obj->result as $oe)
				{
					if($oe->aktiv)
					{
						if($oe->oe_kurzbz==$organisationseinheit)
							$selected='selected';
						else
							$selected='';
	
						echo "<option value='$oe->oe_kurzbz' $selected>$oe->organisationseinheittyp_kurzbz $oe->bezeichnung</option>";
					}
				}
				echo '</SELECT>&nbsp;'.$p->t('zeitsperre/anzahlTage').'<input type="Text" name="days" size="2" maxlength="2" value="'.$days.'"><input type="submit" value="'.$p->t('global/anzeigen').'"></FORM>';
				echo '<br>';
			}
			if($lektor)
				echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'.$p->t('zeitsperre/anzahlTage').' <input type="text" name="days" size="2" maxlength="2" value="'.$days.'"><input type="hidden" name="lektor" value="true"><input type="submit" value="Go"></form>';
			elseif($fix)
				echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'.$p->t('zeitsperre/anzahlTage').' <input type="text" name="days" size="2" maxlength="2" value="'.$days.'"><input type="hidden" name="fix" value="true"><input type="submit" value="Go"></form>';
			elseif(isset($_GET['funktion']) && isset($_GET['stg_kz']))
				echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'.$p->t('zeitsperre/anzahlTage').' <input type="text" name="days" size="2" maxlength="2" value="'.$days.'"><input type="hidden" name="funktion" value="'.$funktion.'"><input type="hidden" name="stg_kz" value="'.$stg_kz.'"><input type="submit" value="Go"></form>';
		echo '
		<a href="'.$export_link.'"><img src="../../../skin/images/xls_icon.png" alt="Icon Excel"> Excel Export</a><br>
		<TABLE id="zeitsperren">
	    <TR>';
	    	
		  	echo '<th>'.$p->t('zeitsperre/monat').'<br>'.$p->t('zeitsperre/tag').'</th>';
			for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
			{
				$tag=date('d',$ts);
				$wt=date('N',$ts);
				$monat=date('M',$ts);
				if ($wt==7 || $wt==6)
					$class='feiertag';
				else
					$class='';
				echo "<th class='$class'><div align=\"center\">".$tagbez[$sprache_index][$wt]."<br>$monat<br>$tag</div></th>";
			}

		echo '</TR>';
	
		$uid='';
		$zs=new zeitsperre();
		if(is_array($mitarbeiter))
		{
			foreach ($mitarbeiter as $ma)
			{
				if ($ma->uid!=$uid) //Um doppelte Eintraege rauszufiltern
				{
					if($ma->aktiv)
					{
						$zs->getzeitsperren($ma->uid, false);
						echo '<tr>';
						echo '<td valign="top">'.trim($ma->nachname).'&nbsp;'.trim($ma->vorname).'</td>';
						for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
						{
							$tag=date('d',$ts);
							$monat=date('M',$ts);
							$wt=date('N',$ts);
				
							if ($wt==7 || $wt==6)
								$class=' class="feiertag" ';
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
							echo '</td>';
						}
						echo '</tr>';
					}
					$uid=$ma->uid;
			}
			}
		}
	
	  echo '</TABLE></body></html>';
}
?>