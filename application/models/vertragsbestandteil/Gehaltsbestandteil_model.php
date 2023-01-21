<?php



class Gehaltsbestandteil_model extends DB_Model
{

    public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_gehaltsbestandteil';
		$this->pk = 'gehaltsbestandteil_id';
        $encryptionkey_filename = APPPATH.'config/extensions/FHC-Core-Personalverwaltung/keys.config.inc.php';
        require($encryptionkey_filename);
	}

    public function getCurrentGBTByDV($dienstverhaeltnis_id)
    {
        $result = null;

		$qry = "
        SELECT
            gehaltsbestandteil_id,
            von,
            bis,
            anmerkung,
            dienstverhaeltnis_id,
            gehaltstyp_kurzbz,
            valorisierungssperre,
            valorisieren,
            pgp_sym_decrypt(grundbetrag,?) grundbetrag,
            pgp_sym_decrypt(betrag_valorisiert,?) betrag_valorisiert,
            gt.bezeichnung as gehaltstyp_bezeichnung
        FROM hr.tbl_gehaltsbestandteil gbt JOIN hr.tbl_gehaltstyp gt using(gehaltstyp_kurzbz)
        WHERE gbt.dienstverhaeltnis_id=? AND
            (gbt.von<=CURRENT_DATE::text::date and (gbt.bis is null OR gbt.bis>=CURRENT_DATE::text::date))
        ORDER BY gt.sort
        ";

        return $this->execQuery($qry, array(ENCRYPTIONKEY, ENCRYPTIONKEY, $dienstverhaeltnis_id));

    }

}