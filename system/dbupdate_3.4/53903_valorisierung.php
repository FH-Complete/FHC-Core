<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_valorisierung_methode' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_valorisierung_methode (
    valorisierung_methode_kurzbz character varying(32) NOT NULL,
    beschreibung character varying NOT NULL,
    CONSTRAINT tbl_valorisierung_methode_pkey PRIMARY KEY (valorisierung_methode_kurzbz)
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_valorisierung_methode TO vilesci;

INSERT INTO hr.tbl_valorisierung_methode (valorisierung_methode_kurzbz, beschreibung) VALUES
    ('ValorisierungProzent', 'Valorisierung um einen Prozentsatz'),
    ('ValorisierungFixBetrag', 'Valorisierung um einen fixen Betrag'),
    ('ValorisierungGestaffelt', 'mehrere Stufen mit unterschiedlichen Prozentwerten')
ON CONFLICT(valorisierung_methode_kurzbz) DO NOTHING;
		";

		if (! $db->db_query($qry))
			echo '<strong>Valorisierung: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_valorisierung_methode wurde neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_valorisierung_instanz' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_valorisierung_instanz (
    valorisierung_instanz_id serial NOT NULL,
    oe_kurzbz character varying(32),
    valorisierungsdatum date NOT NULL,
    valorisierung_kurzbz character varying(128) NOT NULL,
    beschreibung text,
    ausgewaehlt boolean DEFAULT false NOT NULL,
    updatevon character varying(32),
    updateamum timestamp without time zone,
    CONSTRAINT tbl_valorisierung_instanz_pkey PRIMARY KEY (valorisierung_instanz_id),
    CONSTRAINT tbl_valorisierung_instanz_fk1 FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit(oe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT,
    UNIQUE(valorisierung_kurzbz)
);

CREATE UNIQUE INDEX IF NOT EXISTS tbl_valorisierung_instanz_unique_idx ON hr.tbl_valorisierung_instanz (ausgewaehlt, valorisierungsdatum, oe_kurzbz) WHERE (ausgewaehlt = TRUE);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_valorisierung_instanz TO vilesci;
GRANT SELECT,UPDATE ON SEQUENCE hr.tbl_valorisierung_instanz_valorisierung_instanz_id_seq TO vilesci;
		";

		if (! $db->db_query($qry))
			echo '<strong>Valorisierung: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_valorisierung_instanz wurde neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_valorisierung_instanz_methode' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_valorisierung_instanz_methode (
    valorisierung_instanz_id integer NOT NULL,
    valorisierung_methode_kurzbz character varying(32) NOT NULL,
    beschreibung text,
    valorisierung_methode_parameter jsonb NOT NULL,
    CONSTRAINT tbl_valorisierung_instanz_methode_pkey PRIMARY KEY (valorisierung_instanz_id, valorisierung_methode_kurzbz),
    CONSTRAINT tbl_valorisierung_instanz_methode_fk1 FOREIGN KEY (valorisierung_instanz_id) REFERENCES hr.tbl_valorisierung_instanz(valorisierung_instanz_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT tbl_valorisierung_instanz_methode_fk2 FOREIGN KEY (valorisierung_methode_kurzbz) REFERENCES hr.tbl_valorisierung_methode(valorisierung_methode_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_valorisierung_instanz_methode TO vilesci;
		";

		if (! $db->db_query($qry))
			echo '<strong>Valorisierung: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_valorisierung_instanz_methode wurde neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_valorisierung_historie' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_valorisierung_historie (
    valorisierung_historie_id serial NOT NULL,
    gehaltsbestandteil_id integer NOT NULL,
    valorisierungsdatum date NOT NULL,
    betrag_valorisiert bytea NOT NULL,
    insertvon character varying(32),
    insertamum timestamp without time zone DEFAULT now(),
    CONSTRAINT tbl_valorisierung_historie_pkey PRIMARY KEY (valorisierung_historie_id)
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_valorisierung_historie TO vilesci;
GRANT SELECT,UPDATE ON SEQUENCE hr.tbl_valorisierung_historie_valorisierung_historie_id_seq TO vilesci;
		";

		if (! $db->db_query($qry))
			echo '<strong>Valorisierung: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_valorisierung_historie wurde neu erstellt<br>';
	}
}
		