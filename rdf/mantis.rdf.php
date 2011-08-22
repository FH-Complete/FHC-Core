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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */
/*
 * Created on 02.12.2004
 * Erstellt ein RDF mit den Lehrformen
 */
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/mantis.class.php');

$rdf_url='http://www.technikum-wien.at/mantis';

if (isset($_GET['issue_id']))
    $issue_id=$_GET['issue_id'];
else
    $issue_id=null;
$mantis=new mantis();
$mantis->getIssue($issue_id);
//print_r($mantis);
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:MANTIS="<?php echo $rdf_url; ?>/rdf#"
>
    <RDF:Seq about="<?php echo $rdf_url ?>/alle-issues">
	<RDF:li>
	    <RDF:Description  id="<?php echo $mantis->issue_id; ?>"  about="<?php echo $rdf_url.'/'.$mantis->issue_id; ?>" >
		    <MANTIS:issue_id><?php echo $mantis->issue_id;  ?></MANTIS:issue_id>
		    <MANTIS:issue_summary><?php echo $mantis->issue_summary;  ?></MANTIS:issue_summary>
		    <MANTIS:issue_description><?php echo $mantis->issue_description;  ?></MANTIS:issue_description>
	    </RDF:Description>
	</RDF:li>
    </RDF:Seq>
</RDF:RDF>