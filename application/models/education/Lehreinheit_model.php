<?php

use \CI3_Events as Events;
class Lehreinheit_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehreinheit';
		$this->pk = 'lehreinheit_id';

		$this->load->model('education/lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('education/lehreinheitgruppe_model', 'LehreinheitgruppeModel');
		$this->load->model('education/lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
		$this->load->model('organisation/studiengang_model', 'StudiengangModel');
		$this->load->model('ressource/stundenplandev_model', 'StundenplandevModel');
		$this->load->model('ressource/stundenplan_model', 'StundenplanModel');
		$this->load->model('system/Log_model', 'LogModel');
	}

	/**
	 * Gets Lehreinheiten for a Lehrveranstaltung in a Studiensemester.
	 * Includes Lehrfach, Lehreinheitgruppen and Lektoren.
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester
	 * @return array with Lehreinheiten and their Lehreinheitgruppen
	 */
	public function getLesForLv($lehrveranstaltung_id, $studiensemester)
	{
		$lehreinheiten = array();

		$this->addSelect(
			'lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrform_kurzbz,
			stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ,
			sprache, lehre, unr, lvnr, lehrfach_id, gewicht'
		);
		$this->addOrder('lehreinheit_id');
		$les = $this->loadWhere(
			array('lehrveranstaltung_id' => $lehrveranstaltung_id,
				'studiensemester_kurzbz' => $studiensemester)
		);

		if (hasData($les))
		{
			$this->LehrveranstaltungModel->addSelect('kurzbz, bezeichnung');
			foreach ($les->retval as $le)
			{
				$lehrfach = $this->LehrveranstaltungModel->load($le->lehrfach_id);
				if (hasData($lehrfach))
				{
					$letoadd = $le;
					$letoadd->lehrfach_bezeichnung = $lehrfach->retval[0]->bezeichnung;
					$letoadd->lehrfach_kurzbz = $lehrfach->retval[0]->kurzbz;

					// add lehreinheitgruppen, each lehreinheitid
					// having (maybe multiple) lehreinheitgruppen
					$letoadd->lehreinheitgruppen = array();

					$this->LehreinheitgruppeModel->addSelect('lehre.tbl_lehreinheitgruppe.*, tbl_gruppe.bezeichnung, tbl_gruppe.direktinskription');
					$this->LehreinheitgruppeModel->addJoin('public.tbl_gruppe', 'gruppe_kurzbz', 'LEFT');

					$lehreinheitgruppen = $this->LehreinheitgruppeModel->loadWhere(array('lehreinheit_id' => $le->lehreinheit_id));

					if (hasData($lehreinheitgruppen))
					{
						foreach ($lehreinheitgruppen->retval as $lehreinheitgruppe)
						{
							$studiengangresponse = $this->StudiengangModel->load($lehreinheitgruppe->studiengang_kz);
							if (hasData($studiengangresponse))
							{
								$studiengang = $studiengangresponse->retval[0];
								$stgkuerzel = mb_strtoupper($studiengang->typ.$studiengang->kurzbz);

								$letoadd->lehreinheitgruppen[] = array(
									'semester' => $lehreinheitgruppe->semester,
									'verband' => $lehreinheitgruppe->verband,
									'gruppe' => $lehreinheitgruppe->gruppe,
									'gruppe_kurzbz' => $lehreinheitgruppe->gruppe_kurzbz,
									'direktinskription' => $lehreinheitgruppe->direktinskription,
									'studiengang_kz' => $lehreinheitgruppe->studiengang_kz,
									'studiengang_kuerzel' => $stgkuerzel
								);
							}
						}
					}

					// add lektoren
					$letoadd->lektoren = array();
					$lehreinheitmitarbeiter = $this->LehreinheitmitarbeiterModel->loadWhere(array('lehreinheit_id' => $le->lehreinheit_id));
					if (hasData($lehreinheitmitarbeiter))
					{
						foreach ($lehreinheitmitarbeiter->retval as $lehreinheitma)
						{
							$letoadd->lektoren[] = $lehreinheitma->mitarbeiter_uid;
						}
					}

					$lehreinheiten[] = $letoadd;
				}
			}
		}

		return $lehreinheiten;
	}

	/**
	 * Gets students of a Lehreinheit
	 * @param int $lehreinheit_id
	 * @return array
	 */
	public function getStudenten($lehreinheit_id)
	{
		$query = 'SELECT uid, vorname, nachname, prestudent_id '
			. 'FROM campus.vw_student_lehrveranstaltung '
			. 'JOIN campus.vw_student '
			. 'USING (uid) '
			. 'WHERE lehreinheit_id = ?'
			. ' ORDER BY nachname';

		return $this->execQuery($query, array($lehreinheit_id));
	}

	/**
	 * Gets emails of all Studierende in a lehrveranstaltung
	 * @param int $lehreinheit_id
	 * @return array
	 */
	public function getStudentenMail($lehreinheit_id)
	{
		  
		// logic used from cis_menu_lv.inc.php line 335
		return $this->execReadOnlyQuery("
		SELECT
			gruppe_kurzbz,
			CASE 
				WHEN nomail = TRUE THEN 'nomail'
				WHEN gruppe_kurzbz !='' THEN LOWER(gruppe_kurzbz || '@' || ?)
				ELSE LOWER(stg_typ || stg_kurzbz || semester || TRIM(verband) || TRIM(gruppe) || '@' || ?) 
			END AS mail
			
		FROM 
		(
			SELECT 
				distinct vw_lehreinheit.studiensemester_kurzbz, vw_lehreinheit.stg_kurzbz, vw_lehreinheit.stg_typ, vw_lehreinheit.semester,
				COALESCE(vw_lehreinheit.verband,'') as verband, COALESCE(vw_lehreinheit.gruppe,'') as gruppe, vw_lehreinheit.gruppe_kurzbz, tbl_gruppe.mailgrp,
				CASE
					WHEN mailgrp = TRUE OR mailgrp IS NULL THEN FALSE
					ELSE TRUE
				END as nomail
			FROM campus.vw_lehreinheit
			LEFT JOIN public.tbl_gruppe USING(gruppe_kurzbz)
			WHERE 
				vw_lehreinheit.lehrveranstaltung_id=
				(select distinct lehrveranstaltung_id from campus.vw_lehreinheit where lehreinheit_id=?)
				AND
				vw_lehreinheit.studiensemester_kurzbz =
				(select distinct studiensemester_kurzbz from campus.vw_lehreinheit where lehreinheit_id=?)
				AND (vw_lehreinheit.gruppe_kurzbz IS NULL OR
					(vw_lehreinheit.gruppe_kurzbz IS NOT NULL AND (SELECT COUNT(*) FROM public.tbl_benutzergruppe where gruppe_kurzbz = vw_lehreinheit.gruppe_kurzbz AND studiensemester_kurzbz = vw_lehreinheit.studiensemester_kurzbz) > 0))
				
		
		) AS subquery
		",[DOMAIN,DOMAIN,$lehreinheit_id,$lehreinheit_id ]);
	}

    public function getLehreinheitenForStudentAndStudienSemester($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
    {
	$query = <<<EOSQL
	    SELECT
	      le.* 
	    FROM 
	      lehre.tbl_lehreinheit le 
	    JOIN 
	      campus.vw_student_lehrveranstaltung vslv USING(lehreinheit_id)
	    WHERE 
	      vslv.lehrveranstaltung_id = {$this->escape($lehrveranstaltung_id)} AND 
	      vslv.uid = {$this->escape($student_uid)} AND 
	      vslv.studiensemester_kurzbz = {$this->escape($studiensemester_kurzbz)}
EOSQL;

	$res = $this->execReadOnlyQuery($query);
	return $res;
    }
    
    public function getLehrfachIdMitarbeiter($angezeigtes_stsem,$user,$lvid)
    {
	$query = "
	    SELECT
		distinct lehrfach_id
            FROM
                lehre.tbl_lehreinheit
                JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
            WHERE
                studiensemester_kurzbz=" . $this->escape($angezeigtes_stsem) . "
                AND mitarbeiter_uid=" . $this->escape($user)."
                AND lehrveranstaltung_id=" . $this->escape(intval($lvid));
	
	$res = $this->execReadOnlyQuery($query);
	return $res;
    }
    
    public function getLehrfachIdStudierender($angezeigtes_stsem,$user,$lvid)
    {
	$query = "
	    SELECT 
		distinct lehrfach_id
            FROM
                campus.vw_student_lehrveranstaltung
            WHERE
                lehrveranstaltung_id=" . $this->escape(intval($lvid))."
                AND studiensemester_kurzbz=" . $this->escape($angezeigtes_stsem)."
                AND uid=" . $this->escape($user);
	
	$res = $this->execReadOnlyQuery($query);
	return $res;
    }
    
    public function getLehreinheitInfo($lvid, $angezeigtes_stsem, $lehrfach_id)
    {
	$query = "
	    SELECT 
		* 
	    FROM (
		SELECT 
		    distinct on(uid) vorname, nachname, tbl_benutzer.uid as uid,
		    CASE 
			WHEN lehrfunktion_kurzbz='LV-Leitung' THEN true 
			ELSE false 
		    END as lvleiter
		FROM 
		    lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, 
		    public.tbl_benutzer, public.tbl_person
		WHERE
		    tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id
		    AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid = tbl_benutzer.uid
		    AND tbl_person.person_id = tbl_benutzer.person_id 
		    AND lehrveranstaltung_id = " . $this->escape(intval($lvid)) . " 
		    AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT like '_Dummy%' 
		    AND tbl_benutzer.aktiv = true 
		    AND tbl_person.aktiv = true 
		    AND studiensemester_kurzbz = " . $this->escape($angezeigtes_stsem);

	if($lehrfach_id != '')
	{
	    $query .= " AND tbl_lehreinheit.lehrfach_id = " . $this->escape(intval($lehrfach_id));
	}
	
	$query .= " ORDER BY uid, lvleiter desc) as a ORDER BY lvleiter desc, nachname, vorname";
	
	$res = $this->execReadOnlyQuery($query);
	return $res;
    }

	/**
	 * Gets Lehreinheiten for Lehrveranstaltungen in a Studiensemester.
	 * Without using tbl_lehrfach: bezeichnung and kurzbz ALWAYS from lehrveranstaltung
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester
	 * @return array with Lehreinheiten and their Lehreinheitgruppen
	 */
	public function getLesFromLvIds($lehrveranstaltung_id, $studiensemester_kurzbz = null)
	{
		$params = array($lehrveranstaltung_id);

		$query = "
			SELECT
			    lv.lehrveranstaltung_id,
			    le.lehreinheit_id,
				le.lehrform_kurzbz,
				lv.kurzbz,
				lv.bezeichnung,
				lv.semester,
				(
					SELECT
						STRING_AGG(CONCAT(leg.semester, leg.verband, leg.gruppe), ' ')
					FROM lehre.tbl_lehreinheitgruppe leg
					WHERE leg.lehreinheit_id = le.lehreinheit_id
				) AS gruppe,
			     STRING_AGG(tma.kurzbz, ' ') as kuerzel
			FROM
				lehre.tbl_lehreinheit le
			JOIN
				lehre.tbl_lehrveranstaltung lv ON lv.lehrveranstaltung_id = le.lehrveranstaltung_id
			JOIN
				lehre.tbl_lehreinheitmitarbeiter ma USING (lehreinheit_id)
			JOIN
				public.tbl_mitarbeiter tma USING (mitarbeiter_uid)
			WHERE
				lv.lehrveranstaltung_id = ?
				";

		if (isset($studiensemester_kurzbz))
		{
			$query .= " AND le.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		$query .="
			GROUP BY
                lv.lehrveranstaltung_id,
				le.lehreinheit_id,
				le.lehrform_kurzbz,
				lv.kurzbz,
				lv.bezeichnung,
				lv.semester
			ORDER BY
				le.lehreinheit_id;
		";

		return $this->execQuery($query, $params);
	}

	public function getAllLehreinheitenForLvaAndMaUid($lva_id, $ma_uid, $sem_kurzbz)
	{
		$query = "SELECT DISTINCT tbl_lehreinheitmitarbeiter.lehreinheit_id, tbl_lehreinheit.lehrveranstaltung_id, tbl_lehreinheit.lehrform_kurzbz,
						tbl_lehreinheitmitarbeiter.mitarbeiter_uid,
						tbl_lehreinheitgruppe.semester,
						tbl_lehreinheitgruppe.verband,
						tbl_lehreinheitgruppe.gruppe,
						tbl_lehreinheitgruppe.gruppe_kurzbz,
						tbl_lehrveranstaltung.kurzbz,
			 			tbl_studiengang.kurzbzlang,
			 			(SELECT COUNT(DISTINCT datum) FROM campus.vw_stundenplan WHERE lehreinheit_id = lehre.tbl_lehreinheit.lehreinheit_id) as termincount,
						(SELECT COUNT(*) FROM campus.vw_student_lehrveranstaltung WHERE lehreinheit_id = lehre.tbl_lehreinheit.lehreinheit_id) as studentcount
		FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
			JOIN lehre.tbl_lehreinheitgruppe USING(lehreinheit_id)
			JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			JOIN public.tbl_studiengang ON (tbl_lehreinheitgruppe.studiengang_kz = tbl_studiengang.studiengang_kz)
		WHERE lehrveranstaltung_id = ? AND studiensemester_kurzbz = ? AND mitarbeiter_uid = ?
		ORDER BY tbl_lehreinheitgruppe.gruppe_kurzbz";

		return $this->execQuery($query, [$lva_id, $sem_kurzbz, $ma_uid]);
	}


	public function getOes($lehreinheit_id)
	{
		$this->addSelect('tbl_lehrveranstaltung.studiengang_kz,
								tbl_lehrveranstaltung.lehrveranstaltung_id');
		$this->addJoin('lehre.tbl_lehrveranstaltung', 'tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id');
		$result = $this->loadWhere(array('lehreinheit_id' => $lehreinheit_id));

		if (isError($result)) return $result;

		if (hasData($result))
		{
			$lehrveranstaltung = getData($result)[0];
			$oe_result = $this->LehrveranstaltungModel->getAllOe($lehrveranstaltung->lehrveranstaltung_id);
			return success(hasData($oe_result) ? array_column(getData($oe_result), 'oe_kurzbz') : array(''));
		}
	}

	public function getLehrfachOe($lehreinheit_id)
	{
		$this->addSelect('lehrfach.oe_kurzbz');
		$this->addJoin('lehre.tbl_lehrveranstaltung lehrfach', 'lehrfach.lehrveranstaltung_id = tbl_lehreinheit.lehrfach_id', 'LEFT');
		return $this->loadWhere(array('lehreinheit_id' => $lehreinheit_id));
	}


	public function getByLvidStudiensemester($lv_id, $studiensemester_kurzbz, $mitarbeiter_uid = null, $fachbereich_kurzbz = null)
	{
		$qry = "WITH lehreinheiten AS (
				SELECT *
				FROM lehre.tbl_lehreinheit
				WHERE lehrveranstaltung_id = ?
					AND studiensemester_kurzbz = ?
			),
				". $this->_getGruppenCTE() . ", 
				". $this->_getLektorenCTE() . ", 
				". $this->_getFachbereichCTE() . ",
				". $this->_getTagsCTE() . "
				
				SELECT lehreinheiten.*,
						lehreinheiten.lehrform_kurzbz as lv_lehrform_kurzbz,
						tbl_lehrveranstaltung.kurzbz as lv_kurzbz,
						tbl_lehrveranstaltung.bezeichnung as lv_bezeichnung,
						COALESCE(tag_data_agg.tags, '[]'::json) AS tags,
						gruppen.gruppen,
						mitarbeiter.lektoren,
						mitarbeiter.le_planstunden,
						mitarbeiter.vorname,
						mitarbeiter.nachname,
						mitarbeiter.semesterstunden,
						fachbereich.bezeichnung as fachbereich,
						UPPER(CONCAT(tbl_studiengang.typ,tbl_studiengang.kurzbz)) as studiengang,
						semester
				FROM lehreinheiten
					LEFT JOIN lehre.tbl_lehrveranstaltung ON tbl_lehrveranstaltung.lehrveranstaltung_id = lehreinheiten.lehrfach_id
					LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
					LEFT JOIN tag_data_agg ON tag_data_agg.lehreinheit_id = lehreinheiten.lehreinheit_id
					LEFT JOIN mitarbeiter ON lehreinheiten.lehreinheit_id = mitarbeiter.lehreinheit_id
					LEFT JOIN fachbereich ON lehreinheiten.lehreinheit_id = fachbereich.lehreinheit_id
					LEFT JOIN gruppen ON lehreinheiten.lehreinheit_id = gruppen.lehreinheit_id
				WHERE true 
				";

		$params = array($lv_id, $studiensemester_kurzbz);

		if ($mitarbeiter_uid !== null)
		{
			$qry .= " AND lehreinheiten.lehreinheit_id IN ( SELECT lehreinheit_id FROM lehre.tbl_lehreinheitmitarbeiter WHERE mitarbeiter_uid = ?) ";
			$params[] = $mitarbeiter_uid;
		}

		if($fachbereich_kurzbz !== null)
		{
			$qry .= " AND EXISTS ( SELECT 1 FROM lehre.tbl_lehrveranstaltung JOIN public.tbl_fachbereich USING(oe_kurzbz) WHERE fachbereich_kurzbz= ? AND lehrveranstaltung_id=lehreinheiten.lehrfach_id)";
			$params[] = $fachbereich_kurzbz;
		}
		$qry .= " ORDER BY lehrveranstaltung_id;";

		return $this->execReadOnlyQuery($qry, $params);
	}

	private function getLVTmp($stg_kz = null)
	{
		$qry = "SELECT DISTINCT ON(lehrveranstaltung_id) *, 
						'' as stundenblockung,
						'' as lehreinheit_id,
						'' as wochenrythmus,
						'' as raumtyp,
						'' as raumtypalternativ,
						'' as gruppen,
						'' as studienplan_id,
						'' as studienplan_beeichnung, 
						UPPER(CONCAT(vw_lehreinheit.stg_typ, vw_lehreinheit.stg_kurzbz)) as studiengang
                FROM campus.vw_lehreinheit
				WHERE mitarbeiter_uid = ?
				  AND studiensemester_kurzbz = ?";

		if (!is_null($stg_kz)) {
			$qry .= " AND lv_studiengang_kz = ?";
		}

		return $qry;
	}

	public function getLvsByEmployee($mitarbeiter_uid, $studiensemester_kurzbz, $stg_kz = null)
	{
		$qry = "WITH lvs AS (" . $this->getLVTmp($stg_kz) . ")
				SELECT lvs.*
				FROM lvs
				";

		$params = array($mitarbeiter_uid, $studiensemester_kurzbz);
		if (!is_null($stg_kz))
		{
			$params[] = $stg_kz;
		}
		return $this->execReadOnlyQuery($qry, $params);
	}

	public function deleteLehreinheit($lehreinheit_id)
	{
		$lehreinheit = $this->loadWhere(array('lehreinheit_id' => $lehreinheit_id));

		if (isError($lehreinheit)) return $lehreinheit;

		if (!hasData($lehreinheit))
			return error("Lehreinheit not found!");

		$errorReasons = [];
		$addError = function ($reason = null) use (&$errorReasons)
		{
			if ($reason !== null)
			{
				$errorReasons[] = $reason;
			}
		};

		$stundenplandev_result = $this->StundenplandevModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id));
		$stundenplan_result = $this->StundenplanModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id));

		if (hasData($stundenplan_result) || hasData($stundenplandev_result))
			$addError('Dieser LV-Teil ist bereits im LV-Plan verplant und kann daher nicht geloescht werden!');

		Events::trigger(
			'lehreinheit_delete_check',
			$addError,
			$lehreinheit_id
		);

		if (!empty($errorReasons)) return error($errorReasons);

		$this->db->trans_begin();

		Events::trigger(
			'lehreinheit_delete',
			$addError,
			$lehreinheit_id
		);

		$undosql = '';

		$lehreinheit_gruppe_result = $this->LehreinheitgruppeModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id));
		if (hasData($lehreinheit_gruppe_result))
		{
			foreach (getData($lehreinheit_gruppe_result) as $row)
			{
				$values = [
					$this->db->escape($row->lehreinheitgruppe_id),
					$this->db->escape($row->lehreinheit_id),
					$this->db->escape($row->studiengang_kz),
					$this->db->escape($row->semester),
					$this->db->escape($row->verband),
					$this->db->escape($row->gruppe),
					$this->db->escape($row->gruppe_kurzbz),
					$this->db->escape($row->updateamum),
					$this->db->escape($row->updatevon),
					$this->db->escape($row->insertamum),
					$this->db->escape($row->insertvon)
				];

				$undosql .= "INSERT INTO lehre.tbl_lehreinheitgruppe (
						lehreinheitgruppe_id,
						lehreinheit_id,
						studiengang_kz,
						semester,
						verband,
						gruppe,
						gruppe_kurzbz,
						updateamum,
						updatevon,
						insertamum,
						insertvon
					) VALUES (" . implode(', ', $values) . ");\n";
			}

			$lehreinheit_gruppe_delete_result = $this->LehreinheitgruppeModel->delete(array('lehreinheit_id' => $lehreinheit_id));

			if (isError($lehreinheit_gruppe_delete_result))
				$addError(getError($lehreinheit_gruppe_delete_result));
		}

		$lehreinheit_mitarbeiter_result = $this->LehreinheitmitarbeiterModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id));
		if (hasData($lehreinheit_mitarbeiter_result))
		{
			foreach (getData($lehreinheit_mitarbeiter_result) as $row)
			{
				$values = [
					$this->db->escape($row->lehreinheit_id),
					$this->db->escape($row->mitarbeiter_uid),
					$this->db->escape($row->lehrfunktion_kurzbz),
					$this->db->escape($row->planstunden),
					$this->db->escape($row->stundensatz),
					$this->db->escape($row->faktor),
					$this->db->escape($row->anmerkung),
					$this->db->escape($row->bismelden),
					$this->db->escape($row->updateamum),
					$this->db->escape($row->updatevon),
					$this->db->escape($row->insertamum),
					$this->db->escape($row->insertvon),
					$this->db->escape($row->semesterstunden)
				];

				$undosql .= "INSERT INTO lehre.tbl_lehreinheitmitarbeiter (
								lehreinheit_id,
								mitarbeiter_uid,
								lehrfunktion_kurzbz,
								planstunden,
								stundensatz,
								faktor,
								anmerkung,
								bismelden,
								updateamum,
								updatevon,
								insertamum,
								insertvon,
								semesterstunden
							) VALUES (" . implode(', ', $values) . ");\n";
			}

			$lehreinheit_mitarbeiter_delete_result = $this->LehreinheitmitarbeiterModel->delete(array('lehreinheit_id' => $lehreinheit_id));
			if (isError($lehreinheit_mitarbeiter_delete_result))
				$addError(getError($lehreinheit_mitarbeiter_delete_result));
		}

		foreach (getData($lehreinheit) as $row)
		{
			$values = [
				$this->db->escape($row->lehreinheit_id),
				$this->db->escape($row->lehrveranstaltung_id),
				$this->db->escape($row->studiensemester_kurzbz),
				$this->db->escape($row->lehrfach_id),
				$this->db->escape($row->lehrform_kurzbz),
				$this->db->escape($row->stundenblockung),
				$this->db->escape($row->wochenrythmus),
				$this->db->escape($row->start_kw),
				$this->db->escape($row->raumtyp),
				$this->db->escape($row->raumtypalternativ),
				$this->db->escape($row->sprache),
				$this->db->escape($row->lehre),
				$this->db->escape($row->anmerkung),
				$this->db->escape($row->unr),
				$this->db->escape($row->lvnr),
				$this->db->escape($row->updateamum),
				$this->db->escape($row->updatevon),
				$this->db->escape($row->insertamum),
				$this->db->escape($row->insertvon),
			];

			$undosql .= "INSERT INTO lehre.tbl_lehreinheit (
						lehreinheit_id,
						lehrveranstaltung_id,
						studiensemester_kurzbz,
						lehrfach_id,
						lehrform_kurzbz,
						stundenblockung,
						wochenrythmus,
						start_kw,
						raumtyp,
						raumtypalternativ,
						sprache,
						lehre,
						anmerkung,
						unr,
						lvnr,
						updateamum,
						updatevon,
						insertamum,
						insertvon
					) VALUES (" . implode(', ', $values) . ");\n";
		}
		$lehreinheit_result = $this->delete($lehreinheit_id);

		$deleteSql = "DELETE FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id = " . $this->db->escape($lehreinheit_id) ."; \n
						DELETE FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id = " . $this->db->escape($lehreinheit_id) ."; \n
						DELETE FROM lehre.tbl_lehreinheit WHERE lehreinheit_id = " . $this->db->escape($lehreinheit_id) .";";
		if (isError($lehreinheit_result))
			$addError($lehreinheit_result);

		$log_result = $this->LogModel->insert([
			'sql' => $deleteSql,
			'sqlundo' => $undosql,
			'beschreibung' => 'Lehreinheit loeschen - ' . $lehreinheit_id,
			'mitarbeiter_uid' => getAuthUID(),
		]);

		if (isError($log_result))
			$addError($log_result);

		if (!empty($errorReasons))
		{
			$this->db->trans_rollback();
			return error($errorReasons);
		}

		$this->db->trans_commit();
		return success('Contract successfully updated.');
	}

	private function _getGruppenCTE()
	{
		return "gruppen AS (
					SELECT
						lehreinheit_id,
						 STRING_AGG(
								 CASE
									 WHEN (tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL OR tbl_lehreinheitgruppe.gruppe_kurzbz = '')
										 THEN
										 UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) ||
										 COALESCE(TRIM(tbl_lehreinheitgruppe.semester::text), '') ||
										 COALESCE(TRIM(tbl_lehreinheitgruppe.verband), '') ||
										 COALESCE(TRIM(tbl_lehreinheitgruppe.gruppe), '')
									 ELSE
										 CASE
											 WHEN NOT tbl_gruppe.direktinskription THEN tbl_lehreinheitgruppe.gruppe_kurzbz
											 ELSE NULL
											 END
									 END,
								 ' ' 
									ORDER BY
										UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz),
										COALESCE(TRIM(tbl_lehreinheitgruppe.semester::text), ''),
										COALESCE(TRIM(tbl_lehreinheitgruppe.verband), ''),
										COALESCE(TRIM(tbl_lehreinheitgruppe.gruppe), ''),
										COALESCE(tbl_lehreinheitgruppe.gruppe_kurzbz, '')
						 ) AS gruppen
					 FROM lehre.tbl_lehreinheitgruppe
						LEFT JOIN public.tbl_studiengang USING (studiengang_kz)
						LEFT JOIN public.tbl_gruppe USING (gruppe_kurzbz)
						JOIN lehreinheiten USING(lehreinheit_id)
						GROUP BY lehreinheit_id
				)";
	}
	private function _getLektorenCTE()
	{
		return "mitarbeiter AS (
					 SELECT
						tbl_lehreinheitmitarbeiter.lehreinheit_id,
						STRING_AGG(m.kurzbz, ' ') AS lektoren,
						STRING_AGG(tbl_person.vorname, ' ') AS vorname,
						STRING_AGG(tbl_person.nachname, ' ') AS nachname,
						STRING_AGG(tbl_lehreinheitmitarbeiter.semesterstunden::text, ' ') AS semesterstunden,
						STRING_AGG(tbl_lehreinheitmitarbeiter.planstunden::text, ' ') AS le_planstunden
					FROM lehre.tbl_lehreinheitmitarbeiter
						JOIN public.tbl_mitarbeiter m USING (mitarbeiter_uid)
						JOIN lehreinheiten USING(lehreinheit_id)
						JOIN public.tbl_benutzer ON mitarbeiter_uid = uid
						JOIN public.tbl_person ON tbl_benutzer.person_id = tbl_person.person_id
					GROUP BY tbl_lehreinheitmitarbeiter.lehreinheit_id
				)";
	}

	private function _getFachbereichCTE()
	{
		return "fachbereich AS (
					SELECT
						 CONCAT(tbl_organisationseinheit.bezeichnung, ' (',  tbl_organisationseinheit.organisationseinheittyp_kurzbz, ')') as bezeichnung,
						 lehreinheiten.lehreinheit_id
					 FROM public.tbl_organisationseinheit
						JOIN lehre.tbl_lehrveranstaltung AS lehrfach ON tbl_organisationseinheit.oe_kurzbz = lehrfach.oe_kurzbz
						JOIN lehre.tbl_lehreinheit ON lehrfach.lehrveranstaltung_id = tbl_lehreinheit.lehrfach_id
						JOIN lehreinheiten ON tbl_lehreinheit.lehreinheit_id = lehreinheiten.lehreinheit_id
				)";
	}

	private function _getTagsCTE()
	{
		$this->load->config('lvverwaltung');
		$tags = $this->config->item('tags');

		$whereTags = '';
		if (is_array($tags) && !isEmptyArray($tags))
		{
			$tags = array_keys($tags);

			foreach ($tags as $key => $tag)
			{
				$tags[$key] = $this->db->escape($tag);
			}

			$whereTags = " AND tbl_notiz_typ.typ_kurzbz IN (" . implode(",", $tags) . ")";
		}

		return "tag_data_agg AS (
					SELECT
						lehreinheit_id,
						COALESCE(json_agg(tag ORDER BY done), '[]'::json) AS tags
					FROM (
							SELECT DISTINCT ON (public.tbl_notiz.notiz_id)
								tbl_notiz.notiz_id AS id,
								typ_kurzbz,
								array_to_json(tbl_notiz_typ.bezeichnung_mehrsprachig)->>0 AS beschreibung,
								text AS notiz,
								style,
								erledigt AS done,
								lehreinheit_id
							FROM public.tbl_notizzuordnung
								JOIN public.tbl_notiz ON tbl_notizzuordnung.notiz_id = tbl_notiz.notiz_id
								JOIN public.tbl_notiz_typ ON tbl_notiz.typ = tbl_notiz_typ.typ_kurzbz
							WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehreinheiten)"
								. $whereTags.
						") AS tag
					GROUP BY lehreinheit_id
				)";
	}

}
