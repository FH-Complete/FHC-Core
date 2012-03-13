<?php
/* Copyright (C) 2006 Technikum-Wien
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
 ******************************************************************************
 * Beschreibung:
 * Dieses Skript prueft die gesamte Systemumgebung und sollte nach jedem Update gestartet werden.
 * Geprueft wird: - die Datenbank auf aktualitaet, dabei werden fehlende Attribute angelegt.
 *                - Verzeichnisse (ob vorhanden und beschreibbar falls noetig).
 */

require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');

// Datenbank Verbindung
$db = new basis_db();

echo '<H1>Systemcheck!</H1>';
echo '<H2>DB-Updates!</H2>';


// ********************** Pruefungen
echo '<H2>Pruefe Tabellen und Attribute!</H2>';

// **************** Spalte aktiv Tabelle OE
if(!$result = @$db->db_query("SELECT aktiv FROM public.tbl_organisationseinheit LIMIT 1;"))
{
	$qry = 'ALTER TABLE public.tbl_organisationseinheit ADD COLUMN aktiv boolean;
			UPDATE public.tbl_organisationseinheit SET aktiv=true;
			ALTER TABLE public.tbl_organisationseinheit ALTER COLUMN aktiv SET DEFAULT true;
			ALTER TABLE public.tbl_organisationseinheit ALTER COLUMN aktiv SET NOT NULL;';
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_organisationseinheit: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' public.tbl_organisationseinheit: Spalte aktiv hinzugefuegt!<br>';
}

if(!$result = @$db->db_query("SELECT fachbereich FROM public.tbl_funktion LIMIT 1;"))
{
	$qry = "ALTER TABLE public.tbl_funktion ADD COLUMN fachbereich boolean;
			UPDATE public.tbl_funktion SET fachbereich=false;
			ALTER TABLE public.tbl_funktion ALTER COLUMN fachbereich SET DEFAULT false;
			ALTER TABLE public.tbl_funktion ALTER COLUMN fachbereich SET NOT NULL;
			
			ALTER TABLE public.tbl_funktion ADD COLUMN semester boolean;
			UPDATE public.tbl_funktion SET semester=false;
			ALTER TABLE public.tbl_funktion ALTER COLUMN semester SET DEFAULT false;
			ALTER TABLE public.tbl_funktion ALTER COLUMN semester SET NOT NULL;
			
			UPDATE public.tbl_funktion SET semester=true WHERE funktion_kurzbz='stdv';
			UPDATE public.tbl_funktion SET semester=true WHERE funktion_kurzbz='oeh-kandidatur';			
			UPDATE public.tbl_funktion SET fachbereich=true WHERE funktion_kurzbz='fbk';
			UPDATE public.tbl_funktion SET fachbereich=true WHERE funktion_kurzbz='fbl';
			UPDATE public.tbl_funktion SET fachbereich=true WHERE funktion_kurzbz='oezuordnung';
			
			UPDATE public.tbl_benutzerfunktion SET oe_kurzbz='Systementwicklung' WHERE oe_kurzbz='Systementwicklg';
			UPDATE public.tbl_benutzerfunktion SET oe_kurzbz='Unternehmenskommunikation' WHERE oe_kurzbz='Unternehmenskomm';
			UPDATE public.tbl_organisationseinheit SET aktiv=false WHERE oe_kurzbz='Unternehmenskomm';
			UPDATE public.tbl_fachbereich SET aktiv=false WHERE oe_kurzbz='Unternehmenskomm';
			UPDATE public.tbl_organisationseinheit SET aktiv=false WHERE oe_kurzbz='Systementwicklg';
			UPDATE public.tbl_fachbereich SET aktiv=false WHERE oe_kurzbz='Systementwicklg';
			";
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_funktion: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' public.tbl_funktion: Spalte funktion und semester hinzugefuegt!<br>';
}

if($result = $db->db_query("SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE table_name='tbl_benutzerfunktion' AND constraint_name='organisationseinheit_benutzerfunktion'"))
{
	if(!$row = $db->db_fetch_object($result))
	{
		$qry = "UPDATE public.tbl_benutzerfunktion SET oe_kurzbz='etw' WHERE oe_kurzbz='0';
				ALTER TABLE public.tbl_benutzerfunktion ADD CONSTRAINT organisationseinheit_benutzerfunktion FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;";
		if(!$db->db_query($qry))
			echo '<strong>public.tbl_benutzerfunktion: '.$db->db_last_error().'</strong><br>';
		else 
			echo ' public.tbl_benutzerfunktion: FK-Constraint zur tbl_organisationseinheit hinzugefuegt!<br>';
	}
}

if(!@$db->db_query("SELECT bezeichnung FROM public.tbl_benutzerfunktion;"))
{
	$qry = "
	-- Spalte Bezeichnung anlegen
	ALTER TABLE public.tbl_benutzerfunktion ADD COLUMN bezeichnung varchar(64);
	-- Bezeichnung fuellen
	UPDATE public.tbl_benutzerfunktion SET bezeichnung=(SELECT beschreibung FROM public.tbl_funktion WHERE funktion_kurzbz=tbl_benutzerfunktion.funktion_kurzbz);

	-- OE-Zuordnung und FBL auf OE umstellen
	UPDATE public.tbl_benutzerfunktion SET oe_kurzbz=(SELECT oe_kurzbz FROM public.tbl_fachbereich 
													  WHERE fachbereich_kurzbz=tbl_benutzerfunktion.fachbereich_kurzbz)
	WHERE (tbl_benutzerfunktion.funktion_kurzbz='oezuordnung' OR tbl_benutzerfunktion.funktion_kurzbz='fbl') AND tbl_benutzerfunktion.fachbereich_kurzbz is not null;
		
	-- Funktionseintrag aktualisieren
	UPDATE public.tbl_funktion SET fachbereich=false WHERE (funktion_kurzbz='oezuordnung' OR funktion_kurzbz='fbl');
	
	-- Fachbereich Feld leeren
	UPDATE public.tbl_benutzerfunktion SET fachbereich_kurzbz=null WHERE (funktion_kurzbz='oezuordnung' OR funktion_kurzbz='fbl');
	
	-- Stg und Fbl auf Leiter aendern
	UPDATE public.tbl_benutzerfunktion SET funktion_kurzbz='Leitung' WHERE funktion_kurzbz='stgl' OR funktion_kurzbz='fbl';
	
	-- Funktion stgl und fbl entfernen
	DELETE FROM public.tbl_funktion WHERE funktion_kurzbz='fbl' OR funktion_kurzbz='stgl';
	";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_benutzerfunktion: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' public.tbl_benutzerfunktion: bezeichnung hinzugefuegt, Stgl und Fbl durch Leitung ersetzt, oezuordnung korrigiert<br>';
}

if($result = $db->db_query("Select count(*) as anzahl FROM pg_class WHERE relname ='idx_stundenplandev_lehreinheit_id'"))
{
	if(!$row = $db->db_fetch_object($result))
	{
		if($row->anzahl==0)
		{
			$qry = "CREATE INDEX idx_stundenplandev_lehreinheit_id ON lehre.tbl_stundenplandev (lehreinheit_id);";
			if(!$db->db_query($qry))
				echo '<strong>lehre.tbl_stundenplandev: '.$db->db_last_error().'</strong><br>';
			else 
				echo ' lehre.tbl_stundenplandev: Index auf lehreinheit_id angelegt!<br>';
		}
	}
}

if($result = $db->db_query("Select count(*) as anzahl FROM pg_class WHERE relname ='idx_stundenplan_lehreinheit_id'"))
{
	if(!$row = $db->db_fetch_object($result))
	{
		if($row->anzahl==0)
		{
			$qry = "CREATE INDEX idx_stundenplan_lehreinheit_id ON lehre.tbl_stundenplan (lehreinheit_id);";
			if(!$db->db_query($qry))
				echo '<strong>lehre.tbl_stundenplan: '.$db->db_last_error().'</strong><br>';
			else 
				echo ' lehre.tbl_stundenplan: Index auf lehreinheit_id angelegt!<br>';
		}
	}
}

