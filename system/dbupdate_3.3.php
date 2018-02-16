<?php
/* Copyright (C) 2017 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Cristina Hainberger <hainberg@technikum-wien.at>
 *
 * Beschreibung:
 * Dieses Skript prueft die Datenbank auf aktualitaet, dabei werden fehlende Attribute angelegt.
 */

//Spalte statusgrund_id in tbl_prestudentstauts
if(!$result = @$db->db_query("SELECT statusgrund_id FROM public.tbl_prestudentstatus LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_prestudentstatus ADD COLUMN statusgrund_id integer;
		ALTER TABLE public.tbl_prestudentstatus ADD CONSTRAINT fk_prestudentstatus_statusgrund FOREIGN KEY (statusgrund_id) REFERENCES public.tbl_status_grund (statusgrund_id) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudentstatus: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_prestudentstatus: Spalte statusgrund_id hinzugefuegt';
}

// Berechtigungen fuer web User erteilen um Gebiete anlegen zu duerfen
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_gebiet' AND table_schema='testtool' AND grantee='web' AND privilege_type='INSERT'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "GRANT SELECT, INSERT, UPDATE, DELETE ON testtool.tbl_gebiet TO web;
			GRANT SELECT, UPDATE ON testtool.tbl_gebiet_gebiet_id_seq TO web;
			";

		if(!$db->db_query($qry))
			echo '<strong>Testtool Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Web User fuer testtool.tbl_gebiet berechtigt';
	}
}

if(!$result = @$db->db_query("SELECT 1 FROM public.vw_msg_vars LIMIT 1"))
{
	// CREATE OR REPLACE VIEW public.vw_msg_vars and grants privileges
	$qry = '
		CREATE OR REPLACE VIEW public.vw_msg_vars AS (
			SELECT DISTINCT ON(p.person_id, pr.prestudent_id) p.person_id,
				   pr.prestudent_id AS prestudent_id,
				   p.nachname AS "Nachname",
				   p.vorname AS "Vorname",
				   p.anrede AS "Anrede",
				   a.strasse AS "Strasse",
				   a.ort AS "Ort",
				   a.plz AS "PLZ",
				   a.gemeinde AS "Gemeinde",
				   a.langtext AS "Nation",
				   ke.kontakt AS "Email",
				   kt.kontakt AS "Telefon",
				   s.bezeichnung AS "Studiengang DE",
				   s.english AS "Studiengang EN",
				   st.bezeichnung AS "Typ",
				   orgform_kurzbz AS "Orgform"
			  FROM public.tbl_person p
		 LEFT JOIN (
						SELECT person_id,
							   kontakt
						  FROM public.tbl_kontakt
						 WHERE zustellung = TRUE
						   AND kontakttyp = \'email\'
					  ORDER BY kontakt_id DESC
				) ke USING(person_id)
		 LEFT JOIN (
						SELECT person_id,
							   kontakt
						  FROM public.tbl_kontakt
						 WHERE zustellung = TRUE
						   AND kontakttyp IN (\'telefon\', \'mobil\')
					  ORDER BY kontakt_id DESC
				) kt USING(person_id)
		 LEFT JOIN (
						SELECT person_id,
							   strasse,
							   ort,
							   plz,
							   gemeinde,
							   langtext
						  FROM public.tbl_adresse
					 LEFT JOIN bis.tbl_nation ON(bis.tbl_nation.nation_code = public.tbl_adresse.nation)
						 WHERE public.tbl_adresse.heimatadresse = TRUE
					  ORDER BY adresse_id DESC
				) a USING(person_id)
		 LEFT JOIN public.tbl_prestudent pr USING(person_id)
		INNER JOIN public.tbl_studiengang s USING(studiengang_kz)
		INNER JOIN public.tbl_studiengangstyp st USING(typ)
			 WHERE p.aktiv = TRUE
		  ORDER BY p.person_id ASC, pr.prestudent_id ASC
		);';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.vw_msg_vars view created';

	$qry = 'GRANT SELECT ON TABLE public.vw_msg_vars TO web;';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on public.vw_msg_vars';

	$qry = 'GRANT SELECT ON TABLE public.vw_msg_vars TO vilesci;';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on public.vw_msg_vars';
}

if(!$result = @$db->db_query("SELECT 1 FROM public.vw_msg_vars_person LIMIT 1"))
{
	// CREATE OR REPLACE VIEW public.vw_msg_vars and grants privileges
	$qry = '
		CREATE OR REPLACE VIEW public.vw_msg_vars_person AS (
		SELECT DISTINCT ON(p.person_id) p.person_id,
						   p.nachname AS "Nachname",
						   p.vorname AS "Vorname",
						   p.anrede AS "Anrede",
						   a.strasse AS "Strasse",
						   a.ort AS "Ort",
						   a.plz AS "PLZ",
						   a.gemeinde AS "Gemeinde",
						   a.langtext AS "Nation",
						   ke.kontakt AS "Email",
						   kt.kontakt AS "Telefon"
					  FROM public.tbl_person p
				 LEFT JOIN (
								SELECT person_id,
									   kontakt
								  FROM public.tbl_kontakt
								 WHERE zustellung = TRUE
								   AND kontakttyp = \'email\'
							  ORDER BY kontakt_id DESC
						) ke USING(person_id)
				 LEFT JOIN (
								SELECT person_id,
									   kontakt
								  FROM public.tbl_kontakt
								 WHERE zustellung = TRUE
								   AND kontakttyp IN (\'telefon\', \'mobil\')
							  ORDER BY kontakt_id DESC
						) kt USING(person_id)
				 LEFT JOIN (
								SELECT person_id,
									   strasse,
									   ort,
									   plz,
									   gemeinde,
									   langtext
								  FROM public.tbl_adresse
							 LEFT JOIN bis.tbl_nation ON(bis.tbl_nation.nation_code = public.tbl_adresse.nation)
								 WHERE public.tbl_adresse.heimatadresse = TRUE
							  ORDER BY adresse_id DESC
						) a USING(person_id)
				  ORDER BY p.person_id ASC
		);';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars_person: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.vw_msg_vars_person view created';

	$qry = 'GRANT SELECT ON TABLE public.vw_msg_vars_person TO web;';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars_person: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on public.vw_msg_vars_person';

	$qry = 'GRANT SELECT ON TABLE public.vw_msg_vars_person TO vilesci;';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars_person: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on public.vw_msg_vars_person';
}

//Spalte anmerkung und rechnungsadresse in tbl_adresse
if(!$result = @$db->db_query("SELECT rechnungsadresse FROM public.tbl_adresse LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_adresse ADD COLUMN rechnungsadresse boolean NOT NULL DEFAULT false;
		ALTER TABLE public.tbl_adresse ADD COLUMN anmerkung text;
		COMMENT ON COLUMN public.tbl_adresse.typ IS 'h=hauptwohnsitz, n=nebenwohnsitz, f=firma, r=Rechnungsadresse';";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_adresse: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_adresse: Spalte rechnungsadresse und anmerkung hinzugefuegt';
}

//Spalte final tbl_projektarbeit zum Markieren der letztgueltigen Projektarbeit
if(!$result = @$db->db_query("SELECT final FROM lehre.tbl_projektarbeit LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_projektarbeit ADD COLUMN final boolean NOT NULL DEFAULT true;
		COMMENT ON COLUMN lehre.tbl_projektarbeit.final IS 'Markiert letztgültige Version der Projektarbeit';";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_projektarbeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_projektarbeit: Spalte final hinzugefuegt';
}

//Spalte insertamum tbl_pruefungsanmeldung zur Ausgabe des Anmeldedatums auf Anmeldelisten
if(!$result = @$db->db_query("SELECT insertamum FROM campus.tbl_pruefungsanmeldung LIMIT 1"))
{
	$qry = "ALTER TABLE campus.tbl_pruefungsanmeldung ADD COLUMN insertamum timestamp DEFAULT now();";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_pruefungsanmeldung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_pruefungsanmeldung: Spalte insertamum hinzugefuegt';
}

// Berechtigungs View liefert nur noch aktive Benutzer
if($result = $db->db_query("SELECT view_definition FROM information_schema.views WHERE table_schema='system' AND table_name='vw_berechtigung_nichtrekursiv'"))
{
	if($row = $db->db_fetch_object($result))
	{
		if(!mb_stristr($row->view_definition, 'tbl_benutzer.aktiv = true'))
		{
			$qry = "
			CREATE OR REPLACE VIEW system.vw_berechtigung_nichtrekursiv AS
			SELECT
				uid, berechtigung_kurzbz,
				-- art zusammenfassung und nur die nehmen die gleich sind
				CASE WHEN length(art)>length(art1) THEN art1 ELSE art END as art,
				oe_kurzbz, kostenstelle_id
			FROM
				(
				-- Normal
				SELECT
					benutzerberechtigung_id, tbl_benutzerrolle.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle JOIN system.tbl_berechtigung USING(berechtigung_kurzbz)

				-- Rollen
				UNION
				SELECT
					benutzerberechtigung_id, tbl_benutzerrolle.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_berechtigung.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_rolleberechtigung.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle JOIN system.tbl_rolle USING(rolle_kurzbz)
					JOIN system.tbl_rolleberechtigung USING(rolle_kurzbz)
					JOIN system.tbl_berechtigung ON(tbl_rolleberechtigung.berechtigung_kurzbz=tbl_berechtigung.berechtigung_kurzbz)

				-- Funktionen
				UNION
				SELECT
					benutzerberechtigung_id, tbl_benutzerfunktion.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
					tbl_benutzerfunktion.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle JOIN public.tbl_benutzerfunktion USING(funktion_kurzbz)
				WHERE
					(tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now())
					AND (tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())

				-- Funktion Mitarbeiter
				UNION
				SELECT
					benutzerberechtigung_id, vw_mitarbeiter.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle, campus.vw_mitarbeiter
				WHERE
					tbl_benutzerrolle.funktion_kurzbz='Mitarbeiter' and vw_mitarbeiter.aktiv


				-- Funktion Student
				UNION
				SELECT
					benutzerberechtigung_id, vw_student.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle, campus.vw_student
				WHERE
					tbl_benutzerrolle.funktion_kurzbz='Student' and vw_student.aktiv
			) as a
			JOIN public.tbl_benutzer USING(uid)
			WHERE
				-- Datumsgrenzen beruecksichtigen
				tbl_benutzer.aktiv = true
				AND (a.start<=now() OR a.start is null)
				AND (a.ende>=now() OR a.ende is null)

				-- Neagtiv Rechte entfernen
				AND not negativ
				AND NOT EXISTS(SELECT
					1
				FROM
					system.tbl_benutzerrolle JOIN system.tbl_berechtigung USING(berechtigung_kurzbz) WHERE uid=a.uid AND berechtigung_kurzbz=a.berechtigung_kurzbz AND negativ);

			GRANT SELECT ON system.vw_berechtigung_nichtrekursiv TO web;
			GRANT SELECT ON system.vw_berechtigung_nichtrekursiv TO vilesci;
			";

			if(!$db->db_query($qry))
				echo '<strong>system.vw_berechtigung_nichtrekursiv:'.$db->db_last_error().'</strong><br>';
			else
				echo '<br>system.vw_berechtigung_nichtrekursiv angepasst damit nur aktive Benutzer beruecksichtigt werden';
		}
	}
}

// Creates table system.tbl_udf if it doesn't exist and grants privileges
if(!$result = @$db->db_query("SELECT 1 FROM system.tbl_udf LIMIT 1"))
{
	$qry = '
		CREATE TABLE system.tbl_udf (
			"schema"	VARCHAR(32) NOT NULL,
			"table"		VARCHAR(128) NOT NULL,
			"jsons"		JSONB NOT NULL,
			CONSTRAINT tbl_udf_pkey PRIMARY KEY("schema", "table")
		);';
	if(!$db->db_query($qry))
		echo '<strong>system.tbl_udf: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_udf table created';

	$qry = 'COMMENT ON COLUMN system.tbl_udf.schema IS \'Schema of the table\';';
	if(!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_udf.schema: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_udf.schema';

	$qry = 'COMMENT ON COLUMN system.tbl_udf.table IS \'Table name\';';
	if(!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_udf.table: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_udf.table';

	$qry = 'COMMENT ON COLUMN system.tbl_udf.jsons IS \'JSON schema\';';
	if(!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_udf.jsons: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_udf.jsons';

	$qry = 'GRANT SELECT ON TABLE system.tbl_udf TO web;';
	if(!$db->db_query($qry))
		echo '<strong>system.tbl_udf: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_udf';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_udf TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>system.tbl_udf: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_udf';
}

// Add column udf_values to public.tbl_person
if(!$result = @$db->db_query("SELECT udf_values FROM public.tbl_person LIMIT 1"))
{
	$qry = 'ALTER TABLE public.tbl_person ADD COLUMN udf_values JSONB;';
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_person: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added column udf_values to table public.tbl_person';
}

// Add column udf_values to public.tbl_prestudent
if(!$result = @$db->db_query("SELECT udf_values FROM public.tbl_prestudent LIMIT 1"))
{
	$qry = 'ALTER TABLE public.tbl_prestudent ADD COLUMN udf_values JSONB;';
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudent: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added column udf_values to table public.tbl_prestudent';
}

// Add permission for UDF
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'system/udf';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('system/udf', 'UDF');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for UDF<br>';
	}
}

// Spalten mailversand,teilnehmer_anonym,termine_anonym in campus.tbl_coodle
if(!$result = @$db->db_query("SELECT mailversand FROM campus.tbl_coodle LIMIT 1;"))
{
	$qry = "ALTER TABLE campus.tbl_coodle ADD COLUMN mailversand boolean;
			ALTER TABLE campus.tbl_coodle ADD COLUMN teilnehmer_anonym boolean;
			ALTER TABLE campus.tbl_coodle ADD COLUMN termine_anonym boolean;";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_coodle: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_coodle: Spalten mailversand, teilnehmer_anonym und termine_anonym hinzugefuegt!<br>';
}

// Spalte onlinebewerbung_studienplan in lehre.tbl_studienplan
if(!$result = @$db->db_query("SELECT onlinebewerbung_studienplan FROM lehre.tbl_studienplan LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_studienplan ADD COLUMN onlinebewerbung_studienplan boolean NOT NULL DEFAULT true;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_studienplan: Spalte onlinebewerbung_studienplan hinzugefuegt!<br>';
}

// Spalte sort in lehre.tbl_pruefungstyp (gibt Reihenfolge der Prüfungsantritte an)
if(!$result = @$db->db_query("SELECT sort FROM lehre.tbl_pruefungstyp LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_pruefungstyp ADD COLUMN sort smallint;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_pruefungstyp: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_pruefungstyp: Spalte sort hinzugefuegt!<br>';
}

// zusätzliche kommissionelle Prüfung (4.Termin) als Zeile hinzufügen
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_pruefungstyp WHERE pruefungstyp_kurzbz= 'zusKommPruef';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO lehre.tbl_pruefungstyp(pruefungstyp_kurzbz, beschreibung, abschluss) VALUES ('zusKommPruef', 'zusätzliche kommissionelle Prüfung', FALSE);";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_pruefungstyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_pruefungstyp: Zeile zusKommPruef hinzugefuegt!<br>';
	}
}

// Note "entschuldigt" hinzufügen
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_note WHERE anmerkung = 'en' AND (bezeichnung = 'entschuldigt' OR bezeichnung = 'Entschuldigt');"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung, farbe, positiv, notenwert, aktiv, lehre) VALUES((SELECT max(note)+1 FROM lehre.tbl_note),'entschuldigt', 'en', NULL, TRUE, NULL, TRUE, TRUE);";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_note: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_note: Zeile entschuldigt hinzugefuegt!<br>';
	}
}

// Note "unentschuldigt" hinzufügen
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_note WHERE anmerkung = 'ue' AND (bezeichnung = 'unentschuldigt' OR bezeichnung = 'Unentschuldigt');"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung, farbe, positiv, notenwert, aktiv, lehre) VALUES((SELECT max(note)+1 FROM lehre.tbl_note),'unentschuldigt', 'ue', NULL, FALSE, NULL, TRUE, TRUE);";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_note: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_note: Zeile unentschuldigt hinzugefuegt!<br>';
	}
}

// Spalte offiziell in lehre.tbl_note
if(!$result = @$db->db_query("SELECT offiziell FROM lehre.tbl_note LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_note ADD COLUMN offiziell boolean NOT NULL DEFAULT true;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_note: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_note: Spalte offiziell hinzugefuegt!<br>';
}

// Spalte bezeichnung_mehrsprachig in lehre.tbl_note
if(!$result = @$db->db_query("SELECT bezeichnung_mehrsprachig FROM lehre.tbl_note LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_note ADD COLUMN bezeichnung_mehrsprachig varchar(64)[];";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_note '.$db->db_last_error().'</strong><br>';
	else
		echo 'lehre.tbl_note: Spalte bezeichnung_mehrsprachig hinzugefuegt!<br>';

	// Bezeichnung_mehrsprachig aus existierender Bezeichnung vorausfuellen. Ein Eintrag fuer jede Sprache mit Content aktiv.
	$qry_help = "SELECT index FROM public.tbl_sprache WHERE content=TRUE;";
	if(!$result = $db->db_query($qry_help))
		echo '<strong>tbl_note bezeichnung_mehrsprachig: Fehler beim ermitteln der Sprachen: '.$db->db_last_error().'</strong>';
	else
	{
		$qry='';
		while($row = $db->db_fetch_object($result))
			$qry.= "UPDATE lehre.tbl_note set bezeichnung_mehrsprachig[".$row->index."] = bezeichnung;";

		if(!$db->db_query($qry))
			echo '<strong>Setzen der bezeichnung_mehrsprachig fehlgeschlagen: '.$db->db_last_error().'</strong><br>';
		else
			echo 'lehre.tbl_note: bezeichnung_mehrprachig automatisch aus existierender Bezeichnung uebernommen<br>';
	}
}

// Column design_uid, betrieb_uid and operativ_uid to tbl_service
if(!$result = @$db->db_query("SELECT design_uid FROM public.tbl_service LIMIT 1;"))
{
	$qry = "ALTER TABLE public.tbl_service ADD COLUMN design_uid varchar(32);
			ALTER TABLE public.tbl_service ADD COLUMN betrieb_uid varchar(32);
			ALTER TABLE public.tbl_service ADD COLUMN operativ_uid varchar(32);
			ALTER TABLE public.tbl_service ADD CONSTRAINT fk_tbl_service_design_uid FOREIGN KEY (design_uid) REFERENCES public.tbl_benutzer (uid) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE public.tbl_service ADD CONSTRAINT fk_tbl_service_betrieb_uid FOREIGN KEY (betrieb_uid) REFERENCES public.tbl_benutzer (uid) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE public.tbl_service ADD CONSTRAINT fk_tbl_service_operativ_uid FOREIGN KEY (operativ_uid) REFERENCES public.tbl_benutzer (uid) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_service: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_service: Spalten design_uid,betrieb_uid,operativ_uid hinzugefuegt!<br>';
}

// FOREIGN KEY tbl_phrasentext_sprache_fkey: system.tbl_phrasentext.sprache references public.tbl_sprache.sprache
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'tbl_phrasentext_sprache_fkey'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "ALTER TABLE system.tbl_phrasentext ADD CONSTRAINT tbl_phrasentext_sprache_fkey FOREIGN KEY (sprache) REFERENCES public.tbl_sprache(sprache) ON UPDATE CASCADE ON DELETE RESTRICT;";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_phrasentext: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>system.tbl_phrasentext: added foreign key on column sprache referenced to public.tbl_sprache(sprache)';
	}
}

// FOREIGN KEY tbl_phrasentext_orgeinheit_kurzbz_fkey: system.tbl_phrasentext.orgeinheit_kurzbz references public.tbl_organisationseinheit.orgeinheit_kurzbz
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'tbl_phrasentext_orgeinheit_kurzbz_fkey'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "ALTER TABLE system.tbl_phrasentext ADD CONSTRAINT tbl_phrasentext_orgeinheit_kurzbz_fkey FOREIGN KEY (orgeinheit_kurzbz) REFERENCES public.tbl_organisationseinheit(oe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_phrasentext: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>system.tbl_phrasentext: added foreign key on column orgeinheit_kurzbz referenced to public.tbl_organisationseinheit(orgeinheit_kurzbz)';
	}
}

