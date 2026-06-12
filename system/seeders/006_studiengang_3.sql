-- Demodaten fuer Studiengang 3: Organisationseinheit, Studienordnung, Studiengang, Lehrveranstaltungen

INSERT INTO public.tbl_organisationseinheit (oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz, aktiv, mailverteiler, freigabegrenze, kurzzeichen, lehre, standort, warn_semesterstunden_frei, warn_semesterstunden_fix, standort_id)
VALUES('stg3', 'studiengaenge', 'Studiengang 3', 'Studiengang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL);

INSERT INTO public.tbl_studiengang (studiengang_kz,kurzbz,kurzbzlang,typ,bezeichnung,english,farbe,email,telefon,max_semester,max_verband,max_gruppe,erhalter_kz,bescheid,bescheidbgbl1,bescheidbgbl2,bescheidgz,bescheidvom,titelbescheidvom,aktiv,ext_id,orgform_kurzbz,zusatzinfo_html,moodle,oe_kurzbz,sprache,testtool_sprachwahl,studienplaetze,lgartcode,mischform,projektarbeit_note_anzeige,melderelevant,foerderrelevant,standort_code,onlinebewerbung,melde_studiengang_kz) VALUES
 (3,'S3','STG3','b','Studiengang 3','Studiengang 3',NULL,'invalid@example.com',NULL,6,'B','2',5,NULL,NULL,NULL,NULL,NULL,NULL,true,NULL,'VZ','',true,'stg3','German',true,NULL,NULL,false,true,true,true,NULL,false,'0002')
;

INSERT INTO "system".tbl_benutzerrolle
(rolle_kurzbz, berechtigung_kurzbz, uid, funktion_kurzbz, oe_kurzbz, art, studiensemester_kurzbz, "start", ende, negativ, updateamum, updatevon, insertamum, insertvon, kostenstelle_id, anmerkung)
VALUES('assistenz', NULL, 'demoassistenz', NULL, 'stg3', 'suid', NULL, '2025-09-18', NULL, false, NULL, NULL, '2025-09-18 15:46:02.000', 'demoadmin', NULL, NULL);

INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, ext_id, orgform_kurzbz) VALUES
 (3, 1, '','', true, null, null, null),
 (3, 1, 'A','', true, null, null, null),
 (3, 1, 'B','', true, null, null, null),
 (3, 2, '','', true, null, null, null),
 (3, 2, 'A','', true, null, null, null),
 (3, 2, 'B','', true, null, null, null),
 (3, 3, '','', true, null, null, null),
 (3, 3, 'A','', true, null, null, null),
 (3, 3, 'B','', true, null, null, null),
 (3, 4, '','', true, null, null, null),
 (3, 4, 'A','', true, null, null, null),
 (3, 4, 'B','', true, null, null, null),
 (3, 5, '','', true, null, null, null),
 (3, 5, 'A','', true, null, null, null),
 (3, 5, 'B','', true, null, null, null),
 (3, 6, '','', true, null, null, null),
 (3, 6, 'A','', true, null, null, null),
 (3, 6, 'B','', true, null, null, null)
;

INSERT INTO lehre.tbl_studienordnung (studienordnung_id, studiengang_kz,"version",gueltigvon,gueltigbis,bezeichnung,ects,studiengangbezeichnung,studiengangbezeichnung_englisch,studiengangkurzbzlang,akadgrad_id,insertamum,insertvon,updateamum,updatevon,ext_id,status_kurzbz,standort_id) VALUES
 (301, 3,'1','WS2023',NULL,'BS3-WS2023',180.00,'Studiengang 3','Studiengang 3','STG3',NULL,'2023-08-24 14:25:41.728215','auto',NULL,NULL,NULL,'development',NULL);

INSERT INTO lehre.tbl_studienplan (studienplan_id, studienordnung_id,orgform_kurzbz,"version",bezeichnung,regelstudiendauer,sprache,aktiv,semesterwochen,testtool_sprachwahl,insertamum,insertvon,updateamum,updatevon,ext_id,ects_stpl,pflicht_sws,pflicht_lvs,onlinebewerbung_studienplan) VALUES
 (3011, 301,'VZ','1','BS3-WS2023-VZ',6,NULL,true,15,true,'2023-08-24 14:26:12.751416','auto',NULL,NULL,NULL,180.00,NULL,NULL,true);
 
