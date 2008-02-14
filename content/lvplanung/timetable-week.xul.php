<?php
header("Content-type: application/vnd.mozilla.xul+xml");

include('../../vilesci/config.inc.php');
include('../../include/globals.inc.php');
include('../../include/functions.inc.php');
include('../../include/berechtigung.class.php');
include('../../include/lehreinheit.class.php');
include('../../include/zeitwunsch.class.php');
include('../../include/wochenplan.class.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';

// Startwerte setzen
$db_stpl_table=null;

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
//echo $_SERVER[REQUEST_URI];

if (!isset($REMOTE_USER))
	$REMOTE_USER='pam';
$uid=$REMOTE_USER;

$error_msg='';
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg.='Es konnte keine Verbindung zum Server aufgebaut werden!';

// Benutzerdefinierte Variablen laden
$error_msg.=loadVariables($conn,$uid);

if (!isset($ignore_kollision))
	$ignore_kollision=(boolean)false;
elseif ($ignore_kollision=='false')
	$ignore_kollision=(boolean)false;
else
	$ignore_kollision=(boolean)true;
//var_dump($ignore_kollision);

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
	$gruppe_kurzbz=$_GET['gruppe'];
else
	$gruppe=null;
if (isset($_GET['semester_aktuell']))
	$semester_aktuell=$_GET['semester_aktuell'];

if (!isset($semester_aktuell) && $semesterplan)
	$error_msg.='Studien-Semester ist nicht gesetzt!';

?>

<!DOCTYPE page SYSTEM "chrome://tempus/locale/de-AT/tempus.dtd">
<window id="windowTimeTableWeek"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js.php"/>
<?php
if (isset($semesterplan) && $semesterplan)
	echo '<script type="application/x-javascript" src="'.APP_ROOT.'content/lvplanung/stpl-semester-overlay.js.php"/>';
else
	echo '<script type="application/x-javascript" src="'.APP_ROOT.'content/lvplanung/stpl-week-overlay.js.php"/>';
?>
<vbox id="boxTimeTableWeek" flex="5" style="overflow:auto;">
<?php
// Authentifizierung
/*if ($uid=check_student($REMOTE_USER, $conn))
	$user='student';
elseif ($uid=check_lektor($REMOTE_USER, $conn))
	$user='lektor';
else
    die("Cannot set usertype!");*/
$user=NULL;

    // User bestimmen
if (!isset($type))
	$type='lektor';
if (!isset($pers_uid))
	$pers_uid=$uid;

// Datums Format
if(!$result=pg_query($conn, "SET datestyle TO ISO;"))
	$error_msg=pg_last_error($conn);

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
}

