-- INSERTS fuer St.Poelten DB

-- Erhalter

INSERT INTO public.tbl_erhalter(erhalter_kz, kurzbz, bezeichnung, dvr, logo, zvr) VALUES(13,'FHSTP', 'Fachhochschule St. Pölten', '','','');

-- OrgForm

INSERT INTO bis.tbl_orgform(orgform_kurzbz, code, bezeichnung) VALUES ('VZ', 1, 'Vollzeit');
INSERT INTO bis.tbl_orgform(orgform_kurzbz, code, bezeichnung) VALUES ('BB', 2, 'Berufsbegleitend');
INSERT INTO bis.tbl_orgform(orgform_kurzbz, code, bezeichnung) VALUES ('VBB', 3, 'Vollzeit und Berufsbeleitend');
INSERT INTO bis.tbl_orgform(orgform_kurzbz, code, bezeichnung) VALUES ('ZGS', 4, 'Zielgruppenspezifisch');

-- Studiengang

INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('0','fh','d','Fachhochschule','1','A','1','13','VZ',null);
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('2','sb','d','xxxSozialarbeit- berufsbegleitend','8','C','2','13','VZ','14');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('1','vo','d','xxxVerkehrsinformatik und Verkehrsökologie','8','C','2','13','VZ','15');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('261','mt','b','Bakkalaureatsstudiengang Medientechnik','6','C','2','13','VZ','16');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('323','sa','m','Magisterstudiengang Sozialarbeit','3','C','2','13','VZ','17');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('410','is','b','Bakkalaureatsstudiengang IT Security','6','C','2','13','VZ','18');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('405','bc','b','Bakkalaureatsstudiengang Computersimulation','6','C','2','13','VZ','19');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('403','bm','b','Bachelorstudiengang Medienmanagement','6','C','2','13','VZ','20');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('409','mk','b','Bachelorstudiengang Media- und Kommunikationsberatung','6','C','2','13','VZ','21');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('407','di','b','Diätologie','6','C','2','13','VZ','23');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('408','pt','b','Physiotherapie','6','C','2','13','VZ','24');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('406','bs','b','Bachelorstudiengang Soziale Arbeit','6','C','2','13','VZ','22');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('38','tm','d','Telekommunikation und Medien','8','C','2','13','VZ','10');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('88','mm','d','Medienmanagement','8','C','2','13','VZ','11');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('95','cs','d','Computersimulation','8','C','2','13','VZ','12');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('96','so','d','Sozialarbeit','8','C','2','13','VZ','13');
INSERT INTO public.tbl_studiengang(studiengang_kz, kurzbz, typ, bezeichnung, max_semester, max_verband, max_gruppe, erhalter_kz, orgform_kurzbz, ext_id) VALUES('262','ma','m','Masterstudiengang Telekommunikation und Medien','3','C','2','13','VZ','25');

-- Sprache

INSERT INTO public.tbl_sprache(sprache) VALUES('German');
INSERT INTO public.tbl_sprache(sprache) VALUES('English');
INSERT INTO public.tbl_sprache(sprache) VALUES('Espanol');

-- Fachbereich

INSERT INTO public.tbl_fachbereich(fachbereich_kurzbz, bezeichnung, farbe, studiengang_kz, ext_id, aktiv) VALUES('Dummy','','','0',null,true);

-- Ausbildung

