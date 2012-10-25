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
require_once('../../../include/mail.class.php');

$uid = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);
$datum_obj = new datum();

if(!isset($_REQUEST['coodle_id']))
	die($p->t('global/fehlerBeiDerParameteruebergabe'));
	
$coodle_id = $_REQUEST['coodle_id'];

$db = new basis_db();
$coodle = new coodle();
if(!$coodle->load($coodle_id))
{
	die($coodle->errormsg);
}
$event_titel = $coodle->titel;

if($coodle->coodle_status_kurzbz == 'storniert' || $coodle->coodle_status_kurzbz == 'abgeschlossen')
{
	die('Diese Umfrage ist bereits beendet');
}


if(isset($_POST['action']) && $_POST['action']=='start')
{

	echo '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet"  href="../../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	</head>
	<body>
	<h1>'.$p->t('coodle/coodle').' - '.$p->t('coodle/termine').'</h1>';

	// Start der Umfrage
	$coodle_termine = new coodle();
	$coodle_termine->getTermine($coodle_id);
	if(count($coodle_termine->result)>0)
	{
		$coodle_ressource = new coodle();
		$coodle_ressource->getRessourcen($coodle_id);
		if(count($coodle_ressource->result)>0)
		{
			// Status aendern
			$coodle->coodle_status_kurzbz='laufend';
			if(!$coodle->save(false))
				die('Fehler beim Setzen des Status:'. $coodle->errormsg);

			foreach($coodle_ressource->result as $row)
			{
				if($row->uid!='')
				{
					$benutzer = new benutzer();
					if(!$benutzer->load($row->uid))
					{
						echo "Fehler beim Laden des Benutzers ".$db->convert_html_chars($row->uid);
						continue;
					}
					
					if($benutzer->geschlecht=='w')
						$anrede = "Sehr geehrte Frau ";
					else
						$anrede = "Sehr geehrter Herr ";
						
					$anrede.= $benutzer->titelpre.' '.$benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost;
					
					// Interner Teilnehmer
					$email = $row->uid.'@'.DOMAIN;
					$link = APP_ROOT.'cis/public/coodle.php?coodle_id='.urlencode($coodle_id).'&uid='.urlencode($row->uid);
				}
				elseif($row->email!='')
				{
					// Externe Teilnehmer
					$email = $row->email;
					$anrede='Sehr geehrte(r) Herr/Frau '.$row->name; 
					$link=APP_ROOT.'cis/public/coodle.php?coodle_id='.urlencode($coodle_id).'&zugangscode='.urlencode($row->zugangscode);
				}
				else
				{
					// Raueme bekommen kein Mail
					continue;
				}
				$anrede = trim($anrede);
				$sign = "Mit freundlichen Grüßen\n\n";
				$sign .= "Fachhochschule Technikum Wien\n";
				$sign .= "Höchstädtplatz 5\n";
				$sign .= "1200 Wien\n";
				
				
				$html=$anrede.'!<br><br>
					Sie wurden zu einer Terminumfrage zum Thema "'.$db->convert_html_chars($coodle->titel).'" eingeladen.
					<br>
					Bitte folgen Sie dem Link um Ihre Terminwünsche bekannt zu geben:
					<a href="'.$link.'">Link zur Terminumfrage</a>
					<br><br>'.nl2br($sign);
				
				$text=$anrede."!\n\nSie wurden zu einer Terminumfrage zum Thema \"".$db->convert_html_chars($coodle->titel)."\" eingeladen.\n
					Bitte folgen Sie dem Link um Ihre Terminwünsche bekannt zu geben:\n
					$link\n\n$sign";
				
				$mail = new mail($email, 'no-reply@'.DOMAIN,'Termineinladung - '.$coodle->titel, $text);
				$mail->setHTMLContent($html);
				if($mail->send())
				{
					echo $p->t('coodle/mailVersandtAn',array($email))."<br>";
				} 
			}

			echo '<br><b>'.$p->t('coodle/erfolgreichGestartet').'</b>';
			echo '<br><br><a href="uebersicht.php">'.$p->t('coodle/zurueckZurUebersicht').'</a>';
		}
		else
		{
			die($p->t('coodle/keineRessourcenVorhanden'));
		}
	}
	else
	{
		die($p->t('coodle/keineTermineVorhanden'));
	}
		
	echo '</body></html>';
	exit();
}

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
	<script type="text/javascript" src="../../../include/js/jquery.contextmenu.r2.js"></script>
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
		margin-top: 30px;
		border: 1px solid #ccc;
		background: #eee;
		text-align: left;
		}
		
	#ressourcen h4 {
		font-size: 16px;
		margin-top: 0;
		padding-top: 1em;
		}
		
	.ressourcen {
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
	
	#fertig 
	{
		width: 150px;
		padding: 0 10px;
		margin-top: 30px;
		border: 1px solid #ccc;
		background: #eee;
		text-align: left;
	}
		
	#fertig h4 
	{
		font-size: 16px;
		margin-top: 0;
		padding-top: 1em;
	}
		
	#fertig p 
	{
		margin: 1.5em 0;
		font-size: 11px;
		color: #666;
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
				title: $.trim($(this).text()), // use the elements text as the event title
				termin: true
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
			defaultEventMinutes: '.$coodle->dauer.',
			editable: true,
			disableResizing: true,
			droppable: true, // this allows things to be dropped onto the calendar !!!
			drop: function(date, allDay) 
			{ 
				// Event wird auf Kalender gezogen
			
				// gedropptes Event holen
				var originalEventObject = $(this).data("eventObject");
				
				// we need to copy it, so that multiple events dont have a reference to the same object
				var copiedEventObject = $.extend({}, originalEventObject);
				
				// assign it the date that was reported
				copiedEventObject.start = date;
				copiedEventObject.allDay = allDay;

				// Datum konvertieren
				datum = $.fullCalendar.formatDate(date, "yyyy-MM-dd");
				if(allDay)
				{
					uhrzeit = "08:00:00";
					copiedEventObject.start = datum+"T"+uhrzeit;
					copiedEventObject.allDay=false;
				}
				else
					uhrzeit = $.fullCalendar.formatDate(date, "HH:mm:ss");

				//alert("datum:"+datum+" uhrzeit:"+uhrzeit);

				// Termin Speichern
				$.ajax({
					type:"POST",
					url:"coodle_worker.php", 
					data:{ 
							"work": "addTermin",
							"datum": datum,
							"uhrzeit": uhrzeit,
							"coodle_id": "'.$coodle_id.'"
						 },
					success: function(data) 
						{ 
							if(isNaN(data))
								alert("ERROR:"+data)
							else
							{
								copiedEventObject.id=data;
								// render the event on the calendar
								// the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
								$("#calendar").fullCalendar("renderEvent", copiedEventObject, true);
							}
						},
					error: function() { alert("error"); }
				});				
			},
			eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view)
			{
				/*
				alert(
		            event.id + " was moved " +
					$.fullCalendar.formatDate(event.start,"yyyy-MM-dd HH:mm:ss")
		        );*/
				
				datum = $.fullCalendar.formatDate(event.start,"yyyy-MM-dd")
				uhrzeit = $.fullCalendar.formatDate(event.start,"HH:mm:ss")
				if(allDay)
				{
					uhrzeit = "08:00:00";
					event.start = datum+"T"+uhrzeit;
					event.allDay=false;
					$("#calendar").fullCalendar("renderEvent", event, true);
				}
				// Verschiebung Speichern
				$.ajax({
					type:"POST",
					url:"coodle_worker.php", 
					data:{ 
							"work": "moveTermin",
							"datum": datum,
							"uhrzeit": uhrzeit,
							"coodle_termin_id": event.id,
							"coodle_id": "'.$coodle_id.'"
						 },
					success: function(data) 
						{ 
							if(data!="true")
							{
								alert("ERROR:"+data)
								revertFunc();
							}
							else
							{
								// Verschiebung OK
							}
						},
					error: function() { alert("error"); }
				});				
			},
			eventRender: function (event, element) 
			{ 
				// Conext Menue nur an Umfragetermine nicht an FreeBusy Eintraege haengen
				if(event.termin)
				{
					element.contextMenu("myContextMenu",
					{
						bindings: 
						{
							"delete": function(t) 
								{
									// Termin loeschen
									$.ajax({
										type:"POST",
										url:"coodle_worker.php", 
										data:{ 
												"work": "removeTermin",
												"coodle_termin_id": event.id,
												"coodle_id": "'.$coodle_id.'"
											 },
										success: function(data) 
											{ 
												if(data!="true")
												{
													alert("ERROR:"+data)
													revertFunc();
												}
												else
												{
													// Loeschen aus DB OK
													//Event aus Kalender entfernen
													$("#calendar").fullCalendar("removeEvents", event.id);
												}
											},
										error: function() { alert("error"); }
									});				
								},
						}
					});
				}		
			}
		});
	});

	
