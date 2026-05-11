<?php
require_once APPPATH . 'library/treemenu/TreeMenuLib.php';
/**
 * Description of StvMenuLib
 *
 * @author bambi
 */
class StvMenuLib extends TreeMenuLib
{
	public function getSubMenu()
	{
		return [
			'StvMenuLib' => null
		];
	}
}
