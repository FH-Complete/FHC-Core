<?php

class Stufe_widget extends Widget
{
	public function __construct($name, $args, $htmlArgs = array())
	{
		// Calling daddy
		parent::__construct($name, $args, $htmlArgs);
	}
	
    public function display($widgetData)
	{
		// Stufe
		$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
		$this->ReihungstestModel->addSelect('DISTINCT ON(stufe) stufe, stufe AS beschreibung');
		$this->ReihungstestModel->addOrder('stufe');
		$stufen = $this->ReihungstestModel->loadWhere('stufe IS NOT NULL');
		if (hasData($stufen))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->stufe = Partial::HTML_DEFAULT_VALUE;
			$emptyElement->beschreibung = 'Select a stufe...';
			array_unshift($stufen->retval, $emptyElement);
		}
		else
		{
			show_error($stufen);
		}
		
		// Data to be used in the widget view
		$widgetData['stufen'] = $stufen->retval;
		
		// Loads widget view
		$this->view('widgets/stufe', $widgetData);
    }
}