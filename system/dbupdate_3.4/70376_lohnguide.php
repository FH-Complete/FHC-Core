<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_lohnguide_jobfamilie' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_lohnguide_jobfamilie (
    jobfamilie_kurzbz character varying(32) NOT NULL,
    bezeichnung varchar(64) NOT NULL,
    aktiv boolean DEFAULT FALSE,
    sort smallint,
    insertvon character varying(32) NOT NULL,
    insertamum timestamp without time zone DEFAULT now() NOT NULL,
    updatevon character varying(32),
    updateamum timestamp without time zone,
    CONSTRAINT tbl_lohnguide_jobfamilie_pkey PRIMARY KEY (jobfamilie_kurzbz)
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_lohnguide_jobfamilie TO vilesci;

INSERT INTO hr.tbl_lohnguide_jobfamilie(jobfamilie_kurzbz, bezeichnung,aktiv, sort, insertvon, insertamum) VALUES
('FÜHRUNG','Führung',true,1,'system',NOW()),
('AKADEMIA','Akademia',true,2,'system',NOW()),
('VERWALTUNG','Verwaltung',true,3,'system',NOW()),
('TECHNIK','Technik',true,4,'system',NOW()),
('IT_SOFTWARE','IT & Software',true,5,'system',NOW()),
('TECHN_DIENSTE','Technische Dienste',true,6,'system',NOW())
ON CONFLICT (jobfamilie_kurzbz) DO NOTHING;
		";

		if (! $db->db_query($qry))
			echo '<strong>Lohnguide Jobfamilie: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_lohnguide_jobfamilie wurde neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_lohnguide_modellfunktion' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_lohnguide_modellfunktion (
    modellfunktion_kurzbz character varying(32) NOT NULL,
    bezeichnung varchar(64) NOT NULL,
    jobfamilie_kurzbz character varying(32) NOT NULL,
    aktiv boolean DEFAULT FALSE,
    sort smallint,
    insertvon character varying(32) NOT NULL,
    insertamum timestamp without time zone DEFAULT now() NOT NULL,
    updatevon character varying(32),
    updateamum timestamp without time zone,
    CONSTRAINT tbl_lohnguide_modellfunktion_pkey PRIMARY KEY (modellfunktion_kurzbz),
	CONSTRAINT tbl_lohnguide_modellfunktion_jobfamilie_fk FOREIGN KEY (jobfamilie_kurzbz) REFERENCES hr.tbl_lohnguide_jobfamilie (jobfamilie_kurzbz) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_lohnguide_modellfunktion TO vilesci;

INSERT INTO hr.tbl_lohnguide_modellfunktion(modellfunktion_kurzbz, bezeichnung, jobfamilie_kurzbz, aktiv, sort, insertvon, insertamum) VALUES
('ABTEILUNGSLEITUNG','Abteilungsleitung','FÜHRUNG',true,1,'system',NOW()),
('GF','Geschäftsführung','FÜHRUNG',true,2,'system',NOW()),
('KOMPETENZFELDLEITER','Kompetenzfeldleiter*in','FÜHRUNG',true,3,'system',NOW()),
('DEPARTMENTSLEITER','Departmentsleiter*in','FÜHRUNG',true,4,'system',NOW()),
('FAKULTÄTSLEITER','Fakultätsleiter*in','FÜHRUNG',true,5,'system',NOW()),
/* Akademia */
('STUDENTISCHE_MA','Studentische MA','AKADEMIA',true,6,'system',NOW()),
('JUNIOR_LEC_RES','Junior Lecturer/Researcher','AKADEMIA',true,7,'system',NOW()),
('LEC_RES','Lecturer/Researcher','AKADEMIA',true,8,'system',NOW()),
('SEN_LEC_RES','Senior Lecturer/Researcher','AKADEMIA',true,9,'system',NOW()),
('STUDIENGANGSLEITUNG','Studiengangsleitung','AKADEMIA',true,10,'system',NOW()),
/* Verwaltung */
('FK_VERWALTUNG','Fachkraft Verwaltung','VERWALTUNG',true,11,'system',NOW()),
('SFK_VERWALTUNG','Spezial-Fachkraft Verwaltung','VERWALTUNG',true,12,'system',NOW()),
('SP_VERWALTUNG','Spezialist:in Verwaltung','VERWALTUNG',true,13,'system',NOW()),
('EXP_VERWALTUNG','Expert:in Verwaltung','VERWALTUNG',true,14,'system',NOW()),
/* Technik */
('FK_TECHNIK','Fachkraft Technik','TECHNIK',true,15,'system',NOW()),
/* IT & Software */
('FK_IT','Fachkraft IT & Software','IT_SOFTWARE',true,16,'system',NOW()),
('SFK_IT','Spezial-Fachkraft IT & Software','IT_SOFTWARE',true,17,'system',NOW()),
('SP_IT','Spezialist:in IT & Software','IT_SOFTWARE',true,18,'system',NOW()),
('EXP_IT','Expert:in IT & Software','IT_SOFTWARE',true,19,'system',NOW()),
/* Technische Dienste */
('HK_TECHN_DIENSTE','Hilfskraft Technische Dienste','TECHN_DIENSTE',true,20,'system',NOW()),
('FK_TECHN_DIENSTE','Fachkraft Technische Dienste','TECHN_DIENSTE',true,21,'system',NOW()),
('SFK_TECHN_DIENSTE','Spezial-Fachkraft Technische Dienste','TECHN_DIENSTE',true,22,'system',NOW())
ON CONFLICT (modellfunktion_kurzbz) DO NOTHING;


		";

		if (! $db->db_query($qry))
			echo '<strong>Lohnguide Modellfunktion: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_lohnguide_modellfunktion wurde neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_lohnguide_modellstelle' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_lohnguide_modellstelle (
    modellstelle_kurzbz character varying(32) NOT NULL,
    bezeichnung varchar(128) NOT NULL,
	code character varying(32) NOT NULL,
    grade int NOT NULL,
    modellfunktion_kurzbz character varying(32) NOT NULL,
    aktiv boolean DEFAULT FALSE,
    sort smallint,
    insertvon character varying(32) NOT NULL,
    insertamum timestamp without time zone DEFAULT now() NOT NULL,
    updatevon character varying(32),
    updateamum timestamp without time zone,
    CONSTRAINT tbl_lohnguide_modellstelle_pkey PRIMARY KEY (modellstelle_kurzbz),
	CONSTRAINT tbl_lohnguide_modellstelle_modellfunktion_fk FOREIGN KEY (modellfunktion_kurzbz) REFERENCES hr.tbl_lohnguide_modellfunktion (modellfunktion_kurzbz) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_lohnguide_modellstelle TO vilesci;


-- FÜHRUNG
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz,bezeichnung, code, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES
('ABTL_1_4', 'Abteilungsleitung 1/4',  '111',  16, 'ABTEILUNGSLEITUNG', true,  1, 'system', NOW()),
('ABTL_2A_4', 'Abteilungsleitung 2a/4', '112a', 17, 'ABTEILUNGSLEITUNG', true,  2, 'system', NOW()),
('ABTL_2B_4', 'Abteilungsleitung 2b/4', '112b', 17, 'ABTEILUNGSLEITUNG', true,  3, 'system', NOW()),
('ABTL_3A_4', 'Abteilungsleitung 3a/4', '113a', 18, 'ABTEILUNGSLEITUNG', true,  4, 'system', NOW()),
('ABTL_3B_4', 'Abteilungsleitung 3b/4', '113b', 18, 'ABTEILUNGSLEITUNG', true,  5, 'system', NOW()),
('ABTL_4_4', 'Abteilungsleitung 4/4',  '114',  19, 'ABTEILUNGSLEITUNG', true,  6, 'system', NOW()),
('GF_1_2', 'Geschäftsführung 1/2',   '121',  22, 'GF',                true,  7, 'system', NOW()),
('GF_2_2', 'Geschäftsführung 2/2',   '122',  23, 'GF',                true,  8, 'system', NOW()),
('KOMFL_1_1', 'Kompetenzfeldleiter*in 1/1', '131', 15, 'KOMPETENZFELDLEITER', true,  9, 'system', NOW()),
('DEPL_1_1', 'Departmentleiter*in 1/1',    '141', 18, 'DEPARTMENTSLEITER',   true, 10, 'system', NOW()),
('FAKL_1_1', 'Fakultätsleiter*in 1/1',     '151', 20, 'FAKULTÄTSLEITER',     true, 11, 'system', NOW())
ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- AKADEMIA
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, code, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES
('STUDENTISCHE_MA_1_1', 'Studentische MA 1/1',            '211',  5,  'STUDENTISCHE_MA',  true, 12, 'system', NOW()),
('JUNIOR_LEC_RES_1_2', 'Junior Lecturer/Researcher 1/2', '221',  8,  'JUNIOR_LEC_RES',   true, 13, 'system', NOW()),
('JUNIOR_LEC_RES_2_2', 'Junior Lecturer/Researcher 2/2', '222',  9,  'JUNIOR_LEC_RES',   true, 14, 'system', NOW()),
('LEC_RES_1_2', 'Lecturer/Researcher 1/2',        '231',  11, 'LEC_RES',          true, 15, 'system', NOW()),
('LEC_RES_2_2', 'Lecturer/Researcher 2/2',        '232',  12, 'LEC_RES',          true, 16, 'system', NOW()),
('SEN_LEC_RES_1_2', 'Senior Lecturer/Researcher 1/2', '241',  13, 'SEN_LEC_RES',      true, 17, 'system', NOW()),
('SEN_LEC_RES_2_2', 'Senior Lecturer/Researcher 2/2', '242',  14, 'SEN_LEC_RES',      true, 18, 'system', NOW()),
('STGL_1_2', 'Studiengangsleitung 1/2',        '251',  15, 'STUDIENGANGSLEITUNG', true, 19, 'system', NOW()),
('STGL_2_2', 'Studiengangsleitung 2/2',        '252',  16, 'STUDIENGANGSLEITUNG', true, 20, 'system', NOW())
ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- VERWALTUNG
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, code, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES
('FK_VERWALTUNG_1_3', 'Fachkraft Verwaltung 1/3',            '311',  4,  'FK_VERWALTUNG',  true, 21, 'system', NOW()),
('FK_VERWALTUNG_2A_3', 'Fachkraft Verwaltung 2a/3',           '312a', 5,  'FK_VERWALTUNG',  true, 22, 'system', NOW()),
('FK_VERWALTUNG_2B_3', 'Fachkraft Verwaltung 2b/3',           '312b', 5,  'FK_VERWALTUNG',  true, 23, 'system', NOW()),
('FK_VERWALTUNG_3_3', 'Fachkraft Verwaltung 3/3',            '313',  6,  'FK_VERWALTUNG',  true, 24, 'system', NOW()),
('SFK_VERWALTUNG_1_4', 'Spezial-Fachkraft Verwaltung 1/4',   '321',  7,  'SFK_VERWALTUNG', true, 25, 'system', NOW()),
('SFK_VERWALTUNG_2A_4', 'Spezial-Fachkraft Verwaltung 2a/4',  '322a', 8,  'SFK_VERWALTUNG', true, 26, 'system', NOW()),
('SFK_VERWALTUNG_2B_4', 'Spezial-Fachkraft Verwaltung 2b/4',  '322b', 8,  'SFK_VERWALTUNG', true, 27, 'system', NOW()),
('SFK_VERWALTUNG_3A_4', 'Spezial-Fachkraft Verwaltung 3a/4',  '323a', 9,  'SFK_VERWALTUNG', true, 28, 'system', NOW()),
('SFK_VERWALTUNG_3B_4', 'Spezial-Fachkraft Verwaltung 3b/4',  '323b', 9,  'SFK_VERWALTUNG', true, 29, 'system', NOW()),
('SFK_VERWALTUNG_4_4', 'Spezial-Fachkraft Verwaltung 4/4',   '324',  10, 'SFK_VERWALTUNG', true, 30, 'system', NOW()),
('SP_VERWATLTUNG_1_4', 'Spezialist:in Verwaltung 1/4',       '331',  11, 'SP_VERWALTUNG',  true, 31, 'system', NOW()),
('SP_VERWATLTUNG_2A_4', 'Spezialist:in Verwaltung 2a/4',      '332a', 12, 'SP_VERWALTUNG',  true, 32, 'system', NOW()),
('SP_VERWATLTUNG_2B_4', 'Spezialist:in Verwaltung 2b/4',      '332b', 12, 'SP_VERWALTUNG',  true, 33, 'system', NOW()),
('SP_VERWATLTUNG_3A_4', 'Spezialist:in Verwaltung 3a/4',      '333a', 13, 'SP_VERWALTUNG',  true, 34, 'system', NOW()),
('SP_VERWATLTUNG_3B_4', 'Spezialist:in Verwaltung 3b/4',      '333b', 13, 'SP_VERWALTUNG',  true, 35, 'system', NOW()),
('SP_VERWATLTUNG_4_4', 'Spezialist:in Verwaltung 4/4',       '334',  14, 'SP_VERWALTUNG',  true, 36, 'system', NOW()),
('EXP_VERWALTUNG_1_1', 'Expert:in Verwaltung 1/1',           '341',  15, 'EXP_VERWALTUNG', true, 37, 'system', NOW())
ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- TECHNIK
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, code, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES
('FK_TECHNIK_1_3', 'Fachkraft Technik 1/3',  '311',  4, 'FK_TECHNIK', true, 38, 'system', NOW()),
('FK_TECHNIK_2a_3', 'Fachkraft Technik 2a/3', '312a', 5, 'FK_TECHNIK', true, 39, 'system', NOW()),
('FK_TECHNIK_2b_3','Fachkraft Technik 2b/3', '312b', 5, 'FK_TECHNIK', true, 40, 'system', NOW()),
('FK_TECHNIK_3_3', 'Fachkraft Technik 3/3',  '313',  6, 'FK_TECHNIK', true, 41, 'system', NOW())
ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- IT & Software
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, code, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES
('FK_IT_1_2', 'Fachkraft IT & Software 1/2',          '411',  5,  'FK_IT',  true, 42, 'system', NOW()),
('FK_IT_2_2', 'Fachkraft IT & Software 2/2',          '412',  6,  'FK_IT',  true, 43, 'system', NOW()),
('SFK_IT_1_4', 'Spezial-Fachkraft IT & Software 1/4',  '421',  7,  'SFK_IT', true, 44, 'system', NOW()),
('SFK_IT_2_4', 'Spezial-Fachkraft IT & Software 2/4',  '422',  8,  'SFK_IT', true, 45, 'system', NOW()),
('SFK_IT_3_4', 'Spezial-Fachkraft IT & Software 3/4',  '423',  9,  'SFK_IT', true, 46, 'system', NOW()),
('SFK_IT_4_4', 'Spezial-Fachkraft IT & Software 4/4',  '424',  10, 'SFK_IT', true, 47, 'system', NOW()),
('SP_IT_1_4', 'Spezialist:in IT & Software 1/4',      '431',  11, 'SP_IT',  true, 48, 'system', NOW()),
('SP_IT_2A_4', 'Spezialist:in IT & Software 2a/4',     '432a', 12, 'SP_IT',  true, 49, 'system', NOW()),
('SP_IT_2B_4', 'Spezialist:in IT & Software 2b/4',     '432b', 12, 'SP_IT',  true, 50, 'system', NOW()),
('SP_IT_3A_4', 'Spezialist:in IT & Software 3a/4',     '433a', 13, 'SP_IT',  true, 51, 'system', NOW()),
('SP_IT_3B_4', 'Spezialist:in IT & Software 3b/4',     '433b', 13, 'SP_IT',  true, 52, 'system', NOW()),
('SP_IT_4_4', 'Spezialist:in IT & Software 4/4',      '434',  14, 'SP_IT',  true, 53, 'system', NOW()),
('EXP_IT_1_1', 'Expert:in IT & Software 1/1',          '441',  15, 'EXP_IT', true, 54, 'system', NOW())
ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- TECHNISCHE DIENSTE
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, code, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES
('HK_TECHN_DIENSTE_1_4', 'Hilfskraft Technische Dienste 1/4',        '511',  1,  'HK_TECHN_DIENSTE',  true, 55, 'system', NOW()),
('HK_TECHN_DIENSTE_2_4', 'Hilfskraft Technische Dienste 2/4',        '512',  2,  'HK_TECHN_DIENSTE',  true, 56, 'system', NOW()),
('HK_TECHN_DIENSTE_3_4', 'Hilfskraft Technische Dienste 3/4',        '513',  3,  'HK_TECHN_DIENSTE',  true, 57, 'system', NOW()),
('HK_TECHN_DIENSTE_4_4', 'Hilfskraft Technische Dienste 4/4',        '514',  4,  'HK_TECHN_DIENSTE',  true, 58, 'system', NOW()),
('FK_TECHN_DIENSTE_1_2', 'Fachkraft Technische Dienste 1/2',         '521',  5,  'FK_TECHN_DIENSTE',  true, 59, 'system', NOW()),
('FK_TECHN_DIENSTE_2_2', 'Fachkraft Technische Dienste 2/2',         '522',  6,  'FK_TECHN_DIENSTE',  true, 60, 'system', NOW()),
('SFK_TECHN_DIENSTE_1_4', 'Spezial-Fachkraft Technische Dienste 1/4', '531',  7,  'SFK_TECHN_DIENSTE', true, 61, 'system', NOW()),
('SFK_TECHN_DIENSTE_2_4', 'Spezial-Fachkraft Technische Dienste 2/4', '532',  8,  'SFK_TECHN_DIENSTE', true, 62, 'system', NOW()),
('SFK_TECHN_DIENSTE_3_4', 'Spezial-Fachkraft Technische Dienste 3/4', '533',  9,  'SFK_TECHN_DIENSTE', true, 63, 'system', NOW()),
('SFK_TECHN_DIENSTE_4_4', 'Spezial-Fachkraft Technische Dienste 4/4', '534',  10, 'SFK_TECHN_DIENSTE', true, 64, 'system', NOW())
ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

		";

		if (! $db->db_query($qry))
			echo '<strong>Lohnguide Modellstelle: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_lohnguide_modellstelle wurde neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_lohnguide_fachrichtung' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_lohnguide_fachrichtung (
    fachrichtung_kurzbz character varying(32) NOT NULL,
    bezeichnung varchar(32) NOT NULL,
    aktiv boolean DEFAULT FALSE,
    insertvon character varying(32) NOT NULL,
    insertamum timestamp without time zone DEFAULT now() NOT NULL,
    updatevon character varying(32),
    updateamum timestamp without time zone,
    CONSTRAINT tbl_lohnguide_fachrichtung_pkey PRIMARY KEY (fachrichtung_kurzbz)
);

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_lohnguide_fachrichtung TO vilesci;

INSERT INTO hr.tbl_lohnguide_fachrichtung(fachrichtung_kurzbz,bezeichnung,aktiv,insertvon,insertamum) VALUES
('FA00','Keine Berücksichtigung',true,'system',NOW())
ON CONFLICT (fachrichtung_kurzbz) DO NOTHING;

		";

		if (! $db->db_query($qry))
			echo '<strong>Lohnguide Fachrichtung: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_lohnguide_fachrichtung wurde neu erstellt<br>';
	
	}
}



