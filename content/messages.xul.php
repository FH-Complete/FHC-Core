<?php
/* Copyright (C) 2016 fhcomplete.org
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
require_once('../include/functions.inc.php');
require_once('../include/variable.class.php');
require_once('../include/benutzer.class.php');

$user=get_uid();
$variable = new variable();
if(!$variable->loadVariables($user))
{
	die('Fehler beim Laden der Variablen:'.$variable->errormsg);
}

$benutzer = new benutzer();
$benutzer->load($user);

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

$person_id = filter_input(INPUT_GET,'person_id');
echo '
<!DOCTYPE overlay [';
require('../locale/'.$variable->variable->locale.'/fas.dtd');
echo ']>
';
?>

<window id="messages-window" title="messages"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="loadMessages(<?php echo "'".$person_id."','".$benutzer->person_id."'"; ?>);"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/messages.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />

<vbox flex="1">
<popupset>
	<menupopup id="messages-tree-popup">
		<menuitem label="Antwort" oncommand="MessagesSendAnswer();" id="messages-tree-popup-answer" hidden="false"/>
		<menuitem label="Entfernen" oncommand="messagesDelete();" id="messages-tree-popup-delete" hidden="false"/>
	</menupopup>
</popupset>
<hbox style="padding-top: 10px">
	<button oncommand="MessagesNewMessage()" id="messages-button-new" label="Neue Nachricht schicken"/>
	<button oncommand="MessagesSendAnswer()" id="messages-button-answer" label="Antworten"/>
</hbox>

<hbox flex="1">

<tree id="messages-tree" seltype="single" hidecolumnpicker="false" flex="1"
	datasources="rdf:null" ref="http://www.technikum-wien.at/messages"
	style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
	onselect="MessageAuswahl()"
	context="messages-tree-popup"
	flags="dont-build-content"
>

	<treecols>
		<treecol id="messages-tree-betreff" label="Betreff" flex="2" hidden="false" primary="true"
		class="sortDirectionIndicator"
		sort="rdf:http://www.technikum-wien.at/messages/rdf#subject"/>
		<splitter class="tree-splitter"/>
		<treecol id="messages-tree-body" label="Body" flex="2" hidden="true"
			class="sortDirectionIndicator"
			sort="rdf:http://www.technikum-wien.at/messages/rdf#body"/>
		<splitter class="tree-splitter"/>
		<treecol id="messages-tree-message_id" label="MessageID" flex="2" hidden="true"
			class="sortDirectionIndicator"
			sort="rdf:http://www.technikum-wien.at/messages/rdf#message_id"/>
		<splitter class="tree-splitter"/>
		<treecol id="messages-tree-insertamum" label="Datum" flex="2" hidden="false"
			class="sortDirectionIndicator"
			sortActive="true"
			sortDirection="descending"
			sort="rdf:http://www.technikum-wien.at/messages/rdf#insertamum"/>
		<splitter class="tree-splitter"/>
		<treecol id="messages-tree-sender" label="SenderIn" flex="2" hidden="false"
			class="sortDirectionIndicator"
			sort="rdf:http://www.technikum-wien.at/messages/rdf#sender"/>
		<splitter class="tree-splitter"/>
		<treecol id="messages-tree-recipient" label="EmpfängerIn" flex="2" hidden="false"
			class="sortDirectionIndicator"
			sort="rdf:http://www.technikum-wien.at/messages/rdf#recipient"/>
		<splitter class="tree-splitter"/>
		<treecol id="messages-tree-sender_id" label="SenderInID" flex="2" hidden="true"
			class="sortDirectionIndicator"
			sort="rdf:http://www.technikum-wien.at/messages/rdf#sender"/>
		<splitter class="tree-splitter"/>
		<treecol id="messages-tree-recipient_id" label="EmpfängerInID" flex="2" hidden="true"
			class="sortDirectionIndicator"
			sort="rdf:http://www.technikum-wien.at/messages/rdf#recipient_id"/>
		<splitter class="tree-splitter"/>
		<treecol id="messages-tree-status" label="Status" flex="2" hidden="false"
			class="sortDirectionIndicator"
			sort="rdf:http://www.technikum-wien.at/messages/rdf#status"/>
		<splitter class="tree-splitter"/>
		<treecol id="messages-tree-status" label="Letzte Statusänderung" flex="2" hidden="false"
			class="sortDirectionIndicator"
			sort="rdf:http://www.technikum-wien.at/messages/rdf#statusdatum"/>
		<splitter class="tree-splitter"/>
	</treecols>

	<template>
		<treechildren flex="1" >
				<treeitem uri="rdf:*">
				<treerow>
					<treecell label="rdf:http://www.technikum-wien.at/messages/rdf#subject"/>
					<treecell label="rdf:http://www.technikum-wien.at/messages/rdf#body"/>
					<treecell label="rdf:http://www.technikum-wien.at/messages/rdf#message_id"/>
					<treecell label="rdf:http://www.technikum-wien.at/messages/rdf#insertamum"/>
					<treecell label="rdf:http://www.technikum-wien.at/messages/rdf#sender"/>
					<treecell label="rdf:http://www.technikum-wien.at/messages/rdf#recipient"/>
					<treecell label="rdf:http://www.technikum-wien.at/messages/rdf#sender_id"/>
					<treecell label="rdf:http://www.technikum-wien.at/messages/rdf#recipient_id"/>
					<treecell label="rdf:http://www.technikum-wien.at/messages/rdf#status"/>
					<treecell label="rdf:http://www.technikum-wien.at/messages/rdf#statusdatum"/>
				</treerow>
			</treeitem>
		</treechildren>
	</template>
</tree>
<splitter collapse="after" persist="state">
	<grippy />
</splitter>
<iframe id="message-wysiwyg" editortype="html" src="about:blank" flex="1" type="content-primary" style="min-width: 100px; min-height: 100px; border: 1px solid gray; margin: 10px;"/>
</hbox>

</vbox>
</window>
