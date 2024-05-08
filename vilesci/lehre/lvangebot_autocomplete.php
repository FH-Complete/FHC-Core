<?php
/* Copyright (C) 2013 fhcomplete.org
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
 * Authors: Martin Tatzber < tatzberm@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/gruppe.class.php');

	if (!$uid = get_uid())
	die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$rechte = new benutzerberechtigung();
if(!$rechte->getBerechtigungen($uid))
	die('Sie haben keine Berechtigung fuer diese Seite');

if(!$rechte->isBerechtigt('lehre/lehrveranstaltung', null, 's'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if (!$db = new basis_db())
	die('Datenbank kann nicht geoeffnet werden.  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$gruppe_kurzbz=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
$json=array();

$qry="SELECT gruppe_kurzbz FROM public.tbl_gruppe
	WHERE lower(gruppe_kurzbz) LIKE lower('%".$db->db_escape($gruppe_kurzbz)."%')
	AND aktiv=true";
if($result=$db->db_query($qry))
{
	while($row=$db->db_fetch_object($result))
	{
		$item['gruppe_kurzbz']=html_entity_decode($row->gruppe_kurzbz);
		$json[]=$item;
	}
}
echo json_encode($json);
?>