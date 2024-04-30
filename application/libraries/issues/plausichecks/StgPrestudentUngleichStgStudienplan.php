<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class StgPrestudentUngleichStgStudienplan extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->getStgPrestudentUngleichStgStudienplan($studiengang_kz, null, null, $exkludierte_studiengang_kz);

		if (isError($prestudentRes)) return $prestudentRes;

		if (hasData($prestudentRes))
		{
			$prestudents = getData($prestudentRes);

			// populate results with data necessary for writing issues
			foreach ($prestudents as $prestudent)
			{
				$results[] = array(
					'person_id' => $prestudent->person_id,
					'oe_kurzbz' => $prestudent->prestudent_stg_oe_kurzbz,
					'fehlertext_params' => array('prestudent_id' => $prestudent->prestudent_id, 'studienplan' => $prestudent->studienplan),
					'resolution_params' => array('prestudent_id' => $prestudent->prestudent_id, 'studienordnung_id' => $prestudent->studienordnung_id)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Studiengang should be the same for prestudent and studienplan.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param studienordnung_id int if check is to be executed only for a certain studienordnung_id
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getStgPrestudentUngleichStgStudienplan(
		$studiengang_kz = null,
		$prestudent_id = null,
		$studienordnung_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON (ps.prestudent_id) ps.person_id, ps.prestudent_id, stordnung.studienordnung_id,
				stplan.bezeichnung AS studienplan, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudent ps
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
				JOIN lehre.tbl_studienplan stplan USING(studienplan_id)
				JOIN lehre.tbl_studienordnung stordnung USING(studienordnung_id)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_studiengang stg ON ps.studiengang_kz = stg.studiengang_kz
			WHERE
				ps.studiengang_kz<>stordnung.studiengang_kz
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND ps.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		if (isset($studienordnung_id))
		{
			$qry .= " AND stordnung.studienordnung_id = ?";
			$params[] = $studienordnung_id;
		}

		if (isset($exkludierte_studiengang_kz) && !isEmptyArray($exkludierte_studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz NOT IN ?";
			$params[] = $exkludierte_studiengang_kz;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
