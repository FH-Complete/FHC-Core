-- Vollzugriff auf ALLE Anwendungsschemata für alle DB-Rollen.

DO $$
DECLARE
	v_grantee  text;
	v_target   text;
	v_schema   text;
	v_grantees text[] := ARRAY['web', 'vilesci', 'wawi', 'admin', 'PUBLIC'];
	v_done     integer := 0;
BEGIN
	FOREACH v_grantee IN ARRAY v_grantees
	LOOP
		IF v_grantee = 'PUBLIC' THEN
			v_target := 'PUBLIC';
		ELSE
			CONTINUE WHEN NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = v_grantee);
			v_target := quote_ident(v_grantee);
		END IF;

		FOR v_schema IN
			SELECT nspname
			FROM pg_namespace
			WHERE nspname NOT LIKE 'pg\_%' AND nspname <> 'information_schema'
			ORDER BY nspname
		LOOP
			EXECUTE format('GRANT ALL ON SCHEMA %I TO %s', v_schema, v_target);
			EXECUTE format('GRANT ALL ON ALL TABLES IN SCHEMA %I TO %s', v_schema, v_target);
			EXECUTE format('GRANT ALL ON ALL SEQUENCES IN SCHEMA %I TO %s', v_schema, v_target);
			EXECUTE format('GRANT ALL ON ALL FUNCTIONS IN SCHEMA %I TO %s', v_schema, v_target);

			EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA %I GRANT ALL ON TABLES TO %s', v_schema, v_target);
			EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA %I GRANT ALL ON SEQUENCES TO %s', v_schema, v_target);
			EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA %I GRANT ALL ON FUNCTIONS TO %s', v_schema, v_target);

			v_done := v_done + 1;
		END LOOP;
	END LOOP;

	RAISE NOTICE 'Grants auf % Schema/Rollen-Kombinationen vergeben.', v_done;
END $$;
