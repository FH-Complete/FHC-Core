<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/benutzer.class.php'); 
	
if (!$db = new basis_db())
    die('Es konnte keine Verbindung zum Server aufgebaut werden.');
$uid=get_uid();
if(isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete']=='kunde')
{
	$search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
	if (is_null($search) ||$search=='')
		exit();	
    
    $benutzer = new benutzer();
    
	if($benutzer->search(array($search)))
	{
		$result_obj = array();
		foreach($benutzer->result as $row)
		{
			$item['vorname']=html_entity_decode($row->vorname);
			$item['nachname']=html_entity_decode($row->nachname);
			$item['uid']=html_entity_decode($row->uid);
			$result_obj[]=$item;
		}
		echo json_encode($result_obj);
	}
	exit;
}
?>
