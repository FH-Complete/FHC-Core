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

if(!$result = @$db->db_query("SELECT 1 FROM public.vw_msg_vars WHERE person_id=-1 LIMIT 1"))
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

// CREATE OR REPLACE VIEW public.vw_msg_vars_user and grants privileges
if(!$result = @$db->db_query("SELECT 1 FROM public.vw_msg_vars_user LIMIT 1"))
{
	$qry = '
		CREATE OR REPLACE VIEW public.vw_msg_vars_user AS (
			SELECT DISTINCT ON
				(b.uid) b.uid AS "my_uid",
				p.vorname AS "my_vorname",
				p.nachname AS "my_nachname",
				COALESCE(b.alias, b.uid) AS "my_alias",
				ma.telefonklappe AS "my_durchwahl"
			FROM public.tbl_person p
			JOIN public.tbl_benutzer b USING (person_id)
			JOIN public.tbl_mitarbeiter ma ON ma.mitarbeiter_uid = b.uid
			WHERE ma.personalnummer > 0
		);';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars_user: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.vw_msg_vars_user view created';

	$qry = 'GRANT SELECT ON TABLE public.vw_msg_vars_user TO web;';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars_user: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on public.vw_msg_vars_user';

	$qry = 'GRANT SELECT ON TABLE public.vw_msg_vars_user TO vilesci;';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars_user: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on public.vw_msg_vars_user';
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

// change Datatype of lehre.tbl_note.bezeichnung from varchar(32) to varchar(64)
if($result = $db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='lehre' AND TABLE_NAME='tbl_note' AND COLUMN_NAME = 'bezeichnung' AND character_maximum_length < 64"))
{
	if($db->db_num_rows($result)>0)
	{
		$qry = "
		ALTER TABLE lehre.tbl_note ALTER COLUMN bezeichnung TYPE varchar(64);
		";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_note '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte bezeichnung in lehre.tbl_note von kleiner varchar(64) auf varchar(64) geändert<br>';
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

// Note "intern angerechnet" hinzufügen
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_note WHERE anmerkung = 'iar' AND (bezeichnung = 'intern angerechnet' OR bezeichnung = 'Intern angerechnet');"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "
			INSERT INTO
				lehre.tbl_note(note, bezeichnung, anmerkung, farbe, positiv, notenwert, aktiv, lehre, offiziell, bezeichnung_mehrsprachig, lkt_ueberschreibbar)
			VALUES(
				(SELECT max(note)+1 FROM lehre.tbl_note),'intern angerechnet', 'iar', NULL, TRUE, NULL, TRUE, FALSE, FALSE, '{\"intern angerechnet\",\"internally credited\"}', FALSE
			);
		";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_note: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_note: Note intern angerechnet hinzugefuegt!<br>';
	}
}

// Note "nicht zugelassen" hinzufügen
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_note WHERE anmerkung = 'nz' AND (bezeichnung = 'nicht zugelassen' OR bezeichnung = 'Nicht zugelassen');"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "
			INSERT INTO
				lehre.tbl_note(note, bezeichnung, anmerkung, farbe, positiv, notenwert, aktiv, lehre, offiziell, bezeichnung_mehrsprachig, lkt_ueberschreibbar)
			VALUES(
				(SELECT max(note)+1 FROM lehre.tbl_note), 'nicht zugelassen', 'nz', NULL, TRUE, NULL, TRUE, FALSE, FALSE, '{\"nicht zugelassen\",\"not admitted\"}', FALSE
			);
		";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_note: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_note: Note nicht zugelassen hinzugefuegt!<br>';
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

// ADD COLUMN studiengang_kz to testtool.vw_auswertung_ablauf
if(!$result = @$db->db_query("SELECT studiengang_kz FROM testtool.vw_auswertung_ablauf LIMIT 1"))
{
	// CREATE OR REPLACE VIEW testtool.vw_auswertung_ablauf and grants privileges
	$qry = '
		CREATE OR REPLACE VIEW testtool.vw_auswertung_ablauf AS (
			SELECT
				tbl_gebiet.gebiet_id,
				tbl_gebiet.bezeichnung AS gebiet,
				tbl_ablauf.reihung,
				tbl_gebiet.maxpunkte,
				tbl_pruefling.pruefling_id,
				tbl_pruefling.prestudent_id,
				tbl_person.vorname,
				tbl_person.nachname,
				tbl_person.gebdatum,
				tbl_person.geschlecht,
				tbl_pruefling.semester,
				upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
				tbl_studiengang.bezeichnung AS stg_bez,
				tbl_pruefling.registriert,
				tbl_pruefling.idnachweis,
				( SELECT sum(tbl_vorschlag.punkte) AS sum
					   FROM testtool.tbl_vorschlag
						 JOIN testtool.tbl_antwort USING (vorschlag_id)
						 JOIN testtool.tbl_frage USING (frage_id)
					  WHERE tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
				) AS punkte,
				tbl_rt_person.rt_id AS reihungstest_id,
				tbl_ablauf.gewicht,
				tbl_studiengang.studiengang_kz
			FROM
				testtool.tbl_pruefling
			 JOIN testtool.tbl_ablauf ON tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz
			 JOIN testtool.tbl_gebiet USING (gebiet_id)
			 JOIN public.tbl_prestudent USING (prestudent_id)
			 JOIN public.tbl_person USING (person_id)
			 JOIN public.tbl_rt_person USING (person_id)
			 JOIN lehre.tbl_studienplan ON tbl_studienplan.studienplan_id = tbl_rt_person.studienplan_id
			 JOIN lehre.tbl_studienordnung ON tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id
			 JOIN public.tbl_studiengang ON tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz
			WHERE NOT (tbl_ablauf.gebiet_id IN
				(
				SELECT tbl_kategorie.gebiet_id
				FROM testtool.tbl_kategorie
				)
			) AND tbl_studienordnung.studiengang_kz = tbl_pruefling.studiengang_kz
           )';

	if(!$db->db_query($qry))
		echo '<strong>testtool.vw_auswertung_ablauf: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>testtool.vw_auswertung_ablauf view created';
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

// ADD COLUMN geschaeftsjahrvon and geschaeftsjahrbis in wawi.tbl_kostenstelle
if(!$result = @$db->db_query("SELECT geschaeftsjahrvon FROM wawi.tbl_kostenstelle LIMIT 1;"))
{
	$qry = "ALTER TABLE wawi.tbl_kostenstelle ADD COLUMN geschaeftsjahrvon varchar(32);
			ALTER TABLE wawi.tbl_kostenstelle ADD COLUMN geschaeftsjahrbis varchar(32);
			ALTER TABLE wawi.tbl_kostenstelle ADD CONSTRAINT fk_tbl_geschaeftsjahr_geschaeftsjahrvon FOREIGN KEY (geschaeftsjahrvon) REFERENCES public.tbl_geschaeftsjahr (geschaeftsjahr_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE wawi.tbl_kostenstelle ADD CONSTRAINT fk_tbl_geschaeftsjahr_geschaeftsjahrbis FOREIGN KEY (geschaeftsjahrbis) REFERENCES public.tbl_geschaeftsjahr (geschaeftsjahr_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			";

	if(!$db->db_query($qry))
		echo '<strong>wawi.tbl_kostenstelle: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>wawi.tbl_kostenstelle: Spalten geschaeftsjahrvon, geschaeftsjahrbis hinzugefuegt!<br>';
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

// Adds primary key to system.tbl_extensions using column extension_id
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'pk_extensions_extension_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "ALTER TABLE system.tbl_extensions ADD CONSTRAINT pk_extensions_extension_id PRIMARY KEY (extension_id);";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_extensions '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>system.tbl_extensions: added primary key on column extension_id';
	}
}

// UNIQUE INDEX uidx_extensions_name_version
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'uidx_extensions_name_version'"))
{
	if ($db->db_num_rows($result) == 1)
	{
		$qry = 'DROP INDEX system.uidx_extensions_name_version';
		if (!$db->db_query($qry))
			echo '<strong>uidx_extensions_name_version '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Dropped unique uidx_extensions_name_version';
	}
}

// stud_selfservice boolean fuer public.tbl_vorlage
if(!@$db->db_query("SELECT server_kurzbz FROM system.tbl_extensions LIMIT 1"))
{
	$qry = "ALTER TABLE system.tbl_extensions ADD COLUMN server_kurzbz varchar(64);
			ALTER TABLE system.tbl_extensions ADD CONSTRAINT fk_extensios_server_kurzbz FOREIGN KEY (server_kurzbz) REFERENCES system.tbl_server(server_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;";

	if(!$db->db_query($qry))
		echo '<strong>App: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte server_kurzbz in system.tbl_extensions hinzugefügt';
}


// UNIQUE INDEX uidx_extensions_name_version_server
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'uidx_extensions_name_version_server'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'CREATE UNIQUE INDEX uidx_extensions_name_version_server ON system.tbl_extensions USING btree (name, version, server_kurzbz);';
		if (!$db->db_query($qry))
			echo '<strong>uidx_extensions_name_version_server '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created unique uidx_extensions_name_version_server';
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

// Tabellen fuer Person Log
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

// Add index to system.tbl_log
if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_tbl_log_person_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_log_person_id ON system.tbl_log USING btree (person_id)";

		if (! $db->db_query($qry))
			echo '<strong>Indizes: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index fuer system.tbl_log hinzugefuegt';
	}
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
if (!$result = @$db->db_query("SELECT taetigkeit_kurzbz FROM system.tbl_log LIMIT 1"))
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

// App 'lehrauftrag' hinzufügen
if($result = $db->db_query("SELECT 1 FROM system.tbl_app WHERE app='lehrauftrag'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "INSERT INTO system.tbl_app(app) VALUES('lehrauftrag');";

		if(!$db->db_query($qry))
			echo '<strong>App: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue App lehrauftrag in system.tbl_app hinzugefügt';
	}
}

// Archiv boolean fuer public.tbl_akte
if(!@$db->db_query("SELECT archiv FROM public.tbl_akte LIMIT 1"))
{
	// Defaultwerte und Update werden hier nacheinander durchgefuehrt da dies
	// schneller ist als ein ALTER TABLE mit inkludiertem Defaultwert
	$qry = "ALTER TABLE public.tbl_akte ADD COLUMN archiv boolean;
			UPDATE public.tbl_akte SET archiv=true WHERE dokument_kurzbz='Zeugnis';
			UPDATE public.tbl_akte SET archiv=false WHERE dokument_kurzbz<>'Zeugnis';
			ALTER TABLE public.tbl_akte ALTER COLUMN archiv SET DEFAULT false;
			ALTER TABLE public.tbl_akte ALTER COLUMN archiv SET NOT NULL;
			COMMENT ON COLUMN public.tbl_akte.archiv IS 'Is the document part of the archive';";
	if(!$db->db_query($qry))
		echo '<strong>tbl_akte.archiv: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte archiv in public.tbl_akte hinzugefügt';
}

// signiert boolean fuer public.tbl_akte
if(!@$db->db_query("SELECT signiert FROM public.tbl_akte LIMIT 1"))
{
	// Defaultwerte und Update werden hier nacheinander durchgefuehrt da dies
	// schneller ist als ein ALTER TABLE mit inkludiertem Defaultwert
	$qry = "ALTER TABLE public.tbl_akte ADD COLUMN signiert boolean;
			UPDATE public.tbl_akte SET signiert = false;
			ALTER TABLE public.tbl_akte ALTER COLUMN signiert SET DEFAULT false;
			ALTER TABLE public.tbl_akte ALTER COLUMN signiert SET NOT NULL;
			COMMENT ON COLUMN public.tbl_akte.signiert IS 'Is the document digitally signed'";

	if(!$db->db_query($qry))
		echo '<strong>App: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte signiert in public.tbl_akte hinzugefügt';
}

// stud_selfservice boolean fuer public.tbl_akte
if(!@$db->db_query("SELECT stud_selfservice FROM public.tbl_akte LIMIT 1"))
{
	// Defaultwerte und Update werden hier nacheinander durchgefuehrt da dies
	// schneller ist als ein ALTER TABLE mit inkludiertem Defaultwert
	$qry = "ALTER TABLE public.tbl_akte ADD COLUMN stud_selfservice boolean;
			UPDATE public.tbl_akte SET stud_selfservice = false;
			ALTER TABLE public.tbl_akte ALTER COLUMN stud_selfservice SET DEFAULT false;
			ALTER TABLE public.tbl_akte ALTER COLUMN stud_selfservice SET NOT NULL;
			COMMENT ON COLUMN public.tbl_akte.stud_selfservice IS 'Is the document downloadable for students'";

	if(!$db->db_query($qry))
		echo '<strong>App: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte stud_selfservice in public.tbl_akte hinzugefügt';
}

// Berechtigung fuer Vorlagen setzen die vormals direkt in PDFExport.php geprueft wurden
function AddBerechtigungVorlage($berechtigung_arr, $vorlage_arr)
{
	global $db;

	$berechtigung = '{';
	foreach($berechtigung_arr as $item)
	{
		$berechtigung .= '"'.$db->db_escape($item).'",';
	}
	$berechtigung = mb_substr($berechtigung, 0, -1).'}';

	foreach($vorlage_arr as $vorlage)
	{
		$qry = "SELECT 1 FROM public.tbl_vorlagestudiengang
			WHERE berechtigung is null AND vorlage_kurzbz=".$db->db_add_param($vorlage);

		$result = $db->db_query($qry);
		if($db->db_num_rows($result)>0)
		{
			$qry = "UPDATE public.tbl_vorlagestudiengang SET berechtigung='".$berechtigung."'
				WHERE berechtigung is null AND vorlage_kurzbz=".$db->db_add_param($vorlage);

			if(!$db->db_query($qry))
				echo '<strong>Vorlage Berechtigung: '.$db->db_last_error().'</strong><br>';
			else
				echo '<br>Berechtigung '.$berechtigung.' fuer Vorlage '.$vorlage.' gesetzt';
		}
	}
}

AddBerechtigungVorlage(array('admin','assistenz'),array('Lehrveranstaltungszeugnis','Zertifikat','Diplomurkunde',
	'Diplomzeugnis','Bescheid', 'BescheidEng','Bakkurkunde','BakkurkundeEng','Bakkzeugnis',
	'PrProtokollBakk','PrProtokollDipl','Lehrauftrag','DiplomurkundeEng','Zeugnis','ZeugnisEng','StudienerfolgEng',
	'Sammelzeugnis','PrProtDiplEng','PrProtBakkEng','BakkzeugnisEng','DiplomzeugnisEng','statusbericht',
	'DiplSupplement','Zutrittskarte','Projektbeschr','Ausbildungsver','AusbildStatus','PrProtBA','PrProtMA',
	'PrProtBAEng','PrProtMAEng','Studienordnung','Erfolgsnachweis','ErfolgsnwHead','Studienblatt','LV_Informationen',
	'LVZeugnis','AnwListBarcode','Honorarvertrag','AusbVerEng','AusbVerEngHead','Zeugnis','ZeugnisNeu','ZeugnisEngNeu',
	'ErfolgsnachweisE','ErfolgsnwHeadE','Magisterurkunde','Masterurkunde','Defensiourkunde','Magisterzeugnis',
	'Laufzettel','StudienblattEng','Zahlung1','Terminliste','Studienbuchblatt','Veranstaltungen',
	'Inskription','Studienerfolg','OutgoingLearning','OutgoingChangeL','LearningAgree','Zahlung','DichiaSost'
	));
AddBerechtigungVorlage(array('lehre/lvplan'), array('Ressource'));
AddBerechtigungVorlage(array('wawi/inventar','assistenz','basis/betriebsmittel'), array('Uebernahme'));
AddBerechtigungVorlage(array('wawi/bestellung'), array('Bestellung'));
AddBerechtigungVorlage(array('admin','mitarbeiter','assistenz'), array('AccountInfo'));

// archivierbar boolean fuer public.tbl_vorlage
if(!@$db->db_query("SELECT archivierbar FROM public.tbl_vorlage LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_vorlage ADD COLUMN archivierbar boolean DEFAULT false;
			UPDATE public.tbl_vorlage SET archivierbar=true
			WHERE vorlage_kurzbz in('DiplSupplement','Zeugnis','ZeugnisEng', 'Bescheid',' BescheidEng');
			COMMENT ON COLUMN public.tbl_vorlage.archivierbar IS 'Can this document be archived'";

	if(!$db->db_query($qry))
		echo '<strong>App: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte archivierbar in public.tbl_vorlage hinzugefügt';
}

// signierbar boolean fuer public.tbl_vorlage
if(!@$db->db_query("SELECT signierbar FROM public.tbl_vorlage LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_vorlage ADD COLUMN signierbar boolean DEFAULT false;
			COMMENT ON COLUMN public.tbl_vorlage.signierbar IS 'Can this document be digitally signed'";

	if(!$db->db_query($qry))
		echo '<strong>App: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte signierbar in public.tbl_vorlage hinzugefügt';
}

// stud_selfservice boolean fuer public.tbl_vorlage
if(!@$db->db_query("SELECT stud_selfservice FROM public.tbl_vorlage LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_vorlage ADD COLUMN stud_selfservice boolean DEFAULT false;
			COMMENT ON COLUMN public.tbl_vorlage.stud_selfservice IS 'Can this documents be downloaded if archived'";

	if(!$db->db_query($qry))
		echo '<strong>App: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte stud_selfserice in public.tbl_vorlage hinzugefügt';
}

// dokument_kurzbz fuer public.tbl_vorlage
if(!@$db->db_query("SELECT dokument_kurzbz FROM public.tbl_vorlage LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_vorlage ADD COLUMN dokument_kurzbz varchar(8);
			ALTER TABLE public.tbl_vorlage ADD CONSTRAINT fk_vorlage_dokument FOREIGN KEY (dokument_kurzbz) REFERENCES public.tbl_dokument (dokument_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			COMMENT ON COLUMN public.tbl_vorlage.dokument_kurzbz IS 'Connects a Template with the corresponding Dokument'";

	if(!$db->db_query($qry))
		echo '<strong>App: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte dokument_kurzbz in public.tbl_vorlage hinzugefügt';
}

// Remove NOT NULL constraint on vorlaufszeit on public.tbl_ampel
if($result = @$db->db_query("SELECT is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_ampel' AND COLUMN_NAME = 'vorlaufzeit' AND is_nullable = 'NO'"))
{
	if($db->db_num_rows($result) > 0)
	{
		$qry = "ALTER TABLE public.tbl_ampel ALTER COLUMN vorlaufzeit DROP NOT NULL;";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_ampel '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Removed NOT NULL constraint on "vorlaufszeit" from public.tbl_ampel<br>';
	}
}

// Remove NOT NULL constraint on verfallszeit on public.tbl_ampel
if($result = @$db->db_query("SELECT is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_ampel' AND COLUMN_NAME = 'verfallszeit' AND is_nullable = 'NO'"))
{
	if($db->db_num_rows($result) > 0 )
	{
		$qry = "ALTER TABLE public.tbl_ampel ALTER COLUMN verfallszeit DROP NOT NULL;";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_ampel '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Removed NOT NULL constraint on "verfallszeit" from public.tbl_ampel<br>';
	}
}

// Tabelle person_lock hinzufügen
if (!$result = @$db->db_query("SELECT 1 FROM system.tbl_person_lock LIMIT 1"))
{
	$qry = "CREATE TABLE system.tbl_person_lock
			(
				lock_id bigint NOT NULL,
				person_id integer NOT NULL,
				uid varchar(32) NOT NULL,
				zeitpunkt timestamp NOT NULL DEFAULT now(),
				app varchar(32)
			);

			ALTER TABLE system.tbl_person_lock ADD CONSTRAINT pk_lock PRIMARY KEY (lock_id);

			CREATE SEQUENCE system.tbl_person_lock_lock_id_seq
			 INCREMENT BY 1
			 NO MAXVALUE
			 NO MINVALUE
			 CACHE 1;
			ALTER TABLE system.tbl_person_lock ALTER COLUMN lock_id SET DEFAULT nextval('system.tbl_person_lock_lock_id_seq');

			GRANT SELECT, INSERT, DELETE ON system.tbl_person_lock TO vilesci;
			GRANT SELECT, INSERT, DELETE ON system.tbl_person_lock TO web;
			GRANT SELECT, UPDATE ON system.tbl_person_lock_lock_id_seq TO vilesci;
			GRANT SELECT, UPDATE ON system.tbl_person_lock_lock_id_seq TO web;

			ALTER TABLE system.tbl_person_lock ADD CONSTRAINT fk_lock_person_id FOREIGN KEY (person_id) REFERENCES public.tbl_person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_person_lock ADD CONSTRAINT fk_lock_uid FOREIGN KEY (uid) REFERENCES public.tbl_benutzer(uid) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_person_lock ADD CONSTRAINT fk_lock_app FOREIGN KEY (app) REFERENCES system.tbl_app(app) ON UPDATE CASCADE ON DELETE RESTRICT;";
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_person_lock '.$db->db_last_error().'</strong><br>';
	else
		echo ' system.tbl_person_lock hinzugefügt<br>';
}

// Spalte bezeichnung_mehrsprachig in public.tbl_kontakttyp
if(!$result = @$db->db_query("SELECT bezeichnung_mehrsprachig FROM public.tbl_kontakttyp LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_kontakttyp ADD COLUMN bezeichnung_mehrsprachig varchar(128)[];";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_kontakttyp '.$db->db_last_error().'</strong><br>';
	else
		echo 'public.tbl_kontakttyp: Spalte bezeichnung_mehrsprachig hinzugefuegt!<br>';

	// Bezeichnung_mehrsprachig aus existierender Bezeichnung vorausfuellen. Ein Eintrag fuer jede Sprache mit Content aktiv.
	$qry_help = "SELECT index FROM public.tbl_sprache WHERE content=TRUE;";
	if(!$result = $db->db_query($qry_help))
		echo '<strong>tbl_kontakttyp bezeichnung_mehrsprachig: Fehler beim ermitteln der Sprachen: '.$db->db_last_error().'</strong>';
	else
	{
		$qry='';
		while($row = $db->db_fetch_object($result))
			$qry.= "UPDATE public.tbl_kontakttyp set bezeichnung_mehrsprachig[".$row->index."] = beschreibung;";

		if(!$db->db_query($qry))
			echo '<strong>Setzen der bezeichnung_mehrsprachig fehlgeschlagen: '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_kontakttyp: bezeichnung_mehrprachig automatisch aus existierender Beschreibung uebernommen<br>';
	}
}

// INSERT Berechtigungen fuer web User erteilen fuer tbl_msg_status
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_msg_status' AND table_schema='public' AND grantee='web' AND privilege_type='INSERT'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT SELECT, INSERT ON public.tbl_msg_status TO web;";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_msg_status Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo 'INSERT Rechte fuer public.tbl_msg_status fuer web user gesetzt ';
	}
}

// UPDATE Berechtigungen fuer web User erteilen fuer tbl_msg_status
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_msg_status' AND table_schema='public' AND grantee='web' AND privilege_type='UPDATE'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT UPDATE ON public.tbl_msg_status TO web;";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_msg_status Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo 'UPDATE Rechte fuer public.tbl_msg_status fuer web user gesetzt ';
	}
}

// UPDATE Berechtigungen fuer vilesci User erteilen fuer tbl_person_lock
if ($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_person_lock' AND table_schema='system' AND grantee='vilesci' AND privilege_type='UPDATE'"))
{
	if ($db->db_num_rows($result) === 0)
	{
		$qry = "GRANT UPDATE ON system.tbl_person_lock TO vilesci;";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_person_lock Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br> UPDATE Recht fuer system.tbl_person_lock fuer vilesci user gesetzt <br>';
	}
}


// SELECT Berechtigung fuer web User erteilen fuer tbl_mobilitaet
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_mobilitaet' AND table_schema='bis' AND grantee='web' AND privilege_type='SELECT'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT SELECT ON bis.tbl_mobilitaet TO web;";

		if(!$db->db_query($qry))
			echo '<strong>bis.tbl_mobilitaet Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>SELECT Recht fuer bis.tbl_mobilitaet fuer web user gesetzt <br>';
	}
}

/**
 * Kommentare fuer Datenbanktabellen
 */
if($result = $db->db_query("SELECT obj_description('public.ci_apikey'::regclass) as comment"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->comment == '')
		{
			$qry = "
			COMMENT ON TABLE public.ci_apikey IS 'API Keys';
			COMMENT ON TABLE public.tbl_adresse IS 'Person and Company Addresses';
			COMMENT ON TABLE public.tbl_akte IS 'Documents of Persons';
			COMMENT ON TABLE public.tbl_ampel IS 'Notification System';
			COMMENT ON TABLE public.tbl_ampel_benutzer_bestaetigt IS 'Accepted Notifications';
			COMMENT ON TABLE public.tbl_aufnahmeschluessel IS 'DEPRECATED';
			COMMENT ON TABLE public.tbl_aufnahmetermin IS 'DEPRECATED';
			COMMENT ON TABLE public.tbl_aufnahmetermintyp IS 'DEPRECATED';
			COMMENT ON TABLE public.tbl_aufmerksamdurch IS 'Key-Table of Brand Awareness';
			COMMENT ON TABLE public.tbl_bankverbindung IS 'Bank Data of Persons';
			COMMENT ON TABLE public.tbl_benutzer IS 'List of Accounts';
			COMMENT ON TABLE public.tbl_benutzerfunktion IS 'Functions of Persons';
			COMMENT ON TABLE public.tbl_benutzergruppe IS 'Connects Users and Groups';
			COMMENT ON TABLE public.tbl_bewerbungstermine IS 'Application Dates';
			COMMENT ON TABLE public.tbl_buchungstyp IS 'Key-Table of Payment Types';
			COMMENT ON TABLE public.tbl_dokument IS 'Key-Table of Documents';
			COMMENT ON TABLE public.tbl_dokumentprestudent IS 'Accepted Documents of Degree Program';
			COMMENT ON TABLE public.tbl_dokumentstudiengang IS 'Connection Table of Degree Programs and needed Documents';
			COMMENT ON TABLE public.tbl_erhalter IS 'Company Caretaker Information';
			COMMENT ON TABLE public.tbl_fachbereich IS 'Institute, Department';
			COMMENT ON TABLE public.tbl_filter IS 'Predefined Dropdowns for Reports';
			COMMENT ON TABLE public.tbl_firma IS 'Universities, Suppliers, Companies';
			COMMENT ON TABLE public.tbl_firma_mobilitaetsprogramm IS 'Mobility programes of Universities';
			COMMENT ON TABLE public.tbl_firma_organisationseinheit IS 'Connects Companys with Organisation Units';
			COMMENT ON TABLE public.tbl_firmatag IS 'Tags for Companys';
			COMMENT ON TABLE public.tbl_firmentyp IS 'Types of Companys';
			COMMENT ON TABLE public.tbl_fotostatus IS 'Key-Table of Picture Upload Status';
			COMMENT ON TABLE public.tbl_funktion IS 'Key-Table of User Functions';
			COMMENT ON TABLE public.tbl_geschaeftsjahr IS 'Finacial Year';
			COMMENT ON TABLE public.tbl_gruppe IS 'Study Groups, Mail Groups';
			COMMENT ON TABLE public.tbl_kontakt IS 'Contacts of Persons';
			COMMENT ON TABLE public.tbl_kontaktmedium IS 'PreInteressenten-Kontakttypen';
			COMMENT ON TABLE public.tbl_kontakttyp IS 'Key-Table of Contact Types';
			COMMENT ON TABLE public.tbl_konto IS 'Student Payments';
			COMMENT ON TABLE public.tbl_lehrverband IS 'List of Groups';
			COMMENT ON TABLE public.tbl_log IS 'Logging and Undo';
			COMMENT ON TABLE public.tbl_mitarbeiter IS 'Employee Data';
			COMMENT ON TABLE public.tbl_msg_attachment IS 'Messages Attachments';
			COMMENT ON TABLE public.tbl_msg_message IS 'Messages';
			COMMENT ON TABLE public.tbl_msg_recipient IS 'Message Recipients';
			COMMENT ON TABLE public.tbl_msg_status IS 'Message Status';
			COMMENT ON TABLE public.tbl_notiz IS 'Notes';
			COMMENT ON TABLE public.tbl_notiz_dokument IS 'Documents assigned to Notes';
			COMMENT ON TABLE public.tbl_notizzuordnung IS 'Connects Notes with Persons, Courses, …';
			COMMENT ON TABLE public.tbl_organisationseinheit IS 'Organisation Units';
			COMMENT ON TABLE public.tbl_organisationseinheittyp IS 'Key-Table of Types of Organisation Units';
			COMMENT ON TABLE public.tbl_ort IS 'Teaching Rooms, Offices';
			COMMENT ON TABLE public.tbl_ortraumtyp IS 'Connection of Rooms and Room Types';
			COMMENT ON TABLE public.tbl_person IS 'List of all Persons';
			COMMENT ON TABLE public.tbl_person_fotostatus IS 'Connects Picture Upload States with Persons';
			COMMENT ON TABLE public.tbl_personfunktionstandort IS 'Contact Persons of a Company';
			COMMENT ON TABLE public.tbl_preincoming IS 'Incoming Registration';
			COMMENT ON TABLE public.tbl_preincoming_lehrveranstaltung IS 'Incoming Courses';
			COMMENT ON TABLE public.tbl_preinteressent IS 'DEPRECATED';
			COMMENT ON TABLE public.tbl_preinteressentstudiengang IS 'DEPRECATED';
			COMMENT ON TABLE public.tbl_preoutgoing IS 'Outgoing Data';
			COMMENT ON TABLE public.tbl_preoutgoing_firma IS 'Outgoing University';
			COMMENT ON TABLE public.tbl_preoutgoing_lehrveranstaltung IS 'Visited Courses of Outgoings';
			COMMENT ON TABLE public.tbl_preoutgoing_preoutgoing_status IS 'Ougoing Status';
			COMMENT ON TABLE public.tbl_preoutgoing_status IS 'Key-Table of Outgoing Status';
			COMMENT ON TABLE public.tbl_prestudent IS 'Relation Person-DegreeProgram';
			COMMENT ON TABLE public.tbl_prestudentstatus IS 'Student History';
			COMMENT ON TABLE public.tbl_raumtyp IS 'Room Types';
			COMMENT ON TABLE public.tbl_reihungstest IS 'Placement Tests';
			COMMENT ON TABLE public.tbl_rt_ort IS 'Connection Room – Placementtest';
			COMMENT ON TABLE public.tbl_rt_person IS 'Connection Person – Placementtest';
			COMMENT ON TABLE public.tbl_rt_studienplan IS 'Connection StudyPlan – Placementtest';
			COMMENT ON TABLE public.tbl_semesterwochen IS 'Number of Weeks per Semester';
			COMMENT ON TABLE public.tbl_service IS 'ServiceLevelAgreements';
			COMMENT ON TABLE public.tbl_sprache IS 'Key-Table of Languages';
			COMMENT ON TABLE public.tbl_standort IS 'Company Locations';
			COMMENT ON TABLE public.tbl_statistik IS 'Statistics';
			COMMENT ON TABLE public.tbl_status IS 'Key-Table of Student Status';
			COMMENT ON TABLE public.tbl_status_grund IS 'Key-Table of Reasons for Student Status Changes';
			COMMENT ON TABLE public.tbl_student IS 'List of Students';
			COMMENT ON TABLE public.tbl_studentlehrverband IS 'Connection of Students to Semester and Groups';
			COMMENT ON TABLE public.tbl_studiengang IS 'List of Degree Programs';
			COMMENT ON TABLE public.tbl_studiengangstyp IS 'Key-Table of Degree Program Types';
			COMMENT ON TABLE public.tbl_studienjahr IS 'Key-Table of Study Year';
			COMMENT ON TABLE public.tbl_studiensemester IS 'Key-Table of Study Semester';
			COMMENT ON TABLE public.tbl_tag IS 'Orders and Company Tags';
			COMMENT ON TABLE public.tbl_variable IS 'User Variables';
			COMMENT ON TABLE public.tbl_vorlage IS 'Key-Table of Document Templates';
			COMMENT ON TABLE public.tbl_vorlagedokument IS 'Connects Documents with Templates';
			COMMENT ON TABLE public.tbl_vorlagestudiengang IS 'Document Templates of Degree Programs';
			COMMENT ON TABLE bis.tbl_archiv IS 'Ministery Report archive';
			COMMENT ON TABLE bis.tbl_ausbildung IS 'Key-Table of Highest Education of Employees';
			COMMENT ON TABLE bis.tbl_berufstaetigkeit IS 'Key-Table of Professional Activity of Students';
			COMMENT ON TABLE bis.tbl_beschaeftigungsart1 IS 'Key-Table of Contract Types';
			COMMENT ON TABLE bis.tbl_beschaeftigungsart2 IS 'Key-Table of Contract Types';
			COMMENT ON TABLE bis.tbl_beschaeftigungsausmass IS 'Key-Table of Amount of Workinghours';
			COMMENT ON TABLE bis.tbl_besqual IS 'Key-Table of Employee Qualification';
			COMMENT ON TABLE bis.tbl_bisfunktion IS 'Summary of Teaching Hours per Lector';
			COMMENT ON TABLE bis.tbl_bisio IS 'Incoming, Outgoing Mobility';
			COMMENT ON TABLE bis.tbl_bisorgform IS 'Official Organisation Forms for BIS-Meldung';
			COMMENT ON TABLE bis.tbl_bisverwendung IS 'Employee Contracts';
			COMMENT ON TABLE bis.tbl_bundesland IS 'Federal States';
			COMMENT ON TABLE bis.tbl_entwicklungsteam IS 'Members of the Degree Program Development Team';
			COMMENT ON TABLE bis.tbl_gemeinde IS 'Key-Table of Local Community';
			COMMENT ON TABLE bis.tbl_gsprogramm IS 'Joint Degree Programs';
			COMMENT ON TABLE bis.tbl_gsprogrammtyp IS 'Joint Degree Programs';
			COMMENT ON TABLE bis.tbl_gsstudientyp IS 'Joint Degree Programs';
			COMMENT ON TABLE bis.tbl_hauptberuf IS 'Key-Table of Main Job';
			COMMENT ON TABLE bis.tbl_lgartcode IS 'Key-Table of Program Types';
			COMMENT ON TABLE bis.tbl_mobilitaet IS 'Joint Degree Programs of Students';
			COMMENT ON TABLE bis.tbl_mobilitaetsprogramm IS 'Key-Table of Mobility Programs';
			COMMENT ON TABLE bis.tbl_mobilitaetstyp IS 'Key-Table of Type of international activity';
			COMMENT ON TABLE bis.tbl_nation IS 'Key-Table of Nations';
			COMMENT ON TABLE bis.tbl_orgform IS 'Key-Table of Organisation Forms of Degree Programs';
			COMMENT ON TABLE bis.tbl_verwendung IS 'Key-Table of Employee Functions';
			COMMENT ON TABLE bis.tbl_zgv IS 'Key-Table of Requirements Bachelor';
			COMMENT ON TABLE bis.tbl_zgvdoktor IS 'Key-Table of Requirements Doktor';
			COMMENT ON TABLE bis.tbl_zgvgruppe IS 'Aliqoute Reduction Groups';
			COMMENT ON TABLE bis.tbl_zgvgruppe_zuordnung IS 'Aliqoute Reduction Groups';
			COMMENT ON TABLE bis.tbl_zgvmaster IS 'Key-Table of Requirements Master';
			COMMENT ON TABLE bis.tbl_zweck IS 'Key-Table of Purpose of Semester Abroad';
			COMMENT ON TABLE campus.tbl_abgabe IS 'Uploads to Kreuzerltool';
			COMMENT ON TABLE campus.tbl_anwesenheit IS 'Student Attendance';
			COMMENT ON TABLE campus.tbl_beispiel IS 'Kreuzerltool Entries';
			COMMENT ON TABLE campus.tbl_benutzerlvstudiensemester IS 'Subscriptions to Elective Courses';
			COMMENT ON TABLE campus.tbl_content IS 'Content Pages';
			COMMENT ON TABLE campus.tbl_contentchild IS 'Building the Content Tree';
			COMMENT ON TABLE campus.tbl_contentgruppe IS 'Content Permissions';
			COMMENT ON TABLE campus.tbl_contentlog IS 'Locking Log of Content Pages';
			COMMENT ON TABLE campus.tbl_contentsprache IS 'CMS Content in Different Languages';
			COMMENT ON TABLE campus.tbl_coodle IS 'Appointment Surveys';
			COMMENT ON TABLE campus.tbl_coodle_ressource IS 'Ressources Assigned to a Survey';
			COMMENT ON TABLE campus.tbl_coodle_ressource_termin IS 'Selected Time Slots of a Survey';
			COMMENT ON TABLE campus.tbl_coodle_status IS 'Key Table of State of the Survey';
			COMMENT ON TABLE campus.tbl_coodle_termin IS 'Time Slots of a Survey';
			COMMENT ON TABLE campus.tbl_dms IS 'List of CMS Documents';
			COMMENT ON TABLE campus.tbl_dms_kategorie IS 'Document Categories';
			COMMENT ON TABLE campus.tbl_dms_kategorie_gruppe IS 'Restrict Access to Document Categories';
			COMMENT ON TABLE campus.tbl_dms_version IS 'Versions of Documents';
			COMMENT ON TABLE campus.tbl_erreichbarkeit IS 'Key Table of Reachability in Case of Absence';
			COMMENT ON TABLE campus.tbl_feedback IS 'DEPRECATED';
			COMMENT ON TABLE campus.tbl_freebusy IS 'List of FreeBusy Calenders of a Person';
			COMMENT ON TABLE campus.tbl_freebusytyp IS 'Key Table of Supported FreeBusy Urls';
			COMMENT ON TABLE campus.tbl_infoscreen IS 'List of Aavailable Information Monitors';
			COMMENT ON TABLE campus.tbl_infoscreen_content IS 'Content of Information Monitors';
			COMMENT ON TABLE campus.tbl_legesamtnote IS 'Lehreinheit Grades';
			COMMENT ON TABLE campus.tbl_lehre_tools IS 'Additional Course Tools';
			COMMENT ON TABLE campus.tbl_lehre_tools_organisationseinheit IS 'Connects Courses of a Organisationunit to Tools';
			COMMENT ON TABLE campus.tbl_lehrveranstaltung_pruefung IS 'Connects Multiple Courses with one Exam';
			COMMENT ON TABLE campus.tbl_lvgesamtnote IS 'Course Grades Lector';
			COMMENT ON TABLE campus.tbl_lvinfo IS 'DEPRECATED';
			COMMENT ON TABLE campus.tbl_news IS 'studiengang_kz=0 and Semester=NULL -> global News
			studiengang_kz=0 and Semester=0 -> Elective Course News
			studiengang_kz=0 and Semester>0 -> News for selected Semester in all Degree Programs
			studiengang_kz>0 and (Semester=NULL or Semester=0) -> all Semesters in Degree Program
			studiengang_kz>0 and Semester>0 -> News for selected Semester in Degree Program';
			COMMENT ON TABLE campus.tbl_notenschluessel IS 'Kreuzerltool Grading Scheme';
			COMMENT ON TABLE campus.tbl_notenschluesseluebung IS 'Kreuzerltool Grading Scheme';
			COMMENT ON TABLE campus.tbl_paabgabe IS 'Project Submissions';
			COMMENT ON TABLE campus.tbl_paabgabetyp IS 'Key Table of Types of Submissions';
			COMMENT ON TABLE campus.tbl_pruefung IS 'Exams';
			COMMENT ON TABLE campus.tbl_pruefungsanmeldung IS 'Subscriptions to Exams';
			COMMENT ON TABLE campus.tbl_pruefungsfenster IS 'Definition Exam Weeks';
			COMMENT ON TABLE campus.tbl_pruefungsstatus IS 'Key Table of Exam Status';
			COMMENT ON TABLE campus.tbl_pruefungstermin IS 'Exam Time Slots';
			COMMENT ON TABLE campus.tbl_reservierung IS 'Room Reservation';
			COMMENT ON TABLE campus.tbl_resturlaub IS 'DEPRECATED';
			COMMENT ON TABLE campus.tbl_studentbeispiel IS 'Selected Entry in Kreuzerltool';
			COMMENT ON TABLE campus.tbl_studentuebung IS 'Exercise Grades in Kreuzerltool';
			COMMENT ON TABLE campus.tbl_template IS 'Templates for CMS Pages';
			COMMENT ON TABLE campus.tbl_uebung IS 'Exercises in Kreuzerltool';
			COMMENT ON TABLE campus.tbl_veranstaltung IS 'Events';
			COMMENT ON TABLE campus.tbl_veranstaltungskategorie IS 'Event Categories';
			COMMENT ON TABLE campus.tbl_zeitaufzeichnung IS 'Time Sheets of Employees';
			COMMENT ON TABLE campus.tbl_zeitsperre IS 'Absences of Employees';
			COMMENT ON TABLE campus.tbl_zeitsperretyp IS 'Type of Absences';
			COMMENT ON TABLE campus.tbl_zeitwunsch IS 'Teaching Time Preferences';
			COMMENT ON TABLE lehre.tbl_abschlussbeurteilung IS 'Key Table of Final Exam Grades';
			COMMENT ON TABLE lehre.tbl_abschlusspruefung IS 'Final Exam';
			COMMENT ON TABLE lehre.tbl_akadgrad IS 'Academic Title Assigned by Degree Program';
			COMMENT ON TABLE lehre.tbl_anrechnung IS 'Course Crediting';
			COMMENT ON TABLE lehre.tbl_anrechnung_begruendung IS 'Course Crediting Reasons';
			COMMENT ON TABLE lehre.tbl_betreuerart IS 'Key Table of Type of Project Supervisor';
			COMMENT ON TABLE lehre.tbl_ferien IS 'Holidays';
			COMMENT ON TABLE lehre.tbl_lehreinheit IS 'Course Parts';
			COMMENT ON TABLE lehre.tbl_lehreinheitgruppe IS 'Groups Assigned to Course';
			COMMENT ON TABLE lehre.tbl_lehreinheitmitarbeiter IS 'Lectures Assigned to Course';
			COMMENT ON TABLE lehre.tbl_lehrfach IS 'DEPRECATED';
			COMMENT ON TABLE lehre.tbl_lehrform IS 'Key Table of Teached Course Types';
			COMMENT ON TABLE lehre.tbl_lehrfunktion IS 'Key Table of Lector Functions in a Course';
			COMMENT ON TABLE lehre.tbl_lehrmittel IS 'DEPRECATED';
			COMMENT ON TABLE lehre.tbl_lehrtyp IS 'Key Table of Course Types';
			COMMENT ON TABLE lehre.tbl_lehrveranstaltung IS 'Courses, Modules';
			COMMENT ON TABLE lehre.tbl_lehrveranstaltung_kompatibel IS 'Course Compatibility';
			COMMENT ON TABLE lehre.tbl_lvangebot IS 'Offered Course Times';
			COMMENT ON TABLE lehre.tbl_lvregel IS 'Course Attendance Rules';
			COMMENT ON TABLE lehre.tbl_lvregeltyp IS 'Key Table of Course Rule Types';
			COMMENT ON TABLE lehre.tbl_moodle IS 'DEPRECATED';
			COMMENT ON TABLE lehre.tbl_moodle_version IS 'DEPRECATED';
			COMMENT ON TABLE lehre.tbl_note IS 'Key Table of Grades';
			COMMENT ON TABLE lehre.tbl_notenschluessel IS 'Course Grading Scheme';
			COMMENT ON TABLE lehre.tbl_notenschluesselaufteilung IS 'Course Grading Scheme Details';
			COMMENT ON TABLE lehre.tbl_notenschluesselzuordnung IS 'Connection Between Grading Scheme and Course/Degree Program';
			COMMENT ON TABLE lehre.tbl_projektarbeit IS 'Projects';
			COMMENT ON TABLE lehre.tbl_projektbetreuer IS 'Project Supervisor';
			COMMENT ON TABLE lehre.tbl_projekttyp IS 'Key Table of Project Type';
			COMMENT ON TABLE lehre.tbl_pruefung IS 'Exams';
			COMMENT ON TABLE lehre.tbl_pruefungstyp IS 'Key Table of Type of Exams';
			COMMENT ON TABLE lehre.tbl_studienordnung IS 'Additional Information for Degree Programs and Study Regulations';
			COMMENT ON TABLE lehre.tbl_studienordnung_semester IS 'DEPRECATED';
			COMMENT ON TABLE lehre.tbl_studienordnungstatus IS 'Key Table of Study Regulation Status';
			COMMENT ON TABLE lehre.tbl_studienplan IS 'Study Plan';
			COMMENT ON TABLE lehre.tbl_studienplan_lehrveranstaltung IS 'Connects Courses with a Study Plan';
			COMMENT ON TABLE lehre.tbl_studienplan_semester IS 'Valid Semesters of a Study Plan';
			COMMENT ON TABLE lehre.tbl_studienplatz IS 'Defines the Maximum Study Places per Degree Program';
			COMMENT ON TABLE lehre.tbl_stunde IS 'Time Grid of Schedule';
			COMMENT ON TABLE lehre.tbl_stundenplan IS 'Schedule (Productive Data)';
			COMMENT ON TABLE lehre.tbl_stundenplan_betriebsmittel IS 'Required Teaching Material';
			COMMENT ON TABLE lehre.tbl_stundenplandev IS 'Schedule (Developing Data)';
			COMMENT ON TABLE lehre.tbl_vertrag IS 'Teaching Contracts';
			COMMENT ON TABLE lehre.tbl_vertrag_vertragsstatus IS 'Status History of Contracts';
			COMMENT ON TABLE lehre.tbl_vertragsstatus IS 'Key Table of Contract Status';
			COMMENT ON TABLE lehre.tbl_vertragstyp IS 'Key Table of Type of Contracts';
			COMMENT ON TABLE lehre.tbl_zeitfenster IS 'DEPRECATED';
			COMMENT ON TABLE lehre.tbl_zeugnis IS 'DEPRECATED';
			COMMENT ON TABLE lehre.tbl_zeugnisnote IS 'Final Grades for Courses';
			COMMENT ON TABLE system.tbl_app IS 'FH Complete Applications';
			COMMENT ON TABLE system.tbl_appdaten IS 'App Specific Data';
			COMMENT ON TABLE system.tbl_benutzerrolle IS 'Assigns Permissions and Roles to Users';
			COMMENT ON TABLE system.tbl_berechtigung IS 'Key Table of Permissions';
			COMMENT ON TABLE system.tbl_cronjob IS 'Automatic Cronjobs';
			COMMENT ON TABLE system.tbl_extensions IS 'Table to Manage FH Complete Extensions';
			COMMENT ON TABLE system.tbl_filters IS 'Table to Manage FH Complete Filters';
			COMMENT ON TABLE system.tbl_log IS 'Person Log';
			COMMENT ON TABLE system.tbl_logtype IS 'Key Table of Types of Log Entries';
			COMMENT ON TABLE system.tbl_phrase IS 'Multi Language Phrases';
			COMMENT ON TABLE system.tbl_phrasentext IS 'Multi Language Phrases Text';
			COMMENT ON TABLE system.tbl_person_lock IS 'Persons that are locked for editing';
			COMMENT ON TABLE system.tbl_rolle IS 'Permission Roles';
			COMMENT ON TABLE system.tbl_rolleberechtigung IS 'Assigns Permissions to Roles';
			COMMENT ON TABLE system.tbl_server IS 'List of Servers for Cronjobs';
			COMMENT ON TABLE system.tbl_udf IS 'User Defined Fields';
			COMMENT ON TABLE system.tbl_verarbeitungstaetigkeit IS 'Processing Activities';
			COMMENT ON TABLE system.tbl_webservicelog IS 'Webservice Log';
			COMMENT ON TABLE system.tbl_webservicerecht IS 'Webservice Permissions';
			COMMENT ON TABLE system.tbl_webservicetyp IS 'Key Table of Webservice Types';
			COMMENT ON TABLE fue.tbl_aktivitaet IS 'Timesheet SLA Activity';
			COMMENT ON TABLE fue.tbl_aufwandstyp IS 'Estimation Scale Type';
			COMMENT ON TABLE fue.tbl_projekt IS 'Projects';
			COMMENT ON TABLE fue.tbl_projekt_dokument IS 'Assigns a DMS Document to a Project';
			COMMENT ON TABLE fue.tbl_projekt_ressource IS 'Assigns a Ressource to a Project';
			COMMENT ON TABLE fue.tbl_projektphase IS 'Project Phases';
			COMMENT ON TABLE fue.tbl_projekttask IS 'Project Tasks';
			COMMENT ON TABLE fue.tbl_ressource IS 'Project Ressources (Persons, Companys, Inventory)';
			COMMENT ON TABLE fue.tbl_scrumsprint IS 'DEPRECATED';
			COMMENT ON TABLE fue.tbl_scrumteam IS 'DEPRECATED';
			COMMENT ON TABLE wawi.tbl_aufteilung IS 'DEPRECATED';
			COMMENT ON TABLE wawi.tbl_aufteilung_default IS 'DEPRECATED';
			COMMENT ON TABLE wawi.tbl_bestelldetail IS 'Order Details';
			COMMENT ON TABLE wawi.tbl_bestelldetailtag IS 'Order Details Tags';
			COMMENT ON TABLE wawi.tbl_bestellstatus IS 'Key Table of Order Status';
			COMMENT ON TABLE wawi.tbl_bestellung IS 'Orders';
			COMMENT ON TABLE wawi.tbl_bestellung_bestellstatus IS 'Order Status History';
			COMMENT ON TABLE wawi.tbl_bestellungtag IS 'Order Tags';
			COMMENT ON TABLE wawi.tbl_betriebsmittel IS 'Inventory';
			COMMENT ON TABLE wawi.tbl_betriebsmittel_betriebsmittelstatus IS 'Inventory Status History';
			COMMENT ON TABLE wawi.tbl_betriebsmittelperson IS 'Assigns Inventory to a Person';
			COMMENT ON TABLE wawi.tbl_betriebsmittelstatus IS 'Key Table of Inventory Status';
			COMMENT ON TABLE wawi.tbl_betriebsmitteltyp IS 'Key Table of Inventory Type';
			COMMENT ON TABLE wawi.tbl_buchung IS 'Accounting of Lecturers (Addon-Abrechnung)';
			COMMENT ON TABLE wawi.tbl_buchungstyp IS 'Key Table of Booking Types';
			COMMENT ON TABLE wawi.tbl_budget IS 'Budget per Cost Unit';
			COMMENT ON TABLE wawi.tbl_konto IS 'Accounts';
			COMMENT ON TABLE wawi.tbl_konto_kostenstelle IS 'Connects Multiple Accounts with a Cost Unit';
			COMMENT ON TABLE wawi.tbl_kostenstelle IS 'Cost Units';
			COMMENT ON TABLE wawi.tbl_projekt_bestellung IS 'Assigns Orders to a Project';
			COMMENT ON TABLE wawi.tbl_rechnung IS 'Invoice';
			COMMENT ON TABLE wawi.tbl_rechnungsbetrag IS 'Invoice Amount';
			COMMENT ON TABLE wawi.tbl_rechnungstyp IS 'Key Table of Invoice Types';
			COMMENT ON TABLE wawi.tbl_zahlungstyp IS 'Key Table of Payment Types';
			COMMENT ON TABLE testtool.tbl_ablauf IS 'List of Sections per Degree Program';
			COMMENT ON TABLE testtool.tbl_ablauf_vorgaben IS 'Additional Test Configuration';
			COMMENT ON TABLE testtool.tbl_antwort IS 'Answers of the Candidate';
			COMMENT ON TABLE testtool.tbl_frage IS 'List of Questions';
			COMMENT ON TABLE testtool.tbl_frage_sprache IS 'Questions in Different Languages';
			COMMENT ON TABLE testtool.tbl_gebiet IS 'List of Test Sections';
			COMMENT ON TABLE testtool.tbl_kategorie IS 'DEPRECATED';
			COMMENT ON TABLE testtool.tbl_kriterien IS 'DEPRECATED';
			COMMENT ON TABLE testtool.tbl_pruefling IS 'List of Tested Candidates';
			COMMENT ON TABLE testtool.tbl_pruefling_frage IS 'Questions Given to a Candidate';
			COMMENT ON TABLE testtool.tbl_vorschlag IS 'Available Answers to a Question';
			COMMENT ON TABLE testtool.tbl_vorschlag_sprache IS 'Answers in Different Languages';

			COMMENT ON SCHEMA addon IS 'Extensions and Addons';
			COMMENT ON SCHEMA bis IS 'Key Table of and Additional Tables for Ministery Report';
			COMMENT ON SCHEMA campus IS 'Campus Management and CIS';
			COMMENT ON SCHEMA fue IS 'Projectmanagement';
			COMMENT ON SCHEMA lehre IS 'Teaching and Learning';
			COMMENT ON SCHEMA public IS 'Base Data';
			COMMENT ON SCHEMA reports IS 'Cachingtables for Reporting';
			COMMENT ON SCHEMA sync IS 'Synchronisation Data';
			COMMENT ON SCHEMA system IS 'Permissions, Logging';
			COMMENT ON SCHEMA testtool IS 'Placement Test';
			COMMENT ON SCHEMA wawi IS 'Inventory, Orders';

			COMMENT ON COLUMN public.tbl_prestudent.rt_punkte1 IS 'DEPRECATED';
			COMMENT ON COLUMN public.tbl_prestudent.rt_punkte2 IS 'DEPRECATED';
			COMMENT ON COLUMN public.tbl_prestudent.rt_punkte3 IS 'DEPRECATED';
			COMMENT ON COLUMN public.tbl_prestudent.anmeldungreihungstest IS 'DEPRECATED';
			COMMENT ON COLUMN public.tbl_prestudent.reihungstest_id IS 'DEPRECATED';
			COMMENT ON COLUMN public.tbl_prestudent.ausstellungsstaat IS 'DEPRECATED';
			COMMENT ON COLUMN public.tbl_prestudent.aufnahmeschluessel IS 'DEPRECATED';
			COMMENT ON COLUMN lehre.tbl_lehrveranstaltung.old_lehrfach_id IS 'DEPRECATED';
			";

			if(!$db->db_query($qry))
				echo '<strong>Comments: '.$db->db_last_error().'</strong><br>';
			else
				echo 'Kommentare fuer DB Datenbanktabellen hinzugefügt';
		}
	}
}

if (!$result = @$db->db_query("SELECT projekt_id FROM fue.tbl_projekt LIMIT 1"))
{
	$qry = "CREATE SEQUENCE fue.tbl_projekt_projekt_id_seq
			 INCREMENT BY 1
			 NO MAXVALUE
			 NO MINVALUE
			 CACHE 1;
			GRANT SELECT, UPDATE ON fue.tbl_projekt_projekt_id_seq TO vilesci;
			ALTER TABLE fue.tbl_projekt ADD COLUMN projekt_id integer NOT NULL DEFAULT nextval('fue.tbl_projekt_projekt_id_seq');
			ALTER TABLE fue.tbl_projekt ADD CONSTRAINT uk_tbl_projekt_projekt_id UNIQUE (projekt_id);
	";
	if (!$db->db_query($qry))
		echo '<strong>Projekt: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte projekt_id für fue.tbl_projekt hinzugefügt';

}

// add column zeitaufzeichnung to fue.tbl_project
if (!$result = @$db->db_query("SELECT zeitaufzeichnung FROM fue.tbl_projekt LIMIT 1"))
{
	$qry = "ALTER TABLE fue.tbl_projekt ADD COLUMN zeitaufzeichnung BOOLEAN NOT NULL DEFAULT true;";

	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekt '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte zeitaufzeichnung zu fue.tbl_projekt hinzugefügt';
}

// add column zeitaufzeichnung to fue.tbl_projectphase
if (!$result = @$db->db_query("SELECT zeitaufzeichnung FROM fue.tbl_projektphase LIMIT 1"))
{
	$qry = "ALTER TABLE fue.tbl_projektphase ADD COLUMN zeitaufzeichnung BOOLEAN NOT NULL DEFAULT true;";

	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projektphase '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte zeitaufzeichnung zu fue.tbl_projektphase hinzugefügt';
}

// Extension Schema
if ($result = $db->db_query("SELECT schema_name FROM information_schema.schemata WHERE schema_name='extension'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE SCHEMA extension;
				COMMENT ON SCHEMA extension is 'Extension Tables';";

		if (!$db->db_query($qry))
			echo '<strong>Extension: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neues Schema extension hinzugefuegt';
	}
}

// Berechtigungen fuer web-user erteilen Log in public.tbl_log zu schreiben
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_log' AND table_schema='public' AND grantee='web' AND privilege_type='INSERT'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "GRANT SELECT, INSERT ON public.tbl_log TO web;
			";

		if(!$db->db_query($qry))
			echo '<strong>Log Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Web User Insert fuer public.tbl_log berechtigt';
	}
}

// ADD COLUMN oe_kurzbz AND berechtigung_kurzbz to campus.tbl_dms_kategorie
if(!@$db->db_query("SELECT oe_kurzbz FROM campus.tbl_dms_kategorie LIMIT 1"))
{
	$qry = "ALTER TABLE campus.tbl_dms_kategorie ADD COLUMN oe_kurzbz varchar(32);
			ALTER TABLE campus.tbl_dms_kategorie ADD COLUMN berechtigung_kurzbz varchar(32);

			ALTER TABLE campus.tbl_dms_kategorie ADD CONSTRAINT fk_dms_kategorie_oe_kurzbz FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit(oe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE campus.tbl_dms_kategorie ADD CONSTRAINT fk_dms_kategorie_berechtigung_kurzbz FOREIGN KEY (berechtigung_kurzbz) REFERENCES system.tbl_berechtigung(berechtigung_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
			";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_dms_kategorie '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte oe_kurzbz und berechtigung_kurzbz in campus.tbl_dms_kategorie hinzugefügt';
}

// ADD COLUMN cis_suche (boolean) AND schlagworte to campus.tbl_dms_version
if(!@$db->db_query("SELECT cis_suche FROM campus.tbl_dms_version LIMIT 1"))
{
	$qry = "ALTER TABLE campus.tbl_dms_version ADD COLUMN cis_suche boolean NOT NULL DEFAULT false;
			ALTER TABLE campus.tbl_dms_version ADD COLUMN schlagworte text;

			COMMENT ON COLUMN campus.tbl_dms_version.schlagworte IS 'Semicolon-separated string with keywords for CIS-search';
			UPDATE campus.tbl_dms_version SET cis_suche=true WHERE beschreibung != '';
			UPDATE campus.tbl_dms_version SET schlagworte=beschreibung WHERE beschreibung != '';
			";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_dms_version '.$db->db_last_error().'</strong><br>';
		else
			echo '	<br>Spalte cis_suche und schlagworte in campus.tbl_dms_version hinzugefügt.
					<br><b>Alle DMS-Einträge mit befülltem Beschreibungstext wurden auf cis_suche=true gesetzt</b>
					<br><b>Bei allen DMS-Einträge mit befülltem Beschreibungstext, wurde dieser in die Spalte schlagworte übernommen</b>';
}

//---------------------------------------------------------------------------------------------------------------------
// Start changes to Phrases

// ADD COLUMN category to system.tbl_phrase
if (!$result = @$db->db_query("SELECT category FROM system.tbl_phrase LIMIT 1"))
{
	$qry = 'ALTER TABLE system.tbl_phrase ADD COLUMN category character varying(64);';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_phrase: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added column category to table system.tbl_phrase';

	// COMMENT ON TABLE system.tbl_phrase
	$qry = 'COMMENT ON COLUMN system.tbl_phrase.category IS \'To divide the phrases into categories\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to system.tbl_phrase.category: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to system.tbl_phrase.category';
}

// UNIQUE INDEX uidx_phrase_category_phrase
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'uidx_phrase_category_phrase'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'CREATE UNIQUE INDEX uidx_phrase_category_phrase ON system.tbl_phrase USING btree (category, phrase);';
		if (!$db->db_query($qry))
			echo '<strong>uidx_phrase_category_phrase '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created unique uidx_phrase_category_phrase';
	}
}

// UNIQUE INDEX uidx_phrasestext_phrase_id_sprache_orgeinheit_kurzbz_orgform_kurzbz
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'uidx_phrasestext_phrase_id_sprache_orgeinheit_kurzbz_orgform_kurzbz'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'CREATE UNIQUE INDEX uidx_phrasestext_phrase_id_sprache_orgeinheit_kurzbz_orgform_kurzbz ON system.tbl_phrasentext USING btree (phrase_id, sprache, orgeinheit_kurzbz, orgform_kurzbz);';
		if (!$db->db_query($qry))
			echo '<strong>uidx_phrasestext_phrase_id_sprache_orgeinheit_kurzbz_orgform_kurzbz '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created unique uidx_phrasestext_phrase_id_sprache_orgeinheit_kurzbz_orgform_kurzbz';
	}
}

