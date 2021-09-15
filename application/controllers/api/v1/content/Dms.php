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

class Dms extends API_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(array('Dms' => 'basis/dms:rw', 'AktenAcceptedDms' => 'basis/dms:r', 'DelDms' => 'basis/dms:w'));
		// Load library DmsLib
		$this->load->library('DmsLib');
	}

	/**
	 *
	 */
	public function getDms()
	{
		$dms_id = $this->get('dms_id');
		$version = $this->get('version');

		if (isset($dms_id))
		{
			$result = $this->dmslib->read($dms_id, $version);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 *
	 */
	public function getAktenAcceptedDms()
	{
		$person_id = $this->get('person_id');
		$dokument_kurzbz = $this->get('dokument_kurzbz');
		$no_file = $this->get('no_file');

		if (isset($person_id))
		{
			$result = $this->dmslib->getAktenAcceptedDms($person_id, $dokument_kurzbz, $no_file);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 *
	 */
	public function postDms()
	{
		$dms = $this->post();

		if ($this->_validatePost($dms))
		{
			$result = $this->dmslib->save($dms);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 *
	 */
	public function postDelDms()
	{
		$dms = $this->post();

		if ($this->_validateDelete($this->post()))
		{
			$result = $this->dmslib->delete($dms['person_id'], $dms['dms_id']);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validatePost($dms = null)
	{
		if (!isset($dms))
		{
			return false;
		}
		if (!isset($dms['file_content']) || (isset($dms['file_content']) && $dms['file_content'] == ''))
		{
			return false;
		}
		if (!isset($dms['name']) || (isset($dms['name']) && $dms['name'] == ''))
		{
			return false;
		}

		return true;
	}

	private function _validateDelete($dms = null)
	{
		if (!isset($dms))
		{
			return false;
		}
		if (!isset($dms['person_id']) || !is_numeric($dms['person_id']))
		{
			return false;
		}
		if (!isset($dms['dms_id']) || !is_numeric($dms['dms_id']))
		{
			return false;
		}

		return true;
	}
}
