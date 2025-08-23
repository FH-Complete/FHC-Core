<?php
/**
 * Copyright (C) 2025 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Lehrveranstaltung extends FHCAPI_Controller
{
	private $_ci;
	private $_uid;

	public function __construct()
	{
		parent::__construct([
			'getByEmp' => ['admin:r', 'assistenz:r'],
			'getByStg' => ['admin:r', 'assistenz:r'],
			'loadByLV' => ['admin:r', 'assistenz:r'],
		]);

		$this->_ci = &get_instance();
		$this->_setAuthUID();

		$this->_ci->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->_ci->load->library('VariableLib', ['uid' => $this->_uid]);

		$this->loadPhrases(
			array(
				'ui'
			)
		);
	}

	public function getByEmp($studiensemester_kurzbz = null, $mitarbeiter_uid = null, $stg_kz = null)
	{

		if (is_null($mitarbeiter_uid))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$studiensemester_kurzbz = $this->getStudiensemesterKurzbz($studiensemester_kurzbz);

		$lehrveranstaltungen = $this->_ci->LehreinheitModel->getLvsByEmployee($mitarbeiter_uid, $studiensemester_kurzbz, $stg_kz);
		$lehrveranstaltungen_data = $this->getDataOrTerminateWithError($lehrveranstaltungen);

		$tree = [];

		foreach ($lehrveranstaltungen_data as $lehrveranstaltung)
		{
			$lehreinheiten = $this->_ci->LehreinheitModel->getByLvidStudiensemester($lehrveranstaltung->lehrveranstaltung_id, $studiensemester_kurzbz, $mitarbeiter_uid);
			$lehreinheiten_data = $this->getDataOrTerminateWithError($lehreinheiten);

			if (!isset($lehrveranstaltung->_children))
			{
				$lehrveranstaltung->_children = $lehreinheiten_data;
			}
			$tree[] = $lehrveranstaltung;
		}

		$this->terminateWithSuccess($tree);
	}
	public function getByStg($studiensemester_kurzbz = null, $studiengang_kz = null, $semester = null)
	{
		if (is_null($studiengang_kz) || !preg_match("/^-?[1-9][0-9]*$/", (string)$studiengang_kz))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$verband = null;
		if (!is_null($semester) && !is_numeric($semester))
		{
			$verband = $semester;
			$semester = null;
		}

		$this->_ci->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$studiensemester_kurzbz = $this->getStudiensemesterKurzbz($studiensemester_kurzbz);
		$studienplan_data = $this->_ci->StudienplanModel->getStudienplaeneBySemester($studiengang_kz, $studiensemester_kurzbz, $semester, $verband);

		$studienplan_ids = array();
		$only_ids = array();
		$placeholders = array();

		if (hasData($studienplan_data))
		{
			foreach (getData($studienplan_data) as $studienplan) {
				$placeholders[] = "(?, ?)";
				$studienplan_ids[] = $studienplan->studienplan_id;
				$studienplan_ids[] = $studienplan->semester;
				$only_ids[] = $studienplan->studienplan_id;
			}
		}

		$lehrveranstaltungen_data = $this->_ci->LehrveranstaltungModel->getLvsByStudiengang($studienplan_ids, $placeholders, $only_ids, $studiengang_kz, $studiensemester_kurzbz, $semester, $verband);
		$lehrveranstaltungen_data = hasData($lehrveranstaltungen_data) ? getData($lehrveranstaltungen_data) : array();

		$tree = [];
		foreach ($lehrveranstaltungen_data as $row)
		{
			$rowData = $row;

			$lehreinheiten_data = $this->_ci->LehreinheitModel->getByLvidStudiensemester($row->lehrveranstaltung_id, $studiensemester_kurzbz);

			if (hasData($lehreinheiten_data))
			{
				$lehreinheiten = getData($lehreinheiten_data);
				$rowData->_children = $lehreinheiten;
			}

			if (!isEmptyString($row->studienplan_lehrveranstaltung_id_parent))
			{
				$child = $this->_ci->StudienplanModel->loadStudienplanLehrveranstaltung($row->studienplan_lehrveranstaltung_id_parent);

				if (hasData($child))
				{
					$child = getData($child)[0];
					$searchId = $child->lehrveranstaltung_id;

					foreach ($lehrveranstaltungen_data as &$searchParent)
					{
						if ($searchParent->lehrveranstaltung_id === $searchId)
						{
							if (!isset($searchParent->_children))
							{
								$searchParent->_children = [];
							}

							if (is_array($searchParent->_children))
							{
								$searchParent->_children[] = $row;
							}
							else
							{
								$searchParent->_children = [$searchParent->_children, $row];
							}
							break;
						}
					}

				}
			}
			else
			{
				$tree[] = $rowData;
			}
		}

		$counter = 0;
		$this->assignUniqueIndex($tree, $counter);
		$this->terminateWithSuccess($tree);
	}


	public function loadByLV($lehrveranstaltung_id = null)
	{
		if (is_null($lehrveranstaltung_id) || !ctype_digit((string)$lehrveranstaltung_id))
			$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$this->_ci->LehrveranstaltungModel->addSelect('lehrveranstaltung_id, lehrform_kurzbz, lehre, bezeichnung as lvbezeichnung, sprache');
		$lehrveranstaltung_result = $this->_ci->LehrveranstaltungModel->loadWhere(array('lehrveranstaltung_id' => $lehrveranstaltung_id));
		$lehrveranstaltung_result = $this->getDataOrTerminateWithError($lehrveranstaltung_result);
		$lehrveranstaltung = $lehrveranstaltung_result[0];

		$this->_ci->LehreinheitModel->addSelect('lehrveranstaltung_id_kompatibel');
		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehrveranstaltung_kompatibel', 'lehrveranstaltung_id');
		$lehrfaecher = $this->_ci->LehreinheitModel->loadWhere(array('lehrveranstaltung_id' => $lehrveranstaltung->lehrveranstaltung_id));

		$lehrfaecher_array = [];
		if (hasData($lehrfaecher))
			$lehrfaecher_array = array_merge($lehrfaecher_array, array_column(getData($lehrfaecher), 'lehrveranstaltung_id_kompatibel'));

		$lehrfaecher_array[] = $lehrveranstaltung->lehrveranstaltung_id;

		$this->_ci->LehrveranstaltungModel->addDistinct('lehrfach_id');
		$this->_ci->LehrveranstaltungModel->addSelect("tbl_lehrveranstaltung.lehrveranstaltung_id, CONCAT(tbl_lehrveranstaltung.bezeichnung || '(' || tbl_lehrveranstaltung.oe_kurzbz || ')') as lehrfach");
		$this->_ci->LehrveranstaltungModel->db->where_in('tbl_lehrveranstaltung.lehrveranstaltung_id', $lehrfaecher_array);
		$lehrfaecher_result = $this->_ci->LehrveranstaltungModel->load();

		$lehrfaecher_array = hasData($lehrfaecher_result) ? getData($lehrfaecher_result) : array();

		$lehrveranstaltung->lehrfaecher = $lehrfaecher_array;
		$this->terminateWithSuccess($lehrveranstaltung);
	}

	/*
	 * (david) ggf. im naechsten release
	 * public function loadByOrganization($oe_kurzbz)
	{
		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');

		$lehrveranstaltungen = $this->LehrveranstaltungModel->getLvsByOrganization($oe_kurzbz);
		$lehrveranstaltungen_data = $this->getDataOrTerminateWithError($lehrveranstaltungen);
		$tree = [];

		foreach ($lehrveranstaltungen_data as $lehrveranstaltung)
		{
			$lehreinheiten = $this->LehreinheitModel->getByLvidStudiensemester($lehrveranstaltung->lehrveranstaltung_id, $studiensemester_kurzbz);
			$lehreinheiten_data = $this->getDataOrTerminateWithError($lehreinheiten);

			if (!isset($lehrveranstaltung->_children))
			{

				$lehrveranstaltung->_children = $lehreinheiten_data;
			}
			$tree[] = $lehrveranstaltung;
		}
		$this->terminateWithSuccess($tree);
	}*/

	/*public function loadByFachbereich($fachbereich, $mitarbeiter_uid = null)
	{
		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');

		$this->LehreinheitModel->getLvsByFachbereich($fachbereich, $studiensemester_kurzbz, $mitarbeiter_uid);
	}*/
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid)
			show_error('User authentification failed');
	}

	private function assignUniqueIndex(&$nodes, &$counter)
	{
		foreach ($nodes as &$node)
		{
			$node->uniqueindex = $counter++;
			if (!empty($node->_children) && is_array($node->_children))
			{
				$this->assignUniqueIndex($node->_children, $counter);
			}
		}
	}

	private function getStudiensemesterKurzbz($studiensemester_kurzbz = null)
	{
		if (!is_null($studiensemester_kurzbz))
		{
			$studiensemester_result = $this->_ci->StudiensemesterModel->load($studiensemester_kurzbz);

			if (isError($studiensemester_result) || !hasData($studiensemester_result))
				$this->terminateWithError( $this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

			return getData($studiensemester_result)[0]->studiensemester_kurzbz;
		}

		$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);
	}
}
