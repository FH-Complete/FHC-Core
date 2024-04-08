<?php
    
     if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_profil_update_status LIMIT 1"))
    {

        $qry = " CREATE TABLE public.tbl_profil_update_status (
            status_kurzbz VARCHAR(32) NOT NULL,
            beschreibung VARCHAR(256) NULL,
            CONSTRAINT tbl_profil_update_status_pk PRIMARY KEY(status_kurzbz)
        );

        INSERT INTO public.tbl_profil_update_status VALUES  ('Ausstehend','Profil Änderungen die noch nicht bearbeitet wurden'),
                                                            ('Pending','Profil updates that were not processed yet'),
                                                            ('Akzeptiert','Profil Änderungen die akzeptiert wurden'),
                                                            ('Accepted','Profil updates that were accepted'),
                                                            ('Abgelehnt','Profil Änderungen die abgelehn wurden'),
                                                            ('Rejected','Profil updates that were rejected');";

        if(!$db->db_query($qry))
        echo '<strong>public.tbl_profil_update_status: '.$db->db_last_error().'</strong><br>';
    else
        echo '<br>public.tbl_profil_update_status: table created';
    }

    if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_profil_update_topic LIMIT 1"))
    {

        $qry="CREATE TABLE public.tbl_profil_update_topic (
            topic_kurzbz VARCHAR(32) NOT NULL,
            beschreibung VARCHAR(256) NULL,
            CONSTRAINT tbl_profil_update_topic_pk PRIMARY KEY(topic_kurzbz)
        );

        INSERT INTO public.tbl_profil_update_topic VALUES   ('Vorname','Vorname aktualisieren'),
                                                            ('First name','Update first name'),
                                                            ('Nachname','Nachname aktualisieren'),
                                                            ('Last name','Update last name'),
                                                            ('Titel','Titel aktualisieren'),
                                                            ('Title','Update title'),
                                                            ('Postnomen','Postnomen aktualisieren'),
                                                            ('post-nominals','Update post-nominals'),
                                                            ('Private Kontakte','Kontakt aktualisieren'),
                                                            ('Private Contacts','Update contact'),
                                                            ('Kontakt löschen','Kontakt löschen'),
                                                            ('Delete contact','Delete contact'),
                                                            ('Kontakt hinzufügen','Kontakt hinzufügen'),
                                                            ('Add contact','Add contact'),
                                                            ('Private Adressen','Adresse aktualisieren'),
                                                            ('Private Addresses','Update address'),
                                                            ('Adresse löschen','Adresse löschen'),
                                                            ('Delete address','Delete address'),
                                                            ('Adresse hinzufügen','Adresse löschen'),
                                                            ('Add address','Add address');";

        if(!$db->db_query($qry))
        echo '<strong>public.tbl_profil_update_topic: '.$db->db_last_error().'</strong><br>';
    else
        echo '<br>public.tbl_profil_update_topic: table created';
    }

    if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_profil_update LIMIT 1"))
    {
        
            $qry = "CREATE TABLE public.tbl_profil_update (
                profil_update_id INTEGER NOT NULL,
                uid VARCHAR(32) NOT NULL,
                topic VARCHAR(32) NOT NULL,
                requested_change jsonb NOT NULL,
                updateamum TIMESTAMP NULL,
                updatevon VARCHAR(32) NULL,
                insertamum TIMESTAMP NOT NULL,
                insertvon VARCHAR(32) NOT NULL,
                status VARCHAR(32) NOT NULL,
                status_timestamp TIMESTAMP NULL,
                status_message TEXT NULL,
                attachment_id  bigint NULL,
                CONSTRAINT tbl_profil_update_pk PRIMARY KEY(profil_update_id),
                CONSTRAINT tbl_profil_update_fk FOREIGN KEY(uid) REFERENCES public.tbl_benutzer(uid),
                CONSTRAINT tbl_profil_update_status_fk FOREIGN KEY(status) REFERENCES public.tbl_profil_update_status(status_kurzbz),
                CONSTRAINT tbl_profil_update_topic_fk FOREIGN KEY(topic) REFERENCES public.tbl_profil_update_topic(topic_kurzbz),
                CONSTRAINT tbl_profil_update_attachment_fk FOREIGN KEY(attachment_id) REFERENCES campus.tbl_dms(dms_id)
            );            

            CREATE SEQUENCE public.tbl_profil_update_id_seq
			 INCREMENT BY 1
			 NO MAXVALUE
			 NO MINVALUE
			 CACHE 1;
             
		    ALTER TABLE public.tbl_profil_update ALTER COLUMN profil_update_id SET DEFAULT nextval('public.tbl_profil_update_id_seq');
            ALTER SEQUENCE public.tbl_profil_update_id_seq OWNED BY public.tbl_profil_update.profil_update_id;
            
            GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_profil_update TO vilesci;
            GRANT SELECT, INSERT, UPDATE, DELETE ON public.tbl_profil_update TO web; 
            GRANT SELECT, UPDATE ON public.tbl_profil_update_id_seq TO vilesci;
            GRANT SELECT, UPDATE ON public.tbl_profil_update_id_seq TO web;";


        if(!$db->db_query($qry))
            echo '<strong>public.tbl_profil_update: '.$db->db_last_error().'</strong><br>';
        else
            echo '<br>public.tbl_profil_update: table created';
    }
 
    // adds the dms_kategorie profil_aenderung to the database
    if($result = @$db->db_query("SELECT * FROM campus.tbl_dms_kategorie WHERE kategorie_kurzbz='profil_aenderung'"))
    {

        if($db->db_num_rows($result) == 0){
            
            $qry = "INSERT INTO campus.tbl_dms_kategorie VALUES ('profil_aenderung','Dokumente fuer Profil Aenderungen','Dokumente die Belegen ob man eine neue Adresse angemeldet hat oder seinen Namen geaendert hat','dokumente',NULL,NULL);";

            if(!$db->db_query($qry))
                echo '<strong>INSERT OF DMS_KATEGORIE profil_aenderung ERROR : '.$db->db_last_error().'</strong><br>';
            else
                echo '<br>INSERT OF DMS_KATEGORIE profil_aenderung was successful';
        }
        
    }
    
    