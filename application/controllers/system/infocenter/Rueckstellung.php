<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');


class Rueckstellung extends Auth_Controller
{
	private $_ci; // Code igniter instance
	private $_uid;

	public function __construct()
	{
		parent::__construct(
			array(
				'get' => array('infocenter:r', 'lehre/zgvpruefung:r'),
				'set' => array('infocenter:r', 'lehre/zgvpruefung:r'),
				'delete' => array('infocenter:r', 'lehre/zgvpruefung:r'),
				'getStatus' => array('infocenter:rw', 'lehre/zgvpruefung:rw'),
				'getStudienjahrEnd' => array('infocenter:r', 'lehre/zgvpruefung:r'),
			)
		);
		
		$this->load->model('crm/Rueckstellung_model', 'RueckstellungModel');
		$this->load->model('crm/RueckstellungStatus_model', 'RueckstellungStatusModel');
		
		$this->_setAuthUID(); // sets property uid
		
		$this->_ci =& get_instance(); // get code igniter instance
	}
	
	public function get($person_id)
	{
		$result = null;
		$rueckstellung = $this->_ci->RueckstellungModel->getByPersonId($person_id);
		
		if (isError($rueckstellung))
			$this->terminateWithJsonError($this->_ci->p->t('ui', 'fehlerBeimLesen'));
		
		if (hasData($rueckstellung))
			$result = getData($rueckstellung)[0];
		
		$this->outputJsonSuccess($result);
	}
	
	public function set()
	{
		$person_id = $this->input->post('person_id');
		$datum_bis = $this->input->post('datum_bis');
		$status_kurzbz = $this->input->post('status_kurzbz');

		$result = $this->_ci->RueckstellungModel->set($person_id, date_format(date_create($datum_bis), 'Y-m-d'), $status_kurzbz, $this->_uid);
		
		$this->outputJson($result);
	}
	
	public function delete()
	{
		$person_id = $this->input->post('person_id');
		$status = $this->input->post('status');
		
		$rueckstellungd = $this->_ci->RueckstellungModel->getByPersonId($person_id, $status);
		
		if (isError($rueckstellungd))
			$this->terminateWithJsonError($this->p->t('ui', 'fehlerBeimLesen'));
		
		
		if (hasData($rueckstellungd))
		{
			$rueckstellungd = getData($rueckstellungd)[0];
			$result = $this->_ci->RueckstellungModel->delete($rueckstellungd->rueckstellung_id);
			
			if (isError($result))
				$this->terminateWithJsonError($this->_ci->p->t('ui', 'fehlerBeimSpeichern'));
			
			$this->outputJson($result);
		}
	}
	
	public function getStatus($aktiv = true)
	{
		$result = $this->_ci->RueckstellungStatusModel->loadWhere(array('aktiv' => $aktiv));
		
		if (isError($result))
			$this->terminateWithJsonError($this->_ci->p->t('ui', 'fehlerBeimLesen'));
		
		$this->outputJsonSuccess(getData($result));
	}
	
	/**
	 * Gets the End date of the current Studienjahr
	 */
	public function getStudienjahrEnd()
	{
		$this->load->model('organisation/studienjahr_model', 'StudienjahrModel');
		
		$result = $this->_ci->StudienjahrModel->getCurrStudienjahr();
		
		$json = null;
		
		if (hasData($result))
		{
			$json = $result->retval[0]->ende;
		}
		
		$this->outputJsonSuccess(array($json));
	}
	
	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();
		
		if (!$this->_uid) show_error('User authentification failed');
	}
}