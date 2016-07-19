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

class Studiengang2 extends APIv1_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('organisation/studiengang_model', 'StudiengangModel');
	}
	
	public function getStudiengang()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		
		if (isset($studiengang_kz))
		{
			$result = $this->StudiengangModel->load($studiengang_kz);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	public function getAllForBewerbung()
	{
		$this->response($this->StudiengangModel->getAllForBewerbung(), REST_Controller::HTTP_OK);
	}
	
	public function getStudiengangStudienplan()
	{
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		$ausbildungssemester = $this->get('ausbildungssemester');
		
		if (isset($studiensemester_kurzbz) && isset($ausbildungssemester))
		{
			$this->load->model('organisation/studienplan_model', 'StudienplanModel');
			$result = $this->StudienplanModel->addJoin('lehre.tbl_studienplan_semester', 'studienplan_id');
			if ($result->error == EXIT_SUCCESS)
			{
				$result = $this->StudienplanModel->addJoin('lehre.tbl_studienordnung', 'studienordnung_id');
				if ($result->error == EXIT_SUCCESS)
				{
					$this->StudienplanModel->addSelect('tbl_studienplan.*, lehre.tbl_studienordnung.studiengang_kz');
					
					$this->StudienplanModel->addOrder('lehre.tbl_studienordnung.studiengang_kz');
					
					$resultStudienplan = $this->StudienplanModel->loadWhere(
							array('semester' => $ausbildungssemester,
									'studiensemester_kurzbz' => $studiensemester_kurzbz,
									'tbl_studienplan.aktiv' => 'TRUE')
					);
					
					if (is_object($resultStudienplan) && $resultStudienplan->error == EXIT_SUCCESS &&
						is_array($resultStudienplan->retval) && count($resultStudienplan->retval) > 0)
					{
						$studiengangCount = 0;
						$prevStudiengang_kz = '';
						$studiengangArray = array();
						
						for ($i = 0; $i < count($resultStudienplan->retval); $i++)
						{
							if ($prevStudiengang_kz == $resultStudienplan->retval[$i]->studiengang_kz)
							{
								array_push($studiengangArray[$studiengangCount - 1]->studienplaene, $resultStudienplan->retval[$i]);
							}
							else
							{
								$resultStudiengang = $this->StudiengangModel->load($resultStudienplan->retval[$i]->studiengang_kz);
								if (is_object($resultStudiengang) && $resultStudiengang->error == EXIT_SUCCESS &&
									is_array($resultStudiengang->retval) && count($resultStudiengang->retval) > 0)
								{
									$resultStudiengang->retval[0]->studienplaene = array($resultStudienplan->retval[$i]);
									$studiengangArray[$studiengangCount++] = $resultStudiengang->retval[0];
								}
								$prevStudiengang_kz = $resultStudienplan->retval[$i]->studiengang_kz;
							}
						}
						
						$result = $this->_success($studiengangArray);
					}
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