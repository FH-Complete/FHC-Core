<?php

if (!$result = @$db->db_query("SELECT LC_Time FROM public.tbl_sprache WHERE LIMIT 1")) {

    $qry = "
	ALTER TABLE public.tbl_sprache ADD LC_Time VARCHAR(255) ;
	UPDATE public.tbl_sprache SET LC_Time = 'en-GB' where locale ='en-US'; 
	UPDATE public.tbl_sprache SET LC_Time = 'de-AT' where locale ='de-AT'; 
	";

    if (!$db->db_query($qry))
        echo '<strong>public.tbl_sprache: ' . $db->db_last_error() . '</strong><br>';
    else
        echo '<br>public.tbl_sprache: column LC_Time was successfully added';
}

