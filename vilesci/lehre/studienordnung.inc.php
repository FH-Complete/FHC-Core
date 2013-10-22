<?php
/* 
 * Copyright 2013 fhcomplete.org
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studienordnung.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/organisationsform.class.php');
require_once('../../include/sprache.class.php');


$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$method=(isset($_GET['method'])?$_GET['method']:'');

if(!$rechte->isBerechtigt('lehre/studienordnung'))
	die('Sie haben keine Berechtigung für diese Seite');

switch($method)
{
	case 'neueStudienordnung':
		if(!isset($_GET['studiengang_kz']))
			die('Bitte zuerst einen Studiengang auswählen');

		$studiengang_kz = $_GET['studiengang_kz'];

		$studiengang = new studiengang();
		if(!$studiengang->load($studiengang_kz))
			die('Studiengang ist ungültig');

		echo '
		<table>
			<tr>
				<td>Bezeichnung:</td>
				<td><input type="text" id="bezeichnung" maxlenght="512" /></td>
			</tr>
			<tr>
				<td>Version:</td>
				<td><input type="text" id="version" maxlenght="256" /></td>
			</tr>
			<tr>
				<td>Gültig von:</td>
				<td><select id="gueltigvon">
				<option value="" >-- keine Auswahl --</option>';
		$studiensemester = new studiensemester();
		$studiensemester->getAll();
		foreach($studiensemester->studiensemester as $row)
		{
			echo '<option value="'.$row->studiensemester_kurzbz.'">'.$row->studiensemester_kurzbz.'</option>';
		}
		echo '
				</select></td>
			</tr>
			<tr>
				<td>Gültig bis:</td>
				<td><select id="gueltigvon">
				<option value="" >-- keine Auswahl --</option>';
		foreach($studiensemester->studiensemester as $row)
		{
			echo '<option value="'.$row->studiensemester_kurzbz.'">'.$row->studiensemester_kurzbz.'</option>';
		}
		echo '
				</select></td>
			</tr>
			<tr>
				<td>ECTS:</td>
				<td><input type="text" id="ects" maxlength="5" size="5" value="'.($studiengang->max_semester*30).'"/></td>
			</tr>
			<tr>
				<td>Studiengangsbezeichnung:</td>
				<td><input type="text" id="studiengangbezeichnung" maxlength="256" value="'.$studiengang->bezeichnung.'" /></td>
			</tr>
			<tr>
				<td>Studiengangsbezeichnung Englisch:</td>
				<td><input type="text" id="studiengangbezeichnungenglisch" value="'.$studiengang->english.'" maxlength="256" /></td>
			</tr>
			<tr>
				<td>Kurzbezeichnung des Studiengangs:</td>
				<td><input type="text" id="studiengangkurzbzlang" maxlength="8" size="8" value="'.$studiengang->kurzbzlang.'" /></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="button" value="Anlegen" onclick="saveStudienordnung()"/></td>
			</tr>

		</table>
		';
		break;

	case 'neuerStudienplan':
		if(!isset($_GET['studiengang_kz']))
			die('Bitte zuerst einen Studiengang auswählen');

		$studiengang_kz = $_GET['studiengang_kz'];

		$studiengang = new studiengang();
		if(!$studiengang->load($studiengang_kz))
			die('Studiengang ist ungültig');

		echo '
		<table>
			<tr>
				<td>Bezeichnung:</td>
				<td><input type="text" id="bezeichnung" maxlenght="256" /></td>
			</tr>
			<tr>
				<td>Version:</td>
				<td><input type="text" id="version" maxlenght="256" /></td>
			</tr>
			<tr>
				<td>Organisationsform:</td>
				<td><select id="orgform_kurzbz">
				<option value="" >-- keine Auswahl --</option>';
		$orgform = new organisationsform();
		$orgform->getAll();
		foreach($orgform->result as $row)
		{
			echo '<option value="'.$row->orgform_kurzbz.'">'.$row->bezeichnung.'</option>';
		}
		echo '
				</select></td>
			</tr>
			<tr>
				<td>Regelstudiendauer:</td>
				<td><input type="text" id="regelstudiendauer" maxlength="2" size="2" value="'.($studiengang->max_semester).'"/></td>
			</tr>
			<tr>
				<td>Sprache</td>
				<td><select id="sprache">
				<option value="" >-- keine Auswahl --</option>';
		$sprache = new sprache();
		$sprache->getAll();
		foreach($sprache->result as $row)
		{
			echo '<option value="'.$row->sprache.'">'.$row->bezeichnung_arr[DEFAULT_LANGUAGE].'</option>';
		}
		echo '
				</select></td>
			<tr>
				<td></td>
				<td><input type="button" value="Anlegen" onclick="saveStudienplan()" /></td>
			</tr>

		</table>
		';
		break;
	default:
		echo 'Unknown Method'.$method;
		break;
}
?>
