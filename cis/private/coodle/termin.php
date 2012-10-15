<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/coodle.class.php');
require_once('../../../include/datum.class.php');
$uid = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);
$datum_obj = new datum();

echo '<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet"  href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link rel="stylesheet" href="../../../skin/jquery.css" type="text/css"/>
	<script type="text/javascript" src="../../../include/js/jquery.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.tablehover.js"></script>
	
	<link rel="stylesheet" type="text/css" href="../../../include/js/fullcalendar/fullcalendar.css" />
	<link rel="stylesheet" type="text/css" href="../../../include/js/fullcalendar/fullcalendar.print.css" media="print" />
	<script type="text/javascript" src="../../../include/js/jquery-ui.js"></script>
	<script type="text/javascript" src="../../../include/js/fullcalendar/fullcalendar.min.js"></script>
	<title>'.$p->t('coodle/coodle').' - '.$p->t('coodle/termine').'</title>
	
<style type="text/css">
		
	#wrap {
		width: 1100px;
		margin: 0 auto;
		}
		
	#wrap2 {
		float: left;
	}
	#external-events {
		width: 150px;
		padding: 0 10px;
		margin-top: 40px;
		border: 1px solid #ccc;
		background: #eee;
		text-align: left;
		}
		
	#external-events h4 {
		font-size: 16px;
		margin-top: 0;
		padding-top: 1em;
		}
		
	.external-event { /* try to mimick the look of a real event */
		margin: 10px 0;
		padding: 2px 4px;
		background: #3366CC;
		color: #fff;
		font-size: .85em;
		cursor: pointer;
		}
		
	#external-events p {
		margin: 1.5em 0;
		font-size: 11px;
		color: #666;
		}
		
	#external-events p input {
		margin: 0;
		vertical-align: middle;
		}

	#calendar {
		float: right;
		width: 900px;
		}
		
	#ressourcen {
		width: 150px;
		padding: 0 10px;
		margin-top: 50px;
		border: 1px solid #ccc;
		background: #eee;
		text-align: left;
		}
		
	#ressourcen h4 {
		font-size: 16px;
		margin-top: 0;
		padding-top: 1em;
		}
		
	.ressourcen { /* try to mimick the look of a real event */
		margin: 10px 0;
		padding: 2px 4px;
		background: #3366CC;
		color: #fff;
		font-size: .85em;
		cursor: pointer;
		}
		
	#ressourcen p {
		margin: 1.5em 0;
		font-size: 11px;
		color: #666;
		}
		
	#ressourcen p input {
		margin: 0;
		vertical-align: middle;
		}

	#input_ressource{
		margin-top: 10px;
	}

	.ressourceItem
	{
		font-size: x-small;
	}
