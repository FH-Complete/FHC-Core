<?php
require_once APPPATH . 'libraries/treemenu/TreeMenuLib.php';

/**
 * Description of OrgFormLib
 *
 * @author bambi
 */
class OrgFormLib extends TreeMenuLib
{
	public function getData()
	{
		if(!isset($this->params['stg']))
		{
			throw new Exception(self::class . ' Missing Parameter stg.');
		}

		$this->ci->load->model('codex/Orgform_model', 'OrgFormModel');

		$sql = <<<EOSQL
		select 
			o.*
		from
			bis.tbl_orgform o
		join (
			SELECT 
				tl.orgform_kurzbz 
			FROM 
				public.tbl_lehrverband AS tl
			WHERE 
				studiengang_kz = {$this->ci->OrgFormModel->escape($this->params['stg'])}
				and 
				orgform_kurzbz IS NOT null 
			group by 
				orgform_kurzbz
		) v on o.orgform_kurzbz = v.orgform_kurzbz
		order by
			o.orgform_kurzbz
EOSQL;

		$res = $this->ci->OrgFormModel->execReadonlyQuery($sql);
		$orgforms = hasData($res) ? getData($res) : array();

		$this->ci->addMeta('bhorgform_' . $this->params['stg'] , $orgforms);

		return $orgforms;
	}

	protected function getParamsForNextLevel($element=null)
	{
		if($element)
		{
			$paramsstg = array_merge($this->params, array('orgform' => $element->orgform_kurzbz));
			return $paramsstg;
		}
		return $this->params;
	}

	protected function getName($element)
	{
		$name = $element->orgform_kurzbz . ' ' . $element->bezeichnung;
		return $name;
	}
}
