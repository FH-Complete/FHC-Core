<?php
require('../../../config.inc.php');

// Mail Headers festlegen
$headers= "MIME-Version: 1.0\r\n";
$headers.="Content-Type: text/html; charset=iso-8859-1\r\n";


$sipass=array();
$i=0;
$k=0;
$key_nummer=0;
$update=false;
$custom=array(array());
$doppelte=array();
$error=false;
$fausgabe='<table>';

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
$sql_query="SELECT max(asco.employee.reference) AS last_keynr FROM asco.employee;";
//echo $sql_query;
if(!$result=mssql_query($sql_query,$conn_ext))
	die(mssql_get_last_message().'<BR>'.$sql_query);
if ($row=mssql_fetch_object($result))
	$key_nummer=$row->last_keynr+1;
else
	die('Letzte Nummer konnte nicht eruiert werden!');

//einlesen der custom. daten von sipass
$qry="SELECT * FROM asco.employee_custom_data";
if($result = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result))
	{
		$custom[$row->emp_id][$row->field_id]=$row->char_value;
	}
}

$qry="SELECT * FROM asco.employee LEFT OUTER JOIN asco.access_groups ON (asco.employee.acc_grp_id=asco.access_groups.acc_grp_id)";
if($result_ext = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result_ext))
	{
		$sipass[$i]->command='';
		$sipass[$i]->reference=$row->reference;
		$sipass[$i]->last_name=$row->last_name;
		$sipass[$i]->first_name=$row->first_name;
		$sipass[$i]->card_no=(int)$row->card_no;
		$sipass[$i]->start_date=date('d.m.Y',strtotime($row->start_date));
		$sipass[$i]->end_date=date('d.m.Y',strtotime($row->end_date));
		$sipass[$i]->acc_grp_name=$row->acc_grp_name;
		if(isset($custom[$row->emp_id][7]))
		{
			$sipass[$i]->uid=$custom[$row->emp_id][7];   //UID
		}
		else
		{
			$sipass[$i]->uid="";
		}
		if(isset($custom[$row->emp_id][8]))
		{
			$sipass[$i]->matrikelnr=$custom[$row->emp_id][8];   //Matrikelnr.
		}
		else
		{
			$sipass[$i]->matrikelnr="";
		}
		/*if(isset($custom[$row->emp_id][9]))
		{
			$sipass[$i][9]=$custom[$row->emp_id][9];   //Stg./Verwaltung
		}
		else
		{
			$sipass[$i][9]="";
		}*/
		$i++;
	}
}

//mehrfach vergebene karten
$qry="SELECT bmp.person_id as person2, bmp.nachname as nachname2,bmp.nummer as nummer2, bmp.vorname as vorname2, bmp.ausgegebenam as ausgegebenam2, bmp.insertamum AS insertamum2,
		public.vw_betriebsmittelperson.person_id AS person1, public.vw_betriebsmittelperson.nachname as nachname1, public.vw_betriebsmittelperson.nummer as nummer1,
		public.vw_betriebsmittelperson.vorname as vorname1, public.vw_betriebsmittelperson.ausgegebenam as ausgegebenam1, public.vw_betriebsmittelperson.insertamum AS insertamum1
	 FROM public.vw_betriebsmittelperson bmp
	 JOIN public.vw_betriebsmittelperson ON (bmp.nummer=public.vw_betriebsmittelperson.nummer)
	 WHERE (trim(bmp.nachname)!=trim(public.vw_betriebsmittelperson.nachname) OR (trim(bmp.vorname)!=trim(public.vw_betriebsmittelperson.vorname)))
	 AND public.vw_betriebsmittelperson.betriebsmitteltyp='Zutrittskarte' AND bmp.betriebsmitteltyp='Zutrittskarte'
	 AND public.vw_betriebsmittelperson.benutzer_aktiv AND public.vw_betriebsmittelperson.retouram IS NULL AND bmp.benutzer_aktiv AND bmp.retouram IS NULL
	 AND bmp.person_id<public.vw_betriebsmittelperson.person_id
	 ORDER BY bmp.nachname;";
