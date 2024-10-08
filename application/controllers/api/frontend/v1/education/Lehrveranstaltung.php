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

class Lehrveranstaltung extends FHCAPI_Controller
{
	/**
	 * Lehrveranstaltung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array(
			'getTemplateLvTree' => array(
			    'lehre/lehrveranstaltung:rw'
			)
		));

		// Load model LehrveranstaltungModel
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
	}

	/**
	 * Get all Templates and union with all Lehrveranstaltungen of given Studiensemester and Oes of given Berechtigung,
	 * that are assigned to a template. This data structure can be used for nested tabulators' data tree.
	 *
	 * @param null|string $studiensemester_kurzbz
	 * @param null|string $berechtigung
	 * @return array|stdClass|null
	 */
	public function getTemplateLvTree()
	{
		$studiensemester_kurzbz = $this->input->get('studiensemester_kurzbz');
		$berechtigung = $this->input->get('berechtigung');

		if ($berechtigung)
		{
			$oe_permissions = $this->permissionlib->getOE_isEntitledFor($berechtigung);
			if(!$oe_permissions) $oe_permissions = [];

			$result = $this->LehrveranstaltungModel->getTemplateLvTree($studiensemester_kurzbz, $oe_permissions);
		}
		else
		{
			$result = $this->LehrveranstaltungModel->getTemplateLvTree($studiensemester_kurzbz);
		}

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		}

		$this->terminateWithSuccess((getData($result) ?: []));
	}
}
