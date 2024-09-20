<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class AbschlussstatusFehlt extends PlausiChecker
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
		$prestudentRes = $this->getAbschlussstatusFehlt(
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
					'resolution_params' => array('prestudent_id' => $prestudent->prestudent_id)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Prestudent should have a final status.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getAbschlussstatusFehlt(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$prestudent_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON (pre.prestudent_id)
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				NOT EXISTS( /*student does not study anymore*/
					SELECT
						1
					FROM
						public.tbl_prestudentstatus ps
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE
						prestudent_id=pre.prestudent_id
						/* 4 months: There might be Diplomanden, in summer months end status is often not entered yet */
						AND tbl_studiensemester.ende>now() - interval '4 months'
				)
				/* check only valid begininng with 2018 */
				AND '2018-01-01'<(SELECT max(datum) FROM public.tbl_prestudentstatus WHERE prestudent_id=pre.prestudent_id)
				AND NOT EXISTS( /* no end status */
					SELECT 1
					FROM public.tbl_prestudentstatus ps
					WHERE
						prestudent_id=pre.prestudent_id
						AND status_kurzbz IN('Abbrecher','Abgewiesener','Absolvent','Incoming')
				)
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiensemester_kurzbz))
		{
			$prevStudiensemesterRes = $this->_ci->StudiensemesterModel->getPreviousFrom($studiensemester_kurzbz);

			if (isError($prevStudiensemesterRes)) return $prevStudiensemesterRes;

			if (hasData($prevStudiensemesterRes))
			{
				// if Studiensemester given, check only if has status in current or previous semester
				$prevStudiensemester = getData($prevStudiensemesterRes)[0]->studiensemester_kurzbz;
				$qry .= " AND EXISTS (
							SELECT 1
							FROM public.tbl_prestudentstatus ps
							WHERE studiensemester_kurzbz IN (?, ?)
							AND ps.prestudent_id = pre.prestudent_id
						)";
				$params[] = $prevStudiensemester;
				$params[] = $studiensemester_kurzbz;
			}
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
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
