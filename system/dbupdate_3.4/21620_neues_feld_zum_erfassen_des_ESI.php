<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Creates table public.tbl_kennzeichentyp if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 0 FROM public.tbl_kennzeichentyp WHERE 0 = 1'))
{
	$qry = 'CREATE TABLE public.tbl_kennzeichentyp (
				kennzeichentyp_kurzbz varchar(32) NOT NULL,
				bezeichnung varchar(256) NOT NULL,
				aktiv boolean NOT NULL DEFAULT TRUE
			);

			COMMENT ON TABLE public.tbl_kennzeichentyp IS \'Tabelle zur Verwaltung von Typen von externen Personenkennzeichen.\';
			COMMENT ON COLUMN public.tbl_kennzeichentyp.bezeichnung IS \'Voller Name des Kennzeichentyps.\';
			COMMENT ON COLUMN public.tbl_kennzeichentyp.aktiv IS \'Ob der Kennzeichentyp noch aktiv und verwendet wird.\';

			ALTER TABLE public.tbl_kennzeichentyp ADD CONSTRAINT pk_tbl_kennzeichentyp PRIMARY KEY (kennzeichentyp_kurzbz)';

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_kennzeichentyp: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_kennzeichentyp table created';

	$qry = 'GRANT SELECT ON TABLE public.tbl_kennzeichentyp TO web;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_kennzeichentyp: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on public.tbl_kennzeichentyp';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE public.tbl_kennzeichentyp TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_kennzeichentyp: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on public.tbl_kennzeichentyp';
}

// SEQUENCE tbl_kennzeichen_id_seq
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'tbl_kennzeichen_id_seq'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'CREATE SEQUENCE public.tbl_kennzeichen_id_seq
				START WITH 1
				INCREMENT BY 1
				NO MAXVALUE
				NO MINVALUE
				CACHE 1;';

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_kennzeichen_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created sequence: public.tbl_kennzeichen_id_seq';

		// GRANT SELECT, UPDATE ON SEQUENCE public.tbl_kennzeichen_id_seq TO vilesci;
		$qry = 'GRANT SELECT, UPDATE ON SEQUENCE public.tbl_kennzeichen_id_seq TO vilesci;';
		if (!$db->db_query($qry))
			echo '<strong>public.tbl_kennzeichen_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted privileges to <strong>vilesci</strong> on public.tbl_kennzeichen_id_seq';

		// GRANT SELECT, UPDATE ON SEQUENCE public.tbl_kennzeichen_id_seq TO fhcomplete;
		$qry = 'GRANT SELECT, UPDATE ON SEQUENCE public.tbl_kennzeichen_id_seq TO fhcomplete;';
		if (!$db->db_query($qry))
			echo '<strong>public.tbl_kennzeichen_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted privileges to <strong>fhcomplete</strong> on public.tbl_kennzeichen_id_seq';
	}
}

// Creates table public.tbl_kennzeichen if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 0 FROM public.tbl_kennzeichen WHERE 0 = 1'))
{
	$qry = 'CREATE TABLE public.tbl_kennzeichen (
				kennzeichen_id integer NOT NULL DEFAULT NEXTVAL(\'public.tbl_kennzeichen_id_seq\'),
				person_id integer NOT NULL,
				kennzeichentyp_kurzbz varchar(32) NOT NULL,
				inhalt text NOT NULL,
				aktiv boolean NOT NULL DEFAULT TRUE,
				insertamum timestamp DEFAULT NOW(),
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

			COMMENT ON TABLE public.tbl_kennzeichen IS \'Tabelle zum Speichern von externen Personenkennzeichen.\';
			COMMENT ON COLUMN public.tbl_kennzeichen.kennzeichentyp_kurzbz IS \'Typ des externen Personen Kennzeichens.\';
			COMMENT ON COLUMN public.tbl_kennzeichen.inhalt IS \'Das externe Kennzeichen.\';
			COMMENT ON COLUMN public.tbl_kennzeichen.aktiv IS \'Ob das Kennzeichen noch aktiv ist und verwendet wird.\';

			ALTER TABLE public.tbl_kennzeichen ADD CONSTRAINT pk_tbl_kennzeichen PRIMARY KEY (kennzeichen_id);

			ALTER TABLE public.tbl_kennzeichen ADD CONSTRAINT fk_kennzeichen_person FOREIGN KEY (person_id)
				REFERENCES public.tbl_person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE public.tbl_kennzeichen ADD CONSTRAINT fk_kennzeichen_kennzeichentyp_kurzbz FOREIGN KEY (kennzeichentyp_kurzbz)
				REFERENCES public.tbl_kennzeichentyp(kennzeichentyp_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;

			-- create unique constraint, no person can have the same kennzeichen twice
			ALTER TABLE public.tbl_kennzeichen ADD CONSTRAINT uk_kennzeichen_person_id_inhalt UNIQUE (person_id, kennzeichentyp_kurzbz, inhalt);
			-- create unique index - person can only have one active kennzeichen of each type
			CREATE UNIQUE INDEX kennzeichen_aktiv_constraint ON public.tbl_kennzeichen (person_id, kennzeichentyp_kurzbz) WHERE aktiv;';

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_kennzeichen: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_kennzeichen table created';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE public.tbl_kennzeichen TO web;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_kennzeichen: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on public.tbl_kennzeichen';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE public.tbl_kennzeichen TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_kennzeichen: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on public.tbl_kennzeichen';
}

// public.tbl_kennzeichentyp: add type esi
if ($result = $db->db_query("SELECT 1 FROM public.tbl_kennzeichentyp WHERE kennzeichentyp_kurzbz='esi'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO public.tbl_kennzeichentyp(kennzeichentyp_kurzbz, bezeichnung) VALUES('esi', 'European Student Identifier');";

		if(!$db->db_query($qry))
			echo '<strong>Kennzeichentyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neuer Kennzeichentyp esi in public.tbl_kennzeichentyp hinzugefügt';
	}
}

// system.tbl_jobtypes: add type esi
if ($result = $db->db_query("SELECT 1 FROM system.tbl_jobtypes WHERE type='generateESI'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO system.tbl_jobtypes(type, description) VALUES('generateESI', 'Generate and save European Student Identifier');";

		if(!$db->db_query($qry))
			echo '<strong>Jobtyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neuer Jobtyp generateESI in system.tbl_jobtypes hinzugefügt';
	}
}
