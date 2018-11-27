<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Overview of Placementtests
 */
class Reihungstest extends Auth_Controller
{
	private $_uid; // contains the UID of the logged user
	const REIHUNGSTEST_URI = 'organisation/Reihungstest'; // URL prefix for this controller

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'infocenter:r',
				'setNavigationMenuArrayJson' => 'infocenter:r'
			)
		);

		$this->load->library('WidgetLib');
		$this->loadPhrases(
			array(
				'global',
				'ui',
				'filter'
			)
		);

		$this->_uid = getAuthUID();
		$this->load->model('system/filters_model', 'FiltersModel');
		$this->setControllerId(); // sets the controller id
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Main page of the InfoCenter tool
	 */
	public function index()
	{
		$this->_setNavigationMenuIndex(); // define the navigation menu for this page

		$this->load->view('organisation/reihungstest/reihungstest.php');
	}

	/**
	 *  Define the navigation menu for the showDetails page
	 */
	private function _setNavigationMenuIndex()
	{
		$this->load->library('NavigationLib', array('navigation_page' => 'organisation/Reihungstest/index'));

		$link = site_url();

		$listFilters = array();
		$listCustomFilters = array();

		// LOAD FILTERS
		$filters = $this->FiltersModel->getFilterList('reihungstest', 'overview','%');
		if (hasData($filters))
		{
			for ($filtersCounter = 0; $filtersCounter < count($filters->retval); $filtersCounter++)
			{
				$filter = $filters->retval[$filtersCounter];
				$listFilters[$filter->filter_id] = $filter->description[0];
			}
		}

		$filtersArray = array();

		$filtersArray = $this->navigationlib->oneLevel(
			ucfirst('Filter'),	// description
			'#',				// link
			array(),			// children
			'',					// icon
			true,				// expand
			null, 				// subscriptDescription
			null, 				// subscriptLinkClass
			null, 				// subscriptLinkValue
			'', 				// target
			2 					// sort
		);

		$this->_fillFilters($listFilters, $filtersArray);

		// LOAD CUSTOM FILTERS
		$customFilters = $this->FiltersModel->getCustomFiltersList('reihungstest', 'overview', $this->_uid);
		if (hasData($customFilters))
		{
			for ($filtersCounter = 0; $filtersCounter < count($customFilters->retval); $filtersCounter++)
			{
				$filter = $customFilters->retval[$filtersCounter];

				$listCustomFilters[$filter->filter_id] = $filter->description[0];
			}
		}

		if (count($listCustomFilters) > 0)
		{
			$filtersArray['children']['personal'] = $this->navigationlib->oneLevel(
				'Personal filters',	// description
				'#',				// link
				array(),			// children
				'',					// icon
				true,				// expand
				null, 				// subscriptDescription
				null, 				// subscriptLinkClass
				null, 				// subscriptLinkValue
				'', 				// target
				3 					// sort
			);

			$this->_fillCustomFilters($listCustomFilters, $filtersArray['children']['personal']);
		}

		$this->navigationlib->setSessionMenu(
			array(
				'filters' => $this->navigationlib->oneLevel(
					'Filter',		// description
					'#',			// link
					$filtersArray['children'],	// children
					'',				// icon
					true,			// expand
					null,			// subscriptDescription
					null,			// subscriptLinkClass
					null, 			// subscriptLinkValue
					'', 			// target
					10 				// sort
				)
			)
		);
	}
	/**
	 * Utility method used to fill elements of the left menu of the main RT page
	 */
	private function _fillFilters($filters, &$tofill)
	{
		$toPrint = "%s?%s=%s&%s=%s";

		foreach ($filters as $filterId => $description)
		{
			$tofill['children'][] = array(
				'link' => sprintf(
					$toPrint,
					site_url(self::REIHUNGSTEST_URI), 'filter_id', $filterId,
					FHC_Controller::FHC_CONTROLLER_ID,
					$this->getControllerId()
				),
				'description' => $description
			);
		}
	}

	/**
	 * Utility method used to fill elements of the InfoCenter left menu
	 * with the list of the custom filter of the authenticated user
	 */
	private function _fillCustomFilters($filters, &$tofill)
	{
		$toPrint = "%s?%s=%s&%s=%s";

		foreach ($filters as $filterId => $description)
		{
			$tofill['children'][] = array(
				'link' => sprintf(
					$toPrint,
					site_url(self::REIHUNGSTEST_URI), 'filter_id', $filterId,
					FHC_Controller::FHC_CONTROLLER_ID,
					$this->getControllerId()
				),
				'description' => $description,
				'subscriptDescription' => 'Remove',
				'subscriptLinkClass' => 'remove-custom-filter',
				'subscriptLinkValue' => $filterId
			);
		}
	}

	/**
	 * Wrapper for setNavigationMenu, returns JSON message
	 */
	public function setNavigationMenuArrayJson()
	{
		$this->_setNavigationMenuIndex();
		$this->outputJsonSuccess('success');
	}
}
