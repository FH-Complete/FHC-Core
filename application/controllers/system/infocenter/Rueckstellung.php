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
				'getStatus' => array('infocenter:rw', 'lehre/zgvpruefung:rw')
			)
		);
		
		$this->load->model('crm/Rueckstellung_model', 'RueckstellungModel');
		$this->load->model('crm/RueckstellungStatus_model', 'RueckstellungStatusModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->library('PersonLogLib');

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
		{
			$rueckstellung = getData($rueckstellung)[0];
			$fullName = getData($this->_ci->PersonModel->getFullName($rueckstellung->insertvon));
			
			$result = array(
				'von' => $fullName,
				'bezeichnung' => $rueckstellung->bezeichnung,
				'bis' => $rueckstellung->datum_bis,
				'status_kurzbz' => $rueckstellung->status_kurzbz
			);

			if ($rueckstellung->status_kurzbz === 'parked' && $rueckstellung->datum_bis < date('Y-m-d'))
			{
				$this->_ci->RueckstellungModel->delete(array('person_id' => $person_id, 'status_kurzbz' => 'parked'));
				$result = null;
			}
		}

		$this->outputJsonSuccess($result);
	}
	
	public function set()
	{
		$person_id = $this->input->post('person_id');
		$datum_bis = $this->input->post('datum_bis');
		$status_kurzbz = $this->input->post('status_kurzbz');

		$result = $this->_ci->RueckstellungModel->insert(
			array('person_id' => $person_id,
				'status_kurzbz' => $status_kurzbz,
				'datum_bis' => date_format(date_create($datum_bis), 'Y-m-d'),
				'insertvon' => $this->_uid
			)
		);

		if (isError($result))
			$this->terminateWithJsonError(getError($result));
		
		$this->_log($person_id, $status_kurzbz);

		$this->outputJson($result);
	}
	
	public function delete()
	{
		$person_id = $this->input->post('person_id');
		$status = $this->input->post('status');

		$result = $this->_ci->RueckstellungModel->delete(array('person_id' => $person_id, 'status_kurzbz' => $status));

		if (isError($result))
			$this->terminateWithJsonError($this->_ci->p->t('ui', 'fehlerBeimSpeichern'));

		$this->outputJson($result);
	}
	
	public function getStatus($aktiv = true)
	{
		$this->_ci->RueckstellungStatusModel->addOrder('sort');
		$result = $this->_ci->RueckstellungStatusModel->loadWhere(array('aktiv' => $aktiv));
		
		if (isError($result))
			$this->terminateWithJsonError($this->_ci->p->t('ui', 'fehlerBeimLesen'));
		
		$this->outputJsonSuccess(getData($result));
	}

	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();
		
		if (!$this->_uid) show_error('User authentification failed');
	}
	
	private function _log($person_id, $status_kurzbz)
	{
		$message = "Person $person_id set to $status_kurzbz";
		
		$this->_ci->personloglib->log(
			$person_id,
			'Action',
			array(
				'name' => 'Person status set',
				'message' => $message,
				'success' => true
			),
			'bewerbung',
			'infocenter',
			null,
			$this->_uid
		);
	}
}