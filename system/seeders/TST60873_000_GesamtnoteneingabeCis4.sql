-- Seeder of feature-60873/GesamtnoteneingabeCis4. A "-- ==== <group> ====" line starts a group; tests/cypress/tools/dbCheck.js applies single groups.

-- ==== helper_functions ====
-- Semester helpers for group studiengang_5. BS001_helper_functions.sql does not define them.

CREATE OR REPLACE FUNCTION CurrentSemester() RETURNS varchar(32) AS $$
DECLARE res varchar(32);
	BEGIN
		SELECT studiensemester_kurzbz into res FROM public.tbl_studiensemester WHERE start<=now() AND ende>=now() ORDER BY start DESC LIMIT 1;
		return res;
	END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION LastActiveSemester() RETURNS varchar(32) AS $$
DECLARE res varchar(32);
	BEGIN
		SELECT studiensemester_kurzbz into res FROM public.tbl_studiensemester WHERE start<=now() ORDER BY start DESC LIMIT 1;
		return res;
	END;
$$ LANGUAGE plpgsql;

-- ==== studiengang_5 ====
-- Demodaten fuer Studiengang 5: Organisationseinheit, Studienordnung, Studiengang, Lehrveranstaltungen

INSERT INTO public.tbl_organisationseinheit (oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz, aktiv, mailverteiler, freigabegrenze, kurzzeichen, lehre, standort, warn_semesterstunden_frei, warn_semesterstunden_fix, standort_id)
VALUES('stg5', 'studiengaenge', 'Studiengang 5', 'Studiengang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL);

INSERT INTO public.tbl_studiengang (studiengang_kz,kurzbz,kurzbzlang,typ,bezeichnung,english,farbe,email,telefon,max_semester,max_verband,max_gruppe,erhalter_kz,bescheid,bescheidbgbl1,bescheidbgbl2,bescheidgz,bescheidvom,titelbescheidvom,aktiv,ext_id,orgform_kurzbz,zusatzinfo_html,moodle,oe_kurzbz,sprache,testtool_sprachwahl,studienplaetze,lgartcode,mischform,projektarbeit_note_anzeige,melderelevant,foerderrelevant,standort_code,onlinebewerbung,melde_studiengang_kz) VALUES
 (5,'S5','STG5','b','Studiengang 5','Studiengang 5',NULL,'invalid@example.com',NULL,6,'B','2',5,NULL,NULL,NULL,NULL,NULL,NULL,true,NULL,'VZ','',true,'stg5','German',true,NULL,NULL,false,true,true,true,NULL,false,'0002')
;

INSERT INTO "system".tbl_benutzerrolle
(rolle_kurzbz, berechtigung_kurzbz, uid, funktion_kurzbz, oe_kurzbz, art, studiensemester_kurzbz, "start", ende, negativ, updateamum, updatevon, insertamum, insertvon, kostenstelle_id, anmerkung)
VALUES('assistenz', NULL, 'demoassistenz', NULL, 'stg5', 'suid', NULL, '2025-09-18', NULL, false, NULL, NULL, '2025-09-18 15:46:02.000', 'demoadmin', NULL, NULL);

INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, ext_id, orgform_kurzbz) VALUES
 (5, 1, '','', true, null, null, null),
 (5, 1, 'A','', true, null, null, null),
 (5, 1, 'B','', true, null, null, null),
 (5, 2, '','', true, null, null, null),
 (5, 2, 'A','', true, null, null, null),
 (5, 2, 'B','', true, null, null, null),
 (5, 3, '','', true, null, null, null),
 (5, 3, 'A','', true, null, null, null),
 (5, 3, 'B','', true, null, null, null),
 (5, 4, '','', true, null, null, null),
 (5, 4, 'A','', true, null, null, null),
 (5, 4, 'B','', true, null, null, null),
 (5, 5, '','', true, null, null, null),
 (5, 5, 'A','', true, null, null, null),
 (5, 5, 'B','', true, null, null, null),
 (5, 6, '','', true, null, null, null),
 (5, 6, 'A','', true, null, null, null),
 (5, 6, 'B','', true, null, null, null)
;

INSERT INTO lehre.tbl_studienordnung (studienordnung_id, studiengang_kz,"version",gueltigvon,gueltigbis,bezeichnung,ects,studiengangbezeichnung,studiengangbezeichnung_englisch,studiengangkurzbzlang,akadgrad_id,insertamum,insertvon,updateamum,updatevon,ext_id,status_kurzbz,standort_id) VALUES
 (501, 5,'1','WS2023',NULL,'BS5-WS2023',180.00,'Studiengang 5','Studiengang 5','STG5',NULL,'2023-08-24 14:25:41.728215','auto',NULL,NULL,NULL,'development',NULL);

INSERT INTO lehre.tbl_studienplan (studienplan_id, studienordnung_id,orgform_kurzbz,"version",bezeichnung,regelstudiendauer,sprache,aktiv,semesterwochen,testtool_sprachwahl,insertamum,insertvon,updateamum,updatevon,ext_id,ects_stpl,pflicht_sws,pflicht_lvs,onlinebewerbung_studienplan) VALUES
 (5011, 501,'VZ','1','BS5-WS2023-VZ',6,NULL,true,15,true,'2023-08-24 14:26:12.751416','auto',NULL,NULL,NULL,180.00,NULL,NULL,true);
 
