<?php

class Studiengang_model extends DB_Model
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studiengang';
		$this->pk = 'studiengang_kz';
	}

	/**
	 *
	 */
	public function getAllForBewerbung()
	{
		// Checks if the operation is permitted by the API caller
		if (($isEntitled = $this->isEntitled('lehre.vw_studienplan', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('bis.tbl_lgartcode', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		
		$allForBewerbungQuery = 'SELECT DISTINCT studiengang_kz,
										typ,
										organisationseinheittyp_kurzbz,
										studiengangbezeichnung,
										standort,
										studiengangbezeichnung_englisch,
										lgartcode,
										tbl_lgartcode.bezeichnung
								   FROM (SELECT tbl_organisationseinheit.organisationseinheittyp_kurzbz,
												tbl_studiengang.oe_kurzbz,
												tbl_studienordnung.studiengang_kz,
												tbl_studienplan.studienordnung_id,
												tbl_studienplan.studienplan_id,
												tbl_studienplan.orgform_kurzbz,
												tbl_studienplan.version,
												tbl_studienplan.bezeichnung,
												tbl_studienplan.regelstudiendauer,
												tbl_studienplan.sprache,
												tbl_studienplan.aktiv,
												tbl_studienplan.semesterwochen,
												tbl_studienplan.testtool_sprachwahl,
												tbl_studienplan.insertamum,
												tbl_studienplan.insertvon,
												tbl_studienplan.updateamum,
												tbl_studienplan.updatevon,
												tbl_studienordnung.gueltigvon,
												tbl_studienordnung.gueltigbis,
												tbl_studienordnung.ects,
												tbl_studienordnung.studiengangbezeichnung,
												tbl_studienordnung.studiengangbezeichnung_englisch,
												tbl_studienordnung.studiengangkurzbzlang,
												tbl_studienordnung.akadgrad_id,
												tbl_studiengang.kurzbz,
												tbl_studiengang.kurzbzlang,
												tbl_studiengang.typ,
												tbl_studiengang.english,
												tbl_studiengang.farbe,
												tbl_studiengang.email,
												tbl_studiengang.telefon,
												tbl_studiengang.max_semester,
												tbl_studiengang.max_verband,
												tbl_studiengang.max_gruppe,
												tbl_studiengang.erhalter_kz,
												tbl_studiengang.bescheid,
												tbl_studiengang.bescheidbgbl1,
												tbl_studiengang.bescheidbgbl2,
												tbl_studiengang.bescheidgz,
												tbl_studiengang.bescheidvom,
												tbl_studiengang.titelbescheidvom,
												tbl_studiengang.zusatzinfo_html,
												tbl_studiengang.moodle,
												tbl_studiengang.studienplaetze,
												tbl_studiengang.lgartcode,
												tbl_studiengang.mischform,
												tbl_studiengang.projektarbeit_note_anzeige,
												tbl_studiengang.onlinebewerbung,
												tbl_organisationseinheit.oe_parent_kurzbz,
												tbl_organisationseinheit.mailverteiler,
												tbl_organisationseinheit.freigabegrenze,
												tbl_organisationseinheit.kurzzeichen,
												tbl_organisationseinheit.lehre,
												tbl_organisationseinheittyp.beschreibung,
												tbl_organisationseinheit.standort
										   FROM (((((lehre.tbl_studienplan JOIN lehre.tbl_studienordnung USING (studienordnung_id))
														JOIN public.tbl_studiengang USING (studiengang_kz))
														JOIN public.tbl_organisationseinheit USING (oe_kurzbz))
														JOIN public.tbl_organisationseinheittyp USING (organisationseinheittyp_kurzbz))
														LEFT JOIN lehre.tbl_studienplan_semester USING (studienplan_id))
										) t1 LEFT JOIN bis.tbl_lgartcode USING (lgartcode)
								  WHERE t1.onlinebewerbung IS TRUE
									AND t1.aktiv IS TRUE
							   ORDER BY typ, studiengangbezeichnung, tbl_lgartcode.bezeichnung ASC';
		
		return $this->execQuery($allForBewerbungQuery);
	}

	/**
	 *
	 */
	public function getStudienplan($studiensemester_kurzbz, $ausbildungssemester, $aktiv, $onlinebewerbung)
	{
		// Join table public.tbl_studiengang with table lehre.tbl_studienordnung on column studiengang_kz
		$this->addJoin('lehre.tbl_studienordnung', 'studiengang_kz');
		// Then join with table lehre.tbl_studienplan on column studienordnung_id
		$this->addJoin('lehre.tbl_studienplan', 'studienordnung_id');
		// Then join with table lehre.tbl_studienplan_semester on column studienplan_id
		$this->addJoin('lehre.tbl_studienplan_semester', 'studienplan_id');
		
		// Ordering by studiengang_kz and studienplan_id
		$this->addOrder('public.tbl_studiengang.studiengang_kz');
		$this->addOrder('lehre.tbl_studienplan.studienplan_id');
		
		$result = $this->loadTree(
			'tbl_studiengang',
			array(
				'tbl_studienplan'
			),
			array(
				'lehre.tbl_studienplan_semester.studiensemester_kurzbz' => $studiensemester_kurzbz,
				'lehre.tbl_studienplan_semester.semester' => $ausbildungssemester,
				'public.tbl_studiengang.aktiv' => $aktiv,
				'public.tbl_studiengang.onlinebewerbung' => $onlinebewerbung
			),
			array(
				'studienplaene'
			)
		);
		
		return $result;
	}

	/**
	 *
	 */
	public function getStudiengangBewerbung()
	{
		// Join table public.tbl_studiengang with table lehre.tbl_studienordnung on column studiengang_kz
		$this->addJoin('lehre.tbl_studienordnung', 'studiengang_kz');
		// Join table lehre.tbl_studienordnung with table lehre.tbl_akadgrad on column akadgrad_id
		$this->addJoin('lehre.tbl_akadgrad', 'akadgrad_id', 'LEFT');
		// Then join with table lehre.tbl_studienplan on column studienordnung_id
		$this->addJoin('lehre.tbl_studienplan', 'studienordnung_id');
		// Then join with table lehre.tbl_studienplan_semester on column studienplan_id
		$this->addJoin('lehre.tbl_studienplan_semester ss', 'studienplan_id');
		// Then join with table lehre.tbl_bewerbungsfrist on column studiensemester_kurzbz
		$this->addJoin(
			'public.tbl_bewerbungstermine',
			'tbl_bewerbungstermine.studiensemester_kurzbz = ss.studiensemester_kurzbz AND tbl_bewerbungstermine.studienplan_id = ss.studienplan_id',
			'LEFT'
		);
		// Ordering by studiengang_kz and studienplan_id
		$this->addOrder('public.tbl_studiengang.bezeichnung');
		$this->addOrder('lehre.tbl_studienplan.studienplan_id');
		
		$result = $this->loadTree(
			'tbl_studiengang',
			array(
				'tbl_studienplan',
				'tbl_akadgrad'
			),
			'public.tbl_studiengang.aktiv = TRUE
			AND public.tbl_studiengang.onlinebewerbung = TRUE
			AND ((tbl_bewerbungstermine.beginn <= NOW() AND tbl_bewerbungstermine.ende >= NOW()) OR tbl_bewerbungstermine.beginn IS NULL)
			AND ss.studiensemester_kurzbz IN (SELECT DISTINCT studiensemester_kurzbz FROM public.tbl_bewerbungstermine WHERE beginn <= NOW() AND ende >= NOW())
			AND ss.semester = 1
			AND lehre.tbl_studienplan.aktiv = TRUE'
			,
			array(
				'studienplaene',
				'akadgrad'
			)
		);
		
		return $result;
	}
	
	/**
	 * TODO
	 */
	public function getAppliedStudiengang($person_id, $studiensemester_kurzbz, $titel, $status_kurzbz)
	{
		// Then join with table 
		$this->addJoin('public.tbl_prestudent', 'studiengang_kz');
		// Join table 
		$this->addJoin('public.tbl_prestudentstatus', 'prestudent_id');
		// Then join with table 
		$this->addJoin('lehre.tbl_studienplan', 'studienplan_id');
		// Then join with table 
		$this->addJoin(
			'(
				SELECT *
				  FROM public.tbl_notizzuordnung INNER JOIN public.tbl_notiz n USING(notiz_id)
				 WHERE n.titel = \''.$titel.'\') tbl_nn',
			'prestudent_id',
			'LEFT'
		);
		
		// Ordering by studiengang_kz and studienplan_id
		$this->addOrder('public.tbl_studiengang.bezeichnung');
		
		$result = $this->loadTree(
			'tbl_studiengang',
			array(
				'tbl_prestudent',
				'tbl_prestudentstatus',
				'tbl_studienplan',
				'tbl_nn'
			),
			'public.tbl_prestudent.person_id = '.$person_id.
			' AND public.tbl_prestudentstatus.studiensemester_kurzbz = \''.$studiensemester_kurzbz.'\''.
			' AND (public.tbl_prestudentstatus.status_kurzbz = \'Interessent\' OR public.tbl_prestudentstatus.status_kurzbz = \'Bewerber\')'
			,
			array(
				'prestudenten',
				'prestudentstatus',
				'studienplaene',
				'notizen'
			)
		);
		
		return $result;
	}
}