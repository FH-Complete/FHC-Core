<?php
/* Copyright (C) 2008 Technikum-Wien
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
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/globals.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/lehreinheit.class.php');
require_once('../../include/zeitwunsch.class.php');
require_once('../../include/wochenplan.class.php');
require_once('../../include/reservierung.class.php');
require_once('../../include/log.class.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';
$PHP_SELF = $_SERVER['PHP_SELF'];
// Startwerte setzen
$db_stpl_table=null;
$db = new basis_db();

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';

$uid = get_uid();

//Berechtigung pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('lehre/lvplan'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$error_msg='';
$kollision_msg='';

// Benutzerdefinierte Variablen laden
loadVariables($uid);

if (!isset($ignore_kollision))
	$ignore_kollision=(boolean)false;
elseif ($ignore_kollision=='false')
	$ignore_kollision=(boolean)false;
else
	$ignore_kollision=(boolean)true;

if (!isset($alle_unr_mitladen))
	$alle_unr_mitladen=(boolean)false;
elseif ($alle_unr_mitladen=='false')
	$alle_unr_mitladen=(boolean)false;
else
	$alle_unr_mitladen=(boolean)true;

// Bezeichnungen fuer Tabellen und Views
$lva_stpl_view=VIEW_BEGIN.'lva_'.$db_stpl_table;

// Variablen uebernehmen
if (isset($_GET['aktion']))
	$aktion=$_GET['aktion'];
else
	$aktion=null;

if (isset($_GET['semesterplan']))
	$semesterplan=$_GET['semesterplan'];
else
	$semesterplan=false;
if (isset($_GET['new_stunde']))
	$new_stunde=$_GET['new_stunde'];
if (isset($_GET['new_datum']))
	$new_datum=$_GET['new_datum'];
if (isset($_GET['old_ort']))
	$old_ort=$_GET['old_ort'];
if (isset($_GET['new_ort']))
	$new_ort=$_GET['new_ort'];
if (isset($_GET['kollisionsanzahl']))
	$kollisionsanzahl=$_GET['kollisionsanzahl'];
else
	$kollisionsanzahl=0;
if (isset($_GET['ort']))
	$ort=$_GET['ort'];
else
	$ort=null;
if (isset($_GET['datum']))
	$datum=$_GET['datum'];
if (isset($_GET['type']))
	$type=$_GET['type'];
if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else
	$stg_kz=null;
if (isset($_GET['sem']))
	$sem=$_GET['sem'];
else
	$sem=null;
if (isset($_GET['ver']))
	$ver=$_GET['ver'];
else
	$ver=null;
if (isset($_GET['grp']))
	$grp=$_GET['grp'];
else
	$grp=null;
if (isset($_GET['pers_uid']))
	$pers_uid=$_GET['pers_uid'];
if (isset($_GET['gruppe']))
	$gruppe=$_GET['gruppe'];
else
	$gruppe=null;
if (isset($_GET['semester_aktuell']))
	$semester_aktuell=$_GET['semester_aktuell'];

if (!isset($semester_aktuell) && $semesterplan)
	$error_msg.='Studien-Semester ist nicht gesetzt!';

if(isset($_GET['fachbereich_kurzbz']))
	$fachbereich_kurzbz = $_GET['fachbereich_kurzbz'];
else
	$fachbereich_kurzbz=null;

if (isset($_GET['new_unr']))
	$new_unr=$_GET['new_unr'];
else
	$new_unr=null;

if (isset($_GET['new_blockung']))
	$new_blockung=$_GET['new_blockung'];
else
	$new_blockung=null;
?>

<!DOCTYPE page SYSTEM "chrome://tempus/locale/de-AT/tempus.dtd">
<window id="windowTimeTableWeek"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	<?php echo ((isset($semesterplan) && $semesterplan)?'':'onload="setScrollpositionTimeTableWeek()"'); ?>
	>

<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js.php"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
<?php
if (isset($semesterplan) && $semesterplan)
	echo '<script type="application/x-javascript" src="'.APP_ROOT.'content/lvplanung/stpl-semester-overlay.js.php"/>';
else
	echo '<script type="application/x-javascript" src="'.APP_ROOT.'content/lvplanung/stpl-week-overlay.js.php"/>';
?>
<scrollbox id="timetable-week-scrollbox" flex="1" style="overflow:auto;" orient="vertical">
<vbox id="boxTimeTableWeek" flex="5">
<keyset>
  <key id="timetable-week-key-delete" keycode="VK_DELETE" oncommand="TimetableDeleteEntries();"/>
</keyset>

<?php
$user=NULL;

    // User bestimmen
if (!isset($type))
	$type='lektor';
if (!isset($pers_uid))
	$pers_uid=$uid;

// Datums Format
//if(!$db->db_query("SET datestyle TO ISO;"))
//	$error_msg=$db->db_last_error();

// ****************************************************************************
// Variablen fuer Aktionen setzen
if ($aktion=='lva_single_search' || $aktion=='lva_single_set'
	|| $aktion=='lva_multi_search' || $aktion=='lva_multi_set'
	|| $aktion=='lva_stpl_del_multi' || $aktion=='lva_stpl_del_single')
{
	$i=0;
	$name_lva_id='lva_id'.$i;
	while ($i<100 && isset($_GET[$name_lva_id]))
	{
		$lva_id[$i]=$_GET[$name_lva_id];
		//$error_msg.=$lva_id[$i];
		$name_lva_id='lva_id'.++$i;
	}
	$lva_id=array_unique($lva_id);
}
if ($aktion=='stpl_move' || $aktion=='stpl_single_search' || $aktion=='stpl_set' || $aktion=='stpl_delete_single')
{
	$i=0;
	$name_stpl_id='stundenplan_id'.$i;
	while ($i<100 && isset($_GET[$name_stpl_id]))
	{
		$stpl_id[]=$_GET[$name_stpl_id];
		//echo $stpl_id[$i];
		$name_stpl_id='stundenplan_id'.++$i;
	}
	// Mehrfachauswahl uebernehmen
	$j=0;
	$name_stpl_idx='x'.$j.'stundenplan_id0';
	while ($j<100 && isset($_GET[$name_stpl_idx]))
	{
		$i=0;
		$name_stpl_id='x'.$j.'stundenplan_id'.$i;
		while ($i<100 && isset($_GET[$name_stpl_id]))
		{
			$stpl_idx[]=$_GET[$name_stpl_id];
			$name_stpl_id='x'.$j.'stundenplan_id'.++$i;
		}
		$name_stpl_idx='x'.++$j.'stundenplan_id0';
	}

	//ReservierungsIDs
	$i=0;
	$name_res_id='reservierung_id'.$i;
	while ($i<100 && isset($_GET[$name_res_id]))
	{
		$res_id[]=$_GET[$name_res_id];
		$name_res_id='reservierung_id'.++$i;
	}

	// Mehrfachauswahl uebernehmen
	$j=0;
	$name_res_idx='x'.$j.'reservierung_id0';
	while ($j<100 && isset($_GET[$name_res_idx]))
	{
		$i=0;
		$name_res_id='x'.$j.'reservierung_id'.$i;
		while ($i<100 && isset($_GET[$name_res_id]))
		{
			$res_idx[]=$_GET[$name_res_id];
			$name_res_id='x'.$j.'reservierung_id'.++$i;
		}
		$name_res_idx='x'.++$j.'reservierung_id0';
	}
}

// ****************************************************************************
// Aktionen durchfuehren
$db->db_query('BEGIN;');
// *************** Stunden verschieben ****************************************
if ($aktion=='stpl_move' || $aktion=='stpl_set')
{
	$undo='';
	$sql='';
	$moved=array();

	foreach ($stpl_id as $stundenplan_id)
	{
		$moved[]=$stundenplan_id;
		$lehrstunde=new lehrstunde();
		$lehrstunde->load($stundenplan_id,$db_stpl_table);
		if($rechte->isBerechtigt('lehre/lvplan',$lehrstunde->studiengang_kz,'ui'))
		{
			$undo.=$lehrstunde->getUndo($db_stpl_table);
			$diffStunde=$new_stunde-$lehrstunde->stunde;
			$lehrstunde->datum=$new_datum;
			$lehrstunde->stunde=$new_stunde;
			if ($ort!=$old_ort)
				$lehrstunde->ort_kurzbz=$ort;
			if ($aktion=='stpl_set')
				$lehrstunde->ort_kurzbz=$new_ort;

			if($new_unr!='')
				$lehrstunde->unr = $new_unr;

			$kollision=$lehrstunde->kollision($db_stpl_table);
			if ($kollision && !$ignore_kollision)
				$kollision_msg.=$lehrstunde->errormsg;
			if (!$kollision || $ignore_kollision || $kollisionsanzahl>0)
			{
				if(!$lehrstunde->save($uid,$db_stpl_table))
					$error_msg.=$lehrstunde->errormsg;
				$sql.=$lehrstunde->lastqry;
			}
		}
		else
		{
			$error_msg.="Sie haben keine Berechtigung zur Verschiebung von Stunden des Studienganges ".$lehrstunde->studiengang;
		}
	}
	// Mehrfachauswahl
	if (isset($stpl_idx))
	{
		foreach ($stpl_idx as $stundenplan_id)
		{
			if(!in_array($stundenplan_id, $moved))
			{
				$lehrstunde=new lehrstunde();
				$lehrstunde->load($stundenplan_id,$db_stpl_table);
				if($rechte->isBerechtigt('lehre/lvplan',$lehrstunde->studiengang_kz,'ui'))
				{
					$undo.=$lehrstunde->getUndo($db_stpl_table);
					$lehrstunde->datum=$new_datum;
					$lehrstunde->stunde+=$diffStunde;
					if ($ort!=$old_ort)
						$lehrstunde->ort_kurzbz=$ort;
					if ($aktion=='stpl_set')
						$lehrstunde->ort_kurzbz=$new_ort;
					if($new_unr!='')
						$lehrstunde->unr = $new_unr;

					$kollision=$lehrstunde->kollision($db_stpl_table);
					if ($kollision && !$ignore_kollision)
						$kollision_msg.=$lehrstunde->errormsg;
					if (!$kollision || $ignore_kollision || $kollisionsanzahl>0)
					{
						if(!$lehrstunde->save($uid,$db_stpl_table))
							$error_msg.=$lehrstunde->errormsg;
						$sql.=$lehrstunde->lastqry;
					}
				}
				else
				{
					$error_msg.="Sie haben keine Berechtigung zur Verschiebung von Stunden des Studienganges ".$lehrstunde->studiengang;
				}
			}
		}
	}

	//UNDO Befehl schreiben
	if($undo!='' && $error_msg=='' && $sql!='' && $kollision_msg=='')
	{
		$log = new log();
		$log->executetime = date('Y-m-d H:i:s');
		$log->sqlundo = $undo;
		$log->sql = $sql;
		$log->beschreibung = 'Stundenverschiebung '.$new_datum.'('.$new_stunde.') '.$ort;
		$log->mitarbeiter_uid = $uid;
		if(!$log->save(true))
			$error_msg.='Fehler: '.$log->errormsg;

	}
}
// ****************** STPL Delete *******************************
elseif ($aktion=='stpl_delete_single' || $aktion=='stpl_delete_block')
{
	$lehrstunde=new lehrstunde();
	$sql='';
	$geloeschteDaten = '';

	if($rechte->isBerechtigt('lehre/lvplan',null,'uid'))
	{
		//Einzelne Stunden entfernen
		if(isset($stpl_id))
		{
			foreach ($stpl_id as $stundenplan_id)
			{
				$lehrstunde->load($stundenplan_id,$db_stpl_table);
				$geloeschteDaten .= 'Lektor: '.$lehrstunde->lektor_uid.', Datum: '.
									$lehrstunde->datum.', Stunde: '.$lehrstunde->stunde.', Ort: '.
									$lehrstunde->ort_kurzbz.', Verband: '.strtoupper($lehrstunde->studiengang).'-'.
									$lehrstunde->sem.$lehrstunde->ver.$lehrstunde->grp.', Spezialgruppe: '.$lehrstunde->gruppe_kurzbz.'; ';
				$lehrstunde->delete($stundenplan_id,$db_stpl_table);
				$error_msg.=$lehrstunde->errormsg;
				$sql.=$lehrstunde->lastqry.'; ';
			}
		}

		//Loeschen von mehreren Stunden
		if(isset($stpl_idx))
		{
			foreach ($stpl_idx as $stundenplan_id)
			{
				$lehrstunde->load($stundenplan_id,$db_stpl_table);
				$geloeschteDaten .= 'Lektor: '.$lehrstunde->lektor_uid.', Datum: '.
									$lehrstunde->datum.', Stunde: '.$lehrstunde->stunde.', Ort: '.
									$lehrstunde->ort_kurzbz.', Verband: '.strtoupper($lehrstunde->studiengang).'-'.
									$lehrstunde->sem.$lehrstunde->ver.$lehrstunde->grp.', Spezialgruppe: '.$lehrstunde->gruppe_kurzbz.'; ';
				$lehrstunde->delete($stundenplan_id,$db_stpl_table);
				$error_msg.=$lehrstunde->errormsg;
				$sql.=$lehrstunde->lastqry.'; ';
			}
		}

		if(isset($res_id))
		{
			foreach ($res_id as $reservierung_id)
			{
				$reservierung = new reservierung();
				$reservierung->load($reservierung_id);
				$logdata_reservierung = (array)$reservierung;
				$logdata = var_export($logdata_reservierung, true);
				$reservierung->delete($reservierung_id);
				$error_msg.=$reservierung->errormsg;
			}
		}

		//Loeschen von mehreren Reservierungen
		if(isset($res_idx))
		{
			foreach ($res_idx as $reservierung_id)
			{
				$reservierung = new reservierung();
				$reservierung->load($reservierung_id);
				$logdata_reservierung = (array)$reservierung;
				$logdata = var_export($logdata_reservierung, true);
				$reservierung->delete($reservierung_id);
				$error_msg.=$reservierung->errormsg;
			}
		}

		//UNDO Befehl schreiben
		if($error_msg=='' && $sql!='')
		{
			$log = new log();
			$log->executetime = date('Y-m-d H:i:s');
			$log->sqlundo = NULL;
			$log->sql = $sql.' /* Geloeschte Daten: '.$geloeschteDaten.'*/';
			$log->beschreibung = 'Stundenloeschung';
			$log->mitarbeiter_uid = $uid;
			if(!$log->save(true))
				$error_msg.='Fehler: '.$log->errormsg;
		}
	}
	else
	{
		$error_msg.="Sie haben keine Berechtigung fuer diese Aktion";
	}
}
// ******************** Lehrveranstaltung setzen ******************************
elseif ($aktion=='lva_single_set')
{
	if($rechte->isBerechtigt('lehre/lvplan',null,'ui'))
	{
		$z=0;
		foreach ($lva_id AS $le_id)
		{
			$lva[$z]=new lehreinheit();
			$lva[$z]->loadLE($le_id);

			if($new_unr!='')
				$lva[$z]->unr=$new_unr;
			if($new_blockung!='')
				$lva[$z]->stundenblockung=$new_blockung;

			//$error_msg.='test'.$le_id.($lva[$i]->errormsg).($lva[$i]->stundenblockung);
			for ($j=0;$j<$lva[$z]->stundenblockung && $error_msg=='';$j++)
				if (!$lva[$z]->check_lva($new_datum,$new_stunde+$j,$new_ort,$db_stpl_table) && !$ignore_kollision)
					$kollision_msg.=$lva[$z]->errormsg."\n";
			$z++;
		}
		for ($i=0;$i<$z && $error_msg=='';$i++)
		{
			if($new_unr!='')
				$lva[$i]->unr=$new_unr;
			if($new_blockung!='')
				$lva[$i]->stundenblockung=$new_blockung;

			for ($j=0;$j<$lva[$i]->stundenblockung;$j++)
			{

				if (!$lva[$i]->save_stpl($new_datum,$new_stunde+$j,$new_ort,$db_stpl_table,$uid))
					$error_msg.='Error: '.$lva[$i]->errormsg;
			}
		}
	}
	else
	{
		$error_msg.="Sie haben keine Berechtigung fuer diese Aktion";
	}
}
//******************* Multi Verplanung ***************
elseif ($aktion=='lva_multi_set')
{
	if($rechte->isBerechtigt('lehre/lvplan',null,'ui'))
	{
		// Ferien holen
		$ferien=new ferien();
		if ($type=='verband')
			$ferien->getAll($stg_kz);
		else
			$ferien->getAll(0);

		// Ende holen
		if (!$result_semester=$db->db_query("SELECT * FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=".$db->db_add_param($semester_aktuell).";"))
			die ($db->db_last_error());
		if ($db->db_num_rows()>0)
		{
			$row = $db->db_fetch_object();
			$ende = $row->ende;
		}
		else
			$error_msg.="Fatal Error: Ende Datum ist nicht gesetzt ($semester_aktuell)!";

		$ende=mktime(0,0,1,substr($ende,5,2),substr($ende,8,2),substr($ende,0,4));
		$anz_lvas=count($lva_id);
		// Arrays intitialisieren
		$wochenrythmus=array();
		$verplant=array();
		$block=array();
		$wochenrythmus=array();
		$semesterstunden=array();
		$planstunden=array();
		$offenestunden=array();
		// LVAs holen
		$sql_query='SELECT * FROM lehre.'.$lva_stpl_view.' WHERE';
		$lvas='';
		foreach ($lva_id as $id)
			$lvas.=' OR lehreinheit_id='.$id;
		$lvas=substr($lvas,3);
		$sql_query.=$lvas;

		if(!$result_lva = $db->db_query($sql_query))
			$error_msg.=$db->db_last_error();
		$num_rows_lva=$db->db_num_rows($result_lva);
		// Daten aufbereiten
		for ($i=0;$i<$num_rows_lva;$i++)
		{
			$row=$db->db_fetch_object($result_lva,$i);
			$verplant[]=$row->verplant;
			$block[]=$row->stundenblockung;
			$wochenrythmus[]=$row->wochenrythmus;
			$semesterstunden[]=$row->semesterstunden;
			$planstunden[]=$row->planstunden;
			$offenestunden[]=$row->planstunden-$row->verplant;
		}
		// Variablen eindeutig?
		// Offene Stunden
		$os=$offenestunden[0];
		$offenestunden=array_unique($offenestunden);
		if (count($offenestunden)==1)
			$offenestunden=$os;
		else
			$error_msg.='Offene Stunden sind nicht eindeutig!';

		//Blockung
		$blk=$block[0];
		$block=array_unique($block);
		if (count($block)==1)
			$block=$blk;
		else
			$error_msg.='Blockung ist nicht eindeutig!';
		//Wochenrythmus
		$wr=$wochenrythmus[0];
		$wochenrythmus=array_unique($wochenrythmus);
		if (count($wochenrythmus)==1)
			$wochenrythmus=$wr;
		else
			$error_msg.='Wochenrhythmus ist nicht eindeutig!';
		$count=0;
		$rest=$offenestunden;
		if ($rest<=0)
			$error_msg.='Es sind bereits alle Stunden verplant!'.$rest;
		if ($error_msg=='')
		{
			$d=mktime(0,0,1,substr($new_datum,5,2),substr($new_datum,8),substr($new_datum,0,4));
			while ($rest>0 && $d<$ende)
			{
				if ($rest<$block && $rest>0)
					$block=$rest;
				//LVAs holen und pruefen ob moeglich
				for ($i=0;$i<$anz_lvas;$i++)
				{
					$lva[$i]=new lehreinheit();
					$lva[$i]->loadLE($lva_id[$i]);
					for ($j=0;$j<$block;$j++)
						if (!$lva[$i]->check_lva($new_datum,$new_stunde+$j,$new_ort,$db_stpl_table) && !$ignore_kollision)
							$kollision_msg.=$lva[$i]->errormsg;
				}
				// LVAs setzen
				for ($i=0;$i<$anz_lvas && $error_msg=='';$i++)
					for ($j=0;$j<$block;$j++)
						if (!$lva[$i]->save_stpl($new_datum,$new_stunde+$j,$new_ort,$db_stpl_table,$uid))
							$error_msg.=$lva[$i]->errormsg;
				$d=jump_week($d,$wochenrythmus);
				while ($ferien->isferien($d))
					$d=jump_week($d,$wochenrythmus);
				// Es kann sein, dass die Zeitumstellung (1 Stunde) Probleme macht
				// Falls 23 Uhr eine Stunde nach vor
				$new_datum=date('Y-m-d',$d);
				$rest-=$block;
			}
		}
	}
	else
	{
		$error_msg.="Sie haben keine Berechtigung fuer diese Aktion";
	}
}
// Lehrveranstaltungen aus dem Stundenplan loeschen
elseif ($aktion=='lva_stpl_del_multi' || $aktion=='lva_stpl_del_single')
{
	if($rechte->isBerechtigt('lehre/lvplan',null,'uid'))
	{
		$result_semester = $db->db_query("SELECT start,ende FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=".$db->db_add_param($semester_aktuell).";");
		if ($db->db_num_rows()>0)
		{
			$start=date('Y-m-d',$datum);
			if ($aktion=='lva_stpl_del_multi')
			{
				$row = $db->db_fetch_object($result_semester);
				$ende = $row->ende;
			}
			else
				$ende=date('Y-m-d',jump_week($datum,1));
			$anz_lvas=count($lva_id);
			$sql_query_lvaid='';
			$sql_query='DELETE FROM lehre.'.TABLE_BEGIN.$db_stpl_table.' WHERE (';
			for ($i=0;$i<$anz_lvas;$i++)
				$sql_query_lvaid.=' OR lehreinheit_id='.$lva_id[$i];
			$sql_query_lvaid=substr($sql_query_lvaid,3);
			$sql_query.=$sql_query_lvaid;
			$sql_query.=") AND datum>='$start' AND datum<'$ende'";
			if(!$result_lva_del=$db->db_query($sql_query))
				$error_msg.=$db->db_last_error();
		}
		else
			$error_msg.='Studiensemester '.$semester_aktuell.' konnte nicht gefunden werden!';
	}
	else
		$error_msg.="Sie haben keine Berechtigung fuer diese Aktion";
}

