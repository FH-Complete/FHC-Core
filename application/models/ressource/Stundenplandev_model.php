<?php
class Stundenplandev_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_stundenplandev';
		$this->pk = 'stundenplandev_id';
	}

	public function getMissingDirectGroups($studiensemester_kurzbz = null)
	{
		$qry = "
		SELECT
			distinct lehreinheit_id, datum, stunde, mitarbeiter_uid, ort_kurzbz
		FROM
			lehre.tbl_stundenplandev stpl
		WHERE
			lehreinheit_id IN(
				SELECT
					lehreinheit_id
				FROM
					lehre.tbl_lehreinheit
					JOIN lehre.tbl_lehreinheitgruppe USING(lehreinheit_id)
					JOIN public.tbl_gruppe USING(gruppe_kurzbz)
				WHERE
					tbl_gruppe.direktinskription = true
					";

		$parametersArray = array();

		if (!is_null($studiensemester_kurzbz))
		{
			$parametersArray[] = $studiensemester_kurzbz;
			$qry .= ' AND tbl_lehreinheit.studiensemester_kurzbz = ?';
		}
		$qry .= ")
			AND NOT EXISTS(
				SELECT
					1
				FROM
					lehre.tbl_stundenplandev
				WHERE
					datum=stpl.datum
					AND stunde=stpl.stunde
					AND lehreinheit_id=stpl.lehreinheit_id
					AND gruppe_kurzbz=(SELECT
							gruppe_kurzbz
						FROM
							lehre.tbl_lehreinheitgruppe
							JOIN public.tbl_gruppe USING(gruppe_kurzbz)
						WHERE
							lehreinheit_id=stpl.lehreinheit_id
							AND tbl_gruppe.direktinskription = true
						)
				)";

		return $this->execQuery($qry, $parametersArray);
	}

    /**
     * Get Stundenplan data.
     *
     * @param null $lehrveranstaltung_id
     * @param null $studiensemester_kurzbz
     * @param null $lehreinheit_id
     * @param null $mitarbeiter_uid
     * @param null $student_uid
     * @param false $nurBevorstehende If true, only future data is retrieved.
     * @return array|false|stdClass|null
     */
    public function getStundenplanData($lehrveranstaltung_id=null, $studiensemester_kurzbz=null, $lehreinheit_id=null, $mitarbeiter_uid=null, $student_uid=null, $nurBevorstehende = false)
    {
        $params = array();

        $qry = "SELECT
					stpl.datum, min(stpl.stunde) as stundevon, max(stpl.stunde) as stundebis,
					stpl.lehreinheit_id, lehrfach.bezeichnung as lehrfach_bezeichnung,
					array_agg(
					CASE WHEN gruppe_kurzbz is not null THEN gruppe_kurzbz
					ELSE (SELECT UPPER(typ || kurzbz) FROM public.tbl_studiengang WHERE studiengang_kz=stpl.studiengang_kz) || COALESCE(stpl.semester,'0') || COALESCE(stpl.verband,'') || COALESCE(stpl.gruppe,'')
					END) as gruppen, array_agg(mitarbeiter_uid) as lektoren,
					array_agg(ort_kurzbz) as orte,
					array_agg(titel) as titel
				FROM
					lehre.tbl_stundenplandev as stpl
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
				WHERE ";

        if ($lehrveranstaltung_id != '')
        {
            $qry.="
                lehreinheit_id IN (
                    SELECT  lehreinheit_id FROM lehre.tbl_lehreinheit
                    WHERE   lehrveranstaltung_id = ?
                    AND     studiensemester_kurzbz = ?
                )";

            $params[]= $lehrveranstaltung_id;
            $params[]= $studiensemester_kurzbz;

        }
        elseif ($lehreinheit_id!='')
        {
            $qry.=" lehreinheit_id = ?";

            $params[]= $lehreinheit_id;
        }
        elseif ($mitarbeiter_uid != '')
        {
            $qry.= "
                mitarbeiter_uid = ?
                AND lehreinheit_id IN (
				    SELECT  lehreinheit_id
				    FROM    lehre.tbl_lehreinheitmitarbeiter
					JOIN    lehre.tbl_lehreinheit USING (lehreinheit_id)
				    WHERE   mitarbeiter_uid = ?
				    AND     studiensemester_kurzbz IN ( ? )
                )";
            $params[] = $mitarbeiter_uid;
            $params[] = $mitarbeiter_uid;
            $params[] = $studiensemester_kurzbz;
        }
        elseif ($student_uid != '')
        {
            $qry.="
                lehreinheit_id IN (
                    SELECT  lehreinheit_id
                    FROM    campus.vw_student_lehrveranstaltung
                    WHERE   uid = ?
                    AND     studiensemester_kurzbz = ?
                )";

            $params[] = $student_uid;
            $params[] = $studiensemester_kurzbz;
        }
        else
            return false;

        if($nurBevorstehende)
        {
            $qry.= " AND stpl.datum >= NOW()::date ";
        }

        $qry.= "
            GROUP BY stpl.datum, stpl.unr, stpl.lehreinheit_id, lehrfach.bezeichnung
            ORDER BY stpl.datum,  min(stpl.stunde), stpl.unr, stpl.lehreinheit_id
        ";

        return $this->execQuery($qry, $params);
    }

}