// End changes to Phrases
// ---------------------------------------------------------------------------------------------------------------------

// Remove NOT NULL constraint on matrikelnr on public.tbl_student
if($result = @$db->db_query("SELECT is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_student' AND COLUMN_NAME = 'matrikelnr' AND is_nullable = 'NO'"))
{
	if($db->db_num_rows($result) > 0)
	{
		$qry = "ALTER TABLE public.tbl_student ALTER COLUMN matrikelnr DROP NOT NULL;";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_student '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Removed NOT NULL constraint on "matrikelnr" from public.tbl_student<br>';
	}
}

// public.vw_prestudentstatus Datum zur Reihungstestanmeldung aus tbl_rt_person
if($result = $db->db_query("SELECT view_definition FROM information_schema.views WHERE table_schema='public' AND table_name='vw_prestudentstatus'"))
{
	if($row = $db->db_fetch_object($result))
	{
		if(!mb_stristr($row->view_definition, 'tbl_rt_person'))
		{
			$qry = "
			CREATE OR REPLACE VIEW public.vw_prestudentstatus AS
			SELECT tbl_prestudent.prestudent_id,
				tbl_person.person_id,
				tbl_person.staatsbuergerschaft,
				tbl_person.geburtsnation,
				tbl_person.sprache,
				tbl_person.anrede,
				tbl_person.titelpost,
				tbl_person.titelpre,
				tbl_person.nachname,
				tbl_person.vorname,
				tbl_person.vornamen,
				tbl_person.gebdatum,
				tbl_person.gebort,
				tbl_person.gebzeit,
				tbl_person.foto,
				tbl_person.homepage,
				tbl_person.svnr,
				tbl_person.ersatzkennzeichen,
				tbl_person.familienstand,
				tbl_person.geschlecht,
				tbl_person.anzahlkinder,
				tbl_person.aktiv,
				tbl_person.bundesland_code,
				tbl_person.kompetenzen,
				tbl_person.kurzbeschreibung,
				tbl_person.zugangscode,
				tbl_person.foto_sperre,
				tbl_person.matr_nr,
				tbl_prestudent.aufmerksamdurch_kurzbz,
				tbl_prestudent.studiengang_kz,
				tbl_prestudent.berufstaetigkeit_code,
				tbl_prestudent.ausbildungcode,
				tbl_prestudent.zgv_code,
				tbl_prestudent.zgvort,
				tbl_prestudent.zgvdatum,
				tbl_prestudent.zgvmas_code,
				tbl_prestudent.zgvmaort,
				tbl_prestudent.zgvmadatum,
				tbl_prestudent.aufnahmeschluessel,
				tbl_prestudent.facheinschlberuf,
				tbl_prestudent.reihungstest_id,
				(SELECT
							COALESCE(anmeldedatum, tbl_rt_person.insertamum::date)
						FROM
							public.tbl_rt_person
							JOIN public.tbl_reihungstest ON(rt_id=reihungstest_id)
							JOIN lehre.tbl_studienplan USING(studienplan_id)
							JOIN lehre.tbl_studienordnung USING(studienordnung_id)
						WHERE
							person_id=tbl_prestudent.person_id
							AND tbl_reihungstest.studiensemester_kurzbz=prestudentstatus.studiensemester_kurzbz
							AND tbl_studienordnung.studiengang_kz=tbl_prestudent.studiengang_kz
						ORDER BY anmeldedatum DESC, tbl_rt_person.insertamum DESC limit 1
					) as anmeldungreihungstest,
				tbl_prestudent.reihungstestangetreten,
				tbl_prestudent.rt_gesamtpunkte,
				tbl_prestudent.bismelden,
				tbl_prestudent.anmerkung,
				tbl_prestudent.dual,
				tbl_prestudent.rt_punkte1,
				tbl_prestudent.rt_punkte2,
				tbl_prestudent.ausstellungsstaat,
				tbl_prestudent.rt_punkte3,
				tbl_prestudent.zgvdoktor_code,
				tbl_prestudent.zgvdoktorort,
				tbl_prestudent.zgvdoktordatum,
				tbl_prestudent.mentor,
				prestudentstatus.status_kurzbz,
				prestudentstatus.studiensemester_kurzbz,
				prestudentstatus.ausbildungssemester,
				prestudentstatus.datum,
				prestudentstatus.insertamum,
				prestudentstatus.insertvon,
				prestudentstatus.updateamum,
				prestudentstatus.updatevon,
				COALESCE(prestudentstatus.orgform_kurzbz, tbl_studiengang.orgform_kurzbz) AS orgform_kurzbz,
				prestudentstatus.studienplan_id,
				prestudentstatus.bestaetigtam,
				prestudentstatus.bestaetigtvon,
				prestudentstatus.fgm,
				prestudentstatus.faktiv,
				tbl_studiengang.kurzbz,
				tbl_studiengang.kurzbzlang,
				tbl_studiengang.typ,
				tbl_studiensemester.start,
				tbl_studiensemester.ende,
				tbl_studiensemester.studienjahr_kurzbz,
				substr(tbl_studiensemester.studiensemester_kurzbz::text, 3) || lower(substr(tbl_studiensemester.studiensemester_kurzbz::text, 1, 1)) AS studiensemester,
				    CASE
				        WHEN tbl_studiengang.typ = 'b'::bpchar AND tbl_prestudent.zgv_code IS NOT NULL OR tbl_studiengang.typ = 'm'::bpchar AND tbl_prestudent.zgvmas_code IS NOT NULL OR tbl_studiengang.typ = 'd'::bpchar AND tbl_prestudent.zgvdoktor_code IS NOT NULL THEN true
				        ELSE false
				    END AS zgv,
				    CASE
				        WHEN tbl_prestudentstatus.prestudent_id IS NULL THEN false
				        ELSE true
				    END AS student,
				date_part('week'::text, prestudentstatus.datum) AS kw
				FROM public.tbl_person
				 JOIN public.tbl_prestudent USING (person_id)
				 JOIN public.tbl_prestudentstatus prestudentstatus USING (prestudent_id)
				 JOIN public.tbl_studiengang USING (studiengang_kz)
				 JOIN public.tbl_studiensemester USING (studiensemester_kurzbz)
				 LEFT JOIN public.tbl_prestudentstatus ON tbl_prestudentstatus.prestudent_id = prestudentstatus.prestudent_id AND tbl_prestudentstatus.studiensemester_kurzbz::text = prestudentstatus.studiensemester_kurzbz::text AND tbl_prestudentstatus.status_kurzbz::text = 'Student'::text;

			GRANT SELECT ON public.vw_prestudentstatus TO vilesci;
			GRANT SELECT ON public.vw_prestudentstatus TO web;
			";

			if(!$db->db_query($qry))
				echo '<strong>public.vw_prestudentstatus:'.$db->db_last_error().'</strong><br>';
			else
				echo '<br>public.vw_prestudentstatus angepasst damit anmeldungzumreihungstest aus tbl_rt_person kommt';
		}
	}
}

// OE_KURZBZ in system.tbl_filters auf 32 Zeichen verlängert
if($result = $db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='system' AND TABLE_NAME='tbl_filters' AND COLUMN_NAME = 'oe_kurzbz' AND character_maximum_length=16"))
{
	if($db->db_num_rows($result)>0)
	{
		$qry = " ALTER TABLE system.tbl_filters ALTER COLUMN oe_kurzbz TYPE varchar(32)";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_filters '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte oe_kurzbz in system.tbl_filters von varchar(16) auf varchar(32) geändert<br>';
	}
}

// Berechtigungen fuer vilesci User erteilen auf system.tbl_log
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_log' AND table_schema='system' AND grantee='vilesci' AND privilege_type='DELETE'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT DELETE ON system.tbl_log TO vilesci;";

		if(!$db->db_query($qry))
			echo '<strong>Permission Log: '.$db->db_last_error().'</strong><br>';
		else
			echo 'Loeschrechte auf system.tbl_log für Vilesci User hinzugefügt';
	}
}

// Delete-Berechtigungen fuer web User erteilen auf system.tbl_log
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_log' AND table_schema='system' AND grantee='web' AND privilege_type='DELETE'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT DELETE ON system.tbl_log TO web;";

		if(!$db->db_query($qry))
			echo '<strong>Permission Log: '.$db->db_last_error().'</strong><br>';
			else
				echo 'Delete-Rechte auf system.tbl_log für Web User hinzugefügt';
	}
}

// Add missing Foreign Key to public.tbl_rt_person.person_id
if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'fk_tbl_rt_person_person_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "SELECT person_id FROM public.tbl_rt_person WHERE NOT EXISTS(SELECT 1 FROM public.tbl_person WHERE person_id=tbl_rt_person.person_id)";
		if($result = $db->db_query($qry))
		{
			if($db->db_num_rows($result)==0)
			{
				$qry = "ALTER TABLE public.tbl_rt_person ADD CONSTRAINT fk_tbl_rt_person_person_id FOREIGN KEY (person_id) REFERENCES public.tbl_person ON DELETE RESTRICT ON UPDATE CASCADE;";

				if (!$db->db_query($qry))
					echo '<strong>public.tbl_rt_person '.$db->db_last_error().'</strong><br>';
				else
					echo '<br>public.tbl_rt_person: added foreign key on column person_id';
			}
			else
			{
				echo '<strong>public.tbl_rt_person:
				Fehlender Foreign Key auf person_id kann nicht erstellt werden!
				In der Tabelle public.tbl_rt_person wird auf Personen verlinkt die nicht mehr in der Tabelle
				public.tbl_person vorhanden sind. Bitte bereinigen Sie die Datensätze manuell und starten Sie dieses Script erneut.
				</strong>';
			}
		}
	}
}

// Webservicetyp dvb
if ($result = $db->db_query("SELECT * FROM system.tbl_webservicetyp WHERE webservicetyp_kurzbz='dvb'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_webservicetyp(webservicetyp_kurzbz, beschreibung) VALUES('dvb','Datenverbund');";

		if(!$db->db_query($qry))
			echo '<strong>WebserviceLog: '.$db->db_last_error().'</strong><br>';
			else
				echo 'Webservicelog Typ für Datenverbund hinzugefügt';
	}
}

//Spalte bpk in public.tbl_person
if(!$result = @$db->db_query("SELECT bpk FROM public.tbl_person LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_person ADD COLUMN bpk varchar(255);
		COMMENT ON COLUMN public.tbl_person.bpk IS 'Bereichsspezifisches Personenkennzeichen';";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_person: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_person: Spalte bpk hinzugefuegt';
}

// titel und bezeichnung in tbl_akte verlaengert
if($result = $db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='public' AND TABLE_NAME='tbl_akte' AND COLUMN_NAME = 'titel' AND character_maximum_length=32"))
{
	if($db->db_num_rows($result)>0)
	{
		$qry = "ALTER TABLE public.tbl_akte ALTER COLUMN titel TYPE varchar(64);
		ALTER TABLE public.tbl_akte ALTER COLUMN bezeichnung TYPE varchar(64);
		";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_akte '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte titel und bezeichnung in public.tbl_akte von varchar(32) auf varchar(64) geändert<br>';
	}
}

// INSERT und Update Berechtigungen fuer web User erteilen fuer tbl_msg_message
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_msg_message' AND table_schema='public' AND grantee='web' AND privilege_type='INSERT'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT SELECT, INSERT, UPDATE ON public.tbl_msg_message TO web;
		GRANT SELECT, UPDATE ON SEQUENCE public.tbl_msg_message_message_id_seq TO web;";

		if(!$db->db_query($qry))
		{
			echo '<strong>public.tbl_msg_message Berechtigungen: '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo '<br>INSERT und UPDATE Rechte fuer public.tbl_msg_message
				und tbl_msg_message_message_id_seq fuer web user gesetzt ';
		}
	}
}

// INSERT und Update Berechtigungen fuer web User erteilen fuer tbl_msg_recipient
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_msg_recipient' AND table_schema='public' AND grantee='web' AND privilege_type='INSERT'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT SELECT, INSERT, UPDATE ON public.tbl_msg_recipient TO web;";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_msg_message Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>INSERT und UPDATE Rechte fuer public.tbl_msg_message fuer web user gesetzt ';
	}
}

// Add UNIQUE constraint on kurzbz from public.tbl_filter
if($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'uk_filter_kurzbz'"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "ALTER TABLE public.tbl_filter ADD CONSTRAINT uk_filter_kurzbz UNIQUE(kurzbz);";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_filter '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Added UNIQUE constraint on "kurzbz" from public.tbl_filter<br>';
	}
}

