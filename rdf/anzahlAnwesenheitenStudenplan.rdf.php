<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at>
 */
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/studiensemester.class.php');

$db = new basis_db();

$uid = $_GET["uid"];
$studiensemester = $_GET["studiensemester"];
$studiensemester = new studiensemester($studiensemester);

$rdf_url='http://www.technikum-wien.at/anzahlLehreinheiten/';

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ANZAHLLEHREINHEITEN="'.$rdf_url.'rdf#"
>

   <RDF:Seq about="'.$rdf_url.'liste">
';

$qry = 'SELECT COUNT(DISTINCT(datum)) as anzahl from lehre.tbl_stundenplan '
	. 'WHERE mitarbeiter_uid='.$db->db_add_param($uid)
	. ' AND (datum BETWEEN '.$db->db_add_param($studiensemester->start).' AND '.$db->db_add_param($studiensemester->ende).');';

if($db->db_query($qry))
{
    if($db->db_num_rows() == 1)
    {
	if($row = $db->db_fetch_object())
	{
	    echo '<RDF:li>
		    <RDF:Description about="'.$rdf_url.$uid.'">
			<ANZAHLLEHREINHEITEN:anzahl><![CDATA['.$row->anzahl.']]></ANZAHLLEHREINHEITEN:anzahl>'
		    .'</RDF:Description>'
		. '</RDF:li>';
	}
    }
}
else
{
    echo "test";
}
?>
    </RDF:Seq>
</RDF:RDF>