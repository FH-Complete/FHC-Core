<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

$qry = "
	    CREATE OR REPLACE VIEW public.vw_oe_path AS
		WITH RECURSIVE vw_oe_path(oe_kurzbz, bezeichnung, oe_parent_kurzbz, organisationseinheittyp_kurzbz, oetyp_bezeichnung, depth, path, path_kurzbz) AS (
		  SELECT 
		    oe.oe_kurzbz, oe.bezeichnung, oe.oe_parent_kurzbz, oe.organisationseinheittyp_kurzbz, oetyp.bezeichnung AS oetyp_bezeichnung, 0, '/' || oetyp.bezeichnung || ' ' || oe.bezeichnung AS path, '/' || oe.oe_kurzbz AS path_kurzbz
		  FROM 
		    public.tbl_organisationseinheit oe
		  JOIN
		    public.tbl_organisationseinheittyp oetyp USING(organisationseinheittyp_kurzbz)
		  WHERE
		    oe.oe_parent_kurzbz IS NULL
		  UNION ALL
		  SELECT 
		    oe.oe_kurzbz, oe.bezeichnung, oe.oe_parent_kurzbz, oe.organisationseinheittyp_kurzbz, oetyp.bezeichnung AS oetyp_bezeichnung, depth + 1, oet.path || '/'  || oetyp.bezeichnung || ' ' || oe.bezeichnung, oet.path_kurzbz || '/' || oe.oe_kurzbz AS path_kurzbz
		  FROM 
		    public.tbl_organisationseinheit oe, vw_oe_path oet
		  JOIN
		    public.tbl_organisationseinheittyp oetyp USING(organisationseinheittyp_kurzbz)
		  WHERE
		    oe.oe_parent_kurzbz = oet.oe_kurzbz
		)
		SELECT * FROM vw_oe_path ORDER BY path, depth;

		GRANT SELECT ON public.vw_oe_path TO vilesci;
";


if ($result = $db->db_query("SELECT * FROM information_schema.views WHERE table_catalog = '" . DB_NAME . "' AND table_schema = 'public' AND table_name = 'vw_oe_path'"))
{
    if($db->db_num_rows($result) == 0)
    {
	if (!$db->db_query($qry))
		echo '<strong>public.vw_oe_path: ' . $db->db_last_error() . '</strong><br />';
	else
		echo 'public.vw_oe_path: erstellt<br />';
    }
}

if ($result = $db->db_query("SELECT * FROM information_schema.columns WHERE table_catalog = '" . DB_NAME . "' AND table_schema = 'public' AND table_name = 'vw_oe_path' AND column_name = 'path_kurzbz'"))
{
    if($db->db_num_rows($result) == 0)
    {
	if (!$db->db_query($qry))
		echo '<strong>public.vw_oe_path: ' . $db->db_last_error() . '</strong><br />';
	else
		echo 'public.vw_oe_path: neu erstellt mit zusätzlicher spalte path_kurzbz<br />';
    }
}
