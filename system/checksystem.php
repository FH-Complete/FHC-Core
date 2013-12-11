<?php
/* Copyright (C) 2013 FH Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *
 * Beschreibung:
 * Dieses Skript prueft die gesamte Systemumgebung und sollte nach jedem Update gestartet werden.
 * Geprueft wird: - die Datenbank auf aktualitaet, dabei werden fehlende Attribute angelegt.
 */
require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');

// Datenbank Verbindung
$db = new basis_db();
echo '<html>
<head>
	<title>CheckSystem</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css" />
</head>
<body>';

echo '<H1>Systemcheck!</H1>';
echo '<H2>DB-Updates!</H2>';

// *** Pruefung und hinzufuegen der neuen Attribute und Tabellen
echo '<H2>Pruefe Tabellen und Attribute!</H2>';


// ** Studiengangsverwaltung
// Tabelle Studienordnung
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_studienordnung LIMIT 1;"))
{
	$qry = "CREATE TABLE lehre.tbl_studienordnung
			(
				studienordnung_id integer NOT NULL,
				studiengang_kz integer NOT NULL,
				version varchar(256),
				gueltigvon varchar(16),
				gueltigbis varchar(16),
				bezeichnung varchar(512),
				ects numeric(5,2),
				studiengangbezeichnung varchar(256),
				studiengangbezeichnung_englisch varchar(256),
				studiengangkurzbzlang varchar(8),
				akadgrad_id integer,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE lehre.seq_studienordnung_studienordnung_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT pk_studienordnung PRIMARY KEY (studienordnung_id);
		ALTER TABLE lehre.tbl_studienordnung ALTER COLUMN studienordnung_id SET DEFAULT nextval('lehre.seq_studienordnung_studienordnung_id');

		ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT fk_studienordnung_studiengang FOREIGN KEY (studiengang_kz) REFERENCES public.tbl_studiengang (studiengang_kz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT fk_studienordnung_studiensemester_gueltigvon FOREIGN KEY (gueltigvon) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT fk_studienordnung_studiensemester_gueltigbis FOREIGN KEY (gueltigbis) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT fk_studienordnung_akadgrad FOREIGN KEY (akadgrad_id) REFERENCES lehre.tbl_akadgrad (akadgrad_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON lehre.tbl_studienordnung TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_studienordnung TO vilesci;
		GRANT SELECT, UPDATE ON lehre.seq_studienordnung_studienordnung_id TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienordnung: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_studienordnung: Tabelle hinzugefuegt<br>';
}

// Tabelle Studienordnung_Semester
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_studienordnung_semester LIMIT 1;"))
{
	$qry = "CREATE TABLE lehre.tbl_studienordnung_semester
			(
				studienordnung_semester_id integer NOT NULL,
				studienordnung_id integer NOT NULL,
				studiensemester_kurzbz varchar(16) NOT NULL,
				semester smallint NOT NULL
			);

		CREATE SEQUENCE lehre.seq_studienordnung_semester_studienordnung_semester_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE lehre.tbl_studienordnung_semester ADD CONSTRAINT pk_studienordnung_semester PRIMARY KEY (studienordnung_semester_id);
		ALTER TABLE lehre.tbl_studienordnung_semester ALTER COLUMN studienordnung_semester_id SET DEFAULT nextval('lehre.seq_studienordnung_semester_studienordnung_semester_id');

		ALTER TABLE lehre.tbl_studienordnung_semester ADD CONSTRAINT fk_studienordnung_semester_studienordnung_id FOREIGN KEY (studienordnung_id) REFERENCES lehre.tbl_studienordnung (studienordnung_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienordnung_semester ADD CONSTRAINT fk_studienordnung_semester_studiensemester FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON lehre.tbl_studienordnung_semester TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_studienordnung_semester TO vilesci;
		GRANT SELECT, UPDATE ON lehre.seq_studienordnung_semester_studienordnung_semester_id TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienordnung_semester: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_studienordnung_semester: Tabelle hinzugefuegt<br>';
}

// Tabelle Studienplan
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_studienplan LIMIT 1;"))
{
	$qry = "CREATE TABLE lehre.tbl_studienplan
			(
				studienplan_id integer NOT NULL,
				studienordnung_id integer NOT NULL,
				orgform_kurzbz varchar(3),
				version varchar(256),
				bezeichnung varchar(256),
				regelstudiendauer integer,
				sprache varchar(16),
				aktiv boolean NOT NULL,
				semesterwochen smallint,
				testtool_sprachwahl boolean NOT NULL,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE lehre.seq_studienplan_studienplan_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE lehre.tbl_studienplan ADD CONSTRAINT pk_studienplan PRIMARY KEY (studienplan_id);
		ALTER TABLE lehre.tbl_studienplan ALTER COLUMN studienplan_id SET DEFAULT nextval('lehre.seq_studienplan_studienplan_id');

		ALTER TABLE lehre.tbl_studienplan ADD CONSTRAINT fk_studienplan_orgform_kurzbz FOREIGN KEY (orgform_kurzbz) REFERENCES bis.tbl_orgform (orgform_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienplan ADD CONSTRAINT fk_studienplan_studienordnung FOREIGN KEY (studienordnung_id) REFERENCES lehre.tbl_studienordnung (studienordnung_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienplan ADD CONSTRAINT fk_studienplan_sprache FOREIGN KEY (sprache) REFERENCES public.tbl_sprache (sprache) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON lehre.tbl_studienplan TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_studienplan TO vilesci;
		GRANT SELECT, UPDATE ON lehre.seq_studienplan_studienplan_id TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_studienplan: Tabelle hinzugefuegt<br>';
}

// Tabelle Studienplan_lehrveranstaltung
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_studienplan_lehrveranstaltung LIMIT 1;"))
{
	$qry = "CREATE TABLE lehre.tbl_studienplan_lehrveranstaltung
			(
				studienplan_lehrveranstaltung_id integer NOT NULL,
				studienplan_id integer NOT NULL,
				lehrveranstaltung_id integer NOT NULl,
				semester smallint,
				studienplan_lehrveranstaltung_id_parent integer,
				pflicht boolean NOT NULL,
				koordinator varchar(32),
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE lehre.seq_studienplan_studienplan_lehrveranstaltung_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE lehre.tbl_studienplan_lehrveranstaltung ADD CONSTRAINT pk_studienplan_lehrveranstaltung PRIMARY KEY (studienplan_lehrveranstaltung_id);
		ALTER TABLE lehre.tbl_studienplan_lehrveranstaltung ALTER COLUMN studienplan_lehrveranstaltung_id SET DEFAULT nextval('lehre.seq_studienplan_studienplan_lehrveranstaltung_id');

		ALTER TABLE lehre.tbl_studienplan_lehrveranstaltung ADD CONSTRAINT fk_studienplan_lehrveranstaltung_studienplan_id FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienplan_lehrveranstaltung ADD CONSTRAINT fk_studienplan_lehrveranstaltung_lehrveranstaltung_id FOREIGN KEY (lehrveranstaltung_id) REFERENCES lehre.tbl_lehrveranstaltung (lehrveranstaltung_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienplan_lehrveranstaltung ADD CONSTRAINT fk_studienplan_lehrveranstaltung_koordinator FOREIGN KEY (koordinator) REFERENCES public.tbl_benutzer (uid) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON lehre.tbl_studienplan_lehrveranstaltung TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_studienplan_lehrveranstaltung TO vilesci;
		GRANT SELECT, UPDATE ON lehre.seq_studienplan_studienplan_lehrveranstaltung_id TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan_lehrveranstaltung: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_studienplan_lehrveranstaltung: Tabelle hinzugefuegt<br>';
}

// Tabelle lehrveranstaltung_kompatibel
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_lehrveranstaltung_kompatibel LIMIT 1;"))
{
	$qry = "CREATE TABLE lehre.tbl_lehrveranstaltung_kompatibel
			(
				lehrveranstaltung_id integer NOT NULL,
				lehrveranstaltung_id_kompatibel integer NOT NULL
			);

		ALTER TABLE lehre.tbl_lehrveranstaltung_kompatibel ADD CONSTRAINT pk_lehrveranstaltung_kompatibel PRIMARY KEY (lehrveranstaltung_id, lehrveranstaltung_id_kompatibel);

		ALTER TABLE lehre.tbl_lehrveranstaltung_kompatibel ADD CONSTRAINT fk_lehrveranstaltung_kompatibel_lehrveranstaltung_id FOREIGN KEY (lehrveranstaltung_id) REFERENCES lehre.tbl_lehrveranstaltung (lehrveranstaltung_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_lehrveranstaltung_kompatibel ADD CONSTRAINT fk_lehrveranstaltung_kompatibel_lehrveranstaltung_id_kompatibel FOREIGN KEY (lehrveranstaltung_id_kompatibel) REFERENCES lehre.tbl_lehrveranstaltung (lehrveranstaltung_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON lehre.tbl_lehrveranstaltung_kompatibel TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_lehrveranstaltung_kompatibel TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung_kompatibel: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_lehrveranstaltung_kompatibel: Tabelle hinzugefuegt<br>';
}

// Tabelle lvregeltyp
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_lvregeltyp LIMIT 1;"))
{
	$qry = "CREATE TABLE lehre.tbl_lvregeltyp
			(
				lvregeltyp_kurzbz varchar(32) NOT NULL,
				bezeichnung varchar(256)
			);

		ALTER TABLE lehre.tbl_lvregeltyp ADD CONSTRAINT pk_lvregeltyp PRIMARY KEY (lvregeltyp_kurzbz);

		GRANT SELECT ON lehre.tbl_lvregeltyp TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_lvregeltyp TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lvregeltyp: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_lvregeltyp: Tabelle hinzugefuegt<br>';
}


// Tabelle lvregel
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_lvregel LIMIT 1;"))
{
	$qry = "CREATE TABLE lehre.tbl_lvregel
			(
				lvregel_id integer NOT NULL,
				lvregeltyp_kurzbz varchar(32) NOT NULL,
				operator varchar(1),
				parameter text,
				lvregel_id_parent integer,
				lehrveranstaltung_id integer,
				studienplan_lehrveranstaltung_id integer NOT NULL,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE lehre.seq_lvregel_lvregel_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE lehre.tbl_lvregel ADD CONSTRAINT pk_lvregel PRIMARY KEY (lvregel_id);
		ALTER TABLE lehre.tbl_lvregel ALTER COLUMN lvregel_id SET DEFAULT nextval('lehre.seq_lvregel_lvregel_id');

		ALTER TABLE lehre.tbl_lvregel ADD CONSTRAINT fk_lvregel_lvregeltyp_kurzbz FOREIGN KEY (lvregeltyp_kurzbz) REFERENCES lehre.tbl_lvregeltyp(lvregeltyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_lvregel ADD CONSTRAINT fk_lvregel_lvregel_id_parent FOREIGN KEY (lvregel_id_parent) REFERENCES lehre.tbl_lvregel (lvregel_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_lvregel ADD CONSTRAINT fk_lvregel_lehrveranstaltung_lehrveranstaltung_id FOREIGN KEY (lehrveranstaltung_id) REFERENCES lehre.tbl_lehrveranstaltung (lehrveranstaltung_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_lvregel ADD CONSTRAINT fk_lvregel_studienplan_lehrveranstaltung FOREIGN KEY (studienplan_lehrveranstaltung_id) REFERENCES lehre.tbl_studienplan_lehrveranstaltung (studienplan_lehrveranstaltung_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON lehre.tbl_lvregel TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_lvregel TO vilesci;
		GRANT SELECT, UPDATE ON lehre.seq_lvregel_lvregel_id TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lvregel: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_lvregel: Tabelle hinzugefuegt<br>';
}

// Tabelle tbl_lvangebot
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_lvangebot LIMIT 1;"))
{
	$qry = "CREATE TABLE lehre.tbl_lvangebot
			(
				lvangebot_id integer NOT NULL,
				lehrveranstaltung_id integer NOT NULL,
				studiensemester_kurzbz varchar(16) NOT NULL,
				gruppe_kurzbz varchar(32),
				incomingplaetze smallint,
				gesamtplaetze smallint,
				anmeldefenster_start timestamp,
				anmeldefenster_ende timestamp,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE lehre.seq_lvangebot_lvangebot_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE lehre.tbl_lvangebot ADD CONSTRAINT pk_lvangebot PRIMARY KEY (lvangebot_id);
		ALTER TABLE lehre.tbl_lvangebot ALTER COLUMN lvangebot_id SET DEFAULT nextval('lehre.seq_lvangebot_lvangebot_id');

		ALTER TABLE lehre.tbl_lvangebot ADD CONSTRAINT fk_lvangebot_lehrveranstaltung_lehrveranstaltung_id FOREIGN KEY (lehrveranstaltung_id) REFERENCES lehre.tbl_lehrveranstaltung(lehrveranstaltung_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_lvangebot ADD CONSTRAINT fk_lvangebot_studiensemester_studiensemester_kurzbz FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_lvangebot ADD CONSTRAINT fk_lvangebot_gruppe_gruppe_kurzbz FOREIGN KEY (gruppe_kurzbz) REFERENCES public.tbl_gruppe (gruppe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON lehre.tbl_lvangebot TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_lvangebot TO vilesci;
		GRANT SELECT, UPDATE ON lehre.seq_lvangebot_lvangebot_id TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lvangebot: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_lvangebot: Tabelle hinzugefuegt<br>';
}

// Tabelle tbl_lehrtyp
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_lehrtyp LIMIT 1;"))
{
	$qry = "CREATE TABLE lehre.tbl_lehrtyp
			(
				lehrtyp_kurzbz varchar(32) NOT NULL,
				bezeichnung varchar(256)
			);

		ALTER TABLE lehre.tbl_lehrtyp ADD CONSTRAINT pk_lehrtyp PRIMARY KEY (lehrtyp_kurzbz);

		INSERT INTO lehre.tbl_lehrtyp(lehrtyp_kurzbz, bezeichnung) VALUES('lv','Lehrveranstaltung');
		INSERT INTO lehre.tbl_lehrtyp(lehrtyp_kurzbz, bezeichnung) VALUES('modul','Modul');
		INSERT INTO lehre.tbl_lehrtyp(lehrtyp_kurzbz, bezeichnung) VALUES('lf','Lehrfach');

		GRANT SELECT ON lehre.tbl_lehrtyp TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_lehrtyp TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrtyp: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_lehrtyp: Tabelle hinzugefuegt<br>';
}

// Tabelle tbl_studiengangstyp
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_studiengangstyp LIMIT 1;"))
{
	$qry = "CREATE TABLE public.tbl_studiengangstyp
			(
				typ char(1) NOT NULL,
				bezeichnung varchar(256),
				beschreibung text
			);

		ALTER TABLE public.tbl_studiengangstyp ADD CONSTRAINT pk_studiengangstyp PRIMARY KEY (typ);

		GRANT SELECT ON public.tbl_studiengangstyp TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_studiengangstyp TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_studiengangstyp TO admin;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_studiengangstyp TO wawi;

		INSERT INTO public.tbl_studiengangstyp(typ) SELECT distinct typ FROM public.tbl_studiengang;
		ALTER TABLE public.tbl_studiengang ADD CONSTRAINT fk_studiengang_studiengangstyp FOREIGN KEY (typ) REFERENCES public.tbl_studiengangstyp (typ) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_studiengang ALTER COLUMN typ SET NOT NULL;
	";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_studiengangstyp: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' public.tbl_studiengangstyp: Tabelle hinzugefuegt<br>';
}

// Tabelle tbl_lehrveranstaltung
if(!$result = @$db->db_query("SELECT lehrtyp_kurzbz FROM lehre.tbl_lehrveranstaltung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN lehrtyp_kurzbz varchar(32);
	ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN oe_kurzbz varchar(32);
	ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN raumtyp_kurzbz varchar(16);
	ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN anzahlsemester smallint;
	ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN semesterwochen smallint;

	ALTER TABLE lehre.tbl_lehrveranstaltung ADD CONSTRAINT fk_lehrveranstaltung_lehrtyp FOREIGN KEY (lehrtyp_kurzbz) REFERENCES lehre.tbl_lehrtyp (lehrtyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE lehre.tbl_lehrveranstaltung ADD CONSTRAINT fk_lehrveranstaltung_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE lehre.tbl_lehrveranstaltung ADD CONSTRAINT fk_lehrveranstaltung_raumtyp FOREIGN KEY (raumtyp_kurzbz) REFERENCES public.tbl_raumtyp (raumtyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_lehrveranstaltung: Spalten lehrtyp_kurzbz, oe_kurzbz, raumtyp_kurzbz, anzahlsemester hinzugefügt<br>';
}

// Tabelle tbl_studienplatz
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_studienplatz LIMIT 1;"))
{
	$qry = "CREATE TABLE lehre.tbl_studienplatz
			(
				studienplatz_id integer NOT NULL,
				studiengang_kz integer NOT NULL,
				studiensemester_kurzbz varchar(16) NOT NULL,
				orgform_kurzbz varchar(3),
				ausbildungssemester smallint,
				gpz integer,
				npz integer
			);
		
		CREATE SEQUENCE lehre.seq_studienplatz_studienplatz_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE lehre.tbl_studienplatz ADD CONSTRAINT pk_studienplatz PRIMARY KEY (studienplatz_id);
		ALTER TABLE lehre.tbl_studienplatz ALTER COLUMN studienplatz_id SET DEFAULT nextval('lehre.seq_studienplatz_studienplatz_id');

		ALTER TABLE lehre.tbl_studienplatz ADD CONSTRAINT fk_studienplatz_studiengang_studiengang_kz FOREIGN KEY (studiengang_kz) REFERENCES public.tbl_studiengang(studiengang_kz) ON DELETE CASCADE ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienplatz ADD CONSTRAINT fk_studienplatz_studiensemester_studiensemester_kurzbz FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienplatz ADD CONSTRAINT fk_studienplatz_orgform_orgform_kurzbz FOREIGN KEY (orgform_kurzbz) REFERENCES bis.tbl_orgform (orgform_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON lehre.tbl_studienplatz TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_studienplatz TO vilesci;
		GRANT SELECT, UPDATE ON lehre.seq_studienplatz_studienplatz_id TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplatz: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' lehre.tbl_studienplatz: Tabelle hinzugefügt<br>';
}

// Tabelle tbl_appdaten
if(!$result = @$db->db_query("SELECT 1 FROM system.tbl_appdaten LIMIT 1;"))
{
	$qry = "CREATE TABLE system.tbl_appdaten
			(
				appdaten_id integer NOT NULL,
				uid varchar(32) NOT NULL,
				app varchar(64) NOT NULL,
				appversion varchar(20),
				version smallint,
				bezeichnung varchar(512),
				daten text NOT NULL,
				freigabe boolean NOT NULL DEFAULT false,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);
		
		CREATE SEQUENCE system.seq_appdaten_appdaten_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE system.tbl_appdaten ADD CONSTRAINT pk_appdaten PRIMARY KEY (appdaten_id);
		ALTER TABLE system.tbl_appdaten ALTER COLUMN appdaten_id SET DEFAULT nextval('system.seq_appdaten_appdaten_id');

		ALTER TABLE system.tbl_appdaten ADD CONSTRAINT fk_appdaten_benutzer_uid FOREIGN KEY (uid) REFERENCES public.tbl_benutzer(uid) ON DELETE CASCADE ON UPDATE CASCADE;

		GRANT SELECT ON system.tbl_appdaten TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_appdaten TO vilesci;
		GRANT SELECT, UPDATE ON system.seq_appdaten_appdaten_id TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_appdaten: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' system.tbl_appdaten: Tabelle hinzugefügt<br>';
}

// ** Studienordnung Ende **

// UID in Tabelle benutzerberechtigung von 16 Zeichen auf 32 verlängern
if($result = $db->db_query("SELECT character_maximum_length FROM information_schema.columns WHERE column_name='uid' AND table_name='tbl_benutzerrolle' AND table_schema='system';"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->character_maximum_length==16)
		{
			$qry = "ALTER TABLE system.tbl_benutzerrolle ALTER COLUMN uid TYPE varchar(32);";
			if(!$db->db_query($qry))
				echo '<strong>system.tbl_benutzerrolle: '.$db->db_last_error().'</strong><br>';
			else 
				echo 'system.tbl_benutzerrolle: Spalte uid auf 32 Zeichen verlaengert<br>';
		}
	}
}

// tbl_akte wird nachgereicht und anmerkung hinzufügen
if(!$result = @$db->db_query("SELECT nachgereicht FROM public.tbl_akte LIMIT 1;"))
{
	$qry = "ALTER TABLE public.tbl_akte ADD COLUMN nachgereicht boolean DEFAULT false;
            ALTER TABLE public.tbl_akte ADD COLUMN anmerkung varchar(128)";
			
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_akte: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_akte: Spalte nachgereicht hinzugefuegt!<br>
            public.tbl_akte: Spalte anmerkung hinzugefuegt!<br>';
}

// bis.tbl_zgvdoktor anlegen
if(!$result = @$db->db_query("SELECT zgvdoktor_code FROM bis.tbl_zgvdoktor LIMIT 1"))
{
	$qry = "CREATE TABLE bis.tbl_zgvdoktor
			(
				zgvdoktor_code integer NOT NULL,
				zgvdoktor_bez varchar(64),
				zgvdoktor_kurzbz varchar(16)
			);

		ALTER TABLE bis.tbl_zgvdoktor ADD CONSTRAINT pk_zgvdoktor PRIMARY KEY (zgvdoktor_code);

		GRANT SELECT ON bis.tbl_zgvdoktor TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON bis.tbl_zgvdoktor TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_zgvdoktor: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' bis.tbl_zgvdoktor: Tabelle hinzugefügt<br>';
}

// prestudent zgvdoktor hinzufügen
if(!$result = @$db->db_query("SELECT zgvdoktor_code from public.tbl_prestudent LIMIT 1;"))
{
    $qry = "ALTER TABLE public.tbl_prestudent ADD COLUMN zgvdoktor_code integer; 
            ALTER TABLE public.tbl_prestudent ADD COLUMN zgvdoktorort varchar(64);
            ALTER TABLE public.tbl_prestudent ADD COLUMN zgvdoktordatum date;
            
            
            ALTER TABLE public.tbl_prestudent ADD CONSTRAINT fk_zgvdoktor_code FOREIGN KEY (zgvdoktor_code) REFERENCES bis.tbl_zgvdoktor(zgvdoktor_code) ON DELETE RESTRICT ON UPDATE CASCADE;
            ";
    
    if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudent: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_prestudent: Spalte zgvdoktor_code hinzugefuegt<br>
            public.tbl_prestudent: Spalte zgvdoktorort hinzugefuegt<br>
            public.tbl_prestudent: Spalte zgvdoktordatum hinzugefuegt<br>';
}

// tbl_gruppe neues attribut zutrittssystem
if(!$result = @$db->db_query("SELECT zutrittssystem from public.tbl_gruppe LIMIT 1;"))
{
    $qry = "ALTER TABLE public.tbl_gruppe ADD COLUMN zutrittssystem boolean NOT NULL DEFAULT false;";
    
    if(!$db->db_query($qry))
		echo '<strong>public.tbl_gruppe: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_gruppe: Spalte zutrittssystem hinzugefuegt';
}

// tbl_webservicerecht neue Spalte klasse
if(!$result = @$db->db_query("SELECT klasse from system.tbl_webservicerecht LIMIT 1;"))
{
    $qry = "ALTER TABLE system.tbl_webservicerecht ADD COLUMN klasse varchar(256);";
    
    if(!$db->db_query($qry))
		echo '<strong>system.tbl_webservicerecht: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'system.tbl_webservicerecht: Spalte klasse hinzugefügt';
}

// tbl_note neue Spalte Positiv
if(!$result = @$db->db_query("SELECT positiv from lehre.tbl_note LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_note ADD COLUMN positiv boolean NOT NULL DEFAULT true;
		UPDATE lehre.tbl_note SET positiv=false WHERE note in(0,5,7,9,13,14,15)";
    
    if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_note: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'lehre.tbl_note: Spalte positiv hinzugefügt';
}

// tbl_organisationseinheit neue Spalte lehre
if(!$result = @$db->db_query("SELECT lehre FROM public.tbl_organisationseinheit LIMIT 1;"))
{
    $qry = "ALTER TABLE public.tbl_organisationseinheit ADD COLUMN lehre boolean NOT NULL DEFAULT true;
		UPDATE public.tbl_organisationseinheit SET lehre=false WHERE 
		NOT EXISTS(SELECT 1 FROM public.tbl_studiengang WHERE oe_kurzbz=tbl_organisationseinheit.oe_kurzbz)
		AND
		NOT EXISTS(SELECT 1 FROM public.tbl_fachbereich WHERE oe_kurzbz=tbl_organisationseinheit.oe_kurzbz)";
    
    if(!$db->db_query($qry))
		echo '<strong>public.tbl_organisationseinheit: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_organisationseinheit: Spalte lehre hinzugefügt';
}

// ext_id Spalte tbl_preinteressent
if(!$result = @$db->db_query("SELECT ext_id FROM public.tbl_preinteressent LIMIT 1;"))
{
    $qry = "ALTER TABLE public.tbl_preinteressent ADD COLUMN ext_id bigint;";
    
    if(!$db->db_query($qry))
		echo '<strong>public.tbl_preinteressent: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_preinteressent: Spalte ext_id hinzugefügt';
}

// lvnr Spalte tbl_lehrveranstaltung
if(!$result = @$db->db_query("SELECT lvnr FROM lehre.tbl_lehrveranstaltung LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN lvnr varchar(32);";
    
    if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'lehre.tbl_lehrveranstaltung: Spalte lvnr hinzugefügt';
}

// credit_points Spalte tbl_konto
if(!$result = @$db->db_query("SELECT credit_points FROM public.tbl_konto LIMIT 1;"))
{
    $qry = "ALTER TABLE public.tbl_konto ADD COLUMN credit_points numeric(5,2);
    ALTER TABLE public.tbl_buchungstyp ADD COLUMN credit_points numeric(5,2);";
    
    if(!$db->db_query($qry))
		echo '<strong>public.tbl_konto: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_konto / tbl_buchungstyp: Spalte credit_points hinzugefügt';
}


echo '<br><br><br>';

$tabellen=array(
	"bis.tbl_ausbildung"  => array("ausbildungcode","ausbildungbez","ausbildungbeschreibung"),
	"bis.tbl_berufstaetigkeit"  => array("berufstaetigkeit_code","berufstaetigkeit_bez","berufstaetigkeit_kurzbz"),
	"bis.tbl_beschaeftigungsart1"  => array("ba1code","ba1bez","ba1kurzbz"),
	"bis.tbl_beschaeftigungsart2"  => array("ba2code","ba2bez"),
	"bis.tbl_beschaeftigungsausmass"  => array("beschausmasscode","beschausmassbez","min","max"),
	"bis.tbl_besqual"  => array("besqualcode","besqualbez"),
	"bis.tbl_bisfunktion"  => array("bisverwendung_id","studiengang_kz","sws","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_bisio"  => array("bisio_id","mobilitaetsprogramm_code","nation_code","von","bis","zweck_code","student_uid","updateamum","updatevon","insertamum","insertvon","ext_id","ort","universitaet","lehreinheit_id"),
	"bis.tbl_bisverwendung"  => array("bisverwendung_id","ba1code","ba2code","vertragsstunden","beschausmasscode","verwendung_code","mitarbeiter_uid","hauptberufcode","hauptberuflich","habilitation","beginn","ende","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_bundesland"  => array("bundesland_code","kurzbz","bezeichnung"),
	"bis.tbl_entwicklungsteam"  => array("mitarbeiter_uid","studiengang_kz","besqualcode","beginn","ende","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_gemeinde"  => array("gemeinde_id","plz","name","ortschaftskennziffer","ortschaftsname","bulacode","bulabez","kennziffer"),
	"bis.tbl_hauptberuf"  => array("hauptberufcode","bezeichnung"),
	"bis.tbl_lgartcode"  => array("lgartcode","kurzbz","bezeichnung","beantragung"),
	"bis.tbl_mobilitaetsprogramm"  => array("mobilitaetsprogramm_code","kurzbz","beschreibung","sichtbar","sichtbar_outgoing"),
	"bis.tbl_nation"  => array("nation_code","entwicklungsstand","eu","ewr","kontinent","kurztext","langtext","engltext","sperre"),
	"bis.tbl_orgform"  => array("orgform_kurzbz","code","bezeichnung","rolle"),
	"bis.tbl_verwendung"  => array("verwendung_code","verwendungbez"),
	"bis.tbl_zgv"  => array("zgv_code","zgv_bez","zgv_kurzbz"),
	"bis.tbl_zgvmaster"  => array("zgvmas_code","zgvmas_bez","zgvmas_kurzbz"),
	"bis.tbl_zweck"  => array("zweck_code","kurzbz","bezeichnung"),
    "bis.tbl_zgvdoktor" => array("zgvdoktor_code", "zgvdoktor_bez", "zgvdoktor_kurzbz"),
	"campus.tbl_abgabe"  => array("abgabe_id","abgabedatei","abgabezeit","anmerkung"),
	"campus.tbl_beispiel"  => array("beispiel_id","uebung_id","nummer","bezeichnung","punkte","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_benutzerlvstudiensemester"  => array("uid","studiensemester_kurzbz","lehrveranstaltung_id"),
	"campus.tbl_content"  => array("content_id","template_kurzbz","updatevon","updateamum","insertamum","insertvon","oe_kurzbz","menu_open","aktiv","beschreibung"),
	"campus.tbl_contentchild"  => array("contentchild_id","content_id","child_content_id","updatevon","updateamum","insertamum","insertvon","sort"),
	"campus.tbl_contentgruppe"  => array("content_id","gruppe_kurzbz","insertamum","insertvon"),
	"campus.tbl_contentlog"  => array("contentlog_id","contentsprache_id","uid","start","ende"),
	"campus.tbl_contentsprache"  => array("contentsprache_id","content_id","sprache","version","sichtbar","content","reviewvon","reviewamum","updateamum","updatevon","insertamum","insertvon","titel","gesperrt_uid"),
	"campus.tbl_coodle"  => array("coodle_id","titel","beschreibung","coodle_status_kurzbz","dauer","endedatum","insertamum","insertvon","updateamum","updatevon","ersteller_uid"),
	"campus.tbl_coodle_ressource"  => array("coodle_ressource_id","coodle_id","uid","ort_kurzbz","email","name","zugangscode","insertamum","insertvon","updateamum","updatevon"),
	"campus.tbl_coodle_termin"  => array("coodle_termin_id","coodle_id","datum","uhrzeit","auswahl"),
	"campus.tbl_coodle_ressource_termin"  => array("coodle_ressource_id","coodle_termin_id","insertamum","insertvon"),
	"campus.tbl_coodle_status"  => array("coodle_status_kurzbz","bezeichnung"),
	"campus.tbl_dms"  => array("dms_id","oe_kurzbz","dokument_kurzbz","kategorie_kurzbz"),
	"campus.tbl_dms_kategorie"  => array("kategorie_kurzbz","bezeichnung","beschreibung","parent_kategorie_kurzbz"),
	"campus.tbl_dms_kategorie_gruppe" => array("kategorie_kurzbz","gruppe_kurzbz","insertamum","insertvon"),
	"campus.tbl_dms_version"  => array("dms_id","version","filename","mimetype","name","beschreibung","letzterzugriff","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_erreichbarkeit"  => array("erreichbarkeit_kurzbz","beschreibung","farbe"),
	"campus.tbl_feedback"  => array("feedback_id","betreff","text","datum","uid","lehrveranstaltung_id","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_freebusy"  => array("freebusy_id","uid","freebusytyp_kurzbz","url","aktiv","bezeichnung","insertamum","insertvon","updateamum","updatevon"),
	"campus.tbl_freebusytyp" => array("freebusytyp_kurzbz","bezeichnung","beschreibung","url_vorlage"),
	"campus.tbl_infoscreen"  => array("infoscreen_id","bezeichnung","beschreibung","ipadresse"),
	"campus.tbl_infoscreen_content"  => array("infoscreen_content_id","infoscreen_id","content_id","gueltigvon","gueltigbis","insertamum","insertvon","updateamum","updatevon","refreshzeit"),
	"campus.tbl_legesamtnote"  => array("student_uid","lehreinheit_id","note","benotungsdatum","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_lehre_tools" => array("lehre_tools_id","bezeichnung","kurzbz","basis_url","logo_dms_id"),
	"campus.tbl_lehre_tools_organisationseinheit" => array("lehre_tools_id","oe_kurzbz","aktiv"),
	"campus.tbl_lvgesamtnote"  => array("lehrveranstaltung_id","studiensemester_kurzbz","student_uid","note","mitarbeiter_uid","benotungsdatum","freigabedatum","freigabevon_uid","bemerkung","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_lvinfo"  => array("lehrveranstaltung_id","sprache","titel","lehrziele","lehrinhalte","methodik","voraussetzungen","unterlagen","pruefungsordnung","anmerkung","kurzbeschreibung","genehmigt","aktiv","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_news"  => array("news_id","uid","studiengang_kz","fachbereich_kurzbz","semester","betreff","text","datum","verfasser","updateamum","updatevon","insertamum","insertvon","datum_bis","content_id"),
	"campus.tbl_notenschluessel"  => array("lehreinheit_id","note","punkte"),
	"campus.tbl_notenschluesseluebung"  => array("uebung_id","note","punkte"),
	"campus.tbl_paabgabetyp"  => array("paabgabetyp_kurzbz","bezeichnung"),
	"campus.tbl_paabgabe"  => array("paabgabe_id","projektarbeit_id","paabgabetyp_kurzbz","fixtermin","datum","kurzbz","abgabedatum", "insertvon","insertamum","updatevon","updateamum"),
	"campus.tbl_reservierung"  => array("reservierung_id","ort_kurzbz","studiengang_kz","uid","stunde","datum","titel","beschreibung","semester","verband","gruppe","gruppe_kurzbz","veranstaltung_id","insertamum","insertvon"),
	"campus.tbl_resturlaub"  => array("mitarbeiter_uid","resturlaubstage","mehrarbeitsstunden","updateamum","updatevon","insertamum","insertvon","urlaubstageprojahr"),
	"campus.tbl_studentbeispiel"  => array("student_uid","beispiel_id","vorbereitet","probleme","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_studentuebung"  => array("student_uid","mitarbeiter_uid","abgabe_id","uebung_id","note","mitarbeitspunkte","punkte","anmerkung","benotungsdatum","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_template"  => array("template_kurzbz","bezeichnung","xsd","xslt_xhtml","xslfo_pdf"),
	"campus.tbl_uebung"  => array("uebung_id","gewicht","punkte","angabedatei","freigabevon","freigabebis","abgabe","beispiele","statistik","bezeichnung","positiv","defaultbemerkung","lehreinheit_id","maxstd","maxbsp","liste_id","prozent","nummer","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_veranstaltung"  => array("veranstaltung_id","titel","beschreibung","veranstaltungskategorie_kurzbz","inhalt","start","ende","freigabevon","freigabeamum","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_veranstaltungskategorie"  => array("veranstaltungskategorie_kurzbz","bezeichnung","bild","farbe"),
	"campus.tbl_zeitaufzeichnung"  => array("zeitaufzeichnung_id","uid","aktivitaet_kurzbz","projekt_kurzbz","start","ende","beschreibung","oe_kurzbz_1","oe_kurzbz_2","insertamum","insertvon","updateamum","updatevon","ext_id","service_id","kunde_uid"),
	"campus.tbl_zeitsperre"  => array("zeitsperre_id","zeitsperretyp_kurzbz","mitarbeiter_uid","bezeichnung","vondatum","vonstunde","bisdatum","bisstunde","vertretung_uid","updateamum","updatevon","insertamum","insertvon","erreichbarkeit_kurzbz","freigabeamum","freigabevon"),
	"campus.tbl_zeitsperretyp"  => array("zeitsperretyp_kurzbz","beschreibung","farbe"),
	"campus.tbl_zeitwunsch"  => array("stunde","mitarbeiter_uid","tag","gewicht","updateamum","updatevon","insertamum","insertvon"),
	"fue.tbl_aktivitaet"  => array("aktivitaet_kurzbz","beschreibung"),
	"fue.tbl_projekt"  => array("projekt_kurzbz","nummer","titel","beschreibung","beginn","ende","oe_kurzbz","budget","farbe"),
	"fue.tbl_projektphase"  => array("projektphase_id","projekt_kurzbz","projektphase_fk","bezeichnung","beschreibung","start","ende","budget","insertamum","insertvon","updateamum","updatevon","personentage","farbe"),
	"fue.tbl_projekttask"  => array("projekttask_id","projektphase_id","bezeichnung","beschreibung","aufwand","mantis_id","insertamum","insertvon","updateamum","updatevon","projekttask_fk","erledigt","ende","ressource_id","scrumsprint_id"),
	"fue.tbl_projekt_dokument"  => array("projekt_dokument_id","projektphase_id","projekt_kurzbz","dms_id"),
	"fue.tbl_projekt_ressource"  => array("projekt_ressource_id","projekt_kurzbz","projektphase_id","ressource_id","funktion_kurzbz","beschreibung"),
	"fue.tbl_ressource"  => array("ressource_id","student_uid","mitarbeiter_uid","betriebsmittel_id","firma_id","bezeichnung","beschreibung","insertamum","insertvon","updateamum","updatevon"),
	"fue.tbl_scrumteam" => array("scrumteam_kurzbz","bezeichnung","punkteprosprint","tasksprosprint","gruppe_kurzbz"),
	"fue.tbl_scrumsprint" => array("scrumsprint_id","scrumteam_kurzbz","sprint_kurzbz","sprintstart","sprintende","insertamum","insertvon","updateamum","updatevon"),
	"kommune.tbl_match"  => array("match_id","team_sieger","wettbewerb_kurzbz","team_gefordert","team_forderer","gefordertvon","gefordertamum","matchdatumzeit","matchort","matchbestaetigtvon","matchbestaetigtamum","ergebniss","bestaetigtvon","bestaetigtamum"),
	"kommune.tbl_team"  => array("team_kurzbz","bezeichnung","beschreibung","logo"),
	"kommune.tbl_teambenutzer"  => array("uid","team_kurzbz"),
	"kommune.tbl_wettbewerb"  => array("wettbewerb_kurzbz","regeln","forderungstage","teamgroesse","wbtyp_kurzbz","uid","icon"),
	"kommune.tbl_wettbewerbteam"  => array("team_kurzbz","wettbewerb_kurzbz","rang","punkte"),
	"kommune.tbl_wettbewerbtyp"  => array("wbtyp_kurzbz","bezeichnung","farbe"),
	"lehre.tbl_abschlussbeurteilung"  => array("abschlussbeurteilung_kurzbz","bezeichnung","bezeichnung_english"),
	"lehre.tbl_abschlusspruefung"  => array("abschlusspruefung_id","student_uid","vorsitz","pruefer1","pruefer2","pruefer3","abschlussbeurteilung_kurzbz","akadgrad_id","pruefungstyp_kurzbz","datum","sponsion","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","note"),
	"lehre.tbl_akadgrad"  => array("akadgrad_id","akadgrad_kurzbz","studiengang_kz","titel","geschlecht"),
	"lehre.tbl_betreuerart"  => array("betreuerart_kurzbz","beschreibung"),
	"lehre.tbl_ferien"  => array("bezeichnung","studiengang_kz","vondatum","bisdatum"),
	"lehre.tbl_lehreinheit"  => array("lehreinheit_id","lehrveranstaltung_id","studiensemester_kurzbz","lehrfach_id","lehrform_kurzbz","stundenblockung","wochenrythmus","start_kw","raumtyp","raumtypalternativ","sprache","lehre","anmerkung","unr","lvnr","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehreinheitgruppe"  => array("lehreinheitgruppe_id","lehreinheit_id","studiengang_kz","semester","verband","gruppe","gruppe_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehreinheitmitarbeiter"  => array("lehreinheit_id","mitarbeiter_uid","lehrfunktion_kurzbz","semesterstunden","planstunden","stundensatz","faktor","anmerkung","bismelden","updateamum","updatevon","insertamum","insertvon","ext_id","standort_id"),
	"lehre.tbl_lehrfach"  => array("lehrfach_id","studiengang_kz","fachbereich_kurzbz","kurzbz","bezeichnung","farbe","aktiv","semester","sprache","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehrform"  => array("lehrform_kurzbz","bezeichnung","verplanen"),
	"lehre.tbl_lehrfunktion"  => array("lehrfunktion_kurzbz","beschreibung","standardfaktor","sort"),
	"lehre.tbl_lehrmittel" => array("lehrmittel_kurzbz","beschreibung","ort_kurzbz"),
	"lehre.tbl_lehrtyp" => array("lehrtyp_kurzbz","bezeichnung"),
	"lehre.tbl_lehrveranstaltung"  => array("lehrveranstaltung_id","kurzbz","bezeichnung","lehrform_kurzbz","studiengang_kz","semester","sprache","ects","semesterstunden","anmerkung","lehre","lehreverzeichnis","aktiv","planfaktor","planlektoren","planpersonalkosten","plankostenprolektor","koordinator","sort","zeugnis","projektarbeit","updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung_english","orgform_kurzbz","incoming","lehrtyp_kurzbz","oe_kurzbz","raumtyp_kurzbz","anzahlsemester","semesterwochen","lvnr"),
	"lehre.tbl_lehrveranstaltung_kompatibel" => array("lehrveranstaltung_id","lehrveranstaltung_id_kompatibel"),
	"lehre.tbl_lvangebot" => array("lvangebot_id","lehrveranstaltung_id","studiensemester_kurzbz","gruppe_kurzbz","incomingplaetze","gesamtplaetze","anmeldefenster_start","anmeldefenster_ende","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_lvregel" => array("lvregel_id","lvregeltyp_kurzbz","operator","parameter","lvregel_id_parent","lehrveranstaltung_id","studienplan_lehrveranstaltung_id","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_lvregeltyp" => array("lvregeltyp_kurzbz","bezeichnung"),
	"lehre.tbl_moodle"  => array("lehrveranstaltung_id","lehreinheit_id","moodle_id","mdl_course_id","studiensemester_kurzbz","gruppen","insertamum","insertvon","moodle_version"),
	"lehre.tbl_moodle_version"  => array("moodle_version","bezeichnung","pfad"),
	"lehre.tbl_note"  => array("note","bezeichnung","anmerkung","farbe","positiv"),
	"lehre.tbl_projektarbeit"  => array("projektarbeit_id","projekttyp_kurzbz","titel","lehreinheit_id","student_uid","firma_id","note","punkte","beginn","ende","faktor","freigegeben","gesperrtbis","stundensatz","gesamtstunden","themenbereich","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","titel_english","seitenanzahl","abgabedatum","kontrollschlagwoerter","schlagwoerter","schlagwoerter_en","abstract", "abstract_en", "sprache"),
	"lehre.tbl_projektbetreuer"  => array("person_id","projektarbeit_id","betreuerart_kurzbz","note","faktor","name","punkte","stunden","stundensatz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_projekttyp"  => array("projekttyp_kurzbz","bezeichnung"),
	"lehre.tbl_pruefung"  => array("pruefung_id","lehreinheit_id","student_uid","mitarbeiter_uid","note","pruefungstyp_kurzbz","datum","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"lehre.tbl_pruefungstyp"  => array("pruefungstyp_kurzbz","beschreibung","abschluss"),
	"lehre.tbl_studienordnung"  => array("studienordnung_id","studiengang_kz","version","gueltigvon","gueltigbis","bezeichnung","ects","studiengangbezeichnung","studiengangbezeichnung_englisch","studiengangkurzbzlang","akadgrad_id","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_studienordnung_semester"  => array("studienordnung_semester_id","studienordnung_id","studiensemester_kurzbz","semester"),
	"lehre.tbl_studienplan" => array("studienplan_id","studienordnung_id","orgform_kurzbz","version","regelstudiendauer","sprache","aktiv","bezeichnung","insertamum","insertvon","updateamum","updatevon","semesterwochen","testtool_sprachwahl"),
	"lehre.tbl_studienplan_lehrveranstaltung" => array("studienplan_lehrveranstaltung_id","studienplan_id","lehrveranstaltung_id","semester","studienplan_lehrveranstaltung_id_parent","pflicht","koordinator","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_studienplatz" => array("studienplatz_id","studiengang_kz","studiensemester_kurzbz","orgform_kurzbz","ausbildungssemester","gpz","npz"),
	"lehre.tbl_stunde"  => array("stunde","beginn","ende"),
	"lehre.tbl_stundenplan"  => array("stundenplan_id","unr","mitarbeiter_uid","datum","stunde","ort_kurzbz","gruppe_kurzbz","titel","anmerkung","lehreinheit_id","studiengang_kz","semester","verband","gruppe","fix","updateamum","updatevon","insertamum","insertvon"),
	"lehre.tbl_stundenplandev"  => array("stundenplandev_id","lehreinheit_id","unr","studiengang_kz","semester","verband","gruppe","gruppe_kurzbz","mitarbeiter_uid","ort_kurzbz","datum","stunde","titel","anmerkung","fix","updateamum","updatevon","insertamum","insertvon"),
	"lehre.tbl_zeitfenster"  => array("wochentag","stunde","ort_kurzbz","studiengang_kz","gewicht"),
	"lehre.tbl_zeugnis"  => array("zeugnis_id","student_uid","zeugnis","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_zeugnisnote"  => array("lehrveranstaltung_id","student_uid","studiensemester_kurzbz","note","uebernahmedatum","benotungsdatum","bemerkung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_adresse"  => array("adresse_id","person_id","name","strasse","plz","ort","gemeinde","nation","typ","heimatadresse","zustelladresse","firma_id","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_akte"  => array("akte_id","person_id","dokument_kurzbz","uid","inhalt","mimetype","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id","dms_id","nachgereicht","anmerkung"),
	"public.tbl_ampel"  => array("ampel_id","kurzbz","beschreibung","benutzer_select","deadline","vorlaufzeit","verfallszeit","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_ampel_benutzer_bestaetigt"  => array("ampel_benutzer_bestaetigt_id","ampel_id","uid","insertamum","insertvon"),
	"public.tbl_aufmerksamdurch"  => array("aufmerksamdurch_kurzbz","beschreibung","ext_id"),
	"public.tbl_aufnahmeschluessel"  => array("aufnahmeschluessel"),
	"public.tbl_bankverbindung"  => array("bankverbindung_id","person_id","name","anschrift","bic","blz","iban","kontonr","typ","verrechnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_benutzer"  => array("uid","person_id","aktiv","alias","insertamum","insertvon","updateamum","updatevon","ext_id","updateaktivvon","updateaktivam"),
	"public.tbl_benutzerfunktion"  => array("benutzerfunktion_id","fachbereich_kurzbz","uid","oe_kurzbz","funktion_kurzbz","semester", "datum_von","datum_bis", "updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung"),
	"public.tbl_benutzergruppe"  => array("uid","gruppe_kurzbz","studiensemester_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_buchungstyp"  => array("buchungstyp_kurzbz","beschreibung","standardbetrag","standardtext","aktiv","credit_points"),
	"public.tbl_dokument"  => array("dokument_kurzbz","bezeichnung","ext_id"),
	"public.tbl_dokumentprestudent"  => array("dokument_kurzbz","prestudent_id","mitarbeiter_uid","datum","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_dokumentstudiengang"  => array("dokument_kurzbz","studiengang_kz","ext_id"),
	"public.tbl_erhalter"  => array("erhalter_kz","kurzbz","bezeichnung","dvr","logo","zvr"),
	"public.tbl_fachbereich"  => array("fachbereich_kurzbz","bezeichnung","farbe","studiengang_kz","aktiv","ext_id","oe_kurzbz"),
	"public.tbl_firma"  => array("firma_id","name","anmerkung","firmentyp_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id","schule","finanzamt","steuernummer","gesperrt","aktiv"),
	"public.tbl_firma_mobilitaetsprogramm" => array("firma_id","mobilitaetsprogramm_code"),
	"public.tbl_firma_organisationseinheit"  => array("firma_organisationseinheit_id","firma_id","oe_kurzbz","bezeichnung","kundennummer","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_firmentyp"  => array("firmentyp_kurzbz","beschreibung"),
	"public.tbl_firmatag"  => array("firma_id","tag","insertamum","insertvon"),
	"public.tbl_fotostatus"  => array("fotostatus_kurzbz","beschreibung"),
	"public.tbl_funktion"  => array("funktion_kurzbz","beschreibung","aktiv","fachbereich","semester"),
	"public.tbl_geschaeftsjahr"  => array("geschaeftsjahr_kurzbz","start","ende","bezeichnung"),
	"public.tbl_gruppe"  => array("gruppe_kurzbz","studiengang_kz","semester","bezeichnung","beschreibung","sichtbar","lehre","aktiv","sort","mailgrp","generiert","updateamum","updatevon","insertamum","insertvon","ext_id","orgform_kurzbz","gid","content_visible","gesperrt","zutrittssystem"),
	"public.tbl_kontakt"  => array("kontakt_id","person_id","kontakttyp","anmerkung","kontakt","zustellung","updateamum","updatevon","insertamum","insertvon","ext_id","standort_id"),
	"public.tbl_kontaktmedium"  => array("kontaktmedium_kurzbz","beschreibung"),
	"public.tbl_kontakttyp"  => array("kontakttyp","beschreibung"),
	"public.tbl_konto"  => array("buchungsnr","person_id","studiengang_kz","studiensemester_kurzbz","buchungstyp_kurzbz","buchungsnr_verweis","betrag","buchungsdatum","buchungstext","mahnspanne","updateamum","updatevon","insertamum","insertvon","ext_id","credit_points"),
	"public.tbl_lehrverband"  => array("studiengang_kz","semester","verband","gruppe","aktiv","bezeichnung","ext_id","orgform_kurzbz","gid"),
	"public.tbl_log"  => array("log_id","executetime","mitarbeiter_uid","beschreibung","sql","sqlundo"),
	"public.tbl_mitarbeiter"  => array("mitarbeiter_uid","personalnummer","telefonklappe","kurzbz","lektor","fixangestellt","bismelden","stundensatz","ausbildungcode","ort_kurzbz","standort_id","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_notiz"  => array("notiz_id","titel","text","verfasser_uid","bearbeiter_uid","start","ende","erledigt","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_notizzuordnung"  => array("notizzuordnung_id","notiz_id","projekt_kurzbz","projektphase_id","projekttask_id","uid","person_id","prestudent_id","bestellung_id"),
	"public.tbl_ort"  => array("ort_kurzbz","bezeichnung","planbezeichnung","max_person","lehre","reservieren","aktiv","lageplan","dislozierung","kosten","ausstattung","updateamum","updatevon","insertamum","insertvon","ext_id","stockwerk","standort_id","telefonklappe","content_id"),
	"public.tbl_ortraumtyp"  => array("ort_kurzbz","hierarchie","raumtyp_kurzbz"),
	"public.tbl_organisationseinheit" => array("oe_kurzbz", "oe_parent_kurzbz", "bezeichnung","organisationseinheittyp_kurzbz", "aktiv","mailverteiler","freigabegrenze","kurzzeichen","lehre"),
	"public.tbl_organisationseinheittyp" => array("organisationseinheittyp_kurzbz", "bezeichnung", "beschreibung"),
	"public.tbl_person"  => array("person_id","staatsbuergerschaft","geburtsnation","sprache","anrede","titelpost","titelpre","nachname","vorname","vornamen","gebdatum","gebort","gebzeit","foto","anmerkung","homepage","svnr","ersatzkennzeichen","familienstand","geschlecht","anzahlkinder","aktiv","insertamum","insertvon","updateamum","updatevon","ext_id","bundesland_code","kompetenzen","kurzbeschreibung","zugangscode", "foto_sperre"),
	"public.tbl_person_fotostatus"  => array("person_fotostatus_id","person_id","fotostatus_kurzbz","datum","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_personfunktionstandort"  => array("personfunktionstandort_id","funktion_kurzbz","person_id","standort_id","position","anrede"),
	"public.tbl_preincoming"  => array("preincoming_id","person_id","mobilitaetsprogramm_code","zweck_code","firma_id","universitaet","aktiv","bachelorthesis","masterthesis","von","bis","uebernommen","insertamum","insertvon","updateamum","updatevon","anmerkung","zgv","zgv_ort","zgv_datum","zgv_name","zgvmaster","zgvmaster_datum","zgvmaster_ort","zgvmaster_name","program_name","bachelor","master","jahre","person_id_emergency","person_id_coordinator_dep","person_id_coordinator_int","code","deutschkurs1","deutschkurs2","research_area","deutschkurs3"),
	"public.tbl_preincoming_lehrveranstaltung"  => array("preincoming_id","lehrveranstaltung_id","insertamum","insertvon"),
	"public.tbl_preinteressent"  => array("preinteressent_id","person_id","studiensemester_kurzbz","firma_id","erfassungsdatum","einverstaendnis","absagedatum","anmerkung","maturajahr","infozusendung","aufmerksamdurch_kurzbz","kontaktmedium_kurzbz","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_preinteressentstudiengang"  => array("studiengang_kz","preinteressent_id","freigabedatum","uebernahmedatum","prioritaet","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_preoutgoing" => array("preoutgoing_id","uid","dauer_von","dauer_bis","ansprechperson","bachelorarbeit","masterarbeit","betreuer","sprachkurs","intensivsprachkurs","sprachkurs_von","sprachkurs_bis","praktikum","praktikum_von","praktikum_bis","behinderungszuschuss","studienbeihilfe","anmerkung_student", "anmerkung_admin", "studienrichtung_gastuniversitaet", "insertamum","insertvon","updateamum","updatevon","projektarbeittitel"),
	"public.tbl_preoutgoing_firma" => array("preoutgoing_firma_id","preoutgoing_id","mobilitaetsprogramm_code","firma_id","name","auswahl"),
	"public.tbl_preoutgoing_lehrveranstaltung" => array("preoutgoing_lehrveranstaltung_id","preoutgoing_id","bezeichnung","ects","endversion","insertamum","insertvon","updateamum","updatevon","wochenstunden","unitcode"),
	"public.tbl_preoutgoing_preoutgoing_status" => array("status_id","preoutgoing_status_kurzbz","preoutgoing_id","datum","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_preoutgoing_status" => array("preoutgoing_status_kurzbz","bezeichnung"),
	"public.tbl_prestudent"  => array("prestudent_id","aufmerksamdurch_kurzbz","person_id","studiengang_kz","berufstaetigkeit_code","ausbildungcode","zgv_code","zgvort","zgvdatum","zgvmas_code","zgvmaort","zgvmadatum","aufnahmeschluessel","facheinschlberuf","reihungstest_id","anmeldungreihungstest","reihungstestangetreten","rt_gesamtpunkte","rt_punkte1","rt_punkte2","bismelden","anmerkung","dual","insertamum","insertvon","updateamum","updatevon","ext_id","ausstellungsstaat","rt_punkte3", "zgvdoktor_code", "zgvdoktorort", "zgvdoktordatum"),
	"public.tbl_prestudentstatus"  => array("prestudent_id","status_kurzbz","studiensemester_kurzbz","ausbildungssemester","datum","orgform_kurzbz","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_raumtyp"  => array("raumtyp_kurzbz","beschreibung"),
	"public.tbl_reihungstest"  => array("reihungstest_id","studiengang_kz","ort_kurzbz","anmerkung","datum","uhrzeit","updateamum","updatevon","insertamum","insertvon","ext_id","freigeschaltet"),
	"public.tbl_status"  => array("status_kurzbz","beschreibung","anmerkung","ext_id"),
	"public.tbl_semesterwochen"  => array("semester","studiengang_kz","wochen"),
	"public.tbl_service" => array("service_id", "bezeichnung","beschreibung","ext_id","oe_kurzbz","content_id"),
	"public.tbl_sprache"  => array("sprache","locale","flagge","index","content","bezeichnung"),
	"public.tbl_standort"  => array("standort_id","adresse_id","kurzbz","bezeichnung","insertvon","insertamum","updatevon","updateamum","ext_id", "firma_id"),
	"public.tbl_statistik"  => array("statistik_kurzbz","bezeichnung","url","r","gruppe","sql","php","content_id","insertamum","insertvon","updateamum","updatevon","berechtigung_kurzbz"),
	"public.tbl_student"  => array("student_uid","matrikelnr","prestudent_id","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studentlehrverband"  => array("student_uid","studiensemester_kurzbz","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studiengang"  => array("studiengang_kz","kurzbz","kurzbzlang","typ","bezeichnung","english","farbe","email","telefon","max_semester","max_verband","max_gruppe","erhalter_kz","bescheid","bescheidbgbl1","bescheidbgbl2","bescheidgz","bescheidvom","orgform_kurzbz","titelbescheidvom","aktiv","ext_id","zusatzinfo_html","moodle","sprache","testtool_sprachwahl","studienplaetze","oe_kurzbz","lgartcode","mischform","projektarbeit_note_anzeige"),
	"public.tbl_studiengangstyp" => array("typ","bezeichnung","beschreibung"),
	"public.tbl_studiensemester"  => array("studiensemester_kurzbz","bezeichnung","start","ende","ext_id"),
	"public.tbl_tag"  => array("tag"),
	"public.tbl_variable"  => array("name","uid","wert"),
	"public.tbl_vorlage"  => array("vorlage_kurzbz","bezeichnung","anmerkung","mimetype"),
	"public.tbl_vorlagestudiengang"  => array("vorlagestudiengang_id","vorlage_kurzbz","studiengang_kz","version","text","oe_kurzbz"),
	"testtool.tbl_ablauf"  => array("ablauf_id","gebiet_id","studiengang_kz","reihung","gewicht","semester", "insertamum","insertvon","updateamum", "updatevon"),
	"testtool.tbl_antwort"  => array("antwort_id","pruefling_id","vorschlag_id"),
	"testtool.tbl_frage"  => array("frage_id","kategorie_kurzbz","gebiet_id","level","nummer","demo","insertamum","insertvon","updateamum","updatevon"),
	"testtool.tbl_gebiet"  => array("gebiet_id","kurzbz","bezeichnung","beschreibung","zeit","multipleresponse","kategorien","maxfragen","zufallfrage","zufallvorschlag","levelgleichverteilung","maxpunkte","insertamum", "insertvon", "updateamum", "updatevon", "level_start","level_sprung_auf","level_sprung_ab","antwortenprozeile"),
	"testtool.tbl_kategorie"  => array("kategorie_kurzbz","gebiet_id"),
	"testtool.tbl_kriterien"  => array("gebiet_id","kategorie_kurzbz","punkte","typ"),
	"testtool.tbl_pruefling"  => array("pruefling_id","prestudent_id","studiengang_kz","idnachweis","registriert","semester"),
	"testtool.tbl_vorschlag"  => array("vorschlag_id","frage_id","nummer","punkte","insertamum","insertvon","updateamum","updatevon"),
	"testtool.tbl_pruefling_frage"  => array("prueflingfrage_id","pruefling_id","frage_id","nummer","begintime","endtime"),
	"testtool.tbl_frage_sprache"  => array("frage_id","sprache","text","bild","audio","insertamum","insertvon","updateamum","updatevon"),
	"testtool.tbl_vorschlag_sprache"  => array("vorschlag_id","sprache","text","bild","audio","insertamum","insertvon","updateamum","updatevon"),
	"system.tbl_appdaten" => array("appdaten_id","uid","app","appversion","version","bezeichnung","daten","freigabe","insertamum","insertvon","updateamum","updatevon"),
	"system.tbl_cronjob"  => array("cronjob_id","server_kurzbz","titel","beschreibung","file","last_execute","aktiv","running","jahr","monat","tag","wochentag","stunde","minute","standalone","reihenfolge","updateamum", "updatevon","insertamum","insertvon","variablen"),
	"system.tbl_benutzerrolle"  => array("benutzerberechtigung_id","rolle_kurzbz","berechtigung_kurzbz","uid","funktion_kurzbz","oe_kurzbz","art","studiensemester_kurzbz","start","ende","negativ","updateamum", "updatevon","insertamum","insertvon","kostenstelle_id"),
	"system.tbl_berechtigung"  => array("berechtigung_kurzbz","beschreibung"),
	"system.tbl_rolle"  => array("rolle_kurzbz","beschreibung"),
	"system.tbl_rolleberechtigung"  => array("berechtigung_kurzbz","rolle_kurzbz","art"),
	"system.tbl_webservicelog"  => array("webservicelog_id","webservicetyp_kurzbz","request_id","beschreibung","request_data","execute_time","execute_user"),
	"system.tbl_webservicerecht" => array("webservicerecht_id","berechtigung_kurzbz","methode","attribut","insertamum","insertvon","updateamum","updatevon","klasse"),
	"system.tbl_webservicetyp"  => array("webservicetyp_kurzbz","beschreibung"),
	"system.tbl_server"  => array("server_kurzbz","beschreibung"),
	"wawi.tbl_betriebsmittelperson"  => array("betriebsmittelperson_id","betriebsmittel_id","person_id", "anmerkung", "kaution", "ausgegebenam", "retouram","insertamum", "insertvon","updateamum", "updatevon","ext_id","uid"),
	"wawi.tbl_betriebsmittel"  => array("betriebsmittel_id","betriebsmitteltyp","oe_kurzbz", "ort_kurzbz", "beschreibung", "nummer", "hersteller","seriennummer", "bestellung_id","bestelldetail_id", "afa","verwendung","anmerkung","reservieren","updateamum","updatevon","insertamum","insertvon","ext_id","inventarnummer","leasing_bis","inventuramum","inventurvon","anschaffungsdatum","anschaffungswert","hoehe","breite","tiefe","nummer2"),
	"wawi.tbl_betriebsmittel_betriebsmittelstatus"  => array("betriebsmittelbetriebsmittelstatus_id","betriebsmittel_id","betriebsmittelstatus_kurzbz", "datum", "updateamum", "updatevon", "insertamum", "insertvon","anmerkung"),
	"wawi.tbl_betriebsmittelstatus"  => array("betriebsmittelstatus_kurzbz","beschreibung"),
	"wawi.tbl_betriebsmitteltyp"  => array("betriebsmitteltyp","beschreibung","anzahl","kaution","typ_code","mastershapename"),
	"wawi.tbl_budget"  => array("geschaeftsjahr_kurzbz","kostenstelle_id","budget"),
	"wawi.tbl_zahlungstyp"  => array("zahlungstyp_kurzbz","bezeichnung"),
	"wawi.tbl_konto"  => array("konto_id","kontonr","beschreibung","kurzbz","aktiv","insertamum","insertvon","updateamum","updatevon"),
	"wawi.tbl_konto_kostenstelle"  => array("konto_id","kostenstelle_id","insertamum","insertvon"),
	"wawi.tbl_kostenstelle"  => array("kostenstelle_id","oe_kurzbz","bezeichnung","kurzbz","aktiv","insertamum","insertvon","updateamum","updatevon","ext_id","kostenstelle_nr","deaktiviertvon","deaktiviertamum"),
	"wawi.tbl_bestellungtag"  => array("tag","bestellung_id","insertamum","insertvon"),
	"wawi.tbl_bestelldetailtag"  => array("tag","bestelldetail_id","insertamum","insertvon"),
	"wawi.tbl_projekt_bestellung"  => array("projekt_kurzbz","bestellung_id","anteil"),
	"wawi.tbl_bestellung"  => array("bestellung_id","besteller_uid","kostenstelle_id","konto_id","firma_id","lieferadresse","rechnungsadresse","freigegeben","bestell_nr","titel","bemerkung","liefertermin","updateamum","updatevon","insertamum","insertvon","ext_id","zahlungstyp_kurzbz"),
	"wawi.tbl_bestelldetail"  => array("bestelldetail_id","bestellung_id","position","menge","verpackungseinheit","beschreibung","artikelnummer","preisprove","mwst","erhalten","sort","text","updateamum","updatevon","insertamum","insertvon"),
	"wawi.tbl_bestellung_bestellstatus"  => array("bestellung_bestellstatus_id","bestellung_id","bestellstatus_kurzbz","uid","oe_kurzbz","datum","insertamum","insertvon","updateamum","updatevon"),
	"wawi.tbl_bestellstatus"  => array("bestellstatus_kurzbz","beschreibung"),
	"wawi.tbl_rechnungstyp"  => array("rechnungstyp_kurzbz","beschreibung","berechtigung_kurzbz"),
	"wawi.tbl_rechnung"  => array("rechnung_id","bestellung_id","buchungsdatum","rechnungsnr","rechnungsdatum","transfer_datum","buchungstext","insertamum","insertvon","updateamum","updatevon","rechnungstyp_kurzbz","freigegeben","freigegebenvon","freigegebenamum"),
	"wawi.tbl_rechnungsbetrag"  => array("rechnungsbetrag_id","rechnung_id","mwst","betrag","bezeichnung","ext_id"),
	"wawi.tbl_aufteilung"  => array("aufteilung_id","bestellung_id","oe_kurzbz","anteil","insertamum","insertvon","updateamum","updatevon"),
	"wawi.tbl_aufteilung_default"  => array("aufteilung_id","kostenstelle_id","oe_kurzbz","anteil","insertamum","insertvon","updateamum","updatevon"),
);

$tabs=array_keys($tabellen);
//print_r($tabs);
$i=0;
foreach ($tabellen AS $attribute)
{
	$sql_attr='';
	foreach($attribute AS $attr)
		$sql_attr.=$attr.',';
	$sql_attr=substr($sql_attr, 0, -1);

	if (!@$db->db_query('SELECT '.$sql_attr.' FROM '.$tabs[$i].' LIMIT 1;'))
		echo '<BR><strong>'.$tabs[$i].': '.$db->db_last_error().' </strong><BR>';
	else
		echo $tabs[$i].': OK - ';
	flush();
	$i++;
}

echo '<H2>Gegenpruefung!</H2>';
$sql_query="SELECT schemaname,tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema' AND schemaname != 'sync';";
if (!$result=@$db->db_query($sql_query))
		echo '<BR><strong>'.$db->db_last_error().' </strong><BR>';
	else
		while ($row=$db->db_fetch_object($result))
		{
			$fulltablename=$row->schemaname.'.'.$row->tablename;
			if (!isset($tabellen[$fulltablename]))
				echo 'Tabelle '.$fulltablename.' existiert in der DB, aber nicht in diesem Skript!<BR>';
			else
				if (!$result_fields=@$db->db_query("SELECT * FROM $fulltablename LIMIT 1;"))
					echo '<BR><strong>'.$db->db_last_error().' </strong><BR>';
				else
					for ($i=0; $i<$db->db_num_fields($result_fields); $i++)
					{
						$found=false;
						$fieldnameDB=$db->db_field_name($result_fields,$i);
						foreach ($tabellen[$fulltablename] AS $fieldnameARRAY)
							if ($fieldnameDB==$fieldnameARRAY)
							{
								$found=true;
								break;
							}
						if (!$found)
							echo 'Attribut '.$fulltablename.'.<strong>'.$fieldnameDB.'</strong> existiert in der DB, aber nicht in diesem Skript!<BR>';
					}
		}

// ******** Berechtigungen Prüfen ************/
echo '<h2>Berechtigungen pruefen</h2>';
$berechtigung_kurzbz=0;
$beschreibung=1;
$berechtigungen = array(
	array('admin','Super User Rechte'),
	array('assistenz','Assistenz'),
	array('basis/addon','Addons verwalten'),
	array('basis/ampel','Ampeln Administrieren'),
	array('basis/ampeluebersicht','Ampel Übersicht für Leiter'),
	array('basis/berechtigung','Berechtigungsverwaltung'),
	array('basis/betriebsmittel','Betriebsmittel'),
	array('basis/cms','CMS Administration'),
	array('basis/cms_review','CMS Review Berechtigung (nur für admin Reviewer! Normale Reviewer bekommen Benutzerfunktion review)'),
	array('basis/cms_sperrfreigabe','Berechtigung zum Freigeben von gesperrtem Content'),
	array('basis/cronjob','Cronjobverwaltung'),
	array('basis/dms','DMS Download'),
	array('basis/fhausweis','Verwaltungstools für FH Ausweis – Kartentausch, Bildpruefung, Druck'),
	array('basis/firma','Firmenverwaltung'),
	array('basis/firma:begrenzt','Firmenverwaltung'),
	array('basis/infoscreen','Infoscreenverwaltung'),
	array('basis/moodle','basis/moodle'),
	array('basis/news','Newsverwaltung'),
	array('basis/notiz','Notizen'),
	array('basis/organisationseinheit','Organisationseinheiten Verwalten'),
	array('basis/ort','Raum-/Ortverwaltung'),
	array('basis/person','Personen Zusammenlegen, Stg-Wiederholer anlegen, etc'),
	array('basis/service','Services Administrieren (SLAs)'),
	array('basis/statistik','Statistiken Administrieren'),
	array('basis/studiengang','Studiengangsverwaltung'),
	array('basis/testtool','Administrationseite, Gebiete löschen/zurücksetzen'),
	array('basis/variable','Variablenverwaltung'),
	array('inout/incoming','Incomingverwaltung'),
	array('inout/outgoing','Outgoingverwaltung'),
	array('inout/uebersicht','Verbandsanzeige fuer Incoming/Outgoing im FAS'),
	array('lehre','Berechtigung fuer CIS-Seite'),
	array('lehre/abgabetool','Projektabgabetool, Studentenansicht'),
	array('lehre/abgabetool:download','Download von Projektarbeitsabgaben'),
	array('lehre/freifach','Freifachverwaltung'),
	array('lehre/lehrfach','Lehrfachverwaltung'),
	array('lehre/lehrfach:begrenzt','Lehrfachverwaltung - nur aktiv aenderbar, nur aktive LF werden angezeigt'),
	array('lehre/lehrveranstaltung','Lehrveranstaltungsverwaltung'),
	array('lehre/lehrveranstaltung:begrenzt','nur die Felder Lehre, Sort, Zeugnis, BA/DA, FBK und LVInfo dürfen geändert werden (eventuelle Aufteilung in einzelne Berechtigungen??)'),
	array('lehre/lvplan','Tempus'),
	array('lehre/reihungstest','Reihungstestverwaltung'),
	array('lehre/reservierung','erweiterte Reservierung inkl. Lektorauswahl, Stg, Sem und Gruppe'),
	array('lehre/reservierung:begrenzt','normale Raumreservierung im CIS'),
	array('lehre/studienordnung','Studienordnung'),
	array('lehre/vorrueckung','Lehreinheitenvorrückung'),
	array('lv-plan','Stundenplan'),
	array('mitarbeiter','FAS Mitarbeitermodul'),
	array('mitarbeiter/bankdaten','Bankdaten für Mitarbeiter und Studierende anzeigen'),
	array('mitarbeiter/stammdaten','Stammdaten der Mitarbeiter'),
	array('mitarbeiter/urlaube','Mit diesem Recht werden im CIS die Urlaube von allen Mitarbeiter sichtbar'),
	array('mitarbeiter/zeitsperre','Zeitsperren- und Urlaubsverwaltung'),
	array('news','News eintragen'),
	array('planner','Planner Verwaltung'),
	array('preinteressent','Verwaltung der Preinteressenten'),
	array('raumres','Raumreservierung'),
	array('soap/lv','Recht für LV Webservice'),
	array('soap/lvplan','Recht für LV-Plan Webservice'),
	array('soap/mitarbeiter','Recht für Mitarbeiter-Webservice'),
	array('soap/ort','Recht für Ort Webservice'),
	array('soap/student','Recht für Student Webservice'),
	array('soap/studienordnung','Recht für Studienordnung Webservice'),
	array('student/bankdaten','Bankdaten des Studenten'),
	array('student/dokumente','Wenn SUID dann dürfen Dokumente auch wieder entfernt werden'),
	array('student/stammdaten','Stammdaten der Studenten'),
	array('student/vorrueckung','Studentenvorrückung'),
	array('system/developer','Anzeige zusätzlicher Developerinfos'),
	array('system/loginasuser','Berechtigung zum Einloggen als anderer User'),
	array('user','Normale User ohne besonere Rechte'),
	array('veranstaltung','Berechtigungen fuer Veranstaltungen wie Jahresplan'),
	array('wawi/berichte','Alle Berichte anzeigen'),
	array('wawi/bestellung','Bestellungen verwalten'),
	array('wawi/bestellung_advanced','Bestellungen editieren nach dem Abschicken'),
	array('wawi/budget','Budgeteingabe'),
	array('wawi/delete_advanced','Loeschen von freigegebenen Bestellungen'),
	array('wawi/firma','Firmenverwaltung abgespeckt'),
	array('wawi/freigabe','Bestellungen freigeben, entweder oe_kurzbz oder kostenstelle_id muss gesetzt sein'),
	array('wawi/freigabe_advanced','Berechtigung zum Freigeben von ALLEN Bestellungen'),
	array('wawi/inventar','Inventar Administration'),
	array('wawi/inventar:begrenzt','Inventarverwaltung'),
	array('wawi/konto','Kontoverwaltung'),
	array('wawi/kostenstelle','Kostenstellenverwaltung'),
	array('wawi/rechnung','Rechnungen verwalten'),
	array('wawi/rechnung_freigeben','Rechnungen Freigeben (bei Gutschriften)'),
	array('wawi/rechnung_transfer','Rechnungen - Eintragen des TransferDatums'),
	array('wawi/storno','Bestellung stornieren')
);

foreach($berechtigungen as $row)
{
	$qry = "SELECT * FROM system.tbl_berechtigung
			WHERE berechtigung_kurzbz=".$db->db_add_param($row[$berechtigung_kurzbz]);

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)==0)
		{
			// Nicht vorhanden -> anlegen
			$qry_insert="INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES(".
				$db->db_add_param($row[$berechtigung_kurzbz]).','.
				$db->db_add_param($row[$beschreibung]).');';

			if($db->db_query($qry_insert))
				echo '<br>'.$row[$berechtigung_kurzbz].'/'.$row[$beschreibung].' hinzugefügt';
			else
				echo '<br><span class="error">Fehler: '.$row[$berechtigung_kurzbz].'/'.$row[$beschreibung].' hinzufügen nicht möglich</span>';
		}
	}
}
// ******** Pruefen ob die Webservice Berechtigungen alle gesetzt sind **********

echo '<h2>Webservice Berechtigungen pruefen</h2>';

// berechtigung_kurzbz,methode,klasse
$berechtigung_kurzbz=0;
$methode=1;
$klasse=2;
$webservicerecht = array(
	array('soap/studienordnung','load_lva_oe','lehrveranstaltung'),
	array('soap/studienordnung','load','lehrveranstaltung'),
	array('soap/studienordnung','deleteStudienplanLehrveranstaltung','studienplan'),
	array('soap/studienordnung','containsLehrveranstaltung','studienplan'),
	array('soap/studienordnung','loadLehrveranstaltungStudienplanByLvId','studienplan'),
	array('soap/studienordnung','saveStudienplanLehrveranstaltung','studienplan'),
	array('soap/studienordnung','loadStudienordnung','studienordnung'),
	array('soap/studienordnung','delete','lvregel'),
	array('soap/studienordnung','save','lvregel'),
	array('soap/studienordnung','load','lvregel'),
	array('soap/studienordnung','loadLVRegelTypen','lvregel'),
	array('soap/studienordnung','load_lva','lehrveranstaltung'),
	array('soap/studienordnung','getAll','lehrtyp'),
	array('soap/studienordnung','getAll','organisationseinheit'),
	array('soap/studienordnung','getLVRegelTree','lvregel'),
	array('soap/studienordnung','save','studienplan'),
	array('soap/studienordnung','save','studienordnung'),
	array('soap/studienordnung','loadStudienplanSTO','studienplan'),
	array('soap/studienordnung','loadStudienordnungSTG','studienordnung'),
	array('soap/studienordnung','loadStudienplan','studienplan'),
	array('soap/studienordnung','saveSemesterZuordnung','studienordnung'),
	array('soap/studienordnung','deleteSemesterZuordnung','studienordnung')
);

foreach($webservicerecht as $row)
{
	$qry = "SELECT * FROM system.tbl_webservicerecht 
			WHERE berechtigung_kurzbz=".$db->db_add_param($row[$berechtigung_kurzbz])."
			AND methode=".$db->db_add_param($row[$methode])."
			AND klasse=".$db->db_add_param($row[$klasse]);

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)==0)
		{
			// Nicht vorhanden -> anlegen
			$qry_insert="INSERT INTO system.tbl_webservicerecht (berechtigung_kurzbz, methode, insertamum, insertvon, klasse) VALUES(".
				$db->db_add_param($row[$berechtigung_kurzbz]).','.
				$db->db_add_param($row[$methode]).','.
				"now(),'checksystem',".
				$db->db_add_param($row[$klasse]).');';

			if($db->db_query($qry_insert))
				echo '<br>'.$row[$berechtigung_kurzbz].'/'.$row[$methode].'->'.$row[$klasse].' hinzugefügt';
			else
				echo '<br><span class="error">Fehler: '.$row[$berechtigung_kurzbz].'/'.$row[$methode].'->'.$row[$klasse].' hinzufügen nicht möglich</span>';
		}
	}
}

echo '</body></html>';
