<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// public.tbl_kontakttyp: add type email unverified
if($result = $db->db_query("SELECT 1 FROM public.tbl_kontakttyp WHERE kontakttyp='email_unverifiziert'"))
{
	if($db->db_num_rows($result)==0)
	{
	$qry = "INSERT INTO public.tbl_kontakttyp(kontakttyp, beschreibung, bezeichnung_mehrsprachig) VALUES('email_unverifiziert', 'Unverifizierte E-Mail', '{\"Unverifizierte E-Mail\", \"Unverified email\"}');";

		if(!$db->db_query($qry))
			echo '<strong>Kontakttyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neuen Kontakttyp E-Mail unverifiziert in public.tbl_kontakttyp hinzugefügt';
	}
}

// public.tbl_adressentyp: add type Meldeadresse
if($result = $db->db_query("SELECT 1 FROM public.tbl_adressentyp WHERE adressentyp_kurzbz='m'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO public.tbl_adressentyp(adressentyp_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES('m', 'Meldeadresse', '{\"Meldeadresse\", \"Registered adress\"}', 6);";

		if(!$db->db_query($qry))
			echo '<strong>Adressentyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue Adressentyp Meldeadresse in public.tbl_adressentyp hinzugefügt';
	}
}

if (!$result = @$db->db_query('SELECT 1 FROM public.tbl_kontakt_verifikation LIMIT 1'))
{
	$qry = "CREATE SEQUENCE public.tbl_kontakt_verifikation_kontakt_verifikation_id_seq
				INCREMENT BY 1
				NO MAXVALUE
				NO MINVALUE
				START WITH 1
				CACHE 1
				NO CYCLE;

			CREATE TABLE public.tbl_kontakt_verifikation
			(
				kontakt_verifikation_id integer DEFAULT nextval('public.tbl_kontakt_verifikation_kontakt_verifikation_id_seq'::regclass),
				kontakt_id integer UNIQUE NOT NULL,
				verifikation_code varchar(32) UNIQUE NOT NULL,
				erstelldatum timestamp without time zone,
				verifikation_datum timestamp without time zone,
				app varchar(32),
				CONSTRAINT pk_tbl_kontakt_verifikation_id PRIMARY KEY (kontakt_verifikation_id)
			);

			ALTER TABLE public.tbl_kontakt_verifikation ADD CONSTRAINT fk_tbl_kontakt_verifikation_kontakt_id FOREIGN KEY (kontakt_id)
			REFERENCES public.tbl_kontakt (kontakt_id)
			ON DELETE CASCADE ON UPDATE CASCADE;

			ALTER TABLE public.tbl_kontakt_verifikation ADD CONSTRAINT fk_tbl_kontakt_verifikation_app FOREIGN KEY (app)
			REFERENCES system.tbl_app (app)
			ON DELETE RESTRICT ON UPDATE CASCADE;

			COMMENT ON TABLE public.tbl_kontakt_verifikation IS 'Contact verification';
			COMMENT ON COLUMN public.tbl_kontakt_verifikation.kontakt_id IS 'Contact to verify';
			COMMENT ON COLUMN public.tbl_kontakt_verifikation.verifikation_code IS 'Code generated for verification';
			COMMENT ON COLUMN public.tbl_kontakt_verifikation.erstelldatum IS 'Time when verification code was generated';
			COMMENT ON COLUMN public.tbl_kontakt_verifikation.verifikation_datum IS 'Time when contact was verified';
			COMMENT ON COLUMN public.tbl_kontakt_verifikation.app IS 'App where contact was verified';

			GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_kontakt_verifikation TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_kontakt_verifikation TO vilesci;
			GRANT SELECT, UPDATE ON public.tbl_kontakt_verifikation_kontakt_verifikation_id_seq TO vilesci;
			GRANT SELECT, UPDATE ON public.tbl_kontakt_verifikation_kontakt_verifikation_id_seq TO web;
		";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_kontakt_verifikation: '.$db->db_last_error().'</strong><br>';
	else
		echo ' public.tbl_kontakt_verifikation: Tabelle hinzugefuegt<br>';
}
