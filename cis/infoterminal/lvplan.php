<?php
require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/benutzer.class.php');

$db = new basis_db();

echo '<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<body>';

if(isset($_GET['datum']))
	$datum = $_GET['datum'];
else
	$datum = date('Y-m-d');

if(isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else
	$stg_kz='';

if(isset($_GET['fachbereich_kurzbz']))
	$fachbereich_kurzbz=$_GET['fachbereich_kurzbz'];
else
	$fachbereich_kurzbz='';

if(isset($_GET['stockwerk']))
	$stockwerk=$_GET['stockwerk'];
else
	$stockwerk='';

if(isset($_GET['standort_id']))
	$standort_id=$_GET['standort_id'];
else
	$standort_id='';

$qry="
SELECT 
	* 
FROM 
	campus.vw_stundenplan
	JOIN lehre.tbl_stunde USING(stunde) 
	LEFT JOIN public.tbl_ort USING(ort_kurzbz)
WHERE 
	datum=".$db->db_add_param($datum)."
	AND tbl_stunde.ende>TIME 'now' AND tbl_stunde.ende<TIME 'now'+'2 hours'::interval
";

if($stg_kz!='')
	$qry.="AND studiengang_kz=".$db->db_add_param($stg_kz);
if($fachbereich_kurzbz!='')
	$qry.="AND fachbereich_kurzbz=".$db->db_add_param($fachbereich_kurzbz);
if($stockwerk!='')
	$qry.="AND tbl_ort.stockwerk=".$db->db_add_param($stockwerk);
if($standort_id!='')
	$qry.="AND standort_id=".$db->db_add_param($standort_id);
$qry.="ORDER BY stunde";
$stundenzeiten=array();
$stunden=array();
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$stunden[$row->ort_kurzbz][$row->stunde][$row->unr]['lektoren'][]=$row->lektor;
		$stunden[$row->ort_kurzbz][$row->stunde][$row->unr]['lektoren_uids'][]=$row->uid;
		if($row->gruppe_kurzbz!='')
			$stunden[$row->ort_kurzbz][$row->stunde][$row->unr]['gruppen'][]=$row->gruppe_kurzbz;
		else
			$stunden[$row->ort_kurzbz][$row->stunde][$row->unr]['gruppen'][]=mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe;
		$stunden[$row->ort_kurzbz][$row->stunde][$row->unr]['bezeichnung']=$row->lehrfach_bez;
		$stunden[$row->ort_kurzbz][$row->stunde][$row->unr]['farbe']=$row->farbe;
		$stunden[$row->ort_kurzbz][$row->stunde][$row->unr]['lehrform']=$row->lehrform;
		$stunden[$row->ort_kurzbz][$row->stunde][$row->unr]['beginn']=$row->beginn;
		$stunden[$row->ort_kurzbz][$row->stunde][$row->unr]['ende']=$row->ende;
		$stundenzeiten[$row->stunde]['beginn']=$row->beginn;
		$stundenzeiten[$row->stunde]['ende']=$row->ende;

	}
}

echo '<table width="100%" border=0 cellspacing=0 cellpadding=0>
<tr>
	<th style="background-color:gray;"></th>';
ksort($stundenzeiten);
foreach($stundenzeiten as $stunde=>$row_stunden)
{
	echo '<th style="background-color:gray;">';
	echo $stunde.'.Stunde <br> '.mb_substr($row_stunden['beginn'],0,5).' - '.mb_substr($row_stunden['ende'],0,5);
	echo '</th>';
}
echo '</tr>';
foreach($stunden as $ort=>$row_orte)
{
	echo '<tr>';
	echo '<td style="background-color: gray;"><center><b>'.$ort.'</b></center></td>';

	foreach($stundenzeiten as $stunde=>$foo)
	{
		if(isset($row_orte[$stunde]))
			$row_stunden = $row_orte[$stunde];
		else
		{
			echo '<td style="background-color: white;border:1px solid gray;"></td>';
			continue;
		}
		echo '<td>';//Stunde'.$stunde;
		if(is_array($row_stunden))
		{
			foreach($row_stunden as $row_lv)
			{
				echo '<div style="background-color: #'.$row_lv['farbe'].'; text-align:center;height:70px; overflow:auto; border:1px solid gray">';
				echo '<br><span style="font-size: medium; font-weight:bold;">'.$row_lv['bezeichnung'].'</span><br>';
				$lektoren_arr =array_unique($row_lv['lektoren']);
				$lektoren_uidarr =array_unique($row_lv['lektoren_uids']);

				echo '<br><div style="float:right">';
				if(count($lektoren_uidarr)==1)
				{
					$benutzer = new benutzer();
					$benutzer->load($lektoren_uidarr[0]);
					echo $benutzer->titelpre.' '.$benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost;
				}
				else
				{
					$lektoren = implode(', ',$lektoren_arr);
					echo $lektoren;
				}
				echo '&nbsp;&nbsp;</div>';
				echo '<div style="float: left">&nbsp;&nbsp;';
				$gruppen = implode(', ',array_unique($row_lv['gruppen']));
				echo $gruppen;
				echo '</div>';
				echo '</div>';
			}
		}
		echo '</td>';
	}
	echo '</tr>';
}
echo '</table>
</body>
</html>';
