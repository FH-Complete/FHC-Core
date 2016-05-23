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

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Studienplan extends APIv1_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('organisation/studienplan_model', 'StudienplanModel');
		// Load set the uid of the model to let to check the permissions
		$this->StudienplanModel->setUID($this->_getUID());
	}
	
	public function getStudienplaene()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		
		if (isset($studiengang_kz))
		{
			$result = $this->StudienplanModel->addJoin('lehre.tbl_studienordnung', 'studienordnung_id');
			if ($result->error == EXIT_SUCCESS)
			{
				$result = $this->StudienplanModel->loadWhere(array('studiengang_kz' => $this->get('studiengang_kz')));
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	public function getStudienplaeneFromSem()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$ausbildungssemester = $this->get('ausbildungssemester');
		$orgform_kurzbz = $this->get('orgform_kurzbz');
		
		if (isset($studiengang_kz) && isset($studiensemester_kurzbz))
		{
			$result = $this->StudienplanModel->addJoin('lehre.tbl_studienordnung', 'studienordnung_id');
			if ($result->error == EXIT_SUCCESS)
			{
				$result = $this->StudienplanModel->addJoin('lehre.tbl_studienplan_semester', 'studienplan_id');
				if ($result->error == EXIT_SUCCESS)
				{
					$whereArray = array('tbl_studienplan.aktiv' => 'TRUE',
										'tbl_studienordnung.studiengang_kz' => $studiengang_kz,
										'tbl_studienplan_semester.studiensemester_kurzbz' => $studiensemester_kurzbz
									);
					
					if(isset($ausbildungssemester))
					{
						$whereArray['tbl_studienplan_semester.semester'] = $ausbildungssemester;
					}
					
					if(isset($orgform_kurzbz))
					{
						$whereArray['orgform_kurzbz'] = $orgform_kurzbz;
					}
					
					$result = $this->StudienplanModel->loadWhere($whereArray);
				}
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
}