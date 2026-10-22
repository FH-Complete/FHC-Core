<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// ADD COLUMNS insertamum, insertvon, updateamum, updatevon to system.tbl_fehler
if(!$result = @$db->db_query("SELECT insertamum FROM system.tbl_fehler LIMIT 1"))
{
	$qry = "ALTER TABLE system.tbl_fehler ADD COLUMN insertamum timestamp DEFAULT now();
			ALTER TABLE system.tbl_fehler ADD COLUMN insertvon varchar(32);
			ALTER TABLE system.tbl_fehler ADD COLUMN updateamum timestamp DEFAULT now();
			ALTER TABLE system.tbl_fehler ADD COLUMN updatevon varchar(32);";

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehler '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalten insertamum, insertvon, updateamum, updatevon in system.tbl_fehler hinzugefügt';
}

if (!$result = @$db->db_query('SELECT 1 FROM system.tbl_fehler_app LIMIT 1'))
{
	$qry = 'CREATE TABLE system.tbl_fehler_app
			(
				fehlercode varchar(64),
				app varchar(32) NOT NULL,
				insertamum timestamp DEFAULT now(),
				insertvon varchar(32)
			);

			COMMENT ON TABLE system.tbl_fehler_app IS \'Fehler app Zuordnungen\';
			COMMENT ON COLUMN system.tbl_fehler_app.fehlercode IS \'Fehler\';
			COMMENT ON COLUMN system.tbl_fehler_app.app IS \'dem Fehler zugeweisene App\';

			ALTER TABLE system.tbl_fehler_app ADD CONSTRAINT pk_fehler_app PRIMARY KEY (fehlercode, app);
			ALTER TABLE system.tbl_fehler_app ADD CONSTRAINT fk_fehler_app_app FOREIGN KEY (app) REFERENCES system.tbl_app(app) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_fehler_app ADD CONSTRAINT fk_fehler_app_fehlercode FOREIGN KEY (fehlercode) REFERENCES system.tbl_fehler(fehlercode) ON UPDATE CASCADE ON DELETE CASCADE;

			GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_fehler_app TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_fehler_app TO vilesci;

			-- prefill values
			INSERT INTO system.tbl_fehler_app(fehlercode, app, insertvon)
				SELECT fehlercode, app, \'dbupdate\' FROM system.tbl_fehler;

			-- remove not null constraint from old table
			ALTER TABLE system.tbl_fehler ALTER COLUMN app DROP NOT NULL;
		';

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehler_app: '.$db->db_last_error().'</strong><br>';
	else
		echo ' system.tbl_fehler_app: Tabelle hinzugefuegt<br>';
}