// FOREIGN KEY tbl_phrasentext_orgform_kurzbz_fkey: system.tbl_phrasentext.orgform_kurzbz references bis.tbl_orgform.orgform_kurzbz
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'tbl_phrasentext_orgform_kurzbz_fkey'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "ALTER TABLE system.tbl_phrasentext ADD CONSTRAINT tbl_phrasentext_orgform_kurzbz_fkey FOREIGN KEY (orgform_kurzbz) REFERENCES bis.tbl_orgform(orgform_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_phrasentext: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>system.tbl_phrasentext: added foreign key on column orgform_kurzbz referenced to bis.tbl_orgform(orgform_kurzbz)';
	}
}

// Add FOREIGN KEY testtool.tbl_pruefling.prestudent_id
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'fk_pruefling_prestudent'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "SELECT * FROM testtool.tbl_pruefling WHERE prestudent_id is not null AND  NOT EXISTS(SELECT 1 FROM public.tbl_prestudent WHERE prestudent_id=tbl_pruefling.prestudent_id)";
		if($result = $db->db_query($qry))
		{
			if($db->db_num_rows($result) == 0)
			{
				$qry = "ALTER TABLE testtool.tbl_pruefling ADD CONSTRAINT fk_pruefling_prestudent FOREIGN KEY (prestudent_id) REFERENCES public.tbl_prestudent(prestudent_id) ON UPDATE CASCADE ON DELETE RESTRICT;";

				if (!$db->db_query($qry))
					echo '<strong>testtool.tbl_pruefling: '.$db->db_last_error().'</strong><br>';
				else
					echo '<br>testtool.tbl_pruefling: added foreign key on column prestudent_id referenced to public.tbl_prestudent(prestudent_id)';
			}
			else
			{
				echo '<strong>
				Foreign Key für testtool.tbl_pruefling.prestudent_id kann nicht erstellt werden da in tbl_pruefling
				'.$db->db_num_rows($result).' Prestudenten eingetragen sind die nicht in tbl_prestudent vorhanden sind.<br>
				<br>
				Bitte korrigieren Sie die fehlenden Zuordnungen damit der FK erstellt werden kann.
				Mit folgendem Befehl können die falschen Zuordnungen entfernt werden:<br>
				UPDATE testtool.tbl_pruefling SET prestudent_id=null WHERE NOT EXISTS(SELECT 1 FROM public.tbl_prestudent WHERE prestudent_id=tbl_pruefling.prestudent_id)
				</strong>';
			}
		}
	}
}

