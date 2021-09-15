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
 * und versendet Benachrichtigungsmails mit den geaenderten Stunden
 * an die Betroffenen Lektoren und Studenten
 * Uebersichtsmails werden an LV-Planung und Administration geschickt
 */

require_once(dirname(__FILE__).'/../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../config/global.config.inc.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../include/mail.class.php');
require_once(dirname(__FILE__).'/../../include/datum.class.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');

$p_de = new phrasen('German');
$p_en = new phrasen('English');

$sendmail = (boolean)true;

if (isset($_GET['sendmail']))
{
	if ($_GET['sendmail']=='true')
		$sendmail = (boolean)true;
	else
		$sendmail = (boolean)false;
}

$mailstudents = (boolean)true;

// Commandline Paramter parsen bei Aufruf ueber Cronjob
// zb php sync_stpldev_stpl.php --sendmail false
$longopt = array(
	"sendmail:",
);

$commandlineparams = getopt('', $longopt);
if (isset($commandlineparams['sendmail']) && $commandlineparams['sendmail']=='false')
	$sendmail = false;

$datum = new datum();

$count_del = 0;
$count_ins = 0;
$count_upd = 0;
$count_err = 0;

// Mails an die Lektoren und Verbaende
$message = array();

// Nachrichten fuer die LV-Planung
$message_stpl = '
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
	span.engl
	{
		color:gray;
		size:small;
	}
	</style>';

$message_sync = '';

$stsem = new studiensemester();
$studiensemester = $stsem->getaktorNearest();
$ss = new studiensemester($studiensemester);
$datum_begin = $ss->start;
$datum_ende = $ss->ende;

//$datum_begin='2012-08-03';
//$datum_ende='2013-02-02'; // $ss->ende
if (defined('LVPLAN_SYNC_ENDE') && LVPLAN_SYNC_ENDE != '')
	$datum_ende = LVPLAN_SYNC_ENDE;

$db = new basis_db();
$stgwhere = '';
$stgwheredev = '';

if (isset($_GET['custom']))
{
	$sendmail = isset($_GET['mail']);
	$mailstudents = (isset($_GET['nostudentmail'])?false:true);
	$studiengang_kz = $_GET['studiengang_kz'];
	$stgwhere = " AND studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
	$stgwheredev = " AND vw_stundenplandev.studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_INTEGER);
	$datum_begin = $datum->formatDatum($_GET['von'],'Y-m-d');
	$datum_ende = $datum->formatDatum($_GET['bis'],'Y-m-d');
}

$message_summary = '';

// Beginnzeiten holen
$qry = "SELECT stunde,to_char(beginn, 'HH24:MI') AS beginn FROM lehre.tbl_stunde";
$beginnzeit_arr = array();

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$beginnzeit_arr[$row->stunde] = $row->beginn;
	}
}

// ************* FUNCTIONS **************** //

function getStudentsFromGroup($studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz, $studiensemester_kurzbz)
{
	$db = new basis_db();

	$students = array();
	if ($gruppe_kurzbz=='')
	{
		$qry = "SELECT
					distinct student_uid
				FROM
					public.tbl_studentlehrverband
				LEFT JOIN
					public.tbl_benutzer ON (uid=student_uid)
				WHERE
					tbl_benutzer.aktiv=true AND
					studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
					studiengang_kz = ".$db->db_add_param($studiengang_kz)." AND
					semester = ".$db->db_add_param($semester);

		if (trim($verband) != '')
		{
			$qry .= " AND verband = ".$db->db_add_param($verband);
			if (trim($gruppe) != '')
			{
				$qry .= " AND gruppe = ".$db->db_add_param($gruppe);
			}
		}
	}
	else
	{
		$qry = "SELECT
					distinct uid as student_uid
				FROM
					public.tbl_benutzergruppe
				LEFT JOIN
					public.tbl_benutzer USING (uid)
				WHERE
					gruppe_kurzbz=".$db->db_add_param($gruppe_kurzbz)." AND
					studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
					tbl_benutzer.aktiv=true
				";

	}

	if ($db->db_query($qry))
	{
		while ($row = $db->db_fetch_object())
		{
			$students[] = $row->student_uid;
		}
	}

	return $students;
}

// **************************************** //

$message_begin = '
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
	span.engl
	{
		color:gray;
		size:small;
	}
	</style>
	'.$p_de->t('lvplan/automatischeMail').'<BR>'.$p_de->t('lvplan/folgendeAenderungen').'<BR>
	<span class="engl">'.$p_en->t('lvplan/automatischeMail').'<BR>'.$p_en->t('lvplan/folgendeAenderungen').'<BR></span>';

/**
 * Datensaetze holen die neu sind
 */

echo 'Neue Datensaetze werden geholt. ('.date('H:i:s').')'."\n";
flush();

$message_stpl .= 'Neue Datens&auml;tze werden geholt. ('.date('H:i:s').')';

$sql_query = "SELECT *
		FROM lehre.vw_stundenplandev
		WHERE
		datum>=".$db->db_add_param($datum_begin)."
		AND datum<=".$db->db_add_param($datum_ende)." ".$stgwhere."
		AND NOT EXISTS
			(SELECT stundenplan_id FROM lehre.tbl_stundenplan
			WHERE
			datum>=".$db->db_add_param($datum_begin)."
			AND datum<=".$db->db_add_param($datum_ende)."
			AND stundenplan_id=stundenplandev_id)
		ORDER BY datum, stunde;";