if(!$result = @$db->db_query("SELECT * FROM bis.tbl_lgartcode LIMIT 1"))
{
	$qry = '
		CREATE TABLE bis.tbl_lgartcode
		(
		 "lgartcode" Integer NOT NULL,
		 "kurzbz" Character varying(32),
		 "bezeichnung" Character varying(256),
		 "beantragung" Boolean NOT NULL
		)
		WITH (OIDS=FALSE);
		
		ALTER TABLE public.tbl_studiengang ADD COLUMN lgartcode integer;
		
		ALTER TABLE "bis"."tbl_lgartcode" ADD CONSTRAINT "pk_tbl_lgartcode" PRIMARY KEY ("lgartcode");
		ALTER TABLE "public"."tbl_studiengang" ADD CONSTRAINT "lgartcode_studiengang" FOREIGN KEY ("lgartcode") REFERENCES "bis"."tbl_lgartcode" ("lgartcode") ON DELETE RESTRICT ON UPDATE CASCADE;
		
		GRANT SELECT, INSERT, UPDATE, DELETE ON bis.tbl_lgartcode TO "admin";
		GRANT SELECT ON bis.tbl_lgartcode TO "web";
		
		INSERT INTO bis.tbl_lgartcode (lgartcode, kurzbz, bezeichnung, beantragung) VALUES(1, \'LG - MA; MBA\', \'LG zur Weiterbildung - MA; MBA\',true);
		INSERT INTO bis.tbl_lgartcode (lgartcode, kurzbz, bezeichnung, beantragung) VALUES(2, \'LG - akademische/r ...\', \'LG zur Weiterbildung - akademische/r ...\',true);
		INSERT INTO bis.tbl_lgartcode (lgartcode, kurzbz, bezeichnung, beantragung) VALUES(3, \'LG - sonstiger\', \'LG zur Weiterbildung - sonstiger\',false);
		';
	
	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_lgartcode: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' bis.tbl_lgartcode: Lehrgangsart hinzugefuegt!<br>';
}

if(!$result = @$db->db_query("SELECT * FROM wawi.tbl_betriebsmittelperson LIMIT 1"))
{
	$qry = "
		CREATE SCHEMA wawi;
		
		DROP VIEW public.vw_betriebsmittelperson;
		DROP TABLE campus.tbl_bmreservierung;
		
		ALTER TABLE public.tbl_betriebsmittel SET SCHEMA wawi;
		ALTER TABLE public.tbl_betriebsmittelperson SET SCHEMA wawi;
		ALTER TABLE public.tbl_betriebsmitteltyp SET SCHEMA wawi;
		
		ALTER TABLE wawi.tbl_betriebsmittel DROP COLUMN ort_kurzbz;
		
		ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN oe_kurzbz varchar(32);
		ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN ort_kurzbz varchar(16);
		ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN hersteller varchar(128);
		ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN seriennummer varchar(32);
		ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN bestellung_id bigint;
		ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN bestelldetail_id bigint;
		ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN afa smallint;
		ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN verwendung varchar(256);
		ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN anmerkung text;
		
		COMMENT ON COLUMN wawi.tbl_betriebsmittel.nummer IS 'Zutrittskartennummer, Inventarnummer, ...';
		COMMENT ON COLUMN wawi.tbl_betriebsmittel.afa IS 'Jahre fuer die AfA';
		COMMENT ON COLUMN wawi.tbl_betriebsmittel.nummerintern IS '2. Nummer fuer spezielle BM';
		
		ALTER TABLE wawi.tbl_betriebsmittelperson ADD COLUMN betriebsmittelperson_id integer;
		
		CREATE SEQUENCE wawi.seq_betriebsmittelperson_betriebsmittelperson_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1
		;
		
		UPDATE wawi.tbl_betriebsmittelperson SET betriebsmittelperson_id=nextval('wawi.seq_betriebsmittelperson_betriebsmittelperson_id');
		ALTER TABLE wawi.tbl_betriebsmittelperson DROP CONSTRAINT pk_tbl_betriebsmittelperson;
		
		ALTER TABLE wawi.tbl_betriebsmittelperson ADD CONSTRAINT pk_betriebsmittelperson PRIMARY KEY (betriebsmittelperson_id);
		ALTER TABLE wawi.tbl_betriebsmittelperson ALTER COLUMN betriebsmittelperson_id SET DEFAULT nextval('wawi.seq_betriebsmittelperson_betriebsmittelperson_id');
		
		ALTER TABLE wawi.tbl_betriebsmitteltyp ADD COLUMN typ_code character(2);
		
		COMMENT ON COLUMN wawi.tbl_betriebsmitteltyp.typ_code IS 'Fuer Inventarnummerncode';
		
		-- Table wawi.tbl_betriebsmittelstatus
		
		CREATE TABLE wawi.tbl_betriebsmittelstatus(
			betriebsmittelstatus_kurzbz Character varying(16) NOT NULL,
			beschreibung Character varying(256)
		)
		WITH (OIDS=FALSE);
		
		-- Add keys for table wawi.tbl_betriebsmittelstatus
		
		ALTER TABLE wawi.tbl_betriebsmittelstatus ADD CONSTRAINT pk_betriebsmittelstatus PRIMARY KEY (betriebsmittelstatus_kurzbz);
		ALTER TABLE wawi.tbl_betriebsmittelstatus ADD CONSTRAINT betriebsmittelstatus_kurzbz UNIQUE (betriebsmittelstatus_kurzbz);
		
		-- Table tbl_betriebsmittel_betriebsmittelstatus
		
		CREATE TABLE wawi.tbl_betriebsmittel_betriebsmittelstatus(
		 betriebsmittelbetriebsmittelstatus_id Serial NOT NULL,
		 betriebsmittel_id Integer NOT NULL,
		 betriebsmittelstatus_kurzbz Character varying(16) NOT NULL,
		 datum date,
		 anmerkung text,
		 updateamum Timestamp,
		 updatevon Character varying(32),
		 insertamum Timestamp,
		 insertvon Character varying(32)
		)
		WITH (OIDS=FALSE);
		
		ALTER TABLE wawi.tbl_betriebsmittel ALTER COLUMN reservieren SET NOT NULL;
		ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN leasing_bis date;
		
		-- Add keys for table tbl_betriebsmittel_betriebsmittelstatus
		
		ALTER TABLE wawi.tbl_betriebsmittel_betriebsmittelstatus ADD CONSTRAINT pk_betriebsmittelbetriebsmittelstatus PRIMARY KEY (betriebsmittelbetriebsmittelstatus_id);
		
		GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_betriebsmittel_betriebsmittelstatus TO admin;
		GRANT SELECT ON wawi.tbl_betriebsmittel_betriebsmittelstatus TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_betriebsmittelstatus TO admin;
		GRANT SELECT ON wawi.tbl_betriebsmittelstatus TO web;
		GRANT USAGE ON SCHEMA wawi TO web;
		GRANT USAGE ON SCHEMA wawi TO admin;
		GRANT SELECT ON wawi.tbl_betriebsmittelperson TO web;
		
		
		
		CREATE OR REPLACE VIEW public.vw_betriebsmittelperson AS
		 SELECT tbl_betriebsmittelperson.betriebsmittelperson_id, tbl_betriebsmittelperson.betriebsmittel_id, tbl_betriebsmittelperson.person_id, tbl_betriebsmittelperson.anmerkung, tbl_betriebsmittelperson.kaution, tbl_betriebsmittelperson.ausgegebenam, tbl_betriebsmittelperson.retouram, tbl_betriebsmittelperson.insertamum, tbl_betriebsmittelperson.insertvon, tbl_betriebsmittelperson.updateamum, tbl_betriebsmittelperson.updatevon, tbl_betriebsmittelperson.ext_id, tbl_betriebsmittel.beschreibung, tbl_betriebsmittel.betriebsmitteltyp, tbl_betriebsmittel.nummer, tbl_betriebsmittel.nummerintern, tbl_betriebsmittel.reservieren, tbl_betriebsmittel.ort_kurzbz, tbl_person.staatsbuergerschaft, tbl_person.geburtsnation, tbl_person.sprache, tbl_person.anrede, tbl_person.titelpost, tbl_person.titelpre, tbl_person.nachname, tbl_person.vorname, tbl_person.vornamen, tbl_person.gebdatum, tbl_person.gebort, tbl_person.gebzeit, tbl_person.foto, tbl_person.anmerkung AS anmerkungen, tbl_person.homepage, tbl_person.svnr, tbl_person.ersatzkennzeichen, tbl_person.familienstand, tbl_person.geschlecht, tbl_person.anzahlkinder, tbl_person.aktiv, tbl_benutzer.uid, tbl_benutzer.aktiv AS benutzer_aktiv, tbl_benutzer.alias
		   FROM wawi.tbl_betriebsmittelperson
		   JOIN wawi.tbl_betriebsmittel USING (betriebsmittel_id)
		   JOIN public.tbl_person USING (person_id)
		   LEFT JOIN public.tbl_benutzer USING (person_id);
		   
		GRANT SELECT, UPDATE ON wawi.tbl_betriebsmittel_betriebsmi_betriebsmittelbetriebsmittels_seq TO web;
		GRANT SELECT, UPDATE ON wawi.tbl_betriebsmittel_betriebsmi_betriebsmittelbetriebsmittels_seq TO admin;
		GRANT SELECT, UPDATE ON wawi.seq_betriebsmittelperson_betriebsmittelperson_id TO admin;
		GRANT SELECT, UPDATE ON wawi.seq_betriebsmittelperson_betriebsmittelperson_id TO web;
		GRANT SELECT ON public.vw_betriebsmittelperson TO web;
		GRANT SELECT ON public.vw_betriebsmittelperson TO admin;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>wawi: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' wawi schema und tabellen wurden angelegt!<br>';
}

if($result = $db->db_query("SELECT is_nullable FROM information_schema.columns WHERE table_schema='public' AND table_name='tbl_studiengang'  AND column_name='aktiv'"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->is_nullable=='YES')
		{
			$qry = 'ALTER TABLE public.tbl_studiengang ALTER COLUMN aktiv SET NOT NULL;
					ALTER TABLE public.tbl_studiengang ALTER COLUMN testtool_sprachwahl SET NOT NULL;';
			
			if(!$db->db_query($qry))
				echo '<strong>public.tbl_studiengang: '.$db->db_last_error().'</strong><br>';
			else 
				echo 'public.tbl_studiengang: Spalte aktiv und testtool_sprachwahl wurde auf NN gesetzt!<br>';
		}
	}
}

if(@$db->db_query("SELECT organisationsform FROM public.tbl_studiengang LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_studiengang DROP COLUMN organisationsform;";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_studiengang: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_studiengang: Spalte organisationsform entfernt!<br>';
}

if(!@$db->db_query("SELECT insertamum FROM campus.tbl_reservierung LIMIT 1;"))
{
	$qry = "ALTER TABLE campus.tbl_reservierung ADD COLUMN insertamum timestamp;
			ALTER TABLE campus.tbl_reservierung ADD COLUMN insertvon varchar(32);
			DROP VIEW campus.vw_reservierung;

			CREATE VIEW campus.vw_reservierung AS
				SELECT tbl_reservierung.reservierung_id, tbl_reservierung.ort_kurzbz, tbl_reservierung.studiengang_kz, 
					tbl_reservierung.uid, tbl_reservierung.stunde, tbl_reservierung.datum, tbl_reservierung.titel, 
					tbl_reservierung.beschreibung, tbl_reservierung.semester, tbl_reservierung.verband, tbl_reservierung.gruppe, 
					tbl_reservierung.gruppe_kurzbz, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_reservierung.insertamum, tbl_reservierung.insertvon
				FROM campus.tbl_reservierung JOIN public.tbl_studiengang USING (studiengang_kz);
 
			GRANT SELECT ON campus.vw_reservierung TO GROUP web;
			GRANT SELECT ON campus.vw_reservierung TO GROUP admin;";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_reservierung: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'campus.tbl_reservierung: Spalte insertamum und insertvon hinzugefuegt!<br>';
}

if(!@$db->db_query("SELECT aktiv FROM public.tbl_buchungstyp LIMIT 1;"))
{
	$qry = "ALTER TABLE public.tbl_buchungstyp ADD COLUMN aktiv boolean DEFAULT true;
	UPDATE public.tbl_buchungstyp SET aktiv=true;
	ALTER TABLE public.tbl_buchungstyp ALTER COLUMN aktiv SET NOT NULL;";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_buchungstyp: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_buchungstyp: Spalte aktiv hinzugefuegt!<br>';
}

//Firmenverwaltung
if(!@$db->db_query("SELECT steuernummer FROM public.tbl_firma LIMIT 1;"))
{
	$qry = "
	ALTER TABLE public.tbl_firma ADD COLUMN steuernummer varchar(32);
	ALTER TABLE public.tbl_firma ADD COLUMN gesperrt boolean DEFAULT false;
	ALTER TABLE public.tbl_firma ALTER COLUMN gesperrt SET NOT NULL;
	ALTER TABLE public.tbl_firma ADD COLUMN aktiv boolean DEFAULT true;
	ALTER TABLE public.tbl_firma ALTER COLUMN aktiv SET NOT NULL;
	
	-- Table public.tbl_standort
	CREATE SEQUENCE public.tbl_standort_standort_id_seq
		INCREMENT BY 1
		NO MAXVALUE
		NO MINVALUE
		CACHE 1;
		
	DROP VIEW campus.vw_mitarbeiter;
	
	ALTER TABLE public.tbl_standort ADD COLUMN standort_id integer DEFAULT nextval('public.tbl_standort_standort_id_seq');
	ALTER TABLE public.tbl_standort RENAME COLUMN standort_kurzbz TO kurzbz;
	ALTER TABLE public.tbl_standort ADD COLUMN bezeichnung varchar(256);
	ALTER TABLE public.tbl_standort ADD COLUMN insertvon varchar(32);
	ALTER TABLE public.tbl_standort ADD COLUMN insertamum timestamp;
	ALTER TABLE public.tbl_standort ADD COLUMN updatevon varchar(32);
	ALTER TABLE public.tbl_standort ADD COLUMN updateamum timestamp;
	ALTER TABLE public.tbl_standort ADD COLUMN ext_id bigint;
	ALTER TABLE public.tbl_standort ADD COLUMN firma_id integer;
	ALTER TABLE public.tbl_standort ALTER COLUMN adresse_id DROP NOT NULL;
	
	
	
	--  Primary key in tbl_standort aendern
	ALTER TABLE public.tbl_ort DROP CONSTRAINT standort_ort;
	ALTER TABLE public.tbl_mitarbeiter DROP CONSTRAINT standort_mitarbeiter;
	ALTER TABLE public.tbl_standort DROP CONSTRAINT pk_tbl_standort;
	UPDATE public.tbl_standort SET standort_id= nextval('public.tbl_standort_standort_id_seq');
	ALTER TABLE public.tbl_standort ALTER COLUMN standort_id SET NOT NULL;
	ALTER TABLE public.tbl_standort ADD CONSTRAINT pk_standort PRIMARY KEY (standort_id);
	
	ALTER TABLE public.tbl_standort ALTER COLUMN kurzbz DROP NOT NULL;
	
	-- verknuepfung Standort/Firma richtigstellen
	INSERT INTO public.tbl_firmentyp(firmentyp_kurzbz, beschreibung) VALUES('Intern','Intern');
	
	UPDATE public.tbl_firma set firmentyp_kurzbz='Intern', ext_id=(SELECT standort_id FROM public.tbl_adresse JOIN public.tbl_standort USING(adresse_id) WHERE tbl_adresse.firma_id=tbl_firma.firma_id)
	WHERE firma_id in(SELECT tbl_adresse.firma_id FROM public.tbl_standort JOIN public.tbl_adresse USING(adresse_id));
	
	UPDATE public.tbl_standort SET bezeichnung=kurzbz WHERE bezeichnung is null;
	
	UPDATE public.tbl_standort SET firma_id=(SELECT firma_id FROM public.tbl_firma where ext_id=standort_id);
	UPDATE public.tbl_firma SET ext_id=null;

	-- Standorte zu den Firmen anlegen
	INSERT INTO public.tbl_standort(firma_id, adresse_id, kurzbz, bezeichnung)
	SELECT firma_id, adresse_id, substring(tbl_firma.name for 16), tbl_firma.name 
	FROM public.tbl_firma LEFT JOIN public.tbl_adresse USING(firma_id) 
	WHERE tbl_adresse.person_id is null AND firma_id not in (SELECT firma_id FROM public.tbl_standort);

	INSERT INTO public.tbl_standort(firma_id, adresse_id, kurzbz, bezeichnung)
	SELECT firma_id, null, substring(tbl_firma.name for 16), tbl_firma.name 
	FROM public.tbl_firma
	WHERE firma_id not in (SELECT firma_id FROM public.tbl_standort);
	
	-- fk zum standort in tbl_mitarbeiter aendern
	ALTER TABLE public.tbl_mitarbeiter ADD COLUMN standort_id integer;
	UPDATE public.tbl_mitarbeiter SET standort_id=(SELECT standort_id FROM public.tbl_standort where kurzbz=tbl_mitarbeiter.standort_kurzbz);
	ALTER TABLE public.tbl_mitarbeiter ADD CONSTRAINT fk_mitarbeiter_standort FOREIGN KEY (standort_id) REFERENCES public.tbl_standort (standort_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE public.tbl_mitarbeiter DROP COLUMN standort_kurzbz;
	
	-- fk zum standort in tbl_ort aendern
	ALTER TABLE public.tbl_ort ADD COLUMN standort_id integer;
	UPDATE public.tbl_ort SET standort_id=(SELECT standort_id FROM public.tbl_standort WHERE kurzbz=tbl_ort.standort_kurzbz);
	ALTER TABLE public.tbl_ort ADD CONSTRAINT fk_ort_standort FOREIGN KEY (standort_id) REFERENCES public.tbl_standort (standort_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE public.tbl_ort DROP COLUMN standort_kurzbz;	

	-- Table public.tbl_personfunktionstandort
	CREATE SEQUENCE public.tbl_personfunktionstandort_personfunktionstandort_id_seq
		INCREMENT BY 1
		NO MAXVALUE
		NO MINVALUE
		CACHE 1;
	
	CREATE TABLE public.tbl_personfunktionstandort
	(
		personfunktionstandort_id integer DEFAULT nextval('public.tbl_personfunktionstandort_personfunktionstandort_id_seq'),
		funktion_kurzbz varchar(16) NOT NULL,
		person_id Integer NOT NULL,
		position varchar(256),
		anrede varchar(128),
		standort_id Integer
	);

	ALTER TABLE public.tbl_personfunktionstandort ALTER COLUMN personfunktionstandort_id SET NOT NULL;
	ALTER TABLE public.tbl_personfunktionstandort ADD CONSTRAINT pk_personfunktionstandort PRIMARY KEY (personfunktionstandort_id);	
	ALTER TABLE public.tbl_personfunktionstandort ADD CONSTRAINT fk_funktion_personfunktionstandort FOREIGN KEY (funktion_kurzbz) REFERENCES public.tbl_funktion (funktion_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE public.tbl_personfunktionstandort ADD CONSTRAINT fk_person_personfunktionstandort FOREIGN KEY (person_id) REFERENCES public.tbl_person (person_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE public.tbl_personfunktionstandort ADD CONSTRAINT fk_standort_personfunktionstandort FOREIGN KEY (standort_id) REFERENCES public.tbl_standort (standort_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	
	DROP TABLE public.tbl_personfunktionfirma;
		
	-- Table public.tbl_tag
	
	CREATE TABLE public.tbl_tag
	(
		tag varchar(128) NOT NULL
	);
	
	ALTER TABLE public.tbl_tag ADD CONSTRAINT pk_tag PRIMARY KEY (tag);
	ALTER TABLE public.tbl_tag ADD CONSTRAINT tag UNIQUE (tag);
	
	-- Table tbl_firmatag
	
	CREATE TABLE public.tbl_firmatag
	(
		firma_id Integer NOT NULL,
		tag varchar(128) NOT NULL,
		insertamum Timestamp,
		insertvon varchar(32)
	);

	ALTER TABLE public.tbl_firmatag ADD CONSTRAINT pk_firmatag PRIMARY KEY (firma_id,tag);
	ALTER TABLE public.tbl_firmatag ADD CONSTRAINT fk_firmatag_firma FOREIGN KEY (firma_id) REFERENCES public.tbl_firma (firma_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE tbl_firmatag ADD CONSTRAINT fk_tag_firmatag FOREIGN KEY (tag) REFERENCES public.tbl_tag (tag) ON DELETE RESTRICT ON UPDATE CASCADE;

		
	-- Table public.tbl_firma_organisationseinheit
	
	CREATE TABLE public.tbl_firma_organisationseinheit
	(
		firma_organisationseinheit_id Serial NOT NULL,
		firma_id Integer NOT NULL,
		oe_kurzbz Character varying(32) NOT NULL,
		bezeichnung Character varying(256),
		kundennummer Character varying(128),
		insertamum Timestamp,
		insertvon Character varying(32),
		updateamum Timestamp,
		updatevon Character varying(32),
		ext_id Bigint
	);
	
	ALTER TABLE public.tbl_firma_organisationseinheit ADD CONSTRAINT pk_firma_oe PRIMARY KEY (firma_organisationseinheit_id);
	ALTER TABLE public.tbl_firma_organisationseinheit ADD CONSTRAINT uk_firma_oe UNIQUE (firma_id, oe_kurzbz);	
	ALTER TABLE public.tbl_firma_organisationseinheit ADD CONSTRAINT fk_firma_organisationseinheitfirma FOREIGN KEY (firma_id) REFERENCES public.tbl_firma (firma_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE public.tbl_firma_organisationseinheit ADD CONSTRAINT fk_organisationseinheit_organisationseinheitfirma FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	
	-- Lehreinheitmitarbeiter
	ALTER TABLE lehre.tbl_lehreinheitmitarbeiter ADD COLUMN standort_id integer;
	ALTER TABLE lehre.tbl_lehreinheitmitarbeiter ADD CONSTRAINT fk_standort_lehreinheitmitarbeiter FOREIGN KEY (standort_id) REFERENCES public.tbl_standort (standort_id) ON DELETE RESTRICT ON UPDATE CASCADE;

	-- Finanzamt
	ALTER TABLE public.tbl_firma ADD COLUMN finanzamt integer;
	ALTER TABLE public.tbl_firma ADD CONSTRAINT fk_standort_firma FOREIGN KEY (finanzamt) REFERENCES public.tbl_standort (standort_id) ON DELETE RESTRICT ON UPDATE CASCADE;

	-- Kontakt Standort
	ALTER TABLE public.tbl_kontakt ADD COLUMN standort_id integer;
	ALTER TABLE public.tbl_kontakt ADD CONSTRAINT fk_standort_kontakt FOREIGN KEY (standort_id) REFERENCES public.tbl_standort (standort_id) ON DELETE CASCADE ON UPDATE CASCADE;
		
	-- Berechtigungen
	GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_personfunktionstandort TO admin;
	GRANT SELECT ON public.tbl_personfunktionstandort TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_standort TO admin;
	GRANT SELECT ON public.tbl_standort TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_firma_organisationseinheit TO admin;
	GRANT SELECT ON public.tbl_firma_organisationseinheit TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_tag TO admin;
	GRANT SELECT ON public.tbl_tag TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_firmatag TO admin;
	GRANT SELECT ON public.tbl_firmatag TO web;
	GRANT SELECT, UPDATE ON public.tbl_standort_standort_id_seq TO admin;
	GRANT SELECT, UPDATE ON public.tbl_standort_standort_id_seq TO web;
	GRANT SELECT, UPDATE ON public.tbl_personfunktionstandort_personfunktionstandort_id_seq TO admin;
	GRANT SELECT, UPDATE ON public.tbl_personfunktionstandort_personfunktionstandort_id_seq TO admin;
	GRANT SELECT, UPDATE ON public.tbl_firma_organisationseinhei_firma_organisationseinheit_id_seq TO admin;
	GRANT SELECT, UPDATE ON public.tbl_firma_organisationseinhei_firma_organisationseinheit_id_seq TO web;

	-- View wieder anlegen
	CREATE OR REPLACE VIEW campus.vw_mitarbeiter as
	SELECT tbl_benutzer.uid, tbl_mitarbeiter.ausbildungcode, tbl_mitarbeiter.personalnummer, tbl_mitarbeiter.kurzbz, tbl_mitarbeiter.lektor, tbl_mitarbeiter.fixangestellt, tbl_mitarbeiter.telefonklappe, tbl_benutzer.person_id, tbl_benutzer.alias, tbl_person.geburtsnation, tbl_person.sprache, tbl_person.anrede, tbl_person.titelpost, tbl_person.titelpre, tbl_person.nachname, tbl_person.vorname, tbl_person.vornamen, tbl_person.gebdatum, tbl_person.gebort, tbl_person.gebzeit, tbl_person.foto, tbl_mitarbeiter.anmerkung, tbl_person.homepage, tbl_person.svnr, tbl_person.ersatzkennzeichen, tbl_person.geschlecht, tbl_person.familienstand, tbl_person.anzahlkinder, tbl_mitarbeiter.ort_kurzbz, tbl_benutzer.aktiv, tbl_mitarbeiter.bismelden, tbl_mitarbeiter.standort_id, tbl_mitarbeiter.updateamum, tbl_mitarbeiter.updatevon, tbl_mitarbeiter.insertamum, tbl_mitarbeiter.insertvon, tbl_mitarbeiter.ext_id
   	FROM tbl_mitarbeiter
   	JOIN tbl_benutzer ON tbl_mitarbeiter.mitarbeiter_uid::text = tbl_benutzer.uid::text
   	JOIN tbl_person USING (person_id);
   	
   	GRANT SELECT ON campus.vw_mitarbeiter TO admin;
   	GRANT SELECT ON campus.vw_mitarbeiter TO web;
   	
   	-- Syncronisieren der Daten
   		
   	-- Firmenkontakte in tbl_kontakt auslagern
   	-- EMail
	INSERT INTO public.tbl_kontakt(standort_id, kontakttyp, kontakt, insertamum, insertvon, updateamum, updatevon) 
	SELECT standort_id, 'email', email, tbl_firma.insertamum, tbl_firma.insertvon, now(), 'checksystem' FROM 
	public.tbl_firma JOIN public.tbl_standort USING(firma_id) WHERE email is not null AND email<>'';
	
	-- Telefon
	INSERT INTO public.tbl_kontakt(standort_id, kontakttyp, kontakt, insertamum, insertvon, updateamum, updatevon) 
	SELECT standort_id, 'telefon', telefon, tbl_firma.insertamum, tbl_firma.insertvon, now(), 'checksystem' FROM 
	public.tbl_firma JOIN public.tbl_standort USING(firma_id) WHERE telefon is not null AND telefon<>'';
	
	-- Fax
	INSERT INTO public.tbl_kontakt(standort_id, kontakttyp, kontakt, insertamum, insertvon, updateamum, updatevon) 
	SELECT standort_id, 'fax', fax, tbl_firma.insertamum, tbl_firma.insertvon, now(), 'checksystem' FROM 
	public.tbl_firma JOIN public.tbl_standort USING(firma_id) WHERE fax is not null AND fax<>'';

	
	UPDATE public.tbl_kontakt SET standort_id=(SELECT standort_id FROM public.tbl_standort WHERE firma_id=tbl_kontakt.firma_id LIMIT 1) WHERE firma_id IS NOT NULL;

	-- Spalten entfernen
	ALTER TABLE public.tbl_firma DROP COLUMN email;
	ALTER TABLE public.tbl_firma DROP COLUMN telefon;
	ALTER TABLE public.tbl_firma DROP COLUMN fax;
	ALTER TABLE public.tbl_kontakt DROP COLUMN firma_id;
	
	INSERT INTO public.tbl_kontakttyp(kontakttyp, beschreibung) VALUES('homepage','Homepage');
	INSERT INTO public.tbl_funktion (funktion_kurzbz, beschreibung, aktiv, fachbereich, semester) VALUES('Ansprechpartner','Ansprechpartner',true,false,false);
	INSERT INTO public.tbl_firmentyp (firmentyp_kurzbz, beschreibung) VALUES('Finanzamt','Finanzamt');
	
	CREATE INDEX idx_tbl_standort_firma_id ON public.tbl_standort(firma_id);
	";
	
	if(!$db->db_query($qry))
		echo '<strong>Firmenverwaltung: '.$db->db_last_error().'</strong><br>';
	else 
	{
		echo 'Tabellen fuer neue Firmenverwaltung hinzugefuegt!<br>';
		
		//Adressen der Firmen Syncronisieren
		$qry = "
		SELECT 
			adresse, tbl_firma.insertamum, tbl_firma.insertvon, standort_id 
		FROM 
			public.tbl_firma JOIN public.tbl_standort USING(firma_id)
		WHERE 
			tbl_firma.adresse IS NOT NULL
			AND tbl_firma.adresse<>'' 
			AND tbl_standort.adresse_id IS NULL";
		
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				$qry = "INSERT INTO public.tbl_adresse(strasse, typ, heimatadresse, 
							zustelladresse, updateamum, updatevon, insertamum, insertvon)
						VALUES('".addslashes($row->adresse)."', 'f', false, true, now(), 'checksystem', 
						".($row->insertamum!=''?"'".addslashes($row->insertamum)."'":'null').",'".addslashes($row->insertvon)."');
						UPDATE public.tbl_standort SET adresse_id=currval('public.tbl_adresse_adresse_id_seq') WHERE standort_id='".$row->standort_id."';";
				if(!$db->db_query($qry))
				{
					echo 'Fehler beim Syncronisieren der Adress-Daten:'.$qry;
				}
			}
		}
		//Adressfeld entfernen
		$qry = "ALTER TABLE public.tbl_firma DROP COLUMN adresse;";
		
		if(!$db->db_query($qry))
		{
			echo 'Fehler beim Loeschen der Spalte adresse';
		}
	}
}

if(!@$db->db_query("SELECT mailverteiler FROM public.tbl_organisationseinheit LIMIT 1;"))
{
	$qry = "
	ALTER TABLE public.tbl_organisationseinheit ADD COLUMN mailverteiler boolean DEFAULT true;
	UPDATE public.tbl_organisationseinheit SET mailverteiler=false;
	ALTER TABLE public.tbl_organisationseinheit ALTER COLUMN mailverteiler SET NOT NULL;
	
	-- Gruppe Kurzbz auf 32 Zeichen aendern
	DROP VIEW campus.vw_persongruppe;
	DROP VIEW lehre.vw_reservierung;
	DROP VIEW campus.vw_reservierung;
	DROP VIEW lehre.vw_lva_stundenplandev;
	DROP VIEW lehre.vw_lva_stundenplan;
	DROP VIEW campus.vw_student_lehrveranstaltung;
	DROP VIEW campus.vw_lehreinheit;
	DROP VIEW lehre.vw_stundenplan;
	DROP VIEW lehre.vw_stundenplandev;
	DROP VIEW campus.vw_stundenplan;
	DROP VIEW lehre.vw_stundenplandev_student_unr;
	
	ALTER TABLE public.tbl_gruppe ALTER COLUMN gruppe_kurzbz TYPE varchar(32);
	ALTER TABLE campus.tbl_reservierung ALTER COLUMN gruppe_kurzbz TYPE varchar(32);
	ALTER TABLE lehre.tbl_lehreinheitgruppe ALTER COLUMN gruppe_kurzbz TYPE varchar(32);
	ALTER TABLE lehre.tbl_stundenplan ALTER COLUMN gruppe_kurzbz TYPE varchar(32);
	ALTER TABLE lehre.tbl_stundenplandev ALTER COLUMN gruppe_kurzbz TYPE varchar(32);
	ALTER TABLE public.tbl_benutzergruppe ALTER COLUMN gruppe_kurzbz TYPE varchar(32);
		
	CREATE VIEW campus.vw_persongruppe AS
	SELECT tbl_benutzer.uid, tbl_benutzergruppe.gruppe_kurzbz, tbl_gruppe.studiengang_kz, tbl_person.nachname, tbl_person.vorname, tbl_person.vornamen, tbl_person.person_id, tbl_person.gebdatum, tbl_person.titelpost, tbl_person.titelpre, tbl_person.staatsbuergerschaft, tbl_person.geburtsnation, tbl_person.sprache, tbl_person.anrede, tbl_person.gebort, tbl_person.gebzeit, tbl_person.foto, tbl_person.homepage, tbl_person.svnr, tbl_person.ersatzkennzeichen, tbl_person.familienstand, tbl_person.geschlecht, tbl_person.anzahlkinder, tbl_benutzer.alias, tbl_person.anmerkung, tbl_person.aktiv AS aktivperson, tbl_gruppe.mailgrp, tbl_gruppe.sichtbar, tbl_benutzer.aktiv AS aktivbenutzer, tbl_gruppe.semester, tbl_gruppe.bezeichnung, tbl_gruppe.beschreibung, tbl_gruppe.generiert, tbl_gruppe.aktiv AS aktivgruppe, tbl_gruppe.sort, tbl_benutzergruppe.updateamum, tbl_benutzergruppe.updatevon, tbl_benutzergruppe.insertamum, tbl_benutzergruppe.insertvon
	FROM public.tbl_person
	JOIN public.tbl_benutzer USING (person_id)
	JOIN public.tbl_benutzergruppe USING (uid)
	JOIN public.tbl_gruppe USING (gruppe_kurzbz);
	
	CREATE VIEW lehre.vw_reservierung AS
	SELECT tbl_reservierung.reservierung_id, tbl_reservierung.ort_kurzbz, tbl_reservierung.studiengang_kz, tbl_reservierung.uid, tbl_reservierung.stunde, tbl_reservierung.datum, tbl_reservierung.titel, tbl_reservierung.beschreibung, tbl_reservierung.semester, tbl_reservierung.verband, tbl_reservierung.gruppe, tbl_reservierung.gruppe_kurzbz, tbl_studiengang.kurzbz AS stg_kurzbz
	FROM campus.tbl_reservierung
	JOIN public.tbl_studiengang USING (studiengang_kz);
	
	CREATE VIEW campus.vw_reservierung AS
	SELECT tbl_reservierung.reservierung_id, tbl_reservierung.ort_kurzbz, tbl_reservierung.studiengang_kz, tbl_reservierung.uid, tbl_reservierung.stunde, tbl_reservierung.datum, tbl_reservierung.titel, tbl_reservierung.beschreibung, tbl_reservierung.semester, tbl_reservierung.verband, tbl_reservierung.gruppe, tbl_reservierung.gruppe_kurzbz, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_reservierung.insertamum, tbl_reservierung.insertvon
   	FROM campus.tbl_reservierung
   	JOIN public.tbl_studiengang USING (studiengang_kz);
   	
	CREATE VIEW lehre.vw_lva_stundenplandev AS
	SELECT le.lehreinheit_id, le.unr, le.lvnr, tbl_lehrfach.fachbereich_kurzbz, le.lehrfach_id, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe AS lehrfach_farbe, le.lehrform_kurzbz AS lehrform, lema.mitarbeiter_uid AS lektor_uid, tbl_mitarbeiter.kurzbz AS lektor, tbl_studiengang.studiengang_kz, upper(tbl_studiengang.typ::character varying::text || tbl_studiengang.kurzbz::text) AS studiengang, lvb.semester, lvb.verband, lvb.gruppe, lvb.gruppe_kurzbz, le.raumtyp, le.raumtypalternativ, le.stundenblockung, le.wochenrythmus, lema.semesterstunden, lema.planstunden, le.start_kw, le.anmerkung, le.studiensemester_kurzbz, 
	( SELECT count(*) AS count FROM lehre.tbl_stundenplandev WHERE tbl_stundenplandev.mitarbeiter_uid::text = lema.mitarbeiter_uid::text AND tbl_stundenplandev.studiengang_kz = lvb.studiengang_kz AND tbl_stundenplandev.semester = lvb.semester AND (tbl_stundenplandev.verband = lvb.verband OR (tbl_stundenplandev.verband IS NULL OR tbl_stundenplandev.verband = ''::bpchar) AND lvb.verband IS NULL) AND (tbl_stundenplandev.gruppe = lvb.gruppe OR (tbl_stundenplandev.gruppe IS NULL OR tbl_stundenplandev.gruppe = ''::bpchar) AND lvb.gruppe IS NULL) AND (tbl_stundenplandev.gruppe_kurzbz::text = lvb.gruppe_kurzbz::text OR tbl_stundenplandev.gruppe_kurzbz IS NULL AND lvb.gruppe_kurzbz IS NULL) AND tbl_stundenplandev.lehreinheit_id = lvb.lehreinheit_id) AS verplant
	FROM lehre.tbl_lehreinheit le
	JOIN lehre.tbl_lehreinheitmitarbeiter lema USING (lehreinheit_id)
	JOIN lehre.tbl_lehreinheitgruppe lvb USING (lehreinheit_id)
	JOIN public.tbl_studiengang ON lvb.studiengang_kz = tbl_studiengang.studiengang_kz
	JOIN lehre.tbl_lehrfach USING (lehrfach_id)
	JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);
   	
	CREATE VIEW lehre.vw_lva_stundenplan AS
	SELECT le.lehreinheit_id, le.unr, le.lvnr, tbl_lehrfach.fachbereich_kurzbz, le.lehrfach_id, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe AS lehrfach_farbe, le.lehrform_kurzbz AS lehrform, lema.mitarbeiter_uid AS lektor_uid, ma.kurzbz AS lektor, tbl_studiengang.studiengang_kz, tbl_studiengang.kurzbz AS studiengang, lvb.semester, lvb.verband, lvb.gruppe, lvb.gruppe_kurzbz, le.raumtyp, le.raumtypalternativ, le.stundenblockung, le.wochenrythmus, lema.semesterstunden, lema.planstunden, le.start_kw, le.anmerkung, le.studiensemester_kurzbz, 
	( SELECT count(*) AS count FROM lehre.tbl_stundenplan WHERE tbl_stundenplan.mitarbeiter_uid::text = lema.mitarbeiter_uid::text AND tbl_stundenplan.studiengang_kz = lvb.studiengang_kz AND tbl_stundenplan.semester = lvb.semester AND (tbl_stundenplan.verband = lvb.verband OR (tbl_stundenplan.verband IS NULL OR tbl_stundenplan.verband = ''::bpchar) AND lvb.verband IS NULL) AND (tbl_stundenplan.gruppe = lvb.gruppe OR (tbl_stundenplan.gruppe IS NULL OR tbl_stundenplan.gruppe = ''::bpchar) AND lvb.gruppe IS NULL) AND (tbl_stundenplan.gruppe_kurzbz::text = lvb.gruppe_kurzbz::text OR tbl_stundenplan.gruppe_kurzbz IS NULL AND lvb.gruppe_kurzbz IS NULL) AND tbl_stundenplan.lehreinheit_id = lvb.lehreinheit_id) AS verplant
	FROM lehre.tbl_lehreinheit le
	JOIN lehre.tbl_lehreinheitgruppe lvb USING (lehreinheit_id)
	JOIN lehre.tbl_lehreinheitmitarbeiter lema USING (lehreinheit_id)
	JOIN public.tbl_studiengang USING (studiengang_kz)
	JOIN lehre.tbl_lehrfach USING (lehrfach_id)
	JOIN public.tbl_mitarbeiter ma USING (mitarbeiter_uid);
   
	CREATE VIEW campus.vw_student_lehrveranstaltung AS
	SELECT tbl_benutzergruppe.uid, tbl_lehrveranstaltung.zeugnis, tbl_lehrveranstaltung.sort, tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.kurzbz, tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.bezeichnung_english, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.sprache, tbl_lehrveranstaltung.ects, tbl_lehrveranstaltung.semesterstunden, tbl_lehrveranstaltung.anmerkung, tbl_lehrveranstaltung.lehre, tbl_lehrveranstaltung.lehreverzeichnis, tbl_lehrveranstaltung.aktiv, tbl_lehrveranstaltung.planfaktor, tbl_lehrveranstaltung.planlektoren, tbl_lehrveranstaltung.planpersonalkosten, tbl_lehrveranstaltung.plankostenprolektor, tbl_lehrveranstaltung.updateamum, tbl_lehrveranstaltung.updatevon, tbl_lehrveranstaltung.insertamum, tbl_lehrveranstaltung.insertvon, tbl_lehrveranstaltung.ext_id, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.studiensemester_kurzbz, tbl_lehreinheit.lehrfach_id, tbl_lehreinheit.lehrform_kurzbz, tbl_lehreinheit.stundenblockung, tbl_lehreinheit.wochenrythmus, tbl_lehreinheit.start_kw, tbl_lehreinheit.raumtyp, tbl_lehreinheit.raumtypalternativ, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz
	FROM lehre.tbl_lehreinheitgruppe, tbl_benutzergruppe, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
	WHERE tbl_lehreinheitgruppe.gruppe_kurzbz::text = tbl_benutzergruppe.gruppe_kurzbz::text AND tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id AND tbl_lehreinheit.studiensemester_kurzbz::text = tbl_benutzergruppe.studiensemester_kurzbz::text
	UNION 
	SELECT tbl_studentlehrverband.student_uid AS uid, tbl_lehrveranstaltung.zeugnis, tbl_lehrveranstaltung.sort, tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.kurzbz, tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.bezeichnung_english, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.sprache, tbl_lehrveranstaltung.ects, tbl_lehrveranstaltung.semesterstunden, tbl_lehrveranstaltung.anmerkung, tbl_lehrveranstaltung.lehre, tbl_lehrveranstaltung.lehreverzeichnis, tbl_lehrveranstaltung.aktiv, tbl_lehrveranstaltung.planfaktor, tbl_lehrveranstaltung.planlektoren, tbl_lehrveranstaltung.planpersonalkosten, tbl_lehrveranstaltung.plankostenprolektor, tbl_lehrveranstaltung.updateamum, tbl_lehrveranstaltung.updatevon, tbl_lehrveranstaltung.insertamum, tbl_lehrveranstaltung.insertvon, tbl_lehrveranstaltung.ext_id, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.studiensemester_kurzbz, tbl_lehreinheit.lehrfach_id, tbl_lehreinheit.lehrform_kurzbz, tbl_lehreinheit.stundenblockung, tbl_lehreinheit.wochenrythmus, tbl_lehreinheit.start_kw, tbl_lehreinheit.raumtyp, tbl_lehreinheit.raumtypalternativ, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz
	FROM lehre.tbl_lehreinheitgruppe, tbl_studentlehrverband, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
	WHERE tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id AND tbl_lehreinheit.studiensemester_kurzbz::text = tbl_studentlehrverband.studiensemester_kurzbz::text AND tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND tbl_studentlehrverband.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz AND tbl_studentlehrverband.semester = tbl_lehreinheitgruppe.semester AND (btrim(tbl_studentlehrverband.verband::text) = btrim(tbl_lehreinheitgruppe.verband::text) OR (tbl_lehreinheitgruppe.verband IS NULL OR btrim(tbl_lehreinheitgruppe.verband::text) = ''::text) AND tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL) AND (btrim(tbl_studentlehrverband.gruppe::text) = btrim(tbl_lehreinheitgruppe.gruppe::text) OR (tbl_lehreinheitgruppe.gruppe IS NULL OR btrim(tbl_lehreinheitgruppe.gruppe::text) = ''::text) AND tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL);
	
	CREATE VIEW campus.vw_lehreinheit AS
	SELECT tbl_lehrveranstaltung.studiengang_kz AS lv_studiengang_kz, tbl_lehrveranstaltung.semester AS lv_semester, tbl_lehrveranstaltung.kurzbz AS lv_kurzbz, tbl_lehrveranstaltung.bezeichnung AS lv_bezeichnung, tbl_lehrveranstaltung.ects AS lv_ects, tbl_lehrveranstaltung.lehreverzeichnis AS lv_lehreverzeichnis, tbl_lehrveranstaltung.planfaktor AS lv_planfaktor, tbl_lehrveranstaltung.planlektoren AS lv_planlektoren, tbl_lehrveranstaltung.planpersonalkosten AS lv_planpersonalkosten, tbl_lehrveranstaltung.plankostenprolektor AS lv_plankostenprolektor, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrveranstaltung_id, tbl_lehreinheit.studiensemester_kurzbz, tbl_lehreinheit.lehrform_kurzbz, tbl_lehreinheit.stundenblockung, tbl_lehreinheit.wochenrythmus, tbl_lehreinheit.start_kw, tbl_lehreinheit.raumtyp, tbl_lehreinheit.raumtypalternativ, tbl_lehreinheit.lehre, tbl_lehreinheit.unr, tbl_lehreinheit.lvnr, tbl_lehreinheitmitarbeiter.lehrfunktion_kurzbz, tbl_lehreinheit.insertamum, tbl_lehreinheit.insertvon, tbl_lehreinheit.updateamum, tbl_lehreinheit.updatevon, tbl_lehreinheit.lehrfach_id, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehrveranstaltung.aktiv, tbl_lehrfach.sprache, tbl_lehreinheitmitarbeiter.mitarbeiter_uid, tbl_lehreinheitmitarbeiter.semesterstunden, tbl_lehrveranstaltung.semesterstunden AS lv_semesterstunden, tbl_lehreinheitmitarbeiter.planstunden, tbl_lehreinheitmitarbeiter.stundensatz, tbl_lehreinheitmitarbeiter.faktor, tbl_lehreinheit.anmerkung, tbl_mitarbeiter.kurzbz AS lektor, tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe, tbl_lehreinheitgruppe.gruppe_kurzbz, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bez, tbl_studiengang.typ AS stg_typ, tbl_lehreinheitmitarbeiter.anmerkung AS anmerkunglektor, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz
	FROM lehre.tbl_lehreinheit
	JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
	JOIN lehre.tbl_lehrfach USING (lehrfach_id)
	JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)
	JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid)
	JOIN lehre.tbl_lehreinheitgruppe USING (lehreinheit_id)
	JOIN public.tbl_studiengang ON tbl_lehreinheitgruppe.studiengang_kz = tbl_studiengang.studiengang_kz;
	
	CREATE VIEW lehre.vw_stundenplan AS
	SELECT tbl_stundenplan.stundenplan_id, tbl_stundenplan.unr, tbl_stundenplan.mitarbeiter_uid AS uid, tbl_stundenplan.lehreinheit_id, tbl_lehreinheit.lehrfach_id, tbl_stundenplan.datum, tbl_stundenplan.stunde, tbl_stundenplan.ort_kurzbz, tbl_stundenplan.studiengang_kz, tbl_stundenplan.semester, tbl_stundenplan.verband, tbl_stundenplan.gruppe, tbl_stundenplan.gruppe_kurzbz, tbl_stundenplan.titel, tbl_stundenplan.anmerkung, tbl_stundenplan.fix, tbl_lehreinheit.lehrveranstaltung_id, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bezeichnung, tbl_studiengang.typ AS stg_typ, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehreinheit.lehrform_kurzbz AS lehrform, tbl_mitarbeiter.kurzbz AS lektor, tbl_stundenplan.updateamum, tbl_stundenplan.updatevon, tbl_stundenplan.insertamum, tbl_stundenplan.insertvon
	FROM lehre.tbl_stundenplan
	JOIN public.tbl_studiengang USING (studiengang_kz)
	JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
	JOIN lehre.tbl_lehrfach USING (lehrfach_id)
	JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);

	CREATE VIEW lehre.vw_stundenplandev AS
	SELECT tbl_stundenplandev.stundenplandev_id, tbl_stundenplandev.unr, tbl_stundenplandev.mitarbeiter_uid AS uid, tbl_stundenplandev.lehreinheit_id, tbl_lehreinheit.lehrfach_id, tbl_stundenplandev.datum, tbl_stundenplandev.stunde, tbl_stundenplandev.ort_kurzbz, tbl_stundenplandev.studiengang_kz, tbl_stundenplandev.semester, tbl_stundenplandev.verband, tbl_stundenplandev.gruppe, tbl_stundenplandev.gruppe_kurzbz, tbl_stundenplandev.titel, tbl_stundenplandev.anmerkung, tbl_stundenplandev.fix, tbl_lehreinheit.lehrveranstaltung_id, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bezeichnung, tbl_studiengang.typ AS stg_typ, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehreinheit.lehrform_kurzbz AS lehrform, tbl_mitarbeiter.kurzbz AS lektor, tbl_stundenplandev.updateamum, tbl_stundenplandev.updatevon, tbl_stundenplandev.insertamum, tbl_stundenplandev.insertvon
	FROM lehre.tbl_stundenplandev
	JOIN public.tbl_studiengang USING (studiengang_kz)
	JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
	JOIN lehre.tbl_lehrfach USING (lehrfach_id)
	JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);
	
	CREATE VIEW campus.vw_stundenplan AS
	SELECT tbl_stundenplan.stundenplan_id, tbl_stundenplan.unr, tbl_stundenplan.mitarbeiter_uid AS uid, tbl_stundenplan.lehreinheit_id, tbl_lehreinheit.lehrfach_id, tbl_stundenplan.datum, tbl_stundenplan.stunde, tbl_stundenplan.ort_kurzbz, tbl_stundenplan.studiengang_kz, tbl_stundenplan.semester, tbl_stundenplan.verband, tbl_stundenplan.gruppe, tbl_stundenplan.gruppe_kurzbz, tbl_stundenplan.titel, tbl_stundenplan.anmerkung, tbl_stundenplan.fix, tbl_lehreinheit.lehrveranstaltung_id, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bezeichnung, tbl_studiengang.typ AS stg_typ, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehreinheit.lehrform_kurzbz AS lehrform, tbl_mitarbeiter.kurzbz AS lektor, tbl_stundenplan.updateamum, tbl_stundenplan.updatevon, tbl_stundenplan.insertamum, tbl_stundenplan.insertvon
	FROM lehre.tbl_stundenplan
	JOIN public.tbl_studiengang USING (studiengang_kz)
	JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
	JOIN lehre.tbl_lehrfach USING (lehrfach_id)
	JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);
	
	CREATE VIEW lehre.vw_stundenplandev_student_unr AS
	SELECT sub_stpl_uid.unr, sub_stpl_uid.datum, sub_stpl_uid.stunde, sub_stpl_uid.student_uid
	FROM (SELECT stpl.unr, stpl.datum, stpl.stunde, tbl_benutzergruppe.uid AS student_uid
	           FROM lehre.tbl_stundenplandev stpl
	      JOIN public.tbl_benutzergruppe USING (gruppe_kurzbz)
	     WHERE tbl_benutzergruppe.studiensemester_kurzbz::text = ((( SELECT tbl_studiensemester.studiensemester_kurzbz
	              FROM public.tbl_studiensemester
	             WHERE stpl.datum <= tbl_studiensemester.ende AND stpl.datum >= tbl_studiensemester.start))::text)
	     GROUP BY stpl.unr, stpl.datum, stpl.stunde, tbl_benutzergruppe.uid
	UNION 
	     SELECT stpl.unr, stpl.datum, stpl.stunde, tbl_studentlehrverband.student_uid
	           FROM lehre.tbl_stundenplandev stpl
	      JOIN public.tbl_studentlehrverband ON stpl.gruppe_kurzbz IS NULL AND stpl.studiengang_kz = tbl_studentlehrverband.studiengang_kz AND stpl.semester = tbl_studentlehrverband.semester AND (stpl.verband = tbl_studentlehrverband.verband OR stpl.verband = ' '::bpchar AND stpl.verband <> tbl_studentlehrverband.verband) AND (stpl.gruppe = tbl_studentlehrverband.gruppe OR stpl.gruppe = ' '::bpchar AND stpl.gruppe <> tbl_studentlehrverband.gruppe)
	     WHERE tbl_studentlehrverband.studiensemester_kurzbz::text = ((( SELECT tbl_studiensemester.studiensemester_kurzbz
	              FROM public.tbl_studiensemester
	             WHERE stpl.datum <= tbl_studiensemester.ende AND stpl.datum >= tbl_studiensemester.start))::text)
	     GROUP BY stpl.unr, stpl.datum, stpl.stunde, tbl_studentlehrverband.student_uid) sub_stpl_uid
	GROUP BY sub_stpl_uid.unr, sub_stpl_uid.datum, sub_stpl_uid.stunde, sub_stpl_uid.student_uid;

	GRANT SELECT ON campus.vw_persongruppe TO admin;
	GRANT SELECT ON campus.vw_persongruppe TO web;
	GRANT SELECT ON lehre.vw_reservierung TO admin;
	GRANT SELECT ON lehre.vw_reservierung TO web;
	GRANT SELECT ON campus.vw_reservierung TO admin;
	GRANT SELECT ON campus.vw_reservierung TO web;
	GRANT SELECT ON lehre.vw_lva_stundenplan TO admin;
	GRANT SELECT ON lehre.vw_lva_stundenplan TO web;
	GRANT SELECT ON lehre.vw_lva_stundenplandev TO admin;
	GRANT SELECT ON lehre.vw_lva_stundenplandev TO web;
	GRANT SELECT ON campus.vw_student_lehrveranstaltung TO admin;
	GRANT SELECT ON campus.vw_student_lehrveranstaltung TO web;
	GRANT SELECT ON campus.vw_lehreinheit TO admin;
	GRANT SELECT ON campus.vw_lehreinheit TO web;
	GRANT SELECT ON lehre.vw_stundenplan TO admin;
	GRANT SELECT ON lehre.vw_stundenplan TO web;
	GRANT SELECT ON lehre.vw_stundenplandev TO admin;
	GRANT SELECT ON lehre.vw_stundenplandev TO web;
	GRANT SELECT ON campus.vw_stundenplan TO admin;
	GRANT SELECT ON campus.vw_stundenplan TO web;
	GRANT SELECT ON lehre.vw_stundenplandev_student_unr TO admin;
	GRANT SELECT ON lehre.vw_stundenplandev_student_unr TO web;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>tbl_organisationseinheit: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'tbl_organisationseinheit: Spalte mailverteiler hinzugefuegt!<br>';
}

if(!@$db->db_query("SELECT sort FROM lehre.tbl_lehrfunktion LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_lehrfunktion ADD COLUMN sort smallint;";
	
	if(!$db->db_query($qry))
		echo '<strong>tbl_lehrfunktion: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'tbl_lehrfunktion: Spalte sort hinzugefuegt!<br>';
}

if(!@$db->db_query("SELECT * FROM system.tbl_cronjob LIMIT 1;"))
{
	$qry = "
		CREATE TABLE system.tbl_cronjob
		(
			cronjob_id Serial NOT NULL,
			server_kurzbz Character varying(64),
			titel Character varying(64),
			beschreibung Text,
			file Text,
			last_execute Timestamp,
			aktiv Boolean DEFAULT true NOT NULL,
			running Boolean DEFAULT false NOT NULL,
			jahr Character varying(6),
			monat Character varying(4),
			tag Character varying(4),
			wochentag Smallint,
			stunde Character varying(4),
			minute Character varying(4),
			standalone Boolean DEFAULT true NOT NULL,
			reihenfolge Smallint,
			updateamum Timestamp,
			updatevon Character varying(32),
			insertamum Timestamp,
			insertvon Character varying(32),
			variablen text
		);

		ALTER TABLE system.tbl_cronjob ADD CONSTRAINT pk_tbl_cronjob PRIMARY KEY (cronjob_id);

		CREATE TABLE system.tbl_server
		(
 			server_kurzbz Character varying(64) NOT NULL,
 			beschreibung Text
		);
		
		GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_cronjob TO admin;
		GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_server TO admin;
		GRANT SELECT, UPDATE ON system.tbl_cronjob_cronjob_id_seq TO admin;
		";
	
	if(!$db->db_query($qry))
		echo '<strong>tbl_cronjob: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'tbl_cronjob: hinzugefuegt!<br>';
}

//Nummerintern wird in Inventarnummer umbenannt
if(!@$db->db_query("SELECT inventarnummer FROM wawi.tbl_betriebsmittel"))
{
	$qry = "ALTER TABLE wawi.tbl_betriebsmittel RENAME nummerintern TO inventarnummer;
			COMMENT ON COLUMN wawi.tbl_betriebsmittel.inventarnummer IS 'Inventarnummer';
			UPDATE wawi.tbl_betriebsmittel SET inventarnummer=null;
			ALTER TABLE wawi.tbl_betriebsmittel ADD CONSTRAINT uk_betriebsmittel_inventarnummer UNIQUE (inventarnummer);
			";
	
	if(!$db->db_query($qry))
		echo '<strong>tbl_betriebsmittel: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'tbl_betriebsmittel: nummerintern wurde in inventarnummer umbenannt!<br>';
}

//zusaetzliche Spalten fuer campus.vw_student
if(!@$db->db_query('SELECT updateaktivam FROM campus.vw_student LIMIT 1'))
{
	$qry = "DROP VIEW campus.vw_student;
			CREATE VIEW campus.vw_student AS
				SELECT 
					tbl_benutzer.uid, tbl_student.matrikelnr, tbl_student.prestudent_id, tbl_student.studiengang_kz, 
					tbl_student.semester, tbl_student.verband, tbl_student.gruppe, tbl_benutzer.person_id, 
					tbl_benutzer.alias, tbl_person.geburtsnation, tbl_person.sprache, tbl_person.anrede, 
					tbl_person.titelpost, tbl_person.titelpre, tbl_person.nachname, tbl_person.vorname, 
					tbl_person.vornamen, tbl_person.gebdatum, tbl_person.gebort, tbl_person.gebzeit, 
					tbl_person.foto, tbl_person.anmerkung, tbl_person.homepage, tbl_person.svnr, 
					tbl_person.ersatzkennzeichen, tbl_person.geschlecht, tbl_person.familienstand, 
					tbl_person.anzahlkinder, tbl_benutzer.aktiv, tbl_student.updateamum, tbl_student.updatevon, 
					tbl_student.insertamum, tbl_student.insertvon, tbl_student.ext_id, 
					tbl_benutzer.updateaktivam, tbl_benutzer.updateaktivvon
				FROM public.tbl_student
				JOIN public.tbl_benutzer ON (tbl_student.student_uid=tbl_benutzer.uid)
				JOIN public.tbl_person USING (person_id);
			GRANT SELECT ON campus.vw_student TO admin;
			GRANT SELECT ON campus.vw_student TO web;
			";
	if(!$db->db_query($qry))
		echo '<strong>vw_student: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'vw_student: updateaktivam und updateaktivvon hinzugefuegt!<br>';
}

//eine eindeutige ID wird fuer alle Gruppen hinzugefuegt um diese leichter mit LDAP zu Syncronisieren
if(!@$db->db_query('SELECT gid FROM public.tbl_gruppe LIMIT 1'))
{
	$qry = "
	CREATE SEQUENCE public.seq_gruppe_gid
	 INCREMENT BY 1
	 START WITH 50000
	 NO MAXVALUE
	 NO MINVALUE
	 CACHE 1
	;
	GRANT SELECT, UPDATE ON public.seq_gruppe_gid TO admin;
	GRANT SELECT, UPDATE ON public.seq_gruppe_gid TO web;
	
	ALTER TABLE public.tbl_gruppe ADD COLUMN gid bigint DEFAULT nextval('public.seq_gruppe_gid');
	ALTER TABLE public.tbl_lehrverband ADD COLUMN gid bigint DEFAULT nextval('public.seq_gruppe_gid');
	
	UPDATE public.tbl_gruppe SET gid=nextval('public.seq_gruppe_gid');
	UPDATE public.tbl_lehrverband SET gid=nextval('public.seq_gruppe_gid');
	
	ALTER TABLE public.tbl_gruppe ALTER COLUMN gid SET NOT NULL;
	ALTER TABLE public.tbl_lehrverband ALTER COLUMN gid SET NOT NULL;
	
	ALTER TABLE public.tbl_gruppe ADD CONSTRAINT uk_gruppe_gid UNIQUE (gid);
	ALTER TABLE public.tbl_lehrverband ADD CONSTRAINT uk_lehrverbandsgruppe_gid UNIQUE (gid);
	
	--TRIGGER
	CREATE FUNCTION check_unique_gid() RETURNS trigger AS '
		DECLARE
			id INTEGER;
		BEGIN
			
			IF TG_RELNAME=''tbl_gruppe'' THEN
				SELECT INTO id gid FROM public.tbl_lehrverband WHERE gid=NEW.gid;
				
				IF NOT FOUND THEN
					RETURN NEW;
				ELSE
					 RAISE EXCEPTION ''GID Nummer wird bereits in tbl_lehrverband verwendet.'';
					 RETURN NULL;
				END IF;
			ELSE
				SELECT INTO id gid FROM public.tbl_gruppe WHERE gid=NEW.gid;

				IF NOT FOUND THEN
					RETURN NEW;
				ELSE
					 RAISE EXCEPTION ''GID Nummer wird bereits in tbl_gruppe verwendet.'';
					 RETURN NULL;
				END IF;
			END IF;
		END;
	' LANGUAGE 'plpgsql';
	
	CREATE TRIGGER tr_gruppe_unique_gid BEFORE INSERT OR UPDATE ON public.tbl_gruppe FOR EACH ROW EXECUTE PROCEDURE check_unique_gid();
	CREATE TRIGGER tr_lehrverband_unique_gid BEFORE INSERT OR UPDATE ON public.tbl_lehrverband FOR EACH ROW EXECUTE PROCEDURE check_unique_gid();
	
	CREATE OR REPLACE VIEW public.vw_gruppen AS
	SELECT 
		gid, gruppe_kurzbz, uid, mailgrp, beschreibung,
		tbl_gruppe.studiengang_kz, tbl_gruppe.semester, studiensemester_kurzbz, null as verband, null as gruppe
	FROM
		public.tbl_gruppe LEFT JOIN public.tbl_benutzergruppe USING(gruppe_kurzbz)
	UNION
	SELECT
		gid, 
		UPPER(trim(
				(
				SELECT typ || kurzbz FROM public.tbl_studiengang 
			 	WHERE studiengang_kz=tbl_lehrverband.studiengang_kz
			 	) 
				|| tbl_lehrverband.semester 
				|| tbl_lehrverband.verband 
				|| tbl_lehrverband.gruppe
			 )) as gruppe_kurzbz, 
		student_uid, true, bezeichnung as beschreibung, 
		tbl_lehrverband.studiengang_kz, tbl_lehrverband.semester, studiensemester_kurzbz, tbl_lehrverband.verband, tbl_lehrverband.gruppe
	FROM
		public.tbl_lehrverband LEFT JOIN public.tbl_studentlehrverband USING(studiengang_kz, semester)
	WHERE
		(
			tbl_lehrverband.verband=tbl_studentlehrverband.verband 
			OR tbl_lehrverband.verband is null 
			OR trim(tbl_lehrverband.verband)='' 
			OR tbl_studentlehrverband.verband is null
		)
		AND
		(
			tbl_lehrverband.gruppe=tbl_studentlehrverband.gruppe 
			OR tbl_lehrverband.gruppe is null 
			OR trim(tbl_lehrverband.gruppe)='' 
			OR tbl_studentlehrverband.gruppe is null
		);
	GRANT SELECT ON public.vw_gruppen TO admin;
	GRANT SELECT ON public.vw_gruppen TO web;
	
	CREATE INDEX idx_benutzer_aktiv ON public.tbl_benutzer (aktiv);
	";
	
	if(!$db->db_query($qry))
		echo '<strong>tbl_gruppe/tbl_lehrverband: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'tbl_gruppe/tbl_lehrverband: GID wurde hinzugefuegt! (inklusive Trigger und View)<br>';
}

//Spalte incoming zur Lehrveranstaltung hinzufuegen. Legt fest wie viele Incoming an der LV teilnehmen duerfen
if(!@$db->db_query('SELECT incoming FROM lehre.tbl_lehrveranstaltung LIMIT 1'))
{
	$qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN incoming smallint DEFAULT null;";
	
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'lehre.tbl_lehrveranstaltung: Spalte incoming hinzugefuegt<br>';
}

// Spalte mischform zum Studiengang hinzufuegen.
if(!@$db->db_query('SELECT mischform FROM public.tbl_studiengang LIMIT 1'))
{
	$qry = "ALTER TABLE public.tbl_studiengang ADD COLUMN mischform boolean DEFAULT false;
			UPDATE public.tbl_studiengang SET mischform=true WHERE orgform_kurzbz='VBB';
			ALTER TABLE public.tbl_studiengang ALTER COLUMN mischform SET NOT NULL;
			ALTER TABLE bis.tbl_orgform DROP CONSTRAINT tbl_orgform_code_key;";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_studiengang: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_studiengang: Spalte mischform hinzugefuegt<br>';
}

// Spalten fuer Inventur zu Betriebsmitteln hinzufuegen
if(!@$db->db_query('SELECT inventuramum FROM wawi.tbl_betriebsmittel LIMIT 1'))
{
	$qry = "ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN inventuramum timestamp;
			ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN inventurvon varchar(32);
			";
	
	if(!$db->db_query($qry))
		echo '<strong>wawi.tbl_betriebsmittel: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'wawi.tbl_betriebsmittel: Spalten inventuramum und inventurvon hinzugefuegt<br>';
}

// Spalten fuer Inventur zu Betriebsmitteln hinzufuegen
if(!@$db->db_query('SELECT lv_bezeichnung_english FROM campus.vw_lehreinheit LIMIT 1'))
{
	$qry=" 
		DROP VIEW campus.vw_lehreinheit;
		CREATE VIEW campus.vw_lehreinheit AS
		SELECT 
			tbl_lehrveranstaltung.studiengang_kz AS lv_studiengang_kz, tbl_lehrveranstaltung.semester AS lv_semester, 
			tbl_lehrveranstaltung.kurzbz AS lv_kurzbz, tbl_lehrveranstaltung.bezeichnung AS lv_bezeichnung, 
			tbl_lehrveranstaltung.ects AS lv_ects, tbl_lehrveranstaltung.lehreverzeichnis AS lv_lehreverzeichnis, 
			tbl_lehrveranstaltung.planfaktor AS lv_planfaktor, tbl_lehrveranstaltung.planlektoren AS lv_planlektoren, 
			tbl_lehrveranstaltung.planpersonalkosten AS lv_planpersonalkosten, 
			tbl_lehrveranstaltung.plankostenprolektor AS lv_plankostenprolektor, tbl_lehreinheit.lehreinheit_id, 
			tbl_lehreinheit.lehrveranstaltung_id, tbl_lehreinheit.studiensemester_kurzbz, tbl_lehreinheit.lehrform_kurzbz, 
			tbl_lehreinheit.stundenblockung, tbl_lehreinheit.wochenrythmus, tbl_lehreinheit.start_kw, tbl_lehreinheit.raumtyp, 
			tbl_lehreinheit.raumtypalternativ, tbl_lehreinheit.lehre, tbl_lehreinheit.unr, tbl_lehreinheit.lvnr, 
			tbl_lehreinheitmitarbeiter.lehrfunktion_kurzbz, tbl_lehreinheit.insertamum, tbl_lehreinheit.insertvon, 
			tbl_lehreinheit.updateamum, tbl_lehreinheit.updatevon, tbl_lehreinheit.lehrfach_id, tbl_lehrfach.fachbereich_kurzbz, 
			tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, 
			tbl_lehrveranstaltung.aktiv, tbl_lehrfach.sprache, tbl_lehreinheitmitarbeiter.mitarbeiter_uid, 
			tbl_lehreinheitmitarbeiter.semesterstunden, tbl_lehrveranstaltung.semesterstunden AS lv_semesterstunden, 
			tbl_lehreinheitmitarbeiter.planstunden, tbl_lehreinheitmitarbeiter.stundensatz, tbl_lehreinheitmitarbeiter.faktor, 
			tbl_lehreinheit.anmerkung, tbl_mitarbeiter.kurzbz AS lektor, tbl_lehreinheitgruppe.studiengang_kz, 
			tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe, 
			tbl_lehreinheitgruppe.gruppe_kurzbz, tbl_studiengang.kurzbz AS stg_kurzbz, 
			tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bez, tbl_studiengang.typ AS stg_typ, 
			tbl_lehreinheitmitarbeiter.anmerkung AS anmerkunglektor, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz, 
			tbl_lehrveranstaltung.bezeichnung_english as lv_bezeichnung_english
		FROM lehre.tbl_lehreinheit
			JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
			JOIN lehre.tbl_lehrfach USING (lehrfach_id)
			JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)
			JOIN tbl_mitarbeiter USING (mitarbeiter_uid)
			JOIN lehre.tbl_lehreinheitgruppe USING (lehreinheit_id)
			JOIN tbl_studiengang ON tbl_lehreinheitgruppe.studiengang_kz = tbl_studiengang.studiengang_kz;
	   	GRANT SELECT ON campus.vw_lehreinheit TO web;
		GRANT SELECT ON campus.vw_lehreinheit TO admin;";	
	
	if(!$db->db_query($qry))
		echo '<strong>campus.vw_lehreinheit: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'campus.vw_lehreinheit: Spalte lv_bezeichunng_english hinzugefuegt<br>';
}

//Ort bezeichnung von 30 auf 64 Zeichen verlaengern
if($result = $db->db_query("SELECT character_maximum_length FROM information_schema.columns WHERE column_name='bezeichnung' AND table_name='tbl_ort' AND table_schema='public';"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->character_maximum_length==30)
		{
			$qry = "ALTER TABLE public.tbl_ort ALTER COLUMN bezeichnung TYPE varchar(64);";
			if(!$db->db_query($qry))
				echo '<strong>public.tbl_ort: '.$db->db_last_error().'</strong><br>';
			else 
				echo 'public.tbl_ort: Spalte bezeichnung auf 64 Zeichen verlaengert<br>';
		}
	}
}


