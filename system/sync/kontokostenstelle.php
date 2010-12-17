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

require_once('../../config/wawi.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/wawi_kostenstelle.class.php');

$kostenstelle = new wawi_kostenstelle();
$kostenstelle->getAll();
$db = new basis_db();

foreach($kostenstelle->result as $row)
{
	$qry = "INSERT INTO wawi.tbl_konto_kostenstelle(konto_id, kostenstelle_id, insertamum, insertvon) 
			SELECT konto_id, $row->kostenstelle_id, now(),'oesi' FROM wawi.tbl_konto WHERE kontonr::integer<100
			AND konto_id NOT IN(SELECT konto_id FROM wawi.tbl_konto_kostenstelle WHERE kostenstelle_id='$row->kostenstelle_id')";

	if(!$db->db_query($qry))
		echo 'Failed:'.$qry;
}
echo '<br /><br />DONE';
?>