if ($error_msg=='' && ($kollision_msg=='' || $kollisionsanzahl>0))
{
	$db->db_query('COMMIT;');
	if($kollisionsanzahl>0)
		$error_msg.="\nStunden wurden verplant\n";
}
else
	$db->db_query('ROLLBACK;');

$error_msg.=$kollision_msg;

// Stundenplan erstellen
$stdplan=new wochenplan($type);
if (!isset($datum))
	$datum=time();
if (!isset($semesterplan) || !$semesterplan)
	$begin=$ende=$datum;
else
{
	$db->db_query("SELECT start,ende FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=".$db->db_add_param($semester_aktuell).";");
	if ($db->db_num_rows()>0)
	{
		$row = $db->db_fetch_object();
		$begin=strtotime($row->start);
		$ende=strtotime($row->ende);
	}
	else
		$error_msg.='Studiensemester '.$semester_aktuell.' konnte nicht gefunden werden!';
}

// Benutzergruppe
$stdplan->user=$user;
// aktueller Benutzer
$stdplan->user_uid=$uid;

// Zusaetzliche Daten laden
if (! $stdplan->load_data($type,$pers_uid,$ort,$stg_kz,$sem,$ver,$grp,$gruppe,$fachbereich_kurzbz) && $error_msg!='')
	$error_msg.=$stdplan->errormsg;

