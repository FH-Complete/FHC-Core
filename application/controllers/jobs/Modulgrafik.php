<?php
/* Copyright (C) 2024 fhcomplete.net
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
 */

if (!defined("BASEPATH")) exit("No direct script access allowed");

class Modulgrafik extends JOB_Controller
{
	const DEFAULT_XOFFSET = 40;
	const DEFAULT_YOFFSET = 30;
	const DEFAULT_WIDTH	  = 160;
	const DEFAULT_SEMESTER_LABEL_WIDTH = 80;
	const DEFAULT_ECTS_WIDTH = 40;
	const DEFAULT_HEIGHT  = 50;
	const DEFAULT_SPACING = 40;
	const DEFAULT_FONTSIZE = 8;

	protected $curx;
	protected $cury;

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$this->load->model('CL/Drawio_model', 'DrawioModel');
		
		$this->load->library('DocsboxLib', null, 'DocsboxLib');
	}

	public function drawModulGrafik($studienplan_id)
	{
		$sql = <<<EOSQL
		SELECT 
			sl.studienplan_lehrveranstaltung_id, 
			sl.lehrveranstaltung_id, 
			sl.studienplan_lehrveranstaltung_id_parent, 
			COALESCE(sl.studienplan_lehrveranstaltung_id_parent, sl.studienplan_lehrveranstaltung_id) AS lvgrp, 
			sl.semester, 
			lv.bezeichnung, 
			lv.ects, 
			lv.lehrtyp_kurzbz 
		FROM 
			lehre.tbl_studienplan_lehrveranstaltung sl 
		JOIN 
			lehre.tbl_lehrveranstaltung lv USING(lehrveranstaltung_id) 
		WHERE 
			sl.studienplan_id = {$studienplan_id} 
			AND 
			ects > '0.00' 
		ORDER BY 
			sl.semester ASC, 
			lvgrp ASC, 
			lv.lehrtyp_kurzbz DESC
EOSQL;
		
		$result = $this->StudienplanModel->execReadOnlyQuery($sql);
		$lvs = getData($result);

		$errors = array();
		$assoclvs = array();
		$semester = array();
		if($lvs)
		{
			ob_start();

			foreach($lvs as &$lv) 
			{
				if( !isset($semester[$lv->semester]) ) {
				$semester[$lv->semester] = array();
				}
				$lv->parent = null;
				$lv->childs = array();
				$assoclvs[$lv->studienplan_lehrveranstaltung_id] = $lv;
			}
			
			foreach($assoclvs as &$assoclv)
			{
				if( $assoclv->studienplan_lehrveranstaltung_id_parent === NULL )
				{
					$semester[$assoclv->semester][] = $assoclv;
				}
				else
				{
					if( isset($assoclvs[$assoclv->studienplan_lehrveranstaltung_id_parent]) ) 
					{
						$assoclv->parent = $assoclvs[$assoclv->studienplan_lehrveranstaltung_id_parent];
						$assoclvs[$assoclv->studienplan_lehrveranstaltung_id_parent]->childs[] = $assoclv;
					}
					else
					{
						$errors[] = "ERROR: Missing parent lv " . $assoclv->studienplan_lehrveranstaltung_id_parent . "\n";
					}
				}
			}

			function printchilds($childs, $level) {
				if( count($childs) < 1 )
				{
					return;
				}

				foreach($childs as $child)
				{
					echo $level . $child->bezeichnung . " (" . $child->ects . ") "  . $child->lehrtyp_kurzbz . "\n";
					printchilds($child->childs, $level . "\t");
				}
			}
			
			echo "<!--\n\n";

			foreach($semester as $sem => $mods)
			{
				echo $sem . ". Semester: \n";
				foreach($mods as $mod)
				{
					$level = "\t";
					echo $level . $mod->bezeichnung . " (" . $mod->ects . ") " . $mod->lehrtyp_kurzbz . "\n";
					printchilds($mod->childs, $level . "\t");
				}
			}
			echo "-->\n\n";
			
			if (count($errors) === 0)
			{
				$this->DrawioModel->renderFileStart();
				$this->maxxoffset = self::DEFAULT_XOFFSET;
				$this->DrawioModel->renderDiagramStart($studienplan_id, 'Modulgrafik');
				$this->renderSemester($semester);
				$this->DrawioModel->renderDiagramEnd();
				$this->DrawioModel->renderFileEnd();
			}
			else 
			{
				foreach ($errors as $error)
				{
					echo $error;
				}
			}

			$output = ob_get_clean();
			file_put_contents(sys_get_temp_dir().'/Modulgrafik.xml', $output);

			$ret = DocsboxLib::convert(sys_get_temp_dir().'/Modulgrafik.xml', sys_get_temp_dir().'/Modulgrafik.png', 'png');

			echo 'File: '.sys_get_temp_dir().'/Modulgrafik.png'."\n";
		}
		else 
		{
			echo "Keine LVs gefunden.\n";
		}
	}
	
	protected function renderSemester($semester)
	{
		$cury = self::DEFAULT_YOFFSET;
		
		foreach($semester as $sem => $mods)
		{
			$curx = self::DEFAULT_XOFFSET;
			$id = uniqid();
			$ects = 30; //TODO calc
			$this->DrawioModel->renderSemesterLabel($id, $sem, $ects, self::DEFAULT_XOFFSET, $cury, self::DEFAULT_SEMESTER_LABEL_WIDTH, self::DEFAULT_HEIGHT);
			$curx += self::DEFAULT_SEMESTER_LABEL_WIDTH + self::DEFAULT_SPACING;
			$maxmodulheight = 0;
			foreach($mods as $mod)
			{
				$modid = uniqid();
				$size = $this->DrawioModel->renderModulList($modid, $mod, $curx, $cury, self::DEFAULT_ECTS_WIDTH, self::DEFAULT_HEIGHT);
				$curx += $size->width + self::DEFAULT_SPACING;
				if( $size->height > $maxmodulheight)
				{
				$maxmodulheight = $size->height;
				}
			}
			$cury += $maxmodulheight + self::DEFAULT_SPACING;
		}
	}
}

