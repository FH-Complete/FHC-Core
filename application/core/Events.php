<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

Events::on('loadRenderers', function ($renderers) {
	$fhc_core_renderers =& $renderers();
	$fhc_core_renderers["lehreinheit"] = array(
		'calendarEvent' => APP_ROOT.'public/js/components/Cis/Renderer/Lehreinheit/calendarEvent.js',
		'modalTitle' => APP_ROOT.'public/js/components/Cis/Renderer/Lehreinheit/modalTitle.js',
		'modalContent' => APP_ROOT.'public/js/components/Cis/Renderer/Lehreinheit/modalContent.js',
		'calendarEventStyles' => APP_ROOT.'public/css/Cis4/CoreCalendarEvents.css'
	);
});

Events::on('loadRenderers', function ($renderers) {
	$fhc_core_renderers =& $renderers();
	$fhc_core_renderers["reservierung"] = array(
		'calendarEvent' => APP_ROOT.'public/js/components/Cis/Renderer/Reservierungen/calendarEvent.js',
		'modalTitle' => APP_ROOT.'public/js/components/Cis/Renderer/Reservierungen/modalTitle.js',
		'modalContent' => APP_ROOT.'public/js/components/Cis/Renderer/Reservierungen/modalContent.js',
		'calendarEventStyles' => APP_ROOT.'public/css/Cis4/CoreCalendarEvents.css'
	);
});

Events::on('loadRenderers', function ($renderers) {
	$fhc_core_renderers =& $renderers();
	$fhc_core_renderers["ferien"] = array(
		'calendarEvent' => APP_ROOT.'public/js/components/Cis/Renderer/Feiertage/calendarEvent.js',
		'modalTitle' => APP_ROOT.'public/js/components/Cis/Renderer/Feiertage/modalTitle.js',
		'modalContent' => APP_ROOT.'public/js/components/Cis/Renderer/Feiertage/modalContent.js',
		'calendarEventStyles' => APP_ROOT.'public/css/Cis4/CoreCalendarEvents.css'
	);
});


