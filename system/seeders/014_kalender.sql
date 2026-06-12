-- Rooms setup for calendar tests 
INSERT INTO public.tbl_adresse
(adresse_id, person_id, "name", strasse, plz, ort, gemeinde, nation, typ, heimatadresse, zustelladresse, firma_id, updateamum, updatevon, insertamum, insertvon, ext_id, rechnungsadresse, anmerkung, co_name)
VALUES(1, NULL, 'Hauptwohnsitz', 'Stromstraße 18-20/1/10', '1200', 'Wien', 'Wien', 'A', 'h', true, true, NULL, '2007-08-13 10:53:40.298', 'demoadmin', '2007-08-10 10:42:02.579', 'demoadmin', 14470, false, NULL, NULL);

INSERT INTO public.tbl_standort
(kurzbz, adresse_id, standort_id, bezeichnung, insertvon, insertamum, updatevon, updateamum, ext_id, firma_id, code)
VALUES('Vienna Universit', 1, 1, 'Vienna University of Technology, Institute of Design and Assessment of Technology', 'demoadmin', '2012-02-02 10:24:11.803', 'demoadmin', '2015-05-27 09:05:53.894', NULL, 6033, NULL);

INSERT INTO public.tbl_ort
(ort_kurzbz, bezeichnung, planbezeichnung, max_person, lehre, reservieren, aktiv, lageplan, dislozierung, kosten, ausstattung, updateamum, updatevon, insertamum, insertvon, ext_id, stockwerk, telefonklappe, standort_id, content_id, m2, gebteil, oe_kurzbz, arbeitsplaetze)
VALUES
('Bro_F7.37', 'Büro', 'F7.37', NULL, true, true, true, NULL, NULL, NULL, NULL, '2023-09-19 10:35:20.000', 'demoadmin', '2012-09-05 15:20:25.979', 'demoadmin', NULL, 7, NULL, 1, NULL, 21.58, 'F', 'etw', NULL),
('Bro_F7.38', 'Büro', 'F7.38', NULL, true, true, true, NULL, NULL, NULL, NULL, '2023-09-19 10:35:20.000', 'demoadmin', '2012-09-05 15:20:25.979', 'demoadmin', NULL, 7, NULL, 1, NULL, 21.58, 'F', 'etw', NULL),
('Bro_F7.39', 'Büro', 'F7.39', NULL, true, true, true, NULL, NULL, NULL, NULL, '2023-09-19 10:35:20.000', 'demoadmin', '2012-09-05 15:20:25.979', 'demoadmin', NULL, 7, NULL, 1, NULL, 21.58, 'F', 'etw', NULL),
('Bro_F7.40', 'Büro', 'F7.40', NULL, true, true, true, NULL, NULL, NULL, NULL, '2023-09-19 10:35:20.000', 'demoadmin', '2012-09-05 15:20:25.979', 'demoadmin', NULL, 7, NULL, 1, NULL, 21.58, 'F', 'etw', NULL),
('Bro_F7.41', 'Büro', 'F7.41', NULL, true, true, true, NULL, NULL, NULL, NULL, '2023-09-19 10:35:20.000', 'demoadmin', '2012-09-05 15:20:25.979', 'demoadmin', NULL, 7, NULL, 1, NULL, 21.58, 'F', 'etw', NULL);

INSERT INTO public.tbl_ortraumtyp
(ort_kurzbz, hierarchie, raumtyp_kurzbz)
VALUES
('Bro_F7.37', 1, 'Dummy'),
('Bro_F7.38', 1, 'Dummy'),
('Bro_F7.39', 1, 'Dummy'),
('Bro_F7.40', 1, 'Dummy'),
('Bro_F7.41', 1, 'Dummy');
-- ////////////////////////////////////////////////////////////////////////////////////////////////////


-- Calendar setup for event drag and drop tests
INSERT INTO lehre.tbl_kalender
(von, bis, typ, status_kurzbz, vorgaenger_kalender_id, insertamum, insertvon, updateamum, updatevon) VALUES
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '08:00:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '08:45:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '08:45:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '09:30:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '09:40:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '10:25:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '10:25:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '11:10:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '09:40:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '10:25:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '09:40:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '10:25:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '10:25:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '11:10:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '10:25:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '11:10:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin')
;

INSERT INTO lehre.tbl_kalender_ort
(location, ort_kurzbz, kalender_id)
VALUES(NULL, 'Bro_F7.37', 1);
-- ////////////////////////////////////////////////////////////////////////////////////////////////////


-- Calendar setup for resize tests
INSERT INTO lehre.tbl_kalender
(von, bis, typ, status_kurzbz, vorgaenger_kalender_id, insertamum, insertvon, updateamum, updatevon) VALUES
(date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '08:00:00', date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '08:45:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '4 day' + TIME '10:25:00', date_trunc('week', CURRENT_DATE) + INTERVAL '4 day' + TIME '11:10:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '4 day' + TIME '10:25:00', date_trunc('week', CURRENT_DATE) + INTERVAL '4 day' + TIME '11:10:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin')
;
-- ////////////////////////////////////////////////////////////////////////////////////////////////////


