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

		$listFields = $this->FiltersModel->getExecutedQueryListFields();

		$metaData = $this->FiltersModel->getExecutedQueryMetaData();

		$this->loadViewFilters($listFields, $metaData, $dataset);
	}

	/**
	 *
	 */
	private function loadViewFilters($listFields, $metaData, $dataset)
	{
		$this->view(
			'widgets/filter/filter',
			array('listFields' => $listFields, 'metaData' => $metaData, 'dataset' => $dataset)
		);
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
	private function loadViewTableDataset($dataset)
	{
		$this->view('widgets/filter/tableDataset', array('dataset' => $dataset));
	}
}
