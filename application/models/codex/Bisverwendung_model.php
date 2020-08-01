<?php
class Bisverwendung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bisverwendung';
		$this->pk = 'bisverwendung_id';
	}

    /**
     * Get latest (active) Verwendung of the user.
     * @param string $uid
     * @param bool $active If false, returns latest Verwendung no matter if it is still actual.
     * @return array
     */
	public function getLast($uid, $active = true)
    {
        $this->addLimit(1);

        if ($active)
        {
            $condition = '
                mitarbeiter_uid = '. $this->escape($uid). '
                AND ( beginn <= NOW() OR beginn IS NULL )
                AND ( ende >= NOW() OR ende IS NULL )
                ORDER BY ende DESC NULLS FIRST, beginn DESC NULLS LAST
            ';
        }
        else
        {
            $condition =  '
                mitarbeiter_uid = '. $this->escape($uid). '
                ORDER BY ende DESC NULLS FIRST, beginn DESC NULLS LAST
            ';
        }

	    return $this->loadWhere($condition);
    }

	/**
	 * Gets Verwendungen of the user, optionally in a time span.
	 * @param string $uid
	 * @param string $beginn
	 * @param string $ende
	 * @return array
	 */
	public function getVerwendungen($uid, $beginn = null, $ende = null)
	{
		$params = array($uid);

		$qry = 'SELECT * FROM bis.tbl_bisverwendung
				WHERE mitarbeiter_uid = ?';

		if (isset($beginn))
		{
			$qry .= ' AND ( ende >= ? OR ende IS NULL )';
			$params[] = $beginn;
		}

		if (isset($ende))
		{
			$qry .= ' AND ( beginn <= ? OR beginn IS NULL )';
			$params[] = $ende;
		}

        $qry .= ' ORDER BY beginn, ende';

		return $this->execQuery($qry, $params);
	}
}
