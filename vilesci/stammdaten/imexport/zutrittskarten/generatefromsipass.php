<?php
require('../../../config.inc.php');
//include('../../../include/functions.inc.php');
/*require_once('../../../../include/Excel/PEAR.php');
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
require_once('../../../../include/Excel/Validator.php');*/

$sipass=array(array());
$i=0;
$key_nummer=0;
$update=false;

if (!$conn=pg_pconnect(CONN_STRING))
	die(pg_last_error($conn));
	

define("DB_SERVER","192.168.101.230:1433");
define("DB_USER","sa");
define("DB_PASSWD","P1ss0ff");
define("DB_DB","asco4");
		
// zugriff auf mssql-datenbank 
if (!$conn_ext=mssql_connect (DB_SERVER, DB_USER, DB_PASSWD))
	die('Fehler beim Verbindungsaufbau!');
mssql_select_db(DB_DB, $conn_ext);


//letzte Nummer
$sql_query="SELECT max(asco.cardholder.cardholder_id) AS last_keynr FROM asco.cardholder;";
//echo $sql_query;
if(!$result=mssql_query($sql_query,$conn_ext))
	die(mssql_get_last_message().'<BR>'.$sql_query);
if ($row=mssql_fetch_object($result))
	$key_nummer=$row->last_keynr+1;
else
	die('Letzte Nummer konnte nicht eruiert werden!');
	
//einlesen der daten von sipass

$qry="SELECT * FROM asco.cardholder 
JOIN asco.card_physical ON(asco.card_physical.cardholder_id=asco.cardholder.cardholder_id) 
JOIN asco.card_logical ON(asco.card_physical.card_physical_id=asco.card_logical.card_physical_id);";
if($result_ext = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result_ext))
	{
		$sipass[$i][0]='';
		$sipass[$i][1]=$row->cardholder_id;
		$sipass[$i][2]=$row->last_name;
		$sipass[$i][3]=$row->first_name;
		$sipass[$i][4]=$row->number;
		$sipass[$i][5]=date('d.m.Y',strtotime($row->start_date));
		$sipass[$i][6]=date('d.m.Y',strtotime($row->end_date));
		$i++;
	}
}

$qry="SELECT DISTINCT nachname as LastName, vorname as FirstName,nummer as CardNumber, 
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
				$sipass[$j][2]=trim($row->lastname);
				$sipass[$j][3]=trim($row->firstname);
				$sipass[$j][5]=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.$row->jahr));
				$sipass[$j][6]=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.($row->jahr+5)));
				$update=true;
				break;
			}
		}
		if(!$update)
		{
			//wenn nicht gefunden, dann append
			if($row->lastname!='' && $row->firstname!='' && $row->cardnumber!='' &&$row->tag!='' && $row->monat!='' && $row->jahr!='')
			{
				$sipass[$i][0]="A";
				$sipass[$i][1]=$key_nummer;
				$sipass[$i][2]=trim($row->lastname);
				$sipass[$i][3]=trim($row->firstname);
				$sipass[$i][4]=str_replace(" ","",$row->cardnumber);
				$sipass[$i][5]=$row->tag.'.'.$row->monat.'.'.$row->jahr;
				$sipass[$i][6]=$row->tag.'.'.$row->monat.'.'.($row->jahr+5);
				$i++;
				$key_nummer++;
			}
		}
	}
}
$ausdruck='';
for($j=0;$j<$i;$j++)
{
	if(trim($sipass[$j][0]==''))
	{
		$sipass[$j][0]='D';
	}
	$ausdruck.=$sipass[$j][0]."\t";
	$ausdruck.=$sipass[$j][1]."\t";
	$ausdruck.=$sipass[$j][2]."\t";
	$ausdruck.=$sipass[$j][3]."\t";
	$ausdruck.=$sipass[$j][4]."\t";
	$ausdruck.=$sipass[$j][5]."\t";
	$ausdruck.=$sipass[$j][6]."\t";
	$ausdruck.="<Keine>\n";
}
header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=\"SiPassZutrittskartenUpdate". "_" . date("d_m_Y") . ".txt\"");
echo $ausdruck;


/*
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
	if(trim($sipass[$j][0]!=''))
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
}

$workbook->close();
*/
?>