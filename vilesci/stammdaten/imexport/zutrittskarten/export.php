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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
		require_once('../../../../config/vilesci.config.inc.php');
		require_once('../../../../include/basis_db.class.php');
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
//include('../../../include/functions.inc.php');
require_once('../../../../include/Excel/PEAR.php');
require_once('../../../../include/Excel/BIFFwriter.php');
require_once('../../../../include/Excel/Workbook.php');
require_once('../../../../include/Excel/Format.php');
require_once('../../../../include/Excel/Worksheet.php');
require_once('../../../../include/Excel/Parser.php');
require_once('../../../../include/Excel/OLE.php');
require_once('../../../../include/Excel/PPS.php');
require_once('../../../../include/Excel/Root.php');
require_once('../../../../include/Excel/File.php');
require_once('../../../../include/Excel/Writer.php');
require_once('../../../../include/Excel/Validator.php');

// letzte Nummer
$sql_query="SELECT max(key) AS last_keynr FROM sync.tbl_zutrittskarte;";
//echo $sql_query;
if(!$result=$db->db_query($sql_query))
	die($db->db_last_error().'<BR>'.$sql_query);
if ($row=$db->db_fetch_object($result))
	$key_nummer=$row->last_keynr+1;
else
	die ('Letzte Nummer konnte nicht ermittelt werden!');

// Neue Zutrittskarten
/*$sql_query="SELECT svnr,vorname,nachname,nummerintern,nummer,
				max(tbl_benutzer.uid) AS uid, max(matrikelnr) AS matrikelnr, max(kurzbzlang) AS stg_kurzbzlang,
				upper(max(typ) || max(kurzbz)) AS stg_kurzbz,
				EXTRACT(DAY FROM vw_betriebsmittelperson.insertamum) AS tag,
				EXTRACT(MONTH FROM vw_betriebsmittelperson.insertamum) AS monat,
				EXTRACT(YEAR FROM vw_betriebsmittelperson.insertamum) AS jahr
			FROM public.vw_betriebsmittelperson
				LEFT OUTER JOIN (public.tbl_benutzer JOIN public.tbl_student ON (uid=student_uid)
					JOIN public.tbl_studiengang USING (studiengang_kz))
				USING (person_id)
			WHERE betriebsmitteltyp='Zutrittskarte' AND nummer NOT IN (SELECT physaswnumber FROM sync.tbl_zutrittskarte)
			GROUP BY svnr,vorname,nachname,nummerintern,nummer, vw_betriebsmittelperson.insertamum;";
*/

$sql_query="SELECT svnr,vorname,nachname,nummerintern,nummer, uid, matrikelnr, kurzbzlang AS stg_kurzbzlang,
				upper(typ)||upper(kurzbz) AS stg_kurzbz,
				EXTRACT(DAY FROM vw_betriebsmittelperson.insertamum) AS tag,
				EXTRACT(MONTH FROM vw_betriebsmittelperson.insertamum) AS monat,
				EXTRACT(YEAR FROM vw_betriebsmittelperson.insertamum) AS jahr
			FROM public.vw_betriebsmittelperson
				 LEFT OUTER JOIN (public.tbl_student JOIN public.tbl_studiengang USING (studiengang_kz)) ON (uid=student_uid)
			WHERE betriebsmitteltyp='Zutrittskarte' AND benutzer_aktiv AND retouram IS NULL
				AND nummer NOT IN (SELECT physaswnumber FROM sync.tbl_zutrittskarte);";
//echo $sql_query;
if(!$result_neu=$db->db_query($sql_query))
	die($db->db_last_error().'<BR>'.$sql_query);

// Updates von Zutrittskarten
$sql_query="SELECT svnr,vorname,nachname,nummerintern,nummer,firstname,name,key, uid, matrikelnr,
				kurzbzlang AS stg_kurzbzlang, upper(typ)||upper(kurzbz) AS stg_kurzbz, text1,pin,
				EXTRACT(DAY FROM vw_betriebsmittelperson.insertamum) AS tag,
				EXTRACT(MONTH FROM vw_betriebsmittelperson.insertamum) AS monat,
				EXTRACT(YEAR FROM vw_betriebsmittelperson.insertamum) AS jahr
			FROM public.vw_betriebsmittelperson
				 LEFT OUTER JOIN (public.tbl_student JOIN public.tbl_studiengang USING (studiengang_kz)) ON (uid=student_uid)
				 JOIN sync.tbl_zutrittskarte ON (physaswnumber=nummer)
			WHERE benutzer_aktiv AND retouram IS NULL
				AND (trim(vw_betriebsmittelperson.nachname)!=trim(tbl_zutrittskarte.name)
					OR trim(vw_betriebsmittelperson.vorname)!=trim(tbl_zutrittskarte.firstname)
					OR trim(vw_betriebsmittelperson.uid)!=trim(tbl_zutrittskarte.text1));";
//echo $sql_query;
if(!$result_upd=$db->db_query($sql_query))
	die($db->db_last_error().'<BR>'.$sql_query);

// Loeschen von Zutrittskarten
$sql_query="SELECT *
			FROM sync.tbl_zutrittskarte
			WHERE physaswnumber NOT IN
				(SELECT nummer
					FROM public.vw_betriebsmittelperson
					WHERE betriebsmitteltyp='Zutrittskarte' AND retouram IS NULL
				);";	// AND benutzer_aktiv
//echo $sql_query;
if(!$result_del=$db->db_query($sql_query))
	die($db->db_last_error().'<BR>'.$sql_query);