if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_vertragsbestandteil_lohnguide' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
CREATE TABLE IF NOT EXISTS hr.tbl_vertragsbestandteil_lohnguide (
    vertragsbestandteil_id integer NOT NULL,
	vordienstzeit int,
    stellenbezeichnung varchar(255),
    fachrichtung_kurzbz character varying(32) NOT NULL,
    modellstelle_kurzbz character varying(32) NOT NULL,
    kommentar_person varchar(255),
    kommentar_modellstelle varchar(255),
    CONSTRAINT tbl_vertragsbestandteil_lohnguide_pk PRIMARY KEY (vertragsbestandteil_id),
	CONSTRAINT tbl_vertragsbestandteil_fk FOREIGN KEY (vertragsbestandteil_id) REFERENCES hr.tbl_vertragsbestandteil (vertragsbestandteil_id) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT tbl_vertragsbestandteil_lohnguide_fachrichtung_fk FOREIGN KEY (fachrichtung_kurzbz) REFERENCES hr.tbl_lohnguide_fachrichtung (fachrichtung_kurzbz) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT tbl_vertragsbestandteil_modellstelle_fachrichtung_fk FOREIGN KEY (modellstelle_kurzbz) REFERENCES hr.tbl_lohnguide_modellstelle (modellstelle_kurzbz) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE
);

