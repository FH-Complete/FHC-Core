<?php
require_once APPPATH . 'libraries/treemenu/TreeMenuLib.php';

/**
 * Description of InOutLib
 *
 * @author bambi
 */
class InOutLib extends TreeMenuLib
{
	public function getNodes()
	{
		return [
			[
				'name' => 'International',
				'link' => 'inout',
				'children' => [
					[
						'name' => 'Incoming',
						'link' => 'inout/incoming',
						'leaf' => true
					],
					[
						'name' => 'Outgoing',
						'link' => 'inout/outgoing',
						'leaf' => true
					],
					[
						'name' => 'Gemeinsame Studien',
						'link' => 'inout/gemeinsamestudien',
						'leaf' => true
					]
				]
			]
		];
	}

	public function getSubMenu()
	{
		return [];
	}
}