</script>
</head>
<body>
	<h1>'.$p->t('coodle/coodle').' - '.$p->t('coodle/termine').'</h1>';

// Contextmenue
echo '
<div id="myContextMenu" class="contextMenu">
	<ul>
		<li id="delete"><img src="../../../skin/images/delete_round.png" />'.$p->t('global/entfernen').'</li>
	</ul>
</div>';
//echo '<h2>'.$coodle->titel.'</h2>';
//echo $coodle->beschreibung;

echo '<a href="stammdaten.php?coodle_id='.$coodle_id.'">'.$p->t('coodle/ZurueckzumBearbeiten').'</a>';
echo '
<div id="wrap">

<div id="wrap2">
	<div id="external-events">
	<h4>'.$p->t('coodle/dragEvent').'</h4>
	<div class="external-event">'.$db->convert_html_chars($coodle->titel).'</div>
	<p>
	'.$p->t('coodle/terminZiehenBeschreibung').'
	</p>
	</div>
	<div id="ressourcen">
	<h4>'.$p->t('coodle/ressourcen').'</h4>
	<div id="ressourcecontainer">
	</div>
	<div id="ressourcenInput">
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
						"bezeichnung": bezeichnung,
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

echo '
	$(document).ready(function() 
	{';

// Bereits zugeteilte Ressourcen laden

if(!$coodle->getRessourcen($coodle_id))
	die('Fehler:'.$coodle->errormsg);

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

// Bereits eingetragene Terminvorschlaege laden
$coodletermin = new coodle();
if(!$coodletermin->getTermine($coodle_id))
	die('Fehler:'.$coodletermin->errormsg);
foreach($coodletermin->result as $row)
{
	echo '
		var eventObject = 
		{
			id: "'.$db->convert_html_chars($row->coodle_termin_id).'",
			title: "'.$db->convert_html_chars($event_titel).'",
			start: "'.$db->convert_html_chars($row->datum).'T'.$db->convert_html_chars($row->uhrzeit).'",
			allDay: false,
			termin: true
		};
		$("#calendar").fullCalendar("renderEvent", eventObject, true);
		';
}
echo '
	});';

