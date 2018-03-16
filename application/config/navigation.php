<?php

$config['navigation_header'] = array(
	'Vilesci/index' => array(
		'FH-Complete' => base_url('index.ci.php/'),
		'Vilesci' => base_url('/vilesci'),
		'CIS' => CIS_ROOT
	),
	'system/infocenter/InfoCenter/index' => array(
		'FH-Complete' => base_url('index.ci.php/'),
		'Vilesci' => base_url('/vilesci'),
		'CIS' => CIS_ROOT
	),
	'system/infocenter/InfoCenter/showDetails' => array(
		'FH-Complete' => base_url('index.ci.php/'),
		'Vilesci' => base_url('/vilesci'),
		'CIS' => CIS_ROOT
	)
);

$config['navigation_menu'] = array();

$config['navigation_menu']['Vilesci/index'] = array(
	'Dashboard' => array(
		'link' => '#',
		'description' => 'Dashboard',
		'icon' => 'dashboard'
	),
	'Lehre' => array(
		'link' => '#',
		'icon' => 'graduation-cap',
		'description' => 'Lehre',
		'expand' => true,
		'children'=> array(
			'CIS' => array(
				'link' => CIS_ROOT,
				'icon' => '',
				'description' => 'CIS',
				'expand' => true
			),
			'Infocenter' => array(
				'link' => base_url('index.ci.php/system/infocenter/InfoCenter'),
				'icon' => 'info',
				'description' => 'Infocenter',
				'expand' => true
			),
		)
	),
	'Administration' => array(
		'link' => '#',
		'icon' => 'gear',
		'description' => 'Administration',
		'expand' => false,
		'children'=> array(
			'Vilesci' => array(
				'link' => base_url('vilesci/'),
				'icon' => '',
				'description' => 'Vilesci',
				'expand' => true
			),
			'Extensions' => array(
				'link' => base_url('index.ci.php/system/extensions/Manager'),
				'icon' => 'cubes',
				'description' => 'Extensions Manager',
				'expand' => true
			),
			'Datenschutz' => array(
				'link' => base_url('index.ci.php/extensions/FHC-Core-DSMS/export'),
				'description' => 'Datenschutz',
				'icon' => 'legal',
				'expand' => true
			)
		)
	)
);