INSERT INTO bis.tbl_ausbildung VALUES (1, 'PhD', 'Universitätsabschluss mit Doktorat als Zweit- oder Drittabschluss oder PhD-Abschluss');
INSERT INTO bis.tbl_ausbildung VALUES (3, 'FH-Master', 'Fachhochschulabschluss auf Diplom- oder Masterebene');
INSERT INTO bis.tbl_ausbildung VALUES (4, 'Univ.-Bachelor', 'Universitäts- oder Hochschulabschluss auf Bachelorebene (einschließlich Kurzstudien)');
INSERT INTO bis.tbl_ausbildung VALUES (5, 'FH-Bachelor', 'Fachhochschulabschluss auf Bachelorebene');
INSERT INTO bis.tbl_ausbildung VALUES (6, 'Akad-Diplom', 'Diplom einer Akademie für Lehrerbildung, Akademie für Sozialarbeit, Medizinisch-technische Akademie, Hebammenakademie, Militärakademie oder einer anderen anerkannten postsekundären Bildungseinrichtung');
INSERT INTO bis.tbl_ausbildung VALUES (8, 'AHS', 'Reifeprüfung an einer allgemeinbildenden höheren Schule');
INSERT INTO bis.tbl_ausbildung VALUES (9, 'BHS', 'Reife- und Diplomprüfung einer berufsbildenden oder lehrer- und erzieherbildenden höheren Schule');
INSERT INTO bis.tbl_ausbildung VALUES (10, 'Lehrabschluss', 'Lehrabschlussprüfung, berufsbildende mittlere Schule oder vergleichbare Berufsausbildung');
INSERT INTO bis.tbl_ausbildung VALUES (11, 'Pflichtschule', 'Pflichtschule');
INSERT INTO bis.tbl_ausbildung VALUES (7, 'tertiär', 'Anderer tertiärer Bildungsabschluss (Kolleg; Meisterprüfung; Universitätslehrgang oder Lehrgang gemäß §14a Abs.3 FHStG, mit dem kein akademischer Grad verbunden war)');
INSERT INTO bis.tbl_ausbildung VALUES (2, 'Univ.-Master', 'Universitäts- oder Hochschulabschluss auf Diplom- oder Masterebene, Doktorat der Medizin bzw. der Human- oder Zahnmedizin oder Doktorat auf Grund von Studienvorschriften aus der Zeit vor dem Inkrafttretendes AHStG BGBl. Nr. 177/1966 oder Abschluss eines Universitätslehrganges oder Lehrganges universitären Charakters (§51 Abs. 2 Z 23 UG 2002 oder §§26 Abs.1 und 28 Abs.1 UniStG) oder eines Lehrganges zur Weiterbildung (§14a Abs.2 FHStG) mit Mastergrad');

-- Studiensemester

INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2001', '2001-09-03', '2002-01-31', NULL, 'Wintersemester 2001/2002');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2006', '2006-02-13', '2006-07-01', 9, 'Sommersemester 2006');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2005', '2005-02-14', '2005-07-02', 7, 'Sommersemester 2005');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2004', '2004-02-03', '2004-07-03', 5, 'Sommersemester 2004');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2003', '2003-02-02', '2003-07-02', 3, 'Sommersemester 2003');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2002', '2002-02-01', '2002-07-01', 1, 'Sommersemester 2002');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2009', '2009-02-05', '2009-07-05', 15, 'Sommersemester 2009');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2007', '2007-02-12', '2007-07-01', 11, 'Sommersemester 2007');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('SS2008', '2008-02-04', '2008-07-04', 13, 'Sommersemester 2008');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2009', '2009-09-04', '2010-02-04', 16, 'Wintersemester 2009/2010');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2008', '2008-09-03', '2009-02-03', 14, 'Wintersemester 2008/2009');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2006', '2006-09-04', '2007-02-03', 10, 'Wintersemester 2006/2007');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2005', '2005-09-05', '2006-02-04', 8, 'Wintersemester 2005/2006');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2004', '2004-09-06', '2005-02-05', 6, 'Wintersemester 2004/2005');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2003', '2003-09-02', '2004-02-02', 4, 'Wintersemester 2003/2004');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2002', '2002-09-01', '2003-02-01', 2, 'Wintersemester 2002/2003');
INSERT INTO public.tbl_studiensemester(studiensemester_kurzbz, start, ende, ext_id, bezeichnung) VALUES ('WS2007', '2007-09-01', '2008-02-01', 12, 'Wintersemester 2007/2008');


-- tbl_kontakttyp; Type: TABLE DATA; Schema: public; 

SET search_path = public, pg_catalog;
INSERT INTO tbl_kontakttyp VALUES ('email', 'E-Mail');
INSERT INTO tbl_kontakttyp VALUES ('telefon', 'Telefonnummer');
INSERT INTO tbl_kontakttyp VALUES ('mobil', 'Mobiltelefonnummer');
INSERT INTO tbl_kontakttyp VALUES ('fax', 'Faxnummer');
INSERT INTO tbl_kontakttyp VALUES ('so.tel', 'sonstige Telefonnummer');


-- tbl_ausbildung; Type: TABLE DATA; Schema: bis; 

