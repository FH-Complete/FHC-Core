<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_tbl_msg_message_person_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_msg_message_person_id ON public.tbl_msg_message USING btree (person_id)";

		if (! $db->db_query($qry))
			echo '<strong>idx_tbl_msg_message_person_id: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index idx_tbl_msg_message_person_id angelegt<br>';
	}
}

if ($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_tbl_msg_recipient_person_id'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_msg_recipient_person_id ON public.tbl_msg_recipient USING btree (person_id)";

		if (! $db->db_query($qry))
			echo '<strong>idx_tbl_msg_recipient_person_id: ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'Index idx_tbl_msg_recipient_person_id angelegt<br>';
	}
}
