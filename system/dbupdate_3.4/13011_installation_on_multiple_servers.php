<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');

// stud_selfservice boolean fuer public.tbl_vorlage
if(!@$db->db_query("SELECT server_kurzbz FROM system.tbl_extensions LIMIT 1"))
{
	$qry = "ALTER TABLE system.tbl_extensions ADD COLUMN server_kurzbz varchar(64);
			ALTER TABLE system.tbl_extensions ADD CONSTRAINT fk_extensios_server_kurzbz FOREIGN KEY (server_kurzbz) REFERENCES system.tbl_server(server_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;";

	if(!$db->db_query($qry))
		echo '<strong>App: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte server_kurzbz in system.tbl_extensions hinzugefügt';
}


// UNIQUE INDEX uidx_extensions_name_version_server
if ($result = $db->db_query("SELECT 0 FROM pg_class WHERE relname = 'uidx_extensions_name_version_server'"))
{
	if ($db->db_num_rows($result) == 1)
	{
		$qry = 'DROP INDEX system.uidx_extensions_name_version';
		if (!$db->db_query($qry))
			echo '<strong>uidx_extensions_name_version_server '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Dropped unique uidx_extensions_name_version_server';
	}

	$qry = 'CREATE UNIQUE INDEX uidx_extensions_name_version_server ON system.tbl_extensions USING btree (name, version, server_kurzbz);';
	if (!$db->db_query($qry))
		echo '<strong>uidx_extensions_name_version_server '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Created unique uidx_extensions_name_version_server';
}

