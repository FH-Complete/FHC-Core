<?php

/*
 * MimeType widget
 */
class mimetype_widget extends Widget 
{
    public function display($data) 
	{
		if (is_null($data['mimetype']))
				$data['mimetype'] = '';
		$this->load->model('system/Vorlage_model');
		$res = $this->Vorlage_model->getMimeTypes();
		//var_dump($res);
		foreach ($res->retval->result() as $obj)
		{
			$item = array('name' => $obj->mimetype, 'value' => $obj->mimetype);
			if (isset($data['mimetype']) && $obj->mimetype == $data['mimetype'])
				$item['selected'] = true;
			else
				$item['selected'] = false;
			$data['items'][] = $item;
        }
		$this->view('widgets/mimetype', $data);
    }
    
}
