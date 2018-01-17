<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Menue Addon zur Auswahl von LVs
 *
 * Dieses Addon erstellt ein Formular zur Auswahl von
 * Studiensemester, Studiengang, Ausbildungssemester, Studienplan und zeigt die
 * zugehoerigen LVs an
 *
 * Parameter fuer das Params Array:
 * - studiengang_kz
 * - semester
 * - studiensemester_kurzbz
 * - studienplan_id
 * - studiengang_kurzbz_lo 3-stelliges Studiengangskuerzel kleingeschrieben
 * - studiengang_kurzbz_hi 3-stelliges Studiengangskuerzel grossgeschrieben
 */
require_once(dirname(__FILE__).'/menu_addon.class.php');
require_once(dirname(__FILE__).'/../../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../../include/studienordnung.class.php');
require_once(dirname(__FILE__).'/../../include/studienplan.class.php');
require_once(dirname(__FILE__).'/../../include/lehrveranstaltung.class.php');
require_once(dirname(__FILE__).'/../../include/organisationsform.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../include/student.class.php');

class menu_addon_lehrveranstaltungen_studienplan extends menu_addon
{
	public function __construct()
	{
		global $params;

		parent::__construct();

		$this->link=false;

		$sprache = getSprache();
		$user = get_uid();
		$student = new student();
		$studiengang_kz='';
		$semester='';
		$studienplan_id='';
		$studienordnung_id='';
		$db = new basis_db();

		if($student->load($user))
		{
			$studiengang_kz=$student->studiengang_kz;
			$semester=$student->semester;
		}

		$p = new phrasen($sprache);

		$sprachen_obj = new sprache();
		$sprachen_obj->getAll();
		$sprachen_arr=array();
		$sprachen_arr['']='';
		foreach($sprachen_obj->result as $row)
		{
			if(isset($row->bezeichnung_arr[$sprache]))
				$sprachen_arr[$row->sprache]=$row->bezeichnung_arr[$sprache];
			else
				$sprachen_arr[$row->sprache]=$row->sprache;
		}

		$orgform_obj = new organisationsform();
		$orgform_obj->getAll();
		$orgform_arr=array();
		$orgform_arr['']='';
		foreach($orgform_obj->result as $row)
			$orgform_arr[$row->orgform_kurzbz]=$row->bezeichnung;


		$stsem = new studiensemester();
		$studiensemester_kurzbz=$stsem->getaktornext();

		if(isset($params['studiensemester_kurzbz']))
			$studiensemester_kurzbz=$params['studiensemester_kurzbz'];

		if(isset($params['studiengang_kz']) && is_numeric($params['studiengang_kz']))
			$studiengang_kz=$params['studiengang_kz'];

		if(isset($params['semester']) && is_numeric($params['semester']))
			$semester=$params['semester'];
		else
		{
			if(!isset($semester))
				$semester=1;
		}
		if(isset($params['studienplan_id']))
			$studienplan_id=$params['studienplan_id'];

		$this->block.='
			<script language="JavaScript" type="text/javascript">
			<!--
				function MM_jumpMenu(targ, selObj, restore)
				{
				  eval(targ + ".location=\'" + selObj.options[selObj.selectedIndex].value + "\'");

				  if(restore)
				  {
				  	selObj.selectedIndex = 0;
				  }
				}
			  //-->
			</script>';

		$this->block.='
		<table class="tabcontent">';

		// Studiensemester

		$this->block.='
			<tr>
				<td class="tdwrap">
				Studiensemester<br>
					<select name="stsem" onChange="MM_jumpMenu(\'self\',this,0)" style="width:100%">';

		//Anzeigen des DropDown Menues mit Studiensemester
		$studiensemester = new studiensemester();
		$akt_studiensemester = $studiensemester->getakt();
		if($studiensemester->getPlusMinus(5,10))
		{
			foreach($studiensemester->studiensemester as $row)
			{
				$selected = '';
				if($row->studiensemester_kurzbz==$studiensemester_kurzbz)
					$selected = 'selected';
				elseif ($studiensemester_kurzbz=='' && $row->studiensemester_kurzbz==$akt_studiensemester)
				{
					$selected = 'selected';
					$studiensemester_kurzbz=$akt_studiensemester;
				}

				$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$studiengang_kz.'&semester='.$semester.'&studiensemester_kurzbz='.$row->studiensemester_kurzbz.'" '.$selected.'>'.$row->bezeichnung.'</option>';
			}
			$this->block.= '</SELECT>';
		}
		else

			$errormsg .= $studiensemester->errormsg;
		$this->block.='
			</td>
		</tr>';

		// Studiengang
		$this->block.='
			<tr>
				<td class="nowrap">
  			  Studiengang<br>
  			  		<select name="course" onChange="MM_jumpMenu(\'self\',this,0)" style="width:100%">';

		$stg_obj = new studiengang();
		$stg_obj->loadStudiengangFromStudiensemester($studiensemester_kurzbz);

		if(isset($params['studienplan_id']) && is_numeric($params['studienplan_id']))
			$studienplan_id=$params['studienplan_id'];

		$sel_kurzbzlang='';
		foreach($stg_obj->result as $row)
		{
			if($row->studiengang_kz!=0)
			{
				if(isset($studiengang_kz) AND $studiengang_kz == $row->studiengang_kz)
				{
					$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$row->studiengang_kz.'&semester='.$semester.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'" selected>'.$row->kuerzel .' ('.$row->bezeichnung.')</option>';
					$sel_kurzbzlang=$row->kurzbzlang;
				}
				else
				{
					$this->block.='<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$row->studiengang_kz.'&semester='.$semester.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'">'.$row->kuerzel .' ('.$row->bezeichnung.')</option>';
				}
				if(!isset($studiengang_kz) || $studiengang_kz=='')
				{
					$studiengang_kz=$row->studiengang_kz;
				}
			}
		}

		$this->block.='
			  	</select>
			  	</td>
			  </tr>
			  <tr>
			  <td class="nowrap">
			  Semester<br>
			  	<select name="term" onChange="MM_jumpMenu(\'self\',this,0)" style="width:100%">';

		$vorhandenesemester=array();

		$studienplan_obj = new studienplan();
		$studienplan_obj->getStudienplaeneFromSem($studiengang_kz, $studiensemester_kurzbz);
		foreach($studienplan_obj->result as $row_sto)
			$vorhandenesemester[]=$row_sto->semester;

		if(!in_array($semester, $vorhandenesemester))
			$semester='';
		$vorhandenesemester = array_unique($vorhandenesemester);
		sort($vorhandenesemester);

		$studiengang_obj = new studiengang();
		$studiengang_obj->load($studiengang_kz);
		$short = $studiengang_obj->kuerzel;

		$params['studiengang_kz'] = $studiengang_kz;
		$params['semester'] = $semester;
		$params['studiengang_kurzbz_lo'] = strtolower($short);
		$params['studiengang_kurzbz_hi'] = $short;

		foreach($vorhandenesemester as $i)
		{
			if($semester=='')
				$semester=$i;
			if($i==$semester)
				$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$studiengang_kz.'&semester='.$i.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'" selected >'.$i.'. Semester</option>';
			else
				$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$studiengang_kz.'&semester='.$i.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'">'.$i.'. Semester</option>';
		}

		$this->block.='
			  	</select>
			  	</td>
			  </tr>
			  <tr>
			  <td class="nowrap">
			  Studienplan<br>
			  <select name="studienplan_id" onChange="MM_jumpMenu(\'self\',this,0)" style="width:100%">';

		// Studienplan
		$studienplan_obj = new studienplan();
		$studienplan_obj->getStudienplaeneFromSem($studiengang_kz, $studiensemester_kurzbz, $semester);
		$studienordnung_arr = array();
		$studienplan_arr = array();
		foreach($studienplan_obj->result as $row_sto)
		{
			$studienordnung_arr[$row_sto->studienordnung_id]['bezeichnung']=$row_sto->bezeichnung_studienordnung;
			$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['bezeichnung']=$row_sto->bezeichnung_studienplan;

			$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['orgform_kurzbz']=$row_sto->orgform_kurzbz;
			$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['sprache']=$sprachen_arr[$row_sto->sprache];
		}
		// Pruefen ob uebergebene StudienplanID in Auswahl enthalten
		// ist und ggf auf leer setzen
		if($studienplan_id!='')
		{
			$studienplan_found=false;
			foreach($studienplan_arr as $stoid=>$row_sto)
			{
				if(array_key_exists($studienplan_id, $studienplan_arr[$stoid]))
				{
					$studienplan_found=true;
					break;
				}
			}
			if(!$studienplan_found)
			{
				$studienplan_id='';
			}
		}
		foreach($studienordnung_arr as $stoid=>$row_sto)
		{
			$selected='';

			if($studienordnung_id=='')
				$studienordnung_id=$stoid;

			$this->block.='<option value="" disabled>'.$p->t('lehre/studienordnung').': '.$db->convert_html_chars($row_sto['bezeichnung']).'</option>';

			foreach($studienplan_arr[$stoid] as $stpid=>$row_stp)
			{
				$selected='';
				if($studienplan_id=='')
					$studienplan_id=$stpid;
				if($stpid == $studienplan_id)
					$selected='selected';

				$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$studiengang_kz.'&semester='.$semester.'&studiensemester_kurzbz='.$studiensemester_kurzbz.'&studienplan_id='.$stpid.'" '.$selected.'>'.$db->convert_html_chars($row_stp['bezeichnung']).' ( '.$orgform_arr[$row_stp['orgform_kurzbz']].', '.$row_stp['sprache'].' ) </option>';
			}
		}
 		$this->block.='</select></td></tr>';

		$this->block.='</table><br /><br />';

		$this->block.= '<script language="JavaScript" type="text/javascript">';
		$this->block.= '	parent.content.location.href="../cms/news.php?studiengang_kz='.$studiengang_kz.'&semester='.$semester.'"';
		$this->block.= '</script>';

		if (!$lv_obj = new lehrveranstaltung())
			die('Fehler beim Oeffnen der Lehrveranstaltung');

		$lv_obj->lehrveranstaltungen=array();
		if($lv_obj->loadLehrveranstaltungStudienplan($studienplan_id, $semester,'bezeichnung'))
		{
			$tree = $lv_obj->getLehrveranstaltungTree();
			$this->printTree($tree, $sprache, $studiensemester_kurzbz);
		}

		$this->output();
	}