// ADD COLUMN insertamum to public.tbl_rt_person
if(!@$db->db_query("SELECT insertamum FROM public.tbl_rt_person LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_rt_person ADD COLUMN insertamum timestamp DEFAULT now();";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_rt_person '.$db->db_last_error().'</strong><br>';
    else
        echo '<br>Spalte insertamum in public.tbl_rt_person hinzugefügt';
}

// ADD COLUMN insertvon to public.tbl_rt_person
if(!@$db->db_query("SELECT insertvon FROM public.tbl_rt_person LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_rt_person ADD COLUMN insertvon varchar(32);";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_rt_person '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte insertvon in public.tbl_rt_person hinzugefügt';
}

// ADD COLUMN updateamum to public.tbl_rt_person
if(!@$db->db_query("SELECT updateamum FROM public.tbl_rt_person LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_rt_person ADD COLUMN updateamum timestamp DEFAULT now();";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_rt_person '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte updateamum in public.tbl_rt_person hinzugefügt';
}

// ADD COLUMN updatevon to public.tbl_rt_person
if(!@$db->db_query("SELECT updatevon FROM public.tbl_rt_person LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_rt_person ADD COLUMN updatevon varchar(32);";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_rt_person '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte updatevon in public.tbl_rt_person hinzugefügt';
}

// Neue Funktion get_highest_content_version
if(!@$db->db_query("SELECT campus.get_highest_content_version(0)"))
{
	$qry = 'CREATE FUNCTION campus.get_highest_content_version(bigint) RETURNS smallint
			LANGUAGE plpgsql
			AS $_$
					DECLARE i_content_id ALIAS FOR $1;
					DECLARE rec RECORD;
					BEGIN
					SELECT INTO rec version
					FROM campus.tbl_contentsprache
					WHERE content_id=i_content_id
					ORDER BY version desc
					LIMIT 1;

			RETURN rec.version;
			END;
			$_$;

			ALTER FUNCTION campus.get_highest_content_version(bigint) OWNER TO fhcomplete;';

	if(!$db->db_query($qry))
		echo '<strong>campus.get_highest_content_version(content_id): '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Funktion get_highest_content_version(content_id) hinzugefügt';
}

// ADD COLUMN ausstellungsnation and formal_geprueft_amum to public.tbl_akte
if(!@$db->db_query("SELECT ausstellungsnation FROM public.tbl_akte LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_akte ADD COLUMN ausstellungsnation varchar(3);
			ALTER TABLE public.tbl_akte ADD CONSTRAINT fk_tbl_akte_ausstellungsnation FOREIGN KEY (ausstellungsnation) REFERENCES bis.tbl_nation(nation_code) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE public.tbl_akte ADD COLUMN formal_geprueft_amum timestamp;
			COMMENT ON COLUMN public.tbl_akte.ausstellungsnation IS 'Nation-Code des Landes, in dem das Dokument ausgestellt wurde';
			COMMENT ON COLUMN public.tbl_akte.formal_geprueft_amum IS 'Bestaetigungsdatum, an dem das Dokument inhaltlich auf Formalkriterien (Leserlichkeit, Vollständigkeit, etc) geprueft wurde';
			";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_rt_person '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte updatevon in public.tbl_rt_person hinzugefügt';
}

// ADD COLUMN ausstellungsdetails (boolean) to public.tbl_dokument
if(!@$db->db_query("SELECT ausstellungsdetails FROM public.tbl_dokument LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_dokument ADD COLUMN ausstellungsdetails boolean NOT NULL DEFAULT false;
			COMMENT ON COLUMN public.tbl_dokument.ausstellungsdetails IS 'Sollen beim Dokument weitere Felder (zB Ausstellungsnation) angezeigt werden?';
			";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_dokument '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte ausstellungsdetails in public.tbl_dokument hinzugefügt';
}


//---------------------------------------------------------------------------------------------------------------------
// Start extensions

// SEQUENCE tbl_extensions_id_seq
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'tbl_extensions_id_seq'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = '
			CREATE SEQUENCE system.tbl_extensions_id_seq
			    START WITH 1
			    INCREMENT BY 1
			    NO MAXVALUE
			    NO MINVALUE
			    CACHE 1;
			';
		if(!$db->db_query($qry))
			echo '<strong>system.tbl_extensions_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created sequence: system.tbl_extensions_id_seq';

		// GRANT SELECT, UPDATE ON SEQUENCE system.tbl_extensions_id_seq TO vilesci;
		$qry = 'GRANT SELECT, UPDATE ON SEQUENCE system.tbl_extensions_id_seq TO vilesci;';
		if (!$db->db_query($qry))
			echo '<strong>system.tbl_extensions_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_extensions_id_seq';

		// GRANT SELECT, UPDATE ON SEQUENCE system.tbl_extensions_id_seq TO fhcomplete;
		$qry = 'GRANT SELECT, UPDATE ON SEQUENCE system.tbl_extensions_id_seq TO fhcomplete;';
		if (!$db->db_query($qry))
			echo '<strong>system.tbl_extensions_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_extensions_id_seq';
	}
}

// TABLE system.tbl_extensions
if(!@$db->db_query("SELECT 0 FROM system.tbl_extensions WHERE 0 = 1"))
{
	$qry = '
		CREATE TABLE system.tbl_extensions (
			extension_id integer NOT NULL DEFAULT NEXTVAL(\'system.tbl_extensions_id_seq\'),
		    name character varying(128) NOT NULL,
		    version integer NOT NULL,
		    description text,
		    license character varying(256),
		    url character varying(256),
		    core_version character varying(48) NOT NULL,
		    dependencies character varying(128)[],
		    enabled boolean NOT NULL DEFAULT true
		);';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_extensions '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Created table system.tbl_extensions';

	// GRANT SELECT ON TABLE system.tbl_extensions TO web;
	$qry = 'GRANT SELECT ON TABLE system.tbl_extensions TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_extensions '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_extensions';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_extensions TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_extensions TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_extensions '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_extensions';

	// COMMENT ON TABLE system.tbl_extensions
	$qry = 'COMMENT ON TABLE system.tbl_extensions IS \'Table to manage extensions\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_extensions: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_extensions';

	// COMMENT ON COLUMN system.tbl_extensions.name
	$qry = 'COMMENT ON COLUMN system.tbl_extensions.name IS \'Extension unique name\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_extensions.name: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_extensions.name';

	// COMMENT ON COLUMN system.tbl_extensions.core_version
	$qry = 'COMMENT ON COLUMN system.tbl_extensions.core_version IS \'Minimum required core version\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_extensions.core_version: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_extensions.core_version';

	// COMMENT ON COLUMN system.tbl_extensions.dependencies
	$qry = 'COMMENT ON COLUMN system.tbl_extensions.dependencies IS \'Required extensions\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_extensions.dependencies: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_extensions.dependencies';

	// ALTER SEQUENCE system.tbl_extensions_id_seq
	$qry = 'ALTER SEQUENCE system.tbl_extensions_id_seq OWNED BY system.tbl_extensions.extension_id;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_extensions_id_seq '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Altered sequence system.tbl_extensions_id_seq';
}

// UNIQUE INDEX uidx_extensions_name_version
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'uidx_extensions_name_version'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'CREATE UNIQUE INDEX uidx_extensions_name_version ON system.tbl_extensions USING btree (name, version);';
		if (!$db->db_query($qry))
			echo '<strong>uidx_extensions_name_version '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created unique uidx_extensions_name_version';
	}
}

// Add permission for extensions
if ($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'system/extensions';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('system/extensions', 'To manage core extensions');";
		if (!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for extensions<br>';
	}
}

// End extensions
//---------------------------------------------------------------------------------------------------------------------

if (!$result = @$db->db_query("SELECT 1 FROM system.tbl_log LIMIT 1"))
{
	$qry = "CREATE TABLE system.tbl_log
			(
				log_id bigint NOT NULL,
				person_id integer,
				zeitpunkt timestamp NOT NULL DEFAULT now(),
				app varchar(32) NOT NULL,
				oe_kurzbz varchar(32),
				logtype_kurzbz varchar(32) NOT NULL,
				logdata jsonb NOT NULL,
				insertvon varchar(32)
			);
			ALTER TABLE system.tbl_log ADD CONSTRAINT pk_log PRIMARY KEY (log_id);

			CREATE SEQUENCE system.tbl_log_log_id_seq
			 INCREMENT BY 1
			 NO MAXVALUE
			 NO MINVALUE
			 CACHE 1;
			ALTER TABLE system.tbl_log ALTER COLUMN log_id SET DEFAULT nextval('system.tbl_log_log_id_seq');

			GRANT SELECT, INSERT ON system.tbl_log TO vilesci;
			GRANT SELECT, INSERT ON system.tbl_log TO web;
			GRANT SELECT, UPDATE ON system.tbl_log_log_id_seq TO vilesci;
			GRANT SELECT, UPDATE ON system.tbl_log_log_id_seq TO web;

			CREATE TABLE system.tbl_logtype
			(
				logtype_kurzbz varchar(32),
				data_schema jsonb NOT NULL
			);
			ALTER TABLE system.tbl_logtype ADD CONSTRAINT pk_logtype PRIMARY KEY (logtype_kurzbz);
			GRANT SELECT ON system.tbl_logtype TO vilesci;
			GRANT SELECT ON system.tbl_logtype TO web;

			ALTER TABLE system.tbl_log ADD CONSTRAINT fk_log_person_id FOREIGN KEY (person_id) REFERENCES public.tbl_person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_log ADD CONSTRAINT fk_log_app FOREIGN KEY (app) REFERENCES system.tbl_app(app) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_log ADD CONSTRAINT fk_log_oe_kurzbz FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit(oe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_log ADD CONSTRAINT fk_log_logtype_kurzbz FOREIGN KEY (logtype_kurzbz) REFERENCES system.tbl_logtype(logtype_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;

			INSERT INTO system.tbl_logtype VALUES ('Action', '{\"type\": \"object\", \"title\": \"Action\", \"required\": [\"name\", \"success\", \"message\"], \"properties\": {\"name\": {\"type\": \"string\"}, \"message\": {\"type\": \"string\"}, \"success\": {\"type\": \"string\"}}}');
			INSERT INTO system.tbl_logtype VALUES ('Processstate', '{\"type\": \"object\", \"title\": \"Processstate\", \"required\": [\"name\", \"message\"], \"properties\": {\"name\": {\"type\": \"string\"}, \"message\": {\"type\": \"string\"}}}');
			";
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_log '.$db->db_last_error().'</strong><br>';
	else
		echo ' system.tbl_log hinzugefügt<br>';
}

//---------------------------------------------------------------------------------------------------------------------
// Start filters

// SEQUENCE tbl_filters_id_seq
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'tbl_filters_id_seq'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = '
			CREATE SEQUENCE system.tbl_filters_id_seq
				START WITH 1
				INCREMENT BY 1
				NO MAXVALUE
				NO MINVALUE
				CACHE 1;
			';
		if(!$db->db_query($qry))
			echo '<strong>system.tbl_filters_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created sequence: system.tbl_filters_id_seq';

		// GRANT SELECT, UPDATE ON SEQUENCE system.tbl_filters_id_seq TO vilesci;
		$qry = 'GRANT SELECT, UPDATE ON SEQUENCE system.tbl_filters_id_seq TO vilesci;';
		if (!$db->db_query($qry))
			echo '<strong>system.tbl_filters_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_filters_id_seq';

		// GRANT SELECT, UPDATE ON SEQUENCE system.tbl_filters_id_seq TO fhcomplete;
		$qry = 'GRANT SELECT, UPDATE ON SEQUENCE system.tbl_filters_id_seq TO fhcomplete;';
		if (!$db->db_query($qry))
			echo '<strong>system.tbl_filters_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_filters_id_seq';
	}
}

// TABLE system.tbl_filters
if (!@$db->db_query("SELECT 0 FROM system.tbl_filters WHERE 0 = 1"))
{
	$qry = '
		CREATE TABLE system.tbl_filters (
			filter_id integer NOT NULL DEFAULT nextval(\'system.tbl_filters_id_seq\'::regclass),
			app character varying(32) NOT NULL,
			dataset_name character varying(128) NOT NULL,
			filter_kurzbz character varying(64) NOT NULL,
			person_id integer,
			description character varying(128)[] NOT NULL,
			sort integer,
			default_filter boolean DEFAULT FALSE,
			filter jsonb NOT NULL,
			oe_kurzbz character varying(16)
		);';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_filters '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Created table system.tbl_filters';

	// GRANT SELECT ON TABLE system.tbl_filters TO web;
	$qry = 'GRANT SELECT ON TABLE system.tbl_filters TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_filters '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_filters';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_filters TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_filters TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_filters '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_filters';

	// COMMENT ON TABLE system.tbl_filters
	$qry = 'COMMENT ON TABLE system.tbl_filters IS \'Table to manage filters\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_filters: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_filters';

	// COMMENT ON TABLE system.tbl_filters.app
	$qry = 'COMMENT ON COLUMN system.tbl_filters.app IS \'Application which this filter belongs to\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_filters.app: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_filters.app';

	// COMMENT ON TABLE system.tbl_filters.dataset_name
	$qry = 'COMMENT ON COLUMN system.tbl_filters.dataset_name IS \'Name that identifies the data set to be filtered\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_filters.dataset_name: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_filters.dataset_name';

	// COMMENT ON TABLE system.tbl_filters.filter_kurzbz
	$qry = 'COMMENT ON COLUMN system.tbl_filters.filter_kurzbz IS \'Short description of the filter, unique for this application and this data set\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_filters.filter_kurzbz: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_filters.filter_kurzbz';

	// COMMENT ON TABLE system.tbl_filters.person_id
	$qry = 'COMMENT ON COLUMN system.tbl_filters.person_id IS \'Person identifier which this filter belongs to. If null it is global\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_filters.person_id: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_filters.person_id';

	// COMMENT ON TABLE system.tbl_filters.description
	$qry = 'COMMENT ON COLUMN system.tbl_filters.description IS \'Long description for this filter\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_filters.description: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_filters.description';

	// COMMENT ON TABLE system.tbl_filters.sort
	$qry = 'COMMENT ON COLUMN system.tbl_filters.sort IS \'Indicates the order in which the filters appear in a list\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_filters.sort: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_filters.sort';

	// COMMENT ON TABLE system.tbl_filters.default_filter
	$qry = 'COMMENT ON COLUMN system.tbl_filters.default_filter IS \'If it is the default filter for that data set\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_filters.default_filter: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_filters.default_filter';

	// COMMENT ON TABLE system.tbl_filters.filter
	$qry = 'COMMENT ON COLUMN system.tbl_filters.filter IS \'Cointains json that define the filter\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_filters.filter: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_filters.filter';

	// COMMENT ON TABLE system.tbl_filters.oe_kurzbz
	$qry = 'COMMENT ON COLUMN system.tbl_filters.oe_kurzbz IS \'Organisation unit which this filter belongs to. If null it is for all the organisation units\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_filters.oe_kurzbz: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_filters.oe_kurzbz';

	// ALTER SEQUENCE system.tbl_filters_id_seq OWNED BY system.tbl_filters.filter_id;
	$qry = 'ALTER SEQUENCE system.tbl_filters_id_seq OWNED BY system.tbl_filters.filter_id;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_filters_id_seq '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Altered sequence system.tbl_filters_id_seq';
}

// UNIQUE INDEX uidx_filters_app_dataset_name_filter_kurzbz
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'uidx_filters_app_dataset_name_filter_kurzbz'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'CREATE UNIQUE INDEX uidx_filters_app_dataset_name_filter_kurzbz ON system.tbl_filters USING btree (app, dataset_name, filter_kurzbz);';
		if (!$db->db_query($qry))
			echo '<strong>uidx_filters_app_dataset_name_filter_kurzbz '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created unique uidx_filters_app_dataset_name_filter_kurzbz';
	}
}

// Add permission for filters
if ($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'system/filters';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('system/filters', 'To manage core filters');";
		if (!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for filters<br>';
	}
}

// FOREIGN KEY tbl_filters_app_fkey
if ($result = $db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'tbl_filters_app_fkey'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'ALTER TABLE system.tbl_filters ADD CONSTRAINT tbl_filters_app_fkey FOREIGN KEY (app) REFERENCES system.tbl_app(app) ON UPDATE CASCADE ON DELETE RESTRICT;';
		if (!$db->db_query($qry))
			echo '<strong>tbl_filters_app_fkey '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created foreign key tbl_filters_app_fkey';
	}
}

// FOREIGN KEY tbl_filters_person_id_fkey
if ($result = $db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'tbl_filters_person_id_fkey'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'ALTER TABLE system.tbl_filters ADD CONSTRAINT tbl_filters_person_id_fkey FOREIGN KEY (person_id) REFERENCES public.tbl_person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;';
		if (!$db->db_query($qry))
			echo '<strong>tbl_filters_person_id_fkey '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created foreign key tbl_filters_person_id_fkey';
	}
}