// Reporting filter: bezeichnung
if(!$result = @$db->db_query("SELECT bezeichnung FROM public.tbl_filter LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_filter ADD COLUMN bezeichnung varchar(65);";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_filter: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_filter: Spalte bezeichnung hinzugefuegt';
}

// vw_mitarbeiter updateaktivamum updateaktivvon
if(!$result = @$db->db_query("SELECT updateaktivam FROM campus.vw_mitarbeiter LIMIT 1"))
{
	$qry = "CREATE OR REPLACE VIEW campus.vw_mitarbeiter as
		SELECT tbl_benutzer.uid,
		tbl_mitarbeiter.ausbildungcode,
		tbl_mitarbeiter.personalnummer,
		tbl_mitarbeiter.kurzbz,
		tbl_mitarbeiter.lektor,
		tbl_mitarbeiter.fixangestellt,
		tbl_mitarbeiter.telefonklappe,
		tbl_benutzer.person_id,
		tbl_benutzer.alias,
		tbl_person.geburtsnation,
		tbl_person.sprache,
		tbl_person.anrede,
		tbl_person.titelpost,
		tbl_person.titelpre,
		tbl_person.nachname,
		tbl_person.vorname,
		tbl_person.vornamen,
		tbl_person.gebdatum,
		tbl_person.gebort,
		tbl_person.gebzeit,
		tbl_person.foto,
		tbl_mitarbeiter.anmerkung,
		tbl_person.homepage,
		tbl_person.svnr,
		tbl_person.ersatzkennzeichen,
		tbl_person.geschlecht,
		tbl_person.familienstand,
		tbl_person.anzahlkinder,
		tbl_mitarbeiter.ort_kurzbz,
		tbl_benutzer.aktiv,
		tbl_mitarbeiter.bismelden,
		tbl_mitarbeiter.standort_id,
		tbl_mitarbeiter.updateamum,
		tbl_mitarbeiter.updatevon,
		tbl_mitarbeiter.insertamum,
		tbl_mitarbeiter.insertvon,
		tbl_mitarbeiter.ext_id,
		tbl_benutzer.aktivierungscode,
		( SELECT tbl_kontakt.kontakt
		       FROM tbl_kontakt
		      WHERE tbl_kontakt.person_id = tbl_person.person_id AND tbl_kontakt.kontakttyp::text = 'email'::text
		      ORDER BY tbl_kontakt.zustellung DESC
		     LIMIT 1) AS email_privat,
		tbl_benutzer.updateaktivam,
		tbl_benutzer.updateaktivvon,
		greatest(tbl_person.updateamum, tbl_benutzer.updateamum, tbl_mitarbeiter.updateamum) as lastupdate
		FROM public.tbl_mitarbeiter
		 JOIN public.tbl_benutzer ON tbl_mitarbeiter.mitarbeiter_uid::text = tbl_benutzer.uid::text
		 JOIN public.tbl_person USING (person_id);
	";

	if(!$db->db_query($qry))
		echo '<strong>campus.vw_mitarbeiter: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.vw_mitarbeiter: Spalte updateaktivam, updateaktivon, lastupdate hinzugefuegt';
}

// Spalte lkt_ueberschreibbar in lehre.tbl_note
if(!$result = @$db->db_query("SELECT lkt_ueberschreibbar FROM lehre.tbl_note LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_note ADD COLUMN lkt_ueberschreibbar boolean NOT NULL DEFAULT true;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_note: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_note: Spalte lkt_ueberschreibbar hinzugefuegt!<br>';
}


// ADD COLUMN zeitaufzeichnungspflichtig to bis.tbl_bisverwendung
// UPDATE zeitaufzeichnungspflichtig in bis.tbl_bisverwendung
if(!@$db->db_query("SELECT zeitaufzeichnungspflichtig FROM bis.tbl_bisverwendung LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_bisverwendung ADD COLUMN zeitaufzeichnungspflichtig boolean;

			UPDATE
				bis.tbl_bisverwendung
			SET
				zeitaufzeichnungspflichtig=true
			FROM
				public.tbl_mitarbeiter
			WHERE
				tbl_bisverwendung.mitarbeiter_uid = tbl_mitarbeiter.mitarbeiter_uid
			AND
				fixangestellt=true;
			UPDATE
				bis.tbl_bisverwendung
			SET
				zeitaufzeichnungspflichtig=false
			FROM
				public.tbl_mitarbeiter
			WHERE
				tbl_bisverwendung.mitarbeiter_uid = tbl_mitarbeiter.mitarbeiter_uid
			AND
				fixangestellt=false;

			COMMENT ON COLUMN bis.tbl_bisverwendung.zeitaufzeichnungspflichtig IS 'CaseTime Monatslisten mit Vertragsbeginn verpflichtend führen?';";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_bisverwendung '.$db->db_last_error().'</strong><br>';
		else
			echo "<br>Spalte zeitaufzeichnungspflichtig in bis.tbl_bisverwendung hinzugefügt"
				. "<br>Fix angestellte Mitarbeiter auf true gesetzt, alle anderen auf false";
}

// Spalte Priorisierung für tbl_prestudent
if(!$result = @$db->db_query("SELECT priorisierung FROM public.tbl_prestudent LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_prestudent ADD COLUMN priorisierung smallint;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudent: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.tbl_prestudent: Spalte priorisierung hinzugefuegt';
}

// Spalte zgv_erfuellt für tbl_prestudent
if(!$result = @$db->db_query("SELECT zgv_erfuellt FROM public.tbl_prestudent LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_prestudent ADD COLUMN zgv_erfuellt boolean default false;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudent: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.tbl_prestudent: Spalte zgv_erfuellt hinzugefuegt';
}

// Spalte zgvmas_erfuellt für tbl_prestudent
if(!$result = @$db->db_query("SELECT zgvmas_erfuellt FROM public.tbl_prestudent LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_prestudent ADD COLUMN zgvmas_erfuellt boolean default false;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudent: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.tbl_prestudent: Spalte zgvmas_erfuellt hinzugefuegt';
}

// Spalte zgvdoktor_erfuellt für tbl_prestudent
if(!$result = @$db->db_query("SELECT zgvdoktor_erfuellt FROM public.tbl_prestudent LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_prestudent ADD COLUMN zgvdoktor_erfuellt boolean default false;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudent: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.tbl_prestudent: Spalte zgvdoktor_erfuellt hinzugefuegt';
}


// Spalte lieferant in tbl_firma
if(!$result = @$db->db_query("SELECT lieferant FROM public.tbl_firma LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_firma ADD COLUMN lieferant boolean default false;
	UPDATE public.tbl_firma SET lieferant = true WHERE firmentyp_kurzbz = 'Firma'";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_firma: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.tbl_firma: Spalte lieferant hinzugefuegt';
}

// INSERT, UPDATE und DELETE Berechtigungen fuer web User erteilen fuer tbl_rt_person und SEQUENCE public.tbl_rt_person_rt_person_id_seq
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_rt_person' AND table_schema='public' AND grantee='web' AND privilege_type='INSERT'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_rt_person TO web;
				GRANT SELECT, UPDATE ON SEQUENCE public.tbl_rt_person_rt_person_id_seq TO web;";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_rt_person Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>INSERT, UPDATE und DELETE Rechte fuer Tabelle public.tbl_rt_person und SELECT, UPDATE Rechte für Sequenz public.tbl_rt_person_rt_person_id_seq fuer web user gesetzt ';
	}
}

// App 'reihungstest' hinzufügen
if($result = $db->db_query("SELECT 1 FROM system.tbl_app WHERE app='reihungstest'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "INSERT INTO system.tbl_app(app) VALUES('reihungstest');";

		if(!$db->db_query($qry))
			echo '<strong>App: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue App reihungstest in system.tbl_app hinzugefügt';
	}
}

// Spalte direktinskription fuer public.tbl_gruppe
if(!$result = @$db->db_query("SELECT direktinskription FROM public.tbl_gruppe LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_gruppe ADD COLUMN direktinskription boolean NOT NULL DEFAULT FALSE;
	COMMENT ON COLUMN public.tbl_gruppe.direktinskription IS 'Verwendung fuer direkte Zuweisung zu Lehreinheit'";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_gruppe: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.tbl_gruppe: Spalte direktinskription hinzugefuegt';
}

// Spalte aktiv für tbl_betreuerart
if(!$result = @$db->db_query("SELECT aktiv FROM lehre.tbl_betreuerart LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_betreuerart ADD COLUMN aktiv boolean NOT NULL DEFAULT TRUE;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_betreuerart: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_betreuerart: Spalte aktiv hinzugefuegt';
}

// Spalte aktiv für tbl_projekttyp
if(!$result = @$db->db_query("SELECT aktiv FROM lehre.tbl_projekttyp LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_projekttyp ADD COLUMN aktiv boolean NOT NULL DEFAULT TRUE;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_projekttyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_projekttyp: Spalte aktiv hinzugefuegt';
}

// Remove NOT NULL constraint on aufmerksamdurch_kurzbz on public.tbl_prestudent
if($result = @$db->db_query("SELECT is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_prestudent' AND COLUMN_NAME = 'aufmerksamdurch_kurzbz' AND is_nullable = 'NO'"))
{
	if($db->db_num_rows($result) > 0)
	{
		$qry = "ALTER TABLE public.tbl_prestudent ALTER COLUMN aufmerksamdurch_kurzbz DROP NOT NULL;";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_prestudent '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Removed NOT NULL constraint on "aufmerksamdurch_kurzbz" from public.tbl_prestudent<br>';
	}
}

// Spalte Zugangscode zu vw_msg_vars hinzufügen
if(!$result = @$db->db_query('SELECT "Zugangscode" FROM public.vw_msg_vars WHERE person_id=-1 LIMIT 1'))
{
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
			   orgform_kurzbz AS "Orgform",
			   p.zugangscode AS "Zugangscode"
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
	);
	';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.vw_msg_vars zugangscode added';
}

// Spalte Zugangscode zu vw_msg_vars_person hinzufügen
if(!$result = @$db->db_query('SELECT "Zugangscode" FROM public.vw_msg_vars_person LIMIT 1'))
{
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
						   kt.kontakt AS "Telefon",
							( SELECT tbl_studiensemester.bezeichnung
							       FROM tbl_studiensemester
							      WHERE "substring"(tbl_studiensemester.studiensemester_kurzbz::text, 1, 2) = \'WS\'::text AND (tbl_studiensemester.start >= now() AND tbl_studiensemester.ende >= now() OR tbl_studiensemester.start <= now() AND tbl_studiensemester.ende >= now())
							      ORDER BY tbl_studiensemester.start
							     LIMIT 1) AS "WS naechstes",
							( SELECT tbl_studiensemester.bezeichnung
							       FROM tbl_studiensemester
							      WHERE "substring"(tbl_studiensemester.studiensemester_kurzbz::text, 1, 2) = \'WS\'::text AND (tbl_studiensemester.start >= now() AND tbl_studiensemester.ende >= now() OR tbl_studiensemester.start <= now() AND tbl_studiensemester.ende >= now())
							      ORDER BY tbl_studiensemester.start
							     OFFSET 1
							     LIMIT 1) AS "WS uebernaechstes",
							p.zugangscode as "Zugangscode"
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
		echo '<br>public.vw_msg_vars_person zugangscode added';
}

// Spalte Zugangscode zu vw_msg_vars_person hinzufügen
// Fachbereich entfernen
if(!$result = @$db->db_query('SELECT lehrfach_oe_kurzbz FROM campus.vw_lehreinheit LIMIT 1'))
{
	$qry = "
		DROP VIEW campus.vw_lehreinheit;
		CREATE OR REPLACE VIEW campus.vw_lehreinheit as
		SELECT
		tbl_lehrveranstaltung.studiengang_kz AS lv_studiengang_kz, tbl_lehrveranstaltung.semester AS lv_semester, tbl_lehrveranstaltung.kurzbz AS lv_kurzbz, tbl_lehrveranstaltung.bezeichnung AS lv_bezeichnung, tbl_lehrveranstaltung.ects AS lv_ects, tbl_lehrveranstaltung.lehreverzeichnis AS lv_lehreverzeichnis,
		tbl_lehrveranstaltung.planfaktor AS lv_planfaktor, tbl_lehrveranstaltung.planlektoren AS lv_planlektoren, tbl_lehrveranstaltung.planpersonalkosten AS lv_planpersonalkosten, tbl_lehrveranstaltung.plankostenprolektor AS lv_plankostenprolektor, tbl_lehrveranstaltung.orgform_kurzbz AS lv_orgform_kurzbz,
		tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrveranstaltung_id, tbl_lehreinheit.studiensemester_kurzbz, tbl_lehreinheit.lehrform_kurzbz, tbl_lehreinheit.stundenblockung, tbl_lehreinheit.wochenrythmus, tbl_lehreinheit.start_kw, tbl_lehreinheit.raumtyp, tbl_lehreinheit.raumtypalternativ, tbl_lehreinheit.lehre,
		tbl_lehreinheit.unr, tbl_lehreinheit.lvnr, tbl_lehreinheitmitarbeiter.lehrfunktion_kurzbz, tbl_lehreinheit.insertamum, tbl_lehreinheit.insertvon, tbl_lehreinheit.updateamum, tbl_lehreinheit.updatevon,
		lehrfach.lehrveranstaltung_id AS lehrfach_id,
		lehrfach.oe_kurzbz as lehrfach_oe_kurzbz,
		lehrfach.kurzbz AS lehrfach, lehrfach.bezeichnung AS lehrfach_bez, lehrfach.farbe,
		tbl_lehrveranstaltung.aktiv, lehrfach.sprache, tbl_lehreinheitmitarbeiter.mitarbeiter_uid, tbl_lehreinheitmitarbeiter.semesterstunden, tbl_lehrveranstaltung.semesterstunden AS lv_semesterstunden, tbl_lehreinheitmitarbeiter.planstunden, tbl_lehreinheitmitarbeiter.stundensatz, tbl_lehreinheitmitarbeiter.faktor,
		tbl_lehreinheit.anmerkung, tbl_mitarbeiter.kurzbz AS lektor, tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, tbl_lehreinheitgruppe.verband, tbl_lehreinheitgruppe.gruppe, tbl_lehreinheitgruppe.gruppe_kurzbz,
		tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bez, tbl_studiengang.typ AS stg_typ, tbl_lehreinheitmitarbeiter.anmerkung AS anmerkunglektor, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz,
		tbl_lehrveranstaltung.bezeichnung_english AS lv_bezeichnung_english, tbl_lehrveranstaltung.lehrtyp_kurzbz
		   FROM lehre.tbl_lehreinheit
		   JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
		   JOIN lehre.tbl_lehrveranstaltung lehrfach ON (tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
		   JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)
		   JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid)
		   JOIN lehre.tbl_lehreinheitgruppe USING (lehreinheit_id)
		   JOIN public.tbl_studiengang ON tbl_lehreinheitgruppe.studiengang_kz = tbl_studiengang.studiengang_kz;
		GRANT SELECT ON campus.vw_lehreinheit TO admin;
		GRANT SELECT ON campus.vw_lehreinheit TO vilesci;
		GRANT SELECT ON campus.vw_lehreinheit TO web;
	";
	if(!$db->db_query($qry))
		echo '<strong>campus.vw_lehreinheit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.vw_lehreinheit lehrfach_oe_kurzbz added, fachbereich_kurzbz removed';
}

// Berechtigungen fuer web User auf testtool.tbl_kategorie
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_kategorie' AND table_schema='testtool' AND grantee='web' AND privilege_type='SELECT'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "GRANT SELECT, INSERT, UPDATE, DELETE ON testtool.tbl_kategorie TO web;";

		if(!$db->db_query($qry))
			echo '<strong>Testtool Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Web User fuer testtool.tbl_kategorie berechtigt';
	}
}


// Spalte gesperrt in testtool.tbl_pruefling
if(!$result = @$db->db_query("SELECT gesperrt FROM testtool.tbl_pruefling LIMIT 1;"))
{
	$qry = "ALTER TABLE testtool.tbl_pruefling ADD COLUMN gesperrt boolean NOT NULL DEFAULT false;";

	if(!$db->db_query($qry))
		echo '<strong>testtool.tbl_pruefling: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>testtool.tbl_pruefling: Spalte gesperrt hinzugefuegt!<br>';
}


// Kategorisierung von Services
if(!$result = @$db->db_query("SELECT * FROM public.tbl_servicekategorie LIMIT 1"))
{
	$qry = "CREATE TABLE public.tbl_servicekategorie(
			servicekategorie_kurzbz varchar(32) NOT NULL,
			bezeichnung varchar(256),
			sort smallint
		);
		ALTER TABLE public.tbl_servicekategorie ADD CONSTRAINT pk_servicekategorie PRIMARY KEY (servicekategorie_kurzbz);
		GRANT SELECT ON public.tbl_servicekategorie TO vilesci;
		GRANT SELECT ON public.tbl_servicekategorie TO web;
		ALTER TABLE public.tbl_service ADD COLUMN servicekategorie_kurzbz varchar(32);
		ALTER TABLE public.tbl_service ADD CONSTRAINT fk_service_servicekategorie FOREIGN KEY (servicekategorie_kurzbz) REFERENCES public.tbl_servicekategorie (servicekategorie_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		INSERT INTO public.tbl_servicekategorie(servicekategorie_kurzbz, bezeichnung, sort) VALUES('kritisch','Kritisch', 1);
		INSERT INTO public.tbl_servicekategorie(servicekategorie_kurzbz, bezeichnung, sort) VALUES('normal','für täglichen Betrieb nicht kritisch', 2);
		INSERT INTO public.tbl_servicekategorie(servicekategorie_kurzbz, bezeichnung, sort) VALUES('unkritisch','unkritisch für Lehrbetrieb', 3);
		";

	if(!$db->db_query($qry))
		echo '<strong>Servicekategorie: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Servicekategorie zu Services hinzugefügt';
}

$qry_column_desc = "
	SELECT
		pgd.description
	FROM
		pg_catalog.pg_statio_all_tables as st
		JOIN pg_catalog.pg_description pgd ON (pgd.objoid=st.relid)
		JOIN information_schema.columns c ON (pgd.objsubid=c.ordinal_position AND  c.table_schema=st.schemaname AND c.table_name=st.relname)
	WHERE
		table_schema = 'lehre' AND table_name = 'tbl_projektarbeit' AND column_name='faktor'";
if($result = $db->db_query($qry_column_desc))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "
			COMMENT ON COLUMN lehre.tbl_projektarbeit.faktor IS 'DEPRECATED';
			COMMENT ON COLUMN lehre.tbl_projektarbeit.stundensatz IS 'DEPRECATED';
			COMMENT ON COLUMN lehre.tbl_projektarbeit.gesamtstunden IS 'DEPRECATED';
			";

		if(!$db->db_query($qry))
			echo '<strong>tbl_projektarbeit Comment: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>tbl_projektarbeit: faktor, stundensatz und gesamtstunden als deprecated markiert';
	}
}

// App 'budget' hinzufügen
if($result = $db->db_query("SELECT 1 FROM system.tbl_app WHERE app='budget'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "INSERT INTO system.tbl_app(app) VALUES('budget');";

		if(!$db->db_query($qry))
			echo '<strong>App: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue App budget in system.tbl_app hinzugefügt';
	}
}

// Neue Tabelle Nationengruppe
if (!$result = @$db->db_query("SELECT 1 FROM bis.tbl_nationengruppe LIMIT 1"))
{
	$qry = "CREATE TABLE bis.tbl_nationengruppe
			(
				nationengruppe_kurzbz character varying(16) NOT NULL,
				nationengruppe_bezeichnung character varying(128),
				aktiv boolean DEFAULT TRUE
			);

			GRANT SELECT, INSERT, UPDATE, DELETE ON bis.tbl_nationengruppe TO vilesci;
			GRANT SELECT ON bis.tbl_nationengruppe TO web;

			ALTER TABLE bis.tbl_nationengruppe ADD CONSTRAINT pk_nationengruppe_nationengruppe_kurzbz PRIMARY KEY (nationengruppe_kurzbz);";

	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_nationengruppe '.$db->db_last_error().'</strong><br>';
	else
		echo 'Neue Tabelle: bis.tbl_nationengruppe<br>';
}

// Spalte nationengruppe_kurzbz für tbl_nation
if(!$result = @$db->db_query("SELECT nationengruppe_kurzbz FROM bis.tbl_nation LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_nation ADD COLUMN nationengruppe_kurzbz character varying(16);

			ALTER TABLE bis.tbl_nation ADD CONSTRAINT fk_tbl_nation_nationengruppe_kurzbz FOREIGN KEY (nationengruppe_kurzbz) REFERENCES bis.tbl_nationengruppe(nationengruppe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_nation: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_nation: Spalte nationengruppe_kurzbz hinzugefuegt';
}

// Spalte nationengruppe_kurzbz für tbl_bewerbungstermine
if(!$result = @$db->db_query("SELECT nationengruppe_kurzbz FROM public.tbl_bewerbungstermine LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_bewerbungstermine ADD COLUMN nationengruppe_kurzbz character varying(16);

			ALTER TABLE public.tbl_bewerbungstermine ADD CONSTRAINT fk_tbl_bewerbungstermine_nationengruppe_kurzbz FOREIGN KEY (nationengruppe_kurzbz) REFERENCES bis.tbl_nationengruppe(nationengruppe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_bewerbungstermine: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_bewerbungstermine: Spalte nationengruppe_kurzbz hinzugefuegt';
}

// Spalte bezeichnung_mehrsprachig in bis.tbl_orgform
if(!$result = @$db->db_query("SELECT bezeichnung_mehrsprachig FROM bis.tbl_orgform LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_orgform ADD COLUMN bezeichnung_mehrsprachig varchar(255)[];";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_orgform '.$db->db_last_error().'</strong><br>';
	else
		echo 'bis.tbl_orgform: Spalte bezeichnung_mehrsprachig hinzugefuegt!<br>';

	// Bezeichnung_mehrsprachig aus existierender Bezeichnung vorausfuellen. Ein Eintrag fuer jede Sprache mit Content aktiv.
	$qry_help = "SELECT index FROM public.tbl_sprache WHERE content=TRUE;";
	if(!$result = $db->db_query($qry_help))
		echo '<strong>tbl_orgform bezeichnung_mehrsprachig: Fehler beim ermitteln der Sprachen: '.$db->db_last_error().'</strong>';
	else
	{
		$qry='';
		while($row = $db->db_fetch_object($result))
			$qry.= "UPDATE bis.tbl_orgform set bezeichnung_mehrsprachig[".$row->index."] = bezeichnung;";

		if(!$db->db_query($qry))
			echo '<strong>Setzen der bezeichnung_mehrsprachig fehlgeschlagen: '.$db->db_last_error().'</strong><br>';
		else
			echo 'bis.tbl_orgform: bezeichnung_mehrprachig automatisch aus existierender Bezeichnung uebernommen<br>';
	}
}

// Spalte vertragsstunden für tbl_vertrag
if(!$result = @$db->db_query("SELECT vertragsstunden FROM lehre.tbl_vertrag LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_vertrag ADD COLUMN vertragsstunden NUMERIC(5,2);";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_vertrag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_vertrag: Spalte vertragsstunden hinzugefuegt';
}

// Spalte vertragsstunden_studiensemester_kurzbz für tbl_vertrag
if(!$result = @$db->db_query("SELECT vertragsstunden_studiensemester_kurzbz FROM lehre.tbl_vertrag LIMIT 1"))
{
    $qry = "
		ALTER TABLE lehre.tbl_vertrag
			ADD COLUMN vertragsstunden_studiensemester_kurzbz VARCHAR(16);

		ALTER TABLE lehre.tbl_vertrag
			ADD CONSTRAINT fk_vertrag_vertragsstunden_studiensemester_kurzbz
				FOREIGN KEY (vertragsstunden_studiensemester_kurzbz)
					REFERENCES public.tbl_studiensemester (studiensemester_kurzbz)
					ON UPDATE CASCADE ON DELETE RESTRICT;
	";

    if (!$db->db_query($qry))
        echo '<strong>lehre.tbl_vertrag: ' . $db->db_last_error() . '</strong><br>';
    else
        echo '<br>lehre.tbl_vertrag: Spalte vertragsstunden_studiensemester_kurzbz hinzugefuegt';
}

// Create SEQUENCE tbl_zeitaufzeichnung_gd_id
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'tbl_zeitaufzeichnung_gd_id_seq'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = '
			CREATE SEQUENCE campus.tbl_zeitaufzeichnung_gd_id_seq
			    START WITH 1
			    INCREMENT BY 1
			    NO MAXVALUE
			    NO MINVALUE
			    CACHE 1;
			';
		if (!$db->db_query($qry))
			echo '<strong>campus.tbl_zeitaufzeichnung_gd_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created sequence: campus.tbl_zeitaufzeichnung_gd_id_seq';

		// GRANT SELECT, UPDATE ON SEQUENCE campus.tbl_zeitaufzeichnung_gd_id_seq TO web;
		$qry = 'GRANT SELECT, UPDATE ON SEQUENCE campus.tbl_zeitaufzeichnung_gd_id_seq TO web;';
		if (!$db->db_query($qry))
			echo '<strong>campus.tbl_zeitaufzeichnung_gd_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted privileges to <strong>web</strong> on campus.tbl_zeitaufzeichnung_gd_id_seq';

		// GRANT SELECT, UPDATE ON SEQUENCE campus.tbl_zeitaufzeichnung_gd_id_seq TO vilesci;
		$qry = 'GRANT SELECT, UPDATE ON SEQUENCE campus.tbl_zeitaufzeichnung_gd_id_seq TO vilesci;';
		if (!$db->db_query($qry))
			echo '<strong>campus.tbl_zeitaufzeichnung_gd_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted privileges to <strong>vilesci</strong> on campus.tbl_zeitaufzeichnung_gd_id_seq';
	}
}

// Create TABLE campus.tbl_zeitaufzeichnung_gd
if(!@$db->db_query("SELECT 0 FROM campus.tbl_zeitaufzeichnung_gd WHERE 0 = 1")) {
	$qry = '
		CREATE TABLE campus.tbl_zeitaufzeichnung_gd
		(
			zeitaufzeichnung_gd_id integer     NOT NULL DEFAULT NEXTVAL(\'campus.tbl_zeitaufzeichnung_gd_id_seq\'::regclass),
			uid                    varchar(32) NOT NULL,
			studiensemester_kurzbz varchar(16) NOT NULL,
			selbstverwaltete_pause boolean     NOT NULL,
			insertamum             TIMESTAMP            DEFAULT NOW(),
			insertvon              varchar(32),
			updateamum             TIMESTAMP,
			updatevon              varchar(32)
		);

		ALTER TABLE campus.tbl_zeitaufzeichnung_gd ADD CONSTRAINT pk_zeitaufzeichnung_gd_zeitaufzeichnung_gd_id PRIMARY KEY (zeitaufzeichnung_gd_id);

		ALTER TABLE campus.tbl_zeitaufzeichnung_gd ADD CONSTRAINT fk_zeitaufzeichnung_gd_uid FOREIGN KEY (uid) REFERENCES public.tbl_benutzer(uid) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE campus.tbl_zeitaufzeichnung_gd ADD CONSTRAINT fk_zeitaufzeichnung_gd_studiensemester_kurzbz FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE campus.tbl_zeitaufzeichnung_gd ADD CONSTRAINT uk_zeitaufzeichnung_gd_uid_stsem UNIQUE (uid, studiensemester_kurzbz);

		COMMENT ON TABLE campus.tbl_zeitaufzeichnung_gd IS \'Table to manage the lectors parted working times; gd = Geteilte Dienste\';
		COMMENT ON COLUMN campus.tbl_zeitaufzeichnung_gd.selbstverwaltete_pause IS \'Lectors (dis-)agreement to self-manage breaks\';

		';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_zeitaufzeichnung_gd ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Created table campus.tbl_zeitaufzeichnung_gd';


	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_zeitaufzeichnung_gd TO web;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_zeitaufzeichnung_gd TO web;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_zeitaufzeichnung_gd ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on campus.tbl_zeitaufzeichnung_gd';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_zeitaufzeichnung_gd TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE campus.tbl_zeitaufzeichnung_gd TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_zeitaufzeichnung_gd ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on campus.tbl_zeitaufzeichnung_gd';
}


// Insert 'bestellt' to tbl_vertragsstatus
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_vertragsstatus WHERE vertragsstatus_kurzbz = 'bestellt';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO lehre.tbl_vertragsstatus(vertragsstatus_kurzbz, bezeichnung) VALUES('bestellt', 'Bestellt');";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_vertragsstatus '.$db->db_last_error().'</strong><br>';
		else
			echo 'lehre.tbl_vertragsstatus: Added value \'bestellt\'<br>';
	}
}

// Insert 'erteilt' to tbl_vertragsstatus
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_vertragsstatus WHERE vertragsstatus_kurzbz = 'erteilt';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO lehre.tbl_vertragsstatus(vertragsstatus_kurzbz, bezeichnung) VALUES('erteilt', 'Erteilt');";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_vertragsstatus '.$db->db_last_error().'</strong><br>';
		else
			echo 'lehre.tbl_vertragsstatus: Added value \'erteilt\'<br>';
	}
}

// Insert 'akzeptiert' to tbl_vertragsstatus
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_vertragsstatus WHERE vertragsstatus_kurzbz = 'akzeptiert';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO lehre.tbl_vertragsstatus(vertragsstatus_kurzbz, bezeichnung) VALUES('akzeptiert', 'Akzeptiert');";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_vertragsstatus '.$db->db_last_error().'</strong><br>';
		else
			echo 'lehre.tbl_vertragsstatus: Added value \'akzeptiert\'<br>';
	}
}

// Insert 'Betreuung' to tbl_vertragstyp
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_vertragstyp WHERE vertragstyp_kurzbz = 'Betreuung';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO lehre.tbl_vertragstyp(vertragstyp_kurzbz, bezeichnung) VALUES('Betreuung', 'Betreuung');";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_vertragstyp '.$db->db_last_error().'</strong><br>';
		else
			echo 'lehre.tbl_vertragstyp: Added value \'Betreuung\'<br>';
	}
}

// Add permission to order lehrauftrag (lehrauftrag_bestellen)
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'lehre/lehrauftrag_bestellen';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('lehre/lehrauftrag_bestellen', 'Lehrauftrag bestellen');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for lehre/lehrauftrag_bestellen<br>';
	}
}

// Add permission to approve lehrauftrag (lehrauftrag_erteilen)
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'lehre/lehrauftrag_erteilen';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('lehre/lehrauftrag_erteilen', 'Lehrauftrag erteilen');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for lehre/lehrauftrag_erteilen<br>';
	}
}

// Add permission to accept lehrauftrag (lehrauftrag_akzeptieren)
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'lehre/lehrauftrag_akzeptieren';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('lehre/lehrauftrag_akzeptieren', 'Lehrauftrag akzeptieren');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for lehre/lehrauftrag_akzeptieren<br>';
	}
}

// Add column Stufe to tbl_dokumentstudiengang
if(!$result = @$db->db_query("SELECT stufe FROM public.tbl_dokumentstudiengang LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_dokumentstudiengang ADD COLUMN stufe smallint;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_dokumentstudiengang: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_dokumentstudiengang: Spalte stufe hinzugefuegt';
}

// Create TABLE public.tbl_variablenname
if(!@$db->db_query("SELECT 0 FROM public.tbl_variablenname WHERE 0 = 1"))
{
	$qry = '
		CREATE TABLE public.tbl_variablenname
		(
			name varchar(64)     NOT NULL constraint pk_tbl_variablenname primary key,
			defaultwert                    varchar(64)
		);
		COMMENT ON TABLE public.tbl_variablenname IS \'Namen aller benutzerdefinierten Variablen\';

		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'termin_export_db_stpl_table\', null);
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'sleep_time\', null);
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'semester_aktuell\', null);
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'reihungstestverwaltung_punkteberechnung\', null);
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'number_displayed_past_studiensemester\', null);
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'max_kollision\', \'0\');
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'locale\', \'de-AT\');
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'kontofilterstg\', \'false\');
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'kollision_student\', \'false\');
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'infocenter_studiensemester\', null);
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'ignore_zeitsperre\', \'false\');
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'ignore_reservierung\', \'false\');
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'ignore_kollision\', \'false\');
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'fas_id\', null);
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'fasfunktionfilter\', \'alle\');
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'emailadressentrennzeichen\', null);
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'db_stpl_table\', \'stundenplan\');
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'allow_lehrstunde_drop\', \'false\');
		INSERT INTO public.tbl_variablenname (name, defaultwert) VALUES (\'alle_unr_mitladen\', \'false\');

		ALTER TABLE public.tbl_variable ADD CONSTRAINT variablenname_variable FOREIGN KEY (name) REFERENCES public.tbl_variablenname(name) ON UPDATE CASCADE ON DELETE RESTRICT;
		';

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_variablenname ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Created public.tbl_variablenname';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE public.tbl_variablenname TO web;
	$qry = 'GRANT SELECT ON TABLE public.tbl_variablenname TO web;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_variablenname ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on public.tbl_variablenname';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE public.tbl_variablenname TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE public.tbl_variablenname TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_variablenname ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on public.tbl_variablenname';
}

// Add new name type in public.tbl_variablenname
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_variablenname WHERE name = 'infocenter_studiensgangtyp';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_variablenname(name, defaultwert) VALUES('infocenter_studiensgangtyp', 'b');";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_variablenname '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_variablenname: Added name "infocenter_studiensgangtyp"<br>';
	}
}

// Add new name type in public.tbl_variablenname
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_variablenname WHERE name = 'projektuebersicht_studiensemester';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_variablenname(name, defaultwert) VALUES('projektuebersicht_studiensemester', null);";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_variablenname '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_variablenname: Added name "projektuebersicht_studiensemester"<br>';
	}
}

// Add column projektphase_id to tbl_zeitaufzeichnung
if(!$result = @$db->db_query("SELECT projektphase_id FROM campus.tbl_zeitaufzeichnung LIMIT 1"))
{
	$qry = "ALTER TABLE campus.tbl_zeitaufzeichnung ADD COLUMN projektphase_id bigint;
			ALTER TABLE campus.tbl_zeitaufzeichnung ADD CONSTRAINT fk_zeitaufzeichnung_projektphase FOREIGN KEY (projektphase_id) REFERENCES fue.tbl_projektphase (projektphase_id) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_zeitaufzeichnung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_zeitaufzeichnung: Spalte projektphase_id hinzugefuegt';
}

// Add new webservice type in system.tbl_webservicetyp
if ($result = @$db->db_query("SELECT 1 FROM system.tbl_webservicetyp WHERE webservicetyp_kurzbz = 'job';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_webservicetyp(webservicetyp_kurzbz, beschreibung) VALUES('job', 'Cronjob');";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_webservicetyp '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_webservicetyp: Added webservice type "job"<br>';
	}
}

// insert und update fuer public.tbl_vorlage
if(!@$db->db_query("SELECT insertamum FROM public.tbl_vorlage LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_vorlage ADD COLUMN insertamum timestamp;
			ALTER TABLE public.tbl_vorlage ADD COLUMN insertvon varchar(32);
			ALTER TABLE public.tbl_vorlage ADD COLUMN updateamum timestamp;
			ALTER TABLE public.tbl_vorlage ADD COLUMN updatevon varchar(32);";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_vorlage: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalten insertamum,insertvon,updateamum und updatevon in public.tbl_vorlage hinzugefügt';
}

// Spalte archivierbar und signierbar in public.tbl_vorlage auf TRUE gesetzt bei Vorlage: Zertifikat
if($result = @$db->db_query("SELECT 1 FROM public.tbl_vorlage WHERE vorlage_kurzbz = 'Zertifikat' AND archivierbar AND signierbar;"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "UPDATE public.tbl_vorlage SET archivierbar = TRUE, signierbar = TRUE WHERE vorlage_kurzbz = 'Zertifikat';";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_vorlage '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_vorlage: Spalten archivierbar und signierbar auf TRUE gesetzt bei Vorlage: Zertifikat<br>';
	}
}

// Spalte archivierbar und signierbar in public.tbl_vorlage auf TRUE gesetzt bei Vorlage: LVZeugnis
if($result = @$db->db_query("SELECT 1 FROM public.tbl_vorlage WHERE vorlage_kurzbz = 'LVZeugnis' AND archivierbar AND signierbar;"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "UPDATE public.tbl_vorlage SET archivierbar = TRUE, signierbar = TRUE WHERE vorlage_kurzbz = 'LVZeugnis';";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_vorlage '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_vorlage: Spalten archivierbar und signierbar auf TRUE gesetzt bei Vorlage: LVZeugnis<br>';
	}
}

// Spalte archivierbar und signierbar in public.tbl_vorlage auf TRUE gesetzt bei Vorlage: LVZeugnisEng
if($result = @$db->db_query("SELECT 1 FROM public.tbl_vorlage WHERE vorlage_kurzbz = 'LVZeugnisEng' AND archivierbar AND signierbar;"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "UPDATE public.tbl_vorlage SET archivierbar = TRUE, signierbar = TRUE WHERE vorlage_kurzbz = 'LVZeugnisEng';";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_vorlage '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_vorlage: Spalten archivierbar und signierbar auf TRUE gesetzt bei Vorlage: LVZeugnisEng<br>';
	}
}

// Spalte stud_selfservice in public.tbl_vorlage auf TRUE gesetzt bei Vorlage: Zertifikat
if($result = @$db->db_query("SELECT 1 FROM public.tbl_vorlage WHERE vorlage_kurzbz = 'Zertifikat' AND stud_selfservice;"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "UPDATE public.tbl_vorlage SET stud_selfservice = TRUE WHERE vorlage_kurzbz = 'Zertifikat';";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_vorlage '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_vorlage: Spalte stud_selfservice auf TRUE gesetzt bei Vorlage: Zertifikat<br>';
	}
}

// Spalte stud_selfservice in public.tbl_vorlage auf TRUE gesetzt bei Vorlage: LVZeugnis
if($result = @$db->db_query("SELECT 1 FROM public.tbl_vorlage WHERE vorlage_kurzbz = 'LVZeugnis' AND stud_selfservice;"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "UPDATE public.tbl_vorlage SET stud_selfservice = TRUE WHERE vorlage_kurzbz = 'LVZeugnis';";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_vorlage '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_vorlage: Spalte stud_selfservice auf TRUE gesetzt bei Vorlage: LVZeugnis<br>';
	}
}

// Spalte stud_selfservice in public.tbl_vorlage auf TRUE gesetzt bei Vorlage: LVZeugnisEng
if($result = @$db->db_query("SELECT 1 FROM public.tbl_vorlage WHERE vorlage_kurzbz = 'LVZeugnisEng' AND stud_selfservice;"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "UPDATE public.tbl_vorlage SET stud_selfservice = TRUE WHERE vorlage_kurzbz = 'LVZeugnisEng';";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_vorlage '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_vorlage: Spalte stud_selfservice auf TRUE gesetzt bei Vorlage: LVZeugnisEng<br>';
	}
}

// dokument_kurzbz in public.tbl_dokument auf 32 Zeichen verlängert
if($result = @$db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_dokument' AND COLUMN_NAME = 'dokument_kurzbz' AND character_maximum_length = 32"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = " ALTER TABLE public.tbl_dokument ALTER COLUMN dokument_kurzbz TYPE varchar(32)";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_dokument '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte dokument_kurzbz in public.tbl_dokument auf varchar(32) geändert<br>';
	}
}

// dokument_kurzbz in public.tbl_akte auf 32 Zeichen verlängert
if($result = @$db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_akte' AND COLUMN_NAME = 'dokument_kurzbz' AND character_maximum_length = 32"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = " ALTER TABLE public.tbl_akte ALTER COLUMN dokument_kurzbz TYPE varchar(32)";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_dokument '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte dokument_kurzbz in public.tbl_akte auf varchar(32) geändert<br>';
	}
}


