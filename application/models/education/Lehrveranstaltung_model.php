<?php
class Lehrveranstaltung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehrveranstaltung';
		$this->pk = 'lehrveranstaltung_id';

		$this->load->model('organisation/studiengang_model', 'StudiengangModel');
		$this->load->model('organisation/studiensemester_model', 'StudiensemesterModel');
	}

	/**
	 * Get Lehrveranstaltungen by eventQuery string. Use with autocomplete event queries.
	 * @param $eventQuery String
	 * @param string $studiensemester_kurzbz Filter by Studiensemester
	 * @param array $oes Filter by Organisationseinheiten
	 * @return array
	 */
	public function getAutocompleteSuggestions($eventQuery, $studiensemester_kurzbz = null, $oes = null)
	{
		$subQry = $this->_getQryLvsByStudienplan($studiensemester_kurzbz, $oes);
		$params = [];

		/* filter by input string */
		if (is_string($eventQuery)) {
			$subQry.= ' AND lv.bezeichnung ILIKE ?';
			$params[] = '%' . $eventQuery . '%';
		}

		$qry = 'SELECT DISTINCT ON (lehrveranstaltung_id) * FROM ('. $subQry. ') AS tmp';

		return $this->execQuery($qry, $params);
	}

	/**
	 * Get Lehrveranstaltungen with its Stg, OE and OE-type.
	 * Filter by Studiensemester and Organisationseinheiten if necessary.
	 * @param $eventQuery String
	 * @param string $studiensemester_kurzbz Filter by Studiensemester
	 * @param array $oes Filter by Organisationseinheiten
	 * @param array $lv_ids Filter by Lehrveranstaltung-Ids
	 * @return array
	 */
	public function getLvsByStudienplan($studiensemester_kurzbz = null, $oes = null, $lv_ids = null)
	{
		$subQry = $this->_getQryLvsByStudienplan($studiensemester_kurzbz, $oes);
		$qry = 'SELECT * FROM ('. $subQry. ') AS tmp';

		if (isset($lv_ids) && is_array($lv_ids))
		{
			/* filter by lv_ids */
			$implodedLvIds = "'". implode("', '", $lv_ids). "'";
			$qry.= ' WHERE lehrveranstaltung_id IN ('. $implodedLvIds. ')';
		}

		$qry.= ' ORDER BY stg_typ_kurzbz, orgform_kurzbz DESC';

		return $this->execQuery($qry);
	}

	/**
	 * Get basic query to retrieve Lehrveranstaltungen according to the Orgforms and Ausbildungssemesters actual Studienplan.
	 *
	 * @return string
	 */
	private function _getQryLvsByStudienplan($studiensemester_kurzbz = null, $oes = null,  $lehrtyp_kurzbz = 'lv')
	{
		$qry = '
			SELECT
				lv.oe_kurzbz AS lv_oe_kurzbz,
				CASE
                    WHEN oe.organisationseinheittyp_kurzbz = \'Kompetenzfeld\' THEN (\'KF \' || oe.bezeichnung)
                    WHEN oe.organisationseinheittyp_kurzbz = \'Department\' THEN (\'DEP \' || oe.bezeichnung)
                    ELSE (oe.organisationseinheittyp_kurzbz || \' \' || oe.bezeichnung)
                END AS lv_oe_bezeichnung,
				stplsem.studiensemester_kurzbz,
				studienordnung_id,
				sto.studiengang_kz,
				stpl.studienplan_id,
				stplsem.semester,
				stpl.orgform_kurzbz,
				upper(stg.typ || stg.kurzbz) AS stg_typ_kurzbz,    
				stg.bezeichnung AS stg_bezeichnung,
				stgtyp.bezeichnung AS stg_typ_bezeichnung,
			    lv.lehrveranstaltung_id,
				lv.semester,   		
				lv.bezeichnung AS lv_bezeichnung,
				(
				    -- comma seperated string of all lehreinheitgruppen
					SELECT string_agg(bezeichnung, \', \') AS lehreinheitgruppe_bezeichnung
					FROM(
					    -- distinct bezeichnung, as may come multiple times from different lehreinheiten
						SELECT DISTINCT ON (studiengang_kz, bezeichnung) studiengang_kz, bezeichnung FROM
						(
							-- distinct lehreinheitgruppe, as may come multiple times from different lehrform
							SELECT DISTINCT ON (legr.lehreinheitgruppe_id) legr.studiengang_kz,
								-- get Spezialgruppe or Lehrverbandgruppe 
								COALESCE(
									legr.gruppe_kurzbz,
									CONCAT( UPPER(stg1.typ), UPPER(stg1.kurzbz), \'-\', legr.semester, legr.verband, legr.gruppe )
								) as bezeichnung
							FROM lehre.tbl_lehreinheitgruppe 	legr
							JOIN lehre.tbl_lehreinheit 			le USING (lehreinheit_id)
							JOIN lehre.tbl_lehrveranstaltung 	lv1 USING (lehrveranstaltung_id)
							JOIN public.tbl_studiengang 		stg1 ON stg1.studiengang_kz = legr.studiengang_kz
							WHERE lv1.lehrveranstaltung_id 		= lv.lehrveranstaltung_id
							AND le.studiensemester_kurzbz 		= stplsem.studiensemester_kurzbz
						) AS lehreinheitgruppen
					    GROUP BY studiengang_kz, bezeichnung
						ORDER BY studiengang_kz DESC
					) AS uniqueLehreinheitgruppen_bezeichnung
				) AS lehreinheitgruppen_bezeichnung
            FROM
				lehre.tbl_studienplan 							stpl
				JOIN lehre.tbl_studienordnung 					sto USING (studienordnung_id)
				JOIN lehre.tbl_studienplan_semester 			stplsem USING (studienplan_id)
				JOIN lehre.tbl_studienplan_lehrveranstaltung 	stpllv ON (stpllv.studienplan_id = stpl.studienplan_id AND stpllv.semester = stplsem.semester)
				JOIN lehre.tbl_lehrveranstaltung 				lv USING (lehrveranstaltung_id)
				JOIN public.tbl_organisationseinheit 			oe USING (oe_kurzbz)
				JOIN public.tbl_studiengang          			stg ON stg.studiengang_kz = sto.studiengang_kz
				JOIN public.tbl_studiengangstyp 				stgtyp ON stgtyp.typ = stg.typ
			/* filter by lehrtyp_kurzbz, default is lvs only */
			WHERE 
			      lehrtyp_kurzbz = '. $this->db->escape($lehrtyp_kurzbz);

		if (isset($studiensemester_kurzbz) && is_string($studiensemester_kurzbz))
		{
			/* filter by studiensemester */
			$qry.= ' AND stplsem.studiensemester_kurzbz = '. $this->db->escape($studiensemester_kurzbz);

		}

		if (isset($oes) && is_array($oes))
		{
			/* filter by organisationseinheit */
			$implodedOes = "'". implode("', '", $oes). "'";
			$qry.= ' AND lv.oe_kurzbz IN ('. $implodedOes. ')';
		}

		return $qry;
	}

	/**
	 * Get all Templates and union with all Lehrveranstaltungen of given Studiensemester and Oes, that are assigned to
	 * a template. This data structure can be used for nested tabulator data tree.
	 *
	 * @param null|string $studiensemester_kurzbz
	 * @param null|array $oes
	 * @return array|stdClass|null
	 */
	public function getTemplateLvTree($studiensemester_kurzbz = null, $oes = null){
		$params = [];
		$qry = '
		WITH 	
			-- All Lvs that are assigned to a template in given Studiensemester for given Oes
			-- joining via actual Studienplan
			standardisierteLvs AS (
				SELECT
					lv.*,
					stpl.studienplan_id::text as studienplan_id,
					stpl.bezeichnung AS studienplan_bezeichnung,
					stplsem.studiensemester_kurzbz
				FROM
					lehre.tbl_studienplan 							stpl
					JOIN lehre.tbl_studienordnung 					sto USING (studienordnung_id)
					JOIN lehre.tbl_studienplan_semester 			stplsem USING (studienplan_id)
					JOIN lehre.tbl_studienplan_lehrveranstaltung 	stpllv ON (stpllv.studienplan_id = stpl.studienplan_id AND stpllv.semester = stplsem.semester)
					JOIN lehre.tbl_lehrveranstaltung 				lv USING (lehrveranstaltung_id)
					JOIN public.tbl_organisationseinheit 			oe USING (oe_kurzbz)
					JOIN public.tbl_studiengang          			stg ON stg.studiengang_kz = sto.studiengang_kz
					JOIN public.tbl_studiengangstyp 				stgtyp ON stgtyp.typ = stg.typ
				WHERE 
					-- filter type lv
				  	lehrtyp_kurzbz = \'lv\'
				  	-- filter lvs assigned to template (= standardisierte lv)
					AND lehrveranstaltung_template_id IS NOT NULL';

					if (is_string($studiensemester_kurzbz))
					{
						/* filter by studiensemester */
						$params[]= $studiensemester_kurzbz;
						$qry.= ' AND stplsem.studiensemester_kurzbz = ? ';

					}

					if (is_array($oes))
					{
						/* filter by organisationseinheit */
						$params[]= $oes;
						$qry.= ' AND lv.oe_kurzbz IN ? ';
					}
		$qry.= '
			),
			-- All templates
			templateLvs AS (
				SELECT 
					lv.*,
					NULL AS studienplan_id,
					(
						SELECT string_agg(stpl_bezeichnung, \', \')
						FROM
						(
							SELECT stlv.studienplan_bezeichnung AS stpl_bezeichnung
							FROM standardisierteLvs stlv
							WHERE stlv.lehrveranstaltung_template_id = lv.lehrveranstaltung_id
						) AS studienplaene
					) AS studienplan_bezeichnung,
					NULL AS studiensemester_kurzbz
				FROM 
					lehre.tbl_lehrveranstaltung lv
				WHERE 
					-- filter type template
					lehrtyp_kurzbz = \'tpl\'
					-- filter semester that were retrieved by standardisierte lvs semester for selected studiensemester
					AND EXISTS (
						SELECT 1
						FROM standardisierteLvs std
						WHERE std.lehrveranstaltung_template_id = lv.lehrveranstaltung_id
					)';

		if (is_array($oes))
		{
			/* filter by organisationseinheit */
			$params[]= $oes;
			$qry.= ' AND lv.oe_kurzbz IN ? ';
		}
		$qry.= '
			)	
		';

		$qry.= '
			SELECT 
				lv.lehrveranstaltung_id,
				lv.kurzbz,
			    lv.lehrtyp_kurzbz,
				lv.bezeichnung AS lv_bezeichnung, 
				lv.bezeichnung_english,
				lv.studiengang_kz,
				lv.semester,
				lv.oe_kurzbz,
				lv.ects,
				lv.lehrform_kurzbz,
				lv.orgform_kurzbz,
				lv.sprache, 
				lv.aktiv,
				lv.lehrveranstaltung_template_id,
				lv.studienplan_id,
			    lv.studienplan_bezeichnung,
			    lv.studiensemester_kurzbz,
			    upper(stg.typ || stg.kurzbz) AS "stg_typ_kurzbz",    
				stg.bezeichnung AS "stg_bezeichnung",
				stgtyp.bezeichnung AS "stg_typ_bezeichnung",
				CASE
   			 		WHEN oe.organisationseinheittyp_kurzbz = \'Kompetenzfeld\' THEN (\'KF \' || oe.bezeichnung)
                    WHEN oe.organisationseinheittyp_kurzbz = \'Department\' THEN (\'DEP \' || oe.bezeichnung)
                    ELSE (oe.organisationseinheittyp_kurzbz || \' \' || oe.bezeichnung)
                END AS "lv_oe_bezeichnung",
			   (
				    -- comma seperated string of all lehreinheitgruppen
					SELECT string_agg(bezeichnung, \', \') AS lehreinheitgruppe_bezeichnung
					FROM(
					    -- distinct bezeichnung, as may come multiple times from different lehreinheiten
						SELECT DISTINCT ON (studiengang_kz, bezeichnung) studiengang_kz, bezeichnung FROM
						(
							-- distinct lehreinheitgruppe, as may come multiple times from different lehrform
							SELECT DISTINCT ON (legr.lehreinheitgruppe_id) legr.studiengang_kz,
								-- get Spezialgruppe or Lehrverbandgruppe 
								COALESCE(
									legr.gruppe_kurzbz,
									CONCAT( UPPER(stg1.typ), UPPER(stg1.kurzbz), \'-\', legr.semester, legr.verband, legr.gruppe )
								) as bezeichnung
							FROM lehre.tbl_lehreinheitgruppe 	legr
							JOIN lehre.tbl_lehreinheit 			le USING (lehreinheit_id)
							JOIN lehre.tbl_lehrveranstaltung 	lv1 USING (lehrveranstaltung_id)
							JOIN public.tbl_studiengang 		stg1 ON stg1.studiengang_kz = legr.studiengang_kz
							WHERE lv1.lehrveranstaltung_id 		= lv.lehrveranstaltung_id
							AND le.studiensemester_kurzbz 		= lv.studiensemester_kurzbz
						) AS lehreinheitgruppen
					    GROUP BY studiengang_kz, bezeichnung
						ORDER BY studiengang_kz DESC
					) AS uniqueLehreinheitgruppen_bezeichnung
				) AS lehreinheitgruppen_bezeichnung
			FROM (
				SELECT 
					* 
				FROM 
				     standardisierteLvs 
				UNION
				SELECT 
				   * 
				FROM templateLvs 
			) AS lv
				JOIN public.tbl_studiengang          	stg ON stg.studiengang_kz = lv.studiengang_kz
				JOIN public.tbl_studiengangstyp 		stgtyp ON stgtyp.typ = stg.typ
				JOIN public.tbl_organisationseinheit 	oe ON oe.oe_kurzbz = lv.oe_kurzbz
		ORDER BY
			oe.bezeichnung, lv.semester, lv.bezeichnung
		';

		return $this->execQuery($qry, $params);
	}

	/**
	 * Gets unique Groupstrings for Lehrveranstaltungen, e.g. WS2018_BIF_1_PRJM_VZ_LV12345
	 * @param string $studiensemester_kurzbz
	 * @param string $ausbildungssemester
	 * @param string $studiengang_kz
	 * @param string|array $lehrveranstaltung_ids
	 * @return array|null
	 */
	public function getLehrveranstaltungGroupNames($studiensemester_kurzbz, $ausbildungssemester = null, $studiengang_kz = null, $lehrveranstaltung_ids = null)
	{
		$studiengang_kz_arr = array();
		$ausbildungssemester_arr = array();
		$lehrveranstaltung_id_arr = array();

		if (is_numeric($studiengang_kz))
		{
			$studiengang_kz_arr[] = $studiengang_kz;
		}
		elseif (is_array($studiengang_kz))
		{
			$studiengang_kz_arr = $studiengang_kz;
		}
		else
		{
			$studiengangdata = $this->StudiengangModel->getStudiengaengeByStudiensemester($studiensemester_kurzbz);

			if (!hasData($studiengangdata))
				show_error('no studiengaenge retrieved');

			foreach ($studiengangdata->retval as $studiengang)
			{
				$studiengang_kz_arr[] = $studiengang->studiengang_kz;
			}
		}

		if (is_numeric($ausbildungssemester))
		{
			$ausbildungssemester_arr[] = $ausbildungssemester;
		}
		elseif (is_array($ausbildungssemester))
		{
			$ausbildungssemester_arr = $ausbildungssemester;
		}
		else
		{
			foreach ($studiengang_kz_arr as $studiengang_kz_item)
			{
				$result = $this->StudiensemesterModel->getAusbildungssemesterByStudiensemesterAndStudiengang($studiensemester_kurzbz, $studiengang_kz_item);

				if (isError($result))
					return error(getError($result));

				foreach ($result->retval as $semester)
				{
					if (!in_array($semester->semester, $ausbildungssemester_arr))
						$ausbildungssemester_arr[] = $semester->semester;
				}
			}
		}

		if (is_numeric($lehrveranstaltung_ids))
		{
			$lehrveranstaltung_id_arr[] = $lehrveranstaltung_ids;
		}
		elseif (is_array($lehrveranstaltung_ids))
		{
			$lehrveranstaltung_id_arr = $lehrveranstaltung_ids;
		}

		$parametersarray = array($studiensemester_kurzbz, $studiensemester_kurzbz);

		$query = "

			SELECT lehrveranstaltung_id, ? || '_' || kuerzel || '_' || replace(lvpostfix, ' ', '-') AS lvgroupname
				FROM(
						SELECT DISTINCT ON (kuerzel, lvpostfix)
						  lehrveranstaltung_id,
						  UPPER(tbl_studiengang.typ :: VARCHAR(1) || tbl_studiengang.kurzbz)      AS kuerzel,
						  tbl_lehrveranstaltung.semester || '_' || tbl_lehrveranstaltung.kurzbz || '_' || COALESCE(tbl_lehrveranstaltung.orgform_kurzbz, tbl_studiengang.orgform_kurzbz) || '_LV' || lehrveranstaltung_id AS lvpostfix
						FROM lehre.tbl_lehrveranstaltung
						  JOIN public.tbl_studiengang ON tbl_lehrveranstaltung.studiengang_kz = tbl_studiengang.studiengang_kz
						WHERE tbl_lehrveranstaltung.lehrtyp_kurzbz != 'modul'
						AND EXISTS (SELECT 1 FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND studiensemester_kurzbz = ?)";

		if (count($ausbildungssemester_arr) > 0)
			$query .= " AND tbl_lehrveranstaltung.semester IN (". implode(", ", $ausbildungssemester_arr).")";

		if (count($studiengang_kz_arr) > 0)
			$query .= " AND tbl_lehrveranstaltung.studiengang_kz IN (". implode(", ", $studiengang_kz_arr).")";

		if (count($lehrveranstaltung_id_arr) > 0)
		{
			$query .= " AND tbl_lehrveranstaltung.lehrveranstaltung_id IN (". implode(', ', $lehrveranstaltung_id_arr).")";
		}

		$query .= ") lvgroups ORDER BY lvgroupname";

		return $this->execQuery($query, $parametersarray);
	}

	/**
	 * Gets all students of a Lehrveranstaltung
	 * @param $studiensemester_kurzbz
	 * @param $lehrveranstaltung_id
	 * @param $active optional, if true, only active students retrieved, false - only inactive, all students otherwise
	 * @return array|null
	 */
	public function getStudentsByLv($studiensemester_kurzbz, $lehrveranstaltung_id, $active = null)
	{
		$query = "SELECT
			distinct on(nachname, vorname, person_id) vorname, nachname, matrikelnr,
			tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
			(SELECT status_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_student.prestudent_id ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status,
			tbl_bisio.bisio_id, tbl_bisio.von, tbl_bisio.bis, tbl_student.studiengang_kz AS stg_kz_student,
			tbl_zeugnisnote.note, tbl_mitarbeiter.mitarbeiter_uid, tbl_person.matr_nr, tbl_benutzer.uid,
			UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel, tbl_studiengang.orgform_kurzbz, vw_student_lehrveranstaltung.semester, vw_student_lehrveranstaltung.studiensemester_kurzbz, vw_student_lehrveranstaltung.bezeichnung

		FROM
			campus.vw_student_lehrveranstaltung
			JOIN public.tbl_benutzer USING(uid)
			JOIN public.tbl_person USING(person_id)
			LEFT JOIN public.tbl_student ON(uid=student_uid)
			LEFT JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
			LEFT JOIN public.tbl_studentlehrverband USING(student_uid,studiensemester_kurzbz)
			LEFT JOIN lehre.tbl_zeugnisnote on(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id AND tbl_zeugnisnote.student_uid=tbl_student.student_uid AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
			LEFT JOIN bis.tbl_bisio ON(uid=tbl_bisio.student_uid)
			LEFT JOIN public.tbl_studiengang ON(vw_student_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz)
		WHERE
			vw_student_lehrveranstaltung.studiensemester_kurzbz=?
		AND
			vw_student_lehrveranstaltung.lehrveranstaltung_id=?";

		if (isset($active))
		{
			if ($active === true)
				$query .= " AND tbl_benutzer.aktiv";
			elseif ($active === false)
				$query .= " AND tbl_benutzer.aktiv = false";
		}

		$query .=
		" ORDER BY nachname, vorname, person_id, tbl_bisio.bis DESC";

		return $this->execQuery($query, array($studiensemester_kurzbz, $lehrveranstaltung_id));
	}

	/**
	 * Gets all lecturers of a Lehrveranstaltung
	 * @param $studiensemester_kurzbz
	 * @param $lehrveranstaltung_id
	 * @return array|null
	 */
	public function getLecturersByLv($studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$query = "SELECT
					*
				FROM
					(SELECT distinct on(uid) vorname, nachname, tbl_benutzer.uid as uid,
						CASE WHEN
						EXISTS(
							SELECT
								1
							FROM
								lehre.tbl_lehreinheitmitarbeiter lvllem
								JOIN lehre.tbl_lehreinheit lvlle USING(lehreinheit_id)
							WHERE
						 		lehrfunktion_kurzbz='LV-Leitung'
								AND lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id
								AND studiensemester_kurzbz=tbl_lehreinheit.studiensemester_kurzbz
								AND mitarbeiter_uid=tbl_benutzer.uid
						) THEN true ELSE false END as lvleiter
		    		FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_benutzer, public.tbl_person
		    		WHERE
		    			tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
						tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND
						tbl_person.person_id=tbl_benutzer.person_id AND
						lehrveranstaltung_id=? AND
						tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT like '_Dummy%' AND
						tbl_benutzer.aktiv=true AND tbl_person.aktiv=true AND
						studiensemester_kurzbz=?
					) AS a
				ORDER BY lvleiter DESC, nachname, vorname";

		return $this->execQuery($query, array($lehrveranstaltung_id, $studiensemester_kurzbz));
	}
	/**
	 * Gets all Leiter of Lehrveranstaltungsorganisationseinheit
	 * @param $lehrveranstaltung_id
	 * @return array|null
	 */
	public function getLeitungOfLvOe($lehrveranstaltung_id)
	{
		$query = "select distinct vorname, nachname, uid
				FROM
				lehre.tbl_lehrveranstaltung lv
				JOIN public.tbl_organisationseinheit og using (oe_kurzbz)
				JOIN public.tbl_benutzerfunktion bf using (oe_kurzbz)	
				join public.tbl_benutzer b using (uid)
				join public.tbl_person p using (person_id)
				where
				bf.datum_von <= now()::date
				and (bf.datum_bis >= now()::date or bf.datum_bis is null)
				and bf.funktion_kurzbz = 'Leitung' -- Leitung of LV-OE
				and lehrveranstaltung_id = ?";

		return $this->execQuery($query, array($lehrveranstaltung_id));
	}

	/**
	 * Gets Lehrveranstaltungen of a student
	 * @param $student_uid
	 * @param null $studiensemester_kurzbz
	 * @return array|null
	 */
	public function getLvsByStudent($student_uid, $studiensemester_kurzbz = null)
	{
		$params = array($student_uid);

		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung
				WHERE lehrveranstaltung_id IN(SELECT lehrveranstaltung_id FROM campus.vw_student_lehrveranstaltung
											  WHERE uid=?";
		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND studiensemester_kurzbz=?";
			$params[] = $studiensemester_kurzbz;
		}
		$qry .= ") OR lehrveranstaltung_id IN(SELECT lehrveranstaltung_id FROM lehre.tbl_zeugnisnote WHERE student_uid=?";
		$params[] = $student_uid;
		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND studiensemester_kurzbz=?";
			$params[] = $studiensemester_kurzbz;
		}
		$qry .= ") ORDER BY semester, bezeichnung";

		return $this->execQuery($qry, $params);
	}

	/**
	 * Gets Lehrveranstaltungen of a student with grades if available
	 * 
	 * @param string		$student_uid
	 * @param string		$studiensemester_kurzbz
	 * @param string|null	$sprache
	 * @param number|null	$lvid - returns only information about that single lv if the parameter is set
	 * 
	 * @return stdClass
	 */
	public function getLvsByStudentWithGrades($student_uid, $studiensemester_kurzbz, $sprache = null, $lvid=null)
	{
		if ($sprache) {
			$sprache_qry = $this->db->compile_binds('SELECT index FROM public.tbl_sprache WHERE sprache = ?', [$sprache]);
			$bezeichnung = 'bezeichnung_mehrsprachig[(' . $sprache_qry . ')]';
			$sgbezeichnung = $sprache == 'English' ? 'COALESCE(sg.english, sg.bezeichnung)' : 'sg.bezeichnung';
			$lvbezeichnung = $sprache == 'English' ? 'COALESCE(v.bezeichnung_english, v.bezeichnung)' : 'v.bezeichnung';
		} else {
			$bezeichnung = 'bezeichnung';
			$sgbezeichnung = 'sg.bezeichnung';
			$lvbezeichnung = 'v.bezeichnung';
		}

		$this->addDistinct();
		// TODO(chris): selects
		/*
		semester (?)
		module
		bezeichnung
		sg_bezeichnung
		studiengang_kuerzel
		lvnote
		znote
		studiengang_kz
		lehrveranstaltung_id
		benotung
		lvinfo
		farbe

		sprache (?)
		ects (?)
		incoming (?)
		orgform_kurzbz (?)
		*/
		// TODO(chris): module or kf
		#$this->addSelect($this->dbTable . '.*');
		#$this->addSelect('v.*');
		$this->addSelect($this->dbTable . '.benotung');
		$this->addSelect($this->dbTable . '.lvinfo');
		$this->addSelect($this->dbTable . '.farbe');
		$this->addSelect($this->dbTable . '.incoming');
		$this->addSelect($this->dbTable . '.orgform_kurzbz');
		$this->addSelect('v.studiengang_kz');
		$this->addSelect('v.lehrveranstaltung_id');
		$this->addSelect('v.semester');
		
		$this->addSelect('v.sprache');
		$this->addSelect('v.ects');
		$this->addSelect('znn.positiv');

		#$this->addSelect('splv.module');
		$this->addSelect($lvbezeichnung . ' AS bezeichnung');
		$this->addSelect($sgbezeichnung . ' AS sg_bezeichnung');
		$this->addSelect('UPPER(sg.typ::VARCHAR(1) || sg.kurzbz) AS studiengang_kuerzel');

		$this->addSelect('COALESCE(gnn.' . $bezeichnung . ', gnn.bezeichnung, gn.note::text) AS lvnote');
		$this->addSelect('COALESCE(znn.' . $bezeichnung . ', znn.bezeichnung, zn.note::text) AS znote');

		// TODO(chris): Potentielle Anpassung "Eine UID"
		$this->addJoin('campus.vw_student_lehrveranstaltung v', 'lehrveranstaltung_id');
		$this->addJoin('public.tbl_studiengang sg', $this->dbTable . '.studiengang_kz = sg.studiengang_kz');
		$this->db->where("v.lehreverzeichnis<>''");
		if(isset($lvid))
		{
			$this->db->where("v.lehrveranstaltung_id", $lvid);	
		}

		$this->addJoin('campus.tbl_lvgesamtnote gn', 'gn.lehrveranstaltung_id=v.lehrveranstaltung_id AND gn.student_uid=v.uid AND gn.studiensemester_kurzbz=v.studiensemester_kurzbz', 'LEFT');
		$this->addJoin('lehre.tbl_note gnn', 'gn.note=gnn.note', 'LEFT');

		$this->addJoin('lehre.tbl_zeugnisnote zn', 'zn.lehrveranstaltung_id=v.lehrveranstaltung_id AND zn.student_uid=v.uid AND zn.studiensemester_kurzbz=v.studiensemester_kurzbz', 'LEFT');
		$this->addJoin('lehre.tbl_note znn', 'zn.note=znn.note', 'LEFT');

		$this->addOrder('bezeichnung');

		/*if (!defined("CIS_PROFIL_STUDIENPLAN_MODULE_AUSBLENDEN") || !CIS_PROFIL_STUDIENPLAN_MODULE_AUSBLENDEN) {
			$modulebezeichnung = str_replace('v.', 'm.', $lvbezeichnung);
			$modulesql = '
				LEFT JOIN lehre.tbl_studienplan_lehrveranstaltung p ON(lv.studienplan_lehrveranstaltung_id_parent=p.studienplan_lehrveranstaltung_id)
				LEFT JOIN lehre.tbl_lehrveranstaltung m ON(m.lehrveranstaltung_id = p.lehrveranstaltung_id)';
		} else {
			$modulebezeichnung = 'NULL';
			$modulesql = '';
		}

		$this->addJoin('(
			SELECT lv.lehrveranstaltung_id, sps.studiensemester_kurzbz, so.studiengang_kz, lv.semester, ' . $modulebezeichnung . ' AS module
			FROM lehre.tbl_studienplan_lehrveranstaltung lv
			LEFT JOIN lehre.tbl_studienplan sp ON(sp.studienplan_id=lv.studienplan_id)
			JOIN lehre.tbl_studienplan_semester sps ON(sp.studienplan_id=sps.studienplan_id AND sps.semester=lv.semester)
			JOIN lehre.tbl_studienordnung so ON(so.studienordnung_id=sp.studienordnung_id)
			' . $modulesql . '
		) splv', 'splv.lehrveranstaltung_id=v.lehrveranstaltung_id AND splv.studiensemester_kurzbz=v.studiensemester_kurzbz AND splv.studiengang_kz=v.studiengang_kz', 'LEFT');*/

		return $this->loadWhere(['v.uid' => $student_uid, 'v.lehre' => true, 'v.studiensemester_kurzbz' => $studiensemester_kurzbz]);
	}

	/**
	 * Gets valid Lehrveranstaltungen with incoming places for a Studiensemester.
	 * Only
	 * 1. Lvs with incoming places > 0
	 * 2. Studienplan valid in current semester or with Lehrauftrag (i.e. assigned Lehreinheit)
	 * @param $studiensemester_kurzbz
	 * @return object
	 */
	public function getLvsWithIncomingPlaces($studiensemester_kurzbz)
	{
		$studsemres = $this->StudiensemesterModel->load($studiensemester_kurzbz);

		if (!hasData($studsemres))
			return success(array());

		$parametersarray = array($studiensemester_kurzbz, $studsemres->retval[0]->studienjahr_kurzbz, $studiensemester_kurzbz, $studiensemester_kurzbz);

		$query = "
			SELECT * FROM (
				SELECT DISTINCT ON (lv.lehrveranstaltung_id) lv.lehrveranstaltung_id, lv.bezeichnung AS lv_bezeichnung, lv.kurzbz AS lv_kurzbz, lv.sprache, lv.ects, lv.lehre,
				lv.lehreverzeichnis, lv.sws, lv.lvs, lv.alvs, lv.lvps, lv.las, lv.incoming, lv.lehrform_kurzbz, lv.orgform_kurzbz AS lv_orgform, lv.semester,
				? AS studiensemester_kurzbz, ? AS studienjahr_kurzbz, UPPER(stg.typ::VARCHAR(1) || stg.kurzbz) AS studiengang_kuerzel,
				stg.bezeichnung AS studiengang_bezeichnung, stg.english AS studiengang_bezeichnung_english, stg.typ, stg.orgform_kurzbz AS studiengang_orgform,
				tbl_sprache.locale, CASE WHEN lv.orgform_kurzbz NOTNULL THEN lv.orgform_kurzbz ELSE stg.orgform_kurzbz END AS orgform_kurzbz
				FROM lehre.tbl_lehrveranstaltung lv
				JOIN public.tbl_studiengang stg ON lv.studiengang_kz = stg.studiengang_kz
				JOIN public.tbl_sprache ON lv.sprache = tbl_sprache.sprache
				WHERE lv.lehrtyp_kurzbz != 'modul'
						  AND (
							EXISTS
							(
								WITH gueltige_studienplaene AS (
									SELECT studienplan_id, semester
									FROM lehre.tbl_studienplan
									JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
									WHERE tbl_studienplan.aktiv
									AND lehre.tbl_studienplan_semester.studiensemester_kurzbz = ?
								)
								SELECT 1
								FROM lehre.tbl_studienplan_lehrveranstaltung
								WHERE tbl_studienplan_lehrveranstaltung.studienplan_id IN (SELECT studienplan_id FROM gueltige_studienplaene)
									  AND tbl_studienplan_lehrveranstaltung.semester IN
										  (SELECT semester FROM gueltige_studienplaene
											 WHERE gueltige_studienplaene.studienplan_id = tbl_studienplan_lehrveranstaltung.studienplan_id)
									  AND tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id = lv.lehrveranstaltung_id
									  AND tbl_studienplan_lehrveranstaltung.export
							)
							OR EXISTS (SELECT 1 FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id = lv.lehrveranstaltung_id AND studiensemester_kurzbz = ?)
						  )
				AND lv.incoming > 0
				AND lv.aktiv
				AND (stg.typ IN ('b', 'm') OR stg.studiengang_kz = 10006)-- ECI Studiengang Campus International
			) lvs
			ORDER BY studiengang_kuerzel, orgform_kurzbz, lv_bezeichnung, lehrform_kurzbz, lehrveranstaltung_id;
		";

		return $this->execQuery($query, $parametersarray);
	}

	/**
	 * Gets Lehrveranstaltung and its Lehreinheiten (multiple rows possible).
	 * Returns empty array if student has no Lehrveranstaltung.
	 * @param $uid
	 * @param $studiensemester_kurzbz
	 * @param $lehrveranstaltung_id
	 * @return array|null
	 */
	public function getLvByStudent($uid, $studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$query = '
			SELECT * FROM campus.vw_student_lehrveranstaltung
			WHERE uid = ?
			AND studiensemester_kurzbz = ?
			AND lehrveranstaltung_id = ?;
		';

		return $this->execQuery($query, array($uid, $studiensemester_kurzbz, $lehrveranstaltung_id));
	}

	/**
	 * Sucht nach LV Templates und gibt Id und Label ("bezeichnung [kurzbz]") aus
	 * Diese funktion ist für autocomplete gedacht
	 *
	 * @param string $filter Suchfilter
	 * @return \stdClass A return object
	 */
	public function loadTemplates($filter)
	{
		$filter = strtolower($filter);
		$qry = "SELECT
					tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.kurzbz
				FROM
					lehre.tbl_lehrveranstaltung
				WHERE
					tbl_lehrveranstaltung.lehrtyp_kurzbz = 'tpl' AND (
						CAST(tbl_lehrveranstaltung.lehrveranstaltung_id AS TEXT) LIKE '%".$this->db_escape($filter)."%' OR 
						LOWER(tbl_lehrveranstaltung.bezeichnung) LIKE '%".$this->db_escape($filter).	"%' OR 
						LOWER(tbl_lehrveranstaltung.kurzbz) LIKE '%".$this->db_escape($filter).	"%'
					)
			";
		return $this->execQuery($qry);
	}

	/**
	 * Lädt Template und gibt Id und Label ("bezeichnung [kurzbz]") zurück
	 * Diese funktion ist für autocomplete gedacht
	 *
	 * @param string $name
	 * @return \stdClass A return object
	 */
	public function loadTemplateByName($name)
	{
		$qry = "SELECT
					tbl_lehrveranstaltung.lehrveranstaltung_id as id, CONCAT(tbl_lehrveranstaltung.bezeichnung, ' [', tbl_lehrveranstaltung.kurzbz, ']') as label
				FROM
					lehre.tbl_lehrveranstaltung
				WHERE
					tbl_lehrveranstaltung.lehrtyp_kurzbz = 'tpl' AND (
						CAST(tbl_lehrveranstaltung.lehrveranstaltung_id AS TEXT) = '".($name ? $this->db_escape($name) : 0)."' OR tbl_lehrveranstaltung.bezeichnung = '".$this->db_escape($name).	"' OR tbl_lehrveranstaltung.kurzbz = '".$this->db_escape($name).	"'
					)
			";
		return $this->execQuery($qry);
	}


    /**
     * Get ECTS Summe pro angerechnetes Quereinstiegssemester.
     *
     * @param $studiengang_kz
     * @param $studiensemester_kurzbz
     * @param $ausbildungssemester
     * @param $orgform_kurzbz
     * @return array|stdClass|null
     */
    public function getSumQuereinstiegsECTSProSemester($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester, $orgform_kurzbz)
    {
        $qry = '
            SELECT
                sum(tbl_lehrveranstaltung.ects) as "sum_ects"
            FROM
                lehre.tbl_studienplan
                JOIN lehre.tbl_studienplan_lehrveranstaltung USING (studienplan_id)
                JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
            WHERE
                tbl_studienplan.studienplan_id = (
                    SELECT 
                        studienplan_id
                    FROM 
                        lehre.tbl_studienordnung 
                        JOIN lehre.tbl_studienplan USING (studienordnung_id) 
                        JOIN lehre.tbl_studienplan_semester USING (studienplan_id)
                    WHERE tbl_studienordnung.studiengang_kz = ?
                        AND tbl_studienplan_semester.semester = ?
                        AND tbl_studienplan_semester.studiensemester_kurzbz = ?
                        AND tbl_studienplan.orgform_kurzbz = ?
                
                    LIMIT 1
                )
            AND tbl_studienplan_lehrveranstaltung.semester = ?
            AND studienplan_lehrveranstaltung_id_parent IS NULL -- auf Modulebene
            AND tbl_studienplan_lehrveranstaltung.export = TRUE
        ';

        return $this->execQuery($qry, array(
            $studiengang_kz, $ausbildungssemester, $studiensemester_kurzbz, $orgform_kurzbz, $ausbildungssemester)
        );
    }

    /**
     * Get ECTS Summe aller bisher angerechneten LVs.
     * Wenn keine explizite Begruendung angegeben ist, wird eine schulische Begruendung angenommen.
     *
     * @param $student_uid
     * @return array|stdClass|null
     */
    public function getSumAngerechneteECTSByBegruendung($student_uid)
    {
        $qry = '
            SELECT sum(ects), begruendung_id FROM (
                SELECT 
                    lehrveranstaltung_id, studiensemester_kurzbz, ects, COALESCE(begruendung_id, 1) as begruendung_id -- fallback auf externes Zeugnis
            FROM 
                lehre.tbl_zeugnisnote 
                LEFT JOIN lehre.tbl_anrechnung USING(lehrveranstaltung_id, studiensemester_kurzbz) 
                JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
                JOIN public.tbl_student USING(student_uid)
            WHERE 
                tbl_zeugnisnote.note = 6
                AND student_uid = ?
                AND (lehre.tbl_anrechnung.prestudent_id = tbl_student.prestudent_id 
                         OR lehre.tbl_anrechnung.prestudent_id is null)
                
            UNION 
                
            SELECT 
                lehrveranstaltung_id, studiensemester_kurzbz, ects, begruendung_id -- fallback auf externes Zeugnis
            FROM 
                lehre.tbl_anrechnung 
                JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
                JOIN public.tbl_student USING(prestudent_id)
            WHERE 
                genehmigt_von is not null
                AND student_uid = ? 
            ) lvsangerechnet 
            GROUP BY begruendung_id
        ';

        return $this->execQuery($qry, array($student_uid, $student_uid));
    }

    /**
     * Get ECTS Summe aller bisher schulisch begruendeten angerechneten LVs.
     * Derzeit wird auch jede Anrechnung, die nicht beruflich ist, als schulisch angenommen.
     *
     * @param $student_uid
     * @return array|stdClass|null
     */
    public function getEctsSumSchulisch($student_uid, $prestudent_id, $studiengang_kz)
    {
        $qry = '
            SELECT get_ects_summe_schulisch(?, ?, ?) AS ectsSumSchulisch
        ';

        return $this->execQuery($qry, array($student_uid, $prestudent_id, $studiengang_kz));
    }

    /**
     * Get ECTS Summe aller bisher beruflich angerechneten LVs.
     *
     * @param $student_uid
     * @return array|stdClass|null
     */
    public function getEctsSumBeruflich($student_uid)
    {
        $qry = '
            SELECT get_ects_summe_beruflich(?) AS ectsSumBeruflich
        ';

        return $this->execQuery($qry, array($student_uid));
    }

    /**
     * @param integer		$lehrveranstaltung_id
     * @param string		$studiensemester_kurzbz
     * 
     * @return stdClass
     */
    public function getKoordinator($lehrveranstaltung_id, $studiensemester_kurzbz = null)
    {
		$binds = [
			$lehrveranstaltung_id,
			$lehrveranstaltung_id,
			$lehrveranstaltung_id,
			$lehrveranstaltung_id
		];
    	$qry = "
			SELECT 
				a.uid, vorname, nachname, titelpre, titelpost
			FROM (
				SELECT
					koordinator as uid
				FROM
					lehre.tbl_lehrveranstaltung
				WHERE
					lehrveranstaltung_id = ?
				UNION
				SELECT
					uid
				FROM
					lehre.tbl_lehreinheit
					JOIN lehre.tbl_lehrveranstaltung AS lehrfach ON(tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id)
					JOIN public.tbl_fachbereich ON(lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz)
					JOIN public.tbl_benutzerfunktion ON(tbl_fachbereich.fachbereich_kurzbz=tbl_benutzerfunktion.fachbereich_kurzbz)
				WHERE
					tbl_benutzerfunktion.funktion_kurzbz='fbk'
				AND (tbl_benutzerfunktion.datum_von IS null OR tbl_benutzerfunktion.datum_von <= now())
				AND (tbl_benutzerfunktion.datum_bis IS null OR tbl_benutzerfunktion.datum_bis >= now())
				AND tbl_lehreinheit.lehrveranstaltung_id = ?
				AND tbl_benutzerfunktion.oe_kurzbz = (
					SELECT
						tbl_studiengang.oe_kurzbz
					FROM
						lehre.tbl_lehrveranstaltung
						JOIN public.tbl_studiengang USING(studiengang_kz)
					WHERE lehrveranstaltung_id = ?
				)
				AND EXISTS (
					SELECT
						lehrveranstaltung_id
					FROM
						lehre.tbl_lehrveranstaltung
					WHERE
						lehrveranstaltung_id = ?
						AND koordinator IS null
				)
		";

		if ($studiensemester_kurzbz !== null)
		{
			$qry .= " AND tbl_lehreinheit.studiensemester_kurzbz = ?";
			$binds[] = $studiensemester_kurzbz;
		}
		
		$qry .= "
			) AS a
			JOIN campus.vw_mitarbeiter ON(a.uid=vw_mitarbeiter.uid)
			WHERE vw_mitarbeiter.aktiv
		";

		return $this->execQuery($qry, $binds);
	}

    public function getStg($lehrveranstaltung_id)
    {
    	$this->addSelect('stg.*');
    	$this->addJoin('public.tbl_studiengang stg', 'studiengang_kz');
    	return $this->load($lehrveranstaltung_id);
    }
    
    //Berechtigungen auf Fachbereichsebene
    public function getBerechtigungenAufFachberechsebene($lvid, $angezeigtes_stsem)
    {
	    $query = "
		SELECT
		    DISTINCT lehrfach.oe_kurzbz
		FROM
		    lehre.tbl_lehrveranstaltung
		JOIN 
		    lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
		JOIN 
		    lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
		WHERE 
		    tbl_lehrveranstaltung.lehrveranstaltung_id = " . $this->escape(intval($lvid));

	    if(isset($angezeigtes_stsem) && $angezeigtes_stsem != ''){
		$query .= " AND studiensemester_kurzbz = " . $this->escape($angezeigtes_stsem);
	    }

	    $res = $this->execReadOnlyQuery($query);
	    return $res;
    }
    
    public function getStudentEMail($lvid, $angezeigtes_stsem)
    {
		$query = "
			SELECT
				DISTINCT vw_lehreinheit.stg_kurzbz, vw_lehreinheit.stg_typ, 
				vw_lehreinheit.semester, COALESCE(vw_lehreinheit.verband,'') as verband, 
				COALESCE(vw_lehreinheit.gruppe,'') as gruppe, 
				vw_lehreinheit.gruppe_kurzbz, tbl_gruppe.mailgrp
			FROM
				campus.vw_lehreinheit
			LEFT JOIN 
				public.tbl_gruppe USING(gruppe_kurzbz)
			WHERE
				lehrveranstaltung_id = " . $this->escape(intval($lvid)) . "
				AND studiensemester_kurzbz = " . $this->escape($angezeigtes_stsem);

		$res = $this->execReadOnlyQuery($query);
		return $res;
    }
}
