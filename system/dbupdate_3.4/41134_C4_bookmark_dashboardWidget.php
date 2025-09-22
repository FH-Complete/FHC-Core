<?php
if ($result = @$db->db_query("SELECT * FROM information_schema.tables WHERE table_name='tbl_bookmark' AND table_schema='dashboard'")) 
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = "
				CREATE TABLE IF NOT EXISTS dashboard.tbl_bookmark(
					bookmark_id BIGSERIAL PRIMARY KEY,
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

				GRANT SELECT, INSERT, UPDATE, DELETE ON dashboard.tbl_bookmark TO vilesci;
				GRANT SELECT, INSERT, UPDATE, DELETE ON dashboard.tbl_bookmark TO web; 
				GRANT SELECT, UPDATE ON dashboard.tbl_bookmark_bookmark_id_seq TO vilesci;
				GRANT SELECT, UPDATE ON dashboard.tbl_bookmark_bookmark_id_seq TO web;
				";

		if (!$db->db_query($qry))
		{
			echo '<strong>error occurred during tbl_bookmark creation: ' . $db->db_last_error() . '</strong><br>';
		}
		else
		{
			echo '<br>dashboard.tbl_bookmark successfully created';
		}
	}
}
