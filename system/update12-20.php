<?php
/* Copyright (C) 2009 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 ******************************************************************************
 * Beschreibung:
 * Dieses Skript fuehrt Datenbankupdates von Version 1.2 auf 2.0 durch
 */

require_once ('../config/system.config.inc.php');

// Datenbank Verbindung
//if (!$conn = pg_pconnect("host=.technikum-wien.at dbname= user= password="))
if (!$conn = pg_pconnect('host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWORD))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!'.pg_last_error($conn));

echo '<H1>DB-Updates!</H1>';
echo '<H2>Version 1.2 &rarr; 2.0</H2>';

// **************** lehre.tbl_prestudentstatus -> tbl_prestudentstatus ************************
if(!$result = @pg_query($conn, "SELECT * FROM public.tbl_status LIMIT 1;"))
{
	$qry = "ALTER TABLE public.tbl_status RENAME TO tbl_status;
			ALTER TABLE public.tbl_prestudentstatus RENAME TO tbl_prestudentstatus;
			ALTER TABLE public.tbl_status RENAME COLUMN status_kurzbz TO status_kurzbz;
			ALTER TABLE public.tbl_prestudentstatus RENAME COLUMN status_kurzbz TO status_kurzbz;
			UPDATE pg_catalog.pg_constraint SET conname='pk_tbl_status' WHERE conname='pk_tbl_status';
			UPDATE pg_catalog.pg_constraint SET conname='orgform_prestudentstatus' WHERE conname='orgform_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='pk_tbl_prestudentstatus' WHERE conname='pk_tbl_prestudentstatus';
			UPDATE pg_catalog.pg_constraint SET conname='prestudent_prestudentstatus' WHERE conname='prestudent_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='status_prestudentstatus' WHERE conname='rolle_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='studiensemester_prestudentstatus' WHERE conname='studiensemester_prestudentrolle';
			
			CREATE OR REPLACE FUNCTION get_rolle_prestudent (integer, character varying) returns character varying
			DECLARE i_prestudent_id ALIAS FOR $1;
			DECLARE cv_studiensemester_kurzbz ALIAS FOR $2;
			DECLARE rec RECORD;
			BEGIN
			    IF (cv_studiensemester_kurzbz IS NULL) THEN
			        SELECT INTO rec status_kurzbz 
			        FROM public.tbl_prestudentstatus
			        WHERE prestudent_id=i_prestudent_id 
			        ORDER BY datum desc,insertamum desc, ext_id desc
			        LIMIT 1;
			    ELSE
			        SELECT INTO rec status_kurzbz 
			        FROM tbl_prestudentstatus 
			        WHERE prestudent_id=i_prestudent_id AND studiensemester_kurzbz=cv_studiensemester_kurzbz
			        ORDER BY datum desc,insertamum desc, ext_id desc
			        LIMIT 1;
			    END IF;

			RETURN rec.status_kurzbz;
			END;
			";

	if(!pg_query($conn, $qry))
		echo '<strong>public.tbl_status: '.pg_last_error($conn).' </strong><br>';
	else
		echo '	public.tbl_status: Umbenannt auf tbl_status!<br>
				constrains umbenannt
				public.tbl_prestudentstatus: Umbenannt auf tbl_prestudentstatus!<br>
				constrains umbenannt';
}

// *************** public.tbl_organisationseinheit *******************************
if(!$result = @pg_query($conn, "SELECT * FROM public.tbl_organisationseinheit LIMIT 1;"))
{
	$qry = "CREATE TABLE public.tbl_organisationseinheit 
			(
 				oe_kurzbz Character varying(32) NOT NULL,
 				oe_parent_kurzbz Character varying(32),
 				bezeichnung Character varying(256),
 				organisationseinheittyp_kurzbz Character varying(16) NOT NULL
			)
			WITH (OIDS=FALSE);

			ALTER TABLE public.tbl_organisationseinheit ADD CONSTRAINT pk_tbl_organisationseinheit PRIMARY KEY (oe_kurzbz);
			ALTER TABLE public.tbl_organisationseinheit ADD CONSTRAINT oe_parent_oe FOREIGN KEY (oe_parent_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			CREATE TABLE public.tbl_organisationseinheittyp
			(
 				organisationseinheittyp_kurzbz Character varying(16) NOT NULL,
 				bezeichnung Character varying(256),
 				beschreibung text
			)
			WITH (OIDS=FALSE);
			
			ALTER TABLE public.tbl_organisationseinheittyp ADD CONSTRAINT pk_organisationseinheittyp PRIMARY KEY (organisationseinheittyp_kurzbz);
			ALTER TABLE public.tbl_organisationseinheit ADD CONSTRAINT organisationseinheit_organisationseinheittyp FOREIGN KEY (organisationseinheittyp_kurzbz) REFERENCES public.tbl_organisationseinheittyp (organisationseinheittyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;			
			
			GRANT SELECT on public.tbl_organisationseinheit TO GROUP ".DB_CIS_USER_GROUP.";
			GRANT SELECT, INSERT, UDPATE, DELETE on public.tbl_organisationseinheit TO GROUP ".DB_FAS_USER_GROUP.";
			
			GRANT SELECT on public.tbl_organisationseinheittyp TO GROUP ".DB_CIS_USER_GROUP.";
			GRANT SELECT, INSERT, UPDATE, DELETE on public.tbl_organisationseinheittyp TO GROUP ".DB_FAS_USER_GROUP.";
			
			ALTER TABLE public.tbl_studiengang ADD COLUMN oe_kurzbz character varying(32);
			ALTER TABLE public.tbl_studiengang ADD CONSTRAINT studiengang_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			ALTER TABLE public.tbl_fachbereich ADD COLUMN oe_kurzbz character varying(32);
			ALTER TABLE public.tbl_fachbereich ADD CONSTRAINT fachbereich_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			";
	if(!pg_query($conn, $qry))
		echo '<strong>public.tbl_organisationsform: '.pg_last_error($conn).' </strong><br>';
	else
		echo '	public.tbl_organisationsform: Tabelle wurde hinzugef�gt!<br>';
;
}

// ************* system.tbl_berechtigung ******************
if(!$result = @pg_query($conn, "SELECT * FROM system.tbl_berechtigung LIMIT 1;"))
{
	$qry = "CREATE SCHEMA system;
			CREATE TABLE system.tbl_benutzerrolle
			(
 				benutzerberechtigung_id serial NOT NULL,
 				uid Character varying(16),
 				funktion_kurzbz Character varying(16),
 				status_kurzbz Character varying(32),
 				berechtigung_kurzbz Character varying(16),
 				art Character varying(5) DEFAULT 'r'::character varying NOT NULL,
 				oe_kurzbz Character varying(32),
				studiensemester_kurzbz Character varying(16),
				start Date,
				ende Date,
				negativ Boolean DEFAULT FALSE NOT NULL,
				updateamum Timestamp,
				updatevon Character varying(16),
				insertamum Timestamp DEFAULT now(),
				insertvon Character varying(16)
			)
			WITH (OIDS=FALSE);
			
			CREATE INDEX idx_userberechtigung_uid ON system.tbl_benutzerrolle (uid);
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT pk_tbl_benutzerberechtigung PRIMARY KEY (benutzerberechtigung_id);
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT benutzerrolle_benutzer FOREIGN KEY (uid) REFERENCES public.tbl_benutzer (uid) ON DELETE CASCADE ON UPDATE CASCADE;
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT benutzerrolle_funktion FOREIGN KEY (funktion_kurzbz) REFERENCES public.tbl_funktion (funktion_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			CREATE TABLE system.tbl_berechtigung
			(
 				berechtigung_kurzbz Character varying(16) NOT NULL,
 				beschreibung Character varying(256)
			)
			WITH (OIDS=FALSE);
			ALTER TABLE system.tbl_berechtigung ADD CONSTRAINT pk_tbl_berechtigung PRIMARY KEY (berechtigung_kurzbz);
			
			CREATE TABLE system.tbl_status
			(
 				status_kurzbz Character varying(32) NOT NULL,
 				beschreibung Character varying(256)
			) WITH (OIDS=FALSE);
			
			ALTER TABLE system.tbl_status ADD CONSTRAINT pk_tbl_status PRIMARY KEY (status_kurzbz);
			
			CREATE TABLE system.tbl_statusberechtigung
			(
 				berechtigung_kurzbz Character varying(16) NOT NULL,
 				status_kurzbz Character varying(32) NOT NULL
			)
			WITH (OIDS=FALSE);
			
			ALTER TABLE system.tbl_statusberechtigung ADD CONSTRAINT pk_tbl_statusberechtigung PRIMARY KEY (berechtigung_kurzbz,status_kurzbz);
			ALTER TABLE system.tbl_statusberechtigung ADD CONSTRAINT rolleberechtigung_berechtigung FOREIGN KEY (berechtigung_kurzbz) REFERENCES system.tbl_berechtigung (berechtigung_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE system.tbl_statusberechtigung ADD CONSTRAINT rolleberechtigung_rolle FOREIGN KEY (status_kurzbz) REFERENCES system.tbl_status (status_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT benutzerrolle_rolle FOREIGN KEY (status_kurzbz) REFERENCES system.tbl_status (status_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT benutzerrolle_berechtigung FOREIGN KEY (berechtigung_kurzbz) REFERENCES system.tbl_berechtigung (berechtigung_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT benutzerrolle_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT benutzerrolle_studienseemster FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;


			GRANT SELECT ON system.tbl_benutzerrolle TO GROUP ".DB_CIS_USER_GROUP.";
			GRANT SELECT ON system.tbl_berechtigung TO GROUP ".DB_CIS_USER_GROUP.";
			GRANT SELECT ON system.tbl_status TO GROUP ".DB_CIS_USER_GROUP.";
			GRANT SELECT ON system.tbl_statusberechtigung TO GROUP ".DB_CIS_USER_GROUP.";
			
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_benutzerrolle TO GROUP ".DB_FAS_USER_GROUP.";
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_berechtigung TO GROUP ".DB_FAS_USER_GROUP.";
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_status TO GROUP ".DB_FAS_USER_GROUP.";
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_statusberechtigung TO GROUP ".DB_FAS_USER_GROUP.";
			";
	
	if(!pg_query($conn, $qry))
		echo '<strong>system schema: '.pg_last_error($conn).' </strong><br>';
	else
		echo 'system schema: Berechtigungstabellen wurden hinzugef�gt!<br>';

}
?>