// dokument_kurzbz in campus.tbl_dms auf 32 Zeichen verlängert
if($result = $db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='campus' AND TABLE_NAME='tbl_dms' AND COLUMN_NAME = 'dokument_kurzbz' AND character_maximum_length = 32"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "ALTER TABLE campus.tbl_dms ALTER COLUMN dokument_kurzbz TYPE varchar(32)";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_dms '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte dokument_kurzbz in campus.tbl_dms auf varchar(32) geändert<br>';
	}
}

// dokument_kurzbz in public.tbl_dokumentprestudent auf 32 Zeichen verlängert
if($result = @$db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_dokumentprestudent' AND COLUMN_NAME = 'dokument_kurzbz' AND character_maximum_length = 32"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = " ALTER TABLE public.tbl_dokumentprestudent ALTER COLUMN dokument_kurzbz TYPE varchar(32)";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_dokumentprestudent '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte dokument_kurzbz in public.tbl_dokumentprestudent auf varchar(32) geändert<br>';
	}
}

// dokument_kurzbz in public.tbl_dokumentstudiengang auf 32 Zeichen verlängert
if($result = @$db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_dokumentstudiengang' AND COLUMN_NAME = 'dokument_kurzbz' AND character_maximum_length = 32"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = " ALTER TABLE public.tbl_dokumentstudiengang ALTER COLUMN dokument_kurzbz TYPE varchar(32)";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_dokumentstudiengang '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte dokument_kurzbz in public.tbl_dokumentstudiengang auf varchar(32) geändert<br>';
	}
}

// dokument_kurzbz in public.tbl_vorlage auf 32 Zeichen verlängert
if($result = @$db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_vorlage' AND COLUMN_NAME = 'dokument_kurzbz' AND character_maximum_length = 32"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = " ALTER TABLE public.tbl_vorlage ALTER COLUMN dokument_kurzbz TYPE varchar(32)";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_vorlage '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte dokument_kurzbz in public.tbl_vorlage auf varchar(32) geändert<br>';
	}
}

// dokument_kurzbz in public.tbl_vorlagedokument auf 32 Zeichen verlängert
if($result = @$db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_vorlagedokument' AND COLUMN_NAME = 'dokument_kurzbz' AND character_maximum_length = 32"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = " ALTER TABLE public.tbl_vorlagedokument ALTER COLUMN dokument_kurzbz TYPE varchar(32)";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_vorlage '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte dokument_kurzbz in public.tbl_vorlagedokument auf varchar(32) geändert<br>';
	}
}

// Neues Dokument hinzugefuegt LVZeugnisEng
if($result = @$db->db_query("SELECT 1 FROM public.tbl_dokument WHERE dokument_kurzbz = 'LVZeugnisEng'"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_dokument (dokument_kurzbz, bezeichnung, bezeichnung_mehrsprachig) VALUES ('LVZeugnisEng', 'Lehrveranstaltungszeugnis Englisch', '{\"Lehrveranstaltungszeugnis\",\"Course Certificate\"}')";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_dokument '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_dokument: LVZeugnisEng hinzugefügt<br>';
	}
}

// Neues Dokument hinzugefuegt LVZeugnis
if($result = @$db->db_query("SELECT 1 FROM public.tbl_dokument WHERE dokument_kurzbz = 'LVZeugnis'"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_dokument (dokument_kurzbz, bezeichnung, bezeichnung_mehrsprachig) VALUES ('LVZeugnis', 'Lehrveranstaltungszeugnis', '{\"Lehrveranstaltungszeugnis\",\"Course Certificate\"}')";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_dokument '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_dokument: LVZeugnis hinzugefügt<br>';
	}
}

// Neues Dokument hinzugefuegt Zertifikat
if($result = @$db->db_query("SELECT 1 FROM public.tbl_dokument WHERE dokument_kurzbz = 'Zertifikat'"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_dokument (dokument_kurzbz, bezeichnung, bezeichnung_mehrsprachig) VALUES ('Zertifikat', 'Zertifikat', '{\"Zertifikat\",\"Certificate\"}')";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_dokument '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_dokument: Zertifikat hinzugefügt<br>';
	}
}

