<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//Add column habilitation to public.tbl_mitarbeiter
if(!@$db->db_query("SELECT habilitation FROM public.tbl_mitarbeiter LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_mitarbeiter ADD COLUMN habilitation boolean NOT NULL DEFAULT false;
			COMMENT ON COLUMN public.tbl_mitarbeiter.habilitation IS 'Zeigt an, ob Mitarbeiter habilitiert ist (BIS relevant).';";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_mitarbeiter '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte habilitation zu Tabelle public.tbl_mitarbeiter hinzugefügt';
}
