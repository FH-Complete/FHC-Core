<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if (!$result = @$db->db_query('SELECT 0 FROM lehre.tbl_kalender_notiz WHERE 0 = 1'))
{
	//TODO zuordnung typ definieren
	$qry = 'CREATE TABLE lehre.tbl_kalender_notiz (
				eindeutige_kalender_gruppen_id UUID NOT NULL,
				notiz_id integer NOT NULL,

				CONSTRAINT tbl_kalender_notiz_pk PRIMARY KEY (eindeutige_kalender_gruppen_id, notiz_id),
				CONSTRAINT tbl_kalender_notiz_notiz_fk FOREIGN KEY (notiz_id)
					REFERENCES public.tbl_notiz (notiz_id)
					ON DELETE CASCADE ON UPDATE CASCADE
			);';

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_kalender_notiz: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_kalender_notiz table created';

	$qry = 'GRANT SELECT ON TABLE lehre.tbl_kalender_notiz TO web;';
	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_kalender_notiz: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>web</strong> on lehre.tbl_kalender_notiz';

	$qry = 'GRANT SELECT, UPDATE, INSERT, DELETE ON TABLE lehre.tbl_kalender_notiz TO vilesci;';
	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_kalender_notiz: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Granted privileges to <strong>vilesci</strong> on lehre.tbl_kalender_notiz';

	// Delete the related note when its calendar-note assignment no longer exists.
	$qry = 'CREATE OR REPLACE FUNCTION lehre.cleanup_notiz_on_kalender_notiz_delete()
		RETURNS trigger
		LANGUAGE plpgsql
		SECURITY DEFINER
		SET search_path = pg_catalog
		AS $function$
		BEGIN
			DELETE FROM public.tbl_notiz
			WHERE notiz_id = OLD.notiz_id
				AND NOT EXISTS (
					SELECT 1
					FROM lehre.tbl_kalender_notiz
					WHERE eindeutige_kalender_gruppen_id = OLD.eindeutige_kalender_gruppen_id
						AND notiz_id = OLD.notiz_id
				);

			RETURN OLD;
		END;
		$function$;

		CREATE TRIGGER cleanup_notiz_on_kalender_notiz_delete
		AFTER DELETE ON lehre.tbl_kalender_notiz
		FOR EACH ROW
		EXECUTE PROCEDURE lehre.cleanup_notiz_on_kalender_notiz_delete();';

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_notiz cleanup trigger: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_notiz cleanup trigger created';
}

