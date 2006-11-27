<?php
include('../../vilesci/config.inc.php');
include('../../include/functions.inc.php');
include('../../include/lehrstunde.class.php');

$conn=pg_connect(CONN_STRING);
$conn_fas=pg_connect(CONN_STRING_FAS);
$adress='fas_sync@technikum-wien.at';
//$adress='pam@technikum-wien.at';
$adress_stpl='stpl@technikum-wien.at';
$adress_fas='pam@technikum-wien.at';


// error log für jeden Studiengang
$error_log=array();
$missing_lehrfaecher=array();
$missing_einheit=array();
$missing_raumtyp=array();
$missing_lehrform=array();

function printLVA($row)
{
	return 'lvnr='.$row->lvnr.' '.$row->bezeichnung;
}

function getSemesterWhereClause()
{
	global $conn;
	$qry="select * from tbl_studiensemester where ende>now()";
	$result=pg_exec($conn, $qry);
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
	if (!$row->semesterstunden>0) {
		$error_log[$row->studiengang_kz][]=printLVA($row).': Semesterstunden sind nicht größer 0';
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
function getLehrfachNr($kurzbz,$studiengang_kz,$semester,$lehrfach_bezeichnung, $fachbereich_id, $ects, $conn)
{
	global $lehrfach;
	global $text;

	if (isset($lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_nr']))
	{
		//echo 'Nummer:'.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_nr'].'Bez: '.$lehrfach_bezeichnung.'<BR>';

		// Nebenbei die Lehrfachbezeichnung kontrollieren
		if ($lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_bezeichnung']!=$lehrfach_bezeichnung)
		{
			// Update
			$qry="UPDATE tbl_lehrfach SET bezeichnung='$lehrfach_bezeichnung' WHERE lehrfach_nr=".$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_nr'];
			if (!$result=pg_query($conn, $qry))
				echo $qry.' fehlgeschlagen!<BR>';
			else
			{
				echo 'Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurde von '.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_bezeichnung'].' auf '.$lehrfach_bezeichnung.' geaendert!<BR>';
				$text.='Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurde von '.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_bezeichnung'].' auf '.$lehrfach_bezeichnung.' geaendert!\n';
				$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_bezeichnung']=$lehrfach_bezeichnung;
			}
		}

		// Nebenbei die ECTS Punkte kontrollieren
		if ($lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['ects']!=$ects)
		{
			if ($ects!='') //ereg("[0-9]{1,4}[\.|,][0-9]{0,2}$",$ects)
			{
				// Update
				$qry="UPDATE tbl_lehrfach SET ects='$ects' WHERE lehrfach_nr=".$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_nr'];
				//echo $qry.'<BR>';
				if (!$result=pg_query($conn, $qry))
					echo $qry.' fehlgeschlagen!<BR>';
				else
				{
					echo ' Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurden die ECTS-Punkte von '.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['ects'].' auf '.$ects.' geaendert!<BR>';
					$text.='Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurden die ECTS-Punkte von '.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['ects'].' auf '.$ects.' geaendert!\n';
					$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['ects']=$ects;
				}
			}
			else
			{
				echo 'Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' sind die ECTS-Punkte von '.$ects.' nicht Plausibel!<BR>';
				$text.='Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' sind die ECTS-Punkte von '.$ects.' nicht Plausibel!\n';
			}

		}

		// Nebenbei die FachbereichID kontrollieren
		if ($lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['fachbereich_id']!=$fachbereich_id)
		{
			// Update
			$qry="UPDATE tbl_lehrfach SET fachbereich_id=$fachbereich_id WHERE lehrfach_nr=".$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_nr'];
			if (!$result=@pg_query($conn, $qry))
				echo $qry.' fehlgeschlagen!<BR>';
			else
			{
				echo 'Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurde die FachbereichID von '.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['fachbereich_id'].' auf '.$fachbereich_id.' geaendert!<BR>';
				$text.='Bei Lehrfach '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.' wurde die FachbereichID von '.$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['fachbereich_id'].' auf '.$fachbereich_id.' geaendert!\n';
				$lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['fachbereich_id']=$fachbereich_id;
			}
		}
		return $lehrfach[$kurzbz.'/'.$studiengang_kz.'/'.$semester]['lehrfach_nr'];
	}
	//echo 'missing getLehrfachNr: '.$kurzbz.'/'.$studiengang_kz.'/'.$semester.'<br>';
	return -1;
}


/*************************
 * FAS-Synchronisation
 */

// E-Mails der Studiengänge
$stg_mail=array();
$qry="select studiengang_kz,email,kurzbz from tbl_studiengang";
$result=pg_exec($conn, $qry);
while ($row=pg_fetch_object($result))
{
	$stg_mail[$row->studiengang_kz] = $row->email;
	$stg_kurzbz[$row->studiengang_kz]=$row->kurzbz;
}

// Anzahl der LVA in VileSci
$sql_query="SELECT count(*) AS anz FROM tbl_lehrveranstaltung";
//echo $sql_query."<br>";
$result=pg_exec($conn, $sql_query);
$vil_anz_lva=pg_fetch_result($result,0,'anz');

// Lehrfächer holen und in Array speichern (Key ist kurzbz + '/' + lehform_kurzbz)
$sql_query="SELECT lehrfach_nr,kurzbz,studiengang_kz,semester, bezeichnung, fachbereich_id, ects FROM tbl_lehrfach";
$result=pg_exec($conn, $sql_query);
while ($row=pg_fetch_object($result))
{
	$lehrfach[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['lehrfach_nr'] = $row->lehrfach_nr;
	$lehrfach[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['fachbereich_id'] = $row->fachbereich_id;
	$lehrfach[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['lehrfach_bezeichnung'] = $row->bezeichnung;
	$lehrfach[$row->kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]['ects'] = $row->ects;
}
//print_r($lehrfach);
// Einheiten holen
$sql_query="SELECT einheit_kurzbz,bezeichnung FROM tbl_einheit";
$result=pg_exec($conn, $sql_query);
while ($row=pg_fetch_object($result))
	$einheit[$row->einheit_kurzbz] = $row->bezeichnung;
// Raumtypen holen
$sql_query="SELECT raumtyp_kurzbz,beschreibung FROM tbl_raumtyp";
$result=pg_exec($conn, $sql_query);
while ($row=pg_fetch_object($result))
	$raumtyp[$row->raumtyp_kurzbz] = $row->beschreibung;
// Lehformen holen
$sql_query="SELECT lehrform_kurzbz,bezeichnung FROM tbl_lehrform";
$result=pg_exec($conn, $sql_query);
while ($row=pg_fetch_object($result))
	$lehrform[$row->lehrform_kurzbz] = $row->bezeichnung;
//print_r($lehrfach);
echo 'FAS-Datenbank wird abgefragt!<BR><i>';
flush();

// Start Lehrveranstaltungen Synchro
$sql_query="SELECT DISTINCT fas_id,trim(lvnr) AS lvnr,trim(unr)::int8 AS unr,einheit_kurzbz,lektor,trim(upper(lehrfach_kurzbz)) AS lehrfach_kurzbz,
			trim(upper(lehrform)) AS lehrform, lehrfach_bezeichnung,
			studiengang_kz,fachbereich_id,semester,verband,gruppe,raumtyp,raumtypalternativ,
			round(semesterstunden) AS semesterstunden,stundenblockung,wochenrythmus,start_kw,anmerkung,studiensemester_kurzbz, ects
			FROM fas_view_alle_lehreinheiten_vilesci ".
		   "where ".getSemesterWhereClause();
//echo $sql_query."</i><br>";
$result=pg_exec($conn_fas, $sql_query);
$num_rows=pg_numrows($result);
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
	if ($i%100==0)
	{
		echo '-';
		flush();
	}
	$row=pg_fetch_object($result,$i);
	// Kennzahl der Studiengangs bei ehemaligen bTec auf TW aendern.
	if ($row->studiengang_kz==203)
		$row->studiengang_kz=0;
	// Lehrfach-Nr übersetzen (-1 wenn nicht vorhanden)
	$row->lehrfach_nr=getLehrfachNr($row->lehrfach_kurzbz,$row->studiengang_kz,$row->semester, $row->lehrfach_bezeichnung, $row->fachbereich_id, $row->ects, $conn);
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

	if ($row->lehrfach_nr==-1)
	{
		//$error_log[$row->studiengang_kz][]=printLVA($row).': Lehrfach (Kurzbz='".$row->lehrfach_kurzbz."',Lehrform".$row->lehrform) existiert noch nicht. Stundenplanabteilung wurde benachrichtigt.';
		if (!isset($missing_lehrfaecher[$row->lehrfach_kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester])) $missing_lehrfaecher[$row->lehrfach_kurzbz.'/'.$row->studiengang_kz.'/'.$row->semester]=1;
			$valid=false;
	}

	if (validate($row) && $row->lehrfach_nr>-1)
	{
		// SQL vorbereiten (jede LVA vom FAS im VileSci suchen)
		$sql_query="SELECT * from tbl_lehrveranstaltung where fas_id=".$row->fas_id;
		//echo $sql_query;
		$res_lva=pg_query($conn, $sql_query);
		$num_rows_lva=pg_numrows($res_lva);

		// neue LVA
		if ($num_rows_lva==0)
		{
			$text.="Die LVA fas-id=$row->fas_id lvnr=$row->lvnr unr=$row->unr wird neu angelegt.\r";
			$sql_query="INSERT INTO tbl_lehrveranstaltung (lvnr,unr,einheit_kurzbz,lektor,lehrfach_nr,lehrform_kurzbz,";
			$sql_query.="studiengang_kz,fachbereich_id,semester,verband,gruppe,raumtyp,".
                        "raumtypalternativ,semesterstunden,stundenblockung,".
                        "wochenrythmus,start_kw,studiensemester_kurzbz,fas_id,anmerkung) ".
                        "VALUES('$row->lvnr'".
						",$row->unr,".
						(strlen($row->einheit_kurzbz)>0?"'".$row->einheit_kurzbz."'":'NULL').",".
						"'$row->lektor',".
						"'$row->lehrfach_nr',".
						"'$row->lehrform',".
						"'$row->studiengang_kz',".
						"$row->fachbereich_id,".
						"$row->semester,";
			if ($row->verband==null)
				$sql_query.='NULL,';
			else
				$sql_query.="'$row->verband',";
			if ($row->gruppe==null)
				$sql_query.='NULL,';
			else
				$sql_query.="'$row->gruppe',";
			$sql_query.="'$row->raumtyp',".
						"'$row->raumtypalternativ',".
						"$row->semesterstunden,".
						"$row->stundenblockung,".
						"$row->wochenrythmus,".
						"$row->start_kw,".
						"'$row->studiensemester_kurzbz'," .
						"$row->fas_id,'$row->anmerkung')";
			//echo $sql_query.'<BR>';
			if(!$res_insert=@pg_exec($conn, $sql_query))
			{
				$text.=$sql_query;
				$text.="\nFehler: ".pg_errormessage($conn)."\n";
				$insert_error++;
			}
			else
				$anz_insert++;
		}
		// bestehende LVA
		elseif ($num_rows_lva==1)
		{
			$update_sql='';
			$row_lva=pg_fetch_object($res_lva,0);
			//var_dump($row_lva);
			//if ($row->gruppe==NULL)
			//	$row->gruppe=1;
			//echo '-'.$row->lvnr.'-'.$row_lva->lvnr.'-<BR>';
			if ($row->lvnr!=$row_lva->lvnr)
				$update_sql.="lvnr='".$row->lvnr."'";
			elseif ($row->unr!=$row_lva->unr)
				$update_sql.="unr=".$row->unr;
			elseif ($row->einheit_kurzbz!=$row_lva->einheit_kurzbz)
				$update_sql.=(strlen($update_sql)>0?',':'').'einheit_kurzbz='.(strlen($row->einheit_kurzbz)>0?"'".$row->einheit_kurzbz."'":'NULL');
			elseif ($row->lektor!=$row_lva->lektor)
				$update_sql.=(strlen($update_sql)>0?',':'')."lektor='".$row->lektor."'";
			elseif ($row->lehrfach_nr!=$row_lva->lehrfach_nr)
				$update_sql.=(strlen($update_sql)>0?',':'')."lehrfach_nr=".$row->lehrfach_nr;
			elseif ($row->lehrform!=$row_lva->lehrform_kurzbz)
				$update_sql.=(strlen($update_sql)>0?',':'')."lehrform_kurzbz='".$row->lehrform."'";
			elseif ($row->studiengang_kz!=$row_lva->studiengang_kz)
				$update_sql.=(strlen($update_sql)>0?',':'')."studiengang_kz=".$row->studiengang_kz;
			elseif ($row->fachbereich_id!=$row_lva->fachbereich_id)
				$update_sql.=(strlen($update_sql)>0?',':'')."fachbereich_id=".$row->fachbereich_id;
			elseif ($row->semester!=$row_lva->semester)
				$update_sql.=(strlen($update_sql)>0?',':'')."semester=".$row->semester;
			elseif ($row->verband!=$row_lva->verband)
				$update_sql.=(strlen($update_sql)>0?',':'')."verband=".(strlen($row->verband)>0?"'".$row->verband."'":'NULL');
			elseif ($row->gruppe!=$row_lva->gruppe)
				$update_sql.=(strlen($update_sql)>0?',':'')."gruppe=".(strlen($row->gruppe)>0?"'".$row->gruppe."'":'NULL');
			elseif ($row->raumtyp!=$row_lva->raumtyp)
				$update_sql.=(strlen($update_sql)>0?',':'')."raumtyp='".$row->raumtyp."'";
			elseif ($row->raumtypalternativ!=$row_lva->raumtypalternativ)
				$update_sql.=(strlen($update_sql)>0?',':'')."raumtypalternativ='".$row->raumtypalternativ."'";
			elseif ($row->semesterstunden!=$row_lva->semesterstunden)
				$update_sql.=(strlen($update_sql)>0?',':'')."semesterstunden=".$row->semesterstunden;
			elseif ($row->stundenblockung!=$row_lva->stundenblockung)
				$update_sql.=(strlen($update_sql)>0?',':'')."stundenblockung=".$row->stundenblockung;
			elseif ($row->wochenrythmus!=$row_lva->wochenrythmus)
				$update_sql.=(strlen($update_sql)>0?',':'')."wochenrythmus=".$row->wochenrythmus;
			elseif ($row->start_kw!=$row_lva->start_kw)
				$update_sql.=(strlen($update_sql)>0?',':'')."start_kw=".(strlen($row->start_kw)>0?$row->start_kw:'NULL');
			elseif ($row->studiensemester_kurzbz!=$row_lva->studiensemester_kurzbz)
				$update_sql.=(strlen($update_sql)>0?',':'')."studiensemester_kurzbz='".$row->studiensemester_kurzbz."'";
			elseif ($row->anmerkung!=$row_lva->anmerkung)
				$update_sql.=(strlen($update_sql)>0?',':'')."anmerkung='".$row->anmerkung."'";

			if (strlen($update_sql)>0)
			{
				$text.="Die LVA fas-id=$row->fas_id lvnr=$row->lvnr unr=$row->unr wird upgedatet.\r";
				$sql_query="UPDATE tbl_lehrveranstaltung SET ".
						$update_sql.
						" where fas_id=".$row->fas_id;

				//echo $sql_query.'<BR>';
				if(!$res_update=@pg_query($conn, $sql_query))
				{
					$text.=$sql_query;
                    $text.="\rFehler: ".pg_errormessage($conn)."\r";
					$update_error++;
				}
				else
					$anz_update++;

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
				}
			}
		}
		// LVA kommt mehrmals vor ->Warnung
		elseif ($num_rows_lva>1)
		{
			$text.="\r!!! Die LVA fas_id=$row->fas_id kommt mehrfach vor!\r";
			$double_error++;
		}
	}
	else
		$plausi_error++;
}


// ****************
// Ueberfluessige Datensaetze loeschen
$whereClause=getSemesterWhereClause();
$sql_query="DELETE FROM tbl_lehrveranstaltung WHERE fas_id NOT IN
	(SELECT fas_id FROM vw_fas_lehrveranstaltung WHERE $whereClause) AND (fas_id!=0 OR fas_id IS NOT NULL) AND ($whereClause)";
echo $sql_query.'<BR>';
if(!$res_delete=@pg_query($conn, $sql_query))
{
	$text.='\n'.$sql_query;
    $text.="\rFehler: ".pg_errormessage($conn)."\r";
    $text.="\rSolution: DELETE FROM tbl_stundenplandev WHERE lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM tbl_lehrveranstaltung WHERE fas_id NOT IN (SELECT fas_id FROM vw_fas_lehrveranstaltung WHERE $whereClause) AND (fas_id!=0 OR fas_id IS NOT NULL) AND ($whereClause))\r";
}
else
{
	$anz_delete=pg_numrows($res_delete);
}

//Ausgabe Zusammenfassung
$text.="\n$anz_delete Lehrveranstaltungen wurden geloescht!\n";
$text.="$plausi_error Fehler beim Plausibilitaetscheck!\n";
$text.="$update_error Fehler bei LVA-Update!\n";
$text.="$insert_error Fehler bei LVA-Insert!\n";
$text.="$double_error LVA kommen in VileSci doppelt vor!\n\n";
$text.="$anz_update LVA wurden upgedatet.\n";
$text.="$anz_insert LVA wurden neu angelegt.\n\n";
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
	if (!mail($stg_mail[$k],"FAS Synchro mit VileSci (Lehrveranstaltungen) $k",$stg_text,"From: vilesci@technikum-wien.at"))
		echo "Mail an '".$stg_mail[$k]."' konnte nicht verschickt werden!<br>";
	// Stundenplanstelle
	echo "<br>Mail an Studiengang $k ($adress_stpl)<br>";
	if (!mail($adress_stpl,"FAS Synchro mit VileSci (Lehrveranstaltungen) $k",$stg_text,"From: vilesci@technikum-wien.at"))
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
	if (!mail($adress_fas,"FAS Synchro mit VileSci (Lehrveranstaltungen)",$mail_text,"From: vilesci@technikum-wien.at"))
		echo "Mail an '".$adress_fas."' konnte nicht verschickt werden!<br>";
	else
		echo 'Mail wurde verschickt an '.$adress_fas.'!<br>';
?>

<html>
<head>
<title>FAS-Synchro mit VileSci</title>
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
