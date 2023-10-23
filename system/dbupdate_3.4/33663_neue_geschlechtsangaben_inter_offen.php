<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add geschlecht i {inter/inter}
if($result = $db->db_query("SELECT 1 FROM public.tbl_geschlecht WHERE geschlecht = 'i'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO public.tbl_geschlecht(geschlecht, bezeichnung_mehrsprachig, sort) VALUES ('i', '{\"inter\",\"inter\"}', 5)";

		if(!$db->db_query($qry))
			echo '<strong>Geschlecht: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neues Geschlecht i {inter/inter} in public.tbl_geschlecht hinzugefügt';
	}
}

// Add geschlecht {offen/open}
if($result = $db->db_query("SELECT 1 FROM public.tbl_geschlecht WHERE geschlecht = 'o'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO public.tbl_geschlecht(geschlecht, bezeichnung_mehrsprachig, sort) VALUES ('o', '{\"offen\",\"open\"}', 6)";

		if(!$db->db_query($qry))
			echo '<strong>Geschlecht: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neues Geschlecht o {offen/open} in public.tbl_geschlecht hinzugefügt';
	}
}

// Add geschlecht {keine Angabe/not specified}
if($result = $db->db_query("SELECT 1 FROM public.tbl_geschlecht WHERE geschlecht = 'k'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO public.tbl_geschlecht(geschlecht, bezeichnung_mehrsprachig, sort) VALUES ('k', '{\"keine Angabe\",\"not specified\"}', 7)";

		if(!$db->db_query($qry))
			echo '<strong>Geschlecht: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neues Geschlecht k {keine Angabe/not specified} in public.tbl_geschlecht hinzugefügt';
	}
}
