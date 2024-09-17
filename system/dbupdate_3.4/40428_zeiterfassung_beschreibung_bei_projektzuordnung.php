<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if (!$result = @$db->db_query("SELECT arbeitsbeschreibung FROM fue.tbl_projekt LIMIT 1"))
{
	$qry = "ALTER TABLE fue.tbl_projekt ADD COLUMN arbeitsbeschreibung BOOLEAN NOT NULL DEFAULT false;";

	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekt '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte arbeitsbeschreibung zu fue.tbl_projekt hinzugefügt';
}

if (!$result = @$db->db_query("SELECT arbeitsbeschreibung FROM fue.tbl_projektphase LIMIT 1"))
{
	$qry = "ALTER TABLE fue.tbl_projektphase ADD COLUMN arbeitsbeschreibung BOOLEAN NOT NULL DEFAULT false;";

	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projektphase '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte arbeitsbeschreibung zu fue.tbl_projektphase hinzugefügt';
}

