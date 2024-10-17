<?php
/* Copyright (C) 2017 FHComplete.org
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
 * Authors: Andreas Östereicher	<oesi@technikum-wien.at>
 */
require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiensemester.class.php');

// Datenbank Verbindung
$db = new basis_db();


$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('admin'))
{
	exit('Sie haben keine Berechtigung');
}

echo '<html>
<head>
	<title>Studienordnung generieren</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css" />
</head>
<body>
';
if(isset($_POST["start"]) && $_POST["start"] == "start")
{
	$qry = "
	SELECT
		upper(typ || kurzbz) as kurzbz, kurzbzlang, studiengang_kz, bezeichnung, english,max_semester, orgform_kurzbz,
		(
			SELECT
				studiensemester_kurzbz
			FROM
				public.tbl_prestudentstatus
				JOIN public.tbl_prestudent USING(prestudent_id)
				JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
			WHERE
				studiengang_kz=tbl_studiengang.studiengang_kz
			ORDER BY
				tbl_studiensemester.start asc limit 1
		) as start_studiensemester,
		(
			SELECT
				studiensemester_kurzbz
			FROM
				public.tbl_prestudentstatus
				JOIN public.tbl_prestudent USING(prestudent_id)
				JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
			WHERE
				studiengang_kz=tbl_studiengang.studiengang_kz
				AND tbl_prestudentstatus.status_kurzbz='Student'
			ORDER BY
				tbl_studiensemester.start desc limit 1
		) as letztes_studenten_studiensemester,
		(
			SELECT
				gueltigvon
			FROM
				lehre.tbl_studienordnung
				JOIN public.tbl_studiensemester ON(tbl_studiensemester.studiensemester_kurzbz=tbl_studienordnung.gueltigvon)
			WHERE
				tbl_studienordnung.studiengang_kz = tbl_studiengang.studiengang_kz
			ORDER BY
				tbl_studiensemester.start asc limit 1
		) as start_studienordnung
	FROM
		public.tbl_studiengang
	ORDER BY typ, kurzbz
	";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($row->start_studienordnung == '')
			{
				echo $row->bezeichnung.' wird uebersprungen da keine Studienordnung vorhanden ist<br>';
				continue;
			}
			if($row->start_studiensemester == '')
			{
				echo $row->bezeichnung.' wird uebersprungen da keine Statuseintraege vorhanden sind<br>';
				continue;
			}
			if($row->start_studiensemester == $row->start_studienordnung)
			{
				echo $row->bezeichnung.' wird uebersprungen da bereits eine vollstaendige Studienordnung vorhanden ist<br>';
				continue;
			}
			$stsem = new studiensemester();
			$stsem_bis = $stsem->getPreviousFrom($row->start_studienordnung);

			$bezeichnung = sprintf("%04s",$row->studiengang_kz).'-'.$row->kurzbz.'-'.$row->start_studiensemester;

			// Studienordnung anlegen

			$qry_sto = "INSERT INTO lehre.tbl_studienordnung(studiengang_kz, version, gueltigvon, gueltigbis,
				bezeichnung, ects, studiengangbezeichnung, studiengangbezeichnung_englisch, studiengangkurzbzlang,
				insertamum, insertvon, status_kurzbz) VALUES(".
				$db->db_add_param($row->studiengang_kz).','.
				"'01',".
				$db->db_add_param($row->start_studiensemester).','.
				$db->db_add_param($stsem_bis).','.
				$db->db_add_param($bezeichnung).','.
				$db->db_add_param($row->max_semester*30).','.
				$db->db_add_param($row->bezeichnung).','.
				$db->db_add_param($row->english).','.
				$db->db_add_param($row->kurzbzlang).",now(),'autogenerate','approved');";

			if($db->db_query('BEGIN;'.$qry_sto))
			{
				$qry="SELECT currval('lehre.seq_studienordnung_studienordnung_id') as id;";
				if($db->db_query($qry))
				{
					if($rowseq = $db->db_fetch_object())
					{
						$studienordnung_id = $rowseq->id;
					}
				}

				// Studienplan anlegen
				$qry_stpl =	'INSERT INTO lehre.tbl_studienplan (studienordnung_id, orgform_kurzbz,version,
					bezeichnung, regelstudiendauer, sprache, aktiv, semesterwochen, testtool_sprachwahl,
					pflicht_sws, pflicht_lvs, ects_stpl, insertamum, insertvon) VALUES ('.
					$db->db_add_param($studienordnung_id, FHC_INTEGER).', '.
					$db->db_add_param($row->orgform_kurzbz).', '.
					"'V1', ".
					$db->db_add_param($bezeichnung.'-'.$row->orgform_kurzbz).', '.
					$db->db_add_param($row->max_semester, FHC_INTEGER).', '.
					"'German', true, 15, false, 0, 0,".
					$db->db_add_param($row->max_semester*30, FHC_INTEGER).', '.
					"now(),'autogenerate');";

				if($db->db_query($qry_stpl))
				{
					$qry="SELECT currval('lehre.seq_studienplan_studienplan_id') as id;";
					if($db->db_query($qry))
					{
						if($row_seq = $db->db_fetch_object())
						{
							$studienplan_id = $row_seq->id;
						}
					}

					// Gueltigkeiten setzen
					$qry = "SELECT
								*
							FROM
								public.tbl_studiensemester
							WHERE
								start>=(SELECT start FROM public.tbl_studiensemester
										WHERE studiensemester_kurzbz=".$db->db_add_param($row->start_studiensemester).")
								AND ende<=(SELECT ende FROM public.tbl_studiensemester
										WHERE studiensemester_kurzbz=".$db->db_add_param($stsem_bis).")";

					if($result_stsem = $db->db_query($qry))
					{
						while($row_stsem = $db->db_fetch_object($result_stsem))
						{
							if(mb_substr($row_stsem->studiensemester_kurzbz,0,2)=='WS')
								$sem=1;
							else
								$sem=2;

							while($sem<=$row->max_semester)
							{
								$qry_stplsem = "INSERT INTO lehre.tbl_studienplan_semester (
									studienplan_id, studiensemester_kurzbz, semester) VALUES (" .
									$db->db_add_param($studienplan_id) . ', ' .
									$db->db_add_param($row_stsem->studiensemester_kurzbz) . ', ' .
									$db->db_add_param($sem) . '); ';

								$db->db_query($qry_stplsem);
								$sem+=2;
							}
						}
					}
				}

				echo "Generiere ".$bezeichnung." für ".$row->bezeichnung.'<br>';
				$db->db_query('COMMIT;');
			}
		}
	}
}
else
{
	echo '
	<h1>Studienordnungen generieren</h1>
	Dieses Script generiert pro Studiengang eine Studienordnung wenn Statuseinträge vorhanden sind
	jedoch keine dazupassende Studienordnung. Es werden nur Studienordnungen angelegt VOR bereits bestehenden Studienordnungen.
	Es werden keine Lücken gefüllt. Wenn noch keine Studienordnung vorhanden ist, wird der Studiengang uebersprungen.<br>
	Es wird jeweils eine Studienordnung, ein Studienplan und die Gültigkeit gesetzt. Es werde leere Dummy Studienpläne erstellt
	die keine Lehrveranstaltungen zugeordnet haben.<br>
	Dieses Script sollte nur einmalig beim Update auf Version 3.2 gestartet werden und nur dann wenn die Studienpläne
	nicht bereits vollständig eingetragen sind.
	<form method="POST">
	<input type="hidden" name="start" value="start">
	<input type="submit" value="Starten">
	</form>';
}
echo '</body>
</html>';