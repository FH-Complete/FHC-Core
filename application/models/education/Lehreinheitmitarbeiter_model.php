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

    /**
     * @param integer       $lehrveranstaltung_id
     * @param string        $studiensemester_kurzbz
     * 
     * @return stdClass
     */
    public function getForLv($lehrveranstaltung_id, $studiensemester_kurzbz)
    {
        $this->addSelect('ma.uid, ma.vorname, ma.nachname, ma.titelpre, ma.titelpost, lehrfunktion_kurzbz');
        $this->addGroupBy('ma.uid, ma.vorname, ma.nachname, ma.titelpre, ma.titelpost, lehrfunktion_kurzbz');

        $this->addJoin('lehre.tbl_lehreinheit le', 'lehreinheit_id');
        $this->addJoin('campus.vw_mitarbeiter ma', $this->dbTable . '.mitarbeiter_uid=ma.uid');

        $this->addOrder('nachname');
        $this->addOrder('vorname');

        if (defined('CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON') && CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON != '')
        {
            $this->addJoin('(SELECT vertrag_id, CASE WHEN vertragsstatus_kurzbz=\'storno\' THEN 0 WHEN vertragsstatus_kurzbz=\'erteilt\' THEN 1 ELSE 2 END AS vertragsstatus_kurzbz FROM lehre.tbl_vertrag_vertragsstatus) v', 'vertrag_id', 'LEFT');
            $having = $this->db->compile_binds('(EXISTS (SELECT 1 FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=? AND tbl_studiensemester.start < (SELECT start FROM public.tbl_studiensemester stsem WHERE stsem.studiensemester_kurzbz=?)) OR MIN(vertragsstatus_kurzbz)=1)', [
                $studiensemester_kurzbz,
                CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON
            ]);
            $this->db->having($having);
        }

        return $this->loadWhere([
            'lehrveranstaltung_id' => $lehrveranstaltung_id,
            'studiensemester_kurzbz' => $studiensemester_kurzbz
        ]);
    }

}
