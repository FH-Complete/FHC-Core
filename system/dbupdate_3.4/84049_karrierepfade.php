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
