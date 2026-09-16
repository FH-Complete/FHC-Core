-- Demodaten fuer Studiengang 4: Organisationseinheit, Studienordnung, Studiengang, Lehrveranstaltungen, Lehreinheiten 
-- Testdaten fuer LV-Verwaltung, Tempus

INSERT INTO public.tbl_organisationseinheit (oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz, aktiv, mailverteiler, freigabegrenze, kurzzeichen, lehre, standort, warn_semesterstunden_frei, warn_semesterstunden_fix, standort_id)
VALUES('stg4', 'studiengaenge', 'Tempus Examples', 'Studiengang', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL);

INSERT INTO public.tbl_studiengang (studiengang_kz,kurzbz,kurzbzlang,typ,bezeichnung,english,farbe,email,telefon,max_semester,max_verband,max_gruppe,erhalter_kz,bescheid,bescheidbgbl1,bescheidbgbl2,bescheidgz,bescheidvom,titelbescheidvom,aktiv,ext_id,orgform_kurzbz,zusatzinfo_html,moodle,oe_kurzbz,sprache,testtool_sprachwahl,studienplaetze,lgartcode,mischform,projektarbeit_note_anzeige,melderelevant,foerderrelevant,standort_code,onlinebewerbung,melde_studiengang_kz) VALUES
 (4,'S4','STG4','b','Tempus Beispiele','Tempus Examples',NULL,'invalid@example.com',NULL,6,'B','2',5,NULL,NULL,NULL,NULL,NULL,NULL,true,NULL,'VZ','',true,'stg4','German',true,NULL,NULL,false,true,true,true,NULL,false,'0004')
;

INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, ext_id, orgform_kurzbz) VALUES
 (4, 1, '','', true, null, null, null),
 (4, 1, 'A','', true, null, null, null),
 (4, 1, 'B','', true, null, null, null),
 (4, 2, '','', true, null, null, null),
 (4, 2, 'A','', true, null, null, null),
 (4, 2, 'B','', true, null, null, null),
 (4, 3, '','', true, null, null, null),
 (4, 3, 'A','', true, null, null, null),
 (4, 3, 'B','', true, null, null, null),
 (4, 4, '','', true, null, null, null),
 (4, 4, 'A','', true, null, null, null),
 (4, 4, 'B','', true, null, null, null),
 (4, 5, '','', true, null, null, null),
 (4, 5, 'A','', true, null, null, null),
 (4, 5, 'B','', true, null, null, null),
 (4, 6, '','', true, null, null, null),
 (4, 6, 'A','', true, null, null, null),
 (4, 6, 'B','', true, null, null, null)
;

INSERT INTO lehre.tbl_studienordnung (studienordnung_id, studiengang_kz,"version",gueltigvon,gueltigbis,bezeichnung,ects,studiengangbezeichnung,studiengangbezeichnung_englisch,studiengangkurzbzlang,akadgrad_id,insertamum,insertvon,updateamum,updatevon,ext_id,status_kurzbz,standort_id) VALUES
 (401, 4,'1',NearestWintersemester(-4),NULL,'BS4'||NearestWintersemester(-4),180.00,'Tempus Beispiele','Tempus Examples','STG4',NULL,now(),'anondata',NULL,NULL,NULL,'development',NULL);

INSERT INTO lehre.tbl_studienplan (studienplan_id, studienordnung_id,orgform_kurzbz,"version",bezeichnung,regelstudiendauer,sprache,aktiv,semesterwochen,testtool_sprachwahl,insertamum,insertvon,updateamum,updatevon,ext_id,ects_stpl,pflicht_sws,pflicht_lvs,onlinebewerbung_studienplan) VALUES
 (4011, 401,'VZ','1','BS4-'||NearestWintersemester(-4)||'-VZ',6,NULL,true,15,true,now(),'anondata',NULL,NULL,NULL,180.00,NULL,NULL,true);
 
