<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_zeitmodell' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_zeitmodell (
    zeitmodell_id serial NOT NULL,
    bezeichnung text,
    aktiv boolean NOT NULL DEFAULT FALSE,
    stundenanzahl numeric(8,2),
    ext_id text,
    sort smallint,
    CONSTRAINT tbl_zeitmodell_pkey PRIMARY KEY (zeitmodell_id)
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_zeitmodell TO vilesci;

INSERT INTO hr.tbl_zeitmodell(bezeichnung,aktiv, stundenanzahl, sort) VALUES
('Zeitmodell 1', TRUE, 38.5,1),
('Zeitmodell 2', TRUE, 38.5,2)
ON CONFLICT (zeitmodell_id) DO NOTHING;
		";

		if (! $db->db_query($qry))
			echo '<strong>Zeitmodell: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_zeitmodell wurde neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.columns WHERE column_name='zeitmodell_id' AND table_name='tbl_vertragsbestandteil_zeitaufzeichnung' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
			ALTER TABLE 
				hr.tbl_vertragsbestandteil_zeitaufzeichnung 
			ADD COLUMN 
				zeitmodell_id int;
            ALTER TABLE hr.tbl_vertragsbestandteil_zeitaufzeichnung ADD CONSTRAINT vertragsbestandteil_zeitaufzeichnung_zeitmodell_fk FOREIGN KEY (zeitmodell_id) REFERENCES hr.tbl_zeitmodell (zeitmodell_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		";
		if (! $db->db_query($qry))
			echo '<strong>Zeitmodell: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Spalte zeitmodell_id wurde in hr.tbl_vertragsbestandteil_zeitaufzeichnung neu erstellt<br>';
		
	}
}
