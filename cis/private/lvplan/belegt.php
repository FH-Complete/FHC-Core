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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
/**
 * Zeigt an in welchen Stunden des heutigen Tages der Raum 5.26 reserviert ist.
 * Dies dient zur Freischaltung des WLANS
 */
require_once(dirname(__FILE__).'/../../../config/cis.config.inc.php');
require_once(dirname(__FILE__).'/../../../include/basis_db.class.php');

$datum = date('Y-m-d');
$stunden=array();
for($i=1;$i<=16;$i++)
	$stunden[$i]='nein';

$qry = "
SELECT stunde FROM campus.tbl_reservierung WHERE datum='$datum' AND ort_kurzbz='BR_A5.26'
UNION
SELECT stunde FROM lehre.tbl_stundenplan WHERE datum='$datum' AND ort_kurzbz='BR_A5.26'
";

$db = new basis_db();

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$stunden[$row->stunde]='ja';
	}
}

foreach($stunden as $stunde=>$reserviert)
	echo $stunde.' '.$reserviert."\n";
?>