// FOREIGN KEY tbl_filters_oe_kurzbz_fkey
if ($result = $db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'tbl_filters_oe_kurzbz_fkey'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'ALTER TABLE system.tbl_filters ADD CONSTRAINT tbl_filters_oe_kurzbz_fkey FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit(oe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;';
		if (!$db->db_query($qry))
			echo '<strong>tbl_filters_oe_kurzbz_fkey '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created foreign key tbl_filters_oe_kurzbz_fkey';
	}
}
// End filters
//---------------------------------------------------------------------------------------------------------------------

// system.tbl_verarbeitungstaetigkeit
if (!$result = @$db->db_query("SELECT 1 FROM system.tbl_verarbeitungstaetigkeit"))
{
	$qry = "
	CREATE TABLE system.tbl_verarbeitungstaetigkeit
	(
		taetigkeit_kurzbz varchar(32) NOT NULL,
		bezeichnung varchar(255),
		bezeichnung_mehrsprachig varchar(255)[],
		aktiv boolean DEFAULT true
	);

	ALTER TABLE system.tbl_verarbeitungstaetigkeit ADD CONSTRAINT pk_verarbeitungstaetigkeit PRIMARY KEY (taetigkeit_kurzbz);

	INSERT INTO system.tbl_verarbeitungstaetigkeit(taetigkeit_kurzbz, bezeichnung, bezeichnung_mehrsprachig, aktiv)
	VALUES('bewerbung','Bewerbung','{\'Bewerbung\',\'Bewerbung\'}', true);
	INSERT INTO system.tbl_verarbeitungstaetigkeit(taetigkeit_kurzbz, bezeichnung, bezeichnung_mehrsprachig, aktiv)
	VALUES('aufnahme','Reihungs-/Aufnahmeverfahren','{\'Reihungs-/Aufnahmeverfahren\',\'Reihungs-/Aufnahmeverfahren\'}', true);
	INSERT INTO system.tbl_verarbeitungstaetigkeit(taetigkeit_kurzbz, bezeichnung, bezeichnung_mehrsprachig, aktiv)
	VALUES('bewertung','Bewertung/Benotung','{\'Bewertung/Benotung\',\'Bewertung/Benotung\'}', true);
	INSERT INTO system.tbl_verarbeitungstaetigkeit(taetigkeit_kurzbz, bezeichnung, bezeichnung_mehrsprachig, aktiv)
	VALUES('lehrauftraege','Lehraufträge','{\'Lehraufträge\',\'Lehraufträge\'}', true);
	INSERT INTO system.tbl_verarbeitungstaetigkeit(taetigkeit_kurzbz, bezeichnung, bezeichnung_mehrsprachig, aktiv)
	VALUES('datenwartung','Datenwartung','{\'Datenwartung\',\'Datenwartung\'}', true);
	INSERT INTO system.tbl_verarbeitungstaetigkeit(taetigkeit_kurzbz, bezeichnung, bezeichnung_mehrsprachig, aktiv)
	VALUES('kommunikation','Kommunikation','{\'Kommunikation\',\'Kommunikation\'}', true);

	GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_verarbeitungstaetigkeit TO vilesci;
	GRANT SELECT ON system.tbl_verarbeitungstaetigkeit TO web;
	";
	if (!$db->db_query($qry))
		echo '<strong>tbl_verarbeitungstaetigkeit '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Created new table system.tbl_verarbeitungstaetigkeit';
}

// system.tbl_log.taetigkeit_kurzbz
if (!$result = @$db->db_query("SELECT taetigkeit_kurzbz FROM system.tbl_log"))
{
	$qry = "
	ALTER TABLE system.tbl_log ADD COLUMN taetigkeit_kurzbz varchar(32);
	ALTER TABLE system.tbl_log ADD CONSTRAINT fk_log_taetigkeit FOREIGN KEY (taetigkeit_kurzbz) REFERENCES system.tbl_verarbeitungstaetigkeit(taetigkeit_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
	";
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_log.taetigkeit_kurzbz '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added Column taetigkeit_kurzbz to system.tbl_log';
}

// Add missing primary Key to system.tbl_filters.filter_id
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'pk_filters_filter_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "ALTER TABLE system.tbl_filters ADD CONSTRAINT pk_filters_filter_id PRIMARY KEY (filter_id);";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_filters '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>system.tbl_filters: added primary key on column filter_id';
	}
}

// Add index to tbl_akte
if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_tbl_akte_dokument_kurzbz'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = " 	CREATE INDEX idx_tbl_akte_dokument_kurzbz ON tbl_akte USING btree (dokument_kurzbz);
					CREATE INDEX idx_tbl_akte_person_id ON tbl_akte USING btree (person_id);
					CREATE INDEX idx_tbl_akte_person_id_dokument_kurzbz ON tbl_akte USING btree (person_id, dokument_kurzbz)";

		if (! $db->db_query($qry))
			echo '<strong>Indizes: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Diverse Indizes fuer tbl_akte hinzugefuegt';
	}
}

// Berechtigungen fuer vilesci User erteilen auf system.tbl_log
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_log' AND table_schema='system' AND grantee='vilesci' AND privilege_type='UPDATE'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "GRANT UPDATE ON system.tbl_log TO vilesci;";

		if(!$db->db_query($qry))
			echo '<strong>Permission Log: '.$db->db_last_error().'</strong><br>';
		else
			echo 'Updaterechte auf system.tbl_log für Vilesci User hinzugefügt';
	}
}

// App 'core' hinzufügen
if($result = $db->db_query("SELECT 1 FROM system.tbl_app WHERE app='core'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "INSERT INTO system.tbl_app(app) VALUES('core');";

		if(!$db->db_query($qry))
			echo '<strong>App: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue App core in system.tbl_app hinzugefügt';
	}
}

// App 'infocenter' hinzufügen
if($result = $db->db_query("SELECT 1 FROM system.tbl_app WHERE app='infocenter'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "INSERT INTO system.tbl_app(app) VALUES('infocenter');";

		if(!$db->db_query($qry))
			echo '<strong>App: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue App infocenter in system.tbl_app hinzugefügt';
	}
}
// App 'bewerbung' hinzufügen
if($result = $db->db_query("SELECT 1 FROM system.tbl_app WHERE app='bewerbung'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "INSERT INTO system.tbl_app(app) VALUES('bewerbung');";

		if(!$db->db_query($qry))
			echo '<strong>App: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue App bewerbung in system.tbl_app hinzugefügt';
	}
}


// *** Pruefung und hinzufuegen der neuen Attribute und Tabellen
echo '<H2>Pruefe Tabellen und Attribute!</H2>';

echo '<br><br><br>';

