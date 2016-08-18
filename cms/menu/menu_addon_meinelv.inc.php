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
 * Menue Addon zur Anzeige der zugeordneten LVs
 *
 * Zeigt eine Liste mit den LVs zu denen der Lektor oder Student zugeordnet ist.
 */
require_once(dirname(__FILE__).'/menu_addon.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../../include/lehrveranstaltung.class.php');

class menu_addon_meinelv extends menu_addon
{
	public function __construct()
	{
		parent::__construct();

		$sprache = getSprache();
		$user = get_uid();

		$is_lector=check_lektor($user);

		$p = new phrasen($sprache);
		$cutlength=21;

		//Meine LVs Student
		if(!$is_lector)
		{
			$studiengang_obj = new studiengang();
			$studiengang_obj->getAll();

			if ($stsemobj = new studiensemester())
			{
				$stsem_arr=array();
				if(!$stsemobj->getakt())
				{
					$stsem_arr[]=$stsemobj->getNearest();
					$stsem_arr[]=$stsemobj->getNearestFrom($stsem_arr[0]);
				}
				else
				{
					$stsem_arr[] = $stsemobj->getNearest();
				}
				$qry = "SELECT distinct lehrveranstaltung_id, bezeichnung, studiengang_kz, semester, lehre,
							lehreverzeichnis from campus.vw_student_lehrveranstaltung
						WHERE uid=".$this->db_add_param($user)." AND studiensemester_kurzbz in(".$this->db_implode4SQL($stsem_arr).")
						AND lehre=true AND lehreverzeichnis<>'' ORDER BY studiengang_kz, semester, bezeichnung";
				if($result = $this->db_query($qry))
				{
					while($row = $this->db_fetch_object($result))
					{
						$lv_obj = new lehrveranstaltung();
						$lv_obj->load($row->lehrveranstaltung_id);

						if($row->studiengang_kz==0 && $row->semester==0) // Freifach
						{
							$this->items[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
							 'target'=>'content',
							 'link'=>'private/freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id,
							 'name'=>'FF '.$this->CutString($lv_obj->bezeichnung_arr[$sprache], $cutlength)
							);
						}
						else
						{
							$this->items[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
							 'target'=>'content',
							 'link'=>'private/lehre/lesson.php?lvid='.$row->lehrveranstaltung_id,
							 'name'=>$studiengang_obj->kuerzel_arr[$row->studiengang_kz].$row->semester.' '.$this->CutString($lv_obj->bezeichnung_arr[$sprache], $cutlength)
							);
						}
					}
				}
				else
					echo "Fehler beim Auslesen der LV";
			}
			else
			{
				echo "Fehler Semester beim Auslesen der LV";
			}
		}

		//Eigenen LV des eingeloggten Lektors anzeigen
		if($is_lector)
		{
			if ($stsemobj = new studiensemester())
			{
				$stsem_arr=array();
				if(!$stsemobj->getakt())
				{
					$stsem_arr[]=$stsemobj->getNearest();
					$stsem_arr[]=$stsemobj->getNearestFrom($stsem_arr[0]);
				}
				else
				{
					$stsem_arr[] = $stsemobj->getNearest();
				}
				$qry = "SELECT distinct bezeichnung, studiengang_kz, semester, lehreverzeichnis, tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.orgform_kurzbz  FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter
				        WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				        tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
				        mitarbeiter_uid=".$this->db_add_param($user)." AND tbl_lehreinheit.studiensemester_kurzbz in(".$this->db_implode4SQL($stsem_arr).")
				        ORDER BY studiengang_kz, semester, bezeichnung";

				if($result = $this->db_query($qry))
				{
					$this->items[] = array('title'=>$p->t("lvaliste/titel"),
							'target'=>'content',
							'link'=>'private/profile/lva_liste.php',
							'name'=>$p->t("lvaliste/titel"));

					while($row = $this->db_fetch_object($result))
					{
						$lv_obj = new lehrveranstaltung();
						$lv_obj->load($row->lehrveranstaltung_id);

						if($row->studiengang_kz==0 AND $row->semester==0)
						{
							$this->items[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
							 'target'=>'content',
							 'link'=>'private/freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id,
							 'name'=>'FF '.$this->CutString($row->lehreverzeichnis, $cutlength)
							);
						}
						else
						{
							$stg_obj = new studiengang();
							$stg_obj->load($row->studiengang_kz);
							$kurzbz = $stg_obj->kuerzel.'-'.$row->semester.' '.$row->orgform_kurzbz;

							$this->items[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
							 'target'=>'content',
							 'link'=>'private/lehre/lesson.php?lvid='.$row->lehrveranstaltung_id,
							 'name'=>$kurzbz.' '.$this->CutString($lv_obj->bezeichnung_arr[$sprache], $cutlength)
							);
						}
					}
				}
				else
					echo "Fehler beim Auslesen des Lehrfaches";
			}
			else
			{
				echo "Fehler Semester beim Auslesen der LV";
			}
		}
		$this->output();
	}

	private function CutString($strVal, $limit)
	{
		if(mb_strlen($strVal) > $limit+3)
		{
			return mb_substr($strVal, 0, $limit) . "...";
		}
		else
		{
			return $strVal;
		}
	}
}

new menu_addon_meinelv();
