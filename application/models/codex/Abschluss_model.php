<?php
class Abschluss_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_abschluss';
		$this->pk = 'ausbildung_code';
	}

	public function getActiveAbschluesse($languageIndex)
	{
		return $this->execQuery(
			'
				SELECT
					ausbildung_code, bezeichnung[?], in_oesterreich
				FROM
					bis.tbl_abschluss
				WHERE
					aktiv
				ORDER BY
					CASE WHEN in_oesterreich THEN 0 ELSE 1 END, ausbildung_code',
			array($languageIndex)
		);
	}
}