//Raumtyp_kurzbz von 8 auf 16 Zeichen verlaengern
if($result = $db->db_query("SELECT character_maximum_length FROM information_schema.columns WHERE column_name='raumtyp_kurzbz' AND table_name='tbl_raumtyp' AND table_schema='public';"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->character_maximum_length==8)
		{
			$qry = "
			ALTER TABLE public.tbl_raumtyp ALTER COLUMN raumtyp_kurzbz TYPE varchar(16);
			ALTER TABLE public.tbl_ortraumtyp ALTER COLUMN raumtyp_kurzbz TYPE varchar(16);";
			if(!$db->db_query($qry))
				echo '<strong>public.tbl_ort: '.$db->db_last_error().'</strong><br>';
			else 
				echo 'public.tbl_raumtyp: Spalte raumtyp_kurzbz auf 16 Zeichen verlaengert<br>';
		}
	}
}

//bezeichnung_english fuer tbl_abschlussbeurteilung hinzugefuegt
if(!@$db->db_query("SELECT bezeichnung_english FROM lehre.tbl_abschlussbeurteilung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_abschlussbeurteilung ADD COLUMN bezeichnung_english varchar(64);";
	
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlussbeurteilung: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'lehre.tbl_abschlussbeurteilung: Spalte bezeichnung_english hinzugefuegt<br>';
}

//orgform_kurzbz zu vw_lehreinheit hinzufuegen
if(!@$db->db_query("SELECT lv_orgform_kurzbz FROM campus.vw_lehreinheit LIMIT 1;"))
{
	$qry = "DROP VIEW campus.vw_lehreinheit;
	CREATE VIEW campus.vw_lehreinheit AS
	SELECT 
		tbl_lehrveranstaltung.studiengang_kz AS lv_studiengang_kz, tbl_lehrveranstaltung.semester AS lv_semester, tbl_lehrveranstaltung.kurzbz AS lv_kurzbz, tbl_lehrveranstaltung.bezeichnung AS lv_bezeichnung, tbl_lehrveranstaltung.ects AS lv_ects, tbl_lehrveranstaltung.lehreverzeichnis AS lv_lehreverzeichnis, tbl_lehrveranstaltung.planfaktor AS lv_planfaktor, tbl_lehrveranstaltung.planlektoren AS lv_planlektoren, tbl_lehrveranstaltung.planpersonalkosten AS lv_planpersonalkosten, tbl_lehrveranstaltung.plankostenprolektor AS lv_plankostenprolektor, tbl_lehrveranstaltung.orgform_kurzbz as lv_orgform_kurzbz, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrveranstaltung_id, tbl_lehreinheit.studiensemester_kurzbz, tbl_lehreinheit.lehrform_kurzbz, tbl_lehreinheit.stundenblockung, tbl_lehreinheit.wochenrythmus, tbl_lehreinheit.start_kw, tbl_lehreinheit.raumtyp, tbl_lehreinheit.raumtypalternativ, tbl_lehreinheit.lehre, tbl_lehreinheit.unr, tbl_lehreinheit.lvnr, tbl_lehreinheitmitarbeiter.lehrfunktion_kurzbz, tbl_lehreinheit.insertamum, tbl_lehreinheit.insertvon, tbl_lehreinheit.updateamum, tbl_lehreinheit.updatevon, tbl_lehreinheit.lehrfach_id, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehrveranstaltung.aktiv, tbl_lehrfach.sprache, tbl_lehreinheitmitarbeiter.mitarbeiter_uid, tbl_lehreinheitmitarbeiter.semesterstunden, tbl_lehrveranstaltung.semesterstunden AS lv_semesterstunden, tbl_lehreinheitmitarbeiter.planstunden, tbl_lehreinheitmitarbeiter.stundensatz, tbl_lehreinheitmitarbeiter.faktor, tbl_lehreinheit.anmerkung, tbl_mitarbeiter.kurzbz AS lektor, tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe, tbl_lehreinheitgruppe.gruppe_kurzbz, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bez, tbl_studiengang.typ AS stg_typ, tbl_lehreinheitmitarbeiter.anmerkung AS anmerkunglektor, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz, tbl_lehrveranstaltung.bezeichnung_english AS lv_bezeichnung_english
	FROM lehre.tbl_lehreinheit
		JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
		JOIN lehre.tbl_lehrfach USING (lehrfach_id)
		JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)
		JOIN tbl_mitarbeiter USING (mitarbeiter_uid)
		JOIN lehre.tbl_lehreinheitgruppe USING (lehreinheit_id)
		JOIN tbl_studiengang ON tbl_lehreinheitgruppe.studiengang_kz = tbl_studiengang.studiengang_kz;
	GRANT SELECT ON campus.vw_lehreinheit TO web;
	GRANT SELECT ON campus.vw_lehreinheit TO admin ;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>campus.vw_lehreinheit: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'campus.vw_lehreinheit: Spalte lv_orgform_kurzbz hinzugefuegt<br>';
}