$tabellen=array(
	"bis.tbl_bisorgform" => array("bisorgform_kurzbz","code","bezeichnung"),
	"bis.tbl_archiv"  => array("archiv_id","studiensemester_kurzbz","meldung","html","studiengang_kz","insertamum","insertvon","typ"),
	"bis.tbl_ausbildung"  => array("ausbildungcode","ausbildungbez","ausbildungbeschreibung"),
	"bis.tbl_berufstaetigkeit"  => array("berufstaetigkeit_code","berufstaetigkeit_bez","berufstaetigkeit_kurzbz"),
	"bis.tbl_beschaeftigungsart1"  => array("ba1code","ba1bez","ba1kurzbz"),
	"bis.tbl_beschaeftigungsart2"  => array("ba2code","ba2bez"),
	"bis.tbl_beschaeftigungsausmass"  => array("beschausmasscode","beschausmassbez","min","max"),
	"bis.tbl_besqual"  => array("besqualcode","besqualbez"),
	"bis.tbl_bisfunktion"  => array("bisverwendung_id","studiengang_kz","sws","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_bisio"  => array("bisio_id","mobilitaetsprogramm_code","nation_code","von","bis","zweck_code","student_uid","updateamum","updatevon","insertamum","insertvon","ext_id","ort","universitaet","lehreinheit_id"),
	"bis.tbl_bisverwendung"  => array("bisverwendung_id","ba1code","ba2code","vertragsstunden","beschausmasscode","verwendung_code","mitarbeiter_uid","hauptberufcode","hauptberuflich","habilitation","beginn","ende","updateamum","updatevon","insertamum","insertvon","ext_id","dv_art","inkludierte_lehre"),
	"bis.tbl_bundesland"  => array("bundesland_code","kurzbz","bezeichnung"),
	"bis.tbl_entwicklungsteam"  => array("mitarbeiter_uid","studiengang_kz","besqualcode","beginn","ende","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_gemeinde"  => array("gemeinde_id","plz","name","ortschaftskennziffer","ortschaftsname","bulacode","bulabez","kennziffer"),
	"bis.tbl_gsstudientyp" => array("gsstudientyp_kurzbz","bezeichnung","studientyp_code"),
	"bis.tbl_gsprogrammtyp" => array("gsprogrammtyp_kurzbz","bezeichnung","programmtyp_code"),
	"bis.tbl_gsprogramm" => array("gsprogramm_id","programm_code","bezeichnung","gsprogrammtyp_kurzbz"),
	"bis.tbl_hauptberuf"  => array("hauptberufcode","bezeichnung"),
	"bis.tbl_lgartcode"  => array("lgartcode","kurzbz","bezeichnung","beantragung","lgart_biscode"),
	"bis.tbl_mobilitaet" => array("mobilitaet_id","prestudent_id","mobilitaetstyp_kurzbz","studiensemester_kurzbz","mobilitaetsprogramm_code","gsprogramm_id","firma_id","status_kurzbz","ausbildungssemester","insertvon","insertamum","updatevon","updateamum"),
	"bis.tbl_mobilitaetstyp" => array("mobilitaetstyp_kurzbz","bezeichnung","aktiv"),
	"bis.tbl_mobilitaetsprogramm"  => array("mobilitaetsprogramm_code","kurzbz","beschreibung","sichtbar","sichtbar_outgoing"),
	"bis.tbl_nation"  => array("nation_code","entwicklungsstand","eu","ewr","kontinent","kurztext","langtext","engltext","sperre"),
	"bis.tbl_orgform"  => array("orgform_kurzbz","code","bezeichnung","rolle","bisorgform_kurzbz"),
	"bis.tbl_verwendung"  => array("verwendung_code","verwendungbez"),
	"bis.tbl_zgv"  => array("zgv_code","zgv_bez","zgv_kurzbz","bezeichnung"),
	"bis.tbl_zgvmaster"  => array("zgvmas_code","zgvmas_bez","zgvmas_kurzbz","bezeichnung"),
	"bis.tbl_zgvdoktor" => array("zgvdoktor_code", "zgvdoktor_bez", "zgvdoktor_kurzbz","bezeichnung"),
	"bis.tbl_zweck"  => array("zweck_code","kurzbz","bezeichnung"),
	"bis.tbl_zgvgruppe"  => array("gruppe_kurzbz","bezeichnung"),
	"bis.tbl_zgvgruppe_zuordnung"  => array("zgvgruppe_id" ,"studiengang_kz","zgv_code","zgvmas_code","gruppe_kurzbz"),
	"campus.tbl_abgabe"  => array("abgabe_id","abgabedatei","abgabezeit","anmerkung"),
	"campus.tbl_anwesenheit"  => array("anwesenheit_id","uid","einheiten","datum","anwesend","lehreinheit_id","anmerkung","ext_id"),
	"campus.tbl_beispiel"  => array("beispiel_id","uebung_id","nummer","bezeichnung","punkte","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_benutzerlvstudiensemester"  => array("uid","studiensemester_kurzbz","lehrveranstaltung_id"),
	"campus.tbl_content"  => array("content_id","template_kurzbz","updatevon","updateamum","insertamum","insertvon","oe_kurzbz","menu_open","aktiv","beschreibung"),
	"campus.tbl_contentchild"  => array("contentchild_id","content_id","child_content_id","updatevon","updateamum","insertamum","insertvon","sort"),
	"campus.tbl_contentgruppe"  => array("content_id","gruppe_kurzbz","insertamum","insertvon"),
	"campus.tbl_contentlog"  => array("contentlog_id","contentsprache_id","uid","start","ende"),
	"campus.tbl_contentsprache"  => array("contentsprache_id","content_id","sprache","version","sichtbar","content","reviewvon","reviewamum","updateamum","updatevon","insertamum","insertvon","titel","gesperrt_uid"),
	"campus.tbl_coodle"  => array("coodle_id","titel","beschreibung","coodle_status_kurzbz","dauer","endedatum","insertamum","insertvon","updateamum","updatevon","ersteller_uid","mailversand","teilnehmer_anonym","termine_anonym"),
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
	"campus.tbl_infoscreen_content"  => array("infoscreen_content_id","infoscreen_id","content_id","gueltigvon","gueltigbis","insertamum","insertvon","updateamum","updatevon","refreshzeit","exklusiv"),
	"campus.tbl_legesamtnote"  => array("student_uid","lehreinheit_id","note","benotungsdatum","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_lehre_tools" => array("lehre_tools_id","bezeichnung","kurzbz","basis_url","logo_dms_id"),
	"campus.tbl_lehre_tools_organisationseinheit" => array("lehre_tools_id","oe_kurzbz","aktiv"),
	"campus.tbl_lehrveranstaltung_pruefung" => array("lehrveranstaltung_pruefung_id","lehrveranstaltung_id","pruefung_id"),
	"campus.tbl_lvgesamtnote"  => array("lehrveranstaltung_id","studiensemester_kurzbz","student_uid","note","mitarbeiter_uid","benotungsdatum","freigabedatum","freigabevon_uid","bemerkung","updateamum","updatevon","insertamum","insertvon","punkte","ext_id"),
	"campus.tbl_lvinfo"  => array("lehrveranstaltung_id","sprache","titel","lehrziele","lehrinhalte","methodik","voraussetzungen","unterlagen","pruefungsordnung","anmerkung","kurzbeschreibung","genehmigt","aktiv","updateamum","updatevon","insertamum","insertvon","anwesenheit"),
	"campus.tbl_news"  => array("news_id","uid","studiengang_kz","fachbereich_kurzbz","semester","betreff","text","datum","verfasser","updateamum","updatevon","insertamum","insertvon","datum_bis","content_id"),
	"campus.tbl_notenschluessel"  => array("lehreinheit_id","note","punkte"),
	"campus.tbl_notenschluesseluebung"  => array("uebung_id","note","punkte"),
	"campus.tbl_paabgabetyp"  => array("paabgabetyp_kurzbz","bezeichnung"),
	"campus.tbl_paabgabe"  => array("paabgabe_id","projektarbeit_id","paabgabetyp_kurzbz","fixtermin","datum","kurzbz","abgabedatum", "insertvon","insertamum","updatevon","updateamum"),
	"campus.tbl_pruefungsfenster" => array("pruefungsfenster_id","studiensemester_kurzbz","oe_kurzbz","start","ende"),
	"campus.tbl_pruefung" => array("pruefung_id","mitarbeiter_uid","studiensemester_kurzbz","pruefungsfenster_id","pruefungstyp_kurzbz","titel","beschreibung","methode","einzeln","storniert","insertvon","insertamum","updatevon","updateamum","pruefungsintervall"),
	"campus.tbl_pruefungstermin" => array("pruefungstermin_id","pruefung_id","von","bis","teilnehmer_max","teilnehmer_min","anmeldung_von","anmeldung_bis","ort_kurzbz","sammelklausur"),
	"campus.tbl_pruefungsanmeldung" => array("pruefungsanmeldung_id","uid","pruefungstermin_id","lehrveranstaltung_id","status_kurzbz","wuensche","reihung","kommentar","statusupdatevon","statusupdateamum","anrechnung_id","pruefungstyp_kurzbz","insertamum"),
	"campus.tbl_pruefungsstatus" => array("status_kurzbz","bezeichnung"),
	"campus.tbl_reservierung"  => array("reservierung_id","ort_kurzbz","studiengang_kz","uid","stunde","datum","titel","beschreibung","semester","verband","gruppe","gruppe_kurzbz","veranstaltung_id","insertamum","insertvon"),
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
	"fue.tbl_aktivitaet"  => array("aktivitaet_kurzbz","beschreibung","sort"),
	"fue.tbl_aufwandstyp" => array("aufwandstyp_kurzbz","bezeichnung"),
	"fue.tbl_projekt"  => array("projekt_kurzbz","nummer","titel","beschreibung","beginn","ende","oe_kurzbz","budget","farbe","aufwandstyp_kurzbz","ressource_id","anzahl_ma","aufwand_pt"),
	"fue.tbl_projektphase"  => array("projektphase_id","projekt_kurzbz","projektphase_fk","bezeichnung","typ","beschreibung","start","ende","budget","insertamum","insertvon","updateamum","updatevon","personentage","farbe","ressource_id"),
	"fue.tbl_projekttask"  => array("projekttask_id","projektphase_id","bezeichnung","beschreibung","aufwand","mantis_id","insertamum","insertvon","updateamum","updatevon","projekttask_fk","erledigt","ende","ressource_id","scrumsprint_id"),
	"fue.tbl_projekt_dokument"  => array("projekt_dokument_id","projektphase_id","projekt_kurzbz","dms_id"),
	"fue.tbl_projekt_ressource"  => array("projekt_ressource_id","projekt_kurzbz","projektphase_id","ressource_id","funktion_kurzbz","beschreibung","aufwand"),
	"fue.tbl_ressource"  => array("ressource_id","student_uid","mitarbeiter_uid","betriebsmittel_id","firma_id","bezeichnung","beschreibung","insertamum","insertvon","updateamum","updatevon"),
	"fue.tbl_scrumteam" => array("scrumteam_kurzbz","bezeichnung","punkteprosprint","tasksprosprint","gruppe_kurzbz"),
	"fue.tbl_scrumsprint" => array("scrumsprint_id","scrumteam_kurzbz","sprint_kurzbz","sprintstart","sprintende","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_abschlussbeurteilung"  => array("abschlussbeurteilung_kurzbz","bezeichnung","bezeichnung_english"),
	"lehre.tbl_abschlusspruefung"  => array("abschlusspruefung_id","student_uid","vorsitz","pruefer1","pruefer2","pruefer3","abschlussbeurteilung_kurzbz","akadgrad_id","pruefungstyp_kurzbz","datum","uhrzeit","sponsion","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","note"),
	"lehre.tbl_akadgrad"  => array("akadgrad_id","akadgrad_kurzbz","studiengang_kz","titel","geschlecht"),
	"lehre.tbl_anrechnung"  => array("anrechnung_id","prestudent_id","lehrveranstaltung_id","begruendung_id","lehrveranstaltung_id_kompatibel","genehmigt_von","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"lehre.tbl_anrechnung_begruendung"  => array("begruendung_id","bezeichnung"),
	"lehre.tbl_betreuerart"  => array("betreuerart_kurzbz","beschreibung"),
	"lehre.tbl_ferien"  => array("bezeichnung","studiengang_kz","vondatum","bisdatum"),
	"lehre.tbl_lehreinheit"  => array("lehreinheit_id","lehrveranstaltung_id","studiensemester_kurzbz","lehrfach_id","lehrform_kurzbz","stundenblockung","wochenrythmus","start_kw","raumtyp","raumtypalternativ","sprache","lehre","anmerkung","unr","lvnr","updateamum","updatevon","insertamum","insertvon","ext_id","lehrfach_id_old","gewicht"),
	"lehre.tbl_lehreinheitgruppe"  => array("lehreinheitgruppe_id","lehreinheit_id","studiengang_kz","semester","verband","gruppe","gruppe_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehreinheitmitarbeiter"  => array("lehreinheit_id","mitarbeiter_uid","lehrfunktion_kurzbz","semesterstunden","planstunden","stundensatz","faktor","anmerkung","bismelden","updateamum","updatevon","insertamum","insertvon","ext_id","standort_id","vertrag_id"),
	"lehre.tbl_lehrfach"  => array("lehrfach_id","studiengang_kz","fachbereich_kurzbz","kurzbz","bezeichnung","farbe","aktiv","semester","sprache","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehrform"  => array("lehrform_kurzbz","bezeichnung","verplanen","bezeichnung_kurz","bezeichnung_lang"),
	"lehre.tbl_lehrfunktion"  => array("lehrfunktion_kurzbz","beschreibung","standardfaktor","sort"),
	"lehre.tbl_lehrmittel" => array("lehrmittel_kurzbz","beschreibung","ort_kurzbz"),
	"lehre.tbl_lehrtyp" => array("lehrtyp_kurzbz","bezeichnung"),
	"lehre.tbl_lehrveranstaltung"  => array("lehrveranstaltung_id","kurzbz","bezeichnung","lehrform_kurzbz","studiengang_kz","semester","sprache","ects","semesterstunden","anmerkung","lehre","lehreverzeichnis","aktiv","planfaktor","planlektoren","planpersonalkosten","plankostenprolektor","koordinator","sort","zeugnis","projektarbeit","updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung_english","orgform_kurzbz","incoming","lehrtyp_kurzbz","oe_kurzbz","raumtyp_kurzbz","anzahlsemester","semesterwochen","lvnr","farbe","semester_alternativ","old_lehrfach_id","sws","lvs","alvs","lvps","las","benotung","lvinfo","lehrauftrag"),
	"lehre.tbl_lehrveranstaltung_kompatibel" => array("lehrveranstaltung_id","lehrveranstaltung_id_kompatibel"),
	"lehre.tbl_lvangebot" => array("lvangebot_id","lehrveranstaltung_id","studiensemester_kurzbz","gruppe_kurzbz","incomingplaetze","gesamtplaetze","anmeldefenster_start","anmeldefenster_ende","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_lvregel" => array("lvregel_id","lvregeltyp_kurzbz","operator","parameter","lvregel_id_parent","lehrveranstaltung_id","studienplan_lehrveranstaltung_id","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_lvregeltyp" => array("lvregeltyp_kurzbz","bezeichnung"),
	"lehre.tbl_notenschluessel" => array("notenschluessel_kurzbz","bezeichnung"),
	"lehre.tbl_notenschluesselaufteilung" => array("notenschluesselaufteilung_id","notenschluessel_kurzbz","note","punkte"),
	"lehre.tbl_notenschluesselzuordnung" => array("notenschluesselzuordnung_id","notenschluessel_kurzbz","lehrveranstaltung_id","studienplan_id","oe_kurzbz","studiensemester_kurzbz"),
	"lehre.tbl_note"  => array("note","bezeichnung","anmerkung","farbe","positiv","notenwert","aktiv","lehre","offiziell","bezeichnung_mehrsprachig"),
	"lehre.tbl_projektarbeit"  => array("projektarbeit_id","projekttyp_kurzbz","titel","lehreinheit_id","student_uid","firma_id","note","punkte","beginn","ende","faktor","freigegeben","gesperrtbis","stundensatz","gesamtstunden","themenbereich","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","titel_english","seitenanzahl","abgabedatum","kontrollschlagwoerter","schlagwoerter","schlagwoerter_en","abstract", "abstract_en", "sprache","final"),
	"lehre.tbl_projektbetreuer"  => array("person_id","projektarbeit_id","betreuerart_kurzbz","note","faktor","name","punkte","stunden","stundensatz","updateamum","updatevon","insertamum","insertvon","ext_id","vertrag_id"),
	"lehre.tbl_projekttyp"  => array("projekttyp_kurzbz","bezeichnung"),
	"lehre.tbl_pruefung"  => array("pruefung_id","lehreinheit_id","student_uid","mitarbeiter_uid","note","pruefungstyp_kurzbz","datum","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id","pruefungsanmeldung_id","vertrag_id", "punkte"),
	"lehre.tbl_pruefungstyp"  => array("pruefungstyp_kurzbz","beschreibung","abschluss","sort"),
	"lehre.tbl_studienordnung"  => array("studienordnung_id","studiengang_kz","version","gueltigvon","gueltigbis","bezeichnung","ects","studiengangbezeichnung","studiengangbezeichnung_englisch","studiengangkurzbzlang","akadgrad_id","insertamum","insertvon","updateamum","updatevon","ext_id", "status_kurzbz", "standort_id"),
	"lehre.tbl_studienordnungstatus" => array("status_kurzbz","bezeichnung","reihenfolge"),
	"lehre.tbl_studienordnung_semester"  => array("studienordnung_semester_id","studienordnung_id","studiensemester_kurzbz","semester"),
	"lehre.tbl_studienplan" => array("studienplan_id","studienordnung_id","orgform_kurzbz","version","regelstudiendauer","sprache","aktiv","bezeichnung","insertamum","insertvon","updateamum","updatevon","semesterwochen","testtool_sprachwahl","ext_id", "ects_stpl", "pflicht_sws", "pflicht_lvs","onlinebewerbung_studienplan"),
	"lehre.tbl_studienplan_lehrveranstaltung" => array("studienplan_lehrveranstaltung_id","studienplan_id","lehrveranstaltung_id","semester","studienplan_lehrveranstaltung_id_parent","pflicht","koordinator","insertamum","insertvon","updateamum","updatevon","sort","ext_id", "curriculum","export","genehmigung"),
	"lehre.tbl_studienplan_semester" => array("studienplan_semester_id", "studienplan_id", "studiensemester_kurzbz", "semester"),
	"lehre.tbl_studienplatz" => array("studienplatz_id","studiengang_kz","studiensemester_kurzbz","orgform_kurzbz","ausbildungssemester","gpz","npz","insertamum","insertvon","updateamum","updatevon","ext_id", "apz", "studienplan_id"),
	"lehre.tbl_stunde"  => array("stunde","beginn","ende"),
	"lehre.tbl_stundenplan"  => array("stundenplan_id","unr","mitarbeiter_uid","datum","stunde","ort_kurzbz","gruppe_kurzbz","titel","anmerkung","lehreinheit_id","studiengang_kz","semester","verband","gruppe","fix","updateamum","updatevon","insertamum","insertvon"),
	"lehre.tbl_stundenplandev"  => array("stundenplandev_id","lehreinheit_id","unr","studiengang_kz","semester","verband","gruppe","gruppe_kurzbz","mitarbeiter_uid","ort_kurzbz","datum","stunde","titel","anmerkung","fix","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_stundenplan_betriebsmittel" => array("stundenplan_betriebsmittel_id","betriebsmittel_id","stundenplandev_id","anmerkung","insertamum","insertvon"),
	"lehre.tbl_vertrag"  => array("vertrag_id","person_id","vertragstyp_kurzbz","bezeichnung","betrag","insertamum","insertvon","updateamum","updatevon","ext_id","anmerkung","vertragsdatum","lehrveranstaltung_id"),
	"lehre.tbl_vertrag_vertragsstatus"  => array("vertragsstatus_kurzbz","vertrag_id","uid","datum","ext_id","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_vertragstyp"  => array("vertragstyp_kurzbz","bezeichnung"),
	"lehre.tbl_vertragsstatus"  => array("vertragsstatus_kurzbz","bezeichnung"),
	"lehre.tbl_zeitfenster"  => array("wochentag","stunde","ort_kurzbz","studiengang_kz","gewicht"),
	"lehre.tbl_zeugnis"  => array("zeugnis_id","student_uid","zeugnis","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_zeugnisnote"  => array("lehrveranstaltung_id","student_uid","studiensemester_kurzbz","note","uebernahmedatum","benotungsdatum","bemerkung","updateamum","updatevon","insertamum","insertvon","ext_id","punkte"),
	"public.ci_apikey" => array("apikey_id","key","level","ignore_limits","date_created"),
	"public.tbl_adresse"  => array("adresse_id","person_id","name","strasse","plz","ort","gemeinde","nation","typ","heimatadresse","zustelladresse","firma_id","updateamum","updatevon","insertamum","insertvon","ext_id","rechnungsadresse","anmerkung"),
	"public.tbl_akte"  => array("akte_id","person_id","dokument_kurzbz","uid","inhalt","mimetype","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id","dms_id","nachgereicht","anmerkung","titel_intern","anmerkung_intern","nachgereicht_am","ausstellungsnation","formal_geprueft_amum"),
	"public.tbl_ampel"  => array("ampel_id","kurzbz","beschreibung","benutzer_select","deadline","vorlaufzeit","verfallszeit","insertamum","insertvon","updateamum","updatevon","email","verpflichtend","buttontext"),
	"public.tbl_ampel_benutzer_bestaetigt"  => array("ampel_benutzer_bestaetigt_id","ampel_id","uid","insertamum","insertvon"),
	"public.tbl_aufmerksamdurch"  => array("aufmerksamdurch_kurzbz","beschreibung","ext_id","bezeichnung", "aktiv"),
	"public.tbl_aufnahmeschluessel"  => array("aufnahmeschluessel"),
	"public.tbl_aufnahmetermin" => array("aufnahmetermin_id","aufnahmetermintyp_kurzbz","prestudent_id","termin","teilgenommen","bewertung","protokoll","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_aufnahmetermintyp" => array("aufnahmetermintyp_kurzbz","bezeichnung"),
	"public.tbl_bankverbindung"  => array("bankverbindung_id","person_id","name","anschrift","bic","blz","iban","kontonr","typ","verrechnung","updateamum","updatevon","insertamum","insertvon","ext_id","oe_kurzbz"),
	"public.tbl_benutzer"  => array("uid","person_id","aktiv","alias","insertamum","insertvon","updateamum","updatevon","ext_id","updateaktivvon","updateaktivam","aktivierungscode"),
	"public.tbl_benutzerfunktion"  => array("benutzerfunktion_id","fachbereich_kurzbz","uid","oe_kurzbz","funktion_kurzbz","semester", "datum_von","datum_bis", "updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung","wochenstunden"),
	"public.tbl_benutzergruppe"  => array("uid","gruppe_kurzbz","studiensemester_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_bewerbungstermine" => array("bewerbungstermin_id","studiengang_kz","studiensemester_kurzbz","beginn","ende","nachfrist","nachfrist_ende","anmerkung", "insertamum", "insertvon", "updateamum", "updatevon","studienplan_id"),
	"public.tbl_buchungstyp"  => array("buchungstyp_kurzbz","beschreibung","standardbetrag","standardtext","aktiv","credit_points"),
	"public.tbl_dokument"  => array("dokument_kurzbz","bezeichnung","ext_id","bezeichnung_mehrsprachig","dokumentbeschreibung_mehrsprachig","ausstellungsdetails"),
	"public.tbl_dokumentprestudent"  => array("dokument_kurzbz","prestudent_id","mitarbeiter_uid","datum","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_dokumentstudiengang"  => array("dokument_kurzbz","studiengang_kz","ext_id", "onlinebewerbung", "pflicht","beschreibung_mehrsprachig","nachreichbar"),
	"public.tbl_erhalter"  => array("erhalter_kz","kurzbz","bezeichnung","dvr","logo","zvr"),
	"public.tbl_fachbereich"  => array("fachbereich_kurzbz","bezeichnung","farbe","studiengang_kz","aktiv","ext_id","oe_kurzbz"),
	"public.tbl_filter" => array("filter_id","kurzbz","sql","valuename","showvalue","insertamum","insertvon","updateamum","updatevon","type","htmlattr"),
	"public.tbl_firma"  => array("firma_id","name","anmerkung","firmentyp_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id","schule","finanzamt","steuernummer","gesperrt","aktiv","lieferbedingungen","partner_code"),
	"public.tbl_firma_mobilitaetsprogramm" => array("firma_id","mobilitaetsprogramm_code","ext_id"),
	"public.tbl_firma_organisationseinheit"  => array("firma_organisationseinheit_id","firma_id","oe_kurzbz","bezeichnung","kundennummer","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_firmentyp"  => array("firmentyp_kurzbz","beschreibung"),
	"public.tbl_firmatag"  => array("firma_id","tag","insertamum","insertvon"),
	"public.tbl_fotostatus"  => array("fotostatus_kurzbz","beschreibung"),
	"public.tbl_funktion"  => array("funktion_kurzbz","beschreibung","aktiv","fachbereich","semester"),
	"public.tbl_geschaeftsjahr"  => array("geschaeftsjahr_kurzbz","start","ende","bezeichnung"),
	"public.tbl_gruppe"  => array("gruppe_kurzbz","studiengang_kz","semester","bezeichnung","beschreibung","sichtbar","lehre","aktiv","sort","mailgrp","generiert","updateamum","updatevon","insertamum","insertvon","ext_id","orgform_kurzbz","gid","content_visible","gesperrt","zutrittssystem","aufnahmegruppe"),
	"public.tbl_kontakt"  => array("kontakt_id","person_id","kontakttyp","anmerkung","kontakt","zustellung","updateamum","updatevon","insertamum","insertvon","ext_id","standort_id"),
	"public.tbl_kontaktmedium"  => array("kontaktmedium_kurzbz","beschreibung"),
	"public.tbl_kontakttyp"  => array("kontakttyp","beschreibung"),
	"public.tbl_konto"  => array("buchungsnr","person_id","studiengang_kz","studiensemester_kurzbz","buchungstyp_kurzbz","buchungsnr_verweis","betrag","buchungsdatum","buchungstext","mahnspanne","updateamum","updatevon","insertamum","insertvon","ext_id","credit_points", "zahlungsreferenz", "anmerkung"),
	"public.tbl_lehrverband"  => array("studiengang_kz","semester","verband","gruppe","aktiv","bezeichnung","ext_id","orgform_kurzbz","gid"),
	"public.tbl_log"  => array("log_id","executetime","mitarbeiter_uid","beschreibung","sql","sqlundo"),
	"public.tbl_mitarbeiter"  => array("mitarbeiter_uid","personalnummer","telefonklappe","kurzbz","lektor","fixangestellt","bismelden","stundensatz","ausbildungcode","ort_kurzbz","standort_id","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id","kleriker"),
	"public.tbl_msg_attachment" => array("attachment_id","message_id","name","filename"),
	"public.tbl_msg_message" => array("message_id","person_id","subject","body","priority","relationmessage_id","oe_kurzbz","insertamum","insertvon"),
	"public.tbl_msg_recipient" => array("message_id","person_id","token","sent","sentinfo","insertamum","insertvon"),
	"public.tbl_msg_status" => array("message_id","person_id","status","statusinfo","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_notiz"  => array("notiz_id","titel","text","verfasser_uid","bearbeiter_uid","start","ende","erledigt","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_notizzuordnung"  => array("notizzuordnung_id","notiz_id","projekt_kurzbz","projektphase_id","projekttask_id","uid","person_id","prestudent_id","bestellung_id","lehreinheit_id","ext_id","anrechnung_id"),
	"public.tbl_notiz_dokument" => array("notiz_id","dms_id"),
	"public.tbl_ort"  => array("ort_kurzbz","bezeichnung","planbezeichnung","max_person","lehre","reservieren","aktiv","lageplan","dislozierung","kosten","ausstattung","updateamum","updatevon","insertamum","insertvon","ext_id","stockwerk","standort_id","telefonklappe","content_id","m2","gebteil","oe_kurzbz","arbeitsplaetze"),
	"public.tbl_ortraumtyp"  => array("ort_kurzbz","hierarchie","raumtyp_kurzbz"),
	"public.tbl_organisationseinheit" => array("oe_kurzbz", "oe_parent_kurzbz", "bezeichnung","organisationseinheittyp_kurzbz", "aktiv","mailverteiler","freigabegrenze","kurzzeichen","lehre","standort","warn_semesterstunden_frei","warn_semesterstunden_fix","standort_id"),
	"public.tbl_organisationseinheittyp" => array("organisationseinheittyp_kurzbz", "bezeichnung", "beschreibung"),
	"public.tbl_person"  => array("person_id","staatsbuergerschaft","geburtsnation","sprache","anrede","titelpost","titelpre","nachname","vorname","vornamen","gebdatum","gebort","gebzeit","foto","anmerkung","homepage","svnr","ersatzkennzeichen","familienstand","geschlecht","anzahlkinder","aktiv","insertamum","insertvon","updateamum","updatevon","ext_id","bundesland_code","kompetenzen","kurzbeschreibung","zugangscode", "foto_sperre","matr_nr","zugangscode_timestamp","udf_values"),
	"public.tbl_person_fotostatus"  => array("person_fotostatus_id","person_id","fotostatus_kurzbz","datum","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_personfunktionstandort"  => array("personfunktionstandort_id","funktion_kurzbz","person_id","standort_id","position","anrede"),
	"public.tbl_preincoming"  => array("preincoming_id","person_id","mobilitaetsprogramm_code","zweck_code","firma_id","universitaet","aktiv","bachelorthesis","masterthesis","von","bis","uebernommen","insertamum","insertvon","updateamum","updatevon","anmerkung","zgv","zgv_ort","zgv_datum","zgv_name","zgvmaster","zgvmaster_datum","zgvmaster_ort","zgvmaster_name","program_name","bachelor","master","jahre","person_id_emergency","person_id_coordinator_dep","person_id_coordinator_int","code","deutschkurs1","deutschkurs2","research_area","deutschkurs3","ext_id"),
	"public.tbl_preincoming_lehrveranstaltung"  => array("preincoming_id","lehrveranstaltung_id","insertamum","insertvon"),
	"public.tbl_preinteressent"  => array("preinteressent_id","person_id","studiensemester_kurzbz","firma_id","erfassungsdatum","einverstaendnis","absagedatum","anmerkung","maturajahr","infozusendung","aufmerksamdurch_kurzbz","kontaktmedium_kurzbz","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_preinteressentstudiengang"  => array("studiengang_kz","preinteressent_id","freigabedatum","uebernahmedatum","prioritaet","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_preoutgoing" => array("preoutgoing_id","uid","dauer_von","dauer_bis","ansprechperson","bachelorarbeit","masterarbeit","betreuer","sprachkurs","intensivsprachkurs","sprachkurs_von","sprachkurs_bis","praktikum","praktikum_von","praktikum_bis","behinderungszuschuss","studienbeihilfe","anmerkung_student", "anmerkung_admin", "studienrichtung_gastuniversitaet", "insertamum","insertvon","updateamum","updatevon","projektarbeittitel","ext_id"),
	"public.tbl_preoutgoing_firma" => array("preoutgoing_firma_id","preoutgoing_id","mobilitaetsprogramm_code","firma_id","name","auswahl","ext_id"),
	"public.tbl_preoutgoing_lehrveranstaltung" => array("preoutgoing_lehrveranstaltung_id","preoutgoing_id","bezeichnung","ects","endversion","insertamum","insertvon","updateamum","updatevon","wochenstunden","unitcode"),
	"public.tbl_preoutgoing_preoutgoing_status" => array("status_id","preoutgoing_status_kurzbz","preoutgoing_id","datum","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_preoutgoing_status" => array("preoutgoing_status_kurzbz","bezeichnung"),
	"public.tbl_prestudent"  => array("prestudent_id","aufmerksamdurch_kurzbz","person_id","studiengang_kz","berufstaetigkeit_code","ausbildungcode","zgv_code","zgvort","zgvdatum","zgvmas_code","zgvmaort","zgvmadatum","aufnahmeschluessel","facheinschlberuf","reihungstest_id","anmeldungreihungstest","reihungstestangetreten","rt_gesamtpunkte","rt_punkte1","rt_punkte2","bismelden","anmerkung","dual","insertamum","insertvon","updateamum","updatevon","ext_id","ausstellungsstaat","rt_punkte3", "zgvdoktor_code", "zgvdoktorort", "zgvdoktordatum","mentor","zgvnation","zgvmanation","zgvdoktornation","gsstudientyp_kurzbz","aufnahmegruppe_kurzbz","udf_values"),
	"public.tbl_prestudentstatus"  => array("prestudent_id","status_kurzbz","studiensemester_kurzbz","ausbildungssemester","datum","orgform_kurzbz","insertamum","insertvon","updateamum","updatevon","ext_id","studienplan_id","bestaetigtam","bestaetigtvon","fgm","faktiv", "anmerkung","bewerbung_abgeschicktamum","rt_stufe","statusgrund_id"),
	"public.tbl_raumtyp"  => array("raumtyp_kurzbz","beschreibung","kosten"),
	"public.tbl_reihungstest"  => array("reihungstest_id","studiengang_kz","ort_kurzbz","anmerkung","datum","uhrzeit","updateamum","updatevon","insertamum","insertvon","ext_id","freigeschaltet","max_teilnehmer","oeffentlich","studiensemester_kurzbz","aufnahmegruppe_kurzbz","stufe","anmeldefrist"),
	"public.tbl_rt_ort" => array("rt_id","ort_kurzbz","uid"),
	"public.tbl_rt_person" => array("rt_person_id","person_id","rt_id","studienplan_id","anmeldedatum","teilgenommen","ort_kurzbz","punkte","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_rt_studienplan" => array("reihungstest_id","studienplan_id"),
	"public.tbl_status"  => array("status_kurzbz","beschreibung","anmerkung","ext_id","bezeichnung_mehrsprachig"),
	"public.tbl_status_grund" => array("statusgrund_id","status_kurzbz","aktiv","bezeichnung_mehrsprachig","beschreibung"),
	"public.tbl_semesterwochen"  => array("semester","studiengang_kz","wochen"),
	"public.tbl_service" => array("service_id", "bezeichnung","beschreibung","ext_id","oe_kurzbz","content_id","design_uid","betrieb_uid","operativ_uid"),
	"public.tbl_sprache"  => array("sprache","locale","flagge","index","content","bezeichnung"),
	"public.tbl_standort"  => array("standort_id","adresse_id","kurzbz","bezeichnung","insertvon","insertamum","updatevon","updateamum","ext_id", "firma_id","code"),
	"public.tbl_statistik"  => array("statistik_kurzbz","bezeichnung","url","gruppe","sql","content_id","insertamum","insertvon","updateamum","updatevon","berechtigung_kurzbz","publish","preferences"),
	"public.tbl_student"  => array("student_uid","matrikelnr","prestudent_id","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studentlehrverband"  => array("student_uid","studiensemester_kurzbz","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studiengang"  => array("studiengang_kz","kurzbz","kurzbzlang","typ","bezeichnung","english","farbe","email","telefon","max_semester","max_verband","max_gruppe","erhalter_kz","bescheid","bescheidbgbl1","bescheidbgbl2","bescheidgz","bescheidvom","orgform_kurzbz","titelbescheidvom","aktiv","ext_id","zusatzinfo_html","moodle","sprache","testtool_sprachwahl","studienplaetze","oe_kurzbz","lgartcode","mischform","projektarbeit_note_anzeige", "onlinebewerbung"),
	"public.tbl_studiengangstyp" => array("typ","bezeichnung","beschreibung"),
	"public.tbl_studienjahr"  => array("studienjahr_kurzbz","bezeichnung"),
	"public.tbl_studiensemester"  => array("studiensemester_kurzbz","bezeichnung","start","ende","studienjahr_kurzbz","ext_id","beschreibung","onlinebewerbung"),
	"public.tbl_tag"  => array("tag"),
	"public.tbl_variable"  => array("name","uid","wert"),
	"public.tbl_vorlage"  => array("vorlage_kurzbz","bezeichnung","anmerkung","mimetype","attribute"),
	"public.tbl_vorlagedokument"  => array("vorlagedokument_id","sort","vorlagestudiengang_id","dokument_kurzbz"),
	"public.tbl_vorlagestudiengang"  => array("vorlagestudiengang_id","vorlage_kurzbz","studiengang_kz","version","text","oe_kurzbz","style","berechtigung","anmerkung_vorlagestudiengang","aktiv","sprache","subject","orgform_kurzbz"),
	"testtool.tbl_ablauf"  => array("ablauf_id","gebiet_id","studiengang_kz","reihung","gewicht","semester", "insertamum","insertvon","updateamum", "updatevon","ablauf_vorgaben_id","studienplan_id"),
	"testtool.tbl_ablauf_vorgaben"  => array("ablauf_vorgaben_id","studiengang_kz","sprache","sprachwahl","content_id","insertamum","insertvon","updateamum", "updatevon"),
	"testtool.tbl_antwort"  => array("antwort_id","pruefling_id","vorschlag_id"),
	"testtool.tbl_frage"  => array("frage_id","kategorie_kurzbz","gebiet_id","level","nummer","demo","insertamum","insertvon","updateamum","updatevon","aktiv"),
	"testtool.tbl_gebiet"  => array("gebiet_id","kurzbz","bezeichnung","beschreibung","zeit","multipleresponse","kategorien","maxfragen","zufallfrage","zufallvorschlag","levelgleichverteilung","maxpunkte","insertamum", "insertvon", "updateamum", "updatevon", "level_start","level_sprung_auf","level_sprung_ab","antwortenprozeile","bezeichnung_mehrsprachig"),
	"testtool.tbl_kategorie"  => array("kategorie_kurzbz","gebiet_id"),
	"testtool.tbl_kriterien"  => array("gebiet_id","kategorie_kurzbz","punkte","typ"),
	"testtool.tbl_pruefling"  => array("pruefling_id","prestudent_id","studiengang_kz","idnachweis","registriert","semester"),
	"testtool.tbl_vorschlag"  => array("vorschlag_id","frage_id","nummer","punkte","insertamum","insertvon","updateamum","updatevon","aktiv"),
	"testtool.tbl_pruefling_frage"  => array("prueflingfrage_id","pruefling_id","frage_id","nummer","begintime","endtime"),
	"testtool.tbl_frage_sprache"  => array("frage_id","sprache","text","bild","audio","insertamum","insertvon","updateamum","updatevon"),
	"testtool.tbl_vorschlag_sprache"  => array("vorschlag_id","sprache","text","bild","audio","insertamum","insertvon","updateamum","updatevon"),
	"system.tbl_app" => array("app"),
	"system.tbl_appdaten" => array("appdaten_id","uid","app","appversion","version","bezeichnung","daten","freigabe","insertamum","insertvon","updateamum","updatevon"),
	"system.tbl_cronjob"  => array("cronjob_id","server_kurzbz","titel","beschreibung","file","last_execute","aktiv","running","jahr","monat","tag","wochentag","stunde","minute","standalone","reihenfolge","updateamum", "updatevon","insertamum","insertvon","variablen"),
	"system.tbl_benutzerrolle"  => array("benutzerberechtigung_id","rolle_kurzbz","berechtigung_kurzbz","uid","funktion_kurzbz","oe_kurzbz","art","studiensemester_kurzbz","start","ende","negativ","updateamum", "updatevon","insertamum","insertvon","kostenstelle_id","anmerkung"),
	"system.tbl_berechtigung"  => array("berechtigung_kurzbz","beschreibung"),
	"system.tbl_extensions" => array("extension_id","name","version","description","license","url","core_version","dependencies","enabled"),
	"system.tbl_log" => array("log_id","person_id","zeitpunkt","app","oe_kurzbz","logtype_kurzbz","logdata","insertvon","taetigkeit_kurzbz"),
	"system.tbl_logtype" => array("logtype_kurzbz", "data_schema"),
	"system.tbl_filters" => array("filter_id","app","dataset_name","filter_kurzbz","person_id","description","sort","default_filter","filter","oe_kurzbz"),
	"system.tbl_phrase" => array("phrase_id","app","phrase","insertamum","insertvon"),
	"system.tbl_phrasentext" => array("phrasentext_id","phrase_id","sprache","orgeinheit_kurzbz","orgform_kurzbz","text","description","insertamum","insertvon"),
	"system.tbl_rolle"  => array("rolle_kurzbz","beschreibung"),
	"system.tbl_rolleberechtigung"  => array("berechtigung_kurzbz","rolle_kurzbz","art"),
	"system.tbl_verarbeitungstaetigkeit" => array("taetigkeit_kurzbz", "bezeichnung", "bezeichnung_mehrsprachig","aktiv"),
	"system.tbl_webservicelog"  => array("webservicelog_id","webservicetyp_kurzbz","request_id","beschreibung","request_data","execute_time","execute_user"),
	"system.tbl_webservicerecht" => array("webservicerecht_id","berechtigung_kurzbz","methode","attribut","insertamum","insertvon","updateamum","updatevon","klasse"),
	"system.tbl_webservicetyp"  => array("webservicetyp_kurzbz","beschreibung"),
	"system.tbl_server"  => array("server_kurzbz","beschreibung"),
	"system.tbl_udf"  => array("schema", "table", "jsons"),
	"wawi.tbl_betriebsmittelperson"  => array("betriebsmittelperson_id","betriebsmittel_id","person_id", "anmerkung", "kaution", "ausgegebenam", "retouram","insertamum", "insertvon","updateamum", "updatevon","ext_id","uid"),
	"wawi.tbl_betriebsmittel"  => array("betriebsmittel_id","betriebsmitteltyp","oe_kurzbz", "ort_kurzbz", "beschreibung", "nummer", "hersteller","seriennummer", "bestellung_id","bestelldetail_id", "afa","verwendung","anmerkung","reservieren","updateamum","updatevon","insertamum","insertvon","ext_id","inventarnummer","leasing_bis","inventuramum","inventurvon","anschaffungsdatum","anschaffungswert","hoehe","breite","tiefe","nummer2","verplanen"),
	"wawi.tbl_betriebsmittel_betriebsmittelstatus"  => array("betriebsmittelbetriebsmittelstatus_id","betriebsmittel_id","betriebsmittelstatus_kurzbz", "datum", "updateamum", "updatevon", "insertamum", "insertvon","anmerkung"),
	"wawi.tbl_betriebsmittelstatus"  => array("betriebsmittelstatus_kurzbz","beschreibung"),
	"wawi.tbl_betriebsmitteltyp"  => array("betriebsmitteltyp","beschreibung","anzahl","kaution","typ_code","mastershapename"),
	"wawi.tbl_budget"  => array("geschaeftsjahr_kurzbz","kostenstelle_id","budget"),
	"wawi.tbl_zahlungstyp"  => array("zahlungstyp_kurzbz","bezeichnung"),
	"wawi.tbl_konto"  => array("konto_id","kontonr","beschreibung","kurzbz","aktiv","person_id","insertamum","insertvon","updateamum","updatevon","ext_id","person_id"),
	"wawi.tbl_konto_kostenstelle"  => array("konto_id","kostenstelle_id","insertamum","insertvon"),
	"wawi.tbl_kostenstelle"  => array("kostenstelle_id","oe_kurzbz","bezeichnung","kurzbz","aktiv","insertamum","insertvon","updateamum","updatevon","ext_id","kostenstelle_nr","deaktiviertvon","deaktiviertamum"),
	"wawi.tbl_bestellungtag"  => array("tag","bestellung_id","insertamum","insertvon"),
	"wawi.tbl_bestelldetailtag"  => array("tag","bestelldetail_id","insertamum","insertvon"),
	"wawi.tbl_projekt_bestellung"  => array("projekt_kurzbz","bestellung_id","anteil"),
	"wawi.tbl_bestellung"  => array("bestellung_id","besteller_uid","kostenstelle_id","konto_id","firma_id","lieferadresse","rechnungsadresse","freigegeben","bestell_nr","titel","bemerkung","liefertermin","updateamum","updatevon","insertamum","insertvon","ext_id","zahlungstyp_kurzbz","zuordnung_uid","zuordnung_raum","zuordnung","auftragsbestaetigung","auslagenersatz","iban","wird_geleast","nicht_bestellen","empfehlung_leasing"),
	"wawi.tbl_bestelldetail"  => array("bestelldetail_id","bestellung_id","position","menge","verpackungseinheit","beschreibung","artikelnummer","preisprove","mwst","erhalten","sort","text","updateamum","updatevon","insertamum","insertvon"),
	"wawi.tbl_bestellung_bestellstatus"  => array("bestellung_bestellstatus_id","bestellung_id","bestellstatus_kurzbz","uid","oe_kurzbz","datum","insertamum","insertvon","updateamum","updatevon"),
	"wawi.tbl_bestellstatus"  => array("bestellstatus_kurzbz","beschreibung"),
	"wawi.tbl_buchung"  => array("buchung_id","konto_id","kostenstelle_id","buchungstyp_kurzbz","buchungsdatum","buchungstext","betrag","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"wawi.tbl_buchungstyp"  => array("buchungstyp_kurzbz","bezeichnung"),
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
		$sql_attr.='"'.$attr.'",';
	$sql_attr=substr($sql_attr, 0, -1);

	if (!@$db->db_query('SELECT '.$sql_attr.' FROM '.$tabs[$i].' LIMIT 1;'))
		echo '<BR><strong>'.$tabs[$i].': '.$db->db_last_error().' </strong><BR>';
	else
		echo $tabs[$i].': OK - ';
	flush();
	$i++;
}

echo '<H2>Gegenpruefung!</H2>';
$error=false;
$sql_query="SELECT schemaname,tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema' AND schemaname != 'sync' AND schemaname != 'addon' AND schemaname != 'reports';";
if (!$result=@$db->db_query($sql_query))
		echo '<BR><strong>'.$db->db_last_error().' </strong><BR>';
	else
		while ($row=$db->db_fetch_object($result))
		{
			$fulltablename=$row->schemaname.'.'.$row->tablename;
			if (!isset($tabellen[$fulltablename]))
			{
				echo 'Tabelle '.$fulltablename.' existiert in der DB, aber nicht in diesem Skript!<BR>';
				$error=true;
			}
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
						{
							echo 'Attribut '.$fulltablename.'.<strong>'.$fieldnameDB.'</strong> existiert in der DB, aber nicht in diesem Skript!<BR>';
							$error=true;
						}
					}
		}
if($error==false)
	echo '<br>Gegenpruefung fehlerfrei';
?>
