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
								onselect="parentNode.parentNode.parentNode.parentNode.value=this.selection.currentDay+'.'+(parseInt(this.selection.currentMonth)+1)+'.'+this.selection.currentYear"/>
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
</bindings>
