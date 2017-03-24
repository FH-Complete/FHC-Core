<?php

class Studiengang_widget extends Widget
{
	public function __construct($name, $args, $htmlArgs = array())
	{
		// Calling daddy
		parent::__construct($name, $args, $htmlArgs);
	}
	
    public function display($widgetData)
	{
		// Studiengaenge
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->StudiengangModel->addOrder('kurzbzlang');
		$studiengaenge = $this->StudiengangModel->loadWhere(array('aktiv' => true));
		if (hasData($studiengaenge))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->studiengang_kz = Partial::HTML_DEFAULT_VALUE;
			$emptyElement->kurzbzlang = 'Select a studiengang...';
			$emptyElement->bezeichnung = '';
			array_unshift($studiengaenge->retval, $emptyElement);
		}
		else
		{
			show_error($studiengaenge);
		}
		
		// Data to be used in the widget view
		$widgetData['studiengaenge'] = $studiengaenge->retval;
		
		// Loads widget view
		$this->view('widgets/studiengang', $widgetData);
    }
}