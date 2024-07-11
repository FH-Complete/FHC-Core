<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class DualesStudiumOhneMarkierung extends PlausiChecker
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
		$prestudentRes = $this->getDualesStudiumOhneMarkierung(
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
					'fehlertext_params' => array(
						'prestudent_id' => $prestudent->prestudent_id,
						'studienplan' => $prestudent->studienplan
					),
					'resolution_params' => array(
						'prestudent_id' => $prestudent->prestudent_id,
						'studiensemester_kurzbz' => $prestudent->studiensemester_kurzbz
					)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * All prestudents in dual Studiengang should have set the dual flag to true.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getDualesStudiumOhneMarkierung(
		$studiensemester_kurzbz,
		$studiengang_kz = null,
		$prestudent_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT pre.person_id, pre.prestudent_id,
				stpl.bezeichnung AS studienplan,
				status.studiensemester_kurzbz,
				status.ausbildungssemester,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_person USING(person_id)
				JOIN lehre.tbl_studienplan stpl USING(studienplan_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN public.tbl_studiensemester sem USING(studiensemester_kurzbz)
			WHERE
				(stpl.orgform_kurzbz = 'DUA' OR status.orgform_kurzbz = 'DUA')
				AND pre.dual = FALSE
				AND status.studiensemester_kurzbz=?
				AND pre.bismelden
				AND stg.melderelevant
				AND NOT EXISTS (
					SELECT 1
					FROM
						public.tbl_prestudentstatus
						JOIN lehre.tbl_studienplan USING(studienplan_id)
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE
						prestudent_id = pre.prestudent_id
						AND
						(
							-- if there is a newer non-dual status, dual has not to be set
							(
								(
									tbl_studienplan.orgform_kurzbz <> stpl.orgform_kurzbz
									OR status.orgform_kurzbz <> tbl_prestudentstatus.orgform_kurzbz
								)
								AND
								(
									tbl_studiensemester.ende::date > sem.ende::date
									OR (tbl_studiensemester.ende::date = sem.ende::date AND tbl_prestudentstatus.datum::date > status.datum::date)
								)
							)
							OR
								-- exclude Abgewiesene - they are not reported
								tbl_prestudentstatus.status_kurzbz = 'Abgewiesener'
						)
				)";

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
