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
		parent::__construct([
			'getAllRooms' => array('basis/ort:r'),
			'getRooms' => self::PERM_LOGGED,
			'getTypes' => self::PERM_LOGGED,
			'ContentID' => self::PERM_LOGGED,
			'getOrtKurzbzContent' => self::PERM_LOGGED,
			'getRoom' => self::PERM_LOGGED,
			'createRoom' => array('basis/ort:rw'),
			'updateRoom' => array('basis/ort:rw'),
			'deleteRoom' => array('basis/ort:rw'),
		]);

		$this->load->library('form_validation');
		$this->load->library('requests/RoomRequest');

		$this->load->model('ressource/Ort_model', 'OrtModel');
		$this->load->model('ressource/Reservierung_model', 'ReservierungModel');

		$this->config->load('raumsuche');

		$this->loadPhrases([
			'global',
			'ui',
			'lehre',
			'gruppenmanagement',
			'person',
		]);

		
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getAllRooms()
	{
		$paginationSize = $this->input->get('pagination[size]', TRUE);
		$paginationPage = $this->input->get('pagination[page]', TRUE);

		$filter = $this->input->get('filter', TRUE);
		
		$filterData = [];


		$query = "SELECT
				COUNT(*) OVER() AS full_count,
				public.tbl_ort.*, 
				org.bezeichnung as org_bezeichnung,
				org.organisationseinheittyp_kurzbz as org_organisationseinheittyp_kurzbz
			FROM public.tbl_ort 
			LEFT JOIN public.tbl_ort as pr ON pr.ort_kurzbz = public.tbl_ort.parent_ort_kurzbz
			LEFT JOIN public.tbl_organisationseinheit as org ON org.oe_kurzbz = public.tbl_ort.oe_kurzbz";


		$queryWhereFragments = [];

		$searchableIdAttributes = ['standort_id', 'gebteil', 'oe_kurzbz'];
		$searchableTextAttributes = ['ort_kurzbz', 'parent_ort_kurzbz', 'bezeichnung', 'planbezeichnung', 'oe_bezeichnung'];
		$searchableBooleanAttributes = ['lehre', 'reservieren', 'aktiv'];
		$searchableNumericAttributes = ['max_person', 'arbeitsplaetze', 'kosten', 'stockwerk'];
		$searchableNumericSpanAttributes = ['m2'];
		$searchableCustomAttributes = [
			[
				'raw_sql_fragment' => "CONCAT(public.tbl_ort.ort_kurzbz, ' - ', public.tbl_ort.bezeichnung)",
				'filter_parameter' => 'ort_kurzbz_bezeichnung_concat',
			], 
			[
				'raw_sql_fragment' => "CONCAT('[', org.organisationseinheittyp_kurzbz, '] ', org.bezeichnung)",
				'filter_parameter' => 'org_organisationseinheittyp_kurzbz_org_bezeichnung_concat',
			]
		];

		foreach ($searchableIdAttributes as $attribute) {
			if (isset($filter[$attribute]) && $filter[$attribute] !== '') {
				$queryWhereFragments[] = "public.tbl_ort.$attribute = ?";
				$filterData[] = trim($filter[$attribute]);
			}
		}

		foreach ($searchableTextAttributes as $attribute) {
			$tableAttribute = "public.tbl_ort.$attribute";
			if ($attribute === 'oe_bezeichnung') {
				$tableAttribute = "org.bezeichnung";
			}
			if (isset($filter[$attribute]) && $filter[$attribute] !== '') {
				$queryWhereFragments[] = "$tableAttribute ILIKE ?";
				$filterData[] = '%' . trim($filter[$attribute]) . '%';
			}
		}

		foreach ($searchableBooleanAttributes as $attribute) {
			if (isset($filter[$attribute]) && $filter[$attribute] !== '') {
				$queryWhereFragments[] = "public.tbl_ort.$attribute = ?";
				$filterData[] = $filter[$attribute] === 'true' ? true : false;
			}
		}

		foreach ($searchableNumericAttributes as $attribute) {
			if (isset($filter[$attribute]) && $filter[$attribute] !== '') {
				$queryWhereFragments[] = "public.tbl_ort.$attribute = ?";
				$filterData[] = trim($filter[$attribute]);
			}
		}

		foreach ($searchableNumericSpanAttributes as $attribute) {
			if (isset($filter[$attribute]) && $filter[$attribute] !== '') {
				$queryWhereFragments[] = "public.tbl_ort.$attribute >= ? AND public.tbl_ort.$attribute <= ?";
				$filterData[] = trim($filter[$attribute]) - 1;
				$filterData[] = trim($filter[$attribute]) + 1;
			}
		}

		foreach ($searchableCustomAttributes as $customAttribute) {
			if (isset($filter[$customAttribute['filter_parameter']]) && $filter[$customAttribute['filter_parameter']] !== '') {
				$queryWhereFragments[] = $customAttribute['raw_sql_fragment'] . " ILIKE ?";
				$filterData[] = '%' . trim($filter[$customAttribute['filter_parameter']]) . '%';
			}
		}

		if (count($queryWhereFragments) > 0) {
			$query .= ' WHERE ' . implode(' AND ', $queryWhereFragments);
		}

		$sortableAttributes = ['ort_kurzbz', 'bezeichnung', 'planbezeichnung', 'max_person', 'arbeitsplaetze', 'm2', 'lehre', 'reservieren', 'aktiv', 'stockwerk', 'kosten', 'parent_ort_kurzbz', 'org_bezeichnung'];
		$sortableConcatAttributes = [
			[
				'raw_sql_fragment' => "CONCAT('[', org.organisationseinheittyp_kurzbz, '] ', org.bezeichnung)",
				'sort_parameter' => 'org_organisationseinheittyp_kurzbz_org_bezeichnung_concat',
			]
		];
		$sorter = $this->input->get('sort', TRUE);

		foreach ($sortableAttributes as $attribute) {
			if (isset($sorter[$attribute]) && in_array(strtolower($sorter[$attribute]), ['asc', 'desc'])) {
				if ($attribute === 'org_bezeichnung') {
					$query .= " ORDER BY org.bezeichnung " . strtoupper($sorter[$attribute]);
				} else {
					$query .= " ORDER BY public.tbl_ort.$attribute " . strtoupper($sorter[$attribute]);
				}
			}
		}

		foreach ($sortableConcatAttributes as $customAttribute) {
			if (isset($sorter[$customAttribute['sort_parameter']]) && in_array(strtolower($sorter[$customAttribute['sort_parameter']]), ['asc', 'desc'])) {
				$query .= " ORDER BY " . $customAttribute['raw_sql_fragment'] . " " . strtoupper($sorter[$customAttribute['sort_parameter']]);
			}
		}

		if (!isset($sorter)) {
			$query .= ' ORDER BY public.tbl_ort.ort_kurzbz ASC';
		}

		if ($paginationSize && $paginationPage) {
			$query .= " LIMIT ? OFFSET ?";
		}

		$queryData = array_merge($filterData);
		if ($paginationSize && $paginationPage) {
			$queryData = array_merge($filterData, [$paginationSize, ($paginationPage - 1) * $paginationSize]);
		}

		$result = $this->OrtModel->execReadOnlyQuery($query, $queryData);
 
		$queryData = hasData($result) ? getData($result) : [];
		
		if ($paginationSize && $paginationPage) {
			$totalItems = count($queryData) > 0 ? $queryData[0]->full_count : 0;
			$pageCount = ceil($totalItems / $paginationSize);
			$this->addTabulatorPaginationData($pageCount); 
		}
		
		$this->terminateWithSuccess($queryData);
	}

	/**
	 * Retrieves all Ort entries filtered by the provided parameters
	 */
	public function getRooms()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('datum','Datum','required');
		$this->form_validation->set_rules('von','Uhrzeit Von','required|regexresponse_match[/^[0-9]{2}:[0-9]{2}$/]');
		$this->form_validation->set_rules('bis','Uhrzeit Bis','required|regex_match[/^[0-9]{2}:[0-9]{2}$/]');
		if($this->form_validation->run() == FALSE) {
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}
		
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

	public function getRoom($ort_kurzbz)
	{
		$result = $this->OrtModel->load($ort_kurzbz);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$result = hasData($result) ? current(getData($result)) : null;

		return $this->terminateWithSuccess($result);
	}

	public function createRoom()
	{
		
		if (!$this->roomrequest->validate()) {
			$this->terminateWithValidationErrors($this->roomrequest->errors());
			return;
		}

		$this->db->trans_start();

		$data = [
			"parent_ort_kurzbz" => $this->input->post('parent_ort_kurzbz'),
			"oe_kurzbz" => $this->input->post('oe_kurzbz'),
			"content_id" => !empty($this->input->post('content_id')) ? $this->input->post('content_id') : null,
			"standort_id" => $this->input->post('standort_id'),
			"ort_kurzbz" => $this->input->post('ort_kurzbz'),
			"bezeichnung" => $this->input->post('bezeichnung'),
			"planbezeichnung" => $this->input->post('planbezeichnung'),
			"aktiv" => $this->input->post('aktiv') ? true : false,
			"lehre" => $this->input->post('lehre') ? true : false,
			"reservieren" => $this->input->post('reservieren') ? true : false,
			"max_person" => $this->input->post('max_person'),
			"stockwerk" => $this->input->post('stockwerk'),
			"lageplan" => $this->input->post('lageplan'),
			"dislozierung" => $this->input->post('dislozierung'),
			"kosten" => $this->input->post('kosten'),
			"ausstattung" => $this->input->post('ausstattung'),
			"telefonklappe" => $this->input->post('telefonklappe'),
			"m2" => $this->input->post('m2'),
			"gebteil" => $this->input->post('gebteil'),
			"arbeitsplaetze" => $this->input->post('arbeitsplaetze'),
			'insertamum' => date('c'),
			'insertvon' => getAuthUid(),
			'updateamum' => date('c'),
			'updatevon' => getAuthUid()
		];

		$this->OrtModel->db->set($data);
		$result = $this->OrtModel->db->insert($this->OrtModel->getDbTable());

		$this->db->trans_complete();


		return $this->terminateWithSuccess($result);
	}

	public function updateRoom($ort_kurzbz)
	{
		if (!$this->roomrequest->validate("update")) {
			$this->terminateWithValidationErrors($this->roomrequest->errors());
			return;
		}

		$this->db->trans_start();

		$fields = [
			"parent_ort_kurzbz",
			"oe_kurzbz",
			"content_id",
			"standort_id",
			"bezeichnung",
			"planbezeichnung",
			"aktiv",
			"lehre",
			"reservieren",
			"max_person",
			"stockwerk",
			"lageplan",
			"dislozierung",
			"kosten",
			"ausstattung",
			"telefonklappe",
			"m2",
			"gebteil",
			"arbeitsplaetze"
		];

		foreach ($fields as $field) {
			if (array_key_exists($field, $this->input->post())) {
				$data[$field] = $this->input->post($field);
			}
		}

		$data['updateamum'] = date('c');
		$data['updatevon'] = getAuthUid();

		$this->OrtModel->db->set($data);
		$this->OrtModel->db->where('ort_kurzbz', $ort_kurzbz);
		$result = $this->OrtModel->db->update($this->OrtModel->getDbTable());

		$this->db->trans_complete();


		return $this->terminateWithSuccess($result);
	}

	public function deleteRoom($ort_kurzbz)
	{

		$this->db->trans_start();

		$reservationsQuery = "SELECT COUNT(*) FROM campus.tbl_reservierung WHERE ort_kurzbz = ?";
		$reservationsResult = $this->OrtModel->execReadOnlyQuery($reservationsQuery, [$ort_kurzbz]);
		if (isError($reservationsResult)) {
			$this->terminateWithError(getError($reservationsResult), self::ERROR_TYPE_GENERAL);
		}

		$reservationsCount = hasData($reservationsResult) ? getData($reservationsResult)[0]->count : 0;
		if ($reservationsCount > 0) {
			$this->terminateWithError("Cannot delete room with shortcode $ort_kurzbz because there are existing reservations for this room", self::ERROR_TYPE_GENERAL);
		}

		$softwareImageOrtQuery = "SELECT COUNT(*) FROM extension.tbl_softwareimage_ort WHERE ort_kurzbz = ?";
		$softwareImageOrtResult = $this->OrtModel->db->query($softwareImageOrtQuery, [$ort_kurzbz]);
		if ($softwareImageOrtResult === false) {
			$this->terminateWithError($this->p->t('ui', 'error_existingSoftwareImageForRoomTypeUponDeletion'), self::ERROR_TYPE_GENERAL);
		}

		$softwareImageOrtCount = $softwareImageOrtResult->row()->count;
		if ($softwareImageOrtCount > 0) {
			$this->terminateWithError($this->p->t('ui', 'error_existingReservationsForRoomsUponDeletion'), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->OrtModel->delete([
			"ort_kurzbz" => $ort_kurzbz
		]);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$this->db->trans_complete();

		return $this->terminateWithSuccess(true);
	}
}

