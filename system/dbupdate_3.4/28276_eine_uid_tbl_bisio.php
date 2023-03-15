<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if(!$result = @$db->db_query("SELECT prestudent_id FROM bis.tbl_bisio LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_bisio ADD COLUMN prestudent_id int;
			UPDATE bis.tbl_bisio
			SET prestudent_id = student.prestudent_id
			FROM tbl_student student
			WHERE tbl_bisio.student_uid = student.student_uid;";
	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_bisio: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>bis.tbl_bisio: Spalte prestudent_id hinzugefuegt';
}



