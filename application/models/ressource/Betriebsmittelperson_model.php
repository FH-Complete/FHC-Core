<?php
class Betriebsmittelperson_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_betriebsmittelperson';
		$this->pk = 'betriebsmittelperson_id';
	}
	
	/**
	 * Get Betriebsmittel by person.
	 * @param string $person_id
	 * @param string $betriebsmitteltyp
	 * @param bool $isRetourniert   False to retrieve only active Betriebsmittel.
	 * @return array|bool
	 */
	public function getBetriebsmittel($person_id, $betriebsmitteltyp = null, $isRetourniert = null, $onlyAktiveBenutzer=false)
	{
		if (!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id type is not valid.';
			return false;
		}
		
		$this->addJoin('wawi.tbl_betriebsmittel', 'betriebsmittel_id');
		
		if( $onlyAktiveBenutzer ) {
			$this->addJoin('public.tbl_benutzer b', 'b.uid = wawi.tbl_betriebsmittelperson.uid AND b.aktiv = \'t\'');
		}

		$condition = '
			wawi.tbl_betriebsmittelperson.person_id = '. $this->escape($person_id). '
		';
		
		if (is_string($betriebsmitteltyp)) {
			$condition .= '
			 AND betriebsmitteltyp = ' . $this->escape($betriebsmitteltyp);
		}
		
		if ($isRetourniert === true) {
			$condition .= '
			  AND retouram IS NOT NULL';    //  return date is given
		}
		elseif ($isRetourniert === false)
		{
			$condition .= '
			  AND retouram IS NULL';    // default
		}
		
		$this->addOrder('ausgegebenam', 'DESC');   //  default
		
		return $this->loadWhere($condition);
	}

	public function getBetriebsmittelZuordnung($cardIdentifier, $typ = 'Zutrittskarte', $ausgegeben = true)
	{
		$this->addJoin('wawi.tbl_betriebsmittel', 'betriebsmittel_id');

		$where = 'wawi.tbl_betriebsmittel.nummer2 = \'' . $cardIdentifier . '\'
					AND wawi.tbl_betriebsmittel.betriebsmitteltyp = \''. $typ .'\'
					AND (retouram >= now() OR retouram IS NULL)
					';

		if ($ausgegeben)
			$where .= 'AND ausgegebenam <= now()';
		else
			$where .= 'AND (ausgegebenam <= now() OR ausgegebenam IS NULL)';

		return $this->loadWhere($where);
	}

	public function getBetriebsmittelByUid($uid, $betriebsmitteltyp = null, $isRetourniert = false)
	{
		$this->addJoin('wawi.tbl_betriebsmittel', 'betriebsmittel_id');

		$condition = ' wawi.tbl_betriebsmittelperson.uid = '. $this->escape($uid);

		if (is_string($betriebsmitteltyp))
		{
			$condition .= ' AND betriebsmitteltyp = ' . $this->escape($betriebsmitteltyp);
		}

		if ($isRetourniert === true) {
			$condition .= ' AND retouram IS NOT NULL';
		}
		elseif ($isRetourniert === false)
		{
			$condition .= ' AND retouram IS NULL';
		}

		$this->addOrder('ausgegebenam', 'DESC');

		return $this->loadWhere($condition);
	}

	public function getBetriebsmittelData($id, $type_id)
	{
		switch ($type_id) {
			case 'person_id':
				$cond = 'bmp.person_id';
				break;
			case 'uid':
				$cond = 'bmp.uid';
				break;
			case 'betriebsmittelperson_id':
				$cond = 'bmp.betriebsmittelperson_id';
				break;
			default: 
				return error("ID nicht gültig");
		}

		$query = "
			SELECT 
			    bm.nummer, bmp.person_id, bm.betriebsmitteltyp, bmp.anmerkung as anmerkung, bmp.retouram, TO_CHAR(bmp.retouram::timestamp, 'DD.MM.YYYY') AS format_retour, bmp.ausgegebenam, TO_CHAR(bmp.ausgegebenam::timestamp, 'DD.MM.YYYY') AS format_ausgabe, bm.beschreibung, bmp.uid, bmp.kaution, bm.betriebsmittel_id, bmp.betriebsmittelperson_id, bm.inventarnummer, bm.nummer2
			FROM 
			    wawi.tbl_betriebsmittelperson bmp
			JOIN 
			        wawi.tbl_betriebsmittel bm ON (bmp.betriebsmittel_id = bm.betriebsmittel_id)
			WHERE 
			    " . $cond . " = ? ";

		return $this->execQuery($query, array($id));
	}

	/**
	 * Perform a loadWhere on the vw_betriebsmittelperson DB View
	 *
	 * @param array $where
	 *
	 * @return stdClass
	 */
	public function loadViewWhere($where)
	{
		$table = $this->dbTable;
		$this->dbTable = 'public.vw_betriebsmittelperson';
		$result = $this->loadWhere($where);
		$this->dbTable = $table;
		return $result;
	}
}
