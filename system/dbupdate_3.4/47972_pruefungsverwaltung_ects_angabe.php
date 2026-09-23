<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if(!@$db->db_query("SELECT ects FROM campus.tbl_pruefungsanmeldung LIMIT 1"))
{
	$qry = 'ALTER TABLE campus.tbl_pruefungsanmeldung ADD COLUMN ects numeric(5,2);';

	if(!$db->db_query($qry))
		echo '<strong> campus.tbl_pruefungsanmeldung '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_pruefungsanmeldung: Neue Spalte ects hinzugefügt';
}

if(!@$db->db_query("SELECT anderer_raum FROM campus.tbl_pruefungstermin LIMIT 1"))
{
	$qry = 'ALTER TABLE campus.tbl_pruefungstermin ADD COLUMN anderer_raum text NULL;';

	if(!$db->db_query($qry))
		echo '<strong> campus.tbl_pruefungstermin '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_pruefungstermin: Neue Spalte anderer_raum hinzugefügt';
}
