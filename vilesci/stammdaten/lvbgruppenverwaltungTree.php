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
 *			Stefan Puraner		< puraner@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/lehrverband.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if (isset($_POST['studiengang_kz']) && is_numeric($_POST['studiengang_kz']))
	$studiengang_kz = $_POST['studiengang_kz'];
else
	$studiengang_kz = '';

$user = get_uid();

$studiengang = new studiengang();
$studiengang->load($studiengang_kz);

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if ($rechte->isBerechtigt('admin', $studiengang_kz, 'suid')
 || $rechte->isBerechtigt('lehre/gruppe', $studiengang_kz, 'suid'))
	$admin = true;
else
	$admin = false;

$lehrverband = new lehrverband();

//Semester des Studiengangs laden und ausgeben
$semResult = $lehrverband->getSemesterFromStudiengang($studiengang_kz, !$admin);
if ($semResult != false) {
	echo "<ul>";
	foreach ($semResult as $s) {
		$sem = $s["semester"];
		echo "<li id='node_".$studiengang_kz.$sem.$s["aktiv"]."'>
					<a href='javascript:void(0);' onclick='changeState(\"pic_".$studiengang_kz.$sem.$s["aktiv"]."\",$studiengang_kz,\"" . $sem . "\",\" \",\" \",\"" .$s["aktiv"]. "\")'>
						<img id='pic_".$studiengang_kz.$sem.$s["aktiv"]."' src='../../skin/images/" . ($s["aktiv"] == 't' ? 'true.png' : 'false.png') . "' aktiv='".$s["aktiv"]."' height='20'>
					</a>
					<a name='$studiengang_kz$sem' href='javascript:void(0);' onclick='getGruppenDetails(\"edit\",\"\",$studiengang_kz,\"" . $sem . "\",\" \",\" \",\"".$s["aktiv"]."\")'>
						Semester " . $s["semester"] . "
					</a>";

		//Verbände des Semesters holen und ausgeben
		$verbandResult = $lehrverband->getVerbandFromSemester($studiengang_kz, $s["semester"], !$admin);
		if ($verbandResult != false) {
			echo "<ul>";
			foreach ($verbandResult as $v) {
				$verb = $v["verband"];
				if ($verb != ' ') {
					echo "<li id='node_$studiengang_kz$sem$verb'>
								<a href='javascript:void(0);' onclick='changeState(\"pic_".$studiengang_kz.$sem.$verb."\",$studiengang_kz,\"" . $sem . "\",\"" . $verb . "\",\" \",\"" . $v["aktiv"] . "\")'>
									<img id='pic_".$studiengang_kz.$sem.$verb."' src='../../skin/images/" . ($v["aktiv"] == 't' ? 'true.png' : 'false.png') . "' aktiv='".$v["aktiv"]."' height='20'>
								</a>
								<a name='$studiengang_kz$sem$verb' href='javascript:void(0);' onclick='getGruppenDetails(\"edit\",\"\",$studiengang_kz,\"" . $sem . "\",\"" . $verb . "\",\" \",\"".$v["aktiv"]."\")'>
									Verband " . $verb . ($v["bezeichnung"] != '' ? " (" . $v["bezeichnung"] . ")" : '' ) . "
								</a>";
					//Gruppen des Verbandes holen und ausgeben
					$grpResult = $lehrverband->getGruppeFromVerband($studiengang_kz, $s["semester"], $v["verband"], !$admin);
					if ($grpResult != null) {
						echo "<ul>";
						foreach ($grpResult as $g) {
							$grp = $g["gruppe"];
							$grpBez = $g["bezeichnung"];
							if ($grp != ' ') {
								if ($g["gruppe"] != '') {
									echo "<li id=\"node_$studiengang_kz$sem$verb$grp\">
												<a href='javascript:void(0);' onclick='changeState(\"pic_".$studiengang_kz.$sem.$verb.$grp."\",$studiengang_kz,\"" . $sem . "\",\"" . $verb . "\",\"" . $grp . "\",\"" . $g["aktiv"] . "\")'>
													<img id='pic_".$studiengang_kz.$sem.$verb.$grp."' src='../../skin/images/" . ($g["aktiv"] == 't' ? 'true.png' : 'false.png') . "' aktiv='".$g["aktiv"]."' height='20'>
												</a>
												<a name='$studiengang_kz$sem$verb$grp' href='javascript:void(0);' onclick='getGruppenDetails(\"edit\",\"\",$studiengang_kz,\"" . $sem . "\",\"" . $verb . "\",\"" . $grp . "\",\"".$g["aktiv"]."\")'>
													Gruppe " . $grp . ($grpBez != '' ? " (" . $grpBez . ")" : '' ) . "
												</a>
											</li>";
								}
							}
						}
						if($admin)
						{
							//Formular für neue Gruppe
							echo "<li id=\"formNode_".$studiengang_kz.$sem.$verb."\">
								<a href='javascript:void(0);'>
									<form id='newDataForm".$studiengang_kz.$sem.$verb."' method='POST' action='javascript:newGroup(\"".$studiengang_kz.$sem.$verb."\");'>
										<input type='hidden' name='type' value='neu'>
										<input type='hidden' name='studiengang_kz' value='".$studiengang_kz."' />
										<input type='hidden' name='semester' value='".$sem."' />
										<input type='hidden' name='verband' value='".$verb."' />
										<input type='text' maxlength='1' size='1' placeholder='1' name='gruppe_neu'/>
										<input type='submit' value='Gruppe anlegen'/>
									</form>
								</a></li>";
						}
						echo "</ul></li>";
					}
				}
			}
			if($admin)
			{
				//Formular für neuen Verband
				echo "<li id=\"formNode_".$studiengang_kz.$sem."\">
						<a href='javascript:void(0);'>
							<form id='newDataForm".$studiengang_kz.$sem."' method='POST' action='javascript:newVerband(\"".$studiengang_kz.$sem."\");'>
								<input type='hidden' name='type' value='neu'>
								<input type='hidden' name='studiengang_kz' value='".$studiengang_kz."' />
								<input type='hidden' name='semester' value='".$sem."' />
								<input type='text' maxlength='1' size='1' placeholder='A' name='verband_neu'/>
								<input type='submit' value='Verband anlegen'/>
							</form>
						</a>
					</li>";
			}
			//Ausgabe der Spezialgruppen des Semesters
			$gruppe = new gruppe();
			$gruppe->getgruppe($studiengang_kz, $sem);
			echo "<li id=\"spzGrp_".$sem."\"><a href='#'>Spezialgruppen</a><ul>";
			foreach ($gruppe->result as $spezGroup) {
				$state = $spezGroup->aktiv == true ? "t" : "f";
				$kurzBz = $spezGroup->gruppe_kurzbz;
				$type = "edit";
				echo "<li id=\"spzNode_$spezGroup->bezeichnung\">
						<a href='javascript:void(0);' onclick='changeState(\"pic_".$studiengang_kz.$sem.$kurzBz."\",$studiengang_kz,\"" . $sem . "\",\" \",\" \",\"". $state."\",\"".$kurzBz."\")'>
							<img id='pic_".$studiengang_kz.$sem.$kurzBz."' src='../../skin/images/" . ($spezGroup->aktiv == 't' ? 'true.png' : 'false.png') . "' aktiv='".$state."' height='20'>
						</a>
						<a name='".$kurzBz."' href='javascript:void(0);' onclick='getGruppenDetails(\"edit\",\"" . $kurzBz . "\",$studiengang_kz,\"" . $sem . "\")'>
							" . $kurzBz . ($spezGroup->bezeichnung != '' ? "(" . $spezGroup->bezeichnung . ")" : '')."
						</a>
					</li>";
			}
			if($admin)
			{
				//Formular für neue Spezialgruppe
				echo "<li id=\"formNodeSpz_".$studiengang_kz.$sem."\">
						<a href='javascript:void(0);'>
							<form id='newSpzDataForm".$studiengang_kz.$sem."' method='POST' action='javascript:newSpezGroup(\"".$studiengang_kz.$sem."\");'>
								<input type='hidden' name='type' value='neu'>
								<input type='hidden' name='semester' value='".$sem."' />
								<input type='hidden' name='studiengang_kz' value='".$studiengang_kz."' />
									".$studiengang->kuerzel."-".$sem."
								<input type='text' maxlength='11' size='11' name='spzgruppe_neu'/>
								<input type='submit' value='Spezialgruppe anlegen'/>
							</form>
						</a>
					</li>";
			}
			echo "</ul></li></ul></li>";
		}
	}
	//Formular für neues Semester
	if($admin)
	{
		echo "<li id=\"formNode_".$studiengang_kz."\">
				<a href='javascript:void(0);'>
					<form id='newDataForm".$studiengang_kz."' method='POST' action='javascript:newSemester(\"".$studiengang_kz."\");'>
						<input type='hidden' name='type' value='neu'>
						<input type='hidden' name='studiengang_kz' value='".$studiengang_kz."' />
						<input type='text' maxlength='2' size='2' placeholder='10' name='semester_neu'/>
						<input type='submit' value='Semester anlegen'/>
					</form>
				</a>
			</li>";
	}
	echo "</ul></li>";
} else {
	echo "No Data available!";
}
?>
