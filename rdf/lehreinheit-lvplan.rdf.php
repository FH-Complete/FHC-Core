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
 * Created on 02.12.2004
 */
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/notiz.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/zeitaufzeichnung_gd.class.php');
require_once('../include/lehreinheitmitarbeiter.class.php');
require_once('../include/vertrag.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/zeitsperre.class.php');

$uid=get_uid();
$error_msg='';

$error_msg.=loadVariables($uid);

if (isset($semester_aktuell))
	$studiensemester=$semester_aktuell;
else
	echo $error_msg='studiensemester is not set!';
if (isset($_GET['type']))
	$type=$_GET['type'];
else
	$type='lektor';
if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else
	$stg_kz=0;
if (isset($_GET['sem']))
	$sem=$_GET['sem'];
else
	$sem=0;
if (isset($_GET['lektor']))
    $lektor=$_GET['lektor'];
else
	$lektor=$uid;
if (isset($_GET['ver']))
	$ver=$_GET['ver'];
else
	$ver=null;
if (isset($_GET['grp']))
	$grp=$_GET['grp'];
else
	$grp=null;
if (isset($_GET['gruppe']))
	$gruppe_kurzbz=$_GET['gruppe'];
else
	$gruppe_kurzbz=null;

if (isset($_GET['filter']))
	$filter=$_GET['filter'];
else
	$filter=null;
if (isset($_GET['fachbereich_kurzbz']))
	$fachbereich_kurzbz=$_GET['fachbereich_kurzbz'];
else
	$fachbereich_kurzbz=null;

if (isset($_GET['orgform']))
	$orgform=$_GET['orgform'];
else
	$orgform=null;
if (isset($_GET['vertrag']))
	$vertrag=$_GET['vertrag'];
else
	$vertrag=null;

//Sortierreihenfolge
if(isset($_GET['order']))
{
	switch($_GET['order'])
	{
		case 'lektorDESC':
			$order='lektor DESC, offenestunden DESC, lehrfach, lehrform, semester, verband, gruppe, gruppe_kurzbz';
			break;
		case 'lektorASC':
			$order='lektor ASC, offenestunden DESC, lehrfach, lehrform, semester, verband, gruppe, gruppe_kurzbz';
			break;
		case 'lfDESC':
			$order='lehrfach DESC, offenestunden DESC, lehrform, semester, verband, gruppe, gruppe_kurzbz';
			break;
		case 'lfASC':
			$order='lehrfach ASC, offenestunden DESC, lehrform, semester, verband, gruppe, gruppe_kurzbz';
			break;
		case 'stundenDESC':
			$order='offenestunden DESC, lehrfach, lehrform, semester, verband, gruppe, gruppe_kurzbz';
			break;
		case 'stundenASC':
			$order='offenestunden ASC, lehrfach, lehrform, semester, verband, gruppe, gruppe_kurzbz';
			break;
		default:
			$order=null;
			break;
	}
}
else
	$order=null;

// LVA holen
$lva=array();
$lehreinheit=new lehreinheit();
if (!$error_msg)
	if (!$lehreinheit->getLehreinheitLVPL($db_stpl_table,$studiensemester,$type,$stg_kz,$sem,$lektor,$ver,$grp,$gruppe_kurzbz, $order, $fachbereich_kurzbz, $orgform))
		die ('Fehler bei Methode getLehreinheitLVPL(): '.$lehreinheit->errormsg);
$lva=$lehreinheit->lehreinheiten;
$rdf_url='http://www.technikum-wien.at/lehreinheit-lvplan/';

// Positive Zeitsperre 'Zeitverfuegbarkeit' holen
$ss = new Studiensemester($studiensemester);

$zeitsperre = new Zeitsperre();
$zeitsperre->getVonBis($lektor, $ss->start, $ss->ende, 'ZVerfueg');

$zeitverfuegbarkeit = count($zeitsperre->result) > 0 ? 'Zeit verfügbar' : '';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LVA="<?php echo $rdf_url; ?>rdf#">

<RDF:Seq about="<?php echo $rdf_url.'alle'; ?>">

<?php
$anz=count($lva);

