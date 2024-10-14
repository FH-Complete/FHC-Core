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

class Studiensemester extends FHCAPI_Controller
{
	/**
	 * Studiensemester API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'getAll' => self::PERM_LOGGED,
				'getAktNext' => self::PERM_LOGGED
			)
		);
		// Load model StudiensemesterModel
		$this->load->model('organisation/studiensemester_model', 'StudiensemesterModel');
	}

	/**
	 * Get all Studiensemester.
	 *
	 * @param null|string $order Sorting order for the Studiensemester, 'asc' or 'desc'. Defaults to 'asc'.
	 * @param null|string $start Start date of the displayed Studiensemester in the format 'YYYY-MM-DD'.
	 * 	If provided, only Studiensemester starting from this date onwards will be returned.
	 *  eg. '2020-09-01' will start with WS2020.
	 */
	public function getAll()
	{
		$order = $this->input->get('order');
		$start = $this->input->get('start');

		if (strcasecmp($order, 'DESC') == 0)
		{
			$this->StudiensemesterModel->addOrder('ende', 'DESC');
		}
		else
		{
			$this->StudiensemesterModel->addOrder('ende', 'ASC');
		}

		if ($start)
		{
			$result = $this->StudiensemesterModel->loadWhere([
				'start >= ' => $start
			]);
		}
		else
		{
			$result = $this->StudiensemesterModel->load();
		}

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		}

		$this->terminateWithSuccess((getData($result) ?: []));
	}

	/**
	 * @return void
	 */
	public function getAktNext()
	{
		$semester = $this->input->get('semester');

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

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		}

		$this->terminateWithSuccess((getData($result) ?: ''));
	}
}
