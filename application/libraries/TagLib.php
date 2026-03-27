<?php

/**
 * FH-Complete
 *
 * @package             FHC-Helper
 * @author              FHC-Team
 * @copyright           Copyright (c) 2026 fhcomplete.net
 * @license             GPLv3
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;
use \stdClass as stdClass;

class TagLib
{
	const BATCHUSER = 'sftest';
	const TYP_ZUORDNUNG = 'prestudent_id';
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		// Configs
		$this->_ci->load->config('stv');

		// Models
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->_ci->load->model('person/Person_model', 'PersonModel');
		$this->_ci->load->model('person/Notiz_model', 'NotizModel');
		$this->_ci->load->model('system/Notiztyp_model', 'NotiztypModel');
		$this->_ci->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');

		// Tag-Helper


		// Libraries
		$this->_ci->load->library('PermissionLib');
		$this->_ci->load->library('PrestudentLib');


	}

	public function getAutomatedTags($tag, $prestudentIds)
	{
		$prestudentIds = array_unique($prestudentIds);
		$count = 0;
		$tagged = [];
		foreach ($prestudentIds as $value)
		{
			$resultInsertNotiz = $this->_ci->NotizModel->insert(array(
				'titel' => 'TAG',
				'text' => 'AUTOMATED TAG',
				'verfasser_uid' => self::BATCHUSER,
				'erledigt' => false,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => self::BATCHUSER,
				'typ' => $tag
			));

			if (isError($resultInsertNotiz))
				return error ('Error occurred insert Result ' . $value);

			$resultInsertZuordnung = $this->_ci->NotizzuordnungModel->insert(array(
				'notiz_id' => $resultInsertNotiz->retval,
				self::TYP_ZUORDNUNG => $value
			));

			if (isError($resultInsertZuordnung))
				return error ('Error occurred insert Zuordnung' . $value);

			$count++;
			$tagged[] = $value;
		}
		return success([$count, $tagged]);

	}

	public function getAutomatedTagsStudiengang($studiengang_Kzs= null)
	{

	}


}