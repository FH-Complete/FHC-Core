<?php
/**
 * Description of VertragsbestandteilFunktion_model
 *
 * @author bambi
 */
class VertragsbestandteilFunktion_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteil_funktion';
		$this->pk = 'vertragsbestandteil_id';
	}
	
	public function isBenutzerfunktionAlreadyAttachedToAnotherVB($benutzerfunktion_id, $vertragsbestandteil_id)
	{
		$where = array('benutzerfunktion_id' => $benutzerfunktion_id);
		if( intval($vertragsbestandteil_id) > 0 ) 
		{
			$where['vertragsbestandteil_id != '] = $vertragsbestandteil_id;
		}
		$this->addSelect('count(*) AS vbscount');
		$res = $this->loadWhere($where);
		if(isError($res)) 
		{
			throw new Exception('failed to check if benutzerfunktionid is already attached to another vertragsbestanteil');
		}
		$count = (getData($res))[0]->vbscount;
		return $count > 0;
	}

	/**
	 * Returns the saved function contract components (Funktions-Vertragsbestandteile)
     * of an employment relationship (DV) that are marked as main function (Hauptfunktion)
     * and overlap with the period from - to.
	 *
	 * @param int $dienstverhaeltnis_id
	 * @param string|null $von Y-m-d, null = unbegrenzt
	 * @param string|null $bis Y-m-d, null = unbegrenzt
	 * @param array $excludeVbIds IDs from submitted form
	 * @return array of objects (vertragsbestandteil_id, von, bis)
	 */
	public function getOverlappingHauptfunktionen($dienstverhaeltnis_id, $von, $bis, $excludeVbIds = array())
	{
		$excludeVbIds = array_values(array_filter(array_map('intval', $excludeVbIds), function($id) {
			return $id > 0;
		}));
		$excludeclause = (count($excludeVbIds) > 0)
			? 'AND v.vertragsbestandteil_id NOT IN (' . implode(',', $excludeVbIds) . ')'
			: '';

		$sql = <<<EOSQL
			SELECT
				v.vertragsbestandteil_id, v.von, v.bis
			FROM
				hr.tbl_vertragsbestandteil v
			JOIN
				hr.tbl_vertragsbestandteil_funktion f USING(vertragsbestandteil_id)
			WHERE
				v.dienstverhaeltnis_id = ?
			AND
				f.hauptfunktion = true
			AND
				COALESCE(?::date, '2170-12-31'::date) >= COALESCE(v.von, '1970-01-01'::date)
			AND
				COALESCE(?::date, '1970-01-01'::date) <= COALESCE(v.bis, '2170-12-31'::date)
			{$excludeclause}
			ORDER BY
				v.von
EOSQL;

		$ret = $this->execReadOnlyQuery($sql, array($dienstverhaeltnis_id, $bis, $von));
		if( isError($ret) )
		{
			throw new Exception('failed to fetch overlapping job functions');
		}

		return getData($ret) ?? array();
	}
}
