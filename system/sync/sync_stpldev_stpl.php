<?php
/* Copyright (C) 2008 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Syncronisiert die Tabelle Stundenplandev mit der Tabelle Stundenplan
 * und versendet Benachrichtigungsmails mit den geänderten Stunden
 * an die Betroffenen Lektoren und Studenten
 * Uebersichtsmails werden an LV-Planung und Administration geschickt
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/mail.class.php');

echo '<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>LV-Plan-Synchro (stpldev-stpl)</title>
</head>
<body>';

// Startvariablen setzen
$adress='fas_sync@technikum-wien.at';
//$adress_stpl='pam@technikum-wien.at';
$adress_stpl='lvplan@technikum-wien.at';

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
$headers.="Content-Type: text/html; charset=UTF-8\r\n";

$ss=new studiensemester();
$ss->getNearestTillNext();
$datum_begin=$ss->start;
$datum_ende=$ss->ende;
//$datum_begin='2012-08-03';
$datum_ende='2013-02-02'; // $ss->ende

$stgwhere = '';
$stgwheredev = '';
if(isset($_GET['custom']))
{
	$sendmail = isset($_GET['mail']);
	$studiengang_kz=$_GET['studiengang_kz'];
	$stgwhere = " AND studiengang_kz='".addslashes($studiengang_kz)."'";
	$stgwheredev = " AND vw_stundenplandev.studiengang_kz='".addslashes($studiengang_kz)."'";
	$datum_begin = $_GET['von'];
	$datum_ende = $_GET['bis'];
}
$db =new basis_db();
// Beginnzeiten holen
$qry = "SELECT stunde,to_char(beginn, 'HH24:MI') AS beginn FROM lehre.tbl_stunde";
$beginnzeit_arr=array();
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$beginnzeit_arr[$row->stunde]=$row->beginn;
	}
}
// ************* FUNCTIONS **************** //

function getStudentsFromGroup($studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz, $studiensemester_kurzbz)
{
	$db = new basis_db();
	
	$students = array();
	if($gruppe_kurzbz=='')
	{
		$qry = "SELECT
					distinct student_uid
				FROM
					public.tbl_studentlehrverband
				WHERE
					studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND
					studiengang_kz = '".addslashes($studiengang_kz)."' AND
					semester = '".addslashes($semester)."'";
		if(trim($verband)!='')
		{
			$qry.=" AND verband = '".addslashes($verband)."'";
			if(trim($gruppe)!='')
			{
				$qry.=" AND gruppe = '".addslashes($gruppe)."'";
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
					gruppe_kurzbz='".addslashes($gruppe_kurzbz)."' AND
					studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'
				";

	}

	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			$students[]=$row->student_uid;
		}
	}

	return $students;
}

// **************************************** //
$message_begin='
<style>
th,td
{
	text-align:left;
}
.marked
{
	color:red;
}
.unmarked
{
}
</style>
Dies ist eine automatische Mail!<BR>Es haben sich folgende Aenderungen in Ihrem LV-Plan ergeben:<BR>';

/**************************************************
 * Datensaetze holen die neu sind
 */
echo 'Neue Datens&auml;tze werden geholt. ('.date('H:i:s').')<BR>';flush();
$message_stpl .= 'Neue Datens&auml;tze werden geholt. ('.date('H:i:s').')';

$sql_query="SELECT * FROM lehre.vw_stundenplandev WHERE datum>='".addslashes($datum_begin)."' AND datum<='".addslashes($datum_ende)."' ".$stgwhere." AND 
	NOT EXISTS
	(SELECT stundenplan_id FROM lehre.tbl_stundenplan WHERE datum>='".addslashes($datum_begin)."' AND datum<='".addslashes($datum_ende)."' AND stundenplan_id=stundenplandev_id)
	ORDER BY datum, stunde;";


