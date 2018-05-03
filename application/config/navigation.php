<?php

$config['navigation_header'] = array(
	'*' => array(
		'FH-Complete' => site_url(''),
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
				'link' => site_url('/system/infocenter/InfoCenter'),
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
				'link' => site_url('/system/extensions/Manager'),
				'icon' => 'cubes',
				'description' => 'Extensions Manager',
				'expand' => true
			)
		)
	)
);


$config['navigation_menu']['system/infocenter/InfoCenter/index'] = array(
	'Freigegeben' => array(
		'link' => base_url('index.ci.php/system/infocenter/InfoCenter/infocenterFreigegeben'),
		'description' => 'Freigegeben',
		'icon' => 'thumbs-up'
	));

$config['navigation_menu']['system/infocenter/InfoCenter/showDetails'] = array(
	'Freigegeben' => array(
		'link' => base_url('index.ci.php/system/infocenter/InfoCenter/infocenterFreigegeben'),
		'description' => 'Freigegeben',
		'icon' => 'thumbs-up'
	));

$config['navigation_menu']['system/infocenter/InfoCenter/infocenterFreigegeben'] = array(
	'Zurück' => array(
		'link' => base_url('index.ci.php/system/infocenter/InfoCenter/index'),
		'description' => 'Zurück',
		'icon' => 'angle-left'
	));
