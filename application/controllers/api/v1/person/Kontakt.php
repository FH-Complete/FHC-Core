<?php

/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Kontakt extends APIv1_Controller
{
    /**
     * Person API constructor.
     */
    public function __construct()
    {
		parent::__construct();
		// Load model PersonModel
		$this->load->model('person/kontakt_model', 'KontaktModel');
		// Load set the uid of the model to let to check the permissions
		$this->KontaktModel->setUID($this->_getUID());
    }

	public function getKontakt()
    {
		$kontakt_id = $this->get("kontakt_id");
		
		if (isset($kontakt_id))
		{
			$result = $this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT');
			if ($result->error == EXIT_SUCCESS)
			{
				$result = $this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT');
				if ($result->error == EXIT_SUCCESS)
				{
					$result = $this->KontaktModel->loadWhere(array('kontakt_id' => $kontakt_id));
				}
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
    }
	
	public function getOnlyKontakt()
    {
		$kontakt_id = $this->get("kontakt_id");
		
		if (isset($kontakt_id))
		{
			$result = $this->KontaktModel->loadWhere(array('kontakt_id' => $kontakt_id));
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
    }
	
	public function getKontaktByPersonID()
    {
		$person_id = $this->get("person_id");
		
		if (isset($person_id))
		{
			$result = $this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT');
			if ($result->error == EXIT_SUCCESS)
			{
				$result = $this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT');
				if ($result->error == EXIT_SUCCESS)
				{
					$result = $this->KontaktModel->loadWhere(array('person_id' => $person_id));
				}
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
    }
	
	public function getKontaktByPersonIDKontaktTyp()
    {
		$person_id = $this->get("person_id");
		$kontakttyp = $this->get("kontakttyp");
		
		if (isset($person_id) && isset($kontakttyp))
		{
			$result = $this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT');
			if ($result->error == EXIT_SUCCESS)
			{
				$result = $this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT');
				if ($result->error == EXIT_SUCCESS)
				{
					$result = $this->KontaktModel->loadWhere(array('person_id' => $person_id, 'kontakttyp' => $kontakttyp));
				}
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
    }
    
    public function postKontakt()
    {
		$post = $this->_parseData($this->post());
		
		if (is_array($post))
		{
			if (isset($post['kontakt_id']))
			{
				$result = $this->KontaktModel->update($post['kontakt_id'], $post);
			}
			else
			{
				$result = $this->KontaktModel->insert($post);
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
    }
}