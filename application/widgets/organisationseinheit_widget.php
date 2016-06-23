<?php

/*
 * OE widget
 */
class organisationseinheit_widget extends Widget 
{

	protected $_htmltagname = 'oe_kurzbz';
	
    public function display($data) 
	{
		$this->load->model('organisation/Organisationseinheit_model');
		$res = $this->Organisationseinheit_model->getRecursiveList();

		// *** set data ***
		if (isset($data['htmltagname']))
			$this->_htmltagname = $data['htmltagname'];
		
		// items from db
		foreach ($res->retval->result() as $obj)
		{
			$item = array
			(
				'name' => $obj->name,
				'value' => $obj->value
			);
			if (isset($data['oe_kurzbz']) && $data['oe_kurzbz'] == $obj->value)
				$item['selected'] = true;
			else
				$item['selected'] = false;
			$data['items'][] = $item;
        }
		$data['htmltagname'] = $this->_htmltagname;

        $this->view('widgets/organisationseinheit', $data);
    }
    
}