if (!$result = $db->db_query($sql_query))
{
	echo $sql_query.' fehlgeschlagen!'.$db->db_last_error()."\n";
	$message_sync .= $sql_query.' fehlgeschlagen!'.$db->db_last_error();
}
else
{
	echo 'Neue Datensaetze werden angelegt.'."\n";
	flush();

	while ($row = $db->db_fetch_object($result))
	{
		$sql_query='INSERT INTO lehre.tbl_stundenplan
				(stundenplan_id, unr, mitarbeiter_uid, datum, stunde,ort_kurzbz, studiengang_kz, semester, verband,gruppe,
			gruppe_kurzbz,titel,'.(LVPLAN_ANMERKUNG_ANZEIGEN?'anmerkung,':'').'fix,updateamum,updatevon,insertamum,insertvon,lehreinheit_id)
				VALUES'; //spalte anmerkung nur syncen, wenn im Config aktiv

		$sql_query .= "(".$db->db_add_param($row->stundenplandev_id).", ".
			$db->db_add_param($row->unr).", ".
			$db->db_add_param($row->uid).", ".
			$db->db_add_param($row->datum).", ".
			$db->db_add_param($row->stunde).", ".
			$db->db_add_param($row->ort_kurzbz).", ".
			$db->db_add_param($row->studiengang_kz).", ".
			$db->db_add_param($row->semester);

		if ($row->verband == null)
			$sql_query .= ', NULL';
		else
			$sql_query .= ", ".$db->db_add_param($row->verband);

		if ($row->gruppe == null)
			$sql_query .= ', NULL';
		else
			$sql_query .= ", ".$db->db_add_param($row->gruppe);

		if ($row->gruppe_kurzbz == null)
			$sql_query .= ', NULL';
		else
			$sql_query .= ", ".$db->db_add_param($row->gruppe_kurzbz);

		$sql_query .= ", ".$db->db_add_param($row->titel);

		if (LVPLAN_ANMERKUNG_ANZEIGEN) //spalte anmerkung nur syncen, wenn im Config aktiv
			$sql_query .= ", ".$db->db_add_param($row->anmerkung);

		if ($row->fix == 't')
			$sql_query .= ', TRUE';
		else
			$sql_query .= ', FALSE';

		$sql_query .= ", ".$db->db_add_param($row->updateamum).', '.
			$db->db_add_param($row->updatevon).', '.
			$db->db_add_param($row->insertamum).', '.
			$db->db_add_param($row->insertvon);

		if ($row->lehreinheit_id == null)
			$sql_query .= ', NULL';
		else
			$sql_query .= ', '.$db->db_add_param($row->lehreinheit_id);

		$sql_query .= ');';

		if (!$result_insert = $db->db_query($sql_query))
		{
			echo $sql_query.' fehlgeschlagen!'.$db->db_last_error()."\n";
			$message_sync .= $sql_query.' fehlgeschlagen!'.$db->db_last_error();
			$count_err++;
		}
		else
		{
			$count_ins++;

			if ($count_ins % 10 == 0)
			{
				echo '-';
				flush();
			}

			// Mails vorbereiten
			// Lektoren
			if (mb_substr($row->uid, 0, 1) != '_')
			{
				if (!isset($message[$row->uid]->isneu))
				{
					if(!isset($message[$row->uid]))
						$message[$row->uid] = new stdClass();

					$message[$row->uid]->isneu = true;
					$message[$row->uid]->mailadress = $row->uid.'@'.DOMAIN;
					$message[$row->uid]->message_begin = $message_begin.'<BR>';
					$message[$row->uid]->message = '
						<font style="color:green"><strong>'.$p_de->t('lvplan/neueStunden').' / '.$p_en->t('lvplan/neueStunden').'</strong></font>
						<BR>
						<TABLE>
							<TR>
								<TH>'.$p_de->t('lvplan/raum').'<br><span class="engl">'.$p_en->t('lvplan/raum').'</span></TH>
								<TH>'.$p_de->t('lvplan/lehrverband').'<br><span class="engl">'.$p_en->t('lvplan/lehrverband').'</span></TH>
								<TH>'.$p_de->t('lvplan/lektor').'<br><span class="engl">'.$p_en->t('lvplan/lektor').'</span></TH>
								<TH>'.$p_de->t('global/datum').'<br><span class="engl">'.$p_en->t('global/datum').'</span></TH>
								<TH>'.$p_de->t('lvplan/stdBeginn').'<br><span class="engl">'.$p_en->t('lvplan/stdBeginn').'</span></TH>
								<TH>'.$p_de->t('lvplan/lehrfach').'<br><span class="engl">'.$p_en->t('lvplan/lehrfach').'</span></TH>
								<TH>'.$p_de->t('lvplan/info').'<br><span class="engl">'.$p_en->t('lvplan/info').'</span></TH>
							</TR>';
				}

				$message[$row->uid]->message .= "\n";
				$message[$row->uid]->message .= '<TR><TD>'.$row->ort_kurzbz.'</TD>';
				$message[$row->uid]->message .= '<TD>'.
					mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz
				.'</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->lektor.'</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->datum.'</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->titel.'</TD></TR>';
			}

			// Verband
			if ($mailstudents)
			{
				$studenten = getStudentsFromGroup(
					$row->studiengang_kz,
					$row->semester,
					$row->verband,
					$row->gruppe,
					$row->gruppe_kurzbz,
					$ss->studiensemester_kurzbz
				);

				foreach ($studenten as $student)
				{
					if (!isset($message[$student]->isneu))
					{
						if (!isset($message[$student]))
							$message[$student] = new stdClass();

						$message[$student]->isneu = true;
						$message[$student]->mailadress = $student.'@'.DOMAIN;
						$message[$student]->message_begin = $message_begin.'<BR>';
						$message[$student]->message = '
							<font style="color:green">
								<strong>'.$p_de->t('lvplan/neueStunden').' / '.$p_en->t('lvplan/neueStunden').'</strong></font>
							<BR>
								<TABLE>
								<TR>
									<TH>'.$p_de->t('lvplan/raum').'<br><span class="engl">'.$p_en->t('lvplan/raum').'</span></TH>
									<TH>'.
										$p_de->t('lvplan/lehrverband').'<br><span class="engl">'.
										$p_en->t('lvplan/lehrverband').'</span>
									</TH>
									<TH>'.$p_de->t('lvplan/lektor').'<br><span class="engl">'.$p_en->t('lvplan/lektor').'</span></TH>
									<TH>'.
										$p_de->t('global/datum').'<br><span class="engl">'.$p_en->t('global/datum').'</span>
									</TH>
									<TH>'.
										$p_de->t('lvplan/stdBeginn').'<br><span class="engl">'.
										$p_en->t('lvplan/stdBeginn').'</span>
									</TH>
									<TH>'.
										$p_de->t('lvplan/lehrfach').'<br><span class="engl">'.$p_en->t('lvplan/lehrfach').'</span>
									</TH>
									<TH>'.$p_de->t('lvplan/info').'<br><span class="engl">'.$p_en->t('lvplan/info').'</span></TH>
								</TR>';
					}
					$message[$student]->message .= "\n";
					$message[$student]->message .= '<TR><TD>'.$row->ort_kurzbz.'</TD>';
					$message[$student]->message .= '<TD>'.
						mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.
					'</TD>';
					$message[$student]->message .= '<TD>'.$row->lektor.'</TD>';
					$message[$student]->message .= '<TD>'.$row->datum.'</TD>';
					$message[$student]->message .= '<TD>'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</TD>';
					$message[$student]->message .= '<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
					$message[$student]->message .= '<TD>'.$row->titel.'</TD></TR>';
				}
			}
		}
	}

	foreach($message as $msg)
		if ($msg->isneu)
			$msg->message .= '</table><br/>';
}

