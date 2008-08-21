<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Stundenplan-Synchro (stpldev-stpl)</title>
</head>
<body>
<?php
require('../../vilesci/config.inc.php');
require('../../include/functions.inc.php');
require('../../include/studiensemester.class.php');

$conn=pg_connect(CONN_STRING);

// Startvariablen setzen
$adress='fas_sync@technikum-wien.at';
//$adress_stpl='pam@technikum-wien.at';
$adress_stpl='stpl@technikum-wien.at';
if (isset($_GET['sendmail']))
{
	if ($_GET['sendmail']=='true')
		$sendmail=(boolean)true;
	else
		$sendmail=(boolean)false;
}
else
	$sendmail=(boolean)true;

$count_del=0;
$count_ins=0;
$count_upd=0;
$count_err=0;

// error log fuer jeden Studiengang
$error_log=array();
// Mails an die Lektoren und Verbaende
$message=array();
// Nachrichten fuer die Stundenplanstelle
$message_stpl='';
// error_log
$message_sync='';


// Mail Headers festlegen
$headers= "MIME-Version: 1.0\r\n";
$headers.="Content-Type: text/html; charset=iso-8859-1\r\n";

$ss=new studiensemester($conn);
$ss->getAktTillNext();
$datum_begin=$ss->start;
//$datum_begin='2008-01-07';
$datum_ende='2009-02-09'; // $ss->ende
//$datum_ende='2008-01-18'; // $ss->ende

// ************* FUNCTIONS **************** //

function getStudentsFromGroup($studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz, $studiensemester_kurzbz)
{
	global $conn;

	$students = array();
	if($gruppe_kurzbz=='')
	{
		$qry = "SELECT
					distinct student_uid
				FROM
					public.tbl_studentlehrverband
				WHERE
					studiensemester_kurzbz='$studiensemester_kurzbz' AND
					studiengang_kz = '$studiengang_kz' AND
					semester = '$semester'";
		if(trim($verband)!='')
		{
			$qry.=" AND verband = '$verband'";
			if(trim($gruppe)!='')
			{
				$qry.=" AND gruppe = '$gruppe'";
			}
		}
	}
	else
	{
		$qry = "SELECT
					distinct uid as student_uid
				FROM
					public.tbl_benutzergruppe
				WHERE
					gruppe_kurzbz='$gruppe_kurzbz' AND
					studiensemester_kurzbz='$studiensemester_kurzbz'
				";

	}

	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$students[]=$row->student_uid;
		}
	}
	//echo "students $qry:";
	//var_dump($students);
	return $students;
}

// **************************************** //
$message_begin='
<style>
.marked
{
	color:red;
}
.unmarked
{
}
</style>
Dies ist eine automatische Mail!<BR>Es haben sich folgende Aenderungen in Ihrem Stundenplan ergeben:<BR>';

/**************************************************
 * Datensaetze holen die neu sind
 */
echo 'Neue Datens&auml;tze werden geholt. ('.date('H:i:s').')<BR>';flush();
$message_stpl .= 'Neue Datens&auml;tze werden geholt. ('.date('H:i:s').')';
//$sql_query="SELECT * FROM lehre.vw_stundenplandev WHERE datum>='$datum_begin' AND datum<='$datum_ende' AND
//	stundenplandev_id NOT IN
//	(SELECT stundenplan_id FROM lehre.tbl_stundenplan WHERE datum>='$datum_begin' AND datum<='$datum_ende')
//	ORDER BY datum, stunde;";
$sql_query="SELECT * FROM lehre.vw_stundenplandev WHERE datum>='$datum_begin' AND datum<='$datum_ende' AND
	NOT EXISTS
	(SELECT stundenplan_id FROM lehre.tbl_stundenplan WHERE datum>='$datum_begin' AND datum<='$datum_ende' AND stundenplan_id=stundenplandev_id)
	ORDER BY datum, stunde;";