//WaWi
if(!@$db->db_query('SELECT * FROM wawi.tbl_konto LIMIT 1'))
{
	$qry = "
			-- Konto
			CREATE TABLE wawi.tbl_konto
			(
				konto_id bigint NOT NULL,
				kontonr varchar(32),
				beschreibung varchar(256)[],
				kurzbz varchar(32),
				aktiv boolean NOT NULL,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);
			
			CREATE SEQUENCE wawi.seq_konto_konto_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			ALTER TABLE wawi.tbl_konto ADD CONSTRAINT pk_wawi_konto PRIMARY KEY (konto_id);
			ALTER TABLE wawi.tbl_konto ALTER COLUMN konto_id SET DEFAULT nextval('wawi.seq_konto_konto_id');
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_konto TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_konto_konto_id TO admin;
			
			-- Kostenstelle
			CREATE TABLE wawi.tbl_kostenstelle
			(
				kostenstelle_id bigint NOT NULL,
				oe_kurzbz varchar(32),
				bezeichnung varchar(256),
				kurzbz varchar(32),
				aktiv boolean NOT NULL,
				updateamum timestamp,
				updatevon varchar(32),
				insertamum timestamp,
				insertvon varchar(32),
				ext_id bigint,
				kostenstelle_nr	varchar(4),
				deaktiviertvon varchar(32),
				deaktiviertamum timestamp
			);
			
			CREATE SEQUENCE wawi.seq_kostenstelle_kostenstelle_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			ALTER TABLE wawi.tbl_kostenstelle ADD CONSTRAINT pk_wawi_kostenstelle PRIMARY KEY (kostenstelle_id);
			ALTER TABLE wawi.tbl_kostenstelle ALTER COLUMN kostenstelle_id SET DEFAULT nextval('wawi.seq_kostenstelle_kostenstelle_id');			
			ALTER TABLE wawi.tbl_kostenstelle ADD CONSTRAINT fk_kostenstelle_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_kostenstelle TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_kostenstelle_kostenstelle_id TO admin;
			
			-- KontoKostenstelle
			
			CREATE TABLE wawi.tbl_konto_kostenstelle
			(
				konto_id bigint,
				kostenstelle_id bigint,
				insertamum timestamp,
				insertvon varchar(32)
			);
			
			ALTER TABLE wawi.tbl_konto_kostenstelle ADD CONSTRAINT pk_wawi_konto_kostenstelle PRIMARY KEY (konto_id, kostenstelle_id);
			ALTER TABLE wawi.tbl_konto_kostenstelle ADD CONSTRAINT fk_konto_kostenstelle_konto FOREIGN KEY (konto_id) REFERENCES wawi.tbl_konto (konto_id) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_konto_kostenstelle ADD CONSTRAINT fk_konto_kostenstelle_kostenstelle FOREIGN KEY (kostenstelle_id) REFERENCES wawi.tbl_kostenstelle (kostenstelle_id) ON DELETE CASCADE ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_konto_kostenstelle TO admin;
			
			-- Bestellung
			CREATE TABLE wawi.tbl_bestellung
			(
				bestellung_id bigint NOT NULL,
				bestell_nr varchar(16),
				titel varchar(256),
				bemerkung varchar(256),
				liefertermin varchar(16),
				besteller_uid varchar(32),
				lieferadresse bigint,
				kostenstelle_id bigint,
				konto_id bigint,
				rechnungsadresse bigint,
				firma_id bigint,
				freigegeben boolean NOT NULL DEFAULT false,
				updateamum timestamp,
				updatevon varchar(32),
				insertamum timestamp,
				insertvon varchar(32),
				ext_id bigint
			);

			CREATE SEQUENCE wawi.seq_bestellung_bestellung_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			ALTER TABLE wawi.tbl_bestellung ADD CONSTRAINT pk_wawi_bestellung PRIMARY KEY (bestellung_id);
			ALTER TABLE wawi.tbl_bestellung ALTER COLUMN bestellung_id SET DEFAULT nextval('wawi.seq_bestellung_bestellung_id');			
			ALTER TABLE wawi.tbl_bestellung ADD CONSTRAINT fk_bestellung_beteller_uid FOREIGN KEY (besteller_uid) REFERENCES public.tbl_benutzer (uid) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_bestellung ADD CONSTRAINT fk_bestellung_lieferadresse FOREIGN KEY (lieferadresse) REFERENCES public.tbl_adresse (adresse_id) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_bestellung ADD CONSTRAINT fk_bestellung_rechnungsadresse FOREIGN KEY (rechnungsadresse) REFERENCES public.tbl_adresse (adresse_id) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_bestellung ADD CONSTRAINT fk_bestellung_kostenstelle FOREIGN KEY (kostenstelle_id) REFERENCES wawi.tbl_kostenstelle (kostenstelle_id) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_bestellung ADD CONSTRAINT fk_bestellung_konto FOREIGN KEY (konto_id) REFERENCES wawi.tbl_konto (konto_id) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_bestellung ADD CONSTRAINT fk_bestellung_firma FOREIGN KEY (firma_id) REFERENCES public.tbl_firma (firma_id) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestellung TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_bestellung_bestellung_id TO admin;
			
			-- Bestelldetail
			

			CREATE TABLE wawi.tbl_bestelldetail
			(
				bestelldetail_id bigint NOT NULL,
				bestellung_id bigint NOT NULL,
				position integer,
				menge integer,
				verpackungseinheit varchar(16),
				beschreibung text,
				artikelnummer varchar(32),
				preisprove numeric(12,4),
				mwst numeric(4,2),
				erhalten boolean NOT NULL,
				sort integer,
				text boolean NOT NULL,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);
			
			CREATE SEQUENCE wawi.seq_bestelldetail_bestelldetail_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			ALTER TABLE wawi.tbl_bestelldetail ADD CONSTRAINT pk_wawi_bestelldetail PRIMARY KEY (bestelldetail_id);
			ALTER TABLE wawi.tbl_bestelldetail ALTER COLUMN bestelldetail_id SET DEFAULT nextval('wawi.seq_bestelldetail_bestelldetail_id');			
			ALTER TABLE wawi.tbl_bestelldetail ADD CONSTRAINT fk_bestelldetail_bestellung FOREIGN KEY (bestellung_id) REFERENCES wawi.tbl_bestellung (bestellung_id) ON DELETE CASCADE ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestelldetail TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_bestelldetail_bestelldetail_id TO admin;
			
			-- Rechnung
			
			CREATE TABLE wawi.tbl_rechnungstyp
			(
				rechnungstyp_kurzbz varchar(32),
				beschreibung varchar(256),
				berechtigung_kurzbz varchar(32)
			);
			
			ALTER TABLE wawi.tbl_rechnungstyp ADD CONSTRAINT pk_wawi_rechnungstyp PRIMARY KEY (rechnungstyp_kurzbz);
			ALTER TABLE wawi.tbl_rechnungstyp ADD CONSTRAINT fk_berechtigung_rechnungstyp FOREIGN KEY (berechtigung_kurzbz) REFERENCES system.tbl_berechtigung (berechtigung_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_rechnungstyp TO admin;
			
			CREATE TABLE wawi.tbl_rechnung
			(
				rechnung_id bigint NOT NULL,
				bestellung_id integer,
				rechnungstyp_kurzbz varchar(32),
				buchungsdatum date,
				rechnungsnr varchar(32),
				rechnungsdatum date,
				transfer_datum date,
				buchungstext text,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32),
				freigegeben boolean NOT NULL,
				freigegebenamum timestamp,
				freigegebenvon varchar(32)
			);
			
			CREATE SEQUENCE wawi.seq_rechnung_rechnung_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			ALTER TABLE wawi.tbl_rechnung ADD CONSTRAINT pk_wawi_rechnung PRIMARY KEY (rechnung_id);
			ALTER TABLE wawi.tbl_rechnung ALTER COLUMN rechnung_id SET DEFAULT nextval('wawi.seq_rechnung_rechnung_id');			
			ALTER TABLE wawi.tbl_rechnung ADD CONSTRAINT fk_rechnung_bestellung FOREIGN KEY (bestellung_id) REFERENCES wawi.tbl_bestellung (bestellung_id) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_rechnung ADD CONSTRAINT fk_rechnung_rechnungstyp FOREIGN KEY (rechnungstyp_kurzbz) REFERENCES wawi.tbl_rechnungstyp (rechnungstyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_rechnung TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_rechnung_rechnung_id TO admin;
			
			-- Rechnungsbetrag
			
			CREATE TABLE wawi.tbl_rechnungsbetrag
			(
				rechnungsbetrag_id bigint NOT NULL,
				rechnung_id bigint,
				mwst numeric(4,2),
				betrag numeric(12,2),
				bezeichnung text,
				ext_id integer
			);
			
			CREATE SEQUENCE wawi.seq_rechnungsbetrag_rechnungsbetrag_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			ALTER TABLE wawi.tbl_rechnungsbetrag ADD CONSTRAINT pk_wawi_rechnungsbetrag PRIMARY KEY (rechnungsbetrag_id);
			ALTER TABLE wawi.tbl_rechnungsbetrag ALTER COLUMN rechnungsbetrag_id SET DEFAULT nextval('wawi.seq_rechnungsbetrag_rechnungsbetrag_id');			
			ALTER TABLE wawi.tbl_rechnungsbetrag ADD CONSTRAINT fk_rechnungsbetrag_rechnung FOREIGN KEY (rechnung_id) REFERENCES wawi.tbl_rechnung (rechnung_id) ON DELETE CASCADE ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_rechnungsbetrag TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_rechnungsbetrag_rechnungsbetrag_id TO admin;
			
			CREATE TABLE wawi.tbl_budget
			(
				geschaeftsjahr_kurzbz varchar(32) NOT NULL,
				kostenstelle_id bigint NOT NULL,
				budget numeric(12,2) NOT NULL
			);
			
			ALTER TABLE wawi.tbl_budget ADD CONSTRAINT pk_budget PRIMARY KEY (geschaeftsjahr_kurzbz, kostenstelle_id);
		
			ALTER TABLE wawi.tbl_budget ADD CONSTRAINT fk_budget_kostenstelle FOREIGN KEY(kostenstelle_id) REFERENCES wawi.tbl_kostenstelle (kostenstelle_id) ON DELETE CASCADE ON UPDATE CASCADE;
						
			GRANT SELECT, INSERT, DELETE, UPDATE ON wawi.tbl_budget TO admin;
			GRANT SELECT, INSERT, UPDATE, DELETE ON wawi.tbl_budget TO wawi;
			-- Projekt Bestellung
			
			CREATE TABLE wawi.tbl_projekt_bestellung
			(
				projekt_kurzbz varchar(16) NOT NULL,
				bestellung_id bigint NOT NULL,
				anteil numeric(5,2)
			);
			
			ALTER TABLE wawi.tbl_projekt_bestellung ADD CONSTRAINT pk_wawi_projekt_bestellung PRIMARY KEY (projekt_kurzbz, bestellung_id);
			ALTER TABLE wawi.tbl_projekt_bestellung ADD CONSTRAINT fk_projekt_bestellung_bestellung FOREIGN KEY (bestellung_id) REFERENCES wawi.tbl_bestellung (bestellung_id) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_projekt_bestellung ADD CONSTRAINT fk_projekt_bestellung_projekt FOREIGN KEY (projekt_kurzbz) REFERENCES fue.tbl_projekt (projekt_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_projekt_bestellung TO admin;
			
			-- Bestellstatus
			
			CREATE TABLE wawi.tbl_bestellstatus
			(
				bestellstatus_kurzbz varchar(32) NOT NULL,
				beschreibung varchar(256)				
			);
			
			ALTER TABLE wawi.tbl_bestellstatus ADD CONSTRAINT pk_wawi_bestellstatus PRIMARY KEY (bestellstatus_kurzbz);
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestellstatus TO admin;
			
			INSERT INTO wawi.tbl_bestellstatus(bestellstatus_kurzbz, beschreibung) VALUES('Freigabe','Freigabe der Bestellung');
			INSERT INTO wawi.tbl_bestellstatus(bestellstatus_kurzbz, beschreibung) VALUES('Storno','Stornierung einer Bestellung');
			INSERT INTO wawi.tbl_bestellstatus(bestellstatus_kurzbz, beschreibung) VALUES('Lieferung','Ware wurde geliefert');
			INSERT INTO wawi.tbl_bestellstatus(bestellstatus_kurzbz, beschreibung) VALUES('Bestellung','Ware wurde bestellt');
			INSERT INTO wawi.tbl_bestellstatus(bestellstatus_kurzbz, beschreibung) VALUES('Abgeschickt','Bestellvorgang wurde eingeleitet');
			
			CREATE TABLE wawi.tbl_bestellung_bestellstatus
			(
				bestellung_bestellstatus_id bigint NOT NULL,
				bestellung_id bigint NOT NULL,
				bestellstatus_kurzbz varchar(32) NOT NULL,
				uid varchar(32),
				oe_kurzbz varchar(32),
				datum date,
				insertvon varchar(32),
				insertamum timestamp,
				updatevon varchar(32),
				updateamum timestamp
			);
			
			CREATE SEQUENCE wawi.seq_bestellung_bestellstatus_bestellung_bestellstatus_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			ALTER TABLE wawi.tbl_bestellung_bestellstatus ADD CONSTRAINT pk_wawi_bestellung_bestellstatus PRIMARY KEY (bestellung_bestellstatus_id);
			ALTER TABLE wawi.tbl_bestellung_bestellstatus ADD CONSTRAINT fk_bestellung_bestellstatus_bestellung FOREIGN KEY (bestellung_id) REFERENCES wawi.tbl_bestellung (bestellung_id) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_bestellung_bestellstatus ADD CONSTRAINT fk_bestellung_bestellstatus_bestellstatus FOREIGN KEY (bestellstatus_kurzbz) REFERENCES wawi.tbl_bestellstatus (bestellstatus_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_bestellung_bestellstatus ADD CONSTRAINT fk_bestellung_bestellstatus_benutzer FOREIGN KEY (uid) REFERENCES public.tbl_benutzer (uid) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_bestellung_bestellstatus ADD CONSTRAINT fk_bestellung_bestellstatus_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			ALTER TABLE wawi.tbl_bestellung_bestellstatus ALTER COLUMN bestellung_bestellstatus_id SET DEFAULT nextval('wawi.seq_bestellung_bestellstatus_bestellung_bestellstatus_id');
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestellung_bestellstatus TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_bestellung_bestellstatus_bestellung_bestellstatus_id TO admin;
			
			-- Tags
			
			CREATE TABLE wawi.tbl_bestellungtag
			(
				tag varchar(128) NOT NULL,
				bestellung_id bigint NOT NULL,
				insertamum timestamp,
				insertvon varchar(32)
			);
			
			ALTER TABLE wawi.tbl_bestellungtag ADD CONSTRAINT pk_wawi_bestellungtag PRIMARY KEY (tag, bestellung_id);
			ALTER TABLE wawi.tbl_bestellungtag ADD CONSTRAINT fk_bestellungtag_tag FOREIGN KEY (tag) REFERENCES public.tbl_tag (tag) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_bestellungtag ADD CONSTRAINT fk_bestellungtag_bestellung FOREIGN KEY (bestellung_id) REFERENCES wawi.tbl_bestellung (bestellung_id) ON DELETE CASCADE ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestellungtag TO admin;

			CREATE TABLE wawi.tbl_bestelldetailtag
			(
				tag varchar(128) NOT NULL,
				bestelldetail_id bigint NOT NULL,
				insertamum timestamp,
				insertvon varchar(32)
			);
			
			ALTER TABLE wawi.tbl_bestelldetailtag ADD CONSTRAINT pk_wawi_bestelldetailtag PRIMARY KEY (tag, bestelldetail_id);
			ALTER TABLE wawi.tbl_bestelldetailtag ADD CONSTRAINT fk_bestelldetailtag_tag FOREIGN KEY (tag) REFERENCES public.tbl_tag (tag) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_bestelldetailtag ADD CONSTRAINT fk_bestelldetailtag_bestellungdetail FOREIGN KEY (bestelldetail_id) REFERENCES wawi.tbl_bestelldetail (bestelldetail_id) ON DELETE CASCADE ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestelldetailtag TO admin;
			
			-- Aufteilung
			
			CREATE TABLE wawi.tbl_aufteilung_default
			(
				aufteilung_id bigint,
				kostenstelle_id bigint,
				oe_kurzbz varchar(32),
				anteil numeric(5,2),
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);
			
			CREATE SEQUENCE wawi.seq_aufteilung_default_aufteilung_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			ALTER TABLE wawi.tbl_aufteilung_default ADD CONSTRAINT pk_wawi_aufteilung_default PRIMARY KEY (aufteilung_id);
			ALTER TABLE wawi.tbl_aufteilung_default ADD CONSTRAINT fk_aufteilung_default_kostenstelle FOREIGN KEY (kostenstelle_id) REFERENCES wawi.tbl_kostenstelle (kostenstelle_id) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_aufteilung_default ADD CONSTRAINT fk_aufteilung_default_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_aufteilung_default ALTER COLUMN aufteilung_id SET DEFAULT nextval('wawi.seq_aufteilung_default_aufteilung_id');
			
			ALTER TABLE wawi.tbl_aufteilung_default ALTER COLUMN kostenstelle_id SET NOT NULL;
			ALTER TABLE wawi.tbl_aufteilung_default ALTER COLUMN oe_kurzbz SET NOT NULL;
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_aufteilung_default TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_aufteilung_default_aufteilung_id TO admin;
			
			CREATE TABLE wawi.tbl_aufteilung
			(
				aufteilung_id bigint,
				bestellung_id bigint,
				oe_kurzbz varchar(32),
				anteil numeric(5,2),
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);
			
			CREATE SEQUENCE wawi.seq_aufteilung_aufteilung_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			CREATE INDEX idx_aufteilung_bestellung_id ON wawi.tbl_aufteilung USING btree (bestellung_id); 
			
			
			ALTER TABLE wawi.tbl_aufteilung ADD CONSTRAINT pk_wawi_aufteilung PRIMARY KEY (aufteilung_id);
			ALTER TABLE wawi.tbl_aufteilung ADD CONSTRAINT fk_aufteilung_bestellung FOREIGN KEY (bestellung_id) REFERENCES wawi.tbl_bestellung (bestellung_id) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_aufteilung ADD CONSTRAINT fk_aufteilung_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE CASCADE ON UPDATE CASCADE;
			
			ALTER TABLE wawi.tbl_aufteilung ALTER COLUMN bestellung_id SET NOT NULL;
			ALTER TABLE wawi.tbl_aufteilung ALTER COLUMN oe_kurzbz SET NOT NULL;
			
			ALTER TABLE wawi.tbl_aufteilung ALTER COLUMN aufteilung_id SET DEFAULT nextval('wawi.seq_aufteilung_aufteilung_id');
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_aufteilung TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_aufteilung_aufteilung_id TO admin;
			
			-- Freigabegrenze
			ALTER TABLE public.tbl_organisationseinheit ADD COLUMN freigabegrenze numeric(12,2);
			
			-- Berechtigung
			ALTER TABLE system.tbl_benutzerrolle ADD COLUMN kostenstelle_id bigint;
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT fk_bentuzerrolle_kostenstelle FOREIGN KEY(kostenstelle_id) REFERENCES wawi.tbl_kostenstelle (kostenstelle_id) ON DELETE CASCADE ON UPDATE CASCADE;
			
			-- Berechtigung fuer User wawi
			
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_aufteilung_default TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_aufteilung TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_kostenstelle TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_konto TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestellung TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestelldetail TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestellung_bestellstatus TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestellstatus TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_konto_kostenstelle TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestelldetailtag TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_bestellungtag TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_tag TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_projekt_bestellung TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_rechnungstyp TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_rechnung TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON wawi.tbl_rechnungsbetrag TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_firmatag TO wawi;
			GRANT SELECT ON public.tbl_organisationseinheit TO wawi;
			GRANT SELECT ON public.tbl_benutzer TO wawi;
			GRANT SELECT ON public.tbl_person TO wawi;
			GRANT SELECT ON public.tbl_standort TO wawi;
			GRANT SELECT ON public.tbl_adresse TO wawi;
			GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_firma TO wawi;
			GRANT SELECT ON system.tbl_benutzerrolle TO wawi;
			
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_bestellung_bestellstatus_bestellung_bestellstatus_id TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_rechnungsbetrag_rechnungsbetrag_id TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_rechnung_rechnung_id TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_kostenstelle_kostenstelle_id TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_konto_konto_id TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_bestellung_bestellung_id TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_bestelldetail_bestelldetail_id TO admin;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_aufteilung_aufteilung_id TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_aufteilung_aufteilung_id TO wawi;
			
			GRANT USAGE ON SCHEMA wawi TO wawi;
			GRANT USAGE ON SCHEMA system TO wawi;
			
			GRANT SELECT ON system.tbl_berechtigung TO wawi;
			GRANT SELECT ON system.tbl_rolle TO wawi;
			GRANT SELECT ON system.tbl_benutzerrolle TO wawi;
			GRANT SELECT ON system.tbl_rolleberechtigung TO wawi;
			GRANT SELECT ON public.tbl_benutzerfunktion TO wawi;
			GRANT SELECT ON public.tbl_student TO wawi;
			GRANT SELECT, UPDATE, INSERT ON public.tbl_person TO wawi;
			GRANT SELECT ON public.tbl_benutzer TO wawi;
			GRANT SELECT ON public.tbl_mitarbeiter TO wawi;
			GRANT SELECT ON public.tbl_sprache TO wawi;
			GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_firma TO wawi;
			GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_firma_organisationseinheit TO wawi;
			GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_adresse TO wawi;
			GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_kontakt TO wawi;
			GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_standort TO wawi;
			GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_personfunktionstandort TO wawi;
			GRANT SELECT ON public.tbl_studiensemester TO wawi;
			GRANT SELECT ON public.tbl_studiengang TO wawi;
			
			GRANT SELECT, UPDATE ON SEQUENCE public.tbl_firma_firma_id_seq TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE public.tbl_person_person_id_seq TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE tbl_firma_organisationseinhei_firma_organisationseinheit_id_seq TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE public.tbl_adresse_adresse_id_seq TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE public.tbl_kontakt_kontakt_id_seq TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE public.tbl_standort_standort_id_seq TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE public.tbl_personfunktionstandort_personfunktionstandort_id_seq TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_aufteilung_default_aufteilung_id TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_aufteilung_aufteilung_id TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_bestellung_bestellung_id TO wawi;
			GRANT SELECT, UPDATE ON SEQUENCE wawi.seq_bestelldetail_bestelldetail_id TO wawi;
			GRANT SELECT ON bis.tbl_nation TO wawi;
			GRANT USAGE ON SCHEMA bis TO wawi;
			
			GRANT USAGE ON SCHEMA campus TO wawi;
			GRANT SELECT ON campus.vw_mitarbeiter TO wawi;
			
			-- INDEX
			
			CREATE INDEX idx_bestelldetail_bestellung_id ON wawi.tbl_bestelldetail (bestellung_id);
			CREATE INDEX idx_bestellung_kostenstelle_id ON wawi.tbl_bestellung (kostenstelle_id);
			CREATE INDEX idx_bestellung_freigegeben ON wawi.tbl_bestellung (freigegeben);
			CREATE INDEX idx_rechnungsbetrag_rechnung_id ON wawi.tbl_rechnungsbetrag (rechnung_id);
			CREATE INDEX idx_rechnung_bestellung_id ON wawi.tbl_rechnung (bestellung_id);
			CREATE INDEX idx_bestellung_bestellstatus_bestellung_id ON wawi.tbl_bestellung_bestellstatus (bestellung_id);
			
			INSERT INTO wawi.tbl_rechnungstyp(rechnungstyp_kurzbz, beschreibung) VALUES('Rechnung','Rechnung');
			INSERT INTO wawi.tbl_rechnungstyp(rechnungstyp_kurzbz, beschreibung) VALUES('Gutschrift','Gutschrift');
			INSERT INTO wawi.tbl_rechnungstyp(rechnungstyp_kurzbz, beschreibung) VALUES('Honorarnote','Honorarnote');
	";
	
	if(!$db->db_query($qry))
		echo '<strong>WaWi: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'WaWi: Tabellen fuer Warenwirtschaft hinzugefuegt<br>';
}

//Spalte index fuer tbl_sprache hinzugefuegt
if(!@$db->db_query("SELECT index FROM public.tbl_sprache LIMIT 1;"))
{
	$qry = "ALTER TABLE public.tbl_sprache ADD COLUMN index smallint;
	UPDATE public.tbl_sprache SET index=1 WHERE sprache='German';
	UPDATE public.tbl_sprache SET index=2 WHERE sprache='English';
	UPDATE public.tbl_sprache SET index=3 WHERE sprache='Espanol';
	UPDATE public.tbl_sprache SET index=4 WHERE sprache='Ungarisch';";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_sprache: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_sprache: Spalte index hinzugefuegt<br>';
}

//Spalte anmerkung_lehreinheit fuer lehre.vw_stundenplan und vw_stundenplandev hinzugefuegt
if(!@$db->db_query("SELECT anmerkung_lehreinheit FROM lehre.vw_stundenplan LIMIT 1;"))
{
	$qry = "
	CREATE OR REPLACE VIEW lehre.vw_stundenplan AS
		SELECT 
			tbl_stundenplan.stundenplan_id, tbl_stundenplan.unr, tbl_stundenplan.mitarbeiter_uid AS uid, 
			tbl_stundenplan.lehreinheit_id, tbl_lehreinheit.lehrfach_id, tbl_stundenplan.datum, 
			tbl_stundenplan.stunde, tbl_stundenplan.ort_kurzbz, tbl_stundenplan.studiengang_kz, 
			tbl_stundenplan.semester, tbl_stundenplan.verband, tbl_stundenplan.gruppe, 
			tbl_stundenplan.gruppe_kurzbz, tbl_stundenplan.titel, tbl_stundenplan.anmerkung, 
			tbl_stundenplan.fix, tbl_lehreinheit.lehrveranstaltung_id, 
			tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, 
			tbl_studiengang.bezeichnung AS stg_bezeichnung, tbl_studiengang.typ AS stg_typ, 
			tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, 
			tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, 
			tbl_lehreinheit.lehrform_kurzbz AS lehrform, tbl_mitarbeiter.kurzbz AS lektor, 
			tbl_stundenplan.updateamum, tbl_stundenplan.updatevon, tbl_stundenplan.insertamum, 
			tbl_stundenplan.insertvon, tbl_lehreinheit.anmerkung as anmerkung_lehreinheit
   		FROM lehre.tbl_stundenplan
   		JOIN public.tbl_studiengang USING (studiengang_kz)
   		JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
   		JOIN lehre.tbl_lehrfach USING (lehrfach_id)
   		JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);

   	CREATE OR REPLACE VIEW lehre.vw_stundenplandev AS
		SELECT 
			tbl_stundenplandev.stundenplandev_id, tbl_stundenplandev.unr, 
			tbl_stundenplandev.mitarbeiter_uid AS uid, tbl_stundenplandev.lehreinheit_id, 
			tbl_lehreinheit.lehrfach_id, tbl_stundenplandev.datum, tbl_stundenplandev.stunde, 
			tbl_stundenplandev.ort_kurzbz, tbl_stundenplandev.studiengang_kz, 
			tbl_stundenplandev.semester, tbl_stundenplandev.verband, tbl_stundenplandev.gruppe, 
			tbl_stundenplandev.gruppe_kurzbz, tbl_stundenplandev.titel, tbl_stundenplandev.anmerkung, 
			tbl_stundenplandev.fix, tbl_lehreinheit.lehrveranstaltung_id, 
			tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, 
			tbl_studiengang.bezeichnung AS stg_bezeichnung, tbl_studiengang.typ AS stg_typ, 
			tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, 
			tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, 
			tbl_lehreinheit.lehrform_kurzbz AS lehrform, tbl_mitarbeiter.kurzbz AS lektor, 
			tbl_stundenplandev.updateamum, tbl_stundenplandev.updatevon, tbl_stundenplandev.insertamum, 
			tbl_stundenplandev.insertvon, tbl_lehreinheit.anmerkung as anmerkung_lehreinheit
		FROM lehre.tbl_stundenplandev
		JOIN public.tbl_studiengang USING (studiengang_kz)
		JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
		JOIN lehre.tbl_lehrfach USING (lehrfach_id)
		JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);
		
	GRANT SELECT ON lehre.vw_stundenplan TO web;
	GRANT SELECT ON lehre.vw_stundenplan TO admin;
	GRANT SELECT ON lehre.vw_stundenplandev TO web;
	GRANT SELECT ON lehre.vw_stundenplandev TO admin;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>lehre.vw_stundenplan (dev): '.$db->db_last_error().'</strong><br>';
	else 
		echo 'lehre.vw_stundenplan(dev): Spalte anmerkung_lehreinheit hinzugefuegt<br>';
}

// Index hinzufuegen
if($result = $db->db_query("Select count(*) as anzahl FROM pg_class WHERE relname ='idx_student_studiengang_kz'"))
{
	if(!$row = $db->db_fetch_object($result))
	{
		if($row->anzahl==0)
		{
			$qry = "CREATE INDEX idx_student_studiengang_kz ON public.tbl_student (studiengang_kz);";
			if(!$db->db_query($qry))
				echo '<strong>public.tbl_student: '.$db->db_last_error().'</strong><br>';
			else 
				echo ' public.tbl_student: Index auf studiengang_kz angelegt!<br>';
		}
	}
}

// Index hinzufuegen
if($result = $db->db_query("Select count(*) as anzahl FROM pg_class WHERE relname ='idx_gruppe_studiengang_kz'"))
{
	if(!$row = $db->db_fetch_object($result))
	{
		if($row->anzahl==0)
		{
			$qry = "CREATE INDEX idx_gruppe_studiengang_kz ON public.tbl_gruppe (studiengang_kz);";
			if(!$db->db_query($qry))
				echo '<strong>public.tbl_gruppe: '.$db->db_last_error().'</strong><br>';
			else 
				echo ' public.tbl_gruppe: Index auf studiengang_kz angelegt!<br>';
		}
	}
}

// tbl_sprache spalte content
if(!@$db->db_query("SELECT content FROM public.tbl_sprache LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_sprache ADD COLUMN content boolean;
			UPDATE public.tbl_sprache SET content=true WHERE sprache IN('English','German');
			UPDATE public.tbl_sprache SET content=false WHERE content IS NULL;
			ALTER TABLE public.tbl_sprache ALTER COLUMN content SET NOT NULL;";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_sprache: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' public.tbl_sprache: Spalte content hinzugefuegt!<br>';
}

// tbl_geschaeftsjahr hinzufuegen
if(!@$db->db_query("SELECT 1 FROM public.tbl_geschaeftsjahr LIMIT 1"))
{
	$qry = "
	CREATE TABLE public.tbl_geschaeftsjahr
	(
		geschaeftsjahr_kurzbz varchar(32) NOT NULL,
		start date,
		ende date,
		bezeichnung varchar(256)
	);
	ALTER TABLE public.tbl_geschaeftsjahr ADD CONSTRAINT pk_tbl_geschaeftsjahr PRIMARY KEY (geschaeftsjahr_kurzbz);
	
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2000-2001','2000-09-01','2001-08-31','Geschäftsjahr 2000-2001');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2001-2002','2001-09-01','2002-08-31','Geschäftsjahr 2001-2002');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2002-2003','2002-09-01','2003-08-31','Geschäftsjahr 2002-2003');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2003-2004','2003-09-01','2004-08-31','Geschäftsjahr 2003-2004');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2004-2005','2004-09-01','2005-08-31','Geschäftsjahr 2004-2005');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2005-2006','2005-09-01','2006-08-31','Geschäftsjahr 2005-2006');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2006-2007','2006-09-01','2007-08-31','Geschäftsjahr 2006-2007');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2007-2008','2007-09-01','2008-08-31','Geschäftsjahr 2007-2008');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2008-2009','2008-09-01','2009-08-31','Geschäftsjahr 2008-2009');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2009-2010','2009-09-01','2010-08-31','Geschäftsjahr 2009-2010');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2010-2011','2010-09-01','2011-08-31','Geschäftsjahr 2010-2011');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2011-2012','2011-09-01','2012-08-31','Geschäftsjahr 2011-2012');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2012-2013','2012-09-01','2013-08-31','Geschäftsjahr 2012-2013');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2013-2014','2013-09-01','2014-08-31','Geschäftsjahr 2013-2014');
	INSERT INTO public.tbl_geschaeftsjahr(geschaeftsjahr_kurzbz, start, ende, bezeichnung) VALUES('GJ2014-2015','2014-09-01','2015-08-31','Geschäftsjahr 2014-2015');
	
	GRANT SELECT ON public.tbl_geschaeftsjahr TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_geschaeftsjahr TO vilesci;
	GRANT SELECT ON public.tbl_geschaeftsjahr TO wawi;
	
	ALTER TABLE wawi.tbl_budget ADD CONSTRAINT fk_budget_geschaeftsjahr FOREIGN KEY(geschaeftsjahr_kurzbz) REFERENCES public.tbl_geschaeftsjahr (geschaeftsjahr_kurzbz) ON DELETE CASCADE ON UPDATE CASCADE;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_geschaeftsjahr: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle public.tbl_geschaeftsjahr hinzugefuegt!<br>';
}

