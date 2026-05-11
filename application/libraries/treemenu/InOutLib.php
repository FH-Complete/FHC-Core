<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of InOutLib
 *
 * @author bambi
 */
class InOutLib
{
	public function getSubMenu()
	{
		return [
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
		];
	}
}
