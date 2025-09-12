<?php
class Vertrag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_vertrag';
		$this->pk = 'vertrag_id';

        $this->load->model('accounting/Vertragvertragsstatus_model', 'VertragvertragsstatusModel');
        $this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
        $this->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');
	}

    /**
     * Saves Vertrag for a Lehrauftrag and sets Vertragsstatus to 'bestellt'.
     * Also updates vertrag_id in tbl_lehreinheitmitarbeiter or tbl_projektbetreuer.
     * @param $person_id
     * @param $lehrveranstaltung_id
     * @param $lehreinheit_id
     * @param $projektarbeit_id
     * @param $betrag                   Monetary amount of that Lehreinheit / Projektbetreuung.
     * @param $vertragsstunden          Working hours of that Lehreinheit / Projektbetreuung.
     * @param $studiensemester_kurzbz
     * @param $vertragstyp_kurzbz
     * @return array|null               On success object. On failure null.
     */
	public function save($person_id, $mitarbeiter_uid, $lehrveranstaltung_id, $lehreinheit_id, $projektarbeit_id = null, $vertragsstunden, $betrag, $studiensemester_kurzbz)
    {
        $person_id = (isset($person_id) && is_numeric($person_id))
            ? $person_id
            : show_error('peron_id must be set and numeric.');
        $lehreinheit_id = (isset($lehreinheit_id) && is_numeric($lehreinheit_id))
            ? $lehreinheit_id
            : show_error('lehreinheit_id must be set and numeric.');
        $lehrveranstaltung_id = (isset($lehrveranstaltung_id) && is_numeric($lehrveranstaltung_id))
            ? $lehrveranstaltung_id
            : show_error('lehrveranstaltung_id must be set and numeric.');
        $projektarbeit_id = (isset($projektarbeit_id) && is_numeric($projektarbeit_id))
            ? $projektarbeit_id
            : null;
        $vertragsstunden = (isset($vertragsstunden) && is_numeric($vertragsstunden))
            ? $vertragsstunden
            : 0;
        $betrag = (isset($betrag) && is_numeric($betrag))
            ? $betrag
            : 0;
        $mitarbeiter_uid = (isset($mitarbeiter_uid) && is_string($mitarbeiter_uid))
            ? $mitarbeiter_uid
            : show_error('mitarbeiter_uid must be set and a string value.');;

        $vertragstyp_kurzbz = (is_null($projektarbeit_id)) ? 'Lehrauftrag' : 'Betreuung';

        // First check if Vertrag already exists for that Lehrauftrag or for that Projektbetreuerauftrag
        if ($vertragstyp_kurzbz == 'Lehrauftrag')
        {
            if ($this->LehreinheitmitarbeiterModel->hasVertrag($mitarbeiter_uid, $lehreinheit_id))
            {
            	return error('Lehrauftrag existiert bereits');	// Exit if Lehrauftrag already has Vertrag
            }
        }
        elseif ($vertragstyp_kurzbz == 'Betreuung')
        {
            if ($this->ProjektbetreuerModel->hasVertrag($person_id, $projektarbeit_id))
            {
                return error('Lehrauftrag existiert bereits');   // Exit if Projektbetreuung already has Vertrag
            }
        }

        // If Vertrag does not exist, create now
        // Vertragsbezeichnung
        $bezeichnung = $this->_writeVertragsbezeichung($lehrveranstaltung_id, $studiensemester_kurzbz);

        // Start DB transaction
        $this->db->trans_start(false);

        // Insert Vertragsdata
        $result = $this->insert(
            array(
                'person_id' => $person_id,
                'lehrveranstaltung_id' => $lehrveranstaltung_id,
                'vertragstyp_kurzbz' => $vertragstyp_kurzbz,
                'bezeichnung' => $bezeichnung,
                'betrag' => $betrag,
                'insertamum' => 'NOW()',
                'insertvon' => getAuthUID(),
                'vertragsdatum' => 'NOW()',
                'vertragsstunden' => $vertragsstunden,
                'vertragsstunden_studiensemester_kurzbz' => $studiensemester_kurzbz
            )
        );

        // Retrieve primary key
        $vertrag_id = $result->retval;

        // If Vertrag was created successfully, update vertrag_id
        if (isSuccess($result))
        {
            // if Lehrtätigkeit, update vertrag_id in tbl_lehreinheitmitarbeiter
            if ($vertragstyp_kurzbz == 'Lehrauftrag')
            {
                $this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
                $result = $this->LehreinheitmitarbeiterModel->update(
                    array(
                        'lehreinheit_id' =>  $lehreinheit_id,
                        'mitarbeiter_uid' =>$mitarbeiter_uid
                    ),
                    array(
                        'vertrag_id' => $vertrag_id
                    )
                );
            }
            // if (Projekt-)Betreuung, update vertrag_id in tbl_projektbetreuer
            elseif ($vertragstyp_kurzbz == 'Betreuung')
            {
                $this->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');
                $result = $this->ProjektbetreuerModel->update(
                    array(
                        'person_id' =>  $person_id,
                        'projektarbeit_id' => $projektarbeit_id
                    ),
                    array(
                        'vertrag_id' => $vertrag_id
                    )
                );
            }
        }

        // If updating vertrag_id was successfully, set Status to 'bestellt'
        if (isSuccess($result))
        {
            $result = $this->VertragvertragsstatusModel->setStatus($vertrag_id, $mitarbeiter_uid, 'bestellt');
        }

        // Transaction complete!
        $this->db->trans_complete();

        // Check if everything went ok during the transaction
        if ($this->db->trans_status() === false || isError($result))
        {
            $this->db->trans_rollback();
            return error($result->msg, EXIT_ERROR);
        }
        else
        {
            $this->db->trans_commit();
            return success($vertrag_id);
        }
    }

    /**
     * Updates Vertrag and, if resets vertragsstatus as follows:
     * - if vertragsstatus 'erteilt': delete status 'erteilt' and update date of status 'bestellt'
     * - if vertragsstatus 'bestellt': update date of status 'bestellt'
     * @param $vertrag_obj  Object with vertrag properties vertrag_id, vertragsstunden, betrag.
     * @param $mitarbeiter_uid
     */
    public function updateVertrag($vertrag_id, $vertragsstunden, $betrag, $mitarbeiter_uid)
    {
        $vertrag_id = (isset($vertrag_id) && is_numeric($vertrag_id))
            ? $vertrag_id
            : show_error('vertrag_id must be set and numeric.');
        $vertragsstunden = (isset($vertragsstunden) && is_numeric($vertragsstunden))
            ? $vertragsstunden
            : 0;
        $betrag = (isset($betrag) && is_numeric($betrag))
            ? $betrag
            : 0;
        $mitarbeiter_uid = (isset($mitarbeiter_uid) && is_string($mitarbeiter_uid))
            ? $mitarbeiter_uid
            : show_error('mitarbeiter_uid must be set and a string value.');

        // Start DB transaction
        $this->db->trans_start(false);

        // Update contract
        $result = $this->update(
            $vertrag_id,
            array(
                'vertragsstunden' => $vertragsstunden,
                'betrag' => $betrag,
                'updateamum' => $this->escape('NOW()'),
                'updatevon' => getAuthUID()
            )
        );

        // If last vertragsstatus is 'erteilt', delete the status
        if (isSuccess($result))
        {
            $result = $this->VertragvertragsstatusModel->getLastStatus($vertrag_id, $mitarbeiter_uid);

            $lastStatus = getData($result)[0]->vertragsstatus_kurzbz;

            if ($lastStatus == 'erteilt')
            {
                $result = $this->VertragvertragsstatusModel->deleteStatus($vertrag_id, 'erteilt');
            }
        }

        // Update date of status 'bestellt'
        if (isSuccess($result))
        {
            $result = $this->VertragvertragsstatusModel->updateStatus($vertrag_id, 'bestellt');
        }

        // Transaction complete!
        $this->db->trans_complete();

        // Check if everything went ok during the transaction
        if ($this->db->trans_status() === false || isError($result))
        {
            $this->db->trans_rollback();
            return error($result->msg, EXIT_ERROR);
        }
        else
        {
            $this->db->trans_commit();
            return success('Contract successfully updated.');
        }
    }

    /**
     * Gets Lehreinheit ID corresponding to the contract.
     * @param $vertrag_id
     * @return array
     */
    public function getLehreinheitID($vertrag_id)
    {
        $vertragstyp_kurzbz = null;

        $this->addSelect('vertragstyp_kurzbz');
        if ($result = getData($this->load($vertrag_id)))
        {
            $vertragstyp_kurzbz = $result[0]->vertragstyp_kurzbz;
        }
        else
        {
            return error('Fehler beim Laden des Vertrags.');
        }

        if ($vertragstyp_kurzbz == 'Lehrauftrag')
        {
            $this->LehreinheitmitarbeiterModel->addSelect('lehreinheit_id');
            if ($result = $this->LehreinheitmitarbeiterModel->loadWhere(array('vertrag_id' => $vertrag_id)))
            {
                return success($result->retval);
            }
            else
            {
                return error('Fehler beim Ermitteln der Lehreinheit ID');
            }

        }
        elseif ($vertragstyp_kurzbz == 'Betreuung')
        {
            $this->addSelect('lehreinheit_id');
            $this->addJoin('lehre.tbl_projektbetreuer', 'vertrag_id');
            $this->addJoin('lehre.tbl_projektarbeit', 'projektarbeit_id');
            if ($result = $this->loadWhere(array('vertrag_id' => $vertrag_id)))
            {
                return success($result->retval);
            }
            else
            {
                return error('Fehler beim Ermitteln der Lehreinheit ID');
            }
        }
    }

    /**
     * Gets (table) data of lehreinheit_id corresponding to the contract.
     * @param integer $vertrag_id
     * @param string $select To restrict fields, pass select string. e.g. 'lehrveranstaltung_id'.
     * @return array
     */
    public function getLehreinheitData($vertrag_id, $select = '*')
    {
        if ($result = getData($this->getLehreinheitID($vertrag_id)))
        {
            $this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
            $this->LehreinheitModel->addSelect($select);

            if($result = $this->LehreinheitModel->load($result[0]->lehreinheit_id))
            {
                return success($result->retval);
            }
            else
            {
                return error('Fehler beim Laden der Lehreinheit');
            }
        }
        else
        {
            return error('Fehler beim Ermitteln der Lehreinheit ID');
        }
    }

    /**
     * Prueft ob ein Mitarbeiter einen erteilten Vertrag zu einer Lehrveranstaltung besitzt.
     * @param $lehrveranstaltung_id ID der Lehrveranstaltung
     * @param $studiensemester_kurzbz Studiensemester das geprueft wird
     * @param $mitarbeiter_uid UID des Mitarbeiters
     */
    public function isVertragErteiltLV($lehrveranstaltung_id, $studiensemester_kurzbz, $mitarbeiter_uid)
    {
	    if (defined('CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON')
	     && CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON != '')
	    {
		    // Liegt das Studiensemester vor dem Pruefdatum, wird die LV immer als Erteilt angezeigt
		    $stsemquery = "
			    SELECT
				    tbl_studiensemester.start
			    FROM
				    public.tbl_studiensemester
			    WHERE
				    studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz)."
				    AND tbl_studiensemester.start < (
					    SELECT 
						start
					    FROM 
						public.tbl_studiensemester stsem 
					    WHERE
						stsem.studiensemester_kurzbz = " . $this->escape(CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON)."
					    )";

		    if ($stsemresult = $this->execReadOnlyQuery($stsemquery))
		    {
			    $stsemdata = getData($stsemresult);
			    if ($stsemdata && count($stsemdata) > 0)
			    {
				    // Wenn das Studiensemester vor dem Pruefdatum liegt, gilt der Vertrag immer als erteilt.
				    return true;
			    }
		    }
		    else
		    {
			    return false;
		    }
	    }

	    $query = "
		    SELECT
			    1
		    FROM
			    lehre.tbl_lehreinheitmitarbeiter
			    JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
			    JOIN lehre.tbl_vertrag USING(vertrag_id)
			    JOIN lehre.tbl_vertrag_vertragsstatus USING(vertrag_id)
		    WHERE
			    tbl_lehreinheitmitarbeiter.mitarbeiter_uid = " . $this->escape($mitarbeiter_uid) . "
			    AND tbl_lehreinheit.studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz) . "
			    AND tbl_lehreinheit.lehrveranstaltung_id = " . $this->escape(intval($lehrveranstaltung_id)) . "
			    AND tbl_vertrag_vertragsstatus.vertragsstatus_kurzbz='erteilt'
			    AND NOT EXISTS(
				    SELECT 
					1 
				    FROM 
					lehre.tbl_vertrag_vertragsstatus vstatus
				    WHERE 
					vstatus.vertrag_id = tbl_vertrag.vertrag_id
					AND vstatus.vertragsstatus_kurzbz = 'storno'
			    )
	    ";

	    if ($result = $this->execReadOnlyQuery($query))
	    {
		    $data = getData($result);
		    if ($data && count($data) > 0)
		    {
			    return true;
		    }
		    else
		    {
			    return false;
		    }
	    }
	    else
	    {
		    return false;
	    }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Private methods

    /**
     * Generate contract description.
     * Example: WS2017-BEE3-LIA-LAB
     * @param $lehrveranstaltung_id
     * @param $studiensemester_kurzbz   Studiensemester of Lehrauftrag (= when the lector will teach the lehrveranstaltung)
     * @return string   Returns e.g. WS2017-BBE5-GAP-LAB
     */
    private function _writeVertragsbezeichung($lehrveranstaltung_id, $studiensemester_kurzbz)
    {
        $bezeichnung = '';
        $this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
        $this->LehrveranstaltungModel->addSelect('tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.kurzbz AS "lv_kurzbz", lehrform_kurzbz, public.tbl_studiengang.typ, public.tbl_studiengang.kurzbz');
        $this->LehrveranstaltungModel->addJoin('lehre.tbl_studienplan_lehrveranstaltung', 'lehrveranstaltung_id');
        $this->LehrveranstaltungModel->addJoin('lehre.tbl_studienplan', 'studienplan_id');
        $this->LehrveranstaltungModel->addJoin('lehre.tbl_studienordnung', 'studienordnung_id');
        $this->LehrveranstaltungModel->addJoin('public.tbl_studiengang', 'public.tbl_studiengang.studiengang_kz = lehre.tbl_studienordnung.studiengang_kz');
        $result = $this->LehrveranstaltungModel->load($lehrveranstaltung_id);

        if (hasData($result))
        {
            $bezeichnung = $studiensemester_kurzbz. '-';
            $bezeichnung.= strtoupper($result->retval[0]->typ. $result->retval[0]->kurzbz). $result->retval[0]->semester. '-';
            $bezeichnung.= $result->retval[0]->lv_kurzbz. '-';
            $bezeichnung.= $result->retval[0]->lehrform_kurzbz;
        }

        return $bezeichnung;
    }
}
