<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add index to system.tbl_log
if (!$result = @$db->db_query("SELECT 1 FROM hr.tbl_stundensatz LIMIT 1"))
{
	$qry = "
		CREATE TABLE hr.tbl_stundensatztyp
		(
			stundensatztyp varchar(32) NOT NULL,
			bezeichnung varchar(256),
			aktiv boolean NOT NULL DEFAULT true,
			insertamum timestamp DEFAULT now(),
			insertvon varchar(32),
			updateamum timestamp,
			updatevon varchar(32),
			CONSTRAINT tbl_stundensatztyp_pk PRIMARY KEY (stundensatztyp)
		);

		CREATE TABLE hr.tbl_stundensatz
		(
			stundensatz_id integer NOT NULL,
			uid character varying(32),
			stundensatztyp varchar(32),
			stundensatz numeric(6, 2),
			oe_kurzbz character varying(32),
			gueltig_von date,
			gueltig_bis date,
			insertamum timestamp,
			insertvon varchar(32),
			updateamum timestamp,
			updatevon varchar(32),
			CONSTRAINT tbl_stundensatz_pkey PRIMARY KEY (stundensatz_id)
		);

		CREATE SEQUENCE hr.tbl_stundensatz_stundensatz_id_seq
			START WITH 1
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;
		
		ALTER TABLE hr.tbl_stundensatz ALTER COLUMN stundensatz_id SET DEFAULT nextval('hr.tbl_stundensatz_stundensatz_id_seq');

		ALTER TABLE hr.tbl_stundensatz ADD CONSTRAINT tbl_stundensatz_stundensatztyp_fk FOREIGN KEY (stundensatztyp) REFERENCES hr.tbl_stundensatztyp (stundensatztyp) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE hr.tbl_stundensatz ADD CONSTRAINT tbl_stundensatz_uid_fk FOREIGN KEY (uid) REFERENCES public.tbl_mitarbeiter ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE hr.tbl_stundensatz ADD CONSTRAINT tbl_stundensatz_oe_kurzbz_fk FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_stundensatztyp TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_stundensatz TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_stundensatz_stundensatz_id_seq TO vilesci;

		GRANT SELECT ON hr.tbl_stundensatztyp TO web;
		GRANT SELECT ON hr.tbl_stundensatz TO web;

		INSERT INTO hr.tbl_stundensatztyp(stundensatztyp, bezeichnung, insertvon) VALUES('lehre','Lehre', 'checksystem');
		INSERT INTO hr.tbl_stundensatztyp(stundensatztyp, bezeichnung, insertvon) VALUES('kalkulatorisch','kalkulatorische Stundensaetze', 'checksystem');
	
		CREATE INDEX idx_tbl_stundensatz_uid ON hr.tbl_stundensatz USING btree (uid);
		CREATE INDEX idx_tbl_stundensatz_stundensatz_id ON hr.tbl_stundensatz USING btree (stundensatz_id);
		";
	
	if (!$db->db_query($qry))
		echo '<strong>Stundensaetze Tabelle: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'Stundensaetze Tabelle erstellt';
	
	
}
