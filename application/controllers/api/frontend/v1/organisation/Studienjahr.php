<?php
/**
 * FH-Complete
 *
 * @package        FHC-API
 * @author        FHC-Team
 * @copyright    Copyright (c) 2016, fhcomplete.org
 * @license        GPLv3
 * @link        http://fhcomplete.org
 * @since        Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Studienjahr extends FHCAPI_Controller
{
	/**
	 * Studienjahr API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'getAll' => self::PERM_LOGGED,
				'getNext' => self::PERM_LOGGED
			)
		);
		// Load model StudiensemesterModel
		$this->load->model('organisation/studienjahr_model', 'StudienjahrModel');
	}

	/**
	 * Get all Studienjahre.
	 *
	 * @param null|string $order Sorting order for the Studienjahr, 'asc' or 'desc'. Defaults to 'asc'.
	 * @param null|string $start Starting Studienjahre with given studienjahr_kurzbz
	 */
	public function getAll()
	{
		$order = $this->input->get('order');
		$start = $this->input->get('studienjahr_kurzbz');

		if (strcasecmp($order, 'DESC') == 0) {
			$this->StudienjahrModel->addOrder('studienjahr_kurzbz', 'DESC');
		} else {
			$this->StudienjahrModel->addOrder('studienjahr_kurzbz', 'ASC');
		}

		if ($start) {
			$result = $this->StudienjahrModel->loadWhere([
				'studienjahr_kurzbz >= ' => $start
			]);
		} else {
			$result = $this->StudienjahrModel->load();
		}

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		}

		$this->terminateWithSuccess((getData($result) ?: []));
	}

	public function getNext()
	{
		$this->StudienjahrModel->addJoin('public.tbl_studiensemester', 'studienjahr_kurzbz');
		$this->StudienjahrModel->addOrder('start');
		$this->StudienjahrModel->addLimit(1);

		$result = $this->StudienjahrModel->loadWhere(['start >' => 'NOW()']);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		}

		$this->terminateWithSuccess(current(getData($result)));
	}
}
