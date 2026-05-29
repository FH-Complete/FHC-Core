<?php
require_once APPPATH . 'libraries/treemenu/TreeMenuLib.php';

/**
 * Description of InOutLib
 *
 * @author bambi
 */
class StgLib extends TreeMenuLib
{
	public function getNodes()
	{
		$this->ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$res = $this->ci->StudiengangModel->loadWhere(array('aktiv' => true));
		$stgs = hasData($res) ? getData($res) : array();

		$this->ci->addMeta('bhstg', $stgs);
		$nodes = array_map(
			function($stg) {
				return array(
					'name' => strtoupper($stg->typ . $stg->kurzbz) . ' ' . $stg->bezeichnung,
					'link' => 'stg/' . $stg->studiengang_kz,
					'leaf' => false
				);
			},
			$stgs
		);

		return $nodes;
	}

	public function getSubMenu()
	{
		return [
			'StgLib' => 'test123'
		];
	}
}
