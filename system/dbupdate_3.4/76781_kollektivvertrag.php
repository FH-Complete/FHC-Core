<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_kollektivvertrag' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "

CREATE TABLE IF NOT EXISTS hr.tbl_kollektivvertrag (
    kollektivvertrag_kurzbz character varying(32) NOT NULL,
    oe_kurzbz character varying(32),
    bezeichnung varchar(64) NOT NULL,
    aktiv boolean DEFAULT FALSE,
    sort smallint,
    insertvon character varying(32) NOT NULL,
    insertamum timestamp without time zone DEFAULT now() NOT NULL,
    updatevon character varying(32),
    updateamum timestamp without time zone,
    CONSTRAINT tbl_kollektivvertrag_pkey PRIMARY KEY (kollektivvertrag_kurzbz),
    CONSTRAINT tbl_kollektivvertrag_oe_kurzbz_fk FOREIGN KEY (oe_kurzbz) REFERENCES tbl_organisationseinheit(oe_kurzbz) MATCH FULL ON UPDATE CASCADE ON DELETE RESTRICT
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_kollektivvertrag TO vilesci;

INSERT INTO hr.tbl_kollektivvertrag(kollektivvertrag_kurzbz, oe_kurzbz, bezeichnung, aktiv, sort, insertvon, insertamum) VALUES('IT','gmbh','KV IT',true,1,'system',NOW());
INSERT INTO hr.tbl_kollektivvertrag(kollektivvertrag_kurzbz, oe_kurzbz, bezeichnung, aktiv, sort, insertvon, insertamum) VALUES('XY','gst','KV IT',true,1,'system',NOW());
    ";


    if (! $db->db_query($qry))
                echo '<strong>Kollektivvertrag: ' . $db->db_last_error() . '</strong><br>';
            else
                echo 'hr.tbl_kollektivvertrag_verwendungsgruppe wurde neu erstellt<br>';
        
    }

}



if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_kollektivvertrag_verwendungsgruppe' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "

CREATE TABLE IF NOT EXISTS hr.tbl_kollektivvertrag_verwendungsgruppe (
    verwendungsgruppe_kurzbz character varying(32) NOT NULL,
    kollektivvertrag_kurzbz character varying(32) NOT NULL,
    bezeichnung varchar(64) NOT NULL,
    aktiv boolean DEFAULT FALSE,
    sort smallint,
    insertvon character varying(32) NOT NULL,
    insertamum timestamp without time zone DEFAULT now() NOT NULL,
    updatevon character varying(32),
    updateamum timestamp without time zone,
    CONSTRAINT tbl_kollektivvertrag_verwendungsgruppe_pkey PRIMARY KEY (verwendungsgruppe_kurzbz),
    CONSTRAINT tbl_kollektivvertrag_kollektivvertrag_kurzbz_fk FOREIGN KEY (kollektivvertrag_kurzbz) REFERENCES hr.tbl_kollektivvertrag (kollektivvertrag_kurzbz) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_kollektivvertrag_verwendungsgruppe TO vilesci;

INSERT INTO hr.tbl_kollektivvertrag_verwendungsgruppe(verwendungsgruppe_kurzbz, kollektivvertrag_kurzbz, bezeichnung,aktiv, sort, insertvon, insertamum) VALUES
('VG1','IT','VG 1',true,1,'system',NOW()),
('VG2','IT','VG 2',true,2,'system',NOW()),
('VG3','IT','VG 3',true,3,'system',NOW()),
('VG4','IT','VG 4',true,4,'system',NOW()),
('VG5','IT','VG 5',true,5,'system',NOW()),
('VG6','IT','VG 6',true,6,'system',NOW())
ON CONFLICT (verwendungsgruppe_kurzbz) DO NOTHING;
		";

		if (! $db->db_query($qry))
			echo '<strong>KV Gruppe: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_kollektivvertrag_verwendungsgruppe wurde neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_kollektivvertrag_verwendungsgruppenjahr' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_kollektivvertrag_verwendungsgruppenjahr (
    kv_jahre integer NOT NULL,
    bezeichnung varchar(64) NOT NULL,
    verwendungsgruppe_kurzbz character varying(32) NOT NULL,
    aktiv boolean DEFAULT FALSE,
    insertvon character varying(32) NOT NULL,
    insertamum timestamp without time zone DEFAULT now() NOT NULL,
    updatevon character varying(32),
    updateamum timestamp without time zone,
    CONSTRAINT tbl_kollektivvertrag_verwendungsgruppenjahr_pkey PRIMARY KEY (kv_jahre),
	CONSTRAINT tbl_kollektivvertrag_verwendungsgruppenjahr_vg_kurzbz_fk FOREIGN KEY (verwendungsgruppe_kurzbz) REFERENCES hr.tbl_kollektivvertrag_verwendungsgruppe (verwendungsgruppe_kurzbz) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_kollektivvertrag_verwendungsgruppenjahr TO vilesci;

INSERT INTO hr.tbl_kollektivvertrag_verwendungsgruppenjahr(kv_jahre, bezeichnung, verwendungsgruppe_kurzbz, aktiv, insertvon, insertamum) VALUES
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
('5','nach 5','VG6',true,'system',NOW())
ON CONFLICT (kv_jahre) DO NOTHING;


		";

		if (! $db->db_query($qry))
			echo '<strong>KV-Stufe: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_kollektivvertrag_verwendungsgruppenjahr wurde neu erstellt<br>';
	}
}


if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_vertragsbestandteil_kollektivvertrag' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_vertragsbestandteil_kollektivvertrag (
    vertragsbestandteil_id integer NOT NULL,
    verwendungsgruppe_kurzbz character varying(32) NOT NULL,
	kv_jahre integer NOT NULL,
    kommentar varchar(255),
    CONSTRAINT tbl_vertragsbestandteil_kollektivvertrag_pk PRIMARY KEY (vertragsbestandteil_id),
	CONSTRAINT tbl_vertragsbestandteil_fk FOREIGN KEY (vertragsbestandteil_id) REFERENCES hr.tbl_vertragsbestandteil (vertragsbestandteil_id) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT tbl_vertragsbestandteil_kollektivvertrag_vg_kurzbz_fk FOREIGN KEY (verwendungsgruppe_kurzbz) REFERENCES hr.tbl_kollektivvertrag_verwendungsgruppe (verwendungsgruppe_kurzbz) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT tbl_vertragsbestandteil_kollektivvertrag_kv_jahre_fk FOREIGN KEY (kv_jahre) REFERENCES hr.tbl_kollektivvertrag_verwendungsgruppenjahr (kv_jahre) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE
);

COMMENT ON TABLE hr.tbl_vertragsbestandteil_kollektivvertrag IS E'Zuordnung zur Einstufung im Kollektivvertrag';

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_vertragsbestandteil_kollektivvertrag TO vilesci;

INSERT INTO hr.tbl_vertragsbestandteiltyp(vertragsbestandteiltyp_kurzbz, bezeichnung, ueberlappend) VALUES('kollektivvertrag', 'Kollektivvertrag', false);



		";

		if (! $db->db_query($qry))
			echo '<strong>Vertragsbestandteil Lohnguide: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_vertragsbestandteil_kollektivvertrag wurde neu erstellt<br>';
	}
}