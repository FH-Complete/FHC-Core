<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


// Deadline for Application given as Time-Interval after Semesterstart.
$config['interval_blocking_application'] = 'P1M';

// Application submission period given by start- and enddate.
$config['submit_application_start'] = '01.02.2021';
$config['submit_application_end'] = '22.02.2021';

// Lehrveranstaltungen with these grades will be blocked for application
$config['grades_blocking_application'] = array(
	5,  // nicht genügend
	6,  // angerechnet
	9,  // noch nicht eingetragen
	13, // nicht erfolgreich absolviert
	14, // nicht bestanden,
	15, // nicht teilgenommen
	18  // unentschuldigt
);