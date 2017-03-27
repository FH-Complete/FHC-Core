<?php

class Reihungstest_widget extends Widget
{
	public function __construct($name, $args, $htmlArgs = array())
	{
		// Calling daddy
		parent::__construct($name, $args, $htmlArgs);
	}
	
    public function display($widgetData)
	{
		// Reihungstest
		$reihungstest = success(array()); // default value empty array
		// If the parameters studiengang or studiensemester are given and are not empty
		if (isset($widgetData) && is_array($widgetData)
			&& ((isset($widgetData['studiengang']) && !empty($widgetData['studiengang']))
			|| (isset($widgetData['studiensemester']) && !empty($widgetData['studiensemester']))))
		{
			$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
			$this->ReihungstestModel->addSelect('reihungstest_id, concat(datum, \' \',  uhrzeit, \' \', anmerkung) AS beschreibung');
			$this->ReihungstestModel->addOrder('datum', 'DESC');
			
			$parametersArray = array();
			if ($widgetData['studiengang'] != null)
			{
				$parametersArray['studiengang_kz'] = $widgetData['studiengang'];
			}
			if ($widgetData['studiensemester'] != null)
			{
				$parametersArray['studiensemester_kurzbz'] = $widgetData['studiensemester'];
			}
			
			$reihungstest = $this->ReihungstestModel->loadWhere($parametersArray);
			if (isError($reihungstest))
			{
				show_error($reihungstest);
			}
		}
		
		if (!isError($reihungstest))
		{
			if (hasData($reihungstest))
			{
				// Adding an empty element at the beginning
				$emptyElement = new stdClass();
				$emptyElement->reihungstest_id = Partial::HTML_DEFAULT_VALUE;
				$emptyElement->beschreibung = 'Select a reihungstest...';
				array_unshift($reihungstest->retval, $emptyElement);
			}
			else
			{
				// Adding an element to the array
				$emptyElement = new stdClass();
				$emptyElement->reihungstest_id = Partial::HTML_DEFAULT_VALUE;
				$emptyElement->beschreibung = 'No reihungstest found';
				array_unshift($reihungstest->retval, $emptyElement);
			}
		}
		
		// Data to be used in the widget view
		$widgetData['reihungstests'] = $reihungstest->retval;
		
		// Loads widget view
		$this->view('widgets/reihungstest', $widgetData);
    }
}