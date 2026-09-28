INSERT INTO public.tbl_ort
(ort_kurzbz, bezeichnung, planbezeichnung, max_person, lehre, reservieren, aktiv, lageplan, dislozierung, kosten, ausstattung, updateamum, updatevon, insertamum, insertvon, ext_id, stockwerk, telefonklappe, standort_id, content_id, m2, gebteil, oe_kurzbz, arbeitsplaetze)
VALUES('EG03', 'Besprechungsruam EG03', 'EG03', 8, true, true, true, NULL, NULL, NULL, NULL, '2023-08-24 14:18:50.000', 'oesi', '2023-08-24 14:17:30.000', 'oesi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.tbl_ort
(ort_kurzbz, bezeichnung, planbezeichnung, max_person, lehre, reservieren, aktiv, lageplan, dislozierung, kosten, ausstattung, updateamum, updatevon, insertamum, insertvon, ext_id, stockwerk, telefonklappe, standort_id, content_id, m2, gebteil, oe_kurzbz, arbeitsplaetze)
VALUES('EG01', 'Seminarraum EG01', 'EG01', 10, true, true, true, NULL, NULL, NULL, NULL, '2023-08-24 14:18:52.000', 'oesi', '2023-08-24 14:16:47.000', 'oesi', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.tbl_ort
(ort_kurzbz, bezeichnung, planbezeichnung, max_person, lehre, reservieren, aktiv, lageplan, dislozierung, kosten, ausstattung, updateamum, updatevon, insertamum, insertvon, ext_id, stockwerk, telefonklappe, standort_id, content_id, m2, gebteil, oe_kurzbz, arbeitsplaetze)
VALUES('EG02', 'Seminarraum EG02', 'EG02', 10, true, true, true, NULL, NULL, NULL, NULL, '2023-08-24 14:18:54.000', 'oesi', '2023-08-24 14:16:56.000', 'oesi', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO public.tbl_ort
(ort_kurzbz, bezeichnung, planbezeichnung, max_person, lehre, reservieren, aktiv, lageplan, dislozierung, kosten, ausstattung, updateamum, updatevon, insertamum, insertvon, ext_id, stockwerk, telefonklappe, standort_id, content_id, m2, gebteil, oe_kurzbz, arbeitsplaetze)
VALUES('EG04', 'EDV EG04', 'EG04', 20, true, true, true, NULL, NULL, NULL, NULL, '2023-08-24 14:18:55.000', 'oesi', '2023-08-24 14:17:49.000', 'oesi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO public.tbl_ortraumtyp
(ort_kurzbz, hierarchie, raumtyp_kurzbz)
VALUES('EG01', 1, 'SEM');
INSERT INTO public.tbl_ortraumtyp
(ort_kurzbz, hierarchie, raumtyp_kurzbz)
VALUES('EG02', 1, 'SEM');
INSERT INTO public.tbl_ortraumtyp
(ort_kurzbz, hierarchie, raumtyp_kurzbz)
VALUES('EG04', 1, 'EDV2');
INSERT INTO public.tbl_ortraumtyp
(ort_kurzbz, hierarchie, raumtyp_kurzbz)
VALUES('EG04', 2, 'EDV5/6');
