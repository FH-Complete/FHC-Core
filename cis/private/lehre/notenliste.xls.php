<?php
/*
 * Erstellt Notenliste im Excel Format
 */

require_once('../../config.inc.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/Excel/excel.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

   	if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
   		$lvid=$_GET['lvid'];
   	else
   		die("Fehlerhafte Parameteruebergabe");
   	
   	if(isset($_GET['stg']) && is_numeric($_GET['stg']))
   		$stg=$_GET['stg'];
   	else 
   		die("Fehlerhafte Parameteruebergabe");
   		
   	if(isset($_GET['gruppe_kurzbz']))
   		$gruppe_kurzbz = $_GET['gruppe_kurzbz'];
   	else 
   		$gruppe_kurzbz = '';
   		
   	if(isset($_GET['sem']) && is_numeric($_GET['sem']))
   		$sem = $_GET['sem'];
   	else 	
   		$sem = '';
   	
   	if(isset($_GET['verband']))
   		$verband = $_GET['verband'];
   	else 
   		$verband = '';
   		
   	if(isset($_GET['gruppe']) && is_numeric($_GET['gruppe']))
   		$gruppe = $_GET['gruppe'];
   	else
   		$gruppe = '';
   		
   	if(isset($_GET['stsem']))
   		$stsem = $_GET['stsem'];
   	else
   		die('Studiensemester muss uebergeben werden');
   		
   	if(isset($_GET['lehreinheit_id']))
   		$lehreinheit_id = $_GET['lehreinheit_id'];
   	else 
   		$lehreinheit_id = '';
   	
   	/*
	 * Create Excel File
	 */

	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Notenliste". "_" . date("d_m_Y") . ".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Notenliste");

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();

	$format_title =& $workbook->addFormat();
	$format_title->setBold();
//	$format_title->setColor('yellow');
//	$format_title->setPattern(1);
//	$format_title->setFgColor('blue');
	// let's merge
	$format_title->setAlign('merge');

	$lvobj = new lehrveranstaltung($conn, $lvid);
		
	$worksheet->write(0,0,"Notenliste ".$lvobj->bezeichnung);
	
	$stg_obj = new studiengang($conn, $stg);
	
	$qry = "SELECT distinct on(kuerzel, semester, verband, gruppe, gruppe_kurzbz) UPPER(stg_typ::varchar(1) || stg_kurzbz) as kuerzel, semester, verband, gruppe, gruppe_kurzbz from campus.vw_lehreinheit WHERE lehrveranstaltung_id='".addslashes($lvid)."' AND studiensemester_kurzbz='".addslashes($stsem)."'";
	if($lehreinheit_id!='')
		$qry.=" AND lehreinheit_id='".addslashes($lehreinheit_id)."'";
		
	$gruppen='';
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			if($gruppen!='')
				$gruppen.=', ';
			if($row->gruppe_kurzbz=='')
				$gruppen.=trim($row->kuerzel.'-'.$row->semester.$row->verband.$row->gruppe);
			else
				$gruppen=$row->gruppe_kurzbz;
		}
	}
			
	$worksheet->write(1,0,"Studiengang: $stg_obj->bezeichnung $gruppen");
	$lines=2;
	//Lektoren ermitteln
	
	$qry = "SELECT 
				distinct vorname, nachname 
			FROM 
				campus.vw_benutzer, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter 
			WHERE 
				uid=mitarbeiter_uid AND 
				tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND 
				lehrveranstaltung_id='".addslashes($lvid)."' AND 
				studiensemester_kurzbz='".addslashes($stsem)."'";
	
	if($lehreinheit_id!='')
		$qry.=" AND tbl_lehreinheit.lehreinheit_id='".addslashes($lehreinheit_id)."'";
	
	$qry.=' ORDER BY nachname, vorname';
	
	if($result = pg_query($conn,$qry))
	{
		while($row=pg_fetch_object($result))
		{
			$worksheet->write($lines,0,"$row->vorname $row->nachname");
			$lines++;
		}
	}

	//Studenten holen
	$lines++;
	$worksheet->write($lines,1,"Familiennname");
	$worksheet->write($lines,2,"Vorname");
	$worksheet->write($lines,3,"Gruppe");
	$worksheet->write($lines,4,"Kennzeichen");
	$worksheet->write($lines,5,"Note");
	
	
	$qry = "SELECT 
				distinct vorname, nachname, matrikelnr, student_uid as uid, 
				tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe 
			FROM 
				campus.vw_student_lehrveranstaltung JOIN public.tbl_benutzer USING(uid) 
				JOIN public.tbl_person USING(person_id) JOIN public.tbl_student ON(uid=student_uid) 
				LEFT JOIN public.tbl_studentlehrverband USING(student_uid)
			WHERE 
				lehrveranstaltung_id='".addslashes($lvid)."' AND 
				vw_student_lehrveranstaltung.studiensemester_kurzbz='".addslashes($stsem)."' AND
				tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."'";

	if($lehreinheit_id!='')
		$qry.=" AND lehreinheit_id='".addslashes($lehreinheit_id)."'";
	
	$qry.=' ORDER BY nachname, vorname';
	
	if($result = pg_query($conn, $qry))
	{
		$i=1;
		$lines++;
		while($elem = pg_fetch_object($result))
		{
			if(!preg_match('*dummy*',$elem->uid) && $elem->semester!=10)
	   		{   	
				$worksheet->write($lines,0,$i);
				$worksheet->write($lines,1,$elem->nachname);
				$worksheet->write($lines,2,$elem->vorname);
				$worksheet->write($lines,3,$elem->semester.$elem->verband.$elem->gruppe);
				$worksheet->write($lines,4,'="'.trim($elem->matrikelnr).'"');
				$worksheet->write($lines,5,'');
				$i++;
				$lines++;
	   		}
		}
	}
	
	//Notenschluessel
	$worksheet->write(++$lines,0,'Notenschlüssel: 1-Sehr Gut, 2-Gut, 3-Befriedigend, 4-Genügend,');
	$worksheet->write(++$lines,0,'5-Nicht Genügend, 6-Angerechnet, 7-nicht beurteilt,');
	$worksheet->write(++$lines,0,'8-teilgenommen, 9-noch nicht eingetragen, 10-bestanden,');
	$worksheet->write(++$lines,0,'11-approbiert, 12-erfolgreich absolviert, 13-nicht erfolgreich absolviert');	
	
	$worksheet->setColumn(0, 0, 5);
	$worksheet->setColumn(1, 1, 25);
	$worksheet->setColumn(2, 2, 25);
	$worksheet->setColumn(3, 3, 7);
	$worksheet->setColumn(4, 4, 13);
	$workbook->close();

?>
