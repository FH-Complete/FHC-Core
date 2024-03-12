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
		$qry = "
				SELECT
					meldestichtag_id, meldestichtag, studiensemester_kurzbz, insertamum, insertvon, updateamum, updatevon
				FROM
					bis.tbl_bismeldestichtag
				WHERE
					meldestichtag < NOW()
		";

		if (isset($studiensemester_kurzbz))
		{
			$qry .=	"
				AND
					studiensemester_kurzbz= ?";
		}

		$qry .= "
				ORDER BY
					meldestichtag DESC
				LIMIT 1;";

		$result = $this->execQuery($qry, array($studiensemester_kurzbz));

		if (isError($result))
		{
			return error($result);
		}
		if (!hasData($result)) {
			return success("0",'Kein Meldestichtag vorhanden');
		}
		return $result;

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
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			$result = current(getData($result));

			$studiensemester_ende = $result->ende;
		}

		// letztes erreichtes Bismeldedatum holen
		$result = $this->getLastReachedMeldestichtag();
		if (isError($result))
		{
			return error($result);
		}
		if (!hasData($result)) {
			return success("0",'No Statusdata vorhanden');
		}
		$stichtag = current(getData($result));
		$stichtag = $stichtag->meldestichtag;

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

		if($erreicht)
			return success("1", "Studentstatus mit Datum oder Semesterende vor erreichtem Meldestichtag können nicht hinzugefügt werden");

		return success("0", "Meldestatus nicht erreicht");
	}

}
