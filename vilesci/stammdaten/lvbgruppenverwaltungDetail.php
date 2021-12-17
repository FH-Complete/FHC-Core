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

$user = get_uid();
$aktiv = (isset($_POST['aktiv']) ? $_POST['aktiv'] : '');
$changeState = (isset($_POST['changeState']) ? $_POST['changeState'] : '');
$type = (isset($_POST['type']) ? $_POST['type'] : '');
$semester = (isset($_POST['semester']) ? $_POST['semester'] : '');
$verband = (isset($_POST['verband']) ? $_POST['verband'] : '');
$gruppe = (isset($_POST['gruppe']) ? $_POST['gruppe'] : '');
$gruppe_kurzbz = (isset($_POST['gruppe_kurzbz']) ? $_POST['gruppe_kurzbz'] : '');
$studiengang_kz = (isset($_POST['studiengang_kz']) ? $_POST['studiengang_kz'] : '');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if ($rechte->isBerechtigt('admin', $studiengang_kz, 'suid')
	|| $rechte->isBerechtigt('lehre/gruppe', $studiengang_kz, 'suid'))
	$admin = true;
else
	$admin = false;

if ($rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid'))
	$assistenz = true;
else
	$assistenz = false;

if (!$admin && !$assistenz)
	die('Sie haben keine Berechtigung für diesen Studiengang');
$studiengang = new studiengang();
$studiengang->load($studiengang_kz);

//Aenderung des Aktiv Status
if ($changeState != '')
{
	if (!$admin)
		die('Sie haben keine Berechtigung zum Speichern');

	if ($gruppe_kurzbz != '')
	{
		$gruppe = new gruppe();
		if ($gruppe->load($gruppe_kurzbz))
		{
			$gruppe->aktiv = !$gruppe->aktiv;
			if ($gruppe->save(false))
			{
				echo "erfolgreich";
			}
			else
			{
				echo "Fehler beim Aendern des Aktiv-Feldes: $gruppe->errormsg";
			}
		}
		else
		{
			echo "Spezialgruppe wurde nicht gefunden";
		}
	}
	else
	{
		$lvb = new lehrverband();

		if ($lvb->load($studiengang_kz, $semester, $verband, $gruppe))
		{
			$lvb->aktiv = !$lvb->aktiv;
			if ($lvb->save(false))
			{
				echo "erfolgreich";
			}
			else
			{
				echo "Fehler beim Aendern des Aktiv-Feldes: $lvb->errormsg";
			}
		}
		else
		{
			echo "<span class='error'>Lehrverband wurde nicht gefunden</span>";
		}
	}
}


//Anzeigen der Gruppen Details
if ($type == 'edit')
{
	if ($gruppe_kurzbz != '')
	{
		$gruppe = new gruppe();
		if ($gruppe->load($gruppe_kurzbz))
		{

			echo "<div class='detailsDiv'>Details von $gruppe_kurzbz<br><br>";
								//echo "<form action='" . $_SERVER['PHP_SELF'] . "?type=save&studiengang_kz=$studiengang_kz&gruppe_kurzbz=$gruppe_kurzbz' method='POST'>

			echo "<form id='formSpzSave' action='javascript:saveSpzGroup(\"".$gruppe->studiengang_kz."\",\"".$gruppe->gruppe_kurzbz."\",\"save\");' method='POST'>
		  <table>";
			if ($admin)
			{
				echo "<tr>
					<td><b>Kurzbezeichnung:</b></td>
					<td><input id='spzKurzBz' type='text' name='kurzbezeichnung' size='30' maxlength='128' value='$gruppe->gruppe_kurzbz'/></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>";
			}
			echo "<tr>
				<td>Bezeichnung:</td>
				<td><input id='spzBezeichnung' type='text' name='bezeichnung' size='30' maxlength='32' value='$gruppe->bezeichnung'/> (max. 32 Zeichen)</td>
			</tr>";
			if ($admin)
			{
				echo "
				<tr>
					<td>Beschreibung:</td>
					<td><input id='spzBeschreibung' type='text' name='beschreibung' size='30' maxlength='128' value='$gruppe->beschreibung'/> (max. 128 Zeichen)</td>
				</tr>
				<tr>
					<td>Sichtbar:</td>
					<td><input id='spzSichtbar' type='checkbox' name='sichtbar' " . ($gruppe->sichtbar ? 'checked' : '') . " /></td>
				</tr>
				<tr>
					<td>Lehre:</td>
					<td><input id='spzLehre' type='checkbox' name='lehre' " . ($gruppe->lehre ? 'checked' : '') . " /></td>
				</tr>
				<tr>
					<td>Aktiv:</td>
					<td><input id='spzAktiv' type='checkbox' name='aktiv' " . ($gruppe->aktiv ? 'checked' : '') . " /></td>
				</tr>
				<tr>
					<td>Sort:</td>
					<td><input id='spzSort' type='text' name='sort' size='2' maxlength='2' value='$gruppe->sort' /></td>
				</tr>";
				$stg_obj = new studiengang($studiengang_kz);
				if ($stg_obj->mischform)
				{
					echo "
				<tr>
					<td>OrgForm</td>
					<td>";
					echo "	<SELECT id='spzOrgform' name='orgform_kurzbz'>";
					echo "		<OPTION value=''>-- keine Auswahl --</OPTION>";
					$qry_orgform = "SELECT * FROM bis.tbl_orgform WHERE orgform_kurzbz NOT IN ('VBB', 'ZGS') ORDER BY orgform_kurzbz";
					if ($result_orgform = $db->db_query($qry_orgform))
					{
						while ($row_orgform = $db->db_fetch_object($result_orgform))
						{
							if ($row_orgform->orgform_kurzbz == $gruppe->orgform_kurzbz)
								$selected = 'selected';
							else
								$selected = '';

							echo "		<OPTION value='$row_orgform->orgform_kurzbz' $selected>$row_orgform->bezeichnung</OPTION>";
						}
					}
					echo "</SELECT></td>
					</tr>";
				}
				echo "
			<tr>
				<td>Mailgrp:</td>
				<td><input id='spzMailgrp' type='checkbox' name='mailgrp' " . ($gruppe->mailgrp ? 'checked' : '') . " /></td>
			</tr>
			<tr>
				<td>Generiert:</td>
				<td><input id='spzGeneriert' type='checkbox' name='generiert' " . ($gruppe->generiert ? 'checked' : '') . " /></td>
			</tr>";
			}
			echo "
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td></td>
				<td><input type='submit' value='Speichern' /></td>
			</tr>
		  </table>
		  </form></div>";
		}
	}
	else
	{
		$lvb = new lehrverband();
		if ($lvb->load($studiengang_kz, $semester, $verband, $gruppe))
		{

			echo "<div class='detailsDiv'>Details von $studiengang->kuerzel - $semester$verband$gruppe<br><br>";
			echo "<form id='formSave' action='javascript:saveGroup(\"".$lvb->studiengang_kz."\",\"".$lvb->semester."\",\"".$lvb->verband."\",\"".$lvb->gruppe."\",\"save\");' method='POST'>
		  <table>
			<tr>
				<td>Bezeichnung:</td>
				<td><input id='newBez' type='text' name='bezeichnung' size='16' maxlength='16' value='$lvb->bezeichnung'/></td>
			</tr>
			<tr>
				<td>
					<input type='hidden' name='studiengang_kz' value='".$lvb->studiengang_kz."'/>
					<input type='hidden' name='semester' value='".$lvb->semester."'/>
					<input type='hidden' name='verband' value='".$lvb->verband."'/>
					<input type='hidden' name='gruppe' value='".$lvb->gruppe."'/>
					<input type='hidden' name='type' value='save'/>
				</td>
			</tr>";
			if ($admin)
			{
				echo "
			<tr>
				<td>Aktiv:</td>
				<td><input id='aktiv' type='checkbox' name='aktiv' " . ($lvb->aktiv ? 'checked' : '') . " /></td>
			</tr>";
				$stg_obj = new studiengang($studiengang_kz);
				if ($stg_obj->mischform)
				{
					echo "
			<tr>
				<td>OrgForm</td>
				<td>";
					echo "	<SELECT name='orgform_kurzbz' id='orgform_kurzbz'>";
					echo "		<OPTION value=''>-- keine Auswahl --</OPTION>";
					$qry_orgform = "SELECT * FROM bis.tbl_orgform WHERE orgform_kurzbz NOT IN ('VBB', 'ZGS') ORDER BY orgform_kurzbz";
					if ($result_orgform = $db->db_query($qry_orgform))
					{
						while ($row_orgform = $db->db_fetch_object($result_orgform))
						{
							if ($row_orgform->orgform_kurzbz == $lvb->orgform_kurzbz)
								$selected = 'selected';
							else
								$selected = '';

							echo "		<OPTION value='$row_orgform->orgform_kurzbz' $selected>$row_orgform->bezeichnung</OPTION>";
						}
					}
					echo "</SELECT></td>
					</tr>";
				}
			}
			echo "
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td></td>
				<td><input type='submit' value='Speichern' /></td>
			</tr>
		  </table>
		  </form>";
			echo '</div>';
		}
		else
			echo "Gruppe wurde nicht gefunden";
	}
}

//Anlegen einer neuen Gruppe
if ($type == 'neu')
{
	if (!$admin)
		die('Sie haben keine Berechtigung zum Speichern');

	if (isset($_POST['spzgruppe_neu']))
	{
		if(preg_match('/^[A-Z0-9a-z\-\_]*$/', $_POST['spzgruppe_neu']))
		{
			//neue Spezialgruppe anlegen
			$gruppe_kurzbz = $studiengang->kuerzel . '-' . $semester . strtoupper($_POST['spzgruppe_neu']);

			$gruppe = new gruppe();
			if (!$gruppe->exists($gruppe_kurzbz))
			{
				$gruppe->gruppe_kurzbz = $gruppe_kurzbz;
				$gruppe->studiengang_kz = $studiengang_kz;
				$gruppe->semester = $semester;
				$gruppe->bezeichnung = "";
				$gruppe->beschreibung = "";
				$gruppe->aktiv = true;
				$gruppe->sichtbar = true;
				$gruppe->lehre = true;
				$gruppe->sort = '';
				$gruppe->mailgrp = true;
				$gruppe->generiert = false;
				$gruppe->insertamum = date('Y-m-d H:i:s');
				$gruppe->insertvon = $user;

				if ($gruppe->save(true))
				{
					$returndata = array('status'=>'ok','gruppe_kurzbz'=>$gruppe->gruppe_kurzbz,'message'=>'Gruppe wurde angelegt');
					echo json_encode($returndata);
				}
				else
				{
					$returndata = array('status'=>'failed','message'=>"<span class='error'>Fehler beim anlegen der Gruppe:$gruppe->errormsg</span>");
					echo json_encode($returndata);
				}
			}
			else
			{
				$returndata = array('status'=>'failed','message'=>"<span class='error'>Diese Gruppe Existiert bereits: $gruppe_kurzbz</span>");
				echo json_encode($returndata);
			}
		}
		else
		{
			$returndata = array('status'=>'failed','message'=>"<span class='error'>Bitte verwenden Sie für den Gruppennamen keine Sonderzeichen oder Umlaute</span>");
			echo json_encode($returndata);
		}

	}
	else
	{
		$lvb = new lehrverband();

		if (isset($_POST['semester_neu']))
		{
			//Neues Semester anlegen
			$semester = $_POST['semester_neu'];
			$verband = ' ';
			$gruppe = ' ';
		}
		elseif (isset($_POST['verband_neu']))
		{
			//neuen Verband anlegen
			$verband = $_POST['verband_neu'];
			$gruppe = ' ';
		}
		elseif (isset($_POST['gruppe_neu']))
		{
			//neue Gruppe anlegen
			$gruppe = $_POST['gruppe_neu'];
		}

		if (!$lvb->exists($studiengang_kz, $semester, $verband, $gruppe))
		{
			$lvb->studiengang_kz = $studiengang_kz;
			$lvb->semester = $semester;
			$lvb->verband = $verband;
			$lvb->gruppe = $gruppe;
			$lvb->aktiv = true;
			$lvb->bezeichnung = '';

			if ($lvb->save(true))
			{
				$returndata = array('status'=>'ok','gruppe'=>trim($studiengang_kz.$semester.$verband.$gruppe),'message'=>"Daten wurden erfolgreich angelegt");
				echo json_encode($returndata);
			}
			else
			{
				$returndata = array('status'=>'failed','gruppe'=>trim($studiengang_kz.$semester.$verband.$gruppe),'message'=>"<span class='error'>Fehler beim Anlegen der Gruppe: $lvb->errormsg</span>");
				echo json_encode($returndata);
			}
		}
		else
		{
			$returndata = array('status'=>'failed','gruppe'=>trim($studiengang_kz.$semester.$verband.$gruppe),'message'=>"<span class='error'>Diese Gruppe Existiert bereits</span>");
			echo json_encode($returndata);
		}
	}
}

//Speichern der geaenderten Gruppendaten
if ($type == 'save')
{
	//Spezialgruppe speichern
	if ($gruppe_kurzbz != '')
	{
		$gruppe = new gruppe();
		if ($gruppe->load($gruppe_kurzbz))
		{
			if(!preg_match('/^[A-Z0-9a-z\-\_]*$/', $_POST['kurzBzNeu']) ||
				!preg_match('/^[A-Z0-9a-z\-\_]*$/', $_POST['bezeichnung']))
			{
				echo "<span class='error'>Bitte verwenden Sie für die Kurzbezeichnung/Bezeichnung keine Sonderzeichen oder Umlaute</span>";
				return;
			}

			$gruppe->bezeichnung = $_POST['bezeichnung'];
			if ($admin)
			{
				$gruppe->gruppe_kurbzNeu = (isset($_POST['kurzBzNeu']) ? $_POST['kurzBzNeu'] : $gruppe_kurzbz);
				$gruppe->beschreibung = $_POST['beschreibung'];
				$gruppe->sichtbar = isset($_POST['sichtbar']);
				$gruppe->lehre = isset($_POST['lehre']);
				$gruppe->aktiv = isset($_POST['aktiv']);
				$gruppe->sort = $_POST['sort'];
				$gruppe->mailgrp = isset($_POST['mailgrp']);
				$gruppe->generiert = isset($_POST['generiert']);
				if (isset($_POST['orgform_kurzbz']))
					$gruppe->orgform_kurzbz = $_POST['orgform_kurzbz'];
			}
			$gruppe->updateamum = date('Y-m-d H:i:s');
			$gruppe->updatevon = $user;
			if ($gruppe->save(false))
			{
				echo 'Daten wurden erfolgreich geaendert';
			}
			else
			{
				echo "Fehler beim Speichern der Daten: $gruppe->errormsg";
			}

			$semester = $gruppe->semester;
		}
		else
			echo "Gruppe konnte nicht geladen werden";
	}
	else
	{
		//Lehrverbandsgruppe speichern
		$lvb = new lehrverband();
		if ($lvb->load($studiengang_kz, $semester, $verband, $gruppe))
		{
			if(!preg_match('/^[A-Z0-9a-z\-\_]*$/', $_POST['bezeichnung']))
			{
				echo "<span class='error'>Bitte verwenden Sie für die Bezeichnung keine Sonderzeichen oder Umlaute</span>";
				return;
			}

			$lvb->bezeichnung = $_POST['bezeichnung'];

			if ($admin)
			{

				$lvb->aktiv = isset($_POST['aktiv']);
				if (isset($_POST['orgform_kurzbz']))
					$lvb->orgform_kurzbz = $_POST['orgform_kurzbz'];
			}

			if ($lvb->save(false))
			{
				echo 'Daten wurden erfolgreich geaendert';
			}
			else
			{
				echo "Fehler beim Speichern der Daten: $lvb->errormsg";
			}
		}
		else
		{
			echo "Gruppe konnte nicht geladen werden";
		}
	}
}
?>
