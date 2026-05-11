<?php

/**
 * Copyright (C) 2026 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

$config['stv'] = "menu/StvMenuLib";

$config_part_stg_orgform = [
	'prestudent' => [
		'name' => ['stv', 'prestudent'],
		'no_sem_reload' => true,
		'children' => [
			'stdsem' => [
				'generationFunc' => 'getStdSemester',
				'children' => [
					'interessenten' => [
						'name' => ['stv', 'interessenten'],
						'children' => [
							'bewerbungnichtabgeschickt' => [
								'name' => 'Bewerbung nicht abgeschickt'
							],
							'bewerbungabgeschickt' => [
								'name' => 'Bewerbung abgeschickt, Status unbestätigt'
							],
							'zgv' => [
								'name' => 'ZGV erfüllt'
							],
							'statusbestaetigt' => [
								'name' => 'Status bestätigt',
								'children' => [
									'statusbestaetigtrtnichtangemeldet' => [
										'name' => 'Nicht zum Reihungstest angemeldet'
									],
									'statusbestaetigtrtangemeldet' => [
										'name' => 'Reihungstest angemeldet'
									]
								]
							],
							'reihungstestnichtangemeldet' => [
								'name' => 'Nicht zum Reihungstest angemeldet'
							],
							'reihungstestangemeldet' => [
								'name' => 'Reihungstest angemeldet'
							]

						]
					],
					'bewerber' => [
						'name' => ['stv', 'bewerber'],
						'children' => [
							'bewerberrtnichtangemeldet' => [
								'name' => 'Nicht zum Reihungstest angemeldet'
							],
							'bewerberrtangemeldet' => [
								'name' => 'Reihungstest angemeldet',
								'children' => [
									'bewerberrtangemeldetteilgenommen' => [
										'name' => 'Teilgenommen'
									],
									'bewerberrtangemeldetnichtteilgenommen' => [
										'name' => 'Nicht teilgenommen'
									]
								]
							]
						]
					],
					'aufgenommen' => [
						'name' => ['stv', 'aufgenommen']
					],
					'warteliste' => [
						'name' => ['stv', 'warteliste']
					],
					'absage' => [
						'name' => ['stv', 'absage']
					],
					'incoming' => [
						'name' => ['stv', 'incoming']
					]
				]
			]
		]
	],
	'semester' => [
		'generationFunc' => 'getSemester',
		'children' => [
			'group' => [
				'generationFunc' => 'getGroups'
			],
			'verband' => [
				'generationFunc' => 'getVerbaende',
				'children' => [
					'group' => [
						'generationFunc' => 'getVerbandGroups'
					]
				]
			]
		]
	]
];
$config['config_stg_based'] = [
	'stg' => [
		'generationFunc' => 'getStgs',
		'children' => array_merge(
			$config_part_stg_orgform,
			[
				'orgform' => [
					'generationFunc' => 'getOrgforms',
					'children' => $config_part_stg_orgform
				]
			]
		)
	],
	'inout' => [
		'name' => ['stv', 'inout'],
		'children' => [
			'incoming' => [
				'name' => ['stv', 'incoming']
			],
			'outgoing' => [
				'name' => ['stv', 'outgoing']
			],
			'shared_studies' => [
				'name' => ['stv', 'shared_studies']
			]
		]
	]
];
/*$config['config_stg_based'] = [
	'stgLib' => array_merge(
		$config_part_stg_orgform,
		[
			'orgformLib' => [
				'prestudentLib' => [
					'stdsemLib' => [
						'interessentenLib'
					],
					'bewerber' => [
						'name' => ['stv', 'bewerber'],
						'children' => [
							'bewerberrtnichtangemeldet' => [
								'name' => 'Nicht zum Reihungstest angemeldet'
							],
							'bewerberrtangemeldet' => [
								'name' => 'Reihungstest angemeldet',
								'children' => [
									'bewerberrtangemeldetteilgenommen' => [
										'name' => 'Teilgenommen'
									],
									'bewerberrtangemeldetnichtteilgenommen' => [
										'name' => 'Nicht teilgenommen'
									]
								]
							]
						]
					],
					'aufgenommen' => [
						'name' => ['stv', 'aufgenommen']
					],
					'warteliste' => [
						'name' => ['stv', 'warteliste']
					],
					'absage' => [
						'name' => ['stv', 'absage']
					],
					'incoming' => [
						'name' => ['stv', 'incoming']
					]
				]
			]
		]
	],
	'semester' => [
		'generationFunc' => 'getSemester',
		'children' => [
			'group' => [
				'generationFunc' => 'getGroups'
			],
			'verband' => [
				'generationFunc' => 'getVerbaende',
				'children' => [
					'group' => [
						'generationFunc' => 'getVerbandGroups'
					]
				]
			]
		]
	]

				]
			]
		)
	],
	'inoutLib' => [
		'incomingLib',
		'outgoingLib',
		'shared_studiesLib'
	]
];
*/