SET search_path = bis, pg_catalog;
INSERT INTO tbl_ausbildung VALUES (1, 'PhD', 'Universitätsabschluss mit Doktorat als Zweit- oder Drittabschluss oder PhD-Abschluss');
INSERT INTO tbl_ausbildung VALUES (3, 'FH-Master', 'Fachhochschulabschluss auf Diplom- oder Masterebene');
INSERT INTO tbl_ausbildung VALUES (4, 'Univ.-Bachelor', 'Universitäts- oder Hochschulabschluss auf Bachelorebene (einschließlich Kurzstudien)');
INSERT INTO tbl_ausbildung VALUES (5, 'FH-Bachelor', 'Fachhochschulabschluss auf Bachelorebene');
INSERT INTO tbl_ausbildung VALUES (6, 'Akad-Diplom', 'Diplom einer Akademie für Lehrerbildung, Akademie für Sozialarbeit, Medizinisch-technische Akademie, Hebammenakademie, Militärakademie oder einer anderen anerkannten postsekundären Bildungseinrichtung');
INSERT INTO tbl_ausbildung VALUES (8, 'AHS', 'Reifeprüfung an einer allgemeinbildenden höheren Schule');
INSERT INTO tbl_ausbildung VALUES (9, 'BHS', 'Reife- und Diplomprüfung einer berufsbildenden oder lehrer- und erzieherbildenden höheren Schule');
INSERT INTO tbl_ausbildung VALUES (10, 'Lehrabschluss', 'Lehrabschlussprüfung, berufsbildende mittlere Schule oder vergleichbare Berufsausbildung');
INSERT INTO tbl_ausbildung VALUES (11, 'Pflichtschule', 'Pflichtschule');
INSERT INTO tbl_ausbildung VALUES (7, 'tertiär', 'Anderer tertiärer Bildungsabschluss (Kolleg; Meisterprüfung; Universitätslehrgang oder Lehrgang gemäß §14a Abs.3 FHStG, mit dem kein akademischer Grad verbunden war)');
INSERT INTO tbl_ausbildung VALUES (2, 'Univ.-Master', 'Universitäts- oder Hochschulabschluss auf Diplom- oder Masterebene, Doktorat der Medizin bzw. der Human- oder Zahnmedizin oder Doktorat auf Grund von Studienvorschriften aus der Zeit vor dem Inkrafttretendes AHStG BGBl. Nr. 177/1966 oder Abschluss eines Universitätslehrganges oder Lehrganges universitären Charakters (§51 Abs. 2 Z 23 UG 2002 oder §§26 Abs.1 und 28 Abs.1 UniStG) oder eines Lehrganges zur Weiterbildung (§14a Abs.2 FHStG) mit Mastergrad');


-- tbl_zeitsperretyp; Type: TABLE DATA; Schema: campus; 

SET search_path = campus, pg_catalog;
INSERT INTO tbl_zeitsperretyp VALUES ('ReiseAL', 'Dienstreise Ausland', '00BFFF');
INSERT INTO tbl_zeitsperretyp VALUES ('Amt', 'Behördenweg', 'B3B3B3');
INSERT INTO tbl_zeitsperretyp VALUES ('Schulung', 'Weiterbildung', '99FF99');
INSERT INTO tbl_zeitsperretyp VALUES ('Sonstige', 'Sonstiges', '9966CC');
INSERT INTO tbl_zeitsperretyp VALUES ('Telework', 'Heimarbeit', 'FFCCFF');
INSERT INTO tbl_zeitsperretyp VALUES ('ReiseIL', 'Diensreise Inland', '00D926');
INSERT INTO tbl_zeitsperretyp VALUES ('DienstV', 'Dienstverhinderung', 'B3B364');
INSERT INTO tbl_zeitsperretyp VALUES ('DienstF', 'Dienstfreistellung', '39DFA4');
INSERT INTO tbl_zeitsperretyp VALUES ('Krank', 'Krankheit/Spitalsaufenthalt', 'B3B300');
INSERT INTO tbl_zeitsperretyp VALUES ('ZA', 'Zeitausgleich', 'FFA605');
INSERT INTO tbl_zeitsperretyp VALUES ('Arzt', 'Arztbesuch', '0066FF');
INSERT INTO tbl_zeitsperretyp VALUES ('Konfernz', 'Konferenz/Tagung/Seminar', 'CC6633');
INSERT INTO tbl_zeitsperretyp VALUES ('Urlaub', 'Urlaub', 'FF0000');


-- tbl_lehrfunktion; Type: TABLE DATA; Schema: lehre; 

