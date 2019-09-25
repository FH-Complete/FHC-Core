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
     * Checks if the user is a Mitarbeiter.
     * @param string $uid
     * @param boolean null $fixangestellt
     * @return array
     */
    public function isMitarbeiter($uid, $fixangestellt = null)
    {
        $this->addSelect('1');

        if (is_bool($fixangestellt))
        {
            $result = $this->loadWhere(array('mitarbeiter_uid' => $uid, 'fixangestellt' => $fixangestellt));
        }
        else    // default
        {
            $result = $this->loadWhere(array('mitarbeiter_uid' => $uid));
        }

        if(hasData($result))
        {
            return success(true);
        }
        else
        {
            return success(false);
        }
    }
}
