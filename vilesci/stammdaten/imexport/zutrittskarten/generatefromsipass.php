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
$custom=array(array());
$doppelte=array();
$error=false;

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
$sql_query="SELECT max(asco.employee.emp_no) AS last_keynr FROM asco.employee;";
//echo $sql_query;
if(!$result=mssql_query($sql_query,$conn_ext))
	die(mssql_get_last_message().'<BR>'.$sql_query);
if ($row=mssql_fetch_object($result))
	$key_nummer=$row->last_keynr+1;
else
	die('Letzte Nummer konnte nicht eruiert werden!');
	
//einlesen der daten von sipass
$qry="SELECT * FROM asco.employee_custom_data";
if($result = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result))
	{
		$custom[$row->emp_id][$row->field_id]=$row->char_value;
	}
}
$qry="SELECT * FROM asco.employee";
if($result_ext = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result_ext))
	{
		$sipass[$i][0]='';
		$sipass[$i][1]=$row->emp_no;
		$sipass[$i][2]=$row->last_name;
		$sipass[$i][3]=$row->first_name;
		$sipass[$i][4]=$row->card_no;
		$sipass[$i][5]=date('d.m.Y',strtotime($row->start_date));
		$sipass[$i][6]=date('d.m.Y',strtotime($row->end_date));
		if(isset($custom[$row->emp_id][7]))
		{
			$sipass[$i][7]=$custom[$row->emp_id][7];   //UID
		}
		else 
		{
			$sipass[$i][7]="";
		}
		if(isset($custom[$row->emp_id][8]))
		{
			$sipass[$i][8]=$custom[$row->emp_id][8];   //Matrikelnr.
		}
		else 
		{
			$sipass[$i][8]="";
		}
		if(isset($custom[$row->emp_id][9]))
		{
			$sipass[$i][9]=$custom[$row->emp_id][9];   //Stg./Verwaltung
		}
		else 
		{
			$sipass[$i][9]="";
		}
		$i++;
	}
}

//
$qry="SELECT bmp.person_id as person2, bmp.nachname as nachname2,bmp.nummer as nummer2, bmp.vorname as vorname2,
		public.vw_betriebsmittelperson.person_id AS person1, public.vw_betriebsmittelperson.nachname as nachname1, public.vw_betriebsmittelperson.nummer as nummer1, 
		public.vw_betriebsmittelperson.vorname as vorname1, public.vw_betriebsmittelperson.gebdatum as gebdatum1, public.vw_betriebsmittelperson.* FROM public.vw_betriebsmittelperson bmp 
	 JOIN public.vw_betriebsmittelperson ON (bmp.nummer=public.vw_betriebsmittelperson.nummer) 
	 WHERE (trim(bmp.nachname)!=trim(public.vw_betriebsmittelperson.nachname) OR (trim(bmp.vorname)!=trim(public.vw_betriebsmittelperson.vorname))) 
	 AND public.vw_betriebsmittelperson.betriebsmitteltyp='Zutrittskarte' AND bmp.betriebsmitteltyp='Zutrittskarte'
	 AND public.vw_betriebsmittelperson.benutzer_aktiv AND public.vw_betriebsmittelperson.retouram IS NULL AND bmp.benutzer_aktiv AND bmp.retouram IS NULL 
	 AND bmp.person_id<public.vw_betriebsmittelperson.person_id 
	 ORDER BY bmp.nachname;";
if($result = pg_query($conn, $qry))
{
	while($row=pg_fetch_object($result))
	{
		echo "<br>".$row->person2.", ".$row->nachname2.", ".$row->vorname2.", ".$row->nummer2.", ".$row->person1.", ".$row->nachname1.", ".$row->vorname1.", ".$row->nummer1;
		$error=true;
	}
}
//if($error) die("");

$qry="SELECT DISTINCT ON (person_id, nummer) nachname as LastName, vorname as FirstName,nummer as CardNumber, matrikelnr, uid, kurzbzlang, personalnummer, lektor, 
		EXTRACT(DAY FROM vw_betriebsmittelperson.insertamum) AS tag,
		EXTRACT(MONTH FROM vw_betriebsmittelperson.insertamum) AS monat,
		EXTRACT(YEAR FROM vw_betriebsmittelperson.insertamum) AS jahr
	FROM public.vw_betriebsmittelperson
		 LEFT OUTER JOIN (public.tbl_student JOIN public.tbl_studiengang USING (studiengang_kz)) ON (uid=student_uid)
		 LEFT OUTER JOIN public.tbl_mitarbeiter ON (uid=mitarbeiter_uid)
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
				$upd=FALSE;
				if($sipass[$j][2]!=trim($row->lastname))
				{
					$sipass[$j][2]=trim($row->lastname);
					$upd=TRUE;
				}
				if($sipass[$j][3]!=trim($row->firstname))
				{
					$sipass[$j][3]=trim($row->firstname);
					$upd=TRUE;
				}
				if($sipass[$j][5]!=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.$row->jahr)))
				{
					$sipass[$j][5]=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.$row->jahr));
					$upd=TRUE;
				}
				if($sipass[$j][6]!=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.($row->jahr+5))))
				{
					$sipass[$j][6]=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.($row->jahr+5)));
					$upd=TRUE;
				}
				if($sipass[$j][7]!=trim($row->uid))
				{
					$sipass[$j][7]=trim($row->uid);
					$upd=TRUE;
				}
				if($sipass[$j][8]!=trim($row->matrikelnr))
				{
					$sipass[$j][8]=trim($row->matrikelnr);
					$upd=TRUE;
				}
				if($row->personalnummer!='' && $row->personalnummer!= NULL)
				{
					if($sipass[$j][9]!="Verwaltung")
					{
						$sipass[$j][9]="Verwaltung";
						$upd=TRUE;
					}
				}
				else 
				{
					if($sipass[$j][9]!=trim($row->kurzbzlang))
					{
						$sipass[$j][9]=trim($row->kurzbzlang);
						$upd=TRUE;
					}
				}
				if($upd)
				{
					$sipass[$j][0]="U";
				}
				else 
				{
					$sipass[$j][0]="V"; //kein update, wird auch nicht gelöscht
				}
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
				$sipass[$j][7]=trim($row->uid);
				$sipass[$j][8]=trim($row->matrikelnr);
				if($row->personalnummer!='' && $row->personalnummer!= NULL)
				{
					$sipass[$j][9]="Verwaltung";

				}
				else 
				{
					$sipass[$j][9]=trim($row->kurzbzlang);
				}
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
		$ausdruck.=$sipass[$j][0]."\t";
		$ausdruck.=$sipass[$j][1]."\n";
	}
	else 
	{
		if(trim($sipass[$j][0]!='V'))
		{
			$ausdruck.=$sipass[$j][0]."\t";
			$ausdruck.=$sipass[$j][1]."\t";
			$ausdruck.=$sipass[$j][2]."\t";
			$ausdruck.=$sipass[$j][3]."\t";
			$ausdruck.=$sipass[$j][4]."\t";
			$ausdruck.=$sipass[$j][5]."\t";
			$ausdruck.=$sipass[$j][6]."\t";
			$ausdruck.=$sipass[$j][7]."\t";
			$ausdruck.=$sipass[$j][8]."\t";
			$ausdruck.=$sipass[$j][9]."\n";		
		}
	}
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