<?php
class Bisio_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bisio';
		$this->pk = 'bisio_id';
	}

	/**
	 * Gets duration of stay in days by bisio_id.
	 * @param int $bisio_id
	 * @return object success with number of days or error
	 */
	public function getAufenthaltsdauer($bisio_id)
	{
		// get from and to date
		$this->addSelect('von, bis');
		$bisioRes = $this->load($bisio_id);

		if (isError($bisioRes))
			return $bisioRes;

		if (hasData($bisioRes))
		{
			$bisioData = getData($bisioRes)[0];

			$avon = $bisioData->von;
			$abis = $bisioData->bis;

			if (is_null($avon) || is_null($abis))
				return success("Von or bis date not set");

			$vonDate = new DateTime($avon);
			$bisDate = new DateTime($abis);
			$interval = $vonDate->diff($bisDate);
			return success($interval->days);
		}
		else
			return success("Bisio not found");
	}

	/**
	 * checks, if an (extension) table exists to avoid later errors
	 * @param String $schema like 'extension'
	 * @param String $table like 'tbl_mo_bisiozuordnung'
	 * @return boolean
	 */
	public function tableExists($schema, $table)
	{
		$params = array($schema, $table);

		$qry = "SELECT
				  1
				FROM
				  information_schema.role_table_grants
				WHERE
				    table_schema = ?
				AND table_name = ?";

		$result =  $this->execQuery($qry, $params);

		return $result;
	}
}
