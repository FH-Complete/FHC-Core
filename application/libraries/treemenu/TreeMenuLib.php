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
class TreeMenuLib
{
	protected $ci;
	protected $children_config;
	protected $params;
	protected $includeSubMenu;

	public function __construct($children_config)
	{
		$this->ci =& get_instance();
		$this->children_config = $children_config;
		$this->params = array();
		$this->includeSubMenu = false;

		foreach($this->children_config as $child_config)
		{
			$grandchildren_config = isset($child_config['children']) ? $child_config['children'] : [];
			$this->ci->load->library($child_config['library'], $grandchildren_config, basename($child_config['library']));
		}
	}

	public function init($params=array(), $includeSubMenu=false)
	{
		$this->params = $params;
		$this->includeSubMenu = $includeSubMenu;
	}

	public function getNodes()
	{
		$data = $this->getData();

		$nodes = array();
		foreach($data as $element)
		{
			$node = array(
					'name' => $this->getName($element),
					'link' => $this->getLink($element),
					'leaf' => $this->isLeaf()
			);
			if($this->includeSubMenu)
			{
				$node['children'] = $this->getSubMenu($element);
			}
			$nodes[] = $node;
		};

		return $nodes;
	}

	public function getSubMenu($element=null)
	{
		$nodes = array();

		foreach($this->children_config as $childconfig)
		{
			$childlib = basename($childconfig['library']);
			$this->ci->$childlib->init(
				$this->getParamsForNextLevel($element),
				$this->includeSubMenu
			);
			$childnodes = $this->ci->$childlib->getNodes();
			$nodes = array_merge($nodes, $childnodes);
		}

		return $nodes;
	}

	protected function getData()
	{
		return array();
	}

	protected function getParamsForNextLevel($element=null)
	{
		return $this->params;
	}

	protected function getName($element)
	{
		return __METHOD__ . 'NOT IMPLEMENTED';
	}

	protected function getLink($element)
	{
		$link = $this->ci->uri->assoc_to_uri($this->getParamsForNextLevel($element));
		return $link;
	}

	protected function isLeaf()
	{
		if(count($this->children_config) === 0) {
			return true;
		}
		return false;
	}
}
