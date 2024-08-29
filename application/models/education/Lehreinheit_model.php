<?php
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
				ma.mitarbeiter_uid, 
				(
					SELECT 
						STRING_AGG(CONCAT(leg.semester, leg.verband, leg.gruppe), ' ') 
					FROM lehre.tbl_lehreinheitgruppe leg 
					WHERE leg.lehreinheit_id = le.lehreinheit_id
				) AS gruppe
			FROM 
				lehre.tbl_lehreinheit le
			JOIN 
				lehre.tbl_lehrveranstaltung lv ON lv.lehrveranstaltung_id = le.lehrveranstaltung_id
			JOIN 
				lehre.tbl_lehreinheitmitarbeiter ma USING (lehreinheit_id)
			WHERE 
				lv.lehrveranstaltung_id = ?
				--AND le.studiensemester_kurzbz = 'WS2021'
				";

		if (isset($studiensemester_kurzbz))
		{
			$query .= " AND le.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		$query .="	
			ORDER BY 
				le.lehreinheit_id;
		";

		return $this->execQuery($query, $params);

	}

}
