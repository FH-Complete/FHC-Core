<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add table tbl_rueckstellung_status
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_rueckstellung_status LIMIT 1;"))
{
	$qry = "
		CREATE TABLE public.tbl_rueckstellung_status
		(
			status_kurzbz character varying(32),
			bezeichnung_mehrsprachig character varying(256)[],
			sort integer,
			aktiv boolean default true
		);
		ALTER TABLE public.tbl_rueckstellung_status ADD CONSTRAINT pk_tbl_postpone_status_status_kurzbz PRIMARY KEY (status_kurzbz);
		INSERT INTO public.tbl_rueckstellung_status(status_kurzbz, bezeichnung_mehrsprachig, sort) VALUES('parked', '{Parken, Park}', 1);
		INSERT INTO public.tbl_rueckstellung_status(status_kurzbz, bezeichnung_mehrsprachig, sort) VALUES('onhold_bmi', '{Bundesministerium, Federal Ministry}', 2);
		INSERT INTO public.tbl_rueckstellung_status(status_kurzbz, bezeichnung_mehrsprachig, sort) VALUES('onhold_zgv', '{ZGV Prüfung, ZGV examination}', 3);
		INSERT INTO public.tbl_rueckstellung_status(status_kurzbz, bezeichnung_mehrsprachig, sort) VALUES('onhold_drittstaat', '{Drittstaat, Third country}', 4);
		INSERT INTO public.tbl_rueckstellung_status(status_kurzbz, bezeichnung_mehrsprachig, sort) VALUES('onhold_remone', '{Reminder 1, Reminder 1}', 5);
		INSERT INTO public.tbl_rueckstellung_status(status_kurzbz, bezeichnung_mehrsprachig, sort) VALUES('onhold_remtwo', '{Reminder 2, Reminder 2}', 6);
		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_rueckstellung_status TO vilesci;
		GRANT SELECT ON public.tbl_rueckstellung_status TO web;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_rueckstellung_status: '.$db->db_last_error().'</strong><br>';
	else
		echo ' public.tbl_rueckstellung_status: Tabelle hinzugefuegt<br>';
}

// Add table tbl_rueckstellung
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_rueckstellung LIMIT 1;"))
{
	$qry = "
		CREATE TABLE public.tbl_rueckstellung
		(
			rueckstellung_id integer NOT NULL,
			person_id integer NOT NULL,
			status_kurzbz character varying(32) NOT NULL,
			datum_bis timestamp NOT NULL,
			insertamum timestamp without time zone default now(),
			insertvon character varying(32)
		);

		CREATE SEQUENCE public.tbl_rueckstellung_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;

		ALTER TABLE public.tbl_rueckstellung ADD CONSTRAINT pk_tbl_rueckstellung PRIMARY KEY (rueckstellung_id);
		ALTER TABLE public.tbl_rueckstellung ALTER COLUMN rueckstellung_id SET DEFAULT nextval('tbl_rueckstellung_id_seq');
		ALTER TABLE public.tbl_rueckstellung ADD CONSTRAINT fk_rueckstellung_person_id FOREIGN KEY (person_id) REFERENCES public.tbl_person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;
		ALTER TABLE public.tbl_rueckstellung ADD CONSTRAINT fk_rueckstellung_status_kurzbz FOREIGN KEY (status_kurzbz) REFERENCES public.tbl_rueckstellung_status(status_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
		GRANT SELECT, UPDATE ON public.tbl_rueckstellung_id_seq TO vilesci;
		GRANT SELECT, UPDATE ON public.tbl_rueckstellung_id_seq TO web;
		GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_rueckstellung TO vilesci;
		GRANT SELECT, UPDATE, DELETE ON public.tbl_rueckstellung TO web;
	";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_rueckstellung: '.$db->db_last_error().'</strong><br>';
	else
		echo ' public.tbl_rueckstellung: Tabelle hinzugefuegt<br>';

	//Übernahme von "zurückgestellten" und "geparkten" Personen
	$qry = "
		INSERT INTO public.tbl_rueckstellung (person_id, status_kurzbz, datum_bis, insertvon)
		SELECT person_id,
				CASE WHEN
					(lower(l.logdata->>'name') = 'onhold')
				THEN 'onhold_remone'
					ELSE lower(l.logdata->>'name')
				END,
				zeitpunkt, insertvon
		FROM system.tbl_log l
		WHERE (l.logdata->>'name' = 'Onhold' OR l.logdata->>'name' = 'Parked') AND zeitpunkt >= NOW();";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_rueckstellung: '.$db->db_last_error().'</strong><br>';
	else
		echo ' public.tbl_rueckstellung: Bestehene Eintraege uebernommen<br>';
}
