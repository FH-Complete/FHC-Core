INSERT INTO wawi.tbl_betriebsmitteltyp
(betriebsmitteltyp, beschreibung, anzahl, kaution, typ_code, mastershapename)
VALUES('Inventar', NULL, NULL, NULL, NULL, NULL);

INSERT INTO wawi.tbl_betriebsmittel
(betriebsmittel_id, beschreibung, betriebsmitteltyp, nummer, reservieren, updateamum, updatevon, insertamum, insertvon, ext_id, inventarnummer, oe_kurzbz, ort_kurzbz, hersteller, seriennummer, bestellung_id, bestelldetail_id, afa, verwendung, anmerkung, leasing_bis, inventuramum, inventurvon, anschaffungswert, anschaffungsdatum, tiefe, hoehe, breite, nummer2, verplanen)
VALUES
(1, 'pc', 'Laptop', NULL, false, '2024-03-07 08:17:33.000', 'demoadmin', '2015-12-01 14:55:32.000', 'demoadmin', NULL, '00001', NULL, NULL, 'pc', NULL, NULL, NULL, 3, NULL, 'Angelegt von demoadmin für die test', NULL, '2015-12-01 14:55:32.000', 'demoadmin', NULL, NULL, NULL, NULL, NULL, NULL, true),
(2, 'projector', 'Inventar', NULL, false, '2024-03-07 08:17:33.000', 'demoadmin', '2015-12-01 14:55:32.000', 'demoadmin', NULL, '00002', NULL, NULL, 'projector', NULL, NULL, NULL, 3, NULL, 'Angelegt von demoadmin für die test', NULL, '2015-12-01 14:55:32.000', 'demoadmin', NULL, NULL, NULL, NULL, NULL, NULL, true),
(3, 'workstation', 'Inventar', NULL, false, '2024-03-07 08:17:33.000', 'demoadmin', '2015-12-01 14:55:32.000', 'demoadmin', NULL, '00003', NULL, NULL, 'workstation', NULL, NULL, NULL, 3, NULL, 'Angelegt von demoadmin für die test', NULL, '2015-12-01 14:55:32.000', 'demoadmin', NULL, NULL, NULL, NULL, NULL, NULL, true),
(4, 'pc2', 'Laptop', NULL, false, '2024-03-07 08:17:33.000', 'demoadmin', '2015-12-01 14:55:32.000', 'demoadmin', NULL, '00004', NULL, NULL, 'pc2', NULL, NULL, NULL, 3, NULL, 'Angelegt von demoadmin für die test', NULL, '2015-12-01 14:55:32.000', 'demoadmin', NULL, NULL, NULL, NULL, NULL, NULL, true);
