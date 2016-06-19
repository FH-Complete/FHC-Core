<?php

/*
 * JSONEditor widget
 */
class jsoneditor_widget extends Widget 
{
    public function display($data) 
	{
		// set default values if needed
		if (! isset($data['vareditor']))
			$data['vareditor'] = 'jsoneditor';
		if (! isset($data['style']))
			$data['style'] = 'width: 500px; height: 400px;';
		if (! isset($data['id']))
			$data['id'] = 'jsoneditor';
		if (! isset($data['mode']))
			$data['mode'] = 'code';
		if (! isset($data['modes']))
			$data['modes'] = "'code', 'tree', 'form'";// allowed modes
		if (! isset($data['json']))
			$data['json'] = '{
				"array": [1, 2, 3],
				"boolean": true,
				"null": null,
				"number": 123,
				"object": {"a": "b", "c": "d"},
				"string": "Hello FH-Complete"
			  }';
        $this->view('widgets/jsoneditor', $data);
    }
}
