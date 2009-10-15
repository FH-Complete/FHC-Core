<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/vilesci.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/variable.class.php');
require_once('../../../include/functions.inc.php');

$db = new basis_db();

$user = get_uid();
$variable = new variable();
$variable->loadVariables($user);

$stg = new studiengang();
$stg->getAll('typ, kurzbz');

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else 
{
	$stsem_obj = new studiensemester();
	$stsem_obj->getNearestTillNext();
	$stsem = $stsem_obj->studiensemester_kurzbz;
}

$stsem_obj = new studiensemester();
$stsem_obj->getAll();

echo '<h2>Übersicht - Verplanung der Lehreinheiten ('.$variable->variable->db_stpl_table.')</h2>';

echo '<form method="GET">Studiensemester <select name="stsem">';
foreach ($stsem_obj->studiensemester as $row)
{
	echo '<option value="'.$row->studiensemester_kurzbz.'" '.($row->studiensemester_kurzbz==$stsem?'selected':'').'>'.$row->studiensemester_kurzbz.'</option>';
}
echo '</select> <input type="submit" value="Anzeigen"></form>';

$gesamt=0;
$gesamt_verplant=0;
$gesamt_ps=0;
$gesamt_ps_verplant=0;
$content='';
function drawprogress($prozent)
{
	$color='red';
	if($prozent>=80)
		$color='lightgreen';
	elseif($prozent>=50)
		$color='yellow';
	elseif($prozent>=15)
		$color='pink';
	else
		$color='red';
		
	if($prozent==0)
		$bordercolor='2px solid red';
	else 
		$bordercolor='1px solid black';
	return '<div style="border: '.$bordercolor.'; width: 300px"><div style="background-color: '.$color.'; width: '.(intval($prozent*3)).'px">&nbsp;'.$prozent.'%</div></div>';
}
$content.= "\n<table>";
$content.= "\n<tr><th>Studiengang/Semester</th><th></th><th></th><th>Lehreinheiten</th><th></th><th>Stunden</th></tr>";
foreach($stg->result as $row_stg)
{
	$content.= "\n<tr><td colspan='2'><h3>".$row_stg->kuerzel.'</h3></td></tr>';
	$qry = "SELECT count(*) as anzahl, semester 
			FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
			WHERE studiengang_kz='$row_stg->studiengang_kz' AND studiensemester_kurzbz='$stsem' 
			AND lehreinheit_id IN(SELECT lehreinheit_id FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id)
			AND tbl_lehreinheit.lehre
			GROUP BY semester
			ORDER BY semester ASC";

	if($result_sem = $db->db_query($qry))
	{
		//echo $qry;
		
		while($row_sem = $db->db_fetch_object($result_sem))
		{
			$content.= '<tr><td>';
			$content.= $row_sem->semester.'.Semester </td><td>';
			$qry = "SELECT count(*) as verplant FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
					WHERE studiengang_kz='$row_stg->studiengang_kz' AND studiensemester_kurzbz='$stsem' AND semester='$row_sem->semester' AND tbl_lehreinheit.lehre
					AND lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_".$variable->variable->db_stpl_table." WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id)
					AND lehreinheit_id IN(SELECT lehreinheit_id FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id)";
			//echo $qry;
			if($result_verplant = $db->db_query($qry))
			{
				if($row_verplant = $db->db_fetch_object($result_verplant))
				{
					$gesamt+=$row_sem->anzahl;
					$gesamt_verplant+=$row_verplant->verplant;
					$prozent = round($row_verplant->verplant*100/$row_sem->anzahl,2);
					$content.= '('.$row_verplant->verplant.'/'.$row_sem->anzahl.')';
					$content.= '</td><td></td><td>';
					$content.= drawprogress($prozent);
				}
			}
			$content.= '</td><td width="20px"></td><td>';
			
			$qry = "SELECT sum(planstunden) as planstunden
					FROM 
						lehre.tbl_lehreinheit 
						JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
						JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
					WHERE
						tbl_lehrveranstaltung.studiengang_kz='$row_stg->studiengang_kz' AND
						tbl_lehrveranstaltung.semester='$row_sem->semester' AND
						tbl_lehreinheit.studiensemester_kurzbz='$stsem' AND
						tbl_lehreinheit.lehre";
			$ps=0;
			if($result_ps = $db->db_query($qry))
			{
				if($row_ps = $db->db_fetch_object($result_ps))
				{
					$gesamt_ps+=$row_ps->planstunden;
					$ps = $row_ps->planstunden;
				}
			}
			
			$qry = "SELECT count(*) as verplant
					FROM (SELECT distinct datum, stunde, tbl_lehreinheit.unr, tbl_".$variable->variable->db_stpl_table.".mitarbeiter_uid
					FROM 
						lehre.tbl_lehreinheit 
						JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
						JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
						JOIN lehre.tbl_".$variable->variable->db_stpl_table." USING(lehreinheit_id)
					WHERE
						tbl_lehrveranstaltung.studiengang_kz='$row_stg->studiengang_kz' AND
						tbl_lehrveranstaltung.semester='$row_sem->semester' AND
						tbl_lehreinheit.studiensemester_kurzbz='$stsem' AND
						tbl_lehreinheit.lehre
					) a";
			$stdverplant=0;
			//echo $qry;
			if($result_std = $db->db_query($qry))
			{
				if($row_std = $db->db_fetch_object($result_std))
				{
					$gesamt_ps_verplant+=$row_std->verplant;
					$stdverplant = $row_std->verplant;
				}
			}
			$content.= "($stdverplant/$ps)";
			$prozent = round($stdverplant*100/$ps,2);
			$content.= '</td><td>';
			$content.=drawprogress($prozent);
			$content.='</td></tr>';
			//echo $qry;
		}
		$content.='<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
	}
	else 
	{
		$content.= 'Fehler';
	}
}
$content.= '</table>';
$prozent = round($gesamt_verplant*100/$gesamt,2);
echo "<br><hr>\n<table><tr><td><b>Gesamtstatus:</b> (".$gesamt_verplant.'/'.$gesamt.')</td><td width="20px"></td><td>';
echo drawprogress($prozent);
echo "</td></tr></table>\n<hr>";
echo $content;
?>
</body>
</html>