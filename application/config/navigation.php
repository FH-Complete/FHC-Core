<?php

// --------------------------------------------------------------------------------------------------------------------
// Header menu

$config['navigation_header'] = array(
	'*' => array(
		'fhcomplete' => array(
			'link' => site_url(''),
			'icon' => '',
			'description' => 'FH-Complete',
			'sort' => 1
		),
		'vilesci' => array(
			'link' => base_url('vilesci'),
			'icon' => '',
			'description' => 'Vilesci',
			'sort' => 2,
			'requiredPermissions' => 'basis/vilesci:r'
		),
		'cis' => array(
			'link' => CIS_ROOT,
			'icon' => '',
			'description' => 'CIS',
			'sort' => 3
		)
	)
);

// --------------------------------------------------------------------------------------------------------------------
// Left side menu

$config['navigation_menu'] = array();

$config['navigation_menu']['Vilesci/index'] = array(
	'dashboard' => array(
		'link' => '#',
		'description' => 'Dashboard',
		'icon' => 'dashboard',
		'sort' => 1
	),
	'lehre' => array(
		'link' => '#',
		'icon' => 'graduation-cap',
		'description' => 'Lehre',
		'expand' => true,
		'sort' => 2,
		'children'=> array(
			'cis' => array(
				'link' => CIS_ROOT,
				'icon' => '',
				'description' => 'CIS',
				'expand' => true,
				'sort' => 1
			),
			'infocenter' => array(
				'link' => site_url('system/infocenter/InfoCenter'),
				'icon' => 'info',
				'description' => 'Infocenter',
				'expand' => true,
				'sort' => 2,
				'requiredPermissions' => 'infocenter:r'
			),
		)
	),
	'administration' => array(
		'link' => '#',
		'icon' => 'gear',
		'description' => 'Administration',
		'expand' => false,
		'sort' => 3,
		'children'=> array(
			'vilesci' => array(
				'link' => base_url('vilesci'),
				'icon' => '',
				'description' => 'Vilesci',
				'expand' => true,
				'sort' => 1,
				'requiredPermissions' => 'basis/vilesci:r'
			),
			'extensions' => array(
				'link' => site_url('system/extensions/Manager'),
				'icon' => 'cubes',
				'description' => 'Extensions Manager',
				'expand' => true,
				'sort' => 2,
				'requiredPermissions' => 'admin:r'
			)
		)
	)
);

$config['navigation_menu']['system/infocenter/InfoCenter/index'] = array(
	'freigegeben' => array(
		'link' => site_url('system/infocenter/InfoCenter/freigegeben'),
		'description' => 'Freigegeben',
		'icon' => 'thumbs-up',
		'sort' => 1
	)
);

$config['navigation_menu']['system/infocenter/InfoCenter/freigegeben'] = array(
	'back' => array(
		'link' => site_url('system/infocenter/InfoCenter/index'),
		'description' => 'Home',
		'icon' => 'angle-left',
		'sort' => 1
	)
);
