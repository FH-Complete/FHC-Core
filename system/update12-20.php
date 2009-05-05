<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 ******************************************************************************
 * Beschreibung:
 * Dieses Skript fuehrt Datenbankupdates von Version 1.2 auf 2.0 durch
 */

require_once ('../vilesci/config.inc.php');

// Datenbank Verbindung
//if (!$conn = pg_pconnect("host=.technikum-wien.at dbname= user= password="))
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!'.pg_last_error($conn));

echo '<H1>DB-Updates!</H1>';
echo '<H2>Version 1.2 &rarr; 2.0</H2>';

// **************** lehre.tbl_projektarbeit.sprache *******************************
if(!$result = @pg_query($conn, "SELECT * FROM public.tbl_rolle LIMIT 1;"))
{
	$qry = "ALTER TABLE public.tbl_rolle RENAME TO tbl_status;
			ALTER TABLE public.tbl_prestudentrolle RENAME TO tbl_prestudentstatus;
			ALTER TABLE public.tbl_status RENAME COLUMN rolle_kurzbz TO status_kurzbz;
			ALTER TABLE public.tbl_prestudentstatus RENAME COLUMN rolle_kurzbz TO status_kurzbz;
			UPDATE pg_catalog.pg_constraint SET conname='pk_tbl_status' WHERE conname='pk_tbl_rolle';
			UPDATE pg_catalog.pg_constraint SET conname='orgform_prestudentstatus' WHERE conname='orgform_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='pk_tbl_prestudentstatus' WHERE conname='pk_tbl_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='prestudent_prestudentstatus' WHERE conname='prestudent_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='status_prestudentstatus' WHERE conname='rolle_prestudentrolle';
			UPDATE pg_catalog.pg_constraint SET conname='studiensemester_prestudentstatus' WHERE conname='studiensemester_prestudentrolle';
			";

	if(!pg_query($conn, $qry))
		echo '<strong>public.tbl_rolle: '.pg_last_error($conn).' </strong><br>';
	else
		echo '	public.tbl_rolle: Umbenannt auf tbl_status!<br>
				constrains umbenannt
				public.tbl_prestudentrolle: Umbenannt auf tbl_prestudentstatus!<br>
				constrains umbenannt';
}


?>
