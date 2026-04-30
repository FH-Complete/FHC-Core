<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_kv_gruppe' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_kv_gruppe (
    kv_gruppe_kurzbz character varying(32) NOT NULL,
    bezeichnung varchar(64) NOT NULL,
    aktiv boolean DEFAULT FALSE,
    sort smallint,
    insertvon character varying(32) NOT NULL,
    insertamum timestamp without time zone DEFAULT now() NOT NULL,
    updatevon character varying(32),
    updateamum timestamp without time zone,
    CONSTRAINT tbl_kv_gruppe_pkey PRIMARY KEY (kv_gruppe_kurzbz)
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_kv_gruppe TO vilesci;

INSERT INTO hr.tbl_kv_gruppe(kv_gruppe_kurzbz, bezeichnung,aktiv, sort, insertvon, insertamum) VALUES
('VG1','VG 1',true,1,'system',NOW()),
('VG2','VG 2',true,2,'system',NOW()),
('VG3','VG 3',true,3,'system',NOW()),
('VG4','VG 4',true,4,'system',NOW()),
('VG5','VG 5',true,5,'system',NOW()),
('VG6','VG 6',true,6,'system',NOW())
ON CONFLICT (kv_gruppe_kurzbz) DO NOTHING;
		";

		if (! $db->db_query($qry))
			echo '<strong>KV Gruppe: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_kv_gruppe wurde neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_kv_stufe' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_kv_jahre (
    kv_jahre integer NOT NULL,
    bezeichnung varchar(64) NOT NULL,
    kv_gruppe_kurzbz character varying(32) NOT NULL,
    aktiv boolean DEFAULT FALSE,
    sort smallint,
    insertvon character varying(32) NOT NULL,
    insertamum timestamp without time zone DEFAULT now() NOT NULL,
    updatevon character varying(32),
    updateamum timestamp without time zone,
    CONSTRAINT tbl_kv_jahre_pkey PRIMARY KEY (kv_jahre),
	CONSTRAINT tbl_kv_jahre_kv_gruppe_kurzbz_fk FOREIGN KEY (kv_gruppe_kurzbz) REFERENCES hr.tbl_kv_jahre (kv_gruppe_kurzbz) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_kv_jahre TO vilesci;

INSERT INTO hr.tbl_kv_jahre(kv_jahre, bezeichnung, kv_gruppe_kurzbz, aktiv, insertvon, insertamum) VALUES
('0','nach 0','VG1',true, 'system',NOW()),
('2','nach 2','VG1',true, 'system',NOW()),
('4','nach 4','VG1',true, 'system',NOW()),
('5','nach 5','VG1',true, 'system',NOW()),

('0','nach 0','VG2',true,'system',NOW()),
('2','nach 2','VG2',true,'system',NOW()),
('4','nach 4','VG2',true,'system',NOW()),
('5','nach 5','VG2',true,'system',NOW()),

('0','nach 0','VG3',true,'system',NOW()),
('2','nach 2','VG3',true,'system',NOW()),
('4','nach 4','VG3',true,'system',NOW()),
('5','nach 5','VG3',true,'system',NOW()),

('0','nach 0','VG4',true,'system',NOW()),
('2','nach 2','VG4',true,'system',NOW()),
('4','nach 4','VG4',true,'system',NOW()),
('5','nach 5','VG4',true,'system',NOW()),

('0','nach 0','VG5',true,'system',NOW()),
('2','nach 2','VG5',true,'system',NOW()),
('4','nach 4','VG5',true,'system',NOW()),
('5','nach 5','VG5',true,'system',NOW()),

('0','nach 0','VG6',true,'system',NOW()),
('2','nach 2','VG6',true,'system',NOW()),
('4','nach 4','VG6',true,'system',NOW()),
('5','nach 5','VG6',true,'system',NOW()),
ON CONFLICT (kv_jahre) DO NOTHING;


		";

		if (! $db->db_query($qry))
			echo '<strong>KV-Stufe: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_kv_jahre wurde neu erstellt<br>';
	}
}

		