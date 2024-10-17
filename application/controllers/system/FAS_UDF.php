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
		$person_id = $this->input->get('person_id');
		$prestudent_id = $this->input->get('prestudent_id');

		$data = array();

		if (isset($person_id) && is_numeric($person_id))
		{
			if ($this->PersonModel->udfsExistAndDefined())
			{
				$personUdfs = $this->PersonModel->getUDFs($person_id);
				$data['person_id'] = $person_id;
				$data['personUdfs'] = $personUdfs;
			}
		}

		if (isset($prestudent_id) && is_numeric($prestudent_id))
		{
			if ($this->PrestudentModel->udfsExistAndDefined())
			{
				$prestudentUdfs = $this->PrestudentModel->getUDFs($prestudent_id);
				$data['prestudent_id'] = $prestudent_id;
				$data['prestudentUdfs'] = $prestudentUdfs;
			}
		}

		$this->load->view('system/fas_udf', $data);
	}
}