SET search_path = lehre, pg_catalog;
INSERT INTO tbl_lehrfunktion VALUES ('LV-Leitung', 'Lehrveranstaltungsleiter', 1.10);
INSERT INTO tbl_lehrfunktion VALUES ('Betreuung', 'Betreuer', 0.90);
INSERT INTO tbl_lehrfunktion VALUES ('Lektor', 'Lektor', 1.00);
INSERT INTO tbl_lehrfunktion VALUES ('Zweitbetreuung', 'Zweitbetreuung', 0.90);


-- tbl_betriebsmitteltyp; Type: TABLE DATA; Schema: public;
SET search_path = public, pg_catalog;
INSERT INTO tbl_betriebsmitteltyp VALUES ('Zutrittskarte', 'Zutrittskarte', NULL, NULL);
INSERT INTO tbl_betriebsmitteltyp VALUES ('Schluessel', NULL, NULL, NULL);
INSERT INTO tbl_betriebsmitteltyp VALUES ('Laptop', NULL, NULL, NULL);


-- tbl_firmentyp; Type: TABLE DATA; Schema: public;
SET search_path = public, pg_catalog;
INSERT INTO tbl_firmentyp VALUES ('Partnerfirma', '');
INSERT INTO tbl_firmentyp VALUES ('Partneruniversität', '');
INSERT INTO tbl_firmentyp VALUES ('Fachhochschule', 'Fachhochschule');


-- tbl_projekttyp; Type: TABLE DATA; Schema: lehre;
SET search_path = lehre, pg_catalog;
INSERT INTO tbl_projekttyp VALUES ('Bachelor', 'Bachelorarbeit');
INSERT INTO tbl_projekttyp VALUES ('Diplom', 'Diplomarbeit');
INSERT INTO tbl_projekttyp VALUES ('Projekt', 'Projektarbeit');
INSERT INTO tbl_projekttyp VALUES ('Praktikum', 'Berufspraktikum');
INSERT INTO tbl_projekttyp VALUES ('Praxis', 'Praxissemester');


-- tbl_pruefungstyp; Type: TABLE DATA; Schema: lehre;
SET search_path = lehre, pg_catalog;
INSERT INTO tbl_pruefungstyp VALUES ('undefiniert', NULL, false);
INSERT INTO tbl_pruefungstyp VALUES ('Bachelor', 'Bachelorprüfung', true);
INSERT INTO tbl_pruefungstyp VALUES ('Diplom', 'Diplomprüfung', true);
INSERT INTO tbl_pruefungstyp VALUES ('Termin1', '1. Termin', false);
INSERT INTO tbl_pruefungstyp VALUES ('Termin2', '2. Termin', false);
INSERT INTO tbl_pruefungstyp VALUES ('kommPruef', 'kommissionelle Prüfung', false);


-- tbl_erreichbarkeit; Type: TABLE DATA; Schema: campus;
SET search_path = campus, pg_catalog;
INSERT INTO tbl_erreichbarkeit VALUES ('t', 'telefonisch', NULL);
INSERT INTO tbl_erreichbarkeit VALUES ('e', 'eMail', NULL);
INSERT INTO tbl_erreichbarkeit VALUES ('et', 'eMail oder Telefon', NULL);
INSERT INTO tbl_erreichbarkeit VALUES ('n', 'Nicht erreichbar!', 'FF0000');


-- tbl_zeitsperretyp; Type: TABLE DATA; Schema: campus;
SET search_path = campus, pg_catalog;
INSERT INTO tbl_zeitsperretyp VALUES ('ReiseAL', 'Dienstreise Ausland', '00BFFF');
INSERT INTO tbl_zeitsperretyp VALUES ('Amt', 'Behördenweg', 'B3B3B3');
INSERT INTO tbl_zeitsperretyp VALUES ('Schulung', 'Weiterbildung', '99FF99');
INSERT INTO tbl_zeitsperretyp VALUES ('Sonstige', 'Sonstiges', '9966CC');
INSERT INTO tbl_zeitsperretyp VALUES ('Telework', 'Heimarbeit', 'FFCCFF');
INSERT INTO tbl_zeitsperretyp VALUES ('ReiseIL', 'Diensreise Inland', '00D926');
INSERT INTO tbl_zeitsperretyp VALUES ('DienstV', 'Dienstverhinderung', 'B3B364');
INSERT INTO tbl_zeitsperretyp VALUES ('DienstF', 'Dienstfreistellung', '39DFA4');
INSERT INTO tbl_zeitsperretyp VALUES ('Krank', 'Krankheit/Spitalsaufenthalt', 'B3B300');
INSERT INTO tbl_zeitsperretyp VALUES ('ZA', 'Zeitausgleich', 'FFA605');
INSERT INTO tbl_zeitsperretyp VALUES ('Arzt', 'Arztbesuch', '0066FF');
INSERT INTO tbl_zeitsperretyp VALUES ('Konfernz', 'Konferenz/Tagung/Seminar', 'CC6633');
INSERT INTO tbl_zeitsperretyp VALUES ('Urlaub', 'Urlaub', 'FF0000');