-- Calendar setup for role preview tests
INSERT INTO lehre.tbl_kalender
(von, bis, typ, status_kurzbz, vorgaenger_kalender_id, insertamum, insertvon, updateamum, updatevon) VALUES
(date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '13:35:00', date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '14:20:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '13:35:00', date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '14:20:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin')
;
-- ////////////////////////////////////////////////////////////////////////////////////////////////////

-- Calendar setup for API event creation tests
INSERT INTO lehre.tbl_kalender
(von, bis, typ, status_kurzbz, vorgaenger_kalender_id, insertamum, insertvon, updateamum, updatevon) VALUES
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '18:35:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '19:20:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '18:35:00', date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '19:20:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin')
;
-- ////////////////////////////////////////////////////////////////////////////////////////////////////


-- Calendar setup for API event update tests
INSERT INTO lehre.tbl_kalender
(von, bis, typ, status_kurzbz, vorgaenger_kalender_id, insertamum, insertvon, updateamum, updatevon)
VALUES
(date_trunc('week', CURRENT_DATE) + INTERVAL '1 day' + TIME '19:30:00', date_trunc('week', CURRENT_DATE) + INTERVAL '1 day' + TIME '20:15:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '19:30:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '20:15:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '20:15:00', date_trunc('week', CURRENT_DATE) + INTERVAL '2 day' + TIME '21:00:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '19:30:00', date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '20:15:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '20:15:00', date_trunc('week', CURRENT_DATE) + INTERVAL '3 day' + TIME '21:00:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '4 day' + TIME '19:30:00', date_trunc('week', CURRENT_DATE) + INTERVAL '4 day' + TIME '20:15:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '4 day' + TIME '19:30:00', date_trunc('week', CURRENT_DATE) + INTERVAL '4 day' + TIME '20:15:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '4 day' + TIME '20:15:00', date_trunc('week', CURRENT_DATE) + INTERVAL '4 day' + TIME '21:00:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '5 day' + TIME '19:30:00', date_trunc('week', CURRENT_DATE) + INTERVAL '5 day' + TIME '20:15:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '5 day' + TIME '20:15:00', date_trunc('week', CURRENT_DATE) + INTERVAL '5 day' + TIME '21:00:00', 'lehreinheit', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin'),
(date_trunc('week', CURRENT_DATE) + INTERVAL '5 day' + TIME '20:15:00', date_trunc('week', CURRENT_DATE) + INTERVAL '5 day' + TIME '21:00:00', 'reservierung', 'live', NULL, now(), 'demoadmin', NULL, 'demoadmin')
;

INSERT INTO lehre.tbl_kalender_ort
(location, ort_kurzbz, kalender_id)
VALUES
(NULL, 'Bro_F7.37', 13),
(NULL, 'Bro_F7.37', 14);

-- ////////////////////////////////////////////////////////////////////////////////////////////////////

INSERT INTO lehre.tbl_kalender_lehreinheit
(lehreinheit_id, kalender_id)
VALUES
(51001, 1),
(51002, 2),
(51003, 3),
(51003, 4),
(51002, 5),
(51006, 6),
(51002, 7),
(51006, 8),
(51001, 9),
(51003, 10),
(51002, 11),
(51001, 12),
(51002, 13),
(51003, 14),
(51005, 15),
(51001, 16),
(51001, 17),
(51003, 18),
(51001, 19),
(51003, 20),
(51003, 21),
(51004, 22),
(51004, 23),
(51005, 24),
(51003, 25),
(51006, 26);

INSERT INTO lehre.tbl_kalender_event
(kalender_id, titel, beschreibung)
VALUES(21, 'Test reservation', NULL);

INSERT INTO lehre.tbl_kalender_event_teilnehmer
(kalender_id, rolle_kurzbz, uid, studiensemester_kurzbz, gruppe_kurzbz, studiengang_kz, semester, verband, gruppe, studentenlehrverband_id)
VALUES(21, 'teilnehmer', 'demolektor4', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO campus.tbl_zeitsperre
(zeitsperre_id, zeitsperretyp_kurzbz, mitarbeiter_uid, bezeichnung, vondatum, vonstunde, bisdatum, bisstunde, vertretung_uid, updateamum, updatevon, insertamum, insertvon, erreichbarkeit_kurzbz, freigabeamum, freigabevon)
VALUES(1, 'DienstV', 'demolektor1', 'Test', date_trunc('week', CURRENT_DATE) + INTERVAL '5 day', NULL, date_trunc('week', CURRENT_DATE) + INTERVAL '5 day', NULL, 'demoadmin', '2007-04-02 11:04:31.000', 'demoadmin', '2007-04-02 11:04:31.000', 'demoadmin', 't', NULL, NULL);