/**************************************************
* Datensaetze holen die alt sind
*/

echo 'Alte Datensaetze werden geholt.('.date('H:i:s').')'."\n";
flush();

$message_stpl .='<BR>Alte Datens&auml;tze werden geholt.('.date('H:i:s').')<BR>';

$sql_query = "SELECT *
		FROM lehre.vw_stundenplan
		WHERE datum>=".$db->db_add_param($datum_begin)."
		AND datum<=".$db->db_add_param($datum_ende)." ".$stgwhere."
		AND NOT EXISTS
		(SELECT stundenplandev_id FROM lehre.tbl_stundenplandev
		WHERE datum>=".$db->db_add_param($datum_begin)."
		AND datum<=".$db->db_add_param($datum_ende)."
		AND stundenplandev_id=stundenplan_id);";

if (!$result = $db->db_query($sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error()."\n";

	$message_sync.=$sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
}
else
{
	echo 'Alte Datensaetze werden geloescht.'."\n";
	flush();

	while ($row = $db->db_fetch_object($result))
	{
		$sql_query = 'DELETE FROM lehre.tbl_stundenplan WHERE stundenplan_id='.$db->db_add_param($row->stundenplan_id);

		if (!$result_delete = $db->db_query($sql_query))
		{
			echo $sql_query.' fehlgeschlagen!'.$db->db_last_error()."\n";

			$message_sync .= $sql_query.' fehlgeschlagen!'.$db->db_last_error();
			$count_err++;
		}
		else
		{
			$count_del++;

			if ($count_del % 10 == 0)
			{
				echo '-';
				flush();
			}

			// Mails vorbereiten
			// Lektoren
			if (mb_substr($row->uid,0,1) != '_')
			{
				if (!isset($message[$row->uid]->isalt))
				{
					if (!isset($message[$row->uid]))
						$message[$row->uid] = new stdClass();

					$message[$row->uid]->isalt = true;
					$message[$row->uid]->mailadress = $row->uid.'@'.DOMAIN;
					$message[$row->uid]->message_begin = $message_begin.'<BR>';

					if (!isset($message[$row->uid]->message))
						$message[$row->uid]->message = '';

					$message[$row->uid]->message .= '
						<font style="color:#FFA100">
							<strong>'.$p_de->t('lvplan/geloeschteStunden').' / '.$p_en->t('lvplan/geloeschteStunden').'</strong></font><BR>
						<TABLE>
						<TR>
							<TH>'.$p_de->t('lvplan/raum').'<br><span class="engl">'.$p_en->t('lvplan/raum').'</span></TH>
							<TH>'.$p_de->t('lvplan/lehrverband').'<br><span class="engl">'.$p_en->t('lvplan/lehrverband').'</span></TH>
							<TH>'.$p_de->t('lvplan/lektor').'<br><span class="engl">'.$p_en->t('lvplan/lektor').'</span></TH>
							<TH>'.$p_de->t('global/datum').'<br><span class="engl">'.$p_en->t('global/datum').'</span></TH>
							<TH>'.$p_de->t('lvplan/stdBeginn').'<br><span class="engl">'.$p_en->t('lvplan/stdBeginn').'</span></TH>
							<TH>'.$p_de->t('lvplan/lehrfach').'<br><span class="engl">'.$p_en->t('lvplan/lehrfach').'</span></TH>
							<TH>'.$p_de->t('lvplan/info').'<br><span class="engl">'.$p_en->t('lvplan/info').'</span></TH>
						</TR>';
				}
				$message[$row->uid]->message .= "\n";
				$message[$row->uid]->message .= '<TR><TD>'.$row->ort_kurzbz.'</TD>';
				$message[$row->uid]->message .= '<TD>'.
					strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->lektor.'</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->datum.'</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->titel.'</TD></TR>';
			}

			// Verband
			if ($mailstudents)
			{
				$studenten = getStudentsFromGroup(
					$row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $ss->studiensemester_kurzbz
				);

				foreach ($studenten as $student)
				{
					if (!isset($message[$student]->isalt))
					{
						if (!isset($message[$student]))
							$message[$student] = new stdClass();

						$message[$student]->isalt = true;
						$message[$student]->mailadress = $student.'@'.DOMAIN;
						$message[$student]->message_begin = $message_begin.'<BR>';

						if (!isset($message[$student]->message))
							$message[$student]->message = '';

						$message[$student]->message .=
							'<font style="color:#FFA100">
								<strong>'.$p_de->t('lvplan/geloeschteStunden').' / '.$p_en->t('lvplan/geloeschteStunden').
								'</strong></font><BR>
								<TABLE>
								<TR>
									<TH>'.$p_de->t('lvplan/raum').'<br><span class="engl">'.$p_en->t('lvplan/raum').'</span></TH>
									<TH>'
										.$p_de->t('lvplan/lehrverband').'<br><span class="engl">'.$p_en->t('lvplan/lehrverband').
										'</span>
									</TH>
									<TH>'.$p_de->t('lvplan/lektor').'<br><span class="engl">'.$p_en->t('lvplan/lektor').'</span></TH>
									<TH>'.$p_de->t('global/datum').'<br><span class="engl">'.$p_en->t('global/datum').'</span></TH>
									<TH>'.
										$p_de->t('lvplan/stdBeginn').'<br><span class="engl">'.$p_en->t('lvplan/stdBeginn').
										'</span>
									</TH>
									<TH>'.
										$p_de->t('lvplan/lehrfach').'<br><span class="engl">'.$p_en->t('lvplan/lehrfach').
										'</span>
									</TH>
									<TH>'.$p_de->t('lvplan/info').'<br><span class="engl">'.$p_en->t('lvplan/info').'</span></TH>
								</TR>';
					}

					$message[$student]->message .= "\n";
					$message[$student]->message .= '<TR><TD>'.$row->ort_kurzbz.'</TD>';
					$message[$student]->message .= '<TD>'.
						mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.
					'</TD>';
					$message[$student]->message .= '<TD>'.$row->lektor.'</TD>';
					$message[$student]->message .= '<TD>'.$row->datum.'</TD>';
					$message[$student]->message .= '<TD>'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</TD>';
					$message[$student]->message .= '<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
					$message[$student]->message .= '<TD>'.$row->titel.'</TD></TR>';
				}
			}
		}
	}

	foreach ($message as $msg)
		if (isset($msg->isalt))
			$msg->message .= '</TABLE><br/>';
}

/**************************************************
 * Datensaetze holen die anders sind
 */

echo 'Geaenderte Datensaetze werden geholt.('.date('H:i:s').')'."\n";
flush();

$message_stpl .= '<BR>Ge&auml;nderte Datens&auml;tze werden geholt.('.date('H:i:s').')<BR>';

$sql_query = "SELECT vw_stundenplandev.*, vw_stundenplan.datum AS old_datum, vw_stundenplan.stunde AS old_stunde,
				vw_stundenplan.ort_kurzbz AS old_ort_kurzbz, vw_stundenplan.lektor AS old_lektor,
				vw_stundenplan.uid AS old_uid, vw_stundenplan.titel AS old_titel,
				vw_stundenplan.anmerkung AS old_anmerkung
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
				vw_stundenplandev.fix!=vw_stundenplan.fix OR";

if (LVPLAN_ANMERKUNG_ANZEIGEN)
	$sql_query .= " coalesce(vw_stundenplandev.anmerkung,'')!=coalesce(vw_stundenplan.anmerkung,'') OR";

$sql_query .= " vw_stundenplandev.lehreinheit_id!=vw_stundenplan.lehreinheit_id )
				AND vw_stundenplandev.datum>=".$db->db_add_param($datum_begin)."
				AND vw_stundenplandev.datum<=".$db->db_add_param($datum_ende)." ".$stgwheredev.";";

if (!$result = $db->db_query($sql_query))
{
	echo $sql_query.' fehlgeschlagen!'.$db->db_last_error()."\n";

	$message_sync .= $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
}
else
{
	echo 'Datensaetze werden geaendert.'."\n";
	flush();

	while ($row = $db->db_fetch_object($result))
	{
		// Alten Eintrag aus tbl_stundenplan holen
		$sql_query = "SELECT * FROM lehre.tbl_stundenplandev WHERE stundenplandev_id=".$db->db_add_param($row->stundenplandev_id).";";

		if (!$result_old = $db->db_query($sql_query))
		{
			echo $sql_query.' fehlgeschlagen!'.$db->db_last_error()."\n";

			$message_sync .= $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
		}
		else
			$row_old = $db->db_fetch_object($result_old);

		// Datensaetze aendern
		$sql_query = "UPDATE lehre.tbl_stundenplan SET
			unr = ".$db->db_add_param($row->unr).
			", mitarbeiter_uid = ".$db->db_add_param($row->uid).
			", datum = ".$db->db_add_param($row->datum).
			", stunde = ".$db->db_add_param($row->stunde).
			", ort_kurzbz = ".$db->db_add_param($row->ort_kurzbz).
			", studiengang_kz = ".$db->db_add_param($row->studiengang_kz).
			", semester = ".$db->db_add_param($row->semester);
		
		if ($row->verband == null)
			$sql_query .= ', verband = NULL';
		else
			$sql_query .= ", verband = ".$db->db_add_param($row->verband);

		if ($row->gruppe == null)
			$sql_query .= ', gruppe = NULL';
		else
			$sql_query .= ", gruppe = ".$db->db_add_param($row->gruppe);

		if ($row->gruppe_kurzbz == null)
			$sql_query .= ', gruppe_kurzbz = NULL';
		else
			$sql_query .= ", gruppe_kurzbz = ".$db->db_add_param($row->gruppe_kurzbz);

		if (LVPLAN_ANMERKUNG_ANZEIGEN) //spalte anmerkung nur syncen, wenn im Config aktiv
			$sql_query .= ", anmerkung = ".$db->db_add_param($row->anmerkung);

		if ($row->titel == '')
			$sql_query .= ', titel = NULL';
		else
			$sql_query .= ", titel = ".$db->db_add_param($row->titel);

		if ($row->fix == 't')
			$sql_query .= ', fix = TRUE';
		else
			$sql_query.=', fix = FALSE';

		$sql_query .= ", updateamum = ".$db->db_add_param($row->updateamum).", updatevon = ".$db->db_add_param($row->updatevon);

		if ($row->lehreinheit_id == null)
			$sql_query .= ', lehreinheit_id = NULL';
		else
			$sql_query .= ", lehreinheit_id = ".$db->db_add_param($row->lehreinheit_id);

		$sql_query .= " WHERE stundenplan_id = ".$db->db_add_param($row->stundenplandev_id).";";

		if (!$result_update = $db->db_query($sql_query))
		{
			echo $sql_query.' fehlgeschlagen!'.$db->db_last_error()."\n";

			$message_sync .= $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
			$count_err++;
		}
		else
		{
			$count_upd++;

			if ($count_upd % 10 == 0)
			{
				echo '-';
				flush();
			}

			// Mails vorbereiten
			// Lektoren
			if (mb_substr($row->uid, 0, 1) != '_')
			{
				if (!isset($message[$row->uid]->isset))
				{
					if (!isset($message[$row->uid]))
						$message[$row->uid] = new stdClass();

					$message[$row->uid]->isset = true;
					$message[$row->uid]->mailadress = $row->uid.'@'.DOMAIN;
					$message[$row->uid]->message_begin = $message_begin.'<BR>';
					
					if (!isset($message[$row->uid]->message))
						$message[$row->uid]->message = '';

					$message[$row->uid]->message .= '
						<font style="color:blue">
							<strong>'.$p_de->t('lvplan/geaenderteStunden').' / '.$p_en->t('lvplan/geaenderteStunden').'</strong>
						</font>
						<BR>
						<TABLE>
						<TR>
							<TH>'.$p_de->t('lvplan/status').'<br><span class="engl">'.$p_en->t('lvplan/status').'</span></TH>
							<TH>'.$p_de->t('lvplan/raum').'<br><span class="engl">'.$p_en->t('lvplan/raum').'</span></TH>
							<TH>'.$p_de->t('lvplan/lehrverband').'<br><span class="engl">'.$p_en->t('lvplan/lehrverband').'</span></TH>
							<TH>'.$p_de->t('lvplan/lektor').'<br><span class="engl">'.$p_en->t('lvplan/lektor').'</span></TH>
							<TH>'.$p_de->t('global/datum').'<br><span class="engl">'.$p_en->t('global/datum').'</span></TH>
							<TH>'.$p_de->t('lvplan/stdBeginn').'<br><span class="engl">'.$p_en->t('lvplan/stdBeginn').'</span></TH>
							<TH>'.$p_de->t('lvplan/lehrfach').'<br><span class="engl">'.$p_en->t('lvplan/lehrfach').'</span></TH>
							<TH>'.$p_de->t('lvplan/info').'<br><span class="engl">'.$p_en->t('lvplan/info').'</span></TH>
						</TR>';
				}
				$message[$row->uid]->message .= "\n";
				$message[$row->uid]->message .= '<TR><TD>'.$p_de->t('lvplan/vorher').' / '.$p_en->t('lvplan/vorher').': </TD>';
				$message[$row->uid]->message .= '<TD>'.$row->old_ort_kurzbz.'</TD>';
				$message[$row->uid]->message .= '<TD>'.
					mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.
				'</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->old_lektor.'</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->old_datum.'</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->old_stunde.' ('.$beginnzeit_arr[$row->old_stunde].')</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
				$message[$row->uid]->message .= '<TD>'.$row->old_titel.'</TD></TR>';
				$message[$row->uid]->message .= "\n";
				$message[$row->uid]->message .= '<TR><TD>'.$p_de->t('lvplan/jetzt').' / '.$p_en->t('lvplan/jetzt').': </TD>';
				$myclass=($row->ort_kurzbz != $row->old_ort_kurzbz?'marked':'unmarked');
				$message[$row->uid]->message .= '<TD><span class="'.$myclass.'">'.$row->ort_kurzbz.'</span></TD>';
				$myclass='unmarked';
				$message[$row->uid]->message .= '<TD>
					<span class="'.$myclass.'">'.
						strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'
					</span>
				</TD>';
				$myclass = ($row->lektor != $row->old_lektor ? 'marked' : 'unmarked');
				$message[$row->uid]->message .= '<TD><span class="'.$myclass.'">'.$row->lektor.'</span></TD>';
				$myclass = (($row->datum != $row->old_datum) ? 'marked' : 'unmarked');
				$message[$row->uid]->message .= '<TD><span class="'.$myclass.'">'.$row->datum.'</span></TD>';
				$myclass = (($row->stunde != $row->old_stunde) ? 'marked' : 'unmarked');
				$message[$row->uid]->message .= '<TD><span class="'.$myclass.'">'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</span></TD>';
				$myclass = 'unmarked';
				$message[$row->uid]->message .= '<TD>
					<span class="'.$myclass.'">'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</span>
				</TD>';
				$myclass = ($row->titel != $row->old_titel ? 'marked' : 'unmarked');
				$message[$row->uid]->message .= '<TD><span class="'.$myclass.'">'.$row->titel.'</span></TD></TR><TR><TD>----------------</TD></TR>';
			}

			//wenn sich der Lektor geaendert hat dann auch den vorherigen lektor informieren
			//sofern es kein dummylektor ist
			if ($row->uid != $row->old_uid)
			{
				if (mb_substr($row->old_uid, 0, 1) != '_')
				{
					if (!isset($message[$row->old_uid]->isset))
					{
						if (!isset($message[$row->old_uid]))
							$message[$row->old_uid] = new stdClass();

						$message[$row->old_uid]->isset = true;
						$message[$row->old_uid]->mailadress = $row->old_uid.'@'.DOMAIN;
						$message[$row->old_uid]->message_begin = $message_begin.'<BR>';

						if (!isset($message[$row->old_uid]->message))
							$message[$row->old_uid]->message = '';

						$message[$row->old_uid]->message .= '
							<font style="color:blue">
								<strong>'.$p_de->t('lvplan/geaenderteStunden').' / '.$p_en->t('lvplan/geaenderteStunden').'</strong>
							</font>
							<BR>
							<TABLE>
							<TR>
								<TH>'.$p_de->t('lvplan/status').'<br><span class="engl">'.$p_en->t('lvplan/status').'</TH>
								<TH>'.$p_de->t('lvplan/raum').'<br><span class="engl">'.$p_en->t('lvplan/raum').'</span></TH>
								<TH>'.$p_de->t('lvplan/lehrverband').'<br><span class="engl">'.$p_en->t('lvplan/lehrverband').'</span></TH>
								<TH>'.$p_de->t('lvplan/lektor').'<br><span class="engl">'.$p_en->t('lvplan/lektor').'</span></TH>
								<TH>'.$p_de->t('global/datum').'<br><span class="engl">'.$p_en->t('global/datum').'</span></TH>
								<TH>'.$p_de->t('lvplan/stdBeginn').'<br><span class="engl">'.$p_en->t('lvplan/stdBeginn').'</span></TH>
								<TH>'.$p_de->t('lvplan/lehrfach').'<br><span class="engl">'.$p_en->t('lvplan/lehrfach').'</span></TH>
								<TH>'.$p_de->t('lvplan/info').'<br><span class="engl">'.$p_en->t('lvplan/info').'</span></TH>
							</TR>';
					}

					$message[$row->old_uid]->message .= "\n";
					$message[$row->old_uid]->message .= '<TR><TD>'.$p_de->t('lvplan/vorher').' / '.$p_en->t('lvplan/vorher').': </TD>';
					$message[$row->old_uid]->message .= '<TD>'.$row->old_ort_kurzbz.'</TD>';
					$message[$row->old_uid]->message .= '<TD>'.
						mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.
					'</TD>';
					$message[$row->old_uid]->message .= '<TD>'.$row->old_lektor.'</TD>';
					$message[$row->old_uid]->message .= '<TD>'.$row->old_datum.'</TD>';
					$message[$row->old_uid]->message .= '<TD>'.$row->old_stunde.' ('.$beginnzeit_arr[$row->old_stunde].')</TD>';
					$message[$row->old_uid]->message .= '<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
					$message[$row->old_uid]->message .= '<TD>'.$row->old_titel.'</TD></TR>';
					$message[$row->old_uid]->message .= "\n";
					$message[$row->old_uid]->message .= '<TR><TD>'.$p_de->t('lvplan/jetzt').' / '.$p_en->t('lvplan/jetzt').': </TD>';
					$myclass = ($row->ort_kurzbz!=$row->old_ort_kurzbz ? 'marked' : 'unmarked');
					$message[$row->old_uid]->message .= '<TD><span class="'.$myclass.'">'.$row->ort_kurzbz.'</span></TD>';
					$myclass = 'unmarked';
					$message[$row->old_uid]->message .= '<TD>
						<span class="'.$myclass.'">'.
							strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.
						'</span>
					</TD>';
					$myclass = ($row->lektor != $row->old_lektor ? 'marked' : 'unmarked');
					$message[$row->old_uid]->message .= '<TD><span class="'.$myclass.'">'.$row->lektor.'</span></TD>';
					$myclass = (($row->datum != $row->old_datum) ? 'marked' : 'unmarked');
					$message[$row->old_uid]->message .= '<TD><span class="'.$myclass.'">'.$row->datum.'</TD>';
					$myclass = (($row->stunde != $row->old_stunde) ? 'marked' : 'unmarked');
					$message[$row->old_uid]->message .= '<TD>
						<span class="'.$myclass.'">'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</span>
					</TD>';
					$myclass = 'unmarked';
					$message[$row->old_uid]->message .= '<TD>
						<span class="'.$myclass.'">'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</span>
					</TD>';
					$myclass = ($row->titel != $row->old_titel ? 'marked' : 'unmarked');
					$message[$row->old_uid]->message .= '<TD>
						<span class="'.$myclass.'">'.$row->titel.'</span></TD></TR><TR><TD>----------------
					</TD></TR>';
				}
			}

			// Verband
			if ($mailstudents)
			{
				$studenten = getStudentsFromGroup(
					$row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $ss->studiensemester_kurzbz
				);

				foreach ($studenten as $student)
				{
					if (!isset($message[$student]->isset))
					{
						if (!isset($message[$student]))
							$message[$student] = new stdClass();

						$message[$student]->isset = true;
						$message[$student]->mailadress = $student.'@'.DOMAIN;
						$message[$student]->message_begin = $message_begin.'<BR>';

						if (!isset($message[$student]->message))
							$message[$student]->message = '';

						$message[$student]->message .= '
							<font style="color:blue">
								<strong>'.$p_de->t('lvplan/geaenderteStunden').' / '.$p_en->t('lvplan/geaenderteStunden').'</strong>
							</font>
							<BR>
							<TABLE><TR>
								<TH>'.$p_de->t('lvplan/status').'<br><span class="engl">'.$p_en->t('lvplan/status').'</TH>
								<TH>'.$p_de->t('lvplan/raum').'<br><span class="engl">'.$p_en->t('lvplan/raum').'</span></TH>
								<TH>'.$p_de->t('lvplan/lehrverband').'<br><span class="engl">'.$p_en->t('lvplan/lehrverband').'</span></TH>
								<TH>'.$p_de->t('lvplan/lektor').'<br><span class="engl">'.$p_en->t('lvplan/lektor').'</span></TH>
								<TH>'.$p_de->t('global/datum').'<br><span class="engl">'.$p_en->t('global/datum').'</span></TH>
								<TH>'.$p_de->t('lvplan/stdBeginn').'<br><span class="engl">'.$p_en->t('lvplan/stdBeginn').'</span></TH>
								<TH>'.$p_de->t('lvplan/lehrfach').'<br><span class="engl">'.$p_en->t('lvplan/lehrfach').'</span></TH>
								<TH>'.$p_de->t('lvplan/info').'<br><span class="engl">'.$p_en->t('lvplan/info').'</span></TH>
							</TR>';
					}

					$message[$student]->message .= "\n";
					$message[$student]->message .= '<TR><TD>'.$p_de->t('lvplan/vorher').' / '.$p_en->t('lvplan/vorher').': </TD>';
					$message[$student]->message .= '<TD>'.$row->old_ort_kurzbz.'</TD>';
					$message[$student]->message .= '<TD>'.
						mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.
					'</TD>';
					$message[$student]->message .= '<TD>'.$row->old_lektor.'</TD>';
					$message[$student]->message .= '<TD>'.$row->old_datum.'</TD>';
					$message[$student]->message .= '<TD>'.$row->old_stunde.' ('.$beginnzeit_arr[$row->old_stunde].')</TD>';
					$message[$student]->message .= '<TD>'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</TD>';
					$message[$student]->message .= '<TD>'.$row->old_titel.'</TD></TR>';
					$message[$student]->message .= "\n";
					$message[$student]->message .= '<TR><TD>'.$p_de->t('lvplan/jetzt').' / '.$p_en->t('lvplan/jetzt').': </TD>';
					$myclass = ($row->ort_kurzbz != $row->old_ort_kurzbz ? 'marked' : 'unmarked');
					$message[$student]->message .= '<TD><span class="'.$myclass.'">'.$row->ort_kurzbz.'</span></TD>';
					$myclass = 'unmarked';
					$message[$student]->message .= '<TD>
						<span class="'.$myclass.'">'.
							mb_strtoupper($row->stg_typ.$row->stg_kurzbz).'-'.$row->semester.
							$row->verband.$row->gruppe.' '.$row->gruppe_kurzbz.'
						</span>
					</TD>';
					$myclass = ($row->lektor != $row->old_lektor ? 'marked' : 'unmarked');
					$message[$student]->message .= '<TD><span class="'.$myclass.'">'.$row->lektor.'</span></TD>';
					$myclass = (($row->datum != $row->old_datum) ? 'marked' : 'unmarked');
					$message[$student]->message .= '<TD><span class="'.$myclass.'">'.$row->datum.'</span></TD>';
					$myclass = (($row->stunde != $row->old_stunde) ? 'marked' : 'unmarked');
					$message[$student]->message .= '<TD><span class="'.$myclass.'">'.$row->stunde.' ('.$beginnzeit_arr[$row->stunde].')</span></TD>';
					$myclass = 'unmarked';
					$message[$student]->message .= '<TD>
							<span class="'.$myclass.'">'.$row->lehrfach.'-'.$row->lehrform.' ('.$row->lehrfach_bez.')</span>
					</TD>';
					$myclass = ($row->titel!=$row->old_titel ? 'marked' : 'unmarked');
					$message[$student]->message .= '<TD><span class="'.$myclass.'">'.$row->titel.'</span></TD></TR><TR><TD>----------------</TD></TR>';
				}
			}
		}
	}

	foreach ($message as $msg)
		if (isset($msg->isset))
			$msg->message .= '</table><br/>';
}

/**************************************************
 * Mails an Lektoren und Studenten schicken
 */

$message_help = '';

if ($sendmail)
{
	foreach ($message as $msg)
	{
		$mail = new mail($msg->mailadress, MAIL_LVPLAN, 'LV-Plan Update', 'Sie muessen diese Mail als HTML-Mail anzeigen um die LV-Plan Änderungen anzuzeigen');
		$mail->setHTMLContent($msg->message_begin.$msg->message);

		if ($mail->send())
		{
			echo 'Mail an '.$msg->mailadress.' wurde verschickt!'."\n";

			$message_stpl.='Mail an '.$msg->mailadress.' wurde verschickt!<BR>';

			if ($message_help != $msg->message)
			{
				$message_summary .= $msg->message;
				$message_summary .= '<br/><hr><br/>';
				$message_help = $msg->message;
			}
		}
		else
		{
			echo 'Mail an '.$msg->mailadress.' konnte nicht verschickt werden!'."\n";

			$message_sync .= 'Mail an '.$msg->mailadress.' konnte ***nicht*** verschickt werden!<BR>';
		}
	}
}

if (defined('LVPLAN_HORDE_SYNC') && LVPLAN_HORDE_SYNC === true)
{
	// Alle User bei denen sich der LVPlan veraendert hat
	// werden in ein File gesichert. Bei diesen Personen wird der LVPlan im Horde aktualisiert
	$users = array();

	foreach ($message as $uid => $msg)
	{
		$users[] = $uid;
	}

	$uidfile = DOC_ROOT.'../system/hordelvplansync/lvplanupdate.txt';

	// Letzte Durchlaufzeit des Scripts holen
	// anhand der Aenderungszeit des Textfiles mit den UIDs
	if (!$lastmod = filemtime($uidfile))
		$lastmod = time() - 86400; // Wenn die Zeit nicht ermittelt werden kann, werden die letzten 24 Std genommen

	// Zusaetzlich jene holen, bei denen sich die Reservierungen geaendert haben
	$qry = "SELECT * FROM campus.tbl_reservierung WHERE insertamum>'".date('Y-m-d H:i:s',$lastmod)."'";

	if ($result = $db->db_query($qry))
	{
		while ($row = $db->db_fetch_object($result))
		{
			$users[] = $row->uid;

			//Wenn fuer eine Gruppe reserviert wurde, dann die Personen aus der Gruppe holen
			if ($row->semester != '' || $row->verband != '' || $row->gruppe != '' || $row->gruppe_kurzbz != '')
			{
				$studenten = getStudentsFromGroup(
					$row->studiengang_kz, $row->semester, $row->verband, $row->gruppe, $row->gruppe_kurzbz, $ss->studiensemester_kurzbz
				);

				$users = array_merge($users, $studenten);
			}
		}
	}

	// geaenderte User in Textfile schreiben
	$users = array_unique($users);

	if (count($users) > 0)
	{
		if ($fp = fopen($uidfile, 'a'))
		{
			foreach($users as $uid)
			{
				fwrite($fp, $uid."\n");
			}

			fclose($fp);
		}
	}
}

// Mail an Admin
$message_tmp = $count_upd.' Datens&auml;tze wurden ge&auml;ndert.<BR>
			'.$count_ins.' Datens&auml;tze wurden hinzugef&uuml;gt.<BR>
			'.$count_del.' Datens&auml;tze wurden gel&ouml;scht.<BR>
			'.$count_err.' Fehler sind dabei aufgetreten!<BR><BR>';

echo $count_upd.' Datensaetze wurden geaendert.'."\n".
	$count_ins.' Datensaetze wurden hinzugefuegt.'."\n".
	$count_del.' Datensaetze wurden geloescht.'."\n".
	$count_err.' Fehler sind dabei aufgetreten!'."\n";

//Bricht den Code um, da es sonst zu Anzeigefehlern im Mail kommen kann
$message_stpl = wordwrap($message_stpl, 70);
$message_summary = wordwrap($message_summary, 70);

// Message sync
$message_sync = '<HTML><BODY>'.$message_tmp.$message_sync.$message_stpl.'<br/><br/><h3>Details</h3>'.$message_summary.'</BODY></HTML>';

$mail = new mail(MAIL_ADMIN, MAIL_LVPLAN, 'LV-Plan Update Zusammenfassung', 'Sie muessen diese Mail als HTML-Mail anzeigen um die LV-Plan Änderungen anzuzeigen');
$mail->setHTMLContent($message_sync);
if (!$mail->send())
	echo 'Error occurred while sending email to '.MAIL_ADMIN."\n";

// Message stpl
$message_stpl = '<HTML><BODY>'.$message_tmp.$message_stpl.'<br/><br/><h3>Details</h3>'.$message_summary.'</BODY></HTML>';

$mail = new mail(MAIL_LVPLAN, MAIL_LVPLAN, 'LV-Plan Update Zusammenfassung', 'Sie muessen diese Mail als HTML-Mail anzeigen um die LV-Plan Änderungen anzuzeigen');
$mail->setHTMLContent($message_stpl);
if (!$mail->send())
	echo 'Error occurred while sending email to '.MAIL_LVPLAN."\n";

?>

