<?php

if (! defined('DB_NAME')) exit('No direct script access allowed');


if ($result = @$db->db_query("SELECT 1 FROM system.tbl_app WHERE app='personalverwaltung' LIMIT 1"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "INSERT INTO system.tbl_app(app) VALUES('personalverwaltung');";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_app: '.$db->db_last_error().'</strong><br>';
		else
			echo ' system.tbl_app: Personalverwaltung hinzugefügt<br>';
	}
}

if (!$result = @$db->db_query('SELECT 1 FROM system.tbl_fehler_konfigurationsdatentyp LIMIT 1'))
{
	$qry = 'CREATE TABLE system.tbl_fehler_konfigurationsdatentyp
			(
				konfigurationsdatentyp varchar(32)
			);

			COMMENT ON TABLE system.tbl_fehler_konfigurationsdatentyp IS \'Konfigurationsparameter Datentypen\';
			COMMENT ON COLUMN system.tbl_fehler_konfigurationsdatentyp.konfigurationsdatentyp IS \'Datentyp der Konfigurationsparameter, z.B. integer oder string\';

			ALTER TABLE system.tbl_fehler_konfigurationsdatentyp ADD CONSTRAINT pk_fehler_konfigurationsdatentyp PRIMARY KEY (konfigurationsdatentyp);

			GRANT SELECT ON system.tbl_fehler_konfigurationsdatentyp TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_fehler_konfigurationsdatentyp TO vilesci;

			-- prefill values
			INSERT INTO system.tbl_fehler_konfigurationsdatentyp(konfigurationsdatentyp) VALUES(\'integer\');
			INSERT INTO system.tbl_fehler_konfigurationsdatentyp(konfigurationsdatentyp) VALUES(\'float\');
			INSERT INTO system.tbl_fehler_konfigurationsdatentyp(konfigurationsdatentyp) VALUES(\'string\');
		';

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehler_konfigurationsdatentyp: '.$db->db_last_error().'</strong><br>';
	else
		echo ' system.tbl_fehler_konfigurationsdatentyp: Tabelle hinzugefuegt<br>';
}

if (!$result = @$db->db_query('SELECT 1 FROM system.tbl_fehler_konfigurationstyp LIMIT 1'))
{
	$qry = 'CREATE TABLE system.tbl_fehler_konfigurationstyp
			(
				konfigurationstyp_kurzbz varchar(64),
				beschreibung text,
				konfigurationsdatentyp varchar(32),
				app varchar(32) NOT NULL
			);

			COMMENT ON TABLE system.tbl_fehler_konfigurationstyp IS \'Konfigurationsparameter Typen\';
			COMMENT ON COLUMN system.tbl_fehler_konfigurationstyp.konfigurationstyp_kurzbz IS \'Art der Konfiguration\';
			COMMENT ON COLUMN system.tbl_fehler_konfigurationstyp.beschreibung IS \'Kurze Erklärung, was die Konfiguration bewirkt\';
			COMMENT ON COLUMN system.tbl_fehler_konfigurationstyp.app IS \'App, für welche die Konfiguration gilt\';

			ALTER TABLE system.tbl_fehler_konfigurationstyp ADD CONSTRAINT pk_fehler_konfigurationstyp PRIMARY KEY (konfigurationstyp_kurzbz);
			ALTER TABLE system.tbl_fehler_konfigurationstyp ADD CONSTRAINT fk_fehler_konfigurationstyp_app FOREIGN KEY (app) REFERENCES system.tbl_app(app) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_fehler_konfigurationstyp ADD CONSTRAINT fk_fehler_konfigurationstyp_konfigurationsdatentyp FOREIGN KEY (konfigurationsdatentyp) REFERENCES system.tbl_fehler_konfigurationsdatentyp(konfigurationsdatentyp) ON UPDATE CASCADE ON DELETE RESTRICT;

			GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_fehler_konfigurationstyp TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_fehler_konfigurationstyp TO vilesci;

			-- prefill values
			INSERT INTO system.tbl_fehler_konfigurationstyp(konfigurationstyp_kurzbz, beschreibung, konfigurationsdatentyp, app)
				VALUES(\'exkludierteStudiengaenge\', \'Studiengangskennzahlen von Studiengängen, die nicht bei den Studierendenplausichecks berücksichtigt werden\', \'integer\', \'core\');
		';

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehler_konfigurationstyp: '.$db->db_last_error().'</strong><br>';
	else
		echo ' system.tbl_fehler_konfigurationstyp: Tabelle hinzugefuegt<br>';
}

if (!$result = @$db->db_query('SELECT 1 FROM system.tbl_fehler_konfiguration LIMIT 1'))
{
	$qry = 'CREATE TABLE system.tbl_fehler_konfiguration
			(
				konfigurationstyp_kurzbz varchar(64),
				fehlercode varchar(64),
				konfiguration jsonb NOT NULL,
				insertamum timestamp default NOW(),
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

			COMMENT ON TABLE system.tbl_fehler_konfiguration IS \'Konfigurationsparameter pro Fehler\';
			COMMENT ON COLUMN system.tbl_fehler_konfiguration.konfigurationstyp_kurzbz IS \'Art der Konfiguration\';
			COMMENT ON COLUMN system.tbl_fehler_konfiguration.konfiguration IS \'Konfigruationsparameter \';

			ALTER TABLE system.tbl_fehler_konfiguration ADD CONSTRAINT pk_fehler_konfiguration PRIMARY KEY (konfigurationstyp_kurzbz, fehlercode);
			ALTER TABLE system.tbl_fehler_konfiguration ADD CONSTRAINT fk_fehler_konfiguration_konfigurationstyp_kurzbz FOREIGN KEY (konfigurationstyp_kurzbz) REFERENCES system.tbl_fehler_konfigurationstyp(konfigurationstyp_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;
			ALTER TABLE system.tbl_fehler_konfiguration ADD CONSTRAINT fk_fehler_konfiguration_fehlercode FOREIGN KEY (fehlercode) REFERENCES system.tbl_fehler(fehlercode) ON UPDATE CASCADE ON DELETE RESTRICT;

			GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_fehler_konfiguration TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_fehler_konfiguration TO vilesci;
		';

	if(!$db->db_query($qry))
		echo '<strong>system.tbl_fehler_konfiguration: '.$db->db_last_error().'</strong><br>';
	else
		echo ' system.tbl_fehler_konfiguration: Tabelle hinzugefuegt<br>';
}