// Stundenplan einer Woche laden
//if (! $stdplan->load_week($datum,$db_stpl_table))
//	$error_msg.=$stdplan->errormsg;
while ($begin<=$ende)
{
	$stdplan->init_stdplan();
	$datum=$begin;
	$begin=strtotime("+1 week",$begin);

	// Zeitwuensche laden falls benoetigt
	$zeitwunsch=null;
	if ($type=='lektor' || $aktion=='lva_single_search'	|| $aktion=='lva_multi_search')
	{
		$wunsch=new zeitwunsch();
		if ($type=='lektor')
			if ($wunsch->loadPerson($pers_uid,montag($datum)))
				$zeitwunsch=$wunsch->zeitwunsch;
			else
				$error_msg.=$wunsch->errormsg;
		if ($aktion=='lva_single_search' || $aktion=='lva_multi_search')
			if ($wunsch->loadZwLE($lva_id,montag($datum)))
				$zeitwunsch=$wunsch->zeitwunsch;
			else
				$error_msg.=$wunsch->errormsg;
	}

	// Stundenplan einer Woche laden
	if (! $stdplan->load_week($datum,$db_stpl_table, $alle_unr_mitladen))
		$error_msg.=$stdplan->errormsg;

	//Raumvorschlag setzen

	if ($aktion=='lva_single_search' || $aktion=='lva_multi_search')
		if (! $stdplan->load_lva_search($datum,$lva_id,$db_stpl_table, $aktion))
			$error_msg.=$stdplan->errormsg;

	if ($aktion=='stpl_single_search')
	{
		if(isset($stpl_id))
		{
			if (! $stdplan->load_stpl_search($datum,$stpl_id,$db_stpl_table))
				$error_msg.=$stdplan->errormsg;
		}
		else
			$error_msg.='Derzeit gibt es keinen Raumvorschlag fuer Reservierungen';
	}

	// Stundenplan der Woche drucken
	$stdplan->draw_week_xul($semesterplan,$uid,$zeitwunsch, $ignore_kollision, $kollision_student, $max_kollision);

}

?>

</vbox>
</scrollbox>
<label id="TimeTableWeekErrors"><?php echo htmlspecialchars($error_msg); ?></label>

<script type="application/x-javascript">
	<?php
		if ($error_msg!='')
			echo "alert('".str_replace("'",'"',str_replace(chr(10),'\n',htmlspecialchars($error_msg)))."');";
	?>

	top.document.getElementById("statusbarpanel-text").setAttribute("label","<?php echo str_replace(chr(10),' ',htmlspecialchars($PHP_SELF.$error_msg)); ?>");
</script>
</window>
