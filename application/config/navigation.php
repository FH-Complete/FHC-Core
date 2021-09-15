<?php
// Header menu

$config['navigation_header'] = array(
	'*' => array(
		'fhcomplete' => array(
			'link' => site_url(''),
			'icon' => '',
			'description' => 'FH-Complete',
			'sort' => 10
		),
		'Organisation' => array(
			'link' => '#',
			'icon' => 'sitemap',
			'description' => 'Organisation',
			'sort' => 20,
			'requiredPermissions' => 'basis/vilesci:r',
			'children'=> array(
				'vilesci' => array(
					'link' => base_url('vilesci'),
					'icon' => '',
					'description' => 'Vilesci',
					'expand' => true,
					'sort' => 10,
					'requiredPermissions' => 'basis/vilesci:r'
				)
			)
		),
		'Lehre' => array(
			'link' => '#',
			'icon' => 'graduation-cap',
			'description' => 'Lehre',
			'sort' => 30,
			'requiredPermissions' => 'basis/vilesci:r',
			'children'=> array(
				'cis' => array(
					'link' => CIS_ROOT,
					'icon' => '',
					'description' => 'CIS',
					'sort' => 10
				),
				'reihungstest' => array(
					'link' => site_url('organisation/Reihungstest'),
					'description' => 'Reihungstests',
					'expand' => true,
					'sort' => 20,
					'requiredPermissions' => 'infocenter:r'
				),
				'infocenter' => array(
					'link' => site_url('system/infocenter/InfoCenter'),
					'description' => 'Infocenter',
					'expand' => true,
					'sort' => 30,
					'requiredPermissions' => 'infocenter:r'
				),
				'lehrauftrag' => array(
					'link' => site_url('lehre/lehrauftrag/Lehrauftrag/Dashboard'),
					'description' => 'Lehrauftrag',
					'expand' => true,
					'sort' => 40,
					'requiredPermissions' => array(
						'lehre/lehrauftrag_bestellen:r',
						'lehre/lehrauftrag_erteilen:r'
					)
				),
				'zgvueberpruefung' => array(
					'link' => site_url('system/infocenter/ZGVUeberpruefung'),
					'description' => 'ZGV Überprüfung',
					'expand' => true,
					'sort' => 50,
					'requiredPermissions' => array(
						'lehre/zgvpruefung:r'
					)
				)
			)
		),
		'Personen' => array(
			'link' => '#',
			'icon' => 'user',
			'description' => 'Personen',
			'sort' => 40,
			'requiredPermissions' => 'basis/vilesci:r',
			'children'=> array(
				'messages' => array(
					'link' => site_url('system/messages/MessageClient/read'),
					'icon' => '',
					'target' => '_blank',
					'description' => 'Messages',
					'sort' => 10,
				),
				'bpk' => array(
					'link' => site_url('person/BPKWartung'),
					'icon' => '',
					'description' => 'BPK Wartung',
					'sort' => 20,
					'requiredPermissions' => 'admin:r'
				)
			)
		),
		'Administration' => array(
			'link' => '#',
			'icon' => 'gear',
			'description' => 'Administration',
			'expand' => false,
			'sort' => 50,
			'requiredPermissions' => 'admin:r',
			'children'=> array(
				'extensions' => array(
					'link' => site_url('system/extensions/Manager'),
					'description' => 'Extensions Manager',
					'expand' => true,
					'sort' => 10,
					'requiredPermissions' => 'admin:r'
				),
				'logsviewer' => array(
					'link' => site_url('system/LogsViewer'),
					'description' => 'Logs',
					'expand' => true,
					'sort' => 20,
					'requiredPermissions' => 'system/developer:r'
				),
				'jobsqueueviewer' => array(
					'link' => site_url('system/jq/JobsQueueViewer'),
					'description' => 'Jobs Queue Viewer',
					'expand' => true,
					'sort' => 20,
					'requiredPermissions' => 'system/developer:r'
				)
			)
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
	)
);

$config['navigation_menu']['organisation/Reihungstest/index'] = array(
	'reihungstestverwalung' => array(
		'link' => base_url('vilesci/stammdaten/reihungstestverwaltung.php'),
		'description' => 'Reihungstestverwaltung',
		'icon' => 'cogs',
		'sort' => 1,
		'target' => '_blank'
	),
	'auswertung' => array(
		'link' => CIS_ROOT.'/cis/testtool/admin/auswertung.php',
		'description' => 'Auswertung',
		'icon' => 'list-alt',
		'sort' => 1,
		'target' => '_blank'
	)
);

$config['navigation_menu']['lehre/lehrauftrag/Lehrauftrag/*'] = array(
	'lehrauftragDashboard' => array(
		'link' => site_url('lehre/lehrauftrag/Lehrauftrag/Dashboard'),
		'description' => 'Dashboard',
		'icon' => 'dashboard',
		'sort' => 1,
		'requiredPermissions' => array('lehre/lehrauftrag_bestellen:r','lehre/lehrauftrag_erteilen:r')
	),'lehrauftragBestellen' => array(
		'link' => site_url('lehre/lehrauftrag/Lehrauftrag'),
		'description' => 'Lehrauftrag bestellen',
		'icon' => '',
		'sort' => 1,
		'requiredPermissions' => 'lehre/lehrauftrag_bestellen:r'
	),
	'lehrauftragErteilen' => array(
		'link' => site_url('lehre/lehrauftrag/LehrauftragErteilen'),
		'description' => 'Lehrauftrag erteilen',
		'icon' => '',
		'sort' => 1,
		'requiredPermissions' => 'lehre/lehrauftrag_erteilen:r'
	),
	'lehrauftragLehrendeÜbersicht' => array(
		'link' => site_url('lehre/lehrauftrag/Lehrauftrag/LehrendeUebersicht'),
		'description' => 'Lehrendenübersicht',
		'icon' => '',
		'sort' => 1,
		'requiredPermissions' => array('lehre/lehrauftrag_erteilen:r')
	)
);
$config['navigation_menu']['lehre/lehrauftrag/LehrauftragErteilen/*'] = array(
	'lehrauftragDashboard' => array(
		'link' => site_url('lehre/lehrauftrag/Lehrauftrag/Dashboard'),
		'description' => 'Dashboard',
		'icon' => 'dashboard',
		'sort' => 1,
		'requiredPermissions' => array('lehre/lehrauftrag_bestellen:r','lehre/lehrauftrag_erteilen:r')
	),'lehrauftragBestellen' => array(
		'link' => site_url('lehre/lehrauftrag/Lehrauftrag'),
		'description' => 'Lehrauftrag bestellen',
		'icon' => '',
		'sort' => 1,
		'requiredPermissions' => 'lehre/lehrauftrag_bestellen:r'
	),
	'lehrauftragErteilen' => array(
		'link' => site_url('lehre/lehrauftrag/LehrauftragErteilen'),
		'description' => 'Lehrauftrag erteilen',
		'icon' => '',
		'sort' => 1,
		'requiredPermissions' => 'lehre/lehrauftrag_erteilen:r'
	),
	'lehrauftragLehrendeÜbersicht' => array(
		'link' => site_url('lehre/lehrauftrag/Lehrauftrag/LehrendeUebersicht'),
		'description' => 'Lehrendenübersicht',
		'icon' => '',
		'sort' => 1,
		'requiredPermissions' => array('lehre/lehrauftrag_erteilen:r')
	)
);
