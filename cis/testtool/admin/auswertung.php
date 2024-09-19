<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *		  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *		  Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *		  Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
/**
 * Auswertung fuer den Reihungstest
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/Excel/excel.php');
require_once('../../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('lehre/reihungstest'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if(isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete']=='prestudent')
{
	$search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
	if (is_null($search) ||$search=='')
		exit();
	$qry = "SELECT
				nachname, vorname, prestudent_id, student_uid,
				UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) as stg,
				get_rolle_prestudent(prestudent_id, null) as status
			FROM
				public.tbl_person
				JOIN public.tbl_prestudent USING(person_id)
				JOIN public.tbl_studiengang USING(studiengang_kz)
				LEFT JOIN public.tbl_student USING (prestudent_id)
			WHERE
				lower(nachname) like '%".$db->db_escape(mb_strtolower($search))."%' OR
				lower(vorname) like '%".$db->db_escape(mb_strtolower($search))."%' OR
				lower(nachname || ' ' || vorname) like '%".$db->db_escape(mb_strtolower($search))."%' OR
				lower(vorname || ' ' || nachname) like '%".$db->db_escape(mb_strtolower($search))."%' OR
				prestudent_id::text like '%".$db->db_escape(mb_strtolower($search))."%' OR
				student_uid::text like '%".$db->db_escape(mb_strtolower($search))."%'
				ORDER BY nachname,vorname,stg
				LIMIT 10
			";
	if($result = $db->db_query($qry))
	{
		$result_obj = array();
		while($row = $db->db_fetch_object($result))
		{
			$item['vorname']=html_entity_decode($row->vorname);
			$item['nachname']=html_entity_decode($row->nachname);
			$item['stg']=html_entity_decode($row->stg);
			$item['status']=html_entity_decode($row->status);
			$item['prestudent_id']=html_entity_decode($row->prestudent_id);
			$item['student_uid']=html_entity_decode($row->student_uid);
			$result_obj[]=$item;
		}
		echo json_encode($result_obj);
	}
	exit;
}

function sortByField($multArray,$sortField,$desc=true)
{
	$tmpKey='';
	$ResArray=array();

	if(!is_array($multArray))
		return array();

	$maIndex=array_keys($multArray);
	$maSize=count($multArray)-1;

	for($i=0; $i < $maSize ; $i++)
	{
		$minElement=$i;
		$tempMin=$multArray[$maIndex[$i]]->$sortField;
		$tmpKey=$maIndex[$i];
		for($j=$i+1; $j <= $maSize; $j++)
		{
			if($multArray[$maIndex[$j]]->$sortField < $tempMin )
			{
				$minElement=$j;
				$tmpKey=$maIndex[$j];
				$tempMin=$multArray[$maIndex[$j]]->$sortField;
			}
		}
		$maIndex[$minElement]=$maIndex[$i];
		$maIndex[$i]=$tmpKey;
	}

	if($desc)
		for($j=0;$j<=$maSize;$j++)
			$ResArray[$maIndex[$j]]=$multArray[$maIndex[$j]];
	else
		for($j=$maSize;$j>=0;$j--)
			$ResArray[$maIndex[$j]]=$multArray[$maIndex[$j]];

	return $ResArray;
}

$ergebnis='';
$gebiet=array();
$kategorie=array();
$erg_kat=array();
$datum_obj = new datum();
$zgv_arr=array();
$zgvma_arr=array();

$datum_von = isset($_REQUEST['datum_von'])?$_REQUEST['datum_von']:'';
$datum_bis = isset($_REQUEST['datum_bis'])?$_REQUEST['datum_bis']:'';
$reihungstest = isset($_REQUEST['reihungstest'])?$_REQUEST['reihungstest']:'';
$studiengang = isset($_REQUEST['studiengang'])?$_REQUEST['studiengang']:'';
$semester = isset($_REQUEST['semester'])?$_REQUEST['semester']:'';
$prestudent_id = isset($_REQUEST['prestudent_id'])?$_REQUEST['prestudent_id']:'';
$format = (isset($_REQUEST['format'])?$_REQUEST['format']:'');

if($reihungstest!='' && !is_numeric($reihungstest))
	die('ReihungstestID ist ungueltig');
if($studiengang!='' && !is_numeric($studiengang))
	die('Studiengang ist ungueltig');
if($semester!='' && !is_numeric($semester))
	die('Semester ist ungueltig');
if($prestudent_id!='' && !is_numeric($prestudent_id))
	die('PrestudentID ist ungueltig');
if(($reihungstest=='' && isset($_REQUEST['reihungstest'])) && $studiengang=='' && $semester=='' && $prestudent_id=='' && $datum_von=='' && $datum_bis=='')
	die('Waehlen Sie bitte mindestens eine der Optionen aus');

if($datum_von!='')
	$datum_von = $datum_obj->formatDatum($datum_von, 'Y-m-d');
if($datum_bis!='')
	$datum_bis = $datum_obj->formatDatum($datum_bis, 'Y-m-d');

$zgv_arr['']='';
$qry = "SELECT * FROM bis.tbl_zgv";
if($result = $db->db_query($qry))
	while($row = $db->db_fetch_object($result))
		$zgv_arr[$row->zgv_code]=$row->zgv_kurzbz;

$zgvma_arr['']='';
$qry = "SELECT * FROM bis.tbl_zgvmaster";
if($result = $db->db_query($qry))
	while($row = $db->db_fetch_object($result))
		$zgvma_arr[$row->zgvmas_code]=$row->zgvmas_kurzbz;

// Reihungstests laden
$sql_query = "SELECT * FROM public.tbl_reihungstest WHERE date_part('year',datum)=date_part('year',now()) ";

// Wenn Reihungstest ID gesetzt ist, diesen Test zusaetzlich laden, um auch jene außerhalbs des Datumszeitraums zu erwischen
if ($reihungstest != '')
$sql_query .= "UNION SELECT * FROM public.tbl_reihungstest WHERE reihungstest_id=".$db->db_add_param($reihungstest, FHC_INTEGER);

$sql_query .= " ORDER BY datum,uhrzeit";

if(!($result=$db->db_query($sql_query)))
	die($db->db_last_error());

while ($row=$db->db_fetch_object($result))
{
	if(!isset($rtest[$row->reihungstest_id]))
		$rtest[$row->reihungstest_id]=new stdClass();
	$rtest[$row->reihungstest_id]->reihungstest_id=$row->reihungstest_id;
	$rtest[$row->reihungstest_id]->studiengang_kz=$row->studiengang_kz;
	$rtest[$row->reihungstest_id]->ort_kurzbz=$row->ort_kurzbz;
	$rtest[$row->reihungstest_id]->anmerkung=$row->anmerkung;
	$rtest[$row->reihungstest_id]->datum=$row->datum;
	$rtest[$row->reihungstest_id]->uhrzeit=$row->uhrzeit;
}

if (isset($_REQUEST['reihungstest']))
{
	// Vorkommende Gebiete laden
	$sql_query="
		SELECT DISTINCT gebiet_id, gebiet, vw_auswertung_ablauf.reihung
		FROM
			testtool.vw_auswertung_ablauf
			JOIN public.tbl_prestudent USING(prestudent_id)
			JOIN public.tbl_rt_person USING(person_id)
			JOIN public.tbl_reihungstest ON(vw_auswertung_ablauf.reihungstest_id=tbl_reihungstest.reihungstest_id)
			JOIN testtool.tbl_ablauf USING(gebiet_id)
		WHERE 1=1";
	if($reihungstest!='')
		$sql_query.=" AND vw_auswertung_ablauf.reihungstest_id=".$db->db_add_param($reihungstest, FHC_INTEGER);
	if($datum_von!='')
		$sql_query.=" AND tbl_reihungstest.datum>=".$db->db_add_param($datum_von);
	if($datum_bis!='')
		$sql_query.=" AND tbl_reihungstest.datum<=".$db->db_add_param($datum_bis);
	if($studiengang!='')
		$sql_query.=" AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang, FHC_INTEGER);
	if($semester!='')
		$sql_query.=" AND tbl_ablauf.semester=".$db->db_add_param($semester, FHC_INTEGER)." AND tbl_ablauf.studiengang_kz=tbl_prestudent.studiengang_kz";
	if($prestudent_id!='')
		$sql_query.=" AND prestudent_id=".$db->db_add_param($prestudent_id, FHC_INTEGER);

	$sql_query.=" ORDER BY vw_auswertung_ablauf.reihung, gebiet_id";

	if(!($result=$db->db_query($sql_query)))
		 die($db->db_last_error());
	while ($row=$db->db_fetch_object($result))
	{
		if(!isset($gebiet[$row->gebiet_id]))
			$gebiet[$row->gebiet_id]=new stdClass();
		$gebiet[$row->gebiet_id]->name=$row->gebiet;
		$gebiet[$row->gebiet_id]->gebiet_id=$row->gebiet_id;
	}

	// Alle Personen und deren Ergebnisse laden
	$sql_query="SELECT DISTINCT ON (pruefling_id,vw_auswertung_ablauf.gebiet_id)
					*
				FROM
					testtool.vw_auswertung_ablauf
					JOIN public.tbl_prestudent USING(prestudent_id)
					JOIN public.tbl_reihungstest ON(vw_auswertung_ablauf.reihungstest_id=tbl_reihungstest.reihungstest_id)
					JOIN testtool.tbl_ablauf ON(tbl_ablauf.gebiet_id=vw_auswertung_ablauf.gebiet_id)
				WHERE 1=1 AND tbl_ablauf.studiengang_kz=tbl_prestudent.studiengang_kz";
	if($reihungstest!='')
		$sql_query.=" AND vw_auswertung_ablauf.reihungstest_id=".$db->db_add_param($reihungstest, FHC_INTEGER);
	if($datum_von!='')
		$sql_query.=" AND tbl_reihungstest.datum>=".$db->db_add_param($datum_von);
	if($datum_bis!='')
		$sql_query.=" AND tbl_reihungstest.datum<=".$db->db_add_param($datum_bis);
	if($studiengang!='')
		$sql_query.=" AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang, FHC_INTEGER);
	if($semester!='')
		$sql_query.=" AND tbl_ablauf.semester=".$db->db_add_param($semester, FHC_INTEGER);
	if($prestudent_id!='')
		$sql_query.=" AND prestudent_id=".$db->db_add_param($prestudent_id, FHC_INTEGER);


	if(!($result=$db->db_query($sql_query)))
		die($db->db_last_error());

	while ($row=$db->db_fetch_object($result))
	{
		if(!isset($ergebnis[$row->pruefling_id]))
			$ergebnis[$row->pruefling_id]=new stdClass();

		$ergebnis[$row->pruefling_id]->prestudent_id=$row->prestudent_id;
		$ergebnis[$row->pruefling_id]->pruefling_id=$row->pruefling_id;
		$ergebnis[$row->pruefling_id]->nachname=$row->nachname;
		$ergebnis[$row->pruefling_id]->vorname=$row->vorname;
		$ergebnis[$row->pruefling_id]->gebdatum=$row->gebdatum;
		$ergebnis[$row->pruefling_id]->geschlecht=$row->geschlecht;
		$ergebnis[$row->pruefling_id]->idnachweis=$row->idnachweis;
		$ergebnis[$row->pruefling_id]->registriert=$row->registriert;
		$ergebnis[$row->pruefling_id]->stg_kurzbz=$row->stg_kurzbz;
		$ergebnis[$row->pruefling_id]->stg_bez=$row->stg_bez;
		$ergebnis[$row->pruefling_id]->semester=$row->semester;
		$ergebnis[$row->pruefling_id]->zgv=$row->zgv_code;
		$ergebnis[$row->pruefling_id]->zgvma=$row->zgvmas_code;

		if(!isset($ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]))
			$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]=new stdClass();

		$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->name=$row->gebiet;
		$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->punkte=(($row->punkte>=$row->maxpunkte)?$row->maxpunkte:$row->punkte);
		//wenn maxpunkte ueberschritten wurde -> 100%
		if($row->punkte>=$row->maxpunkte)
			$prozent=100;
		else
			$prozent = ($row->punkte/$row->maxpunkte)*100;

		if($row->punkte>=$row->maxpunkte)
			$punkte=$row->maxpunkte;
		else
			$punkte=$row->punkte;

		$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->prozent=$prozent;
		$ergebnis[$row->pruefling_id]->gebiet[$row->gebiet_id]->punkte=$punkte;

		if (isset($ergebnis[$row->pruefling_id]->gesamt))
			$ergebnis[$row->pruefling_id]->gesamt+=$prozent*$row->gewicht;
		else
			$ergebnis[$row->pruefling_id]->gesamt=$prozent*$row->gewicht;

		if (isset($ergebnis[$row->pruefling_id]->gesamtpunkte))
			$ergebnis[$row->pruefling_id]->gesamtpunkte+=$punkte;
		else
			$ergebnis[$row->pruefling_id]->gesamtpunkte=$punkte;
	}

	$ergb=sortByField($ergebnis,'gesamt');

	// Vorkommende Kategorien laden
	$sql_query="SELECT
					DISTINCT kategorie_kurzbz,
					(SELECT sum(punkte) FROM testtool.tbl_vorschlag JOIN testtool.tbl_frage USING(frage_id)
					 WHERE tbl_frage.kategorie_kurzbz=vw_auswertung_kategorie_semester.kategorie_kurzbz) as gesamtpunkte
				 FROM
				 	testtool.vw_auswertung_kategorie_semester
				 	JOIN public.tbl_prestudent USING(prestudent_id)
					JOIN public.tbl_reihungstest ON(vw_auswertung_kategorie_semester.reihungstest_id=tbl_reihungstest.reihungstest_id)
				WHERE 1=1";
	if($reihungstest!='')
		$sql_query.=" AND vw_auswertung_kategorie_semester.reihungstest_id=".$db->db_add_param($reihungstest, FHC_INTEGER);
	if($datum_von!='')
		$sql_query.=" AND tbl_reihungstest.datum>=".$db->db_add_param($datum_von);
	if($datum_bis!='')
		$sql_query.=" AND tbl_reihungstest.datum<=".$db->db_add_param($datum_bis);
	if($studiengang!='')
		$sql_query.=" AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang, FHC_INTEGER);
	if($prestudent_id!='')
		$sql_query.=" AND vw_auswertung_kategorie_semester.prestudent_id=".$db->db_add_param($prestudent_id, FHC_INTEGER);

	if(!($result=$db->db_query($sql_query)))
		die($db->db_last_error());
	$gesamtpunkte=array();

	while ($row=$db->db_fetch_object($result))
	{
		if(!isset($kategorie[$row->kategorie_kurzbz]))
			$kategorie[$row->kategorie_kurzbz] = new stdClass();
		$gesamtpunkte[$row->kategorie_kurzbz]=$row->gesamtpunkte;
		$kategorie[$row->kategorie_kurzbz]->name=$row->kategorie_kurzbz;
	}

	// Ergebnisse laden
	$sql_query="
		SELECT
			vw_auswertung_kategorie_semester.*,
			(SELECT typ FROM testtool.tbl_kriterien
			 WHERE gebiet_id=vw_auswertung_kategorie_semester.gebiet_id AND punkte=vw_auswertung_kategorie_semester.punkte
			 AND kategorie_kurzbz=vw_auswertung_kategorie_semester.kategorie_kurzbz) as typ,
			tbl_prestudent.zgv_code, tbl_prestudent.zgvmas_code
		FROM
			testtool.vw_auswertung_kategorie_semester
			JOIN public.tbl_prestudent USING(prestudent_id)
			JOIN public.tbl_reihungstest ON(vw_auswertung_kategorie_semester.reihungstest_id=tbl_reihungstest.reihungstest_id)
		WHERE 1=1";
	if($reihungstest!='')
		$sql_query.=" AND vw_auswertung_kategorie_semester.reihungstest_id=".$db->db_add_param($reihungstest, FHC_INTEGER);
	if($datum_von!='')
		$sql_query.=" AND tbl_reihungstest.datum>=".$db->db_add_param($datum_von);
	if($datum_bis!='')
		$sql_query.=" AND tbl_reihungstest.datum<=".$db->db_add_param($datum_bis);
	if($studiengang!='')
		$sql_query.=" AND tbl_prestudent.studiengang_kz=".$db->db_add_param($studiengang, FHC_INTEGER);
	//if($semester!='')
	//	$sql_query.=" AND vw_auswertung_kategorie_semester.semester='".addslashes($semester)."'"; Auskommentiert, damit bei der Persönlichkeitsauswertung kein Kandidat verloren geht
	if($prestudent_id!='')
		$sql_query.=" AND prestudent_id=".$db->db_add_param($prestudent_id, FHC_INTEGER);

	$sql_query.=" ORDER BY nachname, vorname";

	if(!($result=$db->db_query($sql_query)))
		 die($db->db_last_error());

	while ($row=$db->db_fetch_object($result))
	{
		if(!isset($erg_kat[$row->pruefling_id]))
			$erg_kat[$row->pruefling_id]=new stdClass();
		if(!isset($erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]))
			$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]=new stdClass();

		$erg_kat[$row->pruefling_id]->pruefling_id=$row->pruefling_id;
		$erg_kat[$row->pruefling_id]->prestudent_id=$row->prestudent_id;
		$erg_kat[$row->pruefling_id]->nachname=$row->nachname;
		$erg_kat[$row->pruefling_id]->vorname=$row->vorname;
		$erg_kat[$row->pruefling_id]->gebdatum=$row->gebdatum;
		$erg_kat[$row->pruefling_id]->geschlecht=$row->geschlecht;
		$erg_kat[$row->pruefling_id]->idnachweis=$row->idnachweis;
		$erg_kat[$row->pruefling_id]->registriert=$row->registriert;
		$erg_kat[$row->pruefling_id]->stg_kurzbz=$row->stg_kurzbz;
		$erg_kat[$row->pruefling_id]->stg_bez=$row->stg_bez;
		$erg_kat[$row->pruefling_id]->semester=$row->semester;
		$erg_kat[$row->pruefling_id]->zgv = $row->zgv_code;
		$erg_kat[$row->pruefling_id]->zgvma = $row->zgvmas_code;
		$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->name=$row->kategorie_kurzbz;
		$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->typ=$row->typ;
		$erg_kat[$row->pruefling_id]->kategorie[$row->kategorie_kurzbz]->punkte=number_format($row->punkte,2).'/'.number_format($gesamtpunkte[$row->kategorie_kurzbz],2);
	}
}

//Studiengaenge laden
$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz', false);
$stg_arr = array();

foreach($stg_obj->result as $row)
	$stg_arr[$row->studiengang_kz]=$row->kuerzel;


if(isset($_REQUEST['format']) && $_REQUEST['format']=='xls')
{
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Auswertung ".((isset ($_REQUEST['reihungstest']) && $_REQUEST['reihungstest']!='')?$stg_arr[$rtest[$reihungstest]->studiengang_kz]." ".$datum_obj->formatDatum($rtest[$reihungstest]->datum,'d.m.Y'):'aller Reihungstests').".xls");
	$workbook->setVersion(8);
	$workbook->setCustomColor (15,192,192,192); //Setzen der HG-Farbe Hellgrau
	$workbook->setCustomColor (22,193,0,0); //Setzen der HG-Farbe Dunkelrot
	// Creating a worksheet
	$titel_studiengang = (isset ($_REQUEST['studiengang']) && $_REQUEST['studiengang']!='');
	$titel_semester = (isset ($_REQUEST['semester']) && $_REQUEST['semester']!='');

	$worksheet =& $workbook->addWorksheet("Technischer Teil "	.($titel_studiengang?$stg_arr[$_REQUEST['studiengang']]:'').($titel_semester?' '.$semester.'.Semester':''));
	$worksheet->setInputEncoding('utf-8');
	$worksheet->setZoom (85);
	//Formate Definieren
	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	$format_bold->setAlign("center");
	$format_bold->setFgColor(15);
	$format_bold->setVAlign('vcenter');

	$format_bold_border =& $workbook->addFormat();
	$format_bold_border->setBold();
	$format_bold_border->setAlign("center");
	$format_bold_border->setFgColor(15);
	$format_bold_border->setBorder(1);
	$format_bold_border->setBorderColor('white');

	$format_date =& $workbook->addFormat();
	$format_date->setNumFormat('YYYY-MM-DD');

	$format_registriert =& $workbook->addFormat();
	$format_registriert->setNumFormat('YYYY-MM-DD hh:mm:ss');

	$format_punkte =& $workbook->addFormat();
	$format_punkte->setNumFormat('0.00');

	$format_punkte_rot =& $workbook->addFormat();
	$format_punkte_rot->setNumFormat('0.00');
	$format_punkte_rot->setColor ('22');

	$format_prozent =& $workbook->addFormat();
	$format_prozent->setNumFormat('0.00%');

	$format_prozent_rot =& $workbook->addFormat();
	$format_prozent_rot->setNumFormat('0.00%');
	$format_prozent_rot->setColor ('22');

	$format_male =& $workbook->addFormat();
	$format_male->setColor ('blue');

	$format_female =& $workbook->addFormat();
	$format_female->setColor ('magenta');

	$spalte=0;
	$zeile=0;

	$worksheet->write(0,$spalte,'PrestudentIn_ID', $format_bold);
	$worksheet->mergeCells(0,0,1,0);
	$maxlength[0]=15;
	$worksheet->write(0,++$spalte,'Nachname', $format_bold);
	$worksheet->mergeCells(0,1,1,1);
	$maxlength[1]=15;
	$worksheet->write(0,++$spalte,'Vorname', $format_bold);
	$worksheet->mergeCells(0,2,1,2);
	$maxlength[2]=15;
	$worksheet->write(0,++$spalte,'GebDatum', $format_bold);
	$worksheet->mergeCells(0,3,1,3);
	$maxlength[3]=10;
	$worksheet->write(0,++$spalte,'G', $format_bold);
	$worksheet->mergeCells(0,4,1,4);
	$maxlength[4]=2;
	$worksheet->write(0,++$spalte,'Registriert', $format_bold);
	$worksheet->mergeCells(0,5,1,5);
	$maxlength[5]=18;
	$worksheet->write(0,++$spalte,'STG', $format_bold);
	$worksheet->mergeCells(0,6,1,6);
	$maxlength[6]=4;
	$worksheet->write(0,++$spalte,'Studiengang', $format_bold);
	$worksheet->mergeCells(0,7,1,7);
	$maxlength[7]=25;
	$worksheet->write(0,++$spalte,'S', $format_bold);
	$worksheet->mergeCells(0,8,1,8);
	$maxlength[8]=2;
	$worksheet->write(0,++$spalte,'ZGV', $format_bold);
	$worksheet->mergeCells(0,9,1,9);
	$maxlength[9]=20;
	$worksheet->write(0,++$spalte,'ZGV MA', $format_bold);
	$worksheet->mergeCells(0,10,1,10);
	$maxlength[10]=20;

	$spalte=9;
	$zeile=0;

	foreach ($gebiet AS $gbt)
	{
		++$spalte;
		$worksheet->write($zeile,++$spalte,strip_tags($gbt->name), $format_bold_border);
		$worksheet->mergeCells($zeile,$spalte,0,$spalte+1);
		$maxlength[$spalte]=10;
	}
	$worksheet->write($zeile,++$spalte+1,'Gesamt', $format_bold_border);
	$worksheet->mergeCells($zeile,++$spalte,0,$spalte+1);
	$maxlength[$spalte]=12;

	$spalte=10;
	$zeile=0;

	foreach ($gebiet AS $gbt)
	{
		$worksheet->write($zeile+1,++$spalte,'Punkte', $format_bold_border);
		$worksheet->write($zeile+1,++$spalte,'Prozent', $format_bold_border);
		$maxlength[$spalte]=10;
	}
	$worksheet->write($zeile+1,++$spalte,'Punkte', $format_bold_border);
	$worksheet->write($zeile+1,++$spalte,'Prozent', $format_bold_border);
	$maxlength[$spalte]=10;

	$maxspalten=$spalte;

	$zeile=1;
	$spalte=0;

	if(isset($ergb))
	{
		foreach ($ergb AS $erg)
		{
			$zeile++;
			$spalte=0;
			$worksheet->write($zeile,$spalte,$erg->prestudent_id);
			$worksheet->write($zeile,++$spalte,$erg->nachname);
			$worksheet->write($zeile,++$spalte,$erg->vorname);
			$worksheet->write($zeile,++$spalte,$erg->gebdatum, $format_date);
			if($erg->geschlecht=='m')
				$worksheet->write($zeile,++$spalte,$erg->geschlecht, $format_male);
			else
				$worksheet->write($zeile,++$spalte,$erg->geschlecht, $format_female);
			$worksheet->write($zeile,++$spalte,$erg->registriert, $format_registriert);
			$worksheet->write($zeile,++$spalte,$erg->stg_kurzbz);
			$worksheet->write($zeile,++$spalte,$erg->stg_bez);
			$worksheet->write($zeile,++$spalte,$erg->semester);
			$worksheet->write($zeile,++$spalte,$zgv_arr[$erg->zgv]);
			$worksheet->write($zeile,++$spalte,$zgvma_arr[$erg->zgvma]);
			foreach ($gebiet AS $gbt)
				if (isset($erg->gebiet[$gbt->gebiet_id]))
				{
					if($erg->gebiet[$gbt->gebiet_id]->punkte!='' && $erg->gebiet[$gbt->gebiet_id]->punkte!='0')
					$worksheet->writeNumber($zeile,++$spalte,$erg->gebiet[$gbt->gebiet_id]->punkte,$format_punkte);
					else
					$worksheet->writeNumber($zeile,++$spalte,$erg->gebiet[$gbt->gebiet_id]->punkte,$format_punkte_rot);
					if($erg->gebiet[$gbt->gebiet_id]->prozent!='0%')
					$worksheet->writeNumber($zeile,++$spalte,$erg->gebiet[$gbt->gebiet_id]->prozent/100,$format_prozent);
					else
					$worksheet->writeNumber($zeile,++$spalte,$erg->gebiet[$gbt->gebiet_id]->prozent/100,$format_prozent_rot);
				}
				else
				{
					$worksheet->write($zeile,++$spalte,'');
					$worksheet->write($zeile,++$spalte,'');
				}
			$worksheet->writeNumber($zeile,++$spalte,$erg->gesamtpunkte, $format_punkte);
			$worksheet->writeNumber($zeile,++$spalte,$erg->gesamt, $format_punkte);
		}
	}

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite);

	if(isset($erg_kat) && count($erg_kat)>0)
	{
	// Creating second worksheet
	$worksheet2 =& $workbook->addWorksheet("Persoenlichkeit");
	$worksheet2->setInputEncoding('utf-8');
	$worksheet2->setZoom (85);

	$spalte=0;
	$zeile=0;

	$worksheet2->write(0,$spalte,'PrestudentIn_ID', $format_bold);
	$worksheet2->mergeCells(0,0,1,0);
	$maxlength[0]=15;
	$worksheet2->write(0,++$spalte,'Nachname', $format_bold);
	$worksheet2->mergeCells(0,1,1,1);
	$maxlength[1]=15;
	$worksheet2->write(0,++$spalte,'Vorname', $format_bold);
	$worksheet2->mergeCells(0,2,1,2);
	$maxlength[2]=15;
	$worksheet2->write(0,++$spalte,'GebDatum', $format_bold);
	$worksheet2->mergeCells(0,3,1,3);
	$maxlength[3]=10;
	$worksheet2->write(0,++$spalte,'G', $format_bold);
	$worksheet2->mergeCells(0,4,1,4);
	$maxlength[4]=2;
	$worksheet2->write(0,++$spalte,'Registriert', $format_bold);
	$worksheet2->mergeCells(0,5,1,5);
	$maxlength[5]=18;
	$worksheet2->write(0,++$spalte,'STG', $format_bold);
	$worksheet2->mergeCells(0,6,1,6);
	$maxlength[6]=4;
	$worksheet2->write(0,++$spalte,'Studiengang', $format_bold);
	$worksheet2->mergeCells(0,7,1,7);
	$maxlength[7]=25;
	$worksheet2->write(0,++$spalte,'S', $format_bold);
	$worksheet2->mergeCells(0,8,1,8);
	$maxlength[8]=2;
	$worksheet2->write(0,++$spalte,'ZGV', $format_bold);
	$worksheet2->mergeCells(0,9,1,9);
	$maxlength[9]=20;
	$worksheet2->write(0,++$spalte,'ZGV MA', $format_bold);
	$worksheet2->mergeCells(0,10,1,10);
	$maxlength[10]=20;

	$spalte=9;
	$zeile=0;

	foreach ($kategorie AS $gbt)
	{
		++$spalte;
		$worksheet2->write($zeile,++$spalte,$gbt->name, $format_bold_border);
		$worksheet2->mergeCells($zeile,$spalte,0,$spalte+1);
		$maxlength[$spalte]=10;
	}

	$spalte=10;
	$zeile=0;

	foreach ($kategorie AS $gbt)
	{
		$worksheet2->write($zeile+1,++$spalte,'Punkte', $format_bold_border);
		$worksheet2->write($zeile+1,++$spalte,'Typ', $format_bold_border);
		$maxlength[$spalte]=10;
	}

	$maxspalten=$spalte;

	$zeile=1;
	$spalte=0;

	foreach ($erg_kat AS $erg)
	{
		$zeile++;
		$spalte=0;
		$worksheet2->write($zeile,$spalte,$erg->prestudent_id);
		$worksheet2->write($zeile,++$spalte,$erg->nachname);
		$worksheet2->write($zeile,++$spalte,$erg->vorname);
		$worksheet2->write($zeile,++$spalte,$erg->gebdatum, $format_date);
		if($erg->geschlecht=='m')
				$worksheet2->write($zeile,++$spalte,$erg->geschlecht, $format_male);
			else
				$worksheet2->write($zeile,++$spalte,$erg->geschlecht, $format_female);
		$worksheet2->write($zeile,++$spalte,$erg->registriert, $format_registriert);
		$worksheet2->write($zeile,++$spalte,$erg->stg_kurzbz);
		$worksheet2->write($zeile,++$spalte,$erg->stg_bez);
		$worksheet2->write($zeile,++$spalte,$erg->semester);
		$worksheet2->write($zeile,++$spalte,$zgv_arr[$erg->zgv]);
		$worksheet2->write($zeile,++$spalte,$zgvma_arr[$erg->zgvma]);
		foreach ($kategorie AS $gbt)
		{
			$worksheet2->write($zeile,++$spalte,$erg->kategorie[$gbt->name]->punkte);
			$worksheet2->write($zeile,++$spalte,$erg->kategorie[$gbt->name]->typ);
		}
	}

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet2->setColumn($i, $i, $breite);
	}
	$workbook->close();
}
else
{
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>

	<head>
	<title>Testtool - Auswertung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link type="text/css" rel="stylesheet" href="../../../skin/style.css.php">
	<link href="../../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript">
	$(document).ready(function()
		{
			$( ".datepicker_datum" ).datepicker({
					 changeMonth: true,
					 changeYear: true,
					 dateFormat: "dd.mm.yy",
					 });

			$("#prestudent").autocomplete({
				source: "auswertung.php?autocomplete=prestudent",
				minLength:2,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value=ui.content[i].nachname+" "+ui.content[i].vorname+" "+ui.content[i].stg+" "+ui.content[i].status+" "+ui.content[i].prestudent_id+" ("+ui.content[i].student_uid+")";
						ui.content[i].label=ui.content[i].nachname+" "+ui.content[i].vorname+" "+ui.content[i].stg+" "+ui.content[i].status+" "+ui.content[i].prestudent_id+" ("+ui.content[i].student_uid+")";
					}
				},
				select: function(event, ui)
				{
					//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
					$("#prestudent_id").val(ui.item.prestudent_id);
				}
			});
		});
	</script>
	</head>

	<body>

	<h1>Auswertung Reihungstest</h1>
	<table width="100%">
		<tr>
			<form method="POST">
			<td style="white-space:nowrap; padding: 10px; background-color: #EEE">
				Reihungstest w&auml;hlen:&nbsp;
					<SELECT id="reihungstest" name="reihungstest">
					<OPTION value="">-- keine Auswahl --</OPTION>';
						$selected = '';
						$select = false;
						foreach($rtest as $rt)
						{
							if($rt->reihungstest_id==$reihungstest && !$select)
							{
								$selected = 'selected';
								$select = true;
							}
							elseif ($prestudent_id=='' && $reihungstest=='' && $rt->datum==date('Y-m-d') && $datum_von=='' && $datum_bis=='' && $studiengang=='' && $semester=='' && !$select)
							{
								$selected = 'selected';
								$select = true;
							}
							else
								$selected = '';

							echo '<OPTION value="'.$rt->reihungstest_id.'" '.$selected.'>'.$rt->datum.' '.(isset($stg_arr[$rt->studiengang_kz])?$stg_arr[$rt->studiengang_kz]:'').' '.$rt->ort_kurzbz.' '.$rt->anmerkung."</OPTION>\n";
						}

					echo '</SELECT>
					<br /><hr style="color: #B3B3B3; background-color: #B3B3B3; border:none;">
				Studiengang:
					<SELECT name="studiengang">
						<OPTION value="">Alle</OPTION>';
						foreach ($stg_arr as $kz=>$kurzbz)
						{
							if(isset($_REQUEST['studiengang']) && $_REQUEST['studiengang']==$kz && $_REQUEST['studiengang']!='')
								$selected='selected';
							else
								$selected='';

							echo '<OPTION value="'.$kz.'" '.$selected.'>'.$kurzbz.'</OPTION>';
						}
						echo '</SELECT>
				Semester:
					<SELECT name="semester">
						<OPTION value="">Alle</OPTION>';
						for ($i=1;$i<9;$i++)
						{
							if (isset($semester) && $semester==$i)
								echo "<option value=\"$i\" selected>$i</option>";
							else
								echo "<option value=\"$i\">$i</option>";
						}
						echo '</SELECT>';

						echo ' von Datum: <INPUT class="datepicker_datum" type="text" name="datum_von" maxlength="10" size="10" value="'.$datum_obj->formatDatum($datum_von, 'd.m.Y').'" />&nbsp;';
						echo 'bis Datum: <INPUT class="datepicker_datum" type="text" name="datum_bis" maxlength="10" size="10" value="'.$datum_obj->formatDatum($datum_bis, 'd.m.Y').'" /><br />';
						echo '<hr style="color: #B3B3B3; background-color: #B3B3B3; border:none;">';
						echo 'PrestudentIn: <INPUT id="prestudent" type="text" name="prestudent_id" size="50" value="'.$prestudent_id.'" placeholder="Name, UID oder Prestudent_id eingeben" onInput="document.getElementById(\'reihungstest\').value=\'\'" onkeyup="document.getElementById(\'prestudent_id\').value=this.value"/><input type="hidden" id="prestudent_id" name="prestudent_id" value="'.$prestudent_id.'" />';
			echo '</td>
			<td style="padding: 5px; background-color: #EEE";">
				<INPUT type="submit" value="Auswerten" />
			</td></form>
			<td style="white-space:nowrap; width: 100%; text-align: right;" align="right;">
				<a href="auswertung_detail.php">Auswertung auf Fragenebene</a>
			</td>
		</tr>
		<tr><td style="padding: 5px;">';
		echo 'Auswahl: <strong>';
		if (isset ($_REQUEST['studiengang']) && $_REQUEST['studiengang']!='')
			echo $stg_arr[$_REQUEST['studiengang']].' ';
		else
			echo 'Alle ';
		if (isset ($_REQUEST['semester']) && $_REQUEST['semester']!='')
			echo $semester.'. Semester ';
		if ($datum_von!='')
			echo 'von '.$datum_obj->formatDatum($datum_von, 'd.m.Y');
		if ($datum_bis!='')
			echo ' bis '.$datum_obj->formatDatum($datum_bis, 'd.m.Y');
		if ($prestudent_id!='')
			echo ' PrestudentID: '.$prestudent_id;

		echo '</strong>';
		echo '</td>
		<td style="padding: 5px; background-color: #EEE";">
		<a href="auswertung.php?studiengang='.$studiengang.'&semester='.$semester.'&datum_von='.$datum_von.'&datum_bis='.$datum_bis.'&prestudent_id='.$prestudent_id.'&reihungstest='.$reihungstest.'&format=xls"><img src="../../../skin/images/xls_icon.png" alt="Excel Icon"> Export</a>
		</td></tr>
	</table><br />';

	if (isset($_REQUEST['reihungstest']))
	{

		echo '<h1>Technischer Teil</h1>

		<table id="zeitsperren">
			<tr>
				<th rowspan="2">PrestudentIn_ID</th><th rowspan="2">Nachname</th><th rowspan="2">Vornamen</th>
				<th rowspan="2">GebDatum</th><th rowspan="2">G</th>
				<th rowspan="2">ZGV</th>
				<th rowspan="2">ZGV MA</th>
				<th rowspan="2">Registriert</th><th rowspan="2">STG</th><th rowspan="2">Studiengang</th><th title="Semester" rowspan="2">S</th>';

				foreach ($gebiet AS $gbt)
					echo '<th colspan="2">'.$gbt->name.'</th>';

				echo '<th colspan="2">Gesamt</th>
			</tr>
			<tr>';

				foreach ($gebiet AS $gbt)
					echo "<th><small>Punkte</small></th><th><small>Prozent</small></th>";

				echo '<th><small>Punkte</small></th><th><small>Prozent</small></th>
		</tr>';

		if(isset($ergb))
		{
			foreach ($ergb AS $erg)
			{
				echo "<tr><td>$erg->prestudent_id [<a href=auswertung_detail_prestudent.php?prestudent_id=$erg->prestudent_id target='blank'>Detail</a>]</td><td>$erg->nachname</td><td>$erg->vorname</td><td>$erg->gebdatum</td><td>$erg->geschlecht</td>
						<td>".$zgv_arr[$erg->zgv]."</td><td>".$zgvma_arr[$erg->zgvma]."</td><td>$erg->registriert</td><td>$erg->stg_kurzbz</td><td>$erg->stg_bez</td><td>$erg->semester</td>";
				//<td>$erg->idnachweis</td>
				foreach ($gebiet AS $gbt)
					if (isset($erg->gebiet[$gbt->gebiet_id]))
						if ($erg->gebiet[$gbt->gebiet_id]->punkte!='' && $erg->gebiet[$gbt->gebiet_id]->punkte!='0')
							echo '<td>'.number_format($erg->gebiet[$gbt->gebiet_id]->punkte,2,',',' ').'</td><td nowrap>'.number_format($erg->gebiet[$gbt->gebiet_id]->prozent,2,',',' ').' %</td>';
						else
							echo '<td style="color:#C10000">'.number_format($erg->gebiet[$gbt->gebiet_id]->punkte,2,',',' ').'</td><td style="color:#C10000" nowrap>'.number_format($erg->gebiet[$gbt->gebiet_id]->prozent,2,',',' ').' %</td>';
					else
						echo '<td></td><td></td>';
				echo '<td>'.number_format($erg->gesamtpunkte,2,',',' ').'</td>';
				echo '<td>'.number_format($erg->gesamt,2,',',' ').'</td>';
				echo '</tr>';
			}
		}

		echo '</table>

		<h1>Persönlichkeit</h1>

		<table id="zeitsperren">
			<tr>
				<th rowspan="2">PrestudentID</th><th rowspan="2">Nachname</th><th rowspan="2">Vornamen</th>
				<th rowspan="2">GebDatum</th><th rowspan="2">G</th>
				<th rowspan="2">ZGV</th>
				<th rowspan="2">ZGV MA</th>
				<th rowspan="2">Registriert</th><th rowspan="2">STG</th><th rowspan="2">Studiengang</th><th rowspan="2">S</th>';

				foreach ($kategorie AS $gbt)
					echo '<th colspan="2">'.$gbt->name.'</th>';

			echo '</tr><tr>';

				foreach ($kategorie AS $gbt)
					echo '<th><small>Punkte</small></th><th><small>Typ</small>';

			echo '</th></tr>';

			foreach ($erg_kat AS $erg)
			{
				echo "<tr><td>$erg->prestudent_id</td><td>$erg->nachname</td><td>$erg->vorname</td><td>$erg->gebdatum</td><td>$erg->geschlecht</td>
							<td>".$zgv_arr[$erg->zgv]."</td><td>".$zgvma_arr[$erg->zgvma]."</td><td>$erg->registriert</td><td>$erg->stg_kurzbz</td><td>$erg->stg_bez</td><td>$erg->semester</td>";
				//<td>$erg->idnachweis</td>
				foreach ($kategorie AS $gbt)
					echo '<td>'.$erg->kategorie[$gbt->name]->punkte.'</td><td>'.$erg->kategorie[$gbt->name]->typ.'</td>';
				echo '</tr>';
			}

		echo '</table>';
	}
}
echo '</body></html>';
?>