// insert und update fuer public.tbl_vorlagestudiengang
if(!@$db->db_query("SELECT insertamum FROM public.tbl_vorlagestudiengang LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_vorlagestudiengang ADD COLUMN insertamum timestamp;
			ALTER TABLE public.tbl_vorlagestudiengang ADD COLUMN insertvon varchar(32);
			ALTER TABLE public.tbl_vorlagestudiengang ADD COLUMN updateamum timestamp;
			ALTER TABLE public.tbl_vorlagestudiengang ADD COLUMN updatevon varchar(32);";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_vorlagestudiengang: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalten insertamum,insertvon,updateamum und updatevon in public.tbl_vorlagestudiengang hinzugefügt';
}

// Add column ects_erworben to bis.tbl_bisio
if(!$result = @$db->db_query("SELECT ects_erworben FROM bis.tbl_bisio LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_bisio ADD COLUMN ects_erworben numeric(5,2);";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_bisio: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_bisio: Spalte ects_erworben hinzugefuegt';
}

// Add column ects_angerechnet to bis.tbl_bisio
if(!$result = @$db->db_query("SELECT ects_angerechnet FROM bis.tbl_bisio LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_bisio ADD COLUMN ects_angerechnet numeric(5,2);";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_bisio: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_bisio: Spalte ects_angerechnet hinzugefuegt';
}

// Add Table bis.tbl_aufenthaltfoerderung
if(!$result = @$db->db_query("SELECT 1 FROM bis.tbl_aufenthaltfoerderung LIMIT 1"))
{
	$qry = "
		CREATE TABLE bis.tbl_aufenthaltfoerderung
		(
			aufenthaltfoerderung_code integer NOT NULL,
			bezeichnung varchar(64)
		);
		ALTER TABLE bis.tbl_aufenthaltfoerderung ADD CONSTRAINT pk_aufenthaltfoerderung PRIMARY KEY (aufenthaltfoerderung_code);

		COMMENT ON TABLE bis.tbl_aufenthaltfoerderung IS 'Key-Table of Outgoing Sponsorship';
		INSERT INTO bis.tbl_aufenthaltfoerderung(aufenthaltfoerderung_code, bezeichnung) VALUES(1,'EU-Förderung');
		INSERT INTO bis.tbl_aufenthaltfoerderung(aufenthaltfoerderung_code, bezeichnung) VALUES(2,'Beihilfe von Bund, Land, Gemeinde');
		INSERT INTO bis.tbl_aufenthaltfoerderung(aufenthaltfoerderung_code, bezeichnung) VALUES(3,'Förderung durch Universität/Hochschule');
		INSERT INTO bis.tbl_aufenthaltfoerderung(aufenthaltfoerderung_code, bezeichnung) VALUES(4,'andere Förderung');
		INSERT INTO bis.tbl_aufenthaltfoerderung(aufenthaltfoerderung_code, bezeichnung) VALUES(5,'keine Förderung');

		CREATE TABLE bis.tbl_bisio_aufenthaltfoerderung
		(
			bisio_id integer NOT NULL,
			aufenthaltfoerderung_code integer NOT NULL
		);
		ALTER TABLE bis.tbl_bisio_aufenthaltfoerderung ADD CONSTRAINT pk_aufenthaltfoerderung_bisio PRIMARY KEY (bisio_id, aufenthaltfoerderung_code);
		COMMENT ON TABLE bis.tbl_bisio_aufenthaltfoerderung IS 'Connects Outgoing Program with Sponsorship';

		ALTER TABLE bis.tbl_bisio_aufenthaltfoerderung ADD CONSTRAINT fk_tbl_bisio_aufenthaltfoerderung_bisio FOREIGN KEY (bisio_id) REFERENCES bis.tbl_bisio (bisio_id) ON DELETE CASCADE ON UPDATE CASCADE;
		ALTER TABLE bis.tbl_bisio_aufenthaltfoerderung ADD CONSTRAINT fk_tbl_bisio_aufenthaltfoerderung_aufenthaltfoerderung FOREIGN KEY (aufenthaltfoerderung_code) REFERENCES bis.tbl_aufenthaltfoerderung (aufenthaltfoerderung_code) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_aufenthaltfoerderung TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_aufenthaltfoerderung TO vilesci;

		GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_bisio_aufenthaltfoerderung TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_bisio_aufenthaltfoerderung TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_aufenthaltfoerderung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_aufenthaltfoerderung hinzugefügt, Tabelle bis.tbl_bisio_aufenthaltfoerderung hinzugefuegt';
}

// Add table bis.tbl_bisio_zweck
if(!$result = @$db->db_query("SELECT 1 FROM bis.tbl_bisio_zweck LIMIT 1"))
{
	$qry = "
		ALTER TABLE bis.tbl_bisio ALTER COLUMN zweck_code DROP NOT NULL;
		CREATE TABLE bis.tbl_bisio_zweck
		(
			bisio_id integer NOT NULL,
			zweck_code varchar(20) NOT NULL
		);
		COMMENT ON TABLE bis.tbl_bisio_zweck IS 'Connects Internships with Reasons';
		ALTER TABLE bis.tbl_bisio_zweck ADD CONSTRAINT pk_tbl_bisio_zweck PRIMARY KEY (bisio_id, zweck_code);
		ALTER TABLE bis.tbl_bisio_zweck ADD CONSTRAINT fk_tbl_bisio_zweck_bisio FOREIGN KEY (bisio_id) REFERENCES bis.tbl_bisio (bisio_id) ON DELETE CASCADE ON UPDATE CASCADE;
		ALTER TABLE bis.tbl_bisio_zweck ADD CONSTRAINT fk_tbl_bisio_zweck_zweck FOREIGN KEY (zweck_code) REFERENCES bis.tbl_zweck (zweck_code) ON DELETE RESTRICT ON UPDATE CASCADE;

		INSERT INTO bis.tbl_bisio_zweck(bisio_id, zweck_code) SELECT bisio_id, zweck_code FROM bis.tbl_bisio WHERE zweck_code is not null;
		COMMENT ON COLUMN bis.tbl_bisio.zweck_code IS 'DEPRECATED';

		GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_bisio_zweck TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_bisio_zweck TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_bisio_zweck: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_bisio_zweck hinzugefuegt, Spalte bis.tbl_bisio.zweck_code als DEPRECATED markiert.';
}

// Add Column incoming and outgoing to bis.tbl_zweck
// change Datatype of bis.tbl_zweck.bezeichnung from varchar(32) to varchar(64)
if(!$result = @$db->db_query("SELECT incoming FROM bis.tbl_zweck LIMIT 1"))
{
	$qry = "
		ALTER TABLE bis.tbl_zweck ALTER COLUMN bezeichnung TYPE varchar(64);
		ALTER TABLE bis.tbl_zweck ADD COLUMN incoming boolean NOT NULL DEFAULT true;
		ALTER TABLE bis.tbl_zweck ADD COLUMN outgoing boolean NOT NULL DEFAULT true;

		INSERT INTO bis.tbl_zweck(zweck_code, kurzbz, bezeichnung, incoming, outgoing) VALUES(4, 'DMD','Diplom-/Masterarbeit bzw. Dissertation', false, true);
		INSERT INTO bis.tbl_zweck(zweck_code, kurzbz, bezeichnung, incoming, outgoing) VALUES(5, 'SK','Besuch von Sprachkursen', false, true);
		INSERT INTO bis.tbl_zweck(zweck_code, kurzbz, bezeichnung, incoming, outgoing) VALUES(6, 'LT','Lehrtätigkeit', false, true);
	";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_zweck: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_zweck Spalte incoming und outgoing hinzugefügt, neue Codexeinträge ergänzt.';
}

// Add column statistik_kurzbz to system.tbl_filters
if(!$result = @$db->db_query("SELECT statistik_kurzbz FROM system.tbl_filters LIMIT 1"))
{
	$qry = "ALTER TABLE system.tbl_filters ADD COLUMN statistik_kurzbz varchar(64);
			ALTER TABLE system.tbl_filters ADD CONSTRAINT fk_filters_statistik FOREIGN KEY (statistik_kurzbz) REFERENCES public.tbl_statistik (statistik_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_filters: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_filters: Spalte statistik_kurzbz hinzugefuegt';
}

// app reporting hinzufügen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_app WHERE app= 'reporting';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_app(app) VALUES ('reporting');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_app: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>system.tbl_app: Zeile reporting hinzugefuegt!<br>';
	}
}

// Add table fue.tbl_projekttyp
if(!$result = @$db->db_query("SELECT 1 FROM fue.tbl_projekttyp LIMIT 1"))
{
	$qry = "
		CREATE TABLE fue.tbl_projekttyp
		(
			projekttyp_kurzbz varchar(32) NOT NULL,
			bezeichnung varchar(255)
		);
		COMMENT ON TABLE fue.tbl_projekttyp IS 'Project Type';
		ALTER TABLE fue.tbl_projekttyp ADD CONSTRAINT pk_tbl_projekttyp PRIMARY KEY (projekttyp_kurzbz);
		ALTER TABLE fue.tbl_projekt ADD COLUMN projekttyp_kurzbz varchar(32);
		ALTER TABLE fue.tbl_projekt ADD CONSTRAINT fk_tbl_projekt_projekttyp FOREIGN KEY (projekttyp_kurzbz) REFERENCES fue.tbl_projekttyp (projekttyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		INSERT INTO fue.tbl_projekttyp(projekttyp_kurzbz, bezeichnung) VALUES ('fue', 'Forschung und Entwicklung');
		INSERT INTO fue.tbl_projekttyp(projekttyp_kurzbz, bezeichnung) VALUES ('intern', 'Intern');
		INSERT INTO fue.tbl_projekttyp(projekttyp_kurzbz, bezeichnung) VALUES ('internoe', 'Intern Organisationseinheit');

		GRANT SELECT ON TABLE fue.tbl_projekttyp TO web;
		GRANT SELECT ON TABLE fue.tbl_projekttyp TO wawi;
		GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE fue.tbl_projekttyp TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekttyp: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>fue.tbl_projekttyp hinzugefuegt.';
}

// Add column oe_kurzbz to public.tbl_msg_recipient
if(!$result = @$db->db_query("SELECT oe_kurzbz FROM public.tbl_msg_recipient LIMIT 1"))
{
	$qry = 'ALTER TABLE public.tbl_msg_recipient ADD COLUMN oe_kurzbz character varying(32);';
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_msg_recipient: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added column oe_kurzbz to table public.tbl_msg_recipient';

	// FOREIGN KEY fk_tbl_msg_recipient_oe_kurzbz: public.tbl_msg_recipient.oe_kurzbz references public.tbl_organisationseinheit.oe_kurzbz
	if ($result = @$db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'fk_tbl_msg_recipient_oe_kurzbz'"))
	{
		if ($db->db_num_rows($result) == 0)
		{
			$qry = "ALTER TABLE public.tbl_msg_recipient ADD CONSTRAINT fk_tbl_msg_recipient_oe_kurzbz FOREIGN KEY (oe_kurzbz)
					REFERENCES public.tbl_organisationseinheit(oe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;";

			if (!$db->db_query($qry))
				echo '<strong>public.tbl_msg_recipient: '.$db->db_last_error().'</strong><br>';
			else
				echo '<br>public.tbl_msg_recipient: added foreign key on column oe_kurzbz referenced to public.tbl_organisationseinheit(oe_kurzbz)';
		}
	}
}

// Add new webservice type in system.tbl_webservicetyp
if ($result = @$db->db_query("SELECT 1 FROM system.tbl_webservicetyp WHERE webservicetyp_kurzbz = 'API';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_webservicetyp(webservicetyp_kurzbz, beschreibung) VALUES('API', 'Cronjob');";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_webservicetyp '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_webservicetyp: Added webservice type "API"<br>';
	}
}

// Add new Table for gender
// Add FK to public.tbl_person and Drop old Check Constraint
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_geschlecht LIMIT 1"))
{
	$qry = "
		CREATE TABLE public.tbl_geschlecht
		(
			geschlecht character(1) NOT NULL,
			bezeichnung_mehrsprachig varchar(255)[],
			sort smallint
		);
		COMMENT ON TABLE public.tbl_geschlecht IS 'Key-Table of Gender';
		ALTER TABLE public.tbl_geschlecht ADD CONSTRAINT pk_tbl_geschlecht PRIMARY KEY (geschlecht);

		INSERT INTO public.tbl_geschlecht(geschlecht, bezeichnung_mehrsprachig, sort) VALUES ('m', '{\"männlich\",\"male\"}', 1);
		INSERT INTO public.tbl_geschlecht(geschlecht, bezeichnung_mehrsprachig, sort) VALUES ('w', '{\"weiblich\",\"female\"}', 2);
		INSERT INTO public.tbl_geschlecht(geschlecht, bezeichnung_mehrsprachig, sort) VALUES ('x', '{\"divers\",\"divers\"}', 3);
		INSERT INTO public.tbl_geschlecht(geschlecht, bezeichnung_mehrsprachig, sort) VALUES ('u', '{\"unbekannt\",\"unknown\"}', 4);

		ALTER TABLE public.tbl_person ADD CONSTRAINT fk_tbl_person_geschlecht FOREIGN KEY (geschlecht) REFERENCES public.tbl_geschlecht (geschlecht) ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE public.tbl_person DROP CONSTRAINT tbl_person_geschlecht;

		GRANT SELECT ON TABLE public.tbl_geschlecht TO web;
		GRANT SELECT ON TABLE public.tbl_geschlecht TO wawi;
		GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE public.tbl_geschlecht TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_geschlecht: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_geschlecht hinzugefügt. Check Constraint für Geschlecht entfernt.';
}

if($result = $db->db_query("SELECT * FROM pg_proc WHERE proname = 'transform_geschlecht'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = '
		CREATE OR REPLACE FUNCTION transform_geschlecht(character, date) RETURNS character
		LANGUAGE plpgsql
		AS $_$
			DECLARE geschlecht ALIAS FOR $1;
			DECLARE gebdatum ALIAS FOR $2;

			BEGIN
				IF geschlecht=\'x\' THEN
					IF date_part(\'day\', gebdatum)::int%2=0 THEN
						geschlecht:=\'m\';
					ELSE
						geschlecht:=\'w\';
					END IF;
				END IF;
			RETURN geschlecht;
			END;
		$_$;';
		if(!$db->db_query($qry))
			echo '<strong>transform_geschlecht: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Function transform_geschlecht hinzugefügt.';
	}
}

// Add column offset to testtool.tbl_gebiet
if(!$result = @$db->db_query("SELECT offsetpunkte FROM testtool.tbl_gebiet LIMIT 1"))
{
	$qry = "ALTER TABLE testtool.tbl_gebiet ADD COLUMN offsetpunkte numeric(8,4)";

	if(!$db->db_query($qry))
		echo '<strong>testtool.tbl_gebiet: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>testtool.tbl_gebiet: Spalte offsetpunkte hinzugefuegt';
}

// ADD COLUMN offset to testtool.vw_auswertung_ablauf
if(!$result = @$db->db_query("SELECT offsetpunkte FROM testtool.vw_auswertung_ablauf LIMIT 1"))
{
	$qry = '
		CREATE OR REPLACE VIEW testtool.vw_auswertung_ablauf AS (
			SELECT
				tbl_gebiet.gebiet_id,
				tbl_gebiet.bezeichnung AS gebiet,
				tbl_ablauf.reihung,
				tbl_gebiet.maxpunkte,
				tbl_pruefling.pruefling_id,
				tbl_pruefling.prestudent_id,
				tbl_person.vorname,
				tbl_person.nachname,
				tbl_person.gebdatum,
				tbl_person.geschlecht,
				tbl_pruefling.semester,
				upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
				tbl_studiengang.bezeichnung AS stg_bez,
				tbl_pruefling.registriert,
				tbl_pruefling.idnachweis,
				( SELECT sum(tbl_vorschlag.punkte) AS sum
					   FROM testtool.tbl_vorschlag
						 JOIN testtool.tbl_antwort USING (vorschlag_id)
						 JOIN testtool.tbl_frage USING (frage_id)
					  WHERE tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
				) AS punkte,
				tbl_rt_person.rt_id AS reihungstest_id,
				tbl_ablauf.gewicht,
				tbl_studiengang.studiengang_kz,
				tbl_gebiet.offsetpunkte
			FROM
				testtool.tbl_pruefling
			 JOIN testtool.tbl_ablauf ON tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz
			 JOIN testtool.tbl_gebiet USING (gebiet_id)
			 JOIN public.tbl_prestudent USING (prestudent_id)
			 JOIN public.tbl_person USING (person_id)
			 JOIN public.tbl_rt_person USING (person_id)
			 JOIN lehre.tbl_studienplan ON tbl_studienplan.studienplan_id = tbl_rt_person.studienplan_id
			 JOIN lehre.tbl_studienordnung ON tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id
			 JOIN public.tbl_studiengang ON tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz
			WHERE NOT (tbl_ablauf.gebiet_id IN
				(
				SELECT tbl_kategorie.gebiet_id
				FROM testtool.tbl_kategorie
				)
			) AND tbl_studienordnung.studiengang_kz = tbl_pruefling.studiengang_kz
           )';

	if(!$db->db_query($qry))
		echo '<strong>testtool.vw_auswertung_ablauf: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>testtool.vw_auswertung_ablauf view created';
}

// ADD COLUMN offset to testtool.vw_auswertung
if(!$result = @$db->db_query("SELECT offsetpunkte FROM testtool.vw_auswertung LIMIT 1"))
{
	$qry = '
		CREATE OR REPLACE VIEW testtool.vw_auswertung AS
			SELECT
				tbl_gebiet.gebiet_id,
				tbl_gebiet.bezeichnung AS gebiet,
				tbl_gebiet.maxpunkte,
				tbl_pruefling.pruefling_id,
				tbl_pruefling.prestudent_id,
				tbl_person.vorname,
				tbl_person.nachname,
				tbl_person.gebdatum,
				tbl_person.geschlecht,
				tbl_pruefling.semester,
				upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
				tbl_studiengang.bezeichnung AS stg_bez,
				tbl_pruefling.registriert,
				tbl_pruefling.idnachweis,
				(
					SELECT
						sum(tbl_vorschlag.punkte) AS sum
					FROM
						testtool.tbl_vorschlag
						JOIN testtool.tbl_antwort USING (vorschlag_id)
						JOIN testtool.tbl_frage USING (frage_id)
					WHERE
						tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id
						AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
				) AS punkte,
				tbl_rt_person.rt_id as reihungstest_id,
				tbl_ablauf.gewicht,
				tbl_person.person_id,
				tbl_gebiet.offsetpunkte
			FROM
				testtool.tbl_pruefling
				JOIN testtool.tbl_ablauf ON (tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz AND tbl_ablauf.semester = tbl_pruefling.semester)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
				JOIN public.tbl_studiengang ON tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz
				JOIN public.tbl_rt_person USING (person_id)
				JOIN lehre.tbl_studienplan ON (tbl_studienplan.studienplan_id = tbl_rt_person.studienplan_id)
				JOIN lehre.tbl_studienordnung ON (tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id)
			WHERE
				tbl_studienordnung.studiengang_kz = tbl_prestudent.studiengang_kz
				AND NOT (tbl_ablauf.gebiet_id IN ( SELECT tbl_kategorie.gebiet_id
					FROM testtool.tbl_kategorie));';

	if(!$db->db_query($qry))
		echo '<strong>testtool.vw_auswertung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>testtool.vw_auswertung view created';
}

// Add column orgform_kurzbz to tbl_bankverbindung
if(!$result = @$db->db_query("SELECT orgform_kurzbz FROM public.tbl_bankverbindung LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_bankverbindung ADD COLUMN orgform_kurzbz varchar(3);
			ALTER TABLE public.tbl_bankverbindung ADD CONSTRAINT fk_bankverbindung_orgform FOREIGN KEY (orgform_kurzbz) REFERENCES bis.tbl_orgform (orgform_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_bankverbindung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_bankverbindung: Spalte orgform_kurzbz hinzugefuegt';
}

// iban, bic und weitere Variablen zu vw_msg_vars hinzufügen
if(!$result = @$db->db_query('SELECT "IBAN Studiengang", "BIC Studiengang", "Studiengangskennzahl", "Einstiegssemester", "Einstiegsstudiensemester", "Vorname Studiengangsassistenz", "Nachname Studiengangsassistenz", "Durchwahl Studiengangsassistenz", "Alias Studiengangsassistenz", "Relative Prio" FROM public.vw_msg_vars WHERE person_id=-1 LIMIT 1'))
{
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
		  last_prestudent_status.orgform_kurzbz AS "Orgform",
		  p.zugangscode AS "Zugangscode",
		  bk.iban AS "IBAN Studiengang",
		  bk.bic AS "BIC Studiengang",
		  s.studiengang_kz AS "Studiengangskennzahl",
		  first_prestudent_status.ausbildungssemester AS "Einstiegssemester",
		  first_prestudent_status.studiensemester AS "Einstiegsstudiensemester",
		  ass.vorname AS "Vorname Studiengangsassistenz",
		  ass.nachname AS "Nachname Studiengangsassistenz",
		  ass.telefonklappe AS "Durchwahl Studiengangsassistenz",
		  ass.alias AS "Alias Studiengangsassistenz",
		  (SELECT count(*)
		   FROM (
					SELECT pss.prestudent_id, pss.person_id, priorisierung,
						   (
							   SELECT status_kurzbz
							   FROM public.tbl_prestudentstatus
							   WHERE prestudent_id = pss.prestudent_id
							   ORDER BY datum DESC,
										tbl_prestudentstatus.insertamum DESC LIMIT 1
						   ) AS laststatus
					FROM public.tbl_prestudent pss
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE person_id = (
						SELECT person_id
						FROM public.tbl_prestudent
						WHERE prestudent_id = pr.prestudent_id
					)
					  AND studiensemester_kurzbz = (
						SELECT studiensemester_kurzbz
						FROM public.tbl_prestudentstatus
						WHERE prestudent_id = pr.prestudent_id
						  AND status_kurzbz = \'Interessent\' LIMIT 1
					)
					  AND status_kurzbz = \'Interessent\'
				) prest
		   WHERE laststatus NOT IN (\'Abbrecher\', \'Abgewiesener\', \'Absolvent\')
		   AND priorisierung <= pr.priorisierung) AS "Relative Prio"
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
		LEFT JOIN (
			SELECT DISTINCT ON (ps.prestudent_id) ps.prestudent_id, tbl_studienplan.orgform_kurzbz
			FROM public.tbl_prestudent ps
					 JOIN public.tbl_prestudentstatus ON ps.prestudent_id = tbl_prestudentstatus.prestudent_id
					 JOIN lehre.tbl_studienplan USING(studienplan_id)
			ORDER BY ps.prestudent_id DESC,
					 tbl_prestudentstatus.datum DESC,
					 tbl_prestudentstatus.insertamum DESC,
					 tbl_prestudentstatus.ext_id DESC
		) last_prestudent_status ON pr.prestudent_id = last_prestudent_status.prestudent_id
		LEFT JOIN (
			SELECT DISTINCT ON (ps.prestudent_id) ps.prestudent_id, tbl_prestudentstatus.ausbildungssemester,
												  studiensemester_kurzbz, tbl_studiensemester.bezeichnung AS studiensemester,
												  tbl_studienordnung.studiengang_kz
			FROM public.tbl_prestudent ps
					 JOIN public.tbl_prestudentstatus ON ps.prestudent_id = tbl_prestudentstatus.prestudent_id
					 JOIN public.tbl_studiensemester USING (studiensemester_kurzbz)
					 JOIN lehre.tbl_studienplan USING(studienplan_id)
					 JOIN lehre.tbl_studienordnung USING (studienordnung_id)
			WHERE tbl_prestudentstatus.status_kurzbz = \'Interessent\'
			ORDER BY ps.prestudent_id ASC,
					 tbl_prestudentstatus.datum ASC,
					 tbl_prestudentstatus.insertamum ASC,
					 tbl_prestudentstatus.ext_id ASC
		) first_prestudent_status ON pr.prestudent_id = first_prestudent_status.prestudent_id
				 LEFT JOIN (
			SELECT DISTINCT ON (tbl_benutzerfunktion.oe_kurzbz) vorname, nachname, oe_kurzbz, telefonklappe, alias
			FROM public.tbl_benutzerfunktion
					 JOIN public.tbl_benutzer USING (uid)
					 JOIN public.tbl_person USING (person_id)
					 JOIN public.tbl_mitarbeiter on tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid
			WHERE tbl_benutzerfunktion.funktion_kurzbz = \'ass\'
			  AND NOW() BETWEEN COALESCE(datum_von, NOW()) AND COALESCE(datum_bis, NOW())
			ORDER BY tbl_benutzerfunktion.oe_kurzbz, tbl_benutzerfunktion.insertamum DESC NULLS LAST, datum_von DESC NULLS LAST
		) ass ON s.oe_kurzbz = ass.oe_kurzbz
				 LEFT JOIN (
			SELECT DISTINCT ON (oe_kurzbz, orgform_kurzbz) oe_kurzbz, orgform_kurzbz, iban, bic
			FROM tbl_bankverbindung
			WHERE oe_kurzbz IS NOT NULL
			ORDER BY oe_kurzbz, orgform_kurzbz, tbl_bankverbindung.insertamum DESC,tbl_bankverbindung.iban
		)bk ON s.oe_kurzbz = bk.oe_kurzbz AND (last_prestudent_status.orgform_kurzbz = bk.orgform_kurzbz OR bk.orgform_kurzbz IS NULL)
		WHERE p.aktiv = TRUE
		ORDER BY p.person_id ASC, pr.prestudent_id ASC
	);';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.vw_msg_vars IBAN Studiengang, BIC Studiengang, Studiengangskennzahl, Einstiegssemester, Einstiegsstudiensemester, Vorname Studiengangsassistenz, Nachname Studiengangsassistenz, Durchwahl Studiengangsassistenz, Alias Studiengangsassistenz, Relative Priorität added';
}

// UNIQUE INDEX unq_idx_ablauf_gebiet_studiengang_semester in testtool.tbl_ablauf löschen und durch neuen INDEX ersetzen, der auch den Studienplan einschließt
if ($result = $db->db_query("SELECT 1 FROM pg_class WHERE relname = 'unq_idx_ablauf_gebiet_studiengang_semester'"))
{
	if ($db->db_num_rows($result) == 1)
	{
		$qry = 'DROP INDEX testtool.unq_idx_ablauf_gebiet_studiengang_semester;';
		$qry .= 'CREATE UNIQUE INDEX unq_idx_ablauf_gebiet_studiengang_semester_studienplan ON testtool.tbl_ablauf USING btree (gebiet_id, studiengang_kz, semester, studienplan_id);';
		if (!$db->db_query($qry))
			echo '<strong>unq_idx_ablauf_gebiet_studiengang_semester_studienplan '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Dropped index "unq_idx_ablauf_gebiet_studiengang_semester" and created unique index "unq_idx_ablauf_gebiet_studiengang_semester_studienplan"';
	}
}

// Creates table system.tbl_jobstatuses if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 1 FROM system.tbl_jobstatuses LIMIT 1'))
{
	$qry = 'CREATE TABLE system.tbl_jobstatuses (
    			status character varying(64) NOT NULL
			);

			COMMENT ON TABLE system.tbl_jobstatuses IS \'All possible job statuses\';
			COMMENT ON COLUMN system.tbl_jobstatuses.status IS \'Job status value and primary key\';

			ALTER TABLE ONLY system.tbl_jobstatuses ADD CONSTRAINT pk_jobstatuses PRIMARY KEY (status);

			INSERT INTO system.tbl_jobstatuses(status) VALUES(\'new\');
			INSERT INTO system.tbl_jobstatuses(status) VALUES(\'running\');
			INSERT INTO system.tbl_jobstatuses(status) VALUES(\'done\');
			INSERT INTO system.tbl_jobstatuses(status) VALUES(\'failed\');
		';

	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobstatuses: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_jobstatuses table created';

	$qry = 'GRANT SELECT ON TABLE system.tbl_jobstatuses TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobstatuses: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_jobstatuses';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_jobstatuses TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobstatuses: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_jobstatuses';
}

// Creates table system.tbl_jobtypes if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 1 FROM system.tbl_jobtypes LIMIT 1'))
{
	$qry = 'CREATE TABLE system.tbl_jobtypes (
			    type character varying(128) NOT NULL,
			    description text NOT NULL
			);

			COMMENT ON TABLE system.tbl_jobtypes IS \'All possible job types\';
			COMMENT ON COLUMN system.tbl_jobtypes.type IS \'Job type value and primary key\';
			COMMENT ON COLUMN system.tbl_jobtypes.description IS \'Job type description\';

			ALTER TABLE ONLY system.tbl_jobtypes ADD CONSTRAINT pk_jobtypes PRIMARY KEY (type);
		';

	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobtypes: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_jobtypes table created';

	$qry = 'GRANT SELECT ON TABLE system.tbl_jobtypes TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobtypes: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_jobtypes';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_jobtypes TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobtypes: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_jobtypes';
}

// Creates table system.tbl_jobsqueue if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 1 FROM system.tbl_jobsqueue LIMIT 1'))
{
	$qry = 'CREATE TABLE system.tbl_jobsqueue (
			    jobid integer NOT NULL,
			    type character varying(128) NOT NULL,
			    creationtime timestamp without time zone DEFAULT now(),
			    status character varying(64) NOT NULL,
			    input jsonb,
			    output jsonb,
			    starttime timestamp without time zone,
			    endtime timestamp without time zone,
			    insertvon character varying(32),
			    insertamum timestamp without time zone DEFAULT now()
			);

			COMMENT ON TABLE system.tbl_jobsqueue IS \'Table to schedule/manage the jobs queue\';
			COMMENT ON COLUMN system.tbl_jobsqueue.jobid IS \'Primary key\';
			COMMENT ON COLUMN system.tbl_jobsqueue.type IS \'Job type\';
			COMMENT ON COLUMN system.tbl_jobsqueue.creationtime IS \'Job creation timestamp\';
			COMMENT ON COLUMN system.tbl_jobsqueue.status IS \'Job current status\';
			COMMENT ON COLUMN system.tbl_jobsqueue.input IS \'Job input in JSON format\';
			COMMENT ON COLUMN system.tbl_jobsqueue.output IS \'Job output in JSON format\';
			COMMENT ON COLUMN system.tbl_jobsqueue.starttime IS \'Job start timestamp\';
			COMMENT ON COLUMN system.tbl_jobsqueue.endtime IS \'Job end timestamp\';
			COMMENT ON COLUMN system.tbl_jobsqueue.insertvon IS \'User/Service who/that inserted this record\';
			COMMENT ON COLUMN system.tbl_jobsqueue.insertamum IS \'Record insert time stamp\';

			CREATE SEQUENCE system.seq_jobsqueue_jobid
			    START WITH 1
			    INCREMENT BY 1
			    NO MINVALUE
			    NO MAXVALUE
			    CACHE 1;

			ALTER SEQUENCE system.seq_jobsqueue_jobid OWNED BY system.tbl_jobsqueue.jobid;

			ALTER TABLE ONLY system.tbl_jobsqueue ALTER COLUMN jobid SET DEFAULT nextval(\'system.seq_jobsqueue_jobid\'::regclass);

			GRANT SELECT, UPDATE ON SEQUENCE system.seq_jobsqueue_jobid TO vilesci;
			GRANT SELECT, UPDATE ON SEQUENCE system.seq_jobsqueue_jobid TO fhcomplete;

			ALTER TABLE ONLY system.tbl_jobsqueue ADD CONSTRAINT pk_jobsqueue PRIMARY KEY (jobid);

			ALTER TABLE ONLY system.tbl_jobsqueue ADD CONSTRAINT fk_jobsqueue_status FOREIGN KEY (status) REFERENCES system.tbl_jobstatuses(status) ON UPDATE CASCADE ON DELETE RESTRICT;

			ALTER TABLE ONLY system.tbl_jobsqueue ADD CONSTRAINT fk_jobsqueue_type FOREIGN KEY (type) REFERENCES system.tbl_jobtypes(type) ON UPDATE CASCADE ON DELETE RESTRICT;
		';

	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobsqueue: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_jobsqueue table created';

	$qry = 'GRANT SELECT ON TABLE system.tbl_jobsqueue TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobsqueue: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_jobsqueue';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_jobsqueue TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobsqueue: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_jobsqueue';
}

// Creates table system.tbl_jobtriggers if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 1 FROM system.tbl_jobtriggers LIMIT 1'))
{
	$qry = 'CREATE TABLE system.tbl_jobtriggers (
			    type character varying(128) NOT NULL,
			    status character varying(64) NOT NULL,
			    following_type character varying(128) NOT NULL
			);

			COMMENT ON TABLE system.tbl_jobtriggers IS \'Table to manage the job triggers\';
			COMMENT ON COLUMN system.tbl_jobtriggers.type IS \'Job type\';
			COMMENT ON COLUMN system.tbl_jobtriggers.status IS \'Job status\';
			COMMENT ON COLUMN system.tbl_jobtriggers.following_type IS \'New job type\';

			ALTER TABLE ONLY system.tbl_jobtriggers ADD CONSTRAINT pk_jobtriggers PRIMARY KEY (type, status, following_type);

			ALTER TABLE ONLY system.tbl_jobtriggers ADD CONSTRAINT fk_jobtriggers_status FOREIGN KEY (status) REFERENCES system.tbl_jobstatuses(status) ON UPDATE CASCADE ON DELETE RESTRICT;

			ALTER TABLE ONLY system.tbl_jobtriggers ADD CONSTRAINT fk_jobtriggers_type FOREIGN KEY (type) REFERENCES system.tbl_jobtypes(type) ON UPDATE CASCADE ON DELETE RESTRICT;

			ALTER TABLE ONLY system.tbl_jobtriggers ADD CONSTRAINT fk_jobtriggers_following_type FOREIGN KEY (following_type) REFERENCES system.tbl_jobtypes(type) ON UPDATE CASCADE ON DELETE RESTRICT;
		';

	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobtriggers: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_jobtriggers table created';

	$qry = 'GRANT SELECT ON TABLE system.tbl_jobtriggers TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobtriggers: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_jobtriggers';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_jobtriggers TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobtriggers: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_jobtriggers';
}

// Add column iso3166_1_a2 to bis.tbl_nation
if (!$result = @$db->db_query("SELECT iso3166_1_a2 FROM bis.tbl_nation LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_nation ADD COLUMN iso3166_1_a2 character varying(2);";
	$qry .= "COMMENT ON COLUMN bis.tbl_nation.iso3166_1_a2 IS 'ISO 3166-1 alpha-2 country code';";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_nation.iso3166_1_a2: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_nation.iso3166_1_a2: Spalte iso3166_1_a2 hinzugefuegt';
}

// Spalte bezeichnung_mehrsprachig in public.tbl_studiengangstyp
if(!$result = @$db->db_query("SELECT bezeichnung_mehrsprachig FROM public.tbl_studiengangstyp LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_studiengangstyp ADD COLUMN bezeichnung_mehrsprachig varchar(255)[];";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_studiengangstyp '.$db->db_last_error().'</strong><br>';
	else
		echo 'public.tbl_studiengangstyp: Spalte bezeichnung_mehrsprachig hinzugefuegt!<br>';

	// Bezeichnung_mehrsprachig aus existierender Bezeichnung vorausfuellen. Ein Eintrag fuer jede Sprache mit Content aktiv.
	$qry_help = "SELECT index FROM public.tbl_sprache WHERE content=TRUE;";
	if(!$result = $db->db_query($qry_help))
		echo '<strong>tbl_studiengangstyp bezeichnung_mehrsprachig: Fehler beim ermitteln der Sprachen: '.$db->db_last_error().'</strong>';
	else
	{
		$qry='';
		while($row = $db->db_fetch_object($result))
			$qry.= "UPDATE public.tbl_studiengangstyp set bezeichnung_mehrsprachig[".$row->index."] = bezeichnung;";

		if(!$db->db_query($qry))
			echo '<strong>Setzen der bezeichnung_mehrsprachig fehlgeschlagen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br> bis.tbl_studiengangstyp: bezeichnung_mehrprachig automatisch aus existierender Bezeichnung uebernommen<br>';
	}
}

// Add type "p" to Studiengangstyp
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_studiengangstyp WHERE typ = 'p';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_studiengangstyp(typ,bezeichnung,beschreibung,bezeichnung_mehrsprachig) VALUES('p', 'PhD',NULL,'{PhD,PhD}');";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_webservicetyp '.$db->db_last_error().'</strong><br>';
		else
			echo '<br> public.tbl_studiengangstyp: Added type "p"<br>';
	}
}

// Add type "l" to Studiengangstyp
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_studiengangstyp WHERE typ = 'l';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_studiengangstyp(typ,bezeichnung,beschreibung,bezeichnung_mehrsprachig) VALUES('l', 'Lehrgang',NULL,'{Lehrgang,Course}');";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_webservicetyp '.$db->db_last_error().'</strong><br>';
		else
			echo '<br> public.tbl_studiengangstyp: Added type "l"<br>';
	}
}

// Add type "d" to Studiengangstyp
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_studiengangstyp WHERE typ = 'd';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_studiengangstyp(typ,bezeichnung,beschreibung,bezeichnung_mehrsprachig) VALUES('d', 'Diplom',NULL,'{Diplom,Diploma}');";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_webservicetyp '.$db->db_last_error().'</strong><br>';
		else
			echo '<br> public.tbl_studiengangstyp: Added type "d"<br>';
	}
}

// Add type "w" to Studiengangstyp
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_studiengangstyp WHERE typ = 'w';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_studiengangstyp(typ,bezeichnung,beschreibung,bezeichnung_mehrsprachig) VALUES('w', 'Weiterbildung',NULL,'{Weiterbildung,Further education}');";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_webservicetyp '.$db->db_last_error().'</strong><br>';
		else
			echo '<br> public.tbl_studiengangstyp: Added type "w"<br>';
	}
}

/**
 * Anpassungen fuer BIS Personalmeldung 6.8
 */
if (!$result = @$db->db_query("SELECT ba1code_bis FROM bis.tbl_beschaeftigungsart1 LIMIT 1"))
{
	/*
	Beschaeftigungsausmass Kodextabelle aktualisieren

	BA1Code alt 1 => BA1Code neu 1
	BA1Code alt 2 => BA1Code neu 2
	BA1Code alt 3 => BA1Code neu 3 (Echter DV)
	BA1Code alt 4 => BA1Code neu 5 (Freier DV -> Sonstiges)
	BA1Code alt 5 => BA1Code neu 4
	BA1Code alt 6 => BA1Code neu 5 (Werkvertrag -> Sonstiges)

	BA1Code ist nicht mehr der Code der gemeldet wird.
	BA1code wird um 100 erhöht damit klar ist, dass es sich um einen anderen Code handelt.
	*/
	$qry = "
		ALTER TABLE bis.tbl_beschaeftigungsart1 ADD COLUMN ba1code_bis smallint;
		UPDATE bis.tbl_beschaeftigungsart1 SET ba1code_bis=1, ba1code=101 WHERE ba1code=1;
		UPDATE bis.tbl_beschaeftigungsart1 SET ba1code_bis=2, ba1code=102 WHERE ba1code=2;
		UPDATE bis.tbl_beschaeftigungsart1 SET ba1code_bis=3, ba1code=103 WHERE ba1code=3;
		UPDATE bis.tbl_beschaeftigungsart1 SET ba1code_bis=5, ba1code=105 WHERE ba1code=4;
		UPDATE bis.tbl_beschaeftigungsart1 SET ba1code_bis=4, ba1code=104 WHERE ba1code=5;
		UPDATE bis.tbl_beschaeftigungsart1 SET ba1code_bis=6, ba1code=106 WHERE ba1code=6;
		";

	/*
	Für Werkvertraege wird eine eigene Beschaeftigungsart erstellt.
	Die alten Eintraege für "Sonstiges (Werkvertrag)" werden auf diese neue Beschaeftigungsart umgehaengt
	da diese bei uns alles Werkvertraege sind.
	Fuer Studentische Hilfskraefte wird ebenfalls eine eigene Beschaeftigungsart erstellt.
	Diese werden als Echter DV gemeldet aber getrennt verwaltet.
	Alle Personen mit einer Funktion als Hilfskraft werden auf diese Beschaeftigungsart geaendert.
	*/
	$qry .= "
		INSERT INTO bis.tbl_beschaeftigungsart1(ba1code, ba1bez, ba1kurzbz, ba1code_bis) VALUES(107,'Werkvertrag (Sonstiges)','Werkvertrag (Sonstiges)', 5);
		INSERT INTO bis.tbl_beschaeftigungsart1(ba1code, ba1bez, ba1kurzbz, ba1code_bis) VALUES(108,'Studentische Hilfskraft (Echter DV)','Stud. Hilfskraft (Echter DV)', 3);
		UPDATE bis.tbl_bisverwendung SET ba1code=107 WHERE ba1code=106;
		UPDATE bis.tbl_bisverwendung SET ba1code=108 WHERE ba1code=103 AND EXISTS(
			SELECT 1 FROM public.tbl_benutzerfunktion
			WHERE uid=tbl_bisverwendung.mitarbeiter_uid AND funktion_kurzbz='hilfskraft' AND
				(
					datum_von BETWEEN tbl_bisverwendung.beginn AND tbl_bisverwendung.ende
					OR
					datum_bis BETWEEN tbl_bisverwendung.beginn AND tbl_bisverwendung.ende
				)
		);
		";

	$qry .= "
		UPDATE bis.tbl_beschaeftigungsart1 SET ba1bez='Dienstverhältnis zur postsekundären Bildungseinrichtung oder deren Träger' WHERE ba1code=103;
		UPDATE bis.tbl_beschaeftigungsart1 SET ba1kurzbz='Lehr- oder Ausbildungsverhältnis', ba1bez='Lehr- oder Ausbildungsverhältnis' WHERE ba1code=104;
		UPDATE bis.tbl_beschaeftigungsart1 SET ba1kurzbz='Freier Dienstvertrag (Sonstiges)', ba1bez='Sonstiges Beschäftigungsverhältnis' WHERE ba1code=105;
		UPDATE bis.tbl_beschaeftigungsart1 SET ba1kurzbz='Andere Bildungseinrichtung', ba1bez='Dienstverhältnis zu einer anderen Bildungseinrichtung oder einem anderen Träger' WHERE ba1code=106;
	";

	/*
	Verwendungs Kodextabelle aktualisieren

	VerwendungCode alt 1 => VerwendungsCode neu 1
	VerwendungCode alt 2 => VerwendungsCode neu 2
	VerwendungCode alt 3 => VerwendungsCode neu 3
	VerwendungCode alt 4 => VerwendungsCode neu 4
	VerwendungCode alt 5 => VerwendungsCode neu 5
	VerwendungCode alt 6 => VerwendungsCode neu 5
	VerwendungCode alt 7 => VerwendungsCode neu 5
	VerwendungCode alt 8 => VerwendungsCode neu 6
	VerwendungCode alt 9 => VerwendungsCode neu 7
	*/

	$qry .= "
		UPDATE bis.tbl_bisverwendung SET verwendung_code=5 WHERE verwendung_code=6;
		UPDATE bis.tbl_bisverwendung SET verwendung_code=5 WHERE verwendung_code=7;
		UPDATE bis.tbl_bisverwendung SET verwendung_code=6 WHERE verwendung_code=8;
		UPDATE bis.tbl_bisverwendung SET verwendung_code=7 WHERE verwendung_code=9;

		UPDATE bis.tbl_verwendung SET verwendungbez='wissenschaftliche Lehre und Forschung' WHERE verwendung_code=1;
		UPDATE bis.tbl_verwendung SET verwendungbez='wissenschaftliche Mitarbeit in Lehre und Forschung' WHERE verwendung_code=2;
		UPDATE bis.tbl_verwendung SET verwendungbez='professionelle Unterstützung der Studierenden in akademischen Belangen' WHERE verwendung_code=3;
		UPDATE bis.tbl_verwendung SET verwendungbez='professionelle Unterstützung der Studierenden in Gesundheits- und Sozialbelangen' WHERE verwendung_code=4;
		UPDATE bis.tbl_verwendung SET verwendungbez='Management' WHERE verwendung_code=5;
		UPDATE bis.tbl_verwendung SET verwendungbez='Verwaltung' WHERE verwendung_code=6;
		UPDATE bis.tbl_verwendung SET verwendungbez='Wartung und Betrieb' WHERE verwendung_code=7;

		DELETE FROM bis.tbl_verwendung WHERE verwendung_code=8;
		DELETE FROM bis.tbl_verwendung WHERE verwendung_code=9;
	";

	$qry.="
		INSERT INTO public.tbl_funktion(funktion_kurzbz, beschreibung, aktiv, fachbereich, semester) VALUES('vertrBefugter','Vertretungsbefugte/r des Erhalters',true,false,false);
		INSERT INTO public.tbl_funktion(funktion_kurzbz, beschreibung, aktiv, fachbereich, semester) VALUES('kollegium_Ltg','Leiter/in des Kollegiums',true,false,false);
		INSERT INTO public.tbl_funktion(funktion_kurzbz, beschreibung, aktiv, fachbereich, semester) VALUES('kollegium_stvLtg','Stellv. Leiter/in des Kollegiums',true,false,false);
	";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_verwendung und bis.tbl_beschaeftigungsart: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Verwendung und Beschaeftigungsart für BIS Version 6.8 aktualisiert.';
}

// Orgform DE und Orform EN zu vw_msg_vars hinzufügen
if(!$result = @$db->db_query('SELECT "Orgform DE", "Orgform EN" FROM public.vw_msg_vars WHERE person_id=-1 LIMIT 1'))
{
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
		  last_prestudent_status.orgform_kurzbz AS "Orgform",
		  p.zugangscode AS "Zugangscode",
		  bk.iban AS "IBAN Studiengang",
		  bk.bic AS "BIC Studiengang",
		  s.studiengang_kz AS "Studiengangskennzahl",
		  first_prestudent_status.ausbildungssemester AS "Einstiegssemester",
		  first_prestudent_status.studiensemester AS "Einstiegsstudiensemester",
		  ass.vorname AS "Vorname Studiengangsassistenz",
		  ass.nachname AS "Nachname Studiengangsassistenz",
		  ass.telefonklappe AS "Durchwahl Studiengangsassistenz",
		  ass.alias AS "Alias Studiengangsassistenz",
		  (SELECT count(*)
		   FROM (
					SELECT pss.prestudent_id, pss.person_id, priorisierung,
						   (
							   SELECT status_kurzbz
							   FROM public.tbl_prestudentstatus
							   WHERE prestudent_id = pss.prestudent_id
							   ORDER BY datum DESC,
										tbl_prestudentstatus.insertamum DESC LIMIT 1
						   ) AS laststatus
					FROM public.tbl_prestudent pss
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					WHERE person_id = (
						SELECT person_id
						FROM public.tbl_prestudent
						WHERE prestudent_id = pr.prestudent_id
					)
					  AND studiensemester_kurzbz = (
						SELECT studiensemester_kurzbz
						FROM public.tbl_prestudentstatus
						WHERE prestudent_id = pr.prestudent_id
						  AND status_kurzbz = \'Interessent\' LIMIT 1
					)
					  AND status_kurzbz = \'Interessent\'
				) prest
		   WHERE laststatus NOT IN (\'Abbrecher\', \'Abgewiesener\', \'Absolvent\')
		   AND priorisierung <= pr.priorisierung) AS "Relative Prio",
		   last_prestudent_status.orgform_bezeichnung_de AS "Orgform DE",
           last_prestudent_status.orgform_bezeichnung_en AS "Orgform EN"
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
		LEFT JOIN (
			SELECT DISTINCT ON (ps.prestudent_id) ps.prestudent_id, tbl_studienplan.orgform_kurzbz,
				tbl_orgform.bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE content=TRUE AND sprache=\'German\' LIMIT 1)] AS orgform_bezeichnung_de,
            	tbl_orgform.bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE content=TRUE AND sprache=\'English\' LIMIT 1)] AS orgform_bezeichnung_en
			FROM public.tbl_prestudent ps
					 JOIN public.tbl_prestudentstatus ON ps.prestudent_id = tbl_prestudentstatus.prestudent_id
					 JOIN lehre.tbl_studienplan USING(studienplan_id)
					 LEFT JOIN bis.tbl_orgform ON tbl_studienplan.orgform_kurzbz = tbl_orgform.orgform_kurzbz
			ORDER BY ps.prestudent_id DESC,
					 tbl_prestudentstatus.datum DESC,
					 tbl_prestudentstatus.insertamum DESC,
					 tbl_prestudentstatus.ext_id DESC
		) last_prestudent_status ON pr.prestudent_id = last_prestudent_status.prestudent_id
		LEFT JOIN (
			SELECT DISTINCT ON (ps.prestudent_id) ps.prestudent_id, tbl_prestudentstatus.ausbildungssemester,
												  studiensemester_kurzbz, tbl_studiensemester.bezeichnung AS studiensemester,
												  tbl_studienordnung.studiengang_kz
			FROM public.tbl_prestudent ps
					 JOIN public.tbl_prestudentstatus ON ps.prestudent_id = tbl_prestudentstatus.prestudent_id
					 JOIN public.tbl_studiensemester USING (studiensemester_kurzbz)
					 JOIN lehre.tbl_studienplan USING(studienplan_id)
					 JOIN lehre.tbl_studienordnung USING (studienordnung_id)
			WHERE tbl_prestudentstatus.status_kurzbz = \'Interessent\'
			ORDER BY ps.prestudent_id ASC,
					 tbl_prestudentstatus.datum ASC,
					 tbl_prestudentstatus.insertamum ASC,
					 tbl_prestudentstatus.ext_id ASC
		) first_prestudent_status ON pr.prestudent_id = first_prestudent_status.prestudent_id
				 LEFT JOIN (
			SELECT DISTINCT ON (tbl_benutzerfunktion.oe_kurzbz) vorname, nachname, oe_kurzbz, telefonklappe, alias
			FROM public.tbl_benutzerfunktion
					 JOIN public.tbl_benutzer USING (uid)
					 JOIN public.tbl_person USING (person_id)
					 JOIN public.tbl_mitarbeiter on tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid
			WHERE tbl_benutzerfunktion.funktion_kurzbz = \'ass\'
			  AND NOW() BETWEEN COALESCE(datum_von, NOW()) AND COALESCE(datum_bis, NOW())
			ORDER BY tbl_benutzerfunktion.oe_kurzbz, tbl_benutzerfunktion.insertamum DESC NULLS LAST, datum_von DESC NULLS LAST
		) ass ON s.oe_kurzbz = ass.oe_kurzbz
				 LEFT JOIN (
			SELECT DISTINCT ON (oe_kurzbz, orgform_kurzbz) oe_kurzbz, orgform_kurzbz, iban, bic
			FROM tbl_bankverbindung
			WHERE oe_kurzbz IS NOT NULL
			ORDER BY oe_kurzbz, orgform_kurzbz, tbl_bankverbindung.insertamum DESC,tbl_bankverbindung.iban
		)bk ON s.oe_kurzbz = bk.oe_kurzbz AND (last_prestudent_status.orgform_kurzbz = bk.orgform_kurzbz OR bk.orgform_kurzbz IS NULL)
		WHERE p.aktiv = TRUE
		ORDER BY p.person_id ASC, pr.prestudent_id ASC
	);';

	if(!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.vw_msg_vars added';
}

// Spalte akzeptiertamum in public.tbl_akte
if(!$result = @$db->db_query("SELECT akzeptiertamum FROM public.tbl_akte LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_akte ADD COLUMN akzeptiertamum timestamp without time zone;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_akte '.$db->db_last_error().'</strong><br>';
	else
		echo 'public.tbl_akte: Spalte akzeptiertamum hinzugefuegt!<br>';
}

// TABLE lehre.tbl_abschlusspruefung_antritt
if (!@$db->db_query("SELECT 0 FROM lehre.tbl_abschlusspruefung_antritt WHERE 0 = 1"))
{
	$qry = '
		CREATE TABLE lehre.tbl_abschlusspruefung_antritt (
			pruefungsantritt_kurzbz character varying(20) NOT NULL,
			bezeichnung character varying(64),
			bezeichnung_english character varying(64),
			sort smallint
		);
		ALTER TABLE lehre.tbl_abschlusspruefung_antritt ADD CONSTRAINT pk_abschlusspruefung_antritt PRIMARY KEY (pruefungsantritt_kurzbz);
		INSERT INTO lehre.tbl_abschlusspruefung_antritt(pruefungsantritt_kurzbz, bezeichnung, bezeichnung_english, sort) VALUES (\'erstantritt\', \'Erstantritt\', \'1st Attempt\', 1);
		INSERT INTO lehre.tbl_abschlusspruefung_antritt(pruefungsantritt_kurzbz, bezeichnung, bezeichnung_english, sort) VALUES (\'erstewiederholung\', \'1. Wiederholung\', \'1st Retake\', 2);
		INSERT INTO lehre.tbl_abschlusspruefung_antritt(pruefungsantritt_kurzbz, bezeichnung, bezeichnung_english, sort) VALUES (\'zweitewiederholung\', \'2. Wiederholung\', \'2nd Retake\', 3);';

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlusspruefung_antritt '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Created table lehre.tbl_abschlusspruefung_antritt';

	// GRANT SELECT ON TABLE lehre.tbl_abschlusspruefung_antritt TO web;
	$qry = 'GRANT SELECT ON TABLE lehre.tbl_abschlusspruefung_antritt TO web;';
	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlusspruefung_antritt '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_abschlusspruefung_antritt';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_abschlusspruefung_antritt TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_abschlusspruefung_antritt TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlusspruefung_antritt '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_abschlusspruefung_antritt';

	// GRANT SELECT, UPDATE ON TABLE lehre.tbl_abschlusspruefung TO web;
	$qry = 'GRANT SELECT, UPDATE ON lehre.tbl_abschlusspruefung TO web;';
	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlusspruefung '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_abschlusspruefung';

	// COMMENT ON TABLE lehre.tbl_abschlusspruefung_antritt
	$qry = 'COMMENT ON TABLE lehre.tbl_abschlusspruefung_antritt IS \'Type of Abschlusspruefung depending on number of attempts\';';
	if (!$db->db_query($qry))
		echo '<strong>Adding comment to lehre.tbl_abschlusspruefung_antritt: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added comment to lehre.tbl_abschlusspruefung_antritt';
}
// add protokoll,endezeit,pruefungsantritt_kurzbz,freigabedatum to lehre.tbl_abschlusspruefung
if(!$result = @$db->db_query("SELECT protokoll,endezeit,pruefungsantritt_kurzbz,freigabedatum FROM lehre.tbl_abschlusspruefung LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_abschlusspruefung ADD COLUMN protokoll text;
			ALTER TABLE lehre.tbl_abschlusspruefung ADD COLUMN endezeit time;
			ALTER TABLE lehre.tbl_abschlusspruefung ADD COLUMN pruefungsantritt_kurzbz character varying(20);
			ALTER TABLE lehre.tbl_abschlusspruefung ADD CONSTRAINT fk_abschlusspruefung_antritt FOREIGN KEY (pruefungsantritt_kurzbz) REFERENCES lehre.tbl_abschlusspruefung_antritt (pruefungsantritt_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE lehre.tbl_abschlusspruefung ADD COLUMN freigabedatum date;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlusspruefung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_abschlusspruefung: Spalten protokoll,endezeit,pruefungsantritt_kurzbz,freigabedatum hinzugefuegt';
}

// Spalte sort in lehre.tbl_abschlussbeurteilung (gibt Reihenfolge der Beurteilungen an)
if(!$result = @$db->db_query("SELECT sort FROM lehre.tbl_abschlussbeurteilung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_abschlussbeurteilung ADD COLUMN sort smallint;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlussbeurteilung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_abschlussbeurteilung: Spalte sort hinzugefuegt!<br>';
}

//Spalte co_adresse in tbl_adresse
if(!$result = @$db->db_query("SELECT co_name FROM public.tbl_adresse LIMIT 1"))
{
	$qry = "
		ALTER TABLE public.tbl_adresse ADD COLUMN co_name varchar(256);
		COMMENT ON COLUMN public.tbl_adresse.co_name IS 'Name des abweichenden Empfaengers';";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_adresse: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_adresse: Spalte co_name und anmerkung hinzugefuegt';
}

// Add column iso3166_1_a3 to tbl_nation
if(!$result = @$db->db_query("SELECT iso3166_1_a3 FROM bis.tbl_nation LIMIT 1"))
{
	$qry = "ALTER table bis.tbl_nation ADD COLUMN iso3166_1_a3 VARCHAR(3);
			COMMENT ON COLUMN bis.tbl_nation.iso3166_1_a3 IS 'ISO 3166-1 alpha-3 country code';";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_nation: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_nation: Spalte iso3166_1_a3 hinzugefuegt';
}

// OE_KURZBZ in system.tbl_filters auf 32 Zeichen verlängert
if($result = $db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='wawi' AND TABLE_NAME='tbl_betriebsmitteltyp' AND COLUMN_NAME = 'typ_code' AND character_maximum_length=2"))
{
	if($db->db_num_rows($result)>0)
	{
		$qry = " ALTER TABLE wawi.tbl_betriebsmitteltyp ALTER COLUMN typ_code TYPE varchar(6)";

		if(!$db->db_query($qry))
			echo '<strong>wawi.tbl_betriebsmitteltyp '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte typ_code in wawi.tbl_betriebsmitteltyp von character(2) auf varchar(6) geändert<br>';
	}
}

// ADD COLUMN azgrelevant in bis.tbl_bisverwendung
if(!$result = @$db->db_query("SELECT azgrelevant FROM bis.tbl_bisverwendung LIMIT 1"))
{
	$qry = "
		ALTER TABLE bis.tbl_bisverwendung ADD COLUMN azgrelevant boolean;
		UPDATE bis.tbl_bisverwendung SET azgrelevant = zeitaufzeichnungspflichtig;
		UPDATE bis.tbl_bisverwendung SET zeitaufzeichnungspflichtig = true WHERE ba1code=103 AND beschausmasscode!=5;
	";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_bisverwendung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_bisverwendung Spalte azgrelevant hinzugefügt.';
}

// Add new funktion type kstzuordnung for cost center assignment in public.tbl_funktion
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_funktion WHERE funktion_kurzbz = 'kstzuordnung';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_funktion(funktion_kurzbz, beschreibung, aktiv, fachbereich, semester) VALUES('kstzuordnung','Standardkostenstelle Zuordnung',true,false,false);";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_funktion '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.tbl_funktion: Added funktion kstzuordnung<br>';
	}
}

// Add column zugangstoken to tbl_projektbetreuer
if(!$result = @$db->db_query("SELECT zugangstoken FROM lehre.tbl_projektbetreuer LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_projektbetreuer ADD COLUMN zugangstoken VARCHAR(32);
			COMMENT ON COLUMN lehre.tbl_projektbetreuer.zugangstoken IS 'Zugangstoken zur Projektarbeitsbewertung fuer externe Betreuer';
			ALTER TABLE lehre.tbl_projektbetreuer ADD CONSTRAINT uk_tbl_projektbetreuer_zugangstoken UNIQUE (zugangstoken);";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_projektbetreuer: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_projektbetreuer: Spalte zugangstoken hinzugefuegt';
}

// Add column zugangstoken_gueltigbis to tbl_projektbetreuer
if(!$result = @$db->db_query("SELECT zugangstoken_gueltigbis FROM lehre.tbl_projektbetreuer LIMIT 1"))
{
	$qry = "ALTER table lehre.tbl_projektbetreuer ADD COLUMN zugangstoken_gueltigbis date;
			COMMENT ON COLUMN lehre.tbl_projektbetreuer.zugangstoken_gueltigbis IS 'Gueligkeitsdatum fuer Zugangstoken zur Projektarbeitsbewertung fuer externe Betreuer';";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_projektbetreuer: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_projektbetreuer: Spalte zugangstoken_gueltigbis hinzugefuegt';
}

// App 'projektarbeitsbeurteilung' hinzufügen
if($result = $db->db_query("SELECT 1 FROM system.tbl_app WHERE app='projektarbeitsbeurteilung'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO system.tbl_app(app) VALUES('projektarbeitsbeurteilung');";

		if(!$db->db_query($qry))
			echo '<strong>App: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue App projektarbeitsbeurteilung in system.tbl_app hinzugefügt';
	}
}

// UPDATE Berechtigung für lehre.tbl_projektbetreuer für web user hinzufügen
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_projektbetreuer' AND table_schema='lehre' AND grantee='web' AND privilege_type='UPDATE'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT UPDATE ON lehre.tbl_projektbetreuer TO web;";

		if(!$db->db_query($qry))
			echo '<strong>Projektbetreuer Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Web User: update fuer lehre.tbl_projektbetreuer berechtigt';
	}
}

// Add column dms_id, studiensemester_kurzbz, anmerkung_student und empfehlung_anrechnung
// Change genehmigt_von and begruendung_id to be NULLABLE
if(!$result = @$db->db_query("SELECT dms_id FROM lehre.tbl_anrechnung LIMIT 1"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "
			ALTER TABLE lehre.tbl_anrechnung ADD COLUMN dms_id bigint;
			ALTER TABLE lehre.tbl_anrechnung ADD COLUMN studiensemester_kurzbz varchar(6);
			ALTER TABLE lehre.tbl_anrechnung ADD COLUMN anmerkung_student text;
			ALTER TABLE lehre.tbl_anrechnung ADD COLUMN empfehlung_anrechnung boolean;

			ALTER TABLE lehre.tbl_anrechnung ADD CONSTRAINT fk_anrechnung_studiensemester FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE lehre.tbl_anrechnung ADD CONSTRAINT fk_anrechnung_dms FOREIGN KEY (dms_id) REFERENCES campus.tbl_dms(dms_id) ON DELETE RESTRICT ON UPDATE CASCADE;

			ALTER TABLE lehre.tbl_anrechnung ALTER COLUMN genehmigt_von DROP NOT NULL;
			ALTER TABLE lehre.tbl_anrechnung ALTER COLUMN begruendung_id DROP NOT NULL;
			ALTER TABLE lehre.tbl_anrechnung ALTER COLUMN insertamum SET DEFAULT NOW();
		";
	}


	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_anrechnung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_anrechnung: Neue Spalten dms_id, studiensemester_kurzbz, anmerkung_student und empfehlung_anrechnung hinzugefuegt. Not null constraint entfernt für genehmigt_von und begruendung_id';
}

// Add DMS category "anrechnung"
if ($result = @$db->db_query("SELECT 1 FROM campus.tbl_dms_kategorie WHERE kategorie_kurzbz = 'anrechnung';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO campus.tbl_dms_kategorie (
					kategorie_kurzbz,
					bezeichnung,
					beschreibung,
					parent_kategorie_kurzbz,
					oe_kurzbz,
					berechtigung_kurzbz
			   ) VALUES(
					'anrechnung',
					'Anrechnung',
					'Dokumente zur Anrechnung von Lehrveranstaltungen',
					'studium',
					'etw',
					NULL
			   );";
		if (!$db->db_query($qry))
			echo '<strong>campus.tbl_dms_kategorie '.$db->db_last_error().'</strong><br>';
		else
			echo ' campus.tbl_dms_kategorie: Added category "anrechnung"!<br>';
	}
}


// Add DMS category permissiongroup for DMS category "anrechnung"
if ($result = @$db->db_query("SELECT 1 FROM campus.tbl_dms_kategorie_gruppe WHERE kategorie_kurzbz = 'anrechnung';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO campus.tbl_dms_kategorie_gruppe (
					kategorie_kurzbz,
					gruppe_kurzbz,
					insertamum,
					insertvon
			   ) VALUES(
					'anrechnung',
					'CMS_LOCK',
					NOW(),
					'dbcheck'
			   );";
		if (!$db->db_query($qry))
			echo '<strong>campus.tbl_dms_kategorie_gruppe '.$db->db_last_error().'</strong><br>';
		else
			echo ' campus.tbl_dms_kategorie_gruppe: Added category group "CMS_LOCK" to category "anrechnung"!<br>';
	}
}

// Add table anrechnungstatus
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_anrechnungstatus LIMIT 1;"))
{
	$qry = "
		CREATE TABLE lehre.tbl_anrechnungstatus
		(
			status_kurzbz varchar(32) NOT NULL,
			bezeichnung_mehrsprachig varchar(64)[]
		);

		ALTER TABLE lehre.tbl_anrechnungstatus ADD CONSTRAINT pk_anrechnungstatus PRIMARY KEY (status_kurzbz);

		INSERT INTO lehre.tbl_anrechnungstatus(status_kurzbz, bezeichnung_mehrsprachig) VALUES('inProgressDP', '{\"bearbeitet von STG-Leitung\",\"processed by STG-Director\"}');
		INSERT INTO lehre.tbl_anrechnungstatus(status_kurzbz, bezeichnung_mehrsprachig) VALUES('inProgressKF', '{\"bearbeitet von KF-Leitung\",\"processed by KF-Manager\"}');
		INSERT INTO lehre.tbl_anrechnungstatus(status_kurzbz, bezeichnung_mehrsprachig) VALUES('inProgressLektor', '{\"Empfehlung angefordert\",\"recommendation requested\"}');
		INSERT INTO lehre.tbl_anrechnungstatus(status_kurzbz, bezeichnung_mehrsprachig) VALUES('approved', '{\"genehmigt\",\"approved\"}');
		INSERT INTO lehre.tbl_anrechnungstatus(status_kurzbz, bezeichnung_mehrsprachig) VALUES('rejected', '{\"abgelehnt\",\"rejected\"}');

		GRANT SELECT ON lehre.tbl_anrechnungstatus TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_anrechnungstatus TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_anrechnungstatus: '.$db->db_last_error().'</strong><br>';
	else
		echo ' lehre.tbl_anrechnungstatus: Tabelle hinzugefuegt<br>';
}

// Add table anrechnung_anrechnungstatus
// Für bestehende genehmigte Anrechnungsanträge wird ein Eintrag mit dem Status 'approved' angelegt
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_anrechnung_anrechnungstatus LIMIT 1;"))
{
	$qry = "
		CREATE TABLE lehre.tbl_anrechnung_anrechnungstatus
		(
			anrechnungstatus_id integer NOT NULL,
			anrechnung_id integer,
			status_kurzbz varchar(32),
			datum date default now(),
			insertamum timestamp default now(),
			insertvon varchar(32)
		);

		ALTER TABLE lehre.tbl_anrechnung_anrechnungstatus ADD CONSTRAINT pk_anrechnung_anrechnungstatus PRIMARY KEY (anrechnungstatus_id);
		ALTER TABLE lehre.tbl_anrechnung_anrechnungstatus ADD CONSTRAINT fk_anrechnung_anrechnungstatus_anrechnung FOREIGN KEY (anrechnung_id) REFERENCES lehre.tbl_anrechnung(anrechnung_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_anrechnung_anrechnungstatus ADD CONSTRAINT fk_anrechnung_anrechnungstatus_anrechnungstatus FOREIGN KEY (status_kurzbz) REFERENCES lehre.tbl_anrechnungstatus (status_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		CREATE SEQUENCE lehre.seq_anrechnung_anrechnungstatus_anrechnungstatus_id
			START WITH 1
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;
		ALTER TABLE lehre.tbl_anrechnung_anrechnungstatus ALTER COLUMN anrechnungstatus_id SET DEFAULT nextval('lehre.seq_anrechnung_anrechnungstatus_anrechnungstatus_id');

		INSERT INTO lehre.tbl_anrechnung_anrechnungstatus(anrechnung_id, status_kurzbz) SELECT anrechnung_id, 'approved' as status_kurzbz FROM lehre.tbl_anrechnung WHERE genehmigt_von is not null;

		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_anrechnung_anrechnungstatus TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_anrechnung_anrechnungstatus TO vilesci;
		GRANT SELECT, UPDATE ON lehre.seq_anrechnung_anrechnungstatus_anrechnungstatus_id TO vilesci;
		GRANT SELECT, UPDATE ON lehre.seq_anrechnung_anrechnungstatus_anrechnungstatus_id TO web;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_anrechnung_anrechnungstatus: '.$db->db_last_error().'</strong><br>';
	else
		echo ' lehre.tbl_anrechnung_anrechnungstatus: Tabelle hinzugefuegt<br>';
}

// Added Bezeichnung 'berufliche Praxis' to Anrechnungbegruendung
if ($result = @$db->db_query("SELECT 1 FROM lehre.tbl_anrechnung_begruendung WHERE bezeichnung = 'berufliche Praxis';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO lehre.tbl_anrechnung_begruendung (bezeichnung) VALUES('berufliche Praxis');";
		if (!$db->db_query($qry))
			echo '<strong>lehre.tbl_anrechnung_begruendung '.$db->db_last_error().'</strong><br>';
		else
			echo ' lehre.tbl_anrechnung_begruendung: Added bezeichnung "berufliche Praxis" <br>';
	}
}

// Added Bezeichnung 'Hochschulzeugnis' to Anrechnungbegruendung
if ($result = @$db->db_query("SELECT 1 FROM lehre.tbl_anrechnung_begruendung WHERE bezeichnung = 'Hochschulzeugnis';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO lehre.tbl_anrechnung_begruendung (bezeichnung) VALUES('Hochschulzeugnis');";
		if (!$db->db_query($qry))
			echo '<strong>lehre.tbl_anrechnung_begruendung '.$db->db_last_error().'</strong><br>';
		else
			echo ' lehre.tbl_anrechnung_begruendung: Added bezeichnung "Hochschulzeugnis" <br>';
	}
}

// Add permission to apply for Anrechnung
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'student/anrechnung_beantragen';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('student/anrechnung_beantragen', 'Anrechnung beantragen');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for student/anrechnung_beantragen<br>';
	}
}

// Add permission to approve Anrechnung
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'lehre/anrechnung_genehmigen';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('lehre/anrechnung_genehmigen', 'Anrechnung genehmigen');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for lehre/anrechnung_genehmigen<br>';
	}
}

// Add permission to recommend Anrechnung
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'lehre/anrechnung_empfehlen';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('lehre/anrechnung_empfehlen', 'Anrechnung empfehlen');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for lehre/anrechnung_empfehlen<br>';
	}
}

// Add permission to create Anrechnung
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'lehre/anrechnung_anlegen';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('lehre/anrechnung_anlegen', 'Anrechnung anlegen');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for lehre/anrechnung_anlegen<br>';
	}
}

// INSERT,DELETE,UPDATE Berechtigung für tbl_dokumentprestudent hinzufügen
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_dokumentprestudent' AND table_schema='public' AND grantee='web' AND privilege_type in ('INSERT','DELETE','UPDATE')"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT INSERT, DELETE, UPDATE ON public.tbl_dokumentprestudent TO web;";

		if(!$db->db_query($qry))
			echo '<strong>dokumentprestudent Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted privileges to public.tbl_dokumentprestudent';
	}
}