//Raumtyp_kurzbz von 8 auf 16 Zeichen verlaengern (tbl_lehreinheit)
if($result = $db->db_query("SELECT character_maximum_length FROM information_schema.columns WHERE column_name='raumtyp' AND table_name='tbl_lehreinheit' AND table_schema='lehre';"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->character_maximum_length==8)
		{
			$qry = "
			DROP VIEW lehre.vw_lva_stundenplan;
			DROP VIEW lehre.vw_lva_stundenplandev;
			DROP VIEW campus.vw_student_lehrveranstaltung;
			DROP VIEW campus.vw_lehreinheit;
			
			ALTER TABLE lehre.tbl_lehreinheit ALTER COLUMN raumtyp TYPE varchar(16);
			ALTER TABLE lehre.tbl_lehreinheit ALTER COLUMN raumtypalternativ TYPE varchar(16);
			
			CREATE VIEW lehre.vw_lva_stundenplandev AS
			SELECT le.lehreinheit_id, le.unr, le.lvnr, tbl_lehrfach.fachbereich_kurzbz, le.lehrfach_id, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe AS lehrfach_farbe, le.lehrform_kurzbz AS lehrform, lema.mitarbeiter_uid AS lektor_uid, tbl_mitarbeiter.kurzbz AS lektor, tbl_studiengang.studiengang_kz, upper(tbl_studiengang.typ::character varying::text || tbl_studiengang.kurzbz::text) AS studiengang, lvb.semester, lvb.verband, lvb.gruppe, lvb.gruppe_kurzbz, le.raumtyp, le.raumtypalternativ, le.stundenblockung, le.wochenrythmus, lema.semesterstunden, lema.planstunden, le.start_kw, le.anmerkung, le.studiensemester_kurzbz, ( SELECT count(*) AS count
			FROM lehre.tbl_stundenplandev
			WHERE tbl_stundenplandev.mitarbeiter_uid::text = lema.mitarbeiter_uid::text AND tbl_stundenplandev.studiengang_kz = lvb.studiengang_kz AND tbl_stundenplandev.semester = lvb.semester AND (tbl_stundenplandev.verband = lvb.verband OR (tbl_stundenplandev.verband IS NULL OR tbl_stundenplandev.verband = ''::bpchar) AND lvb.verband IS NULL) AND (tbl_stundenplandev.gruppe = lvb.gruppe OR (tbl_stundenplandev.gruppe IS NULL OR tbl_stundenplandev.gruppe = ''::bpchar) AND lvb.gruppe IS NULL) AND (tbl_stundenplandev.gruppe_kurzbz::text = lvb.gruppe_kurzbz::text OR tbl_stundenplandev.gruppe_kurzbz IS NULL AND lvb.gruppe_kurzbz IS NULL) AND tbl_stundenplandev.lehreinheit_id = lvb.lehreinheit_id) AS verplant
			FROM lehre.tbl_lehreinheit le
			JOIN lehre.tbl_lehreinheitmitarbeiter lema USING (lehreinheit_id)
			JOIN lehre.tbl_lehreinheitgruppe lvb USING (lehreinheit_id)
			JOIN public.tbl_studiengang ON lvb.studiengang_kz = tbl_studiengang.studiengang_kz
			JOIN lehre.tbl_lehrfach USING (lehrfach_id)
			JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);
			
			CREATE VIEW lehre.vw_lva_stundenplan AS
			SELECT le.lehreinheit_id, le.unr, le.lvnr, tbl_lehrfach.fachbereich_kurzbz, le.lehrfach_id, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe AS lehrfach_farbe, le.lehrform_kurzbz AS lehrform, lema.mitarbeiter_uid AS lektor_uid, ma.kurzbz AS lektor, tbl_studiengang.studiengang_kz, tbl_studiengang.kurzbz AS studiengang, lvb.semester, lvb.verband, lvb.gruppe, lvb.gruppe_kurzbz, le.raumtyp, le.raumtypalternativ, le.stundenblockung, le.wochenrythmus, lema.semesterstunden, lema.planstunden, le.start_kw, le.anmerkung, le.studiensemester_kurzbz, ( SELECT count(*) AS count
			FROM lehre.tbl_stundenplan
			WHERE tbl_stundenplan.mitarbeiter_uid::text = lema.mitarbeiter_uid::text AND tbl_stundenplan.studiengang_kz = lvb.studiengang_kz AND tbl_stundenplan.semester = lvb.semester AND (tbl_stundenplan.verband = lvb.verband OR (tbl_stundenplan.verband IS NULL OR tbl_stundenplan.verband = ''::bpchar) AND lvb.verband IS NULL) AND (tbl_stundenplan.gruppe = lvb.gruppe OR (tbl_stundenplan.gruppe IS NULL OR tbl_stundenplan.gruppe = ''::bpchar) AND lvb.gruppe IS NULL) AND (tbl_stundenplan.gruppe_kurzbz::text = lvb.gruppe_kurzbz::text OR tbl_stundenplan.gruppe_kurzbz IS NULL AND lvb.gruppe_kurzbz IS NULL) AND tbl_stundenplan.lehreinheit_id = lvb.lehreinheit_id) AS verplant
			FROM lehre.tbl_lehreinheit le
			JOIN lehre.tbl_lehreinheitgruppe lvb USING (lehreinheit_id)
			JOIN lehre.tbl_lehreinheitmitarbeiter lema USING (lehreinheit_id)
			JOIN public.tbl_studiengang USING (studiengang_kz)
			JOIN lehre.tbl_lehrfach USING (lehrfach_id)
			JOIN public.tbl_mitarbeiter ma USING (mitarbeiter_uid);
			
			CREATE VIEW campus.vw_student_lehrveranstaltung AS
			SELECT tbl_benutzergruppe.uid, tbl_lehrveranstaltung.zeugnis, tbl_lehrveranstaltung.sort, tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.kurzbz, tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.bezeichnung_english, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.sprache, tbl_lehrveranstaltung.ects, tbl_lehrveranstaltung.semesterstunden, tbl_lehrveranstaltung.anmerkung, tbl_lehrveranstaltung.lehre, tbl_lehrveranstaltung.lehreverzeichnis, tbl_lehrveranstaltung.aktiv, tbl_lehrveranstaltung.planfaktor, tbl_lehrveranstaltung.planlektoren, tbl_lehrveranstaltung.planpersonalkosten, tbl_lehrveranstaltung.plankostenprolektor, tbl_lehrveranstaltung.updateamum, tbl_lehrveranstaltung.updatevon, tbl_lehrveranstaltung.insertamum, tbl_lehrveranstaltung.insertvon, tbl_lehrveranstaltung.ext_id, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.studiensemester_kurzbz, tbl_lehreinheit.lehrfach_id, tbl_lehreinheit.lehrform_kurzbz, tbl_lehreinheit.stundenblockung, tbl_lehreinheit.wochenrythmus, tbl_lehreinheit.start_kw, tbl_lehreinheit.raumtyp, tbl_lehreinheit.raumtypalternativ, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz
           	FROM lehre.tbl_lehreinheitgruppe, tbl_benutzergruppe, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
          	WHERE tbl_lehreinheitgruppe.gruppe_kurzbz::text = tbl_benutzergruppe.gruppe_kurzbz::text AND tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id AND tbl_lehreinheit.studiensemester_kurzbz::text = tbl_benutzergruppe.studiensemester_kurzbz::text
			UNION 
         	SELECT tbl_studentlehrverband.student_uid AS uid, tbl_lehrveranstaltung.zeugnis, tbl_lehrveranstaltung.sort, tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.kurzbz, tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.bezeichnung_english, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.sprache, tbl_lehrveranstaltung.ects, tbl_lehrveranstaltung.semesterstunden, tbl_lehrveranstaltung.anmerkung, tbl_lehrveranstaltung.lehre, tbl_lehrveranstaltung.lehreverzeichnis, tbl_lehrveranstaltung.aktiv, tbl_lehrveranstaltung.planfaktor, tbl_lehrveranstaltung.planlektoren, tbl_lehrveranstaltung.planpersonalkosten, tbl_lehrveranstaltung.plankostenprolektor, tbl_lehrveranstaltung.updateamum, tbl_lehrveranstaltung.updatevon, tbl_lehrveranstaltung.insertamum, tbl_lehrveranstaltung.insertvon, tbl_lehrveranstaltung.ext_id, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.studiensemester_kurzbz, tbl_lehreinheit.lehrfach_id, tbl_lehreinheit.lehrform_kurzbz, tbl_lehreinheit.stundenblockung, tbl_lehreinheit.wochenrythmus, tbl_lehreinheit.start_kw, tbl_lehreinheit.raumtyp, tbl_lehreinheit.raumtypalternativ, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz
           	FROM lehre.tbl_lehreinheitgruppe, tbl_studentlehrverband, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
          	WHERE tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id AND tbl_lehreinheit.studiensemester_kurzbz::text = tbl_studentlehrverband.studiensemester_kurzbz::text AND tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND tbl_studentlehrverband.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz AND tbl_studentlehrverband.semester = tbl_lehreinheitgruppe.semester AND (btrim(tbl_studentlehrverband.verband::text) = btrim(tbl_lehreinheitgruppe.verband::text) OR (tbl_lehreinheitgruppe.verband IS NULL OR btrim(tbl_lehreinheitgruppe.verband::text) = ''::text) AND tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL) AND (btrim(tbl_studentlehrverband.gruppe::text) = btrim(tbl_lehreinheitgruppe.gruppe::text) OR (tbl_lehreinheitgruppe.gruppe IS NULL OR btrim(tbl_lehreinheitgruppe.gruppe::text) = ''::text) AND tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL);
          
          	CREATE VIEW campus.vw_lehreinheit AS
			SELECT tbl_lehrveranstaltung.studiengang_kz AS lv_studiengang_kz, tbl_lehrveranstaltung.semester AS lv_semester, tbl_lehrveranstaltung.kurzbz AS lv_kurzbz, tbl_lehrveranstaltung.bezeichnung AS lv_bezeichnung, tbl_lehrveranstaltung.ects AS lv_ects, tbl_lehrveranstaltung.lehreverzeichnis AS lv_lehreverzeichnis, tbl_lehrveranstaltung.planfaktor AS lv_planfaktor, tbl_lehrveranstaltung.planlektoren AS lv_planlektoren, tbl_lehrveranstaltung.planpersonalkosten AS lv_planpersonalkosten, tbl_lehrveranstaltung.plankostenprolektor AS lv_plankostenprolektor, tbl_lehrveranstaltung.orgform_kurzbz AS lv_orgform_kurzbz, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrveranstaltung_id, tbl_lehreinheit.studiensemester_kurzbz, tbl_lehreinheit.lehrform_kurzbz, tbl_lehreinheit.stundenblockung, tbl_lehreinheit.wochenrythmus, tbl_lehreinheit.start_kw, tbl_lehreinheit.raumtyp, tbl_lehreinheit.raumtypalternativ, tbl_lehreinheit.lehre, tbl_lehreinheit.unr, tbl_lehreinheit.lvnr, tbl_lehreinheitmitarbeiter.lehrfunktion_kurzbz, tbl_lehreinheit.insertamum, tbl_lehreinheit.insertvon, tbl_lehreinheit.updateamum, tbl_lehreinheit.updatevon, tbl_lehreinheit.lehrfach_id, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehrveranstaltung.aktiv, tbl_lehrfach.sprache, tbl_lehreinheitmitarbeiter.mitarbeiter_uid, tbl_lehreinheitmitarbeiter.semesterstunden, tbl_lehrveranstaltung.semesterstunden AS lv_semesterstunden, tbl_lehreinheitmitarbeiter.planstunden, tbl_lehreinheitmitarbeiter.stundensatz, tbl_lehreinheitmitarbeiter.faktor, tbl_lehreinheit.anmerkung, tbl_mitarbeiter.kurzbz AS lektor, tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe, tbl_lehreinheitgruppe.gruppe_kurzbz, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bez, tbl_studiengang.typ AS stg_typ, tbl_lehreinheitmitarbeiter.anmerkung AS anmerkunglektor, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz, tbl_lehrveranstaltung.bezeichnung_english AS lv_bezeichnung_english
			FROM lehre.tbl_lehreinheit
			JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
			JOIN lehre.tbl_lehrfach USING (lehrfach_id)
			JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)
			JOIN tbl_mitarbeiter USING (mitarbeiter_uid)
			JOIN lehre.tbl_lehreinheitgruppe USING (lehreinheit_id)
			JOIN tbl_studiengang ON tbl_lehreinheitgruppe.studiengang_kz = tbl_studiengang.studiengang_kz;
   
			GRANT SELECT on lehre.vw_lva_stundenplan TO GROUP web;
			GRANT SELECT on lehre.vw_lva_stundenplan TO GROUP admin;
			GRANT SELECT on lehre.vw_lva_stundenplandev TO GROUP admin;
			GRANT SELECT on lehre.vw_lva_stundenplandev TO GROUP web;
			GRANT SELECT on campus.vw_student_lehrveranstaltung TO GROUP web;
			GRANT SELECT on campus.vw_student_lehrveranstaltung TO GROUP admin;
			GRANT SELECT on campus.vw_lehreinheit TO GROUP web;
			GRANT SELECT on campus.vw_lehreinheit TO GROUP admin;
			";
			if(!$db->db_query($qry))
				echo '<strong>lehre.tbl_lehreinheit: '.$db->db_last_error().'</strong><br>';
			else 
				echo 'lehre.tbl_lehreinheit: Spalte raumtyp und raumtypalternativ auf 16 Zeichen verlaengert<br>';
		}
	}
}

// tbl_zahlungstyp hinzufuegen
if(!@$db->db_query("SELECT 1 FROM wawi.tbl_zahlungstyp LIMIT 1"))
{
	$qry = "
	CREATE TABLE wawi.tbl_zahlungstyp
	(
		zahlungstyp_kurzbz varchar(32) NOT NULL,
		bezeichnung varchar(256) NOT NULL
	);
	
	ALTER TABLE wawi.tbl_bestellung ADD COLUMN zahlungstyp_kurzbz varchar(32);
	
	ALTER TABLE wawi.tbl_zahlungstyp ADD CONSTRAINT pk_zahlungstyp PRIMARY KEY (zahlungstyp_kurzbz);

	ALTER TABLE wawi.tbl_bestellung ADD CONSTRAINT fk_bestellung_zahlungstyp FOREIGN KEY(zahlungstyp_kurzbz) REFERENCES wawi.tbl_zahlungstyp (zahlungstyp_kurzbz) ON DELETE CASCADE ON UPDATE CASCADE;
	
	INSERT INTO wawi.tbl_zahlungstyp(zahlungstyp_kurzbz, bezeichnung) VALUES('rechnung','Rechnung');
	INSERT INTO wawi.tbl_zahlungstyp(zahlungstyp_kurzbz, bezeichnung) VALUES('kreditkarte','Kreditkarte');
	INSERT INTO wawi.tbl_zahlungstyp(zahlungstyp_kurzbz, bezeichnung) VALUES('vorauszahlung','Vorauszahlung');
	INSERT INTO wawi.tbl_zahlungstyp(zahlungstyp_kurzbz, bezeichnung) VALUES('nachnahme','Nachnahme');
	
	GRANT SELECT, INSERT, DELETE, UPDATE ON wawi.tbl_zahlungstyp TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON wawi.tbl_zahlungstyp TO wawi;
	
	";
	
	if(!$db->db_query($qry))
		echo '<strong>wawi.tbl_zahlungstyp: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle wawi.tbl_zahlungstyp hinzugefuegt!<br>';
}

