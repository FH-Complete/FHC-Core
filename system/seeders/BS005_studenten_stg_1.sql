-- Demodaten fuer Studierende im Studiengang 1

INSERT INTO lehre.tbl_studienordnung
(studienordnung_id, studiengang_kz, "version", gueltigvon, gueltigbis, bezeichnung, ects, studiengangbezeichnung, studiengangbezeichnung_englisch, studiengangkurzbzlang, akadgrad_id, insertamum, insertvon, updateamum, updatevon, ext_id, status_kurzbz, standort_id)
VALUES(1, 1, '1', 'WS2023', NULL, 'BS1-WS2023', 180.00, 'Studiengang 1', 'Studiengang 1', 'STG1', NULL, '2023-08-24 14:25:41.728', 'oesi', NULL, NULL, NULL, 'development', NULL);

INSERT INTO lehre.tbl_studienplan
(studienplan_id, studienordnung_id, orgform_kurzbz, "version", bezeichnung, regelstudiendauer, sprache, aktiv, semesterwochen, testtool_sprachwahl, insertamum, insertvon, updateamum, updatevon, ext_id, ects_stpl, pflicht_sws, pflicht_lvs, onlinebewerbung_studienplan)
VALUES(1, 1, 'VZ', '1', 'BS1-WS2023-VZ', 6, NULL, true, 15, true, '2023-08-24 14:26:12.751', 'oesi', NULL, NULL, NULL, 120.00, NULL, NULL, true);

INSERT INTO lehre.tbl_studienplan_semester
( studienplan_id, studiensemester_kurzbz, semester)
VALUES(1, 'WS2025', 1);
INSERT INTO lehre.tbl_studienplan_semester
(studienplan_id, studiensemester_kurzbz, semester)
VALUES(1, 'SS2026', 1);

-- Personen
INSERT INTO public.tbl_person (person_id, staatsbuergerschaft,geburtsnation,sprache,anrede,titelpost,titelpre,vorname,nachname,vornamen,gebdatum,gebort,gebzeit,foto,anmerkung,homepage,svnr,ersatzkennzeichen,familienstand,geschlecht,anzahlkinder,aktiv,insertamum,insertvon,updateamum,updatevon,ext_id,bundesland_code,kompetenzen,kurzbeschreibung,zugangscode,foto_sperre,udf_values,bpk,matr_aktiv,matr_nr,zugangscode_timestamp,wahlname,unruly) VALUES
 
