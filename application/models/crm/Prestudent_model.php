<?php

class Prestudent_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_prestudent';
		$this->pk = 'prestudent_id';
	}
	
	/**
	 * @return void
	 */
	public function getLastStatuses($person_id, $studiensemester_kurzbz = null, $studiengang_kz = null, $status_kurzbz = null)
	{
		// Checks if the operation is permitted by the API caller
		if (($isEntitled = $this->isEntitled('public.tbl_prestudent', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_prestudentstatus', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_status', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		
		$query = 'SELECT *
					FROM public.tbl_prestudent p
					JOIN (
							SELECT DISTINCT ON(prestudent_id) *
							  FROM public.tbl_prestudentstatus
							 WHERE prestudent_id IN (SELECT prestudent_id FROM public.tbl_prestudent WHERE person_id = ?)
						  ORDER BY prestudent_id, datum desc, insertamum desc
						) ps USING(prestudent_id)
					JOIN public.tbl_status USING(status_kurzbz)
				   WHERE ps.ausbildungssemester = 1';

		$parametersArray = array($person_id);
		
		if ($studiensemester_kurzbz != '')
		{
			array_push($parametersArray, $studiensemester_kurzbz);
			$query .= ' AND ps.studiensemester_kurzbz = ?';
		}
		
		if (isset($studiengang_kz))
		{
			array_push($parametersArray, $studiengang_kz);
			$query .= ' AND p.studiengang_kz = ?';
		}
		
		if ($status_kurzbz != '')
		{
			array_push($parametersArray, $status_kurzbz);
			$query .= ' AND ps.status_kurzbz = ?';
		}
		
		return $this->execQuery($query, $parametersArray);
	}
	
	/**
	 * 
	 */
	public function updateAufnahmegruppe($prestudentIdArray, $aufnahmegruppe)
	{	
		return $this->execQuery(
			'UPDATE public.tbl_prestudent
				SET aufnahmegruppe_kurzbz = ?
			  WHERE prestudent_id IN ?',
			array(
				$aufnahmegruppe,
				$prestudentIdArray
			)
        );
	}
	
	/**
	 * Returns a list of prestudent with additional information:
	 *	- person_id
	 *	- name, surname, gender and birthday
	 *	- email
	 *	- studiengang and orgform
	 *	- studienplan
	 *	- stufe and aufnahmegruppe
	 *	- reihungstest score
	 */
	public function getPrestudentMultiAssign($studiengang = null, $studiensemester = null, $gruppe = null, $reihungstest = null, $stufe = null)
	{
		$this->addSelect(
			'DISTINCT ON(p.person_id, prestudent_id) p.person_id,
			prestudent_id,
			p.nachname,
			p.vorname,
			p.geschlecht,
			p.gebdatum,
			k.kontakt AS email,
			sg.kurzbzlang,
			sg.bezeichnung,
			sg.orgform_kurzbz,
			sgt.bezeichnung AS typ,
			s.bezeichnung AS studienplan,
			ps.rt_stufe,
			aufnahmegruppe_kurzbz,
			rtp.punkte'
		);
		
		$this->addJoin('public.tbl_rt_person rtp', 'person_id');
		$this->addJoin('public.tbl_person p', 'person_id', 'LEFT');
		$this->addJoin(
			'(
					SELECT person_id,
						   kontakt
					  FROM public.tbl_kontakt
					 WHERE zustellung = TRUE
					   AND kontakttyp = \'email\'
				  ORDER BY kontakt_id DESC
			) k',
			'person_id',
			'LEFT'
		);
		$this->addJoin('public.tbl_prestudentstatus ps', 'prestudent_id');
		$this->addJoin('lehre.tbl_studienplan s', 's.studienplan_id = ps.studienplan_id');
		$this->addJoin('lehre.tbl_studienordnung so', 'studienordnung_id');
		$this->addJoin('public.tbl_studiengang sg', 'sg.studiengang_kz = so.studiengang_kz');
		$this->addJoin('public.tbl_studiengangstyp sgt', 'typ');
		
		$this->addOrder('p.person_id', 'ASC');
		$this->addOrder('prestudent_id', 'ASC');
		
		$parametersArray = array('p.aktiv' => true, 'ps.status_kurzbz' => 'Interessent');
		
		if ($studiengang != null)
		{
			$parametersArray['sg.studiengang_kz'] = $studiengang;
		}
		
		if ($studiensemester != null)
		{
			$parametersArray['ps.studiensemester_kurzbz'] = $studiensemester;
		}
		
		if ($gruppe != null)
		{
			$parametersArray['aufnahmegruppe_kurzbz'] = $gruppe;
		}
		
		if ($reihungstest != null)
		{
			$parametersArray['rtp.rt_id'] = $reihungstest;
		}
		
		if ($stufe != null)
		{
			$parametersArray['ps.rt_stufe'] = $stufe;
		}
		
		return $this->loadWhere($parametersArray);
	}
}