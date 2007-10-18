<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Erstellt ein Excel File mit einer Uebersicht der
 * Kosten fuer die Geschaeftsstelle und markiert die Zeilen die in den letzten
 * 31 Tagen veraendert wurden. Dieses File wirde dann per Mail versandt
 */
require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');

if (!$conn=pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

$stsem = new studiensemester($conn);
$semester_aktuell  = $stsem->getaktorNext();

$file = 'lehrauftragsliste.xls';

// Creating a workbook
echo 'Lehrauftragslisten werden erstellt. Bitte warten!<BR>';
flush();
$workbook = new Spreadsheet_Excel_Writer($file);

//Studiengaenge ermitteln bei denen sich die lektorzuordnung innerhalb der letzten 31 Tage geaendert haben
$qry_stg = "SELECT distinct studiengang_kz
			FROM (
				SELECT
					studiengang_kz
				FROM
					lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				WHERE
					(tbl_lehreinheitmitarbeiter.updateamum>now()- interval '31 days' OR
					tbl_lehreinheitmitarbeiter.insertamum>now()- interval '31 days') AND
					lehre.tbl_lehreinheit.studiensemester_kurzbz='$semester_aktuell' AND
					tbl_lehreinheitmitarbeiter.semesterstunden<>0 AND
					tbl_lehreinheitmitarbeiter.semesterstunden is not null AND
					tbl_lehreinheitmitarbeiter.stundensatz<>0 AND
					tbl_lehreinheitmitarbeiter.faktor<>0
				UNION
				SELECT
					studiengang_kz
				FROM
					lehre.tbl_projektbetreuer, lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
				WHERE
					(tbl_projektbetreuer.updateamum>now()-interval '31 days' OR
					tbl_projektbetreuer.insertamum>now()-interval '31 days') AND
					lehre.tbl_lehreinheit.studiensemester_kurzbz='$semester_aktuell' AND
					tbl_projektbetreuer.projektarbeit_id=tbl_projektarbeit.projektarbeit_id AND
					tbl_projektarbeit.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
					tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id
				) as foo
				";

if($result_stg = pg_query($conn, $qry_stg))
{
	while($row_stg = pg_fetch_object($result_stg))
	{
		//Studiengang laden
		$studiengang = new studiengang($conn, $row_stg->studiengang_kz);
		$studiengang_kz=$row_stg->studiengang_kz;

		// Creating a worksheet
		$worksheet =& $workbook->addWorksheet($studiengang->kuerzel);
		//echo "Writing $studiengang->kuerzel ...".microtime()."<br>";
		//Formate Definieren
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();

		$workbook->setCustomColor(10, 255, 186, 179);
		$format_colored =& $workbook->addFormat();
		$format_colored->setFgColor(10);

		$format_number_colored =& $workbook->addFormat();
		$format_number_colored->setNumFormat('0,0.00');
		$format_number_colored->setFgColor(10);

		$format_number =& $workbook->addFormat();
		$format_number->setNumFormat('0,0.00');

		$format_number_bold =& $workbook->addFormat();
		$format_number_bold->setNumFormat('0,0.00');
		$format_number_bold->setBold();

		$format_normal = & $workbook->addFormat();

		$i=0;
		$worksheet->write(0,0,'Erstellt am '.date('d.m.Y').' '.$semester_aktuell.' '.$studiengang->kuerzel, $format_bold);
		//Ueberschriften
		$worksheet->write(2,$i,"Studiengang", $format_bold);
		$worksheet->write(2,++$i,"Personalnr", $format_bold);
		$worksheet->write(2,++$i,"Titel", $format_bold);
		$worksheet->write(2,++$i,"Vorname", $format_bold);
		$worksheet->write(2,++$i,"Familienname", $format_bold);
		$worksheet->write(2,++$i,"Stunden", $format_bold);
		$worksheet->write(2,++$i,"Kosten", $format_bold);

		//Daten holen
		$qry = "SELECT
					tbl_lehreinheit.*, tbl_person.vorname, tbl_person.nachname, tbl_person.titelpre,
					tbl_mitarbeiter.personalnummer, tbl_person.person_id, tbl_mitarbeiter.mitarbeiter_uid,
					tbl_lehreinheitmitarbeiter.faktor as faktor, tbl_lehreinheitmitarbeiter.stundensatz as stundensatz,
					tbl_lehreinheitmitarbeiter.semesterstunden as semesterstunden,
					CASE WHEN COALESCE(tbl_lehreinheitmitarbeiter.updateamum, tbl_lehreinheitmitarbeiter.insertamum)>now()-interval '31 days' THEN 't' ELSE 'f' END as geaendert
				FROM
					lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_mitarbeiter,
					public.tbl_benutzer, public.tbl_person, lehre.tbl_lehrveranstaltung
				WHERE
					tbl_person.person_id = tbl_benutzer.person_id AND
					tbl_benutzer.uid=tbl_mitarbeiter.mitarbeiter_uid AND
					tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid AND
					tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
					tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
					studiengang_kz='$studiengang_kz' AND studiensemester_kurzbz='$semester_aktuell' AND
					tbl_lehreinheitmitarbeiter.semesterstunden<>0 AND tbl_lehreinheitmitarbeiter.semesterstunden is not null
					AND tbl_lehreinheitmitarbeiter.stundensatz<>0 AND tbl_lehreinheitmitarbeiter.faktor<>0
					ORDER BY nachname, vorname, tbl_mitarbeiter.mitarbeiter_uid";

		if($result = pg_query($conn, $qry))
		{
			$zeile=3;
			$gesamtkosten = 0;
			$liste=array();

			while($row=pg_fetch_object($result))
			{
				//Gesamtstunden und Kosten ermitteln
				if(array_key_exists($row->mitarbeiter_uid, $liste))
				{
					$liste[$row->mitarbeiter_uid]['gesamtstunden'] = $liste[$row->mitarbeiter_uid]['gesamtstunden'] + $row->semesterstunden;
					$liste[$row->mitarbeiter_uid]['gesamtkosten'] = $liste[$row->mitarbeiter_uid]['gesamtkosten'] + ($row->semesterstunden*$row->stundensatz*$row->faktor);
				}
				else
				{
					$liste[$row->mitarbeiter_uid]['gesamtstunden'] = $row->semesterstunden;
					$liste[$row->mitarbeiter_uid]['gesamtkosten'] = $row->semesterstunden*$row->stundensatz*$row->faktor;
				}
				$liste[$row->mitarbeiter_uid]['personalnummer'] = $row->personalnummer;
				$liste[$row->mitarbeiter_uid]['titelpre'] = $row->titelpre;
				$liste[$row->mitarbeiter_uid]['vorname'] = $row->vorname;
				$liste[$row->mitarbeiter_uid]['nachname'] = $row->nachname;
				if($row->geaendert=='t')
					$liste[$row->mitarbeiter_uid]['geaendert']=true;
			}

			//Betreuungen fuer Projektarbeiten
			foreach ($liste as $uid=>$arr)
			{
				$qry = "SELECT tbl_projektbetreuer.faktor, tbl_projektbetreuer.stunden, tbl_projektbetreuer.stundensatz, CASE WHEN COALESCE(tbl_projektbetreuer.updateamum, tbl_projektbetreuer.insertamum)>now()-interval '31 days' THEN 't' ELSE 'f' END as geaendert
			        FROM lehre.tbl_projektbetreuer, lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehrveranstaltung,
			               public.tbl_benutzer, lehre.tbl_projektarbeit, campus.vw_student
			        WHERE tbl_projektbetreuer.person_id=tbl_benutzer.person_id AND tbl_benutzer.uid='$uid' AND
			              tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND student_uid=vw_student.uid
			              AND tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND
			              tbl_lehreinheit.studiensemester_kurzbz='$semester_aktuell' AND tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
			              tbl_lehrveranstaltung.studiengang_kz='$studiengang_kz'";

				if($result = pg_query($conn, $qry))
				{
					while($row = pg_fetch_object($result))
					{
						$liste[$uid]['gesamtstunden'] = $liste[$uid]['gesamtstunden'] + $row->stunden;
						$liste[$uid]['gesamtkosten'] = $liste[$uid]['gesamtkosten'] + ($row->stunden*$row->stundensatz*$row->faktor);
						if($row->geaendert=='t')
						{
							$liste[$uid]['geaendert']=true;
						}
					}
				}
			}

			//Daten ausgeben
			foreach ($liste as $row)
			{
				$i=0;
				if(isset($row['geaendert']) && $row['geaendert']==true)
				{
					$format = $format_colored;
					$formatnb = $format_number_colored;
				}
				else
				{
					$format = $format_normal;
					$formatnb = $format_number;
				}
				//Studiengang
				$worksheet->write($zeile,$i,$studiengang->kuerzel, $format);
				//Personalnummer
				$worksheet->write($zeile,++$i,$row['personalnummer'], $format);
				//Titel
				$worksheet->write($zeile,++$i,$row['titelpre'], $format);
				//Vorname
				$worksheet->write($zeile,++$i,$row['vorname'], $format);
				//Nachname
				$worksheet->write($zeile,++$i,$row['nachname'], $format);
				//Stunden
				$worksheet->write($zeile,++$i,$row['gesamtstunden'], $format);
				//Kosten
				$worksheet->writeNumber($zeile,++$i,$row['gesamtkosten'], $formatnb);

				//Kosten zu den Gesamtkosten hinzurechnen
				$gesamtkosten = $gesamtkosten + $row['gesamtkosten'];
				$zeile++;
			}

			//Gesamtkosten anzeigen
			$worksheet->writeNumber($zeile,6,$gesamtkosten, $format_number_bold);
		}
	}

	$workbook->close();

	//Mail versenden mit Excel File im Anhang

	$from = "vilesci@technikum-wien.at";
    $subject = "Lehrauftragsliste ".date('d.m.Y');
    $message = "Dies ist eine automatische eMail!\n\nAnbei die Lehrauftragslisten vom ".date('d.m.Y');
    $message.= "\n\nJederzeit abrufbar unter ".APP_ROOT.'content/statistik/lehrauftragsliste_mail.xls.php';
    $fileatttype = "application/xls";
    $fileattname = "lehrauftragsliste_".date('Y_m_d').".xls";

    $headers = "From: $from";

    $fileobj = fopen($file, 'rb');
    $data = fread($fileobj, filesize($file));
    fclose($fileobj);

    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

    $headers .= "\nMIME-Version: 1.0\n" .
                "Content-Type: multipart/mixed;\n" .
                " boundary=\"{$mime_boundary}\"";

    $message = "This is a multi-part message in MIME format.\n\n" .
            "--{$mime_boundary}\n" .
            "Content-Type: text/plain; charset=\"iso-8859-1\"\n" .
            "Content-Transfer-Encoding: 7bit\n\n" .
            $message . "\n\n";

    $data = chunk_split(base64_encode($data));

    $message .= "--{$mime_boundary}\n" .
             "Content-Type: {$fileatttype};\n" .
             " name=\"{$fileattname}\"\n" .
             "Content-Disposition: attachment;\n" .
             " filename=\"{$fileattname}\"\n" .
             "Content-Transfer-Encoding: base64\n\n" .
             $data . "\n\n" .
             "--{$mime_boundary}--\n";

    if(mail(MAIL_GST.',vilesci@technikum-wien.at', $subject, $message, $headers ))
		echo 'Email mit Lehrauftragslisten wurde an '.MAIL_GST.' versandt!';
     else
        echo "Fehler beim Versenden der Lehrauftragsliste";
}

?>