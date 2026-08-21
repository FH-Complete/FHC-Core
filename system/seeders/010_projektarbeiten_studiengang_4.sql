INSERT INTO lehre.tbl_projektarbeit
(projektarbeit_id, projekttyp_kurzbz, titel, lehreinheit_id, student_uid, firma_id, note, punkte, beginn, ende, faktor, freigegeben, gesperrtbis, stundensatz, themenbereich, anmerkung, updateamum, updatevon, insertamum, insertvon, ext_id, gesamtstunden, titel_english, sprache, seitenanzahl, abgabedatum, kontrollschlagwoerter, schlagwoerter, schlagwoerter_en, abstract, abstract_en, "final") VALUES
(1, 'Bachelor', 'Auswirkung der Sonneneinstrahlung auf die Gesundheit', 46111, 's46b413', NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 14:06:10.000', 'anondata', NULL, 3.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, true),
(2, 'Bachelor', 'Neue Unterrichtsmethoden mit KI', 46111, 's46b414', NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 14:06:10.000', 'anondata', NULL, 3.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, true),
(3, 'Bachelor', 'Betrugsbekämpfung bei Anwesenheitskontrolle', 46111, 's46b415', NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 14:06:10.000', 'anondata', NULL, 3.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, true),
(4, 'Bachelor', 'Neuronale Netze', 46111, 's46b416', NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 14:06:10.000', 'anondata', NULL, 3.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, true)
;

INSERT INTO lehre.tbl_projektbetreuer
(person_id, projektarbeit_id, note, faktor, "name", punkte, stundensatz, updateamum, updatevon, insertamum, insertvon, ext_id, betreuerart_kurzbz, stunden, zugangstoken, zugangstoken_gueltigbis, vertrag_id) VALUES
(21, 1, NULL, NULL, NULL, NULL, 80.00, NULL, NULL, '2025-12-12 14:07:10.000', 'anondata', NULL, 'Begutachter', 4.0000, NULL, NULL, NULL),
(21, 2, NULL, NULL, NULL, NULL, 80.00, NULL, NULL, '2025-12-12 14:07:10.000', 'anondata', NULL, 'Begutachter', 4.0000, NULL, NULL, NULL),
(21, 3, NULL, NULL, NULL, NULL, 80.00, NULL, NULL, '2025-12-12 14:07:10.000', 'anondata', NULL, 'Begutachter', 4.0000, NULL, NULL, NULL),
(21, 4, NULL, NULL, NULL, NULL, 80.00, NULL, NULL, '2025-12-12 14:07:10.000', 'anondata', NULL, 'Begutachter', 4.0000, NULL, NULL, NULL)
;

