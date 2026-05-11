<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

$config['stv_menu'] = array(
	'library' => 'treemenu/StvMenuLib',
	'children' => array(
		'stg' => array(
			'library' => 'treemenu/StgLib',
			'children' => array(
				'pre' => array(
					'library' => 'treemenu/Prestudent'
				),
				'sem' => array(
					'library' => 'treemenu/AusbSemester',
					'children' => array(
						'vbd' => array(
							'library' => 'treemenu/Verband'
						)
					)
				),
				'orgform' => array(
					'library' => 'treemenu/OrgForm',
					'children' => array(
						'pre' => array(
							'library' => 'treemenu/Prestudent'
						),
						'sem' => array(
							'library' => 'treemenu/AusbSemester',
							'children' => array(
								'vbd' => array(
									'library' => 'treemenu/Verband'
								)
							)
						)
					)
				)
			)
		),
		'inout' => array(
			'library' => 'treemenu/InOutLib'
		),
	)
);
