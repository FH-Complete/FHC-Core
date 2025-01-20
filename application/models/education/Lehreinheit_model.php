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
}
