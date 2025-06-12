<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

Events::on('loadRenderers', function ($renderers) {
	$fhc_core_renderers =& $renderers();
	$fhc_core_renderers["lehreinheit"] = array(
		'calendarEvent' => APP_ROOT.'public/js/components/Cis/LvPlan/EventTypes/calendarEvent.js',
		'modalTitle' => APP_ROOT.'public/js/components/Cis/Mylv/modalTitle.js',
		'modalContent' => APP_ROOT.'public/js/components/Cis/Mylv/modalContent.js'
	);
});


