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
require_once('../config/vilesci.config.inc.php');
?>
// ********** FUNKTIONEN ********** //
var MessagePersonID=null;
var MessagesTreeDatasource=''; // Datasource des Adressen Trees
var MessagesSelectID='';
var MessageSenderPersonID='';

var MessagesTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('messages-tree').builder.rebuild();
	}
};

var MessagesTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
      //window.setTimeout(KontaktAdressenTreeSelectID,10);
  }
};

// ****
// * Laedt die Trees
// ****
function loadMessages(person_id, fas_person_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	MessagePersonID = person_id;
	MessageSenderPersonID=fas_person_id;
	
	//Adressen laden
	url = "<?php echo APP_ROOT; ?>rdf/messages.rdf.php?person_id="+person_id+"&"+gettimestamp();
	var tree=document.getElementById('messages-tree');
	try
	{
		MessagesTreeDatasource.removeXMLSinkObserver(MessagesTreeSinkObserver);
		tree.builder.removeListener(MessagesTreeListener);
	}
	catch(e)
	{}

	//Alte DS entfernen
	var oldDatasources = tree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		tree.database.RemoveDataSource(oldDatasources.getNext());
	}

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	MessagesTreeDatasource = rdfService.GetDataSource(url);
	MessagesTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	MessagesTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	tree.database.AddDataSource(MessagesTreeDatasource);
	MessagesTreeDatasource.addXMLSinkObserver(MessagesTreeSinkObserver);
	tree.builder.addListener(MessagesTreeListener);

}


// ****
// * Zeigt HTML Seite zum Erstellen neuer Nachrichten
// ****
function MessagesNewMessage()
{
	window.open('<?php echo APP_ROOT ?>/index.ci.php/system/Messages/write/'+MessageSenderPersonID+'/'+MessagePersonID,'Message','');
}

/**
 * Oeffnet Nachrichtenseite um eine Antwort auf eine Nachricht zu schicken
 */
function MessagesSendAnswer()
{
	var tree=document.getElementById('messages-tree');
	if(tree.currentIndex==-1)
	{
		alert("Bitte markieren Sie zuerst eine Nachricht");
	}
	else
	{
		var MessageId = getTreeCellText(tree, 'messages-tree-message_id', tree.currentIndex);
		var RecipientID = getTreeCellText(tree, 'messages-tree-recipient_id', tree.currentIndex);
		window.open('<?php echo APP_ROOT ?>/index.ci.php/system/Messages/reply/'+MessageId+'/'+RecipientID,'Reply','');
	}
}

function MessageAuswahl()
{
	var tree=document.getElementById('messages-tree');
	if(tree.currentIndex==-1)
	{
		alert("Bitte markieren Sie zuerst eine Nachricht");
	}
	else
	{
		var text = getTreeCellText(tree, 'messages-tree-body', tree.currentIndex);
	}
	document.getElementById('message-wysiwyg').value=text;
}
