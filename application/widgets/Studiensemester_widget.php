<?php

class Studiensemester_widget extends Widget
{
	public function __construct($name, $args, $htmlArgs = array())
	{
		// Calling daddy
		parent::__construct($name, $args, $htmlArgs);
	}
	
    public function display($widgetData)
	{
		// Studiensemester
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->StudiengangModel->addSelect('studiensemester_kurzbz, studiensemester_kurzbz AS beschreibung');
		$this->StudiengangModel->addOrder('studiensemester_kurzbz', 'DESC');
		$studiensemester = $this->StudiensemesterModel->load();
		if (hasData($studiensemester))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->studiensemester_kurzbz = Partial::HTML_DEFAULT_VALUE;
			$emptyElement->beschreibung = 'Select a studiensemester...';
			array_unshift($studiensemester->retval, $emptyElement);
		}
		else if (isError($studiensemester))
		{
			show_error($studiensemester);
		}
		else
		{
			// Adding an element to the array
			$emptyElement = new stdClass();
			$emptyElement->studiensemester_kurzbz = Partial::HTML_DEFAULT_VALUE;
			$emptyElement->beschreibung = 'No studiensemester found';
			array_unshift($studiensemester->retval, $emptyElement);
		}
		
		// Data to be used in the widget view
		$widgetData['studiensemesters'] = $studiensemester->retval;
		
		// Loads widget view
		$this->view('widgets/studiensemester', $widgetData);
    }
}