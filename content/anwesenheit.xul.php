<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';

$student_uid = filter_input(INPUT_GET,'student_uid');
$lehrveranstaltung_id= filter_input(INPUT_GET,'lehrveranstaltung_id');

?>

<window id="anwesenheit-window" title="anwesenheit"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="loadanwesenheit('<?php echo $student_uid;?>','<?php echo $lehrveranstaltung_id;?>');">

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/anwesenheit.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
<vbox flex="1">
	<groupbox id="anwesenheit-groupbox-anwesenheit" flex="1">
		<caption label="Anwesenheit" />
		<hbox flex="1">
			<tree id="anwesenheit-tree" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null"
				ref="http://www.technikum-wien.at/anwesenheit"
				persist="hidden, height"
			>
				<treecols>
					<treecol id="anwesenheit-treecol-lehrveranstaltung" label="Lehrveranstaltung" flex="1" hidden="<?php echo ($lehrveranstaltung_id==''?'false':'true');?>"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/anwesenheit/rdf#lehrveranstaltung_bezeichnung" onclick="anwesenheitTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="anwesenheit-treecol-nachname" label="Nachname" flex="1" hidden="<?php echo ($lehrveranstaltung_id==''?'true':'false');?>"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/anwesenheit/rdf#nachname" onclick="anwesenheitTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="anwesenheit-treecol-vorname" label="Vorname" flex="1" hidden="<?php echo ($lehrveranstaltung_id==''?'true':'false');?>"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/anwesenheit/rdf#vorname" onclick="anwesenheitTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="anwesenheit-treecol-prozent" label="Anwesenheit in Prozent" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/anwesenheit/rdf#prozent" onclick="anwesenheitTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="anwesenheit-treecol-anwesend" label="Anwesend" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/anwesenheit/rdf#anwesend" onclick="anwesenheitTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="anwesenheit-treecol-nichtanwesend" label="Nicht anwesend" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/anwesenheit/rdf#nichtanwesend" onclick="anwesenheitTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="anwesenheit-treecol-uid" label="UID" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/anwesenheit/rdf#uid" onclick="anwesenheitTreeSort()"/>
					<splitter class="tree-splitter"/>
				</treecols>

				<template>
					<rule>
						<treechildren>
							<treeitem uri="rdf:*">
								<treerow properties="rdf:http://www.technikum-wien.at/anwesenheit/rdf#ampel">
									<treecell label="rdf:http://www.technikum-wien.at/anwesenheit/rdf#lehrveranstaltung_bezeichnung" />
									<treecell label="rdf:http://www.technikum-wien.at/anwesenheit/rdf#nachname" />
									<treecell label="rdf:http://www.technikum-wien.at/anwesenheit/rdf#vorname" />
									<treecell label="rdf:http://www.technikum-wien.at/anwesenheit/rdf#prozent" />
									<treecell label="rdf:http://www.technikum-wien.at/anwesenheit/rdf#anwesend" />
									<treecell label="rdf:http://www.technikum-wien.at/anwesenheit/rdf#nichtanwesend" />
									<treecell label="rdf:http://www.technikum-wien.at/anwesenheit/rdf#uid" />
								</treerow>
							</treeitem>
						</treechildren>
					</rule>
				</template>
			</tree>
		</hbox>
	</groupbox>

</vbox>
</window>