INSERT INTO lehre.tbl_lehrveranstaltung (lehrveranstaltung_id, kurzbz,bezeichnung,studiengang_kz,semester,sprache,ects,semesterstunden,anmerkung,lehre,lehreverzeichnis,aktiv,planfaktor,planlektoren,planpersonalkosten,plankostenprolektor,updateamum,updatevon,insertamum,insertvon,ext_id,sort,zeugnis,koordinator,projektarbeit,lehrform_kurzbz,bezeichnung_english,orgform_kurzbz,incoming,lehrmodus_kurzbz,lehrtyp_kurzbz,oe_kurzbz,raumtyp_kurzbz,anzahlsemester,semesterwochen,lvnr,farbe,old_lehrfach_id,semester_alternativ,sws,lvs,alvs,lvps,las,benotung,lvinfo,lehrauftrag,lehrveranstaltung_template_id) VALUES
 (411, 'MOD1.1','Standard',4,1,'German',8.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'MOD','Standard','VZ',5,'regulaer','modul','stg4',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (412, 'MOD1.2','Advanced',4,1,'German',8.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'MOD','Advanced','VZ',5,'regulaer','modul','stg4',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (413, 'MOD1.3','Wahlmodul',4,1,'German',8.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'MOD','Wahlmodul','VZ',5,'regulaer','modul','stg4',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (414, 'MOD1.4','Sonstiges',4,1,'English',6.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'MOD','Sonstiges','VZ',5,'regulaer','modul','stg4',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 
 (461, 'MOD6.1','Bachelorarbeit',4,6,'German',8.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'MOD','Bachelorarbeit','VZ',5,'regulaer','modul','stg4',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (462, 'MOD6.2','International Skills',4,6,'English',5.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'MOD','International Skills','VZ',5,'regulaer','modul','stg4',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL),
 (463, 'MOD6.3','Berufspraktikum',4,6,'German',17.00,NULL,NULL,true,'mod',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'MOD','Berufspraktikum','VZ',5,'regulaer','modul','stg4',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,false,true,true,NULL)
 ;
 
  INSERT INTO lehre.tbl_lehrveranstaltung (lehrveranstaltung_id, kurzbz,bezeichnung,studiengang_kz,semester,sprache,ects,semesterstunden,anmerkung,lehre,lehreverzeichnis,aktiv,planfaktor,planlektoren,planpersonalkosten,plankostenprolektor,updateamum,updatevon,insertamum,insertvon,ext_id,sort,zeugnis,koordinator,projektarbeit,lehrform_kurzbz,bezeichnung_english,orgform_kurzbz,incoming,lehrmodus_kurzbz,lehrtyp_kurzbz,oe_kurzbz,raumtyp_kurzbz,anzahlsemester,semesterwochen,lvnr,farbe,old_lehrfach_id,semester_alternativ,sws,lvs,alvs,lvps,las,benotung,lvinfo,lehrauftrag,lehrveranstaltung_template_id) VALUES
 (4111, 'S1','Standardvorlesung',4,1,'German',8.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'VO','Standardvorlesung','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (4121, 'S2','Vorlesung und Uebung',4,1,'German',8.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'ILV','VorlesungUebung','VZ',5,'regulaer','lv','kfMath',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (4131, 'S3','Getrennte Gruppen',4,1,'German',8.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'ILV','Splittet Groups','VZ',5,'regulaer','lv','kfTech',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (4141, 'A1','Mehrere Lektoren',4,1,'English',6.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'VO','Multilektor','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 
 (4611, 'BA','Bachelorarbeit',4,6,'German',8.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'EL','Bachelorarbeit','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (4621, 'INT','International Skills',4,6,'English',5.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'PRJ','International Skills','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL),
 (4631, 'BPRAK','Berufspraktikum',4,6,'German',17.00,NULL,NULL,true,'lv',true,NULL,NULL,NULL,NULL,now(),'anondata',now(),'anondata',NULL,NULL,true,NULL,false,'SO','Berufspraktikum','VZ',5,'regulaer','lv','kfSprachen',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,true,true,true,NULL)
 ;
 
 INSERT INTO lehre.tbl_studienplan_lehrveranstaltung (studienplan_lehrveranstaltung_id, studienplan_id,lehrveranstaltung_id,semester,studienplan_lehrveranstaltung_id_parent,pflicht,koordinator,insertamum,insertvon,updateamum,updatevon,sort,ext_id,curriculum,export,genehmigung) VALUES
 -- MODULE im Studienplan
 (401, 4011,411,1,NULL,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 (402, 4011,412,1,NULL,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 (403, 4011,413,1,NULL,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 (404, 4011,414,1,NULL,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 
 (461, 4011,461,6,NULL,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 (462, 4011,462,6,NULL,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 (463, 4011,463,6,NULL,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),

 -- LVS zu Modulen
 (415, 4011,4111,1,401,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 (416, 4011,4121,1,401,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 (417, 4011,4131,1,401,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 (418, 4011,4141,1,402,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 
 (465, 4011,4611,6,461,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 (466, 4011,4621,6,462,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true),
 (467, 4011,4631,6,463,true,NULL,now(),'anondata',NULL,NULL,NULL,NULL,true,true,true)
;
	 
INSERT INTO lehre.tbl_studienplan_semester (studienplan_id, studiensemester_kurzbz, semester) VALUES
(4011, NearestWintersemester(-4), 1),
(4011, NearestWintersemester(-3), 2),
(4011, NearestWintersemester(-2), 1),
(4011, NearestWintersemester(-2), 3),
(4011, NearestWintersemester(-1), 2),
(4011, NearestWintersemester(-1), 4),
(4011, NearestWintersemester(0), 1),
(4011, NearestWintersemester(0), 3),
(4011, NearestWintersemester(0), 5),
(4011, NearestWintersemester(+1), 2),
(4011, NearestWintersemester(+1), 4),
(4011, NearestWintersemester(+1), 6);

-- ===== Standardvorlesung
INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(41001, 4111, NearestWintersemester(0), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4111, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4111, 1, 'Standard ein LV-Teil, ein Lektor, eine Gruppe');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(41001, 4, '1', '', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(41001, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 0.00, NULL, NULL);

-- ===== VORLESUNG UND UEBUNG
INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(41002, 4121, NearestWintersemester(0), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4121, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4121, 1, 'Vorlesung');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(41002, 4, '1', '', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(41002, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(41003, 4121, NearestWintersemester(0), NULL, 'UE', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4121, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4121, 1, 'Uebung');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(41003, 4, '1', '', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(41003, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 0.00, NULL, NULL);


-- ======= GETRENNTE GRUPPEN
INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(41004, 4131, NearestWintersemester(0), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4131, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4131, 1, 'Vorlesung 1A');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(41004, 4, '1', 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(41004, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 0.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(41005, 4131, NearestWintersemester(0), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4131, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4131, 1, 'Vorlesung 1B');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(41005, 4, '1', 'B', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(41005, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 0.00, NULL, NULL);

-- ======= Mehrere Lektoren
INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(41006, 4141, NearestWintersemester(0), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4141, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4141, 1, 'Vorlesung 1A und Lektor 2 und 3');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(41006, 4, '1', 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(41006, 'demolektor2', 'Lektor', 8, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 8.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(41006, 'demolektor3', 'Lektor', 8, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 8.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(41007, 4141, NearestWintersemester(0), NULL, 'VO', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4141, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4141, 1, 'Vorlesung 1B und Lektor 4 und 5');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(41007, 4, '1', 'B', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(41007, 'demolektor4', 'Lektor', 8, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 8.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(41007, 'demolektor5', 'Lektor', 8, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 8.00, NULL, NULL);

INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(41008, 4141, NearestWintersemester(0), NULL, 'BE', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4141, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4141, 1, 'LV Leitung die nicht verplant wird');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(41008, 4, '1', 'B', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(41008, 4, '1', 'A', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(41008, 'demolektor4', 'LV-Leitung', 0, NULL, 1.00, NULL, false, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 5.00, NULL, NULL);


-- ===== 6. Semester

-- ===== Bachelorarbeit
INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(46111, 4611, NearestWintersemester(1), NULL, 'EL', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4111, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4611, 1, 'keine Verplanung');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(46111, 4, '6', '', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(46111, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 0.00, NULL, NULL);

-- ===== Internation Skills
INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(46211, 4621, NearestWintersemester(1), NULL, 'EL', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4111, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4621, 1, 'nur für Zeugnis');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(46211, 4, '6', '', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(46211, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 0.00, NULL, NULL);

-- ===== BPRAK
INSERT INTO lehre.tbl_lehreinheit (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id_old, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id, lehrfach_id, gewicht, anmerkung)
VALUES(46311, 4631, NearestWintersemester(1), NULL, 'EL', 2, 1, NULL, 'Dummy', 'Dummy', 'German', true, 4111, NULL, '2023-08-24 14:54:43.000', 'anondata', '2023-08-24 14:54:43.000', 'anondata', NULL, 4631, 1, 'keine Verplanung');

INSERT INTO lehre.tbl_lehreinheitgruppe
(lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id)
VALUES(46311, 4, '6', '', NULL, NULL, NULL, NULL, '2023-08-24 14:58:26.000', 'anondata', NULL);

INSERT INTO lehre.tbl_lehreinheitmitarbeiter
(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, ext_id, semesterstunden, standort_id, vertrag_id)
VALUES(46311, 'demolektor1', 'Lektor', 0, NULL, 1.00, NULL, true, NULL, NULL, '2023-08-24 14:58:40.000', 'anondata', NULL, 0.00, NULL, NULL);

