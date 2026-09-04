<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add stundengrenze table
if (!$result = @$db->db_query("SELECT 1 FROM hr.tbl_stundengrenze LIMIT 1"))
{
	$qry = "
			CREATE TABLE IF NOT EXISTS hr.tbl_stundengrenze (
				stundengrenze_id bigserial NOT NULL,
				mitarbeiter_uid character varying(32) NOT NULL,
				studiensemester_kurzbz varchar(16) NOT NULL,
				oe_kurzbz character varying(32),
				stundengrenze numeric(8, 2),
				insertvon character varying(32) NOT NULL,
				insertamum timestamp without time zone DEFAULT now() NOT NULL,
				updatevon character varying(32),
				updateamum timestamp without time zone,
				CONSTRAINT tbl_stundengrenze_pkey PRIMARY KEY (stundengrenze_id),
				UNIQUE(mitarbeiter_uid, studiensemester_kurzbz, oe_kurzbz)
			);

			ALTER TABLE hr.tbl_stundengrenze DROP CONSTRAINT IF EXISTS tbl_stundengrenze_mitarbeiter_uid_fk;
			ALTER TABLE hr.tbl_stundengrenze ADD CONSTRAINT tbl_stundengrenze_mitarbeiter_uid_fk FOREIGN KEY (mitarbeiter_uid)
			REFERENCES public.tbl_mitarbeiter (mitarbeiter_uid) MATCH FULL
			ON DELETE SET NULL ON UPDATE CASCADE;

			ALTER TABLE hr.tbl_stundengrenze DROP CONSTRAINT IF EXISTS tbl_stundengrenze_studiensemester_kurzbz_fk;
			ALTER TABLE hr.tbl_stundengrenze ADD CONSTRAINT tbl_stundengrenze_studiensemester_kurzbz_fk FOREIGN KEY (studiensemester_kurzbz)
			REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) MATCH FULL
			ON DELETE SET NULL ON UPDATE CASCADE;

			ALTER TABLE hr.tbl_stundengrenze DROP CONSTRAINT IF EXISTS tbl_stundengrenze_oe_kurzbz_fk;
			ALTER TABLE hr.tbl_stundengrenze ADD CONSTRAINT tbl_stundengrenze_oe_kurzbz_fk FOREIGN KEY (oe_kurzbz)
			REFERENCES public.tbl_organisationseinheit (oe_kurzbz) MATCH FULL
			ON DELETE SET NULL ON UPDATE CASCADE;

			COMMENT ON TABLE hr.tbl_stundengrenze IS 'Definition of upper boundaries for hours of lecttureships.';

			GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_stundengrenze TO vilesci;
			GRANT USAGE ON hr.tbl_stundengrenze_stundengrenze_id_seq TO vilesci;";

	if (!$db->db_query($qry))
		echo '<strong>Stundengrenze Tabelle: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'Stundengrenze Tabelle erstellt';

}
