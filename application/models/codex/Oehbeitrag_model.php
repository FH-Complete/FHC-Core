<?php
class Oehbeitrag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_oehbeitrag';
		$this->pk = 'oehbeitrag_id';
	}

	/**
	 * Gets oehbeitrag data valid for a certain Studiensemester.
	 * @param string $studiensemester_kurzbz
	 * @return object
	 */
	public function getByStudiensemester($studiensemester_kurzbz)
	{
		$qry = "WITH semstart AS (
					SELECT start FROM public.tbl_studiensemester
					WHERE studiensemester_kurzbz = ?
				)
				SELECT * FROM bis.tbl_oehbeitrag oehb
				JOIN public.tbl_studiensemester semvon ON oehb.von_studiensemester_kurzbz = semvon.studiensemester_kurzbz
				LEFT JOIN public.tbl_studiensemester sembis ON oehb.bis_studiensemester_kurzbz = sembis.studiensemester_kurzbz
				JOIN semstart ON semstart.start::date >= semvon.start::date AND (sembis.studiensemester_kurzbz IS NULL OR semstart.start::date <= sembis.start::date)
				ORDER BY semvon.start
				LIMIT 1";

		return $this->execQuery($qry, array($studiensemester_kurzbz));
	}

	/**
	 * Gets all Studiensemester for which no Oehbeitrag value assignment exists.
	 * @param string $start_studiensemester_kurzbz semester before the given semester are ignored
	 * @param array $excluded_oehbeitrag_id oehbeitraege to be ignored, i.e. which are assigned
	 * @return object
	 */
	public function getUnassignedStudiensemester($start_studiensemester_kurzbz, $excluded_oehbeitrag_id = array())
	{
		$params =  array($start_studiensemester_kurzbz);

		$qry = "SELECT * FROM public.tbl_studiensemester sem
				WHERE sem.start >= (SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz = ?)
				AND NOT EXISTS (SELECT 1 FROM bis.tbl_oehbeitrag oeh
                    JOIN public.tbl_studiensemester oeh_von ON oeh.von_studiensemester_kurzbz = oeh_von.studiensemester_kurzbz
                    LEFT JOIN public.tbl_studiensemester oeh_bis ON oeh.bis_studiensemester_kurzbz = oeh_bis.studiensemester_kurzbz
                    WHERE sem.start::date >= oeh_von.start::date AND (sem.start::date <= oeh_bis.start::date OR oeh_bis.studiensemester_kurzbz IS NULL)";

		if (!isEmptyArray($excluded_oehbeitrag_id))
		{
			$qry .= " AND oehbeitrag_id NOT IN ?";
			$params[] = $excluded_oehbeitrag_id;
		}

		$qry .= ") ORDER BY sem.start";

		return $this->execQuery($qry, $params);
	}

	/**
	 * Checks if a Öhbeitrag can be assigned for a Studiensemester range.
	 * @param string $von_studiensemester_kurzbz
	 * @param string $bis_studiensemester_kurzbz
	 * @param array $excluded_oehbeitrag_id oehbeitraege to ignore, i.e. which are assignable
	 * @return object array with true if assignable, with false if not
	 */
	public function checkIfStudiensemesterAssignable($von_studiensemester_kurzbz, $bis_studiensemester_kurzbz = null, $excluded_oehbeitrag_id = array())
	{
		$params = array($von_studiensemester_kurzbz);

		$allStdSemSpanQry = "SELECT count(studiensemester_kurzbz) as number_assigned FROM public.tbl_studiensemester sem
			WHERE start >= (SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz = ?)";

		if ($bis_studiensemester_kurzbz != null)
		{
			$allStdSemSpanQry .= " AND (start <= (SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz = ?))";
			$params[] = $bis_studiensemester_kurzbz;
		}

		$allStdSemSpanQry .= " AND EXISTS (SELECT 1 FROM bis.tbl_oehbeitrag
            JOIN public.tbl_studiensemester sem_von ON tbl_oehbeitrag.von_studiensemester_kurzbz = sem_von.studiensemester_kurzbz
            LEFT JOIN public.tbl_studiensemester sem_bis ON tbl_oehbeitrag.bis_studiensemester_kurzbz = sem_bis.studiensemester_kurzbz
            WHERE sem.start >= sem_von.start AND (sem.start <= sem_bis.start OR sem_bis.studiensemester_kurzbz IS NULL)";

		if (!isEmptyArray($excluded_oehbeitrag_id))
		{
			$allStdSemSpanQry .= " AND oehbeitrag_id NOT IN ?";
			$params[] = $excluded_oehbeitrag_id;
		}
		
		$allStdSemSpanQry .= ")";

		$nrAssigned = $this->execQuery($allStdSemSpanQry, $params);

		if (isError($nrAssigned))
			return $nrAssigned;

		if (!hasData($nrAssigned))
			return error("Fehler bei Überprüfung der Möglichkeit der Semesterzuweisung");

		return success(array(getData($nrAssigned)[0]->number_assigned == 0));
	}
}
