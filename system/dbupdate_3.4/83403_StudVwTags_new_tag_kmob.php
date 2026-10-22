<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if($result = @$db->db_query("SELECT typ_kurzbz FROM public.tbl_notiz_typ where typ_kurzbz = 'kmob_auto' LIMIT 1"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
                INSERT INTO public.tbl_notiz_typ
                (
                    typ_kurzbz,
                    bezeichnung_mehrsprachig,
                    automatisiert,
                    aktiv,
                    tag,
                    style,
                    vorrueckung,
                    prioritaet,
                    taglib
                )
                VALUES
                (
                    'kmob_auto',
                    '{kMob, kMob}',
                    true,
                    true,
                    true,
                    'tag_teal',
                    false,
                    12,
                    'tags/CoreKmobTagLib'
                )
            ";

		if(!$db->db_query($qry))
			echo '<strong> public.tbl_notiz_typ '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>public.tbl_notiz_typ: neuer Eintrag kMob in Spalte typ_kurzbz ';
	}
}
