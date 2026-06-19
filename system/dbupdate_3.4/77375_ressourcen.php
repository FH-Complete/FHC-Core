<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

$result = $db->db_query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'lehre' AND table_name = 'tbl_kalender' AND column_name = 'eindeutige_gruppen_id'");
if($db->db_num_rows($result) === 0)
{

	$qry = "ALTER TABLE lehre.tbl_kalender
			ADD COLUMN IF NOT EXISTS eindeutige_gruppen_id UUID";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_kalender: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_kalender column eindeutige_gruppen_id hinzugefuegt';
}

$result = $db->db_query("SELECT count(*) FROM public.tbl_variablenname WHERE name = 'ignore_resources_collisions'");
if($db->db_num_rows($result) === 0)
{
	$qry = "INSERT INTO public.tbl_variablenname
			(name, defaultwert)
			VALUES('ignore_resources_collisions', 'false')";
}

if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_betriebsmittel_kalender LIMIT 1"))
{
	$qry = '
		CREATE TABLE lehre.tbl_betriebsmittel_kalender (
			"betriebsmittel_kalender_id" INTEGER NOT NULL,
			"eindeutige_kalender_gruppen_id" UUID NOT NULL,
			"betriebsmittel_id" INTEGER NOT NULL,
			"anmerkung" TEXT,
			"quelle" VARCHAR(32),
			"insertamum" TIMESTAMP WITH TIME ZONE DEFAULT now(),
			"insertvon" VARCHAR(32),
			"updateamum" TIMESTAMP WITH TIME ZONE DEFAULT now(),
			"updatevon" VARCHAR(32),
			CONSTRAINT pk_betriebsmittel_kalender_id PRIMARY KEY("betriebsmittel_kalender_id"),
			CONSTRAINT fk_betriebsmittel_id FOREIGN KEY("betriebsmittel_id") REFERENCES wawi.tbl_betriebsmittel("betriebsmittel_id") ON DELETE CASCADE,
			CONSTRAINT eindeutige_kalender_gruppen_id_betriebsmittel_id UNIQUE (eindeutige_kalender_gruppen_id, betriebsmittel_id) 
			);';

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_betriebsmittel_kalender: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_betriebsmittel_kalender table created';

	$db->db_query('CREATE SEQUENCE IF NOT EXISTS lehre.seq_tbl_betriebsmittel_kalender_betriebsmittel_kalender_id
     INCREMENT BY 1
     NO MAXVALUE
     NO MINVALUE
     CACHE 1;');

	$db->db_query("ALTER TABLE lehre.tbl_betriebsmittel_kalender ALTER COLUMN betriebsmittel_kalender_id SET DEFAULT nextval('lehre.seq_tbl_betriebsmittel_kalender_betriebsmittel_kalender_id');");

	
	$qry = 'GRANT SELECT ON TABLE lehre.tbl_betriebsmittel_kalender TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_betriebsmittel_kalender: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_betriebsmittel_kalender';

	$qry = 'GRANT USAGE ON lehre.seq_tbl_betriebsmittel_kalender_betriebsmittel_kalender_id TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_betriebsmittel_kalender: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_betriebsmittel_kalender';


	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_betriebsmittel_kalender TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_betriebsmittel_kalender: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_betriebsmittel_kalender';

	$qry = 'GRANT USAGE, INSERT ON lehre.seq_tbl_betriebsmittel_kalender_betriebsmittel_kalender_id TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_betriebsmittel_kalender: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_betriebsmittel_kalender';

	$qry = 'GRANT USAGE, UPDATE ON lehre.seq_tbl_betriebsmittel_kalender_betriebsmittel_kalender_id TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_betriebsmittel_kalender: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_betriebsmittel_kalender';
}