<?php
/* Copyright (C) 2013 FH fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Karl Burkhart <burkhart@technikum-wien.at>,
 *          Manfred Kindl <manfred.kindl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/studiengang.class.php');
require_once('../include/student.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/adresse.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/akadgrad.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/nation.class.php');
require_once('../include/prestudent.class.php');

$prestudent_arr = (isset($_REQUEST['prestudent_id'])?$_REQUEST['prestudent_id']:null);
$prestudent_arr = explode(";",$prestudent_arr);

$db = new basis_db();

echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n"; 
echo "<ausbildungsvertraege>\n";

/////
// Wenn auch PrestudentIDs uebergeben werden
/////
$prest_id = isset($prestudent_arr[1])?$prestudent_arr[1]:$prestudent_arr[0];

$prestudent_help = new prestudent();
// an 2ter stelle da im Aufruf vom FAS ;<prestudent_id>; der erste immer '' ist
if($prestudent_help->load($prest_id))
{
	$studiengang = new studiengang();
	$studiengang->load($prestudent_help->studiengang_kz);
	switch($studiengang->typ)
	{
		case 'b':
			$studTyp = 'Bachelor';
			$titel_kurzbz = 'BSc';
			break;
		case 'm':
			$studTyp = 'Master';
			$titel_kurzbz ='MSc';
			break;
		case 'd':
			$studTyp = 'Diplom';
			break;
		default:
			$studTyp ='';
			$titel_kurzbz = '';
	}
	echo "\t<studiengang_typ>".$studTyp."</studiengang_typ>\n";
	echo "\t<studiengang>".$studiengang->bezeichnung."</studiengang>\n";
	echo "\t<studiengang_englisch>".$studiengang->english."</studiengang_englisch>\n";
}

foreach($prestudent_arr as $prest_id)
{
	if($prest_id=='')
		continue;

		echo "\t<ausbildungsvertrag>\n";

		$prestudent = new prestudent();
		if($prestudent->load($prest_id))
		{
			$person = new person();
			if($person->load($prestudent->person_id))
			{
				$datum_aktuell = date('d.m.Y');
				$gebdatum = date('d.m.Y',strtotime($person->gebdatum));
				$studiengang = new studiengang();
				$studiengang->load($prestudent->studiengang_kz);
				$staatsbuergerschaft = new nation();
				$staatsbuergerschaft->load($person->staatsbuergerschaft);

				$svnr = ($person->svnr == '')?($person->ersatzkennzeichen != ''?'Ersatzkennzeichen: '.$person->ersatzkennzeichen:''):$person->svnr;

				//Wenn Lehrgang, dann Erhalter-KZ vor die Studiengangs-Kz hängen
				if ($studiengang->studiengang_kz<0)
				{
					$stg = new studiengang();
					$stg->load($studiengang->studiengang_kz);

					$studiengang_kz = sprintf("%03s", $stg->erhalter_kz).sprintf("%04s", abs($studiengang->studiengang_kz));
				}
				else
					$studiengang_kz = sprintf("%04s", abs($studiengang->studiengang_kz));

					echo "\t\t<quote>1</quote>\n";
					echo "\t\t<anrede>".$person->anrede."</anrede>\n";
					echo "\t\t<vorname>".$person->vorname." ".$person->vornamen."</vorname>\n";
					echo "\t\t<vornamen>".$person->vornamen."</vornamen>\n";
					echo "\t\t<nachname>".$person->nachname."</nachname>\n";
					echo "\t\t<titelpre>".$person->titelpre."</titelpre>\n";
					echo "\t\t<titelpost>".$person->titelpost."</titelpost>\n";
					echo "\t\t<gebdatum>".$gebdatum."</gebdatum>\n";
					echo "\t\t<gebort>".$person->gebort."</gebort>\n";
					echo "\t\t<staatsbuergerschaft>".$staatsbuergerschaft->langtext."</staatsbuergerschaft>\n";
					echo "\t\t<svnr>".$svnr."</svnr>\n";
					echo "\t\t<studiengang>".$studiengang->bezeichnung."</studiengang>\n";
					echo "\t\t<studiengang_englisch>".$studiengang->english."</studiengang_englisch>\n";
					echo "\t\t<studiengang_kurzbz>".$studiengang->kurzbzlang."</studiengang_kurzbz>\n";
					echo "\t\t<studiengang_kz>".$studiengang_kz."</studiengang_kz>\n";
					echo "\t\t<studiengangSprache>".$studiengang->sprache."</studiengangSprache>";

					echo "\t\t<aktuellesJahr>".date('Y')."</aktuellesJahr>";

					switch($studiengang->typ)
					{
						case 'b':
							$studTyp = 'Bachelor';
							$titel_kurzbz = 'BSc';
							break;
						case 'm':
							$studTyp = 'Master';
							$titel_kurzbz ='MSc';
							break;
						case 'd':
							$studTyp = 'Diplom';
							break;
						default:
							$studTyp ='';
							$titel_kurzbz = '';
					}

					echo "\t\t<titel_kurzbz>".$titel_kurzbz."</titel_kurzbz>\n";
					echo "\t\t<studiengang_typ>".$studTyp."</studiengang_typ>\n";
					echo "\t\t<studiengang_sprache>".$studiengang->sprache."</studiengang_sprache>\n";
					echo "\t\t<studiengang_maxsemester>".$studiengang->max_semester."</studiengang_maxsemester>\n";
					echo "\t\t<studiengang_anzahljahre>".($studiengang->max_semester/2)."</studiengang_anzahljahre>\n";


					//Bis die Akadgrad-Tabelle an die Studienordnung angepasst ist, wird der Akadgrad hier ermittelt

					$akadgrad_titel = '';
					$akadgrad_kurzbz = '';

					$qry = "SELECT * FROM lehre.tbl_akadgrad
							WHERE studiengang_kz=".$db->db_add_param($studiengang->studiengang_kz, FHC_INTEGER)."
							AND (geschlecht=".$db->db_add_param($person->geschlecht, FHC_STRING)." OR geschlecht IS NULL)
							LIMIT 1";

					if($db->db_query($qry))
					{
						if($row = $db->db_fetch_object())
						{
							$akadgrad_titel = $row->titel;
							$akadgrad_kurzbz = $row->akadgrad_kurzbz;
						}
					}

					echo "\t\t<akadgrad>".$akadgrad_titel."</akadgrad>\n";
					echo "\t\t<akadgrad_kurzbz>".$akadgrad_kurzbz."</akadgrad_kurzbz>\n";

					echo "\t\t<datum_aktuell>".$datum_aktuell."</datum_aktuell>\n";

					$adresse = new adresse();
					$adresse->load_pers($person->person_id);

					foreach($adresse->result as $row_adresse)
					{
						if($row_adresse->zustelladresse)
						{
							echo "\t\t<strasse>".$row_adresse->strasse."</strasse>\n";
							echo "\t\t<plz>".$row_adresse->plz." ".$row_adresse->ort."</plz>\n";
							echo "\t\t<nation>".$row_adresse->nation."</nation>\n";
							break;
						}
					}
					$prestudent_orgform = new prestudent();
					$prestudent_orgform->getLastStatus($prest_id, null, null);

					if($prestudent_orgform->orgform_kurzbz!='')
						$orgform = $prestudent_orgform->orgform_kurzbz;
					else
						$orgform = $studiengang->orgform_kurzbz;

					echo "\t\t<orgform>".$orgform."</orgform>\n";

					$ausbildungssemester = ($prestudent_orgform->ausbildungssemester!='')?$prestudent_orgform->ausbildungssemester:'1';
					echo "\t\t<semesterStudent>".$ausbildungssemester."</semesterStudent>";
			}
		}
		echo "\t</ausbildungsvertrag>\n";
}
echo "</ausbildungsvertraege>";

?>
