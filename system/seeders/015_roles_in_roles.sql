-- Create the dummy main role
INSERT INTO system.tbl_rolle (rolle_kurzbz, beschreibung, lange_beschreibung)
VALUES ('dummymainrole', 'Main dummy role', 'This is the main dummy role');

-- Create the dummy basic role
INSERT INTO system.tbl_rolle (rolle_kurzbz, beschreibung, lange_beschreibung)
VALUES ('dummybasicrole', 'Basic dummy role', 'This is the basic dummy role');

-- Link the basic dummy role to the main dummy role 
INSERT INTO system.tbl_rolle_rolle (main_rolle_kurzbz, basic_rolle_kurzbz, insertvon)
VALUES ('dummymainrole', 'dummybasicrole', 'seeders');

-- Create the dummy main permission
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung)
VALUES ('dummymainpermission', 'Main dummy permission');

-- Create the dummy basic permission
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung)
VALUES ('dummybasicpermission', 'Basic dummy permission');

-- Create the dummy user permission
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung)
VALUES ('dummyuserpermission', 'User dummy permission');

-- Link the main dummy permission to the main dummy role 
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art, insertvon)
VALUES ('dummymainpermission', 'dummymainrole', 'suid', 'seeders');

-- Link the basic dummy permission to the basic dummy role 
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art, insertvon)
VALUES ('dummybasicpermission', 'dummybasicrole', 'suid', 'seeders');

-- Link the user dummy permission to the user
INSERT INTO system.tbl_benutzerrolle (rolle_kurzbz, berechtigung_kurzbz, uid, art, insertvon)
VALUES (NULL, 'dummyuserpermission', 'demoadmin', 'suid', 'seeders');

-- Link the main dummy role to the user
INSERT INTO system.tbl_benutzerrolle (rolle_kurzbz, berechtigung_kurzbz, uid, art, insertvon)
VALUES ('dummymainrole', NULL, 'demoadmin', 'suid', 'seeders');

