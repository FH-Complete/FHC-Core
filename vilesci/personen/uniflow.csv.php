<?php
/* Copyright (C) 2010 FH Technikum Wien
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
 *          Markus Pospischil <markus.pospischil@technikum-wien.at>
 */

/**
 * CSV Export fuer das UniFlow Drucksystem
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=uniflow.csv");

$qry = "SELECT 
			uid, tbl_organisationseinheit.oe_kurzbz, tbl_organisationseinheit.bezeichnung, tbl_organisationseinheit.organisationseinheittyp_kurzbz
		FROM 
			campus.vw_mitarbeiter 
			JOIN public.tbl_benutzerfunktion USING(uid) 
			JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
		WHERE 
			tbl_benutzerfunktion.funktion_kurzbz='oezuordnung'
			AND vw_mitarbeiter.aktiv=true
			AND tbl_organisationseinheit.aktiv=true
			AND (tbl_benutzerfunktion.datum_bis >= now() OR tbl_benutzerfunktion.datum_bis IS NULL)
			AND (tbl_benutzerfunktion.datum_von <= now() OR tbl_benutzerfunktion.datum_von IS NULL)
		UNION
		SELECT
			uid, tbl_organisationseinheit.oe_kurzbz, tbl_organisationseinheit.bezeichnung, tbl_organisationseinheit.organisationseinheittyp_kurzbz
		FROM 
			campus.vw_student
			JOIN public.tbl_studiengang USING(studiengang_kz)
			JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
		WHERE
			tbl_organisationseinheit.aktiv=true
			AND vw_student.aktiv=true
		";

echo "Login;KSTName;KSTBeschreibung;KSTstandard\n";

$db = new basis_db();

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		echo mb_str_replace('&','und',"$row->uid;$row->organisationseinheittyp_kurzbz $row->bezeichnung;$row->organisationseinheittyp_kurzbz $row->bezeichnung;1\n");
	}
}

?>