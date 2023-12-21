<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//Add column aktiv to public.tbl_raumtyp
if(!@$db->db_query("SELECT aktiv FROM public.tbl_raumtyp LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_raumtyp ADD COLUMN aktiv boolean NOT NULL DEFAULT true;
			COMMENT ON COLUMN public.tbl_raumtyp.aktiv IS 'Zeigt an, ob Raumtyp aktuell ist.';
			";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_raumtyp '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte aktiv zu Tabelle public.tbl_raumtyp hinzugefügt';
}