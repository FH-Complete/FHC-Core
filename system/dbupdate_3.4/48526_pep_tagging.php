<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Creates table public.tbl_notiz_typ if it doesn't exist and grants privileges
if (!$result = @$db->db_query('SELECT 0 FROM public.tbl_notiz_typ WHERE 0 = 1'))
{
	//TODO zuordnung typ definieren
	$qry = 'CREATE TABLE public.tbl_notiz_typ (
				typ_kurzbz varchar(32) NOT NULL,
				bezeichnung_mehrsprachig character varying(256)[] NOT NULL,
				beschreibung text,
				automatisiert boolean NOT NULL,
				aktiv boolean NOT NULL,
				zuordnung text, 
				tag boolean NOT NULL,
				style text,
				vorrueckung boolean NOT NULL,
				prioritaet smallint
			);

			ALTER TABLE public.tbl_notiz_typ ADD CONSTRAINT pk_tbl_tbl_notiz_typ PRIMARY KEY (typ_kurzbz)';

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_notiz_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_notiz_typ table created';

	$qry = 'GRANT SELECT ON TABLE public.tbl_notiz_typ TO web;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_notiz_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on public.tbl_notiz_typ';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE public.tbl_notiz_typ TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_notiz_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on public.tbl_notiz_typ';
}

if(!@$db->db_query("SELECT typ FROM public.tbl_notiz LIMIT 1"))
{
	$qry = 'ALTER TABLE public.tbl_notiz ADD COLUMN typ varchar(32);
			ALTER TABLE public.tbl_notiz ADD CONSTRAINT tbl_notiz_typ_fkey FOREIGN KEY (typ) REFERENCES public.tbl_notiz_typ (typ_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;';

	if(!$db->db_query($qry))
		echo '<strong> public.tbl_notiz '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_notiz: Neue Spalte typ hinzugefügt';
}
