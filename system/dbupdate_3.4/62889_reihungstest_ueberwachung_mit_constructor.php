<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');

if(!$result = @$db->db_query("SELECT externe_ueberwachung FROM public.tbl_reihungstest LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_reihungstest ADD COLUMN externe_ueberwachung boolean NOT NULL DEFAULT false;";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_reihungstest: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_reihungstest: Spalte externe_ueberwachung hinzugefuegt';
}

if(!$result = @$db->db_query("SELECT 1 FROM testtool.tbl_externe_ueberwachung LIMIT 1"))
{
	$qry = "CREATE TABLE testtool.tbl_externe_ueberwachung (
			externe_ueberwachung_id		INTEGER NOT NULL,
			prestudent_id				INTEGER NOT NULL,
			session_id					UUID NOT NULL,
			insertamum					TIMESTAMP DEFAULT NOW(),
			CONSTRAINT tbl_externe_ueberwachung_pk PRIMARY KEY(externe_ueberwachung_id)
		);
		CREATE SEQUENCE testtool.tbl_externe_ueberwachungg_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;
		ALTER TABLE testtool.tbl_externe_ueberwachung ALTER COLUMN externe_ueberwachung_id SET DEFAULT nextval('testtool.tbl_externe_ueberwachungg_id_seq');
		ALTER TABLE testtool.tbl_externe_ueberwachung ADD CONSTRAINT fk_prestudent_externe_ueberwachung FOREIGN KEY (prestudent_id) REFERENCES public.tbl_prestudent (prestudent_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE testtool.tbl_externe_ueberwachung ADD CONSTRAINT unique_externe_ueberwachung_session_id UNIQUE (session_id);
		GRANT SELECT, INSERT ON testtool.tbl_externe_ueberwachung TO vilesci;
		GRANT SELECT, INSERT ON testtool.tbl_externe_ueberwachung TO web;
		GRANT SELECT, UPDATE ON testtool.tbl_externe_ueberwachungg_id_seq TO vilesci;
		GRANT SELECT, UPDATE ON testtool.tbl_externe_ueberwachungg_id_seq TO web;";

	if(!$db->db_query($qry))
		echo '<strong>testtool.tbl_externe_ueberwachung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>testtool.tbl_externe_ueberwachung: table created';
}
