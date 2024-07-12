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
		// ##########################################################################################

		require_once(FHCPATH.'config/cis.config.inc.php');
		require_once(FHCPATH.'include/lehrveranstaltung.class.php');
		require_once(FHCPATH.'include/studiensemester.class.php');
		require_once(FHCPATH.'include/lehreinheit.class.php');
		require_once(FHCPATH.'include/vertrag.class.php');
		require_once(FHCPATH.'include/functions.inc.php');
		require_once(FHCPATH.'include/benutzerberechtigung.class.php');
		require_once(FHCPATH.'include/studiengang.class.php');
		require_once(FHCPATH.'include/phrasen.class.php');
		require_once(FHCPATH.'include/lvangebot.class.php');
		require_once(FHCPATH.'include/lehre_tools.class.php');

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

		// create documents directory
		$dir_name=DOC_ROOT.'/documents';
		if(!is_dir($dir_name))
		{
			exec('mkdir -m 755 '.escapeshellarg($dir_name));
			exec('sudo chown www-data:teacher '.escapeshellarg($dir_name));
		}

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
		// ##########################################################################################

		$menu = array();

		$this->fhc_menu_lvinfo($menu, $lvid, $studiengang_kz, $lektor_der_lv, $is_lector, $rechte, $lehrfach_oe_kurzbz_arr, $p);

		$this->fhc_menu_semesterplan($menu, $angemeldet, $user_is_allowed_to_upload, $rechte, $lvid, $studiengang_kz, $kurzbz, $semester, $short_short_name, $p);
		
		$this->fhc_menu_download($menu, $angemeldet, $user_is_allowed_to_upload, $rechte, $semester, $kurzbz, $studiengang_kz, $short_short_name, $short, $p);
		
		$this->fhc_menu_notenliste($menu, $angemeldet, $is_lector, $lvid, $studiengang_kz, $angezeigtes_stsem, $kurzbz, $semester, $short_short_name, $p);

		$this->fhc_menu_feedback($menu, $angemeldet, $lvid, $p);

		$this->fhc_menu_gesamtnote($menu, $angemeldet, $lvid, $lv_obj, $is_lector, $angezeigtes_stsem, $p);

		$this->fhc_menu_stundenupload($menu, $angemeldet, $lvid, $is_lector, $studiengang_kz, $kurzbz, $semester, $short, $short_short_name, $p);

		$this->fhc_menu_emailStudierende($menu, $angemeldet, $lehreinheit->lehreinheit_id, $p);

		$this->fhc_menu_pinboard($menu, $angemeldet, $is_lector, $studiengang_kz, $semester, $p);

		$this->fhc_menu_abmeldung($menu, $user, $is_lector, $lvid, $angezeigtes_stsem, $p);

		$this->fhc_menu_lehretools($menu, $lvid, $angezeigtes_stsem, $sprache);

		$this->fhc_menu_anrechnungStudent($menu, $rechte, $lvid, $angezeigtes_stsem, $p);

		$this->fhc_menu_anrechnungLector($menu, $rechte, $angezeigtes_stsem, $p);

		// Addons Menu Logic
		// ##########################################################################################		
        
		$params = [
			'sprache'=>$sprache,
			'p'=>$p,
			'db'=>$db,
			'user'=>$user,
			'is_lector'=>$is_lector,
			'user_is_allowed_to_upload'=>$user_is_allowed_to_upload,
			'rechte'=>$rechte,
			'angezeigtes_stsem'=>$angezeigtes_stsem,
			'lehreinheit'=>$lehreinheit,
			'lv_obj'=>$lv_obj,
			'lv'=>$lv,
			'lvid'=>$lvid,
			'studiengang_kz'=>$studiengang_kz,
			'semester'=>$semester,
			'short'=>$short,
			'stg_obj'=>$stg_obj,
			'kurzbz'=>$kurzbz,
			'short_name'=>$short_name,
			'short_short_name'=>$short_short_name,
			'dir_name'=>$dir_name,
			'angemeldet'=>$angemeldet,
			'lehrfach_id'=>$lehrfach_id,
			'lektor_der_lv'=>$lektor_der_lv,
			'lehrfach_oe_kurzbz_arr'=>$lehrfach_oe_kurzbz_arr,
		];
		
		Events::trigger('lvMenuBuild', 
						// passing $menu per reference
						function & () use (&$menu) {
							return $menu;
						},
						$params
		);

		// Menu sortieren
		// ##########################################################################################
		
		foreach ($menu as $key => $row){
			
			// fills pos array to sort the menu 
			$pos[$key] = $row['position'];

			//adds base_url to both the c4_icon and c4_link
			/* if(array_key_exists('c4_icon',$menu[$key]) ){
				$menu[$key]['c4_icon']=base_url($menu[$key]['c4_icon']);
			}
			if(array_key_exists('c4_link',$menu[$key]) ){
				$menu[$key]['c4_link']=base_url($menu[$key]['c4_link']);
			} */
			

			/* 
			// adds new key with modified icon path to the menu
			$menu[$key]['cis4_icon'] = base_url(str_replace("../../..","",$row['icon']));
			
			// adds new key with modified link_onclick link to the menu 
			if(array_key_exists("link_onclick",$menu[$key])){
				$menu[$key]['cis4_link_onclick'] = str_replace("\"","'",$menu[$key]['link_onclick']);
			}

			// adds new key with modified link to the menu 
			if(array_key_exists("link",$menu[$key])){
				// only replace the link key if the link has an old path
				if(strpos($menu[$key]['link'],"../../..") !== false){
					$menu[$key]['cis4_link'] = base_url(str_replace("../../..","",$menu[$key]['link']));
				}else{
					$menu[$key]['cis4_link'] = $menu[$key]['link'];
				}
			} */
		}

		array_multisort($pos, SORT_ASC, SORT_NUMERIC, $menu);

		// HTTP response
		// ##########################################################################################

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
				'c4_icon'=> base_url('skin/images/button_lvinfo.png'),
				'c4_link'=>'',
				'text'=>$text
			);
		}
	}

	private function fhc_menu_semesterplan(&$menu, $angemeldet, $user_is_allowed_to_upload, $rechte, $lvid, $studiengang_kz, $kurzbz, $semester, $short_short_name, $p){
		
		// Semesterplan
		if((!defined('CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_ANZEIGEN) && $angemeldet)
		{
			$dir_name = $this->ensureDirectoryExists($kurzbz, $semester, $short_short_name, 'semesterplan','teacher');
			
			$dir_empty = $this->isDirectoryEmpty($dir_name);

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
				'c4_icon'=> base_url('skin/images/button_semplan.png'),
				'c4_link'=> $link,
				'link_target'=>'_blank',
				'text'=>$text
			);
		}
	}

	private function fhc_menu_download(&$menu, $angemeldet, $user_is_allowed_to_upload, $rechte, $semester, $kurzbz, $studiengang_kz, $short_short_name, $short, $p){
		
		if((!defined('CIS_LEHRVERANSTALTUNG_DOWNLOAD_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_DOWNLOAD_ANZEIGEN) && $angemeldet)
		{
			$dir_name = $this->ensureDirectoryExists($kurzbz, $semester, $short_short_name, 'download','teacher');
			$dir_empty = $this->isDirectoryEmpty($dir_name);

			$text = '';
			if($dir_empty == false)
			{
				$link = $dir_name.'/';
			}
			else
				$link = '';

			//Wenn user eine Lehrfachzuteilung fuer dieses Lehrfach hat wird
			//Ein Link zum Upload angezeigt und ein Link um das Download-Verzeichnis
			//als Zip Archiv herunterzuladen
			if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',$studiengang_kz) || $rechte->isBerechtigt('lehre',$studiengang_kz))// || $rechte->isBerechtigt('lehre',null,null,$fachbereich_id))
			{
				$text.= mb_strtolower("$kurzbz/$semester/$short/download");
				$text.=  '<br>';
				$text.=  "<a class='Item' target='_blank' href='upload.php?course_id=$studiengang_kz&term_id=$semester&short=$short'>".$p->t('lehre/upload')."</a>";
				$text.=  '&nbsp;&nbsp;&nbsp;';
			}

			$menu[]=array
			(
				'id'=>'core_menu_download',
				'position'=>'30',
				'name'=>$p->t('lehre/download'),
				'icon'=>'../../../skin/images/button_download.png',
				'link'=>$link,
				'c4_icon'=> base_url('skin/images/button_download.png'),
				'c4_link'=>$link,
				'text'=>$text
			);
		}
	}

	private function fhc_menu_notenliste(&$menu, $angemeldet, $is_lector, $lvid, $studiengang_kz, $angezeigtes_stsem, $kurzbz, $semester, $short_short_name, $p){
		// Anwesenheits und Notenlisten
		if(CIS_LEHRVERANSTALTUNG_LEISTUNGSUEBERSICHT_ANZEIGEN || $is_lector)
		{
			$link='';
			$name='';
			if($is_lector)
			{
				$name = $p->t('lehre/anwesenheitsUndNotenlisten');
				$link= "anwesenheitsliste.php?stg_kz=$studiengang_kz&sem=$semester&lvid=$lvid&stsem=$angezeigtes_stsem";
			}

			$text='';
			if(CIS_LEHRVERANSTALTUNG_LEISTUNGSUEBERSICHT_ANZEIGEN && ($angemeldet || $is_lector))
			{
				$dir_name = $this->ensureDirectoryExists($kurzbz, $semester, $short_short_name, 'leistung','teacher');
				$dir_empty = $this->isDirectoryEmpty($dir_name);

				if($dir_empty == false)
				{
					
					if($is_lector)
					{
						$text.= '<a href="'.$dir_name.'" target="_blank">';
						$text.= '<strong>'.$p->t('lehre/leistungsuebersicht').'</strong>';
						$text.= '</a>';
					}
					else
					{
						$name = $p->t('lehre/leistungsuebersicht');
						$link = $dir_name;
					}
				}
				else
				{
					if($is_lector)
					{
						$text.= '<strong>'.$p->t('lehre/leistungsuebersicht').'</strong>';
					}
					else
					{
						$name = $p->t('lehre/leistungsuebersicht');
						$link = '';
					}
				}
			}

			$menu[]=array
			(
				'id'=>'core_menu_anwesenheitslisten',
				'position'=>'40',
				'name'=>$name,
				'icon'=>'../../../skin/images/button_listen.png',
				'link'=>$link,
				'c4_icon'=> base_url('skin/images/button_listen.png'),
				'c4_link'=>$link,
				'text'=>$text
			);
		}
	}

	private function fhc_menu_feedback(&$menu, $angemeldet, $lvid, $p){
		//FEEDBACK
		if((!defined('CIS_LEHRVERANSTALTUNG_FEEDBACK_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_FEEDBACK_ANZEIGEN) && $angemeldet)
		{
			$menu[]=array
			(
				'id'=>'core_menu_feedback',
				'position'=>'50',
				'name'=>$p->t('lehre/feedback'),
				'icon'=>'../../../skin/images/button_feedback.png',
				'link'=>'feedback.php?lvid='.$lvid,
				'c4_icon'=> base_url('skin/images/button_feedback.png'),
				'c4_link'=> base_url('feedback.php?lvid='.$lvid),
				'text'=>''
			);
		}
	}

	private function fhc_menu_gesamtnote(&$menu, $angemeldet, $lvid, $lv_obj, $is_lector, $angezeigtes_stsem, $p){
		//Gesamtnote
		if($is_lector && ((!defined('CIS_LEHRVERANSTALTUNG_GESAMTNOTE_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_GESAMTNOTE_ANZEIGEN) && $angemeldet))
		{
			if($lv_obj->benotung)
			{
				$menu[]=array
				(
					'id'=>'core_menu_gesamtnote',
					'position'=>'80',
					'name'=>$p->t('lehre/gesamtnote'),
					'c4_icon'=> base_url('skin/images/button_endnote.png'),
					'c4_link'=> base_url('benotungstool/lvgesamtnoteverwalten.php?lvid='.urlencode($lvid).'&stsem='.urlencode($angezeigtes_stsem))
				);
			}
			else
			{
				$menu[]=array
				(
					'id'=>'core_menu_gesamtnote',
					'position'=>'80',
					'name'=>$p->t('lehre/gesamtnote'),
					'c4_icon'=>'skin/images/button_endnote.png',
					'text'=>$p->t('lehre/noteneingabedeaktiviert')
				);
			}
		}
	}

	private function fhc_menu_stundenupload(&$menu, $angemeldet, $lvid, $is_lector, $studiengang_kz, $kurzbz, $semester, $short, $short_short_name, $p){
		// Studentenupload
		if((!defined('CIS_LEHRVERANSTALTUNG_STUDENTENUPLOAD_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_STUDENTENUPLOAD_ANZEIGEN) && $angemeldet)
		{
			$link='';
			$link_target='';

			$dir_name = $this->ensureDirectoryExists($kurzbz, $semester, $short_short_name, 'upload','student');
			$dir_empty = $this->isDirectoryEmpty($dir_name);

			if(isset($dir_empty) && $dir_empty == false)
			{
				if($is_lector == true)
				{
					$link='lector_choice.php?lvid='.urlencode($lvid);
					$link_target='_blank';
				}
				else
				{
					$link='upload.php?course_id='.urlencode($studiengang_kz).'&term_id='.urlencode($semester).'&short='.urlencode($short);
					$link_target='_blank';
				}
			}
			else
			{
				if($is_lector == true)
				{
					$link='';
				}
				else
				{
					$link='upload.php?course_id='.urlencode($studiengang_kz).'&term_id='.urlencode($semester).'&short='.urlencode($short);
					$link_target='_blank';
				}
			}
			$menu[]=array
			(
				'id'=>'core_menu_studentenupload',
				'position'=>'90',
				'name'=>$p->t('lehre/studentenAbgabe'),
				'c4_icon'=>'skin/images/button_studiupload.png',
				'c4_link'=>$link,
				'link_target'=>$link_target
			);
		}
	}

	private function fhc_menu_emailStudierende(&$menu, $angemeldet, $lehreinheit_id,$p){
		// Email an Studierende
		if((!defined('CIS_LEHRVERANSTALTUNG_MAILSTUDIERENDE_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_MAILSTUDIERENDE_ANZEIGEN) && $angemeldet)
		{
			$nomail='';
			$mailto='mailto:';
			
			// load the Lehreinheit model and get the student mails
			$this->load->model('education/Lehreinheit_model','LehreinheitModel');
			$studentMails = $this->LehreinheitModel->getStudentenMail($lehreinheit_id);
			
			// get the data of the database result and map the array of objects to their object property
			$studentMails = $this->getDataOrTerminateWithError($studentMails, 'No student mails found');
			
			// emails used to create the mailto link
			$mailtoMails = array();
			$noMails = array();
			$noMailLink = FALSE;

			foreach($studentMails as $mail){
				
				if($mail->mail == 'nomail'){
					$noMails[]=$mail->gruppe_kurzbz;
					$noMailLink= TRUE;
				}else{
					$mailtoMails[]=$mail->mail;
				}
			}

			if($noMailLink){
				$link_onclick='alert(\''.$p->t('lehre/keinMailverteiler',array(implode(" ",$noMails))).'\');';
			}else{
				$link_onclick='';
			}
			
			$mailto .= implode(',',$mailtoMails);
			
			$menu[]=array
			(
				'id'=>'core_menu_mailanstudierende',
				'position'=>'100',
				'name'=>$p->t('lehre/mail'),
				'c4_icon'=>base_url('skin/images/button_feedback.png'),
				'c4_link'=>$mailto,
				'link_onclick'=>$link_onclick
			);
		} 
	}

	private function  fhc_menu_pinboard(&$menu, $angemeldet, $is_lector, $studiengang_kz, $semester, $p){
		// Pinboard
		if((!defined('CIS_LEHRVERANSTALTUNG_PINBOARD_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_PINBOARD_ANZEIGEN) && $angemeldet)
		{
			$text='';
			if($is_lector)
				$text.= "<a href='../../../cms/newsverwaltung.php?studiengang_kz=$studiengang_kz&semester=$semester' class='Item'>".$p->t('profil/adminstration')."</a>";
			
			// this is the new cis4 version
			$menu[]=array
			(
				'id'=>'core_menu_pinboard',
				'position'=>'110',
				'name'=>$p->t('lehre/pinboard'),
				'c4_icon'=>base_url('skin/images/button_pinboard.png'),
				'c4_link'=>base_url('CisHtml/Cms/news'),
				'text'=>$text
			);

			
		}
	}

	private function fhc_menu_abmeldung(&$menu, $user, $is_lector, $lvid, $angezeigtes_stsem, $p){
		if(!defined('CIS_LEHRVERANSTALTUNG_ABMELDUNG_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_ABMELDUNG_ANZEIGEN)
		{
			if(!$is_lector)
			{
				$lvangebot = new lvangebot();
				$gruppen = $lvangebot->AbmeldungMoeglich($lvid, $angezeigtes_stsem, $user);
				if(count($gruppen)>0)
				{
					$menu[]=array
					(
						'id'=>'core_menu_abmeldung',
						'position'=>'120',
						'name'=>$p->t('lehre/abmelden'),
						'icon'=>'../../../skin/images/button_studiupload.png',
						'link'=>'abmeldung.php?lvid='.urlencode($lvid).'&stsem='.urlencode($angezeigtes_stsem),
						'c4_icon'=>base_url('skin/images/button_studiupload.png'),
						'c4_link'=>base_url('abmeldung.php?lvid='.urlencode($lvid).'&stsem='.urlencode($angezeigtes_stsem)),
					);

				}
			}
		}
	}

	private function fhc_menu_lehretools(&$menu, $lvid, $angezeigtes_stsem, $sprache){
		//Anzeigen von zusaetzlichen Lehre-Tools
		$lehretools = new lehre_tools();
		if($lehretools->getTools($lvid, $angezeigtes_stsem))
		{
			if(count($lehretools->result)>0)
			{
				foreach($lehretools->result as $row)
				{
					$menu[]=array
					(
						'id'=>'core_menu_lehretools_'.$row->lehre_tools_id,
						'position'=>'1000',
						'name'=>$row->bezeichnung[$sprache],
						'icon'=>'../../../cms/dms.php?id='.$row->logo_dms_id,
						'link'=>$row->basis_url,
						'c4_icon'=>base_url('cms/dms.php?id='.$row->logo_dms_id),
						'c4_link'=>$row->basis_url,
						'link_target'=>'_blank'
					);
				}
			}
		}
	}

	private function fhc_menu_anrechnungStudent(&$menu, $rechte, $lvid, $angezeigtes_stsem, $p){
		// Anerkennung nachgewiesener Kenntnisse (Anrechnung) - Anzeige fuer Studenten
		if((!defined('CIS_LEHRVERANSTALTUNG_ANRECHNUNG_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_ANRECHNUNG_ANZEIGEN)
        && $rechte->isBerechtigt('student/anrechnung_beantragen'))
		{
			$menu[]=array
			(
				'id'=>'core_menu_anerkennungNachgewiesenerKenntnisse',
				'position'=>'128',
				'name'=>$p->t('lehre/anrechnung'),
				'icon'=>'../../../skin/images/button_listen.png',
				'link' => APP_ROOT. 'index.ci.php/lehre/anrechnung/RequestAnrechnung?studiensemester='.urlencode($angezeigtes_stsem).'&lv_id='.urlencode($lvid),
				'c4_icon'=>base_url('skin/images/button_listen.png'),
				'c4_link' => base_url('index.ci.php/lehre/anrechnung/RequestAnrechnung?studiensemester='.urlencode($angezeigtes_stsem).'&lv_id='.urlencode($lvid))
			);
		}
	}

	private function fhc_menu_anrechnungLector(&$menu, $rechte, $angezeigtes_stsem, $p){
		// Anerkennung nachgewiesener Kenntnisse (Anrechnung) - Anzeige fuer LektorInnen
		if((!defined('CIS_LEHRVERANSTALTUNG_ANRECHNUNG_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_ANRECHNUNG_ANZEIGEN)
		&& $rechte->isBerechtigt('lehre/anrechnung_empfehlen'))
		{
		$menu[]=array
		(
			'id'=>'core_menu_anerkennungNachgewiesenerKenntnisse_empfehlen',
			'position'=>'128',
			'name'=>$p->t('lehre/anrechnungen'),
			'icon'=>'../../../skin/images/button_listen.png',
			'link' => APP_ROOT. 'index.ci.php/lehre/anrechnung/ReviewAnrechnungUebersicht?studiensemester='.urlencode($angezeigtes_stsem),
			'c4_icon'=> base_url('skin/images/button_listen.png'),
			'c4_link' => base_url('index.ci.php/lehre/anrechnung/ReviewAnrechnungUebersicht?studiensemester='.urlencode($angezeigtes_stsem))
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

	private function isDirectoryEmpty($dir){
		return count(scandir($dir)) == 2 ? true : false;
	}
}

