<?php
// header f?r no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';
include('../vilesci/config.inc.php');
include('../include/functions.inc.php');
include('../include/lehrstunde.class.php');
include('../include/stundenplan.class.php');

$uid=get_uid();

// Variablen uebernehmen
if (isset($_GET[aktion]))
	$aktion=$_GET[aktion];
if (isset($_GET[new_stunde]))
	$new_stunde=$_GET[new_stunde];
if (isset($_GET[new_datum]))
	$new_datum=$_GET[new_datum];
if (isset($_GET[type]))
	$type=$_GET[type];
if (isset($_GET[ort_kurzbz]))
	$ort_kurzbz=$_GET[ort_kurzbz];
else
	$ort_kurzbz='EDV6.08';
$i=0;
$name_stpl_id='stundenplan_id'.$i;
while ($i<100 && isset($_GET[$name_stpl_id]))
{
	$stpl_id[]=$_GET[$name_stpl_id];
	//echo $stpl_id[$i];
	$name_stpl_id='stundenplan_id'.++$i;

}


if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
$error_msg.=loadVariables($conn,$uid);

// Authentifizierung
if ($uid=check_student($uid, $conn))
	$user='student';
elseif ($uid=check_lektor($uid, $conn))
	$user='lektor';
else
    die("Cannot set usertype!");

    // User bestimmen
if (!isset($type))
	$type=$user;
if (!isset($pers_uid))
	$pers_uid=$uid;

	// Datums Format
$erg_std=pg_query($conn, "SET datestyle TO ISO;")
	or die(pg_last_error($conn));


// Aktionen durchfuehren
if ($aktion=='stplverschieben')
{
	foreach ($stpl_id as $stundenplan_id)
	{
		$lehrstunde=new lehrstunde($conn);
		$lehrstunde->load($stundenplan_id,$db_stpl_table);
		$lehrstunde->datum=$new_datum;
		$lehrstunde->stunde=$new_stunde;
		$lehrstunde->save($db_stpl_table);
	}
}
// Stundenplan abfragen
$stdplan=new stundenplan($type,$conn);
if (!isset($datum))
	$datum=mktime();

// Benutzergruppe
$stdplan->user=$user;
// aktueller Benutzer
$stdplan->user_uid=$uid;

// Zusaetzliche Daten laden
if (! $stdplan->load_data($type,$pers_uid,$ort_kurzbz,$stg_kz,$sem,$ver,$grp,$einheit_kurzbz,$db_table) )
	die($stdplan->errormsg);
// Stundenplan einer Woche laden
if (! $stdplan->load_week($datum,$db_stpl_table))
	die($stdplan->errormsg);
// Kopfbereich drucken

// Stundenplan der Woche in RDF drucken
$stdplan->draw_week_rdf();
?>