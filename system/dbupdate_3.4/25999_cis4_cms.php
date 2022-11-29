<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add index to system.tbl_log
if(!$result = @$db->db_query("SELECT xslt_xhtml_c4 FROM campus.tbl_template LIMIT 1"))
{
	$qry = "ALTER TABLE campus.tbl_template ADD COLUMN xslt_xhtml_c4 xml;";

	if(!$db->db_query($qry))
		echo '<strong>campus.tbl_template: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>campus.tbl_template: Spalte xslt_xhtml_c4 hinzugefuegt';

	// TODO(chris): add default values
}

$tabellen['campus.tbl_template'][] = 'xslt_xhtml_c4';
