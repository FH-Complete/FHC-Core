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

/*
// Anfangsdatum festlegen
$datum_begin=date("Y-m-d");
// Endedatum festlegen
$sql_query="SET search_path TO lehre; SELECT * FROM public.tbl_studiensemester WHERE ende>=now() ORDER BY start;";
//echo $sql_query.'<BR>';
if (!$result=pg_query($conn, $sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
	$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
}
else
{
	if ($row=pg_fetch_object($result))
		$datum_ende=$row->ende;
	else
		$message_sync.='Kein aktuelles Studiensemester gefunden! '.$sql_query.'<BR>';
}
*/

$ss=new studiensemester($conn);
$ss->getAktTillNext();
$datum_begin=$ss->start;
$datum_ende='2008-02-09'; //$ss->ende;


$message_begin='Dies ist eine automatische Mail!<BR>Es haben sich folgende Aenderungen in Ihrem Stundenplan ergeben:<BR>';


/**************************************************
 * Datensaetze holen die neu sind
 */
echo 'Neue Datens&auml;tze werden angelegt.<BR>';flush();
$sql_query="SELECT * FROM lehre.vw_stundenplandev WHERE datum>='$datum_begin' AND datum<='$datum_ende' AND
	stundenplandev_id NOT IN
	(SELECT stundenplan_id FROM lehre.tbl_stundenplan WHERE datum>='$datum_begin' AND datum<='$datum_ende');";
//echo $sql_query.'<BR>';
if (!$result=pg_query($conn, $sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
	$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
}
else
{
	flush();
	while ($row=pg_fetch_object($result))
	{
		$sql_query='INSERT INTO tbl_stundenplan
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
				if (!isset($message[$row->uid]->isneu));
				{
					$message[$row->uid]->isneu=true;
					$message[$row->uid]->mailadress=$row->uid.'@technikum-wien.at';
					$message[$row->uid]->message=$message_begin.'<BR>Neue Stunden:<BR>
						<TABLE><TR><TD>Ort</TD><TD>Verband</TD><TD>Lektor</TD><TD>Datum/Std</TD><TD>Lehrfach</TD></TR>';
				}
				$message[$row->uid]->message.='<TR><TH>'.$row->ort_kurzbz.'</TH>';
				$message[$row->uid]->message.='<TH>'.$row->stg_typ.$row->stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
				$message[$row->uid]->message.='<TH>'.$row->lektor.'</TH>';
				$message[$row->uid]->message.='<TH>'.$row->datum.'/'.$row->stunde.'</TH>';
				$message[$row->uid]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';
			}
			// Verband
			$verband=$row->stg_typ.$row->stg_kurzbz.$row->semester.$row->verband.$row->gruppe;
			$verband=trim($verband);
			$verband=strtolower($verband);
			if (!isset($message[$verband]->isneu));
			{
				$message[$verband]->isneu=true;
				$message[$verband]->mailadress=$verband.'@technikum-wien.at';
				$message[$verband]->message=$message_begin.'<BR>Neue Stunden:<BR>
						<TABLE><TR><TD>Ort</TD><TD>Verband</TD><TD>Lektor</TD><TD>Datum/Std</TD><TD>Lehrfach</TD></TR>';
			}
			$message[$verband]->message.='<TR><TH>'.$row->ort_kurzbz.'</TH>';
			$message[$verband]->message.='<TH>'.$row->stg_typ.$row->stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
			$message[$verband]->message.='<TH>'.$row->lektor.'</TH>';
			$message[$verband]->message.='<TH>'.$row->datum.'/'.$row->stunde.'</TH>';
			$message[$verband]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';
		}
	}
	foreach($message as $msg)
		if($msg->isneu)
			$msg->message.='</table>';
}

/**************************************************
* Datensaetze holen die alt sind
*/

echo '<BR>Alte Datens&auml;tze werden gel&ouml;scht.<BR>';flush();
$sql_query="SELECT * FROM vw_stundenplan WHERE datum>='$datum_begin' AND datum<='$datum_ende'
				AND stundenplan_id NOT IN
				(SELECT stundenplandev_id FROM tbl_stundenplandev WHERE datum>='$datum_begin' AND datum<='$datum_ende');";
if (!$result=pg_query($conn, $sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
	$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
}
while ($row=pg_fetch_object($result))
{
	$sql_query='DELETE FROM tbl_stundenplan WHERE stundenplan_id='.$row->stundenplan_id;
	//echo $sql_query.'<BR>';
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
			if (!isset($message[$row->uid]->isalt));
			{
				$message[$row->uid]->isalt=true;
				$message[$row->uid]->mailadress=$row->uid.'@technikum-wien.at';
				$message[$row->uid]->message.=$message_begin.'<BR>Gel&ouml;eschte Stunden:<BR>
					<TABLE><TR><TD>Ort</TD><TD>Verband</TD><TD>Lektor</TD><TD>Datum/Std</TD><TD>Lehrfach</TD></TR>';
			}
			$message[$row->uid]->message.='<TR><TH>'.$row->ort_kurzbz.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->stg_typ.$row->stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->lektor.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->datum.'/'.$row->stunde.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';
		}
		// Verband
		$verband=$row->stg_typ.$row->stg_kurzbz.$row->semester.$row->verband.$row->gruppe;
		$verband=trim($verband);
		$verband=strtolower($verband);
		if (!isset($message[$verband]->isalt));
		{
			$message[$verband]->isalt=true;
			$message[$verband]->mailadress=$verband.'@technikum-wien.at';
			$message[$verband]->message.=$message_begin.'<BR>Geaenderte Stunden:<BR>
					<TABLE><TR><TD>Ort</TD><TD>Verband</TD><TD>Lektor</TD><TD>Datum/Std</TD><TD>Lehrfach</TD></TR>';
		}
		$message[$verband]->message.='<TR><TH>'.$row->ort_kurzbz.'</TH>';
		$message[$verband]->message.='<TH>'.$row->stg_typ.$row->stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
		$message[$verband]->message.='<TH>'.$row->lektor.'</TH>';
		$message[$verband]->message.='<TH>'.$row->datum.'/'.$row->stunde.'</TH>';
		$message[$verband]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';
	}
	foreach($message as $msg)
		if($msg->isalt)
			$msg->message.='</table>';
}


