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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>
 */

require_once('../../config/vilesci.config.inc.php');

?>

var global_year;
var global_url;

function getProperties()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('tree-projektmenue');

	// Wenn auf die Ueberschrift geklickt wird, soll nix passieren
	if(tree.currentIndex==-1)
	{
		if(typeof(ProjektSelectKurzbz)!='undefined')
			projekt_kurzbz = ProjektSelectKurzbz;
		else
		{
			alert("Kein gültiges Projekt ausgewählt!");
			return;
		}
	}
	else
		projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
}

function showStudienjahr()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	if(isNaN(global_year))
	{
		var datumAktuell = new Date();
		var jahrAktuell = datumAktuell.getFullYear();
		global_year = jahrAktuell;
	}

	var tree=document.getElementById('tree-projektmenue');
	// Wenn auf die Ueberschrift geklickt wird, soll nix passieren
	if(tree.currentIndex==-1)
	{
		if(typeof(ProjektSelectKurzbz)!='undefined')
			projekt_kurzbz = ProjektSelectKurzbz;
		else
		{
			alert("Kein gültiges Projekt ausgewählt!");
			return;
		}
	}
	else
	{
		projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
	}
	if(projekt_kurzbz == '')
	{
		oe_kurzbz = getTreeCellText(tree, "treecol-projektmenue-oe", tree.currentIndex);
		var url = 'projekt/gantt.svg.php?oe='+oe_kurzbz+'&studienjahr='+(global_year-1)+'&ansicht=studienjahr';
		global_url=url;
	}
	else
	{
		var url = 'projekt/gantt.svg.php?projekt='+projekt_kurzbz+'&studienjahr='+(global_year-1)+'&ansicht=studienjahr';
		global_url = url;
	}

	document.getElementById('iframe-gant-projekt').contentWindow.location.href=url;
}

function showKalenderjahr()
{
	if(isNaN(global_year))
	{
		var datumAktuell = new Date();
		var jahrAktuell = datumAktuell.getFullYear();
		global_year = jahrAktuell;
	}

	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('tree-projektmenue');

	// Wenn auf die Ueberschrift geklickt wird, soll nix passieren
	if(tree.currentIndex==-1)
	{
		if(typeof(ProjektSelectKurzbz)!='undefined')
			projekt_kurzbz = ProjektSelectKurzbz;
		else
		{
			alert("Kein gültiges Projekt ausgewählt!");
			return;
		}
	}
	else
		projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
		
	if(projekt_kurzbz == '')
	{
		oe_kurzbz = getTreeCellText(tree, "treecol-projektmenue-oe", tree.currentIndex);
		var url = 'projekt/gantt.svg.php?oe='+oe_kurzbz+'&studienjahr='+global_year+'&ansicht=kalenderjahr';
		global_url = url;
	}
	else
	{
		var url = 'projekt/gantt.svg.php?projekt='+projekt_kurzbz+'&studienjahr='+global_year+'&ansicht=kalenderjahr';
		global_url = url;
	}

	global_url = url;
	document.getElementById('iframe-gant-projekt').contentWindow.location.href=url;
}

function showZeitraum(beginn, ende)
{
    if(beginn != '' && ende != '')
    {
    	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        var tree=document.getElementById('tree-projektmenue');

        // Wenn auf die Ueberschrift geklickt wird, soll nix passieren
        if(tree.currentIndex==-1)
        {
            alert("Kein gültiges Projekt ausgewählt!");
            return;
        }

        projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
        if(projekt_kurzbz == '')
        {
            oe_kurzbz = getTreeCellText(tree, "treecol-projektmenue-oe", tree.currentIndex);
            var url = 'projekt/gantt.svg.php?oe='+oe_kurzbz+'&beginn='+beginn+'&ende='+ende;
            global_url = url;
        }

        global_url = url;
        document.getElementById('iframe-gant-projekt').contentWindow.location.href=url;
    }
    else
        alert('kein gültiges Datum eingetragen');

}

function showYear()
{
	var datumAktuell = new Date();
	var jahrAktuell = datumAktuell.getFullYear();
	global_year = jahrAktuell;

	foo = document.getElementById('toolbarbutton-menuitem-gantt-kalenderjahr');
	checked=foo.getAttribute('checked');
	// kalenderjahr checked
	if(checked=='true')
	{
		showKalenderjahr();
	}
	else
	{
		showStudienjahr();
	}
}

function drawGantt()
{
	foo = document.getElementById('toolbarbutton-menuitem-gantt-kalenderjahr');
	checked=foo.getAttribute('checked');
	// kalenderjahr checked
	if(checked=='true')
	{
		showKalenderjahr();
	}
	else
	{
		showStudienjahr();
	}
}

function showYearMinus()
{

	global_year = global_year -1;
	foo = document.getElementById('toolbarbutton-menuitem-gantt-kalenderjahr');
	checked=foo.getAttribute('checked');
	// kalenderjahr checked
	if(checked=='true')
	{
		showKalenderjahr();
	}
	else
	{
		showStudienjahr();
	}
}

function showYearPlus()
{

	global_year = global_year +1;
	foo = document.getElementById('toolbarbutton-menuitem-gantt-kalenderjahr');
	checked=foo.getAttribute('checked');
	// kalenderjahr checked
	if(checked=='true')
	{
		showKalenderjahr();
	}
	else
	{
		showStudienjahr();
	}
}

function printGantt()
{
	foo = window.open(global_url);
	foo.onload = function ()
	{
		foo.print();
	}
}




