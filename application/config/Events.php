<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

/**
 * NOTE(chris): example:
	Events::on('stv_conf_student', function (&$res) {
		$res['test'] = [
			'title' => 'TEST',
			'component' => './Stv/Studentenverwaltung/Details/Notizen.js'
		];
	});
 */
