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
			aktiv boolean default true
		);
		ALTER TABLE public.tbl_rueckstellung_status ADD CONSTRAINT pk_tbl_postpone_status_status_kurzbz PRIMARY KEY (status_kurzbz);
		INSERT INTO public.tbl_rueckstellung_status(status_kurzbz, bezeichnung_mehrsprachig) VALUES('parked', '{BewerberIn parken, Park applicant}');
		INSERT INTO public.tbl_rueckstellung_status(status_kurzbz, bezeichnung_mehrsprachig) VALUES('onhold', '{BewerberIn zurückstellen, Put applicant on hold}');
		INSERT INTO public.tbl_rueckstellung_status(status_kurzbz, bezeichnung_mehrsprachig) VALUES('onhold_zgv', '{BewerberIn zurückstellen (ZGV), Put applicant on hold (ZGV)}');
		INSERT INTO public.tbl_rueckstellung_status(status_kurzbz, bezeichnung_mehrsprachig) VALUES('onhold_drittstaat', '{BewerberIn zurückstellen (Drittstaaten), Put applicant on hold (third countries)}');
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
}
