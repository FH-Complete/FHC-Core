<?php
include('../../vilesci/config.inc.php');
include('../../include/functions.inc.php');
include('../../include/lehrstunde.class.php');

$conn=pg_connect(CONN_STRING);
$conn_fas=pg_connect(CONN_STRING_FAS);
//$adress='fas_sync@technikum-wien.at';
$adress='fas_sync@technikum-wien.at';
//$adress_stpl='stpl@technikum-wien.at';
$adress_stpl='stpl@technikum-wien.at';
$adress_fas='fas_sync@technikum-wien.at';


// error log für jeden Studiengang
$error_log=array();
$missing_lehrfaecher=array();
$missing_einheit=array();
$missing_raumtyp=array();
$missing_lehrform=array();

function printLVA($row)
{
	return 'lvnr='.$row->lvnr.' '.$row->lv_bezeichnung;
}

function getSemesterWhereClause()
{
	global $conn;
	$qry="SELECT * FROM public.tbl_studiensemester WHERE ende>now()";
	$result=pg_query($conn, $qry);
	$where='';
	while ($row=pg_fetch_object($result))
	{
		$where.= ((strlen($where)>0)?' or ':'')."studiensemester_kurzbz='".$row->studiensemester_kurzbz."' ";
	}
	if (strlen($where)>0) $where=" ($where) ";
	return $where;
}

function validate($row)
{
	global $error_log,$einheit,$missing_einheit,$missing_raumtyp,$missing_lehrform,$raumtyp,$lehrform;
	$valid=true;
	if ($row->raumtyp==null)
	{
		$error_log[$row->studiengang_kz][]=printLVA($row).': Raumtyp fehlt';
		$valid=false;
	}
	if ($row->semester>8 || $row->semester<1)
	{
		$error_log[$row->studiengang_kz][]=printLVA($row).': Semester bei '.$row->semester.$row->verband.$row->gruppe.' größer als 8';
		$valid=false;
	}
	if (!($row->verband==null || $row->verband=='' || $row->verband=='A' || $row->verband=='B' || $row->verband=='C' || $row->verband=='D'))
	{
		$error_log[$row->studiengang_kz][]=printLVA($row).': Verband bei '.$row->semester.$row->verband.$row->gruppe.' außerhalb des gültigen Bereichs (A bis D)';
		//print_r($row);
		$valid=false;
	}
	if (!($row->gruppe==null || $row->gruppe=='' || $row->gruppe=='1' || $row->gruppe=='2' || $row->gruppe=='3' || $row->gruppe=='4'))
	{
		$error_log[$row->studiengang_kz][]=printLVA($row).': Gruppe bei '.$row->semester.$row->verband.$row->gruppe.' außerhalb des gültigen Bereichs (1 bis 4)';
		$valid=false;
	}
	if (!$row->stundenblockung>0) {
		$error_log[$row->studiengang_kz][]=printLVA($row).': Stundenblockung ist nicht größer 0';
		$valid=false;
	}
	if (!($row->semesterstunden>=0)) {
		$error_log[$row->studiengang_kz][]=printLVA($row).': Semesterstunden sind nicht >= 0';
		$valid=false;
	}
	if (!$row->wochenrythmus>0)
	{
		$error_log[$row->studiengang_kz][]=printLVA($row).': Wochenrythmus ist nicht größer 0';
		$valid=false;
	}
	if ($row->start_kw<=0 || $row->start_kw>53)
	{
		$error_log[$row->studiengang_kz][]=printLVA($row).': Start-KW außerhalb des gültigen Bereichs (1 bis 53)';
		$valid=false;
	}
	if (strlen($row->einheit_kurzbz)>0 && !isset($einheit[$row->einheit_kurzbz]) && !isset($missing_einheit[$row->einheit_kurzbz]))
	{
		$missing_einheit[$row->einheit_kurzbz]=1;
	}
	if (strlen($row->raumtyp)>0 && !isset($raumtyp[$row->raumtyp]) && !isset($missing_raumtyp[$row->raumtyp]))
	{
		$missing_raumtyp[$row->raumtyp]=1;
		$valid=false;
	}
	if (strlen($row->raumtypalternativ)>0 && !isset($raumtyp[$row->raumtypalternativ]) && !isset($missing_raumtyp[$row->raumtypalternativ])) {
		$missing_raumtyp[$row->raumtypalternativ]=1;
	}
	if (!ereg("^[A-Za-z]{1,5}[0-9]{0,1}$",$row->raumtyp))
	{
		$error_log[$row->studiengang_kz][]=$row->raumtyp.': Raumtyp bei LVNR:'.$row->lvnr.' ist nicht plausibel.';
		$valid=false;
	}
	if (!ereg("^[A-Za-z]{1,5}[0-9]{0,1}$",$row->raumtypalternativ))
	{
		$error_log[$row->studiengang_kz][]=$row->raumtypalternativ.': Raumtypalternative bei LVNR:'.$row->lvnr.' ist nicht plausibel.';
		$valid=false;
	}
	if (strlen($row->lehrform)>0 && !isset($lehrform[$row->lehrform]) && !isset($missing_lehrform[$row->lehrform])) {
		$missing_lehrform[$row->lehrform]=1;
	}
	if (!ereg("^[A-Z]{1,5}[0-9]{0,1}$",$row->lehrfach_kurzbz))
	{
		$error_log[$row->studiengang_kz][]=$row->lehrfach_kurzbz.'-'.$row->lehrform.'/'.$row->studiengang_kz.'-'.$row->semester.': Lehrfach-Kuerzel bei LVNR:'.$row->lvnr.' ist nicht plausibel.';
		$valid=false;
	}
	if (!ereg("^[A-Z]{1,3}$",$row->lehrform))
	{
		$error_log[$row->studiengang_kz][]=$row->lehrfach_kurzbz.'-'.$row->lehrform.'/'.$row->studiengang_kz.'-'.$row->semester.': Lehrform bei LVNR:'.$row->lvnr.' ist nicht plausibel.';
		$valid=false;
	}
	return $valid;
}

