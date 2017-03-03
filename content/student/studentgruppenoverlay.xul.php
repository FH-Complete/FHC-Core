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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="interessent-dokumente-overlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/studentgruppenoverlay.js.php" />

<!-- Dokumente Overlay -->
<vbox id="student-gruppen" style="overflow:auto; margin:0px;" flex="1">
<popupset>
	<menupopup id="student-gruppe-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentGruppeDelete();" id="student-gruppe-tree-popup-delete" hidden="false"/>
	</menupopup>
</popupset>
<hbox flex="1">
	<groupbox flex="1">
		<caption label="Gruppen"/>
		<tree id="student-gruppen-tree" seltype="multi" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/gruppen/liste"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:10px;"
				context="student-gruppe-tree-popup"
		>
			<treecols>
				<treecol id="student-gruppen-gruppe_kurzbz" label="Gruppe" flex="1" primary="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/gruppen/rdf#gruppe_kurzbz" />
				<splitter class="tree-splitter"/>
				<treecol id="student-gruppen-bezeichnung" label="Bezeichnung" flex="1" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/gruppen/rdf#bezeichnung" />
				<splitter class="tree-splitter"/>
				<treecol id="student-gruppen-studiensemester_kurzbz" label="Studiensemester" flex="1" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/gruppen/rdf#studiensemester_kurzbz" />
				<splitter class="tree-splitter"/>
				<treecol id="student-gruppen-generiert" label="automatisch generiert" flex="1" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/gruppen/rdf#generiert" />
				<splitter class="tree-splitter"/>
				<treecol id="student-gruppen-uid" label="UID" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/gruppen/rdf#uid" />
				<splitter class="tree-splitter"/>
			</treecols>

			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/gruppen/rdf#gruppe_kurzbz"   />
								<treecell label="rdf:http://www.technikum-wien.at/gruppen/rdf#bezeichnung"   />
								<treecell label="rdf:http://www.technikum-wien.at/gruppen/rdf#studiensemester_kurzbz" />
								<treecell label="rdf:http://www.technikum-wien.at/gruppen/rdf#generiert" />
								<treecell label="rdf:http://www.technikum-wien.at/gruppen/rdf#uid" />
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>
	</groupbox>
</hbox>
</vbox>
</overlay>