INSERT INTO lehre.tbl_lehrveranstaltung (lehrveranstaltung_id, kurzbz,bezeichnung,studiengang_kz,semester,sprache,ects,semesterstunden,anmerkung,lehre,lehreverzeichnis,aktiv,planfaktor,planlektoren,planpersonalkosten,plankostenprolektor,updateamum,updatevon,insertamum,insertvon,ext_id,sort,zeugnis,koordinator,projektarbeit,lehrform_kurzbz,bezeichnung_english,orgform_kurzbz,incoming,lehrmodus_kurzbz,lehrtyp_kurzbz,oe_kurzbz,raumtyp_kurzbz,anzahlsemester,semesterwochen,lvnr,farbe,old_lehrfach_id,semester_alternativ,sws,lvs,alvs,lvps,las,benotung,lvinfo,lehrauftrag,lehrveranstaltung_template_id) VALUES
 (311, 'MOD1.1','Sport',3,1,'German',8.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Communication','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (312, 'MOD1.2','Mathematik',3,1,'German',8.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Mathematics','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (313, 'MOD1.3','Grundlagen',3,1,'German',8.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Basics','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (314, 'MOD1.4','Sprachen',3,1,'English',6.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Communication','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (321, 'MOD2.1','Labor',3,2,'German',10.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Laboratory','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (322, 'MOD2.2','Mathematik',3,2,'German',10.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Mathematics','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (323, 'MOD2.3','Sprachen',3,2,'English',10.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Communication','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (331, 'MOD3.1','Mathematik',3,3,'German',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Mathematics','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (332, 'MOD3.2','Elektrische Signale',3,3,'German',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Electric Signals','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (341, 'MOD4.1','Messtechnik',3,4,'German',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Measurement Technology','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (342, 'MOD4.2','Forschung',3,4,'German',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Research','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (351, 'MOD5.1','Communication',3,5,'English',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Communication','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (352, 'MOD5.2','Regelungstechnik',3,5,'German',15.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Control Engineering','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (361, 'MOD6.1','Bachelorarbeit',3,6,'German',30.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'MOD','Bachelor Thesis','VZ',5,'regulaer','modul','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL)
 ;
 
  INSERT INTO lehre.tbl_lehrveranstaltung (lehrveranstaltung_id, kurzbz,bezeichnung,studiengang_kz,semester,sprache,ects,semesterstunden,anmerkung,lehre,lehreverzeichnis,aktiv,planfaktor,planlektoren,planpersonalkosten,plankostenprolektor,updateamum,updatevon,insertamum,insertvon,ext_id,sort,zeugnis,koordinator,projektarbeit,lehrform_kurzbz,bezeichnung_english,orgform_kurzbz,incoming,lehrmodus_kurzbz,lehrtyp_kurzbz,oe_kurzbz,raumtyp_kurzbz,anzahlsemester,semesterwochen,lvnr,farbe,old_lehrfach_id,semester_alternativ,sws,lvs,alvs,lvps,las,benotung,lvinfo,lehrauftrag,lehrveranstaltung_template_id) VALUES
 (3111, 'LS','Leistungssport',3,1,'German',8.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'VO','Communication','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3121, 'MAT','Mathematik 1',3,1,'German',8.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Mathematics','VZ',5,'regulaer','lv','kfMath',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3131, 'GL','Grundlagen der Programmierung',3,1,'German',8.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Development Basics','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3141, 'ENG','Englisch 1',3,1,'English',6.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'VO','Communication','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3211, 'EL','Elektronik Labor',3,2,'German',10.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'LAB','Laboratory','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3221, 'MAT','Mathematik 2',3,2,'German',10.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Mathematics','VZ',5,'regulaer','lv','kfMath',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3231, 'ENG','Communcation Englisch',3,2,'English',10.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'VO','Communication','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3311, 'MAT','Mathematik 3',3,3,'German',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Mathematics','VZ',5,'regulaer','lv','kfMath',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3321, 'ES','Elektrische Signale',3,3,'German',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Electric Signals','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3411, 'MT','Messtechnik',3,4,'German',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Measurement Technology','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3421, 'BAN','Bewegungsanalyse',3,4,'German',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Movement Analytics','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3511, 'ENG','Communication',3,5,'English',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'VO','Communication','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3521, 'RT','Regelungstechnik',3,5,'German',15.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'ILV','Control Engineering','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (3611, 'BA','Bachelorarbeit',3,6,'German',30.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,'2023-08-24 14:28:39','oesi','2023-08-24 14:28:39','oesi',NULL,NULL,true,NULL,false,'SE','Bachelor Thesis','VZ',5,'regulaer','lv','stg3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL)
 ;
 
 INSERT INTO lehre.tbl_studienplan_lehrveranstaltung (studienplan_lehrveranstaltung_id, studienplan_id,lehrveranstaltung_id,semester,studienplan_lehrveranstaltung_id_parent,pflicht,koordinator,insertamum,insertvon,updateamum,updatevon,sort,ext_id,curriculum,export,genehmigung) VALUES
 -- MODULE im Studienplan
 (301, 3011,311,1,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (302, 3011,312,1,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (303, 3011,313,1,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (304, 3011,314,1,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (305, 3011,321,2,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (306, 3011,322,2,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (307, 3011,323,2,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (308, 3011,331,3,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (309, 3011,332,3,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (310, 3011,341,4,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (311, 3011,342,4,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (312, 3011,351,5,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (313, 3011,352,5,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (314, 3011,361,6,NULL,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),

 -- LVS zu Modulen
 (315, 3011,3111,1,301,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (316, 3011,3121,1,302,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (317, 3011,3131,1,303,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (318, 3011,3141,1,304,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (319, 3011,3211,2,305,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (320, 3011,3221,2,306,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (321, 3011,3231,2,307,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (322, 3011,3311,3,308,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (323, 3011,3321,3,309,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (324, 3011,3411,4,310,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (325, 3011,3421,4,311,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (326, 3011,3511,5,312,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (327, 3011,3521,5,313,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true),
 (328, 3011,3611,6,314,true,NULL,'2023-08-24 14:29:03.506117','oesi',NULL,NULL,NULL,NULL,true,true,true)
;
	 
INSERT INTO lehre.tbl_studienplan_semester (studienplan_id, studiensemester_kurzbz, semester) VALUES
(3011, 'WS2023', 1),
(3011, 'SS2024', 2),
(3011, 'WS2024', 1),
(3011, 'WS2024', 3),
(3011, 'SS2025', 2),
(3011, 'SS2025', 4),
(3011, 'WS2025', 1),
(3011, 'WS2025', 3),
(3011, 'WS2025', 5),
(3011, 'SS2026', 2),
(3011, 'SS2026', 4),
(3011, 'SS2026', 6);


INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(31001, 3131, 'WS2025', NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 3131, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 3131, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(31001, 3, 1, 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(31001, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(31002, 3131, 'WS2025', NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 3131, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 3131, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(31002, 3, 1, 'B', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(31002, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

-- Stundenplan Eintraege
INSERT INTO lehre.tbl_stundenplandev
(stundenplandev_id, lehreinheit_id, unr, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, mitarbeiter_uid, ort_kurzbz, datum, stunde, titel, anmerkung, fix, updateamum, updatevon, insertamum, insertvon)
VALUES(310011, 31001, 31001, 3, 1, 'A', '', NULL, 'demolektor1', 'EG04', '2025-07-31', 7, NULL, NULL, false, '2025-07-31 13:50:49.419', 'demoadmin', '2025-07-31 13:50:49.419', NULL);
	 
INSERT INTO lehre.tbl_stundenplan
(stundenplan_id, lehreinheit_id, unr, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, mitarbeiter_uid, ort_kurzbz, datum, stunde, titel, anmerkung, fix, updateamum, updatevon, insertamum, insertvon)
VALUES(310011, 31001, 31001, 3, 1, 'A', '', NULL, 'demolektor1', 'EG04', '2025-07-31', 7, NULL, NULL, false, '2025-07-31 13:50:49.419', 'demoadmin', '2025-07-31 13:50:49.419', NULL);

INSERT INTO lehre.tbl_stundenplandev
(stundenplandev_id, lehreinheit_id, unr, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, mitarbeiter_uid, ort_kurzbz, datum, stunde, titel, anmerkung, fix, updateamum, updatevon, insertamum, insertvon)
VALUES(310021, 31002, 31002, 3, 1, 'B', '', NULL, 'demolektor1', 'EG04', '2025-08-01', 7, NULL, NULL, false, '2025-07-31 13:50:49.419', 'demoadmin', '2025-07-31 13:50:49.419', NULL);
	 
INSERT INTO lehre.tbl_stundenplan
(stundenplan_id, lehreinheit_id, unr, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, mitarbeiter_uid, ort_kurzbz, datum, stunde, titel, anmerkung, fix, updateamum, updatevon, insertamum, insertvon)
VALUES(310021, 31002, 31002, 3, 1, 'B', '', NULL, 'demolektor1', 'EG04', '2025-08-01', 7, NULL, NULL, false, '2025-07-31 13:50:49.419', 'demoadmin', '2025-07-31 13:50:49.419', NULL);
