<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 ******************************************************************************
 * Beschreibung:
 * Dieses Skript fuehrt Datenbankupdates von Version 1.2 auf 2.0 durch
 */

require_once ('../vilesci/config.inc.php');

// Datenbank Verbindung
//if (!$conn = pg_pconnect("host=.technikum-wien.at dbname= user= password="))
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!'.pg_last_error($conn));

echo '<H1>DB-Updates!</H1>';
echo '<H2>Version 1.2 &rarr; 2.0</H2>';

// **************** lehre.tbl_projektarbeit.sprache *******************************
if(!$result = @pg_query($conn, "SELECT * FROM public.tbl_rolle LIMIT 1;"))
{
	$qry = "ALTER TABLE public.tbl_rolle RENAME TO tbl_status;
			ALTER TABLE public.tbl_prestudentrolle RENAME TO tbl_prestudentstatus;
			ALTER TABLE public.tbl_status RENAME COLUMN rolle_kurzbz TO status_kurzbz;
			ALTER TABLE public.tbl_prestudentstatus RENAME COLUMN rolle_kurzbz TO status_kurzbz;
			UPDATE pg_catalog.pg_constraint SET conname='pk_tbl_status' WHERE conname='pk_tbl_rolle';
			UPDATE pg_catalog.pg_constraint SET conname='orgform_prestudentstatus' WHERE conname='orgform_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='pk_tbl_prestudentstatus' WHERE conname='pk_tbl_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='prestudent_prestudentstatus' WHERE conname='prestudent_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='status_prestudentstatus' WHERE conname='rolle_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='studiensemester_prestudentstatus' WHERE conname='studiensemester_prestudentrolle';
			";

	if(!pg_query($conn, $qry))
		echo '<strong>public.tbl_rolle: '.pg_last_error($conn).' </strong><br>';
	else
		echo '	public.tbl_rolle: Umbenannt auf tbl_status!<br>
				constrains umbenannt
				public.tbl_prestudentrolle: Umbenannt auf tbl_prestudentstatus!<br>
				constrains umbenannt';
}

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
			
			GRANT SELECT on public.tbl_organisationseinheit TO GROUP web;
			GRANT SELECT, INSERT, UDPATE, DELETE on public.tbl_organisationseinheit TO GROUP admin;
			
			GRANT SELECT on public.tbl_organisationseinheittyp TO GROUP web;
			GRANT SELECT, INSERT, UPDATE, DELETE on public.tbl_organisationseinheittyp TO GROUP admin;
			
			ALTER TABLE public.tbl_studiengang ADD COLUMN oe_kurzbz character varying(32);
			ALTER TABLE public.tbl_studiengang ADD CONSTRAINT studiengang_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			ALTER TABLE public.tbl_fachbereich ADD COLUMN oe_kurzbz character varying(32);
			ALTER TABLE public.tbl_fachbereich ADD CONSTRAINT fachbereich_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			";
	if(!pg_query($conn, $qry))
		echo '<strong>public.tbl_organisationsform: '.pg_last_error($conn).' </strong><br>';
	else
		echo '	public.tbl_organisationsform: Tabelle wurde hinzugefügt!<br>';
;
}

if(!$result = @pg_query($conn, "SELECT * FROM system.tbl_berechtigung LIMIT 1;"))
{
	$qry = "CREATE SCHEMA system;
			CREATE TABLE system.tbl_benutzerrolle
			(
 				benutzerberechtigung_id serial NOT NULL,
 				uid Character varying(16),
 				funktion_kurzbz Character varying(16),
 				rolle_kurzbz Character varying(32),
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
			
			CREATE TABLE system.tbl_berechtigung
			(
 				berechtigung_kurzbz Character varying(16) NOT NULL,
 				beschreibung Character varying(256)
			)
			WITH (OIDS=FALSE);
			ALTER TABLE system.tbl_berechtigung ADD CONSTRAINT pk_tbl_berechtigung PRIMARY KEY (berechtigung_kurzbz);
			
			CREATE TABLE system.tbl_rolle
			(
 				rolle_kurzbz Character varying(32) NOT NULL,
 				beschreibung Character varying(256)
			) WITH (OIDS=FALSE);
			
			ALTER TABLE system.tbl_rolle ADD CONSTRAINT pk_tbl_rolle PRIMARY KEY (rolle_kurzbz);
			
			CREATE TABLE system.tbl_rolleberechtigung
			(
 				berechtigung_kurzbz Character varying(16) NOT NULL,
 				rolle_kurzbz Character varying(32) NOT NULL
			)
			WITH (OIDS=FALSE);
			
			ALTER TABLE system.tbl_rolleberechtigung ADD CONSTRAINT pk_tbl_rolleberechtigung PRIMARY KEY (berechtigung_kurzbz,rolle_kurzbz);

			GRANT SELECT ON system.tbl_benutzerrolle TO GROUP web;
			GRANT SELECT ON system.tbl_berechtigung TO GROUP web;
			GRANT SELECT ON system.tbl_rolle TO GROUP web;
			GRANT SELECT ON system.tbl_rolleberechtigung TO GROUP web;
			
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_benutzerrolle TO GROUP web;
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_berechtigung TO GROUP web;
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_rolle TO GROUP web;
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_rolleberechtigung TO GROUP web;
			";
	
	if(!pg_query($conn, $qry))
		echo '<strong>system schema: '.pg_last_error($conn).' </strong><br>';
	else
		echo 'system schema: Tabellen wurde hinzugefügt!<br>';

}
?>
