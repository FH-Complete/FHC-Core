<?php
class Fehler_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_fehler';
		$this->pk = array('fehlercode');
		$this->hasSequence = false;
	}

	/**
	 * Gets all fehler for particular apps.
	 * @param $apps string of one app or array with multiple
	 * @return object success or error
	 */
	public function getByApps($apps)
	{
		if (is_string($apps)) $apps = [$apps];

		$params = [];

		$qry = "
			SELECT
				fehlercode, fehler_kurzbz, fehlercode_extern, fehlertext, fehlertyp_kurzbz
			FROM
				system.tbl_fehler fe";

		if (!isEmptyArray($apps))
		{
			$qry .= "
				WHERE EXISTS (
					SELECT 1
					FROM
						system.tbl_fehler_app
					WHERE
						fehlercode = fe.fehlercode
						AND app IN ?
				)";

			$params[] = $apps;
		}

		$qry .= " ORDER BY fehlercode;";

		return $this->execReadOnlyQuery($qry, $params);
	}
}
