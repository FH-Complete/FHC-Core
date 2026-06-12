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
VALUES(51001, 5221, CurrentSemester(), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5221, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5221, 1);

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
VALUES(51002, 5121, CurrentSemester(), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5121, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5121, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51002, 5, 1, 'B', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(51002, 'demolektor2', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(51003, 5141, CurrentSemester(), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5141, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5141, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51003, 5, 1, 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(51003, 'demolektor3', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(51004, 5211, CurrentSemester(), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5211, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5211, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51004, 5, 1, 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(51004, 'demolektor4', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(51005, 5311, CurrentSemester(), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5311, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5311, 1);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(51005, 5, 1, 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'oesi', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(51005, 'demolektor4', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'oesi', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht)
VALUES(51006, 5321, CurrentSemester(), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, NULL, 5321, NULL, '2023-08-24 14:54:43.000', 'oesi', '2023-08-24 14:54:43.000', 'oesi', NULL, 5321, 1);

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
VALUES('s125b101', 'SS2026', 5, 2, ' ', ' ', NULL, NULL, '2026-06-11 14:33:58.403', 'auto', NULL);

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
 