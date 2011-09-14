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
					<xul:toolbarbutton label="Neue Notiz" oncommand="document.getBindingParent(this).NeueNotiz()" image="../skin/images/NeuDokument.png" tooltiptext="Neue Notiz anlegen" />
					<xul:toolbarbutton label="Aktualisieren" oncommand="document.getBindingParent(this).RefreshNotiz()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
				</xul:toolbar>
			</xul:toolbox>
			<xul:tree anonid="tree-notiz"
			seltype="single" hidecolumnpicker="false" flex="1"
			datasources="rdf:null" ref="http://www.technikum-wien.at/notiz/liste"
			ondblclick="document.getBindingParent(this).openNotiz(document.getBindingParent(this).value);"
			>
			<xul:treecols>
			    <xul:treecol anonid="treecol-notiz-titel" label="Titel" flex="5" primary="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#titel"  />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-text" label="Text" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#text" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-verfasser" label="Verfasser" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#verfasser_uid" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-bearbeiter" label="Bearbeiter" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#bearbeiter_uid" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-start" label="Start" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#startISO" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-ende" label="Ende" flex="2" hidden="false" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#endeISO" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-erledigt" label="Erledigt" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#erledigt" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-notiz_id" label="NotizID" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#notiz_id" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-startISO" label="StartISO" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/notiz/rdf#startISO" />
			    <xul:splitter class="tree-splitter"/>
			    <xul:treecol anonid="treecol-notiz-ende" label="EndeISO" flex="2" hidden="true" persist="hidden width ordinal"
					class="sortDirectionIndicator"
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
			           <xul:treecell label="rdf:http://www.technikum-wien.at/notiz/rdf#erledigt"/>
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
		</xul:vbox>
	</content>
	<implementation>
		<field name="TreeNotizDatasource" />
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
		<method name="NeueNotiz">
			<body>
			<![CDATA[
				//debug('Neue Notiz');
				this.openNotiz();
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
		<method name="LoadNotizTree">
			<parameter name="projekt_kurzbz"/>
			<parameter name="projektphase_id"/>
			<parameter name="projekttask_id"/>
			<parameter name="uid"/>
			<parameter name="person_id"/>
			<parameter name="prestudent_id"/>
			<parameter name="bestellung_id"/>
			<parameter name="addobserver"/>
			<body>
			<![CDATA[
				//debug('LoadNotizTree');
				 netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				
				try
				{
					this.setAttribute('projekt_kurzbz',projekt_kurzbz);
					this.setAttribute('projektphase_id',projektphase_id);
					this.setAttribute('projekttask_id',projekttask_id);
					this.setAttribute('uid',uid);
					this.setAttribute('person_id',person_id);
					this.setAttribute('prestudent_id',prestudent_id);
					this.setAttribute('bestellung_id',bestellung_id);
				
					var datasource="<?php echo APP_ROOT; ?>rdf/notiz.rdf.php?ts="+gettimestamp();
					datasource = datasource+"&projekt_kurzbz="+encodeURIComponent(projekt_kurzbz);
					datasource = datasource+"&projektphase_id="+encodeURIComponent(projektphase_id);
					datasource = datasource+"&projekttask_id="+encodeURIComponent(projekttask_id);
					datasource = datasource+"&uid="+encodeURIComponent(uid);
					datasource = datasource+"&person_id="+encodeURIComponent(person_id);
					datasource = datasource+"&prestudent_id="+encodeURIComponent(prestudent_id);
					datasource = datasource+"&bestellung_id="+encodeURIComponent(bestellung_id);
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
			var projekt_kurzbz = this.getAttribute('projekt_kurzbz');
			var projektphase_id = this.getAttribute('projektphase_id');
			var projekttask_id = this.getAttribute('projekttask_id');
			var uid = this.getAttribute('uid');
			var person_id = this.getAttribute('person_id');
			var prestudent_id = this.getAttribute('prestudent_id');
			var bestellung_id = this.getAttribute('bestellung_id');
			
			if(projekt_kurzbz!='' || projektphase_id!='' || projekttask_id!='' 
			   || uid!='' || person_id!='' || prestudent_id!='' || bestellung_id!='')
			{
				this.LoadNotizTree(projekt_kurzbz,projektphase_id,projekttask_id,uid,person_id,prestudent_id,bestellung_id, true);
			}
		</constructor>
		<destructor>
			//debug('Notiz Binding Stop');
		</destructor>
	</implementation>
	
  </binding>
</bindings>
