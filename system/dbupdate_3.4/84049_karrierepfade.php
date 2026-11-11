<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Karrierepfade: marketing specialisations of a Studiengang, chosen by applicants in the Bewerbungstool

// Add table public.tbl_karrierepfad_typ
if (!$result = @$db->db_query("SELECT 1 FROM public.tbl_karrierepfad_typ LIMIT 1"))
{
	$qry = "
		CREATE TABLE public.tbl_karrierepfad_typ
		(
			karrierepfad_typ_kurzbz character varying(32) NOT NULL,
			bezeichnung_mehrsprachig character varying(256)[] NOT NULL,
			CONSTRAINT pk_tbl_karrierepfad_typ PRIMARY KEY (karrierepfad_typ_kurzbz)
		);
		COMMENT ON TABLE public.tbl_karrierepfad_typ IS 'Key Table of Karrierepfad types';

		INSERT INTO public.tbl_karrierepfad_typ(karrierepfad_typ_kurzbz, bezeichnung_mehrsprachig) VALUES('marketing', '{Marketing, Marketing}');

		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_karrierepfad_typ TO vilesci;
		GRANT SELECT ON public.tbl_karrierepfad_typ TO web;
	";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_karrierepfad_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo 'public.tbl_karrierepfad_typ: Tabelle hinzugefuegt<br>';
}

// Add table public.tbl_karrierepfad
if (!$result = @$db->db_query("SELECT 1 FROM public.tbl_karrierepfad LIMIT 1"))
{
	$qry = "
		CREATE SEQUENCE public.tbl_karrierepfad_karrierepfad_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;

		CREATE TABLE public.tbl_karrierepfad
		(
			karrierepfad_id integer NOT NULL DEFAULT nextval('public.tbl_karrierepfad_karrierepfad_id_seq'),
			karrierepfad_kurzbz character varying(32) NOT NULL,
			karrierepfad_typ_kurzbz character varying(32) NOT NULL,
			studiengang_kz integer NOT NULL,
			bezeichnung_mehrsprachig character varying(256)[] NOT NULL,
			sort smallint,
			aktiv boolean NOT NULL DEFAULT true,
			insertamum timestamp without time zone DEFAULT now(),
			insertvon character varying(32),
			updateamum timestamp without time zone,
			updatevon character varying(32),
			CONSTRAINT pk_tbl_karrierepfad PRIMARY KEY (karrierepfad_id),
			CONSTRAINT uk_tbl_karrierepfad_karrierepfad_kurzbz UNIQUE (karrierepfad_kurzbz)
		);
		COMMENT ON TABLE public.tbl_karrierepfad IS 'Marketing specialisations of a Studiengang. Not official, no Zeugnis, no BIS';
		COMMENT ON COLUMN public.tbl_karrierepfad.karrierepfad_kurzbz IS 'URL param value for Bewerbungstool';

		ALTER TABLE public.tbl_karrierepfad ADD CONSTRAINT fk_tbl_karrierepfad_karrierepfad_typ_kurzbz FOREIGN KEY (karrierepfad_typ_kurzbz)
			REFERENCES public.tbl_karrierepfad_typ(karrierepfad_typ_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE public.tbl_karrierepfad ADD CONSTRAINT fk_tbl_karrierepfad_studiengang_kz FOREIGN KEY (studiengang_kz)
			REFERENCES public.tbl_studiengang(studiengang_kz) ON UPDATE CASCADE ON DELETE RESTRICT;

		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_karrierepfad TO vilesci;
		GRANT SELECT ON public.tbl_karrierepfad TO web;
		GRANT SELECT, UPDATE ON public.tbl_karrierepfad_karrierepfad_id_seq TO vilesci;
	";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_karrierepfad: '.$db->db_last_error().'</strong><br>';
	else
		echo 'public.tbl_karrierepfad: Tabelle hinzugefuegt<br>';
}

// Add table public.tbl_karrierepfad_studienplan
if (!$result = @$db->db_query("SELECT 1 FROM public.tbl_karrierepfad_studienplan LIMIT 1"))
{
	$qry = "
		CREATE TABLE public.tbl_karrierepfad_studienplan
		(
			karrierepfad_id integer NOT NULL,
			studienplan_id integer NOT NULL,
			CONSTRAINT pk_tbl_karrierepfad_studienplan PRIMARY KEY (karrierepfad_id, studienplan_id)
		);
		COMMENT ON TABLE public.tbl_karrierepfad_studienplan IS 'Studienpläne in which a Karrierepfad is selectable';

		ALTER TABLE public.tbl_karrierepfad_studienplan ADD CONSTRAINT fk_tbl_karrierepfad_studienplan_karrierepfad_id FOREIGN KEY (karrierepfad_id)
			REFERENCES public.tbl_karrierepfad(karrierepfad_id) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE public.tbl_karrierepfad_studienplan ADD CONSTRAINT fk_tbl_karrierepfad_studienplan_studienplan_id FOREIGN KEY (studienplan_id)
			REFERENCES lehre.tbl_studienplan(studienplan_id) ON UPDATE CASCADE ON DELETE RESTRICT;

		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_karrierepfad_studienplan TO vilesci;
		GRANT SELECT ON public.tbl_karrierepfad_studienplan TO web;
	";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_karrierepfad_studienplan: '.$db->db_last_error().'</strong><br>';
	else
		echo 'public.tbl_karrierepfad_studienplan: Tabelle hinzugefuegt<br>';
}

// Add column karrierepfad_id to public.tbl_prestudent
if ($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='tbl_prestudent' AND column_name='karrierepfad_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
			ALTER TABLE public.tbl_prestudent ADD COLUMN karrierepfad_id integer;
			COMMENT ON COLUMN public.tbl_prestudent.karrierepfad_id IS 'Karrierepfad chosen by the applicant';

			ALTER TABLE public.tbl_prestudent ADD CONSTRAINT fk_tbl_prestudent_karrierepfad_id FOREIGN KEY (karrierepfad_id)
				REFERENCES public.tbl_karrierepfad(karrierepfad_id) ON UPDATE CASCADE ON DELETE RESTRICT;
		";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_prestudent.karrierepfad_id: '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_prestudent: Spalte karrierepfad_id hinzugefuegt<br>';
	}
}

// Add "Karrierepfad DE" and "Karrierepfad EN" to public.vw_msg_vars: definition of dbupdate_3.3.php plus two columns at the end
if (!$result = @$db->db_query('SELECT "Karrierepfad DE", "Karrierepfad EN" FROM public.vw_msg_vars WHERE person_id=-1 LIMIT 1'))
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
		   last_prestudent_status.orgform_bezeichnung_en AS "Orgform EN",
		   kp.bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE content=TRUE AND sprache=\'German\' LIMIT 1)] AS "Karrierepfad DE",
		   kp.bezeichnung_mehrsprachig[(SELECT index FROM public.tbl_sprache WHERE content=TRUE AND sprache=\'English\' LIMIT 1)] AS "Karrierepfad EN"
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
		LEFT JOIN public.tbl_karrierepfad kp ON kp.karrierepfad_id = pr.karrierepfad_id
		WHERE p.aktiv = TRUE
		ORDER BY p.person_id ASC, pr.prestudent_id ASC
	);';

	if (!$db->db_query($qry))
		echo '<strong>public.vw_msg_vars: '.$db->db_last_error().'</strong><br>';
	else
		echo 'public.vw_msg_vars: Spalten "Karrierepfad DE" und "Karrierepfad EN" hinzugefuegt<br>';
}
