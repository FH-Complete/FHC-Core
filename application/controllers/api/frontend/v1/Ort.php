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

/**
 * This controller operates between (interface) the JS (GUI) and the SearchBarLib (back-end)
 * Provides data to the ajax get calls about the searchbar component
 * This controller works with JSON calls on the HTTP GET and the output is always JSON
 */
class Ort extends FHCAPI_Controller
{
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		// NOTE(chris): additional permission checks will be done in SearchBarLib
		parent::__construct([
			'ContentID' => self::PERM_LOGGED,
			'getOrtKurzbzContent' => self::PERM_LOGGED,
			'getRooms' => self::PERM_LOGGED,
			'getTypes' => self::PERM_LOGGED
		]);

		$this->load->model('ressource/Ort_model', 'OrtModel');
		$this->config->load('raumsuche');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Retrieves all Ort entries filtered by the provided parameters
	 */
	public function getRooms()
	{
		$datum = $this->input->get('datum', TRUE);
		$von = $this->input->get('von', TRUE);
		$bis = $this->input->get('bis', TRUE);
		$typ = $this->input->get('typ', TRUE);
		$personenanzahl = $this->input->get('personenanzahl', TRUE);
		
		
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter(getAuthUID())->retval;
		
		$this->load->model('ressource/Stunde_model', 'StundeModel');
		$vonStunde = getData($this->StundeModel->getStundeForTime($von))[0]->stunde;
		$bisStunde = getData($this->StundeModel->getStundeForTime($bis))[0]->stunde;
		
		$params = array();
		$qry = "SELECT DISTINCT tbl_ort.*
			FROM public.tbl_ort JOIN public.tbl_ortraumtyp USING(ort_kurzbz)
			WHERE aktiv AND lehre AND ort_kurzbz NOT LIKE '\\\\_%'";
		if($typ) {
			$params[] = $typ;
			$qry.= "AND raumtyp_kurzbz = ?";
		}
		
		if(!$isMitarbeiter) { // students are only allowed to get a subset defined by config
			$qry.= ' AND raumtyp_kurzbz IN ?';
			$params[] = $this->config->item('roomtypes_student');
			$this->addMeta('config', $this->config->item('roomtypes_student'));
		}
		
		$qry.= "AND (max_person>= ? OR max_person is null)";
		$params[] = $personenanzahl;

		$qry.="	AND ort_kurzbz NOT IN 
			(
				SELECT ort_kurzbz FROM lehre.tbl_stundenplandev WHERE datum = ? AND stunde >= ? AND stunde <= ? 
				UNION 
				SELECT ort_kurzbz FROM campus.tbl_reservierung WHERE datum= ? AND stunde >= ? AND stunde <= ?
			)
		";
		$params = array_merge($params, [$datum, $vonStunde, $bisStunde, $datum, $vonStunde, $bisStunde]);
		$this->addMeta('qry', $qry);
		$this->addMeta('params', $params);
		$result = $this->OrtModel->execReadOnlyQuery($qry, $params);
		
		$this->terminateWithSuccess($result);
	}

	public function getTypes()
	{
		$this->load->model('ressource/Raumtyp_model', 'RaumtypModel');
		$qry = "SELECT * FROM public.tbl_raumtyp WHERE aktiv = true";
		$params = array();
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		
		$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter(getAuthUID())->retval;
		if(!$isMitarbeiter) { // students are only allowed to get a subset defined by config
			$qry.= ' AND raumtyp_kurzbz IN ?';
			$params[] = $this->config->item('roomtypes_student');
		}
                                 
        $qry .= " ORDER BY raumtyp_kurzbz;";
		
		$result = $this->OrtModel->execReadOnlyQuery($qry, $params);

		$this->terminateWithSuccess(getData($result));
	}
	
	/**
	 * Gets a JSON body via HTTP POST and provides the parameters
	 */
	public function ContentID()
	{
		// if error
		//$this->terminateWithError(SearchBarLib::ERROR_WRONG_JSON, self::ERROR_TYPE_GENERAL);
		
		$ort_kurzbz = $this->input->get('ort_kurzbz',TRUE);
		
		if(!$ort_kurzbz){
			$this->terminateWithError("missing ort_kurzbz parameter", self::ERROR_TYPE_GENERAL);
		}

		$result = $this->OrtModel->getContentID($ort_kurzbz);
		
		if(isError($result)){
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$result = hasData($result) ? current(getData($result)) : null;
		
		$this->terminateWithSuccess($result->content_id ?? NULL);
	}

	/**
	 * @param int		$version
	 * @param string	$sprache
	 * @param boolean	$sichtbar
	 *
	 * @return $content
	 */
	public function getOrtKurzbzContent($version = null, $sprache = null, $sichtbar = true)
	{
		$content_id = $this->input->get("content_id",TRUE);

		$this->load->library('CmsLib');

		$content = $this->cmslib->getContent($content_id, $version, $sprache, $sichtbar);

		if (isError($content))
			$this->terminateWithError(getError($content), self::ERROR_TYPE_GENERAL);

		$content = hasData($content) ? getData($content) : null;

		$this->terminateWithSuccess($content);
	}
}

