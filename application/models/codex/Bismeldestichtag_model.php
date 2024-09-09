<?php
class Bismeldestichtag_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bismeldestichtag';
		$this->pk = 'meldestichtag_id';
	}

	public function getLastReachedMeldestichtag($studiensemester_kurzbz = null)
	{
		$this->addSelect('meldestichtag_id');
		$this->addSelect('meldestichtag');
		$this->addSelect('studiensemester_kurzbz');
		$this->addSelect('insertamum');
		$this->addSelect('insertvon');
		$this->addSelect('updateamum');
		$this->addSelect('updatevon');

		if ($studiensemester_kurzbz) {
			$this->db->where('studiensemester_kurzbz', $studiensemester_kurzbz);
		}

		$this->addOrder('meldestichtag', 'DESC');
		$this->addLimit(1);

		return $this->loadWhere([
			'meldestichtag < NOW()' => null
		]);
	}

	/**
	 * Prüft, ob Meldestichtag für ein bestimmtes Statusdatum und Studiensemester erreicht ist.
	 *
	 * @param $status_datum
	 * @return boolean true wenn erreicht, oder false
	 */
	public function checkIfMeldestichtagErreicht($status_datum, $studiensemester_kurzbz = null)
	{
		$erreicht = false;

		if (isset($studiensemester_kurzbz))
		{
			// Studiensemesterende holen
			$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
			$result = $this->StudiensemesterModel->loadWhere(
				array(
					'studiensemester_kurzbz' => $studiensemester_kurzbz
				)
			);
			if(isError($result))
			{
				return $result;
			}
			$result = current(getData($result));

			$studiensemester_ende = new DateTime($result->ende);
		}

		// letztes erreichtes Bismeldedatum holen
		$result = $this->getLastReachedMeldestichtag();
		if (isError($result))
		{
			return $result;
		}
		if (!hasData($result)) {
			return success("0",'No Statusdata vorhanden');
		}
		$stichtag = current(getData($result));
		$stichtag = new DateTime($stichtag->meldestichtag);

		$statusDatum = new DateTime($status_datum);

		// Prüfen, ob Studentstatusdatum oder Studiensemester vor dem Stichtagsdatum liegen
		if (isset($statusDatum))
		{
			if (isset($stichtag))
				$erreicht = $statusDatum < $stichtag;
		}

		if (isset($studiensemester_ende))
		{
			$erreicht = $erreicht || $studiensemester_ende < $stichtag;
		}

		if ($erreicht)
			return success("1", "Studentstatus mit Datum oder Semesterende vor erreichtem Meldestichtag können nicht hinzugefügt werden");

		return success("0", "Meldestatus nicht erreicht");
	}

	/**
	 * Gets last Bismeldestichtag for a Studiensemester.
	 * @param $studiensemester_kurzbz
	 * @return object success or error
	 */
	public function getByStudiensemester($studiensemester_kurzbz)
	{
		$query = '
				SELECT
					meldestichtag
				FROM
					bis.tbl_bismeldestichtag
					JOIN public.tbl_studiensemester USING (studiensemester_kurzbz)
				WHERE
					studiensemester_kurzbz = ?
				ORDER BY meldestichtag DESC
				LIMIT 1';

		return $this->execQuery($query, array($studiensemester_kurzbz));
	}
}
