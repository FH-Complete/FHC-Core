<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');

// Update Berechtigungen fuer web User erteilen fuer tbl_pruefling_pruefling_id_seq
if($result = @$db->db_query("SELECT has_sequence_privilege('web', 'testtool.tbl_pruefling_pruefling_id_seq', 'UPDATE')"))
{
	
	if($db->db_fetch_object($result)->has_sequence_privilege === "f")
	{
		$qry = "GRANT SELECT, UPDATE ON SEQUENCE testtool.tbl_pruefling_pruefling_id_seq to web;";

		if(!$db->db_query($qry))
			echo '<strong>testtool.tbl_pruefling Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Web User fuer testtool.tbl_pruefling berechtigt';
	}
}

// Update Berechtigungen fuer vilesci User erteilen fuer tbl_pruefling_pruefling_id_seq
if($result = @$db->db_query("SELECT has_sequence_privilege('vilesci', 'testtool.tbl_pruefling_pruefling_id_seq', 'UPDATE')"))
{
	if($db->db_fetch_object($result)->has_sequence_privilege === "f")
	{
		$qry = "GRANT SELECT, UPDATE ON SEQUENCE testtool.tbl_pruefling_pruefling_id_seq to vilesci;";
		
		if(!$db->db_query($qry))
			echo '<strong>testtool.tbl_pruefling Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>vilesci User fuer testtool.tbl_pruefling berechtigt';
	}
}