if($result = pg_query($conn, $qry))
{
	$fausgabe.='<tr><th>PersonID</th><th>Nachname</th><th>vorname</th><th>BetriebsmittelNr</th><th>AusgabeAm</th><th>InsertAmUm</th></tr>';
	while($row=pg_fetch_object($result))
	{
		//echo "<br>".$row->person2.", ".$row->nachname2.", ".$row->vorname2.", ".$row->nummer2.", ".$row->person1.", ".$row->nachname1.", ".$row->vorname1.", ".$row->nummer1;
		//$error=true;
		if(!in_array($row->nummer1,$doppelte))
		{
			$doppelte[$k]=$row->nummer1;
			$fausgabe.='<tr><td>'.$row->person1.'</td><td>'.$row->nachname1.'</td><td>'.$row->vorname1.'</td><td>'.$row->nummer1.'</td><td>'.$row->ausgegebenam1.'</td><td>'.$row->insertamum1.'</td></tr>';
			$fausgabe.='<tr><td>'.$row->person2.'</td><td>'.$row->nachname2.'</td><td>'.$row->vorname2.'</td><td>'.$row->nummer2.'</td><td>'.$row->ausgegebenam2.'</td><td>'.$row->insertamum2.'</td></tr><tr></tr>';
			$k++;
		}
	}
	$fausgabe.='</table>';
}

//Osobsky Michael studiert BEE und MIE - wird daher nicht synchronisiert
$qry="SELECT DISTINCT ON (vw_betriebsmittelperson.person_id, nummer) nachname as LastName, vorname as FirstName,nummer as CardNumber, matrikelnr, uid, kurzbzlang,tbl_studiengang.kurzbz,typ, personalnummer, lektor,
		EXTRACT(DAY FROM vw_betriebsmittelperson.insertamum) AS tag,
		EXTRACT(MONTH FROM vw_betriebsmittelperson.insertamum) AS monat,
		EXTRACT(YEAR FROM vw_betriebsmittelperson.insertamum) AS jahr
	FROM public.vw_betriebsmittelperson
		 LEFT OUTER JOIN (public.tbl_student JOIN public.tbl_studiengang USING (studiengang_kz)) ON (uid=student_uid)
		 LEFT OUTER JOIN public.tbl_mitarbeiter ON (uid=mitarbeiter_uid)
	WHERE betriebsmitteltyp='Zutrittskarte' AND benutzer_aktiv AND retouram IS NULL 
	AND NOT(trim(upper(nachname))='OSOBSKY' AND trim(upper(vorname))='MICHAEL')
	ORDER  BY vw_betriebsmittelperson.person_id,nummer,personalnummer";
//abhanden gekommene karten???