// oe_kurzbz zur tbl_vorlagestudiengang hinzufuegen
if(!@$db->db_query("SELECT oe_kurzbz FROM public.tbl_vorlagestudiengang LIMIT 1"))
{
	$qry = "
	ALTER TABLE public.tbl_vorlagestudiengang ADD COLUMN oe_kurzbz varchar(32);
	UPDATE public.tbl_vorlagestudiengang SET oe_kurzbz = (SELECT oe_kurzbz FROM public.tbl_studiengang WHERE studiengang_kz=tbl_vorlagestudiengang.studiengang_kz);
	ALTER TABLE public.tbl_vorlagestudiengang ALTER COLUMN oe_kurzbz SET NOT NULL;
	ALTER TABLE public.tbl_vorlagestudiengang ADD CONSTRAINT fk_vorlagestudiengang_organisationseinheit FOREIGN KEY(oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_vorlagestudiengang: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle public.tbl_vorlagestudiengang Spalte oe_kurzbz hinzugefuegt!<br>';
}

// kurzzeichen zu tbl_organisationseinheit hinzugefuegt zur generierung der Bestellnummer
if(!@$db->db_query("SELECT kurzzeichen FROM public.tbl_organisationseinheit LIMIT 1"))
{
	$qry = "
	ALTER TABLE public.tbl_organisationseinheit ADD COLUMN kurzzeichen varchar(16);
	";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_organisationseinheit: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle public.tbl_organisationseinheit Spalte kurzzeichen hinzugefuegt!<br>';
}
// kurzzeichen zu tbl_organisationseinheit hinzugefuegt zur generierung der Bestellnummer
if(!@$db->db_query("SELECT vorlagestudiengang_id FROM public.tbl_vorlagestudiengang LIMIT 1"))
{
	$qry = "
	CREATE SEQUENCE public.seq_vorlagestudiengang_vorlagestudiengang_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
	
	ALTER TABLE public.tbl_vorlagestudiengang ADD COLUMN vorlagestudiengang_id bigint DEFAULT nextval('public.seq_vorlagestudiengang_vorlagestudiengang_id');
	ALTER TABLE public.tbl_vorlagestudiengang DROP CONSTRAINT pk_tbl_vorlagestudiengang;
	ALTER TABLE public.tbl_vorlagestudiengang ADD CONSTRAINT pk_vorlagestudiengang PRIMARY KEY (vorlagestudiengang_id);
	ALTER TABLE public.tbl_vorlagestudiengang ALTER COLUMN studiengang_kz SET NOT NULL;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_vorlagestudiengang: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle public.tbl_vorlagestudiengang Spalte vorlagestudiengang_id hinzugefuegt!<br>';
}

// Content Modul
if(!@$db->db_query("SELECT content_id FROM campus.tbl_content LIMIT 1"))
{
	$qry = "
	CREATE TABLE campus.tbl_template(
		template_kurzbz varchar(32) NOT NULL,
		bezeichnung varchar(256),
		xsd XML,
		xslt_xhtml XML,
		xslfo_pdf XML
	);
	
	ALTER TABLE campus.tbl_template ADD CONSTRAINT pk_template PRIMARY KEY (template_kurzbz);
		
	CREATE TABLE campus.tbl_content(
		content_id bigint NOT NULL,
		titel varchar(256),
		template_kurzbz varchar(32) NOT NULL,
		oe_kurzbz varchar(32),
		insertamum timestamp,
		insertvon varchar(32),
		updateamum timestamp,
		updatevon varchar(32)		
	);
	CREATE SEQUENCE campus.seq_content_content_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
	
	ALTER TABLE campus.tbl_content ALTER COLUMN content_id SET DEFAULT nextval('campus.seq_content_content_id');
	ALTER TABLE campus.tbl_content ADD CONSTRAINT pk_content PRIMARY KEY (content_id);
	ALTER TABLE campus.tbl_content ADD CONSTRAINT fk_template_content FOREIGN KEY(template_kurzbz) REFERENCES campus.tbl_template (template_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_content ADD CONSTRAINT fk_organisationseinheit_content FOREIGN KEY(oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	
	CREATE TABLE campus.tbl_contentchild(
		contentchild_id bigint NOT NULL,
		content_id bigint NOT NULL,
		child_content_id bigint NOT NULL,
		insertamum timestamp,
		insertvon varchar(32),
		updateamum timestamp,
		updatevon varchar(32)	
	);
	
	CREATE SEQUENCE campus.seq_contentchild
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
	
	ALTER TABLE campus.tbl_contentchild ALTER COLUMN contentchild_id SET DEFAULT nextval('campus.seq_contentchild');
	ALTER TABLE campus.tbl_contentchild ADD CONSTRAINT pk_contentchild PRIMARY KEY (contentchild_id);
	ALTER TABLE campus.tbl_contentchild ADD CONSTRAINT fk_content_contentchild_content_id FOREIGN KEY(content_id) REFERENCES campus.tbl_content (content_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_contentchild ADD CONSTRAINT fk_content_contentchild_child_content_id FOREIGN KEY(child_content_id) REFERENCES campus.tbl_content (content_id) ON DELETE RESTRICT ON UPDATE CASCADE;

	CREATE TABLE campus.tbl_contentsprache(
		contentsprache_id bigint NOT NULL,
		sprache varchar(16) NOT NULL,
		content_id bigint NOT NULL,
		version smallint,
		sichtbar boolean NOT NULL,
		content XML NOT NULL,
		reviewvon varchar(32),
		reviewamum timestamp,
		updateamum timestamp,
		updatevon varchar(32),
		insertamum timestamp,
		insertvon varchar(32)
	);
	
	CREATE SEQUENCE campus.seq_contentsprache
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
	
	ALTER TABLE campus.tbl_contentsprache ALTER COLUMN contentsprache_id SET DEFAULT nextval('campus.seq_contentsprache');
	ALTER TABLE campus.tbl_contentsprache ADD CONSTRAINT pk_contentsprache PRIMARY KEY (contentsprache_id);
	ALTER TABLE campus.tbl_contentsprache ADD CONSTRAINT fk_sprache_contentsprache FOREIGN KEY(sprache) REFERENCES public.tbl_sprache (sprache) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_contentsprache ADD CONSTRAINT fk_content_contentsprache FOREIGN KEY(content_id) REFERENCES campus.tbl_content (content_id) ON DELETE RESTRICT ON UPDATE CASCADE;

	CREATE TABLE public.tbl_statistik (
		statistik_kurzbz varchar(64) NOT NULL,
		bezeichnung varchar(256),
		content_id bigint,
		url varchar(512),
		gruppe varchar(256),
		r text,
		php text,
		sql text,
		insertamum timestamp,
		insertvon varchar(32),
		updateamum timestamp,
		updatevon varchar(32),
		berechtigung_kurzbz varchar(32)
	);
	
	ALTER TABLE public.tbl_statistik ADD CONSTRAINT pk_statistik PRIMARY KEY (statistik_kurzbz);
	ALTER TABLE public.tbl_statistik ADD CONSTRAINT fk_content_statistik FOREIGN KEY(content_id) REFERENCES campus.tbl_content (content_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE public.tbl_statistik ADD CONSTRAINT fk_berechtigung_statistik FOREIGN KEY(berechtigung_kurzbz) REFERENCES system.tbl_berechtigung (berechtigung_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	
	CREATE TABLE campus.tbl_contentgruppe (
		content_id bigint NOT NULL,
		gruppe_kurzbz varchar(16) NOT NULL,
		insertamum timestamp,
		insertvon varchar(32)
	);
	ALTER TABLE campus.tbl_contentgruppe ADD CONSTRAINT fk_content_contentgruppe FOREIGN KEY(content_id) REFERENCES campus.tbl_content (content_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_contentgruppe ADD CONSTRAINT fk_gruppe_contentgruppe FOREIGN KEY(gruppe_kurzbz) REFERENCES public.tbl_gruppe(gruppe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_contentgruppe ADD CONSTRAINT pk_contentgruppe PRIMARY KEY(content_id, gruppe_kurzbz);

	ALTER TABLE public.tbl_sprache ADD COLUMN bezeichnung varchar(32)[];
	
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_content TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_content TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_contentsprache TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_contentsprache TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_template TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_template TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_contentchild TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_contentchild TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_contentgruppe TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_contentgruppe TO web;
	
	GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_statistik TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_statistik TO web;
	
	ALTER TABLE public.tbl_gruppe ADD COLUMN content_visible boolean NOT NULL DEFAULT false;
	
	GRANT SELECT, UPDATE ON SEQUENCE campus.seq_contentsprache TO admin;
	GRANT SELECT, UPDATE ON SEQUENCE campus.seq_contentsprache TO web;
	GRANT SELECT, UPDATE ON SEQUENCE campus.seq_contentchild TO admin;
	GRANT SELECT, UPDATE ON SEQUENCE campus.seq_contentchild TO web;
	GRANT SELECT, UPDATE ON SEQUENCE campus.seq_content_content_id TO admin;
	GRANT SELECT, UPDATE ON SEQUENCE campus.seq_content_content_id TO web;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>Content-Modul: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Content-Modul hinzugefuegt!<br>';
}
// projektarbeit_note_anzeige zu tbl_studiengang hinzufuegen
if(!@$db->db_query("SELECT projektarbeit_note_anzeige FROM public.tbl_studiengang LIMIT 1"))
{
	$qry = "
	ALTER TABLE public.tbl_studiengang ADD COLUMN projektarbeit_note_anzeige boolean;
	UPDATE public.tbl_studiengang SET projektarbeit_note_anzeige=true;
	ALTER TABLE public.tbl_studiengang ALTER COLUMN projektarbeit_note_anzeige SET NOT NULL;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_studiengang: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle public.tbl_studiengang Spalte projektarbeit_note_anzeige hinzugefuegt!<br>';
}
// Dokumenten Management System
if(!@$db->db_query("SELECT 1 FROM campus.tbl_dms LIMIT 1"))
{
	$qry = "
	CREATE TABLE campus.tbl_dms(
		dms_id bigint NOT NULL,
		version smallint NOT NULL,
		oe_kurzbz varchar(32),
		dokument_kurzbz varchar(8),
		kategorie_kurzbz varchar(32),
		filename varchar(256),
		mimetype varchar(256),
		name varchar(256),
		beschreibung text,
		letzterzugriff timestamp,
		insertamum timestamp,
		insertvon varchar(32),
		updateamum timestamp,
		updatevon varchar(32)		
	);
	
	CREATE TABLE campus.tbl_dms_kategorie(
		kategorie_kurzbz varchar(32) NOT NULL,
		bezeichnung varchar(256),
		beschreibung text
	);
	
	ALTER TABLE campus.tbl_dms ADD CONSTRAINT pk_dms PRIMARY KEY(dms_id, version);
	ALTER TABLE campus.tbl_dms_kategorie ADD CONSTRAINT pk_dms_kategorie PRIMARY KEY(kategorie_kurzbz);
	ALTER TABLE campus.tbl_dms ADD CONSTRAINT fk_organisationseinheit_dms FOREIGN KEY(oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
	ALTER TABLE campus.tbl_dms ADD CONSTRAINT fk_dokument_dms FOREIGN KEY(dokument_kurzbz) REFERENCES public.tbl_dokument (dokument_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
	ALTER TABLE campus.tbl_dms ADD CONSTRAINT fk_dms_kategorie_dms FOREIGN KEY(kategorie_kurzbz) REFERENCES campus.tbl_dms_kategorie (kategorie_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
	
	CREATE SEQUENCE campus.seq_dms_dms_id
	 	INCREMENT BY 1
	 	NO MAXVALUE
		NO MINVALUE
		CACHE 1;
	ALTER TABLE campus.tbl_dms ALTER COLUMN dms_id SET DEFAULT nextval('campus.seq_dms_dms_id');
	
	GRANT SELECT, UPDATE, INSERT, DELETE ON campus.tbl_dms_kategorie TO web;
	GRANT SELECT, UPDATE, INSERT, DELETE ON campus.tbl_dms_kategorie TO admin;
	GRANT SELECT, UPDATE, INSERT, DELETE ON campus.tbl_dms TO web;
	GRANT SELECT, UPDATE, INSERT, DELETE ON campus.tbl_dms TO admin;
	
	GRANT SELECT, UPDATE ON SEQUENCE campus.seq_dms_dms_id TO admin;
	GRANT SELECT, UPDATE ON SEQUENCE campus.seq_dms_dms_id TO web;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_dms: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle campus.tbl_dms und campus.tbl_dms_kategorie hinzugefuegt!<br>';
}
// projektarbeit_note_anzeige zu tbl_studiengang hinzufuegen
if(!@$db->db_query("SELECT parent_kategorie_kurzbz FROM campus.tbl_dms_kategorie LIMIT 1"))
{
	$qry = "
	ALTER TABLE campus.tbl_dms_kategorie ADD COLUMN parent_kategorie_kurzbz varchar(32);
	ALTER TABLE campus.tbl_dms_kategorie ADD CONSTRAINT fk_dms_kategorie_dms_kategorie FOREIGN KEY(parent_kategorie_kurzbz) REFERENCES campus.tbl_dms_kategorie (kategorie_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;	
	";
	
	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_dms_kategorie: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle campus.tbl_dms_kategorie Spalte parent_kategorie_kurzbz hinzugefuegt!<br>';
}
// Preincoming
if(!@$db->db_query("SELECT zugangscode FROM public.tbl_person LIMIT 1"))
{
	$qry = "
		CREATE SEQUENCE public.seq_preincoming_preincoming_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
		CREATE TABLE public.tbl_preincoming
		(
		 preincoming_id Integer NOT NULL,
		 person_id Integer NOT NULL,
		 mobilitaetsprogramm_code Integer,
		 zweck_code Character varying(20),
		 firma_id Integer,
		 anmerkung Text,
		 universitaet Character varying(256),
		 aktiv Boolean DEFAULT true NOT NULL,
		 bachelorthesis Boolean NOT NULL,
		 masterthesis Boolean NOT NULL,
		 von Date,
		 bis Date,
		 uebernommen Boolean DEFAULT false NOT NULL,
		 insertamum Timestamp,
		 insertvon Character varying(32),
		 updateamum Timestamp,
		 updatevon Character varying(32)
		);
		
		
		ALTER TABLE public.tbl_preincoming ALTER COLUMN preincoming_id SET DEFAULT nextval('public.seq_preincoming_preincoming_id');
		
		ALTER TABLE public.tbl_preincoming ADD CONSTRAINT pk_preincoming PRIMARY KEY (preincoming_id);
		ALTER TABLE public.tbl_preincoming ADD CONSTRAINT uk_preincoming_id UNIQUE (preincoming_id);

		ALTER TABLE public.tbl_preincoming ADD CONSTRAINT fk_firma_preincoming FOREIGN KEY(firma_id) REFERENCES public.tbl_firma (firma_id) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE public.tbl_preincoming ADD CONSTRAINT fk_person_preincoming FOREIGN KEY(person_id) REFERENCES public.tbl_person (person_id) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE public.tbl_preincoming ADD CONSTRAINT fk_mobilitaetsprogramm_preincoming FOREIGN KEY(mobilitaetsprogramm_code) REFERENCES bis.tbl_mobilitaetsprogramm (mobilitaetsprogramm_code) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE public.tbl_preincoming ADD CONSTRAINT fk_zweck_preincoming FOREIGN KEY(zweck_code) REFERENCES bis.tbl_zweck (zweck_code) ON UPDATE CASCADE ON DELETE RESTRICT;
		
		CREATE TABLE public.tbl_preincoming_lehrveranstaltung 
		(
 			lehrveranstaltung_id Integer NOT NULL,
 			preincoming_id Integer NOT NULL,
 			insertamum Timestamp,
 			insertvon Character varying(32)
		);
		
		ALTER TABLE public.tbl_preincoming_lehrveranstaltung ADD CONSTRAINT pk_preincoming_lehrveranstaltung PRIMARY KEY (preincoming_id, lehrveranstaltung_id);
		ALTER TABLE public.tbl_preincoming_lehrveranstaltung ADD CONSTRAINT uk_preincoming_lehrveranstaltung UNIQUE (preincoming_id, lehrveranstaltung_id);

		ALTER TABLE public.tbl_preincoming_lehrveranstaltung ADD CONSTRAINT fk_preincoming_preincoming_lehrveranstaltung FOREIGN KEY(preincoming_id) REFERENCES public.tbl_preincoming (preincoming_id) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE public.tbl_preincoming_lehrveranstaltung ADD CONSTRAINT fk_lehrveranstaltung_preincoming_lehrveranstaltung FOREIGN KEY(lehrveranstaltung_id) REFERENCES lehre.tbl_lehrveranstaltung (lehrveranstaltung_id) ON UPDATE CASCADE ON DELETE RESTRICT;
		
		ALTER TABLE public.tbl_person ADD COLUMN zugangscode varchar(32);
		ALTER TABLE public.tbl_person ADD CONSTRAINT uk_zugangscode UNIQUE (zugangscode);
		
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_preincoming TO admin;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_preincoming TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_preincoming_lehrveranstaltung TO admin;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_preincoming_lehrveranstaltung TO web;
			
		GRANT SELECT, UPDATE ON SEQUENCE public.seq_preincoming_preincoming_id TO admin;
		GRANT SELECT, UPDATE ON SEQUENCE public.seq_preincoming_preincoming_id TO web;
		
		GRANT SELECT, INSERT, UPDATE ON public.tbl_person TO web;
		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_adresse TO web;
		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_kontakt TO web;
		GRANT SELECT, UPDATE ON SEQUENCE public.tbl_person_person_id_seq TO web;
		GRANT SELECT, UPDATE ON SEQUENCE public.tbl_adresse_adresse_id_seq TO web;
		GRANT SELECT, UPDATE ON SEQUENCE public.tbl_kontakt_kontakt_id_seq TO web;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>Preincoming Modul: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Preincoming Modul hinzugefuegt!<br>';
}
// Content Titel in Tabelle contentsprache verschoben
if(!@$db->db_query("SELECT titel FROM campus.tbl_contentsprache LIMIT 1"))
{
	$qry = "
	ALTER TABLE campus.tbl_contentsprache ADD COLUMN titel varchar(256);
	UPDATE campus.tbl_contentsprache set titel = (SELECT titel FROM campus.tbl_content WHERE content_id=tbl_contentsprache.content_id);
	ALTER TABLE campus.tbl_content DROP COLUMN titel;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>campus.content: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle campus.content Spalte titel in Tabelle contentsprache verschoben!<br>';
}

// Mobilitaetsprogramm Sichtbar Filter
if(!@$db->db_query("SELECT sichtbar FROM bis.tbl_mobilitaetsprogramm LIMIT 1"))
{
	$qry = "
	ALTER TABLE bis.tbl_mobilitaetsprogramm ADD COLUMN sichtbar boolean DEFAULT true;
	UPDATE bis.tbl_mobilitaetsprogramm SET sichtbar=true;
	ALTER TABLE bis.tbl_mobilitaetsprogramm ALTER COLUMN sichtbar SET NOT NULL
	";
	
	if(!$db->db_query($qry))
		echo '<strong>bis.mobilitaetsprogramm: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle bis.tbl_mobilitaetsprogramm Spalte sichbar hinzugefuegt!<br>';
}

// PreIncoming Erweiterungen
if(!@$db->db_query("SELECT zgv FROM public.tbl_preincoming LIMIT 1"))
{
	$qry = "
	ALTER TABLE public.tbl_preincoming ADD COLUMN zgv varchar(256);
	ALTER TABLE public.tbl_preincoming ADD COLUMN zgv_ort varchar(256);
	ALTER TABLE public.tbl_preincoming ADD COLUMN zgv_datum date;
	ALTER TABLE public.tbl_preincoming ADD COLUMN zgv_name varchar(256);
	ALTER TABLE public.tbl_preincoming ADD COLUMN zgvmaster varchar(256);
	ALTER TABLE public.tbl_preincoming ADD COLUMN zgvmaster_datum date;
	ALTER TABLE public.tbl_preincoming ADD COLUMN zgvmaster_ort varchar(256);
	ALTER TABLE public.tbl_preincoming ADD COLUMN program_name varchar(256);
	ALTER TABLE public.tbl_preincoming ADD COLUMN bachelor boolean DEFAULT false;
	ALTER TABLE public.tbl_preincoming ADD COLUMN master boolean DEFAULT false;
	ALTER TABLE public.tbl_preincoming ADD COLUMN jahre smallint;
	ALTER TABLE public.tbl_preincoming ADD COLUMN person_id_emergency integer;
	ALTER TABLE public.tbl_preincoming ADD COLUMN person_id_coordinator_dep integer;
	ALTER TABLE public.tbl_preincoming ADD COLUMN person_id_coordinator_int integer;
	ALTER TABLE public.tbl_preincoming ADD COLUMN code varchar(64);
	
	ALTER TABLE public.tbl_preincoming ADD CONSTRAINT fk_person_preincoming_emergency FOREIGN KEY(person_id_emergency) REFERENCES public.tbl_person (person_id) ON UPDATE CASCADE ON DELETE RESTRICT;
	ALTER TABLE public.tbl_preincoming ADD CONSTRAINT fk_person_preincoming_coordinator_dep FOREIGN KEY(person_id_coordinator_dep) REFERENCES public.tbl_person (person_id) ON UPDATE CASCADE ON DELETE RESTRICT;
	ALTER TABLE public.tbl_preincoming ADD CONSTRAINT fk_person_preincoming_coordinator_int FOREIGN KEY(person_id_coordinator_int) REFERENCES public.tbl_person (person_id) ON UPDATE CASCADE ON DELETE RESTRICT;
	ALTER TABLE public.tbl_preincoming ALTER COLUMN bachelor SET NOT NULL;
	ALTER TABLE public.tbl_preincoming ALTER COLUMN master SET NOT NULL;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_preincoming: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle public.tbl_preincoming Erweiterungen hinzugefuegt!<br>';
}

// CMS Updates
if(!@$db->db_query("SELECT gesperrt_uid FROM campus.tbl_contentsprache LIMIT 1"))
{
	$qry = "
	ALTER TABLE campus.tbl_contentsprache ADD COLUMN gesperrt_uid varchar(32);
	ALTER TABLE campus.tbl_content ADD COLUMN aktiv boolean DEFAULT true;
	ALTER TABLE campus.tbl_content ADD COLUMN menu_open boolean DEFAULT true;
	ALTER TABLE campus.tbl_contentchild ADD COLUMN sort smallint;
	
	UPDATE campus.tbl_content SET aktiv=true;
	ALTER TABLE campus.tbl_content ALTER COLUMN aktiv SET NOT NULL;
	UPDATE campus.tbl_content SET menu_open=true;
	ALTER TABLE campus.tbl_content ALTER COLUMN menu_open SET NOT NULL;
	
	CREATE TABLE campus.tbl_contentlog
	(
		contentlog_id integer NOT NULL,
		uid varchar(32) NOT NULL,
		contentsprache_id integer NOT NULL,
		start timestamp,
		ende timestamp
	);
	
	CREATE SEQUENCE campus.seq_contentlog_contentlog_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;

	ALTER TABLE campus.tbl_contentlog ALTER COLUMN contentlog_id SET DEFAULT nextval('campus.seq_contentlog_contentlog_id');
		
	ALTER TABLE campus.tbl_contentlog ADD CONSTRAINT pk_contentlog PRIMARY KEY (contentlog_id);
	ALTER TABLE campus.tbl_contentlog ADD CONSTRAINT uk_contentlog_contentlog_id UNIQUE (contentlog_id);
	
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_contentlog TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_contentlog TO admin;
	
	GRANT SELECT, UPDATE ON SEQUENCE campus.seq_contentlog_contentlog_id TO admin;
	GRANT SELECT, UPDATE ON SEQUENCE campus.seq_contentlog_contentlog_id TO web;
	
	ALTER TABLE campus.tbl_contentlog ADD CONSTRAINT fk_benutzer_contentlog FOREIGN KEY(uid) REFERENCES public.tbl_benutzer (uid) ON UPDATE CASCADE ON DELETE RESTRICT;
	ALTER TABLE campus.tbl_contentlog ADD CONSTRAINT fk_contentsprache_contentlog FOREIGN KEY(contentsprache_id) REFERENCES campus.tbl_contentsprache (contentsprache_id) ON UPDATE CASCADE ON DELETE RESTRICT;

	
	";
	
	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_content: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle campus.tbl_content Erweiterungen hinzugefuegt!<br>';
}


// **************** Spalte oe bei Tabelle Projekt
if(!$result = @$db->db_query('SELECT oe_kurzbz FROM fue.tbl_projekt LIMIT 1;'))
{
	$qry = "ALTER TABLE fue.tbl_projekt ADD COLUMN oe_kurzbz VARCHAR(32);
			UPDATE fue.tbl_projekt SET oe_kurzbz='etw';
			ALTER TABLE fue.tbl_projekt ALTER COLUMN oe_kurzbz SET NOT NULL;
			ALTER TABLE fue.tbl_projekt ADD CONSTRAINT fk_projekt_oe FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;";
	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekt: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' fue.tbl_projekt: Spalte oe_kurzbz hinzugefuegt!<br>';
}

// ************** Tabellen fuer Projektphasen
if(!$result = @$db->db_query("SELECT * FROM fue.tbl_projektphase LIMIT 1;"))
{
	$qry = '
			CREATE SEQUENCE fue.seq_projektphase_projektphase_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			CREATE TABLE "fue"."tbl_projektphase"
			(
				"projektphase_id" integer NOT NULL,
				"projekt_kurzbz" Character varying(16),
				"projektphase_fk" Integer,
				"bezeichnung" Character varying(32) NOT NULL,
				"beschreibung" Text,
				"start" Date,
				"ende" Date,
				"budget" Numeric(12,2),
				"insertamum" Timestamp DEFAULT now(),
				"insertvon" Character varying(32),
				"updateamum" Timestamp DEFAULT now(),
				"updatevon" Character varying(32)
			)
			WITH (OIDS=FALSE);
			
			ALTER TABLE fue.tbl_projektphase ALTER COLUMN projektphase_id SET DEFAULT nextval(\'fue.seq_projektphase_projektphase_id\');
	
			ALTER TABLE "fue"."tbl_projektphase" ADD CONSTRAINT "pk_projektphase" PRIMARY KEY ("projektphase_id");
			ALTER TABLE "fue"."tbl_projektphase" ADD CONSTRAINT "fk_projektphase_projekt" FOREIGN KEY ("projekt_kurzbz") REFERENCES "fue"."tbl_projekt" ("projekt_kurzbz") ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE "fue"."tbl_projektphase" ADD CONSTRAINT "fk_projektphase_projektphase" FOREIGN KEY ("projektphase_fk") REFERENCES "fue"."tbl_projektphase" ("projektphase_id") ON DELETE RESTRICT ON UPDATE CASCADE;

			GRANT SELECT, UPDATE ON SEQUENCE fue.seq_projektphase_projektphase_id TO admin;
			GRANT SELECT, INSERT, UPDATE, DELETE ON fue.tbl_projektphase TO admin;
			';
	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projektphase: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' fue.tbl_projektphase: Tabelle neu erstellt!<br>';
}

if(!$result = @$db->db_query("SELECT * FROM fue.tbl_projekttask LIMIT 1;"))
{
	$qry = '
			CREATE SEQUENCE fue.seq_projekttask_projekttask_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			CREATE TABLE "fue"."tbl_projekttask"
			(
				"projekttask_id" Integer NOT NULL,
				"projektphase_id" Integer NOT NULL,
				"bezeichnung" Character varying(256),
				"beschreibung" Text,
				"aufwand" Smallint,
				"mantis_id" Bigint,
				"insertamum" Date DEFAULT now(),
				"insertvon" Character varying(32),
				"updateamum" Date DEFAULT now(),
				"updatevon" Character varying(32)
			)
			WITH (OIDS=FALSE);
			
			ALTER TABLE fue.tbl_projekttask ALTER COLUMN projekttask_id SET DEFAULT nextval(\'fue.seq_projekttask_projekttask_id\');
	
			ALTER TABLE "fue"."tbl_projekttask" ADD CONSTRAINT "pk_projekttask" PRIMARY KEY ("projekttask_id");
			ALTER TABLE "fue"."tbl_projekttask" ADD CONSTRAINT "fk_projekttask_projektphase" FOREIGN KEY ("projektphase_id") REFERENCES "fue"."tbl_projektphase" ("projektphase_id") ON DELETE RESTRICT ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE ON SEQUENCE fue.seq_projekttask_projekttask_id TO admin;
			GRANT SELECT, INSERT, UPDATE, DELETE ON fue.tbl_projekttask TO admin;
			';
	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekttask: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' fue.tbl_projekttask: Tabelle neu erstellt!<br>';
}

// ************** Tabellen fuer Notizen
if(!$result = @$db->db_query("SELECT * FROM public.tbl_notiz LIMIT 1;"))
{
	$qry = '
			CREATE SEQUENCE public.seq_notiz_notiz_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			CREATE TABLE public.tbl_notiz
			(
				notiz_id integer NOT NULL,
				titel Character varying(256) NOT NULL,
				text Text,
				verfasser_uid Character varying(32) NOT NULL,
				bearbeiter_uid Character varying(32) NOT NULL,
				start Date,
				ende Date,
				erledigt Boolean NOT NULL,
				insertamum Timestamp DEFAULT now(),
				insertvon Character varying(32),
				updateamum Timestamp DEFAULT now(),
				updatevon Character varying(32)
			)
			WITH (OIDS=FALSE);
			
			ALTER TABLE public.tbl_notiz ALTER COLUMN notiz_id SET DEFAULT nextval(\'public.seq_notiz_notiz_id\');
	
			ALTER TABLE public.tbl_notiz ADD CONSTRAINT pk_notiz PRIMARY KEY (notiz_id);
			ALTER TABLE public.tbl_notiz ADD CONSTRAINT fk_notiz_benutzer_verfasser FOREIGN KEY (verfasser_uid) REFERENCES public.tbl_benutzer (uid) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE public.tbl_notiz ADD CONSTRAINT fk_notiz_benutzer_bearbeiter FOREIGN KEY (bearbeiter_uid) REFERENCES public.tbl_benutzer (uid) ON DELETE CASCADE ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE ON SEQUENCE public.seq_notiz_notiz_id TO admin;
			GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_notiz TO admin;
			';
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_notiz: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' public.tbl_notiz: Tabelle neu erstellt!<br>';
}

if(!$result = @$db->db_query("SELECT * FROM public.tbl_notizzuordnung LIMIT 1;"))
{
	$qry = 'CREATE SEQUENCE public.seq_notizzuordnung_notizzuordnung_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;
			
			CREATE TABLE public.tbl_notizzuordnung
			(
				notizzuordnung_id Integer NOT NULL,
				notiz_id Integer NOT NULL,
				projekt_kurzbz Character varying(16),
				projektphase_id Integer,
				projekttask_id Integer,
				uid Character varying(32),
				person_id Integer,
				prestudent_id Integer,
				bestellung_id Integer
			)
			WITH (OIDS=FALSE);
			
			ALTER TABLE public.tbl_notizzuordnung ALTER COLUMN notizzuordnung_id SET DEFAULT nextval(\'public.seq_notizzuordnung_notizzuordnung_id\');
	
			ALTER TABLE public.tbl_notizzuordnung ADD CONSTRAINT pk_notizzuordnung PRIMARY KEY (notizzuordnung_id);
			ALTER TABLE "public"."tbl_notizzuordnung" ADD CONSTRAINT "fk_notizzuordnung_benutzer" FOREIGN KEY ("uid") REFERENCES "public"."tbl_benutzer" ("uid") ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE "public"."tbl_notizzuordnung" ADD CONSTRAINT "fk_notizzuordnung_notiz" FOREIGN KEY ("notiz_id") REFERENCES "public"."tbl_notiz" ("notiz_id") ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE "public"."tbl_notizzuordnung" ADD CONSTRAINT "fk_notizzuordnung_projekt" FOREIGN KEY ("projekt_kurzbz") REFERENCES "fue"."tbl_projekt" ("projekt_kurzbz") ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE "public"."tbl_notizzuordnung" ADD CONSTRAINT "fk_notizzuordnung_projektphase" FOREIGN KEY ("projektphase_id") REFERENCES "fue"."tbl_projektphase" ("projektphase_id") ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE "public"."tbl_notizzuordnung" ADD CONSTRAINT "fk_notizzuordnung_projekttask" FOREIGN KEY ("projekttask_id") REFERENCES "fue"."tbl_projekttask" ("projekttask_id") ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE "public"."tbl_notizzuordnung" ADD CONSTRAINT "fk_notizzuordnung_person" FOREIGN KEY ("person_id") REFERENCES "public"."tbl_person" ("person_id") ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE "public"."tbl_notizzuordnung" ADD CONSTRAINT "fk_notizzuordnung_prestudent" FOREIGN KEY ("prestudent_id") REFERENCES "public"."tbl_prestudent" ("prestudent_id") ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE "public"."tbl_notizzuordnung" ADD CONSTRAINT "fk_notizzuordnung_bestellung" FOREIGN KEY ("bestellung_id") REFERENCES "wawi"."tbl_bestellung" ("bestellung_id") ON DELETE CASCADE ON UPDATE CASCADE;
			
			GRANT SELECT, UPDATE ON SEQUENCE public.seq_notizzuordnung_notizzuordnung_id TO admin;
			GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_notizzuordnung TO admin;
			';
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_notizzuordnung: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' public.tbl_notizzuordnung: Tabelle neu erstellt!<br>';
}

if(!$result = @$db->db_query('SELECT zgvmaster_name FROM public.tbl_preincoming LIMIT 1;'))
{
	$qry = "ALTER TABLE public.tbl_preincoming ADD COLUMN zgvmaster_name VARCHAR(256);
			GRANT DELETE ON public.tbl_person TO web;";
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_preincoming: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' public.tbl_preincoming: Spalte zgvmaster_name hinzugefuegt!<br>';
}

if(!$result = @$db->db_query('SELECT content_id FROM campus.tbl_news LIMIT 1;'))
{
	$qry = "ALTER TABLE campus.tbl_news ADD COLUMN content_id integer;
			DROP TABLE campus.tbl_newssprache;
			ALTER TABLE campus.tbl_news ADD CONSTRAINT fk_content_news FOREIGN KEY (content_id) REFERENCES campus.tbl_content (content_id) ON DELETE RESTRICT ON UPDATE CASCADE;
			CREATE INDEX idx_contentsprache_sprache ON campus.tbl_contentsprache (sprache);
			CREATE INDEX idx_contentsprache_version ON campus.tbl_contentsprache (version);
			CREATE INDEX idx_contentsprache_content_id ON campus.tbl_contentsprache (content_id);";
	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_news: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'campus.tbl_news: Spalte content_id hinzugefuegt, tbl_newssprache entfernt, div. Indizes hinzugefuegt!<br>';
}

//tbl_contentgruppe.gruppe_kurzbz auf 32 Zeichen verlaengern
if($result = @$db->db_query("SELECT character_maximum_length as len FROM information_schema.columns WHERE column_name='gruppe_kurzbz' AND table_name='tbl_contentgruppe' AND table_schema='campus';"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->len=='16')
		{
			$qry = "ALTER TABLE campus.tbl_contentgruppe ALTER COLUMN gruppe_kurzbz TYPE varchar(32);";
			
			if(!$db->db_query($qry))
				echo '<strong>campus.tbl_contentgruppe: '.$db->db_last_error().'</strong><br>';
			else 
				echo 'campus.tbl_contentgruppe: Spalte gruppe_kurzbz auf 32 Zeichen verlaengert!<br>';
		}
	}
}

//tbl_content beschreibung hinzufuegen
if(!$result = @$db->db_query("SELECT beschreibung FROM campus.tbl_content LIMIT 1;"))
{
	$qry = "ALTER TABLE campus.tbl_content ADD COLUMN beschreibung text;";
			
	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_content: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'campus.tbl_content: Spalte beschreibung hinzugefuegt!<br>';
}

//fue.tbl_projektphase neue Spalte personentage
if(!$result = @$db->db_query("SELECT personentage FROM fue.tbl_projektphase LIMIT 1;"))
{
	$qry = "ALTER TABLE fue.tbl_projektphase ADD COLUMN personentage integer;
			GRANT SELECT, UPDATE ON SEQUENCE fue.seq_projektphase_projektphase_id TO vilesci;
			GRANT SELECT, UPDATE ON SEQUENCE fue.seq_projekttask_projekttask_id TO vilesci;";
			
	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projektphase: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'fue.tbl_projektphase: Spalte personentage hinzugefuegt!<br>';
}

if(!$result = @$db->db_query("SELECT 1 FROM campus.tbl_dms_version LIMIT 1;"))
{
	$qry = "
	ALTER TABLE campus.tbl_dms RENAME TO tbl_dms_version;
	CREATE TABLE campus.tbl_dms
	(
		dms_id bigint NOT NULL,
		oe_kurzbz varchar(32),
		dokument_kurzbz varchar(8),
		kategorie_kurzbz varchar(32)
	);
	
	ALTER TABLE campus.tbl_dms ADD CONSTRAINT fk_organisationseinheit_dms FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_dms ADD CONSTRAINT fk_dokument_dms FOREIGN KEY (dokument_kurzbz) REFERENCES public.tbl_dokument (dokument_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_dms ADD CONSTRAINT fk_dms_kategorie_dms FOREIGN KEY (kategorie_kurzbz) REFERENCES campus.tbl_dms_kategorie (kategorie_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_dms ADD CONSTRAINT pk_tbl_dms PRIMARY KEY (dms_id);
	ALTER TABLE campus.tbl_dms ALTER COLUMN dms_id SET DEFAULT nextval('campus.seq_dms_dms_id');

	ALTER TABLE campus.tbl_dms_version ALTER COLUMN dms_id DROP DEFAULT;

	INSERT INTO campus.tbl_dms(dms_id, oe_kurzbz, dokument_kurzbz, kategorie_kurzbz) SELECT distinct on(dms_id)  dms_id, oe_kurzbz, dokument_kurzbz, kategorie_kurzbz FROM campus.tbl_dms_version; 
	
	ALTER TABLE campus.tbl_dms_version ADD CONSTRAINT fk_dms_version_dms_id FOREIGN KEY (dms_id) REFERENCES campus.tbl_dms (dms_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_dms_version DROP COLUMN oe_kurzbz;
	ALTER TABLE campus.tbl_dms_version DROP COLUMN dokument_kurzbz;
	ALTER TABLE campus.tbl_dms_version DROP COLUMN kategorie_kurzbz;
	
	GRANT SELECT, UPDATE, INSERT, DELETE ON campus.tbl_dms TO vilesci;
	GRANT SELECT, UPDATE, INSERT, DELETE ON campus.tbl_dms TO web;
	
	CREATE SEQUENCE fue.seq_projekt_dokument_projekt_dokument_id
	 	INCREMENT BY 1
	 	NO MAXVALUE
		NO MINVALUE
		CACHE 1;
		
	CREATE TABLE fue.tbl_projekt_dokument
	(
		projekt_dokument_id bigint NOT NULL,
		projektphase_id bigint,
		projekt_kurzbz varchar(16),
		dms_id bigint
	);
	ALTER TABLE fue.tbl_projekt_dokument ADD CONSTRAINT fk_projekt_dokument_dms FOREIGN KEY (dms_id) REFERENCES campus.tbl_dms (dms_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_projekt_dokument ADD CONSTRAINT fk_projekt_dokument_projekt FOREIGN KEY (projekt_kurzbz) REFERENCES fue.tbl_projekt (projekt_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_projekt_dokument ADD CONSTRAINT fk_projekt_dokument_projektphase FOREIGN KEY (projektphase_id) REFERENCES fue.tbl_projektphase (projektphase_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_projekt_dokument ADD CONSTRAINT pk_tbl_projekt_dokument PRIMARY KEY (projekt_dokument_id);
	ALTER TABLE fue.tbl_projekt_dokument ALTER COLUMN projekt_dokument_id SET DEFAULT nextval('fue.seq_projekt_dokument_projekt_dokument_id');
	
	ALTER TABLE fue.tbl_projekttask ADD COLUMN projekttask_fk bigint;
	ALTER TABLE fue.tbl_projekttask ADD CONSTRAINT fk_projekttask_projekttask FOREIGN KEY (projekttask_fk) REFERENCES fue.tbl_projekttask (projekttask_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	
	GRANT SELECT, UPDATE, INSERT, DELETE ON fue.tbl_projekt_dokument TO vilesci;
	GRANT SELECT, UPDATE, INSERT, DELETE ON fue.tbl_projekt_dokument TO web;
	GRANT SELECT, UPDATE ON SEQUENCE fue.seq_projekt_dokument_projekt_dokument_id TO vilesci;
	GRANT SELECT, UPDATE ON SEQUENCE fue.seq_projekt_dokument_projekt_dokument_id TO web;
	";
			
	if(!$db->db_query($qry))
		echo '<strong>DMS Update: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'DMS Update: DMS und FuE Update!<br>';
}

if(!$result = @$db->db_query("SELECT 1 FROM fue.tbl_ressource LIMIT 1;"))
{
	$qry = "
	CREATE SEQUENCE fue.seq_ressource_ressource_id
	 	INCREMENT BY 1
	 	NO MAXVALUE
		NO MINVALUE
		CACHE 1;
		
	CREATE TABLE fue.tbl_ressource 
	(
	    ressource_id BIGINT NOT NULL,
	    bezeichnung character varying(256),
	    beschreibung text,
	    mitarbeiter_uid character varying(32),
	    student_uid  character varying(32),
	    betriebsmittel_id integer,
	    firma_id integer,
	    insertamum date DEFAULT now() NOT NULL,
	    insertvon character varying(32),
	    updateamum date DEFAULT now() NOT NULL,
	    updatevon character varying(32)
	); 
	
	ALTER TABLE fue.tbl_ressource ADD CONSTRAINT fk_mitarbeiter_ressource FOREIGN KEY (mitarbeiter_uid) REFERENCES public.tbl_mitarbeiter (mitarbeiter_uid) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_ressource ADD CONSTRAINT fk_student_ressource FOREIGN KEY (student_uid) REFERENCES public.tbl_student (student_uid) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_ressource ADD CONSTRAINT fk_betriebsmittel_ressource FOREIGN KEY (betriebsmittel_id) REFERENCES wawi.tbl_betriebsmittel (betriebsmittel_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_ressource ADD CONSTRAINT fk_firma_ressource FOREIGN KEY (firma_id) REFERENCES public.tbl_firma (firma_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_ressource ADD CONSTRAINT pk_tbl_ressource PRIMARY KEY (ressource_id);
	ALTER TABLE fue.tbl_ressource ALTER COLUMN ressource_id SET DEFAULT nextval('fue.seq_ressource_ressource_id');
	
	GRANT SELECT, UPDATE, INSERT, DELETE ON fue.tbl_ressource TO vilesci;
	GRANT SELECT ON fue.tbl_ressource TO web;
	GRANT SELECT, UPDATE ON SEQUENCE fue.seq_ressource_ressource_id TO web;
	GRANT SELECT, UPDATE ON SEQUENCE fue.seq_ressource_ressource_id TO vilesci;
	
	CREATE SEQUENCE fue.seq_projekt_ressource_projekt_ressource_id
	 	INCREMENT BY 1
	 	NO MAXVALUE
		NO MINVALUE
		CACHE 1;
		
	CREATE TABLE fue.tbl_projekt_ressource
	(
		projekt_ressource_id bigint NOT NULL,
		projektphase_id bigint,
		projekt_kurzbz varchar(16),
		ressource_id bigint,
		funktion_kurzbz character varying(16),
		beschreibung text
	);

	ALTER TABLE fue.tbl_projekt_ressource ADD CONSTRAINT fk_projekt_ressource_funktion FOREIGN KEY (funktion_kurzbz) REFERENCES public.tbl_funktion (funktion_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_projekt_ressource ADD CONSTRAINT fk_projekt_ressource_ressource FOREIGN KEY (ressource_id) REFERENCES fue.tbl_ressource (ressource_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_projekt_ressource ADD CONSTRAINT fk_projekt_ressource_projekt FOREIGN KEY (projekt_kurzbz) REFERENCES fue.tbl_projekt (projekt_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_projekt_ressource ADD CONSTRAINT fk_projekt_ressource_projektphase FOREIGN KEY (projektphase_id) REFERENCES fue.tbl_projektphase (projektphase_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE fue.tbl_projekt_ressource ADD CONSTRAINT pk_tbl_projekt_ressource PRIMARY KEY (projekt_ressource_id);
	ALTER TABLE fue.tbl_projekt_ressource ALTER COLUMN projekt_ressource_id SET DEFAULT nextval('fue.seq_projekt_ressource_projekt_ressource_id');
	
	GRANT SELECT, UPDATE, INSERT, DELETE ON fue.tbl_projekt_ressource TO vilesci;
	GRANT SELECT ON fue.tbl_projekt_ressource TO web;
	GRANT SELECT, UPDATE ON SEQUENCE fue.seq_projekt_ressource_projekt_ressource_id TO web;
	GRANT SELECT, UPDATE ON SEQUENCE fue.seq_projekt_ressource_projekt_ressource_id TO vilesci;
	
	INSERT INTO fue.tbl_ressource(student_uid, mitarbeiter_uid, betriebsmittel_id, firma_id, bezeichnung, beschreibung, insertamum, insertvon, updateamum, updatevon) SELECT distinct on(uid) null, uid, null, null, null, null, now(), null, now(), null FROM fue.tbl_projektbenutzer;
	INSERT INTO fue.tbl_projekt_ressource (projekt_kurzbz, projektphase_id, ressource_id, funktion_kurzbz, beschreibung) SELECT projekt_kurzbz, null, (SELECT ressource_id FROM fue.tbl_ressource WHERE mitarbeiter_uid=uid), funktion_kurzbz, null FROM fue.tbl_projektbenutzer;
	
	DROP TABLE fue.tbl_projektbenutzer;
	";
			
	if(!$db->db_query($qry))
		echo '<strong>Projekt Ressourcen: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Projekt Ressoruce: Ressourcen hinzugefuegt!<br>';
}

//fue.tbl_projekt neue Spalte budget
if(!$result = @$db->db_query("SELECT budget FROM fue.tbl_projekt LIMIT 1;"))
{
	$qry = "ALTER TABLE fue.tbl_projekt ADD COLUMN budget numeric(12,2);
			ALTER TABLE fue.tbl_projekttask ADD COLUMN erledigt boolean DEFAULT false;
			ALTER TABLE public.tbl_notiz ALTER COLUMN bearbeiter_uid DROP NOT NULL;
			";
			
	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekt: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'fue.tbl_projekt: Spalte budget hinzugefuegt!<br>
				fue.tbl_projekttask: Spalte erledigt hinzugefuegt!<br>';
}

//public.tbl_prestudent neue Spalte ausstellungsstaat
if(!$result = @$db->db_query("SELECT ausstellungsstaat FROM public.tbl_prestudent"))
{
	$qry = "ALTER TABLE public.tbl_prestudent ADD COLUMN ausstellungsstaat varchar(3);
			ALTER TABLE public.tbl_prestudent ADD CONSTRAINT fk_prestudent_nation FOREIGN KEY (ausstellungsstaat) REFERENCES bis.tbl_nation (nation_code) ON DELETE RESTRICT ON UPDATE CASCADE;			
			";
			
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudent: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_prestudent: Spalte ausstellungsstaat hinzugefuegt!<br>';
}

//wawi.tbl_betriebsmittel neue Spalte anschaffungswert und anschaffungsdatum
if(!$result = @$db->db_query("SELECT anschaffungswert FROM wawi.tbl_betriebsmittel"))
{
	$qry = "ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN anschaffungswert numeric(12,2);
			ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN anschaffungsdatum date;
			ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN tiefe numeric(6,2);
			ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN hoehe numeric(6,2);
			ALTER TABLE wawi.tbl_betriebsmittel ADD COLUMN breite numeric(6,2);
			ALTER TABLE wawi.tbl_betriebsmitteltyp ADD COLUMN mastershapename varchar(256);
			
			UPDATE wawi.tbl_betriebsmittel SET anschaffungswert = (SELECT (preisprove/100*(100+mwst)) FROM wawi.tbl_bestelldetail WHERE bestelldetail_id=tbl_betriebsmittel.bestelldetail_id);
			UPDATE wawi.tbl_betriebsmittel SET anschaffungsdatum = (SELECT datum FROM wawi.tbl_bestellung_bestellstatus WHERE bestellung_id=tbl_betriebsmittel.bestellung_id AND bestellstatus_kurzbz='Lieferung');
			";
			
	if(!$db->db_query($qry))
		echo '<strong>wawi.tbl_betriebsmittel: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'wawi.tbl_betriebsmittel: Spalte anschaffungswert und anschaffungsdatum hinzugefuegt!<br>';
}

//tbl_ort.planbezeichnung von 5 auf 8 Zeichen verlaengern
if($result = @$db->db_query("SELECT character_maximum_length as len FROM information_schema.columns WHERE column_name='planbezeichnung' AND table_name='tbl_ort' AND table_schema='public';"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->len=='5')
		{
			$qry = "ALTER TABLE public.tbl_ort ALTER COLUMN planbezeichnung TYPE varchar(8);";
			
			if(!$db->db_query($qry))
				echo '<strong>public.tbl_ort: '.$db->db_last_error().'</strong><br>';
			else 
				echo 'public.tbl_ort: Spalte planbezeichnung auf 8 Zeichen verlaengert!<br>';
		}
	}
}

//fue.tbl_projekttask ende und ressource spalte
if(!$result = @$db->db_query("SELECT ende FROM fue.tbl_projekttask"))
{
	$qry = "ALTER TABLE fue.tbl_projekttask ADD COLUMN ende date;
			ALTER TABLE fue.tbl_projekttask ADD COLUMN ressource_id integer;

			ALTER TABLE fue.tbl_projekttask ADD CONSTRAINT fk_ressource_projekttask FOREIGN KEY (ressource_id) REFERENCES fue.tbl_ressource (ressource_id) ON DELETE RESTRICT ON UPDATE CASCADE;	
			";
			
	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekttask: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'fue.tbl_projekttask: Spalte ende und ressource_id hinzugefuegt!<br>';
}

//public.tbl_ampel Tabellen fuer Ampelsystem
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_ampel"))
{
	$qry = "
		
			CREATE SEQUENCE public.seq_ampel_ampel_id
			 	INCREMENT BY 1
			 	NO MAXVALUE
				NO MINVALUE
				CACHE 1;
				
			CREATE SEQUENCE public.seq_ampel_benutzer_bestaetigt_ampel_benutzer_bestaetigt_id
			 	INCREMENT BY 1
			 	NO MAXVALUE
				NO MINVALUE
				CACHE 1;
				
			CREATE TABLE public.tbl_ampel
			(
				ampel_id bigint NOT NULL,
				kurzbz varchar(64),
				beschreibung text,
				benutzer_select text NOT NULL,
				deadline date NOT NULL,
				vorlaufzeit smallint NOT NULL,
				verfallszeit smallint NOT NULL,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);
			
			CREATE TABLE public.tbl_ampel_benutzer_bestaetigt
			(
				ampel_benutzer_bestaetigt_id bigint NOT NULL,
				ampel_id integer NOT NULL,
				uid varchar(32) NOT NULL,
				insertamum timestamp,
				insertvon varchar(32)
			);
			
			ALTER TABLE public.tbl_ampel ADD CONSTRAINT pk_tbl_ampel PRIMARY KEY (ampel_id);
			ALTER TABLE public.tbl_ampel_benutzer_bestaetigt ADD CONSTRAINT pk_tbl_ampel_benutzer_bestaetigt PRIMARY KEY (ampel_benutzer_bestaetigt_id);
			ALTER TABLE public.tbl_ampel ALTER COLUMN ampel_id SET DEFAULT nextval('public.seq_ampel_ampel_id');
			ALTER TABLE public.tbl_ampel_benutzer_bestaetigt ALTER COLUMN ampel_benutzer_bestaetigt_id SET DEFAULT nextval('public.seq_ampel_benutzer_bestaetigt_ampel_benutzer_bestaetigt_id');
			
			ALTER TABLE public.tbl_ampel_benutzer_bestaetigt ADD CONSTRAINT fk_ampel_ampel_benutzer_bestaetigt FOREIGN KEY (ampel_id) REFERENCES public.tbl_ampel (ampel_id) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE public.tbl_ampel_benutzer_bestaetigt ADD CONSTRAINT fk_benutzer_ampel_benutzer_bestaetigt FOREIGN KEY (uid) REFERENCES public.tbl_benutzer (uid) ON DELETE CASCADE ON UPDATE CASCADE;
	
			GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_ampel TO vilesci;
			GRANT SELECT ON public.tbl_ampel TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_ampel_benutzer_bestaetigt TO vilesci;
			GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_ampel_benutzer_bestaetigt TO web;
			GRANT SELECT, UPDATE ON SEQUENCE public.seq_ampel_ampel_id TO web;
			GRANT SELECT, UPDATE ON SEQUENCE public.seq_ampel_ampel_id TO vilesci;
			GRANT SELECT, UPDATE ON SEQUENCE public.seq_ampel_benutzer_bestaetigt_ampel_benutzer_bestaetigt_id TO web;
			GRANT SELECT, UPDATE ON SEQUENCE public.seq_ampel_benutzer_bestaetigt_ampel_benutzer_bestaetigt_id TO vilesci;
			
			";
			
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_ampel: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_ampel: Tabellen fuer Ampelsystem hinzugefuegt!<br>';
}

//public.tbl_preincoming
if(!$result = @$db->db_query("SELECT research_area FROM public.tbl_preincoming LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_preincoming ADD COLUMN deutschkurs1 boolean;
			ALTER TABLE public.tbl_preincoming ADD COLUMN deutschkurs2 boolean;
			ALTER TABLE public.tbl_preincoming ADD COLUMN research_area text;
			
			GRANT USAGE ON SCHEMA fue TO wawi;
			GRANT SELECT ON fue.tbl_ressource TO wawi;
			GRANT SELECT ON fue.tbl_projekt_ressource TO wawi;
			GRANT SELECT ON fue.tbl_projekt TO wawi;
			";
			
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_preincoming: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_preincoming: Spalten hinzugefuegt!<br>';
}

// deutschkurs3 zu public.tbl_preincoming
if(!$result = @$db->db_query("SELECT deutschkurs3 FROM public.tbl_preincoming LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_preincoming ADD COLUMN deutschkurs3 boolean;";
			
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_preincoming: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'public.tbl_preincoming: Spalte deutschkurs3 hinzugefuegt!<br>';
}

// mimetype zur tbl_vorlagestudiengang hinzufuegen
if(!@$db->db_query("SELECT mimetype FROM public.tbl_vorlage LIMIT 1"))
{
	$qry = "
	ALTER TABLE public.tbl_vorlage ADD COLUMN mimetype varchar(64);
	";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_vorlage: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle public.tbl_vorlage Spalte mimetype hinzugefuegt!<br>';
}

// tbl_webservicetyp und tbl_webservicelog hinzufügen
if(!@$db->db_query("SELECT 1 FROM system.tbl_webservicelog LIMIT 1"))
{
	$qry = "
	CREATE SEQUENCE system.seq_webservicelog_webservicelog_id
 	INCREMENT BY 1
 	NO MAXVALUE
	NO MINVALUE
	CACHE 1;
	
	CREATE TABLE system.tbl_webservicetyp
	(
		webservicetyp_kurzbz varchar(32) NOT NULL,
		beschreibung varchar(256)
	);
		
	CREATE TABLE system.tbl_webservicelog
	(
		webservicelog_id Integer NOT NULL,
		webservicetyp_kurzbz Character varying(32) NOT NULL,
		request_id Character varying(64),
		beschreibung Character varying(256), 
		request_data Text,
		execute_time Timestamp NOT NULL,
		execute_user Character varying(32)
	);
			
	ALTER TABLE system.tbl_webservicelog ALTER COLUMN webservicelog_id SET DEFAULT nextval('system.seq_webservicelog_webservicelog_id');
	
	ALTER TABLE system.tbl_webservicelog ADD CONSTRAINT pk_webservicelog_id PRIMARY KEY (webservicelog_id);
	ALTER TABLE system.tbl_webservicetyp ADD CONSTRAINT pk_webservicetyp PRIMARY KEY (webservicetyp_kurzbz);
	
	ALTER TABLE system.tbl_webservicelog ADD CONSTRAINT fk_webservicetyp_webservicelog FOREIGN KEY(webservicetyp_kurzbz) REFERENCES system.tbl_webservicetyp(webservicetyp_kurzbz) ON DELETE CASCADE ON UPDATE CASCADE;
	
	GRANT SELECT, INSERT, DELETE, UPDATE ON system.tbl_webservicelog TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_webservicelog TO web;
	GRANT SELECT, INSERT, DELETE, UPDATE ON system.tbl_webservicetyp TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_webservicetyp TO web;
	
	GRANT SELECT, UPDATE ON system.seq_webservicelog_webservicelog_id TO admin;
	GRANT SELECT, UPDATE ON system.seq_webservicelog_webservicelog_id TO web;
	
	INSERT INTO system.tbl_webservicetyp(webservicetyp_kurzbz, beschreibung) VALUES('stip','Schnittstelle der Stipendienstelle');
	INSERT INTO system.tbl_webservicetyp(webservicetyp_kurzbz, beschreibung) VALUES('wienerlinien','Schnittstelle der Wiener Linien');
	";
	
	if(!$db->db_query($qry))
		echo '<strong>system.tbl_webservicelog: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle system.tbl_webservicelog und system.tbl_webservicetyp hinzugefuegt!<br>';
}

// Beschreibung der Ampel auf Array aendern damit mehrere Sprachen verwendet werden koennen
if($result = $db->db_query("SELECT * FROM information_schema.columns WHERE table_name='tbl_ampel' AND table_schema='public' AND column_name='beschreibung' AND data_type='text'"))
{
	//Wenn die Spalte vom Datentyp Text ist wird diese auf Array geaendert
	if($db->db_num_rows($result)>0)
	{
		/*
		 * - Neue Spalte hinzufuegen
		 * - Inhalt auf index 1 kopieren
		 * - Alte Spalte entfernen
		 * - Neue Spalte umbennen
		 */
		$qry = "ALTER TABLE public.tbl_ampel ADD COLUMN beschreibung_neu text[];
				UPDATE public.tbl_ampel SET beschreibung_neu[1]=beschreibung;
				ALTER TABLE public.tbl_ampel DROP COLUMN beschreibung;
				ALTER TABLE public.tbl_ampel RENAME COLUMN beschreibung_neu TO beschreibung;";
	
		if(!$db->db_query($qry))
			echo '<strong>public.tbl_ampel: '.$db->db_last_error().'</strong><br>';
		else 
			echo 'Tabelle public.tbl_ampel Bezeichnung auf Array geaendert!<br>';
	}
}

// Anmerkung bei Lehreinheiten und LV-Plan auf Text aendern 
if($result = $db->db_query("SELECT * FROM information_schema.columns WHERE table_name='tbl_lehreinheit' AND table_schema='lehre' AND column_name='anmerkung' AND data_type='text'"))
{
	//Wenn die Spalte nicht vom Datentyp Text ist wird diese auf Text geaendert
	if($db->db_num_rows($result)==0)
	{
		$qry = "DROP view lehre.vw_stundenplan;
				DROP view lehre.vw_stundenplandev;
				DROP view lehre.vw_lva_stundenplandev;
				DROP view lehre.vw_lva_stundenplan;
				DROP view campus.vw_lehreinheit;
				DROP view campus.vw_stundenplan;
				
				ALTER TABLE lehre.tbl_lehreinheit ALTER COLUMN anmerkung TYPE text;
				ALTER TABLE lehre.tbl_stundenplan ALTER COLUMN anmerkung TYPE text;
				ALTER TABLE lehre.tbl_stundenplandev ALTER COLUMN anmerkung TYPE text;
				
				CREATE VIEW lehre.vw_stundenplan AS
				 SELECT tbl_stundenplan.stundenplan_id, tbl_stundenplan.unr, tbl_stundenplan.mitarbeiter_uid AS uid, tbl_stundenplan.lehreinheit_id, tbl_lehreinheit.lehrfach_id, tbl_stundenplan.datum, tbl_stundenplan.stunde, tbl_stundenplan.ort_kurzbz, tbl_stundenplan.studiengang_kz, tbl_stundenplan.semester, tbl_stundenplan.verband, tbl_stundenplan.gruppe, tbl_stundenplan.gruppe_kurzbz, tbl_stundenplan.titel, tbl_stundenplan.anmerkung, tbl_stundenplan.fix, tbl_lehreinheit.lehrveranstaltung_id, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bezeichnung, tbl_studiengang.typ AS stg_typ, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehreinheit.lehrform_kurzbz AS lehrform, tbl_mitarbeiter.kurzbz AS lektor, tbl_stundenplan.updateamum, tbl_stundenplan.updatevon, tbl_stundenplan.insertamum, tbl_stundenplan.insertvon, tbl_lehreinheit.anmerkung AS anmerkung_lehreinheit
				   FROM lehre.tbl_stundenplan
				   JOIN public.tbl_studiengang USING (studiengang_kz)
				   JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
				   JOIN lehre.tbl_lehrfach USING (lehrfach_id)
				   JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);
				GRANT SELECT ON lehre.vw_stundenplan TO web;
				GRANT SELECT ON lehre.vw_stundenplan TO admin;
				
				CREATE VIEW lehre.vw_stundenplandev AS
				 SELECT tbl_stundenplandev.stundenplandev_id, tbl_stundenplandev.unr, tbl_stundenplandev.mitarbeiter_uid AS uid, tbl_stundenplandev.lehreinheit_id, tbl_lehreinheit.lehrfach_id, tbl_stundenplandev.datum, tbl_stundenplandev.stunde, tbl_stundenplandev.ort_kurzbz, tbl_stundenplandev.studiengang_kz, tbl_stundenplandev.semester, tbl_stundenplandev.verband, tbl_stundenplandev.gruppe, tbl_stundenplandev.gruppe_kurzbz, tbl_stundenplandev.titel, tbl_stundenplandev.anmerkung, tbl_stundenplandev.fix, tbl_lehreinheit.lehrveranstaltung_id, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bezeichnung, tbl_studiengang.typ AS stg_typ, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehreinheit.lehrform_kurzbz AS lehrform, tbl_mitarbeiter.kurzbz AS lektor, tbl_stundenplandev.updateamum, tbl_stundenplandev.updatevon, tbl_stundenplandev.insertamum, tbl_stundenplandev.insertvon, tbl_lehreinheit.anmerkung AS anmerkung_lehreinheit
				   FROM lehre.tbl_stundenplandev
				   JOIN public.tbl_studiengang USING (studiengang_kz)
				   JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
				   JOIN lehre.tbl_lehrfach USING (lehrfach_id)
				   JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);
				GRANT SELECT ON lehre.vw_stundenplandev TO web;
				GRANT SELECT ON lehre.vw_stundenplandev TO admin;
				
				CREATE VIEW lehre.vw_lva_stundenplandev AS
				 SELECT le.lehreinheit_id, le.unr, le.lvnr, tbl_lehrfach.fachbereich_kurzbz, le.lehrfach_id, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe AS lehrfach_farbe, le.lehrform_kurzbz AS lehrform, lema.mitarbeiter_uid AS lektor_uid, tbl_mitarbeiter.kurzbz AS lektor, tbl_studiengang.studiengang_kz, upper(tbl_studiengang.typ::character varying::text || tbl_studiengang.kurzbz::text) AS studiengang, lvb.semester, lvb.verband, lvb.gruppe, lvb.gruppe_kurzbz, le.raumtyp, le.raumtypalternativ, le.stundenblockung, le.wochenrythmus, lema.semesterstunden, lema.planstunden, le.start_kw, le.anmerkung, le.studiensemester_kurzbz, ( SELECT count(*) AS count
				           FROM lehre.tbl_stundenplandev
				          WHERE tbl_stundenplandev.mitarbeiter_uid::text = lema.mitarbeiter_uid::text AND tbl_stundenplandev.studiengang_kz = lvb.studiengang_kz AND tbl_stundenplandev.semester = lvb.semester AND (tbl_stundenplandev.verband = lvb.verband OR (tbl_stundenplandev.verband IS NULL OR tbl_stundenplandev.verband = ''::bpchar) AND lvb.verband IS NULL) AND (tbl_stundenplandev.gruppe = lvb.gruppe OR (tbl_stundenplandev.gruppe IS NULL OR tbl_stundenplandev.gruppe = ''::bpchar) AND lvb.gruppe IS NULL) AND (tbl_stundenplandev.gruppe_kurzbz::text = lvb.gruppe_kurzbz::text OR tbl_stundenplandev.gruppe_kurzbz IS NULL AND lvb.gruppe_kurzbz IS NULL) AND tbl_stundenplandev.lehreinheit_id = lvb.lehreinheit_id) AS verplant
				   FROM lehre.tbl_lehreinheit le
				   JOIN lehre.tbl_lehreinheitmitarbeiter lema USING (lehreinheit_id)
				   JOIN lehre.tbl_lehreinheitgruppe lvb USING (lehreinheit_id)
				   JOIN public.tbl_studiengang ON lvb.studiengang_kz = tbl_studiengang.studiengang_kz
				   JOIN lehre.tbl_lehrfach USING (lehrfach_id)
				   JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);
				GRANT SELECT ON lehre.vw_lva_stundenplandev TO web;
				GRANT SELECT ON lehre.vw_lva_stundenplandev TO admin;
				
				CREATE VIEW lehre.vw_lva_stundenplan AS
				 SELECT le.lehreinheit_id, le.unr, le.lvnr, tbl_lehrfach.fachbereich_kurzbz, le.lehrfach_id, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe AS lehrfach_farbe, le.lehrform_kurzbz AS lehrform, lema.mitarbeiter_uid AS lektor_uid, ma.kurzbz AS lektor, tbl_studiengang.studiengang_kz, tbl_studiengang.kurzbz AS studiengang, lvb.semester, lvb.verband, lvb.gruppe, lvb.gruppe_kurzbz, le.raumtyp, le.raumtypalternativ, le.stundenblockung, le.wochenrythmus, lema.semesterstunden, lema.planstunden, le.start_kw, le.anmerkung, le.studiensemester_kurzbz, ( SELECT count(*) AS count
				           FROM lehre.tbl_stundenplan
				          WHERE tbl_stundenplan.mitarbeiter_uid::text = lema.mitarbeiter_uid::text AND tbl_stundenplan.studiengang_kz = lvb.studiengang_kz AND tbl_stundenplan.semester = lvb.semester AND (tbl_stundenplan.verband = lvb.verband OR (tbl_stundenplan.verband IS NULL OR tbl_stundenplan.verband = ''::bpchar) AND lvb.verband IS NULL) AND (tbl_stundenplan.gruppe = lvb.gruppe OR (tbl_stundenplan.gruppe IS NULL OR tbl_stundenplan.gruppe = ''::bpchar) AND lvb.gruppe IS NULL) AND (tbl_stundenplan.gruppe_kurzbz::text = lvb.gruppe_kurzbz::text OR tbl_stundenplan.gruppe_kurzbz IS NULL AND lvb.gruppe_kurzbz IS NULL) AND tbl_stundenplan.lehreinheit_id = lvb.lehreinheit_id) AS verplant
				   FROM lehre.tbl_lehreinheit le
				   JOIN lehre.tbl_lehreinheitgruppe lvb USING (lehreinheit_id)
				   JOIN lehre.tbl_lehreinheitmitarbeiter lema USING (lehreinheit_id)
				   JOIN public.tbl_studiengang USING (studiengang_kz)
				   JOIN lehre.tbl_lehrfach USING (lehrfach_id)
				   JOIN public.tbl_mitarbeiter ma USING (mitarbeiter_uid);
				GRANT SELECT ON lehre.vw_lva_stundenplan TO web;
				GRANT SELECT ON lehre.vw_lva_stundenplan TO admin;
				
				CREATE VIEW campus.vw_lehreinheit AS
				 SELECT tbl_lehrveranstaltung.studiengang_kz AS lv_studiengang_kz, tbl_lehrveranstaltung.semester AS lv_semester, tbl_lehrveranstaltung.kurzbz AS lv_kurzbz, tbl_lehrveranstaltung.bezeichnung AS lv_bezeichnung, tbl_lehrveranstaltung.ects AS lv_ects, tbl_lehrveranstaltung.lehreverzeichnis AS lv_lehreverzeichnis, tbl_lehrveranstaltung.planfaktor AS lv_planfaktor, tbl_lehrveranstaltung.planlektoren AS lv_planlektoren, tbl_lehrveranstaltung.planpersonalkosten AS lv_planpersonalkosten, tbl_lehrveranstaltung.plankostenprolektor AS lv_plankostenprolektor, tbl_lehrveranstaltung.orgform_kurzbz AS lv_orgform_kurzbz, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrveranstaltung_id, tbl_lehreinheit.studiensemester_kurzbz, tbl_lehreinheit.lehrform_kurzbz, tbl_lehreinheit.stundenblockung, tbl_lehreinheit.wochenrythmus, tbl_lehreinheit.start_kw, tbl_lehreinheit.raumtyp, tbl_lehreinheit.raumtypalternativ, tbl_lehreinheit.lehre, tbl_lehreinheit.unr, tbl_lehreinheit.lvnr, tbl_lehreinheitmitarbeiter.lehrfunktion_kurzbz, tbl_lehreinheit.insertamum, tbl_lehreinheit.insertvon, tbl_lehreinheit.updateamum, tbl_lehreinheit.updatevon, tbl_lehreinheit.lehrfach_id, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehrveranstaltung.aktiv, tbl_lehrfach.sprache, tbl_lehreinheitmitarbeiter.mitarbeiter_uid, tbl_lehreinheitmitarbeiter.semesterstunden, tbl_lehrveranstaltung.semesterstunden AS lv_semesterstunden, tbl_lehreinheitmitarbeiter.planstunden, tbl_lehreinheitmitarbeiter.stundensatz, tbl_lehreinheitmitarbeiter.faktor, tbl_lehreinheit.anmerkung, tbl_mitarbeiter.kurzbz AS lektor, tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe, tbl_lehreinheitgruppe.gruppe_kurzbz, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bez, tbl_studiengang.typ AS stg_typ, tbl_lehreinheitmitarbeiter.anmerkung AS anmerkunglektor, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz, tbl_lehrveranstaltung.bezeichnung_english AS lv_bezeichnung_english
				   FROM lehre.tbl_lehreinheit
				   JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
				   JOIN lehre.tbl_lehrfach USING (lehrfach_id)
				   JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)
				   JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid)
				   JOIN lehre.tbl_lehreinheitgruppe USING (lehreinheit_id)
				   JOIN public.tbl_studiengang ON tbl_lehreinheitgruppe.studiengang_kz = tbl_studiengang.studiengang_kz;
				GRANT SELECT ON campus.vw_lehreinheit TO web;
				GRANT SELECT ON campus.vw_lehreinheit TO admin;
				
				CREATE VIEW campus.vw_stundenplan AS
				 SELECT tbl_stundenplan.stundenplan_id, tbl_stundenplan.unr, tbl_stundenplan.mitarbeiter_uid AS uid, tbl_stundenplan.lehreinheit_id, tbl_lehreinheit.lehrfach_id, tbl_stundenplan.datum, tbl_stundenplan.stunde, tbl_stundenplan.ort_kurzbz, tbl_stundenplan.studiengang_kz, tbl_stundenplan.semester, tbl_stundenplan.verband, tbl_stundenplan.gruppe, tbl_stundenplan.gruppe_kurzbz, tbl_stundenplan.titel, tbl_stundenplan.anmerkung, tbl_stundenplan.fix, tbl_lehreinheit.lehrveranstaltung_id, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bezeichnung, tbl_studiengang.typ AS stg_typ, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehreinheit.lehrform_kurzbz AS lehrform, tbl_mitarbeiter.kurzbz AS lektor, tbl_stundenplan.updateamum, tbl_stundenplan.updatevon, tbl_stundenplan.insertamum, tbl_stundenplan.insertvon
				   FROM lehre.tbl_stundenplan
				   JOIN public.tbl_studiengang USING (studiengang_kz)
				   JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
				   JOIN lehre.tbl_lehrfach USING (lehrfach_id)
				   JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid);
				GRANT SELECT ON campus.vw_stundenplan TO web;
				GRANT SELECT ON campus.vw_stundenplan TO admin;
				   
				";
	
		if(!$db->db_query($qry))
			echo '<strong>public.tbl_lehreinheit: '.$db->db_last_error().'</strong><br>';
		else 
			echo 'Anmerkung der Tabellen lehreinheit, stundenplan und stundenplandev auf text geaendert!<br>';
	}
}

// Tabelle fuer Infoscreens
if(!@$db->db_query("SELECT 1 FROM campus.tbl_infoscreen LIMIT 1"))
{
	$qry = "
	CREATE TABLE campus.tbl_infoscreen
	(
		infoscreen_id integer NOT NULL,
		bezeichnung varchar(64),
		beschreibung text,
		ipadresse varchar(50)
	);
	
	CREATE TABLE campus.tbl_infoscreen_content
	(
		infoscreen_content_id integer NOT NULL,
		infoscreen_id integer,
		content_id integer NOT NULL,
		gueltigvon timestamp,
		gueltigbis timestamp,
		refreshzeit smallint,
		insertamum timestamp,
		insertvon varchar(32),
		updateamum timestamp,
		updatevon varchar(32)		
	);
	
	CREATE SEQUENCE campus.seq_infoscreen_infoscreen_id
 	INCREMENT BY 1
 	NO MAXVALUE
	NO MINVALUE
	CACHE 1;
	
	CREATE SEQUENCE campus.seq_infoscreen_content_infoscreen_content_id
 	INCREMENT BY 1
 	NO MAXVALUE
	NO MINVALUE
	CACHE 1;
	
	ALTER TABLE campus.tbl_infoscreen ALTER COLUMN infoscreen_id SET DEFAULT nextval('campus.seq_infoscreen_infoscreen_id');
	ALTER TABLE campus.tbl_infoscreen_content ALTER COLUMN infoscreen_content_id SET DEFAULT nextval('campus.seq_infoscreen_content_infoscreen_content_id');
	
	ALTER TABLE campus.tbl_infoscreen ADD CONSTRAINT pk_infoscreen PRIMARY KEY (infoscreen_id);
	ALTER TABLE campus.tbl_infoscreen_content ADD CONSTRAINT pk_infoscreen_content PRIMARY KEY (infoscreen_content_id);
	
	ALTER TABLE campus.tbl_infoscreen_content ADD CONSTRAINT fk_infoscreen_infoscreen_content FOREIGN KEY(infoscreen_id) REFERENCES campus.tbl_infoscreen(infoscreen_id) ON DELETE CASCADE ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_infoscreen_content ADD CONSTRAINT fk_content_infoscreen_content FOREIGN KEY(content_id) REFERENCES campus.tbl_content(content_id) ON DELETE CASCADE ON UPDATE CASCADE;
	
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_infoscreen TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_infoscreen_content TO admin;
	
	GRANT SELECT ON campus.tbl_infoscreen TO web;
	GRANT SELECT ON campus.tbl_infoscreen_content TO web;
	
	GRANT SELECT, UPDATE ON campus.seq_infoscreen_infoscreen_id TO admin;
	GRANT SELECT, UPDATE ON campus.seq_infoscreen_content_infoscreen_content_id TO admin;
	
	GRANT SELECT ON wawi.tbl_betriebsmittel TO web;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_infoscreen: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle campus.tbl_infoscreen, campus.tbl_infoscreen_content hinzugefuegt!<br>';
}

// tbl_dms_kategorie_gruppe fuer DMS Berechtigungen
if(!$result = @$db->db_query('SELECT 1 FROM campus.tbl_dms_kategorie_gruppe LIMIT 1;'))
{
	$qry = "
	CREATE TABLE campus.tbl_dms_kategorie_gruppe (
		kategorie_kurzbz varchar(32) NOT NULL,
		gruppe_kurzbz varchar(32) NOT NULL,
		insertamum timestamp,
		insertvon varchar(32)
	);
	
	ALTER TABLE campus.tbl_dms_kategorie_gruppe ADD CONSTRAINT fk_dms_kategorie_dms_kategorie_gruppe FOREIGN KEY(kategorie_kurzbz) REFERENCES campus.tbl_dms_kategorie (kategorie_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_dms_kategorie_gruppe ADD CONSTRAINT fk_gruppe_dms_kategorie_gruppe FOREIGN KEY(gruppe_kurzbz) REFERENCES public.tbl_gruppe(gruppe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_dms_kategorie_gruppe ADD CONSTRAINT pk_dms_kategorie_gruppe PRIMARY KEY(kategorie_kurzbz, gruppe_kurzbz);
	
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_dms_kategorie_gruppe TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_dms_kategorie_gruppe TO web;	
	GRANT SELECT ON fue.tbl_projektphase TO web;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_dms_kategorie_gruppe: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle campus.tbl_dms_kategorie_gruppe hinzugefuegt!<br>';
}


// Tabelle fuer FreeBusy
if(!@$db->db_query("SELECT 1 FROM campus.tbl_freebusy LIMIT 1"))
{
	$qry = "
	CREATE TABLE campus.tbl_freebusy
	(
		freebusy_id integer NOT NULL,
		uid varchar(32) NOT NULL,
		freebusytyp_kurzbz varchar(32) NOT NULL,
		url varchar(1024) NOT NULL,
		aktiv boolean NOT NULL,
		bezeichnung varchar(256),
		insertamum timestamp,
		insertvon varchar(32),
		updateamum timestamp,
		updatevon varchar(32)
	);
	
	CREATE TABLE campus.tbl_freebusytyp
	(
		freebusytyp_kurzbz varchar(32) NOT NULL,
		bezeichnung varchar(256),
		beschreibung text
	);
	
	CREATE SEQUENCE campus.seq_freebusy_freebusy_id
 	INCREMENT BY 1
 	NO MAXVALUE
	NO MINVALUE
	CACHE 1;
		
	ALTER TABLE campus.tbl_freebusy ALTER COLUMN freebusy_id SET DEFAULT nextval('campus.seq_freebusy_freebusy_id');
	
	ALTER TABLE campus.tbl_freebusy ADD CONSTRAINT pk_freebusy PRIMARY KEY (freebusy_id);
	ALTER TABLE campus.tbl_freebusytyp ADD CONSTRAINT pk_freebusytyp PRIMARY KEY (freebusytyp_kurzbz);
	
	ALTER TABLE campus.tbl_freebusy ADD CONSTRAINT fk_freebusytyp_freebusy FOREIGN KEY(freebusytyp_kurzbz) REFERENCES campus.tbl_freebusytyp(freebusytyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE campus.tbl_freebusy ADD CONSTRAINT fk_benutzer_freebusy FOREIGN KEY(uid) REFERENCES public.tbl_benutzer(uid) ON DELETE CASCADE ON UPDATE CASCADE;
	
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_freebusy TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_freebusytyp TO admin;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_freebusy TO web;
	GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_freebusytyp TO web;
		
	GRANT SELECT, UPDATE ON campus.seq_freebusy_freebusy_id TO admin;
	GRANT SELECT, UPDATE ON campus.seq_freebusy_freebusy_id TO web;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_freebusy: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle campus.tbl_freebusy, campus.tbl_freebusytyp hinzugefuegt!<br>';
}


//Rechnungsbetrag auf 4 Nachkommastellen vergroessern
if($result = $db->db_query("SELECT numeric_precision, numeric_scale FROM information_schema.columns WHERE column_name='betrag' AND table_name='tbl_rechnungsbetrag' AND table_schema='wawi';"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->numeric_precision==12 && $row->numeric_precision_scale=2)
		{
			$qry = "ALTER TABLE wawi.tbl_rechnungsbetrag ALTER COLUMN betrag TYPE numeric(14,4);";
			if(!$db->db_query($qry))
				echo '<strong>waw.tbl_rechnungsbetrag: '.$db->db_last_error().'</strong><br>';
			else 
				echo 'wawi.tbl_rechnungsbetrag: Spalte betrag auf 4 Nachkommastellen vergroessert<br>';
		}
	}
}

// tbl_webservicerecht
if(!$result = @$db->db_query('SELECT 1 FROM system.tbl_webservicerecht LIMIT 1;'))
{
	$qry = "
	CREATE TABLE system.tbl_webservicerecht (
		webservicerecht_id bigint NOT NULL,
		berechtigung_kurzbz varchar(32) NOT NULL,
		methode varchar(256) NOT NULL,
		attribut varchar(256),
		insertamum timestamp,
		insertvon varchar(32),
		updateamum timestamp,
		updatevon varchar(32)
	);
	
	CREATE SEQUENCE system.seq_webservicerecht_webservicerecht_id
 	INCREMENT BY 1
 	NO MAXVALUE
	NO MINVALUE
	CACHE 1;
	
	ALTER TABLE system.tbl_webservicerecht ADD CONSTRAINT pk_tbl_webservicerecht PRIMARY KEY (webservicerecht_id);
	ALTER TABLE system.tbl_webservicerecht ALTER COLUMN webservicerecht_id SET DEFAULT nextval('system.seq_webservicerecht_webservicerecht_id');
	ALTER TABLE system.tbl_webservicerecht ADD CONSTRAINT fk_berechtigung_webservicerecht FOREIGN KEY(berechtigung_kurzbz) REFERENCES system.tbl_berechtigung (berechtigung_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	
	CREATE INDEX idx_webservicerecht_methode ON system.tbl_webservicerecht (methode);
	
	GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_webservicerecht TO admin;
	GRANT SELECT ON system.tbl_webservicerecht TO web;	
	";
	
	if(!$db->db_query($qry))
		echo '<strong>system.tbl_webservicerecht: '.$db->db_last_error().'</strong><br>';
	else 
		echo 'Tabelle system.tbl_webservicerecht hinzugefuegt!<br>';
}
echo '<br>';

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
	"bis.tbl_mobilitaetsprogramm"  => array("mobilitaetsprogramm_code","kurzbz","beschreibung","sichtbar"),
	"bis.tbl_nation"  => array("nation_code","entwicklungsstand","eu","ewr","kontinent","kurztext","langtext","engltext","sperre"),
	"bis.tbl_orgform"  => array("orgform_kurzbz","code","bezeichnung","rolle"),
	"bis.tbl_verwendung"  => array("verwendung_code","verwendungbez"),
	"bis.tbl_zgv"  => array("zgv_code","zgv_bez","zgv_kurzbz"),
	"bis.tbl_zgvmaster"  => array("zgvmas_code","zgvmas_bez","zgvmas_kurzbz"),
	"bis.tbl_zweck"  => array("zweck_code","kurzbz","bezeichnung"),
	"campus.tbl_abgabe"  => array("abgabe_id","abgabedatei","abgabezeit","anmerkung"),
	"campus.tbl_beispiel"  => array("beispiel_id","uebung_id","nummer","bezeichnung","punkte","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_benutzerlvstudiensemester"  => array("uid","studiensemester_kurzbz","lehrveranstaltung_id"),
	"campus.tbl_content"  => array("content_id","template_kurzbz","updatevon","updateamum","insertamum","insertvon","oe_kurzbz","menu_open","aktiv","beschreibung"),
	"campus.tbl_contentchild"  => array("contentchild_id","content_id","child_content_id","updatevon","updateamum","insertamum","insertvon","sort"),
	"campus.tbl_contentgruppe"  => array("content_id","gruppe_kurzbz","insertamum","insertvon"),
	"campus.tbl_contentlog"  => array("contentlog_id","contentsprache_id","uid","start","ende"),
	"campus.tbl_contentsprache"  => array("contentsprache_id","content_id","sprache","version","sichtbar","content","reviewvon","reviewamum","updateamum","updatevon","insertamum","insertvon","titel","gesperrt_uid"),
	"campus.tbl_dms"  => array("dms_id","oe_kurzbz","dokument_kurzbz","kategorie_kurzbz"),
	"campus.tbl_dms_kategorie"  => array("kategorie_kurzbz","bezeichnung","beschreibung","parent_kategorie_kurzbz"),
	"campus.tbl_dms_kategorie_gruppe" => array("kategorie_kurzbz","gruppe_kurzbz","insertamum","insertvon"),
	"campus.tbl_dms_version"  => array("dms_id","version","filename","mimetype","name","beschreibung","letzterzugriff","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_erreichbarkeit"  => array("erreichbarkeit_kurzbz","beschreibung","farbe"),
	"campus.tbl_feedback"  => array("feedback_id","betreff","text","datum","uid","lehrveranstaltung_id","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_freebusy"  => array("freebusy_id","uid","freebusytyp_kurzbz","url","aktiv","bezeichnung","insertamum","insertvon","updateamum","updatevon"),
	"campus.tbl_freebusytyp" => array("freebusytyp_kurzbz","bezeichnung","beschreibung"),
	"campus.tbl_infoscreen"  => array("infoscreen_id","bezeichnung","beschreibung","ipadresse"),
	"campus.tbl_infoscreen_content"  => array("infoscreen_content_id","infoscreen_id","content_id","gueltigvon","gueltigbis","insertamum","insertvon","updateamum","updatevon","refreshzeit"),
	"campus.tbl_legesamtnote"  => array("student_uid","lehreinheit_id","note","benotungsdatum","updateamum","updatevon","insertamum","insertvon"),
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
	"campus.tbl_zeitaufzeichnung"  => array("zeitaufzeichnung_id","uid","aktivitaet_kurzbz","projekt_kurzbz","start","ende","beschreibung","studiengang_kz","fachbereich_kurzbz","insertamum","insertvon","updateamum","updatevon"),
	"campus.tbl_zeitsperre"  => array("zeitsperre_id","zeitsperretyp_kurzbz","mitarbeiter_uid","bezeichnung","vondatum","vonstunde","bisdatum","bisstunde","vertretung_uid","updateamum","updatevon","insertamum","insertvon","erreichbarkeit_kurzbz","freigabeamum","freigabevon"),
	"campus.tbl_zeitsperretyp"  => array("zeitsperretyp_kurzbz","beschreibung","farbe"),
	"campus.tbl_zeitwunsch"  => array("stunde","mitarbeiter_uid","tag","gewicht","updateamum","updatevon","insertamum","insertvon"),
	"fue.tbl_aktivitaet"  => array("aktivitaet_kurzbz","beschreibung"),
	"fue.tbl_projekt"  => array("projekt_kurzbz","nummer","titel","beschreibung","beginn","ende","oe_kurzbz","budget"),
	"fue.tbl_projektphase"  => array("projektphase_id","projekt_kurzbz","projektphase_fk","bezeichnung","beschreibung","start","ende","budget","insertamum","insertvon","updateamum","updatevon","personentage"),
	"fue.tbl_projekttask"  => array("projekttask_id","projektphase_id","bezeichnung","beschreibung","aufwand","mantis_id","insertamum","insertvon","updateamum","updatevon","projekttask_fk","erledigt","ende","ressource_id"),
	"fue.tbl_projekt_dokument"  => array("projekt_dokument_id","projektphase_id","projekt_kurzbz","dms_id"),
	"fue.tbl_projekt_ressource"  => array("projekt_ressource_id","projekt_kurzbz","projektphase_id","ressource_id","funktion_kurzbz","beschreibung"),
	"fue.tbl_ressource"  => array("ressource_id","student_uid","mitarbeiter_uid","betriebsmittel_id","firma_id","bezeichnung","beschreibung","insertamum","insertvon","updateamum","updatevon"),
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
	"lehre.tbl_lehrveranstaltung"  => array("lehrveranstaltung_id","kurzbz","bezeichnung","lehrform_kurzbz","studiengang_kz","semester","sprache","ects","semesterstunden","anmerkung","lehre","lehreverzeichnis","aktiv","planfaktor","planlektoren","planpersonalkosten","plankostenprolektor","koordinator","sort","zeugnis","projektarbeit","updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung_english","orgform_kurzbz","incoming"),
	"lehre.tbl_moodle"  => array("lehrveranstaltung_id","lehreinheit_id","moodle_id","mdl_course_id","studiensemester_kurzbz","gruppen","insertamum","insertvon"),
	"lehre.tbl_note"  => array("note","bezeichnung","anmerkung","farbe"),
	"lehre.tbl_projektarbeit"  => array("projektarbeit_id","projekttyp_kurzbz","titel","lehreinheit_id","student_uid","firma_id","note","punkte","beginn","ende","faktor","freigegeben","gesperrtbis","stundensatz","gesamtstunden","themenbereich","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","titel_english","seitenanzahl","abgabedatum","kontrollschlagwoerter","schlagwoerter","schlagwoerter_en","abstract", "abstract_en", "sprache"),
	"lehre.tbl_projektbetreuer"  => array("person_id","projektarbeit_id","betreuerart_kurzbz","note","faktor","name","punkte","stunden","stundensatz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_projekttyp"  => array("projekttyp_kurzbz","bezeichnung"),
	"lehre.tbl_pruefung"  => array("pruefung_id","lehreinheit_id","student_uid","mitarbeiter_uid","note","pruefungstyp_kurzbz","datum","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"lehre.tbl_pruefungstyp"  => array("pruefungstyp_kurzbz","beschreibung","abschluss"),
	"lehre.tbl_stunde"  => array("stunde","beginn","ende"),
	"lehre.tbl_stundenplan"  => array("stundenplan_id","unr","mitarbeiter_uid","datum","stunde","ort_kurzbz","gruppe_kurzbz","titel","anmerkung","lehreinheit_id","studiengang_kz","semester","verband","gruppe","fix","updateamum","updatevon","insertamum","insertvon"),
	"lehre.tbl_stundenplandev"  => array("stundenplandev_id","lehreinheit_id","unr","studiengang_kz","semester","verband","gruppe","gruppe_kurzbz","mitarbeiter_uid","ort_kurzbz","datum","stunde","titel","anmerkung","fix","updateamum","updatevon","insertamum","insertvon"),
	"lehre.tbl_zeitfenster"  => array("wochentag","stunde","ort_kurzbz","studiengang_kz","gewicht"),
	"lehre.tbl_zeugnis"  => array("zeugnis_id","student_uid","zeugnis","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_zeugnisnote"  => array("lehrveranstaltung_id","student_uid","studiensemester_kurzbz","note","uebernahmedatum","benotungsdatum","bemerkung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_adresse"  => array("adresse_id","person_id","name","strasse","plz","ort","gemeinde","nation","typ","heimatadresse","zustelladresse","firma_id","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_akte"  => array("akte_id","person_id","dokument_kurzbz","uid","inhalt","mimetype","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_ampel"  => array("ampel_id","kurzbz","beschreibung","benutzer_select","deadline","vorlaufzeit","verfallszeit","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_ampel_benutzer_bestaetigt"  => array("ampel_benutzer_bestaetigt_id","ampel_id","uid","insertamum","insertvon"),
	"public.tbl_aufmerksamdurch"  => array("aufmerksamdurch_kurzbz","beschreibung","ext_id"),
	"public.tbl_aufnahmeschluessel"  => array("aufnahmeschluessel"),
	"public.tbl_bankverbindung"  => array("bankverbindung_id","person_id","name","anschrift","bic","blz","iban","kontonr","typ","verrechnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_benutzer"  => array("uid","person_id","aktiv","alias","insertamum","insertvon","updateamum","updatevon","ext_id","updateaktivvon","updateaktivam"),
	"public.tbl_benutzerfunktion"  => array("benutzerfunktion_id","fachbereich_kurzbz","uid","oe_kurzbz","funktion_kurzbz","semester", "datum_von","datum_bis", "updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung"),
	"public.tbl_benutzergruppe"  => array("uid","gruppe_kurzbz","studiensemester_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_berechtigung"  => array("berechtigung_kurzbz","beschreibung"),
	"public.tbl_buchungstyp"  => array("buchungstyp_kurzbz","beschreibung","standardbetrag","standardtext","aktiv"),
	"public.tbl_dokument"  => array("dokument_kurzbz","bezeichnung","ext_id"),
	"public.tbl_dokumentprestudent"  => array("dokument_kurzbz","prestudent_id","mitarbeiter_uid","datum","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_dokumentstudiengang"  => array("dokument_kurzbz","studiengang_kz","ext_id"),
	"public.tbl_erhalter"  => array("erhalter_kz","kurzbz","bezeichnung","dvr","logo","zvr"),
	"public.tbl_fachbereich"  => array("fachbereich_kurzbz","bezeichnung","farbe","studiengang_kz","aktiv","ext_id","oe_kurzbz"),
	"public.tbl_firma"  => array("firma_id","name","anmerkung","firmentyp_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id","schule","finanzamt","steuernummer","gesperrt","aktiv"),
	"public.tbl_firma_organisationseinheit"  => array("firma_organisationseinheit_id","firma_id","oe_kurzbz","bezeichnung","kundennummer","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_firmentyp"  => array("firmentyp_kurzbz","beschreibung"),
	"public.tbl_firmatag"  => array("firma_id","tag","insertamum","insertvon"),
	"public.tbl_funktion"  => array("funktion_kurzbz","beschreibung","aktiv","fachbereich","semester"),
	"public.tbl_geschaeftsjahr"  => array("geschaeftsjahr_kurzbz","start","ende","bezeichnung"),
	"public.tbl_gruppe"  => array("gruppe_kurzbz","studiengang_kz","semester","bezeichnung","beschreibung","sichtbar","lehre","aktiv","sort","mailgrp","generiert","updateamum","updatevon","insertamum","insertvon","ext_id","orgform_kurzbz","gid","content_visible"),
	"public.tbl_kontakt"  => array("kontakt_id","person_id","kontakttyp","anmerkung","kontakt","zustellung","updateamum","updatevon","insertamum","insertvon","ext_id","standort_id"),
	"public.tbl_kontaktmedium"  => array("kontaktmedium_kurzbz","beschreibung"),
	"public.tbl_kontakttyp"  => array("kontakttyp","beschreibung"),
	"public.tbl_konto"  => array("buchungsnr","person_id","studiengang_kz","studiensemester_kurzbz","buchungstyp_kurzbz","buchungsnr_verweis","betrag","buchungsdatum","buchungstext","mahnspanne","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_lehrverband"  => array("studiengang_kz","semester","verband","gruppe","aktiv","bezeichnung","ext_id","orgform_kurzbz","gid"),
	"public.tbl_log"  => array("log_id","executetime","mitarbeiter_uid","beschreibung","sql","sqlundo"),
	"public.tbl_mitarbeiter"  => array("mitarbeiter_uid","personalnummer","telefonklappe","kurzbz","lektor","fixangestellt","bismelden","stundensatz","ausbildungcode","ort_kurzbz","standort_id","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_notiz"  => array("notiz_id","titel","text","verfasser_uid","bearbeiter_uid","start","ende","erledigt","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_notizzuordnung"  => array("notizzuordnung_id","notiz_id","projekt_kurzbz","projektphase_id","projekttask_id","uid","person_id","prestudent_id","bestellung_id"),
	"public.tbl_ort"  => array("ort_kurzbz","bezeichnung","planbezeichnung","max_person","lehre","reservieren","aktiv","lageplan","dislozierung","kosten","ausstattung","updateamum","updatevon","insertamum","insertvon","ext_id","stockwerk","standort_id","telefonklappe"),
	"public.tbl_ortraumtyp"  => array("ort_kurzbz","hierarchie","raumtyp_kurzbz"),
	"public.tbl_organisationseinheit" => array("oe_kurzbz", "oe_parent_kurzbz", "bezeichnung","organisationseinheittyp_kurzbz", "aktiv","mailverteiler","freigabegrenze","kurzzeichen"),
	"public.tbl_organisationseinheittyp" => array("organisationseinheittyp_kurzbz", "bezeichnung", "beschreibung"),
	"public.tbl_person"  => array("person_id","staatsbuergerschaft","geburtsnation","sprache","anrede","titelpost","titelpre","nachname","vorname","vornamen","gebdatum","gebort","gebzeit","foto","anmerkung","homepage","svnr","ersatzkennzeichen","familienstand","geschlecht","anzahlkinder","aktiv","insertamum","insertvon","updateamum","updatevon","ext_id","bundesland_code","kompetenzen","kurzbeschreibung","zugangscode"),
	"public.tbl_personfunktionstandort"  => array("personfunktionstandort_id","funktion_kurzbz","person_id","standort_id","position","anrede"),
	"public.tbl_preincoming"  => array("preincoming_id","person_id","mobilitaetsprogramm_code","zweck_code","firma_id","universitaet","aktiv","bachelorthesis","masterthesis","von","bis","uebernommen","insertamum","insertvon","updateamum","updatevon","anmerkung","zgv","zgv_ort","zgv_datum","zgv_name","zgvmaster","zgvmaster_datum","zgvmaster_ort","zgvmaster_name","program_name","bachelor","master","jahre","person_id_emergency","person_id_coordinator_dep","person_id_coordinator_int","code","deutschkurs1","deutschkurs2","research_area","deutschkurs3"),
	"public.tbl_preincoming_lehrveranstaltung"  => array("preincoming_id","lehrveranstaltung_id","insertamum","insertvon"),
	"public.tbl_preinteressent"  => array("preinteressent_id","person_id","studiensemester_kurzbz","firma_id","erfassungsdatum","einverstaendnis","absagedatum","anmerkung","maturajahr","infozusendung","aufmerksamdurch_kurzbz","kontaktmedium_kurzbz","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_preinteressentstudiengang"  => array("studiengang_kz","preinteressent_id","freigabedatum","uebernahmedatum","prioritaet","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_prestudent"  => array("prestudent_id","aufmerksamdurch_kurzbz","person_id","studiengang_kz","berufstaetigkeit_code","ausbildungcode","zgv_code","zgvort","zgvdatum","zgvmas_code","zgvmaort","zgvmadatum","aufnahmeschluessel","facheinschlberuf","reihungstest_id","anmeldungreihungstest","reihungstestangetreten","rt_gesamtpunkte","rt_punkte1","rt_punkte2","bismelden","anmerkung","dual","insertamum","insertvon","updateamum","updatevon","ext_id","ausstellungsstaat"),
	"public.tbl_prestudentstatus"  => array("prestudent_id","status_kurzbz","studiensemester_kurzbz","ausbildungssemester","datum","orgform_kurzbz","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_raumtyp"  => array("raumtyp_kurzbz","beschreibung"),
	"public.tbl_reihungstest"  => array("reihungstest_id","studiengang_kz","ort_kurzbz","anmerkung","datum","uhrzeit","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_status"  => array("status_kurzbz","beschreibung","anmerkung","ext_id"),
	"public.tbl_semesterwochen"  => array("semester","studiengang_kz","wochen"),
	"public.tbl_sprache"  => array("sprache","locale","flagge","index","content","bezeichnung"),
	"public.tbl_standort"  => array("standort_id","adresse_id","kurzbz","bezeichnung","insertvon","insertamum","updatevon","updateamum","ext_id", "firma_id"),
	"public.tbl_statistik"  => array("statistik_kurzbz","bezeichnung","url","r","gruppe","sql","php","content_id","insertamum","insertvon","updateamum","updatevon","berechtigung_kurzbz"),
	"public.tbl_student"  => array("student_uid","matrikelnr","prestudent_id","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studentlehrverband"  => array("student_uid","studiensemester_kurzbz","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studiengang"  => array("studiengang_kz","kurzbz","kurzbzlang","typ","bezeichnung","english","farbe","email","telefon","max_semester","max_verband","max_gruppe","erhalter_kz","bescheid","bescheidbgbl1","bescheidbgbl2","bescheidgz","bescheidvom","orgform_kurzbz","titelbescheidvom","aktiv","ext_id","zusatzinfo_html","moodle","sprache","testtool_sprachwahl","studienplaetze","oe_kurzbz","lgartcode","mischform","projektarbeit_note_anzeige"),
	"public.tbl_studiensemester"  => array("studiensemester_kurzbz","bezeichnung","start","ende","ext_id"),
	"public.tbl_tag"  => array("tag"),
	"public.tbl_variable"  => array("name","uid","wert"),
	"public.tbl_vorlage"  => array("vorlage_kurzbz","bezeichnung","anmerkung","mimetype"),
	"public.tbl_vorlagestudiengang"  => array("vorlagestudiengang_id","vorlage_kurzbz","studiengang_kz","version","text","oe_kurzbz"),
	"sync.tbl_zutrittskarte"  => array("key","name","firstname","groupe","logaswnumber","physaswnumber","validstart","validend","text1","text2","text3","text4","text5","text6","pin"),
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
	"system.tbl_cronjob"  => array("cronjob_id","server_kurzbz","titel","beschreibung","file","last_execute","aktiv","running","jahr","monat","tag","wochentag","stunde","minute","standalone","reihenfolge","updateamum", "updatevon","insertamum","insertvon","variablen"),
	"system.tbl_benutzerrolle"  => array("benutzerberechtigung_id","rolle_kurzbz","berechtigung_kurzbz","uid","funktion_kurzbz","oe_kurzbz","art","studiensemester_kurzbz","start","ende","negativ","updateamum", "updatevon","insertamum","insertvon","kostenstelle_id"),
	"system.tbl_berechtigung"  => array("berechtigung_kurzbz","beschreibung"),
	"system.tbl_rolle"  => array("rolle_kurzbz","beschreibung"),
	"system.tbl_rolleberechtigung"  => array("berechtigung_kurzbz","rolle_kurzbz","art"),
	"system.tbl_webservicelog"  => array("webservicelog_id","webservicetyp_kurzbz","request_id","beschreibung","request_data","execute_time","execute_user"),
	"system.tbl_webservicerecht" => array("webservicerecht_id","berechtigung_kurzbz","methode","attribut","insertamum","insertvon","updateamum","updatevon"),
	"system.tbl_webservicetyp"  => array("webservicetyp_kurzbz","beschreibung"),
	"system.tbl_server"  => array("server_kurzbz","beschreibung"),
	"wawi.tbl_betriebsmittelperson"  => array("betriebsmittelperson_id","betriebsmittel_id","person_id", "anmerkung", "kaution", "ausgegebenam", "retouram","insertamum", "insertvon","updateamum", "updatevon","ext_id"),
	"wawi.tbl_betriebsmittel"  => array("betriebsmittel_id","betriebsmitteltyp","oe_kurzbz", "ort_kurzbz", "beschreibung", "nummer", "hersteller","seriennummer", "bestellung_id","bestelldetail_id", "afa","verwendung","anmerkung","reservieren","updateamum","updatevon","insertamum","insertvon","ext_id","inventarnummer","leasing_bis","inventuramum","inventurvon","anschaffungsdatum","anschaffungswert","hoehe","breite","tiefe"),
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
?>