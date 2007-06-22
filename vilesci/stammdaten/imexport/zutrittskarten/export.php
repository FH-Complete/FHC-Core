<?php
require('../../../config.inc.php');
//include('../../../include/functions.inc.php');
require_once('../../../../include/Excel/PEAR.php');
require_once('../../../../include/Excel/BIFFwriter.php');
require_once('../../../../include/Excel/Workbook.php');
require_once('../../../../include/Excel/Format.php');
require_once('../../../../include/Excel/Worksheet.php');
require_once('../../../../include/Excel/Parser.php');
require_once('../../../../include/Excel/OLE.php');
require_once('../../../../include/Excel/PPS.php');
require_once('../../../../include/Excel/Root.php');
require_once('../../../../include/Excel/File.php');
require_once('../../../../include/Excel/Writer.php');
require_once('../../../../include/Excel/Validator.php');

if (!$conn=pg_pconnect(CONN_STRING))
	die(pg_last_error($conn));

// Neue Zutrittskarten
$sql_query="SELECT *, EXTRACT(DAY FROM insertamum) AS tag, " .
					"EXTRACT(MONTH FROM insertamum) AS monat, " .
					"EXTRACT(YEAR FROM insertamum) AS jahr " .
			"FROM public.vw_betriebsmittelperson " .
			"WHERE betriebsmitteltyp='Zutrittskarte' AND nummer NOT IN " .
				"( SELECT physaswnumber FROM sync.tbl_zutrittskarte);";
//echo $sql_query;
if(!$result_neu=pg_exec($conn, $sql_query))
	die(pg_errormessage().'<BR>'.$sql_query);

//------------ Excel init --------------------------

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();
// sending HTTP headers
$workbook->send("CerpassZutrittskartenUpdate". "_" . date("d_m_Y") . ".xls");
// Creating a worksheet
$worksheet =& $workbook->addWorksheet("CerpassZutrittskartenUpdate");

//$format_bold =& $workbook->addFormat();
//$format_bold->setBold();
//$format_title =& $workbook->addFormat();
//$format_title->setBold();
//	$format_title->setColor('yellow');
//	$format_title->setPattern(1);
//	$format_title->setFgColor('blue');
// let's merge
//$format_title->setAlign('merge');

$worksheet->write(0,0,"(Command)"); //, $format_bold
$worksheet->write(0,1,"(Key)");
$worksheet->write(0,2,"(Name)");
$worksheet->write(0,3,"(FirstName)");
$worksheet->write(0,4,"(Group)");
$worksheet->write(0,5,"(LogAswNumber)");
$worksheet->write(0,6,"(PhysAswNumber)");
$worksheet->write(0,7,"(ValidStart)");
$worksheet->write(0,8,"(ValidEnd)");
$worksheet->write(0,9,"(UID)");
$worksheet->write(0,10,"(Matrikelnummer)");
$worksheet->write(0,11,"(Kurzbezeichnung)");
$worksheet->write(0,12,"(Semester)");
$worksheet->write(0,13,"(Text5)");
$worksheet->write(0,14,"(Titel)");
$worksheet->write(0,15,"(PIN)");
$worksheet->write(0,16,"(CardState)");

// set width of columns
$worksheet->setColumn(0,0,2); // erste Spalte auf width=2
$worksheet->setColumn(1,1,5); // zweite Spalten auf width=5
//$worksheet->setColumn(0,0,22);

$z=1; // Start bei Zeile 1

// Neue Zutrittskarten
while ($row=pg_fetch_object($result_neu))
{
	$command='a';
	$worksheet->write($z,0, utf8_decode($command));
	$worksheet->write($z,1, utf8_decode($row->person_id));
	$worksheet->write($z,2, utf8_decode($row->nachname));
	$worksheet->write($z,3, utf8_decode($row->vorname));
	$worksheet->write($z,4, utf8_decode(substr($row->uid,0,5)));
	$worksheet->write($z,5, utf8_decode($row->person_id));
	$worksheet->write($z,6, utf8_decode($row->nummer));
	$worksheet->write($z,7, utf8_decode($row->tag.'.'.$row->monat.'.'.$row->jahr));
	$worksheet->write($z,8, utf8_decode($row->tag.'.'.$row->monat.'.'.($row->jahr+4)));
	$worksheet->write($z,9, utf8_decode($row->uid));
	$worksheet->write($z,10, utf8_decode($row->svnr));
	$worksheet->write($z,11, utf8_decode('Kurzbz'));
	$worksheet->write($z,12, utf8_decode('Semester'));
	$worksheet->write($z,13, utf8_decode('Text5'));
	$worksheet->write($z,14, utf8_decode($row->titelpre));
	$worksheet->write($z,15, utf8_decode('PIN'));
	$worksheet->write($z,16, utf8_decode('CardState'));
	$z++;		
}

$workbook->close();
?>