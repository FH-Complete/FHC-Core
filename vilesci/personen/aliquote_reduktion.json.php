<?php
/* Copyright (C) 2016 FH Technikum-Wien
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
 * Authors: Andreas Moik <moik@technikum-wien.at>
 */

require_once(dirname(__FILE__)."/../../include/meta/php_utils.php");
require_once(dirname(__FILE__).'/../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../include/globals.inc.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/../../include/berechtigung.class.php');
require_once(dirname(__FILE__).'/../../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../include/prestudent.class.php');
require_once(dirname(__FILE__).'/../../include/studienplatz.class.php');
require_once(dirname(__FILE__).'/../../include/Excel/excel.php');
require_once(dirname(__FILE__).'/../../include/dokument.class.php');


$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!isset($_REQUEST["action"]))
	die("keine Aktion erhalten");
$action = $_REQUEST["action"];

if(!isset($_REQUEST["studiengang_kz"]))
	die("keine studiengang_kz erhalten");
$studiengang_kz = $_REQUEST["studiengang_kz"];


$studiengang = new studiengang($studiengang_kz);

if(!$rechte->isBerechtigt('student/stammdaten', $studiengang->oe_kurzbz, 'suid'))
	die('Sie haben keine Berechtigung');


switch($action)
{
	case "getStudenten":
		if(!isset($_REQUEST["studienplan_id"]))
			die("keine studienplan_id erhalten");
		$studienplan_id = $_REQUEST["studienplan_id"];

		if(!isset($_REQUEST["studiensemester_kurzbz"]))
			die("keine studiensemester_kurzbz erhalten");
		$studiensemester_kurzbz = $_REQUEST["studiensemester_kurzbz"];

		$prestudent = new prestudent();
		$return = $prestudent->getAllStudentenFromStudienplanAndStudsem($studienplan_id, $studiensemester_kurzbz, $studiengang_kz);

		$db = new basis_db();
		foreach($return as $key=>$value)
		{
			$qry = "SELECT
				*
				FROM
					public.tbl_dokumentprestudent
				WHERE
					prestudent_id=".$db->db_add_param($value->prestudent_id)."
					AND dokument_kurzbz=".$db->db_add_param('IvBo'.$value->studiengang_kz);

			if($result_dok = $db->db_query($qry))
			{
				if($db->db_num_rows($result_dok)>0)
					$return[$key]->interviewbogen=true;
				else
					$return[$key]->interviewbogen=false;
			}
		}
		returnAJAX(true,json_encode($return));
		break;


	case "getStudiengaenge":
		$studiengang = new studiengang();
		$studiengang->getAll();
		returnAJAX(true, json_encode($studiengang->result));
		break;


	case "getStudiensemester":
		$studiensemester = new studiensemester();
		$studiensemester->getAll();
		returnAJAX(true, json_encode($studiensemester->studiensemester));
		break;


	case "getStudienplaetze":
		if(!isset($_REQUEST["studiensemester_kurzbz"]))
			die("keine studiensemester_kurzbz erhalten");
		$studiensemester_kurzbz = $_REQUEST["studiensemester_kurzbz"];

		$studienplatz = new studienplatz();
		$studienplatz->load_studiengang_studiensemester($studiengang_kz, $studiensemester_kurzbz, true);
		returnAJAX(true, json_encode($studienplatz->result));
		break;

	case "setAufgenommene":
		if(!isset($_REQUEST["prestudent_ids"]))
			die("keine Studenten erhalten");

		$prestudent_ids = json_decode($_REQUEST["prestudent_ids"]);
		foreach($prestudent_ids as $i)
		{
			$prestudent = new prestudent($i);
			$prestudent->getLastStatus($i);
			$prestudent->status_kurzbz = "Aufgenommener";
			$prestudent->new = true;
			$prestudent->datum = date("Y-m-d H:i:s");
			$prestudent->updatevon = $user;
			$prestudent->updateamum = date("Y-m-d H:i:s");
			$prestudent->save_rolle();
		}
		returnAJAX(true, "");
		break;

	case "dlTable":

		if(!isset($_REQUEST["students"]))
			die("keine Studenten erhalten");
		$students = json_decode($_REQUEST["students"]);



		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();

		// sending HTTP headers
		$workbook->send('aliquote_reduktion_'.$studiengang_kz.'.xls');
		$workbook->setVersion(8);

		// Creating a worksheet
		$worksheet =& $workbook->addWorksheet("Tabelle");
		$worksheet->setInputEncoding('utf-8');

		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();

		$format_float =& $workbook->addFormat();
		$format_float->setNumFormat("0.0000");


		$spalte=0;
		$zeile=0;

		$worksheet->write($zeile,$spalte,'ID',$format_bold);
		$maxlength[$spalte]=3;
		$worksheet->write($zeile,++$spalte,'Nachname',$format_bold);
		$maxlength[$spalte]=7;
		$worksheet->write($zeile,++$spalte,'Vorname',$format_bold);
		$maxlength[$spalte]=7;
		$worksheet->write($zeile,++$spalte,'ZGV Gruppe',$format_bold);
		$maxlength[$spalte]=10;
		$worksheet->write($zeile,++$spalte,'Reihung',$format_bold);
		$maxlength[$spalte]=8;
		$worksheet->write($zeile,++$spalte,'RT Gesamt',$format_bold);
		$maxlength[$spalte]=8;
		$worksheet->write($zeile,++$spalte,'Interviewbogen',$format_bold);
		$maxlength[$spalte]=14;
		$worksheet->write($zeile,++$spalte,'Status',$format_bold);
		$maxlength[$spalte]=8;
		$worksheet->write($zeile,++$spalte,'Auswahl',$format_bold);
		$maxlength[$spalte]=8;


		usort($students, "studentsSort");
		foreach($students as $s)
		{
			$zeile++;
			$spalte=0;

			$worksheet->writeNumber($zeile,$spalte,$s->prestudent_id);
			if(mb_strlen($s->prestudent_id)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($s->prestudent_id);

			$worksheet->write($zeile,++$spalte,$s->nachname);
			if(mb_strlen($s->nachname)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($s->nachname);

			$worksheet->write($zeile,++$spalte, $s->vorname);
			if(mb_strlen($s->vorname)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($s->vorname);

			if(isset($s->bezeichnung) && $s->bezeichnung)
			{
				$worksheet->write($zeile,++$spalte, $s->bezeichnung);
				if(mb_strlen($s->bezeichnung)>$maxlength[$spalte])
					$maxlength[$spalte]=mb_strlen($s->bezeichnung);
			}
			else
			{
				$worksheet->write($zeile,++$spalte, "");
				if(mb_strlen("")>$maxlength[$spalte])
					$maxlength[$spalte]=mb_strlen("");
			}

			$worksheet->writeNumber($zeile,++$spalte, $s->seqPlace);
			if(mb_strlen($s->seqPlace)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($s->seqPlace);

			if(isset($s->rt_gesamtpunkte) && $s->rt_gesamtpunkte)
			{
				$worksheet->writeNumber($zeile,++$spalte, $s->rt_gesamtpunkte, $format_float);
				if(mb_strlen($s->rt_gesamtpunkte)>$maxlength[$spalte])
					$maxlength[$spalte]=mb_strlen($s->rt_gesamtpunkte);
			}
			else
			{
				$worksheet->write($zeile,++$spalte, "");
				if(mb_strlen("")>$maxlength[$spalte])
					$maxlength[$spalte]=mb_strlen("");
			}

			if(isset($s->interviewbogen))
			{
				$worksheet->write($zeile,++$spalte, ($s->interviewbogen?'vorhanden':'nicht vorhanden'));
				if(mb_strlen(($s->interviewbogen?'vorhanden':'nicht vorhanden'))>$maxlength[$spalte])
					$maxlength[$spalte]=mb_strlen(($s->interviewbogen?'vorhanden':'nicht vorhanden'));
			}
			else
			{
				$worksheet->write($zeile,++$spalte, "");
				if(mb_strlen("")>$maxlength[$spalte])
					$maxlength[$spalte]=mb_strlen("");
			}
			$worksheet->write($zeile,++$spalte, $s->laststatus);
			if(mb_strlen($s->laststatus)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($s->laststatus);

			if(isset($s->selected) && $s->selected)
			{
				$worksheet->write($zeile,++$spalte, "x");
				if(mb_strlen("x")>$maxlength[$spalte])
					$maxlength[$spalte]=mb_strlen("x");
			}
			else
			{
				$worksheet->write($zeile,++$spalte, "");
				if(mb_strlen("")>$maxlength[$spalte])
					$maxlength[$spalte]=mb_strlen("");
			}
		}

		//Die Breite der Spalten setzen
		foreach($maxlength as $i=>$breite)
			$worksheet->setColumn($i, $i, $breite+2);

		$workbook->close();
		break;

	default:
		returnAJAX(false,"unknown action: " . $action);
}


function studentsSort($a, $b)
{
	return $a->seqPlace > $b->seqPlace;
}


?>
