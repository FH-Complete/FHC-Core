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

$sipass=array(array());
$i=0;
$update=false;

if (!$conn=pg_pconnect(CONN_STRING))
	die(pg_last_error($conn));
/*	
// zugriff auf mssql-datenbank ----------------------- DB-Zugriff ändern !!!!!!
if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
	die('Fehler beim Verbindungsaufbau!');
mssql_select_db(STPDB_DB, $conn_ext);

/*
//letzte Nummer
$sql_query="SELECT max(key) AS last_keynr FROM ***************;";
//echo $sql_query;
if(!$result=mssql_query($qry,$conn_ext))
	die(mssql_get_last_message().'<BR>'.$sql_query);
if ($row=pg_fetch_object($result))
	$key_nummer=$row->last_keynr+1;
else
	die('Letzte Nummer konnte nicht eruiert werden!');*/
	
//einlesen der daten von sipass
/*
$qry="SELECT ID, LastName, FirstName, CardNumber, StartDate, EndDate  FROM **************;";
if($result_ext = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result_ext))
	{
		$sipass[$i][1]=$result_ext->ID;
		$sipass[$i][2]=$result_ext->LastName;
		$sipass[$i][3]=$result_ext->FirstName;
		$sipass[$i][4]=$result_ext->CardNumber;
		$sipass[$i][5]=$result_ext->StartDate;
		$sipass[$i][6]=$result_ext->EndDate;
		$i++;
	}
}*/

$qry="SELECT DISTINCT vorname as FirstName,nachname as LastName, nummer as CardNumber, 
				EXTRACT(DAY FROM vw_betriebsmittelperson.insertamum) AS tag,
				EXTRACT(MONTH FROM vw_betriebsmittelperson.insertamum) AS monat,
				EXTRACT(YEAR FROM vw_betriebsmittelperson.insertamum) AS jahr
			FROM public.vw_betriebsmittelperson
				 LEFT OUTER JOIN (public.tbl_student JOIN public.tbl_studiengang USING (studiengang_kz)) ON (uid=student_uid)
			WHERE betriebsmitteltyp='Zutrittskarte' AND benutzer_aktiv AND retouram IS NULL;";
//abhanden gekommene karten???

if($result = pg_query($conn, $qry))
{
	while($row=pg_fetch_object($result))
	{
		$update=false;
		for($j=0;$j<$i;$j++)
		{
			//überprüfen, ob bereits vorhanden
			if($sipass[$j][4]==$row->cardnumber)
			{
				$sipass[$j][0]="U";
				$sipass[$j][2]=$row->lastname;
				$sipass[$j][3]=$row->firstname;
				$sipass[$j][5]=$row->tag.'.'.$row->monat.'.'.$row->jahr;
				$sipass[$j][6]=$row->tag.'.'.$row->monat.'.'.($row->jahr+5);
				$update=true;
				break;
			}
		}
		if(!$update)
		{
			//wenn nicht gefunden, dann append
			$sipass[$i][0]="A";
			$sipass[$i][1]='';
			$sipass[$i][2]=$row->lastname;
			$sipass[$i][3]=$row->firstname;
			$sipass[$i][4]=$row->cardnumber;
			$sipass[$i][5]=$row->tag.'.'.$row->monat.'.'.$row->jahr;
			$sipass[$i][6]=$row->tag.'.'.$row->monat.'.'.($row->jahr+5);
			$i++;
		}
	}
}


//------------ Excel init --------------------------

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();
// sending HTTP headers
$workbook->send("SiPassZutrittskartenUpdate". "_" . date("d_m_Y") . ".xls");
// Creating a worksheet
$worksheet =& $workbook->addWorksheet("SiPassZutrittskartenUpdate");

// set width of columns
$worksheet->setColumn(0,0,2); 			// erste Spalte auf width=2
$worksheet->setColumn(0,1,8); 			// zweite Spalte auf width=8
$worksheet->setColumn(0,2,20);
$worksheet->setColumn(0,3,20);
$worksheet->setColumn(0,4,12);
$worksheet->setColumn(0,5,10);
$worksheet->setColumn(0,6,10);


$z=0; 							// Start bei Zeile 0

for($j=0;$j<$i;$j++)
{
	$worksheet->write($z,0, $sipass[$j][0]);
	$worksheet->write($z,1, $sipass[$j][1]);					
	$worksheet->write($z,2, $sipass[$j][2]);
	$worksheet->write($z,3, $sipass[$j][3]);
	$worksheet->write($z,4, $sipass[$j][4]);
	$worksheet->write($z,5, $sipass[$j][5]);				
	$worksheet->write($z,6, $sipass[$j][6]);
	$z++;
}

$workbook->close();
?>