echo '
	function showExterne()
	{
		$("#externePersonen").show();
		$("#ressourcenInput").hide();
	}

	function showRessourcen()
	{
		$("#externePersonen").hide();
		$("#ressourcenInput").show();
	}
		
	function AddExternal()
	{
		name=$("#externePersonName").val();
		email=$("#externePersonEmail").val();
		addRessource(email, "Extern", name);
		$("#externePersonName").val("");
		$("#externePersonEmail").val("");
	}
	</script>
	<p>
	'.$p->t('coodle/ressourcenBeschreibung').'
	<br><br><a href="#" onclick="showExterne(); return false;">'.$p->t('coodle/externePersonhinzu').'</a>
	</div> <!-- RessourcenInput -->
	<div id="externePersonen" style="display: none">
	<p>
	'.$p->t('coodle/name').':<br> <input type="text" id="externePersonName" size="15"><br>
	'.$p->t('coodle/email').':<br> <input type="text" id="externePersonEmail" size="15"><br><br>
	'.$p->t('coodle/externeBeschreibung').'
	</p>
	<input type="button" value="'.$p->t('coodle/externenHinzufuegen').'" onclick="AddExternal()">
	<br><br><a href="#" onclick="showRessourcen(); return false;">'.$p->t('coodle/Ressourcenhinzu').'</a>
	</div>
	</p>

	</div>
	<div id="fertig">
		<h4>'.$p->t('coodle/umfrageStarten').'</h4>
		<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
		<input type="hidden" name="action" value="start" />
		<input type="hidden" name="coodle_id" value="'.$db->convert_html_chars($coodle_id).'" />
		<input type="submit" value="'.$p->t('coodle/umfrageStarten').'" />
		</form>
		<p>
			'.$p->t('coodle/startBeschreibung').'
		</p>
	</div>
</div>
<div id="calendar"></div>

<div style="clear:both"></div>
</div>';

echo '</body>
</html>';
?>
