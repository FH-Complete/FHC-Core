<?php

    if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_cis_profil_update LIMIT 1"))
    {
        $qry = "CREATE TABLE public.tbl_cis_profil_update (
                uid VARCHAR(32) NOT NULL,
                profil_changes jsonb NOT NULL,
                change_timestamp TIMESTAMP NOT NULL,
                CONSTRAINT tbl_cis_profil_update_pk PRIMARY KEY(uid),
                CONSTRAINT tbl_cis_profil_update_fk FOREIGN KEY(uid) REFERENCES public.tbl_benutzer(uid)
            );

            GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_cis_profil_update TO vilesci;
            GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_cis_profil_update TO web;";

        if(!$db->db_query($qry))
            echo '<strong>public.tbl_cis_profil_update: '.$db->db_last_error().'</strong><br>';
        else
            echo '<br>public.tbl_cis_profil_update: table created';
    }


    if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO public.tbl_cis_profil_update(uid, profil_changes, change_timestamp) VALUES('ma0594', '{\"test\":\"data\"}', NOW());";

		if(!$db->db_query($qry))
			echo '<strong>Prüfungstyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>test eintrag in public.tbl_cis_profil_update hinzugefügt';
	}