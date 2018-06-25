<?php

class Filters_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_filters';
		$this->pk = 'filter_id';
	}

	/**
	 * Loads a filter by its app, dataset_name and filter_kurzbz
	 */
	public function getFilterList($app, $dataset_name, $filter_kurzbz)
	{
		$this->addSelect('filter_id, description');
		$this->addOrder('sort', 'ASC');

		$filterParametersArray = array(
			'app' => $app,
			'dataset_name' => $dataset_name,
			'person_id' => null,
			'array_length(description, 1) >' => 0,
			'filter_kurzbz ILIKE' => $filter_kurzbz
		);

		return $this->loadWhere($filterParametersArray);
	}

	/**
	 * Loads a custom filter by its app, dataset_name and the uid of the owner
	 */
	public function getCustomFiltersList($app, $dataset_name, $uid)
	{
		$this->addSelect('filter_id, description');
		$this->addJoin('public.tbl_benutzer', 'person_id');
		$this->addOrder('sort', 'ASC');

		$filterParametersArray = array(
			'app' => $app,
			'dataset_name' => $dataset_name,
			'array_length(description, 1) >' => 0,
			'uid' => $uid
		);

		return $this->loadWhere($filterParametersArray);
	}
}
