<?php

/**
 *
 */
class FilterWidget extends Widget
{
	private $app;
	private $datasetName;

	/**
	 *
	 */
	public function __construct($name, $args = array())
	{
		parent::__construct($name, $args);
	}

	/**
	 *
	 */
	public function display($widgetData)
	{
		$this->load->model('system/Filters_model', 'FiltersModel');

		$this->app = $widgetData['app'];
		$this->datasetName = $widgetData['datasetName'];

		$dataset = $this->FiltersModel->execReadOnlyQuery($widgetData['query']);

		$this->loadViewSelectFields($this->FiltersModel->getExecutedQueryListFields());

		$this->loadViewSelectFilters($this->FiltersModel->getExecutedQueryMetaData());

		$this->loadViewTableDataset($dataset);
	}

	/**
	 *
	 */
	private function loadViewSelectFields($listFields)
	{
		$this->view('widgets/filter/selectFields', array('listFields' => $listFields));
	}

	/**
	 *
	 */
	private function loadViewSelectFilters($metaData)
	{
		$this->view('widgets/filter/selectFilters', array('metaData' => $metaData));
	}

	/**
	 *
	 */
	private function loadViewTableDataset($result)
	{
		$this->view('widgets/filter/tableDataset', array('dataset' => $result));
	}
}