if($result = pg_query($conn, $qry))
{
	while($row=pg_fetch_object($result))
	{
		$update=false;
		$stg_kurzbz=strtoupper(trim($row->typ).trim($row->kurzbz));
		$row->cardnumber=(int)$row->cardnumber;
		//doppelte ueberspringen
		if(in_array($row->cardnumber,$doppelte))
		{
			continue;
		}
		//überprüfen, ob bereits vorhanden
		for($j=0;$j<$i;$j++)
		{
			if($sipass[$j]->card_no==$row->cardnumber)
			{
				$upd=FALSE;
				if($sipass[$j]->last_name!=trim($row->lastname) && !strchr($row->lastname,'ß'))
				{
					$sipass[$j]->last_name_old=$sipass[$j]->last_name;
					$sipass[$j]->last_name=trim($row->lastname);
					$sipass[$j]->update.=' last_name';
					$upd=TRUE;
				}
				if($sipass[$j]->first_name!=trim($row->firstname))
				{
					$sipass[$j]->first_name_old=$sipass[$j]->first_name;
					$sipass[$j]->first_name=trim($row->firstname);
					$sipass[$j]->update.=' first_name';
					$upd=TRUE;
				}
				if($sipass[$j]->start_date!=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.$row->jahr)))
				{
					$sipass[$j]->start_date=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.$row->jahr));
					$sipass[$j]->update.=' start_date';
					$upd=TRUE;
				}
				if($sipass[$j]->end_date!=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.($row->jahr+5))))
				{
					$sipass[$j]->end_date=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.($row->jahr+5)));
					$sipass[$j]->update.=' end_date';
					$upd=TRUE;
				}
				if($sipass[$j]->uid!=trim($row->uid))
				{
					$sipass[$j]->uid=trim($row->uid);
					$sipass[$j]->update.=' uid';
					$upd=TRUE;
				}
				if(trim($row->matrikelnr)!='' && $sipass[$j]->matrikelnr!=trim($row->matrikelnr))
				{
					$sipass[$j]->matrikelnr=trim($row->matrikelnr);
					$sipass[$j]->update=' matrikelnr';
					$upd=TRUE;
				}
				if($row->personalnummer!='' && $row->personalnummer!= NULL)
				{
					if($sipass[$j]->acc_grp_name!="Verwaltung")
					{
						$sipass[$j]->acc_grp_name="Verwaltung";
						$sipass[$j]->update=' acc_grp_name';
					$upd=TRUE;
					}
				}
				else
				{
					if($sipass[$j]->acc_grp_name!=trim($stg_kurzbz))
					{
						$sipass[$j]->acc_grp_name_old=$sipass[$j]->acc_grp_name;
						$sipass[$j]->acc_grp_name=trim($stg_kurzbz);
						$sipass[$j]->update=' acc_grp_name';
						$upd=TRUE;
					}
				}
				// Update nur wenn Gruppe nicht mit # beginnt
				if($upd && substr($sipass[$j]->acc_grp_name,0,1)!='#')
				{
					$sipass[$j]->command="U";
				}
				else
				{
					$sipass[$j]->command="V"; //kein update, wird auch nicht gelöscht
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
				$sipass[$i]->command="A";
				$sipass[$i]->reference=$key_nummer;
				$sipass[$i]->last_name=trim($row->lastname);
				$sipass[$i]->first_name=trim($row->firstname);
				$sipass[$i]->card_no=str_replace(" ","",$row->cardnumber);
				$sipass[$i]->start_date=$row->tag.'.'.$row->monat.'.'.$row->jahr;
				$sipass[$i]->end_date=$row->tag.'.'.$row->monat.'.'.($row->jahr+5);
				$sipass[$j]->uid=trim($row->uid);
				$sipass[$j]->matrikelnr=trim($row->matrikelnr);
				if($row->personalnummer!='' && $row->personalnummer!= NULL)
				{
					$sipass[$j]->acc_grp_name="Verwaltung";

				}
				else
				{
					$sipass[$j]->acc_grp_name=$stg_kurzbz;
				}
				$key_nummer++;
				$i++;
			}
		}
	}
}
$ausdruck='';
for($j=0;$j<$i;$j++)
{
	if(trim($sipass[$j]->command==''))
	{
		$sipass[$j]->command='D';
		if (substr($sipass[$j]->acc_grp_name,0,1)!='#')
		{
			$ausdruck.=$sipass[$j]->command."\t"; 		// Command
			$ausdruck.=$sipass[$j]->reference."\t";		// ID
			$ausdruck.=$sipass[$j]->last_name."\t";		// Lastname
			$ausdruck.=$sipass[$j]->first_name."\t";		// Firstname
			$ausdruck.=$sipass[$j]->acc_grp_name."\t";	// Access Group
			$ausdruck.=$sipass[$j]->card_no."\n";		// Cardnumber
		}
	}
	else
	{
		if(trim($sipass[$j]->command!='V'))
		{
			$ausdruck.=$sipass[$j]->command."\t"; 			// Command
			$ausdruck.=$sipass[$j]->reference."\t";			// ID
			$ausdruck.=$sipass[$j]->last_name."\t";			// Lastname
			$ausdruck.=$sipass[$j]->first_name."\t";			// Firstname
			$ausdruck.=$sipass[$j]->acc_grp_name."\t";		// Access Group
			$ausdruck.=$sipass[$j]->card_no."\t";			// Cardnumber
			$ausdruck.=$sipass[$j]->start_date."\t";			// Valid From
			$ausdruck.=$sipass[$j]->end_date."\t";			// Valid till
			$ausdruck.="0\t";						// CardState
			$ausdruck.=$sipass[$j]->uid."\t";				// Text1 UID
			$ausdruck.=$sipass[$j]->matrikelnr."\t";			// Text2 Matrikelnummer
			if (isset($sipass[$j]->last_name_old))
				$ausdruck.=$sipass[$j]->last_name_old;		// Text3 // alter Vorname
			$ausdruck.="\t";
			if (isset($sipass[$j]->fist_name_old))
				$ausdruck.=$sipass[$j]->fist_name_old;		// Text4 // alter Nachname
			$ausdruck.="\t";
			if (isset($sipass[$j]->acc_grp_name_old))
				$ausdruck.=$sipass[$j]->acc_grp_name_old;	// Text5 // alte Accessgroup
			$ausdruck.="\t";
			if (isset($sipass[$j]->update))
				$ausdruck.=$sipass[$j]->update;			// Text6 // alte Accessgroup
			$ausdruck.="\n";
		}
	}
}
header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=\"SiPassZutrittskartenUpdate". "_" . date("d_m_Y") . ".txt\"");
echo $ausdruck;

mail(MAIL_SUPPORT, 'Mehrfach eingetragenen Zutrittskarten', "<html><body>".$fausgabe.$ausdruck."</body></html>",
	"From: vilesci@technikum-wien.at\nX-Mailer: PHP 5.x\nContent-type: text/html; charset=utf-8");

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