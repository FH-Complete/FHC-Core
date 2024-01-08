<?php
    
    if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_cis_profil_update LIMIT 1"))
    {
        $qry = "CREATE TABLE public.tbl_cis_profil_update (
                profil_update_id INTEGER NOT NULL,
                uid VARCHAR(32) NOT NULL,
                requested_change jsonb NOT NULL,
                change_timestamp TIMESTAMP NOT NULL,
                CONSTRAINT tbl_cis_profil_update_pk PRIMARY KEY(profil_update_id),
                CONSTRAINT tbl_cis_profil_update_fk FOREIGN KEY(uid) REFERENCES public.tbl_benutzer(uid)
            );

            CREATE SEQUENCE public.tbl_cis_profil_update_id_seq
			 INCREMENT BY 1
			 NO MAXVALUE
			 NO MINVALUE
			 CACHE 1;
		    ALTER TABLE public.tbl_cis_profil_update ALTER COLUMN profil_update_id SET DEFAULT nextval('public.tbl_cis_profil_update_id_seq');


            GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_cis_profil_update TO vilesci;
            GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_cis_profil_update TO web; 
            GRANT SELECT, UPDATE ON public.tbl_cis_profil_update_id_seq TO vilesci;
            GRANT SELECT, UPDATE ON public.tbl_cis_profil_update_id_seq TO web;";


        if(!$db->db_query($qry))
            echo '<strong>public.tbl_cis_profil_update: '.$db->db_last_error().'</strong><br>';
        else
            echo '<br>public.tbl_cis_profil_update: table created';
    }

    /* else{
        $qry = "DROP TABLE public.tbl_cis_profil_update;";
        if(!$db->db_query($qry))
            echo '<strong> was not able to delete public.tbl_cis_profil_update: '.$db->db_last_error().'</strong><br>';
        else
            echo '<br>public.tbl_cis_profil_update: table deleted';
    } */


    /* if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO public.tbl_cis_profil_update(uid, profil_data, profil_changes, change_timestamp) VALUES('ma0594','{\"test\":\"data\"}', NOW());";

		if(!$db->db_query($qry))
			echo '<strong>Prüfungstyp: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>test eintrag in public.tbl_cis_profil_update hinzugefügt';
	}   */


    //! was used to add an extra column to the table after the table was already created
    /* 
    
    if(!$result = @$db->db_query("SELECT profil_data FROM public.tbl_cis_profil_update LIMIT 1"))
    {
        $qry = "ALTER TABLE public.tbl_cis_profil_update ADD COLUMN profil_data jsonb;";

        if(!$db->db_query($qry))
            echo '<strong>public.tbl_cis_profil_update: '.$db->db_last_error().'</strong><br>';
        else
            echo '<br>public.tbl_cis_profil_update: Spalte profil_data hinzugefuegt';
    } */