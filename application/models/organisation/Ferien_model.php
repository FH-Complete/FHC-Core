<?php
class Ferien_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_ferien';
		$this->pk = 'ferien_id';
	}

	/**
	 * Loads all Ferien between two dates.
	 * @param $vondatum
	 * @param $bisdatum
	 * @param $studiengang_kz by default, loads only Ferien from oe of 0 Studiengang (and parents)
	 * @return object success or error
	 */
	public function getByDateRange($vondatum, $bisdatum, $studiengang_kz = 0)
	{
		if (!is_numeric($studiengang_kz)) return error("Invalid Studiengang Kz");
		if (!is_valid_date($vondatum)) return error("Invalid von date");
		if (!is_valid_date($bisdatum)) return error("Invalid bis date");

		// get oe from studiengang
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$result = $this->StudiengangModel->loadWhere(['studiengang_kz' => $studiengang_kz]);

		if (isError($result)) return $result;
		if (!hasData($result)) return success([]);

		$oe_kurzbz = getData($result)[0]->oe_kurzbz;

		// get all parents oes
		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		$result = $this->OrganisationseinheitModel->getParents($oe_kurzbz);

		if (isError($result)) return $result;
		if (!hasData($result)) return success([]);

		$parents = array_column(getData($result), 'oe_kurzbz');

		// get ferien - use oe_kurzbz, if "old" ferien without oe, use studiengang
		$qry = "
			SELECT
				*
			FROM
				lehre.tbl_ferien
			WHERE
				(bisdatum >= ? AND vondatum < ?)
				AND (CASE
					WHEN oe_kurzbz IS NOT NULL
					THEN oe_kurzbz IN ?
					ELSE
					studiengang_kz=0
					OR studiengang_kz=?
				END)
			ORDER BY
				vondatum";

		return $this->execReadOnlyQuery($qry, [$vondatum, $bisdatum, $parents, $studiengang_kz]);
	}
}
