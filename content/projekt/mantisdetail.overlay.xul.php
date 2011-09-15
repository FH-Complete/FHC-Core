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
					<textbox id="textbox-projekttask-mantis-mantis_id" disabled="true"/>
					<label value="last_updated" control="textbox-projekttask-mantis-issue_last_updated"/>
   					<textbox id="textbox-projekttask-mantis-issue_last_updated" disabled="true"/>
				</row>
				<row>
					<label value="Projekt ID" control="textbox-projekttask-mantis-issue_project_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_project_id"/>
   					<label value="Projekt Name" control="textbox-projekttask-mantis-issue_project_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_project_name" disabled="true"/>
				</row>
				<row>
					<label value="Zusammenfassung" control="textbox-projekttask-mantis-issue_summary"/>
					<textbox id="textbox-projekttask-mantis-issue_summary"/>
					<label value="Kategorie" control="textbox-projekttask-mantis-issue_category"/>
   					<textbox id="textbox-projekttask-mantis-issue_category" disabled="true"/>
									
				</row>
				<row>
					<label value="Beschreibung" control="textbox-projekttask-mantis-issue_description"/>
   					<textbox id="textbox-projekttask-mantis-issue_description" multiline="true"/>
   					<label value="Status" control="textbox-projekttask-mantis-issue_status_name"/>
   					<vbox>
   						<textbox id="textbox-projekttask-mantis-issue_status_name" disabled="true"/>
   						<spacer />
   					</vbox>
				</row>
				<row>
					<label value="Schritte zur Reproduktion" control="textbox-projekttask-mantis-steps_to_reproduce"/>
   					<textbox id="textbox-projekttask-mantis-issue_steps_to_reproduce" multiline="true"/>
   					<label value="Priorität" control="textbox-projekttask-mantis-issue_priority_name"/>
   					<vbox>
   						<textbox id="textbox-projekttask-mantis-issue_priority_name" disabled="true"/>
   						<spacer />
   					</vbox>
				</row>
				<row>
					<label value="Zusätzliche Informationen" control="textbox-projekttask-mantis-issue_additional_information"/>
   					<textbox id="textbox-projekttask-mantis-issue_additional_information" multiline="true"/>
				</row> 		
				<row>
					<label value="View_state_id" control="textbox-projekttask-mantis-issue_view_state_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_view_state_id" disabled="true"/>
					<label value="Due_date" control="textbox-projekttask-mantis-issue_due_date"/>
   					<textbox id="textbox-projekttask-mantis-issue_due_date" disabled="true"/>
				</row>
				<row>
					<label value="Priority_id" control="textbox-projekttask-mantis-issue_priority_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_priority_id" disabled="true"/>
					
				</row>
				<row>
					<label value="Severity_id" control="textbox-projekttask-mantis-issue_severity_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_severity_id" disabled="true"/>
					<label value="Severity_name" control="textbox-projekttask-mantis-issue_severity_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_severity_name" disabled="true"/>
				</row>
				<row>
					<label value="Status_id" control="textbox-projekttask-mantis-issue_status_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_status_id" disabled="true"/>
					<label value="View_state_name" control="textbox-projekttask-mantis-issue_view_state_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_view_state_name" disabled="true"/>
				</row>
				<row>
					<label value="Reporter_id" control="textbox-projekttask-mantis-issue_reporter_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_reporter_id" disabled="true"/>
					<label value="Reporter_name" control="textbox-projekttask-mantis-issue_reporter_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_reporter_name" disabled="true"/>
				</row>
				<row>
					<label value="Reporter_real_name" control="textbox-projekttask-mantis-issue_reporter_real_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_reporter_real_name" disabled="true"/>
					<label value="Reporter_email" control="textbox-projekttask-mantis-issue_reporter_email"/>
   					<textbox id="textbox-projekttask-mantis-issue_reporter_email" disabled="true"/>
				</row>
				<row>
					<label value="Reproducibility_id" control="textbox-projekttask-mantis-issue_reproducibility_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_reproducibility_id" disabled="true"/>
					<label value="Reproducibility_name" control="textbox-projekttask-mantis-issue_reproducibility_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_reproducibility_name" disabled="true"/>
				</row> 
				<row>
					<label value="Date_submitted" control="textbox-projekttask-mantis-issue_date_submitted"/>
   					<textbox id="textbox-projekttask-mantis-issue_date_submitted" disabled="true"/>
					<label value="Sponsorship_total" control="textbox-projekttask-mantis-issue_sponsorship_total"/>
   					<textbox id="textbox-projekttask-mantis-issue_sponsorship_total" disabled="true"/>
				</row>
				<row>
					<label value="Projection_id" control="textbox-projekttask-mantis-issue_projection_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_projection_id" disabled="true"/>
					<label value="Projection_name" control="textbox-projekttask-mantis-issue_projection_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_projection_name" disabled="true"/>
				</row>
				<row>
					<label value="eta_id" control="textbox-projekttask-mantis-issue_eta_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_eta_id" disabled="true"/>
					<label value="eta_name" control="textbox-projekttask-mantis-issue_eta_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_eta_name" disabled="true"/>
				</row>
				<row>
					<label value="Resolution_id" control="textbox-projekttask-mantis-issue_resolution_id"/>
   					<textbox id="textbox-projekttask-mantis-issue_resolution_id" disabled="true"/>
					<label value="Resolution_name" control="textbox-projekttask-mantis-issue_resolution_name"/>
   					<textbox id="textbox-projekttask-mantis-issue_resolution_name" disabled="true"/>
				</row> 
			</rows>
		</grid>
		<hbox>
			<spacer flex="1" />
			<button id="button-projekttask-mantis-speichern" oncommand="saveProjekttaskMantis()" label="Speichern" />
		</hbox>
	</vbox>
</overlay>