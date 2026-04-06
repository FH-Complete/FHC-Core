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
('ALLGEMEIN','Allgemein',true,2,'system',NOW()),
('TECHNIK','Technik',true,3,'system',NOW()),
('IT','IT',true,4,'system',NOW()),
('PRODUKTION','Produktion',true,5,'system',NOW()),
('HANDW_IH_LOG','Handwerk, Instandhaltung + Logistik',true,6,'system',NOW())
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
('FÜHRUNG_I','Führung I','FÜHRUNG',true,1,'system',NOW()),
('FÜHRUNG_II','Führung I','FÜHRUNG',true,2,'system',NOW()),
('FÜHRUNG_III','Führung I','FÜHRUNG',true,3,'system',NOW()),
('GF','Geschäftsführung','FÜHRUNG',true,4,'system',NOW()),
/* Allgemein */
('FK_ALLGM','Fachkraft Allgemein','ALLGEMEIN',true,5,'system',NOW()),
('SFK_ALLGM','Spezial-Fachkraft Allgemein','ALLGEMEIN',true,6,'system',NOW()),
('SP_ALLGM','Spezialist:in Allgemein','ALLGEMEIN',true,7,'system',NOW()),
('EXP_ALLGM','Expert:in Allgemein','ALLGEMEIN',true,8,'system',NOW()),
('TOP_EXP_ALLGM','Top-Expert:in Allgemein','ALLGEMEIN',true,9,'system',NOW()),
/* Technik */
('FK_TECH','Fachkraft Technik','TECHNIK',true,10,'system',NOW()),
('SFK_TECH','Spezial-Fachkraft Technik','TECHNIK',true,11,'system',NOW()),
('SP_TECH','Spezialist:in Technik','TECHNIK',true,12,'system',NOW()),
('EXP_TECH','Expert:in Technik','TECHNIK',true,13,'system',NOW()),
('TOP_EXP_TECH','Top-Expert:in Technik','TECHNIK',true,14,'system',NOW()),
/* IT */
('FK_IT','Fachkraft IT','IT',true,15,'system',NOW()),
('SFK_IT','Spezial-Fachkraft IT','IT',true,16,'system',NOW()),
('SP_IT','Spezialist:in IT','IT',true,17,'system',NOW()),
('EXP_IT','Expert:in IT','IT',true,18,'system',NOW()),
('TOP_EXP_IT','Top-Expert:in IT','IT',true,19,'system',NOW()),
/* Produktion */
('HK_PROD','Hilfskraft Produktion','PRODUKTION',true,20,'system',NOW()),
('FK_PROD','Fachkraft Produktion','PRODUKTION',true,21,'system',NOW()),
('SFK_PROD','Spezial-Fachkraft Produktion','PRODUKTION',true,22,'system',NOW()),
('SP_PROD','Spezialist:in Produktion','PRODUKTION',true,23,'system',NOW()),
/* Handwerk, Instandhaltung, Logistik */
('HK_HIL','Hilfskraft Handwerk, Instandhaltung + Logistik','HANDW_IH_LOG',true,24,'system',NOW()),
('FK_HIL','Fachkraft Handwerk, Instandhaltung + Logistik','HANDW_IH_LOG',true,25,'system',NOW()),
('SFK_HIL','Spezial-Fachkraft Handwerk, Instandhaltung + Logistik','HANDW_IH_LOG',true,26,'system',NOW()),
('SP_HIL','Spezialist:in Handwerk, Instandhaltung + Logistik','HANDW_IH_LOG',true,27,'system',NOW())
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

INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('111','Führung III 1/5',9,'FÜHRUNG_III',true,13,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('112','Führung III 2/5',10,'FÜHRUNG_III',true,14,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('113','Führung III 3/5',11,'FÜHRUNG_III',true,15,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('114','Führung III 4/5',12,'FÜHRUNG_III',true,16,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('115','Führung III 5/5',13,'FÜHRUNG_III',true,17,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('121','Führung II 1/4',14,'FÜHRUNG_II',true,7,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('122a','Führung II 2a/4',15,'FÜHRUNG_II',true,8,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('122b','Führung II 2b/4',15,'FÜHRUNG_II',true,9,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('123a','Führung II 3a/4',16,'FÜHRUNG_II',true,10,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('123b','Führung II 3b/4',16,'FÜHRUNG_II',true,11,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('124','Führung II 4/4',17,'FÜHRUNG_II',true,12,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('131','Führung I 1/4',18,'FÜHRUNG_I',true,1,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('132a','Führung I 2a/4',19,'FÜHRUNG_I',true,2,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('132b','Führung I 2b/4',19,'FÜHRUNG_I',true,3,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('133a','Führung I 3a/4',20,'FÜHRUNG_I',true,4,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('133b','Führung I 3b/4',20,'FÜHRUNG_I',true,5,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('134','Führung I 4/4',21,'FÜHRUNG_I',true,6,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- GF
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('141','Geschäftsführung 1/5',22,'GF',true,18,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('142a','Geschäftsführung 2a/5',23,'GF',true,19,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('142b','Geschäftsführung 2b/5',23,'GF',true,20,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('143a','Geschäftsführung 3a/5',24,'GF',true,21,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('143b','Geschäftsführung 3b/5',24,'GF',true,22,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('144a','Geschäftsführung 4a/5',25,'GF',true,23,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('144b','Geschäftsführung 4b/5',25,'GF',true,24,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('145','Geschäftsführung 5/5',26,'GF',true,25,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- Allgemein
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('211','Fachkraft Allgemein 1/3',4,'FK_ALLGM',true,26,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('212a','Fachkraft Allgemein 2a/3',5,'FK_ALLGM',true,27,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('212b','Fachkraft Allgemein 2b/3',5,'FK_ALLGM',true,28,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('213','Fachkraft Allgemein 3/3',6,'FK_ALLGM',true,29,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('221','Spezial-Fachkraft Allgemein 1/4', 7,'SFK_ALLGM',true,30,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('222a','Spezial-Fachkraft Allgemein 2a/4',8,'SFK_ALLGM',true,31,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('222b','Spezial-Fachkraft Allgemein 2b/4',8,'SFK_ALLGM',true,32,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('223a','Spezial-Fachkraft Allgemein 3a/4',9,'SFK_ALLGM',true,33,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('223b','Spezial-Fachkraft Allgemein 3b/4',9,'SFK_ALLGM',true,34,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('224','Spezial-Fachkraft Allgemein 4/4',10,'SFK_ALLGM',true,35,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('231','Spezialist:in Allgemein 1/4',11,'SP_ALLGM',true,36,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('232a','Spezialist:in Allgemein 2a/4',12,'SP_ALLGM',true,37,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('232b','Spezialist:in Allgemein 2b/4',12,'SP_ALLGM',true,38,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('233a','Spezialist:in Allgemein 3a/4',13,'SP_ALLGM',true,39,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('233b','Spezialist:in Allgemein 3b/4',13,'SP_ALLGM',true,40,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('234','Spezialist:in Allgemein 4/4',14,'SP_ALLGM',true,41,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('241','Expert:in Allgemein 1/4',15,'EXP_ALLGM',true,42,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('242a','Expert:in Allgemein 2a/4',16,'EXP_ALLGM',true,43,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('242b','Expert:in Allgemein 2b/4',16,'EXP_ALLGM',true,44,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('243a','Expert:in Allgemein 3a/4',17,'EXP_ALLGM',true,45,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('243b','Expert:in Allgemein 3b/4',17,'EXP_ALLGM',true,46,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('244','Expert:in Allgemein 4/4',18,'EXP_ALLGM',true,47,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('251','Top-Expert:in Allgemein 1/1',19,'TOP_EXP_ALLGM',true,48,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- Technik
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('311','Fachkraft Technik 1/3',4,'FK_TECH',true,49,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('312a','Fachkraft Technik 2a/3',5,'FK_TECH',true,50,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('312b','Fachkraft Technik 2b/3',5,'FK_TECH',true,51,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('313','Fachkraft Technik 3/3',6,'FK_TECH',true,52,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('321','Spezial-Fachkraft Technik 1/4',7,'SFK_TECH',true,53,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('322a','Spezial-Fachkraft Technik 2a/4',8,'SFK_TECH',true,54,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('322b','Spezial-Fachkraft Technik 2b/4',8,'SFK_TECH',true,55,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('323a','Spezial-Fachkraft Technik 3a/4',9,'SFK_TECH',true,56,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('323b','Spezial-Fachkraft Technik 3b/4',9,'SFK_TECH',true,57,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('324','Spezial-Fachkraft Technik 4/4',10,'SFK_TECH',true,58,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('331','Spezialist:in Technik 1/4',11,'SP_TECH',true,59,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('332a','Spezialist:in Technik 2a/4',12,'SP_TECH',true,60,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('332b','Spezialist:in Technik 2b/4',12,'SP_TECH',true,61,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('333a','Spezialist:in Technik 3a/4',13,'SP_TECH',true,62,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('333b','Spezialist:in Technik 3b/4',13,'SP_TECH',true,63,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('334','Spezialist:in Technik 4/4',14,'SP_TECH',true,64,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('341','Expert:in Technik 1/4',15,'EXP_TECH',true,65,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('342a','Expert:in Technik 2a/4',16,'EXP_TECH',true,66,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('342b','Expert:in Technik 2b/4',16,'EXP_TECH',true,67,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('343a','Expert:in Technik 3a/4',17,'EXP_TECH',true,68,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('343b','Expert:in Technik 3b/4',17,'EXP_TECH',true,69,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('344','Expert:in Technik 4/4',18,'EXP_TECH',true,70,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('351','Top-Expert:in Technik 1/1',19,'TOP_EXP_TECH',true,71,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- IT
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('411','Fachkraft IT 1/2',5,'FK_IT',true,72,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('412','Fachkraft IT 2/2',6,'FK_IT',true,73,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('421','Spezial-Fachkraft IT 1/4',7,'SFK_IT',true,74,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('422','Spezial-Fachkraft IT 2/4',8,'SFK_IT',true,75,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('423','Spezial-Fachkraft IT 3/4',9,'SFK_IT',true,76,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('424','Spezial-Fachkraft IT 4/4',10,'SFK_IT',true,77,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('431','Spezialist:in IT 1/4',11,'SP_IT',true,78,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('432a','Spezialist:in IT 2a/4',12,'SP_IT',true,79,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('432b','Spezialist:in IT 2b/4',12,'SP_IT',true,80,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('433a','Spezialist:in IT 3a/4',13,'SP_IT',true,81,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('433b','Spezialist:in IT 3b/4',13,'SP_IT',true,82,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('434','Spezialist:in IT 4/4',14,'SP_IT',true,83,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('441','Expert:in IT 1/4',15,'EXP_IT',true,84,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('442','Expert:in IT 2/4',16,'EXP_IT',true,85,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('443','Expert:in IT 3/4',17,'EXP_IT',true,86,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('444','Expert:in IT 4/4',18,'EXP_IT',true,87,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('451','Top-Expert:in IT 1/1',19,'TOP_EXP_IT',true,88,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- Produktion
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('511','Hilfskraft Produktion 1/4',1,'HK_PROD',true,89,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('512','Hilfskraft Produktion 2/4',2,'HK_PROD',true,90,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('513','Hilfskraft Produktion 3/4',3,'HK_PROD',true,91,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('514','Hilfskraft Produktion 4/4',4,'HK_PROD',true,92,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('521','Fachkraft Produktion 1/2',5,'FK_PROD',true,93,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('522','Fachkraft Produktion 2/2',6,'FK_PROD',true,94,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('531','Spezial-Fachkraft Produktion 1/4',7,'SFK_PROD',true,95,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('532','Spezial-Fachkraft Produktion 2/4',8,'SFK_PROD',true,96,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('533','Spezial-Fachkraft Produktion 3/4',9,'SFK_PROD',true,97,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('534','Spezial-Fachkraft Produktion 4/4',10,'SFK_PROD',true,98,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('541','Spezialist:in Produktion 1/4',11,'SP_PROD',true,99,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('542a','Spezialist:in Produktion 2a/4',12,'SP_PROD',true,100,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('542b','Spezialist:in Produktion 2b/4',12,'SP_PROD',true,101,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('543a','Spezialist:in Produktion 3a/4',13,'SP_PROD',true,102,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('543b','Spezialist:in Produktion 3b/4',13,'SP_PROD',true,103,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('544','Spezialist:in Produktion 4/4',14,'SP_PROD',true,104,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

-- Handwerk, Logistik, ..
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('611','Hilfskraft Handwerk, Instandhaltung + Logistik 1/4',1,'HK_HIL',true,105,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('612','Hilfskraft Handwerk, Instandhaltung + Logistik 2/4',2,'HK_HIL',true,106,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('613','Hilfskraft Handwerk, Instandhaltung + Logistik 3/4',3,'HK_HIL',true,107,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('614','Hilfskraft Handwerk, Instandhaltung + Logistik 4/4',4,'HK_HIL',true,108,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('621','Fachkraft Handwerk, Instandhaltung + Logistik 1/2',5,'FK_HIL',true,109,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('622','Fachkraft Handwerk, Instandhaltung + Logistik 2/2',6,'FK_HIL',true,110,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('631','Spezial-Fachkraft Handwerk, Instandhaltung + Logistik 1/4',7,'SFK_HIL',true,111,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('632','Spezial-Fachkraft Handwerk, Instandhaltung + Logistik 2/4',8,'SFK_HIL',true,112,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('633','Spezial-Fachkraft Handwerk, Instandhaltung + Logistik 3/4',9,'SFK_HIL',true,113,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;
INSERT INTO hr.tbl_lohnguide_modellstelle(modellstelle_kurzbz, bezeichnung, grade, modellfunktion_kurzbz, aktiv, sort, insertvon, insertamum) VALUES('634','Spezial-Fachkraft Handwerk, Instandhaltung + Logistik 4/4',10,'SFK_HIL',true,114,'system',NOW()) ON CONFLICT (modellstelle_kurzbz) DO NOTHING;

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
('FA00','Keine Berücksichtigung',true,'system',NOW()),
('FA01','Administration allgemein',true,'system',NOW()),
('FA02','Dienste Infrastruktur',true,'system',NOW()),
('FA03','Finanzwesen & Controlling',true,'system',NOW()),
('FA04','IT',true,'system',NOW()),
('FA05','Logistik',true,'system',NOW()),
('FA06','Marketing & Digitales Marketing',true,'system',NOW()),
('FA07','Produktion',true,'system',NOW()),
('FA08','Technik',true,'system',NOW()),
('FA09','Verkauf',true,'system',NOW())
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
		