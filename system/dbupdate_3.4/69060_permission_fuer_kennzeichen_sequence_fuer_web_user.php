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
 */
if (! defined('DB_NAME')) exit('No direct script access allowed');

// GRANT SELECT, UPDATE ON SEQUENCE public.tbl_kennzeichen_id_seq TO web;
if ($result = $db->db_query("SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = 'tbl_kennzeichen_id_seq' AND relacl::TEXT LIKE '%web=%';"))
{
	if ($db->db_num_rows($result) == 0)
	{
		$qry = 'GRANT SELECT, UPDATE ON SEQUENCE public.tbl_kennzeichen_id_seq TO web;';
		if (!$db->db_query($qry))
			echo '<strong>public.tbl_kennzeichen_id_seq '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Granted privileges to <strong>web</strong> on public.tbl_kennzeichen_id_seq';
	}
}