// Add table zgvpruefungstatus
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_zgvpruefungstatus LIMIT 1;"))
{
	$qry = "
		CREATE TABLE public.tbl_zgvpruefungstatus
		(
			status_kurzbz character varying(32),
			bezeichnung character varying(256)
		);
		ALTER TABLE public.tbl_zgvpruefungstatus ADD CONSTRAINT status_kurzbz PRIMARY KEY (status_kurzbz);
		INSERT INTO public.tbl_zgvpruefungstatus(status_kurzbz, bezeichnung) VALUES('pruefung_stg', 'Wird vom Studiengang geprüft');
		INSERT INTO public.tbl_zgvpruefungstatus(status_kurzbz, bezeichnung) VALUES('rejected', 'Vom Studiengang abgelehnt');
		INSERT INTO public.tbl_zgvpruefungstatus(status_kurzbz, bezeichnung) VALUES('accepted', 'Vom Studiengang akzeptiert');
		INSERT INTO public.tbl_zgvpruefungstatus(status_kurzbz, bezeichnung) VALUES('accepted_pruefung', 'Vom Studiengang akzeptiert mit Prüfung');
		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_zgvpruefungstatus TO vilesci;
		GRANT SELECT ON public.tbl_zgvpruefungstatus TO web;
	";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_zgvpruefungstatus: '.$db->db_last_error().'</strong><br>';
	else
		echo ' public.tbl_zgvpruefungstatus: Tabelle hinzugefuegt<br>';
}

// Add table zgvpruefung
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_zgvpruefung LIMIT 1;"))
{
	$qry = "
		CREATE TABLE public.tbl_zgvpruefung
		(
			zgvpruefung_id integer NOT NULL,
			prestudent_id integer NOT NULL,
			insertamum timestamp without time zone,
			insertvon character varying(32),
			updateamum timestamp without time zone,
			updatevon character varying(32)
		);
		CREATE SEQUENCE public.tbl_zgvpruefung_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;
		ALTER TABLE public.tbl_zgvpruefung ADD CONSTRAINT pk_tbl_zgvpruefung PRIMARY KEY (zgvpruefung_id);
		ALTER TABLE public.tbl_zgvpruefung ALTER COLUMN zgvpruefung_id SET DEFAULT nextval('public.tbl_zgvpruefung_id_seq');
		ALTER TABLE public.tbl_zgvpruefung ADD CONSTRAINT fk_tbl_zgvpruefung_student FOREIGN KEY (prestudent_id) REFERENCES public.tbl_prestudent (prestudent_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		GRANT SELECT, UPDATE ON public.tbl_zgvpruefung_id_seq  TO vilesci;
		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_zgvpruefung TO vilesci;
		GRANT SELECT ON public.tbl_zgvpruefung TO web;
	";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_zgvpruefung: '.$db->db_last_error().'</strong><br>';
	else
		echo ' public.tbl_zgvpruefung: Tabelle hinzugefuegt<br>';
}

// Add table zgvpruefungstatus_status
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_zgvpruefungstatus_status LIMIT 1;"))
{
	$qry = "
		CREATE TABLE public.tbl_zgvpruefungstatus_status
		(
			zgv_pruefung_status_id integer NOT NULL,
			zgvpruefung_id integer NOT NULL,
			status character varying(32),
			datum timestamp without time zone DEFAULT now()
		);
		CREATE SEQUENCE public.tbl_zgvpruefungstatus_status_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;
		ALTER TABLE public.tbl_zgvpruefungstatus_status ADD CONSTRAINT pk_tbl_zgvpruefungstatus_status PRIMARY KEY (zgv_pruefung_status_id);
		ALTER TABLE public.tbl_zgvpruefungstatus_status ALTER COLUMN zgv_pruefung_status_id SET DEFAULT nextval('tbl_zgvpruefungstatus_status_id_seq');
		ALTER TABLE public.tbl_zgvpruefungstatus_status ADD CONSTRAINT fk_tbl_zgvpruefung_zgvpruefung FOREIGN KEY (zgvpruefung_id) REFERENCES public.tbl_zgvpruefung (zgvpruefung_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_zgvpruefungstatus_status ADD CONSTRAINT fk_tbl_zgvpruefung_status FOREIGN KEY (status) REFERENCES public.tbl_zgvpruefungstatus (status_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		GRANT SELECT, UPDATE ON public.tbl_zgvpruefungstatus_status_id_seq  TO vilesci;
		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_zgvpruefungstatus_status TO vilesci;
		GRANT SELECT ON public.tbl_zgvpruefungstatus_status TO web;
	";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_zgvpruefungstatus_status: '.$db->db_last_error().'</strong><br>';
	else
		echo ' public.tbl_zgvpruefungstatus_status: Tabelle hinzugefuegt<br>';
}

// Add table adressentyp
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_adressentyp LIMIT 1;"))
{
	$qry = "
		CREATE TABLE public.tbl_adressentyp
		(
			adressentyp_kurzbz varchar(32),
			bezeichnung varchar(256),
			bezeichnung_mehrsprachig varchar(256)[],
			sort smallint
		);

		COMMENT ON TABLE public.tbl_adressentyp IS 'Types of Addresses';
		ALTER TABLE public.tbl_adressentyp ADD CONSTRAINT pk_tbl_adressentyp PRIMARY KEY (adressentyp_kurzbz);

		INSERT INTO public.tbl_adressentyp(adressentyp_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES ('h', 'Hauptwohnsitz', '{\"Hauptwohnsitz\", \"Principal residence\"}', 1);
		INSERT INTO public.tbl_adressentyp(adressentyp_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES ('n', 'Nebenwohnsitz', '{\"Nebenwohnsitz\", \"Secondary residence\"}', 2);
		INSERT INTO public.tbl_adressentyp(adressentyp_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES ('ho', 'Homeoffice', '{\"Homeoffice\", \"Homeoffice\"}', 3);
		INSERT INTO public.tbl_adressentyp(adressentyp_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES ('r', 'Rechnungsadresse', '{\"Rechnungsadresse\", \"Billing address\"}', 4);
		INSERT INTO public.tbl_adressentyp(adressentyp_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES ('f', 'Firma', '{\"Firma\", \"Company\"}', 5);

		UPDATE public.tbl_adresse SET typ = 'f' WHERE person_id IS NULL AND (typ IS NULL OR typ = '');
		UPDATE public.tbl_adresse SET typ = 'h' WHERE person_id IS NOT NULL AND typ IS NULL;

		ALTER TABLE public.tbl_adresse ADD CONSTRAINT fk_tbl_adresse_adressentyp FOREIGN KEY (typ) REFERENCES public.tbl_adressentyp (adressentyp_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE public.tbl_adresse ALTER COLUMN typ TYPE varchar(32);

		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_adressentyp TO vilesci;
		GRANT SELECT ON public.tbl_adressentyp TO web;
	";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_adressentyp: '.$db->db_last_error().'</strong><br>';
	else
		echo ' public.tbl_adressentyp: Tabelle hinzugefuegt<br>';
}

// Add index to lehre.tbl_pruefung
if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_tbl_pruefung_student_uid'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_pruefung_student_uid ON lehre.tbl_pruefung USING btree (student_uid)";

		if (! $db->db_query($qry))
			echo '<strong>Indizes: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index fuer lehre.pruefung.student_uid hinzugefuegt<br>';
	}
}

// Added Buchungstyp "ZuschussIO"
if ($result = @$db->db_query("SELECT 1 FROM public.tbl_buchungstyp WHERE buchungstyp_kurzbz = 'ZuschussIO';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_buchungstyp (buchungstyp_kurzbz, beschreibung, standardtext) VALUES ('ZuschussIO', 'Zuschuss IO', 'Zuschuss Incoming Outgoing');";
		if (!$db->db_query($qry))
			echo '<strong>public.tbl_buchungstyp '.$db->db_last_error().'</strong><br>';
		else
			echo ' public.tbl_buchungstyp: Added buchungstyp "ZuschussIO" <br>';
	}
}
//Add Column statusgrund_kurzbz to public.tbl_status_grund
if(!@$db->db_query("SELECT statusgrund_kurzbz FROM public.tbl_status_grund LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_status_grund ADD COLUMN statusgrund_kurzbz varchar(32);
			ALTER TABLE public.tbl_status_grund ADD CONSTRAINT uk_tbl_statusgrund_kurzbz UNIQUE (statusgrund_kurzbz);
			";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_status_grund '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte statusgrund_kurzbz zu Tabelle public.tbl_status_grund hinzugefügt';
}

// Add permission to set gemeinde
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'basis/gemeinde';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('basis/gemeinde', 'Gemeindedaten pflegen');";

		if(!$db->db_query($qry))
			echo '<strong>bis.tbl_bisverwendung: '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for basis/gemeinde<br>';
	}
}

// Add column homeoffice to bis.tbl_bisverwendung
if (!$result = @$db->db_query("SELECT homeoffice FROM bis.tbl_bisverwendung LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_bisverwendung ADD COLUMN homeoffice boolean NOT NULL DEFAULT FALSE";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_bisverwendung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_bisverwendung: Spalte homeoffice hinzugefuegt';
}

// ADD COLUMN homeoffice to campus.tbl_zeitaufzeichnung
if(!$result = @$db->db_query("SELECT homeoffice FROM campus.tbl_zeitaufzeichnung LIMIT 1"))
{
	$qry = "
		ALTER TABLE campus.tbl_zeitaufzeichnung ADD COLUMN homeoffice boolean NOT NULL DEFAULT false ;
	";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_zeitaufzeichnung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_zeitaufzeichnung Spalte homeoffice hinzugefügt.';
}

// INDEX idx_anrechnung_anrechnung_status_anrechnung_id
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'idx_anrechnung_anrechnung_status_anrechnung_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'CREATE INDEX idx_anrechnung_anrechnung_status_anrechnung_id ON lehre.tbl_anrechnung_anrechnungstatus USING btree (anrechnung_id)';
		if (!$db->db_query($qry))
			echo '<strong>idx_anrechnung_anrechnung_status_anrechnung_id '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created Index idx_anrechnung_anrechnung_status_anrechnung_id';
	}
}

// Add Table lehre.tbl_lehrmodus
if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_lehrmodus LIMIT 1;"))
{
	$qry = "
		CREATE TABLE lehre.tbl_lehrmodus
		(
			lehrmodus_kurzbz varchar(32) NOT NULL,
			bezeichnung_mehrsprachig varchar(255)[],
			aktiv boolean DEFAULT true
		);
		ALTER TABLE lehre.tbl_lehrmodus ADD CONSTRAINT pk_lehrmodus PRIMARY KEY (lehrmodus_kurzbz);
		INSERT INTO lehre.tbl_lehrmodus(lehrmodus_kurzbz, bezeichnung_mehrsprachig) VALUES('regulaer', '{\"regulaer\",\"regular\"}');
		INSERT INTO lehre.tbl_lehrmodus(lehrmodus_kurzbz, bezeichnung_mehrsprachig) VALUES('standardisiert', '{\"standardisiert\",\"standardized\"}');
		GRANT SELECT ON lehre.tbl_lehrmodus TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_lehrmodus TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrmodus: '.$db->db_last_error().'</strong><br>';
	else
		echo ' lehre.tbl_lehrmodus: Tabelle hinzugefuegt<br>';
}

//Add Column lehrmodus_kurzbz to lehre.tbl_lehrveranstaltung
if(!@$db->db_query("SELECT lehrmodus_kurzbz FROM lehre.tbl_lehrveranstaltung LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN lehrmodus_kurzbz varchar(32);
			ALTER TABLE lehre.tbl_lehrveranstaltung ADD CONSTRAINT fk_lehrveranstaltung_lehrmodus FOREIGN KEY (lehrmodus_kurzbz) REFERENCES lehre.tbl_lehrmodus(lehrmodus_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
			";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte lehrmodus_kurzbz in lehre.tbl_lehrveranstaltung hinzugefügt';
}

// Spalte matr_aktiv in tbl_person zum Markieren von noch nicht scharfgeschaltenen Matrikelnummern vom Datenverbund
if(!$result = @$db->db_query("SELECT matr_aktiv FROM public.tbl_person LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_person ADD COLUMN matr_aktiv boolean NOT NULL DEFAULT false;
		COMMENT ON COLUMN public.tbl_person.matr_aktiv IS 'Zeigt an, ob Matrikelnummer aktiv oder nur vorläufig ist.';";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_person: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_person: Spalte matr_aktiv hinzugefuegt';

	$qry = "UPDATE public.tbl_person SET matr_aktiv = TRUE WHERE matr_nr IS NOT NULL;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_person: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_person: Spalte matr_aktiv auf true gesetzt';
}

// Creates table bis.tbl_oehbeitrag if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 1 FROM bis.tbl_oehbeitrag LIMIT 1'))
{
	$qry = 'CREATE TABLE bis.tbl_oehbeitrag (
			    oehbeitrag_id integer NOT NULL,
				studierendenbeitrag numeric(5,2) NOT NULL,
				versicherung numeric(5,2) NOT NULL,
				von_studiensemester_kurzbz varchar(16) NOT NULL,
				bis_studiensemester_kurzbz varchar(16),
				insertamum timestamp DEFAULT NOW(),
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

			COMMENT ON TABLE bis.tbl_oehbeitrag IS \'Table to save amount of Oehbeitrag and Versicherung.\';
			COMMENT ON COLUMN bis.tbl_oehbeitrag.studierendenbeitrag IS \'Amount of Studierendenbeitrag, Oehbeitrag without Versicherung.\';
			COMMENT ON COLUMN bis.tbl_oehbeitrag.versicherung IS \'Amount of Versicherung as part of the Oehbeitrag\';
			COMMENT ON COLUMN bis.tbl_oehbeitrag.von_studiensemester_kurzbz IS \'Semester from which amounts are valid\';
			COMMENT ON COLUMN bis.tbl_oehbeitrag.bis_studiensemester_kurzbz IS \'Semester until which amounts are valid\';

			CREATE SEQUENCE bis.seq_oehbeitrag_oehbeitrag_id
			    START WITH 1
			    INCREMENT BY 1
			    NO MINVALUE
			    NO MAXVALUE
			    CACHE 1;

			ALTER TABLE bis.tbl_oehbeitrag ALTER COLUMN oehbeitrag_id SET DEFAULT nextval(\'bis.seq_oehbeitrag_oehbeitrag_id\'::regclass);

			GRANT SELECT, UPDATE ON SEQUENCE bis.seq_oehbeitrag_oehbeitrag_id TO vilesci;
			GRANT SELECT, UPDATE ON SEQUENCE bis.seq_oehbeitrag_oehbeitrag_id TO fhcomplete;

			ALTER TABLE bis.tbl_oehbeitrag ADD CONSTRAINT pk_oehbeitrag PRIMARY KEY (oehbeitrag_id);

			ALTER TABLE bis.tbl_oehbeitrag ADD CONSTRAINT fk_oehbeitrag_von_studiensemester_kurzbz FOREIGN KEY (von_studiensemester_kurzbz) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE bis.tbl_oehbeitrag ADD CONSTRAINT fk_oehbeitrag_bis_studiensemester_kurzbz FOREIGN KEY (bis_studiensemester_kurzbz) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;

			ALTER TABLE bis.tbl_oehbeitrag ADD CONSTRAINT uk_oehbeitrag_von_studiensemester_kurzbz UNIQUE (von_studiensemester_kurzbz);';

	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_oehbeitrag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_oehbeitrag table created';

	$qry = 'GRANT SELECT ON TABLE bis.tbl_oehbeitrag TO web;';
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_oehbeitrag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on bis.tbl_oehbeitrag';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_oehbeitrag TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_oehbeitrag: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on bis.tbl_oehbeitrag';
}

// Add column melderelevant to public.tbl_studiengang and prefill values
if (!$result = @$db->db_query("SELECT melderelevant FROM public.tbl_studiengang LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_studiengang ADD COLUMN melderelevant boolean NOT NULL DEFAULT FALSE;
			UPDATE public.tbl_studiengang SET melderelevant = TRUE WHERE tbl_studiengang.studiengang_kz < 10000 AND tbl_studiengang.studiengang_kz <> 0;
			COMMENT ON COLUMN public.tbl_studiengang.melderelevant IS 'Zeigt an, ob Studierende aus Studiengang an Ministerien gemeldet werden müssen'";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_studiengang: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_studiengang: Neue Spalte melderelevant hinzugefuegt.';
}

// TABLE bis.tbl_bisstandort
if (!@$db->db_query("SELECT 1 FROM bis.tbl_bisstandort LIMIT 1"))
{
	$qry = "CREATE TABLE bis.tbl_bisstandort (
				standort_code integer,
				bezeichnung character varying(256),
				aktiv boolean NOT NULL DEFAULT TRUE,
				insertamum timestamp DEFAULT NOW(),
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);
			ALTER TABLE bis.tbl_bisstandort ADD CONSTRAINT pk_bisstandort PRIMARY KEY (standort_code);
			COMMENT ON TABLE bis.tbl_bisstandort IS 'Key-Table of Locations';";

	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_bisstandort '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Created table bis.tbl_bisstandort';

	// GRANT SELECT ON TABLE bis.tbl_bisstandort TO web;
	$qry = 'GRANT SELECT ON TABLE bis.tbl_bisstandort TO web;';
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_bisstandort '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on bis.tbl_bisstandort';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_bisstandort TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE bis.tbl_bisstandort TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_bisstandort '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on bis.tbl_bisstandort';
}

// Add columns foerderrelevant and standort to public.tbl_studiengang
if (!$result = @$db->db_query("SELECT foerderrelevant, standort_code FROM public.tbl_studiengang LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_studiengang ADD COLUMN foerderrelevant boolean NOT NULL DEFAULT TRUE;
			ALTER TABLE public.tbl_studiengang ADD COLUMN standort_code integer;
			ALTER TABLE public.tbl_studiengang ADD CONSTRAINT fk_studiengang_standort_code FOREIGN KEY (standort_code) REFERENCES bis.tbl_bisstandort(standort_code) ON DELETE RESTRICT ON UPDATE CASCADE;
			COMMENT ON COLUMN public.tbl_studiengang.foerderrelevant IS 'Zeigt an, ob Studierende aus Studiengang bei Meldung für Förderungen relevant sind';
			COMMENT ON COLUMN public.tbl_studiengang.standort_code IS 'Zu meldender Standortcode der Studierenden des Studiengangs'";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_studiengang: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_studiengang: Neue Spalten foerderrelevant, standort_code hinzugefuegt.';
}

// Add columns foerderrelevant and standort to public.tbl_prestudent
if (!$result = @$db->db_query("SELECT foerderrelevant, standort_code FROM public.tbl_prestudent LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_prestudent ADD COLUMN foerderrelevant boolean;
			ALTER TABLE public.tbl_prestudent ADD COLUMN standort_code integer;
			ALTER TABLE public.tbl_prestudent ADD CONSTRAINT fk_prestudent_standort_code FOREIGN KEY (standort_code) REFERENCES bis.tbl_bisstandort(standort_code) ON DELETE RESTRICT ON UPDATE CASCADE;
			UPDATE public.tbl_prestudent ps -- set foerderrelevant false for incoming, ausserordentlich, gsextern
			SET foerderrelevant = FALSE
			WHERE EXISTS (SELECT 1 FROM public.tbl_prestudentstatus WHERE prestudent_id = ps.prestudent_id AND status_kurzbz = 'Incoming') -- incoming
			   OR EXISTS (SELECT 1 FROM public.tbl_prestudent
											JOIN public.tbl_person USING (person_id)
											LEFT JOIN public.tbl_student USING (prestudent_id)
											LEFT JOIN bis.tbl_gsstudientyp USING (gsstudientyp_kurzbz)
							WHERE prestudent_id = ps.prestudent_id
							AND (SUBSTRING(matrikelnr, 4, 1) = '9' -- ausserordentlich
							  		OR studientyp_code = 'E')); -- extern
			COMMENT ON COLUMN public.tbl_prestudent.foerderrelevant IS 'Zeigt an, ob Studierende bei Meldung für Förderungen relevant sind. Überschreibt förderrelevant auf Studienganglevel.';
			COMMENT ON COLUMN public.tbl_prestudent.standort_code IS 'Zu meldender Standortcode des Studierenden. Überschreibt standort auf Studienganglevel.';";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudent: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_prestudent: Neue Spalten foerderrelevant, standort_code hinzugefuegt.';
}

// App 'dvuh' hinzufügen
if($result = $db->db_query("SELECT 1 FROM system.tbl_app WHERE app='dvuh'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO system.tbl_app(app) VALUES('dvuh');";

		if(!$db->db_query($qry))
			echo '<strong>App: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue App dvuh in system.tbl_app hinzugefügt';
	}
}

if($result = $db->db_query("SELECT 1 FROM system.tbl_app WHERE app = 'international' "))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO system.tbl_app(app) VALUES('international');";

		if(!$db->db_query($qry))
			echo '<strong>App: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue App international in system.tbl_app hinzugefügt';
	}
}


// Add DMS category "international_nachweis"
if ($result = @$db->db_query("SELECT 1 FROM campus.tbl_dms_kategorie WHERE kategorie_kurzbz = 'international_nachweis';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO campus.tbl_dms_kategorie (
					kategorie_kurzbz,
					bezeichnung,
					beschreibung,
					parent_kategorie_kurzbz,
					oe_kurzbz,
					berechtigung_kurzbz
			   ) VALUES(
					'international_nachweis',
					'International Nachweis',
					'Nachweis der Internationalisierungsmaßnahmen',
					'fas',
					'etw',
					NULL
			   );";
		if (!$db->db_query($qry))
			echo '<strong>campus.tbl_dms_kategorie '.$db->db_last_error().'</strong><br>';
		else
			echo ' campus.tbl_dms_kategorie: Added category "international_nachweis"!<br>';
	}
}
// Add table issue_status
if(!$result = @$db->db_query("SELECT 1 FROM system.tbl_issue_status LIMIT 1;"))
{
	$qry = "CREATE TABLE system.tbl_issue_status (
				  status_kurzbz character varying(32),
				  bezeichnung_mehrsprachig varchar(32)[]
			);

			COMMENT ON TABLE system.tbl_issue_status IS 'Tabelle zur Pflege von Bearbeitungsstatus von issues.';

			ALTER TABLE system.tbl_issue_status ADD CONSTRAINT pk_tbl_issue_status PRIMARY KEY (status_kurzbz);

			INSERT INTO system.tbl_issue_status (status_kurzbz, bezeichnung_mehrsprachig) VALUES('new', '{\"neu\",\"new\"}');
			INSERT INTO system.tbl_issue_status (status_kurzbz, bezeichnung_mehrsprachig) VALUES('inProgress', '{\"in Bearbeitung\",\"in progress\"}');
			INSERT INTO system.tbl_issue_status (status_kurzbz, bezeichnung_mehrsprachig) VALUES('resolved', '{\"behoben\",\"resolved\"}');";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_issue_status: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_issue_status: Tabelle hinzugefuegt';

	// GRANT SELECT ON TABLE system.tbl_issue_status TO web;
	$qry = 'GRANT SELECT ON TABLE system.tbl_issue_status TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_issue_status '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_issue_status';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_issue_status TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_issue_status TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_issue_status '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_issue_status';
}

// Add table fehlertyp
if(!$result = @$db->db_query("SELECT 1 FROM system.tbl_fehlertyp LIMIT 1;"))
{
	$qry = "CREATE TABLE system.tbl_fehlertyp (
				  fehlertyp_kurzbz character varying(32),
				  bezeichnung_mehrsprachig varchar(32)[]
			);

			COMMENT ON TABLE system.tbl_fehlertyp IS 'Tabelle mit fehlertyp für tbl_fehler';

			ALTER TABLE system.tbl_fehlertyp ADD CONSTRAINT pk_tbl_fehlertyp PRIMARY KEY (fehlertyp_kurzbz);

			INSERT INTO system.tbl_fehlertyp (fehlertyp_kurzbz, bezeichnung_mehrsprachig) VALUES('warning', '{\"warnung\",\"warning\"}');
			INSERT INTO system.tbl_fehlertyp (fehlertyp_kurzbz, bezeichnung_mehrsprachig) VALUES('error', '{\"fehler\",\"error\"}');
			INSERT INTO system.tbl_fehlertyp (fehlertyp_kurzbz, bezeichnung_mehrsprachig) VALUES('info', '{\"info\",\"info\"}');";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehlertyp: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_fehlertyp: Tabelle hinzugefuegt';

	// GRANT SELECT ON TABLE system.tbl_fehlertyp TO web;
	$qry = 'GRANT SELECT ON TABLE system.tbl_fehlertyp TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_fehlertyp '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_fehlertyp';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_fehlertyp TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_fehlertyp TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_fehlertyp '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_fehlertyp';
}

// Add table fehler
if(!$result = @$db->db_query("SELECT 1 FROM system.tbl_fehler LIMIT 1;"))
{
	$qry = "CREATE TABLE system.tbl_fehler (
				  fehlercode character varying(64),
				  fehler_kurzbz character varying(64) UNIQUE,
				  fehlercode_extern character varying(64),
				  fehlertext text,
				  fehlertyp_kurzbz character varying(32) NOT NULL,
				  app character varying(32) NOT NULL
			);

			COMMENT ON TABLE system.tbl_fehler IS 'Tabelle zur Pflege von Fehlerfällen';
			COMMENT ON COLUMN system.tbl_fehler.fehlercode IS 'Eindeutiger interner Fehlercode';
			COMMENT ON COLUMN system.tbl_fehler.fehler_kurzbz IS 'Eindeutige Kurzbezeichnung für den Fehler';
			COMMENT ON COLUMN system.tbl_fehler.fehlercode_extern IS 'Code für von vordefinierte, von externen Systemen produzierte Fehler';
			COMMENT ON COLUMN system.tbl_fehler.fehlertext IS 'Interner, eigens definierter Fehlertext mit Platzhaltern für Parameter';
			COMMENT ON COLUMN system.tbl_fehler.fehlertyp_kurzbz IS 'Typ bzw Schweregrad (z.B. warnung, fehler)';
			COMMENT ON COLUMN system.tbl_fehler.app IS 'Ursprungsapp des Fehlers';

			ALTER TABLE system.tbl_fehler ADD CONSTRAINT pk_tbl_fehler PRIMARY KEY (fehlercode);
			ALTER TABLE system.tbl_fehler ADD CONSTRAINT fk_tbl_fehler_fehlertyp_kurzbz FOREIGN KEY (fehlertyp_kurzbz) REFERENCES system.tbl_fehlertyp(fehlertyp_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_fehler ADD CONSTRAINT fk_tbl_fehler_app FOREIGN KEY (app) REFERENCES system.tbl_app(app) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_fehler ADD CONSTRAINT uk_tbl_fehler_fehlercode_fehler_kurzbz UNIQUE (fehlercode, fehler_kurzbz); -- for upsert ON CONFLICT
			ALTER TABLE system.tbl_fehler ADD CONSTRAINT uk_tbl_fehler_fehlercode_extern_app UNIQUE (fehlercode_extern, app); -- for recognizing external errors

			INSERT INTO system.tbl_fehler (fehlercode, fehlertext, fehlertyp_kurzbz, app) VALUES ('UNKNOWN_ERROR', 'Fehler ist aufgetreten', 'error', 'core');
			";


	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehler: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_fehler: Tabelle hinzugefuegt';

	// GRANT SELECT ON TABLE system.tbl_fehler TO web;
	$qry = 'GRANT SELECT, UPDATE ON TABLE system.tbl_fehler TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_fehler '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_fehler';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_fehler TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_fehler TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_fehler '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_fehler';
}

// Add table fehler_zustaendigkeiten
if(!$result = @$db->db_query("SELECT 1 FROM system.tbl_fehler_zustaendigkeiten LIMIT 1;"))
{
	$qry = "CREATE TABLE system.tbl_fehler_zustaendigkeiten (
				  fehlerzustaendigkeiten_id character varying(64) NOT NULL,
				  fehlercode character varying(64) NOT NULL,
				  person_id integer,
				  oe_kurzbz character varying(32),
				  funktion_kurzbz character varying(16)
			);

			COMMENT ON TABLE system.tbl_fehler_zustaendigkeiten IS 'Tabelle zum Hinzufügen für Zuständigkeiten für einzelne Fehler';
			COMMENT ON COLUMN system.tbl_fehler_zustaendigkeiten.fehlercode IS 'Eindeutiger interner Fehlercode';
			COMMENT ON COLUMN system.tbl_fehler_zustaendigkeiten.person_id IS 'person_id der zuständigen Person';
			COMMENT ON COLUMN system.tbl_fehler_zustaendigkeiten.oe_kurzbz IS 'Zuständigkeit für einen fehlercode für eine ganze OE';
			COMMENT ON COLUMN system.tbl_fehler_zustaendigkeiten.funktion_kurzbz IS 'Zusätzliche Einschränkung der OE Zuständigkeit nach funktion';

			CREATE SEQUENCE system.seq_fehlerzustaendigkeiten_id
				START WITH 1
				INCREMENT BY 1
				NO MINVALUE
				NO MAXVALUE
				CACHE 1;

			ALTER TABLE ONLY system.tbl_fehler_zustaendigkeiten ALTER COLUMN fehlerzustaendigkeiten_id SET DEFAULT nextval('system.seq_fehlerzustaendigkeiten_id'::regclass);

			GRANT SELECT, UPDATE ON SEQUENCE system.seq_fehlerzustaendigkeiten_id TO vilesci;
			GRANT SELECT, UPDATE ON SEQUENCE system.seq_fehlerzustaendigkeiten_id TO fhcomplete;

			ALTER TABLE system.tbl_fehler_zustaendigkeiten ADD CONSTRAINT pk_tbl_fehler_zustaendigkeiten PRIMARY KEY (fehlerzustaendigkeiten_id);
			ALTER TABLE system.tbl_fehler_zustaendigkeiten ADD CONSTRAINT fk_tbl_fehler_zustaendigkeiten_fehlercode FOREIGN KEY (fehlercode) REFERENCES system.tbl_fehler(fehlercode) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_fehler_zustaendigkeiten ADD CONSTRAINT fk_tbl_fehler_zustaendigkeiten_person_id FOREIGN KEY (person_id) REFERENCES public.tbl_person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_fehler_zustaendigkeiten ADD CONSTRAINT fk_tbl_fehler_zustaendigkeiten_oe_kurzbz FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit(oe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_fehler_zustaendigkeiten ADD CONSTRAINT fk_tbl_fehler_zustaendigkeiten_funktion_kurzbz FOREIGN KEY (funktion_kurzbz) REFERENCES public.tbl_funktion(funktion_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;

			ALTER TABLE system.tbl_fehler_zustaendigkeiten ADD CONSTRAINT uk_tbl_fehler_zustaendigkeiten_fehlercode_person_id UNIQUE (fehlercode, person_id);
			ALTER TABLE system.tbl_fehler_zustaendigkeiten ADD CONSTRAINT uk_tbl_fehler_zustaendigkeiten_fehlercode_oe_kurzbz_funktion_kurzbz UNIQUE (fehlercode, oe_kurzbz, funktion_kurzbz);";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehler_zustaendigkeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_fehler_zustaendigkeiten: Tabelle hinzugefuegt';

	// GRANT SELECT ON TABLE system.tbl_fehler_zustaendigkeiten TO web;
	$qry = 'GRANT SELECT ON TABLE system.tbl_fehler_zustaendigkeiten TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_fehler_zustaendigkeiten '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_fehler_zustaendigkeiten';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_fehler_zustaendigkeiten TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_fehler_zustaendigkeiten TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_fehler_zustaendigkeiten '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_fehler_zustaendigkeiten';
}

// Add table issue
if(!$result = @$db->db_query("SELECT 1 FROM system.tbl_issue LIMIT 1;"))
{
	$qry = "CREATE TABLE system.tbl_issue (
				  issue_id integer,
				  fehlercode character varying(64) NOT NULL,
				  inhalt text,
				  fehlercode_extern character varying(64),
				  inhalt_extern text,
				  person_id integer,
				  oe_kurzbz character varying(32),
				  datum timestamp without time zone NOT NULL,
				  verarbeitetvon character varying(32),
				  verarbeitetamum timestamp without time zone,
				  status_kurzbz character varying(32) NOT NULL,
				  insertvon character varying(32),
				  insertamum timestamp without time zone DEFAULT now(),
				  updatevon character varying(32),
				  updateamum timestamp without time zone
			);

			COMMENT ON TABLE system.tbl_issue IS 'Tabelle zur Verfolgung von Problemen/Fehlern von verschiedenen Systemen';
			COMMENT ON COLUMN system.tbl_issue.issue_id IS 'Primärschlüssel';
			COMMENT ON COLUMN system.tbl_issue.fehlercode IS 'Identifikationscode  des Problems/Fehlers, kann von anderem System kommen oder eigens definiert sein';
			COMMENT ON COLUMN system.tbl_issue.inhalt IS 'Fehlertext';
			COMMENT ON COLUMN system.tbl_issue.fehlercode_extern IS 'Externer, von App geschriebener Fehlercode. Zum Auseinanderhalten der externen Fehler, die nicht in tbl_fehler definiert sind.';
			COMMENT ON COLUMN system.tbl_issue.inhalt_extern IS 'von externem System kommender Fehlermeldungstext';
			COMMENT ON COLUMN system.tbl_issue.datum IS 'Tag und Zeit des Auftritts des Problems';
			COMMENT ON COLUMN system.tbl_issue.verarbeitetvon IS 'uid des Nutzers, der das Problem verarbeitet hat';
			COMMENT ON COLUMN system.tbl_issue.verarbeitetamum IS 'Tag und Zeit der Problemverarbeitung';
			COMMENT ON COLUMN system.tbl_issue.person_id IS 'Id der Person, für welche das issue besteht';
			COMMENT ON COLUMN system.tbl_issue.oe_kurzbz IS 'Betroffene Organisationseinheit';
			COMMENT ON COLUMN system.tbl_issue.status_kurzbz IS 'Verarbeitsungsstatus';

			CREATE SEQUENCE system.seq_issue_id
				START WITH 1
				INCREMENT BY 1
				NO MINVALUE
				NO MAXVALUE
				CACHE 1;

			ALTER TABLE ONLY system.tbl_issue ALTER COLUMN issue_id SET DEFAULT nextval('system.seq_issue_id'::regclass);

			GRANT SELECT, UPDATE ON SEQUENCE system.seq_issue_id TO vilesci;
			GRANT SELECT, UPDATE ON SEQUENCE system.seq_issue_id TO fhcomplete;

			ALTER TABLE system.tbl_issue ADD CONSTRAINT pk_tbl_issue PRIMARY KEY (issue_id);

			ALTER TABLE system.tbl_issue ADD CONSTRAINT fk_tbl_issue_fehlercode FOREIGN KEY (fehlercode) REFERENCES system.tbl_fehler(fehlercode) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_issue ADD CONSTRAINT fk_tbl_issue_verarbeitetvon FOREIGN KEY (verarbeitetvon) REFERENCES public.tbl_benutzer(uid) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_issue ADD CONSTRAINT fk_tbl_issue_person_id FOREIGN KEY (person_id) REFERENCES public.tbl_person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_issue ADD CONSTRAINT fk_tbl_issue_oe_kurzbz FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit(oe_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;

			ALTER TABLE system.tbl_issue ADD CONSTRAINT chk_tbl_issue_person_id_oe_kurzbz CHECK (person_id IS NOT NULL OR oe_kurzbz IS NOT NULL);

			CREATE INDEX idx_tbl_issue_person_id ON system.tbl_issue USING btree (person_id);
			CREATE INDEX idx_tbl_issue_oe_kurzbz ON system.tbl_issue USING btree (oe_kurzbz);";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_issue: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_issue: Tabelle hinzugefuegt';

	// GRANT SELECT ON TABLE system.tbl_issue TO web;
	$qry = 'GRANT SELECT, UPDATE ON TABLE system.tbl_issue TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_issue '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_issue';

	// GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_issue TO vilesci;
	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_issue TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_issue '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_issue';
}

// Add permission to manage issues
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'system/issues_verwalten';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('system/issues_verwalten', 'Issues verwalten');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>system.tbl_berechtigung: Added permission for system/issues_verwalten';
	}
}

// Add permission to manage bpk in FAS
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'student/bpk';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('student/bpk', 'BPK verwalten');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>system.tbl_berechtigung: Added permission for student/bpk';
	}
}

// Add index to campus.tbl_zeitaufzeichnung.uid
if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_tbl_zeitaufzeichnung_uid'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_zeitaufzeichnung_uid ON campus.tbl_zeitaufzeichnung USING btree (uid)";

		if (! $db->db_query($qry))
			echo '<strong>Indizes: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index fuer campus.tbl_zeitaufzeichnung.uid hinzugefuegt<br>';
	}
}

// Change size of wawi.tbl_kostenstelle.kostenstelle_nr from character varying(4) to character varying(6)
if ($result = $db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='wawi' AND TABLE_NAME='tbl_kostenstelle' AND COLUMN_NAME = 'kostenstelle_nr' AND character_maximum_length < 6"))
{
	if ($db->db_num_rows($result) > 0)
	{
		$qry = "ALTER TABLE wawi.tbl_kostenstelle ALTER COLUMN kostenstelle_nr TYPE varchar(6);";

		if(!$db->db_query($qry))
			echo '<strong>wawi.tbl_kostenstelle '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte kostenstelle_nr in wawi.tbl_kostenstelle von varchar(4) auf varchar(6) geändert<br>';
	}
}

// Add column parameterFuerBehebung to system.tbl_issue
if(!$result = @$db->db_query("SELECT behebung_parameter FROM system.tbl_issue LIMIT 1"))
{
	$qry = 'ALTER TABLE system.tbl_issue ADD COLUMN behebung_parameter JSONB;';
	if(!$db->db_query($qry))
		echo '<strong>system.tbl_issue: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added column behebung_parameter to table system.tbl_issue';
}

// Add table campus.tbl_zeitwunsch_gueltigkeit and migrate initial data
if($result = $db->db_query("SELECT * FROM information_schema.tables WHERE table_schema LIKE 'campus' AND table_name = 'tbl_zeitwunsch_gueltigkeit'"))
{
	if ($db->db_fetch_object($result) == false)
	{
		$qry = "
			CREATE TABLE campus.tbl_zeitwunsch_gueltigkeit
			(
				zeitwunsch_gueltigkeit_id INTEGER NOT NULL,
				mitarbeiter_uid CHARACTER VARYING(32) NOT NULL,
				von DATE,
				bis DATE,
				insertamum TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
				insertvon CHARACTER VARYING(32),
				updateamum TIMESTAMP WITHOUT TIME ZONE,
				updatevon CHARACTER VARYING(32)
			);

			CREATE SEQUENCE campus.seq_zeitwunsch_gueltigkeit_zeitwunsch_gueltigkeit_id
				INCREMENT BY 1
				NO MAXVALUE
				NO MINVALUE
				CACHE 1;

			-- Add Primary Key
			ALTER TABLE campus.tbl_zeitwunsch_gueltigkeit ADD CONSTRAINT pk_zeitwunsch_gueltigkeit_zeitwunsch_gueltigkeit_id PRIMARY KEY (zeitwunsch_gueltigkeit_id);
			ALTER TABLE campus.tbl_zeitwunsch_gueltigkeit ALTER COLUMN zeitwunsch_gueltigkeit_id SET DEFAULT nextval('campus.seq_zeitwunsch_gueltigkeit_zeitwunsch_gueltigkeit_id');

			-- Add Permissions
			GRANT SELECT, UPDATE ON SEQUENCE campus.seq_zeitwunsch_gueltigkeit_zeitwunsch_gueltigkeit_id TO vilesci;
			GRANT SELECT, UPDATE ON SEQUENCE campus.seq_zeitwunsch_gueltigkeit_zeitwunsch_gueltigkeit_id TO web;

			GRANT SELECT, INSERT, UPDATE, DELETE ON campus.tbl_zeitwunsch_gueltigkeit TO vilesci;
			GRANT SELECT, INSERT, UPDATE ON campus.tbl_zeitwunsch_gueltigkeit TO web;

			-- Initial data migration
			INSERT INTO campus.tbl_zeitwunsch_gueltigkeit
			(
				mitarbeiter_uid,
				von,
				bis,
				insertamum,
				insertvon,
				updateamum,
				updatevon
			)
			SELECT * FROM
			(
				-- Unique Mitarbeiter from Zeitwunsch Tabelle, Start of actual Studiensemester and open end
				SELECT DISTINCT mitarbeiter_uid,
				(SELECT start FROM public.tbl_studiensemester WHERE start <= NOW() AND ende >= NOW()),
				NULL::DATE AS \"ende\",
				NOW(),
				'system',
				NOW(),
				'system'
				FROM campus.tbl_zeitwunsch
				ORDER BY mitarbeiter_uid
			) AS init_data
		";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_zeitwunsch_gueltigkeit: '.$db->db_last_error().'</strong><br>';
		else
			echo 'campus.tbl_zeitwunsch_gueltigkeit: Tabelle hinzugefuegt<br>';
	}
}

// Add column zeitwunsch_id (as new primary key) and zeitwunsch_gueltigkeit_id to campus.tbl_zeitwunsch
if ($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'campus' AND table_name = 'tbl_zeitwunsch' AND column_name IN ('zeitwunsch_id', 'zeitwunsch_gueltigkeit_id')"))
{
	if ($db->db_num_rows($result) <> 2)
	{
		$qry = "
			ALTER TABLE campus.tbl_zeitwunsch DROP CONSTRAINT IF EXISTS pk_tbl_zeitwunsch; -- Drop combined pk stunde/mitarbeiter_uid/tag

			-- Add primary key and foreign key
			ALTER TABLE campus.tbl_zeitwunsch ADD COLUMN IF NOT EXISTS zeitwunsch_id INTEGER;
			ALTER TABLE campus.tbl_zeitwunsch ADD COLUMN IF NOT EXISTS zeitwunsch_gueltigkeit_id INTEGER;

			-- Add comments
			COMMENT ON COLUMN campus.tbl_zeitwunsch.zeitwunsch_gueltigkeit_id IS 'Ordnet die Zeitwuensche einer Gueltigkeitsdauer von-bis zu';
			COMMENT ON COLUMN campus.tbl_zeitwunsch.mitarbeiter_uid IS 'DEPRECATED';

			CREATE SEQUENCE campus.seq_zeitwunsch_zeitwunsch_id
				INCREMENT BY 1
				NO MAXVALUE
				NO MINVALUE
				CACHE 1;

			ALTER TABLE campus.tbl_zeitwunsch ALTER COLUMN zeitwunsch_id SET DEFAULT nextval('campus.seq_zeitwunsch_zeitwunsch_id');
			UPDATE campus.tbl_zeitwunsch SET zeitwunsch_id = nextval('campus.seq_zeitwunsch_zeitwunsch_id');

			ALTER TABLE campus.tbl_zeitwunsch ADD CONSTRAINT pk_zeitwunsch_zeitwunsch_id PRIMARY KEY (zeitwunsch_id);
			ALTER TABLE campus.tbl_zeitwunsch ADD CONSTRAINT fk_zeitwunsch_zeitwunsch_gueltigkeit_id FOREIGN KEY (zeitwunsch_gueltigkeit_id) REFERENCES campus.tbl_zeitwunsch_gueltigkeit(zeitwunsch_gueltigkeit_id) ON DELETE RESTRICT ON UPDATE CASCADE;

			-- Set initial zeitwunsch_gueltigkeit_id values to Gueltigkeitszeitraum of actual Studiensemester
			UPDATE campus.tbl_zeitwunsch
			SET zeitwunsch_gueltigkeit_id = (
				SELECT zeitwunsch_gueltigkeit_id
				FROM campus.tbl_zeitwunsch_gueltigkeit zwg
				WHERE tbl_zeitwunsch.mitarbeiter_uid = zwg.mitarbeiter_uid
			);

			-- Set primary key and foreign key NOT NULL
			ALTER TABLE campus.tbl_zeitwunsch ALTER COLUMN zeitwunsch_id SET NOT NULL;
			ALTER TABLE campus.tbl_zeitwunsch ALTER COLUMN zeitwunsch_gueltigkeit_id SET NOT NULL;

			-- Set permissions
			GRANT SELECT, UPDATE ON SEQUENCE campus.seq_zeitwunsch_zeitwunsch_id TO vilesci;
			GRANT SELECT, UPDATE ON SEQUENCE campus.seq_zeitwunsch_zeitwunsch_id TO web;
		";

		if (!$db->db_query($qry))
			echo '<strong>campus.tbl_zeitwunsch: ' . $db->db_last_error() . '</strong><br>';
		else
			echo '<br>campus.tbl_zeitwunsch: Neue Spalte zeitwunsch_id hinzugefuegt.';
	}
}

// Add index beschreibung to system.tbl_webservicelog
if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_webservicelog_beschreibung'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_webservicelog_beschreibung ON system.tbl_webservicelog USING btree (beschreibung)";

		if (! $db->db_query($qry))
			echo '<strong>Indizes: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index fuer system.tbl_webservicelog hinzugefuegt';
	}
}

// Add column melde_studiengang_kz to public.tbl_studiengang and prefill values
if (!$result = @$db->db_query("SELECT melde_studiengang_kz FROM public.tbl_studiengang LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_studiengang ADD COLUMN melde_studiengang_kz varchar(32);
			UPDATE public.tbl_studiengang stg SET melde_studiengang_kz = (SELECT lpad(abs(studiengang_kz)::text, 4, '0') FROM tbl_studiengang WHERE studiengang_kz = stg.studiengang_kz) WHERE melderelevant AND lgartcode IS NULL;
			UPDATE public.tbl_studiengang stg SET melde_studiengang_kz = (SELECT (SELECT lpad(erhalter_kz::text, 3, '0') FROM public.tbl_erhalter) || lpad(abs(studiengang_kz)::text, 4, '0') FROM tbl_studiengang WHERE studiengang_kz = stg.studiengang_kz) WHERE melderelevant AND lgartcode IS NOT NULL;
			COMMENT ON COLUMN public.tbl_studiengang.melde_studiengang_kz IS 'Studiengangskennzahl, mit der der Studiengang gemeldet wird (z.B. Bismeldung)'";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_studiengang: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_studiengang: Neue Spalte melde_studiengang_kz hinzugefuegt.';
}

// ADD COLUMN insertamum to system.tbl_fehler_zustaendigkeiten
if(!@$db->db_query("SELECT insertamum FROM system.tbl_fehler_zustaendigkeiten LIMIT 1"))
{
	$qry = "ALTER TABLE system.tbl_fehler_zustaendigkeiten ADD COLUMN insertamum timestamp DEFAULT now();";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehler_zustaendigkeiten '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte insertamum in system.tbl_fehler_zustaendigkeiten hinzugefügt';
}

// ADD COLUMN insertvon to system.tbl_fehler_zustaendigkeiten
if(!@$db->db_query("SELECT insertvon FROM system.tbl_fehler_zustaendigkeiten LIMIT 1"))
{
	$qry = "ALTER TABLE system.tbl_fehler_zustaendigkeiten ADD COLUMN insertvon varchar(32);";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehler_zustaendigkeiten '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte insertvon in system.tbl_fehler_zustaendigkeiten hinzugefügt';
}

// Insert postive Zeitsperre 'Zeitverfuegbarkeit' to tbl_zeitsperretyp
if($result = @$db->db_query("SELECT 1 FROM campus.tbl_zeitsperretyp WHERE zeitsperretyp_kurzbz = 'ZVerfueg';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO campus.tbl_zeitsperretyp(zeitsperretyp_kurzbz, beschreibung) VALUES('ZVerfueg', 'Zeitverfügbarkeit');";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_zeitsperretyp '.$db->db_last_error().'</strong><br>';
		else
			echo 'campus.tbl_zeitsperretyp: Added value \'ZVerfueg\'<br>';
	}
}

// Add permission to administrate zeitverfuegbarkeiten
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'lehre/zeitverfuegbarkeit';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('lehre/zeitverfuegbarkeit', 'Zeitverfuegbarkeit verwalten');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for lehre/zeitverfuegbarkeit<br>';
	}
}

