<?php

if ($result = @$db->db_query("SELECT * FROM campus.tbl_content WHERE beschreibung='CIS4_ROOT'")) {

    if ($db->db_num_rows($result) == 0) {

		if (!$db->db_query("BEGIN;")) 
		{
			echo '<strong>wasnt able to start transaction: ' . $db->db_last_error() . '</strong><br>';
		}
		else
		{

			$qry =
				"
				INSERT INTO campus.tbl_content 
				(template_kurzbz, oe_kurzbz, insertamum, insertvon, updateamum, updatevon, aktiv, menu_open, beschreibung)
				VALUES  
				(
					'contentmittitel','etw',NOW(),null,null,null,TRUE,FALSE,'CIS4_ROOT'
				);

				INSERT INTO campus.tbl_contentsprache 
				(sprache, content_id, version, sichtbar, content, reviewvon, reviewamum, updateamum, updatevon, insertamum, insertvon, titel, gesperrt_uid)
				VALUES  
				(
					'German',
					-- queries the content_id for the CIS4_ROOT
					(SELECT content_id from campus.tbl_content WHERE beschreibung = 'CIS4_ROOT'),
					1,TRUE,'<content></content>',null,null,null,null,NOW(),null,'Cis40',null
				);

				INSERT INTO campus.tbl_contentchild
				(content_id, child_content_id, insertamum, insertvon, updateamum, updatevon, sort)
				VALUES
				(
					-- queries the content_id for the CIS4_ROOT
					(SELECT content_id from campus.tbl_content WHERE beschreibung = 'CIS4_ROOT'),
					10882, NOW(), null, null, null, 100
				);
				
				
				";




			if (!$db->db_query($qry)) 
			{
				echo '<strong>Menu content: ' . $db->db_last_error() . '</strong><br>';

				// Rollback
				if (!$db->db_query("ROLLBACK;")) 
				{
					echo '<strong>wasnt able to rollback: ' . $db->db_last_error() . '</strong><br>';
				} 
				else 
				{
					echo '<strong>ROLLED BACK</strong><br>';
				}
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
					echo '<strong>COMMITED</strong><br>';
				}

				echo '<br>Menu content created';
			}

		}
	}
}