(101,'A','A',NULL,'Herr',NULL,NULL,'Leonardo','DiCaprio',NULL,'1974-11-11',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c101',false,NULL,NULL,false,NULL,NULL,NULL,false),
(102,'A','A',NULL,'Herr',NULL,NULL,'Robert','Downey Jr.',NULL,'1965-04-04',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c102',false,NULL,NULL,false,NULL,NULL,NULL,false),
(103,'A','A',NULL,'Herr',NULL,NULL,'Denzel','Washington',NULL,'1954-12-28',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c103',false,NULL,NULL,false,NULL,NULL,NULL,false),
(104,'A','A',NULL,'Herr',NULL,NULL,'Al','Pacino',NULL,'1940-04-25',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c104',false,NULL,NULL,false,NULL,NULL,NULL,false),
(105,'A','A',NULL,'Herr',NULL,NULL,'Morgan','Freeman',NULL,'1937-06-01',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c105',false,NULL,NULL,false,NULL,NULL,NULL,false),
(106,'A','A',NULL,'Herr',NULL,NULL,'Daniel','Day-Lewis',NULL,'1957-04-29',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c106',false,NULL,NULL,false,NULL,NULL,NULL,false),
(107,'A','A',NULL,'Herr',NULL,NULL,'Marlon','Brando',NULL,'1924-04-03',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c107',false,NULL,NULL,false,NULL,NULL,NULL,false),
(108,'A','A',NULL,'Herr',NULL,NULL,'Tom','Hanks',NULL,'1956-07-09',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c108',false,NULL,NULL,false,NULL,NULL,NULL,false),
(109,'A','A',NULL,'Herr',NULL,NULL,'Johnny','Depp',NULL,'1963-06-09',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c109',false,NULL,NULL,false,NULL,NULL,NULL,false),
(110,'A','A',NULL,'Herr',NULL,NULL,'Brad','Pitt',NULL,'1963-12-18',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c8110',false,NULL,NULL,false,NULL,NULL,NULL,false),
(111,'A','A',NULL,'Herr',NULL,NULL,'Will','Smith',NULL,'1968-09-25',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c111',false,NULL,NULL,false,NULL,NULL,NULL,false),
(112,'A','A',NULL,'Herr',NULL,NULL,'Keanu','Reeves',NULL,'1964-09-02',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c112',false,NULL,NULL,false,NULL,NULL,NULL,false),
(113,'A','A',NULL,'Herr',NULL,NULL,'Robert','De Niro',NULL,'1943-08-17',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647113',false,NULL,NULL,false,NULL,NULL,NULL,false),
(114,'A','A',NULL,'Herr',NULL,NULL,'Harrison','Ford',NULL,'1942-07-13',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c114',false,NULL,NULL,false,NULL,NULL,NULL,false),
(115,'A','A',NULL,'Herr',NULL,NULL,'Samuel L.','Jackson',NULL,'1948-12-21',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c115',false,NULL,NULL,false,NULL,NULL,NULL,false),
(116,'A','A',NULL,'Herr',NULL,NULL,'Christian','Bale',NULL,'1974-01-30',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c116',false,NULL,NULL,false,NULL,NULL,NULL,false),
(117,'A','A',NULL,'Herr',NULL,NULL,'Tom','Cruise',NULL,'1962-07-03',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c1117',false,NULL,NULL,false,NULL,NULL,NULL,false),
(118,'A','A',NULL,'Herr',NULL,NULL,'Arnold','Schwarzenegger',NULL,'1947-07-30',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c118',false,NULL,NULL,false,NULL,NULL,NULL,false),
(119,'A','A',NULL,'Herr',NULL,NULL,'Sylvester','Stallone',NULL,'1946-07-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c119',false,NULL,NULL,false,NULL,NULL,NULL,false),
(120,'A','A',NULL,'Herr',NULL,NULL,'Karl','Klammer',NULL,'1963-06-09',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c120',false,NULL,NULL,false,NULL,NULL,NULL,false)
;
SELECT setval('public.tbl_person_person_id_seq', 1000,true);

-- Prestudenten
INSERT INTO public.tbl_prestudent (prestudent_id, person_id,studiengang_kz,aufmerksamdurch_kurzbz,berufstaetigkeit_code,ausbildungcode,zgv_code,zgvort,zgvdatum,zgvmas_code,zgvmaort,zgvmadatum,aufnahmeschluessel,facheinschlberuf,reihungstest_id,anmeldungreihungstest,reihungstestangetreten,rt_gesamtpunkte,bismelden,insertamum,insertvon,updateamum,updatevon,ext_id,anmerkung,dual,rt_punkte1,rt_punkte2,ausstellungsstaat,rt_punkte3,udf_values,priorisierung,zgvdoktor_code,zgvdoktorort,zgvdoktordatum,mentor,zgvnation,zgvmanation,zgvdoktornation,gsstudientyp_kurzbz,aufnahmegruppe_kurzbz,foerderrelevant,standort_code,zgv_erfuellt,zgvmas_erfuellt,zgvdoktor_erfuellt) VALUES
	 (1101,101,1,'k.A.',NULL,NULL,9,NULL,'2020-06-01',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1102,102,1,'k.A.',NULL,NULL,9,NULL,'2020-06-02',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1103,103,1,'k.A.',NULL,NULL,9,NULL,'2020-06-03',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1104,104,1,'k.A.',NULL,NULL,9,NULL,'2020-06-04',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1105,105,1,'k.A.',NULL,NULL,9,NULL,'2020-06-05',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1106,106,1,'k.A.',NULL,NULL,9,NULL,'2020-06-06',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1107,107,1,'k.A.',NULL,NULL,9,NULL,'2020-06-07',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1108,108,1,'k.A.',NULL,NULL,9,NULL,'2020-06-08',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1109,109,1,'k.A.',NULL,NULL,9,NULL,'2020-06-09',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1110,110,1,'k.A.',NULL,NULL,9,NULL,'2020-06-10',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1111,111,1,'k.A.',NULL,NULL,9,NULL,'2020-06-01',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1112,112,1,'k.A.',NULL,NULL,9,NULL,'2020-06-02',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1113,113,1,'k.A.',NULL,NULL,9,NULL,'2020-06-03',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1114,114,1,'k.A.',NULL,NULL,9,NULL,'2020-06-04',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1115,115,1,'k.A.',NULL,NULL,9,NULL,'2020-06-05',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1116,116,1,'k.A.',NULL,NULL,9,NULL,'2020-06-06',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1117,117,1,'k.A.',NULL,NULL,9,NULL,'2020-06-07',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1118,118,1,'k.A.',NULL,NULL,9,NULL,'2020-06-08',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1119,119,1,'k.A.',NULL,NULL,9,NULL,'2020-06-09',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false),
 	 (1120,120,1,'k.A.',NULL,NULL,9,NULL,'2020-06-10',NULL,NULL,NULL,NULL,false,NULL,NULL,true,NULL,true,NULL,NULL,'2023-08-24 14:56:04.509813',NULL,NULL,NULL,false,NULL,NULL,'A',0.0000,NULL,NULL,NULL,NULL,NULL,NULL,'A',NULL,NULL,'Intern',NULL,NULL,NULL,false,false,false)
;
SELECT setval('tbl_prestudent_prestudent_id_seq', 2000,true);

-- Prestudent Status

WITH status AS (
    SELECT * FROM (VALUES
        ('Interessent','WS2025',1,'2025-01-15','2023-08-24 14:56:04','auto',NULL,NULL,NULL,1,'2023-08-24','demoadmin',NULL,false,NULL,NULL,NULL,NULL),
	('Bewerber','WS2025',1,'2025-02-20','2023-08-24 14:58:11','auto',NULL,NULL,NULL,1,'2023-08-24','demoadmin',NULL,false,NULL,NULL,NULL,NULL),
	('Aufgenommener','WS2025',1,'2025-05-01','2023-08-24 14:58:14','auto',NULL,NULL,NULL,1,'2023-08-24','demoadmin',NULL,false,NULL,NULL,NULL,NULL),
	('Student','WS2025',1,'2025-08-24','2023-08-24 14:58:16','auto',NULL,NULL,NULL,1,'2023-08-24','demoadmin',NULL,false,NULL,NULL,NULL,NULL)
    ) AS t(status_kurzbz,studiensemester_kurzbz,ausbildungssemester,datum,insertamum,insertvon,updateamum,updatevon,orgform_kurzbz,studienplan_id,bestaetigtam,bestaetigtvon,fgm,faktiv,anmerkung,bewerbung_abgeschicktamum,rt_stufe,statusgrund_id)
),
inserts AS (
    SELECT generate_series(1101, 1120) AS prestudent_id,*
    FROM status
)
INSERT INTO public.tbl_prestudentstatus (prestudent_id, status_kurzbz,studiensemester_kurzbz,ausbildungssemester,datum,insertamum,insertvon,updateamum,updatevon,orgform_kurzbz,studienplan_id,bestaetigtam,bestaetigtvon,fgm,faktiv,anmerkung,bewerbung_abgeschicktamum,rt_stufe,statusgrund_id)
SELECT prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester, datum::date, insertamum::timestamp, insertvon, updateamum::timestamp, updatevon,orgform_kurzbz,studienplan_id,bestaetigtam::date,bestaetigtvon,fgm::smallint,faktiv,anmerkung,bewerbung_abgeschicktamum::timestamp,rt_stufe::smallint,statusgrund_id::int
FROM inserts;

-- tbl_benutzer
WITH persons AS (
    SELECT generate_series(101, 120) AS person_id
)
INSERT INTO public.tbl_benutzer (person_id, uid, aktiv, alias, insertamum, insertvon, updateamum, updatevon, updateaktivam, updateaktivvon, aktivierungscode)
SELECT person_id, 's125b' || person_id, true, null, now(), 'auto', null, null, null, false, null
FROM persons;

-- tbl_student
WITH persons AS (
    SELECT generate_series(101, 120) AS person_id
)
INSERT INTO public.tbl_student (student_uid, matrikelnr, prestudent_id, studiengang_kz, semester, verband, gruppe, updateamum, updatevon, insertamum, insertvon)
SELECT 's125b' || person_id, '2510001' || person_id, ('1' || person_id)::integer, '1', '1', '','', null, null, now(), 'auto'
FROM persons;

-- tbl_studentlehrverband
WITH persons AS (
    SELECT generate_series(101, 120) AS person_id
),
students as (
    SELECT ('s125b' || person_id) as student_uid, person_id
    FROM persons
)
INSERT INTO public.tbl_studentlehrverband (student_uid, studiensemester_kurzbz, studiengang_kz, semester, verband, gruppe, updateamum, updatevon, insertamum, insertvon)
SELECT student_uid, 'WS2025', '1','1','','', null, null, now(), 'auto'
FROM students;