//------------ Excel init --------------------------

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();
// sending HTTP headers
$workbook->send("CerpassZutrittskartenUpdate". "_" . date("d_m_Y") . ".xls");
// Creating a worksheet
$worksheet =& $workbook->addWorksheet("CerpassZutrittskartenUpdate");

//$format_bold =& $workbook->addFormat();
//$format_bold->setBold();
//$format_title =& $workbook->addFormat();
//$format_title->setBold();
//	$format_title->setColor('yellow');
//	$format_title->setPattern(1);
//	$format_title->setFgColor('blue');
// let's merge
//$format_title->setAlign('merge');

$worksheet->write(0,0,"(Command)"); 	// a:Add - u:Update - d:Delete
$worksheet->write(0,1,"(Key)");			// Gleich wie LogAswNumber
$worksheet->write(0,2,"(Name)");
$worksheet->write(0,3,"(FirstName)");
$worksheet->write(0,4,"(Group)");				// Muss auf Anlage vorhanden sein Studenten: StudiengangskennzahlLang Mitarbeiter: Verwaltung/General
$worksheet->write(0,5,"(LogAswNumber)");		// Betriebsmittel: LogNummer
$worksheet->write(0,6,"(PhysAswNumber)");		// Betriebsmittel: Nummer
$worksheet->write(0,7,"(ValidStart)");			// aktuell
$worksheet->write(0,8,"(ValidEnd)");			// +5 Jahre
$worksheet->write(0,9,"(UID)");					//Text1:
$worksheet->write(0,10,"(Matrikelnummer)"); 	//Text2: Wichtig bei Studenten
$worksheet->write(0,11,"(Text3)");	//Text3: Nicht verwenden
$worksheet->write(0,12,"(Text4)");			//Text4: Nicht verwenden
$worksheet->write(0,13,"(Text5)");				//Text5:
$worksheet->write(0,14,"(Text6)");				//Text6:
$worksheet->write(0,15,"(PIN)");		// Nicht verwenden
$worksheet->write(0,16,"(CardState)");	// Bei Neuen immer auf 0

// set width of columns
$worksheet->setColumn(0,0,2); // erste Spalte auf width=2
$worksheet->setColumn(1,1,5); // zweite Spalten auf width=5
//$worksheet->setColumn(0,0,22);

$z=1; // Start bei Zeile 1

// Neue Zutrittskarten
while ($row=$db->db_fetch_object($result_neu))
{
	$command='a';
	$gruppe=$row->stg_kurzbz;
	if ($gruppe=='')
		$gruppe='Verwaltung';
	$worksheet->write($z,0, $command);
	$worksheet->write($z,1, $key_nummer);				//$row->nummerintern);
	$worksheet->write($z,2, $row->nachname);
	$worksheet->write($z,3, $row->vorname);
	$worksheet->write($z,4, $gruppe);
	$worksheet->write($z,5, $key_nummer++);				//$row->nummerintern);
	$worksheet->write($z,6, $row->nummer);
	$worksheet->write($z,7, $row->tag.'.'.$row->monat.'.'.$row->jahr);
	$worksheet->write($z,8, $row->tag.'.'.$row->monat.'.'.($row->jahr+5));
	$worksheet->write($z,9, $row->uid);
	$worksheet->write($z,10,$row->matrikelnr);
	$worksheet->write($z,11,'');
	$worksheet->write($z,12,'');
	$worksheet->write($z,13,'');
	$worksheet->write($z,14,'');
	$worksheet->write($z,15,'');
	$worksheet->write($z,16,'0');
	$z++;
}

// Updates von Zutrittskarten
while ($row=$db->db_fetch_object($result_upd))
{
	$command='u';
	$gruppe=$row->stg_kurzbz;
	if ($gruppe=='')
		$gruppe='Verwaltung';
	$worksheet->write($z,0, $command);
	$worksheet->write($z,1, $row->key);
	$worksheet->write($z,2, $row->nachname);
	$worksheet->write($z,3, $row->vorname);
	$worksheet->write($z,4, $gruppe);
	$worksheet->write($z,5, $row->key);
	$worksheet->write($z,6, $row->nummer);
	$worksheet->write($z,7, $row->tag.'.'.$row->monat.'.'.$row->jahr);
	$worksheet->write($z,8, $row->tag.'.'.$row->monat.'.'.($row->jahr+5));
	$worksheet->write($z,9, $row->uid);
	$worksheet->write($z,10,$row->matrikelnr);
	$worksheet->write($z,11,'');
	$worksheet->write($z,12,$row->text1);
	$worksheet->write($z,13,$row->name);
	$worksheet->write($z,14,$row->firstname);
	$worksheet->write($z,15,$row->pin);
	$worksheet->write($z,16,'0');
	$z++;
}

// Loeschen von Zutrittskarten
while ($row=$db->db_fetch_object($result_del))
{
	$command='d';
	$worksheet->write($z,0, $command);
	$worksheet->write($z,1, $row->key);
	$worksheet->write($z,2, $row->name);
	$worksheet->write($z,3, $row->firstname);
	$worksheet->write($z,4, $row->groupe);
	$worksheet->write($z,5, $row->logaswnumber);
	$worksheet->write($z,6, $row->physaswnumber);
	$worksheet->write($z,7, $row->validstart);
	$worksheet->write($z,8, $row->validend);
	$worksheet->write($z,9, $row->text1);
	$worksheet->write($z,10,$row->text2);
	$worksheet->write($z,11,$row->text3);
	$worksheet->write($z,12,$row->text4);
	$worksheet->write($z,13,$row->text5);
	$worksheet->write($z,14,$row->text6);
	$worksheet->write($z,15,$row->pin);
	$worksheet->write($z,16,'0');
	$z++;
}

$workbook->close();
?>