//Add column wahlname to public.tbl_person
if(!@$db->db_query("SELECT wahlname FROM public.tbl_person LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_person ADD COLUMN wahlname varchar(128);";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_person '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_person: Spalte wahlname hinzugefügt';
}

// Adds Column wahlname to campus.vw_student
if (!$result = @$db->db_query('SELECT wahlname FROM campus.vw_student LIMIT 1'))
{
	$qry = "
		CREATE OR REPLACE VIEW campus.vw_student AS
		SELECT tbl_benutzer.uid,
		    tbl_student.matrikelnr,
		    tbl_student.prestudent_id,
		    tbl_student.studiengang_kz,
		    tbl_student.semester,
		    tbl_student.verband,
		    tbl_student.gruppe,
		    tbl_benutzer.person_id,
		    tbl_benutzer.alias,
		    tbl_person.geburtsnation,
		    tbl_person.sprache,
		    tbl_person.anrede,
		    tbl_person.titelpost,
		    tbl_person.titelpre,
		    tbl_person.nachname,
		    tbl_person.vorname,
		    tbl_person.vornamen,
		    tbl_person.gebdatum,
		    tbl_person.gebort,
		    tbl_person.gebzeit,
		    tbl_person.foto,
		    tbl_person.anmerkung,
		    tbl_person.homepage,
		    tbl_person.svnr,
		    tbl_person.ersatzkennzeichen,
		    tbl_person.geschlecht,
		    tbl_person.familienstand,
		    tbl_person.anzahlkinder,
		    tbl_benutzer.aktiv,
		    tbl_student.updateamum,
		    tbl_student.updatevon,
		    tbl_student.insertamum,
		    tbl_student.insertvon,
		    tbl_student.ext_id,
		    tbl_benutzer.updateaktivam,
		    tbl_benutzer.updateaktivvon,
		    tbl_benutzer.aktivierungscode,
		    ( SELECT tbl_kontakt.kontakt
		           FROM tbl_kontakt
		          WHERE tbl_kontakt.person_id = tbl_person.person_id AND tbl_kontakt.kontakttyp::text = 'email'::text
		          ORDER BY tbl_kontakt.zustellung DESC
		         LIMIT 1) AS email_privat,
		    tbl_person.wahlname
		   FROM public.tbl_student
		     JOIN public.tbl_benutzer ON tbl_student.student_uid::text = tbl_benutzer.uid::text
		     JOIN public.tbl_person USING (person_id);";

	 if (!$db->db_query($qry))
 		echo '<strong>campus.vw_student: '.$db->db_last_error().'</strong><br>';
 	else
 		echo '<br>campus.vw_student: added column wahlname';
}


// Adds Column wahlname to campus.vw_benutzer
if (!$result = @$db->db_query('SELECT wahlname FROM campus.vw_benutzer LIMIT 1'))
{
	$qry = "
		CREATE OR REPLACE VIEW campus.vw_benutzer AS
			SELECT tbl_benutzer.person_id,
		    tbl_benutzer.uid,
		    tbl_benutzer.alias,
		    tbl_person.geburtsnation,
		    tbl_person.sprache,
		    tbl_person.anrede,
		    tbl_person.titelpost,
		    tbl_person.titelpre,
		    tbl_person.nachname,
		    tbl_person.vorname,
		    tbl_person.vornamen,
		    tbl_person.gebdatum,
		    tbl_person.gebort,
		    tbl_person.gebzeit,
		    tbl_person.foto,
		    tbl_person.geschlecht,
		    tbl_person.anmerkung,
		    tbl_person.homepage,
		    tbl_person.svnr,
		    tbl_person.ersatzkennzeichen,
		    tbl_person.familienstand,
		    tbl_person.anzahlkinder,
		    tbl_benutzer.aktiv,
		    tbl_benutzer.insertamum,
		    tbl_benutzer.insertvon,
		    tbl_benutzer.updateamum,
		    tbl_benutzer.updatevon,
		    tbl_benutzer.ext_id,
		    tbl_person.wahlname
		   FROM public.tbl_benutzer
		     JOIN public.tbl_person USING (person_id);";

	 if (!$db->db_query($qry))
 		echo '<strong>campus.vw_benutzer: '.$db->db_last_error().'</strong><br>';
 	else
 		echo '<br>campus.vw_benutzer: added column wahlname';
}

// Adds Column wahlname to campus.vw_mitarbeiter
if (!$result = @$db->db_query('SELECT wahlname FROM campus.vw_mitarbeiter LIMIT 1'))
{
	$qry = "
		CREATE OR REPLACE VIEW campus.vw_mitarbeiter AS
		SELECT tbl_benutzer.uid,
			tbl_mitarbeiter.ausbildungcode,
			tbl_mitarbeiter.personalnummer,
			tbl_mitarbeiter.kurzbz,
			tbl_mitarbeiter.lektor,
			tbl_mitarbeiter.fixangestellt,
			tbl_mitarbeiter.telefonklappe,
			tbl_benutzer.person_id,
			tbl_benutzer.alias,
			tbl_person.geburtsnation,
			tbl_person.sprache,
			tbl_person.anrede,
			tbl_person.titelpost,
			tbl_person.titelpre,
			tbl_person.nachname,
			tbl_person.vorname,
			tbl_person.vornamen,
			tbl_person.gebdatum,
			tbl_person.gebort,
			tbl_person.gebzeit,
			tbl_person.foto,
			tbl_mitarbeiter.anmerkung,
			tbl_person.homepage,
			tbl_person.svnr,
			tbl_person.ersatzkennzeichen,
			tbl_person.geschlecht,
			tbl_person.familienstand,
			tbl_person.anzahlkinder,
			tbl_mitarbeiter.ort_kurzbz,
			tbl_benutzer.aktiv,
			tbl_mitarbeiter.bismelden,
			tbl_mitarbeiter.standort_id,
			tbl_mitarbeiter.updateamum,
			tbl_mitarbeiter.updatevon,
			tbl_mitarbeiter.insertamum,
			tbl_mitarbeiter.insertvon,
			tbl_mitarbeiter.ext_id,
			tbl_benutzer.aktivierungscode,
			( SELECT tbl_kontakt.kontakt
				   FROM tbl_kontakt
				  WHERE tbl_kontakt.person_id = tbl_person.person_id AND tbl_kontakt.kontakttyp::text = 'email'::text
				  ORDER BY tbl_kontakt.zustellung DESC
				 LIMIT 1) AS email_privat,
			tbl_benutzer.updateaktivam,
			tbl_benutzer.updateaktivvon,
			GREATEST(tbl_person.updateamum, tbl_benutzer.updateamum, tbl_mitarbeiter.updateamum) AS lastupdate,
			tbl_person.wahlname
			FROM public.tbl_mitarbeiter
			 JOIN public.tbl_benutzer ON tbl_mitarbeiter.mitarbeiter_uid::text = tbl_benutzer.uid::text
			 JOIN public.tbl_person USING (person_id);";

	 if (!$db->db_query($qry))
 		echo '<strong>campus.vw_mitarbeiter: '.$db->db_last_error().'</strong><br>';
 	else
 		echo '<br>campus.vw_mitarbeiter: added column wahlname';
}

// Creates table public.tbl_gruppe_manager if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 1 FROM public.tbl_gruppe_manager LIMIT 1'))
{
	$qry = 'CREATE TABLE public.tbl_gruppe_manager (
				gruppe_manager_id integer,
				gruppe_kurzbz varchar(32) NOT NULL,
				uid varchar(32) NOT NULL,
				insertamum timestamp DEFAULT NOW(),
				insertvon varchar(32)
			);

			COMMENT ON TABLE public.tbl_gruppe_manager IS \'Table to save assignments groups to their managers.\';
			COMMENT ON COLUMN public.tbl_gruppe_manager.gruppe_kurzbz IS \'Name of group\';
			COMMENT ON COLUMN public.tbl_gruppe_manager.uid IS \'User id of group manager\';

			CREATE SEQUENCE public.seq_gruppe_manager_gruppe_manager_id
				START WITH 1
				INCREMENT BY 1
				NO MINVALUE
				NO MAXVALUE
				CACHE 1;

			ALTER TABLE public.tbl_gruppe_manager ALTER COLUMN gruppe_manager_id SET DEFAULT nextval(\'public.seq_gruppe_manager_gruppe_manager_id\'::regclass);

			GRANT SELECT, UPDATE ON SEQUENCE public.seq_gruppe_manager_gruppe_manager_id TO vilesci;
			GRANT SELECT, UPDATE ON SEQUENCE public.seq_gruppe_manager_gruppe_manager_id TO fhcomplete;

			ALTER TABLE public.tbl_gruppe_manager ADD CONSTRAINT pk_gruppe_manager PRIMARY KEY (gruppe_manager_id);

			ALTER TABLE public.tbl_gruppe_manager ADD CONSTRAINT fk_gruppe_manager_gruppe_kurzbz FOREIGN KEY (gruppe_kurzbz) REFERENCES public.tbl_gruppe(gruppe_kurzbz) ON UPDATE CASCADE ON DELETE CASCADE;
			ALTER TABLE public.tbl_gruppe_manager ADD CONSTRAINT fk_gruppe_manager_uid FOREIGN KEY (uid) REFERENCES public.tbl_benutzer(uid) ON UPDATE CASCADE ON DELETE RESTRICT;

			ALTER TABLE public.tbl_gruppe_manager ADD CONSTRAINT uk_gruppe_manager_gruppe_kurzbz_uid UNIQUE (gruppe_kurzbz, uid);';

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_gruppe_manager: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_gruppe_manager table created';

	$qry = 'GRANT SELECT ON TABLE public.tbl_gruppe_manager TO web;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_gruppe_manager: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on public.tbl_gruppe_manager';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE public.tbl_gruppe_manager TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_gruppe_manager: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on public.tbl_gruppe_manager';
}

// Add permission for managing user groups
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'lehre/gruppenmanager';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('lehre/gruppenmanager', 'Manager einer Gruppe werden und die Gruppe verwalten');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: Added permission for lehre/gruppenmanager<br>';
	}
}

// NOTE(chris): Add "Template" to "Lehrtyp"
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_lehrtyp WHERE bezeichnung = 'Template';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO lehre.tbl_lehrtyp(lehrtyp_kurzbz, bezeichnung) VALUES('tpl', 'Template');";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_lehrtyp '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>lehre.tbl_lehrtyp: Added "Template"';
	}
}

// NOTE(chris): Add Column "lehrveranstaltung_template_id" in tbl_lehrveranstaltung
if(!$result = @$db->db_query("SELECT lehrveranstaltung_template_id FROM lehre.tbl_lehrveranstaltung LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN lehrveranstaltung_template_id integer;
		ALTER TABLE lehre.tbl_lehrveranstaltung ADD CONSTRAINT fk_lehrveranstaltung_template FOREIGN KEY (lehrveranstaltung_template_id) REFERENCES lehre.tbl_lehrveranstaltung (lehrveranstaltung_id) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_lehrveranstaltung: Spalte lehrveranstaltung_template_id hinzugefuegt';
}

// NOTE(chris): Add Webservice Rights for lehrveranstaltung::loadTemplates
if($result = @$db->db_query("SELECT 1 FROM system.tbl_webservicerecht WHERE berechtigung_kurzbz='soap/studienordnung' AND methode = 'loadTemplates' AND klasse = 'lehrveranstaltung';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, insertamum, insertvon, klasse) VALUES('soap/studienordnung', 'loadTemplates', now(), 'checksystem', 'lehrveranstaltung');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_webservicerecht '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>system.tbl_webservicerecht: soap/studienordnung/loadTemplates->lehrveranstaltung hinzugefügt';
	}
}

// NOTE(chris): Add Webservice Rights for lehrveranstaltung::loadTemplateByName
if($result = @$db->db_query("SELECT 1 FROM system.tbl_webservicerecht WHERE berechtigung_kurzbz='soap/studienordnung' AND methode = 'loadTemplateByName' AND klasse = 'lehrveranstaltung';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_webservicerecht(berechtigung_kurzbz, methode, insertamum, insertvon, klasse) VALUES('soap/studienordnung', 'loadTemplateByName', now(), 'checksystem', 'lehrveranstaltung');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_webservicerecht '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>system.tbl_webservicerecht: soap/studienordnung/loadTemplateByName->lehrveranstaltung hinzugefügt';
	}
}

// Add permission for avoiding checks when saving prestudentstatus
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'student/keine_studstatuspruefung';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('student/keine_studstatuspruefung', 'Ermöglicht Umgehen der Checks beim Speichern der Presstudentstatus');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo 'system.tbl_berechtigung: Added permission for student/keine_studstatuspruefung<br>';

	}
}

// Neue Funktion get_ects_summe_schulisch
if(!@$db->db_query("SELECT public.get_ects_summe_schulisch('', 0, 0)"))
{
	$qry = 'CREATE FUNCTION public.get_ects_summe_schulisch(character varying, integer, integer) RETURNS numeric
			LANGUAGE plpgsql
			AS $_$
				DECLARE var_student_uid ALIAS FOR $1;
				DECLARE var_prestudent_id ALIAS FOR $2;
				DECLARE var_studiengang_kz ALIAS FOR $3;
				DECLARE var_einstiegsausbildungssemester integer;
				DECLARE var_einstiegsstudiensemester_kurzbz varchar(32);
				DECLARE var_einstiegsorgform_kurzbz varchar(32);
				DECLARE rec_quereinstiegs_studiensemester RECORD;
				DECLARE sum_quereinstiegs_ects numeric(4, 1) := 0;
				DECLARE sum_schulische_ects numeric(4, 1) := 0;


				BEGIN

				-- IF STUDENT IS QUEREINSTEIGER, GET ECTS SUMME OF ANGERECHNETE SEMESTER
				-- Get Einstiegssemester
				   SELECT INTO var_einstiegsausbildungssemester , var_einstiegsstudiensemester_kurzbz, var_einstiegsorgform_kurzbz  ausbildungssemester, studiensemester_kurzbz, orgform_kurzbz from public.tbl_prestudentstatus
				   WHERE prestudent_id = var_prestudent_id
				   AND status_kurzbz = \'Student\'
				   ORDER BY datum, insertamum, ext_id
				   LIMIT 1;

				-- If Einstiegssemester > 1 (= Quereinsteiger)
				IF (var_einstiegsausbildungssemester > 1) THEN
				-- ...get all Quereinstiegssemester
				   FOR rec_quereinstiegs_studiensemester IN SELECT studiensemester_kurzbz FROM public.tbl_studiensemester
					 WHERE ende <= (select start from public.tbl_studiensemester WHERE studiensemester_kurzbz = var_einstiegsstudiensemester_kurzbz )
					 ORDER BY start DESC
					 LIMIT (var_einstiegsausbildungssemester -1)
				-- ...loop the Quereinstiegssemester
				   LOOP
				-- ...and sum up ECTS of each Quereinstiegssemester
					  sum_quereinstiegs_ects = sum_quereinstiegs_ects + (SELECT
								SUM(tbl_lehrveranstaltung.ects)
							FROM
								lehre.tbl_studienplan
								JOIN lehre.tbl_studienplan_lehrveranstaltung USING (studienplan_id)
								JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
							WHERE
								tbl_studienplan.studienplan_id = (
									SELECT
										studienplan_id
									FROM
										lehre.tbl_studienordnung
										JOIN lehre.tbl_studienplan USING (studienordnung_id)
										JOIN lehre.tbl_studienplan_semester USING (studienplan_id)
										WHERE tbl_studienordnung.studiengang_kz = var_studiengang_kz
										AND tbl_studienplan_semester.semester = var_einstiegsausbildungssemester - 1
										AND tbl_studienplan_semester.studiensemester_kurzbz = rec_quereinstiegs_studiensemester.studiensemester_kurzbz
										AND tbl_studienplan.orgform_kurzbz = var_einstiegsorgform_kurzbz

									LIMIT 1
								)
							AND tbl_studienplan_lehrveranstaltung.semester = var_einstiegsausbildungssemester
							AND studienplan_lehrveranstaltung_id_parent IS NULL -- auf Modulebene
							AND tbl_studienplan_lehrveranstaltung.export = TRUE);

							var_einstiegsausbildungssemester = var_einstiegsausbildungssemester - 1;
				   END LOOP;
				END IF;


				-- GET ECTS SUMME OF ALLE BISHER ANGERECHNETEN LEHRVERANSTALTUNGEN. ANRECHNUNGSGRUND: SCHULISCH.
				SELECT INTO sum_schulische_ects COALESCE(SUM(ects), 0) FROM (
									SELECT
										lehrveranstaltung_id, studiensemester_kurzbz, ects
									FROM
										lehre.tbl_zeugnisnote
										LEFT JOIN lehre.tbl_anrechnung USING(lehrveranstaltung_id, studiensemester_kurzbz)
										JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
										JOIN public.tbl_student USING(student_uid)
									WHERE
										tbl_zeugnisnote.note = 6
										AND student_uid = var_student_uid
										AND lehre.tbl_anrechnung.prestudent_id IN (tbl_student.prestudent_id, NULL)
										AND begruendung_id != 5  -- universitäre ECTS nicht mitrechnen
                        				AND begruendung_id != 4  -- berufliche ECTS nicht mitrechnen
                        				AND (anrechnung_id IS NULL OR (anrechnung_id IS NOT NULL AND genehmigt_von IS NOT NULL )) -- Anrechnungen aus Zeit vor Anrechnungstool ODER digitale Anrechnungen mit Noteneintrag UND Genehmigung (wichtig, um zurückgenommene Genehmigungen, die in der Notentabelle noch als angerechnet eingetragen sind, rauszufiltern)

									UNION

									SELECT
										lehrveranstaltung_id, studiensemester_kurzbz, ects
									FROM
										lehre.tbl_anrechnung
										JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
										JOIN public.tbl_student USING(prestudent_id)
									WHERE
										genehmigt_von IS NOT NULL
										AND student_uid = var_student_uid
										AND begruendung_id != 5  -- universitäre ECTS nicht mitrechnen
                        				AND begruendung_id != 4  -- berufliche ECTS nicht mitrechnen
				) lvsangerechnet;

				-- BUILD ECTS SUMME OF QUEREINSTIEGSSEMESTER- + ANGERECHNETEN LVs-ECTS
				-- Summe aller bisher schulisch begründet angerechneten LVs + der Quereinstiegssemester
				sum_schulische_ects = sum_schulische_ects + sum_quereinstiegs_ects;

				RETURN sum_schulische_ects ;

				END;
				$_$;

			ALTER FUNCTION public.get_ects_summe_schulisch(character varying, integer, integer) OWNER TO fhcomplete;';

	if(!$db->db_query($qry))
		echo '<strong>public.get_ects_summe_schulisch(student_uid, prestudent_id, studiengang_kz): '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Funktion <b>public.get_ects_summe_schulisch(student_uid, prestudent_id, studiengang_kz)</b> hinzugefügt';
}

// Neue Funktion get_ects_summe_beruflich
if(!@$db->db_query("SELECT public.get_ects_summe_beruflich('')"))
{
	$qry = 'CREATE FUNCTION public.get_ects_summe_beruflich(character varying) RETURNS numeric
			LANGUAGE plpgsql
			AS $_$
				DECLARE var_student_uid ALIAS FOR $1;
				DECLARE sum_berufliche_ects numeric(4, 1) := 0;

				BEGIN

					SELECT INTO sum_berufliche_ects COALESCE(SUM(ects), 0) FROM (
						SELECT
							lehrveranstaltung_id, studiensemester_kurzbz, ects
						FROM
							lehre.tbl_zeugnisnote
							LEFT JOIN lehre.tbl_anrechnung USING(lehrveranstaltung_id, studiensemester_kurzbz)
							JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
							JOIN public.tbl_student USING(student_uid)
						WHERE
							tbl_zeugnisnote.note = 6
							AND student_uid = var_student_uid
							AND lehre.tbl_anrechnung.prestudent_id IN (tbl_student.prestudent_id, NULL)
							AND begruendung_id = 4  -- beruflich
							AND (anrechnung_id IS NULL OR (anrechnung_id IS NOT NULL AND genehmigt_von IS NOT NULL )) -- Anrechnungen aus Zeit vor Anrechnungstool ODER digitale Anrechnungen mit Noteneintrag UND Genehmigung (wichtig, um zurückgenommene Genehmigungen, die in der Notentabelle noch als angerechnet eingetragen sind, rauszufiltern)

						UNION

						SELECT
							lehrveranstaltung_id, studiensemester_kurzbz, ects
						FROM
							lehre.tbl_anrechnung
							JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
							JOIN public.tbl_student USING(prestudent_id)
						WHERE
							genehmigt_von is not null
							AND student_uid = var_student_uid
							AND begruendung_id = 4  -- beruflich
					) lvsangerechnet;

				RETURN sum_berufliche_ects;

				END;
				$_$;

			ALTER FUNCTION public.get_ects_summe_beruflich(character varying) OWNER TO fhcomplete;';

	if(!$db->db_query($qry))
		echo '<strong>public.get_ects_summe_beruflich(student_uid): '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Funktion <b>public.get_ects_summe_beruflich(student_uid)</b> hinzugefügt';
}

// Grant SELECT to bis.tbl_gsprogramm for web-user
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_gsprogramm' AND table_schema='bis' AND grantee='web' AND privilege_type in ('SELECT')"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "GRANT SELECT ON bis.tbl_gsprogramm TO web;";

		if(!$db->db_query($qry))
			echo '<strong>tbl_gsprogramm Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted SELECT privileges to web for bis.tbl_gsprogramm';
	}
}

// Insert document type Grant Agreement
if($result = @$db->db_query("SELECT 1 FROM public.tbl_dokument WHERE dokument_kurzbz = 'GrantAgr';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, bezeichnung_mehrsprachig) VALUES('GrantAgr', 'Grant Agreement', '{\"Grant Agreement\",\"Grant Agreement\"}');";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_dokument '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_dokument: Added value \'GrantAgr\'<br>';
	}
}

