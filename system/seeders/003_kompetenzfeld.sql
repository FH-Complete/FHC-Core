-- Kompetenzfedler
INSERT INTO public.tbl_organisationseinheittyp (organisationseinheittyp_kurzbz, bezeichnung, beschreibung) VALUES('Kompetenzfeld', 'Kompetenzfeld', 'Kompetenzfeld');

INSERT INTO public.tbl_organisationseinheit (oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz, aktiv, mailverteiler, freigabegrenze, kurzzeichen, lehre, standort, warn_semesterstunden_frei, warn_semesterstunden_fix, standort_id) VALUES
 ('dep', 'etw', 'Fachabteilung', 'Abteilung', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL),
 ('kfSprachen', 'dep', 'Sprachen', 'Kompetenzfeld', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL),
 ('kfMath', 'dep', 'Mathematik', 'Kompetenzfeld', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL),
 ('kfTech', 'dep', 'Technik', 'Kompetenzfeld', true, false, NULL, NULL, true, NULL, NULL, NULL, NULL);
 
 INSERT INTO public.tbl_organisationseinheit
(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz, aktiv, mailverteiler, freigabegrenze, kurzzeichen, lehre, standort, warn_semesterstunden_frei, warn_semesterstunden_fix, standort_id, fts_bezeichnung)
VALUES('Lehrgaenge', 'etw', 'Lehrgänge', 'Abteilung', true, false, NULL, NULL, false, NULL, NULL, NULL, NULL, '''abteilung'':1 ''lehrgänge'':2'::tsvector);
INSERT INTO public.tbl_organisationseinheit
(oe_kurzbz, oe_parent_kurzbz, bezeichnung, organisationseinheittyp_kurzbz, aktiv, mailverteiler, freigabegrenze, kurzzeichen, lehre, standort, warn_semesterstunden_frei, warn_semesterstunden_fix, standort_id, fts_bezeichnung)
VALUES('studiengaenge', 'etw', 'Studiengänge', 'Abteilung', true, false, NULL, NULL, false, NULL, NULL, NULL, NULL, '''abteilung'':1 ''studiengänge'':2'::tsvector);

INSERT INTO public.tbl_studienjahr (studienjahr_kurzbz, bezeichnung) VALUES
('2026/27', 'Studienjahr 2026/27'),
('2027/28', 'Studienjahr 2027/28')
;

INSERT INTO public.tbl_studiensemester (studiensemester_kurzbz, "start", ende, ext_id, bezeichnung, studienjahr_kurzbz, beschreibung, onlinebewerbung) VALUES
('WS2026', '2026-09-01', '2027-01-31', NULL, 'Wintersemester 2026/27', '2026/27', NULL, false),
('SS2027', '2027-02-01', '2027-07-01', NULL, 'Sommersemester 2027', '2026/27', NULL, false),
('WS2027', '2027-09-01', '2028-01-31', NULL, 'Wintersemester 2027/28', '2027/28', NULL, false),
('SS2028', '2028-02-01', '2028-07-01', NULL, 'Sommersemester 2028', '2027/28', NULL, false)
;