</style>
<script type="text/javascript">

	$(document).ready(function() {
	
	
		/* initialize the external events
		-----------------------------------------------------------------*/
	
		$("#external-events div.external-event").each(function() {
		
			// create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
			// it doesn"t need to have a start or end
			var eventObject = {
				title: $.trim($(this).text()) // use the element"s text as the event title
			};
			
			// store the Event Object in the DOM element so we can get to it later
			$(this).data("eventObject", eventObject);
			
			// make the event draggable using jQuery UI
			$(this).draggable({
				zIndex: 999,
				revert: true,      // will cause the event to go back to its
				revertDuration: 0  //  original position after the drag
			});
			
		});
	
	
		/* initialize the calendar
		-----------------------------------------------------------------*/
		
		$("#calendar").fullCalendar({
			header: {
				left: "prev,next today",
				center: "title",
				right: "month,agendaWeek,agendaDay"
			},
			defaultView: "agendaWeek",
			timeFormat: {
						    // for agendaWeek and agendaDay
						    agenda: "H:mm{ - H:mm}", // 5:00 - 6:30
						
						    // for all other views
						    "": "H:mm"
						},
			allDaySlot: true, // Ganztaegig Row anzeigen
			allDayText: "",	//Text in ganztaegig Spalte	
			axisFormat: "H:mm",
			monthNames: ["Jänner", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
			monthNamesShort: ["Jän", "Feb", "Mär", "Apr", "Mai", "Jun","Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
			dayNames: ["Sonntag", "Montag", "Dienstag", "Mittwoch","Donnerstag", "Freitag", "Samstag"],
			dayNamesShort: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
			columnFormat:{
						    month: "ddd",    // Mon
						    week: "ddd d.M.", // Mon 9/7
						    day: "dddd d.M."  // Monday 9/7
						},
			buttonText: {
						    prev:     "&nbsp;&#9668;&nbsp;",  // left triangle
						    next:     "&nbsp;&#9658;&nbsp;",  // right triangle
						    prevYear: "&nbsp;&lt;&lt;&nbsp;", // <<
						    nextYear: "&nbsp;&gt;&gt;&nbsp;", // >>
						    today:    "heute",
						    month:    "Monat",
						    week:     "Woche",
						    day:      "Tag"
						},
			titleFormat: {
						    month: "MMMM yyyy",                             // September 2009
						    week: "MMM d[ yyyy]{ \'&#8212;\'[ MMM] d yyyy}", // Sep 7 - 13 2009
						    day: "dddd, MMM d, yyyy"                  // Tuesday, Sep 8, 2009
						},
			editable: true,
			droppable: true, // this allows things to be dropped onto the calendar !!!
			drop: function(date, allDay) { // this function is called when something is dropped
			
				// retrieve the dropped element"s stored Event Object
				var originalEventObject = $(this).data("eventObject");
				
				// we need to copy it, so that multiple events don"t have a reference to the same object
				var copiedEventObject = $.extend({}, originalEventObject);
				
				// assign it the date that was reported
				copiedEventObject.start = date;
				copiedEventObject.allDay = allDay;
				
				// render the event on the calendar
				// the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
				$("#calendar").fullCalendar("renderEvent", copiedEventObject, true);
				
				// is the "remove after drop" checkbox checked?
				//if ($("#drop-remove").is(":checked")) {
					// if so, remove the element from the "Draggable Events" list
					//$(this).remove();
				//}
				
			}
		});
		
		
	});

	
</script>
</head>
<body>
	<h1>'.$p->t('coodle/coodle').' - '.$p->t('coodle/termine').'</h1>';


if(!isset($_GET['coodle_id']))
	die($p->t('global/fehlerBeiDerParameteruebergabe'));
	
$coodle_id = $_GET['coodle_id'];

$db = new basis_db();
$coodle = new coodle();
if(!$coodle->load($coodle_id))
{
	die($p->t($coodle->errormsg));
}

//echo '<h2>'.$coodle->titel.'</h2>';
//echo $coodle->beschreibung;

echo '
<div id="wrap">

<div id="wrap2">
	<div id="external-events">
	<h4>'.$p->t('coodle/dragEvent').'</h4>
	<div class="external-event">'.$db->convert_html_chars($coodle->titel).'</div>
	<p>
	'.$p->t('coodle/terminziehenBeschreibung').'
	</p>
	</div>
	<div id="ressourcen">
	<h4>'.$p->t('coodle/ressourcen').'</h4>
	<div id="ressourcecontainer"></div>
	<input id="input_ressource" type="text" size="10" />
	<script>
	function formatItem(row) 
	{
		if(row[1]="Ort")
		    return "O <i>" + row[0] + "<\/i> - "+ row[2] +" " + row[1];
		else
		    return " <i>" + row[2] + "<\/i> - "+ row[0] +" " + row[1];
	}
		
	function selectItem(li) 
	{
		return false;
	}

	$(document).ready(function() {
				  $("#input_ressource").autocomplete("coodle_autocomplete.php", {
					minChars:2,
					matchSubset:1,matchContains:1,
					width:300,
					cacheLength:0,
					onItemSelect:selectItem,
					formatItem:formatItem,
					extraParams:{"work":"ressource"}
				  });
				  
				  $("#input_ressource").result(function(event, data, formatted) 
				  {
				  	var uid = data[0];
				  	var typ = data[1];
				  	var bezeichnung = data[2];
				  	addRessource(uid, typ, bezeichnung);
				  	this.value="";
				  });
	 });
	
	/*
 	 * Fuegt eine Ressource hinzu
	 */  
	function addRessource(id, typ, bezeichnung)
	{
		var code = \'<span class="ressourceItem"> \
				<a href="#delete" onclick="removeRessource(this, \\\'\'+id+\'\\\',\\\'\'+typ+\'\\\'); return false;"> \
					<img src="../../../skin/images/delete_round.png" height="13px" title="'.$p->t('coodle/ressourceEntfernen').'"/> \
				</a> \
				\'+bezeichnung+\' \
			<br /></span>\';
		$("#ressourcecontainer").append(code);
	}

	/*
	 * Loescht eine Ressource
	 */
	function removeRessource(item, id, typ)
	{
		$(item).parent().remove();
	}
	</script>
	<p>
	'.$p->t('coodle/ressourcenBeschreibung').'
	</p>
	</div>
</div>
<div id="calendar"></div>

<div style="clear:both"></div>
</div>';

echo '</body>
</html>';
?>