INSERT INTO lehre.tbl_lehrveranstaltung (lehrveranstaltung_id, kurzbz,bezeichnung,studiengang_kz,semester,sprache,ects,semesterstunden,anmerkung,lehre,lehreverzeichnis,aktiv,planfaktor,planlektoren,planpersonalkosten,plankostenprolektor,updateamum,updatevon,insertamum,insertvon,ext_id,sort,zeugnis,koordinator,projektarbeit,lehrform_kurzbz,bezeichnung_english,orgform_kurzbz,incoming,lehrmodus_kurzbz,lehrtyp_kurzbz,oe_kurzbz,raumtyp_kurzbz,anzahlsemester,semesterwochen,lvnr,farbe,old_lehrfach_id,semester_alternativ,sws,lvs,alvs,lvps,las,benotung,lvinfo,lehrauftrag,lehrveranstaltung_template_id) VALUES
 (511, 'MOD1.1','Sport',5,1,'German',8.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Communication','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (512, 'MOD1.2','Mathematik',5,1,'German',8.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Mathematics','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (513, 'MOD1.3','Grundlagen',5,1,'German',8.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Basics','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (514, 'MOD1.4','Sprachen',5,1,'English',6.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Communication','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (521, 'MOD2.1','Labor',5,2,'German',10.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Laboratory','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (522, 'MOD2.2','Mathematik',5,2,'German',10.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Mathematics','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (523, 'MOD2.3','Sprachen',5,2,'English',10.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Communication','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (531, 'MOD3.1','Mathematik',5,3,'German',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Mathematics','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (532, 'MOD3.2','Elektrische Signale',5,3,'German',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Electric Signals','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (541, 'MOD4.1','Messtechnik',5,4,'German',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Measurement Technology','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (542, 'MOD4.2','Forschung',5,4,'German',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Research','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (551, 'MOD5.1','Communication',5,5,'English',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Communication','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (552, 'MOD5.2','Regelungstechnik',5,5,'German',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Control Engineering','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (561, 'MOD6.1','Bachelorarbeit',5,6,'German',30.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Bachelor Thesis','VZ',5,'regulaer','modul','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL)
 ;
 
  INSERT INTO lehre.tbl_lehrveranstaltung (lehrveranstaltung_id, kurzbz,bezeichnung,studiengang_kz,semester,sprache,ects,semesterstunden,anmerkung,lehre,lehreverzeichnis,aktiv,planfaktor,planlektoren,planpersonalkosten,plankostenprolektor,updateamum,updatevon,insertamum,insertvon,ext_id,sort,zeugnis,koordinator,projektarbeit,lehrform_kurzbz,bezeichnung_english,orgform_kurzbz,incoming,lehrmodus_kurzbz,lehrtyp_kurzbz,oe_kurzbz,raumtyp_kurzbz,anzahlsemester,semesterwochen,lvnr,farbe,old_lehrfach_id,semester_alternativ,sws,lvs,alvs,lvps,las,benotung,lvinfo,lehrauftrag,lehrveranstaltung_template_id) VALUES
 (5111, 'LS','Leistungssport',5,1,'German',8.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'VO','Communication','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5121, 'MAT','Mathematik 1',5,1,'German',8.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Mathematics','VZ',5,'regulaer','lv','kfMath',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5131, 'GL','Grundlagen der Programmierung',5,1,'German',8.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Development Basics','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5141, 'ENG','Englisch 1',5,1,'English',6.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'VO','Communication','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5211, 'EL','Elektronik Labor',5,2,'German',10.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'LAB','Laboratory','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5221, 'MAT','Mathematik 2',5,2,'German',10.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Mathematics','VZ',5,'regulaer','lv','kfMath',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5231, 'ENG','Communcation Englisch',5,2,'English',10.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'VO','Communication','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5311, 'MAT','Mathematik 3',5,3,'German',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Mathematics','VZ',5,'regulaer','lv','kfMath',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5321, 'ES','Elektrische Signale',5,3,'German',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Electric Signals','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5411, 'MT','Messtechnik',5,4,'German',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Measurement Technology','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5421, 'BAN','Bewegungsanalyse',5,4,'German',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Movement Analytics','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5511, 'ENG','Communication',5,5,'English',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'VO','Communication','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5521, 'RT','Regelungstechnik',5,5,'German',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Control Engineering','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (5611, 'BA','Bachelorarbeit',5,6,'German',30.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'SE','Bachelor Thesis','VZ',5,'regulaer','lv','stg5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL)
 ;
 
 INSERT INTO lehre.tbl_studienplan_lehrveranstaltung (studienplan_lehrveranstaltung_id, studienplan_id,lehrveranstaltung_id,semester,studienplan_lehrveranstaltung_id_parent,pflicht,koordinator,insertamum,insertvon,updateamum,updatevon,sort,ext_id,curriculum,export,genehmigung) VALUES
 -- MODULE im Studienplan
 (501, 5011,511,1,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (502, 5011,512,1,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (503, 5011,513,1,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (504, 5011,514,1,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (505, 5011,521,2,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (506, 5011,522,2,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (507, 5011,523,2,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (508, 5011,531,3,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (509, 5011,532,3,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (510, 5011,541,4,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (511, 5011,542,4,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (512, 5011,551,5,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (513, 5011,552,5,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (514, 5011,561,6,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),

 -- LVS zu Modulen
 (515, 5011,5111,1,501,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (516, 5011,5121,1,502,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (517, 5011,5131,1,503,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (518, 5011,5141,1,504,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (519, 5011,5211,2,505,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (520, 5011,5221,2,519,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (521, 5011,5231,2,507,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (522, 5011,5311,3,508,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (523, 5011,5321,3,509,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (524, 5011,5411,4,510,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (525, 5011,5421,4,511,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (526, 5011,5511,5,512,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (527, 5011,5521,5,513,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (528, 5011,5611,6,514,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true)
;
	 
INSERT INTO lehre.tbl_studienplan_semester (studienplan_id, studiensemester_kurzbz, semester) VALUES
(5011, 'WS2023', 1),
(5011, 'SS2024', 2),
(5011, 'WS2024', 1),
(5011, 'WS2024', 3),
(5011, 'SS2025', 2),
(5011, 'SS2025', 4),
(5011, 'WS2025', 1),
(5011, 'WS2025', 3),
(5011, 'WS2025', 5),
(5011, 'SS2026', 2),
(5011, 'SS2026', 4),
(5011, 'SS2026', 6);


INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(51001, 5221, COALESCE(CurrentSemester(), LastActiveSemester()), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5221, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5221, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51001, 5, 1, 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51001, 5, 1, 'B', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(51001, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(51002, 5121, COALESCE(CurrentSemester(), LastActiveSemester()), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5121, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5121, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51002, 5, 1, 'B', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(51002, 'demolektor2', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(51003, 5141, COALESCE(CurrentSemester(), LastActiveSemester()), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5141, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5141, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51003, 5, 1, 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(51003, 'demolektor3', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(51004, 5211, COALESCE(CurrentSemester(), LastActiveSemester()), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5211, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5211, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51004, 5, 1, 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(51004, 'demolektor4', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(51005, 5311, COALESCE(CurrentSemester(), LastActiveSemester()), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5311, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5311, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51005, 5, 1, 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(51005, 'demolektor4', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(51006, 5321, COALESCE(CurrentSemester(), LastActiveSemester()), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5321, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5321, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51006, 5, 1, 'C', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(51006, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

-- Stundenplan Eintraege
INSERT INTO lehre.tbl_stundenplandev
(stundenplandev_id, lehreinheit_id, unr, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, mitarbeiter_uid, ort_kurzbz, datum, stunde, titel, anmerkung, fix, updateamum, updatevon, insertamum, insertvon)
VALUES(510011, 51001, 51001, 5, 1, 'A', '', NULL, 'demolektor1', 'EG04', '2025-07-31', 7, NULL, NULL, false, '2025-07-31 13:50:49.419', 'demoadmin', '2025-07-31 13:50:49.419', NULL);
	 
INSERT INTO lehre.tbl_stundenplan
(stundenplan_id, lehreinheit_id, unr, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, mitarbeiter_uid, ort_kurzbz, datum, stunde, titel, anmerkung, fix, updateamum, updatevon, insertamum, insertvon)
VALUES(510011, 51001, 51001, 5, 1, 'A', '', NULL, 'demolektor1', 'EG04', '2025-07-31', 7, NULL, NULL, false, '2025-07-31 13:50:49.419', 'demoadmin', '2025-07-31 13:50:49.419', NULL);

INSERT INTO lehre.tbl_stundenplandev
(stundenplandev_id, lehreinheit_id, unr, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, mitarbeiter_uid, ort_kurzbz, datum, stunde, titel, anmerkung, fix, updateamum, updatevon, insertamum, insertvon)
VALUES(510021, 31002, 31002, 5, 1, 'B', '', NULL, 'demolektor1', 'EG04', '2025-08-01', 7, NULL, NULL, false, '2025-07-31 13:50:49.419', 'demoadmin', '2025-07-31 13:50:49.419', NULL);
	 
INSERT INTO lehre.tbl_stundenplan
(stundenplan_id, lehreinheit_id, unr, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, mitarbeiter_uid, ort_kurzbz, datum, stunde, titel, anmerkung, fix, updateamum, updatevon, insertamum, insertvon)
VALUES(510021, 31002, 31002, 5, 1, 'B', '', NULL, 'demolektor1', 'EG04', '2025-08-01', 7, NULL, NULL, false, '2025-07-31 13:50:49.419', 'demoadmin', '2025-07-31 13:50:49.419', NULL);

INSERT INTO public.tbl_studentlehrverband
(student_uid, studiensemester_kurzbz, studiengang_kz, semester, verband, gruppe, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES('s125b101', LastActiveSemester(), 5, 2, ' ', ' ', NULL, NULL, '2026-06-11 14:33:58.403', 'auto', NULL);

INSERT INTO public.tbl_gruppe (gruppe_kurzbz, studiengang_kz, semester, bezeichnung, beschreibung, sichtbar, lehre, aktiv, sort, mailgrp, generiert, updateamum, updatevon, insertamum, insertvon, ext_id, orgform_kurzbz, content_visible, gesperrt, direktinskription, zutrittssystem, aufnahmegruppe) VALUES
('GRP_51003', 5, 2, 'BS5 2 ENG', null, false, false, true, NULL, false, false, now(), 'demoadmin', now(), 'demoadmin', NULL, NULL, true, false, true, false, false),
('GRP_51004', 5, 2, 'BS5 2 EL', null, false, false, true, NULL, false, false, now(), 'demoadmin', now(), 'demoadmin', NULL, NULL, true, false, true, false, false)
;

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51003, 5, 2, NULL, NULL, 'GRP_51003', NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51004, 5, 2, NULL, NULL, 'GRP_51004', NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id, studiensemester_kurzbz) VALUES
('s125b101', 'GRP_51003', now(), 'demoadmin', now(), 'demoadmin', NULL, NULL),
('s125b101', 'GRP_51004', now(), 'demoadmin', now(), 'demoadmin', NULL, NULL)
;

-- ==== benotungstool_noten ====
-- Demo data for the Benotungstool / Gesamtnoteneingabe test suite.
--
-- campus.vw_student_lehrveranstaltung, the source of getStudentsByLv(), links students to
-- Lehreinheiten through tbl_lehreinheitgruppe.gruppe_kurzbz = tbl_benutzergruppe.gruppe_kurzbz AND
-- a matching Studiensemester.
--
-- RESULT: demolektor1 -> LV 5221 (12 students), demolektor2 -> LV 5121 (6, the foreign LV for the
-- access tests).

BEGIN;

-- Test semester: the running one, else the last started. Same rule as the suite.
CREATE TEMP TABLE sem AS
SELECT studiensemester_kurzbz AS kurzbz
  FROM public.tbl_studiensemester
 WHERE start <= now()
 ORDER BY (ende >= now()) DESC, start DESC
 LIMIT 1;

-- The two LV fixtures. Own Lehreinheiten (511xx) so the tempus fixtures 51001-51006 stay untouched.
-- members_up_to = highest person_id that joins this group.
CREATE TEMP TABLE lv AS
SELECT v.*, l.semester
  FROM (VALUES
          (true,  5221, 51101, 'GRP_CYNOTEN_H', 'Cypress Noten Haupt', 'demolektor1', 512),
          (false, 5121, 51102, 'GRP_CYNOTEN_F', 'Cypress Noten Fremd', 'demolektor2', 506)
       ) AS v(main, lv_id, le_id, grp, grp_text, lektor, members_up_to)
  JOIN lehre.tbl_lehrveranstaltung l ON l.lehrveranstaltung_id = v.lv_id;

-- The 12 students. person_id is the anchor for every derived key.
CREATE TEMP TABLE stud AS
SELECT id AS person_id, 5000 + id AS prestudent_id, 's525b' || id AS uid
  FROM generate_series(501, 512) AS g(id);

DO $$
BEGIN
	IF NOT EXISTS (SELECT 1 FROM sem) THEN
		RAISE EXCEPTION 'No Studiensemester found, the base dump is missing.';
	END IF;
	IF (SELECT count(*) FROM lv) <> 2 THEN
		RAISE EXCEPTION 'LV 5221/5121 missing, apply group studiengang_5.';
	END IF;
END $$;

-- Termin3 is absent from the base dump; without it every Termin3 insert hits a foreign key error.
INSERT INTO lehre.tbl_pruefungstyp (pruefungstyp_kurzbz, beschreibung)
VALUES ('Termin3', '3. Termin')
    ON CONFLICT DO NOTHING;

-- ------------------------------------------------------------------------------------- students
INSERT INTO public.tbl_person (person_id, vorname, nachname, gebdatum, geschlecht, aktiv)
SELECT person_id, 'Noten', 'Student ' || (person_id - 500), '2000-01-15', 'm', true
  FROM stud
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_prestudent (prestudent_id, person_id, studiengang_kz)
SELECT prestudent_id, person_id, 5
  FROM stud
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_prestudentstatus
       (prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester)
SELECT s.prestudent_id, 'Student', sem.kurzbz, lv.semester
  FROM stud s, sem, lv
 WHERE lv.main
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_benutzer (uid, person_id, aktiv)
SELECT uid, person_id, true
  FROM stud
    ON CONFLICT DO NOTHING;

-- matrikelnr is UNIQUE; scheme of the existing seeders is '251000' + studiengang_kz + person_id
INSERT INTO public.tbl_student
       (student_uid, matrikelnr, prestudent_id, studiengang_kz, semester, verband, gruppe)
SELECT s.uid, '2510005' || s.person_id, s.prestudent_id, 5, lv.semester, '', ''
  FROM stud s, lv
 WHERE lv.main
    ON CONFLICT DO NOTHING;

-- Lehrverband assignment; (5, <semester>, '', '') is created by group studiengang_5.
INSERT INTO public.tbl_studentlehrverband
       (student_uid, studiensemester_kurzbz, studiengang_kz, semester, verband, gruppe)
SELECT s.uid, sem.kurzbz, 5, lv.semester, '', ''
  FROM stud s, sem, lv
 WHERE lv.main
    ON CONFLICT DO NOTHING;

-- ------------------------------------------------------------------- Lehreinheiten and Lektoren
INSERT INTO lehre.tbl_lehreinheit
       (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrform_kurzbz,
        wochenrythmus, raumtyp, raumtypalternativ, sprache, lehrfach_id)
SELECT lv.le_id, lv.lv_id, sem.kurzbz, 'VO', 1, 'Dummy', 'Dummy', 'German', lv.lv_id
  FROM lv, sem
    ON CONFLICT DO NOTHING;

-- Fix the semester if the Lehreinheit is left over from a run in a different one.
UPDATE lehre.tbl_lehreinheit le
   SET studiensemester_kurzbz = sem.kurzbz
  FROM lv, sem
 WHERE le.lehreinheit_id = lv.le_id
   AND le.studiensemester_kurzbz IS DISTINCT FROM sem.kurzbz;

INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz)
SELECT le_id, lektor, 'Lektor'
  FROM lv
    ON CONFLICT DO NOTHING;

-- --------------------------------------------------------------------------------------- groups
INSERT INTO public.tbl_gruppe (gruppe_kurzbz, studiengang_kz, semester, bezeichnung, aktiv)
SELECT grp, 5, semester, grp_text, true
  FROM lv
    ON CONFLICT DO NOTHING;

-- The row the existing seeders lack: attach the group to the Lehreinheit with gruppe_kurzbz set.
-- The primary key is a serial, so NOT EXISTS instead of ON CONFLICT.
INSERT INTO lehre.tbl_lehreinheitgruppe (lehreinheit_id, studiengang_kz, semester, gruppe_kurzbz)
SELECT lv.le_id, 5, lv.semester, lv.grp
  FROM lv
 WHERE NOT EXISTS (SELECT 1 FROM lehre.tbl_lehreinheitgruppe g
                    WHERE g.lehreinheit_id = lv.le_id AND g.gruppe_kurzbz = lv.grp);

-- Membership WITH a semester. The primary key is (uid, gruppe_kurzbz) and excludes the semester,
-- so DO UPDATE repairs a row from an earlier run instead of duplicating it.
INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, studiensemester_kurzbz)
SELECT s.uid, lv.grp, sem.kurzbz
  FROM stud s, lv, sem
 WHERE s.person_id <= lv.members_up_to
    ON CONFLICT (uid, gruppe_kurzbz) DO UPDATE
       SET studiensemester_kurzbz = EXCLUDED.studiensemester_kurzbz;

-- -------------------------------------------------------------------------------- Notenschluessel
INSERT INTO lehre.tbl_notenschluessel (notenschluessel_kurzbz, bezeichnung)
VALUES ('CYNOTEN', 'Cypress Testnotenschluessel')
    ON CONFLICT DO NOTHING;

-- getNote() takes the row with the largest punkte value <= the points given. The 0 threshold makes
-- sure every value maps to a grade. Serial primary key, so NOT EXISTS.
INSERT INTO lehre.tbl_notenschluesselaufteilung (notenschluessel_kurzbz, note, punkte)
SELECT 'CYNOTEN', a.note, a.punkte
  FROM (VALUES (5, 0.0), (4, 51.0), (3, 64.0), (2, 77.0), (1, 90.0)) AS a(note, punkte)
 WHERE NOT EXISTS (SELECT 1 FROM lehre.tbl_notenschluesselaufteilung x
                    WHERE x.notenschluessel_kurzbz = 'CYNOTEN' AND x.punkte = a.punkte);

-- Set the semester explicitly: getKurzbzForLv() builds "... AND studiensemester_kurzbz = ? OR
-- studiensemester_kurzbz IS NULL" without parentheses, so a NULL-semester assignment would apply
-- to every Lehrveranstaltung through AND/OR precedence.
INSERT INTO lehre.tbl_notenschluesselzuordnung
       (notenschluessel_kurzbz, lehrveranstaltung_id, studiensemester_kurzbz)
SELECT 'CYNOTEN', lv.lv_id, sem.kurzbz
  FROM lv, sem
 WHERE lv.main
   AND NOT EXISTS (SELECT 1 FROM lehre.tbl_notenschluesselzuordnung z
                    WHERE z.notenschluessel_kurzbz = 'CYNOTEN'
                      AND z.lehrveranstaltung_id = lv.lv_id
                      AND z.studiensemester_kurzbz = sem.kurzbz);

COMMIT;

-- Fires if the view stays empty anyway, which is the bug this seeder exists to fix.
DO $$
DECLARE v_count integer;
BEGIN
	SELECT count(*) INTO v_count
	  FROM campus.vw_student_lehrveranstaltung v, sem
	 WHERE v.lehrveranstaltung_id = 5221 AND v.studiensemester_kurzbz = sem.kurzbz;

	IF v_count = 0 THEN
		RAISE EXCEPTION 'No students in LV 5221 (%), fixture unusable.', (SELECT kurzbz FROM sem);
	END IF;

	RAISE NOTICE 'OK: % students in LV 5221 (%)', v_count, (SELECT kurzbz FROM sem);
END $$;

SELECT (SELECT kurzbz FROM sem)                                              AS semester,
       (SELECT count(*) FROM campus.vw_student_lehrveranstaltung v, sem
         WHERE v.lehrveranstaltung_id = 5221 AND v.studiensemester_kurzbz = sem.kurzbz) AS lv5221,
       (SELECT count(*) FROM campus.vw_student_lehrveranstaltung v, sem
         WHERE v.lehrveranstaltung_id = 5121 AND v.studiensemester_kurzbz = sem.kurzbz) AS lv5121,
       (SELECT note FROM lehre.tbl_note WHERE bezeichnung = 'entschuldigt')  AS entschuldigt,
       (SELECT note FROM lehre.tbl_note WHERE bezeichnung = 'Noch nicht eingetragen') AS noch_nicht,
       (SELECT count(*) FROM lehre.tbl_notenschluesselaufteilung
         WHERE notenschluessel_kurzbz = 'CYNOTEN')                           AS notenschluessel;

-- ==== schema_grants ====
-- Vollzugriff auf ALLE Anwendungsschemata für alle DB-Rollen.

DO $$
DECLARE
	v_grantee  text;
	v_target   text;
	v_schema   text;
	v_grantees text[] := ARRAY['web', 'vilesci', 'wawi', 'admin', 'PUBLIC'];
	v_done     integer := 0;
BEGIN
	FOREACH v_grantee IN ARRAY v_grantees
	LOOP
		IF v_grantee = 'PUBLIC' THEN
			v_target := 'PUBLIC';
		ELSE
			CONTINUE WHEN NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = v_grantee);
			v_target := quote_ident(v_grantee);
		END IF;

		FOR v_schema IN
			SELECT nspname
			FROM pg_namespace
			WHERE nspname NOT LIKE 'pg\_%' AND nspname <> 'information_schema'
			ORDER BY nspname
		LOOP
			EXECUTE format('GRANT ALL ON SCHEMA %I TO %s', v_schema, v_target);
			EXECUTE format('GRANT ALL ON ALL TABLES IN SCHEMA %I TO %s', v_schema, v_target);
			EXECUTE format('GRANT ALL ON ALL SEQUENCES IN SCHEMA %I TO %s', v_schema, v_target);
			EXECUTE format('GRANT ALL ON ALL FUNCTIONS IN SCHEMA %I TO %s', v_schema, v_target);

			EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA %I GRANT ALL ON TABLES TO %s', v_schema, v_target);
			EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA %I GRANT ALL ON SEQUENCES TO %s', v_schema, v_target);
			EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA %I GRANT ALL ON FUNCTIONS TO %s', v_schema, v_target);

			v_done := v_done + 1;
		END LOOP;
	END LOOP;

	RAISE NOTICE 'Grants auf % Schema/Rollen-Kombinationen vergeben.', v_done;
END $$;

-- ==== benotungstool_berechtigungen ====
-- Benotungstool permissions for the demo accounts. Without them every endpoint answers 403.
--
--   demolektor1    teacher; no oe_kurzbz - assertLvAccess scopes him by the Lehreinheiten he teaches
--   demoassistenz  programme assistant for stg3 and stg5 (test courses 5221 and 5121 live in stg5)
--
-- Idempotent.

BEGIN;

DO $$
BEGIN
	IF NOT EXISTS (SELECT 1 FROM public.tbl_benutzer WHERE uid = 'demolektor1') THEN
		RAISE EXCEPTION 'Benutzer demolektor1 fehlt, die Basisdaten sind unvollstaendig.';
	END IF;
	IF NOT EXISTS (SELECT 1 FROM public.tbl_benutzer WHERE uid = 'demoassistenz') THEN
		RAISE EXCEPTION 'Benutzer demoassistenz fehlt, die Basisdaten sind unvollstaendig.';
	END IF;
END $$;

-- Der Lektor: darf das Werkzeug öffnen, sieht aber nur seine eigenen Lehrveranstaltungen.
INSERT INTO system.tbl_benutzerrolle (uid, berechtigung_kurzbz, art, oe_kurzbz, insertvon, insertamum)
SELECT 'demolektor1', 'lehre/benotungstool', 'suid', NULL, 'seeder', now()
 WHERE NOT EXISTS (
	SELECT 1 FROM system.tbl_benutzerrolle
	 WHERE uid = 'demolektor1'
	   AND berechtigung_kurzbz = 'lehre/benotungstool'
	   AND oe_kurzbz IS NULL
 );

-- Die Assistenz: darf die Lehrveranstaltungen ihrer Studiengänge benoten.
INSERT INTO system.tbl_benutzerrolle (uid, berechtigung_kurzbz, art, oe_kurzbz, insertvon, insertamum)
SELECT 'demoassistenz', 'lehre/benotungstool_assistenz', 'suid', oe.oe_kurzbz, 'seeder', now()
  FROM (VALUES ('stg3'), ('stg5')) AS oe(oe_kurzbz)
 WHERE NOT EXISTS (
	SELECT 1 FROM system.tbl_benutzerrolle b
	 WHERE b.uid = 'demoassistenz'
	   AND b.berechtigung_kurzbz = 'lehre/benotungstool_assistenz'
	   AND b.oe_kurzbz = oe.oe_kurzbz
 );

COMMIT;

-- Zurücknehmen:
--   DELETE FROM system.tbl_benutzerrolle
--    WHERE insertvon = 'seeder' AND berechtigung_kurzbz LIKE 'lehre/benotungstool%';

-- ==== benotungstool_fixture_erweitert ====
-- Extensions of the Benotungstool fixture. Apply after group benotungstool_noten.
--
--   LE 51103   a second Lehreinheit of LV 5221 with two Lektoren and two students of its own. The write
--              paths replace a foreign Lehreinheit, and the grader rules need a Lehreinheit with more
--              than one Lektor. demoadmin sorts before demolektor1, so "the caller" and "the first
--              Lektor" give different results.
--   LE 51104   demolektor1 in LV 5221 in the last Sommersemester with a passed grade entry deadline.
--              The deadline tests need a semester in which the user teaches.
--   Vorlagen   demo texts for 'Notenfreigabe' and 'Sancho_Mail_Template'. Without them every release
--              mail has an empty body. The dbupdate script creates only the tbl_vorlage row.
--
-- The new students sort after 'Student 1'..'Student 12', so the first twelve students of the suite
-- stay the same. Idempotent.

BEGIN;

-- group benotungstool_noten creates temp tables with the same names when both groups run in one session
DROP TABLE IF EXISTS pg_temp.sem, pg_temp.sem_frist, pg_temp.lv, pg_temp.stud;

-- Test semester: the running one, else the last started. Same rule as the suite and group benotungstool_noten.
CREATE TEMP TABLE sem AS
SELECT studiensemester_kurzbz AS kurzbz
  FROM public.tbl_studiensemester
 WHERE start <= now()
 ORDER BY (ende >= now()) DESC, start DESC
 LIMIT 1;

-- The last Sommersemester after its deadline. NOTENEINTRAGUNGSFRIST_SS defaults to 15 November.
CREATE TEMP TABLE sem_frist AS
SELECT studiensemester_kurzbz AS kurzbz
  FROM public.tbl_studiensemester
 WHERE studiensemester_kurzbz ~ '^SS[0-9]{4}$'
   AND make_date(substring(studiensemester_kurzbz FROM 3 FOR 4)::int, 11, 15) < current_date
 ORDER BY start DESC
 LIMIT 1;

CREATE TEMP TABLE lv AS
SELECT lehrveranstaltung_id AS lv_id, semester
  FROM lehre.tbl_lehrveranstaltung
 WHERE lehrveranstaltung_id = 5221;

CREATE TEMP TABLE stud AS
SELECT id AS person_id, 5000 + id AS prestudent_id, 's525b' || id AS uid, vorname
  FROM (VALUES (513, 'Eins'), (514, 'Zwei')) AS g(id, vorname);

DO $$
BEGIN
	IF NOT EXISTS (SELECT 1 FROM sem) THEN
		RAISE EXCEPTION 'No Studiensemester found, the base dump is missing.';
	END IF;
	IF NOT EXISTS (SELECT 1 FROM sem_frist) THEN
		RAISE EXCEPTION 'No Sommersemester with a passed grade entry deadline.';
	END IF;
	IF NOT EXISTS (SELECT 1 FROM lv) THEN
		RAISE EXCEPTION 'LV 5221 missing, apply group studiengang_5.';
	END IF;
	IF NOT EXISTS (SELECT 1 FROM lehre.tbl_lehreinheit WHERE lehreinheit_id = 51101) THEN
		RAISE EXCEPTION 'Lehreinheit 51101 missing, apply group benotungstool_noten first.';
	END IF;
	IF (SELECT count(*) FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid IN ('demoadmin', 'demolektor1')) <> 2 THEN
		RAISE EXCEPTION 'demoadmin or demolektor1 missing, apply BS002_mitarbeiter.sql.';
	END IF;
END $$;

-- ------------------------------------------------------------------------------------- students
INSERT INTO public.tbl_person (person_id, vorname, nachname, gebdatum, geschlecht, aktiv)
SELECT person_id, vorname, 'Zweitgruppe', '2000-01-15', 'm', true
  FROM stud
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_prestudent (prestudent_id, person_id, studiengang_kz)
SELECT prestudent_id, person_id, 5
  FROM stud
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_prestudentstatus
       (prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester)
SELECT s.prestudent_id, 'Student', sem.kurzbz, lv.semester
  FROM stud s, sem, lv
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_benutzer (uid, person_id, aktiv)
SELECT uid, person_id, true
  FROM stud
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_student
       (student_uid, matrikelnr, prestudent_id, studiengang_kz, semester, verband, gruppe)
SELECT s.uid, '2510005' || s.person_id, s.prestudent_id, 5, lv.semester, '', ''
  FROM stud s, lv
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_studentlehrverband
       (student_uid, studiensemester_kurzbz, studiengang_kz, semester, verband, gruppe)
SELECT s.uid, sem.kurzbz, 5, lv.semester, '', ''
  FROM stud s, sem, lv
    ON CONFLICT DO NOTHING;

-- ------------------------------------------------------------------------------ Lehreinheit 51103
INSERT INTO lehre.tbl_lehreinheit
       (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrform_kurzbz,
        wochenrythmus, raumtyp, raumtypalternativ, sprache, lehrfach_id)
SELECT 51103, lv.lv_id, sem.kurzbz, 'VO', 1, 'Dummy', 'Dummy', 'German', lv.lv_id
  FROM lv, sem
    ON CONFLICT DO NOTHING;

UPDATE lehre.tbl_lehreinheit le
   SET studiensemester_kurzbz = sem.kurzbz
  FROM sem
 WHERE le.lehreinheit_id = 51103
   AND le.studiensemester_kurzbz IS DISTINCT FROM sem.kurzbz;

INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz)
SELECT 51103, l.uid, 'Lektor'
  FROM (VALUES ('demoadmin'), ('demolektor1')) AS l(uid)
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_gruppe (gruppe_kurzbz, studiengang_kz, semester, bezeichnung, aktiv)
SELECT 'GRP_CYNOTEN_Z', 5, semester, 'Cypress Noten Zweitgruppe', true
  FROM lv
    ON CONFLICT DO NOTHING;

INSERT INTO lehre.tbl_lehreinheitgruppe (lehreinheit_id, studiengang_kz, semester, gruppe_kurzbz)
SELECT 51103, 5, lv.semester, 'GRP_CYNOTEN_Z'
  FROM lv
 WHERE NOT EXISTS (SELECT 1 FROM lehre.tbl_lehreinheitgruppe g
                    WHERE g.lehreinheit_id = 51103 AND g.gruppe_kurzbz = 'GRP_CYNOTEN_Z');

-- the students of 51103 are members of this group only, so each has exactly one Lehreinheit
INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, studiensemester_kurzbz)
SELECT s.uid, 'GRP_CYNOTEN_Z', sem.kurzbz
  FROM stud s, sem
    ON CONFLICT (uid, gruppe_kurzbz) DO UPDATE
       SET studiensemester_kurzbz = EXCLUDED.studiensemester_kurzbz;

-- ------------------------------------------------------------------------------ Lehreinheit 51104
-- getLvForLektorInSemester needs no group, only the Lehreinheit and its Lektor
INSERT INTO lehre.tbl_lehreinheit
       (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrform_kurzbz,
        wochenrythmus, raumtyp, raumtypalternativ, sprache, lehrfach_id)
SELECT 51104, lv.lv_id, sf.kurzbz, 'VO', 1, 'Dummy', 'Dummy', 'German', lv.lv_id
  FROM lv, sem_frist sf
    ON CONFLICT DO NOTHING;

UPDATE lehre.tbl_lehreinheit le
   SET studiensemester_kurzbz = sf.kurzbz
  FROM sem_frist sf
 WHERE le.lehreinheit_id = 51104
   AND le.studiensemester_kurzbz IS DISTINCT FROM sf.kurzbz;

INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz)
VALUES (51104, 'demolektor1', 'Lektor')
    ON CONFLICT DO NOTHING;

-- ---------------------------------------------------------------------------------------- Vorlagen
INSERT INTO public.tbl_vorlage (vorlage_kurzbz, bezeichnung, anmerkung, mimetype)
VALUES ('Notenfreigabe', 'Notenfreigabe', NULL, 'text/html'),
       ('Sancho_Mail_Template', 'Sancho Mail Template', 'Demo layout of the test instance', 'text/html')
    ON CONFLICT (vorlage_kurzbz) DO NOTHING;

-- Placeholders of Noten::sendFreigabeEmail: lektor, lvaname, neuenotencount, studlist, adressen.
-- An existing active text stays. Studiengang 0 and oe 'etw' are the root, as for the other Vorlagen.
INSERT INTO public.tbl_vorlagestudiengang
       (vorlage_kurzbz, studiengang_kz, version, text, oe_kurzbz, aktiv, insertamum, insertvon)
SELECT v.kurzbz, 0, 1, v.text, 'etw', true, now(), 'seeder'
  FROM (VALUES
          ('Notenfreigabe',
           '<p>{lektor} hat {neuenotencount} Note(n) freigegeben: {lvaname}</p>{studlist}<p>Empf&auml;nger: {adressen}</p>'),
          ('Sancho_Mail_Template', '<html><body>{content}</body></html>')
       ) AS v(kurzbz, text)
 WHERE NOT EXISTS (SELECT 1 FROM public.tbl_vorlagestudiengang x
                    WHERE x.vorlage_kurzbz = v.kurzbz AND x.aktiv);

COMMIT;

DO $$
BEGIN
	IF (SELECT count(*) FROM campus.vw_student_lehrveranstaltung v, sem
	     WHERE v.lehreinheit_id = 51103 AND v.studiensemester_kurzbz = sem.kurzbz) = 0 THEN
		RAISE EXCEPTION 'No students in Lehreinheit 51103 (%), fixture unusable.', (SELECT kurzbz FROM sem);
	END IF;
END $$;

SELECT (SELECT kurzbz FROM sem)                                               AS semester,
       (SELECT kurzbz FROM sem_frist)                                         AS semester_frist,
       (SELECT count(*) FROM campus.vw_student_lehrveranstaltung v, sem
         WHERE v.lehreinheit_id = 51103 AND v.studiensemester_kurzbz = sem.kurzbz) AS le51103_studenten,
       (SELECT count(*) FROM lehre.tbl_lehreinheitmitarbeiter
         WHERE lehreinheit_id = 51103)                                        AS le51103_lektoren,
       (SELECT count(*) FROM public.tbl_vorlagestudiengang
         WHERE vorlage_kurzbz IN ('Notenfreigabe', 'Sancho_Mail_Template') AND aktiv) AS vorlagen;
