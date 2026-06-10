<?php
/* Copyright (C) 2017 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Harald Bamberger <harald.bamberger@technikum-wien.at>,
 *
 * Beschreibung:
 * Dashboard DB Aenderungen
 */
if (! defined('DB_NAME')) exit('No direct script access allowed');

$corewidgetssql = 'select tw.* from dashboard.tbl_widget tw where regexp_match((setup->\'file\')::text, \'^"DashboardWidget\') is not NULL';
if (($rescore = $db->db_query($corewidgetssql)))
{
	if ($db->db_num_rows($rescore) > 0)
	{
		$coreqry = <<<EOCOREQRY

		update
			dashboard.tbl_widget
		set
			setup = jsonb_set(setup, '{file}', regexp_replace((setup->'file')::text, '^"DashboardWidget', '"public/js/components/DashboardWidget')::jsonb)
		where
			regexp_match((setup->'file')::text, '^"DashboardWidget') is not NULL;

EOCOREQRY;

		if (!$db->db_query($coreqry))
		{
			echo '<strong>Dashboard Core Widgets Paths Update: '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo '<br>Dashboard Core Widgets Paths updated.';
		}
	}
}

$extwidgetssql = 'select tw.* from dashboard.tbl_widget tw where regexp_match((setup->\'file\')::text, \'^"../../\') is not NULL';
if (($resext = $db->db_query($extwidgetssql)))
{
	if ($db->db_num_rows($resext) > 0)
	{
		$extqry = <<<EOEXTQRY

		update
			dashboard.tbl_widget
		set
			setup = jsonb_set(setup, '{file}', regexp_replace((setup->'file')::text, '^"../../', '"public/')::jsonb)
		where
			regexp_match((setup->'file')::text, '^"../../') is not NULL;

EOEXTQRY;

		if (!$db->db_query($extqry))
		{
			echo '<strong>Dashboard Extensions Widgets Paths Update: '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo '<br>Dashboard Extensions Widgets Paths updated.';
		}
	}
}