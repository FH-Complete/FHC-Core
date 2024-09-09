<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

// build the menu for fhc 

/**
 * NOTE(chris): example:
	Events::on('stv_conf_student', function (&$res) {
		$res['test'] = [
			'title' => 'TEST',
			'component' => './Stv/Studentenverwaltung/Details/Notizen.js'
		];
	});
 */
