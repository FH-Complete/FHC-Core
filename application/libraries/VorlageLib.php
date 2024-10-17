<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class VorlageLib
{
	private $recipients = array();

	/**
	 * Loads parser library and OrganisationseinheitLib library
	 */
	public function __construct()
	{
		require_once APPPATH.'config/message.php';

		$this->ci =& get_instance();

		$this->ci->load->library('parser');
		$this->ci->load->library('OrganisationseinheitLib');

		$this->ci->load->model('system/Vorlage_model', 'VorlageModel');
		$this->ci->load->model('system/Vorlagestudiengang_model', 'VorlageStudiengangModel');
	}

   	/**
	 * getVorlage() - will load a spezific Template
	 *
	 * @param   int  $vorlage_kurzbz	REQUIRED
	 * @return  struct
	 */
	public function getVorlage($vorlage_kurzbz)
	{
		if (isEmptyString($vorlage_kurzbz))
			return error(MSG_ERR_INVALID_MSG_ID);

		$vorlage = $this->ci->VorlageModel->load($vorlage_kurzbz);
		return $vorlage;
	}

	/**
	 * getSubMessages() - will return all Messages subordinated from a specified message.
	 *
	 * @param   int  $msg_id	REQUIRED
	 * @return  array
	 */
	public function getVorlageByMimetype($mimetype = null)
	{
		$vorlage = $this->ci->VorlageModel->loadWhere(array('mimetype' => $mimetype));
		return $vorlage;
	}

	/**
	 * saveVorlage() - will save a spezific Template.
	 *
	 * @param   array  $data	REQUIRED
	 * @return  array
	 */
	public function saveVorlage($vorlage_kurzbz, $data)
	{
		if (isEmptyArray($data))
			return error(MSG_ERR_INVALID_MSG_ID);

		$vorlage = $this->ci->VorlageModel->update($vorlage_kurzbz, $data);
		return $vorlage;
	}

	/**
	 * getVorlagetextByVorlage() - will load tbl_vorlagestudiengang for a spezific Template.
	 *
	 * @param   string  $vorlage_kurzbz	REQUIRED
	 * @return  array
	 */
	public function getVorlagetextByVorlage($vorlage_kurzbz)
	{
		if (isEmptyString($vorlage_kurzbz)) return error('Not a valid vorlage_kurzbz');

		$vorlage = $this->ci->VorlageStudiengangModel->loadWhere(array('vorlage_kurzbz' => $vorlage_kurzbz));
		return $vorlage;
	}

	/**
	 * loadVorlagetext() - will load the best fitting Template.
	 *
	 * @param   string  $vorlage_kurzbz	REQUIRED
	 * @param   string  $oe_kurzbz		OPTIONAL
	 * @param   string  $orgform_kurzbz	OPTIONAL
	 * @param   string  $sprache		OPTIONAL
	 * @return  array
	 */
	public function loadVorlagetext($vorlage_kurzbz, $oe_kurzbz = null, $orgform_kurzbz = null, $sprache = null)
	{
		if (isEmptyString($vorlage_kurzbz)) return error('Not a valid vorlage_kurzbz');

		// Try to search the template with the given vorlage_kurzbz and other parameters if present
		$queryParameters = array("vorlage_kurzbz" => $vorlage_kurzbz, "aktiv" => true);

		if (isset($oe_kurzbz))
		{
			$queryParameters["oe_kurzbz"] = $oe_kurzbz;
		}
		if (isset($orgform_kurzbz))
		{
			$queryParameters["orgform_kurzbz"] = $orgform_kurzbz;
		}
		if (isset($sprache))
		{
			$queryParameters["sprache"] = $sprache;
		}

		$this->ci->VorlageStudiengangModel->addOrder('version', 'DESC');

		$vorlage = $this->ci->VorlageStudiengangModel->loadWhere($queryParameters);
		// If the searched template was not found
		if (!hasData($vorlage))
		{
			// Builds where clause
			$where = $this->_where($vorlage_kurzbz);

			$vorlage = $this->ci->organisationseinheitlib->treeSearch(
				'public',
				'tbl_vorlagestudiengang',
				array("vorlage_kurzbz", "studiengang_kz", "version", "text", "oe_kurzbz",
						"vorlagestudiengang_id", "style", "berechtigung", "anmerkung_vorlagestudiengang",
						"aktiv", "sprache", "subject", "orgform_kurzbz"),
				$where,
				"version DESC",
				$oe_kurzbz
			);
		}

		return $vorlage;
	}

	/**
	 * _where
	 */
	private function _where($vorlage_kurzbz)
	{
		// Builds where clause
		$where = "vorlage_kurzbz = ".$this->ci->VorlageModel->escape($vorlage_kurzbz);

		$where .= " AND aktiv = true";

		return $where;
	}

	/**
	 * insertVorlagetext() - will load tbl_vorlagestudiengang for a spezific Template.
	 *
	 * @param   string  $vorlage_kurzbz	REQUIRED
	 * @return  array
	 */
	public function insertVorlagetext($data)
	{
		$vorlagetext = $this->ci->VorlageStudiengangModel->insert($data);
		return $vorlagetext;
	}

	/**
	 * loadVorlagetext() - will load tbl_vorlagestudiengang for a spezific Template.
	 *
	 * @param   string  $vorlage_kurzbz	REQUIRED
	 * @return  array
	 */
	public function getVorlagetextById($vorlagestudiengang_id)
	{
		$vorlagetext = $this->ci->VorlageStudiengangModel->load($vorlagestudiengang_id);
		return $vorlagetext;
	}

	/**
	 * saveVorlagetext() - will load tbl_vorlagestudiengang for a spezific Template.
	 *
	 * @param   string  $vorlage_kurzbz	REQUIRED
	 * @return  array
	 */
	public function updateVorlagetext($vorlagestudiengang_id, $data)
	{
		$vorlagetext = $this->ci->VorlageStudiengangModel->update($vorlagestudiengang_id, $data);
		return $vorlagetext;
	}
}
