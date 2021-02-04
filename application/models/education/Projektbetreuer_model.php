<?php
class Projektbetreuer_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_projektbetreuer';
		$this->pk = array('betreuerart_kurzbz', 'projektarbeit_id', 'person_id');
	}

    /**
     * Checks if Projektauftrag has a contract.
     * @param $person_id
     * @param $projektarbeit_id
     * @return array|bool|int       Returns vertrag_id if contract exists. False if doesnt exist. On error array.
     */
    public function hasVertrag($person_id, $projektarbeit_id)
    {
        if (is_numeric($person_id) && is_numeric($projektarbeit_id))
        {
            $result = $this->load(array(
                'person_id' => $person_id,
                'projektarbeit_id' => $projektarbeit_id
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

	/**
	 * Get Projektbetreuer data by authentification token
	 * @param $zugangstoken
	 * @return object
	 */
    public function getBetreuerByToken($zugangstoken)
	{
		$qry = '
			SELECT tbl_projektbetreuer.person_id, tbl_projektbetreuer.projektarbeit_id, student_uid
			FROM lehre.tbl_projektbetreuer
			JOIN lehre.tbl_projektarbeit USING (projektarbeit_id)
			WHERE zugangstoken = ? AND zugangstoken_gueltigbis >= NOW()
			ORDER BY tbl_projektbetreuer.insertamum DESC, projektarbeit_id DESC
			LIMIT 1
		';

		return $this->execQuery($qry, array($zugangstoken));
	}
}
