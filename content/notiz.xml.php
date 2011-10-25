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
require_once('../config/vilesci.config.inc.php');
header("Content-type: application/vnd.mozilla.xul+xml");

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
?>
<bindings xmlns="http://www.mozilla.org/xbl"
          xmlns:xul="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
		  xmlns:xbl="http://www.mozilla.org/xbl"
		  xmlns:html="http://www.w3.org/1999/xhtml"
		  >
  <!--
  Binding fuer die Notizen
  -->
  <binding id="Notiz">
  	<content>
		<xul:vbox flex="1">
			<xul:popupset>
				<xul:popup anonid="notiz-tree-popup">
					<xul:menuitem label="Erledigt"/>
				</xul:popup>
			</xul:popupset>
			<xul:toolbox>
				<xul:toolbar >
					<xul:toolbarbutton anonid="toolbarbutton-notiz-neu" label="Neue Notiz" oncommand="document.getBindingParent(this).NeueNotiz()" image="../skin/images/NeuDokument.png" tooltiptext="Neue Notiz anlegen" />
					<xul:toolbarbutton anonid="toolbarbutton-notiz-del" label="Loeschen" oncommand="document.getBindingParent(this).Loeschen()" image="../skin/images/DeleteIcon.png" disabled="true" tooltiptext="Notiz löschen"/>
					<xul:toolbarbutton anonid="toolbarbutton-notiz-aktualisieren" label="Aktualisieren" oncommand="document.getBindingParent(this).RefreshNotiz()" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
					<xul:toolbarbutton anonid="toolbarbutton-notiz-filter" label="Filter" type="menu">							
				      <xul:menupopup>
						    <xul:menuitem label="Alle Notizen anzeigen" oncommand="document.getBindingParent(this).LoadNotizTree(document.getBindingParent(this).getAttribute('projekt_kurzbz'),document.getBindingParent(this).getAttribute('projektphase_id'),document.getBindingParent(this).getAttribute('projekttask_id'),document.getBindingParent(this).getAttribute('uid'),document.getBindingParent(this).getAttribute('person_id'),document.getBindingParent(this).getAttribute('prestudent_id'),document.getBindingParent(this).getAttribute('bestellung_id'), document.getBindingParent(this).getAttribute('user'), null);" tooltiptext="Alle Notizen anzeigen"/>
							<xul:menuitem label="nur offene Notizen anzeigen" oncommand="document.getBindingParent(this).LoadNotizTree(document.getBindingParent(this).getAttribute('projekt_kurzbz'),document.getBindingParent(this).getAttribute('projektphase_id'),document.getBindingParent(this).getAttribute('projekttask_id'),document.getBindingParent(this).getAttribute('uid'),document.getBindingParent(this).getAttribute('person_id'),document.getBindingParent(this).getAttribute('prestudent_id'),document.getBindingParent(this).getAttribute('bestellung_id'), document.getBindingParent(this).getAttribute('user'), false);" tooltiptext="nur offene Notizen anzeigen"/>
				      </xul:menupopup>
				    </xul:toolbarbutton>
				</xul:toolbar>
			</xul:toolbox>
			<xul:tree anonid="tree-notiz"
			seltype="single" hidecolumnpicker="false" flex="1"
			datasources="rdf:null" ref="http://www.technikum-wien.at/notiz/liste"
			onclick="document.getBindingParent(this).updateErledigt(event);"
			onselect="document.getBindingParent(this).edit(event);"
			editable="true"
			>
			
			<xul:treecols>
			    <xul:treecol anonid="treecol-notiz-titel" label="Titel" flex="5" primary="true" persist="hidden width ordinal"
					class="sortDirectionIndicator" editable="false" sortActive="true"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#titel"  />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-text" label="Text" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator" editable="false" 
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#text" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-verfasser" label="Verfasser" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator" editable="false"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#verfasser_uid" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-bearbeiter" label="Bearbeiter" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator" editable="false"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#bearbeiter_uid" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-start" label="Start" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator" editable="false"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#startISO" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-ende" label="Ende" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator" editable="false"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#endeISO" />
			    <xul:splitter class="tree-splitter"/>
				<xul:treecol anonid="treecol-notiz-erledigt" label="Erledigt" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator" type="checkbox" editable="true"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#erledigt_boolean" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-notiz_id" label="NotizID" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator" editable="false"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#notiz_id" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-startISO" label="StartISO" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator" editable="false"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#startISO" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-ende" label="EndeISO" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator" editable="false"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#endeISO" />
			</xul:treecols>
		
			<xul:template>
			    <xul:rule>
			      <xul:treechildren>
			       <xul:treeitem uri="rdf:*">
			         <xul:treerow>
			           <xul:treecell label="rdf:http://www.technikum-wien.at/notiz/rdf#titel"/>
			           <xul:treecell label="rdf:http://www.technikum-wien.at/notiz/rdf#text"/>
			           <xul:treecell label="rdf:http://www.technikum-wien.at/notiz/rdf#verfasser_uid"/>
			           <xul:treecell label="rdf:http://www.technikum-wien.at/notiz/rdf#bearbeiter_uid"/>
			           <xul:treecell label="rdf:http://www.technikum-wien.at/notiz/rdf#start"/>
			           <xul:treecell label="rdf:http://www.technikum-wien.at/notiz/rdf#ende"/>
			           <xul:treecell label="erledigt" value="rdf:http://www.technikum-wien.at/notiz/rdf#erledigt"/>
			           <xul:treecell label="rdf:http://www.technikum-wien.at/notiz/rdf#notiz_id"/>
			           <xul:treecell label="rdf:http://www.technikum-wien.at/notiz/rdf#startISO"/>
			           <xul:treecell label="rdf:http://www.technikum-wien.at/notiz/rdf#endeISO"/>
			         </xul:treerow>
			       </xul:treeitem>
			      </xul:treechildren>
			    </xul:rule>
			</xul:template>
		    </xul:tree>
		    <!--
		    <xul:button onclick="alert('value:'+document.getBindingParent(this).value);" label="GetValue" />
		    <xul:button onclick="alert('projekt_kurzbz:'+this.getAttribute('projekt_kurzbz'));" label="GetProjektKurzbz" />
		    -->
		    <xul:splitter collapse="after" persist="state">
				<xul:grippy />
			</xul:splitter>
		    <xul:vbox flex="1">
		    	<xul:textbox anonid="textbox-notiz-notiz_id" hidden="true"/>
				
				<xul:groupbox flex="1">
					<xul:caption anonid="caption-notiz-detail" label="Neue Notiz"/>
						<xul:grid anonid="grid-notiz-detail" style="overflow:auto;margin:4px;" flex="1">
						  	<xul:columns  >
								<xul:column flex="1"/>
								<xul:column flex="5"/>
							</xul:columns>
							<xul:rows>
				      			<xul:row>
				      				<xul:label value="Titel"/>
						      		<xul:textbox anonid="textbox-notiz-titel" maxlength="256"/>
								</xul:row>
								<xul:row>
				      				<xul:label value="Text"/>
						      		<xul:textbox anonid="textbox-notiz-text" multiline="true" rows="6"/>
								</xul:row>
								<xul:row>
				      					<xul:label value="Start"/>
				      				<xul:hbox>
						      			<xul:box class="Datum" anonid="box-notiz-start"/>
						      			<xul:label value="Erledigt "/>
						      			<xul:checkbox anonid="checkbox-notiz-erledigt"/>
						      		</xul:hbox>
								</xul:row>
								<xul:row>
									<xul:label value="Ende"/>
									<xul:hbox flex="1">
						      			<xul:box class="Datum" anonid="box-notiz-ende"/>
										<xul:label value="Verfasser"/>
							      		<xul:textbox anonid="textbox-notiz-verfasser" disabled="true"/>
							      	</xul:hbox>
						      	</xul:row>
								<xul:row>
				      				<xul:label value="Bearbeiter"/>
				      				<xul:hbox>
				      					<xul:hbox flex="1">
								      		<xul:menulist anonid="menulist-notiz-bearbeiter"
													editable="true" flex="1"
													datasources="rdf:null"
													ref="http://www.technikum-wien.at/mitarbeiter/liste" 
													oninput="document.getBindingParent(this).BearbeiterLoad(this);"
													oncommand=""
											>
												<xul:template>
												<xul:menupopup>
													<xul:menuitem value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"
										        		      label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname ( rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid )"
													  		  uri="rdf:*"/>
												</xul:menupopup>
												</xul:template>
											</xul:menulist>
										</xul:hbox>
										
							      	</xul:hbox>
								</xul:row>
							</xul:rows>
					</xul:grid>
					<xul:hbox>
						<xul:spacer flex="1" />
						<xul:button anonid="button-notiz-speichern" oncommand="document.getBindingParent(this).Save()" label="Speichern" />
					</xul:hbox>
				</xul:groupbox>
		    </xul:vbox>
		</xul:vbox>
	</content>
	<implementation>
		<field name="TreeNotizDatasource" />
		<field name="selectID" />
		<property name="value">
			<getter>
				try
				{
					netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
					tree = document.getAnonymousElementByAttribute(this ,'anonid', 'tree-notiz');
					var col = tree.columns.getColumnFor(document.getAnonymousElementByAttribute(this ,'anonid', 'treecol-notiz-notiz_id'));
					return tree.view.getCellText(tree.currentIndex, col);
				}
				catch(e)
				{
					return false;
				}
			</getter>
			<setter>
				<![CDATA[
					debug("Value Setter not implemented");
				]]>
			</setter>
		</property>
		<method name="sort">
			<parameter name="treecol"/>
			<body>
			<![CDATA[
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			tree = document.getAnonymousElementByAttribute(this ,'anonid', 'tree-notiz');
					
			 var direction = treecol.getAttribute("sortDirection");
		       var current = treecol.parentNode.firstChild;
		       while (current) {
		          if (current.nodeType==1 && current.localName=="treecol") {
		             current.removeAttribute("sortDirection");
		          }
		          current = current.nextSibling;
		       }
		       if (direction=="ascending") {
		          direction = "descending";
		          treecol.setAttribute("sortDirection",direction);
		       } else {
		          direction = "ascending";
		          treecol.setAttribute("sortDirection",direction);
		       } 
			var sortService = Components.classes["@mozilla.org/xul/xul-sort-service;1"].
			                    getService(Components.interfaces.nsIXULSortService);
			sortService.sort(tree, treecol.getAttribute('sort'), direction);
			treecol.parentNode.parentNode.builder.rebuild();
			debug('sorted in dir:'+direction);
			]]>
			</body>
		</method>
		<method name="DisableDetails">
		<parameter name="val"/>
			<body>
			<![CDATA[
				document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-titel').disabled=val;
				document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-text').disabled=val;
				document.getAnonymousElementByAttribute(this ,'anonid', 'box-notiz-start').disabled=val;
				document.getAnonymousElementByAttribute(this ,'anonid', 'box-notiz-ende').disabled=val;
				document.getAnonymousElementByAttribute(this ,'anonid', 'menulist-notiz-bearbeiter').disabled=val;
				document.getAnonymousElementByAttribute(this ,'anonid', 'checkbox-notiz-erledigt').disabled=val;
				document.getAnonymousElementByAttribute(this ,'anonid', 'button-notiz-speichern').disabled=val;
			]]>
			</body>
		</method>
		<method name="DisableControls">
		<parameter name="val"/>
			<body>
			<![CDATA[
				document.getAnonymousElementByAttribute(this ,'anonid', 'toolbarbutton-notiz-neu').disabled=val;
				document.getAnonymousElementByAttribute(this ,'anonid', 'toolbarbutton-notiz-aktualisieren').disabled=val;
				document.getAnonymousElementByAttribute(this ,'anonid', 'toolbarbutton-notiz-filter').disabled=val;
			]]>
			</body>
		</method>
		<method name="ResetDetails">
			<body>
			<![CDATA[
				document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-notiz_id').value='';
				document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-titel').value='';
				document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-text').value='';
				document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-verfasser').value=getUsername();
				document.getAnonymousElementByAttribute(this ,'anonid', 'box-notiz-start').value='';
				document.getAnonymousElementByAttribute(this ,'anonid', 'box-notiz-ende').value='';
				document.getAnonymousElementByAttribute(this ,'anonid', 'menulist-notiz-bearbeiter').value='';
				document.getAnonymousElementByAttribute(this ,'anonid', 'checkbox-notiz-erledigt').checked=false;
			]]>
			</body>
		</method>
		<method name="Save">
			<body>
			<![CDATA[
				var notiz_id = document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-notiz_id').value;
				var titel = document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-titel').value;
				var text = document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-text').value;
				var start = document.getAnonymousElementByAttribute(this ,'anonid', 'box-notiz-start').iso;
				var ende = document.getAnonymousElementByAttribute(this ,'anonid', 'box-notiz-ende').iso;
				var verfasser_uid = document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-verfasser').value;
								
				menulist = document.getAnonymousElementByAttribute(this ,'anonid', 'menulist-notiz-bearbeiter');
	
				//Es kann sein, dass im Eingabefeld nichts steht und
				//trotzdem ein Eintrag auf selected gesetzt ist.
				//In diesem Fall soll aber kein Wert zurueckgegeben werden
				if(menulist.value=='')
				{
					bearbeiter_uid='';
				}
				else
				{				
					//Wenn es Selektierte Eintraege gibt, dann den value zurueckliefern
					var children = menulist.getElementsByAttribute('selected','true');
					if(children.length>0)
						bearbeiter_uid =  children[0].value;
					else
						bearbeiter_uid = '';
				}
					
				var erledigt = document.getAnonymousElementByAttribute(this ,'anonid', 'checkbox-notiz-erledigt').checked;
				
				var projekt_kurzbz = this.getAttribute('projekt_kurzbz');
				var projektphase_id = this.getAttribute('projektphase_id');
				var projekttask_id = this.getAttribute('projekttask_id');
				var uid = this.getAttribute('uid');
				var person_id = this.getAttribute('person_id');
				var prestudent_id = this.getAttribute('prestudent_id');
				var bestellung_id = this.getAttribute('bestellung_id');
				
				var soapBody = new SOAPObject("saveNotiz");
				//soapBody.appendChild(new SOAPObject("username")).val('joe');
				//soapBody.appendChild(new SOAPObject("passwort")).val('waschl');
				
				var notiz = new SOAPObject("notiz");
				notiz.appendChild(new SOAPObject("notiz_id")).val(notiz_id);
				notiz.appendChild(new SOAPObject("titel")).val(titel);
				notiz.appendChild(new SOAPObject("text")).val(text);
				notiz.appendChild(new SOAPObject("verfasser_uid")).val(verfasser_uid);
				notiz.appendChild(new SOAPObject("bearbeiter_uid")).val(bearbeiter_uid);
				notiz.appendChild(new SOAPObject("start")).val(start);
				notiz.appendChild(new SOAPObject("ende")).val(ende);
				notiz.appendChild(new SOAPObject("erledigt")).val(erledigt);
			
				notiz.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
				notiz.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
				notiz.appendChild(new SOAPObject("projekttask_id")).val(projekttask_id);
				notiz.appendChild(new SOAPObject("uid")).val(uid);
				notiz.appendChild(new SOAPObject("person_id")).val(person_id);
				notiz.appendChild(new SOAPObject("prestudent_id")).val(prestudent_id);
				notiz.appendChild(new SOAPObject("bestellung_id")).val(bestellung_id);
				soapBody.appendChild(notiz);
								
				var sr = new SOAPRequest("saveNotiz",soapBody);
			
				SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/notiz.soap.php?"+gettimestamp();
				
				 function mycallb(obj) {
				  var me=obj;
				  this.invoke=function (respObj) {
				    try
					{
						var id = respObj.Body[0].saveNotizResponse[0].message[0].Text;
						me.selectID=id;
					}
					catch(e)
					{
						try
						{
							var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
						}
						catch(e)
						{
							var fehler = e;
						}
						alert('Fehler: '+fehler);
						return;
					}
					me.RefreshNotiz();
				  }
				}
					 
				var cb=new mycallb(this);
					
				SOAPClient.SendRequest(sr, cb.invoke);
			
			]]>
			</body>
		</method>
		<method name="NeueNotiz">
			<body>
			<![CDATA[
				//debug('Neue Notiz');
				this.ResetDetails();
				this.DisableDetails(false);
				document.getAnonymousElementByAttribute(this ,'anonid', 'caption-notiz-detail').label="Neue Notiz";
				
				var sr = new SOAPRequest("saveNotiz",soapBody);
			
				SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/notiz.soap.php?"+gettimestamp();
			]]>
			</body>
		</method>
		<method name="RefreshNotiz">
			<body>
			<![CDATA[
				//debug('Refresh Notiz');
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				this.TreeNotizDatasource.Refresh(false); //non blocking
			]]>
			</body>
		</method>
		<method name="Loeschen">
			<body>
			<![CDATA[
			
				var notiz_id = document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-notiz_id').value;
				
				// falls nichts markiert ist
				if(notiz_id =='')
					alert('Keine Notiz ausgewählt')
				else
				{
					//Abfrage ob wirklich geloescht werden soll
					if (confirm('Wollen Sie die Notiz mit der ID: '+notiz_id+' wirklich loeschen?'))
					{
	
						var soapBody = new SOAPObject("deleteNotiz");
						//soapBody.appendChild(new SOAPObject("username")).val('joe');
						//soapBody.appendChild(new SOAPObject("passwort")).val('waschl');
						soapBody.appendChild(new SOAPObject("notiz_id")).val(notiz_id);
						
						var sr = new SOAPRequest("deleteNotiz",soapBody);
					
						SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/notiz.soap.php?"+gettimestamp();
						
						function mycallb(obj) {
						  var me=obj;
						  this.invoke=function (respObj) {
						    try
							{
								var id = respObj.Body[0].deleteNotizResponse[0].message[0].Text;
								me.selectID=id;
							}
							catch(e)
							{
								try
								{
									var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
								}
								catch(e)
								{
									var fehler = e;
								}
								alert('Fehler: '+fehler);
								return;
							}
							me.RefreshNotiz();
						  }
						}
						
						var cb=new mycallb(this);
						
						SOAPClient.SendRequest(sr, cb.invoke);
					}
				}
			]]>
			</body>
		</method>
		
		<method name="updateErledigt">
			<parameter name="event"/>
			<body>
			<![CDATA[
			    var row = new Object();
			    var col = new Object();
			    var childElt = new Object();
			    //Tree holen
			    var tree = event.currentTarget; 
			    //Treecol ermitteln in die geklickt wurde
			    tree.treeBoxObject.getCellAt(event.clientX, event.clientY, row, col, childElt);
			    //abbrechen wenn auf Header oder Scrollbar geklickt wurde
			    if(!col.value)
			    	return 0;
			    
				var val = tree.view.getCellValue(row.value, col.value);
				var text = tree.view.getCellText(row.value, col.value);
				
				var col = tree.columns.getColumnFor(document.getAnonymousElementByAttribute(this ,'anonid', 'treecol-notiz-notiz_id'));
				var id = tree.view.getCellText(row.value, col);
				document.getAnonymousElementByAttribute(this ,'anonid', 'toolbarbutton-notiz-del').disabled=false; 
				
				if(text=='erledigt')
				{
					var soapBody = new SOAPObject("setErledigt");
				    soapBody.appendChild(new SOAPObject("notiz_id")).val(id);
				    soapBody.appendChild(new SOAPObject("erledigt")).val(val);

				    var sr = new SOAPRequest("setErledigt",soapBody);
				    SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/notiz.soap.php?"+gettimestamp();
				    				    
				    SOAPClient.SendRequest(sr,function (respObj) {
					    try
						{
							var id = respObj.Body[0].setErledigtResponse[0].message[0].Text;
						}
						catch(e)
						{
							var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
							alert('Fehler: '+fehler);
							return;
						}
					  });
				}
			]]>
			</body>
		</method>
		<method name="edit">
			<parameter name="event"/>
			<body>
			<![CDATA[
			    var id = this.value;
			    
			    if(id!='')
			    {
			    	this.DisableDetails(false);
			    	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			    	//Daten holen
					var url = '<?php echo APP_ROOT ?>rdf/notiz.rdf.php?notiz_id='+id+'&'+gettimestamp();
						
					var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
				                   getService(Components.interfaces.nsIRDFService);
				    
				    var dsource = rdfService.GetDataSourceBlocking(url);
				    
					var subject = rdfService.GetResource("http://www.technikum-wien.at/notiz/" + id);
				
					var predicateNS = "http://www.technikum-wien.at/notiz/rdf";
				
					//RDF parsen
				
					titel = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titel" ));
					text = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#text" ));
					start = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#start" ));
					ende = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ende" ));
					verfasser = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#verfasser_uid" ));
					bearbeiter = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bearbeiter_uid" ));
					erledigt = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#erledigt" ));
					if(erledigt=='true')
						erledigt=true;
					else
						erledigt=false;
						
					document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-notiz_id').value=id;
					document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-titel').value=titel;
					document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-text').value=text;
					document.getAnonymousElementByAttribute(this ,'anonid', 'box-notiz-start').value=start;
					document.getAnonymousElementByAttribute(this ,'anonid', 'box-notiz-ende').value=ende;
					document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-verfasser').value=verfasser;
					document.getAnonymousElementByAttribute(this ,'anonid', 'checkbox-notiz-erledigt').checked=erledigt;
					if(bearbeiter!='')
					{
						menulist = document.getAnonymousElementByAttribute(this ,'anonid', 'menulist-notiz-bearbeiter');
						this.BearbeiterLoad(menulist, bearbeiter);
						
						var children = menulist.getElementsByAttribute('value',bearbeiter);
						menulist.selectedItem=children[0];	
					}
					else
					{
						menulist = document.getAnonymousElementByAttribute(this ,'anonid', 'menulist-notiz-bearbeiter');
						this.BearbeiterLoad(menulist, bearbeiter);
						
						var children = menulist.getElementsByAttribute('value',bearbeiter);
						menulist.selectedItem=null;
					}
					document.getAnonymousElementByAttribute(this ,'anonid', 'caption-notiz-detail').label="Bearbeiten";

			    }
			]]>
			</body>
		</method>
		<method name="BearbeiterLoad">
			<parameter name="menulist" />
			<parameter name="filter" />
			<body>
			<![CDATA[
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		
				if(typeof(filter)=='undefined')
					v = menulist.value;
				else
					v = filter;
			
				if(v.length>2)
				{		
					var url = '<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php?filter='+encodeURIComponent(v)+'&'+gettimestamp();
					
					var oldDatasources = menulist.database.GetDataSources();
					while(oldDatasources.hasMoreElements())
					{
						menulist.database.RemoveDataSource(oldDatasources.getNext());
					}
					//Refresh damit die entfernten DS auch wirklich entfernt werden
					menulist.builder.rebuild();
				
					var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
					if(typeof(filter)=='undefined')
						var datasource = rdfService.GetDataSource(url);
					else
						var datasource = rdfService.GetDataSourceBlocking(url);
					datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
					datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
					menulist.database.AddDataSource(datasource);
					if(typeof(filter)!='undefined')
						menulist.builder.rebuild();
				}
			]]>
			</body>
		</method>
		<method name="LoadNotizTree">
			<parameter name="projekt_kurzbz"/>
			<parameter name="projektphase_id"/>
			<parameter name="projekttask_id"/>
			<parameter name="uid"/>
			<parameter name="person_id"/>
			<parameter name="prestudent_id"/>
			<parameter name="bestellung_id"/>
			<parameter name="user"/>
			<parameter name="erledigt"/>
			<body>
			<![CDATA[
				//debug('LoadNotizTree');
				 netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				
				try
				{
					this.initialsiert=false;
					var tree = document.getAnonymousElementByAttribute(this,'anonid', 'tree-notiz');
					if(tree.view)
						tree.view.selection.clearSelection();
					this.DisableControls(false);
					this.DisableDetails(true);
					this.ResetDetails();
									
					
					this.setAttribute('projekt_kurzbz',projekt_kurzbz);
					this.setAttribute('projektphase_id',projektphase_id);
					this.setAttribute('projekttask_id',projekttask_id);
					this.setAttribute('uid',uid);
					this.setAttribute('person_id',person_id);
					this.setAttribute('prestudent_id',prestudent_id);
					this.setAttribute('bestellung_id',bestellung_id);
					this.setAttribute('user',user);
					
					//Wenn kein Erledigt Parameter uebergeben wird, dann wird die zuletzt 
					//verwendete Einstellung verwendet
					if(typeof erledigt=="undefined")
						erledigt = this.getAttribute('erledigt');

					if(typeof erledigt!="undefined")
						this.setAttribute('erledigt',erledigt);

				
					var datasource="<?php echo APP_ROOT; ?>rdf/notiz.rdf.php?ts="+gettimestamp();
					datasource = datasource+"&projekt_kurzbz="+encodeURIComponent(projekt_kurzbz);
					datasource = datasource+"&projektphase_id="+encodeURIComponent(projektphase_id);
					datasource = datasource+"&projekttask_id="+encodeURIComponent(projekttask_id);
					datasource = datasource+"&uid="+encodeURIComponent(uid);
					datasource = datasource+"&person_id="+encodeURIComponent(person_id);
					datasource = datasource+"&prestudent_id="+encodeURIComponent(prestudent_id);
					datasource = datasource+"&bestellung_id="+encodeURIComponent(bestellung_id);
					datasource = datasource+"&user="+encodeURIComponent(user);

					//Wenn es als Parameter uebergeben wird, ist es ein boolean, sonst ein String
					if((typeof erledigt=="boolean" && erledigt==true) || (typeof erledigt=="string" && erledigt=='true'))
						datasource = datasource+"&erledigt=true";
					else if((typeof erledigt=="boolean" && erledigt==false)	|| (typeof erledigt=="string" && erledigt=='false'))
						datasource = datasource+"&erledigt=false";
					
					
					//debug('Source:'+datasource);
	                var tree = document.getAnonymousElementByAttribute(this ,'anonid', 'tree-notiz');

	                //Alte DS entfernen
	                var oldDatasources = tree.database.GetDataSources();
	                while(oldDatasources.hasMoreElements())
	                {
	                    tree.database.RemoveDataSource(oldDatasources.getNext());
	                }
		               	                 
	                var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	                this.TreeNotizDatasource = rdfService.GetDataSource(datasource);
	                this.TreeNotizDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	                this.TreeNotizDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	                tree.database.AddDataSource(this.TreeNotizDatasource);
	                
	                this.TreeNotizDatasource.addXMLSinkObserver({
	                  notiz: this,
					  onBeginLoad: function(aSink)
					    {},
					
					  onInterrupt: function(aSink)
					    {},
					
					  onResume: function(aSink)
					    {},
					
					  onEndLoad: function(aSink)
					    { 
					     	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
					    
					      //aSink.removeXMLSinkObserver(this);
					      //debug('onEndLoad start Rebuild');
					      var tree = document.getAnonymousElementByAttribute(this.notiz ,'anonid', 'tree-notiz');
						  tree.builder.rebuild();
					    },
					
					  onError: function(aSink, aStatus, aErrorMsg)
					    { alert("error! " + aErrorMsg); }
					});
	                tree.builder.addListener({
	                	notiz: this,
						willRebuild : function(builder)
						{
						},
						didRebuild : function(builder)
					  	{
					  		//Workaround damit das Resize des Trees funktioniert
					  		var tree = document.getAnonymousElementByAttribute(this.notiz ,'anonid', 'tree-notiz');
					  		if(tree.columns)
								tree.columns.restoreNaturalOrder();
							notiz.selectItem();
						}
					});
					
				}
				catch(e)
				{
					debug("Notiz load failed with exception: "+e);
				}
			]]>
			</body>
		</method>
		<method name="selectItem">
			<body>
			<![CDATA[
				var tree=document.getAnonymousElementByAttribute(this,'anonid', 'tree-notiz');
				if(tree.view)
				{
					var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
				
					//In der globalen Variable ist die zu selektierende ID gespeichert
					if(this.selectID!=null)
					{
					   	for(var i=0;i<items;i++)
					   	{
					   		//id der row holen
							var col = tree.columns.getColumnFor(document.getAnonymousElementByAttribute(this ,'anonid', 'treecol-notiz-notiz_id'));
							id = tree.view.getCellText(i, col);
						
							//wenn dies die zu selektierende Zeile
							if(this.selectID==id)
							{
								//Zeile markieren
								tree.view.selection.select(i);
								//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
								tree.treeBoxObject.ensureRowIsVisible(i);
								this.selectID=null;
								
								return true;
							}
					   	}
					}
				}				
			]]>
			</body>
		</method>
		<method name="openNotiz">
			<parameter name="id"/>
			<body>
			<![CDATA[
				var projekt_kurzbz = this.getAttribute('projekt_kurzbz');
				var projektphase_id = this.getAttribute('projektphase_id');
				var projekttask_id = this.getAttribute('projekttask_id');
				var uid = this.getAttribute('uid');
				var person_id = this.getAttribute('person_id');
				var prestudent_id = this.getAttribute('prestudent_id');
				var bestellung_id = this.getAttribute('bestellung_id');
				
				var opener_id = this.getAttribute('id');
				
				var param = ''; 
				
				param = param+'?projekt_kurzbz='+encodeURIComponent(projekt_kurzbz);
				param = param+'&projektphase_id='+encodeURIComponent(projektphase_id);
				param = param+'&projekttask_id='+encodeURIComponent(projekttask_id);
				param = param+'&uid='+encodeURIComponent(uid);
				param = param+'&person_id='+encodeURIComponent(person_id);
				param = param+'&prestudent_id='+encodeURIComponent(prestudent_id);
				param = param+'&bestellung_id='+encodeURIComponent(bestellung_id);
				
				param = param+'&opener_id='+encodeURIComponent(opener_id);
				if(id!=undefined)
					param = param+'&id='+id;  
				
				
			    window.open('<?php echo APP_ROOT; ?>content/notiz.window.xul.php'+param,'Notiz','chrome, status=no, width=500, height=350, centerscreen, resizable');
			]]>
			</body>
		</method>
		
		<constructor>
			this.DisableControls(true);
			this.DisableDetails(true);
			var projekt_kurzbz = this.getAttribute('projekt_kurzbz');
			var projektphase_id = this.getAttribute('projektphase_id');
			var projekttask_id = this.getAttribute('projekttask_id');
			var uid = this.getAttribute('uid');
			var person_id = this.getAttribute('person_id');
			var prestudent_id = this.getAttribute('prestudent_id');
			var bestellung_id = this.getAttribute('bestellung_id');
			var user = this.getAttribute('user');
			
			if(projekt_kurzbz!='' || projektphase_id!='' || projekttask_id!='' 
			   || uid!='' || person_id!='' || prestudent_id!='' || bestellung_id!='' || user!='')
			{
				this.LoadNotizTree(projekt_kurzbz,projektphase_id,projekttask_id,uid,person_id,prestudent_id,bestellung_id, user);
			}
			document.getAnonymousElementByAttribute(this ,'anonid', 'textbox-notiz-verfasser').value=getUsername();
		</constructor>
		<destructor>
			//debug('Notiz Binding Stop');
		</destructor>
	</implementation>
	
  </binding>
</bindings>
