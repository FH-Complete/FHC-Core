<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

$config['stv_menu'] = array(
	'library' => 'treemenu/StvMenuLib',
	'children' => array(
		'stg' => array(
			'library' => 'treemenu/StgLib',
/*
			'children' => array(
				'pre' => array(
					'library' => 'treemenu/PrestudentLib'
				),
				'sem' => array(
					'library' => 'treemenu/AusbSemesterLib',
					'children' => array(
						'vbd' => array(
							'library' => 'treemenu/VerbandLib'
						)
					)
				),
				'orgform' => array(
					'library' => 'treemenu/OrgFormLib',
					'children' => array(
						'pre' => array(
							'library' => 'treemenu/PrestudentLib'
						),
						'sem' => array(
							'library' => 'treemenu/AusbSemesterLib',
							'children' => array(
								'vbd' => array(
									'library' => 'treemenu/VerbandLib'
								)
							)
						)
					)
				)
			)
*/
		),
		'inout' => array(
			'library' => 'treemenu/InOutLib'
		),
	)
);
