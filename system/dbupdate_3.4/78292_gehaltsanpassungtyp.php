<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_gehaltsanpassungtyp' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "

		ALTER TABLE 
			hr.tbl_gehaltsbestandteil 
		    ADD COLUMN IF NOT EXISTS
			gehaltsanpassungtyp_kurzbz character varying(32);

		COMMENT ON COLUMN hr.tbl_gehaltsbestandteil.gehaltsanpassungtyp_kurzbz IS E'Ersteinstufung, indiv. Erhöhung, etc.';
		
		CREATE TABLE IF NOT EXISTS hr.tbl_gehaltsanpassungtyp
		(
			gehaltsanpassungtyp_kurzbz character varying(32) NOT NULL,
			bezeichnung varchar(256),
			sort smallint,
			aktiv boolean NOT NULL DEFAULT true,
			CONSTRAINT tbl_gehaltsanpassungtyp_pk PRIMARY KEY (gehaltsanpassungtyp_kurzbz)
		);

		COMMENT ON TABLE hr.tbl_gehaltsanpassungtyp IS E'Key-Table of Salary Adaption Types';

		ALTER TABLE hr.tbl_gehaltsbestandteil DROP CONSTRAINT IF EXISTS tbl_gehaltsanpassungtyp_fk;
		ALTER TABLE hr.tbl_gehaltsbestandteil ADD CONSTRAINT tbl_gehaltsanpassungtyp_fk FOREIGN KEY (gehaltsanpassungtyp_kurzbz)
		REFERENCES hr.tbl_gehaltsanpassungtyp (gehaltsanpassungtyp_kurzbz) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;
		
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_gehaltsanpassungtyp TO vilesci;

		INSERT INTO hr.tbl_gehaltsanpassungtyp(gehaltsanpassungtyp_kurzbz, bezeichnung, sort, aktiv) VALUES('ersteinstufung','Ersteinstufung', 1, true);
		INSERT INTO hr.tbl_gehaltsanpassungtyp(gehaltsanpassungtyp_kurzbz, bezeichnung, sort, aktiv) VALUES('individuelle_erhoehung','Indiv. Gehaltserhöhung', 2, true);
		INSERT INTO hr.tbl_gehaltsanpassungtyp(gehaltsanpassungtyp_kurzbz, bezeichnung, sort, aktiv) VALUES('strukturelle_anpassung','Strukturelle Anpassung', 3, true);
		INSERT INTO hr.tbl_gehaltsanpassungtyp(gehaltsanpassungtyp_kurzbz, bezeichnung, sort, aktiv) VALUES('funktionsaenderung','Funktionsänderung', 4, true);
		
		";

		if (! $db->db_query($qry))
			echo '<strong>Gehaltsanpassungtyp: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Tabelle und Feld für Gehaltsanpassungtyp wurden neu erstellt<br>';
	}
}
