<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// lehre.tbl_betreuerart: add type Senatsvorsitz
if($result = $db->db_query("SELECT 1 FROM lehre.tbl_betreuerart WHERE betreuerart_kurzbz='Senatsvorsitz'"))
{
	if($db->db_num_rows($result)==0)
	{
	$qry = "INSERT INTO lehre.tbl_betreuerart(betreuerart_kurzbz, beschreibung) VALUES('Senatsvorsitz', 'Senatsvorsitz kommissionelle Prüfung');";

		if(!$db->db_query($qry))
			echo '<strong>Betreuerart: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue Betreuerart Senatsvorsitz in lehre.tbl_betreuerart hinzugefügt';
	}
}

// lehre.tbl_betreuerart: add type Senatsprüfer
if($result = $db->db_query("SELECT 1 FROM lehre.tbl_betreuerart WHERE betreuerart_kurzbz='Senatspruefer'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO lehre.tbl_betreuerart(betreuerart_kurzbz, beschreibung) VALUES('Senatspruefer', 'SenatsprüferIn kommissionelle Prüfung');";

		if(!$db->db_query($qry))
			echo '<strong>Betreuerart: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue Betreuerart Senatspruefer in lehre.tbl_betreuerart hinzugefügt';
	}
}
