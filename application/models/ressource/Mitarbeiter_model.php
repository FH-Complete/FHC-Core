<?php
class Mitarbeiter_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_mitarbeiter';
		$this->pk = 'mitarbeiter_uid';
	}

    /**
     * Checks if the user is a lector.
     * @param string $uid
     * @param boolean null $fixangestellt
     * @return bool
     */
    public function isLektor($uid, $fixangestellt = null)
    {
        $this->addSelect('1');

        if (is_bool($fixangestellt))
        {
            $result = $this->loadWhere(array('mitarbeiter_uid' => $uid, 'lektor' => true, 'fixangestellt' => $fixangestellt));
        }
        else    // Default: if lektor is true
        {
            $result = $this->loadWhere(array('mitarbeiter_uid' => $uid, 'lektor' => true));
        }

        if(hasData($result))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
