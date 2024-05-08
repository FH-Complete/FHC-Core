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
  Binding fuer das Datumfeld
  Zeigt ein DropDown Menue mit einem Kalender zur Datumsauswahl an
  und ueberprueft das eingegebene Datum auf Gueltigkeit
  -->
  <binding id="Datum">
  	<content>
		<!--<xul:textbox maxlength="10" xbl:inherits="disabled, value" size="10" tooltiptext="Format: DD.MM.JJJJ Beispiel: 31.12.2007"/>-->
		<xul:hbox>
			<xul:spacer flex="1"/>
			<xul:menulist anonid="binding-menulist-field" style="padding: 1px" editable="true" xbl:inherits="disabled, value" label="">
				<xul:menupopup>
					<xul:datepicker anonid="binding-datepicker-field" 
								onselect="parentNode.parentNode.parentNode.parentNode.value=this.selection.currentDay+'.'+(parseInt(this.selection.currentMonth)+1)+'.'+this.selection.currentYear; var evt = parentNode.parentNode.ownerDocument.createEvent('HTMLEvents');evt.initEvent('change', true, true );parentNode.parentNode.dispatchEvent( evt );"/>
				</xul:menupopup>		
			</xul:menulist>
			<xul:spacer flex="1"/>
		</xul:hbox>
	</content>
	<implementation>
		<property name="value" onget="return document.getAnonymousElementByAttribute(this ,'anonid', 'binding-menulist-field').label" >
			<setter>
				<![CDATA[
				menulist = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-menulist-field')
				picker = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-datepicker-field')
				//Wert setzen
				menulist.label=val;
				
				if(val!='')
				{
					//Wenn das Datum stimmt, dann wird der Hintergrund auf Weiss gesetzt und
					//das Datum im Datepicker gesetzt
					//Wenn das Datum falsch ist, dann wird der Hintergrund auf Rot gesetzt
					if(CheckDatum(val))
					{
						parts = val.split('.');
	
						dat = new Date(parts[2],parts[1]-1,parts[0]);
						picker.view.setDate(dat);		
						picker.selection.setDate(dat);
						menulist.style.backgroundColor="#FFFFFF";
					}
					else
						menulist.style.backgroundColor="#F46B6B";
				}
				else
				{
					if(!menulist.disabled)
						menulist.style.backgroundColor="#FFFFFF";
				}
					
				]]>
			</setter>
		</property>
		<property name="iso" onget="return ConvertDateToISO(document.getAnonymousElementByAttribute(this ,'anonid', 'binding-menulist-field').label)" >
			<setter>
			<![CDATA[
				return false;
			]]>
			</setter>
		</property>
		<property name="disabled" onget="return document.getAnonymousElementByAttribute(this ,'anonid', 'binding-menulist-field').disabled" >
			<setter>
			<![CDATA[
				document.getAnonymousElementByAttribute(this ,'anonid', 'binding-menulist-field').disabled = val;
			]]>
			</setter>
		</property>
	</implementation>
	<handlers>
		<handler event="input">
			 <![CDATA[
			menulist = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-menulist-field')
			picker = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-datepicker-field')
			var datum = menulist.label;
			
			//Wenn das Datum stimmt, dann wird der Hintergrund auf Weiss gesetzt und
			//das Datum im Datepicker gesetzt
			//Wenn das Datum falsch ist, dann wird der Hintergrund auf Rot gesetzt
			if(datum=='')
			{
				menulist.style.backgroundColor="#FFFFFF";
			}
			else
			{
				if(CheckDatum(datum))
				{	
					parts = datum.split('.');
	
					dat = new Date(parts[2],parts[1]-1,parts[0]);
					picker.view.setDate(dat);		
					picker.selection.setDate(dat);
					menulist.style.backgroundColor="#FFFFFF";
				}
				else
					menulist.style.backgroundColor="#F46B6B";
			}
			
			 ]]>
		</handler>
	</handlers>
  </binding>
  
  <!--
  Binding StyleBox
  in einem Template kann der Style nicht aus einem RDF befuellt werden.
  An diese Box kann ein Attribute mit Styles aus RDF uebergeben werden
  -->
  <binding id="StyleBox">
  	<content>
		<xul:box xbl:inherits="label, tooltiptext, tooltip, value, style=mystyle, flex">
			<children/>
		</xul:box>
	</content>
  </binding>
  
  <!--
  Binding fuer die Standortauswahl
  Zeigt ein DropDown fuer die Firmen an,
  wenn eine Firma ausgewaehlt wird, wird ein DropDown mit den zugehoerigen Standorten angezeigt
  -->
  <binding id="Standort">
  	<content>
		<xul:hbox flex="1">
			<!--<xul:label value="Firma" />-->
			<xul:menulist anonid="binding-standort-menulist-firma"
					  editable="true"
					  xbl:inherits="disabled"
			          datasources="rdf:null" flex="1"
			          ref="http://www.technikum-wien.at/firma/liste" 
			          oninput="document.getBindingParent(this).getFirmen();"
			          oncommand="document.getBindingParent(this).getStandorte();">
				<xul:template>
					<xul:menupopup>
						<xul:menuitem value="rdf:http://www.technikum-wien.at/firma/rdf#firma_id"
			        		      label="rdf:http://www.technikum-wien.at/firma/rdf#name"
						  		  uri="rdf:*"/>
					</xul:menupopup>
				</xul:template>
			</xul:menulist>	
			<!--<xul:label value="Standort" />-->
			<xul:menulist anonid="binding-standort-menulist-standort"
					  xbl:inherits="disabled"
			          datasources="rdf:null" flex="1"
			          ref="http://www.technikum-wien.at/standort/liste">
				<xul:template>
					<xul:menupopup>
						<xul:menuitem value="rdf:http://www.technikum-wien.at/standort/rdf#standort_id"
			        		      label="rdf:http://www.technikum-wien.at/standort/rdf#bezeichnung"
			        		      firma_id="rdf:http://www.technikum-wien.at/standort/rdf#firma_id"
						  		  uri="rdf:*"/>
					</xul:menupopup>
				</xul:template>
			</xul:menulist>	
		</xul:hbox>
	</content>
	<implementation>
		<field name="firmentyp" />
		<field name="autoload" />
		<property name="value" onget="return document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-standort').value" >
			<setter>
				<![CDATA[
				//Standort DropDown mit standorten dieser Firma laden
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				menuliststandort = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-standort');
				menulistfirma = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-firma');
				if(val!='')
				{
					
					var url = '<?php echo APP_ROOT; ?>rdf/standort.rdf.php?standort_id_all='+val+'&'+gettimestamp();
					
					var oldDatasources = menuliststandort.database.GetDataSources();
					while(oldDatasources.hasMoreElements())
					{
						menuliststandort.database.RemoveDataSource(oldDatasources.getNext());
					}
					//Refresh damit die entfernten DS auch wirklich entfernt werden
					menuliststandort.builder.rebuild();
				
					var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
					var datasource = rdfService.GetDataSourceBlocking(url);
					datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
					datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
					menuliststandort.database.AddDataSource(datasource);
					menuliststandort.builder.rebuild();
					
					//Standort markieren
					menuliststandort.selectedIndex=0;
					
	
					//Firmen Drop Down laden
									
					var children = menuliststandort.getElementsByAttribute('selected','true');
					if(children.length>0)
						v = children[0].getAttribute('firma_id');
					else
						return false;
					
					typ=this.getAttribute('firmentyp');
					
					var url = '<?php echo APP_ROOT; ?>rdf/firma.rdf.php?firma_id='+encodeURIComponent(v);
					if(typ!='')
						url = url+'&firmentyp_kurzbz='+encodeURIComponent(typ);
					
					url = url+'&optional=true&'+gettimestamp()
					
					var oldDatasources = menulistfirma.database.GetDataSources();
					while(oldDatasources.hasMoreElements())
					{
						menulistfirma.database.RemoveDataSource(oldDatasources.getNext());
					}
					//Refresh damit die entfernten DS auch wirklich entfernt werden
					menulistfirma.builder.rebuild();
				
					var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
					var datasource = rdfService.GetDataSourceBlocking(url);
					datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
					datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
					menulistfirma.database.AddDataSource(datasource);
					menulistfirma.builder.rebuild();
					//Firma markieren
					menulistfirma.selectedIndex=1;
				}
				else
				{
					menulistfirma.selectedIndex=-1;
					menuliststandort.selectedIndex=-1;
					menulistfirma.value='';
				}
				]]>
			</setter>
		</property>
		<property name="disabled" onget="return document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-firma').disabled" >
			<setter>
			<![CDATA[
				document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-firma').disabled = val;
				document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-standort').disabled = val;
				
				if(!val)
				{
					autoload = this.getAttribute('autoload');
					if(autoload)
					{
						//alert('autoload');
						this.getAllFirmen();
					}
				}				
			]]>
			</setter>
		</property>
		<method name="getFirmen">
			<body>
			<![CDATA[
				// Setzt das Drop Down fuer die Firmen
				
				//Set Source RDF
				menulist = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-firma');
								
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

				v = menulist.value;
				typ=this.getAttribute('firmentyp');
				
				if(v.length>2)
				{		
					var url = '<?php echo APP_ROOT; ?>rdf/firma.rdf.php?filter='+encodeURIComponent(v);
					if(typ!='')
						url = url+'&firmentyp_kurzbz='+encodeURIComponent(typ);
					
					url = url+'&optional=true&'+gettimestamp()
					//alert(url);
					var oldDatasources = menulist.database.GetDataSources();
					while(oldDatasources.hasMoreElements())
					{
						menulist.database.RemoveDataSource(oldDatasources.getNext());
					}
					//Refresh damit die entfernten DS auch wirklich entfernt werden
					menulist.builder.rebuild();
				
					var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
					var datasource = rdfService.GetDataSource(url);
					datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
					datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
					menulist.database.AddDataSource(datasource);
					menulist.builder.rebuild();
				}
				
			]]>
			</body>
		</method>
		<method name="getStandorte">
			<body>
			<![CDATA[
				//Setzt das DropDown fuer die Standorte
				
				//Set Source RDF
				menulist = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-standort');
				menulistfirma = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-firma');
								
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				
				var children = menulistfirma.getElementsByAttribute('selected','true');
				if(children.length>0)
					v = children[0].value;
				else
					return false;
				
				if(v!='')
				{
					var url = '<?php echo APP_ROOT; ?>rdf/standort.rdf.php?firma_id='+v+'&'+gettimestamp();
					
					var oldDatasources = menulist.database.GetDataSources();
					while(oldDatasources.hasMoreElements())
					{
						menulist.database.RemoveDataSource(oldDatasources.getNext());
					}
					//Refresh damit die entfernten DS auch wirklich entfernt werden
					menulist.builder.rebuild();
				
					var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
					var datasource = rdfService.GetDataSourceBlocking(url);
					datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
					datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
					menulist.database.AddDataSource(datasource);
					menulist.builder.rebuild();
					menulist.selectedIndex=0;
					menulist.disabled=false;
				}
				else
				{
					var oldDatasources = menulist.database.GetDataSources();
					while(oldDatasources.hasMoreElements())
					{
						menulist.database.RemoveDataSource(oldDatasources.getNext());
					}
					//Refresh damit die entfernten DS auch wirklich entfernt werden
					menulist.builder.rebuild();
					menulist.value='';
					menulist.selectdIndex=-1;
				}
			]]>
			</body>
		</method>
		<method name="getAllFirmen">
			<body>
			<![CDATA[
				//alert('Load');
				// Setzt das Drop Down fuer die Firmen
				
				//Set Source RDF
				menulist = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-firma');
								
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

				v = menulist.value;
				typ=this.getAttribute('firmentyp');
				
				var url = '<?php echo APP_ROOT; ?>rdf/firma.rdf.php?firmentyp_kurzbz='+encodeURIComponent(typ);
				
				url = url+'&optional=true&'+gettimestamp()
				//alert(url);
				var oldDatasources = menulist.database.GetDataSources();
				while(oldDatasources.hasMoreElements())
				{
					menulist.database.RemoveDataSource(oldDatasources.getNext());
				}
				//Refresh damit die entfernten DS auch wirklich entfernt werden
				menulist.builder.rebuild();
			
				var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
				var datasource = rdfService.GetDataSourceBlocking(url);
				datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
				datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
				menulist.database.AddDataSource(datasource);
				menulist.builder.rebuild();
				
			]]>
			</body>
		</method>
	</implementation>
	<handlers>
	</handlers>
  </binding>
  
  <!--
  Binding fuer die Firmenauswahl
  Zeigt ein DropDown fuer die Firmen an
  -->
  <binding id="Firma">
  	<content>
		<xul:hbox flex="1">
			<xul:menulist anonid="binding-firma-menulist-firma"
					  editable="true"
					  xbl:inherits="disabled"
			          datasources="rdf:null" flex="1"
			          ref="http://www.technikum-wien.at/firma/liste" 
			          oninput="document.getBindingParent(this).getFirmen();">
				<xul:template>
					<xul:menupopup>
						<xul:menuitem value="rdf:http://www.technikum-wien.at/firma/rdf#firma_id"
			        		      label="rdf:http://www.technikum-wien.at/firma/rdf#name"
						  		  uri="rdf:*"/>
					</xul:menupopup>
				</xul:template>
			</xul:menulist>	
		</xul:hbox>
	</content>
	<implementation>
		<field name="firmentyp" />
		<field name="autoload" />
		<property name="value">
			<getter>
				<![CDATA[
				menulistfirma = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-firma-menulist-firma');
				
				var children = menulistfirma.getElementsByAttribute('selected','true');
				if(children.length>0)
					return children[0].value;
				else
					return '';
				]]>
			</getter>
			<setter>
				<![CDATA[
				v = val;
				menulistfirma=document.getAnonymousElementByAttribute(this ,'anonid', 'binding-firma-menulist-firma');
				
				if(v!='')
				{
					typ=this.getAttribute('firmentyp');
					
					var url = '<?php echo APP_ROOT; ?>rdf/firma.rdf.php?firma_id='+encodeURIComponent(v);
					if(typ!='')
						url = url+'&firmentyp_kurzbz='+encodeURIComponent(typ);
					
					url = url+'&optional=true&'+gettimestamp()
					
					var oldDatasources = menulistfirma.database.GetDataSources();
					while(oldDatasources.hasMoreElements())
					{
						menulistfirma.database.RemoveDataSource(oldDatasources.getNext());
					}
					//Refresh damit die entfernten DS auch wirklich entfernt werden
					menulistfirma.builder.rebuild();
				
					var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
					var datasource = rdfService.GetDataSourceBlocking(url);
					datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
					datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
					menulistfirma.database.AddDataSource(datasource);
					menulistfirma.builder.rebuild();
					//Firma markieren
					menulistfirma.selectedIndex=1;
				}
				else
				{
					menulistfirma.selectedIndex=-1;
					menulistfirma.value='';
				}
				]]>
			</setter>
		</property>
		<property name="disabled" onget="return document.getAnonymousElementByAttribute(this ,'anonid', 'binding-standort-menulist-firma').disabled" >
			<setter>
			<![CDATA[
				document.getAnonymousElementByAttribute(this ,'anonid', 'binding-firma-menulist-firma').disabled = val;
				
				if(!val)
				{
					autoload = this.getAttribute('autoload');
					if(autoload)
					{
						this.getAllFirmen();
					}
				}				
			]]>
			</setter>
		</property>
		<method name="getFirmen">
			<body>
			<![CDATA[
				// Setzt das Drop Down fuer die Firmen
				
				//Set Source RDF
				menulist = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-firma-menulist-firma');
								
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

				v = menulist.value;
				typ=this.getAttribute('firmentyp');
				
				if(v.length>2)
				{		
					var url = '<?php echo APP_ROOT; ?>rdf/firma.rdf.php?filter='+encodeURIComponent(v);
					if(typ!='')
						url = url+'&firmentyp_kurzbz='+encodeURIComponent(typ);
					
					url = url+'&optional=true&'+gettimestamp()
					//alert(url);
					var oldDatasources = menulist.database.GetDataSources();
					while(oldDatasources.hasMoreElements())
					{
						menulist.database.RemoveDataSource(oldDatasources.getNext());
					}
					//Refresh damit die entfernten DS auch wirklich entfernt werden
					menulist.builder.rebuild();
				
					var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
					var datasource = rdfService.GetDataSource(url);
					datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
					datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
					menulist.database.AddDataSource(datasource);
					menulist.builder.rebuild();
				}
				
			]]>
			</body>
		</method>
		<method name="getAllFirmen">
			<body>
			<![CDATA[
				//alert('Load');
				// Setzt das Drop Down fuer die Firmen
				
				//Set Source RDF
				menulist = document.getAnonymousElementByAttribute(this ,'anonid', 'binding-firma-menulist-firma');
								
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

				v = menulist.value;
				typ=this.getAttribute('firmentyp');
				
				var url = '<?php echo APP_ROOT; ?>rdf/firma.rdf.php?firmentyp_kurzbz='+encodeURIComponent(typ);
				
				url = url+'&optional=true&'+gettimestamp()
				//alert(url);
				var oldDatasources = menulist.database.GetDataSources();
				while(oldDatasources.hasMoreElements())
				{
					menulist.database.RemoveDataSource(oldDatasources.getNext());
				}
				//Refresh damit die entfernten DS auch wirklich entfernt werden
				menulist.builder.rebuild();
			
				var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
				var datasource = rdfService.GetDataSourceBlocking(url);
				datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
				datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
				menulist.database.AddDataSource(datasource);
				menulist.builder.rebuild();
				
			]]>
			</body>
		</method>
	</implementation>
	<handlers>
	</handlers>
  </binding>

  <!--
  WYSIWYG Editor Binding
  -->
  <binding id="wysiwyg">
  	<content>
		<xul:vbox flex="1" style="margin: 5px;">
			<xul:toolbar>
			  <xul:toolbarbutton tooltiptext="Fett" image="<?php echo APP_ROOT; ?>skin/images/bold.png" stlye="font-weight: bold;" oncommand="document.getBindingParent(this).setBold()"/>
			  <xul:toolbarbutton tooltiptext="Kursiv" image="<?php echo APP_ROOT; ?>skin/images/italic.png" style="font-style: italic;" oncommand="document.getBindingParent(this).setItalic()"/>
			  <xul:toolbarbutton tooltiptext="Unterstrichen" image="<?php echo APP_ROOT; ?>skin/images/underline.png" style="text-decoration: underline" oncommand="document.getBindingParent(this).setUnderline()"/>
			  <xul:toolbarseparator />
			  <xul:toolbarbutton tooltiptext="Linksbündig" image="<?php echo APP_ROOT; ?>skin/images/justifyleft.png" oncommand="document.getBindingParent(this).setJustifyLeft()"/>
			  <xul:toolbarbutton tooltiptext="Zentriert" image="<?php echo APP_ROOT; ?>skin/images/justifycenter.png" oncommand="document.getBindingParent(this).setJustifyCenter()"/>
			  <xul:toolbarbutton tooltiptext="Rechtsbündig" image="<?php echo APP_ROOT; ?>skin/images/justifyright.png" oncommand="document.getBindingParent(this).setJustifyRight()"/>
			  <xul:toolbarseparator />
			  <xul:toolbarbutton label="Format" type="menu">							
			      <xul:menupopup>
					    <xul:menuitem label="Normal" oncommand="document.getBindingParent(this).setFormatblock('&lt;p&gt;')"/>
						<xul:menuitem label="Heading 1" oncommand="document.getBindingParent(this).setFormatblock('&lt;h1&gt;')"/>
						<xul:menuitem label="Heading 2" oncommand="document.getBindingParent(this).setFormatblock('&lt;h2&gt;')"/>
						<xul:menuitem label="Heading 3" oncommand="document.getBindingParent(this).setFormatblock('&lt;h3&gt;')"/>
						<xul:menuitem label="Heading 4" oncommand="document.getBindingParent(this).setFormatblock('&lt;h4&gt;')"/>
						<xul:menuitem label="Heading 5" oncommand="document.getBindingParent(this).setFormatblock('&lt;h5&gt;')"/>
						<xul:menuitem label="Heading 6" oncommand="document.getBindingParent(this).setFormatblock('&lt;h6&gt;')"/>
						<xul:menuitem label="Formatted" oncommand="document.getBindingParent(this).setFormatblock('&lt;pre&gt;')"/>
			      </xul:menupopup>
			  </xul:toolbarbutton>
			</xul:toolbar>
			<html:iframe anonid="wysiwyg-editor" editortype="html" src="about:blank" flex="1" type="content-primary" style="min-width: 100px; min-height: 100px; border: 1px solid gray;"/>
		</xul:vbox>
	</content>
	<implementation>
		<field name="initialisiert" />
		<field name="disabled_state" />
		<property name="value">
			<getter>
				<![CDATA[
				if(!this.initialisiert)
					this.init();
				try
				{
					netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
					editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
					return editor.contentWindow.document.body.innerHTML;
				}
				catch(e)
				{
					return false;
				}
				
				]]>
			</getter>
			<setter>
				<![CDATA[
					//editor initalisieren
					if(!this.initialisiert)
						this.init();
					netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
					editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');

					if(editor.contentWindow.document.body.innerHTML!='')
					{
						//Inhalt leeren
						editor.contentDocument.execCommand("selectall", false, null);
						editor.contentDocument.execCommand("delete", false, null);
					}
					//Value setzen
					if(val!='')
						editor.contentDocument.execCommand("inserthtml", false, val);
				]]>
			</setter>
		</property>
		<property name="disabled">
			<getter>
				return this.disabled_state;
			</getter>
			<setter>
			<![CDATA[
				if(val)
				{
					var editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
					editor.contentDocument.designMode = 'off';
					this.disabled_state=true;
					editor.style.backgroundColor="#EEEEEE";
				}
				else
				{
					var editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
					editor.contentDocument.designMode = 'on';	
					this.disabled_state=false;
					editor.style.backgroundColor="#FFFFFF";
				}
			]]>
			</setter>
		</property>
		<method name="setBold">
			<body>
			<![CDATA[
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
				editor.contentDocument.execCommand("bold", false, null);
			]]>
			</body>
		</method>
		<method name="setItalic">
			<body>
			<![CDATA[
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
				editor.contentDocument.execCommand("italic", false, null);
			]]>
			</body>
		</method>
		<method name="setFormatblock">
			<parameter name="type" />
			<body>
			<![CDATA[
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
				editor.contentDocument.execCommand("formatblock", false, type);
			]]>
			</body>
		</method>
		<method name="setUnderline">
			<body>
			<![CDATA[
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
				editor.contentDocument.execCommand("underline", false, null);
			]]>
			</body>
		</method>

		<method name="setJustifyLeft">
			<body>
			<![CDATA[
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
				editor.contentDocument.execCommand("justifyleft", false, null);
			]]>
			</body>
		</method>

		<method name="setJustifyCenter">
			<body>
			<![CDATA[
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
				editor.contentDocument.execCommand("justifycenter", false, null);
			]]>
			</body>
		</method>

		<method name="setJustifyRight">
			<body>
			<![CDATA[
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
				editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
				editor.contentDocument.execCommand("justifyright", false, null);
			]]>
			</body>
		</method>

		<method name="init">
			<body>
			<![CDATA[
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			editor = document.getAnonymousElementByAttribute(this ,'anonid', 'wysiwyg-editor');
			editor.contentDocument.designMode = 'on';
			editor.style.backgroundColor="#FFFFFF";
			]]>
			</body>
		</method>		
		<constructor>
			//Intialisierung des Editors im Konstruktor funktioniert nicht immer
			//deshalb wird er erst bei der ersten Verwendung initialisiert
		</constructor>
		<destructor>
		</destructor>
	</implementation>
  </binding>
</bindings>
