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
require_once('../../include/standort.class.php');

$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$method=(isset($_GET['method'])?$_GET['method']:'');

if((!$rechte->isBerechtigt('lehre/studienordnung')) && (!$rechte->isBerechtigt('lehre/studienordnungInaktiv')))
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
				<td><input type="text" id="studiengangbezeichnung" maxlength="256" value="'.($new?$studiengang->bezeichnung:$studienordnung->studiengangbezeichnung).'" /></td>
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
			echo '<option value="'.$row->akadgrad_id.'"'.$selected.'>'.$row->studiengang_kz.' - '.$row->akadgrad_kurzbz.' - '.$row->titel.'</option>';
		}
		echo '
					</select>
			</tr>

			<tr>
				<td>Status:</td>
				<td><select id="studienordnung_status">
				<option value="">--keine Auswahl--</option>';
		$studienordnungstatus = new studienordnung();
		$studienordnungstatus->getstatus();
		foreach($studienordnungstatus->result as $row_status)
		{
			if($row_status->status_kurzbz==$studienordnung->status_kurzbz || ($new && $row_status->status_kurzbz == "development"))
				$selected = 'selected';
			else
				$selected = '';
			echo '<option value="'.$db->convert_html_chars($row_status->status_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($row_status->bezeichnung).'</option>';
		}
		echo '

				</select></td>
			</tr>
			<tr>
				<td>Standort</td>
				<td><select id="standort_id">
				<option value="">--keine Auswahl--</option>';
		$standort = new standort();
		$standort->getStandorteWithTyp('Intern');

		foreach($standort->result as $row_standort)
		{
			if($row_standort->standort_id == $studienordnung->standort_id)
				$selected = 'selected';
			else
				$selected = '';
			echo '<option value="'.$db->convert_html_chars($row_standort->standort_id).'" '.$selected.'>'.$db->convert_html_chars($row_standort->bezeichnung).'</option>';
		}
		echo '
				</select>
			</tr>

			<tr>
				<td><span id="submsg" style="color:green; visibility:hidden;">Daten gespeichert</span></td>
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
				<td>ECTS gesamt</td>
				<td><input type="text"  size="6" id="ects_stpl" value="'.$studienplan->ects_stpl.'" /></td>
			</tr>
			<tr>
				<td>Pflicht SWS</td>
				<td><input type="text" size="3" id="pflicht_sws" value="'.$studienplan->pflicht_sws.'" /></td>
			</tr>
			<tr>
				<td>Pflicht LVS</td>
				<td><input type="text"  size="3" id="pflicht_lvs" value="'.$studienplan->pflicht_lvs.'" /></td>
			</tr>
			<tr>
				<td>Onlinebewerbung:</td>';
				if($studienplan->onlinebewerbung_studienplan)
					$checked=' checked="checked"';
				else
					$checked='';
				echo '
				<td><input type="checkbox" id="onlinebewerbung_studienplan"'.($new?' checked="checked"':$checked).'/></td>
			</tr>
			<tr>
				<td><span id="submsg" style="color:green; visibility:hidden;">Daten gespeichert</span></td>
				<td><input type="button" value="Speichern" onclick="saveStudienplan()" /></td>
			</tr>

		</table>
		';
		break;

    case 'semesterSTPLZuordnung':
		$studienplan_id = $_GET["studienplan_id"];

		$studienplan = new studienplan();
		$studienplan->loadStudienplan($studienplan_id);
		$studienSemesterResult = $studienplan->loadStudiensemesterFromStudienplan($studienplan_id);

		$studiensemester = new studiensemester();
		$studiensemester->getAll('desc');
		$studiensemester_array = array();
		foreach ($studiensemester->studiensemester AS $row)
			$studiensemester_array[$row->studiensemester_kurzbz] = false;

		$ausbildungssemesterResult = array();
/*
		$studienSemesterResult = $studienordnung->loadStudiensemesterFromStudienordnung($studienordnung_id);*/
		foreach ($studienSemesterResult as $studienSem)
		{
			$obj = new stdClass();
			$obj->studiensemester = $studienSem;
			$obj->ausbildungssemester = $studienplan->loadAusbildungsemesterFromStudiensemester($studienplan_id, $studienSem);
			$ausbildungssemesterResult[] = $obj;
		}

		$studiengang = new studiengang();
		//$studiengang->load($studienordnung->studiengang_kz);
//		$ausbildungssemester = $studiengang->getSemesterFromStudiengang($studienordnung->studiengang_kz)
		$ausbildungssemester = $studienplan->regelstudiendauer;

		echo '
			<table width="100%" rules="rows">
				<thead>
					<tr>
						<th style="font-size: 1.1em;">Studiensemester</th>
						';
						for($i = 1; $i<=$ausbildungssemester; $i++)
						{
							echo '<th style="font-size: 1.1em">'.$i.". Semester</th>";
						}
					echo '<th>&nbsp;</th>';
		echo '</tr>
				</thead>
				<tbody>';


		foreach($ausbildungssemesterResult as $row)
		{
			if (array_key_exists($row->studiensemester, $studiensemester_array))
				$studiensemester_array[$row->studiensemester] = true;

			echo '<tr id="row_'.$row->studiensemester.'" style="font-size: 1em !important;"><td style="font-size: 1em; padding: 0.5em 0.5em 0.5em 0.5em;" align="center">'.$row->studiensemester.'</td>';
			for($i = 1; $i<=$ausbildungssemester; $i++)
			{
				if(in_array($i, $row->ausbildungssemester))
				{
					echo '<td style="font-size: 1.2em; color: green;" align="center"><a href="#" onclick="javascript:deleteSemesterSTPLZuordnung(\''.$row->studiensemester.'\',\''.$i.'\')"><img id='.$row->studiensemester.$i.' width="30px" src="../../skin/images/true.png"></a></td>';
				}
				else
				{
					echo '<td style="font-size: 1em; color: red;" align="center"><a href="#" onclick="javascript:saveSemesterSTPLZuordnung(\''.$row->studiensemester.'\', \''.$i.'\');"><img width="20px" src="../../skin/images/false.png"></a></td>';
				}
			}
			echo '<td><a href="#" onclick="javascript:deleteSemesterSTPLZuordnung(\''.$row->studiensemester.'\');">Löschen</a></td></tr>';
		}
		echo '<tr>
				<td align="center"><select id="studiensemester">';
				$lastStudiensemesterActive = '';
				$selected = '';
				//Das nächste Studiensemester wird selected
				foreach($studiensemester_array AS $key => $value)
				{
					if ($value == true)
					{
						echo '<option value='.$key.' disabled="disabled">'.$key.'</option>';
						$lastStudiensemesterActive = false;
					}
					else
					{
						if ($selected == '' && $lastStudiensemesterActive !== false)
							$selected = 'selected';
						echo '<option value='.$key.' '.$selected.'>'.$key.'</option>';
					}
					$selected = '';
				}
		echo '</select></td>';
		for($j=1; $j<=$ausbildungssemester; $j++)
		{
			echo '<td align="center"><input type="checkbox" semester='.$j.'></td>';
		}
		echo '
			<td><input style="margin: 0.5em 0 0.5em 0" type="button" value="Zuordnen" onclick="javascript:saveSemesterSTPLZuordnung();"></td>
			</tr>
			<tr>
			</tr>
			';
		echo '</tbody>
			</table>
			';
		break;

	default:
		echo 'Unknown Method'.$method;
		break;
}
?>