if (!$result = $db->db_query($sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
	$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
}
else
{
	echo 'Neue Datens&auml;tze werden angelegt.<BR>';flush();
	while ($row = $db->db_fetch_object($result))
	{
		//echo '.';flush();
		$sql_query='INSERT INTO lehre.tbl_stundenplan
			(stundenplan_id,unr,mitarbeiter_uid,datum,stunde,ort_kurzbz,studiengang_kz,semester,verband,gruppe,
			gruppe_kurzbz,titel,fix,updateamum,updatevon,insertamum,insertvon,lehreinheit_id) VALUES'; //spalte anmerkung entfent vom kindlm am 16.03.2012 da nicht relevant fuer tbl_stundenplan und nur fuer intern gedacht
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
		//$sql_query.=",'$row->titel','$row->anmerkung'"; --> anmerkung auskommentiert vom kindlm am 16.03.2012 da nicht relevant fuer tbl_stundenplan und nur fuer intern gedacht
		$sql_query.=",'$row->titel'";
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
		if (!$result_insert = $db->db_query($sql_query))
		{
			echo $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
			$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
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
			if (mb_substr($row->uid,0,1)!='_')
			{
				if (!isset($message[$row->uid]->isneu))
				{
					$message[$row->uid]->isneu=true;
					$message[$row->uid]->mailadress=$row->uid.'@technikum-wien.at';
					$message[$row->uid]->message_begin=$message_begin.'<BR>';
					$message[$row->uid]->message='<font style="color:green"><strong>Neue Stunden:</strong></font><BR>
						<TABLE><TR><TH>Ort</TH><TH>Verband</TH><TH>Lektor</TH><TH>Datum</TH><TH>Std (Beginnzeit)</TH><TH>Lehrfach</TH><TH>Info</TH></TR>';
				}
				$message[$row->uid]->message.="\n";
				$message[$row->uid]->message.='<TR><TD>'.$row->ort_kurzbz.'</TD>';
				$message[$row->uid]->message.='<TD>'.mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->lektor.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->datum.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</TD>';
				$message[$row->uid]->message.='<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
				$message[$row->uid]->message.='<TD>'.$row->titel.'</TD></TR>';
			}
			// Verband
			$studenten = getStudentsFromGroup($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $ss->studiensemester_kurzbz);
			
			foreach ($studenten as $student)
			{
				if (!isset($message[$student]->isneu))
				{
					$message[$student]->isneu=true;
					$message[$student]->mailadress=$student.'@technikum-wien.at';
					$message[$student]->message_begin=$message_begin.'<BR>';
					$message[$student]->message='<font style="color:green"><strong>Neue Stunden:</strong></font><BR>
							<TABLE><TR><TH>Ort</TH><TH>Verband</TH><TH>Lektor</TH><TH>Datum</TH><TH>Std (Beginnzeit)</TH><TH>Lehrfach</TH><TH>Info</TH></TR>';
				}
				$message[$student]->message.="\n";
				$message[$student]->message.='<TR><TD>'.$row->ort_kurzbz.'</TD>';
				$message[$student]->message.='<TD>'.mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
				$message[$student]->message.='<TD>'.$row->lektor.'</TD>';
				$message[$student]->message.='<TD>'.$row->datum.'</TD>';
				$message[$student]->message.='<TD>'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</TD>';
				$message[$student]->message.='<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
				$message[$student]->message.='<TD>'.$row->titel.'</TD></TR>';
			}
		}
	}
	foreach($message as $msg)
		if($msg->isneu)
			$msg->message.='</table><br/>';
}

/**************************************************
* Datensaetze holen die alt sind
*/

echo '<BR>Alte Datens&auml;tze werden geholt.('.date('H:i:s').')<BR>';flush();
$message_stpl .='<BR>Alte Datens&auml;tze werden geholt.('.date('H:i:s').')<BR>';
$sql_query="SELECT * FROM lehre.vw_stundenplan WHERE datum>='".addslashes($datum_begin)."' AND datum<='".addslashes($datum_ende)."' ".$stgwhere."
				AND NOT EXISTS
				(SELECT stundenplandev_id FROM lehre.tbl_stundenplandev WHERE datum>='".addslashes($datum_begin)."' AND datum<='".addslashes($datum_ende)."' AND stundenplandev_id=stundenplan_id);";
if (!$result = $db->db_query($sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
	$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
}
else
{
	echo '<BR>Alte Datens&auml;tze werden gel&ouml;scht.<BR>';flush();
	while ($row = $db->db_fetch_object($result))
	{
		$sql_query='DELETE FROM lehre.tbl_stundenplan WHERE stundenplan_id='.$row->stundenplan_id;
		
		if (!$result_delete=$db->db_query($sql_query))
		{
			echo $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
			$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
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
			if (mb_substr($row->uid,0,1)!='_')
			{
				if (!isset($message[$row->uid]->isalt))
				{
					$message[$row->uid]->isalt=true;
					$message[$row->uid]->mailadress=$row->uid.'@technikum-wien.at';
					$message[$row->uid]->message_begin=$message_begin.'<BR>';
					$message[$row->uid]->message.='<font style="color:#FFA100"><strong>Gel&ouml;schte Stunden:</strong></font><BR>
						<TABLE><TR><TH>Ort</TH><TH>Verband</TH><TH>Lektor</TH><TH>Datum</TH><TH>Std (Beginnzeit)</TH><TH>Lehrfach</TH><TH>Info</TH></TR>';
				}
				$message[$row->uid]->message.="\n";
				$message[$row->uid]->message.='<TR><TD>'.$row->ort_kurzbz.'</TD>';
				$message[$row->uid]->message.='<TD>'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->lektor.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->datum.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</TD>';
				$message[$row->uid]->message.='<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
				$message[$row->uid]->message.='<TD>'.$row->titel.'</TD></TR>';
			}
			// Verband
			$studenten = getStudentsFromGroup($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $ss->studiensemester_kurzbz);
			
			foreach ($studenten as $student)
			{
				if (!isset($message[$student]->isalt))
				{
					$message[$student]->isalt=true;
					$message[$student]->mailadress=$student.'@technikum-wien.at';
					$message[$student]->message_begin=$message_begin.'<BR>';
					$message[$student]->message.='<font style="color:#FFA100"><strong>Gel&ouml;schte Stunden:</strong></font><BR>
							<TABLE><TR><TH>Ort</TH><TH>Verband</TH><TH>Lektor</TH><TH>Datum</TH><TH>Std (Beginnzeit)</TH><TH>Lehrfach</TH><TH>Info</TH></TR>';
				}
				$message[$student]->message.="\n";
				$message[$student]->message.='<TR><TD>'.$row->ort_kurzbz.'</TD>';
				$message[$student]->message.='<TD>'.mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
				$message[$student]->message.='<TD>'.$row->lektor.'</TD>';
				$message[$student]->message.='<TD>'.$row->datum.'</TD>';
				$message[$student]->message.='<TD>'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</TD>';
				$message[$student]->message.='<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
				$message[$student]->message.='<TD>'.$row->titel.'</TD></TR>';
			}
		}
	}
	foreach($message as $msg)
		if(isset($msg->isalt))
			$msg->message.='</TABLE><br/>';
}

/**************************************************
 * Datensaetze holen die anders sind
 */

echo '<BR>Ge&auml;nderte Datens&auml;tze werden geholt.('.date('H:i:s').')<BR>';flush();
$message_stpl.='<BR>Ge&auml;nderte Datens&auml;tze werden geholt.('.date('H:i:s').')<BR>';
$sql_query="SELECT vw_stundenplandev.*, vw_stundenplan.datum AS old_datum, vw_stundenplan.stunde AS old_stunde,
				vw_stundenplan.ort_kurzbz AS old_ort_kurzbz, vw_stundenplan.lektor AS old_lektor, 
				vw_stundenplan.uid AS old_uid, vw_stundenplan.titel AS old_titel 
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
				coalesce(vw_stundenplandev.titel,'')!=coalesce(vw_stundenplan.titel,'') OR
				vw_stundenplandev.fix!=vw_stundenplan.fix OR
				vw_stundenplandev.lehreinheit_id!=vw_stundenplan.lehreinheit_id )
				AND vw_stundenplandev.datum>='".addslashes($datum_begin)."' 
				AND vw_stundenplandev.datum<='".addslashes($datum_ende)."' ".$stgwheredev.";";
//vw_stundenplandev.anmerkung!=vw_stundenplan.anmerkung OR --> von kindlm am 16.03.2012 aus obigem SQL entfernt, da nicht relevant fuer tbl_stundenplan und nur fuer intern gedacht

//echo $sql_query.'<BR>';
	
		
if (!$result = $db->db_query($sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
	$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
}
else
{
	echo '<BR>Datens&auml;tze werden ge&auml;ndert.<BR>';flush();
	while ($row = $db->db_fetch_object($result))
	{
		//echo '.';flush();
		// Alten Eintrag aus tbl_stundenplan holen
		$sql_query="SELECT * FROM lehre.tbl_stundenplandev WHERE stundenplandev_id='".addslashes($row->stundenplandev_id)."';";
		if (!$result_old = $db->db_query($sql_query))
		{
			echo $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
			$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
		}
		else
			$row_old=$db->db_fetch_object($result_old);

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
//			$sql_query.=",gruppe_kurzbz=NULL, titel=$row->titel";   --> Das war die urspruengliche query. row->titel von kindlm am 16.03.2012 entfernt und in eigenes if geschrieben.
			$sql_query.=',gruppe_kurzbz=NULL';
		else
			$sql_query.=",gruppe_kurzbz='$row->gruppe_kurzbz'";
		//$sql_query.=",titel='$row->titel',anmerkung='$row->anmerkung'"; --> anmerkung auskommentiert vom kindlm am 16.03.2012 da nicht relevant fuer tbl_stundenplan und nur fuer intern gedacht
		if ($row->titel=='')
			$sql_query.=',titel=NULL';
		else
			$sql_query.=",titel=".$db->db_add_param($row->titel);
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
		if (!$result_update=$db->db_query($sql_query))
		{
			echo $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
			$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
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
			if (mb_substr($row->uid,0,1)!='_')
			{
				if (!isset($message[$row->uid]->isset))
				{
					$message[$row->uid]->isset=true;
					$message[$row->uid]->mailadress=$row->uid.'@technikum-wien.at';
					$message[$row->uid]->message_begin=$message_begin.'<BR>';
					$message[$row->uid]->message.='<font style="color:blue"><strong>Ge&auml;nderte Stunden:</strong></font><BR>
						<TABLE><TR><TH>Status</TH><TH>Ort</TH><TH>Verband</TH><TH>Lektor</TH><TH>Datum</TH><TH>Std (Beginnzeit)</TH><TH>Lehrfach</TH><TH>Info</TH></TR>';
				}
				$message[$row->uid]->message.="\n";
				$message[$row->uid]->message.='<TR><TD>Vorher: </TD>';
				$message[$row->uid]->message.='<TD>'.$row->old_ort_kurzbz.'</TD>';
				$message[$row->uid]->message.='<TD>'.mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->old_lektor.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->old_datum.'</TD>';
				$message[$row->uid]->message.='<TD>'.$row->old_stunde.' ('.$beginnzeit_arr[$row->old_stunde].')</TD>';
				$message[$row->uid]->message.='<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
				$message[$row->uid]->message.='<TD>'.$row->old_titel.'</TD></TR>';

				$message[$row->uid]->message.="\n";
				$message[$row->uid]->message.='<TR><TD>Jetzt: </TD>';
				$myclass=($row->ort_kurzbz!=$row->old_ort_kurzbz?'marked':'unmarked');
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.$row->ort_kurzbz.'</span></TD>';
				$myclass='unmarked';
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</span></TD>';
				$myclass=($row->lektor!=$row->old_lektor?'marked':'unmarked');
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.$row->lektor.'</span></TD>';
				$myclass=(($row->datum!=$row->old_datum)?'marked':'unmarked');
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.$row->datum.'</span></TD>';
				$myclass=(($row->stunde!=$row->old_stunde)?'marked':'unmarked');
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</span></TD>';
				$myclass='unmarked';
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</span></TD>';
				$myclass=($row->titel!=$row->old_titel?'marked':'unmarked');
				$message[$row->uid]->message.='<TD><span class="'.$myclass.'">'.$row->titel.'</span></TD></TR><TR><TD>----------------</TD></TR>';
			}

			//wenn sich der Lektor geaendert hat dann auch den vorherigen lektor informieren
			//sofern es kein dummylektor ist
			if($row->uid!=$row->old_uid)
			{
				if (mb_substr($row->old_uid,0,1)!='_')
				{
					if (!isset($message[$row->old_uid]->isset))
					{
						$message[$row->old_uid]->isset=true;
						$message[$row->old_uid]->mailadress=$row->old_uid.'@technikum-wien.at';
						$message[$row->old_uid]->message_begin=$message_begin.'<BR>';
						$message[$row->old_uid]->message.='<font style="color:blue"><strong>Ge&auml;nderte Stunden:</strong></font><BR>
							<TABLE><TR><TH>Status</TH><TH>Ort</TH><TH>Verband</TH><TH>Lektor</TH><TH>Datum</TH><TH>Std (Beginnzeit)</TH><TH>Lehrfach</TH><TH>Info</TH></TR>';
					}
					$message[$row->old_uid]->message.="\n";
					$message[$row->old_uid]->message.='<TR><TD>Vorher: </TD>';
					$message[$row->old_uid]->message.='<TD>'.$row->old_ort_kurzbz.'</TD>';
					$message[$row->old_uid]->message.='<TD>'.mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
					$message[$row->old_uid]->message.='<TD>'.$row->old_lektor.'</TD>';
					$message[$row->old_uid]->message.='<TD>'.$row->old_datum.'</TD>';
					$message[$row->old_uid]->message.='<TD>'.$row->old_stunde.' ('.$beginnzeit_arr[$row->old_stunde].')</TD>';
					$message[$row->old_uid]->message.='<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
					$message[$row->old_uid]->message.='<TD>'.$row->old_titel.'</TD></TR>';

					$message[$row->old_uid]->message.="\n";
					$message[$row->old_uid]->message.='<TR><TD>Jetzt: </TD>';
					$myclass=($row->ort_kurzbz!=$row->old_ort_kurzbz?'marked':'unmarked');
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.$row->ort_kurzbz.'</span></TD>';
					$myclass='unmarked';
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</span></TD>';
					$myclass=($row->lektor!=$row->old_lektor?'marked':'unmarked');
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.$row->lektor.'</span></TD>';
					$myclass=(($row->datum!=$row->old_datum)?'marked':'unmarked');
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.$row->datum.'</TD>';
					$myclass=(($row->stunde!=$row->old_stunde)?'marked':'unmarked');
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</span></TD>';
					$myclass='unmarked';
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</span></TD>';
					$myclass=($row->titel!=$row->old_titel?'marked':'unmarked');
					$message[$row->old_uid]->message.='<TD><span class="'.$myclass.'">'.$row->titel.'</span></TD></TR><TR><TD>----------------</TD></TR>';
				}
			}
			// Verband
			$studenten = getStudentsFromGroup($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $ss->studiensemester_kurzbz);
			
			foreach ($studenten as $student)
			{
				if (!isset($message[$student]->isset))
				{
					$message[$student]->isset=true;
					$message[$student]->mailadress=$student.'@technikum-wien.at';
					$message[$student]->message_begin=$message_begin.'<BR>';
					$message[$student]->message.='<font style="color:blue"><strong>Ge&auml;nderte Stunden:</strong></font><BR>
							<TABLE><TR><TH>Status</TH><TH>Ort</TH><TH>Verband</TH><TH>Lektor</TH><TH>Datum</TH><TH>Std (Beginnzeit)</TH><TH>Lehrfach</TH><TH>Info</TH></TR>';
				}
				$message[$student]->message.="\n";
				$message[$student]->message.='<TR><TD>Vorher: </TD>';
				$message[$student]->message.='<TD>'.$row->old_ort_kurzbz.'</TD>';
				$message[$student]->message.='<TD>'.mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
				$message[$student]->message.='<TD>'.$row->old_lektor.'</TD>';
				$message[$student]->message.='<TD>'.$row->old_datum.'</TD>';
				$message[$student]->message.='<TD>'.$row->old_stunde.' ('.$beginnzeit_arr[$row->old_stunde].')</TD>';
				$message[$student]->message.='<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
				$message[$student]->message.='<TD>'.$row->old_titel.'</TD></TR>';

				$message[$student]->message.="\n";
				$message[$student]->message.='<TR><TD>Jetzt: </TD>';
				$myclass=($row->ort_kurzbz!=$row->old_ort_kurzbz?'marked':'unmarked');
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.$row->ort_kurzbz.'</span></TD>';
				$myclass='unmarked';
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</span></TD>';
				$myclass=($row->lektor!=$row->old_lektor?'marked':'unmarked');
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.$row->lektor.'</span></TD>';
				$myclass=(($row->datum!=$row->old_datum)?'marked':'unmarked');
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.$row->datum.'</span></TD>';
				$myclass=(($row->stunde!=$row->old_stunde)?'marked':'unmarked');
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</span></TD>';
				$myclass='unmarked';
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</span></TD>';
				$myclass=($row->titel!=$row->old_titel?'marked':'unmarked');
				$message[$student]->message.='<TD><span class="'.$myclass.'">'.$row->titel.'</span></TD></TR><TR><TD>----------------</TD></TR>';
			}
		}
	}
	foreach($message as $msg)
		if(isset($msg->isset))
			$msg->message.='</table><br/>';
}

/**************************************************
 * Mails an Lektoren und Studenten schicken
 */
if ($sendmail)
{
	foreach ($message as $msg)
	{
		$mail = new mail($msg->mailadress,MAIL_LVPLAN,'LV-Plan update','Sie muessen diese Mail als HTML-Mail anzeigen um die LV-Plan Änderungen anzuzeigen');
		$mail->setHTMLContent($msg->message_begin.$msg->message);
		if ($mail->send())
		{
			echo 'Mail an '.$msg->mailadress.' wurde verschickt!<BR>';
			$message_stpl.='Mail an '.$msg->mailadress.' wurde verschickt!<BR>';
		}
		else
		{
			echo 'Mail an '.$msg->mailadress.' konnte nicht verschickt werden!<BR>';
			$message_sync.='Mail an '.$msg->mailadress.' konnte ***nicht*** verschickt werden!<BR>';
		}
	}
}
// Alle User bei denen sich der LVPlan veraendert hat
// werden in ein File gesichert. Bei diesen Personen wird der LVPlan im Horde aktualisiert
$users=array();
foreach ($message as $uid=>$msg)
{
	$users[]=$uid;
}

$uidfile = DOC_ROOT.'../system/hordelvplansync/lvplanupdate.txt';

// Letzte Durchlaufzeit des Scripts holen
// anhand der Aenderungszeit des Textfiles mit den UIDs
if(!$lastmod = filemtime($uidfile))
	$lastmod=time()-86400; // Wenn die Zeit nicht ermittelt werden kann, werden die letzten 24 Std genommen

// Zusaetzlich jene holen, bei denen sich die Reservierungen geaendert haben
$qry = "SELECT * FROM campus.tbl_reservierung WHERE insertamum>'".date('Y-m-d H:i:s',$lastmod)."'";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$users[] = $row->uid;
		//Wenn fuer eine Gruppe reserviert wurde, dann die Personen aus der Gruppe holen
		if($row->semester!='' || $row->verband!='' || $row->gruppe!='' || $row->gruppe_kurzbz!='')
		{
			$studenten = getStudentsFromGroup($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $ss->studiensemester_kurzbz);
			$users = array_merge($users, $studenten);
		}
	}
}
// geaenderte User in Textfile schreiben
$users = array_unique($users);
if(count($users)>0)
{
	if($fp = fopen($uidfile, 'w'))
	{
		foreach($users as $uid)
		{
			fwrite($fp, $uid."\n");
		}
		fclose($fp);

		//Horde Syncro starten
		chdir(DOC_ROOT.'../system/hordelvplansync/');
		exec('php5 synchordelvplan.php lvplanupdate.txt >>/var/log/sync/synchordelvplan.log 2>&1');
	}
}
// Mail an Admin
$message_tmp=$count_upd.' Datens&auml;tze wurden ge&auml;ndert.<BR>
			'.$count_ins.' Datens&auml;tze wurden hinzugef&uuml;gt.<BR>
			'.$count_del.' Datens&auml;tze wurden gel&ouml;scht.<BR>
			'.$count_err.' Fehler sind dabei aufgetreten!<BR><BR>';
echo '<BR>'.$message_tmp;

$message_sync='<HTML><BODY>'.$message_tmp.$message_sync.$message_stpl.'</BODY></HTML>';
$mail = new mail(MAIL_ADMIN,MAIL_LVPLAN,'LV-Plan update','Sie muessen diese Mail als HTML-Mail anzeigen um die LV-Plan Änderungen anzuzeigen');
$mail->setHTMLContent($message_sync);
$mail->send();
$message_stpl='<HTML><BODY>'.$message_tmp.$message_stpl.'</BODY></HTML>';
$mail = new mail(MAIL_LVPLAN, MAIL_LVPLAN, 'LV-Plan update', 'Sie muessen diese Mail als HTML-Mail anzeigen um die LV-Plan Änderungen anzuzeigen');
$mail->setHTMLContent($message_stpl);
$mail->send();
?>
</body>
</html>
