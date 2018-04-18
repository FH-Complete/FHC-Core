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
 * Zeigt eine Liste mit den Lehrfächern an zu denen der Lektor oder Student zugeordnet ist.
 */
require_once(dirname(__FILE__).'/menu_addon.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../../include/lehrveranstaltung.class.php');

class menu_addon_meinelvkompatibel extends menu_addon
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
				// Angezeigt wird das Studiensemester das am naehesten ist das davor und das danach
				//cis.config.inc.php: Durch den Eintrag CIS_MEINELV_ANZAHL_SEMESTER_PAST können mehrere Semester aus der Vergangenheit angezeigt werden.
				$stsem = $stsemobj->getNearest();
				$stsem_array = array();
				array_push($stsem_array, $stsem);
				array_push($stsem_array, $stsemobj->getNextFrom($stsem));

				if(defined('CIS_MEINELV_ANZAHL_SEMESTER_PAST'))
				    $end = CIS_MEINELV_ANZAHL_SEMESTER_PAST;
				else
				    $end = 1;

				for($i=0; $i<$end; $i++)
				{
				    $stsem = $stsemobj->getPreviousFrom($stsem);
				    array_unshift($stsem_array, $stsem);
				}

				foreach($stsem_array as $stsem)
				{
					$qry = "SELECT
								lehrfach.bezeichnung, lehrfach.lehrveranstaltung_id as lehrfach_id, vw_student_lehrveranstaltung.lehrveranstaltung_id,
								vw_student_lehrveranstaltung.studiengang_kz, vw_student_lehrveranstaltung.semester
							FROM
								campus.vw_student_lehrveranstaltung
								JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(vw_student_lehrveranstaltung.lehrfach_id=lehrfach.lehrveranstaltung_id)
							WHERE
								uid=".$this->db_add_param($user)."
								AND studiensemester_kurzbz=".$this->db_add_param($stsem)."
								AND vw_student_lehrveranstaltung.lehre=true
								AND vw_student_lehrveranstaltung.lehreverzeichnis<>''
							ORDER BY
								vw_student_lehrveranstaltung.studiengang_kz, vw_student_lehrveranstaltung.semester, lehrfach.bezeichnung";

					if($result = $this->db_query($qry))
					{
						$stsementry=array();
						while($row = $this->db_fetch_object($result))
						{
							$lv_obj = new lehrveranstaltung();
							$lv_obj->load($row->lehrfach_id);

							if($row->studiengang_kz==0 && $row->semester==0) // Freifach
							{
								$stsementry[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
								 'target'=>'content',
								 'link'=>'private/freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$stsem,
								 'name'=>'FF '.CutString($lv_obj->bezeichnung_arr[$sprache], $cutlength, '...')
								);
							}
							else
							{
								$stsementry[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
								 'target'=>'content',
								 'link'=>'private/lehre/lesson.php?lvid='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$stsem,
								 'name'=>CutString($lv_obj->bezeichnung_arr[$sprache], $cutlength, '...')
								);
							}
						}
						if(count($stsementry)>0)
						{
							$this->items[] = array('title'=>$stsem,
									'target'=>'',
									'link'=>'#',
									'name'=>$stsem,
									'childs'=>$stsementry
							);
						}
					}
					else
						echo "Fehler beim Auslesen der LV";
				}
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
				// Angezeigt wird das Studiensemester das am naehesten ist das davor und das danach
				$stsem = $stsemobj->getNearest();
				$stsem_array[]=$stsemobj->getPreviousFrom($stsem);
				$stsem_array[]=$stsem;
				$stsem_array[]=$stsemobj->getNextFrom($stsem);

				$this->items[] = array('title'=>$p->t("lvaliste/titel"),
						'target'=>'content',
						'link'=>'private/profile/lva_liste.php',
						'name'=>$p->t("lvaliste/titel"));

				foreach($stsem_array as $stsem)
				{

					$qry = "SELECT
								distinct tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.studiengang_kz,
								tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.lehreverzeichnis, tbl_lehrveranstaltung.lehrveranstaltung_id,
								tbl_lehrveranstaltung.orgform_kurzbz, lehrfach.lehrveranstaltung_id as lehrfach_id, lehrfach.bezeichnung as lf_bezeichnung
							FROM
								lehre.tbl_lehrveranstaltung
								JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
								JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
								JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
					        WHERE
					        	mitarbeiter_uid=".$this->db_add_param($user)."
					        	AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($stsem)."
					        ORDER BY tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.semester, lehrfach.bezeichnung";

					if($result = $this->db_query($qry))
					{


						$stsementry=array();
						while($row = $this->db_fetch_object($result))
						{
							$lv_obj = new lehrveranstaltung();
							$lv_obj->load($row->lehrfach_id);

							if($row->studiengang_kz==0 AND $row->semester==0)
							{
								$stsementry[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
								 'target'=>'content',
								 'link'=>'private/freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$stsem,
								 'name'=>'FF '.CutString($row->lehreverzeichnis, $cutlength, '...')
								);
							}
							else
							{
								$stg_obj = new studiengang();
								$stg_obj->load($row->studiengang_kz);
								$kurzbz = $stg_obj->kuerzel.'-'.$row->semester.' '.$row->orgform_kurzbz;

								$stsementry[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
								 'target'=>'content',
								 'link'=>'private/lehre/lesson.php?lvid='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$stsem,
								 'name'=>CutString($lv_obj->bezeichnung_arr[$sprache], $cutlength, '...')
								);
							}
						}
						if(count($stsementry)>0)
						{
							$this->items[] = array('title'=>$stsem,
									'target'=>'',
									'link'=>'#',
									'name'=>$stsem,
									'childs'=>$stsementry
							);
						}
					}
					else
						echo "Fehler beim Auslesen des Lehrfaches";
				}

			}
			else
			{
				echo "Fehler Semester beim Auslesen der LV";
			}
		}
		$this->output();
	}
}

new menu_addon_meinelvkompatibel();
?>
