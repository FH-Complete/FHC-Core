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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

	header( 'Expires:  -1' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Pragma: no-cache' );
	header('Content-Type: text/html;charset=UTF-8');

	require_once('../config/wawi.config.inc.php');
	require_once('auth.php');
  	require_once('../include/functions.inc.php');
	require_once('../include/benutzerberechtigung.class.php');
	require_once('../include/mitarbeiter.class.php');
  	require_once ('../include/firma.class.php');
  	require_once('../include/standort.class.php');
  	require_once ('../include/tags.class.php');

  	if (!$uid = get_uid())
		die('Keine UID gefunden:'.$uid.' !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$rechte = new benutzerberechtigung();
	if(!$rechte->getBerechtigungen($uid))
		die('Sie haben keine Berechtigung fuer diese Seite');
	
// ------------------------------------------------------------------------------------------
// Initialisierung
// ------------------------------------------------------------------------------------------
	$errormsg=array();
	$default_status_vorhanden='vorhanden';

// ------------------------------------------------------------------------------------------
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------
  	$debug=trim(isset($_REQUEST['debug']) ? $_REQUEST['debug']:false);

  	$work=trim(isset($_REQUEST['work'])?$_REQUEST['work']:(isset($_REQUEST['ajax'])?$_REQUEST['ajax']:false));
	$work=strtolower($work);

// ------------------------------------------------------------------------------------------
//	Datenlesen
// ------------------------------------------------------------------------------------------
	switch ($work)
	{			
			// Firmen Search
		case 'wawi_firma_search':
		 	$firma_search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($firma_search) ||$firma_search=='')
				exit();	
			$sFirma = new firma();
			if (!$sFirma->getAll($firma_search))
				exit($sFirma->errormsg."\n");
			
			$result=array();
			for ($i=0;$i<count($sFirma->result);$i++)
			{
				$standort = new standort();
				$standort->load_firma($sFirma->result[$i]->firma_id);
				if(isset($standort->result[0]))
					$kurzbz = $standort->result[0]->kurzbz;
				else
					$kurzbz = '';
				$item['gesperrt']=html_entity_decode($sFirma->result[$i]->gesperrt?'!!GESPERRT!! ':'');
				$item['name']=html_entity_decode($sFirma->result[$i]->name);
				$item['kurzbz']=$kurzbz;
				$item['firma_id']=html_entity_decode($sFirma->result[$i]->firma_id);
				$result[]=$item;
//				echo html_entity_decode(($sFirma->result[$i]->gesperrt?'!!GESPERRT!! ':'').$sFirma->result[$i]->name).($kurzbz!=''?' ('.$kurzbz.')':'').'|'.html_entity_decode($sFirma->result[$i]->firma_id)."\n";
			}
			echo json_encode($result);
			break;
			
			// Bestellung Tags
		case 'tags':
		//	$bestell_id = $_REQUEST['bestell_id'];
			$tag_search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
		//	if (is_null($bestell_id) || $tag_search=='')
			//	exit();	
			$tags = new tags(); 
			if (!$tags->getAll())
				exit($tags->errormsg."\n");
			
			$result=array();
			for ($i=0;$i<count($tags->result);$i++)
			{
				$item['tag']=$tags->result[$i]->tag;
				$result[]=$item;
//				echo html_entity_decode($tags->result[$i]->tag)."\n";
			}
			echo json_encode($result);
			break;
			
			// Bestelldetail Tags
		case 'detail_tags':
		//	$detail = $_REQUEST['detail_id'];
			$tag_search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			//if (is_null($detail) || $tag_search=='')
			//	exit();	
			$tags = new tags(); 
			if (!$tags->getAll())
				exit($tags->errormsg."\n");
			$result=array();
			for ($i=0;$i<count($tags->result);$i++)
			{
				$item['tag']=$tags->result[$i]->tag;
				$result[]=$item;
//				echo html_entity_decode($tags->result[$i]->tag)."\n";
			}
			echo json_encode($result);
			break;
		
		case 'wawi_mitarbeiter_search':
		 	$search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
			if (is_null($search) ||$search=='')
				exit();	
			$ma = new mitarbeiter();
			$ma->search($search);
	
			$result=array();
			foreach($ma->result as $row)
			{
				$item['vorname']=html_entity_decode($row->vorname);
				$item['nachname']=html_entity_decode($row->nachname);
				$item['uid']=html_entity_decode($row->uid);
				$result[]=$item;
			//	echo html_entity_decode($row->vorname).' '.html_entity_decode($row->nachname).'|'.html_entity_decode($row->uid)."\n";
			}
			echo json_encode($result);
			break;
	}
	exit();
?>
