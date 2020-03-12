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
require_once(dirname(__FILE__).'/../../config/cis.config.inc.php');
require_once(dirname(__FILE__).'/../../config/global.config.inc.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../../include/lehrveranstaltung.class.php');
require_once(dirname(__FILE__).'/../../include/vertrag.class.php');

class menu_addon_meinelv extends menu_addon
{
	public function __construct()
	{
		parent::__construct();

		$sprache = getSprache();
		$user = get_uid();

		$is_lector=check_lektor($user);

		$p = new phrasen($sprache);
		if (defined("CIS_LVMENUE_CUTLENGTH"))
			$cutlength = CIS_LVMENUE_CUTLENGTH;
		else
			$cutlength = 21;

		//Meine LVs Student
		if(!$is_lector)
		{
			$stsemobj = new studiensemester();

			$stsem_arr=array();
			$stsem_arr[]=$stsemobj->getNearest();
			$stsem_arr[]=$stsemobj->getNearestFrom($stsem_arr[0]);

			foreach($stsem_arr as $stsem)
			{
				$qry = "SELECT
							distinct
							tbl_studiengang.typ,
							tbl_studiengang.kurzbz,
							vw_student_lehrveranstaltung.lehrveranstaltung_id,
							vw_student_lehrveranstaltung.bezeichnung,
							vw_student_lehrveranstaltung.studiengang_kz,
							vw_student_lehrveranstaltung.semester,
							vw_student_lehrveranstaltung.lehre,
							vw_student_lehrveranstaltung.lehreverzeichnis,
							vw_student_lehrveranstaltung.studiensemester_kurzbz
						FROM
							campus.vw_student_lehrveranstaltung
							JOIN public.tbl_studiengang USING(studiengang_kz)
						WHERE
							uid=".$this->db_add_param($user)."
							AND studiensemester_kurzbz=".$this->db_add_param($stsem)."
							AND lehre=true
							AND lehreverzeichnis<>''
						ORDER BY
							tbl_studiengang.typ,
							tbl_studiengang.kurzbz,
							semester,
							bezeichnung";
				if($result = $this->db_query($qry))
				{
					if($this->db_num_rows($result)>0)
					{
						$this->items[] = array('title'=>$stsem,
								'target'=>'_self',
								'link'=>'#'.$stsem,
								'name'=>'<b>'.$stsem.'</b>');

						while($row = $this->db_fetch_object($result))
						{
							$lv_obj = new lehrveranstaltung();
							$lv_obj->load($row->lehrveranstaltung_id);

							if($row->studiengang_kz==0 && $row->semester==0) // Freifach
							{
								$this->items[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
								 'target'=>'content',
								 'link'=>'private/freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$row->studiensemester_kurzbz,
								 'name'=>'FF '.CutString($lv_obj->bezeichnung_arr[$sprache], $cutlength, '...')
								);
							}
							else
							{
								$this->items[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
								 'target'=>'content',
								 'link'=>'private/lehre/lesson.php?lvid='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$row->studiensemester_kurzbz,
								 'name'=>strtoupper($row->typ.$row->kurzbz).$row->semester.' '.CutString($lv_obj->bezeichnung_arr[$sprache], $cutlength, '...')
								);
							}
						}
					}
				}
				else
					echo "Fehler beim Auslesen der LV";
			}
		}

		//Eigenen LV des eingeloggten Lektors anzeigen
		if($is_lector)
		{
			if ($stsemobj = new studiensemester())
			{
				$stsem_arr=array();
				$stsem_arr[]=$stsemobj->getNearest();
				$stsem_arr[]=$stsemobj->getNearestFrom($stsem_arr[0]);

				$this->items[] = array('title'=>$p->t("lvaliste/titel"),
						'target'=>'content',
						'link'=>'private/profile/lva_liste.php',
						'name'=>$p->t("lvaliste/titel"));

				foreach($stsem_arr as $stsem)
				{
					$stsementry=array();
					$qry = "SELECT
								distinct
								tbl_studiengang.typ,
								tbl_studiengang.kurzbz,
								tbl_studiengang.bezeichnung AS studiengang_bezeichnung,
								tbl_studiengangstyp.bezeichnung AS studiengangstyp_bezeichnung,
								tbl_lehrveranstaltung.bezeichnung,
								tbl_lehrveranstaltung.studiengang_kz,
								tbl_lehrveranstaltung.semester,
								tbl_lehrveranstaltung.lehreverzeichnis,
								tbl_lehrveranstaltung.lehrveranstaltung_id,
								tbl_lehrveranstaltung.orgform_kurzbz,
								tbl_lehreinheit.studiensemester_kurzbz,
								tbl_orgform.bezeichnung AS orgform_bezeichnung
							FROM
								lehre.tbl_lehrveranstaltung
							LEFT JOIN
								bis.tbl_orgform ON (tbl_lehrveranstaltung.orgform_kurzbz=tbl_orgform.orgform_kurzbz),
								lehre.tbl_lehreinheit,
								lehre.tbl_lehreinheitmitarbeiter,
								public.tbl_studiengang
							LEFT JOIN
								public.tbl_studiengangstyp ON (tbl_studiengang.typ=tbl_studiengangstyp.typ)

							WHERE
								tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
								tbl_studiengang.studiengang_kz=tbl_lehrveranstaltung.studiengang_kz AND
								tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
								mitarbeiter_uid=".$this->db_add_param($user)." AND
								tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($stsem)."
							ORDER BY
								tbl_studiengang.typ,
								tbl_studiengang.kurzbz,
								tbl_lehrveranstaltung.semester,
								tbl_lehrveranstaltung.bezeichnung";

					if($result = $this->db_query($qry))
					{
						if($this->db_num_rows($result)>0)
						{
							$this->items[] = array('title'=>$stsem,
									'target'=>'_self',
									'link'=>'#'.$stsem,
									'name'=>'<b>'.$stsem.'</b>');

							while($row = $this->db_fetch_object($result))
							{
								$lv_obj = new lehrveranstaltung();
								$lv_obj->load($row->lehrveranstaltung_id);

								// Nur erteilte Vertraege anzeigen wenn dies im Config hinterlegt ist.
								if (defined('CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON')
								 && CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON != '')
								{
									$vertrag = new vertrag();
									if (!$vertrag->isVertragErteiltLV($lv_obj->lehrveranstaltung_id, $stsem, $user))
									{
										continue;
									}
								}

								if($row->studiengang_kz==0 AND $row->semester==0)
								{
									$this->items[] = array('title'=>$lv_obj->bezeichnung_arr[$sprache],
									 'target'=>'content',
									 'link'=>'private/freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id,
									 'name'=>'FF '.CutString($row->lehreverzeichnis, $cutlength, '...')
									);
								}
								else
								{
									$kurzbz = strtoupper($row->typ.$row->kurzbz).'-'.$row->semester.' '.$row->orgform_kurzbz;
									$titel = $row->studiengangstyp_bezeichnung.' '.$row->studiengang_bezeichnung.' '.$row->semester.'.Semester '.$row->orgform_bezeichnung.' '.$lv_obj->bezeichnung_arr[$sprache];

									$this->items[] = array('title'=>$titel,
									 'target'=>'content',
									 'link'=>'private/lehre/lesson.php?lvid='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$row->studiensemester_kurzbz,
									 'name'=>$kurzbz.' '.CutString($lv_obj->bezeichnung_arr[$sprache], $cutlength, '...')
									);
								}
							}
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

new menu_addon_meinelv();
