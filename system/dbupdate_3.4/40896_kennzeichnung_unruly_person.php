<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// add unruly column public.tbl_person
if($result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'public' 
                                           AND table_name = 'tbl_person' AND column_name = 'unruly'"))
{
	if($db->db_num_rows($result) === 0)
	{
		$qry = "ALTER TABLE tbl_person ADD COLUMN unruly BOOLEAN NOT NULL DEFAULT FALSE";

		if(!$db->db_query($qry))
			echo '<strong>Public Tabelle person: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>spalte unruly hinzugefuegt';
	}
}