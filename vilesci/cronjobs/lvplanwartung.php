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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
/**
 * Cronjob der automatisch nicht verplante Gruppen zur bestehenden Planunung hinzufuegt
 * Aufruf von der Commandline
 * php lvplanwartung.php --stg_kz 10001 --semester 0
 * Parameter Semester ist optional stg_kz muss uebergeben werden
 */
require_once(dirname(__FILE__).'/../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../include/basis_db.class.php');
require_once(dirname(__FILE__).'/../../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

// Wenn das Script nicht ueber Commandline gestartet wird, muss eine
// Authentifizierung stattfinden
if(php_sapi_name() != 'cli')
{
	// Benutzerdefinierte Variablen laden
	$user = get_uid();
	loadVariables($user);

	// Berechtigungen pruefen
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('lehre/lvplan', null,'suid'))
		die('Sie haben keine Berechtigung für diese Seite');
}
else
	$user = 'lvplanwartung_cronjob';

// Commandline Paramter parsen bei Aufruf ueber Cronjob
// zb php lvplanwartung.php --stg_kz 10001 --semester 0
$longopt = array(
  "stg_kz:",
  "semester:"
);
$commandlineparams = getopt('', $longopt);
if(isset($commandlineparams['stg_kz']))
	$studiengang_kz=$commandlineparams['stg_kz'];
elseif(isset($_GET['stg_kz']))
	$studiengang_kz=$_GET['stg_kz'];
else
	die("Studiengangskennzahl muss uebergeben werden!\nAufruf: php lvplanwartung.php --stg_kz 10001\n");

if(isset($commandlineparams['semester']))
	$semester=$commandlineparams['semester'];
elseif(isset($_GET['semester']))
	$semester=$_GET['semester'];
else
	$semester='';

$stsem_obj = new studiensemester();
$studiensemester_kurzbz = $stsem_obj->getAktOrNext();

$qry="SELECT
			*, planstunden-verplant::smallint AS offenestunden
		FROM
			lehre.vw_lva_stundenplandev
			JOIN lehre.tbl_lehrform ON (vw_lva_stundenplandev.lehrform=tbl_lehrform.lehrform_kurzbz)
		WHERE
			studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz);

if($semester!='')
	$qry.=" AND semester=".$db->db_add_param($semester);

$qry.="		AND studiengang_kz=".$db->db_add_param($studiengang_kz)."
			AND verplant=0
			AND planstunden>0
			AND lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_stundenplandev)
		ORDER BY offenestunden DESC, lehrfach, lehrform, semester, verband, gruppe, gruppe_kurzbz;";

$stg_obj = new studiengang();
$stg_obj->getAll(null, false);
if($result_lv=$db->db_query($qry))
{
	while($row_lv = $db->db_fetch_object($result_lv))
	{
		// Termine holen
		$qry = "SELECT DISTINCT datum, stunde FROM lehre.tbl_stundenplandev WHERE lehreinheit_id=".$db->db_add_param($row_lv->lehreinheit_id, FHC_INTEGER);

		if(!$result=$db->db_query($qry))
			die ($qry .' '.$db->db_last_error());

		while ($row=$db->db_fetch_object($result))
		{
			$qry = "SELECT
						DISTINCT ort_kurzbz
					FROM
						lehre.tbl_stundenplandev
					WHERE
						lehreinheit_id=".$db->db_add_param($row_lv->lehreinheit_id, FHC_INTEGER)."
						AND datum=".$db->db_add_param($row->datum)."
						AND stunde=".$db->db_add_param($row->stunde).";";

			if(!$result_ort=$db->db_query($qry))
				die ("DB Fehler $qry" .' '.$db->db_last_error());

			while ($row_ort=$db->db_fetch_object($result_ort))
			{
				// Pruefen ob der Eintrag schon in der Datenbank vorhanden ist
				// da sonst bei mehrmaligem Refresh der Seite der Eintrag oefter eingetragen wird
				$qry = "SELECT
							1
						FROM
							lehre.tbl_stundenplandev
						WHERE datum=".$db->db_add_param($row->datum).
						'	AND stunde='.$db->db_add_param($row->stunde).
						'	AND ort_kurzbz='.$db->db_add_param($row_ort->ort_kurzbz).
						'	AND unr='.$db->db_add_param($row_lv->unr).
						'	AND mitarbeiter_uid='.$db->db_add_param($row_lv->lektor_uid).
						'	AND studiengang_kz='.$db->db_add_param($row_lv->studiengang_kz).
						'	AND semester='.$db->db_add_param($row_lv->semester).
						'	AND verband='.$db->db_add_param($row_lv->verband).
						'	AND	gruppe='.$db->db_add_param($row_lv->gruppe);

				if ($row_lv->gruppe_kurzbz!='')
					$qry.=' AND gruppe_kurzbz='.$db->db_add_param($row_lv->gruppe_kurzbz);
				else
					$qry.=' AND gruppe_kurzbz is null';

				if($result_stplcheck=$db->db_query($qry))
				{
					if($db->db_num_rows($result_stplcheck)==0)
					{
						$qry="INSERT INTO lehre.tbl_stundenplandev (datum,stunde,ort_kurzbz,unr,mitarbeiter_uid,studiengang_kz,
								semester,verband,gruppe,gruppe_kurzbz,lehreinheit_id, updatevon, insertvon)
								VALUES (".$db->db_add_param($row->datum).",".
								$db->db_add_param($row->stunde).",".
								$db->db_add_param($row_ort->ort_kurzbz).",".
								$db->db_add_param($row_lv->unr).",".
								$db->db_add_param($row_lv->lektor_uid).",".
								$db->db_add_param($row_lv->studiengang_kz).",".
								$db->db_add_param($row_lv->semester).",".
								$db->db_add_param($row_lv->verband, FHC_STRING, false).",".
								$db->db_add_param($row_lv->gruppe, FHC_STRING, false).",";

						if ($row_lv->gruppe_kurzbz!='')
							$qry.=$db->db_add_param($row_lv->gruppe_kurzbz).",";
						else
							$qry.="NULL,";
						$qry.=$db->db_add_param($row_lv->lehreinheit_id, FHC_INTEGER).",".$db->db_add_param($user).",".$db->db_add_param($user).");";

						if(!$result_insert=$db->db_query($qry))
							die ("DB Fehler $qry" .' '.$db->db_last_error());
						echo "Adding $row->datum - $row->stunde. Stunde $row_ort->ort_kurzbz ".($row_lv->gruppe_kurzbz!=''?$row_lv->gruppe_kurzbz:mb_strtoupper($stg_obj->kuerzel_arr[$row_lv->studiengang_kz]).'-'.$row_lv->semester.$row_lv->verband.$row_lv->gruppe)."\n";
					}
					else
					{
						$outp.='Fehlgeschlagen: Eintrag bereits vorhanden';
					}
				}
			}
		}
	}
}
?>
