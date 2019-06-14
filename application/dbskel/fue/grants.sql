-----------------------------------------------------
-- Revokes all privileges from all granted users
-----------------------------------------------------
REVOKE ALL PRIVILEGES ON SCHEMA fue FROM vilesci;
REVOKE ALL ON ALL TABLES IN SCHEMA fue FROM vilesci;
REVOKE ALL ON ALL SEQUENCES IN SCHEMA fue FROM vilesci;
REVOKE ALL ON ALL FUNCTIONS IN SCHEMA fue FROM vilesci;

----------------------------------------------------------------------------------------------------
-- Gives the desired privileges to the chosen users (with great power comes great responsibility!)
----------------------------------------------------------------------------------------------------

-- Schema privileges
GRANT ALL ON SCHEMA fue TO vilesci;
GRANT USAGE ON SCHEMA fue TO web;
GRANT USAGE ON SCHEMA fue TO wawi;

-- Sequences privileges
GRANT SELECT,UPDATE ON SEQUENCE fue.seq_projekt_dokument_projekt_dokument_id TO vilesci;
GRANT SELECT,UPDATE ON SEQUENCE fue.seq_projekt_dokument_projekt_dokument_id TO web;
