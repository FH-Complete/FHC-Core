<?php

// Add column lange_beschreibung to system.tbl_rolle
if (!$result = @$db->db_query('SELECT "lange_beschreibung" FROM "system"."tbl_rolle" LIMIT 1'))
{
	$qry = 'ALTER TABLE "system"."tbl_rolle" ADD  "lange_beschreibung" TEXT NULL;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_rolle: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added column lange_beschreibung to table system.tbl_rolle';
}

// Creates table system.tbl_rolle_rolle if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 1 FROM system.tbl_rolle_rolle LIMIT 1'))
{
	$qry = 'CREATE TABLE system.tbl_rolle_rolle ( 
			main_rolle_kurzbz VARCHAR(32) NOT NULL,
			basic_rolle_kurzbz VARCHAR(32) NOT NULL,
			insertamum TIMESTAMP NOT NULL DEFAULT NOW() ,
			insertvon VARCHAR(32) NOT NULL
		);';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_rolle_rolle: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_rolle_rolle table created';

	$qry = 'COMMENT ON TABLE system.tbl_rolle_rolle IS \'Table to save the connections between permission roles.\';
		COMMENT ON COLUMN system.tbl_rolle_rolle.main_rolle_kurzbz IS \'Main role\';
		COMMENT ON COLUMN system.tbl_rolle_rolle.basic_rolle_kurzbz IS \'Basic role\';
		COMMENT ON COLUMN system.tbl_rolle_rolle.insertamum IS \'Insert date\';
		COMMENT ON COLUMN system.tbl_rolle_rolle.insertvon IS \'Insert by\';';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_rolle_rolle: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_rolle_rolle table commented';

	$qry = 'ALTER TABLE system.tbl_rolle_rolle ADD CONSTRAINT pk_tbl_rolle_rolle PRIMARY KEY (main_rolle_kurzbz, basic_rolle_kurzbz);';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_rolle_rolle: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_rolle_rolle primary key pk_tbl_rolle_rolle added';

	$qry = 'ALTER TABLE system.tbl_rolle_rolle
			ADD CONSTRAINT fk_tbl_rolle_rolle_main_tbl_rolle
			FOREIGN KEY (main_rolle_kurzbz)
			REFERENCES system.tbl_rolle (rolle_kurzbz)
			ON DELETE RESTRICT ON UPDATE CASCADE;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_rolle_rolle: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_rolle_rolle foreign key fk_tbl_rolle_rolle_main_tbl_rolle added';

	$qry = 'ALTER TABLE system.tbl_rolle_rolle
			ADD CONSTRAINT fk_tbl_rolle_rolle_basic_tbl_rolle
			FOREIGN KEY (basic_rolle_kurzbz)
			REFERENCES system.tbl_rolle (rolle_kurzbz)
			ON DELETE RESTRICT ON UPDATE CASCADE;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_rolle_rolle: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>system.tbl_rolle_rolle foreign key fk_tbl_rolle_rolle_basic_tbl_rolle added';

	$qry = 'GRANT SELECT ON TABLE system.tbl_rolle_rolle TO web;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_rolle_rolle: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on system.tbl_rolle_rolle';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE system.tbl_rolle_rolle TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_rolle_rolle: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on system.tbl_rolle_rolle';
}

// Get the view definition for the vw_berechtigung_nichtrekursiv view
if ($result = $db->db_query("SELECT view_definition FROM information_schema.views WHERE table_schema='system' AND table_name='vw_berechtigung_nichtrekursiv'"))
{
	// If exists (at this point should!)
	if ($row = $db->db_fetch_object($result))
	{
		// If the definition does _not_ contain the system.tbl_rolle_rolle table
		if (!mb_stristr($row->view_definition, 'system.tbl_rolle_rolle'))
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

				-- Rollen in Rollen
				UNION
				SELECT
					benutzerberechtigung_id, tbl_benutzerrolle.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_berechtigung.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_rolleberechtigung.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle
					JOIN system.tbl_rolle USING(rolle_kurzbz)
					JOIN system.tbl_rolle_rolle ON system.tbl_rolle_rolle.main_rolle_kurzbz = system.tbl_rolle.rolle_kurzbz
					JOIN system.tbl_rolleberechtigung ON system.tbl_rolleberechtigung.rolle_kurzbz = system.tbl_rolle_rolle.basic_rolle_kurzbz
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

