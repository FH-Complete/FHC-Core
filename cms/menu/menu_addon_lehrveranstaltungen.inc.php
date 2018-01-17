<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Dieses Addon erstellt ein Formular zur Auswahl von Studiengang und Semester und zeigt die
 * zugehoerigen LVs an
 *
 * Parameter fuer das Params Array:
 * - studiengang_kz
 * - semester
 * - studiengang_kurzbz_lo 3-stelliges Studiengangskuerzel kleingeschrieben
 * - studiengang_kurzbz_hi 3-stelliges Studiengangskuerzel grossgeschrieben
 */
require_once(dirname(__FILE__).'/menu_addon.class.php');
require_once(dirname(__FILE__).'/../../config/cis.config.inc.php');
require_once(dirname(__FILE__).'/../../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../../include/lehrveranstaltung.class.php');
require_once(dirname(__FILE__).'/../../include/organisationsform.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../include/student.class.php');

class menu_addon_lehrveranstaltungen extends menu_addon
{
	public function __construct()
	{
		global $params;

		parent::__construct();

		$this->link=false;

		$sprache = getSprache();
		$user = get_uid();
		$student = new student();
		if($student->load($user))
		{
			$studiengang_kz=$student->studiengang_kz;
			$semester=$student->semester;
		}

		$p = new phrasen($sprache);

		if (defined("CIS_LVMENUE_CUTLENGTH"))
			$cutlength = CIS_LVMENUE_CUTLENGTH;
		else
			$cutlength = 21;

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
		<table class="tabcontent">
			<tr>
				<td width="81" class="tdwrap">'.$p->t('global/studiengang').': </td>
				<td class="tdwrap">
					<select name="course" onChange="MM_jumpMenu(\'self\',this,0)" style="width: 100px;">';

		$stg_obj = new studiengang();
		$stg_obj->getAll('typ, kurzbz');

		if(isset($params['studiengang_kz']) && is_numeric($params['studiengang_kz']))
			$studiengang_kz=$params['studiengang_kz'];

		if(isset($params['semester']) && is_numeric($params['semester']))
			$semester=$params['semester'];
		else
		{
			if(!isset($semester))
				$semester=1;
		}

		$sel_kurzbzlang='';
		foreach($stg_obj->result as $row)
		{
			if($row->studiengang_kz!=0)
			{
				if(isset($studiengang_kz) AND $studiengang_kz == $row->studiengang_kz)
				{
					$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$row->studiengang_kz.'&semester='.$semester.'" selected>'.$row->kuerzel .' ('.$row->bezeichnung.')</option>';
					$sel_kurzbzlang=$row->kurzbzlang;
				}
				else
				{
					$this->block.='<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$row->studiengang_kz.'&semester='.$semester.'">'.$row->kuerzel .' ('.$row->bezeichnung.')</option>';
				}
				if(!isset($studiengang_kz))
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
			  	<td class="tdwrap">&nbsp;</td>
			  </tr>
			  <tr>
			  	<td class="tdwrap">'.$p->t('global/semester').': </td>
			  	<td class="tdwrap">
			  	<select name="term" onChange="MM_jumpMenu(\'self\',this,0)">';

		$short = 'Fehler Stg.Kz '.$studiengang_kz;
		$max = 1;
		if ($stg_obj=new studiengang($studiengang_kz))
		{
			$short = $stg_obj->kuerzel;
		    $max = $stg_obj->max_semester;
		}
		if($semester>$max)
			$semester=1;

		$params['studiengang_kz'] = $studiengang_kz;
		$params['semester'] = $semester;
		$params['studiengang_kurzbz_lo'] = strtolower($short);
		$params['studiengang_kurzbz_hi'] = $short;

		for($i=0;$i<$max;$i++)
		{
			if(($i+1)==$semester)
				$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$studiengang_kz.'&semester='.($i+1).'" selected >'.($i+1).'. Semester</option>';
			else
				$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$studiengang_kz.'&semester='.($i+1).'">'.($i+1).'. Semester</option>';
		}

		$this->block.='
			  	</select>
			  	</td>
			  </tr>
			</table>
		<table>';
		$this->block.= '<script language="JavaScript" type="text/javascript">';
		$this->block.= '	parent.content.location.href="../cms/news.php?studiengang_kz='.$studiengang_kz.'&semester='.$semester.'"';
		$this->block.= '</script>';
		$this->block.='
		<tr>
		  <td class="tdwrap">&nbsp;</td>
		</tr>';


		if (!$lv_obj = new lehrveranstaltung())
			die('Fehler beim Oeffnen der Lehrveranstaltung');

		$lv_obj->lehrveranstaltungen=array();
		if ($lv_obj->load_lva($studiengang_kz,$semester,null,TRUE,TRUE,'orgform_kurzbz DESC, bezeichnung'))
		{
			$db = new basis_db();
			$qry = "SELECT * FROM lehre.tbl_studienordnung WHERE studiengang_kz=".$this->db_add_param($studiengang_kz)." AND status_kurzbz='approved'";
			$genehmigte_sto_vorhanden=false;
			if($result_sto = $db->db_query($qry))
			{
				if($db->db_num_rows($result_sto)>0)
				{
					$genehmigte_sto_vorhanden=true;
				}
			}

			$lastform=null;
			foreach ($lv_obj->lehrveranstaltungen as $row)
			{
				// Alle LVs herausfiltern die nicht in genehmigten Studienplaenen vorkommen
				// Module werden auch herausgefiltert
				if($genehmigte_sto_vorhanden)
				{
					$qry = "SELECT
								count(*) as anzahl
							FROM
								lehre.tbl_studienplan_lehrveranstaltung
								JOIN lehre.tbl_studienplan USING(studienplan_id)
								JOIN lehre.tbl_studienordnung USING(studienordnung_id)
								JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
								JOIN lehre.tbl_lehrtyp USING(lehrtyp_kurzbz)
							WHERE
								tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($row->lehrveranstaltung_id)."
								AND tbl_studienordnung.status_kurzbz='approved'
								AND lehrtyp_kurzbz='lv'";
					if($result_genehmigt = $db->db_query($qry))
					{
						if($row_genehmigt = $db->db_fetch_object($result_genehmigt))
						{
							if($row_genehmigt->anzahl==0)
								continue;
						}
					}
				}
				else
				{
					// Wenn es in diesem Studiengang keine genehmigte Studienordnung gibt dann
					// alle LVs anzeigen und nur die Module herausfiltern
					if($row->lehrtyp_kurzbz!='lv')
						continue;
				}

				if($row->orgform_kurzbz!=$lastform)
				{
					$orgform = new organisationsform();
					$orgform->load($row->orgform_kurzbz);

					$this->block.= "<tr><td><b>$orgform->bezeichnung</b></td></tr>";

					$lastform=$row->orgform_kurzbz;
				}
				$this->block.= '<tr>';
				$this->block.= '	<td class="tdwrap"><ul style="margin: 0px; padding: 0px; ">';
				$this->block.= "<li style='padding: 0px;'><a title=\"".$row->bezeichnung_arr[$sprache]."\" href=\"private/lehre/lesson.php?lvid=$row->lehrveranstaltung_id\" target=\"content\">".CutString($row->bezeichnung_arr[$sprache], $cutlength, '...').' '.$row->lehrform_kurzbz."</a></li>";
				$this->block.= '	</ul></td>';
				$this->block.= '</tr>';
			}
		}
		$this->block.='</table>';
		$this->output();
	}
}

new menu_addon_lehrveranstaltungen();
?>
