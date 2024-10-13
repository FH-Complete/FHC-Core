<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// public.tbl_kontakttyp: add type email unverified
if($result = $db->db_query("SELECT 1 FROM public.tbl_kontakttyp WHERE kontakttyp='email_unverifiziert'"))
{
	if($db->db_num_rows($result)==0)
	{
	$qry = "INSERT INTO public.tbl_kontakttyp(kontakttyp, beschreibung, bezeichnung_mehrsprachig) VALUES('email_unverifiziert', 'Unverifizierte E-Mail', '{\"Unverifizierte E-Mail\", \"Unverified email\"}');";

		if(!$db->db_query($qry))
			echo '<strong>Kontakttyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neuen Kontakttyp E-Mail unverifiziert in public.tbl_kontakttyp hinzugefügt';
	}
}

// public.tbl_adressentyp: add type Meldeadresse
if($result = $db->db_query("SELECT 1 FROM public.tbl_adressentyp WHERE adressentyp_kurzbz='m'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO public.tbl_adressentyp(adressentyp_kurzbz, bezeichnung, bezeichnung_mehrsprachig, sort) VALUES('m', 'Meldeadresse', '{\"Meldeadresse\", \"Registered adress\"}', 6);";

		if(!$db->db_query($qry))
			echo '<strong>Adressentyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Neue Adressentyp Meldeadresse in public.tbl_adressentyp hinzugefügt';
	}
}
