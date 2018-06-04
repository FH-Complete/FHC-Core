<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the FiltersLib (back-end)
 * Provides data to the ajax get calls about the filter
 * Accepts ajax post calls to change the filter data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Filters extends FHC_Controller
{
	const FILTER_PAGE_PARAM = 'filter_page';

	/**
	 * Calls the parent's constructor and loads the FiltersLib
	 */
	public function __construct()
    {
        parent::__construct();

		$this->_loadFiltersLib(); // Loads the FiltersLib with parameters
    }

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 *
	 */
	public function getFilter()
	{
		$this->outputJsonSuccess($this->filterslib->getSession());
	}

	/**
	 * Retrives the number of records present in the dataset
	 */
	public function rowNumber()
	{
		$rowNumber = 0;
		$dataset = $this->filterslib->getElementSession(FiltersLib::SESSION_DATASET);

		if (isset($dataset) && is_array($dataset))
		{
			$rowNumber = count($dataset);
		}

		$this->outputJsonSuccess($rowNumber);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Loads the FiltersLib with the FILTER_PAGE_PARAM parameter
	 * If the parameter FILTER_PAGE_PARAM is not given then the execution of the controller is terminated and
	 * an error message is printed
	 */
	private function _loadFiltersLib()
	{
		// If the parameter FILTER_PAGE_PARAM is present in the HTTP GET or POST
		if (isset($_GET[self::FILTER_PAGE_PARAM]) || isset($_POST[self::FILTER_PAGE_PARAM]))
		{
			// If it is present in the HTTP GET
			if (isset($_GET[self::FILTER_PAGE_PARAM]))
			{
				$filterPage = $this->input->get(self::FILTER_PAGE_PARAM); // is retrived from the HTTP GET
			}
			elseif (isset($_POST[self::FILTER_PAGE_PARAM])) // Else if it is present in the HTTP POST
			{
				$filterPage = $this->input->post(self::FILTER_PAGE_PARAM); // is retrived from the HTTP POST
			}

			// Loads the FiltersLib that contains all the used logic
			$this->load->library('FiltersLib', array(self::FILTER_PAGE_PARAM => $filterPage));
		}
		else // Otherwise an error will be written in the output
		{
			$this->outputJsonError('Parameter '.self::FILTER_PAGE_PARAM.' not provided!');
			exit;
		}
	}

	// /**
	//  *
	//  */
	// public function sortSelectedFields()
	// {
	// 	$selectedFieldsLst = $this->input->post('selectedFieldsLst');
	//
	// 	$filterUniqueId = $this->_getFilterUniqueId();
	//
	// 	$json = new stdClass();
	//
	// 	$session = $this->_readSession($filterUniqueId);
	//
	// 	$allSelectedFields = $this->_getFromSession('allSelectedFields');
	// 	$allColumnsAliases = $this->_getFromSession('allColumnsAliases');
	//
	// 	$json->selectedFields = $this->_getFromSession('selectedFields');
	// 	$json->columnsAliases = $this->_getFromSession('columnsAliases');
	//
	// 	if (isset($selectedFieldsLst) && is_array($selectedFieldsLst))
	// 	{
	// 		$json->selectedFields = $selectedFieldsLst;
	// 		$json->columnsAliases = array();
	//
	// 		for ($i = 0; $i < count($json->selectedFields); $i++)
	// 		{
	// 			$pos = array_search($json->selectedFields[$i], $allSelectedFields);
	//
	// 			$json->columnsAliases[$i] = $json->selectedFields[$i];
	//
	// 			if ($pos !== false)
	// 			{
	// 				if ($allColumnsAliases != null && is_array($allColumnsAliases))
	// 				{
	// 					$json->columnsAliases[$i] = $allColumnsAliases[$pos];
	// 				}
	// 			}
	// 		}
	// 	}
	//
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['selectedFields'] = $json->selectedFields;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['columnsAliases'] = $json->columnsAliases;
	//
	// 	$this->output->set_content_type('application/json')->set_output(json_encode($json));
	// }
	//
	// /**
	//  *
	//  */
	// public function saveFilter()
	// {
	// 	$this->_saveFilter($this->input->post("customFilterDescription"), $this->_getFilterUniqueId());
	//
	// 	$this->output->set_content_type('application/json')->set_output(json_encode('Filter saved'));
	// }
	//
	// /**
	//  *
	//  */
	// private function _saveFilter($customFilterDescription, $filterUniqueId)
	// {
	// 	$objToBeSaved = new stdClass();
	//
	// 	$filterSessionArray = $this->_readSession($filterUniqueId);
	//
	// 	$objToBeSaved->name = $customFilterDescription;
	//
	// 	if (isset($filterSessionArray[self::SELECTED_FIELDS]))
	// 	{
	// 		$selectedFields = $filterSessionArray[self::SELECTED_FIELDS];
	// 		$objToBeSaved->columns = array();
	//
	// 		for ($selectedFieldsCounter = 0; $selectedFieldsCounter < count($selectedFields); $selectedFieldsCounter++)
	// 		{
	// 			$objToBeSaved->columns[$selectedFieldsCounter] = new stdClass();
	// 			$objToBeSaved->columns[$selectedFieldsCounter]->name = $selectedFields[$selectedFieldsCounter];
	// 		}
	// 	}
	//
	// 	if (isset($filterSessionArray[self::SELECTED_FILTERS]))
	// 	{
	// 		$selectedFilters = $filterSessionArray[self::SELECTED_FILTERS];
	// 		$objToBeSaved->filters = array();
	//
	// 		for ($selectedFiltersCounter = 0; $selectedFiltersCounter < count($selectedFilters); $selectedFiltersCounter++)
	// 		{
	// 			$objToBeSaved->filters[$selectedFiltersCounter] = new stdClass();
	// 			$objToBeSaved->filters[$selectedFiltersCounter]->name = $selectedFilters[$selectedFiltersCounter];
	//
	// 			if (isset($filterSessionArray[self::ACTIVE_FILTERS])
	// 				&& isset($filterSessionArray[self::ACTIVE_FILTERS][$selectedFilters[$selectedFiltersCounter]]))
	// 			{
	// 				$objToBeSaved->filters[$selectedFiltersCounter]->condition = $filterSessionArray[self::ACTIVE_FILTERS][$selectedFilters[$selectedFiltersCounter]];
	// 			}
	//
	// 			if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION])
	// 				&& isset($filterSessionArray[self::ACTIVE_FILTERS_OPERATION][$selectedFilters[$selectedFiltersCounter]]))
	// 			{
	// 				$objToBeSaved->filters[$selectedFiltersCounter]->operation = $filterSessionArray[self::ACTIVE_FILTERS_OPERATION][$selectedFilters[$selectedFiltersCounter]];
	// 			}
	//
	// 			if (isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION])
	// 				&& isset($filterSessionArray[self::ACTIVE_FILTERS_OPTION][$selectedFilters[$selectedFiltersCounter]]))
	// 			{
	// 				$objToBeSaved->filters[$selectedFiltersCounter]->option = $filterSessionArray[self::ACTIVE_FILTERS_OPTION][$selectedFilters[$selectedFiltersCounter]];
	// 			}
	// 		}
	// 	}
	//
	// 	$desc = $customFilterDescription;
	// 	$descPGArray = '{"'.$desc.'", "'.$desc.'", "'.$desc.'", "'.$desc.'"}';
	//
	// 	$resultBenutzer = $this->BenutzerModel->load(getAuthUID());
	// 	$personId = $resultBenutzer->retval[0]->person_id;
	//
	// 	$result = $this->FiltersModel->loadWhere(array(
	// 		'app' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['app'],
	// 		'dataset_name' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['datasetName'],
	// 		'description' => $descPGArray,
	// 		'person_id' => $personId
	// 	));
	//
	// 	if (hasData($result))
	// 	{
	// 		$this->FiltersModel->update(
	// 			array(
	// 				'app' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['app'],
	// 				'dataset_name' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['datasetName'],
	// 				'description' => $descPGArray,
	// 				'person_id' => $personId
	// 			),
	// 			array(
	// 				'filter' => json_encode($objToBeSaved)
	// 			)
	// 		);
	// 	}
	// 	else
	// 	{
	// 		$this->FiltersModel->insert(array(
	// 			'app' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['app'],
	// 			'dataset_name' => $_SESSION[self::SESSION_NAME][$filterUniqueId]['datasetName'],
	// 			'filter_kurzbz' => uniqid($personId, true),
	// 			'person_id' => $personId,
	// 			'description' => $descPGArray,
	// 			'sort' => null,
	// 			'default_filter' => false,
	// 			'filter' => json_encode($objToBeSaved),
	// 			'oe_kurzbz' => null
	// 		));
	// 	}
	// }
	//
	// /**
	//  *
	//  */
	// public function deleteCustomFilter()
	// {
	// 	$filter_id = $this->input->post('filter_id');
	//
	// 	if (is_numeric($filter_id))
	// 	{
	// 		$this->FiltersModel->deleteCustomFilter($filter_id);
	//
	// 		$this->output->set_content_type('application/json')->set_output(json_encode('Removed'));
	// 	}
	// }
	//
	// /**
	//  *
	//  */
	// public function removeSelectedFields()
	// {
	// 	$fieldName = $this->input->post('fieldName');
	// 	$filterUniqueId = $this->_getFilterUniqueId();
	//
	// 	$session = $this->_readSession($filterUniqueId);
	//
	// 	$allSelectedFields = $this->_getFromSession('allSelectedFields');
	// 	$allColumnsAliases = $this->_getFromSession('allColumnsAliases');
	//
	// 	$selectedFields = $this->_getFromSession('selectedFields');
	// 	$columnsAliases = $this->_getFromSession('columnsAliases');
	//
	// 	if (($pos = array_search($fieldName, $selectedFields)) !== false)
	// 	{
	// 		array_splice($selectedFields, $pos, 1);
	//
	// 		if ($columnsAliases != null && is_array($columnsAliases))
	// 		{
	// 			array_splice($columnsAliases, $pos, 1);
	// 		}
	// 	}
	//
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['selectedFields'] = $selectedFields;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['columnsAliases'] = $columnsAliases;
	//
	// 	$json = new stdClass();
	//
	// 	$json->allSelectedFields = $allSelectedFields;
	// 	$json->allColumnsAliases = $allColumnsAliases;
	// 	$json->selectedFields = $selectedFields;
	// 	$json->columnsAliases = $columnsAliases;
	//
	// 	$this->output->set_content_type('application/json')->set_output(json_encode($json));
	// }
	//
	// /**
	//  *
	//  */
	// public function removeSelectedFilters()
	// {
	// 	$fieldName = $this->input->post('fieldName');
	// 	$filterUniqueId = $this->_getFilterUniqueId();
	//
	// 	$session = $this->_readSession($filterUniqueId);
	//
	// 	$selectedFilters = $this->_getFromSession('selectedFilters');
	// 	$selectedFiltersActiveFilters = $this->_getFromSession('activeFilters');
	// 	$selectedFiltersActiveFiltersOperation = $this->_getFromSession('activeFiltersOperation');
	// 	$selectedFiltersActiveFiltersOption = $this->_getFromSession('activeFiltersOption');
	//
	// 	if (($pos = array_search($fieldName, $selectedFilters)) !== false)
	// 	{
	// 		array_splice($selectedFilters, $pos, 1);
	// 		array_splice($selectedFiltersActiveFilters, $pos, 1);
	// 		array_splice($selectedFiltersActiveFiltersOperation, $pos, 1);
	// 		array_splice($selectedFiltersActiveFiltersOption, $pos, 1);
	// 	}
	//
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['selectedFilters'] = $selectedFilters;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFilters'] = $selectedFiltersActiveFilters;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOperation'] = $selectedFiltersActiveFiltersOperation;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOption'] = $selectedFiltersActiveFiltersOption;
	//
	// 	$json = new stdClass();
	//
	// 	$json->selectedFilters = $selectedFilters;
	// 	$json->selectedFiltersActiveFilters = $selectedFiltersActiveFilters;
	// 	$json->selectedFiltersActiveFiltersOperation = $selectedFiltersActiveFiltersOperation;
	// 	$json->selectedFiltersActiveFiltersOption = $selectedFiltersActiveFiltersOption;
	//
	// 	$this->output->set_content_type('application/json')->set_output(json_encode($json));
	// }
	//
	// /**
	//  *
	//  */
	// public function addSelectedFields()
	// {
	// 	$fieldName = $this->input->post('fieldName');
	// 	$filterUniqueId = $this->_getFilterUniqueId();
	//
	// 	$session = $this->_readSession($filterUniqueId);
	//
	// 	$allSelectedFields = $this->_getFromSession('allSelectedFields');
	// 	$allColumnsAliases = $this->_getFromSession('allColumnsAliases');
	//
	// 	$selectedFields = $this->_getFromSession('selectedFields');
	// 	$columnsAliases = $this->_getFromSession('columnsAliases');
	//
	// 	if (($pos = array_search($fieldName, $allSelectedFields)) !== false
	// 		&& array_search($fieldName, $selectedFields) === false)
	// 	{
	// 		array_push($selectedFields, $fieldName);
	//
	// 		if ($columnsAliases != null && is_array($columnsAliases))
	// 		{
	// 			array_push($columnsAliases, $allColumnsAliases[$pos]);
	// 		}
	// 	}
	//
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['selectedFields'] = $selectedFields;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['columnsAliases'] = $columnsAliases;
	//
	// 	$json = new stdClass();
	//
	// 	$json->allSelectedFields = $allSelectedFields;
	// 	$json->allColumnsAliases = $allColumnsAliases;
	// 	$json->selectedFields = $selectedFields;
	// 	$json->columnsAliases = $columnsAliases;
	//
	// 	$this->output->set_content_type('application/json')->set_output(json_encode($json));
	// }
	//
	// /**
	//  *
	//  */
	// public function addSelectedFilters()
	// {
	// 	$fieldName = $this->input->post('fieldName');
	// 	$filterUniqueId = $this->_getFilterUniqueId();
	//
	// 	$session = $this->_readSession($filterUniqueId);
	//
	// 	$selectedFilters = $this->_getFromSession('selectedFilters');
	// 	$selectedFiltersActiveFilters = $this->_getFromSession('activeFilters');
	// 	$selectedFiltersActiveFiltersOperation = $this->_getFromSession('activeFiltersOperation');
	// 	$selectedFiltersActiveFiltersOption = $this->_getFromSession('activeFiltersOption');
	//
	// 	if (!in_array($fieldName, $selectedFilters))
	// 	{
	// 		array_push($selectedFilters, $fieldName);
	// 		$selectedFiltersActiveFilters[$fieldName] = "";
	// 		$selectedFiltersActiveFiltersOperation[$fieldName] = "";
	// 		$selectedFiltersActiveFiltersOption[$fieldName] = "";
	// 	}
	//
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['selectedFilters'] = $selectedFilters;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFilters'] = $selectedFiltersActiveFilters;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOperation'] = $selectedFiltersActiveFiltersOperation;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOption'] = $selectedFiltersActiveFiltersOption;
	//
	// 	$json = new stdClass();
	//
	// 	$json->selectedFilters = $selectedFilters;
	// 	$json->selectedFiltersActiveFilters = $selectedFiltersActiveFilters;
	// 	$json->selectedFiltersActiveFiltersOperation = $selectedFiltersActiveFiltersOperation;
	// 	$json->selectedFiltersActiveFiltersOption = $selectedFiltersActiveFiltersOption;
	//
	// 	$this->output->set_content_type('application/json')->set_output(json_encode($json));
	// }
	//
	// /**
	//  *
	//  */
	// public function applyFilter()
	// {
	// 	$fieldNames = $this->input->post('filterNames');
	// 	$filterOperations = $this->input->post('filterOperations');
	// 	$filterOperationValues = $this->input->post('filterOperationValues');
	// 	$filterOptions = $this->input->post('filterOptions');
	// 	$filterUniqueId = $this->_getFilterUniqueId();
	//
	// 	$session = $this->_readSession($filterUniqueId);
	//
	// 	$activeFilters = array_combine($fieldNames, $filterOperationValues);
	// 	$activeFiltersOperation = array_combine($fieldNames, $filterOperations);
	// 	$activeFiltersOption = array_combine($fieldNames, $filterOptions);
	//
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFilters'] = $activeFilters;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOperation'] = $activeFiltersOperation;
	// 	$_SESSION[self::SESSION_NAME][$filterUniqueId]['activeFiltersOption'] = $activeFiltersOption;
	//
	// 	$json = new stdClass();
	//
	// 	$json->fieldNames = $fieldNames;
	// 	$json->activeFilters = $activeFilters;
	// 	$json->activeFiltersOperation = $activeFiltersOperation;
	// 	$json->activeFiltersOption = $activeFiltersOption;
	//
	// 	$this->output->set_content_type('application/json')->set_output(json_encode($json));
	// }
}