COMMENT ON TABLE hr.tbl_vertragsbestandteil_lohnguide IS E'Zuordnung für EU-Entgelttransparenzrichtlinie';

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE hr.tbl_vertragsbestandteil_lohnguide TO vilesci;


		";

		if (! $db->db_query($qry))
			echo '<strong>Vertragsbestandteil Lohnguide: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'hr.tbl_vertragsbestandteil_lohnguide wurde neu erstellt<br>';
	}
}

if($result = $db->db_query("SELECT 1 FROM hr.tbl_vertragsbestandteiltyp WHERE vertragsbestandteiltyp_kurzbz = 'lohnguide'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "insert into hr.tbl_vertragsbestandteiltyp (vertragsbestandteiltyp_kurzbz,bezeichnung,ueberlappend) values('lohnguide','Lohnguide',false)";

		if(!$db->db_query($qry))
			echo '<strong>Public Tabelle person: '.$db->db_last_error().'</strong><br>';
		else
			echo "<br>Vertragsbestandteiltyp 'lohnguide' hinzugefuegt";
	}
}


if ($result = $db->db_query("SELECT * FROM information_schema.columns WHERE column_name='vordienstzeit' AND table_name='tbl_vertragsbestandteil_lohnguide' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
			ALTER TABLE 
				hr.tbl_vertragsbestandteil_lohnguide 
			ADD COLUMN 
				vordienstzeit int;
		";
		if (! $db->db_query($qry))
			echo '<strong>Lohnguide: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Spalte vordienstzeit wurde in hr.tbl_vertragsbestandteil_lohnguide neu erstellt<br>';
		
	}
}


if ($result = $db->db_query("SELECT * FROM hr.tbl_gehaltstyp WHERE gehaltstyp_kurzbz='ueberstundenpauschale'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
		INSERT INTO hr.tbl_gehaltstyp
		    (gehaltstyp_kurzbz, bezeichnung, valorisierung, sort, aktiv, lvexport)
		VALUES
		    ('ueberstundenpauschale','Überstundenpauschale', true, 8, true, true);
		";

		if (! $db->db_query($qry))
			echo '<strong>Gehaltstyp: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Gehaltstyp "Überstundenpauschale" erstellt.<br />';
	}
}

if ($result = $db->db_query("SELECT * FROM hr.tbl_gehaltstyp WHERE gehaltstyp_kurzbz='sachbezug_pkw'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
		INSERT INTO hr.tbl_gehaltstyp
		    (gehaltstyp_kurzbz, bezeichnung, valorisierung, sort, aktiv, lvexport)
		VALUES
		    ('sachbezug_pkw','Sachbezug PKW', true, 9, true, true);
		";

		if (! $db->db_query($qry))
			echo '<strong>Gehaltstyp: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Gehaltstyp "Sachbezug PKW" erstellt.<br />';
	}
}
		