<?php
    
    if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_cis_profil_update LIMIT 1"))
    {
        $qry = "CREATE TABLE public.tbl_cis_profil_update (
                profil_update_id INTEGER NOT NULL,
                uid VARCHAR(32) NOT NULL,
                name TEXT NOT NULL,
                topic VARCHAR(32) NOT NULL,
                requested_change jsonb NOT NULL,
                updateamum TIMESTAMP NULL,
                updatevon VARCHAR(32) NULL,
                insertamum TIMESTAMP NOT NULL,
                insertvon VARCHAR(32) NOT NULL,
                status VARCHAR(32) NOT NULL,
                status_timestamp TIMESTAMP NULL,
                status_message TEXT NULL,
                CONSTRAINT tbl_cis_profil_update_pk PRIMARY KEY(profil_update_id),
                CONSTRAINT tbl_cis_profil_update_fk FOREIGN KEY(uid) REFERENCES public.tbl_benutzer(uid)
            );

            CREATE SEQUENCE public.tbl_cis_profil_update_id_seq
			 INCREMENT BY 1
			 NO MAXVALUE
			 NO MINVALUE
			 CACHE 1;
             
		    ALTER TABLE public.tbl_cis_profil_update ALTER COLUMN profil_update_id SET DEFAULT nextval('public.tbl_cis_profil_update_id_seq');
            ALTER SEQUENCE public.tbl_cis_profil_update_id_seq OWNED BY public.tbl_cis_profil_update.profil_update_id;
            ALTER TABLE public.tbl_cis_profil_update ADD CONSTRAINT tbl_cis_profil_update_restricted_status CHECK (status IN ('pending','accepted','rejected'));
            --ALTER TABLE public.tbl_cis_profil_update ADD CONSTRAINT cis_profil_udpate_topic_uid_unique UNIQUE (uid,topic);

            GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_cis_profil_update TO vilesci;
            GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_cis_profil_update TO web; 
            GRANT SELECT, UPDATE ON public.tbl_cis_profil_update_id_seq TO vilesci;
            GRANT SELECT, UPDATE ON public.tbl_cis_profil_update_id_seq TO web;";


        if(!$db->db_query($qry))
            echo '<strong>public.tbl_cis_profil_update: '.$db->db_last_error().'</strong><br>';
        else
            echo '<br>public.tbl_cis_profil_update: table created';
    }

    
    //? would add a column if the column is missing in the table
  /*   if(!$result = @$db->db_query("SELECT topic FROM public.tbl_cis_profil_update LIMIT 1"))
    {
        $qry = "ALTER TABLE public.tbl_cis_profil_update ADD COLUMN topic varchar(32) NOT NULL;";

        if(!$db->db_query($qry))
            echo '<strong>public.tbl_cis_profil_update: '.$db->db_last_error().'</strong><br>';
        else
            echo '<br>public.tbl_cis_profil_update: Spalte topic hinzugefuegt';
    }  */