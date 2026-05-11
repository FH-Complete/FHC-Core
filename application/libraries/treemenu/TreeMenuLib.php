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
abstract class TreeMenuLib
{
	protected $ci;
	protected $children_config;

	public function __construct($children_config)
	{
		$this->ci = get_instance();
		$this->children_config = $children_config;
		foreach($this->children_config as $child_config)
		{
			$grandchildren_config = isset($child_config['children']) ? $child_config['children'] : [];
			$this->ci->load->library($child_config->library, $grandchildren_config);
		}
	}

	public function getNode($name)
	{
		$node = array(
			'name' => $name
		);

		return $node;
	}

	public abstract function getSubMenu();
}
