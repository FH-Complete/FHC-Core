<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Reservierung extends FHCAPI_Controller
{
	private $_ci;

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'addReservierung' => 'lehre/lvplan:rw',
			'getRollen' => 'lehre/lvplan:rw',
			'getInformation' => 'lehre/lvplan:rw',
			'getLektor' => 'lehre/lvplan:rw',
			'searchGroup' => 'lehre/lvplan:rw',
		]);

		$this->_ci =& get_instance();

		$this->_ci->load->library('LogLib');
		$this->_ci->load->library('form_validation');
		$this->_ci->load->library('KalenderLib');

		$this->_ci->load->model('ressource/Ort_model', 'OrtModel');
		$this->_ci->load->model('ressource/Kalender_Event_Rolle_model', 'KalenderEventRolleModel');
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->_ci->load->model('organisation/gruppe_model', 'GruppeModel');


		$this->loadPhrases([
			'ui'
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getInformation()
	{
		$return_array = array('berechtigt' => false, 'studiengaenge' => []);

		$this->_ci->OrtModel->db->join("
					(select ort,standort_id,strasse, plz
					FROM public.tbl_standort
					LEFT JOIN public.tbl_adresse USING(adresse_id)
				) standort", "standort_id", "LEFT", false);

		$raeume = $this->_ci->OrtModel->loadWhere(array('aktiv' => true, 'reservieren' => true));
		$return_array['raeume'] = hasData($raeume) ? getData($raeume) : [];

		if (!$this->_ci->permissionlib->isBerechtigt('lehre/reservierung'))
			$this->terminateWithSuccess($return_array);

		$stg_berechtigungen = $this->_ci->permissionlib->getSTG_isEntitledFor('lehre/reservierung');
		if (isEmptyArray($stg_berechtigungen))
			$this->terminateWithSuccess($return_array);

		$this->_ci->StudiengangModel->addSelect('studiengang_kz, UPPER(CONCAT(typ, kurzbz)) as kuerzel, kurzbzlang');
		$this->_ci->StudiengangModel->addOrder('typ, kurzbz');
		$this->_ci->StudiengangModel->db->where_in('studiengang_kz', $stg_berechtigungen);
		$studiengaenge = $this->_ci->StudiengangModel->loadWhere(array('aktiv' => true));

		if (isError($studiengaenge))
			$this->terminateWithError(getError($studiengaenge));
		$language = getUserLanguage() == 'German' ? 0 : 1;
		$this->_ci->KalenderEventRolleModel->addOrder('sort');
		$this->_ci->KalenderEventRolleModel->addSelect('rolle_kurzbz, array_to_json(bezeichnung_mehrsprachig::varchar[])->>'. $language. ' as bezeichnung');

		$rollen = $this->_ci->KalenderEventRolleModel->load();

		$this->_ci->StudiensemesterModel->addOrder('start', 'DESC');
		$studiensemester = $this->_ci->StudiensemesterModel->load();

		$return_array['studiengaenge'] = hasData($studiengaenge) ? getData($studiengaenge) : [];
		$return_array['berechtigt'] = true;
		$return_array['rollen'] = hasData($rollen) ? getData($rollen) : [];
		$return_array['studiensemester'] = hasData($studiensemester) ? getData($studiensemester) : [];


		$this->terminateWithSuccess($return_array);
	}
	public function getRaeume()
	{

		$this->_ci->OrtModel->db->join("
					(select ort,standort_id,strasse, plz
					FROM public.tbl_standort
					LEFT JOIN public.tbl_adresse USING(adresse_id)
				) standort", "standort_id", "LEFT", false);

		$result = $this->_ci->OrtModel->loadWhere(array('aktiv' => true, 'reservieren' => true));

		$this->terminateWithSuccess(hasData($result) ? getData($result) : []);
	}

	public function searchGroup()
	{
		$query = $this->input->get('query');
		if (is_null($query))
			$this->terminateWithError($this->_ci->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$stg_berechtigungen = $this->_ci->permissionlib->getSTG_isEntitledFor('lehre/reservierung');

		if (isEmptyArray($stg_berechtigungen))
			$this->terminateWithSuccess([]);


		$query_words = explode(' ', urldecode($query));

		$gruppen_result = $this->_ci->GruppeModel->search($query_words);

		if (isError($gruppen_result))
			$this->terminateWithError(getError($gruppen_result), self::ERROR_TYPE_GENERAL);

		$gruppen_array = array();

		if (hasData($gruppen_result))
			$gruppen_array = getData($gruppen_result);

		$lehrverband_result = $this->_ci->LehrverbandModel->search($query_words);

		$lehrverband_array = array();

		if (isError($lehrverband_result))
			$this->terminateWithError(getError($lehrverband_result), self::ERROR_TYPE_GENERAL);

		if (hasData($lehrverband_result))
			$lehrverband_array = getData($lehrverband_result);

		$all_gruppen = array_merge($gruppen_array, $lehrverband_array);

		$gefilterte_gruppen = array_filter($all_gruppen, function($gruppe) use ($stg_berechtigungen)
		{
			return in_array($gruppe->studiengang_kz, $stg_berechtigungen);
		});

		$this->terminateWithSuccess($gefilterte_gruppen);
	}

	public function getRollen()
	{
		$language = getUserLanguage() == 'German' ? 0 : 1;

		$this->_ci->KalenderEventRolleModel->addOrder('sort');
		$this->_ci->KalenderEventRolleModel->addSelect('rolle_kurzbz, array_to_json(bezeichnung_mehrsprachig::varchar[])->>'. $language. ' as bezeichnung');

		$result = $this->_ci->KalenderEventRolleModel->load();

		$this->terminateWithSuccess(hasData($result) ? getData($result) : []);
	}
	public function addReservierung()
	{

		$this->_ci->form_validation->set_data($_POST);
		$this->_ci->form_validation->set_rules('titel',"titel","required");
		$this->_ci->form_validation->set_rules('beschreibung',"beschreibung","required");
		$this->_ci->form_validation->set_rules('ort_kurzbz',"ort_kurzbz","required");
		$this->_ci->form_validation->set_rules('start_date',"start_date","required");
		$this->_ci->form_validation->set_rules('end_date',"end_date","required");


		if($this->_ci->form_validation->run() === FALSE)
			$this->terminateWithValidationErrors($this->_ci->form_validation->error_array());

		$titel = $this->_ci->input->post('titel', TRUE);
		$beschreibung = $this->_ci->input->post('beschreibung', TRUE);
		$ort_kurzbz = $this->_ci->input->post('ort_kurzbz', TRUE);
		$start_date = $this->_ci->input->post('start_date', TRUE);
		$end_date = $this->_ci->input->post('end_date', TRUE);
		$teilnehmer = $this->_ci->input->post('teilnehmer', TRUE);
		$specialGroups = $this->_ci->input->post('specialGroups', TRUE);
		$specialFinalGroups = $this->_ci->input->post('specialFinalGroups', TRUE);
		$groups = $this->_ci->input->post('groups', TRUE);

		if ($this->_ci->permissionlib->isBerechtigt('lehre/reservierung'))
		{
			if (empty($teilnehmer) || !is_array($teilnehmer))
			{
				$teilnehmer[] = array('uid' => getAuthUID(), 'rolle' => 'organisator');
			}
		}
		else
			$teilnehmer[] = array('uid' => getAuthUID(), 'rolle' => 'organisator');


		$result = $this->_ci->kalenderlib->addReservierung($titel, $beschreibung, $ort_kurzbz, $start_date, $end_date, $teilnehmer, $specialFinalGroups, $specialGroups, $groups);


		if (isError($result))
			$this->terminateWithError(getError($result));

		$this->terminateWithSuccess($result);
	}


	public function getLektor()
	{
		$query = $this->input->get('query');

		if (is_null($query))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$query_words = explode(' ', $query);

		$this->_ci->MitarbeiterModel->addSelect('uid, person_id, vorname, nachname');
		$this->_ci->MitarbeiterModel->addJoin('public.tbl_benutzer', 'uid = mitarbeiter_uid');
		$this->_ci->MitarbeiterModel->addJoin('public.tbl_person', 'person_id');

		$this->_ci->MitarbeiterModel->db->where('public.tbl_benutzer.aktiv', true);

		$this->_ci->MitarbeiterModel->db->group_start();
		foreach ($query_words as $word)
		{
			$this->_ci->MitarbeiterModel->db->group_start();
			$this->_ci->MitarbeiterModel->db->where('tbl_person.vorname ILIKE', "%" . $word . "%");
			$this->_ci->MitarbeiterModel->db->or_where('tbl_person.nachname ILIKE', "%" . $word . "%");
			$this->_ci->MitarbeiterModel->db->or_where('uid ILIKE', "%" . $word . "%");
			$this->_ci->MitarbeiterModel->db->group_end();
		}
		$this->_ci->MitarbeiterModel->db->group_end();
		$this->_ci->MitarbeiterModel->addOrder('nachname');
		$this->_ci->MitarbeiterModel->addOrder('vorname');
		$result = $this->_ci->MitarbeiterModel->load();
		$this->terminateWithSuccess(hasData($result) ? getData($result) : array());
	}

}
