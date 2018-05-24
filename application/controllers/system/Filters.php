<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Filters extends VileSci_Controller
{
	const SESSION_NAME = 'FHC_FILTER_WIDGET';

	const SELECTED_FIELDS = 'selectedFields';
	const SELECTED_FILTERS = 'selectedFilters';
	const ACTIVE_FILTERS = 'activeFilters';
	const ACTIVE_FILTERS_OPTION = 'activeFiltersOption';
	const ACTIVE_FILTERS_OPERATION = 'activeFiltersOperation';
	const FILTER_NAME = 'filterName';

	/**
	 *
	 */
	public function __construct()
    {
        parent::__construct();

        // Load session library
        $this->load->library('session');

		$this->load->model('system/Filters_model', 'FiltersModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
    }

	/**
	 *
	 */
	public function tableDataset()
	{
		$json = new stdClass();

		$session = $this->_readSession($this->_getFilterUniqueId());

		$json->selectedFields = $this->_getFromSession('selectedFields');
		$json->columnsAliases = $this->_getFromSession('columnsAliases');
		$json->additionalColumns = $this->_getFromSession('additionalColumns');
		$json->checkboxes = $this->_getFromSession('checkboxes');
		$json->dataset = $this->_getFromSession('dataset');

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function selectFields()
	{
		$json = new stdClass();

		$session = $this->_readSession($this->_getFilterUniqueId());

		$json->allSelectedFields = $this->_getFromSession('allSelectedFields');
		$json->allColumnsAliases = $this->_getFromSession('allColumnsAliases');

		$json->selectedFields = $this->_getFromSession('selectedFields');
		$json->columnsAliases = $this->_getFromSession('columnsAliases');

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function sortSelectedFields()
	{
		$selectedFieldsLst = $this->input->post('selectedFieldsLst');

		$filterUniqueId = $this->_getFilterUniqueId();

		$json = new stdClass();

		$session = $this->_readSession($filterUniqueId);

		$allSelectedFields = $this->_getFromSession('allSelectedFields');
		$allColumnsAliases = $this->_getFromSession('allColumnsAliases');

		$json->selectedFields = $this->_getFromSession('selectedFields');
		$json->columnsAliases = $this->_getFromSession('columnsAliases');

		if (isset($selectedFieldsLst) && is_array($selectedFieldsLst))
		{
			$json->selectedFields = $selectedFieldsLst;
			$json->columnsAliases = array();

			for ($i = 0; $i < count($json->selectedFields); $i++)
			{
				$pos = array_search($json->selectedFields[$i], $allSelectedFields);

				$json->columnsAliases[$i] = $json->selectedFields[$i];

				if ($pos !== false)
				{
					if ($allColumnsAliases != null && is_array($allColumnsAliases))
					{
						$json->columnsAliases[$i] = $allColumnsAliases[$pos];
					}
				}
			}
		}

		$_SESSION[self::SESSION_NAME][$filterUniqueId]['selectedFields'] = $json->selectedFields;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['columnsAliases'] = $json->columnsAliases;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function selectFilters()
	{
		$json = new stdClass();

		$session = $this->_readSession($this->_getFilterUniqueId());

		$json->allSelectedFields = $this->_getFromSession('allSelectedFields');
		$json->allColumnsAliases = $this->_getFromSession('allColumnsAliases');

		$json->selectedFilters = $this->_getFromSession('selectedFilters');
		$json->selectedFiltersAliases = array();
		$json->selectedFiltersMetaData = array();

		$json->selectedFiltersActiveFilters = array();
		$json->selectedFiltersActiveFiltersOperation = array();
		$json->selectedFiltersActiveFiltersOption = array();

		$metaData = $this->_getFromSession('metaData');
		$activeFilters = $this->_getFromSession('activeFilters');
		$activeFiltersOperation = $this->_getFromSession('activeFiltersOperation');
		$activeFiltersOption = $this->_getFromSession('activeFiltersOption');

		for ($i = 0; $i < count($json->selectedFilters); $i++)
		{
			$pos = array_search($json->selectedFilters[$i], $json->allSelectedFields);

			if ($pos !== false)
			{
				$json->selectedFiltersAliases[$i] = $json->selectedFilters[$i];
				if ($json->allColumnsAliases != null && is_array($json->allColumnsAliases))
				{
					$json->selectedFiltersAliases[$i] = $json->allColumnsAliases[$pos];
				}

				$json->selectedFiltersMetaData[] = $metaData[$pos];

				if (isset($activeFilters[$json->selectedFilters[$i]]))
				{
					$json->selectedFiltersActiveFilters[] = $activeFilters[$json->selectedFilters[$i]];
				}

				if (isset($activeFiltersOperation[$json->selectedFilters[$i]]))
				{
					$json->selectedFiltersActiveFiltersOperation[] = $activeFiltersOperation[$json->selectedFilters[$i]];
				}

				if (isset($activeFiltersOption[$json->selectedFilters[$i]]))
				{
					$json->selectedFiltersActiveFiltersOption[] = $activeFiltersOption[$json->selectedFilters[$i]];
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function saveFilter()
	{
		$this->_saveFilter($this->input->post("customFilterDescription"), $this->_getFilterUniqueId());

		$this->output->set_content_type('application/json')->set_output(json_encode('Filter saved'));
	}

	/**
	 *
	 */
	private function _saveFilter($customFilterDescription, $filterUniqueId)
	{
		$objToBeSaved = new stdClass();

		$filterSessionArray = $this->_readSession($filterUniqueId);

		$objToBeSaved->name = $customFilterDescription;

		if (isset($filterSessionArray[self::SELECTED_FIELDS]))
		{
			$selectedFields = $filterSessionArray[self::SELECTED_FIELDS];
			$objToBeSaved->columns = array();

			for ($selectedFieldsCounter = 0; $selectedFieldsCounter < count($selectedFields); $selectedFieldsCounter++)
			{
				$objToBeSaved->columns[$selectedFieldsCounter] = new stdClass();
				$objToBeSaved->columns[$selectedFieldsCounter]->name = $selectedFields[$selectedFieldsCounter];
			}
		}

		if (isset($filterSessionArray[self::SELECTED_FILTERS]))
		{
			$selectedFilters = $filterSessionArray[self::SELECTED_FILTERS];
			$objToBeSaved->filters = array();

			for ($selectedFiltersCounter = 0; $selectedFiltersCounter < count($selectedFilters); $selectedFiltersCounter++)
			{
				$objToBeSaved->filters[$selectedFiltersCounter] = new stdClass();
				$objToBeSaved->filters[$selectedFiltersCounter]->name = $selectedFilters[$selectedFiltersCounter];

				if (isset($filterSessionArray[self::ACTIVE_FILTERS])
					&& isset($filterSessionArray[self::ACTIVE_FILTERS][$selectedFilters[$selectedFiltersCounter]]))
				{
					$objToBeSaved->filters[$selectedFiltersCounter]->condition = $filterSessionArray[self::ACTIVE_FILTERS][$selectedFilters[$selectedFiltersCounter]];
				}

				if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION])
					&& isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION][$selectedFilters[$selectedFiltersCounter]]))
				{
					$objToBeSaved->filters[$selectedFiltersCounter]->operation = $filterSessionArray[self::ACTIVE_FILTERS_OPERATION][$selectedFilters[$selectedFiltersCounter]];
				}

				if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION])
					&& isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION][$selectedFilters[$selectedFiltersCounter]]))
				{
					$objToBeSaved->filters[$selectedFiltersCounter]->option = $filterSessionArray[self::ACTIVE_FILTERS_OPTION][$selectedFilters[$selectedFiltersCounter]];
				}
			}
		}

		$desc = $customFilterDescription;
		$descPGArray = '{"'.$desc.'", "'.$desc.'", "'.$desc.'", "'.$desc.'"}';

		$resultBenutzer = $this->BenutzerModel->load(getAuthUID());
		$personId = $resultBenutzer->retval[0]->person_id;

		$result = $this->FiltersModel->loadWhere(array(
			'app' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['app'],
			'dataset_name' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['datasetName'],
			'description' => $descPGArray,
			'person_id' => $personId
		));

		if (hasData($result))
		{
			$this->FiltersModel->update(
				array(
					'app' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['app'],
					'dataset_name' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['datasetName'],
					'description' => $descPGArray,
					'person_id' => $personId
				),
				array(
					'filter' => json_encode($objToBeSaved)
				)
			);
		}
		else
		{
			$this->FiltersModel->insert(array(
				'app' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['app'],
				'dataset_name' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['datasetName'],
				'filter_kurzbz' => uniqid($personId, true),
				'person_id' => $personId,
				'description' => $descPGArray,
				'sort' => null,
				'default_filter' => false,
				'filter' => json_encode($objToBeSaved),
				'oe_kurzbz' => null
			));
		}
	}

	/**
	 *
	 */
	public function deleteCustomFilter()
	{
		$filter_id = $this->input->post('filter_id');

		if (is_numeric($filter_id))
		{
			$this->FiltersModel->deleteCustomFilter($filter_id);

			$this->output->set_content_type('application/json')->set_output(json_encode('Removed'));
		}
	}

	/**
	 *
	 */
	public function removeSelectedFields()
	{
		$fieldName = $this->input->post('fieldName');
		$filterUniqueId = $this->_getFilterUniqueId();

		$session = $this->_readSession($filterUniqueId);

		$allSelectedFields = $this->_getFromSession('allSelectedFields');
		$allColumnsAliases = $this->_getFromSession('allColumnsAliases');

		$selectedFields = $this->_getFromSession('selectedFields');
		$columnsAliases = $this->_getFromSession('columnsAliases');

		if (($pos = array_search($fieldName, $selectedFields)) !== false)
		{
			array_splice($selectedFields, $pos, 1);

			if ($columnsAliases != null && is_array($columnsAliases))
			{
				array_splice($columnsAliases, $pos, 1);
			}
		}

		$_SESSION[self::SESSION_NAME][$filterUniqueId]['selectedFields'] = $selectedFields;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['columnsAliases'] = $columnsAliases;

		$json = new stdClass();

		$json->allSelectedFields = $allSelectedFields;
		$json->allColumnsAliases = $allColumnsAliases;
		$json->selectedFields = $selectedFields;
		$json->columnsAliases = $columnsAliases;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function removeSelectedFilters()
	{
		$fieldName = $this->input->post('fieldName');
		$filterUniqueId = $this->_getFilterUniqueId();

		$session = $this->_readSession($filterUniqueId);

		$selectedFilters = $this->_getFromSession('selectedFilters');
		$selectedFiltersActiveFilters = $this->_getFromSession('activeFilters');
		$selectedFiltersActiveFiltersOperation = $this->_getFromSession('activeFiltersOperation');
		$selectedFiltersActiveFiltersOption = $this->_getFromSession('activeFiltersOption');

		if (($pos = array_search($fieldName, $selectedFilters)) !== false)
		{
			array_splice($selectedFilters, $pos, 1);
			array_splice($selectedFiltersActiveFilters, $pos, 1);
			array_splice($selectedFiltersActiveFiltersOperation, $pos, 1);
			array_splice($selectedFiltersActiveFiltersOption, $pos, 1);
		}

		$_SESSION[self::SESSION_NAME][$filterUniqueId]['selectedFilters'] = $selectedFilters;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFilters'] = $selectedFiltersActiveFilters;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOperation'] = $selectedFiltersActiveFiltersOperation;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOption'] = $selectedFiltersActiveFiltersOption;

		$json = new stdClass();

		$json->selectedFilters = $selectedFilters;
		$json->selectedFiltersActiveFilters = $selectedFiltersActiveFilters;
		$json->selectedFiltersActiveFiltersOperation = $selectedFiltersActiveFiltersOperation;
		$json->selectedFiltersActiveFiltersOption = $selectedFiltersActiveFiltersOption;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function addSelectedFields()
	{
		$fieldName = $this->input->post('fieldName');
		$filterUniqueId = $this->_getFilterUniqueId();

		$session = $this->_readSession($filterUniqueId);

		$allSelectedFields = $this->_getFromSession('allSelectedFields');
		$allColumnsAliases = $this->_getFromSession('allColumnsAliases');

		$selectedFields = $this->_getFromSession('selectedFields');
		$columnsAliases = $this->_getFromSession('columnsAliases');

		if (($pos = array_search($fieldName, $allSelectedFields)) !== false
			&& array_search($fieldName, $selectedFields) === false)
		{
			array_push($selectedFields, $fieldName);

			if ($columnsAliases != null && is_array($columnsAliases))
			{
				array_push($columnsAliases, $allColumnsAliases[$pos]);
			}
		}

		$_SESSION[self::SESSION_NAME][$filterUniqueId]['selectedFields'] = $selectedFields;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['columnsAliases'] = $columnsAliases;

		$json = new stdClass();

		$json->allSelectedFields = $allSelectedFields;
		$json->allColumnsAliases = $allColumnsAliases;
		$json->selectedFields = $selectedFields;
		$json->columnsAliases = $columnsAliases;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function addSelectedFilters()
	{
		$fieldName = $this->input->post('fieldName');
		$filterUniqueId = $this->_getFilterUniqueId();

		$session = $this->_readSession($filterUniqueId);

		$selectedFilters = $this->_getFromSession('selectedFilters');
		$selectedFiltersActiveFilters = $this->_getFromSession('activeFilters');
		$selectedFiltersActiveFiltersOperation = $this->_getFromSession('activeFiltersOperation');
		$selectedFiltersActiveFiltersOption = $this->_getFromSession('activeFiltersOption');

		if (!in_array($fieldName, $selectedFilters))
		{
			array_push($selectedFilters, $fieldName);
			$selectedFiltersActiveFilters[$fieldName] = "";
			$selectedFiltersActiveFiltersOperation[$fieldName] = "";
			$selectedFiltersActiveFiltersOption[$fieldName] = "";
		}

		$_SESSION[self::SESSION_NAME][$filterUniqueId]['selectedFilters'] = $selectedFilters;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFilters'] = $selectedFiltersActiveFilters;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOperation'] = $selectedFiltersActiveFiltersOperation;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOption'] = $selectedFiltersActiveFiltersOption;

		$json = new stdClass();

		$json->selectedFilters = $selectedFilters;
		$json->selectedFiltersActiveFilters = $selectedFiltersActiveFilters;
		$json->selectedFiltersActiveFiltersOperation = $selectedFiltersActiveFiltersOperation;
		$json->selectedFiltersActiveFiltersOption = $selectedFiltersActiveFiltersOption;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function applyFilter()
	{
		$fieldNames = $this->input->post('filterNames');
		$filterOperations = $this->input->post('filterOperations');
		$filterOperationValues = $this->input->post('filterOperationValues');
		$filterOptions = $this->input->post('filterOptions');
		$filterUniqueId = $this->_getFilterUniqueId();

		$session = $this->_readSession($filterUniqueId);

		$activeFilters = array_combine($fieldNames, $filterOperationValues);
		$activeFiltersOperation = array_combine($fieldNames, $filterOperations);
		$activeFiltersOption = array_combine($fieldNames, $filterOptions);

		$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFilters'] = $activeFilters;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOperation'] = $activeFiltersOperation;
		$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOption'] = $activeFiltersOption;

		$json = new stdClass();

		$json->fieldNames = $fieldNames;
		$json->activeFilters = $activeFilters;
		$json->activeFiltersOperation = $activeFiltersOperation;
		$json->activeFiltersOption = $activeFiltersOption;

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function rowNumber()
	{
		$json = new stdClass();

		$session = $this->_readSession($this->_getFilterUniqueId());

		$dataset = $this->_getFromSession('dataset');

		if (is_array($dataset))
		{
			$json->rowNumber = count($dataset);
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	private function _readSession($filterUniqueId)
	{
		if (isset($_SESSION[self::SESSION_NAME]) && isset($_SESSION[self::SESSION_NAME][$filterUniqueId]))
			return $_SESSION[self::SESSION_NAME][$filterUniqueId];

		return array();
	}

	/**
	 *
	 */
	private function _writeSession($data, $filterUniqueId)
	{
		if (!isset($_SESSION[self::SESSION_NAME])
			|| (isset($_SESSION[self::SESSION_NAME]) && !is_array($_SESSION[self::SESSION_NAME])))
		{
			$_SESSION[self::SESSION_NAME] = array();
		}

		$_SESSION[self::SESSION_NAME][$filterUniqueId] = $data;
	}

	/**
	 *
	 */
	private function _getFilterUniqueId()
	{
		$_getFilterUniqueId = '';

		if ($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			$_getFilterUniqueId = $this->input->post('filter_page').'/'.$this->input->post('fhc_controller_id');
		}
		elseif ($_SERVER['REQUEST_METHOD'] === 'GET')
		{
			$_getFilterUniqueId = $this->input->get('filter_page').'/'.$this->input->get('fhc_controller_id');
		}

		return $_getFilterUniqueId;
	}

	/**
	 *
	 */
	private function _getFromSession($el)
	{
		$_getFromSession = null;

		if (isset($_SESSION[$el])) return $_SESSION[$el];

		return $_getFromSession;
	}
}