	/**
	 * Stellt die Lehrveranstaltungen in einer Baumstruktur dar.
	 */
	private function printTree($tree, $sprache, $studiensemester_kurzbz)
	{
		$this->block.='<ul>';
		foreach ($tree as $row)
		{
			if(!$row->lehre)
				continue;
			if($row->lehrtyp_kurzbz=='modul')
				$bold='font-weight:bold;';
			else
				$bold='';
			if(!$row->lehrauftrag && defined('CIS_LEHRVERANSTALTUNG_MODULE_LINK') && !CIS_LEHRVERANSTALTUNG_MODULE_LINK)
				$this->block.= "<li style='display:inline-block;white-space: nowrap;padding: 0px; margin:0px; color:#b2b2b2; $bold'>".CutString($row->bezeichnung_arr[$sprache], 21, '...').' '.$row->lehrform_kurzbz."</li>";
			else
				$this->block.= "<li style='display:inline-block;white-space: nowrap;padding: 0px; margin:0px; $bold'><a title=\"".$row->bezeichnung_arr[$sprache]."\" href=\"private/lehre/lesson.php?lvid=$row->lehrveranstaltung_id&studiensemester_kurzbz=$studiensemester_kurzbz\" target=\"content\">".CutString($row->bezeichnung_arr[$sprache], 21, '...').' '.$row->lehrform_kurzbz."</a></li>";

			if(isset($row->childs))
				$this->printTree($row->childs, $sprache, $studiensemester_kurzbz);
		}
		$this->block.="</ul>";
	}
}
new menu_addon_lehrveranstaltungen_studienplan();
?>
