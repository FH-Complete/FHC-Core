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
		$this->hasSequence = false;

		$this->load->model('accounting/Vertrag_model', 'VertragModel');
		$this->load->model('ressource/stundenplandev_model', 'StundenplandevModel');
		$this->load->model('ressource/stundenplan_model', 'StundenplanModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
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

	public function getLektorenByLe($lehreinheit_id)
	{
		$this->addSelect('vorname, nachname, tbl_lehreinheitmitarbeiter.*, stundenplan.verplant');
		$this->addJoin('tbl_benutzer', 'uid = mitarbeiter_uid');
		$this->addJoin('tbl_person', 'person_id');

		$this->addJoin('(
			SELECT 1 as verplant, lehreinheit_id, mitarbeiter_uid
			FROM lehre.tbl_stundenplandev
			GROUP BY lehreinheit_id, mitarbeiter_uid
			
		) stundenplan', 'stundenplan.mitarbeiter_uid = tbl_lehreinheitmitarbeiter.mitarbeiter_uid AND stundenplan.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id', 'LEFT');

		return $this->loadWhere(array('tbl_lehreinheitmitarbeiter.lehreinheit_id' => $lehreinheit_id));
	}

	public function getByLeLektor($lehreinheit_id, $mitarbeiter_uid)
	{
		$this->addSelect('vorname, nachname, tbl_lehreinheitmitarbeiter.*');
		$this->addJoin('tbl_benutzer', 'uid = mitarbeiter_uid');
		$this->addJoin('tbl_person', 'person_id');
		return $this->loadWhere(array('tbl_lehreinheitmitarbeiter.lehreinheit_id' => $lehreinheit_id, 'tbl_lehreinheitmitarbeiter.mitarbeiter_uid' => $mitarbeiter_uid));
	}

	public function deleteLektorFromLe($lehreinheit_id, $mitarbeiter_uid)
	{
		if (defined('FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN') && FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN)
		{
			$vertrag_result = $this->VertragModel->getVertrag($mitarbeiter_uid, $lehreinheit_id);

			if (hasData($vertrag_result))
				return error("Loeschen nur nach Stornierung des Vertrags möglich");
		}

		$stundenplandev_result = $this->StundenplandevModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id, 'mitarbeiter_uid' => $mitarbeiter_uid));
		$stundenplan_result = $this->StundenplanModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id, 'mitarbeiter_uid' => $mitarbeiter_uid));

		if (hasData($stundenplandev_result) || hasData($stundenplan_result))
			return error("Diese/r LektorIn kann nicht gelöscht werden da er schon verplant ist");

		$result = $this->loadWhere(array('lehreinheit_id' => $lehreinheit_id, 'mitarbeiter_uid' => $mitarbeiter_uid));

		if (hasData($result))
		{
			$le_mitarbeiter_array = getData($result)[0];

			if ($le_mitarbeiter_array->vertrag_id !== null)
			{
				$vertrag_result = $this->VertragModel->deleteVertrag($le_mitarbeiter_array->vertrag_id);
				if (isError($vertrag_result))
					return $vertrag_result;
			}

			$delete_result = $this->delete(array('lehreinheit_id' => $lehreinheit_id, 'mitarbeiter_uid' => $mitarbeiter_uid));

			if (isError($delete_result))
				return $delete_result;

			return success($delete_result);
		}
	}


}
