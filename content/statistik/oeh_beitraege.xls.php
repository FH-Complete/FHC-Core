<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Christian Paminger		<christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher	<andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl			<rudolf.hangl@technikum-wien.at> and
 *          Gerald Simane-Sequens	<gerald.simane-sequens@technikum-wien.at>
 */
/**
 * Exportiert eine Liste der OEH-Beitragszahler in ein Excel File.
 * Das betreffende Studiensemester wird uebergeben.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/Excel/excel.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');


$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$erhalter='';
$heute=date("d.m.Y");

$db = new basis_db();
$datum_obj = new datum();
//Parameter holen
$studiensemester_kurzbz = isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'';

if($studiensemester_kurzbz!='')
{

	//Erhalter einlesen
	$qry="SELECT * FROM public.tbl_erhalter";
	if($db->db_query($qry))
	{
		if ($row = $db->db_fetch_object())
		{
			$erhalter=sprintf("%03s",$row->erhalter_kz);
		}
		else
		{
			die('Kein Erhalter gefunden!');
		}
	}
	else
	{
		die('Der Erhalter konnte nicht geladen werden!');
	}
	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->setVersion(8);

	// sending HTTP headers
	$workbook->send("OEH-Beitrag". "_".$studiensemester_kurzbz." erstellt am ".$heute.".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("OEH-Beitragszahler");
	$worksheet->setInputEncoding('utf-8');

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();
	$format_bold->setAlign('center');

	$format_date =& $workbook->addFormat();
	$format_date->setNumFormat('DD.MM.YYYY');

	$format_right =& $workbook->addFormat();
	$format_right->setAlign('right');

	$stg_arr=array();
	$studiengang = new studiengang();
	$studiengang->getAll('typ, kurzbzlang', false);
	foreach ($studiengang->result as $row)
		$stg_arr[$row->studiengang_kz] = $row->kuerzel;

	$spalte=0;
	$zeile=0;

	$worksheet->write($zeile,$spalte,'Personenkennzahl',$format_bold);
	$maxlength[$spalte]=16;
	$worksheet->write($zeile,++$spalte,'Erhalter',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet->write($zeile,++$spalte,'StgKz',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet->write($zeile,++$spalte,'Geschlecht',$format_bold);
	$maxlength[$spalte]=10;
	$worksheet->write($zeile,++$spalte,'Vorname',$format_bold);
	$maxlength[$spalte]=7;
	$worksheet->write($zeile,++$spalte,'Nachname',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet->write($zeile,++$spalte,'Geburtsdatum',$format_bold);
	$maxlength[$spalte]=12;
	$worksheet->write($zeile,++$spalte,'Nation',$format_bold);
	$maxlength[$spalte]=6;
	$worksheet->write($zeile,++$spalte,'Titelpre',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet->write($zeile,++$spalte,'Email',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet->write($zeile,++$spalte,'Telefon',$format_bold);
	$maxlength[$spalte]=7;
	$worksheet->write($zeile,++$spalte,'s_nation',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet->write($zeile,++$spalte,'s_plz',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet->write($zeile,++$spalte,'s_ort',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet->write($zeile,++$spalte,'s_strasse',$format_bold);
	$maxlength[$spalte]=9;
	$worksheet->write($zeile,++$spalte,'w_nation',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet->write($zeile,++$spalte,'w_plz',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet->write($zeile,++$spalte,'w_ort',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet->write($zeile,++$spalte,'w_strasse',$format_bold);
	$maxlength[$spalte]=9;
	$worksheet->write($zeile,++$spalte,'Titelpost',$format_bold);
	$maxlength[$spalte]=9;
	$worksheet->write($zeile,++$spalte,'Semester',$format_bold);
	$maxlength[$spalte]=16;
	$worksheet->write($zeile,++$spalte,'Status',$format_bold);
	$maxlength[$spalte]=20;

	// Daten holen - Alle Personen mit akt. Status Student, Diplomand oder Praktikant plus Incoming
	$qry="SELECT DISTINCT ON (matrikelnr) matrikelnr AS personenkennzahl, tbl_student.studiengang_kz, geschlecht, vorname, nachname, gebdatum AS geburtsdatum,
		geburtsnation AS nation, titelpre, uid || '@".DOMAIN."' AS email,
		(SELECT kontakt FROM public.tbl_kontakt WHERE person_id=public.tbl_person.person_id and (kontakttyp='mobil' OR kontakttyp='telefon') LIMIT 1) AS telefon,
		(SELECT nation FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_nation,
		(SELECT plz FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_plz,
		(SELECT ort FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_ort,
		(SELECT strasse FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_strasse,
		(SELECT nation FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_nation,
		(SELECT plz FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_plz,
		(SELECT ort FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_ort,
		(SELECT strasse FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_strasse,
		titelpost, get_rolle_prestudent(tbl_prestudent.prestudent_id, ".$db->db_add_param($studiensemester_kurzbz).") as status,
		(SELECT ausbildungssemester FROM public.tbl_prestudentstatus WHERE prestudent_id=public.tbl_prestudent.prestudent_id AND tbl_prestudentstatus.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' ORDER BY datum desc LIMIT 1) AS semester
		FROM 
			public.tbl_person
			JOIN public.tbl_benutzer using(person_id)
			JOIN public.tbl_student on(uid=student_uid)
			JOIN public.tbl_prestudent using(prestudent_id)
			JOIN public.tbl_prestudentstatus on(tbl_prestudentstatus.prestudent_id=tbl_student.prestudent_id)
		WHERE 
			tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
			AND get_rolle_prestudent(tbl_prestudent.prestudent_id, ".$db->db_add_param($studiensemester_kurzbz).") in('Student','Diplomand','Praktikant','Incoming')
			AND tbl_student.studiengang_kz<10000 
			AND tbl_prestudent.bismelden=true";
	// AND tbl_benutzer.aktiv=true

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$zeile++;
			$spalte=0;

			$worksheet->write($zeile,$spalte,$row->personenkennzahl,$format_right);
			if(mb_strlen($row->personenkennzahl)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->personenkennzahl);

			$worksheet->write($zeile,++$spalte,'="'.$erhalter.'"',$format_right);
			$worksheet->write($zeile,++$spalte,$row->studiengang_kz);
			if(mb_strlen($row->studiengang_kz)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->studiengang_kz);

			$worksheet->write($zeile,++$spalte,$row->geschlecht);
			if(mb_strlen($row->geschlecht)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->geschlecht);

			$worksheet->write($zeile,++$spalte,$row->vorname);
			if(mb_strlen($row->vorname)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->vorname);

			$worksheet->write($zeile,++$spalte,$row->nachname);
			if(mb_strlen($row->nachname)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->nachname);

			$worksheet->write($zeile,++$spalte, $datum_obj->formatDatum($row->geburtsdatum,"d.m.Y"));
			if(mb_strlen($row->geburtsdatum)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->geburtsdatum);

			$worksheet->write($zeile,++$spalte, $row->nation);
			if(mb_strlen($row->nation)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->nation);

			$worksheet->write($zeile,++$spalte,$row->titelpre);
			if(mb_strlen($row->titelpre)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->titelpre);

			$worksheet->write($zeile,++$spalte,$row->email);
			if(mb_strlen($row->email)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->email);

			$worksheet->write($zeile,++$spalte,$row->telefon);
			if(mb_strlen($row->telefon)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->telefon);

			$worksheet->write($zeile,++$spalte,$row->s_nation);
			if(mb_strlen($row->s_nation)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_nation);

			$worksheet->write($zeile,++$spalte,$row->s_plz);
			if(mb_strlen($row->s_plz)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_plz);

			$worksheet->write($zeile,++$spalte,$row->s_ort);
			if(mb_strlen($row->s_ort)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_ort);

			$worksheet->write($zeile,++$spalte, $row->s_strasse);
			if(mb_strlen($row->s_strasse)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_strasse);

			$worksheet->write($zeile,++$spalte,$row->w_nation);
			if(mb_strlen($row->w_nation)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_nation);

			$worksheet->write($zeile,++$spalte,$row->w_plz);
			if(mb_strlen($row->w_plz)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_plz);

			$worksheet->write($zeile,++$spalte, $row->w_ort);
			if(mb_strlen($row->w_ort)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_ort);

			$worksheet->write($zeile,++$spalte, $row->w_strasse);
			if(mb_strlen($row->w_strasse)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_strasse);

			$worksheet->write($zeile,++$spalte, $row->titelpost);
			if(mb_strlen($row->titelpost)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->titelpost);

			$worksheet->write($zeile,++$spalte, $row->semester);
			if(mb_strlen($row->semester)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->semester);

			$worksheet->write($zeile,++$spalte, $row->status);
			if(mb_strlen($row->status)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->status);
		}
	}

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet->setColumn($i, $i, $breite+2);


	// Creating worksheet "bezahlt"
	$worksheet2 =& $workbook->addWorksheet("bezahlt");
	$worksheet2->setInputEncoding('utf-8');
	$spalte=0;
	$zeile=0;

	$worksheet2->write($zeile,$spalte,'Personenkennzahl',$format_bold);
	$maxlength[$spalte]=16;
	$worksheet2->write($zeile,++$spalte,'Erhalter',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet2->write($zeile,++$spalte,'StgKz',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet2->write($zeile,++$spalte,'Geschlecht',$format_bold);
	$maxlength[$spalte]=10;
	$worksheet2->write($zeile,++$spalte,'Vorname',$format_bold);
	$maxlength[$spalte]=7;
	$worksheet2->write($zeile,++$spalte,'Nachname',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet2->write($zeile,++$spalte,'Geburtsdatum',$format_bold);
	$maxlength[$spalte]=12;
	$worksheet2->write($zeile,++$spalte,'Nation',$format_bold);
	$maxlength[$spalte]=6;
	$worksheet2->write($zeile,++$spalte,'Titelpre',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet2->write($zeile,++$spalte,'Email',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet2->write($zeile,++$spalte,'Telefon',$format_bold);
	$maxlength[$spalte]=7;
	$worksheet2->write($zeile,++$spalte,'s_nation',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet2->write($zeile,++$spalte,'s_plz',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet2->write($zeile,++$spalte,'s_ort',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet2->write($zeile,++$spalte,'s_strasse',$format_bold);
	$maxlength[$spalte]=9;
	$worksheet2->write($zeile,++$spalte,'w_nation',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet2->write($zeile,++$spalte,'w_plz',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet2->write($zeile,++$spalte,'w_ort',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet2->write($zeile,++$spalte,'w_strasse',$format_bold);
	$maxlength[$spalte]=9;
	$worksheet2->write($zeile,++$spalte,'Titelpost',$format_bold);
	$maxlength[$spalte]=9;
	$worksheet2->write($zeile,++$spalte,'Semester',$format_bold);
	$maxlength[$spalte]=16;
	$worksheet2->write($zeile,++$spalte,'Status',$format_bold);
	$maxlength[$spalte]=20;

	// Daten holen - Alle Personen mit akt. Status Student, Diplomand oder Praktikant, die bezahlt haben
	$qry="SELECT DISTINCT ON (matrikelnr) matrikelnr AS personenkennzahl, tbl_student.studiengang_kz, geschlecht, vorname, nachname, gebdatum AS geburtsdatum,
	geburtsnation AS nation, titelpre, uid || '@".DOMAIN."' AS email,
	(SELECT kontakt FROM public.tbl_kontakt WHERE person_id=public.tbl_person.person_id and (kontakttyp='mobil' OR kontakttyp='telefon') LIMIT 1) AS telefon,
	(SELECT nation FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_nation,
	(SELECT plz FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_plz,
	(SELECT ort FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_ort,
	(SELECT strasse FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_strasse,
	(SELECT nation FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_nation,
	(SELECT plz FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_plz,
	(SELECT ort FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_ort,
	(SELECT strasse FROM public.tbl_adresse WHERE person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_strasse,
	titelpost, get_rolle_prestudent(tbl_prestudent.prestudent_id, ".$db->db_add_param($studiensemester_kurzbz).") as status,
	(SELECT ausbildungssemester FROM public.tbl_prestudentstatus WHERE prestudent_id=public.tbl_prestudent.prestudent_id AND tbl_prestudentstatus.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' ORDER BY datum desc LIMIT 1) AS semester
	FROM 
		public.tbl_person
		JOIN public.tbl_konto as ka using(person_id)
		JOIN public.tbl_konto as kb using(person_id)
		JOIN public.tbl_benutzer using(person_id)
		JOIN public.tbl_student on(uid=student_uid)
		JOIN public.tbl_prestudent using(prestudent_id)
		JOIN public.tbl_prestudentstatus on(tbl_prestudentstatus.prestudent_id=tbl_student.prestudent_id)
	WHERE 
		tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
		AND get_rolle_prestudent(tbl_prestudent.prestudent_id, ".$db->db_add_param($studiensemester_kurzbz).") in('Student','Diplomand','Praktikant','Incoming','Absolvent','Abbrecher')
		AND tbl_student.studiengang_kz<10000 
		AND ka.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND ka.buchungstyp_kurzbz in('OEH','Lehrgangsgebuehr') AND tbl_student.studiengang_kz=ka.studiengang_kz
		AND kb.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND kb.buchungstyp_kurzbz in('OEH','Lehrgangsgebuehr') AND tbl_student.studiengang_kz=kb.studiengang_kz
		AND kb.buchungsnr_verweis=ka.buchungsnr";
	//AND tbl_benutzer.aktiv=true

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$zeile++;
			$spalte=0;

			$worksheet2->write($zeile,$spalte,$row->personenkennzahl,$format_right);
			if(mb_strlen($row->personenkennzahl)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->personenkennzahl);

			$worksheet2->write($zeile,++$spalte,'="'.$erhalter.'"',$format_right);

			$worksheet2->write($zeile,++$spalte,$row->studiengang_kz);
			if(mb_strlen($row->studiengang_kz)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->studiengang_kz);

			$worksheet2->write($zeile,++$spalte,$row->geschlecht);
			if(mb_strlen($row->geschlecht)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->geschlecht);

			$worksheet2->write($zeile,++$spalte,$row->vorname);
			if(mb_strlen($row->vorname)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->vorname);

			$worksheet2->write($zeile,++$spalte,$row->nachname);
			if(mb_strlen($row->nachname)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->nachname);

			$worksheet2->write($zeile,++$spalte, $datum_obj->formatDatum($row->geburtsdatum,"d.m.Y"));
			if(mb_strlen($row->geburtsdatum)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->geburtsdatum);

			$worksheet2->write($zeile,++$spalte, $row->nation);
			if(mb_strlen($row->nation)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->nation);

			$worksheet2->write($zeile,++$spalte,$row->titelpre);
			if(mb_strlen($row->titelpre)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->titelpre);

			$worksheet2->write($zeile,++$spalte,$row->email);
			if(mb_strlen($row->email)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->email);

			$worksheet2->write($zeile,++$spalte,$row->telefon);
			if(mb_strlen($row->telefon)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->telefon);

			$worksheet2->write($zeile,++$spalte,$row->s_nation);
			if(mb_strlen($row->s_nation)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_nation);

			$worksheet2->write($zeile,++$spalte,$row->s_plz);
			if(mb_strlen($row->s_plz)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_plz);

			$worksheet2->write($zeile,++$spalte,$row->s_ort);
			if(mb_strlen($row->s_ort)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_ort);

			$worksheet2->write($zeile,++$spalte, $row->s_strasse);
			if(mb_strlen($row->s_strasse)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_strasse);

			$worksheet2->write($zeile,++$spalte,$row->w_nation);
			if(mb_strlen($row->w_nation)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_nation);

			$worksheet2->write($zeile,++$spalte,$row->w_plz);
			if(mb_strlen($row->w_plz)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_plz);

			$worksheet2->write($zeile,++$spalte, $row->w_ort);
			if(mb_strlen($row->w_ort)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_ort);

			$worksheet2->write($zeile,++$spalte, $row->w_strasse);
			if(mb_strlen($row->w_strasse)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_strasse);

			$worksheet2->write($zeile,++$spalte, $row->titelpost);
			if(mb_strlen($row->titelpost)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->titelpost);

			$worksheet2->write($zeile,++$spalte, $row->semester);
			if(mb_strlen($row->semester)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->semester);

			$worksheet2->write($zeile,++$spalte, $row->status);
			if(mb_strlen($row->status)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->status);
		}
	}

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet2->setColumn($i, $i, $breite+2);

	/*
	// Creating worksheet "noch nicht bezahlt"
	$worksheet3 =& $workbook->addWorksheet("nicht bezahlt");

	$spalte=0;
	$zeile=0;

	//$worksheet->write($zeile,$spalte,'OEH-Beitragszahler'.$studiensemester_kurzbz.' erstellt am '.date("d.m.Y"), $format_bold);

	//$spalte=0;
	//$zeile++;

	$worksheet3->write($zeile,$spalte,'Personenkennzahl',$format_bold);
	$maxlength[$spalte]=16;
	$worksheet3->write($zeile,++$spalte,'Erhalter',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet3->write($zeile,++$spalte,'StgKz',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet3->write($zeile,++$spalte,'Geschlecht',$format_bold);
	$maxlength[$spalte]=10;
	$worksheet3->write($zeile,++$spalte,'Vorname',$format_bold);
	$maxlength[$spalte]=7;
	$worksheet3->write($zeile,++$spalte,'Nachname',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet3->write($zeile,++$spalte,'Geburtsdatum',$format_bold);
	$maxlength[$spalte]=12;
	$worksheet3->write($zeile,++$spalte,'Nation',$format_bold);
	$maxlength[$spalte]=6;
	$worksheet3->write($zeile,++$spalte,'Titelpre',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet3->write($zeile,++$spalte,'Email',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet3->write($zeile,++$spalte,'Telefon',$format_bold);
	$maxlength[$spalte]=7;
	$worksheet3->write($zeile,++$spalte,'s_nation',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet3->write($zeile,++$spalte,'s_plz',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet3->write($zeile,++$spalte,'s_ort',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet3->write($zeile,++$spalte,'s_strasse',$format_bold);
	$maxlength[$spalte]=9;
	$worksheet3->write($zeile,++$spalte,'w_nation',$format_bold);
	$maxlength[$spalte]=8;
	$worksheet3->write($zeile,++$spalte,'w_plz',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet3->write($zeile,++$spalte,'w_ort',$format_bold);
	$maxlength[$spalte]=5;
	$worksheet3->write($zeile,++$spalte,'w_strasse',$format_bold);
	$maxlength[$spalte]=9;
	$worksheet3->write($zeile,++$spalte,'Titelpost',$format_bold);
	$maxlength[$spalte]=9;


	// Daten holen
	$qry="SELECT DISTINCT ON (matrikelnr) matrikelnr AS personenkennzahl, '005' as erhalter, tbl_student.studiengang_kz, geschlecht, vorname, nachname, gebdatum AS geburtsdatum,
	geburtsnation AS nation, titelpre, uid || '@technikum-wien.at' AS email,
	(SELECT kontakt FROM public.tbl_kontakt WHERE tbl_kontakt.person_id=public.tbl_person.person_id and (kontakttyp='mobil' OR kontakttyp='telefon') LIMIT 1) AS telefon,
	(SELECT nation FROM public.tbl_adresse WHERE tbl_adresse.person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_nation,
	(SELECT plz FROM public.tbl_adresse WHERE tbl_adresse.person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_plz,
	(SELECT ort FROM public.tbl_adresse WHERE tbl_adresse.person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_ort,
	(SELECT strasse FROM public.tbl_adresse WHERE tbl_adresse.person_id=public.tbl_person.person_id ORDER BY heimatadresse ASC  LIMIT 1) AS s_strasse,
	(SELECT nation FROM public.tbl_adresse WHERE tbl_adresse.person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_nation,
	(SELECT plz FROM public.tbl_adresse WHERE tbl_adresse.person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_plz,
	(SELECT ort FROM public.tbl_adresse WHERE tbl_adresse.person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_ort,
	(SELECT strasse FROM public.tbl_adresse WHERE tbl_adresse.person_id=public.tbl_person.person_id ORDER BY heimatadresse desc LIMIT 1) AS w_strasse,
	titelpost
	FROM tbl_person
	JOIN tbl_benutzer on(tbl_person.person_id=tbl_benutzer.person_id)
	JOIN tbl_student on(uid=student_uid)
	JOIN tbl_prestudentstatus on(tbl_prestudentstatus.prestudent_id=tbl_student.prestudent_id)
	LEFT JOIN public.tbl_konto on(tbl_person.person_id=tbl_konto.person_id)
	WHERE tbl_prestudentstatus.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'
	AND (status_kurzbz='Student' OR status_kurzbz='Diplomand' OR status_kurzbz='Praktikant')
	AND tbl_student.studiengang_kz<999
	AND tbl_person.person_id NOT IN (SELECT person_id FROM tbl_konto WHERE tbl_konto.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'
	AND tbl_konto.buchungstyp_kurzbz='OEH' AND tbl_student.studiengang_kz=tbl_konto.studiengang_kz AND tbl_konto.buchungsnr_verweis IS NOT NULL
	AND person_id=tbl_person.person_id)";

	if($result = $db->db_query($conn, $qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$zeile++;
			$spalte=0;

			$worksheet3->write($zeile,$spalte,$row->personenkennzahl,$format_right);
			if(mb_strlen($row->personenkennzahl)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->personenkennzahl);

			$worksheet3->write($zeile,++$spalte,'="'.$row->erhalter.'"',$format_right);

			$worksheet3->write($zeile,++$spalte,$row->studiengang_kz);
			if(mb_strlen($row->studiengang_kz)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->studiengang_kz);

			$worksheet3->write($zeile,++$spalte,$row->geschlecht);
			if(mb_strlen($row->geschlecht)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->geschlecht);

			$worksheet3->write($zeile,++$spalte,$row->vorname);
			if(mb_strlen($row->vorname)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->vorname);

			$worksheet3->write($zeile,++$spalte,$row->nachname);
			if(mb_strlen($row->nachname)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->nachname);

			$worksheet3->write($zeile,++$spalte, $datum_obj->formatDatum($row->geburtsdatum,"d.m.Y"));
			if(mb_strlen($row->geburtsdatum)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->geburtsdatum);

			$worksheet3->write($zeile,++$spalte, $row->nation);
			if(mb_strlen($row->nation)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->nation);

			$worksheet3->write($zeile,++$spalte,$row->titelpre);
			if(mb_strlen($row->titelpre)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->titelpre);

			$worksheet3->write($zeile,++$spalte,$row->email);
			if(mb_strlen($row->email)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->email);

			$worksheet3->write($zeile,++$spalte,$row->telefon);
			if(mb_strlen($row->telefon)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->telefon);

			$worksheet3->write($zeile,++$spalte,$row->s_nation);
			if(mb_strlen($row->s_nation)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_nation);

			$worksheet3->write($zeile,++$spalte,$row->s_plz);
			if(mb_strlen($row->s_plz)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_plz);

			$worksheet3->write($zeile,++$spalte,$row->s_ort);
			if(mb_strlen($row->s_ort)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_ort);

			$worksheet3->write($zeile,++$spalte, $row->s_strasse);
			if(mb_strlen($row->s_strasse)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->s_strasse);

			$worksheet3->write($zeile,++$spalte,$row->w_nation);
			if(mb_strlen($row->w_nation)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_nation);

			$worksheet3->write($zeile,++$spalte,$row->w_plz);
			if(mb_strlen($row->w_plz)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_plz);

			$worksheet3->write($zeile,++$spalte, $row->w_ort);
			if(mb_strlen($row->w_ort)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_ort);

			$worksheet3->write($zeile,++$spalte, $row->w_strasse);
			if(mb_strlen($row->w_strasse)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->w_strasse);

			$worksheet3->write($zeile,++$spalte, $row->titelpost);
			if(mb_strlen($row->titelpost)>$maxlength[$spalte])
				$maxlength[$spalte]=mb_strlen($row->titelpost);
		}
	}

	//Die Breite der Spalten setzen
	foreach($maxlength as $i=>$breite)
		$worksheet3->setColumn($i, $i, $breite+2);*/

	$workbook->close();
}
else
{
	echo '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>OEH-Beitragszahler</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body class="Background_main">
	<h2>OEH-Beitragszahler</h2>
	';

	echo '<form method="GET" action="'.$_SERVER['PHP_SELF'].'">';
	echo 'Studiensemester: <SELECT name="studiensemester_kurzbz">';

	$stsem = new studiensemester();
	$stsem_akt = $stsem->getaktorNext();
	$stsem->getAll();

	foreach ($stsem->studiensemester as $row)
	{
		if($row->studiensemester_kurzbz==$stsem_akt)
			$selected='selected';
		else
			$selected='';

		echo "<OPTION value='$row->studiensemester_kurzbz' $selected>$row->studiensemester_kurzbz</OPTION>";
	}
	echo "</SELECT>";
	echo " <input type='submit' value='Erstellen'>";
	echo "</form></body></html>";
}
?>