-- tbl_berufstaetigkeit Type: TABLE DATA; Schema: bis;
SET search_path = bis, pg_catalog;
INSERT INTO tbl_berufstaetigkeit VALUES (0, 'nicht berufstätig', 'n.berufstätig');
INSERT INTO tbl_berufstaetigkeit VALUES (2, 'arbeitslos gemeldet mit facheinschlägiger Berufserfahrung', 'fach.arbeitslos');
INSERT INTO tbl_berufstaetigkeit VALUES (3, 'arbeitslos gemeldet sonstige', 'so.arbeitslos');
INSERT INTO tbl_berufstaetigkeit VALUES (6, 'Vollzeit facheinschlägig berufstätig', 'Vz fach');
INSERT INTO tbl_berufstaetigkeit VALUES (7, 'Teilzeit facheinschlägig berufstätig', 'Tz fach');
INSERT INTO tbl_berufstaetigkeit VALUES (9, 'Vollzeit nicht facheinschlägig berufstätig', 'Vz sonst');
INSERT INTO tbl_berufstaetigkeit VALUES (10, 'Teilzeit nicht facheinschlägig berufstätig', 'Tz sonst');


-- tbl_beschaeftigungsart1; Type: TABLE DATA; Schema: bis;
SET search_path = bis, pg_catalog;
INSERT INTO tbl_beschaeftigungsart1 VALUES (4, 'Dienstverhältnis zur Bildungseinrichtung oder deren Träger (Freier Dienstvertrag)', 'Freier Dienstvertrag');
INSERT INTO tbl_beschaeftigungsart1 VALUES (3, 'Dienstverhältnis zur Bildungseinrichtung oder deren Träger (Echter Dienstvertrag)', 'Echter Dienstvertrag');
INSERT INTO tbl_beschaeftigungsart1 VALUES (1, 'Dienstverhältnis zum Bund', 'DV zum Bund');
INSERT INTO tbl_beschaeftigungsart1 VALUES (6, 'Sonstiges Beschäftigungsverhältnis (inkludiert z.B. Werkverträge)', 'Sonstiges (Werkvertrag)');
INSERT INTO tbl_beschaeftigungsart1 VALUES (5, 'Lehr- oder Ausbildungsverhältnis', 'Lehr-oder Ausbildungsverhältnis');
INSERT INTO tbl_beschaeftigungsart1 VALUES (2, 'Dienstverhältnis zu einer anderen Gebietskörperschaft', 'DV anderen Gebietskörperschaft');


-- tbl_beschaeftigungsart2; Type: TABLE DATA; Schema: bis;
SET search_path = bis, pg_catalog;
INSERT INTO tbl_beschaeftigungsart2 VALUES (2, 'unbefristet');
INSERT INTO tbl_beschaeftigungsart2 VALUES (1, 'befristet');


-- tbl_beschaeftigungsausmass; Type: TABLE DATA; Schema: bis;
SET search_path = bis, pg_catalog;
INSERT INTO tbl_beschaeftigungsausmass VALUES (1, 'Vollzeit', 36, 168);
INSERT INTO tbl_beschaeftigungsausmass VALUES (2, '0-15', 0, 15);
INSERT INTO tbl_beschaeftigungsausmass VALUES (3, '16-25', 16, 25);
INSERT INTO tbl_beschaeftigungsausmass VALUES (4, '26-35', 26, 35);
INSERT INTO tbl_beschaeftigungsausmass VALUES (5, 'Karenz', 0, 0);


