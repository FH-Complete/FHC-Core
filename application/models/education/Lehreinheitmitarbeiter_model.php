<?php
class Lehreinheitmitarbeiter_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehreinheitmitarbeiter';
		$this->pk = array('mitarbeiter_uid', 'lehreinheit_id');
	}

    /**
     * Checks if Lehrauftrag has a contract.
     * @param $mitarbeiter_uid
     * @param $lehreinheit_id
     * @return array|bool|int   Returns vertrag_id if contract exists. False if doesnt exist. On error array.
     */
    public function hasVertrag($mitarbeiter_uid, $lehreinheit_id)
    {
        if(is_string($mitarbeiter_uid) && is_numeric($lehreinheit_id))
        {
            $result = $this->load(array(
                'mitarbeiter_uid' => $mitarbeiter_uid,
                'lehreinheit_id' => $lehreinheit_id
            ));

            if (hasData($result))
            {
                return (is_null($result->retval[0]->vertrag_id)) ? false : intval($result->retval[0]->vertrag_id);
            }
            else
            {
                return error($result->msg, EXIT_ERROR);
            }
        }
       else
       {
           return error ('Incorrect parameter type');
       }
    }
}
