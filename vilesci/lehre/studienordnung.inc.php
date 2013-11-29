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
require_once('../../include/akadgrad.class.php');
require_once('../../include/lvregel.class.php');

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

		$studienordnung = new studienordnung();
		$studienordnung_id='';
		if(isset($_GET['studienordnung_id']) && $_GET['studienordnung_id']!='')
		{
			$studienordnung_id = $_GET['studienordnung_id'];
			if(!$studienordnung->loadStudienordnung($studienordnung_id))
				die('Studienordnung konnte nicht geladen werden');
			$new=false;
		}
		else
			$new=true;
		
		echo '
		<input type="hidden" id="studienordnung_id" value ="'.$studienordnung_id.'"/>
		<table>
			<tr>
				<td>Bezeichnung:</td>
				<td><input type="text" id="bezeichnung" maxlenght="512" value="'.$studienordnung->bezeichnung.'" /></td>
			</tr>
			<tr>
				<td>Version:</td>
				<td><input type="text" id="version" maxlenght="256" value="'.$studienordnung->version.'" /></td>
			</tr>
			<tr>
				<td>Gültig von:</td>
				<td><select id="gueltigvon">
				<option value="" >-- keine Auswahl --</option>';
		$studiensemester = new studiensemester();
		$studiensemester->getAll();
		foreach($studiensemester->studiensemester as $row)
		{
			if($row->studiensemester_kurzbz==$studienordnung->gueltigvon)
				$selected=' selected';
			else
				$selected='';
			echo '<option value="'.$row->studiensemester_kurzbz.'"'.$selected.'>'.$row->studiensemester_kurzbz.'</option>';
		}
		echo '
				</select></td>
			</tr>
			<tr>
				<td>Gültig bis:</td>
				<td><select id="gueltigbis">
				<option value="" >-- keine Auswahl --</option>';
		foreach($studiensemester->studiensemester as $row)
		{
			if($row->studiensemester_kurzbz==$studienordnung->gueltigbis)
				$selected=' selected';
			else
				$selected='';
			echo '<option value="'.$row->studiensemester_kurzbz.'"'.$selected.'>'.$row->studiensemester_kurzbz.'</option>';
		}
		echo '
				</select></td>
			</tr>
			<tr>
				<td>ECTS:</td>
				<td><input type="text" id="ects" maxlength="5" size="5" value="'.($new?($studiengang->max_semester*30):$studienordnung->ects).'"/></td>
			</tr>
			<tr>
				<td>Studiengangsbezeichnung:</td>
				<td><input type="text" id="studiengangbezeichnung" maxlength="256" value="'.($new?$studiengang->bezeichnung:$studienordnung->bezeichnung).'" /></td>
			</tr>
			<tr>
				<td>Studiengangsbezeichnung Englisch:</td>
				<td><input type="text" id="studiengangbezeichnungenglisch" value="'.($new?$studiengang->english:$studienordnung->studiengangbezeichnung_englisch).'" maxlength="256" /></td>
			</tr>
			<tr>
				<td>Kurzbezeichnung des Studiengangs:</td>
				<td><input type="text" id="studiengangkurzbzlang" maxlength="8" size="8" value="'.($new?$studiengang->kurzbzlang:$studienordnung->studiengangkurzbzlang).'" /></td>
			</tr>
			<tr>
				<td>Akademischer Grad</td>
				<td>
					<select id="akadgrad_id">
					';
		$akadgrad = new akadgrad();
		$akadgrad->getAll();
		foreach($akadgrad->result as $row)
		{
			if($row->akadgrad_id==$studienordnung->akadgrad_id)
				$selected=' selected';
			else
				$selected='';
			echo '<option value="'.$row->akadgrad_id.'"'.$selected.'>'.$row->akadgrad_kurzbz.'</option>';
		}
		echo '
					</select>
			</tr>
			<tr>
				<td></td>
				<td><input type="button" value="Speichern" onclick="saveStudienordnung()"/></td>
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

		$studienplan = new studienplan();
		$studienplan_id='';
		if(isset($_GET['studienplan_id']) && $_GET['studienplan_id']!='')
		{
			$studienplan_id = $_GET['studienplan_id'];
			if(!$studienplan->loadStudienplan($studienplan_id))
				die('Studienplan konnte nicht geladen werden');
			$new=false;
		}
		else
			$new=true;
		
		echo '
		<input type="hidden" id="studienplan_id" value="'.$studienplan_id.'"/>
		<table>
			<tr>
				<td>Bezeichnung:</td>
				<td><input type="text" id="bezeichnung" maxlenght="256" value="'.$studienplan->bezeichnung.'" /></td>
			</tr>
			<tr>
				<td>Version:</td>
				<td><input type="text" id="version" maxlenght="256" value="'.$studienplan->version.'" /></td>
			</tr>
			<tr>
				<td>Organisationsform:</td>
				<td><select id="orgform_kurzbz">
				<option value="" >-- keine Auswahl --</option>';
		$orgform = new organisationsform();
		$orgform->getAll();
		foreach($orgform->result as $row)
		{
			if($row->orgform_kurzbz==$studienplan->orgform_kurzbz)
				$selected=' selected';
			else
				$selected='';
			echo '<option value="'.$row->orgform_kurzbz.'"'.$selected.'>'.$row->bezeichnung.'</option>';
		}
		echo '
				</select></td>
			</tr>
			<tr>
				<td>Regelstudiendauer:</td>
				<td><input type="text" id="regelstudiendauer" maxlength="2" size="2" value="'.($new?$studiengang->max_semester:$studienplan->regelstudiendauer).'"/></td>
			</tr>
			<tr>
				<td>Sprache</td>
				<td><select id="sprache">
				<option value="" >-- keine Auswahl --</option>';
		$sprache = new sprache();
		$sprache->getAll();
		foreach($sprache->result as $row)
		{
			if($row->sprache==$studienplan->sprache)
				$selected=' selected';
			else
				$selected='';
			echo '<option value="'.$row->sprache.'"'.$selected.'>'.$row->bezeichnung_arr[DEFAULT_LANGUAGE].'</option>';
		}
		echo '
				</select></td>
			<tr>
				<td>Semesterwochen:</td>
				<td><input type="text" id="semesterwochen" maxlength="2" size="2" value="'.($new?'15':$studienplan->semesterwochen).'"/></td>
			</tr>
			<tr>
				<td>Testtool Sprachwahl:</td>';
		if($studienplan->testtool_sprachwahl)
			$checked=' checked="checked"';
		else
			$checked='';
		echo '
				<td><input type="checkbox" id="testtool_sprachwahl"'.($new?' checked="checked"':$checked).'/></td>
			</tr>
			<tr>
				<td>Aktiv:</td>';
		if($studienplan->aktiv)
			$checked=' checked="checked"';
		else
			$checked='';
		echo '
				<td><input type="checkbox" id="aktiv"'.($new?' checked="checked"':$checked).'/></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="button" value="Speichern" onclick="saveStudienplan()" /></td>
			</tr>

		</table>
		';
		break;
	case 'semesterStoZuordnung':
		$studienordnung_id = $_GET["studienordnung_id"];
		$studiensemester_kurzbz = isset($_GET["studiensemester_kurzbz"]) ? $_GET["studiensemester_kurzbz"] : "";
		$semester = isset($_GET["semester"]) ? $_GET["semester"] : "";
		
		$studienordnung = new studienordnung();
		$studienordnung->loadStudienordnung($studienordnung_id);
		
		$studiengang = new studiengang();
		$ausbildungssemester = $studiengang->getSemesterFromStudiengang($studienordnung->studiengang_kz);
		
		$studiensemester = new studiensemester();
		$studiensemester->getAll();
		echo '
			<table width="100%" rules="rows">
				<thead>
					<tr>
						<th>Studiensemester</th>
						';
						for($i = 0; $i<count($ausbildungssemester); $i++)
						{
							echo "<th>".$ausbildungssemester[$i].". Semester</th>";
						}
					echo '<th>&nbsp;</th>';
		echo '</tr>
				</thead>
				<tbody>
				';
				$length = count($studiensemester->studiensemester)-1;
				for($i = $length; $i>0; $i--)
				{
					echo '
						<tr id="'.$studiensemester->studiensemester[$i]->studiensemester_kurzbz.'">
							<td align="center">'.$studiensemester->studiensemester[$i]->studiensemester_kurzbz.'</td>
						';
						foreach($ausbildungssemester as $sem)
						{
							echo '<td align="center"><input type="checkbox" semester='.$sem.'></td>';
						}
					echo '
						<td><input style="margin: 0.5em 0 0.5em 0" type="button" value="Zuordnen" onclick="javascript:saveSemesterStoZuordnung(\''.$studiensemester->studiensemester[$i]->studiensemester_kurzbz.'\');"></td>
						</tr>
						';
				}
		echo '</tbody>
			</table>
			';
		break;
	default:
		echo 'Unknown Method'.$method;
		break;
}
?>
