-- EMPTY public.tbl_person
DELETE FROM public.tbl_person WHERE person_id > 2;
-- INSERT Persons (public.tbl_person)
INSERT INTO public.tbl_person VALUES (3, NULL, NULL, NULL, NULL, NULL, NULL, 'McKenzie', 'Vicenta', 'Abraham', '2002-12-30', 'Brooksburgh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.624239', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '01234567A', false);
INSERT INTO public.tbl_person VALUES (4, NULL, NULL, NULL, NULL, NULL, NULL, 'Wilderman', 'Rocio', 'Jayson', '2002-09-03', 'Hermannshire', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.632551', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '01234567B', false);
INSERT INTO public.tbl_person VALUES (5, NULL, NULL, NULL, NULL, NULL, NULL, 'Harvey', 'Joshuah', 'Halie', '1930-01-18', 'Mitchellville', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.634179', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '01234567C', false);
INSERT INTO public.tbl_person VALUES (6, NULL, NULL, NULL, NULL, NULL, NULL, 'Kessler', 'Neil', 'Dashawn', '1948-06-10', 'Anitafort', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.63728', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '01234567D', false);

-- EMPTY public.tbl_benutzer
DELETE FROM public.tbl_benutzer WHERE person_id > 2;
-- INSERT Benutzer (public.tbl_benutzer)
INSERT INTO public.tbl_benutzer VALUES ('mckenzie', 3, 't', 'mckenzie.vicenta', NOW(), 'codeception', NOW(), 'codeception', NULL, 'codeception', NOW(), NULL);
INSERT INTO public.tbl_benutzer VALUES ('wilderman', 4, 't', 'wilderman.rocio', NOW(), 'codeception', NOW(), 'codeception', NULL, 'codeception', NOW(), NULL);
INSERT INTO public.tbl_benutzer VALUES ('harvey', 5, 't', 'harvey.joshuah', NOW(), 'codeception', NOW(), 'codeception', NULL, 'codeception', NOW(), NULL);
INSERT INTO public.tbl_benutzer VALUES ('kessler', 6, 't', 'kessler.neil', NOW(), 'codeception', NOW(), 'codeception', NULL, 'codeception', NOW(), NULL);

-- EMPTY public.tbl_kontakt
DELETE FROM public.tbl_kontakt WHERE person_id > 2;
-- INSERT Kontakt (public.tbl_kontakt)
INSERT INTO public.tbl_kontakt VALUES (1, 3, 'email', NULL, 'mckenzie.vicenta@calva.dev', 't', NOW(), NULL, NOW(), 'codeception', NULL, NULL);
INSERT INTO public.tbl_kontakt VALUES (2, 4, 'email', NULL, 'wilderman.rocio@calva.dev', 't', NOW(), NULL, NOW(), 'codeception', NULL, NULL);
INSERT INTO public.tbl_kontakt VALUES (3, 5, 'email', NULL, 'harvey.joshuah@calva.dev', 't', NOW(), NULL, NOW(), 'codeception', NULL, NULL);
INSERT INTO public.tbl_kontakt VALUES (4, 6, 'email', NULL, 'kessler.neil@calva.dev', 't', NOW(), NULL, NOW(), 'codeception', NULL, NULL);

-- EMPTY public.tbl_erhalter
DELETE FROM public.tbl_erhalter WHERE erhalter_kz = 1;
-- INSERT Erhalter (public.tbl_erhalter)
INSERT INTO public.tbl_erhalter VALUES (1, '1S', 'Bla bla bla', '12345678', NULL, '1234567812345678');

-- EMPTY public.tbl_studiengangstyp
DELETE FROM public.tbl_studiengangstyp WHERE typ = 'A';
-- INSERT Studiengangstyp (public.tbl_studiengangstyp)
INSERT INTO public.tbl_studiengangstyp VALUES ('A', 'Codeceptiongang', 'Codeceptiongang');

-- EMPTY bis.tbl_bisorgform
DELETE FROM bis.tbl_bisorgform WHERE bisorgform_kurzbz = 'A';
-- INSERT Bisorgform (bis.tbl_bisorgform)
INSERT INTO bis.tbl_bisorgform VALUES ('A', 1, 'Vollzeit');

-- EMPTY bis.tbl_orgform
DELETE FROM bis.tbl_orgform WHERE orgform_kurzbz = 'A';
-- INSERT Orgform (bis.tbl_orgform)
INSERT INTO bis.tbl_orgform VALUES ('A', 1, 'Vollzeit', 't', 'A');

-- EMPTY public.tbl_organisationseinheittyp
DELETE FROM public.tbl_organisationseinheittyp WHERE organisationseinheittyp_kurzbz = 'A';
-- INSERT Organisationseinheittyp (public.tbl_organisationseinheittyp)
INSERT INTO public.tbl_organisationseinheittyp VALUES ('A', NULL, NULL);

-- EMPTY public.tbl_organisationseinheit
DELETE FROM public.tbl_organisationseinheit WHERE oe_kurzbz = 'A';
-- INSERT Organisationseinheit (public.tbl_organisationseinheit)
INSERT INTO public.tbl_organisationseinheit VALUES ('A', 'A', 'A', 'A', 't', 'f', NULL, NULL, 'f', NULL, NULL, NULL, NULL);

-- EMPTY public.tbl_studiengang
DELETE FROM public.tbl_studiengang WHERE studiengang_kz = 1;
-- INSERT Studiengang (public.tbl_studiengang)
INSERT INTO public.tbl_studiengang VALUES (1, 'tw', '1S', 'A', 'Codeception test', 'Codeception test', NULL, 'admin@calva.dev', NULL, 6, 'A', 2, 1, NULL, '1/2', '2,3', '4/5', NOW(), NULL, 't', NULL, 'A', NULL, 't', 'English', 'f', NULL, 'A', NULL, 'f', 't', 'f');

-- EMPTY public.tbl_lehrverband
DELETE FROM public.tbl_lehrverband WHERE studiengang_kz = 1;
-- INSERT Lehrverband (public.tbl_lehrverband)
INSERT INTO public.tbl_lehrverband VALUES (1, 1, 'V', '1', 't', NULL, NULL, NULL, 1);

-- EMPTY public.tbl_mitarbeiter
DELETE FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid = 'mckenzie';
-- INSERT Mitarbeiter (public.tbl_mitarbeiter)
INSERT INTO public.tbl_mitarbeiter VALUES ('mckenzie', 1, NULL, 'Blabla', 't', 't', NULL, NULL, NULL, NOW(), 'codeception', NOW(), 'codeception', NULL, NULL, 't', NULL, 'f');

-- EMPTY public.tbl_ort
DELETE FROM public.tbl_ort WHERE ort_kurzbz = 'Nirvana';
-- INSERT Ort (public.tbl_ort)
INSERT INTO public.tbl_ort VALUES ('Nirvana', 'Blablablabla', 'A-1', 2000, 't', 't', 't', NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- EMPTY public.tbl_studienjahr
DELETE FROM public.tbl_studienjahr WHERE studienjahr_kurzbz = '2123/01';
-- INSERT Studienjahr (public.tbl_studienjahr)
INSERT INTO public.tbl_studienjahr VALUES ('2123/01', NULL);

-- EMPTY lehre.tbl_studienordnungstatus
DELETE FROM lehre.tbl_studienordnungstatus WHERE status_kurzbz = 'A';
-- INSERT Studienordnungstatus (lehre.tbl_studienordnungstatus)
INSERT INTO lehre.tbl_studienordnungstatus VALUES ('A', 'A', 1);

-- EMPTY public.tbl_studiensemester
DELETE FROM public.tbl_studiensemester WHERE studiensemester_kurzbz = 'A';
-- INSERT Studiensemester (public.tbl_studiensemester)
INSERT INTO public.tbl_studiensemester VALUES ('A', NOW(), NOW(), NULL, 'Bla bla bla', '2123/01', NULL, 't');

-- EMPTY lehre.tbl_studienordnung
DELETE FROM lehre.tbl_studienordnung WHERE studienordnung_id = -1;
-- INSERT Studienordnung (lehre.tbl_studienordnung)
INSERT INTO lehre.tbl_studienordnung VALUES (-1, 1, 01, 'A', 'A', 'A', 180.00, 'Bla bla bla', 'Bla bla bla bla', 'A', 1, NOW(), 'codeception', NOW(), 'codeception', NULL, 'A', NULL);

-- EMPTY lehre.tbl_studienplan
DELETE FROM lehre.tbl_studienplan WHERE tbl_studienplan < 5;
-- INSERT Studienplan (lehre.tbl_studienplan)
INSERT INTO lehre.tbl_studienplan VALUES (1, 1, 'A', 'V1', '01234', 6, 'English', 't', 15, 't', NOW(), 'codeception', NOW(), NULL, NULL, NULL, NULL, NULL);
INSERT INTO lehre.tbl_studienplan VALUES (2, 1, 'A', 'V1', '01234', 6, 'English', 't', 15, 't', NOW(), 'codeception', NOW(), NULL, NULL, NULL, NULL, NULL);
INSERT INTO lehre.tbl_studienplan VALUES (3, 1, 'A', 'V1', '01234', 6, 'English', 't', 15, 't', NOW(), 'codeception', NOW(), NULL, NULL, NULL, NULL, NULL);
INSERT INTO lehre.tbl_studienplan VALUES (4, 1, 'A', 'V1', '01234', 6, 'English', 't', 15, 't', NOW(), 'codeception', NOW(), NULL, NULL, NULL, NULL, NULL);