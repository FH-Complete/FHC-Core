<?php

if (!$result = @$db->db_query("SELECT to_regclass('dashboard.tbl_bookmark')")) 
{
	if (!$db->db_query("BEGIN;")) 
	{
		echo '<strong>wasnt able to start transaction for 41134_C4_bookmark_dashboardWidget: ' . $db->db_last_error() . '</strong><br>';
	}
    $qry = "
            CREATE TABLE IF NOT EXISTS dashboard.tbl_bookmark(
                bookmark_id BIGINT PRIMARY KEY,
                uid VARCHAR(255) NOT NULL,
                url VARCHAR(511) NOT NULL,
                title VARCHAR(255) NOT NULL,
                tag VARCHAR(255) NULL,
                insertamum TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                insertvon VARCHAR(255) NULL REFERENCES public.tbl_benutzer(uid),
                updateamum TIMESTAMP NULL,
                updatevon VARCHAR(255) NULL REFERENCES public.tbl_benutzer(uid)
            );

            ALTER TABLE dashboard.tbl_bookmark ADD CONSTRAINT tbl_bookmark_fk FOREIGN KEY(uid) REFERENCES public.tbl_benutzer(uid);

            CREATE SEQUENCE IF NOT EXISTS dashboard.tbl_bookmark_sequence 
            AS BIGINT
            INCREMENT BY 1
            NO MINVALUE
            NO MAXVALUE
            START WITH 1
            CACHE 1
            OWNED BY dashboard.tbl_bookmark.bookmark_id;

            ALTER TABLE dashboard.tbl_bookmark ALTER COLUMN bookmark_id SET DEFAULT nextval('dashboard.tbl_bookmark_sequence ');

            GRANT SELECT, INSERT, UPDATE, DELETE ON dashboard.tbl_bookmark TO vilesci;
            GRANT SELECT, INSERT, UPDATE, DELETE ON dashboard.tbl_bookmark TO web; 
            GRANT SELECT, UPDATE ON dashboard.tbl_bookmark_sequence TO vilesci;
            GRANT SELECT, UPDATE ON dashboard.tbl_bookmark_sequence TO web;
			";

    if (!$db->db_query($qry))
	{
		// Rollback
		if (!$db->db_query("ROLLBACK;")) 
		{
			echo '<strong>wasnt able to rollback: ' . $db->db_last_error() . '</strong><br>';
		} 
		else 
		{
			echo '<strong>ROLLED BACK 41134_C4_bookmark_dashboardWidget</strong><br>';
		}
		echo '<strong>error occurred during tbl_bookmark creation: ' . $db->db_last_error() . '</strong><br>';
	}
    else
	{
		// Commit
		if (!$db->db_query("COMMIT;")) 
		{
			echo '<strong>wasnt able to commit: ' . $db->db_last_error() . '</strong><br>';
		} 
		else 
		{
			echo '<strong>COMMITED 41134_C4_bookmark_dashboardWidget</strong><br>';
		}
		echo '<br>dashboard.tbl_bookmark and dashboard.tbl_bookmark_sequence was created';
	}
}
