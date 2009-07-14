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

require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');

$db = new basis_db();

echo '<H1>DB-Updates!</H1>';
echo '<H2>Version 1.2 &rarr; 2.0</H2>';

// **************** lehre.tbl_prestudentstatus -> tbl_prestudentstatus ************************
if(!@$db->db_query('SELECT * FROM public.tbl_status LIMIT 1;'))
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
			
			CREATE OR REPLACE FUNCTION get_rolle_prestudent (integer, character varying) returns character varying as $$
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
			$$ LANGUAGE plpgsql;
			";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_status: '.$db->db_last_error().' </strong><br>';
	else
		echo '	public.tbl_status: Umbenannt auf tbl_status!<br>
				constrains umbenannt
				public.tbl_prestudentstatus: Umbenannt auf tbl_prestudentstatus!<br>
				constrains umbenannt';
}

// *************** public.tbl_organisationseinheit *******************************
if(!@$db->db_query('SELECT * FROM public.tbl_organisationseinheit LIMIT 1;'))
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
			GRANT SELECT, INSERT, UPDATE, DELETE on public.tbl_organisationseinheit TO GROUP ".DB_FAS_USER_GROUP.";
			
			GRANT SELECT on public.tbl_organisationseinheittyp TO GROUP ".DB_CIS_USER_GROUP.";
			GRANT SELECT, INSERT, UPDATE, DELETE on public.tbl_organisationseinheittyp TO GROUP ".DB_FAS_USER_GROUP.";
			
			ALTER TABLE public.tbl_studiengang ADD COLUMN oe_kurzbz character varying(32);
			ALTER TABLE public.tbl_studiengang ADD CONSTRAINT studiengang_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			ALTER TABLE public.tbl_fachbereich ADD COLUMN oe_kurzbz character varying(32);
			ALTER TABLE public.tbl_fachbereich ADD CONSTRAINT fachbereich_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			
			-- ORGANISATIONSEINHEITEN Anlegen
			INSERT INTO public.tbl_organisationseinheittyp(organisationseinheittyp_kurzbz, bezeichnung, beschreibung) VALUES('Erhalter',null, null);
			INSERT INTO public.tbl_organisationseinheittyp(organisationseinheittyp_kurzbz, bezeichnung, beschreibung) VALUES('Studienzentrum',null, null);
			INSERT INTO public.tbl_organisationseinheittyp(organisationseinheittyp_kurzbz, bezeichnung, beschreibung) VALUES('Studiengang',null, null);
			INSERT INTO public.tbl_organisationseinheittyp(organisationseinheittyp_kurzbz, bezeichnung, beschreibung) VALUES('Institut',null, null);
			INSERT INTO public.tbl_organisationseinheittyp(organisationseinheittyp_kurzbz, bezeichnung, beschreibung) VALUES('Abteilung',null, null);

			-- Technikum Wien Spezifisch!!
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('etw',null,'Technikum Wien','Erhalter');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('ctee','etw','Communication Technologies & Electronic Engineering','Studienzentrum');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('bel','ctee','BEL','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('bew','ctee','BEW','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('bic','ctee','BIC','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mes','ctee','MES','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mie','ctee','MIE','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mtm','ctee','MTM','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mti','ctee','MTI','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('EmbeddedSystems','ctee','Embedded System','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Telecom','ctee','Telecommunications & Internet Technologies','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('ElectronicEng','ctee','Electronic Engineering','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('itbs','etw','Information Technologies & Business Solutions','Studienzentrum');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('bif','itbs','BIF','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('bit','itbs','BIT','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('bwi','itbs','BWI','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mic','itbs','MIC','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mit','itbs','MIT','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mse','itbs','MSE','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mwi','itbs','MWI','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mgs','itbs','MGS','Studiengang');		
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Informatik','itbs','Informatik','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Wirtschaftsinf','itbs','Wirtschaftsinformatik','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('InformationEng','itbs','Information Engineering & Security','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('eet','etw','Engineering & Environmental Technologies','Studienzentrum');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('biw','eet','BIW','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('miw','eet','MIW','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('bmr','eet','BMR','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mmr','eet','MMR','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('bee','eet','BEE','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mee','eet','MEE','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Mechatronics','eet','Mechatronics','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('AdvancedTech','eet','Advanced Technologies','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('ErnEnergie','eet','Erneuerbare Energietechnologien','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('lst','etw','Life Science Technologies','Studienzentrum');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('bbe','lst','BBE','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('bst','lst','BST','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mbe','lst','MBE','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mgr','lst','MGR','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mst','lst','MST','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('mut','lst','MUT','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('BiomedTech','lst','Biomedizinische Technik & Umweltmanagement','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('SportsEng','lst','Sports Engineering and Biomechanics','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('AngewandteMath','etw','Angewandte Mathematik und Naturwissenschaften','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('MgmntWirtRecht','etw','Management, Wirtschaft, Recht','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Sozialkompetenz','etw','Sozialkompetenz- und Managementmethoden','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Sprachen','etw','Sprachen und Kulturwissenschaften','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Dummy','etw','Dummy','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Freifaecher','etw','Freifaecher','Institut');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Praxissemester','etw','Praxissemester','Institut');		
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('eak','etw','Aufbaukurse','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('lca','etw','Cisco Academy','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('ltc','etw','LLL China','Studiengang');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Auslandsbuero','etw','Auslandsbüro','Abteilung');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Bibliothek','etw','Bibliothek','Abteilung');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Geschaeftsltg','etw','Geschäftsleitung','Abteilung');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Unternehmenskommunikation','etw','Unternehmenskommunikation','Abteilung');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Infrastruktur','etw','Infrastruktur','Abteilung');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Haustechnik','Infrastruktur','Haustechnik','Abteilung');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('ServiceDesk','Infrastruktur','ServiceDesk','Abteilung');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('Systementwicklung','Infrastruktur','Systementwicklung','Abteilung');
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) VALUES('ZentrServices','Infrastruktur','ZentraleServices','Abteilung');

			-- Alle noch nicht eingetragenen Institute und Studiengaenge direkt unter etw haengen
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) SELECT lower(typ::varchar(1) || kurzbz), 'etw',  UPPER(typ::varchar(1) || kurzbz), 'Studiengang' FROM public.tbl_studiengang WHERE lower(typ::varchar(1) || kurzbz) not in (SELECT oe_kurzbz FROM public.tbl_organisationseinheit) AND studiengang_kz<>999;
			INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz) SELECT fachbereich_kurzbz, 'etw', bezeichnung, 'Institut' FROM public.tbl_fachbereich WHERE fachbereich_kurzbz not in (SELECT oe_kurzbz FROM public.tbl_organisationseinheit);

			-- Eintraege in Tabelle Studiengang und Fachbereich
			UPDATE public.tbl_studiengang set oe_kurzbz = lower(typ::varchar(1) || kurzbz) WHERE  lower(typ::varchar(1) || kurzbz) in(select oe_kurzbz FROM public.tbl_organisationseinheit);
			UPDATE public.tbl_fachbereich set oe_kurzbz=fachbereich_kurzbz WHERE fachbereich_kurzbz in(select oe_kurzbz FROM public.tbl_organisationseinheit);

			";
	if(!$db->db_query($qry))
		echo '<strong>public.tbl_organisationseinheit: '.$db->db_last_error().' </strong><br>';
	else
		echo '	public.tbl_organisationseinheit: Tabelle wurde hinzugef�gt!<br>';
;
}

// ************* system.tbl_berechtigung ******************
if(!@$db->db_query('SELECT * FROM system.tbl_berechtigung LIMIT 1;'))
{
	$qry = "CREATE SCHEMA system;
	
			GRANT USAGE ON SCHEMA system TO GROUP ".DB_CIS_USER_GROUP.";
			GRANT USAGE ON SCHEMA system TO GROUP ".DB_FAS_USER_GROUP.";
			
			CREATE TABLE system.tbl_benutzerrolle
			(
 				benutzerberechtigung_id serial NOT NULL,
 				rolle_kurzbz Character varying(32),
 				berechtigung_kurzbz Character varying(32),
 				uid Character varying(16),
 				funktion_kurzbz Character varying(16),
 				oe_kurzbz Character varying(32), 				
 				art Character varying(5) DEFAULT 's'::character varying NOT NULL,
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
 				berechtigung_kurzbz Character varying(32) NOT NULL,
 				beschreibung Character varying(256)
			)
			WITH (OIDS=FALSE);
			ALTER TABLE system.tbl_berechtigung ADD CONSTRAINT pk_tbl_berechtigung PRIMARY KEY (berechtigung_kurzbz);
			
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT benutzerrolle_berechtigung FOREIGN KEY (berechtigung_kurzbz) REFERENCES system.tbl_berechtigung (berechtigung_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT benutzerrolle_organisationseinheit FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT benutzerrolle_studiensemester FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

			CREATE TABLE system.tbl_rolle
			(
 				rolle_kurzbz Character varying(32) NOT NULL,
 				beschreibung Character varying(256)
			)
			WITH (OIDS=FALSE);
			
			CREATE TABLE system.tbl_rolleberechtigung
			(
 				berechtigung_kurzbz Character varying(32) NOT NULL,
 				rolle_kurzbz Character varying(32),
 				art Character varying(5)
			)
			WITH (OIDS=FALSE);

			
			ALTER TABLE system.tbl_rolle ADD CONSTRAINT pk_tbl_rolle PRIMARY KEY (rolle_kurzbz);
			ALTER TABLE system.tbl_rolleberechtigung ADD CONSTRAINT pk_tbl_rolleberechtigung PRIMARY KEY(berechtigung_kurzbz, rolle_kurzbz);
			ALTER TABLE system.tbl_rolleberechtigung ADD CONSTRAINT rolleberechtigung_rolle FOREIGN KEY(rolle_kurzbz) REFERENCES system.tbl_rolle (rolle_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE system.tbl_rolleberechtigung ADD CONSTRAINT rolleberechtigung_berechtigung FOREIGN KEY(berechtigung_kurzbz) REFERENCES system.tbl_berechtigung (berechtigung_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
			ALTER TABLE system.tbl_benutzerrolle ADD CONSTRAINT benutzerrolle_rolle FOREIGN KEY (rolle_kurzbz) REFERENCES system.tbl_rolle (rolle_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;			
			
			GRANT SELECT ON system.tbl_benutzerrolle TO GROUP ".DB_CIS_USER_GROUP.";
			GRANT SELECT ON system.tbl_berechtigung TO GROUP ".DB_CIS_USER_GROUP.";
			GRANT SELECT ON system.tbl_rolle TO GROUP ".DB_CIS_USER_GROUP.";
			GRANT SELECT ON system.tbl_rolleberechtigung TO GROUP ".DB_CIS_USER_GROUP.";
			
			GRANT SELECT, UPDATE ON system.tbl_benutzerrolle_benutzerberechtigung_id_seq TO GROUP ".DB_FAS_USER_GROUP.";
			GRANT SELECT, UPDATE ON system.tbl_benutzerrolle_benutzerberechtigung_id_seq TO GROUP ".DB_CIS_USER_GROUP.";
			
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_benutzerrolle TO GROUP ".DB_FAS_USER_GROUP.";
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_berechtigung TO GROUP ".DB_FAS_USER_GROUP.";
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_rolle TO GROUP ".DB_FAS_USER_GROUP.";
			GRANT SELECT, INSERT, UPDATE, DELETE ON system.tbl_rolleberechtigung TO GROUP ".DB_FAS_USER_GROUP.";
			
			
			--- SYNCRONISIEREN
			
			INSERT INTO system.tbl_rolle(rolle_kurzbz, beschreibung) SELECT berechtigung_kurzbz, beschreibung FROM public.tbl_berechtigung;

			-- Berechtigungen uebernehmen
			
			INSERT INTO system.tbl_benutzerrolle(uid, funktion_kurzbz, rolle_kurzbz, berechtigung_kurzbz, art, oe_kurzbz, 
												studiensemester_kurzbz, start, ende, negativ, updateamum, updatevon, insertamum, insertvon)
			SELECT 
				uid, null, berechtigung_kurzbz, null, art,
				CASE WHEN fachbereich_kurzbz IS NOT NULL THEN (SELECT oe_kurzbz FROM public.tbl_fachbereich WHERE fachbereich_kurzbz=tbl_benutzerberechtigung.fachbereich_kurzbz)
					 WHEN studiengang_kz IS NOT NULL THEN (SELECT oe_kurzbz FROM public.tbl_studiengang WHERE studiengang_kz=tbl_benutzerberechtigung.studiengang_kz)
					 ELSE null
				END, 
				studiensemester_kurzbz, start, ende, false, updateamum, updatevon, insertamum, insertvon
			FROM public.tbl_benutzerberechtigung;

			--- ALTE TABELLE LOESCHEN
			DROP TABLE public.tbl_benutzerberechtigung;

			-- Berechtigung anlegen
			INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) SELECT rolle_kurzbz, beschreibung FROM system.tbl_rolle;
			
			-- Berechtigungen zu den Rollen
			INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) SELECT rolle_kurzbz, rolle_kurzbz, 'suid' FROM system.tbl_rolle;
			
			INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('mitarbeiter','admin','suid');
			INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lehre','admin','suid');
			INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lv-plan','admin','suid');
			INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('raumres','admin','suid');
			INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('assistenz','admin','suid');
			INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('news','admin','suid');
			INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('preinteressent','admin','suid');
			INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('veranstaltung','admin','suid');
			
			-- Bei alle ohne oe_kurzbz die Organisaitonseinheit auf etw setzen (admins)
			UPDATE system.tbl_benutzerrolle SET oe_kurzbz='etw' WHERE oe_kurzbz is null;
			";
	
	if(!$db->db_query($qry))
		echo '<strong>system schema: '.$db->db_last_error().' </strong><br>';
	else
		echo 'system schema: Berechtigungstabellen wurden hinzugefuegt!<br>';

}

// **************** public.tbl_benutzerfunktion ************************
if(!@$db->db_query('SELECT oe_kurzbz FROM public.tbl_benutzerfunktion LIMIT 1;'))
{
	$qry = "ALTER TABLE public.tbl_benutzerfunktion ADD COLUMN oe_kurzbz Character varying(32);
			ALTER TABLE public.tbl_benutzerfunktion ADD COLUMN datum_von Date;
			ALTER TABLE public.tbl_benutzerfunktion ADD COLUMN datum_bis Date;
			
			-- studiengang in oe_kurzbz kopieren
			UPDATE public.tbl_benutzerfunktion SET oe_kurzbz = (SELECT lower(typ::varchar(1) || kurzbz) FROM public.tbl_studiengang WHERE studiengang_kz=tbl_benutzerfunktion.studiengang_kz);
			
			DROP VIEW public.vw_benutzerfunktion;
			
			ALTER TABLE public.tbl_benutzerfunktion ALTER COLUMN oe_kurzbz SET NOT NULL;
			
			-- spalte loeschen
			ALTER TABLE public.tbl_benutzerfunktion DROP COLUMN studiengang_kz;
			";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_benutzerfunktion: '.$db->db_last_error().' </strong><br>';
	else
		echo '	public.tbl_benutzerfunktion: Umbenannt auf oe_kurzbz statt studiengang_kz hinzugefuegt!<br>
				Datum Von/Bis hinzugefuegt';
}


?>
