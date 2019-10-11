<?php
class Vertragvertragsstatus_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_vertrag_vertragsstatus';
		$this->pk = array('vertragsstatus_kurzbz', 'vertrag_id');
	}

    /**
     * Updates the date of the given vertragsstatus.
     * @param $vertrag_id
     * @param $vertragsstatus_kurzbz
     * @return array
     */
    public function updateStatus($vertrag_id, $vertragsstatus_kurzbz)
    {
        $user = getAuthUID();
        return $this->update(
            array(
                'vertrag_id' => $vertrag_id,
                'vertragsstatus_kurzbz' => $vertragsstatus_kurzbz
            ),
            array(
                'datum' => $this->escape('NOW()'),
                'updateamum' => $this->escape('NOW()'),
                'updatevon' => $user,
            )
        );
    }


    /**
     * Deletes the given vertragsstatus of the contract.
     * @param $vertrag_id
     * @param $vertragsstatus_kurbz
     * @return array
     */
    public function deleteStatus($vertrag_id, $vertragsstatus_kurzbz)
    {
        return $this->delete(
            array(
                'vertrag_id' => $vertrag_id,
                'vertragsstatus_kurzbz' => $vertragsstatus_kurzbz
            )
        );
    }


}
