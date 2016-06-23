<?php

/*
 * TinyMCE widget
 */
class tinymce_widget extends Widget 
{
    public function display($data) 
	{
		if (! isset($data['selector']))
			$data['selector'] = 'textarea';
		if (! isset($data['name']))
			$data['name'] = 'text';
		if (! isset($data['text']))
			$data['text'] = '';
		if (! isset($data['style']))
			$data['style'] = 'width:100%';
		if (! isset($data['menubar']))
			$data['menubar'] = 'false';
		if (! isset($data['plugins']))
			$data['plugins'] = '
				"advlist autolink lists link image charmap print preview anchor",
        		"searchreplace visualblocks code fullscreen",
        		"insertdatetime media table contextmenu paste"';
		if (! isset($data['toolbar']))
			$data['toolbar'] = 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code';

        $this->view('widgets/tinymce', $data);
    }
    
}
