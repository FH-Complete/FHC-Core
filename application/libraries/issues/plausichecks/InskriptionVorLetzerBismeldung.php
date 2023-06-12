<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class InskriptionVorLetzerBismeldung extends PlausiChecker
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
		$prestudentRes = $this->getInskriptionVorLetzerBismeldung(
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
						'datum_bismeldung' => date_format(date_create($prestudent->datum_bismeldung), 'd.m.Y'),
						'prestudent_id' => $prestudent->prestudent_id,
						'studiensemester_kurzbz' => $prestudent->studiensemester_kurzbz
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
	 * Students of a semester shouldn't start studies before the date of Bismeldung.
	 * e.g. If student studies in WS2022 datum of status shouldn't be before 15.4.2020
	 * e.g. If student studies in SS2022 datum of status shouldn't be before 15.11.2022
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getInskriptionVorLetzerBismeldung(
		$studiensemester_kurzbz,
		$studiengang_kz = null,
		$prestudent_id = null,
		$exkludierte_studiengang_kz = null
	) {
		// get Bismeldedatum
		$datumBis = $this->_getBisdateFromSemester($studiensemester_kurzbz);

		$params = array($datumBis, $studiensemester_kurzbz, $datumBis);

		// get active students
		$qry = "
			SELECT
				DISTINCT ON (student.student_uid) ? AS datum_bismeldung,
				prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_benutzer benutzer
				JOIN public.tbl_student student on(benutzer.uid = student.student_uid)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				benutzer.aktiv=true
				AND status.studiensemester_kurzbz = ?
				/* inscription date before date of first student status */
				AND (
					SELECT datum
					FROM public.tbl_prestudentstatus
					WHERE prestudent_id = prestudent.prestudent_id
					AND studiensemester_kurzbz = status.studiensemester_kurzbz
					AND status_kurzbz = 'Student'
					ORDER BY datum, insertamum, ext_id
					LIMIT 1
				) < ?
				AND stg.melderelevant
				AND prestudent.bismelden";

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

	/**
	 * Gets Bismeldedate from Studiensemester.
	 * @param studiensemester_kurzbz string
	 */
	private function _getBisdateFromSemester($studiensemester_kurzbz)
	{
		$semesterYear = substr($studiensemester_kurzbz, 2, 6);
		$semesterType = substr($studiensemester_kurzbz, 0, 2);

		if ($semesterType == 'SS')
		{
			return date_format(date_create(($semesterYear - 1)."-11-15"), 'Y-m-d');
		}

		if ($semesterType == 'WS')
		{
			return date_format(date_create($semesterYear."-04-15"), 'Y-m-d');
		}
	}
}
