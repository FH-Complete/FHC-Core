<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class BewerberNichtZumRtAngetreten extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		// get parameters from config
		$exkludierte_studiengang_kz = isset($this->_config['exkludierteStudiengaenge']) ? $this->_config['exkludierteStudiengaenge'] : null;

		// pass parameters needed for plausicheck
		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;

		// get all students failing the plausicheck
		$prestudentRes = $this->getBewerberNichtZumRtAngetreten(
			$studiensemester_kurzbz,
			$studiengang_kz,
			null,
			$exkludierte_studiengang_kz
		);

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
					'fehlertext_params' => array('prestudent_id' => $prestudent->prestudent_id),
					'resolution_params' => array(
						'studiensemester_kurzbz' => $prestudent->studiensemester_kurzbz,
						'prestudent_id' => $prestudent->prestudent_id
					)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Bewerber should have participated in Reihungstest.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getBewerberNichtZumRtAngetreten(
		$studiensemester_kurzbz,
		$studiengang_kz = null,
		$prestudent_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$previousStudiensemesterRes = $this->_ci->StudiensemesterModel->getPreviousFrom($studiensemester_kurzbz);

		if (isError($previousStudiensemesterRes)) return $previousStudiensemesterRes;

		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent prestudent
				JOIN public.tbl_prestudentstatus status ON(prestudent.prestudent_id=status.prestudent_id)
				JOIN public.tbl_person USING(person_id)
				LEFT JOIN bis.tbl_orgform USING(orgform_kurzbz)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				status_kurzbz='Bewerber'
				AND reihungstestangetreten=false
				AND stg.melderelevant
				AND prestudent.bismelden";

		if (hasData($previousStudiensemesterRes))
		{
			$previousStudiensemester = getData($previousStudiensemesterRes)[0]->studiensemester_kurzbz;
			$qry .= " AND (studiensemester_kurzbz=? OR studiensemester_kurzbz=?)";
			$params[] = $previousStudiensemester;
		}
		else
		{
			$qry .= " AND studiensemester_kurzbz=?";
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		if (isset($exkludierte_studiengang_kz) && !isEmptyArray($exkludierte_studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz NOT IN ?";
			$params[] = $exkludierte_studiengang_kz;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