//echo $sql_query.'<BR>';
if (!$result=pg_query($conn, $sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
	$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
}
else
{
	echo 'Neue Datens&auml;tze werden angelegt.<BR>';flush();
	while ($row=pg_fetch_object($result))
	{
		//echo '.';flush();
		$sql_query='INSERT INTO lehre.tbl_stundenplan
			(stundenplan_id,unr,mitarbeiter_uid,datum,stunde,ort_kurzbz,studiengang_kz,semester,verband,gruppe,
			gruppe_kurzbz,titel,anmerkung,fix,updateamum,updatevon,insertamum,insertvon,lehreinheit_id) VALUES';
		$sql_query.="($row->stundenplandev_id,$row->unr,'$row->uid','$row->datum',$row->stunde,'$row->ort_kurzbz',
			$row->studiengang_kz,$row->semester";
		if ($row->verband==null)
			$sql_query.=',NULL';
		else
			$sql_query.=",'$row->verband'";
		if ($row->gruppe==null)
			$sql_query.=',NULL';
		else
			$sql_query.=",'$row->gruppe'";
		if ($row->gruppe_kurzbz==null)
			$sql_query.=',NULL';
		else
			$sql_query.=",'$row->gruppe_kurzbz'";
		$sql_query.=",'$row->titel','$row->anmerkung'";
		if ($row->fix=='t')
			$sql_query.=',TRUE';
		else
			$sql_query.=',FALSE';
		$sql_query.=",'$row->updateamum','$row->updatevon','$row->insertamum','$row->insertvon'";
		if ($row->lehreinheit_id==null)
			$sql_query.=',NULL';
		else
			$sql_query.=",$row->lehreinheit_id";
		$sql_query.=');';
		//echo $sql_query;
		if (!$result_insert=pg_query($conn, $sql_query))
		{
			echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
			$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
			$count_err++;
		}
		else
		{
			$count_ins++;
			if ($count_ins%10==0)
			{
				echo '-';
				flush();
			}
			// Mails vorbereiten
			// Lektoren
			if (substr($row->uid,0,1)!='_')
			{
				if (!isset($message[$row->uid]->isneu))
				{
					$message[$row->uid]->isneu=true;
					$message[$row->uid]->mailadress=$row->uid.'@technikum-wien.at';
					$message[$row->uid]->message_begin=$message_begin.'<BR>';
					$message[$row->uid]->message='Neue Stunden:<BR>
						<TABLE><TR><TD>Ort</TD><TD>Verband</TD><TD>Lektor</TD><TD>Datum/Std</TD><TD>Lehrfach</TD></TR>';
				}
				$message[$row->uid]->message.='<TR><TH>'.$row->ort_kurzbz.'</TH>';
				$message[$row->uid]->message.='<TH>'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
				$message[$row->uid]->message.='<TH>'.$row->lektor.'</TH>';
				$message[$row->uid]->message.='<TH>'.$row->datum.'/'.$row->stunde.'</TH>';
				$message[$row->uid]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';
			}
			// Verband
			$studenten = getStudentsFromGroup($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $ss->studiensemester_kurzbz);
			//$verband=$row->stg_typ.$row->stg_kurzbz.$row->semester.$row->verband.$row->gruppe;
			//$verband=trim($verband);
			//$verband=strtolower($verband);
			foreach ($studenten as $student)
			{
				if (!isset($message[$student]->isneu))
				{
					$message[$student]->isneu=true;
					$message[$student]->mailadress=$student.'@technikum-wien.at';
					$message[$student]->message=$message_begin.'<BR>Neue Stunden:<BR>
							<TABLE><TR><TD>Ort</TD><TD>Verband</TD><TD>Lektor</TD><TD>Datum/Std</TD><TD>Lehrfach</TD></TR>';
				}
				$message[$student]->message.='<TR><TH>'.$row->ort_kurzbz.'</TH>';
				$message[$student]->message.='<TH>'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
				$message[$student]->message.='<TH>'.$row->lektor.'</TH>';
				$message[$student]->message.='<TH>'.$row->datum.'/'.$row->stunde.'</TH>';
				$message[$student]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';
			}
		}
	}
	foreach($message as $msg)
		if($msg->isneu)
			$msg->message.='</table>';
}

/**************************************************
* Datensaetze holen die alt sind
*/

echo '<BR>Alte Datens&auml;tze werden geholt.('.date('H:i:s').')<BR>';flush();
$message_stpl .='<BR>Alte Datens&auml;tze werden geholt.('.date('H:i:s').')<BR>';
//$sql_query="SELECT * FROM lehre.vw_stundenplan WHERE datum>='$datum_begin' AND datum<='$datum_ende'
//				AND stundenplan_id NOT IN
//				(SELECT stundenplandev_id FROM lehre.tbl_stundenplandev WHERE datum>='$datum_begin' AND datum<='$datum_ende');";
$sql_query="SELECT * FROM lehre.vw_stundenplan WHERE datum>='$datum_begin' AND datum<='$datum_ende'
				AND NOT EXISTS
				(SELECT stundenplandev_id FROM lehre.tbl_stundenplandev WHERE datum>='$datum_begin' AND datum<='$datum_ende' AND stundenplandev_id=stundenplan_id);";
if (!$result=pg_query($conn, $sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
	$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
}
else
{
	echo '<BR>Alte Datens&auml;tze werden gel&ouml;scht.<BR>';flush();
	while ($row=pg_fetch_object($result))
	{
		$sql_query='DELETE FROM lehre.tbl_stundenplan WHERE stundenplan_id='.$row->stundenplan_id;
		//echo '.';flush();
		//echo $sql_query.'<BR>';
		//var_dump($message);
		if (!$result_delete=pg_query($conn, $sql_query))
		{
			echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
			$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
			$count_err++;
		}
		else
		{
			$count_del++;
			if ($count_del%10==0)
			{
				echo '-';
				flush();
			}
			// Mails vorbereiten
			// Lektoren
			if (substr($row->uid,0,1)!='_')
			{
				if (!isset($message[$row->uid]->isalt))
				{
					$message[$row->uid]->isalt=true;
					$message[$row->uid]->mailadress=$row->uid.'@technikum-wien.at';
					$message[$row->uid]->message_begin=$message_begin.'<BR>';
					$message[$row->uid]->message.='Gel&ouml;eschte Stunden:<BR>
						<TABLE><TR><TD>Ort</TD><TD>Verband</TD><TD>Lektor</TD><TD>Datum/Std</TD><TD>Lehrfach</TD></TR>';
				}
				$message[$row->uid]->message.='<TR><TH>'.$row->ort_kurzbz.'</TH>';
				$message[$row->uid]->message.='<TH>'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
				$message[$row->uid]->message.='<TH>'.$row->lektor.'</TH>';
				$message[$row->uid]->message.='<TH>'.$row->datum.'/'.$row->stunde.'</TH>';
				$message[$row->uid]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';
			}
			// Verband
			$studenten = getStudentsFromGroup($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $ss->studiensemester_kurzbz);
			//$verband=$row->stg_typ.$row->stg_kurzbz.$row->semester.$row->verband.$row->gruppe;
			//$verband=trim($verband);
			//$verband=strtolower($verband);
			foreach ($studenten as $student)
			{
				if (!isset($message[$student]->isalt))
				{
					$message[$student]->isalt=true;
					$message[$student]->mailadress=$student.'@technikum-wien.at';
					$message[$student]->message_begin=$message_begin.'<BR>';
					$message[$student]->message.='Geaenderte Stunden:<BR>
							<TABLE><TR><TD>Ort</TD><TD>Verband</TD><TD>Lektor</TD><TD>Datum/Std</TD><TD>Lehrfach</TD></TR>';
				}
				$message[$student]->message.='<TR><TH>'.$row->ort_kurzbz.'</TH>';
				$message[$student]->message.='<TH>'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
				$message[$student]->message.='<TH>'.$row->lektor.'</TH>';
				$message[$student]->message.='<TH>'.$row->datum.'/'.$row->stunde.'</TH>';
				$message[$student]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';
			}
		}
	}
	foreach($message as $msg)
		if(isset($msg->isalt))
			$msg->message.='</TABLE>';
}

/**************************************************
 * Datensaetze holen die anders sind
 */

echo '<BR>Ge&auml;nderte Datens&auml;tze werden geholt.('.date('H:i:s').')<BR>';flush();
$message_stpl.='<BR>Ge&auml;nderte Datens&auml;tze werden geholt.('.date('H:i:s').')<BR>';
$sql_query="SELECT vw_stundenplandev.*, vw_stundenplan.datum AS old_datum, vw_stundenplan.stunde AS old_stunde,
				vw_stundenplan.ort_kurzbz AS old_ort_kurzbz, vw_stundenplan.lektor AS old_lektor, vw_stundenplan.uid AS old_uid
			FROM lehre.vw_stundenplandev, lehre.vw_stundenplan
			WHERE vw_stundenplan.stundenplan_id=vw_stundenplandev.stundenplandev_id AND (
				vw_stundenplandev.unr!=vw_stundenplan.unr OR
				vw_stundenplandev.uid!=vw_stundenplan.uid OR
		 		vw_stundenplandev.datum!=vw_stundenplan.datum OR
				vw_stundenplandev.stunde!=vw_stundenplan.stunde OR
				vw_stundenplandev.ort_kurzbz!=vw_stundenplan.ort_kurzbz OR
				vw_stundenplandev.studiengang_kz!=vw_stundenplan.studiengang_kz OR
				vw_stundenplandev.semester!=vw_stundenplan.semester OR
				vw_stundenplandev.verband!=vw_stundenplan.verband OR
				vw_stundenplandev.gruppe!=vw_stundenplan.gruppe OR
				vw_stundenplandev.gruppe_kurzbz!=vw_stundenplan.gruppe_kurzbz OR
				vw_stundenplandev.titel!=vw_stundenplan.titel OR
				vw_stundenplandev.anmerkung!=vw_stundenplan.anmerkung OR
				vw_stundenplandev.fix!=vw_stundenplan.fix OR
				vw_stundenplandev.lehreinheit_id!=vw_stundenplan.lehreinheit_id )
				AND vw_stundenplandev.datum>='$datum_begin';";
//echo $sql_query.'<BR>';
if (!$result=pg_query($conn, $sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
	$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
}
else
{
	echo '<BR>Datens&auml;tze werden ge&auml;ndert.<BR>';flush();
	while ($row=pg_fetch_object($result))
	{
		//echo '.';flush();
		// Alten Eintrag aus tbl_stundenplan holen
		$sql_query="SELECT * FROM lehre.tbl_stundenplandev WHERE stundenplandev_id=$row->stundenplandev_id;";
		if (!$result_old=pg_query($conn, $sql_query))
		{
			echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
			$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
		}
		else
			$row_old=pg_fetch_object($result_old);

		// Datensaetze aendern
		$sql_query="UPDATE lehre.tbl_stundenplan SET
			unr=$row->unr,mitarbeiter_uid='$row->uid',datum='$row->datum',stunde=$row->stunde,
			ort_kurzbz='$row->ort_kurzbz',studiengang_kz=$row->studiengang_kz,semester=$row->semester";
		if ($row->verband==null)
			$sql_query.=',verband=NULL';
		else
			$sql_query.=",verband='$row->verband'";
		if ($row->gruppe==null)
			$sql_query.=',gruppe=NULL';
		else
			$sql_query.=",gruppe='$row->gruppe'";
		if ($row->gruppe_kurzbz==null)
			$sql_query.=',gruppe_kurzbz=NULL';
		else
			$sql_query.=",gruppe_kurzbz='$row->gruppe_kurzbz'";
		$sql_query.=",titel='$row->titel',anmerkung='$row->anmerkung'";
		if ($row->fix=='t')
			$sql_query.=',fix=TRUE';
		else
			$sql_query.=',fix=FALSE';
		$sql_query.=",updateamum='$row->updateamum',updatevon='$row->updatevon'";
		if ($row->lehreinheit_id==null)
			$sql_query.=',lehreinheit_id=NULL';
		else
			$sql_query.=",lehreinheit_id=$row->lehreinheit_id";
		$sql_query.=" WHERE stundenplan_id=$row->stundenplandev_id;";
		echo $sql_query.'<BR>';
		if (!$result_update=pg_query($conn, $sql_query))
		{
			echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
			$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
			$count_err++;
		}
		else
		{
			$count_upd++;
			if ($count_upd%10==0)
			{
				echo '-';
				flush();
			}
			// Mails vorbereiten
			// Lektoren
			if (substr($row->uid,0,1)!='_')
			{
				if (!isset($message[$row->uid]->isset))
				{
					$message[$row->uid]->isset=true;
					$message[$row->uid]->mailadress=$row->uid.'@technikum-wien.at';
					$message[$row->uid]->message_begin=$message_begin.'<BR>';
					$message[$row->uid]->message.='Ge&auml;nderte Stunden:<BR>
						<TABLE><TR><TH>Status</TH><TH>Ort</TH><TH>Verband</TH><TH>Lektor</TH><TH>Datum/Std</TH><TH>Lehrfach</TH></TR>';
				}
				$message[$row->uid]->message.='<TR><TD>Vorher: </TD>';
				$message[$row->uid]->message.='<TD>'.$row->old_ort_kurzbz.'</TD>';
				$message[$row->uid]->message.='<TD>'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->old_lektor.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->old_datum.'/'.$row->old_stunde.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD></TR>';

				$message[$row->uid]->message.='<TR><TD>Jetzt: </TD>';
				$myclass=($row->ort_kurzbz!=$row->old_ort_kurzbz?'marked':'unmarked');
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.$row->ort_kurzbz.'</span></TD>';
				$myclass='unmarked';
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</span></TD>';
				$myclass=($row->lektor!=$row->old_lektor?'marked':'unmarked');
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.$row->lektor.'</span></TD>';
				$myclass=(($row->datum!=$row->old_datum) || ($row->stunde!=$row->old_stunde)?'marked':'unmarked');
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.$row->datum.'/'.$row->stunde.'</span></TD>';
				$myclass='unmarked';
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</span></TD></TR>';
			}

			//wenn sich der Lektor geaendert hat dann auch den vorherigen lektor informieren
			//sofern es kein dummylektor ist
			if($row->uid!=$row->old_uid)
			{
				if (substr($row->old_uid,0,1)!='_')
				{
					if (!isset($message[$row->old_uid]->isset))
					{
						$message[$row->old_uid]->isset=true;
						$message[$row->old_uid]->mailadress=$row->old_uid.'@technikum-wien.at';
						$message[$row->old_uid]->message_begin=$message_begin.'<BR>';
						$message[$row->old_uid]->message.='Ge&auml;nderte Stunden:<BR>
							<TABLE><TR><TH>Status</TH><TH>Ort</TH><TH>Verband</TH><TH>Lektor</TH><TH>Datum/Std</TH><TH>Lehrfach</TH></TR>';
					}
					$message[$row->old_uid]->message.='<TR><TD>Vorher: </TD>';
					$message[$row->old_uid]->message.='<TD>'.$row->old_ort_kurzbz.'</TD>';
					$message[$row->old_uid]->message.='<TD>'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
					$message[$row->old_uid]->message.='<TD>'.$row->old_lektor.'</TD>';
					$message[$row->old_uid]->message.='<TD>'.$row->old_datum.'/'.$row->old_stunde.'</TD>';
					$message[$row->old_uid]->message.='<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD></TR>';

					$message[$row->old_uid]->message.='<TR><TD>Jetzt: </TD>';
					$myclass=($row->ort_kurzbz!=$row->old_ort_kurzbz?'marked':'unmarked');
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.$row->ort_kurzbz.'</span></TD>';
					$myclass='unmarked';
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</span></TD>';
					$myclass=($row->lektor!=$row->old_lektor?'marked':'unmarked');
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.$row->lektor.'</span></TD>';
					$myclass=(($row->datum!=$row->old_datum) || ($row->stunde!=$row->old_stunde)?'marked':'unmarked');
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.$row->datum.'/'.$row->stunde.'</span></TD>';
					$myclass='unmarked';
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</span></TD></TR>';
				}
			}
			// Verband
			$studenten = getStudentsFromGroup($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $ss->studiensemester_kurzbz);
			//$verband=$row->stg_typ.$row->stg_kurzbz.$row->semester.$row->verband.$row->gruppe;
			//$verband=trim($verband);
			foreach ($studenten as $student)
			{
				//$verband=strtolower($verband);
				if (!isset($message[$student]->isset))
				{
					$message[$student]->isset=true;
					$message[$student]->mailadress=$student.'@technikum-wien.at';
					$message[$student]->message_begin=$message_begin.'<BR>';
					$message[$student]->message.='Ge&auml;nderte Stunden:<BR>
							<TABLE><TR><TH>Status</TH><TH>Ort</TH><TH>Verband</TH><TH>Lektor</TH><TH>Datum/Std</TH><TH>Lehrfach</TH></TR>';
				}
				$message[$student]->message.='<TR><TD>Vorher: </TD>';
				$message[$student]->message.='<TD>'.$row->old_ort_kurzbz.'</TD>';
				$message[$student]->message.='<TD>'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
				$message[$student]->message.='<TD>'.$row->old_lektor.'</TD>';
				$message[$student]->message.='<TD>'.$row->old_datum.'/'.$row->old_stunde.'</TD>';
				$message[$student]->message.='<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD></TR>';

				$message[$student]->message.='<TR><TD>Jetzt: </TD>';
				$myclass=($row->ort_kurzbz!=$row->old_ort_kurzbz?'marked':'unmarked');
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.$row->ort_kurzbz.'</span></TD>';
				$myclass='unmarked';
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</span></TD>';
				$myclass=($row->lektor!=$row->old_lektor?'marked':'unmarked');
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.$row->lektor.'</span></TD>';
				$myclass=(($row->datum!=$row->old_datum) || ($row->stunde!=$row->old_stunde)?'marked':'unmarked');
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.$row->datum.'/'.$row->stunde.'</span></TD>';
				$myclass='unmarked';
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</span></TD></TR>';
			}
		}
	}
	foreach($message as $msg)
		if(isset($msg->isset))
			$msg->message.='</table>';
}

/**************************************************
 * Mails an Lektoren und Studenten schicken
 */
if ($sendmail)
	foreach ($message as $msg)
		//if (mail('oesi@technikum-wien.at',"Stundenplan update - ".$msg->mailadress,$msg->message_begin.$msg->message,$headers."From: stpl@technikum-wien.at"))
		if (mail($msg->mailadress,"Stundenplan update",$msg->message_begin.$msg->message,$headers."From: stpl@technikum-wien.at"))
		{
			echo 'Mail an '.$msg->mailadress.' wurde verschickt!<BR>';
			$message_stpl.='Mail an '.$msg->mailadress.' wurde verschickt!<BR>';
		}
		else
		{
			echo 'Mail an '.$msg->mailadress.' konnte nicht verschickt werden!<BR>';
			$message_sync.='Mail an '.$msg->mailadress.' konnte ***nicht*** verschickt werden!<BR>';
		}
// Mail an Admin
$message_tmp=$count_upd.' Datens&auml;tze wurden ge&auml;ndert.<BR>
			'.$count_ins.' Datens&auml;tze wurden hinzugef&uuml;gt.<BR>
			'.$count_del.' Datens&auml;tze wurden gel&ouml;scht.<BR>
			'.$count_err.' Fehler sind dabei aufgetreten!<BR><BR>';
echo '<BR>'.$message_tmp;
$message_sync='<HTML><BODY>'.$message_tmp.$message_sync.$message_stpl.'</BODY></HTML>';
mail(MAIL_ADMIN,"Stundenplan update",$message_sync,$headers."From: ".MAIL_LVPLAN);
//mail('oesi@technikum-wien.at',"Stundenplan update",$message_sync,$headers."From: ".MAIL_LVPLAN);
$message_stpl='<HTML><BODY>'.$message_tmp.$message_stpl.'</BODY></HTML>';
mail(MAIL_LVPLAN,"Stundenplan update",$message_stpl,$headers."From: ".MAIL_LVPLAN);
//mail('oesi@technikum-wien.at',"Stundenplan update",$message_stpl,$headers."From: ".MAIL_LVPLAN);
?>
</body>
</html>
