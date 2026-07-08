<?php
class Dokument_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_dokument';
		$this->pk = 'dokument_kurzbz';
	}

	/**
	 * Loads all missing Documents of a Studiengang
	 * a Prestudent has not submitted
	 * @param integer studiengang_kz
	 * @param integer prestudent_id
	 * @param boolean archivdokumente
	 * 	Default: true.
	 * 	If false, documents that are archivable (tbl_vorlage.archivierbar e.g. certificate, notice, ...) not retrieved
	 * @return Array of Documents || error
	 */
	public function getMissingDocuments($studiengang_kz, $prestudent_id = null, $archivdokumente = false, $person_id = null)
	{
		$parametersArray = array($studiengang_kz);

		$qry = "SELECT
    				tbl_dokument.* ,
					tbl_dokumentstudiengang.*
				FROM public.tbl_dokument
				JOIN public.tbl_dokumentstudiengang USING(dokument_kurzbz)
				LEFT JOIN public.tbl_vorlage ON (tbl_dokument.dokument_kurzbz = tbl_vorlage.vorlage_kurzbz)
				WHERE studiengang_kz = ? ";

		if($prestudent_id)
		{
			array_push($parametersArray, $prestudent_id);
			$qry.="	AND tbl_dokument.dokument_kurzbz NOT IN (
					SELECT dokument_kurzbz FROM public.tbl_dokumentprestudent WHERE prestudent_id= ?)";
		}

		if(!$archivdokumente)
		{
			$qry.="	AND (tbl_vorlage.archivierbar = FALSE OR tbl_vorlage.archivierbar IS NULL)";
		}

		$qry.=" ORDER BY tbl_dokument.dokument_kurzbz;";

		return $this->execQuery($qry, $parametersArray);
	}

	public function getUnacceptedDocuments($prestudent_id, $person_id)
	{
		$parametersArray = array($person_id, $prestudent_id);

		$qry = " SELECT
				  a.akte_id,
				  a.bezeichnung,
				  a.dokument_kurzbz,
				  a.titel_intern,
				  a.anmerkung_intern,
				  a.insertamum as hochgeladenamum,
				  a.updatevon,
				  a.insertvon,
				  a.uid,
				  a.dms_id,
				  a.anmerkung as infotext,
				  a.nachgereicht,
				  CASE
					WHEN inhalt IS NOT NULL
					OR a.dms_id IS NOT NULL THEN true
					ELSE false
				  END AS vorhanden,
				  a.nachgereicht_am,
				  ausstellungsnation,
				  formal_geprueft_amum,
				  archiv,
				  signiert,
				  stud_selfservice,
				  akzeptiertamum,
				  inhalt
				FROM
				  public.tbl_akte a
				WHERE
				  a.person_id = ?
				  AND a.dokument_kurzbz NOT IN (
					SELECT
					  dokument_kurzbz
					FROM
					  public.tbl_dokumentprestudent
					WHERE
					  prestudent_id = ?
					)
				AND a.dokument_kurzbz NOT IN ('Zeugnis','DiplSupp','Bescheid')
				ORDER BY a.dokument_kurzbz;";

		return $this->execQuery($qry, $parametersArray);
	}
}
