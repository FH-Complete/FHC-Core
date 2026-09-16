-- Demodaten fuer Mitarbeiter
-- Personen
INSERT INTO public.tbl_person (person_id, staatsbuergerschaft,geburtsnation,sprache,anrede,titelpost,titelpre,vorname,nachname,vornamen,gebdatum,gebort,gebzeit,foto,anmerkung,homepage,svnr,ersatzkennzeichen,familienstand,geschlecht,anzahlkinder,aktiv,insertamum,insertvon,updateamum,updatevon,ext_id,bundesland_code,kompetenzen,kurzbeschreibung,zugangscode,foto_sperre,udf_values,bpk,matr_aktiv,matr_nr,zugangscode_timestamp,wahlname,unruly) VALUES
(21,'A','A',NULL,'Herr',NULL,NULL,'Demo','Lektor 1',NULL,'1980-01-15',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c21',false,NULL,NULL,false,NULL,NULL,NULL,false),
(22,'A','A',NULL,'Herr',NULL,NULL,'Demo','Lektor 2',NULL,'1980-01-15',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c22',false,NULL,NULL,false,NULL,NULL,NULL,false),
(23,'A','A',NULL,'Herr',NULL,NULL,'Demo','Lektor 3',NULL,'1980-01-15',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c23',false,NULL,NULL,false,NULL,NULL,NULL,false),
(24,'A','A',NULL,'Herr',NULL,NULL,'Demo','Lektor 4',NULL,'1980-01-15',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c24',false,NULL,NULL,false,NULL,NULL,NULL,false),
(25,'A','A',NULL,'Herr',NULL,NULL,'Demo','Lektor 5',NULL,'1980-01-15',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'m',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c25',false,NULL,NULL,false,NULL,NULL,NULL,false),
(26,'A','A',NULL,'Frau',NULL,NULL,'Demo','Assistenz',NULL,'1985-01-15',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'w',NULL,true,'2023-08-24 14:56:04.509813','auto','2025-05-13 10:57:19.826386',NULL,NULL,NULL,NULL,NULL,'64e753647c26',false,NULL,NULL,false,NULL,NULL,NULL,false)
;


INSERT INTO public.tbl_benutzer (person_id, uid, aktiv, alias, insertamum, insertvon, updateamum, updatevon, updateaktivam, updateaktivvon, aktivierungscode) VALUES 
('21', 'demolektor1' , true, null, now(), 'auto', null, null, null, false, null),
('22', 'demolektor2' , true, null, now(), 'auto', null, null, null, false, null),
('23', 'demolektor3' , true, null, now(), 'auto', null, null, null, false, null),
('24', 'demolektor4' , true, null, now(), 'auto', null, null, null, false, null),
('25', 'demolektor5' , true, null, now(), 'auto', null, null, null, false, null),
('26', 'demoassistenz' , true, null, now(), 'auto', null, null, null, false, null)
;

INSERT INTO public.tbl_mitarbeiter (mitarbeiter_uid, personalnummer, telefonklappe, kurzbz, lektor, fixangestellt, stundensatz, ausbildungcode, ort_kurzbz, insertamum, insertvon, bismelden) VALUES 
('demolektor1', 21, null, 'DemoLKT1', true, true, 80, null, null, now(), 'auto', true),
('demolektor2', 22, null, 'DemoLKT2', true, true, 80, null, null, now(), 'auto', true),
('demolektor3', 23, null, 'DemoLKT3', true, true, 80, null, null, now(), 'auto', true),
('demolektor4', 24, null, 'DemoLKT4', true, true, 80, null, null, now(), 'auto', true),
('demolektor5', 25, null, 'DemoLKT5', true, true, 80, null, null, now(), 'auto', true),
('demoassistenz', 26, null, 'DemoAss', true, true, 80, null, null, now(), 'auto', true)
;


INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id, studiensemester_kurzbz) VALUES
('demoadmin', 'MA', now(), 'demoadmin', now(), 'demoadmin', NULL, NULL),
('demolektor1', 'MA', now(), 'demoadmin', now(), 'demoadmin', NULL, NULL),
('demolektor2', 'MA', now(), 'demoadmin', now(), 'demoadmin', NULL, NULL),
('demolektor3', 'MA', now(), 'demoadmin', now(), 'demoadmin', NULL, NULL),
('demolektor4', 'MA', now(), 'demoadmin', now(), 'demoadmin', NULL, NULL),
('demolektor5', 'MA', now(), 'demoadmin', now(), 'demoadmin', NULL, NULL),
('demoassistenz', 'MA', now(), 'demoadmin', now(), 'demoadmin', NULL, NULL)
;
