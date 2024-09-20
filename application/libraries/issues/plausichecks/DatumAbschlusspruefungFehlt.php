<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

/**
 *
 */
class DatumAbschlusspruefungFehlt extends PlausiChecker
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
		$prestudentRes = $this->getDatumAbschlusspruefungFehlt(
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
						'abschlusspruefung_id' => $prestudent->abschlusspruefung_id
					),
					'resolution_params' => array('abschlusspruefung_id' => $prestudent->abschlusspruefung_id)
				);
			}
		}

		// return the results
		return success($results);
	}

	/**
	 * Date of final exam shouldn't be missing for Absolvent.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param abschlusspruefung_id int if check is to be executed for a certain Abschlussprüfung
	 * @param exkludierte_studiengang_kz array if certain Studiengänge have to be excluded from check
	 * @return success with prestudents or error
	 */
	public function getDatumAbschlusspruefungFehlt(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$abschlusspruefung_id = null,
		$exkludierte_studiengang_kz = null
	) {
		$params = array();

		$qry = "
			SELECT
				pre.person_id, pre.prestudent_id,
				pruefung.sponsion, pruefung.datum, pruefung.abschlusspruefung_id,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_student stud USING(prestudent_id)
				JOIN public.tbl_prestudentstatus prestatus USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN lehre.tbl_abschlusspruefung pruefung ON stud.student_uid = pruefung.student_uid
			WHERE
				status_kurzbz = 'Absolvent'
				AND NOT EXISTS ( /* exclude gs */
					SELECT 1
					FROM bis.tbl_mobilitaet
					WHERE prestudent_id = pre.prestudent_id
					AND studiensemester_kurzbz = prestatus.studiensemester_kurzbz
				)
				AND abschlussbeurteilung_kurzbz!='nicht'
				AND abschlussbeurteilung_kurzbz IS NOT NULL
				AND pruefung.datum IS NULL
				AND pre.bismelden
				AND stg.melderelevant";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND prestatus.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($abschlusspruefung_id))
		{
			$qry .= " AND pruefung.abschlusspruefung_id = ?";
			$params[] = $abschlusspruefung_id;
		}

		if (isset($exkludierte_studiengang_kz) && !isEmptyArray($exkludierte_studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz NOT IN ?";
			$params[] = $exkludierte_studiengang_kz;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}
