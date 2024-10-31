<?php
if (!defined('DB_NAME')) exit('No direct script access allowed');

// Activate module pg_trgm
if (!$db->db_num_rows(@$db->db_query("SELECT 1 
FROM pg_extension WHERE extname = 'pg_trgm' LIMIT 1;")))
{
	$qry = "CREATE extension pg_trgm;";

	if (!$db->db_query($qry))
		echo '<strong>Module pg_trgm ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'Module pg_trgm: activated<br>';
}


// Add additional computed columns
// Add column fts_bezeichnung to public.tbl_organisationseinheit
if (!@$db->db_query("SELECT fts_bezeichnung FROM public.tbl_organisationseinheit LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_organisationseinheit ADD COLUMN fts_bezeichnung tsvector;";
	$qry .= "COMMENT ON COLUMN public.tbl_organisationseinheit.fts_bezeichnung IS 'used for search - auto generated w triggers';";

	if (!$db->db_query($qry))
		echo '<strong> public.tbl_organisationseinheit ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'public.tbl_organisationseinheit: new column "fts_bezeichnung" added<br>';
}

// Add function tr_update_tbl_organisationseinheit_fts_bezeichnung to public
if (!$db->db_num_rows(@$db->db_query("SELECT 1 FROM pg_proc WHERE proname = 'tr_update_tbl_organisationseinheit_fts_bezeichnung' LIMIT 1;")))
{
	$qry = "CREATE FUNCTION tr_update_tbl_organisationseinheit_fts_bezeichnung()
		RETURNS TRIGGER 
		LANGUAGE PLPGSQL
		AS
		$$
		BEGIN
			IF TG_TABLE_NAME = 'tbl_organisationseinheit' THEN
				NEW.fts_bezeichnung := to_tsvector('simple', COALESCE((SELECT bezeichnung FROM public.tbl_organisationseinheittyp WHERE organisationseinheittyp_kurzbz = NEW.organisationseinheittyp_kurzbz), '') || ' ' || COALESCE(NEW.bezeichnung, ''));
			ELSIF TG_TABLE_NAME = 'tbl_organisationseinheittyp' THEN
				UPDATE public.tbl_organisationseinheit SET fts_bezeichnung = to_tsvector('simple', COALESCE(NEW.bezeichnung, '') || ' ' || COALESCE(bezeichnung, '')) WHERE organisationseinheittyp_kurzbz = NEW.organisationseinheittyp_kurzbz;
			END IF;
			RETURN NEW;
		END;
		$$";

	if (!$db->db_query($qry))
		echo '<strong> public.tr_update_tbl_organisationseinheit_fts_bezeichnung ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'public.tr_update_tbl_organisationseinheit_fts_bezeichnung: function created<br>';
}

$update_column = false;
// Add trigger tr_organisationseinheit_update_organisationseinheittyp_kurzbz to public.tbl_organisationseinheit
if (!$db->db_num_rows(@$db->db_query("SELECT 1 FROM information_schema.triggers WHERE event_object_table ='tbl_organisationseinheit' AND trigger_name = 'tr_organisationseinheit_update_organisationseinheittyp_kurzbz' LIMIT 1;")))
{
	$qry = "CREATE TRIGGER tr_organisationseinheit_update_organisationseinheittyp_kurzbz
		BEFORE UPDATE OF organisationseinheittyp_kurzbz OR INSERT
		ON public.tbl_organisationseinheit
		FOR EACH ROW
		EXECUTE FUNCTION tr_update_tbl_organisationseinheit_fts_bezeichnung();";

	if (!$db->db_query($qry))
		echo '<strong> public.tbl_organisationseinheit ' . $db->db_last_error() . '</strong><br>';
	else {
		echo 'public.tbl_organisationseinheit: trigger "tr_organisationseinheit_update_organisationseinheittyp_kurzbz" created<br>';
		$update_column = true;
	}
}

// Add trigger tr_organisationseinheittyp_update_bezeichnung to public.tbl_organisationseinheittyp
if (!$db->db_num_rows(@$db->db_query("SELECT 1 FROM information_schema.triggers WHERE event_object_table ='tbl_organisationseinheittyp' AND trigger_name = 'tr_organisationseinheittyp_update_bezeichnung' LIMIT 1;")))
{
	$qry = "CREATE TRIGGER tr_organisationseinheittyp_update_bezeichnung
		BEFORE UPDATE OF bezeichnung
		ON public.tbl_organisationseinheittyp
		FOR EACH ROW
		EXECUTE FUNCTION tr_update_tbl_organisationseinheit_fts_bezeichnung();";

	if (!$db->db_query($qry))
		echo '<strong> public.tbl_organisationseinheittyp ' . $db->db_last_error() . '</strong><br>';
	else {
		echo 'public.tbl_organisationseinheittyp: trigger "tr_organisationseinheittyp_update_bezeichnung" created<br>';
		$update_column = true;
	}
}

// Update fts_bezeichnung on tbl_organisationseinheit with new triggers
if ($update_column)
{
	$qry = "UPDATE public.tbl_organisationseinheittyp SET bezeichnung = bezeichnung;";

	if (!$db->db_query($qry))
		echo '<strong> public.tbl_organisationseinheit ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'public.tbl_organisationseinheit: column "fts_bezeichnung" updated<br>';
}


// Add Trigram Indexes
// Add index for kontakt to public.tbl_kontakt
if ($result = @$db->db_query("SELECT 1 
FROM pg_indexes WHERE indexname = 'idx_tbl_kontakt_kontakt_trgm';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_kontakt_kontakt_trgm ON public.tbl_kontakt USING GIN (COALESCE(kontakt, '') gin_trgm_ops);";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_kontakt ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'public.tbl_kontakt: added index "idx_tbl_kontakt_kontakt_trgm"<br>';
	}
}
// Add index for vorname to public.tbl_person
if ($result = @$db->db_query("SELECT 1 
FROM pg_indexes WHERE indexname = 'idx_tbl_person_vorname_trgm';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_person_vorname_trgm ON public.tbl_person USING GIN (COALESCE(vorname, '') gin_trgm_ops);";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_person ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'public.tbl_person: added index "idx_tbl_person_vorname_trgm"<br>';
	}
}
// Add index for nachname to public.tbl_person
if ($result = @$db->db_query("SELECT 1 
FROM pg_indexes WHERE indexname = 'idx_tbl_person_nachname_trgm';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_person_nachname_trgm ON public.tbl_person USING GIN (COALESCE(nachname, '') gin_trgm_ops);";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_person ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'public.tbl_person: added index "idx_tbl_person_nachname_trgm"<br>';
	}
}
// Add index for vorname || ' ' || nachname to public.tbl_person
if ($result = @$db->db_query("SELECT 1 
FROM pg_indexes WHERE indexname = 'idx_tbl_person_name_trgm';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "CREATE INDEX idx_tbl_person_name_trgm ON public.tbl_person USING GIN (COALESCE((vorname || ' ' || nachname), '') gin_trgm_ops);";

		if (!$db->db_query($qry))
			echo '<strong>public.tbl_person ' . $db->db_last_error() . '</strong><br>';
		else
			echo 'public.tbl_person: added index "idx_tbl_person_name_trgm"<br>';
	}
}


// Add Vector Indexes
// Add index for fts_bezeichnung to public.tbl_organisationseinheit
if (!$db->db_num_rows(@$db->db_query("SELECT 1 
FROM pg_indexes WHERE indexname = 'idx_tbl_organisationseinheit_fts_bezeichnung_vector' LIMIT 1;")))
{
	$qry = "CREATE INDEX idx_tbl_organisationseinheit_fts_bezeichnung_vector ON public.tbl_organisationseinheit USING GIN (fts_bezeichnung);";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_organisationseinheit ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'public.tbl_organisationseinheit: added index "idx_tbl_organisationseinheit_fts_bezeichnung_vector"<br>';
}
// Add index for titel || ' ' || content to campus.tbl_contentsprache
if (!$db->db_num_rows(@$db->db_query("SELECT 1 
FROM pg_indexes WHERE indexname = 'idx_tbl_contentsprache_fts_titel_content_vector' LIMIT 1;")))
{
	$qry = "
		CREATE INDEX idx_tbl_contentsprache_fts_titel_content_vector 
		ON campus.tbl_contentsprache 
		USING GIN ((setweight(to_tsvector('simple', COALESCE(titel, '')), 'A') || setweight(to_tsvector('simple', COALESCE(content, '')::text), 'B')));
	";

	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_contentsprache ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'campus.tbl_contentsprache: added index "idx_tbl_contentsprache_fts_titel_content_vector"<br>';
}
// Add index for schlagworte to campus.tbl_dms_version
if (!$db->db_num_rows(@$db->db_query("SELECT 1 
FROM pg_indexes WHERE indexname = 'idx_tbl_dms_version_fts_schlagworte_vector' LIMIT 1;")))
{
	$qry = "
		CREATE INDEX idx_tbl_dms_version_fts_schlagworte_vector 
		ON campus.tbl_dms_version 
		USING GIN ((to_tsvector('simple', COALESCE(schlagworte, ''))));
	";

	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_dms_version ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'campus.tbl_contentsprache: added index "idx_tbl_dms_version_fts_schlagworte_vector"<br>';
}
