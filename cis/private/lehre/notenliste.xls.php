<?php
/*
 * Aufruf:
 * notenliste.xls.php?stg=222&lfvt=1234 //alle Studenten vom Studiengang 222 Lehrfach 1234
 * notenliste.xls.php?stg=222&sem=1&lfvt=1234 //alle Studenten vom Studiengang 222 und Semester 1 Lehrfach 1234
 * notenliste.xls.php?stg=222&sem=1&verband=A&lfvt=1234 //alle Studenten vom Studiengang 222, Semester 1, Verband A Lehrfach 1234
 * notenliste.xls.php?stg=222&sem=1&verband=A&gruppe=1&lfvt=1234 //alle Studenten vom Studiengang 222, Semester 1, Verband A, Gruppe 1 Lehrfach 1234
 * notenliste.xls.php?stg=222&sem=1&einheit=DVT-1xyz1&lfvt=1234 //alle Studenten vom Studiengang 222, Semester 1,  Einheit DVT-1xyz1 Lehrfach 1234
*/

require_once('../../config.inc.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/Excel/PEAR.php');
require_once('../../../include/Excel/BIFFwriter.php');
require_once('../../../include/Excel/Workbook.php');
require_once('../../../include/Excel/Format.php');
require_once('../../../include/Excel/Worksheet.php');
require_once('../../../include/Excel/Parser.php');
require_once('../../../include/Excel/OLE.php');
require_once('../../../include/Excel/PPS.php');
require_once('../../../include/Excel/Root.php');
require_once('../../../include/Excel/File.php');
require_once('../../../include/Excel/Writer.php');


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
	
	if($gruppe_kurzbz!='')
		$grpname = "Gruppe: $gruppe_kurzbz";
	else 
		if($sem!='')
			$grpname = "Gruppe: $sem$verband$gruppe";
		else 
			$grpname = '';
		
	$worksheet->write(1,0,"Studiengang: $stg_obj->bezeichnung $grpname");
	$lines=2;
	//Lektoren ermitteln
	$stsem_obj = new studiensemester($conn);
	$stsem = $stsem_obj->getaktorNext();

	$qry = "SELECT distinct vorname, nachname FROM campus.vw_benutzer, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter WHERE uid=mitarbeiter_uid AND tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND lehrveranstaltung_id='".addslashes($lvid)."' AND studiensemester_kurzbz='$stsem' ORDER BY nachname, vorname;";

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
	
	
	$qry = 'SELECT distinct vorname, nachname, uid, matrikelnr, verband, gruppe, semester FROM ';
	if($gruppe_kurzbz!='')
		$qry .= "campus.vw_student JOIN public.tbl_benutzergruppe USING(uid) WHERE gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
	else 
	{
		$qry .= "campus.vw_student WHERE studiengang_kz='$stg' AND semester='$sem'";
		if($verband!='')
			$qry.=" AND verband='$verband'";
		if($gruppe!='')
			$qry.=" AND gruppe='$gruppe'";
	}	
	$qry.= " ORDER BY nachname, vorname";
	//echo $qry;
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
	
	$worksheet->setColumn(0, 0, 5);
	$worksheet->setColumn(1, 1, 25);
	$worksheet->setColumn(2, 2, 25);
	$worksheet->setColumn(3, 3, 7);
	$worksheet->setColumn(4, 4, 13);
	$workbook->close();

?>
