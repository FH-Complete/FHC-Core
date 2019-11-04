<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FAS_UDF extends Auth_Controller
{
	const FAS_UDF_SESSION_NAME = 'fasUdfSessionName';

	public function __construct()
    {
        parent::__construct(
			array(
				'index' => 'basis/person:r',
				'saveUDF' => 'basis/person:rw'
			)
		);

		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
    }

	/**
	 *
	 */
	public function index()
	{
		$fasUdfSession = getSession(self::FAS_UDF_SESSION_NAME);

		$person_id = $this->input->get('person_id');
		if (isset($fasUdfSession['person_id']))
		{
			if (!isset($person_id))
			{
				$person_id = $fasUdfSession['person_id'];
			}
			unset($fasUdfSession['person_id']);
		}

		$prestudent_id = $this->input->get('prestudent_id');
		if (isset($fasUdfSession['prestudent_id']))
		{
			if (!isset($prestudent_id))
			{
				$prestudent_id = $fasUdfSession['prestudent_id'];
			}
			unset($fasUdfSession['prestudent_id']);
		}

		$result = null;
		if (isset($fasUdfSession['result']))
		{
			$result = clone $fasUdfSession['result'];
			setSessionElement(self::FAS_UDF_SESSION_NAME, 'result', null);
		}

		$data = array('result' => $result);

		if (isset($person_id) && is_numeric($person_id))
		{
			if ($this->PersonModel->hasUDF())
			{
				$personUdfs = $this->PersonModel->getUDFs($person_id);
				$personUdfs['person_id'] = $person_id;
				$data['personUdfs'] = $personUdfs;
			}
		}

		if (isset($prestudent_id) && is_numeric($prestudent_id))
		{
			if ($this->PrestudentModel->hasUDF())
			{
				$prestudentUdfs = $this->PrestudentModel->getUDFs($prestudent_id);
				$prestudentUdfs['prestudent_id'] = $prestudent_id;
				$data['prestudentUdfs'] = $prestudentUdfs;
			}
		}

		$this->load->view('system/fas_udf', $data);
	}

	/**
	 *
	 */
	public function saveUDF()
	{
		$udfs = $this->input->post();
		$validation = $this->_validate($udfs);

		$userdata = array(
			'person_id' => $this->input->post('person_id'),
			'prestudent_id' => $this->input->post('prestudent_id')
		);

		if (isSuccess($validation))
		{
			// Load model UDF_model
			$this->load->model('system/FAS_UDF_model', 'FASUDFModel');

			$result = $this->FASUDFModel->saveUDFs($udfs);

			$userdata['result'] = $result;
		}
		else
		{
			$userdata['result'] = $validation;
		}

		setSessionElement(self::FAS_UDF_SESSION_NAME, 'person_id', $userdata['person_id']);
		setSessionElement(self::FAS_UDF_SESSION_NAME, 'prestudent_id', $userdata['prestudent_id']);
		setSessionElement(self::FAS_UDF_SESSION_NAME, 'result', $userdata['result']);

		redirect('system/FAS_UDF');
	}

	/**
	 *
	 */
	private function _validate($udfs)
	{
		$validation = error('person_id or prestudent_id is missing');

		if((isset($udfs['person_id']) && !(is_null($udfs['person_id'])) && ($udfs['person_id'] != ''))
			|| (isset($udfs['prestudent_id']) && !(is_null($udfs['prestudent_id'])) && ($udfs['prestudent_id'] != '')))
		{
			$validation = success(true);
		}

		return $validation;
	}
}
