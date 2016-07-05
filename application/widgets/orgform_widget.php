<?php

/*
 * OrgForm widget
 */
class orgform_widget extends Widget
{
	protected $_htmltagname = 'orgform_kurzbz';

    public function display($data)
	{
		$this->load->model('codex/Orgform_model', 'OrgFormModel');
		$res = $this->OrgFormModel->load();

		// set data
		if (isset($data['htmltagname']))
			$this->_htmltagname = $data['htmltagname'];

		// empty item for null values
		$item = array
		(
			'name' => ' - ',
			'value' => ''
		);
		if (empty($data['oe_kurzbz']))
			$item['selected'] = true;
		else
			$item['selected'] = false;
		$data['items'][] = $item;

		// items from db
		foreach ($res->retval as $obj)
		{
			$item = array
			(
				'name' => $obj->orgform_kurzbz.' ('.$obj->bezeichnung.')',
				'value' => $obj->orgform_kurzbz
			);
			if (isset($data['orgform_kurzbz']) && $obj->orgform_kurzbz == $data['orgform_kurzbz'])
				$item['selected'] = true;
			else
				$item['selected'] = false;
			$data['items'][] = $item;
        }
		$data['htmltagname'] = $this->_htmltagname;

        $this->view('widgets/orgform', $data);
    }
}
