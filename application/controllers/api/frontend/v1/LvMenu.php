<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');


use CI3_Events as Events;

/**
 * This controller operates between (interface) the JS (GUI) and the SearchBarLib (back-end)
 * Provides data to the ajax get calls about the searchbar component
 * This controller works with JSON calls on the HTTP GET and the output is always JSON
 */
class LvMenu extends FHCAPI_Controller
{
    

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getLvMenu' => self::PERM_LOGGED
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * 
	 */
	public function getLvMenu($lvid, $studiensemester_kurzbz)
	{

		// return early if parameters are missing
		if(!isset($lvid) || !isset($studiensemester_kurzbz))
			$this->terminateWithError('Missing parameters', self::ERROR_TYPE_GENERAL);

		// FH-Core Variables 
		// #############################################

		require_once(FHCPATH.'config/cis.config.inc.php');
		require_once(FHCPATH.'include/lehrveranstaltung.class.php');
		require_once(FHCPATH.'include/studiensemester.class.php');
		require_once(FHCPATH.'include/lehreinheit.class.php');
		require_once(FHCPATH.'include/vertrag.class.php');
		require_once(FHCPATH.'include/functions.inc.php');
		require_once(FHCPATH.'include/benutzerberechtigung.class.php');
		require_once(FHCPATH.'include/studiengang.class.php');
		require_once(FHCPATH.'include/phrasen.class.php');

		// get the sprache
		$sprache = getSprache();
		$p = new phrasen($sprache);

		// get the db
		if (!$db = new basis_db())
		die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

		// get the user
		if (!$user=get_uid())
			die($p->t('global/nichtAngemeldet'));

		// check if is_lector
		if(check_lektor($user))
			$is_lector=true;
		else
			$is_lector=false;

		// definition of user_is_allowed_to_upload
		$user_is_allowed_to_upload=false;

		// rechte des users
		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($user);

		$angezeigtes_stsem = $studiensemester_kurzbz;

		// load lehreinheit
		$lehreinheit = new lehreinheit($lvid);

		// load lehrveranstaltung
		$lv_obj = new lehrveranstaltung();
		$lv_obj->load($lvid);
		$lv=$lv_obj;

		// define studiengang_kz / semester / lehrverzeichnis
		$studiengang_kz = $lv->studiengang_kz;
		$semester = $lv->semester;
		$short = $lv->lehreverzeichnis;

		// load studiengang
		$stg_obj = new studiengang();
		$stg_obj->load($lv->studiengang_kz);
		$kurzbz = $stg_obj->kuerzel;

		$short_name = $lv->bezeichnung;
		$short_short_name = $lv->lehreverzeichnis;

		// angemeldet
		$angemeldet = true;
		if(defined('CIS_LEHRVERANSTALTUNG_WENNANGEMELDET_DETAILS_ANZEIGEN') && CIS_LEHRVERANSTALTUNG_WENNANGEMELDET_DETAILS_ANZEIGEN && !$is_lector)
		{
			$angemeldet = false;

			$lehrveranstaltung_obj = new lehrveranstaltung();
			$result = $lehrveranstaltung_obj->getLehreinheitenOfLv($lvid, $user, $angezeigtes_stsem);

			if(count($result)>0)
				$angemeldet = true;
		}

		// lehrfach
		$lehrfach_id='';
		
		if(defined('CIS_LEHRVERANSTALTUNG_LEHRFACH_ANZEIGEN') && CIS_LEHRVERANSTALTUNG_LEHRFACH_ANZEIGEN)
		{
			// Wenn der eingeloggte User zu einer der Lehreinheiten zugeteilt ist
			// wird zusätzlich das Lehrfach der Lehreinheit angezeigt.
			if($is_lector )
			{
				$result = $this->lehreinheit->lehrfach_id_mitarbeiter($angezeigtes_stsem,$user,$lvid);
			}
			else
			{
				$result = $this->lehreinheit->lehrfach_id_studierender($angezeigtes_stsem,$user,$lvid);
				
			}

			// Wenn die LV mehrere verschiedenen Lehrfaecher hat, und der User zu mehreren davon zugeteilt ist
			// wird das Lehrfach nicht angezeigt damit es nicht zu verwirrungen kommt.
			if(count($result)==1 && $row = $result[0])
			{   
				$lehrfach_id=$row->lehrfach_id;
			}
		}

		// lektor der lv
		$lektor_der_lv=false;
		
		$db_result = $lehreinheit->lehreinheitInfo($lvid,$angezeigtes_stsem,$lehrfach_id);

		$num_rows_result = count($db_result);

		if($num_rows_result > 0)
		{
			
			foreach($db_result as $row_lector)
			{
				
				// Lektor wird erst angezeigt wenn der Auftrag erteilt wurde
				if (defined('CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON')
					&& CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON != '')
				{
					$vertrag = new vertrag();
					if (!$vertrag->isVertragErteiltLV($lvid, $angezeigtes_stsem, $row_lector->uid))
					{
						continue;
					}
				}

			
				if($user==$row_lector->uid)
				{
					$lektor_der_lv=true;
					$user_is_allowed_to_upload=true;
				}

				// style of the link
				if($row_lector->lvleiter=='t')
					$style='style="font-weight: bold"';
				else
					$style='';

			}
		}

		//Berechtigungen auf Fachbereichsebene
		$lehrfach_oe_kurzbz_arr = array();
		if($result = $lv_obj->getBerechtigungenAufFachberechsebene($lvid,$angezeigtes_stsem))
		{
			foreach($result as $row)
			{
				$lehrfach_oe_kurzbz_arr[]=$row->oe_kurzbz;
				if($rechte->isBerechtigt('lehre',$row->oe_kurzbz) || $rechte->isBerechtigt('assistenz',$stg_obj->oe_kurzbz)){
					$user_is_allowed_to_upload=true;
				}
			}
		}

		// FH-Core Menu Logic 
		// #############################################

		$menu = array();

		$this->fhc_menu_lvinfo($menu, $lvid, $studiengang_kz, $lektor_der_lv, $is_lector, $rechte, $lehrfach_oe_kurzbz_arr, $p);

		$this->fhc_menu_semesterplan($menu, $angemeldet, $user_is_allowed_to_upload, $rechte, $lvid, $studiengang_kz, $kurzbz, $semester, $short_short_name, $p);

		// Addons Menu Logic
		// #############################################
		
        Events::trigger('lvMenuBuild', 
						// callback function for the onEvents to add newValues to the $menu
						function ($addonMenu) use (&$menu) {
							foreach($addonMenu as $m){
								$menu[]= $m;
							}
						},
						$lvid,
						$studiensemester_kurzbz,
						$studiengang_kz,
						$p,
						$rechte,
						$lv,
						$angezeigtes_stsem,
						$lektor_der_lv,
						$lehrfach_oe_kurzbz_arr,
						$is_lector
		);

		$this->terminateWithSuccess($menu);

	}


