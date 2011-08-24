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

	$sprache = getSprache();
	$p = new phrasen($sprache);
	$sprache_obj = new sprache();
	$sprache_obj->load($sprache);
	$sprache_index=$sprache_obj->index;
	
	$uid = get_uid();

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

	// Link fuer den Export
	$export_link='zeitsperre_export.php?';
	$export_param='';
	
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

	$days=trim((isset($_REQUEST['days']) && is_numeric($_REQUEST['days'])?$_REQUEST['days']:14));

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
		if (is_null($funktion))
			$mitarbeiter=$ma->getMitarbeiter($lektor,$fix);
		else
			$mitarbeiter=$ma->getMitarbeiterStg(true,null,$stge,$funktion);
	}

echo '
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
			echo '</SELECT><input style="display:none;" type="Text" name="days" value="'.$days.'"><input type="submit" value="'.$p->t('global/anzeigen').'"></FORM>';
			echo '<br>';
		}
	echo '
	<a class="Item" href="'.$export_link.'">Excel</a>
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
		?>
	</TR>

	<?php
	$zs=new zeitsperre();
	if(is_array($mitarbeiter))
	{
		foreach ($mitarbeiter as $ma)
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
					$erbk=$zs->getErreichbarkeit($ts);
					echo '<td '.$class.' style="white-space: nowrap;">'.$grund.'<br>'.$erbk.'</td>';
				}
				echo '</tr>';
			}
		}
	}
	?>

  </TABLE>
</body>
</html>