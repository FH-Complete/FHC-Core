<?php

if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='lehre/unterrichtszeiten_gk' LIMIT 1"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "
		INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES
		('lehre/unterrichtszeiten_gk','Unterrichtszeiten Gültigkeit')
		";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: lehre/unterrichtszeiten_gk permissions inserted<br>';
	}
}

if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='lehre/unterrichtszeiten_typ' LIMIT 1"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "
		INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES
		('lehre/unterrichtszeiten_typ','Unterrichtszeiten Typ')
		";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_berechtigung: lehre/unterrichtszeiten_typ permissions inserted<br>';
	}
}

if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_unterrichtszeiten LIMIT 1"))
{
	$qry = '
		CREATE TABLE lehre.tbl_unterrichtszeiten (
			"unterrichtszeit_id" INTEGER NOT NULL,
			"unterrichtszeit_gruppe_identifikator" VARCHAR(32) NOT NULL,
			"wochentag" INTEGER NOT NULL,
			"uhrzeit_von" TIME NOT NULL,
			"uhrzeit_bis" TIME NOT NULL,
			"unterrichtszeitentyp_kurzbz" VARCHAR(32) NOT NULL,
			"unterrichtszeitengueltigkeit_id" INTEGER NOT NULL,
			"insertamum" TIMESTAMP WITH TIME ZONE DEFAULT now(),
			"insertvon" VARCHAR(32),
			"updateamum" TIMESTAMP WITH TIME ZONE DEFAULT now(),
			"updatevon" VARCHAR(32),
			CONSTRAINT pk_unterrichtszeit_id PRIMARY KEY("unterrichtszeit_id")
			);';

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_unterrichtszeiten table created';

	$db->db_query('CREATE SEQUENCE IF NOT EXISTS lehre.seq_tbl_unterrichtszeiten_unterrichtszeit_id
     INCREMENT BY 1
     NO MAXVALUE
     NO MINVALUE
     CACHE 1;');

	$db->db_query("ALTER TABLE lehre.tbl_unterrichtszeiten ALTER COLUMN unterrichtszeit_id SET DEFAULT nextval('lehre.seq_tbl_unterrichtszeiten_unterrichtszeit_id');");

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_unterrichtszeiten TO admin;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>admin</strong> on lehre.tbl_unterrichtszeiten';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_unterrichtszeiten TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_unterrichtszeiten';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_unterrichtszeiten TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_unterrichtszeiten';

	$qry = 'GRANT USAGE, INSERT ON lehre.seq_tbl_unterrichtszeiten_unterrichtszeit_id TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_unterrichtszeiten';

	$qry = 'GRANT USAGE, UPDATE ON lehre.seq_tbl_unterrichtszeiten_unterrichtszeit_id TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_unterrichtszeiten';

	$qry = 'GRANT USAGE, INSERT ON lehre.seq_tbl_unterrichtszeiten_unterrichtszeit_id TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_unterrichtszeiten';

	$qry = 'GRANT USAGE, UPDATE ON lehre.seq_tbl_unterrichtszeiten_unterrichtszeit_id TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_unterrichtszeiten';


	$qry = 'GRANT USAGE, INSERT ON lehre.seq_tbl_unterrichtszeiten_unterrichtszeit_id TO admin;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>admin</strong> on lehre.tbl_unterrichtszeiten';

	$qry = 'GRANT USAGE, UPDATE ON lehre.seq_tbl_unterrichtszeiten_unterrichtszeit_id TO admin;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>admin</strong> on lehre.tbl_unterrichtszeiten';
}

if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_unterrichtszeiten_typ LIMIT 1"))
{
	$qry = '
		CREATE TABLE lehre.tbl_unterrichtszeiten_typ (
			"unterrichtszeitentyp_kurzbz" VARCHAR(32) NOT NULL,
			"bezeichnung_mehrsprachig" TEXT[] NOT NULL,
			"aktiv" BOOLEAN DEFAULT true,
			"hintergrundfarbe" VARCHAR(7),
			"insertamum" TIMESTAMP WITH TIME ZONE DEFAULT now(),
			"insertvon" VARCHAR(32),
			"updateamum" TIMESTAMP WITH TIME ZONE DEFAULT now(),
			"updatevon" VARCHAR(32),
			CONSTRAINT pk_unterrichtszeitentyp_kurzbz PRIMARY KEY("unterrichtszeitentyp_kurzbz")
		);';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_unterrichtszeiten_typ table created';

	$db->db_query('CREATE SEQUENCE IF NOT EXISTS lehre.seq_tbl_tbl_unterrichtszeiten_typ_unterrichtszeitentyp_kurzbz
     INCREMENT BY 1
     NO MAXVALUE
     NO MINVALUE
     CACHE 1;');

	$db->db_query("ALTER TABLE lehre.tbl_unterrichtszeiten_typ ALTER COLUMN unterrichtszeitentyp_kurzbz SET DEFAULT nextval('lehre.seq_tbl_tbl_unterrichtszeiten_typ_unterrichtszeitentyp_kurzbz');");

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_unterrichtszeiten_typ TO admin;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>admin</strong> on lehre.tbl_unterrichtszeiten_typ';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_unterrichtszeiten_typ TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_unterrichtszeiten_typ';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_unterrichtszeiten_typ TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_unterrichtszeiten_typ';

	$qry = 'GRANT USAGE, INSERT ON lehre.seq_tbl_tbl_unterrichtszeiten_typ_unterrichtszeitentyp_kurzbz TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_unterrichtszeiten_typ';

	$qry = 'GRANT USAGE, UPDATE ON lehre.seq_tbl_tbl_unterrichtszeiten_typ_unterrichtszeitentyp_kurzbz TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_unterrichtszeiten_typ';

	$qry = 'GRANT USAGE, INSERT ON lehre.seq_tbl_tbl_unterrichtszeiten_typ_unterrichtszeitentyp_kurzbz TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_unterrichtszeiten_typ';

	$qry = 'GRANT USAGE, UPDATE ON lehre.seq_tbl_tbl_unterrichtszeiten_typ_unterrichtszeitentyp_kurzbz TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_unterrichtszeiten_typ';


	$qry = 'GRANT USAGE, INSERT ON lehre.seq_tbl_tbl_unterrichtszeiten_typ_unterrichtszeitentyp_kurzbz TO admin;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>admin</strong> on lehre.tbl_unterrichtszeiten_typ';

	$qry = 'GRANT USAGE, UPDATE ON lehre.seq_tbl_tbl_unterrichtszeiten_typ_unterrichtszeitentyp_kurzbz TO admin;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>admin</strong> on lehre.tbl_unterrichtszeiten_typ';


	$qry = "
	INSERT INTO lehre.tbl_unterrichtszeiten_typ (unterrichtszeitentyp_kurzbz, bezeichnung_mehrsprachig, aktiv, hintergrundfarbe) VALUES
		('unterrichtszeiten', ARRAY['de:unterrichtszeiten', 'en:teaching times'], 't', '#FFFFFF'),
		('vorlesungen', ARRAY['de:Vorlesung', 'en:Lecture'], 't', '#FF8A8A'),
		('backuptage', ARRAY['de:Übung', 'en:Exercise'], 't', '#8AFF8A'),
		('ausgleichswochen', ARRAY['de:Ausgleichswochen', 'en:Compensation Weeks'], 't', '#8A8AFF'),
		('prüfungswochen', ARRAY['de:Prüfungswochen', 'en:Exam Weeks'], 't', '#FFFF8A');
	";	

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_typ seeders: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Seeded lehre.tbl_unterrichtszeiten_typ with main class time slot types';

}

if(!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_unterrichtszeiten_gueltigkeit LIMIT 1"))
{
	$qry = '
		CREATE TABLE lehre.tbl_unterrichtszeiten_gueltigkeit (
			"unterrichtszeitengueltigkeit_id" BIGINT NOT NULL,
			"gueltig_von" DATE NOT NULL,
			"gueltig_bis" DATE NOT NULL,
			"oe_kurzbz" INTEGER NOT NULL,
			"ausbildungssemester" SMALLINT,
			"anmerkung" TEXT,
			"unterrichtszeitentyp_kurzbz" VARCHAR(32) NOT NULL,
			"studienplan_id" INTEGER,
			"insertamum" TIMESTAMP WITH TIME ZONE DEFAULT now(),
			"insertvon" VARCHAR(32),
			"updateamum" TIMESTAMP WITH TIME ZONE DEFAULT now(),
			"updatevon" VARCHAR(32),
			CONSTRAINT pk_unterrichtszeitengueltigkeit_id PRIMARY KEY("unterrichtszeitengueltigkeit_id")
			);';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_unterrichtszeiten_gueltigkeit table created';

	$db->db_query('CREATE SEQUENCE IF NOT EXISTS lehre.seq
     INCREMENT BY 1
     NO MAXVALUE
     NO MINVALUE
     CACHE 1;');

	$db->db_query("ALTER TABLE lehre.tbl_unterrichtszeiten_gueltigkeit ALTER COLUMN unterrichtszeitengueltigkeit_id SET DEFAULT nextval('lehre.seq');");

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_unterrichtszeiten_gueltigkeit TO admin;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>admin</strong> on lehre.tbl_unterrichtszeiten_gueltigkeit';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_unterrichtszeiten_gueltigkeit TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_unterrichtszeiten_gueltigkeit';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_unterrichtszeiten_gueltigkeit TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_unterrichtszeiten_gueltigkeit';

	$qry = 'GRANT USAGE, INSERT ON lehre.seq TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_unterrichtszeiten_gueltigkeit';

	$qry = 'GRANT USAGE, UPDATE ON lehre.seq TO vilesci;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_unterrichtszeiten_gueltigkeit';

	$qry = 'GRANT USAGE, INSERT ON lehre.seq TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_unterrichtszeiten_gueltigkeit';

	$qry = 'GRANT USAGE, UPDATE ON lehre.seq TO web;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_unterrichtszeiten_gueltigkeit';


	$qry = 'GRANT USAGE, INSERT ON lehre.seq TO admin;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>admin</strong> on lehre.tbl_unterrichtszeiten_gueltigkeit';

	$qry = 'GRANT USAGE, UPDATE ON lehre.seq TO admin;';
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>admin</strong> on lehre.tbl_unterrichtszeiten_gueltigkeit';
}



$result = $db->db_query("SELECT constraint_name FROM information_schema.table_constraints 
	WHERE table_name='tbl_unterrichtszeiten' AND constraint_type='FOREIGN KEY' AND constraint_name='fk_unterrichtszeitentyp_kurzbz'");
if($db->db_num_rows($result)==0)
{
	$qry = "ALTER TABLE lehre.tbl_unterrichtszeiten ADD CONSTRAINT fk_unterrichtszeitentyp_kurzbz FOREIGN KEY(unterrichtszeitentyp_kurzbz) REFERENCES lehre.tbl_unterrichtszeiten_typ(unterrichtszeitentyp_kurzbz);";
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added foreign key constraint fk_unterrichtszeitentyp_kurzbz to lehre.tbl_unterrichtszeiten';
}

$result = $db->db_query("SELECT constraint_name FROM information_schema.table_constraints 
	WHERE table_name='tbl_unterrichtszeiten' AND constraint_type='FOREIGN KEY' AND constraint_name='fk_unterrichtszeitengueltigkeit_id'");
if($db->db_num_rows($result)==0)
{
	$qry = "ALTER TABLE lehre.tbl_unterrichtszeiten ADD CONSTRAINT fk_unterrichtszeitengueltigkeit_id FOREIGN KEY(unterrichtszeitengueltigkeit_id) REFERENCES lehre.tbl_unterrichtszeiten_gueltigkeit(unterrichtszeitengueltigkeit_id);";
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added foreign key constraint fk_unterrichtszeitengueltigkeit_id to lehre.tbl_unterrichtszeiten';
}

$result = $db->db_query("SELECT constraint_name FROM information_schema.table_constraints 
	WHERE table_name='tbl_unterrichtszeiten_gueltigkeit' AND constraint_type='FOREIGN KEY' AND constraint_name='fk_unterrichtszeitentyp_kurzbz'");
if($db->db_num_rows($result)==0)
{
	$qry = "ALTER TABLE lehre.tbl_unterrichtszeiten_gueltigkeit ADD CONSTRAINT fk_unterrichtszeitentyp_kurzbz FOREIGN KEY(unterrichtszeitentyp_kurzbz) REFERENCES lehre.tbl_unterrichtszeiten_typ(unterrichtszeitentyp_kurzbz);";
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added foreign key constraint fk_unterrichtszeitentyp_kurzbz to lehre.tbl_unterrichtszeiten_gueltigkeit';
}

$result = $db->db_query("SELECT constraint_name FROM information_schema.table_constraints 
	WHERE table_name='tbl_unterrichtszeiten_gueltigkeit' AND constraint_type='FOREIGN KEY' AND constraint_name='fk_studienplan_id'");
if($db->db_num_rows($result)==0)
{
	$qry = "ALTER TABLE lehre.tbl_unterrichtszeiten_gueltigkeit ADD CONSTRAINT fk_studienplan_id FOREIGN KEY(studienplan_id) REFERENCES lehre.tbl_studienplan(studienplan_id);";
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added foreign key constraint fk_studienplan_id to lehre.tbl_unterrichtszeiten_gueltigkeit';
}

$result = $db->db_query("SELECT constraint_name FROM information_schema.table_constraints 
	WHERE table_name='tbl_unterrichtszeiten_gueltigkeit' AND constraint_type='FOREIGN KEY' AND constraint_name='fk_oe_kurzbz'");
if($db->db_num_rows($result)==0)
{
	$qry = "ALTER TABLE lehre.tbl_unterrichtszeiten_gueltigkeit ADD CONSTRAINT fk_oe_kurzbz FOREIGN KEY(oe_kurzbz) REFERENCES public.tbl_studiengang(studiengang_kz);";
	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_unterrichtszeiten_gueltigkeit: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Added foreign key constraint fk_oe_kurzbz to lehre.tbl_unterrichtszeiten_gueltigkeit';
}
?>