if ($anz>0)
{
	foreach ($lva as $l)
	{
		$lva_ids='';
		$lehrverband='';
		$lvnr='';
		$lektor='';
		$gruppe_kurzbz='';
		$i=0;
		// IDs der Lehreinheiten
		$leids = array_unique($l->lehreinheit_id);
		$anzahl_notizen=0;
		foreach($leids as $lva_id)
		{
			$lva_ids.='&amp;lva_id'.$i++.'='.$lva_id;
			$notiz = new notiz();
			$anzahl_notizen+=$notiz->getAnzahlNotizen(null, null, null, null, null, null, null, null, null, $lva_id);
			$lehreinheitids[] = $lva_id;
		}
		// Lektoren
		$lektor='';
		$l->lektor=array_unique($l->lektor);

		$fixangestellt = false;
		foreach ($l->lektor_uid as $lktuid)
		{
			$ma = new mitarbeiter();
			$ma->load($lktuid);
			if ($ma->fixangestellt)
			{
				$fixangestellt = true;
				break;
			}
		}

		$selbstverwaltete_pause = false;
		foreach ($l->lektor_uid as $lktuid)
		{
			$gd = new zeitaufzeichnung_gd();
			if($gd->load($lktuid, $studiensemester))
			{
				if ($gd->selbstverwaltete_pause)
				{
					$selbstverwaltete_pause = true;
					break;
				}
			}
		}

		sort($l->lektor);
		foreach($l->lektor as $lv)
			$lektor.=$lv.' ';
		// Lehrverbaende
		$l->lehrverband=array_unique($l->lehrverband);
		sort($l->lehrverband);
		foreach($l->lehrverband as $lv)
			$lehrverband.=$lv.' ';
		// LVNRs
		foreach($l->lvnr as $lv)
			$lvnr.=$lv.' ';
		foreach($l->gruppe_kurzbz as $lv)
			$gruppe_kurzbz.=$lv.' ';
		// Stundenblockung
		$stundenblockung='';
		$l->stundenblockung=array_unique($l->stundenblockung);
		sort($l->stundenblockung);
		foreach($l->stundenblockung as $sb)
			$stundenblockung.=$sb.' ';
		if (count($l->stundenblockung)>1)
			$stundenblockung.=' ?';
		// Start KW
		$start_kw='';
		$l->start_kw=array_unique($l->start_kw);
		sort($l->start_kw);
		foreach($l->start_kw as $kw)
			$start_kw.=$kw.' ';
		if (count($l->start_kw)>1)
			$start_kw.=' ?';
		// Wochenrythmus
		$wochenrythmus='';
		$l->wochenrythmus=array_unique($l->wochenrythmus);
		sort($l->wochenrythmus);
		foreach($l->wochenrythmus as $wr)
			$wochenrythmus.=$wr.' ';
		if (count($l->wochenrythmus)>1)
			$wochenrythmus.=' ?';
		// Lehrfach
		$lehrfach='';
		$l->lehrfach=array_unique($l->lehrfach);
		sort($l->lehrfach);
		foreach($l->lehrfach as $lf)
			$lehrfach.=$lf.' ';
		if (count($l->lehrfach)>1)
			$lehrfach.=' ?';
		// Lehrform
		$lehrform='';
		$l->lehrform=array_unique($l->lehrform);
		sort($l->lehrform);
		foreach($l->lehrform as $lf)
			$lehrform.=$lf.' ';
		if (count($l->lehrform)>1)
			$lehrform.=' ?';
		// Semesterstunden
		$semesterstunden='';
		$l->semesterstunden=array_unique($l->semesterstunden);
		sort($l->semesterstunden);
		foreach($l->semesterstunden as $lf)
			$semesterstunden.=$lf.' ';
		if (count($l->semesterstunden)>1)
			$semesterstunden.=' ?';

		// Planstunden
		$planstunden='';
		$l->planstunden=array_unique($l->planstunden);
		sort($l->planstunden);
		foreach($l->planstunden as $lf)
			$planstunden.=$lf.' ';
		if (count($l->planstunden)>1)
			$planstunden.=' ?';

		// Verplant
		$verplant='';
		$l->verplant=array_unique($l->verplant);
		sort($l->verplant);
		foreach($l->verplant as $lf)
			$verplant.=$lf.' ';
		if (count($l->verplant)>1)
			$verplant.=' ?';
		// Offene Stunden
		$offenestunden='';
		$l->offenestunden=array_unique($l->offenestunden);
		sort($l->offenestunden);
		foreach($l->offenestunden as $os)
			$offenestunden.=$os.' ';
		if (count($l->offenestunden)>1)
			$offenestunden.=' ?';

		if($filter!='')
		{
			$filter = mb_strtolower($filter);
			if(!mb_strstr(mb_strtolower($lektor), $filter) &&
			   !mb_strstr(mb_strtolower($lehrfach), $filter) &&
			   !mb_strstr(mb_strtolower($l->lehrfach_bez[0]), $filter) &&
			   !mb_strstr(mb_strtolower(implode('',$l->stg)), $filter))
			{
				continue;
			}
		}

		$fixangestellt_info = '';
		if($fixangestellt)
		{
			if($selbstverwaltete_pause)
			{
				$fixangestellt_info = 'SVP';
			}
			else
				$fixangestellt_info = 'FIX';
		}
		else
			$fixangestellt_info = 'EXT';

		$vertragsstatus_arr = array();
		$vertragsstatus_kurzbz_arr = array();
		// Lehrauftragsstatus ermitteln
		foreach ($l->lem as $row_lem)
		{
			$lem_obj = new lehreinheitmitarbeiter();
			if ($lem_obj->load($row_lem['lehreinheit_id'], $row_lem['mitarbeiter_uid']))
			{
				if ($lem_obj->vertrag_id != '')
				{
					$vertrag_obj = new vertrag();
					if($vertrag_obj->getStatus($lem_obj->vertrag_id))
					{
						$vertragsstatus_arr[] = $vertrag_obj->vertragsstatus_bezeichnung;
						$vertragsstatus_kurzbz_arr[] = $vertrag_obj->vertragsstatus_kurzbz;
					}
				}
				else
				{
					$vertragsstatus_arr[] = 'Neu';
				}
			}
		}
		$vertragsstatus = implode(',', array_unique($vertragsstatus_arr));

		if (!is_null($vertrag) && $vertrag != '')
		{
			switch($vertrag)
			{
				// Alle ab Status erteilt herausfiltern
				// der rest wird verworfen
				case 'erteilt':
					if (!in_array('erteilt', $vertragsstatus_kurzbz_arr)
					 && !in_array('akzeptiert', $vertragsstatus_kurzbz_arr))
					{
						continue 2;
					}
					break;

				// Alle ab Status bestellt herausfiltern
				// der rest wird verworfen
				case 'bestellt':
					if (!in_array('bestellt', $vertragsstatus_kurzbz_arr)
					 && !in_array('erteilt', $vertragsstatus_kurzbz_arr)
					 && !in_array('akzeptiert', $vertragsstatus_kurzbz_arr))
					{
						continue 2;
					}
					break;
				default:
					break;
			}
		}
		echo'<RDF:li>
			<RDF:Description  id="lva'.($anz--).'" about="'.$rdf_url.$l->unr.'">
					<LVA:lvnr>'.$lvnr.'</LVA:lvnr>
					<LVA:unr>'.$l->unr.'</LVA:unr>
					<LVA:lektor>'.$lektor.'</LVA:lektor>
					<LVA:fixangestellt_info>'.$fixangestellt_info.'</LVA:fixangestellt_info>
					<LVA:lehrfach_id>'.$l->lehrfach_id.'</LVA:lehrfach_id>
					<LVA:studiengang_kz>'.$l->stg_kz[0].'</LVA:studiengang_kz>
					<LVA:fachbereich_kurzbz>'.$l->fachbereich.'</LVA:fachbereich_kurzbz>
					<LVA:semester>'.$l->semester[0].'</LVA:semester>
					<LVA:verband>'.$l->verband[0].'</LVA:verband>
					<LVA:gruppe>'.$l->gruppe[0].'</LVA:gruppe>
					<LVA:gruppe_kurzbz>'.$l->gruppe_kurzbz[0].'</LVA:gruppe_kurzbz>
					<LVA:raumtyp>'.$l->raumtyp.'</LVA:raumtyp>
					<LVA:raumtypalternativ>'.$l->raumtypalternativ.'</LVA:raumtypalternativ>
					<LVA:semesterstunden>'.$planstunden.'</LVA:semesterstunden>
					<LVA:stundenblockung>'.$stundenblockung.'</LVA:stundenblockung>
					<LVA:wochenrythmus>'.$wochenrythmus.'</LVA:wochenrythmus>
					<LVA:verplant>'.$verplant.'</LVA:verplant>
					<LVA:offenestunden>'.$offenestunden.'</LVA:offenestunden>
					<LVA:start_kw>'.$start_kw.'</LVA:start_kw>
					<LVA:anmerkung><![CDATA['.$l->anmerkung[0].']]></LVA:anmerkung>
					<LVA:studiensemester_kurzbz>'.$l->studiensemester_kurzbz.'</LVA:studiensemester_kurzbz>
					<LVA:lehrfach>'.$lehrfach.'</LVA:lehrfach>
					<LVA:lehrform>'.$lehrform.'</LVA:lehrform>
					<LVA:lehrfach_bez><![CDATA['.$l->lehrfach_bez[0].']]></LVA:lehrfach_bez>
					<LVA:lehrfach_farbe>#'.$l->lehrfach_farbe[0].'</LVA:lehrfach_farbe>
					<LVA:lva_ids>'.$lva_ids.'</LVA:lva_ids>
					<LVA:lehrverband>'.$lehrverband.'</LVA:lehrverband>
					<LVA:anzahl_notizen>'.$anzahl_notizen.'</LVA:anzahl_notizen>
					<LVA:lehreinheit_id>'.$l->lehreinheit_id[0].'</LVA:lehreinheit_id>
					<LVA:vertragsstatus>'.$vertragsstatus.'</LVA:vertragsstatus>
					<LVA:zeitverfuegbarkeit>'. $zeitverfuegbarkeit. '</LVA:zeitverfuegbarkeit>
				</RDF:Description>
			</RDF:li>';
	}
}
?>
</RDF:Seq>
</RDF:RDF>
