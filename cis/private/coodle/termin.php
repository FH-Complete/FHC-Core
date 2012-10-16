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
require_once('../../../include/benutzer.class.php');
require_once('../../../include/ort.class.php');

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

	$(document).ready(function() 
	{
		// Coodle Termin initialisieren	
		$("#external-events div.external-event").each(function() 
		{
			var eventObject = 
			{
				title: $.trim($(this).text()) // use the elements text as the event title
			};
			
			// store the Event Object in the DOM element so we can get to it later
			$(this).data("eventObject", eventObject);
			
			// make the event draggable using jQuery UI
			$(this).draggable(
			{
				zIndex: 999,
				revert: true,      // will cause the event to go back to its
				revertDuration: 0  //  original position after the drag
			});
		});
	
	
		// Kalender Initialisieren
		$("#calendar").fullCalendar(
		{
			header:	{
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
			drop: function(date, allDay) 
			{ 
				// this function is called when something is dropped
			
				// retrieve the dropped elements stored Event Object
				var originalEventObject = $(this).data("eventObject");
				
				// we need to copy it, so that multiple events dont have a reference to the same object
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
				
			},
			eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view)
			{
				alert(
		            event.title + " was moved " +
		            dayDelta + " days and " +
		            minuteDelta + " minutes."
		        );
		
		        if (allDay) {
		            alert("Event is now all-day");
		        }else{
		            alert("Event has a time-of-day");
		        }
		
		        if (!confirm("Are you sure about this change?")) {
		            revertFunc();
		        }
			},
			eventResize: function(event,dayDelta,minuteDelta,revertFunc, ui, view) 
			{

		        alert(
		            "The end date of " + event.title + "has been moved " +
		            dayDelta + " days and " +
		            minuteDelta + " minutes."
		        );
		
		        if (!confirm("is this okay?")) {
		            revertFunc();
		        }
		
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
	<div id="ressourcecontainer">
	</div>
	<input id="input_ressource" type="text" size="10" />
	<script>
	
	// Formatieren des Eintrages im Autocomplete Feld
	function formatItem(row) 
	{
		if(row[1]=="Ort")
		    return "<i>" + row[0] + "<\/i> - "+ row[2] +" " + row[1];
		else
		    return "<i>" + row[2] + "<\/i> - "+ row[0] +" " + row[1];
	}
		
	function selectItem(li) 
	{
		return false;
	}

	$(document).ready(function() 
	{
		// Autocomplete Feld fuer Ressourcen initialisieren	
		$("#input_ressource").autocomplete("coodle_autocomplete.php", {
			minChars:2,
			matchSubset:1,matchContains:1,
			width:300,
			cacheLength:0,
			onItemSelect:selectItem,
			formatItem:formatItem,
			extraParams:{"work":"ressource"}
		});
		  
		// Auswahl eines Eintrages im Autocomplete Feld
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
		// Ressource Speichern
		$.ajax({
				type:"POST",
				url:"coodle_worker.php", 
				data:{ 
						"work": "addressource",
						"id": id, 
						"typ": typ,
						"coodle_id": "'.$coodle_id.'"
					 },
				success: function(data) 
					{ 
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							// Speichern der Ressource OK
							addRessourceToContent(id, typ, bezeichnung);
						}

					},
				error: function() { alert("error"); }
			});
	}
	// Zeigt eine Ressoure mit deren Events an
	function addRessourceToContent(id, typ, bezeichnung)
	{
		// Anzeige der Ressource mit Loeschen Button
		var code = \'<span class="ressourceItem"> \
				<a href="#delete" onclick="removeRessource(this, \\\'\'+id+\'\\\',\\\'\'+typ+\'\\\'); return false;"> \
					<img src="../../../skin/images/delete_round.png" height="13px" title="'.$p->t('coodle/ressourceEntfernen').'"/> \
				</a> \
				\'+bezeichnung+\' \
			<br /></span>\';
		$("#ressourcecontainer").append(code);

		// Events der Ressource hinzufuegen
		$("#calendar").fullCalendar("addEventSource",
			{
				url:"coodle_events.php?code="+encodeURIComponent(id+typ),
				type: "POST",
				data:   {
							typ: typ,
							id: id
						},
				error: function() {
					alert("Error fetching data for "+typ+" "+id);
				},
				color:"gray"
				//textColor:"black"
			});

	}
	function removeRessource(item, id, typ)
	{
		// Ressource entfernen
		$.ajax({
				type:"POST",
				url:"coodle_worker.php", 
				data:{ 
						"work": "removeressource",
						"id": id, 
						"typ": typ,
						"coodle_id": "'.$coodle_id.'"
					 },
				success: function(data) 
					{ 
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							// Entfernen der Ressource OK
							removeRessourceFromContent(item, id, typ);
						}

					},
				error: function() { alert("error"); }
			});
	
	}

	/*
	 * Loescht eine Ressource
	 */
	function removeRessourceFromContent(item, id, typ)
	{
		
		$("#calendar").fullCalendar("removeEventSource",
			{
				url:"coodle_events.php?code="+encodeURIComponent(id+typ),
				type: "POST",
				data:   {
							typ: typ,
							id: id
						},
				error: function() {
					alert("Error fetching data for "+typ+" "+id);
				}
			});
		$(item).parent().remove();
	}';

// Bereits zugeteilte Ressourcen laden

if(!$coodle->getRessourcen($coodle_id))
	die('Fehler:'.$coodle->errormsg);
echo '
	$(document).ready(function() 
	{';
foreach($coodle->result as $row)
{
	echo "\n\t";
	$typ='';
	$id='';
	$bezeichnung='';

	if($row->uid!='')
	{
		$typ='Person';
		$id=$row->uid;
		$benutzer = new benutzer();
		$benutzer->load($row->uid);
		$bezeichnung = $benutzer->nachname.' '.$benutzer->vorname;
	}
	elseif($row->ort_kurzbz!='')
	{
		$typ='Ort';
		$id=$row->ort_kurzbz;
		$ort = new ort();
		$ort->load($row->ort_kurzbz);
		$bezeichnung = $ort->bezeichnung;
	}
	elseif($row->email!='')
	{
		$typ = 'Extern';
		$id = $row->email;
		$bezeichnung = $row->name;
	}
	echo 'addRessourceToContent("'.$db->convert_html_chars($id).'", "'.$db->convert_html_chars($typ).'", "'.$db->convert_html_chars($bezeichnung).'");';
}
echo '
	});';

echo '
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
