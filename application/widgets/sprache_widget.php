<?php

/*
 * OE widget
 */
class sprache_widget extends Widget
{
    public function display($data)
	{
		$this->load->model('system/Sprache_model');
		$res = $this->Sprache_model->loadWhole();
		//var_dump($res);
		foreach ($res->retval as $obj)
		{
			$item = array('sprache' => $obj->sprache);
			if (isset($data['sprache']) && $obj->sprache == $data['sprache'])
				$item['selected'] = true;
			else
				$item['selected'] = false;
			$data['items'][] = $item;
        }
		if (! isset($data['sprache']))
			$data['sprache'] = 'German';
        $this->view('widgets/sprache', $data);
    }

}
