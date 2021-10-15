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
		$this->resetQuery(); // reset any previous built query

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
		$this->resetQuery(); // reset any previous built query

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

	/**
	 * Loads all filters by their app and dataset_name
	 */
	public function getFiltersByAppDatasetNamePersonId($app, $dataset_name, $person_id)
	{
		$query = '
		(
			-- Global filters
			SELECT gs.filter_id,
				gs.person_id,
				gs.description
			  FROM system.tbl_filters gs
			 WHERE gs.app = ?
			   AND gs.dataset_name = ?
			   AND gs.person_id IS NULL
		      ORDER BY gs.person_id DESC, gs.sort ASC
		)
		UNION ALL
		(
			-- Personal filters
			SELECT ps.filter_id,
				ps.person_id,
				ps.description
			  FROM system.tbl_filters ps
			 WHERE ps.app = ?
			   AND ps.dataset_name = ?
			   AND ps.person_id = ?
		      ORDER BY ps.person_id DESC, ps.sort ASC
		)';

		return $this->execQuery($query, array($app, $dataset_name, $app, $dataset_name, $person_id));
	}
}

