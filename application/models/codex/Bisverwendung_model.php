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
     * @param bool $active If false, returns latest Verwendung no matter if actual or not (ignores ending/beginning date).
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
                ORDER BY ende DESC NULLS LAST, beginn DESC NULLS LAST
            ';
        }
        else
        {
            $condition =  '
                mitarbeiter_uid = '. $this->escape($uid). '
                ORDER BY ende DESC NULLS LAST, beginn DESC NULLS LAST
            ';
        }

	    return $this->loadWhere($condition);
    }
}
