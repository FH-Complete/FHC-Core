<?php
/* Copyright (C) 2026 fhcomplete.org
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
 * Authors: Christopher Hacker <christopher.hacker@technikum-wien.at>,
 *
 * Description:
 * Cleanup Dashboard DB data
 */
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Cleanup presets
if ($result = @$db->db_query("
	SELECT 1 
	FROM dashboard.tbl_dashboard_preset 
	WHERE preset ? COALESCE(funktion_kurzbz, 'general') 
	OR preset ? 'custom' 
	LIMIT 1
")) {
	if ($db->db_num_rows($result)) {
		$qry = "
			UPDATE dashboard.tbl_dashboard_preset 
			SET preset = COALESCE(preset->COALESCE(funktion_kurzbz, 'general'), preset->'custom')->'widgets'
			WHERE preset ? COALESCE(funktion_kurzbz, 'general') 
			OR preset ? 'custom' 
		";

		$result = $db->db_query($qry);

		if (!$result) {
			echo '<strong>dashboard.tbl_dashboard_preset '.$db->db_last_error().'</strong><br>';
		} else {
			$affected_rows = $db->db_affected_rows($result);
			echo 'dashboard.tbl_dashboard_preset: ' . $affected_rows . ' rows migrated<br>';
		}
	}
}

// Cleanup user overrides
if ($result = @$db->db_query("
	SELECT 1
	FROM dashboard.tbl_dashboard_benutzer_override
	WHERE EXISTS (
		SELECT 1
		FROM jsonb_each(override)
		WHERE value ? 'widgets'
		LIMIT 1
	) AND override <> '[]'::jsonb
	LIMIT 1
")) {
	if ($db->db_num_rows($result)) {
		$qry = "
			UPDATE dashboard.tbl_dashboard_benutzer_override 
			SET override = COALESCE((
				SELECT json_object_agg(key, value) FROM (
					SELECT value->'widgets' AS widgets 
					FROM jsonb_each(override) 
					WHERE jsonb_typeof(value->'widgets') = 'object'
				) x, jsonb_each(widgets)
			), '[]')
			WHERE EXISTS (
			    SELECT 1
			    FROM jsonb_each(override)
			    WHERE value ? 'widgets'
			    LIMIT 1
			) AND override <> '[]'::jsonb
		";

		$result = $db->db_query($qry);

		if (!$result) {
			echo '<strong>dashboard.tbl_dashboard_benutzer_override '.$db->db_last_error().'</strong><br>';
		} else {
			$affected_rows = $db->db_affected_rows($result);
			echo 'dashboard.tbl_dashboard_benutzer_override: ' . $affected_rows . ' rows migrated<br>';
		}
	}
}