// ****************************************************************************
// Aktionen durchfuehren
$error_msg.=db_query($conn,'BEGIN;');
// *************** Stunden verschieben ****************************************
if ($aktion=='stpl_move' || $aktion=='stpl_set')
{
	foreach ($stpl_id as $stundenplan_id)
	{
		$lehrstunde=new lehrstunde($conn);
		$lehrstunde->load($stundenplan_id,$db_stpl_table);
		$diffStunde=$new_stunde-$lehrstunde->stunde;
		$lehrstunde->datum=$new_datum;
		$lehrstunde->stunde=$new_stunde;
		if ($ort!=$old_ort)
			$lehrstunde->ort_kurzbz=$ort;
		if ($aktion=='stpl_set')
			$lehrstunde->ort_kurzbz=$new_ort;
		$kollision=$lehrstunde->kollision($db_stpl_table);
		if ($kollision)
			$error_msg.=$lehrstunde->errormsg;
		if (!$kollision || $ignore_kollision)
		{
			$lehrstunde->save($uid,$db_stpl_table);
			$error_msg.=$lehrstunde->errormsg;
		}
	}
	// Mehrfachauswahl
	if (isset($stpl_idx))
		foreach ($stpl_idx as $stundenplan_id)
		{
			$lehrstunde=new lehrstunde($conn);
			$lehrstunde->load($stundenplan_id,$db_stpl_table);
			$lehrstunde->datum=$new_datum;
			$lehrstunde->stunde+=$diffStunde;
			if ($ort!=$old_ort)
				$lehrstunde->ort_kurzbz=$ort;
			if ($aktion=='stpl_set')
				$lehrstunde->ort_kurzbz=$new_ort;
			$kollision=$lehrstunde->kollision($db_stpl_table);
			if ($kollision)
				$error_msg.=$lehrstunde->errormsg;
			if (!$kollision || $ignore_kollision)
			{
				$lehrstunde->save($uid,$db_stpl_table);
				$error_msg.=$lehrstunde->errormsg;
			}
		}
}
// ****************** STPL Delete *******************************
elseif ($aktion=='stpl_delete_single' || $aktion=='stpl_delete_block')
{
	$lehrstunde=new lehrstunde($conn);
	foreach ($stpl_id as $stundenplan_id)
	{
		$lehrstunde->delete($stundenplan_id,$db_stpl_table);
		$error_msg.=$lehrstunde->errormsg;
	}
}
// ******************** Lehrveranstaltung setzen ******************************
elseif ($aktion=='lva_single_set')
{
	//$anz_lvas=count($lva_id);
	$z=0;
	foreach ($lva_id AS $le_id)
	{
		$lva[$z]=new lehreinheit($conn);
		$lva[$z]->loadLE($le_id);
		//$error_msg.='test'.$le_id.($lva[$i]->errormsg).($lva[$i]->stundenblockung);
		for ($j=0;$j<$lva[$z]->stundenblockung && $error_msg=='';$j++)
			if (!$lva[$z]->check_lva($new_datum,$new_stunde+$j,$new_ort,$db_stpl_table) && !$ignore_kollision)
				$error_msg.=$lva[$z]->errormsg;
		$z++;
	}
	for ($i=0;$i<$z && $error_msg=='';$i++)
	{
		//$lva[$i]=new lehrveranstaltung($lva_id[$i]);
		//$error_msg.='Blockung'.$lva[$i]->stundenblockung.var_dump($lva);
		//$error_msg.='Datum:'.$new_datum.' Std:'.($new_stunde+$j).$new_ort.$db_stpl_table.$uid;
		for ($j=0;$j<$lva[$i]->stundenblockung;$j++)
			if (!$lva[$i]->save_stpl($new_datum,$new_stunde+$j,$new_ort,$db_stpl_table,$uid))
				$error_msg.='Error: '.$lva[$i]->errormsg;
			//else die('test');
	}
	//$error_msg.='test';
}
//******************* Multi Verplanung ***************
elseif ($aktion=='lva_multi_set')
{
	// Ferien holen
	$ferien=new ferien($conn);
	if ($type=='verband')
		$ferien->getAll($stg_kz);
	else
		$ferien->getAll(0);

	// Ende holen
	if (!$result_semester=pg_query($conn,"SELECT * FROM public.tbl_studiensemester WHERE studiensemester_kurzbz='$semester_aktuell';"))
		die (pg_last_error($conn));
	if (pg_numrows($result_semester)>0)
		$ende=pg_result($result_semester,0,'ende');
	else
		$error_msg.="Fatal Error: Ende Datum ist nicht gesetzt ($semester_aktuell)!";
	//echo '<label>'.$ende.'</label>';
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
	if(!$result_lva=pg_query($conn, $sql_query))
		$error_msg.=pg_last_error($conn);
	$num_rows_lva=pg_numrows($result_lva);
	// Daten aufbereiten
	for ($i=0;$i<$num_rows_lva;$i++)
	{
		$row=pg_fetch_object($result_lva,$i);
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
	//$error_msg.='Offene Stunden='.$offenestunden;

	/*// Verplante Stunden
	$verplant=array_unique($verplant);
	if (count($verplant)==1)
		$verplant=$verplant[0];
	else
		$error_msg.='Verplante Stunden sind nicht eindeutig!';
	//Semesterstunden
	$semesterstunden=array_unique($semesterstunden);
	if (count($semesterstunden)==1)
		$semesterstunden=$semesterstunden[0];
	else
		$error_msg.='Semesterstunden sind nicht eindeutig!';*/

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
		$error_msg.='Wochenrythmus ist nicht eindeutig!';
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
				$lva[$i]=new lehreinheit($conn);
				$lva[$i]->loadLE($lva_id[$i]);
				for ($j=0;$j<$block;$j++)
					if (!$lva[$i]->check_lva($new_datum,$new_stunde+$j,$new_ort,$db_stpl_table) && !$ignore_kollision)
						$error_msg.=$lva[$i]->errormsg;
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
// Lehrveranstaltungen aus dem Stundenplan loeschen
elseif ($aktion=='lva_stpl_del_multi' || $aktion=='lva_stpl_del_single')
{
	$result_semester=pg_query($conn,"SELECT start,ende FROM public.tbl_studiensemester WHERE studiensemester_kurzbz='$semester_aktuell';");
	if (pg_numrows($result_semester)>0)
	{
		$start=date('Y-m-d',$datum);
		if ($aktion=='lva_stpl_del_multi')
			$ende=pg_result($result_semester,0,'ende');
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
		if(!$result_lva_del=pg_query($conn, $sql_query))
			$error_msg.=pg_last_error($conn);
	}
	else
		$error_msg.='Studiensemester '.$semester_aktuell.' konnte nicht gefunden werden!';
}

if ($error_msg=='')
	$error_msg.=@db_query($conn,'COMMIT;');
else
	$error_msg.=@db_query($conn,'ROLLBACK;');

// Stundenplan erstellen
$stdplan=new wochenplan($type,$conn);
if (!isset($datum))
	$datum=mktime();
if (!isset($semesterplan) || !$semesterplan)
	$begin=$ende=$datum;
else
{
	$result_semester=pg_query($conn,"SELECT start,ende FROM public.tbl_studiensemester WHERE studiensemester_kurzbz='$semester_aktuell';");
	if (pg_numrows($result_semester)>0)
	{
		$begin=strtotime(pg_result($result_semester,0,'start'));
		$ende=strtotime(pg_result($result_semester,0,'ende'));
	}
	else
		$error_msg.='Studiensemester '.$semester_aktuell.' konnte nicht gefunden werden!';
}

// Benutzergruppe
$stdplan->user=$user;
// aktueller Benutzer
$stdplan->user_uid=$uid;

// Zeitwuensche laden falls benoetigt
$zeitwunsch=null;
if ($type=='lektor' || $aktion=='lva_single_search'	|| $aktion=='lva_multi_search')
{
	$wunsch=new zeitwunsch($conn);
	if ($type=='lektor')
		if ($wunsch->loadPerson($pers_uid,$datum))
			$zeitwunsch=$wunsch->zeitwunsch;
		else
			$error_msg.=$wunsch->errormsg;
	if ($aktion=='lva_single_search' || $aktion=='lva_multi_search')
		if ($wunsch->loadZwLE($lva_id,$datum))
			$zeitwunsch=$wunsch->zeitwunsch;
		else
			$error_msg.=$wunsch->errormsg;
}

// Zusaetzliche Daten laden
if (! $stdplan->load_data($type,$pers_uid,$ort,$stg_kz,$sem,$ver,$grp,$gruppe) && $error_msg!='')
	$error_msg.=$stdplan->errormsg;
//echo 'load_data'.$error_msg;
// Stundenplan einer Woche laden
if (! $stdplan->load_week($datum,$db_stpl_table))
	$error_msg.=$stdplan->errormsg;
while ($begin<=$ende)
{
	$stdplan->init_stdplan();
	$datum=$begin;
	$begin+=604800;	// eine Woche
	//echo '<label>'.date("Y-m-d - D",$datum).$datum.'</label>';
	// Stundenplan einer Woche laden
	if (! $stdplan->load_week($datum,$db_stpl_table))
		$error_msg.=$stdplan->errormsg;
	//echo 'load_week'.$error_msg;
	if ($aktion=='lva_single_search' || $aktion=='lva_multi_search')
		if (! $stdplan->load_lva_search($datum,$lva_id,$db_stpl_table, $aktion))
			$error_msg.=$stdplan->errormsg;
		else
			$error_msg.=$stdplan->errormsg;
	//echo 'load_lva_search'.$error_msg;
	if ($aktion=='stpl_single_search')
		if (! $stdplan->load_stpl_search($datum,$stpl_id,$db_stpl_table))
			$error_msg.=$stdplan->errormsg;
	//echo 'load_stpl_search'.$error_msg;

	// Stundenplan der Woche drucken
	$stdplan->draw_week_xul($semesterplan,$uid,$zeitwunsch, $ignore_kollision);
}
//echo $error_msg;.$_SERVER["REQUEST_URI"]
?>

</vbox>
<label id="TimeTableWeekErrors"><?php echo htmlspecialchars($error_msg); ?></label>

<script type="application/x-javascript">
	<?php
		if ($error_msg!='')
			echo "alert('".str_replace("'",'"',$error_msg)."');";
	?>
	top.document.getElementById("statusbarpanel-text").setAttribute("label","<?php echo htmlspecialchars($PHP_SELF.$error_msg); ?>");
</script>
</window>