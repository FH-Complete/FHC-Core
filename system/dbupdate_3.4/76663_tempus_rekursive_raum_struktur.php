<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if(!$result = @$db->db_query("SELECT parent_ort_kurzbz FROM public.tbl_ort LIMIT 1;"))
{
	$qry = 'ALTER TABLE public.tbl_ort ADD COLUMN parent_ort_kurzbz VARCHAR(16);';
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_ort: '.$db->db_last_error().'</strong><br>';
	else
		echo ' public.tbl_ort: parent_ort_kurzbz added successfully.<br>';
}

$result = $db->db_query("SELECT constraint_name FROM information_schema.table_constraints 
	WHERE table_name='tbl_ort' AND constraint_type='FOREIGN KEY' AND constraint_name='fk_parent_ort_kurzbz'");
if($db->db_num_rows($result)==0)
{
	$qry = "ALTER TABLE public.tbl_ort ADD CONSTRAINT fk_parent_ort_kurzbz FOREIGN KEY(parent_ort_kurzbz) REFERENCES public.tbl_ort(ort_kurzbz);";
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_ort: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added foreign key constraint fk_parent_ort_kurzbz to public.tbl_ort';
}