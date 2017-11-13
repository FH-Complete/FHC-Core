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
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
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
		    <MANTIS:issue_id><![CDATA[<?php echo $mantis->issue_id;  ?>]]></MANTIS:issue_id>
		    <MANTIS:issue_summary><![CDATA[<?php echo $mantis->issue_summary;  ?>]]></MANTIS:issue_summary>
		    <MANTIS:issue_description><![CDATA[<?php echo $mantis->issue_description;  ?>]]></MANTIS:issue_description>
		    <MANTIS:issue_view_state_id><![CDATA[<?php echo $mantis->issue_view_state->id;  ?>]]></MANTIS:issue_view_state_id>
		    <MANTIS:issue_view_state_name><![CDATA[<?php echo $mantis->issue_view_state->name;  ?>]]></MANTIS:issue_view_state_name>
		    <MANTIS:issue_last_updated><![CDATA[<?php echo $mantis->issue_last_updated;  ?>]]></MANTIS:issue_last_updated>
		    <MANTIS:issue_project_id><![CDATA[<?php echo $mantis->issue_project->id;  ?>]]></MANTIS:issue_project_id>
		    <MANTIS:issue_project_name><![CDATA[<?php echo $mantis->issue_project->name;  ?>]]></MANTIS:issue_project_name>
		    <MANTIS:issue_category><![CDATA[<?php echo $mantis->issue_category;  ?>]]></MANTIS:issue_category>
		    <MANTIS:issue_priority_id><![CDATA[<?php echo $mantis->issue_priority->id;  ?>]]></MANTIS:issue_priority_id>
		    <MANTIS:issue_priority_name><![CDATA[<?php echo $mantis->issue_priority->name;  ?>]]></MANTIS:issue_priority_name>
		    <MANTIS:issue_severity_id><![CDATA[<?php echo $mantis->issue_severity->id;  ?>]]></MANTIS:issue_severity_id>
		    <MANTIS:issue_severity_name><![CDATA[<?php echo $mantis->issue_severity->name;  ?>]]></MANTIS:issue_severity_name>
		    <MANTIS:issue_status_id><![CDATA[<?php echo $mantis->issue_status->id;  ?>]]></MANTIS:issue_status_id>
		    <MANTIS:issue_status_name><![CDATA[<?php echo $mantis->issue_status->name;  ?>]]></MANTIS:issue_status_name>
		    <MANTIS:issue_reporter_id><![CDATA[<?php echo $mantis->issue_reporter->id;  ?>]]></MANTIS:issue_reporter_id>
		    <MANTIS:issue_reporter_name><![CDATA[<?php echo $mantis->issue_reporter->name;  ?>]]></MANTIS:issue_reporter_name>
		    <MANTIS:issue_reporter_real_name><![CDATA[<?php echo $mantis->issue_reporter->real_name;  ?>]]></MANTIS:issue_reporter_real_name>
		    <MANTIS:issue_reporter_email><![CDATA[<?php echo $mantis->issue_reporter->email;  ?>]]></MANTIS:issue_reporter_email>
		    <MANTIS:issue_reproducibility_id><![CDATA[<?php echo $mantis->issue_reproducibility->id;  ?>]]></MANTIS:issue_reproducibility_id>
		    <MANTIS:issue_reproducibility_name><![CDATA[<?php echo $mantis->issue_reproducibility->name;  ?>]]></MANTIS:issue_reproducibility_name>
		    <MANTIS:issue_date_submitted><![CDATA[<?php echo $mantis->issue_date_submitted;  ?>]]></MANTIS:issue_date_submitted>
		    <MANTIS:issue_sponsorship_total><![CDATA[<?php echo $mantis->issue_sponsorship_total;  ?>]]></MANTIS:issue_sponsorship_total>
		    <MANTIS:issue_projection_id><![CDATA[<?php echo $mantis->issue_projection->id;  ?>]]></MANTIS:issue_projection_id>
		    <MANTIS:issue_projection_name><![CDATA[<?php echo $mantis->issue_projection->name;  ?>]]></MANTIS:issue_projection_name>
		    <MANTIS:issue_eta_id><![CDATA[<?php echo $mantis->issue_eta->id;  ?>]]></MANTIS:issue_eta_id>
		    <MANTIS:issue_eta_name><![CDATA[<?php echo $mantis->issue_eta->name;  ?>]]></MANTIS:issue_eta_name>
			<MANTIS:issue_tags_name><![CDATA[<?php echo $mantis->issue_tags->name;  ?>]]></MANTIS:issue_tags_name>
		    <MANTIS:issue_resolution_id><![CDATA[<?php echo $mantis->issue_resolution->id;  ?>]]></MANTIS:issue_resolution_id>
		    <MANTIS:issue_resolution_name><![CDATA[<?php echo $mantis->issue_resolution->name;  ?>]]></MANTIS:issue_resolution_name>
		    <MANTIS:issue_due_date><![CDATA[<?php echo $mantis->issue_due_date;  ?>]]></MANTIS:issue_due_date>
		    <MANTIS:issue_steps_to_reproduce><![CDATA[<?php echo $mantis->issue_steps_to_reproduce;  ?>]]></MANTIS:issue_steps_to_reproduce>
		    <MANTIS:issue_additional_information><![CDATA[<?php echo $mantis->issue_additional_information;  ?>]]></MANTIS:issue_additional_information>
	    </RDF:Description>
	</RDF:li>
    </RDF:Seq>
</RDF:RDF>