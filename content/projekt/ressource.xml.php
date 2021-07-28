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
require_once('../../config/vilesci.config.inc.php');
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
  <binding id="Ressource">
  	<content>
		<xul:vbox flex="1">
			<xul:popupset>
				<xul:popup anonid="ressource-tree-popup">
					<xul:menuitem label="Erledigt"/>
				</xul:popup>
			</xul:popupset>
			<xul:toolbox>
				<xul:toolbar >
					<xul:toolbarbutton label="Aktualisieren" oncommand="document.getBindingParent(this).RefreshRessource()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
                    <xul:toolbarbutton label="Loeschen" oncommand="document.getBindingParent(this).DeleteRessource(document.getBindingParent(this).value)" disabled="false" image="../skin/images/DeleteIcon.png" tooltiptext="Ressource löschen"/>
				</xul:toolbar>
			</xul:toolbox>
			<xul:tree anonid="tree-ressource"
			seltype="single" hidecolumnpicker="false" flex="1"
			datasources="rdf:null" ref="http://www.technikum-wien.at/ressource/liste"
			ondblclick="document.getBindingParent(this).openProjektRessource(document.getBindingParent(this).value);"
			ondrop="nsDragAndDrop.drop(event,
				{
					getSupportedFlavours : function ()
					{
				  	  	var flavours = new FlavourSet();
				  	  	flavours.appendFlavour('application/fhc-ressource');
				  	  	return flavours;
				  	},
				  	onDrop: function (evt,dropdata,session)
				  	{
					    document.getBindingParent(event.target).AddRessource(dropdata.data);
				  	},
				  	onDragStart: function (evt,transferData,action){}
				})"
			ondragdrop="nsDragAndDrop.drop(event,
				{
					getSupportedFlavours : function ()
					{
				  	  	var flavours = new FlavourSet();
				  	  	flavours.appendFlavour('application/fhc-ressource');
				  	  	return flavours;
				  	},
				  	onDrop: function (evt,dropdata,session)
				  	{
					    document.getBindingParent(event.target).AddRessource(dropdata.data);
				  	},
				  	onDragStart: function (evt,transferData,action){}
				})"
			ondragover="nsDragAndDrop.dragOver(event,{
				getSupportedFlavours : function ()
				{
			  	  	var flavours = new FlavourSet();
			  	  	flavours.appendFlavour('application/fhc-ressource');
			  	  	return flavours;
			  	},
			  	onDragEnter: function (evt,flavour,session)
				{
				},
				onDragExit: function (evt,flavour,session)
				{
			  	},
			  	onDragOver: function(evt,flavour,session)
			  	{
			  		evt.preventDefault();
			  	},
			  	onDrop: function (evt,dropdata,session)
			  	{
			  	},
			  	onDragStart: function (evt,transferData,action)
				{
			  	}
			})"
			ondragenter="nsDragAndDrop.dragEnter(event,{
				getSupportedFlavours : function ()
				{
			  	  	var flavours = new FlavourSet();
			  	  	flavours.appendFlavour('application/fhc-ressource');
			  	  	return flavours;
			  	},
			  	onDragEnter: function (evt,flavour,session)
				{
				},
				onDragExit: function (evt,flavour,session)
				{
			  	},
			  	onDragOver: function(evt,flavour,session)
			  	{
			  	},
			  	onDrop: function (evt,dropdata,session)
			  	{
			  	},
			  	onDragStart: function (evt,transferData,action)
				{
			  	}
			})"
			ondragexit="nsDragAndDrop.dragExit(event,{
				getSupportedFlavours : function ()
				{
			  	  	var flavours = new FlavourSet();
			  	  	flavours.appendFlavour('application/fhc-ressource');
			  	  	return flavours;
			  	},
			  	onDragEnter: function (evt,flavour,session)
				{
				},
				onDragExit: function (evt,flavour,session)
				{
			  	},
			  	onDragOver: function(evt,flavour,session)
			  	{
			  	},
			  	onDrop: function (evt,dropdata,session)
			  	{
			  	},
			  	onDragStart: function (evt,transferData,action)
				{
			  	}
			})"
			>
			<xul:treecols>
				<xul:treecol anonid="treecol-ressource-bezeichnung" label="Bezeichnung" flex="2" primary="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#bezeichnung" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-description" label="Anzeige" flex="5" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#description"  />
				<xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-aufwand" label="Aufwand" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#aufwand" />
					<xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-funktion_kurzbz" label="Funktion" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#funktion_kurzbz" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-typ" label="Typ" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#typ" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-ressource_id" label="ID" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#ressource_uid" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-beschreibung" label="Beschreibung" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#beschreibung" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-mitarbeiter_uid" label="MitarbeiterUID" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#mitarbeiter_uid" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-student_uid" label="StudentInUID" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#student_uid" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-betriebsmittel_id" label="BetriebsmittelID" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#betriebsmittel_id" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-firma_id" label="FirmaID" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#firma_id" />
					<xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-projekt_ressource_id" label="ProjektRessourceID" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#projekt_ressource_id" />
			</xul:treecols>

			<xul:template>
			    <xul:rule>
			      <xul:treechildren>
			       <xul:treeitem uri="rdf:*">
			         <xul:treerow>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#bezeichnung"/>
					   <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#rdf_description"/>
					   <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#aufwand"/>
					   <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#funktion_kurzbz"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#typ"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#ressource_id"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#beschreibung"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#mitarbeiter_uid"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#student_uid"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#betriebsmittel_id"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#firma_id"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#projekt_ressource_id"/>
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
		</xul:vbox>
	</content>
	<implementation>
		<field name="TreeRessourceDatasource" />
		<property name="value">
			<getter>
				try
				{
					netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
					tree = document.getAnonymousElementByAttribute(this ,'anonid', 'tree-ressource');
					var col = tree.columns.getColumnFor(document.getAnonymousElementByAttribute(this ,'anonid', 'treecol-ressource-ressource_id'));
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
		<method name="RefreshRessource">
			<body>
			<![CDATA[
				//debug('Refresh Notiz');
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				this.TreeRessourceDatasource.Refresh(false); //non blocking
			]]>
			</body>
		</method>
        <method name="DeleteRessource">
            <parameter name="ressource_id"/>
		    <body>
			<![CDATA[
				//debug('Refresh Notiz');
                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
                try
                {
                    var col = tree.columns.getColumnFor(document.getAnonymousElementByAttribute(this ,'anonid', 'treecol-ressource-projekt_ressource_id'));
                    var projekt_ressource_id =  tree.view.getCellText(tree.currentIndex, col);
                    var projekt_kurzbz = this.getAttribute('projekt_kurzbz');
                    var projektphase_id = this.getAttribute('projektphase_id');
                    //var projekt_ressource_id = this.getAttribute('projekt_ressource_id');

                    var soapBody = new SOAPObject("deleteProjektRessource");
                    //soapBody.appendChild(new SOAPObject("username")).val('joe');
                    //soapBody.appendChild(new SOAPObject("passwort")).val('waschl');

                    var projektRessource = new SOAPObject("projektRessource");

                    if(projekt_kurzbz != '')
                    {
                        projektRessource.appendChild(new SOAPObject("projektphase_id")).val('');
                        projektRessource.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
                    }else if(projektphase_id != '')
                    {
                        projektRessource.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
                        projektRessource.appendChild(new SOAPObject("projekt_kurzbz")).val('');
                    }
                    projektRessource.appendChild(new SOAPObject("ressource_id")).val(ressource_id);
                    projektRessource.appendChild(new SOAPObject("projekt_ressource_id")).val(projekt_ressource_id);
                    soapBody.appendChild(projektRessource);

                    var sr = new SOAPRequest("deleteProjektRessource",soapBody);
				    SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/ressource_projekt.soap.php?"+gettimestamp();

				    function mycallb(obj) {
					  var me=obj;
					  this.invoke=function (respObj) {
					    try
						{
							var id = respObj.Body[0].deleteProjektRessourceResponse[0].message[0].Text;
						}
						catch(e)
						{
							var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
							alert('Fehler: '+fehler);
							return;
						}
						me.RefreshRessource();
					  }
					}
					var cb=new mycallb(this);

				    SOAPClient.SendRequest(sr,cb.invoke);

				}
				catch(e)
				{
					debug("Ressource load failed with exception: "+e);
				}


			]]>
			</body>
		</method>
		<method name="LoadRessourceTree">
			<parameter name="projekt_kurzbz"/>
			<parameter name="projektphase_id"/>
			<body>
			<![CDATA[
				 netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

				try
				{
					this.setAttribute('projekt_kurzbz',projekt_kurzbz);
					this.setAttribute('projektphase_id',projektphase_id);

					if(projekt_kurzbz!='')
					{
						var datasource="<?php echo APP_ROOT; ?>rdf/ressource.rdf.php?ts="+gettimestamp();
						datasource = datasource+"&projekt_kurzbz="+encodeURIComponent(projekt_kurzbz);
					}
					else if(projektphase_id!='')
					{
						var datasource="<?php echo APP_ROOT; ?>rdf/ressource.rdf.php?ts="+gettimestamp();
						datasource = datasource+"&projekt_phase="+encodeURIComponent(projektphase_id);
					}

					//debug('Source:'+datasource);
	                var tree = document.getAnonymousElementByAttribute(this ,'anonid', 'tree-ressource');

	                //Alte DS entfernen
	                var oldDatasources = tree.database.GetDataSources();
	                while(oldDatasources.hasMoreElements())
	                {
	                    tree.database.RemoveDataSource(oldDatasources.getNext());
	                }

	                var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	                this.TreeRessourceDatasource = rdfService.GetDataSource(datasource);
	                this.TreeRessourceDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	                this.TreeRessourceDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	                tree.database.AddDataSource(this.TreeRessourceDatasource);

	                this.TreeRessourceDatasource.addXMLSinkObserver({
	                  ressource: this,
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
					      var tree = document.getAnonymousElementByAttribute(this.ressource ,'anonid', 'tree-ressource');
						  tree.builder.rebuild();
					    },

					  onError: function(aSink, aStatus, aErrorMsg)
					    { alert("error! " + aErrorMsg); }
					});
	                tree.builder.addListener({
	                	ressource: this,
						willRebuild : function(builder)
						{
						},
						didRebuild : function(builder)
					  	{
							var tree = document.getAnonymousElementByAttribute(this.ressource ,'anonid', 'tree-ressource');

						  	//Workaround damit das Resize des Trees funktioniert
							tree.columns.restoreNaturalOrder();

					  		//Nach dem Laden alle Subtrees aufklappen
					  		var treeView = tree.treeBoxObject.view;
							for (var i = 0; i < treeView.rowCount; i++)
							{
								if (treeView.isContainer(i) && !treeView.isContainerOpen(i))
								treeView.toggleOpenState(i);
							}
						}
					});
				}
				catch(e)
				{
					debug("Ressource load failed with exception: "+e);
				}
			]]>
			</body>
		</method>
		<method name="AddRessource">
			<parameter name="id"/>
			<body>
			<![CDATA[
				 netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

				try
				{
					var projekt_kurzbz = this.getAttribute('projekt_kurzbz');
					var projektphase_id = this.getAttribute('projektphase_id');
					var funktion_kurzbz = this.getAttribute('funktion_kurzbz');
					//debug(projekt_kurzbz);
					//debug(id);

					var soapBody = new SOAPObject("saveProjektRessource");
				    //soapBody.appendChild(new SOAPObject("username")).val('joe');
					//soapBody.appendChild(new SOAPObject("passwort")).val('waschl');

					var projektRessource = new SOAPObject("projektRessource");

				    projektRessource.appendChild(new SOAPObject("projekt_ressource_id")).val('');

				    if(projekt_kurzbz != '')
				    {
					    projektRessource.appendChild(new SOAPObject("projektphase_id")).val('');
					    projektRessource.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
				    }else if(projektphase_id != '')
				    {
					    projektRessource.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
					    projektRessource.appendChild(new SOAPObject("projekt_kurzbz")).val('');
				    }
				    projektRessource.appendChild(new SOAPObject("ressource_id")).val(id);
				    projektRessource.appendChild(new SOAPObject("funktion_kurzbz")).val('Mitarbeiter');
				    projektRessource.appendChild(new SOAPObject("beschreibung")).val('');

					soapBody.appendChild(projektRessource);

				    var sr = new SOAPRequest("saveProjektRessource",soapBody);
				    SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/ressource_projekt.soap.php?"+gettimestamp();

				    function mycallb(obj) {
					  var me=obj;
					  this.invoke=function (respObj) {
					    try
						{
							var id = respObj.Body[0].saveProjektRessourceResponse[0].message[0].Text;
						}
						catch(e)
						{
							var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
							alert('Fehler: '+fehler);
							return;
						}
						me.RefreshRessource();
					  }
					}

					var cb=new mycallb(this);

				    SOAPClient.SendRequest(sr,cb.invoke);

				}
				catch(e)
				{
					debug("Ressource load failed with exception: "+e);
				}


			]]>
			</body>
		</method>
		<method name="openProjektRessource">
			<parameter name="id"/>
				<body>
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				var col = tree.columns.getColumnFor(document.getAnonymousElementByAttribute(this ,'anonid', 'treecol-ressource-projekt_ressource_id'));
				var projekt_ressource_id =  tree.view.getCellText(tree.currentIndex, col);
				var vonlinks=screen.width/2;
				var vonoben = screen.height/2;
				window.open('<?php echo APP_ROOT; ?>content/projekt/projekt_ressource.window.xul.php?id='+projekt_ressource_id,'Projektressource verwalten', 'height=200, width=300,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no, left='+vonlinks+', top='+vonoben);
				</body>
		</method>
		<method name="openRessource">
			<parameter name="id"/>
			<body>
			<![CDATA[
				var projekt_kurzbz = this.getAttribute('projekt_kurzbz');
				var projektphase_id = this.getAttribute('projektphase_id');


				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				tree = document.getAnonymousElementByAttribute(this ,'anonid', 'tree-ressource');
				var col = tree.columns.getColumnFor(document.getAnonymousElementByAttribute(this ,'anonid', 'treecol-ressource-aufwand'));
				aufwand =  tree.view.getCellText(tree.currentIndex, col);

				var col = tree.columns.getColumnFor(document.getAnonymousElementByAttribute(this ,'anonid', 'treecol-ressource-projekt_ressource_id'));
				var projekt_ressource_id =  tree.view.getCellText(tree.currentIndex, col);


				var col = tree.columns.getColumnFor(document.getAnonymousElementByAttribute(this ,'anonid', 'treecol-ressource-beschreibung'));
				var beschreibung =  tree.view.getCellText(tree.currentIndex, col);

				var col = tree.columns.getColumnFor(document.getAnonymousElementByAttribute(this ,'anonid', 'treecol-ressource-funktion_kurzbz'));
				var funktion_kurzbz =  tree.view.getCellText(tree.currentIndex, col);

				if(projekt_ressource_id!='')
				{
					if(aufwand = prompt("Aufwand:",aufwand))
					{
						try
						{
							var soapBody = new SOAPObject("saveProjektRessource");
							var projektRessource = new SOAPObject("projektRessource");

						    projektRessource.appendChild(new SOAPObject("projekt_ressource_id")).val(projekt_ressource_id);

						    if(projekt_kurzbz != '')
						    {
							    projektRessource.appendChild(new SOAPObject("projektphase_id")).val('');
							    projektRessource.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
						    }
						    else if(projektphase_id != '')
						    {
							    projektRessource.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
							    projektRessource.appendChild(new SOAPObject("projekt_kurzbz")).val('');
						    }

						    projektRessource.appendChild(new SOAPObject("ressource_id")).val(id);
						    projektRessource.appendChild(new SOAPObject("funktion_kurzbz")).val(funktion_kurzbz);
						    projektRessource.appendChild(new SOAPObject("beschreibung")).val(beschreibung);
						    projektRessource.appendChild(new SOAPObject("aufwand")).val(aufwand);

							soapBody.appendChild(projektRessource);

						    var sr = new SOAPRequest("saveProjektRessource",soapBody);
						    SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/ressource_projekt.soap.php?"+gettimestamp();

						    function mycallb(obj)
						    {
								  var me=obj;
								  this.invoke=function (respObj)
								  {
									    try
										{
											var id = respObj.Body[0].saveProjektRessourceResponse[0].message[0].Text;
										}
										catch(e)
										{
											var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
											alert('Fehler: '+fehler);
											return;
										}
										me.RefreshRessource();
								  }
							}

							var cb=new mycallb(this);

						    SOAPClient.SendRequest(sr,cb.invoke);

						}
						catch(e)
						{
							debug("Ressource load failed with exception: "+e);
						}
					}
				}
			]]>
			</body>
		</method>
		<constructor>
			var projekt_kurzbz = this.getAttribute('projekt_kurzbz');
			var projektphase_id = this.getAttribute('projektphase_id');

			if(projekt_kurzbz!='')
			{
				this.LoadRessourceTree(projekt_kurzbz);
			}
		</constructor>
		<destructor>
			//debug('Notiz Binding Stop');
		</destructor>
	</implementation>

  </binding>
</bindings>
