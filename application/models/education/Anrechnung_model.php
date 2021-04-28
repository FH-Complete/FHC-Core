<?php
class Anrechnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_anrechnung';
		$this->pk = 'anrechnung_id';
	}
	
	/**
	 * Save Anrechnungstatus.
	 * @param $anrechnung_id
	 * @param $status_kurzbz
	 * @return array|null
	 */
	public function saveAnrechnungstatus($anrechnung_id, $status_kurzbz)
	{
		$qry = '
			INSERT INTO lehre.tbl_anrechnung_anrechnungstatus (
				anrechnung_id, status_kurzbz, insertvon
			) VALUES ( ?, ?, ?);
		';
		
		return $this->execQuery($qry, array($anrechnung_id, $status_kurzbz, getAuthUID()));
	}
	
	/**
	 * Get the last inserted Anrechnungstatus
	 * @param $anrechnung_id
	 * @return array|null
	 */
	public function getLastAnrechnungstatus($anrechnung_id, $status_kurzbz = null)
	{
		if (is_string($status_kurzbz))
		{
			$qry = '
				SELECT *
				FROM lehre.tbl_anrechnungstatus
				JOIN lehre.tbl_anrechnung_anrechnungstatus USING (status_kurzbz)
				WHERE anrechnung_id = ?
				AND status_kurzbz = ?
				ORDER BY insertamum DESC
				LIMIT 1
			';
			
			return $this->execQuery($qry, array($anrechnung_id, $status_kurzbz));
		}
		
		
		$qry = '
			SELECT *
			FROM lehre.tbl_anrechnungstatus
			JOIN lehre.tbl_anrechnung_anrechnungstatus USING (status_kurzbz)
			WHERE anrechnung_id = ?
			ORDER BY insertamum DESC
			LIMIT 1
		';
		
		return $this->execQuery($qry, array($anrechnung_id));
	}
	
	/**
	 * Get status approved / rejected, if present.
	 * @param $anrechnung_id
	 * @return array|null
	 */
	public function getApprovedOrRejected($anrechnung_id)
	{
		$qry = '
			SELECT *
			FROM lehre.tbl_anrechnungstatus
			JOIN lehre.tbl_anrechnung_anrechnungstatus USING (status_kurzbz)
			WHERE anrechnung_id = ?
			AND (status_kurzbz = \'approved\' OR status_kurzbz = \'rejected\')
			ORDER BY insertamum DESC
			LIMIT 1
		';
		
		return $this->execQuery($qry, array($anrechnung_id));
	}
}
