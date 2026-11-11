-- Test data Karrierepfade (ticket 84049) for Studiengang 3, Studienplan 3011 (VZ)
-- 31-33 active, 34 inactive; Studienplan 4011 has no Karrierepfade

INSERT INTO public.tbl_karrierepfad (karrierepfad_id, karrierepfad_kurzbz, karrierepfad_typ_kurzbz, studiengang_kz, bezeichnung_mehrsprachig, sort, aktiv, insertvon) VALUES
 (31, 'TSTITSEC', 'marketing', 3, '{IT Security, IT Security}', 1, true, 'seeder'),
 (32, 'TSTAIDS', 'marketing', 3, '{AI und Data Science, AI and Data Science}', 2, true, 'seeder'),
 (33, 'TSTSWE', 'marketing', 3, '{Software Engineering, Software Engineering}', 3, true, 'seeder'),
 (34, 'TSTINAKTIV', 'marketing', 3, '{Inaktiver Karrierepfad, Inactive Karrierepfad}', 4, false, 'seeder');

SELECT setval('public.tbl_karrierepfad_karrierepfad_id_seq', (SELECT max(karrierepfad_id) FROM public.tbl_karrierepfad));

INSERT INTO public.tbl_karrierepfad_studienplan (karrierepfad_id, studienplan_id) VALUES
 (31, 3011),
 (32, 3011),
 (33, 3011),
 (34, 3011);

-- One prestudent with a Karrierepfad for the display in Infocenter, StV and Reihungstest
UPDATE public.tbl_prestudent SET karrierepfad_id = 31 WHERE prestudent_id = 3301;
