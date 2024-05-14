<?php
/* Copyright (C) 2013 fhcomplete.org
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
/**
 * Initialisierung des Addons
 */
?>
addon.push( 
{
	init: function() 
	{
		// Diese Funktion wird nach dem Laden des FAS aufgerufen

		/*

		// Hinzufuegen eines zusaetzlichen Tabs bei Mitarbeitern mit einem Label darin
		var tabitem = document.createElement("tab");
		tabitem.setAttribute("id","addon-template-tab");
		tabitem.setAttribute("label","Template");

		var mitarbeitertabs = document.getElementById("mitarbeiter-tabs");
		mitarbeitertabs.appendChild(tabitem);

		var tabpanelitem = document.createElement("vbox");
		tabpanelitem.setAttribute("id","addon-template-tabpannel-vbox");
		var label = document.createElement("label");
		label.setAttribute("value","Template");
		tabpanelitem.appendChild(label);

		var mitarbeitertabpanels=document.getElementById("mitarbeiter-tabpanels-main");
		mitarbeitertabpanels.appendChild(tabpanelitem);

		// zusaetzliche Funktion beim klicken des Suchen Button bei Mitarbeitern hinzufuegen
		searchbutton = document.getElementById("mitarbeiter-toolbar-button-search");
		searchbutton.addEventListener("command",AddonTemplateMitarbeiterSearch, true);

		// Menuepunkt hinzufuegen
		statistikmenue = document.getElementById("menu-statistic-popup");

		var menuentry = document.createElement("menuitem");
		menuentry.setAttribute("id","addons-template-mymenuentry");
		menuentry.setAttribute("label","Addon Template Menu Entry");
		menuentry.addEventListener("command",AddonTemplateMenuEntry, true);
	
		statistikmenue.appendChild(menuentry);
		
		*/
	},
	selectMitarbeiter: function(person_id, mitarbeiter_uid)
	{
	},
	selectStudent: function(person_id, prestudent_id, student_uid)
	{
	},
	selectVerband: function(item)
	{
	},
	selectInstitut: function(institut)
	{
	},
	selectLektor: function(lektor)
	{
	}
});

function AddonTemplateMitarbeiterSearch()
{
	alert("AddonTemplateSearchButtonClicked");
}

function AddonTemplateMenuEntry()
{
	alert("AddonTemplateMenuEntry clicked");
}
