<?php
if (!defined('DB_NAME'))
	exit('No direct script access allowed');

if (!$result = @$db->db_query("SELECT prestudent_id FROM public.tbl_akte LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_akte ADD COLUMN prestudent_id int;";
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_akte: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>public.tbl_akte: Spalte prestudent_id hinzugefuegt';
	
	$qry = "CREATE INDEX idx_tbl_akte_prestudent_id ON public.tbl_akte USING btree (prestudent_id);";
	
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_akte: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>public.tbl_akte: Index prestudent_id hinzugefuegt';

	$qry = "ALTER TABLE public.tbl_akte ADD CONSTRAINT fk_prestudent_id_akte FOREIGN KEY (prestudent_id) REFERENCES public.tbl_prestudent(prestudent_id) ON DELETE RESTRICT ON UPDATE CASCADE;";
	
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_akte: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>public.tbl_akte: FK hinzugefuegt';
}
