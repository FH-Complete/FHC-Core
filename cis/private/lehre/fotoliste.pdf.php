<?php

/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Cristina Hainberger <hainberg@technikum-wien.at>
 *
 * Description: This file creates a studentlist with students' profile fotos
 * by a given studiengangs- and lehrveranstaltungs ID (and eventually a given lehreinheit ID).
 * If fotos are locked by student, a dummy picture is inserted instead of the students foto.
 * EXCEPTION: if user has admins or assitents rights, ALL students' fotos are iserted (even locked ones)
 *
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/dokument_export.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/erhalter.class.php');
require_once('../../../include/datum.class.php');


$output = 'pdf';
$show_all_fotos = false;


//check user access & $_GET vars
if (!$db = new basis_db())
    die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();


if (isset($_GET['lvid']) && is_numeric($_GET['lvid']))
    $lvid = $_GET['lvid'];
else
    die('Eine gueltige LvID muss uebergeben werden');

isset($_GET['stsem']) ? $studiensemester = $_GET['stsem'] : die('Ein Studiensemester muss uebergeben werden');

$lv = new lehrveranstaltung();
$lv->load($lvid);

$stg = new studiengang();
$stg->load($lv->studiengang_kz);

$doc = new dokument_export('fotoliste', $stg->oe_kurzbz);

$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($user);

if (!$berechtigung->isBerechtigt('admin') && !$berechtigung->isBerechtigt('assistenz') && !$berechtigung->isBerechtigt('lehre', $lv->oe_kurzbz, 's') && !check_lektor_lehrveranstaltung($user, $lvid, $studiensemester))
    die('Sie muessen LektorIn der LV sein oder das Recht "ADMIN", "ASSISTENZ" oder "LEHRE" haben, um diese Seite aufrufen zu koennen');

if ($berechtigung->isBerechtigt('admin') || $berechtigung->isBerechtigt('assistenz'))
    $show_all_fotos = true;

if (isset($_GET['output']) && ($output = 'odt' || $output = 'doc'))
    $output = $_GET['output'];
isset($_GET['stg_kz']) ? $studiengang = $_GET['stg_kz'] : $studiengang = NULL;
isset($_GET['lehreinheit_id']) ? $lehreinheit = $_GET['lehreinheit_id'] : $lehreinheit = NULL;


//****************************   overall lehrveranstaltungs data   *******************************
//load overall lehrveranstaltungs-data
$qry = "SELECT DISTINCT ON
            (kuerzel, semester, verband, gruppe, gruppe_kurzbz)
            UPPER(stg_typ || stg_kurzbz) as kuerzel,
            lv_bezeichnung,
            stg_bez,
			semester,
			verband,
			gruppe,
			gruppe_kurzbz,
            stg_typ
		FROM
            campus.vw_lehreinheit
		WHERE
            lehrveranstaltung_id=" . $db->db_add_param($lvid, FHC_INTEGER) . "
		AND
            studiensemester_kurzbz=" . $db->db_add_param($studiensemester);
if ($lehreinheit != '')
    $qry .= " AND lehreinheit_id=" . $db->db_add_param($lehreinheit, FHC_INTEGER);

$gruppen_string = '';
$gruppen_string_arr = array();
$stg_typ = $stg->typ;
$stg_bezeichnung = $stg->bezeichnung;
$lv_bezeichnung = '';

//structure overall lehrveranstaltungs data
if ($result = $db->db_query($qry)) {
    while ($row = $db->db_fetch_object($result)) {
        //lehrveranstaltung
        $lv_bezeichnung = $row->lv_bezeichnung;

        //collect all gruppenkürzel
        if ($row->gruppe_kurzbz == '')
            $gruppen_string = trim($row->kuerzel . '-' . $row->semester . $row->verband . $row->gruppe);
        else
            $gruppen_string = $row->gruppe_kurzbz;

        $gruppen_string_arr[] = $gruppen_string;
    }
}

//concatinate distinct gruppenkürzel
$studiengruppe = implode(", ", array_unique($gruppen_string_arr));

//get studiengangstyp-bezeichnung
$qry = "SELECT
            bezeichnung
        FROM
            public.tbl_studiengangstyp
        WHERE
            typ =" . $db->db_add_param($stg_typ);

if ($result = $db->db_query($qry)) {
    $row = $db->db_fetch_object($result);
    $stg_typ_bezeichnung = $row->bezeichnung;
}


//add overall lehrveranstaltungs-data for XML
$data = array(
    'lehrveranstaltung' => $lv_bezeichnung,
    'studiengang' => $stg_bezeichnung,
    'studiengangs_typ' => $stg_typ_bezeichnung,
    'studiensemester' => $studiensemester,
    'studiengruppe' => $studiengruppe
);



