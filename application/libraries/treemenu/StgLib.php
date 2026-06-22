<?php
require_once APPPATH . 'libraries/treemenu/TreeMenuLib.php';

/**
 * Description of InOutLib
 *
 * @author bambi
 */
class StgLib extends TreeMenuLib
{
	protected function getData()
	{
		$this->ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->ci->StudiengangModel->addOrder('typ');
		$this->ci->StudiengangModel->addOrder('kurzbz');
		$res = $this->ci->StudiengangModel->loadWhere(array('aktiv' => true));
		$stgs = hasData($res) ? getData($res) : array();

		$this->ci->addMeta('bhstg', $stgs);

		return $stgs;
	}

	protected function getParamsForNextLevel($element=null)
	{
		if($element)
		{
			$paramsstg = array_merge($this->params, array('stg' => $element->studiengang_kz));
			return $paramsstg;
		}
		return $this->params;
	}

	protected function getName($element)
	{
		$name = strtoupper($element->typ . $element->kurzbz) . ' ' . $element->bezeichnung;
		return $name;
	}
}
