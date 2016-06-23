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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
// ************************************
// * Script zur Pruefung und Korrektur
// * moeglicher Inkonsistenzen
// *
// * - Studenten ohne Prestudent_id werden korrigiert
// * - Inkonsistenzen der Tabellen tbl_studentlehrverband, tbl_student werden korrigiert
// **********************************
require_once(dirname(__FILE__).'/../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../include/person.class.php');
require_once(dirname(__FILE__).'/../include/benutzer.class.php');
require_once(dirname(__FILE__).'/../include/prestudent.class.php');
require_once(dirname(__FILE__).'/../include/lehrverband.class.php');
require_once(dirname(__FILE__).'/../include/mail.class.php');

$db = new basis_db();

$text='';
$statistik ='';
$abunterbrecher_verschoben_error=0;
$abunterbrecher_verschoben=0;


// *****
// * Gruppenzuteilung von Abbrechern und Unterbrechern korrigieren.
// * Abbrecher werden in die Gruppe 0A verschoben
// * Unterbrecher in die Gruppe 0B
// *****
$text.="\n\nKorrigiere Gruppenzuteilungen von Ab-/Unterbrechern\n";

//Alle Ab-/Unterbrecher holen die nicht im 0. Semester sind
$qry = "SELECT
			uid,
			tbl_prestudent.studiengang_kz,
			tbl_prestudent.prestudent_id,
			status_kurzbz,
			studiensemester_kurzbz
		FROM
			public.tbl_prestudent,
			public.tbl_prestudentstatus
		WHERE
			tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id AND
			(
				tbl_prestudentstatus.status_kurzbz='Unterbrecher' OR
				tbl_prestudentstatus.status_kurzbz='Abbrecher'
			)
			AND
			EXISTS (SELECT
						*
					FROM
						public.tbl_studentlehrverband
					WHERE
			        	tbl_studentlehrverband.prestudent_id=tbl_prestudent.prestudent_id AND
			        	studiensemester_kurzbz=tbl_prestudentstatus.studiensemester_kurzbz AND
			        	semester<>0
			        )
		";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		//Eintrag nur korrigieren wenn der Abbrecher/Unterbrecher Status der letzte in diesem Studiensemester ist
		$prestd = new prestudent();
		$prestd->getLastStatus($row->prestudent_id, $row->studiensemester_kurzbz);

		if($prestd->status_kurzbz=='Unterbrecher' || $prestd->status_kurzbz=='Abbrecher')
		{
			//Studentlehrverbandeintrag aktualisieren
			$lvb = new prestudent();
			if($lvb->studentlehrverband_exists($row->prestudent_id, $row->studiensemester_kurzbz))
				$lvb->new = false;
			else
			{
				$lvb->new = true;
				$lvb->insertamum = date('Y-m-d H:i:s');
				$lvb->insertvon = 'chkstudentlvb';
			}

			$lvb->uid = $row->uid;
			$lvb->studiensemester_kurzbz=$row->studiensemester_kurzbz;
			$lvb->studiengang_kz = $row->studiengang_kz;
			$lvb->semester = '0';
			$lvb->verband = ($prestd->status_kurzbz=='Unterbrecher'?'B':'A');
			$lvb->gruppe = ' ';
			$lvb->updateamum = date('Y-m-d H:i:s');
			$lvb->updatevon = 'chkstudentlvb';

			//Pruefen ob der Lehrverband exisitert, wenn nicht dann wird er angelegt
			$lehrverband = new lehrverband();
			if(!$lehrverband->exists($lvb->studiengang_kz, $lvb->semester, $lvb->verband, $lvb->gruppe))
			{
				$lehrverband->studiengang_kz = $lvb->studiengang_kz;
				$lehrverband->semester = $lvb->semester;
				$lehrverband->verband = $lvb->verband;
				$lehrverband->gruppe = $lvb->gruppe;
				$lehrverband->bezeichnung = ($lvb->verband=='A'?'Abbrecher':'Unterbrecher');

				$lehrverband->save(true);
			}

			if($lvb->save_studentlehrverband())
			{
				$text.="Student $lvb->uid wurde im $row->studiensemester_kurzbz in die Gruppe $lvb->semester$lvb->verband verschoben\n";
				$abunterbrecher_verschoben++;
			}
			else
			{
				$text.="Fehler biem Speichern des Lehrverbandeintrages bei $lvb->uid:".$lvb->errormsg."\n";
				$abunterbrecher_verschoben_error++;
			}
		}
	}
}

$statistik .= "$abunterbrecher_verschoben Studenten wurden ins 0. Semester verschoben\n ";
$statistik .= "$abunterbrecher_verschoben_error Fehler sind beim Verschieben aufgetreten\n ";
$statistik .= "\n\n";

$mail = new mail(MAIL_ADMIN, 'vilesci@'.DOMAIN, 'CHECK Studentlehrverband', $statistik.$text);
if($mail->send())
	echo 'Mail an '.MAIL_ADMIN.' wurde versandt';
else
	echo 'Fehler beim Versenden des Mails an '.MAIL_ADMIN;

echo nl2br("\n\n".$statistik.$text);

?>
