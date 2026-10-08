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
	// advanced filters of getRooms: tbl_ort column => filter type, the same list as in Raumsuche.js.
	// text: contains, case-insensitive; select: equals; range: <column>_min, <column>_max; flag: true; exists: not null
	const ADVANCED_FILTERS = [
		'ort_kurzbz' => 'text',
		'bezeichnung' => 'text',
		'planbezeichnung' => 'text',
		'stockwerk' => 'range',
		'gebteil' => 'select',
		'm2' => 'range',
		'lehre' => 'flag',
		'content_id' => 'exists'
	];

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
			'getTypes' => self::PERM_LOGGED,
			'getStandorte' => self::PERM_LOGGED,
			'getAdvancedFilterOptions' => self::PERM_LOGGED
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
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('datum','Datum','required');
		$this->form_validation->set_rules('von','Uhrzeit Von','required|regex_match[/^[0-9]{2}:[0-9]{2}$/]');
		$this->form_validation->set_rules('bis','Uhrzeit Bis','required|regex_match[/^[0-9]{2}:[0-9]{2}$/]');
		$this->form_validation->set_rules('standort_id','Standort','regex_match[/^([0-9]+|none)$/]');
		foreach (self::ADVANCED_FILTERS as $column => $type) {
			if ($type == 'range') {
				$this->form_validation->set_rules($column.'_min', $column, 'numeric');
				$this->form_validation->set_rules($column.'_max', $column, 'numeric');
			} elseif ($type == 'flag' || $type == 'exists') {
				$this->form_validation->set_rules($column, $column, 'in_list[true]');
			} else {
				$this->form_validation->set_rules($column, $column, 'max_length[255]');
			}
		}
		if($this->form_validation->run() == FALSE) {
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}
		
		$datum = $this->input->get('datum', TRUE);
		$von = $this->input->get('von', TRUE);
		$bis = $this->input->get('bis', TRUE);
		$typ = $this->input->get('typ', TRUE);
		$personenanzahl = $this->input->get('personenanzahl', TRUE);
		$standort_id = $this->input->get('standort_id', TRUE);
		
		
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter(getAuthUID())->retval;
		
		$this->load->model('ressource/Stunde_model', 'StundeModel');
		$vonStunde = getData($this->StundeModel->getStundeForTime($von))[0]->stunde;
		$bisStunde = getData($this->StundeModel->getStundeForTime($bis))[0]->stunde;
		
		$params = array();
		$qry = "SELECT DISTINCT tbl_ort.*, tbl_standort.bezeichnung AS standort
			FROM public.tbl_ort JOIN public.tbl_ortraumtyp USING(ort_kurzbz)
			LEFT JOIN public.tbl_standort USING(standort_id)
			WHERE aktiv AND reservieren";
		if($typ) {
			$params[] = $typ;
			$qry.= " AND raumtyp_kurzbz = ?";
		}
		
		if($standort_id === 'none') { // rooms without a Standort
			$qry.= " AND standort_id IS NULL";
		} elseif($standort_id) {
			$params[] = $standort_id;
			$qry.= " AND standort_id = ?";
		} else {
			$qry.= " AND standort_id IS NOT NULL";
		}

		$qry.= $this->advancedFilterSql($params);

		if(!$isMitarbeiter) { // students are only allowed to get a subset defined by config
			$qry.= ' AND raumtyp_kurzbz IN ?';
			$params[] = $this->config->item('roomtypes_student');
			$this->addMeta('config', $this->config->item('roomtypes_student'));
		}
		
		$qry.= " AND (max_person>= ? OR max_person is null)";
		$params[] = $personenanzahl;

		$qry.="	AND ort_kurzbz NOT IN 
			(
				SELECT ort_kurzbz FROM lehre.tbl_stundenplandev WHERE datum = ? AND stunde >= ? AND stunde <= ? 
				UNION 
				SELECT ort_kurzbz FROM campus.tbl_reservierung WHERE datum= ? AND stunde >= ? AND stunde <= ?
			)
		";
		$params = array_merge($params, [$datum, $vonStunde, $bisStunde, $datum, $vonStunde, $bisStunde]);
//		$this->addMeta('qry', $qry);
//		$this->addMeta('params', $params);
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
	 * Standorte of the rooms that pass the base filter of getRooms
	 */
	public function getStandorte()
	{
		$qry = "SELECT DISTINCT standort_id, tbl_standort.bezeichnung
			FROM public.tbl_ort JOIN public.tbl_standort USING(standort_id)
			WHERE aktiv AND reservieren
			ORDER BY tbl_standort.bezeichnung";

		$result = $this->OrtModel->execReadOnlyQuery($qry);

		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	/**
	 * Values of the select filters: column => distinct values of the rooms that pass the base filter of getRooms
	 */
	public function getAdvancedFilterOptions()
	{
		$options = [];
		foreach (self::ADVANCED_FILTERS as $column => $type) {
			if ($type != 'select')
				continue;

			$result = $this->OrtModel->execReadOnlyQuery("SELECT DISTINCT ".$column." AS value
				FROM public.tbl_ort
				WHERE aktiv AND reservieren AND ".$column." <> ''
				ORDER BY ".$column);
			$options[$column] = array_column($this->getDataOrTerminateWithError($result), 'value');
		}

		$this->terminateWithSuccess($options);
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

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * SQL conditions of the advanced filters in the GET parameters, adds their values to $params
	 */
	private function advancedFilterSql(&$params)
	{
		// no xss_clean: the values are bound, and it could change a select value so that it no longer equals the column
		$sql = '';
		foreach (self::ADVANCED_FILTERS as $column => $type) {
			if ($type == 'range') {
				$min = $this->input->get($column.'_min');
				$max = $this->input->get($column.'_max');
				if ($min !== null && $min !== '') {
					$sql.= " AND tbl_ort.".$column." >= ?::numeric";
					$params[] = $min;
				}
				if ($max !== null && $max !== '') {
					$sql.= " AND tbl_ort.".$column." <= ?::numeric";
					$params[] = $max;
				}
				continue;
			}

			$value = $this->input->get($column);
			if ($value === null || $value === '')
				continue;

			if ($type == 'flag') {
				$sql.= " AND tbl_ort.".$column;
			} elseif ($type == 'exists') {
				$sql.= " AND tbl_ort.".$column." IS NOT NULL";
			} elseif ($type == 'select') {
				$sql.= " AND tbl_ort.".$column." = ?";
				$params[] = $value;
			} else {
				$sql.= " AND strpos(lower(tbl_ort.".$column."), lower(?)) > 0";
				$params[] = $value;
			}
		}
		return $sql;
	}
}

