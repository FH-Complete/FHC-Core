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

class Studiensemester extends API_Controller
{
	/**
	 * Studiensemester API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'Studiensemester' => 'basis/studiensemester:rw',
				'NextStudiensemester' => 'basis/studiensemester:r',
				'All' => 'basis/studiensemester:r',
				'Akt' => 'basis/studiensemester:r',
				'AktNext' => 'basis/studiensemester:r',
				'LastOrAktSemester' => 'basis/studiensemester:r',
				'NextFrom' => 'basis/studiensemester:r',
				'Previous' => 'basis/studiensemester:r',
				'Nearest' => 'basis/studiensemester:r',
				'Finished' => 'basis/studiensemester:r',
				'Timestamp' => 'basis/studiensemester:r'
			)
		);
		// Load model StudiensemesterModel
		$this->load->model('organisation/studiensemester_model', 'StudiensemesterModel');


	}

	/**
	 * @return void
	 */
	public function getStudiensemester()
	{
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');

		if (isset($studiensemester_kurzbz))
		{
			$result = $this->StudiensemesterModel->load($studiensemester_kurzbz);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function getNextStudiensemester()
	{
		$art = $this->get('art');

		$this->StudiensemesterModel->addOrder('start');
		$this->StudiensemesterModel->addLimit(1);

		if (isset($art))
		{
			$result = $this->StudiensemesterModel->loadWhere(
				array('start >' => 'NOW()',
						'SUBSTRING(studiensemester_kurzbz FROM 1 FOR 2) = ' => $art
				)
			);
		}
		else
		{
			$result = $this->StudiensemesterModel->loadWhere(array('start >' => 'NOW()'));
		}

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getAll()
	{
		$order = $this->get('order');

		if (strcasecmp($order, 'DESC') == 0)
		{
			$this->StudiensemesterModel->addOrder('ende', 'DESC');
		}
		else
		{
			$this->StudiensemesterModel->addOrder('ende', 'ASC');
		}

		$result = $this->StudiensemesterModel->load();

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getAkt()
	{
		$result = $this->StudiensemesterModel->loadWhere(array('start <=' => 'NOW()', 'ende >=' => 'NOW()'));

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getAktNext()
	{
		$semester = $this->get('semester');

		$result = null;

		if (!is_numeric($semester))
		{
			$result = $this->StudiensemesterModel->loadWhere(array('start <=' => 'NOW()', 'ende >=' => 'NOW()'));
		}

		if (!hasData($result))
		{
			$this->StudiensemesterModel->addOrder('ende');
			$this->StudiensemesterModel->addLimit(1);

			$whereArray = array('ende >=' => 'NOW()');

			if (is_numeric($semester))
			{
				if ($semester % 2 == 0)
				{
					$ss = 'SS';
				}
				else
				{
					$ss = 'WS';
				}

				$whereArray['SUBSTRING(studiensemester_kurzbz FROM 1 FOR 2) ='] = $ss;
			}

			$result = $this->StudiensemesterModel->loadWhere($whereArray);
		}

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getLastOrAktSemester()
	{
		$result = $this->StudiensemesterModel->getLastOrAktSemester($this->get('days'));

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getNextFrom()
	{
		$result = $this->StudiensemesterModel->getNextFrom($this->get('studiensemester_kurzbz'));

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getPrevious()
	{
		$this->StudiensemesterModel->addOrder('ende', 'DESC');
		$this->StudiensemesterModel->addLimit(1);

		$result = $this->StudiensemesterModel->loadWhere(array('ende <' => 'NOW()'));

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getNearest()
	{
		$result = $this->StudiensemesterModel->getNearest($this->get('semester'));

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getFinished()
	{
		$limit = $this->get('limit');

		$this->StudiensemesterModel->addOrder('ende', 'DESC');
		$this->StudiensemesterModel->addLimit($limit);

		$result = $this->StudiensemesterModel->loadWhere(array('start <=' => 'NOW()'));

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getTimestamp()
	{
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');

		if (isset($studiensemester_kurzbz))
		{
			$result = $this->StudiensemesterModel->load($studiensemester_kurzbz);

			if (is_array($result->retval) && count($result->retval) > 0)
			{
				$studiensemester = $result->retval[0];

				if (is_object($studiensemester))
				{
					$start = "";
					if (isset($studiensemester->start))
					{
						$start = mktime(0, 0, 0,
							mb_substr($studiensemester->start, 5, 2),
							mb_substr($studiensemester->start, 8, 2),
							mb_substr($studiensemester->start, 0, 4)
						);
					}

					$ende = "";
					if (isset($studiensemester->ende))
					{
						$ende = mktime(0, 0, 0,
							mb_substr($studiensemester->ende, 5, 2),
							mb_substr($studiensemester->ende, 8, 2),
							mb_substr($studiensemester->ende, 0, 4)
						);
					}

					$result->retval = array(
						'studiensemester_kurzbz' => $studiensemester_kurzbz,
						'start' => $start,
						'ende' => $ende
					);
				}
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postStudiensemester()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studiensemester_kurzbz']))
			{
				$result = $this->StudiensemesterModel->update($this->post()['studiensemester_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->StudiensemesterModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($studiensemester = NULL)
	{
		return true;
	}
}
