UPDATE public.tbl_funktion SET funktion_kurzbz='Student' WHERE funktion_kurzbz='stud';

UPDATE public.tbl_benutzer SET uid='demoadmin' WHERE uid='admin';

-- Add Missing functions
INSERT INTO public.tbl_funktion (funktion_kurzbz, beschreibung, aktiv, fachbereich, semester, hrrelevant, vertragsrelevant) VALUES('hsv', 'Hochschulvertretung', true, false, false, false, false);
INSERT INTO public.tbl_funktion (funktion_kurzbz, beschreibung, aktiv, fachbereich, semester, hrrelevant, vertragsrelevant) VALUES('jgv', 'Jahrgangsvertretung', true, false, true, false, false);
INSERT INTO public.tbl_funktion (funktion_kurzbz, beschreibung, aktiv, fachbereich, semester, hrrelevant, vertragsrelevant) VALUES('oezuordnung', 'Diszpl-Zuordnung', true, false, false, true, true);
INSERT INTO public.tbl_funktion (funktion_kurzbz, beschreibung, aktiv, fachbereich, semester, hrrelevant, vertragsrelevant) VALUES('fachzuordnung', 'Fachliche Zuordnung', true, false, false, true, true);

-- Add Permissions to Admin Role
INSERT INTO "system".tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art, anmerkung, insertamum, insertvon) VALUES('basis/cis', 'admin', 'suid', 'Zugriff auf CIS', now(), 'demoadmin');

INSERT INTO "system".tbl_benutzerrolle (rolle_kurzbz, berechtigung_kurzbz, uid, funktion_kurzbz, oe_kurzbz, art, studiensemester_kurzbz, "start", ende, negativ, updateamum, updatevon, insertamum, insertvon, kostenstelle_id, anmerkung) VALUES
(NULL, 'dashboard/benutzer', NULL, 'Mitarbeiter', NULL, 'suid', NULL, NULL, NULL, false, NULL, NULL, now(), 'demoadmin', NULL, NULL),
(NULL, 'dashboard/benutzer', NULL, 'Student', NULL, 'suid', NULL, NULL, NULL, false, NULL, NULL, now(), 'demoadmin', NULL, NULL)
;

INSERT INTO "system".tbl_webservicetyp (webservicetyp_kurzbz, beschreibung) VALUES('content', 'CIS-Content ID');

SELECT setval('campus.seq_contentsprache', (select max(contentsprache_id) from campus.tbl_contentsprache), true);
SELECT setval('campus.seq_contentchild', (select max(contentchild_id) from campus.tbl_contentchild), true);


INSERT INTO public.tbl_gruppe (gruppe_kurzbz, studiengang_kz, semester, bezeichnung, beschreibung, sichtbar, lehre, aktiv, sort, mailgrp, generiert, updateamum, updatevon, insertamum, insertvon, ext_id, orgform_kurzbz, content_visible, gesperrt, direktinskription, zutrittssystem, aufnahmegruppe) VALUES
('MA', 0, NULL, 'MA', 'MA', false, false, true, NULL, false, false, now(), 'demoadmin', now(), 'demoadmin', NULL, NULL, true, false, false, false, false),
('STUD', 0, NULL, 'STUD', 'STUD', false, false, true, NULL, false, false, now(), 'demoadmin', now(), 'demoadmin', NULL, NULL, true, false, false, false, false)
;