//Spalte aktiv zu bis.tbl_zgv hinzufügen
if (!$result = @$db->db_query("SELECT aktiv FROM bis.tbl_zgv LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_zgv ADD COLUMN aktiv BOOLEAN NOT NULL DEFAULT true;";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_zgv '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte aktiv zu bis.tbl_zgv hinzugefügt';
}

//Spalte aktiv zu bis.tbl_zgvmaster hinzufügen
if (!$result = @$db->db_query("SELECT aktiv FROM bis.tbl_zgvmaster LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_zgvmaster ADD COLUMN aktiv BOOLEAN NOT NULL DEFAULT true;";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_zgvmaster '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte aktiv zu bis.tbl_zgvmaster hinzugefügt';
}

//Spalte aktiv zu bis.tbl_zgvdoktor hinzufügen
if (!$result = @$db->db_query("SELECT aktiv FROM bis.tbl_zgvdoktor LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_zgvdoktor ADD COLUMN aktiv BOOLEAN NOT NULL DEFAULT true;";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_zgvdoktor '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte aktiv zu bis.tbl_zgvdoktor hinzugefügt';
}

// ADD COLUMN studienkennung_uni to bis.tbl_gsprogramm
if(!@$db->db_query("SELECT studienkennung_uni FROM bis.tbl_gsprogramm LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_gsprogramm ADD COLUMN studienkennung_uni varchar(32);";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_gsprogramm '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte studienkennung_uni in bis.tbl_gsprogramm hinzugefügt';
}

// ADD COLUMN herkunftsland_code to bis.tbl_bisio
if(!@$db->db_query("SELECT herkunftsland_code FROM bis.tbl_bisio LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_bisio ADD COLUMN herkunftsland_code varchar(3);
			ALTER TABLE bis.tbl_bisio ADD CONSTRAINT fk_tbl_bisio_herkunftsland_code FOREIGN KEY (herkunftsland_code) REFERENCES bis.tbl_nation(nation_code) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_bisio '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte herkunftsland_code in bis.tbl_bisio hinzugefügt';
}

// *** Pruefung und hinzufuegen der neuen Attribute und Tabellen
echo '<H2>Pruefe Tabellen und Attribute!</H2>';

$tabellen=array(
	"bis.tbl_bisorgform" => array("bisorgform_kurzbz","code","bezeichnung"),
	"bis.tbl_archiv"  => array("archiv_id","studiensemester_kurzbz","meldung","html","studiengang_kz","insertamum","insertvon","typ"),
	"bis.tbl_aufenthaltfoerderung" => array("aufenthaltfoerderung_code", "bezeichnung"),
	"bis.tbl_bisio_aufenthaltfoerderung" => array("bisio_id","aufenthaltfoerderung_code"),
	"bis.tbl_ausbildung"  => array("ausbildungcode","ausbildungbez","ausbildungbeschreibung"),
	"bis.tbl_berufstaetigkeit"  => array("berufstaetigkeit_code","berufstaetigkeit_bez","berufstaetigkeit_kurzbz"),
	"bis.tbl_beschaeftigungsart1"  => array("ba1code","ba1bez","ba1kurzbz","ba1code_bis"),
	"bis.tbl_beschaeftigungsart2"  => array("ba2code","ba2bez"),
	"bis.tbl_beschaeftigungsausmass"  => array("beschausmasscode","beschausmassbez","min","max"),
	"bis.tbl_besqual"  => array("besqualcode","besqualbez"),
	"bis.tbl_bisfunktion"  => array("bisverwendung_id","studiengang_kz","sws","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_bisio"  => array("bisio_id","mobilitaetsprogramm_code","nation_code","von","bis","zweck_code","student_uid","updateamum","updatevon","insertamum","insertvon","ext_id","ort","universitaet","lehreinheit_id","ects_erworben","ects_angerechnet","herkunftsland_code"),
	"bis.tbl_bisio_zweck"  => array("bisio_id","zweck_code"),
	"bis.tbl_bisstandort"  => array("standort_code","bezeichnung","aktiv","insertamum","insertvon","updateamum","updatevon"),
	"bis.tbl_bisverwendung"  => array("bisverwendung_id","ba1code","ba2code","vertragsstunden","beschausmasscode","verwendung_code","mitarbeiter_uid","hauptberufcode","hauptberuflich","habilitation","beginn","ende","updateamum","updatevon","insertamum","insertvon","ext_id","dv_art","inkludierte_lehre","zeitaufzeichnungspflichtig","azgrelevant", "homeoffice"),
	"bis.tbl_bundesland"  => array("bundesland_code","kurzbz","bezeichnung"),
	"bis.tbl_entwicklungsteam"  => array("mitarbeiter_uid","studiengang_kz","besqualcode","beginn","ende","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_gemeinde"  => array("gemeinde_id","plz","name","ortschaftskennziffer","ortschaftsname","bulacode","bulabez","kennziffer"),
	"bis.tbl_gsstudientyp" => array("gsstudientyp_kurzbz","bezeichnung","studientyp_code"),
	"bis.tbl_gsprogrammtyp" => array("gsprogrammtyp_kurzbz","bezeichnung","programmtyp_code"),
	"bis.tbl_gsprogramm" => array("gsprogramm_id","programm_code","bezeichnung","gsprogrammtyp_kurzbz","studienkennung_uni"),
	"bis.tbl_hauptberuf"  => array("hauptberufcode","bezeichnung"),
	"bis.tbl_lgartcode"  => array("lgartcode","kurzbz","bezeichnung","beantragung","lgart_biscode"),
	"bis.tbl_mobilitaet" => array("mobilitaet_id","prestudent_id","mobilitaetstyp_kurzbz","studiensemester_kurzbz","mobilitaetsprogramm_code","gsprogramm_id","firma_id","status_kurzbz","ausbildungssemester","insertvon","insertamum","updatevon","updateamum"),
	"bis.tbl_mobilitaetstyp" => array("mobilitaetstyp_kurzbz","bezeichnung","aktiv"),
	"bis.tbl_mobilitaetsprogramm"  => array("mobilitaetsprogramm_code","kurzbz","beschreibung","sichtbar","sichtbar_outgoing"),
	"bis.tbl_nation"  => array("nation_code","entwicklungsstand","eu","ewr","kontinent","kurztext","langtext","engltext","sperre","nationengruppe_kurzbz", "iso3166_1_a2","iso3166_1_a3"),
	"bis.tbl_nationengruppe"  => array("nationengruppe_kurzbz","nationengruppe_bezeichnung","aktiv"),
	"bis.tbl_oehbeitrag"  => array("oehbeitrag_id","studierendenbeitrag","versicherung","von_studiensemester_kurzbz","bis_studiensemester_kurzbz","insertamum","insertvon","updateamum","updatevon"),
	"bis.tbl_orgform"  => array("orgform_kurzbz","code","bezeichnung","rolle","bisorgform_kurzbz","bezeichnung_mehrsprachig"),
	"bis.tbl_verwendung"  => array("verwendung_code","verwendungbez"),
	"bis.tbl_zgv"  => array("zgv_code","zgv_bez","zgv_kurzbz","bezeichnung","aktiv"),
	"bis.tbl_zgvmaster"  => array("zgvmas_code","zgvmas_bez","zgvmas_kurzbz","bezeichnung","aktiv"),
	"bis.tbl_zgvdoktor" => array("zgvdoktor_code", "zgvdoktor_bez", "zgvdoktor_kurzbz","bezeichnung","aktiv"),
	"bis.tbl_zweck"  => array("zweck_code","kurzbz","bezeichnung","incoming","outgoing"),
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
	"campus.tbl_dms_kategorie"  => array("kategorie_kurzbz","bezeichnung","beschreibung","parent_kategorie_kurzbz","oe_kurzbz","berechtigung_kurzbz"),
	"campus.tbl_dms_kategorie_gruppe" => array("kategorie_kurzbz","gruppe_kurzbz","insertamum","insertvon"),
	"campus.tbl_dms_version"  => array("dms_id","version","filename","mimetype","name","beschreibung","letzterzugriff","updateamum","updatevon","insertamum","insertvon","cis_suche","schlagworte"),
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
	"campus.tbl_zeitaufzeichnung"  => array("zeitaufzeichnung_id","uid","aktivitaet_kurzbz","projekt_kurzbz","start","ende","beschreibung","oe_kurzbz_1","oe_kurzbz_2","insertamum","insertvon","updateamum","updatevon","ext_id","service_id","kunde_uid","projektphase_id","homeoffice"),
	"campus.tbl_zeitaufzeichnung_gd"  => array("zeitaufzeichnung_gd_id","uid","studiensemester_kurzbz","selbstverwaltete_pause","insertamum","insertvon","updateamum","updatevon"),
	"campus.tbl_zeitsperre"  => array("zeitsperre_id","zeitsperretyp_kurzbz","mitarbeiter_uid","bezeichnung","vondatum","vonstunde","bisdatum","bisstunde","vertretung_uid","updateamum","updatevon","insertamum","insertvon","erreichbarkeit_kurzbz","freigabeamum","freigabevon"),
	"campus.tbl_zeitsperretyp"  => array("zeitsperretyp_kurzbz","beschreibung","farbe"),
	"campus.tbl_zeitwunsch"  => array("stunde","mitarbeiter_uid","tag","gewicht","updateamum","updatevon","insertamum","insertvon", "zeitwunsch_id", "zeitwunsch_gueltigkeit_id"),
	"campus.tbl_zeitwunsch_gueltigkeit"  => array("zeitwunsch_gueltigkeit_id","mitarbeiter_uid","von","bis","insertamum","insertvon", "updateamum","updatevon"),
	"fue.tbl_aktivitaet"  => array("aktivitaet_kurzbz","beschreibung","sort"),
	"fue.tbl_aufwandstyp" => array("aufwandstyp_kurzbz","bezeichnung"),
	"fue.tbl_projekt"  => array("projekt_kurzbz","nummer","titel","beschreibung","beginn","ende","oe_kurzbz","budget","farbe","aufwandstyp_kurzbz","ressource_id","anzahl_ma","aufwand_pt","projekt_id","projekttyp_kurzbz","zeitaufzeichnung"),
	"fue.tbl_projektphase"  => array("projektphase_id","projekt_kurzbz","projektphase_fk","bezeichnung","typ","beschreibung","start","ende","budget","insertamum","insertvon","updateamum","updatevon","personentage","farbe","ressource_id","zeitaufzeichnung"),
	"fue.tbl_projekttask"  => array("projekttask_id","projektphase_id","bezeichnung","beschreibung","aufwand","mantis_id","insertamum","insertvon","updateamum","updatevon","projekttask_fk","erledigt","ende","ressource_id","scrumsprint_id"),
	"fue.tbl_projekttyp"  => array("projekttyp_kurzbz","bezeichnung"),
	"fue.tbl_projekt_dokument"  => array("projekt_dokument_id","projektphase_id","projekt_kurzbz","dms_id"),
	"fue.tbl_projekt_ressource"  => array("projekt_ressource_id","projekt_kurzbz","projektphase_id","ressource_id","funktion_kurzbz","beschreibung","aufwand"),
	"fue.tbl_ressource"  => array("ressource_id","student_uid","mitarbeiter_uid","betriebsmittel_id","firma_id","bezeichnung","beschreibung","insertamum","insertvon","updateamum","updatevon"),
	"fue.tbl_scrumteam" => array("scrumteam_kurzbz","bezeichnung","punkteprosprint","tasksprosprint","gruppe_kurzbz"),
	"fue.tbl_scrumsprint" => array("scrumsprint_id","scrumteam_kurzbz","sprint_kurzbz","sprintstart","sprintende","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_abschlussbeurteilung"  => array("abschlussbeurteilung_kurzbz","bezeichnung","bezeichnung_english","sort"),
	"lehre.tbl_abschlusspruefung"  => array("abschlusspruefung_id","student_uid","vorsitz","pruefer1","pruefer2","pruefer3","abschlussbeurteilung_kurzbz","akadgrad_id","pruefungstyp_kurzbz","datum","uhrzeit","sponsion","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","note","protokoll","endezeit","pruefungsantritt_kurzbz","freigabedatum"),
	"lehre.tbl_abschlusspruefung_antritt"  => array("pruefungsantritt_kurzbz","bezeichnung","bezeichnung_english","sort"),
	"lehre.tbl_akadgrad"  => array("akadgrad_id","akadgrad_kurzbz","studiengang_kz","titel","geschlecht"),
	"lehre.tbl_anrechnung"  => array("anrechnung_id","prestudent_id","lehrveranstaltung_id","begruendung_id","lehrveranstaltung_id_kompatibel","genehmigt_von","insertamum","insertvon","updateamum","updatevon","ext_id", "dms_id", "studiensemester_kurzbz", "anmerkung_student", "empfehlung_anrechnung"),
	"lehre.tbl_anrechnungstatus"  => array("status_kurzbz", "bezeichnung_mehrsprachig"),
	"lehre.tbl_anrechnung_anrechnungstatus"  => array("anrechnungstatus_id", "anrechnung_id", "status_kurzbz", "datum", "insertamum", "insertvon"),
	"lehre.tbl_anrechnung_begruendung"  => array("begruendung_id","bezeichnung"),
	"lehre.tbl_betreuerart"  => array("betreuerart_kurzbz","beschreibung","aktiv"),
	"lehre.tbl_ferien"  => array("bezeichnung","studiengang_kz","vondatum","bisdatum"),
	"lehre.tbl_lehreinheit"  => array("lehreinheit_id","lehrveranstaltung_id","studiensemester_kurzbz","lehrfach_id","lehrform_kurzbz","stundenblockung","wochenrythmus","start_kw","raumtyp","raumtypalternativ","sprache","lehre","anmerkung","unr","lvnr","updateamum","updatevon","insertamum","insertvon","ext_id","lehrfach_id_old","gewicht"),
	"lehre.tbl_lehreinheitgruppe"  => array("lehreinheitgruppe_id","lehreinheit_id","studiengang_kz","semester","verband","gruppe","gruppe_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehreinheitmitarbeiter"  => array("lehreinheit_id","mitarbeiter_uid","lehrfunktion_kurzbz","semesterstunden","planstunden","stundensatz","faktor","anmerkung","bismelden","updateamum","updatevon","insertamum","insertvon","ext_id","standort_id","vertrag_id"),
	"lehre.tbl_lehrfach"  => array("lehrfach_id","studiengang_kz","fachbereich_kurzbz","kurzbz","bezeichnung","farbe","aktiv","semester","sprache","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehrform"  => array("lehrform_kurzbz","bezeichnung","verplanen","bezeichnung_kurz","bezeichnung_lang"),
	"lehre.tbl_lehrfunktion"  => array("lehrfunktion_kurzbz","beschreibung","standardfaktor","sort"),
	"lehre.tbl_lehrmittel" => array("lehrmittel_kurzbz","beschreibung","ort_kurzbz"),
	"lehre.tbl_lehrmodus" => array("lehrmodus_kurzbz","bezeichnung_mehrsprachig","aktiv"),
	"lehre.tbl_lehrtyp" => array("lehrtyp_kurzbz","bezeichnung"),
	"lehre.tbl_lehrveranstaltung"  => array("lehrveranstaltung_id","kurzbz","bezeichnung","lehrform_kurzbz","studiengang_kz","semester","sprache","ects","semesterstunden","anmerkung","lehre","lehreverzeichnis","aktiv","planfaktor","planlektoren","planpersonalkosten","plankostenprolektor","koordinator","sort","zeugnis","projektarbeit","updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung_english","orgform_kurzbz","incoming","lehrtyp_kurzbz","oe_kurzbz","raumtyp_kurzbz","anzahlsemester","semesterwochen","lvnr","farbe","semester_alternativ","old_lehrfach_id","sws","lvs","alvs","lvps","las","benotung","lvinfo","lehrauftrag","lehrmodus_kurzbz","lehrveranstaltung_template_id"),
	"lehre.tbl_lehrveranstaltung_kompatibel" => array("lehrveranstaltung_id","lehrveranstaltung_id_kompatibel"),
	"lehre.tbl_lvangebot" => array("lvangebot_id","lehrveranstaltung_id","studiensemester_kurzbz","gruppe_kurzbz","incomingplaetze","gesamtplaetze","anmeldefenster_start","anmeldefenster_ende","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_lvregel" => array("lvregel_id","lvregeltyp_kurzbz","operator","parameter","lvregel_id_parent","lehrveranstaltung_id","studienplan_lehrveranstaltung_id","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_lvregeltyp" => array("lvregeltyp_kurzbz","bezeichnung"),
	"lehre.tbl_notenschluessel" => array("notenschluessel_kurzbz","bezeichnung"),
	"lehre.tbl_notenschluesselaufteilung" => array("notenschluesselaufteilung_id","notenschluessel_kurzbz","note","punkte"),
	"lehre.tbl_notenschluesselzuordnung" => array("notenschluesselzuordnung_id","notenschluessel_kurzbz","lehrveranstaltung_id","studienplan_id","oe_kurzbz","studiensemester_kurzbz"),
	"lehre.tbl_note"  => array("note","bezeichnung","anmerkung","farbe","positiv","notenwert","aktiv","lehre","offiziell","bezeichnung_mehrsprachig","lkt_ueberschreibbar"),
	"lehre.tbl_projektarbeit"  => array("projektarbeit_id","projekttyp_kurzbz","titel","lehreinheit_id","student_uid","firma_id","note","punkte","beginn","ende","faktor","freigegeben","gesperrtbis","stundensatz","gesamtstunden","themenbereich","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","titel_english","seitenanzahl","abgabedatum","kontrollschlagwoerter","schlagwoerter","schlagwoerter_en","abstract", "abstract_en", "sprache","final"),
	"lehre.tbl_projektbetreuer"  => array("person_id","projektarbeit_id","betreuerart_kurzbz","note","faktor","name","punkte","stunden","stundensatz","updateamum","updatevon","insertamum","insertvon","ext_id","vertrag_id", "zugangstoken", "zugangstoken_gueltigbis"),
	"lehre.tbl_projekttyp"  => array("projekttyp_kurzbz","bezeichnung","aktiv"),
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
	"lehre.tbl_vertrag"  => array("vertrag_id","person_id","vertragstyp_kurzbz","bezeichnung","betrag","insertamum","insertvon","updateamum","updatevon","ext_id","anmerkung","vertragsdatum","lehrveranstaltung_id", "vertragsstunden", "vertragsstunden_studiensemester_kurzbz"),
	"lehre.tbl_vertrag_vertragsstatus"  => array("vertragsstatus_kurzbz","vertrag_id","uid","datum","ext_id","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_vertragstyp"  => array("vertragstyp_kurzbz","bezeichnung"),
	"lehre.tbl_vertragsstatus"  => array("vertragsstatus_kurzbz","bezeichnung"),
	"lehre.tbl_zeitfenster"  => array("wochentag","stunde","ort_kurzbz","studiengang_kz","gewicht"),
	"lehre.tbl_zeugnis"  => array("zeugnis_id","student_uid","zeugnis","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_zeugnisnote"  => array("lehrveranstaltung_id","student_uid","studiensemester_kurzbz","note","uebernahmedatum","benotungsdatum","bemerkung","updateamum","updatevon","insertamum","insertvon","ext_id","punkte"),
	"public.ci_apikey" => array("apikey_id","key","level","ignore_limits","date_created"),
	"public.tbl_adresse"  => array("adresse_id","person_id","name","strasse","plz","ort","gemeinde","nation","typ","heimatadresse","zustelladresse","firma_id","updateamum","updatevon","insertamum","insertvon","ext_id","rechnungsadresse","anmerkung", "co_name"),
	"public.tbl_adressentyp"  => array("adressentyp_kurzbz", "bezeichnung", "bezeichnung_mehrsprachig", "sort"),
	"public.tbl_akte"  => array("akte_id","person_id","dokument_kurzbz","uid","inhalt","mimetype","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id","dms_id","nachgereicht","anmerkung","titel_intern","anmerkung_intern","nachgereicht_am","ausstellungsnation","formal_geprueft_amum","archiv","signiert","stud_selfservice","akzeptiertamum"),
	"public.tbl_ampel"  => array("ampel_id","kurzbz","beschreibung","benutzer_select","deadline","vorlaufzeit","verfallszeit","insertamum","insertvon","updateamum","updatevon","email","verpflichtend","buttontext"),
	"public.tbl_ampel_benutzer_bestaetigt"  => array("ampel_benutzer_bestaetigt_id","ampel_id","uid","insertamum","insertvon"),
	"public.tbl_aufmerksamdurch"  => array("aufmerksamdurch_kurzbz","beschreibung","ext_id","bezeichnung", "aktiv"),
	"public.tbl_aufnahmeschluessel"  => array("aufnahmeschluessel"),
	"public.tbl_aufnahmetermin" => array("aufnahmetermin_id","aufnahmetermintyp_kurzbz","prestudent_id","termin","teilgenommen","bewertung","protokoll","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_aufnahmetermintyp" => array("aufnahmetermintyp_kurzbz","bezeichnung"),
	"public.tbl_bankverbindung"  => array("bankverbindung_id","person_id","name","anschrift","bic","blz","iban","kontonr","typ","verrechnung","updateamum","updatevon","insertamum","insertvon","ext_id","oe_kurzbz", "orgform_kurzbz"),
	"public.tbl_benutzer"  => array("uid","person_id","aktiv","alias","insertamum","insertvon","updateamum","updatevon","ext_id","updateaktivvon","updateaktivam","aktivierungscode"),
	"public.tbl_benutzerfunktion"  => array("benutzerfunktion_id","fachbereich_kurzbz","uid","oe_kurzbz","funktion_kurzbz","semester", "datum_von","datum_bis", "updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung","wochenstunden"),
	"public.tbl_benutzergruppe"  => array("uid","gruppe_kurzbz","studiensemester_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_bewerbungstermine" => array("bewerbungstermin_id","studiengang_kz","studiensemester_kurzbz","beginn","ende","nachfrist","nachfrist_ende","anmerkung", "insertamum", "insertvon", "updateamum", "updatevon","studienplan_id","nationengruppe_kurzbz"),
	"public.tbl_buchungstyp"  => array("buchungstyp_kurzbz","beschreibung","standardbetrag","standardtext","aktiv","credit_points"),
	"public.tbl_dokument"  => array("dokument_kurzbz","bezeichnung","ext_id","bezeichnung_mehrsprachig","dokumentbeschreibung_mehrsprachig","ausstellungsdetails"),
	"public.tbl_dokumentprestudent"  => array("dokument_kurzbz","prestudent_id","mitarbeiter_uid","datum","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_dokumentstudiengang"  => array("dokument_kurzbz","studiengang_kz","ext_id", "onlinebewerbung", "pflicht","beschreibung_mehrsprachig","nachreichbar","stufe"),
	"public.tbl_erhalter"  => array("erhalter_kz","kurzbz","bezeichnung","dvr","logo","zvr"),
	"public.tbl_fachbereich"  => array("fachbereich_kurzbz","bezeichnung","farbe","studiengang_kz","aktiv","ext_id","oe_kurzbz"),
	"public.tbl_filter" => array("filter_id","kurzbz","sql","valuename","showvalue","insertamum","insertvon","updateamum","updatevon","type","htmlattr", "bezeichnung"),
	"public.tbl_firma"  => array("firma_id","name","anmerkung","firmentyp_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id","schule","finanzamt","steuernummer","gesperrt","aktiv","lieferbedingungen","partner_code","lieferant"),
	"public.tbl_firma_mobilitaetsprogramm" => array("firma_id","mobilitaetsprogramm_code","ext_id"),
	"public.tbl_firma_organisationseinheit"  => array("firma_organisationseinheit_id","firma_id","oe_kurzbz","bezeichnung","kundennummer","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_firmentyp"  => array("firmentyp_kurzbz","beschreibung"),
	"public.tbl_firmatag"  => array("firma_id","tag","insertamum","insertvon"),
	"public.tbl_fotostatus"  => array("fotostatus_kurzbz","beschreibung"),
	"public.tbl_funktion"  => array("funktion_kurzbz","beschreibung","aktiv","fachbereich","semester"),
	"public.tbl_geschlecht"  => array("geschlecht","bezeichnung_mehrsprachig","sort"),
	"public.tbl_geschaeftsjahr"  => array("geschaeftsjahr_kurzbz","start","ende","bezeichnung"),
	"public.tbl_gruppe"  => array("gruppe_kurzbz","studiengang_kz","semester","bezeichnung","beschreibung","sichtbar","lehre","aktiv","sort","mailgrp","generiert","updateamum","updatevon","insertamum","insertvon","ext_id","orgform_kurzbz","gid","content_visible","gesperrt","zutrittssystem","aufnahmegruppe","direktinskription"),
	"public.tbl_kontakt"  => array("kontakt_id","person_id","kontakttyp","anmerkung","kontakt","zustellung","updateamum","updatevon","insertamum","insertvon","ext_id","standort_id"),
	"public.tbl_kontaktmedium"  => array("kontaktmedium_kurzbz","beschreibung"),
	"public.tbl_kontakttyp"  => array("kontakttyp","beschreibung","bezeichnung_mehrsprachig"),
	"public.tbl_konto"  => array("buchungsnr","person_id","studiengang_kz","studiensemester_kurzbz","buchungstyp_kurzbz","buchungsnr_verweis","betrag","buchungsdatum","buchungstext","mahnspanne","updateamum","updatevon","insertamum","insertvon","ext_id","credit_points", "zahlungsreferenz", "anmerkung"),
	"public.tbl_lehrverband"  => array("studiengang_kz","semester","verband","gruppe","aktiv","bezeichnung","ext_id","orgform_kurzbz","gid"),
	"public.tbl_log"  => array("log_id","executetime","mitarbeiter_uid","beschreibung","sql","sqlundo"),
	"public.tbl_mitarbeiter"  => array("mitarbeiter_uid","personalnummer","telefonklappe","kurzbz","lektor","fixangestellt","bismelden","stundensatz","ausbildungcode","ort_kurzbz","standort_id","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id","kleriker"),
	"public.tbl_msg_attachment" => array("attachment_id","message_id","name","filename"),
	"public.tbl_msg_message" => array("message_id","person_id","subject","body","priority","relationmessage_id","oe_kurzbz","insertamum","insertvon"),
	"public.tbl_msg_recipient" => array("message_id","person_id","token","sent","sentinfo","insertamum","insertvon","oe_kurzbz"),
	"public.tbl_msg_status" => array("message_id","person_id","status","statusinfo","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_notiz"  => array("notiz_id","titel","text","verfasser_uid","bearbeiter_uid","start","ende","erledigt","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_notizzuordnung"  => array("notizzuordnung_id","notiz_id","projekt_kurzbz","projektphase_id","projekttask_id","uid","person_id","prestudent_id","bestellung_id","lehreinheit_id","ext_id","anrechnung_id"),
	"public.tbl_notiz_dokument" => array("notiz_id","dms_id"),
	"public.tbl_ort"  => array("ort_kurzbz","bezeichnung","planbezeichnung","max_person","lehre","reservieren","aktiv","lageplan","dislozierung","kosten","ausstattung","updateamum","updatevon","insertamum","insertvon","ext_id","stockwerk","standort_id","telefonklappe","content_id","m2","gebteil","oe_kurzbz","arbeitsplaetze"),
	"public.tbl_ortraumtyp"  => array("ort_kurzbz","hierarchie","raumtyp_kurzbz"),
	"public.tbl_organisationseinheit" => array("oe_kurzbz", "oe_parent_kurzbz", "bezeichnung","organisationseinheittyp_kurzbz", "aktiv","mailverteiler","freigabegrenze","kurzzeichen","lehre","standort","warn_semesterstunden_frei","warn_semesterstunden_fix","standort_id"),
	"public.tbl_organisationseinheittyp" => array("organisationseinheittyp_kurzbz", "bezeichnung", "beschreibung"),
	"public.tbl_person"  => array("person_id","staatsbuergerschaft","geburtsnation","sprache","anrede","titelpost","titelpre","nachname","vorname","vornamen","gebdatum","gebort","gebzeit","foto","anmerkung","homepage","svnr","ersatzkennzeichen","familienstand","geschlecht","anzahlkinder","aktiv","insertamum","insertvon","updateamum","updatevon","ext_id","bundesland_code","kompetenzen","kurzbeschreibung","zugangscode", "foto_sperre","matr_nr","zugangscode_timestamp","udf_values","bpk","matr_aktiv","wahlname"),
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
	"public.tbl_prestudent"  => array("prestudent_id","aufmerksamdurch_kurzbz","person_id","studiengang_kz","berufstaetigkeit_code","ausbildungcode","zgv_code","zgvort","zgvdatum","zgvmas_code","zgvmaort","zgvmadatum","aufnahmeschluessel","facheinschlberuf","reihungstest_id","anmeldungreihungstest","reihungstestangetreten","rt_gesamtpunkte","rt_punkte1","rt_punkte2","bismelden","anmerkung","dual","insertamum","insertvon","updateamum","updatevon","ext_id","ausstellungsstaat","rt_punkte3", "zgvdoktor_code", "zgvdoktorort", "zgvdoktordatum","mentor","zgvnation","zgvmanation","zgvdoktornation","gsstudientyp_kurzbz","aufnahmegruppe_kurzbz","udf_values","priorisierung","foerderrelevant","standort_code","zgv_erfuellt","zgvmas_erfuellt","zgvdoktor_erfuellt"),
	"public.tbl_prestudentstatus"  => array("prestudent_id","status_kurzbz","studiensemester_kurzbz","ausbildungssemester","datum","orgform_kurzbz","insertamum","insertvon","updateamum","updatevon","ext_id","studienplan_id","bestaetigtam","bestaetigtvon","fgm","faktiv", "anmerkung","bewerbung_abgeschicktamum","rt_stufe","statusgrund_id"),
	"public.tbl_raumtyp"  => array("raumtyp_kurzbz","beschreibung","kosten"),
	"public.tbl_reihungstest"  => array("reihungstest_id","studiengang_kz","ort_kurzbz","anmerkung","datum","uhrzeit","updateamum","updatevon","insertamum","insertvon","ext_id","freigeschaltet","max_teilnehmer","oeffentlich","studiensemester_kurzbz","aufnahmegruppe_kurzbz","stufe","anmeldefrist"),
	"public.tbl_rt_ort" => array("rt_id","ort_kurzbz","uid"),
	"public.tbl_rt_person" => array("rt_person_id","person_id","rt_id","studienplan_id","anmeldedatum","teilgenommen","ort_kurzbz","punkte","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_rt_studienplan" => array("reihungstest_id","studienplan_id"),
	"public.tbl_status"  => array("status_kurzbz","beschreibung","anmerkung","ext_id","bezeichnung_mehrsprachig"),
	"public.tbl_status_grund" => array("statusgrund_id","status_kurzbz","aktiv","bezeichnung_mehrsprachig","beschreibung","statusgrund_kurzbz"),
	"public.tbl_semesterwochen"  => array("semester","studiengang_kz","wochen"),
	"public.tbl_service" => array("service_id", "bezeichnung","beschreibung","ext_id","oe_kurzbz","content_id","design_uid","betrieb_uid","operativ_uid","servicekategorie_kurzbz"),
	"public.tbl_servicekategorie" => array("servicekategorie_kurzbz", "bezeichnung","sort"),
	"public.tbl_sprache"  => array("sprache","locale","flagge","index","content","bezeichnung"),
	"public.tbl_standort"  => array("standort_id","adresse_id","kurzbz","bezeichnung","insertvon","insertamum","updatevon","updateamum","ext_id", "firma_id","code"),
	"public.tbl_statistik"  => array("statistik_kurzbz","bezeichnung","url","gruppe","sql","content_id","insertamum","insertvon","updateamum","updatevon","berechtigung_kurzbz","publish","preferences"),
	"public.tbl_student"  => array("student_uid","matrikelnr","prestudent_id","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studentlehrverband"  => array("student_uid","studiensemester_kurzbz","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studiengang"  => array("studiengang_kz","kurzbz","kurzbzlang","typ","bezeichnung","english","farbe","email","telefon","max_semester","max_verband","max_gruppe","erhalter_kz","bescheid","bescheidbgbl1","bescheidbgbl2","bescheidgz","bescheidvom","orgform_kurzbz","titelbescheidvom","aktiv","ext_id","zusatzinfo_html","moodle","sprache","testtool_sprachwahl","studienplaetze","oe_kurzbz","lgartcode","mischform","projektarbeit_note_anzeige", "onlinebewerbung","melderelevant","foerderrelevant","standort_code","melde_studiengang_kz"),
	"public.tbl_studiengangstyp" => array("typ","bezeichnung","beschreibung","bezeichnung_mehrsprachig"),
	"public.tbl_studienjahr"  => array("studienjahr_kurzbz","bezeichnung"),
	"public.tbl_studiensemester"  => array("studiensemester_kurzbz","bezeichnung","start","ende","studienjahr_kurzbz","ext_id","beschreibung","onlinebewerbung"),
	"public.tbl_tag"  => array("tag"),
	"public.tbl_variable"  => array("name","uid","wert"),
	"public.tbl_variablenname"  => array("name","defaultwert"),
	"public.tbl_vorlage"  => array("vorlage_kurzbz","bezeichnung","anmerkung","mimetype","attribute","archivierbar","signierbar","stud_selfservice","dokument_kurzbz","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_vorlagedokument"  => array("vorlagedokument_id","sort","vorlagestudiengang_id","dokument_kurzbz"),
	"public.tbl_vorlagestudiengang"  => array("vorlagestudiengang_id","vorlage_kurzbz","studiengang_kz","version","text","oe_kurzbz","style","berechtigung","anmerkung_vorlagestudiengang","aktiv","sprache","subject","orgform_kurzbz","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_zgvpruefungstatus" => array("status_kurzbz","bezeichnung"),
	"public.tbl_zgvpruefung" => array("zgvpruefung_id","prestudent_id","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_zgvpruefungstatus_status" => array("zgv_pruefung_status_id","zgvpruefung_id","status","datum"),
	"testtool.tbl_ablauf"  => array("ablauf_id","gebiet_id","studiengang_kz","reihung","gewicht","semester", "insertamum","insertvon","updateamum", "updatevon","ablauf_vorgaben_id","studienplan_id"),
	"testtool.tbl_ablauf_vorgaben"  => array("ablauf_vorgaben_id","studiengang_kz","sprache","sprachwahl","content_id","insertamum","insertvon","updateamum", "updatevon"),
	"testtool.tbl_antwort"  => array("antwort_id","pruefling_id","vorschlag_id"),
	"testtool.tbl_frage"  => array("frage_id","kategorie_kurzbz","gebiet_id","level","nummer","demo","insertamum","insertvon","updateamum","updatevon","aktiv"),
	"testtool.tbl_gebiet"  => array("gebiet_id","kurzbz","bezeichnung","beschreibung","zeit","multipleresponse","kategorien","maxfragen","zufallfrage","zufallvorschlag","levelgleichverteilung","maxpunkte","insertamum", "insertvon", "updateamum", "updatevon", "level_start","level_sprung_auf","level_sprung_ab","antwortenprozeile","bezeichnung_mehrsprachig", "offsetpunkte"),
	"testtool.tbl_kategorie"  => array("kategorie_kurzbz","gebiet_id"),
	"testtool.tbl_kriterien"  => array("gebiet_id","kategorie_kurzbz","punkte","typ"),
	"testtool.tbl_pruefling"  => array("pruefling_id","prestudent_id","studiengang_kz","idnachweis","registriert","semester", "gesperrt"),
	"testtool.tbl_vorschlag"  => array("vorschlag_id","frage_id","nummer","punkte","insertamum","insertvon","updateamum","updatevon","aktiv"),
	"testtool.tbl_pruefling_frage"  => array("prueflingfrage_id","pruefling_id","frage_id","nummer","begintime","endtime"),
	"testtool.tbl_frage_sprache"  => array("frage_id","sprache","text","bild","audio","insertamum","insertvon","updateamum","updatevon"),
	"testtool.tbl_vorschlag_sprache"  => array("vorschlag_id","sprache","text","bild","audio","insertamum","insertvon","updateamum","updatevon"),
	"system.tbl_app" => array("app"),
	"system.tbl_appdaten" => array("appdaten_id","uid","app","appversion","version","bezeichnung","daten","freigabe","insertamum","insertvon","updateamum","updatevon"),
	"system.tbl_cronjob"  => array("cronjob_id","server_kurzbz","titel","beschreibung","file","last_execute","aktiv","running","jahr","monat","tag","wochentag","stunde","minute","standalone","reihenfolge","updateamum", "updatevon","insertamum","insertvon","variablen"),
	"system.tbl_benutzerrolle"  => array("benutzerberechtigung_id","rolle_kurzbz","berechtigung_kurzbz","uid","funktion_kurzbz","oe_kurzbz","art","studiensemester_kurzbz","start","ende","negativ","updateamum", "updatevon","insertamum","insertvon","kostenstelle_id","anmerkung"),
	"system.tbl_berechtigung"  => array("berechtigung_kurzbz","beschreibung"),
	"system.tbl_extensions" => array("extension_id","name","version","description","license","url","core_version","dependencies","enabled", "server_kurzbz"),
	"system.tbl_fehler" => array("fehlercode","fehler_kurzbz","fehlercode_extern","fehlertext","fehlertyp_kurzbz","app"),
	"system.tbl_fehlertyp" => array("fehlertyp_kurzbz","bezeichnung_mehrsprachig"),
	"system.tbl_fehler_zustaendigkeiten" => array("fehlerzustaendigkeiten_id","fehlercode","person_id","oe_kurzbz","funktion_kurzbz", "insertamum", "insertvon"),
	"system.tbl_issue" => array("issue_id","fehlercode","fehlercode_extern","inhalt","inhalt_extern","person_id","oe_kurzbz","datum","verarbeitetvon","verarbeitetamum","status_kurzbz","behebung_parameter","insertvon","insertamum","updatevon","updateamum"),
	"system.tbl_issue_status" => array("status_kurzbz","bezeichnung_mehrsprachig"),
	"system.tbl_log" => array("log_id","person_id","zeitpunkt","app","oe_kurzbz","logtype_kurzbz","logdata","insertvon","taetigkeit_kurzbz"),
	"system.tbl_logtype" => array("logtype_kurzbz", "data_schema"),
	"system.tbl_filters" => array("filter_id","app","dataset_name","filter_kurzbz","person_id","description","sort","default_filter","filter","oe_kurzbz","statistik_kurzbz"),
	"system.tbl_jobsqueue" => array("jobid", "type", "creationtime", "status", "input", "output", "starttime", "endtime", "insertvon", "insertamum"),
	"system.tbl_jobstatuses" => array("status"),
	"system.tbl_jobtriggers" => array("type", "status", "following_type"),
	"system.tbl_jobtypes" => array("type", "description"),
	"system.tbl_phrase" => array("phrase_id","app","phrase","insertamum","insertvon","category"),
	"system.tbl_phrasentext" => array("phrasentext_id","phrase_id","sprache","orgeinheit_kurzbz","orgform_kurzbz","text","description","insertamum","insertvon"),
	"system.tbl_rolle"  => array("rolle_kurzbz","beschreibung"),
	"system.tbl_rolleberechtigung"  => array("berechtigung_kurzbz","rolle_kurzbz","art"),
	"system.tbl_verarbeitungstaetigkeit" => array("taetigkeit_kurzbz", "bezeichnung", "bezeichnung_mehrsprachig","aktiv"),
	"system.tbl_webservicelog"  => array("webservicelog_id","webservicetyp_kurzbz","request_id","beschreibung","request_data","execute_time","execute_user"),
	"system.tbl_webservicerecht" => array("webservicerecht_id","berechtigung_kurzbz","methode","attribut","insertamum","insertvon","updateamum","updatevon","klasse"),
	"system.tbl_webservicetyp"  => array("webservicetyp_kurzbz","beschreibung"),
	"system.tbl_server"  => array("server_kurzbz","beschreibung"),
	"system.tbl_udf"  => array("schema", "table", "jsons"),
	"system.tbl_person_lock" => array("lock_id", "person_id", "uid", "zeitpunkt", "app"),
	"wawi.tbl_betriebsmittelperson"  => array("betriebsmittelperson_id","betriebsmittel_id","person_id", "anmerkung", "kaution", "ausgegebenam", "retouram","insertamum", "insertvon","updateamum", "updatevon","ext_id","uid"),
	"wawi.tbl_betriebsmittel"  => array("betriebsmittel_id","betriebsmitteltyp","oe_kurzbz", "ort_kurzbz", "beschreibung", "nummer", "hersteller","seriennummer", "bestellung_id","bestelldetail_id", "afa","verwendung","anmerkung","reservieren","updateamum","updatevon","insertamum","insertvon","ext_id","inventarnummer","leasing_bis","inventuramum","inventurvon","anschaffungsdatum","anschaffungswert","hoehe","breite","tiefe","nummer2","verplanen"),
	"wawi.tbl_betriebsmittel_betriebsmittelstatus"  => array("betriebsmittelbetriebsmittelstatus_id","betriebsmittel_id","betriebsmittelstatus_kurzbz", "datum", "updateamum", "updatevon", "insertamum", "insertvon","anmerkung"),
	"wawi.tbl_betriebsmittelstatus"  => array("betriebsmittelstatus_kurzbz","beschreibung"),
	"wawi.tbl_betriebsmitteltyp"  => array("betriebsmitteltyp","beschreibung","anzahl","kaution","typ_code","mastershapename"),
	"wawi.tbl_zahlungstyp"  => array("zahlungstyp_kurzbz","bezeichnung"),
	"wawi.tbl_konto"  => array("konto_id","kontonr","beschreibung","kurzbz","aktiv","person_id","insertamum","insertvon","updateamum","updatevon","ext_id","person_id"),
	"wawi.tbl_konto_kostenstelle"  => array("konto_id","kostenstelle_id","insertamum","insertvon"),
	"wawi.tbl_kostenstelle"  => array("kostenstelle_id","oe_kurzbz","bezeichnung","kurzbz","aktiv","insertamum","insertvon","updateamum","updatevon","ext_id","kostenstelle_nr","deaktiviertvon","deaktiviertamum", "geschaeftsjahrvon", "geschaeftsjahrbis"),
	"wawi.tbl_bestellungtag"  => array("tag","bestellung_id","insertamum","insertvon"),
	"wawi.tbl_bestelldetailtag"  => array("tag","bestelldetail_id","insertamum","insertvon"),
	"wawi.tbl_projekt_bestellung"  => array("projekt_kurzbz","bestellung_id","anteil"),
	"wawi.tbl_bestellung"  => array("bestellung_id","besteller_uid","kostenstelle_id","konto_id","firma_id","lieferadresse","rechnungsadresse","freigegeben","bestell_nr","titel","bemerkung","liefertermin","updateamum","updatevon","insertamum","insertvon","ext_id","zahlungstyp_kurzbz"),
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
$errors = 0;
foreach ($tabellen AS $attribute)
{
	$sql_attr = '';
	foreach($attribute AS $attr)
		$sql_attr.='"'.$attr.'",';
	$sql_attr=substr($sql_attr, 0, -1);

	if (!@$db->db_query('SELECT '.$sql_attr.' FROM '.$tabs[$i].' LIMIT 1;'))
	{
		echo '<BR><strong>'.$tabs[$i].': '.$db->db_last_error().' </strong><BR>';
		$errors++;
	}
	/*else
		echo $tabs[$i].': OK - ';*/
	flush();
	$i++;
}
if ($errors == 0)
{
	echo '<strong>Keine Fehler aufgetreten</strong>';
}

echo '<H2>Gegenpruefung!</H2>';
$error=false;
$sql_query="SELECT schemaname,tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema' AND schemaname != 'sync' AND schemaname != 'addon' AND schemaname != 'reports' AND schemaname != 'extension';";
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
