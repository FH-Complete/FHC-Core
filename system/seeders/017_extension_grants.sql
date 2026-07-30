-- Grant all db users access to the "extension" schema.
--
-- The dump creates the schema and gives it no grants, unlike public/lehre/campus, and
-- truncate_db.sql only grants on public. Any application not connecting as the owner then fails
-- every query in that schema with "permission denied for schema extension"

DO $$
DECLARE
	v_role  text;
	v_roles text[] := ARRAY['web', 'vilesci', 'wawi', 'admin'];
	v_done  integer := 0;
BEGIN
	IF NOT EXISTS (SELECT 1 FROM pg_namespace WHERE nspname = 'extension') THEN
		RAISE NOTICE 'Schema "extension" does not exist, skipped.';
		RETURN;
	END IF;

	FOREACH v_role IN ARRAY v_roles
	LOOP
		CONTINUE WHEN NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = v_role);

		EXECUTE format('GRANT USAGE ON SCHEMA extension TO %I', v_role);
		EXECUTE format('GRANT SELECT ON ALL TABLES IN SCHEMA extension TO %I', v_role);
		EXECUTE format('GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA extension TO %I', v_role);
		-- and for whatever an addon creates later
		EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA extension GRANT SELECT ON TABLES TO %I', v_role);
		EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA extension GRANT EXECUTE ON FUNCTIONS TO %I', v_role);

		v_done := v_done + 1;
	END LOOP;

	RAISE NOTICE 'Granted on schema "extension" for % role(s).', v_done;
END $$;
