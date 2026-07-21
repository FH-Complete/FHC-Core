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
 * Beschreibung:
 * Dashboard Widgets: Verknüpfung einer (einzelnen) Berechtigung pro Widget.
 * Neue Spalte dashboard.tbl_widget.berechtigung_kurzbz mit FK auf
 * system.tbl_berechtigung. Ist die Spalte NULL, erfordert das Widget keine
 * gesonderte Berechtigung. Benutzer denen die verknüpfte Berechtigung fehlt,
 * bekommen das Widget nicht mehr zur Auswahl angezeigt bzw. sehen (falls bereits
 * am Dashboard) einen "Fehlende Berechtigung"-Screen.
 */
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add column dashboard.tbl_widget.berechtigung_kurzbz (FK => system.tbl_berechtigung)
if ($result = @$db->db_query("
	SELECT 1
	FROM information_schema.columns
	WHERE table_schema = 'dashboard'
		AND table_name = 'tbl_widget'
		AND column_name = 'berechtigung_kurzbz';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = "
			ALTER TABLE dashboard.tbl_widget
				ADD COLUMN berechtigung_kurzbz VARCHAR(32) NULL;

			ALTER TABLE dashboard.tbl_widget
				ADD CONSTRAINT tbl_widget_berechtigung_fk
				FOREIGN KEY (berechtigung_kurzbz)
				REFERENCES system.tbl_berechtigung (berechtigung_kurzbz)
				ON UPDATE CASCADE ON DELETE SET NULL;";

		if (!$db->db_query($qry))
		{
			echo '<strong>dashboard.tbl_widget: '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo 'dashboard.tbl_widget: Spalte berechtigung_kurzbz (FK system.tbl_berechtigung) hinzugefuegt<br>';
		}
	}
}
