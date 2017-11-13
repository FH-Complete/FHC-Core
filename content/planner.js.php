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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
?>
// ----------------------------------------------------------
// ------- CLASS Progressmeter ------------------------------
function Progressmeter(progress_id)
{
	var id=progress_id;
	var runningprogress=0;
    this.StopPM=StopPM;
    this.StartPM=StartPM;
    
    function StartPM()
    {
        // Progressmeter starten.
		document.getElementById(id).setAttribute('mode','undetermined');
		runningprogress++;
    }

    function StopPM()
    {
    	runningprogress--;
    	if(runningprogress<0)
    		runningprogress=0;
    	
        // Progressmeter stoppen wenn alle fertig sind
        if(runningprogress==0)
			document.getElementById(id).setAttribute('mode','determined');
    }
}
// ------ EndOf CLASS Progressmeter ------------------------------

var globalProgressmeter=new Progressmeter('statusbar-progressmeter');
//globalProgressmeter.StartPM();

function closeWindow()
{	
	window.close();
}

function onLoad()
{
	try
	{
  		//Funktion ueberschreiben damit sie nicht nochmal aufgerufen wird
  		//wenn zb ein IFrame geladen wird
  		onLoad=function() {return false};
  		ressourceTreeLoad();
  		 
  		//Notizen des Users laden
		notiz = document.getElementById('box-notizen');
		notiz.LoadNotizTree('','','','','','','', getUsername(),'');
	}
	catch(e)
	{
		debug('catched'+e);
		onLoad=function() {return false};
	}
}

function loadRightFrame()
{

}

function loadURL(event)
{
        var contentFrame = document.getElementById('contentFrame');
        var url = event.target.getAttribute('value');

        if (url) contentFrame.setAttribute('src', url);
}
