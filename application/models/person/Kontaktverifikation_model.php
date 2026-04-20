<?php
class Kontaktverifikation_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_kontakt_verifikation';
		$this->pk = 'kontakt_verifikation_id';
	}

	/**
	 * Gets contact verification for a person and a verification code
	 * @param person_id
	 * @param kontakttyp
	 * @param verifikation_code
	 * @param expiration_days number of days after which verifikation code expires
	 * @return object success or error
	 */
	public function getKontaktVerifikation($person_id, $kontakttyp, $verifikation_code, $expiration_days = 1)
	{
		$qry = "
			SELECT
				kt.kontakt_id,
				kv.verifikation_code
			FROM
				public.tbl_kontakt_verifikation kv
				JOIN public.tbl_kontakt kt USING(kontakt_id)
			WHERE kt.person_id = ?
				AND kt.kontakttyp = ?
				AND kv.verifikation_code = ?
				AND kv.erstelldatum >= NOW() - INTERVAL '".$this->escape($expiration_days)." days'
			ORDER BY
				kt.kontakt_id DESC
			LIMIT 1";

		return $this->execQuery($qry, array($person_id, $kontakttyp, $verifikation_code));
	}
}