/**************************************************
 * Datensaetze holen die anders sind
 */

echo '<BR>Datens&auml;tze werden ge&auml;ndert.<BR>';flush();
$sql_query="SELECT vw_stundenplandev.*, vw_stundenplan.datum AS old_datum, vw_stundenplan.stunde AS old_stunde,
				vw_stundenplan.ort_kurzbz AS old_ort_kurzbz, vw_stundenplan.lektor AS old_lektor
			FROM vw_stundenplandev, vw_stundenplan
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
while ($row=pg_fetch_object($result))
{
	// Alten Eintrag aus tbl_stundenplan holen
	$sql_query="SELECT * FROM tbl_stundenplandev WHERE stundenplandev_id=$row->stundenplandev_id;";
	if (!$result_old=pg_query($conn, $sql_query))
	{
		echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
		$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
	}
	else
		$row_old=pg_fetch_object($result_old);

	// Datensaetze aendern
	$sql_query="UPDATE tbl_stundenplan SET
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
			if (!isset($message[$row->uid]->isset));
			{
				$message[$row->uid]->isset=true;
				$message[$row->uid]->mailadress=$row->uid.'@technikum-wien.at';
				$message[$row->uid]->message.=$message_begin.'<BR>Ge&auml;nderte Stunden:<BR>
					<TABLE><TR><TD>Status</TD><TD>Ort</TD><TD>Verband</TD><TD>Lektor</TD><TD>Datum/Std</TD><TD>Lehrfach</TD></TR>';
			}
			$message[$row->uid]->message.='<TR><TH>Vorher: </TH><TH>'.$row->old_ort_kurzbz.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->stg_typ.$row->stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->old_lektor.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->old_datum.'/'.$row->old_stunde.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';

			$message[$row->uid]->message.='<TR><TH>Jetzt: </TH><TH>'.$row->ort_kurzbz.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->stg_typ.$row->stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->lektor.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->datum.'/'.$row->stunde.'</TH>';
			$message[$row->uid]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';
		}
		// Verband
		$verband=$row->stg_typ.$row->stg_kurzbz.$row->semester.$row->verband.$row->gruppe;
		$verband=trim($verband);
		$verband=strtolower($verband);
		if (!isset($message[$verband]->isset));
		{
			$message[$verband]->isset=true;
			$message[$verband]->mailadress=$verband.'@technikum-wien.at';
			$message[$verband]->message.=$message_begin.'<BR>Ge&auml;nderte Stunden:<BR>
					<TABLE><TR><TD>Status</TD><TD>Ort</TD><TD>Verband</TD><TD>Lektor</TD><TD>Datum/Std</TD><TD>Lehrfach</TD></TR>';
		}
		$message[$verband]->message.='<TR><TH>Vorher: </TH><TH>'.$row->old_ort_kurzbz.'</TH>';
		$message[$verband]->message.='<TH>'.$row->stg_typ.$row->stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
		$message[$verband]->message.='<TH>'.$row->old_lektor.'</TH>';
		$message[$verband]->message.='<TH>'.$row->old_datum.'/'.$row->old_stunde.'</TH>';
		$message[$verband]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';

		$message[$verband]->message.='<TR><TH>Jetzt: </TH><TH>'.$row->ort_kurzbz.'</TH>';
		$message[$verband]->message.='<TH>'.$row->stg_typ.$row->stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TH>';
		$message[$verband]->message.='<TH>'.$row->lektor.'</TH>';
		$message[$verband]->message.='<TH>'.$row->datum.'/'.$row->stunde.'</TH>';
		$message[$verband]->message.='<TH>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TH></TR>';
	}
	foreach($message as $msg)
		if($msg->isset)
			$msg->message.='</table>';
}

/**************************************************
 * Mails an Lektoren und Verbaende schicken
 */
if ($sendmail)
	foreach ($message as $msg)
		//if (mail('pam@technikum-wien.at',"Stundenplan update",$msg->message,$headers."From: stpl@technikum-wien.at"))
		if (mail($msg->mailadress,"Stundenplan update",$msg->message,$headers."From: stpl@technikum-wien.at"))
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
mail('pam@technikum-wien.at',"Stundenplan update",$message_sync,$headers."From: stpl@technikum-wien.at");
$message_stpl='<HTML><BODY>'.$message_tmp.$message_stpl.'</BODY></HTML>';
mail('stpl@technikum-wien.at',"Stundenplan update",$message_stpl,$headers."From: stpl@technikum-wien.at");
?>
</body>
</html>
