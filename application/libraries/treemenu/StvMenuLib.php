<?php
require_once APPPATH . 'libraries/treemenu/TreeMenuLib.php';
/**
 * Description of StvMenuLib
 *
 * @author bambi
 */
class StvMenuLib extends TreeMenuLib
{
	public function getSubMenu()
	{
		$nodes = array();

		foreach($this->children_config as $childconfig)
		{
			$childlib = basename($childconfig['library']);
			$childnodes = $this->ci->$childlib->getNodes();
			$nodes = array_merge($nodes, $childnodes);
		}

		return $nodes;
	}
}
