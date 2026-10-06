<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add column account_creation to public.tbl_studiengang
if( !@$db->db_query('SELECT account_creation FROM public.tbl_studiengang LIMIT 1'))
{
	$qry = 'ALTER TABLE public.tbl_studiengang ADD COLUMN IF NOT EXISTS account_creation BOOLEAN NOT NULL DEFAULT TRUE;';
	
	$qry .= "COMMENT ON COLUMN public.tbl_studiengang.account_creation IS 'Flag for the student accounts creation on IDAM';";
	
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_studiengang.account_creation: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_studiengang.account_creation: Neue Spalte hinzugefügt';
}