/**
 * FAS-Lehrfach auf interne Lehrfach-Nr übersetzen
 */
function getLehrfachId($kurzbz,$studiengang_kz,$semester,$lehrfach_bezeichnung, $fachbereich_id, $conn)
{
	global $lehrfach;
	global $text;

	if (isset($lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_id']))
	{
		//echo 'Nummer:'.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_nr'].'Bez: '.$lehrfach_bezeichnung.'<BR>';

		// Nebenbei die Lehrfachbezeichnung kontrollieren
		if ($lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_bezeichnung']!=$lehrfach_bezeichnung)
		{
			// Update
			$qry="UPDATE lehre.tbl_lehrfach SET bezeichnung='$lehrfach_bezeichnung' WHERE lehrfach_id=".$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_id'];
			if (!$result=pg_query($conn, $qry))
				echo $qry.' fehlgeschlagen!<BR>';
			else
			{
				echo 'Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurde von '.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_bezeichnung'].' auf '.$lehrfach_bezeichnung.' geaendert!<BR>';
				$text.='Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurde von '.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_bezeichnung'].' auf '.$lehrfach_bezeichnung." geaendert!\n";
				$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_bezeichnung']=$lehrfach_bezeichnung;
			}
		}

		// Nebenbei die FachbereichID kontrollieren
		if ($lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['fachbereich_id']!=$fachbereich_id)
		{
			// Update
			$qry="UPDATE lehre.tbl_lehrfach SET fachbereich_kurzbz=(SELECT fachbereich_kurzbz FROM public.tbl_fachbereich where ext_id=$fachbereich_id LIMIT 1) WHERE lehrfach_id=".$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_id'];
			if (!$result=@pg_query($conn, $qry))
				echo $qry.' fehlgeschlagen!<BR>';
			else
			{
				echo 'Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurde die FachbereichID von '.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['fachbereich_id'].' auf '.$fachbereich_id.' geaendert!<BR>';
				$text.='Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurde die FachbereichID von '.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['fachbereich_id'].' auf '.$fachbereich_id.' geaendert!\n';
				$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['fachbereich_id']=$fachbereich_id;
			}
		}
		return $lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_id'];
	}
	//echo 'missing getLehrfachNr: '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.'<br>';
	return -1;
}

/**
 * FAS-LV auf interne LV-id übersetzen
 */
function getLvId($kurzbz,$studiengang_kz,$semester,$lv_bezeichnung, $ects, $conn)
{
	global $lehrveranstaltung;
	global $text;

	if (isset($lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrveranstaltung_id']))
	{
		//echo 'Nummer:'.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_nr'].'Bez: '.$lehrfach_bezeichnung.'<BR>';

		// Nebenbei die LVbezeichnung kontrollieren
		if ($lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['bezeichnung']!=$lv_bezeichnung)
		{
			// Update
			$qry="UPDATE lehre.tbl_lehrveranstaltung SET bezeichnung='$lv_bezeichnung' WHERE lehrveranstaltung_id=".$lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrveranstaltung_id'];
			if (!$result=pg_query($conn, $qry))
				echo $qry.' fehlgeschlagen!<BR>';
			else
			{
				echo 'Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurde von '.$lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['bezeichnung'].' auf '.$lv_bezeichnung.' geaendert!<BR>';
				$text.='Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurde von '.$lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['bezeichnung'].' auf '.$lv_bezeichnung." geaendert!\n";
				$lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['bezeichnung']=$lv_bezeichnung;
			}
		}

		// Nebenbei die ECTS Punkte kontrollieren
		
		if ($lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['ects']!=$ects)
		{
			if ($ects!='') //ereg("[0-9]{1,4}[\.|,][0-9]{0,2}$",$ects)
			{
				// Update
				$qry="UPDATE lehre.tbl_lehrveranstaltung SET ects='$ects' WHERE lehrveranstaltung_id=".$lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrveranstaltung_id'];
				//echo $qry.'<BR>';
				if (!$result=pg_query($conn, $qry))
					echo $qry.' fehlgeschlagen!<BR>';
				else
				{
					echo ' Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurden die ECTS-Punkte von '.$lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['ects'].' auf '.$ects.' geaendert!<BR>';
					$text.='Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurden die ECTS-Punkte von '.$lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['ects'].' auf '.$ects." geaendert!\n";
					$lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['ects']=$ects;
				}
			}
			else
			{
				echo 'Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' sind die ECTS-Punkte von '.$ects.' nicht Plausibel!<BR>';
				$text.='Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' sind die ECTS-Punkte von '.$ects." nicht Plausibel!\n";
			}

		}

		return $lehrveranstaltung[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrveranstaltung_id'];
	}
	//echo 'missing getLehrfachNr: '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.'<br>';
	return -1;
}

/*************************
 * FAS-Synchronisation
 */

// E-Mails der Studiengänge
$stg_mail=array();
$qry="select studiengang_kz,email,upper(typ::varchar(1) || kurzbz) as kurzbz FROM public.tbl_studiengang";
$result=pg_query($conn, $qry);
while ($row=pg_fetch_object($result))
{
	$stg_mail[$row->studiengang_kz] = $row->email;//'oesi@technikum-wien.at';
	$stg_kurzbz[$row->studiengang_kz]=$row->kurzbz;
}

// Anzahl der LVA in VileSci
$sql_query="SELECT count(*) AS anz FROM lehre.tbl_lehrveranstaltung";
//echo $sql_query."<br>";
$result=pg_query($conn, $sql_query);
$row=pg_fetch_object($result);
$vil_anz_lva = $row->anz;

// Lehrfächer holen und in Array speichern (Key ist kurzbz + '/' + lehform_kurzbz)
$sql_query="SELECT lehrfach_id, tbl_lehrfach.kurzbz, tbl_lehrfach.studiengang_kz, tbl_lehrfach.semester, tbl_lehrfach.bezeichnung, tbl_fachbereich.ext_id as fachbereich_id, tbl_fachbereich.fachbereich_kurzbz FROM lehre.tbl_lehrfach JOIN public.tbl_fachbereich using(fachbereich_kurzbz)";
$result=pg_query($conn, $sql_query);
while ($row=pg_fetch_object($result))
{
	$lehrfach[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['lehrfach_id'] = $row->lehrfach_id;
	$lehrfach[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['fachbereich_id'] = $row->fachbereich_id;
	$lehrfach[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['lehrfach_bezeichnung'] = $row->bezeichnung;
	//$lehrfach[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['ects'] = $row->ects;
}
//LVA holen und in Array speichern (Key ist kurzbz + '/' + lehform_kurzbz)
$sql_query="SELECT lehrveranstaltung_id,kurzbz,studiengang_kz,semester, bezeichnung, ects FROM lehre.tbl_lehrveranstaltung";
$result=pg_query($conn, $sql_query);
while ($row=pg_fetch_object($result))
{
	$lehrveranstaltung[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['lehrveranstaltung_id'] = $row->lehrveranstaltung_id;
	//$lehrveranstaltung[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['lehrfach_id'] = $row->lehrfach_id;
	$lehrveranstaltung[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['bezeichnung'] = $row->bezeichnung;
	$lehrveranstaltung[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['ects'] = $row->ects;
}

//print_r($lehrfach);
// Gruppen holen
$sql_query="SELECT gruppe_kurzbz,bezeichnung FROM public.tbl_gruppe";
$result=pg_query($conn, $sql_query);
while ($row=pg_fetch_object($result))
	$einheit[$row->gruppe_kurzbz] = $row->bezeichnung;
// Raumtypen holen
$sql_query="SELECT raumtyp_kurzbz,beschreibung FROM public.tbl_raumtyp";
$result=pg_query($conn, $sql_query);
while ($row=pg_fetch_object($result))
	$raumtyp[$row->raumtyp_kurzbz] = $row->beschreibung;
// Lehformen holen
$sql_query="SELECT lehrform_kurzbz,bezeichnung FROM lehre.tbl_lehrform";
$result=pg_query($conn, $sql_query);
while ($row=pg_fetch_object($result))
	$lehrform[$row->lehrform_kurzbz] = $row->bezeichnung;
//print_r($lehrfach);
echo 'FAS-Datenbank wird abgefragt!<BR><i>';
flush();

// Start Lehrveranstaltungen Synchro
$sql_query="SELECT DISTINCT fas_id,trim(lvnr) AS lvnr,trim(unr)::int8 AS unr,einheit_kurzbz,lektor,trim(upper(lehrfach_kurzbz)) AS lehrfach_kurzbz,
			trim(upper(lehrform)) AS lehrform, lehrfach_bezeichnung, trim(upper(lv_kurzbz)) AS lv_kurzbz, lv_bezeichnung, 
			studiengang_kz,fachbereich_id,semester,verband,gruppe,raumtyp,raumtypalternativ,
			round(semesterstunden) AS semesterstunden,stundenblockung,wochenrythmus,start_kw,anmerkung,studiensemester_kurzbz, ects
			FROM fas_view_alle_lehreinheiten_vilesci ".
		   "where ".getSemesterWhereClause();
//echo $sql_query."</i><br>";
$result=pg_query($conn_fas, $sql_query);
$num_rows=pg_num_rows($result);
$text="Dies ist eine automatische eMail!\r\r";
$text.="Es wurde eine Synchronisation mit FAS durchgeführt.\r";
$text.="Anzahl der LVA vom FAS-Import: $num_rows \r";
$text.="Anzahl der LVA in der VileSci: $vil_anz_lva \r\r";
$plausi_error=0;
$update_error=0;
$insert_error=0;
$double_error=0;
$anz_update=0;
$anz_insert=0;
echo $num_rows.' Datensaetze<BR>';
for ($i=0;$i<$num_rows;$i++)
{
	
	//if ($i%100==0)
	//{
	//	echo '-';
	//	flush();
	//}
	$row=pg_fetch_object($result,$i);
	// Kennzahl der Studiengangs bei ehemaligen bTec auf TW aendern.
	if ($row->studiengang_kz==203)
		$row->studiengang_kz=0;
	// Lehrfach-Nr übersetzen (-1 wenn nicht vorhanden)
	$row->lehrfach_id=getLehrfachid($row->lehrfach_kurzbz,$row->studiengang_kz,$row->semester, $row->lehrfach_bezeichnung, $row->fachbereich_id, $conn);
	$row->lehrveranstaltung_id=getLvId($row->lv_kurzbz,$row->studiengang_kz,$row->semester, $row->lv_bezeichnung, $row->ects, $conn);
	// Einheit vollstaendiger Name
	if (count($row->einheit_kurzbz)>0)
		$row->einheit_kurzbz=$stg_kurzbz[$row->studiengang_kz].'-'.$row->einheit_kurzbz;

	// Plausibilitaetscheck
	//if ($row->gruppe==NULL)
	//	$row->gruppe='1';

	//
	if (!$row->stundenblockung>0)
		$row->stundenblockung=1;
	if (!$row->start_kw>0)
		$row->start_kw=1;
	if (!$row->wochenrythmus>0)
		$row->wochenrythmus=1;

	if ($row->lehrfach_id==-1)
	{
		//$error_log[$row->studiengang_kz][]=printLVA($row).': Lehrfach (Kurzbz='".$row->lehrfach_kurzbz."',Lehrform".$row->lehrform) existiert noch nicht. Stundenplanabteilung wurde benachrichtigt.';
		if (!isset($missing_lehrfaecher[$row->lehrfach_kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester])) $missing_lehrfaecher[$row->lehrfach_kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]=1;
			$valid=false;
	}
	if ($row->lehrveranstaltung_id==-1)
	{
		if (!isset($missing_lehrveranstaltungen[$row->lv_kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester])) $missing_lehrveranstaltungen[$row->lv_kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]=1;
			$valid=false;
	}

	if (validate($row) && $row->lehrfach_id>-1 && $row->lehrveranstaltung_id>-1)
	{
		// SQL vorbereiten (jede LVA vom FAS im VileSci suchen)
		$sql_query="SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE tbl_lehreinheitmitarbeiter.ext_id=".$row->fas_id;
		//echo $sql_query;
		$res_lva=pg_query($conn, $sql_query);
		$num_rows_lva=pg_num_rows($res_lva);

		// neue Lehreinheit
		if ($num_rows_lva==0)
		{
			$text.="Die Lehreinheit fas-id=$row->fas_id lvnr=$row->lvnr unr=$row->unr wird neu angelegt.\r";
			pg_query($conn, 'BEGIN');
			//Neue Lehreinheit anlegen
			$sql_query='INSERT INTO lehre.tbl_lehreinheit (lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id,'.
						 'lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache,'.
						 'lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon) '.
                        "VALUES('$row->lehrveranstaltung_id','$row->studiensemester_kurzbz','$row->lehrfach_id',".
                        "'$row->lehrform',".
                        "'$row->stundenblockung',".
                        "'$row->wochenrythmus',".
                        "'$row->start_kw',".
                        "'$row->raumtyp',".
                        "'$row->raumtypalternativ',".
                        "'German',".
                        "true,".
                        "'$row->anmerkung',".
                        "'$row->unr',".
                        "'$row->lvnr',".
                        "now(),'auto', now(),'auto');";
			echo $sql_query.'<BR>';
			if(!$res_insert=@pg_query($conn, $sql_query))
			{
				$text.=$sql_query;
				$text.="\nFehler: ".pg_errormessage($conn)."\n";
				$insert_error++;
				pg_query($conn, 'ROLLBACK');
			}
			else
			{
				//Lehreinheit_id auslesen
				$sql_query = "SELECT currval('lehre.tbl_lehreinheit_lehreinheit_id_seq') as id";
				echo $sql_query.'<br>';
				if(!$row_seq = pg_fetch_object(pg_query($conn, $sql_query)))
				{
					$text.=$sql_query;
					$text.="\nFehler: Sequence konnte nicht ausgelesen werden\n";
					$insert_error++;
					pg_query($conn,'ROLLBACK');
				}
				else 
				{
					//Gruppe zuteilen
					$sql_query = 'INSERT INTO lehre.tbl_lehreinheitgruppe(lehreinheit_id, studiengang_kz,'.
					             ' semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon ,insertamum, insertvon)'.
					             " VALUES('$row_seq->id','$row->studiengang_kz', '$row->semester',";
					if ($row->verband==null)
						$sql_query.='NULL,';
					else
						$sql_query.="'$row->verband',";
					if ($row->gruppe==null)
						$sql_query.='NULL,';
					else
						$sql_query.="'$row->gruppe',";
					$sql_query.=(strlen($row->einheit_kurzbz)>0?"'".$row->einheit_kurzbz."'":'NULL').",now(),'auto',now(),'auto');";
					echo $sql_query.'<br>';
					if(!pg_query($conn, $sql_query))
					{
						$text.=$sql_query;
						$text.="\nFehler: ".pg_errormessage($conn)."\n";
						$insert_error++;
						pg_query($conn, 'ROLLBACK');
					}
					else 
					{
						//Lektor zuteilen
						$sql_query = 'INSERT INTO lehre.tbl_lehreinheitmitarbeiter(lehreinheit_id, mitarbeiter_uid,'.
									 'lehrfunktion_kurzbz, semesterstunden, planstunden, stundensatz, faktor, anmerkung,'.
									 'bismelden, updateamum, updatevon, insertamum, insertvon, ext_id)'.
									 " VALUES('$row_seq->id','$row->lektor','lektor','$row->semesterstunden',".
									 " NULL, NULL, 1, NULL, true, now(), 'auto',now(),'auto', '$row->fas_id');";
						echo $sql_query.'<br>';
						if(!pg_query($conn, $sql_query))
						{
							$text.=$sql_query;
							$text.="\nFehler: ".pg_errormessage($conn)."\n";
							$insert_error++;
							pg_query($conn, 'ROLLBACK');
						}
						else 
						{
							pg_query($conn, 'COMMIT');
							$anz_insert++;
						}
					}
				}
			}
			
		}		
		elseif ($num_rows_lva>0)// bestehende Lehreinheit
		{	
			$update_sql='';
			$row_lva=pg_fetch_object($res_lva);
			
			$sql_query = "SELECT * FROM lehre.tbl_lehreinheit WHERE lehreinheit_id='$row_lva->lehreinheit_id'";
			if(!$result_le = pg_query($conn, $sql_query))
			{
				$update_error++;
				$text.="\nLehreinheit konnte nicht geladen werden: $sql_query\n";
			}
			else 
			{
				$row_le = pg_fetch_object($result_le);
				$update=false;
				$update_sql='';
				if ($row->lvnr!=$row_le->lvnr)
					$update_sql.=(strlen($update_sql)>0?',':'')."lvnr='".$row->lvnr."'";
				if ($row->unr!=$row_le->unr)
					$update_sql.=(strlen($update_sql)>0?',':'')."unr=".$row->unr;
				//if ($row->lehrfach_nr!=$row_le->lehrfach_id)
				//	$update_sql.=(strlen($update_sql)>0?',':'')."lehrfach_nr=".$row->lehrfach_nr;
				if ($row->lehrform!=$row_le->lehrform_kurzbz)
					$update_sql.=(strlen($update_sql)>0?',':'')."lehrform_kurzbz='".$row->lehrform."'";	
				//if ($row->studiengang_kz!=$row_le->studiengang_kz)
				//	$update_sql.=(strlen($update_sql)>0?',':'')."studiengang_kz=".$row->studiengang_kz;
				//if ($row->semester!=$row_le->semester)
				//	$update_sql.=(strlen($update_sql)>0?',':'')."semester=".$row->semester;
				if ($row->raumtyp!=$row_le->raumtyp)
					$update_sql.=(strlen($update_sql)>0?',':'')."raumtyp='".$row->raumtyp."'";
				if ($row->raumtypalternativ!=$row_le->raumtypalternativ)
					$update_sql.=(strlen($update_sql)>0?',':'')."raumtypalternativ='".$row->raumtypalternativ."'";	
				if ($row->stundenblockung!=$row_le->stundenblockung)
					$update_sql.=(strlen($update_sql)>0?',':'')."stundenblockung=".$row->stundenblockung;
				if ($row->wochenrythmus!=$row_le->wochenrythmus)
					$update_sql.=(strlen($update_sql)>0?',':'')."wochenrythmus=".$row->wochenrythmus;
				if ($row->start_kw!=$row_le->start_kw)
					$update_sql.=(strlen($update_sql)>0?',':'')."start_kw=".(strlen($row->start_kw)>0?$row->start_kw:'NULL');
				if ($row->studiensemester_kurzbz!=$row_le->studiensemester_kurzbz)
					$update_sql.=(strlen($update_sql)>0?',':'')."studiensemester_kurzbz='".$row->studiensemester_kurzbz."'";
				if ($row->anmerkung!=$row_le->anmerkung)
					$update_sql.=(strlen($update_sql)>0?',':'')."anmerkung='".$row->anmerkung."'";	
				
				if (strlen($update_sql)>0)
				{				
					$sql_query="UPDATE lehre.tbl_lehreinheit SET ".
							$update_sql.
							" where lehreinheit_id=".$row_le->lehreinheit_id;
					if(!$res_update=@pg_query($conn, $sql_query))
					{
						$text.=$sql_query;
	                    $text.="\rFehler: ".pg_errormessage($conn)."\r";
						$update_error++;
					}
					else
						$update=true;
				}
				
				$sql_query = 'SELECT * FROM lehre.tbl_lehreinheitmitarbeiter'.
				             " WHERE lehreinheit_id='$row_lva->lehreinheit_id' AND mitarbeiter_uid='$row->lektor'";
				//echo $sql_query.'<br>';
				if($result_lektor = pg_query($conn, $sql_query))
				{
					if(pg_num_rows($result_lektor)>0)
					{
						//Update Lehreinheitmitarbeiter
						$row_lektor = pg_fetch_object($result_lektor);
						$update_sql='';
						if ($row->semesterstunden!=$row_lektor->semesterstunden)
							$update_sql.=(strlen($update_sql)>0?',':'')."semesterstunden=".$row->semesterstunden;
						
						if($update_sql!='')
						{
							$sql_query = "UPDATE lehre.tbl_lehreinheitmitarbeiter SET $update_sql".
										" WHERE lehreinheit_id='$row_lva->lehreinheit_id' AND mitarbeiter_uid='$row->lektor'";
							echo $sql_query.'<br>';
							if(!pg_query($conn, $sql_query))
							{
								$update_error++;
								$text .=$sql_query;
								$text.="\nFehler:".pg_errormessage($conn);
							}
							else 
								$update=true;
						}
					}
					else 
					{
						//Lehreinheitmitarbeiter Eintrag hinzufuegen
						$sql_query = 'INSERT INTO lehre.tbl_lehreinheitmitarbeiter(lehreinheit_id, mitarbeiter_uid,'.
						              'lehrfunktion_kurzbz, semesterstunden, planstunden,'.
						              'bismelden, updateamum, updatevon, insertamum, insertvon, ext_id)'.
						              " VALUES('$row_lva->lehreinheit_id', '$row->lektor', 'lektor', '$row->semesterstunden','".
						              " $row->semesterstunden',true,now(),'auto',now(),'auto','$row->fas_id');";
						echo $sql_query.'<br>';
						if(!pg_query($conn, $sql_query))
						{
							$text.=$sql_query;
							$text.="\nFehler:".pg_errormessage($conn);
							$update_error++;
						}
						else 
							$update=true;
					}
				}
				
				$sql_query = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$row_lva->lehreinheit_id'";
				if($row->einheit_kurzbz!='')
				{
					$sql_query.=" AND gruppe_kurzbz='$row->einheit_kurzbz'";
				}
				else 
				{
					$sql_query.=" AND studiengang_kz='$row->studiengang_kz' AND semester='$row->semester'";
					if($row->verband!='')
						$sql_query.=" AND verband='$row->verband'";
					if($row->gruppe!='')
						$sql_query.=" AND gruppe='$row->gruppe'";
				}

				if($result_gruppen = pg_query($conn, $sql_query))
				{
					if(pg_num_rows($result_gruppen)==0)
					{
						if($row->einheit_kurzbz!='' && isset($einheit[$row->einheit_kurzbz]))
						{
							//Gruppeneintrag hinzuguegen
							$sql_query = 'INSERT INTO lehre.tbl_lehreinheitgruppe(lehreinheit_id, studiengang_kz, semester,'.
							             ' verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon)'.
							             " VALUES ('$row_lva->lehreinheit_id','$row->studiengang_kz', '$row->semester',".
							             (strlen($row->verband)>0?"'$row->verband'":'NULL').','.
							             (strlen($row->gruppe)>0?"'$row->gruppe'":'NULL').','.
							             (strlen($row->einheit_kurzbz)>0?"'$row->einheit_kurzbz'":'NULL').','.
							             "now(),'auto', now(),'auto');";
							echo $sql_query.'<br>';
							if(pg_query($conn, $sql_query))
							{
								$update=true;
							}
							else 
							{
								$text.=$sql_query;
								$text.="\nFehler:".pg_errormessage($conn);
								$update_error++;
							}							
						}
						else 
						{
							if(!isset($missing_einheit[$row->einheit_kurzbz]))
								$missing_einheit[$row->einheit_kurzbz]=1;
						}
					}
				}
				else 
				{	
					$text.=$sql_query;
					$text.="\nFehler:".pg_errormessage($conn);
					$update_error++;
				}
				
			
				if($update)
				{
					$text.="Die Lehreinheit fas-id=$row->fas_id lvnr=$row->lvnr unr=$row->unr wurde upgedatet.\r";
					$anz_update++;
				}
				
					/*
					// ****************
					// Auch in tbl_stundenplandev updaten
					$sql_query="SELECT * FROM tbl_stundenplandev WHERE
						lehrveranstaltung_id=$row_lva->lehrveranstaltung_id AND datum>=now()";
					//echo $sql_query.'<BR>';
					if(!$res_upd_stpl=@pg_query($conn, $sql_query))
					{
						$text.=$sql_query;
	                    $text.="\rFehler: ".pg_errormessage($conn)."\r";
					}
					else
					{
						if (!pg_query($conn,"BEGIN;"))
							$text.="\rFehler: ".pg_errormessage($conn)."\r";
						$kollision=false;
						while ($row_upd_stpl=pg_fetch_object($res_upd_stpl))
						{
							// Lehrstunde auf Kollisionen checken
							$lehrstunde=new lehrstunde($conn);
							//echo '<BR>STPL-ID:'.$row_upd_stpl->stundenplandev_id.'<BR>';
							if (!$lehrstunde->load($row_upd_stpl->stundenplandev_id))
								echo $lehrstunde->errormsg;
							$lehrstunde->lektor_uid=$row->lektor;
							if (!$lehrstunde->kollision())
							{
								if (!$lehrstunde->save('sync_fas_lva'))
									echo $lehrstunde->errormsg;
							}
							else
							{
								$error_log[$row->studiengang_kz][]=$lehrstunde->errormsg;
								$text.="\rKollision: ".$lehrstunde->errormsg."\r";
								$kollision=true;
								echo "Kollision: ".$lehrstunde->errormsg."<BR>";
							}
						}
						if ($kollision)
						{
							if (!pg_query($conn,"ROLLBACK;"))
								$text.="\rFehler: ".pg_errormessage($conn)."\r";
						}
						else
							if (!pg_query($conn,"COMMIT;"))
								$text.="\rFehler: ".pg_errormessage($conn)."\r";
					}*/
				}			
			}
		// LVA kommt mehrmals vor ->Warnung
		//elseif ($num_rows_lva>1)
		//{
		//	$text.="\r!!! Die LVA fas_id=$row->fas_id kommt mehrfach vor!\r";
		//	$double_error++;
		//}
	}
	else
		$plausi_error++;
}



// ****************
// Ueberfluessige Datensaetze loeschen
$whereClause=getSemesterWhereClause();
$sql_query="SELECT tbl_lehreinheitmitarbeiter.ext_id FROM lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehreinheit WHERE tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND $whereClause AND tbl_lehreinheitmitarbeiter.ext_id NOT IN (SELECT fas_id FROM lehre.vw_fas_lehrveranstaltung WHERE ($whereClause) AND (fas_id!=0 OR fas_id IS NOT NULL) AND ($whereClause))";
//echo $sql_query.'<BR>';
$anz_delete=0;
if($res_delete=pg_query($conn, $sql_query))
{
	while($row = pg_fetch_object($res_delete))
	{		
		$qry = "DELETE FROM lehre.tbl_lehreinheitgruppe WHERE ext_id='$row->ext_id';
				DELETE FROM lehre.tbl_lehreinheitmitarbeiter WHERE ext_id='$row->ext_id';";
		$text.="Lehreinheitengruppe und Lehreinheitmitarbeiter mit FAS_ID '$row->ext_id' wird geloescht\n";
				
		if(pg_query($conn, $qry))
		{
			$anz_delete++;
			$text.="Lehreinheitengruppe und Lehreinheitmitarbeiter mit FAS_ID '$row->ext_id' wurde geloescht\n";
		}
		else 
		{
			$text.=$qry;
			$text.="\nFehler beim loeschen:".pg_errormessage($conn);"\n";
		}
	}
}
else
{	
	$text.="\n".$sql_query;
    $text.="\nFehler: ".pg_errormessage($conn)."\n";
}



//Ausgabe Zusammenfassung
$text.="\n$anz_delete Lehrveranstaltungen wurden geloescht!\n";
$text.="$plausi_error Fehler beim Plausibilitaetscheck!\n";
$text.="$update_error Fehler bei LVA-Update!\n";
$text.="$insert_error Fehler bei LVA-Insert!\n";
$text.="$double_error LVA kommen in VileSci doppelt vor!\n\n";
$text.="$anz_update LE wurden upgedatet.\n";
$text.="$anz_insert LE wurden neu angelegt.\n\n";
$text.="\nEND OF SYNCHRONISATION\n";

// Validation error hinzufügen
while(list($k,$v)=each($error_log))
{
	$text.="\n\nStudiengang $k:\n";
	foreach($v as $txt)
		$text.="  $txt\n";
}
// fehlende lehrfächer
$text.="\n\nFehlende Lehrfächer: \n";
while(list($k,$v)=each($missing_lehrfaecher))
{
	$text.="  $k\n";
}
$text.="\n\nFehlende Lehrveranstaltungen: \n";
while(list($k,$v)=each($missing_lehrveranstaltungen))
{
	$text.="  $k\n";
}
// fehlende einheiten
$text.="\n\nFehlende Einheiten: \n";
while(list($k,$v)=each($missing_einheit))
{
	$text.="  $k\n";
}
// fehlende raumtypen
$text.="\n\nFehlende Raumtypen: \n";
while(list($k,$v)=each($missing_raumtyp))
{
	$text.="  $k\n";
}
// fehlende lehrformen
$text.="\n\nFehlende Lehrformen: \n";
while(list($k,$v)=each($missing_lehrform))
{
	$text.="  $k\n";
}

if (mail($adress,"FAS Synchro mit VileSci (Lehrveranstaltungen)",$text,"From: vilesci@technikum-wien.at"))
	$sendmail=true;
else
	$sendmail=false;

//print "debug: ";print_r($stg_mail);

// Einzelnen Mails an Studiengänge verschicken
reset($error_log);
while(list($k,$v)=each($error_log))
{
	echo "<br>Mail an Studiengang $k ".$stg_mail[$k].":<br>";
	$stg_text="Dies ist eine automatische Mail!\nFolgende Fehler sind bei der Synchronisation der Lehrveranstaltungen aufgetreten:\n\n";
	foreach($v as $txt)
		$stg_text.="$txt\n";
	echo $stg_text.'<br>';
	// Studiengang
	if (!mail($stg_mail[$k],"FAS Synchro mit Portal (Lehrveranstaltungen) $k",$stg_text,"From: vilesci@technikum-wien.at"))
		echo "Mail an '".$stg_mail[$k]."' konnte nicht verschickt werden!<br>";
	// Stundenplanstelle
	echo "<br>Mail an Studiengang $k ($adress_stpl)<br>";
	if (!mail($adress_stpl,"FAS Synchro mit Portal (Lehrveranstaltungen) $k",$stg_text,"From: vilesci@technikum-wien.at"))
		echo 'Mail an "'.$adress_stpl.'" konnte nicht verschickt werden!<br>';

}

// Doppelte IDs im FAS prüfen
$sql_query="SELECT count(*) AS anzahl, fas_id FROM fas_view_alle_lehreinheiten_vilesci
			GROUP BY fas_id HAVING count(*)>1";
//echo $sql_query."</i><br>";
$result=pg_query($conn_fas, $sql_query);
$num_rows=pg_numrows($result);
$mail_text="Folgende $num_rows IDs kommen in der View fas_view_alle_lehreinheiten_vilesci (fas_id) mehrfach vor:\n\n";
$mail_text_false='';
if ($num_rows>0)
	while ($row=pg_fetch_object($result))
		$mail_text_false.=$row->fas_id.'->'.$row->anzahl."x\n";
$mail_text.=$mail_text_false."\n\nBitte überprüfen die Daten im FAS!!!";
if ($mail_text_false!='')
	if (!mail($adress_fas,"FAS Synchro mit Portal (Lehrveranstaltungen)",$mail_text,"From: vilesci@technikum-wien.at"))
		echo "Mail an '".$adress_fas."' konnte nicht verschickt werden!<br>";
	else
		echo 'Mail wurde verschickt an '.$adress_fas.'!<br>';
		
?>

<html>
<head>
<title>FAS-Synchro mit PORTAL</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
if ($sendmail)
	echo 'Mail wurde verschickt an '.$adress.'!<br>';
else
	echo "Mail konnte nicht verschickt werden!<br>";
echo nl2br($text);

?>
</body>
</html>
