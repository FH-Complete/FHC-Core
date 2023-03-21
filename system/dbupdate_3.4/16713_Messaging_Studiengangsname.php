<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');

// Change Studiengang DE and Studiengang EN 
if ($result = @$db->db_query("SELECT definition FROM pg_views WHERE schemaname = 'public' AND viewname = 'vw_msg_vars';"))
{
	// Get the view description
	if ($row = $db->db_fetch_object($result))
	{
		// If the view is still declared in the old way
		if (strpos($row->definition, 's.bezeichnung AS "Studiengang DE"') !== false
			|| strpos($row->definition, 's.english AS "Studiengang EN"') !== false)
		{
			$qry = '
-- The view must be dropped because the data type of columns Studiengang DE and Studiengang EN has been changed from CHARACTER VARYING to TEXT
DROP VIEW public.vw_msg_vars;
-- Create the view
CREATE OR REPLACE VIEW public.vw_msg_vars AS (
	SELECT DISTINCT ON (p.person_id, pr.prestudent_id) p.person_id,
		pr.prestudent_id,
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
		COALESCE(so.studiengangbezeichnung::TEXT, s.bezeichnung::TEXT) AS "Studiengang DE",
		COALESCE(so.studiengangbezeichnung_englisch::TEXT, s.english::TEXT) AS "Studiengang EN",
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
		last_prestudent_status.orgform_bezeichnung_de AS "Orgform DE",
		last_prestudent_status.orgform_bezeichnung_en AS "Orgform EN",
		(
			SELECT COUNT(*) AS count
			  FROM (
				SELECT
					pss.prestudent_id,
		                	pss.person_id,
		        		pss.priorisierung,
		                	(
						SELECT tbl_prestudentstatus_1.status_kurzbz
						  FROM tbl_prestudentstatus tbl_prestudentstatus_1
		                		 WHERE tbl_prestudentstatus_1.prestudent_id = pss.prestudent_id
		                      	      ORDER BY tbl_prestudentstatus_1.datum DESC, tbl_prestudentstatus_1.insertamum DESC
						 LIMIT 1
					) AS laststatus
		        	  FROM tbl_prestudent pss
				  JOIN tbl_prestudentstatus USING (prestudent_id)
		        	 WHERE pss.person_id = (
						(
							SELECT tbl_prestudent.person_id
		                			  FROM tbl_prestudent
		                			 WHERE tbl_prestudent.prestudent_id = pr.prestudent_id
						)
					)
				   AND tbl_prestudentstatus.studiensemester_kurzbz::TEXT = (
						(
							(
								SELECT tbl_prestudentstatus_1.studiensemester_kurzbz
								  FROM tbl_prestudentstatus tbl_prestudentstatus_1
		                				 WHERE tbl_prestudentstatus_1.prestudent_id = pr.prestudent_id
								   AND tbl_prestudentstatus_1.status_kurzbz::TEXT = \'Interessent\'::TEXT
								 LIMIT 1
							)
						)::TEXT
					)
				   AND tbl_prestudentstatus.status_kurzbz::TEXT = \'Interessent\'::TEXT
				) prest
			 WHERE (prest.laststatus::TEXT <> ALL (ARRAY[\'Abbrecher\'::CHARACTER VARYING::TEXT, \'Abgewiesener\'::CHARACTER VARYING::TEXT, \'Absolvent\'::CHARACTER VARYING::TEXT]))
			   AND prest.priorisierung <= pr.priorisierung
		) AS "Relative Prio"
	  FROM tbl_person p
     LEFT JOIN (
		SELECT tbl_kontakt.person_id,
			tbl_kontakt.kontakt
		  FROM tbl_kontakt
		 WHERE tbl_kontakt.zustellung = TRUE
		   AND tbl_kontakt.kontakttyp::TEXT = \'email\'::TEXT
	      ORDER BY tbl_kontakt.kontakt_id DESC
	) ke USING (person_id)
     LEFT JOIN (
		SELECT tbl_kontakt.person_id,
			tbl_kontakt.kontakt
	          FROM tbl_kontakt
	         WHERE tbl_kontakt.zustellung = TRUE
		   AND (tbl_kontakt.kontakttyp::TEXT = ANY (ARRAY[\'telefon\'::CHARACTER VARYING::TEXT, \'mobil\'::CHARACTER VARYING::TEXT]))
	      ORDER BY tbl_kontakt.kontakt_id DESC
	) kt USING (person_id)
     LEFT JOIN (
		SELECT tbl_adresse.person_id,
			tbl_adresse.strasse,
			tbl_adresse.ort,
			tbl_adresse.plz,
			tbl_adresse.gemeinde,
			tbl_nation.langtext
		  FROM tbl_adresse
	     LEFT JOIN bis.tbl_nation ON (tbl_nation.nation_code::TEXT = tbl_adresse.nation::TEXT)
	         WHERE tbl_adresse.heimatadresse = true
	      ORDER BY tbl_adresse.adresse_id DESC
	) a USING (person_id)
     LEFT JOIN tbl_prestudent pr USING (person_id)
	  JOIN tbl_studiengang s USING (studiengang_kz)
	  JOIN tbl_studiengangstyp st USING (typ)
     LEFT JOIN lehre.tbl_studienordnung so USING(studiengang_kz)
     LEFT JOIN (
		SELECT DISTINCT ON (ps.prestudent_id) ps.prestudent_id,
			tbl_studienplan.orgform_kurzbz,
			tbl_orgform.bezeichnung_mehrsprachig[
				(
					(
						SELECT tbl_sprache.index
				                  FROM tbl_sprache
				                 WHERE tbl_sprache.content = TRUE
						   AND tbl_sprache.sprache::TEXT = \'German\'::TEXT
				                 LIMIT 1
					)
				)
			] AS orgform_bezeichnung_de,
			tbl_orgform.bezeichnung_mehrsprachig[
				(
					(
						SELECT tbl_sprache.index
						  FROM tbl_sprache
						 WHERE tbl_sprache.content = TRUE
						   AND tbl_sprache.sprache::TEXT = \'English\'::TEXT
				                 LIMIT 1
					)
				)
			] AS orgform_bezeichnung_en
           	  FROM tbl_prestudent ps
		  JOIN tbl_prestudentstatus ON (ps.prestudent_id = tbl_prestudentstatus.prestudent_id)
		  JOIN lehre.tbl_studienplan USING (studienplan_id)
	     LEFT JOIN bis.tbl_orgform ON (tbl_studienplan.orgform_kurzbz::TEXT = tbl_orgform.orgform_kurzbz::TEXT)
	      ORDER BY ps.prestudent_id DESC, tbl_prestudentstatus.datum DESC, tbl_prestudentstatus.insertamum DESC, tbl_prestudentstatus.ext_id DESC
	) last_prestudent_status ON (pr.prestudent_id = last_prestudent_status.prestudent_id)
     LEFT JOIN (
		SELECT DISTINCT ON (ps.prestudent_id) ps.prestudent_id,
			tbl_prestudentstatus.ausbildungssemester,
			tbl_prestudentstatus.studiensemester_kurzbz,
			tbl_studiensemester.bezeichnung AS studiensemester,
			tbl_studienordnung.studiengang_kz
		  FROM tbl_prestudent ps
		  JOIN tbl_prestudentstatus ON (ps.prestudent_id = tbl_prestudentstatus.prestudent_id)
		  JOIN tbl_studiensemester USING (studiensemester_kurzbz)
		  JOIN lehre.tbl_studienplan USING (studienplan_id)
		  JOIN lehre.tbl_studienordnung USING (studienordnung_id)
		 WHERE tbl_prestudentstatus.status_kurzbz::TEXT = \'Interessent\'::TEXT
	      ORDER BY ps.prestudent_id, tbl_prestudentstatus.datum, tbl_prestudentstatus.insertamum, tbl_prestudentstatus.ext_id
	) first_prestudent_status ON (pr.prestudent_id = first_prestudent_status.prestudent_id)
     LEFT JOIN (
		SELECT DISTINCT ON (tbl_benutzerfunktion.oe_kurzbz) tbl_person.vorname,
			tbl_person.nachname,
			tbl_benutzerfunktion.oe_kurzbz,
			tbl_mitarbeiter.telefonklappe,
			tbl_benutzer.alias
		  FROM tbl_benutzerfunktion
		  JOIN tbl_benutzer USING (uid)
		  JOIN tbl_person USING (person_id)
		  JOIN tbl_mitarbeiter ON (tbl_benutzer.uid::TEXT = tbl_mitarbeiter.mitarbeiter_uid::TEXT)
		 WHERE tbl_benutzerfunktion.funktion_kurzbz::TEXT = \'ass\'::TEXT
		   AND NOW() >= COALESCE(tbl_benutzerfunktion.datum_von::TIMESTAMP with time zone, NOW())
		   AND NOW() <= COALESCE(tbl_benutzerfunktion.datum_bis::TIMESTAMP with time zone, NOW())
	      ORDER BY tbl_benutzerfunktion.oe_kurzbz, tbl_benutzerfunktion.insertamum DESC NULLS LAST, tbl_benutzerfunktion.datum_von DESC NULLS LAST
	) ass ON (s.oe_kurzbz::TEXT = ass.oe_kurzbz::TEXT)
     LEFT JOIN (
		SELECT DISTINCT ON (tbl_bankverbindung.oe_kurzbz, tbl_bankverbindung.orgform_kurzbz) tbl_bankverbindung.oe_kurzbz,
			tbl_bankverbindung.orgform_kurzbz,
			tbl_bankverbindung.iban,
			tbl_bankverbindung.bic
		  FROM tbl_bankverbindung
		 WHERE tbl_bankverbindung.oe_kurzbz IS NOT NULL
	      ORDER BY tbl_bankverbindung.oe_kurzbz, tbl_bankverbindung.orgform_kurzbz, tbl_bankverbindung.insertamum DESC, tbl_bankverbindung.iban
	) bk ON (s.oe_kurzbz::TEXT = bk.oe_kurzbz::TEXT AND (last_prestudent_status.orgform_kurzbz::TEXT = bk.orgform_kurzbz::TEXT OR bk.orgform_kurzbz IS NULL))
	 WHERE p.aktiv = true
      ORDER BY p.person_id, pr.prestudent_id
);';

			if(!$db->db_query($qry))
				echo '<strong>public.vw_msg_vars: '.$db->db_last_error().'</strong><br>';
			else
				echo '<br>public.vw_msg_vars changed Studiengang DE and Studiengang EN';

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
	}
}

