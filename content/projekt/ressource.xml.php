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
				</xul:toolbar>
			</xul:toolbox>
			<xul:tree anonid="tree-ressource"
			seltype="single" hidecolumnpicker="false" flex="1"
			datasources="rdf:null" ref="http://www.technikum-wien.at/ressource/liste"
			ondblclick="document.getBindingParent(this).openNotiz(document.getBindingParent(this).value);"
			>
			<xul:treecols>
				<xul:treecol anonid="treecol-ressource-bezeichnung" label="Bezeichnung" flex="2" primary="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#bezeichnung" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-ressource-descriptioin" label="Anzeige" flex="5" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/ressource/rdf#description"  />
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
			    <xul:treecol anonid="treecol-ressource-student_uid" label="StudentUID" flex="2" hidden="true" persist="hidden width ordinal"
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
			</xul:treecols>
		
			<xul:template>
			    <xul:rule>
			      <xul:treechildren>
			       <xul:treeitem uri="rdf:*">
			         <xul:treerow>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#bezeichnung"/>
					   <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#rdf_description"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#typ"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#ressource_id"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#beschreibung"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#mitarbeiter_uid"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#student_uid"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#betriebsmittel_id"/>
			           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#firma_id"/>
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
				this.TreeNotizDatasource.Refresh(false); //non blocking
			]]>
			</body>
		</method>
		<method name="LoadRessourceTree">
			<parameter name="projekt_kurzbz"/>
			<parameter name="projektphase_id"/>
			<body>
			<![CDATA[
				//debug('LoadNotizTree');
				 netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				
				try
				{
					this.setAttribute('projekt_kurzbz',projekt_kurzbz);
					this.setAttribute('projektphase_id',projektphase_id);

					if(projekt_kurzbz!='')
					{
						var datasource="<?php echo APP_ROOT; ?>rdf/ressource.rdf.php?ts="+gettimestamp();
						datasource = datasource+"&projekt_kurzbz="+encodeURIComponent(projekt_kurzbz);
					}else if(projektphase_id!='')
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
					      var tree = document.getAnonymousElementByAttribute(this.notiz ,'anonid', 'tree-ressource');
						  tree.builder.rebuild();
					    },
					
					  onError: function(aSink, aStatus, aErrorMsg)
					    { alert("error! " + aErrorMsg); }
					});
	                tree.builder.addListener({
						willRebuild : function(builder)
						{
						},
						didRebuild : function(builder)
					  	{
					  		//debug("didrebuild");
					  		//builder.removeListener(this);
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
