<?php

// Add column pid to system.tbl_jobsqueue
if (!$result = @$db->db_query('SELECT "pid" FROM "system"."tbl_jobsqueue" LIMIT 1'))
{
	$qry = 'ALTER TABLE "system"."tbl_jobsqueue" ADD "pid" INT NULL DEFAULT 0;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobsqueue: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added column pid to table system.tbl_jobsqueue';
}

// Add column uid to system.tbl_jobsqueue
if (!$result = @$db->db_query('SELECT "uid" FROM "system"."tbl_jobsqueue" LIMIT 1'))
{
	$qry = 'ALTER TABLE "system"."tbl_jobsqueue" ADD "uid" VARCHAR(32) NULL DEFAULT NULL;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobsqueue: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added column uid to table system.tbl_jobsqueue';
}

// Add column progress to system.tbl_jobsqueue
if (!$result = @$db->db_query('SELECT "progress" FROM "system"."tbl_jobsqueue" LIMIT 1'))
{
	$qry = 'ALTER TABLE "system"."tbl_jobsqueue" ADD "progress" NUMERIC(2,1) NULL DEFAULT 0;';
	if (!$db->db_query($qry))
		echo '<strong>system.tbl_jobsqueue: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added column progress to table system.tbl_jobsqueue';
}

// Add foreign key fk_jobsqueue_benutzer_uid on system.tbl_jobsqueue.uid with public.tbl_benutzer.uid
if ($result = $db->db_query("SELECT conname FROM pg_constraint WHERE conname = 'fk_jobsqueue_benutzer_uid'"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'ALTER TABLE "system"."tbl_jobsqueue" ADD CONSTRAINT "fk_jobsqueue_benutzer_uid" FOREIGN KEY ("uid") REFERENCES "public"."tbl_benutzer" ("uid") ON DELETE RESTRICT ON UPDATE CASCADE;';
		if (!$db->db_query($qry))
			echo '<strong>system.tbl_jobsqueue: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Created foreign key fk_jobsqueue_benutzer_uid';
	}
}

// Add new webservice type in system.tbl_webservicetyp
if ($result = @$db->db_query("SELECT 1 FROM system.tbl_webservicetyp WHERE webservicetyp_kurzbz = 'lrt';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_webservicetyp(webservicetyp_kurzbz, beschreibung) VALUES('lrt', 'Long Run Task');";

		if (!$db->db_query($qry))
			echo '<strong>system.tbl_webservicetyp '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_webservicetyp: Added webservice type "lrt"<br>';
	}
}

