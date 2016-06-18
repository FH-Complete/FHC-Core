<?php

/*
 * OE widget
 */
class organisationseinheit_widget extends Widget 
{
    public function display($data) 
	{
		$this->load->model('organisation/Organisationseinheit_model');
		$res = $this->Organisationseinheit_model->getRecursiveList();
		var_dump($res);
		foreach ($res->retval->result() as $obj)
		{
			$item = array('name' => $obj->name, 'value' => $obj->value);
			if (isset($data['oe_kurzbz']) && $obj->value == $data['oe_kurzbz'])
				$item['selected'] = true;
			else
				$item['selected'] = false;
			$data['items'][] = $item;
        }
		if (! isset($data['name']))
			$data['name'] = 'oe_kurzbz';
        $this->view('widgets/organisationseinheit', $data);
    }
    
}
