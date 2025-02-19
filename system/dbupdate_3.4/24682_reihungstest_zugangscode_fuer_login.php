<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');


if(!$result = @$db->db_query("SELECT zugangs_ueberpruefung FROM public.tbl_reihungstest LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_reihungstest ADD COLUMN zugangs_ueberpruefung boolean NOT NULL DEFAULT false;
			ALTER TABLE public.tbl_reihungstest ADD COLUMN zugangscode smallint DEFAULT NULL;";
	
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_reihungstest: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_reihungstest: Spalte zugangs_ueberpruefung und zugangscode hinzugefuegt';
}