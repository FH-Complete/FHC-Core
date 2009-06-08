<?php
require_once('../../vilesci/config.inc.php');
require_once('../../include/studiengang.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Failed to Connect');


$stg_obj = new studiengang($conn);
$stg_obj->getAll(null, false);
$stg_arr = array();

foreach ($stg_obj->result as $stg) 
	$stg_arr[$stg->studiengang_kz] = $stg->kuerzel;	

echo '<html>
<head>
<title>Studiengang - Details</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head><body>';


// ********* Studenten LAND ************
echo '<h2>Studenten LAND</h2>';

$qry = "SELECT 
			studiengang_kz, studiensemester_kurzbz, kurztext as geburtsnation, geschlecht, count(*) as anzahl
		FROM 
			public.tbl_person, public.tbl_prestudent, public.tbl_prestudentstatus, bis.tbl_nation 
		WHERE
			tbl_person.person_id=tbl_prestudent.person_id AND
			tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id AND
			tbl_prestudentstatus.status_kurzbz='Student' AND
			tbl_nation.nation_code=tbl_person.geburtsnation
		GROUP BY studiengang_kz, studiensemester_kurzbz, kurztext, geschlecht";

echo '<table><tr><th>Studiengang</th><th>Studiensemester</th><th>Land</th><th>m/w</th><th>Anzahl</th></tr>';

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		echo '<tr>';
		echo '<td>'.$stg_arr[$row->studiengang_kz].'</td>';
		echo '<td>'.$row->studiensemester_kurzbz.'</td>';
		echo '<td>'.$row->geburtsnation.'</td>';
		echo '<td>'.$row->geschlecht.'</td>';
		echo '<td>'.$row->anzahl.'</td>';
		echo '</tr>';
	}
}

echo '</table>';

// ********* Bewerber LAND ************
echo '<br><br><h2>Bewerber LAND</h2>';

$qry = "SELECT 
			studiengang_kz, studiensemester_kurzbz, kurztext as geburtsnation, geschlecht, count(*) as anzahl
		FROM 
			public.tbl_person, public.tbl_prestudent, public.tbl_prestudentstatus, bis.tbl_nation 
		WHERE
			tbl_person.person_id=tbl_prestudent.person_id AND
			tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id AND
			tbl_prestudentstatus.status_kurzbz='Bewerber' AND
			tbl_nation.nation_code=tbl_person.geburtsnation
		GROUP BY studiengang_kz, studiensemester_kurzbz, kurztext, geschlecht";

echo '<table><tr><th>Studiengang</th><th>Studiensemester</th><th>Land</th><th>m/w</th><th>Anzahl</th></tr>';

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		echo '<tr>';
		echo '<td>'.$stg_arr[$row->studiengang_kz].'</td>';
		echo '<td>'.$row->studiensemester_kurzbz.'</td>';
		echo '<td>'.$row->geburtsnation.'</td>';
		echo '<td>'.$row->geschlecht.'</td>';
		echo '<td>'.$row->anzahl.'</td>';
		echo '</tr>';
	}
}

echo '</table>';


// ********* Studenten Bundesland ************
echo '<br><br><h2>Studenten Bundesland</h2>';

$qry = "SELECT 
			studiengang_kz, studiensemester_kurzbz, bulabez, geschlecht, count(*) as anzahl
		FROM 
			public.tbl_person, public.tbl_prestudent, public.tbl_prestudentstatus, public.tbl_adresse, bis.tbl_gemeinde
		WHERE
			tbl_person.person_id=tbl_prestudent.person_id AND
			tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id AND
			tbl_prestudentstatus.status_kurzbz='Student' AND
			tbl_adresse.person_id=tbl_person.person_id AND
			tbl_adresse.plz=tbl_gemeinde.plz AND
			tbl_person.geburtsnation='A'
		GROUP BY studiengang_kz, studiensemester_kurzbz, bulabez, geschlecht";

echo '<table><tr><th>Studiengang</th><th>Studiensemester</th><th>Land</th><th>m/w</th><th>Anzahl</th></tr>';

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		echo '<tr>';
		echo '<td>'.$stg_arr[$row->studiengang_kz].'</td>';
		echo '<td>'.$row->studiensemester_kurzbz.'</td>';
		echo '<td>'.$row->bulabez.'</td>';
		echo '<td>'.$row->geschlecht.'</td>';
		echo '<td>'.$row->anzahl.'</td>';
		echo '</tr>';
	}
}

echo '</table>';

// ********* Studenten Bundesland ************
echo '<br><br><h2>Bewerber Bundesland</h2>';

$qry = "SELECT 
			studiengang_kz, studiensemester_kurzbz, bulabez, geschlecht, count(*) as anzahl
		FROM 
			public.tbl_person, public.tbl_prestudent, public.tbl_prestudentstatus, public.tbl_adresse, bis.tbl_gemeinde
		WHERE
			tbl_person.person_id=tbl_prestudent.person_id AND
			tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id AND
			tbl_prestudentstatus.status_kurzbz='Bewerber' AND
			tbl_adresse.person_id=tbl_person.person_id AND
			tbl_adresse.plz=tbl_gemeinde.plz AND
			tbl_person.geburtsnation='A'
		GROUP BY studiengang_kz, studiensemester_kurzbz, bulabez, geschlecht";

echo '<table><tr><th>Studiengang</th><th>Studiensemester</th><th>Land</th><th>m/w</th><th>Anzahl</th></tr>';

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		echo '<tr>';
		echo '<td>'.$stg_arr[$row->studiengang_kz].'</td>';
		echo '<td>'.$row->studiensemester_kurzbz.'</td>';
		echo '<td>'.$row->bulabez.'</td>';
		echo '<td>'.$row->geschlecht.'</td>';
		echo '<td>'.$row->anzahl.'</td>';
		echo '</tr>';
	}
}

echo '</table>';


?>