	private function fhc_menu_lvinfo(&$menu, $lvid, $studiengang_kz, $lektor_der_lv, $is_lector, $rechte, $lehrfach_oe_kurzbz_arr, $p){
		
		// LVINFO
		if(!defined('CIS_LEHRVERANSTALTUNG_LVINFO_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_LVINFO_ANZEIGEN)
		{
			$text='';
			$need_br=false;

			// Bearbeiten Button anzeigen wenn Lektor der LV und bearbeiten fuer Lektoren aktiviert ist
			// Oder Berechtigung zum Bearbeiten eingetragen ist
			if((!defined('CIS_LEHRVERANSTALTUNG_LVINFO_LEKTOR_EDIT') && $lektor_der_lv)
			|| (defined('CIS_LEHRVERANSTALTUNG_LVINFO_LEKTOR_EDIT') && CIS_LEHRVERANSTALTUNG_LVINFO_LEKTOR_EDIT==true && $lektor_der_lv)
			|| $rechte->isBerechtigt('lehre/lvinfo',$studiengang_kz)
			|| $rechte->isBerechtigtMultipleOe('lehre/lvinfo', $lehrfach_oe_kurzbz_arr)
			)
			{
				if($need_br)
					$text.= "<br>";
				$text.= "<a href='ects/index.php?lvid=$lvid' target='_blank' class='Item'>".$p->t('lehre/lvInfoBearbeiten')."</a>";
			}
			elseif ($is_lector)
			{
				$text.= "<br>Bearbeiten der LV-Infos derzeit gesperrt";
			}

			$menu[]=array
			(
				'id'=>'core_menu_lvinfo',
				'position'=>'10',
				'name'=>$p->t('lehre/lehrveranstaltungsinformation'),
				'icon'=>'../../../skin/images/button_lvinfo.png',
				'link'=>'',
				'text'=>$text
			);
		}
	}

	private function fhc_menu_semesterplan(&$menu, $angemeldet, $user_is_allowed_to_upload, $rechte, $lvid, $studiengang_kz, $kurzbz, $semester, $short_short_name, $p){
		
		// Semesterplan
		if((!defined('CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_ANZEIGEN) && $angemeldet)
		{
			$dir_name = $this->ensureDirectoryExists($kurzbz, $semester, $short_short_name, 'semesterplan','teacher');
			
			$dir_empty = count(scandir($dir_name)) == 2 ? true : false;

			if($dir_empty == false)
			{
				$link = $dir_name.'/';
			}
			else
				$link = '';

			$text='';
			if((!defined('CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_LEKTOR_EDIT') && $user_is_allowed_to_upload)
			|| (defined('CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_LEKTOR_EDIT') && CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_LEKTOR_EDIT==true && $user_is_allowed_to_upload)
			|| $rechte->isBerechtigt('admin',$studiengang_kz) || $rechte->isBerechtigt('lehre',$studiengang_kz))
			{
				$text.= '<a class="Item" href="#" onClick="javascript:window.open(\'semupload.php?lvid='.$lvid.'\',\'_blank\',\'width=400,height=300,location=no,menubar=no,status=no,toolbar=no\');return false;">';
				$text.= $p->t('lehre/semesterplanUpload')."</a>";

				$text.= '&nbsp;&nbsp;&nbsp;<a class="Item" href="semdownhlp.php" >';
				$text.= $p->t('lehre/semesterplanVorlage');
				$text.= ' [html]';
				$text.= '</a>';
				$text.= '&nbsp;<a class="Item" href="semdownhlp.php?format=doc" >';
				$text.= '[doc]';
				$text.= '</a>';
				$text.= '&nbsp;<a href="#" onClick="showSemPlanHelp()";>('.$p->t('lehre/semesterplanVorlageHilfe').')</a>';
			}

			$menu[]=array
			(
				'id'=>'core_menu_semesterplan',
				'position'=>'20',
				'name'=>$p->t('lehre/semesterplan'),
				'icon'=>'../../../skin/images/button_semplan.png',
				'link'=>$link,
				'link_target'=>'_blank',
				'text'=>$text
			);
		}
	}

	private function ensureDirectoryExists($kurzbz, $semester, $short_short_name, $type, $role){
		$dir_name = DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/'.$type;

		if (!is_dir($dir_name)) {
			exec('mkdir -m 775 -p '.escapeshellarg($dir_name));
			exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)));
			exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester));
			exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)));
			exec('sudo chown www-data:'.$role.' '.escapeshellarg($dir_name));
		}

		return $dir_name;
	}
}

