<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if(!$result = @$db->db_query("SELECT prestudent_id FROM lehre.tbl_abschlusspruefung LIMIT 1"))
{
	$qry = "ALTER TABLE lehre.tbl_abschlusspruefung ADD COLUMN prestudent_id int;
			UPDATE lehre.tbl_abschlusspruefung
			SET prestudent_id = student.prestudent_id
			FROM tbl_student student
			WHERE lehre.tbl_abschlusspruefung.student_uid = student.student_uid;";
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlusspruefung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_abschlusspruefung: Spalte prestudent_id hinzugefuegt';
	
	$qry = "CREATE INDEX idx_abschlusspruefung_prestudent_id ON lehre.tbl_abschlusspruefung USING btree (prestudent_id);";
	
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlusspruefung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_abschlusspruefung: Index prestudent_id hinzugefuegt';
}

if ($result = @$db->db_query("SELECT student_uid FROM lehre.tbl_abschlusspruefung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_abschlusspruefung DROP COLUMN student_uid;";
	
	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlusspruefung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'lehre.tbl_abschlusspruefung: Spalte student_uid entfernt.<br>';
}

