<?php
/* Copyright (C) 2012 Technikum-Wien
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
 * 
 */

	header( 'Expires:  -1' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Pragma: no-cache' );
	header('Content-Type: text/html;charset=UTF-8');

	require_once('../../../config/cis.config.inc.php');
  	require_once('../../../include/functions.inc.php');
	require_once('../../../include/mitarbeiter.class.php');

// ------------------------------------------------------------------------------------------
// Parameter Aufruf uebernehmen
// ------------------------------------------------------------------------------------------

  	$work=trim(isset($_REQUEST['work'])?$_REQUEST['work']:(isset($_REQUEST['ajax'])?$_REQUEST['ajax']:false));
	$work=strtolower($work);

// ------------------------------------------------------------------------------------------
//	Datenlesen
// ------------------------------------------------------------------------------------------
	switch ($work)
	{			
        // Suche nach anprechperson -> mitarbeiter
		case 'outgoing_ansprechperson_search':
		 	$search=trim((isset($_REQUEST['q']) ? $_REQUEST['q']:''));
			if (is_null($search) ||$search=='')
				exit();	
			$ma = new mitarbeiter();
			$ma->search($search);
	
			foreach($ma->result as $row)
				echo html_entity_decode($row->vorname).' '.html_entity_decode($row->nachname).'|'.html_entity_decode($row->uid)."\n";
			break;
	}
	exit();
?>
