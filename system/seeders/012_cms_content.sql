INSERT INTO campus.tbl_content
(content_id, template_kurzbz, oe_kurzbz, insertamum, insertvon, updateamum, updatevon, aktiv, menu_open, beschreibung) VALUES
(30, 'contentmittitel', 'etw', now(), 'demoadmin', now(), 'demoadmin', true, false, NULL),
(32, 'contentmittitel', 'etw', now(), 'demoadmin', now(), 'demoadmin', true, false, NULL),
(33, 'redirect', 'etw', now(), 'demoadmin', now(), 'demoadmin', true, false, NULL),
(34, 'redirect', 'etw', now(), 'demoadmin', now(), 'demoadmin', true, false, NULL),
(35, 'redirect', 'etw', now(), 'demoadmin', now(), 'demoadmin', true, false, NULL),
(36, 'redirect', 'etw', now(), 'demoadmin', now(), 'demoadmin', true, false, NULL),
(37, 'redirect', 'etw', now(), 'demoadmin', now(), 'demoadmin', true, false, NULL),
(38, 'redirect', 'etw', now(), 'demoadmin', now(), 'demoadmin', true, false, NULL)
;

INSERT INTO campus.tbl_contentsprache (sprache, content_id, "version", sichtbar, "content", reviewvon, reviewamum, updateamum, updatevon, insertamum, insertvon, titel, gesperrt_uid) VALUES
('German', 30, 1, true, '<content></content>', NULL, NULL, now(), 'demoadmin', now(), 'demoadmin', 'CIS4', NULL),
('German', 32, 1, true, '<content></content>', NULL, NULL, now(), 'demoadmin', now(), 'demoadmin', 'Studium', NULL),
('German', 33, 1, true, '<content><url><![CDATA[../cis.php/Cis/Stundenplan]]></url><target><![CDATA[]]></target></content>', NULL, NULL, now(), 'demoadmin', now(), 'demoadmin', 'LV-Plan', NULL),
('German', 34, 1, true, '<content><url><![CDATA[../cis.php/Cis4]]></url><target><![CDATA[]]></target></content>', NULL, NULL, now(), 'demoadmin', now(), 'demoadmin', 'Dahsboard', NULL),
('German', 35, 1, true, '<content><url><![CDATA[../cis.php/Cis/MyLv]]></url><target><![CDATA[]]></target></content>', NULL, NULL, now(), 'demoadmin', now(), 'demoadmin', 'Meine LVs', NULL),
('German', 36, 1, true, '<content><url><![CDATA[../cis.php/Cis/Studium]]></url><target><![CDATA[]]></target></content>', NULL, NULL, now(), 'demoadmin', now(), 'demoadmin', 'Studium', NULL),
('German', 37, 1, true, '<content><url><![CDATA[../cis.php/Cis/Profil]]></url><target><![CDATA[]]></target></content>', NULL, NULL, now(), 'demoadmin', now(), 'demoadmin', 'Profil', NULL),
('German', 38, 1, true, '<content><url><![CDATA[../cis.php/Cis/Documents]]></url><target><![CDATA[]]></target></content>', NULL, NULL, now(), 'demoadmin', now(), 'demoadmin', 'Dokumente', NULL)
;

INSERT INTO campus.tbl_contentchild (content_id, child_content_id, insertamum, insertvon, updateamum, updatevon, sort) VALUES
(30, 37, now(), 'demoadmin', NULL, NULL, 5),
(30, 27, now(), 'demoadmin', NULL, NULL, 7),
(30, 32, now(), 'demoadmin', NULL, NULL, 2),
(30, 34, now(), 'demoadmin', NULL, NULL, 1),
(32, 33, now(), 'demoadmin', NULL, NULL, 1),
(32, 36, now(), 'demoadmin', NULL, NULL, 2),
(32, 38, now(), 'demoadmin', NULL, NULL, 3),
(32, 35, now(), 'demoadmin', NULL, NULL, 4)
;

INSERT INTO campus.tbl_contentgruppe (content_id, gruppe_kurzbz, insertamum, insertvon) VALUES
(30, 'MA', now(), 'demoadmin'),
(30, 'STUD', now(), 'demoadmin')
;
