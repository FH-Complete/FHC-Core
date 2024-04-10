<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add index to system.tbl_log
if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_dienstverhaeltnis' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
		CREATE SCHEMA IF NOT EXISTS hr;
		COMMENT ON SCHEMA hr IS E'Personalverwaltung';

		ALTER SCHEMA hr OWNER TO fhcomplete;

		CREATE EXTENSION IF NOT EXISTS pgcrypto;

		CREATE TABLE hr.tbl_dienstverhaeltnis
		(
			dienstverhaeltnis_id serial NOT NULL,
			mitarbeiter_uid character varying(32),
			vertragsart_kurzbz varchar(32),
			oe_kurzbz character varying(32),
			von date,
			bis date,
			insertamum timestamp,
			insertvon varchar(32),
			updateamum timestamp,
			updatevon varchar(32),
			CONSTRAINT tbl_dienstverhaeltnis_pk PRIMARY KEY (dienstverhaeltnis_id)
		);

		CREATE TABLE hr.tbl_vertragsbestandteil
		(
			vertragsbestandteil_id serial NOT NULL,
			dienstverhaeltnis_id integer NOT NULL,
			vertragsbestandteiltyp_kurzbz varchar(32) NOT NULL,
			von date,
			bis date,
			insertamum timestamp,
			insertvon varchar(32),
			updateamum timestamp,
			updatevon varchar(32),
			CONSTRAINT tbl_vertragsbestandteil_pk PRIMARY KEY (vertragsbestandteil_id)
		);

		CREATE TABLE hr.tbl_gehaltsbestandteil
		(
			gehaltsbestandteil_id serial NOT NULL,
			dienstverhaeltnis_id integer NOT NULL,
			vertragsbestandteil_id integer,
			gehaltstyp_kurzbz character varying(32) NOT NULL,
			von date,
			bis date,
			anmerkung text,
			grundbetrag bytea,
			betrag_valorisiert bytea,
			valorisierungssperre date,
			insertamum timestamp,
			insertvon varchar(32),
			updateamum timestamp,
			updatevon varchar(32),
			valorisierung boolean NOT NULL,
			auszahlungen smallint NOT NULL DEFAULT 14,
			CONSTRAINT tbl_gehaltsbestandteil_pk PRIMARY KEY (gehaltsbestandteil_id)
		);

		COMMENT ON COLUMN hr.tbl_gehaltsbestandteil.grundbetrag IS E'verschluesselt - Ursprüngliches Gehalt laut Vertrag';
		COMMENT ON COLUMN hr.tbl_gehaltsbestandteil.betrag_valorisiert IS E'verschluesselt - Valorisierter aktueller Betrag';
		COMMENT ON COLUMN hr.tbl_gehaltsbestandteil.auszahlungen IS E'Wie oft im Jahr wird das Gehalt bezahlt. zb 14x oder nur 12x';
		COMMENT ON COLUMN hr.tbl_gehaltsbestandteil.valorisierung IS E'Wird dieser Bestandteil mitvalorisiert';
		COMMENT ON COLUMN hr.tbl_gehaltsbestandteil.valorisierungssperre IS E'Bis zu welchem Datum ist dieser Bestandteil von der Valorisierung ausgenommen';

		ALTER TABLE hr.tbl_gehaltsbestandteil ADD CONSTRAINT tbl_dienstverhaeltnis_fk FOREIGN KEY (dienstverhaeltnis_id)
		REFERENCES hr.tbl_dienstverhaeltnis (dienstverhaeltnis_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE hr.tbl_vertragsbestandteil ADD CONSTRAINT tbl_dienstverhaeltnis_fk FOREIGN KEY (dienstverhaeltnis_id)
		REFERENCES hr.tbl_dienstverhaeltnis (dienstverhaeltnis_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_gehaltshistorie
		(
			gehaltshistorie_id serial NOT NULL,
			datum date,
			betrag bytea,
			gehaltsbestandteil_id integer,
			mitarbeiter_uid character varying(32),
			CONSTRAINT tbl_gehaltshistorie_pk PRIMARY KEY (gehaltshistorie_id)
		);

		COMMENT ON COLUMN hr.tbl_gehaltshistorie.betrag IS E'verschluesselt';

		ALTER TABLE hr.tbl_gehaltshistorie ADD CONSTRAINT tbl_gehaltshistorie_gehaltsbestandteil_id_fk FOREIGN KEY (gehaltsbestandteil_id)
		REFERENCES hr.tbl_gehaltsbestandteil (gehaltsbestandteil_id) MATCH FULL
		ON UPDATE CASCADE ON DELETE SET NULL;

		ALTER TABLE hr.tbl_gehaltshistorie ADD CONSTRAINT tbl_gehaltshistorie_mitarbeiter_uid_fk FOREIGN KEY (mitarbeiter_uid)
		REFERENCES public.tbl_mitarbeiter (mitarbeiter_uid) MATCH FULL
		ON DELETE SET NULL ON UPDATE CASCADE;

		ALTER TABLE hr.tbl_dienstverhaeltnis ADD CONSTRAINT tbl_mitarbeiter_fk FOREIGN KEY (mitarbeiter_uid)
		REFERENCES public.tbl_mitarbeiter (mitarbeiter_uid) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_gehaltstyp
		(
			gehaltstyp_kurzbz character varying(32) NOT NULL,
			bezeichnung varchar(256),
			valorisierung boolean NOT NULL DEFAULT true,
			sort smallint,
			aktiv boolean NOT NULL DEFAULT true,
			CONSTRAINT gehaltstypen_pk PRIMARY KEY (gehaltstyp_kurzbz)
		);

		COMMENT ON TABLE hr.tbl_gehaltstyp IS E'Key-Table of Salary Types';

		ALTER TABLE hr.tbl_gehaltsbestandteil ADD CONSTRAINT tbl_gehaltstyp_fk FOREIGN KEY (gehaltstyp_kurzbz)
		REFERENCES hr.tbl_gehaltstyp (gehaltstyp_kurzbz) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_vertragsart
		(
			vertragsart_kurzbz varchar(32) NOT NULL,
			bezeichnung varchar(256),
			anmerkung text,
			dienstverhaeltnis boolean,
			vertragsart_kurzbz_parent character varying(32),
			aktiv boolean NOT NULL DEFAULT true,
			sort smallint,
			CONSTRAINT tbl_vertragsar_pk PRIMARY KEY (vertragsart_kurzbz)
		);

		COMMENT ON TABLE hr.tbl_vertragsart IS E'Key-Table of Contract Types';
		COMMENT ON COLUMN hr.tbl_vertragsart.dienstverhaeltnis IS E'Kann dieser Typ direkt beim Dienstverhaeltnis zugeordnet werden';

		ALTER TABLE hr.tbl_dienstverhaeltnis ADD CONSTRAINT tbl_vertragsart_fk FOREIGN KEY (vertragsart_kurzbz)
		REFERENCES hr.tbl_vertragsart (vertragsart_kurzbz) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_sachaufwand
		(
			sachaufwand_id serial NOT NULL,
			mitarbeiter_uid character varying(32),
			sachaufwandtyp_kurzbz character varying(32),
			dienstverhaeltnis_id integer,
			beginn date,
			ende date,
			anmerkung text,
			insertamum timestamp,
			insertvon character varying(32),
			updateamum timestamp,
			updatevon character varying(32),
			CONSTRAINT tbl_sachaufwand_pk PRIMARY KEY (sachaufwand_id)
		);

		ALTER TABLE hr.tbl_sachaufwand ADD CONSTRAINT tbl_mitarbeiter_fk FOREIGN KEY (mitarbeiter_uid)
		REFERENCES public.tbl_mitarbeiter (mitarbeiter_uid) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_sachaufwandtyp
		(
			sachaufwandtyp_kurzbz character varying(32) NOT NULL,
			bezeichnung character varying(256),
			sort smallint,
			aktiv boolean NOT NULL DEFAULT true,
			CONSTRAINT tbl_sachaufwandtyp_pk PRIMARY KEY (sachaufwandtyp_kurzbz)
		);

		ALTER TABLE hr.tbl_sachaufwand ADD CONSTRAINT tbl_sachaufwandtyp_fk FOREIGN KEY (sachaufwandtyp_kurzbz)
		REFERENCES hr.tbl_sachaufwandtyp (sachaufwandtyp_kurzbz) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE hr.tbl_sachaufwand ADD CONSTRAINT tbl_dienstverhaeltnis_fk FOREIGN KEY (dienstverhaeltnis_id)
		REFERENCES hr.tbl_dienstverhaeltnis (dienstverhaeltnis_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE public.tbl_funktion ADD COLUMN hrrelevant boolean NOT NULL DEFAULT false;
		ALTER TABLE public.tbl_funktion ADD COLUMN vertragsrelevant boolean NOT NULL DEFAULT false;

		ALTER TABLE hr.tbl_gehaltsbestandteil ADD CONSTRAINT tbl_vertragsbestandteil_fk FOREIGN KEY (vertragsbestandteil_id)
		REFERENCES hr.tbl_vertragsbestandteil (vertragsbestandteil_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_vertragsbestandteiltyp
		(
			vertragsbestandteiltyp_kurzbz varchar(32) NOT NULL,
			bezeichnung varchar(256),
			ueberlappend boolean NOT NULL,
			CONSTRAINT tbl_vertragsbestandteiltyp_pk PRIMARY KEY (vertragsbestandteiltyp_kurzbz)
		);

		COMMENT ON TABLE hr.tbl_vertragsbestandteiltyp IS E'Type of Contract-Part';
		COMMENT ON COLUMN hr.tbl_vertragsbestandteiltyp.ueberlappend IS E'Dürfen sich Einträge von diesem Typ zeitlich überlappen';

		ALTER TABLE hr.tbl_vertragsbestandteil ADD CONSTRAINT tbl_vertragsbestandteiltyp_fk FOREIGN KEY (vertragsbestandteiltyp_kurzbz)
		REFERENCES hr.tbl_vertragsbestandteiltyp (vertragsbestandteiltyp_kurzbz) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_vertragsbestandteil_funktion
		(
			vertragsbestandteil_id integer NOT NULL,
			benutzerfunktion_id integer,
			CONSTRAINT tbl_vertragsbestandteil_funktion_pk PRIMARY KEY (vertragsbestandteil_id)
		);

		ALTER TABLE hr.tbl_vertragsbestandteil_funktion ADD CONSTRAINT tbl_benutzerfunktion_fk FOREIGN KEY (benutzerfunktion_id)
		REFERENCES public.tbl_benutzerfunktion (benutzerfunktion_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_vertragsbestandteil_stunden
		(
			vertragsbestandteil_id integer NOT NULL,
			wochenstunden numeric(4,2),
			teilzeittyp_kurzbz character varying(32),
			CONSTRAINT tbl_vertragsbestandteil_stunden_pk PRIMARY KEY (vertragsbestandteil_id)
		);

		COMMENT ON COLUMN hr.tbl_vertragsbestandteil_stunden.teilzeittyp_kurzbz IS E'Altersteilzeit, Elternteilzeit';

		CREATE TABLE hr.tbl_teilzeittyp
		(
			teilzeittyp_kurzbz character varying(32) NOT NULL,
			bezeichnung varchar(256) NOT NULL,
			aktiv boolean NOT NULL DEFAULT true,
			CONSTRAINT tbl_teilzeittyp_pk PRIMARY KEY (teilzeittyp_kurzbz)
		);

		ALTER TABLE hr.tbl_vertragsbestandteil_stunden ADD CONSTRAINT tbl_teilzeittyp_fk FOREIGN KEY (teilzeittyp_kurzbz)
		REFERENCES hr.tbl_teilzeittyp (teilzeittyp_kurzbz) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_vertragsbestandteil_freitext
		(
			vertragsbestandteil_id integer NOT NULL,
			freitexttyp_kurzbz varchar(32) NOT NULL,
			titel varchar(256),
			anmerkung text,
			CONSTRAINT tbl_vertragsbestandteil_freitext_pk PRIMARY KEY (vertragsbestandteil_id)
		);

		CREATE TABLE hr.tbl_vertragsbestandteil_freitexttyp
		(
			freitexttyp_kurzbz varchar(32) NOT NULL,
			bezeichnung varchar(128),
			ueberlappend boolean NOT NULL DEFAULT true,
			kuendigungsrelevant boolean NOT NULL DEFAULT false,
			CONSTRAINT tbl_freitexttyp_pk PRIMARY KEY (freitexttyp_kurzbz)
		);
		COMMENT ON TABLE hr.tbl_vertragsbestandteil_freitexttyp IS E'Key-Table FreeTextType (Sideletter, Ersatzarbeitskraft, AllIn, Befristung, Überstundenpauschale)';
		COMMENT ON COLUMN hr.tbl_vertragsbestandteil_freitexttyp.ueberlappend IS E'Dürfen sich Einträge von diesem Typ zeitlich überlappen';
		COMMENT ON COLUMN hr.tbl_vertragsbestandteil_freitexttyp.kuendigungsrelevant IS E'Ist dieser Freitext bei einer Kündigung zu berücksichtigen';

		ALTER TABLE hr.tbl_vertragsbestandteil_freitext ADD CONSTRAINT tbl_vertragsbestandteil_freitexttyp_fk FOREIGN KEY (freitexttyp_kurzbz)
		REFERENCES hr.tbl_vertragsbestandteil_freitexttyp (freitexttyp_kurzbz) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE hr.tbl_vertragsbestandteil_freitext ADD CONSTRAINT tbl_vertragsbestandteil_fk FOREIGN KEY (vertragsbestandteil_id)
		REFERENCES hr.tbl_vertragsbestandteil (vertragsbestandteil_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE hr.tbl_vertragsbestandteil_stunden ADD CONSTRAINT tbl_vertragsbestandteil_fk FOREIGN KEY (vertragsbestandteil_id)
		REFERENCES hr.tbl_vertragsbestandteil (vertragsbestandteil_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE hr.tbl_vertragsbestandteil_funktion ADD CONSTRAINT tbl_vertragsbestandteil_fk FOREIGN KEY (vertragsbestandteil_id)
		REFERENCES hr.tbl_vertragsbestandteil (vertragsbestandteil_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_vertragsbestandteil_zeitaufzeichnung
		(
			vertragsbestandteil_id integer NOT NULL,
			zeitaufzeichnung boolean NOT NULL,
			azgrelevant boolean NOT NULL,
			homeoffice boolean NOT NULL,
			CONSTRAINT tbl_vertragsbestandteil_zeitaufzeichnung_pk PRIMARY KEY (vertragsbestandteil_id)
		);

		CREATE TABLE hr.tbl_vertragsbestandteil_urlaubsanspruch
		(
			vertragsbestandteil_id integer NOT NULL,
			tage smallint,
			CONSTRAINT tbl_vertragsbestandteil_urlaubsanspruch_pk PRIMARY KEY (vertragsbestandteil_id)
		);

		CREATE TABLE hr.tbl_vertragsbestandteil_kuendigungsfrist
		(
			vertragsbestandteil_id integer NOT NULL,
			arbeitgeber_frist smallint,
			arbeitnehmer_frist smallint,
			CONSTRAINT tbl_vertragsbestandteil_kuendigungsfrist_pk PRIMARY KEY (vertragsbestandteil_id)
		);

		ALTER TABLE hr.tbl_vertragsbestandteil_zeitaufzeichnung ADD CONSTRAINT tbl_vertragsbestandteil_fk FOREIGN KEY (vertragsbestandteil_id)
		REFERENCES hr.tbl_vertragsbestandteil (vertragsbestandteil_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE hr.tbl_vertragsbestandteil_urlaubsanspruch ADD CONSTRAINT tbl_vertragsbestandteil_fk FOREIGN KEY (vertragsbestandteil_id)
		REFERENCES hr.tbl_vertragsbestandteil (vertragsbestandteil_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE hr.tbl_vertragsbestandteil_kuendigungsfrist ADD CONSTRAINT tbl_vertragsbestandteil_fk FOREIGN KEY (vertragsbestandteil_id)
		REFERENCES hr.tbl_vertragsbestandteil (vertragsbestandteil_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_vertragsbestandteil_karenz
		(
			vertragsbestandteil_id integer NOT NULL,
			karenztyp_kurzbz varchar(32),
			geplanter_geburtstermin date,
			tatsaechlicher_geburtstermin date,
			CONSTRAINT tbl_vertragsbestandteil_karenz_pk PRIMARY KEY (vertragsbestandteil_id)
		);

		ALTER TABLE hr.tbl_vertragsbestandteil_karenz ADD CONSTRAINT tbl_vertragsbestandteil_fk FOREIGN KEY (vertragsbestandteil_id)
		REFERENCES hr.tbl_vertragsbestandteil (vertragsbestandteil_id) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE TABLE hr.tbl_karenztyp
		(
			karenztyp_kurzbz varchar(32) NOT NULL,
			bezeichnung character varying(256) NOT NULL,
			CONSTRAINT tbl_karenztyp_pk PRIMARY KEY (karenztyp_kurzbz)
		);
		COMMENT ON TABLE hr.tbl_karenztyp IS E'Key-Table Elternkarenz, Bildungskarenz, Papamonat';

		ALTER TABLE hr.tbl_vertragsbestandteil_karenz ADD CONSTRAINT tbl_karenztyp_fk FOREIGN KEY (karenztyp_kurzbz)
		REFERENCES hr.tbl_karenztyp (karenztyp_kurzbz) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE hr.tbl_dienstverhaeltnis ADD CONSTRAINT tbl_organisationseinheit_fk FOREIGN KEY (oe_kurzbz)
		REFERENCES public.tbl_organisationseinheit (oe_kurzbz) MATCH FULL
		ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE hr.tbl_vertragsart ADD CONSTRAINT fk_vertragsart_vertragsartparent FOREIGN KEY (vertragsart_kurzbz_parent)
		REFERENCES hr.tbl_vertragsart (vertragsart_kurzbz) MATCH SIMPLE
		ON DELETE NO ACTION ON UPDATE NO ACTION;

		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_sachaufwand TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_sachaufwandtyp TO vilesci;

		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_dienstverhaeltnis TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsart TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsbestandteil TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsbestandteiltyp TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsbestandteil_funktion TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsbestandteil_stunden TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsbestandteil_freitext TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsbestandteil_freitexttyp TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsbestandteil_zeitaufzeichnung TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsbestandteil_urlaubsanspruch TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsbestandteil_kuendigungsfrist TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_vertragsbestandteil_karenz TO vilesci;
		GRANT SELECT ON hr.tbl_vertragsbestandteil_karenz TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_karenztyp TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_teilzeittyp TO vilesci;
		GRANT SELECT ON hr.tbl_vertragsbestandteil_freitext TO web;
		GRANT SELECT ON hr.tbl_vertragsbestandteil_freitexttyp TO web;

		GRANT SELECT ON hr.tbl_dienstverhaeltnis TO web;
		GRANT SELECT ON hr.tbl_vertragsart TO web;
		GRANT SELECT ON hr.tbl_vertragsbestandteil TO web;
		GRANT SELECT ON hr.tbl_vertragsbestandteiltyp TO web;
		GRANT SELECT ON hr.tbl_vertragsbestandteil_stunden TO web;
		GRANT SELECT ON hr.tbl_vertragsbestandteil_zeitaufzeichnung TO web;

		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_gehaltsbestandteil TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_gehaltshistorie TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_gehaltstyp TO vilesci;

		GRANT USAGE ON SCHEMA hr TO vilesci;
		GRANT USAGE ON SCHEMA hr TO web;

		INSERT INTO hr.tbl_karenztyp(karenztyp_kurzbz, bezeichnung) VALUES('elternkarenz','Elternkarenz');
		INSERT INTO hr.tbl_karenztyp(karenztyp_kurzbz, bezeichnung) VALUES('bildungskarenz','Bildungskarenz');
		INSERT INTO hr.tbl_karenztyp(karenztyp_kurzbz, bezeichnung) VALUES('papamonat','Papamonat');

		INSERT INTO hr.tbl_teilzeittyp(teilzeittyp_kurzbz, bezeichnung) VALUES('altersteilzeit','Altersteilzeit');
		INSERT INTO hr.tbl_teilzeittyp(teilzeittyp_kurzbz, bezeichnung) VALUES('elternteilzeit','Elternteilzeit');
		INSERT INTO hr.tbl_teilzeittyp(teilzeittyp_kurzbz, bezeichnung) VALUES('wiedereingliederungteilzeit','Wiedereingliederungsteilzeit');

		INSERT INTO hr.tbl_vertragsart(vertragsart_kurzbz, bezeichnung, anmerkung, dienstverhaeltnis, vertragsart_kurzbz_parent, aktiv, sort) VALUES('echterdv','Echter DV','Echter Dienstvertrag', true, null, true, 100);
		INSERT INTO hr.tbl_vertragsart(vertragsart_kurzbz, bezeichnung, anmerkung, dienstverhaeltnis, vertragsart_kurzbz_parent, aktiv, sort) VALUES('studentischehilfskr','Studentische Hilfskraft','Studentische Hilfskraft', true, 'echterdv', true, 101);

		INSERT INTO hr.tbl_vertragsart(vertragsart_kurzbz, bezeichnung, anmerkung, dienstverhaeltnis, vertragsart_kurzbz_parent, aktiv, sort) VALUES('freierdv','Freier DV','Freier Dienstvertrag', false, null, true, 200);
		INSERT INTO hr.tbl_vertragsart(vertragsart_kurzbz, bezeichnung, anmerkung, dienstverhaeltnis, vertragsart_kurzbz_parent, aktiv, sort) VALUES('externerlehrender','externer Lehrender','Externer Lehrender Freier DV', true, 'freierdv', true, 201);
		INSERT INTO hr.tbl_vertragsart(vertragsart_kurzbz, bezeichnung, anmerkung, dienstverhaeltnis, vertragsart_kurzbz_parent, aktiv, sort) VALUES('gastlektor','Gastlektor','Gastlektor', true, 'freierdv', true, 202);
		INSERT INTO hr.tbl_vertragsart(vertragsart_kurzbz, bezeichnung, anmerkung, dienstverhaeltnis, vertragsart_kurzbz_parent, aktiv, sort) VALUES('echterfreier','Echter Freier DV','Echter Freier DV', true, 'freierdv', true, 203);

		INSERT INTO hr.tbl_vertragsart(vertragsart_kurzbz, bezeichnung, anmerkung, dienstverhaeltnis, vertragsart_kurzbz_parent, aktiv, sort) VALUES('werkvertrag','Werkvertrag','Werkvertrag', true, null, true, 300);
		INSERT INTO hr.tbl_vertragsart(vertragsart_kurzbz, bezeichnung, anmerkung, dienstverhaeltnis, vertragsart_kurzbz_parent, aktiv, sort) VALUES('ueberlassungsvertrag','Überlassungsvertrag','Überlassungsvertrag', true, 'werkvertrag', true, 300);

		INSERT INTO hr.tbl_gehaltstyp(gehaltstyp_kurzbz, bezeichnung, valorisierung, sort, aktiv) VALUES('basisgehalt','Basisgehalt', true, 1, true);
		INSERT INTO hr.tbl_gehaltstyp(gehaltstyp_kurzbz, bezeichnung, valorisierung, sort, aktiv) VALUES('grundgehalt','Grundgehalt', true, 2, true);
		INSERT INTO hr.tbl_gehaltstyp(gehaltstyp_kurzbz, bezeichnung, valorisierung, sort, aktiv) VALUES('zulage','Zulage', true, 3, true);
		INSERT INTO hr.tbl_gehaltstyp(gehaltstyp_kurzbz, bezeichnung, valorisierung, sort, aktiv) VALUES('praemie','Prämie', false, 4, true);
		INSERT INTO hr.tbl_gehaltstyp(gehaltstyp_kurzbz, bezeichnung, valorisierung, sort, aktiv) VALUES('lohnausgleichatz','Lohnausgleich ATZ', false, 5, true);
		INSERT INTO hr.tbl_gehaltstyp(gehaltstyp_kurzbz, bezeichnung, valorisierung, sort, aktiv) VALUES('zusatzvereinbarung','Zusatzvereinbarung',false, 6, true);

		INSERT INTO hr.tbl_vertragsbestandteil_freitexttyp(freitexttyp_kurzbz, bezeichnung, ueberlappend, kuendigungsrelevant) VALUES('allin','All-In', false, false);
		INSERT INTO hr.tbl_vertragsbestandteil_freitexttyp(freitexttyp_kurzbz, bezeichnung, ueberlappend, kuendigungsrelevant) VALUES('ersatzarbeitskraft','Ersatzarbeitskraft', false, true);
		INSERT INTO hr.tbl_vertragsbestandteil_freitexttyp(freitexttyp_kurzbz, bezeichnung, ueberlappend, kuendigungsrelevant) VALUES('zusatzvereinbarung','Zusatzvereinbarung', true, false);
		INSERT INTO hr.tbl_vertragsbestandteil_freitexttyp(freitexttyp_kurzbz, bezeichnung, ueberlappend, kuendigungsrelevant) VALUES('befristung','Befristung', false, true);
		INSERT INTO hr.tbl_vertragsbestandteil_freitexttyp(freitexttyp_kurzbz, bezeichnung, ueberlappend, kuendigungsrelevant) VALUES('sonstiges','Sonstiges', true, false);

		INSERT INTO hr.tbl_vertragsbestandteiltyp(vertragsbestandteiltyp_kurzbz, bezeichnung, ueberlappend) VALUES('stunden', 'Stunden', false);
		INSERT INTO hr.tbl_vertragsbestandteiltyp(vertragsbestandteiltyp_kurzbz, bezeichnung, ueberlappend) VALUES('funktion', 'Funktion', true);
		INSERT INTO hr.tbl_vertragsbestandteiltyp(vertragsbestandteiltyp_kurzbz, bezeichnung, ueberlappend) VALUES('freitext', 'Freitext', true);
		INSERT INTO hr.tbl_vertragsbestandteiltyp(vertragsbestandteiltyp_kurzbz, bezeichnung, ueberlappend) VALUES('zeitaufzeichnung', 'Zeitaufzeichnung', false);
		INSERT INTO hr.tbl_vertragsbestandteiltyp(vertragsbestandteiltyp_kurzbz, bezeichnung, ueberlappend) VALUES('urlaubsanspruch', 'Urlaubsanspruch', false);
		INSERT INTO hr.tbl_vertragsbestandteiltyp(vertragsbestandteiltyp_kurzbz, bezeichnung, ueberlappend) VALUES('kuendigungsfrist', 'Kündigungsfrist', false);
		INSERT INTO hr.tbl_vertragsbestandteiltyp(vertragsbestandteiltyp_kurzbz, bezeichnung, ueberlappend) VALUES('karenz', 'Karenz', false);

		INSERT INTO hr.tbl_sachaufwandtyp(sachaufwandtyp_kurzbz, bezeichnung, sort, aktiv) VALUES('jobticket', 'Jobticket', 1, true);
		INSERT INTO hr.tbl_sachaufwandtyp(sachaufwandtyp_kurzbz, bezeichnung, sort, aktiv) VALUES('klimaticket', 'Klimaticket', 1, true);
		INSERT INTO hr.tbl_sachaufwandtyp(sachaufwandtyp_kurzbz, bezeichnung, sort, aktiv) VALUES('pendlerpauschale', 'Pendlerpauschale', 1, true);

		CREATE INDEX idx_tbl_dienstverhaeltnis_mitarbeiter_uid ON hr.tbl_dienstverhaeltnis USING btree (mitarbeiter_uid);
		CREATE INDEX idx_tbl_vertragsbestandteil_dienstverhaeltnis_id ON hr.tbl_vertragsbestandteil USING btree (dienstverhaeltnis_id);
		CREATE INDEX idx_tbl_gehaltshistorie_gehaltsbestandteil_id ON hr.tbl_gehaltshistorie USING btree (gehaltsbestandteil_id);
		CREATE INDEX idx_tbl_gehaltsbestandteil_dienstverhaeltnis_id ON hr.tbl_gehaltsbestandteil USING btree (dienstverhaeltnis_id);

		COMMENT ON TABLE hr.tbl_dienstverhaeltnis IS E'Dienstverhaeltnisse von Mitarbeitern';
		COMMENT ON TABLE hr.tbl_gehaltshistorie IS E'Historie monatlich abgerechneter Gehaelter';
		COMMENT ON TABLE hr.tbl_gehaltsbestandteil IS E'Gehaltskomponenten zu Vertraegen';
		COMMENT ON TABLE hr.tbl_sachaufwand IS E'Zusatzvergütungen für Mitarbeiter';
		COMMENT ON TABLE hr.tbl_sachaufwandtyp IS E'Key-Table for Sachaufwand';
		COMMENT ON TABLE hr.tbl_teilzeittyp IS E'Key-Table Altersteilzeit, Elternteilzeit';

		GRANT USAGE ON hr.tbl_dienstverhaeltnis_dienstverhaeltnis_id_seq TO vilesci;
		GRANT USAGE ON hr.tbl_vertragsbestandteil_vertragsbestandteil_id_seq TO vilesci;
		GRANT USAGE ON hr.tbl_gehaltshistorie_gehaltshistorie_id_seq TO vilesci;
		GRANT USAGE ON hr.tbl_sachaufwand_sachaufwand_id_seq TO vilesci;

		GRANT USAGE ON hr.tbl_gehaltsbestandteil_gehaltsbestandteil_id_seq TO vilesci;
		";

		if (! $db->db_query($qry))
			echo '<strong>Vertraege: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'HR Schema und Vertagstabellen wurden neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_dvendegrund' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
		    CREATE TABLE hr.tbl_dvendegrund (
			dvendegrund_kurzbz character varying(32) NOT NULL ,
			bezeichnung character varying(255) NOT NULL,
			bezeichnung_mehrsprachig character varying(255)[] NOT NULL,
			aktiv boolean DEFAULT true NOT NULL,
			sort integer DEFAULT 1 NOT NULL,
			PRIMARY KEY (dvendegrund_kurzbz),
			CONSTRAINT tbl_dvendegrund_bezeichnung_key UNIQUE (bezeichnung)
		    );

		    GRANT SELECT, UPDATE, INSERT, DELETE ON hr.tbl_dvendegrund TO vilesci;

		    INSERT INTO 
			hr.tbl_dvendegrund (dvendegrund_kurzbz, bezeichnung, bezeichnung_mehrsprachig) 
		    VALUES
			('kuendigung_arbeitnehmer', 'Kündigung durch Arbeitnehmer', ARRAY['Kündigung durch Arbeitnehmer', 'Cancellation by Employee']), 
			('kuendigung_arbeitgeber', 'Kündigung durch Arbeitgeber', ARRAY['Kündigung durch Arbeitgeber', 'Cancellation by Employer']),
			('entlassung', 'Entlassung', ARRAY['Entlassung', 'Dismissal']),
			('sonstige', 'Sonstige', ARRAY['Sonstige', 'Miscellaneous']),
			('einvernehmlich', 'Einvernehmliche Auflösung', ARRAY['Einvernehmliche Auflösung', 'Rescission']),
			('ablaufzeit', 'Ablauf durch Zeit', ARRAY['Ablauf durch Zeit', 'Expired by lapse of time']);
		";
		if (! $db->db_query($qry))
			echo '<strong>Vertraege: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Tabelle tbl_dvendegrund wurde im HR Schema neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.columns WHERE column_name='dvendegrund_kurzbz' AND table_name='tbl_dienstverhaeltnis' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
		    ALTER TABLE 
			hr.tbl_dienstverhaeltnis 
		    ADD COLUMN
			dvendegrund_kurzbz character varying(255) 
		    CONSTRAINT 
			tbl_dvendegrund_fk 
		    REFERENCES 
			hr.tbl_dvendegrund(dvendegrund_kurzbz) 
		    ON UPDATE 
			cascade 
		    ON DELETE 
			restrict
		";
		if (! $db->db_query($qry))
			echo '<strong>Vertraege: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Spalte dvendegrund_kurzbz wurde in hr.tbl_dienstverhaeltnis neu erstellt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM information_schema.columns WHERE column_name='dvendegrund_anmerkung' AND table_name='tbl_dienstverhaeltnis' AND table_schema='hr'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
		    ALTER TABLE 
			hr.tbl_dienstverhaeltnis 
		    ADD COLUMN
			dvendegrund_anmerkung character varying(255)
		";
		if (! $db->db_query($qry))
			echo '<strong>Vertraege: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Spalte dvendegrund_anmerkung wurde in hr.tbl_dienstverhaeltnis neu erstellt<br>';
	}
}
