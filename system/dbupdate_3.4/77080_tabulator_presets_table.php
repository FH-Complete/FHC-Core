<?php

if (!defined('DB_NAME'))
	exit('No direct script access allowed');

if (!$result = @$db->db_query("SELECT 1 FROM public.tbl_tabulator_presets LIMIT 1")) {
	$qry = "CREATE TABLE public.tbl_tabulator_presets (
		preset_id int NOT NULL,
		benutzer_uid varchar(33) NOT NULL,
		table_name varchar(255) NOT NULL,
		preset_name varchar(255) NOT NULL,
		preset_json varchar(10000) NOT NULL,
		CONSTRAINT tbl_tabulator_presets_preset_id_pk PRIMARY KEY(preset_id),
		CONSTRAINT tbl_tabulator_presets_benutzer_uid_fk FOREIGN KEY (benutzer_uid) REFERENCES public.tbl_benutzer(uid)
		);";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_tabulator_presets: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>public.tbl_tabulator_presets: table created';


	$db->db_query('CREATE SEQUENCE IF NOT EXISTS public.seq_tbl_tabulator_presets_preset_id
     INCREMENT BY 1
     NO MAXVALUE
     NO MINVALUE
     CACHE 1;');

	$db->db_query("ALTER TABLE public.tbl_tabulator_presets ALTER COLUMN preset_id SET DEFAULT nextval('public.seq_tbl_tabulator_presets_preset_id');");


	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE public.tbl_tabulator_presets TO web;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_tabulator_presets: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on public.tbl_tabulator_presets';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE public.tbl_tabulator_presets TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_tabulator_presets: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on public.tbl_tabulator_presets';

	$qry = 'GRANT USAGE ON public.seq_tbl_tabulator_presets_preset_id TO web;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_tabulator_presets: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on public.tbl_tabulator_presets';

	$qry = 'GRANT USAGE ON public.seq_tbl_tabulator_presets_preset_id TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>public.tbl_tabulator_presets: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on public.tbl_tabulator_presets';
}