//****************************   students data   *******************************
//load students-data
$qry = 'SELECT DISTINCT ON
			(nachname, vorname, person_id)
            vorname,
            nachname,
            wahlname,
            matrikelnr,
			tbl_studentlehrverband.semester,
            tbl_studentlehrverband.verband,
            tbl_studentlehrverband.gruppe,
			(SELECT
                status_kurzbz
            FROM
                public.tbl_prestudentstatus
            WHERE
                prestudent_id=tbl_student.prestudent_id
            ORDER BY
                datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status,
            tbl_studiengang.kurzbz,
            tbl_studiengang.typ,
            tbl_bisio.bisio_id,
            tbl_bisio.von,
            tbl_bisio.bis,
            tbl_student.studiengang_kz AS stg_kz_student,
            tbl_zeugnisnote.note,
            tbl_mitarbeiter.mitarbeiter_uid,
            tbl_person.person_id,
            tbl_person.matr_nr,
            tbl_person.geschlecht,
            tbl_person.foto,
            tbl_person.foto_sperre
		FROM
			campus.vw_student_lehrveranstaltung
            JOIN public.tbl_benutzer USING(uid)
			JOIN public.tbl_person USING(person_id)
            LEFT JOIN public.tbl_student ON(uid=student_uid)
            LEFT JOIN public.tbl_studiengang ON(tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz)
			LEFT JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
			LEFT JOIN public.tbl_studentlehrverband USING(student_uid,studiensemester_kurzbz)
			LEFT JOIN lehre.tbl_zeugnisnote ON(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id
                AND tbl_zeugnisnote.student_uid=tbl_student.student_uid
                AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
            LEFT JOIN bis.tbl_bisio ON(uid=tbl_bisio.student_uid)
		WHERE
			vw_student_lehrveranstaltung.lehrveranstaltung_id=' . $db->db_add_param($lvid, FHC_INTEGER) . ' AND
			vw_student_lehrveranstaltung.studiensemester_kurzbz=' . $db->db_add_param($studiensemester);

if ($lehreinheit != '')
    $qry .= ' AND vw_student_lehrveranstaltung.lehreinheit_id=' . $db->db_add_param($lehreinheit, FHC_INTEGER);

$qry .= ' ORDER BY nachname, vorname, person_id, tbl_bisio.bis DESC';

$stsem_obj = new studiensemester();
$stsem_obj->load($studiensemester);
$stsemdatumvon = $stsem_obj->start;
$stsemdatumbis = $stsem_obj->ende;

$erhalter = new erhalter();
$erhalter->getAll();

$a_o_kz = '9' . sprintf("%03s", $erhalter->result[0]->erhalter_kz); //Stg_Kz AO-Studierende auslesen (9005 fuer FHTW)
$anzahl_studierende = 0;
$datum = new datum();
$zusatz = '';
$foto_url_arr = array();

//structure students data
if ($result = $db->db_query($qry)) {
    while ($row = $db->db_fetch_object($result)) {
        if ($row->status != 'Abbrecher' && $row->status != 'Unterbrecher') {
            $anzahl_studierende++;

            if ($row->status == 'Incoming') //Incoming
                $zusatz = '(i)';
            else
                $zusatz = '';

            if ($row->bisio_id != '' && $row->status != 'Incoming' && ($row->bis > $stsemdatumvon || $row->bis == '') && $row->von < $stsemdatumbis
                && (anzahlTage($row->von, $row->bis) >= 30)) //Outgoing
                $zusatz .= '(o)(ab '. $datum->formatDatum($row->von, 'd.m.Y'). ')';

            if ($row->note == 6) //angerechnet
                $zusatz .= '(ar)';

            if ($row->mitarbeiter_uid != '') //mitarbeiter
                $zusatz .= '(ma)';

            if ($row->stg_kz_student == $a_o_kz) //Außerordentliche Studierende
                $zusatz .= '(a.o.)';

            //wenn Wahlname vorhanden, wird dieser anstelle des Vornamens angezeigt
            if ($row->wahlname != '')
            {
              $vorname = $row->wahlname;
            }
            else
            {
              $vorname = $row->vorname;
            }

            //allow admin and assistenz to see ALL fotos (even if locked by user)
            if ($show_all_fotos)
                $row->foto_sperre = 'f';

            //create foto (if not locked by student OR if fotolist is created by admin or assistenz)
            $foto_url = '';

			if ($row->foto_sperre == 'f' && $row->foto != '') {
                $foto_src = $row->foto;
                $foto_url = sys_get_temp_dir() . '/foto' . trim($row->person_id) . '.jpg';
                $foto_url_arr[] = $foto_url;

                //create writeable file
                if (!$foto = fopen($foto_url, 'w'))
                    die("Das Bild konnte nicht erstellt werden");
                //add foto base64-code
                if (!fwrite($foto, base64_decode($foto_src)))
                {
                    die("Das Bild konnte nicht erstellt werden");
                }

                //add foto to document
                $doc->addImage($foto_url, trim($row->person_id) . '.jpg', 'image/jpg');
            }
            elseif ($row->foto_sperre == 't')
            {
                $foto_url = 'gesperrt';
            }

            //create studiengruppe
            $student_studiengruppe = strtoupper($row->typ.$row->kurzbz.'-'.$row->semester);

            //add studierenden data for XML
            $data[] = array('studierende' => array(
                    'vorname' => $vorname,
                    'nachname' => mb_strtoupper($row->nachname, 'UTF-8'),
                    'personenkennzeichen' => trim($row->matrikelnr),
                    'geschlecht' => $row->geschlecht,
                    'foto_gesperrt' => $row->foto_sperre, // f/t
                    'foto_url' => $foto_url,
                    'studiengruppe' => $student_studiengruppe,
                    'verband' => trim($row->verband),
                    'gruppe' => trim($row->gruppe),
                    'zusatz' => $zusatz
            ));
        }
    }
    //Anzahl Studierende in Array $data (an erster Stelle) einfuegen
    $data = array_reverse($data, true);
    $data['anzahl_studierende'] = $anzahl_studierende;
    $data = array_reverse($data, true);
}

//add data to fotoliste.xsl
$doc->addDataArray($data, 'fotoliste');

//set doc name
$doc->setFilename('Fotoliste_'.$stg_bezeichnung.'_'.$studiensemester.'_'.$lv_bezeichnung);

//create doc in format required
if (!$doc->create($output))
    die($doc->errormsg);

//download doc
$doc->output();

//unlink doc from tmp-folder
$doc->close();

//unlink fotos from tmp-folder
foreach ($foto_url_arr as $foto_url)
    unlink($foto_url);
