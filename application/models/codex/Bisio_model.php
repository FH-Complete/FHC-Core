<?php
class Bisio_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bisio';
		$this->pk = 'bisio_id';
	}

	/**
	 * Gets duration of stay in days by bisio_id.
	 * @param int $bisio_id
	 * @return object success with number of days or error
	 */
	public function getAufenthaltsdauer($bisio_id)
	{
		// get from and to date
		$this->addSelect('von, bis');
		$bisioRes = $this->load($bisio_id);

		if (isError($bisioRes))
			return $bisioRes;

		if (hasData($bisioRes))
		{
			$bisioData = getData($bisioRes)[0];

			$avon = $bisioData->von;
			$abis = $bisioData->bis;

			if (is_null($avon) || is_null($abis))
				return success("Von or bis date not set");

			$vonDate = new DateTime($avon);
			$bisDate = new DateTime($abis);
			$interval = $vonDate->diff($bisDate);
			return success($interval->days);
		}
		else
			return success("Bisio not found");
	}

	/**
	 * Gets outgoing students of certain Semester
	 * @param String studiensemester_kurzbz
	 * @return array of prestudent_ids
	 */
	public function getOutgoingsOfSemester($studiensemester_kurzbz)
	{
		$query = "
			SELECT DISTINCT ps.prestudent_id, tbl_bisio.von, tbl_bisio.bis
			FROM bis.tbl_bisio
			JOIN public.tbl_student USING (student_uid)
			JOIN public.tbl_prestudent ps USING (prestudent_id)
			JOIN public.tbl_prestudentstatus pss ON (ps.prestudent_id = pss.prestudent_id)
			JOIN public.tbl_studiensemester ss ON (pss.studiensemester_kurzbz = ss.studiensemester_kurzbz)
			WHERE ss.studiensemester_kurzbz = ?
			AND (
			  tbl_bisio.von <= ss.ende
			  AND (
				tbl_bisio.bis >= ss.start
				OR tbl_bisio.bis IS NULL
			  )
			)
		";

		return $this->execQuery($query, array($studiensemester_kurzbz));
	}

	public function isPrestudentOutgoing($studiensemester_kurzbz, $prestudent_id)
	{
		$query = "
			SELECT
				ps.prestudent_id, tbl_bisio.von, tbl_bisio.bis
			FROM bis.tbl_bisio
			JOIN public.tbl_student USING (student_uid)
			JOIN public.tbl_prestudent ps USING (prestudent_id)
			JOIN public.tbl_prestudentstatus pss ON (ps.prestudent_id = pss.prestudent_id)
			JOIN public.tbl_studiensemester ss ON (pss.studiensemester_kurzbz = ss.studiensemester_kurzbz)
			WHERE ss.studiensemester_kurzbz = ?
			--AND pss.status_kurzbz = 'Student'
			AND (
			  tbl_bisio.von <= ss.ende
			  AND (
				tbl_bisio.bis >= ss.start
				OR tbl_bisio.bis IS NULL
			  )
			)
			AND ps.prestudent_id = ?
		";

		return $this->execQuery($query, array($studiensemester_kurzbz, $prestudent_id));
	}
}
