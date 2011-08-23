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

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>
<overlay id="overlayMantisDetail"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<!-- ************************ -->
	<!-- *  Mantisdetail   * -->
	<!-- ************************ -->
	<vbox id="box-projekttask-mantis" flex="1">
		<description>Issue</description>
			
		<grid id="grid-projekttask-mantis" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="2"/>
				<column flex="1"/>
				<column flex="2"/>
			</columns>
			<rows>
				<row>
					<label value="Mantis ID" control="textbox-projekttask-mantis-mantis_id"/>
					<textbox id="textbox-projekttask-mantis-mantis_id"/>
					<label value="last_updated" control="textbox-projekttask-mantis-issue_last_updated"/>
   					<textbox id="textbox-projekttask-mantis-issue_last_updated"/>
				</row>
				<row>
					<label value="Summary" control="textbox-projekttask-mantis-issue_summary"/>
					<textbox id="textbox-projekttask-mantis-issue_summary"/>
					<label value="Description" control="textbox-projekttask-mantis-issue_description"/>
   					<textbox id="textbox-projekttask-mantis-issue_description"/>
				</row>
				<row>
					<label value="View_state_id" control="textbox-projekttask-mantis-issue_view_state_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_view_state_id"/>
					<label value="View_state_name" control="textbox-projekttask-mantis-issue_view_state_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_view_state_name"/>
				</row>
				<row>
					<label value="Project_id" control="textbox-projekttask-mantis-issue_project_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_project_id"/>
					<label value="Project_name" control="textbox-projekttask-mantis-issue_project_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_project_name"/>
				</row> 		
				<row>
					<label value="Category" control="textbox-projekttask-mantis-issue_category"/>
   					<textbox id="textbox-projekttask-mantis-issue_category"/>
					<label value="Due_date" control="textbox-projekttask-mantis-issue_due_date"/>
   					<textbox id="textbox-projekttask-mantis-issue_due_date"/>
				</row>
				<row>
					<label value="Priority_id" control="textbox-projekttask-mantis-issue_priority_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_priority_id"/>
					<label value="Priority_name" control="textbox-projekttask-mantis-issue_priority_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_priority_name"/>
				</row>
				<row>
					<label value="Severity_id" control="textbox-projekttask-mantis-issue_severity_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_severity_id"/>
					<label value="Severity_name" control="textbox-projekttask-mantis-issue_severity_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_severity_name"/>
				</row>
				<row>
					<label value="Status_id" control="textbox-projekttask-mantis-issue_status_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_status_id"/>
					<label value="Status_name" control="textbox-projekttask-mantis-issue_status_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_status_name"/>
				</row>
				<row>
					<label value="Reporter_id" control="textbox-projekttask-mantis-issue_reporter_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_reporter_id"/>
					<label value="Reporter_name" control="textbox-projekttask-mantis-issue_reporter_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_reporter_name"/>
				</row>
				<row>
					<label value="Reporter_real_name" control="textbox-projekttask-mantis-issue_reporter_real_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_reporter_real_name"/>
					<label value="Reporter_email" control="textbox-projekttask-mantis-issue_reporter_email"/>
   					<textbox id="textbox-projekttask-mantis-issue_reporter_email"/>
				</row>
				<row>
					<label value="Reproducibility_id" control="textbox-projekttask-mantis-issue_reproducibility_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_reproducibility_id"/>
					<label value="Reproducibility_name" control="textbox-projekttask-mantis-issue_reproducibility_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_reproducibility_name"/>
				</row> 
				<row>
					<label value="Date_submitted" control="textbox-projekttask-mantis-issue_date_submitted"/>
   					<textbox id="textbox-projekttask-mantis-issue_date_submitted"/>
					<label value="Sponsorship_total" control="textbox-projekttask-mantis-issue_sponsorship_total"/>
   					<textbox id="textbox-projekttask-mantis-issue_sponsorship_total"/>
				</row>
				<row>
					<label value="Projection_id" control="textbox-projekttask-mantis-issue_projection_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_projection_id"/>
					<label value="Projection_name" control="textbox-projekttask-mantis-issue_projection_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_projection_name"/>
				</row>
				<row>
					<label value="eta_id" control="textbox-projekttask-mantis-issue_eta_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_eta_id"/>
					<label value="eta_name" control="textbox-projekttask-mantis-issue_eta_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_eta_name"/>
				</row>
				<row>
					<label value="Resolution_id" control="textbox-projekttask-mantis-issue_resolution_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_resolution_id"/>
					<label value="Resolution_name" control="textbox-projekttask-mantis-issue_resolution_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_resolution_name"/>
				</row> 
				<row>
					<label value="Attachments" control="textbox-projekttask-mantis-issue_attachments"/>
   					<textbox id="textbox-projekttask-mantis-issue_attachments"/>
				</row>
				
			</rows>
		</grid>
		<hbox>
			<spacer flex="1" />
			<button id="button-projekttask-mantis-speichern" oncommand="saveProjekttaskMantis()" label="Speichern" />
		</hbox>
	</vbox>
	
	<vbox id="projekttask-mantis" flex="1">
	<description>Mantis Details</description>
	</vbox>
</overlay>