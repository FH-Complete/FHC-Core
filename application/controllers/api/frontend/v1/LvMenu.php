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

		$this->load->model("ressource/Mitarbeiter_model");
		$this->load->model("education/Lehreinheit_model");
		$this->load->model("education/Lehrveranstaltung_model");
		$this->load->model("organisation/Studiengang_model");
		$this->load->model("accounting/Vertrag_model");
		$this->load->model("system/Variable_model");
		$this->load->model("person/Benutzergruppe_model");
		$this->load->model("education/Lvangebot_model");
		$this->load->model("ressource/Lehretools_model");

		$this->load->library("PermissionLib", null, 'PermissionLib');

		$this->load->library("PhrasesLib");
		$this->loadPhrases(array('global', 'lehre'));
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

		// get the sprache
		$sprache = getUserLanguage();

		// get the user
		if (!$user=getAuthUID())
		    $this->terminateWithError($this->p->t('global', 'nichtAngemeldet'));

		// check if is_lector
		$is_lector = false;
		$mares = $this->Mitarbeiter_model->isMitarbeiter($user);
		if(hasData($mares))
		{
		    $is_lector = getData($mares);
		}

		// definition of user_is_allowed_to_upload
		$user_is_allowed_to_upload=false;
		$angezeigtes_stsem = $studiensemester_kurzbz;

		// load lehrveranstaltung
		$lvres = $this->Lehrveranstaltung_model->load($lvid);
		if(!hasData($lvres)) 
		{
		    $this->terminateWithError('LV ' . $lvid . ' not found.');
		}
		$lv = (getData($lvres))[0];

		// define studiengang_kz / semester / lehrverzeichnis
		$studiengang_kz = $lv->studiengang_kz;
		$semester = $lv->semester;
		$short = $lv->lehreverzeichnis;

		// load studiengang
		$stgres = $this->Studiengang_model->load($lv->studiengang_kz);
		if(!hasData($stgres))
		{
		    $this->terminateWithError('Stg ' . $lv->studiengang_kz . ' nof found.');
		}
		$stg = (getData($stgres))[0];
		$kurzbz = strtoupper($stg->typ . $stg->kurzbz);

		$short_name = $lv->bezeichnung;
		$short_short_name = $lv->lehreverzeichnis;

		// angemeldet
		$angemeldet = true;
		if(defined('CIS_LEHRVERANSTALTUNG_WENNANGEMELDET_DETAILS_ANZEIGEN') && CIS_LEHRVERANSTALTUNG_WENNANGEMELDET_DETAILS_ANZEIGEN && !$is_lector)
		{
			$angemeldet = false;

			$lesres = $this->Lehreinheit_model->getLehreinheitenForStudentAndStudienSemester(
			    $lvid, $user, $angezeigtes_stsem
			);

			if(hasData($lesres) && count(getData($lesres)) > 0)
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
				$result = $this->Lehreinheit_model->getLehrfachIdMitarbeiter($angezeigtes_stsem,$user,$lvid);
			}
			else
			{
				$result = $this->Lehreinheit_model->getLehrfachIdStudierender($angezeigtes_stsem,$user,$lvid);
			}

			// Wenn die LV mehrere verschiedenen Lehrfaecher hat, und der User zu mehreren davon zugeteilt ist
			// wird das Lehrfach nicht angezeigt damit es nicht zu verwirrungen kommt.
			if( ($lehrfaecher = getData($result)) && count($lehrfaecher)==1 && ($lehrfach = $lehrfaecher[0]))
			{
				$lehrfach_id=$lehrfach->lehrfach_id;
			}
		}

		// lektor der lv
		$lektor_der_lv=false;

		$leinfores = $this->Lehreinheit_model->getLehreinheitInfo($lvid,$angezeigtes_stsem,$lehrfach_id);
		$db_result = hasData($leinfores) ? getData($leinfores) : array();

		foreach($db_result as $row_lector)
		{

			// Lektor wird erst angezeigt wenn der Auftrag erteilt wurde
			if (defined('CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON')
				&& CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON != '')
			{
				if (!$this->Vertrag_model->isVertragErteiltLV($lvid, $angezeigtes_stsem, $row_lector->uid))
				{
					continue;
				}
			}


			if($user == $row_lector->uid)
			{
				$lektor_der_lv=true;
				$user_is_allowed_to_upload=true;
			}

			// style of the link
			if($row_lector->lvleiter === true)
				$style='style="font-weight: bold"';
			else
				$style='';

		}

		//Berechtigungen auf Fachbereichsebene
		$lehrfach_oe_kurzbz_arr = array();
		$fbres = $this->Lehrveranstaltung_model->getBerechtigungenAufFachberechsebene($lvid, $angezeigtes_stsem);
		$fbs = (hasData($fbres)) ? getData($fbres) : array();
		foreach($fbs as $row)
		{
			$lehrfach_oe_kurzbz_arr[] = $row->oe_kurzbz;
			if($this->PermissionLib->isBerechtigt('lehre', null, $row->oe_kurzbz) 
			    || $this->PermissionLib->isBerechtigt('assistenz', null, $stg->oe_kurzbz))
			{
				$user_is_allowed_to_upload=true;
			}
		}

		// FH-Core Menu Logic 
		// ##########################################################################################

		$menu = array();

		$this->fhc_menu_lvinfo($menu, $lvid, $studiengang_kz, $lektor_der_lv, $is_lector, $lehrfach_oe_kurzbz_arr);
		
		$this->fhc_menu_feedback($menu, $angemeldet, $lvid);
		
		$this->fhc_menu_gesamtnote($menu, $angemeldet, $lvid, $lv, $is_lector, $angezeigtes_stsem);
		
		$this->fhc_menu_emailStudierende($menu, $user, $angemeldet, $lvid, $angezeigtes_stsem);
		
		$this->fhc_menu_abmeldung($menu, $user, $is_lector, $lvid, $angezeigtes_stsem);
		
		$this->fhc_menu_lehretools($menu, $lvid, $angezeigtes_stsem, $sprache);
		
		$this->fhc_menu_anrechnungStudent($menu, $lvid, $angezeigtes_stsem);
		
		$this->fhc_menu_anrechnungLector($menu, $angezeigtes_stsem);
		

		// Addons Menu Logic
		// ##########################################################################################		

		$params = [
			'sprache'=>$sprache,
			//'p'=>$p,
			'ci_p'=> $this->p,
			//'db'=>$db,
			'user'=>$user,
			'is_lector'=>$is_lector,
			'user_is_allowed_to_upload'=>$user_is_allowed_to_upload,
			//'rechte'=>$rechte,
			'angezeigtes_stsem'=>$angezeigtes_stsem,
			//'lehreinheit'=>$lehreinheit,
			'lv_obj'=>$lv,
			'lv'=>$lv,
			'lvid'=>$lvid,
			'studiengang_kz'=>$studiengang_kz,
			'semester'=>$semester,
			'short'=>$short,
			'stg_obj'=>$stg,
			'kurzbz'=>$kurzbz,
			'short_name'=>$short_name,
			'short_short_name'=>$short_short_name,
			//'dir_name'=>$dir_name,
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

			// removes menu points that are not needed in the c4 lvUebersicht
			if( !array_key_exists('c4_link',$row) || !array_key_exists('c4_icon',$row)){
				unset($menu[$key]);
				continue;
			}
			
			// fills pos array to sort the menu 
			$pos[$key] = $row['position'];

		}

		array_multisort($pos, SORT_ASC, SORT_NUMERIC, $menu);

		// HTTP response
		// ##########################################################################################
		
		$this->terminateWithSuccess($menu);

	}


	private function fhc_menu_digitale_anwesenheiten(&$menu, $angemeldet, $studiengang_kz, $semester, $lvid, $angezeigtes_stsem){
		
		// DIGITALE ANWESENHEITEN
		if (defined('CIS_LEHRVERANSTALTUNG_ANWESENHEIT_ANZEIGEN') && CIS_LEHRVERANSTALTUNG_ANWESENHEIT_ANZEIGEN && $angemeldet) {

			$menu[] = array
			(
				'id' => 'core_menu_digitale_anwesenheitslisten',
				'position' => '50',
				'name' => $this->p->t('lehre', 'digiAnw'),
				'c4_icon' => base_url('skin/images/button_kreuzerltool.png'),
				'c4_link' => base_url("index.ci.php/extensions/FHC-Core-Anwesenheiten/?stg_kz=$studiengang_kz&sem=$semester&lvid=$lvid&sem_kurzbz=$angezeigtes_stsem&nav=false"),
				'c4_linkList' => []
			);
		}
	}

	private function fhc_menu_lvinfo(&$menu, $lvid, $studiengang_kz, $lektor_der_lv, $is_lector, $lehrfach_oe_kurzbz_arr){
		
		// LVINFO
		if(!defined('CIS_LEHRVERANSTALTUNG_LVINFO_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_LVINFO_ANZEIGEN)
		{
			$c4_linkList=array();
			
			// Bearbeiten Button anzeigen wenn Lektor der LV und bearbeiten fuer Lektoren aktiviert ist
			// Oder Berechtigung zum Bearbeiten eingetragen ist
			if((!defined('CIS_LEHRVERANSTALTUNG_LVINFO_LEKTOR_EDIT') && $lektor_der_lv)
			|| (defined('CIS_LEHRVERANSTALTUNG_LVINFO_LEKTOR_EDIT') && CIS_LEHRVERANSTALTUNG_LVINFO_LEKTOR_EDIT==true && $lektor_der_lv)
			|| $this->PermissionLib->isBerechtigt('lehre/lvinfo',$studiengang_kz)
			|| $this->PermissionLib->isBerechtigtMultipleOe('lehre/lvinfo', $lehrfach_oe_kurzbz_arr)
			)
			{
				$c4_linkList[]= [$this->p->t('lehre', 'lvInfoBearbeiten'), 'ects/index.php?lvid='.$lvid];
			}
			elseif ($is_lector)
			{
				$c4_linkList[]= ["Bearbeiten der LV-Infos derzeit gesperrt",'#'];
			}

			$menu[]=array
			(
				'id'=>'core_menu_lvinfo',
				'position'=>'10',
				'name'=>$this->p->t('lehre', 'lehrveranstaltungsinformation'),
				'icon'=>'../../../skin/images/button_lvinfo.png',
				'link'=>'',
				'c4_icon'=> base_url('skin/images/button_lvinfo.png'),
				'c4_link'=>'',
				'c4_linkList'=>$c4_linkList
			);
		}
	}

	private function fhc_menu_feedback(&$menu, $angemeldet, $lvid){
		//FEEDBACK
		if((!defined('CIS_LEHRVERANSTALTUNG_FEEDBACK_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_FEEDBACK_ANZEIGEN) && $angemeldet)
		{
			$menu[]=array
			(
				'id'=>'core_menu_feedback',
				'position'=>'60',
				'name'=>$this->p->t('lehre', 'feedback'),
				'c4_icon'=> base_url('skin/images/button_feedback.png'),
				'c4_link'=> base_url('feedback.php?lvid='.$lvid),
			);
		}
	}

	private function fhc_menu_gesamtnote(&$menu, $angemeldet, $lvid, $lv_obj, $is_lector, $angezeigtes_stsem){
		//Gesamtnote
		if($is_lector && ((!defined('CIS_LEHRVERANSTALTUNG_GESAMTNOTE_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_GESAMTNOTE_ANZEIGEN) && $angemeldet))
		{
			if($lv_obj->benotung)
			{
				$menu[]=array
				(
					'id'=>'core_menu_gesamtnote',
					'position'=>'80',
					'name'=>$this->p->t('lehre', 'gesamtnote'),
					'c4_icon'=> base_url('skin/images/button_endnote.png'),
					'c4_link'=> base_url('cis/private/lehre/benotungstool/lvgesamtnoteverwalten.php?lvid='.urlencode($lvid).'&stsem='.urlencode($angezeigtes_stsem))
					//'c4_link'=> base_url('benotungstool/lvgesamtnoteverwalten.php?lvid='.urlencode($lvid).'&stsem='.urlencode($angezeigtes_stsem))
				);
			}
			else
			{
				$menu[]=array
				(
					'id'=>'core_menu_gesamtnote',
					'position'=>'80',
					'name'=>$this->p->t('lehre', 'gesamtnote'),
					'c4_icon'=>base_url('skin/images/button_endnote.png'),
					'c4_link'=>'#',
					'c4_linkList'=>[[$this->p->t('lehre', 'noteneingabedeaktiviert'),'#']],
				);
			}
		}
	}

	

	private function fhc_menu_emailStudierende(&$menu, $user, $angemeldet, $lvid, $angezeigtes_stsem){
		// Email an Studierende
		if((!defined('CIS_LEHRVERANSTALTUNG_MAILSTUDIERENDE_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_MAILSTUDIERENDE_ANZEIGEN) && $angemeldet)
		{
			$mailto='mailto:';
			$c4_linkList=array();
			
			$studentMailsRes = $this->Lehrveranstaltung_model->getStudentEMail($lvid, $angezeigtes_stsem);
			
			// get the data of the database result and map the array of objects to their object property
			$studentMails = $this->getDataOrTerminateWithError($studentMailsRes, 'No student mails found');
			

			$nomail='';
			$variablesres = $this->Variable_model->getVariables($user);
			$variables = (hasData($variablesres)) ? getData($variablesres) : array();

			foreach ($studentMails as $row)
			{
				if($row->gruppe_kurzbz != '')
				{
					$bngrp_uids = $this->Benutzergruppe_model->getUids($row->gruppe_kurzbz, $angezeigtes_stsem);
					if(count($bngrp_uids) > 0)
					{
						if(!$row->mailgrp)
						{
							$nomail = $row->gruppe_kurzbz . ' ';
						}
						else
						{
							$mailto .= mb_strtolower($row->gruppe_kurzbz . '@' 
								. DOMAIN . $variables['emailadressentrennzeichen']);
						}
					}
				}
				else
				{
					$mailto .= mb_strtolower($row->stg_typ . $row->stg_kurzbz 
						. $row->semester . trim($row->verband) . trim($row->gruppe) 
						. '@' . DOMAIN . $variables['emailadressentrennzeichen']);
				}
			}

			if($nomail != '') 
			{
				$c4_linkList[] = array(
					$this->p->t('lehre', 'keinMailverteiler', array('nomail' => $nomail)), 
					'#'
				);
				$link_onclick = 'alert(\''.$this->p->t('lehre', 'keinMailverteiler', array('nomail' => $nomail)) . '\');';
			}
			else
			{
				$link_onclick = '';
			}
			
			
			$menu[]=array
			(
				'id'=>'core_menu_mailanstudierende',
				'position'=>'100',
				'name'=>$this->p->t('lehre', 'mail'),
				'c4_icon'=>base_url('skin/images/button_feedback.png'),
				'c4_icon2' => 'fa-regular fa-envelope',
				'c4_link'=>$mailto,
				'c4_linkList'=>$c4_linkList,
				'link_onclick'=>$link_onclick
			);
		} 
	}

	

	private function fhc_menu_abmeldung(&$menu, $user, $is_lector, $lvid, $angezeigtes_stsem){
		if(!defined('CIS_LEHRVERANSTALTUNG_ABMELDUNG_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_ABMELDUNG_ANZEIGEN)
		{
			if(!$is_lector)
			{
				$gruppen = $this->Lvangebot_model->AbmeldungMoeglich($lvid, $angezeigtes_stsem, $user);
				if(count($gruppen) > 0)
				{
					$menu[]=array
					(
						'id'=>'core_menu_abmeldung',
						'position'=>'120',
						'name'=>$this->p->t('lehre', 'abmelden'),
						'c4_icon'=>base_url('skin/images/button_studiupload.png'),
						'c4_link'=>base_url('abmeldung.php?lvid='.urlencode($lvid).'&stsem='.urlencode($angezeigtes_stsem)),
					);

				}
			}
		}
	}

	private function fhc_menu_lehretools(&$menu, $lvid, $angezeigtes_stsem, $sprache){
		//Anzeigen von zusaetzlichen Lehre-Tools
		$lehretools = $this->Lehretools_model->getTools($lvid, $angezeigtes_stsem, $sprache);
		foreach($lehretools as $row)
		{
			$menu[] = array(
				'id' => 'core_menu_lehretools_' . $row->lehre_tools_id,
				'position' => '1000',
				'name' => $row->bezeichnung,
				'c4_icon' => base_url('cms/dms.php?id='.$row->logo_dms_id),
				'c4_link' => $row->basis_url,
			);
		}
	}

    private function fhc_menu_anrechnungStudent(&$menu, $lvid, $angezeigtes_stsem){
		// Anerkennung nachgewiesener Kenntnisse (Anrechnung) - Anzeige fuer Studenten
		if((!defined('CIS_LEHRVERANSTALTUNG_ANRECHNUNG_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_ANRECHNUNG_ANZEIGEN)
			&& $this->PermissionLib->isBerechtigt('student/anrechnung_beantragen'))
		{
			$menu[]=array
			(
				'id' => 'core_menu_anerkennungNachgewiesenerKenntnisse',
				'position' => '128',
				'name' => $this->p->t('lehre', 'anrechnung'),
				'c4_icon' => base_url('skin/images/button_listen.png'),
				'c4_icon2' => 'fa-regular fa-folder-open',
				'c4_link' => base_url('cis.php/lehre/anrechnung/RequestAnrechnung?studiensemester='.urlencode($angezeigtes_stsem).'&lv_id='.urlencode($lvid))
			);
		}
    }

    private function fhc_menu_anrechnungLector(&$menu, $angezeigtes_stsem){
		// Anerkennung nachgewiesener Kenntnisse (Anrechnung) - Anzeige fuer LektorInnen
		if((!defined('CIS_LEHRVERANSTALTUNG_ANRECHNUNG_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_ANRECHNUNG_ANZEIGEN)
			&& $this->PermissionLib->isBerechtigt('lehre/anrechnung_empfehlen'))
		{
			$menu[]=array
			(
				'id' => 'core_menu_anerkennungNachgewiesenerKenntnisse_empfehlen',
				'position' => '128',
				'name' => $this->p->t('lehre', 'anrechnungen'),
				'c4_icon'=> base_url('skin/images/button_listen.png'),
				'c4_icon2' => 'fa-regular fa-folder-open',
				'c4_link' => base_url('cis.php/lehre/anrechnung/ReviewAnrechnungUebersicht?studiensemester='.urlencode($angezeigtes_stsem))
			);
		}
    }
}