// Delete note assignments after a physical delete or when every row in the
// calendar group has an inactive status.
$qry = 'CREATE OR REPLACE FUNCTION lehre.cleanup_kalender_notiz()
		RETURNS trigger
		LANGUAGE plpgsql
		SECURITY DEFINER
		SET search_path = pg_catalog
		AS $function$
		DECLARE
			gruppen_id UUID;
		BEGIN
			IF TG_OP = \'DELETE\' THEN
				gruppen_id := OLD.eindeutige_kalender_gruppen_id;
			ELSIF TG_OP = \'UPDATE\' THEN
				IF (NEW.status_kurzbz IS DISTINCT FROM \'deleted\'
					AND NEW.status_kurzbz IS DISTINCT FROM \'archived\')
					OR OLD.status_kurzbz IS NOT DISTINCT FROM NEW.status_kurzbz
				THEN
					RETURN NEW;
				END IF;

				gruppen_id := NEW.eindeutige_kalender_gruppen_id;
			ELSE
				RETURN NULL;
			END IF;

			IF NOT EXISTS (
				SELECT 1
				FROM lehre.tbl_kalender
				WHERE eindeutige_kalender_gruppen_id = gruppen_id
					AND status_kurzbz IS DISTINCT FROM \'deleted\'
					AND status_kurzbz IS DISTINCT FROM \'archived\'
			)
			THEN
				DELETE FROM lehre.tbl_kalender_notiz
				WHERE eindeutige_kalender_gruppen_id = gruppen_id;
			END IF;

			IF TG_OP = \'DELETE\' THEN
				RETURN OLD;
			END IF;

			RETURN NEW;
		END;
		$function$;';

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_kalender_notiz cleanup function: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_kalender_notiz cleanup function created';

$result = $db->db_query("SELECT 1
	FROM pg_catalog.pg_trigger AS t
	JOIN pg_catalog.pg_class AS c ON c.oid = t.tgrelid
	JOIN pg_catalog.pg_namespace AS n ON n.oid = c.relnamespace
	WHERE n.nspname = 'lehre'
		AND c.relname = 'tbl_kalender'
		AND t.tgname = 'cleanup_kalender_notiz'
		AND NOT t.tgisinternal");

if ($db->db_num_rows($result) === 0)
{
	$qry = 'CREATE TRIGGER cleanup_kalender_notiz
		AFTER DELETE OR UPDATE OF status_kurzbz ON lehre.tbl_kalender
		FOR EACH ROW
		EXECUTE PROCEDURE lehre.cleanup_kalender_notiz();';

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_kalender_notiz cleanup trigger: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_kalender_notiz cleanup trigger created';
}

// Delete resource assignments after a physical delete or when every row in the
// calendar group has an inactive status.
$qry = 'CREATE OR REPLACE FUNCTION lehre.cleanup_betriebsmittel_kalender()
		RETURNS trigger
		LANGUAGE plpgsql
		SECURITY DEFINER
		SET search_path = pg_catalog
		AS $function$
		DECLARE
			gruppen_id UUID;
		BEGIN
			IF TG_OP = \'DELETE\' THEN
				gruppen_id := OLD.eindeutige_kalender_gruppen_id;
			ELSIF TG_OP = \'UPDATE\' THEN
				IF (NEW.status_kurzbz IS DISTINCT FROM \'deleted\'
					AND NEW.status_kurzbz IS DISTINCT FROM \'archived\')
					OR OLD.status_kurzbz IS NOT DISTINCT FROM NEW.status_kurzbz
				THEN
					RETURN NEW;
				END IF;

				gruppen_id := NEW.eindeutige_kalender_gruppen_id;
			ELSE
				RETURN NULL;
			END IF;

			IF NOT EXISTS (
				SELECT 1
				FROM lehre.tbl_kalender
				WHERE eindeutige_kalender_gruppen_id = gruppen_id
					AND status_kurzbz IS DISTINCT FROM \'deleted\'
					AND status_kurzbz IS DISTINCT FROM \'archived\'
			)
			THEN
				DELETE FROM lehre.tbl_betriebsmittel_kalender
				WHERE eindeutige_kalender_gruppen_id = gruppen_id;
			END IF;

			IF TG_OP = \'DELETE\' THEN
				RETURN OLD;
			END IF;

			RETURN NEW;
		END;
		$function$;';

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_betriebsmittel_kalender cleanup function: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_betriebsmittel_kalender cleanup function created';

$result = $db->db_query("SELECT 1
	FROM pg_catalog.pg_trigger AS t
	JOIN pg_catalog.pg_class AS c ON c.oid = t.tgrelid
	JOIN pg_catalog.pg_namespace AS n ON n.oid = c.relnamespace
	WHERE n.nspname = 'lehre'
		AND c.relname = 'tbl_kalender'
		AND t.tgname = 'cleanup_betriebsmittel_kalender'
		AND NOT t.tgisinternal");

if ($db->db_num_rows($result) === 0)
{
	$qry = 'CREATE TRIGGER cleanup_betriebsmittel_kalender
		AFTER DELETE OR UPDATE OF status_kurzbz ON lehre.tbl_kalender
		FOR EACH ROW
		EXECUTE PROCEDURE lehre.cleanup_betriebsmittel_kalender();';

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_betriebsmittel_kalender cleanup trigger: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>lehre.tbl_betriebsmittel_kalender cleanup trigger created';
}

$result = $db->db_query("SELECT 1
	FROM information_schema.columns
	WHERE table_schema = 'public'
		AND table_name = 'tbl_notizzuordnung'
		AND column_name = 'eindeutige_kalender_gruppen_id'");

if ($db->db_num_rows($result) === 1)
{
	$qry = "ALTER TABLE public.tbl_notizzuordnung
		DROP COLUMN eindeutige_kalender_gruppen_id";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_notizzuordnung: '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_notizzuordnung: eindeutige_kalender_gruppen_id column removed';
}