-- tbl_besqual; Type: TABLE DATA; Schema: bis;
SET search_path = bis, pg_catalog;
INSERT INTO tbl_besqual VALUES (0, 'Keine');
INSERT INTO tbl_besqual VALUES (1, 'Habilitation');
INSERT INTO tbl_besqual VALUES (2, 'der Habilitation gleichwertige Qualifikation');
INSERT INTO tbl_besqual VALUES (3, 'berufliche Tätigkeit');


-- tbl_hauptberuf; Type: TABLE DATA; Schema: bis;
SET search_path = bis, pg_catalog;
INSERT INTO tbl_hauptberuf VALUES (0, 'Universität');
INSERT INTO tbl_hauptberuf VALUES (1, 'Fachhochschule');
INSERT INTO tbl_hauptberuf VALUES (2, 'Andere postsekundäre Bildungseinrichtung');
INSERT INTO tbl_hauptberuf VALUES (3, 'Allgemeinbildende höhere Schule');
INSERT INTO tbl_hauptberuf VALUES (4, 'Berufsbildende höhere Schule');
INSERT INTO tbl_hauptberuf VALUES (5, 'Andere Schule');
INSERT INTO tbl_hauptberuf VALUES (6, 'Öffentlicher Sektor');
INSERT INTO tbl_hauptberuf VALUES (7, 'Unternehmenssektor');
INSERT INTO tbl_hauptberuf VALUES (8, 'Freiberuflich tätig');
INSERT INTO tbl_hauptberuf VALUES (9, 'Privater gemeinnütziger Sektor');
INSERT INTO tbl_hauptberuf VALUES (10, 'Außerhochschulische Forschungseinrichtung');
INSERT INTO tbl_hauptberuf VALUES (11, 'Internationale Organisation');
INSERT INTO tbl_hauptberuf VALUES (12, 'Sonstiges');



-- tbl_verwendung; Type: TABLE DATA; Schema: bis;
SET search_path = bis, pg_catalog;
INSERT INTO tbl_verwendung VALUES (1, 'Lehr- und Forschungspersonal (Academic staff)');
INSERT INTO tbl_verwendung VALUES (2, 'Lehr- und Forschungshilfspersonal (Teaching and Research assistants)');
INSERT INTO tbl_verwendung VALUES (3, 'Akademische Dienste für Studierende(Academic Support');
INSERT INTO tbl_verwendung VALUES (5, 'Studiengangsleiter/in');
INSERT INTO tbl_verwendung VALUES (6, 'Leiter/in FH-Kollegium');
INSERT INTO tbl_verwendung VALUES (7, 'Management (School Level Management)');
INSERT INTO tbl_verwendung VALUES (8, 'Verwaltung (School Level Administrative Personnel)');
INSERT INTO tbl_verwendung VALUES (9, 'Hauspersonal, Gebäude-/Haustechnik (Maintainance and Operations Personnel)');
INSERT INTO tbl_verwendung VALUES (4, 'Soziale Dienste und Gesundheitsdienste (Health and Social Support)');


-- tbl_aktivitaet; Type: TABLE DATA; Schema: fue;
SET search_path = fue, pg_catalog;
INSERT INTO tbl_aktivitaet VALUES ('ServiceVO', 'Service (VorOrt)');
INSERT INTO tbl_aktivitaet VALUES ('Service', 'Service');
INSERT INTO tbl_aktivitaet VALUES ('Schulung', 'Schulung die gegeben wird.');
INSERT INTO tbl_aktivitaet VALUES ('Arbeit', 'Arbeit (allgemein)');
INSERT INTO tbl_aktivitaet VALUES ('Besprechung', 'Besprechung');
INSERT INTO tbl_aktivitaet VALUES ('Workshop', 'Workshop');
INSERT INTO tbl_aktivitaet VALUES ('TelefonSupport', 'TelefonSupport');
INSERT INTO tbl_aktivitaet VALUES ('eMailSupport', 'eMailSupport');


-- tbl_projekt; Type: TABLE DATA; Schema: fue;
SET search_path = fue, pg_catalog;
INSERT INTO tbl_projekt VALUES ('Tempus', NULL, 'Tempus', NULL, '2005-09-01', NULL);
INSERT INTO tbl_projekt VALUES ('StPoelten', NULL, 'FH-Complete StPoelten', NULL, '2007-09-01', NULL);
INSERT INTO tbl_projekt VALUES ('FASo', NULL, 'FASonline', NULL, '2007-02-01', NULL);


-- 
